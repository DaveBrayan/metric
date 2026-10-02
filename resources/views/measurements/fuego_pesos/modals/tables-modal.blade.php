    <!-- ==========================================================================
             MODAL 8: TABLAS TÉCNICAS NORMATIVAS — CARGA DE FUEGO POR PESO (NB 58005 / NTP 453)
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
                            Tablas Normativas — Carga de Fuego por Peso
                        </h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                            Criterios técnicos de cálculo, poder calorífico de materiales (Ki) y factores según Norma Boliviana NB 58005 y NTP 453
                        </span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeFireTablesModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 20px 24px; max-height: 75vh; overflow-y: auto;">
                <!-- Navegación por pestañas de normas -->
                <div class="fire-tables-nav-tabs">
                    <button type="button" class="fire-tab-nav active" onclick="switchFireTableTab('tab-ki-mat')">
                        <span>1. Poder Calorífico (Ki)</span>
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

                <!-- PESTAÑA 1: PODER CALORÍFICO DE MATERIALES (Ki / Pci) -->
                <div id="tab-ki-mat" class="fire-table-tab-pane active">
                    <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 13px; font-weight: 700; color: #334155;">
                            Valores de poder calorífico intrínseco (Ki / Pci) por material combustible:
                        </span>
                        <input type="text" id="filterNormTableInput" placeholder="Buscar material en la tabla..."
                            style="padding: 5px 10px; font-size: 12px; border: 1.5px solid #cbd5e1; border-radius: 8px; outline: none; width: 220px;"
                            onkeyup="filterNormativeCatalogTable(this)">
                    </div>

                    <div style="max-height: 380px; overflow-y: auto; border: 1.5px solid #e2e8f0; border-radius: 10px;">
                        <table class="tech-normative-table" id="normativeCatalogTable">
                            <thead>
                                <tr>
                                    <th style="width: 40%;">Material Combustible</th>
                                    <th style="width: 20%; text-align: center;">Ki (Mcal/kg)</th>
                                    <th style="width: 20%; text-align: center;">Ki (MJ/kg)</th>
                                    <th style="width: 20%; text-align: center;">Ci Sugerido</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Aceite de algodón</td><td style="text-align: center; font-family: monospace;">9.40</td><td style="text-align: center; font-family: monospace;">39.33</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Aceite mineral / lubricantes</td><td style="text-align: center; font-family: monospace;">10.00</td><td style="text-align: center; font-family: monospace;">41.84</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Aceite de parafina</td><td style="text-align: center; font-family: monospace;">10.30</td><td style="text-align: center; font-family: monospace;">43.10</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Acetona</td><td style="text-align: center; font-family: monospace;">7.40</td><td style="text-align: center; font-family: monospace;">30.96</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Aguarrás / solvente</td><td style="text-align: center; font-family: monospace;">10.20</td><td style="text-align: center; font-family: monospace;">42.68</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Alcohol etílico (Etanol)</td><td style="text-align: center; font-family: monospace;">7.10</td><td style="text-align: center; font-family: monospace;">29.71</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Algodón / fardos</td><td style="text-align: center; font-family: monospace;">4.00</td><td style="text-align: center; font-family: monospace;">16.74</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Antracita (Carbón mineral)</td><td style="text-align: center; font-family: monospace;">7.80</td><td style="text-align: center; font-family: monospace;">32.64</td><td style="text-align: center;">1.0</td></tr>
                                <tr><td>Azúcar</td><td style="text-align: center; font-family: monospace;">4.00</td><td style="text-align: center; font-family: monospace;">16.74</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Cartón corrugado</td><td style="text-align: center; font-family: monospace;">4.00</td><td style="text-align: center; font-family: monospace;">16.74</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Caucho y neumáticos</td><td style="text-align: center; font-family: monospace;">9.50</td><td style="text-align: center; font-family: monospace;">39.75</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Cuero y calzado</td><td style="text-align: center; font-family: monospace;">4.60</td><td style="text-align: center; font-family: monospace;">19.25</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Gasóleo (Diesel / Combustibles)</td><td style="text-align: center; font-family: monospace;">10.50</td><td style="text-align: center; font-family: monospace;">43.93</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Grasas animales / vegetales</td><td style="text-align: center; font-family: monospace;">9.20</td><td style="text-align: center; font-family: monospace;">38.49</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Harina de trigo / cereales</td><td style="text-align: center; font-family: monospace;">4.00</td><td style="text-align: center; font-family: monospace;">16.74</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Madera de pino / eucalipto</td><td style="text-align: center; font-family: monospace;">4.40</td><td style="text-align: center; font-family: monospace;">18.41</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Papel de oficina e imprenta</td><td style="text-align: center; font-family: monospace;">4.00</td><td style="text-align: center; font-family: monospace;">16.74</td><td style="text-align: center;">1.3</td></tr>
                                <tr><td>Polietileno (PE) y plásticos</td><td style="text-align: center; font-family: monospace;">11.10</td><td style="text-align: center; font-family: monospace;">46.44</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Poliestireno (Plastoformo)</td><td style="text-align: center; font-family: monospace;">9.90</td><td style="text-align: center; font-family: monospace;">41.42</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Poliuretano (Espuma flexible)</td><td style="text-align: center; font-family: monospace;">6.00</td><td style="text-align: center; font-family: monospace;">25.10</td><td style="text-align: center;">1.6</td></tr>
                                <tr><td>Resinas epóxicas y sintéticas</td><td style="text-align: center; font-family: monospace;">8.50</td><td style="text-align: center; font-family: monospace;">35.56</td><td style="text-align: center;">1.6</td></tr>
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
