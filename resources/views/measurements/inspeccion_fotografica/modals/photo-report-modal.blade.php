<!-- Modal de Configuración y Generación de Reporte Fotográfico (Tema Celeste) -->
<div class="modal-backdrop-custom" id="photoReportModal" role="dialog" aria-modal="true" aria-labelledby="photoReportModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 640px;">
        <div class="modal-header-custom">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 34px; height: 34px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                        <circle cx="9" cy="9" r="2"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                    </svg>
                </div>
                <div>
                    <h2 id="photoReportModalTitle" style="font-size: 17px; margin: 0;">Configuración de Reporte Fotográfico</h2>
                    <span style="font-size: 12px; color: #64748b;">Genera el catálogo visual de evidencias</span>
                </div>
            </div>
            <button type="button" class="btn-close-modal" onclick="closePhotoReportModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 22px 24px;">
            <div class="form-field-group">
                <label class="form-field-label" for="photo_report_title">Título del Catálogo</label>
                <input type="text" id="photo_report_title" class="custom-form-input"
                    value="Reporte Fotográfico de Inspección en Campo — {{ $module->project ? $module->project->name : 'Pachabol' }}">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                <div class="form-field-group" style="margin-bottom: 0;">
                    <label class="form-field-label" for="photo_report_columns">Fotos por Fila</label>
                    <select id="photo_report_columns" class="custom-form-select">
                        <option value="2" selected>2 Fotos por Fila (Estándar)</option>
                        <option value="3">3 Fotos por Fila (Compacto)</option>
                        <option value="1">1 Foto por Fila (Detallado)</option>
                    </select>
                </div>
                <div class="form-field-group" style="margin-bottom: 0;">
                    <label class="form-field-label" for="photo_report_orientation">Orientación de Hoja</label>
                    <select id="photo_report_orientation" class="custom-form-select">
                        <option value="portrait" selected>Vertical (Retrato)</option>
                        <option value="landscape">Horizontal (Paisaje)</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 16px;">
                <label style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: #334155; cursor: pointer;">
                    <input type="checkbox" id="photo_report_include_gps" checked style="accent-color: #0284c7; width: 16px; height: 16px;">
                    <span>Incluir coordenadas satelitales GPS / UTM por punto</span>
                </label>
                <label style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 600; color: #334155; cursor: pointer;">
                    <input type="checkbox" id="photo_report_include_desc" checked style="accent-color: #0284c7; width: 16px; height: 16px;">
                    <span>Incluir descripciones y observaciones detalladas</span>
                </label>
            </div>

            <div style="font-size: 12px; color: #64748b; line-height: 1.4;">
                Se generará una vista optimizada para impresión profesional (Ctrl + P) o guardado en PDF de alta fidelidad.
            </div>
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closePhotoReportModal()">Cancelar</button>
            <button type="button" class="btn-primary-hero-action" onclick="generatePhotoReport()">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect width="12" height="8" x="6" y="14"/>
                </svg>
                <span>Generar / Imprimir Catálogo</span>
            </button>
        </div>
    </div>
</div>
