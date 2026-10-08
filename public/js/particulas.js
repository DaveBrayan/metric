/**
 * METRIC V2 — Monitoreo de Partículas Ocupacionales (PM10 & PM2.5)
 * Módulo JavaScript interactivo y controlador de modales (Blade Compatible)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_PARTICULAS_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/particulas/header');

// Umbrales Normativos ACGIH (TLVs)
const PM10_LIMIT = 10.0; // µg/m³ (Fracción Inhalable)
const PM25_LIMIT = 3.0;  // µg/m³ (Fracción Respirable)

/* ==========================================================================
   1. CÁLCULO EN VIVO DE PROMEDIOS PM10 Y PM2.5 & CUMPLIMIENTO
   ========================================================================== */
function calculateParticulasAverages(prefix) {
    // PM10
    const pm10_1 = parseFloat(document.getElementById(`${prefix}_pm10_1`)?.value) || null;
    const pm10_2 = parseFloat(document.getElementById(`${prefix}_pm10_2`)?.value) || null;
    const pm10_3 = parseFloat(document.getElementById(`${prefix}_pm10_3`)?.value) || null;
    const pm10Vals = [pm10_1, pm10_2, pm10_3].filter(v => v !== null && !isNaN(v));

    const pm10PromEl = document.getElementById(`${prefix}_pm10_prom_disp`);
    const pm10BadgeEl = document.getElementById(`${prefix}_pm10_compliance_badge`);
    if (pm10Vals.length > 0) {
        const pm10Avg = pm10Vals.reduce((a, b) => a + b, 0) / pm10Vals.length;
        if (pm10PromEl) pm10PromEl.textContent = pm10Avg.toFixed(3);
        if (pm10BadgeEl) {
            if (pm10Avg <= PM10_LIMIT) {
                pm10BadgeEl.className = 'badge-compliance cumple';
                pm10BadgeEl.textContent = 'Cumple ACGIH';
            } else {
                pm10BadgeEl.className = 'badge-compliance excede';
                pm10BadgeEl.textContent = 'Excede TLV';
            }
            pm10BadgeEl.style.display = 'inline-flex';
        }
    } else {
        if (pm10PromEl) pm10PromEl.textContent = '—';
        if (pm10BadgeEl) pm10BadgeEl.style.display = 'none';
    }

    // PM2.5
    const pm25_1 = parseFloat(document.getElementById(`${prefix}_pm25_1`)?.value) || null;
    const pm25_2 = parseFloat(document.getElementById(`${prefix}_pm25_2`)?.value) || null;
    const pm25_3 = parseFloat(document.getElementById(`${prefix}_pm25_3`)?.value) || null;
    const pm25Vals = [pm25_1, pm25_2, pm25_3].filter(v => v !== null && !isNaN(v));

    const pm25PromEl = document.getElementById(`${prefix}_pm25_prom_disp`);
    const pm25BadgeEl = document.getElementById(`${prefix}_pm25_compliance_badge`);
    if (pm25Vals.length > 0) {
        const pm25Avg = pm25Vals.reduce((a, b) => a + b, 0) / pm25Vals.length;
        if (pm25PromEl) pm25PromEl.textContent = pm25Avg.toFixed(3);
        if (pm25BadgeEl) {
            if (pm25Avg <= PM25_LIMIT) {
                pm25BadgeEl.className = 'badge-compliance cumple';
                pm25BadgeEl.textContent = 'Cumple ACGIH';
            } else {
                pm25BadgeEl.className = 'badge-compliance excede';
                pm25BadgeEl.textContent = 'Excede TLV';
            }
            pm25BadgeEl.style.display = 'inline-flex';
        }
    } else {
        if (pm25PromEl) pm25PromEl.textContent = '—';
        if (pm25BadgeEl) pm25BadgeEl.style.display = 'none';
    }
}

/* ==========================================================================
   2. SLIDER / CARRUSEL DE FOTOS PARA CREAR Y EDITAR
   ========================================================================== */
let sliderState = {
    create: { photos: [], currentIndex: 0 },
    edit: { photos: [], currentIndex: 0 }
};

function handleMultipleImagesSelected(input, prefix) {
    if (!input.files || input.files.length === 0) return;
    const files = Array.from(input.files);
    
    // Convertir a URLs de objeto para preview inmediato
    const urls = files.map(file => URL.createObjectURL(file));
    sliderState[prefix].photos = urls;
    sliderState[prefix].currentIndex = 0;
    
    renderPhotoSlider(prefix);
}

function slidePhotoNav(prefix, dir) {
    const state = sliderState[prefix];
    if (state.photos.length <= 1) return;
    state.currentIndex = (state.currentIndex + dir + state.photos.length) % state.photos.length;
    renderPhotoSlider(prefix);
}

function setSliderPhotoIndex(prefix, index) {
    sliderState[prefix].currentIndex = index;
    renderPhotoSlider(prefix);
}

function renderPhotoSlider(prefix) {
    const state = sliderState[prefix];
    const total = state.photos.length;
    const counter = document.getElementById(`${prefix}_slider_counter`);
    const prevBtn = document.getElementById(`${prefix}_slider_btn_prev`);
    const nextBtn = document.getElementById(`${prefix}_slider_btn_next`);
    const imgEl = document.getElementById(`${prefix}_slider_img`);
    const placeholder = document.getElementById(`${prefix}_slider_placeholder`);
    const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
    const countIndicator = document.getElementById(`${prefix}_photo_count_indicator`);

    if (countIndicator) {
        countIndicator.textContent = `${total} ${total === 1 ? 'foto' : 'fotos'}`;
    }

    if (total === 0) {
        if (imgEl) { imgEl.src = ''; imgEl.style.display = 'none'; }
        if (placeholder) placeholder.style.display = 'flex';
        if (counter) counter.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (thumbsStrip) { thumbsStrip.innerHTML = ''; thumbsStrip.style.display = 'none'; }
        return;
    }

    if (placeholder) placeholder.style.display = 'none';
    if (imgEl) {
        imgEl.src = state.photos[state.currentIndex];
        imgEl.style.display = 'block';
    }

    if (counter) {
        counter.textContent = `${state.currentIndex + 1} / ${total}`;
        counter.style.display = 'block';
    }

    if (prevBtn) prevBtn.style.display = (total > 1) ? 'grid' : 'none';
    if (nextBtn) nextBtn.style.display = (total > 1) ? 'grid' : 'none';

    if (thumbsStrip) {
        if (total > 1) {
            thumbsStrip.style.display = 'flex';
            thumbsStrip.innerHTML = state.photos.map((src, idx) => `
                <div class="slider-thumb-item ${idx === state.currentIndex ? 'active' : ''}" onclick="setSliderPhotoIndex('${prefix}', ${idx})">
                    <img src="${src}" alt="Thumb ${idx + 1}">
                </div>
            `).join('');
        } else {
            thumbsStrip.style.display = 'none';
        }
    }
}

/* ==========================================================================
   3. GEOLOCALIZACIÓN Y MINI-MAPAS (LEAFLET)
   ========================================================================== */
let miniMaps = { create: null, edit: null };
let miniMapMarkers = { create: null, edit: null };

function initMiniMap(prefix, lat = -16.5034, lng = -68.1324) {
    const mapContainerId = `${prefix}_modal_map`;
    const mapContainer = document.getElementById(mapContainerId);
    if (!mapContainer || typeof L === 'undefined') return;

    if (miniMaps[prefix]) {
        miniMaps[prefix].remove();
        miniMaps[prefix] = null;
    }

    const map = L.map(mapContainerId, {
        center: [lat, lng],
        zoom: 15,
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    const marker = L.marker([lat, lng], { draggable: true }).addTo(map);

    marker.on('dragend', function (e) {
        const pos = e.target.getLatLng();
        updateCoordsFromLatLng(prefix, pos.lat, pos.lng);
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoordsFromLatLng(prefix, e.latlng.lat, e.latlng.lng);
    });

    miniMaps[prefix] = map;
    miniMapMarkers[prefix] = marker;

    setTimeout(() => { map.invalidateSize(); }, 300);
}

function updateCoordsFromLatLng(prefix, lat, lng) {
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);

    // Convertir LatLng a UTM WGS84
    const utm = latLngToUtm(lat, lng);
    const eastingInput = document.getElementById(`${prefix}_utm_easting`);
    const northingInput = document.getElementById(`${prefix}_utm_northing`);
    const zoneInput = document.getElementById(`${prefix}_utm_zone`);
    const utmDisp = document.getElementById(`${prefix}_utm_display`);

    if (eastingInput) eastingInput.value = utm.easting.toFixed(2);
    if (northingInput) northingInput.value = utm.northing.toFixed(2);
    if (zoneInput) zoneInput.value = utm.zone;

    if (utmDisp) {
        utmDisp.textContent = `E: ${utm.easting.toFixed(1)}, N: ${utm.northing.toFixed(1)}, Z: ${utm.zone}`;
    }
}

function syncUtmToMap(prefix) {
    const easting = parseFloat(document.getElementById(`${prefix}_utm_easting`)?.value);
    const northing = parseFloat(document.getElementById(`${prefix}_utm_northing`)?.value);
    const zone = document.getElementById(`${prefix}_utm_zone`)?.value || '20K';
    const utmDisp = document.getElementById(`${prefix}_utm_display`);

    if (isNaN(easting) || isNaN(northing)) return;

    if (utmDisp) {
        utmDisp.textContent = `E: ${easting.toFixed(1)}, N: ${northing.toFixed(1)}, Z: ${zone}`;
    }

    const latLng = utmToLatLng(easting, northing, zone);
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);
    if (latInput) latInput.value = latLng.lat.toFixed(7);
    if (lngInput) lngInput.value = latLng.lng.toFixed(7);

    if (miniMaps[prefix] && miniMapMarkers[prefix]) {
        miniMapMarkers[prefix].setLatLng([latLng.lat, latLng.lng]);
        miniMaps[prefix].panTo([latLng.lat, latLng.lng]);
    }
}

function getCurrentGpsPosition(latInputId, lngInputId, prefix) {
    if (!navigator.geolocation) {
        alert('Geolocalización no soportada en este navegador.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateCoordsFromLatLng(prefix, lat, lng);
            if (miniMaps[prefix] && miniMapMarkers[prefix]) {
                miniMapMarkers[prefix].setLatLng([lat, lng]);
                miniMaps[prefix].setView([lat, lng], 16);
            }
        },
        (err) => {
            alert('No se pudo obtener la posición GPS: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

// Helpers UTM <-> LatLng (WGS84)
function utmToLatLng(easting, northing, zoneStr = '20K') {
    let zoneNumber = 20;
    let isSouth = true;
    const match = zoneStr.trim().match(/^([0-9]{1,2})\s*([C-X]?)$/i);
    if (match) {
        zoneNumber = parseInt(match[1], 10);
        const band = (match[2] || '').toUpperCase();
        if (band !== '') {
            isSouth = (band < 'N');
        }
    }

    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - f * f);
    const e1 = (1 - Math.sqrt(1 - e * e)) / (1 + Math.sqrt(1 - e * e));

    let x = easting - 500000.0;
    let y = northing;
    if (isSouth) y -= 10000000.0;

    const M = y / k0;
    const mu = M / (a * (1 - (e * e) / 4 - (3 * Math.pow(e, 4)) / 64 - (5 * Math.pow(e, 6)) / 256));

    const phi1Rad = mu + (3 * e1 / 2 - 27 * Math.pow(e1, 3) / 32) * Math.sin(2 * mu)
        + (21 * e1 * e1 / 16 - 55 * Math.pow(e1, 4) / 32) * Math.sin(4 * mu)
        + (151 * Math.pow(e1, 3) / 96) * Math.sin(6 * mu);

    const N1 = a / Math.sqrt(1 - e * e * Math.sin(phi1Rad) * Math.sin(phi1Rad));
    const T1 = Math.tan(phi1Rad) * Math.tan(phi1Rad);
    const C1 = (e * e / (1 - e * e)) * Math.cos(phi1Rad) * Math.cos(phi1Rad);
    const R1 = a * (1 - e * e) / Math.pow(1 - e * e * Math.sin(phi1Rad) * Math.sin(phi1Rad), 1.5);
    const D = x / (N1 * k0);

    const latRad = phi1Rad - (N1 * Math.tan(phi1Rad) / R1) * (
        D * D / 2
        - (5 + 3 * T1 + 10 * C1 - 4 * C1 * C1 - 9 * (e * e / (1 - e * e))) * Math.pow(D, 4) / 24
        + (61 + 90 * T1 + 298 * C1 + 45 * T1 * T1 - 252 * (e * e / (1 - e * e)) - 3 * C1 * C1) * Math.pow(D, 6) / 720
    );

    const lonOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    const lonRad = (D - (1 + 2 * T1 + C1) * Math.pow(D, 3) / 6
        + (5 - 2 * C1 + 28 * T1 - 3 * C1 * C1 + 8 * (e * e / (1 - e * e)) + 24 * T1 * T1) * Math.pow(D, 5) / 120
    ) / Math.cos(phi1Rad);

    return {
        lat: parseFloat((latRad * 180 / Math.PI).toFixed(7)),
        lng: parseFloat((lonOrigin + lonRad * 180 / Math.PI).toFixed(7))
    };
}

function latLngToUtm(lat, lng) {
    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - f * f);

    const latRad = lat * Math.PI / 180;
    const lonRad = lng * Math.PI / 180;

    let zoneNumber = Math.floor((lng + 180) / 6) + 1;
    if (lat >= 56.0 && lat < 64.0 && lng >= 3.0 && lng < 12.0) zoneNumber = 32;

    const lonOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    const lonOriginRad = lonOrigin * Math.PI / 180;

    const ePrimeSquared = (e * e) / (1 - e * e);
    const N = a / Math.sqrt(1 - e * e * Math.sin(latRad) * Math.sin(latRad));
    const T = Math.tan(latRad) * Math.tan(latRad);
    const C = ePrimeSquared * Math.cos(latRad) * Math.cos(latRad);
    const A = Math.cos(latRad) * (lonRad - lonOriginRad);

    const M = a * ((1 - e * e / 4 - 3 * Math.pow(e, 4) / 64 - 5 * Math.pow(e, 6) / 256) * latRad
        - (3 * e * e / 8 + 3 * Math.pow(e, 4) / 32 + 45 * Math.pow(e, 6) / 1024) * Math.sin(2 * latRad)
        + (15 * Math.pow(e, 4) / 256 + 45 * Math.pow(e, 6) / 1024) * Math.sin(4 * latRad)
        - (35 * Math.pow(e, 6) / 3072) * Math.sin(6 * latRad));

    const easting = k0 * N * (A + (1 - T + C) * Math.pow(A, 3) / 6
        + (5 - 18 * T + T * T + 72 * C - 58 * ePrimeSquared) * Math.pow(A, 5) / 120) + 500000.0;

    let northing = k0 * (M + N * Math.tan(latRad) * (A * A / 2
        + (5 - T + 9 * C + 4 * C * C) * Math.pow(A, 4) / 24
        + (61 - 58 * T + T * T + 600 * C - 330 * ePrimeSquared) * Math.pow(A, 6) / 720));

    if (lat < 0) northing += 10000000.0;

    const band = getUtmLetterDesignator(lat);
    return {
        easting: parseFloat(easting.toFixed(2)),
        northing: parseFloat(northing.toFixed(2)),
        zone: `${zoneNumber}${band}`
    };
}

function getUtmLetterDesignator(lat) {
    if (lat <= 84 && lat >= 72) return 'X';
    else if (lat < 72 && lat >= 64) return 'W';
    else if (lat < 64 && lat >= 56) return 'V';
    else if (lat < 56 && lat >= 48) return 'U';
    else if (lat < 48 && lat >= 40) return 'T';
    else if (lat < 40 && lat >= 32) return 'S';
    else if (lat < 32 && lat >= 24) return 'R';
    else if (lat < 24 && lat >= 16) return 'Q';
    else if (lat < 16 && lat >= 8) return 'P';
    else if (lat < 8 && lat >= 0) return 'N';
    else if (lat < 0 && lat >= -8) return 'M';
    else if (lat < -8 && lat >= -16) return 'L';
    else if (lat < -16 && lat >= -24) return 'K';
    else if (lat < -24 && lat >= -32) return 'J';
    else if (lat < -32 && lat >= -40) return 'H';
    else if (lat < -40 && lat >= -48) return 'G';
    else if (lat < -48 && lat >= -56) return 'F';
    else if (lat < -56 && lat >= -64) return 'E';
    else if (lat < -64 && lat >= -72) return 'D';
    else if (lat < -72 && lat >= -80) return 'C';
    return 'Z';
}

/* ==========================================================================
   4. MODALES: ABRIR / CERRAR & POBLAR
   ========================================================================== */
function openCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;
    modal.classList.add('active');
    sliderState.create = { photos: [], currentIndex: 0 };
    renderPhotoSlider('create');
    calculateParticulasAverages('create');
    setTimeout(() => initMiniMap('create'), 200);
}

function closeCreateMeasurementModal() {
    document.getElementById('createMeasurementModal')?.classList.remove('active');
}

let isEditUnlocked = false;

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

    // Indicador de Estado y Botón de Desbloqueo en el Encabezado
    const badge = document.getElementById('modalModeStatusBadge');
    if (badge) {
        badge.className = isEditing ? 'modal-badge-edit' : 'modal-badge-view';
        badge.textContent = isEditing ? 'Modo Edición' : 'Solo Lectura';
    }

    const btn = document.getElementById('btnToggleEditMode');
    const btnText = document.getElementById('btnToggleEditModeText');
    const btnIcon = document.getElementById('btnToggleEditModeIcon');
    if (btn) {
        if (isEditing) {
            btn.classList.add('active-editing');
            if (btnText) btnText.textContent = 'Lectura';
            if (btnIcon) {
                btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
            }
        } else {
            btn.classList.remove('active-editing');
            if (btnText) btnText.textContent = 'Editar';
            if (btnIcon) {
                btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>`;
            }
        }
    }

    // Habilitar / Deshabilitar Campos de Entrada
    const fieldsToToggle = [
        'edit_measurement_date',
        'edit_measurement_time',
        'edit_staff_id',
        'edit_area',
        'edit_workstation',
        'edit_punto_medicion',
        'edit_temperatura',
        'edit_hr_percent',
        'edit_pm10_1',
        'edit_pm10_2',
        'edit_pm10_3',
        'edit_pm25_1',
        'edit_pm25_2',
        'edit_pm25_3',
        'edit_utm_easting',
        'edit_utm_northing',
        'edit_utm_zone',
        'edit_latitude',
        'edit_longitude',
        'edit_observations'
    ];

    fieldsToToggle.forEach(fieldId => {
        const el = document.getElementById(fieldId);
        if (el) el.disabled = !isEditing;
    });

    // Visibilidad de Botones y Controles de Edición
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const gpsBtn = document.getElementById('edit_btn_gps');
    if (gpsBtn) gpsBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const placeholder = document.getElementById('edit_slider_placeholder');
    if (placeholder) {
        placeholder.style.cursor = isEditing ? 'pointer' : 'default';
        const strong = placeholder.querySelector('strong');
        const span = placeholder.querySelector('span');
        if (strong) strong.textContent = isEditing ? 'Subir Fotografías del Punto' : 'Sin Fotografías Registradas';
        if (span) span.textContent = isEditing ? 'Haz clic para agregar o reemplazar imágenes' : 'Activa el modo edición para agregar fotografías';
    }

    // Arrastre de marcador en mapa
    if (miniMapMarkers['edit'] && miniMapMarkers['edit'].dragging) {
        if (isEditing) miniMapMarkers['edit'].dragging.enable();
        else miniMapMarkers['edit'].dragging.disable();
    }
}

function toggleModalEditMode() {
    applyEditModeState(!isEditUnlocked);
}

function openViewMeasurementModal(item) {
    const modal = document.getElementById('editMeasurementModal');
    if (!modal) return;

    document.getElementById('edit_measurement_id').value = item.id;
    document.getElementById('edit_point_number').value = item.num || '';
    document.getElementById('edit_pt_num_disp').textContent = item.num || '';
    document.getElementById('edit_measurement_date').value = item.raw_date || '';
    document.getElementById('edit_measurement_time').value = item.time || '';
    document.getElementById('edit_area').value = item.area || '';
    document.getElementById('edit_workstation').value = item.workstation || item.punto_medicion || '';
    document.getElementById('edit_punto_medicion').value = item.punto_medicion || item.workstation || '';
    document.getElementById('edit_temperatura').value = item.temperatura !== null ? item.temperatura : '';
    document.getElementById('edit_hr_percent').value = item.hr_percent !== null ? item.hr_percent : '';
    document.getElementById('edit_observations').value = item.raw_observations || item.observations || '';

    // Staff
    const staffEl = document.getElementById('edit_modal_registered_by');
    const staffSelect = document.getElementById('edit_staff_id');
    if (staffSelect) {
        if (item.staff_id) {
            staffSelect.value = item.staff_id;
        } else if (item.registered_by) {
            for (let i = 0; i < staffSelect.options.length; i++) {
                if (staffSelect.options[i].text.toLowerCase().includes(item.registered_by.toLowerCase())) {
                    staffSelect.selectedIndex = i;
                    break;
                }
            }
        }
        if (staffEl && staffSelect.selectedIndex >= 0 && staffSelect.options[staffSelect.selectedIndex]) {
            staffEl.textContent = staffSelect.options[staffSelect.selectedIndex].text.split('—')[0].trim();
        } else if (staffEl) {
            staffEl.textContent = item.registered_by || REGISTERED_BY_HEADER || '';
        }
    } else if (staffEl) {
        staffEl.textContent = item.registered_by || REGISTERED_BY_HEADER || '';
    }

    // PM10 Valores
    const pm10Arr = item.pm10_values || [];
    document.getElementById('edit_pm10_1').value = pm10Arr[0] !== undefined ? pm10Arr[0] : '';
    document.getElementById('edit_pm10_2').value = pm10Arr[1] !== undefined ? pm10Arr[1] : '';
    document.getElementById('edit_pm10_3').value = pm10Arr[2] !== undefined ? pm10Arr[2] : '';

    // PM2.5 Valores
    const pm25Arr = item.pm25_values || [];
    document.getElementById('edit_pm25_1').value = pm25Arr[0] !== undefined ? pm25Arr[0] : '';
    document.getElementById('edit_pm25_2').value = pm25Arr[1] !== undefined ? pm25Arr[1] : '';
    document.getElementById('edit_pm25_3').value = pm25Arr[2] !== undefined ? pm25Arr[2] : '';

    // Coordenadas
    document.getElementById('edit_utm_easting').value = item.utm_easting || '';
    document.getElementById('edit_utm_northing').value = item.utm_northing || '';
    document.getElementById('edit_utm_zone').value = item.utm_zone || '20K';
    document.getElementById('edit_latitude').value = item.latitude || '';
    document.getElementById('edit_longitude').value = item.longitude || '';

    const utmDisp = document.getElementById('edit_utm_display');
    if (utmDisp) {
        utmDisp.textContent = `E: ${item.utm_easting || '—'}, N: ${item.utm_northing || '—'}, Z: ${item.utm_zone || '20K'}`;
    }

    // Form Action
    document.getElementById('editMeasurementForm').action = `/modulos/${MODULE_ID}/particulas/mediciones/${item.id}`;

    // Fotos
    sliderState.edit.photos = item.images || (item.image_path ? [item.image_path] : []);
    sliderState.edit.currentIndex = 0;
    renderPhotoSlider('edit');

    calculateParticulasAverages('edit');

    // Modo Solo Lectura por defecto al abrir
    applyEditModeState(false);

    modal.classList.add('active');

    const lat = item.latitude ? parseFloat(item.latitude) : -16.5034;
    const lng = item.longitude ? parseFloat(item.longitude) : -68.1324;
    setTimeout(() => initMiniMap('edit', lat, lng), 200);
}

function openEditMeasurementModal(item) {
    openViewMeasurementModal(item);
}

function closeEditMeasurementModal() {
    document.getElementById('editMeasurementModal')?.classList.remove('active');
}

function confirmDeleteMeasurement(id, num) {
    const form = document.getElementById('deleteMeasurementForm');
    if (!form) return;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar punto de medición?',
            text: `Se eliminará el punto N° ${num}. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            buttonsStyling: false,
            customClass: {
                popup: 'metric-swal-popup',
                confirmButton: 'metric-swal-btn-danger',
                cancelButton: 'metric-swal-btn-subtle'
            }
        }).then((res) => {
            if (res.isConfirmed) {
                form.action = `/modulos/${MODULE_ID}/particulas/mediciones/${id}`;
                form.submit();
            }
        });
    } else {
        if (confirm(`¿Estás seguro de eliminar el punto N° ${num}?`)) {
            form.action = `/modulos/${MODULE_ID}/particulas/mediciones/${id}`;
            form.submit();
        }
    }
}

/* ==========================================================================
   5. VISOR DE FOTOGRAFÍA AMPLIFICADA
   ========================================================================== */
function openPhotoViewer(url, title = 'Fotografía de Medición') {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const titleEl = document.getElementById('photoViewerTitle');
    if (!modal || !img) return;

    img.src = url;
    if (titleEl) titleEl.textContent = title;
    modal.style.display = 'grid';
}

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.style.display = 'none';
}

/* ==========================================================================
   6. MODAL MAPA INDIVIDUAL
   ========================================================================== */
let singleMapInstance = null;
let singleMapMarker = null;

function openPointMapModal(lat, lng, name, desc, photoUrl) {
    const modal = document.getElementById('mapLocationModal');
    if (!modal || typeof L === 'undefined') return;

    document.getElementById('mapCardPointName').textContent = name;
    document.getElementById('mapCardLocationDesc').textContent = desc;
    document.getElementById('mapCardCoords').textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('openInGoogleMapsBtn').href = `https://www.google.com/maps?q=${lat},${lng}`;

    const thumbWrap = document.getElementById('mapModalPhotoThumbWrap');
    const thumbImg = document.getElementById('mapModalPhotoImg');
    if (photoUrl && thumbWrap && thumbImg) {
        thumbImg.src = photoUrl;
        thumbWrap.style.display = 'flex';
    } else if (thumbWrap) {
        thumbWrap.style.display = 'none';
    }

    modal.classList.add('active');

    setTimeout(() => {
        if (singleMapInstance) {
            singleMapInstance.remove();
        }
        singleMapInstance = L.map('mapContainerLeaflet', {
            center: [lat, lng],
            zoom: 16,
            attributionControl: false
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(singleMapInstance);
        singleMapMarker = L.marker([lat, lng]).addTo(singleMapInstance);
        singleMapMarker.bindPopup(`<b>${name}</b><br>${desc}`).openPopup();
    }, 200);
}

function closeMapModal() {
    document.getElementById('mapLocationModal')?.classList.remove('active');
}

/* ==========================================================================
   7. MODAL UBICACIONES GENERALES (TODOS LOS PUNTOS)
   ========================================================================== */
let allLocationsMapInstance = null;
let allLocationsMarkers = [];

function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal || typeof L === 'undefined') return;
    modal.classList.add('active');

    setTimeout(() => {
        if (allLocationsMapInstance) {
            allLocationsMapInstance.remove();
            allLocationsMarkers = [];
        }

        const validPoints = ALL_MEASUREMENTS_DATA.filter(m => m.latitude && m.longitude);
        const center = validPoints.length > 0 ? [validPoints[0].latitude, validPoints[0].longitude] : [-16.5034, -68.1324];

        allLocationsMapInstance = L.map('allLocationsMapLeaflet', {
            center: center,
            zoom: 14,
            attributionControl: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(allLocationsMapInstance);

        const group = [];
        ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
            if (m.latitude && m.longitude) {
                const marker = L.marker([m.latitude, m.longitude]).addTo(allLocationsMapInstance);
                const popupHtml = `
                    <div style="font-family: 'Outfit', sans-serif; min-width: 180px;">
                        <strong style="font-size: 13px; color: #0284c7;">Punto #${m.num}: ${m.punto_medicion || m.workstation}</strong>
                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">${m.area}</div>
                        <div style="font-size: 11px; margin-top: 4px;">PM10: <b>${m.pm10_prom !== null ? m.pm10_prom + ' µg/m³' : '—'}</b></div>
                        <div style="font-size: 11px;">PM2.5: <b>${m.pm25_prom !== null ? m.pm25_prom + ' µg/m³' : '—'}</b></div>
                        <div style="font-size: 10.5px; color: #0369a1; font-weight: 700; margin-top: 4px;">Registrado por: ${m.registered_by}</div>
                    </div>
                `;
                marker.bindPopup(popupHtml);
                allLocationsMarkers[idx] = marker;
                group.push([m.latitude, m.longitude]);
            }
        });

        if (group.length > 0) {
            allLocationsMapInstance.fitBounds(group, { padding: [40, 40] });
        }
    }, 200);
}

function closeAllLocationsModal() {
    document.getElementById('allLocationsModal')?.classList.remove('active');
}

function focusPointOnAllLocationsMap(index) {
    const m = ALL_MEASUREMENTS_DATA[index];
    if (!m || !m.latitude || !m.longitude || !allLocationsMapInstance) return;

    allLocationsMapInstance.setView([m.latitude, m.longitude], 17, { animate: true });
    if (allLocationsMarkers[index]) {
        allLocationsMarkers[index].openPopup();
    }
}

/* ==========================================================================
   8. MODAL TABLAS NORMATIVAS & EXPORTACIÓN
   ========================================================================== */
function openNormativeTablesModal() {
    document.getElementById('normativeTablesModal')?.classList.add('active');
}

function closeNormativeTablesModal() {
    document.getElementById('normativeTablesModal')?.classList.remove('active');
}

function openExportModal() {
    document.getElementById('exportOptionsModal')?.classList.add('active');
}

function closeExportModal() {
    document.getElementById('exportOptionsModal')?.classList.remove('active');
}

/* ==========================================================================
   9. BÚSQUEDA EN VIVO Y PAGINACIÓN REACTIVA
   ========================================================================== */
const PAGE_SIZE = 10;
let currentPage = 1;
let filteredRows = [];

function searchParticulasLive() {
    const input = document.getElementById('particulasSearchInput');
    const query = (input?.value || '').toLowerCase().trim();
    const rows = Array.from(document.querySelectorAll('.particulas-data-row'));

    filteredRows = rows.filter(row => {
        const text = row.getAttribute('data-search') || '';
        return text.includes(query);
    });

    currentPage = 1;
    renderPagination();
}

function renderPagination() {
    const rows = Array.from(document.querySelectorAll('.particulas-data-row'));
    const total = filteredRows.length;
    const totalPages = Math.ceil(total / PAGE_SIZE) || 1;

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * PAGE_SIZE;
    const endIdx = startIdx + PAGE_SIZE;

    // Ocultar todas las filas primero
    rows.forEach(r => r.style.display = 'none');

    // Mostrar solo las de la página actual
    filteredRows.slice(startIdx, endIdx).forEach(r => r.style.display = '');

    // Fila sin resultados
    const noResultsRow = document.getElementById('noResultsSearchRow');
    const emptyTableRow = document.getElementById('emptyTableRow');
    if (noResultsRow) {
        noResultsRow.style.display = (total === 0 && rows.length > 0) ? '' : 'none';
    }
    if (emptyTableRow) {
        emptyTableRow.style.display = (rows.length === 0) ? '' : 'none';
    }

    // Info de paginación
    const pageStart = total > 0 ? startIdx + 1 : 0;
    const pageEnd = Math.min(endIdx, total);
    const pageStartEl = document.getElementById('partPageStart');
    const pageEndEl = document.getElementById('partPageEnd');
    const pageTotalEl = document.getElementById('partPageTotal');

    if (pageStartEl) pageStartEl.textContent = pageStart;
    if (pageEndEl) pageEndEl.textContent = pageEnd;
    if (pageTotalEl) pageTotalEl.textContent = total;

    // Botones de control
    const controls = document.getElementById('particulasPaginationControls');
    if (!controls) return;

    let html = `
        <button type="button" class="pagination-btn" ${currentPage === 1 ? 'disabled' : ''} onclick="goToPage(${currentPage - 1})">❮</button>
    `;
    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
            html += `<button type="button" class="pagination-btn ${p === currentPage ? 'active' : ''}" onclick="goToPage(${p})">${p}</button>`;
        } else if (p === currentPage - 2 || p === currentPage + 2) {
            html += `<span style="padding: 0 4px; color: #94a3b8;">...</span>`;
        }
    }
    html += `
        <button type="button" class="pagination-btn" ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">❯</button>
    `;
    controls.innerHTML = html;
}

function goToPage(page) {
    currentPage = page;
    renderPagination();
}

/* ==========================================================================
   10. AUTOGUARDADO INLINE DE ENCABEZADO TÉCNICO
   ========================================================================== */
function autoSaveHeaderField() {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.classList.add('visible', 'saving');
        badge.querySelector('span').textContent = 'Guardando...';
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
            badge.querySelector('span').textContent = 'Guardado';
            setTimeout(() => { badge.classList.remove('visible'); }, 2000);
        }
    })
    .catch(err => {
        if (badge) {
            badge.classList.remove('saving');
            badge.querySelector('span').textContent = 'Error al guardar';
        }
    });
}

/* ==========================================================================
   11. EXPORTACIÓN EXCEL DE ALTA FIDELIDAD (ExcelJS)
   ========================================================================== */
async function exportParticulasExcel() {
    if (typeof ExcelJS === 'undefined') {
        alert('Cargando librería ExcelJS. Por favor intenta en unos segundos.');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'PACHABOL S.R.L. - METRIC V2';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Partículas Ocupacionales', {
        pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true, fitToWidth: 1 }
    });

    // 1. Estilos Globales
    const primaryColor = 'FF0284C7'; // Celeste Institucional
    const headerBg = 'FF0369A1';
    const subheaderBg = 'FFF0F9FF';
    const borderStyle = { style: 'thin', color: { argb: 'FFCBD5E1' } };
    const allBorders = { top: borderStyle, left: borderStyle, bottom: borderStyle, right: borderStyle };

    // Título Principal
    sheet.mergeCells('A1:O2');
    const titleCell = sheet.getCell('A1');
    titleCell.value = 'PLANILLA TÉCNICA DE MONITOREO DE PARTÍCULAS OCUPACIONALES (PM10 & PM2.5)';
    titleCell.font = { name: 'Calibri', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } };
    titleCell.alignment = { vertical: 'middle', horizontal: 'center' };

    // 2. Encabezado Técnico
    sheet.mergeCells('A3:D3');
    sheet.getCell('A3').value = `INSTALACIÓN: ${TECHNICAL_HEADER_DATA.installationName || '—'}`;
    sheet.getCell('A3').font = { bold: true, size: 10 };

    sheet.mergeCells('E3:H3');
    sheet.getCell('E3').value = `TIPO: ${TECHNICAL_HEADER_DATA.monitoringType || 'Seguimiento'}`;
    sheet.getCell('E3').font = { bold: true, size: 10 };

    sheet.mergeCells('I3:L3');
    sheet.getCell('I3').value = `FECHA: ${TECHNICAL_HEADER_DATA.startDateFormatted || '—'} al ${TECHNICAL_HEADER_DATA.endDateFormatted || '—'}`;
    sheet.getCell('I3').font = { bold: true, size: 10 };

    sheet.mergeCells('M3:O3');
    sheet.getCell('M3').value = `EQUIPO: ${TECHNICAL_HEADER_DATA.equipmentName || 'Medidor de Partículas'} (${TECHNICAL_HEADER_DATA.equipmentModel || 'LKC-1000'})`;
    sheet.getCell('M3').font = { bold: true, size: 10 };

    // 3. Encabezados de Tabla (Fila 5 y 6)
    sheet.mergeCells('A5:A6');
    sheet.getCell('A5').value = 'N°';
    sheet.mergeCells('B5:B6');
    sheet.getCell('B5').value = 'FECHA';
    sheet.mergeCells('C5:C6');
    sheet.getCell('C5').value = 'HORA';
    sheet.mergeCells('D5:D6');
    sheet.getCell('D5').value = 'ÁREA';
    sheet.mergeCells('E5:E6');
    sheet.getCell('E5').value = 'PUESTO / PUNTO DE MEDICIÓN';
    sheet.mergeCells('F5:F6');
    sheet.getCell('F5').value = 'TEMP (°C)';
    sheet.mergeCells('G5:G6');
    sheet.getCell('G5').value = 'HR (%)';

    // Grupo PM10
    sheet.mergeCells('H5:K5');
    sheet.getCell('H5').value = 'FRACCIÓN INHALABLE PM10 (µg/m³)';
    sheet.getCell('H6').value = 'MED 1';
    sheet.getCell('I6').value = 'MED 2';
    sheet.getCell('J6').value = 'MED 3';
    sheet.getCell('K6').value = 'PROM';

    // Grupo PM2.5
    sheet.mergeCells('L5:O5');
    sheet.getCell('L5').value = 'FRACCIÓN RESPIRABLE PM2.5 (µg/m³)';
    sheet.getCell('L6').value = 'MED 1';
    sheet.getCell('M6').value = 'MED 2';
    sheet.getCell('N6').value = 'MED 3';
    sheet.getCell('O6').value = 'PROM';

    // Estilos a cabeceras
    ['A5','B5','C5','D5','E5','F5','G5','H5','H6','I6','J6','K6','L5','L6','M6','N6','O6'].forEach(cellRef => {
        const cell = sheet.getCell(cellRef);
        cell.font = { bold: true, size: 9.5, color: { argb: 'FFFFFFFF' } };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: headerBg } };
        cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
        cell.border = allBorders;
    });

    // 4. Filas de Datos
    let rowIdx = 7;
    ALL_MEASUREMENTS_DATA.forEach(m => {
        const pm10Vals = m.pm10_values || [];
        const pm25Vals = m.pm25_values || [];

        const row = sheet.getRow(rowIdx);
        row.values = [
            m.num,
            m.date,
            m.time,
            m.area,
            m.punto_medicion || m.workstation,
            m.temperatura !== null ? m.temperatura : '',
            m.hr_percent !== null ? m.hr_percent : '',
            pm10Vals[0] !== undefined ? pm10Vals[0] : '',
            pm10Vals[1] !== undefined ? pm10Vals[1] : '',
            pm10Vals[2] !== undefined ? pm10Vals[2] : '',
            m.pm10_prom !== null ? m.pm10_prom : '',
            pm25Vals[0] !== undefined ? pm25Vals[0] : '',
            pm25Vals[1] !== undefined ? pm25Vals[1] : '',
            pm25Vals[2] !== undefined ? pm25Vals[2] : '',
            m.pm25_prom !== null ? m.pm25_prom : ''
        ];

        row.alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell(4).alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell(5).alignment = { vertical: 'middle', horizontal: 'left' };

        for (let col = 1; col <= 15; col++) {
            row.getCell(col).border = allBorders;
            row.getCell(col).font = { size: 9.5 };
        }

        // Resaltar promedios
        row.getCell(11).font = { bold: true, size: 10, color: { argb: 'FFB45309' } };
        row.getCell(15).font = { bold: true, size: 10, color: { argb: 'FFC2410C' } };

        rowIdx++;
    });

    // Ancho de Columnas
    sheet.columns = [
        { width: 6 },  // N°
        { width: 12 }, // Fecha
        { width: 10 }, // Hora
        { width: 22 }, // Área
        { width: 26 }, // Puesto
        { width: 11 }, // Temp
        { width: 10 }, // HR
        { width: 10 }, // PM10 1
        { width: 10 }, // PM10 2
        { width: 10 }, // PM10 3
        { width: 12 }, // PM10 Prom
        { width: 10 }, // PM2.5 1
        { width: 10 }, // PM2.5 2
        { width: 10 }, // PM2.5 3
        { width: 12 }, // PM2.5 Prom
    ];

    // Descargar Archivo
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Monitoreo_Particulas_${TECHNICAL_HEADER_DATA.installationName || 'Reporte'}.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    closeExportModal();
}

/* ==========================================================================
   12. REPORTE FOTOGRÁFICO DE PARTÍCULAS
   ========================================================================== */
let photoReportGrid = '2x3';
let photoReportOrientation = 'landscape';

function openPhotoReportConfigModal() {
    closeExportModal();
    document.getElementById('photoReportModal')?.classList.add('active');
}

function closePhotoReportConfigModal() {
    document.getElementById('photoReportModal')?.classList.remove('active');
}

function setPhotoGridPreset(preset) {
    photoReportGrid = preset;
    document.querySelectorAll('.photo-grid-pill').forEach(el => {
        el.classList.toggle('active', el.getAttribute('data-grid') === preset);
    });
}

function setPhotoOrientation(orient) {
    photoReportOrientation = orient;
    document.querySelectorAll('.photo-orient-pill').forEach(el => {
        el.classList.toggle('active', el.getAttribute('data-orient') === orient);
    });
}

function selectAllPhotoReportPoints(check) {
    document.querySelectorAll('.pr-point-checkbox').forEach(cb => {
        cb.checked = check;
    });
}

function togglePhotoReportCardSelection(id) {
    const cb = document.querySelector(`#pr_card_${id} input[type="checkbox"]`);
    if (cb) cb.checked = !cb.checked;
}

function generatePhotoReportPrintView() {
    const selectedCheckboxes = Array.from(document.querySelectorAll('.pr-point-checkbox:checked'));
    const selectedIds = selectedCheckboxes.map(cb => parseInt(cb.value, 10));

    const selectedPoints = ALL_MEASUREMENTS_DATA.filter(m => selectedIds.includes(m.id));
    if (selectedPoints.length === 0) {
        alert('Por favor selecciona al menos un punto para el reporte fotográfico.');
        return;
    }

    const [cols, rows] = photoReportGrid.split('x').map(Number);
    const photosPerPage = cols * rows;

    let pagesHtml = '';
    for (let i = 0; i < selectedPoints.length; i += photosPerPage) {
        const pageItems = selectedPoints.slice(i, i + photosPerPage);
        pagesHtml += `
            <div class="photo-print-page ${photoReportOrientation}" style="page-break-after: always; padding: 20px; box-sizing: border-box;">
                <div style="border-bottom: 2px solid #0284c7; padding-bottom: 8px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <h2 style="font-family: 'Outfit', sans-serif; font-size: 16px; margin: 0; color: #0284c7;">CATÁLOGO FOTOGRÁFICO — MONITOREO DE PARTÍCULAS OCUPACIONALES</h2>
                        <span style="font-size: 11px; color: #64748b;">${TECHNICAL_HEADER_DATA.installationName || 'Instalación'} — ${TECHNICAL_HEADER_DATA.startDateFormatted || ''}</span>
                    </div>
                    <span style="font-size: 11px; font-weight: bold; color: #0284c7;">Página ${Math.floor(i / photosPerPage) + 1} de ${Math.ceil(selectedPoints.length / photosPerPage)}</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(${cols}, 1fr); grid-template-rows: repeat(${rows}, 1fr); gap: 14px; height: calc(100% - 60px);">
                    ${pageItems.map(m => `
                        <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 8px; display: flex; flex-direction: column; justify-content: space-between; background: #ffffff;">
                            <div style="width: 100%; height: 160px; background: #f1f5f9; border-radius: 6px; overflow: hidden; display: grid; place-items: center; margin-bottom: 6px;">
                                ${m.image_path ? `<img src="${m.image_path}" style="width: 100%; height: 100%; object-fit: cover;">` : '<span style="font-size: 11px; color: #94a3b8;">Sin Fotografía</span>'}
                            </div>
                            <div style="font-size: 10.5px; line-height: 1.3;">
                                <strong style="color: #0284c7;">Punto #${m.num}: ${m.punto_medicion || m.workstation}</strong><br>
                                <span style="color: #64748b;">${m.area}</span><br>
                                <span>PM10: <b>${m.pm10_prom !== null ? m.pm10_prom + ' µg/m³' : '—'}</b> | PM2.5: <b>${m.pm25_prom !== null ? m.pm25_prom + ' µg/m³' : '—'}</b></span>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    }

    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Reporte Fotográfico de Partículas Ocupacionales — ${TECHNICAL_HEADER_DATA.installationName || ''}</title>
            <style>
                @page { size: letter ${photoReportOrientation}; margin: 10mm; }
                body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; }
                @media print { .no-print { display: none; } }
            </style>
        </head>
        <body onload="window.print()">
            ${pagesHtml}
        </body>
        </html>
    `);
    printWindow.document.close();
}

/* ==========================================================================
   13. INICIALIZACIÓN AL CARGAR EL DOM
   ========================================================================== */
document.addEventListener('DOMContentLoaded', function () {
    filteredRows = Array.from(document.querySelectorAll('.particulas-data-row'));
    renderPagination();
});

// Exportaciones Globales
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.toggleModalEditMode = toggleModalEditMode;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewer = closePhotoViewer;

