<!-- ==========================================================================
     MODAL: VISOR DE FOTOGRAFÍAS
     ========================================================================== -->
<div class="modal-backdrop-custom" id="photoViewerModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="photoViewerTitle">
    <div class="modal-dialog-illumination" style="max-width: 720px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3">
                    <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                    <circle cx="12" cy="13" r="3"/>
                </svg>
                <h2 id="photoViewerTitle">Evidencia Fotográfica</h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closePhotoViewerModal()">✕</button>
        </div>
        <div class="modal-body-custom" style="padding: 16px; background: #0f172a; text-align: center;">
            <div id="photoViewerContainer" style="display: flex; flex-direction: column; align-items: center; gap: 12px;">
                <img id="mainViewerImage" src="" alt="Foto de Campo" style="max-width: 100%; max-height: 480px; border-radius: 8px; object-fit: contain;">
                <div id="photoThumbnailsRow" style="display: flex; gap: 8px; overflow-x: auto; max-width: 100%; padding: 6px 0;"></div>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closePhotoViewerModal()">Cerrar</button>
        </div>
    </div>
</div>
