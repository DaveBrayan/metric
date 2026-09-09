/**
 * METRIC V2 — Monitoreo de Iluminación Ocupacional
 * Módulo JavaScript interactivo y controlador de modales (Vite ES Module)
 */

// Inicialización de configuración del servidor
const cfg = window.METRIC_ILLUMINATION_CONFIG || {};
const MODULE_ID = cfg.moduleId || window.MODULE_ID;
const CSRF_TOKEN = cfg.csrfToken || window.CSRF_TOKEN || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const ALL_MEASUREMENTS_DATA = cfg.measurements || window.ALL_MEASUREMENTS_DATA || [];
const TECHNICAL_HEADER_DATA = cfg.technicalHeader || window.TECHNICAL_HEADER_DATA || {};
const PHOTO_REPORT_INITIAL_SETTINGS = cfg.photoReportSettings || window.PHOTO_REPORT_INITIAL_SETTINGS || {};
const REGISTERED_BY_HEADER = cfg.registeredByHeader || window.REGISTERED_BY_HEADER || '';
const UPDATE_HEADER_URL = cfg.updateHeaderUrl || window.UPDATE_HEADER_URL || ('/modulos/' + MODULE_ID + '/iluminacion/header');

        /* ==========================================================================
           DATOS NORMATIVOS PARA NIVEL REQUERIDO Y LEYENDA DINÁMICA
           ========================================================================== */
        const NORMATIVE_DATA = {
            "25": { area: "Paso en construcción", app: "Pasillos y vías en obras." },
            "50": { area: "Tránsito general", app: "Pasillos, almacenes y baños." },
            "75": { area: "Trabajo en construcción", app: "Áreas operativas dentro de la obra." },
            "100": { area: "Tareas simples", app: "Mover materiales y supervisar maquinaria." },
            "300": { area: "Oficinas y talleres", app: "Computadoras, lectura, escritura y trabajos comunes." },
            "750": { area: "Finos y detalle", app: "Pintura de detalle e inspección de piezas pequeñas." },
            "1500": { area: "Alta precisión", app: "Ensamble de piezas minúsculas o diminutas." },
            "3000": { area: "Casos especiales", app: "Joyería, electrónica fina y cirugías." }
        };

        const ACTIVITY_TO_LUX = {
            "Paso en construcción": 25,
            "Tránsito general": 50,
            "Trabajo en construcción": 75,
            "Tareas simples": 100,
            "Oficinas y talleres": 300,
            "Finos y detalle": 750,
            "Alta precisión": 1500,
            "Casos especiales": 3000
        };

        const LUX_TO_ACTIVITY = {
            "25": "Paso en construcción",
            "50": "Tránsito general",
            "75": "Trabajo en construcción",
            "100": "Tareas simples",
            "300": "Oficinas y talleres",
            "750": "Finos y detalle",
            "1500": "Alta precisión",
            "3000": "Casos especiales"
        };

        function onActivityDescriptionChange(prefix) {
            const actSelect = document.getElementById(`${prefix}_activity_description`);
            const reqSelect = document.getElementById(`${prefix}_required_lux`);
            if (!actSelect || !reqSelect) return;

            const activity = actSelect.value;
            if (ACTIVITY_TO_LUX[activity]) {
                reqSelect.value = String(ACTIVITY_TO_LUX[activity]);
            }
            updateNormativeLegend(prefix);
            renderReadingsBadges(prefix);
        }

        function onRequiredLuxChange(prefix) {
            const actSelect = document.getElementById(`${prefix}_activity_description`);
            const reqSelect = document.getElementById(`${prefix}_required_lux`);
            if (!reqSelect) return;

            const val = String(parseInt(reqSelect.value) || 300);
            if (actSelect && LUX_TO_ACTIVITY[val]) {
                actSelect.value = LUX_TO_ACTIVITY[val];
            }
            updateNormativeLegend(prefix);
            renderReadingsBadges(prefix);
        }

        function updateNormativeLegend(prefix) {
            const select = document.getElementById(`${prefix}_required_lux`);
            const areaEl = document.getElementById(`${prefix}_legend_area`);
            const badgeEl = document.getElementById(`${prefix}_legend_badge`);
            const appEl = document.getElementById(`${prefix}_legend_app`);
            if (!select) return;

            const val = String(parseInt(select.value) || 300);
            if (NORMATIVE_DATA[val]) {
                if (areaEl) areaEl.textContent = NORMATIVE_DATA[val].area;
                if (badgeEl) badgeEl.textContent = `${val} LUX Mínimo`;
                if (appEl) appEl.textContent = NORMATIVE_DATA[val].app;
            } else {
                if (areaEl) areaEl.textContent = "Personalizado";
                if (badgeEl) badgeEl.textContent = `${select.value} LUX`;
                if (appEl) appEl.textContent = "Valor normativo específico según criterio técnico.";
            }
        }

        /* ==========================================================================
           GESTIÓN DE MEDICIONES LUX (HASTA 25 PUNTOS EN BADGES)
           ========================================================================== */
        let createReadingsList = [];
        let editReadingsList = [];
        let isEditUnlocked = false;

        function addReadingPoint(prefix) {
            if (prefix === 'edit' && !isEditUnlocked) return;
            const list = (prefix === 'create') ? createReadingsList : editReadingsList;
            if (list.length >= 25) {
                alert('Límite alcanzado: Máximo 25 puntos de medición.');
                return;
            }

            const input = document.getElementById(`${prefix}_quick_lux_input`);
            if (!input) return;
            const val = parseFloat(input.value);
            if (isNaN(val) || val < 0) {
                alert('Ingrese un valor numérico de LUX válido.');
                input.focus();
                return;
            }

            list.push(val);
            input.value = '';
            input.focus();
            renderReadingsBadges(prefix);
        }

        function removeReadingPoint(prefix, index) {
            if (prefix === 'edit' && !isEditUnlocked) return;
            const list = (prefix === 'create') ? createReadingsList : editReadingsList;
            list.splice(index, 1);
            renderReadingsBadges(prefix);
        }

        function renderReadingsBadges(prefix) {
            const list = (prefix === 'create') ? createReadingsList : editReadingsList;
            const container = document.getElementById(`${prefix}_readings_badges_container`);
            const countBadge = document.getElementById(`${prefix}_readings_count_badge`);
            const displayEl = document.getElementById(`${prefix}_calc_lux_display`);
            const hiddenJson = document.getElementById(`${prefix}_readings_json`);
            const hiddenMeasured = document.getElementById(`${prefix}_measured_lux`);
            const reqSelect = document.getElementById(`${prefix}_required_lux`);

            if (!container) return;

            if (countBadge) countBadge.textContent = `${list.length}/25`;
            if (hiddenJson) hiddenJson.value = JSON.stringify(list);

            if (list.length === 0) {
                container.innerHTML = `<span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas agregadas. Ingrese valores (hasta 25 puntos).</span>`;
                if (displayEl) displayEl.textContent = '0.0 LUX';
                if (hiddenMeasured) hiddenMeasured.value = '0';
                const strip = document.getElementById(`${prefix}_readings_summary_strip`);
                if (strip) {
                    strip.innerHTML = `
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span>Mín: <strong style="color: #94a3b8;">—</strong></span>
                            <span style="color: #cbd5e1;">|</span>
                            <span>Máx: <strong style="color: #94a3b8;">—</strong></span>
                            <span style="color: #cbd5e1;">|</span>
                            <span>Promedio: <strong id="${prefix}_calc_lux_display" style="color: #94a3b8;">0.0 LUX</strong></span>
                        </div>
                        <div>
                            <span style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Sin datos</span>
                        </div>
                    `;
                }
                return;
            }

            let sum = 0;
            let badgesHtml = '';
            const canDelete = (prefix === 'create' || isEditUnlocked);

            list.forEach((val, idx) => {
                sum += val;
                badgesHtml += `
                    <span class="lux-reading-badge">
                        <span>#${idx + 1}: <strong>${val.toFixed(1)}</strong></span>
                        ${canDelete ? `<button type="button" onclick="removeReadingPoint('${prefix}', ${idx})" title="Eliminar lectura #${idx + 1}">✕</button>` : ''}
                    </span>
                `;
            });

            container.innerHTML = badgesHtml;

            const minVal = Math.min(...list);
            const maxVal = Math.max(...list);
            const avg = sum / list.length;
            const req = reqSelect ? (parseFloat(reqSelect.value) || 300) : 300;
            const compliant = avg >= req;

            if (hiddenMeasured) hiddenMeasured.value = avg.toFixed(1);

            const strip = document.getElementById(`${prefix}_readings_summary_strip`);
            if (strip) {
                strip.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span>Mín: <strong style="color: var(--ink); font-weight: 800;">${minVal.toFixed(1)} LUX</strong></span>
                        <span style="color: #cbd5e1;">|</span>
                        <span>Máx: <strong style="color: var(--ink); font-weight: 800;">${maxVal.toFixed(1)} LUX</strong></span>
                        <span style="color: #cbd5e1;">|</span>
                        <span>Promedio: <strong id="${prefix}_calc_lux_display" style="color: #0284c7; font-weight: 800; font-size: 12.5px;">${avg.toFixed(1)} LUX</strong></span>
                    </div>
                    <div>
                        <span style="font-size: 10.5px; padding: 2px 8px; border-radius: 4px; font-weight: 800; display: inline-flex; align-items: center; gap: 3px; background: ${compliant ? '#ecfdf5' : '#fef2f2'}; color: ${compliant ? '#059669' : '#dc2626'}; border: 1px solid ${compliant ? '#a7f3d0' : '#fecaca'};">
                            ${compliant
                        ? '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> CUMPLE'
                        : '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> NO CUMPLE'
                    }
                        </span>
                    </div>
                `;
            }
        }

        /* ==========================================================================
           ARCHIVO FOTOGRÁFICO SLIDE / CARRUSEL CONTINUO (MÚLTIPLES FOTOS)
           ========================================================================== */
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
                img.onclick = () => openPhotoViewer(photos[idx], `Fotografía ${idx + 1} de ${count}`);
                img.style.cursor = 'zoom-in';
            }

            if (counter) {
                counter.textContent = `${idx + 1} / ${count}`;
                counter.style.display = 'inline-block';
            }

            // Botones de navegación previa / siguiente
            if (prevBtn) prevBtn.style.display = count > 1 ? 'grid' : 'none';
            if (nextBtn) nextBtn.style.display = count > 1 ? 'grid' : 'none';

            // Tira de miniaturas con botón para eliminar en modo edición
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

        /**
         * Eliminar una fotografía cargada (específico para edición o creación)
         */
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

        /**
         * Variable global para sincronizar el estado de compresión y prevenir envíos prematuros
         */
        let isOptimizingPhotos = false;

        /**
         * Compresión inteligente y adaptativa según peso (bytes) y dimensiones (px).
         * No aplica la misma compresión a todas las imágenes:
         * - Nivel 0 (<= 1.2 MB y <= 1920px): Se mantiene intacta original (0% pérdida de calidad).
         * - Nivel 1 (1.2MB - 4MB o <= 2800px): Máxima fidelidad 2.5K QHD (2560px), calidad 0.93 (visualmente lossless).
         * - Nivel 2 (4MB - 9MB o <= 4500px): Escala a 2200px con calidad 0.90 (mantiene textos, bordes y lecturas de luxómetro nítidos).
         * - Nivel 3 (> 9MB o sensores móviles 48MP+/8000px): Escala a 2048px con calidad 0.88 y remuestreo bicúbico de alta precisión.
         */
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

                        // NIVEL 0: ÓPTIMA / LIVIANA
                        // Si ya pesa <= 1.2 MB y sus dimensiones no superan 1920px (Full HD),
                        // no se recomprime en lo absoluto para no degradar generaciones ni perder nitidez.
                        if (fileSize <= 1.2 * 1024 * 1024 && maxDim <= 1920) {
                            return resolve(file);
                        }

                        // Determinar parámetros adaptativos según peso y resolución
                        let targetMaxDim;
                        let targetQuality;

                        if (fileSize <= 4 * 1024 * 1024 && maxDim <= 2800) {
                            // NIVEL 1: 1.2 MB a 4 MB o hasta 2.8K
                            // Excelente resolución (2560px QHD), calidad 0.93 (lossless visual)
                            targetMaxDim = 2560;
                            targetQuality = 0.93;
                        } else if (fileSize <= 9 * 1024 * 1024 && maxDim <= 4500) {
                            // NIVEL 2: 4 MB a 9 MB o hasta 4.5K
                            // Escala a 2200px, calidad 0.90 (pantallas de luxómetros y sellos nítidos)
                            targetMaxDim = 2200;
                            targetQuality = 0.90;
                        } else {
                            // NIVEL 3: > 9 MB o fotos de smartphone 48MP/64MP/108MP (6000x8000+)
                            // Escala a 2048px (ideal para reportes Carta de alta resolución 300 DPI), calidad 0.88
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

                        // Si las dimensiones no cambiaron y el peso ya es razonable (< 1.5MB), no recomprimir
                        if (newWidth === width && newHeight === height && fileSize <= 1.5 * 1024 * 1024) {
                            return resolve(file);
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = newWidth;
                        canvas.height = newHeight;
                        const ctx = canvas.getContext('2d');

                        // Suavizado bicúbico de alta fidelidad para nitidez de texto y pantalla de luxómetro
                        ctx.imageSmoothingEnabled = true;
                        ctx.imageSmoothingQuality = 'high';

                        // Fondo blanco para imágenes transparentes PNG/WebP convertidas a JPG
                        ctx.fillStyle = '#ffffff';
                        ctx.fillRect(0, 0, newWidth, newHeight);

                        ctx.drawImage(img, 0, 0, newWidth, newHeight);

                        canvas.toBlob((blob) => {
                            if (!blob || blob.size >= fileSize) {
                                // Si el blob generado no es más liviano que el original, conservar original
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

            // Feedback visual en el botón selector de fotos
            const btnTrigger = document.getElementById(`${prefix}_btn_add_photos`);
            const originalBtnHtml = btnTrigger ? btnTrigger.innerHTML : '';
            if (btnTrigger) {
                btnTrigger.disabled = true;
                btnTrigger.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    <span>Optimizando según peso y calidad...</span>
                `;
            }

            // Deshabilitar botón submit del modal mientras se optimiza
            const submitBtn = document.getElementById(`${prefix}_modal_submit_btn`);
            if (submitBtn) submitBtn.disabled = true;

            try {
                if (prefix === 'create') {
                    modalPhotos.create = [];
                }

                // Optimización adaptativa individual para cada foto
                const optimizedFiles = [];
                for (const file of rawFiles) {
                    const opt = await compressImageFile(file);
                    optimizedFiles.push(opt);
                }

                // Reasignar los archivos optimizados al input mediante DataTransfer
                try {
                    const dt = new DataTransfer();
                    optimizedFiles.forEach(f => dt.items.add(f));
                    input.files = dt.files;
                } catch (err) {
                    console.warn('DataTransfer no soportado en este navegador', err);
                }

                // Cargar en el slider interactivo
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
                if (submitBtn) submitBtn.disabled = false;
            }
        }

        /* ==========================================================================
           CONVERSIÓN Y GESTIÓN DE COORDENADAS UTM (WGS84) & MINI MAPAS
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
                northing += 10000000; // Falso norte para hemisferio sur (Bolivia)
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

            // Mover marcador en el mini mapa del modal
            if (prefix === 'create' && createModalMarker) {
                createModalMarker.setLatLng([pos.lat, pos.lng]);
                if (createModalMap) createModalMap.panTo([pos.lat, pos.lng]);
            } else if (prefix === 'edit' && editModalMarker) {
                editModalMarker.setLatLng([pos.lat, pos.lng]);
                if (editModalMap) editModalMap.panTo([pos.lat, pos.lng]);
            }
        }

        let createModalMap = null;
        let createModalMarker = null;
        let editModalMap = null;
        let editModalMarker = null;

        function initModalMiniMap(prefix, lat, lng) {
            const mapContainerId = `${prefix}_modal_map`;
            const container = document.getElementById(mapContainerId);
            if (!container) return;

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

        /* ==========================================================================
           AUTO-GUARDADO DIRECTO DEL ENCABEZADO TÉCNICO
           ========================================================================== */
        let autoSaveTimeout = null;

        function autoSaveHeaderField() {
            const badge = document.getElementById('headerAutoSaveBadge');
            if (badge) {
                badge.classList.add('visible', 'saving');
                badge.innerHTML = `
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin-slow"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                    <span>Guardando...</span>
                `;
            }

            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                const installationName = document.getElementById('inline_installation_name')?.value || '';
                const startDate = document.getElementById('inline_start_date')?.value || '';
                const endDate = document.getElementById('inline_end_date')?.value || '';
                const monitoringType = document.getElementById('inline_monitoring_type')?.value || '';

                fetch(UPDATE_HEADER_URL, {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": CSRF_TOKEN,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        installation_name: installationName,
                        start_date: startDate,
                        end_date: endDate,
                        monitoring_type: monitoringType
                    })
                })
                    .then(res => res.json())
                    .then(data => {
                        if (badge) {
                            badge.classList.remove('saving');
                            badge.classList.add('visible');
                            badge.innerHTML = `
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Guardado</span>
                        `;
                            setTimeout(() => {
                                badge.classList.remove('visible');
                            }, 2500);
                        }
                    })
                    .catch(err => {
                        console.error("Error al guardar encabezado técnico:", err);
                        if (badge) {
                            badge.classList.remove('saving');
                            badge.innerHTML = `<span style="color: #ef4444;">Error</span>`;
                        }
                    });
            }, 300);
        }

        /* ==========================================================================
           GEOLOCALIZACIÓN GPS DIRECTA DEL DISPOSITIVO
           ========================================================================== */
        function getCurrentGpsPosition(latInputId, lngInputId, prefix) {
            if (prefix === 'edit' && !isEditUnlocked) return;
            if (!navigator.geolocation) {
                alert('La geolocalización no es soportada por este navegador.');
                return;
            }

            const latInput = document.getElementById(latInputId);
            const lngInput = document.getElementById(lngInputId);

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    if (prefix) {
                        updateUtmFromLatLng(prefix, lat, lng);
                        if (prefix === 'create' && createModalMarker) {
                            createModalMarker.setLatLng([lat, lng]);
                            if (createModalMap) createModalMap.panTo([lat, lng]);
                        } else if (prefix === 'edit' && editModalMarker) {
                            editModalMarker.setLatLng([lat, lng]);
                            if (editModalMap) editModalMap.panTo([lat, lng]);
                        }
                    } else {
                        if (latInput) latInput.value = lat.toFixed(7);
                        if (lngInput) lngInput.value = lng.toFixed(7);
                    }
                },
                (err) => {
                    alert('No se pudo obtener la ubicación GPS: ' + err.message);
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        }

        /* ==========================================================================
           PAGINACIÓN REACTIVA (10 VISIBLES POR PÁGINA) & FILTRADO EN VIVO
           ========================================================================== */
        const ILL_PAGE_SIZE = 10;
        let currentIllPage = 1;
        let currentIllFilter = 'all';

        function getMatchingRows() {
            const searchTerm = document.getElementById('illuminationSearchInput')?.value.trim().toLowerCase() || '';
            const rows = Array.from(document.querySelectorAll('#illuminationMasterTable tbody tr.illumination-data-row'));

            return rows.filter(row => {
                const compliantStatus = row.getAttribute('data-compliant') || '';
                const lightingType = row.getAttribute('data-lighting') || '';
                const searchData = row.getAttribute('data-search') || '';

                // Match filter
                let matchesFilter = true;
                if (currentIllFilter === 'compliant') {
                    matchesFilter = (compliantStatus === 'compliant');
                } else if (currentIllFilter === 'non-compliant') {
                    matchesFilter = (compliantStatus === 'non-compliant');
                } else if (['Natural', 'Artificial', 'Mixta'].includes(currentIllFilter)) {
                    matchesFilter = (lightingType === currentIllFilter);
                }

                // Match search
                const matchesSearch = (!searchTerm || searchData.includes(searchTerm));

                return matchesFilter && matchesSearch;
            });
        }

        function updateIlluminationPagination() {
            const matchingRows = getMatchingRows();
            const allRows = Array.from(document.querySelectorAll('#illuminationMasterTable tbody tr.illumination-data-row'));
            const totalItems = matchingRows.length;
            const totalPages = Math.max(1, Math.ceil(totalItems / ILL_PAGE_SIZE));

            if (currentIllPage > totalPages) currentIllPage = totalPages;
            if (currentIllPage < 1) currentIllPage = 1;

            const startIdx = (currentIllPage - 1) * ILL_PAGE_SIZE;
            const endIdx = startIdx + ILL_PAGE_SIZE;

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

            const startEl = document.getElementById('illPageStart');
            const endEl = document.getElementById('illPageEnd');
            const totalEl = document.getElementById('illPageTotal');

            if (startEl) startEl.textContent = totalItems === 0 ? 0 : (startIdx + 1);
            if (endEl) endEl.textContent = Math.min(endIdx, totalItems);
            if (totalEl) totalEl.textContent = totalItems;

            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
            const container = document.getElementById('illuminationPaginationControls');
            if (!container) return;

            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '';

            html += `<button type="button" class="ill-pag-btn" onclick="goToIllPage(${currentIllPage - 1})" ${currentIllPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            </button>`;

            for (let p = 1; p <= totalPages; p++) {
                if (p === 1 || p === totalPages || (p >= currentIllPage - 1 && p <= currentIllPage + 1)) {
                    html += `<button type="button" class="ill-pag-btn ${p === currentIllPage ? 'active' : ''}" onclick="goToIllPage(${p})">${p}</button>`;
                } else if (p === currentIllPage - 2 || p === currentIllPage + 2) {
                    html += `<span style="padding: 0 4px; color: #94a3b8; font-weight: 700;">...</span>`;
                }
            }

            html += `<button type="button" class="ill-pag-btn" onclick="goToIllPage(${currentIllPage + 1})" ${currentIllPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </button>`;

            container.innerHTML = html;
        }

        function goToIllPage(page) {
            currentIllPage = page;
            updateIlluminationPagination();
            const table = document.getElementById('illuminationMasterTable');
            if (table) table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        function filterIllumination(filterKey, btn) {
            currentIllFilter = filterKey;
            document.querySelectorAll('#illuminationFilterGroup .filter-pill-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            currentIllPage = 1;
            updateIlluminationPagination();
        }

        function searchIlluminationLive() {
            currentIllPage = 1;
            updateIlluminationPagination();
        }

        /* ==========================================================================
           MAPA MODAL COMPLETO (LEAFLET + FOTOGRAFÍA DEL PUNTO)
           ========================================================================== */
        let leafletMapInstance = null;
        let leafletMarkerInstance = null;
        let activeMapPhotoUrl = null;
        let activeMapPointTitle = null;

        function openMapModal(item) {
            const modal = document.getElementById('mapLocationModal');
            if (!modal) return;

            document.getElementById('mapModalTitle').textContent = `Punto #${item.num} — ${item.area}`;
            document.getElementById('mapModalSubtitle').textContent = `${item.workstation} • ${item.measurement_point}`;
            document.getElementById('mapCardPointName').textContent = `${item.area} — ${item.workstation}`;
            document.getElementById('mapCardLocationDesc').textContent = item.location && item.location !== '—' ? item.location : 'Ubicación registrada en planta';

            const luxBadge = document.getElementById('mapCardLuxBadge');
            if (luxBadge) {
                luxBadge.className = `lux-measured-badge ${item.is_compliant ? 'compliant' : 'non-compliant'}`;
                luxBadge.textContent = `${item.measured_lux} LUX (${item.is_compliant ? 'CUMPLE' : 'NO CUMPLE'})`;
            }

            activeMapPhotoUrl = item.image_path || null;
            activeMapPointTitle = `Punto #${item.num}: ${item.measurement_point}`;
            const photoWrap = document.getElementById('mapModalPhotoThumbWrap');
            const photoImg = document.getElementById('mapModalPhotoImg');
            if (activeMapPhotoUrl) {
                if (photoImg) photoImg.src = activeMapPhotoUrl;
                if (photoWrap) photoWrap.style.display = 'flex';
            } else {
                if (photoWrap) photoWrap.style.display = 'none';
            }

            let lat = parseFloat(item.latitude);
            let lng = parseFloat(item.longitude);

            if (isNaN(lat) || isNaN(lng)) {
                lat = -16.5034120;
                lng = -68.1324560;
            }

            let utmText = '';
            if (item.location && item.location.includes('E:') && item.location.includes('N:')) {
                utmText = item.location;
            } else if (!isNaN(lat) && !isNaN(lng)) {
                const utm = latLngToUtm(lat, lng);
                utmText = utm.formatted;
            } else {
                utmText = 'E: 218468.016, N: 7627234.367, Z: 20K';
            }

            document.getElementById('mapCardLocationDesc').textContent = utmText;
            document.getElementById('mapCardCoords').textContent = utmText;

            const gmapsBtn = document.getElementById('openInGoogleMapsBtn');
            if (gmapsBtn) {
                gmapsBtn.href = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
            }

            modal.classList.add('open');

            setTimeout(() => {
                initOrUpdateLeafletMap(lat, lng, item);
            }, 120);
        }

        function initOrUpdateLeafletMap(lat, lng, item) {
            const container = document.getElementById('mapContainerLeaflet');
            if (!container) return;

            if (!leafletMapInstance) {
                leafletMapInstance = L.map('mapContainerLeaflet').setView([lat, lng], 16);

                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(leafletMapInstance);
            } else {
                leafletMapInstance.invalidateSize();
                leafletMapInstance.setView([lat, lng], 16);
            }

            if (leafletMarkerInstance) {
                leafletMapInstance.removeLayer(leafletMarkerInstance);
            }

            let popupContent = `
                <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; max-width: 200px;">
                    <strong style="color: #0f172a; font-size: 13px;">Punto #${item.num}</strong><br>
                    <span style="color: #64748b;">${item.area}</span><br>
                    <div style="margin: 4px 0; font-weight: 700; color: ${item.is_compliant ? '#059669' : '#dc2626'};">${item.measured_lux} LUX</div>
            `;

            if (item.image_path) {
                popupContent += `
                    <div style="margin-top: 6px; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; cursor: pointer;" onclick="openPhotoViewer('${item.image_path}', 'Punto #${item.num}')">
                        <img src="${item.image_path}" style="width: 100%; height: 90px; object-fit: cover; display: block;">
                    </div>
                `;
            }

            popupContent += `</div>`;

            leafletMarkerInstance = L.marker([lat, lng])
                .addTo(leafletMapInstance)
                .bindPopup(popupContent)
                .openPopup();
        }

        function closeMapModal() {
            const modal = document.getElementById('mapLocationModal');
            if (modal) modal.classList.remove('open');
        }

        function expandCurrentMapPhoto() {
            if (activeMapPhotoUrl) {
                openPhotoViewer(activeMapPhotoUrl, activeMapPointTitle);
            }
        }

        /* ==========================================================================
           GESTIÓN DE MODALES: CREACIÓN, VISUALIZACIÓN Y DESBLOQUEO DE EDICIÓN
           ========================================================================== */
        function openCreateMeasurementModal() {
            const modal = document.getElementById('createMeasurementModal');
            if (modal) {
                modal.classList.add('open');
                createReadingsList = [];
                renderReadingsBadges('create');

                // Reset actividad y nivel requerido
                const actSelect = document.getElementById('create_activity_description');
                if (actSelect) actSelect.value = 'Oficinas y talleres';
                const reqSelect = document.getElementById('create_required_lux');
                if (reqSelect) reqSelect.value = '300';
                updateNormativeLegend('create');

                // Reset fotos slider
                modalPhotos.create = [];
                modalPhotoIndex.create = 0;
                renderPhotoSlider('create');
                const fileInput = document.getElementById('create_images_input');
                if (fileInput) fileInput.value = '';

                // Reset observaciones
                const obs = document.getElementById('create_observations');
                if (obs) obs.value = '';

                // Coordenadas UTM por defecto (E: 218468.016, N: 7627234.367, Z: 20K)
                const defaultEasting = 218468.016;
                const defaultNorthing = 7627234.367;
                const defaultZone = '20K';
                const eInput = document.getElementById('create_utm_easting');
                const nInput = document.getElementById('create_utm_northing');
                const zInput = document.getElementById('create_utm_zone');
                if (eInput) eInput.value = defaultEasting.toFixed(3);
                if (nInput) nInput.value = defaultNorthing.toFixed(3);
                if (zInput) zInput.value = defaultZone;

                const pos = utmToLatLng(defaultEasting, defaultNorthing, defaultZone);
                const formatted = `E: ${defaultEasting.toFixed(3)}, N: ${defaultNorthing.toFixed(3)}, Z: ${defaultZone}`;
                const disp = document.getElementById('create_utm_display');
                if (disp) disp.textContent = formatted;
                const locInput = document.getElementById('create_location');
                if (locInput) locInput.value = formatted;
                const latInput = document.getElementById('create_latitude');
                const lngInput = document.getElementById('create_longitude');
                if (latInput) latInput.value = pos.lat.toFixed(7);
                if (lngInput) lngInput.value = pos.lng.toFixed(7);

                setTimeout(() => {
                    initModalMiniMap('create', pos.lat, pos.lng);
                }, 150);
            }
        }

        function closeCreateMeasurementModal() {
            const modal = document.getElementById('createMeasurementModal');
            if (modal) modal.classList.remove('open');
        }

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
                'edit_workstation',
                'edit_measurement_point',
                'edit_activity_description',
                'edit_lighting_type',
                'edit_required_lux',
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

            // Visibilidad de Botones y Controles de Edición
            const submitBtn = document.getElementById('edit_modal_submit_btn');
            if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';

            const readingsInputBar = document.getElementById('edit_readings_input_bar');
            if (readingsInputBar) readingsInputBar.style.display = isEditing ? 'flex' : 'none';

            const gpsBtn = document.getElementById('edit_btn_gps');
            if (gpsBtn) gpsBtn.style.display = isEditing ? 'inline-flex' : 'none';

            const addPhotosBtn = document.getElementById('edit_btn_add_photos');
            if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'inline-flex' : 'none';

            // Arrastre de marcador en mapa
            if (editModalMarker && editModalMarker.dragging) {
                if (isEditing) editModalMarker.dragging.enable();
                else editModalMarker.dragging.disable();
            }

            // Re-renderizar lecturas para mostrar/ocultar botón eliminar ✕
            renderReadingsBadges('edit');

            // Sincronizar estado de fotos y botón eliminar foto
            syncEditRemainingImages();
            renderPhotoSlider('edit');
        }

        function toggleModalEditMode() {
            applyEditModeState(!isEditUnlocked);
        }

        function openViewMeasurementModal(item) {
            const form = document.getElementById('editMeasurementForm');
            if (!form) return;

            form.action = `/modulos/${MODULE_ID}/iluminacion/mediciones/${item.id}`;

            document.getElementById('edit_point_number').value = item.num || '';
            document.getElementById('edit_pt_num_disp').textContent = item.num || '01';

            // Personal registrador en encabezado y selector
            const staffEl = document.getElementById('edit_modal_registered_by');
            const staffSelect = document.getElementById('edit_staff_id');
            if (staffSelect) {
                if (item.staff_id) {
                    staffSelect.value = item.staff_id;
                } else if (item.registered_by) {
                    let matched = false;
                    for (let i = 0; i < staffSelect.options.length; i++) {
                        if (staffSelect.options[i].text.toLowerCase().includes(item.registered_by.toLowerCase())) {
                            staffSelect.selectedIndex = i;
                            matched = true;
                            break;
                        }
                    }
                    if (!matched && staffSelect.options.length > 0) {
                        // Selección por defecto
                    }
                }
                if (staffEl && staffSelect.selectedIndex >= 0 && staffSelect.options[staffSelect.selectedIndex]) {
                    const selText = staffSelect.options[staffSelect.selectedIndex].text;
                    staffEl.textContent = selText.split('—')[0].trim();
                } else if (staffEl) {
                    staffEl.textContent = item.registered_by || REGISTERED_BY_HEADER || '';
                }
            } else if (staffEl) {
                staffEl.textContent = item.registered_by || REGISTERED_BY_HEADER || '';
            }

            // Fecha y Hora de medición
            document.getElementById('edit_measurement_date').value = item.raw_date || '';
            document.getElementById('edit_measurement_time').value = item.time !== '—' ? item.time : '';
            document.getElementById('edit_area').value = item.area || '';
            document.getElementById('edit_workstation').value = item.workstation || '';
            document.getElementById('edit_measurement_point').value = item.measurement_point || '';

            // Descripción de Actividad
            const actSelect = document.getElementById('edit_activity_description');
            if (actSelect) {
                actSelect.value = item.activity_description || 'Oficinas y talleres';
            }

            // Tipo de Iluminación
            document.getElementById('edit_lighting_type').value = item.lighting_type || 'Artificial';

            // Nivel Requerido Select & Legend
            const reqSelect = document.getElementById('edit_required_lux');
            if (reqSelect) {
                const rawReq = String(parseInt(item.raw_required_lux) || (ACTIVITY_TO_LUX[item.activity_description] || 300));
                reqSelect.value = rawReq;
                updateNormativeLegend('edit');
            }

            // Mediciones LUX (Badges de lecturas hasta 25 puntos)
            editReadingsList = [];
            if (item.readings && Array.isArray(item.readings) && item.readings.length > 0) {
                editReadingsList = [...item.readings];
            } else if (item.raw_measured_lux > 0) {
                editReadingsList = [parseFloat(item.raw_measured_lux)];
            }

            // Visor Fotográfico Slide Continuo (Múltiples Fotos)
            modalPhotos.edit = [];
            if (item.images && Array.isArray(item.images) && item.images.length > 0) {
                modalPhotos.edit = [...item.images];
            } else if (item.image_path) {
                modalPhotos.edit = [item.image_path];
            }
            modalPhotoIndex.edit = 0;
            syncEditRemainingImages();
            renderPhotoSlider('edit');

            // Coordenadas UTM y Lat/Lng
            let utmEasting = 218468.016;
            let utmNorthing = 7627234.367;
            let utmZone = '20K';
            let lat = (item.latitude !== null && item.latitude !== '') ? parseFloat(item.latitude) : NaN;
            let lng = (item.longitude !== null && item.longitude !== '') ? parseFloat(item.longitude) : NaN;

            let parsedUtm = false;
            if (item.location && typeof item.location === 'string') {
                const match = item.location.match(/E:\s*([0-9.]+),\s*N:\s*([0-9.]+),\s*Z:\s*([0-9A-Za-z]+)/i);
                if (match) {
                    utmEasting = parseFloat(match[1]);
                    utmNorthing = parseFloat(match[2]);
                    utmZone = match[3].toUpperCase();
                    parsedUtm = true;
                    const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
                    lat = pos.lat;
                    lng = pos.lng;
                }
            }

            if (!parsedUtm) {
                if (!isNaN(lat) && !isNaN(lng)) {
                    const u = latLngToUtm(lat, lng);
                    utmEasting = u.easting;
                    utmNorthing = u.northing;
                    utmZone = u.zone;
                } else {
                    const pos = utmToLatLng(utmEasting, utmNorthing, utmZone);
                    lat = pos.lat;
                    lng = pos.lng;
                }
            }

            const eInput = document.getElementById('edit_utm_easting');
            const nInput = document.getElementById('edit_utm_northing');
            const zInput = document.getElementById('edit_utm_zone');
            const disp = document.getElementById('edit_utm_display');
            const locInput = document.getElementById('edit_location');
            const latInput = document.getElementById('edit_latitude');
            const lngInput = document.getElementById('edit_longitude');

            const formattedUtm = `E: ${utmEasting.toFixed(3)}, N: ${utmNorthing.toFixed(3)}, Z: ${utmZone}`;
            if (eInput) eInput.value = utmEasting.toFixed(3);
            if (nInput) nInput.value = utmNorthing.toFixed(3);
            if (zInput) zInput.value = utmZone;
            if (disp) disp.textContent = formattedUtm;
            if (locInput) locInput.value = formattedUtm;
            if (latInput) latInput.value = isNaN(lat) ? '' : lat.toFixed(7);
            if (lngInput) lngInput.value = isNaN(lng) ? '' : lng.toFixed(7);
            document.getElementById('edit_observations').value = item.observations || '';

            // Limpiar selector de archivo
            const fileInput = document.getElementById('edit_images_input');
            if (fileInput) fileInput.value = '';

            // Iniciar SIEMPRE en Modo Consulta (Solo Lectura)
            applyEditModeState(false);

            const modal = document.getElementById('editMeasurementModal');
            if (modal) {
                modal.classList.add('open');
                setTimeout(() => {
                    initModalMiniMap('edit', isNaN(lat) ? -16.5034120 : lat, isNaN(lng) ? -68.1324560 : lng);
                }, 150);
            }
        }

        // Alias para compatibilidad
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

        function confirmDeleteMeasurement(id, pointNum) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: `¿Eliminar Punto #${pointNum}?`,
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    focusCancel: true,
                    buttonsStyling: false,
                    customClass: {
                        popup: 'metric-swal-popup',
                        confirmButton: 'metric-swal-btn-danger',
                        cancelButton: 'metric-swal-btn-cancel'
                    }
                }).then((res) => {
                    if (res.isConfirmed) {
                        const form = document.getElementById('deleteMeasurementForm');
                        form.action = `/modulos/${MODULE_ID}/iluminacion/mediciones/${id}`;
                        form.submit();
                    }
                });
            } else {
                if (confirm(`¿Estás seguro de eliminar el punto de medición #${pointNum}?`)) {
                    const form = document.getElementById('deleteMeasurementForm');
                    form.action = `/modulos/${MODULE_ID}/iluminacion/mediciones/${id}`;
                    form.submit();
                }
            }
        }

        /* ==========================================================================
           GESTIÓN DE MODAL EXPORTAR & GENERACIÓN EXCEL (.XLSX)
           ========================================================================== */
        /* ==========================================================================
           GESTIÓN DE MODAL REPORTE FOTOGRÁFICO EN MOSAICO Y EXPORTACIÓN PDF
           ========================================================================== */
        let photoReportConfig = {
            grid: '2x3', // '2x3', '2x4', '3x3', '3x4'
            orientation: 'portrait',
            selectedPoints: new Set(),
            photoIndices: {},
            currentTab: 'interactive',
            isInitialized: false
        };

        let photoReportAutoSaveTimeout = null;

        function openExportModal() {
            const modal = document.getElementById('exportOptionsModal');
            if (modal) modal.classList.add('open');
        }

        function closeExportModal() {
            const modal = document.getElementById('exportOptionsModal');
            if (modal) modal.classList.remove('open');
        }

        function openPhotoReportModal() {
            closeExportModal();
            const modal = document.getElementById('photoReportModal');
            if (modal) {
                modal.classList.add('open');
                initPhotoReportEngine();
            }
        }

        function closePhotoReportModal() {
            const modal = document.getElementById('photoReportModal');
            if (modal) modal.classList.remove('open');
        }

        /**
         * Inicializar motor de reporte fotográfico con datos guardados
         */
        function initPhotoReportEngine() {
            if (!photoReportConfig.isInitialized) {
                let saved = null;
                // 1. Intentar cargar desde backend (PHOTO_REPORT_INITIAL_SETTINGS)
                if (typeof PHOTO_REPORT_INITIAL_SETTINGS === 'object' && PHOTO_REPORT_INITIAL_SETTINGS && Object.keys(PHOTO_REPORT_INITIAL_SETTINGS).length > 0) {
                    saved = PHOTO_REPORT_INITIAL_SETTINGS;
                }
                // 2. Si no hay en backend, revisar localStorage
                if (!saved) {
                    try {
                        const local = localStorage.getItem('metric_photo_report_' + MODULE_ID);
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
                if (saved && Array.isArray(saved.selected_points) && saved.selected_points.length > 0) {
                    saved.selected_points.forEach(id => photoReportConfig.selectedPoints.add(Number(id)));
                } else {
                    // Por defecto, seleccionar todos los puntos disponibles
                    ALL_MEASUREMENTS_DATA.forEach(m => photoReportConfig.selectedPoints.add(Number(m.id)));
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

        /**
         * Obtener arreglo de imágenes válidas de un punto de medición
         */
        function getPointImages(m) {
            if (Array.isArray(m.images) && m.images.length > 0) {
                return m.images.filter(Boolean);
            }
            if (m.image_path) {
                return [m.image_path];
            }
            return [];
        }

        /**
         * Formatear código requerido: ILU-XX (ej. ILU-17)
         */
        function formatPointCode(num) {
            if (!num) return 'ILU-01';
            const str = String(num).trim();
            if (str.toUpperCase().startsWith('ILU-')) {
                return str.toUpperCase();
            }
            const numericPart = str.replace(/[^0-9]/g, '');
            if (numericPart) {
                return 'ILU-' + numericPart.padStart(2, '0');
            }
            return 'ILU-' + str;
        }

        /**
         * Escapar texto para HTML seguro
         */
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        /**
         * Obtener cantidad de fotos por hoja según la distribución
         */
        function getPhotosPerPage(grid) {
            switch (grid) {
                case '2x4': return 8;
                case '3x3': return 9;
                case '3x4': return 12;
                case '2x3':
                default: return 6;
            }
        }

        /**
         * Cambiar distribución en mosaico (2x3, 2x4, 3x3, 3x4)
         */
        function changeGridDistribution(newGrid) {
            const validGrids = ['2x3', '2x4', '3x3', '3x4'];
            if (!validGrids.includes(newGrid)) return;

            photoReportConfig.grid = newGrid;
            updateGridSelectorButtonsUI();
            renderPhotoSheets();
            updateSelectionCounterUI();
            triggerAutoSavePhotoReportConfig();
        }

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

        /**
         * Alternar selección de punto para el PDF
         */
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

        /**
         * Seleccionar todas o ninguna foto
         */
        function selectAllPoints(select) {
            ALL_MEASUREMENTS_DATA.forEach(m => {
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

        /**
         * Actualizar contador de selección y número de hojas calculadas
         */
        function updateSelectionCounterUI() {
            const count = photoReportConfig.selectedPoints.size;
            const total = ALL_MEASUREMENTS_DATA.length;
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

        /**
         * Navegar entre fotografías de un punto usando las flechas (< y >)
         */
        function navigatePointPhoto(pointId, delta, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            pointId = Number(pointId);
            const item = ALL_MEASUREMENTS_DATA.find(m => Number(m.id) === pointId);
            if (!item) return;

            const images = getPointImages(item);
            if (images.length <= 1) return;

            const curr = photoReportConfig.photoIndices[pointId] || 0;
            const next = (curr + delta + images.length) % images.length;
            photoReportConfig.photoIndices[pointId] = next;

            // Actualizar imagen y badge en tarjeta interactiva
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

        /**
         * Alternar pestañas: Configurar Mosaico vs Vista Previa Hojas Carta
         */
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

        /**
         * Renderizar Cuadrícula Interactiva de Configuración
         */
        function renderInteractiveGrid() {
            const container = document.getElementById('photoInteractiveGridContainer');
            if (!container) return;

            if (ALL_MEASUREMENTS_DATA.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 45px 20px; color: #94a3b8; background: #ffffff; border-radius: 12px; border: 1.5px dashed #cbd5e1;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 8px;"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        <p style="margin: 0; font-size: 13.5px; font-weight: 600;">No hay mediciones registradas para generar el reporte fotográfico.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            ALL_MEASUREMENTS_DATA.forEach(m => {
                const pointId = Number(m.id);
                const images = getPointImages(m);
                const selectedIdx = photoReportConfig.photoIndices[pointId] || 0;
                const currentImg = images[selectedIdx] || images[0] || '';
                const isSelected = photoReportConfig.selectedPoints.has(pointId);
                const codeFormatted = formatPointCode(m.num);
                const hasMultiplePhotos = images.length > 1;

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

                        <!-- ARRIBA DE LA FOTO: Solo Código, Área y Punto -->
                        <div class="photo-card-info-top">
                            <div class="photo-info-row">
                                <span class="photo-info-label">Código:</span>
                                <span class="photo-info-value photo-code">${codeFormatted}</span>
                            </div>
                            <div class="photo-info-row">
                                <span class="photo-info-label">Área:</span>
                                <span class="photo-info-value" title="${escapeHtml(m.area || '—')}">${escapeHtml(m.area || '—')}</span>
                            </div>
                            <div class="photo-info-row">
                                <span class="photo-info-label">Punto:</span>
                                <span class="photo-info-value" title="${escapeHtml(m.measurement_point || '—')}">${escapeHtml(m.measurement_point || '—')}</span>
                            </div>
                        </div>

                        <!-- MARCO DE FOTOGRAFÍA CON CARRUSEL DE FLECHAS -->
                        <div class="photo-card-img-box">
                            ${currentImg ? `
                                <img id="photoImg_${pointId}" src="${currentImg}" alt="${codeFormatted}" onclick="openPhotoViewer('${currentImg}', '${codeFormatted}: ${addslashes(m.measurement_point || '')}')" title="Clic para ampliar fotografía">
                                <button type="button" class="photo-zoom-btn" onclick="openPhotoViewer('${currentImg}', '${codeFormatted}: ${addslashes(m.measurement_point || '')}')" title="Ampliar">
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

                        <!-- DEBAJO DE LA FOTO: Solo Fecha y Hora -->
                        <div class="photo-card-info-bottom">
                            <span class="photo-dt-label">
                                <svg class="photo-dt-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                Fecha y hora:
                            </span>
                            <span class="photo-dt-pill">${escapeHtml(m.date || '—')} - ${escapeHtml(m.time || '—')}</span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        /**
         * Renderizar Hojas Tamaño Carta para Vista Previa y Media Print
         */
        function renderPhotoSheets() {
            const sheetsContainer = document.getElementById('photoSheetsContainer');
            const printContainer = document.getElementById('photoReportPrintArea');

            const selectedItems = ALL_MEASUREMENTS_DATA.filter(m => photoReportConfig.selectedPoints.has(Number(m.id)));
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

            // Dividir en páginas según perPage
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
                                        <circle cx="12" cy="12" r="4"/>
                                        <path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/>
                                    </svg>
                                </div>
                                <div class="sheet-header-titles">
                                    <div class="sheet-header-brand-row">
                                        <span class="sheet-company-title">PACHABOL S.R.L.</span>
                                        <span class="sheet-brand-dot">•</span>
                                        <span class="sheet-system-tag">Higiene &amp; Seguridad Ocupacional</span>
                                    </div>
                                    <h1>REPORTE FOTOGRÁFICO — MONITOREO DE ILUMINACIÓN</h1>
                                    <div class="sheet-header-subtitle-row">
                                        <span class="sheet-tech-tag">METRIC v2 • SISTEMA DE GESTIÓN TÉCNICA &amp; ARCHIVO DIGITAL</span>
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
                                        <span class="sheet-meta-val" title="${escapeHtml(TECHNICAL_HEADER_DATA.installationName || 'No registrada')}">${escapeHtml(TECHNICAL_HEADER_DATA.installationName || 'No registrada')}</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">EQUIPO:</span>
                                        <span class="sheet-meta-val">${escapeHtml(TECHNICAL_HEADER_DATA.equipmentName || 'Luxómetro')} (${escapeHtml(TECHNICAL_HEADER_DATA.equipmentModel || 'PCE-174')})</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">FECHA:</span>
                                        <span class="sheet-meta-val">${escapeHtml(TECHNICAL_HEADER_DATA.startDateFormatted || '—')}${TECHNICAL_HEADER_DATA.endDateFormatted && TECHNICAL_HEADER_DATA.endDateFormatted !== TECHNICAL_HEADER_DATA.startDateFormatted ? ' al ' + escapeHtml(TECHNICAL_HEADER_DATA.endDateFormatted) : ''}</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">SERIE / MARCA:</span>
                                        <span class="sheet-meta-val">${escapeHtml(TECHNICAL_HEADER_DATA.equipmentSerial || '150206371')} • ${escapeHtml(TECHNICAL_HEADER_DATA.equipmentBrand || 'PCE')}</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">TIPO:</span>
                                        <span class="sheet-meta-val">${escapeHtml(TECHNICAL_HEADER_DATA.monitoringType || 'Diurno')}</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">PARÁMETRO:</span>
                                        <span class="sheet-meta-val">Nivel de Iluminación (Lux)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CUADRÍCULA DE FOTOGRAFÍAS EN MOSAICO (DISTRIBUCIÓN ELEGIDA) -->
                        <div class="sheet-photos-grid dist-${photoReportConfig.grid}">
                            ${pageItems.map(m => {
                    const pointId = Number(m.id);
                    const images = getPointImages(m);
                    const selectedIdx = photoReportConfig.photoIndices[pointId] || 0;
                    const chosenImg = images[selectedIdx] || images[0] || '';
                    const codeFormatted = formatPointCode(m.num);

                    return `
                                    <div class="sheet-card-item">
                                        <!-- ARRIBA DE LA FOTO: Solo Código, Área y Punto -->
                                        <div class="photo-card-info-top">
                                            <div class="photo-info-row">
                                                <span class="photo-info-label">Código:</span>
                                                <span class="photo-info-value photo-code">${codeFormatted}</span>
                                            </div>
                                            <div class="photo-info-row">
                                                <span class="photo-info-label">Área:</span>
                                                <span class="photo-info-value" title="${escapeHtml(m.area || '—')}">${escapeHtml(m.area || '—')}</span>
                                            </div>
                                            <div class="photo-info-row">
                                                <span class="photo-info-label">Punto:</span>
                                                <span class="photo-info-value" title="${escapeHtml(m.measurement_point || '—')}">${escapeHtml(m.measurement_point || '—')}</span>
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

                                        <!-- DEBAJO DE LA FOTO: Solo Fecha y Hora -->
                                        <div class="photo-card-info-bottom">
                                            <span class="photo-dt-label">
                                                <svg class="photo-dt-icon" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                                </svg>
                                                Fecha y hora:
                                            </span>
                                            <span class="photo-dt-pill">${escapeHtml(m.date || '—')} - ${escapeHtml(m.time || '—')}</span>
                                        </div>
                                    </div>
                                `;
                }).join('')}
                        </div>

                        <!-- PIE DE PÁGINA ELEGANTE OFICIAL DEL PDF -->
                        <div class="sheet-footer-box">
                            <div class="sheet-footer-brand">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                <span>METRIC v2 — Pachabol S.R.L. • Higiene &amp; Seguridad Ocupacional</span>
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

        /**
         * Auto-guardar configuración con debounce
         */
        function triggerAutoSavePhotoReportConfig() {
            if (photoReportAutoSaveTimeout) clearTimeout(photoReportAutoSaveTimeout);
            photoReportAutoSaveTimeout = setTimeout(() => {
                savePhotoReportSettingsToServer(false);
            }, 650);
        }

        /**
         * Guardar configuración en base de datos (AJAX) y en localStorage
         */
        async function savePhotoReportSettingsToServer(showToast = false) {
            const btnSave = document.getElementById('btnSaveReportSettings');
            if (btnSave && showToast) {
                btnSave.disabled = true;
                btnSave.innerHTML = `
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="spin" style="animation: spin 0.8s linear infinite;"><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"/><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>
                    <span>Guardando...</span>
                `;
            }

            const payload = {
                grid: photoReportConfig.grid,
                orientation: photoReportConfig.orientation,
                selected_points: Array.from(photoReportConfig.selectedPoints),
                photo_indices: photoReportConfig.photoIndices
            };

            // Guardar de inmediato en localStorage
            try {
                localStorage.setItem('metric_photo_report_' + MODULE_ID, JSON.stringify(payload));
            } catch (e) {
                console.warn('Error saving to localStorage', e);
            }

            // Guardar en backend MySQL
            try {
                const res = await fetch(`/modulos/${MODULE_ID}/iluminacion/photo-report-settings`, {
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

        /**
         * Imprimir o Guardar PDF en Tamaño Carta
         */
        function printPhotoReport() {
            if (photoReportConfig.selectedPoints.size === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Sin selección',
                        text: 'Debes seleccionar al menos una fotografía para generar e imprimir el reporte en PDF.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        buttonsStyling: false,
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                    });
                } else {
                    alert('Debes seleccionar al menos una fotografía para imprimir.');
                }
                return;
            }

            // Asegurar que las hojas estén actualizadas
            renderPhotoSheets();

            // Lanzar diálogo de impresión
            setTimeout(() => {
                window.print();
            }, 120);
        }

        /* ==========================================================================
           MODAL MAPA DE TODAS LAS UBICACIONES (LEAFLET MULTI-PUNTO)
           ========================================================================== */
        let allLocationsMapInstance = null;
        let allLocationsMarkersLayer = null;
        let allLocationsMarkersList = [];

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

            if (!allLocationsMapInstance) {
                allLocationsMapInstance = L.map('allLocationsMapLeaflet').setView([-16.503412, -68.132456], 15);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(allLocationsMapInstance);
                allLocationsMarkersLayer = L.featureGroup().addTo(allLocationsMapInstance);
            } else {
                allLocationsMapInstance.invalidateSize();
            }

            allLocationsMarkersLayer.clearLayers();
            allLocationsMarkersList = [];

            const bounds = [];

            ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
                let lat = parseFloat(m.latitude);
                let lng = parseFloat(m.longitude);

                if (isNaN(lat) || isNaN(lng)) {
                    lat = -16.503412 + (idx * 0.00035);
                    lng = -68.132456 + (idx * 0.00035);
                }

                bounds.push([lat, lng]);

                const pinHtml = `
                    <div class="custom-map-pin ${m.is_compliant ? 'pin-compliant' : 'pin-non-compliant'}" title="Punto #${m.num}">
                        ${m.num}
                    </div>
                `;

                const customIcon = L.divIcon({
                    html: pinHtml,
                    className: 'custom-div-pin-wrapper',
                    iconSize: [30, 30],
                    iconAnchor: [15, 15],
                    popupAnchor: [0, -16]
                });

                let photoSection = '';
                if (m.image_path) {
                    photoSection = `
                        <div style="margin-top: 6px; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; cursor: pointer;" onclick="openPhotoViewer('${m.image_path}', 'Punto #${m.num}: ${addslashes(m.measurement_point)}')">
                            <img src="${m.image_path}" style="width: 100%; height: 90px; object-fit: cover; display: block;">
                        </div>
                    `;
                }

                const popupHtml = `
                    <div style="font-family: 'Outfit', sans-serif; font-size: 12px; line-height: 1.4; min-width: 210px; max-width: 260px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">
                            <strong style="font-size: 13px; color: #0f172a;">Punto #${m.num}</strong>
                            <span style="font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: ${m.is_compliant ? '#ecfdf5' : '#fef2f2'}; color: ${m.is_compliant ? '#059669' : '#dc2626'}; border: 1px solid ${m.is_compliant ? '#a7f3d0' : '#fecaca'};">
                                ${m.is_compliant ? 'CUMPLE' : 'NO CUMPLE'}
                            </span>
                        </div>
                        <div style="font-weight: 700; color: #1e293b; margin-bottom: 2px;">${m.measurement_point}</div>
                        <div style="font-size: 11.5px; color: #64748b; margin-bottom: 6px;">${m.area} • ${m.workstation}</div>

                        <div style="display: flex; align-items: center; gap: 6px; background: #f0f9ff; border: 1px solid #bae6fd; padding: 4px 8px; border-radius: 6px; margin-bottom: 6px;">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span style="font-size: 11px; color: #0369a1;">Registrado por: <strong>${m.registered_by}</strong></span>
                        </div>

                        <div style="font-size: 11.5px; margin-bottom: 6px;">
                            <span>Lectura: <strong>${m.measured_lux} LUX</strong></span>
                            <span style="color: #64748b;">(Req: ${m.required_lux} LUX)</span>
                        </div>

                        ${photoSection}
                    </div>
                `;

                const marker = L.marker([lat, lng], { icon: customIcon })
                    .bindPopup(popupHtml);

                allLocationsMarkersLayer.addLayer(marker);
                allLocationsMarkersList.push(marker);
            });

            if (bounds.length > 0) {
                allLocationsMapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 17 });
            }
        }

        function focusPointOnAllLocationsMap(index) {
            if (!allLocationsMarkersList[index] || !allLocationsMapInstance) return;
            const marker = allLocationsMarkersList[index];
            allLocationsMapInstance.setView(marker.getLatLng(), 17, { animate: true });
            marker.openPopup();
        }

        function addslashes(str) {
            return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
        }

        /* ==========================================================================
           EXPORTACIÓN PLANILLA EXCEL (.XLSX) CON FORMATO TÉCNICO OFICIAL
           ========================================================================== */
        async function downloadExcelPlanilla() {
            if (typeof ExcelJS === 'undefined') {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Inicializando librería...',
                        text: 'La librería de exportación se está cargando. Por favor reintenta en un momento.',
                        icon: 'info',
                        confirmButtonText: 'Aceptar',
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                    });
                } else {
                    alert('Cargando librería Excel. Por favor reintenta.');
                }
                return;
            }

            try {
                const workbook = new ExcelJS.Workbook();
                workbook.creator = 'METRIC v2 Pachabol';
                workbook.created = new Date();
                const sheet = workbook.addWorksheet('Planilla de Iluminación', {
                    views: [{ showGridLines: true }]
                });

                // 29 Columnas en total (A hasta AC)
                sheet.columns = [
                    { key: 'num', width: 6 },                   // A: N°
                    { key: 'area', width: 26 },                  // B: Área
                    { key: 'workstation', width: 24 },           // C: Puesto de trabajo
                    { key: 'point', width: 20 },                 // D: Punto de medición
                    { key: 'activity', width: 26 },              // E: Descripción de la actividad
                    { key: 'time', width: 14 },                  // F: Horario de medición
                    { key: 'lighting', width: 14 },              // G: Tipo de iluminación
                    { key: 'required_lux', width: 16 },          // H: Nivel iluminancia requerido (Lux)
                    { key: 'm1', width: 7 },                     // I: M1
                    { key: 'm2', width: 7 },                     // J: M2
                    { key: 'm3', width: 7 },                     // K: M3
                    { key: 'm4', width: 7 },                     // L: M4
                    { key: 'm5', width: 7 },                     // M: M5
                    { key: 'm6', width: 7 },                     // N: M6
                    { key: 'm7', width: 7 },                     // O: M7
                    { key: 'm8', width: 7 },                     // P: M8
                    { key: 'm9', width: 7 },                     // Q: M9
                    { key: 'm10', width: 7 },                    // R: M10
                    { key: 'm11', width: 7 },                    // S: M11
                    { key: 'm12', width: 7 },                    // T: M12
                    { key: 'm13', width: 7 },                    // U: M13
                    { key: 'm14', width: 7 },                    // V: M14
                    { key: 'm15', width: 7 },                    // W: M15
                    { key: 'm16', width: 7 },                    // X: M16
                    { key: 'min', width: 8 },                    // Y: Min
                    { key: 'max', width: 8 },                    // Z: Max
                    { key: 'prom', width: 10 },                  // AA: Promedio
                    { key: 'compliance', width: 14 },            // AB: Cumple/no cumple el valor
                    { key: 'obs', width: 30 }                    // AC: Observaciones
                ];

                const thinBorder = {
                    top: { style: 'thin', color: { argb: 'FF000000' } },
                    left: { style: 'thin', color: { argb: 'FF000000' } },
                    bottom: { style: 'thin', color: { argb: 'FF000000' } },
                    right: { style: 'thin', color: { argb: 'FF000000' } }
                };

                const applyBoxBorder = (startRow, startCol, endRow, endCol) => {
                    for (let r = startRow; r <= endRow; r++) {
                        for (let c = startCol; c <= endCol; c++) {
                            const cell = sheet.getCell(r, c);
                            cell.border = thinBorder;
                        }
                    }
                };

                // FILA 1: Encabezado Banner Azul #2B579A
                sheet.mergeCells('A1:AC1');
                const r1 = sheet.getCell('A1');
                r1.value = 'PLANILLA DE MEDICIÓN Y EVALUACIÓN DE NIVELES DE ILUMINACIÓN';
                r1.font = { name: 'Calibri', size: 13, bold: true, color: { argb: 'FFFFFFFF' } };
                r1.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF2B579A' } };
                r1.alignment = { vertical: 'middle', horizontal: 'center' };
                sheet.getRow(1).height = 28;

                // FILA 2: Espacio
                sheet.getRow(2).height = 10;

                // FILAS 3 A 6: Encabezados Técnicos Duales (Instalación vs Equipo)
                const instName = document.getElementById('inline_installation_name')?.value || TECHNICAL_HEADER_DATA.installationName || 'PAPELBOL - VILLA TUNARI';
                const stDate = TECHNICAL_HEADER_DATA.startDateFormatted || '23/7/2026';
                const enDate = TECHNICAL_HEADER_DATA.endDateFormatted || '24/7/2026';
                const monType = document.getElementById('inline_monitoring_type')?.value || TECHNICAL_HEADER_DATA.monitoringType || 'RUTINARIO:   SEGUIMIENTO: X';
                const eqName = TECHNICAL_HEADER_DATA.equipmentName || 'LUXÓMETRO - PCE';
                const eqBrand = TECHNICAL_HEADER_DATA.equipmentBrand || 'PCE';
                const eqModel = TECHNICAL_HEADER_DATA.equipmentModel || 'PCE - 174';
                const eqSerial = TECHNICAL_HEADER_DATA.equipmentSerial || '150206371';

                // Lado Izquierdo
                // Fila 3
                sheet.mergeCells('A3:C3');
                sheet.mergeCells('D3:L3');
                sheet.getCell('A3').value = 'INSTALACIÓN:';
                sheet.getCell('D3').value = instName;

                // Fila 4
                sheet.mergeCells('A4:C4');
                sheet.mergeCells('D4:L4');
                sheet.getCell('A4').value = 'FECHA DE INICIO:';
                sheet.getCell('D4').value = stDate;

                // Fila 5
                sheet.mergeCells('A5:C5');
                sheet.mergeCells('D5:L5');
                sheet.getCell('A5').value = 'FECHA DE FINALIZACIÓN:';
                sheet.getCell('D5').value = enDate;

                // Fila 6
                sheet.mergeCells('A6:C6');
                sheet.mergeCells('D6:L6');
                sheet.getCell('A6').value = 'TIPO DE MONITOREO:';
                sheet.getCell('D6').value = monType.includes('SEGUIMIENTO') ? monType : `RUTINARIO:   SEGUIMIENTO: ${monType || 'X'}`;

                applyBoxBorder(3, 1, 6, 12);

                // Lado Derecho
                // Fila 3
                sheet.mergeCells('T3:V3');
                sheet.mergeCells('W3:AC3');
                sheet.getCell('T3').value = 'EQUIPO:';
                sheet.getCell('W3').value = eqName;

                // Fila 4
                sheet.mergeCells('T4:V4');
                sheet.mergeCells('W4:AC4');
                sheet.getCell('T4').value = 'MARCA:';
                sheet.getCell('W4').value = eqBrand;

                // Fila 5
                sheet.mergeCells('T5:V5');
                sheet.mergeCells('W5:AC5');
                sheet.getCell('T5').value = 'MODELO:';
                sheet.getCell('W5').value = eqModel;

                // Fila 6
                sheet.mergeCells('T6:V6');
                sheet.mergeCells('W6:AC6');
                sheet.getCell('T6').value = 'SERIE:';
                sheet.getCell('W6').value = eqSerial;

                applyBoxBorder(3, 20, 6, 29);

                // Formato tipográfico de las cajas técnicas 3-6
                for (let r = 3; r <= 6; r++) {
                    sheet.getRow(r).height = 19;
                    const leftLbl = sheet.getCell(r, 1);
                    leftLbl.font = { name: 'Calibri', size: 9, bold: true };
                    leftLbl.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

                    const leftVal = sheet.getCell(r, 4);
                    leftVal.font = { name: 'Calibri', size: 9 };
                    leftVal.alignment = { vertical: 'middle', horizontal: 'center' };

                    const rightLbl = sheet.getCell(r, 20);
                    rightLbl.font = { name: 'Calibri', size: 9, bold: true };
                    rightLbl.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

                    const rightVal = sheet.getCell(r, 23);
                    rightVal.font = { name: 'Calibri', size: 9 };
                    rightVal.alignment = { vertical: 'middle', horizontal: 'center' };
                }

                // FILA 7: EVALUACIÓN DE RIESGOS (Centrado)
                sheet.mergeCells('A7:AC7');
                const r7 = sheet.getCell('A7');
                r7.value = 'EVALUACIÓN DE RIESGOS';
                r7.font = { name: 'Calibri', size: 12, bold: true, color: { argb: 'FF000000' } };
                r7.alignment = { vertical: 'middle', horizontal: 'center' };
                sheet.getRow(7).height = 25;

                // FILAS 8 y 9: ENCABEZADOS DE TABLA (Fondo #D9E1F2)
                const headerBg = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD9E1F2' } };
                const headerFont = { name: 'Calibri', size: 9, bold: true, color: { argb: 'FF000000' } };

                sheet.mergeCells('A8:A9');
                sheet.getCell('A8').value = 'N°';

                sheet.mergeCells('B8:B9');
                sheet.getCell('B8').value = 'Área';

                sheet.mergeCells('C8:C9');
                sheet.getCell('C8').value = 'Puesto de trabajo';

                sheet.mergeCells('D8:D9');
                sheet.getCell('D8').value = 'Punto de medición';

                sheet.mergeCells('E8:E9');
                sheet.getCell('E8').value = 'Descripción de la actividad';

                sheet.mergeCells('F8:F9');
                sheet.getCell('F8').value = 'Horario de\nmedición';

                sheet.mergeCells('G8:G9');
                sheet.getCell('G8').value = 'Tipo de\niluminación';

                sheet.mergeCells('H8:H9');
                sheet.getCell('H8').value = 'Nivel\niluminancia\nrequerido (Lux)';

                // M1 a M16 en fila 8 combinada
                sheet.mergeCells('I8:X8');
                sheet.getCell('I8').value = 'Medición de iluminancia (Lux)';

                for (let mIdx = 1; mIdx <= 16; mIdx++) {
                    const colNum = 8 + mIdx; // 9 = I
                    sheet.getCell(9, colNum).value = `M${mIdx}`;
                }

                // Resultados Min, Max, Promedio
                sheet.mergeCells('Y8:AA8');
                sheet.getCell('Y8').value = 'Resultados';
                sheet.getCell(9, 25).value = 'Min';
                sheet.getCell(9, 26).value = 'Max';
                sheet.getCell(9, 27).value = 'Promedio';

                sheet.mergeCells('AB8:AB9');
                sheet.getCell('AB8').value = 'Cumple/no\ncumple el\nvalor';

                sheet.mergeCells('AC8:AC9');
                sheet.getCell('AC8').value = 'Observaciones';

                sheet.getRow(8).height = 26;
                sheet.getRow(9).height = 20;

                for (let r = 8; r <= 9; r++) {
                    for (let c = 1; c <= 29; c++) {
                        const cell = sheet.getCell(r, c);
                        cell.fill = headerBg;
                        cell.font = headerFont;
                        cell.border = thinBorder;
                        cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                    }
                }

                // FILAS DE DATOS (A partir de fila 10)
                let currentRow = 10;
                ALL_MEASUREMENTS_DATA.forEach((item, index) => {
                    sheet.getRow(currentRow).height = 24;

                    let readings = Array.isArray(item.readings) ? item.readings : [];
                    if (readings.length === 0 && item.raw_measured_lux > 0) {
                        readings = [parseFloat(item.raw_measured_lux)];
                    }

                    let minVal = null;
                    let maxVal = null;
                    let avgVal = parseFloat(item.raw_measured_lux) || 0;

                    if (readings.length > 0) {
                        minVal = Math.min(...readings);
                        maxVal = Math.max(...readings);
                        const sum = readings.reduce((a, b) => a + b, 0);
                        avgVal = sum / readings.length;
                    }

                    const reqLux = parseFloat(item.raw_required_lux) || 300;
                    const isComp = avgVal >= reqLux;

                    // Col A: N°
                    const cA = sheet.getCell(currentRow, 1);
                    cA.value = parseInt(item.num) || (index + 1);
                    cA.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Col B: Área
                    const cB = sheet.getCell(currentRow, 2);
                    cB.value = item.area || '';
                    cB.alignment = { vertical: 'middle', horizontal: 'left' };

                    // Col C: Puesto de trabajo
                    const cC = sheet.getCell(currentRow, 3);
                    cC.value = item.workstation || '';
                    cC.alignment = { vertical: 'middle', horizontal: 'left' };

                    // Col D: Punto de medición
                    const cD = sheet.getCell(currentRow, 4);
                    cD.value = item.measurement_point || '';
                    cD.alignment = { vertical: 'middle', horizontal: 'left' };

                    // Col E: Descripción de la actividad
                    const cE = sheet.getCell(currentRow, 5);
                    cE.value = item.activity_description || '';
                    cE.alignment = { vertical: 'middle', horizontal: 'left' };

                    // Col F: Horario de medición
                    const cF = sheet.getCell(currentRow, 6);
                    cF.value = item.time && item.time !== '—' ? item.time : '';
                    cF.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Col G: Tipo de iluminación
                    const cG = sheet.getCell(currentRow, 7);
                    cG.value = item.lighting_type || 'Natural';
                    cG.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Col H: Nivel iluminancia requerido (Lux)
                    const cH = sheet.getCell(currentRow, 8);
                    cH.value = reqLux;
                    cH.numFmt = '#,##0.00';
                    cH.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Cols I a X: M1 a M16
                    for (let mIdx = 0; mIdx < 16; mIdx++) {
                        const colNum = 9 + mIdx;
                        const cM = sheet.getCell(currentRow, colNum);
                        if (readings[mIdx] !== undefined) {
                            cM.value = parseFloat(readings[mIdx]);
                            cM.numFmt = '#,##0.0';
                        } else {
                            cM.value = '';
                        }
                        cM.alignment = { vertical: 'middle', horizontal: 'center' };
                    }

                    // Col Y: Min
                    const cY = sheet.getCell(currentRow, 25);
                    if (minVal !== null) {
                        cY.value = parseFloat(minVal.toFixed(1));
                        cY.numFmt = '#,##0.0';
                    } else {
                        cY.value = '';
                    }
                    cY.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Col Z: Max
                    const cZ = sheet.getCell(currentRow, 26);
                    if (maxVal !== null) {
                        cZ.value = parseFloat(maxVal.toFixed(1));
                        cZ.numFmt = '#,##0.0';
                    } else {
                        cZ.value = '';
                    }
                    cZ.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Col AA: Promedio
                    const cAA = sheet.getCell(currentRow, 27);
                    cAA.value = parseFloat(avgVal.toFixed(1));
                    cAA.numFmt = '#,##0.0';
                    cAA.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Col AB: Cumple/no cumple el valor
                    const cAB = sheet.getCell(currentRow, 28);
                    cAB.value = isComp ? 'Cumple' : 'No Cumple';
                    cAB.alignment = { vertical: 'middle', horizontal: 'center' };

                    // Col AC: Observaciones
                    const cAC = sheet.getCell(currentRow, 29);
                    cAC.value = item.observations && item.observations !== 'Sin observaciones' ? item.observations : '';
                    cAC.alignment = { vertical: 'middle', horizontal: 'left' };

                    // Aplicar fuentes y bordes a toda la fila
                    for (let c = 1; c <= 29; c++) {
                        const cell = sheet.getCell(currentRow, c);
                        cell.font = { name: 'Calibri', size: 9 };
                        cell.border = thinBorder;
                    }

                    currentRow++;
                });

                // Descargar archivo .xlsx
                const buffer = await workbook.xlsx.writeBuffer();
                const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                const fileNameSafe = (instName || 'Estudio_Iluminacion').replace(/[^a-zA-Z0-9_-]/g, '_');
                link.download = `Planilla_Iluminacion_${fileNameSafe}.xlsx`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                closeExportModal();

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Planilla Generada!',
                        text: 'El archivo Excel oficial ha sido descargado exitosamente.',
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        timer: 3500,
                        timerProgressBar: true,
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                    });
                }
            } catch (err) {
                console.error('Error al generar planilla Excel:', err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error de Exportación',
                        text: 'Ocurrió un inconveniente al generar el archivo Excel: ' + err.message,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-danger' }
                    });
                } else {
                    alert('Error al exportar: ' + err.message);
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateIlluminationPagination();

            const createForm = document.getElementById('createMeasurementForm');
            if (createForm) {
                createForm.addEventListener('submit', function (e) {
                    if (isOptimizingPhotos) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Optimizando fotografías',
                                text: 'Por favor espera unos momentos mientras se analiza y optimiza el peso y calidad de las imágenes.',
                                icon: 'info',
                                confirmButtonText: 'Entendido',
                                customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                            });
                        } else {
                            alert('Por favor espera unos momentos mientras se analiza y optimiza el peso y calidad de las imágenes.');
                        }
                        return false;
                    }
                    const btn = document.getElementById('create_modal_submit_btn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = `
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            <span>Guardando punto y fotografías...</span>
                        `;
                    }
                });
            }

            const editForm = document.getElementById('editMeasurementForm');
            if (editForm) {
                editForm.addEventListener('submit', function (e) {
                    if (isOptimizingPhotos) {
                        e.preventDefault();
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                title: 'Optimizando fotografías',
                                text: 'Por favor espera unos momentos mientras se analiza y optimiza el peso y calidad de las imágenes.',
                                icon: 'info',
                                confirmButtonText: 'Entendido',
                                customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                            });
                        } else {
                            alert('Por favor espera unos momentos mientras se analiza y optimiza el peso y calidad de las imágenes.');
                        }
                        return false;
                    }
                    const btn = document.getElementById('edit_modal_submit_btn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = `
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                            <span>Guardando cambios y fotografías...</span>
                        `;
                    }
                });
            }

            ['createMeasurementModal', 'editMeasurementModal', 'mapLocationModal', 'photoViewerModal', 'exportOptionsModal', 'allLocationsModal', 'photoReportModal'].forEach(id => {
                const modal = document.getElementById(id);
                if (modal) {
                    modal.addEventListener('click', (e) => {
                        if (e.target === modal) modal.classList.remove('open');
                    });
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    closeCreateMeasurementModal();
                    closeEditMeasurementModal();
                    closeMapModal();
                    closePhotoViewer();
                    closeExportModal();
                    closeAllLocationsModal();
                    closePhotoReportModal();
                }
            });
        });

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            setTimeout(updateIlluminationPagination, 60);
        }
// Exposición de funciones en window para eventos inline HTML (onclick, onchange, etc.)
window.onActivityDescriptionChange = onActivityDescriptionChange;
window.onRequiredLuxChange = onRequiredLuxChange;
window.updateNormativeLegend = updateNormativeLegend;
window.addReadingPoint = addReadingPoint;
window.removeReadingPoint = removeReadingPoint;
window.renderReadingsBadges = renderReadingsBadges;
window.renderPhotoSlider = renderPhotoSlider;
window.slidePhotoNav = slidePhotoNav;
window.selectSlidePhoto = selectSlidePhoto;
window.deleteActivePhoto = deleteActivePhoto;
window.syncEditRemainingImages = syncEditRemainingImages;
window.compressImageFile = compressImageFile;
window.handleMultipleImagesSelected = handleMultipleImagesSelected;
window.latLngToUtm = latLngToUtm;
window.utmToLatLng = utmToLatLng;
window.updateUtmFromLatLng = updateUtmFromLatLng;
window.syncUtmToMap = syncUtmToMap;
window.initModalMiniMap = initModalMiniMap;
window.autoSaveHeaderField = autoSaveHeaderField;
window.getCurrentGpsPosition = getCurrentGpsPosition;
window.updateIlluminationPagination = updateIlluminationPagination;
window.renderPaginationControls = renderPaginationControls;
window.goToIllPage = goToIllPage;
window.filterIllumination = filterIllumination;
window.searchIlluminationLive = searchIlluminationLive;
window.openMapModal = openMapModal;
window.initOrUpdateLeafletMap = initOrUpdateLeafletMap;
window.closeMapModal = closeMapModal;
window.expandCurrentMapPhoto = expandCurrentMapPhoto;
window.openCreateMeasurementModal = openCreateMeasurementModal;
window.closeCreateMeasurementModal = closeCreateMeasurementModal;
window.applyEditModeState = applyEditModeState;
window.toggleModalEditMode = toggleModalEditMode;
window.openViewMeasurementModal = openViewMeasurementModal;
window.openEditMeasurementModal = openEditMeasurementModal;
window.closeEditMeasurementModal = closeEditMeasurementModal;
window.openPhotoViewer = openPhotoViewer;
window.closePhotoViewer = closePhotoViewer;
window.confirmDeleteMeasurement = confirmDeleteMeasurement;
window.openExportModal = openExportModal;
window.closeExportModal = closeExportModal;
window.openPhotoReportModal = openPhotoReportModal;
window.closePhotoReportModal = closePhotoReportModal;
window.initPhotoReportEngine = initPhotoReportEngine;
window.getPointImages = getPointImages;
window.formatPointCode = formatPointCode;
window.escapeHtml = escapeHtml;
window.getPhotosPerPage = getPhotosPerPage;
window.changeGridDistribution = changeGridDistribution;
window.updateGridSelectorButtonsUI = updateGridSelectorButtonsUI;
window.togglePointSelection = togglePointSelection;
window.selectAllPoints = selectAllPoints;
window.updateSelectionCounterUI = updateSelectionCounterUI;
window.navigatePointPhoto = navigatePointPhoto;
window.switchPhotoReportTab = switchPhotoReportTab;
window.renderInteractiveGrid = renderInteractiveGrid;
window.renderPhotoSheets = renderPhotoSheets;
window.triggerAutoSavePhotoReportConfig = triggerAutoSavePhotoReportConfig;
window.savePhotoReportSettingsToServer = savePhotoReportSettingsToServer;
window.printPhotoReport = printPhotoReport;
window.openAllLocationsModal = openAllLocationsModal;
window.closeAllLocationsModal = closeAllLocationsModal;
window.initAllLocationsMap = initAllLocationsMap;
window.focusPointOnAllLocationsMap = focusPointOnAllLocationsMap;
window.downloadExcelPlanilla = downloadExcelPlanilla;
