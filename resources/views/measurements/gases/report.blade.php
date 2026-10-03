@extends('layouts.app')

@section('title', 'Informe Técnico — Planilla de Monitoreo de Gases Ocupacionales & Ambientales — Metric v2')

@push('styles')
    @metricStyle('gases')
    <style>
        .ilum-report-container {
            width: 100%;
            padding: 20px 0 50px 0;
            background-color: #f1f5f9;
            min-height: calc(100vh - 70px);
        }

        .ilum-sheet-wrapper {
            width: 100%;
            max-width: 1300px;
            margin: 0 auto;
            padding: 0 12px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .ilum-sheet-toolbar {
            width: 100%;
            max-width: 279.4mm;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(15, 28, 46, 0.04);
            flex-wrap: wrap;
            box-sizing: border-box;
        }

        .ilum-toolbar-left,
        .ilum-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .ilum-badge-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 9px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 700;
            background: #e0f2fe;
            color: #0369a1;
            border: 1px solid #bae6fd;
            letter-spacing: 0.3px;
        }

        .header-auto-save-status {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            transition: all 0.25s ease;
        }

        .header-auto-save-status.saving {
            background-color: #fefce8;
            color: #ca8a04;
            border-color: #fef08a;
        }

        .header-auto-save-status.error {
            background-color: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        .btn-toolbar-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            border: 1px solid transparent;
        }

        .btn-toolbar-subtle {
            background: #ffffff;
            color: #475569;
            border-color: #cbd5e1;
        }

        .btn-toolbar-subtle:hover {
            background: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-toolbar-word {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border-color: #075985;
            box-shadow: 0 2px 6px rgba(3, 105, 161, 0.25);
        }

        .btn-toolbar-word:hover {
            background: linear-gradient(135deg, #10b9df 0%, #0284c7 100%);
            transform: translateY(-1px);
        }

        /* Contenedor Hoja Carta Horizontal */
        .ilum-sheet-card {
            background: #ffffff;
            border: 1.5px solid #000000;
            box-shadow: 0 10px 30px -5px rgba(15, 28, 46, 0.09), 0 2px 6px -1px rgba(15, 28, 46, 0.04);
            width: 279.4mm;
            max-width: 100%;
            min-height: 215.9mm;
            box-sizing: border-box;
            padding: 6mm 7mm;
            border-radius: 2px;
            margin: 0 auto 30px auto;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #000000;
            overflow-x: auto;
        }

        .ilum-live-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            color: inherit;
            padding: 1px 3px;
            box-sizing: border-box;
            border-radius: 2px;
            transition: all 0.15s ease;
        }

        .ilum-live-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .ilum-live-input:focus {
            outline: none;
            border-color: #10b9df;
            background: #ffffff;
            box-shadow: 0 0 0 1.5px rgba(16, 185, 223, 0.2);
        }

        /* Tabla Principal Oficial */
        .report-table-master {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.8pt;
            margin-top: 6px;
        }

        .report-table-master th,
        .report-table-master td {
            border: 1px solid #000000;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .report-table-master th {
            background-color: #f1f5f9;
            font-weight: bold;
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .ilum-sheet-toolbar,
            nav,
            header,
            footer,
            .btn-toolbar-action {
                display: none !important;
            }
            .ilum-report-container {
                padding: 0 !important;
                background: #ffffff !important;
            }
            .ilum-sheet-card {
                border: 1px solid #000000 !important;
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                padding: 4mm !important;
                page-break-after: always;
            }
            @page {
                size: letter landscape;
                margin: 6mm;
            }
        }
    </style>
@endpush

@section('content')
<div class="ilum-report-container">
    <div class="ilum-sheet-wrapper">

        <!-- Barra de Herramientas Superior -->
        <div class="ilum-sheet-toolbar">
            <div class="ilum-toolbar-left">
                <a href="{{ route('modules.gases', $module->id) }}" class="btn-toolbar-action btn-toolbar-subtle">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    <span>Volver al Módulo</span>
                </a>
                <span class="ilum-badge-tag">
                    <span>MÓDULO: GASES</span>
                </span>
                <span id="reportAutoSaveStatus" class="header-auto-save-status">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                    <span>Guardado</span>
                </span>
            </div>

            <div class="ilum-toolbar-right">
                <button type="button" class="btn-toolbar-action btn-toolbar-subtle" onclick="window.print()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect width="12" height="8" x="6" y="14"></rect>
                    </svg>
                    <span>Imprimir / PDF</span>
                </button>
                <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="saveReportDataAsync()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                        <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        <polyline points="7 3 7 8 15 8"></polyline>
                    </svg>
                    <span>Guardar Informe</span>
                </button>
            </div>
        </div>

        <!-- Hoja Técnica Oficial (Landscape) -->
        <div class="ilum-sheet-card" id="gasesPrintSheet">
            <!-- Encabezado con Logo y Datos de la Empresa -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 6px; border: 1.5px solid #000;">
                <tr>
                    <td style="width: 140px; text-align: center; padding: 6px; border-right: 1.5px solid #000;">
                        <div style="font-weight: 900; font-size: 13pt; color: #0f172a; letter-spacing: 0.5px;">PACHABOL</div>
                        <div style="font-size: 6pt; color: #64748b; font-weight: bold;">SERVICIOS AMBIENTALES</div>
                    </td>
                    <td style="text-align: center; padding: 6px; border-right: 1.5px solid #000;">
                        <div style="font-weight: 900; font-size: 10pt; text-transform: uppercase;">
                            PLANILLA DE MEDICIÓN Y EVALUACIÓN DE NIVELES DE GASES
                        </div>
                        <div style="font-size: 7pt; color: #334155; margin-top: 2px;">
                            MONITOREO DE HIGIENE INDUSTRIAL Y CALIDAD DE AIRE
                        </div>
                    </td>
                    <td style="width: 120px; font-size: 6.5pt; padding: 4px 6px; text-align: left;">
                        <div><strong>CÓDIGO:</strong> FOR-MON-GAS-01</div>
                        <div><strong>VERSIÓN:</strong> 02</div>
                        <div><strong>FECHA:</strong> {{ $startDateFormatted }}</div>
                    </td>
                </tr>
            </table>

            <!-- Metadatos Técnicos del Monitoreo y Equipo -->
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 8px; font-size: 7.5pt; border: 1px solid #000;">
                <tr>
                    <th style="width: 14%; background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px;">INSTALACIÓN:</th>
                    <td style="width: 36%; border: 1px solid #000; padding: 2px 4px;">
                        <input type="text" id="report_installation_name" class="ilum-live-input" value="{{ $installationName }}" onchange="saveReportDataAsync()">
                    </td>
                    <th style="width: 14%; background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px;">EQUIPO:</th>
                    <td style="width: 36%; border: 1px solid #000; padding: 2px 4px;">
                        <input type="text" id="report_equipment_name" class="ilum-live-input" value="{{ $equipmentName }}" onchange="saveReportDataAsync()">
                    </td>
                </tr>
                <tr>
                    <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px;">FECHAS:</th>
                    <td style="border: 1px solid #000; padding: 2px 4px;">
                        {{ $startDateFormatted }} al {{ $endDateFormatted }}
                    </td>
                    <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px;">MARCA / MODELO:</th>
                    <td style="border: 1px solid #000; padding: 2px 4px;">
                        <input type="text" id="report_equipment_brand_model" class="ilum-live-input" value="{{ $equipmentBrand }} - {{ $equipmentModel }}" onchange="saveReportDataAsync()">
                    </td>
                </tr>
                <tr>
                    <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px;">TIPO MONITOREO:</th>
                    <td style="border: 1px solid #000; padding: 2px 4px;">
                        <input type="text" id="report_monitoring_type" class="ilum-live-input" value="{{ $monitoringType }}" onchange="saveReportDataAsync()">
                    </td>
                    <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px;">SERIE:</th>
                    <td style="border: 1px solid #000; padding: 2px 4px;">
                        <input type="text" id="report_equipment_serial" class="ilum-live-input" value="{{ $equipmentSerial }}" onchange="saveReportDataAsync()">
                    </td>
                </tr>
            </table>

            <!-- Tabla Maestra de Mediciones -->
            <table class="report-table-master">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 25px;">N°</th>
                        <th rowspan="2" style="width: 65px;">FECHA / HORA</th>
                        <th rowspan="2" style="width: 110px;">ÁREA / PUESTO</th>
                        <th rowspan="2" style="width: 90px;">PUNTO DE MEDICIÓN</th>
                        <th colspan="3" style="background: #bae6fd;">CONDICIONES AMBIENTALES</th>
                        <th colspan="5" style="background: #e0f2fe;">GASES EVALUADOS & LECTURAS</th>
                        <th rowspan="2" style="width: 110px;">OBSERVACIONES</th>
                    </tr>
                    <tr>
                        <th style="width: 45px; background: #f0f9ff;">T (°C)</th>
                        <th style="width: 50px; background: #f0f9ff;">P (mmHg)</th>
                        <th style="width: 55px; background: #f0f9ff;">V. Aire (Km/h)</th>
                        <th style="width: 80px; background: #f0f9ff;">GAS</th>
                        <th style="width: 38px; background: #f0f9ff;">MED. 1</th>
                        <th style="width: 38px; background: #f0f9ff;">MED. 2</th>
                        <th style="width: 38px; background: #f0f9ff;">MED. 3</th>
                        <th style="width: 45px; background: #f0f9ff;">PROM.</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($measurementsList as $idx => $m)
                        @php
                            $gasesReadings = is_array($m->gases_readings) ? $m->gases_readings : (json_decode($m->gases_readings, true) ?: []);
                            $gasesKeys = array_keys($gasesReadings);
                            $gasesCount = max(1, count($gasesKeys));
                            $firstGas = $gasesKeys[0] ?? null;
                            $firstGData = $firstGas ? ($gasesReadings[$firstGas] ?? []) : [];
                            $dateStr = $m->measurement_date ? $m->measurement_date->format('d/m/Y') : '—';
                        @endphp
                        <tr>
                            <td rowspan="{{ $gasesCount }}">{{ $m->point_number ?: str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }}</td>
                            <td rowspan="{{ $gasesCount }}">{{ $dateStr }}<br><small>{{ $m->measurement_time ?: '—' }}</small></td>
                            <td rowspan="{{ $gasesCount }}" style="text-align: left;">
                                <strong>{{ $m->area }}</strong><br>
                                <span style="color: #475569;">{{ $m->workstation }}</span>
                            </td>
                            <td rowspan="{{ $gasesCount }}" style="text-align: left;">{{ $m->measurement_point }}</td>
                            <td rowspan="{{ $gasesCount }}">{{ $m->temperatura !== null ? $m->temperatura . '°C' : '—' }}</td>
                            <td rowspan="{{ $gasesCount }}">{{ $m->presion_atm !== null ? $m->presion_atm : '—' }}</td>
                            <td rowspan="{{ $gasesCount }}">{{ $m->vel_aire !== null ? $m->vel_aire : '—' }}</td>

                            <!-- Primer Gas -->
                            @if($firstGas)
                                <td><strong>{{ $firstGData['formula'] ?? strtoupper($firstGas) }}</strong> ({{ $firstGData['unit'] ?? 'ppm' }})</td>
                                <td>{{ $firstGData['m1'] ?? ($firstGData['med1'] ?? '—') }}</td>
                                <td>{{ $firstGData['m2'] ?? ($firstGData['med2'] ?? '—') }}</td>
                                <td>{{ $firstGData['m3'] ?? ($firstGData['med3'] ?? '—') }}</td>
                                <td><strong>{{ $firstGData['prom'] ?? '—' }}</strong></td>
                            @else
                                <td colspan="5" style="color: #94a3b8; font-style: italic;">Sin lecturas analíticas</td>
                            @endif

                            <td rowspan="{{ $gasesCount }}" style="text-align: left; font-size: 6.5pt;">{{ $m->observations ?: 'Sin observaciones' }}</td>
                        </tr>

                        <!-- Filas adicionales para el resto de gases del mismo punto -->
                        @for($gIdx = 1; $gIdx < $gasesCount; $gIdx++)
                            @php
                                $gKey = $gasesKeys[$gIdx];
                                $gData = $gasesReadings[$gKey] ?? [];
                            @endphp
                            <tr>
                                <td><strong>{{ $gData['formula'] ?? strtoupper($gKey) }}</strong> ({{ $gData['unit'] ?? 'ppm' }})</td>
                                <td>{{ $gData['m1'] ?? ($gData['med1'] ?? '—') }}</td>
                                <td>{{ $gData['m2'] ?? ($gData['med2'] ?? '—') }}</td>
                                <td>{{ $gData['m3'] ?? ($gData['med3'] ?? '—') }}</td>
                                <td><strong>{{ $gData['prom'] ?? '—' }}</strong></td>
                            </tr>
                        @endfor
                    @empty
                        <tr>
                            <td colspan="12" style="padding: 20px; text-align: center; color: #94a3b8;">No se han registrado puntos de medición de gases en este módulo.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Firmas y Responsables -->
            <table style="width: 100%; margin-top: 18px; font-size: 7pt; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; text-align: center; padding: 20px 40px 0 40px;">
                        <div style="border-top: 1px solid #000; padding-top: 4px;">
                            <strong>RESPONSABLE TÉCNICO DE CAMPO</strong><br>
                            <span>{{ $registeredByHeader }}</span>
                        </div>
                    </td>
                    <td style="width: 50%; text-align: center; padding: 20px 40px 0 40px;">
                        <div style="border-top: 1px solid #000; padding-top: 4px;">
                            <strong>SUPERVISIÓN Y CONTROL DE CALIDAD</strong><br>
                            <span>PACHABOL MEDIO AMBIENTE & SEGURIDAD</span>
                        </div>
                    </td>
                </tr>
            </table>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function saveReportDataAsync() {
        const badge = document.getElementById('reportAutoSaveStatus');
        if (badge) {
            badge.className = 'header-auto-save-status saving';
            badge.innerHTML = '<span>Guardando...</span>';
        }

        const payload = {
            _token: "{{ csrf_token() }}",
            installation_name: document.getElementById('report_installation_name')?.value || '',
            monitoring_type: document.getElementById('report_monitoring_type')?.value || '',
            equipment_name: document.getElementById('report_equipment_name')?.value || '',
            equipment_serial: document.getElementById('report_equipment_serial')?.value || '',
        };

        try {
            const resp = await fetch("{{ route('modules.gases.report.save', $module->id) }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (badge) {
                badge.className = 'header-auto-save-status';
                badge.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Guardado</span>';
            }
        } catch (e) {
            if (badge) {
                badge.className = 'header-auto-save-status error';
                badge.innerHTML = '<span>Error al guardar</span>';
            }
        }
    }
</script>
@endpush
