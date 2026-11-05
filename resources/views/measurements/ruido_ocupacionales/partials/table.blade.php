<!-- 3. Tabla Maestra de Mediciones de Ruido Ocupacional -->
<div class="ruido-table-card">
    <!-- Toolbar (Buscador a la Derecha) -->
    <div class="ruido-toolbar" style="display: flex; justify-content: flex-end;">
        <div class="search-box-pill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" id="ruidoSearchInput" class="search-input-pill"
                placeholder="Buscar por área, puesto, punto, tipo o personal..."
                onkeyup="searchRuidoOcupacionalLive()">
        </div>
    </div>

    <!-- Tabla Responsive -->
    <div class="table-responsive-box">
        <table class="modern-table" id="ruidoMasterTable">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">N°</th>
                    <th style="width: 115px;">Fecha / Hora</th>
                    <th style="width: 160px;">Área / Puesto</th>
                    <th style="width: 140px;">Punto de Medición</th>
                    <th style="width: 120px;">Tipo Ruido</th>
                    <th style="width: 130px; text-align: center;">Parámetros (TPE / POND)</th>
                    <th style="width: 135px; text-align: center;">Nivel LEQ (dBA)</th>
                    <th style="width: 80px; text-align: center;">Imágenes</th>
                    <th style="width: 140px;">Registrado por</th>
                    <th style="width: 100px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="ruidoTableBody">
                @forelse($measurements as $m)
                    <tr class="ruido-data-row"
                        data-compliant="{{ ($m['leq_t_db'] <= 85.0) ? 'compliant' : 'non-compliant' }}"
                        data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . ($m['workstation'] ?? '') . ' ' . $m['punto_medicion'] . ' ' . $m['tipo_ruido'] . ' ' . $m['registered_by'] . ' ' . ($m['leq_t_db'] ?? '')) }}">

                        <!-- 1. N° -->
                        <td
                            style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                            {{ $m['num'] }}
                        </td>

                        <!-- 2. Fecha / Hora -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] ?? $m['date_formatted'] }}</span>
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
                                    title="{{ $m['workstation'] ?? $m['punto_medicion'] }}">{{ $m['workstation'] ?? $m['punto_medicion'] }}</span>
                            </div>
                        </td>

                        <!-- 4. Punto de Medición -->
                        <td title="{{ $m['punto_medicion'] }}" style="font-weight: 600; color: #1e293b;">
                            {{ $m['punto_medicion'] }}
                        </td>

                        <!-- 5. Tipo Ruido -->
                        <td>
                            <span
                                style="font-size: 12px; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 2px 7px; border-radius: 4px; border: 1px solid #bae6fd; display: inline-block;">
                                {{ $m['tipo_ruido'] }}
                            </span>
                        </td>

                        <!-- 6. Parámetros (TPE / POND) -->
                        <td style="text-align: center;">
                            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 4px;">
                                <span class="lux-req-code" title="Tiempo de Exposición (Horas)">{{ number_format($m['tiempo_expos_h'], 1) }}h</span>
                                <span class="lux-req-code" title="Ponderación / Respuesta">{{ $m['ponderacion'] ?? 'A' }}/{{ substr($m['respuesta'] ?? 'Lenta', 0, 1) }}</span>
                            </div>
                        </td>

                        <!-- 7. Nivel LEQ (dBA) -->
                        <td style="text-align: center;">
                            @php $isOk = ($m['leq_t_db'] <= 85.0); @endphp
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 2px;">
                                <span class="lux-measured-badge {{ $isOk ? 'compliant' : 'non-compliant' }}"
                                    title="{{ $isOk ? 'Dentro del Límite Permisible (≤ 85 dBA)' : 'Supera el Límite Permisible (> 85 dBA)' }}">
                                    @if($isOk)
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
                                    <span>{{ number_format($m['leq_t_db'], 1) }} dBA</span>
                                </span>
                                @if(!empty($m['requiere_dosimetria']))
                                    <span style="font-size: 10px; font-weight: 700; color: #dc2626;">Req. Dosimetría</span>
                                @endif
                            </div>
                        </td>

                        <!-- 8. Imágenes -->
                        <td style="text-align: center; vertical-align: middle;">
                            @php
                                $imgSrc = !empty($m['image_path']) ? $m['image_path'] : (!empty($m['images']) && count($m['images']) > 0 ? $m['images'][0] : null);
                                $imgCount = !empty($m['images_count']) ? $m['images_count'] : (!empty($m['images']) ? count($m['images']) : 0);
                            @endphp
                            @if(!empty($imgSrc))
                                <div class="table-thumb-preview"
                                    onclick="openPhotoViewer('{{ $imgSrc }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['punto_medicion'] ?: $m['area']) }}')"
                                    title="Ver fotografía ampliada">
                                    <img src="{{ $imgSrc }}" alt="Foto">
                                    @if($imgCount > 1)
                                        <span class="thumb-count-badge">{{ $imgCount }}</span>
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

                        <!-- 10. Acciones -->
                        <td style="text-align: right;">
                            <div class="admin-actions-cell" style="display: flex; justify-content: flex-end; gap: 8px;">
                                <!-- Editar -->
                                <button type="button" class="btn-admin-icon-action theme-cyan"
                                    onclick="openEditMeasurementById({{ $m['id'] }})"
                                    title="Editar punto de medición" aria-label="Editar">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        <path d="m15 5 4 4" />
                                    </svg>
                                </button>

                                <!-- Eliminar -->
                                <button type="button" class="btn-admin-icon-action theme-danger"
                                    onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['num'] }}')"
                                    title="Eliminar punto" aria-label="Eliminar">
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
                        <td colspan="10" style="text-align: center; color: #64748b; padding: 36px;">
                            No se han registrado puntos de medición en este módulo de ruido ocupacional.
                        </td>
                    </tr>
                @endforelse
                <tr id="noResultsRow" style="display: none;">
                    <td colspan="10" style="text-align: center; color: #64748b; padding: 36px;">
                        No se encontraron puntos de medición que coincidan con la búsqueda.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Barra de Paginación Reactiva (10 por página) -->
    <div class="ruido-pagination-container" id="ruidoPaginationBar">
        <div class="ruido-pagination-info" id="ruidoPaginationInfo">
            Mostrando <strong id="ruidoPageStart">1</strong> a <strong id="ruidoPageEnd">10</strong> de <strong
                id="ruidoPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
        </div>
        <div class="ruido-pagination-controls" id="ruidoPaginationControls">
            <!-- Dinámico por JS -->
        </div>
    </div>
</div>
