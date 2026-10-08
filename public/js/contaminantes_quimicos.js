/**
 * METRIC V2 — Monitoreo de Contaminantes Químicos
 * Módulo JavaScript interactivo y controlador de modales (Vite ES Module)
 */

// 1. Inicialización de configuración del servidor
const cfg = window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/contaminantes-quimicos/header');

// 2. Estado Global de Paginación y Filtrado
let currentPage = 1;
const itemsPerPage = 10;
let filteredRows = [];

// 3. Estado de Modales y Carrusel Fotográfico
let isEditUnlocked = false;
let modalPhotos = {
    create: [],
    edit: []
};
let modalPhotoIndex = {
    create: 0,
    edit: 0
};

// Mapas Leaflet
let createMiniMap = null;
let createMiniMarker = null;
let editMiniMap = null;
let editMiniMarker = null;
let allLocMap = null;
let allLocMarkers = [];
let singleLocMap = null;
let singleLocMarker = null;

/* ==========================================================================
   CÁLCULOS TÉCNICOS EN TIEMPO REAL (MASA NETA, VOLUMEN, CONCENTRACIÓN)
   ========================================================================== */
function calcContaminantes(prefix) {
    const miEl = document.getElementById(`${prefix}_masa_inicial_filtro_mg`);
    const mfEl = document.getElementById(`${prefix}_masa_final_filtro_mg`);
    const hiEl = document.getElementById(`${prefix}_hora_inicio`);
    const hfEl = document.getElementById(`${prefix}_hora_final`);
    const qiEl = document.getElementById(`${prefix}_q_inicial_lmin`);
    const qfEl = document.getElementById(`${prefix}_q_final_lmin`);

    const masaIni = parseFloat(miEl ? miEl.value : 0) || 0;
    const masaFin = parseFloat(mfEl ? mfEl.value : 0) || 0;
    const masaNeta = (masaFin > masaIni) ? (masaFin - masaIni) : 0;

    let qIni = parseFloat(qiEl ? qiEl.value : 0) || 0;
    let qFin = parseFloat(qfEl ? qfEl.value : 0) || 0;
    let qProm = 0;
    if (qIni > 0 && qFin > 0) qProm = (qIni + qFin) / 2;
    else qProm = qIni || qFin || 0;

    let tiempoMin = 0;
    const hIniVal = hiEl ? hiEl.value : '';
    const hFinVal = hfEl ? hfEl.value : '';
    if (hIniVal && hFinVal) {
        const [h1, m1] = hIniVal.split(':').map(Number);
        const [h2, m2] = hFinVal.split(':').map(Number);
        if (!isNaN(h1) && !isNaN(m1) && !isNaN(h2) && !isNaN(m2)) {
            let t1 = h1 * 60 + m1;
            let t2 = h2 * 60 + m2;
            if (t2 < t1) t2 += 24 * 60;
            tiempoMin = t2 - t1;
        }
    }

    // Volumen en m3 = (Q_prom L/min * tiempo min) / 1000
    const volumenM3 = (qProm > 0 && tiempoMin > 0) ? (qProm * tiempoMin / 1000) : 0;

    // Concentración en mg/m3 = masa_neta (mg) / volumen (m3)
    const concMgM3 = (volumenM3 > 0 && masaNeta > 0) ? (masaNeta / volumenM3) : 0;

    const mnDisp = document.getElementById(`${prefix}_calc_masa_neta`);
    const volDisp = document.getElementById(`${prefix}_calc_volumen`);
    const concDisp = document.getElementById(`${prefix}_calc_conc`);

    if (mnDisp) mnDisp.textContent = `${masaNeta.toFixed(4)} mg`;
    if (volDisp) volDisp.textContent = `${volumenM3.toFixed(4)} m³`;
    if (concDisp) concDisp.textContent = `${concMgM3.toFixed(4)} mg/m³`;
}

/* ==========================================================================
   BÚSQUEDA EN VIVO Y PAGINACIÓN REACTIVA
   ========================================================================== */
function getTableDataRows() {
    const tbody = document.getElementById('contaminantesTableBody');
    if (!tbody) return [];
    return Array.from(tbody.querySelectorAll('tr.illumination-data-row'));
}

function searchContaminantesLive() {
    const input = document.getElementById('contaminantesSearchInput');
    const query = input ? input.value.toLowerCase().trim() : '';
    const rows = getTableDataRows();

    filteredRows = rows.filter(row => {
        const searchAttr = row.getAttribute('data-search') || '';
        return searchAttr.includes(query);
    });

    currentPage = 1;
    applyPagination();
}

function applyPagination() {
    const rows = getTableDataRows();
    const emptyRow = document.getElementById('emptyTableRow');
    const noResultsRow = document.getElementById('noResultsSearchRow');

    if (rows.length === 0) {
        if (emptyRow) emptyRow.style.display = '';
        if (noResultsRow) noResultsRow.style.display = 'none';
        updatePaginationBar(0, 0, 0);
        return;
    }

    if (emptyRow) emptyRow.style.display = 'none';

    if (filteredRows.length === 0) {
        rows.forEach(r => r.style.display = 'none');
        if (noResultsRow) noResultsRow.style.display = '';
        updatePaginationBar(0, 0, 0);
        return;
    }

    if (noResultsRow) noResultsRow.style.display = 'none';

    const total = filteredRows.length;
    const totalPages = Math.ceil(total / itemsPerPage) || 1;
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const startIdx = (currentPage - 1) * itemsPerPage;
    const endIdx = startIdx + itemsPerPage;

    rows.forEach(r => r.style.display = 'none');
    filteredRows.slice(startIdx, endIdx).forEach(r => r.style.display = '');

    updatePaginationBar(startIdx + 1, Math.min(endIdx, total), total);
    renderPaginationControls(totalPages);
}

function updatePaginationBar(start, end, total) {
    const startEl = document.getElementById('cqPageStart');
    const endEl = document.getElementById('cqPageEnd');
    const totalEl = document.getElementById('cqPageTotal');
    if (startEl) startEl.textContent = start;
    if (endEl) endEl.textContent = end;
    if (totalEl) totalEl.textContent = total;
}

function renderPaginationControls(totalPages) {
    const container = document.getElementById('contaminantesPaginationControls');
    if (!container) return;
    container.innerHTML = '';

    if (totalPages <= 1) return;

    // Botón Anterior
    const prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.className = 'pagination-btn';
    prevBtn.innerHTML = '‹';
    prevBtn.disabled = currentPage === 1;
    prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; applyPagination(); } };
    container.appendChild(prevBtn);

    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
            const pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = `pagination-btn ${p === currentPage ? 'active' : ''}`;
            pageBtn.textContent = p;
            pageBtn.onclick = () => { currentPage = p; applyPagination(); };
            container.appendChild(pageBtn);
        } else if (p === currentPage - 2 || p === currentPage + 2) {
            const dots = document.createElement('span');
            dots.style.padding = '0 4px';
            dots.style.color = '#94a3b8';
            dots.textContent = '...';
            container.appendChild(dots);
        }
    }

    // Botón Siguiente
    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.className = 'pagination-btn';
    nextBtn.innerHTML = '›';
    nextBtn.disabled = currentPage === totalPages;
    nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; applyPagination(); } };
    container.appendChild(nextBtn);
}

/* ==========================================================================
   AUTO-GUARDADO DEL ENCABEZADO TÉCNICO (INLINE)
   ========================================================================== */
function autoSaveHeaderField() {
    const installation = document.getElementById('inline_installation_name')?.value || '';
    const startDate = document.getElementById('inline_start_date')?.value || '';
    const endDate = document.getElementById('inline_end_date')?.value || '';
    const monitoringType = document.getElementById('inline_monitoring_type')?.value || '';

    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.style.background = '#fef3c7';
        badge.style.color = '#d97706';
        badge.style.borderColor = '#fde68a';
        badge.innerHTML = `<span>Guardando...</span>`;
    }

    fetch(UPDATE_HEADER_URL, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            installation_name: installation,
            start_date: startDate,
            end_date: endDate,
            monitoring_type: monitoringType
        })
    })
    .then(res => res.json())
    .then(data => {
        if (badge) {
            badge.style.background = '#ecfdf5';
            badge.style.color = '#059669';
            badge.style.borderColor = '#a7f3d0';
            badge.innerHTML = `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>Guardado</span>`;
        }
    })
    .catch(err => {
        if (badge) {
            badge.style.background = '#fef2f2';
            badge.style.color = '#dc2626';
            badge.style.borderColor = '#fecaca';
            badge.innerHTML = `<span>Error</span>`;
        }
    });
}

/* ==========================================================================
   CARRUSEL FOTOGRÁFICO DE MODALES (CREATE Y EDIT)
   ========================================================================== */
function renderPhotoSlider(prefix) {
    const photos = modalPhotos[prefix] || [];
    const idx = modalPhotoIndex[prefix] || 0;
    const viewport = document.getElementById(`${prefix}_slider_viewport`);
    const imgEl = document.getElementById(`${prefix}_slider_img`);
    const placeholder = document.getElementById(`${prefix}_slider_placeholder`);
    const counterBadge = document.getElementById(`${prefix}_slider_counter`);
    const prevBtn = document.getElementById(`${prefix}_slider_btn_prev`);
    const nextBtn = document.getElementById(`${prefix}_slider_btn_next`);
    const countInd = document.getElementById(`${prefix}_photo_count_indicator`);
    const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
    const deleteBtn = document.getElementById(`${prefix}_btn_delete_photo`);

    if (countInd) countInd.textContent = `${photos.length} ${photos.length === 1 ? 'foto' : 'fotos'}`;

    if (photos.length === 0) {
        if (imgEl) { imgEl.style.display = 'none'; imgEl.src = ''; }
        if (placeholder) placeholder.style.display = 'flex';
        if (counterBadge) counterBadge.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (thumbsStrip) { thumbsStrip.style.display = 'none'; thumbsStrip.innerHTML = ''; }
        if (deleteBtn) deleteBtn.style.display = 'none';
        return;
    }

    if (placeholder) placeholder.style.display = 'none';
    if (imgEl) {
        imgEl.style.display = 'block';
        imgEl.src = photos[idx];
    }

    if (counterBadge) {
        counterBadge.style.display = 'block';
        counterBadge.textContent = `${idx + 1} / ${photos.length}`;
    }

    if (prevBtn) prevBtn.style.display = photos.length > 1 ? 'grid' : 'none';
    if (nextBtn) nextBtn.style.display = photos.length > 1 ? 'grid' : 'none';
    if (deleteBtn) {
        const canDelete = (prefix === 'create') || (prefix === 'edit' && isEditUnlocked);
        deleteBtn.style.display = canDelete ? 'inline-flex' : 'none';
    }

    if (thumbsStrip) {
        thumbsStrip.style.display = photos.length > 1 ? 'flex' : 'none';
        thumbsStrip.innerHTML = '';
        photos.forEach((url, i) => {
            const thumb = document.createElement('div');
            thumb.className = `slider-thumb-item ${i === idx ? 'active' : ''}`;
            thumb.innerHTML = `<img src="${url}" alt="Foto ${i+1}">`;
            thumb.onclick = () => {
                modalPhotoIndex[prefix] = i;
                renderPhotoSlider(prefix);
            };
            thumbsStrip.appendChild(thumb);
        });
    }
}

function slidePhotoNav(prefix, direction) {
    const photos = modalPhotos[prefix] || [];
    if (photos.length === 0) return;
    let newIdx = modalPhotoIndex[prefix] + direction;
    if (newIdx < 0) newIdx = photos.length - 1;
    if (newIdx >= photos.length) newIdx = 0;
    modalPhotoIndex[prefix] = newIdx;
    renderPhotoSlider(prefix);
}

function handleMultipleImagesSelected(input, prefix) {
    if (!input || !input.files || input.files.length === 0) return;
    const files = Array.from(input.files);
    files.forEach(file => {
        const url = URL.createObjectURL(file);
        modalPhotos[prefix].push(url);
    });
    modalPhotoIndex[prefix] = modalPhotos[prefix].length - 1;
    syncEditRemainingImages();
    renderPhotoSlider(prefix);
}

function deleteActivePhoto(prefix) {
    const photos = modalPhotos[prefix] || [];
    if (photos.length === 0) return;
    const idx = modalPhotoIndex[prefix];
    photos.splice(idx, 1);
    if (modalPhotoIndex[prefix] >= photos.length) {
        modalPhotoIndex[prefix] = Math.max(0, photos.length - 1);
    }
    syncEditRemainingImages();
    renderPhotoSlider(prefix);
}

function syncEditRemainingImages() {
    const remainingInput = document.getElementById('edit_remaining_images');
    if (remainingInput) {
        const photos = modalPhotos.edit || [];
        const originalUrls = photos.filter(p => typeof p === 'string' && !p.startsWith('blob:'));
        remainingInput.value = JSON.stringify(originalUrls);
    }
}

/* ==========================================================================
   MAPAS LEAFLET Y GEORREFERENCIACIÓN
   ========================================================================== */
function utmToLatLng(easting, northing, zoneStr = '19K') {
    const zone = parseInt(zoneStr) || 19;
    const isSouth = true;
    const x = parseFloat(easting) - 500000.0;
    const y = isSouth ? parseFloat(northing) - 10000000.0 : parseFloat(northing);
    const k0 = 0.9996;
    const a = 6378137.0;
    const eccSquared = 0.00669438;
    const e1 = (1 - Math.sqrt(1 - eccSquared)) / (1 + Math.sqrt(1 - eccSquared));
    const M = y / k0;
    const mu = M / (a * (1 - eccSquared / 4 - 3 * Math.pow(eccSquared, 2) / 64 - 5 * Math.pow(eccSquared, 3) / 256));
    const phi1Rad = mu + (3 * e1 / 2 - 27 * Math.pow(e1, 3) / 32) * Math.sin(2 * mu) + (21 * Math.pow(e1, 2) / 16 - 55 * Math.pow(e1, 4) / 32) * Math.sin(4 * mu) + (151 * Math.pow(e1, 3) / 96) * Math.sin(6 * mu);
    const N1 = a / Math.sqrt(1 - eccSquared * Math.pow(Math.sin(phi1Rad), 2));
    const T1 = Math.pow(Math.tan(phi1Rad), 2);
    const C1 = eccSquared / (1 - eccSquared) * Math.pow(Math.cos(phi1Rad), 2);
    const R1 = a * (1 - eccSquared) / Math.pow(1 - eccSquared * Math.pow(Math.sin(phi1Rad), 2), 1.5);
    const D = x / (N1 * k0);
    let lat = phi1Rad - (N1 * Math.tan(phi1Rad) / R1) * (D * D / 2 - (5 + 3 * T1 + 10 * C1 - 4 * C1 * C1 - 9 * (eccSquared / (1 - eccSquared))) * Math.pow(D, 4) / 24);
    lat = lat * 180 / Math.PI;
    let lng = (D - (1 + 2 * T1 + C1) * Math.pow(D, 3) / 6 + (5 - 2 * C1 + 28 * T1 - 3 * C1 * C1 + 8 * (eccSquared / (1 - eccSquared)) + 24 * T1 * T1) * Math.pow(D, 5) / 120) / Math.cos(phi1Rad);
    const longOrigin = (zone - 1) * 6 - 180 + 3;
    lng = longOrigin + (lng * 180 / Math.PI);
    return { lat: parseFloat(lat.toFixed(7)), lng: parseFloat(lng.toFixed(7)) };
}

function latLngToUtm(lat, lng) {
    const a = 6378137.0;
    const eccSquared = 0.00669438;
    const k0 = 0.9996;
    const latRad = lat * Math.PI / 180.0;
    const lngRad = lng * Math.PI / 180.0;
    let zoneNumber = Math.floor((lng + 180) / 6) + 1;
    const longOrigin = (zoneNumber - 1) * 6 - 180 + 3;
    const longOriginRad = longOrigin * Math.PI / 180.0;
    const eccPrimeSquared = (eccSquared) / (1 - eccSquared);
    const N = a / Math.sqrt(1 - eccSquared * Math.sin(latRad) * Math.sin(latRad));
    const T = Math.tan(latRad) * Math.tan(latRad);
    const C = eccPrimeSquared * Math.cos(latRad) * Math.cos(latRad);
    const A = Math.cos(latRad) * (lngRad - longOriginRad);
    const M = a * ((1 - eccSquared / 4 - 3 * eccSquared * eccSquared / 64 - 5 * eccSquared * eccSquared * eccSquared / 256) * latRad - (3 * eccSquared / 8 + 3 * eccSquared * eccSquared / 32 + 45 * eccSquared * eccSquared * eccSquared / 1024) * Math.sin(2 * latRad) + (15 * eccSquared * eccSquared / 256 + 45 * eccSquared * eccSquared * eccSquared / 1024) * Math.sin(4 * latRad) - (35 * eccSquared * eccSquared * eccSquared / 3072) * Math.sin(6 * latRad));
    let utmEasting = (k0 * N * (A + (1 - T + C) * A * A * A / 6 + (5 - 18 * T + T * T + 72 * C - 58 * eccPrimeSquared) * A * A * A * A * A / 120) + 500000.0);
    let utmNorthing = (k0 * (M + N * Math.tan(latRad) * (A * A / 2 + (5 - T + 9 * C + 4 * C * C) * A * A * A * A / 24 + (61 - 58 * T + T * T + 600 * C - 330 * eccPrimeSquared) * A * A * A * A * A * A / 720)));
    if (lat < 0) utmNorthing += 10000000.0;
    return { easting: parseFloat(utmEasting.toFixed(3)), northing: parseFloat(utmNorthing.toFixed(3)), zone: `${zoneNumber}K` };
}

function initModalMiniMap(prefix, lat, lng) {
    const containerId = `${prefix}_modal_map`;
    const container = document.getElementById(containerId);
    if (!container) return;

    const initialLat = !isNaN(lat) && lat !== null ? lat : -16.5034;
    const initialLng = !isNaN(lng) && lng !== null ? lng : -68.1324;

    if (prefix === 'create') {
        if (!createMiniMap) {
            createMiniMap = L.map(containerId, { zoomControl: true, attributionControl: false }).setView([initialLat, initialLng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(createMiniMap);
            createMiniMarker = L.marker([initialLat, initialLng], { draggable: true }).addTo(createMiniMap);
            createMiniMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateUtmFromMap('create', pos.lat, pos.lng);
            });
            createMiniMap.on('click', function (e) {
                createMiniMarker.setLatLng(e.latlng);
                updateUtmFromMap('create', e.latlng.lat, e.latlng.lng);
            });
        } else {
            createMiniMap.setView([initialLat, initialLng], 16);
            createMiniMarker.setLatLng([initialLat, initialLng]);
            createMiniMap.invalidateSize();
        }
    } else {
        if (!editMiniMap) {
            editMiniMap = L.map(containerId, { zoomControl: true, attributionControl: false }).setView([initialLat, initialLng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(editMiniMap);
            editMiniMarker = L.marker([initialLat, initialLng], { draggable: false }).addTo(editMiniMap);
            editMiniMarker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateUtmFromMap('edit', pos.lat, pos.lng);
            });
            editMiniMap.on('click', function (e) {
                if (isEditUnlocked) {
                    editMiniMarker.setLatLng(e.latlng);
                    updateUtmFromMap('edit', e.latlng.lat, e.latlng.lng);
                }
            });
        } else {
            editMiniMap.setView([initialLat, initialLng], 16);
            editMiniMarker.setLatLng([initialLat, initialLng]);
            editMiniMap.invalidateSize();
        }
    }
}

function updateUtmFromMap(prefix, lat, lng) {
    const u = latLngToUtm(lat, lng);
    const eInput = document.getElementById(`${prefix}_utm_easting`);
    const nInput = document.getElementById(`${prefix}_utm_northing`);
    const zInput = document.getElementById(`${prefix}_utm_zone`);
    const disp = document.getElementById(`${prefix}_utm_display`);
    const locInput = document.getElementById(`${prefix}_location`);
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);

    if (eInput) eInput.value = u.easting.toFixed(3);
    if (nInput) nInput.value = u.northing.toFixed(3);
    if (zInput) zInput.value = u.zone;
    const formatted = `E: ${u.easting.toFixed(3)}, N: ${u.northing.toFixed(3)}, Z: ${u.zone}`;
    if (disp) disp.textContent = formatted;
    if (locInput) locInput.value = formatted;
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);
}

function syncUtmToMap(prefix) {
    const easting = parseFloat(document.getElementById(`${prefix}_utm_easting`)?.value || 0);
    const northing = parseFloat(document.getElementById(`${prefix}_utm_northing`)?.value || 0);
    const zone = document.getElementById(`${prefix}_utm_zone`)?.value || '19K';

    if (easting > 100000 && northing > 1000000) {
        const pos = utmToLatLng(easting, northing, zone);
        const latInput = document.getElementById(`${prefix}_latitude`);
        const lngInput = document.getElementById(`${prefix}_longitude`);
        const locInput = document.getElementById(`${prefix}_location`);
        const disp = document.getElementById(`${prefix}_utm_display`);

        if (latInput) latInput.value = pos.lat;
        if (lngInput) lngInput.value = pos.lng;
        const formatted = `E: ${easting.toFixed(3)}, N: ${northing.toFixed(3)}, Z: ${zone}`;
        if (disp) disp.textContent = formatted;
        if (locInput) locInput.value = formatted;

        if (prefix === 'create' && createMiniMap && createMiniMarker) {
            createMiniMap.setView([pos.lat, pos.lng], 16);
            createMiniMarker.setLatLng([pos.lat, pos.lng]);
        } else if (prefix === 'edit' && editMiniMap && editMiniMarker) {
            editMiniMap.setView([pos.lat, pos.lng], 16);
            editMiniMarker.setLatLng([pos.lat, pos.lng]);
        }
    }
}

function getCurrentGpsPosition(latFieldId, lngFieldId, prefix) {
    if (!navigator.geolocation) {
        alert('La geolocalización no está soportada en este navegador.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        pos => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateUtmFromMap(prefix, lat, lng);
            if (prefix === 'create' && createMiniMap && createMiniMarker) {
                createMiniMap.setView([lat, lng], 17);
                createMiniMarker.setLatLng([lat, lng]);
            } else if (prefix === 'edit' && editMiniMap && editMiniMarker) {
                editMiniMap.setView([lat, lng], 17);
                editMiniMarker.setLatLng([lat, lng]);
            }
        },
        err => {
            alert('No se pudo obtener la posición GPS. Asegúrate de otorgar permisos de ubicación.');
        },
        { enableHighAccuracy: true, timeout: 10000 }
    );
}

/* ==========================================================================
   MODALES: ABRIR / CERRAR Y CONTROL DE MODO DE EDICIÓN
   ========================================================================== */
function openCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;
    modalPhotos.create = [];
    modalPhotoIndex.create = 0;
    renderPhotoSlider('create');
    calcContaminantes('create');
    modal.classList.add('open');
    setTimeout(() => {
        initModalMiniMap('create', -16.5034, -68.1324);
        syncUtmToMap('create');
    }, 150);
}

function closeCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) modal.classList.remove('open');
}

function openViewMeasurementModal(item) {
    const form = document.getElementById('editMeasurementForm');
    if (!form) return;

    form.action = `/modulos/${MODULE_ID}/contaminantes-quimicos/mediciones/${item.id}`;

    document.getElementById('edit_point_number').value = item.num || '';
    document.getElementById('edit_pt_num_disp').textContent = item.num || '01';

    // Personal registrador
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

    // Datos del formulario
    document.getElementById('edit_measurement_date').value = item.raw_date || '';
    document.getElementById('edit_measurement_time').value = item.time !== '—' ? item.time : '';
    document.getElementById('edit_area').value = item.area || '';
    document.getElementById('edit_punto_medicion').value = item.punto_medicion || '';
    document.getElementById('edit_trabajador_nombre').value = item.trabajador_nombre !== '—' ? item.trabajador_nombre : '';
    document.getElementById('edit_masa_inicial_filtro_mg').value = item.masa_inicial_filtro_mg || '';
    document.getElementById('edit_masa_final_filtro_mg').value = item.masa_final_filtro_mg || '';
    document.getElementById('edit_hora_inicio').value = item.hora_inicio || '08:00';
    document.getElementById('edit_hora_final').value = item.hora_final || '12:00';
    document.getElementById('edit_q_inicial_lmin').value = item.q_inicial_lmin || '';
    document.getElementById('edit_q_final_lmin').value = item.q_final_lmin || '';
    document.getElementById('edit_t_inicial_c').value = item.t_inicial_c || '';
    document.getElementById('edit_presion_hpa').value = item.presion_hpa || '';
    document.getElementById('edit_observations').value = item.observations || '';

    // Carrusel fotográfico
    modalPhotos.edit = [];
    if (item.images && Array.isArray(item.images) && item.images.length > 0) {
        modalPhotos.edit = [...item.images];
    } else if (item.image_path) {
        modalPhotos.edit = [item.image_path];
    }
    modalPhotoIndex.edit = 0;
    syncEditRemainingImages();
    renderPhotoSlider('edit');

    // Cálculos
    calcContaminantes('edit');

    // Coordenadas
    let lat = (item.latitude !== null && item.latitude !== '') ? parseFloat(item.latitude) : NaN;
    let lng = (item.longitude !== null && item.longitude !== '') ? parseFloat(item.longitude) : NaN;
    let easting = item.utm_easting || 592450.0;
    let northing = item.utm_northing || 8175320.0;
    let zone = item.utm_zone || '19K';

    const eInput = document.getElementById('edit_utm_easting');
    const nInput = document.getElementById('edit_utm_northing');
    const zInput = document.getElementById('edit_utm_zone');
    const disp = document.getElementById('edit_utm_display');
    const locInput = document.getElementById('edit_location');
    const latInput = document.getElementById('edit_latitude');
    const lngInput = document.getElementById('edit_longitude');

    if (eInput) eInput.value = easting;
    if (nInput) nInput.value = northing;
    if (zInput) zInput.value = zone;
    const formatted = `E: ${parseFloat(easting).toFixed(3)}, N: ${parseFloat(northing).toFixed(3)}, Z: ${zone}`;
    if (disp) disp.textContent = formatted;
    if (locInput) locInput.value = formatted;
    if (latInput) latInput.value = isNaN(lat) ? '' : lat;
    if (lngInput) lngInput.value = isNaN(lng) ? '' : lng;

    applyEditModeState(false);

    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.add('open');
        setTimeout(() => {
            initModalMiniMap('edit', isNaN(lat) ? -16.5034 : lat, isNaN(lng) ? -68.1324 : lng);
        }, 150);
    }
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

function applyEditModeState(isEditing) {
    isEditUnlocked = isEditing;
    const form = document.getElementById('editMeasurementForm');
    const badge = document.getElementById('modalModeStatusBadge');
    const btn = document.getElementById('btnToggleEditMode');
    const btnText = document.getElementById('btnToggleEditModeText');
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    const gpsBtn = document.getElementById('edit_btn_gps');

    if (form) {
        if (isEditing) form.classList.remove('modal-view-mode');
        else form.classList.add('modal-view-mode');
    }

    if (badge) {
        badge.textContent = isEditing ? 'Modo Edición' : 'Solo Lectura';
        badge.style.background = isEditing ? '#fef3c7' : '#e0f2fe';
        badge.style.color = isEditing ? '#b45309' : '#0284c7';
        badge.style.borderColor = isEditing ? '#fde68a' : '#bae6fd';
    }

    if (btn) {
        if (isEditing) {
            btn.classList.add('active-editing');
            if (btnText) btnText.textContent = 'Bloquear';
        } else {
            btn.classList.remove('active-editing');
            if (btnText) btnText.textContent = 'Editar';
        }
    }

    const fieldsToToggle = [
        'edit_measurement_date',
        'edit_measurement_time',
        'edit_staff_id',
        'edit_area',
        'edit_punto_medicion',
        'edit_trabajador_nombre',
        'edit_masa_inicial_filtro_mg',
        'edit_masa_final_filtro_mg',
        'edit_hora_inicio',
        'edit_hora_final',
        'edit_q_inicial_lmin',
        'edit_q_final_lmin',
        'edit_t_inicial_c',
        'edit_presion_hpa',
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

    if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';
    if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'inline-flex' : 'none';
    if (gpsBtn) gpsBtn.style.display = isEditing ? 'inline-flex' : 'none';

    if (editMiniMarker && editMiniMarker.dragging) {
        if (isEditing) editMiniMarker.dragging.enable();
        else editMiniMarker.dragging.disable();
    }

    renderPhotoSlider('edit');
}

function toggleModalEditMode() {
    applyEditModeState(!isEditUnlocked);
}

function openPhotoViewer(url, title) {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const t = document.getElementById('photoViewerTitle');
    if (img) img.src = url;
    if (t) t.textContent = title || 'Fotografía del Punto';
    if (modal) modal.classList.add('open');
}

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.classList.remove('open');
}

function openExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.add('open');
}

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal');
    if (modal) modal.classList.remove('open');
}

function openNormativeTablesModal() {
    const modal = document.getElementById('contaminantesTablesModal');
    if (modal) modal.classList.add('open');
}

function closeNormativeTablesModal() {
    const modal = document.getElementById('contaminantesTablesModal');
    if (modal) modal.classList.remove('open');
}

function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');
    setTimeout(() => {
        initAllLocationsMap();
    }, 150);
}

function closeAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) modal.classList.remove('open');
}

function initAllLocationsMap() {
    const container = document.getElementById('allLocationsMapLeaflet');
    if (!container) return;

    if (!allLocMap) {
        allLocMap = L.map('allLocationsMapLeaflet', { zoomControl: true }).setView([-16.5034, -68.1324], 14);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(allLocMap);
    }

    allLocMarkers.forEach(m => allLocMap.removeLayer(m));
    allLocMarkers = [];

    const bounds = [];
    ALL_MEASUREMENTS_DATA.forEach((item, idx) => {
        let lat = item.latitude;
        let lng = item.longitude;
        if (!lat || !lng) {
            const pos = utmToLatLng(item.utm_easting || 592450, item.utm_northing || 8175320, item.utm_zone || '19K');
            lat = pos.lat;
            lng = pos.lng;
        }
        if (!isNaN(lat) && !isNaN(lng)) {
            const marker = L.circleMarker([lat, lng], {
                radius: 8,
                fillColor: '#0284c7',
                color: '#ffffff',
                weight: 2,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(allLocMap);

            marker.bindPopup(`
                <div style="font-family: 'Outfit', sans-serif; min-width: 180px;">
                    <div style="font-weight: 800; color: #0284c7; font-size: 13px; margin-bottom: 2px;">#${item.num} — ${item.punto_medicion}</div>
                    <div style="font-size: 11.5px; color: #475569; margin-bottom: 4px;">${item.area}</div>
                    <div style="font-size: 11px; font-weight: 700; color: #0f172a;">Vol: ${item.volumen_m3} m³</div>
                    <div style="font-size: 11px; color: #64748b; margin-top: 4px; border-top: 1px solid #e2e8f0; padding-top: 4px;">Registrado por: <strong>${item.registered_by}</strong></div>
                </div>
            `);
            allLocMarkers.push(marker);
            bounds.push([lat, lng]);
        }
    });

    if (bounds.length > 0) {
        allLocMap.fitBounds(bounds, { padding: [30, 30] });
    }
    allLocMap.invalidateSize();
}

function focusPointOnAllLocationsMap(idx) {
    const item = ALL_MEASUREMENTS_DATA[idx];
    if (!item || !allLocMap || !allLocMarkers[idx]) return;
    const marker = allLocMarkers[idx];
    allLocMap.setView(marker.getLatLng(), 17);
    marker.openPopup();
}

function confirmDeleteMeasurement(id, pointNum) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `¿Eliminar Punto #${pointNum}?`,
            text: 'Esta acción no se puede deshacer.',
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
                    form.action = `/modulos/${MODULE_ID}/contaminantes-quimicos/mediciones/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Eliminar punto #${pointNum}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = `/modulos/${MODULE_ID}/contaminantes-quimicos/mediciones/${id}`;
                form.submit();
            }
        }
    }
}

/* ==========================================================================
   EXPORTACIÓN DE PLANILLA TÉCNICA EXCEL (.XLSX)
   ========================================================================== */
async function downloadExcelPlanilla() {
    if (typeof ExcelJS === 'undefined') {
        alert('La librería ExcelJS se está cargando. Intente de nuevo en un segundo.');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Metric v2 Pachabol';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Contaminantes Químicos', {
        pageSetup: { orientation: 'landscape', paperSize: 9 }
    });

    // Encabezado
    sheet.mergeCells('A1:L1');
    const titleCell = sheet.getCell('A1');
    titleCell.value = 'REGISTRO Y EVALUACIÓN DE CONTAMINANTES QUÍMICOS — PACHABOL';
    titleCell.font = { name: 'Arial', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0284C7' } };
    titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
    sheet.getRow(1).height = 30;

    // Encabezados de Columnas
    const headers = [
        'N°', 'Fecha', 'Hora', 'Área / Sección', 'Punto de Muestreo', 'Trabajador',
        'Masa Inicial (mg)', 'Masa Final (mg)', 'Masa Neta (mg)', 'Caudal Prom (L/min)',
        'Volumen (m³)', 'Concentración (mg/m³)', 'Observaciones', 'Registrado Por'
    ];

    sheet.addRow(headers);
    const headerRow = sheet.getRow(2);
    headerRow.font = { name: 'Arial', size: 10, bold: true, color: { argb: 'FFFFFFFF' } };
    headerRow.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0F172A' } };
    headerRow.alignment = { horizontal: 'center', vertical: 'middle' };
    headerRow.height = 24;

    // Datos
    ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
        sheet.addRow([
            m.num || (idx + 1),
            m.date || '',
            m.time || '',
            m.area || '',
            m.punto_medicion || '',
            m.trabajador_nombre || '',
            m.masa_inicial_filtro_mg || 0,
            m.masa_final_filtro_mg || 0,
            m.masa_neta_mg || 0,
            m.q_prom_lmin || 0,
            m.volumen_m3 || 0,
            m.concentracion_mg_m3 || 0,
            m.observations || '',
            m.registered_by || ''
        ]);
    });

    // Autoajuste de columnas
    sheet.columns.forEach(col => {
        let maxLen = 12;
        col.eachCell({ includeEmpty: true }, cell => {
            const val = cell.value ? cell.value.toString() : '';
            if (val.length > maxLen) maxLen = Math.min(val.length + 3, 35);
        });
        col.width = maxLen;
    });

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Contaminantes_Quimicos_${MODULE_ID}_${new Date().toISOString().slice(0,10)}.xlsx`;
    link.click();
    closeExportModal();
}

function openPhotoReportDirectly() {
    alert('Función de visualización fotográfica disponible en el botón de imágenes de cada punto.');
    closeExportModal();
}

// 4. Inicialización al Cargar el DOM
document.addEventListener('DOMContentLoaded', () => {
    filteredRows = getTableDataRows();
    applyPagination();
});

// 5. Exportar Funciones al Objeto Window Global
window.searchContaminantesLive = searchContaminantesLive;
window.autoSaveHeaderField = autoSaveHeaderField;
window.calcContaminantes = calcContaminantes;
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.toggleModalEditMode = toggleModalEditMode;
window.slidePhotoNav = slidePhotoNav;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.deleteActivePhoto = deleteActivePhoto;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewer = closePhotoViewer;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openNormativeTablesModal = openNormativeTablesModal;
window.closeNormativeTablesModal = closeNormativeTablesModal;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.downloadExcelPlanilla = downloadExcelPlanilla;
window.openPhotoReportDirectly = openPhotoReportDirectly;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.syncUtmToMap = syncUtmToMap;
