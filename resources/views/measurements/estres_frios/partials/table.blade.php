    <!-- 3. Tabla Maestra de Mediciones de Estrés por Frío (WCI / Sensación Térmica) -->
    <div class="cold-stress-table-card illumination-table-card">
        <!-- Toolbar & Filtros -->
        <div class="cold-stress-toolbar illumination-toolbar">
            <div class="table-filter-pills" id="coldStressFilterGroup">
                <button type="button" class="filter-pill-btn active" onclick="filterColdStress('all', this)">
                    Todos ({{ $totalMeasurements }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterColdStress('Bajo', this)">
                    Riesgo Bajo
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterColdStress('Moderado', this)">
                    Riesgo Moderado
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterColdStress('Alto', this)">
                    Riesgo Alto / Crítico
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterColdStress('compliant', this)">
                    Conformes ({{ $compliantCount }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterColdStress('non-compliant', this)">
                    No Conformes ({{ $nonCompliantCount }})
                </button>
            </div>

            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="coldStressSearchInput" class="search-input-pill"
                    placeholder="Buscar por puesto, área, punto, personal o parámetros..."
                    onkeyup="searchColdStressLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="coldStressMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <th style="width: 120px;">Fecha / Hora</th>
                        <th style="width: 200px;">Área / Puesto</th>
                        <th style="width: 200px;">Metabolismo & Aislamiento</th>
                        <th style="width: 210px; text-align: center;">Parámetros Ambientales</th>
                        <th style="width: 160px; text-align: center;">Sensación & WCI</th>
                        <th style="width: 160px; text-align: center;">Riesgo / TLE</th>
                        <th style="width: 85px; text-align: center;">Imágenes</th>
                        <th style="width: 140px;">Registrado por</th>
                        <th style="width: 90px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="coldStressTableBody">
                    @forelse($measurements as $idx => $m)
                        @php
                            $isCompliant = $m['is_compliant'] ?? true;
                            $riesgo = $m['nivel_riesgo'] ?? 'Bajo';
                            $rawNum = (string)($m['num'] ?? $m['point_number'] ?? ($idx + 1));
                            $cleanNum = intval(preg_replace('/[^0-9]/', '', $rawNum)) ?: ($idx + 1);
                            $searchBlob = strtolower(($cleanNum) . ' ' . ($m['area'] ?? '') . ' ' . ($m['puesto_trabajo'] ?? '') . ' ' . ($m['desc_actividades'] ?? '') . ' ' . ($m['metabolismo'] ?? '') . ' ' . ($m['aislamiento'] ?? '') . ' ' . ($m['staff_name'] ?? '') . ' ' . ($m['location_description'] ?? ''));
                            $firstPhoto = !empty($m['photos']) && is_array($m['photos']) ? $m['photos'][0] : ($m['image_path'] ?? null);
                            $photoCount = !empty($m['photos']) && is_array($m['photos']) ? count($m['photos']) : ($firstPhoto ? 1 : 0);
                        @endphp
                        <tr class="cold-stress-data-row"
                            data-compliant="{{ $isCompliant ? 'compliant' : 'non-compliant' }}"
                            data-risk="{{ $riesgo }}"
                            data-search="{{ $searchBlob }}">

                            <!-- 1. N° -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $cleanNum }}
                            </td>

                            <!-- 2. Fecha / Hora -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date_formatted'] }}</span>
                                    <span class="table-time-pill" title="Hora de medición">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <span>{{ $m['time_raw'] ?: '—' }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Área / Puesto de Trabajo -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 13.5px;" title="{{ $m['puesto_trabajo'] }}">
                                        {{ $m['puesto_trabajo'] ?: 'Puesto no especificado' }}
                                    </span>
                                    <span style="font-size: 11.5px; color: #64748b; font-weight: 600;" title="Área: {{ $m['area'] }}">
                                        {{ $m['area'] ?: 'Área Operativa' }}
                                    </span>
                                </div>
                            </td>

                            <!-- 4. Metabolismo & Aislamiento -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span class="activity-name-tag" title="{{ $m['metabolismo'] }}">
                                        {{ $m['metabolismo'] }}
                                    </span>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 600;" title="Aislamiento: {{ $m['aislamiento'] }}">
                                        {{ $m['aislamiento'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- 5. Parámetros Ambientales (T. Aire, V. Viento, %HR, Presión) -->
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 3px;"
                                     title="Lecturas: Temp Aire: {{ $m['temp_c'] }}°C | Viento: {{ $m['vel_viento_ms'] }} m/s | %HR: {{ $m['hr_percent'] }}% | Presión: {{ $m['presion_mmhg'] }} mmHg">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                        <span style="font-size: 12.5px; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: #0284c7;">
                                            {{ $m['temp_c'] !== null ? number_format($m['temp_c'], 1) . '°C' : '—' }}
                                        </span>
                                        <span style="font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                            {{ $m['vel_viento_ms'] !== null ? number_format($m['vel_viento_ms'], 2) . ' m/s' : '0.20 m/s' }}
                                        </span>
                                    </div>
                                    <div style="font-size: 11px; font-family: 'JetBrains Mono', monospace; color: #64748b;">
                                        <span>HR: {{ $m['hr_percent'] !== null ? number_format($m['hr_percent'], 0) . '%' : '—' }}</span>
                                        @if($m['presion_mmhg'])
                                            <span style="color: #94a3b8;"> • </span>
                                            <span>P: {{ number_format($m['presion_mmhg'], 0) }} mmHg</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- 6. Sensación Térmica & WCI -->
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 3px;">
                                    <span style="font-family: 'JetBrains Mono', monospace; font-weight: 800; font-size: 13.5px; color: {{ ($m['sensacion_termica_c'] !== null && $m['sensacion_termica_c'] <= 0) ? '#0284c7' : '#059669' }};"
                                        title="Sensación Térmica: {{ $m['sensacion_termica_c'] }} °C">
                                        {{ $m['sensacion_termica_c'] !== null ? number_format($m['sensacion_termica_c'], 1) . ' °C' : '—' }}
                                    </span>
                                    <span class="wbgt-val-pill {{ $isCompliant ? 'compliant' : 'non-compliant' }}"
                                        style="font-size: 11px; padding: 1px 7px;"
                                        title="Índice WCI: {{ $m['indice_viento_wci'] }} W/m²">
                                        WCI: {{ $m['indice_viento_wci'] !== null ? number_format($m['indice_viento_wci'], 0) . ' W/m²' : '—' }}
                                    </span>
                                </div>
                            </td>

                            <!-- 7. Nivel de Riesgo & TLE -->
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 3px;">
                                    @if(str_contains(strtolower($riesgo), 'bajo'))
                                        <span class="table-compliance-badge badge-cumple">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                            <span>Riesgo Bajo</span>
                                        </span>
                                    @elseif(str_contains(strtolower($riesgo), 'moderado'))
                                        <span class="table-compliance-badge" style="background:#fefce8;color:#ca8a04;border:1px solid #fef08a;">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <circle cx="12" cy="12" r="10" />
                                            </svg>
                                            <span>Moderado</span>
                                        </span>
                                    @else
                                        <span class="table-compliance-badge badge-nocumple">
                                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                <line x1="18" y1="6" x2="6" y2="18" />
                                                <line x1="6" y1="6" x2="18" y2="18" />
                                            </svg>
                                            <span>{{ $riesgo }}</span>
                                        </span>
                                    @endif
                                    <span style="font-size: 10.5px; font-weight: 600; color: #64748b; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        title="{{ $m['tiempo_limite_exposicion'] }}">
                                        {{ $m['tiempo_limite_exposicion'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- 8. Imágenes / Fotos (Thumbnail idéntico a iluminación y calor) -->
                            <td style="text-align: center;">
                                @if($firstPhoto)
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $firstPhoto }}', 'Punto {{ $cleanNum }}: {{ addslashes($m['puesto_trabajo']) }}')"
                                        title="Ver fotografía ampliada">
                                        <img src="{{ $firstPhoto }}" alt="Foto">
                                        @if($photoCount > 1)
                                            <span style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">
                                                {{ $photoCount }}
                                            </span>
                                        @endif
                                    </div>
                                @else
                                    <div class="table-thumb-preview" style="cursor: default; opacity: 0.5;" title="Sin fotografía registrada">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            <!-- 9. Registrado por (Badge idéntico a iluminación) -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['staff_name'] }}">
                                    {{ $m['staff_name'] }}
                                </span>
                            </td>

                            <!-- 10. Acciones -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick='openViewMeasurementModal(@json($m))'
                                        title="Ver detalle del punto"
                                        aria-label="Ver">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>
                                    <button type="button" class="btn-admin-icon-action theme-danger"
                                        onclick="confirmDeleteMeasurement({{ $m['id'] }}, '{{ addslashes($m['puesto_trabajo']) }}')"
                                        title="Eliminar medición"
                                        aria-label="Eliminar">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyTableRow">
                            <td colspan="10">
                                <div class="table-empty-state">
                                    <div class="empty-icon-wrap" style="color: #0284c7; background: #f0f9ff;">
                                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z" />
                                            <line x1="2" y1="12" x2="6" y2="12" />
                                            <line x1="18" y1="12" x2="22" y2="12" />
                                        </svg>
                                    </div>
                                    <h3>No hay mediciones de estrés por frío registradas</h3>
                                    <p>Haz clic en "+ Nuevo Punto de Medición" para registrar la primera evaluación de estrés por frío e índice WCI.</p>
                                    <button type="button" class="btn-primary-hero-action" onclick="openCreateMeasurementModal()">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                        <span>Registrar Primera Medición</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="10" style="text-align: center; color: #64748b; padding: 36px; font-weight: 500;">
                            No se encontraron puntos de medición que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (Idéntica a Iluminación y Calor) -->
        <div class="illumination-pagination-container cold-stress-pagination-container" id="coldStressPaginationBar">
            <div class="illumination-pagination-info cold-stress-pagination-info" id="coldStressPaginationInfo">
                Mostrando <strong id="coldStressPageStart">1</strong> a <strong id="coldStressPageEnd">{{ min(10, count($measurements)) }}</strong> de <strong
                    id="coldStressPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
            </div>
            <div class="illumination-pagination-controls cold-stress-pagination-controls" id="coldStressPaginationControls">
                <!-- Generado reactivamente por JS -->
            </div>
        </div>
    </div>
