    <!-- 3. Tabla Maestra de Mediciones de Partículas Ambientales -->
    <div class="illumination-table-card">
        <!-- Toolbar (Buscador a la Derecha) -->
        <div class="illumination-toolbar" style="display: flex; justify-content: flex-end;">
            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="particulasAmbSearchInput" class="search-input-pill"
                    placeholder="Buscar por área, estación, fecha, personal o ubicación..."
                    onkeyup="searchParticulasAmbLive()">
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="particulasAmbMasterTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">N°</th>
                        <th style="width: 125px;">Fecha / Horario</th>
                        <th style="width: 175px;">Área / Estación</th>
                        <th style="width: 155px;">Meteorología</th>
                        <th style="min-width: 160px;">Muestreo PM-10</th>
                        <th style="min-width: 160px;">Muestreo PST</th>
                        <th style="width: 85px; text-align: center;">Caudal</th>
                        <th style="width: 75px; text-align: center;">Fotos</th>
                        <th style="width: 130px;">Registrado por</th>
                        <th style="width: 95px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="particulasAmbTableBody">
                    @forelse($measurements as $m)
                        <tr class="particulas-amb-data-row"
                            data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . $m['punto_medicion'] . ' ' . $m['location'] . ' ' . $m['registered_by'] . ' ' . ($m['pm10_prom'] ?? '') . ' ' . ($m['pst_prom'] ?? '')) }}">

                            <!-- 1. N° -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $m['num'] }}
                            </td>

                            <!-- 2. Fecha / Horario -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 3px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
                                    <span class="table-time-pill" title="Período: {{ $m['hora_inicio'] }} a {{ $m['hora_fin'] }} ({{ $m['diferencia_horas'] }} hrs)">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <span>{{ $m['hora_inicio'] }} - {{ $m['hora_fin'] }} ({{ $m['diferencia_horas'] }}h)</span>
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Área / Estación -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $m['area'] }}</span>
                                    <span style="font-size: 11.5px; color: #0284c7; font-weight: 600;" title="{{ $m['punto_medicion'] }}">
                                        {{ $m['punto_medicion'] }}
                                    </span>
                                    <span style="font-size: 10.5px; color: #64748b; font-family: monospace;">
                                        UTM: {{ number_format($m['utm_easting'], 0, '.', '') }}, {{ number_format($m['utm_northing'], 0, '.', '') }} ({{ $m['utm_zone'] }})
                                    </span>
                                </div>
                            </td>

                            <!-- 4. Meteorología -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px; font-size: 11.5px; color: #334155;">
                                    <div>
                                        <span style="color: #64748b;">Temp:</span> <strong>{{ $m['temp_max'] }}° / {{ $m['temp_min'] }}°C</strong>
                                    </div>
                                    <div>
                                        <span style="color: #64748b;">Presión:</span> <strong>{{ $m['presion_atm'] }} mmHg</strong>
                                    </div>
                                    <div>
                                        <span style="color: #64748b;">Viento:</span> <strong>{{ $m['vel_viento'] }} km/h ({{ $m['dir_viento'] }})</strong>
                                    </div>
                                </div>
                            </td>

                            <!-- 5. Muestreo PM-10 -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div class="part-pm10-tag" title="Filtros: Ini {{ $m['pm10_filtro_inicial'] }}g / Fin {{ $m['pm10_filtro_final'] }}g">
                                        <span class="pm10-label">PM-10:</span>
                                        <span class="part-val-prom">{{ $m['pm10_prom'] !== null ? number_format($m['pm10_prom'], 2, '.', '') : '—' }}</span>
                                        <span class="part-unit-lbl">µg/m³</span>
                                    </div>
                                    @if($m['pm10_filtro_inicial'] !== null || $m['pm10_filtro_final'] !== null)
                                        <div style="font-size: 10.5px; color: #64748b;">
                                            <span>F. Ini: <strong>{{ $m['pm10_filtro_inicial'] ?? '—' }} g</strong></span> |
                                            <span>F. Fin: <strong>{{ $m['pm10_filtro_final'] ?? '—' }} g</strong></span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- 6. Muestreo PST -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <div class="part-pm25-tag" title="Filtros: Ini {{ $m['pst_filtro_inicial'] }}g / Fin {{ $m['pst_filtro_final'] }}g">
                                        <span class="pm25-label">PST:</span>
                                        <span class="part-val-prom">{{ $m['pst_prom'] !== null ? number_format($m['pst_prom'], 2, '.', '') : '—' }}</span>
                                        <span class="part-unit-lbl">µg/m³</span>
                                    </div>
                                    @if($m['pst_filtro_inicial'] !== null || $m['pst_filtro_final'] !== null)
                                        <div style="font-size: 10.5px; color: #64748b;">
                                            <span>F. Ini: <strong>{{ $m['pst_filtro_inicial'] ?? '—' }} g</strong></span> |
                                            <span>F. Fin: <strong>{{ $m['pst_filtro_final'] ?? '—' }} g</strong></span>
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- 7. Caudal -->
                            <td style="text-align: center;">
                                <div style="font-size: 12px; font-weight: 700; color: #0284c7;">
                                    {{ $m['caudal'] }}
                                </div>
                                <div style="font-size: 10px; color: #64748b;">L/min</div>
                            </td>

                            <!-- 8. Imágenes -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Estación {{ $m['num'] }}: {{ addslashes($m['punto_medicion']) }}')"
                                        title="Ver fotografía ampliada">
                                        <img src="{{ $m['image_path'] }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                                        @if(!empty($m['images_count']) && $m['images_count'] > 1)
                                            <span style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">
                                                {{ $m['images_count'] }}
                                            </span>
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

                            <!-- 9. Registrado por -->
                            <td>
                                <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 10. Acciones (Ver / Editar & Eliminar) -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <!-- Ver / Editar Detalle -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick='openViewMeasurementModal(@json($m))' title="Ver y Editar Detalle"
                                        aria-label="Ver y Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                            <circle cx="12" cy="12" r="3" />
                                        </svg>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger"
                                        onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['num'] }}')"
                                        title="Eliminar Estación" aria-label="Eliminar">
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
                            <td colspan="10" style="text-align: center; color: #64748b; padding: 36px;">
                                No se han registrado estaciones de muestreo ambiental en este módulo.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="10" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron estaciones que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Paginación Reactiva -->
        <div class="illumination-pagination-container">
            <div class="illumination-pagination-info">
                Mostrando <strong id="partPageTotal">{{ $totalMeasurements }}</strong> estaciones de muestreo
            </div>
            <div class="illumination-pagination-controls" id="particulasAmbPaginationControls"></div>
        </div>
    </div>
