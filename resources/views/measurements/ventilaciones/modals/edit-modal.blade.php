    <!-- ==========================================================================
         MODAL 2: DETALLE / EDICIÓN DE MEDICIÓN DE VENTILACIÓN (CONSULTA Y EDICIÓN)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="editMeasModalTitle">
        <div class="modal-dialog-ventilation modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    <h2 id="editMeasModalTitle">Detalle del Punto de Medición</h2>
                    <span id="modalModeStatusBadge" class="modal-badge-view">Solo Lectura</span>
                    <!-- Personal Registrado -->
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

                        <!-- COLUMNA 1: Datos Técnicos y Parámetros de Ventilación -->
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

                            <!-- Local de Trabajo -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_local_trabajo">Local de Trabajo <span class="req">*</span></label>
                                <input type="text" name="local_trabajo" id="edit_local_trabajo" class="custom-form-input" required disabled>
                            </div>

                            <!-- Tipo de Local (44 Opciones Normativas) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_tipo_local">
                                    <span>Tipo de Local (Normativo) <span class="req">*</span></span>
                                </label>
                                <select name="tipo_local" id="edit_tipo_local" class="custom-form-select" required disabled onchange="onTipoLocalChange('edit')">
                                    @foreach(\App\Http\Controllers\VentilationController::$tiposLocalNorma as $tipo => $norma)
                                        <option value="{{ $tipo }}" data-min="{{ $norma['min'] }}" data-max="{{ $norma['max'] }}" data-intervalo="{{ $norma['intervalo'] }}">
                                            {{ $tipo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Ventilación & Elemento -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_tipo_ventilacion">Tipo Ventilación <span class="req">*</span></label>
                                    <select name="tipo_ventilacion" id="edit_tipo_ventilacion" class="custom-form-select" required disabled>
                                        <option value="Natural">Natural</option>
                                        <option value="Mecánica">Mecánica</option>
                                    </select>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_elemento_ventilacion">Elemento</label>
                                    <select name="elemento_ventilacion" id="edit_elemento_ventilacion" class="custom-form-select" disabled>
                                        <option value="Ventana">Ventana</option>
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
                                    <label class="form-field-label" for="edit_temperatura_seca_c">Temp. Seca (°C)</label>
                                    <input type="number" step="0.1" name="temperatura_seca_c" id="edit_temperatura_seca_c" class="custom-form-input" disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_vel_aire_ms">Vel. Aire (m/s) <span class="req">*</span></label>
                                    <input type="number" step="0.01" name="vel_aire_ms" id="edit_vel_aire_ms" class="custom-form-input" required disabled oninput="recalcVentilation('edit')">
                                </div>
                            </div>

                            <!-- Área de Ventilación (L, A, D) -->
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; margin-bottom: 12px;">
                                <span style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Área de Ventilación (m)</span>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Largo (m)</label>
                                        <input type="number" step="0.01" name="area_largo_m" id="edit_area_largo_m" class="custom-form-input" placeholder="L" disabled oninput="recalcVentilation('edit')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Ancho (m)</label>
                                        <input type="number" step="0.01" name="area_ancho_m" id="edit_area_ancho_m" class="custom-form-input" placeholder="A" disabled oninput="recalcVentilation('edit')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Diám. (m)</label>
                                        <input type="number" step="0.01" name="area_diametro_m" id="edit_area_diametro_m" class="custom-form-input" placeholder="D" disabled oninput="recalcVentilation('edit')">
                                    </div>
                                </div>
                            </div>

                            <!-- Volumen del Ambiente (L, A, H) -->
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 8px 10px; margin-bottom: 12px;">
                                <span style="font-size: 11px; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Volumen del Ambiente (m)</span>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Largo (m)</label>
                                        <input type="number" step="0.01" name="vol_largo_m" id="edit_vol_largo_m" class="custom-form-input" placeholder="L" disabled oninput="recalcVentilation('edit')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Ancho (m)</label>
                                        <input type="number" step="0.01" name="vol_ancho_m" id="edit_vol_ancho_m" class="custom-form-input" placeholder="A" disabled oninput="recalcVentilation('edit')">
                                    </div>
                                    <div>
                                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600;">Alto (m)</label>
                                        <input type="number" step="0.01" name="vol_alto_m" id="edit_vol_alto_m" class="custom-form-input" placeholder="H" disabled oninput="recalcVentilation('edit')">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="renovaciones_min" id="edit_renovaciones_min">
                            <input type="hidden" name="renovaciones_max" id="edit_renovaciones_max">
                            <input type="hidden" name="renovaciones_intervalo" id="edit_renovaciones_intervalo">
                            <input type="hidden" name="cumple" id="edit_cumple">
                        </div>

                        <!-- COLUMNA 2: Evidencia Fotográfica (Slide / Carrusel) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Evidencia Fotográfica</span>
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

                                    <!-- Botón para eliminar la foto activa en edición -->
                                    <button type="button" class="slider-del-photo-btn" id="edit_slider_del_btn"
                                        onclick="deleteActiveSlidePhoto('edit')" style="display: none;"
                                        title="Eliminar esta fotografía">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" />
                                        </svg>
                                    </button>

                                    <img id="edit_slider_img" class="modal-slider-main-img" src="" alt="Foto punto"
                                        style="display: none;" onclick="openPhotoViewer(this.src, 'Fotografía Ampliada')">

                                    <div id="edit_slider_placeholder" class="modal-slider-placeholder"
                                        onclick="if(!document.getElementById('edit_measurement_date').disabled) document.getElementById('edit_images_input').click()">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografía</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Activa el modo edición para adjuntar imágenes</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*"
                                    style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                                <input type="hidden" name="remaining_images" id="edit_remaining_images" value="[]">

                                <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos"
                                    style="display: none;" onclick="document.getElementById('edit_images_input').click()">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span>+ Subir / Agregar Fotografías</span>
                                </button>
                            </div>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Haz clic en la imagen para verla en pantalla completa.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Dónde Está Ubicado -->
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
                                    </svg>
                                    Mi GPS
                                </button>
                            </div>

                            <div class="utm-coords-grid">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_easting">Este (E)</label>
                                    <input type="number" step="any" name="utm_easting" id="edit_utm_easting" class="custom-form-input"
                                        placeholder="218468.016" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                    <input type="number" step="any" name="utm_northing" id="edit_utm_northing" class="custom-form-input"
                                        placeholder="7627234.367" disabled oninput="syncUtmToMap('edit')">
                                </div>
                                <div class="form-field-group" style="max-width: 85px;">
                                    <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                    <input type="text" name="utm_zone" id="edit_utm_zone" class="custom-form-input" placeholder="20K"
                                        value="20K" disabled oninput="syncUtmToMap('edit')">
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

                            <!-- Mini Mapa interactivo -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="edit_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3"
                                    disabled placeholder="Sin observaciones registradas..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom" id="editModalFooter">
                    <button type="button" class="btn-secondary-subtle" onclick="closeEditMeasurementModal()">Cerrar</button>
                    <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn" style="display: none;">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
