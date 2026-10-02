/**
 * MONITOREO DE OPACIDAD (VEHICULAR / HUMOS) — INTERACTION LOGIC
 * Pachabol Systems Metric v2
 */

// Global state
let opacityMeasurements = window.ALL_MEASUREMENTS_DATA || [];
let allLocationsMap = null;
let allLocationsMarkers = [];
let createModalMap = null;
let createModalMarker = null;
let editModalMap = null;
let editModalMarker = null;
let isEditUnlocked = false;
let isOptimizingPhotos = false;

const modalPhotos = {
    create: [],
    edit: []
};
const modalPhotoIndex = {
    create: 0,
    edit: 0
};

// =========================================================================
// 1. COORDENADAS UTM (WGS84) Y CONVERSIONES
// =========================================================================
function latLngToUtm(lat, lng) {
    const a = 6378137;
    const f = 1 / 298.257223563;
    const b = a * (1 - f);
    const e = Math.sqrt((a * a - b * b) / (a * a));
    const ePrime = Math.sqrt((a * a - b * b) / (b * b));
    const k0 = 0.9996;

    const latRad = (lat * Math.PI) / 180;
    const lngRad = (lng * Math.PI) / 180;

    let zoneNum = Math.floor((lng + 180) / 6) + 1;
    if (zoneNum > 60) zoneNum = 60;
    if (zoneNum < 1) zoneNum = 1;

    const letters = "CDEFGHJKLMNPQRSTUVWX";
    let zoneLetter = 'K';
    if (lat >= -80 && lat <= 84) {
        const bandIndex = Math.min(letters.length - 1, Math.max(0, Math.floor((lat + 80) / 8)));
        zoneLetter = letters[bandIndex];
    }

    const lon0 = (zoneNum - 1) * 6 - 180 + 3;
    const lon0Rad = (lon0 * Math.PI) / 180;

    const e2 = e * e;
    const sinLat = Math.sin(latRad);
    const cosLat = Math.cos(latRad);
    const tanLat = Math.tan(latRad);

    const n = a / Math.sqrt(1 - e2 * sinLat * sinLat);
    const t = tanLat * tanLat;
    const c = ePrime * ePrime * cosLat * cosLat;
    const A = (lngRad - lon0Rad) * cosLat;

    const m =
        a *
        ((1 - e2 / 4 - (3 * Math.pow(e2, 2)) / 64 - (5 * Math.pow(e2, 3)) / 256) * latRad -
            ((3 * e2) / 8 + (3 * Math.pow(e2, 2)) / 32 + (45 * Math.pow(e2, 3)) / 1024) * Math.sin(2 * latRad) +
            ((15 * Math.pow(e2, 2)) / 256 + (45 * Math.pow(e2, 3)) / 1024) * Math.sin(4 * latRad) -
            ((35 * Math.pow(e2, 3)) / 3072) * Math.sin(6 * latRad));

    const A2 = A * A;
    const A3 = A2 * A;
    const A4 = A2 * A2;
    const A5 = A4 * A;
    const A6 = A3 * A3;

    let easting =
        k0 *
            n *
            (A +
                ((1 - t + c) * A3) / 6 +
                ((5 - 18 * t + t * t + 72 * c - 58 * ePrime * ePrime) * A5) / 120) +
        500000;

    let northing =
        k0 *
        (m +
            n *
                tanLat *
                (A2 / 2 +
                    ((5 - t + 9 * c + 4 * c * c) * A4) / 24 +
                    ((61 - 58 * t + t * t + 600 * c - 330 * ePrime * ePrime) * A6) / 720));

    if (lat < 0) {
        northing += 10000000; // Hemisferio Sur
    }

    const zone = `${zoneNum}${zoneLetter}`;
    const formatted = `E: ${easting.toFixed(1)}, N: ${northing.toFixed(1)}, Z: ${zone}`;

    return { easting, northing, zoneNum, zoneLetter, zone, formatted };
}

function utmToLatLng(easting, northing, zoneStr) {
    let zoneNum = 19;
    let zoneLetter = 'K';
    if (typeof zoneStr === 'string') {
        const m = zoneStr.match(/(\d+)\s*([A-Za-z]?)/);
        if (m) {
            zoneNum = parseInt(m[1], 10) || 19;
            zoneLetter = (m[2] || 'K').toUpperCase();
        }
    } else if (typeof zoneStr === 'number') {
        zoneNum = zoneStr;
    }

    const a = 6378137;
    const f = 1 / 298.257223563;
    const b = a * (1 - f);
    const e = Math.sqrt((a * a - b * b) / (a * a));
    const ePrime = Math.sqrt((a * a - b * b) / (b * b));
    const k0 = 0.9996;

    const isSouth = zoneLetter ? zoneLetter < 'N' : true;
    const x = easting - 500000;
    const y = isSouth ? northing - 10000000 : northing;

    const m = y / k0;
    const e2 = e * e;
    const e4 = e2 * e2;
    const e6 = e4 * e2;
    const e1 = (1 - Math.sqrt(1 - e2)) / (1 + Math.sqrt(1 - e2));

    const mu = m / (a * (1 - e2 / 4 - (3 * e4) / 64 - (5 * e6) / 256));

    const phi1 =
        mu +
        ((3 * e1) / 2 - (27 * Math.pow(e1, 3)) / 32) * Math.sin(2 * mu) +
        ((21 * e1 * e1) / 16 - (55 * Math.pow(e1, 4)) / 32) * Math.sin(4 * mu) +
        ((151 * Math.pow(e1, 3)) / 96) * Math.sin(6 * mu) +
        ((1097 * Math.pow(e1, 4)) / 512) * Math.sin(8 * mu);

    const sinPhi1 = Math.sin(phi1);
    const cosPhi1 = Math.cos(phi1);
    const tanPhi1 = Math.tan(phi1);

    const n1 = a / Math.sqrt(1 - e2 * sinPhi1 * sinPhi1);
    const t1 = tanPhi1 * tanPhi1;
    const c1 = ePrime * ePrime * cosPhi1 * cosPhi1;
    const r1 = (a * (1 - e2)) / Math.pow(1 - e2 * sinPhi1 * sinPhi1, 1.5);
    const d = x / (n1 * k0);

    const d2 = d * d;
    const d3 = d2 * d;
    const d4 = d2 * d2;
    const d5 = d4 * d;
    const d6 = d3 * d3;

    const lat =
        phi1 -
        ((n1 * tanPhi1) / r1) *
            (d2 / 2 -
                ((5 + 3 * t1 + 10 * c1 - 4 * c1 * c1 - 9 * ePrime * ePrime) * d4) / 24 +
                ((61 + 90 * t1 + 298 * c1 + 45 * t1 * t1 - 252 * ePrime * ePrime - 3 * c1 * c1) * d6) / 720);

    const lon0 = (zoneNum - 1) * 6 - 180 + 3;
    const lng =
        (lon0 * Math.PI) / 180 +
        (d -
            ((1 + 2 * t1 + c1) * d3) / 6 +
            ((5 - 2 * c1 + 28 * t1 - 3 * c1 * c1 + 8 * ePrime * ePrime + 24 * t1 * t1) * d5) / 120) /
            cosPhi1;

    return {
        lat: (lat * 180) / Math.PI,
        lng: (lng * 180) / Math.PI
    };
}

function updateUtmFromLatLng(prefix, lat, lng) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);
    if (isNaN(lat) || isNaN(lng)) return;

    const utm = latLngToUtm(lat, lng);
    const eInput = document.getElementById(`${prefix}_utm_easting`);
    const nInput = document.getElementById(`${prefix}_utm_northing`);
    const zInput = document.getElementById(`${prefix}_utm_zone`);
    const disp = document.getElementById(`${prefix}_utm_display`);
    const locInput = document.getElementById(`${prefix}_location_description`);
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);

    if (eInput) eInput.value = utm.easting.toFixed(1);
    if (nInput) nInput.value = utm.northing.toFixed(1);
    if (zInput) zInput.value = utm.zone;
    if (disp) disp.textContent = utm.formatted;
    if (locInput) locInput.value = utm.formatted;
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);
}

window.syncUtmToMap = function (prefix) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    const eInput = document.getElementById(`${prefix}_utm_easting`);
    const nInput = document.getElementById(`${prefix}_utm_northing`);
    const zInput = document.getElementById(`${prefix}_utm_zone`);
    if (!eInput || !nInput) return;

    const eVal = parseFloat(eInput.value);
    const nVal = parseFloat(nInput.value);
    const zVal = (zInput ? zInput.value.trim() : '') || '19K';

    if (isNaN(eVal) || isNaN(nVal)) return;

    const pos = utmToLatLng(eVal, nVal, zVal);
    if (isNaN(pos.lat) || isNaN(pos.lng)) return;

    const formatted = `E: ${eVal.toFixed(1)}, N: ${nVal.toFixed(1)}, Z: ${zVal.toUpperCase()}`;
    const disp = document.getElementById(`${prefix}_utm_display`);
    if (disp) disp.textContent = formatted;

    const locInput = document.getElementById(`${prefix}_location_description`);
    if (locInput) locInput.value = formatted;

    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);
    if (latInput) latInput.value = pos.lat.toFixed(7);
    if (lngInput) lngInput.value = pos.lng.toFixed(7);

    if (prefix === 'create' && createModalMarker) {
        createModalMarker.setLatLng([pos.lat, pos.lng]);
        if (createModalMap) createModalMap.panTo([pos.lat, pos.lng]);
    } else if (prefix === 'edit' && editModalMarker) {
        editModalMarker.setLatLng([pos.lat, pos.lng]);
        if (editModalMap) editModalMap.panTo([pos.lat, pos.lng]);
    }
};

function initModalMiniMap(prefix, lat, lng) {
    const mapContainerId = `${prefix}_modal_map`;
    const container = document.getElementById(mapContainerId);
    if (!container || typeof L === 'undefined') return;

    lat = parseFloat(lat) || -16.503412;
    lng = parseFloat(lng) || -68.132456;

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

// =========================================================================
// 2. VISOR Y SLIDER DE FOTOGRAFÍAS
// =========================================================================
window.openPhotoViewer = function (photoUrl, title) {
    window.openPhotoViewerModal(photoUrl, title);
};

window.closePhotoViewer = function () {
    window.closePhotoViewerModal();
};

window.openPhotoViewerModal = function (photoUrl, title) {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const t = document.getElementById('photoViewerTitle');
    if (modal && img) {
        img.src = photoUrl;
        if (t) t.textContent = title || 'Evidencia Fotográfica del Vehículo';
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closePhotoViewerModal = function () {
    const modal = document.getElementById('photoViewerModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.renderPhotoSlider = function (prefix) {
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
    const countIndicator = document.getElementById(`${prefix}_photo_count_indicator`);
    const delBtn = document.getElementById(`${prefix}_btn_delete_photo`);

    if (countIndicator) {
        countIndicator.textContent = count === 1 ? '1 foto' : `${count} fotos`;
    }

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
        img.onclick = () => window.openPhotoViewer(photos[idx], `Fotografía ${idx + 1} de ${count}`);
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
};

window.slidePhotoNav = function (prefix, direction) {
    const photos = modalPhotos[prefix] || [];
    if (photos.length <= 1) return;
    let idx = modalPhotoIndex[prefix] + direction;
    if (idx < 0) idx = photos.length - 1;
    if (idx >= photos.length) idx = 0;
    modalPhotoIndex[prefix] = idx;
    window.renderPhotoSlider(prefix);
};

window.selectSlidePhoto = function (prefix, index) {
    modalPhotoIndex[prefix] = index;
    window.renderPhotoSlider(prefix);
};

window.deleteActivePhoto = function (prefix, targetIndex = null) {
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

    window.renderPhotoSlider(prefix);

    if (prefix === 'edit') {
        syncEditRemainingImages();
    }
};

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

async function compressImageFile(file) {
    if (!file.type || !file.type.startsWith('image/') || file.type === 'image/gif' || file.type === 'image/svg+xml') {
        return file;
    }

    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const img = new Image();
            img.onload = () => {
                const width = img.naturalWidth || img.width;
                const height = img.naturalHeight || img.height;
                const maxDim = Math.max(width, height);
                const fileSize = file.size;

                if (fileSize <= 1.2 * 1024 * 1024 && maxDim <= 1920) {
                    return resolve(file);
                }

                let targetMaxDim = 2048;
                let targetQuality = 0.88;

                if (fileSize <= 4 * 1024 * 1024 && maxDim <= 2800) {
                    targetMaxDim = 2560;
                    targetQuality = 0.93;
                } else if (fileSize <= 9 * 1024 * 1024 && maxDim <= 4500) {
                    targetMaxDim = 2200;
                    targetQuality = 0.90;
                }

                let newWidth = width;
                let newHeight = height;

                if (maxDim > targetMaxDim) {
                    if (width >= height) {
                        newWidth = targetMaxDim;
                        newHeight = Math.round((height * targetMaxDim) / width);
                    } else {
                        newHeight = targetMaxDim;
                        newWidth = Math.round((width * targetMaxDim) / height);
                    }
                }

                if (newWidth === width && newHeight === height && fileSize <= 1.5 * 1024 * 1024) {
                    return resolve(file);
                }

                const canvas = document.createElement('canvas');
                canvas.width = newWidth;
                canvas.height = newHeight;
                const ctx = canvas.getContext('2d');
                ctx.imageSmoothingEnabled = true;
                ctx.imageSmoothingQuality = 'high';
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, newWidth, newHeight);
                ctx.drawImage(img, 0, 0, newWidth, newHeight);

                canvas.toBlob((blob) => {
                    if (!blob || blob.size >= fileSize) {
                        resolve(file);
                    } else {
                        const newFileName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                        const optimizedFile = new File([blob], newFileName, {
                            type: 'image/jpeg',
                            lastModified: Date.now()
                        });
                        resolve(optimizedFile);
                    }
                }, 'image/jpeg', targetQuality);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

window.handleMultipleImagesSelected = async function (input, prefix) {
    if (!input.files || input.files.length === 0) return;
    const rawFiles = Array.from(input.files);

    isOptimizingPhotos = true;
    const btnTrigger = document.getElementById(`${prefix}_btn_add_photos`);
    const originalBtnHtml = btnTrigger ? btnTrigger.innerHTML : '';
    if (btnTrigger) {
        btnTrigger.disabled = true;
        btnTrigger.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            <span>Optimizando imágenes...</span>
        `;
    }

    const submitBtn = document.getElementById(`${prefix}_modal_submit_btn`);
    if (submitBtn) submitBtn.disabled = true;

    try {
        if (prefix === 'create') {
            modalPhotos.create = [];
        }

        const optimizedFiles = [];
        for (const file of rawFiles) {
            const opt = await compressImageFile(file);
            optimizedFiles.push(opt);
        }

        try {
            const dt = new DataTransfer();
            optimizedFiles.forEach(f => dt.items.add(f));
            input.files = dt.files;
        } catch (err) {
            console.warn('DataTransfer no soportado', err);
        }

        let loadedCount = 0;
        optimizedFiles.forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                modalPhotos[prefix].push(e.target.result);
                loadedCount++;
                if (loadedCount === optimizedFiles.length) {
                    modalPhotoIndex[prefix] = modalPhotos[prefix].length - 1;
                    window.renderPhotoSlider(prefix);
                }
            };
            reader.readAsDataURL(file);
        });
    } finally {
        isOptimizingPhotos = false;
        if (btnTrigger) {
            btnTrigger.disabled = false;
            btnTrigger.innerHTML = originalBtnHtml;
        }
        if (submitBtn) submitBtn.disabled = false;
    }
};

// =========================================================================
// 3. CONTROL DE MODO CONSULTA (SOLO LECTURA) Y EDICIÓN
// =========================================================================
window.applyEditModeState = function (isEditing) {
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

    const fieldsToToggle = [
        'edit_measurement_date',
        'edit_measurement_time',
        'edit_staff_id',
        'edit_area',
        'edit_altitud',
        'edit_tipo_vehiculo',
        'edit_placa',
        'edit_marca',
        'edit_modelo',
        'edit_temp_c',
        'edit_limite_normativa',
        'edit_opa_1',
        'edit_opa_2',
        'edit_opa_3',
        'edit_rpm_1',
        'edit_rpm_2',
        'edit_rpm_3',
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

    const submitBtn = document.getElementById('edit_modal_submit_btn');
    if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const gpsBtn = document.getElementById('edit_btn_gps');
    if (gpsBtn) gpsBtn.style.display = isEditing ? 'inline-flex' : 'none';

    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'inline-flex' : 'none';

    if (editModalMarker && editModalMarker.dragging) {
        if (isEditing) editModalMarker.dragging.enable();
        else editModalMarker.dragging.disable();
    }

    syncEditRemainingImages();
    window.renderPhotoSlider('edit');
};

window.toggleModalEditMode = function () {
    window.applyEditModeState(!isEditUnlocked);
};

// =========================================================================
// 4. MODALES DE CREACIÓN Y EDICIÓN
// =========================================================================
window.openCreateMeasurementModal = function () {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';

        modalPhotos.create = [];
        modalPhotoIndex.create = 0;
        window.renderPhotoSlider('create');

        const defaultEasting = 218468.0;
        const defaultNorthing = 7627234.0;
        const defaultZone = '19K';
        const eInput = document.getElementById('create_utm_easting');
        const nInput = document.getElementById('create_utm_northing');
        const zInput = document.getElementById('create_utm_zone');
        if (eInput) eInput.value = defaultEasting.toFixed(1);
        if (nInput) nInput.value = defaultNorthing.toFixed(1);
        if (zInput) zInput.value = defaultZone;

        const pos = utmToLatLng(defaultEasting, defaultNorthing, defaultZone);
        const formatted = `E: ${defaultEasting.toFixed(1)}, N: ${defaultNorthing.toFixed(1)}, Z: ${defaultZone}`;
        const disp = document.getElementById('create_utm_display');
        if (disp) disp.textContent = formatted;
        const locInput = document.getElementById('create_location_description');
        if (locInput) locInput.value = formatted;
        const latInput = document.getElementById('create_latitude');
        const lngInput = document.getElementById('create_longitude');
        if (latInput) latInput.value = pos.lat.toFixed(7);
        if (lngInput) lngInput.value = pos.lng.toFixed(7);

        window.recalcOpacidad('create');

        setTimeout(() => {
            initModalMiniMap('create', pos.lat, pos.lng);
        }, 150);
    }
};

window.closeCreateMeasurementModal = function () {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.onAltitudeChange = function (prefix) {
    const altSelect = document.getElementById(`${prefix}_altitud`);
    const limInput = document.getElementById(`${prefix}_limite_normativa`);
    const altText = document.getElementById(`${prefix}_altitud_text`);
    const altLimText = document.getElementById(`${prefix}_altitud_limit_text`);

    const alt = altSelect ? altSelect.value : '1500-3000';
    let limitVal = 2.80;
    if (alt === '0-1500') limitVal = 2.44;
    else if (alt === '1500-3000') limitVal = 2.80;
    else if (alt === '3000-4500') limitVal = 3.22;

    if (limInput) limInput.value = limitVal.toFixed(2);
    if (altText) altText.textContent = `Altitud: ${alt} msnm`;
    if (altLimText) altLimText.textContent = `${limitVal.toFixed(2).replace('.', ',')} m⁻¹`;

    window.recalcOpacidad(prefix);
};

window.openViewMeasurementModal = function (item) {
    const modal = document.getElementById('editMeasurementModal');
    if (!modal) return;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${window.MODULE_ID}/opacidad/mediciones/${item.id}`;
    }

    // Encabezado
    const ptNumDisp = document.getElementById('edit_pt_num_disp');
    const ptNumHidden = document.getElementById('edit_point_number');
    if (ptNumDisp) ptNumDisp.textContent = item.num || '01';
    if (ptNumHidden) ptNumHidden.value = item.num || '01';

    // Personal registrador
    const staffEl = document.getElementById('edit_modal_registered_by');
    const staffSelect = document.getElementById('edit_staff_id');
    if (staffSelect) {
        if (item.staff_id) {
            staffSelect.value = item.staff_id;
        }
        if (staffEl && staffSelect.selectedIndex >= 0 && staffSelect.options[staffSelect.selectedIndex]) {
            staffEl.textContent = staffSelect.options[staffSelect.selectedIndex].text.split('—')[0].trim();
        } else if (staffEl) {
            staffEl.textContent = item.staff_name || window.REGISTERED_BY_HEADER || '';
        }
    }

    // Datos del formulario
    if (document.getElementById('edit_area')) {
        document.getElementById('edit_area').value = item.area || '';
    }
    if (document.getElementById('edit_altitud')) {
        document.getElementById('edit_altitud').value = item.altitud || '1500-3000';
    }

    document.getElementById('edit_tipo_vehiculo').value = item.tipo_vehiculo || '';
    document.getElementById('edit_marca').value = item.marca || '';
    document.getElementById('edit_modelo').value = item.modelo || '';
    document.getElementById('edit_placa').value = item.placa || '';

    document.getElementById('edit_temp_c').value = item.temp_c !== null ? item.temp_c : '';
    document.getElementById('edit_opa_1').value = item.opa_1 !== null ? item.opa_1 : '';
    document.getElementById('edit_opa_2').value = item.opa_2 !== null ? item.opa_2 : '';
    document.getElementById('edit_opa_3').value = item.opa_3 !== null ? item.opa_3 : '';
    document.getElementById('edit_rpm_1').value = item.rpm_1 !== null ? item.rpm_1 : '';
    document.getElementById('edit_rpm_2').value = item.rpm_2 !== null ? item.rpm_2 : '';
    document.getElementById('edit_rpm_3').value = item.rpm_3 !== null ? item.rpm_3 : '';

    const alt = item.altitud || '1500-3000';
    let defaultLim = 2.80;
    if (alt === '0-1500') defaultLim = 2.44;
    else if (alt === '1500-3000') defaultLim = 2.80;
    else if (alt === '3000-4500') defaultLim = 3.22;

    document.getElementById('edit_limite_normativa').value = item.limite_normativa || defaultLim.toFixed(2);
    document.getElementById('edit_measurement_date').value = item.date_raw || '';
    document.getElementById('edit_measurement_time').value = item.time_raw || '';

    const altText = document.getElementById('edit_altitud_text');
    const altLimText = document.getElementById('edit_altitud_limit_text');
    if (altText) altText.textContent = `Altitud: ${alt} msnm`;
    if (altLimText) altLimText.textContent = `${(item.limite_normativa || defaultLim).toFixed(2).replace('.', ',')} m⁻¹`;

    // Fotos
    modalPhotos.edit = Array.isArray(item.photos) ? [...item.photos] : (item.photos ? [item.photos] : []);
    modalPhotoIndex.edit = 0;
    syncEditRemainingImages();
    window.renderPhotoSlider('edit');

    // Ubicación & UTM
    let lat = parseFloat(item.latitude);
    let lng = parseFloat(item.longitude);
    const easting = parseFloat(item.utm_easting);
    const northing = parseFloat(item.utm_northing);
    const zone = item.utm_zone || '19K';

    if ((isNaN(lat) || isNaN(lng)) && !isNaN(easting) && !isNaN(northing)) {
        const p = utmToLatLng(easting, northing, zone);
        lat = p.lat;
        lng = p.lng;
    }

    if (isNaN(lat) || isNaN(lng)) {
        lat = -16.503412;
        lng = -68.132456;
    }

    document.getElementById('edit_latitude').value = lat.toFixed(7);
    document.getElementById('edit_longitude').value = lng.toFixed(7);
    document.getElementById('edit_utm_zone').value = zone;
    if (!isNaN(easting)) document.getElementById('edit_utm_easting').value = easting.toFixed(1);
    if (!isNaN(northing)) document.getElementById('edit_utm_northing').value = northing.toFixed(1);

    const utmFormatted = `E: ${!isNaN(easting) ? easting.toFixed(1) : '—'}, N: ${!isNaN(northing) ? northing.toFixed(1) : '—'}, Z: ${zone}`;
    const disp = document.getElementById('edit_utm_display');
    if (disp) disp.textContent = utmFormatted;

    document.getElementById('edit_location_description').value = item.location_description || utmFormatted;
    document.getElementById('edit_observations').value = item.observations || '';

    // Iniciar siempre en modo Consulta (Solo Lectura)
    window.applyEditModeState(false);

    window.recalcOpacidad('edit');

    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        initModalMiniMap('edit', lat, lng);
    }, 150);
};

window.openEditMeasurementModal = function (item) {
    window.openViewMeasurementModal(item);
    window.applyEditModeState(true);
};

window.closeEditMeasurementModal = function () {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

// =========================================================================
// 5. CÁLCULO EN VIVO DE PROMEDIOS Y CUMPLIMIENTO LMP
// =========================================================================
window.recalcOpacidad = function (prefix) {
    const o1 = parseFloat(document.getElementById(`${prefix}_opa_1`)?.value) || null;
    const o2 = parseFloat(document.getElementById(`${prefix}_opa_2`)?.value) || null;
    const o3 = parseFloat(document.getElementById(`${prefix}_opa_3`)?.value) || null;

    const r1 = parseFloat(document.getElementById(`${prefix}_rpm_1`)?.value) || null;
    const r2 = parseFloat(document.getElementById(`${prefix}_rpm_2`)?.value) || null;
    const r3 = parseFloat(document.getElementById(`${prefix}_rpm_3`)?.value) || null;

    const limite = parseFloat(document.getElementById(`${prefix}_limite_normativa`)?.value) || 2.80;

    // Promedio Opacidad
    const oVals = [o1, o2, o3].filter(v => v !== null);
    let avgOpa = null;
    if (oVals.length > 0) {
        avgOpa = oVals.reduce((a, b) => a + b, 0) / oVals.length;
    }

    // Promedio RPM (sin redondear a entero)
    const rVals = [r1, r2, r3].filter(v => v !== null);
    let avgRpm = null;
    if (rVals.length > 0) {
        avgRpm = rVals.reduce((a, b) => a + b, 0) / rVals.length;
    }

    const dispOpa = document.getElementById(`${prefix}_opa_promedio_display`);
    const dispRpm = document.getElementById(`${prefix}_rpm_promedio_display`);
    const badgeCumple = document.getElementById(`${prefix}_cumple_badge`);

    if (dispOpa) {
        dispOpa.textContent = avgOpa !== null ? `${avgOpa.toFixed(2)} %` : '— %';
    }
    if (dispRpm) {
        dispRpm.textContent = avgRpm !== null ? (avgRpm % 1 === 0 ? `${avgRpm} RPM` : `${avgRpm.toFixed(2)} RPM`) : '— RPM';
    }

    if (badgeCumple) {
        if (avgOpa === null) {
            badgeCumple.innerHTML = `<span class="badge-compliance-ok" style="background:#f1f5f9;color:#64748b;border-color:#cbd5e1;"><span>PENDIENTE</span></span>`;
        } else if (avgOpa <= limite) {
            badgeCumple.innerHTML = `<span class="badge-compliance-ok"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><polyline points="20 6 9 17 4 12"/></svg><span>CUMPLE LMP (≤ ${limite} m⁻¹)</span></span>`;
        } else {
            badgeCumple.innerHTML = `<span class="badge-compliance-danger"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span>SUPERA LÍMITE (> ${limite} m⁻¹)</span></span>`;
        }
    }
};

// =========================================================================
// 6. CAPTURA DE COORDENADAS GPS
// =========================================================================
window.captureCoordinatesGPS = function (prefix) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    if (!navigator.geolocation) {
        alert('Tu navegador no soporta geolocalización satelital.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (pos) {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            updateUtmFromLatLng(prefix, lat, lng);

            if (prefix === 'create' && createModalMarker) {
                createModalMarker.setLatLng([lat, lng]);
                if (createModalMap) createModalMap.panTo([lat, lng]);
            } else if (prefix === 'edit' && editModalMarker) {
                editModalMarker.setLatLng([lat, lng]);
                if (editModalMap) editModalMap.panTo([lat, lng]);
            }
        },
        function (err) {
            alert('No se pudo obtener la posición satelital: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
};

// =========================================================================
// 7. AUTO-GUARDADO INLINE DE ENCABEZADO TÉCNICO
// =========================================================================
window.autoSaveHeaderField = function () {
    const badge = document.getElementById('headerAutoSaveBadge');
    if (badge) {
        badge.classList.add('visible', 'saving');
        badge.querySelector('span').textContent = 'Guardando...';
    }

    const payload = {
        installation_name: document.getElementById('inline_installation_name')?.value || '',
        start_date: document.getElementById('inline_start_date')?.value || '',
        end_date: document.getElementById('inline_end_date')?.value || '',
        monitoring_type: document.getElementById('inline_monitoring_type')?.value || '',
        _token: window.CSRF_TOKEN,
    };

    fetch(`/modulos/${window.MODULE_ID}/opacidad/header`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': window.CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(res => res.json())
    .then(data => {
        if (badge) {
            badge.classList.remove('saving');
            badge.querySelector('span').textContent = 'Guardado';
            setTimeout(() => { badge.classList.remove('visible'); }, 2500);
        }
    })
    .catch(err => {
        if (badge) {
            badge.classList.remove('saving');
            badge.querySelector('span').textContent = 'Error';
        }
    });
};

// =========================================================================
// 8. FILTROS Y BÚSQUEDA REACTIVA EN TABLA
// =========================================================================
window.filterOpacityTable = function (type, btn) {
    document.querySelectorAll('.filter-pill-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');

    const rows = document.querySelectorAll('.opacity-row-item');
    rows.forEach(r => {
        const isCompliant = r.getAttribute('data-compliant') === 'true';
        if (type === 'all') {
            r.style.display = '';
        } else if (type === 'compliant') {
            r.style.display = isCompliant ? '' : 'none';
        } else if (type === 'danger') {
            r.style.display = !isCompliant ? '' : 'none';
        }
    });
};

window.searchOpacityTable = function () {
    const q = document.getElementById('opacitySearchInput')?.value.toLowerCase().trim() || '';
    const rows = document.querySelectorAll('.opacity-row-item');

    rows.forEach(r => {
        const text = (r.getAttribute('data-search') || '').toLowerCase();
        r.style.display = text.includes(q) ? '' : 'none';
    });
};

window.confirmDeleteMeasurement = function (id, placa) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar medición?',
            text: `Se eliminará el registro del vehículo ${placa}. Esta acción no se puede deshacer.`,
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
                form.action = `/modulos/${window.MODULE_ID}/opacidad/mediciones/${id}`;
                form.submit();
            }
        });
    } else if (confirm(`¿Eliminar la medición del vehículo ${placa}?`)) {
        const form = document.getElementById('deleteMeasurementForm');
        form.action = `/modulos/${window.MODULE_ID}/opacidad/mediciones/${id}`;
        form.submit();
    }
};

// =========================================================================
// 9. MODALES AUXILIARES (UBICACIONES, EXPORT, TABLAS, FOTOS)
// =========================================================================
window.openAllLocationsModal = function () {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';

    setTimeout(() => {
        initAllLocationsLeafletMap();
    }, 200);
};

window.closeAllLocationsModal = function () {
    const modal = document.getElementById('allLocationsModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

function initAllLocationsLeafletMap() {
    if (allLocationsMap) {
        allLocationsMap.invalidateSize();
        return;
    }

    const mapContainer = document.getElementById('allLocationsMapLeaflet');
    if (!mapContainer || typeof L === 'undefined') return;

    allLocationsMap = L.map('allLocationsMapLeaflet').setView([-16.5, -68.15], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(allLocationsMap);

    const validPoints = opacityMeasurements.filter(m => m.latitude && m.longitude);
    const latLngBounds = [];

    validPoints.forEach((m, idx) => {
        const color = m.is_compliant ? '#059669' : '#dc2626';
        const marker = L.circleMarker([m.latitude, m.longitude], {
            radius: 8,
            fillColor: color,
            color: '#ffffff',
            weight: 2,
            opacity: 1,
            fillOpacity: 0.9
        }).addTo(allLocationsMap);

        marker.bindPopup(`
            <div style="font-family:'Outfit',sans-serif; font-size:12.5px; line-height:1.4;">
                <strong style="color:#4f46e5;">#${m.point_number || m.num} — ${m.placa || 'Sin Placa'}</strong><br>
                <span>Vehículo: <strong>${m.tipo_vehiculo} ${m.marca || ''} ${m.modelo || ''}</strong></span><br>
                <span>Opacidad: <strong>${m.opa_promedio !== null ? m.opa_promedio + '%' : '—'}</strong> (LMP: ${m.limite_normativa} m⁻¹)</span><br>
                <span>Estado: <strong style="color:${color};">${m.is_compliant ? 'CUMPLE' : 'SUPERA LÍMITE'}</strong></span><br>
                <span>Técnico: ${m.staff_name}</span>
            </div>
        `);

        allLocationsMarkers.push(marker);
        latLngBounds.push([m.latitude, m.longitude]);
    });

    if (latLngBounds.length > 0) {
        allLocationsMap.fitBounds(latLngBounds, { padding: [30, 30] });
    }
}

window.focusPointOnAllLocationsMap = function (idx) {
    if (allLocationsMarkers[idx]) {
        const m = allLocationsMarkers[idx];
        allLocationsMap.setView(m.getLatLng(), 16);
        m.openPopup();
    }
};

window.openExportModal = function () {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closeExportModal = function () {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.openOpacityTablesModal = function () {
    const modal = document.getElementById('opacityTablesModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closeOpacityTablesModal = function () {
    const modal = document.getElementById('opacityTablesModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};

window.openPhotoReportModal = function () {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
};

window.closePhotoReportModal = function () {
    const modal = document.getElementById('photoReportModal');
    if (modal) {
        modal.classList.remove('open');
        document.body.style.overflow = '';
    }
};
