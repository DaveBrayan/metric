    <!-- ==========================================================================
         MODAL 8: TABLAS NORMATIVAS Y LÍMITES PERMISIBLES DE CALIDAD DE AIRE
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="normativeTablesModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="normativeTablesModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 860px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 34px; height: 34px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="M3 9h18" />
                            <path d="M9 21V9" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="normativeTablesModalTitle" style="font-size: 17px; margin: 0;">Límites Permisibles de Calidad del Aire</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Normativa Ambiental Boliviana (Ley 1333 RMCA) y Directrices Internacionales OMS</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeNormativeTablesModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 22px 24px;">
                <!-- Tabla Ley 1333 -->
                <div style="margin-bottom: 20px;">
                    <div style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: var(--ink); margin-bottom: 8px;">
                        1. Reglamento en Materia de Contaminación Atmosférica (Ley N° 1333 — Anexo 1)
                    </div>
                    <table class="part-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="part-th-blue">Contaminante</th>
                                <th class="part-th-blue">Tiempo de Exposición</th>
                                <th class="part-th-blue">LMP (µg/m³)</th>
                                <th class="part-th-blue">Método de Medición</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight: bold; text-align: left; padding-left: 8px;">Partículas Totales en Suspensión (PTS)</td>
                                <td align="center">24 horas</td>
                                <td align="center" style="font-weight: bold; color: #0284c7;">260 µg/m³</td>
                                <td style="text-align: left; padding-left: 8px;">Muestreador de Alto Volumen (Hi-Vol)</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: left; padding-left: 8px;">Partículas Totales en Suspensión (PTS)</td>
                                <td align="center">Media Aritmética Anual</td>
                                <td align="center" style="font-weight: bold; color: #0284c7;">75 µg/m³</td>
                                <td style="text-align: left; padding-left: 8px;">Gravimetría</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: left; padding-left: 8px;">Material Particulado PM10</td>
                                <td align="center">24 horas</td>
                                <td align="center" style="font-weight: bold; color: #0284c7;">150 µg/m³</td>
                                <td style="text-align: left; padding-left: 8px;">Separación Inercial / Hi-Vol PM10</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: left; padding-left: 8px;">Material Particulado PM10</td>
                                <td align="center">Media Aritmética Anual</td>
                                <td align="center" style="font-weight: bold; color: #0284c7;">50 µg/m³</td>
                                <td style="text-align: left; padding-left: 8px;">Gravimetría / Atenuación Beta</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Guías OMS -->
                <div>
                    <div style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: var(--ink); margin-bottom: 8px;">
                        2. Directrices Mundiales de la OMS sobre la Calidad del Aire (2021)
                    </div>
                    <table class="part-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="part-th-blue">Contaminante</th>
                                <th class="part-th-blue">Promedio Temporal</th>
                                <th class="part-th-blue">Nivel Guía OMS (µg/m³)</th>
                                <th class="part-th-blue">Objetivo Interino 1</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight: bold; text-align: left; padding-left: 8px;">PM2.5 (Fracción Fina Respirable)</td>
                                <td align="center">24 horas (P99)</td>
                                <td align="center" style="font-weight: bold; color: #059669;">15 µg/m³</td>
                                <td align="center">75 µg/m³</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: left; padding-left: 8px;">PM2.5 (Fracción Fina Respirable)</td>
                                <td align="center">Anual</td>
                                <td align="center" style="font-weight: bold; color: #059669;">5 µg/m³</td>
                                <td align="center">35 µg/m³</td>
                            </tr>
                            <tr>
                                <td style="font-weight: bold; text-align: left; padding-left: 8px;">PM10 (Fracción Gruesa)</td>
                                <td align="center">24 horas (P99)</td>
                                <td align="center" style="font-weight: bold; color: #059669;">45 µg/m³</td>
                                <td align="center">150 µg/m³</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeNormativeTablesModal()">Entendido / Cerrar</button>
            </div>
        </div>
    </div>
