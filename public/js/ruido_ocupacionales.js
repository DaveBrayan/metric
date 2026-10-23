/**
 * METRIC V2 — Monitoreo de Ruido Ocupacional
 * Módulo JavaScript interactivo, cálculos acústicos Leq logarítmicos,
 * mapas Leaflet y controlador de modales (Vite ES Module)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_RUIDO_OCUPACIONAL_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/ruido-ocupacional/header');

// Variables de Estado
let currentGrid = PHOTO_REPORT_INITIAL_SETTINGS.grid || '2x3';
let selectedPoints = PHOTO_REPORT_INITIAL_SETTINGS.selected_points || ALL_MEASUREMENTS_DATA.map(m => m.id);
let photoIndices = PHOTO_REPORT_INITIAL_SETTINGS.photo_indices || {};
let singlePointMapInstance = null;
let allLocationsMapInstance = null;
let allLocationsMarkersLayer = null;
let allLocationsMarkersList = [];

// Paginación y Filtros de la Tabla Maestra
const ROWS_PER_PAGE = 10;
let currentPage = 1;
let currentFilterType = 'all';
let currentSearchQuery = '';

// Almacenamiento local de fotos
const modalPhotoStore = {
    create: { files: [], urls: [], activeIndex: 0 },
    edit: { files: [], existingUrls: [], remainingUrls: [], urls: [], activeIndex: 0 }
};

// Diccionario de Zonas y Normativas
const ZONAS_CONFIG = {
    'RASIM - ANEXO 12-C': {
        'Industrial - día': { horario: '08:00 a 22:00', limite: 70.0 },
        'Industrial - noche': { horario: '22:00 a 08:00', limite: 65.0 },
        'Comercial - día': { horario: '08:00 a 22:00', limite: 65.0 },
        'Comercial - noche': { horario: '22:00 a 08:00', limite: 60.0 },
        'Vivienda y oficinas - día': { horario: '08:00 a 22:00', limite: 60.0 },
        'Vivienda y oficinas - noche': { horario: '22:00 a 08:00', limite: 55.0 },
        'Hospitales': { horario: 'Todo el día', limite: 55.0 },
    },
    'RMCA - ANEXO 6': {
        'General - día': { horario: '06:00 a 22:00', limite: 68.0 },
        'General - noche': { horario: '22:00 a 06:00', limite: 65.0 },
        'Descanso*': { horario: 'Horario de descanso', limite: 55.0 },
    }
};

/* ==========================================================================
   1. CÁLCULO ACÚSTICO LEQ (ISO 1996 / EPA / RASIM)
   ========================================================================== */
function calculateAcousticLeq(values) {
    const valid = values.map(v => parseFloat(v)).filter(v => !isNaN(v) && v > 0);
    if (valid.length === 0) return null;
    let sum = 0;
    valid.forEach(v => {
        sum += Math.pow(10, v / 10.0);
    });
    const leq = 10 * Math.log10(sum / valid.length);
    return Math.round(leq * 10) / 10;
}

window.handleNormativaChange = function (prefix) {
    const normSelect = document.getElementById(`${prefix}_normativa`);
    const zonaSelect = document.getElementById(`${prefix}_tipo_zona`);
    const horarioInput = document.getElementById(`${prefix}_horario`);
    const limiteInput = document.getElementById(`${prefix}_limite_normativa`);

    if (!normSelect || !zonaSelect) return;
    const normVal = normSelect.value;
    const zonas = ZONAS_CONFIG[normVal] || {};

    zonaSelect.innerHTML = '<option value="">Seleccione tipo de zona...</option>';
    Object.keys(zonas).forEach(z => {
        const opt = document.createElement('option');
        opt.value = z;
        opt.textContent = z;
        zonaSelect.appendChild(opt);
    });

    if (horarioInput) horarioInput.value = '';
    if (limiteInput) limiteInput.value = '';
    recalcruidoOcupacional(prefix);
};

window.handleTipoZonaChange = function (prefix) {
    const normSelect = document.getElementById(`${prefix}_normativa`);
    const zonaSelect = document.getElementById(`${prefix}_tipo_zona`);
    const horarioInput = document.getElementById(`${prefix}_horario`);
    const limiteInput = document.getElementById(`${prefix}_limite_normativa`);

    if (!normSelect || !zonaSelect) return;
    const normVal = normSelect.value;
    const zonaVal = zonaSelect.value;
    const data = ZONAS_CONFIG[normVal]?.[zonaVal];

    if (data) {
        if (horarioInput) horarioInput.value = data.horario;
        if (limiteInput) limiteInput.value = data.limite;
    }
    recalcruidoOcupacional(prefix);
};

window.addCardinalPointInput = function (prefix, cardinalKey) {
    const container = document.getElementById(`${prefix}_${cardinalKey}_points_container`);
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'point-pill-input-row';
    row.innerHTML = `
        <input type="number" step="0.1" class="custom-form-input cardinal-point-val" placeholder="ej. 62.5" oninput="recalcruidoOcupacional('${prefix}')">
        <button type="button" class="btn-icon-del-point" onclick="this.closest('.point-pill-input-row').remove(); recalcruidoOcupacional('${prefix}');" title="Eliminar medición">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    `;
    container.appendChild(row);
    recalcruidoOcupacional(prefix);
};

window.recalcruidoOcupacional = function (prefix) {
    const limiteInput = document.getElementById(`${prefix}_limite_normativa`);
    const lmpDisp = document.getElementById(`${prefix}_lmp_display`);
    const leqDisp = document.getElementById(`${prefix}_leq_display`);
    const cumpleBadge = document.getElementById(`${prefix}_cumple_badge`);

    const limite = parseFloat(limiteInput?.value) || 68.0;
    if (lmpDisp) lmpDisp.textContent = `${limite.toFixed(1)} dBA`;

    // Recopilar todos los inputs de los 4 puntos cardinales
    const cardinalKeys = ['p1_norte', 'p2_sur', 'p3_este', 'p4_oeste'];
    const allVals = [];

    cardinalKeys.forEach(k => {
        const container = document.getElementById(`${prefix}_${k}_points_container`);
        const hiddenField = document.getElementById(`${prefix}_${k}_puntos_json`);
        const kVals = [];
        if (container) {
            container.querySelectorAll('.cardinal-point-val').forEach(inp => {
                const v = parseFloat(inp.value);
                if (!isNaN(v) && v > 0) {
                    kVals.push(v);
                    allVals.push(v);
                }
            });
        }
        if (hiddenField) {
            hiddenField.value = JSON.stringify(kVals);
        }
    });

    const leq = calculateAcousticLeq(allVals);
    if (leqDisp) {
        leqDisp.textContent = leq !== null ? `${leq.toFixed(1)} dBA` : '— dBA';
    }

    const isCompliant = leq !== null ? (leq <= limite) : true;
    if (cumpleBadge) {
        if (isCompliant) {
            cumpleBadge.className = 'badge-compliance-ok';
            cumpleBadge.innerHTML = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12" /></svg><span>CUMPLE</span>`;
        } else {
            cumpleBadge.className = 'badge-compliance-danger';
            cumpleBadge.innerHTML = `<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg><span>NO CUMPLE</span>`;
        }
    }
};

/* ==========================================================================
   2. UTM & MAPAS LEAFLET
   ========================================================================== */
function utmToLatLngJS(utmX, utmY, utmZoneStr = '19K') {
    let utmZoneNum = 19;
    let utmZoneLetter = 'K';
    const m = String(utmZoneStr).match(/(\d+)\s*([A-Za-z]?)/);
    if (m) {
        utmZoneNum = parseInt(m[1], 10) || 19;
        utmZoneLetter = (m[2] || 'K').toUpperCase();
    }

    const utmA = 6378137.0;
    const utmF = 1 / 298.257223563;
    const utmB = utmA * (1 - utmF);
    const utmE = Math.sqrt((utmA * utmA - utmB * utmB) / (utmA * utmA));
    const utmEPrime = Math.sqrt((utmA * utmA - utmB * utmB) / (utmB * utmB));
    const utmK0 = 0.9996;

    const utmIsSouth = utmZoneLetter ? (utmZoneLetter < 'N') : true;
    const x = parseFloat(utmX) - 500000.0;
    const y = utmIsSouth ? parseFloat(utmY) - 10000000.0 : parseFloat(utmY);

    const utmMarc = y / utmK0;
    const utmE2 = utmE * utmE;
    const utmE4 = utmE2 * utmE2;
    const utmE6 = utmE4 * utmE2;
    const utmE1 = (1 - Math.sqrt(1 - utmE2)) / (1 + Math.sqrt(1 - utmE2));

    const utmMu = utmMarc / (utmA * (1 - utmE2 / 4 - 3 * utmE4 / 64 - 5 * utmE6 / 256));

    const utmPhi1 = utmMu +
        (3 * utmE1 / 2 - 27 * Math.pow(utmE1, 3) / 32) * Math.sin(2 * utmMu) +
        (21 * utmE1 * utmE1 / 16 - 55 * Math.pow(utmE1, 4) / 32) * Math.sin(4 * utmMu) +
        (151 * Math.pow(utmE1, 3) / 96) * Math.sin(6 * utmMu) +
        (1097 * Math.pow(utmE1, 4) / 512) * Math.sin(8 * utmMu);

    const utmSinPhi1 = Math.sin(utmPhi1);
    const utmCosPhi1 = Math.cos(utmPhi1);
    const utmTanPhi1 = Math.tan(utmPhi1);

    const utmN1 = utmA / Math.sqrt(1 - utmE2 * utmSinPhi1 * utmSinPhi1);
    const utmT1 = utmTanPhi1 * utmTanPhi1;
    const utmC1 = utmEPrime * utmEPrime * utmCosPhi1 * utmCosPhi1;
    const utmR1 = utmA * (1 - utmE2) / Math.pow(1 - utmE2 * utmSinPhi1 * utmSinPhi1, 1.5);
    const utmD = x / (utmN1 * utmK0);

    const utmD2 = utmD * utmD;
    const utmD3 = utmD2 * utmD;
    const utmD4 = utmD2 * utmD2;
    const utmD5 = utmD4 * utmD;
    const utmD6 = utmD3 * utmD3;

    const utmLat = utmPhi1 - (utmN1 * utmTanPhi1 / utmR1) * (
        utmD2 / 2 -
        (5 + 3 * utmT1 + 10 * utmC1 - 4 * utmC1 * utmC1 - 9 * utmEPrime * utmEPrime) * utmD4 / 24 +
        (61 + 90 * utmT1 + 298 * utmC1 + 45 * utmT1 * utmT1 - 252 * utmEPrime * utmEPrime - 3 * utmC1 * utmC1) * utmD6 / 720
    );

    const utmLon = (utmD - (1 + 2 * utmT1 + utmC1) * utmD3 / 6 +
        (5 - 2 * utmC1 + 28 * utmT1 - 3 * utmC1 * utmC1 + 8 * utmEPrime * utmEPrime + 24 * utmT1 * utmT1) * utmD5 / 120
    ) / utmCosPhi1;

    const utmZoneCenterLon = (utmZoneNum - 1) * 6 - 180 + 3;
    const lat = (utmLat * 180.0) / Math.PI;
    const lng = utmZoneCenterLon + (utmLon * 180.0) / Math.PI;

    return { lat: Math.round(lat * 1e7) / 1e7, lng: Math.round(lng * 1e7) / 1e7 };
}

window.captureCoordinatesGPS = function (prefix) {
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización GPS.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            // Convert LatLng to approximate UTM 19K
            const utmX = Math.round(500000 + (lng + 69) * 111320);
            const utmY = Math.round(10000000 + lat * 110574);
            const zone = '19K';

            const zbInput = document.getElementById(`${prefix}_zona_banda`);
            if (zbInput) zbInput.value = zone;

            const nX = document.getElementById(`${prefix}_norte_x`);
            const nY = document.getElementById(`${prefix}_norte_y`);
            const sX = document.getElementById(`${prefix}_sur_x`);
            const sY = document.getElementById(`${prefix}_sur_y`);
            const eX = document.getElementById(`${prefix}_este_x`);
            const eY = document.getElementById(`${prefix}_este_y`);
            const oX = document.getElementById(`${prefix}_oeste_x`);
            const oY = document.getElementById(`${prefix}_oeste_y`);

            if (nX) nX.value = `${utmX}.000E`;
            if (nY) nY.value = `${utmY + 20}.000N`;

            if (sX) sX.value = `${utmX}.000E`;
            if (sY) sY.value = `${utmY - 20}.000N`;

            if (eX) eX.value = `${utmX + 20}.000E`;
            if (eY) eY.value = `${utmY}.000N`;

            if (oX) oX.value = `${utmX - 20}.000E`;
            if (oY) oY.value = `${utmY}.000N`;

            const latInp = document.getElementById(`${prefix}_latitude`);
            const lngInp = document.getElementById(`${prefix}_longitude`);
            if (latInp) latInp.value = lat;
            if (lngInp) lngInp.value = lng;
        },
        err => {
            console.warn('GPS Error:', err);
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
};

/* ==========================================================================
   3. MODALES DE Ruido Ocupacional
   ========================================================================== */
window.openCreateMeasurementModal = function () {
    const m = document.getElementById('createMeasurementModal');
    if (m) m.classList.add('open');
};

window.closeCreateMeasurementModal = function () {
    const m = document.getElementById('createMeasurementModal');
    if (m) m.classList.remove('open');
};

window.openEditMeasurementModal = function (item) {
    const m = document.getElementById('editMeasurementModal');
    if (!m) return;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${MODULE_ID}/ruido-ambiental/mediciones/${item.id}`;
    }

    // Set fields
    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v ?? ''; };
    setVal('edit_measurement_date', item.raw_date);
    setVal('edit_measurement_time', item.time);
    setVal('edit_normativa', item.normativa);
    handleNormativaChange('edit');
    setVal('edit_tipo_zona', item.tipo_zona);
    handleTipoZonaChange('edit');
    setVal('edit_limite_normativa', item.raw_limite_normativa);
    setVal('edit_zona_banda', item.zona_banda);

    setVal('edit_norte_colindancia', item.norte_colindancia);
    setVal('edit_norte_x', item.norte_x);
    setVal('edit_norte_y', item.norte_y);

    setVal('edit_sur_colindancia', item.sur_colindancia);
    setVal('edit_sur_x', item.sur_x);
    setVal('edit_sur_y', item.sur_y);

    setVal('edit_este_colindancia', item.este_colindancia);
    setVal('edit_este_x', item.este_x);
    setVal('edit_este_y', item.este_y);

    setVal('edit_oeste_colindancia', item.oeste_colindancia);
    setVal('edit_oeste_x', item.oeste_x);
    setVal('edit_oeste_y', item.oeste_y);

    setVal('edit_p1_norte_inicio', item.p1_norte_inicio);
    setVal('edit_p1_norte_fin', item.p1_norte_fin);
    setVal('edit_p2_sur_inicio', item.p2_sur_inicio);
    setVal('edit_p2_sur_fin', item.p2_sur_fin);
    setVal('edit_p3_este_inicio', item.p3_este_inicio);
    setVal('edit_p3_este_fin', item.p3_este_fin);
    setVal('edit_p4_oeste_inicio', item.p4_oeste_inicio);
    setVal('edit_p4_oeste_fin', item.p4_oeste_fin);

    // Populate cardinal point input rows
    const populatePoints = (cardinalKey, pointsList) => {
        const container = document.getElementById(`edit_${cardinalKey}_points_container`);
        if (!container) return;
        container.innerHTML = '';
        (pointsList || []).forEach(val => {
            const row = document.createElement('div');
            row.className = 'point-pill-input-row';
            row.innerHTML = `
                <input type="number" step="0.1" class="custom-form-input cardinal-point-val" value="${val}" oninput="recalcruidoOcupacional('edit')">
                <button type="button" class="btn-icon-del-point" onclick="this.closest('.point-pill-input-row').remove(); recalcruidoOcupacional('edit');">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            `;
            container.appendChild(row);
        });
    };

    populatePoints('p1_norte', item.p1_norte_puntos);
    populatePoints('p2_sur', item.p2_sur_puntos);
    populatePoints('p3_este', item.p3_este_puntos);
    populatePoints('p4_oeste', item.p4_oeste_puntos);

    setVal('edit_observations', item.raw_observations);
    setVal('edit_latitude', item.latitude);
    setVal('edit_longitude', item.longitude);

    recalcruidoOcupacional('edit');
    m.classList.add('open');
};

window.closeEditMeasurementModal = function () {
    const m = document.getElementById('editMeasurementModal');
    if (m) m.classList.remove('open');
};

window.confirmDeleteMeasurement = function (id, pointNum) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar medición?',
            text: `Se eliminará el punto ${pointNum} de Ruido Ocupacional. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            buttonsStyling: false,
            customClass: {
                popup: 'metric-swal-popup',
                confirmButton: 'metric-swal-btn-danger',
                cancelButton: 'metric-swal-btn-cancel'
            }
        }).then((res) => {
            if (res.isConfirmed) {
                const f = document.getElementById('deleteMeasurementForm');
                if (f) {
                    f.action = `/modulos/${MODULE_ID}/ruido-ambiental/mediciones/${id}`;
                    f.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar medición ${pointNum}?`)) {
            const f = document.getElementById('deleteMeasurementForm');
            if (f) {
                f.action = `/modulos/${MODULE_ID}/ruido-ambiental/mediciones/${id}`;
                f.submit();
            }
        }
    }
};

/**
 * Builds a single row for the Cálculos summary table with NTS-002/17 formulas.
 */
function buildCalculosRow(m, idx) {
    var med = m.mediciones_db || [];
    var tpe = parseFloat(m.tiempo_expos_h) || 8;
    var valid = med.map(function (v) { return parseFloat(v); }).filter(function (v) { return !isNaN(v) && v > 0; });
    var n = valid.length, sumaE = 0, npsMax = 0;
    valid.forEach(function (v) { sumaE += Math.pow(10, v / 10.0); if (v > npsMax) npsMax = v; });
    var leq = n > 0 ? (10 * Math.log10(sumaE / n)) : 0;
    var tmpe = leq > 0 ? (8 / Math.pow(2, (leq - 85) / 3)) : null;
    var dosis = (tmpe && tmpe > 0) ? (tpe / tmpe) : null;
    var cumple = dosis !== null && dosis <= 1;
    var leqC = (n > 0 && leq > 85) ? '#dc2626' : '#059669';
    var dosC = (dosis !== null && dosis > 1) ? '#dc2626' : '#059669';
    var r = '<tr style="border-bottom:1px solid #e2e8f0;">';
    r += '<td style="padding:8px 6px;text-align:center;font-weight:800;color:#94a3b8;">' + (idx + 1) + '</td>';
    r += '<td style="padding:8px 6px;font-weight:600;color:#0284c7;font-size:11px;">' + (m.area || '—') + '</td>';
    r += '<td style="padding:8px 6px;font-weight:600;color:#334155;font-size:11px;">' + (m.punto_medicion || '—') + '</td>';
    r += '<td style="padding:8px 6px;text-align:center;font-weight:700;">' + (n > 0 ? npsMax.toFixed(1) : '—') + '</td>';
    r += '<td style="padding:8px 6px;text-align:center;font-family:monospace;font-size:10px;color:#64748b;">' + (n > 0 ? sumaE.toExponential(3) : '—') + '</td>';
    r += '<td style="padding:8px 6px;text-align:center;font-weight:800;color:' + leqC + ';">' + (n > 0 ? leq.toFixed(2) : '—') + '</td>';
    r += '<td style="padding:8px 6px;text-align:center;font-weight:700;">' + tpe.toFixed(1) + '</td>';
    r += '<td style="padding:8px 6px;text-align:center;font-weight:700;">' + (tmpe !== null ? tmpe.toFixed(2) : '—') + '</td>';
    r += '<td style="padding:8px 6px;text-align:center;font-weight:800;color:' + dosC + ';">' + (dosis !== null ? dosis.toFixed(4) : '—') + '</td>';
    if (n === 0) { r += '<td style="padding:8px 6px;text-align:center;color:#94a3b8;">—</td>'; }
    else if (cumple) { r += '<td style="padding:8px 6px;text-align:center;"><span style="padding:2px 8px;background:#dcfce7;color:#166534;border-radius:4px;font-weight:700;font-size:10px;">CUMPLE</span></td>'; }
    else { r += '<td style="padding:8px 6px;text-align:center;"><span style="padding:2px 8px;background:#fee2e2;color:#991b1b;border-radius:4px;font-weight:700;font-size:10px;">NO CUMPLE</span></td>'; }
    r += '<td style="padding:8px 6px;text-align:center;"><button class="btn-admin-icon-action theme-cyan" onclick="openCalculosModal(ALL_MEASUREMENTS_DATA[' + idx + '], ' + idx + ')" title="Matriz de Cálculos" style="padding:4px 8px;"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg></button></td>';
    r += '</tr>';
    return r;
}

/**
 * Abre el modal de Cálculos con la matriz completa de 30 puntos 10^(NPSi/10) y fórmulas NTS-002/17.
 */
window.openCalculosListModal = function () {
    if (typeof openCalculosModal === 'function') {
        openCalculosModal(null, null);
    } else {
        const modal = document.getElementById('calculosModalRuido');
        if (modal) modal.style.display = 'flex';
    }
};

window.closeCalculosListModal = function () {
    if (typeof closeCalculosModal === 'function') {
        closeCalculosModal();
    } else {
        const modal = document.getElementById('calculosModalRuido');
        if (modal) modal.style.display = 'none';
    }
};


/* ==========================================================================
   4. MODAL MAPA GENERAL DE TODAS LAS UBICACIONES
   ========================================================================== */
window.openAllLocationsModal = function () {
    const m = document.getElementById('allLocationsModal');
    if (m) {
        m.classList.add('open');
        setTimeout(initAllLocationsMap, 200);
    }
};

window.closeAllLocationsModal = function () {
    const m = document.getElementById('allLocationsModal');
    if (m) m.classList.remove('open');
};

function initAllLocationsMap() {
    const container = document.getElementById('allLocationsMapLeaflet');
    if (!container || typeof L === 'undefined') return;

    if (!allLocationsMapInstance) {
        allLocationsMapInstance = L.map('allLocationsMapLeaflet').setView([-16.5, -68.15], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(allLocationsMapInstance);
        allLocationsMarkersLayer = L.featureGroup().addTo(allLocationsMapInstance);
    }

    allLocationsMarkersLayer.clearLayers();
    allLocationsMarkersList = [];

    const bounds = [];
    ALL_MEASUREMENTS_DATA.forEach((item, idx) => {
        let lat = item.latitude;
        let lng = item.longitude;

        if ((!lat || !lng) && item.norte_x && item.norte_y) {
            const eNum = parseFloat(String(item.norte_x).replace(/[^0-9.]/g, ''));
            const nNum = parseFloat(String(item.norte_y).replace(/[^0-9.]/g, ''));
            if (eNum && nNum) {
                const conv = utmToLatLngJS(eNum, nNum, item.zona_banda || '19K');
                lat = conv.lat;
                lng = conv.lng;
            }
        }

        if (lat && lng) {
            const dosisOk = (item.dosis !== null && item.dosis !== undefined && item.dosis <= 1.0);
            const leqOk = (item.leq_t_db !== null && item.leq_t_db !== undefined && item.leq_t_db <= 85);
            const isOk = dosisOk && leqOk;
            const markerColor = isOk ? '#059669' : '#dc2626';
            const iconSvg = `<svg width="24" height="24" viewBox="0 0 24 24" fill="${markerColor}" stroke="#ffffff" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5" fill="#ffffff"/></svg>`;

            const customIcon = L.divIcon({
                className: 'custom-loc-marker',
                html: iconSvg,
                iconSize: [24, 24],
                iconAnchor: [12, 24]
            });

            const marker = L.marker([lat, lng], { icon: customIcon }).addTo(allLocationsMarkersLayer);
            marker.bindPopup(`
                <div style="font-family: sans-serif; min-width: 170px;">
                    <div style="font-weight: 800; font-size: 13px; color: #0f172a; margin-bottom: 2px;">#${item.num} — Ruido Ocupacional</div>
                    <div style="font-size: 11.5px; color: #64748b;">${item.area} • ${item.punto_medicion}</div>
                    <div style="margin-top: 5px; font-weight: 700; color: ${isOk ? '#059669' : '#dc2626'}; font-size: 12px;">LAeq: ${item.leq_t_db !== null ? item.leq_t_db.toFixed(1) : '—'} dBA | Dosis: ${item.dosis !== null ? item.dosis.toFixed(2) : '—'}</div>
                    <div style="font-size: 11px; color: #0284c7; margin-top: 4px;">Reg: ${item.registered_by}</div>
                </div>
            `);

            allLocationsMarkersList[idx] = marker;
            bounds.push([lat, lng]);
        }
    });

    if (bounds.length > 0) {
        allLocationsMapInstance.fitBounds(bounds, { padding: [35, 35], maxZoom: 16 });
    }
}

window.focusPointOnAllLocationsMap = function (idx) {
    const marker = allLocationsMarkersList[idx];
    if (marker && allLocationsMapInstance) {
        allLocationsMapInstance.setView(marker.getLatLng(), 16, { animate: true });
        marker.openPopup();
    }
};

/* ==========================================================================
   5. MODAL MAPA INDIVIDUAL DE PUNTO
   ========================================================================== */
window.openMapModal = function (lat, lng, pointName, isCompliant, utmInfo) {
    const m = document.getElementById('mapModal');
    if (!m) return;
    m.classList.add('open');

    const titleEl = document.getElementById('mapModalTitle');
    if (titleEl) titleEl.textContent = `Ubicación GPS — ${pointName}`;

    const infoEl = document.getElementById('mapModalUtmInfo');
    if (infoEl) infoEl.textContent = utmInfo || `${lat}, ${lng}`;

    setTimeout(() => {
        if (!singlePointMapInstance && typeof L !== 'undefined') {
            singlePointMapInstance = L.map('singlePointMapLeaflet').setView([lat || -16.5, lng || -68.15], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(singlePointMapInstance);
        }
        if (singlePointMapInstance && lat && lng) {
            singlePointMapInstance.setView([lat, lng], 16);
            L.marker([lat, lng]).addTo(singlePointMapInstance).bindPopup(`<b>${pointName}</b><br>${utmInfo}`).openPopup();
        }
    }, 200);
};

window.closeMapModal = function () {
    const m = document.getElementById('mapModal');
    if (m) m.classList.remove('open');
};

/* ==========================================================================
   6. MODAL VISOR FOTOGRÁFICO
   ========================================================================== */
window.openPhotoViewerModal = function (url, pointName, caption) {
    const m = document.getElementById('photoViewerModal');
    if (!m) return;
    const img = document.getElementById('photoViewerImage');
    const title = document.getElementById('photoViewerTitle');
    const desc = document.getElementById('photoViewerCaption');

    if (img) img.src = url;
    if (title) title.textContent = pointName || 'Evidencia Fotográfica';
    if (desc) desc.textContent = caption || 'Registro fotográfico perimetral del sonómetro';

    m.classList.add('open');
};

window.closePhotoViewerModal = function () {
    const m = document.getElementById('photoViewerModal');
    if (m) m.classList.remove('open');
};

/* ==========================================================================
   7. MODAL EXPORTAR & TABLAS TÉCNICAS
   ========================================================================== */
window.openExportModal = function () {
    console.log('[Ruido Ocupacional] 🚀 openExportModal() disparado desde JS principal');
    const m = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    console.log('[Ruido Ocupacional] 🔍 Modal encontrado:', m);
    if (m) {
        m.classList.add('open', 'active');
        m.style.setProperty('display', 'flex', 'important');
        console.log('[Ruido Ocupacional] ✅ Modal de exportación abierto exitosamente.');
    } else {
        console.error('[Ruido Ocupacional] ❌ No se encontró #exportOptionsModal ni #exportModal en el DOM.');
    }
};

window.closeExportModal = function () {
    console.log('[Ruido Ocupacional] 🚪 closeExportModal() disparado desde JS principal');
    const m = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (m) {
        m.classList.remove('open', 'active');
        m.style.setProperty('display', 'none', 'important');
    }
};

window.openruidoOcupacionalTablesModal = function () {
    const m = document.getElementById('ruidoOcupacionalTablesModal');
    if (m) m.classList.add('open');
};

window.closeruidoOcupacionalTablesModal = function () {
    const m = document.getElementById('ruidoOcupacionalTablesModal');
    if (m) m.classList.remove('open');
};

window.openPhotoReportModal = function () {
    const m = document.getElementById('photoReportModal');
    if (m) m.classList.add('open');
};

window.closePhotoReportModal = function () {
    const m = document.getElementById('photoReportModal');
    if (m) m.classList.remove('open');
};

window.openTechHeaderEditModal = function () {
    const m = document.getElementById('techHeaderEditModal');
    if (m) m.classList.add('open');
};


    /**
     * Descarga reporte de Ruido Ocupacional en formato Word (.docx)
     */
    window.downloadWordReport = function (measurementId) {
        const measurement = ALL_MEASUREMENTS_DATA.find(m => m.id === measurementId);
        if (!measurement) {
            alert('Medición no encontrada');
            return;
        }

        // URL para descargar el documento Word
        const downloadUrl = `/modulos/${MODULE_ID}/ruido-ocupacional/mediciones/${measurementId}/download-word`;

        // Crear un elemento <a> temporal para descargar
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `Ruido_Ocupacional_P${measurement.num}.docx`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        console.log('Descarga iniciada:', link.download);
    };


    window.closeTechHeaderEditModal = function () {
        const m = document.getElementById('techHeaderEditModal');
        if (m) m.classList.remove('open');
    };

    /* ==========================================================================
       8. FILTROS EN VIVO Y BÚSQUEDA EN TABLA MAESTRA
       ========================================================================== */
    window.filterruidoOcupacional = function (type, btn) {
        currentFilterType = type;
        document.querySelectorAll('#ruidoFilterGroup .filter-pill-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        applyTableFilters();
    };

    window.searchRuidoOcupacionalLive = function () {
        const inp = document.getElementById('ruidoSearchInput');
        currentSearchQuery = (inp?.value || '').toLowerCase().trim();
        applyTableFilters();
    };

    function applyTableFilters() {
        const rows = document.querySelectorAll('#ruidoTableBody .ruido-data-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const rowNormativa = (row.getAttribute('data-normativa') || '').toLowerCase();
            const rowCompliant = row.getAttribute('data-compliant');
            const rowSearch = (row.getAttribute('data-search') || '').toLowerCase();

            let matchesFilter = true;
            if (currentFilterType === 'compliant') matchesFilter = (rowCompliant === 'compliant');
            else if (currentFilterType === 'non-compliant') matchesFilter = (rowCompliant === 'non-compliant');
            else if (currentFilterType === 'rasim') matchesFilter = rowNormativa.includes('rasim');
            else if (currentFilterType === 'rmca') matchesFilter = rowNormativa.includes('rmca');

            const matchesSearch = !currentSearchQuery || rowSearch.includes(currentSearchQuery);

            if (matchesFilter && matchesSearch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noRes = document.getElementById('noResultsRow');
        if (noRes) {
            noRes.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
        }
    }

    /* ==========================================================================
       9. AUTO-GUARDADO DE ENCABEZADO TÉCNICO INLINE
       ========================================================================== */
    let autoSaveTimer = null;
    async function autoSaveHeaderField() {
        const badge = document.getElementById('headerAutoSaveBadge');
        if (badge) {
            badge.classList.add('visible', 'saving');
            badge.querySelector('span').textContent = 'Guardando...';
        }

        clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(async () => {
            const payload = {
                installation_name: document.getElementById('inline_installation_name')?.value || '',
                start_date: document.getElementById('inline_start_date')?.value || null,
                end_date: document.getElementById('inline_end_date')?.value || null,
                monitoring_type: document.getElementById('inline_monitoring_type')?.value || ''
            };

            try {
                const res = await fetch(UPDATE_HEADER_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });
                if (res.ok && badge) {
                    badge.classList.remove('saving');
                    badge.querySelector('span').textContent = 'Guardado';
                    setTimeout(() => {
                        badge.classList.remove('visible');
                    }, 2500);
                }
            } catch (e) {
                console.error('Error auto-guardando encabezado:', e);
                if (badge) {
                    badge.querySelector('span').textContent = 'Error';
                }
            }
        }, 600);
    }
    window.autoSaveHeaderField = autoSaveHeaderField;

    /* ==========================================================================
       VISOR DE FOTOGRAFÍAS (Estilo Iluminación)
       ========================================================================== */
    window.openPhotoViewer = function(url, title, caption) {
        const modal = document.getElementById('photoViewerModal');
        const img = document.getElementById('photoViewerImg') || document.getElementById('photoViewerImage');
        const t = document.getElementById('photoViewerTitle');
        const c = document.getElementById('photoViewerCaption');
        if (img) img.src = url || '';
        if (t) t.textContent = title || 'Fotografía de Medición';
        if (c) c.textContent = caption || 'Registro de sonómetro en campo';
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('open');
        }
    };
    window.openPhotoViewerModal = window.openPhotoViewer;

    window.closePhotoViewerModal = function() {
        const modal = document.getElementById('photoViewerModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('open');
        }
    };
    window.closePhotoViewer = window.closePhotoViewerModal;

    /* Búsqueda en vivo de la tabla maestra */
    window.searchRuidoOcupacionalLive = function() {
        const input = document.getElementById('ruidoSearchInput');
        const q = input ? input.value.trim().toLowerCase() : '';
        const rows = document.querySelectorAll('#ruidoTableBody tr.ruido-data-row');
        let visible = 0;
        rows.forEach(row => {
            const text = row.getAttribute('data-search') || row.innerText.toLowerCase();
            if (!q || text.includes(q)) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });
        const noRes = document.getElementById('noResultsRow');
        if (noRes) noRes.style.display = (visible === 0) ? '' : 'none';
    };

    // Inicialización cuando carga el DOM
    document.addEventListener('DOMContentLoaded', () => {
        recalcruidoOcupacional('create');

        document.querySelectorAll('.modal-backdrop-custom').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('open', 'active');
                    modal.style.setProperty('display', 'none', 'important');
                }
            });
        });
    });

/* ==========================================================================
   MODAL DE EXPORTACIÓN & REPORTE FOTOGRÁFICO MOSAICO CARTA (RUIDO OCUPACIONAL)
   ========================================================================== */
function openExportModal() {
    console.log('[Ruido Ocupacional] 🚀 openExportModal() ejecutado');
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    console.log('[Ruido Ocupacional] 🔍 Modal encontrado:', modal);
    if (modal) {
        modal.classList.add('open', 'active');
        modal.style.setProperty('display', 'flex', 'important');
        console.log('[Ruido Ocupacional] ✅ Modal de exportación visible.');
    } else {
        console.error('[Ruido Ocupacional] ❌ No se encontró el modal #exportOptionsModal ni #exportModal.');
    }
}
window.openExportModal = openExportModal;

function closeExportModal() {
    console.log('[Ruido Ocupacional] 🚪 closeExportModal() ejecutado');
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('open', 'active');
        modal.style.setProperty('display', 'none', 'important');
    }
}
window.closeExportModal = closeExportModal;

const photoReportConfig = {
    grid: '2x3',
    selectedPoints: new Set(),
    photoIndices: {},
    globalFit: 'contain',
    pointFits: {},
    currentTab: 'interactive',
    isInitialized: false
};
let photoReportAutoSaveTimeout = null;

function openPhotoReportModal() {
    closeExportModal();
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.add('open', 'active');
        modal.style.setProperty('display', 'flex', 'important');
        initPhotoReportEngine();
    }
}
window.openPhotoReportModal = openPhotoReportModal;
window.openPhotoReportConfigModal = openPhotoReportModal;

function closePhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.remove('open', 'active');
        modal.style.setProperty('display', 'none', 'important');
    }
}
window.closePhotoReportModal = closePhotoReportModal;
window.closePhotoReportConfigModal = closePhotoReportModal;

function initPhotoReportEngine() {
    if (!photoReportConfig.isInitialized) {
        let saved = null;
        if (typeof window.PHOTO_REPORT_INITIAL_SETTINGS === 'object' && window.PHOTO_REPORT_INITIAL_SETTINGS && Object.keys(window.PHOTO_REPORT_INITIAL_SETTINGS).length > 0) {
            saved = window.PHOTO_REPORT_INITIAL_SETTINGS;
        }
        if (!saved) {
            try {
                const local = localStorage.getItem('metric_ruido_ocupacional_photo_' + (window.MODULE_ID || '1'));
                if (local) saved = JSON.parse(local);
            } catch (e) {
                console.warn('Error reading localStorage', e);
            }
        }

        const validGrids = ['2x3', '2x4', '3x3', '3x4'];
        if (saved && saved.grid && validGrids.includes(saved.grid)) {
            photoReportConfig.grid = saved.grid;
        } else {
            photoReportConfig.grid = '2x3';
        }

        photoReportConfig.selectedPoints.clear();
        const allItems = window.ALL_MEASUREMENTS_DATA || [];
        if (saved && Array.isArray(saved.selected_points) && saved.selected_points.length > 0) {
            saved.selected_points.forEach(id => photoReportConfig.selectedPoints.add(Number(id)));
        } else {
            allItems.forEach(m => photoReportConfig.selectedPoints.add(Number(m.id)));
        }

        photoReportConfig.photoIndices = {};
        if (saved && saved.photo_indices && typeof saved.photo_indices === 'object') {
            for (const [k, v] of Object.entries(saved.photo_indices)) {
                photoReportConfig.photoIndices[Number(k)] = Number(v) || 0;
            }
        }

        if (saved) {
            if (saved.image_fit === 'cover' || saved.global_fit === 'cover') {
                photoReportConfig.globalFit = 'cover';
            } else {
                photoReportConfig.globalFit = 'contain';
            }

            photoReportConfig.pointFits = {};
            const fits = saved.photo_fits || saved.point_fits;
            if (fits && typeof fits === 'object') {
                for (const [k, v] of Object.entries(fits)) {
                    if (v === 'cover' || v === 'contain') {
                        photoReportConfig.pointFits[Number(k)] = v;
                    }
                }
            }
        }

        photoReportConfig.isInitialized = true;
    }

    updateGridSelectorButtonsUI();
    updateGlobalFitButtonsUI();
    renderInteractiveGrid();
    renderPhotoSheets();
    updateSelectionCounterUI();
}
window.initPhotoReportEngine = initPhotoReportEngine;

function getPointImages(m) {
    if (Array.isArray(m.images) && m.images.length > 0) {
        return m.images.filter(Boolean);
    }
    if (Array.isArray(m.image_paths) && m.image_paths.length > 0) {
        return m.image_paths.filter(Boolean);
    }
    if (m.image_path) {
        return [m.image_path];
    }
    return [];
}

function formatPointCode(num) {
    if (!num) return 'RO-01';
    const str = String(num).trim();
    if (str.toUpperCase().startsWith('RO-') || str.toUpperCase().startsWith('P-') || str.toUpperCase().startsWith('R-')) {
        return str.toUpperCase();
    }
    const numericPart = str.replace(/[^0-9]/g, '');
    if (numericPart) {
        return 'RO-' + numericPart.padStart(2, '0');
    }
    return '#' + str;
}

function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function addslashes(str) {
    return (str || '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

function getPhotosPerPage(grid) {
    switch (grid) {
        case '2x4': return 8;
        case '3x3': return 9;
        case '3x4': return 12;
        case '2x3':
        default: return 6;
    }
}

function changeGridDistribution(newGrid) {
    const validGrids = ['2x3', '2x4', '3x3', '3x4'];
    if (!validGrids.includes(newGrid)) return;

    photoReportConfig.grid = newGrid;
    updateGridSelectorButtonsUI();
    renderPhotoSheets();
    updateSelectionCounterUI();
    triggerAutoSavePhotoReportConfig();
}
window.changeGridDistribution = changeGridDistribution;
window.changeRuidoMosaicGrid = changeGridDistribution;

function updateGridSelectorButtonsUI() {
    const buttons = document.querySelectorAll('#gridDistSelector .btn-grid-dist');
    buttons.forEach(btn => {
        if (btn.dataset.grid === photoReportConfig.grid) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}

function getPointImageFit(pointId) {
    pointId = Number(pointId);
    if (photoReportConfig.pointFits && (photoReportConfig.pointFits[pointId] === 'cover' || photoReportConfig.pointFits[pointId] === 'contain')) {
        return photoReportConfig.pointFits[pointId];
    }
    return photoReportConfig.globalFit || 'contain';
}
window.getPointImageFit = getPointImageFit;

function updateGlobalFitButtonsUI() {
    const buttons = document.querySelectorAll('#photoGlobalFitSelector .btn-photo-fit-global');
    buttons.forEach(btn => {
        if (btn.dataset.fit === (photoReportConfig.globalFit || 'contain')) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
}
window.updateGlobalFitButtonsUI = updateGlobalFitButtonsUI;

function setGlobalImageFit(fit) {
    if (fit !== 'contain' && fit !== 'cover') return;
    photoReportConfig.globalFit = fit;
    photoReportConfig.pointFits = {};
    updateGlobalFitButtonsUI();
    renderInteractiveGrid();
    renderPhotoSheets();
    triggerAutoSavePhotoReportConfig();
}
window.setGlobalImageFit = setGlobalImageFit;

function setPointImageFit(pointId, fit, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    pointId = Number(pointId);
    if (fit !== 'contain' && fit !== 'cover') return;

    photoReportConfig.pointFits[pointId] = fit;

    const card = document.getElementById('photoPointCard_' + pointId);
    if (card) {
        const imgBox = card.querySelector('.photo-card-img-box');
        if (imgBox) {
            imgBox.classList.remove('fit-contain', 'fit-cover');
            imgBox.classList.add('fit-' + fit);
        }
        const btns = card.querySelectorAll('.btn-card-fit');
        btns.forEach(b => {
            if (b.dataset.fit === fit) {
                b.classList.add('active');
            } else {
                b.classList.remove('active');
            }
        });
    }

    renderPhotoSheets();
    triggerAutoSavePhotoReportConfig();
}
window.setPointImageFit = setPointImageFit;

function togglePointSelection(pointId, isChecked) {
    pointId = Number(pointId);
    if (isChecked) {
        photoReportConfig.selectedPoints.add(pointId);
    } else {
        photoReportConfig.selectedPoints.delete(pointId);
    }

    const card = document.getElementById('photoPointCard_' + pointId);
    if (card) {
        if (isChecked) {
            card.classList.remove('excluded');
        } else {
            card.classList.add('excluded');
        }
    }

    updateSelectionCounterUI();
    renderPhotoSheets();
    triggerAutoSavePhotoReportConfig();
}
window.togglePointSelection = togglePointSelection;

function selectAllPoints(select) {
    const allItems = window.ALL_MEASUREMENTS_DATA || [];
    allItems.forEach(m => {
        const id = Number(m.id);
        const chk = document.getElementById('chkPoint_' + id);
        const card = document.getElementById('photoPointCard_' + id);
        if (select) {
            photoReportConfig.selectedPoints.add(id);
            if (chk) chk.checked = true;
            if (card) card.classList.remove('excluded');
        } else {
            photoReportConfig.selectedPoints.delete(id);
            if (chk) chk.checked = false;
            if (card) card.classList.add('excluded');
        }
    });

    updateSelectionCounterUI();
    renderPhotoSheets();
    triggerAutoSavePhotoReportConfig();
}
window.selectAllPoints = selectAllPoints;

function updateSelectionCounterUI() {
    const count = photoReportConfig.selectedPoints.size;
    const total = (window.ALL_MEASUREMENTS_DATA || []).length;
    const textEl = document.getElementById('photoSelectionCountText');
    if (textEl) {
        textEl.textContent = `${count} de ${total} seleccionadas`;
    }

    const perPage = getPhotosPerPage(photoReportConfig.grid);
    const totalPages = Math.ceil(count / perPage) || 0;
    const pagesInd = document.getElementById('photoReportPagesIndicator');
    if (pagesInd) {
        pagesInd.textContent = `Hojas Carta: ${totalPages} (${photoReportConfig.grid})`;
    }
}

function navigatePointPhoto(pointId, delta, event) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    pointId = Number(pointId);
    const allItems = window.ALL_MEASUREMENTS_DATA || [];
    const item = allItems.find(m => Number(m.id) === pointId);
    if (!item) return;

    const images = getPointImages(item);
    if (images.length <= 1) return;

    const curr = photoReportConfig.photoIndices[pointId] || 0;
    const next = (curr + delta + images.length) % images.length;
    photoReportConfig.photoIndices[pointId] = next;

    const imgEl = document.getElementById('photoImg_' + pointId);
    if (imgEl) {
        imgEl.src = images[next];
    }
    const countEl = document.getElementById('photoCountPill_' + pointId);
    if (countEl) {
        countEl.textContent = `Foto ${next + 1} de ${images.length}`;
    }

    renderPhotoSheets();
    triggerAutoSavePhotoReportConfig();
}
window.navigatePointPhoto = navigatePointPhoto;

function switchPhotoReportTab(tab) {
    photoReportConfig.currentTab = tab;
    const tabBtnInteractive = document.getElementById('tabBtnInteractive');
    const tabBtnSheets = document.getElementById('tabBtnSheets');
    const interactiveView = document.getElementById('photoInteractiveView');
    const sheetsView = document.getElementById('photoSheetsView');

    if (tab === 'sheets') {
        if (tabBtnInteractive) tabBtnInteractive.classList.remove('active');
        if (tabBtnSheets) tabBtnSheets.classList.add('active');
        if (interactiveView) interactiveView.style.display = 'none';
        if (sheetsView) {
            sheetsView.style.display = 'block';
            renderPhotoSheets();
        }
    } else {
        if (tabBtnInteractive) tabBtnInteractive.classList.add('active');
        if (tabBtnSheets) tabBtnSheets.classList.remove('active');
        if (interactiveView) interactiveView.style.display = 'block';
        if (sheetsView) sheetsView.style.display = 'none';
    }
}
window.switchPhotoReportTab = switchPhotoReportTab;

function renderInteractiveGrid() {
    const container = document.getElementById('photoInteractiveGridContainer');
    if (!container) return;

    const allItems = window.ALL_MEASUREMENTS_DATA || [];
    if (allItems.length === 0) {
        container.innerHTML = `
            <div style="grid-column: 1 / -1; text-align: center; padding: 45px 20px; color: #94a3b8; background: #ffffff; border-radius: 12px; border: 1.5px dashed #cbd5e1;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 8px;"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                <p style="margin: 0; font-size: 13.5px; font-weight: 600;">No hay mediciones de ruido ocupacional registradas para generar el reporte fotográfico.</p>
            </div>
        `;
        return;
    }

    let html = '';
    allItems.forEach(m => {
        const pointId = Number(m.id);
        const images = getPointImages(m);
        const selectedIdx = photoReportConfig.photoIndices[pointId] || 0;
        const currentImg = images[selectedIdx] || images[0] || '';
        const isSelected = photoReportConfig.selectedPoints.has(pointId);
        const codeFormatted = formatPointCode(m.num || m.id);
        const hasMultiplePhotos = images.length > 1;

        const puesto = m.punto_medicion || 'Punto de Medición';
        const area = m.area || 'Área General';
        const tipoRuido = m.tipo_ruido || 'Continuo';
        const tpe = m.tiempo_expos_h !== null && m.tiempo_expos_h !== undefined ? m.tiempo_expos_h + ' h' : '8.0 h';

        const leqVal = (m.leq_t_db !== null && m.leq_t_db !== undefined && !isNaN(m.leq_t_db)) ? Number(m.leq_t_db).toFixed(1) : '—';
        let leqBadgeColor = '#059669';
        let leqBadgeBg = '#ecfdf5';
        let evalText = 'Cumple LMP (≤ 85 dBA)';

        if (leqVal !== '—' && Number(leqVal) > 85.0) {
            leqBadgeColor = '#dc2626';
            leqBadgeBg = '#fef2f2';
            evalText = 'Supera LMP (> 85 dBA)';
        }

        const pointFit = getPointImageFit(pointId);

        html += `
            <div class="photo-point-card ${isSelected ? '' : 'excluded'}" id="photoPointCard_${pointId}">
                <div class="photo-card-select-bar">
                    <label class="photo-select-checkbox-lbl" title="Marcar para incluir en el reporte PDF">
                        <input type="checkbox" id="chkPoint_${pointId}" ${isSelected ? 'checked' : ''} onchange="togglePointSelection(${pointId}, this.checked)">
                        <span>Incluir en PDF</span>
                    </label>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <div class="photo-card-fit-toggle" title="Ajuste de foto para este punto">
                            <button type="button" class="btn-card-fit ${pointFit === 'contain' ? 'active' : ''}" data-fit="contain" onclick="setPointImageFit(${pointId}, 'contain', event)" title="Contener: Foto completa sin recortar">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2" stroke-dasharray="2 2"/><rect width="10" height="10" x="7" y="7" rx="1"/></svg>
                                <span>Contener</span>
                            </button>
                            <button type="button" class="btn-card-fit ${pointFit === 'cover' ? 'active' : ''}" data-fit="cover" onclick="setPointImageFit(${pointId}, 'cover', event)" title="Expandir: Llenar el recuadro sin distorsionar">
                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                <span>Expandir</span>
                            </button>
                        </div>
                        <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;">
                            ${codeFormatted}
                        </span>
                    </div>
                </div>

                <div class="photo-card-info-top">
                    <div class="photo-info-row">
                        <span class="photo-info-label">Puesto:</span>
                        <span class="photo-info-value" title="${escapeHtml(puesto)}" style="font-weight: 800; color: #0f172a;">${escapeHtml(puesto)}</span>
                    </div>
                    <div class="photo-info-row">
                        <span class="photo-info-label">Área:</span>
                        <span class="photo-info-value" title="${escapeHtml(area)}">${escapeHtml(area)}</span>
                    </div>
                    <div class="photo-info-row" style="margin-top: 3px; align-items: center; gap: 6px;">
                        <span style="font-size: 10px; font-weight: 800; color: ${leqBadgeColor}; background: ${leqBadgeBg}; padding: 2px 7px; border-radius: 4px;">
                            Leq: ${leqVal} dBA • ${evalText}
                        </span>
                    </div>
                    <div class="photo-info-row" style="margin-top: 2px;">
                        <span class="photo-info-label">Tipo:</span>
                        <span class="photo-info-value" style="font-size: 10.5px; color: #475569;">${escapeHtml(tipoRuido)} (Exp: ${tpe})</span>
                    </div>
                </div>

                <div class="photo-card-img-box fit-${pointFit}" id="photoCardImgBox_${pointId}">
                    ${currentImg ? `
                        <img id="photoImg_${pointId}" src="${currentImg}" alt="${codeFormatted}" onclick="openPhotoViewer('${currentImg}', '${codeFormatted}: ${addslashes(puesto)}')" title="Clic para ampliar fotografía">
                        <button type="button" class="photo-zoom-btn" onclick="openPhotoViewer('${currentImg}', '${codeFormatted}: ${addslashes(puesto)}')" title="Ampliar">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        </button>
                    ` : `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #64748b;">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            <span style="font-size: 11px; margin-top: 4px; font-weight: 600;">Sin fotografía</span>
                        </div>
                    `}

                    ${hasMultiplePhotos ? `
                        <button type="button" class="photo-nav-arrow left" onclick="navigatePointPhoto(${pointId}, -1, event)" title="Foto anterior">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button type="button" class="photo-nav-arrow right" onclick="navigatePointPhoto(${pointId}, 1, event)" title="Siguiente foto">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <div class="photo-count-pill" id="photoCountPill_${pointId}">
                            Foto ${selectedIdx + 1} de ${images.length}
                        </div>
                    ` : (images.length === 1 ? `
                        <div class="photo-count-pill">Foto 1 de 1</div>
                    ` : '')}
                </div>

                <div class="photo-card-info-bottom">
                    <span class="photo-dt-label">
                        <svg class="photo-dt-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        ${escapeHtml(m.date || m.date_formatted || '—')} ${escapeHtml(m.time && m.time !== '—' ? m.time : '')}
                    </span>
                    <span class="photo-dt-pill" title="${escapeHtml(m.registered_by || '')}">Téc: ${escapeHtml(m.registered_by || 'Especialista')}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}
window.renderPhotoInteractiveGrid = renderInteractiveGrid;

function renderPhotoSheets() {
    const sheetsContainer = document.getElementById('photoSheetsContainer');
    const printContainer = document.getElementById('photoReportPrintArea');

    const allItems = window.ALL_MEASUREMENTS_DATA || [];
    const tech = window.TECHNICAL_HEADER_DATA || {};
    const selectedItems = allItems.filter(m => photoReportConfig.selectedPoints.has(Number(m.id)));
    const perPage = getPhotosPerPage(photoReportConfig.grid);

    if (selectedItems.length === 0) {
        const emptyHtml = `
            <div style="text-align: center; padding: 50px 20px; color: #94a3b8; background: #ffffff; border-radius: 10px; border: 1.5px dashed #cbd5e1; max-width: 600px; margin: 30px auto;">
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 8px;"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                <h3 style="font-size: 15px; margin: 0 0 6px 0; color: #334155;">No hay fotografías seleccionadas para el PDF</h3>
                <p style="margin: 0; font-size: 12.5px;">Activa las casillas "Incluir en PDF" o haz clic en "Todas" para armar las hojas del reporte.</p>
            </div>
        `;
        if (sheetsContainer) sheetsContainer.innerHTML = emptyHtml;
        if (printContainer) printContainer.innerHTML = '';
        return;
    }

    const pages = [];
    for (let i = 0; i < selectedItems.length; i += perPage) {
        pages.push(selectedItems.slice(i, i + perPage));
    }

    const totalPages = pages.length;
    const now = new Date();
    const dateEmissionFormatted = now.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + now.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });

    let sheetsHtml = '';

    pages.forEach((pageItems, pageIdx) => {
        const pageNum = pageIdx + 1;

        sheetsHtml += `
            <div class="photo-report-sheet">
                <div class="sheet-header-box">
                    <div class="sheet-header-left">
                        <div class="sheet-header-logo-badge">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
                            </svg>
                        </div>
                        <div class="sheet-header-titles">
                            <div class="sheet-header-brand-row">
                                <span class="sheet-company-title">PACHABOL S.R.L.</span>
                                <span class="sheet-brand-dot">•</span>
                                <span class="sheet-system-tag">Higiene &amp; Seguridad Ocupacional</span>
                            </div>
                            <h1>REPORTE FOTOGRÁFICO — RUIDO OCUPACIONAL</h1>
                            <div class="sheet-header-subtitle-row">
                                <span class="sheet-tech-tag">NORMATIVA NTS-002/17 • R.M. 343/18 • LEY GENERAL DE HIGIENE Y SALUD OCUPACIONAL</span>
                            </div>
                        </div>
                    </div>

                    <div class="sheet-header-meta-card">
                        <div class="sheet-meta-card-header">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <span>FICHA TÉCNICA DE MONITOREO</span>
                        </div>
                        <div class="sheet-meta-grid">
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">INSTALACIÓN:</span>
                                <span class="sheet-meta-val" title="${escapeHtml(tech.installationName || '—')}">${escapeHtml(tech.installationName || '—')}</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">TÉCNICO:</span>
                                <span class="sheet-meta-val" title="${escapeHtml(tech.registeredByHeader || window.REGISTERED_BY_HEADER || '—')}">${escapeHtml(tech.registeredByHeader || window.REGISTERED_BY_HEADER || '—')}</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">PERÍODO:</span>
                                <span class="sheet-meta-val">${escapeHtml(tech.startDateFormatted || '—')}${tech.endDateFormatted && tech.endDateFormatted !== tech.startDateFormatted ? ' al ' + escapeHtml(tech.endDateFormatted) : ''}</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">EQUIPO:</span>
                                <span class="sheet-meta-val" style="color: #38bdf8; font-weight: 800;">${escapeHtml(tech.equipmentName || 'Sonómetro Clase 1')}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sheet-photos-grid dist-${photoReportConfig.grid}">
                    ${pageItems.map(m => {
                        const pointId = Number(m.id);
                        const images = getPointImages(m);
                        const selectedIdx = photoReportConfig.photoIndices[pointId] || 0;
                        const currentImg = images[selectedIdx] || images[0] || '';
                        const codeFormatted = formatPointCode(m.num || m.id);
                        const puesto = m.punto_medicion || 'Punto de Medición';
                        const area = m.area || 'Área General';
                        const tipoRuido = m.tipo_ruido || 'Continuo';
                        const leqVal = (m.leq_t_db !== null && m.leq_t_db !== undefined && !isNaN(m.leq_t_db)) ? Number(m.leq_t_db).toFixed(1) : '—';
                        const evalColor = (leqVal !== '—' && Number(leqVal) > 85.0) ? '#dc2626' : '#059669';

                        const pointFit = getPointImageFit(pointId);

                        return `
                            <div class="sheet-card-item">
                                <div class="photo-card-info-top">
                                    <div class="photo-info-row" style="justify-content: space-between;">
                                        <span class="photo-code">${codeFormatted}</span>
                                        <span style="font-weight: 800; font-size: 8px; color: ${evalColor};">Leq: ${leqVal} dBA</span>
                                    </div>
                                    <div class="photo-info-row" style="margin-top: 1px;">
                                        <span class="photo-info-label">PUESTO:</span>
                                        <span class="photo-info-value" style="font-weight: 800;" title="${escapeHtml(puesto)}">${escapeHtml(puesto)}</span>
                                    </div>
                                    <div class="photo-info-row">
                                        <span class="photo-info-label">ÁREA:</span>
                                        <span class="photo-info-value" title="${escapeHtml(area)}">${escapeHtml(area)}</span>
                                    </div>
                                    <div class="photo-info-row">
                                        <span class="photo-info-label">TIPO:</span>
                                        <span class="photo-info-value" style="font-size: 7.5px; color: #64748b;">${escapeHtml(tipoRuido)}</span>
                                    </div>
                                </div>

                                <div class="photo-card-img-box fit-${pointFit}">
                                    ${currentImg ? `
                                        <img src="${currentImg}" alt="${codeFormatted}" crossorigin="anonymous">
                                    ` : `
                                        <div style="color: #64748b; font-size: 8.5px; font-weight: 700; text-align: center; padding: 10px;">
                                             Sin fotografía
                                        </div>
                                    `}
                                </div>

                                <div class="photo-card-info-bottom">
                                    <span class="photo-dt-label">
                                        <svg class="photo-dt-icon" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        ${escapeHtml(m.date || m.date_formatted || '—')} ${escapeHtml(m.time && m.time !== '—' ? m.time : '')}
                                    </span>
                                    <span class="photo-dt-pill">${escapeHtml(m.registered_by || 'Especialista')}</span>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>

                <div class="sheet-footer-box">
                    <div class="sheet-footer-brand">
                        <span>PACHABOL S.R.L.</span>
                        <span style="color: #cbd5e1;">|</span>
                        <span>SISTEMA METRIC</span>
                        <span style="color: #cbd5e1;">|</span>
                        <span>HOJA TAMAÑO CARTA</span>
                    </div>
                    <div>
                        <span>Emisión: ${dateEmissionFormatted}</span>
                    </div>
                    <div>
                        <span class="sheet-footer-page-num">PÁGINA ${pageNum} DE ${totalPages}</span>
                    </div>
                </div>
            </div>
        `;
    });

    if (sheetsContainer) sheetsContainer.innerHTML = sheetsHtml;
    if (printContainer) printContainer.innerHTML = sheetsHtml;
}
window.renderPhotoSheets = renderPhotoSheets;

function triggerAutoSavePhotoReportConfig() {
    clearTimeout(photoReportAutoSaveTimeout);
    photoReportAutoSaveTimeout = setTimeout(() => {
        savePhotoReportSettingsToServer(false);
    }, 1500);
}

async function savePhotoReportSettingsToServer(showToast = false) {
    const btnSave = document.getElementById('btnSaveReportSettings');
    if (btnSave && showToast) {
        btnSave.disabled = true;
        btnSave.innerHTML = `
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="animation: spin 0.8s linear infinite;">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
            </svg>
            <span>Guardando...</span>
        `;
    }

    const payload = {
        grid: photoReportConfig.grid,
        selected_points: Array.from(photoReportConfig.selectedPoints),
        photo_indices: photoReportConfig.photoIndices,
        image_fit: photoReportConfig.globalFit,
        photo_fits: photoReportConfig.pointFits
    };

    try {
        localStorage.setItem('metric_ruido_ocupacional_photo_' + (window.MODULE_ID || '1'), JSON.stringify(payload));
    } catch (e) {
        console.warn('Error saving to localStorage', e);
    }

    const saveUrl = (window.METRIC_RUIDO_OCUPACIONAL_CONFIG && window.METRIC_RUIDO_OCUPACIONAL_CONFIG.savePhotoReportSettingsUrl)
        || `/modulos/${window.MODULE_ID}/ruido-ocupacional/photo-report-settings`;

    try {
        const res = await fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': window.CSRF_TOKEN || (window.METRIC_RUIDO_OCUPACIONAL_CONFIG && window.METRIC_RUIDO_OCUPACIONAL_CONFIG.csrfToken)
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (showToast) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Configuración Guardada!',
                    text: `Distribución (${photoReportConfig.grid}), puntos seleccionados y fotos preferidas almacenados exitosamente.`,
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    timer: 2500,
                    timerProgressBar: true,
                    buttonsStyling: false,
                    customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                });
            }
        }
    } catch (err) {
        console.error('Error guardando configuración de reporte fotográfico:', err);
        if (showToast) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Guardado local',
                    text: 'La configuración se guardó en tu navegador. Puedes continuar con la exportación.',
                    icon: 'info',
                    confirmButtonText: 'Aceptar',
                    timer: 2500,
                    buttonsStyling: false,
                    customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                });
            }
        }
    } finally {
        if (btnSave && showToast) {
            btnSave.disabled = false;
            btnSave.innerHTML = `
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span>Guardar Selección</span>
            `;
        }
    }
}
window.savePhotoReportSettingsToServer = savePhotoReportSettingsToServer;

function fixSheetImagesForCanvas(sheet) {
    const revertList = [];
    const boxes = sheet.querySelectorAll('.sheet-card-item .photo-card-img-box');

    boxes.forEach(box => {
        const img = box.querySelector('img');
        if (!img) return;

        const nw = img.naturalWidth;
        const nh = img.naturalHeight;
        if (!nw || !nh) return;

        const isCover = box.classList.contains('fit-cover');
        const boxRect = box.getBoundingClientRect();
        const boxWidth = boxRect.width || box.clientWidth;
        const boxHeight = boxRect.height || box.clientHeight;
        if (boxWidth <= 0 || boxHeight <= 0) return;

        const imgRatio = nw / nh;
        const boxRatio = boxWidth / boxHeight;

        let scaledWidth = boxWidth;
        let scaledHeight = boxHeight;
        let offsetX = 0;
        let offsetY = 0;

        if (isCover) {
            // Expandir / Cover: Fill container completely without distortion
            if (imgRatio > boxRatio) {
                scaledHeight = boxHeight;
                scaledWidth = boxHeight * imgRatio;
                offsetX = (boxWidth - scaledWidth) / 2;
                offsetY = 0;
            } else {
                scaledWidth = boxWidth;
                scaledHeight = boxWidth / imgRatio;
                offsetX = 0;
                offsetY = (boxHeight - scaledHeight) / 2;
            }
        } else {
            // Contener / Contain: Fit entirely inside container without distortion
            if (imgRatio > boxRatio) {
                scaledWidth = boxWidth;
                scaledHeight = boxWidth / imgRatio;
                offsetX = 0;
                offsetY = (boxHeight - scaledHeight) / 2;
            } else {
                scaledHeight = boxHeight;
                scaledWidth = boxHeight * imgRatio;
                offsetX = (boxWidth - scaledWidth) / 2;
                offsetY = 0;
            }
        }

        const prevBoxStyle = box.getAttribute('style') || '';
        const prevImgStyle = img.getAttribute('style') || '';

        box.style.setProperty('position', 'relative', 'important');
        box.style.setProperty('overflow', 'hidden', 'important');

        img.style.setProperty('position', 'absolute', 'important');
        img.style.setProperty('left', `${offsetX}px`, 'important');
        img.style.setProperty('top', `${offsetY}px`, 'important');
        img.style.setProperty('width', `${scaledWidth}px`, 'important');
        img.style.setProperty('height', `${scaledHeight}px`, 'important');
        img.style.setProperty('max-width', 'none', 'important');
        img.style.setProperty('max-height', 'none', 'important');
        img.style.setProperty('min-width', '0', 'important');
        img.style.setProperty('min-height', '0', 'important');
        img.style.setProperty('object-fit', 'fill', 'important');
        img.style.setProperty('margin', '0', 'important');
        img.style.setProperty('display', 'block', 'important');

        revertList.push(() => {
            if (prevBoxStyle) {
                box.setAttribute('style', prevBoxStyle);
            } else {
                box.removeAttribute('style');
            }
            if (prevImgStyle) {
                img.setAttribute('style', prevImgStyle);
            } else {
                img.removeAttribute('style');
            }
        });
    });

    return function restoreAll() {
        revertList.forEach(fn => fn());
    };
}

async function downloadPhotoReportPDF() {
    if (!photoReportConfig.selectedPoints || photoReportConfig.selectedPoints.size === 0) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Sin selección',
                text: 'Debes seleccionar al menos una fotografía para generar y descargar el reporte en PDF.',
                icon: 'warning',
                confirmButtonText: 'Entendido',
                buttonsStyling: false,
                customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
            });
        } else {
            alert('Debes seleccionar al menos una fotografía para descargar el PDF.');
        }
        return;
    }

    const btnDownload = document.getElementById('btnDownloadPhotoReportPdf');
    const origBtnHtml = btnDownload ? btnDownload.innerHTML : '';

    try {
        if (btnDownload) {
            btnDownload.disabled = true;
            btnDownload.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="animation: spin 0.8s linear infinite;">
                    <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                </svg>
                <span>Generando PDF...</span>
            `;
        }

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Generando Reporte PDF',
                html: `
                    <div style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:12px 0;">
                        <span style="font-size:13px;color:#475569;">Compilando hojas tamaño carta en alta resolución...</span>
                        <span id="pdfGenProgressText" style="font-size:11.5px;font-weight:700;color:#0284c7;">Preparando páginas...</span>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                },
                customClass: { popup: 'metric-swal-popup' }
            });
        }

        switchPhotoReportTab('sheets');
        renderPhotoSheets();
        await new Promise(r => setTimeout(r, 150));

        const sheetsContainer = document.getElementById('photoSheetsContainer');
        if (!sheetsContainer) {
            throw new Error('No se encontró el contenedor de hojas del reporte fotográfico.');
        }

        const sheets = Array.from(sheetsContainer.querySelectorAll('.photo-report-sheet'));
        if (sheets.length === 0) {
            throw new Error('No hay hojas generadas para exportar en el reporte.');
        }

        const JsPDF = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
        if (!JsPDF) {
            throw new Error('La librería jsPDF no está disponible en la página.');
        }
        if (typeof html2canvas === 'undefined') {
            throw new Error('La librería html2canvas no está disponible en la página.');
        }

        const pdf = new JsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'letter',
            compress: true
        });

        const letterWidthMm = 215.9;
        const letterHeightMm = 279.4;

        for (let i = 0; i < sheets.length; i++) {
            const sheet = sheets[i];

            const progressEl = document.getElementById('pdfGenProgressText');
            if (progressEl) {
                progressEl.textContent = `Procesando hoja ${i + 1} de ${sheets.length}...`;
            }

            const sheetImages = Array.from(sheet.querySelectorAll('img'));
            await Promise.all(sheetImages.map(img => {
                if (img.complete) return Promise.resolve();
                return new Promise(resolve => {
                    img.onload = resolve;
                    img.onerror = resolve;
                });
            }));

            const prevBoxShadow = sheet.style.boxShadow;
            const prevBorderRadius = sheet.style.borderRadius;
            const prevBorder = sheet.style.border;
            const prevMargin = sheet.style.margin;

            sheet.style.boxShadow = 'none';
            sheet.style.borderRadius = '0';
            sheet.style.border = 'none';
            sheet.style.margin = '0';

            const restoreImages = fixSheetImagesForCanvas(sheet);
            let canvas;
            try {
                canvas = await html2canvas(sheet, {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    backgroundColor: '#ffffff'
                });
            } finally {
                restoreImages();
            }

            sheet.style.boxShadow = prevBoxShadow;
            sheet.style.borderRadius = prevBorderRadius;
            sheet.style.border = prevBorder;
            sheet.style.margin = prevMargin;

            const imgData = canvas.toDataURL('image/jpeg', 0.95);

            if (i > 0) {
                pdf.addPage('letter', 'portrait');
            }

            pdf.addImage(imgData, 'JPEG', 0, 0, letterWidthMm, letterHeightMm, undefined, 'FAST');
        }

        const tech = window.TECHNICAL_HEADER_DATA || {};
        const installationClean = (tech.installationName || 'Instalacion').trim().replace(/[^a-zA-Z0-9_\-]/g, '_').substring(0, 30);
        const dateStr = new Date().toISOString().slice(0, 10);
        const fileName = `Reporte_Fotografico_Ruido_Ocupacional_${installationClean}_${dateStr}.pdf`;

        pdf.save(fileName);

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Descarga Exitosa!',
                text: `El reporte fotográfico (${sheets.length} ${sheets.length === 1 ? 'hoja carta' : 'hojas carta'}) se ha descargado en formato PDF.`,
                icon: 'success',
                confirmButtonText: 'Aceptar',
                timer: 3000,
                timerProgressBar: true,
                buttonsStyling: false,
                customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
            });
        }
    } catch (err) {
        console.error('Error generando o descargando PDF:', err);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Error al exportar PDF',
                text: 'Ocurrió un error al generar el archivo PDF: ' + (err.message || 'Error desconocido'),
                icon: 'error',
                confirmButtonText: 'Aceptar',
                buttonsStyling: false,
                customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-danger' }
            });
        } else {
            alert('Error generando PDF: ' + err.message);
        }
    } finally {
        if (btnDownload && origBtnHtml) {
            btnDownload.disabled = false;
            btnDownload.innerHTML = origBtnHtml;
        }
    }
}
window.downloadPhotoReportPDF = downloadPhotoReportPDF;

function printPhotoReport() {
    downloadPhotoReportPDF();
}
window.printPhotoReport = printPhotoReport;

