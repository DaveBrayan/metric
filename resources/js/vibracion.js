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
    const modal = document.getElementById('exportModal');
    if (modal) modal.style.display = 'flex';
};

window.closeExportModal = function() {
    const modal = document.getElementById('exportModal');
    if (modal) modal.style.display = 'none';
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
