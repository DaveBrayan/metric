<div class="modal-backdrop-custom" id="opacityTablesModal" role="dialog" aria-modal="true" aria-labelledby="opacityTablesModalTitle">
    <div class="modal-dialog-opacity" style="max-width: 860px;">
        <div class="modal-header-custom">
            <div>
                <h2 id="opacityTablesModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); margin: 0 0 2px 0;">
                    Límites Máximos Permisibles — Opacidad Vehicular
                </h2>
                <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                    Normativa nacional e internacional para motores de encendido por compresión (Diésel)
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeOpacityTablesModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 22px;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                        <th style="padding: 10px 14px; text-align: left; font-weight: 800;">Año del Modelo del Vehículo</th>
                        <th style="padding: 10px 14px; text-align: center; font-weight: 800;">Coeficiente k (m⁻¹)</th>
                        <th style="padding: 10px 14px; text-align: center; font-weight: 800;">Opacidad (%)</th>
                        <th style="padding: 10px 14px; text-align: left; font-weight: 800;">Norma de Referencia</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 10px 14px; font-weight: 700;">Vehículos 1995 y anteriores</td>
                        <td style="padding: 10px 14px; text-align: center; font-family: monospace;">2.62 m⁻¹</td>
                        <td style="padding: 10px 14px; text-align: center; font-weight: 800; color: #d97706;">65.0 %</td>
                        <td style="padding: 10px 14px; color: #64748b;">Ley 1333 / RMCA Anexo 5</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                        <td style="padding: 10px 14px; font-weight: 700;">Vehículos 1996 a 2005</td>
                        <td style="padding: 10px 14px; text-align: center; font-family: monospace;">1.61 m⁻¹</td>
                        <td style="padding: 10px 14px; text-align: center; font-weight: 800; color: #0284c7;">50.0 %</td>
                        <td style="padding: 10px 14px; color: #64748b;">Decreto Supremo 28139</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 10px 14px; font-weight: 700;">Vehículos 2006 en adelante (Euro 2 / Euro 3)</td>
                        <td style="padding: 10px 14px; text-align: center; font-family: monospace;">1.20 m⁻¹</td>
                        <td style="padding: 10px 14px; text-align: center; font-weight: 800; color: #059669;">40.0 %</td>
                        <td style="padding: 10px 14px; color: #64748b;">NB 62002 / EPA</td>
                    </tr>
                    <tr style="background: #f8fafc;">
                        <td style="padding: 10px 14px; font-weight: 700;">Vehículos Euro 4 / Euro 5 / Filtro DPF</td>
                        <td style="padding: 10px 14px; text-align: center; font-family: monospace;">0.50 m⁻¹</td>
                        <td style="padding: 10px 14px; text-align: center; font-weight: 800; color: #059669;">20.0 %</td>
                        <td style="padding: 10px 14px; color: #64748b;">Norma Europea ECE R24</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closeOpacityTablesModal()">Cerrar</button>
        </div>
    </div>
</div>
