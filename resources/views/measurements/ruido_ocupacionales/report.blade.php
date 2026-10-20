@extends('layouts.app')
@section('title','Informe Ruido Ocupacional — NTS-002/17')
@push('styles')
<style>
.r-c{width:100%;padding:20px 0 50px;background:#f1f5f9;min-height:calc(100vh - 70px)}
.r-w{width:100%;max-width:297mm;margin:0 auto;padding:0 12px}
.r-p{width:100%;max-width:297mm;background:#fff;padding:18mm 15mm;box-shadow:0 2px 8px rgba(0,0,0,0.04);margin-bottom:20px}
.r-tb{width:100%;max-width:297mm;display:flex;justify-content:space-between;gap:12px;margin-bottom:14px;background:#fff;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;flex-wrap:wrap}
.btn-rpt{display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;border:none;background:#fff;color:#475569}
.btn-rpt:hover{background:#f8fafc}
.btn-rpt-p{background:#0284c7;color:#fff}
.btn-rpt-p:hover{background:#0369a1}
.rt{font-size:13px;font-weight:800;text-align:center;color:#0f172a;margin:10px 0 6px;padding-bottom:6px;border-bottom:2px solid #0284c7;line-height:1.4}
.rs{font-size:10px;font-weight:600;text-align:center;color:#475569;margin-bottom:10px}
.ht{width:100%;border-collapse:collapse;font-size:10px;margin-bottom:10px}
.ht td{padding:4px 8px;border:0.5px solid #cbd5e1}
.ht td:nth-child(even){background:#f8fafc}
.ht-l{font-weight:700;width:28%}
.dt{width:100%;border-collapse:collapse;font-size:8.5px;margin-bottom:10px}
.dt th{background:#e0f2fe;color:#0284c7;padding:5px 4px;text-align:left;font-weight:700;border:0.5px solid #0284c7;line-height:1.3}
.dt td{padding:5px 4px;border:0.5px solid #cbd5e1;line-height:1.3}
.dt .c{text-align:center}
.ok{background:#dcfce7;color:#166534;padding:1px 3px;border-radius:2px;font-weight:700}
.no{background:#fee2e2;color:#991b1b;padding:1px 3px;border-radius:2px;font-weight:700}
.sig{margin-top:18px;display:grid;grid-template-columns:1fr 1fr;gap:15px;font-size:9px}
.sigb{border-top:1px solid #000;height:36px;text-align:center;padding-top:3px}
.nota-ref{margin-top:12px;padding:8px;background:#f8fafc;border-left:3px solid #0284c7;font-size:8px;color:#475569}
@media print{.r-tb{display:none!important}.r-c{padding:0;background:#fff}.r-p{box-shadow:none;padding:12mm 10mm;margin:0}@page{size:landscape;margin:8mm}}
</style>
@endpush
@section('content')
<div class="r-c">
<div class="r-w">
<div class="r-tb">
<a href="{{ route('modules.ruido_ocupacional', $module->id) }}" class="btn-rpt">← Volver</a>
<button class="btn-rpt btn-rpt-p" onclick="window.print()">🖨 Imprimir Informe</button>
</div>
<div class="r-p">
<div class="rt">MEDICIÓN Y EVALUACIÓN PARA PERIODOS O ESTUDIOS<br>MENORES O IGUALES A 8 HORAS O UN TURNO DE TRABAJO</div>
<div class="rs">NTS-002/17 — Evaluación de Ruido Ocupacional</div>
<table class="ht">
<tr><td class="ht-l">EMPRESA / PROYECTO:</td><td>{{ $installationName }}</td><td class="ht-l">EQUIPO:</td><td>{{ $equipmentName }}</td></tr>
<tr><td class="ht-l">FECHA DE INICIO:</td><td>{{ $startDateFormatted }}</td><td class="ht-l">MARCA:</td><td>{{ $equipmentBrand }}</td></tr>
<tr><td class="ht-l">FECHA DE FINALIZACIÓN:</td><td>{{ $endDateFormatted }}</td><td class="ht-l">MODELO:</td><td>{{ $equipmentModel }}</td></tr>
<tr><td class="ht-l">TIPO DE MONITOREO:</td><td>OCUPACIONAL</td><td class="ht-l">SERIE:</td><td>{{ $equipmentSerial }}</td></tr>
</table>

<table class="dt">
<thead>
<tr>
<th rowspan="2" style="width:3%;text-align:center;">N°</th>
<th rowspan="2" style="width:10%;">Área de Trabajo</th>
<th rowspan="2" style="width:8%;">Punto de medición</th>
<th rowspan="2" style="width:7%;">Tipo de ruido</th>
<th rowspan="2" style="width:5%;text-align:center;">TPE (Hrs)</th>
<th colspan="2" style="text-align:center;">Datos del equipo</th>
<th rowspan="2" style="width:6%;text-align:center;">NPS (max) dB(A)</th>
<th rowspan="2" style="width:7%;text-align:center;">LAeq,T dB(A)</th>
<th rowspan="2" style="width:6%;text-align:center;">TMPE (Hrs)</th>
<th rowspan="2" style="width:6%;text-align:center;">Dosis</th>
<th rowspan="2" style="width:8%;text-align:center;">¿TPE supera TMPE?</th>
<th rowspan="2" style="width:8%;text-align:center;">¿Dosimetría?</th>
<th rowspan="2" style="width:11%;">Observaciones</th>
</tr>
<tr>
<th style="width:5%;text-align:center;">Pond.</th>
<th style="width:5%;text-align:center;">Resp.</th>
</tr>
</thead>
<tbody>
@forelse($measurements as $m)
<tr>
<td class="c">{{ $loop->iteration }}</td>
<td>{{ $m['area'] }}</td>
<td>{{ $m['punto_medicion'] }}</td>
<td>{{ $m['tipo_ruido'] }}</td>
<td class="c">{{ number_format($m['tiempo_expos_h'], 2) }}</td>
<td class="c">{{ $m['ponderacion'] }}</td>
<td class="c">{{ $m['respuesta'] }}</td>
<td class="c"><b>{{ $m['nps_max'] !== null ? number_format($m['nps_max'], 2) : '' }}</b></td>
<td class="c" style="font-weight:700;color:{{ ($m['leq_t_db'] !== null && $m['leq_t_db'] > 85) ? '#991b1b' : '#166534' }};">{{ $m['leq_t_db'] !== null ? number_format($m['leq_t_db'], 2) : '' }}</td>
<td class="c">{{ $m['tmpe'] !== null ? number_format($m['tmpe'], 2) : '' }}</td>
<td class="c" style="font-weight:700;color:{{ ($m['dosis'] !== null && $m['dosis'] > 1) ? '#991b1b' : '#166534' }};">{{ $m['dosis'] !== null ? number_format($m['dosis'], 4) : '' }}</td>
<td class="c"><span class="{{ $m['tpe_supera_tmpe'] ? 'no' : 'ok' }}">{{ $m['tpe_supera_tmpe'] ? 'Si' : 'No' }}</span></td>
<td class="c"><span class="{{ $m['requiere_dosimetria'] ? 'no' : 'ok' }}">{{ $m['requiere_dosimetria'] ? 'Si' : 'No' }}</span></td>
<td style="font-size:8px;">{{ $m['observations'] ?? '' }}</td>
</tr>
@empty
<tr><td colspan="14" class="c" style="color:#94a3b8;padding:15px;">Sin mediciones registradas</td></tr>
@endforelse
</tbody>
</table>

<div class="nota-ref">
<b>Referencia Normativa NTS-002/17:</b><br>
LAeq,T = 10·log₁₀((1/N)·Σ10^(NPSi/10)) &nbsp;|&nbsp;
TMPE = 8 / 2^((LAeq,T − 85) / 3) &nbsp;|&nbsp;
Dosis = TPE / TMPE &nbsp;|&nbsp;
Cumple si Dosis ≤ 1.0 (TPE no supera TMPE). Base: 85 dB(A) para 8 horas, tasa de intercambio 3 dB.
</div>

<div class="sig">
<div class="sigb"><b style="font-size:10px;">Técnico Responsable</b><div style="font-size:8px;color:#64748b;">{{ $registeredBy }}</div></div>
<div class="sigb"><b style="font-size:10px;">Autoridad Competente</b></div>
</div>
</div>
</div>
</div>
@endsection

