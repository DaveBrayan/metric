<div class="modal-backdrop-custom" id="allLocationsModal" role="dialog" aria-modal="true" aria-labelledby="allLocationsModalTitle">
    <div class="modal-dialog-opacity" style="max-width: 1100px; width: 95%;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div>
                    <h2 id="allLocationsModalTitle" style="font-size: 17px; margin: 0; font-family: 'Outfit', sans-serif; font-weight: 800; color: var(--ink);">
                        Ubicaciones de Medición — Opacidad Vehicular
                    </h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                        Parque vehicular georreferenciado y personal técnico evaluador
                    </span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 12px; font-weight: 800; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px;">
                    {{ $totalMeasurements }} Vehículos Registrados
                </span>
                <button type="button" class="btn-close-modal" onclick="closeAllLocationsModal()" aria-label="Cerrar">✕</button>
            </div>
        </div>

        <div class="modal-body-custom" style="padding: 18px 22px;">
            <div class="all-loc-modal-grid">
                <!-- Mapa Leaflet -->
                <div>
                    <div id="allLocationsMapLeaflet"></div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #64748b; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                                <span>Cumple LMP (≤ 50%)</span>
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 5px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #dc2626; display: inline-block;"></span>
                                <span>Supera Límite (> 50%)</span>
                            </span>
                        </div>
                        <span style="font-size: 11.5px; color: #94a3b8;">Haz clic en un marcador para ver ficha técnica del vehículo</span>
                    </div>
                </div>

                <!-- Panel Lateral con Lista de Vehículos -->
                <div class="all-loc-sidebar">
                    <div style="padding: 4px 6px; font-size: 12.5px; font-weight: 800; color: var(--ink); display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span>Vehículos Evaluados</span>
                        <span style="font-size: 11px; color: #0284c7;">Clic para enfocar</span>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        @forelse($measurements as $idx => $m)
                            <div class="all-loc-point-item" onclick="focusPointOnAllLocationsMap({{ $idx }})">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-size: 11.5px; font-weight: 800; background: {{ $m['is_compliant'] ? '#ecfdf5' : '#fef2f2' }}; color: {{ $m['is_compliant'] ? '#065f46' : '#991b1b' }}; border: 1px solid {{ $m['is_compliant'] ? '#a7f3d0' : '#fecaca' }}; padding: 1px 6px; border-radius: 4px;">
                                            #{{ $m['num'] }}
                                        </span>
                                        <span style="font-weight: 800; font-size: 12.5px; color: var(--ink); font-family: monospace;">
                                            {{ $m['placa'] ?: 'S/P' }}
                                        </span>
                                    </div>
                                    <span style="font-size: 11px; font-weight: 800; color: {{ $m['is_compliant'] ? '#16a34a' : '#dc2626' }}; font-family: 'Outfit', sans-serif;">
                                        {{ $m['opa_promedio'] !== null ? $m['opa_promedio'] . ' %' : '—' }}
                                    </span>
                                </div>
                                <div style="font-size: 11.5px; color: #64748b;">
                                    {{ $m['tipo_vehiculo'] }} {{ $m['marca'] }} {{ $m['modelo'] }}
                                </div>
                                <div style="font-size: 11px; color: #94a3b8; margin-top: 3px; display: flex; align-items: center; justify-content: space-between;">
                                    <span>Téc: <strong>{{ $m['staff_name'] }}</strong></span>
                                    <span>{{ $m['date_formatted'] }}</span>
                                </div>
                            </div>
                        @empty
                            <div style="padding: 24px; text-align: center; color: #94a3b8; font-size: 12px;">
                                No hay vehículos registrados.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closeAllLocationsModal()">Cerrar</button>
        </div>
    </div>
</div>
