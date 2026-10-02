/**
 * ==========================================================================
 * ERGONOMÍA ROSA — MOTOR REACTIVO & DESIGN SYSTEM (METRIC v2)
 * Rapid Office Strain Assessment (ROSA) - 3 Column Layout & Interactive Engine
 * ==========================================================================
 */

// 1. Matrices Normativas ROSA
const ROSA_TABLE_A1 = {
    1: { 1: 1, 2: 2, 3: 3 },
    2: { 1: 2, 2: 3, 3: 4 },
    3: { 1: 3, 2: 4, 3: 5 },
    4: { 1: 4, 2: 5, 3: 6 },
    5: { 1: 5, 2: 6, 3: 7 }
};

const ROSA_TABLE_A2 = {
    1: { 1: 1, 2: 2, 3: 3, 4: 4, 5: 5 },
    2: { 1: 2, 2: 3, 3: 4, 4: 5, 5: 6 },
    3: { 1: 3, 2: 4, 3: 5, 4: 6, 5: 7 },
    4: { 1: 4, 2: 5, 3: 6, 4: 7, 5: 8 },
    5: { 1: 5, 2: 6, 3: 7, 4: 8, 5: 9 }
};

const ROSA_TABLE_A = {
    1: { 1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9 },
    2: { 1: 2, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9 },
    3: { 1: 3, 2: 3, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9 },
    4: { 1: 4, 2: 4, 3: 4, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9 },
    5: { 1: 5, 2: 5, 3: 5, 4: 5, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9 },
    6: { 1: 6, 2: 6, 3: 6, 4: 6, 5: 6, 6: 6, 7: 7, 8: 8, 9: 9 },
    7: { 1: 7, 2: 7, 3: 7, 4: 7, 5: 7, 6: 7, 7: 7, 8: 8, 9: 9 }
};

const ROSA_TABLE_B = {
    1: { 1: 1, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6 },
    2: { 1: 1, 2: 2, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6 },
    3: { 1: 2, 2: 2, 3: 3, 4: 3, 5: 4, 6: 5, 7: 6 },
    4: { 1: 3, 2: 3, 3: 3, 4: 4, 5: 4, 6: 5, 7: 6 },
    5: { 1: 4, 2: 4, 3: 4, 4: 4, 5: 5, 6: 5, 7: 6 },
    6: { 1: 5, 2: 5, 3: 5, 4: 5, 5: 5, 6: 6, 7: 6 },
    7: { 1: 6, 2: 6, 3: 6, 4: 6, 5: 6, 6: 6, 7: 7 }
};

const ROSA_TABLE_C = {
    1: { 1: 1, 2: 1, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6 },
    2: { 1: 1, 2: 2, 3: 2, 4: 3, 5: 4, 6: 5, 7: 6 },
    3: { 1: 2, 2: 2, 3: 3, 4: 3, 5: 4, 6: 5, 7: 6 },
    4: { 1: 3, 2: 3, 3: 3, 4: 4, 5: 4, 6: 5, 7: 6 },
    5: { 1: 4, 2: 4, 3: 4, 4: 4, 5: 5, 6: 5, 7: 6 },
    6: { 1: 5, 2: 5, 3: 5, 4: 5, 5: 5, 6: 6, 7: 6 },
    7: { 1: 6, 2: 6, 3: 6, 4: 6, 5: 6, 6: 6, 7: 7 }
};

const ROSA_TABLE_D = {
    1: { 1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8 },
    2: { 1: 2, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8 },
    3: { 1: 3, 2: 3, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8 },
    4: { 1: 4, 2: 4, 3: 4, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8 },
    5: { 1: 5, 2: 5, 3: 5, 4: 5, 5: 5, 6: 6, 7: 7, 8: 8 },
    6: { 1: 6, 2: 6, 3: 6, 4: 6, 5: 6, 6: 6, 7: 7, 8: 8 },
    7: { 1: 7, 2: 7, 3: 7, 4: 7, 5: 7, 6: 7, 7: 7, 8: 8 },
    8: { 1: 8, 2: 8, 3: 8, 4: 8, 5: 8, 6: 8, 7: 8, 8: 8 }
};

const ROSA_TABLE_E = {
    1:  { 1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10 },
    2:  { 1: 2, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10 },
    3:  { 1: 3, 2: 3, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10 },
    4:  { 1: 4, 2: 4, 3: 4, 4: 4, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10 },
    5:  { 1: 5, 2: 5, 3: 5, 4: 5, 5: 5, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10 },
    6:  { 1: 6, 2: 6, 3: 6, 4: 6, 5: 6, 6: 6, 7: 7, 8: 8, 9: 9, 10: 10 },
    7:  { 1: 7, 2: 7, 3: 7, 4: 7, 5: 7, 6: 7, 7: 7, 8: 8, 9: 9, 10: 10 },
    8:  { 1: 8, 2: 8, 3: 8, 4: 8, 5: 8, 6: 8, 7: 8, 8: 8, 9: 9, 10: 10 },
    9:  { 1: 9, 2: 9, 3: 9, 4: 9, 5: 9, 6: 9, 7: 9, 8: 9, 9: 9, 10: 10 },
    10: { 1: 10, 2: 10, 3: 10, 4: 10, 5: 10, 6: 10, 7: 10, 8: 10, 9: 10, 10: 10 }
};

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
 * Función biomecánica para calcular Puntuaciones y Nivel de Acción ROSA
 */
function calculateRosa(data) {
    // 1. Silla (Tabla A)
    const altBase = parseInt(data.altura_asiento_base) || 1;
    const altMod = (data.asiento_espacio ? 1 : 0) + (data.asiento_no_regulable ? 1 : 0);
    const altFinal = Math.min(5, Math.max(1, altBase + altMod));

    const profBase = parseInt(data.profundidad_base) || 1;
    const profMod = data.profundidad_no_regulable ? 1 : 0;
    const profFinal = Math.min(3, Math.max(1, profBase + profMod));

    const asientoScore = (ROSA_TABLE_A1[altFinal] && ROSA_TABLE_A1[altFinal][profFinal]) || 1;

    const repoBase = parseInt(data.reposabrazos_base) || 1;
    const repoMod = (data.reposabrazos_anchos ? 1 : 0) + (data.reposabrazos_duros ? 1 : 0) + (data.reposabrazos_no_regulable ? 1 : 0);
    const repoFinal = Math.min(5, Math.max(1, repoBase + repoMod));

    const respBase = parseInt(data.respaldo_base) || 1;
    const respMod = (data.respaldo_sin_soporte ? 1 : 0) + (data.respaldo_no_regulable ? 1 : 0);
    const respFinal = Math.min(5, Math.max(1, respBase + respMod));

    const soporteScore = (ROSA_TABLE_A2[repoFinal] && ROSA_TABLE_A2[repoFinal][respFinal]) || 1;

    const asientoIdx = Math.min(7, Math.max(1, asientoScore));
    const soporteIdx = Math.min(9, Math.max(1, soporteScore));
    const sillaBaseScore = (ROSA_TABLE_A[asientoIdx] && ROSA_TABLE_A[asientoIdx][soporteIdx]) || 1;

    const sillaTiempo = parseInt(data.silla_tiempo_uso) || 0;
    const scoreA = Math.min(10, Math.max(1, sillaBaseScore + sillaTiempo));

    // 2. Pantalla y Teléfono (Tabla B)
    const pantBase = parseInt(data.pantalla_base) || 1;
    const pantMod = (data.pantalla_torsion ? 1 : 0) + (data.pantalla_documentos ? 1 : 0);
    const pantTiempo = data.pantalla_tiempo ? 1 : 0;
    const pantScore = Math.min(7, Math.max(1, pantBase + pantMod + pantTiempo));

    const telBase = parseInt(data.telefono_base) || 1;
    const telMod = data.telefono_cuello ? 2 : 0;
    const telTiempo = data.telefono_tiempo ? 1 : 0;
    const telScore = Math.min(7, Math.max(1, telBase + telMod + telTiempo));

    const scoreB = (ROSA_TABLE_B[pantScore] && ROSA_TABLE_B[pantScore][telScore]) || 1;

    // 3. Ratón y Teclado (Tabla C)
    const ratBase = parseInt(data.raton_base) || 1;
    const ratMod = (data.raton_distinto_plano ? 1 : 0) + (data.raton_pequeno ? 1 : 0);
    const ratTiempo = data.raton_tiempo ? 1 : 0;
    const ratScore = Math.min(7, Math.max(1, ratBase + ratMod + ratTiempo));

    const tecBase = parseInt(data.teclado_base) || 1;
    const tecMod = (data.teclado_desviacion ? 1 : 0) + (data.teclado_inclinado ? 1 : 0);
    const tecTiempo = data.teclado_tiempo ? 1 : 0;
    const tecScore = Math.min(7, Math.max(1, tecBase + tecMod + tecTiempo));

    const scoreC = (ROSA_TABLE_C[ratScore] && ROSA_TABLE_C[ratScore][tecScore]) || 1;

    // 4. Periféricos (Tabla D)
    const scoreBIdx = Math.min(8, Math.max(1, scoreB));
    const scoreCIdx = Math.min(8, Math.max(1, scoreC));
    const scoreD = (ROSA_TABLE_D[scoreBIdx] && ROSA_TABLE_D[scoreBIdx][scoreCIdx]) || 1;

    // 5. ROSA Inicial y Final (Tabla E)
    const scoreAIdx = Math.min(10, Math.max(1, scoreA));
    const scoreDIdx = Math.min(10, Math.max(1, scoreD));
    const scoreE = (ROSA_TABLE_E[scoreAIdx] && ROSA_TABLE_E[scoreAIdx][scoreDIdx]) || 1;

    // 6. Score Final ROSA (1-10)
    const scoreFinal = Math.min(10, Math.max(1, scoreE));

    // 7. Clasificación de Riesgo y Acción
    let riskLevel = 'Inapreciable';
    let actionLevel = 'Nivel 1: Postura óptima, no se requiere acción';
    let badgeClass = 'risk-inapreciable';
    let color = '#047857';

    if (scoreFinal <= 2) {
        riskLevel = 'Inapreciable';
        actionLevel = 'Nivel 1: Postura óptima, no se requiere acción';
        badgeClass = 'risk-inapreciable';
        color = '#047857';
    } else if (scoreFinal <= 4) {
        riskLevel = 'Bajo';
        actionLevel = 'Nivel 2: Riesgo bajo, considerar mejoras';
        badgeClass = 'risk-bajo';
        color = '#4d7c0f';
    } else if (scoreFinal === 5) {
        riskLevel = 'Medio (Alerta)';
        actionLevel = 'Nivel 3: Nivel de alerta, es necesaria acción pronto';
        badgeClass = 'risk-medio';
        color = '#b45309';
    } else if (scoreFinal <= 8) {
        riskLevel = 'Alto';
        actionLevel = 'Nivel 4: Es necesaria la acción ergonómica pronto';
        badgeClass = 'risk-alto';
        color = '#c2410c';
    } else {
        riskLevel = 'Muy Alto';
        actionLevel = 'Nivel 5: Es necesaria la intervención ergonómica de inmediato';
        badgeClass = 'risk-muy-alto';
        color = '#b91c1c';
    }

    return {
        scoreA,
        scoreB,
        scoreC,
        scoreD,
        scoreE,
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

    // Habilitar / Deshabilitar Campos de Entrada
    const fieldsToToggle = [
        'edit_measurement_date',
        'edit_measurement_time',
        'edit_staff_id',
        'edit_area_sector',
        'edit_puesto_trabajo',
        'edit_factor_riesgo',
        'edit_num_trabajadores',
        'edit_tiempo_exposicion',
        'edit_procedimiento_escrito',
        'edit_capacitacion',
        'edit_ubicacion_sintoma',
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
function openCreateRosaModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;

    modal.classList.add('open');

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
    openCreateRosaModal();
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

    form.action = `/modulos/${window.MODULE_ID}/ergonomia-rosa/mediciones/${item.id}`;

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

    // Datos del Puesto
    document.getElementById('edit_measurement_date').value = item.raw_date || item.measurement_date || '';
    let rawTime = (item.time && item.time !== '—') ? item.time : (item.measurement_time || '');
    if (rawTime && typeof rawTime === 'string') {
        const timeMatch = rawTime.trim().match(/^(\d{1,2}):(\d{2})/);
        if (timeMatch) {
            rawTime = `${timeMatch[1].padStart(2, '0')}:${timeMatch[2]}`;
        }
    }
    document.getElementById('edit_measurement_time').value = rawTime;
    document.getElementById('edit_area_sector').value = item.area_sector || '';
    document.getElementById('edit_puesto_trabajo').value = item.puesto_trabajo || '';

    setSelectWithFallback(document.getElementById('edit_factor_riesgo'), item.factor_riesgo, 'Trabajo prolongado frente a PVD / Pantallas');
    document.getElementById('edit_num_trabajadores').value = item.num_trabajadores || 1;
    setSelectWithFallback(document.getElementById('edit_tiempo_exposicion'), item.tiempo_exposicion_horas, '8');
    setSelectWithFallback(document.getElementById('edit_procedimiento_escrito'), item.procedimiento_escrito, 'Si');
    setSelectWithFallback(document.getElementById('edit_capacitacion'), item.capacitacion, 'Si');
    setSelectWithFallback(document.getElementById('edit_ubicacion_sintoma'), item.ubicacion_sintoma, 'Ninguna');
    if (document.getElementById('edit_observaciones')) {
        document.getElementById('edit_observaciones').value = item.observaciones || '';
    }

    // Parámetros ROSA
    setRadioValue('altura_asiento_base', item.altura_asiento_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_asiento_espacio', (item.altura_asiento_mod > 0 || item.asiento_espacio));
    setRadioValue('profundidad_base', item.profundidad_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_profundidad_no_regulable', (item.profundidad_mod > 0 || item.profundidad_no_regulable));
    setRadioValue('reposabrazos_base', item.reposabrazos_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_reposabrazos_duros', (item.reposabrazos_mod > 0 || item.reposabrazos_duros));
    setRadioValue('respaldo_base', item.respaldo_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_respaldo_no_regulable', (item.respaldo_mod > 0 || item.respaldo_no_regulable));
    setRadioValue('silla_tiempo_uso', item.silla_tiempo_uso !== undefined ? item.silla_tiempo_uso : 0, '#editMeasurementForm');

    setRadioValue('pantalla_base', item.pantalla_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_pantalla_torsion', (item.pantalla_mod > 0 || item.pantalla_torsion));
    setCheckboxValue('edit_pantalla_tiempo', (item.pantalla_tiempo > 0 || item.pantalla_tiempo));

    setRadioValue('telefono_base', item.telefono_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_telefono_cuello', (item.telefono_mod > 0 || item.telefono_cuello));
    setCheckboxValue('edit_telefono_tiempo', (item.telefono_tiempo > 0 || item.telefono_tiempo));

    setRadioValue('raton_base', item.raton_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_raton_distinto_plano', (item.raton_mod > 0 || item.raton_distinto_plano));
    setCheckboxValue('edit_raton_tiempo', (item.raton_tiempo > 0 || item.raton_tiempo));

    setRadioValue('teclado_base', item.teclado_base || 1, '#editMeasurementForm');
    setCheckboxValue('edit_teclado_desviacion', (item.teclado_mod > 0 || item.teclado_desviacion));
    setCheckboxValue('edit_teclado_tiempo', (item.teclado_tiempo > 0 || item.teclado_tiempo));

    setCheckboxValue('edit_actividad_estatica', item.actividad_estatica > 0);
    setCheckboxValue('edit_actividad_repetitiva', item.actividad_repetitiva > 0);

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
    if (el) el.checked = !!checked;
}

// ==========================================================================
// RECÁLCULO EN TIEMPO REAL ROSA
// ==========================================================================
function updateCreateLiveScore() {
    const data = {
        altura_asiento_base: getRadioValue('altura_asiento_base', '#createMeasurementForm'),
        asiento_espacio: getCheckboxValue('create_asiento_espacio'),
        profundidad_base: getRadioValue('profundidad_base', '#createMeasurementForm'),
        profundidad_no_regulable: getCheckboxValue('create_profundidad_no_regulable'),
        reposabrazos_base: getRadioValue('reposabrazos_base', '#createMeasurementForm'),
        reposabrazos_duros: getCheckboxValue('create_reposabrazos_duros'),
        respaldo_base: getRadioValue('respaldo_base', '#createMeasurementForm'),
        respaldo_no_regulable: getCheckboxValue('create_respaldo_no_regulable'),
        silla_tiempo_uso: getRadioValue('silla_tiempo_uso', '#createMeasurementForm'),
        pantalla_base: getRadioValue('pantalla_base', '#createMeasurementForm'),
        pantalla_torsion: getCheckboxValue('create_pantalla_torsion'),
        pantalla_tiempo: getCheckboxValue('create_pantalla_tiempo'),
        telefono_base: getRadioValue('telefono_base', '#createMeasurementForm'),
        telefono_cuello: getCheckboxValue('create_telefono_cuello'),
        telefono_tiempo: getCheckboxValue('create_telefono_tiempo'),
        raton_base: getRadioValue('raton_base', '#createMeasurementForm'),
        raton_distinto_plano: getCheckboxValue('create_raton_distinto_plano'),
        raton_tiempo: getCheckboxValue('create_raton_tiempo'),
        teclado_base: getRadioValue('teclado_base', '#createMeasurementForm'),
        teclado_desviacion: getCheckboxValue('create_teclado_desviacion'),
        teclado_tiempo: getCheckboxValue('create_teclado_tiempo')
    };

    const res = calculateRosa(data);

    const elA = document.getElementById('create_live_score_a');
    const elB = document.getElementById('create_live_score_b');
    const elC = document.getElementById('create_live_score_c');
    const elD = document.getElementById('create_live_score_d');
    const elFinal = document.getElementById('create_live_score_final');
    const elRisk = document.getElementById('create_live_risk_level');
    const elAction = document.getElementById('create_live_action_level');
    const elBox = document.getElementById('create_live_final_box');

    if (elA) elA.textContent = res.scoreA;
    if (elB) elB.textContent = res.scoreB;
    if (elC) elC.textContent = res.scoreC;
    if (elD) elD.textContent = res.scoreD;
    if (elFinal) elFinal.textContent = res.scoreFinal;
    if (elRisk) elRisk.textContent = `Riesgo ${res.riskLevel}`;
    if (elAction) elAction.textContent = res.actionLevel;
    if (elBox) elBox.className = `reba-live-final-display ${res.badgeClass}`;
}

function updateEditLiveScore() {
    const data = {
        altura_asiento_base: getRadioValue('altura_asiento_base', '#editMeasurementForm'),
        asiento_espacio: getCheckboxValue('edit_asiento_espacio'),
        profundidad_base: getRadioValue('profundidad_base', '#editMeasurementForm'),
        profundidad_no_regulable: getCheckboxValue('edit_profundidad_no_regulable'),
        reposabrazos_base: getRadioValue('reposabrazos_base', '#editMeasurementForm'),
        reposabrazos_duros: getCheckboxValue('edit_reposabrazos_duros'),
        respaldo_base: getRadioValue('respaldo_base', '#editMeasurementForm'),
        respaldo_no_regulable: getCheckboxValue('edit_respaldo_no_regulable'),
        silla_tiempo_uso: getRadioValue('silla_tiempo_uso', '#editMeasurementForm'),
        pantalla_base: getRadioValue('pantalla_base', '#editMeasurementForm'),
        pantalla_torsion: getCheckboxValue('edit_pantalla_torsion'),
        pantalla_tiempo: getCheckboxValue('edit_pantalla_tiempo'),
        telefono_base: getRadioValue('telefono_base', '#editMeasurementForm'),
        telefono_cuello: getCheckboxValue('edit_telefono_cuello'),
        telefono_tiempo: getCheckboxValue('edit_telefono_tiempo'),
        raton_base: getRadioValue('raton_base', '#editMeasurementForm'),
        raton_distinto_plano: getCheckboxValue('edit_raton_distinto_plano'),
        raton_tiempo: getCheckboxValue('edit_raton_tiempo'),
        teclado_base: getRadioValue('teclado_base', '#editMeasurementForm'),
        teclado_desviacion: getCheckboxValue('edit_teclado_desviacion'),
        teclado_tiempo: getCheckboxValue('edit_teclado_tiempo')
    };

    const res = calculateRosa(data);

    const elA = document.getElementById('edit_live_score_a');
    const elB = document.getElementById('edit_live_score_b');
    const elC = document.getElementById('edit_live_score_c');
    const elD = document.getElementById('edit_live_score_d');
    const elFinal = document.getElementById('edit_live_score_final');
    const elRisk = document.getElementById('edit_live_risk_level');
    const elAction = document.getElementById('edit_live_action_level');
    const elBox = document.getElementById('edit_live_final_box');

    if (elA) elA.textContent = res.scoreA;
    if (elB) elB.textContent = res.scoreB;
    if (elC) elC.textContent = res.scoreC;
    if (elD) elD.textContent = res.scoreD;
    if (elFinal) elFinal.textContent = res.scoreFinal;
    if (elRisk) elRisk.textContent = `Riesgo ${res.riskLevel}`;
    if (elAction) elAction.textContent = res.actionLevel;
    if (elBox) elBox.className = `reba-live-final-display ${res.badgeClass}`;
}

// ==========================================================================
// ELIMINACIÓN Y CONFIRMACIÓN
// ==========================================================================
function confirmDeleteMeasurement(id, num) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: `¿Eliminar Evaluación ROSA #${num}?`,
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
                    form.action = `/modulos/${window.MODULE_ID}/ergonomia-rosa/mediciones/${id}`;
                    form.submit();
                }
            }
        });
    } else {
        if (confirm(`¿Estás seguro de eliminar la evaluación ergonómica ROSA del puesto #${num}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = `/modulos/${window.MODULE_ID}/ergonomia-rosa/mediciones/${id}`;
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
            monitoring_type: document.getElementById('inline_monitoring_type')?.value || 'Ergonomía ROSA'
        };

        try {
            const resp = await fetch(window.METRIC_ERGONOMIA_ROSA_CONFIG.updateHeaderUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.METRIC_ERGONOMIA_ROSA_CONFIG.csrfToken
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
            console.error('[Ergonomia ROSA] Error guardando encabezado:', err);
        }
    }, 600);
}

// ==========================================================================
// MODALES ADICIONALES (TABLAS, EXPORTAR, MAPA GENERAL, LIGHTBOX)
// ==========================================================================
function openRosaTablesModal() {
    const m = document.getElementById('rosaTablesModal');
    if (m) m.classList.add('open');
}

function closeRosaTablesModal() {
    const m = document.getElementById('rosaTablesModal');
    if (m) m.classList.remove('open');
}

function switchRosaTab(tabName) {
    document.querySelectorAll('.reba-tab-btn').forEach(b => {
        b.classList.toggle('active', b.dataset.tab === tabName);
    });
    document.querySelectorAll('.reba-tab-pane').forEach(p => {
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

function openPhotoViewer(url, caption) {
    const m = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImage') || document.getElementById('photoViewerImg');
    const cap = document.getElementById('photoViewerCaption') || document.getElementById('photoViewerTitle');
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
                                Score ROSA: ${item.score_final} (${item.risk_level})
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

function panToRosaLocation(lat, lng) {
    if (allLocationsMapInstance && !isNaN(lat) && !isNaN(lng)) {
        allLocationsMapInstance.setView([lat, lng], 16, { animate: true });
    }
}

// ==========================================================================
// EXPORTACIÓN EXCEL OFICIAL (.XLSX)
// ==========================================================================
async function downloadRosaExcelPlanilla() {
    if (typeof ExcelJS === 'undefined') {
        alert('Librería ExcelJS no cargada aún. Por favor espera un momento.');
        return;
    }

    const workbook = new ExcelJS.Workbook();
    workbook.creator = 'Metric v2 — Pachabol';
    workbook.created = new Date();

    const sheet = workbook.addWorksheet('Evaluación ROSA Oficial', {
        pageSetup: { orientation: 'landscape', paperSize: 9 }
    });

    const cyanHeaderFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0284C7' } };
    const grayHeaderFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };

    sheet.mergeCells('A1:L1');
    const titleCell = sheet.getCell('A1');
    titleCell.value = 'PLANILLA TÉCNICA OFICIAL — EVALUACIÓN ERGONÓMICA ROSA (RAPID OFFICE STRAIN ASSESSMENT)';
    titleCell.font = { name: 'Calibri', size: 14, bold: true, color: { argb: 'FFFFFFFF' } };
    titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
    titleCell.fill = cyanHeaderFill;
    sheet.getRow(1).height = 30;

    sheet.mergeCells('A2:D2');
    sheet.getCell('A2').value = `INSTALACIÓN: ${window.TECHNICAL_HEADER_DATA.installationName || 'No especificada'}`;
    sheet.getCell('A2').font = { bold: true };

    sheet.mergeCells('E2:H2');
    sheet.getCell('E2').value = `FECHAS: ${window.TECHNICAL_HEADER_DATA.startDateFormatted || '-'} a ${window.TECHNICAL_HEADER_DATA.endDateFormatted || '-'}`;
    sheet.getCell('E2').font = { bold: true };

    sheet.mergeCells('I2:L2');
    sheet.getCell('I2').value = `MONITOREO: ${window.TECHNICAL_HEADER_DATA.monitoringType || 'Ergonomía ROSA'}`;
    sheet.getCell('I2').font = { bold: true };

    const headers = [
        'N°', 'FECHA', 'ÁREA / SECTOR', 'PUESTO DE TRABAJO',
        'FACTOR DE RIESGO', 'N° TRAB.', 'SILLA (A)', 'PANTALLA/TEL (B)',
        'RATÓN/TECL (C)', 'PERIF. (D)', 'SCORE ROSA', 'NIVEL DE RIESGO'
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
            m.factor_riesgo || 'Posturas frente a PVD',
            m.num_trabajadores || 1,
            m.score_a || 1,
            m.score_b || 1,
            m.score_c || 1,
            m.score_d || 1,
            m.score_final || 1,
            m.risk_level || 'Inapreciable'
        ]);

        row.height = 20;
        row.eachCell((cell, colNumber) => {
            cell.alignment = { vertical: 'middle', horizontal: colNumber in [1, 2, 6, 7, 8, 9, 10, 11] ? 'center' : 'left' };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                right: { style: 'thin', color: { argb: 'FFE2E8F0' } }
            };

            if (colNumber === 11 || colNumber === 12) {
                cell.font = { bold: true };
                const score = parseInt(m.score_final) || 1;
                if (score <= 2) {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFECFDF5' } };
                    cell.font = { bold: true, color: { argb: 'FF047857' } };
                } else if (score <= 4) {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF7FEE7' } };
                    cell.font = { bold: true, color: { argb: 'FF4D7C0F' } };
                } else if (score === 5) {
                    cell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFFBEB' } };
                    cell.font = { bold: true, color: { argb: 'FFB45309' } };
                } else if (score <= 8) {
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
    link.download = `Ergonomia_ROSA_${window.TECHNICAL_HEADER_DATA.installationName || 'Estudio'}.xlsx`;
    link.click();
}

// ==========================================================================
// MOTOR DE BÚSQUEDA Y PAGINACIÓN REACTIVA (ROSA)
// ==========================================================================
const ROSA_PAGE_SIZE = 10;
let currentRosaPage = 1;

function getMatchingRosaRows() {
    const input = document.getElementById('rosaTableSearchInput');
    const searchTerm = input ? input.value.trim().toLowerCase() : '';
    const rows = Array.from(document.querySelectorAll('#rosaMasterTable tbody tr.rosa-data-row'));

    return rows.filter(row => {
        const searchData = (row.getAttribute('data-searchable') || '').toLowerCase();
        return !searchTerm || searchData.includes(searchTerm);
    });
}

function updateRosaPagination() {
    const matchingRows = getMatchingRosaRows();
    const allRows = Array.from(document.querySelectorAll('#rosaMasterTable tbody tr.rosa-data-row'));
    const totalItems = matchingRows.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / ROSA_PAGE_SIZE));

    if (currentRosaPage > totalPages) currentRosaPage = totalPages;
    if (currentRosaPage < 1) currentRosaPage = 1;

    const startIdx = (currentRosaPage - 1) * ROSA_PAGE_SIZE;
    const endIdx = startIdx + ROSA_PAGE_SIZE;

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

    const startEl = document.getElementById('rosaPageStart');
    const endEl = document.getElementById('rosaPageEnd');
    const totalEl = document.getElementById('rosaPageTotal');

    if (startEl) startEl.textContent = totalItems === 0 ? 0 : (startIdx + 1);
    if (endEl) endEl.textContent = Math.min(endIdx, totalItems);
    if (totalEl) totalEl.textContent = totalItems;

    renderRosaPaginationControls(totalPages);
}

function renderRosaPaginationControls(totalPages) {
    const container = document.getElementById('rosaPaginationControls');
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    html += `<button type="button" class="ill-pag-btn" onclick="goToRosaPage(${currentRosaPage - 1})" ${currentRosaPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
    </button>`;

    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentRosaPage - 1 && p <= currentRosaPage + 1)) {
            html += `<button type="button" class="ill-pag-btn ${p === currentRosaPage ? 'active' : ''}" onclick="goToRosaPage(${p})">${p}</button>`;
        } else if (p === currentRosaPage - 2 || p === currentRosaPage + 2) {
            html += `<span style="padding: 0 4px; color: #94a3b8; font-weight: 700;">...</span>`;
        }
    }

    html += `<button type="button" class="ill-pag-btn" onclick="goToRosaPage(${currentRosaPage + 1})" ${currentRosaPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
    </button>`;

    container.innerHTML = html;
}

function goToRosaPage(page) {
    currentRosaPage = page;
    updateRosaPagination();
    const table = document.getElementById('rosaMasterTable');
    if (table) table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function filterRosaTable() {
    currentRosaPage = 1;
    updateRosaPagination();
}

// ==========================================================================
// INICIALIZACIÓN Y EVENT LISTENERS
// ==========================================================================
document.addEventListener('DOMContentLoaded', () => {
    // Inicializar paginación de la tabla
    updateRosaPagination();

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
window.openCreateRosaModal = openCreateRosaModal;
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
window.openRosaTablesModal = openRosaTablesModal;
window.closeRosaTablesModal = closeRosaTablesModal;
window.switchRosaTab = switchRosaTab;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openPhotoReportModal = openPhotoReportModal;
window.closePhotoReportModal = closePhotoReportModal;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewerModal = closePhotoViewerModal;
window.closePhotoViewer = closePhotoViewer;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.panToRosaLocation = panToRosaLocation;
window.filterRosaTable = filterRosaTable;
window.goToRosaPage = goToRosaPage;
window.updateRosaPagination = updateRosaPagination;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.autoSaveHeaderField = autoSaveHeaderField;
window.downloadRosaExcelPlanilla = downloadRosaExcelPlanilla;
window.updateCreateLiveScore = updateCreateLiveScore;
window.updateEditLiveScore = updateEditLiveScore;
