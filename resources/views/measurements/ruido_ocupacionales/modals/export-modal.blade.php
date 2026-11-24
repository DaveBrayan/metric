<!-- ==========================================================================
     MODAL: OPCIONES DE EXPORTACIÓN (PLANILLA EXCEL)
     RUIDO OCUPACIONAL — METRIC V2 PACHABOL
     ========================================================================== -->
<div class="modal-backdrop-custom" id="exportOptionsModal" role="dialog" aria-modal="true"
    aria-labelledby="exportModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 520px;">
        <!-- Header del Modal -->
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div
                    style="width: 36px; height: 36px; border-radius: 10px; background: #ecfdf5; color: #059669; display: grid; place-items: center; border: 1px solid #a7f3d0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                </div>
                <div>
                    <h2 id="exportModalTitle" style="font-size: 17px; margin: 0; font-weight: 700; color: #0f172a;">Exportar Monitoreo de Ruido Ocupacional</h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">Descarga la planilla técnica oficial en formato Excel (.xlsx)</span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeExportModal()" aria-label="Cerrar">✕</button>
        </div>

        <!-- Cuerpo del Modal (Solo Planilla Excel) -->
        <div class="modal-body-custom" style="padding: 22px 24px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 16px;">
                <!-- Opción: Planilla Excel (Tema Verde Corporativo) -->
                <div class="export-option-card excel-theme" onclick="downloadRuidoOcupacionalExcel()" style="cursor: pointer; margin: 0; width: 100%;">
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
                                <p>Encabezado corporativo, matriz completa de puntos de sonometría, niveles Leq (dBA), TPE, TMPE y dosis según NTS-002/17.</p>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="export-card-btn excel-btn" onclick="event.stopPropagation(); downloadRuidoOcupacionalExcel();" style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span>Descargar Planilla (.xlsx)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer del Modal -->
        <div class="modal-footer-custom" style="justify-content: flex-end;">
            <button type="button" class="btn-secondary-subtle" onclick="closeExportModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
    // Garantizar disponibilidad global de modales de exportación
    if (typeof window.openExportModal !== 'function') {
        window.openExportModal = function () {
            const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
            if (modal) {
                modal.classList.add('open', 'active');
                modal.style.setProperty('display', 'flex', 'important');
            }
        };
    }

    if (typeof window.closeExportModal !== 'function') {
        window.closeExportModal = function () {
            const modal = document.getElementById('exportOptionsModal') || document.getElementById('exportModal');
            if (modal) {
                modal.classList.remove('open', 'active');
                modal.style.setProperty('display', 'none', 'important');
            }
        };
    }

    // Puente directo para invocar la función de exportación
    if (typeof window.downloadRuidoOcupacionalExcel !== 'function') {
        window.downloadRuidoOcupacionalExcel = function () {
            if (typeof window.downloadExcelPlanilla === 'function') {
                return window.downloadExcelPlanilla();
            }
            console.warn('[Ruido Ocupacional] Esperando carga de la biblioteca Excel...');
        };
    }
</script>
