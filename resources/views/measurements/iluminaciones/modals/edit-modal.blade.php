    <!-- ==========================================================================
             MODAL 2: DETALLE / EDICIÓN DE MEDICIÓN (MODO CONSULTA CON BOTÓN EDITAR)
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

                        <!-- COLUMNA 1: Datos Técnicos y Mediciones LUX -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & LUX</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="edit_pt_num_disp">01</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="edit_point_number">

                            <!-- Fecha y Hora (Prominente) -->
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
                                        disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_workstation">Puesto de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="workstation" id="edit_workstation" class="custom-form-input"
                                        required disabled>
                                </div>
                            </div>

                            <!-- 1. Punto de Medición -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_measurement_point">Punto de Medición <span
                                        class="req">*</span></label>
                                <input type="text" name="measurement_point" id="edit_measurement_point"
                                    class="custom-form-input" required disabled>
                            </div>

                            <!-- 2. Descripción de la Actividad & Tipo de Iluminación en paralelo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_activity_description">
                                        <span>Descripción de Actividad <span class="req">*</span></span>
                                    </label>
                                    <select name="activity_description" id="edit_activity_description"
                                        class="custom-form-select" required disabled
                                        onchange="onActivityDescriptionChange('edit')">
                                        <option value="Paso en construcción" data-lux="25">Paso en construcción</option>
                                        <option value="Tránsito general" data-lux="50">Tránsito general</option>
                                        <option value="Trabajo en construcción" data-lux="75">Trabajo en construcción
                                        </option>
                                        <option value="Tareas simples" data-lux="100">Tareas simples</option>
                                        <option value="Oficinas y talleres" data-lux="300">Oficinas y talleres</option>
                                        <option value="Finos y detalle" data-lux="750">Finos y detalle</option>
                                        <option value="Alta precisión" data-lux="1500">Alta precisión</option>
                                        <option value="Casos especiales" data-lux="3000">Casos especiales</option>
                                    </select>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_lighting_type">Tipo de Iluminación <span
                                            class="req">*</span></label>
                                    <select name="lighting_type" id="edit_lighting_type" class="custom-form-select" required
                                        disabled>
                                        <option value="Artificial">Artificial</option>
                                        <option value="Natural">Natural</option>
                                        <option value="Mixta">Mixta</option>
                                    </select>
                                </div>
                            </div>

                            <!-- 3. Nivel Requerido (Sin la leyenda descriptiva) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_required_lux">
                                    <span>Nivel Requerido (LUX) <span class="req">*</span></span>
                                </label>
                                <select name="required_lux" id="edit_required_lux" class="custom-form-select" disabled
                                    onchange="onRequiredLuxChange('edit')">
                                    <option value="25">25 LUX</option>
                                    <option value="50">50 LUX</option>
                                    <option value="75">75 LUX</option>
                                    <option value="100">100 LUX</option>
                                    <option value="300">300 LUX</option>
                                    <option value="750">750 LUX</option>
                                    <option value="1500">1500 LUX</option>
                                    <option value="3000">3000 LUX</option>
                                </select>
                            </div>

                            <!-- 4. Mediciones LUX (Hasta 25 puntos en Badges) -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <div class="form-field-label">
                                    <span>Lecturas de Luxometría (Hasta 25 puntos)</span>
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7;"
                                        id="edit_readings_count_badge">0/25</span>
                                </div>
                                <div class="readings-input-bar" id="edit_readings_input_bar" style="display: none;">
                                    <input type="number" step="0.1" id="edit_quick_lux_input" class="custom-form-input"
                                        placeholder="Ej: 345.5 (Presiona Enter para agregar)"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault(); addReadingPoint('edit');}">
                                    <button type="button" class="btn-add-reading" onclick="addReadingPoint('edit')">+
                                        Agregar</button>
                                </div>
                                <!-- Badges de Puntos Registrados -->
                                <div id="edit_readings_badges_container" class="readings-badges-container">
                                    <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas
                                        agregadas.</span>
                                </div>
                                <div class="readings-summary-strip" id="edit_readings_summary_strip">
                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <span>Mín: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Máx: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Promedio: <strong id="edit_calc_lux_display" style="color: #94a3b8;">0.0
                                                LUX</strong></span>
                                    </div>
                                    <div>
                                        <span
                                            style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Sin
                                            datos</span>
                                    </div>
                                </div>
                                <input type="hidden" name="readings" id="edit_readings_json" value="[]">
                                <input type="hidden" name="measured_lux" id="edit_measured_lux" value="0">
                            </div>
                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico TIPO SLIDE / CARRUSEL (Más grande) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Archivo Fotográfico (Slide)</span>
                                <span style="font-size: 11px; color: #64748b;" id="edit_photo_count_indicator">0
                                    fotos</span>
                            </div>

                            <div class="modal-photo-slider-wrapper">
                                <div class="modal-slider-viewport" id="edit_slider_viewport">
                                    <span id="edit_slider_counter" class="slider-counter-badge" style="display: none;">1 /
                                        1</span>

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
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin
                                            Fotografía</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">No se registraron imágenes para
                                            este punto</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*"
                                    style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                                <input type="hidden" name="remaining_images" id="edit_remaining_images">
                                <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                                    <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos"
                                        onclick="document.getElementById('edit_images_input').click()"
                                        style="display: none;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3">
                                            <path d="M5 12h14" />
                                            <path d="M12 5v14" />
                                        </svg>
                                        <span>+ Subir / Agregar Nuevas Fotos</span>
                                    </button>
                                    <button type="button" class="btn-delete-active-photo" id="edit_btn_delete_photo"
                                        onclick="deleteActivePhoto('edit')" style="display: none;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3">
                                            <path d="M3 6h18" />
                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                            <line x1="10" y1="11" x2="10" y2="17" />
                                            <line x1="14" y1="11" x2="14" y2="17" />
                                        </svg>
                                        <span>Eliminar Fotografía Actual</span>
                                    </button>
                                </div>
                            </div>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar todas las
                                fotos del punto en modo carrusel continuo.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Dónde Está Ubicado (GPS + Mini Mapa + Observaciones) -->
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
                                    <input type="number" step="any" id="edit_utm_easting" class="custom-form-input"
                                        placeholder="218468.016" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" id="edit_utm_northing" class="custom-form-input"
                                        placeholder="7627234.367" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                    <input type="text" id="edit_utm_zone" class="custom-form-input" placeholder="20K"
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

                            <!-- Observaciones: Textarea directamente debajo del mapa -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3"
                                    disabled
                                    placeholder="Observaciones técnicas, fuentes de deslumbramiento, estado de luminarias..."></textarea>
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
