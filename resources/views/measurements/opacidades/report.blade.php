@extends('layouts.app')

@section('title', 'Informe Técnico — Planilla de Medición y Evaluación de Opacidad Vehicular — Metric v2')

@push('styles')
    @metricStyle('opacidad')
    <style>
        .opa-report-container {
            width: 100%;
            padding: 20px 0 50px 0;
            background-color: #f1f5f9;
            min-height: calc(100vh - 70px);
        }

        .opa-sheet-wrapper {
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 12px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .opa-sheet-toolbar {
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

        .opa-toolbar-left,
        .opa-toolbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
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

        /* Contenedor de Hoja Carta Horizontal (Exact Letter Landscape: 279.4mm x 215.9mm) */
        .opa-sheet-card {
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
        .opa-live-input {
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

        .opa-live-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .opa-live-input:focus {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        /* Tablas Oficiales de Opacidad Adaptadas a Hoja Carta */
        .opa-report-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #000000;
            table-layout: fixed;
            margin-bottom: 12px;
        }

        .opa-report-table th,
        .opa-report-table td {
            border: 1px solid #000000;
            padding: 4px 3px;
            vertical-align: middle;
            box-sizing: border-box;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: clip;
        }

        .opa-report-table th {
            background-color: #e2efda;
            color: #000000;
            font-weight: bold;
            text-align: center;
            font-size: 6.5pt;
            line-height: 1.25;
            word-break: normal !important;
            overflow-wrap: normal !important;
            word-wrap: normal !important;
            white-space: normal !important;
            hyphens: none !important;
        }

        .opa-report-table tbody tr:hover td {
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

        .table-action-toast.info {
            background-color: #0284c7;
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
            .opa-report-container {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .opa-sheet-toolbar,
            .top-navbar,
            .sidebar-custom,
            nav,
            footer,
            header {
                display: none !important;
            }
            .opa-sheet-wrapper {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .opa-sheet-card {
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
<div class="opa-report-container">
    <div class="opa-sheet-wrapper">

        <!-- Barra de Navegación y Herramientas -->
        <div class="opa-sheet-toolbar">
            <div class="opa-toolbar-left">
                <a href="{{ route('modules.opacity', $module->id) }}" class="btn-toolbar-action btn-toolbar-subtle"
                    title="Volver a la vista de monitoreo de opacidad">
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

            <div class="opa-toolbar-right">
                <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadOpacidadWordDoc()"
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
        <div class="opa-sheet-card" id="opacidadReportSheet">

            <!-- 1. Encabezado Azul Principal -->
            <div style="background-color: #2c73b8; color: #ffffff; text-align: center; padding: 5px 8px; font-weight: bold; font-size: 9.5pt; text-transform: uppercase; border: 1.5px solid #000000; letter-spacing: 0.3px;">
                PLANILLA DE MEDICIÓN Y EVALUACIÓN DE OPACIDAD VEHICULAR Y MAQUINARIA - EMISIÓN DE GASES
            </div>

            <!-- 2. Tabla de Información Técnica y Equipamiento -->
            <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-top: -1.5px; margin-bottom: 10px; font-family: Arial, Helvetica, sans-serif; font-size: 7.5pt;">
                <tbody>
                    <tr>
                        <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">EMPRESA / PROYECTO:</td>
                        <td style="width: 32%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_empresa_proyecto" class="opa-live-input" value="{{ $installationName }}"
                                placeholder="Empresa o proyecto..." oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">EQUIPO:</td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_equipo" class="opa-live-input" value="{{ $equipmentName }}"
                                placeholder="Equipo de medición..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">FECHA DE INICIO DEL MONITOREO:</td>
                        <td style="width: 32%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_fecha_inicio" class="opa-live-input" value="{{ $startDateFormatted }}"
                                placeholder="dd/mm/aaaa" oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">MARCA:</td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_marca" class="opa-live-input" value="{{ $equipmentBrand }}"
                                placeholder="Marca del equipo..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">FECHA DE FINALIZACIÓN DEL MONITOREO:</td>
                        <td style="width: 32%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_fecha_fin" class="opa-live-input" value="{{ $endDateFormatted }}"
                                placeholder="dd/mm/aaaa" oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">MODELO:</td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_modelo" class="opa-live-input" value="{{ $equipmentModel }}"
                                placeholder="Modelo del equipo..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">TIPO DE MONITOREO:</td>
                        <td style="width: 32%; border: 1px solid #000000; padding: 2px 5px; background: #ffffff;">
                            @php
                                $isRutinario = str_contains(strtoupper($monitoringType), 'RUTIN');
                                $isSeguimiento = !empty($monitoringType) ? (str_contains(strtoupper($monitoringType), 'SEGUI') || !$isRutinario) : true;
                            @endphp
                            <div style="display: flex; align-items: center; gap: 14px; font-size: 7.5pt;">
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
                        <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">SERIE:</td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_serie" class="opa-live-input" value="{{ $equipmentSerial }}"
                                placeholder="Número de serie..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- ========================================================================= -->
            <!-- 3. TABLA 1: CARACTERISTICAS DE LOS VEHICULOS -->
            <!-- ========================================================================= -->
            <div style="text-align: center; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin: 8px 0 5px 0; color: #000000; letter-spacing: 0.2px;">
                CARACTERISTICAS DE LOS VEHICULOS
            </div>

            <table class="opa-report-table" id="tableCaracteristicasVehiculos">
                <colgroup>
                    <col style="width: 3.5%;">
                    <col style="width: 14.0%;">
                    <col style="width: 9.0%;">
                    <col style="width: 8.5%;">
                    <col style="width: 8.0%;">
                    <col style="width: 18.0%;">
                    <col style="width: 8.0%;">
                    <col style="width: 7.0%;">
                    <col style="width: 8.0%;">
                    <col style="width: 16.0%;">
                </colgroup>
                <thead>
                    <tr>
                        <th style="text-align: center;">N°</th>
                        <th style="text-align: center;">Coordenadas</th>
                        <th style="text-align: center;">Altitud</th>
                        <th style="text-align: center;">Fecha de<br>medición</th>
                        <th style="text-align: center;">Hora de<br>medición</th>
                        <th style="text-align: center;">Nombre del vehículo /<br>maquinaria</th>
                        <th style="text-align: center;">Marca</th>
                        <th style="text-align: center;">Modelo</th>
                        <th style="text-align: center;">Placa</th>
                        <th style="text-align: center;">Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehiclesList as $v)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $v['num'] }}</td>
                            <td style="text-align: center; font-size: 6.8pt; font-family: monospace;">{{ $v['coordenadas'] ?: '—' }}</td>
                            <td style="text-align: center; font-family: Arial, monospace;">{{ $v['altitud'] ?? '1500-3000' }}</td>
                            <td style="text-align: center;">{{ $v['fecha_medicion'] ?: '—' }}</td>
                            <td style="text-align: center;">{{ $v['hora_medicion'] ?: '—' }}</td>
                            <td style="text-align: left; font-weight: 600;">{{ $v['nombre_vehiculo'] }}</td>
                            <td style="text-align: center;">{{ $v['marca'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $v['modelo'] }}</td>
                            <td style="text-align: center; font-weight: 700; font-family: monospace;">{{ $v['placa'] }}</td>
                            <td style="text-align: left; font-size: 6.8pt;">{{ $v['observaciones_tab1'] ?? '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" style="text-align: center; color: #64748b; padding: 12px;">
                                No se encontraron registros de vehículos en este módulo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- ========================================================================= -->
            <!-- 4. TABLA 2: RESULTADOS OBTENIDO EN COMPARACIÓN CON LA NB 62002 -->
            <!-- ========================================================================= -->
            <div style="text-align: center; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin: 12px 0 5px 0; color: #000000; letter-spacing: 0.2px;">
                RESULTADOS OBTENIDO EN COMPARACIÓN CON LA NB 62002
            </div>

            <table class="opa-report-table" id="tableResultadosOpacidad">
                <colgroup>
                    <col style="width: 3.5%;">
                    <col style="width: 17.5%;">
                    <col style="width: 8.0%;">
                    <col style="width: 7.0%;">
                    <col style="width: 8.0%;">
                    <col style="width: 6.0%;">
                    <col style="width: 7.0%;">
                    <col style="width: 5.0%;">
                    <col style="width: 5.0%;">
                    <col style="width: 5.0%;">
                    <col style="width: 8.0%;">
                    <col style="width: 8.0%;">
                    <col style="width: 12.0%;">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align: center;">N°</th>
                        <th rowspan="2" style="text-align: center;">Nombre del vehículo /<br>maquinaria</th>
                        <th rowspan="2" style="text-align: center;">Marca</th>
                        <th rowspan="2" style="text-align: center;">Modelo</th>
                        <th rowspan="2" style="text-align: center;">Placa</th>
                        <th rowspan="2" style="text-align: center;">Temp.<br>(°C)</th>
                        <th rowspan="2" style="text-align: center;">RPM</th>
                        <th colspan="3" style="text-align: center;">Registro de 3 lecturas<br>K(m-1)</th>
                        <th rowspan="2" style="text-align: center;">Media<br>K(m-1)</th>
                        <th rowspan="2" style="text-align: center;">Limite<br>permisible<br>K(m-1)</th>
                        <th rowspan="2" style="text-align: center;">Observaciones</th>
                    </tr>
                    <tr>
                        <th style="text-align: center; font-size: 6.0pt;">L1</th>
                        <th style="text-align: center; font-size: 6.0pt;">L2</th>
                        <th style="text-align: center; font-size: 6.0pt;">L3</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vehiclesList as $v)
                        @php
                            $isCumple = ($v['observaciones_tab2'] ?? '') === 'Cumple' || !empty($v['is_compliant']);
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: bold;">{{ $v['num'] }}</td>
                            <td style="text-align: left; font-weight: 600;">{{ $v['nombre_vehiculo'] }}</td>
                            <td style="text-align: center;">{{ $v['marca'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $v['modelo'] }}</td>
                            <td style="text-align: center; font-weight: 700; font-family: monospace;">{{ $v['placa'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $v['temp_c'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $v['rpm'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $v['lectura_1'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $v['lectura_2'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $v['lectura_3'] }}</td>
                            <td style="text-align: center; font-weight: 700; font-family: monospace; background: #f0fdf4;">{{ $v['media_k'] }}</td>
                            <td style="text-align: center; font-weight: 600; font-family: monospace;">{{ $v['limite_permisible'] }}</td>
                            <td style="text-align: center; font-weight: bold; {{ $isCumple ? 'color: #16a34a;' : 'color: #dc2626;' }}">
                                {{ $v['observaciones_tab2'] }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" style="text-align: center; color: #64748b; padding: 12px;">
                                No se encontraron resultados de opacidad en este módulo.
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
    const SAVE_REPORT_URL = "{{ route('modules.opacity.report.save', $module->id) }}";
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
            const empresaProyecto = document.getElementById('hdr_empresa_proyecto')?.value || '';
            const equipo = document.getElementById('hdr_equipo')?.value || '';
            const fechaInicio = document.getElementById('hdr_fecha_inicio')?.value || '';
            const fechaFin = document.getElementById('hdr_fecha_fin')?.value || '';
            const marca = document.getElementById('hdr_marca')?.value || '';
            const modelo = document.getElementById('hdr_modelo')?.value || '';
            const serie = document.getElementById('hdr_serie')?.value || '';

            const isRutinario = document.getElementById('hdr_tipo_rutinario')?.checked;
            const isSeguimiento = document.getElementById('hdr_tipo_seguimiento')?.checked;
            let monitoringType = 'Emisión de Humos Vehiculares';
            if (isRutinario) monitoringType = 'Rutinario';
            if (isSeguimiento) monitoringType = 'Seguimiento';

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
                installation_name: empresaProyecto,
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
     * Exportación de las Planillas Oficiales de Opacidad a Microsoft Word (.doc) en formato Horizontal (Landscape)
     */
    function downloadOpacidadWordDoc() {
        const empresaProyecto = document.getElementById('hdr_empresa_proyecto')?.value || '{{ $installationName }}';
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

        // 1. Filas de Tabla 1: Características
        const table1 = document.getElementById('tableCaracteristicasVehiculos');
        const rows1 = table1.querySelectorAll('tbody tr');
        let table1RowsHtml = '';
        rows1.forEach(r => {
            const cells = r.querySelectorAll('td');
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                table1RowsHtml += `<tr><td colspan="10" style="border: 1px solid #000000; padding: 6px; text-align: center; color: #666666;">No hay registros.</td></tr>`;
                return;
            }
            let rowHtml = '<tr>';
            cells.forEach((c, idx) => {
                const text = c.innerText.trim();
                let align = 'center';
                let weight = 'normal';
                if (idx === 0) { align = 'center'; weight = 'bold'; }
                else if (idx === 5 || idx === 9) { align = 'left'; }
                else if (idx === 8) { align = 'center'; weight = 'bold'; }
                rowHtml += `<td style="border: 1px solid #000000; padding: 3px 3px; text-align: ${align}; font-weight: ${weight}; font-size: 7pt; font-family: Arial, sans-serif; vertical-align: middle;">${text}</td>`;
            });
            rowHtml += '</tr>';
            table1RowsHtml += rowHtml;
        });

        // 2. Filas de Tabla 2: Resultados
        const table2 = document.getElementById('tableResultadosOpacidad');
        const rows2 = table2.querySelectorAll('tbody tr');
        let table2RowsHtml = '';
        rows2.forEach(r => {
            const cells = r.querySelectorAll('td');
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                table2RowsHtml += `<tr><td colspan="13" style="border: 1px solid #000000; padding: 6px; text-align: center; color: #666666;">No hay resultados.</td></tr>`;
                return;
            }
            let rowHtml = '<tr>';
            cells.forEach((c, idx) => {
                const text = c.innerText.trim();
                let align = 'center';
                let weight = 'normal';
                let color = '#000000';
                if (idx === 0) { align = 'center'; weight = 'bold'; }
                else if (idx === 1) { align = 'left'; weight = 'bold'; }
                else if (idx === 4 || idx === 10) { align = 'center'; weight = 'bold'; }
                else if (idx === 12) {
                    align = 'center';
                    weight = 'bold';
                    color = text.toLowerCase().includes('no') ? '#dc2626' : '#16a34a';
                }
                rowHtml += `<td style="border: 1px solid #000000; padding: 3px 2.5px; text-align: ${align}; font-weight: ${weight}; font-size: 7pt; color: ${color}; font-family: Arial, sans-serif; vertical-align: middle;">${text}</td>`;
            });
            rowHtml += '</tr>';
            table2RowsHtml += rowHtml;
        });

        const wordHtml = `
<html xmlns:o='urn:schemas-microsoft-com:office:office' 
      xmlns:w='urn:schemas-microsoft-com:office:word' 
      xmlns='http://www.w3.org/TR/REC-html40'>
<head>
    <meta charset='utf-8'>
    <title>Planilla de Medición y Evaluación de Opacidad Vehicular</title>
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
                PLANILLA DE MEDICIÓN Y EVALUACIÓN DE OPACIDAD VEHICULAR Y MAQUINARIA - EMISIÓN DE GASES
            </td>
        </tr>
    </table>

    <!-- TABLA DE INFORMACIÓN TÉCNICA -->
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-top: -1.5px; margin-bottom: 8px; font-family: Arial, sans-serif; font-size: 7.5pt;">
        <tr>
            <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">EMPRESA / PROYECTO:</td>
            <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 5px;">${empresaProyecto}</td>
            <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">EQUIPO:</td>
            <td style="width: 38%; border: 1px solid #000000; padding: 2.5px 5px;">${equipo}</td>
        </tr>
        <tr>
            <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">FECHA DE INICIO DEL MONITOREO:</td>
            <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 5px;">${fechaInicio}</td>
            <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">MARCA:</td>
            <td style="width: 38%; border: 1px solid #000000; padding: 2.5px 5px;">${marca}</td>
        </tr>
        <tr>
            <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">FECHA DE FINALIZACIÓN DEL MONITOREO:</td>
            <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 5px;">${fechaFin}</td>
            <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">MODELO:</td>
            <td style="width: 38%; border: 1px solid #000000; padding: 2.5px 5px;">${modelo}</td>
        </tr>
        <tr>
            <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">TIPO DE MONITOREO:</td>
            <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 5px;">
                RUTINARIO: &nbsp;<b>${rutinarioMark}</b> &nbsp;&nbsp;&nbsp;&nbsp; SEGUIMIENTO: &nbsp;<b>${seguimientoMark}</b>
            </td>
            <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold;">SERIE:</td>
            <td style="width: 38%; border: 1px solid #000000; padding: 2.5px 5px;">${serie}</td>
        </tr>
    </table>

    <!-- TÍTULO TABLA 1: CARACTERISTICAS DE LOS VEHICULOS -->
    <div style="text-align: center; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin: 8px 0 5px 0; color: #000000;">
        CARACTERISTICAS DE LOS VEHICULOS
    </div>

    <!-- TABLA 1 -->
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 7pt; margin-bottom: 12px;">
        <thead>
            <tr style="background-color: #e2efda; background: #e2efda;">
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 22px; font-size: 6.5pt;">N°</th>
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 100px; font-size: 6.5pt;">Coordenadas</th>
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.5pt;">Altitud</th>
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.5pt;">Fecha de<br>medición</th>
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 60px; font-size: 6.5pt;">Hora de<br>medición</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 130px; font-size: 6.5pt;">Nombre del vehículo /<br>maquinaria</th>
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.5pt;">Marca</th>
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 55px; font-size: 6.5pt;">Modelo</th>
                <th style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.5pt;">Placa</th>
                <th style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 120px; font-size: 6.5pt;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            ${table1RowsHtml}
        </tbody>
    </table>

    <!-- TÍTULO TABLA 2: RESULTADOS OBTENIDO EN COMPARACIÓN CON LA NB 62002 -->
    <div style="text-align: center; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin: 10px 0 5px 0; color: #000000;">
        RESULTADOS OBTENIDO EN COMPARACIÓN CON LA NB 62002
    </div>

    <!-- TABLA 2 -->
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 7pt;">
        <thead>
            <tr style="background-color: #e2efda; background: #e2efda;">
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 22px; font-size: 6.5pt;">N°</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 130px; font-size: 6.5pt;">Nombre del vehículo /<br>maquinaria</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.5pt;">Marca</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 55px; font-size: 6.5pt;">Modelo</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.5pt;">Placa</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 45px; font-size: 6.5pt;">Temp.<br>(°C)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 55px; font-size: 6.5pt;">RPM</th>
                <th colspan="3" style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 110px; font-size: 6.5pt;">Registro de 3 lecturas<br>K(m-1)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 60px; font-size: 6.5pt;">Media<br>K(m-1)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.5pt;">Limite<br>permisible<br>K(m-1)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 90px; font-size: 6.5pt;">Observaciones</th>
            </tr>
            <tr style="background-color: #e2efda; background: #e2efda;">
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 36px; font-size: 6.0pt;">L1</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 36px; font-size: 6.0pt;">L2</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 36px; font-size: 6.0pt;">L3</th>
            </tr>
        </thead>
        <tbody>
            ${table2RowsHtml}
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
        const sanitizedName = (empresaProyecto || 'Opacidad').replace(/[^a-zA-Z0-9_-]/g, '_');
        a.download = `Planilla_Opacidad_${sanitizedName}.doc`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showActionToast('Planilla de opacidad descargada en formato Word (.doc)', 'info');
    }
</script>
@endpush
