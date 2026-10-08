<!-- 3. Tabla Maestra de Mediciones de Vibración Ocupacional -->
<div class="illumination-table-card">
    <!-- Toolbar (Buscador a la Derecha) -->
    <div class="illumination-toolbar" style="display: flex; justify-content: flex-end;">
        <div class="search-box-pill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" id="vibracionSearchInput" class="search-input-pill"
                placeholder="Buscar por código, área, puesto, trabajador, equipo o personal..."
                onkeyup="searchVibracionLive()">
        </div>
    </div>

    <!-- Tabla Responsive -->
    <div class="table-responsive-box">
        <table class="modern-table" id="vibracionMasterTable">
            <thead>
                <tr>
                    <th style="width: 75px; text-align: center;">Código</th>
                    <th style="width: 110px;">Fecha / Hora</th>
                    <th style="width: 155px;">Área / Puesto</th>
                    <th style="width: 150px;">Trabajador / Equipo</th>
                    <th style="width: 115px; text-align: center;">Tipo</th>
                    <th style="width: 80px; text-align: center;">T. Exp (h)</th>
                    <th style="width: 90px; text-align: right;">Aeq X</th>
                    <th style="width: 90px; text-align: right;">Aeq Y</th>
                    <th style="width: 90px; text-align: right;">Aeq Z</th>
                    <th style="width: 115px; text-align: right;">A(8) (m/s²)</th>
                    <th style="width: 110px; text-align: center;">Evaluación</th>
                    <th style="width: 75px; text-align: center;">Imágenes</th>
                    <th style="width: 125px;">Registrado por</th>
                    <th style="width: 95px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="vibracionTableBody">
                @forelse($measurements as $item)
                    @php
                        $isCE = $item['tipo'] === 'cuerpo_entero';
                        $statusClass = match($item['estado']) {
                            'CUMPLE' => 'compliant',
                            'NIVEL DE ACCIÓN' => 'warning',
                            'SUPERA LÍMITE' => 'non-compliant',
                            default => 'compliant'
                        };
                        $numPhotos = !empty($item['images_urls']) ? count($item['images_urls']) : 0;
                        $firstPhoto = (!empty($item['images_urls']) && count($item['images_urls']) > 0) ? $item['images_urls'][0] : null;
                    @endphp
                    <tr class="illumination-data-row"
                        data-id="{{ $item['id'] }}"
                        data-search="{{ strtolower($item['codigo'] . ' ' . $item['area'] . ' ' . $item['puesto_trabajo'] . ' ' . $item['trabajador_evaluado'] . ' ' . $item['maquina_equipo'] . ' ' . ($item['registered_by'] ?? '')) }}">

                        <!-- 1. Código -->
                        <td style="text-align: center;">
                            <span class="badge-code" style="color: #0284c7; background: #e0f2fe; border-color: #bae6fd;">
                                {{ $item['codigo'] }}
                            </span>
                        </td>

                        <!-- 2. Fecha / Hora -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $item['date'] }}</span>
                                <span class="table-time-pill" title="Hora de registro">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <span>{{ $item['time'] }}</span>
                                </span>
                            </div>
                        </td>

                        <!-- 3. Área / Puesto -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $item['area'] }}</span>
                                <span style="font-size: 11.5px; color: #64748b; font-weight: 500;"
                                    title="{{ $item['puesto_trabajo'] }}">{{ $item['puesto_trabajo'] }}</span>
                            </div>
                        </td>

                        <!-- 4. Trabajador / Equipo -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 600; color: #334155; font-size: 12.5px;">{{ $item['trabajador_evaluado'] }}</span>
                                <span style="font-size: 11px; color: #94a3b8;" title="{{ $item['maquina_equipo'] }}">{{ $item['maquina_equipo'] }}</span>
                            </div>
                        </td>

                        <!-- 5. Tipo -->
                        <td style="text-align: center;">
                            <span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: {{ $isCE ? '#f0fdf4; color: #15803d; border: 1px solid #bbf7d0;' : '#eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' }}">
                                {{ $isCE ? 'Cuerpo Entero' : 'Mano-Brazo' }}
                            </span>
                        </td>

                        <!-- 6. Tiempo de Exposición (h) -->
                        <td style="text-align: center; font-weight: 700; color: #334155; font-size: 12px;">
                            {{ number_format((float)$item['tiempo_expos_h'], 2, ',', '.') }} h
                        </td>

                        <!-- 7. Aeq X -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12px; color: #475569; font-weight: 600;">
                                {{ number_format((float)$item['aeqx'], 4, ',', '.') }}
                            </span>
                        </td>

                        <!-- 8. Aeq Y -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12px; color: #475569; font-weight: 600;">
                                {{ number_format((float)$item['aeqy'], 4, ',', '.') }}
                            </span>
                        </td>

                        <!-- 9. Aeq Z -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12px; color: #475569; font-weight: 600;">
                                {{ number_format((float)$item['aeqz'], 4, ',', '.') }}
                            </span>
                        </td>

                        <!-- 10. A(8) Normalizado -->
                        <td style="text-align: right;">
                            <span class="lux-measured-badge {{ $statusClass }}" style="font-size: 11.5px; font-family: monospace;">
                                {{ number_format((float)$item['a8'], 4, ',', '.') }} m/s²
                            </span>
                        </td>

                        <!-- 11. Evaluación -->
                        <td style="text-align: center;">
                            <span class="lux-measured-badge {{ $statusClass }}" style="font-size: 10.5px; padding: 2px 8px;">
                                {{ $item['estado'] }}
                            </span>
                        </td>

                        <!-- 12. Imágenes -->
                        <td style="text-align: center;">
                            @if(!empty($firstPhoto))
                                <div class="table-thumb-preview"
                                    onclick='openPhotoViewerModal(@json($item['images_urls']), "{{ $item['codigo'] }} - {{ addslashes($item['area']) }}")'
                                    title="Ver fotografía ampliada">
                                    <img src="{{ $firstPhoto }}" alt="Foto">
                                    @if($numPhotos > 1)
                                        <span style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">{{ $numPhotos }}</span>
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

                        <!-- 13. Registrado por -->
                        <td>
                            <span class="badge-registered-staff" title="Registrado por: {{ $item['registered_by'] ?? $registeredByHeader }}">
                                {{ $item['registered_by'] ?? $registeredByHeader }}
                            </span>
                        </td>

                        <!-- 14. Acciones -->
                        <td style="text-align: right;">
                            <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                <!-- Ver Detalle -->
                                <button type="button" class="btn-admin-icon-action theme-cyan"
                                    onclick='openViewMeasurementModal(@json($item))' title="Ver detalle del punto"
                                    aria-label="Ver">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>

                                <!-- Eliminar -->
                                <button type="button" class="btn-admin-icon-action theme-danger"
                                    onclick="confirmDeleteVibracionMeasurement('{{ $item['id'] }}', '{{ $item['codigo'] }}')"
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
                        <td colspan="14" style="text-align: center; color: #64748b; padding: 36px;">
                            No se han registrado puntos de monitoreo en este módulo de vibraciones ocupacionales.
                        </td>
                    </tr>
                @endforelse
                <tr id="noResultsSearchRow" style="display: none;">
                    <td colspan="14" style="text-align: center; color: #64748b; padding: 36px;">
                        No se encontraron puntos de monitoreo que coincidan con la búsqueda.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Barra de Paginación Reactiva (10 por página) -->
    <div class="illumination-pagination-container" id="vibracionPaginationBar">
        <div class="illumination-pagination-info" id="vibracionPaginationInfo">
            Mostrando <strong id="vibPageStart">1</strong> a <strong id="vibPageEnd">10</strong> de <strong
                id="vibPageTotal">{{ $measurements->count() }}</strong> puntos de monitoreo
        </div>
        <div class="illumination-pagination-controls" id="vibracionPaginationControls">
            <!-- Dinámico por JS -->
        </div>
    </div>
</div>
