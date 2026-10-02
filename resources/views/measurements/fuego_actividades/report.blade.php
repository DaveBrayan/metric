@extends('layouts.app')

@section('title', 'Informe Técnico — Carga de Fuego por Actividad (NB 58005 / NTP 453) — Metric v2')

@push('styles')
    @metricStyle('fuego_actividades')
    <style>
        .fire-report-page {
            width: 100%;
            padding: 20px 0 60px 0;
            background-color: #f1f5f9;
            min-height: calc(100vh - 70px);
        }

        .fire-report-container {
            width: 100%;
            max-width: 1320px;
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
            background: #f8fafc;
        }

        .fire-live-input:focus {
            border-color: #ea580c;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(234, 88, 12, 0.15);
        }

        /* Selects estilizados */
        .fire-live-select {
            width: 100%;
            border: 1px solid transparent;
            border-radius: 2px;
            padding: 1px 2px;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            background: transparent;
            outline: none;
            cursor: pointer;
        }

        .fire-live-select:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .fire-live-select:focus {
            border-color: #ea580c;
            background: #ffffff;
        }

        /* Tablas de Paso 1 (Excel Replica Image 1) */
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

        /* Main Red/Pink Matrix Table */
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

        .bg-soft-green {
            background-color: #e2efda !important;
        }

        .bg-soft-red {
            background-color: #f8cecc !important;
        }

        .bg-soft-yellow {
            background-color: #fff2cc !important;
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
    </style>
@endpush

@section('content')
<div class="fire-report-page">
    <div class="fire-report-container">

        {{-- 1. Encabezado y Navegación --}}
        <div class="illumination-header-banner" style="margin-bottom: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <a href="{{ route('modules.fire_activity', $module->id) }}" class="btn-secondary-subtle" style="padding: 5px 12px; font-size: 12px;">
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
                    <span>Informe Técnico — Carga de Fuego por Actividad</span>
                </h1>
                <p style="margin: 0; color: #64748b; font-size: 13.5px;">
                    Matriz Dinámica según Registros, Resumen de Macro Área y Dotación de Extintores (NB 58005 / NTP 453)
                </p>
            </div>

            <div class="header-action-group" style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn-secondary-subtle" onclick="syncFromDatabaseRecords()" 
                    title="Cargar y sincronizar sectores, actividades y equipos directamente desde los registros del monitoreo">
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

        {{-- Datalist para Autocompletado de Actividades Normativas --}}
        <datalist id="fireActivitiesDatalist">
            <option value="Oficinas comerciales">
            <option value="Orfebrería">
            <option value="Papel">
            <option value="Papelería">
            <option value="Aparatos eléctricos">
            <option value="Aparatos electrónicos">
            <option value="Alimentación, platos precocinados">
            <option value="Abonos químicos">
            <option value="Aceites comestibles">
            <option value="Aceites comestibles, expedición">
            <option value="Aceites: mineral, vegetal y animal">
            <option value="Acero">
            <option value="Acetileno">
            <option value="Ácido carbónico">
            <option value="Ácidos inorgánicos">
            <option value="Acumuladores">
            <option value="Algodón">
            <option value="Algodón, almacén de">
            <option value="Alimentación, embalaje">
            <option value="Almacenes de talleres, repuestos">
            <option value="Almidón">
            <option value="Alquitrán">
            <option value="Aluminio">
            <option value="Archivos">
            <option value="Automóviles, accesorios">
            <option value="Automóviles, garajes y talleres">
            <option value="Automóviles, pintura">
            <option value="Azúcar">
            <option value="Barnices">
            <option value="Bebidas alcohólicas">
            <option value="Bebidas sin alcohol">
            <option value="Bibliotecas">
            <option value="Cables eléctricos">
            <option value="Calzado, almacén y venta">
            <option value="Cartón">
            <option value="Cartón ondulado">
            <option value="Cartonaje, expedición de">
            <option value="Caucho">
            <option value="Cemento">
            <option value="Cera y parafinas">
            <option value="Cervecerías">
            <option value="Chocolate, fabricación">
            <option value="Colchones y espumas">
            <option value="Combustibles líquidos">
            <option value="Cuero sintético">
            <option value="Farmacéuticos">
            <option value="Fundiciones de metales">
            <option value="Gases licuados">
            <option value="Imprentas y artes gráficas">
            <option value="Laboratorios químicos">
            <option value="Madera">
            <option value="Muebles de madera">
            <option value="Plásticos y polímeros">
            <option value="Químicos industriales">
            <option value="Textiles y confección">
            <option value="Vidriería y cerámica">
        </datalist>

        {{-- 2. Barra Interactiva de Pasos (Stepper) --}}
        <div class="fire-stepper-bar">
            <div class="fire-stepper-track">
                <!-- Paso 1 -->
                <button type="button" class="fire-step-btn active" data-step="1" onclick="switchReportStep(1)">
                    <div class="fire-step-number">1</div>
                    <div class="fire-step-text">
                        <span class="fire-step-title">Paso 1: Matriz de Carga de Fuego</span>
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
        {{-- PASO 1: MATRIZ DE CARGA DE FUEGO POR ACTIVIDAD (DINÁMICA SEGÚN REGISTROS)  --}}
        {{-- ========================================================================= --}}
        <div class="step-pane active" id="pane_step_1">
            <div class="fire-matrix-card">

                <!-- Bloque Superior: Macro Área, Dimensiones y Equipos Contra Incendios -->
                <div class="fire-matrix-top-grid">
                    <div class="fire-top-info-box">
                        <div class="fire-top-label-row">
                            <span class="fire-top-label">MACRO AREA:</span>
                            <input type="text" id="macroAreaInput" class="fire-top-input-box"
                                value="{{ $reportData['macroarea'] ?? 'PLANTA BAJA' }}"
                                placeholder="Ej. PLANTA BAJA" oninput="onMacroAreaChange(this.value)">
                        </div>

                        <div style="margin-top: 10px;">
                            <span class="fire-top-label" style="display: block; margin-bottom: 6px;">DIMENSIONES:</span>
                            <table style="border-collapse: collapse; border: 1.5px solid #000000; width: 220px; font-size: 8pt; font-family: Arial, sans-serif;">
                                <tr>
                                    <td style="border: 1px solid #000000; padding: 3px 6px; font-weight: bold; width: 80px; background: #f8fafc;">LT (m)</td>
                                    <td style="border: 1px solid #000000; padding: 2px 4px;">
                                        <input type="number" step="0.01" id="macroLtInput" class="fire-live-input" style="font-weight: bold;"
                                            value="{{ $reportData['dimensions']['lt'] ?? '' }}" placeholder="—" oninput="onMacroDimensionsChange()">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 1px solid #000000; padding: 3px 6px; font-weight: bold; background: #f8fafc;">At (m)</td>
                                    <td style="border: 1px solid #000000; padding: 2px 4px;">
                                        <input type="number" step="0.01" id="macroAtInput" class="fire-live-input" style="font-weight: bold;"
                                            value="{{ $reportData['dimensions']['at'] ?? '' }}" placeholder="—" oninput="onMacroDimensionsChange()">
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- EQUIPOS CONTRA INCENDIOS (Sin botones + y -, con columnas: UBICACIÓN | TIPO | CANTIDAD | OBS.) -->
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

                <!-- Tabla Principal con Header Rojo/Salmón -->
                <div style="overflow-x: auto; margin-top: 15px;">
                    <table class="fire-main-table" id="fireMainMatrixTable">
                        <thead>
                            <tr>
                                <th style="width: 140px;">ÁREA O SECTOR</th>
                                <th style="width: 170px;">ACTIVIDAD</th>
                                <th style="width: 95px;">TIPO</th>
                                <th style="width: 120px;">DESCRIPCIÓN</th>
                                <th style="width: 50px;">Yi (m)</th>
                                <th style="width: 50px;">Xi (m)</th>
                                <th style="width: 50px;">hi (m)</th>
                                <th style="width: 55px;">Lt (m)</th>
                                <th style="width: 55px;">At (m)</th>
                                <th style="width: 70px;">Area x<br>(m2)</th>
                                <th style="width: 75px;">qxi<br>(Mcal/m3)</th>
                                <th style="width: 60px;">Ai (m2)</th>
                                <th style="width: 45px;">Ci</th>
                                <th style="width: 45px;">Ra</th>
                                <th style="width: 80px;">Qp<br>(Mcal/m2)</th>
                                <th style="width: 70px;">Nivel<br>de riesgo</th>
                                <th style="width: 40px;">N°</th>
                                <th style="width: 75px;">Mult_1</th>
                                <th style="width: 75px;">Aux_2</th>
                            </tr>
                        </thead>
                        <tbody id="fireMainMatrixBody">
                            <!-- Los sectores y actividades se renderizan dinámicamente según registros -->
                        </tbody>
                    </table>
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
                        EVALUACIÓN SEGÚN NORMA BOLIVIANA NB 58005 Y NTP 453
                    </div>
                </div>

                <!-- TABLA 1 (Image 2): RESUMEN POR SECTOR -->
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

                <!-- TABLA 2 (Image 3): RESUMEN DE LA MACRO ÁREA -->
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

                <!-- TABLA 1 (Image 4 Superior): MATRIZ COMPLETA DE EXTINTORES -->
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

                <!-- TABLA 2 (Image 4 Inferior): RESUMEN DE DOTACIÓN -->
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
                    <b>Criterios Técnicos de Cálculo y Dotación (NB 58005):</b>
                    <ul style="margin: 3px 0 0 16px; padding: 0;">
                        <li>La cantidad teórica se determina calculando el cociente entre la superficie a proteger y la capacidad de cubrimiento unitaria para la clase de fuego correspondiente.</li>
                        <li>Las distancias máximas de traslado hacia el extintor más cercano son: <b>25.00 m</b> para fuegos Clase A, <b>15.25 m</b> para fuegos Clase B, y <b>15.25 m</b> para combinaciones Clase AB.</li>
                        <li>Todo extintor debe ubicarse a una altura no mayor a 1.50 m del nivel de piso terminado, con señalización fotoluminiscente normalizada y acceso libre de obstáculos.</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    // 1. Catálogo Normativo de Actividades con valores de Producción y Almacén (extra!$A$2:$K$571)
    // Fórmulas correspondientes:
    // - qxi (Mcal/m3): =SI(D10="Producción"; BUSCARV(C10; extra!$A$2:$K$571; 3; 0); BUSCARV(C10; extra!$A$2:$K$571; 7; 0))
    // - Ci:            =BUSCARV(C10; extra!$A$2:$K$571; 10; 0)
    // - Ra:            =SI(D10="Producción"; BUSCARV(C10; extra!$A$2:$K$571; 5; 0); BUSCARV(C10; extra!$A$2:$K$571; 9; 0))
    // - Qp (Mcal/m2):  =SUMA(U10:U13) / (Lt * At)
    // - Nivel Riesgo:  =SI(P10<=200; "BAJO"; SI(P10<=800; "MEDIO"; "ALTO"))
    // - N° Riesgo:     =+SI(P10<=100;"1";SI(P10<=200;"2";SI(P10<=300;"3";SI(P10<=400;"4";SI(P10<=800;"5";SI(P10<=1600;"6";SI(P10<=3200;"7";"8")))))))
    const ACTIVITY_NORMATIVE_CATALOG = {
        "abonos quimicos": { qxi_prod: 48, ra_prod: 1.5, qxi_alm: 48, ra_alm: 1.0, ci: 1.0 },
        "aceites comestibles, expedicion": { qxi_prod: 215, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "aceites comestibles": { qxi_prod: 240, ra_prod: 3.0, qxi_alm: 4520, ra_alm: 3.0, ci: 1.2 },
        "aceites: mineral, vegetal y animal": { qxi_prod: 0, ra_prod: null, qxi_alm: 4520, ra_alm: 3.0, ci: 1.2 },
        "acero": { qxi_prod: 10, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "acetileno": { qxi_prod: 168, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.6 },
        "acido carbonico": { qxi_prod: 10, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "acidos inorganicos": { qxi_prod: 20, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "acumuladores": { qxi_prod: 96, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.0 },
        "algodon en rama": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 264, ra_alm: 3.0, ci: 1.2 },
        "algodon, almacen de": { qxi_prod: 0, ra_prod: null, qxi_alm: 311, ra_alm: 3.0, ci: 1.2 },
        "algodon": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 311, ra_alm: 3.0, ci: 1.2 },
        "alimentacion, embalaje": { qxi_prod: 192, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.2 },
        "alimentacion, expedicion": { qxi_prod: 240, ra_prod: 3.0, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "alimentacion, platos precocinados": { qxi_prod: 48, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "almacenes de talleres": { qxi_prod: 287, ra_prod: 3.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "almidon": { qxi_prod: 480, ra_prod: 3.0, qxi_alm: 0, ra_alm: null, ci: 1.6 },
        "alquitran": { qxi_prod: 0, ra_prod: null, qxi_alm: 814, ra_alm: 3.0, ci: 1.2 },
        "aluminio": { qxi_prod: 48, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "aparatos de radio": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 48, ra_alm: 1.0, ci: 1.2 },
        "aparatos de television": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 48, ra_alm: 1.0, ci: 1.2 },
        "aparatos electricos": { qxi_prod: 96, ra_prod: 1.0, qxi_alm: 96, ra_alm: 1.0, ci: 1.2 },
        "aparatos electronicos": { qxi_prod: 96, ra_prod: 1.0, qxi_alm: 96, ra_alm: 1.0, ci: 1.2 },
        "archivos": { qxi_prod: 1005, ra_prod: 3.0, qxi_alm: 407, ra_alm: 3.0, ci: 1.2 },
        "automoviles, garajes y aparcamientos": { qxi_prod: 48, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "automoviles, pintura": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.6 },
        "automoviles, reparacion": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "azucar": { qxi_prod: 96, ra_prod: 1.5, qxi_alm: 2010, ra_alm: 3.0, ci: 1.6 },
        "barnices": { qxi_prod: 1197, ra_prod: 3.0, qxi_alm: 598, ra_alm: 3.0, ci: 1.6 },
        "bebidas alcoholicas": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.6 },
        "bebidas sin alcohol": { qxi_prod: 20, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.6 },
        "bibliotecas": { qxi_prod: 479, ra_prod: 3.0, qxi_alm: 479, ra_alm: 3.0, ci: 1.2 },
        "cables": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 144, ra_alm: 1.5, ci: 1.0 },
        "calzado": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 96, ra_alm: 1.0, ci: 1.2 },
        "carton": { qxi_prod: 72, ra_prod: 1.5, qxi_alm: 1005, ra_alm: 1.5, ci: 1.2 },
        "carton ondulado": { qxi_prod: 192, ra_prod: 3.0, qxi_alm: 311, ra_alm: 3.0, ci: 1.2 },
        "cartonaje, expedicion de": { qxi_prod: 144, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "cartonaje": { qxi_prod: 192, ra_prod: 1.5, qxi_alm: 598, ra_alm: 3.0, ci: 1.2 },
        "caucho": { qxi_prod: 144, ra_prod: 1.5, qxi_alm: 6843, ra_alm: 3.0, ci: 1.2 },
        "cemento": { qxi_prod: 10, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "cera": { qxi_prod: 311, ra_prod: 3.0, qxi_alm: 814, ra_alm: 3.0, ci: 1.0 },
        "cervecerias": { qxi_prod: 20, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "chocolate": { qxi_prod: 96, ra_prod: 1.5, qxi_alm: 814, ra_alm: 1.5, ci: 1.2 },
        "colchones": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 1197, ra_alm: 3.0, ci: 1.2 },
        "combustibles liquidos": { qxi_prod: 860, ra_prod: 3.0, qxi_alm: 860, ra_alm: 3.0, ci: 1.6 },
        "cuero sintetico": { qxi_prod: 240, ra_prod: 1.5, qxi_alm: 407, ra_alm: 1.5, ci: 1.2 },
        "cuero": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 407, ra_alm: 1.5, ci: 1.2 },
        "deposito": { qxi_prod: 192, ra_prod: 1.5, qxi_alm: 264, ra_alm: 3.0, ci: 1.2 },
        "farmacias": { qxi_prod: 192, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "fundicion de metales": { qxi_prod: 10, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "gasolineras": { qxi_prod: 0, ra_prod: null, qxi_alm: 0, ra_alm: null, ci: 1.6 },
        "granos": { qxi_prod: 144, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.6 },
        "grasas": { qxi_prod: 240, ra_prod: 3.0, qxi_alm: 4307, ra_alm: 3.0, ci: 1.2 },
        "harina en sacos": { qxi_prod: 479, ra_prod: 3.0, qxi_alm: 2010, ra_alm: 3.0, ci: 1.6 },
        "harina": { qxi_prod: 407, ra_prod: 3.0, qxi_alm: 3110, ra_alm: 3.0, ci: 1.6 },
        "hospitales": { qxi_prod: 72, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "hoteles": { qxi_prod: 72, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "imprentas": { qxi_prod: 96, ra_prod: 1.5, qxi_alm: 1914, ra_alm: 3.0, ci: 1.2 },
        "juguetes": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.2 },
        "laboratorios quimicos": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "librerias": { qxi_prod: 240, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.2 },
        "licores": { qxi_prod: 96, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.6 },
        "madera": { qxi_prod: 192, ra_prod: 1.5, qxi_alm: 1005, ra_alm: 3.0, ci: 1.2 },
        "muebles de madera": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.2 },
        "muebles": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 192, ra_alm: 1.5, ci: 1.2 },
        "neumaticos": { qxi_prod: 168, ra_prod: 1.5, qxi_alm: 430, ra_alm: 3.0, ci: 1.2 },
        "oficinas comerciales": { qxi_prod: 192, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "oficinas tecnicas": { qxi_prod: 144, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "oficinas postales": { qxi_prod: 96, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "orfebreria": { qxi_prod: 48, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "panaderias": { qxi_prod: 240, ra_prod: 1.5, qxi_alm: 72, ra_alm: 1.0, ci: 1.0 },
        "papel": { qxi_prod: 48, ra_prod: 1.0, qxi_alm: 2390, ra_alm: 3.0, ci: 1.2 },
        "papeleria": { qxi_prod: 192, ra_prod: 1.5, qxi_alm: 264, ra_alm: 3.0, ci: 1.2 },
        "plasticos": { qxi_prod: 480, ra_prod: 3.0, qxi_alm: 1412, ra_alm: 3.0, ci: 1.2 },
        "quimicos industriales": { qxi_prod: 72, ra_prod: 3.0, qxi_alm: 240, ra_alm: 3.0, ci: 1.0 },
        "restaurantes": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "talleres mecanicos": { qxi_prod: 48, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "talleres de pintura": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 0, ra_alm: null, ci: 1.6 },
        "teatros": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 264, ra_alm: 3.0, ci: 1.0 },
        "textiles y confeccion": { qxi_prod: 72, ra_prod: 1.0, qxi_alm: 240, ra_alm: 3.0, ci: 1.2 },
        "textiles": { qxi_prod: 120, ra_prod: 1.5, qxi_alm: 240, ra_alm: 3.0, ci: 1.2 },
        "vidrieria y ceramica": { qxi_prod: 20, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 },
        "vidrio": { qxi_prod: 20, ra_prod: 1.0, qxi_alm: 0, ra_alm: null, ci: 1.0 }
    };

    /**
     * Resuelve los valores normativos de una actividad según las fórmulas de Excel
     */
    function lookupNormativeValues(actividad, tipo) {
        if (!actividad) return { qxi: 0, ci: 1.0, ra: null };
        const cleanAct = normalizeText(actividad);
        const isProd = normalizeText(tipo).includes('prod');

        for (const [key, val] of Object.entries(ACTIVITY_NORMATIVE_CATALOG)) {
            if (cleanAct.includes(key) || key.includes(cleanAct)) {
                const rawQxi = isProd ? val.qxi_prod : val.qxi_alm;
                const rawRa = isProd ? val.ra_prod : val.ra_alm;
                return {
                    qxi: rawQxi !== undefined && rawQxi !== null ? rawQxi : 0,
                    ci: val.ci !== undefined && val.ci !== null ? val.ci : 1.0,
                    ra: (rawRa !== undefined && rawRa !== null && rawRa !== '') ? rawRa : null
                };
            }
        }

        return {
            qxi: isProd ? 192 : 0,
            ci: 1.0,
            ra: isProd ? 1.5 : null
        };
    }

    function lookupNormativeQxi(actividad, tipo) {
        const res = lookupNormativeValues(actividad, tipo);
        return res.qxi;
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

    function normalizeText(txt) {
        if (!txt) return '';
        return txt.toString().toLowerCase()
            .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .trim();
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

        // Fórmulas solicitadas:
        // N° ext. para fuegos clase A = A (m2) / Superficie cubierta para fuegos clase A
        const numExtA = (covA > 0) ? (totalArea / covA) : 0;

        // N° ext. Para fuegos clase B = A (m2) / Superficie cubierta para fuegos clase B
        const numExtB = (covB > 0) ? (totalArea / covB) : 0;

        // CANTIDAD TEÓRICA REQUERIDA = =REDONDEAR.MAS(MAX(K2:L2);0)
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

    // 2. Configuración y Estado Global
    window.FIRE_REPORT_CONFIG = {
        moduleId: {{ $module->id ?? 1 }},
        csrfToken: "{{ csrf_token() }}",
        saveUrl: "{{ route('modules.fire_activity.report.save', $module->id ?? 1) }}",
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
        // Asegurar que las dimensiones del bloque superior tomen las dimensiones del sector evaluado si están vacías
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
        // Recalcular normativas y QP de todos los sectores al cargar
        if (localData.sectors && Array.isArray(localData.sectors)) {
            localData.sectors.forEach((sec, sIdx) => {
                if (sec.items && Array.isArray(sec.items)) {
                    sec.items.forEach(item => {
                        const norm = lookupNormativeValues(item.actividad, item.tipo);
                        item.qxi = norm.qxi;
                        item.ci = norm.ci;
                        item.ra = norm.ra;
                    });
                }
                recalculateSectorQp(sIdx);
            });
        }
        renderStep1Matrix();
        renderStep2Tables();
        renderStep3Tables();
    });

    /**
     * Sincronizar dinámicamente con los registros de la Base de Datos
     */
    function syncFromDatabaseRecords() {
        const dbSecs = window.FIRE_REPORT_CONFIG.dbSectors;
        const dbEqs = window.FIRE_REPORT_CONFIG.dbEquipments;

        if (!dbSecs || dbSecs.length === 0) {
            alert("No hay registros de sectores en la base de datos para este módulo. Puede agregar sectores desde el monitoreo.");
            return;
        }

        if (confirm("¿Desea sincronizar y recargar la matriz con los " + dbSecs.length + " sectores registrados en la base de datos?")) {
            localData.sectors = JSON.parse(JSON.stringify(dbSecs));
            if (dbEqs && dbEqs.length > 0) {
                localData.fire_equipments = JSON.parse(JSON.stringify(dbEqs));
            }

            renderEquipmentsTable();
            renderStep1Matrix();
            renderStep2Tables();
            renderStep3Tables();
            autoSaveFireReport();
            showSyncToast();
        }
    }

    function showSyncToast() {
        const badge = document.getElementById('globalAutoSaveBadge');
        if (badge) {
            badge.className = 'fire-auto-save-badge';
            badge.style.backgroundColor = '#ecfdf5';
            badge.style.color = '#059669';
            badge.innerHTML = `
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>¡Sincronizado con BD!</span>
            `;
            setTimeout(() => {
                badge.style.backgroundColor = '';
                badge.style.color = '';
                setSaveStatus('saved');
            }, 2500);
        }
    }

    /**
     * Navegación de Pasos (Stepper)
     */
    function switchReportStep(stepNum) {
        currentStep = stepNum;
        
        document.querySelectorAll('.fire-step-btn').forEach(btn => {
            const bStep = parseInt(btn.getAttribute('data-step'), 10);
            if (bStep === stepNum) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        const progressFill = document.getElementById('stepperProgressBar');
        if (progressFill) {
            const pct = stepNum === 1 ? '33.33%' : (stepNum === 2 ? '66.66%' : '100%');
            progressFill.style.width = pct;
        }

        document.querySelectorAll('.step-pane').forEach((pane, idx) => {
            if (idx + 1 === stepNum) {
                pane.classList.add('active');
            } else {
                pane.classList.remove('active');
            }
        });

        if (stepNum === 2) {
            renderStep2Tables();
        } else if (stepNum === 3) {
            renderStep3Tables();
        }
    }

    /**
     * Render Tabla de Equipos Contra Incendios (UBICACIÓN | TIPO | CANTIDAD | OBS.)
     */
    function renderEquipmentsTable() {
        const tbody = document.getElementById('fireEquipmentsTableBody');
        if (!tbody) return;

        const eqList = localData.fire_equipments || [];
        if (eqList.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" style="text-align: center; color: #64748b; padding: 6px;">No hay equipos contra incendios registrados en los sectores.</td></tr>`;
            return;
        }

        let html = '';
        eqList.forEach((eq, idx) => {
            html += `
                <tr>
                    <td style="font-weight: bold; text-transform: uppercase;">
                        ${escapeHtml(eq.ubicacion || 'Sector')}
                    </td>
                    <td>
                        ${escapeHtml(eq.tipo || 'EXTINTOR')}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        ${eq.cantidad || eq.cant || 1}
                    </td>
                    <td style="color: #334155;">
                        ${escapeHtml(eq.obs || eq.observacion || 'En servicio')}
                    </td>
                </tr>
            `;
        });

        tbody.innerHTML = html;
    }

    /**
     * Render Step 1: Matriz Dinámica según Registros
     */
    function renderStep1Matrix() {
        const tbody = document.getElementById('fireMainMatrixBody');
        if (!tbody) return;

        if (localData.sectors.length === 0) {
            tbody.innerHTML = `<tr><td colspan="19" style="text-align: center; padding: 16px; color: #64748b; font-weight: bold;">No hay registros de sectores en este módulo. Haga clic en "Sincronizar Registros BD" o registre sectores desde la página de monitoreo.</td></tr>`;
            return;
        }

        let html = '';

        localData.sectors.forEach((sec, secIdx) => {
            const items = sec.items && Array.isArray(sec.items) ? sec.items : [];
            const rowCount = Math.max(items.length, 1);

            items.forEach((item, itemIdx) => {
                const isFirst = (itemIdx === 0);
                const rowBgClass = (secIdx % 2 === 0) ? 'bg-soft-green' : 'bg-soft-red';
                const isProd = (item.tipo || 'Producción').toLowerCase().includes('prod');

                html += `<tr data-sec-idx="${secIdx}" data-item-idx="${itemIdx}" class="${rowBgClass}">`;

                // Columna 1: ÁREA O SECTOR (rowspan)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" style="font-weight: bold; text-align: center; vertical-align: middle; background-color: inherit; text-transform: uppercase;">
                            <span>${escapeHtml(sec.name || `SECTOR ${secIdx + 1}`)}</span>
                        </td>
                    `;
                }

                // Columna 2: ACTIVIDAD
                html += `
                    <td style="text-align: left; padding-left: 8px;">
                        <span>${escapeHtml(item.actividad || '—')}</span>
                    </td>
                `;

                // Columna 3: TIPO
                html += `
                    <td style="text-align: center;">
                        <span>${escapeHtml(item.tipo || 'Producción')}</span>
                    </td>
                `;

                // Columna 4: DESCRIPCIÓN
                html += `
                    <td style="text-align: left; padding-left: 8px;">
                        <span>${escapeHtml(item.descripcion || '—')}</span>
                    </td>
                `;

                // Columna 5: Yi (m)
                html += `
                    <td style="text-align: center;">
                        <span>${formatDimensionNumber(item.yi)}</span>
                    </td>
                `;

                // Columna 6: Xi (m)
                html += `
                    <td style="text-align: center;">
                        <span>${formatDimensionNumber(item.xi)}</span>
                    </td>
                `;

                // Columna 7: hi (m)
                html += `
                    <td style="text-align: center;">
                        <span>${formatDimensionNumber(item.hi)}</span>
                    </td>
                `;

                // Columnas 8, 9, 10: Lt (m), At (m), Area x (m2) — Solo en 1ra fila
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" style="text-align: center; vertical-align: middle; background-color: inherit; font-weight: bold;">
                            <span>${formatDimensionNumber(sec.lt)}</span>
                        </td>
                        <td rowspan="${rowCount}" style="text-align: center; vertical-align: middle; background-color: inherit; font-weight: bold;">
                            <span>${formatDimensionNumber(sec.at)}</span>
                        </td>
                        <td rowspan="${rowCount}" style="text-align: center; vertical-align: middle; font-weight: bold; background-color: inherit;">
                            <span id="secArea_${secIdx}">${sec.area_m2 ? formatDecimal(sec.area_m2, 2) : '—'}</span>
                        </td>
                    `;
                }

                // Columna 11: qxi (Mcal/m3) — NO EDITABLE (calculado automáticamente según fórmulas normativas)
                const norm = lookupNormativeValues(item.actividad, item.tipo);
                item.qxi = norm.qxi;
                item.ci = norm.ci;
                item.ra = norm.ra;

                html += `
                    <td style="text-align: center; font-weight: bold;">
                        <span id="itemQxi_${secIdx}_${itemIdx}">${item.qxi !== undefined && item.qxi !== '' ? item.qxi : '0'}</span>
                    </td>
                `;

                // Columna 12: Ai (m2) = Yi * Xi — NO EDITABLE (calculado)
                const aiVal = (item.yi && item.xi) ? (parseFloat(item.yi) * parseFloat(item.xi)) : (item.ai || 0);
                html += `
                    <td style="text-align: center; font-weight: bold;">
                        <span id="itemAi_${secIdx}_${itemIdx}">${aiVal ? formatDecimal(aiVal, 2) : '—'}</span>
                    </td>
                `;

                // Columna 13: Ci — =BUSCARV(C10; extra!$A$2:$K$571; 10; 0) — NO EDITABLE
                html += `
                    <td style="text-align: center; font-weight: bold;">
                        <span id="itemCi_${secIdx}_${itemIdx}">${item.ci !== undefined && item.ci !== '' ? formatDecimal(item.ci, 1) : '1,0'}</span>
                    </td>
                `;

                // Columna 14: Ra — =SI(D10="Producción"; BUSCARV(C10; extra; 5; 0); BUSCARV(C10; extra; 9; 0)) — NO EDITABLE con decimales
                const raDisplay = (item.ra !== undefined && item.ra !== '' && item.ra !== null && !isNaN(parseFloat(item.ra))) ? formatDecimal(item.ra, 1) : '';
                html += `
                    <td style="text-align: center; font-weight: bold;">
                        <span id="itemRa_${secIdx}_${itemIdx}">${raDisplay}</span>
                    </td>
                `;

                // Columnas 15, 16, 17: Qp (Mcal/m2), Nivel de riesgo, N° — Solo en 1ra fila (NO EDITABLES)
                if (isFirst) {
                    html += `
                        <td rowspan="${rowCount}" style="text-align: center; vertical-align: middle; font-weight: 800; font-size: 8pt; background-color: inherit;">
                            <span id="secQp_${secIdx}">${sec.qp ? formatDecimal(sec.qp, 2) : '—'}</span>
                        </td>
                        <td rowspan="${rowCount}" style="text-align: center; vertical-align: middle; font-weight: 800; font-size: 7.5pt; background-color: inherit;">
                            <span id="secRisk_${secIdx}">${sec.risk_level || 'BAJO'}</span>
                        </td>
                        <td rowspan="${rowCount}" style="text-align: center; vertical-align: middle; font-weight: bold; background-color: inherit;">
                            <span id="secNum_${secIdx}">${sec.level_num || 1}</span>
                        </td>
                    `;
                }

                // Columna 18: Mult_1 = =SI(D10="producción"; L10*M10*N10; L10*M10*H10*N10)
                const qxiVal = parseFloat(item.qxi || 0);
                const ciVal = (item.ci !== undefined && item.ci !== '') ? parseFloat(item.ci) : 1.0;
                const hiVal = (item.hi !== undefined && item.hi !== '' && item.hi !== null && !isNaN(parseFloat(item.hi)) && parseFloat(item.hi) > 0) ? parseFloat(item.hi) : 1.0;
                const mult1 = isProd ? (qxiVal * aiVal * ciVal) : (qxiVal * aiVal * hiVal * ciVal);
                item.mult_1 = Math.round(mult1 * 100) / 100;

                html += `
                    <td style="text-align: center; font-weight: bold; background-color: inherit;">
                        <span id="itemMult1_${secIdx}_${itemIdx}">${formatDecimal(mult1, 2)}</span>
                    </td>
                `;

                // Columna 19: Aux_2 = =SI.ERROR(T10*O10*M10; 0)
                const hasRa = (item.ra !== undefined && item.ra !== '' && item.ra !== null && !isNaN(parseFloat(item.ra)));
                const raNum = hasRa ? parseFloat(item.ra) : null;
                let aux2 = 0;
                if (hasRa && mult1 > 0) {
                    aux2 = mult1 * raNum * aiVal;
                }
                item.aux_2 = Math.round(aux2 * 100) / 100;

                html += `
                    <td style="text-align: center; font-weight: bold; background-color: inherit;">
                        <span id="itemAux2_${secIdx}_${itemIdx}">${formatDecimal(aux2, 2)}</span>
                    </td>
                `;

                html += `</tr>`;
            });
        });

        tbody.innerHTML = html;
    }

    function updateSectorName(secIdx, val) {
        if (!localData.sectors[secIdx]) return;
        localData.sectors[secIdx].name = val;
        renderStep2Tables();
        autoSaveFireReport();
    }

    /**
     * Reacción al cambio de Actividad o Tipo (Aplica fórmulas de Excel para qxi, Ci y Ra)
     */
    function onActivityChange(secIdx, itemIdx, val) {
        if (!localData.sectors[secIdx] || !localData.sectors[secIdx].items[itemIdx]) return;
        const item = localData.sectors[secIdx].items[itemIdx];
        item.actividad = val;

        // Auto-calcular qxi, Ci y Ra según catálogo normativo
        const norm = lookupNormativeValues(item.actividad, item.tipo);
        item.qxi = norm.qxi;
        item.ci = norm.ci;
        item.ra = norm.ra;

        const elQxi = document.getElementById(`itemQxi_${secIdx}_${itemIdx}`);
        if (elQxi) elQxi.innerText = norm.qxi;

        const elCi = document.getElementById(`itemCi_${secIdx}_${itemIdx}`);
        if (elCi) elCi.innerText = formatDecimal(norm.ci, 1);

        const elRa = document.getElementById(`itemRa_${secIdx}_${itemIdx}`);
        if (elRa) elRa.innerText = (norm.ra !== undefined && norm.ra !== null && norm.ra !== '' && !isNaN(parseFloat(norm.ra))) ? formatDecimal(norm.ra, 1) : '';

        recalculateSectorQp(secIdx);
        autoSaveFireReport();
    }

    function onTypeChange(secIdx, itemIdx, val) {
        if (!localData.sectors[secIdx] || !localData.sectors[secIdx].items[itemIdx]) return;
        const item = localData.sectors[secIdx].items[itemIdx];
        item.tipo = val;

        // Auto-calcular qxi y Ra aplicando la fórmula según el nuevo Tipo
        const norm = lookupNormativeValues(item.actividad, item.tipo);
        item.qxi = norm.qxi;
        item.ra = norm.ra;

        const elQxi = document.getElementById(`itemQxi_${secIdx}_${itemIdx}`);
        if (elQxi) elQxi.innerText = norm.qxi;

        const elRa = document.getElementById(`itemRa_${secIdx}_${itemIdx}`);
        if (elRa) elRa.innerText = (norm.ra !== undefined && norm.ra !== null && norm.ra !== '' && !isNaN(parseFloat(norm.ra))) ? formatDecimal(norm.ra, 1) : '';

        recalculateSectorQp(secIdx);
        autoSaveFireReport();
    }

    function updateItemDimensions(secIdx, itemIdx, dim, val) {
        if (!localData.sectors[secIdx] || !localData.sectors[secIdx].items[itemIdx]) return;
        const item = localData.sectors[secIdx].items[itemIdx];
        item[dim] = val !== '' ? parseFloat(val) : '';

        const yi = parseFloat(item.yi || 0);
        const xi = parseFloat(item.xi || 0);
        const ai = (yi && xi) ? (yi * xi) : 0;
        item.ai = ai;

        const elAi = document.getElementById(`itemAi_${secIdx}_${itemIdx}`);
        if (elAi) elAi.innerText = ai ? formatDecimal(ai, 2) : '—';

        recalculateSectorQp(secIdx);
        autoSaveFireReport();
    }

    function updateSectorDimensions(secIdx, dim, val) {
        if (!localData.sectors[secIdx]) return;
        localData.sectors[secIdx][dim] = val ? parseFloat(val) : 0;
        
        const lt = parseFloat(localData.sectors[secIdx].lt || 0);
        const at = parseFloat(localData.sectors[secIdx].at || 0);
        const area = (lt && at) ? (lt * at) : 0;
        localData.sectors[secIdx].area_m2 = area;

        const elArea = document.getElementById(`secArea_${secIdx}`);
        if (elArea) elArea.innerText = area ? formatDecimal(area, 2) : '—';

        recalculateSectorQp(secIdx);
        autoSaveFireReport();
    }

    function updateItemField(secIdx, itemIdx, field, val) {
        if (!localData.sectors[secIdx] || !localData.sectors[secIdx].items[itemIdx]) return;
        localData.sectors[secIdx].items[itemIdx][field] = val;
        autoSaveFireReport();
    }

    function updateItemNumeric(secIdx, itemIdx, field, val) {
        if (!localData.sectors[secIdx] || !localData.sectors[secIdx].items[itemIdx]) return;
        localData.sectors[secIdx].items[itemIdx][field] = val !== '' ? parseFloat(val) : '';
        recalculateSectorQp(secIdx);
        autoSaveFireReport();
    }

    function recalculateSectorQp(secIdx) {
        const sec = localData.sectors[secIdx];
        if (!sec) return;

        const lt = parseFloat(sec.lt || 0);
        const at = parseFloat(sec.at || 0);
        const area = (lt && at) ? (lt * at) : parseFloat(sec.area_m2 || 0);
        let sumAux2 = 0;

        (sec.items || []).forEach((item, itemIdx) => {
            const yi = parseFloat(item.yi || 0);
            const xi = parseFloat(item.xi || 0);
            const ai = (yi && xi) ? (yi * xi) : (parseFloat(item.ai || 0));
            const qxi = parseFloat(item.qxi || 0);
            const ci = (item.ci !== undefined && item.ci !== '') ? parseFloat(item.ci) : 1.0;
            const isProd = (item.tipo || 'Producción').toLowerCase().includes('prod');
            const hiVal = (item.hi !== undefined && item.hi !== '' && item.hi !== null && !isNaN(parseFloat(item.hi)) && parseFloat(item.hi) > 0) ? parseFloat(item.hi) : 1.0;

            // Mult_1: =SI(D10="producción"; L10*M10*N10; L10*M10*H10*N10)
            const mult1 = isProd ? (qxi * ai * ci) : (qxi * ai * hiVal * ci);
            item.mult_1 = Math.round(mult1 * 100) / 100;

            // Aux_2: =SI.ERROR(T10*O10*M10; 0) = Mult_1 * Ra * Ai
            const hasRa = (item.ra !== undefined && item.ra !== '' && item.ra !== null && !isNaN(parseFloat(item.ra)));
            const raNum = hasRa ? parseFloat(item.ra) : null;
            let aux2 = 0;
            if (hasRa && mult1 > 0) {
                aux2 = mult1 * raNum * ai;
            }
            item.aux_2 = Math.round(aux2 * 100) / 100;

            sumAux2 += aux2;

            const elMult1 = document.getElementById(`itemMult1_${secIdx}_${itemIdx}`);
            if (elMult1) elMult1.innerText = formatDecimal(mult1, 2);

            const elAux2 = document.getElementById(`itemAux2_${secIdx}_${itemIdx}`);
            if (elAux2) elAux2.innerText = formatDecimal(aux2, 2);
        });

        let qp = 0;
        if (area > 0) {
            // Fórmula de Excel: =SUMA(U10:U13)/(I10*J10*I10*J10) = sum(Aux_2) / (Lt * At * Lt * At)
            const denominator = (lt > 0 && at > 0) ? (lt * at * lt * at) : (area * area);
            qp = (denominator > 0) ? (sumAux2 / denominator) : 0;
        }

        const risk = calculateRiskLevel(qp);
        const levelNum = calculateRiskLevelNum(qp);

        sec.qp = Math.round(qp * 100) / 100;
        sec.risk_level = risk;
        sec.level_num = levelNum;

        const elQp = document.getElementById(`secQp_${secIdx}`);
        if (elQp) elQp.innerText = qp ? formatDecimal(qp, 2) : '—';

        const elRisk = document.getElementById(`secRisk_${secIdx}`);
        if (elRisk) elRisk.innerText = risk;

        const elNum = document.getElementById(`secNum_${secIdx}`);
        if (elNum) elNum.innerText = levelNum;

        // Recalcular resúmenes de Paso 2 y Paso 3
        renderStep2Tables();
        renderStep3Tables();
    }

    function onMacroAreaChange(val) {
        localData.macroarea = val;
        if (localData.macro_summary) localData.macro_summary.macroarea = val;
        if (localData.extinguishers) localData.extinguishers.macroarea = val;
        renderStep2Tables();
        renderStep3Tables();
        autoSaveFireReport();
    }

    function onMacroDimensionsChange() {
        const lt = document.getElementById('macroLtInput')?.value;
        const at = document.getElementById('macroAtInput')?.value;
        localData.dimensions = {
            lt: lt !== '' ? parseFloat(lt) : '',
            at: at !== '' ? parseFloat(at) : ''
        };
        autoSaveFireReport();
    }

    /**
     * Render Step 2: Hojas Carta Horizontal con 2 Tablas (Image 2 + Image 3)
     */
    function renderStep2Tables() {
        const tbodySectors = document.getElementById('step2TableSectorsBody');
        const tbodyMacro = document.getElementById('step2TableMacroBody');
        if (!tbodySectors || !tbodyMacro) return;

        let totalArea = 0;
        let sumATxQP = 0; // Campo ATxQP acumulado de todos los sectores
        let sectorsHtml = '';

        localData.sectors.forEach((sec, idx) => {
            const area = parseFloat(sec.area_m2 || (sec.lt * sec.at) || 0);
            const qp = parseFloat(sec.qp || 0);
            const at_x_qp = area * qp; // Campo interno ATxQP
            sec.at_x_qp = Math.round(at_x_qp * 100) / 100;

            totalArea += area;
            sumATxQP += at_x_qp;

            sectorsHtml += `
                <tr>
                    <td style="font-weight: bold; text-align: left; padding-left: 12px; text-transform: uppercase;">
                        ${escapeHtml(sec.name || `SECTOR ${idx + 1}`)}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        ${formatDecimal(area, 2)}
                    </td>
                    <td style="text-align: center; font-weight: bold;">
                        ${formatDecimal(qp, 2)}
                    </td>
                </tr>
            `;
        });

        tbodySectors.innerHTML = sectorsHtml || `<tr><td colspan="3" style="text-align: center; color: #64748b;">No hay sectores registrados.</td></tr>`;

        // Cálculo de QM de la Macro Área: suma(ATxQP) / Área total
        const qm = (totalArea > 0) ? (sumATxQP / totalArea) : 0;
        
        // Fórmula de riesgo: =SI(J2<=200;"BAJO";SI(J2<=800;"MEDIO";"ALTO")) donde J2 es QM
        const macroRisk = calculateRiskLevel(qm);

        // Fórmula de nivel: =+SI(J2<=100;"1";SI(J2<=200;"2";SI(J2<=300;"3";SI(J2<=400;"4";SI(J2<=800;"5";SI(J2<=1600;"6";SI(J2<=3200;"7";"8")))))))
        const macroLevel = calculateRiskLevelNum(qm);

        localData.macro_summary = {
            macroarea: localData.macroarea || 'PLANTA BAJA',
            total_area: Math.round(totalArea * 100) / 100,
            qm: Math.round(qm * 100) / 100,
            risk_level: macroRisk,
            level_num: macroLevel
        };

        tbodyMacro.innerHTML = `
            <tr>
                <td style="font-weight: bold; text-align: left; padding-left: 12px; text-transform: uppercase;">
                    ${escapeHtml(localData.macroarea || 'PLANTA BAJA')}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    ${formatDecimal(totalArea, 2)}
                </td>
                <td style="text-align: center; font-weight: 800; font-size: 9pt;">
                    ${formatDecimal(qm, 2)}
                </td>
                <td style="text-align: center; font-weight: 800;">
                    ${macroRisk}
                </td>
                <td style="text-align: center; font-weight: bold;">
                    ${macroLevel}
                </td>
            </tr>
        `;
    }

    /**
     * Render Step 3: Dotación y Asignación de Extintores (Image 4)
     */
    function renderStep3Tables() {
        const tbodyFull = document.getElementById('step3TableFullBody');
        const tbodySummary = document.getElementById('step3TableSummaryBody');
        if (!tbodyFull || !tbodySummary) return;

        const macroAreaName = localData.macroarea || 'PLANTA BAJA';
        const totalArea = (localData.macro_summary && localData.macro_summary.total_area > 0)
            ? localData.macro_summary.total_area
            : (localData.sectors.length > 0 ? localData.sectors.reduce((acc, s) => acc + (parseFloat(s.area_m2 || (s.lt * s.at) || 0)), 0) : 18.60);
        const qpMacro = (localData.macro_summary && localData.macro_summary.qm !== undefined) ? localData.macro_summary.qm : 0;
        const riskLevel = localData.macro_summary?.risk_level || calculateRiskLevel(qpMacro);

        const currentType = localData.extinguishers?.extinguisher_type || '50Kg ABC';
        const calc = calculateExtinguisherValues(currentType, totalArea, riskLevel);

        const savedFinalQty = localData.extinguishers?.final_assigned_qty;
        const finalQty = (savedFinalQty !== undefined && savedFinalQty !== null && !isNaN(parseInt(savedFinalQty, 10))) 
            ? parseInt(savedFinalQty, 10) 
            : calc.theoretical_qty;

        localData.extinguishers = {
            macroarea: macroAreaName,
            area_total: Math.round(totalArea * 100) / 100,
            qp_macro: Math.round(qpMacro * 100) / 100,
            risk_level: riskLevel,
            extinguisher_type: currentType,
            potential_a: calc.potential_a,
            potential_bc: calc.potential_bc,
            covered_area_a: calc.covered_area_a,
            covered_area_b: calc.covered_area_b,
            num_ext_a: calc.num_ext_a,
            num_ext_b: calc.num_ext_b,
            theoretical_qty: calc.theoretical_qty,
            final_assigned_qty: finalQty,
            distance_b: calc.distance_b,
            distance_a: calc.distance_a,
            distance_ab: calc.distance_ab
        };

        const extOptions = Object.keys(EXTINGUISHER_TYPES_CATALOG).map(opt => {
            const isSel = (opt === currentType) ? 'selected' : '';
            return `<option value="${opt}" ${isSel}>${opt}</option>`;
        }).join('');

        // Render Tabla 1 Superior (Matriz Completa)
        tbodyFull.innerHTML = `
            <tr style="background-color: #f8cecc;">
                <td style="font-weight: bold; text-transform: uppercase;">${escapeHtml(macroAreaName)}</td>
                <td style="font-weight: bold;">${formatDecimal(totalArea, 2)}</td>
                <td style="font-weight: bold;">${formatDecimal(qpMacro, 2)}</td>
                <td style="font-weight: bold;">${riskLevel}</td>
                <td style="padding: 2px;">
                    <select class="fire-live-input" style="font-weight: bold; text-align: center; width: 100%; cursor: pointer;"
                        onchange="onExtinguisherTypeChange(this.value)">
                        ${extOptions}
                    </select>
                </td>
                <td style="font-weight: bold;">${calc.potential_a || '—'}</td>
                <td style="font-weight: bold;">${calc.potential_bc || '—'}</td>
                <td>${calc.covered_area_a > 0 ? formatDecimal(calc.covered_area_a, 2) : '—'}</td>
                <td>${calc.covered_area_b > 0 ? formatDecimal(calc.covered_area_b, 2) : '—'}</td>
                <td style="font-weight: bold;">${calc.covered_area_a > 0 ? formatDecimal(calc.num_ext_a, 2) : '0,00'}</td>
                <td style="font-weight: bold;">${calc.covered_area_b > 0 ? formatDecimal(calc.num_ext_b, 2) : '0,00'}</td>
                <td style="font-weight: 800; font-size: 8pt; background-color: #f8cecc;">${calc.theoretical_qty}</td>
                <td style="font-weight: 800; font-size: 8pt; background-color: #e2efda;">
                    <input type="number" min="0" step="1" class="fire-live-input" style="text-align: center; font-weight: 800; width: 100%;"
                        value="${finalQty}" oninput="onFinalQtyChange(this.value)">
                </td>
                <td>${calc.distance_b > 0 ? formatDecimal(calc.distance_b, 2) : '—'}</td>
                <td>${calc.distance_a > 0 ? formatDecimal(calc.distance_a, 2) : '—'}</td>
                <td>${calc.distance_ab > 0 ? formatDecimal(calc.distance_ab, 2) : '—'}</td>
            </tr>
        `;

        // Render Tabla 2 Inferior (Resumen de Dotación)
        tbodySummary.innerHTML = `
            <tr>
                <td style="font-weight: bold; text-align: left; padding-left: 10px; text-transform: uppercase;">
                    ${escapeHtml(macroAreaName)}
                </td>
                <td style="font-weight: bold;">${formatDecimal(totalArea, 2)}</td>
                <td style="font-weight: bold;">${formatDecimal(qpMacro, 2)}</td>
                <td style="font-weight: bold;">${riskLevel}</td>
                <td style="font-weight: bold;">${escapeHtml(currentType)}</td>
                <td style="font-weight: bold;">${calc.potential_a || '—'}</td>
                <td style="font-weight: bold;">${calc.potential_bc || '—'}</td>
                <td style="font-weight: 800; font-size: 8.5pt;">${calc.theoretical_qty}</td>
                <td style="font-weight: 800; font-size: 8.5pt; color: #16a34a;">${finalQty}</td>
            </tr>
        `;
    }

    function onExtinguisherTypeChange(typeVal) {
        if (!localData.extinguishers) localData.extinguishers = {};
        localData.extinguishers.extinguisher_type = typeVal;
        renderStep3Tables();
        autoSaveFireReport();
    }

    function onFinalQtyChange(val) {
        if (!localData.extinguishers) localData.extinguishers = {};
        const q = parseInt(val, 10);
        localData.extinguishers.final_assigned_qty = isNaN(q) ? 0 : Math.max(0, q);
        
        // Actualizar celda en la tabla 2 también
        const tbodySummary = document.getElementById('step3TableSummaryBody');
        if (tbodySummary) {
            const lastCell = tbodySummary.querySelector('tr td:last-child');
            if (lastCell) lastCell.innerText = localData.extinguishers.final_assigned_qty;
        }
        autoSaveFireReport();
    }

    /**
     * Auto-guardado asíncrono
     */
    function autoSaveFireReport() {
        clearTimeout(autoSaveTimer);
        setSaveStatus('saving');

        autoSaveTimer = setTimeout(() => {
            fetch(window.FIRE_REPORT_CONFIG.saveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.FIRE_REPORT_CONFIG.csrfToken
                },
                body: JSON.stringify({
                    report_data: localData
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setSaveStatus('saved');
                } else {
                    setSaveStatus('error');
                }
            })
            .catch(() => {
                setSaveStatus('error');
            });
        }, 600);
    }

    function setSaveStatus(status) {
        const badges = [
            document.getElementById('globalAutoSaveBadge'),
            document.getElementById('step2AutoSaveBadge'),
            document.getElementById('step3AutoSaveBadge')
        ];

        badges.forEach(b => {
            if (!b) return;
            b.className = 'fire-auto-save-badge';
            if (status === 'saving') {
                b.classList.add('saving');
                b.innerHTML = `
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="animate-spin">
                        <path d="M21 12a9 9 0 1 1-6.219-8.56"/>
                    </svg>
                    <span>Guardando...</span>
                `;
            } else if (status === 'error') {
                b.classList.add('error');
                b.innerHTML = `
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span>Error al guardar</span>
                `;
            } else {
                b.innerHTML = `
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polyline points="20 6 9 17 4 12" />
                    </svg>
                    <span>Guardado</span>
                `;
            }
        });
    }

    /**
     * Exportación de Paso 2 a Microsoft Word (.doc) en formato Horizontal (Landscape)
     */
    function downloadStep2WordDoc() {
        const installation = window.FIRE_REPORT_CONFIG.installationName;
        const macroAreaName = localData.macroarea || 'PLANTA BAJA';
        const totalArea = localData.macro_summary?.total_area || 85.31;
        const qm = localData.macro_summary?.qm || 13.58;
        const risk = localData.macro_summary?.risk_level || 'BAJO';
        const level = localData.macro_summary?.level_num || 1;

        let sectorsRows = '';
        localData.sectors.forEach((sec, idx) => {
            sectorsRows += `
                <tr>
                    <td style="border: 1px solid #000000; padding: 4px 6px; font-weight: bold; text-align: left; text-transform: uppercase;">
                        ${escapeHtml(sec.name || `Sector ${idx + 1}`)}
                    </td>
                    <td style="border: 1px solid #000000; padding: 4px 6px; text-align: center; font-weight: bold;">
                        ${formatDecimal(sec.area_m2 || 0, 2)}
                    </td>
                    <td style="border: 1px solid #000000; padding: 4px 6px; text-align: center; font-weight: bold;">
                        ${formatDecimal(sec.qp || 0, 2)}
                    </td>
                </tr>
            `;
        });

        const wordHtml = `
<html xmlns:o='urn:schemas-microsoft-com:office:office' 
      xmlns:w='urn:schemas-microsoft-com:office:word' 
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <title>Resumen de Carga de Fuego por Sectores y Macro Área</title>
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
            margin: 0.4in 0.5in 0.4in 0.5in;
            mso-header-margin: 0.2in;
            mso-footer-margin: 0.2in;
            mso-page-orientation: landscape;
        }
        div.Section1 {
            page: Section1;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000000;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            table-layout: fixed;
            width: 100%;
        }
        th, td {
            mso-line-height-rule: exactly;
        }
    </style>
</head>
<body lang='ES-BO'>
<div class='Section1'>

    <!-- ENCABEZADO -->
    <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 6px; margin-bottom: 12px;">
        <div style="font-size: 11.5pt; font-weight: bold; text-transform: uppercase;">
            PLANILLA OFICIAL — RESUMEN DE CARGA DE FUEGO POR SECTORES Y MACRO ÁREA
        </div>
        <div style="font-size: 9pt; font-weight: bold; color: #334155; margin-top: 2px;">
            EVALUACIÓN SEGÚN NORMA BOLIVIANA NB 58005 Y NTP 453
        </div>
    </div>

    <!-- TABLA 1: RESUMEN DE SECTORES (Image 2) -->
    <div style="font-weight: bold; font-size: 9pt; text-transform: uppercase; margin-bottom: 5px;">
        1. Carga de Fuego por Área o Sector
    </div>
    <table style="border: 1.5px solid #000000; margin-bottom: 16px; font-size: 8pt;">
        <thead>
            <tr style="background-color: #fff2cc; background: #fff2cc;">
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: left; font-size: 8.5pt; width: 50%;">ÁREA O SECTOR</th>
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: center; font-size: 8.5pt; width: 25%;">Área (m2)</th>
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: center; font-size: 8.5pt; width: 25%;">Qp (Mcal/m2)</th>
            </tr>
        </thead>
        <tbody>
            ${sectorsRows}
        </tbody>
    </table>

    <!-- TABLA 2: RESUMEN DE MACRO ÁREA (Image 3) -->
    <div style="font-weight: bold; font-size: 9pt; text-transform: uppercase; margin-bottom: 5px;">
        2. Carga de Fuego Total de la Macro Área
    </div>
    <table style="border: 1.5px solid #000000; font-size: 8pt;">
        <thead>
            <tr style="background-color: #fff2cc; background: #fff2cc;">
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: left; font-size: 8.5pt; width: 35%;">Macro área</th>
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: center; font-size: 8.5pt; width: 20%;">Área total</th>
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: center; font-size: 8.5pt; width: 20%;">QM (Mcal/m2)</th>
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: center; font-size: 8.5pt; width: 15%;">Riesgo</th>
                <th style="border: 1px solid #000000; padding: 5px 6px; text-align: center; font-size: 8.5pt; width: 10%;">Nivel</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #000000; padding: 4px 6px; font-weight: bold; text-transform: uppercase;">${escapeHtml(macroAreaName)}</td>
                <td style="border: 1px solid #000000; padding: 4px 6px; text-align: center; font-weight: bold;">${formatDecimal(totalArea, 2)}</td>
                <td style="border: 1px solid #000000; padding: 4px 6px; text-align: center; font-weight: 800; font-size: 9pt;">${formatDecimal(qm, 2)}</td>
                <td style="border: 1px solid #000000; padding: 4px 6px; text-align: center; font-weight: 800;">${risk}</td>
                <td style="border: 1px solid #000000; padding: 4px 6px; text-align: center; font-weight: bold;">${level}</td>
            </tr>
        </tbody>
    </table>

</div>
</body>
</html>
        `;

        const blob = new Blob(['\ufeff', wordHtml], { type: 'application/msword;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        const sanitized = (macroAreaName || 'Resumen_Carga_Fuego').replace(/[^a-zA-Z0-9_-]/g, '_');
        a.download = `Resumen_Carga_Fuego_Sectores_${sanitized}.doc`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    /**
     * Exportación de Paso 3 a Microsoft Word (.doc) en formato Horizontal (Landscape)
     */
    function downloadStep3WordDoc() {
        const installation = window.FIRE_REPORT_CONFIG.installationName;
        const ext = localData.extinguishers || {};
        const macroAreaName = ext.macroarea || localData.macroarea || 'PLANTA BAJA';
        const totalArea = ext.area_total || 85.31;
        const qpMacro = ext.qp_macro || 13.58;
        const riskLevel = ext.risk_level || 'BAJO';

        const extType = ext.extinguisher_type || '50Kg ABC';
        const calc = calculateExtinguisherValues(extType, totalArea, riskLevel);
        const potA = calc.potential_a;
        const potBC = calc.potential_bc;
        const covA = calc.covered_area_a;
        const covB = calc.covered_area_b;
        const numExtA = calc.num_ext_a;
        const numExtB = calc.num_ext_b;
        const theorQty = calc.theoretical_qty;
        const finalQty = (ext.final_assigned_qty !== undefined && ext.final_assigned_qty !== null) ? ext.final_assigned_qty : theorQty;
        const distB = calc.distance_b;
        const distA = calc.distance_a;
        const distAB = calc.distance_ab;

        const wordHtml = `
<html xmlns:o='urn:schemas-microsoft-com:office:office' 
      xmlns:w='urn:schemas-microsoft-com:office:word' 
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <title>Determinación de Dotación de Extintores NB 58005</title>
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
            margin: 0.35in 0.4in 0.35in 0.4in;
            mso-header-margin: 0.2in;
            mso-footer-margin: 0.2in;
            mso-page-orientation: landscape;
        }
        div.Section1 {
            page: Section1;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #000000;
        }
        table {
            border-collapse: collapse;
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
            table-layout: fixed;
            width: 100%;
        }
        th, td {
            mso-line-height-rule: exactly;
        }
    </style>
</head>
<body lang='ES-BO'>
<div class='Section1'>

    <!-- ENCABEZADO -->
    <div style="text-align: center; border-bottom: 2px solid #000000; padding-bottom: 6px; margin-bottom: 10px;">
        <div style="font-size: 11pt; font-weight: bold; text-transform: uppercase;">
            DETERMINACIÓN DE LA DOTACIÓN DE EXTINTORES CONTRA INCENDIOS
        </div>
        <div style="font-size: 8.5pt; font-weight: bold; color: #334155; margin-top: 2px;">
            CÁLCULO DE POTENCIAL EXTINTOR Y ASIGNACIÓN NORMATIVA SEGÚN NB 58005
        </div>
    </div>

    <!-- TABLA 1: MATRIZ COMPLETA (Image 4 Superior) -->
    <div style="font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin-bottom: 4px;">
        1. Matriz de Cálculo de Potencial Extintor y Distancias Máximas de Traslado
    </div>
    <table style="border: 1.5px solid #000000; margin-bottom: 14px; font-size: 6.5pt;">
        <thead>
            <tr style="background-color: #ffffff; background: #ffffff;">
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 75px;">Macro área</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 45px;">A (m2)</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 60px;">Qp MACRO<br>(Mcal/m2)</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 50px;">Nivel de<br>riesgo</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 65px;">Tipo de extintor<br>aproximado</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 50px;">Potencial<br>extintor A</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 50px;">Potencial<br>extintor BC</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 65px;">Superficie cubierta<br>fuegos clase A</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 65px;">Superficie cubierta<br>fuegos clase B</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 50px;">N° ext.<br>clase A</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 50px;">N° ext.<br>clase B</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 60px;">CANTIDAD<br>TEÓRICA</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 60px;">CANTIDAD<br>EN PLANO</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 42px;">Clase B</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 42px;">Clase A</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 42px;">Clase AB</th>
            </tr>
        </thead>
        <tbody>
            <tr style="background-color: #f8cecc; background: #f8cecc;">
                <td style="border: 1px solid #000000; padding: 3px 2px; font-weight: bold; text-align: center; text-transform: uppercase;">${escapeHtml(macroAreaName)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${formatDecimal(totalArea, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${formatDecimal(qpMacro, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${riskLevel}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${escapeHtml(extType)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${escapeHtml(potA)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${escapeHtml(potBC)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center;">${formatDecimal(covA, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center;">${formatDecimal(covB, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${formatDecimal(numExtA, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold;">${formatDecimal(numExtB, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: 800; font-size: 7.5pt; background-color: #f8cecc;">${theorQty}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: 800; font-size: 7.5pt; background-color: #e2efda;">${finalQty}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center;">${formatDecimal(distB, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center;">${formatDecimal(distA, 2)}</td>
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center;">${formatDecimal(distAB, 2)}</td>
            </tr>
        </tbody>
    </table>

    <!-- TABLA 2: RESUMEN DE DOTACIÓN (Image 4 Inferior) -->
    <div style="font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin-bottom: 4px;">
        2. Resumen de Dotación Teórica y Asignación Final de Extintores
    </div>
    <table style="border: 1.5px solid #000000; margin-bottom: 12px; font-size: 7pt;">
        <thead>
            <tr style="background-color: #fff2cc; background: #fff2cc;">
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: left; width: 140px;">Macro área</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 70px;">A (m2)</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 90px;">Qp MACRO<br>(Mcal/m2)</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 75px;">Nivel de<br>riesgo</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 100px;">Tipo de extintor<br>aproximado</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 70px;">Potencial<br>extintor A</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 70px;">Potencial<br>extintor BC</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 85px;">CANTIDAD<br>TEÓRICA</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 85px;">CANTIDAD<br>EN PLANO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border: 1px solid #000000; padding: 4px 3px; font-weight: bold; text-align: left; text-transform: uppercase;">${escapeHtml(macroAreaName)}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: bold;">${formatDecimal(totalArea, 2)}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: bold;">${formatDecimal(qpMacro, 2)}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: bold;">${riskLevel}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: bold;">${escapeHtml(extType)}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: bold;">${escapeHtml(potA)}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: bold;">${escapeHtml(potBC)}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: 800;">${theorQty}</td>
                <td style="border: 1px solid #000000; padding: 4px 3px; text-align: center; font-weight: 800; color: #16a34a;">${finalQty}</td>
            </tr>
        </tbody>
    </table>

</div>
</body>
</html>
        `;

        const blob = new Blob(['\ufeff', wordHtml], { type: 'application/msword;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        const sanitized = (macroAreaName || 'Dotacion_Extintores').replace(/[^a-zA-Z0-9_-]/g, '_');
        a.download = `Dotacion_Extintores_NB58005_${sanitized}.doc`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    /**
     * Helpers de formateo
     */
    function formatDecimal(val, decimals = 2) {
        if (val === null || val === undefined || isNaN(val)) return '0,00';
        const num = parseFloat(val);
        return num.toFixed(decimals).replace('.', ',');
    }

    function formatDimensionNumber(val) {
        if (val === undefined || val === '' || val === null || isNaN(parseFloat(val))) return '—';
        const num = parseFloat(val);
        if (num % 1 === 0) return num.toString();
        return num.toFixed(2).replace(/\.?0+$/, '').replace('.', ',');
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
</script>
@endpush
