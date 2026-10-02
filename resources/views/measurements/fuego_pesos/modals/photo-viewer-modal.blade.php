    <!-- ==========================================================================
             MODAL 4: VISOR DE FOTOGRAFÍAS EN TAMAÑO COMPLETO
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoViewerModal" role="dialog" aria-modal="true"
        aria-labelledby="photoViewerTitle">
        <div class="modal-dialog-illumination" style="max-width: 800px; background: transparent; border: none; box-shadow: none;">
            <div style="position: relative; background: #0f172a; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.5);">
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(8px); border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <span id="photoViewerTitle" style="color: #ffffff; font-weight: 700; font-size: 13.5px;">Fotografía del Sector</span>
                    <button type="button" onclick="closePhotoViewer()" style="background: rgba(255,255,255,0.15); border: 0; color: #fff; width: 28px; height: 28px; border-radius: 50%; cursor: pointer; display: grid; place-items: center; font-size: 13px;">✕</button>
                </div>
                <div style="padding: 10px; display: grid; place-items: center; min-height: 380px; max-height: 75vh; overflow: hidden;">
                    <img id="photoViewerImg" src="" alt="Foto Ampliada" style="max-width: 100%; max-height: 70vh; object-fit: contain; border-radius: 8px;">
                </div>
            </div>
        </div>
    </div>
