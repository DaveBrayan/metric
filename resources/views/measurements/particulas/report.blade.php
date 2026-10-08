@extends('layouts.app')

@section('title', 'Informe Oficial de Partículas Ocupacionales — ' . ($module->name ?? 'Monitoreo de Partículas'))

@push('styles')
    @metricStyle('particulas')
@endpush

@section('content')
<div class="part-report-outer-container">

    <!-- Encabezado de Navegación y Acciones Globales -->
    <div class="report-top-action-bar">
        <div class="top-action-left">
            <a href="{{ route('modules.particulas', $module->id) }}" class="btn-back-to-module">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver al Módulo</span>
            </a>
            <div class="module-breadcrumbs-trail">
                <span class="trail-project">{{ $module->project->name ?? 'Proyecto' }}</span>
                <span class="trail-separator">/</span>
                <span class="trail-module">Partículas Ocupacionales</span>
                <span class="trail-separator">/</span>
                <span class="trail-badge">Informe de Evaluación (TLVs ACGIH)</span>
            </div>
        </div>

        <div class="top-action-right">
            <button type="button" class="btn-global-download-all" onclick="downloadAllParticulasDoc()" title="Descargar informe completo con todos los pasos en un único archivo Microsoft Word (.doc)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span>Descargar Informe Completo (.doc)</span>
            </button>
        </div>
    </div>

    <!-- Stepper de Navegación entre Pasos (Carta Vertical) -->
    <div class="stepper-horizontal-card">
        <div class="stepper-nav-tabs">
            <!-- Paso 1: Puntos de Medición -->
            <button type="button" class="step-nav-btn active" id="step_tab_1" onclick="switchStep(1)">
                <div class="step-nav-number">1</div>
                <div class="step-nav-text">
                    <span class="step-nav-title">PUNTOS DE MEDICIÓN</span>
                    <span class="step-nav-subtitle">Registro General de Puntos UTM</span>
                </div>
            </button>

            <!-- Paso 2: Evaluación de Riesgos -->
            <button type="button" class="step-nav-btn" id="step_tab_2" onclick="switchStep(2)">
                <div class="step-nav-number">2</div>
                <div class="step-nav-text">
                    <span class="step-nav-title">EVALUACIÓN DE RIESGOS</span>
                    <span class="step-nav-subtitle">Concentración PM10 & PM2.5 (TLVs ACGIH)</span>
                </div>
            </button>
        </div>

        <div class="stepper-progress-track">
            <div class="stepper-progress-fill" id="stepperProgressBar" style="width: 50%;"></div>
        </div>
    </div>

    <!-- Contenedor Principal de Hojas de Pasos -->
    <div class="stepper-content-area">

        <!-- ========================================================================= -->
        <!-- PASO 1: PUNTOS DE MEDICIÓN (HOJA CARTA VERTICAL - LIMPIO: SOLO TÍTULO Y TABLA) -->
        <!-- ========================================================================= -->
        <div class="step-pane-content" id="step_pane_1" style="display: block;">
            <div class="part-sheet-wrapper">

                <!-- Barra de Herramientas del Paso 1 -->
                <div class="part-sheet-toolbar">
                    <div class="part-toolbar-left">
                        <span class="step-pane-badge">Paso 1 de 2 — Puntos de Medición</span>
                        <span id="step1AutoSaveBadge" class="header-auto-save-status">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Guardado</span>
                        </span>
                    </div>

                    <div class="part-toolbar-right">
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
                <div class="part-sheet-card" id="step1DocumentSheet" style="padding-top: 20mm;">

                    <!-- Título Oficial Centrado (Fiel a Imagen 2 del Usuario) -->
                    <div style="text-align: center; margin: 0 0 14px 0;">
                        <h2 style="font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase; margin: 0; font-family: Arial, sans-serif; letter-spacing: 0.4px;">
                            PUNTOS DE MEDICIÓN
                        </h2>
                    </div>

                    <!-- Tabla de Puntos de Medición con Coordenadas UTM E, N (Fiel a la Imagen 2) -->
                    <table class="part-table" id="tableStep1Points">
                        <colgroup>
                            <col style="width: 8%;">
                            <col style="width: 30%;">
                            <col style="width: 36%;">
                            <col style="width: 13%;">
                            <col style="width: 13%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th rowspan="2" class="part-th-blue">N°</th>
                                <th rowspan="2" class="part-th-blue">Área</th>
                                <th rowspan="2" class="part-th-blue">Punto de medición</th>
                                <th colspan="2" class="part-th-blue">Coordenadas UTM</th>
                            </tr>
                            <tr>
                                <th class="part-th-blue">E</th>
                                <th class="part-th-blue">N</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($measurementsList as $idx => $m)
                                @php
                                    $numDisp = $idx + 1;
                                    $areaDisp = $m->area ?: '—';
                                    $pointDisp = $m->punto_medicion ?: ($m->workstation ?: '—');

                                    // Coordenadas UTM
                                    $eastingStr = '—';
                                    $northingStr = '—';
                                    if ($m->utm_easting !== null) {
                                        $eastingStr = number_format((float)$m->utm_easting, 2, ',', '.');
                                    }
                                    if ($m->utm_northing !== null) {
                                        $northingStr = number_format((float)$m->utm_northing, 2, ',', '.');
                                    }

                                    // Si no tiene utm directo pero tiene location JSON
                                    if ($eastingStr === '—' && !empty($m->location)) {
                                        $locRaw = trim($m->location);
                                        if (str_starts_with($locRaw, '{') || str_starts_with($locRaw, '[')) {
                                            $d = json_decode($locRaw, true);
                                            if (is_array($d)) {
                                                if (!empty($d['easting'])) $eastingStr = number_format((float)$d['easting'], 2, ',', '.');
                                                if (!empty($d['northing'])) $northingStr = number_format((float)$d['northing'], 2, ',', '.');
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td align="center" style="text-align: center; font-weight: bold;">{{ $numDisp }}</td>
                                    <td style="text-align: left; padding-left: 6px;">{{ $areaDisp }}</td>
                                    <td style="text-align: left; padding-left: 6px;">{{ $pointDisp }}</td>
                                    <td align="center" style="text-align: center;">{{ $eastingStr }}</td>
                                    <td align="center" style="text-align: center;">{{ $northingStr }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" align="center" style="padding: 16px; color: #64748b; font-style: italic;">
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
        <!-- PASO 2: EVALUACIÓN DE RIESGOS (HOJA CARTA VERTICAL - FIEL A IMAGEN 3 Y 4)  -->
        <!-- ========================================================================= -->
        <div class="step-pane-content" id="step_pane_2" style="display: none;">
            <div class="part-sheet-wrapper">

                <!-- Barra de Herramientas del Paso 2 -->
                <div class="part-sheet-toolbar">
                    <div class="part-toolbar-left">
                        <span class="step-pane-badge">Paso 2 de 2 — Evaluación de Riesgos y Cumplimiento Normativo</span>
                        <span id="step2AutoSaveBadge" class="header-auto-save-status">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Guardado</span>
                        </span>
                    </div>

                    <div class="part-toolbar-right">
                        <button type="button" class="btn-toolbar-action btn-toolbar-subtle" onclick="printStep(2)"
                            title="Imprimir o guardar como PDF en hoja tamaño carta vertical">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect width="12" height="8" x="6" y="14"></rect>
                            </svg>
                            <span>Imprimir / PDF</span>
                        </button>
                        <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadStep2Doc()"
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

                <!-- Documento Hoja Carta Vertical Paso 2 -->
                <div class="part-sheet-card" id="step2DocumentSheet">

                    <!-- Metadatos de la Empresa y Equipo (Fiel a Imagen 3) -->
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 8pt; border: 1px solid #000;">
                        <tr>
                            <th style="width: 18%; background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">INSTALACIÓN:</th>
                            <td style="width: 32%; border: 1px solid #000; padding: 2px 4px;">
                                <input type="text" id="step2_installation_name" class="part-live-input" value="{{ $installationName }}" onchange="saveReportDataAsync()">
                            </td>
                            <th style="width: 18%; background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">EQUIPO:</th>
                            <td style="width: 32%; border: 1px solid #000; padding: 2px 4px;">
                                <input type="text" id="step2_equipment_name" class="part-live-input" value="{{ $equipmentName }}" onchange="saveReportDataAsync()">
                            </td>
                        </tr>
                        <tr>
                            <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">FECHA DE INICIO:</th>
                            <td style="border: 1px solid #000; padding: 2px 4px;">
                                {{ $startDateFormatted }}
                            </td>
                            <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">MARCA:</th>
                            <td style="border: 1px solid #000; padding: 2px 4px;">
                                <input type="text" id="step2_equipment_brand" class="part-live-input" value="{{ $equipmentBrand }}" onchange="saveReportDataAsync()">
                            </td>
                        </tr>
                        <tr>
                            <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">FECHA DE FINALIZACIÓN:</th>
                            <td style="border: 1px solid #000; padding: 2px 4px;">
                                {{ $endDateFormatted }}
                            </td>
                            <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">MODELO:</th>
                            <td style="border: 1px solid #000; padding: 2px 4px;">
                                <input type="text" id="step2_equipment_model" class="part-live-input" value="{{ $equipmentModel }}" onchange="saveReportDataAsync()">
                            </td>
                        </tr>
                        <tr>
                            <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">TIPO DE MONITOREO:</th>
                            <td style="border: 1px solid #000; padding: 2px 4px;">
                                RUTINARIO: &nbsp;&nbsp;&nbsp;&nbsp; SEGUIMIENTO: <strong>X</strong>
                            </td>
                            <th style="background: #f8fafc; border: 1px solid #000; text-align: left; padding: 3px 5px; font-weight: bold;">SERIE:</th>
                            <td style="border: 1px solid #000; padding: 2px 4px;">
                                <input type="text" id="step2_equipment_serial" class="part-live-input" value="{{ $equipmentSerial }}" onchange="saveReportDataAsync()">
                            </td>
                        </tr>
                    </table>

                    <!-- Título Oficial Centrado -->
                    <div style="text-align: center; margin: 10px 0 10px 0;">
                        <h2 style="font-size: 11pt; font-weight: bold; color: #000000; text-transform: uppercase; margin: 0; font-family: Arial, sans-serif; letter-spacing: 0.4px;">
                            EVALUACIÓN DE RIESGOS
                        </h2>
                    </div>

                    <!-- Tabla de Evaluación de Riesgos (Fiel a Imagen 3 y 4 del Usuario) -->
                    <table class="part-table" id="tableStep2Risks">
                        <colgroup>
                            <col style="width: 4%;">
                            <col style="width: 15%;">
                            <col style="width: 22%;">
                            <col style="width: 9%;">
                            <col style="width: 7%;">
                            <col style="width: 7%;">
                            <col style="width: 8%;">
                            <col style="width: 8%;">
                            <col style="width: 7%;">
                            <col style="width: 7%;">
                            <col style="width: 6%;">
                            <col style="width: 6%;">
                            <col style="width: 14%;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th rowspan="2" class="part-th-blue">N°</th>
                                <th rowspan="2" class="part-th-blue">Área</th>
                                <th rowspan="2" class="part-th-blue">Punto de medición</th>
                                <th rowspan="2" class="part-th-blue">Hora de Medición</th>
                                <th rowspan="2" class="part-th-blue">Temp °C</th>
                                <th rowspan="2" class="part-th-blue">H.R.%</th>
                                <th colspan="2" class="part-th-blue">Resultados obtenidos (µg/m³)</th>
                                <th colspan="2" class="part-th-blue">Límite Permisible (µg/m³)</th>
                                <th colspan="2" class="part-th-blue">Cumplimiento con la Norma</th>
                                <th rowspan="2" class="part-th-blue">Observaciones</th>
                            </tr>
                            <tr>
                                <th class="part-th-blue">PM 10</th>
                                <th class="part-th-blue">PM 2,5</th>
                                <th class="part-th-blue">PM 10</th>
                                <th class="part-th-blue">PM 2,5</th>
                                <th class="part-th-blue">PM 10</th>
                                <th class="part-th-blue">PM 2,5</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($measurementsList as $idx => $m)
                                @php
                                    $numDisp = $idx + 1;
                                    $areaDisp = $m->area ?: '—';
                                    $pointDisp = $m->punto_medicion ?: ($m->workstation ?: '—');
                                    $timeDisp = $m->measurement_time ?: '—';

                                    $tempDisp = $m->temperatura !== null ? (str_contains((string)$m->temperatura, '.') ? number_format((float)$m->temperatura, 1, ',', '') : number_format((float)$m->temperatura, 0, ',', '')) : '—';
                                    $hrDisp = $m->hr_percent !== null ? (str_contains((string)$m->hr_percent, '.') ? number_format((float)$m->hr_percent, 1, ',', '') : number_format((float)$m->hr_percent, 0, ',', '')) : '—';

                                    // PM10
                                    $pm10Prom = $m->pm10_prom !== null ? (float)$m->pm10_prom : null;
                                    $pm10Str = $pm10Prom !== null ? number_format($pm10Prom, 2, ',', '') : '—';
                                    $pm10Cumple = $pm10Prom !== null ? ($pm10Prom <= 10.0) : null;
                                    $pm10CumpleStr = $pm10Cumple !== null ? ($pm10Cumple ? 'SI' : 'NO') : '—';

                                    // PM2.5
                                    $pm25Prom = $m->pm25_prom !== null ? (float)$m->pm25_prom : null;
                                    $pm25Str = $pm25Prom !== null ? number_format($pm25Prom, 2, ',', '') : '—';
                                    $pm25Cumple = $pm25Prom !== null ? ($pm25Prom <= 3.0) : null;
                                    $pm25CumpleStr = $pm25Cumple !== null ? ($pm25Cumple ? 'SI' : 'NO') : '—';

                                    $obsDisp = $m->observations ?: ($m->observaciones ?: 'Todo bien');
                                @endphp
                                <tr>
                                    <td align="center" style="text-align: center; font-weight: bold;">{{ $numDisp }}</td>
                                    <td style="text-align: left; padding-left: 5px;">{{ $areaDisp }}</td>
                                    <td style="text-align: left; padding-left: 5px;">{{ $pointDisp }}</td>
                                    <td align="center" style="text-align: center;">{{ $timeDisp }}</td>
                                    <td align="center" style="text-align: center;">{{ $tempDisp }}</td>
                                    <td align="center" style="text-align: center;">{{ $hrDisp }}</td>
                                    <td align="right" style="text-align: right; padding-right: 5px; font-weight: bold;">{{ $pm10Str }}</td>
                                    <td align="right" style="text-align: right; padding-right: 5px; font-weight: bold;">{{ $pm25Str }}</td>
                                    <td align="center" style="text-align: center;">10(I)</td>
                                    <td align="center" style="text-align: center;">3(R)</td>
                                    <td align="center" style="text-align: center; font-weight: bold; {{ $pm10Cumple === false ? 'color: #dc2626;' : 'color: #000000;' }}">
                                        {{ $pm10CumpleStr }}
                                    </td>
                                    <td align="center" style="text-align: center; font-weight: bold; {{ $pm25Cumple === false ? 'color: #dc2626;' : 'color: #000000;' }}">
                                        {{ $pm25CumpleStr }}
                                    </td>
                                    <td style="text-align: left; padding-left: 5px; font-size: 7.5pt; line-height: 1.2;">{!! nl2br(e($obsDisp)) !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" align="center" style="padding: 16px; color: #64748b; font-style: italic;">
                                        No se han registrado mediciones de material particulado en este módulo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>

            </div>
        </div>

    </div>

</div>
@endsection

@push('styles')
<style>
    /* Configuración Estricta de Hoja Tamaño Carta Vertical */
    @page {
        size: 215.9mm 279.4mm;
        margin: 12mm 15mm 12mm 15mm;
    }

    @page WordSection1 {
        size: 612.0pt 792.0pt;
        margin: 36.0pt 36.0pt 36.0pt 36.0pt;
    }

    div.WordSection1 {
        page: WordSection1;
    }

    body {
        background-color: #f1f5f9;
        font-family: Arial, Helvetica, sans-serif;
    }

    .part-report-outer-container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 14px 16px 40px 16px;
        box-sizing: border-box;
    }

    /* 1. Barra Superior de Acciones */
    .report-top-action-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 14px;
        flex-wrap: wrap;
        gap: 12px;
    }

    .top-action-left {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-wrap: wrap;
    }

    .btn-back-to-module {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        padding: 7px 14px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .btn-back-to-module:hover {
        background: #f0f9ff;
        border-color: #10b9df;
        color: #0896b5;
        transform: translateX(-2px);
        box-shadow: 0 2px 8px rgba(16, 185, 223, 0.2);
    }

    .module-breadcrumbs-trail {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #64748b;
    }

    .trail-project {
        font-weight: 600;
        color: #475569;
    }

    .trail-separator {
        color: #cbd5e1;
    }

    .trail-module {
        font-weight: 700;
        color: #0896b5;
    }

    .trail-badge {
        background: #f0f9ff;
        color: #0896b5;
        font-size: 11px;
        font-weight: 800;
        padding: 2px 8px;
        border-radius: 6px;
        border: 1px solid #bae6fd;
        letter-spacing: 0.3px;
    }

    .btn-global-download-all {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #10b9df 0%, #0799a7 100%);
        color: #ffffff;
        border: none;
        padding: 8px 18px;
        border-radius: 9999px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 4px 14px rgba(16, 185, 223, 0.4);
    }

    .btn-global-download-all:hover {
        background: linear-gradient(135deg, #0ba3c5 0%, #058490 100%);
        box-shadow: 0 6px 18px rgba(16, 185, 223, 0.55);
        transform: translateY(-1px);
    }

    /* 2. Stepper de Navegación */
    .stepper-horizontal-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 10px 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        margin-bottom: 16px;
    }

    .stepper-nav-tabs {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .step-nav-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 12px;
        background: #f8fafc;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: left;
    }

    .step-nav-btn:hover {
        background: #f0f9ff;
        border-color: #bae6fd;
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
        background: #f0f9ff;
        border-color: #10b9df;
        box-shadow: 0 2px 10px rgba(16, 185, 223, 0.2);
    }

    .step-nav-btn.active .step-nav-number {
        background: linear-gradient(135deg, #10b9df 0%, #0799a7 100%);
        color: #ffffff;
        border-color: #10b9df;
        box-shadow: 0 2px 6px rgba(16, 185, 223, 0.4);
    }

    .step-nav-btn.active .step-nav-title {
        color: #0896b5;
        font-weight: 800;
    }

    .step-nav-btn.active .step-nav-subtitle {
        color: #0799a7;
        font-weight: 600;
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
        background: linear-gradient(90deg, #10b9df 0%, #0799a7 100%);
        transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* 3. Paneles de Pasos */
    .stepper-content-area {
        width: 100%;
        margin-top: 10px;
    }

    .step-pane-content {
        display: none;
        animation: fadeInStep 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes fadeInStep {
        from { opacity: 0; transform: translateY(4px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .part-sheet-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }

    .part-sheet-toolbar {
        width: 215.9mm;
        max-width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
        padding: 0 2px;
        box-sizing: border-box;
        flex-wrap: wrap;
        gap: 8px;
    }

    .part-toolbar-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .step-pane-badge {
        font-size: 12px;
        font-weight: 800;
        color: #0896b5;
        background: #f0f9ff;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #bae6fd;
    }

    .header-auto-save-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        font-weight: 700;
        color: #059669;
        background: #ecfdf5;
        padding: 3px 8px;
        border-radius: 6px;
        border: 1px solid #a7f3d0;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .header-auto-save-status.active {
        opacity: 1;
    }

    .part-toolbar-right {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-toolbar-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-toolbar-subtle {
        background: #ffffff;
        color: #334155;
        border-color: #cbd5e1;
    }

    .btn-toolbar-subtle:hover {
        background: #f0f9ff;
        border-color: #10b9df;
        color: #0896b5;
    }

    .btn-toolbar-word {
        background: linear-gradient(135deg, #10b9df 0%, #0799a7 100%);
        color: #ffffff;
        border: none;
        box-shadow: 0 2px 8px rgba(16, 185, 223, 0.35);
    }

    .btn-toolbar-word:hover {
        background: linear-gradient(135deg, #0ba3c5 0%, #058490 100%);
        box-shadow: 0 4px 12px rgba(16, 185, 223, 0.5);
        transform: translateY(-1px);
        color: #ffffff;
    }

    /* 4. Hoja Carta Oficial */
    .part-sheet-card {
        width: 215.9mm;
        min-height: 279.4mm;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        padding: 12mm 15mm 12mm 15mm;
        box-sizing: border-box;
        margin: 0 auto;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        color: #000000;
    }

    .part-live-input {
        width: 100%;
        border: none;
        background: transparent;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        color: #000000;
        padding: 2px;
        outline: none;
        box-sizing: border-box;
    }

    .part-live-input:focus {
        background: #f0fdf4;
        border-radius: 2px;
    }

    /* 5. Tablas Oficiales */
    .part-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid #000000;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        margin-top: 4px;
        table-layout: fixed;
    }

    .part-table th, 
    .part-table td {
        border: 1px solid #000000;
        padding: 3.5px 3px;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        color: #000000;
        box-sizing: border-box;
        line-height: 1.15;
    }

    .part-th-blue {
        background-color: #0070c0 !important;
        background: #0070c0 !important;
        color: #ffffff !important;
        font-weight: bold;
        text-align: center;
        padding: 5px 3px !important;
        font-size: 8pt;
        border: 1px solid #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* Print Media Queries */
    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .part-report-outer-container {
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .report-top-action-bar,
        .stepper-horizontal-card,
        .part-sheet-toolbar {
            display: none !important;
        }

        .part-sheet-card {
            border: none !important;
            box-shadow: none !important;
            width: 100% !important;
            min-height: auto !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .step-pane-content {
            display: none !important;
        }

        .step-pane-content.print-active {
            display: block !important;
        }

        .part-th-blue {
            background-color: #0070c0 !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    let currentStep = 1;
    const totalSteps = 2;

    function switchStep(stepNum) {
        currentStep = stepNum;

        // Actualizar Tabs
        document.querySelectorAll('.step-nav-btn').forEach((btn, idx) => {
            if (idx + 1 === stepNum) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Actualizar Barra de Progreso
        const progressPercent = (stepNum / totalSteps) * 100;
        const bar = document.getElementById('stepperProgressBar');
        if (bar) bar.style.width = `${progressPercent}%`;

        // Mostrar Panel Correspondiente
        document.querySelectorAll('.step-pane-content').forEach((pane, idx) => {
            if (idx + 1 === stepNum) {
                pane.style.display = 'block';
            } else {
                pane.style.display = 'none';
            }
        });

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // Auto-guardado asíncrono
    let saveTimeout = null;
    function saveReportDataAsync() {
        const badge1 = document.getElementById('step1AutoSaveBadge');
        const badge2 = document.getElementById('step2AutoSaveBadge');

        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(() => {
            const inst = document.getElementById('step2_installation_name') ? document.getElementById('step2_installation_name').value : '';
            const eqName = document.getElementById('step2_equipment_name') ? document.getElementById('step2_equipment_name').value : '';

            fetch("{{ route('modules.particulas.report.save', $module->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    installation_name: inst,
                    equipment_name: eqName
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (badge1) { badge1.classList.add('active'); setTimeout(() => badge1.classList.remove('active'), 2500); }
                    if (badge2) { badge2.classList.add('active'); setTimeout(() => badge2.classList.remove('active'), 2500); }
                }
            })
            .catch(err => console.error('Error guardando metadatos:', err));
        }, 500);
    }

    function printStep(stepNum) {
        document.querySelectorAll('.step-pane-content').forEach((pane, idx) => {
            if (idx + 1 === stepNum) {
                pane.classList.add('print-active');
            } else {
                pane.classList.remove('print-active');
            }
        });
        window.print();
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // ==========================================
    // EXPORTACIÓN A MICROSOFT WORD (.DOC)
    // ==========================================

    function generateStep1WordHtml() {
        const table = document.getElementById('tableStep1Points');
        let rowsHtml = '';
        if (table) {
            table.querySelectorAll('tbody tr').forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 5) {
                    rowsHtml += `<tr>
                        <td align="center" style="text-align: center; font-weight: bold;">${tds[0].innerText}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[1].innerText)}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[2].innerText)}</td>
                        <td align="center" style="text-align: center;">${escapeHtml(tds[3].innerText)}</td>
                        <td align="center" style="text-align: center;">${escapeHtml(tds[4].innerText)}</td>
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
        PUNTOS DE MEDICIÓN
    </p>

    <table width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
        <colgroup>
            <col width="8%" style="width: 8%;">
            <col width="30%" style="width: 30%;">
            <col width="36%" style="width: 36%;">
            <col width="13%" style="width: 13%;">
            <col width="13%" style="width: 13%;">
        </colgroup>
        <thead>
            <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">N°</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Área</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Punto de medición</th>
                <th colspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Coordenadas UTM</th>
            </tr>
            <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">E</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">N</th>
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
        a.download = 'Paso_1_Puntos_Medicion_Particulas.doc';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function generateStep2WordHtml() {
        const table = document.getElementById('tableStep2Risks');
        let rowsHtml = '';
        if (table) {
            table.querySelectorAll('tbody tr').forEach(tr => {
                const tds = tr.querySelectorAll('td');
                if (tds.length >= 13) {
                    const isPm10No = tds[10].innerText.trim() === 'NO';
                    const isPm25No = tds[11].innerText.trim() === 'NO';

                    rowsHtml += `<tr>
                        <td align="center" style="text-align: center; font-weight: bold;">${tds[0].innerText}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[1].innerText)}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt;">${escapeHtml(tds[2].innerText)}</td>
                        <td align="center" style="text-align: center;">${tds[3].innerText}</td>
                        <td align="center" style="text-align: center;">${tds[4].innerText}</td>
                        <td align="center" style="text-align: center;">${tds[5].innerText}</td>
                        <td align="right" style="text-align: right; padding-right: 4pt; font-weight: bold;">${tds[6].innerText}</td>
                        <td align="right" style="text-align: right; padding-right: 4pt; font-weight: bold;">${tds[7].innerText}</td>
                        <td align="center" style="text-align: center;">${tds[8].innerText}</td>
                        <td align="center" style="text-align: center;">${tds[9].innerText}</td>
                        <td align="center" style="text-align: center; font-weight: bold; ${isPm10No ? 'color: red;' : ''}">${tds[10].innerText}</td>
                        <td align="center" style="text-align: center; font-weight: bold; ${isPm25No ? 'color: red;' : ''}">${tds[11].innerText}</td>
                        <td align="left" style="text-align: left; padding-left: 4pt; font-size: 7.5pt;">${escapeHtml(tds[12].innerText)}</td>
                    </tr>`;
                }
            });
        }

        const instVal = document.getElementById('step2_installation_name') ? document.getElementById('step2_installation_name').value : '{{ $installationName }}';
        const eqVal = document.getElementById('step2_equipment_name') ? document.getElementById('step2_equipment_name').value : '{{ $equipmentName }}';
        const brandVal = document.getElementById('step2_equipment_brand') ? document.getElementById('step2_equipment_brand').value : '{{ $equipmentBrand }}';
        const modelVal = document.getElementById('step2_equipment_model') ? document.getElementById('step2_equipment_model').value : '{{ $equipmentModel }}';
        const serialVal = document.getElementById('step2_equipment_serial') ? document.getElementById('step2_equipment_serial').value : '{{ $equipmentSerial }}';

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
    <table width="100%" border="1" cellspacing="0" cellpadding="0" style="margin-bottom: 10pt;">
        <tr>
            <th width="18%" align="left" style="background-color: #f8fafc; font-weight: bold;">INSTALACIÓN:</th>
            <td width="32%">${escapeHtml(instVal)}</td>
            <th width="18%" align="left" style="background-color: #f8fafc; font-weight: bold;">EQUIPO:</th>
            <td width="32%">${escapeHtml(eqVal)}</td>
        </tr>
        <tr>
            <th align="left" style="background-color: #f8fafc; font-weight: bold;">FECHA DE INICIO:</th>
            <td>{{ $startDateFormatted }}</td>
            <th align="left" style="background-color: #f8fafc; font-weight: bold;">MARCA:</th>
            <td>${escapeHtml(brandVal)}</td>
        </tr>
        <tr>
            <th align="left" style="background-color: #f8fafc; font-weight: bold;">FECHA DE FINALIZACIÓN:</th>
            <td>{{ $endDateFormatted }}</td>
            <th align="left" style="background-color: #f8fafc; font-weight: bold;">MODELO:</th>
            <td>${escapeHtml(modelVal)}</td>
        </tr>
        <tr>
            <th align="left" style="background-color: #f8fafc; font-weight: bold;">TIPO DE MONITOREO:</th>
            <td>RUTINARIO: &nbsp;&nbsp;&nbsp;&nbsp; SEGUIMIENTO: <b>X</b></td>
            <th align="left" style="background-color: #f8fafc; font-weight: bold;">SERIE:</th>
            <td>${escapeHtml(serialVal)}</td>
        </tr>
    </table>

    <p class="MsoNormal" align="center" style="text-align: center; margin: 12pt 0 8pt 0; font-weight: bold; font-size: 11pt; text-transform: uppercase;">
        EVALUACIÓN DE RIESGOS
    </p>

    <table width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
        <colgroup>
            <col width="4%" style="width: 4%;">
            <col width="15%" style="width: 15%;">
            <col width="22%" style="width: 22%;">
            <col width="9%" style="width: 9%;">
            <col width="7%" style="width: 7%;">
            <col width="7%" style="width: 7%;">
            <col width="8%" style="width: 8%;">
            <col width="8%" style="width: 8%;">
            <col width="7%" style="width: 7%;">
            <col width="7%" style="width: 7%;">
            <col width="6%" style="width: 6%;">
            <col width="6%" style="width: 6%;">
            <col width="14%" style="width: 14%;">
        </colgroup>
        <thead>
            <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">N°</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Área</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Punto de medición</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Hora de Medición</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Temp °C</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">H.R.%</th>
                <th colspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Resultados obtenidos (µg/m³)</th>
                <th colspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Límite Permisible (µg/m³)</th>
                <th colspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Cumplimiento con la Norma</th>
                <th rowspan="2" style="color: #ffffff; font-weight: bold; background-color: #0070c0;">Observaciones</th>
            </tr>
            <tr class="th-blue" style="background-color: #0070c0; background: #0070c0; color: #ffffff;">
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PM 10</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PM 2,5</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PM 10</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PM 2,5</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PM 10</th>
                <th style="color: #ffffff; font-weight: bold; background-color: #0070c0;">PM 2,5</th>
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

    function downloadStep2Doc() {
        const html = generateStep2WordHtml();
        const blob = new Blob(['\ufeff' + html], { type: 'application/msword;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'Paso_2_Evaluacion_Riesgos_Particulas_ACGIH.doc';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function downloadAllParticulasDoc() {
        const step1Content = generateStep1WordHtml();
        const step2Content = generateStep2WordHtml();

        // Extraer contenido interno
        const extractDiv = (html) => {
            const m = html.match(/<div class="WordSection1">([\s\S]*?)<\/div>/i);
            return m ? m[1] : '';
        };

        const fullHtml = `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
  @page WordSection1 { size: 612.0pt 792.0pt; margin: 36.0pt 36.0pt 36.0pt 36.0pt; }
  @page WordSection2 { size: 612.0pt 792.0pt; margin: 36.0pt 36.0pt 36.0pt 36.0pt; }
  div.WordSection1 { page: WordSection1; }
  div.WordSection2 { page: WordSection2; }
  body { font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; margin: 0; padding: 0; }
  table { border-collapse: collapse; width: 100%; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt; }
  th, td { border: 1.0pt solid #000000; padding: 3.5pt 4.0pt; font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; }
  .th-blue { background-color: #0070c0; background: #0070c0; color: #ffffff; font-weight: bold; text-align: center; }
  p.MsoNormal { margin: 0cm; font-family: Arial, sans-serif; font-size: 8.5pt; }
</style>
</head>
<body>
<div class="WordSection1">
    ${extractDiv(step1Content)}
</div>
<br clear="all" style="page-break-before: always; mso-break-type: section-break;" />
<div class="WordSection2">
    ${extractDiv(step2Content)}
</div>
</body>
</html>`;

        const blob = new Blob(['\ufeff' + fullHtml], { type: 'application/msword;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'Informe_Completo_Particulas_Ocupacionales_ACGIH.doc';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
@endpush
