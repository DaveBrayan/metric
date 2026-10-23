    <!-- ==========================================================================
         MODAL: REPORTE FOTOGRÁFICO DE PUESTOS DE ERGONOMÍA ROSA (MOSAICO CARTA)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoReportModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="photoReportModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1280px; width: 98%;">
            <!-- Header del Modal -->
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 38px; height: 38px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center; flex-shrink: 0;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                            <circle cx="12" cy="13" r="4" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="photoReportModalTitle"
                            style="font-size: 17px; margin: 0; font-weight: 800; color: #0f172a;">Reporte Fotográfico — Ergonomía ROSA</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Distribución en mosaico (2x3, 2x4, 3x3, 3x4) en formato de hoja Carta para exportación PDF</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="btn-toolbar-action" id="btnSaveReportSettings"
                        onclick="savePhotoReportSettingsToServer(true)" title="Guardar distribución y selección de fotos">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                            <polyline points="17 21 17 13 7 13 7 21" />
                            <polyline points="7 3 7 8 15 8" />
                        </svg>
                        <span>Guardar Selección</span>
                    </button>
                    <button type="button" class="btn-close-modal" onclick="closePhotoReportModal()"
                        aria-label="Cerrar">✕</button>
                </div>
            </div>

            <!-- Toolbar con Selectores de Distribución, Selección de Fotos y Pestañas -->
            <div class="photo-report-toolbar">
                <!-- Selector de Distribución Mosaico -->
                <div class="photo-toolbar-group">
                    <span class="photo-toolbar-label">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect width="7" height="7" x="3" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="3" rx="1" />
                            <rect width="7" height="7" x="14" y="14" rx="1" />
                            <rect width="7" height="7" x="3" y="14" rx="1" />
                        </svg>
                        Distribución Mosaico:
                    </span>
                    <div class="grid-dist-selector" id="gridDistSelector">
                        <button type="button" class="btn-grid-dist" data-grid="2x3" onclick="changeGridDistribution('2x3')">
                            <span>2x3</span>
                            <span class="dist-sub">(6 fotos)</span>
                        </button>
                        <button type="button" class="btn-grid-dist" data-grid="2x4" onclick="changeGridDistribution('2x4')">
                            <span>2x4</span>
                            <span class="dist-sub">(8 fotos)</span>
                        </button>
                        <button type="button" class="btn-grid-dist" data-grid="3x3" onclick="changeGridDistribution('3x3')">
                            <span>3x3</span>
                            <span class="dist-sub">(9 fotos)</span>
                        </button>
                        <button type="button" class="btn-grid-dist" data-grid="3x4" onclick="changeGridDistribution('3x4')">
                            <span>3x4</span>
                            <span class="dist-sub">(12 fotos)</span>
                        </button>
                    </div>
                </div>

                <!-- Controles de Selección Masiva -->
                <div class="photo-toolbar-group">
                    <span class="photo-toolbar-label">Selección:</span>
                    <button type="button" class="btn-toolbar-action" id="btnSelectAllPoints" onclick="selectAllPoints(true)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Todas</span>
                    </button>
                    <button type="button" class="btn-toolbar-action" id="btnDeselectAllPoints" onclick="selectAllPoints(false)">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                        <span>Ninguna</span>
                    </button>
                    <span class="photo-selection-pill" id="photoSelectionCountText">0 de 0 seleccionadas</span>
                </div>

                <!-- Pestañas de Vista y Acción de Descarga Directa PDF -->
                <div class="photo-toolbar-group" style="margin-left: auto;">
                    <div class="photo-view-tabs">
                        <button type="button" class="photo-tab-btn active" id="tabBtnInteractive" onclick="switchPhotoReportTab('interactive')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                            <span>Configurar Mosaico</span>
                        </button>
                        <button type="button" class="photo-tab-btn" id="tabBtnSheets" onclick="switchPhotoReportTab('sheets')">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            <span>Vista Previa Hojas Carta</span>
                        </button>
                    </div>
                    <span class="photo-selection-pill" id="photoReportPagesIndicator" style="background: #e0f2fe; color: #0284c7; border-color: #bae6fd;">
                        Hojas Carta: 0 (2x3)
                    </span>
                    <button type="button" class="btn-toolbar-action btn-toolbar-primary" id="btnDownloadPhotoReportPdf" onclick="downloadPhotoReportPDF()" title="Descargar Reporte en PDF (Hoja Carta)">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        <span>Descargar PDF (Carta)</span>
                    </button>
                </div>
            </div>

            <!-- Contenedor Principal Scrollable -->
            <div class="photo-report-container">
                <!-- Modo 1: Cuadrícula Interactiva de Configuración -->
                <div id="photoInteractiveView">
                    <div class="photo-interactive-grid" id="photoInteractiveGridContainer">
                        <!-- Renderizado dinámico desde ergonomia_rosa.js -->
                    </div>
                </div>

                <!-- Modo 2: Vista Previa de Hojas Tamaño Carta -->
                <div id="photoSheetsView" style="display: none;">
                    <div class="photo-sheets-container" id="photoSheetsContainer">
                        <!-- Renderizado dinámico de hojas carta desde ergonomia_rosa.js -->
                    </div>
                </div>
            </div>

            <!-- Footer del Modal -->
            <div class="modal-footer-custom" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #64748b;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                    <span>Las fotos marcadas se organizan en páginas verticales tamaño Carta (8.5 x 11 pulg.). Usa las flechas sobre cada foto para elegir la evidencia preferida.</span>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="button" class="btn-secondary-subtle" onclick="closePhotoReportModal()">Cerrar</button>
                    <button type="button" class="btn-primary-hero-action" onclick="downloadPhotoReportPDF()">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Descargar PDF (Carta)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor Exclusivo Oculto para Impresión / Descarga PDF -->
    <div id="photoReportPrintArea" style="display: none;"></div>
