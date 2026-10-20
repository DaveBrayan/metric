<div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
    <div class="modal-dialog-ruido">
        <div class="modal-header-custom">
            <div>
                <h2 id="createModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 19px; font-weight: 800; color: var(--ink); margin: 0 0 3px 0;">
                    Nuevo Punto de Medición — Ruido Ocupacional
                </h2>
                <p style="font-size: 13px; color: #64748b; margin: 0;">
                    Análisis de exposición laboral según NTS-002/17 (LAeq, TMPE, Dosis)
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()" aria-label="Cerrar">✕</button>
        </div>

        <form action="{{ route('modules.ruido_ocupacional.measurements.store', $module->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body-custom">
                <!-- SECCIÓN 1: IDENTIFICACIÓN LABORAL -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span>1. Ubicación Laboral</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Área o Zona <span class="req">*</span></label>
                            <input type="text" name="area" class="custom-form-input" placeholder="ej. Sala de Máquinas" required>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Punto de Medición <span class="req">*</span></label>
                            <input type="text" name="punto_medicion" class="custom-form-input" placeholder="ej. Centro de operarios" required>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Tipo de Ruido <span class="req">*</span></label>
                            <select name="tipo_ruido" class="custom-form-select" required>
                                <option value="">Seleccione...</option>
                                <option value="Continuo">Continuo</option>
                                <option value="Intermitente">Intermitente</option>
                                <option value="Impulsivo">Impulsivo</option>
                                <option value="Mixto">Mixto</option>
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Actividad / Maquinaria <span class="req">*</span></label>
                            <input type="text" name="actividad" class="custom-form-input" placeholder="ej. Corte de acero, prensa" required>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: PARÁMETROS DE MEDICIÓN -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 2v20M2 12h20M7.07 7.07a6 6 0 0 1 9.86 0M5.64 5.64a9 9 0 0 1 12.72 0"/></svg>
                        <span>2. Configuración del Sonómetro</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                        <div class="form-field-group">
                            <label class="form-field-label">Ponderación <span class="req">*</span></label>
                            <select name="ponderacion" class="custom-form-select" required>
                                <option value="A">A (recomendado)</option>
                                <option value="C">C</option>
                                <option value="Z">Z (lineal)</option>
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Respuesta <span class="req">*</span></label>
                            <select name="respuesta" class="custom-form-select" required>
                                <option value="F">F (Rápida)</option>
                                <option value="S">S (Lenta)</option>
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Tiempo Exposición (h) <span class="req">*</span></label>
                            <input type="number" name="tiempo_expos_h" step="0.1" class="custom-form-input" placeholder="ej. 8.0" required>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: MEDICIONES DE RUIDO -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V9a5 5 0 0 1 10 0v2"/></svg>
                        <span>3. Lecturas del Sonómetro (dB)</span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 8px;">
                        <input type="number" step="0.1" class="custom-form-input medicion-input" placeholder="Med 1" style="font-size: 12px; text-align: center;">
                        <input type="number" step="0.1" class="custom-form-input medicion-input" placeholder="Med 2" style="font-size: 12px; text-align: center;">
                        <input type="number" step="0.1" class="custom-form-input medicion-input" placeholder="Med 3" style="font-size: 12px; text-align: center;">
                        <input type="number" step="0.1" class="custom-form-input medicion-input" placeholder="Med 4" style="font-size: 12px; text-align: center;">
                        <input type="number" step="0.1" class="custom-form-input medicion-input" placeholder="Med 5" style="font-size: 12px; text-align: center;">
                        <input type="number" step="0.1" class="custom-form-input medicion-input" placeholder="Med 6" style="font-size: 12px; text-align: center;">
                    </div>
                    <p style="font-size: 11px; color: #94a3b8; margin-top: 8px;">Se recomienda mínimo 6 mediciones.</p>
                    <input type="hidden" name="mediciones_db" id="mediciones_json">
                </div>

                <!-- SECCIÓN 4: PROTECCIÓN AUDITIVA -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 18px; margin-bottom: 20px;">
                    <div style="font-weight: 800; font-size: 13.5px; color: #0284c7; margin-bottom: 14px; display: flex; align-items: center; gap: 8px; font-family: 'Outfit', sans-serif;">
                        <span>4. Equipos de Protección</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="form-field-group">
                            <label class="form-field-label">¿Usa Protección?</label>
                            <select name="uso_protectores" id="create_uso_protectores" class="custom-form-select" onchange="toggleProtectorFields()">
                                <option value="">Seleccione...</option>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>

                        <div class="form-field-group" id="tipo_protector_group" style="display: none;">
                            <label class="form-field-label">Tipo</label>
                            <input type="text" name="tipo_protector" class="custom-form-input" placeholder="ej. Tapones">
                        </div>

                        <div class="form-field-group" id="nrr_group" style="display: none;">
                            <label class="form-field-label">NRR (dB)</label>
                            <input type="number" name="nrr_db" step="0.1" class="custom-form-input" placeholder="ej. 28.0">
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 5: DOCUMENTACIÓN -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-field-group">
                        <label class="form-field-label">Evidencia Fotográfica</label>
                        <input type="file" name="photos[]" multiple accept="image/*" class="custom-form-input" style="padding: 8px;">
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Personal Registrador</label>
                        <select name="staff_id" class="custom-form-select">
                            @foreach($staffList as $stf)
                            <option value="{{ $stf->id }}">{{ $stf->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-field-group">
                    <label class="form-field-label">Observaciones</label>
                    <textarea name="observations" rows="2" class="custom-form-input" placeholder="Condiciones de trabajo, interferencias..."></textarea>
                </div>

                <!-- Fecha y hora -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-field-group">
                        <label class="form-field-label">Fecha de Medición <span class="req">*</span></label>
                        <input type="date" name="measurement_date" class="custom-form-input" required>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Hora de Medición</label>
                        <input type="time" name="measurement_time" class="custom-form-input">
                    </div>
                </div>

                <!-- Resumen -->
                <div style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1.5px solid #bbf7d0; padding: 14px 18px; border-radius: 12px;">
                    <div>
                        <div style="font-size: 11px; font-weight: 800; color: #16a34a; text-transform: uppercase;">Proyección de Cumplimiento</div>
                        <div style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: #065f46;">LAeq: <span id="create_leq_display">—</span> dBA | Dosis: <span id="create_dosis_display">—</span></div>
                    </div>
                </div>

                <input type="hidden" name="latitude" id="create_latitude">
                <input type="hidden" name="longitude" id="create_longitude">
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                <button type="submit" class="btn-primary-hero-action">Guardar Medición</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleProtectorFields(){var sel=document.getElementById('create_uso_protectores').value;var tg=document.getElementById('tipo_protector_group'), ng=document.getElementById('nrr_group');if(sel==='1'){tg.style.display=''; ng.style.display='';}else{tg.style.display='none'; ng.style.display='none';}}
function updateMedicionesDisplay(){var inputs=document.querySelectorAll('.medicion-input');var vals=Array.from(inputs).map(i=>parseFloat(i.value)||null).filter(v=>v!==null);document.getElementById('mediciones_json').value=JSON.stringify(vals);if(vals.length>0){var sumaE=vals.reduce((s,v)=>s+Math.pow(10,v/10),0);var leq=10*Math.log10(sumaE/vals.length);var tpe=parseFloat(document.querySelector('input[name="tiempo_expos_h"]')?.value)||8;var tmpe=8/Math.pow(2,(leq-85)/3);var dosis=tpe/tmpe;document.getElementById('create_leq_display').textContent=leq.toFixed(1);document.getElementById('create_dosis_display').textContent=(dosis<=1?'✓':'⚠')+' '+dosis.toFixed(2);}}
document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.medicion-input').forEach(i=>i.addEventListener('input',updateMedicionesDisplay));});
</script>
