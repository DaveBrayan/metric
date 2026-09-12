<div class="modal-backdrop-custom" id="mapModal" role="dialog" aria-modal="true" aria-labelledby="mapModalTitle">
    <div class="modal-dialog-ruido" style="max-width: 750px;">
        <div class="modal-header-custom">
            <div>
                <h2 id="mapModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 17px; font-weight: 800; color: var(--ink); margin: 0 0 2px 0;">
                    Ubicación GPS del Punto de Medición
                </h2>
                <p id="mapModalUtmInfo" style="font-size: 12px; color: #64748b; font-family: monospace; margin: 0;">
                    Coordenadas UTM
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeMapModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 16px;">
            <div id="singlePointMapLeaflet" style="width: 100%; height: 380px; border-radius: 12px; border: 1px solid #cbd5e1;"></div>
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-primary-hero-action" onclick="closeMapModal()" style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
        </div>
    </div>
</div>
