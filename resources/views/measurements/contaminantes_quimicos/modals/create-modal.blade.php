<!-- ==========================================================================
     MODAL 1: NUEVA MEDICIÓN DE CONTAMINANTES QUÍMICOS (3 COLUMNAS)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true"
    aria-labelledby="createMeasModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M10 2v7.31L4.89 20a2 2 0 0 0 1.77 3h14.68a2 2 0 0 0 1.77-3L14 9.31V2" />
                    <path d="M8.5 2h7" />
                    <path d="M7 16h10" />
                </svg>
                <h2 id="createMeasModalTitle">Nuevo Punto de Muestreo de Contaminantes Químicos</h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()"
                aria-label="Cerrar">✕</button>
        </div>
        <form action="{{ route('modules.contaminantes_quimicos.measurements.store', $module->id) }}" method="POST"
            enctype="multipart/form-data" id="createMeasurementForm">
            @csrf
            <div class="modal-body-custom">
                <div class="modal-three-cols-grid">

                    <!-- COLUMNA 1: Datos Técnicos y Parámetros de Muestreo -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>1. Datos del Punto & Muestreo</span>
                            <span style="font-size: 11px; font-weight: 700; color: #0284c7;">N° <strong
                                    id="create_pt_num_disp">{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}</strong></span>
                        </div>

                        <input type="hidden" name="point_number" id="create_point_number"
                            value="{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}">

                        <!-- Fecha y Hora -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_measurement_date">Fecha de Muestreo <span
                                        class="req">*</span></label>
                                <input type="date" name="measurement_date" id="create_measurement_date"
                                    class="custom-form-input" required value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_measurement_time">Hora de Registro</label>
                                <input type="time" name="measurement_time" id="create_measurement_time"
                                    class="custom-form-input" value="{{ date('H:i') }}">
                            </div>
                        </div>

                        <!-- Técnico de Campo / Personal a Cargo -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_staff_id">
                                <span>Técnico de Higiene / Personal a Cargo <span class="req">*</span></span>
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

                        <!-- Área y Punto de Muestreo -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_area">Área / Sección <span
                                        class="req">*</span></label>
                                <input type="text" name="area" id="create_area" class="custom-form-input" required
                                    placeholder="Ej: Mezclado y Reactivos">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_punto_medicion">Punto de Muestreo <span
                                        class="req">*</span></label>
                                <input type="text" name="punto_medicion" id="create_punto_medicion" class="custom-form-input"
                                    required placeholder="Ej: Zona Respiratoria Operador 1">
                            </div>
                        </div>

                        <!-- Trabajador Evaluado -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_trabajador_nombre">Trabajador Evaluado</label>
                            <input type="text" name="trabajador_nombre" id="create_trabajador_nombre" class="custom-form-input"
                                placeholder="Nombre completo del operador...">
                        </div>

                        <!-- Masa Inicial y Masa Final Filtro (mg) -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_masa_inicial_filtro_mg">
                                    <span>Masa Inicial Filtro (mg)</span>
                                </label>
                                <input type="number" step="0.0001" name="masa_inicial_filtro_mg" id="create_masa_inicial_filtro_mg"
                                    class="custom-form-input" placeholder="Ej: 15.2340" oninput="calcContaminantes('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_masa_final_filtro_mg">
                                    <span>Masa Final Filtro (mg)</span>
                                </label>
                                <input type="number" step="0.0001" name="masa_final_filtro_mg" id="create_masa_final_filtro_mg"
                                    class="custom-form-input" placeholder="Ej: 16.1420" oninput="calcContaminantes('create')">
                            </div>
                        </div>

                        <!-- Horario de Muestreo (Hora Inicio y Hora Final) -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_hora_inicio">Hora Inicio Muestreo</label>
                                <input type="time" name="hora_inicio" id="create_hora_inicio" class="custom-form-input"
                                    value="08:00" onchange="calcContaminantes('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_hora_final">Hora Final Muestreo</label>
                                <input type="time" name="hora_final" id="create_hora_final" class="custom-form-input"
                                    value="12:00" onchange="calcContaminantes('create')">
                            </div>
                        </div>

                        <!-- Caudales Inicial y Final (L/min) -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_q_inicial_lmin">Caudal Inicial (L/min)</label>
                                <input type="number" step="0.01" name="q_inicial_lmin" id="create_q_inicial_lmin"
                                    class="custom-form-input" value="2.00" oninput="calcContaminantes('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_q_final_lmin">Caudal Final (L/min)</label>
                                <input type="number" step="0.01" name="q_final_lmin" id="create_q_final_lmin"
                                    class="custom-form-input" value="2.00" oninput="calcContaminantes('create')">
                            </div>
                        </div>

                        <!-- Condiciones Ambientales (Temperatura y Presión) -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_t_inicial_c">Temp. Inicial (°C)</label>
                                <input type="number" step="0.1" name="t_inicial_c" id="create_t_inicial_c"
                                    class="custom-form-input" placeholder="Ej: 19.5">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_presion_hpa">Presión Atmosférica (hPa)</label>
                                <input type="number" step="0.1" name="presion_hpa" id="create_presion_hpa"
                                    class="custom-form-input" value="1013.2">
                            </div>
                        </div>

                        <!-- Strip de Resumen de Cálculos en Vivo -->
                        <div class="readings-summary-strip" id="create_calc_summary_strip" style="margin-top: 8px;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap; font-size: 11.5px;">
                                <span>Masa Neta: <strong id="create_calc_masa_neta" style="color: #0284c7;">0.0000 mg</strong></span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>Volumen: <strong id="create_calc_volumen" style="color: #0284c7;">0.0000 m³</strong></span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>Conc: <strong id="create_calc_conc" style="color: #059669;">0.0000 mg/m³</strong></span>
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
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#0284c7"
                                        stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                        style="margin-bottom: 10px;">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                    <strong style="font-size: 13.5px; color: #0284c7; margin-bottom: 4px;">Clic para Subir Fotografías</strong>
                                    <span style="font-size: 11.5px; color: #64748b;">Fotos del tren de muestreo, punto y trabajador</span>
                                </div>
                            </div>

                            <div class="slider-thumbs-strip" id="create_slider_thumbs" style="display: none;"></div>

                            <input type="file" name="images[]" id="create_images_input" multiple accept="image/*"
                                style="display: none;" onchange="handleMultipleImagesSelected(this, 'create')">
                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                                <button type="button" class="btn-add-photos-trigger"
                                    onclick="document.getElementById('create_images_input').click()">
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
                                    <span>Eliminar Foto</span>
                                </button>
                            </div>
                        </div>
                        <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite registrar y previsualizar todas las fotografías del tren de muestreo químico en carrusel.</span>
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
                                <input type="number" step="any" name="utm_easting" id="create_utm_easting"
                                    class="custom-form-input" placeholder="592450.000"
                                    oninput="syncUtmToMap('create')">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_utm_northing">Norte (N)</label>
                                <input type="number" step="any" name="utm_northing" id="create_utm_northing"
                                    class="custom-form-input" placeholder="8175320.000"
                                    oninput="syncUtmToMap('create')">
                            </div>
                            <div class="form-field-group" style="max-width: 85px;">
                                <label class="form-field-label" for="create_utm_zone">Zona (Z)</label>
                                <input type="text" name="utm_zone" id="create_utm_zone" class="custom-form-input"
                                    placeholder="19K" value="19K" oninput="syncUtmToMap('create')">
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
                                placeholder="Condiciones del entorno, fuente de emisión química, EPP utilizado..."></textarea>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle"
                    onclick="closeCreateMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action">Guardar Punto de Muestreo</button>
            </div>
        </form>
    </div>
</div>
