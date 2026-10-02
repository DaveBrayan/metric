<!-- Modal: Mapa Individual de Puesto REBA -->
<div class="modal-backdrop-custom" id="mapModal" role="dialog" aria-modal="true" aria-labelledby="mapModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 840px; width: 92%; height: 75vh; display: flex; flex-direction: column;">
        <div class="modal-header-custom" style="padding: 14px 20px;">
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="width: 34px; height: 34px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                        <circle cx="12" cy="10" r="3" />
                    </svg>
                </div>
                <div>
                    <h3 id="mapModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">
                        Geolocalización del Puesto de Trabajo
                    </h3>
                    <span id="mapModalSubtitle" style="font-size: 11.5px; color: #64748b;">Coordenadas UTM y ubicación espacial</span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeMapModal()">✕</button>
        </div>
        <div class="modal-body-custom" style="flex: 1; padding: 0; position: relative;">
            <div id="singlePointMapContainer" style="width: 100%; height: 100%; min-height: 380px;"></div>
        </div>
        <div class="modal-footer-custom" style="padding: 10px 20px; justify-content: flex-end;">
            <button type="button" class="btn-secondary-subtle" onclick="closeMapModal()">Cerrar</button>
        </div>
    </div>
</div>
