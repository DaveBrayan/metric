<!-- ==========================================================================
     MODAL: DETALLE / EDICIÓN DE PUNTO DE RUIDO OCUPACIONAL (3 COLUMNAS - METRIC STANDARD)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="editMeasModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
                <h2 id="editMeasModalTitle">Detalle del Punto de Ruido Ocupacional</h2>
                <span id="modalModeStatusBadge" class="modal-badge-view">Solo Lectura</span>
                <!-- Personal Registrado al lado de Solo Lectura -->
                <div style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9999px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; color: #334155;"
                    title="Personal Registrador">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <span id="edit_modal_registered_by">{{ $registeredByHeader ?? 'Técnico' }}</span>
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

                    <!-- COLUMNA 1: Parámetros Laborales & Mediciones LAeq -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>1. Parámetros & Mediciones LAeq</span>
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">Código: <strong
                                    id="edit_pt_num_disp">P-01</strong></span>
                        </div>

                        <input type="hidden" name="point_number" id="edit_point_number">
                        <input type="hidden" name="mediciones_db" id="edit_mediciones_db_json" value="[]">

                        <!-- Fecha y Hora -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_measurement_date">Fecha de Medición <span class="req">*</span></label>
                                <input type="date" name="measurement_date" id="edit_measurement_date" class="custom-form-input" required disabled>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_measurement_time">Hora <span class="req">*</span></label>
                                <input type="time" name="measurement_time" id="edit_measurement_time" class="custom-form-input" required disabled>
                            </div>
                        </div>

                        <!-- Técnico / Responsable -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_staff_id">
                                <span>Técnico de Campo / Responsable <span class="req">*</span></span>
                            </label>
                            <select name="staff_id" id="edit_staff_id" class="custom-form-select" disabled
                                onchange="if(document.getElementById('edit_modal_registered_by') && this.selectedIndex >= 0) document.getElementById('edit_modal_registered_by').textContent = this.options[this.selectedIndex].text.split('—')[0].trim();">
                                @if(isset($staffList) && count($staffList) > 0)
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} @if(!empty($staff->position ?? $staff->role)) — {{ $staff->position ?? $staff->role }} @endif</option>
                                    @endforeach
                                @else
                                    <option value="">{{ $registeredByHeader ?? 'Técnico' }}</option>
                                @endif
                            </select>
                        </div>

                        <!-- Área y Puesto de Trabajo -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_area">Área de Trabajo <span class="req">*</span></label>
                                <input type="text" name="area" id="edit_area" class="custom-form-input" required disabled>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_punto_medicion">Punto de Medición <span class="req">*</span></label>
                                <input type="text" name="punto_medicion" id="edit_punto_medicion" class="custom-form-input" required disabled>
                            </div>
                        </div>

                        <!-- Actividad / Maquinaria y Tipo de Ruido -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_actividad">Actividad / Maquinaria <span class="req">*</span></label>
                                <input type="text" name="actividad" id="edit_actividad" class="custom-form-input" required disabled>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_tipo_ruido">Tipo de Ruido <span class="req">*</span></label>
                                <select name="tipo_ruido" id="edit_tipo_ruido" class="custom-form-select" required disabled>
                                    <option value="Continuo">Continuo</option>
                                    <option value="Intermitente">Intermitente</option>
                                    <option value="Impulsivo">Impulsivo</option>
                                    <option value="Mixto">Mixto</option>
                                </select>
                            </div>
                        </div>

                        <!-- Parámetros del Sonómetro: TPE, Ponderación, Respuesta -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_tiempo_expos_h">T. Exp. (h) <span class="req">*</span></label>
                                <input type="number" step="0.1" min="0.1" max="24" name="tiempo_expos_h" id="edit_tiempo_expos_h" class="custom-form-input" required disabled onchange="recalculateRuidoModal('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_ponderacion">Pond. <span class="req">*</span></label>
                                <select name="ponderacion" id="edit_ponderacion" class="custom-form-select" required disabled>
                                    <option value="A">A (dBA)</option>
                                    <option value="C">C (dBC)</option>
                                    <option value="Z">Z (Lineal)</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_respuesta">Resp. <span class="req">*</span></label>
                                <select name="respuesta" id="edit_respuesta" class="custom-form-select" required disabled>
                                    <option value="Lenta">Lenta (Slow)</option>
                                    <option value="Rápida">Rápida (Fast)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Protección Auditiva (Uso, Tipo, NRR) -->
                        <div style="display: grid; grid-template-columns: 1fr 1.3fr 1fr; gap: 10px;">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_uso_protectores">Uso Prot.</label>
                                <select name="uso_protectores" id="edit_uso_protectores" class="custom-form-select" disabled>
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_tipo_protector">Tipo Protector</label>
                                <input type="text" name="tipo_protector" id="edit_tipo_protector" class="custom-form-input" placeholder="ej. Tapones silicona" disabled>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_nrr_db">NRR (dB)</label>
                                <input type="number" step="0.1" name="nrr_db" id="edit_nrr_db" class="custom-form-input" placeholder="ej. 25" disabled>
                            </div>
                        </div>

                        <!-- Bloque de Lecturas del Sonómetro (dBA) -->
                        <div class="form-field-group" style="margin-bottom: 0;">
                            <div class="form-field-label">
                                <span>Lecturas del Sonómetro (dBA)</span>
                                <span style="font-size: 11px; font-weight: 700; color: #0284c7;"
                                    id="edit_readings_count_badge">0 lecturas</span>
                            </div>
                            <!-- Barra rápida para agregar lecturas -->
                            <div class="readings-input-bar" id="edit_readings_input_bar" style="display: none;">
                                <input type="number" step="0.1" id="edit_quick_db_input" class="custom-form-input"
                                    placeholder="Ej: 83.5 (Presiona Enter)"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault(); addNoiseReadingPoint('edit');}">
                                <button type="button" class="btn-add-reading" onclick="addNoiseReadingPoint('edit')">+ Agregar</button>
                            </div>
                            <!-- Badges de lecturas registradas -->
                            <div id="edit_readings_badges_container" class="readings-badges-container">
                                <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas registradas.</span>
                            </div>
                            <!-- Tira de Resumen de Cálculos NTS-002/17 -->
                            <div class="readings-summary-strip" id="edit_calc_summary_strip" style="margin-top: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 11.5px;">
                                    <span>LAeq: <strong id="edit_calc_laeq" style="color: #0284c7;">—</strong></span>
                                    <span style="color: #cbd5e1;">|</span>
                                    <span>TMPE: <strong id="edit_calc_tmpe" style="color: #475569;">—</strong></span>
                                    <span style="color: #cbd5e1;">|</span>
                                    <span>Dosis: <strong id="edit_calc_dosis" style="color: #64748b;">—</strong></span>
                                </div>
                                <div>
                                    <span class="lux-measured-badge compliant" id="edit_calc_badge" style="font-size: 10.5px; padding: 2px 7px;">CUMPLE</span>
                                </div>
                            </div>
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
                                    <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografía</strong>
                                    <span style="font-size: 11.5px; color: #94a3b8;">No se registraron imágenes para este punto</span>
                                </div>
                            </div>

                            <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                            <input type="file" name="photos[]" id="edit_images_input" multiple accept="image/*"
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
                        <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar todas las fotos del punto en modo carrusel continuo.</span>
                    </div>

                    <!-- COLUMNA 3: Ubicación Geográfica (GPS + Mini Mapa + Observaciones) -->
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
                                <input type="number" step="any" id="edit_utm_easting" name="utm_easting" class="custom-form-input"
                                    placeholder="592450.000" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                <input type="number" step="any" id="edit_utm_northing" name="utm_northing" class="custom-form-input"
                                    placeholder="8175320.000" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group" style="max-width: 85px;">
                                <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                <input type="text" id="edit_utm_zone" name="utm_zone" class="custom-form-input" placeholder="19K"
                                    value="19K" disabled oninput="syncUtmToMap('edit')">
                            </div>
                        </div>
                        <div class="utm-preview-pill">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                <path d="M2 12h20" />
                            </svg>
                            <span id="edit_utm_display">E: —, N: —, Z: 19K</span>
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
                            <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3"
                                disabled
                                placeholder="Observaciones técnicas, fuentes de ruido predominantes, ciclos de trabajo..."></textarea>
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
