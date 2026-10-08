/**
 * METRIC V2 — Monitoreo de Partículas Ambientales (Calidad del Aire)
 * Módulo JavaScript interactivo y controlador de modales (Blade Compatible)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_PARTICULAS_AMB_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/particulas-ambientales/header');

// Umbrales Normativos Ambientales (Ley 1333 RMCA / OMS)
const PM10_LIMIT_24H = 150.0; // µg/m³ (24 horas)
const PM25_LIMIT_24H = 25.0;  // µg/m³ (24 horas)
const PTS_LIMIT_24H = 260.0;  // µg/m³ (24 horas)

/* ==========================================================================
   1. CÁLCULO DE DIFERENCIA DE HORAS Y GRAVIMETRÍA
   ========================================================================== */

function calculateParticulasAmbDiffHours(prefix) {
    const fIni = document.getElementById(`${prefix}_fecha_inicio`)?.value;
    const hIni = document.getElementById(`${prefix}_hora_inicio`)?.value;
    const fFin = document.getElementById(`${prefix}_fecha_fin`)?.value;
    const hFin = document.getElementById(`${prefix}_hora_fin`)?.value;

    if (fIni && hIni && fFin && hFin) {
        try {
            const start = new Date(`${fIni}T${hIni}:00`);
            const end = new Date(`${fFin}T${hFin}:00`);
            let diffMs = end - start;
            if (diffMs > 0) {
                const diffHours = (diffMs / (1000 * 60 * 60)).toFixed(2);
                const diffEl = document.getElementById(`${prefix}_diferencia_horas`);
                if (diffEl) {
                    diffEl.value = diffHours;
                }
            }
        } catch (e) {
            console.error('Error calculando diferencia de horas:', e);
        }
    }
    calculateParticulasAmbGravimetric(prefix);
}

function calculateParticulasAmbGravimetric(prefix) {
    const caudal = parseFloat(document.getElementById(`${prefix}_caudal`)?.value) || 1130.0;
    const diffHours = parseFloat(document.getElementById(`${prefix}_diferencia_horas`)?.value) || 24.0;

    let volumenM3 = 0;
    if (caudal > 0 && diffHours > 0) {
        volumenM3 = (caudal / 1000.0) * (diffHours * 60.0);
    }

    // PM-10
    const pm10Ini = parseFloat(document.getElementById(`${prefix}_pm10_filtro_inicial`)?.value);
    const pm10Fin = parseFloat(document.getElementById(`${prefix}_pm10_filtro_final`)?.value);
    const pm10Disp = document.getElementById(`${prefix}_pm10_prom_disp`);
    const pm10Hidden = document.getElementById(`${prefix}_pm10_prom`);

    if (!isNaN(pm10Ini) && !isNaN(pm10Fin) && pm10Fin >= pm10Ini && volumenM3 > 0) {
        const conc = ((pm10Fin - pm10Ini) * 1000000.0) / volumenM3;
        if (pm10Disp) pm10Disp.textContent = conc.toFixed(2);
        if (pm10Hidden) pm10Hidden.value = conc.toFixed(3);
    } else {
        if (pm10Disp) pm10Disp.textContent = '0.00';
    }

    // PST
    const pstIni = parseFloat(document.getElementById(`${prefix}_pst_filtro_inicial`)?.value);
    const pstFin = parseFloat(document.getElementById(`${prefix}_pst_filtro_final`)?.value);
    const pstDisp = document.getElementById(`${prefix}_pst_prom_disp`);
    const pstHidden = document.getElementById(`${prefix}_pst_prom`);

    if (!isNaN(pstIni) && !isNaN(pstFin) && pstFin >= pstIni && volumenM3 > 0) {
        const conc = ((pstFin - pstIni) * 1000000.0) / volumenM3;
        if (pstDisp) pstDisp.textContent = conc.toFixed(2);
        if (pstHidden) pstHidden.value = conc.toFixed(3);
    } else {
        if (pstDisp) pstDisp.textContent = '0.00';
    }
}

/* ==========================================================================
   2. SLIDER DE FOTOS
   ========================================================================== */
let sliderState = {
    create: { photos: [], currentIndex: 0 },
    edit: { photos: [], currentIndex: 0 }
};

function handleMultipleImagesSelected(input, prefix) {
    if (!input.files || input.files.length === 0) return;
    const files = Array.from(input.files);
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

    if (countIndicator) countIndicator.textContent = `${total} ${total === 1 ? 'foto' : 'fotos'}`;

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
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
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

    const utm = latLngToUtm(lat, lng);
    const eastingInput = document.getElementById(`${prefix}_utm_easting`);
    const northingInput = document.getElementById(`${prefix}_utm_northing`);
    const zoneInput = document.getElementById(`${prefix}_utm_zone`);

    if (eastingInput) eastingInput.value = utm.easting.toFixed(2);
    if (northingInput) northingInput.value = utm.northing.toFixed(2);
    if (zoneInput) zoneInput.value = utm.zone;
}

function syncUtmToMap(prefix) {
    const easting = parseFloat(document.getElementById(`${prefix}_utm_easting`)?.value);
    const northing = parseFloat(document.getElementById(`${prefix}_utm_northing`)?.value);
    const zone = document.getElementById(`${prefix}_utm_zone`)?.value || '19K';

    if (isNaN(easting) || isNaN(northing)) return;

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

// Helpers UTM <-> LatLng
function utmToLatLng(easting, northing, zoneStr = '19K') {
    let zoneNumber = 19;
    let isSouth = true;
    const match = zoneStr.trim().match(/^([0-9]{1,2})\s*([C-X]?)$/i);
    if (match) {
        zoneNumber = parseInt(match[1], 10);
        const band = (match[2] || '').toUpperCase();
        if (band !== '') isSouth = (band < 'N');
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
    );

    const lonOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    const lonRad = (D - (1 + 2 * T1 + C1) * Math.pow(D, 3) / 6) / Math.cos(phi1Rad);

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
    const lonOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    const lonOriginRad = lonOrigin * Math.PI / 180;

    const ePrimeSquared = (e * e) / (1 - e * e);
    const N = a / Math.sqrt(1 - e * e * Math.sin(latRad) * Math.sin(latRad));
    const T = Math.tan(latRad) * Math.tan(latRad);
    const C = ePrimeSquared * Math.cos(latRad) * Math.cos(latRad);
    const A = Math.cos(latRad) * (lonRad - lonOriginRad);

    const M = a * ((1 - e * e / 4) * latRad - (3 * e * e / 8) * Math.sin(2 * latRad) + (15 * Math.pow(e, 4) / 256) * Math.sin(4 * latRad));

    const easting = k0 * N * (A + (1 - T + C) * Math.pow(A, 3) / 6) + 500000.0;
    let northing = k0 * (M + N * Math.tan(latRad) * (A * A / 2));
    if (lat < 0) northing += 10000000.0;

    return {
        easting: parseFloat(easting.toFixed(2)),
        northing: parseFloat(northing.toFixed(2)),
        zone: `${zoneNumber}K`
    };
}

/* ==========================================================================
   4. MODALES: ABRIR Y CERRAR
   ========================================================================== */
function openCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;
    modal.classList.add('active');
    modal.style.display = 'flex';
    sliderState.create = { photos: [], currentIndex: 0 };
    renderPhotoSlider('create');
    calculateParticulasAmbDiffHours('create');
    setTimeout(() => initMiniMap('create'), 200);
}

function closeCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
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
        'edit_fecha_inicio',
        'edit_hora_inicio',
        'edit_fecha_fin',
        'edit_hora_fin',
        'edit_diferencia_horas',
        'edit_caudal',
        'edit_area',
        'edit_punto_medicion',
        'edit_temp_max',
        'edit_temp_min',
        'edit_presion_atm',
        'edit_vel_viento',
        'edit_dir_viento',
        'edit_pm10_filtro_inicial',
        'edit_pm10_filtro_final',
        'edit_pst_filtro_inicial',
        'edit_pst_filtro_final',
        'edit_utm_easting',
        'edit_utm_northing',
        'edit_utm_zone',
        'edit_staff_id',
        'edit_observations'
    ];

    fieldsToToggle.forEach(fieldId => {
        const el = document.getElementById(fieldId);
        if (el) el.disabled = !isEditing;
    });

    // Visibilidad de Botones y Controles de Edición
    const submitBtn = document.getElementById('btnSaveEditMeasurement');
    if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const gpsBtn = document.getElementById('edit_btn_gps');
    if (gpsBtn) gpsBtn.disabled = !isEditing;

    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'flex' : 'none';

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

    // Configura la acción del formulario
    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${MODULE_ID}/particulas-ambientales/mediciones/${item.id}`;
    }

    document.getElementById('edit_measurement_id').value = item.id;
    document.getElementById('edit_point_number').value = item.num || '';
    document.getElementById('edit_pt_num_disp').textContent = item.num || '';
    
    // Fechas y Horas
    document.getElementById('edit_fecha_inicio').value = item.fecha_inicio || item.raw_date || '';
    document.getElementById('edit_hora_inicio').value = item.hora_inicio || item.time || '08:00';
    document.getElementById('edit_fecha_fin').value = item.fecha_fin || item.fecha_inicio || item.raw_date || '';
    document.getElementById('edit_hora_fin').value = item.hora_fin || '08:00';
    document.getElementById('edit_diferencia_horas').value = item.diferencia_horas !== null ? item.diferencia_horas : '24.00';
    document.getElementById('edit_caudal').value = item.caudal !== null ? item.caudal : '1130.0';

    // Área y Punto
    document.getElementById('edit_area').value = item.area || '';
    document.getElementById('edit_punto_medicion').value = item.punto_medicion || '';

    // Meteorología
    document.getElementById('edit_temp_max').value = item.temp_max !== null ? item.temp_max : '';
    document.getElementById('edit_temp_min').value = item.temp_min !== null ? item.temp_min : '';
    document.getElementById('edit_presion_atm').value = item.presion_atm !== null ? item.presion_atm : '';
    document.getElementById('edit_vel_viento').value = item.vel_viento !== null ? item.vel_viento : '';
    document.getElementById('edit_dir_viento').value = item.dir_viento || '';

    // Muestreo PM-10
    document.getElementById('edit_pm10_filtro_inicial').value = item.pm10_filtro_inicial !== null ? item.pm10_filtro_inicial : '';
    document.getElementById('edit_pm10_filtro_final').value = item.pm10_filtro_final !== null ? item.pm10_filtro_final : '';
    const pm10PromEl = document.getElementById('edit_pm10_prom_disp');
    if (pm10PromEl) pm10PromEl.textContent = item.pm10_prom !== null ? Number(item.pm10_prom).toFixed(2) : '0.00';

    // Muestreo PST
    document.getElementById('edit_pst_filtro_inicial').value = item.pst_filtro_inicial !== null ? item.pst_filtro_inicial : '';
    document.getElementById('edit_pst_filtro_final').value = item.pst_filtro_final !== null ? item.pst_filtro_final : '';
    const pstPromEl = document.getElementById('edit_pst_prom_disp');
    if (pstPromEl) pstPromEl.textContent = item.pst_prom !== null ? Number(item.pst_prom).toFixed(2) : '0.00';

    // Staff
    const staffEl = document.getElementById('edit_modal_registered_by');
    if (staffEl) {
        staffEl.textContent = item.registered_by || REGISTERED_BY_HEADER || '';
    }
    const staffSelect = document.getElementById('edit_staff_id');
    if (staffSelect && item.staff_id) {
        staffSelect.value = item.staff_id;
    }

    // Coordenadas
    document.getElementById('edit_utm_easting').value = item.utm_easting || '';
    document.getElementById('edit_utm_northing').value = item.utm_northing || '';
    document.getElementById('edit_utm_zone').value = item.utm_zone || '19K';
    document.getElementById('edit_latitude').value = item.latitude || '';
    document.getElementById('edit_longitude').value = item.longitude || '';

    // Observaciones
    document.getElementById('edit_observations').value = item.raw_observations || item.observations || '';

    // Fotos
    sliderState.edit.photos = item.images || (item.image_path ? [item.image_path] : []);
    sliderState.edit.currentIndex = 0;
    renderPhotoSlider('edit');

    // Modo Solo Lectura por defecto al abrir
    applyEditModeState(false);

    modal.classList.add('active');
    modal.style.display = 'flex';

    const lat = item.latitude ? parseFloat(item.latitude) : -16.5034;
    const lng = item.longitude ? parseFloat(item.longitude) : -68.1324;
    setTimeout(() => initMiniMap('edit', lat, lng), 200);
}

function openEditMeasurementModal(item) {
    openViewMeasurementModal(item);
}

function closeEditMeasurementModal() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

function confirmDeleteMeasurement(id, num) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar estación de muestreo?',
            text: `Se eliminará la estación de monitoreo N° ${num}. Esta acción no se puede deshacer.`,
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
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/modulos/${MODULE_ID}/particulas-ambientales/mediciones/${id}/delete`;
                
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = CSRF_TOKEN;
                form.appendChild(csrfInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    } else {
        if (confirm(`¿Eliminar la estación de monitoreo N° ${num}?`)) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/modulos/${MODULE_ID}/particulas-ambientales/mediciones/${id}/delete`;
            
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = CSRF_TOKEN;
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        }
    }
}

function openPhotoViewer(url, title = 'Fotografía Ambiental') {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const titleEl = document.getElementById('photoViewerTitle');
    if (!modal || !img) return;

    img.src = url;
    if (titleEl) titleEl.textContent = title;
    modal.classList.add('active');
    modal.style.display = 'flex';
}

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal || typeof L === 'undefined') return;
    modal.classList.add('active');
    modal.style.display = 'flex';

    setTimeout(() => {
        const mapEl = document.getElementById('allLocationsMapLeaflet');
        if (!mapEl) return;
        const map = L.map('allLocationsMapLeaflet', {
            center: [-16.5034, -68.1324],
            zoom: 14,
            attributionControl: false
        });
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        const group = [];
        ALL_MEASUREMENTS_DATA.forEach(m => {
            if (m.latitude && m.longitude) {
                const marker = L.marker([m.latitude, m.longitude]).addTo(map);
                marker.bindPopup(`<b>${m.punto_medicion}</b><br>${m.area}<br>PM10: ${m.pm10_prom || '—'} µg/m³ | PST: ${m.pst_prom || '—'} µg/m³`);
                group.push([m.latitude, m.longitude]);
            }
        });

        if (group.length > 0) {
            map.fitBounds(group, { padding: [40, 40] });
        }
    }, 200);
}

function closeAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

function openExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
    }
}

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

function openNormativeTablesModal() {
    const modal = document.getElementById('normativeTablesModal');
    if (modal) {
        modal.classList.add('active');
        modal.style.display = 'flex';
    }
}

function closeNormativeTablesModal() {
    const modal = document.getElementById('normativeTablesModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

/* ==========================================================================
   5. BÚSQUEDA EN VIVO Y PAGINACIÓN
   ========================================================================== */
const PAGE_SIZE = 10;
let currentPage = 1;
let filteredRows = [];

function searchParticulasAmbLive() {
    const input = document.getElementById('particulasAmbSearchInput');
    const query = (input?.value || '').toLowerCase().trim();
    const rows = Array.from(document.querySelectorAll('.particulas-amb-data-row'));

    filteredRows = rows.filter(row => {
        const text = row.getAttribute('data-search') || '';
        return text.includes(query);
    });

    currentPage = 1;
    renderPagination();
}

function renderPagination() {
    const rows = Array.from(document.querySelectorAll('.particulas-amb-data-row'));
    const total = filteredRows.length;
    const totalPages = Math.ceil(total / PAGE_SIZE) || 1;

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * PAGE_SIZE;
    const endIdx = startIdx + PAGE_SIZE;

    rows.forEach(r => r.style.display = 'none');
    filteredRows.slice(startIdx, endIdx).forEach(r => r.style.display = '');

    const noResultsRow = document.getElementById('noResultsSearchRow');
    const emptyTableRow = document.getElementById('emptyTableRow');
    if (noResultsRow) {
        noResultsRow.style.display = (total === 0 && rows.length > 0) ? '' : 'none';
    }
    if (emptyTableRow) {
        emptyTableRow.style.display = (rows.length === 0) ? '' : 'none';
    }

    const totalEl = document.getElementById('partPageTotal');
    if (totalEl) totalEl.textContent = total;

    const controls = document.getElementById('particulasAmbPaginationControls');
    if (controls) {
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
            <button type="button" class="pagination-btn ${currentPage === totalPages ? 'disabled' : ''} onclick="goToPage(${currentPage + 1})">❯</button>
        `;
        controls.innerHTML = html;
    }
}

function goToPage(p) {
    currentPage = p;
    renderPagination();
}

/* ==========================================================================
   6. EXPORTACIÓN EXCEL CON EXCELJS
   ========================================================================== */
async function exportParticulasAmbExcel() {
    if (typeof ExcelJS === 'undefined') {
        alert('Cargando ExcelJS...');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    const sheet = workbook.addWorksheet('Partículas Ambientales', {
        pageSetup: { paperSize: 9, orientation: 'landscape', fitToPage: true }
    });

    const primaryColor = 'FF0284C7';
    sheet.mergeCells('A1:L2');
    const titleCell = sheet.getCell('A1');
    titleCell.value = 'PLANILLA TÉCNICA DE MONITOREO DE PARTÍCULAS AMBIENTALES (PM10 & PST)';
    titleCell.font = { name: 'Calibri', size: 13, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: primaryColor } };
    titleCell.alignment = { vertical: 'middle', horizontal: 'center' };

    sheet.mergeCells('A3:F3');
    sheet.getCell('A3').value = `INSTALACIÓN: ${TECHNICAL_HEADER_DATA.installationName || '—'}`;
    sheet.getCell('A3').font = { bold: true, size: 10 };

    sheet.mergeCells('G3:L3');
    sheet.getCell('G3').value = `FECHA: ${TECHNICAL_HEADER_DATA.startDateFormatted || '—'} al ${TECHNICAL_HEADER_DATA.endDateFormatted || '—'}`;
    sheet.getCell('G3').font = { bold: true, size: 10 };

    // Header Table
    const headers = ['N°', 'FECHA INICIO', 'HORA INICIO', 'HORAS DIF.', 'ÁREA', 'ESTACIÓN DE MUESTREO', 'TEMP MÁX/MÍN (°C)', 'PRESIÓN (mmHg)', 'VIENTO (Km/h)', 'PM10 (µg/m³)', 'PST (µg/m³)', 'CAUDAL (L/min)', 'OBSERVACIONES'];
    const row5 = sheet.getRow(5);
    row5.values = headers;
    row5.eachCell(c => {
        c.font = { bold: true, color: { argb: 'FFFFFFFF' } };
        c.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0369A1' } };
        c.alignment = { vertical: 'middle', horizontal: 'center' };
    });

    let rowIdx = 6;
    ALL_MEASUREMENTS_DATA.forEach(m => {
        const row = sheet.getRow(rowIdx);
        row.values = [
            m.num,
            m.fecha_inicio || m.date,
            m.hora_inicio || m.time,
            m.diferencia_horas || 24,
            m.area,
            m.punto_medicion,
            `${m.temp_max}° / ${m.temp_min}°C`,
            m.presion_atm || '—',
            `${m.vel_viento} (${m.dir_viento})`,
            m.pm10_prom || '—',
            m.pst_prom || '—',
            m.caudal || 1130,
            m.observations
        ];
        rowIdx++;
    });

    sheet.columns = [
        { width: 6 }, { width: 14 }, { width: 14 }, { width: 12 }, { width: 22 }, { width: 26 },
        { width: 18 }, { width: 14 }, { width: 16 }, { width: 14 }, { width: 14 }, { width: 14 },
        { width: 30 }
    ];

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Monitoreo_Particulas_Ambientales_${TECHNICAL_HEADER_DATA.installationName || 'Reporte'}.xlsx`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    closeExportModal();
}

document.addEventListener('DOMContentLoaded', function () {
    filteredRows = Array.from(document.querySelectorAll('.particulas-amb-data-row'));
    renderPagination();
});

// Exportaciones Globales
window.calculateParticulasAmbDiffHours = calculateParticulasAmbDiffHours;
window.calculateParticulasAmbGravimetric = calculateParticulasAmbGravimetric;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.slidePhotoNav = slidePhotoNav;
window.setSliderPhotoIndex = setSliderPhotoIndex;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.syncUtmToMap = syncUtmToMap;
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.toggleModalEditMode = toggleModalEditMode;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewer = closePhotoViewer;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openNormativeTablesModal = openNormativeTablesModal;
window.closeNormativeTablesModal = closeNormativeTablesModal;
window.searchParticulasAmbLive = searchParticulasAmbLive;
window.exportParticulasAmbExcel = exportParticulasAmbExcel;
