    <!-- ==========================================================================
         MODAL 3: MAPA INDIVIDUAL DE ESTACIÓN AMBIENTAL
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="mapLocationModal" role="dialog" aria-modal="true" style="display: none;">
        <div class="modal-dialog-illumination" style="max-width: 680px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #f0f9ff; color: #0284c7; display: grid; place-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" /><circle cx="12" cy="10" r="3" />
                        </svg>
                    </div>
                    <div>
                        <h2 style="font-size: 16px; margin: 0;" id="mapCardPointName">Estación de Monitoreo</h2>
                        <span id="mapModalSubtitle" style="font-size: 12px; color: #64748b; font-weight: 500;">Estación Ambiental de Calidad del Aire</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeMapModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 16px;">
                <div id="mapContainerLeaflet" style="height: 320px; border-radius: 12px; border: 1.5px solid #cbd5e1;"></div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
                    <div>
                        <div id="mapCardLocationDesc" style="font-weight: 700; font-size: 13px; color: var(--ink);">—</div>
                        <div id="mapCardCoords" style="font-size: 11.5px; color: #0284c7; font-family: monospace;">—</div>
                    </div>
                    <a id="openInGoogleMapsBtn" href="#" target="_blank" class="btn-secondary-subtle" style="font-size: 12px; padding: 6px 14px;">
                        <span>Ver en Google Maps</span>
                    </a>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-secondary-subtle" onclick="closeMapModal()">Cerrar</button>
            </div>
        </div>
    </div>
