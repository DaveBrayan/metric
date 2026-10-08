<!-- ==========================================================================
     MODAL: TABLAS NORMATIVAS (ISO 2631-1 & ISO 5349-1)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="normativeTablesModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="normTablesModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 780px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3">
                    <rect width="18" height="18" x="3" y="3" rx="2" />
                    <path d="M3 9h18" />
                    <path d="M9 21V9" />
                </svg>
                <h2 id="normTablesModalTitle">Valores Límite de Exposición y Niveles de Acción</h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeNormativeTablesModal()">✕</button>
        </div>
        <div class="modal-body-custom" style="padding: 20px; background: #ffffff;">
            <div style="display: flex; flex-direction: column; gap: 16px;">
                
                <!-- Tabla 1: Cuerpo Entero -->
                <div>
                    <h3 style="font-size: 13.5px; font-weight: 800; color: #0284c7; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>1. Vibración de Cuerpo Entero (ISO 2631-1 / NTS 009)</span>
                    </h3>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr style="background: #e0f2fe; color: #0369a1;">
                                <th style="padding: 8px; border: 1px solid #cbd5e1; text-align: left;">Parámetro</th>
                                <th style="padding: 8px; border: 1px solid #cbd5e1; text-align: center;">A(8) Normalizado</th>
                                <th style="padding: 8px; border: 1px solid #cbd5e1; text-align: left;">Acción Requerida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: 700;">Nivel de Acción</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; text-align: center; font-weight: 800; color: #d97706;">0.50 m/s²</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-size: 11.5px; color: #475569;">Vigilancia médica, control ergonómico preventivo.</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: 700;">Valor Límite de Exposición (VLE)</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; text-align: center; font-weight: 800; color: #dc2626;">1.15 m/s²</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-size: 11.5px; color: #475569;">Límite máximo permitido para una jornada diaria de 8 horas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Tabla 2: Mano - Brazo -->
                <div>
                    <h3 style="font-size: 13.5px; font-weight: 800; color: #0284c7; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                        <span>2. Vibración de Mano - Brazo (ISO 5349-1 / NTS 009)</span>
                    </h3>
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; border: 1px solid #e2e8f0;">
                        <thead>
                            <tr style="background: #e0f2fe; color: #0369a1;">
                                <th style="padding: 8px; border: 1px solid #cbd5e1; text-align: left;">Parámetro</th>
                                <th style="padding: 8px; border: 1px solid #cbd5e1; text-align: center;">A(8) Normalizado</th>
                                <th style="padding: 8px; border: 1px solid #cbd5e1; text-align: left;">Acción Requerida</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: 700;">Nivel de Acción</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; text-align: center; font-weight: 800; color: #d97706;">2.50 m/s²</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-size: 11.5px; color: #475569;">Medidas técnicas de reducción, rotación de personal.</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-weight: 700;">Valor Límite de Exposición (VLE)</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; text-align: center; font-weight: 800; color: #dc2626;">5.00 m/s²</td>
                                <td style="padding: 8px; border: 1px solid #e2e8f0; font-size: 11.5px; color: #475569;">Límite máximo permitido. No debe sobrepasarse.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closeNormativeTablesModal()">Cerrar</button>
        </div>
    </div>
</div>
