    <!-- ==========================================================================
         MODAL 5: OPCIONES DE EXPORTACIÓN (PLANILLA EXCEL & REPORTE FOTOGRÁFICO)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="exportOptionsModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="exportModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 680px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 34px; height: 34px; border-radius: 9px; background: #ecfdf5; color: #059669; display: grid; place-items: center;">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="exportModalTitle" style="font-size: 17px; margin: 0;">Exportar Partículas Ambientales</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Selecciona el formato de exportación para calidad del aire</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeExportModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 22px 24px;">
                <div class="export-options-grid">
                    <!-- Opción 1: Planilla Excel -->
                    <div class="export-option-card excel-theme" onclick="exportParticulasAmbExcel()">
                        <div>
                            <div class="export-card-top">
                                <div class="export-card-icon excel-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="8" y1="13" x2="16" y2="13" />
                                        <line x1="8" y1="17" x2="16" y2="17" />
                                        <polyline points="10 9 9 9 8 9" />
                                    </svg>
                                </div>
                                <div class="export-card-body">
                                    <span class="export-card-badge badge-excel">Planilla Oficial .xlsx</span>
                                    <h3>Planilla Técnica Excel</h3>
                                    <p>Genera la planilla técnica completa con fracciones PM10, PM2.5 y PTS, datos climáticos y límites Ley 1333.</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="export-card-btn excel-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <span>Descargar Planilla (.xlsx)</span>
                        </button>
                    </div>

                    <!-- Opción 2: Reporte Fotográfico -->
                    <div class="export-option-card" onclick="openPhotoReportConfigModal()">
                        <div>
                            <div class="export-card-top">
                                <div class="export-card-icon photo-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                                        <circle cx="12" cy="13" r="4" />
                                    </svg>
                                </div>
                                <div class="export-card-body">
                                    <span class="export-card-badge badge-photo">Catálogo Visual</span>
                                    <h3>Reporte Fotográfico</h3>
                                    <p>Diseña y exporta la cuadrícula de fotografías ambientales con coordenadas UTM y fecha/hora.</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="export-card-btn photo-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                <circle cx="9" cy="9" r="2" />
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                            </svg>
                            <span>Ver Reporte Fotográfico</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-secondary-subtle" onclick="closeExportModal()">Cerrar</button>
            </div>
        </div>
    </div>
