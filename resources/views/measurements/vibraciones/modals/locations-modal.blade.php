<!-- ==========================================================================
     MODAL: MAPA DE TODAS LAS UBICACIONES
     ========================================================================== -->
<div class="modal-backdrop-custom" id="allLocationsModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="allLocModalTitle">
    <div class="modal-dialog-illumination modal-dialog-lg">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                    <circle cx="12" cy="10" r="3" />
                </svg>
                <h2 id="allLocModalTitle">Mapa General de Puntos de Vibración</h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeAllLocationsModal()">✕</button>
        </div>
        <div class="modal-body-custom" style="padding: 0; background: #ffffff;">
            <div id="allLocationsMap" style="width: 100%; height: 480px;"></div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closeAllLocationsModal()">Cerrar</button>
        </div>
    </div>
</div>
