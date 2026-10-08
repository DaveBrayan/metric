    <!-- ==========================================================================
         MODAL 1: NUEVA MEDICIÓN DE PARTÍCULAS (MODAL EN 3 COLUMNAS)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="createMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3" />
                        <circle cx="19" cy="8" r="2" />
                        <circle cx="5" cy="16" r="2" />
                        <circle cx="17" cy="17" r="1.5" />
                        <circle cx="7" cy="7" r="1.5" />
                    </svg>
                    <h2 id="createMeasModalTitle">Nuevo Punto de Monitoreo de Partículas Ocupacionales</h2>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()"
                    aria-label="Cerrar">✕</button>
            </div>
            <form action="{{ route('modules.particulas.measurements.store', $module->id) }}" method="POST"
                enctype="multipart/form-data" id="createMeasurementForm">
                @csrf
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos Técnicos, Clima & Fracciones PM10 / PM2.5 -->
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
                                    <label class="form-field-label" for="create_measurement_date">Fecha de Medición <span
                                            class="req">*</span></label>
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

                            <!-- Área y Puesto de Trabajo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_area">Área de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="area" id="create_area" class="custom-form-input" required
                                        placeholder="Ej: Planta de Trituración / Taller">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_workstation">Puesto de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="workstation" id="create_workstation" class="custom-form-input"
                                        required placeholder="Ej: Operador de Chancadora">
                                </div>
                            </div>

                            <!-- Punto de Medición -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_punto_medicion">Punto de Medición <span
                                        class="req">*</span></label>
                                <input type="text" name="punto_medicion" id="create_punto_medicion"
                                    class="custom-form-input" required placeholder="Ej: Altura de zona de respiración del operador">
                            </div>

                            <!-- CONDICIONES AMBIENTALES / METEOROLÓGICAS -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_temperatura">TEMPERATURA (°C)</label>
                                    <input type="number" step="0.1" name="temperatura" id="create_temperatura"
                                        class="custom-form-input" placeholder="Ej: 22.5">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_hr_percent">HUMEDAD RELATIVA (%)</label>
                                    <input type="number" step="0.1" name="hr_percent" id="create_hr_percent"
                                        class="custom-form-input" placeholder="Ej: 45.0">
                                </div>
                            </div>

                            <!-- LECTURAS ANALÍTICAS PM10 & PM2.5 (3 MEDICIONES C/U) -->
                            <div class="part-analytical-section-card">
                                <!-- PM10 (Inhalable) -->
                                <div class="part-fraction-input-box">
                                    <div class="part-fraction-input-header">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span class="part-fraction-title pm10">FRACCIÓN PM10 (mg/m³)</span>
                                            <span class="part-fraction-badge-norm">LMP: 10 mg/m³</span>
                                        </div>
                                        <span id="create_pm10_compliance_badge" class="badge-compliance cumple" style="display: none;">Cumple</span>
                                    </div>
                                    <div class="part-measurements-row">
                                        <div class="part-input-cell">
                                            <label>MED. 1</label>
                                            <input type="number" step="0.001" name="pm10_1" id="create_pm10_1" class="custom-form-input"
                                                placeholder="0.000" oninput="calculateParticulasAverages('create')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 2</label>
                                            <input type="number" step="0.001" name="pm10_2" id="create_pm10_2" class="custom-form-input"
                                                placeholder="0.000" oninput="calculateParticulasAverages('create')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 3</label>
                                            <input type="number" step="0.001" name="pm10_3" id="create_pm10_3" class="custom-form-input"
                                                placeholder="0.000" oninput="calculateParticulasAverages('create')">
                                        </div>
                                        <div class="part-promedio-cell">
                                            <label>PROMEDIO</label>
                                            <div class="part-promedio-badge" id="create_pm10_prom_disp">—</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- PM2.5 (Respirable) -->
                                <div class="part-fraction-input-box">
                                    <div class="part-fraction-input-header">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span class="part-fraction-title pm25">FRACCIÓN PM2.5 (mg/m³)</span>
                                            <span class="part-fraction-badge-norm">LMP: 3 mg/m³</span>
                                        </div>
                                        <span id="create_pm25_compliance_badge" class="badge-compliance cumple" style="display: none;">Cumple</span>
                                    </div>
                                    <div class="part-measurements-row">
                                        <div class="part-input-cell">
                                            <label>MED. 1</label>
                                            <input type="number" step="0.001" name="pm25_1" id="create_pm25_1" class="custom-form-input"
                                                placeholder="0.000" oninput="calculateParticulasAverages('create')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 2</label>
                                            <input type="number" step="0.001" name="pm25_2" id="create_pm25_2" class="custom-form-input"
                                                placeholder="0.000" oninput="calculateParticulasAverages('create')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 3</label>
                                            <input type="number" step="0.001" name="pm25_3" id="create_pm25_3" class="custom-form-input"
                                                placeholder="0.000" oninput="calculateParticulasAverages('create')">
                                        </div>
                                        <div class="part-promedio-cell">
                                            <label>PROMEDIO</label>
                                            <div class="part-promedio-badge" id="create_pm25_prom_disp">—</div>
                                        </div>
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
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite subir varias fotos del punto de muestreo. Puedes navegar con las flechas.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica, GPS, Mini Mapa & Observaciones -->
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

                            <!-- Mini Mapa interactivo: Dónde está ubicado -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa (Haz clic para posicionar)</label>
                                <div id="create_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="create_observations">Observaciones</label>
                                <textarea name="observations" id="create_observations" class="custom-form-textarea" rows="3"
                                    placeholder="Observaciones de ventilación, uso de respirador N95/P100, campanas extractoras..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle"
                        onclick="closeCreateMeasurementModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action" id="create_modal_submit_btn">Guardar Punto de Partículas</button>
                </div>
            </form>
        </div>
    </div>
