<!-- ==========================================================================
     MODAL: VISOR DE FOTOGRAFÍA AMPLIFICADA CON NAVEGACIÓN (METRIC v2)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="photoViewerModal" onclick="closePhotoViewer()" role="dialog" aria-modal="true" style="z-index: 999999 !important;">
    <div class="modal-dialog-illumination" style="max-width: 660px; width: 92%; background: transparent; box-shadow: none; z-index: 1000000 !important;"
        onclick="event.stopPropagation()">
        <div style="position: relative; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.45); border: 1px solid #cbd5e1;">
            
            <!-- Encabezado con Título y Contador -->
            <div style="padding: 12px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                <div>
                    <h3 id="photoViewerTitle" style="font-size: 14px; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2;">
                        Fotografía de Medición
                    </h3>
                    <span id="photoViewerCounter" style="display: none; font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 4px; margin-top: 4px; border: 1px solid #bae6fd;">
                        Foto 1 de 1
                    </span>
                </div>
                <button type="button" class="btn-close-modal" onclick="closePhotoViewer()" title="Cerrar (Esc)" aria-label="Cerrar">✕</button>
            </div>

            <!-- Visor Principal con Flechas de Navegación -->
            <div style="position: relative; padding: 14px; display: grid; place-items: center; background: #0f172a; min-height: 300px;">
                <img id="photoViewerImg" src="" alt="Fotografía"
                    style="max-width: 100%; max-height: 65vh; object-fit: contain; border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.5);">
                <!-- Alias de ID para compatibilidad -->
                <img id="photoViewerMainImage" src="" alt="Fotografía" style="display: none;">
                <img id="photoViewerImage" src="" alt="Fotografía" style="display: none;">

                <!-- Flecha Anterior -->
                <button type="button" id="photoViewerPrevBtn" onclick="navigatePhotoViewer(-1)"
                    style="display: none; position: absolute; left: 14px; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border-radius: 50%; background: rgba(15, 23, 42, 0.78); border: 1.5px solid rgba(255,255,255,0.3); color: #ffffff; place-items: center; cursor: pointer; transition: all 0.2s ease; backdrop-filter: blur(4px); box-shadow: 0 4px 12px rgba(0,0,0,0.35);"
                    title="Foto anterior (←)" aria-label="Foto anterior">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                </button>

                <!-- Flecha Siguiente -->
                <button type="button" id="photoViewerNextBtn" onclick="navigatePhotoViewer(1)"
                    style="display: none; position: absolute; right: 14px; top: 50%; transform: translateY(-50%); width: 42px; height: 42px; border-radius: 50%; background: rgba(15, 23, 42, 0.78); border: 1.5px solid rgba(255,255,255,0.3); color: #ffffff; place-items: center; cursor: pointer; transition: all 0.2s ease; backdrop-filter: blur(4px); box-shadow: 0 4px 12px rgba(0,0,0,0.35);"
                    title="Foto siguiente (→)" aria-label="Foto siguiente">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </div>

            <!-- Tira de Miniaturas Inferior -->
            <div id="photoViewerThumbnailsStrip" style="display: none; gap: 8px; justify-content: center; padding: 10px 14px; background: #1e293b; overflow-x: auto; border-top: 1px solid rgba(255,255,255,0.1);">
            </div>

        </div>
    </div>
</div>
