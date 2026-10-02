@extends('layouts.app')

@section('title', 'Informe Técnico — Carga de Fuego por Peso (NB 58005 / NTP 453) — Metric v2')

@push('styles')
    @metricStyle('fuego_pesos')
    <style>
        .fire-report-page {
            width: 100%;
            padding: 20px 0 60px 0;
            background-color: #f1f5f9;
            min-height: calc(100vh - 70px);
        }

        .fire-report-container {
            width: 100%;
            max-width: 1380px;
            margin: 0 auto;
            padding: 0 16px;
            box-sizing: border-box;
        }

        /* Stepper Header Bar */
        .fire-stepper-bar {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 14px 14px 14px;
            margin-bottom: 22px;
            box-shadow: 0 2px 8px rgba(15, 28, 46, 0.04);
        }

        .fire-stepper-track {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 12px;
        }

        .fire-step-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1.5px solid transparent;
            background: transparent;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: left;
            user-select: none;
        }

        .fire-step-btn:hover:not(.active) {
            background: #f8fafc;
            border-color: #e2e8f0;
        }

        .fire-step-btn.active {
            background: #fff7ed;
            border-color: #ea580c;
            box-shadow: 0 2px 8px rgba(234, 88, 12, 0.12);
        }

        .fire-step-number {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 13px;
            font-weight: 800;
            background: #f1f5f9;
            color: #64748b;
            border: 1.5px solid #cbd5e1;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .fire-step-btn.active .fire-step-number {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #ffffff;
            border-color: #c2410c;
            box-shadow: 0 2px 6px rgba(234, 88, 12, 0.35);
        }

        .fire-step-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .fire-step-title {
            font-size: 13px;
            font-weight: 800;
            color: #1e293b;
            line-height: 1.25;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fire-step-btn.active .fire-step-title {
            color: #ea580c;
        }

        .fire-step-subtitle {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .fire-step-btn.active .fire-step-subtitle {
            color: #c2410c;
        }

        .fire-stepper-progress {
            width: 100%;
            height: 4px;
            background: #f1f5f9;
            border-radius: 9999px;
            overflow: hidden;
        }

        .fire-stepper-fill {
            height: 100%;
            background: linear-gradient(90deg, #f97316 0%, #ea580c 100%);
            border-radius: 9999px;
            transition: width 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* Status Badge */
        .fire-auto-save-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            background-color: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            transition: all 0.25s ease;
        }

        .fire-auto-save-badge.saving {
            background-color: #fefce8;
            color: #ca8a04;
            border-color: #fef08a;
        }

        .fire-auto-save-badge.error {
            background-color: #fef2f2;
            color: #dc2626;
            border-color: #fecaca;
        }

        /* Toolbar */
        .fire-sheet-toolbar {
            width: 100%;
            max-width: 279.4mm;
            margin: 0 auto 14px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 8px 16px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(15, 28, 46, 0.04);
            box-sizing: border-box;
            flex-wrap: wrap;
        }

        .btn-toolbar-download-word {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 7px 16px;
            border-radius: 8px;
            background: linear-gradient(135deg, #1e40af 0%, #1d4ed8 100%);
            color: #ffffff;
            border: 1px solid #1e3a8a;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(30, 64, 175, 0.25);
            user-select: none;
            text-decoration: none;
        }

        .btn-toolbar-download-word:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(30, 64, 175, 0.35);
        }

        /* Contenedor Hoja Carta Horizontal (Exact Letter Landscape: 279.4mm x 215.9mm) */
        .fire-sheet-card {
            background: #ffffff;
            border: 1.5px solid #000000;
            box-shadow: 0 10px 30px -5px rgba(15, 28, 46, 0.09), 0 2px 6px -1px rgba(15, 28, 46, 0.04);
            width: 279.4mm;
            max-width: 100%;
            min-height: 215.9mm;
            box-sizing: border-box;
            padding: 8mm 9mm;
            border-radius: 2px;
            margin: 0 auto 30px auto;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000000;
            overflow-x: auto;
        }

        /* Inputs editables dentro de tablas */
        .fire-live-input {
            width: 100%;
            border: 1px solid transparent;
            border-radius: 2px;
            padding: 2px 4px;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            background: transparent;
            outline: none;
            box-sizing: border-box;
            text-align: inherit;
            transition: all 0.15s ease;
        }

        .fire-live-input:hover {
            border-color: #cbd5e1;
            background: #ffffff;
        }

        .fire-live-input:focus {
            border-color: #ea580c;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.15);
        }

        /* Tablas de Paso 1 (Excel Replica Image) */
        .fire-matrix-card {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(15, 28, 46, 0.05);
            padding: 20px;
            box-sizing: border-box;
            margin-bottom: 30px;
            overflow-x: auto;
        }

        .fire-matrix-top-grid {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 20px;
            margin-bottom: 18px;
            align-items: start;
        }

        .fire-top-info-box {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .fire-top-label-row {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .fire-top-label {
            font-weight: 800;
            font-size: 13px;
            color: #0f172a;
            white-space: nowrap;
        }

        .fire-top-input-box {
            border: 1.5px solid #000000;
            border-radius: 2px;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 13px;
            width: 100%;
            max-width: 260px;
            background: #ffffff;
            outline: none;
        }

        .fire-top-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
        }

        .fire-top-table th, .fire-top-table td {
            border: 1px solid #000000;
            padding: 3px 6px;
            vertical-align: middle;
        }

        .fire-top-table th {
            background: #ffffff;
            font-weight: 800;
            text-align: center;
            font-size: 8pt;
        }

        /* Main Red/Pink Matrix Table (Exact Match to Excel Snapshot) */
        .fire-main-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            color: #000000;
            table-layout: auto;
        }

        .fire-main-table th, .fire-main-table td {
            border: 1px solid #000000;
            padding: 3px 4px;
            vertical-align: middle;
            box-sizing: border-box;
            line-height: 1.25;
        }

        .fire-main-table thead th {
            background-color: #f28b82;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 7.5pt;
            letter-spacing: 0.2px;
            padding: 6px 3px;
            text-shadow: 0 1px 1px rgba(0,0,0,0.15);
        }

        .fire-main-table thead th.th-aux {
            background-color: #cfd8dc !important;
            color: #263238 !important;
            text-shadow: none;
        }

        .bg-soft-green {
            background-color: #e2efda !important;
        }

        .bg-soft-red {
            background-color: #f8cecc !important;
        }

        .bg-soft-yellow {
            background-color: #fff2cc !important;
        }

        .bg-soft-grey {
            background-color: #f1f5f9 !important;
        }

        .bg-white {
            background-color: #ffffff !important;
        }

        /* Step 2 & 3 Tables */
        .fire-summary-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000000;
            margin-bottom: 16px;
        }

        .fire-summary-table th, .fire-summary-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: middle;
        }

        .fire-summary-table th {
            background-color: #fff2cc;
            color: #000000;
            font-weight: bold;
            text-align: center;
            font-size: 8.5pt;
        }

        .fire-ext-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #000000;
            margin-bottom: 14px;
        }

        .fire-ext-table th, .fire-ext-table td {
            border: 1px solid #000000;
            padding: 3.5px 3px;
            vertical-align: middle;
            text-align: center;
        }

        .fire-ext-table th {
            background-color: #ffffff;
            color: #000000;
            font-weight: bold;
            font-size: 7pt;
            line-height: 1.2;
        }

        .step-pane {
            display: none;
        }

        .step-pane.active {
            display: block;
        }

        /* Mini Action Buttons in Table */
        .btn-mini-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #64748b;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-mini-action:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        .btn-add-mat {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #16a34a;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-top: 4px;
        }

        .btn-add-mat:hover {
            background: #dcfce7;
            border-color: #86efac;
            color: #15803d;
        }

        .btn-add-sec {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            border: 1px solid #ea580c;
            background: #fff7ed;
            color: #c2410c;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
            margin-top: 12px;
        }

        .btn-add-sec:hover {
            background: #ffedd5;
            border-color: #c2410c;
        }
    </style>
@endpush

@section('content')
<div class="fire-report-page">
    <div class="fire-report-container">

        {{-- 1. Encabezado y Navegación --}}
        <div class="illumination-header-banner" style="margin-bottom: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <a href="{{ route('modules.fire_weight', $module->id) }}" class="btn-secondary-subtle" style="padding: 5px 12px; font-size: 12px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12" />
                            <polyline points="12 19 5 12 12 5" />
                        </svg>
                        <span>Volver al Monitoreo</span>
                    </a>
                    <span style="font-size: 12px; color: #94a3b8;">/</span>
                    <span style="font-size: 12px; font-weight: 700; color: #ea580c; background: #fff7ed; padding: 2px 10px; border-radius: 9999px; border: 1px solid #ffedd5;">
                        {{ $installationName }}
                    </span>
                </div>
                <h1>
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z" />
                    </svg>
                    <span>Informe Técnico — Carga de Fuego por Peso</span>
                </h1>
                <p style="margin: 0; color: #64748b; font-size: 13.5px;">
                    Matriz por Materiales y Peso, Resumen de Macro Área y Dotación de Extintores (NB 58005 / NTP 453)
                </p>
            </div>

            <div class="header-action-group" style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn-secondary-subtle" onclick="syncFromDatabaseRecords()" 
                    title="Cargar y sincronizar sectores, materiales y equipos directamente desde los registros del monitoreo">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"></polyline>
                        <polyline points="1 20 1 14 7 14"></polyline>
                        <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                    </svg>
                    <span>Sincronizar Registros BD</span>
                </button>
                <span id="globalAutoSaveBadge" class="fire-auto-save-badge">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>Guardado</span>
                </span>
            </div>
        </div>

        {{-- 2. Barra Interactiva de Pasos (Stepper) --}}
        <div class="fire-stepper-bar">
            <div class="fire-stepper-track">
                <!-- Paso 1 -->
                <button type="button" class="fire-step-btn active" data-step="1" onclick="switchReportStep(1)">
                    <div class="fire-step-number">1</div>
                    <div class="fire-step-text">
                        <span class="fire-step-title">Paso 1: Matriz de Carga de Fuego por Peso</span>
                        <span class="fire-step-subtitle">Dinámico según Registros</span>
                    </div>
                </button>

                <!-- Paso 2 -->
                <button type="button" class="fire-step-btn" data-step="2" onclick="switchReportStep(2)">
                    <div class="fire-step-number">2</div>
                    <div class="fire-step-text">
                        <span class="fire-step-title">Paso 2: Resumen de Sectores</span>
                        <span class="fire-step-subtitle">Hoja Carta Horizontal (2 Tablas)</span>
                    </div>
                </button>

                <!-- Paso 3 -->
                <button type="button" class="fire-step-btn" data-step="3" onclick="switchReportStep(3)">
                    <div class="fire-step-number">3</div>
                    <div class="fire-step-text">
                        <span class="fire-step-title">Paso 3: Extintores</span>
                        <span class="fire-step-subtitle">Dotación y Asignación NB 58005</span>
                    </div>
                </button>
            </div>

            <div class="fire-stepper-progress">
                <div class="fire-stepper-fill" id="stepperProgressBar" style="width: 33.33%;"></div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- PASO 1: MATRIZ DE CARGA DE FUEGO POR PESO (DINÁMICA SEGÚN REGISTROS)       --}}
        {{-- ========================================================================= --}}
        <div class="step-pane active" id="pane_step_1">
            <div class="fire-matrix-card">

                <!-- Bloque Superior: Macro Área, Dimensiones y Equipos Contra Incendios -->
                <div class="fire-matrix-top-grid">
                    <div class="fire-top-info-box">
                        <div class="fire-top-label-row">
                            <span class="fire-top-label">MACRO AREA:</span>
                            <div class="fire-top-input-box" style="background: #ffffff; display: flex; align-items: center;">
                                <span style="font-weight: bold; font-size: 13px; color: #0f172a; text-transform: uppercase;">
                                    {{ $reportData['macroarea'] ?? 'PLANTA BAJA' }}
                                </span>
                            </div>
                        </div>

                        <div style="margin-top: 10px;">
                            <span class="fire-top-label" style="display: block; margin-bottom: 6px;">DIMENSIONES:</span>
                            <table style="border-collapse: collapse; border: 1.5px solid #000000; width: 220px; font-size: 8pt; font-family: Arial, sans-serif;">
                                <tr>
                                    <td style="border: 1px solid #000000; padding: 4px 8px; font-weight: bold; width: 80px; background: #f8fafc;">LT (m)</td>
                                    <td style="border: 1px solid #000000; padding: 4px 8px; font-weight: bold; text-align: center; background: #ffffff;">
                                        {{ !empty($reportData['dimensions']['lt']) ? number_format((float)$reportData['dimensions']['lt'], 2) : '—' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #000000; padding: 4px 8px; font-weight: bold; background: #f8fafc;">At (m)</td>
                                    <td style="border: 1px solid #000000; padding: 4px 8px; font-weight: bold; text-align: center; background: #ffffff;">
                                        {{ !empty($reportData['dimensions']['at']) ? number_format((float)$reportData['dimensions']['at'], 2) : '—' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- EQUIPOS CONTRA INCENDIOS (UBICACIÓN | TIPO | CANTIDAD | OBS.) -->
                    <div>
                        <div style="margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                            <span class="fire-top-label">EQUIPOS CONTRA INCENDIOS</span>
                        </div>
                        <table class="fire-top-table" id="fireEquipmentsTable">
                            <thead>
                                <tr>
                                    <th style="width: 28%; text-align: left; padding-left: 6px;">UBICACIÓN</th>
                                    <th style="width: 32%; text-align: left; padding-left: 6px;">TIPO</th>
                                    <th style="width: 14%; text-align: center;">CANTIDAD</th>
                                    <th style="width: 26%; text-align: left; padding-left: 6px;">OBS.</th>
                                </tr>
                            </thead>
                            <tbody id="fireEquipmentsTableBody">
                                <!-- Filas generadas dinámicamente según registros de BD -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Tabla Principal con Estructura Exacta del Excel del Usuario -->
                <div style="overflow-x: auto; margin-top: 15px;">
                    <table class="fire-main-table" id="fireMainMatrixTable">
                        <thead>
                            <tr>
                                <th style="width: 130px;">ÁREA</th>
                                <th style="width: 105px;">MATERIAL</th>
                                <th style="width: 170px;">DESCRIPCIÓN</th>
                                <th style="width: 75px;">Peso (Kg)</th>
                                <th style="width: 60px;">Cantidad</th>
                                <th style="width: 65px;">Hi<br>(Mcal/Kg)</th>
                                <th style="width: 45px;">Ci</th>
                                <th style="width: 145px;">ACTIVIDAD</th>
                                <th style="width: 50px;">L(m)</th>
                                <th style="width: 50px;">A(m)</th>
                                <th style="width: 80px;">PixHixCi</th>
                                <th style="width: 45px;">Ra</th>
                                <th style="width: 85px;">Qp<br>(Mcal/m2)</th>
                                <th style="width: 75px;">Nivel de<br>riesgo</th>
                                <th style="width: 40px;">N°</th>
                                <th style="width: 75px;" class="th-aux">Mult_1</th>
                                <th style="width: 60px;" class="th-aux">Area</th>
                            </tr>
                        </thead>
                        <tbody id="fireMainMatrixBody">
                            <!-- Filas generadas reactivamente -->
                        </tbody>
                    </table>
                </div>

                <div style="margin-top: 14px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 11.5px; color: #64748b;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>Sectores, materiales y áreas cargados directamente de los registros existentes del monitoreo (NB 58005 / NTP 453).</span>
                    </div>
                    <span style="font-size: 11px; color: #94a3b8;">
                        * Cambios calculados y guardados automáticamente en la base de datos
                    </span>
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- PASO 2: RESUMEN DE SECTORES Y MACRO ÁREA (HOJA CARTA HORIZONTAL)           --}}
        {{-- ========================================================================= --}}
        <div class="step-pane" id="pane_step_2">
            
            <!-- Barra de Herramientas de la Hoja -->
            <div class="fire-sheet-toolbar">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 800; color: #0f172a; text-transform: uppercase;">
                        Formato Oficial Carta Horizontal
                    </span>
                    <span id="step2AutoSaveBadge" class="fire-auto-save-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span>Guardado</span>
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="btn-toolbar-download-word" onclick="downloadStep2WordDoc()"
                        title="Descargar documento compatible con Microsoft Word (.doc) en formato horizontal y tamaño carta">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span>Descargar Word (.doc)</span>
                    </button>
                </div>
            </div>

            <!-- Hoja Carta Horizontal (Landscape) -->
            <div class="fire-sheet-card" id="step2SheetCard">
                
                <!-- Encabezado Oficial -->
                <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 8px; margin-bottom: 14px;">
                    <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                        PLANILLA OFICIAL — RESUMEN DE CARGA DE FUEGO POR SECTORES Y MACRO ÁREA
                    </div>
                    <div style="font-size: 8.5pt; font-weight: bold; color: #334155; margin-top: 3px;">
                        EVALUACIÓN POR PESO SEGÚN NORMA BOLIVIANA NB 58005 Y NTP 453
                    </div>
                </div>

                <!-- TABLA 1: RESUMEN POR SECTOR -->
                <div style="margin-bottom: 18px;">
                    <div style="font-weight: bold; font-size: 9pt; margin-bottom: 6px; text-transform: uppercase;">
                        1. Carga de Fuego por Área o Sector
                    </div>
                    <table class="fire-summary-table" id="step2TableSectors">
                        <thead>
                            <tr>
                                <th style="width: 50%; text-align: left; padding-left: 12px;">ÁREA O SECTOR</th>
                                <th style="width: 25%; text-align: center;">Área (m2)</th>
                                <th style="width: 25%; text-align: center;">Qp (Mcal/m2)</th>
                            </tr>
                        </thead>
                        <tbody id="step2TableSectorsBody">
                            <!-- Filas generadas reactivamente -->
                        </tbody>
                    </table>
                </div>

                <!-- TABLA 2: RESUMEN DE LA MACRO ÁREA -->
                <div>
                    <div style="font-weight: bold; font-size: 9pt; margin-bottom: 6px; text-transform: uppercase;">
                        2. Carga de Fuego Total de la Macro Área
                    </div>
                    <table class="fire-summary-table" id="step2TableMacro">
                        <thead>
                            <tr>
                                <th style="width: 35%; text-align: left; padding-left: 12px;">Macro área</th>
                                <th style="width: 20%; text-align: center;">Área total</th>
                                <th style="width: 20%; text-align: center;">QM (Mcal/m2)</th>
                                <th style="width: 15%; text-align: center;">Riesgo</th>
                                <th style="width: 10%; text-align: center;">Nivel</th>
                            </tr>
                        </thead>
                        <tbody id="step2TableMacroBody">
                            <!-- Fila generada reactivamente -->
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- PASO 3: EXTINTORES (DOTACIÓN Y ASIGNACIÓN SEGÚN NB 58005)                  --}}
        {{-- ========================================================================= --}}
        <div class="step-pane" id="pane_step_3">
            
            <!-- Barra de Herramientas de la Hoja -->
            <div class="fire-sheet-toolbar">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; font-weight: 800; color: #0f172a; text-transform: uppercase;">
                        Dotación de Extintores — Hoja Carta Horizontal
                    </span>
                    <span id="step3AutoSaveBadge" class="fire-auto-save-badge">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                        <span>Guardado</span>
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="btn-toolbar-download-word" onclick="downloadStep3WordDoc()"
                        title="Descargar documento compatible con Microsoft Word (.doc) en formato horizontal y tamaño carta">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                            <polyline points="7 10 12 15 17 10" />
                            <line x1="12" y1="15" x2="12" y2="3" />
                        </svg>
                        <span>Descargar Word (.doc)</span>
                    </button>
                </div>
            </div>

            <!-- Hoja Carta Horizontal (Landscape) -->
            <div class="fire-sheet-card" id="step3SheetCard">
                
                <!-- Encabezado Oficial -->
                <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 8px; margin-bottom: 14px;">
                    <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                        DETERMINACIÓN DE LA DOTACIÓN DE EXTINTORES CONTRA INCENDIOS
                    </div>
                    <div style="font-size: 8.5pt; font-weight: bold; color: #334155; margin-top: 3px;">
                        CÁLCULO DE POTENCIAL EXTINTOR Y ASIGNACIÓN NORMATIVA SEGÚN NB 58005
                    </div>
                </div>

                <!-- TABLA 1: MATRIZ COMPLETA DE EXTINTORES -->
                <div style="margin-bottom: 22px;">
                    <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 6px; text-transform: uppercase;">
                        1. Matriz de Cálculo de Potencial Extintor y Distancias Máximas de Traslado
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="fire-ext-table" id="step3TableFull">
                            <thead>
                                <tr>
                                    <th style="width: 85px;">Macro área</th>
                                    <th style="width: 50px;">A (m2)</th>
                                    <th style="width: 65px;">Qp MACRO<br>(Mcal/m2)</th>
                                    <th style="width: 55px;">Nivel de<br>riesgo</th>
                                    <th style="width: 75px;">Tipo de<br>extintor<br>aproximado</th>
                                    <th style="width: 55px;">Potencial<br>extintor A</th>
                                    <th style="width: 55px;">Potencial<br>extintor BC</th>
                                    <th style="width: 70px;">Superficie<br>cubierta para<br>fuegos clase A</th>
                                    <th style="width: 70px;">Superficie<br>cubierta para<br>fuegos clase B</th>
                                    <th style="width: 55px;">N° ext.<br>para fuegos<br>clase A</th>
                                    <th style="width: 55px;">N° ext.<br>Para fuegos<br>clase B</th>
                                    <th style="width: 65px;">CANTIDAD<br>TEÓRICA<br>REQUERIDA</th>
                                    <th style="width: 65px;">CANTIDAD<br>FINAL<br>ASIGNADA<br>EN PLANO</th>
                                    <th style="width: 48px;">Clase B</th>
                                    <th style="width: 48px;">Clase A</th>
                                    <th style="width: 48px;">Clase AB</th>
                                </tr>
                            </thead>
                            <tbody id="step3TableFullBody">
                                <!-- Generado reactivamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TABLA 2: RESUMEN DE DOTACIÓN -->
                <div style="margin-bottom: 18px;">
                    <div style="font-weight: bold; font-size: 8.5pt; margin-bottom: 6px; text-transform: uppercase;">
                        2. Resumen de Dotación Teórica y Asignación Final de Extintores
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="fire-ext-table" id="step3TableSummary">
                            <thead>
                                <tr style="background-color: #fff2cc;">
                                    <th style="width: 140px; background-color: #fff2cc;">Macro área</th>
                                    <th style="width: 80px; background-color: #fff2cc;">A (m2)</th>
                                    <th style="width: 95px; background-color: #fff2cc;">Qp MACRO<br>(Mcal/m2)</th>
                                    <th style="width: 80px; background-color: #fff2cc;">Nivel de<br>riesgo</th>
                                    <th style="width: 110px; background-color: #fff2cc;">Tipo de extintor<br>aproximado</th>
                                    <th style="width: 80px; background-color: #fff2cc;">Potencial<br>extintor A</th>
                                    <th style="width: 80px; background-color: #fff2cc;">Potencial<br>extintor BC</th>
                                    <th style="width: 95px; background-color: #fff2cc;">CANTIDAD<br>TEÓRICA<br>REQUERIDA</th>
                                    <th style="width: 95px; background-color: #fff2cc;">CANTIDAD<br>FINAL<br>ASIGNADA<br>EN PLANO</th>
                                </tr>
                            </thead>
                            <tbody id="step3TableSummaryBody">
                                <!-- Generado reactivamente -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Criterios Normativos NB 58005 -->
                <div style="border: 1px solid #cbd5e1; background: #f8fafc; padding: 8px 12px; font-size: 7pt; line-height: 1.35; border-radius: 4px; color: #334155;">
                    <b>Criterios Técnicos de Cálculo y Dotación por Peso (NB 58005):</b>
                    <ul style="margin: 3px 0 0 16px; padding: 0;">
                        <li>La carga de fuego unitaria de cada material se calcula según: <b>PixHixCi = (Peso × Cantidad) × Hi × Ci</b>.</li>
                        <li>La densidad de carga de fuego ponderada del sector es: <b>Qp = (Σ PixHixCi × Ra) / (L × A)</b>.</li>
                        <li>Las distancias máximas de traslado hacia el extintor más cercano son: <b>25.00 m</b> para Clase A, <b>15.25 m</b> para Clase B y <b>15.25 m</b> para Clase AB.</li>
                        <li>Los extintores deben ubicarse a una altura máxima de 1.50 m, libres de obstáculos y con señalización normalizada.</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1. Catálogo Completo de Materiales Combustibles (extra!$A$2:$D$126)
    // Fórmulas Excel:
    // - Hi (Mcal/Kg): =VLOOKUP(C10, extra!$A$2:$D$126, 3, 0)
    // - Ci:          =VLOOKUP(C10, extra!$A$2:$D$126, 4, 0)
    const MATERIALS_CATALOG = {
        "Aceite de algodón": { "hi": 9, "ci": 1 },
        "Aceite de creosota": { "hi": 9, "ci": 1.2 },
        "Aceite de lino": { "hi": 9, "ci": 1.2 },
        "Aceite mineral": { "hi": 10, "ci": 1 },
        "Aceite de oliva": { "hi": 10, "ci": 1 },
        "Aceite de parafina": { "hi": 10, "ci": 1 },
        "Acetaldehído": { "hi": 6, "ci": 1.6 },
        "Acetamida": { "hi": 5, "ci": 1 },
        "Acetato de amilo": { "hi": 8, "ci": 1.2 },
        "Acetato de polivinilo": { "hi": 5, "ci": 1 },
        "Acetona": { "hi": 7, "ci": 1.6 },
        "Acetileno": { "hi": 12, "ci": 1.6 },
        "Acetileno disuelto": { "hi": 4, "ci": 1.6 },
        "Ácido acético": { "hi": 4, "ci": 1.2 },
        "Ácido benzoico": { "hi": 6, "ci": 1 },
        "Acroleína": { "hi": 7, "ci": 1.6 },
        "Aguarrás": { "hi": 10, "ci": 1.2 },
        "Albúmina vegetal": { "hi": 6, "ci": 1 },
        "Alcanfor": { "hi": 9, "ci": 1.2 },
        "Alcohol alílico": { "hi": 8, "ci": 1.6 },
        "Alcohol amílico": { "hi": 10, "ci": 1.2 },
        "Alcohol butílico": { "hi": 8, "ci": 1.2 },
        "Alcohol cetílico": { "hi": 10, "ci": 1 },
        "Alcohol etílico": { "hi": 6, "ci": 1.6 },
        "Alcohol metílico": { "hi": 5, "ci": 1.6 },
        "Almidón": { "hi": 4, "ci": 1 },
        "Anhídrido acético": { "hi": 4, "ci": 1.2 },
        "Anilina": { "hi": 9, "ci": 1.2 },
        "Antraceno": { "hi": 10, "ci": 1 },
        "Antracita": { "hi": 8, "ci": 1 },
        "Azúcar": { "hi": 4, "ci": 1 },
        "Azufre": { "hi": 2, "ci": 1.2 },
        "Benzaldehído": { "hi": 8, "ci": 1.2 },
        "Bencina": { "hi": 10, "ci": 1.6 },
        "Benzol": { "hi": 10, "ci": 1.6 },
        "Benzofena": { "hi": 8, "ci": 1 },
        "Butano": { "hi": 11, "ci": 1.6 },
        "Cacao en polvo": { "hi": 4, "ci": 1 },
        "Café": { "hi": 4, "ci": 1 },
        "Cafeína": { "hi": 5, "ci": 1 },
        "Calcio": { "hi": 1, "ci": 1.2 },
        "Caucho": { "hi": 10, "ci": 1.2 },
        "Carbón": { "hi": 7.5, "ci": 1 },
        "Carbono": { "hi": 8, "ci": 1 },
        "Cartón": { "hi": 4, "ci": 1 },
        "Cartón asfáltico": { "hi": 5, "ci": 1.2 },
        "Celuloide": { "hi": 4, "ci": 1.6 },
        "Celulosa": { "hi": 4, "ci": 1 },
        "Cereales": { "hi": 4, "ci": 1 },
        "Chocolate": { "hi": 6, "ci": 1 },
        "Cicloheptano": { "hi": 11, "ci": 1.6 },
        "Ciclohexano": { "hi": 11, "ci": 1.6 },
        "Ciclopentano": { "hi": 11, "ci": 1.6 },
        "Ciclopropano": { "hi": 12, "ci": 1.6 },
        "Cloruro de polivinilo": { "hi": 5, "ci": 1 },
        "Cola celulósica": { "hi": 9, "ci": 1.2 },
        "Coque de hulla": { "hi": 7, "ci": 1 },
        "Cuero": { "hi": 5, "ci": 1 },
        "Dietilamina": { "hi": 10, "ci": 1.6 },
        "Dietilcetona": { "hi": 8, "ci": 1.6 },
        "Dietileter": { "hi": 9, "ci": 1.6 },
        "Difenil": { "hi": 10, "ci": 1.2 },
        "Dinamita (75 %)": { "hi": 1, "ci": 1.6 },
        "Dipenteno": { "hi": 11, "ci": 1.2 },
        "Ebonita": { "hi": 8, "ci": 1 },
        "Etano": { "hi": 12, "ci": 1.6 },
        "Éter amílico": { "hi": 10, "ci": 1.6 },
        "Éter etílico": { "hi": 8, "ci": 1.6 },
        "Fibra de coco": { "hi": 6, "ci": 1 },
        "Fenol": { "hi": 8, "ci": 1.2 },
        "Fósforo": { "hi": 6, "ci": 1.6 },
        "Furano": { "hi": 6, "ci": 1.6 },
        "Gasóleo": { "hi": 10, "ci": 1.2 },
        "Glicerina": { "hi": 4, "ci": 1 },
        "Grasas": { "hi": 10, "ci": 1 },
        "Gutapercha": { "hi": 11, "ci": 1.2 },
        "Harina de trigo": { "hi": 4, "ci": 1 },
        "Heptano": { "hi": 11, "ci": 1.6 },
        "Hexametileno": { "hi": 11, "ci": 1.6 },
        "Hexano": { "hi": 11, "ci": 1.6 },
        "Hidrógeno": { "hi": 34, "ci": 1.6 },
        "Hidruro de magnesio": { "hi": 4, "ci": 1.6 },
        "Hidruro de sodio": { "hi": 2, "ci": 1.6 },
        "Lana": { "hi": 5, "ci": 1 },
        "Leche en polvo": { "hi": 4, "ci": 1 },
        "Lino": { "hi": 4, "ci": 1 },
        "Linóleum": { "hi": 5, "ci": 1 },
        "Madera": { "hi": 4, "ci": 1 },
        "Magnesio": { "hi": 6, "ci": 1.6 },
        "Malta": { "hi": 4, "ci": 1 },
        "Mantequilla": { "hi": 9, "ci": 1 },
        "Metano": { "hi": 12, "ci": 1.6 },
        "Monóxido de carbono": { "hi": 2, "ci": 1.6 },
        "Nitrito de acetona": { "hi": 7, "ci": 1.6 },
        "Nitrocelulosa": { "hi": 2, "ci": 1.6 },
        "Octano": { "hi": 11, "ci": 1.6 },
        "Papel": { "hi": 4, "ci": 1 },
        "Parafina": { "hi": 11, "ci": 1 },
        "Pentano": { "hi": 12, "ci": 1.6 },
        "Petróleo": { "hi": 10, "ci": 1.2 },
        "Poliamida": { "hi": 7, "ci": 1 },
        "Policarbonato": { "hi": 7, "ci": 1 },
        "Poliéster": { "hi": 6, "ci": 1 },
        "Poliestireno": { "hi": 10, "ci": 1 },
        "Polietileno": { "hi": 10, "ci": 1 },
        "Poliisobutileno": { "hi": 11, "ci": 1 },
        "Politetrafluoretileno": { "hi": 1, "ci": 1 },
        "Poliuretano": { "hi": 6, "ci": 1.2 },
        "Propano": { "hi": 11, "ci": 1.6 },
        "Rayón": { "hi": 4, "ci": 1 },
        "Resina de pino": { "hi": 10, "ci": 1.2 },
        "Resina de fenol": { "hi": 6, "ci": 1 },
        "Resina de urea": { "hi": 5, "ci": 1 },
        "Seda": { "hi": 5, "ci": 1 },
        "Sisal": { "hi": 4, "ci": 1 },
        "Sodio": { "hi": 1, "ci": 1.6 },
        "Sulfuro de carbono": { "hi": 3, "ci": 1.6 },
        "Tabaco": { "hi": 4, "ci": 1 },
        "Té": { "hi": 4, "ci": 1 },
        "Tetralina": { "hi": 11, "ci": 1.2 },
        "Toluol": { "hi": 10, "ci": 1.6 },
        "Triacetato": { "hi": 4, "ci": 1 },
        "Turba": { "hi": 8, "ci": 1 },
        "Urea": { "hi": 2, "ci": 1 },
        "Viscosa": { "hi": 4, "ci": 1 }
    };

    // 2. Catálogo Completo de Actividades y su factor de riesgo Ra (extra!$F$1:$H$94)
    // Fórmula Excel:
    // - Ra:          =VLOOKUP(I10, extra!$F$1:$H$94, 3, 0)
    const ACTIVITIES_CATALOG = {
        "Aceites comestibles – fabricación": 1.5,
        "Almacenes - en general": 1,
        "Barnices - fabricación": 1.5,
        "Barnizados - taller": 1.5,
        "Bebidas - sin alcohol": 1,
        "Bebidas alcohólicas fabricación": 1.5,
        "Bebidas carbonatadas - fabricación": 1,
        "Betún - preparación": 1,
        "Carpintería": 1.5,
        "Café - torrefacto": 1.5,
        "Cartón - fabricación de cajas y elementos": 1.5,
        "Caucho - fabricación de objetos": 1.5,
        "Celuloide - fabricación": 1,
        "Cera - fabricación de artículos": 1,
        "Cerámica - taller": 1,
        "Cerveza - fabricación": 1.5,
        "Chocolate - fabricación": 1.5,
        "Colas - fabricación": 1,
        "Confección - talleres": 1,
        "Conservas - fabricación": 1,
        "Corcho - tratamiento": 1.5,
        "Cuerdas": 1.5,
        "Fabricación Cosméticos": 1,
        "Cuero - tratamiento y objetos": 1.5,
        "Destilerías - mat. inflamables": 1.5,
        "Disolventes - destilación": 1.5,
        "Ebanistería (sin alm. madera)": 1,
        "Electricista - taller": 1.5,
        "Electricidad - fabricación aparatos": 1,
        "Electricidad - reparación aparatos": 1.5,
        "Electrónica – fabricación aparatos": 1,
        "Electrónica - reparación aparatos": 1,
        "Motores eléctricos - fabricación": 1.5,
        "Orfebrería - fabricación": 1,
        "Panificación - elaboración y hornos de pan": 1,
        "Pasamanería - taller": 1,
        "Embarcaciones - fabricación": 1.5,
        "Escobas - fabricación": 1,
        "Esterillas - fabricación": 1,
        "Fertilizantes químicos - fabricación": 1.5,
        "Fibras artificiales": 1.5,
        "Producción manipulación": 1,
        "Forjas y herrerías": 1,
        "Frigoríficos - cámaras": 1,
        "Fundición de metales": 1,
        "Galvanoplástica": 1,
        "Géneros de punto - fabricación": 1.5,
        "Grasas comestibles - fabricación": 1.5,
        "Imprenta": 1.5,
        "Industrias químicas": 3,
        "Juguetes - fabricación": 1.5,
        "Laboratorios eléctricos": 1,
        "Laboratorios físicos y metalúrgicos": 1,
        "Laboratorios fotográficos": 1,
        "Laboratorios químicos": 1.5,
        "Licores - fabricación": 1.5,
        "Madera – fabricación contrachapados": 1.5,
        "Mampostería - fabricación": 1,
        "Mantequilla - fabricación": 1,
        "Máquinas - fabricación": 1.5,
        "Marcos - fabricación": 1.5,
        "Materiales usados - tratamiento": 1.5,
        "Mecanización de metales": 1,
        "Medias - fabricación": 1.5,
        "Medicamentos - laboratorios": 1,
        "Metales - fabricación de artículos": 1,
        "Muebles - fabricación (madera)": 1.5,
        "Muebles - fabricación (metal)": 1,
        "Molinos harineros": 1.5,
        "Resinas sintéticas - fabricación": 1.5,
        "Sacos - fabricación": 1,
        "Seda artificial - fabricación": 1.5,
        "Taller mecánico": 1,
        "Papel - fabricación": 1,
        "Pastas alimenticias - fabricación": 1,
        "Pinturas - talleres": 1.5,
        "Pinturas y barnices - fabricación": 3,
        "Pinceles y cepillos - fabricación": 3,
        "Pirotecnia - fabricación": 1.5,
        "Plancha - taller": 3,
        "Placas de resina sintética -fabricación": 1,
        "Productos alimenticios - fabricación": 1.5,
        "Reparaciones - taller": 1,
        "Tapicería": 1.5,
        "Teatro": 1,
        "Tejidos - fábricas": 1,
        "Telefónica - central": 1,
        "Tintas de imprenta - fabricación": 1.5,
        "Tintorerías": 1,
        "Transformadores - construcción": 1,
        "Vidrio - fabricación de artículos": 1,
        "Vulcanización": 1.5,
        "Zapatos - fabricación": 1.5
    };

    function lookupMaterialValues(material) {
        if (!material) return { hi: 4.0, ci: 1.0 };
        
        // 1. Búsqueda exacta
        if (MATERIALS_CATALOG[material]) {
            return MATERIALS_CATALOG[material];
        }

        const clean = normalizeText(material);
        
        // 2. Búsqueda normalizada
        for (const [key, val] of Object.entries(MATERIALS_CATALOG)) {
            if (normalizeText(key) === clean) {
                return val;
            }
        }

        // 3. Búsqueda por subcadena
        for (const [key, val] of Object.entries(MATERIALS_CATALOG)) {
            const kClean = normalizeText(key);
            if (clean.includes(kClean) || kClean.includes(clean)) {
                return val;
            }
        }
        
        return { hi: 4.0, ci: 1.0 };
    }

    function lookupActivityRa(actividad) {
        if (!actividad) return 1.0;
        
        // 1. Búsqueda exacta
        if (ACTIVITIES_CATALOG[actividad] !== undefined) {
            return ACTIVITIES_CATALOG[actividad];
        }

        const clean = normalizeText(actividad);

        // 2. Búsqueda normalizada
        for (const [key, val] of Object.entries(ACTIVITIES_CATALOG)) {
            if (normalizeText(key) === clean) {
                return val;
            }
        }

        // 3. Búsqueda por subcadena
        for (const [key, val] of Object.entries(ACTIVITIES_CATALOG)) {
            const kClean = normalizeText(key);
            if (clean.includes(kClean) || kClean.includes(clean)) {
                return val;
            }
        }

        return 1.0;
    }

    function normalizeText(txt) {
        if (!txt) return '';
        return txt.toString().toLowerCase()
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .replace(/[–—\-_\/\.\,]/g, " ")
            .replace(/\s+/g, " ")
            .trim();
    }

    /**
     * Fórmula: =SI(P10<=200;"BAJO";SI(P10<=800;"MEDIO";"ALTO"))
     */
    function calculateRiskLevel(qp) {
        const val = parseFloat(qp || 0);
        if (val <= 200) return 'BAJO';
        if (val <= 800) return 'MEDIO';
        return 'ALTO';
    }

    /**
     * Fórmula: =+SI(P10<=100;"1";SI(P10<=200;"2";SI(P10<=300;"3";SI(P10<=400;"4";SI(P10<=800;"5";SI(P10<=1600;"6";SI(P10<=3200;"7";"8")))))))
     */
    function calculateRiskLevelNum(qp) {
        const val = parseFloat(qp || 0);
        if (val <= 100) return 1;
        if (val <= 200) return 2;
        if (val <= 300) return 3;
        if (val <= 400) return 4;
        if (val <= 800) return 5;
        if (val <= 1600) return 6;
        if (val <= 3200) return 7;
        return 8;
    }

    // Catálogos y Tablas Normativas de Extintores (NB 58005)
    const EXTINGUISHER_TYPES_CATALOG = {
        "4,5Kg ABC": { extintor: "ABC", pot_a: "4A", pot_b: "60B" },
        "9Kg ABC":   { extintor: "ABC", pot_a: "10A", pot_b: "80B" },
        "50Kg ABC":  { extintor: "ABC", pot_a: "40A", pot_b: "160B" },
        "4,5Kg BC":  { extintor: "CO2", pot_a: "", pot_b: "10B" },
        "7Kg BC":    { extintor: "CO2", pot_a: "", pot_b: "20B" },
        "9Kg BC":    { extintor: "CO2", pot_a: "", pot_b: "20B" },
        "6L H2O":    { extintor: "Agua", pot_a: "1A", pot_b: "" },
        "9,5L H2O":  { extintor: "Agua", pot_a: "2A", pot_b: "" }
    };

    const COVERED_AREA_A_TABLE = {
        "1A":  { dist: 23, BAJO: 280,  MEDIO: 0,    ALTO: 0 },
        "2A":  { dist: 23, BAJO: 560,  MEDIO: 280,  ALTO: 186 },
        "3A":  { dist: 23, BAJO: 840,  MEDIO: 420,  ALTO: 280 },
        "4A":  { dist: 23, BAJO: 1050, MEDIO: 560,  ALTO: 370 },
        "6A":  { dist: 23, BAJO: 1050, MEDIO: 840,  ALTO: 560 },
        "10A": { dist: 23, BAJO: 1050, MEDIO: 1050, ALTO: 840 },
        "20A": { dist: 23, BAJO: 1050, MEDIO: 1050, ALTO: 1050 },
        "40A": { dist: 23, BAJO: 1050, MEDIO: 1050, ALTO: 1050 }
    };

    const COVERED_AREA_B_TABLE = {
        "5B":   { BAJO: 242.5, MEDIO: 0,     ALTO: 0,      dist_BAJO: 9.15,  dist_MEDIO: 5.49,  dist_ALTO: 1.98 },
        "10B":  { BAJO: 673.5, MEDIO: 242.5, ALTO: 0,      dist_BAJO: 15.25, dist_MEDIO: 9.15,  dist_ALTO: 3.29 },
        "20B":  { BAJO: 673.5, MEDIO: 673.5, ALTO: 0,      dist_BAJO: 15.25, dist_MEDIO: 15.25, dist_ALTO: 5.49 },
        "40B":  { BAJO: 673.5, MEDIO: 673.5, ALTO: 242.5,  dist_BAJO: 15.25, dist_MEDIO: 15.25, dist_ALTO: 9.15 },
        "60B":  { BAJO: 673.5, MEDIO: 673.5, ALTO: 431.1,  dist_BAJO: 15.25, dist_MEDIO: 15.25, dist_ALTO: 12.2 },
        "80B":  { BAJO: 673.5, MEDIO: 673.5, ALTO: 673.5,  dist_BAJO: 15.25, dist_MEDIO: 15.25, dist_ALTO: 15.25 },
        "160B": { BAJO: 673.5, MEDIO: 673.5, ALTO: 2182.3, dist_BAJO: 15.25, dist_MEDIO: 15.25, dist_ALTO: 27.45 }
    };

    function calculateExtinguisherValues(extType, totalArea, riskLevel) {
        const normRisk = (riskLevel || 'BAJO').toUpperCase().trim();
        const riskKey = normRisk.includes('ALTO') ? 'ALTO' : (normRisk.includes('MEDIO') ? 'MEDIO' : 'BAJO');

        const typeData = EXTINGUISHER_TYPES_CATALOG[extType] || EXTINGUISHER_TYPES_CATALOG["50Kg ABC"];
        const potA = typeData.pot_a || '';
        const potBC = typeData.pot_b || '';

        let covA = 0;
        let distA = 23;
        if (potA && COVERED_AREA_A_TABLE[potA]) {
            covA = COVERED_AREA_A_TABLE[potA][riskKey] || 0;
            distA = COVERED_AREA_A_TABLE[potA].dist || 23;
        }

        let covB = 0;
        let distB = 15.25;
        if (potBC && COVERED_AREA_B_TABLE[potBC]) {
            covB = COVERED_AREA_B_TABLE[potBC][riskKey] || 0;
            const distProp = `dist_${riskKey}`;
            distB = COVERED_AREA_B_TABLE[potBC][distProp] || 15.25;
        }

        const distAB = (distB > 0 && distB <= distA) ? distB : 15.25;

        const numExtA = (covA > 0) ? (totalArea / covA) : 0;
        const numExtB = (covB > 0) ? (totalArea / covB) : 0;

        const maxExt = Math.max(numExtA, numExtB);
        const theorQty = (totalArea > 0 && maxExt > 0) ? Math.ceil(maxExt) : 1;

        return {
            extinguisher_type: extType,
            potential_a: potA,
            potential_bc: potBC,
            covered_area_a: covA,
            covered_area_b: covB,
            num_ext_a: Math.round(numExtA * 100) / 100,
            num_ext_b: Math.round(numExtB * 100) / 100,
            theoretical_qty: theorQty,
            distance_b: distB,
            distance_a: distA,
            distance_ab: distAB
        };
    }

    // 3. Configuración y Estado Global
    window.FIRE_REPORT_CONFIG = {
        moduleId: {{ $module->id ?? 1 }},
        csrfToken: "{{ csrf_token() }}",
        saveUrl: "{{ route('modules.fire_weight.report.save', $module->id ?? 1) }}",
        installationName: @json($installationName ?? 'Planta Industrial Central'),
        registeredBy: @json($registeredByHeader ?? 'Ing. Reynaldo Pachabol'),
        reportData: @json($reportData ?? []),
        dbSectors: @json($dbSectors ?? []),
        dbEquipments: @json($dbEquipments ?? [])
    };

    let currentStep = 1;
    let autoSaveTimer = null;
    let localData = JSON.parse(JSON.stringify(window.FIRE_REPORT_CONFIG.reportData || {}));

    if (!localData.sectors || !Array.isArray(localData.sectors)) {
        localData.sectors = [];
    }
    if (!localData.macroarea) localData.macroarea = 'PLANTA BAJA';
    if (!localData.dimensions) localData.dimensions = { lt: '', at: '' };
    if (!localData.fire_equipments) localData.fire_equipments = [];
    if (!localData.extinguishers) localData.extinguishers = {};

    document.addEventListener('DOMContentLoaded', function () {
        if ((!localData.dimensions || !localData.dimensions.lt || localData.dimensions.lt === '') && localData.sectors && localData.sectors.length > 0) {
            if (!localData.dimensions) localData.dimensions = {};
            localData.dimensions.lt = localData.sectors[0].lt || '';
            localData.dimensions.at = localData.sectors[0].at || '';
            
            const ltInp = document.getElementById('macroLtInput');
            const atInp = document.getElementById('macroAtInput');
            if (ltInp && localData.dimensions.lt) ltInp.value = localData.dimensions.lt;
            if (atInp && localData.dimensions.at) atInp.value = localData.dimensions.at;
        }

        renderEquipmentsTable();
        recalculateAll();
        renderStep1Matrix();
        renderStep2Tables();
        renderStep3Tables();
    });

    // 4. Stepper Switcher
    function switchReportStep(stepNumber) {
        currentStep = stepNumber;
        document.querySelectorAll('.fire-step-btn').forEach(btn => {
            if (parseInt(btn.getAttribute('data-step')) === stepNumber) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        const progressPercent = stepNumber === 1 ? '33.33%' : (stepNumber === 2 ? '66.66%' : '100%');
        const fillBar = document.getElementById('stepperProgressBar');
        if (fillBar) fillBar.style.width = progressPercent;

        document.querySelectorAll('.step-pane').forEach((pane, idx) => {
            if (idx + 1 === stepNumber) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });

        if (stepNumber === 2) renderStep2Tables();
        if (stepNumber === 3) renderStep3Tables();
    }

    // 5. Renderizado de Equipos Contra Incendios (Solo Lectura)
    function renderEquipmentsTable() {
        const tbody = document.getElementById('fireEquipmentsTableBody');
        if (!tbody) return;

        const eqs = (localData.fire_equipments && localData.fire_equipments.length > 0)
            ? localData.fire_equipments
            : (window.FIRE_REPORT_CONFIG.dbEquipments || []);

        if (eqs.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #64748b; padding: 6px;">Sin equipos registrados</td></tr>`;
            return;
        }

        tbody.innerHTML = eqs.map(eq => `
            <tr>
                <td style="padding: 4px 6px; font-weight: bold; color: #0f172a;">${escapeHtml(eq.ubicacion || '—')}</td>
                <td style="padding: 4px 6px; color: #334155;">${escapeHtml(eq.tipo || '—')}</td>
                <td style="text-align: center; padding: 4px 6px; font-weight: bold; color: #0f172a;">${eq.cantidad || 1}</td>
                <td style="padding: 4px 6px; color: #475569;">${escapeHtml(eq.obs || '—')}</td>
            </tr>
        `).join('');
    }

    // 6. Renderizado de Matriz Principal de Paso 1 (Solo Lectura, Registro Fiel de BD)
    function renderStep1Matrix() {
        const tbody = document.getElementById('fireMainMatrixBody');
        if (!tbody) return;

        if (!localData.sectors || localData.sectors.length === 0) {
            tbody.innerHTML = `<tr><td colspan="17" style="text-align: center; color: #64748b; padding: 28px; font-weight: 500;">No hay sectores registrados para este monitoreo. Registre los sectores y materiales desde la aplicación móvil.</td></tr>`;
            return;
        }

        let html = '';

        localData.sectors.forEach((sec, sIdx) => {
            const materials = (sec.materials && sec.materials.length > 0) 
                ? sec.materials 
                : [{ material: '—', descripcion: '—', peso_kg: 0, cantidad: 1, hi: 4, ci: 1, pixhixci: 0 }];
            const rowCount = materials.length;

            materials.forEach((mat, mIdx) => {
                const isFirst = (mIdx === 0);
                const pixhixci = ((parseFloat(mat.peso_kg || 0) * parseFloat(mat.cantidad || 1)) * parseFloat(mat.hi || 0) * parseFloat(mat.ci || 1));
                const formattedPix = isNaN(pixhixci) ? '0' : (pixhixci % 1 === 0 ? pixhixci.toFixed(0) : pixhixci.toFixed(1));

                html += `<tr>`;

                // 1. ÁREA / SECTOR (Rowspan)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-soft-green" style="font-weight: 800; text-align: center; vertical-align: middle; padding: 6px 6px; font-size: 8pt; text-transform: uppercase; color: #0f172a;">
                            ${escapeHtml(sec.name || '—')}
                        </td>
                    `;
                }

                // 2. MATERIAL (Soft Green)
                html += `
                    <td class="bg-soft-green" style="font-weight: 600; padding: 4px 6px; color: #0f172a;">
                        ${escapeHtml(mat.material || '—')}
                    </td>
                `;

                // 3. DESCRIPCIÓN (Soft Green)
                html += `
                    <td class="bg-soft-green" style="padding: 4px 6px; color: #334155;">
                        ${escapeHtml(mat.descripcion || '—')}
                    </td>
                `;

                // 4. Peso (Kg) (White)
                html += `
                    <td class="bg-white" style="text-align: right; padding: 4px 6px; font-weight: 500; color: #0f172a;">
                        ${mat.peso_kg !== undefined && mat.peso_kg !== '' ? parseFloat(mat.peso_kg).toFixed(2) : '0.00'}
                    </td>
                `;

                // 5. Cantidad (White)
                html += `
                    <td class="bg-white" style="text-align: right; padding: 4px 6px; font-weight: 500; color: #0f172a;">
                        ${mat.cantidad !== undefined ? parseFloat(mat.cantidad).toFixed(0) : '1'}
                    </td>
                `;

                // 6. Hi (Mcal/Kg) (Soft Red)
                html += `
                    <td class="bg-soft-red" style="text-align: center; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                        ${parseFloat(mat.hi || 4).toFixed(2)}
                    </td>
                `;

                // 7. Ci (Soft Red)
                html += `
                    <td class="bg-soft-red" style="text-align: center; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                        ${parseFloat(mat.ci || 1.0).toFixed(2)}
                    </td>
                `;

                // 8. ACTIVIDAD (Soft Yellow, Rowspan on first row)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-soft-yellow" style="vertical-align: middle; padding: 4px 6px; font-weight: 600; color: #0f172a;">
                            ${escapeHtml(sec.actividad || 'Almacenes - en general')}
                        </td>
                    `;
                }

                // 9. L(m) (White, Rowspan on first row)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-white" style="text-align: center; vertical-align: middle; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                            ${sec.lt !== undefined && sec.lt !== '' ? parseFloat(sec.lt).toFixed(2) : '—'}
                        </td>
                    `;
                }

                // 10. A(m) (White, Rowspan on first row)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-white" style="text-align: center; vertical-align: middle; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                            ${sec.at !== undefined && sec.at !== '' ? parseFloat(sec.at).toFixed(2) : '—'}
                        </td>
                    `;
                }

                // 11. PixHixCi (Soft Red, per material row)
                html += `
                    <td class="bg-soft-red" style="text-align: right; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                        ${formattedPix}
                    </td>
                `;

                // 12. Ra (White, Rowspan on first row)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-white" style="text-align: center; vertical-align: middle; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                            ${parseFloat(sec.ra || 1.0).toFixed(2)}
                        </td>
                    `;
                }

                // 13. Qp (Mcal/m2) (White, Rowspan on first row)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-white" style="text-align: center; vertical-align: middle; font-weight: 800; font-size: 8.5pt; color: #0f172a; padding: 4px 6px;">
                            ${parseFloat(sec.qp || 0).toFixed(2)}
                        </td>
                    `;
                }

                // 14. Nivel de riesgo (Soft Red, Rowspan on first row)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-soft-red" style="text-align: center; vertical-align: middle; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                            ${sec.risk_level || 'BAJO'}
                        </td>
                    `;
                }

                // 15. N° (Soft Red, Rowspan on first row)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" class="bg-soft-red" style="text-align: center; vertical-align: middle; font-weight: bold; padding: 4px 6px; color: #0f172a;">
                            ${sec.level_num || 1}
                        </td>
                    `;
                }

                // 16. Mult_1 (Auxiliary column = PixHixCi)
                html += `
                    <td class="bg-soft-grey" style="text-align: right; color: #475569; font-family: monospace; padding: 4px 6px;">
                        ${formattedPix}
                    </td>
                `;

                // 17. Area (Auxiliary column = L * A, Rowspan on first row, or 0 on secondary rows)
                if (isFirst) {
                    html += `
                        <td class="bg-soft-grey" style="text-align: center; font-weight: bold; color: #1e293b; font-family: monospace; padding: 4px 6px;">
                            ${parseFloat(sec.area_m2 || 0).toFixed(0)}
                        </td>
                    `;
                } else {
                    html += `
                        <td class="bg-soft-grey" style="text-align: center; color: #94a3b8; font-family: monospace; padding: 4px 6px;">
                            0
                        </td>
                    `;
                }

                html += `</tr>`;
            });
        });

        tbody.innerHTML = html;
    }

    // 8. Motor de Recálculo Matemático Normativo
    function recalculateSector(sIdx) {
        const sec = localData.sectors[sIdx];
        if (!sec) return;

        let sumPix = 0;
        if (sec.materials && Array.isArray(sec.materials)) {
            sec.materials.forEach(m => {
                const peso = parseFloat(m.peso_kg || 0);
                const cant = parseFloat(m.cantidad || 1);
                const hi = parseFloat(m.hi || 0);
                const ci = parseFloat(m.ci || 1.0);
                const pix = (peso * cant) * hi * ci;
                m.pixhixci = Math.round(pix * 100) / 100;
                sumPix += m.pixhixci;
            });
        }

        const lt = parseFloat(sec.lt || 0);
        const at = parseFloat(sec.at || 0);
        const area = (lt > 0 && at > 0) ? Math.round(lt * at * 100) / 100 : parseFloat(sec.area_m2 || 0);
        sec.area_m2 = area;

        const ra = parseFloat(sec.ra || 1.0);
        const qp = (area > 0) ? Math.round(((sumPix * ra) / area) * 100) / 100 : 0.0;
        sec.qp = qp;
        sec.at_x_qp = Math.round((area * qp) * 100) / 100;
        sec.risk_level = calculateRiskLevel(qp);
        sec.level_num = calculateRiskLevelNum(qp);

        recalculateMacro();
    }

    function recalculateAll() {
        if (localData.sectors && Array.isArray(localData.sectors)) {
            localData.sectors.forEach((sec, idx) => {
                recalculateSector(idx);
            });
        }
        recalculateMacro();
    }

    function recalculateMacro() {
        const sectors = localData.sectors || [];
        const totalArea = sectors.reduce((acc, s) => acc + parseFloat(s.area_m2 || 0), 0);
        const sumATxQP = sectors.reduce((acc, s) => acc + (parseFloat(s.area_m2 || 0) * parseFloat(s.qp || 0)), 0);
        const qm = (totalArea > 0) ? Math.round((sumATxQP / totalArea) * 100) / 100 : 0.0;
        const macroRisk = calculateRiskLevel(qm);
        const macroLevel = calculateRiskLevelNum(qm);

        localData.macro_summary = {
            macroarea: localData.macroarea || 'PLANTA BAJA',
            total_area: Math.round(totalArea * 100) / 100,
            qm: qm,
            risk_level: macroRisk,
            level_num: macroLevel
        };

        const existingExtCount = (localData.fire_equipments && localData.fire_equipments.length > 0) ? localData.fire_equipments.length : 0;
        const extType = (localData.extinguishers && localData.extinguishers.extinguisher_type) ? localData.extinguishers.extinguisher_type : "50Kg ABC";
        const extCalc = calculateExtinguisherValues(extType, totalArea, macroRisk);

        localData.extinguishers = {
            macroarea: localData.macroarea || 'PLANTA BAJA',
            area_total: Math.round(totalArea * 100) / 100,
            qp_macro: qm,
            risk_level: macroRisk,
            extinguisher_type: extType,
            potential_a: extCalc.potential_a,
            potential_bc: extCalc.potential_bc,
            covered_area_a: extCalc.covered_area_a,
            covered_area_b: extCalc.covered_area_b,
            num_ext_a: extCalc.num_ext_a,
            num_ext_b: extCalc.num_ext_b,
            theoretical_qty: extCalc.theoretical_qty,
            final_assigned_qty: localData.extinguishers?.final_assigned_qty !== undefined ? localData.extinguishers.final_assigned_qty : Math.max(extCalc.theoretical_qty, existingExtCount, 1),
            distance_b: extCalc.distance_b,
            distance_a: extCalc.distance_a,
            distance_ab: extCalc.distance_ab
        };
    }

    // 9. Renderizado de Paso 2 (Hoja Carta Horizontal - 2 Tablas)
    function renderStep2Tables() {
        const tbodySectors = document.getElementById('step2TableSectorsBody');
        const tbodyMacro = document.getElementById('step2TableMacroBody');
        if (!tbodySectors || !tbodyMacro) return;

        const sectors = localData.sectors || [];
        if (sectors.length === 0) {
            tbodySectors.innerHTML = `<tr><td colspan="3" style="text-align: center; color: #64748b; padding: 12px;">Sin sectores registrados</td></tr>`;
            tbodyMacro.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #64748b; padding: 12px;">Sin datos de macro área</td></tr>`;
            return;
        }

        tbodySectors.innerHTML = sectors.map(s => `
            <tr>
                <td style="text-align: left; padding-left: 12px; font-weight: bold;">
                    ${escapeHtml(s.name || '')}
                </td>
                <td style="text-align: center;">
                    ${parseFloat(s.area_m2 || 0).toFixed(2)}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    ${parseFloat(s.qp || 0).toFixed(2)}
                </td>
            </tr>
        `).join('');

        const ms = localData.macro_summary || {};
        tbodyMacro.innerHTML = `
            <tr>
                <td style="text-align: left; padding-left: 12px; font-weight: bold; text-transform: uppercase;">
                    ${escapeHtml(ms.macroarea || localData.macroarea || 'PLANTA BAJA')}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    ${parseFloat(ms.total_area || 0).toFixed(2)}
                </td>
                <td style="text-align: center; font-weight: bold; color: #ea580c;">
                    ${parseFloat(ms.qm || 0).toFixed(2)}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    ${ms.risk_level || 'BAJO'}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    ${ms.level_num || 1}
                </td>
            </tr>
        `;
    }

    // 10. Renderizado de Paso 3 (Dotación de Extintores NB 58005)
    function renderStep3Tables() {
        const tbodyFull = document.getElementById('step3TableFullBody');
        const tbodySummary = document.getElementById('step3TableSummaryBody');
        if (!tbodyFull || !tbodySummary) return;

        const ext = localData.extinguishers || {};
        const extType = ext.extinguisher_type || '50Kg ABC';

        // Select de tipos de extintor
        const extSelectHtml = `
            <select class="fire-live-input" style="font-weight: bold; cursor: pointer;" onchange="onExtinguisherTypeChange(this.value)">
                ${Object.keys(EXTINGUISHER_TYPES_CATALOG).map(t => `<option value="${t}" ${t === extType ? 'selected' : ''}>${t}</option>`).join('')}
            </select>
        `;

        tbodyFull.innerHTML = `
            <tr>
                <td style="font-weight: bold; text-transform: uppercase;">${escapeHtml(ext.macroarea || localData.macroarea || 'PLANTA BAJA')}</td>
                <td>${parseFloat(ext.area_total || 0).toFixed(2)}</td>
                <td style="font-weight: bold;">${parseFloat(ext.qp_macro || 0).toFixed(2)}</td>
                <td style="font-weight: bold;">${ext.risk_level || 'BAJO'}</td>
                <td style="padding: 2px;">${extSelectHtml}</td>
                <td style="font-weight: bold;">${ext.potential_a || '—'}</td>
                <td style="font-weight: bold;">${ext.potential_bc || '—'}</td>
                <td>${parseFloat(ext.covered_area_a || 0).toFixed(2)}</td>
                <td>${parseFloat(ext.covered_area_b || 0).toFixed(2)}</td>
                <td>${parseFloat(ext.num_ext_a || 0).toFixed(2)}</td>
                <td>${parseFloat(ext.num_ext_b || 0).toFixed(2)}</td>
                <td style="font-weight: 800; background: #fff2cc;">${ext.theoretical_qty || 1}</td>
                <td style="background: #e2efda;">
                    <input type="number" min="1" step="1" class="fire-live-input" style="text-align: center; font-weight: 800;"
                        value="${ext.final_assigned_qty !== undefined ? ext.final_assigned_qty : (ext.theoretical_qty || 1)}"
                        oninput="onExtFinalQtyChange(this.value)">
                </td>
                <td>${parseFloat(ext.distance_b || 15.25).toFixed(2)}</td>
                <td>${parseFloat(ext.distance_a || 25.00).toFixed(2)}</td>
                <td>${parseFloat(ext.distance_ab || 15.25).toFixed(2)}</td>
            </tr>
        `;

        tbodySummary.innerHTML = `
            <tr>
                <td style="font-weight: bold; text-transform: uppercase;">${escapeHtml(ext.macroarea || localData.macroarea || 'PLANTA BAJA')}</td>
                <td>${parseFloat(ext.area_total || 0).toFixed(2)}</td>
                <td style="font-weight: bold;">${parseFloat(ext.qp_macro || 0).toFixed(2)}</td>
                <td style="font-weight: bold;">${ext.risk_level || 'BAJO'}</td>
                <td style="font-weight: bold;">${extType}</td>
                <td>${ext.potential_a || '—'}</td>
                <td>${ext.potential_bc || '—'}</td>
                <td style="font-weight: 800; background: #fff2cc;">${ext.theoretical_qty || 1}</td>
                <td style="font-weight: 800; background: #e2efda;">${ext.final_assigned_qty !== undefined ? ext.final_assigned_qty : (ext.theoretical_qty || 1)}</td>
            </tr>
        `;
    }

    function onExtinguisherTypeChange(typeVal) {
        if (!localData.extinguishers) localData.extinguishers = {};
        localData.extinguishers.extinguisher_type = typeVal;
        recalculateMacro();
        renderStep3Tables();
        triggerDebouncedAutoSave();
    }

    function onExtFinalQtyChange(qtyVal) {
        if (!localData.extinguishers) localData.extinguishers = {};
        localData.extinguishers.final_assigned_qty = parseInt(qtyVal) || 1;
        renderStep3Tables();
        triggerDebouncedAutoSave();
    }

    // 11. Sincronización Directa de Registros de Base de Datos
    function syncFromDatabaseRecords() {
        if (!confirm('¿Desea recargar la estructura y sectores directamente desde los registros del monitoreo en BD?')) return;
        window.location.reload();
    }

    // 12. Sistema de Auto-Guardado AJAX con Indicador Visual
    function triggerDebouncedAutoSave() {
        updateAutoSaveBadges('saving', 'Guardando...');
        if (autoSaveTimer) clearTimeout(autoSaveTimer);
        autoSaveTimer = setTimeout(() => {
            saveReportToServer();
        }, 1200);
    }

    function updateAutoSaveBadges(state, text) {
        const badges = [
            document.getElementById('globalAutoSaveBadge'),
            document.getElementById('step2AutoSaveBadge'),
            document.getElementById('step3AutoSaveBadge')
        ];

        badges.forEach(b => {
            if (!b) return;
            b.className = `fire-auto-save-badge ${state}`;
            b.querySelector('span').textContent = text;
        });
    }

    function saveReportToServer() {
        fetch(window.FIRE_REPORT_CONFIG.saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.FIRE_REPORT_CONFIG.csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                report_data: localData,
                installation_name: localData.macroarea || window.FIRE_REPORT_CONFIG.installationName
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateAutoSaveBadges('saved', 'Guardado');
            } else {
                updateAutoSaveBadges('error', 'Error al guardar');
            }
        })
        .catch(err => {
            console.error('Error saving report:', err);
            updateAutoSaveBadges('error', 'Error de red');
        });
    }

    // 13. Exportación Oficial a Documentos Microsoft Word (.doc) en Orientación Horizontal
    function downloadStep2WordDoc() {
        const macroArea = localData.macroarea || 'PLANTA BAJA';
        const sectors = localData.sectors || [];
        const ms = localData.macro_summary || {};

        let sectorsHtml = sectors.map(s => `
            <tr>
                <td style="border:1px solid #000000; padding:6px; font-weight:bold;">${escapeHtml(s.name || '')}</td>
                <td style="border:1px solid #000000; padding:6px; text-align:center;">${parseFloat(s.area_m2 || 0).toFixed(2)}</td>
                <td style="border:1px solid #000000; padding:6px; text-align:center; font-weight:bold;">${parseFloat(s.qp || 0).toFixed(2)}</td>
            </tr>
        `).join('');

        let docHtml = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset='utf-8'>
                <title>Resumen Carga de Fuego por Peso - ${escapeHtml(macroArea)}</title>
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
                        size: 279.4mm 215.9mm;
                        mso-page-orientation: landscape;
                        margin: 15mm 15mm 15mm 15mm;
                        mso-header-margin: 35.4pt;
                        mso-footer-margin: 35.4pt;
                        mso-paper-source: 0;
                    }
                    div.Section1 { page: Section1; }
                    body { font-family: Arial, sans-serif; font-size: 9pt; color: #000000; }
                    table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
                    th { background-color: #fff2cc; border: 1px solid #000000; padding: 6px; font-weight: bold; text-align: center; }
                    td { border: 1px solid #000000; padding: 5px; }
                </style>
            </head>
            <body>
                <div class="Section1">
                    <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 8px; margin-bottom: 14px;">
                        <h2 style="margin: 0; font-size: 13pt; text-transform: uppercase;">PLANILLA OFICIAL — RESUMEN DE CARGA DE FUEGO POR SECTORES Y MACRO ÁREA</h2>
                        <p style="margin: 3px 0 0 0; font-size: 10pt; font-weight: bold; color: #334155;">EVALUACIÓN POR PESO SEGÚN NORMA BOLIVIANA NB 58005 Y NTP 453</p>
                    </div>

                    <h3 style="font-size: 10pt; text-transform: uppercase; margin-bottom: 6px;">1. Carga de Fuego por Área o Sector</h3>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 50%; text-align: left; padding-left: 8px;">ÁREA O SECTOR</th>
                                <th style="width: 25%;">Área (m2)</th>
                                <th style="width: 25%;">Qp (Mcal/m2)</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${sectorsHtml}
                        </tbody>
                    </table>

                    <h3 style="font-size: 10pt; text-transform: uppercase; margin-bottom: 6px;">2. Carga de Fuego Total de la Macro Área</h3>
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 35%; text-align: left; padding-left: 8px;">Macro área</th>
                                <th style="width: 20%;">Área total</th>
                                <th style="width: 20%;">QM (Mcal/m2)</th>
                                <th style="width: 15%;">Riesgo</th>
                                <th style="width: 10%;">Nivel</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight: bold; text-transform: uppercase; padding-left: 8px;">${escapeHtml(ms.macroarea || macroArea)}</td>
                                <td style="text-align: center; font-weight: bold;">${parseFloat(ms.total_area || 0).toFixed(2)}</td>
                                <td style="text-align: center; font-weight: bold; color: #c2410c;">${parseFloat(ms.qm || 0).toFixed(2)}</td>
                                <td style="text-align: center; font-weight: bold;">${ms.risk_level || 'BAJO'}</td>
                                <td style="text-align: center; font-weight: bold;">${ms.level_num || 1}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </body>
            </html>
        `;

        downloadDocFile(docHtml, `Resumen_Carga_Fuego_Peso_${macroArea.replace(/\s+/g, '_')}.doc`);
    }

    function downloadStep3WordDoc() {
        const ext = localData.extinguishers || {};
        const macroArea = ext.macroarea || localData.macroarea || 'PLANTA BAJA';

        let docHtml = `
            <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
            <head>
                <meta charset='utf-8'>
                <title>Dotación de Extintores - ${escapeHtml(macroArea)}</title>
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
                        size: 279.4mm 215.9mm;
                        mso-page-orientation: landscape;
                        margin: 15mm 15mm 15mm 15mm;
                        mso-header-margin: 35.4pt;
                        mso-footer-margin: 35.4pt;
                        mso-paper-source: 0;
                    }
                    div.Section1 { page: Section1; }
                    body { font-family: Arial, sans-serif; font-size: 8pt; color: #000000; }
                    table { border-collapse: collapse; width: 100%; margin-bottom: 16px; }
                    th { background-color: #f1f5f9; border: 1px solid #000000; padding: 5px; font-weight: bold; text-align: center; font-size: 7.5pt; }
                    td { border: 1px solid #000000; padding: 4px; text-align: center; font-size: 7.5pt; }
                </style>
            </head>
            <body>
                <div class="Section1">
                    <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 8px; margin-bottom: 14px;">
                        <h2 style="margin: 0; font-size: 13pt; text-transform: uppercase;">DETERMINACIÓN DE LA DOTACIÓN DE EXTINTORES CONTRA INCENDIOS</h2>
                        <p style="margin: 3px 0 0 0; font-size: 10pt; font-weight: bold; color: #334155;">CÁLCULO DE POTENCIAL EXTINTOR Y ASIGNACIÓN NORMATIVA SEGÚN NB 58005</p>
                    </div>

                    <h3 style="font-size: 9pt; text-transform: uppercase; margin-bottom: 6px;">1. Matriz de Cálculo de Potencial Extintor y Distancias Máximas de Traslado</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Macro área</th>
                                <th>A (m2)</th>
                                <th>Qp MACRO (Mcal/m2)</th>
                                <th>Nivel de riesgo</th>
                                <th>Tipo de extintor</th>
                                <th>Potencial A</th>
                                <th>Potencial BC</th>
                                <th>Sup. Cubierta A</th>
                                <th>Sup. Cubierta B</th>
                                <th>N° ext. A</th>
                                <th>N° ext. B</th>
                                <th style="background:#fff2cc;">CANTIDAD TEÓRICA</th>
                                <th style="background:#e2efda;">CANTIDAD ASIGNADA</th>
                                <th>Clase B</th>
                                <th>Clase A</th>
                                <th>Clase AB</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight:bold; text-transform:uppercase;">${escapeHtml(macroArea)}</td>
                                <td>${parseFloat(ext.area_total || 0).toFixed(2)}</td>
                                <td style="font-weight:bold;">${parseFloat(ext.qp_macro || 0).toFixed(2)}</td>
                                <td style="font-weight:bold;">${ext.risk_level || 'BAJO'}</td>
                                <td style="font-weight:bold;">${ext.extinguisher_type || '50Kg ABC'}</td>
                                <td>${ext.potential_a || '—'}</td>
                                <td>${ext.potential_bc || '—'}</td>
                                <td>${parseFloat(ext.covered_area_a || 0).toFixed(2)}</td>
                                <td>${parseFloat(ext.covered_area_b || 0).toFixed(2)}</td>
                                <td>${parseFloat(ext.num_ext_a || 0).toFixed(2)}</td>
                                <td>${parseFloat(ext.num_ext_b || 0).toFixed(2)}</td>
                                <td style="font-weight:bold; background:#fff2cc;">${ext.theoretical_qty || 1}</td>
                                <td style="font-weight:bold; background:#e2efda;">${ext.final_assigned_qty || 1}</td>
                                <td>${parseFloat(ext.distance_b || 15.25).toFixed(2)}</td>
                                <td>${parseFloat(ext.distance_a || 25.00).toFixed(2)}</td>
                                <td>${parseFloat(ext.distance_ab || 15.25).toFixed(2)}</td>
                            </tr>
                        </tbody>
                    </table>

                    <h3 style="font-size: 9pt; text-transform: uppercase; margin-bottom: 6px;">2. Resumen de Dotación Teórica y Asignación Final de Extintores</h3>
                    <table>
                        <thead>
                            <tr style="background:#fff2cc;">
                                <th style="background:#fff2cc;">Macro área</th>
                                <th style="background:#fff2cc;">A (m2)</th>
                                <th style="background:#fff2cc;">Qp MACRO (Mcal/m2)</th>
                                <th style="background:#fff2cc;">Nivel de riesgo</th>
                                <th style="background:#fff2cc;">Tipo de extintor aproximado</th>
                                <th style="background:#fff2cc;">Potencial extintor A</th>
                                <th style="background:#fff2cc;">Potencial extintor BC</th>
                                <th style="background:#fff2cc;">CANTIDAD TEÓRICA</th>
                                <th style="background:#fff2cc;">CANTIDAD ASIGNADA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td style="font-weight:bold; text-transform:uppercase;">${escapeHtml(macroArea)}</td>
                                <td>${parseFloat(ext.area_total || 0).toFixed(2)}</td>
                                <td style="font-weight:bold;">${parseFloat(ext.qp_macro || 0).toFixed(2)}</td>
                                <td style="font-weight:bold;">${ext.risk_level || 'BAJO'}</td>
                                <td style="font-weight:bold;">${ext.extinguisher_type || '50Kg ABC'}</td>
                                <td>${ext.potential_a || '—'}</td>
                                <td>${ext.potential_bc || '—'}</td>
                                <td style="font-weight:bold; background:#fff2cc;">${ext.theoretical_qty || 1}</td>
                                <td style="font-weight:bold; background:#e2efda;">${ext.final_assigned_qty || 1}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </body>
            </html>
        `;

        downloadDocFile(docHtml, `Dotacion_Extintores_Peso_${macroArea.replace(/\s+/g, '_')}.doc`);
    }

    function downloadDocFile(htmlContent, fileName) {
        const blob = new Blob(['\ufeff' + htmlContent], {
            type: 'application/msword;charset=utf-8'
        });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
</script>
@endpush
