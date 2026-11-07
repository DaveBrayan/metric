<!-- ==========================================================================
     MODAL: NUEVO PUNTO DE RUIDO OCUPACIONAL (3 COLUMNAS - METRIC STANDARD)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="createModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M2 12h20M7.07 7.07a6 6 0 0 1 9.86 0M5.64 5.64a9 9 0 0 1 12.72 0" />
                </svg>
                <h2 id="createModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 19px; font-weight: 800; color: var(--ink); margin: 0;">
                    Nuevo Punto de Medición — Ruido Ocupacional
                </h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()" aria-label="Cerrar">✕</button>
        </div>

        <form action="{{ route('modules.ruido_ocupacional.measurements.store', $module->id) }}" method="POST"
            enctype="multipart/form-data" id="createMeasurementForm">
            @csrf
            <div class="modal-body-custom">
                <div class="modal-three-cols-grid">

                    <!-- COLUMNA 1: Parámetros Laborales & Mediciones LAeq -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>1. Parámetros & Mediciones LAeq</span>
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                    id="create_pt_num_disp">P-{{ str_pad(($totalMeasurements ?? 0) + 1, 2, '0', STR_PAD_LEFT) }}</strong></span>
                        </div>

                        <input type="hidden" name="point_number" id="create_point_number"
                            value="P-{{ str_pad(($totalMeasurements ?? 0) + 1, 2, '0', STR_PAD_LEFT) }}">
                        <input type="hidden" name="mediciones_db" id="create_mediciones_db_json" value="[]">

                        <!-- Fecha y Hora -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_measurement_date">Fecha de Medición <span class="req">*</span></label>
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
                                <span>Técnico de Campo / Responsable <span class="req">*</span></span>
                            </label>
                            <select name="staff_id" id="create_staff_id" class="custom-form-select">
                                @if(isset($staffList) && count($staffList) > 0)
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} @if(!empty($staff->position ?? $staff->role)) — {{ $staff->position ?? $staff->role }} @endif</option>
                                    @endforeach
                                @else
                                    <option value="">{{ $registeredByHeader ?? 'Técnico de Campo' }}</option>
                                @endif
                            </select>
                        </div>

                        <!-- Área y Punto de Medición -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_area">Área de Trabajo <span class="req">*</span></label>
                                <input type="text" name="area" id="create_area" class="custom-form-input" required
                                    placeholder="ej. Sala de Compresores">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_punto_medicion">Punto de Medición <span class="req">*</span></label>
                                <input type="text" name="punto_medicion" id="create_punto_medicion" class="custom-form-input" required
                                    placeholder="ej. Operador Central">
                            </div>
                        </div>

                        <!-- Oculto: Actividad y Maquinaria -->
                        <input type="hidden" name="actividad" id="create_actividad" value="General">
                        <input type="hidden" name="maquinaria_equipo" id="create_maquinaria_equipo" value="">

                        <!-- Tipo de Ruido (Opciones App & Normativa) -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_tipo_ruido">Tipo de Ruido <span class="req">*</span></label>
                            <select name="tipo_ruido" id="create_tipo_ruido" class="custom-form-select" required>
                                <option value="Estable">Estable (Continuo)</option>
                                <option value="Fluctuante" selected>Fluctuante (Intermitente)</option>
                                <option value="Estable escalonado">Estable escalonado</option>
                                <option value="Impacto">Impacto (Impulsivo)</option>
                                <option value="Continuo">Continuo</option>
                                <option value="Intermitente">Intermitente</option>
                                <option value="Impulsivo">Impulsivo</option>
                                <option value="Mixto">Mixto</option>
                            </select>
                        </div>

                        <!-- Parámetros del Sonómetro: TPE, Ponderación, Respuesta -->
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_tiempo_expos_h">T. Exp. (h) <span class="req">*</span></label>
                                <input type="number" step="0.1" min="0.1" max="24" name="tiempo_expos_h" id="create_tiempo_expos_h"
                                    class="custom-form-input" value="8.0" required onchange="recalculateRuidoModal('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_ponderacion">Pond. <span class="req">*</span></label>
                                <select name="ponderacion" id="create_ponderacion" class="custom-form-select" required>
                                    <option value="A" selected>A (dBA)</option>
                                    <option value="C">C (dBC)</option>
                                    <option value="Z">Z (Lineal)</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_respuesta">Resp. <span class="req">*</span></label>
                                <select name="respuesta" id="create_respuesta" class="custom-form-select" required>
                                    <option value="Lento" selected>Lento / Lenta (Slow - S)</option>
                                    <option value="Rápido">Rápido / Rápida (Fast - F)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Protección Auditiva (Uso, Tipo, NRR) -->
                        <div style="display: grid; grid-template-columns: 1fr 1.3fr 1fr; gap: 10px;">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_uso_protectores">Uso Prot.</label>
                                <select name="uso_protectores" id="create_uso_protectores" class="custom-form-select">
                                    <option value="0">No</option>
                                    <option value="1">Sí</option>
                                </select>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_tipo_protector">Tipo Protector</label>
                                <input type="text" name="tipo_protector" id="create_tipo_protector" class="custom-form-input" placeholder="ej. Tapones silicona">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_nrr_db">NRR (dB)</label>
                                <input type="number" step="0.1" name="nrr_db" id="create_nrr_db" class="custom-form-input" placeholder="ej. 25">
                            </div>
                        </div>

                        <!-- Bloque de Lecturas del Sonómetro (dBA) -->
                        <div class="form-field-group" style="margin-bottom: 0;">
                            <div class="form-field-label">
                                <span>Lecturas del Sonómetro (dBA)</span>
                                <span style="font-size: 11px; font-weight: 700; color: #0284c7;"
                                    id="create_readings_count_badge">0 lecturas</span>
                            </div>
                            <!-- Barra rápida para agregar lecturas -->
                            <div class="readings-input-bar" id="create_readings_input_bar" style="display: flex;">
                                <input type="number" step="0.1" id="create_quick_db_input" class="custom-form-input"
                                    placeholder="Ej: 83.5 (Presiona Enter)"
                                    onkeydown="if(event.key==='Enter'){event.preventDefault(); addNoiseReadingPoint('create');}">
                                <button type="button" class="btn-add-reading" onclick="addNoiseReadingPoint('create')">+ Agregar</button>
                            </div>
                            <!-- Badges de lecturas registradas -->
                            <div id="create_readings_badges_container" class="readings-badges-container">
                                <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas registradas.</span>
                            </div>
                            <!-- Tira de Resumen de Cálculos NTS-002/17 -->
                            <div class="readings-summary-strip" id="create_calc_summary_strip" style="margin-top: 6px;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 11.5px;">
                                    <span>LAeq: <strong id="create_calc_laeq" style="color: #0284c7;">—</strong></span>
                                    <span style="color: #cbd5e1;">|</span>
                                    <span>TMPE: <strong id="create_calc_tmpe" style="color: #475569;">—</strong></span>
                                    <span style="color: #cbd5e1;">|</span>
                                    <span>Dosis: <strong id="create_calc_dosis" style="color: #64748b;">—</strong></span>
                                </div>
                                <div>
                                    <span class="lux-measured-badge compliant" id="create_calc_badge" style="font-size: 10.5px; padding: 2px 7px;">CUMPLE</span>
                                </div>
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

                                <div id="create_slider_placeholder" class="modal-slider-placeholder">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        style="margin-bottom: 10px;">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                    <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografías</strong>
                                    <span style="font-size: 11.5px; color: #94a3b8;">Adjunta fotos para documentar este punto</span>
                                </div>
                            </div>

                            <div class="slider-thumbs-strip" id="create_slider_thumbs" style="display: none;"></div>

                            <input type="file" name="photos[]" id="create_images_input" multiple accept="image/*"
                                style="display: none;" onchange="handleMultipleImagesSelected(this, 'create')">

                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                                <button type="button" class="btn-add-photos-trigger" id="create_btn_add_photos"
                                    onclick="document.getElementById('create_images_input').click()"
                                    style="display: flex;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span>+ Subir / Agregar Fotos</span>
                                </button>
                                <button type="button" class="btn-delete-active-photo" id="create_btn_delete_photo"
                                    onclick="deleteActivePhoto('create')" style="display: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M3 6h18" />
                                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                        <line x1="10" y1="11" x2="10" y2="17" />
                                        <line x1="14" y1="11" x2="14" y2="17" />
                                    </svg>
                                    <span>Eliminar Foto Actual</span>
                                </button>
                            </div>
                        </div>
                        <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite cargar múltiples fotos del punto en modo carrusel continuo.</span>
                    </div>

                    <!-- COLUMNA 3: Ubicación Geográfica (GPS + Mini Mapa + Observaciones) -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>3. Ubicación & GPS</span>
                            <button type="button" id="create_btn_gps"
                                onclick="getCurrentGpsPosition('create_latitude', 'create_longitude', 'create')"
                                style="display: inline-flex; background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; align-items: center; gap: 3px;">
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
                                <input type="number" step="any" id="create_utm_easting" name="utm_easting" class="custom-form-input"
                                    placeholder="592450.000" oninput="syncUtmToMap('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_utm_northing">Norte (N)</label>
                                <input type="number" step="any" id="create_utm_northing" name="utm_northing" class="custom-form-input"
                                    placeholder="8175320.000" oninput="syncUtmToMap('create')">
                            </div>
                            <div class="form-field-group" style="max-width: 85px;">
                                <label class="form-field-label" for="create_utm_zone">Zona (Z)</label>
                                <input type="text" id="create_utm_zone" name="utm_zone" class="custom-form-input" placeholder="19K"
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
                            <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                            <div id="create_modal_map" class="modal-minimap-container"></div>
                        </div>

                        <!-- Observaciones -->
                        <div class="form-field-group" style="margin-top: 6px;">
                            <label class="form-field-label" for="create_observations">Observaciones</label>
                            <textarea name="observations" id="create_observations" class="custom-form-textarea" rows="3"
                                placeholder="Observaciones técnicas, fuentes de ruido predominantes, ciclos de trabajo..."></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action" id="create_modal_submit_btn">+ Guardar Punto de Medición</button>
            </div>
        </form>
    </div>
</div>
