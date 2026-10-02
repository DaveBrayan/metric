    <!-- ==========================================================================
             MODAL 1: NUEVO SECTOR DE CARGA DE FUEGO POR ACTIVIDAD (3 COLUMNAS)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true"
        aria-labelledby="createMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg" style="max-width: 1100px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 34px; height: 34px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="createMeasModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: var(--ink);">
                            Nuevo Sector — Carga de Fuego por Actividad
                        </h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                            Registro de dimensiones, actividades normativas (NB 58005) y equipos contra incendios
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()" aria-label="Cerrar">✕</button>
            </div>

            <form action="{{ route('modules.fire_activity.measurements.store', $module->id ?? 1) }}" method="POST"
                enctype="multipart/form-data" id="createMeasurementForm">
                @csrf
                <div class="modal-body-custom" style="max-height: 75vh; overflow-y: auto;">
                    <div class="modal-three-cols-grid" style="grid-template-columns: 1fr 1.15fr 1fr;">

                        <!-- COLUMNA 1: Datos del Sector & Dimensiones -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Sector</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong
                                        id="create_pt_num_disp">{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="create_point_number"
                                value="{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}">

                            <!-- Fecha y Hora -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_date">Fecha <span class="req">*</span></label>
                                    <input type="date" name="measurement_date" id="create_measurement_date"
                                        class="custom-form-input" required value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_time">Hora</label>
                                    <input type="time" name="measurement_time" id="create_measurement_time"
                                        class="custom-form-input" value="{{ date('H:i') }}">
                                </div>
                            </div>

                            <!-- Técnico de Campo / Evaluador -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_staff_id">
                                    <span>Técnico Evaluador <span class="req">*</span></span>
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

                            <!-- Macroárea -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_macroarea">
                                    <span>Macroárea / Edificio <span class="req">*</span></span>
                                </label>
                                <input type="text" name="macroarea" id="create_macroarea" class="custom-form-input"
                                    required placeholder="Ej: Galpón Principal de Producción">
                            </div>

                            <!-- Nombre del Sector / Área de Incendio -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_sector_name">
                                    <span>Nombre del Sector <span class="req">*</span></span>
                                </label>
                                <input type="text" name="sector_name" id="create_sector_name" class="custom-form-input"
                                    required placeholder="Ej: Sector A1 - Almacén de Insumos">
                            </div>

                            <!-- Dimensiones del Sector (Yi Largo x Xi Ancho -> Superficie S) -->
                            <div class="form-field-group" style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 12px;">
                                <div style="font-size: 11.5px; font-weight: 800; color: #334155; text-transform: uppercase; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Dimensiones del Sector</span>
                                    <span id="create_calc_area_badge" style="font-size: 12px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px;">0.00 m²</span>
                                </div>
                                <div class="form-grid-two-cols">
                                    <div>
                                        <label class="form-field-label" style="font-size: 11px;">Yi - Largo (m) <span class="req">*</span></label>
                                        <input type="number" step="0.01" min="0.1" name="yi_largo" id="create_yi_largo"
                                            class="custom-form-input tech-val-mono" placeholder="0.00" required oninput="recalcSectorArea('create')">
                                    </div>
                                    <div>
                                        <label class="form-field-label" style="font-size: 11px;">Xi - Ancho (m) <span class="req">*</span></label>
                                        <input type="number" step="0.01" min="0.1" name="xi_ancho" id="create_xi_ancho"
                                            class="custom-form-input tech-val-mono" placeholder="0.00" required oninput="recalcSectorArea('create')">
                                    </div>
                                </div>
                            </div>

                            <!-- Coordenadas GPS / UTM -->
                            <div class="form-field-group">
                                <label class="form-field-label">Coordenadas UTM / GPS</label>
                                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 6px;">
                                    <input type="text" name="utm_zone" id="create_utm_zone" class="custom-form-input" placeholder="Zona (20K)" value="20K">
                                    <input type="number" step="0.01" name="utm_easting" id="create_utm_easting" class="custom-form-input" placeholder="Este (X)">
                                    <input type="number" step="0.01" name="utm_northing" id="create_utm_northing" class="custom-form-input" placeholder="Norte (Y)">
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA 2: Actividades Normativas dentro del Sector -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading" style="justify-content: space-between;">
                                <span>2. Actividades del Sector</span>
                                <button type="button" class="btn-secondary-subtle" onclick="addActivityRow('create')"
                                    style="padding: 4px 10px; font-size: 11.5px; border-radius: 6px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>+ Añadir Actividad</span>
                                </button>
                            </div>

                            <p style="font-size: 11.5px; color: #64748b; margin-top: -6px; margin-bottom: 10px;">
                                Selecciona una o varias actividades normativas de almacenamiento o producción para el sector:
                            </p>

                            <!-- Contenedor dinámico de actividades -->
                            <div id="create_activities_container" style="display: flex; flex-direction: column; gap: 10px; max-height: 480px; overflow-y: auto; padding-right: 2px;">
                                <!-- Fila de Actividad Inicial (Generada por JS o template) -->
                            </div>
                        </div>

                        <!-- COLUMNA 3: Equipos Contra Incendio, Fotos y Observaciones -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading" style="justify-content: space-between;">
                                <span>3. Equipos & Evidencias</span>
                                <button type="button" class="btn-secondary-subtle" onclick="addFireEquipmentRow('create')"
                                    style="padding: 4px 10px; font-size: 11.5px; border-radius: 6px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    <span>+ Equipo</span>
                                </button>
                            </div>

                            <!-- Equipos Contra Incendios Dinámicos -->
                            <div style="margin-bottom: 12px;">
                                <label class="form-field-label" style="font-size: 12px; margin-bottom: 6px;">
                                    Equipos Contra Incendios en este Sector:
                                </label>
                                <div id="create_fire_equipments_container" style="display: flex; flex-direction: column; gap: 8px; max-height: 180px; overflow-y: auto;">
                                    <!-- Filas de equipos generadas dinámicamente -->
                                </div>
                            </div>

                            <!-- Evidencias Fotográficas -->
                            <div class="form-field-group">
                                <label class="form-field-label">Evidencias Fotográficas del Sector</label>
                                <div class="photo-dropzone-box" onclick="document.getElementById('create_photos_input').click()">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="1.8">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                        <circle cx="9" cy="9" r="2"/>
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                    </svg>
                                    <span style="font-size: 12px; font-weight: 700; color: #475569; margin-top: 4px;">Haz clic o arrastra fotografías aquí</span>
                                    <span style="font-size: 10.5px; color: #94a3b8;">Formatos JPG, PNG (Hasta 10 MB por archivo)</span>
                                </div>
                                <input type="file" name="photos[]" id="create_photos_input" multiple accept="image/*" style="display: none;" onchange="handlePhotoUploadPreview(this, 'create_photo_preview_container')">
                                <div id="create_photo_preview_container" style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px;"></div>
                            </div>

                            <!-- Observaciones Generales -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label" for="create_observations">Observaciones y Recomendaciones</label>
                                <textarea name="observations" id="create_observations" class="custom-form-input" rows="3"
                                    placeholder="Detalles sobre ventilación, estado de extintores, señalética, orden y limpieza..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Footer del Modal -->
                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeCreateMeasurementModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Guardar Sector</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
