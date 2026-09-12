    <!-- ==========================================================================
             MODAL: VISOR DE MAPA Y FOTOGRAFÍA DEL PUNTO (OPACIDAD)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="mapLocationModal" role="dialog" aria-modal="true"
        aria-labelledby="mapModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 860px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div
                        style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                            <circle cx="12" cy="10" r="3" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="mapModalTitle" style="font-size: 17px; margin: 0;">Ubicación de Medición en Mapa</h2>
                        <span id="mapModalSubtitle" style="font-size: 12px; color: #64748b; font-weight: 500;">Punto de Opacidad Vehicular</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeMapModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <!-- Panel de Información y Fotografía -->
                <div class="map-card-info-grid">
                    <div>
                        <div style="font-size: 14px; font-weight: 800; color: var(--ink); margin-bottom: 3px;"
                            id="mapCardPointName">Vehículo / Placa</div>
                        <div style="font-size: 12.5px; color: #64748b; margin-bottom: 6px;" id="mapCardLocationDesc">
                            Ubicación física / Área</div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span id="mapCardLuxBadge" class="lux-measured-badge compliant"
                                style="font-size: 11.5px; padding: 3px 8px;">0.00 %</span>
                            <span id="mapCardCoords"
                                style="font-family: monospace; font-size: 11.5px; background: #ffffff; border: 1px solid #cbd5e1; padding: 3px 7px; border-radius: 6px; color: #334155;">-16.5034,
                                -68.1324</span>
                        </div>
                    </div>

                    <!-- Miniatura de Foto en el Mapa si existe -->
                    <div id="mapModalPhotoThumbWrap"
                        style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                        <div id="mapModalPhotoThumb" class="table-thumb-preview"
                            style="width: 58px; height: 58px; border-radius: 10px;" onclick="expandCurrentMapPhoto()"
                            title="Clic para ampliar fotografía">
                            <img id="mapModalPhotoImg" src="" alt="Fotografía">
                        </div>
                        <span style="font-size: 10.5px; font-weight: 700; color: #0284c7; cursor: pointer;"
                            onclick="expandCurrentMapPhoto()">Ver Foto</span>
                    </div>
                </div>

                <!-- Contenedor del Mapa Leaflet -->
                <div id="mapContainerLeaflet"></div>
            </div>

            <div class="modal-footer-custom" style="justify-content: space-between;">
                <a id="openInGoogleMapsBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn-secondary-subtle"
                    style="font-size: 12px; padding: 7px 14px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
                        <polyline points="15 3 21 3 21 9" />
                        <line x1="10" y1="14" x2="21" y2="3" />
                    </svg>
                    <span>Abrir en Google Maps</span>
                </a>
                <button type="button" class="btn-primary-hero-action" onclick="closeMapModal()"
                    style="padding: 8px 18px; font-size: 13px; background: #0284c7;">Cerrar</button>
            </div>
        </div>
    </div>
