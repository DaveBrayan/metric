@extends('layouts.app')

@section('title', 'Informe Técnico — Planilla de Medición y Evaluación de Niveles de Iluminación — Metric v2')

@push('styles')
    @metricStyle('iluminacion')
    <style>
        .ilum-report-container {
            width: 100%;
            padding: 20px 0 50px 0;
            background-color: #f1f5f9;
            min-height: calc(100vh - 70px);
        }

        .ilum-sheet-wrapper {
            width: 100%;
            max-width: 1240px;
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
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
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
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
            color: #ffffff;
            border-color: #1e3a8a;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.25);
        }

        .btn-toolbar-word:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.35);
        }

        /* Contenedor de Hoja Carta Horizontal (Exact Letter Landscape Dimensions: 279.4mm x 215.9mm) */
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

        /* Inputs editables dentro de la hoja */
        .ilum-live-input {
            width: 100%;
            border: 1px solid transparent;
            border-radius: 2px;
            padding: 1px 3px;
            font-family: Arial, Helvetica, sans-serif;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            background: transparent;
            outline: none;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }

        .ilum-live-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .ilum-live-input:focus {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        /* Tabla Principal Oficial Adaptada Proporcionalmente a Hoja Carta */
        .ilum-report-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ $maxReadingsCount > 16 ? '6.2pt' : ($maxReadingsCount > 10 ? '6.8pt' : '7.2pt') }};
            color: #000000;
            table-layout: fixed;
        }

        .ilum-report-table th,
        .ilum-report-table td {
            border: 1px solid #000000;
            padding: {{ $maxReadingsCount > 16 ? '3.5px 1.5px' : '4px 2.5px' }};
            vertical-align: middle;
            box-sizing: border-box;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: clip;
        }

        .ilum-report-table th {
            background-color: #deebf7;
            color: #000000;
            font-weight: bold;
            text-align: center;
            font-size: {{ $maxReadingsCount > 16 ? '5.8pt' : ($maxReadingsCount > 10 ? '6.3pt' : '6.8pt') }};
            line-height: 1.25;
            word-break: normal !important;
            overflow-wrap: normal !important;
            word-wrap: normal !important;
            white-space: normal !important;
            hyphens: none !important;
        }

        .ilum-report-table tbody tr:hover td {
            background-color: #f8fafc;
        }

        .table-action-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 9999;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
            animation: slideInUp 0.3s ease;
        }

        .table-action-toast.success {
            background-color: #10b981;
            color: #ffffff;
        }

        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .ilum-report-container {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .ilum-sheet-toolbar,
            .top-navbar,
            .sidebar-custom,
            nav,
            footer,
            header {
                display: none !important;
            }
            .ilum-sheet-wrapper {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .ilum-sheet-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                overflow: visible !important;
            }
            @page {
                size: letter landscape;
                margin: 5mm 6mm;
            }
        }
    </style>
@endpush

@section('content')
@php
    // Cálculo proporcional exacto de anchos de columnas para que todo encaje en 100% del ancho
    // Total base de columnas no-M = 13 columnas (N°, Área, Puesto, Punto, Actividad, Horario, Tipo, Req, Min, Max, Promedio, Cumple, Obs)
    // Se calcula el porcentaje para cada lectura M (hasta 25 lecturas) de forma compacta
    $mCount = max(1, $maxReadingsCount);
    
    // Porcentajes base adaptados para que sumen 100%
    if ($mCount >= 20) {
        $wEachM = 1.55;
        $wNum = 2.0; $wArea = 6.2; $wPuesto = 6.2; $wPunto = 5.8; $wDesc = 7.0;
        $wHora = 4.5; $wTipo = 4.8; $wReq = 5.2;
        $wMin = 2.7; $wMax = 2.7; $wProm = 3.6;
        $wCumple = 4.4;
        $wObs = max(4.0, round(100 - ($wNum + $wArea + $wPuesto + $wPunto + $wDesc + $wHora + $wTipo + $wReq + ($wEachM * $mCount) + $wMin + $wMax + $wProm + $wCumple), 2));
    } elseif ($mCount >= 12) {
        $wEachM = 2.1;
        $wNum = 2.2; $wArea = 7.2; $wPuesto = 7.2; $wPunto = 6.8; $wDesc = 8.0;
        $wHora = 5.0; $wTipo = 5.5; $wReq = 6.0;
        $wMin = 3.0; $wMax = 3.0; $wProm = 4.0;
        $wCumple = 4.8;
        $wObs = max(5.0, round(100 - ($wNum + $wArea + $wPuesto + $wPunto + $wDesc + $wHora + $wTipo + $wReq + ($wEachM * $mCount) + $wMin + $wMax + $wProm + $wCumple), 2));
    } elseif ($mCount >= 7) {
        $wEachM = 2.4;
        $wNum = 2.2; $wArea = 7.8; $wPuesto = 7.8; $wPunto = 7.2; $wDesc = 8.8;
        $wHora = 5.2; $wTipo = 5.8; $wReq = 6.2;
        $wMin = 3.2; $wMax = 3.2; $wProm = 4.2;
        $wCumple = 5.0;
        $wObs = max(6.0, round(100 - ($wNum + $wArea + $wPuesto + $wPunto + $wDesc + $wHora + $wTipo + $wReq + ($wEachM * $mCount) + $wMin + $wMax + $wProm + $wCumple), 2));
    } else {
        // Para 1..6 mediciones: M no es tan grande/ancho, y las demás columnas tienen espacio holgado
        $wEachM = 2.8;
        $wNum = 2.5; $wArea = 8.5; $wPuesto = 8.5; $wPunto = 7.8; $wDesc = 9.5;
        $wHora = 5.8; $wTipo = 6.2; $wReq = 6.8;
        $wMin = 3.4; $wMax = 3.4; $wProm = 4.6;
        $wCumple = 5.4;
        $wObs = max(8.0, round(100 - ($wNum + $wArea + $wPuesto + $wPunto + $wDesc + $wHora + $wTipo + $wReq + ($wEachM * $mCount) + $wMin + $wMax + $wProm + $wCumple), 2));
    }
@endphp

<div class="ilum-report-container">
    <div class="ilum-sheet-wrapper">

        <!-- Barra de Navegación y Herramientas -->
        <div class="ilum-sheet-toolbar">
            <div class="ilum-toolbar-left">
                <a href="{{ route('modules.illumination', $module->id) }}" class="btn-toolbar-action btn-toolbar-subtle"
                    title="Volver a la vista de monitoreo de iluminación">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    <span>Volver al Monitoreo</span>
                </a>

                <span id="autoSaveBadge" class="header-auto-save-status">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>Guardado</span>
                </span>
            </div>

            <div class="ilum-toolbar-right">
                <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadIluminacionWordDoc()"
                    title="Descargar documento compatible con Microsoft Word (.doc) en formato horizontal y tamaño carta">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <span>Descargar Word (.doc)</span>
                </button>
            </div>
        </div>

        <!-- Documento Oficial (Hoja Carta Horizontal - 279.4mm x 215.9mm - Arial) -->
        <div class="ilum-sheet-card" id="iluminacionReportSheet">

            <!-- 1. Encabezado Azul Principal -->
            <div style="background-color: #2c73b8; color: #ffffff; text-align: center; padding: 5px 8px; font-weight: bold; font-size: 9.5pt; text-transform: uppercase; border: 1.5px solid #000000; letter-spacing: 0.3px;">
                PLANILLA DE MEDICIÓN Y EVALUACIÓN DE NIVELES DE ILUMINACIÓN
            </div>

            <!-- 2. Tabla de Información Técnica y Equipamiento -->
            <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-top: -1.5px; margin-bottom: 10px; font-family: Arial, Helvetica, sans-serif; font-size: 7.5pt;">
                <tbody>
                    <tr>
                        <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">INSTALACIÓN:</td>
                        <td style="width: 35%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_instalacion" class="ilum-live-input" value="{{ $installationName }}"
                                placeholder="Razón social o instalación..." oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">EQUIPO:</td>
                        <td style="width: 37%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_equipo" class="ilum-live-input" value="{{ $equipmentName }}"
                                placeholder="Equipo de medición..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">FECHA DE INICIO:</td>
                        <td style="width: 35%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_fecha_inicio" class="ilum-live-input" value="{{ $startDateFormatted }}"
                                placeholder="dd/mm/aaaa" oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">MARCA:</td>
                        <td style="width: 37%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_marca" class="ilum-live-input" value="{{ $equipmentBrand }}"
                                placeholder="Marca del equipo..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">FECHA DE FINALIZACIÓN:</td>
                        <td style="width: 35%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_fecha_fin" class="ilum-live-input" value="{{ $endDateFormatted }}"
                                placeholder="dd/mm/aaaa" oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">MODELO:</td>
                        <td style="width: 37%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_modelo" class="ilum-live-input" value="{{ $equipmentModel }}"
                                placeholder="Modelo del equipo..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">TIPO DE MONITOREO:</td>
                        <td style="width: 35%; border: 1px solid #000000; padding: 2px 5px; background: #ffffff;">
                            @php
                                $isRutinario = str_contains(strtoupper($monitoringType), 'RUTIN');
                                $isSeguimiento = !empty($monitoringType) ? (str_contains(strtoupper($monitoringType), 'SEGUI') || !$isRutinario) : true;
                            @endphp
                            <div style="display: flex; align-items: center; gap: 12px; font-size: 7.5pt;">
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <span>RUTINARIO:</span>
                                    <input type="checkbox" id="hdr_tipo_rutinario" onchange="handleMonitoringTypeChange('rutinario')" {{ $isRutinario ? 'checked' : '' }}>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 4px; cursor: pointer;">
                                    <span>SEGUIMIENTO:</span>
                                    <input type="checkbox" id="hdr_tipo_seguimiento" onchange="handleMonitoringTypeChange('seguimiento')" {{ $isSeguimiento ? 'checked' : '' }}>
                                </label>
                            </div>
                        </td>
                        <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">SERIE:</td>
                        <td style="width: 37%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_serie" class="ilum-live-input" value="{{ $equipmentSerial }}"
                                placeholder="Número de serie..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- 3. Título de la Sección -->
            <div style="text-align: center; font-weight: bold; font-size: 9.5pt; text-transform: uppercase; margin: 10px 0 6px 0; color: #000000; letter-spacing: 0.3px;">
                EVALUACIÓN DE RIESGOS
            </div>

            <!-- 4. Matriz de Medición y Evaluación de Iluminancia (Columnas Adaptables Dinámicas hasta 25 Lecturas) -->
            <table class="ilum-report-table" id="tablePlanillaIluminacion">
                <colgroup>
                    <col style="width: {{ $wNum }}%;">
                    <col style="width: {{ $wArea }}%;">
                    <col style="width: {{ $wPuesto }}%;">
                    <col style="width: {{ $wPunto }}%;">
                    <col style="width: {{ $wDesc }}%;">
                    <col style="width: {{ $wHora }}%;">
                    <col style="width: {{ $wTipo }}%;">
                    <col style="width: {{ $wReq }}%;">
                    @for($k = 1; $k <= $mCount; $k++)
                        <col style="width: {{ $wEachM }}%;">
                    @endfor
                    <col style="width: {{ $wMin }}%;">
                    <col style="width: {{ $wMax }}%;">
                    <col style="width: {{ $wProm }}%;">
                    <col style="width: {{ $wCumple }}%;">
                    <col style="width: {{ $wObs }}%;">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align: center;">N°</th>
                        <th rowspan="2" style="text-align: center;">Área</th>
                        <th rowspan="2" style="text-align: center;">Puesto de<br>trabajo</th>
                        <th rowspan="2" style="text-align: center;">Punto de<br>medición</th>
                        <th rowspan="2" style="text-align: center;">Descripción<br>de la actividad</th>
                        <th rowspan="2" style="text-align: center;">Horario de<br>medición</th>
                        <th rowspan="2" style="text-align: center;">Tipo de<br>iluminación</th>
                        <th rowspan="2" style="text-align: center;">Nivel Iluminancia<br>requerido (lux)</th>
                        
                        {{-- Sub-columnas dinámicas que crecen según la cantidad de lecturas (hasta 25) --}}
                        <th colspan="{{ $mCount }}" style="text-align: center; padding: 2px 1px;">
                            Medición de iluminancia (Lux)
                        </th>
                        
                        <th colspan="3" style="text-align: center; padding: 2px 1px;">Resultados</th>
                        <th rowspan="2" style="text-align: center;">Cumple / no<br>cumple</th>
                        <th rowspan="2" style="text-align: center;">Observaciones</th>
                    </tr>
                    <tr>
                        @for($k = 1; $k <= $mCount; $k++)
                            <th style="text-align: center; font-weight: normal; font-size: 6pt; padding: 1px 0;">
                                M{{ $k }}
                            </th>
                        @endfor
                        <th style="text-align: center;">Min</th>
                        <th style="text-align: center;">Max</th>
                        <th style="text-align: center;">Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($measurementsList as $m)
                        <tr>
                            {{-- 1. N° --}}
                            <td style="text-align: center; font-weight: bold;">{{ $m['num'] }}</td>

                            {{-- 2. Área --}}
                            <td style="text-align: left;">{{ $m['area'] ?? '—' }}</td>

                            {{-- 3. Puesto de trabajo --}}
                            <td style="text-align: left;">{{ $m['workstation'] ?? '—' }}</td>

                            {{-- 4. Punto de medición --}}
                            <td style="text-align: left;">{{ $m['measurement_point'] ?? '—' }}</td>

                            {{-- 5. Descripción de la actividad --}}
                            <td style="text-align: left;">{{ $m['activity_description'] ?? '—' }}</td>

                            {{-- 6. Horario de medición --}}
                            <td style="text-align: center;">{{ $m['time'] ?? '—' }}</td>

                            {{-- 7. Tipo de iluminación --}}
                            <td style="text-align: center;">{{ $m['lighting_type'] ?? 'Natural' }}</td>

                            {{-- 8. Nivel Iluminancia requerido (lux) --}}
                            <td style="text-align: center; font-weight: 500;">
                                {{ number_format((float)($m['required_lux'] ?? 100), 2, ',', '.') }}
                            </td>

                            {{-- 9.. Sub-columnas M1 a M(n) dinámicas adaptadas (hasta 25) --}}
                            @for($k = 0; $k < $mCount; $k++)
                                @php
                                    $val = isset($m['readings'][$k]) ? $m['readings'][$k] : null;
                                @endphp
                                <td style="text-align: center; font-family: Arial, monospace; font-size: 6.2pt; padding: 1.5px 1px;">
                                    {{ $val !== null ? number_format((float)$val, 1, ',', '.') : '' }}
                                </td>
                            @endfor

                            {{-- Resultados: Min, Max, Promedio --}}
                            <td style="text-align: center; font-family: Arial, monospace;">
                                {{ isset($m['min_lux']) && $m['min_lux'] > 0 ? number_format((float)$m['min_lux'], 1, ',', '.') : '—' }}
                            </td>
                            <td style="text-align: center; font-family: Arial, monospace;">
                                {{ isset($m['max_lux']) && $m['max_lux'] > 0 ? number_format((float)$m['max_lux'], 1, ',', '.') : '—' }}
                            </td>
                            <td style="text-align: center; font-weight: bold; font-family: Arial, monospace;">
                                {{ isset($m['avg_lux']) && $m['avg_lux'] > 0 ? number_format((float)$m['avg_lux'], 1, ',', '.') : '0,0' }}
                            </td>

                            {{-- Cumple/no cumple el valor --}}
                            <td style="text-align: center; font-weight: bold;">
                                {{ $m['compliance_text'] ?? ($m['is_compliant'] ? 'Cumple' : 'No cumple') }}
                            </td>

                            {{-- Observaciones --}}
                            <td style="text-align: left; font-size: 6.5pt;">
                                {{ $m['observations'] ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 12 + $mCount }}" style="text-align: center; color: #64748b; padding: 16px;">
                                No se encontraron registros de medición en este módulo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const MODULE_ID = {{ $module->id }};
    const MAX_READINGS_COUNT = {{ $mCount }};
    const SAVE_REPORT_URL = "{{ route('modules.illumination.report.save', $module->id) }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    let autoSaveTimeout = null;

    function showActionToast(message, type = 'success') {
        const existing = document.querySelector('.table-action-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `table-action-toast ${type}`;
        toast.innerHTML = `
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(12px)';
            setTimeout(() => toast.remove(), 400);
        }, 3500);
    }

    function setAutoSaveStatus(status) {
        const badge = document.getElementById('autoSaveBadge');
        if (!badge) return;

        badge.className = 'header-auto-save-status';
        if (status === 'saving') {
            badge.classList.add('saving');
            badge.innerHTML = `
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
                <span>Guardando...</span>
            `;
        } else if (status === 'saved') {
            badge.innerHTML = `
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Guardado</span>
            `;
        } else if (status === 'error') {
            badge.classList.add('error');
            badge.innerHTML = `
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="15" y1="9" x2="9" y2="15" />
                    <line x1="9" y1="9" x2="15" y2="15" />
                </svg>
                <span>Error al guardar</span>
            `;
        }
    }

    function handleMonitoringTypeChange(type) {
        const chkRutinario = document.getElementById('hdr_tipo_rutinario');
        const chkSeguimiento = document.getElementById('hdr_tipo_seguimiento');

        if (type === 'rutinario' && chkRutinario.checked) {
            chkSeguimiento.checked = false;
        } else if (type === 'seguimiento' && chkSeguimiento.checked) {
            chkRutinario.checked = false;
        }
        autoSaveReportHeader();
    }

    function autoSaveReportHeader() {
        clearTimeout(autoSaveTimeout);
        setAutoSaveStatus('saving');

        autoSaveTimeout = setTimeout(() => {
            const instalacion = document.getElementById('hdr_instalacion')?.value || '';
            const equipo = document.getElementById('hdr_equipo')?.value || '';
            const fechaInicio = document.getElementById('hdr_fecha_inicio')?.value || '';
            const fechaFin = document.getElementById('hdr_fecha_fin')?.value || '';
            const marca = document.getElementById('hdr_marca')?.value || '';
            const modelo = document.getElementById('hdr_modelo')?.value || '';
            const serie = document.getElementById('hdr_serie')?.value || '';

            const isRutinario = document.getElementById('hdr_tipo_rutinario')?.checked;
            const isSeguimiento = document.getElementById('hdr_tipo_seguimiento')?.checked;
            let monitoringType = 'Seguimiento';
            if (isRutinario) monitoringType = 'Rutinario';
            if (isSeguimiento) monitoringType = 'Seguimiento';

            // Convert DD/MM/YYYY to YYYY-MM-DD for backend date fields if formatted
            const parseDateToIso = (dStr) => {
                if (!dStr) return null;
                const parts = dStr.split('/');
                if (parts.length === 3) {
                    return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
                }
                return dStr;
            };

            const payload = {
                _token: CSRF_TOKEN,
                installation_name: instalacion,
                equipment_name: equipo,
                equipment_brand: marca,
                equipment_model: modelo,
                equipment_serial: serie,
                start_date: parseDateToIso(fechaInicio),
                end_date: parseDateToIso(fechaFin),
                monitoring_type: monitoringType,
            };

            fetch(SAVE_REPORT_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify(payload),
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setAutoSaveStatus('saved');
                } else {
                    setAutoSaveStatus('error');
                }
            })
            .catch(() => {
                setAutoSaveStatus('error');
            });
        }, 600);
    }

    /**
     * Exportación de la Planilla Oficial a Microsoft Word (.doc) en formato Horizontal (Landscape)
     */
    function downloadIluminacionWordDoc() {
        const instalacion = document.getElementById('hdr_instalacion')?.value || '{{ $installationName }}';
        const equipo = document.getElementById('hdr_equipo')?.value || '{{ $equipmentName }}';
        const fechaInicio = document.getElementById('hdr_fecha_inicio')?.value || '{{ $startDateFormatted }}';
        const fechaFin = document.getElementById('hdr_fecha_fin')?.value || '{{ $endDateFormatted }}';
        const marca = document.getElementById('hdr_marca')?.value || '{{ $equipmentBrand }}';
        const modelo = document.getElementById('hdr_modelo')?.value || '{{ $equipmentModel }}';
        const serie = document.getElementById('hdr_serie')?.value || '{{ $equipmentSerial }}';

        const isRutinario = document.getElementById('hdr_tipo_rutinario')?.checked;
        const isSeguimiento = document.getElementById('hdr_tipo_seguimiento')?.checked;
        const rutinarioMark = isRutinario ? 'X' : '&nbsp;';
        const seguimientoMark = isSeguimiento ? 'X' : '&nbsp;';

        // Generar subcabeceras M1..Mn
        let mHeadersHtml = '';
        for (let k = 1; k <= MAX_READINGS_COUNT; k++) {
            mHeadersHtml += `<th style="border: 1px solid #000000; padding: 2px 1px; text-align: center; font-weight: normal; font-size: 6pt; background-color: #deebf7;">M${k}</th>`;
        }

        // Clonar filas de la tabla
        const tableElement = document.getElementById('tablePlanillaIluminacion');
        const rows = tableElement.querySelectorAll('tbody tr');
        let tableRowsHtml = '';

        const totalCols = 12 + MAX_READINGS_COUNT;

        rows.forEach(r => {
            const cells = r.querySelectorAll('td');
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                tableRowsHtml += `<tr><td colspan="${totalCols}" style="border: 1px solid #000000; padding: 6px; text-align: center; color: #666666;">No hay registros disponibles.</td></tr>`;
                return;
            }

            let rowHtml = '<tr>';
            const promIdx = 7 + MAX_READINGS_COUNT + 2; // Índice del promedio
            const cumpleIdx = promIdx + 1; // Índice de Cumple

            cells.forEach((c, idx) => {
                const text = c.innerText.trim();
                let align = 'left';
                let weight = 'normal';
                let fontSize = '7pt';

                if (idx === 0) { align = 'center'; weight = 'bold'; }
                else if (idx >= 5 && idx <= (7 + MAX_READINGS_COUNT + 2)) { 
                    align = 'center'; 
                    if (idx >= 7) fontSize = '6.2pt';
                    if (idx === promIdx) weight = 'bold';
                }
                else if (idx === cumpleIdx) { align = 'center'; weight = 'bold'; }
                else if (idx === (cumpleIdx + 1)) { fontSize = '6.5pt'; }

                rowHtml += `<td style="border: 1px solid #000000; padding: 2px 2px; text-align: ${align}; font-weight: ${weight}; font-size: ${fontSize}; font-family: Arial, sans-serif; vertical-align: middle;">${text}</td>`;
            });
            rowHtml += '</tr>';
            tableRowsHtml += rowHtml;
        });

        const wordHtml = `
<html xmlns:o='urn:schemas-microsoft-com:office:office' 
      xmlns:w='urn:schemas-microsoft-com:office:word' 
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <title>Planilla de Medición y Evaluación de Niveles de Iluminación</title>
    <!--[if gte mso 9]>
    <xml>
        <w:WordDocument>
            <w:View>Print</w:View>
            <w:Zoom>100</w:Zoom>
            <w:DoNotOptimizeForBrowser/>
        </w:WordDocument>
    </xml>
    <![endif]-->
    <style>
        @page Section1 {
            size: 11.0in 8.5in;
            margin: 0.3in 0.35in 0.3in 0.35in;
            mso-header-margin: 0.15in;
            mso-footer-margin: 0.15in;
            mso-page-orientation: landscape;
        }
        div.Section1 {
            page: Section1;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #000000;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            table-layout: fixed;
        }
        th, td {
            mso-line-height-rule: exactly;
        }
    </style>
</head>
<body lang='ES-BO'>
<div class='Section1'>

    <!-- ENCABEZADO AZUL -->
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-bottom: 0px;">
        <tr style="background-color: #2c73b8; background: #2c73b8;">
            <td style="border: 1.5px solid #000000; padding: 5px 6px; text-align: center; font-weight: bold; font-size: 9.5pt; color: #ffffff; text-transform: uppercase;">
                PLANILLA DE MEDICIÓN Y EVALUACIÓN DE NIVELES DE ILUMINACIÓN
            </td>
        </tr>
    </table>

    <!-- TABLA DE INFORMACIÓN TÉCNICA -->
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-top: -1.5px; margin-bottom: 8px; font-family: Arial, sans-serif; font-size: 7.5pt;">
        <tr>
            <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">INSTALACIÓN:</td>
            <td style="width: 35%; border: 1px solid #000000; padding: 2.5px 5px;">${instalacion}</td>
            <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">EQUIPO:</td>
            <td style="width: 37%; border: 1px solid #000000; padding: 2.5px 5px;">${equipo}</td>
        </tr>
        <tr>
            <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">FECHA DE INICIO:</td>
            <td style="width: 35%; border: 1px solid #000000; padding: 2.5px 5px;">${fechaInicio}</td>
            <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">MARCA:</td>
            <td style="width: 37%; border: 1px solid #000000; padding: 2.5px 5px;">${marca}</td>
        </tr>
        <tr>
            <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">FECHA DE FINALIZACIÓN:</td>
            <td style="width: 35%; border: 1px solid #000000; padding: 2.5px 5px;">${fechaFin}</td>
            <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">MODELO:</td>
            <td style="width: 37%; border: 1px solid #000000; padding: 2.5px 5px;">${modelo}</td>
        </tr>
        <tr>
            <td style="width: 15%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">TIPO DE MONITOREO:</td>
            <td style="width: 35%; border: 1px solid #000000; padding: 2.5px 5px;">
                RUTINARIO: &nbsp;<b>${rutinarioMark}</b> &nbsp;&nbsp;&nbsp;&nbsp; SEGUIMIENTO: &nbsp;<b>${seguimientoMark}</b>
            </td>
            <td style="width: 13%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">SERIE:</td>
            <td style="width: 37%; border: 1px solid #000000; padding: 2.5px 5px;">${serie}</td>
        </tr>
    </table>

    <!-- TÍTULO EVALUACIÓN DE RIESGOS -->
    <div style="text-align: center; font-weight: bold; font-size: 9.5pt; text-transform: uppercase; margin: 8px 0 5px 0; color: #000000;">
        EVALUACIÓN DE RIESGOS
    </div>

    <!-- TABLA DE EVALUACIÓN DE ILUMINACIÓN (COLUMNAS DINÁMICAS ADAPTADAS) -->
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 7pt;">
        <thead>
            <tr style="background-color: #deebf7; background: #deebf7;">
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 1px; text-align: center; width: 16px; font-size: 6.2pt; word-break: normal; white-space: normal;">N°</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 2px; text-align: center; width: 68px; font-size: 6.2pt; word-break: normal; white-space: normal;">Área</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 2px; text-align: center; width: 68px; font-size: 6.2pt; word-break: normal; white-space: normal;">Puesto de<br>trabajo</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 2px; text-align: center; width: 62px; font-size: 6.2pt; word-break: normal; white-space: normal;">Punto de<br>medición</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 2px; text-align: center; width: 68px; font-size: 6.2pt; word-break: normal; white-space: normal;">Descripción<br>de la actividad</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 1px; text-align: center; width: 44px; font-size: 6.2pt; word-break: normal; white-space: normal;">Horario de<br>medición</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 1px; text-align: center; width: 44px; font-size: 6.2pt; word-break: normal; white-space: normal;">Tipo de<br>iluminación</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 2px 1px; text-align: center; width: 50px; font-size: 6.2pt; word-break: normal; white-space: normal;">Nivel Iluminancia<br>requerido (lux)</th>
                
                <th colspan="${MAX_READINGS_COUNT}" style="border: 1px solid #000000; padding: 2px 1px; text-align: center; font-size: 6.2pt; word-break: normal; white-space: normal;">
                    Medición de iluminancia (Lux)
                </th>
                
                <th colspan="3" style="border: 1px solid #000000; padding: 2px 1px; text-align: center; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Resultados</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 3px 1px; text-align: center; width: 48px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Cumple / no<br>cumple</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 65px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Observaciones</th>
            </tr>
            <tr style="background-color: #deebf7; background: #deebf7;">
                ${mHeadersHtml}
                <th style="border: 1px solid #000000; padding: 2px 1px; text-align: center; width: 24px; font-size: 6.2pt; word-break: normal; white-space: normal;">Min</th>
                <th style="border: 1px solid #000000; padding: 2px 1px; text-align: center; width: 24px; font-size: 6.2pt; word-break: normal; white-space: normal;">Max</th>
                <th style="border: 1px solid #000000; padding: 2px 1px; text-align: center; width: 28px; font-size: 6.2pt; word-break: normal; white-space: normal;">Promedio</th>
            </tr>
        </thead>
        <tbody>
            ${tableRowsHtml}
        </tbody>
    </table>

</div>
</body>
</html>
        `;

        const blob = new Blob(['\ufeff', wordHtml], {
            type: 'application/msword;charset=utf-8'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Planilla_Iluminacion_Ocupacional_Modulo_${MODULE_ID}.doc`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showActionToast('Planilla Word (.doc) descargada en formato Horizontal.');
    }
</script>
@endpush
