<!-- ==========================================================================
     MODAL: OPCIONES DE EXPORTACIÓN (PLANILLA EXCEL & REPORTE FOTOGRÁFICO)
     RUIDO OCUPACIONAL — METRIC V2 PACHABOL
     ========================================================================== -->
<div class="modal-backdrop-custom" id="exportOptionsModal" role="dialog" aria-modal="true"
    aria-labelledby="exportModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 680px;">
        <!-- Header del Modal -->
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
                    <h2 id="exportModalTitle" style="font-size: 17px; margin: 0;">Exportar Monitoreo de Ruido Ocupacional</h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">Selecciona el formato de exportación para este estudio</span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeExportModal()" aria-label="Cerrar">✕</button>
        </div>

        <!-- Cuerpo del Modal en Dos Columnas (Verde y Celeste) -->
        <div class="modal-body-custom" style="padding: 22px 24px;">
            <div class="export-options-grid">
                <!-- Opción 1: Planilla Excel (Tema Verde) -->
                <div class="export-option-card excel-theme" onclick="downloadRuidoOcupacionalExcel()">
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

                <!-- Opción 2: Reporte Fotográfico (Tema Celeste) -->
                <div class="export-option-card" onclick="openPhotoReportModal()">
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
                                <p>Diseña y exporta la cuadrícula de fotografías técnicas con sonometría, puestos de trabajo, Leq y hojas tamaño Carta en PDF.</p>
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

        <!-- Footer del Modal -->
        <div class="modal-footer-custom" style="justify-content: flex-end;">
            <button type="button" class="btn-secondary-subtle" onclick="closeExportModal()">Cerrar</button>
        </div>
    </div>
</div>

<script>
    async function downloadRuidoOcupacionalExcel() {
        if (typeof ExcelJS === 'undefined') {
            alert('Librería ExcelJS no cargada aún. Por favor espera un momento.');
            return;
        }

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'Metric v2 — Pachabol';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Ruido Ocupacional NTS-002', {
            pageSetup: { orientation: 'landscape', paperSize: 9 }
        });

        const cyanHeaderFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF0284C7' } };
        const grayHeaderFill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFF1F5F9' } };

        sheet.mergeCells('A1:O1');
        const titleCell = sheet.getCell('A1');
        titleCell.value = 'PLANILLA TÉCNICA OFICIAL — MONITOREO DE RUIDO OCUPACIONAL (NORMATIVA NTS-002/17 • R.M. 343/18)';
        titleCell.font = { name: 'Calibri', size: 13, bold: true, color: { argb: 'FFFFFFFF' } };
        titleCell.alignment = { horizontal: 'center', vertical: 'middle' };
        titleCell.fill = cyanHeaderFill;
        sheet.getRow(1).height = 30;

        const tech = window.TECHNICAL_HEADER_DATA || {};
        sheet.mergeCells('A2:D2');
        sheet.getCell('A2').value = `INSTALACIÓN: ${tech.installationName || 'No especificada'}`;
        sheet.getCell('A2').font = { bold: true, size: 10 };

        sheet.mergeCells('E2:H2');
        sheet.getCell('E2').value = `PERÍODO: ${tech.startDateFormatted || '-'} al ${tech.endDateFormatted || '-'}`;
        sheet.getCell('E2').font = { bold: true, size: 10 };

        sheet.mergeCells('I2:L2');
        sheet.getCell('I2').value = `EQUIPO: ${tech.equipmentName || 'Sonómetro Clase 1'} (${tech.equipmentModel || 'SV 971A'})`;
        sheet.getCell('I2').font = { bold: true, size: 10 };

        sheet.mergeCells('M2:O2');
        sheet.getCell('M2').value = `TÉCNICO: ${tech.registeredByHeader || window.REGISTERED_BY_HEADER || 'Técnico de Campo'}`;
        sheet.getCell('M2').font = { bold: true, size: 10 };
        sheet.getRow(2).height = 22;

        const headers = [
            'N°', 'FECHA', 'HORA', 'ÁREA / SECTOR', 'PUESTO / PUNTO',
            'TIPO RUIDO', 'TPE (h)', 'POND.', 'RESP.', 'LEQ (dBA)',
            'TMPE (h)', 'DOSIS (%)', 'EVALUACIÓN NTS-002/17', 'REGISTRADO POR', 'OBSERVACIONES'
        ];

        const headerRow = sheet.addRow(headers);
        headerRow.height = 24;
        headerRow.eachCell((cell) => {
            cell.fill = grayHeaderFill;
            cell.font = { bold: true, color: { argb: 'FF0F172A' }, size: 10 };
            cell.alignment = { horizontal: 'center', vertical: 'middle' };
            cell.border = {
                top: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                bottom: { style: 'medium', color: { argb: 'FF0284C7' } },
                left: { style: 'thin', color: { argb: 'FFCBD5E1' } },
                right: { style: 'thin', color: { argb: 'FFCBD5E1' } }
            };
        });

        const measurements = window.ALL_MEASUREMENTS_DATA || @json($measurements ?? []);

        measurements.forEach((m, idx) => {
            const num = m.num || (idx + 1);
            const date = m.date || m.date_formatted || '—';
            const time = m.time || '—';
            const area = m.area || '—';
            const puesto = m.punto_medicion || '—';
            const tipoRuido = m.tipo_ruido || 'Continuo';
            const tpe = m.tiempo_expos_h !== null && m.tiempo_expos_h !== undefined ? Number(m.tiempo_expos_h) : 8.0;
            const pond = m.ponderacion || 'A';
            const resp = m.respuesta || 'Lenta';
            const leq = m.leq_t_db !== null && m.leq_t_db !== undefined ? Number(m.leq_t_db) : null;
            const tmpe = m.tmpe !== null && m.tmpe !== undefined ? Number(m.tmpe) : null;
            const dosis = m.dosis !== null && m.dosis !== undefined ? (Number(m.dosis) * 100) : null;
            const cumple = (leq !== null && leq <= 85.0);
            const evalText = leq !== null ? (cumple ? 'CUMPLE LMP' : 'SUPERA LMP') : '—';
            const staff = m.registered_by || '—';
            const obs = m.observations || '—';

            const row = sheet.addRow([
                num, date, time, area, puesto,
                tipoRuido, tpe.toFixed(2), pond, resp,
                leq !== null ? leq.toFixed(1) : '—',
                tmpe !== null ? tmpe.toFixed(2) : '—',
                dosis !== null ? dosis.toFixed(1) + '%' : '—',
                evalText, staff, obs
            ]);

            row.alignment = { vertical: 'middle' };
            row.getCell(1).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(2).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(3).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(6).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(7).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(8).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(9).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(10).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(11).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(12).alignment = { horizontal: 'center', vertical: 'middle' };
            row.getCell(13).alignment = { horizontal: 'center', vertical: 'middle' };

            // Colorear celda de evaluación
            const evalCell = row.getCell(13);
            if (leq !== null) {
                if (cumple) {
                    evalCell.font = { bold: true, color: { argb: 'FF065F46' } };
                    evalCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD1FAE5' } };
                } else {
                    evalCell.font = { bold: true, color: { argb: 'FF991B1B' } };
                    evalCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFEE2E2' } };
                }
            }

            row.eachCell((cell) => {
                cell.border = {
                    top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                    bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                    left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
                    right: { style: 'thin', color: { argb: 'FFE2E8F0' } }
                };
            });
        });

        sheet.columns.forEach((col) => {
            let maxLen = 10;
            col.eachCell({ includeEmpty: false }, (cell) => {
                const val = cell.value ? cell.value.toString() : '';
                if (val.length > maxLen && val.length < 50) maxLen = val.length;
            });
            col.width = Math.max(maxLen + 3, 10);
        });

        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        const instClean = (tech.installationName || 'Monitoreo').trim().replace(/[^a-zA-Z0-9_\-]/g, '_').substring(0, 25);
        a.download = `Planilla_Ruido_Ocupacional_${instClean}_${new Date().toISOString().slice(0, 10)}.xlsx`;
        a.click();
        window.URL.revokeObjectURL(url);
        closeExportModal();
    }
    window.downloadRuidoOcupacionalExcel = downloadRuidoOcupacionalExcel;

    // Garantizar disponibilidad global de modales de exportación y reporte fotográfico
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
</script>
