/**
 * METRIC V2 — Monitoreo de Dosimetría de Ruido Ocupacional
 * Módulo JavaScript interactivo, cálculos acústicos y controlador de modales (Vite ES Module)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_DOSIMETRY_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/dosimetria/header');

// Variables de Estado
let currentGrid = PHOTO_REPORT_INITIAL_SETTINGS.grid || '2x3';
let selectedPoints = PHOTO_REPORT_INITIAL_SETTINGS.selected_points || ALL_MEASUREMENTS_DATA.map(m => m.id);
let photoIndices = PHOTO_REPORT_INITIAL_SETTINGS.photo_indices || {};
let createMapInstance = null;
let editMapInstance = null;
let singlePointMapInstance = null;
let allLocationsMapInstance = null;
let allLocationsMarkersLayer = null;
let allLocationsMarkersList = [];

// Paginación y Filtros de la Tabla Maestra
const ROWS_PER_PAGE = 10;
let currentPage = 1;
let currentFilterType = 'all';
let currentSearchQuery = '';

// Almacenamiento local de fotos seleccionadas para modal create / edit
const modalPhotoStore = {
    create: { files: [], urls: [], activeIndex: 0 },
    edit: { files: [], existingUrls: [], remainingUrls: [], urls: [], activeIndex: 0 }
};

/* ==========================================================================
   1. CÁLCULOS DINÁMICOS DE DOSIMETRÍA DE RUIDO (LMP & CUMPLIMIENTO)
   ========================================================================== */
function calculateLmpForTpe(tpeHoras) {
    const tpe = parseFloat(tpeHoras) || 8.0;
    if (tpe <= 0) return 85.0;
    // LMP = 85 - 10 * log10(TPE / 8) / log10(2)
    const lmp = 85.0 - (10.0 * Math.log10(tpe / 8.0) / Math.log10(2.0));
    return Math.round(lmp * 10) / 10;
}

function recalcDosimetry(prefix) {
    const tpeInput = document.getElementById(`${prefix}_tiempo_expos_h`);
    const leqInput = document.getElementById(`${prefix}_leq_t_db`);
    const lmpDisp = document.getElementById(`${prefix}_lmp_display`);
    const cumpleBadge = document.getElementById(`${prefix}_cumple_badge`);
    const cumpleText = document.getElementById(`${prefix}_cumple_text`);

    const tpe = parseFloat(tpeInput?.value) || 8.0;
    const lmp = calculateLmpForTpe(tpe);
    const leq = parseFloat(leqInput?.value);

    if (lmpDisp) {
        lmpDisp.textContent = `${lmp.toFixed(1)} dBA`;
    }

    let isCompliant = true;
    if (!isNaN(leq)) {
        isCompliant = leq <= lmp;
    }

    if (cumpleBadge && cumpleText) {
        if (isCompliant) {
            cumpleBadge.className = 'badge-compliance-ok';
            cumpleBadge.innerHTML = `
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <span id="${prefix}_cumple_text">CUMPLE</span>
            `;
        } else {
            cumpleBadge.className = 'badge-compliance-danger';
            cumpleBadge.innerHTML = `
                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <line x1="18" y1="6" x2="6" y2="18" />
                    <line x1="6" y1="6" x2="18" y2="18" />
                </svg>
                <span id="${prefix}_cumple_text">NO CUMPLE</span>
            `;
        }
    }
}

/* ==========================================================================
   2. UTM / LAT-LNG MATH & MAPAS INTERACTIVOS LEAFLET
   ========================================================================== */
function utmToLatLngJS(utmX, utmY, utmZoneStr = '20K') {
    let utmZoneNum = 20;
    let utmZoneLetter = 'K';
    const m = String(utmZoneStr).match(/(\d+)\s*([A-Za-z]?)/);
    if (m) {
        utmZoneNum = parseInt(m[1], 10) || 20;
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

    const utmLon0 = (utmZoneNum - 1) * 6 - 180 + 3;
    const utmLon = (utmLon0 * Math.PI / 180.0) + (
        utmD -
        (1 + 2 * utmT1 + utmC1) * utmD3 / 6 +
        (5 - 2 * utmC1 + 28 * utmT1 - 3 * utmC1 * utmC1 + 8 * utmEPrime * utmEPrime + 24 * utmT1 * utmT1) * utmD5 / 120
    ) / utmCosPhi1;

    return {
        lat: utmLat * 180.0 / Math.PI,
        lng: utmLon * 180.0 / Math.PI
    };
}

function latLngToUtmJS(lat, lng) {
    const utmZoneNum = Math.floor((lng + 180) / 6) + 1;
    const utmZoneLetter = lat >= 0 ? 'N' : 'K';

    const utmA = 6378137.0;
    const utmF = 1 / 298.257223563;
    const utmB = utmA * (1 - utmF);
    const utmE = Math.sqrt((utmA * utmA - utmB * utmB) / (utmA * utmA));
    const utmEPrime = Math.sqrt((utmA * utmA - utmB * utmB) / (utmB * utmB));
    const utmK0 = 0.9996;

    const latRad = lat * Math.PI / 180.0;
    const lngRad = lng * Math.PI / 180.0;
    const lngOrigin = ((utmZoneNum - 1) * 6 - 180 + 3) * Math.PI / 180.0;

    const sinLat = Math.sin(latRad);
    const cosLat = Math.cos(latRad);
    const tanLat = Math.tan(latRad);

    const n = utmA / Math.sqrt(1 - utmE * utmE * sinLat * sinLat);
    const t = tanLat * tanLat;
    const c = utmEPrime * utmEPrime * cosLat * cosLat;
    const a = cosLat * (lngRad - lngOrigin);

    const m = utmA * (
        (1 - utmE * utmE / 4 - 3 * Math.pow(utmE, 4) / 64 - 5 * Math.pow(utmE, 6) / 256) * latRad -
        (3 * utmE * utmE / 8 + 3 * Math.pow(utmE, 4) / 32 + 45 * Math.pow(utmE, 6) / 1024) * Math.sin(2 * latRad) +
        (15 * Math.pow(utmE, 4) / 256 + 45 * Math.pow(utmE, 6) / 1024) * Math.sin(4 * latRad) -
        (35 * Math.pow(utmE, 6) / 3072) * Math.sin(6 * latRad)
    );

    const easting = utmK0 * n * (
        a + (1 - t + c) * Math.pow(a, 3) / 6 +
        (5 - 18 * t + t * t + 72 * c - 58 * utmEPrime * utmEPrime) * Math.pow(a, 5) / 120
    ) + 500000.0;

    let northing = utmK0 * (
        m + n * tanLat * (
            a * a / 2 +
            (5 - t + 9 * c + 4 * c * c) * Math.pow(a, 4) / 24 +
            (61 - 58 * t + t * t + 600 * c - 330 * utmEPrime * utmEPrime) * Math.pow(a, 6) / 720
        )
    );

    if (lat < 0) {
        northing += 10000000.0;
    }

    return {
        easting: easting,
        northing: northing,
        zone: `${utmZoneNum}${utmZoneLetter}`
    };
}

function initLeafletMiniMap(prefix, initialLat, initialLng) {
    const containerId = `${prefix}_modal_map`;
    const container = document.getElementById(containerId);
    if (!container || typeof L === 'undefined') return;

    let mapInstance = prefix === 'create' ? createMapInstance : editMapInstance;
    if (mapInstance) {
        mapInstance.remove();
    }

    const defaultLat = initialLat || -16.5000;
    const defaultLng = initialLng || -68.1500;

    mapInstance = L.map(containerId, {
        center: [defaultLat, defaultLng],
        zoom: (initialLat && initialLng) ? 17 : 13,
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(mapInstance);

    let marker = null;
    if (initialLat && initialLng) {
        marker = L.marker([initialLat, initialLng]).addTo(mapInstance);
    }

    mapInstance.on('click', function(e) {
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        if (marker) {
            marker.setLatLng(e.latlng);
        } else {
            marker = L.marker(e.latlng).addTo(mapInstance);
        }

        const utm = latLngToUtmJS(lat, lng);
        const eastingInput = document.getElementById(`${prefix}_utm_easting`);
        const northingInput = document.getElementById(`${prefix}_utm_northing`);
        const zoneInput = document.getElementById(`${prefix}_utm_zone`);
        const dispEl = document.getElementById(`${prefix}_utm_display`);
        const latInput = document.getElementById(`${prefix}_latitude`);
        const lngInput = document.getElementById(`${prefix}_longitude`);
        const locInput = document.getElementById(`${prefix}_location`);

        if (eastingInput) eastingInput.value = utm.easting.toFixed(3);
        if (northingInput) northingInput.value = utm.northing.toFixed(3);
        if (zoneInput) zoneInput.value = utm.zone;
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        if (locInput) locInput.value = `E: ${utm.easting.toFixed(3)}, N: ${utm.northing.toFixed(3)}, Z: ${utm.zone}`;
        if (dispEl) dispEl.textContent = `E: ${utm.easting.toFixed(3)}, N: ${utm.northing.toFixed(3)}, Z: ${utm.zone}`;
    });

    if (prefix === 'create') {
        createMapInstance = mapInstance;
    } else {
        editMapInstance = mapInstance;
    }

    setTimeout(() => {
        mapInstance.invalidateSize();
    }, 250);
}

function syncUtmToMap(prefix) {
    const easting = parseFloat(document.getElementById(`${prefix}_utm_easting`)?.value);
    const northing = parseFloat(document.getElementById(`${prefix}_utm_northing`)?.value);
    const zone = document.getElementById(`${prefix}_utm_zone`)?.value || '20K';
    const dispEl = document.getElementById(`${prefix}_utm_display`);
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);
    const locInput = document.getElementById(`${prefix}_location`);

    if (!isNaN(easting) && !isNaN(northing)) {
        const conv = utmToLatLngJS(easting, northing, zone);
        if (latInput) latInput.value = conv.lat;
        if (lngInput) lngInput.value = conv.lng;
        if (locInput) locInput.value = `E: ${easting.toFixed(3)}, N: ${northing.toFixed(3)}, Z: ${zone}`;
        if (dispEl) dispEl.textContent = `E: ${easting.toFixed(3)}, N: ${northing.toFixed(3)}, Z: ${zone}`;

        const mapInstance = prefix === 'create' ? createMapInstance : editMapInstance;
        if (mapInstance && typeof L !== 'undefined') {
            mapInstance.setView([conv.lat, conv.lng], 17);
            mapInstance.eachLayer(layer => {
                if (layer instanceof L.Marker) {
                    layer.setLatLng([conv.lat, conv.lng]);
                }
            });
        }
    }
}

function getCurrentGpsPosition(latId, lngId, prefix) {
    if (!navigator.geolocation) {
        return;
    }
    navigator.geolocation.getCurrentPosition(pos => {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const utm = latLngToUtmJS(lat, lng);

        const eastingInput = document.getElementById(`${prefix}_utm_easting`);
        const northingInput = document.getElementById(`${prefix}_utm_northing`);
        const zoneInput = document.getElementById(`${prefix}_utm_zone`);
        const dispEl = document.getElementById(`${prefix}_utm_display`);
        const latInput = document.getElementById(latId);
        const lngInput = document.getElementById(lngId);
        const locInput = document.getElementById(`${prefix}_location`);

        if (eastingInput) eastingInput.value = utm.easting.toFixed(3);
        if (northingInput) northingInput.value = utm.northing.toFixed(3);
        if (zoneInput) zoneInput.value = utm.zone;
        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
        if (locInput) locInput.value = `E: ${utm.easting.toFixed(3)}, N: ${utm.northing.toFixed(3)}, Z: ${utm.zone}`;
        if (dispEl) dispEl.textContent = `E: ${utm.easting.toFixed(3)}, N: ${utm.northing.toFixed(3)}, Z: ${utm.zone}`;

        initLeafletMiniMap(prefix, lat, lng);
    }, err => {
        // Silencioso según requerimiento del usuario
    }, { enableHighAccuracy: true, timeout: 10000 });
}

/* ==========================================================================
   3. GESTIÓN DE FOTOS Y CARRUSEL / SLIDE
   ========================================================================== */
function handleMultipleImagesSelected(input, prefix) {
    if (!input.files || input.files.length === 0) return;
    const store = modalPhotoStore[prefix];
    for (let i = 0; i < input.files.length; i++) {
        const file = input.files[i];
        store.files.push(file);
        store.urls.push(URL.createObjectURL(file));
    }
    store.activeIndex = store.urls.length - 1;
    renderModalPhotoSlider(prefix);
}

function renderModalPhotoSlider(prefix) {
    const store = modalPhotoStore[prefix];
    let allPhotos = [];
    if (prefix === 'create') {
        allPhotos = store.urls;
    } else {
        allPhotos = [...store.remainingUrls, ...store.urls];
    }

    const mainImg = document.getElementById(`${prefix}_slider_img`);
    const placeholder = document.getElementById(`${prefix}_slider_placeholder`);
    const counter = document.getElementById(`${prefix}_slider_counter`);
    const btnPrev = document.getElementById(`${prefix}_slider_btn_prev`);
    const btnNext = document.getElementById(`${prefix}_slider_btn_next`);
    const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
    const countIndicator = document.getElementById(`${prefix}_photo_count_indicator`);

    const count = allPhotos.length;
    if (countIndicator) countIndicator.textContent = `${count} ${count === 1 ? 'foto' : 'fotos'}`;

    if (count === 0) {
        if (mainImg) mainImg.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
        if (counter) counter.style.display = 'none';
        if (btnPrev) btnPrev.style.display = 'none';
        if (btnNext) btnNext.style.display = 'none';
        if (thumbsStrip) thumbsStrip.style.display = 'none';
        return;
    }

    if (store.activeIndex >= count) store.activeIndex = count - 1;
    if (store.activeIndex < 0) store.activeIndex = 0;

    const currentUrl = allPhotos[store.activeIndex];
    if (mainImg) {
        mainImg.src = currentUrl;
        mainImg.style.display = 'block';
    }
    if (placeholder) placeholder.style.display = 'none';

    if (counter) {
        counter.textContent = `${store.activeIndex + 1} / ${count}`;
        counter.style.display = count > 1 ? 'block' : 'none';
    }

    if (btnPrev) btnPrev.style.display = count > 1 ? 'grid' : 'none';
    if (btnNext) btnNext.style.display = count > 1 ? 'grid' : 'none';

    if (thumbsStrip) {
        thumbsStrip.innerHTML = '';
        if (count > 1) {
            thumbsStrip.style.display = 'flex';
            allPhotos.forEach((url, idx) => {
                const thumb = document.createElement('div');
                thumb.className = `slider-thumb-item ${idx === store.activeIndex ? 'active' : ''}`;
                thumb.innerHTML = `<img src="${url}" alt="Miniatura ${idx + 1}">`;
                thumb.onclick = () => {
                    store.activeIndex = idx;
                    renderModalPhotoSlider(prefix);
                };
                thumbsStrip.appendChild(thumb);
            });
        } else {
            thumbsStrip.style.display = 'none';
        }
    }
}

function slidePhotoNav(prefix, dir) {
    const store = modalPhotoStore[prefix];
    const total = prefix === 'create' ? store.urls.length : (store.remainingUrls.length + store.urls.length);
    if (total <= 1) return;
    store.activeIndex = (store.activeIndex + dir + total) % total;
    renderModalPhotoSlider(prefix);
}

function syncEditRemainingImages() {
    const remInput = document.getElementById('edit_remaining_images');
    if (remInput && modalPhotoStore.edit) {
        remInput.value = JSON.stringify(modalPhotoStore.edit.remainingUrls || []);
    }
    const delBtn = document.getElementById('edit_slider_del_btn');
    if (delBtn && modalPhotoStore.edit) {
        const hasPhotos = (modalPhotoStore.edit.remainingUrls.length + modalPhotoStore.edit.urls.length) > 0;
        delBtn.style.display = (isEditUnlocked && hasPhotos) ? 'inline-flex' : 'none';
    }
}

function deleteActiveSlidePhoto(prefix) {
    if (prefix !== 'edit') return;
    const store = modalPhotoStore.edit;
    const totalExisting = store.remainingUrls.length;
    const totalNew = store.urls.length;
    const total = totalExisting + totalNew;
    if (total === 0) return;

    if (store.activeIndex < totalExisting) {
        store.remainingUrls.splice(store.activeIndex, 1);
        syncEditRemainingImages();
    } else {
        const newIdx = store.activeIndex - totalExisting;
        store.urls.splice(newIdx, 1);
        store.files.splice(newIdx, 1);
    }

    store.activeIndex = Math.max(0, store.activeIndex - 1);
    renderModalPhotoSlider('edit');
    syncEditRemainingImages();
}

let isEditUnlocked = false;

function toggleModalEditMode() {
    applyEditModeState(!isEditUnlocked);
}

function setModalReadOnlyMode(isReadOnly) {
    applyEditModeState(!isReadOnly);
}

function applyEditModeState(isEditing) {
    isEditUnlocked = isEditing;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        if (isEditing) {
            form.classList.remove('modal-view-mode');
        } else {
            form.classList.add('modal-view-mode');
        }
    }

    const badge = document.getElementById('modalModeStatusBadge');
    if (badge) {
        badge.textContent = isEditing ? 'Modo Edición' : 'Solo Lectura';
        badge.style.background = isEditing ? '#dcfce7' : '#f1f5f9';
        badge.style.color = isEditing ? '#15803d' : '#475569';
        badge.style.borderColor = isEditing ? '#86efac' : '#cbd5e1';
    }

    const btnText = document.getElementById('btnToggleEditModeText');
    const footerBtnText = document.getElementById('footerBtnToggleEditText');
    const btnIcon = document.getElementById('btnToggleEditModeIcon');
    const toggleBtn = document.getElementById('btnToggleEditMode');

    if (btnText) btnText.textContent = isEditing ? 'Lectura' : 'Editar';
    if (footerBtnText) footerBtnText.textContent = isEditing ? 'Ver Solo Lectura' : 'Editar Punto';

    if (btnIcon) {
        if (isEditing) {
            btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
        } else {
            btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>`;
        }
    }

    if (toggleBtn) {
        toggleBtn.style.background = isEditing ? '#fef3c7' : '#f0f9ff';
        toggleBtn.style.borderColor = isEditing ? '#f59e0b' : '#0284c7';
        toggleBtn.style.color = isEditing ? '#b45309' : '#0284c7';
    }

    const fields = [
        'edit_measurement_date', 'edit_measurement_time', 'edit_staff_id', 'edit_area',
        'edit_punto_medicion', 'edit_tipo_ruido', 'edit_tiempo_expos_h', 'edit_ponderacion',
        'edit_respuesta', 'edit_duracion_medicion_h', 'edit_leq_t_db', 'edit_nps_max_db',
        'edit_nps_min_db', 'edit_utm_easting', 'edit_utm_northing', 'edit_utm_zone',
        'edit_observations'
    ];

    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = !isEditing;
    });

    const submitBtn = document.getElementById('edit_modal_submit_btn');
    if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const gpsBtn = document.getElementById('edit_btn_gps');
    if (gpsBtn) gpsBtn.style.display = isEditing ? 'inline-flex' : 'none';

    syncEditRemainingImages();
}

/* ==========================================================================
   4. MODAL HANDLERS (CREATE, EDIT, VIEW, DELETE)
   ========================================================================== */
function openCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;

    modalPhotoStore.create = { files: [], urls: [], activeIndex: 0 };
    renderModalPhotoSlider('create');

    const form = document.getElementById('createMeasurementForm');
    if (form) form.reset();

    const dateInput = document.getElementById('create_measurement_date');
    if (dateInput) dateInput.value = new Date().toISOString().split('T')[0];

    const timeInput = document.getElementById('create_measurement_time');
    if (timeInput) {
        const now = new Date();
        timeInput.value = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
    }

    const tpeInput = document.getElementById('create_tiempo_expos_h');
    if (tpeInput) tpeInput.value = '8.0';

    recalcDosimetry('create');
    const createUtmDisp = document.getElementById('create_utm_display');
    if (createUtmDisp) createUtmDisp.textContent = 'E: —, N: —, Z: 20K';
    initLeafletMiniMap('create', null, null);

    modal.classList.add('open');
}

function closeCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) modal.classList.remove('open');
}

function openViewMeasurementModal(data) {
    const modal = document.getElementById('editMeasurementModal');
    if (!modal) return;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${MODULE_ID}/dosimetria/mediciones/${data.id}`;
    }

    document.getElementById('edit_pt_num_disp').textContent = data.num || '01';
    document.getElementById('edit_point_number').value = data.num || '01';
    document.getElementById('edit_measurement_id').value = data.id;
    document.getElementById('edit_measurement_date').value = data.raw_date || '';
    document.getElementById('edit_measurement_time').value = data.time !== '—' ? data.time : '';
    document.getElementById('edit_area').value = data.area || '';
    document.getElementById('edit_punto_medicion').value = data.punto_medicion || '';
    document.getElementById('edit_tipo_ruido').value = data.tipo_ruido || 'Fluctuante';
    document.getElementById('edit_tiempo_expos_h').value = data.raw_tiempo_expos_h || '8.0';
    document.getElementById('edit_ponderacion').value = data.ponderacion || 'A';
    document.getElementById('edit_respuesta').value = data.respuesta || 'Lento';
    document.getElementById('edit_duracion_medicion_h').value = data.raw_duracion_medicion_h || '';
    document.getElementById('edit_leq_t_db').value = data.raw_leq_t_db || '';
    document.getElementById('edit_nps_max_db').value = data.raw_nps_max_db || '';
    document.getElementById('edit_nps_min_db').value = data.raw_nps_min_db || '';

    document.getElementById('edit_utm_easting').value = data.utm_easting || '';
    document.getElementById('edit_utm_northing').value = data.utm_northing || '';
    document.getElementById('edit_utm_zone').value = data.utm_zone || '20K';
    document.getElementById('edit_location').value = data.location || '';
    document.getElementById('edit_latitude').value = data.latitude || '';
    document.getElementById('edit_longitude').value = data.longitude || '';
    document.getElementById('edit_observations').value = data.raw_observations || '';

    const easting = data.utm_easting ? Number(data.utm_easting).toFixed(3) : null;
    const northing = data.utm_northing ? Number(data.utm_northing).toFixed(3) : null;
    const zone = data.utm_zone || '20K';
    const editUtmDisp = document.getElementById('edit_utm_display');
    if (editUtmDisp) {
        editUtmDisp.textContent = (easting && northing) ? `E: ${easting}, N: ${northing}, Z: ${zone}` : 'E: —, N: —, Z: 20K';
    }

    const staffEl = document.getElementById('edit_modal_registered_by');
    const staffSelect = document.getElementById('edit_staff_id');
    if (staffSelect) {
        if (data.staff_id) {
            staffSelect.value = data.staff_id;
        } else if (data.registered_by) {
            for (let i = 0; i < staffSelect.options.length; i++) {
                if (staffSelect.options[i].text.toLowerCase().includes(data.registered_by.toLowerCase())) {
                    staffSelect.selectedIndex = i;
                    break;
                }
            }
        }
        if (staffEl && staffSelect.selectedIndex >= 0 && staffSelect.options[staffSelect.selectedIndex]) {
            staffEl.textContent = staffSelect.options[staffSelect.selectedIndex].text.split('—')[0].trim();
        } else if (staffEl) {
            staffEl.textContent = data.registered_by || 'Técnico de Campo';
        }
    } else if (staffEl) {
        staffEl.textContent = data.registered_by || 'Técnico de Campo';
    }

    if (document.getElementById('edit_registered_by_disp')) {
        document.getElementById('edit_registered_by_disp').textContent = data.registered_by;
    }

    recalcDosimetry('edit');

    // Inicializar fotos existentes
    const rawImages = data.images || (data.image_path ? [data.image_path] : []);
    modalPhotoStore.edit = {
        files: [],
        existingUrls: [...rawImages],
        remainingUrls: [...rawImages],
        urls: [],
        activeIndex: 0
    };
    renderModalPhotoSlider('edit');
    syncEditRemainingImages();

    // Inicializar mini-mapa
    initLeafletMiniMap('edit', data.latitude, data.longitude);

    // Iniciar en modo Solo Lectura por defecto
    applyEditModeState(false);

    modal.classList.add('open');
}

function closeEditMeasurementModal() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) modal.classList.remove('open');
}

function confirmDeleteMeasurement(id, pointNum) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar Punto?',
            text: `¿Estás seguro de eliminar el punto de dosimetría #${pointNum}? Esta acción no se puede deshacer.`,
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
        }).then(res => {
            if (res.isConfirmed) {
                const form = document.getElementById('deleteMeasurementForm');
                if (form) {
                    form.action = `/modulos/${MODULE_ID}/dosimetria/mediciones/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar el punto #${pointNum}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = `/modulos/${MODULE_ID}/dosimetria/mediciones/${id}`;
                form.submit();
            }
        }
    }
}

/* ==========================================================================
   5. MAPA INDIVIDUAL, UBICACIONES Y VISOR FOTOGRÁFICO
   ========================================================================== */
function openMapModal(m) {
    const modal = document.getElementById('mapLocationModal');
    if (!modal) return;

    document.getElementById('mapCardPointName').textContent = m.punto_medicion || 'Punto de Medición';
    document.getElementById('mapCardLocationDesc').textContent = `${m.area || ''} — ${m.tipo_ruido} (TPE: ${m.tiempo_expos_h}h)`;
    
    const badgeEl = document.getElementById('mapCardLuxBadge');
    if (badgeEl) {
        badgeEl.textContent = `${m.leq_t_db} dB`;
        badgeEl.className = `leq-measured-badge ${m.cumple === 'SI' ? 'compliant' : 'non-compliant'}`;
    }

    const coordsEl = document.getElementById('mapCardCoords');
    if (coordsEl) {
        coordsEl.textContent = m.location || `${m.latitude}, ${m.longitude}`;
    }

    const thumbWrap = document.getElementById('mapModalPhotoThumbWrap');
    const thumbImg = document.getElementById('mapModalPhotoImg');
    if (m.image_path) {
        if (thumbWrap) thumbWrap.style.display = 'flex';
        if (thumbImg) thumbImg.src = m.image_path;
    } else {
        if (thumbWrap) thumbWrap.style.display = 'none';
    }

    const gMapsBtn = document.getElementById('openInGoogleMapsBtn');
    if (gMapsBtn) {
        if (m.latitude && m.longitude) {
            gMapsBtn.href = `https://www.google.com/maps/search/?api=1&query=${m.latitude},${m.longitude}`;
            gMapsBtn.style.display = 'inline-flex';
        } else {
            gMapsBtn.style.display = 'none';
        }
    }

    modal.classList.add('open');

    setTimeout(() => {
        if (singlePointMapInstance) singlePointMapInstance.remove();
        if (typeof L !== 'undefined') {
            const lat = m.latitude || -16.5000;
            const lng = m.longitude || -68.1500;
            singlePointMapInstance = L.map('mapContainerLeaflet', {
                center: [lat, lng],
                zoom: (m.latitude && m.longitude) ? 17 : 13,
                attributionControl: false
            });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(singlePointMapInstance);
            if (m.latitude && m.longitude) {
                L.marker([lat, lng]).addTo(singlePointMapInstance).bindPopup(`<strong>#${m.num}: ${m.punto_medicion}</strong><br>Leq,T: ${m.leq_t_db} dB (LMP: ${m.lmp} dBA)`).openPopup();
            }
        }
    }, 250);
}

function closeMapModal() {
    const modal = document.getElementById('mapLocationModal');
    if (modal) modal.classList.remove('open');
}

function openPhotoViewer(imgUrl, title = 'Fotografía del Punto') {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const titleEl = document.getElementById('photoViewerTitle');
    if (img) img.src = imgUrl;
    if (titleEl) titleEl.textContent = title;
    if (modal) modal.classList.add('open');
}

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.classList.remove('open');
}

function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');

    setTimeout(() => {
        initAllLocationsMap();
    }, 250);
}

function closeAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) modal.classList.remove('open');
}

function initAllLocationsMap() {
    const container = document.getElementById('allLocationsMapLeaflet');
    if (!container || typeof L === 'undefined') return;

    if (allLocationsMapInstance) {
        allLocationsMapInstance.remove();
    }

    allLocationsMapInstance = L.map('allLocationsMapLeaflet', {
        center: [-16.5000, -68.1500],
        zoom: 13,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(allLocationsMapInstance);

    allLocationsMarkersList = [];
    const bounds = L.latLngBounds([]);

    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        let lat = m.latitude;
        let lng = m.longitude;
        if (!lat || !lng) {
            if (m.utm_easting && m.utm_northing) {
                const conv = utmToLatLngJS(m.utm_easting, m.utm_northing, m.utm_zone || '20K');
                lat = conv.lat;
                lng = conv.lng;
            }
        }

        if (lat && lng) {
            const isCompliant = m.cumple === 'SI';
            const color = isCompliant ? '#059669' : '#dc2626';

            const customIcon = L.divIcon({
                className: 'custom-loc-marker',
                html: `<div style="background: ${color}; color: #fff; width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center; font-weight: 800; font-size: 11px; border: 2px solid #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.3);">${m.num}</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            const marker = L.marker([lat, lng], { icon: customIcon }).addTo(allLocationsMapInstance);
            
            const popupContent = `
                <div style="font-family: sans-serif; min-width: 180px; padding: 2px;">
                    <div style="font-weight: 800; font-size: 13px; color: #0f172a; margin-bottom: 2px;">#${m.num}: ${m.punto_medicion}</div>
                    <div style="font-size: 11.5px; color: #64748b; margin-bottom: 4px;">${m.area} • ${m.tipo_ruido}</div>
                    <div style="font-size: 12px; font-weight: 700; color: ${color}; margin-bottom: 4px;">Leq,T: ${m.leq_t_db} dB (LMP: ${m.lmp} dBA)</div>
                    <div style="font-size: 11px; color: #0284c7; background: #f0f9ff; padding: 2px 6px; border-radius: 4px; border: 1px solid #bae6fd;">
                        Registrado por: <strong>${m.registered_by}</strong>
                    </div>
                </div>
            `;
            marker.bindPopup(popupContent);
            allLocationsMarkersList[idx] = marker;
            bounds.extend([lat, lng]);
        }
    });

    if (bounds.isValid()) {
        allLocationsMapInstance.fitBounds(bounds, { padding: [40, 40] });
    }
}

function focusPointOnAllLocationsMap(index) {
    const marker = allLocationsMarkersList[index];
    if (marker && allLocationsMapInstance) {
        allLocationsMapInstance.setView(marker.getLatLng(), 17);
        marker.openPopup();
    }
}

/* ==========================================================================
   6. TABLA TÉCNICA E INFORME (MODAL VER TABLAS)
   ========================================================================== */
function openDosimetryTablesModal() {
    const modal = document.getElementById('dosimetryTablesModal');
    if (modal) modal.classList.add('open');
}

function closeDosimetryTablesModal() {
    const modal = document.getElementById('dosimetryTablesModal');
    if (modal) modal.classList.remove('open');
}

function switchDosimetryTableTab(tab) {
    const btnInforme = document.getElementById('tabBtn_informe');
    const btnDatos = document.getElementById('tabBtn_datos');
    const panelInforme = document.getElementById('panel_dosimetry_informe');
    const panelDatos = document.getElementById('panel_dosimetry_datos');

    if (tab === 'informe') {
        if (btnInforme) btnInforme.classList.add('active');
        if (btnDatos) btnDatos.classList.remove('active');
        if (panelInforme) panelInforme.classList.add('active');
        if (panelDatos) panelDatos.classList.remove('active');
    } else {
        if (btnDatos) btnDatos.classList.add('active');
        if (btnInforme) btnInforme.classList.remove('active');
        if (panelDatos) panelDatos.classList.add('active');
        if (panelInforme) panelInforme.classList.remove('active');
    }
}

/* ==========================================================================
   7. MODAL EXPORTAR (EXCEL & REPORTE FOTOGRÁFICO)
   ========================================================================== */
function openExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.add('open');
}

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.remove('open');
}

/* ==========================================================================
   8. EXPORTACIÓN TÉCNICA OFICIAL EN EXCEL CON EXCELJS (INFORME & DATOS)
   ========================================================================== */
async function downloadExcelPlanilla() {
    if (typeof ExcelJS === 'undefined') {
        alert('Cargando librería ExcelJS, por favor intenta en unos segundos...');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'METRIC v2 Pachabol';
    workbook.created = new Date();

    // =========================================================================
    // HOJA 1: INFORME DE EVALUACIÓN (Según formato oficial de informe)
    // =========================================================================
    const wsInforme = workbook.addWorksheet('INFORME_DOSIMETRIA', {
        views: [{ showGridLines: true }]
    });

    wsInforme.columns = [
        { width: 6 },   // A: N°
        { width: 22 },  // B: Área de Trabajo
        { width: 22 },  // C: Punto de medición
        { width: 16 },  // D: Tipo de ruido
        { width: 18 },  // E: TPE (Hrs)
        { width: 14 },  // F: Ponderación
        { width: 14 },  // G: Respuesta
        { width: 16 },  // H: Tiempo duración medición (Hrs)
        { width: 16 },  // I: NPS max (dB(A))
        { width: 16 },  // J: NPS min (dB(A))
        { width: 18 },  // K: Leq, T (dB(A))(*)
        { width: 18 },  // L: Laeq, d (dB(A))(**)
        { width: 18 },  // M: Dosis de ruido (***)
        { width: 30 },  // N: Acciones a tomar
    ];

    // Encabezado Corporativo
    wsInforme.mergeCells('A1:N1');
    const titleCell1 = wsInforme.getCell('A1');
    titleCell1.value = 'METRIC V2 — INFORME DE MEDICIÓN DE DOSIMETRÍA DE RUIDO — EVALUACIÓN DE CONFORMIDAD OCUPACIONAL';
    titleCell1.font = { name: 'Arial', size: 12, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell1.alignment = { horizontal: 'center', vertical: 'middle' };
    titleCell1.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
    wsInforme.getRow(1).height = 30;

    // Fila 2: Instalación y Equipo
    wsInforme.mergeCells('A2:G2');
    wsInforme.getCell('A2').value = `INSTALACIÓN: ${TECHNICAL_HEADER_DATA.installationName || '—'}`;
    wsInforme.getCell('A2').font = { name: 'Arial', size: 9.5, bold: true };
    wsInforme.getCell('A2').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };

    wsInforme.mergeCells('H2:N2');
    wsInforme.getCell('H2').value = `EQUIPO: ${TECHNICAL_HEADER_DATA.equipmentName || 'Dosímetro de Ruido'} (${TECHNICAL_HEADER_DATA.equipmentBrand || ''} ${TECHNICAL_HEADER_DATA.equipmentModel || ''}) - SERIE: ${TECHNICAL_HEADER_DATA.equipmentSerial || ''}`;
    wsInforme.getCell('H2').font = { name: 'Arial', size: 9.5, bold: true };
    wsInforme.getCell('H2').fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };
    wsInforme.getRow(2).height = 22;

    // Filas 4 y 5: Encabezados con Nivel Doble
    wsInforme.mergeCells('A4:A5');
    wsInforme.getCell('A4').value = 'Nº';

    wsInforme.mergeCells('B4:B5');
    wsInforme.getCell('B4').value = 'Área de Trabajo';

    wsInforme.mergeCells('C4:C5');
    wsInforme.getCell('C4').value = 'Punto de medición';

    wsInforme.mergeCells('D4:D5');
    wsInforme.getCell('D4').value = 'Tipo de ruido';

    wsInforme.mergeCells('E4:E5');
    wsInforme.getCell('E4').value = 'Tiempo promedio de Exposición del personal en la jornada (TPE) (Hrs)';

    wsInforme.mergeCells('F4:G4');
    wsInforme.getCell('F4').value = 'Datos del equipo';
    wsInforme.getCell('F5').value = 'Ponderación';
    wsInforme.getCell('G5').value = 'Respuesta';

    wsInforme.mergeCells('H4:H5');
    wsInforme.getCell('H4').value = 'Tiempo de duración de la medición (Hrs)';

    wsInforme.mergeCells('I4:I5');
    wsInforme.getCell('I4').value = 'Nivel de presión sonora (NPS) (max.) (dB (A))';

    wsInforme.mergeCells('J4:J5');
    wsInforme.getCell('J4').value = 'Nivel de presión sonora (NPS) (min.) (dB (A))';

    wsInforme.mergeCells('K4:K5');
    wsInforme.getCell('K4').value = 'Nivel de presión sonora continuo equivalente Laeq, T (dB (A))(*)';

    wsInforme.mergeCells('L4:L5');
    wsInforme.getCell('L4').value = 'Nivel de presión sonora dirario equivalente Laeq, d (dB (A))(**)';

    wsInforme.mergeCells('M4:M5');
    wsInforme.getCell('M4').value = 'Dosis de ruido para periodos o estudios a 8 horas (***)';

    wsInforme.mergeCells('N4:N5');
    wsInforme.getCell('N4').value = 'Acciones a tomar en caso de superar la Dosis de Ruido Proyectado a 8 horas';

    // Estilos para encabezados de informe
    [wsInforme.getRow(4), wsInforme.getRow(5)].forEach((r, idx) => {
        r.height = idx === 0 ? 26 : 22;
        r.eachCell((cell) => {
            cell.font = { name: 'Arial', size: 9, bold: true, color: { argb: 'FF0F172A' } };
            cell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE0F2FE' } };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FF94A3B8' } },
                left: { style: 'thin', color: { argb: 'FF94A3B8' } },
                bottom: { style: 'thin', color: { argb: 'FF94A3B8' } },
                right: { style: 'thin', color: { argb: 'FF94A3B8' } }
            };
        });
    });

    let rowInformeIdx = 6;
    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        const row = wsInforme.getRow(rowInformeIdx);
        const tpeVal = m.raw_tiempo_expos_h || 8.0;
        const durVal = m.raw_duracion_medicion_h || 0.0;
        const npsMaxVal = m.raw_nps_max_db !== null ? m.raw_nps_max_db : '';
        const npsMinVal = m.raw_nps_min_db !== null ? m.raw_nps_min_db : '';
        const leqTVal = m.raw_leq_t_db !== null ? m.raw_leq_t_db : '';

        // Fórmulas Excel de usuario:
        // L = IFERROR(K + (10 * LOG10(E / 8)), "")
        // M = IFERROR(10^((L - 85) / 10), "")
        const formulaLaeqD = leqTVal !== '' ? { formula: `IFERROR(K${rowInformeIdx}+(10*LOG10(E${rowInformeIdx}/8)), "")` } : (m.raw_laeq_d_db || '');
        const formulaDosis = leqTVal !== '' ? { formula: `IFERROR(10^((L${rowInformeIdx}-85)/10), "")` } : (m.raw_dosis_ruido || '');

        row.values = [
            m.num || (idx + 1),
            m.area || '',
            m.punto_medicion || '',
            m.tipo_ruido || '',
            tpeVal,
            m.ponderacion || 'A',
            (m.respuesta || 'LENTA').toUpperCase(),
            durVal > 0 ? durVal : '',
            npsMaxVal,
            npsMinVal,
            leqTVal,
            formulaLaeqD,
            formulaDosis,
            m.raw_observations || (m.observations !== 'Sin observaciones' ? m.observations : '')
        ];

        row.eachCell((cell, colNumber) => {
            cell.font = { name: 'Arial', size: 9.5 };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                left: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                bottom: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                right: { style: 'thin', color: { argb: 'FFCBD5E1' } }
            };

            if (colNumber === 1 || colNumber === 4 || colNumber === 5 || colNumber === 6 || colNumber === 7 || colNumber === 8) {
                cell.alignment = { horizontal: 'center', vertical: 'middle' };
            } else if (colNumber >= 9 && colNumber <= 13) {
                cell.alignment = { horizontal: 'right', vertical: 'middle' };
                cell.numFmt = '0.00';
            } else {
                cell.alignment = { horizontal: 'left', vertical: 'middle' };
            }
        });

        row.height = 22;
        rowInformeIdx++;
    });

    // =========================================================================
    // HOJA 2: PÁGINA DE DATOS (MATRIZ DE REGISTRO)
    // =========================================================================
    const wsDatos = workbook.addWorksheet('DATOS_DE_CAMPO', {
        views: [{ showGridLines: true }]
    });

    wsDatos.columns = [
        { width: 8 },   // A: NRO.
        { width: 22 },  // B: ÁREA DE TRABAJO
        { width: 22 },  // C: PUNTO DE MEDICION
        { width: 16 },  // D: TIPO DE RUIDO
        { width: 14 },  // E: TPE (Hr)
        { width: 14 },  // F: PONDERACION
        { width: 14 },  // G: RESPUESTA
        { width: 18 },  // H: TIEMPO DE MEDICIÓN (Hr)
        { width: 16 },  // I: NPS MAX (dB)
        { width: 16 },  // J: NPS MIN (dB)
        { width: 16 },  // K: Leq,T (dB)
        { width: 26 },  // L: OBSERVACIONES
    ];

    const dataHeaders = [
        'NRO.', 'ÁREA DE TRABAJO', 'PUNTO DE MEDICION', 'TIPO DE RUIDO', 'TPE (Hr)',
        'PONDERACION', 'RESPUESTA', 'TIEMPO DE MEDICIÓN (Hr)', 'NPS MAX (dB)', 'NPS MIN (dB)',
        'Leq,T (dB)', 'OBSERVACIONES'
    ];

    const dataHeaderRow = wsDatos.getRow(1);
    dataHeaderRow.values = dataHeaders;
    dataHeaderRow.height = 26;
    dataHeaderRow.eachCell((cell) => {
        cell.font = { name: 'Arial', size: 9.5, bold: true, color: { argb: 'FF000000' } };
        cell.alignment = { horizontal: 'center', vertical: 'middle' };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF8FAFC' } };
        cell.border = {
            top: { style: 'thin', color: { argb: 'FF000000' } },
            left: { style: 'thin', color: { argb: 'FF000000' } },
            bottom: { style: 'medium', color: { argb: 'FF000000' } },
            right: { style: 'thin', color: { argb: 'FF000000' } }
        };
    });

    let rowDatosIdx = 2;
    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        const row = wsDatos.getRow(rowDatosIdx);
        row.values = [
            m.num || (idx + 1),
            m.area || '',
            m.punto_medicion || '',
            m.tipo_ruido || '',
            m.raw_tiempo_expos_h || 8.0,
            m.ponderacion || 'A',
            (m.respuesta || 'LENTO').toUpperCase(),
            m.raw_duracion_medicion_h || '',
            m.raw_nps_max_db !== null ? m.raw_nps_max_db : '',
            m.raw_nps_min_db !== null ? m.raw_nps_min_db : '',
            m.raw_leq_t_db !== null ? m.raw_leq_t_db : '',
            m.raw_observations || (m.observations !== 'Sin observaciones' ? m.observations : '')
        ];

        row.eachCell((cell, colNumber) => {
            cell.font = { name: 'Arial', size: 9.5 };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FF000000' } },
                left: { style: 'thin', color: { argb: 'FF000000' } },
                bottom: { style: 'thin', color: { argb: 'FF000000' } },
                right: { style: 'thin', color: { argb: 'FF000000' } }
            };

            if (colNumber === 1 || colNumber === 4 || colNumber === 5 || colNumber === 6 || colNumber === 7 || colNumber === 8) {
                cell.alignment = { horizontal: 'center', vertical: 'middle' };
            } else if (colNumber >= 9 && colNumber <= 11) {
                cell.alignment = { horizontal: 'right', vertical: 'middle' };
                cell.numFmt = '0.00';
            } else {
                cell.alignment = { horizontal: 'left', vertical: 'middle' };
            }
        });

        row.height = 22;
        rowDatosIdx++;
    });

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Dosimetria_Ruido_Modulo_${MODULE_ID}_${new Date().toISOString().slice(0,10)}.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

/* ==========================================================================
   9. REPORTE FOTOGRÁFICO MOSAICO (CARTA PARA PDF)
   ========================================================================== */
function openPhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (!modal) return;
    modal.classList.add('open');
    renderPhotoReportInteractiveGrid();
    renderPhotoReportSheets();
    updatePhotoSelectionCount();
}

function closePhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) modal.classList.remove('open');
}

function changeGridDistribution(gridKey) {
    currentGrid = gridKey;
    document.querySelectorAll('#gridDistSelector .btn-grid-dist').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.grid === gridKey);
    });
    renderPhotoReportSheets();
}

function selectAllPoints(select) {
    if (select) {
        selectedPoints = ALL_MEASUREMENTS_DATA.map(m => m.id);
    } else {
        selectedPoints = [];
    }
    renderPhotoReportInteractiveGrid();
    renderPhotoReportSheets();
    updatePhotoSelectionCount();
}

function updatePhotoSelectionCount() {
    const textEl = document.getElementById('photoSelectionCountText');
    if (textEl) {
        textEl.textContent = `${selectedPoints.length} de ${ALL_MEASUREMENTS_DATA.length} seleccionadas`;
    }
}

function switchPhotoReportTab(tabKey) {
    const btnInteractive = document.getElementById('tabBtnInteractive');
    const btnSheets = document.getElementById('tabBtnSheets');
    const viewInteractive = document.getElementById('photoInteractiveView');
    const viewSheets = document.getElementById('photoSheetsView');

    if (tabKey === 'interactive') {
        btnInteractive?.classList.add('active');
        btnSheets?.classList.remove('active');
        if (viewInteractive) viewInteractive.style.display = 'block';
        if (viewSheets) viewSheets.style.display = 'none';
    } else {
        btnSheets?.classList.add('active');
        btnInteractive?.classList.remove('active');
        if (viewInteractive) viewInteractive.style.display = 'none';
        if (viewSheets) viewSheets.style.display = 'block';
        renderPhotoReportSheets();
    }
}

function renderPhotoReportInteractiveGrid() {
    const container = document.getElementById('photoInteractiveGridContainer');
    if (!container) return;
    container.innerHTML = '';

    ALL_MEASUREMENTS_DATA.forEach(m => {
        const isSelected = selectedPoints.includes(m.id);
        const rawImages = m.images && m.images.length > 0 ? m.images : (m.image_path ? [m.image_path] : []);
        const currentIdx = photoIndices[m.id] || 0;
        const currentImgUrl = rawImages[currentIdx] || '';

        const card = document.createElement('div');
        card.className = `photo-interactive-card ${isSelected ? 'selected' : ''}`;
        card.innerHTML = `
            <div class="photo-card-topbar">
                <label style="display: flex; align-items: center; gap: 6px; font-weight: 800; font-size: 12px; cursor: pointer;">
                    <input type="checkbox" ${isSelected ? 'checked' : ''} onchange="togglePointPhotoSelection(${m.id}, this.checked)">
                    <span>Punto #${m.num}</span>
                </label>
                <span style="font-size: 11px; font-weight: 700; color: ${m.cumple === 'SI' ? '#059669' : '#dc2626'};">${m.leq_t_db} dB</span>
            </div>
            <div class="photo-card-viewport">
                ${currentImgUrl ? `<img src="${currentImgUrl}" alt="Foto punto" class="photo-card-img">` : '<span style="color: #94a3b8; font-size: 11.5px;">Sin foto</span>'}
            </div>
            <div class="photo-card-footer">
                <strong style="display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${m.punto_medicion}</strong>
                <span style="color: #64748b; font-size: 11px;">${m.area}</span>
            </div>
        `;
        container.appendChild(card);
    });
}

function togglePointPhotoSelection(pointId, checked) {
    if (checked) {
        if (!selectedPoints.includes(pointId)) selectedPoints.push(pointId);
    } else {
        selectedPoints = selectedPoints.filter(id => id !== pointId);
    }
    renderPhotoReportInteractiveGrid();
    renderPhotoReportSheets();
    updatePhotoSelectionCount();
}

function renderPhotoReportSheets() {
    const container = document.getElementById('photoSheetsContainer');
    const printArea = document.getElementById('photoReportPrintArea');
    if (!container) return;

    container.innerHTML = '';
    if (printArea) printArea.innerHTML = '';

    const selectedData = ALL_MEASUREMENTS_DATA.filter(m => selectedPoints.includes(m.id));
    
    let capacity = 6;
    let gridClass = 'grid-2x3';
    if (currentGrid === '2x4') { capacity = 8; gridClass = 'grid-2x4'; }
    if (currentGrid === '3x3') { capacity = 9; gridClass = 'grid-3x3'; }
    if (currentGrid === '3x4') { capacity = 12; gridClass = 'grid-3x4'; }

    const totalPages = Math.max(1, Math.ceil(selectedData.length / capacity));
    const pagesIndicator = document.getElementById('photoReportPagesIndicator');
    if (pagesIndicator) pagesIndicator.textContent = `Hojas calculadas: ${totalPages}`;

    for (let page = 0; page < totalPages; page++) {
        const pageItems = selectedData.slice(page * capacity, (page + 1) * capacity);
        const sheet = document.createElement('div');
        sheet.className = 'photo-report-sheet';

        let itemsHtml = '';
        pageItems.forEach(m => {
            const rawImages = m.images && m.images.length > 0 ? m.images : (m.image_path ? [m.image_path] : []);
            const currentIdx = photoIndices[m.id] || 0;
            const currentImgUrl = rawImages[currentIdx] || '';

            itemsHtml += `
                <div class="sheet-photo-item">
                    <div class="sheet-photo-box">
                        ${currentImgUrl ? `<img src="${currentImgUrl}" alt="Foto punto">` : '<span style="color: #94a3b8; font-size: 10px;">Sin fotografía</span>'}
                    </div>
                    <div class="sheet-photo-info">
                        <strong>#${m.num}: ${m.punto_medicion}</strong><br>
                        ${m.area} • ${m.tipo_ruido} • Leq,T: <strong>${m.leq_t_db} dB</strong> (LMP: ${m.lmp} dBA) - ${m.cumple}
                    </div>
                </div>
            `;
        });

        sheet.innerHTML = `
            <div class="sheet-header-box">
                <div>
                    <div class="sheet-header-title">REPORTE FOTOGRÁFICO — DOSIMETRÍA DE RUIDO</div>
                    <div class="sheet-header-meta">Instalación: ${TECHNICAL_HEADER_DATA.installationName || '—'}</div>
                </div>
                <div style="text-align: right; font-size: 9.5px; color: #64748b;">
                    Hoja ${page + 1} de ${totalPages}
                </div>
            </div>
            <div class="sheet-mosaic-grid ${gridClass}">
                ${itemsHtml}
            </div>
            <div class="sheet-footer-box">
                <span>METRIC v2 Pachabol — Dosimetría de Ruido</span>
                <span>Página ${page + 1} de ${totalPages}</span>
            </div>
        `;

        container.appendChild(sheet);
        if (printArea) {
            printArea.appendChild(sheet.cloneNode(true));
        }
    }
}

async function savePhotoReportSettingsToServer(showAlert = false) {
    try {
        const res = await fetch(`/modulos/${MODULE_ID}/dosimetria/photo-report-settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
            },
            body: JSON.stringify({
                settings: {
                    grid: currentGrid,
                    selected_points: selectedPoints,
                    photo_indices: photoIndices
                }
            })
        });
        const data = await res.json();
        if (data.success && showAlert && typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Guardado!',
                text: 'Configuración de reporte fotográfico guardada con éxito.',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'metric-swal-popup' }
            });
        }
    } catch (e) {
        console.error(e);
    }
}

function printPhotoReport() {
    renderPhotoReportSheets();
    window.print();
}

/* ==========================================================================
   10. AUTO-GUARDADO DE ENCABEZADO TÉCNICO INLINE
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

/* ==========================================================================
   11. FILTRADO, BÚSQUEDA Y PAGINACIÓN REACTIVA
   ========================================================================== */
function filterDosimetry(filter, btn) {
    currentFilterType = filter;
    document.querySelectorAll('#dosimetryFilterGroup .filter-pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    currentPage = 1;
    applyTableFiltersAndPagination();
}

function searchDosimetryLive() {
    const input = document.getElementById('dosimetrySearchInput');
    currentSearchQuery = (input?.value || '').toLowerCase().trim();
    currentPage = 1;
    applyTableFiltersAndPagination();
}

function applyTableFiltersAndPagination() {
    const rows = Array.from(document.querySelectorAll('#dosimetryTableBody .dosimetry-data-row'));
    const emptyRow = document.getElementById('emptyTableRow');
    const noResultsRow = document.getElementById('noResultsSearchRow');

    let visibleRows = rows.filter(row => {
        const cumple = row.dataset.cumple || '';
        const ruido = row.dataset.ruido || '';
        const search = row.dataset.search || '';

        // Filtro por píldoras
        let passFilter = true;
        if (currentFilterType === 'SI' || currentFilterType === 'NO') {
            passFilter = cumple === currentFilterType;
        } else if (currentFilterType !== 'all') {
            passFilter = ruido === currentFilterType;
        }

        // Filtro por texto
        let passSearch = true;
        if (currentSearchQuery) {
            passSearch = search.includes(currentSearchQuery);
        }

        return passFilter && passSearch;
    });

    const totalVisible = visibleRows.length;
    const totalPages = Math.max(1, Math.ceil(totalVisible / ROWS_PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    const startIdx = (currentPage - 1) * ROWS_PER_PAGE;
    const endIdx = startIdx + ROWS_PER_PAGE;

    rows.forEach(r => r.style.display = 'none');
    visibleRows.slice(startIdx, endIdx).forEach(r => r.style.display = '');

    if (emptyRow) emptyRow.style.display = rows.length === 0 ? '' : 'none';
    if (noResultsRow) noResultsRow.style.display = (rows.length > 0 && totalVisible === 0) ? '' : 'none';

    // Actualizar Info y Controles de Paginación
    const pageStartEl = document.getElementById('dosiPageStart');
    const pageEndEl = document.getElementById('dosiPageEnd');
    const pageTotalEl = document.getElementById('dosiPageTotal');

    if (pageStartEl) pageStartEl.textContent = totalVisible > 0 ? (startIdx + 1) : 0;
    if (pageEndEl) pageEndEl.textContent = Math.min(endIdx, totalVisible);
    if (pageTotalEl) pageTotalEl.textContent = totalVisible;

    renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
    const controls = document.getElementById('dosimetryPaginationControls');
    if (!controls) return;
    controls.innerHTML = '';

    if (totalPages <= 1) return;

    // Botón Anterior
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '❮';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => {
        if (currentPage > 1) {
            currentPage--;
            applyTableFiltersAndPagination();
        }
    };
    controls.appendChild(prevBtn);

    for (let p = 1; p <= totalPages; p++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = `page-btn ${p === currentPage ? 'active' : ''}`;
        pageBtn.textContent = p;
        pageBtn.onclick = () => {
            currentPage = p;
            applyTableFiltersAndPagination();
        };
        controls.appendChild(pageBtn);
    }

    // Botón Siguiente
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.innerHTML = '❯';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => {
        if (currentPage < totalPages) {
            currentPage++;
            applyTableFiltersAndPagination();
        }
    };
    controls.appendChild(nextBtn);
}

// Exponer funciones al entorno global window
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.openViewMeasurementModal = openViewMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.openMapModal = openMapModal;
window.closeMapModal = closeMapModal;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewer = closePhotoViewer;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;
window.openDosimetryTablesModal = openDosimetryTablesModal;
window.closeDosimetryTablesModal = closeDosimetryTablesModal;
window.switchDosimetryTableTab = switchDosimetryTableTab;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.downloadExcelPlanilla = downloadExcelPlanilla;
window.openPhotoReportModal = openPhotoReportModal;
window.closePhotoReportModal = closePhotoReportModal;
window.changeGridDistribution = changeGridDistribution;
window.selectAllPoints = selectAllPoints;
window.togglePointPhotoSelection = togglePointPhotoSelection;
window.switchPhotoReportTab = switchPhotoReportTab;
window.savePhotoReportSettingsToServer = savePhotoReportSettingsToServer;
window.printPhotoReport = printPhotoReport;
window.autoSaveHeaderField = autoSaveHeaderField;
window.filterDosimetry = filterDosimetry;
window.searchDosimetryLive = searchDosimetryLive;
window.recalcDosimetry = recalcDosimetry;
window.syncUtmToMap = syncUtmToMap;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.slidePhotoNav = slidePhotoNav;
window.deleteActiveSlidePhoto = deleteActiveSlidePhoto;
window.syncEditRemainingImages = syncEditRemainingImages;
window.toggleModalEditMode = toggleModalEditMode;
window.setModalReadOnlyMode = setModalReadOnlyMode;
window.applyEditModeState = applyEditModeState;

// Inicialización en DOMContentLoaded
document.addEventListener('DOMContentLoaded', () => {
    applyTableFiltersAndPagination();
});
