/**
 * METRIC V2 — Monitoreo de Ruido Ambiental
 * Módulo JavaScript interactivo, cálculos acústicos Leq logarítmicos,
 * mapas Leaflet y controlador de modales (Vite ES Module)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_RUIDO_AMBIENTAL_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/ruido-ambiental/header');

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

window.handleNormativaChange = function(prefix) {
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
    recalcRuidoAmbiental(prefix);
};

window.handleTipoZonaChange = function(prefix) {
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
    recalcRuidoAmbiental(prefix);
};

window.addCardinalPointInput = function(prefix, cardinalKey) {
    const container = document.getElementById(`${prefix}_${cardinalKey}_points_container`);
    if (!container) return;

    const row = document.createElement('div');
    row.className = 'point-pill-input-row';
    row.innerHTML = `
        <input type="number" step="0.1" class="custom-form-input cardinal-point-val" placeholder="ej. 62.5" oninput="recalcRuidoAmbiental('${prefix}')">
        <button type="button" class="btn-icon-del-point" onclick="this.closest('.point-pill-input-row').remove(); recalcRuidoAmbiental('${prefix}');" title="Eliminar medición">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M18 6L6 18M6 6l12 12"/></svg>
        </button>
    `;
    container.appendChild(row);
    recalcRuidoAmbiental(prefix);
};

window.recalcRuidoAmbiental = function(prefix) {
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

window.captureCoordinatesGPS = function(prefix) {
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
   3. MODALES DE RUIDO AMBIENTAL
   ========================================================================== */
window.openCreateMeasurementModal = function() {
    const m = document.getElementById('createMeasurementModal');
    if (m) m.classList.add('open');
};

window.closeCreateMeasurementModal = function() {
    const m = document.getElementById('createMeasurementModal');
    if (m) m.classList.remove('open');
};

window.openEditMeasurementModal = function(item) {
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
                <input type="number" step="0.1" class="custom-form-input cardinal-point-val" value="${val}" oninput="recalcRuidoAmbiental('edit')">
                <button type="button" class="btn-icon-del-point" onclick="this.closest('.point-pill-input-row').remove(); recalcRuidoAmbiental('edit');">
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

    recalcRuidoAmbiental('edit');
    m.classList.add('open');
};

window.closeEditMeasurementModal = function() {
    const m = document.getElementById('editMeasurementModal');
    if (m) m.classList.remove('open');
};

window.confirmDeleteMeasurement = function(id, pointNum) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar medición?',
            text: `Se eliminará el punto ${pointNum} de Ruido Ambiental. Esta acción no se puede deshacer.`,
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

/* ==========================================================================
   4. MODAL MAPA GENERAL DE TODAS LAS UBICACIONES
   ========================================================================== */
window.openAllLocationsModal = function() {
    const m = document.getElementById('allLocationsModal');
    if (m) {
        m.classList.add('open');
        setTimeout(initAllLocationsMap, 200);
    }
};

window.closeAllLocationsModal = function() {
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
            const isOk = item.is_compliant;
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
                    <div style="font-weight: 800; font-size: 13px; color: #0f172a; margin-bottom: 2px;">#${item.num} — Ruido Ambiental</div>
                    <div style="font-size: 11.5px; color: #64748b;">${item.normativa} • ${item.tipo_zona}</div>
                    <div style="margin-top: 5px; font-weight: 700; color: ${isOk ? '#059669' : '#dc2626'}; font-size: 12px;">Leq: ${item.leq_d} dBA (LMP: ${item.limite_normativa})</div>
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

window.focusPointOnAllLocationsMap = function(idx) {
    const marker = allLocationsMarkersList[idx];
    if (marker && allLocationsMapInstance) {
        allLocationsMapInstance.setView(marker.getLatLng(), 16, { animate: true });
        marker.openPopup();
    }
};

/* ==========================================================================
   5. MODAL MAPA INDIVIDUAL DE PUNTO
   ========================================================================== */
window.openMapModal = function(lat, lng, pointName, isCompliant, utmInfo) {
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

window.closeMapModal = function() {
    const m = document.getElementById('mapModal');
    if (m) m.classList.remove('open');
};

/* ==========================================================================
   6. MODAL VISOR FOTOGRÁFICO
   ========================================================================== */
window.openPhotoViewerModal = function(url, pointName, caption) {
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

window.closePhotoViewerModal = function() {
    const m = document.getElementById('photoViewerModal');
    if (m) m.classList.remove('open');
};

/* ==========================================================================
   7. MODAL EXPORTAR & TABLAS TÉCNICAS
   ========================================================================== */
window.openExportModal = function() {
    const m = document.getElementById('exportModal');
    if (m) m.classList.add('open');
};

window.closeExportModal = function() {
    const m = document.getElementById('exportModal');
    if (m) m.classList.remove('open');
};

window.openRuidoAmbientalTablesModal = function() {
    const m = document.getElementById('ruidoAmbientalTablesModal');
    if (m) m.classList.add('open');
};

window.closeRuidoAmbientalTablesModal = function() {
    const m = document.getElementById('ruidoAmbientalTablesModal');
    if (m) m.classList.remove('open');
};

window.openPhotoReportModal = function() {
    const m = document.getElementById('photoReportModal');
    if (m) m.classList.add('open');
};

window.closePhotoReportModal = function() {
    const m = document.getElementById('photoReportModal');
    if (m) m.classList.remove('open');
};

window.openTechHeaderEditModal = function() {
    const m = document.getElementById('techHeaderEditModal');
    if (m) m.classList.add('open');
};

window.closeTechHeaderEditModal = function() {
    const m = document.getElementById('techHeaderEditModal');
    if (m) m.classList.remove('open');
};

/* ==========================================================================
   8. FILTROS EN VIVO Y BÚSQUEDA EN TABLA MAESTRA
   ========================================================================== */
window.filterRuidoAmbiental = function(type, btn) {
    currentFilterType = type;
    document.querySelectorAll('#ruidoFilterGroup .filter-pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    applyTableFilters();
};

window.searchRuidoAmbientalLive = function() {
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

// Inicialización cuando carga el DOM
document.addEventListener('DOMContentLoaded', () => {
    recalcRuidoAmbiental('create');
});
