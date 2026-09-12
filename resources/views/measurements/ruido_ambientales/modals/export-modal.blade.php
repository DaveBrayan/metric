<div class="modal-backdrop-custom" id="exportModal" role="dialog" aria-modal="true" aria-labelledby="exportModalTitle">
    <div class="modal-dialog-ruido" style="max-width: 650px;">
        <div class="modal-header-custom">
            <div>
                <h2 id="exportModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); margin: 0;">
                    Exportar Informe de Ruido Ambiental
                </h2>
                <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                    Generación de planillas oficiales en Excel y catálogo fotográfico
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeExportModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 22px;">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <!-- Opción 1: Planilla Excel -->
                <div style="border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; transition: all 0.2s ease;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 10px; background: #ecfdf5; color: #059669; display: grid; place-items: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <path d="M8 13h8"/><path d="M8 17h8"/><path d="M10 9h4"/>
                            </svg>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 14px; color: var(--ink);">Planilla Técnica Excel (.xlsx)</div>
                            <div style="font-size: 12px; color: #64748b;">Matriz de datos completa, Leq logarítmico, colindancias y coordenadas UTM.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-primary-hero-action" onclick="exportRuidoAmbientalExcel()" style="padding: 8px 16px; font-size: 12.5px; white-space: nowrap;">
                        Descargar (.xlsx)
                    </button>
                </div>

                <!-- Opción 2: Catálogo Fotográfico -->
                <div style="border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px; transition: all 0.2s ease;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 10px; background: #f0f9ff; color: #0284c7; display: grid; place-items: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                            </svg>
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 14px; color: var(--ink);">Catálogo Fotográfico de Campo</div>
                            <div style="font-size: 12px; color: #64748b;">Panel de imágenes, linderos y sonómetros registrados en alta resolución.</div>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary-subtle" onclick="openPhotoReportModal(); closeExportModal();" style="padding: 8px 16px; font-size: 12.5px; white-space: nowrap;">
                        Ver Catálogo
                    </button>
                </div>
            </div>
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closeExportModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
    function exportRuidoAmbientalExcel() {
        if (typeof ExcelJS === 'undefined') {
            alert('ExcelJS se está cargando. Por favor reintenta en unos segundos.');
            return;
        }

        const wb = new ExcelJS.Workbook();
        const ws = wb.addWorksheet('Ruido Ambiental');

        ws.columns = [
            { header: 'N°', key: 'num', width: 6 },
            { header: 'Fecha', key: 'date', width: 14 },
            { header: 'Hora', key: 'time', width: 10 },
            { header: 'Normativa', key: 'normativa', width: 22 },
            { header: 'Tipo Zona', key: 'zona', width: 20 },
            { header: 'Horario', key: 'horario', width: 16 },
            { header: 'LMP (dBA)', key: 'lmp', width: 12 },
            { header: 'Leq Resultante (dBA)', key: 'leq', width: 20 },
            { header: 'Evaluación', key: 'eval', width: 16 },
            { header: 'Colindancia Norte', key: 'norte', width: 22 },
            { header: 'UTM Norte (X/Y)', key: 'norte_xy', width: 20 },
            { header: 'Colindancia Sur', key: 'sur', width: 22 },
            { header: 'UTM Sur (X/Y)', key: 'sur_xy', width: 20 },
            { header: 'Colindancia Este', key: 'este', width: 22 },
            { header: 'UTM Este (X/Y)', key: 'este_xy', width: 20 },
            { header: 'Colindancia Oeste', key: 'oeste', width: 22 },
            { header: 'UTM Oeste (X/Y)', key: 'oeste_xy', width: 20 },
            { header: 'Personal Registrador', key: 'staff', width: 24 },
            { header: 'Observaciones', key: 'obs', width: 30 }
        ];

        ws.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
        ws.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0284C7' } };

        const measurements = @json($measurements);
        measurements.forEach(m => {
            ws.addRow({
                num: m.num,
                date: m.date_formatted,
                time: m.time_raw,
                normativa: m.normativa,
                zona: m.tipo_zona,
                horario: m.horario,
                lmp: m.limite_normativa,
                leq: m.leq_d,
                eval: m.is_compliant ? 'CUMPLE' : 'NO CUMPLE',
                norte: m.norte_colindancia,
                norte_xy: m.norte_x + ' / ' + m.norte_y,
                sur: m.sur_colindancia,
                sur_xy: m.sur_x + ' / ' + m.sur_y,
                este: m.este_colindancia,
                este_xy: m.este_x + ' / ' + m.este_y,
                oeste: m.oeste_colindancia,
                oeste_xy: m.oeste_x + ' / ' + m.oeste_y,
                staff: m.registered_by,
                obs: m.observations
            });
        });

        wb.xlsx.writeBuffer().then(buf => {
            const blob = new Blob([buf], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Reporte_Ruido_Ambiental_{{ $module->id }}_' + new Date().toISOString().slice(0, 10) + '.xlsx';
            a.click();
            window.URL.revokeObjectURL(url);
            closeExportModal();
        });
    }
</script>
