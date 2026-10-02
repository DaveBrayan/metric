@extends('layouts.app')

@section('title', 'Informe Técnico — Planilla de Medición y Evaluación de Dosimetrías — Metric v2')

@push('styles')
    @metricStyle('dosimetria')
    <style>
        .dosi-report-container {
            width: 100%;
            padding: 20px 0 50px 0;
            background-color: #f1f5f9;
            min-height: calc(100vh - 70px);
        }

        .dosi-sheet-wrapper {
            width: 100%;
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 12px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .dosi-sheet-toolbar {
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

        .dosi-toolbar-left,
        .dosi-toolbar-right {
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

        /* Contenedor de Hoja Carta Horizontal (Letter Landscape: 279.4mm x 215.9mm) */
        .dosi-sheet-card {
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
        .dosi-live-input {
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

        .dosi-live-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .dosi-live-input:focus {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }

        /* Tabla Principal Oficial Adaptada Proporcionalmente a Hoja Carta */
        .dosi-report-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #000000;
            table-layout: fixed;
        }

        .dosi-report-table th,
        .dosi-report-table td {
            border: 1px solid #000000;
            padding: 4px 2.5px;
            vertical-align: middle;
            box-sizing: border-box;
            line-height: 1.3;
            overflow: hidden;
            text-overflow: clip;
        }

        .dosi-report-table th {
            background-color: #deebf7;
            color: #000000;
            font-weight: bold;
            text-align: center;
            font-size: 6.2pt;
            line-height: 1.25;
            word-break: normal !important;
            overflow-wrap: normal !important;
            word-wrap: normal !important;
            white-space: normal !important;
            hyphens: none !important;
        }

        .dosi-report-table tbody tr:hover td {
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
            .dosi-report-container {
                background: #ffffff !important;
                padding: 0 !important;
            }
            .dosi-sheet-toolbar,
            .top-navbar,
            .sidebar-custom,
            nav,
            footer,
            header {
                display: none !important;
            }
            .dosi-sheet-wrapper {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .dosi-sheet-card {
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
<div class="dosi-report-container">
    <div class="dosi-sheet-wrapper">

        <!-- Barra de Navegación y Herramientas -->
        <div class="dosi-sheet-toolbar">
            <div class="dosi-toolbar-left">
                <a href="{{ route('modules.dosimetry', $module->id) }}" class="btn-toolbar-action btn-toolbar-subtle"
                    title="Volver a la vista de monitoreo de dosimetría">
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

            <div class="dosi-toolbar-right">
                <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadDosimetriaWordDoc()"
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
        <div class="dosi-sheet-card" id="dosimetriaReportSheet">

            <!-- 1. Encabezado Azul Principal -->
            <div style="background-color: #2c73b8; color: #ffffff; text-align: center; padding: 5px 8px; font-weight: bold; font-size: 9.5pt; text-transform: uppercase; border: 1.5px solid #000000; letter-spacing: 0.3px;">
                MEDICIÓN Y EVALUACIÓN PARA ESTUDIOS PROYECTADOS A 8 HORAS O PARA UN TURNO DE TRABAJO - DOSIMETRÍAS
            </div>

            <!-- 2. Tabla de Información Técnica y Equipamiento -->
            <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; margin-top: -1.5px; margin-bottom: 10px; font-family: Arial, Helvetica, sans-serif; font-size: 7.5pt;">
                <tbody>
                    <tr>
                        <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">EMPRESA / PROYECTO:</td>
                        <td style="width: 32%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_empresa_proyecto" class="dosi-live-input" value="{{ $installationName }}"
                                placeholder="Empresa o proyecto..." oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">EQUIPO:</td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_equipo" class="dosi-live-input" value="{{ $equipmentName }}"
                                placeholder="Equipo de medición..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">FECHA DE INICIO DEL MONITOREO:</td>
                        <td style="width: 32%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_fecha_inicio" class="dosi-live-input" value="{{ $startDateFormatted }}"
                                placeholder="dd/mm/aaaa" oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">MARCA:</td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_marca" class="dosi-live-input" value="{{ $equipmentBrand }}"
                                placeholder="Marca del equipo..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 18%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">FECHA DE FINALIZACIÓN DEL MONITOREO:</td>
                        <td style="width: 32%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_fecha_fin" class="dosi-live-input" value="{{ $endDateFormatted }}"
                                placeholder="dd/mm/aaaa" oninput="autoSaveReportHeader()">
                        </td>
                        <td style="width: 12%; border: 1px solid #000000; padding: 2.5px 5px; font-weight: bold; background: #ffffff;">MODELO:</td>
                        <td style="width: 38%; border: 1px solid #000000; padding: 1.5px 4px; background: #ffffff;">
                            <input type="text" id="hdr_modelo" class="dosi-live-input" value="{{ $equipmentModel }}"
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
                            <input type="text" id="hdr_serie" class="dosi-live-input" value="{{ $equipmentSerial }}"
                                placeholder="Número de serie..." oninput="autoSaveReportHeader()">
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- 3. Título de la Sección -->
            <div style="text-align: center; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin: 9px 0 6px 0; color: #000000; letter-spacing: 0.2px;">
                MEDICIÓN Y EVALUACIÓN PARA ESTUDIOS PROYECTADOS A 8 HORAS O PARA UN TURNO DE TRABAJO - DOSIMETRÍAS
            </div>

            <!-- 4. Matriz de Medición y Evaluación de Dosimetría (14 Columnas Proporcionales) -->
            <table class="dosi-report-table" id="tablePlanillaDosimetria">
                <colgroup>
                    <col style="width: 2.5%;">
                    <col style="width: 9.5%;">
                    <col style="width: 8.0%;">
                    <col style="width: 6.5%;">
                    <col style="width: 7.5%;">
                    <col style="width: 4.5%;">
                    <col style="width: 5.0%;">
                    <col style="width: 6.5%;">
                    <col style="width: 6.5%;">
                    <col style="width: 6.5%;">
                    <col style="width: 8.5%;">
                    <col style="width: 8.5%;">
                    <col style="width: 7.5%;">
                    <col style="width: 12.5%;">
                </colgroup>
                <thead>
                    <tr>
                        <th rowspan="2" style="text-align: center;">N°</th>
                        <th rowspan="2" style="text-align: center;">Área de Trabajo</th>
                        <th rowspan="2" style="text-align: center;">Punto de medición</th>
                        <th rowspan="2" style="text-align: center;">Tipo de ruido</th>
                        <th rowspan="2" style="text-align: center;">Tiempo promedio de Exposición del personal en la jornada (TPE) (Hrs)</th>
                        <th colspan="2" style="text-align: center;">Datos del equipo</th>
                        <th rowspan="2" style="text-align: center;">Tiempo de duración de la medición (Hrs)</th>
                        <th rowspan="2" style="text-align: center;">Nivel de presión sonora (NPS) (max.) (dB (A))</th>
                        <th rowspan="2" style="text-align: center;">Nivel de presión sonora (NPS) (min.) (dB (A))</th>
                        <th rowspan="2" style="text-align: center;">Nivel de presión sonora continuo equivalente Laeq, T (dB (A))(*)</th>
                        <th rowspan="2" style="text-align: center;">Nivel de presión sonora dirario equivalente Laeq, d (dB (A))(**)</th>
                        <th rowspan="2" style="text-align: center;">Dosis de ruido para periodos o estudios a 8 horas (***)</th>
                        <th rowspan="2" style="text-align: center;">Acciones a tomar en caso de superar la Dosis de Ruido Proyectado a 8 horas</th>
                    </tr>
                    <tr>
                        <th style="text-align: center;">Ponderación</th>
                        <th style="text-align: center;">Respuesta</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($measurementsList as $m)
                        <tr>
                            {{-- 1. N° --}}
                            <td style="text-align: center; font-weight: bold;">{{ $m['num'] }}</td>

                            {{-- 2. Área de Trabajo --}}
                            <td style="text-align: left; font-weight: 500;">{{ $m['area'] ?? '—' }}</td>

                            {{-- 3. Punto de medición --}}
                            <td style="text-align: left;">{{ $m['punto_medicion'] ?? '—' }}</td>

                            {{-- 4. Tipo de ruido --}}
                            <td style="text-align: center;">{{ $m['tipo_ruido'] ?? 'Fluctuante' }}</td>

                            {{-- 5. Tiempo promedio de Exposición (TPE) (Hrs) --}}
                            <td style="text-align: center; font-family: Arial, monospace;">
                                {{ isset($m['tiempo_expos_h']) ? (is_numeric($m['tiempo_expos_h']) ? ($m['tiempo_expos_h'] == floor($m['tiempo_expos_h']) ? (int)$m['tiempo_expos_h'] : number_format((float)$m['tiempo_expos_h'], 2, ',', '.')) : $m['tiempo_expos_h']) : '8' }}
                            </td>

                            {{-- 6. Ponderación --}}
                            <td style="text-align: center;">{{ $m['ponderacion'] ?? 'A' }}</td>

                            {{-- 7. Respuesta --}}
                            <td style="text-align: center;">{{ $m['respuesta'] ?? 'LENTA' }}</td>

                            {{-- 8. Tiempo de duración de la medición (Hrs) --}}
                            <td style="text-align: center; font-family: Arial, monospace;">
                                {{ isset($m['duracion_medicion_h']) && $m['duracion_medicion_h'] !== null ? number_format((float)$m['duracion_medicion_h'], 2, ',', '.') : '—' }}
                            </td>

                            {{-- 9. NPS (max.) (dB (A)) --}}
                            <td style="text-align: center; font-family: Arial, monospace;">
                                {{ isset($m['nps_max_db']) && $m['nps_max_db'] !== null ? number_format((float)$m['nps_max_db'], 2, ',', '.') : '—' }}
                            </td>

                            {{-- 10. NPS (min.) (dB (A)) --}}
                            <td style="text-align: center; font-family: Arial, monospace;">
                                {{ isset($m['nps_min_db']) && $m['nps_min_db'] !== null ? number_format((float)$m['nps_min_db'], 2, ',', '.') : '—' }}
                            </td>

                            {{-- 11. Laeq, T (dB (A))(*) --}}
                            <td style="text-align: center; font-family: Arial, monospace; font-weight: 600;">
                                {{ isset($m['leq_t_db']) && $m['leq_t_db'] !== null ? number_format((float)$m['leq_t_db'], 2, ',', '.') : '—' }}
                            </td>

                            {{-- 12. Laeq, d (dB (A))(**) --}}
                            <td style="text-align: center; font-family: Arial, monospace; font-weight: 600;">
                                {{ isset($m['laeq_d_db']) && $m['laeq_d_db'] !== null ? number_format((float)$m['laeq_d_db'], 2, ',', '.') : '—' }}
                            </td>

                            {{-- 13. Dosis de ruido proyectado a 8 horas (***) --}}
                            <td style="text-align: center; font-family: Arial, monospace; font-weight: 600;">
                                {{ isset($m['dosis_ruido']) && $m['dosis_ruido'] !== null ? number_format((float)$m['dosis_ruido'], 2, ',', '.') : '—' }}
                            </td>

                            {{-- 14. Acciones a tomar --}}
                            <td style="text-align: left; font-size: 6.8pt;">
                                {{ $m['acciones_tomar'] ?? '' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" style="text-align: center; color: #64748b; padding: 18px;">
                                No se encontraron registros de medición de dosimetría en este módulo.
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
    const SAVE_REPORT_URL = "{{ route('modules.dosimetry.report.save', $module->id) }}";
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
     * Exportación de la Planilla Oficial de Dosimetría a Microsoft Word (.doc) en formato Horizontal (Landscape)
     */
    function downloadDosimetriaWordDoc() {
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

        // Clonar filas de la tabla
        const tableElement = document.getElementById('tablePlanillaDosimetria');
        const rows = tableElement.querySelectorAll('tbody tr');
        let tableRowsHtml = '';

        rows.forEach(r => {
            const cells = r.querySelectorAll('td');
            if (cells.length === 1 && cells[0].getAttribute('colspan')) {
                tableRowsHtml += `<tr><td colspan="14" style="border: 1px solid #000000; padding: 6px; text-align: center; color: #666666;">No hay registros disponibles.</td></tr>`;
                return;
            }

            let rowHtml = '<tr>';
            cells.forEach((c, idx) => {
                const text = c.innerText.trim();
                let align = 'center';
                let weight = 'normal';
                let fontSize = '7pt';

                if (idx === 0) { align = 'center'; weight = 'bold'; }
                else if (idx === 1 || idx === 2) { align = 'left'; }
                else if (idx >= 3 && idx <= 12) {
                    align = 'center';
                    if (idx >= 10 && idx <= 12) weight = 'bold';
                }
                else if (idx === 13) { align = 'left'; fontSize = '6.8pt'; }

                rowHtml += `<td style="border: 1px solid #000000; padding: 3px 2.5px; text-align: ${align}; font-weight: ${weight}; font-size: ${fontSize}; font-family: Arial, sans-serif; vertical-align: middle;">${text}</td>`;
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
    <title>Planilla de Medición y Evaluación para Estudios Proyectados a 8 Horas - Dosimetrías</title>
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
                MEDICIÓN Y EVALUACIÓN PARA ESTUDIOS PROYECTADOS A 8 HORAS O PARA UN TURNO DE TRABAJO - DOSIMETRÍAS
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

    <!-- TÍTULO DE LA SECCIÓN -->
    <div style="text-align: center; font-weight: bold; font-size: 8.5pt; text-transform: uppercase; margin: 8px 0 5px 0; color: #000000;">
        MEDICIÓN Y EVALUACIÓN PARA ESTUDIOS PROYECTADOS A 8 HORAS O PARA UN TURNO DE TRABAJO - DOSIMETRÍAS
    </div>

    <!-- TABLA DE EVALUACIÓN DE DOSIMETRÍAS (14 COLUMNAS PROPORCIONALES) -->
    <table style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 7pt;">
        <thead>
            <tr style="background-color: #deebf7; background: #deebf7;">
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 18px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">N°</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 75px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Área de Trabajo</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Punto de medición</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 55px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Tipo de ruido</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Tiempo promedio de Exposición del personal en la jornada (TPE) (Hrs)</th>
                <th colspan="2" style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Datos del equipo</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 55px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Tiempo de duración de la medición (Hrs)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 55px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Nivel de presión sonora (NPS) (max.) (dB (A))</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 55px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Nivel de presión sonora (NPS) (min.) (dB (A))</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 70px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Nivel de presión sonora continuo equivalente Laeq, T (dB (A))(*)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 70px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Nivel de presión sonora dirario equivalente Laeq, d (dB (A))(**)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 2px; text-align: center; width: 65px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Dosis de ruido para periodos o estudios a 8 horas (***)</th>
                <th rowspan="2" style="border: 1px solid #000000; padding: 4px 3px; text-align: center; width: 100px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Acciones a tomar en caso de superar la Dosis de Ruido Proyectado a 8 horas</th>
            </tr>
            <tr style="background-color: #deebf7; background: #deebf7;">
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 38px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Ponderación</th>
                <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center; width: 42px; font-size: 6.2pt; line-height: 1.25; word-break: normal; white-space: normal;">Respuesta</th>
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
        const sanitizedName = (empresaProyecto || 'Dosimetria').replace(/[^a-zA-Z0-9_-]/g, '_');
        a.download = `Planilla_Dosimetria_${sanitizedName}.doc`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);

        showActionToast('Planilla de dosimetría descargada en formato Word (.doc)', 'info');
    }
</script>
@endpush
