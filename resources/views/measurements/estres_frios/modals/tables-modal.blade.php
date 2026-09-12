    <!-- ==========================================================================
         MODAL: TABLA TÉCNICA E INFORME DE ESTRÉS POR FRÍO (WCI / SENSACIÓN TÉRMICA)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="tablesModal" role="dialog" aria-modal="true"
        aria-labelledby="tablesModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1450px; width: 98%;">
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
                        <h2 id="tablesModalTitle" style="font-size: 17px; margin: 0; color: var(--ink);">Matriz Técnica de Estrés Térmico por Frío e Informe de Evaluación</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Evaluación de Índice de Enfriamiento por Viento (WCI), Sensación Térmica y TLE Normativo</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 12px; font-weight: 800; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px;">
                        {{ $totalMeasurements }} Puntos Registrados
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeTablesModal()" aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 16px 20px; max-height: calc(88vh - 120px); overflow-y: auto;">
                
                <!-- Pestañas de Navegación del Modal de Tablas -->
                <div style="display: flex; gap: 10px; border-bottom: 1.5px solid #e2e8f0; margin-bottom: 16px; padding-bottom: 8px;">
                    <button type="button" class="filter-pill-btn active" id="tabBtnColdReport" onclick="switchColdStressTableTab('report', this)">
                        <span>Matriz de Evaluación (Informe)</span>
                    </button>
                    <button type="button" class="filter-pill-btn" id="tabBtnColdWci" onclick="switchColdStressTableTab('wci', this)">
                        <span>Criterios WCI & Sensación Térmica</span>
                    </button>
                    <button type="button" class="filter-pill-btn" id="tabBtnColdTle" onclick="switchColdStressTableTab('tle', this)">
                        <span>Régimen de Trabajo y Calentamiento (TLE)</span>
                    </button>
                </div>

                <!-- 1. Matriz de Evaluación (Informe) -->
                <div id="panelColdReport" class="table-tab-panel active">
                    <div class="report-eval-title-banner" style="background: #0284c7;">
                        INFORME DE MEDICIÓN DE ESTRÉS POR FRÍO (WCI) — EVALUACIÓN DE CONFORMIDAD
                    </div>

                    <div class="table-responsive-box" style="margin-top: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow-x: auto;">
                        <table class="matrix-tech-table matrix-report-table" id="tableColdStressReport">
                            <thead>
                                <tr>
                                    <th style="width: 40px; text-align: center;">N°</th>
                                    <th style="min-width: 140px; text-align: left;">Área</th>
                                    <th style="min-width: 140px; text-align: left;">Puesto de Trabajo</th>
                                    <th style="min-width: 100px; text-align: center;">Código</th>
                                    <th style="min-width: 140px; text-align: left;">Metabolismo</th>
                                    <th style="min-width: 140px; text-align: left;">Aislamiento</th>
                                    <th style="width: 80px; text-align: center;">T. Aire (°C)</th>
                                    <th style="width: 85px; text-align: center;">Viento (m/s)</th>
                                    <th style="width: 75px; text-align: center;">HR (%)</th>
                                    <th style="width: 95px; text-align: center;">Sensación (°C)</th>
                                    <th style="width: 95px; text-align: center;">WCI (W/m²)</th>
                                    <th style="width: 110px; text-align: center;">Nivel de Riesgo</th>
                                    <th style="min-width: 180px; text-align: left;">TLE Máximo</th>
                                    <th style="width: 110px; text-align: center;">Conformidad</th>
                                    <th style="min-width: 150px; text-align: left;">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($measurements as $m)
                                    @php
                                        $isCompliant = $m['is_compliant'] ?? true;
                                        $riesgo = $m['nivel_riesgo'] ?? 'Bajo';
                                    @endphp
                                    <tr>
                                        <td style="text-align: center; font-weight: 800; color: #64748b;">{{ $m['num'] }}</td>
                                        <td style="font-weight: 700; color: var(--ink);">{{ $m['area'] }}</td>
                                        <td style="font-weight: 600; color: #334155;">{{ $m['puesto_trabajo'] }}</td>
                                        <td style="text-align: center;"><span class="table-code-badge">{{ $m['point_number'] }}</span></td>
                                        <td style="font-size: 11.5px; color: #475569;">{{ $m['metabolismo'] }}</td>
                                        <td style="font-size: 11.5px; color: #475569;">{{ $m['aislamiento'] }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 700;">{{ $m['temp_c'] !== null ? number_format($m['temp_c'], 1) : '—' }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace;">{{ $m['vel_viento_ms'] !== null ? number_format($m['vel_viento_ms'], 2) : '—' }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace;">{{ $m['hr_percent'] !== null ? number_format($m['hr_percent'], 1) : '—' }}</td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #0284c7;">
                                            {{ $m['sensacion_termica_c'] !== null ? number_format($m['sensacion_termica_c'], 1) . ' °C' : '—' }}
                                        </td>
                                        <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 700;">
                                            {{ $m['indice_viento_wci'] !== null ? number_format($m['indice_viento_wci'], 0) : '—' }}
                                        </td>
                                        <td style="text-align: center;">
                                            @if(str_contains(strtolower($riesgo), 'bajo'))
                                                <span class="table-compliance-badge badge-cumple">Riesgo Bajo</span>
                                            @elseif(str_contains(strtolower($riesgo), 'moderado'))
                                                <span class="table-compliance-badge" style="background:#fefce8;color:#ca8a04;border:1px solid #fef08a;">Moderado</span>
                                            @else
                                                <span class="table-compliance-badge badge-nocumple">{{ $riesgo }}</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 11.5px; font-weight: 600; color: #334155;">{{ $m['tiempo_limite_exposicion'] }}</td>
                                        <td style="text-align: center;">
                                            @if($isCompliant)
                                                <span class="table-compliance-badge badge-cumple">Cumple</span>
                                            @else
                                                <span class="table-compliance-badge badge-nocumple">Riesgo Alto</span>
                                            @endif
                                        </td>
                                        <td style="font-size: 11.5px; color: #64748b;">{{ $m['observations'] ?: 'Sin observaciones' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="15" style="text-align: center; color: #94a3b8; padding: 24px;">No hay mediciones registradas para mostrar en la tabla técnica.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2. Pestaña: Criterios WCI & Sensación Térmica -->
                <div id="panelColdWci" class="table-tab-panel" style="display: none;">
                    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 18px;">
                        <h3 style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                            Escala del Índice de Enfriamiento por Viento (WCI - Siple & Passel / ACGIH)
                        </h3>
                        <table class="matrix-tech-table">
                            <thead>
                                <tr>
                                    <th>Índice WCI (W/m²)</th>
                                    <th>Sensación Térmica Estimada</th>
                                    <th>Nivel de Riesgo para la Salud</th>
                                    <th>Efecto Fisiológico Principal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>&lt; 1000</strong></td>
                                    <td>Agradable a Fresco (&gt; -5 °C)</td>
                                    <td><span class="table-compliance-badge badge-cumple">Riesgo Bajo</span></td>
                                    <td>Condición confortable con vestimenta normal.</td>
                                </tr>
                                <tr>
                                    <td><strong>1000 a 1200</strong></td>
                                    <td>Muy Frío (-5 °C a -15 °C)</td>
                                    <td><span class="table-compliance-badge" style="background:#fefce8;color:#ca8a04;border:1px solid #fef08a;">Moderado</span></td>
                                    <td>Desagradable, enfriamiento de manos y pies.</td>
                                </tr>
                                <tr>
                                    <td><strong>1200 a 1400</strong></td>
                                    <td>Extremadamente Frío (-15 °C a -25 °C)</td>
                                    <td><span class="table-compliance-badge badge-nocumple">Alto</span></td>
                                    <td>Congelación de piel expuesta en 1 hora con viento.</td>
                                </tr>
                                <tr>
                                    <td><strong>1400 a 1600</strong></td>
                                    <td>Gélido / Peligroso (-25 °C a -35 °C)</td>
                                    <td><span class="table-compliance-badge badge-nocumple">Muy Alto</span></td>
                                    <td>Congelación de piel expuesta en 1 minuto.</td>
                                </tr>
                                <tr>
                                    <td><strong>&gt; 1600</strong></td>
                                    <td>Crítico / Severo (&lt; -35 °C)</td>
                                    <td><span class="table-compliance-badge badge-nocumple">Crítico / Severo</span></td>
                                    <td>Congelación de piel expuesta en 30 segundos.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 3. Pestaña: Régimen de Trabajo y Calentamiento (TLE) -->
                <div id="panelColdTle" class="table-tab-panel" style="display: none;">
                    <div style="background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 18px;">
                        <h3 style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 12px;">
                            Esquema de Trabajo y Calentamiento por Turno de 4 Horas (ACGIH TLVs Frío)
                        </h3>
                        <table class="matrix-tech-table">
                            <thead>
                                <tr>
                                    <th>Rango Temperatura Aire (°C)</th>
                                    <th>Sin Viento (0 a 2 m/s)</th>
                                    <th>Viento Ligero (2 a 4 m/s)</th>
                                    <th>Viento Fuerte (&gt; 4 m/s)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>-10 °C a -25 °C</strong></td>
                                    <td>Turno normal (1 pausa de 10 min)</td>
                                    <td>Turno normal (1 pausa de 10 min)</td>
                                    <td>4 pausas de 10 min</td>
                                </tr>
                                <tr>
                                    <td><strong>-26 °C a -35 °C</strong></td>
                                    <td>Turno normal (1 pausa de 10 min)</td>
                                    <td>4 pausas de 10 min</td>
                                    <td>2 pausas de 15 min / Max 2 horas</td>
                                </tr>
                                <tr>
                                    <td><strong>-36 °C a -43 °C</strong></td>
                                    <td>4 pausas de 10 min</td>
                                    <td>2 pausas de 15 min / Max 2 horas</td>
                                    <td>1 periodo max 30 min</td>
                                </tr>
                                <tr>
                                    <td><strong>&lt; -43 °C</strong></td>
                                    <td>Suspender trabajos exteriores</td>
                                    <td>Suspender trabajos exteriores</td>
                                    <td>Prohibido trabajo no esencial</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-secondary-subtle" onclick="closeTablesModal()">Cerrar</button>
            </div>
        </div>
    </div>
