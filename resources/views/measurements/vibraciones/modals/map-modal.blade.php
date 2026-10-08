<!-- ==========================================================================
     MODAL: MAPA DE UBICACIÓN INDIVIDUAL
     ========================================================================== -->
<div class="modal-backdrop-custom" id="singleLocationModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="singleLocModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 780px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                    <circle cx="12" cy="10" r="3" />
                </svg>
                <h2 id="singleLocModalTitle">Ubicación Geográfica del Punto</h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeSingleLocationModal()">✕</button>
        </div>
        <div class="modal-body-custom" style="padding: 0; background: #ffffff;">
            <div id="singleLocationMap" style="width: 100%; height: 380px;"></div>
            <div style="padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #475569;">
                <div>
                    Punto: <strong id="singleLocCode" style="color: #0284c7;">—</strong> &bull; Área: <span id="singleLocArea">—</span>
                </div>
                <div>
                    UTM: <span id="singleLocUtm" style="font-family: monospace; font-weight: 700;">—</span>
                </div>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closeSingleLocationModal()">Cerrar</button>
        </div>
    </div>
</div>
