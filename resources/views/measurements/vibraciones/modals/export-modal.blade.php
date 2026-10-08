<!-- ==========================================================================
     MODAL: EXPORTACIÓN OFICIAL (EXCEL & CATÁLOGO FOTOGRÁFICO)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="exportModal" role="dialog" aria-modal="true" style="display: none;"
    aria-labelledby="exportModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 580px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.3">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <h2 id="exportModalTitle">Exportar Datos de Vibración</h2>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeExportModal()">✕</button>
        </div>
        <div class="modal-body-custom" style="padding: 20px; background: #ffffff;">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <!-- Opción 1: Planilla Excel -->
                <div style="border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; background: #f8fafc;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; background: #ecfdf5; border-radius: 10px; display: grid; place-items: center; color: #059669;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h8"/><path d="M8 17h8"/><path d="M10 9h4"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 13.5px; color: #0f172a;">Planilla de Campo Oficial (.xlsx)</div>
                            <div style="font-size: 11.5px; color: #64748b;">Tabla completa con fórmulas ISO 2631-1 e ISO 5349-1</div>
                        </div>
                    </div>
                    <button type="button" class="btn-primary-hero-action" onclick="exportVibracionExcel()" style="padding: 8px 16px; font-size: 12px; background: #059669;">
                        Descargar Excel
                    </button>
                </div>

                <!-- Opción 2: Informe Word -->
                <div style="border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; background: #f8fafc;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; background: #f0f9ff; border-radius: 10px; display: grid; place-items: center; color: #0284c7;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 13.5px; color: #0f172a;">Informe Técnico Oficial (.doc)</div>
                            <div style="font-size: 11.5px; color: #64748b;">Formato corporativo con resultados y matriz</div>
                        </div>
                    </div>
                    <a href="{{ route('modules.vibracion.report', $module->id) }}" class="btn-primary-hero-action" style="padding: 8px 16px; font-size: 12px; text-decoration: none;">
                        Abrir Informe
                    </a>
                </div>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closeExportModal()">Cerrar</button>
        </div>
    </div>
</div>
