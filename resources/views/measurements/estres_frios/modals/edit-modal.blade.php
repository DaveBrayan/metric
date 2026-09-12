    <!-- ==========================================================================
         MODAL 2: DETALLE / EDICIÓN DE MEDICIÓN DE ESTRÉS POR FRÍO
         (MODO CONSULTA 3 COLUMNAS CON BOTÓN EDITAR SUPERIOR IDÉNTICO A ILUMINACIÓN / CALOR)
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
                    <h2 id="editMeasModalTitle">Detalle del Punto de Medición</h2>
                    <span id="modalModeStatusBadge" class="modal-badge-view">Solo Lectura</span>
                    
                    <!-- Personal Registrado en el Encabezado -->
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
                    <button type="button" class="btn-modal-edit-action" id="btnToggleEditMode"
                        onclick="toggleModalEditMode()">
                        <span id="btnToggleEditModeIcon" style="display: inline-flex; align-items: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                <path d="m15 5 4 4" />
                            </svg>
                        </span>
                        <span id="btnToggleEditModeText">Editar</span>
                    </button>
                    <button type="button" class="btn-close-modal" onclick="closeEditMeasurementModal()"
                        aria-label="Cerrar">✕</button>
                </div>
            </div>

            <form id="editMeasurementForm" action="" method="POST" enctype="multipart/form-data" class="modal-view-mode">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos Operativos, Vestimenta & Metabolismo -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & Actividad</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="edit_pt_num_disp">01</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="edit_point_number">

                            <!-- Fecha y Hora -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_date">Fecha <span class="req">*</span></label>
                                    <input type="date" name="measurement_date" id="edit_measurement_date"
                                        class="custom-form-input" required disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_time">Hora</label>
                                    <input type="time" name="measurement_time" id="edit_measurement_time"
                                        class="custom-form-input" disabled>
                                </div>
                            </div>

                            <!-- Personal Registrador -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_staff_id">
                                    <span>Técnico de Campo / Personal <span class="req">*</span></span>
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

                            <!-- Área / Sección -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_area">Área / Sección <span class="req">*</span></label>
                                <input type="text" name="area" id="edit_area" class="custom-form-input" required disabled placeholder="Ej: Cámara Frigorífica 01">
                            </div>

                            <!-- Puesto de Trabajo -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_puesto_trabajo">Puesto de Trabajo <span class="req">*</span></label>
                                <input type="text" name="puesto_trabajo" id="edit_puesto_trabajo" class="custom-form-input" required disabled placeholder="Ej: Operador de Cámara">
                            </div>

                            <!-- Descripción de Actividades -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_desc_actividades">Descripción de Actividades</label>
                                <input type="text" name="desc_actividades" id="edit_desc_actividades" class="custom-form-input" disabled placeholder="Ej: Estiba y despacho de productos congelados">
                            </div>

                            <!-- Actividad Metabólica -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_metabolismo">Actividad Metabólica <span class="req">*</span></label>
                                <select name="metabolismo" id="edit_metabolismo" class="custom-form-select" disabled onchange="recalcColdStress('edit')">
                                    <option value="Metabolismo ligero (115 W)">Ligero (115 W - Inspección / Escritorio)</option>
                                    <option value="Metabolismo moderado (200 W)">Moderado (200 W - Trabajo manual / Caminata)</option>
                                    <option value="Metabolismo alto (300 W)">Alto (300 W - Carga pesada / Esfuerzo)</option>
                                    <option value="Metabolismo muy alto (400 W)">Muy Alto (400 W - Trabajo continuo intenso)</option>
                                </select>
                            </div>

                            <!-- Aislamiento Térmico de Vestimenta -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label" for="edit_aislamiento">Aislamiento Vestimenta (clo)</label>
                                <select name="aislamiento" id="edit_aislamiento" class="custom-form-select" disabled onchange="recalcColdStress('edit')">
                                    <option value="Vestimenta estándar (1.0 clo)">Ropa normal de trabajo (1.0 clo)</option>
                                    <option value="Ropa térmica ligera (1.5 clo)">Térmica ligera + guantes (1.5 clo)</option>
                                    <option value="Traje frigorífico completo (2.5 clo)">Traje térmico frigorífico (2.5 clo)</option>
                                    <option value="Traje polar extremo (3.5 clo)">Polar extremo con capucha (3.5 clo)</option>
                                </select>
                            </div>
                        </div>

                        <!-- COLUMNA 2: Parámetros Termo-Anemométricos & Cálculo WCI -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Parámetros & Evaluación WCI</span>
                            </div>

                            <!-- Temperatura Aire Ta (°C) y Velocidad Viento (m/s) -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_temp_c">T. Aire Ta (°C) <span class="req">*</span></label>
                                    <input type="number" step="0.1" name="temp_c" id="edit_temp_c" class="custom-form-input" required disabled oninput="recalcColdStress('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_vel_viento_ms">Vel. Viento (m/s)</label>
                                    <input type="number" step="0.01" min="0" name="vel_viento_ms" id="edit_vel_viento_ms" class="custom-form-input" disabled oninput="recalcColdStress('edit')">
                                </div>
                            </div>

                            <!-- %HR y Presión Barométrica -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_hr_percent">% Humedad Relativa</label>
                                    <input type="number" step="0.1" min="0" max="100" name="hr_percent" id="edit_hr_percent" class="custom-form-input" disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_presion_mmhg">Presión P (mmHg)</label>
                                    <input type="number" step="1" name="presion_mmhg" id="edit_presion_mmhg" class="custom-form-input" disabled>
                                </div>
                            </div>

                            <!-- Previsualización de Cálculos en Vivo de Estrés por Frío -->
                            <div class="calculation-preview-box" style="margin-top: 6px; background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 12px; padding: 12px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <span style="font-size: 11.5px; font-weight: 800; color: #0284c7; text-transform: uppercase;">Evaluación WCI / Sensación</span>
                                    <span id="edit_riesgo_badge"><span class="table-compliance-badge badge-cumple"><span>Riesgo Bajo</span></span></span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; text-align: center;">
                                    <div style="background: #ffffff; padding: 6px 8px; border-radius: 8px; border: 1px solid #e0f2fe;">
                                        <span style="font-size: 10.5px; color: #64748b; display: block;">Sensación Térmica</span>
                                        <strong id="edit_sensacion_display" style="font-size: 14px; color: #0284c7;">— °C</strong>
                                    </div>
                                    <div style="background: #ffffff; padding: 6px 8px; border-radius: 8px; border: 1px solid #e0f2fe;">
                                        <span style="font-size: 10.5px; color: #64748b; display: block;">Índice WCI</span>
                                        <strong id="edit_wci_display" style="font-size: 14px; color: #0f172a;">—</strong>
                                    </div>
                                </div>
                                <div style="margin-top: 6px; background: #ffffff; padding: 6px 8px; border-radius: 8px; border: 1px solid #e0f2fe; text-align: center;">
                                    <span style="font-size: 10px; color: #64748b; display: block;">Tiempo Límite de Exposición (TLE)</span>
                                    <strong id="edit_tle_display" style="font-size: 11.5px; color: #334155;">Jornada normal con EPP</strong>
                                </div>
                            </div>

                            <!-- Observaciones / EPP Térmico -->
                            <div class="form-field-group" style="margin-top: 8px; margin-bottom: 0;">
                                <label class="form-field-label" for="edit_observations">Observaciones / EPP Térmico</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="2"
                                    disabled placeholder="Estado de indumentaria térmica, guantes, calzado aislante, rotación..."></textarea>
                            </div>
                        </div>

                        <!-- COLUMNA 3: Archivo Fotográfico & Ubicación / Mini-Mapa -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Archivo Fotográfico & GPS</span>
                                <button type="button" id="edit_btn_gps"
                                    onclick="captureCoordinatesGPS('edit')"
                                    style="display: none; background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; align-items: center; gap: 3px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                        <circle cx="12" cy="12" r="10" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    Mi GPS
                                </button>
                            </div>

                            <!-- Visor de Fotos Tipo Slide / Carrusel -->
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
                                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            style="margin-bottom: 6px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                        <strong style="font-size: 13px; color: #64748b; margin-bottom: 2px;">Sin Fotografía</strong>
                                        <span style="font-size: 11px; color: #94a3b8;">No se registraron imágenes para este punto</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="photos[]" id="edit_images_input" multiple accept="image/*"
                                    style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                                <input type="hidden" name="remaining_images" id="edit_remaining_images">
                                <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-top: 4px;">
                                    <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos"
                                        onclick="document.getElementById('edit_images_input').click()"
                                        style="display: none;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                            <path d="M5 12h14" /><path d="M12 5v14" />
                                        </svg>
                                        <span>+ Subir Fotos</span>
                                    </button>
                                    <button type="button" class="btn-delete-active-photo" id="edit_btn_delete_photo"
                                        onclick="deleteActivePhoto('edit')" style="display: none;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                            <polyline points="3 6 5 6 21 6" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        </svg>
                                        <span>Eliminar Foto</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Coordenadas UTM / GPS -->
                            <div class="utm-coords-grid" style="margin-top: 6px;">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_easting">Este (E)</label>
                                    <input type="number" step="any" id="edit_utm_easting" name="utm_easting" class="custom-form-input"
                                        placeholder="218468.0" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" id="edit_utm_northing" name="utm_northing" class="custom-form-input"
                                        placeholder="7627234.0" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group" style="max-width: 80px;">
                                    <label class="form-field-label" for="edit_utm_zone">Zona</label>
                                    <input type="text" id="edit_utm_zone" name="utm_zone" class="custom-form-input" placeholder="19K"
                                        value="19K" disabled oninput="syncUtmToMap('edit')">
                                </div>
                            </div>
                            <input type="hidden" name="latitude" id="edit_latitude">
                            <input type="hidden" name="longitude" id="edit_longitude">

                            <!-- Mini Mapa interactivo -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="edit_modal_map" class="modal-minimap-container" style="height: 120px;"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle" onclick="closeEditMeasurementModal()">Cerrar</button>
                    <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn"
                        style="display: none;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
