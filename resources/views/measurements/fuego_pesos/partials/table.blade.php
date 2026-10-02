    <!-- 3. Tabla Maestra de Sectores de Carga de Fuego por Peso -->
    <div class="illumination-table-card">
        <!-- Toolbar (Buscador a la Derecha) -->
        <div class="illumination-toolbar" style="display: flex; justify-content: flex-end;">
            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="fireSearchInput" class="search-input-pill"
                    placeholder="Buscar por macroárea, sector, material, equipos o evaluador..."
                    onkeyup="searchFireWeightLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="fireMasterTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">N°</th>
                        <th style="width: 105px;">Fecha / Hora</th>
                        <th style="width: 180px;">Macroárea / Sector</th>
                        <th style="width: 125px;">Dimensiones</th>
                        <th style="width: 200px;">Materiales & Equipos</th>
                        <th style="width: 130px;">Carga Ponderada / Riesgo</th>
                        <th style="width: 75px; text-align: center;">Imágenes</th>
                        <th style="width: 135px;">Registrado por</th>
                        <th style="width: 95px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="fireTableBody">
                    @forelse($measurements as $m)
                        @php
                            $searchKeywords = strtolower(
                                $m['num'] . ' ' . 
                                ($m['macroarea'] ?? '') . ' ' . 
                                ($m['sector_name'] ?? '') . ' ' . 
                                ($m['location'] ?? '') . ' ' . 
                                ($m['registered_by'] ?? '') . ' ' .
                                ($m['risk_level'] ?? '') . ' ' .
                                collect($m['materials'] ?? [])->pluck('material')->implode(' ') . ' ' .
                                collect($m['fire_equipments'] ?? [])->pluck('tipo')->implode(' ')
                            );
                            $matCount = count($m['materials'] ?? []);
                        @endphp
                        <tr class="fire-data-row"
                            data-risk="{{ strtolower($m['risk_level'] ?? 'bajo') }}"
                            data-search="{{ $searchKeywords }}">

                            <!-- 1. N° -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $m['num'] }}
                            </td>

                            <!-- 2. Fecha / Hora -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
                                    <span class="table-time-pill" title="Hora de evaluación">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <span>{{ $m['time'] }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Macroárea / Sector -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 800; color: var(--ink); font-size: 13px;">{{ $m['sector_name'] }}</span>
                                    <span style="font-size: 11.5px; color: #64748b; font-weight: 500;" title="{{ $m['macroarea'] }}">
                                        {{ $m['macroarea'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- 4. Dimensiones (Largo x Ancho = Area) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-family: monospace; font-size: 12px; font-weight: 700; color: #0f172a;">
                                        {{ number_format($m['dimensions']['yi_largo'] ?? 0, 2) }}m × {{ number_format($m['dimensions']['xi_ancho'] ?? 0, 2) }}m
                                    </span>
                                    <span style="font-size: 11.5px; font-weight: 800; color: #0284c7;">
                                        S = {{ number_format($m['dimensions']['area_m2'] ?? 0, 2) }} m²
                                    </span>
                                </div>
                            </td>

                            <!-- 5. Materiales Registrados & Equipos Contra Incendios -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <div>
                                        <span class="fire-material-count-pill" style="display: inline-flex; align-items: center; gap: 5px; padding: 2.5px 8px; border-radius: 6px; background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; font-weight: 700; font-size: 11.5px;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                            </svg>
                                            <span>{{ $matCount }} {{ $matCount === 1 ? 'material' : 'materiales' }}</span>
                                        </span>
                                    </div>

                                    @if(!empty($m['fire_equipments']) && count($m['fire_equipments']) > 0)
                                        <div style="display: flex; flex-wrap: wrap; gap: 3px;">
                                            @foreach($m['fire_equipments'] as $eq)
                                                <span class="fire-eq-badge {{ strtolower(str_replace(' ', '_', $eq['tipo'] ?? 'extintor')) }}" style="font-size: 10.5px; padding: 1.5px 6px;" title="{{ $eq['observacion'] ?? '' }}">
                                                    @if($eq['tipo'] === 'EXTINTOR')
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M15 6v14a2 2 0 0 1-2 2H9a2 2 0 0 1-2-2V6"/><path d="M7 6h8"/><path d="M11 2v4"/><path d="M14 4h4"/></svg>
                                                    @elseif($eq['tipo'] === 'PULSADOR')
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="12" cy="12" r="3"/></svg>
                                                    @elseif($eq['tipo'] === 'ALARMA')
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                                                    @elseif($eq['tipo'] === 'HIDRANTE' || $eq['tipo'] === 'BOCA DE INCENDIO')
                                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M12 2v20"/><path d="M17 5H7"/><path d="M5 10h14"/><path d="M9 14h6"/></svg>
                                                    @endif
                                                    <span>{{ $eq['cantidad'] }} {{ $eq['tipo'] }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span style="font-size: 11px; color: #94a3b8; font-style: italic;">Sin equipos</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 6. Carga Ponderada Qs / Nivel de Riesgo -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span class="fire-risk-badge {{ $m['risk_theme'] ?? 'emerald' }}" title="Riesgo Intrínseco: {{ $m['risk_level'] }}">
                                        @if(($m['risk_level'] ?? '') === 'Bajo')
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12" /></svg>
                                        @elseif(($m['risk_level'] ?? '') === 'Medio')
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        @endif
                                        <span>Riesgo {{ $m['risk_level'] }}</span>
                                    </span>
                                    <span class="fire-qs-text" title="Carga de fuego ponderada por peso">
                                        {{ number_format($m['qs_mj_m2'] ?? 0, 1) }} MJ/m²
                                    </span>
                                    <span style="font-size: 10px; color: #64748b; font-family: monospace;">
                                        ({{ number_format($m['qs_mcal_m2'] ?? 0, 1) }} Mcal/m²)
                                    </span>
                                </div>
                            </td>

                            <!-- 7. Imágenes (con indicador de fotos) -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Sector {{ $m['num'] }}: {{ addslashes($m['sector_name']) }}')"
                                        title="Ver fotografía ampliada">
                                        <img src="{{ $m['image_path'] }}" alt="Foto">
                                        @if(!empty($m['images_count']) && $m['images_count'] > 1)
                                            <span style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">{{ $m['images_count'] }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="table-thumb-preview" style="cursor: default; opacity: 0.5;" title="Sin fotografía registrada">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            <!-- 8. Registrado por (Badge elegante con el nombre del usuario/técnico) -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 9. Acciones (Ver Detalle, Eliminar) -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <!-- Ver Detalle / Mapa del Punto -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick="openViewMeasurementModalById('{{ $m['id'] }}')" title="Ver detalle del sector"
                                        aria-label="Ver">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>

                                    <!-- Ver Informe Técnico -->
                                    <a href="{{ route('modules.fire_weight.report', ['id' => $module->id, 'measurement_id' => $m['id']]) }}" class="btn-admin-icon-action theme-warning"
                                        title="Ver Informe Técnico" aria-label="Informe Técnico">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                    </a>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger"
                                        onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['num'] }}')"
                                        title="Eliminar Sector" aria-label="Eliminar">
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
                            <td colspan="9" style="text-align: center; color: #64748b; padding: 36px;">
                                No se han registrado sectores de incendio en este estudio de carga de fuego por peso.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="9" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron sectores que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (10 por página) -->
        <div class="illumination-pagination-container" id="firePaginationBar">
            <div class="illumination-pagination-info" id="firePaginationInfo">
                Mostrando <strong id="firePageStart">1</strong> a <strong id="firePageEnd">10</strong> de <strong
                    id="firePageTotal">{{ $totalMeasurements }}</strong> sectores de incendio
            </div>
            <div class="illumination-pagination-controls" id="firePaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>
