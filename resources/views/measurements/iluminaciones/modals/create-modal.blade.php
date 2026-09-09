    <!-- ==========================================================================
             MODAL 1: NUEVA MEDICIÓN DE ILUMINACIÓN (MODAL GRANDE EN 3 COLUMNAS)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="createMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4" />
                        <path d="M12 2v2" />
                        <path d="M12 20v2" />
                        <path d="m4.93 4.93 1.41 1.41" />
                        <path d="m17.66 17.66 1.41 1.41" />
                    </svg>
                    <h2 id="createMeasModalTitle">Nuevo Punto de Medición de Iluminación</h2>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()"
                    aria-label="Cerrar">✕</button>
            </div>
            <form action="{{ route('modules.illumination.measurements.store', $module->id) }}" method="POST"
                enctype="multipart/form-data" id="createMeasurementForm">
                @csrf
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos Técnicos y Mediciones LUX -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & LUX</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="create_pt_num_disp">{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="create_point_number"
                                value="{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}">

                            <!-- Fecha y Hora (Prominente) -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_date">Fecha de Medición <span
                                            class="req">*</span></label>
                                    <input type="date" name="measurement_date" id="create_measurement_date"
                                        class="custom-form-input" required value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_time">Hora</label>
                                    <input type="time" name="measurement_time" id="create_measurement_time"
                                        class="custom-form-input" value="{{ date('H:i') }}">
                                </div>
                            </div>

                            <!-- Técnico de Campo / Personal a Cargo -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_staff_id">
                                    <span>Técnico de Campo / Personal a Cargo <span class="req">*</span></span>
                                </label>
                                <select name="staff_id" id="create_staff_id" class="custom-form-select" required>
                                    @if(isset($staffList) && count($staffList) > 0)
                                        @foreach($staffList as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name }} @if(!empty($staff->position)) — {{ $staff->position }} @endif</option>
                                        @endforeach
                                    @else
                                        <option value="">{{ $registeredByHeader }}</option>
                                    @endif
                                </select>
                            </div>

                            <!-- Área y Puesto de Trabajo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_area">Área de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="area" id="create_area" class="custom-form-input" required
                                        placeholder="Ej: Planta de Producción">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_workstation">Puesto de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="workstation" id="create_workstation" class="custom-form-input"
                                        required placeholder="Ej: Operador de Prensa #1">
                                </div>
                            </div>

                            <!-- 1. Punto de Medición -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_measurement_point">Punto de Medición <span
                                        class="req">*</span></label>
                                <input type="text" name="measurement_point" id="create_measurement_point"
                                    class="custom-form-input" required placeholder="Ej: Mesa central plano 0.85m">
                            </div>

                            <!-- 2. Descripción de la Actividad (Determina el Nivel Requerido) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_activity_description">
                                    <span>Descripción de la Actividad <span class="req">*</span></span>
                                    <span style="font-size: 11px; color: #0284c7; font-weight: 600;">Tipo de Tarea /
                                        Área</span>
                                </label>
                                <select name="activity_description" id="create_activity_description"
                                    class="custom-form-select" required onchange="onActivityDescriptionChange('create')">
                                    <option value="Paso en construcción" data-lux="25">Paso en construcción</option>
                                    <option value="Tránsito general" data-lux="50">Tránsito general</option>
                                    <option value="Trabajo en construcción" data-lux="75">Trabajo en construcción</option>
                                    <option value="Tareas simples" data-lux="100">Tareas simples</option>
                                    <option value="Oficinas y talleres" data-lux="300" selected>Oficinas y talleres</option>
                                    <option value="Finos y detalle" data-lux="750">Finos y detalle</option>
                                    <option value="Alta precisión" data-lux="1500">Alta precisión</option>
                                    <option value="Casos especiales" data-lux="3000">Casos especiales</option>
                                </select>
                            </div>

                            <!-- 3. Tipo de Iluminación -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_lighting_type">Tipo de Iluminación <span
                                        class="req">*</span></label>
                                <select name="lighting_type" id="create_lighting_type" class="custom-form-select" required>
                                    <option value="Artificial" selected>Artificial</option>
                                    <option value="Natural">Natural</option>
                                    <option value="Mixta">Mixta</option>
                                </select>
                            </div>

                            <!-- 4. Nivel Requerido (Desplegable Normativo sincronizado con Leyenda) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_required_lux">
                                    <span>Nivel Requerido (LUX) <span class="req">*</span></span>
                                </label>
                                <select name="required_lux" id="create_required_lux" class="custom-form-select"
                                    onchange="onRequiredLuxChange('create')">
                                    <option value="25">25 LUX</option>
                                    <option value="50">50 LUX</option>
                                    <option value="75">75 LUX</option>
                                    <option value="100">100 LUX</option>
                                    <option value="300" selected>300 LUX</option>
                                    <option value="750">750 LUX</option>
                                    <option value="1500">1500 LUX</option>
                                    <option value="3000">3000 LUX</option>
                                </select>
                                <!-- Leyenda de aplicación según norma -->
                                <div id="create_normative_legend" class="normative-lux-legend">
                                    <div class="legend-top-row">
                                        <span class="legend-area-name" id="create_legend_area">Oficinas y talleres</span>
                                        <span class="legend-lux-badge" id="create_legend_badge">300 LUX Mínimo</span>
                                    </div>
                                    <p class="legend-app-text" id="create_legend_app">Computadoras, lectura, escritura y
                                        trabajos comunes.</p>
                                </div>
                            </div>

                            <!-- 5. Mediciones LUX (Hasta 25 puntos en Badges) -->
                            <div class="form-field-group">
                                <div class="form-field-label">
                                    <span>Lecturas de Luxometría (Hasta 25 puntos)</span>
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7;"
                                        id="create_readings_count_badge">0/25</span>
                                </div>
                                <div class="readings-input-bar">
                                    <input type="number" step="0.1" id="create_quick_lux_input" class="custom-form-input"
                                        placeholder="Ej: 345.5 (Presiona Enter para agregar)"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault(); addReadingPoint('create');}">
                                    <button type="button" class="btn-add-reading" onclick="addReadingPoint('create')">+
                                        Agregar</button>
                                </div>
                                <!-- Badges de Puntos Registrados -->
                                <div id="create_readings_badges_container" class="readings-badges-container">
                                    <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas
                                        agregadas. Ingrese valores (hasta 25 puntos) o escriba el valor promedio.</span>
                                </div>
                                <div class="readings-summary-strip" id="create_readings_summary_strip">
                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <span>Mín: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Máx: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Promedio: <strong id="create_calc_lux_display" style="color: #94a3b8;">0.0
                                                LUX</strong></span>
                                    </div>
                                    <div>
                                        <span
                                            style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Sin
                                            datos</span>
                                    </div>
                                </div>
                                <input type="hidden" name="readings" id="create_readings_json" value="[]">
                                <input type="hidden" name="measured_lux" id="create_measured_lux" value="300">
                            </div>

                            <!-- Personal Registrador (Fijo en encabezado, no modificable) -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label">Personal Registrador</label>
                                <div class="custom-form-input"
                                    style="background: #f8fafc; display: flex; align-items: center; gap: 7px; color: #334155; font-weight: 600; cursor: default;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    <span>{{ $registeredByHeader }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico TIPO SLIDE / CARRUSEL (Más grande) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Archivo Fotográfico (Slide)</span>
                                <span style="font-size: 11px; color: #64748b;" id="create_photo_count_indicator">0
                                    fotos</span>
                            </div>

                            <div class="modal-photo-slider-wrapper">
                                <div class="modal-slider-viewport" id="create_slider_viewport">
                                    <span id="create_slider_counter" class="slider-counter-badge" style="display: none;">1 /
                                        1</span>

                                    <button type="button" class="slider-nav-btn prev" id="create_slider_btn_prev"
                                        onclick="slidePhotoNav('create', -1)" style="display: none;"
                                        aria-label="Anterior">❮</button>
                                    <button type="button" class="slider-nav-btn next" id="create_slider_btn_next"
                                        onclick="slidePhotoNav('create', 1)" style="display: none;"
                                        aria-label="Siguiente">❯</button>

                                    <img id="create_slider_img" class="modal-slider-main-img" src="" alt="Foto punto"
                                        style="display: none;">

                                    <div id="create_slider_placeholder" class="modal-slider-placeholder"
                                        onclick="document.getElementById('create_images_input').click()"
                                        style="cursor: pointer;">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Subir
                                            Fotografías del Punto</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Haz clic para seleccionar una o más
                                            imágenes</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="create_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="create_images_input" multiple accept="image/*"
                                    style="display: none;" onchange="handleMultipleImagesSelected(this, 'create')">
                                <button type="button" class="btn-add-photos-trigger" id="create_btn_add_photos"
                                    onclick="document.getElementById('create_images_input').click()">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span>+ Subir / Agregar Fotografías</span>
                                </button>
                            </div>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite subir varias fotos
                                del punto. Puedes pasar imagen por imagen con las flechas.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Dónde Está Ubicado (GPS + Mini Mapa + Observaciones) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button"
                                    onclick="getCurrentGpsPosition('create_latitude', 'create_longitude', 'create')"
                                    style="background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <circle cx="12" cy="12" r="10" />
                                        <circle cx="12" cy="12" r="3" />
                                        <line x1="12" y1="2" x2="12" y2="5" />
                                        <line x1="12" y1="19" x2="12" y2="22" />
                                        <line x1="2" y1="12" x2="5" y2="12" />
                                        <line x1="19" y1="12" x2="22" y2="12" />
                                    </svg>
                                    Mi GPS
                                </button>
                            </div>

                            <div class="utm-coords-grid">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_utm_easting">Este (E)</label>
                                    <input type="number" step="any" id="create_utm_easting" class="custom-form-input"
                                        placeholder="218468.016" oninput="syncUtmToMap('create')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" id="create_utm_northing" class="custom-form-input"
                                        placeholder="7627234.367" oninput="syncUtmToMap('create')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label" for="create_utm_zone">Zona (Z)</label>
                                    <input type="text" id="create_utm_zone" class="custom-form-input" placeholder="20K"
                                        value="20K" oninput="syncUtmToMap('create')">
                                </div>
                            </div>
                            <div class="utm-preview-pill">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                    <path d="M2 12h20" />
                                </svg>
                                <span id="create_utm_display">E: —, N: —, Z: 20K</span>
                            </div>
                            <input type="hidden" name="location" id="create_location">
                            <input type="hidden" name="latitude" id="create_latitude">
                            <input type="hidden" name="longitude" id="create_longitude">

                            <!-- Mini Mapa interactivo: Dónde está ubicado -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa (Haz clic para
                                    posicionar)</label>
                                <div id="create_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones: Textarea directamente debajo del mapa -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="create_observations">Observaciones</label>
                                <textarea name="observations" id="create_observations" class="custom-form-textarea" rows="3"
                                    placeholder="Observaciones técnicas, fuentes de deslumbramiento, estado de luminarias..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle"
                        onclick="closeCreateMeasurementModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action" id="create_modal_submit_btn">Guardar Punto de
                        Medición</button>
                </div>
            </form>
        </div>
    </div>
