    <!-- ==========================================================================
         MODAL 8: TABLAS NORMATIVAS Y LÍMITES PERMISIBLES DE GASES
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="normativeTablesModal" role="dialog" aria-modal="true"
        aria-labelledby="tablesModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 820px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div
                        style="width: 34px; height: 34px; border-radius: 9px; background: #e0f2fe; color: #10b9df; display: grid; place-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/>
                            <path d="M6 6h10"/>
                            <path d="M6 10h10"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="tablesModalTitle" style="font-size: 17px; margin: 0;">Límites Máximos Permisibles para Gases</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Normativa Internacional OSHA / NIOSH / ACGIH & NB 510001</span>
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
                                <th>Gas / Agente Químico</th>
                                <th>Fórmula</th>
                                <th>Unidad</th>
                                <th>LMP / TWA (8h)</th>
                                <th>STEL (15min)</th>
                                <th>Efecto Crítico / Riesgo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Oxígeno</strong></td>
                                <td><span class="gas-formula-badge">O2</span></td>
                                <td>ppm</td>
                                <td>19.5% - 23.5%</td>
                                <td>—</td>
                                <td>Asfixia simple / Enriquecimiento</td>
                            </tr>
                            <tr>
                                <td><strong>Ácido Sulfhídrico</strong></td>
                                <td><span class="gas-formula-badge">H2S</span></td>
                                <td>ppm</td>
                                <td>1.0 ppm</td>
                                <td>5.0 ppm</td>
                                <td>Toxicidad aguda, parálisis olfativa</td>
                            </tr>
                            <tr>
                                <td><strong>Monóxido de Carbono</strong></td>
                                <td><span class="gas-formula-badge">CO</span></td>
                                <td>ppm</td>
                                <td>25.0 ppm</td>
                                <td>—</td>
                                <td>Asfixia química, carboxihemoglobina</td>
                            </tr>
                            <tr>
                                <td><strong>Gases Combustibles</strong></td>
                                <td><span class="gas-formula-badge">LEL</span></td>
                                <td>%</td>
                                <td>&lt; 10% LEL</td>
                                <td>—</td>
                                <td>Riesgo de inflamabilidad / explosión</td>
                            </tr>
                            <tr>
                                <td><strong>Formaldehídos</strong></td>
                                <td><span class="gas-formula-badge">HCHO</span></td>
                                <td>mg/m3</td>
                                <td>0.1 ppm (0.12 mg/m3)</td>
                                <td>0.3 ppm</td>
                                <td>Irritación ocular, carcinógeno</td>
                            </tr>
                            <tr>
                                <td><strong>Compuestos Orgánicos Volátiles</strong></td>
                                <td><span class="gas-formula-badge">T-VOC</span></td>
                                <td>mg/m3</td>
                                <td>0.5 mg/m3</td>
                                <td>—</td>
                                <td>Irritación mucosa, cefaleas</td>
                            </tr>
                            <tr>
                                <td><strong>Dióxido de Carbono</strong></td>
                                <td><span class="gas-formula-badge">CO2</span></td>
                                <td>ppm</td>
                                <td>5000 ppm</td>
                                <td>30000 ppm</td>
                                <td>Asfixia, acidosis metabólica</td>
                            </tr>
                            <tr>
                                <td><strong>Arsénico Inorgánico</strong></td>
                                <td><span class="gas-formula-badge">As</span></td>
                                <td>mg/m3</td>
                                <td>0.01 mg/m3</td>
                                <td>—</td>
                                <td>Toxicidad celular, carcinógeno</td>
                            </tr>
                            <tr>
                                <td><strong>Dióxido de Azufre</strong></td>
                                <td><span class="gas-formula-badge">SO2</span></td>
                                <td>ppm</td>
                                <td>0.25 ppm</td>
                                <td>—</td>
                                <td>Irritación pulmonar, broncoconstricción</td>
                            </tr>
                            <tr>
                                <td><strong>Amoniaco</strong></td>
                                <td><span class="gas-formula-badge">NH3</span></td>
                                <td>ppm</td>
                                <td>25 ppm</td>
                                <td>35 ppm</td>
                                <td>Corrosión de vías respiratorias</td>
                            </tr>
                            <tr>
                                <td><strong>Cloro Gaseoso</strong></td>
                                <td><span class="gas-formula-badge">Cl2</span></td>
                                <td>ppm</td>
                                <td>0.1 ppm</td>
                                <td>0.4 ppm</td>
                                <td>Edema pulmonar, irritación severa</td>
                            </tr>
                            <tr>
                                <td><strong>Compuestos Orgánicos Volátiles Totales</strong></td>
                                <td><span class="gas-formula-badge">TCOV</span></td>
                                <td>ppm</td>
                                <td>Referencial</td>
                                <td>—</td>
                                <td>Calidad de aire interior</td>
                            </tr>
                            <tr>
                                <td><strong>Dióxido de Nitrógeno</strong></td>
                                <td><span class="gas-formula-badge">NO2</span></td>
                                <td>ppm</td>
                                <td>0.2 ppm</td>
                                <td>—</td>
                                <td>Toxicidad respiratoria profunda</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-primary-hero-action" onclick="closeNormativeTablesModal()">Entendido</button>
            </div>
        </div>
    </div>
