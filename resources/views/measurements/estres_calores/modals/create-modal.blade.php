<div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 860px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 9px; background: #fff7ed; color: #ea580c; display: grid; place-items: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
                <div>
                    <h2 id="createModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: #0f172a;">Nuevo Punto de Medición — Estrés Térmico (Calor)</h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">Evaluación de Índice TGBH / WBGT, Gasto Metabólico y Límites TLV</span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()" aria-label="Cerrar">✕</button>
        </div>

        <form action="{{ route('modules.heat_stress.measurements.store', $module->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body-custom" style="padding: 20px 24px; max-height: 75vh; overflow-y: auto;">
                <!-- 1. Identificación y Entorno -->
                <div class="form-section-title">
                    <span>1. Identificación del Punto y Condiciones Operativas</span>
                </div>
                <div class="form-row-3col">
                    <div class="form-group-custom">
                        <label for="create_point_number">N° Punto <span class="text-danger">*</span></label>
                        <input type="number" id="create_point_number" name="point_number" value="{{ $totalMeasurements + 1 }}" required min="1" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_area">Área / Sección <span class="text-danger">*</span></label>
                        <input type="text" id="create_area" name="area" required placeholder="Ej: Fundición / Calderas / Planta" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_puesto_trabajo">Puesto / Tarea <span class="text-danger">*</span></label>
                        <input type="text" id="create_puesto_trabajo" name="puesto_trabajo" required placeholder="Ej: Operador de Horno" class="form-input-custom">
                    </div>
                </div>

                <div class="form-row-3col" style="margin-top: 10px;">
                    <div class="form-group-custom">
                        <label for="create_interior_exterior">Entorno / Carga Solar <span class="text-danger">*</span></label>
                        <select id="create_interior_exterior" name="interior_exterior" class="form-select-custom" onchange="recalcHeatStress('create')">
                            <option value="Interior" selected>Interior / Sin Carga Solar (0.7·Tbh + 0.3·Tg)</option>
                            <option value="Exterior">Exterior / Con Carga Solar (0.7·Tbh + 0.2·Tg + 0.1·Tbs)</option>
                        </select>
                    </div>
                    <div class="form-group-custom">
                        <label for="create_tasa_metabolica">Tasa Metabólica <span class="text-danger">*</span></label>
                        <select id="create_tasa_metabolica" name="tasa_metabolica" class="form-select-custom" onchange="recalcHeatStress('create')">
                            <option value="Clase 0 - En reposo">Clase 0 - En reposo (115 W)</option>
                            <option value="Clase 1 - Índice metabólico bajo">Clase 1 - Índice bajo (180 W)</option>
                            <option value="Clase 2 - Índice metabólico medio" selected>Clase 2 - Índice medio (300 W)</option>
                            <option value="Clase 3 - Índice metabólico alto">Clase 3 - Índice alto (415 W)</option>
                            <option value="Clase 4 - Índice metabólico muy alto">Clase 4 - Índice muy alto (520 W)</option>
                        </select>
                    </div>
                    <div class="form-group-custom">
                        <label for="create_tipo_ropa_cav">Vestimenta / Ajuste CAV</label>
                        <select id="create_tipo_ropa_cav" name="tipo_ropa_cav" class="form-select-custom" onchange="recalcHeatStress('create')">
                            <option value="Ropa de Trabajo" selected>Ropa de Trabajo convencional (+0 °C)</option>
                            <option value="Overol de tela normal">Overol de tela normal (+0 °C)</option>
                            <option value="Doble capa de tela">Overol doble capa (+3.0 °C)</option>
                            <option value="Poliolefina / Tyvek">Overol poliolefina / Tyvek (+1.0 °C)</option>
                            <option value="Delantal impermeable">Delantal impermeable (+1.5 °C)</option>
                            <option value="Barrera de vapor / Impermeable">Traje impermeable vapor (+10.0 °C)</option>
                            <option value="Traje impermeable con capucha de vapor">Traje impermeable con capucha (+11.0 °C)</option>
                        </select>
                    </div>
                </div>

                <div class="form-row-3col" style="margin-top: 10px;">
                    <div class="form-group-custom">
                        <label for="create_aclimatado">Estado de Aclimatación</label>
                        <select id="create_aclimatado" name="aclimatado" class="form-select-custom" onchange="recalcHeatStress('create')">
                            <option value="Sí" selected>Personal Aclimatado (LMP mayor)</option>
                            <option value="No">Personal No Aclimatado</option>
                        </select>
                    </div>
                    <div class="form-group-custom">
                        <label for="create_measurement_date">Fecha de Monitoreo</label>
                        <input type="date" id="create_measurement_date" name="measurement_date" value="{{ date('Y-m-d') }}" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_measurement_time">Hora de Medición</label>
                        <input type="time" id="create_measurement_time" name="measurement_time" value="{{ date('H:i') }}" class="form-input-custom">
                    </div>
                </div>

                <!-- 2. Registro de Temperaturas Termohigrométricas -->
                <div class="form-section-title" style="margin-top: 16px;">
                    <span>2. Registro Termohigrométrico y Sensores (°C)</span>
                </div>
                <div class="form-row-4col" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;">
                    <div class="form-group-custom">
                        <label for="create_temp_c">T. Bulbo Seco Tbs (°C) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" id="create_temp_c" name="temp_c" required placeholder="Ej: 32.5" class="form-input-custom" oninput="recalcHeatStress('create')">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_wb_c">T. Bulbo Húmedo Tbh (°C) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" id="create_wb_c" name="wb_c" required placeholder="Ej: 24.8" class="form-input-custom" oninput="recalcHeatStress('create')">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_gt_c">T. de Globo Tg (°C) <span class="text-danger">*</span></label>
                        <input type="number" step="0.1" id="create_gt_c" name="gt_c" required placeholder="Ej: 36.2" class="form-input-custom" oninput="recalcHeatStress('create')">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_wbgt_c">TGBH Directo (Opcional)</label>
                        <input type="number" step="0.1" id="create_wbgt_c" name="wbgt_c" placeholder="Auto-calculado" class="form-input-custom" oninput="recalcHeatStress('create')">
                    </div>
                </div>

                <div class="form-row-3col" style="margin-top: 10px;">
                    <div class="form-group-custom">
                        <label for="create_hr_percent">Humedad Relativa (%)</label>
                        <input type="number" step="0.1" min="0" max="100" id="create_hr_percent" name="hr_percent" placeholder="Ej: 55.0" class="form-input-custom">
                    </div>
                    <div class="form-group-custom">
                        <label for="create_vel_viento_ms">Velocidad de Viento (m/s)</label>
                        <input type="number" step="0.01" id="create_vel_viento_ms" name="vel_viento_ms" placeholder="Ej: 0.50" class="form-input-custom">
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

                <!-- 3. Previsualización de Cálculos en Vivo -->
                <div class="calculation-preview-box" style="margin-top: 16px; background: #fff7ed; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 800; color: #ea580c; text-transform: uppercase;">Resultados Calculados TGBH / Límites ACGIH</span>
                        <span id="create_cumple_badge"><span class="badge-compliance-ok"><span>PENDIENTE</span></span></span>
                    </div>
                    <div class="stats-mini-row" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; text-align: center;">
                        <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #ffedd5;">
                            <span style="font-size: 11px; color: #64748b; display: block;">TGBH Medido</span>
                            <strong id="create_wbgt_display" style="font-size: 15px; color: #0f172a;">— °C</strong>
                        </div>
                        <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #ffedd5;">
                            <span style="font-size: 11px; color: #64748b; display: block;">TGBH Efectivo (+CAV)</span>
                            <strong id="create_wbgt_efectivo_display" style="font-size: 15px; color: #ea580c;">— °C</strong>
                        </div>
                        <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #ffedd5;">
                            <span style="font-size: 11px; color: #64748b; display: block;">Límite TLV</span>
                            <strong id="create_lmp_display" style="font-size: 15px; color: #0f172a;">28.0 °C</strong>
                        </div>
                        <div style="background: #fff; padding: 8px; border-radius: 8px; border: 1px solid #ffedd5;">
                            <span style="font-size: 11px; color: #64748b; display: block;">Régimen de Ciclo</span>
                            <strong id="create_regimen_display" style="font-size: 12px; color: #0f172a;">100% Trabajo / Continuo</strong>
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
                    <textarea id="create_desc_actividades" name="desc_actividades" rows="2" class="form-input-custom" placeholder="Detalle de tareas, esfuerzo físico, ciclos de operación..."></textarea>
                </div>
                <div class="form-group-custom" style="margin-top: 10px;">
                    <label for="create_observations">Observaciones / Medidas de Control</label>
                    <textarea id="create_observations" name="observations" rows="2" class="form-input-custom" placeholder="Fuentes térmicas, ventilación, hidratación de trabajadores..."></textarea>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action" style="background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);">
                    <span>Guardar Medición</span>
                </button>
            </div>
        </form>
    </div>
</div>
