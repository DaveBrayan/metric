    <!-- 3. Tabla Maestra de Mediciones de Partículas -->
    <div class="illumination-table-card">
        <!-- Toolbar (Buscador a la Derecha) -->
        <div class="illumination-toolbar" style="display: flex; justify-content: flex-end;">
            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="particulasSearchInput" class="search-input-pill"
                    placeholder="Buscar por área, puesto, punto, personal o ubicación..."
                    onkeyup="searchParticulasLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="particulasMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <th style="width: 115px;">Fecha / Hora</th>
                        <th style="width: 180px;">Área / Puesto</th>
                        <th style="width: 160px;">Punto de Medición</th>
                        <th style="min-width: 320px;">Fracciones de Partículas (PM10 & PM2.5)</th>
                        <th style="width: 85px; text-align: center;">Imágenes</th>
                        <th style="width: 140px;">Registrado por</th>
                        <th style="width: 100px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="particulasTableBody">
                    @forelse($measurements as $m)
                        <tr class="particulas-data-row"
                            data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . $m['workstation'] . ' ' . $m['punto_medicion'] . ' ' . $m['location'] . ' ' . $m['registered_by'] . ' ' . ($m['pm10_prom'] ?? '') . ' ' . ($m['pm25_prom'] ?? '')) }}">

                            <!-- 1. N° -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $m['num'] }}
                            </td>

                            <!-- 2. Fecha / Hora -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
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

                            <!-- 3. Área / Puesto de Trabajo -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $m['area'] }}</span>
                                    <span style="font-size: 11.5px; color: #64748b; font-weight: 500;"
                                        title="{{ $m['workstation'] }}">{{ $m['workstation'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Punto de Medición -->
                            <td title="{{ $m['punto_medicion'] }}" style="font-weight: 600; color: #1e293b;">
                                {{ $m['punto_medicion'] }}
                            </td>

                            <!-- 5. Fracciones de Partículas (PM10 & PM2.5) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                        <!-- PM10 Tag -->
                                        <div class="part-pm10-tag" title="Fracción Inhalable PM10 (LMP ACGIH: 10 µg/m³)">
                                            <span class="pm10-label">PM10:</span>
                                            <span class="part-val-prom">{{ $m['pm10_prom'] !== null ? number_format($m['pm10_prom'], 3, '.', '') : '—' }}</span>
                                            <span class="part-unit-lbl">µg/m³</span>
                                            @if($m['pm10_cumple'] !== null)
                                                <span class="badge-compliance {{ $m['pm10_cumple'] ? 'cumple' : 'excede' }}">
                                                    {{ $m['pm10_cumple'] ? 'Cumple' : 'Excede' }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- PM2.5 Tag -->
                                        <div class="part-pm25-tag" title="Fracción Respirable PM2.5 (LMP ACGIH: 3 µg/m³)">
                                            <span class="pm25-label">PM2.5:</span>
                                            <span class="part-val-prom">{{ $m['pm25_prom'] !== null ? number_format($m['pm25_prom'], 3, '.', '') : '—' }}</span>
                                            <span class="part-unit-lbl">µg/m³</span>
                                            @if($m['pm25_cumple'] !== null)
                                                <span class="badge-compliance {{ $m['pm25_cumple'] ? 'cumple' : 'excede' }}">
                                                    {{ $m['pm25_cumple'] ? 'Cumple' : 'Excede' }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Clima si existe -->
                                    @if($m['temperatura'] !== null || $m['hr_percent'] !== null)
                                        <div style="display: flex; gap: 10px; font-size: 11px; color: #64748b; font-weight: 500;">
                                            @if($m['temperatura'] !== null)
                                                <span>Temp: <strong>{{ $m['temperatura'] }} °C</strong></span>
                                            @endif
                                            @if($m['hr_percent'] !== null)
                                                <span>HR: <strong>{{ $m['hr_percent'] }} %</strong></span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- 6. Imágenes -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['punto_medicion']) }}')"
                                        title="Ver fotografía ampliada">
                                        <img src="{{ $m['image_path'] }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover; display: block;">
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

                            <!-- 7. Registrado por -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 8. Acciones (Editar & Eliminar) -->
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
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 36px;">
                                No se han registrado puntos de medición en este módulo de partículas.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron puntos de medición que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (10 por página) -->
        <div class="illumination-pagination-container" id="particulasPaginationBar">
            <div class="illumination-pagination-info" id="particulasPaginationInfo">
                Mostrando <strong id="partPageStart">1</strong> a <strong id="partPageEnd">10</strong> de <strong
                    id="partPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
            </div>
            <div class="illumination-pagination-controls" id="particulasPaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>
