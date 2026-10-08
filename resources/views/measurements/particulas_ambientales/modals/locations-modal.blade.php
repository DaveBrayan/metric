    <!-- ==========================================================================
         MODAL 6: MAPA GENERAL DE TODAS LAS ESTACIONES AMBIENTALES
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="allLocationsModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="allLocationsModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1100px; width: 95%;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 9px; background: #f0f9ff; color: #0284c7; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21" />
                            <line x1="9" y1="3" x2="9" y2="18" />
                            <line x1="15" y1="6" x2="15" y2="21" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="allLocationsModalTitle" style="font-size: 17px; margin: 0;">Ubicaciones de Partículas Ambientales</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Red de estaciones de muestreo y receptores georreferenciados</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span
                        style="font-size: 12px; font-weight: 800; background: #f0f9ff; color: #0284c7; border: 1px solid #bae6fd; padding: 4px 10px; border-radius: 20px;">
                        {{ $totalMeasurements }} Estaciones Registradas
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeAllLocationsModal()"
                        aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <div class="all-loc-modal-grid">
                    <div>
                        <div id="allLocationsMapLeaflet" style="height: 420px; border-radius: 12px; border: 1.5px solid #cbd5e1;"></div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeAllLocationsModal()">Cerrar</button>
            </div>
        </div>
    </div>
