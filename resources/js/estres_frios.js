/**
 * METRIC V2 — MONITOREO DE ESTRÉS TÉRMICO (FRÍO / WCI)
 * JavaScript Interactivo, Cálculos Termo-Anemométricos, Mapas, Fotos y Exportación
 * Paridad 100% con Módulo de Iluminación y Estrés por Calor
 */

const cfg = window.METRIC_COLD_STRESS_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/estres-frio/header');

let allLocationsMap = null;
let allLocationsMarkers = [];
let createModalMap = null;
let createModalMarker = null;
let editModalMap = null;
let editModalMarker = null;
let isEditUnlocked = false;

const addslashes = (str) => String(str || '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');

// =========================================================================
// 1. GESTIÓN DE ARCHIVO FOTOGRÁFICO SLIDE / CARRUSEL
// =========================================================================
const modalPhotos = {
    create: [],
    edit: []
};

const modalPhotoIndex = {
    create: 0,
    edit: 0
};

function renderPhotoSlider(prefix) {
    const photos = modalPhotos[prefix] || [];
    const count = photos.length;
    let idx = modalPhotoIndex[prefix] || 0;

    if (idx >= count) idx = Math.max(0, count - 1);
    if (idx < 0) idx = 0;
    modalPhotoIndex[prefix] = idx;

    const img = document.getElementById(`${prefix}_slider_img`);
    const placeholder = document.getElementById(`${prefix}_slider_placeholder`);
    const counter = document.getElementById(`${prefix}_slider_counter`);
    const prevBtn = document.getElementById(`${prefix}_slider_btn_prev`);
    const nextBtn = document.getElementById(`${prefix}_slider_btn_next`);
    const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
    const delBtn = document.getElementById(`${prefix}_btn_delete_photo`);

    if (delBtn) {
        delBtn.style.display = (prefix === 'edit' && isEditUnlocked && count > 0) ? 'inline-flex' : 'none';
    }

    if (count === 0) {
        if (img) { img.src = ''; img.style.display = 'none'; }
        if (placeholder) placeholder.style.display = 'flex';
        if (counter) counter.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (thumbsStrip) { thumbsStrip.innerHTML = ''; thumbsStrip.style.display = 'none'; }
        return;
    }

    if (placeholder) placeholder.style.display = 'none';
    if (img) {
        img.src = photos[idx];
        img.style.display = 'block';
        img.onclick = () => openPhotoViewer(photos[idx], `Fotografía ${idx + 1} de ${count}`);
        img.style.cursor = 'zoom-in';
    }

    if (counter) {
        counter.textContent = `${idx + 1} / ${count}`;
        counter.style.display = 'inline-block';
    }

    if (prevBtn) prevBtn.style.display = count > 1 ? 'grid' : 'none';
    if (nextBtn) nextBtn.style.display = count > 1 ? 'grid' : 'none';

    if (thumbsStrip) {
        if (count > 1) {
            thumbsStrip.style.display = 'flex';
            let thumbsHtml = '';
            photos.forEach((src, i) => {
                const showDel = (prefix === 'edit' && isEditUnlocked);
                thumbsHtml += `
                    <div class="slider-thumb-item ${i === idx ? 'active' : ''}" onclick="selectSlidePhoto('${prefix}', ${i})" title="Foto #${i + 1}">
                        <img src="${src}" alt="Thumb ${i + 1}">
                        ${showDel ? `<button type="button" class="thumb-del-badge" onclick="event.stopPropagation(); deleteActivePhoto('${prefix}', ${i})" title="Eliminar foto #${i + 1}">✕</button>` : ''}
                    </div>
                `;
            });
            thumbsStrip.innerHTML = thumbsHtml;
        } else {
            thumbsStrip.innerHTML = '';
            thumbsStrip.style.display = 'none';
        }
    }
}

function slidePhotoNav(prefix, direction) {
    const photos = modalPhotos[prefix] || [];
    if (photos.length <= 1) return;
    let idx = modalPhotoIndex[prefix] + direction;
    if (idx < 0) idx = photos.length - 1;
    if (idx >= photos.length) idx = 0;
    modalPhotoIndex[prefix] = idx;
    renderPhotoSlider(prefix);
}

function selectSlidePhoto(prefix, index) {
    modalPhotoIndex[prefix] = index;
    renderPhotoSlider(prefix);
}

function deleteActivePhoto(prefix, targetIndex = null) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    const photos = modalPhotos[prefix] || [];
    if (photos.length === 0) return;

    const idxToRemove = targetIndex !== null ? targetIndex : (modalPhotoIndex[prefix] || 0);
    if (idxToRemove < 0 || idxToRemove >= photos.length) return;

    photos.splice(idxToRemove, 1);
    modalPhotos[prefix] = photos;

    if (modalPhotoIndex[prefix] >= photos.length) {
        modalPhotoIndex[prefix] = Math.max(0, photos.length - 1);
    }

    renderPhotoSlider(prefix);

    if (prefix === 'edit') {
        syncEditRemainingImages();
    }
}

function syncEditRemainingImages() {
    const hidden = document.getElementById('edit_remaining_images');
    if (hidden) {
        const existingRemaining = (modalPhotos.edit || []).filter(p => !p.startsWith('data:'));
        hidden.value = JSON.stringify(existingRemaining);
    }
    const delBtn = document.getElementById('edit_btn_delete_photo');
    if (delBtn) {
        delBtn.style.display = (isEditUnlocked && modalPhotos.edit && modalPhotos.edit.length > 0) ? 'inline-flex' : 'none';
    }
}

function handleMultipleImagesSelected(input, prefix) {
    if (!input || !input.files || input.files.length === 0) return;
    const files = Array.from(input.files);

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            modalPhotos[prefix].push(e.target.result);
            modalPhotoIndex[prefix] = modalPhotos[prefix].length - 1;
            renderPhotoSlider(prefix);
            if (prefix === 'edit') syncEditRemainingImages();
        };
        reader.readAsDataURL(file);
    });
}

// =========================================================================
// 2. MODAL DE CREACIÓN
// =========================================================================
function openCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modalPhotos.create = [];
        modalPhotoIndex.create = 0;
        renderPhotoSlider('create');

        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
        recalcColdStress('create');

        setTimeout(() => {
            initModalMiniMap('create', -16.5034120, -68.1324560);
        }, 150);
    }
}

function closeCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

// =========================================================================
// 3. MODO LECTURA Y DESBLOQUEO DE EDICIÓN EN MODAL DE DETALLE (3 COLUMNAS)
// =========================================================================
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
        'edit_puesto_trabajo',
        'edit_desc_actividades',
        'edit_metabolismo',
        'edit_aislamiento',
        'edit_temp_c',
        'edit_vel_viento_ms',
        'edit_hr_percent',
        'edit_presion_mmhg',
        'edit_utm_easting',
        'edit_utm_northing',
        'edit_utm_zone',
        'edit_observations'
    ];

    fieldsToToggle.forEach(fieldId => {
        const el = document.getElementById(fieldId);
        if (el) el.disabled = !isEditing;
    });

    // Visibilidad de Botones de Edición
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const gpsBtn = document.getElementById('edit_btn_gps');
    if (gpsBtn) gpsBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'inline-flex' : 'none';

    // Arrastre de marcador en mapa
    if (editModalMarker && editModalMarker.dragging) {
        if (isEditing) editModalMarker.dragging.enable();
        else editModalMarker.dragging.disable();
    }

    // Sincronizar estado de fotos y botón eliminar foto
    syncEditRemainingImages();
    renderPhotoSlider('edit');
}

function toggleModalEditMode() {
    applyEditModeState(!isEditUnlocked);
}

// =========================================================================
// 4. MODAL DE VER DETALLE / EDICIÓN (3 COLUMNAS)
// =========================================================================
function openViewMeasurementModal(item) {
    const modal = document.getElementById('editMeasurementModal');
    if (!modal) return;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${MODULE_ID}/estres-frio/mediciones/${item.id}`;
    }

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = (val !== null && val !== undefined) ? val : '';
    };

    // Extraer número limpio únicamente numérico (1, 2, 3...)
    const rawNumStr = String(item.num || item.point_number || '1');
    const cleanNum = parseInt(rawNumStr.replace(/\D/g, '')) || 1;

    setVal('edit_point_number', cleanNum);
    const ptDisp = document.getElementById('edit_pt_num_disp');
    if (ptDisp) ptDisp.textContent = cleanNum;

    setVal('edit_area', item.area || '');
    setVal('edit_puesto_trabajo', item.puesto_trabajo || '');
    setVal('edit_desc_actividades', item.desc_actividades || '');
    setVal('edit_metabolismo', item.metabolismo || 'Metabolismo moderado (200 W)');
    setVal('edit_aislamiento', item.aislamiento || 'Traje frigorífico completo (2.5 clo)');

    setVal('edit_measurement_date', item.date_raw || item.measurement_date || '');
    setVal('edit_measurement_time', item.time_raw || item.measurement_time || '');

    setVal('edit_temp_c', item.temp_c);
    setVal('edit_vel_viento_ms', item.vel_viento_ms);
    setVal('edit_hr_percent', item.hr_percent);
    setVal('edit_presion_mmhg', item.presion_mmhg);
    setVal('edit_observations', item.observations || '');

    // Técnico Registrador en Encabezado y Selector
    const staffEl = document.getElementById('edit_modal_registered_by');
    const staffSelect = document.getElementById('edit_staff_id');
    if (staffSelect) {
        if (item.staff_id) {
            staffSelect.value = item.staff_id;
        } else if (item.staff_name || item.registered_by) {
            const nameSearch = (item.staff_name || item.registered_by).toLowerCase();
            for (let i = 0; i < staffSelect.options.length; i++) {
                if (staffSelect.options[i].text.toLowerCase().includes(nameSearch)) {
                    staffSelect.selectedIndex = i;
                    break;
                }
            }
        }
        if (staffEl && staffSelect.selectedIndex >= 0 && staffSelect.options[staffSelect.selectedIndex]) {
            staffEl.textContent = staffSelect.options[staffSelect.selectedIndex].text.split('—')[0].trim();
        } else if (staffEl) {
            staffEl.textContent = item.staff_name || item.registered_by || REGISTERED_BY_HEADER || '';
        }
    } else if (staffEl) {
        staffEl.textContent = item.staff_name || item.registered_by || REGISTERED_BY_HEADER || '';
    }

    // Visor Fotográfico
    modalPhotos.edit = [];
    if (item.images && Array.isArray(item.images) && item.images.length > 0) {
        modalPhotos.edit = [...item.images];
    } else if (item.photos && Array.isArray(item.photos) && item.photos.length > 0) {
        modalPhotos.edit = [...item.photos];
    } else if (item.photo_url) {
        modalPhotos.edit = [item.photo_url];
    } else if (item.image_path) {
        modalPhotos.edit = [item.image_path];
    }
    modalPhotoIndex.edit = 0;
    syncEditRemainingImages();
    renderPhotoSlider('edit');

    // Coordenadas UTM y Lat/Lng
    let lat = (item.latitude !== null && item.latitude !== '') ? parseFloat(item.latitude) : NaN;
    let lng = (item.longitude !== null && item.longitude !== '') ? parseFloat(item.longitude) : NaN;
    let utmZone = item.utm_zone || '19K';
    let utmEasting = (item.utm_easting !== null && item.utm_easting !== '') ? parseFloat(item.utm_easting) : NaN;
    let utmNorthing = (item.utm_northing !== null && item.utm_northing !== '') ? parseFloat(item.utm_northing) : NaN;

    if (isNaN(utmEasting) || isNaN(utmNorthing)) {
        if (!isNaN(lat) && !isNaN(lng)) {
            const u = latLngToUTM(lat, lng);
            utmEasting = u.easting;
            utmNorthing = u.northing;
            utmZone = u.zone;
        } else {
            utmEasting = 218468.0;
            utmNorthing = 7627234.0;
            const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
            lat = pos.lat;
            lng = pos.lng;
        }
    } else if (isNaN(lat) || isNaN(lng)) {
        const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
        lat = pos.lat;
        lng = pos.lng;
    }

    setVal('edit_latitude', isNaN(lat) ? '' : lat.toFixed(7));
    setVal('edit_longitude', isNaN(lng) ? '' : lng.toFixed(7));
    setVal('edit_utm_zone', utmZone);
    setVal('edit_utm_easting', isNaN(utmEasting) ? '' : utmEasting.toFixed(1));
    setVal('edit_utm_northing', isNaN(utmNorthing) ? '' : utmNorthing.toFixed(1));

    // Iniciar SIEMPRE en Modo Consulta (Solo Lectura)
    applyEditModeState(false);

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    recalcColdStress('edit');

    setTimeout(() => {
        initModalMiniMap('edit', isNaN(lat) ? -16.5034120 : lat, isNaN(lng) ? -68.1324560 : lng);
    }, 150);
}

function openEditMeasurementModal(item) {
    openViewMeasurementModal(item);
}

function closeEditMeasurementModal() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
        applyEditModeState(false);
    }
}

// =========================================================================
// 5. CÁLCULO EN VIVO DE ÍNDICE DE ENFRIAMIENTO (WCI), SENSACIÓN Y TLE
// =========================================================================
function recalcColdStress(prefix) {
    const tVal = document.getElementById(`${prefix}_temp_c`)?.value;
    const vVal = document.getElementById(`${prefix}_vel_viento_ms`)?.value;

    const tempC = (tVal !== '' && tVal !== undefined && !isNaN(tVal)) ? parseFloat(tVal) : null;
    const velViento = (vVal !== '' && vVal !== undefined && !isNaN(vVal)) ? parseFloat(vVal) : 0.2;

    const dispWci = document.getElementById(`${prefix}_wci_display`);
    const dispSensacion = document.getElementById(`${prefix}_sensacion_display`);
    const dispTle = document.getElementById(`${prefix}_tle_display`);
    const badgeRiesgo = document.getElementById(`${prefix}_riesgo_badge`);

    if (tempC === null) {
        if (dispWci) dispWci.textContent = '—';
        if (dispSensacion) dispSensacion.textContent = '— °C';
        if (dispTle) dispTle.textContent = 'Jornada normal con EPP';
        if (badgeRiesgo) badgeRiesgo.innerHTML = `<span class="table-compliance-badge" style="background:#f1f5f9;color:#64748b;border:1px solid #cbd5e1;"><span>PENDIENTE</span></span>`;
        return;
    }

    const v = velViento > 0 ? velViento : 0.2;
    // Fórmula WCI Siple & Passel
    const wci = (10.45 + 10.0 * Math.sqrt(v) - v) * (33.0 - tempC);

    const vKmh = v * 3.6;
    let sensacion = tempC;
    if (vKmh > 4.8 && tempC <= 10.0) {
        sensacion = 13.12 + 0.6215 * tempC - 11.37 * Math.pow(vKmh, 0.16) + 0.3965 * tempC * Math.pow(vKmh, 0.16);
    } else {
        sensacion = tempC - (v * 1.2);
    }

    let riesgo = 'Riesgo Bajo';
    let tle = 'Jornada continua con EPP estándar';
    let isCompliant = true;

    if (wci < 1000) {
        riesgo = 'Riesgo Bajo';
        tle = 'Jornada continua con EPP estándar';
    } else if (wci < 1200) {
        riesgo = 'Moderado';
        tle = '45 a 60 min de labor / 10 min calentamiento';
    } else if (wci < 1400) {
        riesgo = 'Alto';
        tle = 'Máximo 30 min continuo / Ropa certificada';
        isCompliant = false;
    } else {
        riesgo = 'Crítico / Severo';
        tle = 'Máximo 15 min / Prohibido trabajo en solitario';
        isCompliant = false;
    }

    if (dispWci) dispWci.textContent = `${wci.toFixed(0)} W/m²`;
    if (dispSensacion) dispSensacion.textContent = `${sensacion.toFixed(1)} °C`;
    if (dispTle) dispTle.textContent = tle;

    if (badgeRiesgo) {
        if (isCompliant) {
            badgeRiesgo.innerHTML = `<span class="table-compliance-badge badge-cumple"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><span>${riesgo.toUpperCase()}</span></span>`;
        } else {
            badgeRiesgo.innerHTML = `<span class="table-compliance-badge badge-nocumple"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span>${riesgo.toUpperCase()}</span></span>`;
        }
    }
}

// =========================================================================
// 6. MINI MAPAS & COORDENADAS UTM
// =========================================================================
function initModalMiniMap(prefix, lat, lng) {
    const mapContainerId = `${prefix}_modal_map`;
    const container = document.getElementById(mapContainerId);
    if (!container || typeof L === 'undefined') return;

    lat = parseFloat(lat) || -16.5034120;
    lng = parseFloat(lng) || -68.1324560;

    if (prefix === 'create') {
        if (!createModalMap) {
            createModalMap = L.map(mapContainerId).setView([lat, lng], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(createModalMap);

            createModalMarker = L.marker([lat, lng], { draggable: true }).addTo(createModalMap);

            createModalMarker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                updateUtmFromLatLng('create', pos.lat, pos.lng);
            });

            createModalMap.on('click', (e) => {
                createModalMarker.setLatLng(e.latlng);
                updateUtmFromLatLng('create', e.latlng.lat, e.latlng.lng);
            });
        } else {
            createModalMap.invalidateSize();
            createModalMap.setView([lat, lng], 16);
            createModalMarker.setLatLng([lat, lng]);
        }
    } else {
        if (!editModalMap) {
            editModalMap = L.map(mapContainerId).setView([lat, lng], 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(editModalMap);

            editModalMarker = L.marker([lat, lng], { draggable: isEditUnlocked }).addTo(editModalMap);

            editModalMarker.on('dragend', (e) => {
                if (!isEditUnlocked) return;
                const pos = e.target.getLatLng();
                updateUtmFromLatLng('edit', pos.lat, pos.lng);
            });

            editModalMap.on('click', (e) => {
                if (!isEditUnlocked) return;
                editModalMarker.setLatLng(e.latlng);
                updateUtmFromLatLng('edit', e.latlng.lat, e.latlng.lng);
            });
        } else {
            editModalMap.invalidateSize();
            editModalMap.setView([lat, lng], 16);
            editModalMarker.setLatLng([lat, lng]);
            if (editModalMarker.dragging) {
                if (isEditUnlocked) editModalMarker.dragging.enable();
                else editModalMarker.dragging.disable();
            }
        }
    }
}

function updateUtmFromLatLng(prefix, lat, lng) {
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);

    const utm = latLngToUTM(lat, lng);
    const zInput = document.getElementById(`${prefix}_utm_zone`);
    const eInput = document.getElementById(`${prefix}_utm_easting`);
    const nInput = document.getElementById(`${prefix}_utm_northing`);
    if (zInput) zInput.value = utm.zone;
    if (eInput) eInput.value = utm.easting.toFixed(1);
    if (nInput) nInput.value = utm.northing.toFixed(1);
}

function syncUtmToMap(prefix) {
    const eInput = document.getElementById(`${prefix}_utm_easting`);
    const nInput = document.getElementById(`${prefix}_utm_northing`);
    const zInput = document.getElementById(`${prefix}_utm_zone`);

    const easting = parseFloat(eInput?.value);
    const northing = parseFloat(nInput?.value);
    const zone = zInput?.value || '19K';

    if (!isNaN(easting) && !isNaN(northing)) {
        const pos = utmToLatLng(easting, northing, zone);
        const latInput = document.getElementById(`${prefix}_latitude`);
        const lngInput = document.getElementById(`${prefix}_longitude`);
        if (latInput) latInput.value = pos.lat.toFixed(7);
        if (lngInput) lngInput.value = pos.lng.toFixed(7);

        const map = prefix === 'create' ? createModalMap : editModalMap;
        const marker = prefix === 'create' ? createModalMarker : editModalMarker;
        if (map && marker) {
            marker.setLatLng([pos.lat, pos.lng]);
            map.setView([pos.lat, pos.lng], 16);
        }
    }
}

function captureCoordinatesGPS(prefix) {
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización satelital.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateUtmFromLatLng(prefix, lat, lng);

            const map = prefix === 'create' ? createModalMap : editModalMap;
            const marker = prefix === 'create' ? createModalMarker : editModalMarker;
            if (map && marker) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
            }
        },
        function (err) {
            alert('No se pudo obtener la posición satelital: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

function latLngToUTM(lat, lng) {
    const zone = Math.floor((lng + 180) / 6) + 1;
    const utmZone = `${zone}K`;

    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - f * f);

    const latRad = lat * (Math.PI / 180);
    const lngRad = lng * (Math.PI / 180);
    const lngOrigin = ((zone - 1) * 6 - 180 + 3) * (Math.PI / 180);

    const n = a / Math.sqrt(1 - Math.pow(e * Math.sin(latRad), 2));
    const t = Math.pow(Math.tan(latRad), 2);
    const c = (Math.pow(e, 2) / (1 - Math.pow(e, 2))) * Math.pow(Math.cos(latRad), 2);
    const al = Math.cos(latRad) * (lngRad - lngOrigin);

    const m = a * (
        (1 - Math.pow(e, 2) / 4 - 3 * Math.pow(e, 4) / 64 - 5 * Math.pow(e, 6) / 256) * latRad
        - (3 * Math.pow(e, 2) / 8 + 3 * Math.pow(e, 4) / 32 + 45 * Math.pow(e, 6) / 1024) * Math.sin(2 * latRad)
        + (15 * Math.pow(e, 4) / 256 + 45 * Math.pow(e, 6) / 1024) * Math.sin(4 * latRad)
        - (35 * Math.pow(e, 6) / 3072) * Math.sin(6 * latRad)
    );

    const easting = k0 * n * (al + (1 - t + c) * Math.pow(al, 3) / 6 + (5 - 18 * t + Math.pow(t, 2) + 72 * c - 58 * Math.pow(e, 2)) * Math.pow(al, 5) / 120) + 500000.0;
    let northing = k0 * (m + n * Math.tan(latRad) * (Math.pow(al, 2) / 2 + (5 - t + 9 * c + 4 * Math.pow(c, 2)) * Math.pow(al, 4) / 24 + (61 - 58 * t + Math.pow(t, 2) + 600 * c - 330 * Math.pow(e, 2)) * Math.pow(al, 6) / 720));
    if (lat < 0) northing += 10000000.0;

    return { zone: utmZone, easting, northing };
}

function utmToLatLng(easting, northing, zoneStr) {
    const zone = parseInt(zoneStr) || 19;
    const isSouth = true; // Por defecto Bolivia/Sur

    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - f * f);
    const e1 = (1 - Math.sqrt(1 - e * e)) / (1 + Math.sqrt(1 - e * e));

    const x = easting - 500000.0;
    let y = northing;
    if (isSouth) y -= 10000000.0;

    const m = y / k0;
    const mu = m / (a * (1 - e * e / 4 - 3 * Math.pow(e, 4) / 64 - 5 * Math.pow(e, 6) / 256));

    const phi1Rad = mu + (3 * e1 / 2 - 27 * Math.pow(e1, 3) / 32) * Math.sin(2 * mu)
        + (21 * Math.pow(e1, 2) / 16 - 55 * Math.pow(e1, 4) / 32) * Math.sin(4 * mu)
        + (151 * Math.pow(e1, 3) / 96) * Math.sin(6 * mu);

    const n1 = a / Math.sqrt(1 - Math.pow(e * Math.sin(phi1Rad), 2));
    const t1 = Math.pow(Math.tan(phi1Rad), 2);
    const c1 = (e * e / (1 - e * e)) * Math.pow(Math.cos(phi1Rad), 2);
    const r1 = a * (1 - e * e) / Math.pow(1 - Math.pow(e * Math.sin(phi1Rad), 2), 1.5);
    const d = x / (n1 * k0);

    let latRad = phi1Rad - (n1 * Math.tan(phi1Rad) / r1) * (
        Math.pow(d, 2) / 2
        - (5 + 3 * t1 + 10 * c1 - 4 * Math.pow(c1, 2) - 9 * (e * e / (1 - e * e))) * Math.pow(d, 4) / 24
        + (61 + 90 * t1 + 298 * c1 + 45 * Math.pow(t1, 2) - 252 * (e * e / (1 - e * e)) - 3 * Math.pow(c1, 2)) * Math.pow(d, 6) / 720
    );

    const lngOrigin = ((zone - 1) * 6 - 180 + 3) * (Math.PI / 180);
    let lngRad = lngOrigin + (
        d
        - (1 + 2 * t1 + c1) * Math.pow(d, 3) / 6
        + (5 - 2 * c1 + 28 * t1 - 3 * Math.pow(c1, 2) + 8 * (e * e / (1 - e * e)) + 24 * Math.pow(t1, 2)) * Math.pow(d, 5) / 120
    ) / Math.cos(phi1Rad);

    return {
        lat: latRad * (180 / Math.PI),
        lng: lngRad * (180 / Math.PI)
    };
}

// =========================================================================
// 7. AUTO-GUARDADO INLINE DE ENCABEZADO TÉCNICO
// =========================================================================
let autoSaveTimeout = null;

function autoSaveHeaderField() {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.classList.add('saving');
        badge.querySelector('span').textContent = 'Guardando...';
    }

    clearTimeout(autoSaveTimeout);
    autoSaveTimeout = setTimeout(() => {
        const payload = {
            installation_name: document.getElementById('inline_installation_name')?.value || '',
            start_date: document.getElementById('inline_start_date')?.value || '',
            end_date: document.getElementById('inline_end_date')?.value || '',
            monitoring_type: document.getElementById('inline_monitoring_type')?.value || '',
            _token: CSRF_TOKEN,
        };

        fetch(UPDATE_HEADER_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify(payload),
        })
        .then(res => res.json())
        .then(data => {
            if (badge) {
                badge.classList.remove('saving');
                badge.querySelector('span').textContent = 'Guardado';
                setTimeout(() => { if (badge.querySelector('span')) badge.querySelector('span').textContent = 'Guardado'; }, 2500);
            }
        })
        .catch(err => {
            if (badge) {
                badge.classList.remove('saving');
                badge.querySelector('span').textContent = 'Error';
            }
        });
    }, 600);
}

// =========================================================================
// 8. FILTROS, BÚSQUEDA Y PAGINACIÓN REACTIVA EN TABLA (10 POR PÁGINA)
// =========================================================================
let currentColdStressPage = 1;
const COLD_PAGE_SIZE = 10;
let currentColdStressFilter = 'all';

function getMatchingColdStressRows() {
    const q = document.getElementById('coldStressSearchInput')?.value.toLowerCase().trim() || '';
    const allRows = Array.from(document.querySelectorAll('#coldStressMasterTable tbody tr.cold-stress-data-row'));

    return allRows.filter(r => {
        const compliantStatus = r.getAttribute('data-compliant') || '';
        const risk = (r.getAttribute('data-risk') || '').toLowerCase();
        const text = (r.getAttribute('data-search') || '').toLowerCase();

        let matchesFilter = true;
        if (currentColdStressFilter === 'all') {
            matchesFilter = true;
        } else if (currentColdStressFilter === 'compliant') {
            matchesFilter = (compliantStatus === 'compliant');
        } else if (currentColdStressFilter === 'non-compliant') {
            matchesFilter = (compliantStatus === 'non-compliant');
        } else if (currentColdStressFilter === 'Bajo') {
            matchesFilter = risk.includes('bajo');
        } else if (currentColdStressFilter === 'Moderado') {
            matchesFilter = risk.includes('moderado');
        } else if (currentColdStressFilter === 'Alto') {
            matchesFilter = (risk.includes('alto') || risk.includes('severo') || risk.includes('crítico'));
        }

        const matchesSearch = !q || text.includes(q);
        return matchesFilter && matchesSearch;
    });
}

function updateColdStressPagination() {
    const matchingRows = getMatchingColdStressRows();
    const allRows = Array.from(document.querySelectorAll('#coldStressMasterTable tbody tr.cold-stress-data-row'));
    const totalItems = matchingRows.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / COLD_PAGE_SIZE));

    if (currentColdStressPage > totalPages) currentColdStressPage = totalPages;
    if (currentColdStressPage < 1) currentColdStressPage = 1;

    const startIdx = (currentColdStressPage - 1) * COLD_PAGE_SIZE;
    const endIdx = startIdx + COLD_PAGE_SIZE;

    allRows.forEach(row => row.style.display = 'none');
    matchingRows.slice(startIdx, endIdx).forEach(row => {
        row.style.display = '';
    });

    const emptyTableRow = document.getElementById('emptyTableRow');
    const noResultsSearchRow = document.getElementById('noResultsSearchRow');
    if (allRows.length === 0) {
        if (emptyTableRow) emptyTableRow.style.display = '';
        if (noResultsSearchRow) noResultsSearchRow.style.display = 'none';
    } else if (totalItems === 0) {
        if (emptyTableRow) emptyTableRow.style.display = 'none';
        if (noResultsSearchRow) noResultsSearchRow.style.display = '';
    } else {
        if (emptyTableRow) emptyTableRow.style.display = 'none';
        if (noResultsSearchRow) noResultsSearchRow.style.display = 'none';
    }

    const startEl = document.getElementById('coldStressPageStart');
    const endEl = document.getElementById('coldStressPageEnd');
    const totalEl = document.getElementById('coldStressPageTotal');

    if (startEl) startEl.textContent = totalItems === 0 ? 0 : (startIdx + 1);
    if (endEl) endEl.textContent = Math.min(endIdx, totalItems);
    if (totalEl) totalEl.textContent = totalItems;

    renderColdStressPaginationControls(totalPages);
}

function renderColdStressPaginationControls(totalPages) {
    const container = document.getElementById('coldStressPaginationControls');
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    html += `<button type="button" class="cold-pag-btn ill-pag-btn" onclick="goToColdStressPage(${currentColdStressPage - 1})" ${currentColdStressPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    </button>`;

    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentColdStressPage - 1 && p <= currentColdStressPage + 1)) {
            html += `<button type="button" class="cold-pag-btn ill-pag-btn ${p === currentColdStressPage ? 'active' : ''}" onclick="goToColdStressPage(${p})">${p}</button>`;
        } else if (p === currentColdStressPage - 2 || p === currentColdStressPage + 2) {
            html += `<span style="padding: 0 4px; color: #94a3b8; font-weight: 700;">...</span>`;
        }
    }

    html += `<button type="button" class="cold-pag-btn ill-pag-btn" onclick="goToColdStressPage(${currentColdStressPage + 1})" ${currentColdStressPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </button>`;

    container.innerHTML = html;
}

function goToColdStressPage(page) {
    currentColdStressPage = page;
    updateColdStressPagination();
    const table = document.getElementById('coldStressMasterTable');
    if (table) table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function filterColdStress(type, btn) {
    currentColdStressFilter = type;
    document.querySelectorAll('#coldStressFilterGroup .filter-pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    currentColdStressPage = 1;
    updateColdStressPagination();
}

function searchColdStressLive() {
    currentColdStressPage = 1;
    updateColdStressPagination();
}

// =========================================================================
// 9. MODALES AUXILIARES (UBICACIONES, EXPORT, TABLAS, FOTOS)
// =========================================================================
function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        initAllLocationsLeafletMap();
    }, 200);
}

function closeAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function initAllLocationsLeafletMap() {
    const container = document.getElementById('allLocationsMapLeaflet');
    if (!container || typeof L === 'undefined') return;

    if (!allLocationsMap) {
        allLocationsMap = L.map('allLocationsMapLeaflet').setView([-16.503412, -68.132456], 15);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(allLocationsMap);
    } else {
        allLocationsMap.invalidateSize();
    }

    // Limpiar marcadores previos
    allLocationsMarkers.forEach(m => allLocationsMap.removeLayer(m));
    allLocationsMarkers = [];

    const latLngBounds = [];

    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        let lat = (m.latitude !== null && m.latitude !== '') ? parseFloat(m.latitude) : NaN;
        let lng = (m.longitude !== null && m.longitude !== '') ? parseFloat(m.longitude) : NaN;

        if (isNaN(lat) || isNaN(lng)) {
            if (m.utm_easting && m.utm_northing) {
                const pos = utmToLatLng(parseFloat(m.utm_easting), parseFloat(m.utm_northing), m.utm_zone || '19K');
                lat = pos.lat;
                lng = pos.lng;
            } else {
                lat = -16.503412 + (idx * 0.00035);
                lng = -68.132456 + (idx * 0.00035);
            }
        }

        latLngBounds.push([lat, lng]);

        // Número limpio únicamente numérico (1, 2, 3...)
        const rawNumStr = String(m.num || m.point_number || (idx + 1));
        const cleanNum = parseInt(rawNumStr.replace(/\D/g, '')) || (idx + 1);

        const isCompliant = m.is_compliant !== false && m.is_compliant !== 0 && m.is_compliant !== '0';

        const pinHtml = `
            <div class="custom-map-pin ${isCompliant ? 'pin-compliant' : 'pin-non-compliant'}" title="Punto ${cleanNum}">
                ${cleanNum}
            </div>
        `;

        const customIcon = L.divIcon({
            html: pinHtml,
            className: 'custom-div-pin-wrapper',
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -16]
        });

        const marker = L.marker([lat, lng], { icon: customIcon }).addTo(allLocationsMap);

        const firstPhoto = (m.photos && m.photos.length > 0) ? m.photos[0] : (m.photo_url || m.image_path || null);
        let photoSection = '';
        if (firstPhoto) {
            photoSection = `
                <div style="margin-top: 6px; border-radius: 8px; overflow: hidden; border: 1px solid #cbd5e1; cursor: zoom-in;" onclick="openPhotoViewer('${firstPhoto}', 'Punto ${cleanNum}: ${addslashes(m.puesto_trabajo || '')}')">
                    <img src="${firstPhoto}" style="width: 100%; height: 95px; object-fit: cover; display: block;">
                </div>
            `;
        }

        const popupHtml = `
            <div style="font-family: 'Outfit', sans-serif; font-size: 12px; line-height: 1.4; min-width: 220px; max-width: 270px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">
                    <strong style="font-size: 13px; color: #0f1c2e;">Punto ${cleanNum}</strong>
                    <span style="font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: ${isCompliant ? '#ecfdf5' : '#fef2f2'}; color: ${isCompliant ? '#059669' : '#dc2626'}; border: 1px solid ${isCompliant ? '#a7f3d0' : '#fecaca'};">
                        ${isCompliant ? 'CONFORME' : 'RIESGO ALTO'}
                    </span>
                </div>
                <div style="font-weight: 700; color: #1e293b; margin-bottom: 2px;">${m.puesto_trabajo || 'Puesto no especificado'}</div>
                <div style="font-size: 11.5px; color: #64748b; margin-bottom: 6px;">${m.area || 'Área Operativa'} ${m.desc_actividades ? '• ' + m.desc_actividades : ''}</div>
                ${photoSection}
                <div style="display: flex; align-items: center; gap: 6px; background: #f0f9ff; border: 1px solid #bae6fd; padding: 4px 8px; border-radius: 6px; margin-top: 6px; margin-bottom: 6px;">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span style="font-size: 11px; color: #0369a1;">Registrado por: <strong>${m.staff_name || m.registered_by || 'Técnico'}</strong></span>
                </div>
                <div style="font-size: 11.5px; margin-top: 4px; border-top: 1px dashed #e2e8f0; padding-top: 4px; display: flex; justify-content: space-between;">
                    <span>Sensación: <strong>${m.sensacion_termica_c !== null ? m.sensacion_termica_c + ' °C' : '—'}</strong></span>
                    <span>WCI: <strong>${m.indice_viento_wci !== null ? m.indice_viento_wci + ' W/m²' : '—'}</strong></span>
                </div>
            </div>
        `;

        marker.bindPopup(popupHtml);
        allLocationsMarkers.push(marker);
    });

    if (latLngBounds.length > 0) {
        allLocationsMap.fitBounds(latLngBounds, { padding: [30, 30] });
    }
}

function focusPointOnAllLocationsMap(idx) {
    if (allLocationsMarkers[idx] && allLocationsMap) {
        const m = allLocationsMarkers[idx];
        allLocationsMap.setView(m.getLatLng(), 16);
        m.openPopup();
    }
}

function openExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function openTablesModal() {
    const modal = document.getElementById('tablesModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closeTablesModal() {
    const modal = document.getElementById('tablesModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function switchColdStressTableTab(tab, btn) {
    document.querySelectorAll('#tablesModal .filter-pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    document.querySelectorAll('#tablesModal .table-tab-panel').forEach(p => p.style.display = 'none');

    if (tab === 'report') {
        const p = document.getElementById('panelColdReport');
        if (p) p.style.display = 'block';
    } else if (tab === 'wci') {
        const p = document.getElementById('panelColdWci');
        if (p) p.style.display = 'block';
    } else if (tab === 'tle') {
        const p = document.getElementById('panelColdTle');
        if (p) p.style.display = 'block';
    }
}

function openPhotoViewer(photoUrl, title) {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const t = document.getElementById('photoViewerTitle');
    if (modal && img) {
        img.src = photoUrl;
        if (t) t.textContent = title || 'Evidencia Fotográfica';
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function openPhotoViewerModal(photoUrl, title) {
    openPhotoViewer(photoUrl, title);
}

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function closePhotoViewerModal() {
    closePhotoViewer();
}

function openPhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
}

function closePhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
}

function confirmDeleteMeasurement(id, pointCode) {
    const url = `/modulos/${MODULE_ID}/estres-frio/mediciones/${id}`;
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar Punto de Medición?',
            text: `Se eliminará permanentemente la evaluación #${pointCode}. Esta acción no se puede revertir.`,
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
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteMeasurementForm');
                if (form) {
                    form.action = url;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar la medición #${pointCode}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = url;
                form.submit();
            }
        }
    }
}

// =========================================================================
// 10. EXPORTACIÓN TÉCNICA A EXCEL (EXCELJS)
// =========================================================================
async function downloadColdStressExcel() {
    if (typeof ExcelJS === 'undefined') {
        alert('ExcelJS se está cargando. Por favor reintenta en unos segundos.');
        return;
    }

    const wb = new ExcelJS.Workbook();
    const ws = wb.addWorksheet('Estrés Térmico Frío WCI');

    // Estilos Corporativos METRIC
    const headerFill = {
        type: 'pattern',
        pattern: 'solid',
        fgColor: { argb: 'FF0F1C2E' }
    };
    const headerFont = {
        name: 'Segoe UI',
        size: 10,
        bold: true,
        color: { argb: 'FFFFFFFF' }
    };

    ws.columns = [
        { header: 'N°', key: 'num', width: 6 },
        { header: 'PUESTO DE TRABAJO', key: 'puesto_trabajo', width: 24 },
        { header: 'ÁREA / SECCIÓN', key: 'area', width: 22 },
        { header: 'HORA DE MEDICIÓN', key: 'time_raw', width: 18 },
        { header: 'DESCRIPCIÓN DE ACTIVIDADES', key: 'desc_actividades', width: 30 },
        { header: 'METABOLISMO', key: 'metabolismo', width: 26 },
        { header: 'AISLAMIENTO (clo)', key: 'aislamiento', width: 26 },
        { header: 'TEMP AIRE (°C)', key: 'temp_c', width: 14 },
        { header: 'VEL. VIENTO (m/s)', key: 'vel_viento_ms', width: 16 },
        { header: '%HR', key: 'hr_percent', width: 10 },
        { header: 'P (mmHg)', key: 'presion_mmhg', width: 12 },
        { header: 'SENSACIÓN TÉRMICA (°C)', key: 'sensacion_termica_c', width: 22 },
        { header: 'ÍNDICE WCI (W/m²)', key: 'indice_viento_wci', width: 18 },
        { header: 'NIVEL DE RIESGO', key: 'nivel_riesgo', width: 18 },
        { header: 'TLE MÁXIMO', key: 'tiempo_limite_exposicion', width: 30 },
        { header: 'REGISTRADO POR', key: 'staff_name', width: 22 },
        { header: 'OBSERVACIONES', key: 'observations', width: 28 },
    ];

    ws.getRow(1).height = 26;
    ws.getRow(1).fill = headerFill;
    ws.getRow(1).font = headerFont;
    ws.getRow(1).alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };

    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        const rawNumStr = String(m.num || m.point_number || (idx + 1));
        const cleanNum = parseInt(rawNumStr.replace(/\D/g, '')) || (idx + 1);

        const row = ws.addRow({
            num: cleanNum,
            puesto_trabajo: m.puesto_trabajo || '—',
            area: m.area || '—',
            time_raw: m.time_raw || m.time || '—',
            desc_actividades: m.desc_actividades || '—',
            metabolismo: m.metabolismo || '—',
            aislamiento: m.aislamiento || '—',
            temp_c: m.temp_c !== null && m.temp_c !== undefined ? `${Number(m.temp_c).toFixed(1)}°C` : '—',
            vel_viento_ms: m.vel_viento_ms !== null && m.vel_viento_ms !== undefined ? Number(m.vel_viento_ms).toFixed(2) : '—',
            hr_percent: m.hr_percent !== null && m.hr_percent !== undefined ? `${Number(m.hr_percent).toFixed(0)}%` : '—',
            presion_mmhg: m.presion_mmhg !== null && m.presion_mmhg !== undefined ? Number(m.presion_mmhg).toFixed(0) : '—',
            sensacion_termica_c: m.sensacion_termica_c !== null && m.sensacion_termica_c !== undefined ? `${Number(m.sensacion_termica_c).toFixed(1)}°C` : '—',
            indice_viento_wci: m.indice_viento_wci !== null && m.indice_viento_wci !== undefined ? Number(m.indice_viento_wci).toFixed(0) : '—',
            nivel_riesgo: m.nivel_riesgo || 'Bajo',
            tiempo_limite_exposicion: m.tiempo_limite_exposicion || 'Jornada normal',
            staff_name: m.staff_name || '—',
            observations: m.observations || 'Sin observaciones'
        });

        row.height = 22;
        row.alignment = { vertical: 'middle' };
        row.getCell('num').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('puesto_trabajo').alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell('area').alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell('time_raw').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('desc_actividades').alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell('metabolismo').alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell('aislamiento').alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell('temp_c').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('vel_viento_ms').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('hr_percent').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('presion_mmhg').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('sensacion_termica_c').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('indice_viento_wci').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('nivel_riesgo').alignment = { vertical: 'middle', horizontal: 'center' };
        row.getCell('tiempo_limite_exposicion').alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell('staff_name').alignment = { vertical: 'middle', horizontal: 'left' };
        row.getCell('observations').alignment = { vertical: 'middle', horizontal: 'left' };
    });

    const buffer = await wb.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Estres_Termico_Frio_Modulo_${MODULE_ID}.xlsx`;
    a.click();
    window.URL.revokeObjectURL(url);
}

// =========================================================================
// 11. EXPOSICIÓN GLOBAL A WINDOW
// =========================================================================
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.applyEditModeState = applyEditModeState;
window.toggleModalEditMode = toggleModalEditMode;
window.recalcColdStress = recalcColdStress;
window.captureCoordinatesGPS = captureCoordinatesGPS;
window.syncUtmToMap = syncUtmToMap;
window.autoSaveHeaderField = autoSaveHeaderField;
window.filterColdStress = filterColdStress;
window.searchColdStressLive = searchColdStressLive;
window.goToColdStressPage = goToColdStressPage;
window.updateColdStressPagination = updateColdStressPagination;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openTablesModal = openTablesModal;
window.closeTablesModal = closeTablesModal;
window.switchColdStressTableTab = switchColdStressTableTab;
window.openPhotoViewer = openPhotoViewer;
window.openPhotoViewerModal = openPhotoViewerModal;
window.closePhotoViewer = closePhotoViewer;
window.closePhotoViewerModal = closePhotoViewerModal;
window.openPhotoReportModal = openPhotoReportModal;
window.closePhotoReportModal = closePhotoReportModal;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.downloadColdStressExcel = downloadColdStressExcel;
window.slidePhotoNav = slidePhotoNav;
window.selectSlidePhoto = selectSlidePhoto;
window.deleteActivePhoto = deleteActivePhoto;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;

// Inicializar paginación al cargar la página
document.addEventListener('DOMContentLoaded', () => {
    updateColdStressPagination();
});
