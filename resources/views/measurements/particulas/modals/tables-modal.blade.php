    <!-- ==========================================================================
         MODAL 8: TABLAS NORMATIVAS Y LÍMITES PERMISIBLES DE PARTÍCULAS
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="normativeTablesModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="tablesModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 820px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div
                        style="width: 34px; height: 34px; border-radius: 9px; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; display: grid; place-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                            <path d="M6 6h10"/>
                            <path d="M6 10h10"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="tablesModalTitle" style="font-size: 17px; margin: 0;">Límites Máximos Permisibles para Material Particulado</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Valores Límite Umbral (TLVs) ACGIH / OSHA & Norma Boliviana NB 510001</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeNormativeTablesModal()"
                    aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <div class="table-responsive-box">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>Fracción de Partícula</th>
                                <th>Tamaño de Corte Aerodinámico</th>
                                <th>Límite Máximo Permisible (ACGIH TLV-TWA)</th>
                                <th>Efecto Crítico / Impacto en la Salud</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <strong style="color: #0284c7;">Fracción Inhalable (PM10)</strong>
                                </td>
                                <td>&le; 10 &micro;m (D50 = 10 &micro;m)</td>
                                <td><strong>10.0 mg/m³</strong> (10,000 &micro;g/m³)</td>
                                <td>Irritación del tracto respiratorio superior, rinitis, bronquitis crónica, sobrecarga pulmonar.</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: #0369a1;">Fracción Respirable (PM2.5)</strong>
                                </td>
                                <td>&le; 2.5 &micro;m a 4 &micro;m (D50 = 4 &micro;m)</td>
                                <td><strong>3.0 mg/m³</strong> (3,000 &micro;g/m³)</td>
                                <td>Penetración profunda en alvéolos pulmonares, fibrosis, neumoconiosis, intercambio gaseoso afectado.</td>
                            </tr>
                            <tr>
                                <td>
                                    <strong style="color: #475569;">Partículas Totales en Suspensión (PTS)</strong>
                                </td>
                                <td>&le; 100 &micro;m</td>
                                <td><strong>10.0 mg/m³</strong> (Referencial)</td>
                                <td>Molestia visual, obstrucción nasal, depósito en mucosas nasofaríngeas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 16px; padding: 12px 14px; background: #f0f9ff; border-radius: 10px; border: 1px solid #bae6fd; font-size: 11.5px; color: #0369a1; line-height: 1.5;">
                    <strong>Nota Técnica:</strong> Los valores límites de umbral (TLVs) representan condiciones bajo las cuales se cree que casi todos los trabajadores pueden estar expuestos repetidamente día tras día durante una jornada laboral de 8 horas sin sufrir efectos adversos para la salud (ACGIH 2024).
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-primary-hero-action" onclick="closeNormativeTablesModal()">Entendido</button>
            </div>
        </div>
    </div>
