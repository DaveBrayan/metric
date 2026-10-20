<div class="modal-backdrop-custom" id="ruidoAmbientalTablesModal" role="dialog" aria-modal="true" aria-labelledby="ruidoTablesModalTitle">
    <div class="modal-dialog-ruido" style="max-width: 1100px; width: 95%;">
        <div class="modal-header-custom">
            <div>
                <h2 id="ruidoTablesModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); margin: 0;">
                    Matriz Técnica & Evaluación de Ruido Ocupacional
                </h2>
                <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                    Análisis de dosimetría y cumplimiento de NTS-002/17 (LAeq, TMPE, Dosis)
                </p>
            </div>
            <button type="button" class="btn-close-modal" onclick="closeRuidoAmbientalTablesModal()" aria-label="Cerrar">✕</button>
        </div>

        <div class="modal-body-custom" style="padding: 20px;">
            <div class="table-responsive-box">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th style="width: 50px; text-align: center;">N°</th>
                            <th>Área</th>
                            <th>Punto Medición</th>
                            <th>Tipo Ruido</th>
                            <th style="text-align: center;">TPE (h)</th>
                            <th style="text-align: center;">LAeq (dBA)</th>
                            <th style="text-align: center;">TMPE (h)</th>
                            <th style="text-align: center;">Dosis</th>
                            <th style="text-align: center;">Cumple</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($measurements as $m)
                        <tr>
                            <td style="text-align: center; font-weight: 800; color: #94a3b8;">{{ $m['num'] }}</td>
                            <td style="font-size: 12.5px; font-weight: 600; color: #0284c7;">{{ $m['area'] }}</td>
                            <td style="font-size: 12.5px; color: #334155;">{{ $m['punto_medicion'] }}</td>
                            <td style="font-size: 12px; color: #64748b;"><span style="padding:4px 8px;background:#f0f9ff;border-left:3px solid #0284c7;border-radius:4px;">{{ $m['tipo_ruido'] }}</span></td>
                            <td style="text-align: center; font-weight: 600;">{{ number_format($m['tiempo_expos_h'], 1) }}</td>
                            <td style="text-align: center; font-weight: 800; color: {{ ($m['leq_t_db'] !== null && $m['leq_t_db'] <= 85) ? '#059669' : '#dc2626' }};">{{ $m['leq_t_db'] !== null ? number_format($m['leq_t_db'], 1) : '—' }}</td>
                            <td style="text-align: center; font-weight: 600;">{{ $m['tmpe'] !== null ? number_format($m['tmpe'], 1) : '—' }}</td>
                            <td style="text-align: center; font-weight: 800; color: {{ ($m['dosis'] !== null && $m['dosis'] <= 1.0) ? '#059669' : '#dc2626' }};">{{ $m['dosis'] !== null ? number_format($m['dosis'], 2) : '—' }}</td>
                            <td style="text-align: center;">
                                @if($m['dosis'] !== null && $m['dosis'] <= 1.0)
                                <span class="badge-compliance-ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>OK</span></span>
                                @else
                                <span class="badge-compliance-danger"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span>NO OK</span></span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: #94a3b8; padding: 30px;">No hay datos para la matriz técnica.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer-custom">
            <button type="button" class="btn-primary-hero-action" onclick="closeRuidoAmbientalTablesModal()" style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
        </div>
    </div>
</div>
