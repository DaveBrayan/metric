<div class="modal-backdrop-custom" id="calculosModalRuido" role="dialog" aria-modal="true" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.65); z-index:9999; backdrop-filter:blur(3px);">
    <div class="modal-dialog-ruido" style="max-width:98vw; width:98vw; max-height:92vh; background:#ffffff; border-radius:10px; display:flex; flex-direction:column; box-shadow:0 25px 50px -12px rgba(0,0,0,0.35); overflow:hidden; border:1px solid #cbd5e1;">
        
        {{-- Header del Modal --}}
        <div class="modal-header-custom" style="display:flex; justify-content:space-between; align-items:center; padding:14px 20px; border-bottom:1px solid #cbd5e1; background:#f8fafc;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:34px; height:34px; border-radius:8px; background:#e0f2fe; color:#0284c7; display:flex; align-items:center; justify-content:center;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="2" width="16" height="20" rx="2"/>
                        <line x1="8" y1="6" x2="16" y2="6"/>
                        <line x1="16" y1="14" x2="16" y2="18"/>
                        <path d="M16 10h.01"/>
                        <path d="M12 10h.01"/>
                        <path d="M8 10h.01"/>
                        <path d="M12 14h.01"/>
                        <path d="M8 14h.01"/>
                        <path d="M12 18h.01"/>
                        <path d="M8 18h.01"/>
                    </svg>
                </div>
                <div>
                    <h2 id="calculosModalTitle" style="font-family:'Outfit',sans-serif; font-size:17px; font-weight:800; color:#0f172a; margin:0; line-height:1.2;">
                        Matriz de Cálculos de Ruido Ocupacional
                    </h2>
                    <p id="calculosModalSubtitle" style="font-size:12px; color:#64748b; margin:2px 0 0 0;">
                        Transformación Energética 10^(NPSi/10) · NTS-002/17
                    </p>
                </div>
            </div>

            <div style="display:flex; align-items:center; gap:10px;">
                <button type="button" class="btn-secondary-subtle" onclick="exportCalculosTableToExcel()" title="Descargar hoja de cálculo compatible con Excel" style="padding:6px 12px; font-size:12px; display:inline-flex; align-items:center; gap:6px; border-radius:6px; background:#f1f5f9; border:1px solid #cbd5e1; cursor:pointer; font-weight:600; color:#334155; transition:all 0.15s;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Exportar a Excel (.csv)
                </button>

                <button type="button" class="btn-close-modal" onclick="closeCalculosModal()" style="width:30px; height:30px; display:flex; align-items:center; justify-content:center; background:#f1f5f9; border:1px solid #e2e8f0; border-radius:6px; font-size:16px; cursor:pointer; color:#64748b; transition:all 0.15s;" onmouseenter="this.style.background='#fee2e2';this.style.color='#dc2626'" onmouseleave="this.style.background='#f1f5f9';this.style.color='#64748b'">✕</button>
            </div>
        </div>

        {{-- Cuerpo del Modal --}}
        <div class="modal-body-custom" style="padding:14px 18px; overflow-y:auto; flex:1; background:#ffffff;">
            
            {{-- Tabla con desplazamiento horizontal responsivo --}}
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch; border:1px solid #94a3b8; border-radius:4px; background:#ffffff; box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                <table id="calculosMatrixTable" style="width:100%; border-collapse:collapse; font-size:11px; white-space:nowrap;">
                    <thead>
                        {{-- Fila 1 de Encabezados (Excel style) --}}
                        <tr style="background:#d9d9d9; border-bottom:1px solid #94a3b8;">
                            <th rowspan="2" style="position:sticky; left:0; z-index:4; background:#d9d9d9; padding:8px 12px; text-align:center; font-weight:800; color:#000000; border-right:2px solid #94a3b8; border-bottom:2px solid #94a3b8; font-size:12px; min-width:55px;">
                                NRO.
                            </th>
                            <th colspan="30" style="padding:7px 6px; text-align:center; font-weight:800; color:#000000; border-right:2px solid #94a3b8; border-bottom:1px solid #94a3b8; font-size:12px; background:#d9d9d9; letter-spacing:0.5px;">
                                10^(NPSI/10)
                            </th>
                            <th rowspan="2" style="padding:8px 12px; text-align:center; font-weight:800; color:#000000; border-right:1px solid #94a3b8; border-bottom:2px solid #94a3b8; font-size:12px; background:#d9d9d9; min-width:110px;" title="=SUMA(B2:AE2)">
                                <span style="font-size:14px; font-weight:bold; vertical-align:middle;">&sum;</span> 10<sup>(NPSi/10)</sup>
                            </th>
                            <th rowspan="2" style="padding:8px 12px; text-align:center; font-weight:800; color:#000000; border-right:1px solid #94a3b8; border-bottom:2px solid #94a3b8; font-size:12px; background:#d9d9d9; min-width:75px;" title="=MAX(DATOS!H2:AK2)">
                                MAX
                            </th>
                            <th rowspan="2" style="padding:8px 12px; text-align:center; font-weight:800; color:#000000; border-right:1px solid #94a3b8; border-bottom:2px solid #94a3b8; font-size:12px; background:#d9d9d9; min-width:90px;" title="=10*LOG10((1/30)*AF2)">
                                LAeq,T<br><span style="font-weight:normal; font-size:10px; color:#334155;">(dB)</span>
                            </th>
                            <th rowspan="2" style="padding:8px 12px; text-align:center; font-weight:800; color:#000000; border-right:1px solid #94a3b8; border-bottom:2px solid #94a3b8; font-size:12px; background:#d9d9d9; min-width:95px;" title="=SI.ERROR(8/(2^((AH2-85)/3)); &quot;&quot;)">
                                TMPE<br><span style="font-weight:normal; font-size:10px; color:#334155;">(horas)</span>
                            </th>
                            <th rowspan="2" style="padding:8px 12px; text-align:center; font-weight:800; color:#000000; border-bottom:2px solid #94a3b8; font-size:12px; background:#d9d9d9; min-width:85px;" title="=DATOS!E2/AI2">
                                DOSIS
                            </th>
                        </tr>

                        {{-- Fila 2 de Encabezados: Subcolumnas 1 a 30 --}}
                        <tr style="background:#e5e7eb; border-bottom:2px solid #94a3b8;">
                            @for ($col = 1; $col <= 30; $col++)
                                <th style="padding:5px 4px; text-align:center; font-size:10px; font-weight:700; color:#334155; border-right:{{ $col === 30 ? '2px solid #94a3b8' : '1px solid #cbd5e1' }}; min-width:62px;">
                                    {{ $col }}
                                </th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody id="calculosMatrixTableBody">
                        {{-- Inyectado dinámicamente vía JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- Pie explicativo de fórmulas técnicas NTS-002/17 --}}
            <div style="margin-top:12px; padding:10px 14px; background:#f8fafc; border-radius:6px; border:1px solid #e2e8f0; display:flex; flex-wrap:wrap; gap:16px; font-size:11px; color:#475569; align-items:center;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#0284c7;"></span>
                    <strong style="color:#0f172a;">10^(NPSi/10):</strong> Transformación energética de cada punto en decibeles.
                </div>
                <div>
                    <strong style="color:#0369a1;">&sum; 10^(NPSi/10):</strong> <code style="background:#e0f2fe; color:#0369a1; padding:1px 5px; border-radius:3px;">=SUMA(B2:AE2)</code>
                </div>
                <div>
                    <strong style="color:#0f172a;">MAX:</strong> <code style="background:#f1f5f9; color:#0f172a; padding:1px 5px; border-radius:3px;">=MAX(DATOS!H2:AK2)</code>
                </div>
                <div>
                    <strong style="color:#059669;">LAeq,T:</strong> <code style="background:#dcfce7; color:#166534; padding:1px 5px; border-radius:3px;">=10*LOG10((1/30)*AF2)</code>
                </div>
                <div>
                    <strong style="color:#475569;">TMPE:</strong> <code style="background:#f1f5f9; color:#334155; padding:1px 5px; border-radius:3px;">=SI.ERROR(8/(2^((AH2-85)/3));"")</code>
                </div>
                <div>
                    <strong style="color:#dc2626;">DOSIS:</strong> <code style="background:#fee2e2; color:#991b1b; padding:1px 5px; border-radius:3px;">=DATOS!E2/AI2</code> (Tiempo Exposición / TMPE)
                </div>
            </div>

        </div>

        {{-- Footer del Modal --}}
        <div class="modal-footer-custom" style="display:flex; justify-content:space-between; align-items:center; padding:10px 20px; border-top:1px solid #e2e8f0; background:#f8fafc;">
            <div id="calcStatusSummary" style="font-size:12px; color:#64748b; font-weight:600;">
                <!-- Contador de registros cargados -->
            </div>
            <button type="button" class="btn-primary-hero-action" onclick="closeCalculosModal()" style="padding:7px 20px; font-size:13px; font-weight:700; border-radius:6px; background:#0284c7; color:#ffffff; border:none; cursor:pointer;">
                Cerrar
            </button>
        </div>

    </div>
</div>

<script>
/**
 * Formatea los valores de energía acústica 10^(NPSi/10) exactamente con la convención de Excel
 */
function formatEnergyExcel(e) {
    if (!e || e <= 0 || isNaN(e)) return '<span style="color:#cbd5e1;">—</span>';
    
    // Números muy grandes (e.g. 10 millones en adelante o redondeados como 2E+06)
    if (e >= 10000000) {
        return e.toExponential(0).toUpperCase();
    }
    if (e >= 1000000) {
        var rounded = Math.round(e);
        if (rounded % 100000 === 0) {
            return rounded.toExponential(0).toUpperCase();
        }
        return rounded.toString();
    }
    if (e >= 1000) {
        return Math.round(e).toString();
    }
    // Números menores a 1000 con decimales en coma (ej: 231,6 - 99,62 - 6,537 - 10,43015)
    var s = (Math.round(e * 100000) / 100000).toString().replace('.', ',');
    return s;
}

/**
 * Renderiza la matriz completa de cálculos para todas las mediciones
 */
function renderCalculosMatrix(targetIdx) {
    var data = window.ALL_MEASUREMENTS_DATA || [];
    var tbody = document.getElementById('calculosMatrixTableBody');
    var statusSummary = document.getElementById('calcStatusSummary');
    if (!tbody) return;

    if (statusSummary) {
        statusSummary.textContent = data.length + ' registro(s) de medición evaluados con 30 puntos NPS.';
    }

    if (data.length === 0) {
        tbody.innerHTML = '<tr><td colspan="37" style="text-align:center;padding:36px;color:#94a3b8;font-size:12px;">No hay mediciones registradas en este monitoreo.</td></tr>';
        return;
    }

    var html = '';
    data.forEach(function (m, idx) {
        var med = m.mediciones_db || [];
        if (typeof med === 'string') {
            try { med = JSON.parse(med) || []; } catch(e) { med = []; }
        }
        var tpe = parseFloat(m.tiempo_expos_h) || 8.0;

        var slotEnergies = [];
        var npsMax = 0;
        var hasAnyNps = false;

        // Exactamente 30 columnas
        for (var i = 0; i < 30; i++) {
            var rawVal = (med && med[i] !== undefined && med[i] !== null && med[i] !== '') ? parseFloat(med[i]) : null;
            if (rawVal !== null && !isNaN(rawVal) && rawVal > 0) {
                hasAnyNps = true;
                if (rawVal > npsMax) npsMax = rawVal;
                var energy = Math.pow(10.0, rawVal / 10.0);
                slotEnergies.push({ nps: rawVal, energy: energy });
            } else {
                slotEnergies.push({ nps: null, energy: 0 });
            }
        }

        // 1. =SUMA(B2:AE2)
        var sumaE = 0;
        for (var k = 0; k < 30; k++) {
            sumaE += slotEnergies[k].energy;
        }

        // 2. MAX: =MAX(DATOS!H2:AK2)
        // 3. LAeq,T: =10*LOG10((1/30)*AF2)
        var laeq = (hasAnyNps && sumaE > 0) ? (10.0 * Math.log10((1.0 / 30.0) * sumaE)) : 0;

        // 4. TMPE: =SI.ERROR(8/(2^((AH2-85)/3)); "")
        var tmpe = (laeq > 0) ? (8.0 / Math.pow(2.0, (laeq - 85.0) / 3.0)) : null;

        // 5. DOSIS: =DATOS!E2/AI2
        var dosis = (tmpe !== null && tmpe > 0) ? (tpe / tmpe) : null;

        var isHighlighted = (targetIdx !== undefined && targetIdx !== null && targetIdx === idx);
        var baseBg = isHighlighted ? '#fef9c3' : (idx % 2 === 0 ? '#ffffff' : '#f8fafc');
        var rowId = 'calc-row-' + idx;

        var r = '<tr id="' + rowId + '" style="background:' + baseBg + ';border-bottom:1px solid #cbd5e1;transition:background 0.15s;" onmouseenter="this.style.background=\'#f1f5f9\'" onmouseleave="this.style.background=\'' + baseBg + '\'">';

        // NRO.
        r += '<td style="position:sticky;left:0;z-index:2;background:' + (isHighlighted ? '#fef08a' : (idx % 2 === 0 ? '#ffffff' : '#f8fafc')) + ';padding:6px 10px;text-align:center;font-weight:800;color:#000000;border-right:2px solid #94a3b8;border-bottom:1px solid #cbd5e1;font-size:12px;" title="Registro ' + (idx + 1) + ' | Punto: ' + (m.punto_medicion || 'P-' + (idx+1)) + ' | Área: ' + (m.area || '—') + ' | TPE: ' + tpe + 'h">' + (idx + 1) + '</td>';

        // 30 Columnas de energía
        for (var p = 0; p < 30; p++) {
            var item = slotEnergies[p];
            if (item.nps !== null && item.energy > 0) {
                var displayVal = formatEnergyExcel(item.energy);
                r += '<td style="padding:6px 6px;text-align:right;font-family:\'Consolas\',monospace;font-size:11px;color:#000000;border-right:' + (p === 29 ? '2px solid #94a3b8' : '1px solid #e2e8f0') + ';border-bottom:1px solid #cbd5e1;" title="Punto NPS ' + (p + 1) + ': ' + item.nps + ' dB&#10;10^(' + item.nps + '/10) = ' + item.energy.toFixed(4) + '">' + displayVal + '</td>';
            } else {
                r += '<td style="padding:6px 6px;text-align:center;font-family:\'Consolas\',monospace;font-size:11px;color:#cbd5e1;border-right:' + (p === 29 ? '2px solid #94a3b8' : '1px solid #e2e8f0') + ';border-bottom:1px solid #cbd5e1;">—</td>';
            }
        }

        // =SUMA(B2:AE2)
        var sumText = hasAnyNps ? Math.round(sumaE).toString() : '—';
        r += '<td style="padding:6px 10px;text-align:right;font-family:\'Consolas\',monospace;font-size:11px;font-weight:800;color:#0369a1;background:#f0f9ff;border-right:1px solid #94a3b8;border-bottom:1px solid #cbd5e1;" title="=SUMA(B' + (idx+2) + ':AE' + (idx+2) + ') = ' + sumaE.toFixed(2) + '">' + sumText + '</td>';

        // MAX
        var maxText = hasAnyNps ? npsMax.toFixed(2).replace('.', ',') : '—';
        r += '<td style="padding:6px 10px;text-align:right;font-family:\'Consolas\',monospace;font-size:11px;font-weight:700;color:#000000;background:#ffffff;border-right:1px solid #94a3b8;border-bottom:1px solid #cbd5e1;" title="=MAX(DATOS!H' + (idx+2) + ':AK' + (idx+2) + ') = ' + (hasAnyNps ? npsMax.toFixed(2) : 0) + ' dB">' + maxText + '</td>';

        // LAeq,T (dB)
        var leqText = hasAnyNps ? laeq.toFixed(2).replace('.', ',') : '—';
        var leqColor = (hasAnyNps && laeq > 85.0) ? '#dc2626' : '#059669';
        r += '<td style="padding:6px 10px;text-align:right;font-family:\'Consolas\',monospace;font-size:11px;font-weight:800;color:' + leqColor + ';background:#ffffff;border-right:1px solid #94a3b8;border-bottom:1px solid #cbd5e1;" title="=10*LOG10((1/30)*AF' + (idx+2) + ') = ' + laeq.toFixed(2) + ' dB">' + leqText + '</td>';

        // TMPE (horas)
        var tmpeText = tmpe !== null ? tmpe.toFixed(2).replace('.', ',') : '—';
        r += '<td style="padding:6px 10px;text-align:right;font-family:\'Consolas\',monospace;font-size:11px;font-weight:700;color:#000000;background:#ffffff;border-right:1px solid #94a3b8;border-bottom:1px solid #cbd5e1;" title="=SI.ERROR(8/(2^((AH' + (idx+2) + '-85)/3)); &quot;&quot;) = ' + tmpeText + ' horas">' + tmpeText + '</td>';

        // DOSIS
        var dosisText = dosis !== null ? dosis.toFixed(3).replace('.', ',') : '—';
        var dosisColor = (dosis !== null && dosis > 1.0) ? '#dc2626' : '#059669';
        r += '<td style="padding:6px 10px;text-align:right;font-family:\'Consolas\',monospace;font-size:11px;font-weight:800;color:' + dosisColor + ';background:#ffffff;border-bottom:1px solid #cbd5e1;" title="=DATOS!E' + (idx+2) + '/AI' + (idx+2) + ' (' + tpe + 'h / ' + tmpeText + 'h) = ' + (dosis !== null ? dosis.toFixed(4) : '—') + '">' + dosisText + '</td>';

        r += '</tr>';
        html += r;
    });

    tbody.innerHTML = html;

    if (targetIdx !== undefined && targetIdx !== null) {
        setTimeout(function() {
            var targetRow = document.getElementById('calc-row-' + targetIdx);
            if (targetRow) {
                targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }, 150);
    }
}

/**
 * Abre el modal de cálculos (sea para toda la lista o para un punto específico)
 */
function openCalculosModal(m, targetIdx) {
    var modal = document.getElementById('calculosModalRuido');
    if (!modal) return;
    
    // Si viene de un botón individual, inferir targetIdx si no vino
    if (m && (targetIdx === undefined || targetIdx === null)) {
        var data = window.ALL_MEASUREMENTS_DATA || [];
        targetIdx = data.findIndex(function(x) { return x.id === m.id; });
        if (targetIdx < 0) targetIdx = 0;
    }

    renderCalculosMatrix(targetIdx);
    modal.style.display = 'flex';
}

/**
 * Abre el modal de cálculos para todas las mediciones
 */
window.openCalculosListModal = function() {
    openCalculosModal(null, null);
};

/**
 * Cierra el modal de cálculos
 */
function closeCalculosModal() {
    var modal = document.getElementById('calculosModalRuido');
    if (modal) modal.style.display = 'none';
}
window.closeCalculosListModal = closeCalculosModal;

/**
 * Exporta la matriz de cálculos en formato CSV compatible con Microsoft Excel (con BOM UTF-8)
 */
function exportCalculosTableToExcel() {
    var data = window.ALL_MEASUREMENTS_DATA || [];
    if (data.length === 0) {
        alert('No hay mediciones para exportar.');
        return;
    }
    var sep = ';';
    var csv = '\uFEFF'; // BOM UTF-8

    // Fila 1 Encabezados
    var h = ['NRO.'];
    for (var i = 1; i <= 30; i++) {
        h.push('10^(NPS' + i + '/10)');
    }
    h.push('∑ 10^(NPSi/10)');
    h.push('MAX');
    h.push('LAeq,T (dB)');
    h.push('TMPE (horas)');
    h.push('DOSIS');
    csv += h.join(sep) + '\r\n';

    data.forEach(function (m, idx) {
        var med = m.mediciones_db || [];
        if (typeof med === 'string') {
            try { med = JSON.parse(med) || []; } catch(e) { med = []; }
        }
        var tpe = parseFloat(m.tiempo_expos_h) || 8.0;
        var row = [(idx + 1).toString()];
        var sumaE = 0;
        var npsMax = 0;
        var hasAny = false;

        for (var p = 0; p < 30; p++) {
            var rawVal = (med && med[p] !== undefined && med[p] !== null && med[p] !== '') ? parseFloat(med[p]) : null;
            if (rawVal !== null && !isNaN(rawVal) && rawVal > 0) {
                hasAny = true;
                if (rawVal > npsMax) npsMax = rawVal;
                var e = Math.pow(10.0, rawVal / 10.0);
                sumaE += e;
                row.push(formatEnergyExcel(e).replace(/<[^>]*>/g, ''));
            } else {
                row.push('');
            }
        }

        var laeq = (hasAny && sumaE > 0) ? (10.0 * Math.log10((1.0 / 30.0) * sumaE)) : 0;
        var tmpe = (laeq > 0) ? (8.0 / Math.pow(2.0, (laeq - 85.0) / 3.0)) : null;
        var dosis = (tmpe !== null && tmpe > 0) ? (tpe / tmpe) : null;

        row.push(hasAny ? Math.round(sumaE).toString() : '');
        row.push(hasAny ? npsMax.toFixed(2).replace('.', ',') : '');
        row.push(hasAny ? laeq.toFixed(2).replace('.', ',') : '');
        row.push(tmpe !== null ? tmpe.toFixed(2).replace('.', ',') : '');
        row.push(dosis !== null ? dosis.toFixed(3).replace('.', ',') : '');

        csv += row.join(sep) + '\r\n';
    });

    var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    var link = document.createElement('a');
    var url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', 'calculos_ruido_ocupacional_nts002.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
