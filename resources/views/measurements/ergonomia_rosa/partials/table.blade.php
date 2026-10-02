    <!-- 3. Tabla Maestra de Evaluaciones de Ergonomía ROSA con Búsqueda Reactiva -->
    <div class="illumination-table-card">
        <div class="illumination-toolbar" style="display: flex; justify-content: space-between; align-items: center;">
            <div class="illumination-table-title" style="display: flex; align-items: center; gap: 10px;">
                <h2 style="font-family: 'Outfit', sans-serif; font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">Evaluaciones Ergonómicas ROSA Registradas</h2>
                <span class="badge-count" id="rosaHeaderTotalCount" style="background: #e0f2fe; color: #0284c7; font-size: 12px; font-weight: 800; padding: 3px 10px; border-radius: 9999px; border: 1px solid #bae6fd;">{{ $totalMeasurements }} {{ $totalMeasurements == 1 ? 'puesto' : 'puestos' }}</span>
            </div>

            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="rosaTableSearchInput" class="search-input-pill"
                    placeholder="Buscar por área, puesto, riesgo, tarea..." onkeyup="filterRosaTable()">
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="illumination-custom-table" id="rosaMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <th style="width: 110px;">FECHA / HORA</th>
                        <th>ÁREA / PUESTO DE TRABAJO</th>
                        <th>FACTOR DE RIESGO & TAREAS</th>
                        <th>SCORE ROSA & ACCIÓN</th>
                        <th style="text-align: center; width: 75px;">IMÁGENES</th>
                        <th>REGISTRADO POR</th>
                        <th style="text-align: right; width: 120px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody id="rosaTableBody">
                    @forelse($measurements as $m)
                        <tr class="rosa-data-row" data-searchable="{{ strtolower(($m['num'] ?? '') . ' ' . ($m['area_sector'] ?? '') . ' ' . ($m['puesto_trabajo'] ?? '') . ' ' . ($m['factor_riesgo'] ?? '') . ' ' . ($m['risk_level'] ?? '') . ' ' . ($m['registered_by'] ?? '') . ' ' . (is_array($m['tareas'] ?? null) ? implode(' ', $m['tareas']) : ($m['tareas'] ?? ''))) }}">
                            <!-- 1. Número Correlativo -->
                            <td style="text-align: center;">
                                <span class="reba-num-badge">{{ $m['num'] }}</span>
                            </td>

                            <!-- 2. Fecha y Hora -->
                            <td>
                                <div style="font-weight: 800; color: #0f172a; font-size: 12.5px;">
                                    {{ $m['date'] }}
                                </div>
                                <div style="font-size: 11px; color: #64748b; font-family: monospace;">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" style="vertical-align: -1px; margin-right: 2px;">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>{{ $m['time'] }}
                                </div>
                            </td>

                            <!-- 3. Área / Puesto de Trabajo -->
                            <td>
                                <div style="font-weight: 800; color: #0f172a; font-size: 13px;">
                                    {{ $m['puesto_trabajo'] }}
                                </div>
                                <div style="font-size: 11.5px; color: #64748b; margin-top: 1px;">
                                    {{ $m['area_sector'] }}
                                </div>
                                <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 1px 6px; border-radius: 4px; border: 1px solid #bae6fd;">
                                        {{ $m['num_trabajadores'] }} {{ $m['num_trabajadores'] == 1 ? 'Trabajador' : 'Trabajadores' }}
                                    </span>
                                </div>
                            </td>

                            <!-- 4. Factor de Riesgo & Tareas -->
                            <td>
                                @if(!empty($m['factor_riesgo']))
                                    <span style="display: inline-block; font-size: 11px; font-weight: 800; color: #0369a1; background: #f0f9ff; border: 1px solid #bae6fd; padding: 2px 8px; border-radius: 6px; margin-bottom: 4px;">
                                        {{ $m['factor_riesgo'] }}
                                    </span>
                                @endif
                                @if(!empty($m['tareas']) && is_array($m['tareas']))
                                    <div style="font-size: 11.5px; color: #334155; line-height: 1.3;">
                                        @foreach(array_slice($m['tareas'], 0, 2) as $t)
                                             <div>• {{ $t }}</div>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($m['ubicacion_sintoma']) && $m['ubicacion_sintoma'] !== 'Ninguna')
                                    <div style="font-size: 10.5px; color: #b91c1c; font-weight: 700; margin-top: 2px;">
                                        Síntoma: {{ $m['ubicacion_sintoma'] }}
                                    </div>
                                @endif
                            </td>

                            <!-- 5. Score ROSA & Acción -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="reba-score-pill {{ $m['badge_class'] ?? 'risk-medio' }}">
                                        <span class="reba-score-val">{{ $m['score_final'] ?? 1 }}</span>
                                        <span class="reba-score-label">ROSA</span>
                                    </div>
                                    <div>
                                        <div style="font-weight: 800; font-size: 12.5px; color: #0f172a;">
                                            Riesgo {{ $m['risk_level'] ?? 'Medio' }}
                                        </div>
                                        <div style="font-size: 11px; color: #64748b; max-width: 140px; line-height: 1.2;">
                                            {{ $m['action_level'] ?? 'Es necesaria acción' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- 7. Imágenes (Miniatura con Lightbox) -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview"
                                        onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Puesto {{ $m['num'] }}: {{ addslashes($m['puesto_trabajo']) }}')"
                                        title="Ver evidencia fotográfica">
                                        <img src="{{ $m['image_path'] }}" alt="Foto">
                                        @if(!empty($m['images_count']) && $m['images_count'] > 1)
                                            <span style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">{{ $m['images_count'] }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="table-thumb-preview" style="cursor: default; opacity: 0.5;" title="Sin fotografía">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8"
                                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                            <circle cx="9" cy="9" r="2" />
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            <!-- 8. Registrado por (Badge elegante) -->
                            <td>
                                <span class="badge-registered-staff" title="Evaluador: {{ $m['registered_by'] }}">
                                    {{ $m['registered_by'] }}
                                </span>
                            </td>

                            <!-- 9. Acciones (Ver Tablas, Ver Detalle y Eliminar) -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="display: flex; justify-content: flex-end; gap: 6px;">
                                    <!-- Ver Tablas / Anexo 2 -->
                                    <a href="{{ route('modules.ergonomia_rosa.tables', ['id' => $module->id ?? 1, 'evaluation_id' => $m['id']]) }}"
                                        class="btn-admin-icon-action theme-amber"
                                        title="Ver Tablas Normativas y Registro Anexo 2 de este puesto" aria-label="Ver Tablas">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" />
                                            <path d="M3 9h18" />
                                            <path d="M3 15h18" />
                                            <path d="M9 3v18" />
                                        </svg>
                                    </a>

                                    <!-- Ver Detalle / Editar -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick='openViewMeasurementModal(@json($m))' title="Ver detalle de evaluación ROSA"
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
                                        title="Eliminar Evaluación" aria-label="Eliminar">
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
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 36px;">
                                No se han registrado evaluaciones ergonómicas ROSA en este estudio.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron evaluaciones que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (10 por página) -->
        <div class="illumination-pagination-container" id="rosaPaginationBar">
            <div class="illumination-pagination-info" id="rosaPaginationInfo">
                Mostrando <strong id="rosaPageStart">{{ $totalMeasurements > 0 ? 1 : 0 }}</strong> a <strong id="rosaPageEnd">{{ min(10, $totalMeasurements) }}</strong> de <strong
                    id="rosaPageTotal">{{ $totalMeasurements }}</strong> evaluaciones ROSA
            </div>
            <div class="illumination-pagination-controls" id="rosaPaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>
