    <!-- ==========================================================================
             MODAL 2: DETALLE / EDICIÓN DE SECTOR DE CARGA DE FUEGO POR ACTIVIDAD
             (MODO CONSULTA CON BOTÓN EDITAR - IDÉNTICO A ILUMINACIÓN)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="editMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg" style="max-width: 1240px; width: 96%;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z"/>
                    </svg>
                    <h2 id="editMeasModalTitle">Detalle del Sector de Carga de Fuego</h2>
                    <span id="modalModeStatusBadge" class="modal-badge-view">Solo Lectura</span>
                    <!-- Evaluador Registrado -->
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9999px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; color: #334155;"
                        title="Técnico Evaluador">
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
                <div class="modal-body-custom" style="max-height: calc(100vh - 170px); overflow-y: auto;">
                    
                    {{-- 1. GRID PRINCIPAL SUPERIOR DE 3 COLUMNAS --}}
                    <div class="modal-three-cols-grid" style="margin-bottom: 20px;">

                        <!-- COLUMNA 1: Datos Técnicos del Sector y Dimensiones -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Sector & Dimensiones</span>
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

                            <!-- Técnico de Campo / Evaluador -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_staff_id">
                                    <span>Técnico Evaluador <span class="req">*</span></span>
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

                            <!-- Macroárea / Edificio -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_macroarea">
                                    <span>Macroárea / Edificio <span class="req">*</span></span>
                                </label>
                                <input type="text" name="macroarea" id="edit_macroarea" class="custom-form-input" required disabled>
                            </div>

                            <!-- Nombre del Sector / Área de Incendio -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_sector_name">
                                    <span>Nombre del Sector <span class="req">*</span></span>
                                </label>
                                <input type="text" name="sector_name" id="edit_sector_name" class="custom-form-input" required disabled>
                            </div>

                            <!-- Dimensiones Geométricas del Sector -->
                            <div class="form-field-group" style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 10px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                                    <span style="font-size: 11px; font-weight: 800; color: #334155; text-transform: uppercase;">Dimensiones del Sector</span>
                                    <span id="edit_calc_area_badge" style="font-size: 11.5px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 5px;">0.00 m²</span>
                                </div>
                                <div class="form-grid-two-cols">
                                    <div>
                                        <label class="form-field-label" style="font-size: 11px;">Yi - Largo (m) <span class="req">*</span></label>
                                        <input type="number" step="0.01" min="0.1" name="yi_largo" id="edit_yi_largo"
                                            class="custom-form-input" placeholder="0.00" required disabled oninput="recalcSectorArea('edit')">
                                    </div>
                                    <div>
                                        <label class="form-field-label" style="font-size: 11px;">Xi - Ancho (m) <span class="req">*</span></label>
                                        <input type="number" step="0.01" min="0.1" name="xi_ancho" id="edit_xi_ancho"
                                            class="custom-form-input" placeholder="0.00" required disabled oninput="recalcSectorArea('edit')">
                                    </div>
                                </div>
                            </div>

                            <!-- Resumen de Carga de Fuego Qs y Nivel de Riesgo -->
                            <div class="readings-summary-strip" id="edit_fire_summary_strip" style="margin-top: 4px; padding: 8px 12px; border-radius: 8px;">
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: #64748b;">Densidad de Carga de Fuego Qs</span>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <strong id="edit_qs_mj_disp" style="font-size: 14px; font-weight: 800; color: var(--ink);">0.0 MJ/m²</strong>
                                        <span style="font-size: 11.5px; color: #64748b; font-family: monospace;" id="edit_qs_mcal_disp">(0.0 Mcal/m²)</span>
                                    </div>
                                </div>
                                <div>
                                    <span id="edit_risk_summary_badge" class="fire-risk-badge emerald" style="font-size: 11px; padding: 4px 10px;">
                                        Riesgo Bajo
                                    </span>
                                </div>
                            </div>

                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico TIPO SLIDE / CARRUSEL (Idéntico a Iluminación) -->
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

                                    <img id="edit_slider_img" class="modal-slider-main-img" src="" alt="Foto sector"
                                        style="display: none; cursor: pointer;" onclick="expandCurrentModalPhoto('edit')">

                                    <div id="edit_slider_placeholder" class="modal-slider-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                            style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografía</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">No se registraron imágenes para este sector</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*"
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
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">
                                Visualiza las evidencias fotográficas del sector de incendio en carrusel navegable continuo.
                            </span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & GPS + Mini Mapa + Observaciones -->
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

                            <!-- Mini Mapa interactivo: Dónde está ubicado -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="edit_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones: Textarea directamente debajo del mapa -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones y Recomendaciones</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3"
                                    disabled
                                    placeholder="Observaciones de ventilación, fuentes de ignición, estado de extintores..."></textarea>
                            </div>
                        </div>

                    </div>

                    {{-- 2. SECCIÓN INFERIOR DUAL: ACTIVIDADES NORMATIVAS Y EQUIPOS CONTRA INCENDIOS --}}
                    <div style="display: grid; grid-template-columns: 1.35fr 1fr; gap: 20px; border-top: 1.5px solid #e2e8f0; padding-top: 18px;">
                        
                        <!-- BLOQUE A: Actividades Normativas Registradas en este Sector -->
                        <div class="modal-col-card" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 14px;">
                            <div class="modal-col-heading" style="margin-bottom: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2">
                                        <rect width="7" height="9" x="3" y="3" rx="1"/>
                                        <rect width="7" height="5" x="14" y="3" rx="1"/>
                                        <rect width="7" height="9" x="14" y="12" rx="1"/>
                                        <rect width="7" height="5" x="3" y="16" rx="1"/>
                                    </svg>
                                    <span style="color: var(--ink);">Actividades Normativas del Sector</span>
                                </div>
                                <button type="button" class="btn-secondary-subtle" id="edit_btn_add_activity" onclick="addActivityRow('edit')"
                                    style="display: none; padding: 4px 10px; font-size: 11.5px; border-radius: 6px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>+ Añadir Actividad</span>
                                </button>
                            </div>
                            <p style="font-size: 11.5px; color: #64748b; margin: 0 0 10px 0;">
                                Clasificación de almacenamiento / producción, dimensiones y coeficientes normativos (NB 58005):
                            </p>

                            <!-- Contenedor dinámico de actividades -->
                            <div id="edit_activities_container" style="display: flex; flex-direction: column; gap: 10px; max-height: 420px; overflow-y: auto; padding-right: 2px;">
                                <!-- Poblado dinámicamente con las actividades de la app -->
                            </div>
                        </div>

                        <!-- BLOQUE B: Equipos de Protección y Extinción Contra Incendios -->
                        <div class="modal-col-card" style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 14px;">
                            <div class="modal-col-heading" style="margin-bottom: 4px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2">
                                        <path d="M15 6v14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V6"/>
                                        <path d="M7 6h8"/><path d="M11 2v4"/><path d="M14 4h4"/>
                                    </svg>
                                    <span style="color: var(--ink);">Equipos Contra Incendios</span>
                                </div>
                                <button type="button" class="btn-secondary-subtle" id="edit_btn_add_equipment" onclick="addFireEquipmentRow('edit')"
                                    style="display: none; padding: 4px 10px; font-size: 11.5px; border-radius: 6px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>+ Equipo</span>
                                </button>
                            </div>
                            <p style="font-size: 11.5px; color: #64748b; margin: 0 0 10px 0;">
                                Inventario de extintores, pulsadores, detectores e hidrantes asignados al sector:
                            </p>

                            <!-- Contenedor dinámico de equipos -->
                            <div id="edit_fire_equipments_container" style="display: flex; flex-direction: column; gap: 8px; max-height: 420px; overflow-y: auto; padding-right: 2px;">
                                <!-- Poblado dinámicamente con los equipos de la app -->
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

