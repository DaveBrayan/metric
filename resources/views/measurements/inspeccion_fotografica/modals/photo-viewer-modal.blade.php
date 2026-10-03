<!-- Modal Visor de Fotografías de Alta Calidad -->
<div class="modal-backdrop-custom" id="photoViewerModal" role="dialog" aria-modal="true" aria-labelledby="photoViewerTitle" style="background: rgba(15, 23, 42, 0.88) !important;">
    <div class="modal-dialog-illumination" style="max-width: 900px; background: transparent; border: none; box-shadow: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; color: #ffffff;">
            <div>
                <h3 id="photoViewerTitle" style="margin: 0; font-size: 16px; font-weight: 700; color: #ffffff;">Fotografía de Inspección</h3>
                <span id="photoViewerCounter" style="font-size: 12px; color: #94a3b8;">Foto 1 de 1</span>
            </div>
            <button type="button" class="btn-close-modal" onclick="closePhotoViewerModal()" style="background: rgba(255,255,255,0.15); color: #ffffff;" aria-label="Cerrar">✕</button>
        </div>

        <div style="position: relative; background: #000000; border-radius: 16px; overflow: hidden; display: flex; align-items: center; justify-content: center; min-height: 480px; max-height: 68vh;">
            <img id="photoViewerMainImage" src="" alt="Foto Inspección" style="max-width: 100%; max-height: 68vh; object-fit: contain;">

            <!-- Flecha Izquierda -->
            <button type="button" id="photoViewerPrevBtn" onclick="navigatePhotoViewer(-1)"
                style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border-radius: 50%; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.2); color: #ffffff; display: grid; place-items: center; cursor: pointer; transition: all 0.2s ease;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
            </button>

            <!-- Flecha Derecha -->
            <button type="button" id="photoViewerNextBtn" onclick="navigatePhotoViewer(1)"
                style="position: absolute; right: 16px; top: 50%; transform: translateY(-50%); width: 44px; height: 44px; border-radius: 50%; background: rgba(15, 23, 42, 0.7); border: 1px solid rgba(255,255,255,0.2); color: #ffffff; display: grid; place-items: center; cursor: pointer; transition: all 0.2s ease;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
        </div>

        <!-- Thumbnails Strip -->
        <div id="photoViewerThumbnailsStrip" style="display: flex; gap: 8px; justify-content: center; margin-top: 12px; overflow-x: auto; padding: 4px;">
            <!-- Se puebla dinámicamente con JS -->
        </div>
    </div>
</div>
