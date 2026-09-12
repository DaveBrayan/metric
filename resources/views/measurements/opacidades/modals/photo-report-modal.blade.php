    <!-- ==========================================================================
             MODAL: REPORTE FOTOGRÁFICO DE PUNTOS DE MONITOREO DE OPACIDAD (MOSAICO CARTA)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoReportModal" role="dialog" aria-modal="true"
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
                            style="font-size: 17px; margin: 0; font-weight: 800; color: #0f172a;">Reporte Fotográfico —
                            Monitoreo de Opacidad</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Distribución en mosaico (2x4, 2x3,
                            3x3, 3x4) en formato de hoja Carta para exportación PDF</span>
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
                    <button type="button" class="btn-toolbar-action btn-toolbar-primary btn-print-action"
                        onclick="printPhotoReport()" title="Imprimir o guardar en PDF (Tamaño Carta)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9" />
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                            <rect width="12" height="8" x="6" y="14" />
                        </svg>
                        <span>Imprimir / Guardar PDF</span>
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

                <!-- Herramientas de Selección de Fotos para el PDF -->
                <div class="photo-toolbar-group">
                    <button type="button" class="btn-toolbar-action" onclick="selectAllPoints(true)"
                        title="Seleccionar todas las fotos para el PDF">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span>Todas</span>
                    </button>
                    <button type="button" class="btn-toolbar-action" onclick="selectAllPoints(false)"
                        title="Deseleccionar todas las fotos">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                        <span>Ninguna</span>
                    </button>
                    <div class="photo-selection-pill" id="photoSelectionCountPill">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                            <circle cx="12" cy="13" r="4" />
                        </svg>
                        <span id="photoSelectionCountText">0 de 0 seleccionadas</span>
                    </div>
                </div>

                <!-- Alternador de Vista: Configurar vs Hojas Carta -->
                <div class="photo-toolbar-group">
                    <div class="photo-view-tabs">
                        <button type="button" class="photo-tab-btn active" id="tabBtnInteractive"
                            onclick="switchPhotoReportTab('interactive')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <rect width="18" height="18" x="3" y="3" rx="2" />
                                <path d="M3 9h18" />
                                <path d="M9 21V9" />
                            </svg>
                            <span>Configurar Mosaico</span>
                        </button>
                        <button type="button" class="photo-tab-btn" id="tabBtnSheets"
                            onclick="switchPhotoReportTab('sheets')">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                            </svg>
                            <span>Vista Previa Hojas Carta</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Contenedor Principal de Contenido del Reporte -->
            <div class="photo-report-container">
                <!-- VISTA 1: Selector Interactivo con Flechas y Checkboxes -->
                <div id="photoInteractiveView">
                    <div class="photo-interactive-grid" id="photoInteractiveGridContainer">
                        <!-- Generado dinámicamente por JavaScript -->
                    </div>
                </div>

                <!-- VISTA 2: Vista Previa de Hojas Tamaño Carta Paginadas -->
                <div id="photoSheetsView" style="display: none;">
                    <div class="photo-sheets-container" id="photoSheetsContainer">
                        <!-- Generado dinámicamente por JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Footer del Modal -->
            <div class="modal-footer-custom" style="justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 10px; font-size: 12px; color: #64748b;">
                    <span>METRIC v2 Pachabol — Módulo de Opacidad Vehicular</span>
                    <span>•</span>
                    <span id="photoReportPagesIndicator" style="font-weight: 700; color: #0284c7;">Hojas calculadas:
                        0</span>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <button type="button" class="btn-secondary-subtle" onclick="closePhotoReportModal()">Cerrar</button>
                    <button type="button" class="btn-primary-hero-action" onclick="printPhotoReport()"
                        style="padding: 6px 16px; font-size: 12.5px; background: #0284c7;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.2">
                            <polyline points="6 9 6 2 18 2 18 9" />
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                            <rect width="12" height="8" x="6" y="14" />
                        </svg>
                        <span>Imprimir / PDF</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Área Exclusiva de Impresión PDF (Hojas Tamaño Carta para @media print) -->
    <div id="photoReportPrintArea" style="display: none;">
        <!-- Se inyectan dinámicamente aquí las hojas .photo-report-sheet para imprimir -->
    </div>
