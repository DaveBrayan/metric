/**
 * ==========================================================================
 * METRIC V2 — VIBRACIÓN OCUPACIONAL (ISO 2631-1 & ISO 5349-1)
 * Lógica modularizada: 3 Columnas, Modo Solo Lectura / Edición,
 * Carrusel Continuo, Georreferenciación UTM / Leaflet, Paginación Reactiva,
 * Cálculos ISO en Vivo y Exportación Excel.
 * ==========================================================================
 */

// Estado global de paginación
let vibracionCurrentPage = 1;
const vibracionRowsPerPage = 10;
let vibracionFilteredRows = [];

// Instancias de Mapas Leaflet
let modalMiniMaps = { create: null, edit: null };
let modalMiniMarkers = { create: null, edit: null };
let singleMapInstance = null;
let allMapInstance = null;

// Estado del Carrusel Fotográfico en modales
let sliderState = {
    create: { photos: [], currentIndex: 0 },
    edit: { photos: [], currentIndex: 0, removedUrls: [] }
};

// Estado de modo de edición en Modal de Detalle
let isEditModeActive = false;

document.addEventListener('DOMContentLoaded', () => {
    initVibracionTable();
});

/* ==========================================================================
   1. TABLA MAESTRA, BUSCADOR EN VIVO Y PAGINACIÓN REACTIVA (10 POR PÁGINA)
   ========================================================================== */

function initVibracionTable() {
    const tableBody = document.getElementById('vibracionTableBody');
    if (!tableBody) return;

    const rows = Array.from(tableBody.querySelectorAll('.illumination-data-row'));
    vibracionFilteredRows = rows;
    vibracionCurrentPage = 1;
    renderVibracionPagination();
}

window.searchVibracionLive = function() {
    const input = document.getElementById('vibracionSearchInput');
    if (!input) return;

    const term = input.value.trim().toLowerCase();
    const tableBody = document.getElementById('vibracionTableBody');
    if (!tableBody) return;

    const allRows = Array.from(tableBody.querySelectorAll('.illumination-data-row'));
    const noResultsRow = document.getElementById('noResultsSearchRow');

    if (!term) {
        vibracionFilteredRows = allRows;
    } else {
        vibracionFilteredRows = allRows.filter(row => {
            const searchText = (row.getAttribute('data-search') || '').toLowerCase();
            return searchText.includes(term);
        });
    }

    if (vibracionFilteredRows.length === 0 && allRows.length > 0) {
        if (noResultsRow) noResultsRow.style.display = '';
    } else {
        if (noResultsRow) noResultsRow.style.display = 'none';
    }

    vibracionCurrentPage = 1;
    renderVibracionPagination();
};

function renderVibracionPagination() {
    const total = vibracionFilteredRows.length;
    const totalPages = Math.max(1, Math.ceil(total / vibracionRowsPerPage));

    if (vibracionCurrentPage > totalPages) {
        vibracionCurrentPage = totalPages;
    }

    const startIdx = (vibracionCurrentPage - 1) * vibracionRowsPerPage;
    const endIdx = startIdx + vibracionRowsPerPage;

    // Ocultar todas las filas
    const tableBody = document.getElementById('vibracionTableBody');
    if (tableBody) {
        const allRows = tableBody.querySelectorAll('.illumination-data-row');
        allRows.forEach(r => r.style.display = 'none');
    }

    // Mostrar solo las filas de la página activa
    vibracionFilteredRows.slice(startIdx, endIdx).forEach(row => {
        row.style.display = '';
    });

    // Actualizar leyenda informativa
    const dispStart = total === 0 ? 0 : startIdx + 1;
    const dispEnd = Math.min(endIdx, total);

    const elStart = document.getElementById('vibPageStart');
    const elEnd = document.getElementById('vibPageEnd');
    const elTotal = document.getElementById('vibPageTotal');

    if (elStart) elStart.textContent = dispStart;
    if (elEnd) elEnd.textContent = dispEnd;
    if (elTotal) elTotal.textContent = total;

    // Generar controles de paginación
    const controls = document.getElementById('vibracionPaginationControls');
    if (!controls) return;

    controls.innerHTML = '';
    if (totalPages <= 1) return;

    // Botón Anterior
    const prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.className = 'pagination-btn';
    prevBtn.innerHTML = '&laquo;';
    prevBtn.disabled = vibracionCurrentPage === 1;
    prevBtn.onclick = () => {
        if (vibracionCurrentPage > 1) {
            vibracionCurrentPage--;
            renderVibracionPagination();
        }
    };
    controls.appendChild(prevBtn);

    // Botones de Páginas
    for (let p = 1; p <= totalPages; p++) {
        if (totalPages > 7) {
            if (p !== 1 && p !== totalPages && Math.abs(p - vibracionCurrentPage) > 2) {
                if (p === 2 || p === totalPages - 1) {
                    const dots = document.createElement('span');
                    dots.textContent = '...';
                    dots.style.padding = '0 4px';
                    dots.style.color = '#94a3b8';
                    controls.appendChild(dots);
                }
                continue;
            }
        }

        const pageBtn = document.createElement('button');
        pageBtn.type = 'button';
        pageBtn.className = 'pagination-btn' + (p === vibracionCurrentPage ? ' active' : '');
        pageBtn.textContent = p;
        pageBtn.onclick = () => {
            vibracionCurrentPage = p;
            renderVibracionPagination();
        };
        controls.appendChild(pageBtn);
    }

    // Botón Siguiente
    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'pagination-btn';
    nextBtn.innerHTML = '&raquo;';
    nextBtn.disabled = vibracionCurrentPage === totalPages;
    nextBtn.onclick = () => {
        if (vibracionCurrentPage < totalPages) {
            vibracionCurrentPage++;
            renderVibracionPagination();
        }
    };
    controls.appendChild(nextBtn);
}

/* ==========================================================================
   2. MODALES (3 COLUMNAS): CREAR Y VER / EDITAR CON TOGGLE
   ========================================================================== */

window.openCreateMeasurementModal = function() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;

    modal.style.display = 'flex';
    sliderState.create = { photos: [], currentIndex: 0 };
    renderPhotoSlider('create');

    setTimeout(() => {
        initModalMiniMap('create', -16.5, -68.15);
        handleVibTipoChange('create');
        recalculateVibracionModal('create');
    }, 150);
};

window.closeCreateMeasurementModal = function() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) modal.style.display = 'none';
};

/**
 * Abre el Modal de Detalle (3 Columnas) iniciando siempre en modo Solo Lectura
 */
window.openViewMeasurementModal = function(item) {
    const modal = document.getElementById('editMeasurementModal');
    const form = document.getElementById('editMeasurementForm');
    if (!modal || !form) return;

    // Configurar acción del formulario
    form.action = `/modulos/${window.MODULE_ID}/vibracion/mediciones/${item.id}`;

    // Llenar campos
    const ptCode = item.codigo || item.point_number || `VIB-${item.id}`;
    const codeDisp = document.getElementById('edit_pt_num_disp');
    if (codeDisp) codeDisp.textContent = ptCode;

    setVal('edit_point_number', ptCode);
    setVal('edit_codigo', ptCode);
    setVal('edit_measurement_date', item.raw_date || item.measurement_date || '');
    setVal('edit_measurement_time', item.time || item.measurement_time || '08:00');
    setVal('edit_area', item.area || '');
    setVal('edit_puesto_trabajo', item.puesto_trabajo || item.workstation || '');
    setVal('edit_trabajador_evaluado', item.trabajador_evaluado || '');
    setVal('edit_maquina_equipo', item.maquina_equipo || '');
    setVal('edit_duracion_jornada_h', item.duracion_jornada_h || 8.0);
    setVal('edit_duracion_prueba_min', item.duracion_prueba_min || 15);
    setVal('edit_observations', item.observations || '');

    if (item.staff_id && document.getElementById('edit_staff_id')) {
        document.getElementById('edit_staff_id').value = item.staff_id;
    }

    const staffBadge = document.getElementById('edit_modal_registered_by');
    if (staffBadge) {
        staffBadge.textContent = item.registered_by || window.REGISTERED_BY_HEADER || 'Técnico';
    }

    // Tipo
    const isCE = (item.tipo === 'cuerpo_entero' || !item.tipo);
    setVal('edit_tipo', isCE ? 'cuerpo_entero' : 'mano_brazo');

    // Tiempo expos
    const texp = parseFloat(item.tiempo_expos_h || 8.0).toFixed(2);
    const texpSel = document.getElementById('edit_tiempo_expos_h');
    if (texpSel) {
        let matched = Array.from(texpSel.options).find(o => Math.abs(parseFloat(o.value) - parseFloat(texp)) < 0.05);
        if (matched) texpSel.value = matched.value;
        else texpSel.value = "8.0";
    }

    // Config sensor
    if (isCE) {
        setVal('edit_ub_acelerometro', item.ub_acelerometro || 'base_asiento');
        setVal('edit_aeqx', parseFloat(item.aeqx_ce ?? item.aeqx ?? 0).toFixed(4));
        setVal('edit_aeqy', parseFloat(item.aeqy_ce ?? item.aeqy ?? 0).toFixed(4));
        setVal('edit_aeqz', parseFloat(item.aeqz_ce ?? item.aeqz ?? 0).toFixed(4));
    } else {
        setVal('edit_mano_afectada', item.mano_afectada || 'derecha');
        setVal('edit_aeqx', parseFloat(item.aeqx_mb ?? item.aeqx ?? 0).toFixed(4));
        setVal('edit_aeqy', parseFloat(item.aeqy_mb ?? item.aeqy ?? 0).toFixed(4));
        setVal('edit_aeqz', parseFloat(item.aeqz_mb ?? item.aeqz ?? 0).toFixed(4));
    }

    // Georreferenciación
    setVal('edit_utm_zone', item.utm_zone || '19K');
    setVal('edit_utm_easting', item.utm_easting || '');
    setVal('edit_utm_northing', item.utm_northing || '');
    setVal('edit_latitude', item.latitude || '');
    setVal('edit_longitude', item.longitude || '');

    const utmDisp = document.getElementById('edit_utm_display');
    if (utmDisp) {
        utmDisp.textContent = `E: ${item.utm_easting || '—'}, N: ${item.utm_northing || '—'}, Z: ${item.utm_zone || '19K'}`;
    }

    // Archivo fotográfico (Slide continuo)
    let images = [];
    if (item.images_urls && Array.isArray(item.images_urls)) {
        images = item.images_urls;
    } else if (item.image_urls && Array.isArray(item.image_urls)) {
        images = item.image_urls;
    } else if (item.image_path) {
        images = [item.image_path];
    }
    sliderState.edit = { photos: [...images], currentIndex: 0, removedUrls: [] };
    renderPhotoSlider('edit');
    updateRemainingImagesInput('edit');

    // Modo solo lectura por defecto
    applyEditModeState(false);

    modal.style.display = 'flex';

    setTimeout(() => {
        let lat = parseFloat(item.latitude);
        let lng = parseFloat(item.longitude);
        if (isNaN(lat) || isNaN(lng)) {
            if (item.utm_easting && item.utm_northing) {
                const conv = utmToLatLng(item.utm_easting, item.utm_northing, item.utm_zone || '19K');
                lat = conv.lat;
                lng = conv.lng;
            } else {
                lat = -16.5;
                lng = -68.15;
            }
        }
        initModalMiniMap('edit', lat, lng);
        handleVibTipoChange('edit');
        recalculateVibracionModal('edit');
    }, 150);
};

window.closeEditMeasurementModal = function() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) modal.style.display = 'none';
};

window.toggleModalEditMode = function() {
    applyEditModeState(!isEditModeActive);
};

function applyEditModeState(isEdit) {
    isEditModeActive = isEdit;
    const form = document.getElementById('editMeasurementForm');
    const badge = document.getElementById('modalModeStatusBadge');
    const btnText = document.getElementById('btnToggleEditModeText');
    const btnIcon = document.getElementById('btnToggleEditModeIcon');
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    const btnGps = document.getElementById('edit_btn_gps');
    const btnAddPhotos = document.getElementById('edit_btn_add_photos');
    const btnDelPhoto = document.getElementById('edit_btn_delete_photo');

    if (!form) return;

    if (isEdit) {
        form.classList.remove('modal-view-mode');
        if (badge) {
            badge.textContent = 'Modo Edición';
            badge.style.background = '#e0f2fe';
            badge.style.color = '#0284c7';
            badge.style.borderColor = '#bae6fd';
        }
        if (btnText) btnText.textContent = 'Cancelar';
        if (btnIcon) {
            btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
        }
        if (submitBtn) submitBtn.style.display = 'inline-flex';
        if (btnGps) btnGps.style.display = 'inline-flex';
        if (btnAddPhotos) btnAddPhotos.style.display = 'flex';
        if (btnDelPhoto && sliderState.edit.photos.length > 0) btnDelPhoto.style.display = 'inline-flex';

        form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
            el.disabled = false;
        });
    } else {
        form.classList.add('modal-view-mode');
        if (badge) {
            badge.textContent = 'Solo Lectura';
            badge.style.background = '#f1f5f9';
            badge.style.color = '#475569';
            badge.style.borderColor = '#cbd5e1';
        }
        if (btnText) btnText.textContent = 'Editar';
        if (btnIcon) {
            btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>`;
        }
        if (submitBtn) submitBtn.style.display = 'none';
        if (btnGps) btnGps.style.display = 'none';
        if (btnAddPhotos) btnAddPhotos.style.display = 'none';
        if (btnDelPhoto) btnDelPhoto.style.display = 'none';

        form.querySelectorAll('input:not([type="hidden"]), select, textarea').forEach(el => {
            el.disabled = true;
        });
    }
}

function setVal(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = (val !== null && val !== undefined) ? val : '';
}

/* ==========================================================================
   3. AUTO-GUARDADO DE ENCABEZADO TÉCNICO INLINE
   ========================================================================== */

window.autoSaveHeaderField = function() {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> <span>Guardando...</span>`;
        badge.style.color = '#0284c7';
        badge.style.background = '#f0f9ff';
        badge.style.borderColor = '#bae6fd';
    }

    const payload = {
        _token: window.CSRF_TOKEN,
        installation_name: document.getElementById('inline_installation_name')?.value || '',
        start_date: document.getElementById('inline_start_date')?.value || null,
        end_date: document.getElementById('inline_end_date')?.value || null,
        monitoring_type: document.getElementById('inline_monitoring_type')?.value || ''
    };

    fetch(window.METRIC_VIBRACION_CONFIG.updateHeaderUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (badge) {
            setTimeout(() => {
                badge.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> <span>Guardado</span>`;
                badge.style.color = '#059669';
                badge.style.background = '#ecfdf5';
                badge.style.borderColor = '#a7f3d0';
            }, 300);
        }
    })
    .catch(err => {
        console.error('Error auto-saving header:', err);
        if (badge) {
            badge.innerHTML = `<span>Error</span>`;
            badge.style.color = '#dc2626';
            badge.style.background = '#fef2f2';
            badge.style.borderColor = '#fecaca';
        }
    });
};

/* ==========================================================================
   4. CARRUSEL / SLIDER FOTOGRÁFICO CONTINUO
   ========================================================================== */

function renderPhotoSlider(mode) {
    const state = sliderState[mode];
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

    if (state.currentIndex >= count) state.currentIndex = count - 1;
    if (state.currentIndex < 0) state.currentIndex = 0;

    if (placeholder) placeholder.style.display = 'none';
    if (mainImg) {
        mainImg.style.display = 'block';
        mainImg.src = state.photos[state.currentIndex];
    }

    if (counter) {
        counter.style.display = 'block';
        counter.textContent = `${state.currentIndex + 1} / ${count}`;
    }

    const showNav = count > 1;
    if (btnPrev) btnPrev.style.display = showNav ? 'grid' : 'none';
    if (btnNext) btnNext.style.display = showNav ? 'grid' : 'none';

    if (btnDel && mode === 'edit' && isEditModeActive) {
        btnDel.style.display = 'inline-flex';
    }

    // Miniaturas
    if (thumbsStrip) {
        if (count > 1) {
            thumbsStrip.style.display = 'flex';
            thumbsStrip.innerHTML = '';
            state.photos.forEach((p, idx) => {
                const thumb = document.createElement('div');
                thumb.className = 'slider-thumb-item' + (idx === state.currentIndex ? ' active' : '');
                thumb.innerHTML = `<img src="${p}" alt="Thumb ${idx + 1}">`;
                thumb.onclick = () => selectPhotoThumbnail(mode, idx);
                thumbsStrip.appendChild(thumb);
            });
        } else {
            thumbsStrip.style.display = 'none';
            thumbsStrip.innerHTML = '';
        }
    }
}

window.slidePhotoNav = function(mode, delta) {
    const state = sliderState[mode];
    if (state.photos.length <= 1) return;
    state.currentIndex = (state.currentIndex + delta + state.photos.length) % state.photos.length;
    renderPhotoSlider(mode);
};

window.selectPhotoThumbnail = function(mode, idx) {
    sliderState[mode].currentIndex = idx;
    renderPhotoSlider(mode);
};

window.handleMultipleImagesSelected = function(input, mode) {
    if (!input.files || input.files.length === 0) return;
    const files = Array.from(input.files);

    files.forEach(file => {
        const reader = new FileReader();
        reader.onload = (e) => {
            sliderState[mode].photos.push(e.target.result);
            sliderState[mode].currentIndex = sliderState[mode].photos.length - 1;
            renderPhotoSlider(mode);
        };
        reader.readAsDataURL(file);
    });
};

window.deleteActivePhoto = function(mode) {
    const state = sliderState[mode];
    if (state.photos.length === 0) return;

    const removedUrl = state.photos[state.currentIndex];
    if (mode === 'edit' && removedUrl && typeof removedUrl === 'string' && !removedUrl.startsWith('data:')) {
        state.removedUrls.push(removedUrl);
        updateRemainingImagesInput('edit');
    }

    state.photos.splice(state.currentIndex, 1);
    if (state.currentIndex >= state.photos.length) {
        state.currentIndex = Math.max(0, state.photos.length - 1);
    }
    renderPhotoSlider(mode);
};

function updateRemainingImagesInput(mode) {
    const remInput = document.getElementById(`${mode}_remaining_images`);
    if (remInput) {
        const activeServerUrls = sliderState[mode].photos.filter(p => typeof p === 'string' && !p.startsWith('data:'));
        remInput.value = JSON.stringify(activeServerUrls);
    }
}

/* ==========================================================================
   5. MINI MAPAS LEAFLET Y COORDENADAS UTM
   ========================================================================== */

function initModalMiniMap(mode, lat, lng) {
    const containerId = `${mode}_modal_map`;
    const container = document.getElementById(containerId);
    if (!container) return;

    if (!modalMiniMaps[mode]) {
        const map = L.map(containerId, {
            center: [lat, lng],
            zoom: 15,
            zoomControl: false,
            attributionControl: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(map);

        const marker = L.marker([lat, lng], { draggable: true }).addTo(map);

        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            updateCoordsFromLatLng(mode, pos.lat, pos.lng);
        });

        map.on('click', function(e) {
            if (mode === 'edit' && !isEditModeActive) return;
            marker.setLatLng(e.latlng);
            updateCoordsFromLatLng(mode, e.latlng.lat, e.latlng.lng);
        });

        modalMiniMaps[mode] = map;
        modalMiniMarkers[mode] = marker;
    } else {
        modalMiniMaps[mode].invalidateSize();
        modalMiniMaps[mode].setView([lat, lng], 15);
        modalMiniMarkers[mode].setLatLng([lat, lng]);
    }
}

function updateCoordsFromLatLng(mode, lat, lng) {
    setVal(`${mode}_latitude`, lat.toFixed(7));
    setVal(`${mode}_longitude`, lng.toFixed(7));

    const zoneStr = document.getElementById(`${mode}_utm_zone`)?.value || '19K';
    const utm = wgs84ToUtm(lat, lng, zoneStr);

    setVal(`${mode}_utm_easting`, utm.easting.toFixed(3));
    setVal(`${mode}_utm_northing`, utm.northing.toFixed(3));

    const utmDisp = document.getElementById(`${mode}_utm_display`);
    if (utmDisp) {
        utmDisp.textContent = `E: ${utm.easting.toFixed(3)}, N: ${utm.northing.toFixed(3)}, Z: ${zoneStr}`;
    }
}

window.syncUtmToMap = function(mode) {
    const easting = parseFloat(document.getElementById(`${mode}_utm_easting`)?.value);
    const northing = parseFloat(document.getElementById(`${mode}_utm_northing`)?.value);
    const zoneStr = document.getElementById(`${mode}_utm_zone`)?.value || '19K';

    if (isNaN(easting) || isNaN(northing)) return;

    const conv = utmToLatLng(easting, northing, zoneStr);
    setVal(`${mode}_latitude`, conv.lat.toFixed(7));
    setVal(`${mode}_longitude`, conv.lng.toFixed(7));

    const utmDisp = document.getElementById(`${mode}_utm_display`);
    if (utmDisp) {
        utmDisp.textContent = `E: ${easting.toFixed(3)}, N: ${northing.toFixed(3)}, Z: ${zoneStr}`;
    }

    if (modalMiniMaps[mode] && modalMiniMarkers[mode]) {
        modalMiniMaps[mode].setView([conv.lat, conv.lng], 15);
        modalMiniMarkers[mode].setLatLng([conv.lat, conv.lng]);
    }
};

window.getCurrentGpsPosition = function(latInputId, lngInputId, mode) {
    if (!navigator.geolocation) {
        alert('Geolocalización no soportada por el navegador.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateCoordsFromLatLng(mode, lat, lng);
            if (modalMiniMaps[mode] && modalMiniMarkers[mode]) {
                modalMiniMaps[mode].setView([lat, lng], 16);
                modalMiniMarkers[mode].setLatLng([lat, lng]);
            }
        },
        (err) => {
            alert('No se pudo obtener la posición GPS: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

/* ==========================================================================
   6. CÁLCULOS NORMATIVOS ISO 2631-1 & ISO 5349-1 EN VIVO
   ========================================================================== */

window.handleVibTipoChange = function(mode) {
    const tipo = document.getElementById(`${mode}_tipo`)?.value || 'cuerpo_entero';
    const isCE = tipo === 'cuerpo_entero';

    const cfgCE = document.getElementById(`${mode}_sensor_config_ce`);
    const cfgMB = document.getElementById(`${mode}_sensor_config_mb`);

    if (cfgCE) cfgCE.style.display = isCE ? 'block' : 'none';
    if (cfgMB) cfgMB.style.display = isCE ? 'none' : 'block';

    recalculateVibracionModal(mode);
};

window.recalculateVibracionModal = function(mode) {
    const tipo = document.getElementById(`${mode}_tipo`)?.value || 'cuerpo_entero';
    const isCE = tipo === 'cuerpo_entero';

    const ax = parseFloat(document.getElementById(`${mode}_aeqx`)?.value) || 0;
    const ay = parseFloat(document.getElementById(`${mode}_aeqy`)?.value) || 0;
    const az = parseFloat(document.getElementById(`${mode}_aeqz`)?.value) || 0;
    const texp = parseFloat(document.getElementById(`${mode}_tiempo_expos_h`)?.value) || 8.0;

    let a8 = 0;
    let atot = 0;
    let vle = isCE ? 1.15 : 5.00;
    let action = isCE ? 0.50 : 2.50;

    if (isCE) {
        // Cuerpo Entero (ISO 2631-1): kx=1.4, ky=1.4, kz=1.0
        const kx_ax = 1.4 * ax;
        const ky_ay = 1.4 * ay;
        const kz_az = 1.0 * az;

        atot = Math.sqrt(kx_ax * kx_ax + ky_ay * ky_ay + kz_az * kz_az);
        const maxAxis = Math.max(kx_ax, ky_ay, kz_az);
        a8 = maxAxis * Math.sqrt(texp / 8.0);
    } else {
        // Mano - Brazo (ISO 5349-1): ahv = sqrt(ax^2 + ay^2 + az^2)
        atot = Math.sqrt(ax * ax + ay * ay + az * az);
        a8 = atot * Math.sqrt(texp / 8.0);
    }

    // Actualizar elementos en vivo
    const elA8 = document.getElementById(`${mode}_calc_a8`);
    const elAtot = document.getElementById(`${mode}_calc_atotal`);
    const elVle = document.getElementById(`${mode}_calc_vle`);
    const elBadge = document.getElementById(`${mode}_calc_badge`);

    if (elA8) elA8.textContent = `${a8.toFixed(4)} m/s²`;
    if (elAtot) elAtot.textContent = atot.toFixed(4);
    if (elVle) elVle.textContent = `${vle.toFixed(2)} m/s²`;

    if (elBadge) {
        if (a8 <= action) {
            elBadge.className = 'lux-measured-badge compliant';
            elBadge.textContent = 'CUMPLE';
        } else if (a8 <= vle) {
            elBadge.className = 'lux-measured-badge warning';
            elBadge.textContent = 'NIVEL DE ACCIÓN';
        } else {
            elBadge.className = 'lux-measured-badge non-compliant';
            elBadge.textContent = 'SUPERA LÍMITE';
        }
    }
};

/* ==========================================================================
   7. MODALES COMPLEMENTARIOS: MAPAS, FOTOGRAFÍAS, TABLAS & EXPORTACIÓN
   ========================================================================== */

window.openAllLocationsModal = function() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.style.display = 'flex';

    setTimeout(() => {
        const container = document.getElementById('allLocationsMap');
        if (!container) return;

        if (!allMapInstance) {
            allMapInstance = L.map('allLocationsMap').setView([-16.5, -68.15], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(allMapInstance);
        } else {
            allMapInstance.invalidateSize();
        }

        const data = window.METRIC_VIBRACION_CONFIG.measurements || [];
        const bounds = [];

        data.forEach(item => {
            if (item.latitude && item.longitude) {
                const lat = parseFloat(item.latitude);
                const lng = parseFloat(item.longitude);
                if (!isNaN(lat) && !isNaN(lng)) {
                    bounds.push([lat, lng]);
                    const marker = L.marker([lat, lng]).addTo(allMapInstance);
                    marker.bindPopup(`
                        <div style="font-size:12px; font-family:'Inter',sans-serif;">
                            <strong style="color:#0284c7;">${item.codigo || 'Punto'}</strong><br>
                            <b>Área:</b> ${item.area || '—'}<br>
                            <b>Puesto:</b> ${item.puesto_trabajo || '—'}<br>
                            <b>A(8):</b> ${parseFloat(item.a8 || 0).toFixed(4)} m/s²<br>
                            <b>Estado:</b> ${item.estado || '—'}
                        </div>
                    `);
                }
            }
        });

        if (bounds.length > 0) {
            allMapInstance.fitBounds(bounds, { padding: [30, 30] });
        }
    }, 200);
};

window.closeAllLocationsModal = function() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) modal.style.display = 'none';
};

window.openSingleLocationModal = function(item) {
    const modal = document.getElementById('singleLocationModal');
    if (!modal) return;

    modal.style.display = 'flex';
    document.getElementById('singleLocCode').textContent = item.codigo || '—';
    document.getElementById('singleLocArea').textContent = item.area || '—';
    document.getElementById('singleLocUtm').textContent = `E: ${item.utm_easting || '—'}, N: ${item.utm_northing || '—'}, Z: ${item.utm_zone || '19K'}`;

    setTimeout(() => {
        let lat = parseFloat(item.latitude);
        let lng = parseFloat(item.longitude);
        if (isNaN(lat) || isNaN(lng)) {
            lat = -16.5; lng = -68.15;
        }

        if (!singleMapInstance) {
            singleMapInstance = L.map('singleLocationMap').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(singleMapInstance);
            L.marker([lat, lng]).addTo(singleMapInstance);
        } else {
            singleMapInstance.invalidateSize();
            singleMapInstance.setView([lat, lng], 15);
        }
    }, 200);
};

window.closeSingleLocationModal = function() {
    const modal = document.getElementById('singleLocationModal');
    if (modal) modal.style.display = 'none';
};

window.openNormativeTablesModal = function() {
    const modal = document.getElementById('normativeTablesModal');
    if (modal) modal.style.display = 'flex';
};

window.closeNormativeTablesModal = function() {
    const modal = document.getElementById('normativeTablesModal');
    if (modal) modal.style.display = 'none';
};

window.openExportModal = function() {
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('open', 'active');
        modal.style.display = 'flex';
    }
};

window.closeExportModal = function() {
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('open', 'active');
        modal.style.display = 'none';
    }
};

window.openPhotoViewerModal = function(photos, title) {
    const modal = document.getElementById('photoViewerModal');
    if (!modal) return;

    const list = Array.isArray(photos) ? photos : [photos];
    if (list.length === 0) return;

    document.getElementById('photoViewerTitle').textContent = title || 'Evidencia Fotográfica';
    const mainImg = document.getElementById('mainViewerImage');
    mainImg.src = list[0];

    const thumbsRow = document.getElementById('photoThumbnailsRow');
    thumbsRow.innerHTML = '';

    if (list.length > 1) {
        list.forEach((url, i) => {
            const img = document.createElement('img');
            img.src = url;
            img.style.width = '60px';
            img.style.height = '45px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '4px';
            img.style.cursor = 'pointer';
            img.style.border = i === 0 ? '2px solid #0284c7' : '2px solid transparent';
            img.onclick = () => {
                mainImg.src = url;
                thumbsRow.querySelectorAll('img').forEach(t => t.style.borderColor = 'transparent');
                img.style.borderColor = '#0284c7';
            };
            thumbsRow.appendChild(img);
        });
    }

    modal.style.display = 'flex';
};

window.closePhotoViewerModal = function() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.style.display = 'none';
};

window.confirmDeleteVibracionMeasurement = function(id, code) {
    if (confirm(`¿Estás seguro de que deseas eliminar el punto ${code}? Esta acción no se puede deshacer.`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/modulos/${window.MODULE_ID}/vibracion/mediciones/${id}`;

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = window.CSRF_TOKEN;
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        document.body.appendChild(form);
        form.submit();
    }
};

/* ==========================================================================
   8. EXPORTACIÓN TÉCNICA A EXCEL (EXCELJS)
   ========================================================================== */

window.exportVibracionExcel = async function() {
    if (typeof ExcelJS === 'undefined') {
        alert('Cargando librería de exportación Excel. Intenta de nuevo en un segundo.');
        return;
    }

    const data = window.METRIC_VIBRACION_CONFIG.measurements || [];
    const headerInfo = window.METRIC_VIBRACION_CONFIG.technicalHeader || {};

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Metric v2 Pachabol';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Monitoreo Vibración');

    sheet.columns = [
        { key: 'num', width: 10 },
        { key: 'codigo', width: 14 },
        { key: 'fecha', width: 14 },
        { key: 'hora', width: 12 },
        { key: 'area', width: 26 },
        { key: 'puesto', width: 26 },
        { key: 'trabajador', width: 26 },
        { key: 'equipo', width: 24 },
        { key: 'tipo', width: 18 },
        { key: 'texp', width: 14 },
        { key: 'aeqx', width: 14 },
        { key: 'aeqy', width: 14 },
        { key: 'aeqz', width: 14 },
        { key: 'a8', width: 16 },
        { key: 'estado', width: 18 },
        { key: 'easting', width: 16 },
        { key: 'northing', width: 16 },
        { key: 'zone', width: 10 },
        { key: 'staff', width: 22 }
    ];

    // Encabezado Corporativo
    sheet.mergeCells('A1:S1');
    const titleRow = sheet.getCell('A1');
    titleRow.value = 'PACHABOL S.R.L. — PLANILLA TÉCNICA DE MONITOREO DE VIBRACIÓN OCUPACIONAL';
    titleRow.font = { name: 'Arial', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
    titleRow.alignment = { horizontal: 'center', vertical: 'middle' };
    titleRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0284C7' } };
    sheet.getRow(1).height = 36;

    sheet.mergeCells('A2:S2');
    const subTitle = sheet.getCell('A2');
    subTitle.value = `Instalación: ${headerInfo.installationName || '—'} | Normativas: ISO 2631-1 & ISO 5349-1`;
    subTitle.font = { name: 'Arial', size: 11, bold: true, color: { argb: 'FF0F172A' } };
    subTitle.alignment = { horizontal: 'center', vertical: 'middle' };
    subTitle.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFE0F2FE' } };
    sheet.getRow(2).height = 24;

    sheet.addRow([]);

    // Cabeceras de Columnas
    const headers = [
        'N°', 'Código', 'Fecha', 'Hora', 'Área', 'Puesto de Trabajo',
        'Trabajador Evaluado', 'Máquina / Equipo', 'Tipo Monitoreo', 'T. Exp (h)',
        'Aeq X (m/s²)', 'Aeq Y (m/s²)', 'Aeq Z (m/s²)', 'A(8) Normalizado',
        'Evaluación', 'Este (X)', 'Norte (Y)', 'Zona', 'Técnico Responsable'
    ];
    const headerRow = sheet.addRow(headers);
    headerRow.height = 26;
    headerRow.eachCell(cell => {
        cell.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FFFFFFFF' } };
        cell.alignment = { horizontal: 'center', vertical: 'middle' };
        cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0369A1' } };
        cell.border = {
            top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            bottom: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            left: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            right: { style: 'thin', color: { argb: 'FFCBD5E1' } }
        };
    });

    // Filas de Datos
    data.forEach((item, idx) => {
        const isCE = item.tipo === 'cuerpo_entero';
        const row = sheet.addRow([
            idx + 1,
            item.codigo || `VIB-${idx + 1}`,
            item.date || '—',
            item.time || '—',
            item.area || '—',
            item.puesto_trabajo || '—',
            item.trabajador_evaluado || '—',
            item.maquina_equipo || '—',
            isCE ? 'Cuerpo Entero' : 'Mano-Brazo',
            parseFloat(item.tiempo_expos_h || 8.0),
            parseFloat(item.aeqx || 0),
            parseFloat(item.aeqy || 0),
            parseFloat(item.aeqz || 0),
            parseFloat(item.a8 || 0),
            item.estado || 'CUMPLE',
            item.utm_easting ? parseFloat(item.utm_easting) : '',
            item.utm_northing ? parseFloat(item.utm_northing) : '',
            item.utm_zone || '19K',
            item.registered_by || window.REGISTERED_BY_HEADER || '—'
        ]);

        row.height = 20;
        row.eachCell((cell, colNumber) => {
            cell.font = { name: 'Arial', size: 9.5 };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                right: { style: 'thin', color: { argb: 'FFE2E8F0' } }
            };
            if ([1, 2, 3, 4, 9, 10, 15, 18].includes(colNumber)) {
                cell.alignment = { horizontal: 'center', vertical: 'middle' };
            } else if ([11, 12, 13, 14, 16, 17].includes(colNumber)) {
                cell.alignment = { horizontal: 'right', vertical: 'middle' };
            } else {
                cell.alignment = { horizontal: 'left', vertical: 'middle' };
            }
        });
    });

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Planilla_Vibracion_${headerInfo.installationName || 'Proyecto'}_${new Date().toISOString().slice(0, 10)}.xlsx`;
    link.click();
};
window.downloadExcelPlanilla = window.exportVibracionExcel;


/* ==========================================================================
   9. UTILIDADES DE CONVERSIÓN GEOGRÁFICA (WGS84 <-> UTM)
   ========================================================================== */

function wgs84ToUtm(lat, lon, zoneStr) {
    let zoneNumber = 19;
    if (zoneStr) {
        const m = zoneStr.match(/(\d+)/);
        if (m) zoneNumber = parseInt(m[1]);
    }

    const a = 6378137.0;
    const eccSquared = 0.00669438;
    const k0 = 0.9996;

    const latRad = lat * (Math.PI / 180);
    const lonRad = lon * (Math.PI / 180);
    const lonOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    const lonOriginRad = lonOrigin * (Math.PI / 180);

    const eccPrimeSquared = eccSquared / (1 - eccSquared);
    const N = a / Math.sqrt(1 - eccSquared * Math.sin(latRad) * Math.sin(latRad));
    const T = Math.tan(latRad) * Math.tan(latRad);
    const C = eccPrimeSquared * Math.cos(latRad) * Math.cos(latRad);
    const A = Math.cos(latRad) * (lonRad - lonOriginRad);

    const M = a * ((1 - eccSquared / 4 - 3 * eccSquared * eccSquared / 64 - 5 * eccSquared * eccSquared * eccSquared / 256) * latRad
        - (3 * eccSquared / 8 + 3 * eccSquared * eccSquared / 32 + 45 * eccSquared * eccSquared * eccSquared / 1024) * Math.sin(2 * latRad)
        + (15 * eccSquared * eccSquared / 256 + 45 * eccSquared * eccSquared * eccSquared / 1024) * Math.sin(4 * latRad)
        - (35 * eccSquared * eccSquared * eccSquared / 3072) * Math.sin(6 * latRad));

    let easting = k0 * N * (A + (1 - T + C) * A * A * A / 6 + (5 - 18 * T + T * T + 72 * C - 58 * eccPrimeSquared) * A * A * A * A * A / 120) + 500000.0;
    let northing = k0 * (M + N * Math.tan(latRad) * (A * A / 2 + (5 - T + 9 * C + 4 * C * C) * A * A * A * A / 24 + (61 - 58 * T + T * T + 600 * C - 330 * eccPrimeSquared) * A * A * A * A * A * A / 720));

    if (lat < 0) northing += 10000000.0;

    return { easting, northing, zone: zoneStr };
}

function utmToLatLng(easting, northing, zoneStr) {
    let zoneNumber = 19;
    let isSouth = true;
    if (zoneStr) {
        const m = zoneStr.match(/(\d+)\s*([A-Za-z]?)/);
        if (m) {
            zoneNumber = parseInt(m[1]);
            if (m[2] && m[2].toUpperCase() > 'M') isSouth = false;
        }
    }

    const a = 6378137.0;
    const e = 0.081819191;
    const e1sq = 0.006739497;
    const k0 = 0.9996;

    const x = parseFloat(easting) - 500000.0;
    let y = parseFloat(northing);
    if (isSouth) y -= 10000000.0;

    const m_val = y / k0;
    const mu = m_val / (a * (1.0 - e * e / 4.0 - 3.0 * Math.pow(e, 4) / 64.0 - 5.0 * Math.pow(e, 6) / 256.0));
    const e1 = (1.0 - Math.sqrt(1.0 - e * e)) / (1.0 + Math.sqrt(1.0 - e * e));

    const j1 = 3.0 * e1 / 2.0 - 27.0 * Math.pow(e1, 3) / 32.0;
    const j2 = 21.0 * Math.pow(e1, 2) / 16.0 - 55.0 * Math.pow(e1, 4) / 32.0;
    const j3 = 151.0 * Math.pow(e1, 3) / 96.0;
    const j4 = 1097.0 * Math.pow(e1, 4) / 512.0;

    const fp = mu + j1 * Math.sin(2.0 * mu) + j2 * Math.sin(4.0 * mu) + j3 * Math.sin(6.0 * mu) + j4 * Math.sin(8.0 * mu);
    const c1 = e1sq * Math.pow(Math.cos(fp), 2);
    const t1 = Math.pow(Math.tan(fp), 2);
    const r1 = a * (1.0 - e * e) / Math.pow(1.0 - e * e * Math.pow(Math.sin(fp), 2), 1.5);
    const n1 = a / Math.sqrt(1.0 - e * e * Math.pow(Math.sin(fp), 2));
    const d = x / (n1 * k0);

    let lat = fp - (n1 * Math.tan(fp) / r1) * (d * d / 2.0 - (5.0 + 3.0 * t1 + 10.0 * c1 - 4.0 * c1 * c1 - 9.0 * e1sq) * Math.pow(d, 4) / 24.0 + (61.0 + 90.0 * t1 + 298.0 * c1 + 45.0 * t1 * t1 - 252.0 * e1sq - 3.0 * c1 * c1) * Math.pow(d, 6) / 720.0);
    lat = lat * (180.0 / Math.PI);

    const lonOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    let lon = (d - (1.0 + 2.0 * t1 + c1) * Math.pow(d, 3) / 6.0 + (5.0 - 2.0 * c1 + 28.0 * t1 - 3.0 * c1 * c1 + 8.0 * e1sq + 24.0 * Math.pow(t1, 2)) * Math.pow(d, 5) / 120.0) / Math.cos(fp);
    lon = lonOrigin + lon * (180.0 / Math.PI);

    return { lat, lng: lon };
}

/* ==========================================================================
   10. MODAL DE EXPORTACIÓN Y REPORTE FOTOGRÁFICO MOSAICO CARTA (VIBRACIÓN)
   ========================================================================== */
function openExportModal() {
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('open', 'active');
        modal.style.display = 'flex';
    }
}
window.openExportModal = openExportModal;

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('open', 'active');
        modal.style.display = 'none';
    }
}
window.closeExportModal = closeExportModal;

function openPhotoViewer(src, caption = '') {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('mainViewerImage') || document.getElementById('photoViewerImg');
    const title = document.getElementById('photoViewerTitle');
    if (!modal || !img) return;

    img.src = src;
    if (title) title.textContent = caption || 'Evidencia Fotográfica de Vibración';
    modal.classList.add('open', 'active');
    modal.style.display = 'flex';
}
window.openPhotoViewer = openPhotoViewer;

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) {
        modal.classList.remove('open', 'active');
        modal.style.display = 'none';
    }
}
window.closePhotoViewer = closePhotoViewer;
window.closePhotoViewerModal = closePhotoViewer;

const photoReportConfig = {
    grid: '2x3',
    selectedPoints: new Set(),
    photoIndices: {},
    currentTab: 'interactive',
    isInitialized: false
};
let photoReportAutoSaveTimeout = null;

function openPhotoReportModal() {
    closeExportModal();
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.add('open', 'active');
        modal.style.display = 'flex';
        initPhotoReportEngine();
    }
}
window.openPhotoReportModal = openPhotoReportModal;
window.openPhotoReportConfigModal = openPhotoReportModal;

function closePhotoReportModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.remove('open', 'active');
        modal.style.display = 'none';
    }
}
window.closePhotoReportModal = closePhotoReportModal;
window.closePhotoReportConfigModal = closePhotoReportModal;

/**
 * Inicializar motor de reporte fotográfico con datos guardados
 */
function initPhotoReportEngine() {
    if (!photoReportConfig.isInitialized) {
        let saved = null;
        if (typeof window.PHOTO_REPORT_INITIAL_SETTINGS === 'object' && window.PHOTO_REPORT_INITIAL_SETTINGS && Object.keys(window.PHOTO_REPORT_INITIAL_SETTINGS).length > 0) {
            saved = window.PHOTO_REPORT_INITIAL_SETTINGS;
        }
        if (!saved) {
            try {
                const local = localStorage.getItem('metric_vibracion_photo_' + (window.MODULE_ID || '1'));
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

        // Puntos seleccionados para el PDF
        photoReportConfig.selectedPoints.clear();
        const allItems = window.ALL_MEASUREMENTS_DATA || [];
        if (saved && Array.isArray(saved.selected_points) && saved.selected_points.length > 0) {
            saved.selected_points.forEach(id => photoReportConfig.selectedPoints.add(Number(id)));
        } else {
            allItems.forEach(m => photoReportConfig.selectedPoints.add(Number(m.id)));
        }

        // Índices de fotos por punto
        photoReportConfig.photoIndices = {};
        if (saved && saved.photo_indices && typeof saved.photo_indices === 'object') {
            for (const [k, v] of Object.entries(saved.photo_indices)) {
                photoReportConfig.photoIndices[Number(k)] = Number(v) || 0;
            }
        }

        photoReportConfig.isInitialized = true;
    }

    updateGridSelectorButtonsUI();
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
    if (Array.isArray(m.raw_images) && m.raw_images.length > 0) {
        return m.raw_images.filter(Boolean);
    }
    return [];
}

function formatPointCode(num) {
    if (!num) return 'VIB-01';
    const str = String(num).trim();
    if (str.toUpperCase().startsWith('VIB-') || str.toUpperCase().startsWith('V-') || str.toUpperCase().startsWith('P-')) {
        return str.toUpperCase();
    }
    const numericPart = str.replace(/[^0-9]/g, '');
    if (numericPart) {
        return 'VIB-' + numericPart.padStart(2, '0');
    }
    return '#' + str;
}

function escapeHtml(str) {
    if (!str) return '';
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
                <p style="margin: 0; font-size: 13.5px; font-weight: 600;">No hay puntos de monitoreo de vibración registrados para generar el reporte fotográfico.</p>
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
        const codeFormatted = formatPointCode(m.num || m.codigo);
        const hasMultiplePhotos = images.length > 1;

        const estadoStr = String(m.estado || 'CUMPLE').toUpperCase();
        let estadoBadge = `<span style="font-size: 10px; font-weight: 800; background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 2px 7px; border-radius: 4px;">CUMPLE</span>`;
        if (estadoStr.includes('ACCIÓN') || estadoStr.includes('ACCION')) {
            estadoBadge = `<span style="font-size: 10px; font-weight: 800; background: #fffbeb; color: #b45309; border: 1px solid #fde68a; padding: 2px 7px; border-radius: 4px;">NIVEL DE ACCIÓN</span>`;
        } else if (estadoStr.includes('SUPERA') || estadoStr.includes('NO CUMPLE')) {
            estadoBadge = `<span style="font-size: 10px; font-weight: 800; background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; padding: 2px 7px; border-radius: 4px;">SUPERA LÍMITE</span>`;
        }

        const a8Val = m.a8 !== null && m.a8 !== undefined && !isNaN(m.a8) ? parseFloat(m.a8).toFixed(2) : '—';
        const tipoLabel = m.tipo_label || (m.tipo === 'mano_brazo' ? 'Mano - Brazo' : 'Cuerpo Entero');

        html += `
            <div class="photo-point-card ${isSelected ? '' : 'excluded'}" id="photoPointCard_${pointId}">
                <!-- Barra de Selección para el PDF -->
                <div class="photo-card-select-bar">
                    <label class="photo-select-checkbox-lbl" title="Marcar para incluir en el reporte PDF">
                        <input type="checkbox" id="chkPoint_${pointId}" ${isSelected ? 'checked' : ''} onchange="togglePointSelection(${pointId}, this.checked)">
                        <span>Incluir en PDF</span>
                    </label>
                    <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;">
                        ${codeFormatted}
                    </span>
                </div>

                <!-- ARRIBA DE LA FOTO: Puesto, Área, Máquina, Tipo y A(8) -->
                <div class="photo-card-info-top">
                    <div class="photo-info-row">
                        <span class="photo-info-label">Puesto:</span>
                        <span class="photo-info-value" title="${escapeHtml(m.puesto_trabajo || m.workstation || 'Operador')}" style="font-weight: 800; color: #0f172a;">${escapeHtml(m.puesto_trabajo || m.workstation || 'Operador')}</span>
                    </div>
                    <div class="photo-info-row">
                        <span class="photo-info-label">Área:</span>
                        <span class="photo-info-value" title="${escapeHtml(m.area || 'General')}">${escapeHtml(m.area || 'General')} ${m.maquina_equipo && m.maquina_equipo !== '—' ? '• ' + escapeHtml(m.maquina_equipo) : ''}</span>
                    </div>
                    <div class="photo-info-row" style="margin-top: 2px; flex-wrap: wrap; gap: 4px;">
                        ${estadoBadge}
                        <span style="font-size: 9.5px; font-weight: 700; background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; padding: 1.5px 5px; border-radius: 4px;">
                            ${escapeHtml(tipoLabel)}
                        </span>
                        <span style="font-size: 9.5px; font-weight: 700; background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; padding: 1.5px 5px; border-radius: 4px;">
                            A(8): <strong>${a8Val} m/s²</strong>
                        </span>
                    </div>
                </div>

                <!-- MARCO DE FOTOGRAFÍA CON CARRUSEL DE FLECHAS -->
                <div class="photo-card-img-box">
                    ${currentImg ? `
                        <img id="photoImg_${pointId}" src="${currentImg}" alt="${codeFormatted}" onclick="openPhotoViewer('${currentImg}', '${codeFormatted}: ${addslashes(m.puesto_trabajo || m.workstation || '')}')" title="Clic para ampliar fotografía">
                        <button type="button" class="photo-zoom-btn" onclick="openPhotoViewer('${currentImg}', '${codeFormatted}: ${addslashes(m.puesto_trabajo || m.workstation || '')}')" title="Ampliar">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                        </button>
                    ` : `
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #64748b;">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                            <span style="font-size: 11px; margin-top: 4px; font-weight: 600;">Sin fotografía</span>
                        </div>
                    `}

                    <!-- Flechas para alternar qué foto va al PDF -->
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

                <!-- DEBAJO DE LA FOTO: Fecha, Hora y Trabajador -->
                <div class="photo-card-info-bottom">
                    <span class="photo-dt-label">
                        <svg class="photo-dt-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                        </svg>
                        ${escapeHtml(m.date || '—')} ${escapeHtml(m.time && m.time !== '—' ? m.time : '')}
                    </span>
                    <span class="photo-dt-pill" title="${escapeHtml(m.trabajador_evaluado || m.maquina_equipo || '')}">${escapeHtml(m.trabajador_evaluado || m.maquina_equipo || 'Operador')}</span>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
}
window.renderInteractiveGrid = renderInteractiveGrid;

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
                <!-- ENCABEZADO EJECUTIVO OFICIAL CON COLORES DEL TEMA -->
                <div class="sheet-header-box">
                    <div class="sheet-header-left">
                        <div class="sheet-header-logo-badge">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                            </svg>
                        </div>
                        <div class="sheet-header-titles">
                            <div class="sheet-header-brand-row">
                                <span class="sheet-company-title">PACHABOL S.R.L.</span>
                                <span class="sheet-brand-dot">•</span>
                                <span class="sheet-system-tag">Higiene &amp; Seguridad Ocupacional</span>
                            </div>
                            <h1>REPORTE FOTOGRÁFICO — MONITOREO DE VIBRACIÓN</h1>
                            <div class="sheet-header-subtitle-row">
                                <span class="sheet-tech-tag">METRIC v2 • CUERPO ENTERO &amp; MANO-BRAZO (ISO 2631-1 &amp; ISO 5349-1)</span>
                            </div>
                        </div>
                    </div>

                    <!-- Ficha Técnica de Metadatos del Encabezado -->
                    <div class="sheet-header-meta-card">
                        <div class="sheet-meta-card-header">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            <span>Datos Técnicos de Monitoreo</span>
                        </div>
                        <div class="sheet-meta-grid">
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">INSTALACIÓN:</span>
                                <span class="sheet-meta-val" title="${escapeHtml(tech.installationName || 'No registrada')}">${escapeHtml(tech.installationName || 'No registrada')}</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">EQUIPO:</span>
                                <span class="sheet-meta-val">${escapeHtml(tech.equipmentName || 'Acelerómetro Triaxial')} (${escapeHtml(tech.equipmentModel || 'SV 106A')})</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">FECHA:</span>
                                <span class="sheet-meta-val">${escapeHtml(tech.startDateFormatted || '—')}${tech.endDateFormatted && tech.endDateFormatted !== tech.startDateFormatted ? ' al ' + escapeHtml(tech.endDateFormatted) : ''}</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">SERIE / MARCA:</span>
                                <span class="sheet-meta-val">${escapeHtml(tech.equipmentSerial || '—')} • ${escapeHtml(tech.equipmentBrand || 'Svantek')}</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">TIPO:</span>
                                <span class="sheet-meta-val">${escapeHtml(tech.monitoringType || 'Seguimiento de Vibraciones')}</span>
                            </div>
                            <div class="sheet-meta-row">
                                <span class="sheet-meta-lbl">NORMATIVA:</span>
                                <span class="sheet-meta-val">ISO 2631-1 (Cuerpo Entero) • ISO 5349-1 (Mano - Brazo)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CUADRÍCULA DE FOTOGRAFÍAS EN MOSAICO -->
                <div class="sheet-photos-grid dist-${photoReportConfig.grid}">
                    ${pageItems.map(m => {
                        const pointId = Number(m.id);
                        const images = getPointImages(m);
                        const selectedIdx = photoReportConfig.photoIndices[pointId] || 0;
                        const chosenImg = images[selectedIdx] || images[0] || '';
                        const codeFormatted = formatPointCode(m.num || m.codigo);
                        const estadoStr = String(m.estado || 'CUMPLE').toUpperCase();
                        const a8Val = m.a8 !== null && m.a8 !== undefined && !isNaN(m.a8) ? parseFloat(m.a8).toFixed(2) : '—';
                        const tipoLabel = m.tipo_label || (m.tipo === 'mano_brazo' ? 'Mano - Brazo' : 'Cuerpo Entero');

                        let estadoColor = '#166534';
                        if (estadoStr.includes('ACCIÓN') || estadoStr.includes('ACCION')) estadoColor = '#b45309';
                        if (estadoStr.includes('SUPERA') || estadoStr.includes('NO CUMPLE')) estadoColor = '#991b1b';

                        return `
                            <div class="sheet-card-item">
                                <!-- ARRIBA DE LA FOTO: Código, Puesto, Tipo y A(8) -->
                                <div class="photo-card-info-top">
                                    <div class="photo-info-row" style="justify-content: space-between;">
                                        <div style="display: flex; align-items: baseline; gap: 4px; overflow: hidden;">
                                            <span class="photo-info-value photo-code">${codeFormatted}</span>
                                            <span class="photo-info-value" title="${escapeHtml(m.puesto_trabajo || m.workstation || 'Operador')}" style="font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(m.puesto_trabajo || m.workstation || 'Operador')}</span>
                                        </div>
                                    </div>
                                    <div class="photo-info-row">
                                        <span class="photo-info-label">Área:</span>
                                        <span class="photo-info-value" title="${escapeHtml(m.area || 'General')}" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${escapeHtml(m.area || 'General')} ${m.maquina_equipo && m.maquina_equipo !== '—' ? '• ' + escapeHtml(m.maquina_equipo) : ''}</span>
                                    </div>
                                    <div class="photo-info-row" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 7.5px;">
                                        ${escapeHtml(tipoLabel)} • A(8): <strong>${a8Val} m/s²</strong> • <span style="font-weight: 800; color: ${estadoColor};">${escapeHtml(estadoStr)}</span>
                                    </div>
                                </div>

                                <!-- FOTOGRAFÍA -->
                                <div class="photo-card-img-box">
                                    ${chosenImg ? `
                                        <img src="${chosenImg}" alt="${codeFormatted}">
                                    ` : `
                                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8;">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/></svg>
                                            <span style="font-size: 9.5px; font-weight: 600; margin-top: 2px;">Sin fotografía</span>
                                        </div>
                                    `}
                                </div>

                                <!-- DEBAJO DE LA FOTO: Fecha, Hora y Trabajador/Máquina -->
                                <div class="photo-card-info-bottom">
                                    <span class="photo-dt-label">
                                        <svg class="photo-dt-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                        </svg>
                                        ${escapeHtml(m.date || '—')} ${escapeHtml(m.time && m.time !== '—' ? m.time : '')}
                                    </span>
                                    <span class="photo-dt-pill" title="${escapeHtml(m.trabajador_evaluado || m.maquina_equipo || '')}">${escapeHtml(m.trabajador_evaluado || m.maquina_equipo || 'Operador')}</span>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>

                <!-- PIE DE PÁGINA ELEGANTE OFICIAL DEL PDF -->
                <div class="sheet-footer-box">
                    <div class="sheet-footer-brand">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <span>METRIC v2 — Pachabol S.R.L. • Monitoreo de Vibración Ocupacional</span>
                    </div>
                    <div style="font-style: italic; color: #64748b; font-size: 8px;">
                        Reporte Fotográfico Técnico • Formato Hoja Carta (${photoReportConfig.grid})
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 8px;">
                        <span class="sheet-footer-page-num">Página ${pageNum} de ${totalPages}</span>
                        <span>${dateEmissionFormatted}</span>
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
    if (photoReportAutoSaveTimeout) clearTimeout(photoReportAutoSaveTimeout);
    photoReportAutoSaveTimeout = setTimeout(() => {
        savePhotoReportSettingsToServer(false);
    }, 650);
}

async function savePhotoReportSettingsToServer(showToast = false) {
    const btnSave = document.getElementById('btnSaveReportSettings');
    if (btnSave && showToast) {
        btnSave.disabled = true;
        btnSave.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 0.8s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>
            <span>Guardando...</span>
        `;
    }

    const payload = {
        grid: photoReportConfig.grid,
        selected_points: Array.from(photoReportConfig.selectedPoints),
        photo_indices: photoReportConfig.photoIndices
    };

    try {
        localStorage.setItem('metric_vibracion_photo_' + (window.MODULE_ID || '1'), JSON.stringify(payload));
    } catch (e) {
        console.warn('Error saving to localStorage', e);
    }

    try {
        const res = await fetch(`/modulos/${window.MODULE_ID}/vibracion/photo-report-settings`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN
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

            const canvas = await html2canvas(sheet, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            });

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
        const fileName = `Reporte_Fotografico_Vibracion_${installationClean}_${dateStr}.pdf`;

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
