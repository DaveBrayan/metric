    <!-- ==========================================================================
         MODAL: DETALLE / EDICIÓN DE MEDICIÓN DE OPACIDAD (3 COLUMNAS - MODO CONSULTA CON BOTÓN EDITAR)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="editMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <h2 id="editMeasModalTitle">Detalle del Punto — Opacidad Vehicular</h2>
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

                        <!-- COLUMNA 1: Datos Técnicos y Mediciones de Opacidad / RPM -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Vehículo & Ensayos</span>
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
                                <select name="staff_id" id="edit_staff_id" class="custom-form-select" disabled
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

                            <!-- Área y Altitud (con selector dinámico) -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_area">Área / Sector</label>
                                    <input type="text" name="area" id="edit_area" class="custom-form-input"
                                        placeholder="Ej: Área Operativa" disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_altitud">Altitud (msnm) <span class="req">*</span></label>
                                    <select name="altitud" id="edit_altitud" class="custom-form-select" disabled
                                        onchange="onAltitudeChange('edit')">
                                        <option value="0-1500">0 - 1500 msnm</option>
                                        <option value="1500-3000">1500 - 3000 msnm</option>
                                        <option value="3000-4500">3000 - 4500 msnm</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Leyenda informativa de altitud y límite normativo -->
                            <div id="edit_altitud_legend" style="background: #eef2ff; border: 1.5px solid #c7d2fe; border-radius: 10px; padding: 8px 12px; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#4338ca" stroke-width="2.2"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                                    <div>
                                        <div style="font-size: 10px; font-weight: 700; color: #6366f1; text-transform: uppercase;">Norma NB 62002</div>
                                        <div style="font-size: 12px; font-weight: 800; color: #312e81;" id="edit_altitud_text">Altitud: 1500-3000 msnm</div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <span style="font-size: 10px; font-weight: 700; color: #4338ca;">LMP:</span>
                                    <span style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 900; color: #3730a3; margin-left: 3px;" id="edit_altitud_limit_text">2,80 m⁻¹</span>
                                </div>
                            </div>

                            <!-- Tipo de Vehículo & Placa -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_tipo_vehiculo">Tipo de Vehículo <span class="req">*</span></label>
                                    <input type="text" name="tipo_vehiculo" id="edit_tipo_vehiculo" class="custom-form-input"
                                        required disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_placa">Placa / Identificador <span class="req">*</span></label>
                                    <input type="text" name="placa" id="edit_placa" class="custom-form-input"
                                        required disabled style="text-transform: uppercase; font-family: monospace; font-weight: 800;">
                                </div>
                            </div>

                            <!-- Marca & Modelo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_marca">Marca</label>
                                    <input type="text" name="marca" id="edit_marca" class="custom-form-input" disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_modelo">Modelo</label>
                                    <input type="text" name="modelo" id="edit_modelo" class="custom-form-input" disabled>
                                </div>
                            </div>

                            <!-- Temp Motor & Límite Normativo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_temp_c">Temperatura Motor (°C)</label>
                                    <input type="number" step="0.1" name="temp_c" id="edit_temp_c" class="custom-form-input" disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_limite_normativa">Límite Normativo (m⁻¹)</label>
                                    <input type="number" step="0.01" name="limite_normativa" id="edit_limite_normativa" class="custom-form-input"
                                        disabled oninput="recalcOpacidad('edit')">
                                </div>
                            </div>

                            <!-- 3 Lecturas de Opacidad (%) -->
                            <div class="form-field-group">
                                <label class="form-field-label">Lecturas de Opacidad (%) — 3 Ensayos</label>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                                    <input type="number" step="0.01" name="opa_1" id="edit_opa_1" class="custom-form-input" placeholder="L1 (%)" disabled oninput="recalcOpacidad('edit')">
                                    <input type="number" step="0.01" name="opa_2" id="edit_opa_2" class="custom-form-input" placeholder="L2 (%)" disabled oninput="recalcOpacidad('edit')">
                                    <input type="number" step="0.01" name="opa_3" id="edit_opa_3" class="custom-form-input" placeholder="L3 (%)" disabled oninput="recalcOpacidad('edit')">
                                </div>
                            </div>

                            <!-- 3 Lecturas de Tacómetro (RPM) -->
                            <div class="form-field-group">
                                <label class="form-field-label">Lecturas de Tacómetro (RPM de Corte)</label>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px;">
                                    <input type="number" step="any" name="rpm_1" id="edit_rpm_1" class="custom-form-input" placeholder="RPM 1" disabled oninput="recalcOpacidad('edit')">
                                    <input type="number" step="any" name="rpm_2" id="edit_rpm_2" class="custom-form-input" placeholder="RPM 2" disabled oninput="recalcOpacidad('edit')">
                                    <input type="number" step="any" name="rpm_3" id="edit_rpm_3" class="custom-form-input" placeholder="RPM 3" disabled oninput="recalcOpacidad('edit')">
                                </div>
                            </div>

                            <!-- Resumen en Vivo de Cálculos (Opacidad, RPM y Cumple) -->
                            <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; margin-top: 4px; display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <div style="font-size: 10px; font-weight: 700; color: #0284c7; text-transform: uppercase;">Media K(m⁻¹)</div>
                                    <div style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 900; color: #0369a1;" id="edit_opa_promedio_display">— %</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase;">RPM Prom.</div>
                                    <div style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: #15803d;" id="edit_rpm_promedio_display">— RPM</div>
                                </div>
                                <div style="text-align: right;" id="edit_cumple_badge">
                                    <span class="badge-compliance-ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6"><polyline points="20 6 9 17 4 12"/></svg><span>CUMPLE</span></span>
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

                                    <img id="edit_slider_img" class="modal-slider-main-img" src="" alt="Foto vehículo"
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
                                        <span style="font-size: 11.5px; color: #94a3b8;">No se registraron imágenes para este vehículo</span>
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
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar todas las fotos del vehículo en modo carrusel continuo.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Dónde Está Ubicado (GPS + Mini Mapa + Observaciones) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button" id="edit_btn_gps"
                                    onclick="captureCoordinatesGPS('edit')"
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
                                        placeholder="218468.0" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" name="utm_northing" id="edit_utm_northing" class="custom-form-input"
                                        placeholder="7627234.0" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                    <input type="text" name="utm_zone" id="edit_utm_zone" class="custom-form-input" placeholder="19K"
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
                            <input type="hidden" name="location_description" id="edit_location_description">
                            <input type="hidden" name="latitude" id="edit_latitude">
                            <input type="hidden" name="longitude" id="edit_longitude">

                            <!-- Mini Mapa interactivo: Dónde está ubicado -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="edit_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones: Textarea directamente debajo del mapa -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones y Estado del Escape</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3"
                                    disabled
                                    placeholder="Observaciones técnicas del vehículo, humo, escape..."></textarea>
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

