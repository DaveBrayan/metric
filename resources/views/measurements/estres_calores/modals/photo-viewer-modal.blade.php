    <!-- ==========================================================================
             MODAL: VISOR DE FOTOGRAFÍA AMPLIFICADA (ESTRÉS POR CALOR)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoViewerModal" onclick="closePhotoViewer()" role="dialog" aria-modal="true" style="z-index: 999999 !important;">
        <div class="modal-dialog-illumination" style="max-width: 600px; background: transparent; box-shadow: none; z-index: 1000000 !important;"
            onclick="event.stopPropagation()">
            <div
                style="position: relative; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.4);">
                <div
                    style="padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0;">
                    <h3 id="photoViewerTitle" style="font-size: 14px; font-weight: 800; color: var(--ink); margin: 0;">
                        Fotografía del Punto de Calor</h3>
                    <button type="button" class="btn-close-modal" onclick="closePhotoViewer()">✕</button>
                </div>
                <div style="padding: 12px; display: grid; place-items: center; background: #0f172a;">
                    <img id="photoViewerImg" src="" alt="Fotografía"
                        style="max-width: 100%; max-height: 65vh; object-fit: contain; border-radius: 8px;">
                </div>
            </div>
        </div>
    </div>
