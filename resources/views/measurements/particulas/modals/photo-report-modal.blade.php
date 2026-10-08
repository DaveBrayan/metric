    <!-- ==========================================================================
         MODAL 7: CONFIGURADOR DE REPORTE FOTOGRÁFICO DE PARTÍCULAS
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoReportModal" role="dialog" aria-modal="true" style="display: none;"
        aria-labelledby="photoReportModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 900px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div
                        style="width: 34px; height: 34px; border-radius: 9px; background: #f0f9ff; color: #0284c7; display: grid; place-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z" />
                            <circle cx="12" cy="13" r="4" />
                        </svg>
                    </div>
                    <div>
                        <h2 id="photoReportModalTitle" style="font-size: 17px; margin: 0;">Catálogo / Reporte Fotográfico de Partículas Ocupacionales</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Configura la distribución en cuadrícula y genera el informe fotográfico</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closePhotoReportConfigModal()"
                    aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <!-- Opciones de Cuadrícula y Orientación -->
                <div class="photo-report-controls-bar">
                    <div class="photo-report-ctrl-group">
                        <label class="photo-report-label">Distribución (Cuadrícula):</label>
                        <div class="photo-report-pills-group">
                            <button type="button" class="photo-grid-pill active" data-grid="2x3" onclick="setPhotoGridPreset('2x3')">2x3 (6 fotos/pág)</button>
                            <button type="button" class="photo-grid-pill" data-grid="2x4" onclick="setPhotoGridPreset('2x4')">2x4 (8 fotos/pág)</button>
                            <button type="button" class="photo-grid-pill" data-grid="3x3" onclick="setPhotoGridPreset('3x3')">3x3 (9 fotos/pág)</button>
                            <button type="button" class="photo-grid-pill" data-grid="3x4" onclick="setPhotoGridPreset('3x4')">3x4 (12 fotos/pág)</button>
                        </div>
                    </div>

                    <div class="photo-report-ctrl-group">
                        <label class="photo-report-label">Orientación:</label>
                        <div class="photo-report-pills-group">
                            <button type="button" class="photo-orient-pill active" data-orient="landscape" onclick="setPhotoOrientation('landscape')">Horizontal</button>
                            <button type="button" class="photo-orient-pill" data-orient="portrait" onclick="setPhotoOrientation('portrait')">Vertical</button>
                        </div>
                    </div>
                </div>

                <!-- Selector de Puntos a Incluir -->
                <div class="photo-report-points-selection-box">
                    <div class="photo-report-points-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="font-weight: 800; font-size: 13px; color: var(--ink);">Puntos de Medición para el Reporte</div>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn-subtle-pill" onclick="selectAllPhotoReportPoints(true)">Seleccionar Todos</button>
                            <button type="button" class="btn-subtle-pill" onclick="selectAllPhotoReportPoints(false)">Deseleccionar</button>
                        </div>
                    </div>

                    <div class="photo-report-items-grid" id="photoReportItemsGrid">
                        @foreach($measurements as $m)
                            <label class="photo-report-point-card {{ !empty($m['image_path']) ? 'has-photo' : 'no-photo' }}" id="pr_card_{{ $m['id'] }}">
                                <input type="checkbox" class="pr-point-checkbox" value="{{ $m['id'] }}" checked onchange="togglePhotoReportCardSelection('{{ $m['id'] }}')">
                                <div class="pr-card-thumb">
                                    @if(!empty($m['image_path']))
                                        <img src="{{ $m['image_path'] }}" alt="Foto">
                                    @else
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                            <circle cx="9" cy="9" r="2"/>
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="pr-card-info">
                                    <div class="pr-card-title">Punto #{{ $m['num'] }}</div>
                                    <div class="pr-card-area">{{ $m['area'] }}</div>
                                    <div class="pr-card-workstation">{{ $m['punto_medicion'] ?: $m['workstation'] }}</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: space-between;">
                <button type="button" class="btn-secondary-subtle" onclick="closePhotoReportConfigModal()">Cancelar</button>
                <button type="button" class="btn-primary-hero-action" onclick="generatePhotoReportPrintView()">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect width="12" height="8" x="6" y="14"/>
                    </svg>
                    <span>Imprimir / Exportar Reporte Fotográfico</span>
                </button>
            </div>
        </div>
    </div>
