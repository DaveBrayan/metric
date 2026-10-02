    <!-- 3. Tabla Maestra de Mediciones de Ventilación -->
    <div class="ventilation-table-card">
        <!-- Toolbar (Buscador a la Derecha) -->
        <div class="ventilation-toolbar" style="display: flex; justify-content: flex-end;">
            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="ventilationSearchInput" class="search-input-pill"
                    placeholder="Buscar por local, tipo, elemento, personal o ubicación..."
                    onkeyup="searchVentilationLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="ventilationMasterTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">N°</th>
                        <th style="width: 105px;">Fecha / Hora</th>
                        <th style="width: 170px;">Local / Tipo Local</th>
                        <th style="width: 120px;">Tipo / Elemento</th>
                        <th style="width: 110px;">Vel. Aire</th>
                        <th style="width: 130px;">Caudal & Vol.</th>
                        <th style="width: 95px;">Renov/h</th>
                        <th style="width: 120px;">Ref. Min / Max</th>
                        <th style="width: 75px; text-align: center;">Imágenes</th>
                        <th style="width: 130px;">Registrado por</th>
                        <th style="width: 95px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="ventilationTableBody">
                    @forelse($measurements as $m)
                        <tr class="ventilation-data-row"
                            data-compliant="{{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}"
                            data-type="{{ $m['tipo_ventilacion'] }}"
                            data-search="{{ strtolower($m['num'] . ' ' . $m['local_trabajo'] . ' ' . $m['tipo_local'] . ' ' . $m['tipo_ventilacion'] . ' ' . $m['elemento_ventilacion'] . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">

                            <!-- 1. N° -->
                            <td
                                style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
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

                            <!-- 3. Local de Trabajo / Tipo de Local -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 13px;" title="{{ $m['local_trabajo'] }}">{{ $m['local_trabajo'] }}</span>
                                    <span style="font-size: 11px; color: #0284c7; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 170px;"
                                        title="{{ $m['tipo_local'] }}">{{ $m['tipo_local'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Tipo Ventilación / Elemento -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span class="vent-type-tag {{ $m['tipo_ventilacion'] === 'Natural' ? 'natural' : 'mecanica' }}">
                                        @if($m['tipo_ventilacion'] === 'Natural')
                                             <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                 <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/>
                                             </svg>
                                        @else
                                             <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                                 <path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/>
                                                 <path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>
                                             </svg>
                                        @endif
                                        <span>{{ $m['tipo_ventilacion'] }}</span>
                                    </span>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">{{ $m['elemento_ventilacion'] }}</span>
                                </div>
                            </td>

                            <!-- 5. Vel. Aire -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: #0f172a; font-family: monospace;">{{ $m['vel_aire_ms'] }} m/s</span>
                                    <span style="font-size: 10.5px; color: #64748b; font-family: monospace;">{{ $m['vel_aire_mh'] }} m/h</span>
                                </div>
                            </td>

                            <!-- 6. Caudal & Vol. -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-size: 12px; font-weight: 700; color: #0284c7; font-family: monospace;">{{ $m['caudal_m3h'] }} m³/h</span>
                                    <span style="font-size: 10.5px; color: #64748b; font-family: monospace;">Vol: {{ $m['volumen_m3'] }} m³</span>
                                </div>
                            </td>

                            <!-- 7. Renov/h -->
                            <td>
                                <span class="renov-val-badge {{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}">
                                    {{ $m['renovaciones_h'] }} /h
                                </span>
                            </td>

                            <!-- 8. Ref. Min / Max -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 1px;">
                                    <span style="font-size: 11.5px; font-weight: 700; color: #334155;">{{ $m['renovaciones_intervalo'] }}</span>
                                    <span style="font-size: 10px; color: #94a3b8;">Min: {{ $m['renovaciones_min'] ?? '—' }} | Max: {{ $m['renovaciones_max'] ?? '—' }}</span>
                                </div>
                            </td>

                            <!-- 9. Imágenes -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['local_trabajo']) }}')"
                                        title="Ver evidencia fotográfica">
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

                            <!-- 10. Registrado por -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 11. Acciones -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <!-- Ver Detalle / Editar -->
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
                                No se han registrado puntos de medición en este módulo de ventilación.
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
        <div class="ventilation-pagination-container" id="ventilationPaginationBar">
            <div class="ventilation-pagination-info" id="ventilationPaginationInfo">
                Mostrando <strong id="ventPageStart">1</strong> a <strong id="ventPageEnd">10</strong> de <strong
                    id="ventPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
            </div>
            <div class="ventilation-pagination-controls" id="ventilationPaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>
