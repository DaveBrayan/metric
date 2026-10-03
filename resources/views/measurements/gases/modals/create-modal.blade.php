    <!-- ==========================================================================
         MODAL 1: NUEVA MEDICIÓN DE GASES (MODAL GRANDE EN 3 COLUMNAS)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="createMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z" />
                    </svg>
                    <h2 id="createMeasModalTitle">Nuevo Punto de Monitoreo de Gases</h2>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()"
                    aria-label="Cerrar">✕</button>
            </div>
            <form action="{{ route('modules.gases.measurements.store', $module->id) }}" method="POST"
                enctype="multipart/form-data" id="createMeasurementForm">
                @csrf
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos Técnicos, Clima & Gases Analíticos -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & Gases</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="create_pt_num_disp">{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="create_point_number"
                                value="{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}">

                            <!-- Fecha y Hora -->
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
                                        placeholder="Ej: Mina Subterránea / Taller">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_workstation">Puesto de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="workstation" id="create_workstation" class="custom-form-input"
                                        required placeholder="Ej: Operador de Perforadora">
                                </div>
                            </div>

                            <!-- Punto de Medición y Actividad -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_point">Punto de Medición <span
                                            class="req">*</span></label>
                                    <input type="text" name="measurement_point" id="create_measurement_point"
                                        class="custom-form-input" required placeholder="Ej: Frente de avance nivel 3">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_activity_description">Descripción de Actividad</label>
                                    <input type="text" name="activity_description" id="create_activity_description"
                                        class="custom-form-input" placeholder="Ej: Perforación y voladura">
                                </div>
                            </div>

                            <!-- CONDICIONES AMBIENTALES / METEOROLÓGICAS (2 + 1 columnas) -->
                            <div class="environmental-section-card">
                                <div class="env-section-header">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                                    </svg>
                                    <span>Condiciones Ambientales / Meteorológicas</span>
                                </div>
                                <div class="form-grid-two-cols">
                                    <div class="form-field-group">
                                        <label class="form-field-label" for="create_temperatura">TEMPERATURA (°C)</label>
                                        <input type="number" step="0.1" name="temperatura" id="create_temperatura"
                                            class="custom-form-input" placeholder="Ej: 22.5">
                                    </div>
                                    <div class="form-field-group">
                                        <label class="form-field-label" for="create_presion_atm">PRESIÓN ATMOSFÉRICA (mmHg)</label>
                                        <input type="number" step="0.1" name="presion_atm" id="create_presion_atm"
                                            class="custom-form-input" placeholder="Ej: 495.0">
                                    </div>
                                </div>
                                <div class="form-field-group" style="margin-bottom: 0;">
                                    <label class="form-field-label" for="create_vel_aire">VEL. PROM. AIRE (Km/h)</label>
                                    <input type="number" step="0.1" name="vel_aire" id="create_vel_aire"
                                        class="custom-form-input" placeholder="Ej: 1.2">
                                </div>
                            </div>

                            <!-- LECTURAS ANALÍTICAS DE GASES DINÁMICAS (3 MEDICIONES C/U) -->
                            <div class="gases-analytical-section-card">
                                <div class="gases-section-header">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.3">
                                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z" />
                                        </svg>
                                        <span style="font-weight: 800; font-size: 12.5px; color: var(--ink);">Lecturas Analíticas de Gases</span>
                                    </div>
                                    <span id="create_selected_gases_count_badge" class="badge-count-pill">0 seleccionados</span>
                                </div>

                                <!-- Selector de Gases -->
                                <div class="add-gas-selector-bar">
                                    <select id="create_gas_to_add_select" class="custom-form-select" style="flex: 1;">
                                        <option value="" selected disabled>+ Seleccionar gas a evaluar...</option>
                                        <option value="o2">Oxígeno O2 (ppm)</option>
                                        <option value="h2s">Ácido Sulfhídrico H2S (ppm)</option>
                                        <option value="co">Monóxidos de Carbono CO (ppm)</option>
                                        <option value="lel">Gases combustibles LEL (%)</option>
                                        <option value="hcho">Formaldehidos HCHO (mg/m3)</option>
                                        <option value="tvoc">Compuesto Orgánicos Volátiles T-VOC (mg/m3)</option>
                                        <option value="co2">Dióxido de Carbono CO2 (ppm)</option>
                                        <option value="as">Arsénico inorgánico As (mg/m3)</option>
                                        <option value="so2">Dioxido de azufre SO2 (ppm)</option>
                                        <option value="nh3">Amoniaco NH3 (ppm)</option>
                                        <option value="cl2">Cloro gaseoso Cl2 (ppm)</option>
                                        <option value="tcov">Compuestos organicos volatiles TCOV (ppm)</option>
                                        <option value="no2">Dioxido de nitrogeno NO2 (ppm)</option>
                                    </select>
                                    <button type="button" class="btn-add-gas-action" onclick="addGasCardFromSelect('create')">
                                        + Agregar Gas
                                    </button>
                                </div>

                                <!-- Contenedor de Tarjetas de Gases Dinámicos -->
                                <div id="create_dynamic_gases_container" class="dynamic-gases-cards-scroll">
                                    <div class="empty-gases-msg" id="create_empty_gases_msg">
                                        No hay gases seleccionados para este punto. Selecciona uno arriba para ingresar sus 3 mediciones.
                                    </div>
                                </div>

                                <input type="hidden" name="selected_gases" id="create_selected_gases_json" value="[]">
                                <input type="hidden" name="gases_readings" id="create_gases_readings_json" value="{}">
                            </div>

                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico TIPO SLIDE / CARRUSEL -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Archivo Fotográfico (Slide)</span>
                                <span style="font-size: 11px; color: #64748b;" id="create_photo_count_indicator">0 fotos</span>
                            </div>

                            <div class="modal-photo-slider-wrapper">
                                <div class="modal-slider-viewport" id="create_slider_viewport">
                                    <span id="create_slider_counter" class="slider-counter-badge" style="display: none;">1 / 1</span>

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
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Subir Fotografías del Punto</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Haz clic para seleccionar una o más imágenes</span>
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
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite subir varias fotos del punto de muestreo. Puedes navegar con las flechas.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica, GPS, Mini Mapa & Observaciones -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button"
                                    onclick="getCurrentGpsPosition('create_latitude', 'create_longitude', 'create')"
                                    style="background: none; border: none; color: #10b9df; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
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
                                    <input type="number" step="any" name="utm_easting" id="create_utm_easting" class="custom-form-input"
                                        placeholder="218468.016" oninput="syncUtmToMap('create')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" name="utm_northing" id="create_utm_northing" class="custom-form-input"
                                        placeholder="7627234.367" oninput="syncUtmToMap('create')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label" for="create_utm_zone">Zona (Z)</label>
                                    <input type="text" name="utm_zone" id="create_utm_zone" class="custom-form-input" placeholder="20K"
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
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa (Haz clic para posicionar)</label>
                                <div id="create_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="create_observations">Observaciones</label>
                                <textarea name="observations" id="create_observations" class="custom-form-textarea" rows="3"
                                    placeholder="Observaciones de ventilación, presencia de olores, uso de EPP respiratorio..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle"
                        onclick="closeCreateMeasurementModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action" id="create_modal_submit_btn">Guardar Punto de Gases</button>
                </div>
            </form>
        </div>
    </div>
