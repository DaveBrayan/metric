<div class="modal-backdrop-custom" id="ruidoAmbientalTablesModal" role="dialog" aria-modal="true" aria-labelledby="ruidoTablesModalTitle">
    <div class="modal-dialog-ruido" style="max-width: 1100px; width: 95%;">
        <div class="modal-header-custom">
            <div>
                <h2 id="ruidoTablesModalTitle" style="font-family: 'Outfit', sans-serif; font-size: 18px; font-weight: 800; color: var(--ink); margin: 0;">
                    Matriz Técnica & Evaluación de Ruido Ambiental
                </h2>
                <p style="font-size: 12.5px; color: #64748b; margin: 0;">
                    Cuadro comparativo perimetral frente a Límites Máximos Permisibles (LMP)
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
                            <th>Normativa / Tipo Zona</th>
                            <th>Colindancia N (X/Y)</th>
                            <th>Colindancia S (X/Y)</th>
                            <th>Colindancia E (X/Y)</th>
                            <th>Colindancia O (X/Y)</th>
                            <th style="text-align: center;">LMP</th>
                            <th style="text-align: center;">Leq (dBA)</th>
                            <th style="text-align: center;">Evaluación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($measurements as $m)
                            <tr>
                                <td style="text-align: center; font-weight: 800; color: #94a3b8;">{{ $m['num'] }}</td>
                                <td>
                                    <div style="font-weight: 700; color: #0284c7; font-size: 12.5px;">{{ $m['normativa'] }}</div>
                                    <div style="font-size: 11.5px; color: #64748b;">{{ $m['tipo_zona'] }}</div>
                                </td>
                                <td style="font-size: 11px;">{{ $m['norte_colindancia'] }}<br><span style="color:#94a3b8; font-family:monospace;">{{ $m['norte_x'] }}</span></td>
                                <td style="font-size: 11px;">{{ $m['sur_colindancia'] }}<br><span style="color:#94a3b8; font-family:monospace;">{{ $m['sur_x'] }}</span></td>
                                <td style="font-size: 11px;">{{ $m['este_colindancia'] }}<br><span style="color:#94a3b8; font-family:monospace;">{{ $m['este_x'] }}</span></td>
                                <td style="font-size: 11px;">{{ $m['oeste_colindancia'] }}<br><span style="color:#94a3b8; font-family:monospace;">{{ $m['oeste_x'] }}</span></td>
                                <td style="text-align: center; font-weight: 700;">{{ $m['limite_normativa'] }}</td>
                                <td style="text-align: center; font-weight: 800; color: {{ $m['is_compliant'] ? '#059669' : '#dc2626' }};">{{ $m['leq_d'] }}</td>
                                <td style="text-align: center;">
                                    @if($m['is_compliant'])
                                        <span class="badge-compliance-ok"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg><span>CUMPLE</span></span>
                                    @else
                                        <span class="badge-compliance-danger"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><span>NO CUMPLE</span></span>
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
