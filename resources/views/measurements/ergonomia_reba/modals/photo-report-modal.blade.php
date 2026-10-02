<!-- ==========================================================================
     MODAL 7: REPORTE FOTOGRÁFICO DE EVALUACIONES REBA (MOSAICO CARTA)
     ========================================================================== -->
<div class="modal-backdrop-custom" id="photoReportModal" role="dialog" aria-modal="true"
    aria-labelledby="photoReportModalTitle">
    <div class="modal-dialog-illumination" style="max-width: 1280px; width: 98%; max-height: 92vh; display: flex; flex-direction: column;">
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
                        style="font-size: 17px; margin: 0; font-weight: 800; color: #0f172a;">Catálogo Fotográfico — Ergonomía REBA</h2>
                    <span style="font-size: 12px; color: #64748b; font-weight: 500;">Distribución en mosaico (2x3, 2x4, 3x3, 3x4) en formato de hoja Carta para exportación PDF</span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn-primary-hero-action" onclick="window.print()"
                    title="Imprimir o guardar en PDF (Tamaño Carta)">
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

        <div class="modal-body-custom" style="flex: 1; overflow-y: auto; padding: 20px 24px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; flex-wrap: wrap; gap: 12px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 12px 16px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <label style="font-size: 12.5px; font-weight: 800; color: #0f172a;">Distribución de Mosaico por Hoja:</label>
                    <select id="rebaMosaicDistributionSelect" class="reba-input-control" style="width: 150px; height: 36px; padding: 4px 8px;" onchange="changeRebaMosaicGrid(this.value)">
                        <option value="2x3">2 x 3 (6 fotos)</option>
                        <option value="2x4">2 x 4 (8 fotos)</option>
                        <option value="3x3">3 x 3 (9 fotos)</option>
                        <option value="3x4">3 x 4 (12 fotos)</option>
                    </select>
                </div>
                <div style="font-size: 12px; color: #64748b; font-weight: 600;">
                    Total puestos: <strong>{{ count($measurements) }}</strong>
                </div>
            </div>

            <!-- Grid de Fotos Demo -->
            <div id="rebaPhotoMosaicGrid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                @foreach($measurements as $m)
                    <div style="border: 1.5px solid #cbd5e1; border-radius: 12px; overflow: hidden; background: #ffffff; box-shadow: 0 2px 8px rgba(15, 28, 46, 0.04);">
                        <div style="height: 160px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                            @if(!empty($m['image_path']))
                                <img src="{{ $m['image_path'] }}" alt="{{ $m['puesto_trabajo'] }}" style="width: 100%; height: 100%; object-fit: cover; cursor: pointer;"
                                    onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Puesto {{ $m['num'] }}: {{ addslashes($m['puesto_trabajo']) }}')">
                            @else
                                <div style="display: flex; flex-direction: column; align-items: center; gap: 6px; color: #94a3b8;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                    <span style="font-size: 11px; font-weight: 600;">Sin fotografía</span>
                                </div>
                            @endif
                            <span class="reba-score-pill {{ $m['badge_class'] ?? 'risk-medio' }}" style="position: absolute; top: 8px; right: 8px; font-size: 11px; padding: 2px 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
                                Score {{ $m['score_final'] ?? 1 }}
                            </span>
                        </div>
                        <div style="padding: 12px 14px;">
                            <div style="font-size: 11px; font-weight: 800; color: #0284c7; text-transform: uppercase;">
                                Puesto #{{ $m['num'] }}
                            </div>
                            <div style="font-weight: 800; font-size: 13px; color: #0f172a; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $m['puesto_trabajo'] }}
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $m['area_sector'] }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="modal-footer-custom" style="justify-content: flex-end;">
            <button type="button" class="btn-secondary-subtle" onclick="closePhotoReportModal()">Cerrar</button>
        </div>
    </div>
</div>
