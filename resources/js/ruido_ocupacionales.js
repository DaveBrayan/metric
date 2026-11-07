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
    if (!m) return;

    // Reset lecturas de ruido para creación
    noiseReadingsStore.create = [];
    renderNoiseReadings('create');

    // Reset carrusel fotográfico para creación
    sliderState.create = { photos: [], currentIndex: 0, removedUrls: [] };
    renderPhotoSlider('create');
    const fileInput = document.getElementById('create_images_input');
    if (fileInput) fileInput.value = '';

    m.classList.add('open');
    m.style.display = 'flex';

    setTimeout(() => {
        initModalMiniMap('create', -16.5, -68.15);
    }, 150);
};

window.closeCreateMeasurementModal = function () {
    const m = document.getElementById('createMeasurementModal');
    if (m) {
        m.classList.remove('open');
        m.style.display = 'none';
    }
};

const noiseReadingsStore = {
    create: [],
    edit: []
};
let isEditUnlocked = false;
const modalMiniMaps = { create: null, edit: null };
const modalMarkers = { create: null, edit: null };

const sliderState = {
    create: { photos: [], currentIndex: 0, removedUrls: [] },
    edit: { photos: [], currentIndex: 0, removedUrls: [] }
};

window.openViewMeasurementModal = function (item) {
    const m = document.getElementById('editMeasurementModal');
    if (!m) return;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${MODULE_ID}/ruido-ocupacional/mediciones/${item.id}`;
    }

    const ptCode = item.num || item.point_number || `P-${item.id}`;
    const codeDisp = document.getElementById('edit_pt_num_disp');
    if (codeDisp) codeDisp.textContent = ptCode;

    const setVal = (id, v) => { const el = document.getElementById(id); if (el) el.value = v ?? ''; };
    setVal('edit_point_number', ptCode);
    setVal('edit_measurement_date', item.raw_date || item.date || '');
    setVal('edit_measurement_time', item.raw_time || item.time || '08:00');
    setVal('edit_area', item.area || '');
    setVal('edit_punto_medicion', item.punto_medicion || '');
    setVal('edit_actividad', item.actividad || 'General');
    setVal('edit_maquinaria_equipo', item.maquinaria_equipo || '');

    // Normalizar y preseleccionar Tipo de Ruido con tolerancia a mayúsculas y variantes
    const rawTipo = (item.tipo_ruido || 'Estable').trim();
    const selTipo = document.getElementById('edit_tipo_ruido');
    if (selTipo) {
        let matched = false;
        for (let i = 0; i < selTipo.options.length; i++) {
            if (selTipo.options[i].value.toLowerCase() === rawTipo.toLowerCase()) {
                selTipo.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched) {
            const lower = rawTipo.toLowerCase();
            if (lower.includes('estab') || lower.includes('contin')) selTipo.value = 'Estable';
            else if (lower.includes('fluct') || lower.includes('interm')) selTipo.value = 'Fluctuante';
            else if (lower.includes('impac') || lower.includes('impul')) selTipo.value = 'Impacto';
            else {
                const opt = new Option(rawTipo, rawTipo, true, true);
                selTipo.add(opt);
                selTipo.value = rawTipo;
            }
        }
    }

    // Normalizar y preseleccionar Respuesta del Sonómetro
    const rawResp = (item.respuesta || 'Lento').trim().toLowerCase();
    const selResp = document.getElementById('edit_respuesta');
    if (selResp) {
        if (rawResp.startsWith('rap') || rawResp === 'f' || rawResp === 'fast') {
            selResp.value = 'Rápido';
        } else {
            selResp.value = 'Lento';
        }
    }

    setVal('edit_tiempo_expos_h', item.tiempo_expos_h || 8.0);
    setVal('edit_ponderacion', item.ponderacion || 'A');
    setVal('edit_uso_protectores', item.uso_protectores ? '1' : '0');
    setVal('edit_tipo_protector', item.tipo_protector || '');
    setVal('edit_nrr_db', item.nrr_db || '');
    setVal('edit_observations', item.observations || '');

    if (item.staff_id && document.getElementById('edit_staff_id')) {
        document.getElementById('edit_staff_id').value = item.staff_id;
    }

    const staffBadge = document.getElementById('edit_modal_registered_by');
    if (staffBadge) {
        staffBadge.textContent = item.registered_by || window.REGISTERED_BY_HEADER || 'Técnico';
    }

    // Georreferenciación UTM y Lat/Lng
    setVal('edit_utm_zone', item.utm_zone || '19K');
    setVal('edit_utm_easting', item.utm_easting || '');
    setVal('edit_utm_northing', item.utm_northing || '');
    setVal('edit_latitude', item.latitude || '');
    setVal('edit_longitude', item.longitude || '');
    setVal('edit_location', item.location || '');

    const utmDisp = document.getElementById('edit_utm_display');
    if (utmDisp) {
        utmDisp.textContent = `E: ${item.utm_easting || '—'}, N: ${item.utm_northing || '—'}, Z: ${item.utm_zone || '19K'}`;
    }

    // Lecturas del Sonómetro (dBA)
    noiseReadingsStore.edit = [];
    if (item.mediciones_db && Array.isArray(item.mediciones_db) && item.mediciones_db.length > 0) {
        noiseReadingsStore.edit = [...item.mediciones_db];
    }
    renderNoiseReadings('edit');

    // Archivo fotográfico (Slide continuo)
    let images = [];
    if (item.images && Array.isArray(item.images) && item.images.length > 0) {
        images = item.images;
    } else if (item.image_path) {
        images = [item.image_path];
    }
    const safeImages = images.filter(Boolean).map(src => {
        if (typeof src !== 'string') return '';
        if (src.startsWith('http') || src.startsWith('data:') || src.startsWith('blob:')) return src;
        return src.startsWith('/') ? src : ('/' + src);
    }).filter(Boolean);
    sliderState.edit = { photos: safeImages, currentIndex: 0, removedUrls: [] };
    renderPhotoSlider('edit');
    syncEditRemainingImages();

    // Iniciar siempre en modo Solo Lectura
    applyEditModeState(false);

    m.classList.add('open');
    m.style.display = 'flex';

    setTimeout(() => {
        let lat = parseFloat(item.latitude);
        let lng = parseFloat(item.longitude);
        if (isNaN(lat) || isNaN(lng)) {
            if (item.utm_easting && item.utm_northing) {
                const conv = utmToLatLngJS(item.utm_easting, item.utm_northing, item.utm_zone || '19K');
                lat = conv.lat;
                lng = conv.lng;
            } else {
                lat = -16.5;
                lng = -68.15;
            }
        }
        initModalMiniMap('edit', lat, lng);
    }, 150);
};

window.openEditMeasurementModal = function (item) {
    window.openViewMeasurementModal(item);
};

window.closeEditMeasurementModal = function () {
    const m = document.getElementById('editMeasurementModal');
    if (m) {
        m.classList.remove('open', 'active');
        m.style.display = 'none';
        applyEditModeState(false);
    }
};

window.toggleModalEditMode = function () {
    applyEditModeState(!isEditUnlocked);
};

function applyEditModeState(isEditing) {
    isEditUnlocked = isEditing;
    const form = document.getElementById('editMeasurementForm');
    const badge = document.getElementById('modalModeStatusBadge');
    const btn = document.getElementById('btnToggleEditMode');
    const btnText = document.getElementById('btnToggleEditModeText');
    const btnIcon = document.getElementById('btnToggleEditModeIcon');
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    const btnGps = document.getElementById('edit_btn_gps');
    const btnAddPhotos = document.getElementById('edit_btn_add_photos');
    const btnDelPhoto = document.getElementById('edit_btn_delete_photo');
    const readingsInputBar = document.getElementById('edit_readings_input_bar');

    if (!form) return;

    if (isEditing) {
        form.classList.remove('modal-view-mode');
        if (badge) {
            badge.className = 'modal-badge-edit';
            badge.textContent = 'Modo Edición';
            badge.style.cssText = '';
        }
        if (btn) btn.classList.add('active-editing');
        if (btnText) btnText.textContent = 'Lectura';
        if (btnIcon) {
            btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
        }
        if (submitBtn) submitBtn.style.display = 'inline-flex';
        if (btnGps) btnGps.style.display = 'inline-flex';
        if (btnAddPhotos) btnAddPhotos.style.display = 'flex';
        if (btnDelPhoto && sliderState.edit.photos.length > 0) btnDelPhoto.style.display = 'inline-flex';
        if (readingsInputBar) readingsInputBar.style.display = 'flex';

        form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
            el.disabled = false;
        });

        if (modalMarkers.edit && modalMarkers.edit.dragging) {
            modalMarkers.edit.dragging.enable();
        }
    } else {
        form.classList.add('modal-view-mode');
        if (badge) {
            badge.className = 'modal-badge-view';
            badge.textContent = 'Solo Lectura';
            badge.style.cssText = '';
        }
        if (btn) btn.classList.remove('active-editing');
        if (btnText) btnText.textContent = 'Editar';
        if (btnIcon) {
            btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>`;
        }
        if (submitBtn) submitBtn.style.display = 'none';
        if (btnGps) btnGps.style.display = 'none';
        if (btnAddPhotos) btnAddPhotos.style.display = 'none';
        if (btnDelPhoto) btnDelPhoto.style.display = 'none';
        if (readingsInputBar) readingsInputBar.style.display = 'none';

        form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
            el.disabled = true;
        });

        if (modalMarkers.edit && modalMarkers.edit.dragging) {
            modalMarkers.edit.dragging.disable();
        }
    }

    renderNoiseReadings('edit');
    renderPhotoSlider('edit');
}

window.addNoiseReadingPoint = function (prefix) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    const input = document.getElementById(`${prefix}_quick_db_input`);
    if (!input) return;
    const val = parseFloat(input.value);
    if (isNaN(val) || val <= 0) {
        alert('Ingrese un nivel de decibeles válido (> 0 dBA).');
        input.focus();
        return;
    }
    if (!noiseReadingsStore[prefix]) noiseReadingsStore[prefix] = [];
    noiseReadingsStore[prefix].push(val);
    input.value = '';
    input.focus();
    renderNoiseReadings(prefix);
};

window.removeNoiseReadingPoint = function (prefix, index) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    if (!noiseReadingsStore[prefix]) return;
    noiseReadingsStore[prefix].splice(index, 1);
    renderNoiseReadings(prefix);
};

function renderNoiseReadings(prefix) {
    const list = noiseReadingsStore[prefix] || [];
    const container = document.getElementById(`${prefix}_readings_badges_container`);
    const countBadge = document.getElementById(`${prefix}_readings_count_badge`);
    const hiddenJson = document.getElementById(`${prefix}_mediciones_db_json`);

    if (!container) return;

    if (countBadge) countBadge.textContent = `${list.length} ${list.length === 1 ? 'lectura' : 'lecturas'}`;
    if (hiddenJson) hiddenJson.value = JSON.stringify(list);

    if (list.length === 0) {
        container.innerHTML = `<span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas registradas.</span>`;
        updateNoiseSummaryStrip(prefix, null);
        return;
    }

    const canDelete = (prefix === 'create') || (prefix === 'edit' && isEditUnlocked);
    let badgesHtml = '';
    list.forEach((val, idx) => {
        badgesHtml += `
            <span class="lux-reading-badge" style="font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 3px 8px; border-radius: 6px; border: 1px solid #bae6fd; display: inline-flex; align-items: center; gap: 4px;">
                <span>#${idx + 1}: <strong>${val.toFixed(1)} dB</strong></span>
                ${canDelete ? `<button type="button" onclick="removeNoiseReadingPoint('${prefix}', ${idx})" title="Eliminar lectura #${idx + 1}" style="background: none; border: none; color: #0284c7; cursor: pointer; font-size: 11px; padding: 0 2px;">✕</button>` : ''}
            </span>
        `;
    });
    container.innerHTML = badgesHtml;

    recalculateRuidoModal(prefix);
}

window.recalculateRuidoModal = function (prefix) {
    const list = (noiseReadingsStore[prefix] || []).map(v => parseFloat(v)).filter(v => !isNaN(v) && v > 0);
    const leq = calculateAcousticLeq(list);
    const tpeInput = document.getElementById(`${prefix}_tiempo_expos_h`);
    const tpe = tpeInput ? (parseFloat(tpeInput.value) || 8.0) : 8.0;

    let tmpe = null;
    let dosis = null;
    if (leq !== null && leq > 0) {
        tmpe = 8.0 / Math.pow(2.0, (leq - 85.0) / 3.0);
        dosis = (tpe / tmpe) * 100.0;
    }

    updateNoiseSummaryStrip(prefix, { leq, tmpe, dosis });
};

function updateNoiseSummaryStrip(prefix, data) {
    const laeqEl = document.getElementById(`${prefix}_calc_laeq`);
    const tmpeEl = document.getElementById(`${prefix}_calc_tmpe`);
    const dosisEl = document.getElementById(`${prefix}_calc_dosis`);
    const badgeEl = document.getElementById(`${prefix}_calc_badge`);

    if (!data || data.leq === null) {
        if (laeqEl) laeqEl.textContent = '—';
        if (tmpeEl) tmpeEl.textContent = '—';
        if (dosisEl) dosisEl.textContent = '—';
        if (badgeEl) {
            badgeEl.textContent = 'Sin datos';
            badgeEl.className = 'lux-measured-badge';
            badgeEl.style.background = '#f1f5f9';
            badgeEl.style.color = '#64748b';
            badgeEl.style.borderColor = '#cbd5e1';
        }
        return;
    }

    const { leq, tmpe, dosis } = data;
    const isCompliant = (dosis !== null && dosis <= 100.0) && (leq <= 85.0);

    if (laeqEl) {
        laeqEl.textContent = `${leq.toFixed(1)} dBA`;
        laeqEl.style.color = (leq <= 85.0) ? '#059669' : '#dc2626';
    }
    if (tmpeEl) tmpeEl.textContent = tmpe !== null ? `${tmpe.toFixed(2)} h` : '—';
    if (dosisEl) {
        dosisEl.textContent = dosis !== null ? `${dosis.toFixed(1)}%` : '—';
        dosisEl.style.color = (dosis <= 100.0) ? '#059669' : '#dc2626';
    }
    if (badgeEl) {
        badgeEl.textContent = isCompliant ? '✔ CUMPLE' : '✕ NO CUMPLE';
        badgeEl.className = `lux-measured-badge ${isCompliant ? 'compliant' : 'non-compliant'}`;
        badgeEl.style.background = isCompliant ? '#ecfdf5' : '#fef2f2';
        badgeEl.style.color = isCompliant ? '#059669' : '#dc2626';
        badgeEl.style.borderColor = isCompliant ? '#a7f3d0' : '#fecaca';
    }
}

// Carrusel de Fotografías
function renderPhotoSlider(mode) {
    const state = sliderState[mode] || { photos: [], currentIndex: 0 };
    const counter = document.getElementById(`${mode}_slider_counter`);
    const btnPrev = document.getElementById(`${mode}_slider_btn_prev`);
    const btnNext = document.getElementById(`${mode}_slider_btn_next`);
    const mainImg = document.getElementById(`${mode}_slider_img`);
    const placeholder = document.getElementById(`${mode}_slider_placeholder`);
    const thumbsStrip = document.getElementById(`${mode}_slider_thumbs`);
    const countIndicator = document.getElementById(`${mode}_photo_count_indicator`);
    const btnDel = document.getElementById(`${mode}_btn_delete_photo`);

    const count = state.photos.length;
    if (countIndicator) {
        countIndicator.textContent = count === 1 ? '1 foto' : `${count} fotos`;
    }

    const canDelete = (mode === 'create') || (mode === 'edit' && isEditUnlocked);

    if (count === 0) {
        if (counter) counter.style.display = 'none';
        if (btnPrev) btnPrev.style.display = 'none';
        if (btnNext) btnNext.style.display = 'none';
        if (mainImg) { mainImg.style.display = 'none'; mainImg.src = ''; }
        if (placeholder) placeholder.style.display = 'flex';
        if (thumbsStrip) { thumbsStrip.style.display = 'none'; thumbsStrip.innerHTML = ''; }
        if (btnDel) btnDel.style.display = 'none';
        return;
    }

    if (state.currentIndex >= count) state.currentIndex = Math.max(0, count - 1);

    if (counter) {
        counter.textContent = `${state.currentIndex + 1} / ${count}`;
        counter.style.display = count > 1 ? 'block' : 'none';
    }
    if (btnPrev) btnPrev.style.display = count > 1 ? 'flex' : 'none';
    if (btnNext) btnNext.style.display = count > 1 ? 'flex' : 'none';

    if (mainImg) {
        mainImg.src = state.photos[state.currentIndex];
        mainImg.style.display = 'block';
    }
    if (placeholder) placeholder.style.display = 'none';

    if (btnDel) {
        btnDel.style.display = canDelete ? 'inline-flex' : 'none';
    }

    if (thumbsStrip) {
        if (count > 1) {
            thumbsStrip.style.display = 'flex';
            thumbsStrip.innerHTML = state.photos.map((src, idx) => `
                <div class="slider-thumb-item ${idx === state.currentIndex ? 'active' : ''}"
                     onclick="selectSlidePhoto('${mode}', ${idx})"
                     title="Ver foto ${idx + 1}"
                     style="position: relative; width: 48px; height: 48px; border-radius: 6px; cursor: pointer; border: 2px solid ${idx === state.currentIndex ? '#0284c7' : '#cbd5e1'}; flex-shrink: 0;">
                    <img src="${src}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 4px; display: block;">
                    ${canDelete ? `<button type="button" class="thumb-del-badge" onclick="event.stopPropagation(); deleteActivePhoto('${mode}', ${idx})" title="Eliminar foto #${idx + 1}">✕</button>` : ''}
                </div>
            `).join('');
        } else {
            thumbsStrip.style.display = 'none';
            thumbsStrip.innerHTML = '';
        }
    }
}

window.slidePhotoNav = function (mode, delta) {
    const state = sliderState[mode];
    if (!state || state.photos.length <= 1) return;
    state.currentIndex = (state.currentIndex + delta + state.photos.length) % state.photos.length;
    renderPhotoSlider(mode);
};

window.selectSlidePhoto = function (mode, index) {
    const state = sliderState[mode];
    if (!state || index < 0 || index >= state.photos.length) return;
    state.currentIndex = index;
    renderPhotoSlider(mode);
};

window.deleteActivePhoto = function (mode, targetIndex = null) {
    if (mode === 'edit' && !isEditUnlocked) return;
    const state = sliderState[mode];
    if (!state || state.photos.length === 0) return;

    const idxToRemove = targetIndex !== null ? targetIndex : state.currentIndex;
    if (idxToRemove < 0 || idxToRemove >= state.photos.length) return;

    const removedUrl = state.photos.splice(idxToRemove, 1)[0];
    if (removedUrl && !removedUrl.startsWith('data:')) {
        state.removedUrls.push(removedUrl);
    }
    if (state.currentIndex >= state.photos.length) {
        state.currentIndex = Math.max(0, state.photos.length - 1);
    }
    if (mode === 'edit') {
        syncEditRemainingImages();
    }
    renderPhotoSlider(mode);
};

function syncEditRemainingImages() {
    const state = sliderState.edit;
    const input = document.getElementById('edit_remaining_images');
    if (!input || !state) return;
    const remaining = state.photos.filter(p => !p.startsWith('data:') && !p.startsWith('blob:'));
    input.value = JSON.stringify(remaining);
}

window.handleMultipleImagesSelected = function (input, mode) {
    const state = sliderState[mode];
    if (!state || !input.files || input.files.length === 0) return;

    Array.from(input.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (e) {
            state.photos.push(e.target.result);
            renderPhotoSlider(mode);
            if (mode === 'edit') syncEditRemainingImages();
        };
        reader.readAsDataURL(file);
    });
};

// Mini Mapa Leaflet
function initModalMiniMap(mode, lat, lng) {
    const mapEl = document.getElementById(`${mode}_modal_map`);
    if (!mapEl || typeof L === 'undefined') return;

    if (modalMiniMaps[mode]) {
        modalMiniMaps[mode].remove();
        modalMiniMaps[mode] = null;
        modalMarkers[mode] = null;
    }

    modalMiniMaps[mode] = L.map(mapEl, {
        center: [lat, lng],
        zoom: 16,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(modalMiniMaps[mode]);

    const isDraggable = (mode === 'create') || isEditUnlocked;
    modalMarkers[mode] = L.marker([lat, lng], {
        draggable: isDraggable
    }).addTo(modalMiniMaps[mode]);

    modalMarkers[mode].on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateUtmFromLatLng(pos.lat, pos.lng, mode);
    });

    setTimeout(() => {
        if (modalMiniMaps[mode]) modalMiniMaps[mode].invalidateSize();
    }, 200);
}

function updateUtmFromLatLng(lat, lng, mode) {
    const latInput = document.getElementById(`${mode}_latitude`);
    const lngInput = document.getElementById(`${mode}_longitude`);
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);

    const conv = latLngToUtmJS(lat, lng);
    const eInput = document.getElementById(`${mode}_utm_easting`);
    const nInput = document.getElementById(`${mode}_utm_northing`);
    const zInput = document.getElementById(`${mode}_utm_zone`);
    const disp = document.getElementById(`${mode}_utm_display`);
    const locInput = document.getElementById(`${mode}_location`);

    if (eInput) eInput.value = conv.easting.toFixed(3);
    if (nInput) nInput.value = conv.northing.toFixed(3);
    if (zInput) zInput.value = conv.zone;
    const formatted = `E: ${conv.easting.toFixed(3)}, N: ${conv.northing.toFixed(3)}, Z: ${conv.zone}`;
    if (disp) disp.textContent = formatted;
    if (locInput) locInput.value = formatted;
}

window.syncUtmToMap = function (mode) {
    const eInput = document.getElementById(`${mode}_utm_easting`);
    const nInput = document.getElementById(`${mode}_utm_northing`);
    const zInput = document.getElementById(`${mode}_utm_zone`);
    if (!eInput || !nInput) return;

    const easting = parseFloat(eInput.value);
    const northing = parseFloat(nInput.value);
    const zone = zInput ? zInput.value : '19K';

    if (isNaN(easting) || isNaN(northing)) return;

    const pos = utmToLatLngJS(easting, northing, zone);
    const latInput = document.getElementById(`${mode}_latitude`);
    const lngInput = document.getElementById(`${mode}_longitude`);
    if (latInput) latInput.value = pos.lat.toFixed(7);
    if (lngInput) lngInput.value = pos.lng.toFixed(7);

    const disp = document.getElementById(`${mode}_utm_display`);
    const locInput = document.getElementById(`${mode}_location`);
    const formatted = `E: ${easting.toFixed(3)}, N: ${northing.toFixed(3)}, Z: ${zone}`;
    if (disp) disp.textContent = formatted;
    if (locInput) locInput.value = formatted;

    if (modalMarkers[mode] && modalMiniMaps[mode]) {
        modalMarkers[mode].setLatLng([pos.lat, pos.lng]);
        modalMiniMaps[mode].panTo([pos.lat, pos.lng]);
    }
};

window.getCurrentGpsPosition = function (latId, lngId, mode) {
    if (!navigator.geolocation) {
        alert('La geolocalización no está soportada por su navegador.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateUtmFromLatLng(lat, lng, mode);
            if (modalMarkers[mode] && modalMiniMaps[mode]) {
                modalMarkers[mode].setLatLng([lat, lng]);
                modalMiniMaps[mode].panTo([lat, lng]);
            }
        },
        function (err) {
            alert('No se pudo obtener la ubicación GPS: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
};

function latLngToUtmJS(lat, lng) {
    let zoneNumber = Math.floor((lng + 180) / 6) + 1;
    if (zoneNumber < 1) zoneNumber = 1;
    if (zoneNumber > 60) zoneNumber = 60;
    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const e = Math.sqrt(2 * f - f * f);
    const ePrimeSquared = (e * e) / (1 - e * e);
    const latRad = lat * (Math.PI / 180);
    const lngRad = lng * (Math.PI / 180);
    const centralLongitude = ((zoneNumber - 1) * 6 - 180 + 3) * (Math.PI / 180);
    const k0 = 0.9996;
    const N = a / Math.sqrt(1 - e * e * Math.sin(latRad) * Math.sin(latRad));
    const T = Math.tan(latRad) * Math.tan(latRad);
    const C = ePrimeSquared * Math.cos(latRad) * Math.cos(latRad);
    const A = Math.cos(latRad) * (lngRad - centralLongitude);
    const M = a * ((1 - e * e / 4 - 3 * e * e * e * e / 64 - 5 * e * e * e * e * e * e / 256) * latRad
        - (3 * e * e / 8 + 3 * e * e * e * e / 32 + 45 * e * e * e * e * e * e / 1024) * Math.sin(2 * latRad)
        + (15 * e * e * e * e / 256 + 45 * e * e * e * e * e * e / 1024) * Math.sin(4 * latRad)
        - (35 * e * e * e * e * e * e / 3072) * Math.sin(6 * latRad));
    const easting = k0 * N * (A + (1 - T + C) * A * A * A / 6 + (5 - 18 * T + T * T + 72 * C - 58 * ePrimeSquared) * A * A * A * A * A / 120) + 500000;
    let northing = k0 * (M + N * Math.tan(latRad) * (A * A / 2 + (5 - T + 9 * C + 4 * C * C) * A * A * A * A / 24 + (61 - 58 * T + T * T + 600 * C - 330 * ePrimeSquared) * A * A * A * A * A * A / 720));
    if (lat < 0) northing += 10000000.0;
    return { easting: Math.round(easting * 1000) / 1000, northing: Math.round(northing * 1000) / 1000, zone: zoneNumber + 'K' };
}

window.confirmDeleteMeasurement = function (id, pointNum) {
    const deleteAction = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/modulos/${MODULE_ID}/ruido-ocupacional/mediciones/${id}`;
        
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = CSRF_TOKEN;
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        document.body.appendChild(form);
        form.submit();
    };

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
            if (res.isConfirmed) deleteAction();
        });
    } else {
        if (confirm(`¿Eliminar medición ${pointNum}?`)) {
            deleteAction();
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
    if (typeof window.universalClosePhotoViewer === 'function') {
        window.universalClosePhotoViewer();
        return;
    }
    const m = document.getElementById('photoViewerModal');
    if (m) {
        m.classList.remove('open', 'active', 'show');
        m.style.removeProperty('display');
        m.style.display = 'none';
    }
    document.body.style.removeProperty('overflow');
};
window.closePhotoViewer = window.closePhotoViewerModal;

/* ==========================================================================
   7. MODAL EXPORTAR & TABLAS TÉCNICAS
   ========================================================================== */
window.openExportModal = function () {
    console.log('[Ruido Ocupacional] 🚀 openExportModal() disparado desde JS principal');
    const m = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (m) {
        m.classList.add('open', 'active');
        m.style.setProperty('display', 'flex', 'important');
        console.log('[Ruido Ocupacional] ✅ Modal de exportación abierto exitosamente.');
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
       8. FILTROS EN VIVO, BÚSQUEDA Y PAGINACIÓN REACTIVA EN TABLA MAESTRA
       ========================================================================== */
    const RUIDO_PAGE_SIZE = 10;
    let currentRuidoPage = 1;

    function getMatchingRuidoRows() {
        const inp = document.getElementById('ruidoSearchInput');
        const q = (inp?.value || '').toLowerCase().trim();
        const allRows = Array.from(document.querySelectorAll('#ruidoTableBody tr.ruido-data-row'));

        return allRows.filter(row => {
            const rowCompliant = row.getAttribute('data-compliant');
            const rowSearch = (row.getAttribute('data-search') || row.innerText || '').toLowerCase();

            let matchesFilter = true;
            if (currentFilterType === 'compliant') matchesFilter = (rowCompliant === 'compliant');
            else if (currentFilterType === 'non-compliant') matchesFilter = (rowCompliant === 'non-compliant');

            const matchesSearch = !q || rowSearch.includes(q);
            return matchesFilter && matchesSearch;
        });
    }

    function updateRuidoPagination() {
        const matchingRows = getMatchingRuidoRows();
        const allRows = Array.from(document.querySelectorAll('#ruidoTableBody tr.ruido-data-row'));
        const totalItems = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / RUIDO_PAGE_SIZE));

        if (currentRuidoPage > totalPages) currentRuidoPage = totalPages;
        if (currentRuidoPage < 1) currentRuidoPage = 1;

        const startIdx = (currentRuidoPage - 1) * RUIDO_PAGE_SIZE;
        const endIdx = startIdx + RUIDO_PAGE_SIZE;

        allRows.forEach(row => row.style.display = 'none');
        matchingRows.slice(startIdx, endIdx).forEach(row => {
            row.style.display = '';
        });

        const emptyTableRow = document.getElementById('emptyTableRow');
        const noResultsRow = document.getElementById('noResultsRow');
        if (allRows.length === 0) {
            if (emptyTableRow) emptyTableRow.style.display = '';
            if (noResultsRow) noResultsRow.style.display = 'none';
        } else if (totalItems === 0) {
            if (emptyTableRow) emptyTableRow.style.display = 'none';
            if (noResultsRow) noResultsRow.style.display = '';
        } else {
            if (emptyTableRow) emptyTableRow.style.display = 'none';
            if (noResultsRow) noResultsRow.style.display = 'none';
        }

        const startEl = document.getElementById('ruidoPageStart');
        const endEl = document.getElementById('ruidoPageEnd');
        const totalEl = document.getElementById('ruidoPageTotal');

        if (startEl) startEl.textContent = totalItems === 0 ? 0 : (startIdx + 1);
        if (endEl) endEl.textContent = Math.min(endIdx, totalItems);
        if (totalEl) totalEl.textContent = totalItems;

        renderRuidoPaginationControls(totalPages);
    }

    function renderRuidoPaginationControls(totalPages) {
        const container = document.getElementById('ruidoPaginationControls');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        html += `<button type="button" class="ruido-pag-btn" onclick="goToRuidoPage(${currentRuidoPage - 1})" ${currentRuidoPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || (p >= currentRuidoPage - 1 && p <= currentRuidoPage + 1)) {
                html += `<button type="button" class="ruido-pag-btn ${p === currentRuidoPage ? 'active' : ''}" onclick="goToRuidoPage(${p})">${p}</button>`;
            } else if (p === currentRuidoPage - 2 || p === currentRuidoPage + 2) {
                html += `<span style="padding: 0 4px; color: #94a3b8; font-weight: 700;">...</span>`;
            }
        }

        html += `<button type="button" class="ruido-pag-btn" onclick="goToRuidoPage(${currentRuidoPage + 1})" ${currentRuidoPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>`;

        container.innerHTML = html;
    }

    function goToRuidoPage(page) {
        currentRuidoPage = page;
        updateRuidoPagination();
        const table = document.getElementById('ruidoMasterTable');
        if (table) table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    window.goToRuidoPage = goToRuidoPage;
    window.updateRuidoPagination = updateRuidoPagination;
    window.renderRuidoPaginationControls = renderRuidoPaginationControls;

    window.filterruidoOcupacional = function (type, btn) {
        currentFilterType = type;
        document.querySelectorAll('#ruidoFilterGroup .filter-pill-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        currentRuidoPage = 1;
        updateRuidoPagination();
    };

    window.searchRuidoOcupacionalLive = function () {
        currentRuidoPage = 1;
        updateRuidoPagination();
    };

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
        if (typeof window.universalClosePhotoViewer === 'function') {
            window.universalClosePhotoViewer();
            return;
        }
        const modal = document.getElementById('photoViewerModal');
        if (modal) {
            modal.classList.remove('open', 'active', 'show');
            modal.style.removeProperty('display');
            modal.style.display = 'none';
        }
        document.body.style.removeProperty('overflow');
    };
    window.closePhotoViewer = window.closePhotoViewerModal;

    // Inicialización cuando carga el DOM
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof updateRuidoPagination === 'function') {
            updateRuidoPagination();
        }

        if (typeof window.recalcruidoOcupacional === 'function') {
            window.recalcruidoOcupacional('create');
        } else if (typeof recalcruidoOcupacional === 'function') {
            recalcruidoOcupacional('create');
        }

        document.querySelectorAll('.modal-backdrop-custom').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('open', 'active');
                    modal.style.setProperty('display', 'none', 'important');
                }
            });
        });
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(() => {
            if (typeof updateRuidoPagination === 'function') {
                updateRuidoPagination();
            }
        }, 60);
    }

/* ==========================================================================
   MODAL DE EXPORTACIÓN & REPORTE FOTOGRÁFICO MOSAICO CARTA (RUIDO OCUPACIONAL)
   ========================================================================== */
function openExportModal() {
    console.log('[Ruido Ocupacional] 🚀 openExportModal() ejecutado');
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
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
window.photoReportConfig = photoReportConfig;
let photoReportAutoSaveTimeout = null;

function getSafeMeasurementsArray() {
    let data = window.ALL_MEASUREMENTS_DATA;
    if ((!data || (Array.isArray(data) && data.length === 0)) && window.METRIC_RUIDO_OCUPACIONAL_CONFIG) {
        data = window.METRIC_RUIDO_OCUPACIONAL_CONFIG.measurements;
    }
    if (!data) return [];
    if (Array.isArray(data)) return data;
    if (typeof data === 'object') return Object.values(data);
    return [];
}
window.getSafeMeasurementsArray = getSafeMeasurementsArray;

function openEditMeasurementById(id) {
    const list = getSafeMeasurementsArray();
    const item = list.find(m => Number(m.id) === Number(id));
    if (item && typeof openEditMeasurementModal === 'function') {
        openEditMeasurementModal(item);
    }
}
window.openEditMeasurementById = openEditMeasurementById;

function openPhotoViewerByPointId(pointId) {
    const all = getSafeMeasurementsArray();
    const item = all.find(m => Number(m.id) === Number(pointId));
    if (!item) return;
    const images = getPointImages(item);
    const idx = photoReportConfig.photoIndices[pointId] || 0;
    const img = images[idx] || images[0];
    if (img) {
        const code = formatPointCode(item.num || item.id);
        const title = `${code}: ${item.punto_medicion || item.area || 'Punto'}`;
        if (typeof window.universalOpenPhotoViewer === 'function') {
            window.universalOpenPhotoViewer(images, title, idx);
        } else if (typeof openPhotoViewer === 'function') {
            openPhotoViewer(images, title);
        }
    }
}
window.openPhotoViewerByPointId = openPhotoViewerByPointId;

function openPhotoReportModal() {
    console.log('[Ruido Ocupacional] 📸 openPhotoReportModal() ejecutado');
    closeExportModal();
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.add('open', 'active');
        modal.style.setProperty('display', 'flex', 'important');
        photoReportConfig.isInitialized = false;
        initPhotoReportEngine();
    } else {
        console.error('[Ruido Ocupacional] ❌ No se encontró #photoReportModal en el DOM');
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
    const allItems = getSafeMeasurementsArray();
    const validPointIds = new Set(allItems.map(m => Number(m.id)));

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
        let hasValidSavedPoint = false;
        if (saved && Array.isArray(saved.selected_points) && saved.selected_points.length > 0) {
            saved.selected_points.forEach(id => {
                const numId = Number(id);
                if (validPointIds.has(numId)) {
                    photoReportConfig.selectedPoints.add(numId);
                    hasValidSavedPoint = true;
                }
            });
        }

        // Si no había selección guardada o ninguno de los guardados coincide con los puntos actuales, seleccionar TODOS
        if (!hasValidSavedPoint || photoReportConfig.selectedPoints.size === 0) {
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
    } else {
        // Si ya estaba inicializado, asegurarse de que haya selección activa
        if (photoReportConfig.selectedPoints.size === 0 && allItems.length > 0) {
            allItems.forEach(m => photoReportConfig.selectedPoints.add(Number(m.id)));
        }
    }

    // Salvaguarda final: si por alguna razón la selección quedó vacía teniendo mediciones, seleccionar todas
    if (photoReportConfig.selectedPoints.size === 0 && allItems.length > 0) {
        allItems.forEach(m => photoReportConfig.selectedPoints.add(Number(m.id)));
    }

    updateGridSelectorButtonsUI();
    updateGlobalFitButtonsUI();
    renderInteractiveGrid();
    renderPhotoSheets();
    updateSelectionCounterUI();
}
window.initPhotoReportEngine = initPhotoReportEngine;

function getPointImages(m) {
    if (!m) return [];
    let list = [];
    if (Array.isArray(m.images) && m.images.length > 0) {
        list = m.images;
    } else if (typeof m.images === 'string' && m.images.trim()) {
        try {
            const parsed = JSON.parse(m.images);
            if (Array.isArray(parsed)) list = parsed;
            else list = [m.images];
        } catch (e) {
            list = [m.images];
        }
    } else if (Array.isArray(m.image_paths) && m.image_paths.length > 0) {
        list = m.image_paths;
    } else if (typeof m.image_paths === 'string' && m.image_paths.trim()) {
        try {
            const parsed = JSON.parse(m.image_paths);
            if (Array.isArray(parsed)) list = parsed;
            else list = [m.image_paths];
        } catch (e) {
            list = [m.image_paths];
        }
    } else if (m.image_path) {
        list = [m.image_path];
    }
    return list.filter(Boolean);
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
    const allItems = getSafeMeasurementsArray();
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
    const total = getSafeMeasurementsArray().length;
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
    const allItems = getSafeMeasurementsArray();
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

    const allItems = getSafeMeasurementsArray();
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
                        <img id="photoImg_${pointId}" src="${currentImg}" alt="${codeFormatted}" onclick="openPhotoViewerByPointId(${pointId})" title="Clic para ampliar fotografía">
                        <button type="button" class="photo-zoom-btn" onclick="openPhotoViewerByPointId(${pointId})" title="Ampliar">
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

    const allItems = getSafeMeasurementsArray();
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

