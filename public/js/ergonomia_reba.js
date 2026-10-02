/**
 * ==========================================================================
 * ERGONOMÍA REBA — MOTOR REACTIVO & DESIGN SYSTEM (METRIC v2)
 * Rapid Entire Body Assessment (REBA) - 3 Column Layout & Interactive Engine
 * ==========================================================================
 */

// 1. Matrices Normativas REBA (Hignett & McAtamney / NTP 601 / ISO 11226)
const REBA_TABLE_A = {
    1: { 1: [1, 2, 3, 4], 2: [2, 3, 4, 5], 3: [3, 4, 5, 6] },
    2: { 1: [2, 3, 4, 5], 2: [3, 4, 5, 6], 3: [4, 5, 6, 7] },
    3: { 1: [2, 4, 5, 6], 2: [4, 5, 6, 7], 3: [5, 6, 7, 8] },
    4: { 1: [3, 5, 6, 7], 2: [5, 6, 7, 8], 3: [6, 7, 8, 9] },
    5: { 1: [4, 6, 7, 8], 2: [6, 7, 8, 9], 3: [7, 8, 9, 9] }
};

const REBA_TABLE_B = {
    1: { 1: [1, 2, 2], 2: [1, 2, 3] },
    2: { 1: [1, 2, 3], 2: [2, 3, 4] },
    3: { 1: [3, 4, 5], 2: [4, 5, 5] },
    4: { 1: [4, 5, 5], 2: [5, 6, 7] },
    5: { 1: [6, 7, 8], 2: [7, 8, 8] },
    6: { 1: [7, 8, 8], 2: [8, 9, 9] }
};

const REBA_TABLE_C = [
    [1, 1, 1, 2, 3, 3, 4, 5, 6, 7, 7, 7],
    [1, 2, 2, 3, 4, 4, 5, 6, 6, 7, 7, 8],
    [2, 3, 3, 3, 4, 5, 6, 7, 7, 8, 8, 8],
    [3, 4, 4, 4, 5, 6, 7, 8, 8, 9, 9, 9],
    [4, 4, 4, 5, 6, 7, 8, 8, 9, 9, 9, 9],
    [6, 6, 6, 7, 8, 8, 9, 9, 10, 10, 10, 10],
    [7, 7, 7, 8, 9, 9, 9, 10, 10, 11, 11, 11],
    [8, 8, 8, 9, 10, 10, 10, 10, 10, 11, 11, 11],
    [9, 9, 9, 10, 10, 10, 11, 11, 11, 12, 12, 12],
    [10, 10, 10, 11, 11, 11, 11, 12, 12, 12, 12, 12],
    [11, 11, 11, 11, 12, 12, 12, 12, 12, 12, 12, 12],
    [12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12, 12]
];

/**
 * Asigna un valor a un <select> con normalización estricta, búsqueda insensible y fallback dinámico
 */
function setSelectWithFallback(selectEl, rawVal, defaultVal) {
    if (!selectEl) return;
    const val = (rawVal !== null && rawVal !== undefined) ? String(rawVal).trim() : (defaultVal || '');
    if (!val) {
        if (selectEl.options.length > 0) selectEl.selectedIndex = 0;
        return;
    }

    // 1. Coincidencia exacta insensible a mayúsculas
    for (let i = 0; i < selectEl.options.length; i++) {
        if (selectEl.options[i].value.toLowerCase() === val.toLowerCase() || 
            selectEl.options[i].text.toLowerCase() === val.toLowerCase()) {
            selectEl.selectedIndex = i;
            selectEl.value = selectEl.options[i].value;
            return;
        }
    }

    // 2. Coincidencia normalizada (sin tildes, sin caracteres especiales)
    const clean = (s) => s ? s.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase().replace(/[^a-z0-9]/g, "") : '';
    const cleanVal = clean(val);
    for (let i = 0; i < selectEl.options.length; i++) {
        const optValClean = clean(selectEl.options[i].value);
        const optTextClean = clean(selectEl.options[i].text);
        if (optValClean === cleanVal || optTextClean === cleanVal) {
            selectEl.selectedIndex = i;
            selectEl.value = selectEl.options[i].value;
            return;
        }
    }

    // 3. Coincidencia por subcadena / inclusión mutua
    for (let i = 0; i < selectEl.options.length; i++) {
        const optValClean = clean(selectEl.options[i].value);
        const optTextClean = clean(selectEl.options[i].text);
        if ((optValClean && (optValClean.includes(cleanVal) || cleanVal.includes(optValClean))) ||
            (optTextClean && (optTextClean.includes(cleanVal) || cleanVal.includes(optTextClean)))) {
            selectEl.selectedIndex = i;
            selectEl.value = selectEl.options[i].value;
            return;
        }
    }

    // 4. Si es un valor personalizado que no existe en el select, agregarlo dinámicamente
    const customOpt = document.createElement('option');
    customOpt.value = val;
    customOpt.textContent = val;
    customOpt.selected = true;
    selectEl.appendChild(customOpt);
    selectEl.value = val;
}

/**
 * Función biomecánica para calcular Puntuaciones y Nivel de Acción REBA
 */
function calculateReba(data) {
    // 1. Grupo A (Tronco, Cuello, Piernas & Carga)
    const troncoBase = parseInt(data.tronco_base) || 1;
    const troncoMod = data.tronco_mod ? 1 : 0;
    const troncoFinal = Math.min(5, Math.max(1, troncoBase + troncoMod));

    const cuelloBase = parseInt(data.cuello_base) || 1;
    const cuelloMod = data.cuello_mod ? 1 : 0;
    const cuelloFinal = Math.min(3, Math.max(1, cuelloBase + cuelloMod));

    const piernasBase = parseInt(data.piernas_base) || 1;
    let piernasMod = 0;
    if (data.piernas_flexion_30_60 !== undefined || data.piernas_flexion_mas_60 !== undefined) {
        const p30 = (data.piernas_flexion_30_60 === true || data.piernas_flexion_30_60 === 1 || data.piernas_flexion_30_60 === '1') ? 1 : 0;
        const p60 = (data.piernas_flexion_mas_60 === true || data.piernas_flexion_mas_60 === 2 || data.piernas_flexion_mas_60 === '2' || data.piernas_flexion_mas_60 === 1 || data.piernas_flexion_mas_60 === '1') ? 2 : 0;
        piernasMod = p30 + p60;
    } else if (data.piernas_mod !== undefined && data.piernas_mod !== null) {
        piernasMod = parseInt(data.piernas_mod) || 0;
    }
    const piernasFinal = Math.max(1, piernasBase + piernasMod);

    const colPiernas = Math.min(4, Math.max(1, piernasFinal));
    const scoreTablaA = (REBA_TABLE_A[troncoFinal] && REBA_TABLE_A[troncoFinal][cuelloFinal])
        ? REBA_TABLE_A[troncoFinal][cuelloFinal][colPiernas - 1]
        : 1;

    const cargaFuerza = parseInt(data.carga_fuerza) || 0;
    const cargaBrusca = data.carga_brusca ? 1 : 0;
    const scoreA = Math.min(12, Math.max(1, scoreTablaA + cargaFuerza + cargaBrusca));

    // 2. Grupo B (Brazos, Antebrazos, Muñecas & Agarre)
    const brazoBase = parseInt(data.brazo_base) || 1;
    let brazoMod = 0;
    if (data.brazo_abduccion) brazoMod += 1;
    if (data.brazo_hombro_elevado) brazoMod += 1;
    if (data.brazo_apoyo_gravedad) brazoMod -= 1;
    const brazoFinal = Math.min(6, Math.max(1, brazoBase + brazoMod));

    const antebrazoFinal = Math.min(2, Math.max(1, parseInt(data.antebrazo_base) || 1));

    const munecaBase = parseInt(data.muneca_base) || 1;
    const munecaMod = data.muneca_mod ? 1 : 0;
    const munecaFinal = Math.min(3, Math.max(1, munecaBase + munecaMod));

    const scoreTablaB = (REBA_TABLE_B[brazoFinal] && REBA_TABLE_B[brazoFinal][antebrazoFinal])
        ? REBA_TABLE_B[brazoFinal][antebrazoFinal][munecaFinal - 1]
        : 1;

    const agarre = parseInt(data.agarre) || 0;
    const scoreB = Math.min(12, Math.max(1, scoreTablaB + agarre));

    // 3. Puntuación C (Score A vs Score B)
    const scoreC = (REBA_TABLE_C[scoreA - 1] && REBA_TABLE_C[scoreA - 1][scoreB - 1])
        ? REBA_TABLE_C[scoreA - 1][scoreB - 1]
        : 1;

    // 4. Actividad Muscular
    const actEstatica = data.actividad_estatica ? 1 : 0;
    const actRepetitiva = data.actividad_repetitiva ? 1 : 0;
    const actInestable = data.actividad_inestable ? 1 : 0;
    const scoreActividad = actEstatica + actRepetitiva + actInestable;

    // 5. Score Final REBA (1 a 15)
    const scoreFinal = Math.min(15, Math.max(1, scoreC + scoreActividad));

    // 6. Clasificación de Riesgo y Nivel de Acción
    let riskLevel = 'Inapreciable';
    let actionLevel = 'Nivel 0: No es necesaria acción';
    let badgeClass = 'risk-inapreciable';
    let color = '#047857';

    if (scoreFinal === 1) {
        riskLevel = 'Inapreciable';
        actionLevel = 'Nivel 0: No es necesaria acción';
        badgeClass = 'risk-inapreciable';
        color = '#047857';
    } else if (scoreFinal <= 3) {
        riskLevel = 'Bajo';
        actionLevel = 'Nivel 1: Puede ser necesaria la acción';
        badgeClass = 'risk-bajo';
        color = '#4d7c0f';
    } else if (scoreFinal <= 7) {
        riskLevel = 'Medio';
        actionLevel = 'Nivel 2: Es necesaria la acción';
        badgeClass = 'risk-medio';
        color = '#b45309';
    } else if (scoreFinal <= 10) {
        riskLevel = 'Alto';
        actionLevel = 'Nivel 3: Es necesaria la acción pronto';
        badgeClass = 'risk-alto';
        color = '#c2410c';
    } else {
        riskLevel = 'Muy Alto';
        actionLevel = 'Nivel 4: Es necesaria la acción de inmediato';
        badgeClass = 'risk-muy-alto';
        color = '#b91c1c';
    }

    return {
        troncoFinal,
        cuelloFinal,
        piernasFinal,
        scoreTablaA,
        scoreA,
        brazoFinal,
        antebrazoFinal,
        munecaFinal,
        scoreTablaB,
        scoreB,
        scoreC,
        scoreActividad,
        scoreFinal,
        riskLevel,
        actionLevel,
        badgeClass,
        color
    };
}

// ==========================================================================
// ESTADO GLOBAL DEL VISOR FOTOGRÁFICO & MODO EDICIÓN
// ==========================================================================
let modalPhotos = { create: [], edit: [] };
let modalPhotoIndex = { create: 0, edit: 0 };
let isEditUnlocked = false;
let isOptimizingPhotos = false;

// Variables Leaflet
let createModalMap = null;
let createModalMarker = null;
let editModalMap = null;
let editModalMarker = null;

// ==========================================================================
// VISOR FOTOGRÁFICO TIPO SLIDE CONTINUO
// ==========================================================================
function renderPhotoSlider(prefix) {
    const photos = modalPhotos[prefix] || [];
    const idx = modalPhotoIndex[prefix] || 0;
    const count = photos.length;

    const imgEl = document.getElementById(`${prefix}_slider_img`);
    const placeholderEl = document.getElementById(`${prefix}_slider_placeholder`);
    const counterBadge = document.getElementById(`${prefix}_slider_counter`);
    const prevBtn = document.getElementById(`${prefix}_slider_btn_prev`);
    const nextBtn = document.getElementById(`${prefix}_slider_btn_next`);
    const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
    const countInd = document.getElementById(`${prefix}_photo_count_indicator`);

    if (countInd) {
        countInd.textContent = count === 1 ? '1 foto' : `${count} fotos`;
    }

    if (count === 0) {
        if (imgEl) { imgEl.src = ''; imgEl.style.display = 'none'; }
        if (placeholderEl) placeholderEl.style.display = 'flex';
        if (counterBadge) counterBadge.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (thumbsStrip) { thumbsStrip.innerHTML = ''; thumbsStrip.style.display = 'none'; }
        return;
    }

    if (placeholderEl) placeholderEl.style.display = 'none';
    if (imgEl) {
        imgEl.src = photos[idx];
        imgEl.style.display = 'block';
    }

    if (counterBadge) {
        counterBadge.textContent = `${idx + 1} / ${count}`;
        counterBadge.style.display = 'block';
    }

    if (prevBtn) prevBtn.style.display = count > 1 ? 'grid' : 'none';
    if (nextBtn) nextBtn.style.display = count > 1 ? 'grid' : 'none';

    if (thumbsStrip) {
        if (count > 1) {
            thumbsStrip.style.display = 'flex';
            let html = '';
            photos.forEach((src, i) => {
                const showDel = (prefix === 'edit' && isEditUnlocked) || prefix === 'create';
                html += `
                    <div class="slider-thumb-item ${i === idx ? 'active' : ''}" onclick="selectSlidePhoto('${prefix}', ${i})" title="Foto #${i + 1}">
                        <img src="${src}" alt="Thumb ${i + 1}">
                        ${showDel ? `<button type="button" class="thumb-del-badge" onclick="event.stopPropagation(); deleteActivePhoto('${prefix}', ${i})" title="Eliminar foto #${i + 1}">✕</button>` : ''}
                    </div>
                `;
            });
            thumbsStrip.innerHTML = html;
        } else {
            thumbsStrip.innerHTML = '';
            thumbsStrip.style.display = 'none';
        }
    }
}

function slidePhotoNav(prefix, direction) {
    const photos = modalPhotos[prefix] || [];
    if (photos.length <= 1) return;
    let idx = (modalPhotoIndex[prefix] || 0) + direction;
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

                let targetMaxDim = 2560;
                let targetQuality = 0.93;

                if (fileSize <= 4 * 1024 * 1024 && maxDim <= 2800) {
                    targetMaxDim = 2560;
                    targetQuality = 0.93;
                } else if (fileSize <= 9 * 1024 * 1024 && maxDim <= 4500) {
                    targetMaxDim = 2200;
                    targetQuality = 0.90;
                } else {
                    targetMaxDim = 2048;
                    targetQuality = 0.88;
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

async function handleMultipleImagesSelected(input, prefix) {
    if (!input.files || input.files.length === 0) return;
    const rawFiles = Array.from(input.files);

    isOptimizingPhotos = true;

    const btnTrigger = document.getElementById(`${prefix}_btn_add_photos`);
    const originalBtnHtml = btnTrigger ? btnTrigger.innerHTML : '';
    if (btnTrigger) {
        btnTrigger.disabled = true;
        btnTrigger.innerHTML = `<span>Optimizando fotos...</span>`;
    }

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
                    renderPhotoSlider(prefix);
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
    }
}

// ==========================================================================
// CONVERSIÓN UTM & MINI MAPA LEAFLET
// ==========================================================================
function latLngToUtm(lat, lng) {
    const a = 6378137;
    const f = 1 / 298.257223563;
    const b = a * (1 - f);
    const e = Math.sqrt((a * a - b * b) / (a * a));
    const ePrime = Math.sqrt((a * a - b * b) / (b * b));
    const k0 = 0.9996;

    const latRad = lat * Math.PI / 180;
    const lngRad = lng * Math.PI / 180;

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
    const lon0Rad = lon0 * Math.PI / 180;

    const e2 = e * e;
    const sinLat = Math.sin(latRad);
    const cosLat = Math.cos(latRad);
    const tanLat = Math.tan(latRad);

    const n = a / Math.sqrt(1 - e2 * sinLat * sinLat);
    const t = tanLat * tanLat;
    const c = ePrime * ePrime * cosLat * cosLat;
    const A = (lngRad - lon0Rad) * cosLat;

    const m = a * (
        (1 - e2 / 4 - 3 * Math.pow(e2, 2) / 64 - 5 * Math.pow(e2, 3) / 256) * latRad
        - (3 * e2 / 8 + 3 * Math.pow(e2, 2) / 32 + 45 * Math.pow(e2, 3) / 1024) * Math.sin(2 * latRad)
        + (15 * Math.pow(e2, 2) / 256 + 45 * Math.pow(e2, 3) / 1024) * Math.sin(4 * latRad)
        - (35 * Math.pow(e2, 3) / 3072) * Math.sin(6 * latRad)
    );

    const A2 = A * A;
    const A3 = A2 * A;
    const A4 = A2 * A2;
    const A5 = A4 * A;
    const A6 = A3 * A3;

    let easting = k0 * n * (
        A
        + (1 - t + c) * A3 / 6
        + (5 - 18 * t + t * t + 72 * c - 58 * ePrime * ePrime) * A5 / 120
    ) + 500000;

    let northing = k0 * (
        m
        + n * tanLat * (
            A2 / 2
            + (5 - t + 9 * c + 4 * c * c) * A4 / 24
            + (61 - 58 * t + t * t + 600 * c - 330 * ePrime * ePrime) * A6 / 720
        )
    );

    if (lat < 0) {
        northing += 10000000;
    }

    const zone = `${zoneNum}${zoneLetter}`;
    const formatted = `E: ${easting.toFixed(3)}, N: ${northing.toFixed(3)}, Z: ${zone}`;
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

    const isSouth = zoneLetter ? (zoneLetter < 'N') : true;
    const x = easting - 500000;
    const y = isSouth ? northing - 10000000 : northing;

    const m = y / k0;
    const e2 = e * e;
    const e4 = e2 * e2;
    const e6 = e4 * e2;
    const e1 = (1 - Math.sqrt(1 - e2)) / (1 + Math.sqrt(1 - e2));

    const mu = m / (a * (1 - e2 / 4 - 3 * e4 / 64 - 5 * e6 / 256));

    const phi1 = mu + (3 * e1 / 2 - 27 * Math.pow(e1, 3) / 32) * Math.sin(2 * mu)
        + (21 * e1 * e1 / 16 - 55 * Math.pow(e1, 4) / 32) * Math.sin(4 * mu)
        + (151 * Math.pow(e1, 3) / 96) * Math.sin(6 * mu)
        + (1097 * Math.pow(e1, 4) / 512) * Math.sin(8 * mu);

    const sinPhi1 = Math.sin(phi1);
    const cosPhi1 = Math.cos(phi1);
    const tanPhi1 = Math.tan(phi1);

    const n1 = a / Math.sqrt(1 - e2 * sinPhi1 * sinPhi1);
    const t1 = tanPhi1 * tanPhi1;
    const c1 = ePrime * ePrime * cosPhi1 * cosPhi1;
    const r1 = a * (1 - e2) / Math.pow(1 - e2 * sinPhi1 * sinPhi1, 1.5);
    const d = x / (n1 * k0);

    const d2 = d * d;
    const d3 = d2 * d;
    const d4 = d2 * d2;
    const d5 = d4 * d;
    const d6 = d3 * d3;

    const lat = phi1 - (n1 * tanPhi1 / r1) * (
        d2 / 2
        - (5 + 3 * t1 + 10 * c1 - 4 * c1 * c1 - 9 * ePrime * ePrime) * d4 / 24
        + (61 + 90 * t1 + 298 * c1 + 45 * t1 * t1 - 252 * ePrime * ePrime - 3 * c1 * c1) * d6 / 720
    );

    const lon0 = (zoneNum - 1) * 6 - 180 + 3;
    const lng = (lon0 * Math.PI / 180) + (
        d
        - (1 + 2 * t1 + c1) * d3 / 6
        + (5 - 2 * c1 + 28 * t1 - 3 * c1 * c1 + 8 * ePrime * ePrime + 24 * t1 * t1) * d5 / 120
    ) / cosPhi1;

    return {
        lat: lat * 180 / Math.PI,
        lng: lng * 180 / Math.PI
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
    const locInput = document.getElementById(`${prefix}_location`);
    const latInput = document.getElementById(`${prefix}_latitude`);
    const lngInput = document.getElementById(`${prefix}_longitude`);

    if (eInput) eInput.value = utm.easting.toFixed(3);
    if (nInput) nInput.value = utm.northing.toFixed(3);
    if (zInput) zInput.value = utm.zone;
    if (disp) disp.textContent = utm.formatted;
    if (locInput) locInput.value = utm.formatted;
    if (latInput) latInput.value = lat.toFixed(7);
    if (lngInput) lngInput.value = lng.toFixed(7);
}

function syncUtmToMap(prefix) {
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

    const formatted = `E: ${eVal.toFixed(3)}, N: ${nVal.toFixed(3)}, Z: ${zVal.toUpperCase()}`;
    const disp = document.getElementById(`${prefix}_utm_display`);
    if (disp) disp.textContent = formatted;

    const locInput = document.getElementById(`${prefix}_location`);
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
}

function initModalMiniMap(prefix, lat, lng) {
    const mapContainerId = `${prefix}_modal_map`;
    const container = document.getElementById(mapContainerId);
    if (!container || typeof L === 'undefined') return;

    lat = parseFloat(lat) || -16.5000;
    lng = parseFloat(lng) || -68.1500;

    if (prefix === 'create') {
        if (!createModalMap) {
            createModalMap = L.map(mapContainerId).setView([lat, lng], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
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
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
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

function getCurrentGpsPosition(latFieldId, lngFieldId, prefix) {
    if (!navigator.geolocation) {
        alert('Geolocalización no soportada por el navegador.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateUtmFromLatLng(prefix, lat, lng);
            syncUtmToMap(prefix);
        },
        (err) => {
            alert('No se pudo obtener la ubicación GPS: ' + err.message);
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}


// ==========================================================================
// HELPERS PARA ESCAPE Y ENTRADA DINÁMICA DE TRABAJADORES Y TAREAS
// ==========================================================================
function escapeHtml(text) {
    if (!text) return '';
    return String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function renderWorkerInputs(prefix, count, names = []) {
    const container = document.getElementById(`${prefix}_nombres_trabajadores_container`);
    const badge = document.getElementById(`${prefix}_workers_count_badge`);
    if (!container) return;

    count = Math.max(1, parseInt(count) || 1);
    if (badge) {
        badge.textContent = count === 1 ? '1 trabajador' : `${count} trabajadores`;
    }

    container.innerHTML = '';
    for (let i = 0; i < count; i++) {
        const val = names[i] || '';
        const row = document.createElement('div');
        row.className = 'worker-name-row';
        row.style.cssText = 'display: flex; align-items: center; gap: 6px;';
        const isDisabled = (prefix === 'edit' && !isEditUnlocked) ? 'disabled' : '';
        row.innerHTML = `
            <span style="font-size: 11px; font-weight: 800; color: #fff; background: #0284c7; padding: 4px 7px; border-radius: 6px; flex-shrink: 0;">T${i + 1}</span>
            <input type="text" name="nombres_trabajadores[]" class="custom-form-input worker-name-input" value="${escapeHtml(val)}" placeholder="Nombre y apellido del trabajador ${i + 1}" ${isDisabled}>
        `;
        container.appendChild(row);
    }
}

function updateWorkerNamesInputs(prefix) {
    const numInput = document.getElementById(`${prefix}_num_trabajadores`);
    const count = parseInt(numInput ? numInput.value : 1) || 1;
    const currentInputs = document.querySelectorAll(`#${prefix}_nombres_trabajadores_container input`);
    const currentNames = Array.from(currentInputs).map(inp => inp.value);
    renderWorkerInputs(prefix, count, currentNames);
}

function renderTaskInputs(prefix, tasks = []) {
    const container = document.getElementById(`${prefix}_tareas_container`);
    if (!container) return;

    if (!Array.isArray(tasks)) {
        tasks = [];
    }
    if (tasks.length === 0) {
        tasks = [''];
    }

    container.innerHTML = '';
    tasks.slice(0, 3).forEach((taskText, idx) => {
        const row = document.createElement('div');
        row.className = 'tarea-row';
        row.style.cssText = 'display: flex; align-items: center; gap: 6px;';
        const isDisabled = (prefix === 'edit' && !isEditUnlocked) ? 'disabled' : '';
        const canDelete = (tasks.length > 1);
        const delBtn = canDelete ? `
            <button type="button" class="btn-del-tarea" onclick="removeTareaInput('${prefix}', ${idx})" style="width: 28px; height: 28px; border-radius: 6px; border: 1px solid #fecaca; background: #fee2e2; color: #dc2626; font-size: 13px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0;" title="Eliminar tarea" ${isDisabled ? 'disabled' : ''}>✕</button>
        ` : '';
        row.innerHTML = `
            <span style="font-size: 11px; font-weight: 800; color: #475569; background: #f1f5f9; padding: 4px 7px; border-radius: 6px; flex-shrink: 0;">${idx + 1}</span>
            <input type="text" name="tareas[]" class="custom-form-input tarea-input" value="${escapeHtml(taskText || '')}" placeholder="Tarea ${idx + 1}: Ej. Levantamiento manual de cajas..." ${isDisabled}>
            ${delBtn}
        `;
        container.appendChild(row);
    });

    const addBtn = document.getElementById(`${prefix}_btn_add_tarea`);
    if (addBtn) {
        const show = (prefix === 'create' || isEditUnlocked) && tasks.length < 3;
        addBtn.style.display = show ? 'inline-flex' : 'none';
    }
}

function addTareaInput(prefix) {
    if (prefix === 'edit' && !isEditUnlocked) {
        applyEditModeState(true);
    }
    const currentInputs = document.querySelectorAll(`#${prefix}_tareas_container input.tarea-input`);
    const currentTasks = Array.from(currentInputs).map(inp => inp.value);
    if (currentTasks.length >= 3) return;
    currentTasks.push('');
    renderTaskInputs(prefix, currentTasks);
    const newInputs = document.querySelectorAll(`#${prefix}_tareas_container input.tarea-input`);
    if (newInputs.length > 0) {
        newInputs[newInputs.length - 1].focus();
    }
}

function removeTareaInput(prefix, index) {
    if (prefix === 'edit' && !isEditUnlocked) {
        applyEditModeState(true);
    }
    const currentInputs = document.querySelectorAll(`#${prefix}_tareas_container input.tarea-input`);
    const currentTasks = Array.from(currentInputs).map(inp => inp.value);
    if (currentTasks.length <= 1) return;
    currentTasks.splice(index, 1);
    renderTaskInputs(prefix, currentTasks);
}

// ==========================================================================
// CONTROL DE MODO CONSULTA / MODO EDICIÓN (ESTILO ILUMINACIÓN)
// ==========================================================================
function applyEditModeState(isEditing) {
    isEditUnlocked = isEditing;

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        if (isEditing) form.classList.remove('modal-view-mode');
        else form.classList.add('modal-view-mode');
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

    // Habilitar / Deshabilitar Campos de Entrada (Todos los 17 campos)
    const fieldsToToggle = [
        'edit_measurement_date',
        'edit_measurement_time',
        'edit_staff_id',
        'edit_area_sector',
        'edit_factor_riesgo',
        'edit_num_trabajadores',
        'edit_edad',
        'edit_puesto_trabajo',
        'edit_procedimiento_escrito',
        'edit_capacitacion',
        'edit_fuerza_agarre',
        'edit_carga_peso_kg',
        'edit_distancia_m',
        'edit_ayuda_mecanica',
        'edit_descripcion_carga',
        'edit_manifestacion_temprana',
        'edit_ubicacion_sintoma',
        'edit_tiempo_exposicion',
        'edit_utm_easting',
        'edit_utm_northing',
        'edit_utm_zone',
        'edit_latitude',
        'edit_longitude',
        'edit_observaciones'
    ];

    fieldsToToggle.forEach(fieldId => {
        const el = document.getElementById(fieldId);
        if (el) el.disabled = !isEditing;
    });

    // Desbloquear / Bloquear dinámicos
    document.querySelectorAll('#editMeasurementForm .worker-name-input, #editMeasurementForm .tarea-input').forEach(inp => {
        inp.disabled = !isEditing;
    });
    document.querySelectorAll('#editMeasurementForm .btn-del-tarea').forEach(btn => {
        btn.disabled = !isEditing;
    });

    const editAddTareaBtn = document.getElementById('edit_btn_add_tarea');
    if (editAddTareaBtn) {
        const taskCount = document.querySelectorAll('#edit_tareas_container .tarea-row').length;
        editAddTareaBtn.style.display = (isEditing && taskCount < 3) ? 'inline-flex' : 'none';
    }

    // Desbloquear radios y checkboxes del formulario
    const formRadios = document.querySelectorAll('#editMeasurementForm input[type="radio"], #editMeasurementForm input[type="checkbox"]');
    formRadios.forEach(inp => inp.disabled = !isEditing);

    // Botones y controles
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
    renderPhotoSlider('edit');
}

function toggleModalEditMode() {
    applyEditModeState(!isEditUnlocked);
}

// ==========================================================================
// MODAL NUEVA EVALUACIÓN (CREATE)
// ==========================================================================
function openCreateRebaModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;

    modal.classList.add('open');

    // Reset campos de texto y selección
    if (document.getElementById('create_area_sector')) document.getElementById('create_area_sector').value = '';
    if (document.getElementById('create_puesto_trabajo')) document.getElementById('create_puesto_trabajo').value = '';
    if (document.getElementById('create_num_trabajadores')) document.getElementById('create_num_trabajadores').value = '1';
    if (document.getElementById('create_edad')) document.getElementById('create_edad').value = '';
    if (document.getElementById('create_fuerza_agarre')) document.getElementById('create_fuerza_agarre').value = '';
    if (document.getElementById('create_carga_peso_kg')) document.getElementById('create_carga_peso_kg').value = '';
    if (document.getElementById('create_distancia_m')) document.getElementById('create_distancia_m').value = '';
    if (document.getElementById('create_ayuda_mecanica')) document.getElementById('create_ayuda_mecanica').value = '';
    if (document.getElementById('create_descripcion_carga')) document.getElementById('create_descripcion_carga').value = '';
    if (document.getElementById('create_observaciones')) document.getElementById('create_observaciones').value = '';

    setSelectWithFallback(document.getElementById('create_factor_riesgo'), 'Levantamiento y descenso manual de carga', 'Levantamiento y descenso manual de carga');
    setSelectWithFallback(document.getElementById('create_procedimiento_escrito'), 'Si', 'Si');
    setSelectWithFallback(document.getElementById('create_capacitacion'), 'Si', 'Si');
    setSelectWithFallback(document.getElementById('create_manifestacion_temprana'), 'No', 'No');
    setSelectWithFallback(document.getElementById('create_ubicacion_sintoma'), 'Ninguna', 'Ninguna');
    setSelectWithFallback(document.getElementById('create_tiempo_exposicion'), '8', '8');

    renderWorkerInputs('create', 1, ['']);
    renderTaskInputs('create', ['']);

    // Reset fotos
    modalPhotos.create = [];
    modalPhotoIndex.create = 0;
    renderPhotoSlider('create');

    const fileInput = document.getElementById('create_images_input');
    if (fileInput) fileInput.value = '';

    // Coordenadas por defecto
    const defaultEasting = 591320.10;
    const defaultNorthing = 8175310.40;
    const defaultZone = '19K';

    const eInput = document.getElementById('create_utm_easting');
    const nInput = document.getElementById('create_utm_northing');
    const zInput = document.getElementById('create_utm_zone');
    if (eInput) eInput.value = defaultEasting.toFixed(2);
    if (nInput) nInput.value = defaultNorthing.toFixed(2);
    if (zInput) zInput.value = defaultZone;

    const pos = utmToLatLng(defaultEasting, defaultNorthing, defaultZone);
    const formatted = `E: ${defaultEasting.toFixed(2)}, N: ${defaultNorthing.toFixed(2)}, Z: ${defaultZone}`;
    const disp = document.getElementById('create_utm_display');
    if (disp) disp.textContent = formatted;
    const locInput = document.getElementById('create_location');
    if (locInput) locInput.value = formatted;
    const latInput = document.getElementById('create_latitude');
    const lngInput = document.getElementById('create_longitude');
    if (latInput) latInput.value = pos.lat.toFixed(7);
    if (lngInput) lngInput.value = pos.lng.toFixed(7);

    updateCreateLiveScore();

    setTimeout(() => {
        initModalMiniMap('create', pos.lat, pos.lng);
    }, 150);
}

function openCreateMeasurementModal() {
    openCreateRebaModal();
}

function closeCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) modal.classList.remove('open');
}

// ==========================================================================
// MODAL DETALLE / EDICIÓN EVALUACIÓN (VIEW & EDIT)
// ==========================================================================
function openViewMeasurementModal(item) {
    const form = document.getElementById('editMeasurementForm');
    if (!form) return;

    form.action = `/modulos/${window.MODULE_ID}/ergonomia-reba/mediciones/${item.id}`;

    document.getElementById('edit_measurement_id').value = item.id || '';
    document.getElementById('edit_point_number').value = item.num || item.point_number || '';
    const ptDisp = document.getElementById('edit_pt_num_disp');
    if (ptDisp) ptDisp.textContent = item.num || item.point_number || '01';

    // Evaluador
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
            staffEl.textContent = item.registered_by || window.REGISTERED_BY_HEADER || '';
        }
    } else if (staffEl) {
        staffEl.textContent = item.registered_by || window.REGISTERED_BY_HEADER || '';
    }

    // Datos del Puesto (En orden idéntico a Card 1 de la app móvil)
    document.getElementById('edit_measurement_date').value = item.raw_date || item.measurement_date || '';
    let rawTime = (item.time && item.time !== '—') ? item.time : (item.measurement_time || '');
    if (rawTime && typeof rawTime === 'string') {
        const timeMatch = rawTime.trim().match(/^(\d{1,2}):(\d{2})/);
        if (timeMatch) {
            rawTime = `${timeMatch[1].padStart(2, '0')}:${timeMatch[2]}`;
        }
    }
    document.getElementById('edit_measurement_time').value = rawTime;

    // 1. Área y Sector en estudio
    if (document.getElementById('edit_area_sector')) {
        document.getElementById('edit_area_sector').value = item.area_sector || '';
    }

    // 2. Factor de riesgo disergonómico
    setSelectWithFallback(document.getElementById('edit_factor_riesgo'), item.factor_riesgo, 'Levantamiento y descenso manual de carga');

    // 3. N° de trabajadores
    if (document.getElementById('edit_num_trabajadores')) {
        document.getElementById('edit_num_trabajadores').value = item.num_trabajadores || 1;
    }

    // 4. Puesto de trabajo
    if (document.getElementById('edit_puesto_trabajo')) {
        document.getElementById('edit_puesto_trabajo').value = item.puesto_trabajo || '';
    }

    // 5. Edad
    if (document.getElementById('edit_edad')) {
        document.getElementById('edit_edad').value = item.edad || '';
    }

    // 6. Procedimiento escrito
    setSelectWithFallback(document.getElementById('edit_procedimiento_escrito'), item.procedimiento_escrito, 'Si');

    // 7. Capacitación
    setSelectWithFallback(document.getElementById('edit_capacitacion'), item.capacitacion, 'Si');

    // 8. Nombre(s) del trabajador/es
    let workers = [];
    if (Array.isArray(item.nombres_trabajadores)) {
        workers = item.nombres_trabajadores;
    } else if (typeof item.nombres_trabajadores === 'string' && item.nombres_trabajadores.trim().length > 0) {
        try {
            const parsed = JSON.parse(item.nombres_trabajadores);
            workers = Array.isArray(parsed) ? parsed : [item.nombres_trabajadores];
        } catch(e) {
            workers = item.nombres_trabajadores.split(',').map(s => s.trim());
        }
    }
    const workerCount = Math.max(parseInt(item.num_trabajadores) || 1, workers.length || 1);
    renderWorkerInputs('edit', workerCount, workers);

    // 9. Fuerza de agarre
    if (document.getElementById('edit_fuerza_agarre')) {
        document.getElementById('edit_fuerza_agarre').value = item.fuerza_agarre || '';
    }

    // 10. Carga / Peso (kg)
    if (document.getElementById('edit_carga_peso_kg')) {
        document.getElementById('edit_carga_peso_kg').value = (item.carga_peso_kg !== null && item.carga_peso_kg !== undefined) ? item.carga_peso_kg : '';
    }

    // 11. Distancia (m)
    if (document.getElementById('edit_distancia_m')) {
        document.getElementById('edit_distancia_m').value = (item.distancia_m !== null && item.distancia_m !== undefined) ? item.distancia_m : '';
    }

    // 12. Ayuda Mecánica
    if (document.getElementById('edit_ayuda_mecanica')) {
        document.getElementById('edit_ayuda_mecanica').value = item.ayuda_mecanica || '';
    }

    // 13. Descripción Carga
    if (document.getElementById('edit_descripcion_carga')) {
        document.getElementById('edit_descripcion_carga').value = item.descripcion_carga || '';
    }

    // 14. Manifestación temprana
    setSelectWithFallback(document.getElementById('edit_manifestacion_temprana'), item.manifestacion_temprana, 'No');

    // 15. Ubicación del síntoma
    setSelectWithFallback(document.getElementById('edit_ubicacion_sintoma'), item.ubicacion_sintoma, 'Ninguna');

    // 16. Tiempo de exposición (h)
    setSelectWithFallback(document.getElementById('edit_tiempo_exposicion'), item.tiempo_exposicion_horas, '8');

    // 17. Tareas analizadas
    let tasks = [];
    if (Array.isArray(item.tareas)) {
        tasks = item.tareas;
    } else if (typeof item.tareas === 'string' && item.tareas.trim().length > 0) {
        try {
            const parsed = JSON.parse(item.tareas);
            tasks = Array.isArray(parsed) ? parsed : [item.tareas];
        } catch(e) {
            tasks = item.tareas.split('\n').map(s => s.trim()).filter(s => s.length > 0);
        }
    }
    renderTaskInputs('edit', tasks);

    if (document.getElementById('edit_observaciones')) {
        document.getElementById('edit_observaciones').value = item.observaciones || '';
    }

    // Parámetros REBA — Grupo A
    setRadioValue('tronco_base', item.tronco_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_tronco_mod', !!item.tronco_mod);
    setRadioValue('cuello_base', item.cuello_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_cuello_mod', !!item.cuello_mod);
    setRadioValue('piernas_base', item.piernas_base || 1, '#editMeasurementForm');
    const pMod = parseInt(item.piernas_mod) || 0;
    const has30a60 = item.piernas_flexion_30_60 !== undefined ? !!item.piernas_flexion_30_60 : (pMod === 1 || pMod === 3);
    const hasMas60 = item.piernas_flexion_mas_60 !== undefined ? !!item.piernas_flexion_mas_60 : (pMod === 2 || pMod === 3);
    setCheckboxValue('edit_piernas_flexion_30_60', has30a60);
    setCheckboxValue('edit_piernas_flexion_mas_60', hasMas60);
    setRadioValue('carga_fuerza', item.carga_fuerza || 0, '#editMeasurementForm');
    setCheckboxValue('edit_carga_brusca', !!item.carga_brusca);

    // Parámetros REBA — Grupo B
    setRadioValue('brazo_base', item.brazo_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_brazo_abduccion', !!item.brazo_abduccion);
    setCheckboxValue('edit_brazo_hombro_elevado', !!item.brazo_hombro_elevado);
    setCheckboxValue('edit_brazo_apoyo_gravedad', !!item.brazo_apoyo_gravedad);
    setRadioValue('antebrazo_base', item.antebrazo_base || 1, '#editMeasurementForm');
    setRadioValue('muneca_base', item.muneca_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_muneca_mod', !!item.muneca_mod);
    setRadioValue('agarre', item.agarre || 0, '#editMeasurementForm');

    // Actividad
    setCheckboxValue('edit_actividad_estatica', !!item.actividad_estatica);
    setCheckboxValue('edit_actividad_repetitiva', !!item.actividad_repetitiva);
    setCheckboxValue('edit_actividad_inestable', !!item.actividad_inestable);

    // Fotos en Slide
    modalPhotos.edit = [];
    if (item.images && Array.isArray(item.images) && item.images.length > 0) {
        modalPhotos.edit = [...item.images];
    } else if (item.image_path) {
        modalPhotos.edit = [item.image_path];
    }
    modalPhotoIndex.edit = 0;
    syncEditRemainingImages();
    renderPhotoSlider('edit');

    // Coordenadas UTM
    let utmEasting = 591320.10;
    let utmNorthing = 8175310.40;
    let utmZone = '19K';
    let lat = (item.latitude !== null && item.latitude !== '') ? parseFloat(item.latitude) : NaN;
    let lng = (item.longitude !== null && item.longitude !== '') ? parseFloat(item.longitude) : NaN;

    if (item.utm_easting && item.utm_northing) {
        utmEasting = parseFloat(item.utm_easting);
        utmNorthing = parseFloat(item.utm_northing);
        utmZone = item.utm_zone || '19K';
        const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
        lat = pos.lat;
        lng = pos.lng;
    } else if (!isNaN(lat) && !isNaN(lng)) {
        const u = latLngToUtm(lat, lng);
        utmEasting = u.easting;
        utmNorthing = u.northing;
        utmZone = u.zone;
    } else {
        const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
        lat = pos.lat;
        lng = pos.lng;
    }

    const eInput = document.getElementById('edit_utm_easting');
    const nInput = document.getElementById('edit_utm_northing');
    const zInput = document.getElementById('edit_utm_zone');
    const disp = document.getElementById('edit_utm_display');
    const locInput = document.getElementById('edit_location');
    const latInput = document.getElementById('edit_latitude');
    const lngInput = document.getElementById('edit_longitude');

    const formattedUtm = `E: ${utmEasting.toFixed(2)}, N: ${utmNorthing.toFixed(2)}, Z: ${utmZone}`;
    if (eInput) eInput.value = utmEasting.toFixed(2);
    if (nInput) nInput.value = utmNorthing.toFixed(2);
    if (zInput) zInput.value = utmZone;
    if (disp) disp.textContent = formattedUtm;
    if (locInput) locInput.value = formattedUtm;
    if (latInput) latInput.value = isNaN(lat) ? '' : lat.toFixed(7);
    if (lngInput) lngInput.value = isNaN(lng) ? '' : lng.toFixed(7);

    const fileInput = document.getElementById('edit_images_input');
    if (fileInput) fileInput.value = '';

    updateEditLiveScore();

    // INICIAR SIEMPRE EN MODO CONSULTA (SOLO LECTURA)
    applyEditModeState(false);

    const modal = document.getElementById('editMeasurementModal');
    if (modal) {
        modal.classList.add('open');
        setTimeout(() => {
            initModalMiniMap('edit', isNaN(lat) ? -16.5000 : lat, isNaN(lng) ? -68.1500 : lng);
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

// Helpers para inputs
function getRadioValue(name, formScope = '') {
    const prefix = formScope ? `${formScope} ` : '';
    const el = document.querySelector(`${prefix}input[name="${name}"]:checked`);
    return el ? el.value : '1';
}

function setRadioValue(name, val, formScope = '') {
    const prefix = formScope ? `${formScope} ` : '';
    const el = document.querySelector(`${prefix}input[name="${name}"][value="${val}"]`);
    if (el) {
        el.checked = true;
        const cards = document.querySelectorAll(`${prefix}input[name="${name}"]`);
        cards.forEach(c => {
            const p = c.closest('.reba-option-card');
            if (p) p.classList.toggle('selected', c.checked);
        });
    }
}

function getCheckboxValue(id) {
    const el = document.getElementById(id);
    return el && el.checked ? 1 : 0;
}

function setCheckboxValue(id, checked) {
    const el = document.getElementById(id);
    if (el) {
        el.checked = !!checked;
        const p = el.closest('.reba-option-card');
        if (p) p.classList.toggle('selected', !!checked);
    }
}

// ==========================================================================
// RECÁLCULO EN TIEMPO REAL REBA
// ==========================================================================
function updateCreateLiveScore() {
    const chk30 = document.getElementById('create_piernas_flexion_30_60');
    const chk60 = document.getElementById('create_piernas_flexion_mas_60');
    const is30 = chk30 ? chk30.checked : false;
    const is60 = chk60 ? chk60.checked : false;
    const pMod = (is30 ? 1 : 0) + (is60 ? 2 : 0);

    const data = {
        tronco_base: getRadioValue('tronco_base', '#createMeasurementForm'),
        tronco_mod: getCheckboxValue('create_tronco_mod'),
        cuello_base: getRadioValue('cuello_base', '#createMeasurementForm'),
        cuello_mod: getCheckboxValue('create_cuello_mod'),
        piernas_base: getRadioValue('piernas_base', '#createMeasurementForm'),
        piernas_flexion_30_60: is30,
        piernas_flexion_mas_60: is60,
        piernas_mod: pMod,
        carga_fuerza: getRadioValue('carga_fuerza', '#createMeasurementForm'),
        carga_brusca: getCheckboxValue('create_carga_brusca'),
        brazo_base: getRadioValue('brazo_base', '#createMeasurementForm'),
        brazo_abduccion: getCheckboxValue('create_brazo_abduccion'),
        brazo_hombro_elevado: getCheckboxValue('create_brazo_hombro_elevado'),
        brazo_apoyo_gravedad: getCheckboxValue('create_brazo_apoyo_gravedad'),
        antebrazo_base: getRadioValue('antebrazo_base', '#createMeasurementForm'),
        muneca_base: getRadioValue('muneca_base', '#createMeasurementForm'),
        muneca_mod: getCheckboxValue('create_muneca_mod'),
        agarre: getRadioValue('agarre', '#createMeasurementForm'),
        actividad_estatica: getCheckboxValue('create_actividad_estatica'),
        actividad_repetitiva: getCheckboxValue('create_actividad_repetitiva'),
        actividad_inestable: getCheckboxValue('create_actividad_inestable')
    };

    const res = calculateReba(data);

    const valA = document.getElementById('create_live_score_a');
    const valB = document.getElementById('create_live_score_b');
    const valC = document.getElementById('create_live_score_c');
    const valAct = document.getElementById('create_live_score_act');
    const valFinal = document.getElementById('create_live_score_final');
    const valRisk = document.getElementById('create_live_risk_level');
    const valAction = document.getElementById('create_live_action_level');
    const boxFinal = document.getElementById('create_live_final_box');

    if (valA) valA.textContent = res.scoreA;
    if (valB) valB.textContent = res.scoreB;
    if (valC) valC.textContent = res.scoreC;
    if (valAct) valAct.textContent = `+${res.scoreActividad}`;
    if (valFinal) valFinal.textContent = res.scoreFinal;
    if (valRisk) valRisk.textContent = `Riesgo ${res.riskLevel}`;
    if (valAction) valAction.textContent = res.actionLevel;
    if (boxFinal) boxFinal.className = `reba-live-final-display ${res.badgeClass}`;
}

function updateEditLiveScore() {
    const chk30 = document.getElementById('edit_piernas_flexion_30_60');
    const chk60 = document.getElementById('edit_piernas_flexion_mas_60');
    const is30 = chk30 ? chk30.checked : false;
    const is60 = chk60 ? chk60.checked : false;
    const pMod = (is30 ? 1 : 0) + (is60 ? 2 : 0);

    const data = {
        tronco_base: getRadioValue('tronco_base', '#editMeasurementForm'),
        tronco_mod: getCheckboxValue('edit_tronco_mod'),
        cuello_base: getRadioValue('cuello_base', '#editMeasurementForm'),
        cuello_mod: getCheckboxValue('edit_cuello_mod'),
        piernas_base: getRadioValue('piernas_base', '#editMeasurementForm'),
        piernas_flexion_30_60: is30,
        piernas_flexion_mas_60: is60,
        piernas_mod: pMod,
        carga_fuerza: getRadioValue('carga_fuerza', '#editMeasurementForm'),
        carga_brusca: getCheckboxValue('edit_carga_brusca'),
        brazo_base: getRadioValue('brazo_base', '#editMeasurementForm'),
        brazo_abduccion: getCheckboxValue('edit_brazo_abduccion'),
        brazo_hombro_elevado: getCheckboxValue('edit_brazo_hombro_elevado'),
        brazo_apoyo_gravedad: getCheckboxValue('edit_brazo_apoyo_gravedad'),
        antebrazo_base: getRadioValue('antebrazo_base', '#editMeasurementForm'),
        muneca_base: getRadioValue('muneca_base', '#editMeasurementForm'),
        muneca_mod: getCheckboxValue('edit_muneca_mod'),
        agarre: getRadioValue('agarre', '#editMeasurementForm'),
        actividad_estatica: getCheckboxValue('edit_actividad_estatica'),
        actividad_repetitiva: getCheckboxValue('edit_actividad_repetitiva'),
        actividad_inestable: getCheckboxValue('edit_actividad_inestable')
    };

    const res = calculateReba(data);

    const valA = document.getElementById('edit_live_score_a');
    const valB = document.getElementById('edit_live_score_b');
    const valC = document.getElementById('edit_live_score_c');
    const valAct = document.getElementById('edit_live_score_act');
    const valFinal = document.getElementById('edit_live_score_final');
    const valRisk = document.getElementById('edit_live_risk_level');
    const valAction = document.getElementById('edit_live_action_level');
    const boxFinal = document.getElementById('edit_live_final_box');

    if (valA) valA.textContent = res.scoreA;
    if (valB) valB.textContent = res.scoreB;
    if (valC) valC.textContent = res.scoreC;
    if (valAct) valAct.textContent = `+${res.scoreActividad}`;
    if (valFinal) valFinal.textContent = res.scoreFinal;
    if (valRisk) valRisk.textContent = `Riesgo ${res.riskLevel}`;
    if (valAction) valAction.textContent = res.actionLevel;
    if (boxFinal) boxFinal.className = `reba-live-final-display ${res.badgeClass}`;
}

// ==========================================================================
// ELIMINACIÓN Y CONFIRMACIÓN
// ==========================================================================
function confirmDeleteMeasurement(id, num) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `¿Eliminar Evaluación REBA #${num}?`,
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            focusCancel: true
        }).then((res) => {
            if (res.isConfirmed) {
                const form = document.getElementById('deleteMeasurementForm');
                if (form) {
                    form.action = `/modulos/${window.MODULE_ID}/ergonomia-reba/mediciones/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Estás seguro de eliminar la evaluación ergonómica REBA del puesto #${num}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = `/modulos/${window.MODULE_ID}/ergonomia-reba/mediciones/${id}`;
                form.submit();
            }
        }
    }
}



// ==========================================================================
// AUTO-SAVE ENCABEZADO TÉCNICO INLINE
// ==========================================================================
let autoSaveDebounceTimer = null;
function autoSaveHeaderField() {
    clearTimeout(autoSaveDebounceTimer);
    autoSaveDebounceTimer = setTimeout(async () => {
        const payload = {
            installation_name: document.getElementById('inline_installation_name')?.value || '',
            start_date: document.getElementById('inline_start_date')?.value || '',
            end_date: document.getElementById('inline_end_date')?.value || '',
            monitoring_type: document.getElementById('inline_monitoring_type')?.value || 'Ergonomía REBA'
        };

        try {
            const resp = await fetch(window.METRIC_ERGONOMIA_REBA_CONFIG.updateHeaderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.METRIC_ERGONOMIA_REBA_CONFIG.csrfToken
                },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (data.success) {
                const badge = document.getElementById('headerAutoSaveBadge');
                if (badge) {
                    badge.classList.add('visible');
                    setTimeout(() => badge.classList.remove('visible'), 2200);
                }
            }
        } catch (err) {
            console.error('[Ergonomia REBA] Error guardando encabezado:', err);
        }
    }, 600);
}

// ==========================================================================
// MODALES ADICIONALES (TABLAS, EXPORTAR, MAPA GENERAL, LIGHTBOX)
// ==========================================================================
function openRebaTablesModal() {
    const m = document.getElementById('rebaTablesModal');
    if (m) m.classList.add('open');
}

function closeRebaTablesModal() {
    const m = document.getElementById('rebaTablesModal');
    if (m) m.classList.remove('open');
}

function switchRebaTab(tabName) {
    document.querySelectorAll('#rebaTablesModal .reba-tab-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.tab === tabName);
    });
    document.querySelectorAll('#rebaTablesModal .reba-tab-pane').forEach(p => {
        p.style.display = p.id === `tab_pane_${tabName}` ? 'block' : 'none';
    });
}

function openExportModal() {
    const m = document.getElementById('exportOptionsModal');
    if (m) m.classList.add('open');
}

function closeExportModal() {
    const m = document.getElementById('exportOptionsModal');
    if (m) m.classList.remove('open');
}

function openPhotoReportModal() {
    closeExportModal();
    const m = document.getElementById('photoReportModal');
    if (m) m.classList.add('open');
}

function closePhotoReportModal() {
    const m = document.getElementById('photoReportModal');
    if (m) m.classList.remove('open');
}

function changeRebaMosaicGrid(val) {
    const grid = document.getElementById('rebaPhotoMosaicGrid');
    if (!grid) return;
    if (val === '2x3' || val === '3x3') {
        grid.style.gridTemplateColumns = 'repeat(3, 1fr)';
    } else if (val === '2x4' || val === '3x4') {
        grid.style.gridTemplateColumns = 'repeat(4, 1fr)';
    }
}

function openPhotoViewer(url, caption) {
    const m = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImage');
    const cap = document.getElementById('photoViewerCaption');
    if (m && img) {
        img.src = url;
        if (cap) cap.innerText = caption || 'Evidencia Fotográfica';
        m.classList.add('open');
    }
}

function closePhotoViewerModal() {
    const m = document.getElementById('photoViewerModal');
    if (m) m.classList.remove('open');
}

function closePhotoViewer() {
    closePhotoViewerModal();
}

let allLocationsMapInstance = null;
function openAllLocationsModal() {
    const m = document.getElementById('allLocationsModal');
    if (!m) return;
    m.classList.add('open');

    setTimeout(() => {
        if (!allLocationsMapInstance) {
            allLocationsMapInstance = L.map('allLocationsMapLeaflet').setView([-16.5000, -68.1500], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(allLocationsMapInstance);
        }

        allLocationsMapInstance.invalidateSize();

        const measurements = window.ALL_MEASUREMENTS_DATA || [];
        const bounds = [];

        measurements.forEach(item => {
            const lat = parseFloat(item.latitude || (item.location && item.location.lat));
            const lng = parseFloat(item.longitude || (item.location && item.location.lng));
            if (!isNaN(lat) && !isNaN(lng)) {
                bounds.push([lat, lng]);
                const marker = L.circleMarker([lat, lng], {
                    radius: 9,
                    fillColor: '#0284c7',
                    color: '#ffffff',
                    weight: 2,
                    opacity: 1,
                    fillOpacity: 0.9
                }).addTo(allLocationsMapInstance);

                marker.bindPopup(`
                    <div style="font-family: Outfit, sans-serif; font-size: 13px;">
                        <strong>Puesto #${item.num || item.point_number}: ${item.puesto_trabajo}</strong><br>
                        <span style="color: #64748b; font-size: 11.5px;">${item.area_sector}</span><br>
                        <div style="margin-top: 6px;">
                            <span style="background: #e0f2fe; color: #0284c7; font-weight: 800; padding: 2px 6px; border-radius: 4px; font-size: 11px;">
                                Score REBA: ${item.score_final} (${item.risk_level})
                            </span>
                        </div>
                    </div>
                `);
            }
        });

        if (bounds.length > 0) {
            allLocationsMapInstance.fitBounds(bounds, { padding: [40, 40] });
        }
    }, 200);
}

function closeAllLocationsModal() {
    const m = document.getElementById('allLocationsModal');
    if (m) m.classList.remove('open');
}

function panToRebaLocation(lat, lng) {
    if (allLocationsMapInstance && !isNaN(lat) && !isNaN(lng)) {
        allLocationsMapInstance.setView([lat, lng], 16, { animate: true });
    }
}

// ==========================================================================
// EXPORTACIÓN EXCEL OFICIAL (.XLSX)
// ==========================================================================
async function downloadRebaExcelPlanilla() {
    if (typeof ExcelJS === 'undefined') {
        alert('Librería ExcelJS no cargada aún. Por favor espera un momento.');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Metric v2 — Pachabol';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Evaluación REBA Oficial', {
        pageSetup: { orientation: 'landscape', paperSize: 9 }
    });

    const cyanHeaderFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0284C7' } };
    const grayHeaderFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };

    sheet.mergeCells('A1:L1');
    const titleCell = sheet.getCell('A1');
    titleCell.value = 'PLANILLA TÉCNICA OFICIAL — EVALUACIÓN ERGONÓMICA REBA (RAPID ENTIRE BODY ASSESSMENT)';
    titleCell.font = { name: 'Calibri', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
    titleCell.fill = cyanHeaderFill;
    sheet.getRow(1).height = 30;

    sheet.mergeCells('A2:D2');
    sheet.getCell('A2').value = `INSTALACIÓN: ${window.TECHNICAL_HEADER_DATA?.installationName || 'No especificada'}`;
    sheet.getCell('A2').font = { bold: true };

    sheet.mergeCells('E2:H2');
    sheet.getCell('E2').value = `FECHAS: ${window.TECHNICAL_HEADER_DATA?.startDateFormatted || '-'} a ${window.TECHNICAL_HEADER_DATA?.endDateFormatted || '-'}`;
    sheet.getCell('E2').font = { bold: true };

    sheet.mergeCells('I2:L2');
    sheet.getCell('I2').value = `MONITOREO: ${window.TECHNICAL_HEADER_DATA?.monitoringType || 'Ergonomía REBA'}`;
    sheet.getCell('I2').font = { bold: true };

    const headers = [
        'N°', 'FECHA', 'ÁREA / SECTOR', 'PUESTO DE TRABAJO',
        'FACTOR DE RIESGO', 'N° TRAB.', 'GRUPO A', 'GRUPO B',
        'TABLA C', 'ACTIVIDAD', 'SCORE REBA', 'NIVEL DE RIESGO'
    ];

    const headerRow = sheet.addRow(headers);
    headerRow.height = 24;
    headerRow.eachCell((cell) => {
        cell.fill = grayHeaderFill;
        cell.font = { bold: true, color: { argb: 'FF0F172A' }, size: 10.5 };
        cell.alignment = { horizontal: 'center', vertical: 'middle' };
        cell.border = {
            top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            bottom: { style: 'medium', color: { argb: 'FF0284C7' } },
            left: { style: 'thin', color: { argb: 'FFCBD5E1' } },
            right: { style: 'thin', color: { argb: 'FFCBD5E1' } }
        };
    });

    const measurements = window.ALL_MEASUREMENTS_DATA || [];
    measurements.forEach((m, idx) => {
        const row = sheet.addRow([
            m.num || (idx + 1),
            m.date || m.measurement_date || '-',
            m.area_sector || '-',
            m.puesto_trabajo || '-',
            m.factor_riesgo || 'Posturas forzadas',
            m.num_trabajadores || 1,
            m.score_a || 1,
            m.score_b || 1,
            m.score_c || 1,
            `+${m.score_actividad || 0}`,
            m.score_final || 1,
            m.risk_level || 'Inapreciable'
        ]);

        row.height = 20;
        row.eachCell((cell, colNumber) => {
            cell.alignment = { vertical: 'middle', horizontal: [1, 2, 6, 7, 8, 9, 10, 11].includes(colNumber) ? 'center' : 'left' };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                right: { style: 'thin', color: { argb: 'FFE2E8F0' } }
            };

            if (colNumber === 11 || colNumber === 12) {
                cell.font = { bold: true };
                const score = parseInt(m.score_final) || 1;
                if (score === 1) {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECFDF5' } };
                    cell.font = { bold: true, color: { argb: 'FF047857' } };
                } else if (score <= 3) {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF7FEE7' } };
                    cell.font = { bold: true, color: { argb: 'FF4D7C0F' } };
                } else if (score <= 7) {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFBEB' } };
                    cell.font = { bold: true, color: { argb: 'FFB45309' } };
                } else if (score <= 10) {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFF7ED' } };
                    cell.font = { bold: true, color: { argb: 'FFC2410C' } };
                } else {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFEF2F2' } };
                    cell.font = { bold: true, color: { argb: 'FFB91C1C' } };
                }
            }
        });
    });

    sheet.columns = [
        { width: 6 }, { width: 13 }, { width: 24 }, { width: 28 },
        { width: 30 }, { width: 10 }, { width: 11 }, { width: 16 },
        { width: 16 }, { width: 12 }, { width: 14 }, { width: 20 }
    ];

    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `Ergonomia_REBA_${window.TECHNICAL_HEADER_DATA?.installationName || 'Estudio'}.xlsx`;
    link.click();
}

// ==========================================================================
// MOTOR DE BÚSQUEDA Y PAGINACIÓN REACTIVA (REBA)
// ==========================================================================
const REBA_PAGE_SIZE = 10;
let currentRebaPage = 1;

function getMatchingRebaRows() {
    const input = document.getElementById('rebaTableSearchInput');
    const searchTerm = input ? input.value.trim().toLowerCase() : '';
    const rows = Array.from(document.querySelectorAll('#rebaMasterTable tbody tr.reba-data-row'));

    return rows.filter(row => {
        const searchData = (row.getAttribute('data-searchable') || '').toLowerCase();
        return !searchTerm || searchData.includes(searchTerm);
    });
}

function updateRebaPagination() {
    const matchingRows = getMatchingRebaRows();
    const allRows = Array.from(document.querySelectorAll('#rebaMasterTable tbody tr.reba-data-row'));
    const totalItems = matchingRows.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / REBA_PAGE_SIZE));

    if (currentRebaPage > totalPages) currentRebaPage = totalPages;
    if (currentRebaPage < 1) currentRebaPage = 1;

    const startIdx = (currentRebaPage - 1) * REBA_PAGE_SIZE;
    const endIdx = startIdx + REBA_PAGE_SIZE;

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

    const startEl = document.getElementById('rebaPageStart');
    const endEl = document.getElementById('rebaPageEnd');
    const totalEl = document.getElementById('rebaPageTotal');

    if (startEl) startEl.textContent = totalItems === 0 ? 0 : (startIdx + 1);
    if (endEl) endEl.textContent = Math.min(endIdx, totalItems);
    if (totalEl) totalEl.textContent = totalItems;

    renderRebaPaginationControls(totalPages);
}

function renderRebaPaginationControls(totalPages) {
    const container = document.getElementById('rebaPaginationControls');
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    html += `<button type="button" class="ill-pag-btn" onclick="goToRebaPage(${currentRebaPage - 1})" ${currentRebaPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    </button>`;

    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentRebaPage - 1 && p <= currentRebaPage + 1)) {
            html += `<button type="button" class="ill-pag-btn ${p === currentRebaPage ? 'active' : ''}" onclick="goToRebaPage(${p})">${p}</button>`;
        } else if (p === currentRebaPage - 2 || p === currentRebaPage + 2) {
            html += `<span style="padding: 0 4px; color: #94a3b8; font-weight: 700;">...</span>`;
        }
    }

    html += `<button type="button" class="ill-pag-btn" onclick="goToRebaPage(${currentRebaPage + 1})" ${currentRebaPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </button>`;

    container.innerHTML = html;
}

function goToRebaPage(page) {
    currentRebaPage = page;
    updateRebaPagination();
    const table = document.getElementById('rebaMasterTable');
    if (table) table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function filterRebaTable() {
    currentRebaPage = 1;
    updateRebaPagination();
}

// ==========================================================================
// INICIALIZACIÓN Y EVENT LISTENERS
// ==========================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar paginación de la tabla
    updateRebaPagination();

    // Escuchar cambios en inputs de radios para actualizar estilos de tarjetas
    document.querySelectorAll('.reba-option-card input').forEach(inp => {
        inp.addEventListener('change', () => {
            const formScope = inp.closest('#editMeasurementForm') ? '#editMeasurementForm' : '#createMeasurementForm';
            if (inp.type === 'radio') {
                document.querySelectorAll(`${formScope} input[name="${inp.name}"]`).forEach(r => {
                    const card = r.closest('.reba-option-card');
                    if (card) card.classList.toggle('selected', r.checked);
                });
            } else {
                const card = inp.closest('.reba-option-card');
                if (card) card.classList.toggle('selected', inp.checked);
            }
        });
    });

    // Cerrar modales con Escape y backdrop click
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop-custom.open').forEach(modal => {
                modal.classList.remove('open');
            });
            applyEditModeState(false);
        }
    });

    document.querySelectorAll('.modal-backdrop-custom').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('open');
                applyEditModeState(false);
            }
        });
    });
});

// EXPORTACIONES GLOBALES PARA ONCLICK INLINE
window.openCreateRebaModal = openCreateRebaModal;
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.toggleModalEditMode = toggleModalEditMode;
window.applyEditModeState = applyEditModeState;
window.slidePhotoNav = slidePhotoNav;
window.selectSlidePhoto = selectSlidePhoto;
window.deleteActivePhoto = deleteActivePhoto;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.syncUtmToMap = syncUtmToMap;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.openRebaTablesModal = openRebaTablesModal;
window.closeRebaTablesModal = closeRebaTablesModal;
window.openFireTablesModal = openRebaTablesModal;
window.closeFireTablesModal = closeRebaTablesModal;
window.switchRebaTab = switchRebaTab;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openPhotoReportModal = openPhotoReportModal;
window.closePhotoReportModal = closePhotoReportModal;
window.changeRebaMosaicGrid = changeRebaMosaicGrid;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewerModal = closePhotoViewerModal;
window.closePhotoViewer = closePhotoViewer;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.panToRebaLocation = panToRebaLocation;
window.filterRebaTable = filterRebaTable;
window.goToRebaPage = goToRebaPage;
window.updateRebaPagination = updateRebaPagination;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.autoSaveHeaderField = autoSaveHeaderField;
window.downloadRebaExcelPlanilla = downloadRebaExcelPlanilla;
window.updateCreateLiveScore = updateCreateLiveScore;
window.updateEditLiveScore = updateEditLiveScore;
window.updateWorkerNamesInputs = updateWorkerNamesInputs;
window.addTareaInput = addTareaInput;
window.removeTareaInput = removeTareaInput;

// Escucha reactiva para estilos de selección en tarjetas de opciones
document.addEventListener('change', (e) => {
    if (e.target.matches('.reba-option-card input[type="radio"]')) {
        const name = e.target.name;
        const form = e.target.closest('form') || document;
        form.querySelectorAll(`input[name="${name}"]`).forEach(r => {
            const card = r.closest('.reba-option-card');
            if (card) card.classList.toggle('selected', r.checked);
        });
    } else if (e.target.matches('.reba-option-card input[type="checkbox"]')) {
        const card = e.target.closest('.reba-option-card');
        if (card) card.classList.toggle('selected', e.target.checked);
    }
});

