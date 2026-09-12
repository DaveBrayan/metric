<div class="modal-backdrop-custom" id="photoReportModal" role="dialog" aria-modal="true" aria-labelledby="photoReportModalTitle">
    <div class="modal-dialog-ruido" style="max-width: 1000px; width: 95%;">
        <div class="modal-header-custom">
            <div>
                <h2 id="photoReportModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); margin: 0;">
                    Catálogo y Reporte Fotográfico — Ruido Ambiental
                </h2>
                <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                    Configuración de cuadrícula fotográfica para informe técnico oficial
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closePhotoReportModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                @forelse($measurements as $m)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; justify-content: space-between;">
                            <span style="font-weight: 800; font-size: 13px; color: #0284c7;">Punto {{ $m['num'] }}</span>
                            <span style="font-size: 11px; font-weight: 700; color: {{ $m['is_compliant'] ? '#059669' : '#dc2626' }};">{{ $m['leq_d'] }} dBA</span>
                        </div>
                        <div style="height: 160px; border-radius: 8px; overflow: hidden; background: #e2e8f0; display: grid; place-items: center;">
                            @if(!empty($m['image_path']))
                                <img src="{{ $m['image_path'] }}" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <span style="font-size: 12px; color: #94a3b8; font-style: italic;">Sin imagen registrada</span>
                            @endif
                        </div>
                        <div style="font-size: 11.5px; color: #475569;">
                            <strong>{{ $m['normativa'] }}</strong> • {{ $m['tipo_zona'] }}
                        </div>
                        <div style="font-size: 11px; color: #64748b; line-height: 1.3;">
                            {{ $m['observations'] }}
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; color: #94a3b8; padding: 40px;">
                        No hay puntos con registro fotográfico en este módulo.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-secondary-subtle" onclick="closePhotoReportModal()">Cerrar</button>
        </div>
    </div>
</div>
