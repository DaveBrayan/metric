<!-- ==========================================================================
     MODAL: VISOR DE FOTOGRAFÍA AMPLIFICADA (Estilo Iluminación)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="photoViewerModal" onclick="closePhotoViewerModal()" role="dialog" aria-modal="true" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.85); z-index:999999 !important; backdrop-filter:blur(3px);">
    <div class="modal-dialog-illumination" style="max-width:620px; width:92%; background:transparent; box-shadow:none; z-index:1000000 !important;"
        onclick="event.stopPropagation()">
        <div style="position:relative; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,0.45); border:1px solid #cbd5e1;">
            
            <div style="padding:14px 18px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #e2e8f0; background:#f8fafc;">
                <div>
                    <h3 id="photoViewerTitle" style="font-size:14px; font-weight:800; color:#0f172a; margin:0; line-height:1.2;">
                        Fotografía de Medición
                    </h3>
                    <p id="photoViewerCaption" style="font-size:12px; color:#64748b; margin:2px 0 0 0;">
                        Registro en campo
                    </p>
                </div>
                <button type="button" class="btn-close-modal" onclick="closePhotoViewerModal()" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; font-size:16px; cursor:pointer; color:#64748b; transition:all 0.15s;" onmouseenter="this.style.background='#fee2e2';this.style.color='#dc2626'" onmouseleave="this.style.background='#f1f5f9';this.style.color='#64748b'">✕</button>
            </div>

            <div style="padding:14px; display:grid; place-items:center; background:#0f172a; min-height:260px;">
                <img id="photoViewerImg" src="" alt="Fotografía"
                    style="max-width:100%; max-height:65vh; object-fit:contain; border-radius:8px; box-shadow:0 4px 14px rgba(0,0,0,0.4);">
                <img id="photoViewerImage" src="" alt="Fotografía" style="display:none;">
            </div>

            <div style="padding:10px 18px; display:flex; justify-content:flex-end; border-top:1px solid #e2e8f0; background:#f8fafc;">
                <button type="button" class="btn-primary-hero-action" onclick="closePhotoViewerModal()" style="padding:7px 18px; font-size:13px; font-weight:700; border-radius:6px; background:#0284c7; color:#ffffff; border:none; cursor:pointer;">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>
