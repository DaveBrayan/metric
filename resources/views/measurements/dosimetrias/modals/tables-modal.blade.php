    <!-- ==========================================================================
         MODAL: TABLAS TÉCNICAS DE DOSIMETRÍA DE RUIDO (2 PESTAÑAS: INFORME & DATOS)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="dosimetryTablesModal" role="dialog" aria-modal="true"
        aria-labelledby="dosimetryTablesModalTitle">
        <div class="modal-dialog-ventilation" style="max-width: 1550px; width: 98%;">
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
                        <h2 id="dosimetryTablesModalTitle" style="font-size: 17px; margin: 0; color: var(--ink);">Tabla de Dosimetría de Ruido e Informe de Evaluación</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Matriz de parámetros acústicos, Leq,T, Laeq,d, Dosis de Ruido y acciones correctivas</span>
                    </div>
                </div>

                <!-- Selector de Pestañas (Informe / Datos) -->
                <div class="tables-modal-tabs-bar">
                    <button type="button" class="tab-btn-pill active" id="tabBtn_informe" onclick="switchDosimetryTableTab('informe')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                            <polyline points="10 9 9 9 8 9"/>
                        </svg>
                        <span>Informe</span>
                    </button>
                    <button type="button" class="tab-btn-pill" id="tabBtn_datos" onclick="switchDosimetryTableTab('datos')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <ellipse cx="12" cy="5" rx="9" ry="3"/>
                            <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>
                            <path d="M3 12c0 1.66 4 3 9 3s9-1.34 9-3"/>
                        </svg>
                        <span>Datos</span>
                    </button>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 12px; font-weight: 800; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px;">
                        {{ count($measurements) }} Puntos Registrados
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeDosimetryTablesModal()" aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 16px 20px; max-height: calc(88vh - 120px); overflow-y: auto;">
                
                <!-- ========================================================= -->
                <!-- PESTAÑA 1: INFORME / EVALUACIÓN DE DOSIMETRÍA             -->
                <!-- ========================================================= -->
                <div id="panel_dosimetry_informe" class="table-tab-panel active">
                    <div class="report-eval-title-banner" style="background: #e0f2fe; color: #0369a1; padding: 9px 16px; border-radius: 6px; border: 1px solid #bae6fd; font-size: 13px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; text-align: center; margin-bottom: 8px;">
                        INFORME DE MEDICIÓN DE DOSIMETRÍA DE RUIDO — EVALUACIÓN DE CONFORMIDAD OCUPACIONAL
                    </div>

                    <div class="table-responsive-box" style="margin-top: 6px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow-x: auto;">
                        <table class="matrix-tech-table matrix-report-table-dosi" id="tableDosimetriaInforme" style="width: 100%; border-collapse: collapse; min-width: 1450px;">
                            <thead>
                                <tr style="background: #e0f2fe; border-bottom: 1px solid #cbd5e1;">
                                    <th rowspan="2" style="width: 45px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Nº</th>
                                    <th rowspan="2" style="min-width: 150px; text-align: left; padding: 8px 10px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Área de Trabajo</th>
                                    <th rowspan="2" style="min-width: 140px; text-align: left; padding: 8px 10px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Punto de medición</th>
                                    <th rowspan="2" style="width: 105px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Tipo de ruido</th>
                                    <th rowspan="2" style="width: 115px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Tiempo promedio de Exposición del personal en la jornada (TPE) (Hrs)</th>
                                    <th colspan="2" style="text-align: center; padding: 6px 8px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Datos del equipo</th>
                                    <th rowspan="2" style="width: 100px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Tiempo de duración de la medición (Hrs)</th>
                                    <th rowspan="2" style="width: 105px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Nivel de presión sonora (NPS) (max.) (dB (A))</th>
                                    <th rowspan="2" style="width: 105px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Nivel de presión sonora (NPS) (min.) (dB (A))</th>
                                    <th rowspan="2" style="width: 125px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Nivel de presión sonora continuo equivalente Laeq, T (dB (A))(*)</th>
                                    <th rowspan="2" style="width: 125px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; background: #dbeafe;">Nivel de presión sonora dirario equivalente Laeq, d (dB (A))(**)</th>
                                    <th rowspan="2" style="width: 110px; text-align: center; padding: 8px 6px; font-size: 11px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1; background: #e0f2fe;">Dosis de ruido para periodos o estudios a 8 horas (***)</th>
                                    <th rowspan="2" style="min-width: 170px; text-align: left; padding: 8px 10px; font-size: 11px; font-weight: 800; color: #0f172a; border-bottom: 1px solid #cbd5e1;">Acciones a tomar en caso de superar la Dosis de Ruido Proyectado a 8 horas</th>
                                </tr>
                                <tr style="background: #e0f2fe; border-bottom: 2px solid #cbd5e1;">
                                    <th style="width: 80px; text-align: center; padding: 6px 4px; font-size: 10.5px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Ponderación</th>
                                    <th style="width: 85px; text-align: center; padding: 6px 4px; font-size: 10.5px; font-weight: 800; color: #0f172a; border-right: 1px solid #cbd5e1; border-bottom: 1px solid #cbd5e1;">Respuesta</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($measurements as $m)
                                    <tr style="border-bottom: 1px solid #cbd5e1;">
                                        <td style="text-align: center; font-weight: 700; color: #0f172a; padding: 7px 6px; border-right: 1px solid #e2e8f0;">{{ $m['num'] }}</td>
                                        <td style="font-weight: 600; color: #0f172a; padding: 7px 10px; border-right: 1px solid #e2e8f0;">{{ $m['area'] ?? '—' }}</td>
                                        <td style="color: #0f172a; padding: 7px 10px; border-right: 1px solid #e2e8f0;">{{ $m['punto_medicion'] ?? '—' }}</td>
                                        <td style="text-align: center; color: #0f172a; padding: 7px 6px; border-right: 1px solid #e2e8f0;">{{ $m['tipo_ruido'] ?? '—' }}</td>
                                        <td style="text-align: center; font-weight: 600; font-family: monospace; padding: 7px 6px; border-right: 1px solid #e2e8f0;">
                                            {{ $m['raw_tiempo_expos_h'] !== null ? ($m['raw_tiempo_expos_h'] == round($m['raw_tiempo_expos_h']) ? number_format($m['raw_tiempo_expos_h'], 0, ',', '.') : number_format($m['raw_tiempo_expos_h'], 2, ',', '.')) : '—' }}
                                        </td>
                                        <td style="text-align: center; font-weight: 700; color: #0f172a; padding: 7px 4px; border-right: 1px solid #e2e8f0;">{{ $m['ponderacion'] ?? 'A' }}</td>
                                        <td style="text-align: center; font-size: 11.5px; font-weight: 700; color: #0f172a; padding: 7px 4px; border-right: 1px solid #e2e8f0;">{{ strtoupper($m['respuesta'] ?? 'LENTA') }}</td>
                                        <td style="text-align: center; font-family: monospace; padding: 7px 6px; border-right: 1px solid #e2e8f0;">
                                            {{ $m['raw_duracion_medicion_h'] > 0 ? number_format($m['raw_duracion_medicion_h'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-family: monospace; padding: 7px 8px; border-right: 1px solid #e2e8f0;">
                                            {{ $m['raw_nps_max_db'] !== null ? number_format($m['raw_nps_max_db'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-family: monospace; padding: 7px 8px; border-right: 1px solid #e2e8f0;">
                                            {{ $m['raw_nps_min_db'] !== null ? number_format($m['raw_nps_min_db'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-weight: 700; font-family: monospace; color: #0284c7; padding: 7px 8px; border-right: 1px solid #e2e8f0;">
                                            {{ $m['raw_leq_t_db'] !== null ? number_format($m['raw_leq_t_db'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-family: monospace; color: #0369a1; background: #f0f9ff; padding: 7px 8px; border-right: 1px solid #e2e8f0;">
                                            {{ $m['laeq_d_db'] }}
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-family: monospace; color: #0f172a; background: #f8fafc; padding: 7px 8px; border-right: 1px solid #e2e8f0;">
                                            {{ $m['dosis_ruido'] }}
                                        </td>
                                        <td style="color: #334155; font-size: 12px; padding: 7px 10px;">
                                            {{ $m['raw_observations'] ?: ($m['observations'] !== 'Sin observaciones' ? $m['observations'] : '—') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="14" style="text-align: center; color: #94a3b8; padding: 24px;">
                                            No hay puntos registrados en este módulo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ========================================================= -->
                <!-- PESTAÑA 2: PÁGINA DE DATOS (MATRIZ DE REGISTRO)           -->
                <!-- ========================================================= -->
                <div id="panel_dosimetry_datos" class="table-tab-panel">
                    <div class="report-eval-title-banner" style="background: #f8fafc; color: #334155; padding: 9px 16px; border-radius: 6px; border: 1px solid #cbd5e1; font-size: 13px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase; text-align: center; margin-bottom: 8px;">
                        MATRIZ DE DATOS DE CAMPO — MONITOREO DE DOSIMETRÍA
                    </div>

                    <div class="table-responsive-box" style="margin-top: 6px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow-x: auto;">
                        <table class="matrix-tech-table matrix-data-table-dosi" id="tableDosimetriaDatos" style="width: 100%; border-collapse: collapse; min-width: 1300px;">
                            <thead>
                                <tr style="background: #ffffff; border-bottom: 2px solid #0f172a;">
                                    <th style="width: 50px; text-align: center; padding: 10px 6px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">NRO.</th>
                                    <th style="min-width: 160px; text-align: left; padding: 10px 10px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">ÁREA DE TRABAJO</th>
                                    <th style="min-width: 150px; text-align: left; padding: 10px 10px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">PUNTO DE MEDICION</th>
                                    <th style="width: 120px; text-align: center; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">TIPO DE RUIDO</th>
                                    <th style="width: 90px; text-align: center; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">TPE (Hr)</th>
                                    <th style="width: 100px; text-align: center; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">PONDERACION</th>
                                    <th style="width: 100px; text-align: center; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">RESPUESTA</th>
                                    <th style="width: 115px; text-align: center; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">TIEMPO DE MEDICIÓN (Hr)</th>
                                    <th style="width: 105px; text-align: right; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">NPS MAX (dB)</th>
                                    <th style="width: 105px; text-align: right; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">NPS MIN (dB)</th>
                                    <th style="width: 105px; text-align: right; padding: 10px 8px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">Leq,T (dB)</th>
                                    <th style="min-width: 170px; text-align: left; padding: 10px 10px; font-size: 11px; font-weight: 800; color: #000000; border: 1px solid #000000;">OBSERVACIONES</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($measurements as $m)
                                    <tr style="border-bottom: 1px solid #000000;">
                                        <td style="text-align: center; font-weight: 700; color: #000000; padding: 8px 6px; border: 1px solid #000000;">{{ $m['num'] }}</td>
                                        <td style="font-weight: 600; color: #000000; padding: 8px 10px; border: 1px solid #000000;">{{ $m['area'] ?? '—' }}</td>
                                        <td style="color: #000000; padding: 8px 10px; border: 1px solid #000000;">{{ $m['punto_medicion'] ?? '—' }}</td>
                                        <td style="text-align: center; color: #000000; padding: 8px; border: 1px solid #000000;">{{ $m['tipo_ruido'] ?? '—' }}</td>
                                        <td style="text-align: center; font-weight: 600; font-family: monospace; color: #000000; padding: 8px; border: 1px solid #000000;">
                                            {{ $m['raw_tiempo_expos_h'] !== null ? ($m['raw_tiempo_expos_h'] == round($m['raw_tiempo_expos_h']) ? number_format($m['raw_tiempo_expos_h'], 0, ',', '.') : number_format($m['raw_tiempo_expos_h'], 2, ',', '.')) : '—' }}
                                        </td>
                                        <td style="text-align: center; font-weight: 700; color: #000000; padding: 8px; border: 1px solid #000000;">{{ $m['ponderacion'] ?? 'A' }}</td>
                                        <td style="text-align: center; font-weight: 700; color: #000000; padding: 8px; border: 1px solid #000000;">{{ strtoupper($m['respuesta'] ?? 'LENTO') }}</td>
                                        <td style="text-align: center; font-family: monospace; color: #000000; padding: 8px; border: 1px solid #000000;">
                                            {{ $m['raw_duracion_medicion_h'] > 0 ? number_format($m['raw_duracion_medicion_h'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-family: monospace; color: #000000; padding: 8px; border: 1px solid #000000;">
                                            {{ $m['raw_nps_max_db'] !== null ? number_format($m['raw_nps_max_db'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-family: monospace; color: #000000; padding: 8px; border: 1px solid #000000;">
                                            {{ $m['raw_nps_min_db'] !== null ? number_format($m['raw_nps_min_db'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-weight: 800; font-family: monospace; color: #000000; padding: 8px; border: 1px solid #000000;">
                                            {{ $m['raw_leq_t_db'] !== null ? number_format($m['raw_leq_t_db'], 2, ',', '.') : '—' }}
                                        </td>
                                        <td style="color: #000000; font-size: 12px; padding: 8px 10px; border: 1px solid #000000;">
                                            {{ $m['raw_observations'] ?: ($m['observations'] !== 'Sin observaciones' ? $m['observations'] : '—') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" style="text-align: center; color: #94a3b8; padding: 24px;">
                                            No hay puntos registrados en este módulo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-primary-hero-action" onclick="closeDosimetryTablesModal()"
                    style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
            </div>
        </div>
    </div>
