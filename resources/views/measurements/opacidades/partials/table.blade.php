<div class="opacity-table-card">
    <div class="opacity-toolbar">
        <div class="table-filter-pills">
            <button type="button" class="filter-pill-btn active" onclick="filterOpacityTable('all', this)">
                Todos ({{ $totalMeasurements }})
            </button>
            <button type="button" class="filter-pill-btn" onclick="filterOpacityTable('compliant', this)">
                Conformes ({{ $compliantCount }})
            </button>
            <button type="button" class="filter-pill-btn" onclick="filterOpacityTable('danger', this)">
                No Conformes ({{ $nonCompliantCount }})
            </button>
        </div>

        <div class="search-box-pill">
            <svg class="search-icon-inside" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <circle cx="11" cy="11" r="8" />
                <line x1="21" y1="21" x2="16.65" y2="16.65" />
            </svg>
            <input type="text" id="opacitySearchInput" class="search-input-pill"
                placeholder="Buscar por placa, vehículo, marca o personal..." onkeyup="searchOpacityTable()">
        </div>
    </div>

    <div style="overflow-x: auto; width: 100%;">
        <table id="opacityMasterTable">
            <thead>
                <tr>
                    <th style="width: 45px; text-align: center;">N°</th>
                    <th style="width: 140px;">Fecha / Hora</th>
                    <th style="width: 160px;">Vehículo / Placa</th>
                    <th style="width: 140px;">Marca / Modelo</th>
                    <th style="width: 130px;">Temp. Motor (°C)</th>
                    <th style="width: 160px;">Opacidad (1, 2, 3)</th>
                    <th style="width: 150px;">Opa Prom. / Estado</th>
                    <th style="width: 130px;">RPM Promedio</th>
                    <th style="width: 90px; text-align: center;">Fotos</th>
                    <th style="width: 160px;">Registrado Por</th>
                    <th style="width: 100px; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($measurements as $m)
                    <tr class="opacity-row-item" data-compliant="{{ $m['is_compliant'] ? 'true' : 'false' }}"
                        data-search="{{ strtolower($m['placa'] . ' ' . $m['tipo_vehiculo'] . ' ' . $m['marca'] . ' ' . $m['modelo'] . ' ' . $m['staff_name']) }}">
                        
                        <!-- 1. N° -->
                        <td style="text-align: center; font-weight: 800; color: #94a3b8; font-family: 'Outfit', sans-serif;">
                            {{ $m['num'] }}
                        </td>

                        <!-- 2. Fecha / Hora -->
                        <td>
                            <div style="font-weight: 700; color: var(--ink, #0f1c2e); font-size: 13px;">{{ $m['date_formatted'] }}</div>
                            <div style="font-size: 11px; color: #64748b; font-family: monospace;">{{ $m['time_raw'] ?: '—' }}</div>
                        </td>

                        <!-- 3. Vehículo / Placa -->
                        <td>
                            <div style="font-weight: 800; color: #0284c7; font-size: 13px; font-family: monospace;">{{ $m['placa'] ?: 'S/P' }}</div>
                            <div style="font-size: 12px; color: #475569; font-weight: 600;">{{ $m['tipo_vehiculo'] }}</div>
                        </td>

                        <!-- 4. Marca / Modelo -->
                        <td>
                            <div style="font-weight: 700; color: #1e293b;">{{ $m['marca'] ?: '—' }}</div>
                            <div style="font-size: 11.5px; color: #64748b;">{{ $m['modelo'] ?: '—' }}</div>
                        </td>

                        <!-- 5. Temp Motor -->
                        <td>
                            <span style="font-weight: 700; color: #334155; font-family: monospace;">
                                {{ $m['temp_c'] !== null ? $m['temp_c'] . ' °C' : '—' }}
                            </span>
                        </td>

                        <!-- 6. Opacidad 1, 2, 3 -->
                        <td>
                            <div style="font-size: 12px; font-family: monospace; color: #475569;">
                                {{ $m['opa_1'] !== null ? $m['opa_1'] : '—' }} /
                                {{ $m['opa_2'] !== null ? $m['opa_2'] : '—' }} /
                                {{ $m['opa_3'] !== null ? $m['opa_3'] : '—' }} %
                            </div>
                        </td>

                        <!-- 7. Opa Promedio / Estado -->
                        <td>
                            <div style="font-weight: 800; font-size: 13.5px; color: {{ $m['is_compliant'] ? '#059669' : '#dc2626' }}; font-family: 'Outfit', sans-serif;">
                                {{ $m['opa_promedio'] !== null ? $m['opa_promedio'] . ' %' : '—' }}
                            </div>
                            <div style="margin-top: 3px;">
                                @if($m['is_compliant'])
                                    <span class="badge-compliance-ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>CUMPLE</span></span>
                                @else
                                    <span class="badge-compliance-danger"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span>SUPERA</span></span>
                                @endif
                            </div>
                        </td>

                        <!-- 8. RPM Promedio -->
                        <td>
                            <span style="font-weight: 700; color: #475569; font-family: monospace;">
                                {{ $m['rpm_promedio'] !== null ? $m['rpm_promedio'] . ' RPM' : '—' }}
                            </span>
                        </td>

                        <!-- 9. Fotos -->
                        <td style="text-align: center;">
                            @if(!empty($m['photos']) && count($m['photos']) > 0)
                                <button type="button" class="btn-secondary-subtle" style="padding: 4px 8px; font-size: 11px;"
                                    onclick="openPhotoViewerModal('{{ $m['photos'][0] }}', 'Vehículo {{ $m['placa'] }}')" title="Ver fotografía">
                                    📷 {{ count($m['photos']) }}
                                </button>
                            @else
                                <span style="font-size: 11px; color: #94a3b8;">—</span>
                            @endif
                        </td>

                        <!-- 10. Registrado Por -->
                        <td>
                            <div style="font-weight: 600; color: #334155; font-size: 12.5px;">{{ $m['staff_name'] }}</div>
                        </td>

                        <!-- 11. Acciones -->
                        <td style="text-align: right;">
                            <div style="display: inline-flex; align-items: center; gap: 6px;">
                                <button type="button" class="btn-secondary-subtle" style="padding: 5px 8px;"
                                    onclick='openEditMeasurementModal(@json($m))' title="Editar medición">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                                </button>
                                <button type="button" class="btn-secondary-subtle" style="padding: 5px 8px; color: #dc2626; border-color: #fecaca;"
                                    onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['placa'] }}')" title="Eliminar medición">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" style="text-align: center; color: #64748b; padding: 40px;">
                            No hay mediciones de opacidad registradas en este módulo. Haz clic en <strong>Nuevo Punto de Medición</strong> para agregar la primera.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
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
