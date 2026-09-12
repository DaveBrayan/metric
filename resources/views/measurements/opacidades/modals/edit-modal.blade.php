<div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
    <div class="modal-dialog-opacity">
        <div class="modal-header-custom">
            <div>
                <h2 id="editModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 19px; font-weight: 800; color: var(--ink); margin: 0 0 3px 0;">
                    Editar Punto de Medición — Opacidad
                </h2>
                <p style="font-size: 13px; color: #64748b; margin: 0;">
                    Actualización de ensayos de opacidad vehicular, RPM y parámetros de emisión.
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeEditMeasurementModal()" aria-label="Cerrar">✕</button>
        </div>

        <form id="editMeasurementForm" action="" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body-custom">
                <!-- CARD 1: DATOS DEL VEHÍCULO -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 10.9 2 11.2V16c0 .6.4 1 1 1h2"/>
                            <circle cx="7" cy="17" r="2"/>
                            <path d="M9 17h6"/>
                            <circle cx="17" cy="17" r="2"/>
                        </svg>
                        <span>1. Identificación del Vehículo / Maquinaria</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Tipo de Vehículo <span class="req">*</span></label>
                            <input type="text" name="tipo_vehiculo" id="edit_tipo_vehiculo" class="custom-form-input" required>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Placa / Identificador <span class="req">*</span></label>
                            <input type="text" name="placa" id="edit_placa" class="custom-form-input" required style="text-transform: uppercase;">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Marca</label>
                            <input type="text" name="marca" id="edit_marca" class="custom-form-input">
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Modelo</label>
                            <input type="text" name="modelo" id="edit_modelo" class="custom-form-input">
                        </div>
                    </div>
                </div>

                <!-- CARD 2: MEDICIONES DE OPACIDAD, RPM Y TEMPERATURA -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 6v6l4 2"/>
                        </svg>
                        <span>2. Ensayos de Aceleración Libre (Opacidad & Tacometría)</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Temperatura del Motor (°C)</label>
                            <input type="number" step="0.1" name="temp_c" id="edit_temp_c" class="custom-form-input">
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Límite Normativo LMP (%)</label>
                            <input type="number" step="0.1" name="limite_normativa" id="edit_limite_normativa" class="custom-form-input" oninput="recalcOpacidad('edit')">
                        </div>
                    </div>

                    <!-- Tres Ensayos de Opacidad (%) -->
                    <div style="margin-bottom: 14px;">
                        <label class="form-field-label" style="margin-bottom: 8px;">Lecturas de Opacidad (%) — 3 Ensayos Consecutivos</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                            <input type="number" step="0.01" name="opa_1" id="edit_opa_1" class="custom-form-input" placeholder="Ensayo 1 (%)" oninput="recalcOpacidad('edit')">
                            <input type="number" step="0.01" name="opa_2" id="edit_opa_2" class="custom-form-input" placeholder="Ensayo 2 (%)" oninput="recalcOpacidad('edit')">
                            <input type="number" step="0.01" name="opa_3" id="edit_opa_3" class="custom-form-input" placeholder="Ensayo 3 (%)" oninput="recalcOpacidad('edit')">
                        </div>
                    </div>

                    <!-- Tres Ensayos de RPM -->
                    <div>
                        <label class="form-field-label" style="margin-bottom: 8px;">Lecturas de Tacómetro (RPM de Corte)</label>
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
                            <input type="number" step="1" name="rpm_1" id="edit_rpm_1" class="custom-form-input" placeholder="RPM 1" oninput="recalcOpacidad('edit')">
                            <input type="number" step="1" name="rpm_2" id="edit_rpm_2" class="custom-form-input" placeholder="RPM 2" oninput="recalcOpacidad('edit')">
                            <input type="number" step="1" name="rpm_3" id="edit_rpm_3" class="custom-form-input" placeholder="RPM 3" oninput="recalcOpacidad('edit')">
                        </div>
                    </div>
                </div>

                <!-- CARD 3: GEORREFERENCIACIÓN Y FECHAS -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px;">
                        <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                <circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span>3. Georreferenciación & Coordenadas Satelitales</span>
                        </div>
                        <button type="button" class="btn-secondary-subtle" onclick="captureCoordinatesGPS('edit')" style="padding: 7px 16px; font-size: 12.5px;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4">
                                <circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/>
                            </svg>
                            <span>Capturar Coordenadas GPS</span>
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Zona UTM</label>
                            <input type="text" name="utm_zone" id="edit_utm_zone" class="custom-form-input">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Este (X)</label>
                            <input type="text" name="utm_easting" id="edit_utm_easting" class="custom-form-input">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Norte (Y)</label>
                            <input type="text" name="utm_northing" id="edit_utm_northing" class="custom-form-input">
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Fecha de Medición</label>
                            <input type="date" name="measurement_date" id="edit_measurement_date" class="custom-form-input">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Hora</label>
                            <input type="time" name="measurement_time" id="edit_measurement_time" class="custom-form-input">
                        </div>
                    </div>
                </div>

                <!-- CARD 4: FOTOS, PERSONAL Y OBSERVACIONES -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-field-group">
                        <label class="form-field-label">Evidencias Fotográficas</label>
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
                    <label class="form-field-label">Observaciones y Estado del Escape</label>
                    <textarea name="observations" id="edit_observations" rows="2" class="custom-form-input"></textarea>
                </div>

                <!-- Resumen en Vivo -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 14px 18px; border-radius: 12px; margin-top: 10px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #16a34a; text-transform: uppercase;">Opacidad Promedio Calculada</div>
                        <div style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 800; color: #065f46;" id="edit_opa_promedio_display">— %</div>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">RPM Promedio</div>
                        <div style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; color: #1e293b;" id="edit_rpm_promedio_display">— RPM</div>
                    </div>
                    <div style="text-align: right;">
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
