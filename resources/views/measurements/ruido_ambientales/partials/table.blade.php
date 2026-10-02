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
                placeholder="Buscar por zona, colindancia, personal o coordenadas..."
                onkeyup="searchRuidoAmbientalLive()">
        </div>
    </div>

    <!-- Tabla Responsive -->
    <div class="table-responsive-box">
        <table class="modern-table" id="ruidoMasterTable">
            <thead>
                <tr>
                    <th style="width: 55px; text-align: center;">N°</th>
                    <th style="width: 110px;">Fecha / Hora</th>
                    <th style="width: 165px;">Normativa & Zona</th>
                    <th style="width: 95px; text-align: center;">LMP (dBA)</th>
                    <th style="width: 220px;">Colindancias & Coordenadas UTM</th>
                    <th style="width: 170px;">Puntos Cardinales</th>
                    <th style="width: 125px; text-align: center;">Leq Total / Estado</th>
                    <th style="width: 80px; text-align: center;">Imágenes</th>
                    <th style="width: 140px;">Registrado por</th>
                    <th style="width: 110px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="ruidoTableBody">
                @forelse($measurements as $m)
                    <tr class="ruido-data-row"
                        data-compliant="{{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}"
                        data-normativa="{{ $m['normativa'] }}"
                        data-search="{{ strtolower($m['num'] . ' ' . $m['normativa'] . ' ' . $m['tipo_zona'] . ' ' . $m['norte_colindancia'] . ' ' . $m['sur_colindancia'] . ' ' . $m['este_colindancia'] . ' ' . $m['oeste_colindancia'] . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">

                        <!-- 1. N° -->
                        <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                            {{ $m['num'] }}
                        </td>

                        <!-- 2. Fecha / Hora -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; color: #64748b; font-family: monospace;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                                        <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <span>{{ $m['time'] }}</span>
                                </span>
                            </div>
                        </td>

                        <!-- 3. Normativa & Zona -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 700; color: #0284c7; font-size: 12.5px;">{{ $m['normativa'] }}</span>
                                <span style="font-size: 12px; color: var(--ink); font-weight: 600;">{{ $m['tipo_zona'] }}</span>
                                <span style="font-size: 11px; color: #64748b;">{{ $m['horario'] }}</span>
                            </div>
                        </td>

                        <!-- 4. LMP -->
                        <td style="text-align: center;">
                            <span style="display: inline-block; font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 13px; color: var(--ink); background: #f1f5f9; padding: 3px 8px; border-radius: 6px;">
                                {{ $m['limite_normativa'] }}
                            </span>
                        </td>

                        <!-- 5. Colindancias & Coordenadas UTM -->
                        <td>
                            <div style="font-size: 11px; display: flex; flex-direction: column; gap: 2px;">
                                <div><strong style="color: #0284c7;">N:</strong> {{ $m['norte_colindancia'] }} <span style="color: #94a3b8; font-family: monospace;">({{ $m['norte_x'] }})</span></div>
                                <div><strong style="color: #0284c7;">S:</strong> {{ $m['sur_colindancia'] }} <span style="color: #94a3b8; font-family: monospace;">({{ $m['sur_x'] }})</span></div>
                                <div><strong style="color: #0284c7;">E:</strong> {{ $m['este_colindancia'] }} <span style="color: #94a3b8; font-family: monospace;">({{ $m['este_x'] }})</span></div>
                                <div><strong style="color: #0284c7;">O:</strong> {{ $m['oeste_colindancia'] }} <span style="color: #94a3b8; font-family: monospace;">({{ $m['oeste_x'] }})</span></div>
                            </div>
                        </td>

                        <!-- 6. Puntos Cardinales Leq -->
                        <td>
                            <div style="font-size: 11.5px; display: flex; flex-direction: column; gap: 2px;">
                                <div><strong>P1 (N):</strong> {{ $m['p1_norte_leq'] !== null ? $m['p1_norte_leq'] . ' dBA' : '—' }} <span style="color: #94a3b8; font-size: 10.5px;">({{ count($m['p1_norte_puntos']) }} pts)</span></div>
                                <div><strong>P2 (S):</strong> {{ $m['p2_sur_leq'] !== null ? $m['p2_sur_leq'] . ' dBA' : '—' }} <span style="color: #94a3b8; font-size: 10.5px;">({{ count($m['p2_sur_puntos']) }} pts)</span></div>
                                <div><strong>P3 (E):</strong> {{ $m['p3_este_leq'] !== null ? $m['p3_este_leq'] . ' dBA' : '—' }} <span style="color: #94a3b8; font-size: 10.5px;">({{ count($m['p3_este_puntos']) }} pts)</span></div>
                                <div><strong>P4 (O):</strong> {{ $m['p4_oeste_leq'] !== null ? $m['p4_oeste_leq'] . ' dBA' : '—' }} <span style="color: #94a3b8; font-size: 10.5px;">({{ count($m['p4_oeste_puntos']) }} pts)</span></div>
                            </div>
                        </td>

                        <!-- 7. Leq Total / Estado -->
                        <td style="text-align: center;">
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                                <span style="font-family: 'Outfit', sans-serif; font-size: 14px; font-weight: 800; color: {{ $m['is_compliant'] ? '#059669' : '#dc2626' }};">
                                    {{ $m['leq_d'] }} dBA
                                </span>
                                @if($m['is_compliant'])
                                    <span class="badge-compliance-ok">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12" /></svg>
                                        <span>CUMPLE</span>
                                    </span>
                                @else
                                    <span class="badge-compliance-danger">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" /></svg>
                                        <span>NO CUMPLE</span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- 8. Imágenes -->
                        <td style="text-align: center;">
                            @if(!empty($m['image_path']))
                                <button type="button" onclick="openPhotoViewerModal('{{ $m['image_path'] }}', 'Punto {{ $m['num'] }}', '{{ addslashes($m['normativa'] . ' - ' . $m['tipo_zona']) }}')"
                                    style="border: none; background: transparent; cursor: pointer; padding: 0;" title="Ver evidencia fotográfica">
                                    <img src="{{ $m['image_path'] }}" alt="Foto" style="width: 38px; height: 38px; border-radius: 8px; object-fit: cover; border: 1.5px solid #cbd5e1;">
                                </button>
                            @else
                                <span style="font-size: 11.5px; color: #cbd5e1;">—</span>
                            @endif
                        </td>

                        <!-- 9. Registrado por -->
                        <td>
                            <span style="font-size: 12px; font-weight: 600; color: #334155;">{{ $m['registered_by'] }}</span>
                        </td>

                        <!-- 10. Acciones -->
                        <td>
                            <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px;">
                                <!-- Botón Mapa GPS -->
                                @if($m['latitude'] && $m['longitude'])
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick="openMapModal({{ $m['latitude'] }}, {{ $m['longitude'] }}, 'Punto {{ $m['num'] }}', {{ $m['is_compliant'] ? 'true' : 'false' }}, 'UTM: {{ $m['norte_x'] }}, {{ $m['norte_y'] }}')"
                                        title="Ver Mapa GPS del Punto" aria-label="Ver Mapa">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                    </button>
                                @endif

                                <!-- Botón Editar -->
                                <button type="button" class="btn-admin-icon-action theme-amber"
                                    onclick='openEditMeasurementModal(@json($m))'
                                    title="Editar Punto de Medición" aria-label="Editar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        <path d="m15 5 4 4" />
                                    </svg>
                                </button>

                                <!-- Botón Eliminar -->
                                <button type="button" class="btn-admin-icon-action theme-danger"
                                    onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['num'] }}')"
                                    title="Eliminar Punto" aria-label="Eliminar">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyTableRow">
                        <td colspan="10" style="text-align: center; color: #64748b; padding: 36px;">
                            No hay puntos de medición registrados en este módulo. Haz clic en <strong>Nuevo Punto de Medición</strong> para agregar el primero.
                        </td>
                    </tr>
                @endforelse
                <tr id="noResultsRow" style="display: none;">
                    <td colspan="10" style="text-align: center; color: #64748b; padding: 36px;">
                        No se encontraron mediciones que coincidan con la búsqueda.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
