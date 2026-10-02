    <!-- ==========================================================================
         MODAL: DETALLE / EDICIÓN DE MEDICIÓN DE DOSIMETRÍA (MODO CONSULTA CON BOTÓN EDITAR)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="editMeasModalTitle">
        <div class="modal-dialog-ventilation modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <h2 id="editMeasModalTitle">Detalle del Punto de Dosimetría</h2>
                    <span id="modalModeStatusBadge" class="modal-badge-view"
                        style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 9999px; font-size: 11px; font-weight: 700; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;">
                        Solo Lectura
                    </span>
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
                        onclick="toggleModalEditMode()"
                        style="display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer; border: 1px solid #0284c7; background: #f0f9ff; color: #0284c7; transition: all 0.2s ease;">
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
            <form action="" method="POST" enctype="multipart/form-data" id="editMeasurementForm" class="modal-view-mode">
                @csrf
                @method('PUT')
                <input type="hidden" name="measurement_id" id="edit_measurement_id">
                
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos del Puesto y Parámetros Acústicos -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Puesto & Parámetros</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="edit_pt_num_disp">01</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="edit_point_number">

                            <!-- Fecha y Hora -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_date">Fecha de Medición <span class="req">*</span></label>
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
                                <select name="staff_id" id="edit_staff_id" class="custom-form-select" required disabled
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

                            <!-- Área de Trabajo -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_area">Área de Trabajo <span class="req">*</span></label>
                                <input type="text" name="area" id="edit_area" class="custom-form-input" required disabled
                                    placeholder="Ej: Planta de Producción, Maestranza...">
                            </div>

                            <!-- Punto de Medición -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_punto_medicion">Punto de Medición / Puesto <span class="req">*</span></label>
                                <input type="text" name="punto_medicion" id="edit_punto_medicion" class="custom-form-input" required disabled
                                    placeholder="Ej: Operador Torno CNC, Soldador...">
                            </div>

                            <!-- Tipo de Ruido & TPE (Hr) -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_tipo_ruido">Tipo de Ruido <span class="req">*</span></label>
                                    <select name="tipo_ruido" id="edit_tipo_ruido" class="custom-form-select" required disabled>
                                        <option value="Estable">Estable</option>
                                        <option value="Fluctuante">Fluctuante</option>
                                        <option value="Estable escalonado">Estable escalonado</option>
                                        <option value="Impacto">Impacto</option>
                                    </select>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_tiempo_expos_h">TPE (Horas) <span class="req">*</span></label>
                                    <input type="number" step="0.1" min="0.1" max="24" name="tiempo_expos_h" id="edit_tiempo_expos_h"
                                        class="custom-form-input" required value="8.0" disabled oninput="recalcDosimetry('edit')">
                                </div>
                            </div>

                            <!-- Ponderación & Respuesta -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_ponderacion">Ponderación</label>
                                    <select name="ponderacion" id="edit_ponderacion" class="custom-form-select" disabled>
                                        <option value="A">A (dBA)</option>
                                        <option value="C">C (dBC)</option>
                                    </select>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_respuesta">Respuesta</label>
                                    <select name="respuesta" id="edit_respuesta" class="custom-form-select" disabled>
                                        <option value="Lento">Lento (Slow)</option>
                                        <option value="Rápido">Rápido (Fast)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- TARJETA INTERNA: Mediciones y Niveles Sonoros -->
                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 12px; margin-top: 8px;">
                                <span style="font-size: 11.5px; font-weight: 800; color: #334155; display: block; margin-bottom: 8px;">
                                    Niveles de Presión Sonora (dB)
                                </span>
                                
                                <div class="form-grid-two-cols">
                                    <div class="form-field-group" style="margin-bottom: 8px;">
                                        <label class="form-field-label" for="edit_duracion_medicion_h">Tiempo Medición (h)</label>
                                        <input type="number" step="0.01" name="duracion_medicion_h" id="edit_duracion_medicion_h"
                                            class="custom-form-input" placeholder="Ej: 1.50" disabled oninput="recalcDosimetry('edit')">
                                    </div>
                                    <div class="form-field-group" style="margin-bottom: 8px;">
                                        <label class="form-field-label" for="edit_leq_t_db">Leq,T (dB) <span class="req">*</span></label>
                                        <input type="number" step="0.1" name="leq_t_db" id="edit_leq_t_db"
                                            class="custom-form-input" required placeholder="Ej: 83.4" disabled oninput="recalcDosimetry('edit')">
                                    </div>
                                </div>

                                <div class="form-grid-two-cols">
                                    <div class="form-field-group" style="margin-bottom: 0;">
                                        <label class="form-field-label" for="edit_nps_max_db">NPS MAX (dB)</label>
                                        <input type="number" step="0.1" name="nps_max_db" id="edit_nps_max_db"
                                            class="custom-form-input" placeholder="Ej: 92.1" disabled>
                                    </div>
                                    <div class="form-field-group" style="margin-bottom: 0;">
                                        <label class="form-field-label" for="edit_nps_min_db">NPS MIN (dB)</label>
                                        <input type="number" step="0.1" name="nps_min_db" id="edit_nps_min_db"
                                            class="custom-form-input" placeholder="Ej: 68.5" disabled>
                                    </div>
                                </div>
                            </div>

                            <!-- Indicador de Cumplimiento Normativo -->
                            <div style="margin-top: 10px; display: flex; align-items: center; justify-content: space-between; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 6px 12px;">
                                <div style="font-size: 11.5px; color: #475569; font-weight: 600;">
                                    LMP Calculado: <strong id="edit_lmp_display" style="color: #0284c7;">85.0 dBA</strong>
                                </div>
                                <div id="edit_cumple_badge" class="badge-compliance-ok">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <span id="edit_cumple_text">CUMPLE</span>
                                </div>
                            </div>

                            <!-- Personal Registrador -->
                            <div class="form-field-group" style="margin-top: 10px; margin-bottom: 0;">
                                <label class="form-field-label">Personal Registrador</label>
                                <div class="custom-form-input"
                                    style="background: #f8fafc; display: flex; align-items: center; gap: 7px; color: #334155; font-weight: 600; cursor: default;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    <span id="edit_registered_by_disp">{{ $registeredByHeader }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA 2: Evidencia Fotográfica (Slide / Carrusel) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Evidencia Fotográfica (Slide)</span>
                                <span style="font-size: 11px; color: #64748b;" id="edit_photo_count_indicator">0 fotos</span>
                            </div>

                            <div class="modal-photo-slider-wrapper">
                                <div class="modal-slider-viewport" id="edit_slider_viewport" style="position: relative;">
                                    <span id="edit_slider_counter" class="slider-counter-badge" style="display: none;">1 / 1</span>

                                    <!-- Botón para eliminar foto activa en modo edición -->
                                    <button type="button" class="btn-delete-active-photo" id="edit_slider_del_btn"
                                        onclick="deleteActiveSlidePhoto('edit')" style="display: none;"
                                        title="Eliminar esta fotografía">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                    </button>

                                    <button type="button" class="slider-nav-btn prev" id="edit_slider_btn_prev"
                                        onclick="slidePhotoNav('edit', -1)" style="display: none;"
                                        aria-label="Anterior">❮</button>
                                    <button type="button" class="slider-nav-btn next" id="edit_slider_btn_next"
                                        onclick="slidePhotoNav('edit', 1)" style="display: none;"
                                        aria-label="Siguiente">❯</button>

                                    <img id="edit_slider_img" class="modal-slider-main-img" src="" alt="Foto punto"
                                        style="display: none;">

                                    <div id="edit_slider_placeholder" class="modal-slider-placeholder"
                                        onclick="if(isEditUnlocked) document.getElementById('edit_images_input').click()"
                                        style="cursor: pointer;">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Evidencia Fotográfica</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Sin fotografías registradas</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <!-- Input oculto para conservar fotos existentes seleccionadas -->
                                <input type="hidden" name="remaining_images" id="edit_remaining_images">

                                <input type="file" name="photos[]" id="edit_images_input" multiple accept="image/*"
                                    style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
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
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar las fotos del dosímetro en el puesto de trabajo.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Mapa + Observaciones -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button" id="edit_btn_gps"
                                    onclick="getCurrentGpsPosition('edit_latitude', 'edit_longitude', 'edit')"
                                    style="background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; display: none; align-items: center; gap: 3px;">
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
                                        placeholder="218468.016" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" name="utm_northing" id="edit_utm_northing" class="custom-form-input"
                                        placeholder="7627234.367" disabled oninput="syncUtmToMap('edit')">
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

                            <!-- Mini Mapa interactivo -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="edit_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3" disabled
                                    placeholder="Fuentes generadoras de ruido, EPP auditivo utilizado por el trabajador, ciclos de trabajo..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom" style="display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" class="btn-secondary-subtle" onclick="closeEditMeasurementModal()">Cerrar</button>
                        <!-- Botón de Editar en el pie de página -->
                        <button type="button" class="btn-secondary-subtle" id="footerBtnToggleEdit" onclick="toggleModalEditMode()"
                            style="display: inline-flex; align-items: center; gap: 5px; color: #0284c7; border-color: #bae6fd; font-weight: 700;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                <path d="m15 5 4 4" />
                            </svg>
                            <span id="footerBtnToggleEditText">Editar Punto</span>
                        </button>
                    </div>
                    <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn" style="display: none;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
