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
                    <th style="width: 50px; text-align: center;">N°</th>
                    <th style="width: 75px; text-align: center;">Código</th>
                    <th style="width: 110px;">Fecha / Hora</th>
                    <th style="width: 155px;">Área / Puesto</th>
                    <th style="width: 150px;">Trabajador / Equipo</th>
                    <th style="width: 115px; text-align: center;">Tipo</th>
                    <th style="width: 80px; text-align: center;">T. Exp (h)</th>
                    <th style="width: 85px; text-align: right;">Aeq X</th>
                    <th style="width: 85px; text-align: right;">Aeq Y</th>
                    <th style="width: 85px; text-align: right;">Aeq Z</th>
                    <th style="width: 115px; text-align: right;">A(8) (m/s²)</th>
                    <th style="width: 115px; text-align: center;">Evaluación</th>
                    <th style="width: 75px; text-align: center;">Imágenes</th>
                    <th style="width: 130px;">Registrado por</th>
                    <th style="width: 95px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="vibracionTableBody">
                @forelse($measurements as $idx => $item)
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
                        $rawNum = (string)($item['num'] ?? $item['point_number'] ?? ($idx + 1));
                        $cleanNum = intval(preg_replace('/[^0-9]/', '', $rawNum)) ?: ($idx + 1);
                    @endphp
                    <tr class="illumination-data-row"
                        data-id="{{ $item['id'] }}"
                        data-search="{{ strtolower($cleanNum . ' ' . $item['codigo'] . ' ' . $item['area'] . ' ' . $item['puesto_trabajo'] . ' ' . $item['trabajador_evaluado'] . ' ' . $item['maquina_equipo'] . ' ' . ($item['registered_by'] ?? '')) }}">

                        <!-- 1. N° -->
                        <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                            {{ sprintf('%02d', $cleanNum) }}
                        </td>

                        <!-- 2. Código -->
                        <td style="text-align: center;">
                            <span class="badge-code" style="color: #0284c7; background: #e0f2fe; border: 1px solid #bae6fd;">
                                {{ $item['codigo'] }}
                            </span>
                        </td>

                        <!-- 3. Fecha / Hora -->
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

                        <!-- 4. Área / Puesto -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $item['area'] }}</span>
                                <span style="font-size: 11.5px; color: #64748b; font-weight: 500;"
                                    title="{{ $item['puesto_trabajo'] }}">{{ $item['puesto_trabajo'] }}</span>
                            </div>
                        </td>

                        <!-- 5. Trabajador / Equipo -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 600; color: #334155; font-size: 12.5px;">{{ $item['trabajador_evaluado'] }}</span>
                                <span style="font-size: 11px; color: #94a3b8;" title="{{ $item['maquina_equipo'] }}">{{ $item['maquina_equipo'] }}</span>
                            </div>
                        </td>

                        <!-- 6. Tipo -->
                        <td style="text-align: center;">
                            <span style="display: inline-block; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background: {{ $isCE ? '#f0fdf4; color: #15803d; border: 1px solid #bbf7d0;' : '#eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;' }}">
                                {{ $isCE ? 'Cuerpo Entero' : 'Mano-Brazo' }}
                            </span>
                        </td>

                        <!-- 7. Tiempo de Exposición (h) -->
                        <td style="text-align: center;">
                            <span class="lux-req-code" style="font-size: 11px; padding: 2px 7px;">
                                {{ number_format((float)$item['tiempo_expos_h'], 2, ',', '.') }} h
                            </span>
                        </td>

                        <!-- 8. Aeq X -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12px; color: #475569; font-weight: 600;">
                                {{ number_format((float)$item['aeqx'], 4, ',', '.') }}
                            </span>
                        </td>

                        <!-- 9. Aeq Y -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12px; color: #475569; font-weight: 600;">
                                {{ number_format((float)$item['aeqy'], 4, ',', '.') }}
                            </span>
                        </td>

                        <!-- 10. Aeq Z -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12px; color: #475569; font-weight: 600;">
                                {{ number_format((float)$item['aeqz'], 4, ',', '.') }}
                            </span>
                        </td>

                        <!-- 11. A(8) Normalizado -->
                        <td style="text-align: right;">
                            <span class="lux-req-code" style="font-size: 12px; font-weight: 800; color: #1e293b; padding: 3px 8px;">
                                {{ number_format((float)$item['a8'], 4, ',', '.') }} m/s²
                            </span>
                        </td>

                        <!-- 12. Evaluación -->
                        <td style="text-align: center;">
                            @if($item['estado'] === 'CUMPLE')
                                <span class="lux-measured-badge compliant" style="font-size: 11px; padding: 2px 8px;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <span>CUMPLE</span>
                                </span>
                            @elseif($item['estado'] === 'NIVEL DE ACCIÓN')
                                <span class="lux-measured-badge warning" style="font-size: 10px; padding: 2px 7px;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="12" y1="8" x2="12" y2="12" />
                                        <line x1="12" y1="16" x2="12.01" y2="16" />
                                    </svg>
                                    <span>N. ACCIÓN</span>
                                </span>
                            @else
                                <span class="lux-measured-badge non-compliant" style="font-size: 10.5px; padding: 2px 8px;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                    <span>SUPERA</span>
                                </span>
                            @endif
                        </td>

                        <!-- 13. Imágenes -->
                        <td style="text-align: center;">
                            @if(!empty($firstPhoto))
                                <div class="table-thumb-preview"
                                    onclick='openPhotoViewer(@json($item["images_urls"] ?? $item["images"] ?? [$firstPhoto]), "{{ $item["codigo"] }} - {{ addslashes($item["area"]) }}")'
                                    title="Ver fotografía ampliada">
                                    <img src="{{ $firstPhoto }}" alt="Foto">
                                    @if($numPhotos > 1)
                                        <span class="table-thumb-count-badge">{{ $numPhotos }}</span>
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

                        <!-- 14. Registrado por -->
                        <td>
                            <span class="badge-registered-staff" title="Registrado por: {{ $item['registered_by'] ?? $registeredByHeader }}">
                                {{ $item['registered_by'] ?? $registeredByHeader }}
                            </span>
                        </td>

                        <!-- 15. Acciones -->
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
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path
                                            d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyTableRow">
                        <td colspan="15">
                            <div class="table-empty-state">
                                <div class="empty-icon-wrap" style="color: #0284c7; background: #f0f9ff;">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2" />
                                    </svg>
                                </div>
                                <h3>No hay mediciones de vibración registradas</h3>
                                <p>Haz clic en "+ Nuevo Punto de Medición" para registrar la primera evaluación de vibración ocupacional (cuerpo entero o mano-brazo).</p>
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
                    <td colspan="15" style="text-align: center; color: #64748b; padding: 36px; font-weight: 500;">
                        No se encontraron puntos de monitoreo que coincidan con la búsqueda.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Barra de Paginación Reactiva (10 por página) -->
    <div class="illumination-pagination-container vibracion-pagination-container" id="vibracionPaginationBar">
        <div class="illumination-pagination-info vibracion-pagination-info" id="vibracionPaginationInfo">
            Mostrando <strong id="vibPageStart">1</strong> a <strong id="vibPageEnd">{{ min(10, count($measurements)) }}</strong> de <strong
                id="vibPageTotal">{{ $totalMeasurements }}</strong> puntos de monitoreo
        </div>
        <div class="illumination-pagination-controls vibracion-pagination-controls" id="vibracionPaginationControls">
            <!-- Dinámico por JS -->
        </div>
    </div>
</div>
