<!-- ==========================================================================
     MODAL 2: DETALLE / EDICIÓN DE PUNTO DE VIBRACIÓN OCUPACIONAL (3 COLUMNAS)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="editMeasModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12h3l2 4 4-8 4 8 3-5 2 1h4" />
                </svg>
                <h2 id="editMeasModalTitle">Detalle del Punto de Vibración</h2>
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

                    <!-- COLUMNA 1: Datos Generales, Tiempos & Aceleraciones -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>1. Parámetros & Mediciones</span>
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">Código: <strong
                                    id="edit_pt_num_disp">VIB-1</strong></span>
                        </div>

                        <input type="hidden" name="point_number" id="edit_point_number">
                        <input type="hidden" name="codigo" id="edit_codigo">

                        <!-- Fecha y Hora -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_measurement_date">Fecha <span class="req">*</span></label>
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
                                <span>Técnico / Responsable de Monitoreo <span class="req">*</span></span>
                            </label>
                            <select name="staff_id" id="edit_staff_id" class="custom-form-select" disabled
                                onchange="if(document.getElementById('edit_modal_registered_by')) document.getElementById('edit_modal_registered_by').textContent = this.options[this.selectedIndex].text.split('—')[0].trim();">
                                @if(isset($staffList) && count($staffList) > 0)
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} @if(!empty($staff->role)) ({{ $staff->role }}) @endif</option>
                                    @endforeach
                                @else
                                    <option value="">{{ $registeredByHeader }}</option>
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
                                <label class="form-field-label" for="edit_puesto_trabajo">Puesto de Trabajo <span class="req">*</span></label>
                                <input type="text" name="puesto_trabajo" id="edit_puesto_trabajo" class="custom-form-input" required disabled>
                            </div>
                        </div>

                        <!-- Trabajador y Máquina / Equipo -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_trabajador_evaluado">Trabajador Evaluado <span class="req">*</span></label>
                                <input type="text" name="trabajador_evaluado" id="edit_trabajador_evaluado" class="custom-form-input" required disabled>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_maquina_equipo">Máquina / Herramienta <span class="req">*</span></label>
                                <input type="text" name="maquina_equipo" id="edit_maquina_equipo" class="custom-form-input" required disabled>
                            </div>
                        </div>

                        <!-- Duración de Jornada y Duración de Prueba -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_duracion_jornada_h">Duración Jornada (h) <span class="req">*</span></label>
                                <input type="number" step="any" name="duracion_jornada_h" id="edit_duracion_jornada_h" class="custom-form-input" required disabled onchange="recalculateVibracionModal('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_duracion_prueba_min">Duración Prueba <span class="req">*</span></label>
                                <select name="duracion_prueba_min" id="edit_duracion_prueba_min" class="custom-form-select" required disabled>
                                    <option value="15">15 min</option>
                                    <option value="30">30 min</option>
                                    <option value="45">45 min</option>
                                    <option value="60">60 min</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tipo de Monitoreo & Tiempo de Exposición -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_tipo">Tipo de Monitoreo <span class="req">*</span></label>
                                <select name="tipo" id="edit_tipo" class="custom-form-select" required disabled onchange="handleVibTipoChange('edit')">
                                    <option value="cuerpo_entero">Cuerpo Entero (ISO 2631-1)</option>
                                    <option value="mano_brazo">Mano - Brazo (ISO 5349-1)</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_tiempo_expos_h">Tiempo de Exposición (h) <span class="req">*</span></label>
                                <select name="tiempo_expos_h" id="edit_tiempo_expos_h" class="custom-form-select" required disabled onchange="recalculateVibracionModal('edit')">
                                    <option value="0.17">0,17 h (~10 min)</option>
                                    <option value="0.5">0,5 h (~30 min)</option>
                                    <option value="1.0">1 h</option>
                                    <option value="2.0">2 h</option>
                                    <option value="4.0">4 h</option>
                                    <option value="6.0">6 h</option>
                                    <option value="8.0">8 h</option>
                                </select>
                            </div>
                        </div>

                        <!-- Configuración de Sensor / Ubicación -->
                        <div id="edit_sensor_config_ce" class="form-field-group">
                            <label class="form-field-label" for="edit_ub_acelerometro">Ubicación del Acelerómetro</label>
                            <select name="ub_acelerometro" id="edit_ub_acelerometro" class="custom-form-select" disabled>
                                <option value="base_asiento">Base del asiento (Operador sentado)</option>
                                <option value="espaldar_asiento">Espaldar del asiento</option>
                                <option value="base_pies">Base de pies (Operador de pie)</option>
                            </select>
                        </div>

                        <div id="edit_sensor_config_mb" class="form-field-group" style="display: none;">
                            <label class="form-field-label" for="edit_mano_afectada">Mano Evaluada / Afectada</label>
                            <select name="mano_afectada" id="edit_mano_afectada" class="custom-form-select" disabled>
                                <option value="derecha">Mano Derecha</option>
                                <option value="izquierda">Mano Izquierda</option>
                                <option value="ambas">Ambas Manos</option>
                            </select>
                        </div>

                        <!-- Aceleraciones Ejes X, Y, Z -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_aeqx">Aeq X <span class="req">*</span></label>
                                <input type="number" step="any" name="aeqx" id="edit_aeqx" class="custom-form-input tech-val-mono" required disabled onkeyup="recalculateVibracionModal('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_aeqy">Aeq Y <span class="req">*</span></label>
                                <input type="number" step="any" name="aeqy" id="edit_aeqy" class="custom-form-input tech-val-mono" required disabled onkeyup="recalculateVibracionModal('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_aeqz">Aeq Z <span class="req">*</span></label>
                                <input type="number" step="any" name="aeqz" id="edit_aeqz" class="custom-form-input tech-val-mono" required disabled onkeyup="recalculateVibracionModal('edit')">
                            </div>
                        </div>

                        <!-- Strip de Resumen de Cálculos ISO -->
                        <div class="readings-summary-strip" id="edit_calc_summary_strip" style="margin-top: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 11.5px;">
                                <span>A(8): <strong id="edit_calc_a8" style="color: #0284c7;">0.0000 m/s²</strong></span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>Atot: <strong id="edit_calc_atotal" style="color: #475569;">0.0000</strong></span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>VLE: <strong id="edit_calc_vle" style="color: #64748b;">1.15 m/s²</strong></span>
                            </div>
                            <div>
                                <span class="lux-measured-badge compliant" id="edit_calc_badge" style="font-size: 10.5px; padding: 2px 7px;">CUMPLE</span>
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

                            <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*"
                                style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                            <input type="hidden" name="keep_images" id="edit_remaining_images">
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
                        <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Visualiza las fotografías del punto de monitoreo en modo carrusel continuo.</span>
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
                                <input type="number" step="any" id="edit_utm_easting" class="custom-form-input"
                                    placeholder="592450.000" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                <input type="number" step="any" id="edit_utm_northing" class="custom-form-input"
                                    placeholder="8175320.000" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group" style="max-width: 85px;">
                                <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                <input type="text" id="edit_utm_zone" class="custom-form-input" placeholder="19K"
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
                                placeholder="Observaciones técnicas, estado de amortiguadores, terreno, velocidad..."></textarea>
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
