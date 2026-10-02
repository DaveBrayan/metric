<!-- ==========================================================================
     MODAL: PROMPTS, ANÁLISIS TÉCNICO, OBSERVACIONES Y RECOMENDACIONES (MÉTODO REBA)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="rebaPromptsModal" role="dialog" aria-modal="true" onclick="if(event.target === this) closeRebaPromptsModal()" 
     style="position: fixed; inset: 0; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); display: none; align-items: center; justify-content: center; z-index: 99999; padding: 16px;">
    
    <div class="modal-dialog-illumination modal-dialog-prompts" 
         style="max-width: 900px; width: 100%; max-height: 90vh; background: #ffffff; border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0,0,0,0.06); display: flex; flex-direction: column; overflow: hidden; border: 1px solid #e2e8f0; animation: modalFadeIn 0.2s ease-out;">
        
        <!-- Header del Modal -->
        <div class="modal-header-custom" 
             style="padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: flex-start; background: #f8fafc;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                    <span style="display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 9999px; background: #eff6ff; color: #0284c7; font-size: 11px; font-weight: 700; text-transform: uppercase; border: 1px solid #bae6fd; letter-spacing: 0.3px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>
                            <path d="M18 15h6"/>
                            <path d="M21 12v6"/>
                        </svg>
                        Asistente de Ergonomía REBA
                    </span>
                </div>
                <h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.3;">
                    Prompts y Análisis Técnico del Puesto
                </h2>
                <p style="margin: 3px 0 0 0; font-size: 12.5px; color: #64748b;" id="rebaPromptsSubtitle">
                    Puesto: <strong style="color: #0f172a;">{{ isset($selectedMeasurement) && $selectedMeasurement ? $selectedMeasurement->puesto_trabajo : 'General' }}</strong> (Punto {{ isset($selectedMeasurement) && $selectedMeasurement ? $selectedMeasurement->point_number : '01' }}) — Puntuación REBA: <strong style="color: #0284c7;">{{ isset($rebaScores) ? $rebaScores['score_final'] : (isset($selectedMeasurement) && $selectedMeasurement ? $selectedMeasurement->score_final : '—') }}</strong>
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeRebaPromptsModal()" aria-label="Cerrar" 
                    style="background: #f1f5f9; border: 1px solid #e2e8f0; width: 32px; height: 32px; border-radius: 8px; font-size: 16px; color: #64748b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.15s ease;"
                    onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';" 
                    onmouseout="this.style.background='#f1f5f9'; this.style.color='#64748b';">
                ✕
            </button>
        </div>

        <!-- Cuerpo del Modal (Formulario con campos a 100% de ancho) -->
        <div class="modal-body-custom" 
             style="padding: 20px 24px; max-height: calc(88vh - 140px); overflow-y: auto; background: #ffffff;">
            <form id="rebaPromptsForm" style="display: flex; flex-direction: column; gap: 16px; width: 100%; box-sizing: border-box;" onsubmit="event.preventDefault(); saveRebaPrompts();">
                
                <!-- Campo 1: Prompt para Asistente IA -->
                <div style="display: flex; flex-direction: column; gap: 6px; width: 100%; box-sizing: border-box; background: #f8fafc; padding: 14px; border-radius: 10px; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                        <label for="prompt_input" style="font-size: 13px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 7px; margin: 0;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                            </svg>
                            <span>Prompt para Asistente IA (Generador de Informe Técnico)</span>
                        </label>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <button type="button" onclick="resetDefaultRebaPrompt()" title="Restablecer prompt técnico predeterminado"
                                    style="padding: 5px 12px; font-size: 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.15s ease;"
                                    onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#94a3b8';" 
                                    onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1';">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
                                <span>Plantilla IA</span>
                            </button>
                            <button type="button" onclick="copyRebaPromptText()" title="Copiar prompt al portapapeles"
                                    style="padding: 5px 12px; font-size: 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #ffffff; color: #334155; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.15s ease;"
                                    onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#94a3b8';" 
                                    onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1';">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                <span>Copiar Prompt</span>
                            </button>
                        </div>
                    </div>
                    <textarea id="prompt_input" name="prompt" rows="5" 
                              placeholder="Escriba o genere aquí el prompt detallado para la evaluación con IA..."
                              style="width: 100% !important; min-width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; border-radius: 8px !important; border: 1.5px solid #cbd5e1 !important; padding: 10px 14px !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; font-size: 13px !important; color: #1e293b !important; line-height: 1.5 !important; background: #ffffff !important; resize: vertical !important; min-height: 110px !important; outline: none !important;"
                              onfocus="this.style.borderColor='#0284c7'; this.style.boxShadow='0 0 0 3px rgba(2, 132, 199, 0.15)';"
                              onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">{{ $promptsData['prompt'] ?? '' }}</textarea>
                    <span style="font-size: 11.5px; color: #64748b; line-height: 1.3;">
                        Este prompt instruye a la IA a generar el 'Análisis Técnico', 'Observaciones' y 'Recomendaciones' según los resultados cuantitativos del método REBA.
                    </span>
                </div>

                <!-- Campo 2: 4. Análisis técnico del puesto -->
                <div style="display: flex; flex-direction: column; gap: 6px; width: 100%; box-sizing: border-box;">
                    <label for="analisis_tecnico_input" style="font-size: 13px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 7px; margin: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span>4. Análisis técnico del puesto</span>
                    </label>
                    <textarea id="analisis_tecnico_input" name="analisis_tecnico" rows="4" 
                              placeholder="Pegue o redacte aquí las instrucciones o el análisis técnico postural, ergonómico y biomecánico..."
                              style="width: 100% !important; min-width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; border-radius: 8px !important; border: 1.5px solid #cbd5e1 !important; padding: 10px 14px !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; font-size: 13px !important; color: #1e293b !important; line-height: 1.5 !important; background: #ffffff !important; resize: vertical !important; min-height: 85px !important; outline: none !important;"
                              onfocus="this.style.borderColor='#059669'; this.style.boxShadow='0 0 0 3px rgba(5, 150, 105, 0.15)';"
                              onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">{{ $promptsData['analisis_tecnico'] ?? '' }}</textarea>
                </div>

                <!-- Campo 3: 5. Observaciones encontradas -->
                <div style="display: flex; flex-direction: column; gap: 6px; width: 100%; box-sizing: border-box;">
                    <label for="observaciones_input" style="font-size: 13px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 7px; margin: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <span>5. Observaciones encontradas (Viñetas automáticas)</span>
                    </label>
                    <textarea id="observaciones_input" name="observaciones" rows="4" 
                              placeholder="• Observación 1&#10;• Observación 2&#10;• Observación 3"
                              style="width: 100% !important; min-width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; border-radius: 8px !important; border: 1.5px solid #cbd5e1 !important; padding: 10px 14px !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; font-size: 13px !important; color: #1e293b !important; line-height: 1.5 !important; background: #ffffff !important; resize: vertical !important; min-height: 85px !important; outline: none !important;"
                              onfocus="this.style.borderColor='#d97706'; this.style.boxShadow='0 0 0 3px rgba(217, 119, 6, 0.15)';"
                              onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">{{ $promptsData['observaciones'] ?? '' }}</textarea>
                </div>

                <!-- Campo 4: 6. Recomendaciones por puesto -->
                <div style="display: flex; flex-direction: column; gap: 6px; width: 100%; box-sizing: border-box;">
                    <label for="recomendaciones_input" style="font-size: 13px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 7px; margin: 0;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                        <span>6. Recomendaciones por puesto (Medidas de ingeniería y administrativas)</span>
                    </label>
                    <textarea id="recomendaciones_input" name="recomendaciones" rows="4" 
                              placeholder="• Recomendación 1&#10;• Recomendación 2&#10;• Recomendación 3"
                              style="width: 100% !important; min-width: 100% !important; max-width: 100% !important; box-sizing: border-box !important; border-radius: 8px !important; border: 1.5px solid #cbd5e1 !important; padding: 10px 14px !important; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important; font-size: 13px !important; color: #1e293b !important; line-height: 1.5 !important; background: #ffffff !important; resize: vertical !important; min-height: 85px !important; outline: none !important;"
                              onfocus="this.style.borderColor='#7c3aed'; this.style.boxShadow='0 0 0 3px rgba(124, 58, 237, 0.15)';"
                              onblur="this.style.borderColor='#cbd5e1'; this.style.boxShadow='none';">{{ $promptsData['recomendaciones'] ?? '' }}</textarea>
                </div>

            </form>
        </div>

        <!-- Footer del Modal -->
        <div class="modal-footer-custom" 
             style="padding: 14px 24px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; align-items: center; gap: 12px; background: #f8fafc;">
            <button type="button" onclick="closeRebaPromptsModal()" 
                    style="padding: 9px 18px; border-radius: 8px; border: 1px solid #cbd5e1; background: #ffffff; color: #475569; font-size: 13px; font-weight: 700; cursor: pointer; transition: all 0.15s ease;"
                    onmouseover="this.style.background='#f1f5f9';" 
                    onmouseout="this.style.background='#ffffff';">
                Cerrar
            </button>
            <button type="button" onclick="saveRebaPrompts()" id="btnSaveRebaPrompts"
                    style="padding: 9px 22px; border-radius: 8px; border: none; background: #0284c7; color: #ffffff; font-size: 13px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 6px rgba(2, 132, 199, 0.3); transition: all 0.15s ease;"
                    onmouseover="this.style.background='#0369a1';" 
                    onmouseout="this.style.background='#0284c7';">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span>Guardar y Aplicar</span>
            </button>
        </div>

    </div>

</div>
