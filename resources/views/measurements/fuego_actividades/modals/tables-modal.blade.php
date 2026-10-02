    <!-- ==========================================================================
             MODAL 8: TABLAS TÉCNICAS NORMATIVAS — CARGA DE FUEGO (NB 58005 / NTP 453)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="fireTablesModal" role="dialog" aria-modal="true"
        aria-labelledby="fireTablesModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1040px; width: 95%;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 9px; background: #fffbeb; color: #d97706; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="M3 9h18" />
                            <path d="M3 15h18" />
                            <path d="M9 3v18" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="fireTablesModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: var(--ink);">
                            Tablas Normativas — Carga de Fuego por Actividad
                        </h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                            Criterios técnicos de cálculo según Norma Boliviana NB 58005 y NTP 453
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeFireTablesModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 20px 24px; max-height: 75vh; overflow-y: auto;">
                <!-- Navegación por pestañas de normas -->
                <div class="fire-tables-nav-tabs">
                    <button type="button" class="fire-tab-nav active" onclick="switchFireTableTab('tab-qsi')">
                        <span>1. Cargas por Actividad (qsi)</span>
                    </button>
                    <button type="button" class="fire-tab-nav" onclick="switchFireTableTab('tab-ci-ra')">
                        <span>2. Factores Ci y Ra</span>
                    </button>
                    <button type="button" class="fire-tab-nav" onclick="switchFireTableTab('tab-riesgo')">
                        <span>3. Niveles de Riesgo</span>
                    </button>
                    <button type="button" class="fire-tab-nav" onclick="switchFireTableTab('tab-extintores')">
                        <span>4. Dotación de Extintores</span>
                    </button>
                </div>

                <!-- PESTAÑA 1: CARGAS POR ACTIVIDAD (qsi) -->
                <div id="tab-qsi" class="fire-table-tab-pane active">
                    <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px; font-weight: 700; color: #334155;">
                            Valores de densidad de carga de fuego intrínseca (qsi) según actividad y uso:
                        </span>
                        <input type="text" id="filterNormTableInput" placeholder="Buscar actividad en la tabla..."
                            style="padding: 5px 10px; font-size: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; outline: none; width: 220px;"
                            onkeyup="filterNormativeCatalogTable(this)">
                    </div>

                    <div style="max-height: 380px; overflow-y: auto; border: 1.5px solid #e2e8f0; border-radius: 10px;">
                        <table class="tech-normative-table" id="normativeCatalogTable">
                            <thead>
                                <tr>
                                    <th style="width: 45%;">Actividad / Sector Industrial</th>
                                    <th style="width: 20%; text-align: center;">qsi (MJ/m²)</th>
                                    <th style="width: 20%; text-align: center;">qsi (Mcal/m²)</th>
                                    <th style="width: 15%; text-align: center;">Ci Sugerido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Abonos químicos</td><td style="text-align: center; font-family: monospace;">200</td><td style="text-align: center; font-family: monospace;">48</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Aceites comestibles, expedición</td><td style="text-align: center; font-family: monospace;">400</td><td style="text-align: center; font-family: monospace;">96</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Aceites: mineral, vegetal y animal</td><td style="text-align: center; font-family: monospace;">2,400</td><td style="text-align: center; font-family: monospace;">573</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Acero (almacén o producción)</td><td style="text-align: center; font-family: monospace;">100</td><td style="text-align: center; font-family: monospace;">24</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Algodón, almacén de</td><td style="text-align: center; font-family: monospace;">800</td><td style="text-align: center; font-family: monospace;">191</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Alimentación, embalaje</td><td style="text-align: center; font-family: monospace;">400</td><td style="text-align: center; font-family: monospace;">96</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Almacenes de talleres y repuestos</td><td style="text-align: center; font-family: monospace;">300</td><td style="text-align: center; font-family: monospace;">72</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Aparatos eléctricos y electrónicos</td><td style="text-align: center; font-family: monospace;">300</td><td style="text-align: center; font-family: monospace;">72</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Archivos y bibliotecas</td><td style="text-align: center; font-family: monospace;">650</td><td style="text-align: center; font-family: monospace;">155</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Automóviles, pintura y barnices</td><td style="text-align: center; font-family: monospace;">1,600</td><td style="text-align: center; font-family: monospace;">382</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Cartonaje y papel corrugado</td><td style="text-align: center; font-family: monospace;">500</td><td style="text-align: center; font-family: monospace;">120</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Caucho y neumáticos</td><td style="text-align: center; font-family: monospace;">1,800</td><td style="text-align: center; font-family: monospace;">430</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Cuero y calzado</td><td style="text-align: center; font-family: monospace;">600</td><td style="text-align: center; font-family: monospace;">143</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Madera, aserraderos y carpintería</td><td style="text-align: center; font-family: monospace;">800</td><td style="text-align: center; font-family: monospace;">191</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Plásticos y polímeros sintéticos</td><td style="text-align: center; font-family: monospace;">1,200</td><td style="text-align: center; font-family: monospace;">287</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Textiles y confecciones</td><td style="text-align: center; font-family: monospace;">500</td><td style="text-align: center; font-family: monospace;">120</td><td style="text-align: center;">1.3</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PESTAÑA 2: FACTORES Ci y Ra -->
                <div id="tab-ci-ra" class="fire-table-tab-pane" style="display: none;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <!-- Factor Ci -->
                        <div style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px; background: #ffffff;">
                            <h3 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px;">
                                Coeficiente de Combustibilidad (Ci)
                            </h3>
                            <p style="font-size: 12px; color: #64748b; line-height: 1.4;">
                                Pondera la velocidad de combustión y facilidad de ignición de los materiales:
                            </p>
                            <table class="tech-normative-table">
                                <thead>
                                    <tr><th>Grado de Combustibilidad</th><th style="text-align: center;">Ci</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Baja:</strong> Metales, inorgánicos, lanas minerales</td><td style="text-align: center; font-family: monospace; font-weight: 800;">1.0</td></tr>
                                    <tr><td><strong>Media:</strong> Madera, papel, algodón, cartón, cueros</td><td style="text-align: center; font-family: monospace; font-weight: 800;">1.3</td></tr>
                                    <tr><td><strong>Alta:</strong> Plásticos, solventes, hidrocarburos, espumas</td><td style="text-align: center; font-family: monospace; font-weight: 800;">1.6</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Factor Ra -->
                        <div style="border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px; background: #ffffff;">
                            <h3 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px;">
                                Coeficiente de Riesgo de Activación (Ra)
                            </h3>
                            <p style="font-size: 12px; color: #64748b; line-height: 1.4;">
                                Pondera las fuentes de ignición existentes en el sector o proceso:
                            </p>
                            <table class="tech-normative-table">
                                <thead>
                                    <tr><th>Nivel de Activación</th><th style="text-align: center;">Ra</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><strong>Bajo:</strong> Sin fuentes de calor directas ni llamas abiertas</td><td style="text-align: center; font-family: monospace; font-weight: 800;">1.0</td></tr>
                                    <tr><td><strong>Medio:</strong> Maquinaria rotativa, calefacción, trabajos en caliente controlados</td><td style="text-align: center; font-family: monospace; font-weight: 800;">1.5</td></tr>
                                    <tr><td><strong>Alto:</strong> Soldadura continua, calderas, hornos, vapores inflamables</td><td style="text-align: center; font-family: monospace; font-weight: 800;">2.0</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- PESTAÑA 3: NIVELES DE RIESGO -->
                <div id="tab-riesgo" class="fire-table-tab-pane" style="display: none;">
                    <div style="border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                        <table class="tech-normative-table">
                            <thead>
                                <tr>
                                    <th>Nivel de Riesgo Intrínseco</th>
                                    <th style="text-align: center;">Densidad Qs (MJ/m²)</th>
                                    <th style="text-align: center;">Densidad Qs (Mcal/m²)</th>
                                    <th>Exigencias de Protección Pasiva y Activa</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="background: #f0fdf4;">
                                    <td>
                                        <span class="fire-risk-badge emerald" style="font-size: 12px;">Riesgo Bajo</span>
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-weight: 800;">Qs &le; 840</td>
                                    <td style="text-align: center; font-family: monospace; font-weight: 800;">Qs &le; 200</td>
                                    <td style="font-size: 12px; color: #334155;">Extintores portátiles a máx. 20m de recorrido. Señalética de evacuación y alumbrado de emergencia.</td>
                                </tr>
                                <tr style="background: #fffbeb;">
                                    <td>
                                        <span class="fire-risk-badge amber" style="font-size: 12px;">Riesgo Medio</span>
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-weight: 800;">840 &lt; Qs &le; 3,360</td>
                                    <td style="text-align: center; font-family: monospace; font-weight: 800;">200 &lt; Qs &le; 800</td>
                                    <td style="font-size: 12px; color: #334155;">Extintores a máx. 15m. Sistema de detección automática y pulsadores manuales. BIEs de 25mm si superficie &gt; 500m².</td>
                                </tr>
                                <tr style="background: #fff1f2;">
                                    <td>
                                        <span class="fire-risk-badge rose" style="font-size: 12px;">Riesgo Alto</span>
                                    </td>
                                    <td style="text-align: center; font-family: monospace; font-weight: 800;">Qs &gt; 3,360</td>
                                    <td style="text-align: center; font-family: monospace; font-weight: 800;">Qs &gt; 800</td>
                                    <td style="font-size: 12px; color: #334155;">Extintores reforzados con carros rodantes de 50kg. Detección automática, pulsadores, BIEs / Hidrantes exteriores y rociadores automáticos según área.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- PESTAÑA 4: DOTACIÓN DE EXTINTORES -->
                <div id="tab-extintores" class="fire-table-tab-pane" style="display: none;">
                    <div style="border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                        <table class="tech-normative-table">
                            <thead>
                                <tr>
                                    <th>Tipo de Riesgo</th>
                                    <th style="text-align: center;">Potencial Mínimo</th>
                                    <th style="text-align: center;">Área Máx. / Extintor</th>
                                    <th style="text-align: center;">Distancia Máx. Traslado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td><strong>Riesgo Bajo (Clase A)</strong></td><td style="text-align: center; font-weight: 700;">2-A</td><td style="text-align: center; font-family: monospace;">200 m²</td><td style="text-align: center; font-family: monospace;">20 m</td></tr>
                                <tr><td><strong>Riesgo Medio (Clase A)</strong></td><td style="text-align: center; font-weight: 700;">3-A / 4-A</td><td style="text-align: center; font-family: monospace;">150 m²</td><td style="text-align: center; font-family: monospace;">15 m</td></tr>
                                <tr><td><strong>Riesgo Alto (Clase A)</strong></td><td style="text-align: center; font-weight: 700;">4-A / 6-A</td><td style="text-align: center; font-family: monospace;">100 m²</td><td style="text-align: center; font-family: monospace;">10 m</td></tr>
                                <tr><td><strong>Líquidos Inflamables (Clase B)</strong></td><td style="text-align: center; font-weight: 700;">20-B a 80-B</td><td style="text-align: center; font-family: monospace;">Según volumen</td><td style="text-align: center; font-family: monospace;">15 m</td></tr>
                                <tr><td><strong>Equipos Energizados (Clase C)</strong></td><td style="text-align: center; font-weight: 700;">CO2 / Clean Agent</td><td style="text-align: center; font-family: monospace;">Por tablero/rack</td><td style="text-align: center; font-family: monospace;">10 m</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-primary-hero-action" onclick="closeFireTablesModal()"
                    style="padding: 8px 18px; font-size: 13px;">Entendido</button>
            </div>
        </div>
    </div>
