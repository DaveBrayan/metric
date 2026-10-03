/**
 * METRIC v2 — MÓDULO DE INSPECCIÓN FOTOGRÁFICA
 * Interactividad del cliente, modal en 3 columnas, carrusel de fotografías,
 * mapas interactivos Leaflet y georreferenciación.
 */

document.addEventListener('DOMContentLoaded', function () {
    initPhotographicModule();
});

// Configuración global
const cfg = window.METRIC_PHOTOGRAPHIC_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';

// Instancias y estados de mapa
let allLocationsMapInstance = null;
let allLocationsMarkers = [];
let singleMapInstance = null;
let singleMapMarkers = [];
let modalMiniMapInstance = null;
let modalMiniMapMarker = null;

// Estados del visor y carrusel de fotos
let currentViewerImages = [];
let currentViewerIndex = 0;
let modalPhotos = { edit: [] };
let modalPhotoIndex = { edit: 0 };
let isModalEditMode = false;

function initPhotographicModule() {
    // Escuchar cambios para autosave de encabezado
    const inputs = ['inline_installation_name', 'inline_start_date', 'inline_end_date', 'inline_monitoring_type'];
    inputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', autoSaveHeaderField);
        }
    });

    // Tecla Escape para cerrar modales abiertos
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAllOpenModals();
        }
        if (document.getElementById('photoViewerModal')?.classList.contains('open')) {
            if (e.key === 'ArrowLeft') navigatePhotoViewer(-1);
            if (e.key === 'ArrowRight') navigatePhotoViewer(1);
        }
    });
}

/**
 * Cierra todos los modales abiertos en la página
 */
function closeAllOpenModals() {
    closeEditMeasurementModal();
    closeMapModal();
    closeAllLocationsModal();
    closePhotoViewerModal();
    closeExportModal();
    closePhotoReportModal();
}

/**
 * Búsqueda en vivo de filas en la tabla
 */
function searchPhotographicLive() {
    const input = document.getElementById('photographicSearchInput');
    if (!input) return;
    const term = input.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.photographic-data-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const searchData = row.getAttribute('data-search') || '';
        if (term === '' || searchData.includes(term)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const counter = document.getElementById('tablePointsCounter');
    if (counter) {
        counter.textContent = `${visibleCount} Puntos`;
    }
}

/**
 * Autoguardado de campos de encabezado técnico
 */
function autoSaveHeaderField() {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (!cfg.updateHeaderUrl) return;

    if (badge) {
        badge.innerHTML = '<span>Guardando...</span>';
        badge.style.background = '#fef3c7';
        badge.style.color = '#b45309';
    }

    const payload = {
        _token: CSRF_TOKEN,
        installation_name: document.getElementById('inline_installation_name')?.value || '',
        start_date: document.getElementById('inline_start_date')?.value || null,
        end_date: document.getElementById('inline_end_date')?.value || null,
        monitoring_type: document.getElementById('inline_monitoring_type')?.value || '',
    };

    fetch(cfg.updateHeaderUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(res => {
        if (badge) {
            badge.innerHTML = `
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Guardado</span>
            `;
            badge.style.background = '#e0f2fe';
            badge.style.color = '#0284c7';
        }
    })
    .catch(() => {
        if (badge) {
            badge.innerHTML = '<span>Error al guardar</span>';
            badge.style.background = '#fee2e2';
            badge.style.color = '#ef4444';
        }
    });
}

/* ==========================================================================
   MODAL EN 3 COLUMNAS: MODO CONSULTA (SOLO LECTURA) VS MODO EDICIÓN
   ========================================================================== */

function openViewMeasurementModal(item) {
    const modal = document.getElementById('editMeasurementModal');
    const form = document.getElementById('editMeasurementForm');
    if (!modal || !form || !item) return;

    form.action = `/modulos/${MODULE_ID}/inspeccion-fotografica/mediciones/${item.id}`;

    // Columna 1: Datos Generales
    document.getElementById('edit_measurement_id').value = item.id;
    document.getElementById('edit_point_number').value = item.point_number || '';
    document.getElementById('edit_pt_num_disp').textContent = item.point_number || '01';

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

    document.getElementById('edit_inspection_date').value = item.date || '';
    document.getElementById('edit_inspection_time').value = item.time || '';
    document.getElementById('edit_area').value = item.area || '';

    // Selector de Categoría
    const obsSelect = document.getElementById('edit_observation');
    if (obsSelect) {
        const catVal = (item.observation || '').trim();
        let matched = false;
        for (let i = 0; i < obsSelect.options.length; i++) {
            if (obsSelect.options[i].value.toLowerCase() === catVal.toLowerCase()) {
                obsSelect.selectedIndex = i;
                matched = true;
                break;
            }
        }
        if (!matched && catVal) {
            const opt = document.createElement('option');
            opt.value = catVal;
            opt.textContent = catVal;
            opt.selected = true;
            obsSelect.appendChild(opt);
        }
    }

    document.getElementById('edit_description').value = item.description || '';

    // Columna 2: Carrusel Fotográfico
    modalPhotos.edit = [];
    if (item.images && Array.isArray(item.images) && item.images.length > 0) {
        modalPhotos.edit = [...item.images];
    } else if (item.image_path) {
        modalPhotos.edit = [item.image_path];
    }
    modalPhotoIndex.edit = 0;
    renderPhotoSlider('edit');
    syncRemainingImagesInputs('edit');

    // Columna 3: Coordenadas y Mini-mapa
    let easting = item.utm_easting ? parseFloat(item.utm_easting) : null;
    let northing = item.utm_northing ? parseFloat(item.utm_northing) : null;
    let zone = item.utm_zone || '20K';
    let lat = item.latitude !== null && item.latitude !== '' ? parseFloat(item.latitude) : NaN;
    let lng = item.longitude !== null && item.longitude !== '' ? parseFloat(item.longitude) : NaN;

    if ((!easting || !northing) && !isNaN(lat) && !isNaN(lng)) {
        const utm = latLngToUtm(lat, lng);
        easting = utm.easting;
        northing = utm.northing;
        zone = utm.zone;
    } else if (easting && northing && (isNaN(lat) || isNaN(lng))) {
        const pos = utmToLatLng(easting, northing, zone);
        lat = pos.lat;
        lng = pos.lng;
    }

    if (!easting || !northing) {
        easting = 585325;
        northing = 8169231;
        zone = '20K';
        if (isNaN(lat) || isNaN(lng)) {
            lat = -16.5034;
            lng = -68.1324;
        }
    }

    const eInput = document.getElementById('edit_utm_easting');
    const nInput = document.getElementById('edit_utm_northing');
    const zInput = document.getElementById('edit_utm_zone');
    const utmDisp = document.getElementById('edit_utm_display');
    const latInput = document.getElementById('edit_latitude');
    const lngInput = document.getElementById('edit_longitude');

    if (eInput) eInput.value = easting ? Math.round(easting) : '';
    if (nInput) nInput.value = northing ? Math.round(northing) : '';
    if (zInput) zInput.value = zone;
    if (utmDisp) utmDisp.textContent = `E: ${easting ? Math.round(easting) : '—'}, N: ${northing ? Math.round(northing) : '—'}, Z: ${zone}`;
    if (latInput) latInput.value = !isNaN(lat) ? lat.toFixed(7) : '';
    if (lngInput) lngInput.value = !isNaN(lng) ? lng.toFixed(7) : '';

    // Iniciar SIEMPRE en Modo Consulta
    applyEditModeState(false);

    modal.classList.add('open');

    setTimeout(() => {
        initModalMiniMap('edit', !isNaN(lat) ? lat : -16.5034, !isNaN(lng) ? lng : -68.1324);
    }, 180);
}

function openEditMeasurementModal(item) {
    openViewMeasurementModal(item);
}

function closeEditMeasurementModal() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        applyEditModeState(false);
    }
}

function toggleModalEditMode() {
    applyEditModeState(!isModalEditMode);
}

function applyEditModeState(editing) {
    isModalEditMode = editing;
    const form = document.getElementById('editMeasurementForm');
    const badge = document.getElementById('modalModeStatusBadge');
    const btnText = document.getElementById('btnToggleEditModeText');
    const btn = document.getElementById('btnToggleEditMode');
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    const btnAddPhotos = document.getElementById('edit_btn_add_photos');
    const btnDeletePhoto = document.getElementById('edit_btn_delete_photo');
    const btnGps = document.getElementById('edit_btn_gps');

    if (editing) {
        form?.classList.remove('modal-view-mode');
        if (badge) {
            badge.textContent = 'Modo Edición';
            badge.className = 'modal-badge-edit';
        }
        if (btnText) btnText.textContent = 'Consultar';
        if (btn) btn.classList.add('active-editing');
        if (submitBtn) submitBtn.style.display = 'inline-flex';
        if (btnAddPhotos) btnAddPhotos.style.display = modalPhotos.edit.length < 3 ? 'inline-flex' : 'none';
        if (btnDeletePhoto) btnDeletePhoto.style.display = modalPhotos.edit.length > 0 ? 'inline-flex' : 'none';
        if (btnGps) btnGps.style.display = 'inline-flex';

        // Habilitar campos
        form?.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.id !== 'edit_measurement_id' && el.id !== 'edit_point_number') {
                el.removeAttribute('disabled');
            }
        });

        if (modalMiniMapMarker) {
            modalMiniMapMarker.dragging?.enable();
        }
    } else {
        form?.classList.add('modal-view-mode');
        if (badge) {
            badge.textContent = 'Solo Lectura';
            badge.className = 'modal-badge-view';
        }
        if (btnText) btnText.textContent = 'Editar';
        if (btn) btn.classList.remove('active-editing');
        if (submitBtn) submitBtn.style.display = 'none';
        if (btnAddPhotos) btnAddPhotos.style.display = 'none';
        if (btnDeletePhoto) btnDeletePhoto.style.display = 'none';
        if (btnGps) btnGps.style.display = 'none';

        // Deshabilitar campos
        form?.querySelectorAll('input, select, textarea').forEach(el => {
            el.setAttribute('disabled', 'disabled');
        });

        if (modalMiniMapMarker) {
            modalMiniMapMarker.dragging?.disable();
        }
    }
}

/* ==========================================================================
   SLIDER / CARRUSEL FOTOGRÁFICO EN MODAL
   ========================================================================== */

function renderPhotoSlider(scope) {
    const list = modalPhotos[scope] || [];
    const idx = modalPhotoIndex[scope] || 0;

    const imgEl = document.getElementById(`${scope}_slider_img`);
    const placeholder = document.getElementById(`${scope}_slider_placeholder`);
    const counter = document.getElementById(`${scope}_slider_counter`);
    const btnPrev = document.getElementById(`${scope}_slider_btn_prev`);
    const btnNext = document.getElementById(`${scope}_slider_btn_next`);
    const thumbs = document.getElementById(`${scope}_slider_thumbs`);
    const indicator = document.getElementById(`${scope}_photo_count_indicator`);
    const btnAdd = document.getElementById(`${scope}_btn_add_photos`);
    const btnDel = document.getElementById(`${scope}_btn_delete_photo`);

    if (indicator) indicator.textContent = `${list.length} ${list.length === 1 ? 'foto' : 'fotos'}`;

    if (list.length === 0) {
        if (imgEl) imgEl.style.display = 'none';
        if (placeholder) placeholder.style.display = 'flex';
        if (counter) counter.style.display = 'none';
        if (btnPrev) btnPrev.style.display = 'none';
        if (btnNext) btnNext.style.display = 'none';
        if (thumbs) thumbs.style.display = 'none';
        if (btnDel) btnDel.style.display = 'none';
        if (btnAdd && isModalEditMode) btnAdd.style.display = 'inline-flex';
        return;
    }

    if (imgEl) {
        imgEl.style.display = 'block';
        imgEl.src = list[idx];
    }
    if (placeholder) placeholder.style.display = 'none';

    if (counter) {
        counter.style.display = 'block';
        counter.textContent = `${idx + 1} / ${list.length}`;
    }

    if (list.length > 1) {
        if (btnPrev) btnPrev.style.display = 'grid';
        if (btnNext) btnNext.style.display = 'grid';
    } else {
        if (btnPrev) btnPrev.style.display = 'none';
        if (btnNext) btnNext.style.display = 'none';
    }

    if (isModalEditMode) {
        if (btnAdd) btnAdd.style.display = list.length < 3 ? 'inline-flex' : 'none';
        if (btnDel) btnDel.style.display = 'inline-flex';
    }

    // Miniaturas
    if (thumbs) {
        thumbs.style.display = 'flex';
        thumbs.innerHTML = '';
        list.forEach((src, i) => {
            const thumb = document.createElement('div');
            thumb.className = `slider-thumb-item ${i === idx ? 'active' : ''}`;
            thumb.innerHTML = `<img src="${src}" alt="Thumb ${i + 1}" />`;
            thumb.onclick = () => {
                modalPhotoIndex[scope] = i;
                renderPhotoSlider(scope);
            };
            thumbs.appendChild(thumb);
        });
    }
}

function slidePhotoNav(scope, delta) {
    const list = modalPhotos[scope] || [];
    if (list.length <= 1) return;
    modalPhotoIndex[scope] = (modalPhotoIndex[scope] + delta + list.length) % list.length;
    renderPhotoSlider(scope);
}

function deleteActivePhoto(scope) {
    const list = modalPhotos[scope] || [];
    if (list.length === 0) return;
    const curIdx = modalPhotoIndex[scope] || 0;
    list.splice(curIdx, 1);
    modalPhotoIndex[scope] = Math.max(0, curIdx - 1);
    syncRemainingImagesInputs(scope);
    renderPhotoSlider(scope);
}

function handleMultipleImagesSelected(input, scope) {
    if (!input.files || input.files.length === 0) return;
    const list = modalPhotos[scope] || [];
    const remainingSlots = 3 - list.length;

    Array.from(input.files).slice(0, remainingSlots).forEach(file => {
        const url = URL.createObjectURL(file);
        list.push(url);
    });

    modalPhotoIndex[scope] = list.length - 1;
    syncRemainingImagesInputs(scope);
    renderPhotoSlider(scope);
}

function syncRemainingImagesInputs(scope) {
    const container = document.getElementById(`${scope}_remaining_images_container`);
    if (!container) return;
    container.innerHTML = '';
    const list = modalPhotos[scope] || [];
    list.forEach(src => {
        if (src.startsWith('http') || src.startsWith('/uploads') || src.startsWith('uploads')) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'existing_images[]';
            hidden.value = src;
            container.appendChild(hidden);
        }
    });
}

/* ==========================================================================
   MINI MAPA LEAFLET EN MODAL
   ========================================================================== */

function initModalMiniMap(scope, lat, lng) {
    const mapEl = document.getElementById(`${scope}_modal_map`);
    if (!mapEl) return;

    if (!modalMiniMapInstance) {
        modalMiniMapInstance = L.map(mapEl, {
            zoomControl: false,
            attributionControl: false
        }).setView([lat, lng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(modalMiniMapInstance);

        modalMiniMapMarker = L.marker([lat, lng], {
            draggable: isModalEditMode
        }).addTo(modalMiniMapInstance);

        modalMiniMapMarker.on('dragend', function (e) {
            const pos = e.target.getLatLng();
            updateModalGpsCoords(scope, pos.lat, pos.lng);
        });
    } else {
        modalMiniMapInstance.setView([lat, lng], 16);
        if (modalMiniMapMarker) {
            modalMiniMapMarker.setLatLng([lat, lng]);
            if (isModalEditMode) {
                modalMiniMapMarker.dragging?.enable();
            } else {
                modalMiniMapMarker.dragging?.disable();
            }
        }
    }

    setTimeout(() => {
        modalMiniMapInstance.invalidateSize();
    }, 200);
}

function updateModalGpsCoords(scope, lat, lng) {
    const latInput = document.getElementById(`${scope}_latitude`);
    const lngInput = document.getElementById(`${scope}_longitude`);
    const eInput = document.getElementById(`${scope}_utm_easting`);
    const nInput = document.getElementById(`${scope}_utm_northing`);
    const utmDisp = document.getElementById(`${scope}_utm_display`);

    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);

    const utm = latLngToUtm(lat, lng);
    if (eInput) eInput.value = Math.round(utm.easting);
    if (nInput) nInput.value = Math.round(utm.northing);
    if (utmDisp) utmDisp.textContent = `E: ${Math.round(utm.easting)}, N: ${Math.round(utm.northing)}, Z: ${utm.zone}`;
}

function syncUtmToMap(scope) {
    const eVal = parseFloat(document.getElementById(`${scope}_utm_easting`)?.value || '0');
    const nVal = parseFloat(document.getElementById(`${scope}_utm_northing`)?.value || '0');
    const zVal = document.getElementById(`${scope}_utm_zone`)?.value || '20K';

    if (eVal > 0 && nVal > 0) {
        const pos = utmToLatLng(eVal, nVal, zVal);
        if (!isNaN(pos.lat) && !isNaN(pos.lng)) {
            const latInput = document.getElementById(`${scope}_latitude`);
            const lngInput = document.getElementById(`${scope}_longitude`);
            const utmDisp = document.getElementById(`${scope}_utm_display`);

            if (latInput) latInput.value = pos.lat.toFixed(7);
            if (lngInput) lngInput.value = pos.lng.toFixed(7);
            if (utmDisp) utmDisp.textContent = `E: ${Math.round(eVal)}, N: ${Math.round(nVal)}, Z: ${zVal}`;

            if (modalMiniMapInstance && modalMiniMapMarker) {
                modalMiniMapInstance.setView([pos.lat, pos.lng], 16);
                modalMiniMapMarker.setLatLng([pos.lat, pos.lng]);
            }
        }
    }
}

function getCurrentGpsPosition(latFieldId, lngFieldId, scope) {
    if (!navigator.geolocation) {
        alert('Geolocalización no soportada en este navegador.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateModalGpsCoords(scope, lat, lng);
            if (modalMiniMapInstance && modalMiniMapMarker) {
                modalMiniMapInstance.setView([lat, lng], 17);
                modalMiniMapMarker.setLatLng([lat, lng]);
            }
        },
        err => {
            alert('No se pudo obtener la posición GPS: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 8000 }
    );
}

/* ==========================================================================
   MODAL DE TODAS LAS UBICACIONES (SPLIT MAP + SIDEBAR)
   ========================================================================== */

function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');

    setTimeout(() => {
        initAllLocationsMap();
    }, 180);
}

function closeAllLocationsModal() {
    document.getElementById('allLocationsModal')?.classList.remove('open');
}

function initAllLocationsMap() {
    const container = document.getElementById('allLocationsMapLeaflet');
    if (!container) return;

    if (!allLocationsMapInstance) {
        allLocationsMapInstance = L.map('allLocationsMapLeaflet', {
            zoomControl: true,
            attributionControl: false
        }).setView([-16.5034, -68.1324], 14);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(allLocationsMapInstance);
    }

    allLocationsMarkers.forEach(m => allLocationsMapInstance.removeLayer(m));
    allLocationsMarkers = [];

    const measurements = cfg.measurements || [];
    const validPoints = measurements.filter(m => m.latitude !== null && m.longitude !== null);

    if (validPoints.length === 0) {
        allLocationsMapInstance.setView([-16.5034, -68.1324], 14);
        return;
    }

    const group = [];
    measurements.forEach((p, idx) => {
        if (p.latitude === null || p.longitude === null) return;

        const customIcon = L.divIcon({
            className: 'custom-map-pin',
            html: `<div style="background:#0284c7;color:#fff;font-weight:800;font-size:11px;padding:3px 7px;border-radius:6px;border:1.5px solid #fff;box-shadow:0 3px 8px rgba(2,132,199,0.4);white-space:nowrap;">#${p.point_number}</div>`,
            iconSize: [30, 24],
            iconAnchor: [15, 12]
        });

        const m = L.marker([p.latitude, p.longitude], { icon: customIcon }).addTo(allLocationsMapInstance);
        m.bindPopup(`
            <div style="font-family:'Segoe UI',sans-serif;font-size:12.5px;min-width:160px;">
                <div style="font-weight:800;color:#0284c7;font-size:13px;margin-bottom:2px;">Punto #${p.point_number} — ${p.observation}</div>
                <div style="color:#334155;font-weight:600;">${p.area}</div>
                ${p.description ? `<div style="color:#64748b;font-size:11.5px;margin-top:2px;">${p.description}</div>` : ''}
                <div style="color:#0284c7;font-size:11px;margin-top:4px;font-weight:700;">Registrado por: ${p.registered_by}</div>
            </div>
        `);

        allLocationsMarkers[idx] = m;
        group.push([p.latitude, p.longitude]);
    });

    if (group.length > 0) {
        allLocationsMapInstance.fitBounds(group, { padding: [50, 50] });
    }

    allLocationsMapInstance.invalidateSize();
}

function focusPointOnAllLocationsMap(index) {
    const measurements = cfg.measurements || [];
    const p = measurements[index];
    if (!p || p.latitude === null || p.longitude === null || !allLocationsMapInstance) return;

    allLocationsMapInstance.setView([p.latitude, p.longitude], 17, { animate: true });
    if (allLocationsMarkers[index]) {
        allLocationsMarkers[index].openPopup();
    }
}

/* ==========================================================================
   MODAL DE MAPA INDIVIDUAL
   ========================================================================== */

function openSinglePointMap(lat, lng, pointName, areaName) {
    const modal = document.getElementById('mapModal');
    if (!modal) return;
    modal.classList.add('open');

    setTimeout(() => {
        const container = document.getElementById('photographicLeafletMap');
        if (!container) return;

        if (!singleMapInstance) {
            singleMapInstance = L.map('photographicLeafletMap', {
                zoomControl: true,
                attributionControl: false
            }).setView([lat, lng], 17);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19
            }).addTo(singleMapInstance);
        } else {
            singleMapInstance.setView([lat, lng], 17);
        }

        singleMapMarkers.forEach(m => singleMapInstance.removeLayer(m));
        singleMapMarkers = [];

        const marker = L.marker([lat, lng]).addTo(singleMapInstance);
        marker.bindPopup(`<strong>${pointName}</strong><br>${areaName}<br><span style="font-family:monospace;font-size:11px;">${lat.toFixed(5)}, ${lng.toFixed(5)}</span>`).openPopup();
        singleMapMarkers.push(marker);

        const info = document.getElementById('mapPointInfoText');
        const coords = document.getElementById('mapCoordsText');
        if (info) info.textContent = `${pointName} — ${areaName}`;
        if (coords) coords.textContent = `Lat: ${lat.toFixed(5)}, Lng: ${lng.toFixed(5)}`;

        singleMapInstance.invalidateSize();
    }, 180);
}

function closeMapModal() {
    document.getElementById('mapModal')?.classList.remove('open');
}

/* ==========================================================================
   VISOR DE FOTOGRAFÍAS A PANTALLA COMPLETA (LIGHTBOX)
   ========================================================================== */

function openPhotoViewerModal(images, startIndex, pointTitle) {
    if (!images || images.length === 0) return;
    currentViewerImages = images;
    currentViewerIndex = startIndex || 0;

    const modal = document.getElementById('photoViewerModal');
    const titleEl = document.getElementById('photoViewerTitle');
    if (titleEl) titleEl.textContent = pointTitle || 'Fotografía de Inspección';

    renderCurrentPhotoInViewer();
    if (modal) modal.classList.add('open');
}

function renderCurrentPhotoInViewer() {
    const imgEl = document.getElementById('photoViewerMainImage');
    const counterEl = document.getElementById('photoViewerCounter');
    const stripEl = document.getElementById('photoViewerThumbnailsStrip');

    if (imgEl) {
        imgEl.src = currentViewerImages[currentViewerIndex];
    }
    if (counterEl) {
        counterEl.textContent = `Foto ${currentViewerIndex + 1} de ${currentViewerImages.length}`;
    }

    if (stripEl) {
        stripEl.innerHTML = '';
        currentViewerImages.forEach((src, idx) => {
            const thumb = document.createElement('img');
            thumb.src = src;
            thumb.style.width = '48px';
            thumb.style.height = '48px';
            thumb.style.borderRadius = '8px';
            thumb.style.objectFit = 'cover';
            thumb.style.cursor = 'pointer';
            thumb.style.border = idx === currentViewerIndex ? '2px solid #0ea5e9' : '1px solid rgba(255,255,255,0.4)';
            thumb.onclick = () => {
                currentViewerIndex = idx;
                renderCurrentPhotoInViewer();
            };
            stripEl.appendChild(thumb);
        });
    }
}

function navigatePhotoViewer(delta) {
    if (currentViewerImages.length <= 1) return;
    currentViewerIndex = (currentViewerIndex + delta + currentViewerImages.length) % currentViewerImages.length;
    renderCurrentPhotoInViewer();
}

function closePhotoViewerModal() {
    document.getElementById('photoViewerModal')?.classList.remove('open');
}

/* ==========================================================================
   EXPORTACIÓN & REPORTE FOTOGRÁFICO
   ========================================================================== */

function openExportModal() {
    document.getElementById('exportOptionsModal')?.classList.add('open');
}

function closeExportModal() {
    document.getElementById('exportOptionsModal')?.classList.remove('open');
}

function openPhotoReportModal() {
    closeExportModal();
    document.getElementById('photoReportModal')?.classList.add('open');
}

function closePhotoReportModal() {
    document.getElementById('photoReportModal')?.classList.remove('open');
}

function generatePhotoReport() {
    const title = document.getElementById('photo_report_title')?.value || 'Reporte Fotográfico';
    const cols = document.getElementById('photo_report_columns')?.value || '2';
    const orient = document.getElementById('photo_report_orientation')?.value || 'portrait';
    const incGps = document.getElementById('photo_report_include_gps')?.checked ?? true;
    const incDesc = document.getElementById('photo_report_include_desc')?.checked ?? true;

    const measurements = cfg.measurements || [];
    
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        alert('Por favor habilita las ventanas emergentes en tu navegador.');
        return;
    }

    let gridHtml = '';
    measurements.forEach(m => {
        const images = Array.isArray(m.images) ? m.images : [];
        if (images.length === 0) return;

        images.forEach((img) => {
            gridHtml += `
                <div class="photo-card-item" style="border:1px solid #cbd5e1;border-radius:8px;overflow:hidden;page-break-inside:avoid;background:#fff;">
                    <img src="${img}" alt="Foto Punto ${m.point_number}" style="width:100%;height:220px;object-fit:cover;display:block;" />
                    <div style="padding:10px;font-size:12px;">
                        <div style="font-weight:800;color:#0284c7;margin-bottom:4px;">Punto #${m.point_number} — ${m.area}</div>
                        <div style="font-weight:600;color:#1e293b;margin-bottom:3px;">${m.observation || ''}</div>
                        ${incDesc && m.description ? `<div style="font-size:11px;color:#64748b;margin-bottom:3px;">${m.description}</div>` : ''}
                        ${incGps && m.latitude ? `<div style="font-family:monospace;font-size:10.5px;color:#059669;font-weight:700;">GPS: ${m.latitude.toFixed(5)}, ${m.longitude.toFixed(5)}</div>` : ''}
                    </div>
                </div>
            `;
        });
    });

    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>${title}</title>
            <style>
                @page { size: A4 ${orient}; margin: 15mm; }
                body { font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 20px; color: #0f172a; }
                .report-header { border-bottom: 2px solid #0284c7; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-end; }
                .logo-text { font-size: 22px; font-weight: 900; color: #0284c7; }
                .title-text { font-size: 16px; font-weight: 800; text-align: right; }
                .photos-grid { display: grid; grid-template-columns: repeat(${cols}, 1fr); gap: 16px; }
            </style>
        </head>
        <body>
            <div class="report-header">
                <div>
                    <div class="logo-text">PACHABOL • METRIC</div>
                    <div style="font-size: 11px; color: #64748b; font-weight: 600;">CATÁLOGO FOTOGRÁFICO DE EVIDENCIAS</div>
                </div>
                <div class="title-text">${title}</div>
            </div>
            <div class="photos-grid">
                ${gridHtml}
            </div>
            <script>
                window.onload = function() { window.print(); };
            </script>
        </body>
        </html>
    `);
    printWindow.document.close();
}

/* ==========================================================================
   ELIMINAR PUNTO DE INSPECCIÓN
   ========================================================================== */

function confirmDeleteMeasurement(id, pointNumber) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `¿Eliminar Punto #${pointNumber}?`,
            text: 'Esta acción no se puede deshacer. Se eliminarán los registros y fotografías asociadas.',
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
        }).then(result => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteMeasurementForm');
                if (form) {
                    form.action = `/modulos/${MODULE_ID}/inspeccion-fotografica/mediciones/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Seguro que deseas eliminar el Punto #${pointNumber}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = `/modulos/${MODULE_ID}/inspeccion-fotografica/mediciones/${id}`;
                form.submit();
            }
        }
    }
}

/* ==========================================================================
   UTM CONVERSION HELPERS
   ========================================================================== */

function latLngToUtm(lat, lng) {
    const zone = Math.floor((lng + 180) / 6) + 1;
    const zoneLetter = lat < 0 ? 'K' : 'N';
    const zoneStr = `${zone}${zoneLetter}`;

    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - f * f);
    const ePrimeSq = (e * e) / (1 - e * e);

    const latRad = (lat * Math.PI) / 180;
    const lngRad = (lng * Math.PI) / 180;
    const lngOrigin = ((zone - 1) * 6 - 180 + 3) * (Math.PI / 180);

    const N = a / Math.sqrt(1 - e * e * Math.sin(latRad) * Math.sin(latRad));
    const T = Math.tan(latRad) * Math.tan(latRad);
    const C = ePrimeSq * Math.cos(latRad) * Math.cos(latRad);
    const A = Math.cos(latRad) * (lngRad - lngOrigin);

    const M = a * ((1 - e * e / 4 - 3 * Math.pow(e, 4) / 64 - 5 * Math.pow(e, 6) / 256) * latRad
        - (3 * e * e / 8 + 3 * Math.pow(e, 4) / 32 + 45 * Math.pow(e, 6) / 1024) * Math.sin(2 * latRad)
        + (15 * Math.pow(e, 4) / 256 + 45 * Math.pow(e, 6) / 1024) * Math.sin(4 * latRad)
        - (35 * Math.pow(e, 6) / 3072) * Math.sin(6 * latRad));

    const easting = k0 * N * (A + (1 - T + C) * Math.pow(A, 3) / 6 + (5 - 18 * T + T * T + 72 * C - 58 * ePrimeSq) * Math.pow(A, 5) / 120) + 500000.0;
    let northing = k0 * (M + N * Math.tan(latRad) * (A * A / 2 + (5 - T + 9 * C + 4 * C * C) * Math.pow(A, 4) / 24 + (61 - 58 * T + T * T + 600 * C - 330 * ePrimeSq) * Math.pow(A, 6) / 720));
    if (lat < 0) {
        northing += 10000000.0;
    }

    return { easting, northing, zone: zoneStr };
}

function utmToLatLng(easting, northing, utmZone) {
    const zoneNumber = parseInt(utmZone.replace(/[^0-9]/g, '')) || 20;
    const isSouthern = !utmZone.toUpperCase().endsWith('N');

    const a = 6378137.0;
    const f = 1 / 298.257223563;
    const k0 = 0.9996;
    const e = Math.sqrt(2 * f - f * f);
    const ePrimeSq = (e * e) / (1 - e * e);

    const x = easting - 500000.0;
    let y = northing;
    if (isSouthern && y > 5000000) {
        y -= 10000000.0;
    }

    const M = y / k0;
    const mu = M / (a * (1 - e * e / 4 - 3 * Math.pow(e, 4) / 64 - 5 * Math.pow(e, 6) / 256));

    const e1 = (1 - Math.sqrt(1 - e * e)) / (1 + Math.sqrt(1 - e * e));
    const phi1 = mu + (3 * e1 / 2 - 27 * Math.pow(e1, 3) / 32) * Math.sin(2 * mu)
        + (21 * Math.pow(e1, 2) / 16 - 55 * Math.pow(e1, 4) / 32) * Math.sin(4 * mu)
        + (151 * Math.pow(e1, 3) / 96) * Math.sin(6 * mu);

    const N1 = a / Math.sqrt(1 - e * e * Math.sin(phi1) * Math.sin(phi1));
    const T1 = Math.tan(phi1) * Math.tan(phi1);
    const C1 = ePrimeSq * Math.cos(phi1) * Math.cos(phi1);
    const R1 = a * (1 - e * e) / Math.pow(1 - e * e * Math.sin(phi1) * Math.sin(phi1), 1.5);
    const D = x / (N1 * k0);

    const lat = phi1 - (N1 * Math.tan(phi1) / R1) * (D * D / 2 - (5 + 3 * T1 + 10 * C1 - 4 * C1 * C1 - 9 * ePrimeSq) * Math.pow(D, 4) / 24
        + (61 + 90 * T1 + 298 * C1 + 45 * T1 * T1 - 252 * ePrimeSq - 3 * C1 * C1) * Math.pow(D, 6) / 720);

    const lon = (D - (1 + 2 * T1 + C1) * Math.pow(D, 3) / 6
        + (5 - 2 * C1 + 28 * T1 - 3 * C1 * C1 + 8 * ePrimeSq + 24 * T1 * T1) * Math.pow(D, 5) / 120) / Math.cos(phi1);

    const lonOrigin = ((zoneNumber - 1) * 6 - 180 + 3) * (Math.PI / 180);

    return {
        lat: (lat * 180) / Math.PI,
        lng: ((lon + lonOrigin) * 180) / Math.PI
    };
}

// Exportar globalmente para Blade / onclick
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.toggleModalEditMode = toggleModalEditMode;
window.slidePhotoNav = slidePhotoNav;
window.deleteActivePhoto = deleteActivePhoto;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.syncUtmToMap = syncUtmToMap;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;
window.openSinglePointMap = openSinglePointMap;
window.closeMapModal = closeMapModal;
window.openPhotoViewerModal = openPhotoViewerModal;
window.closePhotoViewerModal = closePhotoViewerModal;
window.navigatePhotoViewer = navigatePhotoViewer;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openPhotoReportModal = openPhotoReportModal;
window.closePhotoReportModal = closePhotoReportModal;
window.generatePhotoReport = generatePhotoReport;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.searchPhotographicLive = searchPhotographicLive;
