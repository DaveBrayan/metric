<div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 860px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
                <div>
                    <h2 id="createModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: #0f172a;">Nuevo Punto de Medición — Estrés Térmico (Frío)</h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">Evaluación de Sensación Térmica, Índice WCI y Tiempos Límite (TLE)</span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()" aria-label="Cerrar">✕</button>
        </div>

        <form action="{{ route('modules.cold_stress.measurements.store', $module->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body-custom" style="padding: 20px 24px; max-height: 75vh; overflow-y: auto;">
                <!-- 1. Identificación y Ubicación -->
                <div class="form-section-title">
                    <span>1. Identificación del Punto y Entorno</span>
                </div>
                <div class="form-row-3col">
                    <div class="form-group-custom">
                        <label for="create_point_number">N° Punto <span class="text-danger">*</span></label>
                        <input type="number" id="create_point_number" name="point_number" value="{{ $totalMeasurements + 1 }}" required min="1" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_area">Área / Sección <span class="text-danger">*</span></label>
                        <input type="text" id="create_area" name="area" required placeholder="Ej: Cámara Frigorífica 01 / Almacén Frío" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_puesto_trabajo">Puesto / Tarea <span class="text-danger">*</span></label>
                        <input type="text" id="create_puesto_trabajo" name="puesto_trabajo" required placeholder="Ej: Operador de Carga y Estiba" class="form-input-custom">
                    </div>
                </div>

                <div class="form-row-3col" style="margin-top: 10px;">
                    <div class="form-group-custom">
                        <label for="create_metabolismo">Actividad Metabólica <span class="text-danger">*</span></label>
                        <select id="create_metabolismo" name="metabolismo" class="form-select-custom" onchange="recalcColdStress('create')">
                            <option value="Metabolismo ligero (115 W)">Ligero (115 W - Inspección/Escritorio)</option>
                            <option value="Metabolismo moderado (200 W)" selected>Moderado (200 W - Trabajo manual/Caminata)</option>
                            <option value="Metabolismo alto (300 W)">Alto (300 W - Carga pesada/Esfuerzo)</option>
                            <option value="Metabolismo muy alto (400 W)">Muy Alto (400 W - Trabajo continuo intenso)</option>
                        </select>
                    </div>
                    <div class="form-group-custom">
                        <label for="create_aislamiento">Aislamiento Vestimenta (clo)</label>
                        <select id="create_aislamiento" name="aislamiento" class="form-select-custom" onchange="recalcColdStress('create')">
                            <option value="Vestimenta estándar (1.0 clo)">Ropa normal de trabajo (1.0 clo)</option>
                            <option value="Ropa térmica ligera (1.5 clo)">Térmica ligera + guantes (1.5 clo)</option>
                            <option value="Traje frigorífico completo (2.5 clo)" selected>Traje térmico frigorífico (2.5 clo)</option>
                            <option value="Traje polar extremo (3.5 clo)">Polar extremo con capucha (3.5 clo)</option>
                        </select>
                    </div>
                    <div class="form-group-custom">
                        <label for="create_staff_id">Personal Registrador</label>
                        <select id="create_staff_id" name="staff_id" class="form-select-custom">
                            @foreach($staffList as $st)
                                <option value="{{ $st->id }}">{{ $st->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-row-2col" style="margin-top: 10px;">
                    <div class="form-group-custom">
                        <label for="create_measurement_date">Fecha de Monitoreo</label>
                        <input type="date" id="create_measurement_date" name="measurement_date" value="{{ date('Y-m-d') }}" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_measurement_time">Hora de Medición</label>
                        <input type="time" id="create_measurement_time" name="measurement_time" value="{{ date('H:i') }}" class="form-input-custom">
                    </div>
                </div>

                <!-- 2. Parámetros Termo-Anemométricos -->
                <div class="form-section-title" style="margin-top: 16px;">
                    <span>2. Parámetros Ambientales de Medición</span>
                </div>
                <div class="form-row-4col" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <div class="form-group-custom">
                        <label for="create_temp_c">Temperatura Aire Ta (°C) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" id="create_temp_c" name="temp_c" required placeholder="Ej: -5.0" class="form-input-custom" oninput="recalcColdStress('create')">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_vel_viento_ms">Velocidad Viento (m/s)</label>
                        <input type="number" step="0.01" min="0" id="create_vel_viento_ms" name="vel_viento_ms" placeholder="Ej: 0.50" class="form-input-custom" oninput="recalcColdStress('create')">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_hr_percent">Humedad Relativa (%)</label>
                        <input type="number" step="0.1" min="0" max="100" id="create_hr_percent" name="hr_percent" placeholder="Ej: 65.0" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_presion_mmhg">Presión (mmHg)</label>
                        <input type="number" step="0.1" id="create_presion_mmhg" name="presion_mmhg" placeholder="Ej: 495.0" class="form-input-custom">
                    </div>
                </div>

                <!-- 3. Previsualización de Cálculos en Vivo -->
                <div class="calculation-preview-box" style="margin-top: 16px; background: #f0f9ff; border: 1.5px solid #bae6fd; border-radius: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 800; color: #0369a1; text-transform: uppercase;">Resultados Calculados en Vivo (WCI / TLE)</span>
                        <span id="create_riesgo_badge"><span class="table-compliance-badge badge-cumple"><span>PENDIENTE</span></span></span>
                    </div>
                    <div class="stats-mini-row" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; text-align: center;">
                        <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #e0f2fe;">
                            <span style="font-size: 11px; color: #64748b; display: block;">Sensación Térmica</span>
                            <strong id="create_sensacion_display" style="font-size: 15px; color: #0284c7;">— °C</strong>
                        </div>
                        <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #e0f2fe;">
                            <span style="font-size: 11px; color: #64748b; display: block;">Índice WCI</span>
                            <strong id="create_wci_display" style="font-size: 15px; color: #0f172a;">—</strong>
                        </div>
                        <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #e0f2fe;">
                            <span style="font-size: 11px; color: #64748b; display: block;">Régimen TLE Máximo</span>
                            <strong id="create_tle_display" style="font-size: 12px; color: #0f172a;">Jornada normal con EPP</strong>
                        </div>
                    </div>
                </div>

                <!-- 4. Georreferenciación & Fotos -->
                <div class="form-section-title" style="margin-top: 16px;">
                    <span>3. Georreferenciación y Evidencias Fotográficas</span>
                </div>
                <div class="form-row-2col">
                    <div class="form-group-custom">
                        <label>Coordenadas GPS (WGS84)</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="number" step="0.0000001" id="create_latitude" name="latitude" placeholder="Latitud (-16.xxx)" class="form-input-custom">
                            <input type="number" step="0.0000001" id="create_longitude" name="longitude" placeholder="Longitud (-68.xxx)" class="form-input-custom">
                            <button type="button" class="btn-secondary-subtle" onclick="captureCoordinatesGPS('create')" title="Capturar GPS satelital" style="flex-shrink: 0; padding: 0 12px;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2">
                                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group-custom">
                        <label for="create_photos">Fotografías del Punto</label>
                        <input type="file" id="create_photos" name="photos[]" multiple accept="image/*" class="form-input-custom">
                    </div>
                </div>

                <div class="form-row-3col" style="margin-top: 10px;">
                    <div class="form-group-custom">
                        <label for="create_utm_zone">Zona UTM</label>
                        <input type="text" id="create_utm_zone" name="utm_zone" value="19K" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_utm_easting">UTM Este (X)</label>
                        <input type="number" step="0.1" id="create_utm_easting" name="utm_easting" placeholder="Ej: 593214.2" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_utm_northing">UTM Norte (Y)</label>
                        <input type="number" step="0.1" id="create_utm_northing" name="utm_northing" placeholder="Ej: 8175432.8" class="form-input-custom">
                    </div>
                </div>

                <!-- 5. Observaciones y Descripción de Actividad -->
                <div class="form-section-title" style="margin-top: 16px;">
                    <span>4. Descripción de Actividad y Observaciones Técnicas</span>
                </div>
                <div class="form-group-custom">
                    <label for="create_desc_actividades">Descripción de Actividades en el Puesto</label>
                    <textarea id="create_desc_actividades" name="desc_actividades" rows="2" class="form-input-custom" placeholder="Detalle de tareas en frío, manipulación de productos congelados..."></textarea>
                </div>
                <div class="form-group-custom" style="margin-top: 10px;">
                    <label for="create_observations">Observaciones / EPP Térmico</label>
                    <textarea id="create_observations" name="observations" rows="2" class="form-input-custom" placeholder="Estado de guantes térmicos, calzado aislante, rotación y salas de calentamiento..."></textarea>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                    <span>Guardar Medición</span>
                </button>
            </div>
        </form>
    </div>
</div>
