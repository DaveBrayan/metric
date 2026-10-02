<!-- ==========================================================================
     MODAL 6: MAPA GENERAL DE TODAS LAS UBICACIONES Y PERSONAL REGISTRADOR
     ========================================================================== -->
<div class="modal-backdrop-custom" id="allLocationsModal" role="dialog" aria-modal="true"
    aria-labelledby="allLocationsModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 1100px; width: 95%; max-height: 90vh; display: flex; flex-direction: column;">
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
                    <h2 id="allLocationsModalTitle" style="font-size: 17px; margin: 0; font-weight: 800; color: var(--ink, #0f172a);">
                        Ubicaciones de Puestos de Trabajo — Ergonomía REBA
                    </h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">
                        Puestos evaluados geolocalizados, niveles de riesgo ergonómico y ergónomo evaluador
                    </span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span
                    style="font-size: 12px; font-weight: 800; background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; padding: 4px 10px; border-radius: 20px;">
                    {{ $totalMeasurements }} Puestos Registrados
                </span>
                <button type="button" class="btn-close-modal" onclick="closeAllLocationsModal()"
                    aria-label="Cerrar">✕</button>
            </div>
        </div>

        <div class="modal-body-custom" style="padding: 18px 22px; flex: 1; overflow-y: auto;">
            <div class="all-loc-modal-grid">
                <!-- Mapa Leaflet Interactivo -->
                <div>
                    <div id="allLocationsMapLeaflet" style="height: 390px; border-radius: 12px; border: 1.5px solid #cbd5e1; background: #f8fafc;"></div>
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #64748b; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #047857; display: inline-block;"></span>
                                <span>Inapreciable (1)</span>
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #4d7c0f; display: inline-block;"></span>
                                <span>Bajo (2-3)</span>
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #b45309; display: inline-block;"></span>
                                <span>Medio (4-7)</span>
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #c2410c; display: inline-block;"></span>
                                <span>Alto (8-10)</span>
                            </span>
                            <span style="display: inline-flex; align-items: center; gap: 4px;">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #b91c1c; display: inline-block;"></span>
                                <span>Muy Alto (11+)</span>
                            </span>
                        </div>
                        <span style="font-size: 11.5px; color: #94a3b8;">Haz clic en un marcador para ver el detalle</span>
                    </div>
                </div>

                <!-- Lista Lateral de Puestos y Evaluadores -->
                <div class="all-loc-list-sidebar">
                    <h3 style="font-size: 13px; font-weight: 800; color: #334155; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px;">
                        Puestos y Personal Evaluador
                    </h3>
                    <div class="all-loc-scrollable">
                        @forelse($measurements as $m)
                            <div class="all-loc-item-card" onclick="panToRebaLocation({{ $m['location']['lat'] ?? '-16.5000' }}, {{ $m['location']['lng'] ?? '-68.1500' }})">
                                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px;">
                                    <div style="flex: 1;">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 11px; font-weight: 800; background: #e0f2fe; color: #0284c7; padding: 1px 6px; border-radius: 4px;">
                                                #{{ $m['num'] }}
                                            </span>
                                            <span style="font-weight: 800; font-size: 12.5px; color: #0f172a;">
                                                {{ $m['puesto_trabajo'] }}
                                            </span>
                                        </div>
                                        <div style="font-size: 11.5px; color: #64748b; margin-top: 2px;">
                                            {{ $m['area_sector'] }}
                                        </div>
                                    </div>
                                    <span class="reba-score-pill {{ $m['badge_class'] ?? 'risk-medio' }}" style="padding: 2px 8px; font-size: 11px;">
                                        Score {{ $m['score_final'] ?? 1 }}
                                    </span>
                                </div>
                                <div style="margin-top: 8px; padding-top: 6px; border-top: 1px dashed #e2e8f0; display: flex; align-items: center; justify-content: space-between; font-size: 11px;">
                                    <span style="color: #64748b;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -1px; margin-right: 2px;">
                                            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                        {{ $m['registered_by'] ?? 'Ing. Carlos Mendoza' }}
                                    </span>
                                    <span style="color: #0284c7; font-weight: 700; cursor: pointer;">
                                        Centrar ➔
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div style="text-align: center; color: #94a3b8; font-size: 12px; padding: 24px;">
                                No hay puestos geolocalizados disponibles.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer-custom" style="justify-content: flex-end;">
            <button type="button" class="btn-secondary-subtle" onclick="closeAllLocationsModal()">Cerrar</button>
        </div>
    </div>
</div>
