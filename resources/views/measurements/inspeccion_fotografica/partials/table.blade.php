<!-- ==========================================================================
     TABLA MAESTRA DE PUNTOS DE INSPECCIÓN FOTOGRÁFICA
     ========================================================================== -->
<div class="illumination-table-card">
    <!-- Toolbar de Búsqueda -->
    <div class="illumination-toolbar" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
        <div style="font-weight: 800; font-size: 14px; color: var(--ink); display: flex; align-items: center; gap: 8px;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="8" y1="6" x2="21" y2="6"/>
                <line x1="8" y1="12" x2="21" y2="12"/>
                <line x1="8" y1="18" x2="21" y2="18"/>
                <line x1="3" y1="6" x2="3.01" y2="6"/>
                <line x1="3" y1="12" x2="3.01" y2="12"/>
                <line x1="3" y1="18" x2="3.01" y2="18"/>
            </svg>
            <span>Catálogo de Puntos Evaluados</span>
            <span id="tablePointsCounter" style="font-size: 11.5px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 6px;">
                {{ $measurements->count() }} Puntos
            </span>
        </div>

        <div class="search-box-pill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" id="photographicSearchInput" class="search-input-pill"
                placeholder="Buscar por área, categoría, descripción, técnico o GPS..."
                onkeyup="searchPhotographicLive()">
        </div>
    </div>

    <!-- Tabla Responsive -->
    <div class="table-responsive-box">
        <table class="modern-table" id="photographicMasterTable">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">N°</th>
                    <th style="width: 120px;">Fecha / Hora</th>
                    <th style="width: 160px;">Área / Sector</th>
                    <th style="min-width: 180px;">Categoría</th>
                    <th style="min-width: 180px;">Descripción</th>
                    <th style="width: 120px; text-align: center;">Fotografías</th>
                    <th style="width: 150px;">Ubicación GPS / UTM</th>
                    <th style="width: 140px;">Registrado por</th>
                    <th style="width: 100px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="photographicTableBody">
                @forelse($measurements as $m)
                    <tr class="photographic-data-row"
                        data-search="{{ strtolower($m['point_number'] . ' ' . $m['area'] . ' ' . $m['observation'] . ' ' . $m['description'] . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">

                        <!-- 1. N° de Punto -->
                        <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #0284c7; font-size: 13.5px; text-align: center;">
                            #{{ $m['point_number'] }}
                        </td>

                        <!-- 2. Fecha / Hora -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date_formatted'] }}</span>
                                @if(!empty($m['time']))
                                    <span class="table-time-pill" title="Hora de captura">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10" />
                                            <polyline points="12 6 12 12 16 14" />
                                        </svg>
                                        <span>{{ $m['time'] }}</span>
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- 3. Área / Sector -->
                        <td>
                            <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $m['area'] }}</span>
                        </td>

                        <!-- 4. Categoría -->
                        <td>
                            <span style="font-size: 12px; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 3px 8px; border-radius: 6px; border: 1px solid #bae6fd; display: inline-block;">
                                {{ $m['observation'] }}
                            </span>
                        </td>

                        <!-- 5. Descripción -->
                        <td>
                            @if(!empty($m['description']))
                                <span style="font-size: 12px; color: #475569; line-height: 1.35;">
                                    {{ $m['description'] }}
                                </span>
                            @else
                                <span style="color: #94a3b8; font-style: italic; font-size: 12px;">Sin descripción</span>
                            @endif
                        </td>

                        <!-- 6. Fotografías (Hasta 3 miniaturas) -->
                        <td style="text-align: center;">
                            @if(!empty($m['images']) && count($m['images']) > 0)
                                <div class="photo-badge-wrap" style="justify-content: center;">
                                    @foreach(array_slice($m['images'], 0, 3) as $imgIdx => $img)
                                        <img src="{{ $img }}" alt="Foto {{ $imgIdx + 1 }}" class="photo-thumb-clickable"
                                            onclick='openPhotoViewerModal(@json($m["images"]), {{ $imgIdx }}, "{{ addslashes($m["point_name"]) }}")'
                                            title="Ver fotografía ampliada">
                                    @endforeach
                                    @if(count($m['images']) > 3)
                                        <span class="photo-count-badge">+{{ count($m['images']) - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin fotos</span>
                            @endif
                        </td>

                        <!-- 7. Ubicación GPS / UTM -->
                        <td>
                            @if($m['latitude'] !== null && $m['longitude'] !== null)
                                <button type="button" class="location-coord-badge"
                                    onclick="openSinglePointMap({{ $m['latitude'] }}, {{ $m['longitude'] }}, '{{ addslashes($m['point_name']) }}', '{{ addslashes($m['area']) }}')"
                                    title="Ver ubicación en el mapa">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    @if(!empty($m['utm_easting']) && !empty($m['utm_northing']))
                                        <span>E:{{ round($m['utm_easting']) }} N:{{ round($m['utm_northing']) }}</span>
                                    @else
                                        <span>{{ number_format($m['latitude'], 4) }}, {{ number_format($m['longitude'], 4) }}</span>
                                    @endif
                                </button>
                            @elseif(!empty($m['location']))
                                <span class="location-coord-badge empty" title="{{ $m['location'] }}">
                                    {{ Str::limit($m['location'], 16) }}
                                </span>
                            @else
                                <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin GPS</span>
                            @endif
                        </td>

                        <!-- 8. Registrado por -->
                        <td>
                            <span class="badge-registered-staff" title="Registrado por: {{ $m['registered_by'] }}">
                                {{ $m['registered_by'] }}
                            </span>
                        </td>

                        <!-- 9. Acciones (Ver Detalle + Eliminar) -->
                        <td style="text-align: right;">
                            <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                <!-- Ver Detalle (Abre modal en 3 columnas en modo consulta) -->
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
                                    onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['point_number'] }}')"
                                    title="Eliminar punto" aria-label="Eliminar">
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
                        <td colspan="9" style="text-align: center; padding: 48px 20px;">
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px;">
                                <div style="width: 54px; height: 54px; border-radius: 14px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                                        <circle cx="12" cy="13" r="3"/>
                                    </svg>
                                </div>
                                <div style="font-weight: 800; font-size: 15px; color: var(--ink);">
                                    Sin Puntos de Inspección Registrados
                                </div>
                                <div style="font-size: 13px; color: #64748b; max-width: 440px;">
                                    Los puntos de inspección fotográfica se sincronizan automáticamente desde la aplicación móvil o puedes gestionarlos directamente.
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
