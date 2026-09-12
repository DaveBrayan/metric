<div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-dialog-ruido">
        <div class="modal-header-custom">
            <div>
                <h2 id="editModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); margin: 0 0 2px 0;">
                    Editar Punto de Medición — Ruido Ambiental
                </h2>
                <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                    Actualización de mediciones perimetrales, linderos y parámetros acústicos Leq.
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeEditMeasurementModal()" aria-label="Cerrar">✕</button>
        </div>

        <form id="editMeasurementForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body-custom">
                <!-- CARD 1: NORMATIVA, TIPO DE ZONA, HORARIO Y LÍMITE -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; margin-bottom: 18px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>1. Normativa y Límites Máximos Permisibles (LMP)</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Normativa Aplicable <span class="req">*</span></label>
                            <select name="normativa" id="edit_normativa" class="custom-form-select" required onchange="handleNormativaChange('edit')">
                                <option value="">Seleccione normativa...</option>
                                <option value="RASIM - ANEXO 12-C">RASIM - ANEXO 12-C</option>
                                <option value="RMCA - ANEXO 6">RMCA - ANEXO 6</option>
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Tipo de Zona <span class="req">*</span></label>
                            <select name="tipo_zona" id="edit_tipo_zona" class="custom-form-select" required onchange="handleTipoZonaChange('edit')">
                                <option value="">Primero seleccione normativa...</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Horario de Medición</label>
                            <input type="text" name="horario" id="edit_horario" class="custom-form-input" placeholder="ej. 08:00 a 22:00">
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Límite según Normativa (LMP dBA) <span class="req">*</span></label>
                            <input type="number" step="0.1" name="limite_normativa" id="edit_limite_normativa" class="custom-form-input" required placeholder="ej. 68.0" oninput="recalcRuidoAmbiental('edit')">
                        </div>
                    </div>
                </div>

                <!-- CARD 2: ZONA Y BANDA + COORDENADAS UTM (N, S, E, O) CON BOTÓN GPS -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; margin-bottom: 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                        <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; display: flex; align-items: center; gap: 6px;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            <span>2. Georreferenciación & Colindancias Perimetrales</span>
                        </div>
                        <button type="button" class="btn-secondary-subtle" onclick="captureCoordinatesGPS('edit')" style="padding: 6px 14px; font-size: 12px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>
                            <span>Capturar Coordenadas GPS</span>
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Zona y Banda</label>
                            <input type="text" name="zona_banda" id="edit_zona_banda" class="custom-form-input">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Fecha de Medición</label>
                            <input type="date" name="measurement_date" id="edit_measurement_date" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Hora General</label>
                            <input type="time" name="measurement_time" id="edit_measurement_time" class="custom-form-input">
                        </div>
                    </div>

                    <!-- Grilla de Colindancias N, S, E, O -->
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <!-- Norte -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 10px;">
                            <input type="text" name="norte_colindancia" id="edit_norte_colindancia" class="custom-form-input" placeholder="Colindancia Norte">
                            <input type="text" name="norte_x" id="edit_norte_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="norte_y" id="edit_norte_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                        <!-- Sur -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 10px;">
                            <input type="text" name="sur_colindancia" id="edit_sur_colindancia" class="custom-form-input" placeholder="Colindancia Sur">
                            <input type="text" name="sur_x" id="edit_sur_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="sur_y" id="edit_sur_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                        <!-- Este -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 10px;">
                            <input type="text" name="este_colindancia" id="edit_este_colindancia" class="custom-form-input" placeholder="Colindancia Este">
                            <input type="text" name="este_x" id="edit_este_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="este_y" id="edit_este_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                        <!-- Oeste -->
                        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 10px;">
                            <input type="text" name="oeste_colindancia" id="edit_oeste_colindancia" class="custom-form-input" placeholder="Colindancia Oeste">
                            <input type="text" name="oeste_x" id="edit_oeste_x" class="custom-form-input" placeholder="X (Este)">
                            <input type="text" name="oeste_y" id="edit_oeste_y" class="custom-form-input" placeholder="Y (Norte)">
                        </div>
                    </div>
                </div>

                <!-- CARD 3: PUNTOS DE MEDICIÓN SEPARADOS (P1-NORTE, P2-SUR, P3-ESTE, P4-OESTE) -->
                <div style="margin-bottom: 18px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/></svg>
                        <span>3. Puntos Cardinales & Mediciones Sonoras (dBA)</span>
                    </div>

                    <!-- P1 - NORTE -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P1 — NORTE</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('edit', 'p1_norte')" title="Agregar medición">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div>
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p1_norte_inicio" id="edit_p1_norte_inicio" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p1_norte_fin" id="edit_p1_norte_fin" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="edit_p1_norte_points_container"></div>
                                <input type="hidden" name="p1_norte_puntos" id="edit_p1_norte_puntos_json">
                            </div>
                        </div>
                    </div>

                    <!-- P2 - SUR -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P2 — SUR</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('edit', 'p2_sur')" title="Agregar medición">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div>
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p2_sur_inicio" id="edit_p2_sur_inicio" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p2_sur_fin" id="edit_p2_sur_fin" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="edit_p2_sur_points_container"></div>
                                <input type="hidden" name="p2_sur_puntos" id="edit_p2_sur_puntos_json">
                            </div>
                        </div>
                    </div>

                    <!-- P3 - ESTE -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P3 — ESTE</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('edit', 'p3_este')" title="Agregar medición">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div>
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p3_este_inicio" id="edit_p3_este_inicio" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p3_este_fin" id="edit_p3_este_fin" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="edit_p3_este_points_container"></div>
                                <input type="hidden" name="p3_este_puntos" id="edit_p3_este_puntos_json">
                            </div>
                        </div>
                    </div>

                    <!-- P4 - OESTE -->
                    <div class="cardinal-section-card">
                        <div class="cardinal-section-header">
                            <span class="cardinal-badge-title">P4 — OESTE</span>
                            <button type="button" class="btn-icon-add-point" onclick="addCardinalPointInput('edit', 'p4_oeste')" title="Agregar medición">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                        <div class="cardinal-inputs-grid">
                            <div>
                                <label class="form-field-label">Hora Inicio</label>
                                <input type="time" name="p4_oeste_inicio" id="edit_p4_oeste_inicio" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Hora Fin</label>
                                <input type="time" name="p4_oeste_fin" id="edit_p4_oeste_fin" class="custom-form-input">
                            </div>
                            <div>
                                <label class="form-field-label">Mediciones (dBA)</label>
                                <div id="edit_p4_oeste_points_container"></div>
                                <input type="hidden" name="p4_oeste_puntos" id="edit_p4_oeste_puntos_json">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 4: FOTOS Y OBSERVACIONES -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 12px;">
                    <div class="form-field-group">
                        <label class="form-field-label">Evidencia Fotográfica Adicional</label>
                        <input type="file" name="photos[]" multiple accept="image/*" class="custom-form-input" style="padding: 8px;">
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Personal Registrador</label>
                        <select name="staff_id" id="edit_staff_id" class="custom-form-select">
                            @foreach($staffList as $stf)
                                <option value="{{ $stf->id }}">{{ $stf->name }} ({{ $stf->position ?? 'Técnico' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-field-group">
                    <label class="form-field-label">Observaciones Perimetrales</label>
                    <textarea name="observations" id="edit_observations" rows="2" class="custom-form-input" placeholder="Detalles de fuentes emisoras..."></textarea>
                </div>

                <!-- Resumen Acústico en Vivo -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px 16px; border-radius: 12px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase;">Resumen Leq Estimado</div>
                        <div style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: #065f46;" id="edit_leq_display">— dBA</div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 11px; font-weight: 700; color: #64748b;">LMP: <strong id="edit_lmp_display">68.0 dBA</strong></div>
                        <div style="margin-top: 3px;" id="edit_cumple_badge">
                            <span class="badge-compliance-ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>CUMPLE</span></span>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="edit_latitude">
                <input type="hidden" name="longitude" id="edit_longitude">
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeEditMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action">Actualizar</button>
            </div>
        </form>
    </div>
</div>
