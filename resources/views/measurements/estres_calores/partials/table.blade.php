    <!-- 3. Tabla Maestra de Mediciones de Estrés por Calor (TGBH / WBGT) -->
    <div class="heat-stress-table-card">
        <!-- Toolbar & Filtros -->
        <div class="heat-stress-toolbar">
            <div class="table-filter-pills" id="heatStressFilterGroup">
                <button type="button" class="filter-pill-btn active" onclick="filterHeatStress('all', this)">
                    Todos ({{ $totalMeasurements }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterHeatStress('Interior', this)">
                    Interior
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterHeatStress('Exterior', this)">
                    Exterior
                </button>
            </div>

            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="heatStressSearchInput" class="search-input-pill"
                    placeholder="Buscar por puesto, actividad, entorno, personal o parámetros..."
                    onkeyup="searchHeatStressLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="heatStressMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <th style="width: 125px;">Fecha / Hora</th>
                        <th style="width: 200px;">Puesto / Entorno</th>
                        <th style="width: 220px;">Actividad & Vestimenta</th>
                        <th style="width: 240px; text-align: center;">Tasa Metabólica & Mediciones (TGBH)</th>
                        <th style="width: 85px; text-align: center;">Imágenes</th>
                        <th style="width: 140px;">Registrado por</th>
                        <th style="width: 90px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="heatStressTableBody">
                    @forelse($measurements as $m)
                        @php
                            $isCompliant = $m['is_compliant'] ?? true;
                            $env = $m['interior_exterior'] ?? 'Interior';
                            $searchBlob = strtolower(($m['num'] ?? '') . ' ' . ($m['puesto_trabajo'] ?? '') . ' ' . ($m['desc_actividades'] ?? '') . ' ' . ($m['tipo_ropa_cav'] ?? '') . ' ' . ($m['interior_exterior'] ?? '') . ' ' . ($m['tasa_metabolica'] ?? '') . ' ' . ($m['staff_name'] ?? '') . ' ' . ($m['location_description'] ?? ''));
                            $firstPhoto = !empty($m['photos']) && is_array($m['photos']) ? $m['photos'][0] : null;
                            $photoCount = !empty($m['photos']) && is_array($m['photos']) ? count($m['photos']) : 0;
                            $isAclimatado = ($m['aclimatado'] === 'Sí' || $m['aclimatado'] === '1' || $m['aclimatado'] === 1);
                        @endphp
                        <tr class="heat-stress-data-row"
                            data-compliant="{{ $isCompliant ? 'compliant' : 'non-compliant' }}"
                            data-environment="{{ $env }}"
                            data-search="{{ $searchBlob }}">

                            <!-- 1. N° -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $m['num'] }}
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

                            <!-- 3. Puesto / Entorno (Puesto de trabajo arriba, Entorno abajo) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 13.5px;" title="{{ $m['puesto_trabajo'] }}">
                                        {{ $m['puesto_trabajo'] ?: 'Puesto no especificado' }}
                                    </span>
                                    <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                        <span class="table-badge-env {{ strtolower($env) === 'exterior' ? 'badge-env-ext' : 'badge-env-int' }}" style="width: fit-content;">
                                            {{ $env }}
                                        </span>
                                        <span style="font-size: 11px; font-weight: 600; color: {{ $isAclimatado ? '#059669' : '#d97706' }};"
                                            title="Estado de aclimatación del trabajador">
                                            ● {{ $isAclimatado ? 'Aclimatado' : 'No Aclimatado' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- 4. Actividad & Vestimenta (Descripción, CAV y Capucha) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <span class="activity-name-tag" title="{{ $m['desc_actividades'] }}">
                                        {{ $m['desc_actividades'] ?: 'Actividad Operativa' }}
                                    </span>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 600;"
                                        title="Tipo de Ropa: {{ $m['tipo_ropa_cav'] }} | Ajuste CAV: {{ $m['cav_ajuste_db'] }}°C | Capucha: {{ $m['capucha'] }}">
                                        {{ $m['tipo_ropa_cav'] }} {{ $m['cav_ajuste_db'] > 0 ? '(+' . $m['cav_ajuste_db'] . '°C CAV)' : '' }} {{ $m['capucha'] === 'Sí' ? '• Con Capucha' : '' }}
                                    </span>
                                </div>
                            </td>

                            <!-- 5. Tasa Metabólica & Mediciones (TGBH, Temp, HR, Viento, P, WB, GT) -->
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 3px;"
                                     title="Lecturas: Temp Aire: {{ $m['temp_c'] }}°C | %HR: {{ $m['hr_percent'] }}% | Viento: {{ $m['vel_viento_ms'] }} m/s | Presión: {{ $m['presion_mmhg'] }} mmHg | Tbh: {{ $m['wb_c'] }}°C | Tg: {{ $m['gt_c'] }}°C">
                                    <div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
                                        <span style="font-size: 11.5px; font-weight: 700; color: #1e293b;">
                                            {{ $m['tasa_metabolica'] }}
                                        </span>
                                        <span class="wbgt-val-pill {{ $isCompliant ? 'compliant' : 'non-compliant' }}"
                                            style="font-size: 12px; padding: 1px 7px;"
                                            title="TGBH / WBGT: {{ $m['wbgt_c'] }}°C">
                                            WBGT: {{ $m['wbgt_c'] !== null ? number_format($m['wbgt_c'], 1) . ' °C' : '—' }}
                                        </span>
                                    </div>
                                    <div style="font-size: 11px; font-family: 'JetBrains Mono', monospace; color: #475569;">
                                        <span style="color: #0284c7; font-weight: 700;">{{ $m['temp_c'] !== null ? number_format($m['temp_c'], 1) . '°C' : '—' }}</span>
                                        <span style="color: #94a3b8;"> • </span>
                                        <span>HR: {{ $m['hr_percent'] !== null ? number_format($m['hr_percent'], 0) . '%' : '—' }}</span>
                                        <span style="color: #94a3b8;"> • </span>
                                        <span>V: {{ $m['vel_viento_ms'] !== null ? number_format($m['vel_viento_ms'], 1) . ' m/s' : '—' }}</span>
                                    </div>
                                </div>
                            </td>

                            <!-- 6. Imágenes / Fotos (Thumbnail idéntico a iluminación) -->
                            <td style="text-align: center;">
                                @if($firstPhoto)
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $firstPhoto }}', '{{ addslashes($m['puesto_trabajo']) }} - {{ addslashes($m['desc_actividades']) }}')"
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

                            <!-- 7. Registrado por (Badge idéntico a iluminación) -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['staff_name'] }}">
                                    {{ $m['staff_name'] }}
                                </span>
                            </td>

                            <!-- 8. Acciones -->
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
                            <td colspan="8">
                                <div class="table-empty-state">
                                    <div class="empty-icon-wrap" style="color: #0284c7; background: #f0f9ff;">
                                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                            <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z" />
                                        </svg>
                                    </div>
                                    <h3>No hay mediciones de estrés por calor registradas</h3>
                                    <p>Haz clic en "+ Nuevo Punto de Medición" para registrar la primera evaluación de sobrecarga térmica TGBH / WBGT.</p>
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
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 36px; font-weight: 500;">
                            No se encontraron puntos de medición que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (Idéntica a Iluminación) -->
        <div class="illumination-pagination-container heat-stress-pagination-container" id="heatStressPaginationBar">
            <div class="illumination-pagination-info heat-stress-pagination-info" id="heatStressPaginationInfo">
                Mostrando <strong id="heatStressPageStart">1</strong> a <strong id="heatStressPageEnd">{{ min(10, count($measurements)) }}</strong> de <strong
                    id="heatStressPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
            </div>
            <div class="illumination-pagination-controls heat-stress-pagination-controls" id="heatStressPaginationControls">
                <!-- Generado reactivamente por JS -->
            </div>
        </div>
    </div>
