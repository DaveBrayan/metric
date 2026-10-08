@extends('layouts.app')

@section('title', 'Informe Técnico — Planilla de Monitoreo y Evaluación de Gases — Metric v2')

@push('styles')
    @metricStyle('gases')
    <style>
        /* ==========================================================================
           CONTENEDOR GENERAL Y STEPPER PARA INFORME DE GASES (TAMAÑO CARTA VERTICAL)
           ========================================================================== */
        .stepper-page-wrapper {
            width: 100%;
            padding: 12px 0 60px 0;
            min-height: calc(100vh - 70px);
        }

        /* 1. Header Banner */
        .illumination-header-banner {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .illumination-header-banner h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.4px;
            color: var(--ink, #0f172a);
            margin: 6px 0 4px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* 2. Barra Superior de Pasos (Stepper) */
        .stepper-header-bar {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 10px 14px;
            margin-bottom: 24px;
            box-shadow: 0 4px 14px rgba(15, 28, 46, 0.04);
            position: sticky;
            top: 70px;
            z-index: 40;
            backdrop-filter: blur(10px);
        }

        .stepper-nav-track {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            padding-bottom: 4px;
            scrollbar-width: thin;
        }

        .stepper-nav-track::-webkit-scrollbar {
            height: 4px;
        }

        .stepper-nav-track::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .step-nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 8px 14px;
            border-radius: 10px;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: left;
            white-space: nowrap;
            flex-shrink: 0;
            user-select: none;
        }

        .step-nav-btn:hover:not(.active) {
            background: #f1f5f9;
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .step-nav-number {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 12px;
            font-weight: 800;
            background: #ffffff;
            color: #64748b;
            border: 1.5px solid #cbd5e1;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .step-nav-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .step-nav-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            line-height: 1.2;
        }

        .step-nav-subtitle {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .step-nav-btn.active {
            background: #e0f2fe;
            border-color: #0284c7;
            box-shadow: 0 2px 10px rgba(2, 132, 199, 0.18);
        }

        .step-nav-btn.active .step-nav-number {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
        }

        .step-nav-btn.active .step-nav-title {
            color: #0284c7;
            font-weight: 800;
        }

        .step-nav-btn.active .step-nav-subtitle {
            color: #0369a1;
            font-weight: 600;
        }

        .step-nav-btn.completed .step-nav-number {
            background: #ecfdf5;
            color: #059669;
            border-color: #a7f3d0;
        }

        .stepper-progress-track {
            width: 100%;
            height: 3px;
            background: #e2e8f0;
            border-radius: 9999px;
            margin-top: 8px;
            overflow: hidden;
        }

        .stepper-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #0284c7 0%, #0070c0 100%);
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* 3. Contenedor de Paneles de Pasos */
        .stepper-content-area {
            width: 100%;
            margin-top: 10px;
        }

        .step-pane-content {
            display: none;
            animation: fadeInStep 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .step-pane-content.active {
            display: block;
        }

        @keyframes fadeInStep {
            from {
                opacity: 0;
                transform: translateY(6px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* 4. Barra de Herramientas de la Hoja */
        .gases-sheet-wrapper {
            width: 100%;
            max-width: 816px; /* 8.5 in @ 96 DPI = 816px (Carta Vertical) */
            margin: 0 auto;
        }

        .gases-sheet-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 10px 16px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(15, 28, 46, 0.03);
            flex-wrap: wrap;
        }

        .gases-toolbar-left,
        .gases-toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .step-pane-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11.5px;
            font-weight: 700;
            background: #f0f9ff;
            color: #0284c7;
            border: 1px solid #bae6fd;
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
            background: linear-gradient(135deg, #0070c0 0%, #005a9e 100%);
            color: #ffffff;
            border-color: #004d88;
            box-shadow: 0 2px 6px rgba(0, 112, 192, 0.25);
        }

        .btn-toolbar-word:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0070c0 100%);
            transform: translateY(-1px);
        }

        .btn-download-all {
            background: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        .btn-download-all:hover {
            background: #1e293b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* ==========================================================================
           5. HOJA TAMAÑO CARTA VERTICAL (215.9mm x 279.4mm / 8.5in x 11in)
           ========================================================================== */
        .gases-sheet-card {
            background: #ffffff;
            border: 1.5px solid #000000;
            box-shadow: 0 6px 24px rgba(15, 23, 42, 0.08);
            width: 100%;
            max-width: 816px; /* 8.5 in @ 96 DPI */
            min-height: 1056px; /* 11 in @ 96 DPI */
            box-sizing: border-box;
            padding: 16mm 14mm;
            border-radius: 2px;
            margin: 0 auto 30px auto;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000000;
            position: relative;
        }

        .gases-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
            color: #000000;
            border: 1px solid #000000;
        }

        .gases-table th,
        .gases-table td {
            border: 1px solid #000000;
            padding: 4px 4px;
            vertical-align: middle;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5pt;
        }

        /* Encabezados Azules Oficiales */
        .gases-th-blue {
            background-color: #0070c0 !important;
            background: #0070c0 !important;
            color: #ffffff !important;
            font-weight: bold !important;
            text-align: center !important;
            border: 1px solid #000000 !important;
            padding: 5px 3px !important;
            text-transform: uppercase;
            font-size: 8.5pt !important;
            letter-spacing: 0.2px;
        }

        .gases-live-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            padding: 1px 3px;
            box-sizing: border-box;
            border-radius: 2px;
            transition: all 0.15s ease;
        }

        .gases-live-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .gases-live-input:focus {
            outline: none;
            border-color: #0070c0;
            background: #ffffff;
            box-shadow: 0 0 0 1.5px rgba(0, 112, 192, 0.2);
        }

        /* 6. Barra Inferior de Navegación del Stepper */
        .stepper-footer-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 816px;
            margin: 20px auto 40px auto;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 12px 20px;
            box-shadow: 0 2px 8px rgba(15, 28, 46, 0.04);
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-step-nav {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid transparent;
        }

        .btn-step-prev {
            background: #f1f5f9;
            color: #475569;
            border-color: #cbd5e1;
        }

        .btn-step-prev:hover:not(:disabled) {
            background: #e2e8f0;
            color: #0f172a;
        }

        .btn-step-prev:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .btn-step-next {
            background: linear-gradient(135deg, #0070c0 0%, #005a9e 100%);
            color: #ffffff;
            border-color: #004d88;
            box-shadow: 0 2px 6px rgba(0, 112, 192, 0.25);
        }

        .btn-step-next:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0070c0 100%);
            transform: translateY(-1px);
        }

        .step-indicator-text {
            font-size: 13px;
            font-weight: 700;
            color: #475569;
        }

        /* 7. Impresión en Hoja Carta Vertical */
        @media print {
            body {
                background: #ffffff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .stepper-header-bar,
            .gases-sheet-toolbar,
            .illumination-header-banner,
            .stepper-footer-nav,
            nav,
            header,
            footer,
            .sidebar-nav,
            .btn-toolbar-action {
                display: none !important;
            }
            .stepper-page-wrapper {
                padding: 0 !important;
                background: #ffffff !important;
            }
            .step-pane-content {
                display: block !important;
            }
            .gases-sheet-wrapper {
                max-width: 100% !important;
                width: 100% !important;
            }
            .gases-sheet-card {
                border: 1px solid #000000 !important;
                box-shadow: none !important;
                margin: 0 auto 20px auto !important;
                width: 100% !important;
                max-width: 100% !important;
                padding: 8mm !important;
                page-break-after: always;
                page-break-inside: avoid;
            }
            @page {
                size: letter portrait;
                margin: 8mm;
            }
        }
    </style>
@endpush

@section('content')
<div class="stepper-page-wrapper">

    <!-- 1. Encabezado Principal y Retorno al Monitoreo -->
    <div class="illumination-header-banner">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <a href="{{ route('modules.gases', $module->id) }}" class="btn-secondary-subtle" style="padding: 6px 14px; font-size: 12px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    <span>Volver al Monitoreo</span>
                </a>
                <span style="font-size: 12px; color: #94a3b8;">/</span>
                <span style="font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 9999px; border: 1px solid #bae6fd;">
                    {{ $installationName }}
                </span>
            </div>
            <h1>
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0070c0" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z" />
                </svg>
                <span>Informe de Evaluación de Gases — Formato Carta Vertical</span>
            </h1>
            <p style="margin: 0; color: #64748b; font-size: 13.5px;">
                Planillas técnicas en formato vertical tamaño carta: Paso 1 (Puntos Evaluados) y Pasos Dinámicos por Tipo de Gas según TLVs ACGIH
            </p>
        </div>

        <div class="header-action-group">
            <a href="{{ route('modules.gases', $module->id) }}" class="btn-primary-hero-action" style="background: linear-gradient(135deg, #0070c0 0%, #005a9e 100%);">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Ir al Monitoreo</span>
            </a>
        </div>
    </div>

    @php
        $totalSteps = 1 + count($gasReports);
    @endphp

    <!-- 2. Barra Superior de Pasos Interactivos (Stepper) -->
    <div class="stepper-header-bar">
        <div class="stepper-nav-track" id="gasesStepperTrack">
            
            <!-- Paso 1: Puntos Evaluados -->
            <button type="button" class="step-nav-btn active" data-step="1" onclick="goToStep(1)" title="Paso 1: Puntos de Monitoreo Evaluados">
                <div class="step-nav-number">1</div>
                <div class="step-nav-text">
                    <span class="step-nav-title">Paso 1: Puntos Evaluados</span>
                    <span class="step-nav-subtitle">Puntos de Monitoreo</span>
                </div>
            </button>

            <!-- Pasos 2 a N: Un Paso por Cada Tipo de Gas Registrado -->
            @foreach($gasReports as $gKey => $gData)
                @php
                    $stepNum = $loop->iteration + 1;
                    $gInfo = $gData['info'];
                @endphp
                <button type="button" class="step-nav-btn" data-step="{{ $stepNum }}" onclick="goToStep({{ $stepNum }})" title="Paso {{ $stepNum }}: Evaluación de {{ $gInfo['formula'] }}">
                    <div class="step-nav-number">{{ $stepNum }}</div>
                    <div class="step-nav-text">
                        <span class="step-nav-title">Paso {{ $stepNum }}: {{ $gInfo['formula'] }}</span>
                        <span class="step-nav-subtitle">{{ $gInfo['name'] }}</span>
                    </div>
                </button>
            @endforeach

        </div>

        <!-- Barra de Progreso del Stepper -->
        <div class="stepper-progress-track">
            <div class="stepper-progress-fill" id="stepperProgressBar" style="width: {{ round(100 / max(1, $totalSteps), 1) }}%;"></div>
        </div>
    </div>

    <!-- 3. Contenido de los Pasos (Paneles en Formato Carta Vertical) -->
    <div class="stepper-content-area">

        <!-- ========================================================================= -->
        <!-- PASO 1: PUNTOS DE MONITOREO EVALUADOS (HOJA CARTA VERTICAL)               -->
        <!-- ========================================================================= -->
        <div class="step-pane-content active" id="step_pane_1">
            <div class="gases-sheet-wrapper">

                <!-- Barra de Herramientas del Paso 1 -->
                <div class="gases-sheet-toolbar">
                    <div class="gases-toolbar-left">
                        <span class="step-pane-badge">Paso 1 de {{ $totalSteps }} — Puntos Evaluados</span>
                        <span id="step1AutoSaveBadge" class="header-auto-save-status">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Guardado</span>
                        </span>
                    </div>

                    <div class="gases-toolbar-right">
                        <button type="button" class="btn-toolbar-action btn-toolbar-subtle" onclick="printStep(1)"
                            title="Imprimir o guardar como PDF en hoja tamaño carta vertical">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect width="12" height="8" x="6" y="14"></rect>
                            </svg>
                            <span>Imprimir / PDF</span>
                        </button>
                        <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadStep1Doc()"
                            title="Descargar documento oficial compatible con Microsoft Word (.doc) en formato carta vertical">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <span>Descargar Word (.doc)</span>
                        </button>
                    </div>
                </div>

                <!-- Documento Hoja Carta Vertical Paso 1 (Limpio: Solo Título y Tabla) -->
                <div class="gases-sheet-card" id="step1DocumentSheet" style="padding-top: 20mm;">

                    <!-- Título Oficial Centrado -->
                    <div style="text-align: center; margin: 0 0 14px 0;">
                        <h2 style="font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase; margin: 0; font-family: Arial, sans-serif; letter-spacing: 0.4px;">
                            PUNTOS DE MONITOREO EVALUADOS
                        </h2>
                    </div>

                    <!-- Tabla de Puntos Evaluados (Fiel a la Imagen 1 del Usuario) -->
                    <table class="gases-table" id="tableStep1Points">
                        <colgroup>
                            <col style="width: 5%;">
                            <col style="width: 20%;">
                            <col style="width: 24%;">
                            <col style="width: 12%;">
                            <col style="width: 9%;">
                            <col style="width: 20%;">
                            <col style="width: 5%;">
                            <col style="width: 5%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="gases-th-blue">N°</th>
                                <th class="gases-th-blue">ÁREA DE TRABAJO</th>
                                <th class="gases-th-blue">PUNTO DE MEDICIÓN</th>
                                <th class="gases-th-blue">FECHA</th>
                                <th class="gases-th-blue">HORA</th>
                                <th class="gases-th-blue">COORDENADAS UTM</th>
                                <th class="gases-th-blue">%H.R.</th>
                                <th class="gases-th-blue">T °C</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($measurementsList as $idx => $m)
                                @php
                                    $numDisp = $idx + 1;

                                    $dateStr = $m->measurement_date ? $m->measurement_date->format('d/m/Y') : '—';
                                    $timeStr = $m->measurement_time ?: '—';

                                    // Formato de Coordenadas UTM: 707779,02E; 8010782,99N Z19K
                                    $utmStr = '—';
                                    if ($m->utm_easting !== null && $m->utm_northing !== null) {
                                        $eastingFormatted = number_format((float)$m->utm_easting, 2, ',', '');
                                        $northingFormatted = number_format((float)$m->utm_northing, 2, ',', '');
                                        $zoneFormatted = $m->utm_zone ? ' ' . $m->utm_zone : ' Z19K';
                                        $utmStr = "{$eastingFormatted}E; {$northingFormatted}N{$zoneFormatted}";
                                    } elseif (!empty($m->location)) {
                                        $utmStr = $m->location;
                                    }

                                    // %H.R. y Temperatura
                                    $hrVal = $m->presion_atm !== null ? (float)$m->presion_atm : null;
                                    $tempVal = $m->temperatura !== null ? (float)$m->temperatura : null;
                                @endphp
                                <tr>
                                    <td align="center" style="text-align: center; font-weight: bold;">{{ $numDisp }}</td>
                                    <td style="text-align: left; padding-left: 5px;">{{ $m->area ?: '—' }}</td>
                                    <td style="text-align: left; padding-left: 5px;">{{ $m->measurement_point ?: ($m->workstation ?: '—') }}</td>
                                    <td align="center" style="text-align: center;">{{ $dateStr }}</td>
                                    <td align="center" style="text-align: center;">{{ $timeStr }}</td>
                                    <td align="center" style="text-align: center; font-size: 7.8pt;">{{ $utmStr }}</td>
                                    <td align="right" style="text-align: right; padding-right: 4px;">{{ $hrVal !== null ? (str_contains((string)$hrVal, '.') ? number_format($hrVal, 1, ',', '') : number_format($hrVal, 0, ',', '')) : '—' }}</td>
                                    <td align="right" style="text-align: right; padding-right: 4px;">{{ $tempVal !== null ? number_format($tempVal, 0, ',', '') : '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" align="center" style="padding: 16px; color: #64748b; font-style: italic;">
                                        No se han registrado puntos de medición en este módulo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PASOS 2 A N: EVALUACIÓN POR TIPO DE GAS REGISTRADO (HOJA CARTA VERTICAL)  -->
        <!-- (Sin cabecera superior repetitiva ni firmas inferiores, solo tabla limpia) -->
        <!-- ========================================================================= -->
        @foreach($gasReports as $gKey => $gData)
            @php
                $stepIndex = $loop->iteration + 1;
                $gInfo = $gData['info'];
                $points = $gData['points'];
                $formula = $gInfo['formula'];
                $unit = $gInfo['unit'];
                $tlvDisplay = $gInfo['tlv'];
                $evalTitle = $gInfo['eval_title'] ?? ("EVALUACIÓN DE {$formula} EN EL AMBIENTE");
            @endphp
            <div class="step-pane-content" id="step_pane_{{ $stepIndex }}">
                <div class="gases-sheet-wrapper">

                    <!-- Barra de Herramientas del Paso Dinámico -->
                    <div class="gases-sheet-toolbar">
                        <div class="gases-toolbar-left">
                            <span class="step-pane-badge">Paso {{ $stepIndex }} de {{ $totalSteps }} — Evaluación {{ $formula }}</span>
                            <span id="step{{ $stepIndex }}AutoSaveBadge" class="header-auto-save-status">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Guardado</span>
                            </span>
                        </div>

                        <div class="gases-toolbar-right">
                            <button type="button" class="btn-toolbar-action btn-toolbar-subtle" onclick="printStep({{ $stepIndex }})"
                                title="Imprimir o guardar como PDF en hoja tamaño carta vertical">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                    <rect width="12" height="8" x="6" y="14"></rect>
                                </svg>
                                <span>Imprimir / PDF</span>
                            </button>
                            <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadGasStepDoc('{{ $gKey }}', {{ $stepIndex }})"
                                title="Descargar documento oficial compatible con Microsoft Word (.doc) en formato carta vertical">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span>Descargar Word (.doc)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Documento Hoja Carta Vertical Paso Dinámico (Limpio: Solo Título y Tabla) -->
                    <div class="gases-sheet-card" id="step{{ $stepIndex }}DocumentSheet" style="padding-top: 20mm;">

                        <!-- Título Oficial Centrado de Evaluación del Gas (Fiel a la Imagen 2) -->
                        <div style="text-align: center; margin: 0 0 14px 0;">
                            <h2 style="font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase; margin: 0; font-family: Arial, sans-serif; letter-spacing: 0.4px;">
                                {{ $evalTitle }}
                            </h2>
                        </div>

                        <!-- Tabla de Evaluación del Gas Específico (Fiel a Imagen 2 del Usuario) -->
                        <table class="gases-table" id="tableStep{{ $stepIndex }}Gas_{{ $gKey }}">
                            <colgroup>
                                <col style="width: 5%;">
                                <col style="width: 25%;">
                                <col style="width: 28%;">
                                <col style="width: 16%;">
                                <col style="width: 14%;">
                                <col style="width: 12%;">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="gases-th-blue">N°</th>
                                    <th class="gases-th-blue">ÁREA DE TRABAJO</th>
                                    <th class="gases-th-blue">PUNTO DE MEDICIÓN</th>
                                    <th class="gases-th-blue">{{ $formula }} ({{ $unit }})</th>
                                    <th class="gases-th-blue">TLV ({{ $unit }})</th>
                                    <th class="gases-th-blue">CUMPLE</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($points as $pt)
                                    <tr>
                                        <td align="center" style="text-align: center; font-weight: bold;">{{ $pt['num'] }}</td>
                                        <td style="text-align: left; padding-left: 5px;">{{ $pt['area'] }}</td>
                                        <td style="text-align: left; padding-left: 5px;">{{ $pt['measurement_point'] }}</td>
                                        <td align="right" style="text-align: right; padding-right: 12px; font-weight: 600;">{{ $pt['formatted_avg'] }}</td>
                                        <td align="right" style="text-align: right; padding-right: 12px;">{{ $pt['tlv'] }}</td>
                                        <td align="center" style="text-align: center; font-weight: bold; {{ $pt['cumple'] === 'NO' ? 'color: #dc2626;' : '' }}">{{ $pt['cumple'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" align="center" style="padding: 16px; color: #64748b; font-style: italic;">
                                            No se han registrado puntos para este gas en el módulo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>

                </div>
            </div>
        @endforeach

    </div>

    <!-- 4. Barra Inferior de Navegación del Stepper -->
    <div class="stepper-footer-nav">
        <button type="button" class="btn-step-nav btn-step-prev" id="btnStepPrev" onclick="navigateStep(-1)" disabled>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                <polyline points="15 18 9 12 15 6" />
            </svg>
            <span>Paso Anterior</span>
        </button>

        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="step-indicator-text">
                Paso <strong id="stepIndicatorNumber">1</strong> de <strong>{{ $totalSteps }}</strong>
            </span>
            <button type="button" class="btn-toolbar-action btn-download-all" onclick="downloadAllGasesDoc()"
                title="Descargar todos los pasos en un único archivo Word (.doc) con saltos de página">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span>Descargar Todo (.doc)</span>
            </button>
        </div>

        <button type="button" class="btn-step-nav btn-step-next" id="btnStepNext" onclick="navigateStep(1)">
            <span>Siguiente Paso</span>
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                <polyline points="9 18 15 12 9 6" />
            </svg>
        </button>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const totalSteps = {{ $totalSteps }};
    let currentStep = 1;
    const csrfToken = "{{ csrf_token() }}";
    const gasReportsData = @json($gasReports);

    // =========================================================================
    // CONTROLADOR DEL STEPPER INTERACTIVO
    // =========================================================================
    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;
        currentStep = step;

        // 1. Actualizar botones del Stepper
        document.querySelectorAll('.step-nav-btn').forEach(btn => {
            const s = parseInt(btn.getAttribute('data-step'), 10);
            btn.classList.remove('active', 'completed');
            if (s === currentStep) {
                btn.classList.add('active');
            } else if (s < currentStep) {
                btn.classList.add('completed');
            }
        });

        // 2. Cambiar panel activo
        document.querySelectorAll('.step-pane-content').forEach(pane => {
            pane.classList.remove('active');
        });
        const targetPane = document.getElementById(`step_pane_${currentStep}`);
        if (targetPane) targetPane.classList.add('active');

        // 3. Barra de Progreso
        const progress = (currentStep / totalSteps) * 100;
        const bar = document.getElementById('stepperProgressBar');
        if (bar) bar.style.width = `${progress}%`;

        // 4. Indicador numérico
        const ind = document.getElementById('stepIndicatorNumber');
        if (ind) ind.textContent = currentStep;

        // 5. Botones Prev / Next
        const btnPrev = document.getElementById('btnStepPrev');
        const btnNext = document.getElementById('btnStepNext');
        if (btnPrev) btnPrev.disabled = (currentStep === 1);
        if (btnNext) {
            if (currentStep === totalSteps) {
                btnNext.innerHTML = `<span>Finalizar</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`;
            } else {
                btnNext.innerHTML = `<span>Siguiente Paso</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="9 18 15 12 9 6"/></svg>`;
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function navigateStep(direction) {
        goToStep(currentStep + direction);
    }

    // Navegación con flechas de teclado (Left/Right)
    document.addEventListener('keydown', (e) => {
        if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) {
            return;
        }
        if (e.key === 'ArrowRight') {
            navigateStep(1);
        } else if (e.key === 'ArrowLeft') {
            navigateStep(-1);
        }
    });

    // =========================================================================
    // AUTO-GUARDADO DE ENCABEZADOS Y DATOS TÉCNICOS
    // =========================================================================
    let saveTimer = null;
    async function saveReportDataAsync() {
        clearTimeout(saveTimer);
        const badges = document.querySelectorAll('.header-auto-save-status');
        badges.forEach(b => {
            b.className = 'header-auto-save-status saving';
            b.innerHTML = '<span>Guardando...</span>';
        });

        saveTimer = setTimeout(async () => {
            const payload = {
                _token: csrfToken,
                installation_name: document.getElementById('step1_installation_name')?.value || '',
                monitoring_type: document.getElementById('step1_monitoring_type')?.value || '',
                equipment_name: document.getElementById('step1_equipment_name')?.value || '',
                equipment_serial: document.getElementById('step1_equipment_serial')?.value || '',
            };

            try {
                const resp = await fetch("{{ route('modules.gases.report.save', $module->id) }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify(payload)
                });
                const data = await resp.json();
                badges.forEach(b => {
                    b.className = 'header-auto-save-status';
                    b.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Guardado</span>';
                });
            } catch (e) {
                badges.forEach(b => {
                    b.className = 'header-auto-save-status error';
                    b.innerHTML = '<span>Error al guardar</span>';
                });
            }
        }, 500);
    }

    // =========================================================================
    // IMPRESIÓN OFICIAL TAMAÑO CARTA VERTICAL
    // =========================================================================
    function printStep(stepNum) {
        goToStep(stepNum);
        setTimeout(() => {
            window.print();
        }, 150);
    }

    // =========================================================================
    // GENERADORES WORD (.DOC) COMPATIBLES CON MICROSOFT WORD EN CARTA VERTICAL
    // =========================================================================
    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildWordHeaderAndMetaHtml(docTitle, subTitle, code) {
        const inst = document.getElementById('step1_installation_name')?.value || @json($installationName);
        const eqName = document.getElementById('step1_equipment_name')?.value || @json($equipmentName);
        const eqBrandModel = document.getElementById('step1_equipment_brand_model')?.value || @json($equipmentBrand . ' - ' . $equipmentModel);
        const eqSerial = document.getElementById('step1_equipment_serial')?.value || @json($equipmentSerial);
        const monType = document.getElementById('step1_monitoring_type')?.value || @json($monitoringType);
        const dates = @json($startDateFormatted . ' al ' . $endDateFormatted);

        return `
        <table width="100%" border="1" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: 1.5pt solid #000000; margin-bottom: 8pt;">
            <tr>
                <td width="22%" align="center" style="padding: 4pt; text-align: center; border: 1.5pt solid #000000;">
                    <p class="MsoNormal" style="font-weight: bold; font-size: 11pt; margin: 0; color: #0f172a;">PACHABOL</p>
                    <p class="MsoNormal" style="font-size: 6.5pt; font-weight: bold; color: #64748b; margin: 0;">SERVICIOS AMBIENTALES</p>
                </td>
                <td width="56%" align="center" style="padding: 4pt; text-align: center; border: 1.5pt solid #000000;">
                    <p class="MsoNormal" style="font-weight: bold; font-size: 9pt; text-transform: uppercase; margin: 0;">${escapeHtml(docTitle)}</p>
                    <p class="MsoNormal" style="font-size: 7pt; color: #334155; margin: 2pt 0 0 0;">${escapeHtml(subTitle)}</p>
                </td>
                <td width="22%" style="padding: 3pt 5pt; font-size: 6.5pt; border: 1.5pt solid #000000;">
                    <p class="MsoNormal" style="margin: 0;"><strong>CÓDIGO:</strong> ${escapeHtml(code)}</p>
                    <p class="MsoNormal" style="margin: 0;"><strong>VERSIÓN:</strong> 02</p>
                    <p class="MsoNormal" style="margin: 0;"><strong>FECHA:</strong> ${escapeHtml(@json($startDateFormatted))}</p>
                </td>
            </tr>
        </table>

        <table width="100%" border="1" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: 1pt solid #000000; font-size: 8pt; margin-bottom: 8pt;">
            <tr>
                <td width="16%" style="background: #f8fafc; font-weight: bold; padding: 2.5pt 4pt;">INSTALACIÓN:</td>
                <td width="34%" style="padding: 2.5pt 4pt;">${escapeHtml(inst)}</td>
                <td width="16%" style="background: #f8fafc; font-weight: bold; padding: 2.5pt 4pt;">EQUIPO:</td>
                <td width="34%" style="padding: 2.5pt 4pt;">${escapeHtml(eqName)}</td>
            </tr>
            <tr>
                <td style="background: #f8fafc; font-weight: bold; padding: 2.5pt 4pt;">FECHAS:</td>
                <td style="padding: 2.5pt 4pt;">${escapeHtml(dates)}</td>
                <td style="background: #f8fafc; font-weight: bold; padding: 2.5pt 4pt;">MARCA/MODELO:</td>
                <td style="padding: 2.5pt 4pt;">${escapeHtml(eqBrandModel)}</td>
            </tr>
            <tr>
                <td style="background: #f8fafc; font-weight: bold; padding: 2.5pt 4pt;">TIPO MONITOREO:</td>
                <td style="padding: 2.5pt 4pt;">${escapeHtml(monType)}</td>
                <td style="background: #f8fafc; font-weight: bold; padding: 2.5pt 4pt;">SERIE:</td>
                <td style="padding: 2.5pt 4pt;">${escapeHtml(eqSerial)}</td>
            </tr>
        </table>`;
    }

    function buildWordFooterSignaturesHtml() {
        const regBy = @json($registeredByHeader);
        return `
        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 24pt; font-size: 8pt;">
            <tr>
                <td width="50%" align="center" style="text-align: center; padding: 18pt 24pt 0 24pt;">
                    <div style="border-top: 1pt solid #000000; padding-top: 4pt;">
                        <p class="MsoNormal" style="font-weight: bold; margin: 0;">RESPONSABLE TÉCNICO DE CAMPO</p>
                        <p class="MsoNormal" style="margin: 0;">${escapeHtml(regBy)}</p>
                    </div>
                </td>
                <td width="50%" align="center" style="text-align: center; padding: 18pt 24pt 0 24pt;">
                    <div style="border-top: 1pt solid #000000; padding-top: 4pt;">
                        <p class="MsoNormal" style="font-weight: bold; margin: 0;">SUPERVISIÓN Y CONTROL DE CALIDAD</p>
                        <p class="MsoNormal" style="margin: 0;">PACHABOL MEDIO AMBIENTE & SEGURIDAD</p>
                    </div>
                </td>
            </tr>
        </table>`;
    }

    // 1. Descargar Word para Paso 1
    function generateStep1WordHtml() {
        const table = document.getElementById('tableStep1Points');
        let rowsHtml = '';
        if (table) {
            const trs = table.querySelectorAll('tbody tr');
            trs.forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 8) {
                    rowsHtml += `
                    <tr>
                        <td align="center" style="text-align: center; font-weight: bold;">${tds[0].innerText}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[1].innerText)}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[2].innerText)}</td>
                        <td align="center" style="text-align: center;">${tds[3].innerText}</td>
                        <td align="center" style="text-align: center;">${tds[4].innerText}</td>
                        <td align="center" style="text-align: center; font-size: 7.5pt;">${escapeHtml(tds[5].innerText)}</td>
                        <td align="right" style="text-align: right; padding-right: 4pt;">${tds[6].innerText}</td>
                        <td align="right" style="text-align: right; padding-right: 4pt;">${tds[7].innerText}</td>
                    </tr>`;
                }
            });
        }

        return `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
  @page WordSection1 { size: 612.0pt 792.0pt; margin: 36.0pt 36.0pt 36.0pt 36.0pt; }
  div.WordSection1 { page: WordSection1; }
  body { font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; margin: 0; padding: 0; }
  table { border-collapse: collapse; width: 100%; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt; }
  th, td { border: 1.0pt solid #000000; padding: 3.0pt 3.5pt; font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; }
  .th-blue { background-color: #0070c0; background: #0070c0; color: #ffffff; font-weight: bold; text-align: center; }
  p.MsoNormal { margin: 0cm; font-family: Arial, sans-serif; font-size: 8.5pt; }
</style>
</head>
<body>
<div class="WordSection1">
    <p class="MsoNormal" align="center" style="text-align: center; margin: 18pt 0 10pt 0; font-weight: bold; font-size: 11pt; text-transform: uppercase;">
        PUNTOS DE MONITOREO EVALUADOS
    </p>

    <table width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
        <colgroup>
            <col width="5%" style="width: 5%;">
            <col width="20%" style="width: 20%;">
            <col width="24%" style="width: 24%;">
            <col width="12%" style="width: 12%;">
            <col width="9%" style="width: 9%;">
            <col width="20%" style="width: 20%;">
            <col width="5%" style="width: 5%;">
            <col width="5%" style="width: 5%;">
        </colgroup>
        <thead>
            <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">N°</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">ÁREA DE TRABAJO</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PUNTO DE MEDICIÓN</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">FECHA</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">HORA</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">COORDENADAS UTM</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">%H.R.</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">T °C</th>
            </tr>
        </thead>
        <tbody>
            ${rowsHtml}
        </tbody>
    </table>
</div>
</body>
</html>`;
    }

    function downloadStep1Doc() {
        const html = generateStep1WordHtml();
        const blob = new Blob(['\ufeff' + html], { type: 'application/msword;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'Paso_1_Puntos_Monitoreo_Gases_Evaluados.doc';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // 2. Descargar Word para un Paso de Gas Específico (Limpio: Solo Título y Tabla)
    function generateGasStepWordHtml(gasKey, stepNum) {
        const gasData = gasReportsData[gasKey] || { info: { formula: gasKey.toUpperCase(), unit: 'ppm', eval_title: `EVALUACIÓN DE ${gasKey.toUpperCase()} EN EL AMBIENTE` } };
        const gasInfo = gasData.info;
        const table = document.getElementById(`tableStep${stepNum}Gas_${gasKey}`);
        let rowsHtml = '';
        if (table) {
            const trs = table.querySelectorAll('tbody tr');
            trs.forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 6) {
                    rowsHtml += `
                    <tr>
                        <td align="center" style="text-align: center; font-weight: bold;">${tds[0].innerText}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[1].innerText)}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[2].innerText)}</td>
                        <td align="right" style="text-align: right; padding-right: 10pt; font-weight: bold;">${tds[3].innerText}</td>
                        <td align="right" style="text-align: right; padding-right: 10pt;">${tds[4].innerText}</td>
                        <td align="center" style="text-align: center; font-weight: bold;">${tds[5].innerText}</td>
                    </tr>`;
                }
            });
        }

        return `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
  @page WordSection1 { size: 612.0pt 792.0pt; margin: 36.0pt 36.0pt 36.0pt 36.0pt; }
  div.WordSection1 { page: WordSection1; }
  body { font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; margin: 0; padding: 0; }
  table { border-collapse: collapse; width: 100%; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt; }
  th, td { border: 1.0pt solid #000000; padding: 3.5pt 4.0pt; font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; }
  .th-blue { background-color: #0070c0; background: #0070c0; color: #ffffff; font-weight: bold; text-align: center; }
  p.MsoNormal { margin: 0cm; font-family: Arial, sans-serif; font-size: 8.5pt; }
</style>
</head>
<body>
<div class="WordSection1">
    <p class="MsoNormal" align="center" style="text-align: center; margin: 18pt 0 10pt 0; font-weight: bold; font-size: 11pt; text-transform: uppercase;">
        ${escapeHtml(gasInfo.eval_title || `EVALUACIÓN DE ${gasInfo.formula} EN EL AMBIENTE`)}
    </p>

    <table width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
        <colgroup>
            <col width="5%" style="width: 5%;">
            <col width="25%" style="width: 25%;">
            <col width="28%" style="width: 28%;">
            <col width="16%" style="width: 16%;">
            <col width="14%" style="width: 14%;">
            <col width="12%" style="width: 12%;">
        </colgroup>
        <thead>
            <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">N°</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">ÁREA DE TRABAJO</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PUNTO DE MEDICIÓN</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">${escapeHtml(gasInfo.formula)} (${escapeHtml(gasInfo.unit)})</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">TLV (${escapeHtml(gasInfo.unit)})</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">CUMPLE</th>
            </tr>
        </thead>
        <tbody>
            ${rowsHtml}
        </tbody>
    </table>
</div>
</body>
</html>`;
    }

    function downloadGasStepDoc(gasKey, stepNum) {
        const html = generateGasStepWordHtml(gasKey, stepNum);
        const blob = new Blob(['\ufeff' + html], { type: 'application/msword;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Evaluacion_${gasKey.toUpperCase()}_Gases_ACGIH.doc`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    // 3. Descargar Informe Completo (Paso 1 + Pasos de Gas con saltos de página)
    function downloadAllGasesDoc() {
        let fullHtmlSections = '';

        // Sección 1: Puntos Evaluados
        fullHtmlSections += `
        <div class="WordSection1">
            <p class="MsoNormal" align="center" style="text-align: center; margin: 18pt 0 10pt 0; font-weight: bold; font-size: 11pt; text-transform: uppercase;">
                PUNTOS DE MONITOREO EVALUADOS
            </p>
            <table width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
                <colgroup>
                    <col width="5%" style="width: 5%;">
                    <col width="20%" style="width: 20%;">
                    <col width="24%" style="width: 24%;">
                    <col width="12%" style="width: 12%;">
                    <col width="9%" style="width: 9%;">
                    <col width="20%" style="width: 20%;">
                    <col width="5%" style="width: 5%;">
                    <col width="5%" style="width: 5%;">
                </colgroup>
                <thead>
                    <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">N°</th>
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">ÁREA DE TRABAJO</th>
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PUNTO DE MEDICIÓN</th>
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">FECHA</th>
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">HORA</th>
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">COORDENADAS UTM</th>
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">%H.R.</th>
                        <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">T °C</th>
                    </tr>
                </thead>
                <tbody>
                    ${(function() {
                        const table = document.getElementById('tableStep1Points');
                        let rows = '';
                        if (table) {
                            table.querySelectorAll('tbody tr').forEach(tr => {
                                const tds = tr.querySelectorAll('td');
                                if (tds.length >= 8) {
                                    rows += `<tr>
                                        <td align="center" style="text-align: center; font-weight: bold;">${tds[0].innerText}</td>
                                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[1].innerText)}</td>
                                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[2].innerText)}</td>
                                        <td align="center" style="text-align: center;">${tds[3].innerText}</td>
                                        <td align="center" style="text-align: center;">${tds[4].innerText}</td>
                                        <td align="center" style="text-align: center; font-size: 7.5pt;">${escapeHtml(tds[5].innerText)}</td>
                                        <td align="right" style="text-align: right; padding-right: 4pt;">${tds[6].innerText}</td>
                                        <td align="right" style="text-align: right; padding-right: 4pt;">${tds[7].innerText}</td>
                                    </tr>`;
                                }
                            });
                        }
                        return rows;
                    })()}
                </tbody>
            </table>
        </div>`;

        // Secciones 2 a N: Un Paso por Cada Gas (Limpio: Solo Título y Tabla)
        let stepIdx = 2;
        Object.keys(gasReportsData).forEach(gKey => {
            const gasData = gasReportsData[gKey];
            const gasInfo = gasData.info;
            const table = document.getElementById(`tableStep${stepIdx}Gas_${gKey}`);
            let rowsHtml = '';
            if (table) {
                table.querySelectorAll('tbody tr').forEach(tr => {
                    const tds = tr.querySelectorAll('td');
                    if (tds.length >= 6) {
                        rowsHtml += `<tr>
                            <td align="center" style="text-align: center; font-weight: bold;">${tds[0].innerText}</td>
                            <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[1].innerText)}</td>
                            <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[2].innerText)}</td>
                            <td align="right" style="text-align: right; padding-right: 10pt; font-weight: bold;">${tds[3].innerText}</td>
                            <td align="right" style="text-align: right; padding-right: 10pt;">${tds[4].innerText}</td>
                            <td align="center" style="text-align: center; font-weight: bold;">${tds[5].innerText}</td>
                        </tr>`;
                    }
                });
            }

            fullHtmlSections += `
            <br clear="all" style="page-break-before: always; mso-break-type: section-break;" />
            <div class="WordSection${stepIdx}">
                <p class="MsoNormal" align="center" style="text-align: center; margin: 18pt 0 10pt 0; font-weight: bold; font-size: 11pt; text-transform: uppercase;">
                    ${escapeHtml(gasInfo.eval_title || `EVALUACIÓN DE ${gasInfo.formula} EN EL AMBIENTE`)}
                </p>
                <table width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
                    <colgroup>
                        <col width="5%" style="width: 5%;">
                        <col width="25%" style="width: 25%;">
                        <col width="28%" style="width: 28%;">
                        <col width="16%" style="width: 16%;">
                        <col width="14%" style="width: 14%;">
                        <col width="12%" style="width: 12%;">
                    </colgroup>
                    <thead>
                        <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                            <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">N°</th>
                            <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">ÁREA DE TRABAJO</th>
                            <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PUNTO DE MEDICIÓN</th>
                            <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">${escapeHtml(gasInfo.formula)} (${escapeHtml(gasInfo.unit)})</th>
                            <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">TLV (${escapeHtml(gasInfo.unit)})</th>
                            <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">CUMPLE</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            </div>`;

            stepIdx++;
        });

        const completeDocHtml = `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
  @page { size: 612.0pt 792.0pt; margin: 36.0pt 36.0pt 36.0pt 36.0pt; }
  body { font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; margin: 0; padding: 0; }
  table { border-collapse: collapse; width: 100%; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt; }
  th, td { border: 1.0pt solid #000000; padding: 3.5pt 4.0pt; font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; }
  .th-blue { background-color: #0070c0; background: #0070c0; color: #ffffff; font-weight: bold; text-align: center; }
  p.MsoNormal { margin: 0cm; font-family: Arial, sans-serif; font-size: 8.5pt; }
</style>
</head>
<body>
    ${fullHtmlSections}
</body>
</html>`;

        const blob = new Blob(['\ufeff' + completeDocHtml], { type: 'application/msword;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'Informe_Completo_Monitoreo_Gases_ACGIH.doc';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
@endpush
