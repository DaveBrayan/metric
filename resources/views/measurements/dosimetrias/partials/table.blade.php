    <!-- 3. Tabla Maestra de Mediciones de Dosimetría -->
    <div class="dosimetry-table-card illumination-table-card">
        <!-- Toolbar & Filtros -->
        <div class="dosimetry-toolbar illumination-toolbar">
            <div class="table-filter-pills" id="dosimetryFilterGroup">
                <button type="button" class="filter-pill-btn active" onclick="filterDosimetry('all', this)">
                    Todos ({{ count($measurements) }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterDosimetry('SI', this)">
                    Cumple ({{ $stats['cumple_count'] ?? 0 }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterDosimetry('NO', this)">
                    No Cumple ({{ $stats['no_cumple_count'] ?? 0 }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterDosimetry('Estable', this)">
                    Estable
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterDosimetry('Fluctuante', this)">
                    Fluctuante
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterDosimetry('Impacto', this)">
                    Impacto
                </button>
            </div>

            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="dosimetrySearchInput" class="search-input-pill"
                    placeholder="Buscar por área, punto, tipo de ruido, técnico o ubicación..."
                    onkeyup="searchDosimetryLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="dosimetryMasterTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">N°</th>
                        <th style="width: 105px;">Fecha / Hora</th>
                        <th style="width: 160px;">Área de Trabajo</th>
                        <th style="width: 140px;">Punto Medición</th>
                        <th style="width: 120px;">Tipo Ruido</th>
                        <th style="width: 85px; text-align: center;">TPE (h)</th>
                        <th style="width: 100px; text-align: center;">Pond. / Resp.</th>
                        <th style="width: 90px; text-align: center;">T. Med. (h)</th>
                        <th style="width: 95px; text-align: right;">NPS Max</th>
                        <th style="width: 95px; text-align: right;">NPS Min</th>
                        <th style="width: 105px; text-align: right;">Leq,T (dB)</th>
                        <th style="width: 95px; text-align: center;">LMP (dBA)</th>
                        <th style="width: 90px; text-align: center;">Cumple</th>
                        <th style="width: 75px; text-align: center;">Imágenes</th>
                        <th style="width: 140px;">Registrado por</th>
                        <th style="width: 90px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="dosimetryTableBody">
                    @forelse($measurements as $m)
                        <tr class="dosimetry-data-row"
                            data-cumple="{{ $m['cumple'] }}"
                            data-ruido="{{ $m['tipo_ruido'] }}"
                            data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . $m['punto_medicion'] . ' ' . $m['tipo_ruido'] . ' ' . $m['ponderacion'] . ' ' . $m['respuesta'] . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">

                            <!-- 1. N° -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13px; text-align: center;">
                                {{ $m['num'] }}
                            </td>

                            <!-- 2. Fecha / Hora -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-size: 12px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
                                    <span class="table-time-pill" title="Hora de medición">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <span>{{ $m['time'] }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Área de Trabajo -->
                            <td>
                                <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $m['area'] }}</span>
                            </td>

                            <!-- 4. Punto de Medición -->
                            <td>
                                <span style="font-weight: 600; color: #1e293b; font-size: 12.5px;" title="{{ $m['punto_medicion'] }}">
                                    {{ $m['punto_medicion'] }}
                                </span>
                            </td>

                            <!-- 5. Tipo Ruido -->
                            <td>
                                <span class="noise-type-tag {{ strtolower(str_replace(' ', '-', $m['tipo_ruido'])) }}">
                                    {{ $m['tipo_ruido'] }}
                                </span>
                            </td>

                            <!-- 6. TPE (h) -->
                            <td style="text-align: center;">
                                <span class="calc-mono-pill">{{ $m['tiempo_expos_h'] }}h</span>
                            </td>

                            <!-- 7. Pond. / Resp. -->
                            <td style="text-align: center;">
                                <span style="font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    {{ $m['ponderacion'] }} / {{ $m['respuesta'] }}
                                </span>
                            </td>

                            <!-- 8. T. Medición (h) -->
                            <td style="text-align: center;">
                                <span class="calc-mono-pill">{{ $m['duracion_medicion_h'] !== '—' ? $m['duracion_medicion_h'] . 'h' : '—' }}</span>
                            </td>

                            <!-- 9. NPS Max (dB) -->
                            <td style="text-align: right; font-family: monospace; font-size: 12.5px; font-weight: 600; color: #0f172a;">
                                {{ $m['nps_max_db'] }}
                            </td>

                            <!-- 10. NPS Min (dB) -->
                            <td style="text-align: right; font-family: monospace; font-size: 12.5px; font-weight: 600; color: #0f172a;">
                                {{ $m['nps_min_db'] }}
                            </td>

                            <!-- 11. Leq,T (dB) -->
                            <td style="text-align: right;">
                                <span class="leq-measured-badge {{ $m['cumple'] === 'SI' ? 'compliant' : 'non-compliant' }}" title="Nivel Sonoro Equivalente Leq,T">
                                    {{ $m['leq_t_db'] }} dB
                                </span>
                            </td>

                            <!-- 12. LMP (dBA) -->
                            <td style="text-align: center;">
                                <span class="lmp-req-code">{{ $m['lmp'] }} dBA</span>
                            </td>

                            <!-- 13. Cumple (SI / NO) -->
                            <td style="text-align: center;">
                                @if($m['cumple'] === 'SI')
                                    <span class="badge-compliance-ok" title="Cumple con el Límite Máximo Permisible">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <polyline points="20 6 9 17 4 12" />
                                        </svg>
                                        <span>SI</span>
                                    </span>
                                @elseif($m['cumple'] === 'NO')
                                    <span class="badge-compliance-danger" title="Supera el Límite Máximo Permisible">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                            <line x1="18" y1="6" x2="6" y2="18" />
                                            <line x1="6" y1="6" x2="18" y2="18" />
                                        </svg>
                                        <span>NO</span>
                                    </span>
                                @else
                                    <span class="badge-compliance-neutral">—</span>
                                @endif
                            </td>

                            <!-- 14. Imágenes -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['punto_medicion']) }}')"
                                        title="Ver fotografía ampliada">
                                        <img src="{{ $m['image_path'] }}" alt="Foto">
                                        @if(!empty($m['images_count']) && $m['images_count'] > 1)
                                            <span class="thumb-count-badge">{{ $m['images_count'] }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="table-thumb-preview" style="cursor: default; opacity: 0.5;"
                                        title="Sin fotografía registrada">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            <!-- 15. Registrado por -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 16. Acciones -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 6px;">
                                    <!-- Ver / Editar Detalle -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick='openViewMeasurementModal(@json($m))' title="Ver detalle del punto"
                                        aria-label="Ver">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger"
                                        onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['num'] }}')"
                                        title="Eliminar Punto" aria-label="Eliminar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            <line x1="10" y1="11" x2="10" y2="17" />
                                            <line x1="14" y1="11" x2="14" y2="17" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyTableRow">
                            <td colspan="16" style="text-align: center; color: #64748b; padding: 36px;">
                                No se han registrado puntos de medición en este módulo de dosimetría.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="16" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron puntos de medición que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (10 por página) -->
        <div class="dosimetry-pagination-container illumination-pagination-container" id="dosimetryPaginationBar">
            <div class="dosimetry-pagination-info illumination-pagination-info" id="dosimetryPaginationInfo">
                Mostrando <strong id="dosiPageStart">1</strong> a <strong id="dosiPageEnd">10</strong> de <strong
                    id="dosiPageTotal">{{ count($measurements) }}</strong> puntos de medición
            </div>
            <div class="dosimetry-pagination-controls illumination-pagination-controls" id="dosimetryPaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>
