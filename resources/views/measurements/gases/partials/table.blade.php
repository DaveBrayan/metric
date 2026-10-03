    <!-- 3. Tabla Maestra de Mediciones de Gases -->
    <div class="illumination-table-card">
        <!-- Toolbar (Buscador a la Derecha) -->
        <div class="illumination-toolbar" style="display: flex; justify-content: flex-end;">
            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="gasesSearchInput" class="search-input-pill"
                    placeholder="Buscar por área, puesto, gas, personal o ubicación..."
                    onkeyup="searchGasesLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="gasesMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <th style="width: 115px;">Fecha / Hora</th>
                        <th style="width: 180px;">Área / Puesto</th>
                        <th style="width: 150px;">Punto de Medición</th>
                        <th style="min-width: 320px;">Lecturas Analíticas (Gases & Promedios)</th>
                        <th style="width: 85px; text-align: center;">Imágenes</th>
                        <th style="width: 140px;">Registrado por</th>
                        <th style="width: 100px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="gasesTableBody">
                    @forelse($measurements as $m)
                        @php
                            $gasesList = [];
                            $gasesDefs = \App\Http\Controllers\GasesController::getGasesDefinitions();
                            $defsByKey = [];
                            foreach ($gasesDefs as $gd) {
                                $defsByKey[$gd['key']] = $gd;
                            }

                            // 1. Extraer desde gases_readings
                            $gReadings = is_array($m['gases_readings']) ? $m['gases_readings'] : (json_decode($m['gases_readings'] ?? '', true) ?: []);
                            if (is_string($gReadings)) $gReadings = json_decode($gReadings, true) ?: [];

                            if (!empty($gReadings) && is_array($gReadings)) {
                                foreach ($gReadings as $gk => $gData) {
                                    $def = $defsByKey[$gk] ?? ['name' => strtoupper($gk), 'formula' => strtoupper($gk), 'unit' => 'ppm'];
                                    $prom = isset($gData['prom']) && $gData['prom'] !== '' && $gData['prom'] !== null ? (float)$gData['prom'] : null;
                                    if ($prom === null && isset($gData['values']) && is_array($gData['values'])) {
                                        $numVals = array_values(array_filter($gData['values'], fn($v) => is_numeric($v)));
                                        if (!empty($numVals)) $prom = round(array_sum($numVals) / count($numVals), 2);
                                    }
                                    $gasesList[$gk] = [
                                        'key' => $gk,
                                        'name' => $gData['name'] ?? $def['name'],
                                        'formula' => $gData['formula'] ?? $def['formula'],
                                        'unit' => $gData['unit'] ?? $def['unit'],
                                        'prom' => $prom,
                                    ];
                                }
                            }

                            // 2. Extraer desde columnas individuales de respaldo
                            foreach ($gasesDefs as $gd) {
                                $gk = $gd['key'];
                                $promCol = $m["{$gk}_prom"] ?? null;
                                $valsCol = $m["{$gk}_values"] ?? null;
                                if (!isset($gasesList[$gk])) {
                                    if ($promCol !== null && $promCol !== '') {
                                        $gasesList[$gk] = [
                                            'key' => $gk,
                                            'name' => $gd['name'],
                                            'formula' => $gd['formula'],
                                            'unit' => $gd['unit'],
                                            'prom' => (float)$promCol,
                                        ];
                                    } elseif (!empty($valsCol)) {
                                        $valsArr = is_array($valsCol) ? $valsCol : (json_decode($valsCol, true) ?: []);
                                        if (is_string($valsArr)) $valsArr = json_decode($valsArr, true) ?: [];
                                        $numVals = array_values(array_filter($valsArr, fn($v) => is_numeric($v)));
                                        if (!empty($numVals)) {
                                            $gasesList[$gk] = [
                                                'key' => $gk,
                                                'name' => $gd['name'],
                                                'formula' => $gd['formula'],
                                                'unit' => $gd['unit'],
                                                'prom' => round(array_sum($numVals) / count($numVals), 2),
                                            ];
                                        }
                                    }
                                }
                            }

                            // 3. Extraer desde selected_gases
                            $selGases = is_array($m['selected_gases']) ? $m['selected_gases'] : (json_decode($m['selected_gases'] ?? '', true) ?: []);
                            if (is_string($selGases)) $selGases = json_decode($selGases, true) ?: [];
                            if (!empty($selGases) && is_array($selGases)) {
                                foreach ($selGases as $gk) {
                                    if (!isset($gasesList[$gk]) && isset($defsByKey[$gk])) {
                                        $gasesList[$gk] = [
                                            'key' => $gk,
                                            'name' => $defsByKey[$gk]['name'],
                                            'formula' => $defsByKey[$gk]['formula'],
                                            'unit' => $defsByKey[$gk]['unit'],
                                            'prom' => null,
                                        ];
                                    }
                                }
                            }

                            $totalGasesCount = count($gasesList);
                            $searchTags = [];
                            foreach ($gasesList as $g) {
                                $searchTags[] = $g['name'] . ' ' . $g['formula'] . ' ' . ($g['prom'] ?? '');
                            }
                        @endphp
                        <tr class="gases-data-row"
                            data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . $m['workstation'] . ' ' . $m['measurement_point'] . ' ' . implode(' ', $searchTags) . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">

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
                            <td title="{{ $m['measurement_point'] }}" style="font-weight: 600; color: #1e293b;">
                                {{ $m['measurement_point'] }}
                            </td>

                            <!-- 5. Lecturas Analíticas (Gases & Promedios) -->
                            <td>
                                @if($totalGasesCount > 0)
                                    <div style="display: flex; flex-direction: column; gap: 6px;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span class="gases-count-pill" title="Total de gases normativos evaluados en este punto">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                                    <polygon points="12 2 2 7 12 12 22 7 12 2" />
                                                    <polyline points="2 17 12 22 22 17" />
                                                    <polyline points="2 12 12 17 22 12" />
                                                </svg>
                                                <span><strong>{{ $totalGasesCount }}</strong> {{ $totalGasesCount == 1 ? 'Gas evaluado' : 'Gases evaluados' }}</span>
                                            </span>
                                        </div>
                                        <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                                            @foreach($gasesList as $g)
                                                <span class="gas-evaluated-tag" title="{{ $g['name'] }}">
                                                    <strong class="gas-formula-name">{{ $g['formula'] }}:</strong>
                                                    <span class="gas-prom-val">{{ ($g['prom'] !== null && is_numeric($g['prom'])) ? number_format((float)$g['prom'], 2, '.', '') : '—' }}</span>
                                                    <span class="gas-unit-lbl">{{ $g['unit'] }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    <span style="font-size: 12px; color: #94a3b8; font-style: italic;">Sin gases registrados</span>
                                @endif
                            </td>

                            <!-- 6. Imágenes -->
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

                            <!-- 7. Registrado por -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 8. Acciones (Editar & Eliminar) -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <!-- Editar Punto de Gases -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick='openEditMeasurementModal(@json($m))' title="Editar medición de gases"
                                        aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                            <path d="m15 5 4 4" />
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
                                No se han registrado puntos de medición en este módulo de gases.
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
        <div class="illumination-pagination-container" id="gasesPaginationBar">
            <div class="illumination-pagination-info" id="gasesPaginationInfo">
                Mostrando <strong id="gasPageStart">1</strong> a <strong id="gasPageEnd">10</strong> de <strong
                    id="gasPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
            </div>
            <div class="illumination-pagination-controls" id="gasesPaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>
