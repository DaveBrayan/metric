    <!-- ==========================================================================
         MODAL 1: NUEVA ESTACIÓN DE PARTÍCULAS AMBIENTALES
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="createMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <path d="M12 3v18" />
                        <path d="M3 12h18" />
                    </svg>
                    <h2 id="createMeasModalTitle">Nueva Estación de Partículas Ambientales</h2>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()"
                    aria-label="Cerrar">✕</button>
            </div>
            <form action="{{ route('modules.particulas_ambientales.measurements.store', $module->id) }}" method="POST" enctype="multipart/form-data" id="createMeasurementForm">
                @csrf
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Horarios, Clima & Muestreo Gravimétrico -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Horarios, Clima & Muestreo</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">Estación: <strong
                                        id="create_pt_num_disp">PA-{{ $totalMeasurements + 1 }}</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="create_point_number" value="PA-{{ $totalMeasurements + 1 }}">

                            <!-- Fechas y Horas de Inicio y Fin -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label">Fecha Inicio <span class="req">*</span></label>
                                    <input type="date" name="fecha_inicio" class="custom-form-input" id="create_fecha_inicio" value="{{ date('Y-m-d') }}" required onchange="calculateParticulasAmbDiffHours('create')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">Hora Inicio <span class="req">*</span></label>
                                    <input type="time" name="hora_inicio" class="custom-form-input" id="create_hora_inicio" value="08:00" required onchange="calculateParticulasAmbDiffHours('create')">
                                </div>
                            </div>

                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label">Fecha Finalización <span class="req">*</span></label>
                                    <input type="date" name="fecha_fin" class="custom-form-input" id="create_fecha_fin" value="{{ date('Y-m-d', strtotime('+1 day')) }}" required onchange="calculateParticulasAmbDiffHours('create')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">Hora Finalización <span class="req">*</span></label>
                                    <input type="time" name="hora_fin" class="custom-form-input" id="create_hora_fin" value="08:00" required onchange="calculateParticulasAmbDiffHours('create')">
                                </div>
                            </div>

                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label">Diferencia de Horas (h)</label>
                                    <input type="number" step="any" name="diferencia_horas" class="custom-form-input" id="create_diferencia_horas" value="24.00" onkeyup="calculateParticulasAmbGravimetric('create')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">Caudal (L/min)</label>
                                    <input type="number" step="any" name="caudal" class="custom-form-input" id="create_caudal" value="1130.0" onkeyup="calculateParticulasAmbGravimetric('create')">
                                </div>
                            </div>

                            <!-- Área y Punto de Medición -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label">Área / Zona de Monitoreo <span class="req">*</span></label>
                                    <input type="text" name="area" class="custom-form-input" id="create_area" placeholder="Ej: ALMACÉN DE AGREGADOS" required>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">Punto de Medición / Estación <span class="req">*</span></label>
                                    <input type="text" name="punto_medicion" class="custom-form-input" id="create_punto_medicion" placeholder="Ej: INGRESO" required>
                                </div>
                            </div>

                            <!-- Meteorología -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label">Temp. Máx (°C)</label>
                                    <input type="number" step="any" name="temp_max" class="custom-form-input" id="create_temp_max" value="25.0">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">Temp. Mín (°C)</label>
                                    <input type="number" step="any" name="temp_min" class="custom-form-input" id="create_temp_min" value="28.0">
                                </div>
                            </div>

                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label">Presión Atm. (mmHg)</label>
                                    <input type="number" step="any" name="presion_atm" class="custom-form-input" id="create_presion_atm" value="495.0">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">Vel. Viento (Km/h)</label>
                                    <input type="number" step="any" name="vel_viento" class="custom-form-input" id="create_vel_viento" value="23.0">
                                </div>
                            </div>

                            <div class="form-field-group">
                                <label class="form-field-label">Dirección del Viento</label>
                                <input type="text" name="dir_viento" class="custom-form-input" id="create_dir_viento" placeholder="Ej: Noreste, NE..." value="Noreste">
                            </div>

                            <!-- Muestreo PM-10 -->
                            <div class="part-readings-group" style="margin-top: 8px; margin-bottom: 8px;">
                                <div class="part-readings-header">
                                    <span class="part-badge-tag pm10">Muestreo PM-10 (LMP: 150 µg/m³)</span>
                                    <span class="part-prom-tag">Conc: <strong id="create_pm10_prom_disp">0.00</strong> µg/m³</span>
                                </div>
                                <div class="form-grid-two-cols">
                                    <div class="form-field-group">
                                        <label class="form-field-label" style="font-size: 11px;">Peso Filtro Inicial (gr)</label>
                                        <input type="number" step="any" name="pm10_filtro_inicial" class="custom-form-input" id="create_pm10_filtro_inicial" placeholder="0.1234" onkeyup="calculateParticulasAmbGravimetric('create')">
                                    </div>
                                    <div class="form-field-group">
                                        <label class="form-field-label" style="font-size: 11px;">Peso Filtro Final (gr)</label>
                                        <input type="number" step="any" name="pm10_filtro_final" class="custom-form-input" id="create_pm10_filtro_final" placeholder="0.1354" onkeyup="calculateParticulasAmbGravimetric('create')">
                                    </div>
                                </div>
                                <input type="hidden" name="pm10_prom" id="create_pm10_prom" value="">
                            </div>

                            <!-- Muestreo PST -->
                            <div class="part-readings-group" style="margin-bottom: 6px;">
                                <div class="part-readings-header">
                                    <span class="part-badge-tag pm25">Muestreo PST (LMP: 260 µg/m³)</span>
                                    <span class="part-prom-tag">Conc: <strong id="create_pst_prom_disp">0.00</strong> µg/m³</span>
                                </div>
                                <div class="form-grid-two-cols">
                                    <div class="form-field-group">
                                        <label class="form-field-label" style="font-size: 11px;">Peso Filtro Inicial (gr)</label>
                                        <input type="number" step="any" name="pst_filtro_inicial" class="custom-form-input" id="create_pst_filtro_inicial" placeholder="0.1456" onkeyup="calculateParticulasAmbGravimetric('create')">
                                    </div>
                                    <div class="form-field-group">
                                        <label class="form-field-label" style="font-size: 11px;">Peso Filtro Final (gr)</label>
                                        <input type="number" step="any" name="pst_filtro_final" class="custom-form-input" id="create_pst_filtro_final" placeholder="0.2365" onkeyup="calculateParticulasAmbGravimetric('create')">
                                    </div>
                                </div>
                                <input type="hidden" name="pst_prom" id="create_pst_prom" value="">
                            </div>
                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico (Slide / Carrusel Central) -->
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

                                    <img id="create_slider_img" class="modal-slider-main-img" src="" alt="Foto estación"
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
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Subir Fotografías Ambientales</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Haz clic para agregar o reemplazar imágenes</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="create_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" multiple accept="image/*" id="create_images_input" style="display: none;" onchange="handleMultipleImagesSelected(this, 'create')">
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
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar las fotografías de la estación ambiental. Puedes navegar con las flechas o miniaturas.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica, GPS, Mini Mapa & Observaciones -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button" class="btn-gps-action" onclick="getCurrentGpsPosition('create_latitude', 'create_longitude', 'create')" title="Obtener coordenadas GPS en vivo"
                                    style="background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                        <circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="3" />
                                    </svg>
                                    Mi GPS
                                </button>
                            </div>

                            <div class="utm-coords-grid">
                                <div class="form-field-group">
                                    <label class="form-field-label">UTM Este (E)</label>
                                    <input type="number" step="any" name="utm_easting" class="custom-form-input" id="create_utm_easting" placeholder="592450.00" onchange="syncUtmToMap('create')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">UTM Norte (N)</label>
                                    <input type="number" step="any" name="utm_northing" class="custom-form-input" id="create_utm_northing" placeholder="8175320.00" onchange="syncUtmToMap('create')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label">Zona (Z)</label>
                                    <input type="text" name="utm_zone" class="custom-form-input" id="create_utm_zone" value="19K">
                                </div>
                            </div>

                            <div class="utm-preview-pill">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20" />
                                    <path d="M2 12h20" />
                                </svg>
                                <span id="create_utm_display">E: 592450.00, N: 8175320.00, Z: 19K</span>
                            </div>

                            <input type="hidden" name="location" id="create_location">
                            <input type="hidden" name="latitude" id="create_latitude" value="-16.5034">
                            <input type="hidden" name="longitude" id="create_longitude" value="-68.1324">

                            <!-- Mini Mapa Leaflet -->
                            <div style="margin-top: 6px;">
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="create_modal_map" class="modal-minimap-container" style="height: 140px; border-radius: 10px; border: 1.5px solid #cbd5e1;"></div>
                            </div>

                            <!-- Personal Asignado -->
                            <div class="form-field-group" style="margin-top: 8px;">
                                <label class="form-field-label">Técnico / Personal de Campo</label>
                                <select name="staff_id" class="custom-form-input" id="create_staff_id">
                                    <option value="">Seleccionar técnico...</option>
                                    @foreach($staffList as $st)
                                        <option value="{{ $st->id }}">{{ $st->full_name ?: $st->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label">Observaciones</label>
                                <textarea name="observations" class="custom-form-input" id="create_observations" rows="3" placeholder="Observaciones técnicas de la estación..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Estación</button>
                </div>
            </form>
        </div>
    </div>
