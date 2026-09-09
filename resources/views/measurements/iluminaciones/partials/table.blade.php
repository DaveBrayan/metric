    <!-- 3. Tabla Maestra de Mediciones de Iluminación (Sin Observaciones) -->
    <div class="illumination-table-card">
        <!-- Toolbar & Filtros -->
        <div class="illumination-toolbar">
            <div class="table-filter-pills" id="illuminationFilterGroup">
                <button type="button" class="filter-pill-btn active" onclick="filterIllumination('all', this)">
                    Todos ({{ $totalMeasurements }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('compliant', this)">
                    Conformes
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('non-compliant', this)">
                    No Conformes
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('Natural', this)">
                    Natural
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('Artificial', this)">
                    Artificial
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('Mixta', this)">
                    Mixta
                </button>
            </div>

            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="illuminationSearchInput" class="search-input-pill"
                    placeholder="Buscar por área, puesto, punto, personal o ubicación..."
                    onkeyup="searchIlluminationLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="illuminationMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <th style="width: 110px;">Fecha / Hora</th>
                        <th style="width: 170px;">Área / Puesto</th>
                        <th style="width: 130px;">Punto de Medición</th>
                        <th style="width: 160px;">Descripción de Actividad</th>
                        <th style="width: 115px;">Tipo Iluminación</th>
                        <th style="width: 105px;">Nivel Requerido</th>
                        <th style="width: 130px;">Mediciones (LUX)</th>
                        <th style="width: 80px; text-align: center;">Imágenes</th>
                        <th style="width: 140px;">Registrado por</th>
                        <th style="width: 100px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="illuminationTableBody">
                    @forelse($measurements as $m)
                        <tr class="illumination-data-row"
                            data-compliant="{{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}"
                            data-lighting="{{ $m['lighting_type'] }}"
                            data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . $m['workstation'] . ' ' . $m['measurement_point'] . ' ' . $m['activity_description'] . ' ' . $m['lighting_type'] . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">

                            <!-- 1. N° -->
                            <td
                                style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $m['num'] }}
                            </td>

                            <!-- 2. Fecha / Hora (Hora debajo de Fecha con colores del tema) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span
                                        style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
                                    <span class="table-time-pill" title="Hora de medición">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <span>{{ $m['time'] }}</span>
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Área / Puesto de Trabajo (Puesto debajo de Área) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $m['area'] }}</span>
                                    <span style="font-size: 11.5px; color: #64748b; font-weight: 500;"
                                        title="{{ $m['workstation'] }}">{{ $m['workstation'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Punto de Medición -->
                            <td title="{{ $m['measurement_point'] }}" style="font-weight: 600; color: #1e293b;">
                                {{ $m['measurement_point'] }}
                            </td>

                            <!-- 5. Descripción de Actividad -->
                            <td>
                                <span
                                    style="font-size: 12px; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 2px 7px; border-radius: 4px; border: 1px solid #bae6fd;">
                                    {{ $m['activity_description'] }}
                                </span>
                            </td>

                            <!-- 6. Tipo Iluminación -->
                            <td>
                                <span class="lighting-type-tag {{ $m['lighting_type'] }}">
                                    @if($m['lighting_type'] === 'Natural')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="4" />
                                            <path d="M12 2v2" />
                                            <path d="M12 20v2" />
                                        </svg>
                                    @elseif($m['lighting_type'] === 'Artificial')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M9 18h6" />
                                            <path d="M10 22h4" />
                                            <path
                                                d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5.76.76 1.23 1.52 1.41 2.5" />
                                        </svg>
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <path d="m4.93 4.93 14.14 14.14" />
                                        </svg>
                                    @endif
                                    <span>{{ $m['lighting_type'] }}</span>
                                </span>
                            </td>

                            <!-- 7. Nivel Requerido -->
                            <td>
                                <span class="lux-req-code">{{ $m['required_lux'] }} LUX</span>
                            </td>

                            <!-- 8. Mediciones (LUX) con contador de puntos -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span class="lux-measured-badge {{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}"
                                        title="{{ $m['is_compliant'] ? 'Cumple con el nivel requerido' : 'Por debajo del nivel requerido' }}">
                                        @if($m['is_compliant'])
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12" />
                                            </svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="18" y1="6" x2="6" y2="18" />
                                                <line x1="6" y1="6" x2="18" y2="18" />
                                            </svg>
                                        @endif
                                        <span>{{ $m['measured_lux'] }} LUX</span>
                                    </span>
                                    @if(!empty($m['readings_count']) && $m['readings_count'] > 1)
                                        <span
                                            style="font-size: 10.5px; font-weight: 700; color: #64748b;">{{ $m['readings_count'] }}
                                            lecturas prom.</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 9. Imágenes (con indicador de fotos) -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['measurement_point']) }}')"
                                        title="Ver fotografía ampliada">
                                        <img src="{{ $m['image_path'] }}" alt="Foto">
                                        @if(!empty($m['images_count']) && $m['images_count'] > 1)
                                            <span
                                                style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">{{ $m['images_count'] }}</span>
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

                            <!-- 10. Registrado por (Badge elegante con el nombre del usuario/técnico) -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 11. Acciones (Botones de acción solo icono con estilo idéntico a Equipos) -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <!-- Ver Detalle -->
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
                                            <path
                                                d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            <line x1="10" y1="11" x2="10" y2="17" />
                                            <line x1="14" y1="11" x2="14" y2="17" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyTableRow">
                            <td colspan="11" style="text-align: center; color: #64748b; padding: 36px;">
                                No se han registrado puntos de medición en este módulo de iluminación.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="11" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron puntos de medición que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (10 por página) -->
        <div class="illumination-pagination-container" id="illuminationPaginationBar">
            <div class="illumination-pagination-info" id="illuminationPaginationInfo">
                Mostrando <strong id="illPageStart">1</strong> a <strong id="illPageEnd">10</strong> de <strong
                    id="illPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
            </div>
            <div class="illumination-pagination-controls" id="illuminationPaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>