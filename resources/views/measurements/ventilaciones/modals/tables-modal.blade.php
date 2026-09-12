    <!-- ==========================================================================
         MODAL: TABLAS TÉCNICAS Y EVALUACIÓN DE RIESGOS (2 PESTAÑAS: DATOS & INFORME)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="ventilationTablesModal" role="dialog" aria-modal="true"
        aria-labelledby="tablesModalTitle">
        <div class="modal-dialog-ventilation" style="max-width: 1350px; width: 98%;">
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
                        <h2 id="tablesModalTitle" style="font-size: 17px; margin: 0; color: var(--ink);">Tablas de Ventilación y Evaluación de Riesgos</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Matriz técnica de cálculo dimensional e informe de conformidad ocupacional</span>
                    </div>
                </div>

                <!-- Selector de Pestañas (Informe / Datos) -->
                <div class="tables-modal-tabs-bar">
                    <button type="button" class="tab-btn-pill active" id="tabBtn_informe" onclick="switchVentilationTableTab('informe')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span>Informe</span>
                    </button>
                    <button type="button" class="tab-btn-pill" id="tabBtn_datos" onclick="switchVentilationTableTab('datos')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <ellipse cx="12" cy="5" rx="9" ry="3"/>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                            <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/>
                        </svg>
                        <span>Datos</span>
                    </button>
                </div>

                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 800; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px;">
                        {{ $totalMeasurements }} Puntos
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeVentilationTablesModal()" aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 16px 20px; max-height: calc(88vh - 120px); overflow-y: auto;">
                
                <!-- ========================================================= -->
                <!-- PESTAÑA 1: INFORME / EVALUACIÓN DE RIESGOS                 -->
                <!-- ========================================================= -->
                <div id="panel_ventilation_informe" class="table-tab-panel active">
                    <div class="report-eval-title-banner">
                        EVALUACIÓN DE RIESGOS
                    </div>

                    <div class="table-responsive-box" style="margin-top: 8px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <table class="matrix-tech-table matrix-report-table" id="tableVentilacionInforme">
                            <thead>
                                <tr>
                                    <th style="width: 45px; text-align: center;">NRO.</th>
                                    <th style="min-width: 170px; text-align: left;">LOCAL DE TRABAJO</th>
                                    <th style="width: 110px; text-align: center;">TIPO DE VENTILACIÓN</th>
                                    <th style="width: 130px; text-align: center;">FUENTE</th>
                                    <th style="width: 95px; text-align: center;">TEMPERATURA SECA</th>
                                    <th style="width: 100px; text-align: center;">VELOCIDAD DE AIRE [m/s]</th>
                                    <th style="width: 95px; text-align: center;">ÁREA DE VENTILACIÓN [m2]</th>
                                    <th style="width: 125px; text-align: center;">CAUDAL DE EXTRACCIÓN O INYECCIÓN DE AIRE [m3/h]</th>
                                    <th style="width: 105px; text-align: center;">VOLUMEN DEL AMBIENTE [m3]</th>
                                    <th style="width: 115px; text-align: center;">NRO. DE RENOVACIONES POR HORA</th>
                                    <th style="width: 95px; text-align: center;">VALOR REFERENCIAL</th>
                                    <th style="width: 85px; text-align: center;">¿CUMPLE?</th>
                                    <th style="min-width: 180px; text-align: left;">OBSERVACIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($measurements as $m)
                                    @php
                                        $obs = '';
                                        if (!empty($m['raw_observations']) && $m['raw_observations'] !== 'Sin observaciones') {
                                            $obs = $m['raw_observations'];
                                        } elseif (!empty($m['observations']) && $m['observations'] !== 'Sin observaciones') {
                                            $obs = $m['observations'];
                                        }
                                        $cumpleStr = $m['cumple'] ?? ($m['is_compliant'] ? 'SI' : 'NO');
                                    @endphp
                                    <tr>
                                        <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ $m['num'] }}</td>
                                        <td style="font-weight: 600; color: #0f172a;">{{ $m['local_trabajo'] }}</td>
                                        <td style="text-align: center; color: #0f172a;">{{ $m['tipo_ventilacion'] }}</td>
                                        <td style="text-align: center; color: #0f172a;">{{ $m['elemento_ventilacion'] }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ $m['temperatura_seca_c'] }}</td>
                                        <td style="text-align: center; font-family: monospace; font-weight: 600; color: #0f172a;">{{ $m['vel_aire_ms'] }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ $m['area_ventilacion_m2'] }}</td>
                                        <td style="text-align: center; font-family: monospace; font-weight: 600; color: #0f172a;">{{ $m['caudal_m3h'] }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ $m['volumen_m3'] }}</td>
                                        <td style="text-align: center; font-family: monospace; font-weight: 800; color: #0f172a;">{{ $m['renovaciones_h'] }}</td>
                                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0f172a;">{{ $m['renovaciones_intervalo'] }}</td>
                                        <td style="text-align: center; font-weight: 800; color: #0f172a;">
                                            {{ $cumpleStr }}
                                        </td>
                                        <td style="font-size: 12px; font-weight: 600; color: #0f172a;">
                                            {{ $obs }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" style="text-align: center; padding: 25px; color: #94a3b8;">
                                             No hay registros de evaluación para mostrar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- PESTAÑA 2: DATOS TÉCNICOS (DIMENSIONES Y RENOVACIONES)     -->
                <!-- ========================================================= -->
                <div id="panel_ventilation_datos" class="table-tab-panel" style="display: none;">
                    <div class="tables-view-header-strip">
                        <div style="font-size: 13px; font-weight: 700; color: #334155;">
                            TABLA DE DATOS TÉCNICOS Y MEDICIONES DE VENTILACIÓN
                        </div>
                        <span style="font-size: 11.5px; color: #64748b;">Fórmula de Renovación: 3600 * ((Velocidad * Área) / Volumen)</span>
                    </div>

                    <div class="table-responsive-box" style="margin-top: 8px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
                        <table class="matrix-tech-table" id="tableVentilacionDatos">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 45px; text-align: center;">Nro.</th>
                                    <th rowspan="2" style="min-width: 170px; text-align: left;">LOCAL DE TRABAJO</th>
                                    <th rowspan="2" style="width: 95px; text-align: center;">TEMPERATURA SECA [°C]</th>
                                    <th rowspan="2" style="width: 115px; text-align: center;">VENTILACIÓN NATURAL / MECANICA</th>
                                    <th rowspan="2" style="width: 125px; text-align: center;">TIPO DE VENTILACIÓN</th>
                                    <th rowspan="2" style="width: 95px; text-align: center;">VELOCIDAD DEL AIRE [m/s]</th>
                                    <th colspan="3" style="text-align: center; background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">ÁREA DE VENTILACIÓN [m]</th>
                                    <th colspan="3" style="text-align: center; background: #f8fafc; border-bottom: 1px solid #cbd5e1;">VOLUMEN DEL AMBIENTE [m]</th>
                                    <th rowspan="2" style="width: 120px; text-align: center; background: #f1f5f9;">NRO. DE RENOVACIONES POR HORA CALCULADA</th>
                                    <th rowspan="2" style="min-width: 160px; text-align: left;">TIPO DE LOCAL</th>
                                    <th colspan="3" style="text-align: center; background: #f1f5f9; border-bottom: 1px solid #cbd5e1;">NRO. DE RENOVACIONES POR HORA REFERENCIAL</th>
                                </tr>
                                <tr>
                                    <!-- Subcolumnas Área -->
                                    <th style="width: 50px; text-align: center; background: #f1f5f9;">L</th>
                                    <th style="width: 50px; text-align: center; background: #f1f5f9;">A</th>
                                    <th style="width: 50px; text-align: center; background: #f1f5f9;">D</th>
                                    <!-- Subcolumnas Volumen -->
                                    <th style="width: 55px; text-align: center; background: #f8fafc;">LARGO</th>
                                    <th style="width: 55px; text-align: center; background: #f8fafc;">ANCHO</th>
                                    <th style="width: 55px; text-align: center; background: #f8fafc;">ALTO</th>
                                    <!-- Subcolumnas Referencial -->
                                    <th style="width: 45px; text-align: center; background: #f1f5f9;">MIN</th>
                                    <th style="width: 45px; text-align: center; background: #f1f5f9;">MAX</th>
                                    <th style="width: 70px; text-align: center; background: #f1f5f9;">INTERVALO</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($measurements as $m)
                                    <tr>
                                        <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ $m['num'] }}</td>
                                        <td style="font-weight: 600; color: #0f172a;">{{ $m['local_trabajo'] }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ $m['temperatura_seca_c'] }}</td>
                                        <td style="text-align: center; color: #0f172a;">{{ $m['tipo_ventilacion'] }}</td>
                                        <td style="text-align: center; color: #0f172a;">{{ $m['elemento_ventilacion'] }}</td>
                                        <td style="text-align: center; font-family: monospace; font-weight: 600; color: #0f172a;">{{ $m['vel_aire_ms'] }}</td>
                                        
                                        <!-- Área L, A, D -->
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ number_format((float)$m['area_largo_m'], 2) }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ number_format((float)$m['area_ancho_m'], 2) }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ number_format((float)$m['area_diametro_m'], 2) }}</td>
                                        
                                        <!-- Vol Largo, Ancho, Alto -->
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ number_format((float)$m['vol_largo_m'], 2) }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ number_format((float)$m['vol_ancho_m'], 2) }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #0f172a;">{{ number_format((float)$m['vol_alto_m'], 2) }}</td>
                                        
                                        <!-- Renov/h Calculada -->
                                        <td style="text-align: center; font-weight: 800; font-family: monospace; color: #0f172a;">
                                            {{ $m['renovaciones_h'] }}
                                        </td>
                                        
                                        <!-- Tipo de Local -->
                                        <td style="font-size: 12px; color: #0f172a;">{{ $m['tipo_local'] }}</td>
                                        
                                        <!-- Referencial Min, Max, Intervalo -->
                                        <td style="text-align: center; font-family: monospace; font-weight: 600; color: #0f172a;">{{ $m['renovaciones_min'] ?? '—' }}</td>
                                        <td style="text-align: center; font-family: monospace; font-weight: 600; color: #0f172a;">{{ $m['renovaciones_max'] ?? '—' }}</td>
                                        <td style="text-align: center; font-family: monospace; font-weight: 700; color: #0f172a;">{{ $m['renovaciones_intervalo'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="17" style="text-align: center; padding: 25px; color: #94a3b8;">
                                            No hay registros de ventilación para mostrar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-primary-hero-action" onclick="closeVentilationTablesModal()" style="padding: 8px 22px; font-size: 13px;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
