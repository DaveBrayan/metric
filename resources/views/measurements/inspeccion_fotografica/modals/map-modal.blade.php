<!-- Modal de Mapa Interactivo Leaflet -->
<div class="modal-backdrop-custom" id="mapModal" role="dialog" aria-modal="true" aria-labelledby="mapModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 860px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
                        <line x1="8" y1="2" x2="8" y2="18"/>
                        <line x1="16" y1="6" x2="16" y2="22"/>
                    </svg>
                </div>
                <div>
                    <h2 id="mapModalTitle" style="font-size: 17px; margin: 0;">Georreferenciación de Inspección</h2>
                    <span id="mapModalSubtitle" style="font-size: 12px; color: #64748b;">Visualización geoespacial de puntos evaluados</span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeMapModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 0; position: relative;">
            <div id="photographicLeafletMap" style="width: 100%; height: 460px; background: #e2e8f0;"></div>
            <div id="mapCoordsFooter" style="padding: 10px 18px; background: #ffffff; border-top: 1px solid #e2e8f0; font-size: 12px; color: #475569; display: flex; justify-content: space-between; align-items: center;">
                <span id="mapPointInfoText">Punto seleccionado</span>
                <span id="mapCoordsText" style="font-family: monospace; font-weight: 700; color: #0284c7;">--</span>
            </div>
        </div>

        <div class="modal-footer-custom" style="justify-content: space-between;">
            <div style="font-size: 12px; color: #64748b;">
                Usa la rueda del ratón o pellizca para hacer zoom.
            </div>
            <button type="button" class="btn-secondary-subtle" onclick="closeMapModal()">Cerrar Mapa</button>
        </div>
    </div>
</div>
