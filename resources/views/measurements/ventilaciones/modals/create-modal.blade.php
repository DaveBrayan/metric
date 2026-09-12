    <!-- ==========================================================================
         MODAL: NUEVA MEDICIÓN DE VENTILACIÓN (3 COLUMNAS)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="createMeasModalTitle">
        <div class="modal-dialog-ventilation modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2" />
                        <path d="M9.6 4.6A2 2 0 1 1 11 8H2" />
                        <path d="M12.6 19.4A2 2 0 1 0 14 16H2" />
                    </svg>
                    <h2 id="createMeasModalTitle">Nuevo Punto de Medición de Ventilación</h2>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()"
                    aria-label="Cerrar">✕</button>
            </div>
            <form action="{{ route('modules.ventilation.measurements.store', $module->id) }}" method="POST"
                enctype="multipart/form-data" id="createMeasurementForm">
                @csrf
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos Técnicos y Parámetros de Ventilación -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & Mediciones</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="create_pt_num_disp">{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="create_point_number"
                                value="{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}">

                            <!-- Fecha y Hora -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_date">Fecha de Medición <span class="req">*</span></label>
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

                            <!-- Local de Trabajo -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_local_trabajo">Local de Trabajo <span class="req">*</span></label>
                                <input type="text" name="local_trabajo" id="create_local_trabajo" class="custom-form-input" required
                                    placeholder="Ej: Taller Central, Oficina Contabilidad, Cocina...">
                            </div>

                            <!-- Tipo de Local (44 Opciones Normativas) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_tipo_local">
                                    <span>Tipo de Local (Normativo) <span class="req">*</span></span>
                                </label>
                                <select name="tipo_local" id="create_tipo_local" class="custom-form-select" required onchange="onTipoLocalChange('create')">
                                    @foreach(\App\Http\Controllers\VentilationController::$tiposLocalNorma as $tipo => $norma)
                                        <option value="{{ $tipo }}" data-min="{{ $norma['min'] }}" data-max="{{ $norma['max'] }}" data-intervalo="{{ $norma['intervalo'] }}" {{ $loop->first ? 'selected' : '' }}>
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Ventilación & Elemento -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_tipo_ventilacion">Tipo Ventilación <span class="req">*</span></label>
                                    <select name="tipo_ventilacion" id="create_tipo_ventilacion" class="custom-form-select" required>
                                        <option value="Natural" selected>Natural</option>
                                        <option value="Mecánica">Mecánica</option>
                                    </select>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_elemento_ventilacion">Elemento</label>
                                    <select name="elemento_ventilacion" id="create_elemento_ventilacion" class="custom-form-select">
                                        <option value="Ventana" selected>Ventana</option>
                                        <option value="Puerta">Puerta</option>
                                        <option value="Rejas">Rejas</option>
                                        <option value="Ventilador circular">Ventilador circular</option>
                                        <option value="Extractor circular">Extractor circular</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Temperatura Seca & Velocidad del Aire -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_temperatura_seca_c">Temp. Seca (°C)</label>
                                    <input type="number" step="0.1" name="temperatura_seca_c" id="create_temperatura_seca_c" class="custom-form-input" placeholder="Ej: 22.5">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_vel_aire_ms">Vel. Aire (m/s) <span class="req">*</span></label>
                                    <input type="number" step="0.01" name="vel_aire_ms" id="create_vel_aire_ms" class="custom-form-input" required placeholder="Ej: 1.25" oninput="recalcVentilation('create')">
                                </div>
                            </div>

                            <!-- Área de Ventilación (L, A, D) -->
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; margin-bottom: 12px;">
                                <span style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Área de Ventilación (m)</span>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Largo (m)</label>
                                        <input type="number" step="0.01" name="area_largo_m" id="create_area_largo_m" class="custom-form-input" placeholder="L" oninput="recalcVentilation('create')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Ancho (m)</label>
                                        <input type="number" step="0.01" name="area_ancho_m" id="create_area_ancho_m" class="custom-form-input" placeholder="A" oninput="recalcVentilation('create')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Diám. (m)</label>
                                        <input type="number" step="0.01" name="area_diametro_m" id="create_area_diametro_m" class="custom-form-input" placeholder="D" oninput="recalcVentilation('create')">
                                    </div>
                                </div>
                            </div>

                            <!-- Volumen del Ambiente (L, A, H) -->
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; margin-bottom: 12px;">
                                <span style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Volumen del Ambiente (m)</span>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Largo (m)</label>
                                        <input type="number" step="0.01" name="vol_largo_m" id="create_vol_largo_m" class="custom-form-input" placeholder="L" oninput="recalcVentilation('create')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Ancho (m)</label>
                                        <input type="number" step="0.01" name="vol_ancho_m" id="create_vol_ancho_m" class="custom-form-input" placeholder="A" oninput="recalcVentilation('create')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Alto (m)</label>
                                        <input type="number" step="0.01" name="vol_alto_m" id="create_vol_alto_m" class="custom-form-input" placeholder="H" oninput="recalcVentilation('create')">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="renovaciones_min" id="create_renovaciones_min" value="10">
                            <input type="hidden" name="renovaciones_max" id="create_renovaciones_max" value="15">
                            <input type="hidden" name="renovaciones_intervalo" id="create_renovaciones_intervalo" value="10 - 15">
                            <input type="hidden" name="cumple" id="create_cumple" value="CUMPLE">

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
                                    <span>{{ $registeredByHeader }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA 2: Evidencia Fotográfica (Slide / Carrusel) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Evidencia Fotográfica (Slide)</span>
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
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Subir Evidencia Fotográfica</strong>
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
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite subir varias fotos del punto. Puedes pasar imagen por imagen con las flechas.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Mapa + Observaciones -->
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

                            <!-- Mini Mapa interactivo -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa (Haz clic para posicionar)</label>
                                <div id="create_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="create_observations">Observaciones</label>
                                <textarea name="observations" id="create_observations" class="custom-form-textarea" rows="3"
                                    placeholder="Condiciones del flujo de aire, obstrucciones, ventanas abiertas/cerradas..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action" id="create_modal_submit_btn">Guardar Punto de Medición</button>
                </div>
            </form>
        </div>
    </div>
