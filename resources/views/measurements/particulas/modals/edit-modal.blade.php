    <!-- ==========================================================================
         MODAL 2: DETALLE / EDICIÓN DE MEDICIÓN DE PARTÍCULAS OCUPACIONALES
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
            <form action="" method="POST" enctype="multipart/form-data" id="editMeasurementForm" class="modal-view-mode">
                @csrf
                @method('PUT')
                <input type="hidden" name="measurement_id" id="edit_measurement_id">

                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">

                        <!-- COLUMNA 1: Datos Técnicos, Clima & Fracciones PM10 / PM2.5 -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & Mediciones</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="edit_pt_num_disp">01</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="edit_point_number">

                            <!-- Fecha y Hora -->
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
                                <select name="staff_id" id="edit_staff_id" class="custom-form-select" required disabled>
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
                                    <input type="text" name="area" id="edit_area" class="custom-form-input" required disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_workstation">Puesto de Trabajo <span
                                            class="req">*</span></label>
                                    <input type="text" name="workstation" id="edit_workstation" class="custom-form-input"
                                        required disabled>
                                </div>
                            </div>

                            <!-- Punto de Medición -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_punto_medicion">Punto de Medición <span
                                        class="req">*</span></label>
                                <input type="text" name="punto_medicion" id="edit_punto_medicion"
                                    class="custom-form-input" required disabled>
                            </div>

                            <!-- CONDICIONES AMBIENTALES / METEOROLÓGICAS -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_temperatura">TEMPERATURA (°C)</label>
                                    <input type="number" step="0.1" name="temperatura" id="edit_temperatura"
                                        class="custom-form-input" disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_hr_percent">HUMEDAD RELATIVA (%)</label>
                                    <input type="number" step="0.1" name="hr_percent" id="edit_hr_percent"
                                        class="custom-form-input" disabled>
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
                                        <span id="edit_pm10_compliance_badge" class="badge-compliance cumple" style="display: none;">Cumple</span>
                                    </div>
                                    <div class="part-measurements-row">
                                        <div class="part-input-cell">
                                            <label>MED. 1</label>
                                            <input type="number" step="0.001" name="pm10_1" id="edit_pm10_1" class="custom-form-input"
                                                placeholder="0.000" disabled oninput="calculateParticulasAverages('edit')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 2</label>
                                            <input type="number" step="0.001" name="pm10_2" id="edit_pm10_2" class="custom-form-input"
                                                placeholder="0.000" disabled oninput="calculateParticulasAverages('edit')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 3</label>
                                            <input type="number" step="0.001" name="pm10_3" id="edit_pm10_3" class="custom-form-input"
                                                placeholder="0.000" disabled oninput="calculateParticulasAverages('edit')">
                                        </div>
                                        <div class="part-promedio-cell">
                                            <label>PROMEDIO</label>
                                            <div class="part-promedio-badge" id="edit_pm10_prom_disp">—</div>
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
                                        <span id="edit_pm25_compliance_badge" class="badge-compliance cumple" style="display: none;">Cumple</span>
                                    </div>
                                    <div class="part-measurements-row">
                                        <div class="part-input-cell">
                                            <label>MED. 1</label>
                                            <input type="number" step="0.001" name="pm25_1" id="edit_pm25_1" class="custom-form-input"
                                                placeholder="0.000" disabled oninput="calculateParticulasAverages('edit')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 2</label>
                                            <input type="number" step="0.001" name="pm25_2" id="edit_pm25_2" class="custom-form-input"
                                                placeholder="0.000" disabled oninput="calculateParticulasAverages('edit')">
                                        </div>
                                        <div class="part-input-cell">
                                            <label>MED. 3</label>
                                            <input type="number" step="0.001" name="pm25_3" id="edit_pm25_3" class="custom-form-input"
                                                placeholder="0.000" disabled oninput="calculateParticulasAverages('edit')">
                                        </div>
                                        <div class="part-promedio-cell">
                                            <label>PROMEDIO</label>
                                            <div class="part-promedio-badge" id="edit_pm25_prom_disp">—</div>
                                        </div>
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

                                    <div id="edit_slider_placeholder" class="modal-slider-placeholder"
                                        onclick="if(isEditUnlocked) document.getElementById('edit_images_input').click()"
                                        style="cursor: default;">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografías Registradas</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Activa el modo edición para agregar fotografías</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*"
                                    style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                                <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos"
                                    style="display: none;"
                                    onclick="document.getElementById('edit_images_input').click()">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span>+ Subir / Agregar Fotografías</span>
                                </button>
                            </div>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar las fotografías del punto de muestreo. Puedes navegar con las flechas.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica, GPS, Mini Mapa & Observaciones -->
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
                                    <input type="number" step="any" name="utm_easting" id="edit_utm_easting" class="custom-form-input"
                                        disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" name="utm_northing" id="edit_utm_northing" class="custom-form-input"
                                        disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                    <input type="text" name="utm_zone" id="edit_utm_zone" class="custom-form-input"
                                        disabled oninput="syncUtmToMap('edit')">
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

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3" disabled></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle"
                        onclick="closeEditMeasurementModal()">Cerrar</button>
                    <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn" style="display: none;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
