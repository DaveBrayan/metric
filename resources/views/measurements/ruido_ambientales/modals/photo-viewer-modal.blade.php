<div class="modal-backdrop-custom" id="photoViewerModal" role="dialog" aria-modal="true" aria-labelledby="photoViewerTitle">
    <div class="modal-dialog-ruido" style="max-width: 680px;">
        <div class="modal-header-custom">
            <div>
                <h2 id="photoViewerTitle" style="font-family: 'Outfit', sans-serif; font-size: 17px; font-weight: 800; color: var(--ink); margin: 0;">
                    Evidencia Fotográfica
                </h2>
                <p id="photoViewerCaption" style="font-size: 12px; color: #64748b; margin: 0;">
                    Registro de sonómetro en campo
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closePhotoViewerModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 16px; text-align: center; background: #0f172a;">
            <img id="photoViewerImage" src="" alt="Evidencia" style="max-width: 100%; max-height: 500px; border-radius: 12px; object-fit: contain;">
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-primary-hero-action" onclick="closePhotoViewerModal()" style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
        </div>
    </div>
</div>
