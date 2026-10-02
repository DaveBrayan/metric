<!-- Modal: Visor Fotográfico Lightbox ROSA -->
<div id="photoViewerModal" class="modal-backdrop-custom" role="dialog" aria-modal="true" style="background: rgba(15, 23, 42, 0.94) !important; z-index: 10000;" onclick="closePhotoViewerModal()">
    <div style="position: relative; max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; align-items: center; justify-content: center;" onclick="event.stopPropagation()">
        <button type="button" onclick="closePhotoViewerModal()"
            style="position: absolute; top: -44px; right: 0; background: rgba(255, 255, 255, 0.15); border: none; color: #ffffff; cursor: pointer; font-size: 18px; width: 36px; height: 36px; border-radius: 50%; display: grid; place-items: center; transition: all 0.2s;">
            ✕
        </button>
        <img id="photoViewerImage" src="" alt="Evidencia Fotográfica"
            style="max-width: 100%; max-height: 80vh; border-radius: 12px; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6); object-fit: contain; border: 2px solid rgba(255,255,255,0.1);">
        <div id="photoViewerCaption" style="color: #f1f5f9; font-size: 14px; font-weight: 700; margin-top: 14px; text-align: center; font-family: 'Outfit', sans-serif;">
            Evidencia Fotográfica
        </div>
    </div>
</div>
