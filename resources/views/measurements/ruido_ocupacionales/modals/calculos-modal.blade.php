<div class="modal-backdrop-custom" id="calculosModalRuido" role="dialog" aria-modal="true">
    <div class="modal-dialog-ruido" style="max-width:1200px;width:96%;"><div class="modal-header-custom">
            <div><h2 id="calculosModalTitle" style="font-family:'Outfit',sans-serif;font-size:18px;font-weight:800;color:var(--ink);margin:0;">Cálculos y Evaluación de Ruido Ocupacional</h2>
                <p id="calculosModalSubtitle" style="font-size:12.5px;color:#64748b;margin:0;">Análisis técnico NTS-002/17</p></div>
            <button type="button" class="btn-close-modal" onclick="closeCalculosModal()">✕</button>
        </div>
        <div class="modal-body-custom" style="padding:20px;max-height:72vh;overflow-y:auto;">
            {{-- Vista de lista: Todas las mediciones --}}
            <div id="calculosListContent" style="display:none;"></div>

            {{-- Vista detalle: Un solo punto de medición --}}
            <div id="calculosSingleContent" style="display:grid;gap:15px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                    <div style="padding:15px;background:#f8fafc;border-radius:8px;border-left:4px solid #0284c7;">
                        <div style="font-size:11px;font-weight:700;color:#64748b;">Área</div>
                        <div id="calc_area" style="font-size:14px;font-weight:800;color:#0284c7;">—</div>
                    </div>
                    <div style="padding:15px;background:#f8fafc;border-radius:8px;border-left:4px solid #0284c7;">
                        <div style="font-size:11px;font-weight:700;color:#64748b;">Punto</div>
                        <div id="calc_punto" style="font-size:14px;font-weight:800;color:#0284c7;">—</div>
                    </div>
                </div>
                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                    <thead><tr style="background:#e0f2fe;border-bottom:2px solid #0284c7;">
                        <th style="padding:10px;text-align:left;font-weight:700;color:#0284c7;">Métrica</th>
                        <th style="padding:10px;text-align:center;font-weight:700;color:#0284c7;">Fórmula NTS-002/17</th>
                        <th style="padding:10px;text-align:right;font-weight:700;color:#0284c7;">Valor</th>
                    </tr></thead>
                    <tbody><tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:12px;">Σ Energía Acústica</td>
                        <td style="padding:12px;text-align:center;font-family:monospace;color:#64748b;">Σ 10^(NPSi/10)</td>
                        <td id="calc_nps_formula" style="padding:12px;text-align:right;font-weight:700;color:#0284c7;">—</td>
                    </tr><tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:12px;">LAeq,T (dB)</td>
                        <td style="padding:12px;text-align:center;font-family:monospace;color:#64748b;">10·log₁₀((1/N)·Σ)</td>
                        <td id="calc_leq" style="padding:12px;text-align:right;font-weight:700;color:#059669;">—</td>
                    </tr><tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:12px;">TMPE (horas)</td>
                        <td style="padding:12px;text-align:center;font-family:monospace;color:#64748b;">8/2^((LAeq−85)/3)</td>
                        <td id="calc_tmpe" style="padding:12px;text-align:right;font-weight:700;color:#334155;">—</td>
                    </tr><tr style="border-bottom:1px solid #e2e8f0;">
                        <td style="padding:12px;">DOSIS</td>
                        <td style="padding:12px;text-align:center;font-family:monospace;color:#64748b;">TPE / TMPE</td>
                        <td id="calc_dosis" style="padding:12px;text-align:right;font-weight:700;color:#dc2626;">—</td>
                    </tr><tr>
                        <td style="padding:12px;font-weight:700;">ESTADO</td>
                        <td style="padding:12px;"></td>
                        <td id="calc_status" style="padding:12px;text-align:right;font-weight:700;">—</td>
                    </tr></tbody>
                </table>
                <div style="padding:15px;background:#fafafa;border-radius:8px;border:1px solid #e2e8f0;">
                    <div style="font-size:12px;font-weight:700;color:#334155;margin-bottom:10px;">Mediciones NPS y Transformación Energética 10^(NPSi/10)</div>
                    <div id="calc_mediciones" style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;font-size:11px;"></div>
                </div>
            </div>
        </div>
        <div class="modal-footer-custom">
            <button type="button" class="btn-primary-hero-action" onclick="closeCalculosModal()" style="padding:8px 18px;font-size:13px;">Cerrar</button>
        </div>
    </div>
</div>
<script>
function openCalculosModal(m){
    var area=m.area||'—', punto=m.punto_medicion||'—', med=m.mediciones_db||[], tpe=parseFloat(m.tiempo_expos_h)||8;
    // NTS-002/17: Energy transformation and calculations
    var valid=med.map(function(v){return parseFloat(v)}).filter(function(v){return !isNaN(v)&&v>0});
    var n=valid.length, sumaE=0, npsMax=0;
    valid.forEach(function(v){ sumaE+=Math.pow(10,v/10.0); if(v>npsMax) npsMax=v; });
    var leq=n>0?(10*Math.log10(sumaE/n)):0;
    var tmpe=leq>0?(8/Math.pow(2,(leq-85)/3)):null;
    var dosis=(tmpe&&tmpe>0)?(tpe/tmpe):null;
    var cumple=dosis!==null&&dosis<=1;
    var stat=!cumple?'<span style="color:#dc2626;">⚠ NO CUMPLE (Dosis > 1)</span>':'<span style="color:#059669;">✓ CUMPLE (Dosis ≤ 1)</span>';

    document.getElementById('calc_area').textContent=area;
    document.getElementById('calc_punto').textContent=punto;
    document.getElementById('calc_nps_formula').textContent=n>0?sumaE.toExponential(4):'—';
    document.getElementById('calc_leq').textContent=n>0?leq.toFixed(2)+' dB':'—';
    document.getElementById('calc_tmpe').textContent=tmpe!==null?tmpe.toFixed(2)+' h':'—';
    document.getElementById('calc_dosis').textContent=dosis!==null?dosis.toFixed(4):'—';
    document.getElementById('calc_status').innerHTML=stat;

    var html=med.map(function(v,i){
        var nps=parseFloat(v), energy=(nps>0)?Math.pow(10,nps/10.0):0;
        return '<div style="padding:6px;background:white;border-radius:4px;text-align:center;border:1px solid #e2e8f0;">'
            +'<span style="color:#0284c7;font-weight:700;font-size:10px;">NPS'+(i+1)+'</span>'
            +'<div style="font-size:13px;font-weight:800;">'+v+'</div>'
            +'<div style="font-size:9px;color:#64748b;margin-top:2px;">'+energy.toExponential(2)+'</div></div>';
    }).join('');
    document.getElementById('calc_mediciones').innerHTML=html||'Sin datos';

    // Show single view, hide list view
    var sv=document.getElementById('calculosSingleContent'); if(sv) sv.style.display='';
    var lv=document.getElementById('calculosListContent'); if(lv) lv.style.display='none';
    var ti=document.getElementById('calculosModalTitle'); if(ti) ti.textContent='Cálculos — '+area+' / '+punto;
    var su=document.getElementById('calculosModalSubtitle'); if(su) su.textContent='NTS-002/17 · Detalle de medición';
    document.getElementById('calculosModalRuido').style.display='flex';
}
function closeCalculosModal(){document.getElementById('calculosModalRuido').style.display='none'}
</script>
