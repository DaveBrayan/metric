<!-- ==========================================================================
     MODAL: DETALLE / EDICIÓN DE PUNTO DE INSPECCIÓN FOTOGRÁFICA (3 COLUMNAS)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true"
    aria-labelledby="editMeasModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg" style="max-width: 1180px; width: 96%;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <div style="width: 34px; height: 34px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                </div>
                <h2 id="editMeasModalTitle">Detalle del Punto de Inspección</h2>
                <span id="modalModeStatusBadge" class="modal-badge-view">Solo Lectura</span>
                <!-- Personal Registrador -->
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
            <input type="hidden" name="measurement_id" id="edit_measurement_id">
            
            <div class="modal-body-custom">
                <div class="modal-three-cols-grid">

                    <!-- COLUMNA 1: Datos de Inspección & Categoría -->
                    <div class="modal-col-card">
                        <div class="modal-col-heading">
                            <span>1. Datos de Inspección</span>
                            <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                    id="edit_pt_num_disp">01</strong></span>
                        </div>

                        <input type="hidden" name="point_number" id="edit_point_number">

                        <!-- Fecha y Hora -->
                        <div class="form-grid-two-cols">
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_inspection_date">Fecha <span class="req">*</span></label>
                                <input type="date" name="inspection_date" id="edit_inspection_date"
                                    class="custom-form-input" required disabled>
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_inspection_time">Hora</label>
                                <input type="time" name="inspection_time" id="edit_inspection_time"
                                    class="custom-form-input" disabled>
                            </div>
                        </div>

                        <!-- Técnico de Campo / Personal a Cargo -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_staff_id">
                                <span>Técnico de Campo / Personal <span class="req">*</span></span>
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

                        <!-- Área / Sector -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_area">Área / Sector <span class="req">*</span></label>
                            <input type="text" name="area" id="edit_area" class="custom-form-input" required disabled
                                placeholder="Ej: Planta Baja, Almacén General, Calderas...">
                        </div>

                        <!-- Categoría de Inspección (Selector con 31 categorías) -->
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_observation">
                                <span>Categoría de Inspección <span class="req">*</span></span>
                            </label>
                            <select name="observation" id="edit_observation" class="custom-form-select" required disabled>
                                <option value="Extintores">Extintores</option>
                                <option value="Alarmas">Alarmas</option>
                                <option value="Detectores">Detectores</option>
                                <option value="Bocas de incendio">Bocas de incendio</option>
                                <option value="Hidrantes">Hidrantes</option>
                                <option value="Señalizaciones Baños">Señalizaciones Baños</option>
                                <option value="Cocina">Cocina</option>
                                <option value="Comedores">Comedores</option>
                                <option value="Vestidores">Vestidores</option>
                                <option value="Áreas administrativas">Áreas administrativas</option>
                                <option value="Mantenimiento">Mantenimiento</option>
                                <option value="Áreas operativas">Áreas operativas</option>
                                <option value="Almacenes">Almacenes</option>
                                <option value="Residuos">Residuos</option>
                                <option value="Instalaciones electricas">Instalaciones electricas</option>
                                <option value="Ropa de Trabajo y EPP">Ropa de Trabajo y EPP</option>
                                <option value="Fachada y logo">Fachada y logo</option>
                                <option value="Pasillos y áreas exteriores">Pasillos y áreas exteriores</option>
                                <option value="Maquinaria y herramientas">Maquinaria y herramientas</option>
                                <option value="Sustancias peligrosas">Sustancias peligrosas</option>
                                <option value="Botiquin de primeros auxilios">Botiquin de primeros auxilios</option>
                                <option value="Agua potable">Agua potable</option>
                                <option value="Kit de emergencia para quimicos">Kit de emergencia para quimicos</option>
                                <option value="Bloqueo y etiquetado">Bloqueo y etiquetado</option>
                                <option value="Equipos a presion: Calderas, compresoras, hornos, bomba">Equipos a presion: Calderas, compresoras, hornos, bomba</option>
                                <option value="Simulacro">Simulacro</option>
                                <option value="Capacitaciones">Capacitaciones</option>
                                <option value="Transporte y vivienda">Transporte y vivienda</option>
                                <option value="Otros">Otros</option>
                            </select>
                        </div>

                        <!-- Descripción Detallada (Opcional) -->
                        <div class="form-field-group" style="margin-bottom: 0;">
                            <label class="form-field-label" for="edit_description">Descripción Detallada (Opcional)</label>
                            <textarea name="description" id="edit_description" class="custom-form-textarea" rows="3" disabled
                                placeholder="Detalles de la condición encontrada, hallazgos, estado técnico..."></textarea>
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
                                    <span style="font-size: 11.5px; color: #94a3b8;">No se registraron fotografías para este punto</span>
                                </div>
                            </div>

                            <!-- Miniaturas -->
                            <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                            <input type="file" name="photos[]" id="edit_images_input" multiple accept="image/*"
                                style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                            <div id="edit_remaining_images_container"></div>

                            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                                <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos"
                                    onclick="document.getElementById('edit_images_input').click()"
                                    style="display: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3">
                                        <path d="M5 12h14" />
                                        <path d="M12 5v14" />
                                    </svg>
                                    <span>+ Subir Fotos (Hasta 3)</span>
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
                                    <span>Eliminar Foto Actual</span>
                                </button>
                            </div>
                        </div>
                        <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar y deslizar todas las imágenes del punto en modo continuo.</span>
                    </div>

                    <!-- COLUMNA 3: Ubicación Geográfica & GPS -->
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

                        <div class="utm-coords-grid" style="display: grid; grid-template-columns: 1fr 1fr 75px; gap: 8px;">
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label" for="edit_utm_easting">Este (E)</label>
                                <input type="number" step="any" name="utm_easting" id="edit_utm_easting" class="custom-form-input"
                                    placeholder="585325" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label" for="edit_utm_northing">Norte (N)</label>
                                <input type="number" step="any" name="utm_northing" id="edit_utm_northing" class="custom-form-input"
                                    placeholder="8169231" disabled oninput="syncUtmToMap('edit')">
                            </div>
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label" for="edit_utm_zone">Zona (Z)</label>
                                <input type="text" name="utm_zone" id="edit_utm_zone" class="custom-form-input" placeholder="20K"
                                    value="20K" disabled oninput="syncUtmToMap('edit')">
                            </div>
                        </div>

                        <div class="utm-preview-pill" style="display: flex; align-items: center; gap: 6px; padding: 6px 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; font-size: 11.5px; font-family: monospace; color: #166534; margin-top: 4px;">
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
                            <label class="form-field-label" style="margin-bottom: 4px; margin-top: 6px;">Ubicación en Mapa</label>
                            <div id="edit_modal_map" class="modal-minimap-container" style="height: 190px;"></div>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeEditMeasurementModal()">Cerrar</button>
                <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn"
                    style="display: none;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>Guardar Cambios</span>
                </button>
            </div>
        </form>
    </div>
</div>
