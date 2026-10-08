<!-- ==========================================================================
     MODAL 1: NUEVO PUNTO DE MONITOREO DE VIBRACIÓN OCUPACIONAL (3 COLUMNAS)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="createMeasModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 12h3l2 4 4-8 4 8 3-5 2 1h4" />
                </svg>
                <h2 id="createMeasModalTitle">Nuevo Punto de Monitoreo de Vibración</h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()"
                aria-label="Cerrar">✕</button>
        </div>
        <form action="{{ route('modules.vibracion.measurements.store', $module->id) }}" method="POST"
            enctype="multipart/form-data" id="createMeasurementForm">
            @csrf
            <div class="modal-body-custom">
                <div class="modal-three-cols-grid">

                    <!-- COLUMNA 1: Datos Generales, Tiempos & Aceleraciones -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>1. Parámetros & Mediciones</span>
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">Código: <strong
                                    id="create_pt_num_disp">VIB-{{ $totalMeasurements + 1 }}</strong></span>
                        </div>

                        <input type="hidden" name="point_number" id="create_point_number"
                            value="VIB-{{ $totalMeasurements + 1 }}">
                        <input type="hidden" name="codigo" id="create_codigo"
                            value="VIB-{{ $totalMeasurements + 1 }}">

                        <!-- Fecha y Hora -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_measurement_date">Fecha <span class="req">*</span></label>
                                <input type="date" name="measurement_date" id="create_measurement_date"
                                    class="custom-form-input" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_measurement_time">Hora <span class="req">*</span></label>
                                <input type="time" name="measurement_time" id="create_measurement_time"
                                    class="custom-form-input" required value="{{ date('H:i') }}">
                            </div>
                        </div>

                        <!-- Técnico / Responsable -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_staff_id">
                                <span>Técnico / Responsable de Monitoreo <span class="req">*</span></span>
                            </label>
                            <select name="staff_id" id="create_staff_id" class="custom-form-select" required>
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
                                <label class="form-field-label" for="create_area">Área de Trabajo <span class="req">*</span></label>
                                <input type="text" name="area" id="create_area" class="custom-form-input" required
                                    placeholder="Ej: PLANTA INDUSTRIAL - ÁREA DE MOLIENDA">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_puesto_trabajo">Puesto de Trabajo <span class="req">*</span></label>
                                <input type="text" name="puesto_trabajo" id="create_puesto_trabajo" class="custom-form-input"
                                    required placeholder="Ej: OPERADOR DE EXCAVADORA 320D">
                            </div>
                        </div>

                        <!-- Trabajador y Máquina / Equipo -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_trabajador_evaluado">Trabajador Evaluado <span class="req">*</span></label>
                                <input type="text" name="trabajador_evaluado" id="create_trabajador_evaluado"
                                    class="custom-form-input" required placeholder="Ej: JUAN PÉREZ FLORES">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_maquina_equipo">Máquina / Herramienta <span class="req">*</span></label>
                                <input type="text" name="maquina_equipo" id="create_maquina_equipo"
                                    class="custom-form-input" required placeholder="Ej: EXCAVADORA CAT 320D">
                            </div>
                        </div>

                        <!-- Duración de Jornada y Duración de Prueba -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_duracion_jornada_h">Duración Jornada (h) <span class="req">*</span></label>
                                <input type="number" step="any" name="duracion_jornada_h" id="create_duracion_jornada_h"
                                    class="custom-form-input" value="8.0" required onchange="recalculateVibracionModal('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_duracion_prueba_min">Duración Prueba <span class="req">*</span></label>
                                <select name="duracion_prueba_min" id="create_duracion_prueba_min" class="custom-form-select" required>
                                    <option value="15" selected>15 min</option>
                                    <option value="30">30 min</option>
                                    <option value="45">45 min</option>
                                    <option value="60">60 min</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tipo de Monitoreo & Tiempo de Exposición -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_tipo">Tipo de Monitoreo <span class="req">*</span></label>
                                <select name="tipo" id="create_tipo" class="custom-form-select" required onchange="handleVibTipoChange('create')">
                                    <option value="cuerpo_entero" selected>Cuerpo Entero (ISO 2631-1)</option>
                                    <option value="mano_brazo">Mano - Brazo (ISO 5349-1)</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_tiempo_expos_h">Tiempo de Exposición (h) <span class="req">*</span></label>
                                <select name="tiempo_expos_h" id="create_tiempo_expos_h" class="custom-form-select" required onchange="recalculateVibracionModal('create')">
                                    <option value="0.17">0,17 h (~10 min)</option>
                                    <option value="0.5">0,5 h (~30 min)</option>
                                    <option value="1.0">1 h</option>
                                    <option value="2.0">2 h</option>
                                    <option value="4.0">4 h</option>
                                    <option value="6.0">6 h</option>
                                    <option value="8.0" selected>8 h</option>
                                </select>
                            </div>
                        </div>

                        <!-- Configuración de Sensor / Ubicación -->
                        <div id="create_sensor_config_ce" class="form-field-group">
                            <label class="form-field-label" for="create_ub_acelerometro">Ubicación del Acelerómetro</label>
                            <select name="ub_acelerometro" id="create_ub_acelerometro" class="custom-form-select">
                                <option value="base_asiento" selected>Base del asiento (Operador sentado)</option>
                                <option value="espaldar_asiento">Espaldar del asiento</option>
                                <option value="base_pies">Base de pies (Operador de pie)</option>
                            </select>
                        </div>

                        <div id="create_sensor_config_mb" class="form-field-group" style="display: none;">
                            <label class="form-field-label" for="create_mano_afectada">Mano Evaluada / Afectada</label>
                            <select name="mano_afectada" id="create_mano_afectada" class="custom-form-select">
                                <option value="derecha" selected>Mano Derecha</option>
                                <option value="izquierda">Mano Izquierda</option>
                                <option value="ambas">Ambas Manos</option>
                            </select>
                        </div>

                        <!-- Aceleraciones Ejes X, Y, Z -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_aeqx">Aeq X <span class="req">*</span></label>
                                <input type="number" step="any" name="aeqx" id="create_aeqx" class="custom-form-input tech-val-mono"
                                    value="0.0000" placeholder="0.0000" required onkeyup="recalculateVibracionModal('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_aeqy">Aeq Y <span class="req">*</span></label>
                                <input type="number" step="any" name="aeqy" id="create_aeqy" class="custom-form-input tech-val-mono"
                                    value="0.0000" placeholder="0.0000" required onkeyup="recalculateVibracionModal('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_aeqz">Aeq Z <span class="req">*</span></label>
                                <input type="number" step="any" name="aeqz" id="create_aeqz" class="custom-form-input tech-val-mono"
                                    value="0.0000" placeholder="0.0000" required onkeyup="recalculateVibracionModal('create')">
                            </div>
                        </div>

                        <!-- Strip de Resumen de Cálculos ISO -->
                        <div class="readings-summary-strip" id="create_calc_summary_strip" style="margin-top: 6px;">
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 11.5px;">
                                <span>A(8): <strong id="create_calc_a8" style="color: #0284c7;">0.0000 m/s²</strong></span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>Atot: <strong id="create_calc_atotal" style="color: #475569;">0.0000</strong></span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>VLE: <strong id="create_calc_vle" style="color: #64748b;">1.15 m/s²</strong></span>
                            </div>
                            <div>
                                <span class="lux-measured-badge compliant" id="create_calc_badge" style="font-size: 10.5px; padding: 2px 7px;">CUMPLE</span>
                            </div>
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
                        <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite subir varias fotos del punto y navegar con las flechas continuas.</span>
                    </div>

                    <!-- COLUMNA 3: Ubicación Geográfica (GPS + Mini Mapa + Observaciones) -->
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
                                    placeholder="592450.000" oninput="syncUtmToMap('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_utm_northing">Norte (N)</label>
                                <input type="number" step="any" id="create_utm_northing" class="custom-form-input"
                                    placeholder="8175320.000" oninput="syncUtmToMap('create')">
                            </div>
                            <div class="form-field-group" style="max-width: 85px;">
                                <label class="form-field-label" for="create_utm_zone">Zona (Z)</label>
                                <input type="text" id="create_utm_zone" class="custom-form-input" placeholder="19K"
                                    value="19K" oninput="syncUtmToMap('create')">
                            </div>
                        </div>
                        <div class="utm-preview-pill">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.2">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                <path d="M2 12h20" />
                            </svg>
                            <span id="create_utm_display">E: —, N: —, Z: 19K</span>
                        </div>
                        <input type="hidden" name="location" id="create_location">
                        <input type="hidden" name="latitude" id="create_latitude">
                        <input type="hidden" name="longitude" id="create_longitude">

                        <!-- Mini Mapa interactivo -->
                        <div>
                            <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa (Haz clic para posicionar)</label>
                            <div id="create_modal_map" class="modal-minimap-container"></div>
                        </div>

                        <!-- Observaciones -->
                        <div class="form-field-group" style="margin-top: 6px;">
                            <label class="form-field-label" for="create_observations">Observaciones</label>
                            <textarea name="observations" id="create_observations" class="custom-form-textarea" rows="3"
                                placeholder="Observaciones técnicas, estado de amortiguadores, terreno, velocidad..."></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action" id="create_modal_submit_btn">Guardar Punto de Vibración</button>
            </div>
        </form>
    </div>
</div>
