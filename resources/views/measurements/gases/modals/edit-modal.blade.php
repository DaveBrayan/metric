    <!-- ==========================================================================
         MODAL 2: DETALLE / EDICIÓN DE MEDICIÓN DE GASES (MODO CONSULTA CON BOTÓN EDITAR)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="editMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <h2 id="editMeasModalTitle">Detalle del Punto de Monitoreo de Gases</h2>
                    <span id="edit_modal_mode_badge" class="modal-badge-view">Solo Lectura</span>
                    <!-- Personal Registrado al lado de Solo Lectura -->
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9999px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; color: #334155;"
                        title="Personal Registrador">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                            <circle cx="12" cy="7" r="4" />
                        </svg>
                        <span id="edit_modal_registered_by">{{ $registeredByHeader }}</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- Botón para activar/desactivar la edición -->
                    <button type="button" class="btn-modal-edit-action" id="edit_btn_toggle_edit"
                        onclick="toggleEditLock()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                            <path d="m15 5 4 4" />
                        </svg>
                        <span>Editar</span>
                    </button>
                    <button type="button" class="btn-close-modal" onclick="closeEditMeasurementModal()"
                        aria-label="Cerrar">✕</button>
                </div>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" id="editMeasurementForm" class="modal-view-mode">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos Técnicos, Clima & Gases Analíticos -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & Gases</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="edit_pt_num_disp">01</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="edit_point_number">

                            <!-- Fecha y Hora -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_date">Fecha de Medición <span
                                            class="req">*</span></label>
                                    <input type="date" name="measurement_date" id="edit_measurement_date"
                                        class="custom-form-input" required disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_time">Hora</label>
                                    <input type="time" name="measurement_time" id="edit_measurement_time"
                                        class="custom-form-input" disabled>
                                </div>
                            </div>

                            <!-- Técnico de Campo / Personal a Cargo -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_staff_id">
                                    <span>Técnico de Campo / Personal a Cargo <span class="req">*</span></span>
                                </label>
                                <select name="staff_id" id="edit_staff_id" class="custom-form-select" disabled
                                    onchange="if(document.getElementById('edit_modal_registered_by')) document.getElementById('edit_modal_registered_by').textContent = this.options[this.selectedIndex].text.split('—')[0].trim();">
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
                                    <label class="form-field-label" for="edit_area">Área de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="area" id="edit_area" class="custom-form-input" required
                                        disabled placeholder="Ej: Mina Subterránea / Taller">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_workstation">Puesto de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="workstation" id="edit_workstation" class="custom-form-input"
                                        required disabled placeholder="Ej: Operador de Perforadora">
                                </div>
                            </div>

                            <!-- Punto de Medición y Actividad -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_point">Punto de Medición <span
                                            class="req">*</span></label>
                                    <input type="text" name="measurement_point" id="edit_measurement_point"
                                        class="custom-form-input" required disabled placeholder="Ej: Frente de avance nivel 3">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_activity_description">Descripción de Actividad</label>
                                    <input type="text" name="activity_description" id="edit_activity_description"
                                        class="custom-form-input" disabled placeholder="Ej: Perforación y voladura">
                                </div>
                            </div>

                            <!-- CONDICIONES AMBIENTALES / METEOROLÓGICAS (2 + 1 columnas) -->
                            <div class="environmental-section-card">
                                <div class="env-section-header">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>
                                    </svg>
                                    <span>Condiciones Ambientales / Meteorológicas</span>
                                </div>
                                <div class="form-grid-two-cols">
                                    <div class="form-field-group">
                                        <label class="form-field-label" for="edit_temperatura">TEMPERATURA (°C)</label>
                                        <input type="number" step="0.1" name="temperatura" id="edit_temperatura"
                                            class="custom-form-input" disabled placeholder="Ej: 22.5">
                                    </div>
                                    <div class="form-field-group">
                                        <label class="form-field-label" for="edit_presion_atm">PRESIÓN ATMOSFÉRICA (mmHg)</label>
                                        <input type="number" step="0.1" name="presion_atm" id="edit_presion_atm"
                                            class="custom-form-input" disabled placeholder="Ej: 495.0">
                                    </div>
                                </div>
                                <div class="form-field-group" style="margin-bottom: 0;">
                                    <label class="form-field-label" for="edit_vel_aire">VEL. PROM. AIRE (Km/h)</label>
                                    <input type="number" step="0.1" name="vel_aire" id="edit_vel_aire"
                                        class="custom-form-input" disabled placeholder="Ej: 1.2">
                                </div>
                            </div>

                            <!-- LECTURAS ANALÍTICAS DE GASES DINÁMICAS (3 MEDICIONES C/U) -->
                            <div class="gases-analytical-section-card">
                                <div class="gases-section-header">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3">
                                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z" />
                                        </svg>
                                        <span style="font-weight: 800; font-size: 12.5px; color: var(--ink);">Lecturas Analíticas de Gases</span>
                                    </div>
                                    <span id="edit_selected_gases_count_badge" class="badge-count-pill">0 seleccionados</span>
                                </div>

                                <!-- Selector de Gases (Oculto en Modo Consulta) -->
                                <div class="add-gas-selector-bar" id="edit_add_gas_bar" style="display: none;">
                                    <select id="edit_gas_to_add_select" class="custom-form-select" style="flex: 1;">
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
                                    <button type="button" class="btn-add-gas-action" onclick="addGasCardFromSelect('edit')">
                                        + Agregar Gas
                                    </button>
                                </div>

                                <!-- Contenedor de Tarjetas de Gases Dinámicos -->
                                <div id="edit_dynamic_gases_container" class="dynamic-gases-cards-scroll">
                                    <div class="empty-gases-msg" id="edit_empty_gases_msg">
                                        No hay gases seleccionados para este punto.
                                    </div>
                                </div>

                                <input type="hidden" name="selected_gases" id="edit_selected_gases_json" value="[]">
                                <input type="hidden" name="gases_readings" id="edit_gases_readings_json" value="{}">
                            </div>

                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico TIPO SLIDE / CARRUSEL -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Archivo Fotográfico (Slide)</span>
                                <span style="font-size: 11px; color: #64748b;" id="edit_photo_count_indicator">0 fotos</span>
                            </div>

                            <div class="modal-photo-slider-wrapper">
                                <div class="modal-slider-viewport" id="edit_slider_viewport">
                                    <span id="edit_slider_counter" class="slider-counter-badge" style="display: none;">1 / 1</span>

                                    <button type="button" class="slider-nav-btn prev" id="edit_slider_btn_prev"
                                        onclick="slidePhotoNav('edit', -1)" style="display: none;"
                                        aria-label="Anterior">❮</button>
                                    <button type="button" class="slider-nav-btn next" id="edit_slider_btn_next"
                                        onclick="slidePhotoNav('edit', 1)" style="display: none;"
                                        aria-label="Siguiente">❯</button>

                                    <img id="edit_slider_img" class="modal-slider-main-img" src="" alt="Foto punto"
                                        style="display: none;">

                                    <div id="edit_slider_placeholder" class="modal-slider-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografías</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">No se han adjuntado fotos a este punto</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*"
                                    style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                                <input type="hidden" name="remaining_images" id="edit_remaining_images_json" value="[]">

                                <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos"
                                    onclick="document.getElementById('edit_images_input').click()" style="display: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span>+ Subir / Agregar Fotografías</span>
                                </button>
                            </div>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar y gestionar fotos del punto de muestreo.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica, GPS, Mini Mapa & Observaciones -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button" id="edit_btn_gps"
                                    onclick="getCurrentGpsPosition('edit_latitude', 'edit_longitude', 'edit')"
                                    style="display: none; background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; align-items: center; gap: 3px;">
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
                                    <label class="form-field-label" for="edit_utm_easting">Este (E)</label>
                                    <input type="number" step="any" name="utm_easting" id="edit_utm_easting" class="custom-form-input"
                                        placeholder="585325.237" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" name="utm_northing" id="edit_utm_northing" class="custom-form-input"
                                        placeholder="8169060.368" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                    <input type="text" name="utm_zone" id="edit_utm_zone" class="custom-form-input" placeholder="20K"
                                        value="20K" disabled oninput="syncUtmToMap('edit')">
                                </div>
                            </div>
                            <div class="utm-preview-pill">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                    <path d="M2 12h20" />
                                </svg>
                                <span id="edit_utm_display">E: —, N: —, Z: 20K</span>
                            </div>
                            <input type="hidden" name="location" id="edit_location">
                            <input type="hidden" name="latitude" id="edit_latitude">
                            <input type="hidden" name="longitude" id="edit_longitude">

                            <!-- Mini Mapa interactivo: Dónde está ubicado -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="edit_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3"
                                    disabled
                                    placeholder="Observaciones técnicas, fuentes de emisión, estado de ventilación..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle"
                        onclick="closeEditMeasurementModal()">Cerrar</button>
                    <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn"
                        style="display: none;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
