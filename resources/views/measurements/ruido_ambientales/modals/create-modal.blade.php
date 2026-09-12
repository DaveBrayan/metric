<div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal-dialog-ruido">
        <div class="modal-header-custom">
            <div>
                <h2 id="createModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 19px; font-weight: 800; color: var(--ink); margin: 0 0 3px 0;">
                    Nuevo Punto de Medición — Ruido Ambiental
                </h2>
                <p style="font-size: 13px; color: #64748b; margin: 0;">
                    Registro perimetral en linderos (Norte, Sur, Este, Oeste), límites normativos y acústica Leq.
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()" aria-label="Cerrar">✕</button>
        </div>

        <form action="{{ route('modules.ruido_ambiental.measurements.store', $module->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body-custom">
                <!-- CARD 1: NORMATIVA, TIPO DE ZONA, HORARIO Y LÍMITE -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>1. Normativa y Límites Máximos Permisibles (LMP)</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Normativa Aplicable <span class="req">*</span></label>
                            <select name="normativa" id="create_normativa" class="custom-form-select" required onchange="handleNormativaChange('create')">
                                <option value="">Seleccione normativa...</option>
                                <option value="RASIM - ANEXO 12-C">RASIM - ANEXO 12-C</option>
                                <option value="RMCA - ANEXO 6">RMCA - ANEXO 6</option>
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Tipo de Zona <span class="req">*</span></label>
                            <select name="tipo_zona" id="create_tipo_zona" class="custom-form-select" required onchange="handleTipoZonaChange('create')">
                                <option value="">Primero seleccione normativa...</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Horario de Medición</label>
                            <input type="text" name="horario" id="create_horario" class="custom-form-input" placeholder="ej. 08:00 a 22:00">
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Límite según Normativa (LMP dBA) <span class="req">*</span></label>
                            <input type="number" step="0.1" name="limite_normativa" id="create_limite_normativa" class="custom-form-input" required placeholder="ej. 68.0" oninput="recalcRuidoAmbiental('create')">
                        </div>
                    </div>
                </div>

                <!-- CARD 2: ZONA Y BANDA + COORDENADAS UTM (N, S, E, O) CON BOTÓN GPS -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                        <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>2. Georreferenciación & Colindancias Perimetrales</span>
                        </div>
                        <button type="button" class="btn-secondary-subtle" onclick="captureCoordinatesGPS('create')" style="padding: 7px 16px; font-size: 12.5px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                            <span>Capturar Coordenadas GPS</span>
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Zona y Banda</label>
                            <input type="text" name="zona_banda" id="create_zona_banda" class="custom-form-input" value="19K">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Fecha de Medición</label>
                            <input type="date" name="measurement_date" id="create_measurement_date" class="custom-form-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Hora General</label>
                            <input type="time" name="measurement_time" id="create_measurement_time" class="custom-form-input" value="{{ date('H:i') }}">
                        </div>
                    </div>

                    <!-- Grilla de Colindancias N, S, E, O -->
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Norte -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 12px;">
                            <input type="text" name="norte_colindancia" id="create_norte_colindancia" class="custom-form-input" placeholder="Colindancia Norte (ej. Calle / Vía Principal)">
                            <input type="text" name="norte_x" id="create_norte_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="norte_y" id="create_norte_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                        <!-- Sur -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 12px;">
                            <input type="text" name="sur_colindancia" id="create_sur_colindancia" class="custom-form-input" placeholder="Colindancia Sur (ej. Lote / Terreno Vecino)">
                            <input type="text" name="sur_x" id="create_sur_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="sur_y" id="create_sur_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                        <!-- Este -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 12px;">
                            <input type="text" name="este_colindancia" id="create_este_colindancia" class="custom-form-input" placeholder="Colindancia Este (ej. Terreno Abierto)">
                            <input type="text" name="este_x" id="create_este_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="este_y" id="create_este_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                        <!-- Oeste -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 12px;">
                            <input type="text" name="oeste_colindancia" id="create_oeste_colindancia" class="custom-form-input" placeholder="Colindancia Oeste (ej. Instalaciones Industriales)">
                            <input type="text" name="oeste_x" id="create_oeste_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="oeste_y" id="create_oeste_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                    </div>
                </div>

                <!-- CARD 3: PUNTOS DE MEDICIÓN SEPARADOS (P1-NORTE, P2-SUR, P3-ESTE, P4-OESTE) -->
                <div style="margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M2 10v4"/><path d="M6 6v12"/><path d="M10 3v18"/><path d="M14 7v10"/><path d="M18 5v14"/><path d="M22 10v4"/></svg>
                        <span>3. Puntos Cardinales & Mediciones Sonoras (dBA)</span>
                    </div>

                    <!-- P1 - NORTE -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P1 — NORTE</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('create', 'p1_norte')" title="Agregar medición">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p1_norte_inicio" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p1_norte_fin" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="create_p1_norte_points_container">
                                    <div class="point-pill-input-row">
                                        <input type="number" step="0.1" class="custom-form-input cardinal-point-val" placeholder="ej. 63.2" oninput="recalcRuidoAmbiental('create')">
                                    </div>
                                </div>
                                <input type="hidden" name="p1_norte_puntos" id="create_p1_norte_puntos_json">
                            </div>
                        </div>
                    </div>

                    <!-- P2 - SUR -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P2 — SUR</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('create', 'p2_sur')" title="Agregar medición">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p2_sur_inicio" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p2_sur_fin" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="create_p2_sur_points_container">
                                    <div class="point-pill-input-row">
                                        <input type="number" step="0.1" class="custom-form-input cardinal-point-val" placeholder="ej. 59.8" oninput="recalcRuidoAmbiental('create')">
                                    </div>
                                </div>
                                <input type="hidden" name="p2_sur_puntos" id="create_p2_sur_puntos_json">
                            </div>
                        </div>
                    </div>

                    <!-- P3 - ESTE -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P3 — ESTE</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('create', 'p3_este')" title="Agregar medición">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p3_este_inicio" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p3_este_fin" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="create_p3_este_points_container">
                                    <div class="point-pill-input-row">
                                        <input type="number" step="0.1" class="custom-form-input cardinal-point-val" placeholder="ej. 61.4" oninput="recalcRuidoAmbiental('create')">
                                    </div>
                                </div>
                                <input type="hidden" name="p3_este_puntos" id="create_p3_este_puntos_json">
                            </div>
                        </div>
                    </div>

                    <!-- P4 - OESTE -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P4 — OESTE</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('create', 'p4_oeste')" title="Agregar medición">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p4_oeste_inicio" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p4_oeste_fin" class="custom-form-input">
                            </div>
                            <div class="form-field-group">
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="create_p4_oeste_points_container">
                                    <div class="point-pill-input-row">
                                        <input type="number" step="0.1" class="custom-form-input cardinal-point-val" placeholder="ej. 64.0" oninput="recalcRuidoAmbiental('create')">
                                    </div>
                                </div>
                                <input type="hidden" name="p4_oeste_puntos" id="create_p4_oeste_puntos_json">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: FOTOS Y OBSERVACIONES -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-field-group">
                        <label class="form-field-label">Evidencia Fotográfica</label>
                        <input type="file" name="photos[]" multiple accept="image/*" class="custom-form-input" style="padding: 8px;">
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Personal Registrador</label>
                        <select name="staff_id" class="custom-form-select">
                            @foreach($staffList as $stf)
                                <option value="{{ $stf->id }}">{{ $stf->name }} ({{ $stf->position ?? 'Técnico' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-field-group">
                    <label class="form-field-label">Observaciones Perimetrales</label>
                    <textarea name="observations" rows="2" class="custom-form-input" placeholder="Detalles de fuentes emisoras, tráfico vehicular, condiciones de viento..."></textarea>
                </div>

                <!-- Resumen Acústico en Vivo -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1.5px solid #bbf7d0; padding: 14px 18px; border-radius: 12px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 800; color: #16a34a; text-transform: uppercase; letter-spacing: 0.5px;">Resumen Leq Estimado</div>
                        <div style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 800; color: #065f46;" id="create_leq_display">— dBA</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11.5px; font-weight: 700; color: #64748b;">LMP: <strong id="create_lmp_display">68.0 dBA</strong></div>
                        <div style="margin-top: 4px;" id="create_cumple_badge">
                            <span class="badge-compliance-ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>CUMPLE</span></span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="create_latitude">
                <input type="hidden" name="longitude" id="create_longitude">
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action">Guardar</button>
            </div>
        </form>
    </div>
</div>
