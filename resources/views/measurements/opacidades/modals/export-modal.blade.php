<div class="modal-backdrop-custom" id="exportModal" role="dialog" aria-modal="true" aria-labelledby="exportModalTitle">
    <div class="modal-dialog-opacity" style="max-width: 580px;">
        <div class="modal-header-custom">
            <div>
                <h2 id="exportModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); margin: 0 0 2px 0;">
                    Exportar Monitoreo de Opacidad
                </h2>
                <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                    Generación de planillas de cálculo Excel y catálogos fotográficos
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeExportModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 22px;">
            <div style="display: flex; flex-direction: column; gap: 14px;">
                <!-- Opción 1: Excel Técnico Oficial -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #ecfdf5; color: #059669; display: grid; place-items: center; font-size: 20px;">
                            📊
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 13.5px; color: var(--ink);">Planilla Técnica Oficial (Excel)</div>
                            <div style="font-size: 12px; color: #64748b;">Matriz de vehículos, lecturas de opacidad 1..3, RPM y evaluación LMP</div>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary-subtle" onclick="exportOpacityExcel()" style="color: #059669; border-color: #a7f3d0;">
                        Descargar
                    </button>
                </div>

                <!-- Opción 2: Reporte Fotográfico -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: grid; place-items: center; font-size: 20px;">
                            📷
                        </div>
                        <div>
                            <div style="font-weight: 800; font-size: 13.5px; color: var(--ink);">Catálogo Fotográfico de Ensayo</div>
                            <div style="font-size: 12px; color: #64748b;">Reporte con fichas técnicas, tubos de escape y placas vehiculares</div>
                        </div>
                    </div>
                    <button type="button" class="btn-secondary-subtle" onclick="openPhotoReportModal()" style="color: #2563eb; border-color: #bfdbfe;">
                        Configurar
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
    function exportOpacityExcel() {
        if (typeof ExcelJS === 'undefined') {
            alert('ExcelJS está cargando. Por favor intenta en unos segundos.');
            return;
        }

        const wb = new ExcelJS.Workbook();
        const ws = wb.addWorksheet('Opacidad Vehicular');

        ws.columns = [
            { header: 'N°', key: 'num', width: 6 },
            { header: 'Fecha', key: 'date', width: 14 },
            { header: 'Hora', key: 'time', width: 10 },
            { header: 'Tipo Vehículo', key: 'tipo', width: 22 },
            { header: 'Placa', key: 'placa', width: 14 },
            { header: 'Marca', key: 'marca', width: 16 },
            { header: 'Modelo', key: 'modelo', width: 16 },
            { header: 'Temp Motor (°C)', key: 'temp', width: 16 },
            { header: 'Opa 1 (%)', key: 'opa1', width: 12 },
            { header: 'Opa 2 (%)', key: 'opa2', width: 12 },
            { header: 'Opa 3 (%)', key: 'opa3', width: 12 },
            { header: 'Opa Promedio (%)', key: 'opa_prom', width: 18 },
            { header: 'LMP (%)', key: 'lmp', width: 12 },
            { header: 'RPM Promedio', key: 'rpm_prom', width: 16 },
            { header: 'Cumplimiento', key: 'cumple', width: 16 },
            { header: 'Zona UTM', key: 'zone', width: 10 },
            { header: 'Este (X)', key: 'east', width: 14 },
            { header: 'Norte (Y)', key: 'north', width: 14 },
            { header: 'Personal Registrador', key: 'staff', width: 24 },
            { header: 'Observaciones', key: 'obs', width: 30 }
        ];

        // Header styling
        ws.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };
        ws.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F46E5' } };

        opacityMeasurements.forEach(m => {
            ws.addRow({
                num: m.num,
                date: m.date_formatted,
                time: m.time_raw,
                tipo: m.tipo_vehiculo,
                placa: m.placa,
                marca: m.marca,
                modelo: m.modelo,
                temp: m.temp_c,
                opa1: m.opa_1,
                opa2: m.opa_2,
                opa3: m.opa_3,
                opa_prom: m.opa_promedio,
                lmp: m.limite_normativa,
                rpm_prom: m.rpm_promedio,
                cumple: m.is_compliant ? 'CUMPLE' : 'SUPERA LÍMITE',
                zone: m.utm_zone,
                east: m.utm_easting,
                north: m.utm_northing,
                staff: m.staff_name,
                obs: m.observations
            });
        });

        wb.xlsx.writeBuffer().then(buffer => {
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `Monitoreo_Opacidad_Vehicular_${window.MODULE_ID}.xlsx`;
            a.click();
            window.URL.revokeObjectURL(url);
            closeExportModal();
        });
    }
</script>
