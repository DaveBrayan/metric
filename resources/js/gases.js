/**
 * METRIC V2 — Monitoreo de Gases Ocupacionales y Ambientales
 * Módulo JavaScript interactivo y controlador de modales (Vite ES Module / Blade Compatible)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_GASES_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/gases/header');

/* ==========================================================================
   1. DEFINICIÓN DE LOS 13 GASES NORMATIVOS
   ========================================================================== */
const GAS_DEFINITIONS = {
    o2: { key: 'o2', name: 'Oxígeno', formula: 'O2', unit: 'ppm' },
    h2s: { key: 'h2s', name: 'Ácido Sulfhídrico', formula: 'H2S', unit: 'ppm' },
    co: { key: 'co', name: 'Monóxidos de Carbono', formula: 'CO', unit: 'ppm' },
    lel: { key: 'lel', name: 'Gases combustibles', formula: 'LEL', unit: '%' },
    hcho: { key: 'hcho', name: 'Formaldehidos', formula: 'HCHO', unit: 'mg/m3' },
    tvoc: { key: 'tvoc', name: 'Compuesto Orgánicos Volátiles', formula: 'T-VOC', unit: 'mg/m3' },
    co2: { key: 'co2', name: 'Dióxido de Carbono', formula: 'CO2', unit: 'ppm' },
    as: { key: 'as', name: 'Arsénico inorgánico', formula: 'As', unit: 'mg/m3' },
    so2: { key: 'so2', name: 'Dioxido de azufre', formula: 'SO2', unit: 'ppm' },
    nh3: { key: 'nh3', name: 'Amoniaco', formula: 'NH3', unit: 'ppm' },
    cl2: { key: 'cl2', name: 'Cloro gaseoso', formula: 'Cl2', unit: 'ppm' },
    tcov: { key: 'tcov', name: 'Compuestos organicos volatiles', formula: 'TCOV', unit: 'ppm' },
    no2: { key: 'no2', name: 'Dioxido de nitrogeno', formula: 'NO2', unit: 'ppm' }
};

/* ==========================================================================
   2. GESTIÓN DINÁMICA DE TARJETAS DE GASES (MEDICIONES 1, 2, 3 & PROMEDIO)
   ========================================================================== */
let createSelectedGases = [];
let editSelectedGases = [];
let createGasesReadings = {};
let editGasesReadings = {};
let isEditUnlocked = false;

function addGasCardFromSelect(prefix) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    const selectEl = document.getElementById(`${prefix}_gas_to_add_select`);
    if (!selectEl) return;
    const gasKey = selectEl.value;
    if (!gasKey || !GAS_DEFINITIONS[gasKey]) return;

    const selectedList = (prefix === 'create') ? createSelectedGases : editSelectedGases;
    if (selectedList.includes(gasKey)) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Gas ya agregado',
                text: `El gas ${GAS_DEFINITIONS[gasKey].name} (${GAS_DEFINITIONS[gasKey].formula}) ya está en la lista.`,
                icon: 'info',
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            alert('Este gas ya está agregado en el formulario.');
        }
        selectEl.value = '';
        return;
    }

    selectedList.push(gasKey);
    const readingsObj = (prefix === 'create') ? createGasesReadings : editGasesReadings;
    if (!readingsObj[gasKey]) {
        readingsObj[gasKey] = { m1: null, m2: null, m3: null, prom: null };
    }

    renderDynamicGasCards(prefix);
    selectEl.value = '';
}

function removeGasCard(prefix, gasKey) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    const selectedList = (prefix === 'create') ? createSelectedGases : editSelectedGases;
    const idx = selectedList.indexOf(gasKey);
    if (idx !== -1) {
        selectedList.splice(idx, 1);
    }
    const readingsObj = (prefix === 'create') ? createGasesReadings : editGasesReadings;
    delete readingsObj[gasKey];

    renderDynamicGasCards(prefix);
}

function onGasMeasurementInput(prefix, gasKey) {
    const m1Input = document.getElementById(`${prefix}_${gasKey}_m1`);
    const m2Input = document.getElementById(`${prefix}_${gasKey}_m2`);
    const m3Input = document.getElementById(`${prefix}_${gasKey}_m3`);
    const promDisplay = document.getElementById(`${prefix}_${gasKey}_prom_disp`);

    const v1 = (m1Input && m1Input.value.trim() !== '') ? parseFloat(m1Input.value) : null;
    const v2 = (m2Input && m2Input.value.trim() !== '') ? parseFloat(m2Input.value) : null;
    const v3 = (m3Input && m3Input.value.trim() !== '') ? parseFloat(m3Input.value) : null;

    const validValues = [v1, v2, v3].filter(v => v !== null && !isNaN(v));
    let prom = null;
    if (validValues.length > 0) {
        const sum = validValues.reduce((acc, curr) => acc + curr, 0);
        prom = parseFloat((sum / validValues.length).toFixed(2));
    }

    const readingsObj = (prefix === 'create') ? createGasesReadings : editGasesReadings;
    if (!readingsObj[gasKey]) {
        readingsObj[gasKey] = {};
    }
    readingsObj[gasKey].m1 = (v1 !== null && !isNaN(v1)) ? v1 : null;
    readingsObj[gasKey].m2 = (v2 !== null && !isNaN(v2)) ? v2 : null;
    readingsObj[gasKey].m3 = (v3 !== null && !isNaN(v3)) ? v3 : null;
    readingsObj[gasKey].prom = (prom !== null && !isNaN(prom)) ? prom : null;

    if (promDisplay) {
        promDisplay.textContent = (prom !== null && !isNaN(prom)) ? Number(prom).toFixed(2) : '—';
    }

    syncGasesHiddenInputs(prefix);
}

function renderDynamicGasCards(prefix) {
    const container = document.getElementById(`${prefix}_dynamic_gases_container`);
    const badgeCount = document.getElementById(`${prefix}_selected_gases_count_badge`);
    if (!container) return;

    const selectedList = (prefix === 'create') ? createSelectedGases : editSelectedGases;
    const readingsObj = (prefix === 'create') ? createGasesReadings : editGasesReadings;
    const isEditMode = (prefix === 'edit');
    const isReadOnly = isEditMode && !isEditUnlocked;

    if (badgeCount) {
        badgeCount.textContent = `${selectedList.length} seleccionados`;
    }

    if (selectedList.length === 0) {
        container.innerHTML = `
            <div class="empty-gases-msg">
                No hay gases seleccionados para este punto. Selecciona uno arriba para ingresar sus 3 mediciones.
            </div>
        `;
        syncGasesHiddenInputs(prefix);
        return;
    }

    let html = '';
    selectedList.forEach(gasKey => {
        const gas = GAS_DEFINITIONS[gasKey] || { name: gasKey.toUpperCase(), formula: gasKey.toUpperCase(), unit: 'ppm' };
        const data = readingsObj[gasKey] || { m1: null, m2: null, m3: null, prom: null };
        const m1Val = (data.m1 !== null && data.m1 !== undefined && !isNaN(data.m1)) ? data.m1 : '';
        const m2Val = (data.m2 !== null && data.m2 !== undefined && !isNaN(data.m2)) ? data.m2 : '';
        const m3Val = (data.m3 !== null && data.m3 !== undefined && !isNaN(data.m3)) ? data.m3 : '';
        const promText = (data.prom !== null && data.prom !== undefined && !isNaN(data.prom)) ? Number(data.prom).toFixed(2) : '—';

        html += `
            <div class="gas-single-card" id="${prefix}_card_${gasKey}">
                <div class="gas-card-header">
                    <div class="gas-card-title-group">
                        <span class="gas-title-name">${gas.name} (${gas.formula})</span>
                        <span class="gas-unit-tag">${gas.unit}</span>
                    </div>
                    ${!isReadOnly ? `
                        <button type="button" class="btn-remove-gas" onclick="removeGasCard('${prefix}', '${gasKey}')" title="Quitar este gas">✕</button>
                    ` : ''}
                </div>
                <div class="gas-measurements-row">
                    <div class="gas-input-box">
                        <label>MED. 1</label>
                        <input type="number" step="0.001" id="${prefix}_${gasKey}_m1" class="custom-form-input"
                            value="${m1Val}" ${isReadOnly ? 'disabled' : ''}
                            oninput="onGasMeasurementInput('${prefix}', '${gasKey}')" placeholder="0.00">
                    </div>
                    <div class="gas-input-box">
                        <label>MED. 2</label>
                        <input type="number" step="0.001" id="${prefix}_${gasKey}_m2" class="custom-form-input"
                            value="${m2Val}" ${isReadOnly ? 'disabled' : ''}
                            oninput="onGasMeasurementInput('${prefix}', '${gasKey}')" placeholder="0.00">
                    </div>
                    <div class="gas-input-box">
                        <label>MED. 3</label>
                        <input type="number" step="0.001" id="${prefix}_${gasKey}_m3" class="custom-form-input"
                            value="${m3Val}" ${isReadOnly ? 'disabled' : ''}
                            oninput="onGasMeasurementInput('${prefix}', '${gasKey}')" placeholder="0.00">
                    </div>
                    <div class="gas-promedio-box">
                        <label>PROMEDIO</label>
                        <div class="gas-promedio-badge" id="${prefix}_${gasKey}_prom_disp">${promText}</div>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    syncGasesHiddenInputs(prefix);
}

function syncGasesHiddenInputs(prefix) {
    const selectedList = (prefix === 'create') ? createSelectedGases : editSelectedGases;
    const readingsObj = (prefix === 'create') ? createGasesReadings : editGasesReadings;

    const selJson = document.getElementById(`${prefix}_selected_gases_json`);
    const readJson = document.getElementById(`${prefix}_gases_readings_json`);

    if (selJson) {
        selJson.value = JSON.stringify(selectedList);
    }
    if (readJson) {
        const cleanReadings = {};
        selectedList.forEach(k => {
            if (readingsObj[k]) {
                cleanReadings[k] = {
                    values: [readingsObj[k].m1, readingsObj[k].m2, readingsObj[k].m3],
                    prom: readingsObj[k].prom
                };
            }
        });
        readJson.value = JSON.stringify(cleanReadings);
    }
}

/* ==========================================================================
   3. GESTIÓN DEL CARRUSEL / VISOR DE FOTOGRAFÍAS
   ========================================================================== */
const modalPhotos = { create: [], edit: [] };
const modalPhotoIndex = { create: 0, edit: 0 };

async function compressImageBrowser(file, maxWidth = 1600, quality = 0.85) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                let width = img.width;
                let height = img.height;
                if (width > maxWidth) {
                    height = Math.round((height * maxWidth) / width);
                    width = maxWidth;
                }
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                canvas.toBlob((blob) => {
                    resolve(new File([blob], file.name, { type: 'image/jpeg', lastModified: Date.now() }));
                }, 'image/jpeg', quality);
            };
            img.onerror = () => resolve(file);
        };
        reader.onerror = () => resolve(file);
    });
}

async function handleMultipleImagesSelected(inputEl, prefix) {
    if (!inputEl.files || inputEl.files.length === 0) return;
    const files = Array.from(inputEl.files);

    const btnTrigger = document.getElementById(`${prefix}_btn_add_photos`);
    const submitBtn = document.getElementById(`${prefix}_modal_submit_btn`);
    const originalBtnHtml = btnTrigger ? btnTrigger.innerHTML : '';

    if (btnTrigger) {
        btnTrigger.disabled = true;
        btnTrigger.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin-icon">
                <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
            </svg>
            <span>Optimizando ${files.length} foto(s)...</span>
        `;
    }
    if (submitBtn) submitBtn.disabled = true;

    try {
        const dt = new DataTransfer();
        for (let i = 0; i < files.length; i++) {
            const f = files[i];
            const compressed = await compressImageBrowser(f);
            dt.items.add(compressed);

            const reader = new FileReader();
            reader.onload = (e) => {
                modalPhotos[prefix].push(e.target.result);
                modalPhotoIndex[prefix] = modalPhotos[prefix].length - 1;
                renderPhotoSlider(prefix);
            };
            reader.readAsDataURL(compressed);
        }
        inputEl.files = dt.files;
    } catch (e) {
        console.error('Error procesando imágenes:', e);
    } finally {
        if (btnTrigger) {
            btnTrigger.disabled = false;
            btnTrigger.innerHTML = originalBtnHtml;
        }
        if (submitBtn) submitBtn.disabled = false;
    }
}

function renderPhotoSlider(prefix) {
    const photos = modalPhotos[prefix] || [];
    const idx = modalPhotoIndex[prefix] || 0;
    const count = photos.length;

    const viewport = document.getElementById(`${prefix}_slider_viewport`);
    const counterBadge = document.getElementById(`${prefix}_slider_counter`);
    const prevBtn = document.getElementById(`${prefix}_slider_btn_prev`);
    const nextBtn = document.getElementById(`${prefix}_slider_btn_next`);
    const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
    const photoCountIndicator = document.getElementById(`${prefix}_photo_count_indicator`);
    const mainImg = document.getElementById(`${prefix}_slider_img`);
    const placeholder = document.getElementById(`${prefix}_slider_placeholder`);

    if (photoCountIndicator) {
        photoCountIndicator.textContent = `${count} foto${count !== 1 ? 's' : ''}`;
    }

    if (!viewport) return;

    if (count === 0) {
        if (mainImg) { mainImg.style.display = 'none'; mainImg.src = ''; }
        if (placeholder) placeholder.style.display = 'flex';
        if (counterBadge) counterBadge.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'none';
        if (nextBtn) nextBtn.style.display = 'none';
        if (thumbsStrip) {
            thumbsStrip.innerHTML = '';
            thumbsStrip.style.display = 'none';
        }
        return;
    }

    if (placeholder) placeholder.style.display = 'none';
    if (mainImg) {
        mainImg.src = photos[idx];
        mainImg.style.display = 'block';
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
            let thumbsHtml = '';
            photos.forEach((src, i) => {
                const showDel = (prefix === 'create' || isEditUnlocked);
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
    const hidden = document.getElementById('edit_remaining_images_json');
    if (hidden) {
        const existingRemaining = (modalPhotos.edit || []).filter(p => !p.startsWith('data:'));
        hidden.value = JSON.stringify(existingRemaining);
    }
}

/* ==========================================================================
   4. CONVERSIÓN Y GESTIÓN DE COORDENADAS UTM (WGS84) & MINI MAPAS
   ========================================================================== */
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
    let zoneNum = 20;
    let zoneLetter = 'K';
    if (typeof zoneStr === 'string') {
        const m = zoneStr.match(/(\d+)\s*([A-Za-z]?)/);
        if (m) {
            zoneNum = parseInt(m[1], 10) || 20;
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
    const zVal = (zInput ? zInput.value.trim() : '') || '20K';

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

    const map = (prefix === 'create') ? createMinimap : editMinimap;
    const marker = (prefix === 'create') ? createMarker : editMarker;
    if (map && marker) {
        marker.setLatLng([pos.lat, pos.lng]);
        map.panTo([pos.lat, pos.lng]);
    }
}

let createMinimap = null;
let editMinimap = null;
let createMarker = null;
let editMarker = null;
let allLocationsMap = null;
let allLocationsMarkersGroup = null;
let currentSingleMap = null;
let currentSingleMarker = null;

function initModalMinimap(prefix, lat = -16.5000, lng = -68.1500) {
    const mapId = `${prefix}_modal_map`;
    const container = document.getElementById(mapId);
    if (!container || typeof L === 'undefined') return;

    let map = (prefix === 'create') ? createMinimap : editMinimap;
    if (map) {
        map.remove();
    }

    map = L.map(mapId, {
        center: [lat, lng],
        zoom: 14,
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    const marker = L.marker([lat, lng], {
        draggable: (prefix === 'create' || isEditUnlocked)
    }).addTo(map);

    marker.on('dragend', function () {
        const position = marker.getLatLng();
        updateUtmFromLatLng(prefix, position.lat, position.lng);
    });

    map.on('click', function (e) {
        if (prefix === 'edit' && !isEditUnlocked) return;
        marker.setLatLng(e.latlng);
        updateUtmFromLatLng(prefix, e.latlng.lat, e.latlng.lng);
    });

    if (prefix === 'create') {
        createMinimap = map;
        createMarker = marker;
    } else {
        editMinimap = map;
        editMarker = marker;
    }

    setTimeout(() => {
        map.invalidateSize();
    }, 250);
}

function getCurrentGpsPosition(latInputId, lngInputId, prefix) {
    if (prefix === 'edit' && !isEditUnlocked) return;
    if (!navigator.geolocation) {
        alert('La geolocalización no es soportada por su navegador.');
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateUtmFromLatLng(prefix, lat, lng);
            const map = (prefix === 'create') ? createMinimap : editMinimap;
            const marker = (prefix === 'create') ? createMarker : editMarker;
            if (map && marker) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 16);
            }
        },
        (err) => {
            console.warn('Error al obtener GPS:', err);
            alert('No se pudo obtener la ubicación GPS actual.');
        },
        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    );
}

/* ==========================================================================
   5. CONTROLADORES DE MODALES (CREACIÓN, EDICIÓN & CONSULTA)
   ========================================================================== */
function openCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (!modal) return;
    modal.classList.add('open');

    createSelectedGases = [];
    createGasesReadings = {};
    modalPhotos.create = [];
    modalPhotoIndex.create = 0;

    renderDynamicGasCards('create');
    renderPhotoSlider('create');

    const latInput = document.getElementById('create_latitude');
    const lngInput = document.getElementById('create_longitude');
    let lat = -16.5000;
    let lng = -68.1500;
    if (latInput && lngInput && latInput.value && lngInput.value) {
        lat = parseFloat(latInput.value);
        lng = parseFloat(lngInput.value);
    }
    initModalMinimap('create', lat, lng);
}

function closeCreateMeasurementModal() {
    const modal = document.getElementById('createMeasurementModal');
    if (modal) modal.classList.remove('open');
}

function openEditMeasurementModal(item) {
    const modal = document.getElementById('editMeasurementModal');
    if (!modal) return;

    isEditUnlocked = false;
    modal.classList.add('open');

    const form = document.getElementById('editMeasurementForm');
    if (form) {
        form.action = `/modulos/${MODULE_ID}/gases/mediciones/${item.id}`;
    }

    const ptNumDisp = document.getElementById('edit_pt_num_disp');
    const ptNumInput = document.getElementById('edit_point_number');
    const dateInput = document.getElementById('edit_measurement_date');
    const timeInput = document.getElementById('edit_measurement_time');
    const staffSelect = document.getElementById('edit_staff_id');
    const regByBadge = document.getElementById('edit_modal_registered_by');
    const areaInput = document.getElementById('edit_area');
    const workInput = document.getElementById('edit_workstation');
    const pointInput = document.getElementById('edit_measurement_point');
    const actInput = document.getElementById('edit_activity_description');
    const obsInput = document.getElementById('edit_observations');

    if (ptNumDisp) ptNumDisp.textContent = item.num || item.point_number || '01';
    if (ptNumInput) ptNumInput.value = item.num || item.point_number || '01';
    if (dateInput) dateInput.value = item.raw_date || item.date || (item.measurement_date ? item.measurement_date.substring(0, 10) : '');
    if (timeInput) timeInput.value = item.time && item.time !== '—' ? item.time.substring(0, 5) : (item.measurement_time ? item.measurement_time.substring(0, 5) : '');
    if (staffSelect && item.staff_id) staffSelect.value = item.staff_id;
    if (regByBadge) regByBadge.textContent = item.registered_by || REGISTERED_BY_HEADER || 'Técnico de Campo';
    if (areaInput) areaInput.value = item.area && item.area !== '—' ? item.area : '';
    if (workInput) workInput.value = item.workstation && item.workstation !== '—' ? item.workstation : '';
    if (pointInput) pointInput.value = item.measurement_point && item.measurement_point !== '—' ? item.measurement_point : '';
    if (actInput) actInput.value = item.activity_description || '';
    if (obsInput) obsInput.value = (item.raw_observations !== undefined && item.raw_observations !== null) ? item.raw_observations : (item.observations && item.observations !== 'Sin observaciones' ? item.observations : '');

    const tempInput = document.getElementById('edit_temperatura');
    const presInput = document.getElementById('edit_presion_atm');
    const velInput = document.getElementById('edit_vel_aire');
    if (tempInput) tempInput.value = item.temperatura !== null && item.temperatura !== undefined ? item.temperatura : '';
    if (presInput) presInput.value = item.presion_atm !== null && item.presion_atm !== undefined ? item.presion_atm : '';
    if (velInput) velInput.value = item.vel_aire !== null && item.vel_aire !== undefined ? item.vel_aire : '';

    // Extracción inteligente de gases seleccionados y lecturas
    editSelectedGases = [];
    editGasesReadings = {};

    let rawSel = item.selected_gases;
    if (typeof rawSel === 'string') {
        try { rawSel = JSON.parse(rawSel); } catch (e) { rawSel = []; }
    }
    if (Array.isArray(rawSel)) {
        rawSel.forEach(k => {
            if (typeof k === 'string' && GAS_DEFINITIONS[k] && !editSelectedGases.includes(k)) {
                editSelectedGases.push(k);
            }
        });
    }

    let rawReadings = item.gases_readings;
    if (typeof rawReadings === 'string') {
        try { rawReadings = JSON.parse(rawReadings); } catch (e) { rawReadings = {}; }
    }
    if (rawReadings && typeof rawReadings === 'object') {
        Object.keys(rawReadings).forEach(k => {
            if (GAS_DEFINITIONS[k] && !editSelectedGases.includes(k)) {
                editSelectedGases.push(k);
            }
        });
    }

    // Comprobar también columnas individuales tipo o2_values, o2_prom, etc.
    Object.keys(GAS_DEFINITIONS).forEach(k => {
        let colVals = item[`${k}_values`];
        let colProm = item[`${k}_prom`];
        if (typeof colVals === 'string') {
            try { colVals = JSON.parse(colVals); } catch(e) {}
        }
        if ((Array.isArray(colVals) && colVals.length > 0) || (colProm !== null && colProm !== undefined && colProm !== '')) {
            if (!editSelectedGases.includes(k)) {
                editSelectedGases.push(k);
            }
        }
    });

    // Armar editGasesReadings para cada gas seleccionado
    editSelectedGases.forEach(gasKey => {
        let m1 = null;
        let m2 = null;
        let m3 = null;
        let prom = null;

        // 1. De gases_readings
        if (rawReadings && rawReadings[gasKey]) {
            const gr = rawReadings[gasKey];
            if (Array.isArray(gr)) {
                m1 = (gr[0] !== undefined && gr[0] !== null && gr[0] !== '') ? parseFloat(gr[0]) : null;
                m2 = (gr[1] !== undefined && gr[1] !== null && gr[1] !== '') ? parseFloat(gr[1]) : null;
                m3 = (gr[2] !== undefined && gr[2] !== null && gr[2] !== '') ? parseFloat(gr[2]) : null;
            } else if (typeof gr === 'object') {
                if (Array.isArray(gr.values)) {
                    m1 = (gr.values[0] !== undefined && gr.values[0] !== null && gr.values[0] !== '') ? parseFloat(gr.values[0]) : null;
                    m2 = (gr.values[1] !== undefined && gr.values[1] !== null && gr.values[1] !== '') ? parseFloat(gr.values[1]) : null;
                    m3 = (gr.values[2] !== undefined && gr.values[2] !== null && gr.values[2] !== '') ? parseFloat(gr.values[2]) : null;
                } else {
                    if (gr.m1 !== undefined && gr.m1 !== null && gr.m1 !== '') m1 = parseFloat(gr.m1);
                    if (gr.m2 !== undefined && gr.m2 !== null && gr.m2 !== '') m2 = parseFloat(gr.m2);
                    if (gr.m3 !== undefined && gr.m3 !== null && gr.m3 !== '') m3 = parseFloat(gr.m3);
                }
                if (gr.prom !== undefined && gr.prom !== null && gr.prom !== '') {
                    prom = parseFloat(parseFloat(gr.prom).toFixed(2));
                }
            }
        }

        // 2. Fallback de columnas individuales
        if (m1 === null && m2 === null && m3 === null) {
            let colVals = item[`${gasKey}_values`];
            if (typeof colVals === 'string') {
                try { colVals = JSON.parse(colVals); } catch(e) {}
            }
            if (Array.isArray(colVals)) {
                if (colVals[0] !== undefined && colVals[0] !== null && colVals[0] !== '') m1 = parseFloat(colVals[0]);
                if (colVals[1] !== undefined && colVals[1] !== null && colVals[1] !== '') m2 = parseFloat(colVals[1]);
                if (colVals[2] !== undefined && colVals[2] !== null && colVals[2] !== '') m3 = parseFloat(colVals[2]);
            }
        }

        if (item[`${gasKey}_m1`] !== undefined && item[`${gasKey}_m1`] !== null && item[`${gasKey}_m1`] !== '') m1 = parseFloat(item[`${gasKey}_m1`]);
        if (item[`${gasKey}_m2`] !== undefined && item[`${gasKey}_m2`] !== null && item[`${gasKey}_m2`] !== '') m2 = parseFloat(item[`${gasKey}_m2`]);
        if (item[`${gasKey}_m3`] !== undefined && item[`${gasKey}_m3`] !== null && item[`${gasKey}_m3`] !== '') m3 = parseFloat(item[`${gasKey}_m3`]);

        if (prom === null && item[`${gasKey}_prom`] !== undefined && item[`${gasKey}_prom`] !== null && item[`${gasKey}_prom`] !== '') {
            prom = parseFloat(parseFloat(item[`${gasKey}_prom`]).toFixed(2));
        }

        // Si tenemos lecturas pero no prom, calcular promedio
        const validValues = [m1, m2, m3].filter(v => v !== null && !isNaN(v));
        if (validValues.length > 0) {
            const sum = validValues.reduce((a, b) => a + b, 0);
            prom = parseFloat((sum / validValues.length).toFixed(2));
        }

        editGasesReadings[gasKey] = {
            m1: (m1 !== null && !isNaN(m1)) ? m1 : null,
            m2: (m2 !== null && !isNaN(m2)) ? m2 : null,
            m3: (m3 !== null && !isNaN(m3)) ? m3 : null,
            prom: (prom !== null && !isNaN(prom)) ? prom : null
        };
    });

    renderDynamicGasCards('edit');

    // Manejo de fotografías para carrusel
    modalPhotos.edit = [];
    if (Array.isArray(item.images) && item.images.length > 0) {
        modalPhotos.edit = item.images.map(p => p.startsWith('http') || p.startsWith('/') ? p : `/storage/${p}`);
    } else if (Array.isArray(item.photos) && item.photos.length > 0) {
        modalPhotos.edit = item.photos.map(p => p.startsWith('http') || p.startsWith('/') ? p : `/storage/${p}`);
    } else if (item.image_path) {
        modalPhotos.edit = [item.image_path.startsWith('http') || item.image_path.startsWith('/') ? item.image_path : `/storage/${item.image_path}`];
    }
    modalPhotoIndex.edit = 0;
    renderPhotoSlider('edit');
    syncEditRemainingImages();

    // Ubicación & GPS (UTM WGS84)
    let lat = -16.5000;
    let lng = -68.1500;
    const latInput = document.getElementById('edit_latitude');
    const lngInput = document.getElementById('edit_longitude');
    const eInput = document.getElementById('edit_utm_easting');
    const nInput = document.getElementById('edit_utm_northing');
    const zInput = document.getElementById('edit_utm_zone');
    const disp = document.getElementById('edit_utm_display');
    const locInput = document.getElementById('edit_location');

    if (item.latitude && item.longitude) {
        lat = parseFloat(item.latitude);
        lng = parseFloat(item.longitude);
        updateUtmFromLatLng('edit', lat, lng);
    } else if (item.utm_easting && item.utm_northing) {
        const eVal = parseFloat(item.utm_easting);
        const nVal = parseFloat(item.utm_northing);
        const zVal = item.utm_zone || '20K';
        if (eInput) eInput.value = eVal.toFixed(3);
        if (nInput) nInput.value = nVal.toFixed(3);
        if (zInput) zInput.value = zVal;
        const pos = utmToLatLng(eVal, nVal, zVal);
        lat = pos.lat;
        lng = pos.lng;
        if (latInput) latInput.value = lat.toFixed(7);
        if (lngInput) lngInput.value = lng.toFixed(7);
        const formatted = `E: ${eVal.toFixed(3)}, N: ${nVal.toFixed(3)}, Z: ${zVal}`;
        if (disp) disp.textContent = formatted;
        if (locInput) locInput.value = formatted;
    } else {
        if (eInput) eInput.value = '';
        if (nInput) nInput.value = '';
        if (zInput) zInput.value = '20K';
        if (disp) disp.textContent = 'E: —, N: —, Z: 20K';
    }

    initModalMinimap('edit', lat, lng);

    // Inicializar siempre en Modo Consulta (Solo Lectura)
    updateEditModalMode(false);
}

function closeEditMeasurementModal() {
    const modal = document.getElementById('editMeasurementModal');
    if (modal) modal.classList.remove('open');
}

function toggleEditLock(unlock = null) {
    if (unlock !== null) {
        isEditUnlocked = unlock;
    } else {
        isEditUnlocked = !isEditUnlocked;
    }
    updateEditModalMode(isEditUnlocked);
}

function updateEditModalMode(unlocked) {
    isEditUnlocked = unlocked;
    const modal = document.getElementById('editMeasurementModal');
    const form = document.getElementById('editMeasurementForm');
    const badge = document.getElementById('edit_modal_mode_badge');
    const toggleBtn = document.getElementById('edit_btn_toggle_edit');
    const submitBtn = document.getElementById('edit_modal_submit_btn');
    const addPhotosBtn = document.getElementById('edit_btn_add_photos');
    const addGasBar = document.getElementById('edit_add_gas_bar');
    const gpsBtn = document.getElementById('edit_btn_gps');

    if (unlocked) {
        if (form) form.classList.remove('modal-view-mode');
        if (badge) {
            badge.className = 'modal-badge-edit';
            badge.textContent = 'Modo Edición';
        }
        if (toggleBtn) {
            toggleBtn.className = 'btn-modal-edit-action active-editing';
            toggleBtn.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                <span>Bloquear Edición</span>
            `;
        }
        if (submitBtn) submitBtn.style.display = 'inline-flex';
        if (addPhotosBtn) addPhotosBtn.style.display = 'inline-flex';
        if (addGasBar) addGasBar.style.display = 'flex';
        if (gpsBtn) gpsBtn.style.display = 'inline-flex';
    } else {
        if (form) form.classList.add('modal-view-mode');
        if (badge) {
            badge.className = 'modal-badge-view';
            badge.textContent = 'Solo Lectura';
        }
        if (toggleBtn) {
            toggleBtn.className = 'btn-modal-edit-action';
            toggleBtn.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                <span>Editar</span>
            `;
        }
        if (submitBtn) submitBtn.style.display = 'none';
        if (addPhotosBtn) addPhotosBtn.style.display = 'none';
        if (addGasBar) addGasBar.style.display = 'none';
        if (gpsBtn) gpsBtn.style.display = 'none';
    }

    if (modal) {
        const formInputs = modal.querySelectorAll('input:not([type="hidden"]), select, textarea');
        formInputs.forEach(input => {
            if (input.id !== 'edit_point_number') {
                input.disabled = !unlocked;
            }
        });
    }

    renderDynamicGasCards('edit');
    renderPhotoSlider('edit');

    if (editMarker) {
        if (unlocked && editMarker.dragging) {
            editMarker.dragging.enable();
        } else if (editMarker && editMarker.dragging) {
            editMarker.dragging.disable();
        }
    }
}

function confirmDeleteMeasurement(id, pointNumber) {
    if (typeof Swal === 'undefined') {
        if (confirm(`¿Está seguro de eliminar el punto de monitoreo N° ${pointNumber}? Esta acción no se puede deshacer.`)) {
            const form = document.getElementById('deleteMeasurementForm');
            if (form) {
                form.action = `/modulos/${MODULE_ID}/gases/mediciones/${id}`;
                form.submit();
            }
        }
        return;
    }

    Swal.fire({
        title: `¿Eliminar Punto N° ${pointNumber}?`,
        text: 'Se eliminarán todas las lecturas de gases y fotografías asociadas permanentemente.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, Eliminar',
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
                form.action = `/modulos/${MODULE_ID}/gases/mediciones/${id}`;
                form.submit();
            }
        }
    });
}

/* ==========================================================================
   6. MAPA DE TODAS LAS UBICACIONES, MAPA INDIVIDUAL & VISOR DE FOTOS
   ========================================================================== */
function openAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (!modal) return;
    modal.classList.add('open');

    setTimeout(() => {
        initAllLocationsMap();
    }, 250);
}

function closeAllLocationsModal() {
    const modal = document.getElementById('allLocationsModal');
    if (modal) modal.classList.remove('open');
}

function initAllLocationsMap() {
    const mapId = 'allLocationsMapLeaflet';
    const container = document.getElementById(mapId);
    if (!container || typeof L === 'undefined') return;

    if (allLocationsMap) {
        allLocationsMap.remove();
    }

    allLocationsMap = L.map(mapId, {
        center: [-16.5000, -68.1500],
        zoom: 13,
        zoomControl: true,
        attributionControl: false
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(allLocationsMap);

    allLocationsMarkersGroup = L.featureGroup().addTo(allLocationsMap);

    const measurements = ALL_MEASUREMENTS_DATA || [];
    let validCoordsCount = 0;

    measurements.forEach(item => {
        if (item.latitude && item.longitude) {
            const lat = parseFloat(item.latitude);
            const lng = parseFloat(item.longitude);
            if (!isNaN(lat) && !isNaN(lng)) {
                validCoordsCount++;
                const marker = L.marker([lat, lng]);

                let gasesHtml = '';
                if (Array.isArray(item.selected_gases) && item.selected_gases.length > 0) {
                    gasesHtml = item.selected_gases.map(g => `<span class="gas-badge-tag">${(GAS_DEFINITIONS[g]?.formula || g).toUpperCase()}</span>`).join(' ');
                }

                marker.bindPopup(`
                    <div style="font-family: inherit; font-size: 12px; min-width: 180px;">
                        <div style="font-weight: 800; font-size: 13px; color: #0369a1; margin-bottom: 4px;">Punto N° ${item.num || item.point_number || ''}</div>
                        <div><strong>Área:</strong> ${item.area || '—'}</div>
                        <div><strong>Puesto:</strong> ${item.workstation || '—'}</div>
                        <div><strong>Lugar:</strong> ${item.measurement_point || '—'}</div>
                        ${gasesHtml ? `<div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 3px;">${gasesHtml}</div>` : ''}
                    </div>
                `);

                allLocationsMarkersGroup.addLayer(marker);
            }
        }
    });

    if (validCoordsCount > 0) {
        allLocationsMap.fitBounds(allLocationsMarkersGroup.getBounds().pad(0.2));
    }
}

function focusPointOnAllMap(id, lat, lng) {
    if (!allLocationsMap || lat === null || lng === null || isNaN(lat) || isNaN(lng)) return;
    allLocationsMap.setView([lat, lng], 17);
}

function focusPointOnAllLocationsMap(idx) {
    if (!allLocationsMap) return;
    const measurements = ALL_MEASUREMENTS_DATA || [];
    const item = measurements[idx];
    if (!item) return;
    const lat = parseFloat(item.latitude);
    const lng = parseFloat(item.longitude);
    if (!isNaN(lat) && !isNaN(lng)) {
        allLocationsMap.setView([lat, lng], 17);
    }
}

function openMapModal(pointName, locationDesc, lat, lng, photoUrl = '') {
    const modal = document.getElementById('mapLocationModal');
    if (!modal) return;

    const nameEl = document.getElementById('mapCardPointName');
    const descEl = document.getElementById('mapCardLocationDesc');
    const coordsEl = document.getElementById('mapCardCoords');
    if (nameEl) nameEl.textContent = pointName || 'Punto de Gases';
    if (descEl) descEl.textContent = locationDesc || 'Ubicación física';
    if (coordsEl) coordsEl.textContent = `${lat}, ${lng}`;

    const gmapsBtn = document.getElementById('openInGoogleMapsBtn');
    if (gmapsBtn) gmapsBtn.href = `https://www.google.com/maps?q=${lat},${lng}`;

    const photoWrap = document.getElementById('mapModalPhotoThumbWrap');
    const photoImg = document.getElementById('mapModalPhotoImg');
    if (photoUrl && photoWrap && photoImg) {
        photoImg.src = photoUrl;
        photoWrap.style.display = 'flex';
    } else if (photoWrap) {
        photoWrap.style.display = 'none';
    }

    modal.classList.add('open');

    setTimeout(() => {
        const container = document.getElementById('mapContainerLeaflet');
        if (!container || typeof L === 'undefined') return;

        if (currentSingleMap) currentSingleMap.remove();

        currentSingleMap = L.map('mapContainerLeaflet', {
            center: [lat, lng],
            zoom: 16,
            zoomControl: true,
            attributionControl: false
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19
        }).addTo(currentSingleMap);

        currentSingleMarker = L.marker([lat, lng]).addTo(currentSingleMap);
    }, 250);
}

function closeMapModal() {
    const modal = document.getElementById('mapLocationModal');
    if (modal) modal.classList.remove('open');
}

function expandCurrentMapPhoto() {
    const photoImg = document.getElementById('mapModalPhotoImg');
    if (photoImg && photoImg.src) {
        openPhotoViewer(photoImg.src, 'Fotografía del Punto');
    }
}

function openPhotoViewer(src, caption = '') {
    const modal = document.getElementById('photoViewerModal');
    const img = document.getElementById('photoViewerImg');
    const title = document.getElementById('photoViewerTitle');
    if (!modal || !img) return;

    img.src = src;
    if (title) title.textContent = caption || 'Fotografía de Medición de Gases';
    modal.classList.add('open');
}

function closePhotoViewer() {
    const modal = document.getElementById('photoViewerModal');
    if (modal) modal.classList.remove('open');
}

function openExportModal() {
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (modal) modal.classList.add('open');
}

function closeExportModal() {
    const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
    if (modal) modal.classList.remove('open');
}

function openPhotoReportConfigModal() {
    closeExportModal();
    const modal = document.getElementById('photoReportModal');
    if (modal) modal.classList.add('open');
}

function closePhotoReportConfigModal() {
    const modal = document.getElementById('photoReportModal');
    if (modal) modal.classList.remove('open');
}

function setPhotoGridPreset(preset) {
    document.querySelectorAll('.photo-grid-pill').forEach(pill => {
        pill.classList.toggle('active', pill.getAttribute('data-grid') === preset);
    });
}

function setPhotoOrientation(orient) {
    document.querySelectorAll('.photo-orient-pill').forEach(pill => {
        pill.classList.toggle('active', pill.getAttribute('data-orient') === orient);
    });
}

function selectAllPhotoReportPoints(selectAll) {
    document.querySelectorAll('.pr-point-checkbox').forEach(cb => {
        cb.checked = selectAll;
    });
}

function generatePhotoReportPdf() {
    const form = document.getElementById('photoReportForm');
    if (form) {
        form.submit();
    }
}

/* ==========================================================================
   7. ENCABEZADO TÉCNICO DUAL CON EDICIÓN INLINE DIRECTA
   ========================================================================== */
function editHeaderField(field) {
    const dispEl = document.getElementById(`disp_${field}`);
    const editEl = document.getElementById(`edit_${field}`);
    const btnEl = document.getElementById(`btn_edit_${field}`);
    if (!dispEl || !editEl) return;

    dispEl.style.display = 'none';
    editEl.style.display = 'block';
    if (btnEl) btnEl.style.display = 'none';

    const input = editEl.querySelector('input, select, textarea');
    if (input) {
        input.focus();
        if (input.select) input.select();
    }
}

function cancelHeaderEdit(field) {
    const dispEl = document.getElementById(`disp_${field}`);
    const editEl = document.getElementById(`edit_${field}`);
    const btnEl = document.getElementById(`btn_edit_${field}`);
    if (!dispEl || !editEl) return;

    editEl.style.display = 'none';
    dispEl.style.display = 'inline-flex';
    if (btnEl) btnEl.style.display = 'inline-flex';
}

async function saveHeaderField(field) {
    const editEl = document.getElementById(`edit_${field}`);
    const input = editEl ? editEl.querySelector('input, select, textarea') : null;
    if (!input) return;

    const value = input.value;
    const dispEl = document.getElementById(`disp_${field}`);
    const valText = dispEl ? dispEl.querySelector('.header-display-val') : null;

    try {
        const payload = {};
        payload[field] = value;

        const response = await fetch(UPDATE_HEADER_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();
        if (data.success) {
            if (valText) {
                if (input.tagName === 'SELECT') {
                    valText.textContent = input.options[input.selectedIndex].text;
                } else {
                    valText.textContent = value || '—';
                }
            }
            cancelHeaderEdit(field);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Encabezado actualizado',
                    showConfirmButton: false,
                    timer: 2000
                });
            }
        } else {
            alert(data.message || 'Error al actualizar el campo');
        }
    } catch (e) {
        console.error('Error guardando encabezado:', e);
        alert('Error de conexión al actualizar el campo');
    }
}

/* ==========================================================================
   8. EXPOSICIÓN GLOBAL A WINDOW PARA COMPATIBILIDAD CON BLADE INLINE ONCLICK
   ========================================================================== */
window.addGasCardFromSelect = addGasCardFromSelect;
window.removeGasCard = removeGasCard;
window.onGasMeasurementInput = onGasMeasurementInput;
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.toggleEditLock = toggleEditLock;
window.updateEditModalMode = updateEditModalMode;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.slidePhotoNav = slidePhotoNav;
window.selectSlidePhoto = selectSlidePhoto;
window.deleteActivePhoto = deleteActivePhoto;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.syncUtmToMap = syncUtmToMap;
window.updateUtmFromLatLng = updateUtmFromLatLng;
window.latLngToUtm = latLngToUtm;
window.utmToLatLng = utmToLatLng;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.focusPointOnAllMap = focusPointOnAllMap;
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;
window.openMapModal = openMapModal;
window.closeMapModal = closeMapModal;
window.expandCurrentMapPhoto = expandCurrentMapPhoto;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewer = closePhotoViewer;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openPhotoReportConfigModal = openPhotoReportConfigModal;
window.closePhotoReportConfigModal = closePhotoReportConfigModal;
window.setPhotoGridPreset = setPhotoGridPreset;
window.setPhotoOrientation = setPhotoOrientation;
window.selectAllPhotoReportPoints = selectAllPhotoReportPoints;
window.generatePhotoReportPdf = generatePhotoReportPdf;
window.editHeaderField = editHeaderField;
window.saveHeaderField = saveHeaderField;
window.cancelHeaderEdit = cancelHeaderEdit;

/* ==========================================================================
   9. BÚSQUEDA Y FILTRADO EN TABLA PRINCIPAL DE GASES
   ========================================================================== */
document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchMeasurementInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.gases-data-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const searchData = (row.getAttribute('data-search') || '').toLowerCase();
                const matches = searchData.includes(query);
                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            const noResults = document.getElementById('noResultsSearchRow');
            if (noResults) {
                noResults.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
            }
        });
    }

    // Modal backdrop click handlers
    document.querySelectorAll('.modal-backdrop-custom').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) {
                backdrop.classList.remove('open');
            }
        });
    });
});
