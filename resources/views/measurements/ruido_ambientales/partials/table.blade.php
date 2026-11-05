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
                            @php
                                $pointPhotos = !empty($m['images']) ? $m['images'] : (!empty($m['photos']) ? $m['photos'] : (!empty($m['image_path']) ? [$m['image_path']] : []));
                            @endphp
                            @if(!empty($pointPhotos) && count($pointPhotos) > 0)
                                <div class="table-thumb-preview"
                                    onclick='openPhotoViewer(@json($pointPhotos), "Punto {{ $m['num'] }}: {{ addslashes($m['normativa'] . ' - ' . $m['tipo_zona']) }}")'
                                    title="Ver fotografía ampliada">
                                    <img src="{{ $pointPhotos[0] }}" alt="Foto">
                                    @if(count($pointPhotos) > 1)
                                        <span class="table-thumb-count-badge">{{ count($pointPhotos) }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="table-thumb-preview empty" title="Sin fotografía registrada">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                </div>
                            @endif
                        </td>

                        <!-- 9. Registrado por (Badge oficial) -->
                        <td>
                            <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                {{ $m['registered_by'] }}
                            </span>
                        </td>

                        <!-- 10. Acciones -->
                        <td style="text-align: right;">
                            <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                <!-- Botón Mapa GPS -->
                                @if($m['latitude'] && $m['longitude'])
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick="openMapModal({{ $m['latitude'] }}, {{ $m['longitude'] }}, 'Punto {{ $m['num'] }}', {{ $m['is_compliant'] ? 'true' : 'false' }}, 'UTM: {{ $m['norte_x'] }}, {{ $m['norte_y'] }}')"
                                        title="Ver Mapa GPS del Punto" aria-label="Ver Mapa">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                                            <circle cx="12" cy="10" r="3" />
                                        </svg>
                                    </button>
                                @endif

                                <!-- Botón Editar -->
                                <button type="button" class="btn-admin-icon-action theme-cyan"
                                    onclick='openEditMeasurementModal(@json($m))'
                                    title="Editar Punto de Medición" aria-label="Editar">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                        <path d="m15 5 4 4" />
                                    </svg>
                                </button>

                                <!-- Botón Eliminar -->
                                <button type="button" class="btn-admin-icon-action theme-danger"
                                    onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['num'] }}')"
                                    title="Eliminar Punto" aria-label="Eliminar">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
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

    <!-- Barra de Paginación Reactiva (10 por página) -->
    <div class="illumination-pagination-container" id="ruidoPaginationBar">
        <div class="illumination-pagination-info" id="ruidoPaginationInfo">
            Mostrando <strong id="ruidoPageStart">1</strong> a <strong id="ruidoPageEnd">10</strong> de <strong
                id="ruidoPageTotal">{{ count($measurements) }}</strong> puntos de monitoreo
        </div>
        <div class="illumination-pagination-controls" id="ruidoPaginationControls">
            <!-- Dinámico por JS -->
        </div>
    </div>
</div>

<script>
    let currentRuidoPage = 1;
    const RUIDO_PAGE_SIZE = 10;

    function getRuidoMatchingRows() {
        const query = (document.getElementById('ruidoSearchInput')?.value || '').trim().toLowerCase();
        const allRows = Array.from(document.querySelectorAll('#ruidoMasterTable tbody tr.ruido-data-row'));
        if (!query) return allRows;
        return allRows.filter(row => {
            const data = (row.getAttribute('data-search') || '').toLowerCase();
            return data.includes(query);
        });
    }

    function updateRuidoPagination() {
        const allRows = Array.from(document.querySelectorAll('#ruidoMasterTable tbody tr.ruido-data-row'));
        const matchingRows = getRuidoMatchingRows();
        const totalItems = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / RUIDO_PAGE_SIZE));

        if (currentRuidoPage > totalPages) currentRuidoPage = totalPages;
        if (currentRuidoPage < 1) currentRuidoPage = 1;

        const startIdx = (currentRuidoPage - 1) * RUIDO_PAGE_SIZE;
        const endIdx = startIdx + RUIDO_PAGE_SIZE;

        allRows.forEach(row => row.style.display = 'none');
        matchingRows.slice(startIdx, endIdx).forEach(row => row.style.display = '');

        const emptyRow = document.getElementById('emptyTableRow');
        const noResultsRow = document.getElementById('noResultsRow');
        if (allRows.length === 0) {
            if (emptyRow) emptyRow.style.display = '';
            if (noResultsRow) noResultsRow.style.display = 'none';
        } else if (totalItems === 0) {
            if (emptyRow) emptyRow.style.display = 'none';
            if (noResultsRow) noResultsRow.style.display = '';
        } else {
            if (emptyRow) emptyRow.style.display = 'none';
            if (noResultsRow) noResultsRow.style.display = 'none';
        }

        const startLabel = totalItems === 0 ? 0 : startIdx + 1;
        const endLabel = Math.min(endIdx, totalItems);

        const pageStartEl = document.getElementById('ruidoPageStart');
        const pageEndEl = document.getElementById('ruidoPageEnd');
        const pageTotalEl = document.getElementById('ruidoPageTotal');
        if (pageStartEl) pageStartEl.textContent = startLabel;
        if (pageEndEl) pageEndEl.textContent = endLabel;
        if (pageTotalEl) pageTotalEl.textContent = totalItems;

        renderRuidoPaginationControls(totalPages);
    }

    function renderRuidoPaginationControls(totalPages) {
        const container = document.getElementById('ruidoPaginationControls');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        html += `<button type="button" class="ill-pag-btn" onclick="goToRuidoPage(${currentRuidoPage - 1})" ${currentRuidoPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || (p >= currentRuidoPage - 1 && p <= currentRuidoPage + 1)) {
                html += `<button type="button" class="ill-pag-btn ${p === currentRuidoPage ? 'active' : ''}" onclick="goToRuidoPage(${p})">${p}</button>`;
            } else if (p === currentRuidoPage - 2 || p === currentRuidoPage + 2) {
                html += `<span style="color:#94a3b8; padding:0 3px;">...</span>`;
            }
        }

        html += `<button type="button" class="ill-pag-btn" onclick="goToRuidoPage(${currentRuidoPage + 1})" ${currentRuidoPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>`;

        container.innerHTML = html;
    }

    function goToRuidoPage(page) {
        currentRuidoPage = page;
        updateRuidoPagination();
    }

    function searchRuidoAmbientalLive() {
        currentRuidoPage = 1;
        updateRuidoPagination();
    }

    document.addEventListener('DOMContentLoaded', function() {
        updateRuidoPagination();
    });
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(updateRuidoPagination, 50);
    }
</script>
