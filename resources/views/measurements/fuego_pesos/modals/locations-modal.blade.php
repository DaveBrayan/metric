    <!-- ==========================================================================
             MODAL 6: MAPA GENERAL DE TODAS LAS UBICACIONES Y PERSONAL REGISTRADOR
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="allLocationsModal" role="dialog" aria-modal="true"
        aria-labelledby="allLocationsModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1100px; width: 95%;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="allLocationsModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: var(--ink);">
                            Ubicaciones de Sectores de Carga de Fuego por Peso
                        </h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                            Sectores de incendio geolocalizados, niveles de riesgo intrínseco ($Q_s$) y personal técnico evaluador
                        </span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span
                        style="font-size: 12px; font-weight: 800; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 4px 10px; border-radius: 20px;">
                        {{ $totalMeasurements }} Sectores Registrados
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeAllLocationsModal()"
                        aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <div class="all-loc-modal-grid">
                    <!-- Mapa Leaflet Interactivo -->
                    <div>
                        <div id="allLocationsMapLeaflet"></div>
                        <div
                            style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #64748b; flex-wrap: wrap; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                                    <span>Riesgo Bajo (&le; 840 MJ/m²)</span>
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #d97706; display: inline-block;"></span>
                                    <span>Riesgo Medio (840 - 3360 MJ/m²)</span>
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #e11d48; display: inline-block;"></span>
                                    <span>Riesgo Alto (&gt; 3360 MJ/m²)</span>
                                </span>
                            </div>
                            <span style="font-size: 11.5px; color: #94a3b8;">Haz clic en un marcador para ver el detalle y evaluador</span>
                        </div>
                    </div>

                    <!-- Panel Lateral con Lista de Sectores y Personal Registrador -->
                    <div class="all-loc-sidebar">
                        <div
                            style="padding: 4px 6px; font-size: 12.5px; font-weight: 800; color: var(--ink); display: flex; align-items: center; justify-content: space-between;">
                            <span>Sectores Registrados</span>
                            <span style="font-size: 11px; color: #0284c7; font-weight: 700;">Clic para enfocar</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse($measurements as $idx => $m)
                                <div class="all-loc-point-item" onclick="focusPointOnAllLocationsMap({{ $idx }})">
                                    <div class="all-loc-point-top">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span
                                                style="font-size: 11.5px; font-weight: 800; background: {{ ($m['risk_level'] ?? '') === 'Bajo' ? '#ecfdf5' : (($m['risk_level'] ?? '') === 'Medio' ? '#fffbeb' : '#fff1f2') }}; color: {{ ($m['risk_level'] ?? '') === 'Bajo' ? '#065f46' : (($m['risk_level'] ?? '') === 'Medio' ? '#92400e' : '#9f1239') }}; border: 1px solid {{ ($m['risk_level'] ?? '') === 'Bajo' ? '#a7f3d0' : (($m['risk_level'] ?? '') === 'Medio' ? '#fde68a' : '#fecdd3') }}; padding: 1px 6px; border-radius: 4px;">
                                                #{{ $m['num'] }}
                                            </span>
                                            <strong
                                                style="font-size: 12.5px; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                                {{ $m['sector_name'] }}
                                            </strong>
                                        </div>
                                        <span
                                            style="font-size: 11px; font-weight: 700; color: {{ ($m['risk_level'] ?? '') === 'Bajo' ? '#059669' : (($m['risk_level'] ?? '') === 'Medio' ? '#d97706' : '#e11d48') }};">
                                            {{ number_format($m['qs_mj_m2'] ?? 0, 1) }} MJ/m²
                                        </span>
                                    </div>
                                    <div
                                        style="font-size: 11.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $m['macroarea'] }} • S = {{ number_format($m['dimensions']['area_m2'] ?? 0, 1) }} m²
                                    </div>
                                    <div
                                        style="display: flex; align-items: center; gap: 6px; margin-top: 3px; font-size: 11.5px; color: #0284c7; background: #f0f9ff; padding: 4px 8px; border-radius: 6px; border: 1px solid #e0f2fe;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        <span
                                            style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            Evaluado por: <strong>{{ $m['registered_by'] }}</strong>
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; color: #94a3b8; padding: 20px; font-size: 12.5px;">
                                    No hay sectores registrados en este módulo.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-primary-hero-action" onclick="closeAllLocationsModal()"
                    style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
            </div>
        </div>
    </div>
