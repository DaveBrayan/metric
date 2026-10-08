<!-- 3. Tabla Maestra de Mediciones de Contaminantes Químicos -->
<div class="illumination-table-card">
    <!-- Toolbar (Buscador a la Derecha) -->
    <div class="illumination-toolbar" style="display: flex; justify-content: flex-end;">
        <div class="search-box-pill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" id="contaminantesSearchInput" class="search-input-pill"
                placeholder="Buscar por área, punto, trabajador, personal o ubicación..."
                onkeyup="searchContaminantesLive()">
        </div>
    </div>

    <!-- Tabla Responsive -->
    <div class="table-responsive-box">
        <table class="modern-table" id="contaminantesMasterTable">
            <thead>
                <tr>
                    <th style="width: 60px; text-align: center;">N°</th>
                    <th style="width: 110px;">Fecha / Hora</th>
                    <th style="width: 160px;">Área / Punto</th>
                    <th style="width: 150px;">Trabajador</th>
                    <th style="width: 120px; text-align: center;">Horario (Inicio-Fin)</th>
                    <th style="width: 100px; text-align: right;">Masa Neta (mg)</th>
                    <th style="width: 110px; text-align: right;">Caudal (L/min)</th>
                    <th style="width: 105px; text-align: right;">Volumen (m³)</th>
                    <th style="width: 120px; text-align: right;">Concentración</th>
                    <th style="width: 75px; text-align: center;">Imágenes</th>
                    <th style="width: 130px;">Registrado por</th>
                    <th style="width: 95px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="contaminantesTableBody">
                @forelse($measurements as $m)
                    <tr class="illumination-data-row"
                        data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . $m['punto_medicion'] . ' ' . $m['trabajador_nombre'] . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">

                        <!-- 1. N° -->
                        <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #0284c7; font-size: 13.5px; text-align: center;">
                            {{ $m['num'] }}
                        </td>

                        <!-- 2. Fecha / Hora -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
                                <span class="table-time-pill" title="Hora de registro">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <span>{{ $m['time'] }}</span>
                                </span>
                            </div>
                        </td>

                        <!-- 3. Área / Punto de Muestreo -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $m['area'] }}</span>
                                <span style="font-size: 11.5px; color: #64748b; font-weight: 500;"
                                    title="{{ $m['punto_medicion'] }}">{{ $m['punto_medicion'] }}</span>
                            </div>
                        </td>

                        <!-- 4. Trabajador Evaluado -->
                        <td>
                            <span style="font-weight: 600; color: #334155; font-size: 12.5px;">
                                {{ $m['trabajador_nombre'] }}
                            </span>
                        </td>

                        <!-- 5. Horario Muestreo (Inicio-Fin) -->
                        <td style="text-align: center;">
                            <div style="display: flex; flex-direction: column; gap: 2px; align-items: center;">
                                <span style="font-size: 12px; font-weight: 700; color: #334155; font-family: monospace;">
                                    {{ $m['hora_inicio'] }} - {{ $m['hora_final'] }}
                                </span>
                                @if(!empty($m['tiempo_muestreo_min']) && $m['tiempo_muestreo_min'] > 0)
                                    <span style="font-size: 10.5px; font-weight: 600; color: #0284c7; background: #f0f9ff; padding: 1px 6px; border-radius: 4px; border: 1px solid #bae6fd;">
                                        {{ $m['tiempo_muestreo_min'] }} min
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- 6. Masa Neta (mg) -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-weight: 700; font-size: 12.5px; color: #0f172a;">
                                {{ number_format((float)$m['masa_neta_mg'], 4, ',', '.') }} mg
                            </span>
                        </td>

                        <!-- 7. Caudal Prom. (L/min) -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12.5px; font-weight: 600; color: #475569;">
                                {{ number_format((float)$m['q_prom_lmin'], 3, ',', '.') }}
                            </span>
                        </td>

                        <!-- 8. Volumen Muestreado (m³) -->
                        <td style="text-align: right;">
                            <span style="font-family: monospace; font-size: 12.5px; font-weight: 700; color: #0284c7;">
                                {{ number_format((float)$m['volumen_m3'], 4, ',', '.') }} m³
                            </span>
                        </td>

                        <!-- 9. Concentración (mg/m³) -->
                        <td style="text-align: right;">
                            <span class="lux-measured-badge compliant" style="font-size: 11.5px; font-family: monospace;">
                                {{ number_format((float)$m['concentracion_mg_m3'], 4, ',', '.') }} mg/m³
                            </span>
                        </td>

                        <!-- 10. Imágenes -->
                        <td style="text-align: center;">
                            @if(!empty($m['image_path']))
                                <div class="table-thumb-preview"
                                    onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['punto_medicion']) }}')"
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

                        <!-- 11. Registrado por -->
                        <td>
                            <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                {{ $m['registered_by'] }}
                            </span>
                        </td>

                        <!-- 12. Acciones -->
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
                        <td colspan="12" style="text-align: center; color: #64748b; padding: 36px;">
                            No se han registrado puntos de muestreo en este módulo de contaminantes químicos.
                        </td>
                    </tr>
                @endforelse
                <tr id="noResultsSearchRow" style="display: none;">
                    <td colspan="12" style="text-align: center; color: #64748b; padding: 36px;">
                        No se encontraron puntos de muestreo que coincidan con la búsqueda.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Barra de Paginación Reactiva (10 por página) -->
    <div class="illumination-pagination-container" id="contaminantesPaginationBar">
        <div class="illumination-pagination-info" id="contaminantesPaginationInfo">
            Mostrando <strong id="cqPageStart">1</strong> a <strong id="cqPageEnd">10</strong> de <strong
                id="cqPageTotal">{{ $totalMeasurements }}</strong> puntos de muestreo
        </div>
        <div class="illumination-pagination-controls" id="contaminantesPaginationControls">
            <!-- Dinámico por JS -->
        </div>
    </div>
</div>
