<!-- ==========================================================================
     MODAL: TABLAS NORMATIVAS Y LÍMITES PERMISIBLES (NB 510002 / ACGIH / NIOSH)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="contaminantesTablesModal" role="dialog" aria-modal="true"
    aria-labelledby="contaminantesTablesModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 1100px; width: 95%;">
        <div class="modal-header-custom" style="flex-wrap: wrap; gap: 12px; padding-bottom: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div
                    style="width: 38px; height: 38px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" />
                        <path d="M3 9h18" />
                        <path d="M3 15h18" />
                        <path d="M9 3v18" />
                    </svg>
                </div>
                <div>
                    <h2 id="contaminantesTablesModalTitle" style="font-size: 17px; margin: 0; color: var(--ink);">Valores Límites Permisibles — Agentes Químicos (NB 510002 / ACGIH)</h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">Límites de exposición ponderada en el tiempo (TLV-TWA) y valores techo (TLV-C)</span>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn-close-modal" onclick="closeNormativeTablesModal()" aria-label="Cerrar">✕</button>
            </div>
        </div>

        <div class="modal-body-custom" style="padding: 16px 20px; max-height: calc(85vh - 120px); overflow-y: auto;">
            
            <div class="table-responsive-box" style="margin-top: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow-x: auto;">
                <table class="matrix-tech-table matrix-report-table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">N°</th>
                            <th style="text-align: left; min-width: 220px;">Agente Químico / Contaminante</th>
                            <th style="text-align: center; width: 130px;">Fórmula / CAS</th>
                            <th style="text-align: center; width: 130px;">TLV-TWA (mg/m³)</th>
                            <th style="text-align: center; width: 130px;">TLV-TWA (ppm)</th>
                            <th style="text-align: center; width: 140px;">TLV-STEL / C</th>
                            <th style="text-align: left; min-width: 240px;">Efectos Críticos / Observaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">1</td>
                            <td style="font-weight: 700; color: #0284c7;">Polvo Total (Inhalable)</td>
                            <td style="text-align: center; font-family: monospace;">—</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">10.0 mg/m³</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="font-size: 12px; color: #334155;">Irritación del tracto respiratorio superior</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">2</td>
                            <td style="font-weight: 700; color: #0284c7;">Polvo Respirable</td>
                            <td style="text-align: center; font-family: monospace;">—</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">3.0 mg/m³</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="font-size: 12px; color: #334155;">Efectos en parénquima pulmonar</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">3</td>
                            <td style="font-weight: 700; color: #0284c7;">Sílice Libre Cristalina (Cuarzo)</td>
                            <td style="text-align: center; font-family: monospace;">14808-60-7</td>
                            <td style="text-align: center; font-weight: 700; color: #dc2626;">0.025 mg/m³</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="text-align: center; color: #64748b;">A2 (Sospechoso)</td>
                            <td style="font-size: 12px; color: #334155;">Silicosis, fibrosis pulmonar, carcinogénico</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">4</td>
                            <td style="font-weight: 700; color: #0284c7;">Humos de Soldadura (Totales)</td>
                            <td style="text-align: center; font-family: monospace;">—</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">5.0 mg/m³</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="font-size: 12px; color: #334155;">Fiebre por humos metálicos, irritación</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">5</td>
                            <td style="font-weight: 700; color: #0284c7;">Monóxido de Carbono (CO)</td>
                            <td style="text-align: center; font-family: monospace;">630-08-0</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">29.0 mg/m³</td>
                            <td style="text-align: center; font-weight: 700; color: #0284c7;">25 ppm</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="font-size: 12px; color: #334155;">Hipoxia tisular, carboxihemoglobina</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">6</td>
                            <td style="font-weight: 700; color: #0284c7;">Dióxido de Carbono (CO₂)</td>
                            <td style="text-align: center; font-family: monospace;">124-38-9</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">9000 mg/m³</td>
                            <td style="text-align: center; font-weight: 700; color: #0284c7;">5000 ppm</td>
                            <td style="text-align: center; font-weight: 700; color: #d97706;">30000 ppm (STEL)</td>
                            <td style="font-size: 12px; color: #334155;">Asfixia simple, acidosis respiratoria</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">7</td>
                            <td style="font-weight: 700; color: #0284c7;">Ácido Sulfhídrico (H₂S)</td>
                            <td style="text-align: center; font-family: monospace;">7783-06-4</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">1.4 mg/m³</td>
                            <td style="text-align: center; font-weight: 700; color: #0284c7;">1 ppm</td>
                            <td style="text-align: center; font-weight: 700; color: #dc2626;">5 ppm (STEL)</td>
                            <td style="font-size: 12px; color: #334155;">Parálisis olfativa, irritación ocular y pulmonar</td>
                        </tr>
                        <tr>
                            <td style="text-align: center; font-weight: 700;">8</td>
                            <td style="font-weight: 700; color: #0284c7;">Vapores Orgánicos (Tolueno)</td>
                            <td style="text-align: center; font-family: monospace;">108-88-3</td>
                            <td style="text-align: center; font-weight: 700; color: #0f172a;">75.0 mg/m³</td>
                            <td style="text-align: center; font-weight: 700; color: #0284c7;">20 ppm</td>
                            <td style="text-align: center; color: #64748b;">—</td>
                            <td style="font-size: 12px; color: #334155;">Depresión del SNC, daño hepatorrenal</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <div class="modal-footer-custom" style="justify-content: flex-end;">
            <button type="button" class="btn-primary-hero-action" onclick="closeNormativeTablesModal()"
                style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
        </div>
    </div>
</div>
