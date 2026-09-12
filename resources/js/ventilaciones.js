/**
 * METRIC V2 — Monitoreo de Ventilación Ocupacional
 * Módulo JavaScript interactivo y controlador de modales (Vite ES Module)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_VENTILATION_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/ventilacion/header');
const TIPOS_LOCAL_NORMA = cfg.tiposLocalNorma || window.TIPOS_LOCAL_NORMA || {};

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
    edit: { files: [], existingUrls: [], remainingUrls: [], activeIndex: 0 }
};

/* ==========================================================================
   1. CÁLCULOS DINÁMICOS DE VENTILACIÓN
   ========================================================================== */
function onTipoLocalChange(prefix) {
    const select = document.getElementById(`${prefix}_tipo_local`);
    if (!select) return;
    const selectedOpt = select.options[select.selectedIndex];
    const minVal = selectedOpt?.dataset?.min ? parseFloat(selectedOpt.dataset.min) : null;
    const maxVal = selectedOpt?.dataset?.max ? parseFloat(selectedOpt.dataset.max) : null;
    const intervaloVal = selectedOpt?.dataset?.intervalo || (minVal && maxVal ? `${minVal} - ${maxVal}` : '—');

    const minInput = document.getElementById(`${prefix}_renovaciones_min`);
    const maxInput = document.getElementById(`${prefix}_renovaciones_max`);
    const intervaloInput = document.getElementById(`${prefix}_renovaciones_intervalo`);
    const dispIntervalo = document.getElementById(`${prefix}_disp_intervalo`);

    if (minInput) minInput.value = minVal !== null ? minVal : '';
    if (maxInput) maxInput.value = maxVal !== null ? maxVal : '';
    if (intervaloInput) intervaloInput.value = intervaloVal;
    if (dispIntervalo) dispIntervalo.textContent = intervaloVal;

    recalcVentilation(prefix);
}

function recalcVentilation(prefix) {
    const velMsEl = document.getElementById(`${prefix}_vel_aire_ms`);
    const largoEl = document.getElementById(`${prefix}_area_largo_m`);
    const anchoEl = document.getElementById(`${prefix}_area_ancho_m`);
    const diametroEl = document.getElementById(`${prefix}_area_diametro_m`);

    const volLargoEl = document.getElementById(`${prefix}_vol_largo_m`);
    const volAnchoEl = document.getElementById(`${prefix}_vol_ancho_m`);
    const volAltoEl = document.getElementById(`${prefix}_vol_alto_m`);

    const velMs = parseFloat(velMsEl?.value) || 0.0;
    const velMh = velMs * 3600.0;

    const largo = parseFloat(largoEl?.value) || 0.0;
    const ancho = parseFloat(anchoEl?.value) || 0.0;
    const diametro = parseFloat(diametroEl?.value) || 0.0;

    // Fórmula Área de ventilación (en informe): =(DATOS!G3*DATOS!H3)+(3,1416*DATOS!I3)
    const areaM2 = (largo * ancho) + (3.1416 * diametro);
    
    // Caudal = F9 * G9 = vel_aire_ms * area_ventilacion
    const caudalM3h = velMs * areaM2;

    const volLargo = parseFloat(volLargoEl?.value) || 0.0;
    const volAncho = parseFloat(volAnchoEl?.value) || 0.0;
    const volAlto = parseFloat(volAltoEl?.value) || 0.0;

    // Volumen = J3*K3*L3
    const volumenM3 = (volLargo > 0 && volAncho > 0 && volAlto > 0) ? (volLargo * volAncho * volAlto) : 0.0;
    
    // Renovaciones/h en Datos: =SI.ERROR(3600*((F3*((G3*H3)+((3,1416/4)*(I3*I3))))/(J3*K3*L3));"")
    const areaRenov = (largo * ancho) + ((3.1416 / 4.0) * (diametro * diametro));
    const renovH = volumenM3 > 0 ? (3600.0 * ((velMs * areaRenov) / volumenM3)) : 0.0;

    // Actualizar visualizadores
    const dispArea = document.getElementById(`${prefix}_disp_area`);
    const dispCaudal = document.getElementById(`${prefix}_disp_caudal`);
    const dispVolumen = document.getElementById(`${prefix}_disp_volumen`);
    const dispRenov = document.getElementById(`${prefix}_disp_renov`);
    const dispCumple = document.getElementById(`${prefix}_disp_cumple`);
    const inputCumple = document.getElementById(`${prefix}_cumple`);
    const minInput = document.getElementById(`${prefix}_renovaciones_min`);

    if (dispArea) dispArea.textContent = `${areaM2.toFixed(4)} m²`;
    if (dispCaudal) dispCaudal.textContent = `${caudalM3h.toFixed(2)} m³/h`;
    if (dispVolumen) dispVolumen.textContent = `${volumenM3.toFixed(2)} m³`;
    if (dispRenov) dispRenov.textContent = `${renovH.toFixed(2)} /h`;

    const minVal = parseFloat(minInput?.value);
    let isCompliant = true;
    if (!isNaN(minVal) && minVal > 0) {
        isCompliant = renovH >= minVal;
    }

    const cumpleText = isCompliant ? 'CUMPLE' : 'NO CUMPLE';
    if (inputCumple) inputCumple.value = cumpleText;
    if (dispCumple) {
        dispCumple.textContent = cumpleText;
        if (isCompliant) {
            dispCumple.classList.remove('no-cumple');
        } else {
            dispCumple.classList.add('no-cumple');
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
        const disabled = document.getElementById(`${prefix}_measurement_date`)?.disabled;
        if (disabled) return;

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
        alert('La geolocalización no está soportada en este navegador.');
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
        alert('No se pudo obtener la ubicación GPS: ' + err.message);
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

    const viewport = document.getElementById(`${prefix}_slider_viewport`);
    const mainImg = document.getElementById(`${prefix}_slider_img`);
    const placeholder = document.getElementById(`${prefix}_slider_placeholder`);
    const counter = document.getElementById(`${prefix}_slider_counter`);
    const btnPrev = document.getElementById(`${prefix}_slider_btn_prev`);
    const btnNext = document.getElementById(`${prefix}_slider_btn_next`);
    const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
    const countIndicator = document.getElementById(`${prefix}_photo_count_indicator`);
    const delBtn = document.getElementById(`${prefix}_slider_del_btn`);

    const count = allPhotos.length;
    if (countIndicator) countIndicator.textContent = `${count} ${count === 1 ? 'foto' : 'fotos'}`;

    if (count === 0) {
        if (mainImg) mainImg.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
        if (counter) counter.style.display = 'none';
        if (btnPrev) btnPrev.style.display = 'none';
        if (btnNext) btnNext.style.display = 'none';
        if (thumbsStrip) thumbsStrip.style.display = 'none';
        if (delBtn) delBtn.style.display = 'none';
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

    if (prefix === 'edit' && delBtn) {
        const isEditMode = !document.getElementById('edit_measurement_date')?.disabled;
        delBtn.style.display = (isEditMode && count > 0) ? 'inline-flex' : 'none';
    }
}

function slidePhotoNav(prefix, dir) {
    const store = modalPhotoStore[prefix];
    const total = prefix === 'create' ? store.urls.length : (store.remainingUrls.length + store.urls.length);
    if (total <= 1) return;
    store.activeIndex = (store.activeIndex + dir + total) % total;
    renderModalPhotoSlider(prefix);
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
        const remInput = document.getElementById('edit_remaining_images');
        if (remInput) remInput.value = JSON.stringify(store.remainingUrls);
    } else {
        const newIdx = store.activeIndex - totalExisting;
        store.urls.splice(newIdx, 1);
        store.files.splice(newIdx, 1);
    }

    store.activeIndex = Math.max(0, store.activeIndex - 1);
    renderModalPhotoSlider('edit');
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

    onTipoLocalChange('create');
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
        form.action = `/modulos/${MODULE_ID}/ventilacion/mediciones/${data.id}`;
    }

    document.getElementById('edit_pt_num_disp').textContent = data.num || '01';
    document.getElementById('edit_point_number').value = data.num || '01';
    document.getElementById('edit_measurement_date').value = data.raw_date || '';
    document.getElementById('edit_measurement_time').value = data.time !== '—' ? data.time : '';
    document.getElementById('edit_local_trabajo').value = data.local_trabajo || '';
    
    const tipoLocalSelect = document.getElementById('edit_tipo_local');
    if (tipoLocalSelect) {
        tipoLocalSelect.value = data.tipo_local || 'Locales de trabajo en general';
    }

    document.getElementById('edit_tipo_ventilacion').value = data.tipo_ventilacion || 'Natural';
    document.getElementById('edit_elemento_ventilacion').value = data.elemento_ventilacion || 'Ventana';
    document.getElementById('edit_temperatura_seca_c').value = data.raw_temperatura_seca_c || '';
    document.getElementById('edit_vel_aire_ms').value = data.raw_vel_aire_ms || '';
    
    document.getElementById('edit_area_largo_m').value = data.area_largo_m || '';
    document.getElementById('edit_area_ancho_m').value = data.area_ancho_m || '';
    document.getElementById('edit_area_diametro_m').value = data.area_diametro_m || '';

    document.getElementById('edit_vol_largo_m').value = data.vol_largo_m || '';
    document.getElementById('edit_vol_ancho_m').value = data.vol_ancho_m || '';
    document.getElementById('edit_vol_alto_m').value = data.vol_alto_m || '';

    document.getElementById('edit_renovaciones_min').value = data.renovaciones_min || '';
    document.getElementById('edit_renovaciones_max').value = data.renovaciones_max || '';
    document.getElementById('edit_renovaciones_intervalo').value = data.renovaciones_intervalo || '';
    document.getElementById('edit_cumple').value = data.cumple || 'CUMPLE';

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

    if (data.staff_id && document.getElementById('edit_staff_id')) {
        document.getElementById('edit_staff_id').value = data.staff_id;
    }
    if (document.getElementById('edit_modal_registered_by')) {
        document.getElementById('edit_modal_registered_by').textContent = data.registered_by;
    }

    recalcVentilation('edit');

    // Inicializar fotos
    const rawImages = data.images || (data.image_path ? [data.image_path] : []);
    modalPhotoStore.edit = {
        files: [],
        existingUrls: [...rawImages],
        remainingUrls: [...rawImages],
        urls: [],
        activeIndex: 0
    };
    const remInput = document.getElementById('edit_remaining_images');
    if (remInput) remInput.value = JSON.stringify(rawImages);
    renderModalPhotoSlider('edit');

    // Inicializar mini-mapa
    initLeafletMiniMap('edit', data.latitude, data.longitude);

    // Forzar modo Sólo Lectura
    setModalReadOnlyMode(true);

    modal.classList.add('open');
}

function closeEditMeasurementModal() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) modal.classList.remove('open');
}

function toggleModalEditMode() {
    const dateInput = document.getElementById('edit_measurement_date');
    const isCurrentlyReadOnly = dateInput ? dateInput.disabled : true;
    setModalReadOnlyMode(!isCurrentlyReadOnly);
}

function setModalReadOnlyMode(isReadOnly) {
    const form = document.getElementById('editMeasurementForm');
    const badge = document.getElementById('modalModeStatusBadge');
    const toggleBtnText = document.getElementById('btnToggleEditModeText');
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    const gpsBtn = document.getElementById('edit_btn_gps');
    const delPhotoBtn = document.getElementById('edit_slider_del_btn');

    const fields = [
        'edit_measurement_date', 'edit_measurement_time', 'edit_staff_id', 'edit_local_trabajo',
        'edit_tipo_local', 'edit_tipo_ventilacion', 'edit_elemento_ventilacion', 'edit_temperatura_seca_c',
        'edit_vel_aire_ms', 'edit_area_largo_m', 'edit_area_ancho_m', 'edit_area_diametro_m',
        'edit_vol_largo_m', 'edit_vol_ancho_m', 'edit_vol_alto_m', 'edit_utm_easting',
        'edit_utm_northing', 'edit_utm_zone', 'edit_observations'
    ];

    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.disabled = isReadOnly;
    });

    if (badge) {
        badge.textContent = isReadOnly ? 'Solo Lectura' : 'Modo Edición';
        badge.className = isReadOnly ? 'modal-badge-view' : 'modal-badge-view' ;
        badge.style.background = isReadOnly ? '#f1f5f9' : '#dcfce7';
        badge.style.color = isReadOnly ? '#475569' : '#15803d';
        badge.style.borderColor = isReadOnly ? '#cbd5e1' : '#86efac';
    }

    if (toggleBtnText) toggleBtnText.textContent = isReadOnly ? 'Editar' : 'Cancelar Edición';
    if (submitBtn) submitBtn.style.display = isReadOnly ? 'none' : 'inline-flex';
    if (addPhotosBtn) addPhotosBtn.style.display = isReadOnly ? 'none' : 'inline-flex';
    if (gpsBtn) gpsBtn.style.display = isReadOnly ? 'none' : 'inline-flex';
    if (delPhotoBtn) {
        const hasPhotos = modalPhotoStore.edit.remainingUrls.length + modalPhotoStore.edit.urls.length > 0;
        delPhotoBtn.style.display = (!isReadOnly && hasPhotos) ? 'inline-flex' : 'none';
    }
}

function confirmDeleteMeasurement(id, pointNum) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar Punto?',
            text: `¿Estás seguro de eliminar el punto de ventilación #${pointNum}? Esta acción no se puede deshacer.`,
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
                    form.action = `/modulos/${MODULE_ID}/ventilacion/mediciones/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar el punto #${pointNum}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = `/modulos/${MODULE_ID}/ventilacion/mediciones/${id}`;
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

    document.getElementById('mapCardPointName').textContent = m.local_trabajo || 'Local de Trabajo';
    document.getElementById('mapCardLocationDesc').textContent = `${m.tipo_local || ''} — ${m.tipo_ventilacion} (${m.elemento_ventilacion})`;
    
    const badgeEl = document.getElementById('mapCardVentBadge');
    if (badgeEl) {
        badgeEl.textContent = `${m.renovaciones_h} /h`;
        badgeEl.className = `renov-val-badge ${m.is_compliant ? 'compliant' : 'non-compliant'}`;
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
                L.marker([lat, lng]).addTo(singlePointMapInstance).bindPopup(`<strong>#${m.num}: ${m.local_trabajo}</strong><br>${m.renovaciones_h} /h (${m.cumple})`).openPopup();
            }
        }
    }, 250);
}

function closeMapModal() {
    const modal = document.getElementById('mapLocationModal');
    if (modal) modal.classList.remove('open');
}

function addslashes(str) {
    if (!str) return '';
    return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
}

function extractLatLngFromItem(item) {
    let lat = (item.latitude !== null && item.latitude !== undefined && item.latitude !== '') ? parseFloat(item.latitude) : NaN;
    let lng = (item.longitude !== null && item.longitude !== undefined && item.longitude !== '') ? parseFloat(item.longitude) : NaN;

    if (isNaN(lat) || isNaN(lng)) {
        if (item.location && typeof item.location === 'string') {
            const loc = item.location.trim();
            if (loc.startsWith('{') || loc.startsWith('[')) {
                try {
                    const parsed = JSON.parse(loc);
                    if (parsed.latitude && parsed.longitude) {
                        lat = parseFloat(parsed.latitude);
                        lng = parseFloat(parsed.longitude);
                    } else if (parsed.lat && parsed.lng) {
                        lat = parseFloat(parsed.lat);
                        lng = parseFloat(parsed.lng);
                    } else if (parsed.easting && parsed.northing) {
                        const pos = utmToLatLngJS(parseFloat(parsed.easting), parseFloat(parsed.northing), parsed.utm_zone || parsed.zone || '20K');
                        lat = pos.lat;
                        lng = pos.lng;
                    }
                } catch(e) {}
            } else {
                const mE = loc.match(/E:\s*([0-9.]+)/i);
                const mN = loc.match(/N:\s*([0-9.]+)/i);
                const mZ = loc.match(/Z:\s*([0-9A-Za-z]+)/i);
                if (mE && mN) {
                    const pos = utmToLatLngJS(parseFloat(mE[1]), parseFloat(mN[1]), mZ ? mZ[1] : '20K');
                    lat = pos.lat;
                    lng = pos.lng;
                }
            }
        }
    }

    return {
        lat: !isNaN(lat) ? lat : -16.5034120,
        lng: !isNaN(lng) ? lng : -68.1324560,
        isValid: !isNaN(lat) && !isNaN(lng)
    };
}

function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');

    setTimeout(() => {
        initAllLocationsMap();
    }, 200);
}

function initAllLocationsMap() {
    const container = document.getElementById('allLocationsMapLeaflet');
    if (!container) return;

    if (!allLocationsMapInstance) {
        allLocationsMapInstance = L.map('allLocationsMapLeaflet').setView([-16.503412, -68.132456], 15);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(allLocationsMapInstance);
        allLocationsMarkersLayer = L.featureGroup().addTo(allLocationsMapInstance);
    } else {
        allLocationsMapInstance.invalidateSize();
    }

    allLocationsMarkersLayer.clearLayers();
    allLocationsMarkersList = [];

    const bounds = [];

    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        const coords = extractLatLngFromItem(m);
        let lat = coords.lat;
        let lng = coords.lng;

        if (!coords.isValid) {
            lat = -16.503412 + (idx * 0.00035);
            lng = -68.132456 + (idx * 0.00035);
        }

        bounds.push([lat, lng]);

        const pinHtml = `
            <div class="custom-map-pin ${m.is_compliant ? 'pin-compliant' : 'pin-non-compliant'}" title="Punto #${m.num}">
                ${m.num}
            </div>
        `;

        const customIcon = L.divIcon({
            html: pinHtml,
            className: 'custom-div-pin-wrapper',
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -16]
        });

        let photoSection = '';
        const imgUrl = m.image_path || (Array.isArray(m.images) && m.images.length > 0 ? m.images[0] : null);
        if (imgUrl) {
            photoSection = `
                <div style="margin-top: 6px; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; cursor: pointer;" onclick="openPhotoViewer('${imgUrl}', 'Punto #${m.num}: ${addslashes(m.local_trabajo)}')">
                    <img src="${imgUrl}" style="width: 100%; height: 90px; object-fit: cover; display: block;">
                </div>
            `;
        }

        const popupHtml = `
            <div style="font-family: 'Outfit', sans-serif; font-size: 12px; line-height: 1.4; min-width: 220px; max-width: 270px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">
                    <strong style="font-size: 13px; color: #0f172a;">Punto #${m.num}</strong>
                    <span style="font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: ${m.is_compliant ? '#ecfdf5' : '#fef2f2'}; color: ${m.is_compliant ? '#065f46' : '#991b1b'}; border: 1px solid ${m.is_compliant ? '#a7f3d0' : '#fecaca'};">
                        ${m.is_compliant ? 'CUMPLE' : 'NO CUMPLE'}
                    </span>
                </div>
                <div style="font-weight: 700; color: #1e293b; margin-bottom: 2px;">${m.local_trabajo}</div>
                <div style="font-size: 11.5px; color: #64748b; margin-bottom: 6px;">${m.tipo_local || ''} • ${m.tipo_ventilacion || ''} (${m.elemento_ventilacion || ''})</div>

                <div style="display: flex; align-items: center; gap: 6px; background: #f0f9ff; border: 1px solid #bae6fd; padding: 4px 8px; border-radius: 6px; margin-bottom: 6px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span style="font-size: 11px; color: #0369a1;">Registrado por: <strong>${m.registered_by}</strong></span>
                </div>

                <div style="font-size: 11.5px; margin-bottom: 4px;">
                    <span>Renovaciones: <strong>${m.renovaciones_h} /h</strong></span>
                    <span style="color: #64748b;">(Ref: ${m.valor_referencial_renov_h} /h)</span>
                </div>
                <div style="font-size: 11.5px; color: #64748b; margin-bottom: 6px;">
                    Vel: <strong>${m.vel_aire_ms} m/s</strong> • Caudal: <strong>${m.caudal_m3h} m³/h</strong>
                </div>

                ${photoSection}
            </div>
        `;

        const marker = L.marker([lat, lng], { icon: customIcon })
            .bindPopup(popupHtml);

        allLocationsMarkersLayer.addLayer(marker);
        allLocationsMarkersList.push(marker);
    });

    if (bounds.length > 0) {
        allLocationsMapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 17 });
    }
}

function closeAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) modal.classList.remove('open');
}

function focusPointOnAllLocationsMap(index) {
    if (!allLocationsMarkersList[index] || !allLocationsMapInstance) return;
    const marker = allLocationsMarkersList[index];
    allLocationsMapInstance.setView(marker.getLatLng(), 17, { animate: true });
    marker.openPopup();
}

function openPhotoViewer(url, title) {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const titleEl = document.getElementById('photoViewerTitle');
    if (!modal || !img) return;

    img.src = url;
    if (titleEl) titleEl.textContent = title || 'Evidencia Fotográfica';
    modal.classList.add('open');
}

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.classList.remove('open');
}

function expandCurrentMapPhoto() {
    const img = document.getElementById('mapModalPhotoImg');
    if (img && img.src) {
        openPhotoViewer(img.src, 'Fotografía de la Ubicación');
    }
}

/* ==========================================================================
   6. AUTO-GUARDADO ENCABEZADO TÉCNICO
   ========================================================================== */
function autoSaveHeaderField() {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.classList.add('visible', 'saving');
        badge.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> <span>Guardando...</span>`;
    }

    const payload = {
        _token: CSRF_TOKEN,
        installation_name: document.getElementById('inline_installation_name')?.value || '',
        start_date: document.getElementById('inline_start_date')?.value || '',
        end_date: document.getElementById('inline_end_date')?.value || '',
        monitoring_type: document.getElementById('inline_monitoring_type')?.value || ''
    };

    fetch(UPDATE_HEADER_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (badge) {
            badge.classList.remove('saving');
            badge.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span>Guardado</span>`;
            setTimeout(() => { badge.classList.remove('visible'); }, 2000);
        }
    })
    .catch(err => {
        if (badge) {
            badge.classList.remove('saving');
            badge.innerHTML = `<span style="color: #dc2626;">Error al guardar</span>`;
        }
    });
}

/* ==========================================================================
   7. FILTROS Y BÚSQUEDA REACTIVA EN TABLA
   ========================================================================== */
function filterVentilation(type, btn) {
    currentFilterType = type;
    const group = document.getElementById('ventilationFilterGroup');
    if (group) {
        group.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('active'));
    }
    if (btn) btn.classList.add('active');
    applyTableFiltersAndPagination();
}

function searchVentilationLive() {
    const input = document.getElementById('ventilationSearchInput');
    currentSearchQuery = input ? input.value.trim().toLowerCase() : '';
    applyTableFiltersAndPagination();
}

function applyTableFiltersAndPagination() {
    const rows = Array.from(document.querySelectorAll('.ventilation-data-row'));
    let matchedRows = [];

    rows.forEach(row => {
        const compliant = row.dataset.compliant;
        const ventType = row.dataset.type;
        const search = row.dataset.search || '';

        let matchFilter = true;
        if (currentFilterType === 'compliant' && compliant !== 'compliant') matchFilter = false;
        if (currentFilterType === 'non-compliant' && compliant !== 'non-compliant') matchFilter = false;
        if (currentFilterType === 'Natural' && ventType !== 'Natural') matchFilter = false;
        if (currentFilterType === 'Mecánica' && ventType !== 'Mecánica') matchFilter = false;

        let matchSearch = true;
        if (currentSearchQuery && !search.includes(currentSearchQuery)) matchSearch = false;

        if (matchFilter && matchSearch) {
            matchedRows.push(row);
        } else {
            row.style.display = 'none';
        }
    });

    const totalMatched = matchedRows.length;
    const totalPages = Math.ceil(totalMatched / ROWS_PER_PAGE) || 1;
    if (currentPage > totalPages) currentPage = 1;

    const startIdx = (currentPage - 1) * ROWS_PER_PAGE;
    const endIdx = startIdx + ROWS_PER_PAGE;

    matchedRows.forEach((row, idx) => {
        if (idx >= startIdx && idx < endIdx) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });

    const noResultsRow = document.getElementById('noResultsSearchRow');
    if (noResultsRow) {
        noResultsRow.style.display = (totalMatched === 0 && rows.length > 0) ? '' : 'none';
    }

    // Actualizar barra de paginación
    const pageStart = document.getElementById('ventPageStart');
    const pageEnd = document.getElementById('ventPageEnd');
    const pageTotal = document.getElementById('ventPageTotal');
    if (pageStart) pageStart.textContent = totalMatched > 0 ? (startIdx + 1) : 0;
    if (pageEnd) pageEnd.textContent = Math.min(endIdx, totalMatched);
    if (pageTotal) pageTotal.textContent = totalMatched;

    renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
    const container = document.getElementById('ventilationPaginationControls');
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    // Prev
    const prevBtn = document.createElement('button');
    prevBtn.className = 'page-btn';
    prevBtn.innerHTML = '❮';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => { currentPage--; applyTableFiltersAndPagination(); };
    container.appendChild(prevBtn);

    // Page Numbers
    for (let p = 1; p <= totalPages; p++) {
        const pageBtn = document.createElement('button');
        pageBtn.className = `page-btn ${p === currentPage ? 'active' : ''}`;
        pageBtn.textContent = p;
        pageBtn.onclick = () => { currentPage = p; applyTableFiltersAndPagination(); };
        container.appendChild(pageBtn);
    }

    // Next
    const nextBtn = document.createElement('button');
    nextBtn.className = 'page-btn';
    nextBtn.innerHTML = '❯';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => { currentPage++; applyTableFiltersAndPagination(); };
    container.appendChild(nextBtn);
}

/* ==========================================================================
   8. EXPORTACIÓN EXCEL (.XLSX) CON EXCELJS
   ========================================================================== */
function openExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.add('open');
}

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.remove('open');
}

async function downloadExcelPlanilla() {
    if (typeof ExcelJS === 'undefined') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Inicializando librería...',
                text: 'La librería de exportación se está cargando. Por favor reintenta en un momento.',
                icon: 'info',
                confirmButtonText: 'Aceptar',
                customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
            });
        } else {
            alert('Cargando librería ExcelJS. Por favor intenta de nuevo en unos segundos.');
        }
        return;
    }

    try {
        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'METRIC v2 Pachabol';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Planilla de Ventilación', {
            views: [{ showGridLines: true }],
            pageSetup: { orientation: 'landscape', paperSize: 9 }
        });

        // 13 Columnas en total (A hasta M) exactamente como en el formato oficial
        sheet.columns = [
            { key: 'num', width: 6 },                   // A: NRO.
            { key: 'local', width: 28 },                 // B: LOCAL DE TRABAJO
            { key: 'tipo_vent', width: 18 },             // C: TIPO DE VENTILACIÓN
            { key: 'fuente', width: 20 },                // D: FUENTE
            { key: 'temp_seca', width: 14 },             // E: TEMPERATURA SECA
            { key: 'vel_aire', width: 15 },              // F: VELOCIDAD DE AIRE [m/s]
            { key: 'area_vent', width: 15 },             // G: ÁREA DE VENTILACIÓN [m2]
            { key: 'caudal', width: 18 },                // H: CAUDAL DE EXTRACCIÓN O INYECCIÓN [m3/h]
            { key: 'volumen', width: 16 },               // I: VOLUMEN DEL AMBIENTE [m3]
            { key: 'renovaciones', width: 18 },          // J: NRO. DE RENOVACIONES POR HORA
            { key: 'val_ref', width: 14 },               // K: VALOR REFERENCIAL
            { key: 'cumple', width: 10 },                // L: ¿CUMPLE?
            { key: 'obs', width: 32 }                    // M: OBSERVACIONES
        ];

        const thinBorder = {
            top: { style: 'thin', color: { argb: 'FF000000' } },
            left: { style: 'thin', color: { argb: 'FF000000' } },
            bottom: { style: 'thin', color: { argb: 'FF000000' } },
            right: { style: 'thin', color: { argb: 'FF000000' } }
        };

        const applyBoxBorder = (startRow, startCol, endRow, endCol) => {
            for (let r = startRow; r <= endRow; r++) {
                for (let c = startCol; c <= endCol; c++) {
                    const cell = sheet.getCell(r, c);
                    cell.border = thinBorder;
                }
            }
        };

        // FILA 1: Encabezado Título Banner Pastel #D9E1F2
        sheet.mergeCells('A1:M1');
        const r1 = sheet.getCell('A1');
        r1.value = 'PLANILLA DE MEDICIÓN Y EVALUACIÓN DE VENTILACIÓN';
        r1.font = { name: 'Calibri', size: 12, bold: true, color: { argb: 'FF000000' } };
        r1.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD9E1F2' } };
        r1.alignment = { vertical: 'middle', horizontal: 'center' };
        sheet.getRow(1).height = 28;
        applyBoxBorder(1, 1, 1, 13);

        // FILA 2: Espacio
        sheet.getRow(2).height = 8;

        // FILAS 3 A 6: Encabezados Técnicos Duales (Empresa/Proyecto vs Equipo)
        const instName = document.getElementById('inline_installation_name')?.value || TECHNICAL_HEADER_DATA.installationName || 'PAPELBOL - VILLA TUNARI';
        const stDate = TECHNICAL_HEADER_DATA.startDateFormatted || '23/7/2026';
        const enDate = TECHNICAL_HEADER_DATA.endDateFormatted || '24/7/2026';
        const monType = document.getElementById('inline_monitoring_type')?.value || TECHNICAL_HEADER_DATA.monitoringType || 'RUTINARIO:     SEGUIMIENTO: x';
        const eqName = TECHNICAL_HEADER_DATA.equipmentName || 'ANEMÓMETRO - EXTECH';
        const eqBrand = TECHNICAL_HEADER_DATA.equipmentBrand || 'EXTECH';
        const eqModel = TECHNICAL_HEADER_DATA.equipmentModel || 'AN100';
        const eqSerial = TECHNICAL_HEADER_DATA.equipmentSerial || '211111005';

        // Fila 3
        sheet.mergeCells('A3:D3');
        sheet.mergeCells('E3:G3');
        sheet.mergeCells('H3:I3');
        sheet.mergeCells('J3:M3');
        sheet.getCell('A3').value = 'EMPRESA / PROYECTO:';
        sheet.getCell('E3').value = instName;
        sheet.getCell('H3').value = 'EQUIPO:';
        sheet.getCell('J3').value = eqName;

        // Fila 4
        sheet.mergeCells('A4:D4');
        sheet.mergeCells('E4:G4');
        sheet.mergeCells('H4:I4');
        sheet.mergeCells('J4:M4');
        sheet.getCell('A4').value = 'FECHA DE INICIO DEL MONITOREO:';
        sheet.getCell('E4').value = stDate;
        sheet.getCell('H4').value = 'MARCA:';
        sheet.getCell('J4').value = eqBrand;

        // Fila 5
        sheet.mergeCells('A5:D5');
        sheet.mergeCells('E5:G5');
        sheet.mergeCells('H5:I5');
        sheet.mergeCells('J5:M5');
        sheet.getCell('A5').value = 'FECHA DE FINALIZACIÓN DEL MONITOREO:';
        sheet.getCell('E5').value = enDate;
        sheet.getCell('H5').value = 'MODELO:';
        sheet.getCell('J5').value = eqModel;

        // Fila 6
        sheet.mergeCells('A6:D6');
        sheet.mergeCells('E6:G6');
        sheet.mergeCells('H6:I6');
        sheet.mergeCells('J6:M6');
        sheet.getCell('A6').value = 'TIPO DE MONITOREO:';
        sheet.getCell('E6').value = monType;
        sheet.getCell('H6').value = 'SERIE:';
        sheet.getCell('J6').value = eqSerial;

        // Estilos para Filas 3 a 6
        for (let r = 3; r <= 6; r++) {
            sheet.getRow(r).height = 20;
            applyBoxBorder(r, 1, r, 13);
            
            const cellLabelLeft = sheet.getCell(r, 1);
            cellLabelLeft.font = { name: 'Calibri', size: 9, bold: true };
            cellLabelLeft.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

            const cellValLeft = sheet.getCell(r, 5);
            cellValLeft.font = { name: 'Calibri', size: 9, bold: false };
            cellValLeft.alignment = { vertical: 'middle', horizontal: 'center' };

            const cellLabelRight = sheet.getCell(r, 8);
            cellLabelRight.font = { name: 'Calibri', size: 9, bold: true };
            cellLabelRight.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

            const cellValRight = sheet.getCell(r, 10);
            cellValRight.font = { name: 'Calibri', size: 9, bold: false };
            cellValRight.alignment = { vertical: 'middle', horizontal: 'center' };
        }

        // FILA 7: EVALUACIÓN DE RIESGOS
        sheet.mergeCells('A7:M7');
        const r7 = sheet.getCell('A7');
        r7.value = 'EVALUACIÓN DE RIESGOS';
        r7.font = { name: 'Calibri', size: 10, bold: true, color: { argb: 'FF000000' } };
        r7.alignment = { vertical: 'middle', horizontal: 'center' };
        sheet.getRow(7).height = 22;
        applyBoxBorder(7, 1, 7, 13);

        // FILA 8: Encabezados de Columnas de la Tabla
        const colHeaders = [
            'NRO.',
            'LOCAL DE TRABAJO',
            'TIPO DE VENTILACIÓN',
            'FUENTE',
            'TEMPERATURA SECA',
            'VELOCIDAD DE AIRE [m/s]',
            'ÁREA DE VENTILACIÓN [m2]',
            'CAUDAL DE EXTRACCIÓN O INYECCIÓN [m3/h]',
            'VOLUMEN DEL AMBIENTE [m3]',
            'NRO. DE RENOVACIONES POR HORA',
            'VALOR REFERENCIAL',
            '¿CUMPLE?',
            'OBSERVACIONES'
        ];

        const headerRow = sheet.getRow(8);
        headerRow.height = 36;
        colHeaders.forEach((title, idx) => {
            const cell = headerRow.getCell(idx + 1);
            cell.value = title;
            cell.font = { name: 'Calibri', size: 9, bold: true, color: { argb: 'FF000000' } };
            cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD9E1F2' } };
            cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
            cell.border = thinBorder;
        });

        // FILAS 9+: Registros de Medición
        ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
            const rowNum = 9 + idx;
            const r = sheet.getRow(rowNum);
            r.height = 22;

            const tempSeca = (m.raw_temperatura_seca_c !== null && m.raw_temperatura_seca_c !== undefined && !isNaN(parseFloat(m.raw_temperatura_seca_c))) ? parseFloat(m.raw_temperatura_seca_c) : null;
            const velMs = (m.raw_vel_aire_ms !== null && m.raw_vel_aire_ms !== undefined && !isNaN(parseFloat(m.raw_vel_aire_ms))) ? parseFloat(m.raw_vel_aire_ms) : 0;
            const areaM2 = (m.raw_area_ventilacion_m2 !== null && m.raw_area_ventilacion_m2 !== undefined && !isNaN(parseFloat(m.raw_area_ventilacion_m2))) ? parseFloat(m.raw_area_ventilacion_m2) : 0;
            const caudal = (m.raw_caudal_m3h !== null && m.raw_caudal_m3h !== undefined && !isNaN(parseFloat(m.raw_caudal_m3h))) ? parseFloat(m.raw_caudal_m3h) : 0;
            const volumen = (m.raw_volumen_m3 !== null && m.raw_volumen_m3 !== undefined && !isNaN(parseFloat(m.raw_volumen_m3))) ? parseFloat(m.raw_volumen_m3) : 0;
            const renov = (m.raw_renovaciones_h !== null && m.raw_renovaciones_h !== undefined && !isNaN(parseFloat(m.raw_renovaciones_h))) ? parseFloat(m.raw_renovaciones_h) : 0;
            const cumpleStr = m.cumple || (m.is_compliant ? 'SI' : 'NO');
            const obsText = (m.raw_observations && m.raw_observations !== 'Sin observaciones') 
                ? m.raw_observations 
                : ((m.observations && m.observations !== 'Sin observaciones') ? m.observations : '');

            // 1. NRO.
            r.getCell(1).value = parseInt(m.num, 10) || (idx + 1);
            r.getCell(1).alignment = { vertical: 'middle', horizontal: 'center' };

            // 2. LOCAL DE TRABAJO
            r.getCell(2).value = m.local_trabajo || '';
            r.getCell(2).alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

            // 3. TIPO DE VENTILACIÓN
            r.getCell(3).value = m.tipo_ventilacion || 'Natural';
            r.getCell(3).alignment = { vertical: 'middle', horizontal: 'center' };

            // 4. FUENTE
            r.getCell(4).value = m.elemento_ventilacion || 'Ventana';
            r.getCell(4).alignment = { vertical: 'middle', horizontal: 'center' };

            // 5. TEMPERATURA SECA
            if (tempSeca !== null) {
                r.getCell(5).value = tempSeca;
                r.getCell(5).numFmt = '#,##0.00';
            } else {
                r.getCell(5).value = '—';
            }
            r.getCell(5).alignment = { vertical: 'middle', horizontal: 'center' };

            // 6. VELOCIDAD DE AIRE [m/s]
            r.getCell(6).value = velMs;
            r.getCell(6).numFmt = '#,##0.00';
            r.getCell(6).alignment = { vertical: 'middle', horizontal: 'center' };

            // 7. ÁREA DE VENTILACIÓN [m2]
            r.getCell(7).value = areaM2;
            r.getCell(7).numFmt = '#,##0.00';
            r.getCell(7).alignment = { vertical: 'middle', horizontal: 'center' };

            // 8. CAUDAL DE EXTRACCIÓN O INYECCIÓN [m3/h]
            r.getCell(8).value = caudal;
            r.getCell(8).numFmt = '#,##0.00';
            r.getCell(8).alignment = { vertical: 'middle', horizontal: 'center' };

            // 9. VOLUMEN DEL AMBIENTE [m3]
            r.getCell(9).value = volumen;
            r.getCell(9).numFmt = '#,##0.00';
            r.getCell(9).alignment = { vertical: 'middle', horizontal: 'center' };

            // 10. NRO. DE RENOVACIONES POR HORA
            r.getCell(10).value = renov;
            r.getCell(10).numFmt = '#,##0.00';
            r.getCell(10).alignment = { vertical: 'middle', horizontal: 'center' };

            // 11. VALOR REFERENCIAL
            r.getCell(11).value = m.renovaciones_intervalo || '—';
            r.getCell(11).alignment = { vertical: 'middle', horizontal: 'center' };

            // 12. ¿CUMPLE?
            r.getCell(12).value = cumpleStr;
            r.getCell(12).alignment = { vertical: 'middle', horizontal: 'center' };

            // 13. OBSERVACIONES
            r.getCell(13).value = obsText;
            r.getCell(13).alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

            for (let c = 1; c <= 13; c++) {
                const cell = r.getCell(c);
                cell.font = { name: 'Calibri', size: 9, color: { argb: 'FF000000' } };
                cell.border = thinBorder;
            }
        });

        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `Planilla_Ventilacion_${MODULE_ID}_${new Date().toISOString().slice(0, 10)}.xlsx`;
        link.click();

        closeExportModal();
    } catch (err) {
        console.error('Error generando Excel:', err);
        alert('Error al generar la planilla Excel: ' + err.message);
    }
}

/* ==========================================================================
   9. REPORTE FOTOGRÁFICO MOSAICO Y PDF
   ========================================================================== */
function openPhotoReportModal() {
    closeExportModal();
    const modal = document.getElementById('photoReportModal');
    if (!modal) return;

    renderPhotoReportInteractive();
    renderPhotoReportSheets();
    modal.classList.add('open');
}

function closePhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) modal.classList.remove('open');
}

function switchPhotoReportTab(tab) {
    const btnInteractive = document.getElementById('tabBtnInteractive');
    const btnSheets = document.getElementById('tabBtnSheets');
    const viewInteractive = document.getElementById('photoInteractiveView');
    const viewSheets = document.getElementById('photoSheetsView');

    if (tab === 'interactive') {
        btnInteractive.classList.add('active');
        btnSheets.classList.remove('active');
        viewInteractive.style.display = 'block';
        viewSheets.style.display = 'none';
    } else {
        btnSheets.classList.add('active');
        btnInteractive.classList.remove('active');
        viewSheets.style.display = 'block';
        viewInteractive.style.display = 'none';
        renderPhotoReportSheets();
    }
}

function changeGridDistribution(grid) {
    currentGrid = grid;
    const container = document.getElementById('gridDistSelector');
    if (container) {
        container.querySelectorAll('.btn-grid-dist').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.grid === grid);
        });
    }
    renderPhotoReportSheets();
}

function selectAllPoints(selectVal) {
    if (selectVal) {
        selectedPoints = ALL_MEASUREMENTS_DATA.map(m => m.id);
    } else {
        selectedPoints = [];
    }
    renderPhotoReportInteractive();
    renderPhotoReportSheets();
}

function renderPhotoReportInteractive() {
    const container = document.getElementById('photoInteractiveGridContainer');
    const countPill = document.getElementById('photoSelectionCountText');
    if (!container) return;

    container.innerHTML = '';
    const totalSelected = selectedPoints.length;
    if (countPill) countPill.textContent = `${totalSelected} de ${ALL_MEASUREMENTS_DATA.length} seleccionadas`;

    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        const isSelected = selectedPoints.includes(m.id);
        const card = document.createElement('div');
        card.className = `photo-card-interactive ${isSelected ? '' : 'excluded'}`;

        const rawImages = m.images || (m.image_path ? [m.image_path] : []);
        const chosenIdx = photoIndices[m.id] || 0;
        const currentPhoto = rawImages[chosenIdx] || m.image_path || '';

        card.innerHTML = `
            <div class="photo-card-header">
                <label style="display: flex; align-items: center; gap: 6px; font-weight: 800; cursor: pointer;">
                    <input type="checkbox" ${isSelected ? 'checked' : ''} onchange="togglePointSelection(${m.id}, this.checked)">
                    <span>Punto #${m.num}: ${m.local_trabajo}</span>
                </label>
                <span class="renov-val-badge ${m.is_compliant ? 'compliant' : 'non-compliant'}" style="font-size: 11px;">
                    ${m.renovaciones_h} /h
                </span>
            </div>
            <div class="photo-card-body-img">
                ${currentPhoto ? `<img src="${currentPhoto}" alt="Foto punto">` : `<span style="color: #94a3b8; font-size: 12px;">Sin Foto</span>`}
                ${rawImages.length > 1 ? `
                    <div style="position: absolute; bottom: 6px; right: 6px; display: flex; gap: 4px;">
                        <button type="button" class="slider-nav-btn prev" style="position: static; transform: none; width: 26px; height: 26px; font-size: 11px;" onclick="cyclePointPhoto(${m.id}, -1)">❮</button>
                        <span style="background: rgba(0,0,0,0.7); color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 4px;">${chosenIdx + 1}/${rawImages.length}</span>
                        <button type="button" class="slider-nav-btn next" style="position: static; transform: none; width: 26px; height: 26px; font-size: 11px;" onclick="cyclePointPhoto(${m.id}, 1)">❯</button>
                    </div>
                ` : ''}
            </div>
            <div class="photo-card-footer">
                <div><strong>Tipo:</strong> ${m.tipo_local} — ${m.tipo_ventilacion}</div>
                <div><strong>Caudal:</strong> ${m.caudal_m3h} m³/h | <strong>Vel:</strong> ${m.vel_aire_ms} m/s</div>
                <div><strong>Ref:</strong> ${m.renovaciones_intervalo} (${m.cumple})</div>
            </div>
        `;

        container.appendChild(card);
    });
}

function togglePointSelection(id, checked) {
    if (checked) {
        if (!selectedPoints.includes(id)) selectedPoints.push(id);
    } else {
        selectedPoints = selectedPoints.filter(pId => pId !== id);
    }
    renderPhotoReportInteractive();
    renderPhotoReportSheets();
}

function cyclePointPhoto(id, dir) {
    const point = ALL_MEASUREMENTS_DATA.find(m => m.id === id);
    if (!point) return;
    const rawImages = point.images || (point.image_path ? [point.image_path] : []);
    if (rawImages.length <= 1) return;

    let current = photoIndices[id] || 0;
    current = (current + dir + rawImages.length) % rawImages.length;
    photoIndices[id] = current;

    renderPhotoReportInteractive();
    renderPhotoReportSheets();
}

function renderPhotoReportSheets() {
    const container = document.getElementById('photoSheetsContainer');
    const printArea = document.getElementById('photoReportPrintArea');
    const pagesInd = document.getElementById('photoReportPagesIndicator');
    if (!container) return;

    container.innerHTML = '';
    if (printArea) printArea.innerHTML = '';

    const activePoints = ALL_MEASUREMENTS_DATA.filter(m => selectedPoints.includes(m.id));
    const itemsPerSheet = { '2x3': 6, '2x4': 8, '3x3': 9, '3x4': 12 }[currentGrid] || 6;
    const totalSheets = Math.ceil(activePoints.length / itemsPerSheet) || 1;

    if (pagesInd) pagesInd.textContent = `Hojas calculadas: ${totalSheets}`;

    for (let s = 0; s < totalSheets; s++) {
        const sheetPoints = activePoints.slice(s * itemsPerSheet, (s + 1) * itemsPerSheet);
        const sheetEl = createPhotoSheetElement(s + 1, totalSheets, sheetPoints);
        container.appendChild(sheetEl);

        if (printArea) {
            printArea.appendChild(sheetEl.cloneNode(true));
        }
    }
}

function createPhotoSheetElement(sheetNum, totalSheets, points) {
    const sheet = document.createElement('div');
    sheet.className = 'photo-report-sheet';

    let mosaicCellsHtml = '';
    points.forEach(m => {
        const rawImages = m.images || (m.image_path ? [m.image_path] : []);
        const chosenIdx = photoIndices[m.id] || 0;
        const currentPhoto = rawImages[chosenIdx] || m.image_path || '';

        mosaicCellsHtml += `
            <div class="mosaic-cell">
                <div class="mosaic-cell-img">
                    ${currentPhoto ? `<img src="${currentPhoto}" alt="Foto">` : `<span style="font-size: 11px; color: #94a3b8;">Sin fotografía</span>`}
                </div>
                <div class="mosaic-cell-info">
                    <div style="font-weight: 800; color: #0f172a;">Punto #${m.num}: ${m.local_trabajo}</div>
                    <div style="color: #64748b;">${m.tipo_local} — ${m.tipo_ventilacion} (${m.elemento_ventilacion})</div>
                    <div><strong>Renov/h:</strong> ${m.renovaciones_h} /h | <strong>Ref:</strong> ${m.renovaciones_intervalo} (<strong>${m.cumple}</strong>)</div>
                    <div><strong>Caudal:</strong> ${m.caudal_m3h} m³/h | <strong>Vel:</strong> ${m.vel_aire_ms} m/s</div>
                    <div style="color: #0284c7; font-size: 9px;">Reg: ${m.registered_by}</div>
                </div>
            </div>
        `;
    });

    sheet.innerHTML = `
        <div class="sheet-header-corp">
            <div>
                <strong style="font-size: 14px; color: #0f172a; text-transform: uppercase;">REPORTE FOTOGRÁFICO — MONITOREO DE VENTILACIÓN</strong>
                <div style="font-size: 11px; color: #64748b;">${TECHNICAL_HEADER_DATA.installationName || 'Instalación'} • ${TECHNICAL_HEADER_DATA.startDateFormatted || ''}</div>
            </div>
            <div style="text-align: right; font-size: 10px; color: #64748b;">
                <div><strong>METRIC V2</strong> PACHABOL</div>
                <div>Página ${sheetNum} de ${totalSheets}</div>
            </div>
        </div>

        <div class="sheet-grid-mosaic grid-${currentGrid}">
            ${mosaicCellsHtml}
        </div>

        <div class="sheet-footer-corp">
            <span>Equipo: ${TECHNICAL_HEADER_DATA.equipmentName || 'Termo-Anemómetro'} • S/N: ${TECHNICAL_HEADER_DATA.equipmentSerial || '—'}</span>
            <span>Estudio Técnico de Higiene y Seguridad Ocupacional</span>
        </div>
    `;

    return sheet;
}

function savePhotoReportSettingsToServer(showAlert = false) {
    const payload = {
        _token: CSRF_TOKEN,
        grid: currentGrid,
        selected_points: selectedPoints,
        photo_indices: photoIndices
    };

    fetch(`/modulos/${MODULE_ID}/ventilacion/photo-report-settings`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (showAlert && typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¡Configuración Guardada!',
                text: 'La distribución y selección del reporte fotográfico se guardaron correctamente.',
                icon: 'success',
                timer: 2500,
                showConfirmButton: false
            });
        }
    });
}

function printPhotoReport() {
    renderPhotoReportSheets();
    window.print();
}

/* ==========================================================================
   9.1. MODAL DE TABLAS Y EVALUACIÓN DE RIESGOS (PESTAÑAS DATOS & INFORME)
   ========================================================================== */
function openVentilationTablesModal() {
    const modal = document.getElementById('ventilationTablesModal');
    if (modal) {
        modal.classList.add('open');
        switchVentilationTableTab('informe');
    }
}

function closeVentilationTablesModal() {
    const modal = document.getElementById('ventilationTablesModal');
    if (modal) modal.classList.remove('open');
}

function switchVentilationTableTab(tabName) {
    const btnDatos = document.getElementById('tabBtn_datos');
    const btnInforme = document.getElementById('tabBtn_informe');
    const panelDatos = document.getElementById('panel_ventilation_datos');
    const panelInforme = document.getElementById('panel_ventilation_informe');

    if (tabName === 'datos') {
        if (btnDatos) btnDatos.classList.add('active');
        if (btnInforme) btnInforme.classList.remove('active');
        if (panelDatos) {
            panelDatos.classList.add('active');
            panelDatos.style.display = 'block';
        }
        if (panelInforme) {
            panelInforme.classList.remove('active');
            panelInforme.style.display = 'none';
        }
    } else {
        if (btnDatos) btnDatos.classList.remove('active');
        if (btnInforme) btnInforme.classList.add('active');
        if (panelDatos) {
            panelDatos.classList.remove('active');
            panelDatos.style.display = 'none';
        }
        if (panelInforme) {
            panelInforme.classList.add('active');
            panelInforme.style.display = 'block';
        }
    }
}

/* ==========================================================================
   10. EXPOSICIÓN GLOBAL A WINDOW
   ========================================================================== */
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.openViewMeasurementModal = openViewMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.toggleModalEditMode = toggleModalEditMode;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.openMapModal = openMapModal;
window.closeMapModal = closeMapModal;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewer = closePhotoViewer;
window.expandCurrentMapPhoto = expandCurrentMapPhoto;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.downloadExcelPlanilla = downloadExcelPlanilla;
window.openPhotoReportModal = openPhotoReportModal;
window.closePhotoReportModal = closePhotoReportModal;
window.switchPhotoReportTab = switchPhotoReportTab;
window.changeGridDistribution = changeGridDistribution;
window.selectAllPoints = selectAllPoints;
window.togglePointSelection = togglePointSelection;
window.cyclePointPhoto = cyclePointPhoto;
window.savePhotoReportSettingsToServer = savePhotoReportSettingsToServer;
window.printPhotoReport = printPhotoReport;
window.slidePhotoNav = slidePhotoNav;
window.deleteActiveSlidePhoto = deleteActiveSlidePhoto;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.onTipoLocalChange = onTipoLocalChange;
window.recalcVentilation = recalcVentilation;
window.syncUtmToMap = syncUtmToMap;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.autoSaveHeaderField = autoSaveHeaderField;
window.filterVentilation = filterVentilation;
window.searchVentilationLive = searchVentilationLive;
window.openVentilationTablesModal = openVentilationTablesModal;
window.closeVentilationTablesModal = closeVentilationTablesModal;
window.switchVentilationTableTab = switchVentilationTableTab;

document.addEventListener('DOMContentLoaded', () => {
    applyTableFiltersAndPagination();
});
