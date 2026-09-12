    <!-- ==========================================================================
         MODAL: TABLA TÉCNICA E INFORME DE ILUMINACIÓN OCUPACIONAL (PESTAÑA INFORME)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="illuminationTablesModal" role="dialog" aria-modal="true"
        aria-labelledby="illuminationTablesModalTitle">
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
                        <h2 id="illuminationTablesModalTitle" style="font-size: 17px; margin: 0; color: var(--ink);">Tabla de Iluminación Ocupacional e Informe de Evaluación</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Matriz de lecturas de iluminancia (Lux), resultados estadísticos y verificación de cumplimiento</span>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 12px; font-weight: 800; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px;">
                        {{ $totalMeasurements }} Puntos Registrados
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeIlluminationTablesModal()" aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 16px 20px; max-height: calc(88vh - 120px); overflow-y: auto;">
                
                <!-- ========================================================= -->
                <!-- PESTAÑA ÚNICA: INFORME / EVALUACIÓN DE ILUMINANCIA        -->
                <!-- ========================================================= -->
                <div id="panel_illumination_informe" class="table-tab-panel active">
                    <div class="report-eval-title-banner">
                        INFORME DE MEDICIÓN DE ILUMINANCIA (LUX) — EVALUACIÓN DE CONFORMIDAD
                    </div>

                    <div class="table-responsive-box" style="margin-top: 10px; border-radius: 8px; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow-x: auto;">
                        <table class="matrix-tech-table matrix-report-table" id="tableIluminacionInforme">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="width: 40px; text-align: center;">N°</th>
                                    <th rowspan="2" style="min-width: 170px; text-align: left;">Área</th>
                                    <th rowspan="2" style="min-width: 160px; text-align: left;">Puesto de trabajo</th>
                                    <th rowspan="2" style="min-width: 150px; text-align: left;">Punto de medición</th>
                                    <th rowspan="2" style="min-width: 180px; text-align: left;">Descripción de la actividad</th>
                                    <th rowspan="2" style="width: 100px; text-align: center;">Horario de medición</th>
                                    <th rowspan="2" style="width: 110px; text-align: center;">Tipo de iluminación</th>
                                    <th rowspan="2" style="width: 120px; text-align: right;">Nivel iluminancia requerido (lux)</th>
                                    <th colspan="16" style="text-align: center;">Medición de iluminancia (Lux)</th>
                                    <th colspan="3" style="text-align: center;">Resultados</th>
                                    <th rowspan="2" style="width: 110px; text-align: center;">Cumple/no cumple el valor</th>
                                    <th rowspan="2" style="min-width: 180px; text-align: left;">Observaciones</th>
                                </tr>
                                <tr>
                                    <th style="width: 45px; text-align: center;">M1</th>
                                    <th style="width: 45px; text-align: center;">M2</th>
                                    <th style="width: 45px; text-align: center;">M3</th>
                                    <th style="width: 45px; text-align: center;">M4</th>
                                    <th style="width: 45px; text-align: center;">M5</th>
                                    <th style="width: 45px; text-align: center;">M6</th>
                                    <th style="width: 45px; text-align: center;">M7</th>
                                    <th style="width: 45px; text-align: center;">M8</th>
                                    <th style="width: 45px; text-align: center;">M9</th>
                                    <th style="width: 45px; text-align: center;">M10</th>
                                    <th style="width: 45px; text-align: center;">M11</th>
                                    <th style="width: 45px; text-align: center;">M12</th>
                                    <th style="width: 45px; text-align: center;">M13</th>
                                    <th style="width: 45px; text-align: center;">M14</th>
                                    <th style="width: 45px; text-align: center;">M15</th>
                                    <th style="width: 45px; text-align: center;">M16</th>
                                    <th style="width: 60px; text-align: right;">Min</th>
                                    <th style="width: 60px; text-align: right;">Max</th>
                                    <th style="width: 70px; text-align: right;">Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($measurements as $m)
                                    @php
                                        // Extraer lecturas numéricas M1-M16
                                        $readings = [];
                                        if (!empty($m['readings']) && is_array($m['readings'])) {
                                            foreach ($m['readings'] as $val) {
                                                if ($val !== null && $val !== '' && is_numeric($val)) {
                                                    $readings[] = (float)$val;
                                                }
                                            }
                                        }
                                        if (empty($readings) && isset($m['raw_measured_lux']) && (float)$m['raw_measured_lux'] > 0) {
                                            $readings = [(float)$m['raw_measured_lux']];
                                        }

                                        $hasReadings = count($readings) > 0;
                                        $minVal = $hasReadings ? min($readings) : (isset($m['raw_measured_lux']) ? (float)$m['raw_measured_lux'] : null);
                                        $maxVal = $hasReadings ? max($readings) : (isset($m['raw_measured_lux']) ? (float)$m['raw_measured_lux'] : null);
                                        $avgVal = $hasReadings ? (array_sum($readings) / count($readings)) : (isset($m['raw_measured_lux']) ? (float)$m['raw_measured_lux'] : 0);
                                        $reqVal = isset($m['raw_required_lux']) ? (float)$m['raw_required_lux'] : (float)($m['required_lux'] ?? 0);

                                        // Evaluación: El promedio debe ser mayor o igual al nivel de iluminancia requerido
                                        $isCumple = $avgVal >= $reqVal;
                                        $cumpleText = $isCumple ? 'Cumple' : 'No cumple';

                                        $obs = '';
                                        if (!empty($m['raw_observations']) && $m['raw_observations'] !== 'Sin observaciones') {
                                            $obs = $m['raw_observations'];
                                        } elseif (!empty($m['observations']) && $m['observations'] !== 'Sin observaciones') {
                                            $obs = $m['observations'];
                                        }
                                    @endphp
                                    <tr>
                                        <td style="text-align: center; font-weight: 700; color: #0f172a;">{{ $m['num'] }}</td>
                                        <td style="font-weight: 600; color: #0f172a;">{{ $m['area'] ?? '—' }}</td>
                                        <td style="color: #0f172a;">{{ $m['workstation'] ?? '—' }}</td>
                                        <td style="color: #0f172a;">{{ $m['measurement_point'] ?? '—' }}</td>
                                        <td style="color: #0f172a;">{{ $m['activity_description'] ?? '—' }}</td>
                                        <td style="text-align: center; color: #0f172a;">{{ $m['time'] ?? '—' }}</td>
                                        <td style="text-align: center; color: #0f172a;">{{ $m['lighting_type'] ?? '—' }}</td>
                                        <td style="text-align: right; font-weight: 600; color: #0f172a;">
                                            {{ number_format($reqVal, 2, ',', '.') }}
                                        </td>

                                        {{-- Columnas M1 a M16 --}}
                                        @for($k = 0; $k < 16; $k++)
                                            <td style="text-align: right; color: #0f172a; font-family: monospace;">
                                                {{ isset($readings[$k]) ? number_format($readings[$k], 1, ',', '.') : '' }}
                                            </td>
                                        @endfor

                                        {{-- Resultados: Min, Max, Promedio --}}
                                        <td style="text-align: right; font-weight: 600; color: #0f172a; font-family: monospace;">
                                            {{ $minVal !== null ? number_format($minVal, 1, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-weight: 600; color: #0f172a; font-family: monospace;">
                                            {{ $maxVal !== null ? number_format($maxVal, 1, ',', '.') : '—' }}
                                        </td>
                                        <td style="text-align: right; font-weight: 700; color: #0f172a; font-family: monospace;">
                                            {{ number_format($avgVal, 1, ',', '.') }}
                                        </td>

                                        {{-- Cumple/no cumple el valor --}}
                                        <td style="text-align: center; font-weight: 700; color: {{ $isCumple ? '#059669' : '#dc2626' }};">
                                            {{ $cumpleText }}
                                        </td>

                                        {{-- Observaciones --}}
                                        <td style="color: #0f172a;">{{ $obs }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="28" style="text-align: center; color: #94a3b8; padding: 24px;">
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
                <button type="button" class="btn-primary-hero-action" onclick="closeIlluminationTablesModal()"
                    style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
            </div>
        </div>
    </div>
