<!-- ==========================================================================
         MODAL: TABLA TÉCNICA DE ESTRÉS POR CALOR (15 PARÁMETROS COMPLETOS)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="tablesModal" role="dialog" aria-modal="true"
        aria-labelledby="tablesModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1500px; width: 98%;">
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
                        <h2 id="tablesModalTitle" style="font-size: 17px; margin: 0; color: var(--ink);">Tabla Técnica de Mediciones de Estrés por Calor</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Registro completo de parámetros termohigrométricos, actividad, vestimenta y sobrecarga térmica TGBH / WBGT</span>
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
                <div class="table-responsive-box" style="border-radius: 10px; border: 1.5px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.04); overflow-x: auto;">
                    <table class="matrix-tech-table" id="tableHeatStressTechnicalFull">
                        <thead>
                            <tr>
                                <th style="min-width: 170px; text-align: left;">PUESTO DE TRABAJO</th>
                                <th style="min-width: 120px; text-align: center;">INTERIOR / EXTERIOR</th>
                                <th style="min-width: 110px; text-align: center;">ACLIMATADO</th>
                                <th style="min-width: 120px; text-align: center;">HORA DE MEDICIÓN</th>
                                <th style="min-width: 220px; text-align: left;">DESCRIPCIÓN DE ACTIVIDADES</th>
                                <th style="min-width: 190px; text-align: left;">TIPO DE ROPA DE TRABAJO CAV °C</th>
                                <th style="min-width: 90px; text-align: center;">CAPUCHA</th>
                                <th style="min-width: 170px; text-align: left;">TASA METABÓLICA W</th>
                                <th style="min-width: 80px; text-align: center;">%HR</th>
                                <th style="min-width: 110px; text-align: center;">VEL. VIENTO (m/s)</th>
                                <th style="min-width: 95px; text-align: center;">P (mmHg)</th>
                                <th style="min-width: 95px; text-align: center;">TEMP °C</th>
                                <th style="min-width: 95px; text-align: center;">WBGT °C</th>
                                <th style="min-width: 90px; text-align: center;">WB °C</th>
                                <th style="min-width: 90px; text-align: center;">GT °C</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($measurements as $m)
                                @php
                                    $env = $m['interior_exterior'] ?? 'Interior';
                                    $isAclimatado = ($m['aclimatado'] === 'Sí' || $m['aclimatado'] === '1' || $m['aclimatado'] === 1);
                                    $hasCapucha = ($m['capucha'] === 'Sí' || $m['capucha'] === '1' || $m['capucha'] === 1);
                                @endphp
                                <tr>
                                    <!-- 1. PUESTO DE TRABAJO -->
                                    <td style="font-weight: 700; color: var(--ink);">
                                        {{ $m['puesto_trabajo'] ?: '—' }}
                                    </td>

                                    <!-- 2. INTERIOR/EXTERIOR -->
                                    <td style="text-align: center;">
                                        <span class="table-badge-env {{ strtolower($env) === 'exterior' ? 'badge-env-ext' : 'badge-env-int' }}">
                                            {{ $env }}
                                        </span>
                                    </td>

                                    <!-- 3. ACLIMATADO -->
                                    <td style="text-align: center;">
                                        <span style="font-weight: 700; color: {{ $isAclimatado ? '#059669' : '#d97706' }};">
                                            ● {{ $isAclimatado ? 'Sí' : 'No' }}
                                        </span>
                                    </td>

                                    <!-- 4. HORA DE MEDICION -->
                                    <td style="text-align: center;">
                                        <span class="table-time-pill">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                <circle cx="12" cy="12" r="10" />
                                                <polyline points="12 6 12 12 16 14" />
                                            </svg>
                                            <span>{{ $m['time_raw'] ?: '—' }}</span>
                                        </span>
                                    </td>

                                    <!-- 5. DESCRIPCIÓN DE ACTIVIDADES -->
                                    <td style="color: #334155; font-size: 12px; line-height: 1.35;">
                                        {{ $m['desc_actividades'] ?: '—' }}
                                    </td>

                                    <!-- 6. TIPO DE ROPA DE TRABAJO CAV °C -->
                                    <td style="font-size: 12px; color: #1e293b; font-weight: 600;">
                                        {{ $m['tipo_ropa_cav'] ?: 'Ropa de Trabajo' }}
                                        @if(($m['cav_ajuste_db'] ?? 0) > 0)
                                            <span style="color: #ea580c; font-weight: 700;">(+{{ number_format($m['cav_ajuste_db'], 1) }}°C)</span>
                                        @endif
                                    </td>

                                    <!-- 7. CAPUCHA -->
                                    <td style="text-align: center; font-weight: 700; color: {{ $hasCapucha ? '#0284c7' : '#64748b' }};">
                                        {{ $hasCapucha ? 'Sí' : 'No' }}
                                    </td>

                                    <!-- 8. TASA METABOLICA W -->
                                    <td style="font-size: 11.5px; color: #1e293b; font-weight: 600;">
                                        {{ $m['tasa_metabolica'] ?: '—' }}
                                    </td>

                                    <!-- 9. %HR -->
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #334155;">
                                        {{ $m['hr_percent'] !== null ? number_format($m['hr_percent'], 0) . '%' : '—' }}
                                    </td>

                                    <!-- 10. VEL. VIENTO (m/s) -->
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #334155;">
                                        {{ $m['vel_viento_ms'] !== null ? number_format($m['vel_viento_ms'], 1) : '—' }}
                                    </td>

                                    <!-- 11. P(mmHg) -->
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 600; color: #334155;">
                                        {{ $m['presion_mmhg'] !== null ? number_format($m['presion_mmhg'], 0) : '—' }}
                                    </td>

                                    <!-- 12. TEMP °C -->
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 700; color: #0284c7;">
                                        {{ $m['temp_c'] !== null ? number_format($m['temp_c'], 1) . '°' : '—' }}
                                    </td>

                                    <!-- 13. WBGT °C -->
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; font-weight: 800; color: #0f172a;">
                                        {{ $m['wbgt_c'] !== null ? number_format($m['wbgt_c'], 1) . '°' : '—' }}
                                    </td>

                                    <!-- 14. WB °C -->
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #475569;">
                                        {{ $m['wb_c'] !== null ? number_format($m['wb_c'], 1) . '°' : '—' }}
                                    </td>

                                    <!-- 15. GT °C -->
                                    <td style="text-align: center; font-family: 'JetBrains Mono', monospace; color: #475569;">
                                        {{ $m['gt_c'] !== null ? number_format($m['gt_c'], 1) . '°' : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="15" style="text-align: center; color: #94a3b8; padding: 32px;">
                                        No hay mediciones registradas para mostrar en la tabla técnica.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: space-between;">
                <span style="font-size: 12px; color: #64748b;">
                    Total de registros: <strong>{{ count($measurements) }}</strong>
                </span>
                <button type="button" class="btn-secondary-subtle" onclick="closeTablesModal()">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
