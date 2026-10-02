<div class="opacity-table-card">
    <div class="opacity-toolbar" style="display: flex; justify-content: flex-end;">
        <div class="search-box-pill">
            <svg class="search-icon-inside" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" id="opacitySearchInput" class="search-input-pill"
                placeholder="Buscar por área, placa, vehículo, marca o personal..." onkeyup="searchOpacityTable()">
        </div>
    </div>

    <div style="overflow-x: auto; width: 100%;">
        <table class="modern-table" id="opacityMasterTable">
            <thead>
                <tr>
                    <th style="width: 50px; text-align: center;">N°</th>
                    <th style="width: 120px;">Fecha / Hora</th>
                    <th style="width: 160px;">Área / Altitud</th>
                    <th style="width: 150px;">Vehículo / Placa</th>
                    <th style="width: 140px;">Marca / Modelo</th>
                    <th style="width: 110px;">Temp. Motor</th>
                    <th style="width: 120px;">Límite Normativa</th>
                    <th style="width: 160px;">Opacidad Prom.</th>
                    <th style="width: 120px;">RPM Promedio</th>
                    <th style="width: 85px; text-align: center;">Imágenes</th>
                    <th style="width: 150px;">Registrado Por</th>
                    <th style="width: 100px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody id="opacityTableBody">
                @forelse($measurements as $m)
                    <tr class="opacity-row-item" data-compliant="{{ $m['is_compliant'] ? 'true' : 'false' }}"
                        data-search="{{ strtolower($m['num'] . ' ' . ($m['area'] ?? '') . ' ' . ($m['altitud'] ?? '') . ' ' . $m['placa'] . ' ' . $m['tipo_vehiculo'] . ' ' . $m['marca'] . ' ' . $m['modelo'] . ' ' . $m['staff_name']) }}">
                        
                        <!-- 1. N° -->
                        <td style="text-align: center; font-weight: 800; color: #94a3b8; font-family: 'Outfit', sans-serif; font-size: 13.5px;">
                            {{ $m['num'] }}
                        </td>

                        <!-- 2. Fecha / Hora -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-size: 12.5px; font-weight: 700; color: var(--ink, #0f1c2e);">{{ $m['date_formatted'] }}</span>
                                <span class="table-time-pill" title="Hora de medición">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                    <span>{{ $m['time_raw'] ?: '—' }}</span>
                                </span>
                            </div>
                        </td>

                        <!-- 3. Área / Altitud -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 700; color: var(--ink, #0f1c2e); font-size: 13px;">{{ $m['area'] ?: 'Área General' }}</span>
                                <span class="altitud-pill" title="Rango de Altitud sobre el nivel del mar">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m8 3 4 8 5-5 5 15H2L8 3z"/></svg>
                                    <span>{{ $m['altitud'] ?: '1500-3000' }} msnm</span>
                                </span>
                            </div>
                        </td>

                        <!-- 4. Vehículo / Placa -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 800; color: #0284c7; font-size: 13px; font-family: monospace;">{{ $m['placa'] ?: 'S/P' }}</span>
                                <span style="font-size: 11.5px; color: #64748b; font-weight: 600;">{{ $m['tipo_vehiculo'] }}</span>
                            </div>
                        </td>

                        <!-- 5. Marca / Modelo -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span style="font-weight: 700; color: #1e293b; font-size: 13px;">{{ $m['marca'] ?: '—' }}</span>
                                <span style="font-size: 11.5px; color: #64748b; font-weight: 500;">{{ $m['modelo'] ?: '—' }}</span>
                            </div>
                        </td>

                        <!-- 6. Temp Motor -->
                        <td>
                            <span style="font-weight: 700; color: #334155; font-family: monospace; font-size: 12.5px;">
                                {{ $m['temp_c'] !== null ? $m['temp_c'] . ' °C' : '—' }}
                            </span>
                        </td>

                        <!-- 7. Límite Normativa -->
                        <td>
                            <span style="font-size: 12px; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 3px 8px; border-radius: 6px; border: 1px solid #bae6fd; font-family: monospace; display: inline-block;">
                                {{ number_format($m['limite_normativa'], 2, ',', '.') }} m⁻¹
                            </span>
                        </td>

                        <!-- 8. Opacidad Promedio / Evaluaciones -->
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 2px;">
                                <span class="badge-compliance-{{ $m['is_compliant'] ? 'ok' : 'danger' }}">
                                    @if($m['is_compliant'])
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                    @else
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                    @endif
                                    <span>{{ $m['opa_promedio'] !== null ? number_format($m['opa_promedio'], 2, ',', '.') . ' %' : '—' }}</span>
                                </span>
                                @if($m['opa_1'] !== null || $m['opa_2'] !== null || $m['opa_3'] !== null)
                                    <span style="font-size: 10.5px; font-family: monospace; color: #64748b; font-weight: 600;">
                                        ({{ $m['opa_1'] !== null ? $m['opa_1'] : '—' }} / {{ $m['opa_2'] !== null ? $m['opa_2'] : '—' }} / {{ $m['opa_3'] !== null ? $m['opa_3'] : '—' }})
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- 9. RPM Promedio -->
                        <td>
                            <span style="font-weight: 700; color: #15803d; font-family: monospace; font-size: 12.5px;">
                                {{ $m['rpm_promedio'] !== null ? number_format($m['rpm_promedio'], 2, ',', '.') . ' RPM' : '—' }}
                            </span>
                        </td>

                        <!-- 10. Imágenes -->
                        <td style="text-align: center;">
                            @if(!empty($m['photos']) && count($m['photos']) > 0)
                                <div class="table-thumb-preview"
                                    onclick="openPhotoViewer('{{ $m['photos'][0] }}', 'Vehículo {{ $m['placa'] ?: $m['num'] }}')"
                                    title="Ver fotografía ampliada">
                                    <img src="{{ $m['photos'][0] }}" alt="Foto">
                                    @if(count($m['photos']) > 1)
                                        <span style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">{{ count($m['photos']) }}</span>
                                    @endif
                                </div>
                            @else
                                <div class="table-thumb-preview" style="cursor: default; opacity: 0.5;" title="Sin fotografía registrada">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                </div>
                            @endif
                        </td>

                        <!-- 11. Registrado Por -->
                        <td>
                            <span class="badge-registered-staff" title="Registrado por: {{ $m['staff_name'] }}">
                                {{ $m['staff_name'] }}
                            </span>
                        </td>

                        <!-- 12. Acciones -->
                        <td style="text-align: right;">
                            <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                <!-- Ver Detalle -->
                                <button type="button" class="btn-admin-icon-action theme-cyan"
                                    onclick='openViewMeasurementModal(@json($m))' title="Ver detalle del vehículo"
                                    aria-label="Ver">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                </button>

                                <!-- Eliminar -->
                                <button type="button" class="btn-admin-icon-action theme-danger"
                                    onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['placa'] ?: $m['num'] }}')"
                                    title="Eliminar medición" aria-label="Eliminar">
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
                    <tr>
                        <td colspan="12" style="text-align: center; color: #64748b; padding: 40px;">
                            No hay mediciones de opacidad registradas en este módulo. Haz clic en <strong>Nuevo Punto de Medición</strong> para agregar la primera.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function searchOpacityTable() {
        const query = document.getElementById('opacitySearchInput').value.trim().toLowerCase();
        const rows = document.querySelectorAll('.opacity-row-item');
        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            row.style.display = searchData.includes(query) ? '' : 'none';
        });
    }

    function confirmDeleteMeasurement(id, placa) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar medición?',
                text: `Se eliminará el registro del vehículo ${placa}. Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                buttonsStyling: false,
                customClass: {
                    popup: 'metric-swal-popup',
                    confirmButton: 'metric-swal-btn-danger',
                    cancelButton: 'metric-swal-btn-cancel'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('deleteMeasurementForm');
                    form.action = `/modulos/{{ $module->id }}/opacidad/mediciones/${id}`;
                    form.submit();
                }
            });
        } else if (confirm(`¿Eliminar la medición del vehículo ${placa}?`)) {
            const form = document.getElementById('deleteMeasurementForm');
            form.action = `/modulos/{{ $module->id }}/opacidad/mediciones/${id}`;
            form.submit();
        }
    }
</script>

