@extends('layouts.app')

@section('title', 'Informe Oficial de Vibración Ocupacional — ' . ($module->name ?? 'Monitoreo de Vibración'))

@push('styles')
    @metricStyle('vibracion')
@endpush

@section('content')
<div class="part-report-outer-container">

    <!-- Encabezado de Navegación y Acciones Globales -->
    <div class="report-top-action-bar">
        <div class="top-action-left">
            <a href="{{ route('modules.vibracion', $module->id) }}" class="btn-back-to-module">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Volver al Módulo</span>
            </a>
            <div class="module-breadcrumbs-trail">
                <span class="trail-project">{{ $module->project->name ?? 'Proyecto' }}</span>
                <span class="trail-separator">/</span>
                <span class="trail-module">Vibración Ocupacional</span>
                <span class="trail-separator">/</span>
                <span class="trail-badge">Informe Técnico (ISO 2631-1 & ISO 5349-1)</span>
            </div>
        </div>

        <div class="top-action-right">
            <button type="button" class="btn-global-download-all" onclick="downloadAllVibracionDoc()" title="Descargar informe completo en Microsoft Word (.doc)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span>Descargar Informe Completo (.doc)</span>
            </button>
        </div>
    </div>

    <!-- Stepper de Navegación entre Pasos -->
    <div class="stepper-horizontal-card">
        <div class="stepper-nav-tabs">
            <!-- Paso 1: Resultados por Punto de Medición -->
            <button type="button" class="step-nav-btn active" id="step_tab_1" onclick="switchStep(1)">
                <div class="step-nav-number">1</div>
                <div class="step-nav-text">
                    <span class="step-nav-title">RESULTADOS POR PUNTO DE MEDICIÓN</span>
                    <span class="step-nav-subtitle">Evaluación de Aceleración y Dosis A(8) Individual</span>
                </div>
            </button>

            <!-- Paso 2: Matriz Consolidada de Vibraciones -->
            <button type="button" class="step-nav-btn" id="step_tab_2" onclick="switchStep(2)">
                <div class="step-nav-number">2</div>
                <div class="step-nav-text">
                    <span class="step-nav-title">MATRIZ CONSOLIDADA DE VIBRACIONES</span>
                    <span class="step-nav-subtitle">Registro Integral por Puestos y Cumplimiento Normativo</span>
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
        <!-- PASO 1: RESULTADOS POR PUNTO DE MEDICIÓN (TABLAS POR CADA REGISTRO)       -->
        <!-- ========================================================================= -->
        <div class="step-pane-content" id="step_pane_1" style="display: block;">
            <div class="part-sheet-wrapper">

                <div class="part-sheet-toolbar">
                    <div class="part-toolbar-left">
                        <span class="step-pane-badge">Paso 1 de 2 — Resultados Individuales por Punto</span>
                        <span id="step1AutoSaveBadge" class="header-auto-save-status">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Actualizado</span>
                        </span>
                    </div>

                    <div class="part-toolbar-right">
                        <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadStep1Doc()"
                            title="Descargar documento oficial compatible con Microsoft Word (.doc)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <span>Descargar Word (.doc)</span>
                        </button>
                    </div>
                </div>

                <div class="part-sheet-card" id="step1DocumentSheet">

                    @forelse($measurementsList as $idx => $m)
                        @php
                            $isCE = $m->tipo === 'cuerpo_entero';
                            $statusColor = match($m->estado) {
                                'CUMPLE' => '#15803d',
                                'NIVEL DE ACCIÓN' => '#b45309',
                                'SUPERA LÍMITE' => '#b91c1c',
                                default => '#000000'
                            };
                        @endphp
                        <!-- Card / Tabla por Cada Registro -->
                        <div class="punto-medicion-card-wrapper" style="margin-bottom: 22px; page-break-inside: avoid;">
                            <table class="part-punto-table" style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 8.5pt; border: 1.5px solid #000;">
                                <!-- Fila 1: Punto de medición -->
                                <tr style="background-color: #ffedd5;">
                                    <th style="width: 44%; border: 1px solid #000; padding: 6px 8px; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt;">
                                        PUNTO DE MEDICIÓN
                                    </th>
                                    <th colspan="2" style="width: 56%; border: 1px solid #000; padding: 6px 8px; text-align: center; font-weight: bold; text-transform: uppercase; font-size: 9pt; color: #c2410c;">
                                        {{ $m->point_code }}
                                    </th>
                                </tr>
                                <!-- Fila 2: Área y Puesto de Trabajo -->
                                <tr>
                                    <td style="font-weight: bold; border: 1px solid #000; padding: 5px 8px; background: #fafafa;">Área / Puesto de Trabajo:</td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-weight: 600;">
                                        {{ $m->area }} &bull; {{ $m->puesto_trabajo }}
                                    </td>
                                </tr>
                                <!-- Fila 3: Trabajador & Máquina -->
                                <tr>
                                    <td style="font-weight: bold; border: 1px solid #000; padding: 5px 8px; background: #fafafa;">Trabajador / Equipo Evaluado:</td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 5px 8px; text-align: center;">
                                        {{ $m->trabajador_evaluado }} / {{ $m->maquina_equipo }}
                                    </td>
                                </tr>
                                <!-- Fila 4: Coordenadas UTM -->
                                <tr>
                                    <td style="font-weight: bold; border: 1px solid #000; padding: 5px 8px; background: #fafafa;">Coordenadas UTM:</td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 5px 8px; text-align: center;">
                                        {{ number_format((float)$m->utm_easting, 3, '.', '') }} E; {{ number_format((float)$m->utm_northing, 3, '.', '') }} N; {{ $m->utm_zone ?? '19K' }}
                                    </td>
                                </tr>
                                <!-- Fila 5: Tipo de Monitoreo & Normativa -->
                                <tr style="background-color: #ffedd5;">
                                    <th style="border: 1px solid #000; padding: 6px 8px; text-align: left; font-size: 8.5pt;">Tipo de Monitoreo / Normativa:</th>
                                    <th colspan="2" style="border: 1px solid #000; padding: 6px 8px; text-align: center; font-weight: bold; font-size: 9pt;">
                                        {{ $m->tipo_label }} ({{ $m->normativa }})
                                    </th>
                                </tr>
                                <!-- Fila 6: Fecha y Hora -->
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 8px;">Fecha y Hora de Medición:</td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 5px 8px; text-align: center;">
                                        {{ $m->fecha_fmt }} &bull; {{ $m->hora_fmt }}
                                    </td>
                                </tr>
                                <!-- Fila 7: Duración de Jornada y Prueba -->
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 8px;">Duración de Jornada / Tiempo de Muestreo:</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Jornada: {{ number_format((float)$m->duracion_jornada_h, 1, ',', '.') }} h</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Prueba: {{ $m->duracion_prueba_min }} min</td>
                                </tr>
                                <!-- Fila 8: Tiempo de Exposición Diario -->
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 8px; font-weight: bold;">Tiempo de Exposición Efectiva (Texp):</td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-weight: bold;">
                                        {{ number_format((float)$m->tiempo_expos_h, 2, ',', '.') }} horas
                                    </td>
                                </tr>
                                <!-- Fila 9: Ubicación del Sensor -->
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 8px;">Ubicación del Sensor / Mano Evaluada:</td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 5px 8px; text-align: center;">
                                        {{ $isCE ? $m->ub_acelerometro_label : $m->mano_afectada_label }}
                                    </td>
                                </tr>
                                <!-- Fila 10: Encabezados Ejes Triaxiales -->
                                <tr style="background-color: #ffedd5;">
                                    <th style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Eje X (Longitudinal / awx)</th>
                                    <th style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Eje Y (Transversal / awy)</th>
                                    <th style="border: 1px solid #000; padding: 5px 8px; text-align: center;">Eje Z (Vertical / awz)</th>
                                </tr>
                                <!-- Fila 11: Aceleraciones Aeq -->
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-family: monospace;">{{ number_format((float)$m->aeqx, 4, ',', '.') }} m/s²</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-family: monospace;">{{ number_format((float)$m->aeqy, 4, ',', '.') }} m/s²</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-family: monospace;">{{ number_format((float)$m->aeqz, 4, ',', '.') }} m/s²</td>
                                </tr>
                                <!-- Fila 12: Aceleración Total Ponderada -->
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 8px; font-weight: bold;">Aceleración Total Ponderada ({{ $isCE ? 'Av' : 'Ahv' }}):</td>
                                    <td colspan="2" style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-weight: bold; font-family: monospace;">
                                        {{ number_format((float)$m->atotal, 4, ',', '.') }} m/s²
                                    </td>
                                </tr>
                                <!-- Fila 13: Límites Permisibles -->
                                <tr>
                                    <td style="border: 1px solid #000; padding: 5px 8px;">Nivel de Acción / Valor Límite (VLE):</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-weight: bold; color: #d97706;">Acción: {{ number_format((float)$m->nivel_accion, 2, ',', '.') }} m/s²</td>
                                    <td style="border: 1px solid #000; padding: 5px 8px; text-align: center; font-weight: bold; color: #dc2626;">Límite: {{ number_format((float)$m->limite_vle, 2, ',', '.') }} m/s²</td>
                                </tr>
                                <!-- Fila 14: EXPOSICIÓN DIARIA NORMALIZADA A(8) -->
                                <tr style="background-color: #ffffff;">
                                    <td style="border: 1.5px solid #000; padding: 6px 8px; font-weight: bold; text-transform: uppercase;">
                                        EXPOSICIÓN DIARIA NORMALIZADA A(8):
                                    </td>
                                    <td style="border: 1.5px solid #000; padding: 6px 8px; text-align: center; font-weight: 900; font-size: 10.5pt; color: {{ $statusColor }};">
                                        {{ number_format((float)$m->a8, 4, ',', '.') }} m/s²
                                    </td>
                                    <td style="border: 1.5px solid #000; padding: 6px 8px; text-align: center; font-weight: 900; font-size: 9.5pt; color: {{ $statusColor }};">
                                        {{ $m->estado }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @empty
                        <div style="padding: 24px; text-align: center; color: #64748b; font-style: italic; border: 1px dashed #cbd5e1; border-radius: 8px;">
                            No se han registrado puntos de vibración en este módulo.
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- PASO 2: MATRIZ CONSOLIDADA DE VIBRACIONES (TABLA EN HOJA VERTICAL)       -->
        <!-- ========================================================================= -->
        <div class="step-pane-content" id="step_pane_2" style="display: none;">
            <div class="part-sheet-wrapper">

                <div class="part-sheet-toolbar">
                    <div class="part-toolbar-left">
                        <span class="step-pane-badge">Paso 2 de 2 — Matriz Consolidada de Vibraciones</span>
                        <span id="step2AutoSaveBadge" class="header-auto-save-status">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polyline points="20 6 9 17 4 12" />
                            </svg>
                            <span>Actualizado</span>
                        </span>
                    </div>

                    <div class="part-toolbar-right">
                        <button type="button" class="btn-toolbar-action btn-toolbar-word" onclick="downloadStep2Doc()"
                            title="Descargar documento oficial compatible con Microsoft Word (.doc)">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <span>Descargar Word (.doc)</span>
                        </button>
                    </div>
                </div>

                <div class="part-sheet-card" id="step2DocumentSheet">

                    <!-- Tabla Consolidada de Vibraciones en Hoja Vertical -->
                    <table class="part-consolidada-table" style="width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 7.2pt; border: 1.5px solid #000;">
                        <thead>
                            <tr style="background-color: #ffedd5;">
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 6%;">Código</th>
                                <th style="border: 1px solid #000; padding: 5px 3px; text-align: center; font-weight: bold; width: 12%;">Área</th>
                                <th style="border: 1px solid #000; padding: 5px 3px; text-align: center; font-weight: bold; width: 12%;">Puesto de Trabajo</th>
                                <th style="border: 1px solid #000; padding: 5px 3px; text-align: center; font-weight: bold; width: 11%;">Trabajador Evaluado</th>
                                <th style="border: 1px solid #000; padding: 5px 3px; text-align: center; font-weight: bold; width: 10%;">Máquina / Equipo</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 8%;">Tipo</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 6%;">T.Exp (h)</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 7%;">Aeq X (m/s²)</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 7%;">Aeq Y (m/s²)</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 7%;">Aeq Z (m/s²)</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 8%;">A(8) (m/s²)</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 5%;">Nivel Acción</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 5%;">Límite VLE</th>
                                <th style="border: 1px solid #000; padding: 5px 2px; text-align: center; font-weight: bold; width: 8%;">Evaluación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($measurementsList as $m)
                                @php
                                    $statusColor = match($m->estado) {
                                        'CUMPLE' => '#15803d',
                                        'NIVEL DE ACCIÓN' => '#b45309',
                                        'SUPERA LÍMITE' => '#b91c1c',
                                        default => '#000000'
                                    };
                                @endphp
                                <tr>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-weight: 700; color: #c2410c;">{{ $m->point_code }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 3px; text-align: left; font-weight: 500;">{{ $m->area }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 3px; text-align: left; font-weight: 600;">{{ $m->puesto_trabajo }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 3px; text-align: left;">{{ $m->trabajador_evaluado }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 3px; text-align: left;">{{ $m->maquina_equipo }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-weight: 600;">{{ $m->tipo === 'cuerpo_entero' ? 'Cuerpo' : 'Mano-Brazo' }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center;">{{ number_format((float)$m->tiempo_expos_h, 2, ',', '.') }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-family: monospace;">{{ number_format((float)$m->aeqx, 4, ',', '.') }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-family: monospace;">{{ number_format((float)$m->aeqy, 4, ',', '.') }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-family: monospace;">{{ number_format((float)$m->aeqz, 4, ',', '.') }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-weight: 900; font-family: monospace; color: {{ $statusColor }};">
                                        {{ number_format((float)$m->a8, 4, ',', '.') }}
                                    </td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-weight: 600;">{{ number_format((float)$m->nivel_accion, 2, ',', '.') }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-weight: 600;">{{ number_format((float)$m->limite_vle, 2, ',', '.') }}</td>
                                    <td style="border: 1px solid #000; padding: 4px 2px; text-align: center; font-weight: 800; color: {{ $statusColor }};">
                                        {{ $m->estado }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="14" style="border: 1px solid #000; padding: 16px; text-align: center; color: #64748b; font-style: italic;">
                                        No se han registrado puntos en este monitoreo de vibración.
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
    @page { size: 215.9mm 279.4mm; margin: 12mm 15mm 12mm 15mm; }
    body { background-color: #f1f5f9; font-family: Arial, Helvetica, sans-serif; }
    .part-report-outer-container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 14px 16px 40px 16px; box-sizing: border-box; }
    .report-top-action-bar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 12px; }
    .top-action-left { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
    .btn-back-to-module { display: inline-flex; align-items: center; gap: 6px; background: #ffffff; border: 1.5px solid #cbd5e1; padding: 7px 14px; border-radius: 9999px; font-size: 13px; font-weight: 700; color: #334155; text-decoration: none; transition: all 0.2s ease; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
    .btn-back-to-module:hover { background: #fff7ed; border-color: #f97316; color: #ea580c; transform: translateX(-2px); box-shadow: 0 2px 8px rgba(249, 115, 22, 0.2); }
    .module-breadcrumbs-trail { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; }
    .trail-project { font-weight: 600; color: #475569; }
    .trail-separator { color: #cbd5e1; }
    .trail-module { font-weight: 700; color: #ea580c; }
    .trail-badge { background: #fff7ed; color: #ea580c; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px; border: 1px solid #fed7aa; letter-spacing: 0.3px; }
    .btn-global-download-all { display: inline-flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #ffffff; border: none; padding: 8px 18px; border-radius: 9999px; font-size: 13px; font-weight: 800; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 4px 14px rgba(249, 115, 22, 0.4); }
    .btn-global-download-all:hover { background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); box-shadow: 0 6px 18px rgba(249, 115, 22, 0.55); transform: translateY(-1px); }
    .stepper-horizontal-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); margin-bottom: 16px; }
    .stepper-nav-tabs { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .step-nav-btn { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: all 0.2s ease; text-align: left; }
    .step-nav-btn:hover { background: #fff7ed; border-color: #fed7aa; transform: translateY(-1px); }
    .step-nav-btn.active { background: #fff7ed; border-color: #f97316; box-shadow: 0 2px 10px rgba(249, 115, 22, 0.2); }
    .step-nav-btn.active .step-nav-number { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #ffffff; border-color: #f97316; box-shadow: 0 2px 6px rgba(249, 115, 22, 0.4); }
    .step-nav-btn.active .step-nav-title { color: #ea580c; font-weight: 800; }
    .step-nav-btn.active .step-nav-subtitle { color: #c2410c; font-weight: 600; }
    .stepper-progress-track { width: 100%; height: 3px; background: #e2e8f0; border-radius: 9999px; margin-top: 8px; overflow: hidden; }
    .stepper-progress-fill { height: 100%; background: linear-gradient(90deg, #f97316 0%, #ea580c 100%); transition: width 0.3s ease; }
    .step-nav-number { width: 28px; height: 28px; border-radius: 50%; display: grid; place-items: center; font-size: 12px; font-weight: 800; background: #ffffff; color: #64748b; border: 1.5px solid #cbd5e1; }
    .step-nav-title { font-size: 12.5px; font-weight: 700; color: #334155; }
    .step-nav-subtitle { font-size: 11px; color: #64748b; font-weight: 500; }
    .part-sheet-card { width: 215.9mm; min-height: 279.4mm; background: #ffffff; border: 1px solid #cbd5e1; box-shadow: 0 4px 20px rgba(0,0,0,0.08); padding: 14mm 15mm; box-sizing: border-box; margin: 0 auto; font-size: 8pt; color: #000; }
    .step-pane-badge { font-size: 12px; font-weight: 800; color: #ea580c; background: #fff7ed; padding: 4px 10px; border-radius: 6px; border: 1px solid #fed7aa; }
    .btn-toolbar-action { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 8px; border: 1px solid transparent; cursor: pointer; transition: all 0.2s ease; }
    .btn-toolbar-subtle { background: #ffffff; color: #334155; border-color: #cbd5e1; }
    .btn-toolbar-subtle:hover { background: #fff7ed; border-color: #f97316; color: #ea580c; }
    .btn-toolbar-word { background: linear-gradient(135deg, #f97316 0%, #ea580c 100%); color: #ffffff; border: none; box-shadow: 0 2px 8px rgba(249, 115, 22, 0.35); }
    .btn-toolbar-word:hover { background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%); box-shadow: 0 4px 12px rgba(249, 115, 22, 0.5); transform: translateY(-1px); color: #ffffff; }
    
    .part-consolidada-table thead { display: table-header-group; }
    .part-consolidada-table tr { page-break-inside: avoid; }
    .punto-medicion-card-wrapper { page-break-inside: avoid; }

    @media print {
        body { background: #ffffff !important; }
        .report-top-action-bar, .stepper-horizontal-card, .part-sheet-toolbar { display: none !important; }
        .part-report-outer-container { padding: 0 !important; max-width: 100% !important; margin: 0 !important; }
        .part-sheet-card { border: none !important; box-shadow: none !important; padding: 0 !important; width: 100% !important; min-height: auto !important; }
        .part-consolidada-table thead { display: table-header-group; }
        .part-consolidada-table tr { page-break-inside: avoid; }
    }
</style>
@endpush

@push('scripts')
<script>
    function switchStep(step) {
        document.querySelectorAll('.step-pane-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.step-nav-btn').forEach(el => el.classList.remove('active'));
        
        const pane = document.getElementById(`step_pane_${step}`);
        const tab = document.getElementById(`step_tab_${step}`);
        const bar = document.getElementById('stepperProgressBar');
        
        if (pane) pane.style.display = 'block';
        if (tab) tab.classList.add('active');
        if (bar) bar.style.width = step === 1 ? '50%' : '100%';
    }

    function downloadStep1Doc() {
        const content = document.getElementById('step1DocumentSheet').innerHTML;
        exportHtmlToWord(content, 'Vibracion_Ocupacional_Resultados_Por_Punto.doc', 'portrait');
    }

    function downloadStep2Doc() {
        const content = document.getElementById('step2DocumentSheet').innerHTML;
        exportHtmlToWord(content, 'Vibracion_Ocupacional_Matriz_Consolidada.doc', 'portrait');
    }

    function downloadAllVibracionDoc() {
        const c1 = document.getElementById('step1DocumentSheet').innerHTML;
        const c2 = document.getElementById('step2DocumentSheet').innerHTML;
        const combined = c1 + '<br clear="all" style="page-break-before:always;" />' + c2;
        exportHtmlToWord(combined, 'Informe_Tecnico_Vibracion_Ocupacional.doc', 'portrait');
    }

    function exportHtmlToWord(htmlContent, filename, orientation) {
        const isLandscape = orientation === 'landscape';
        const pageSize = isLandscape ? 'size: 279.4mm 215.9mm; margin: 12mm 15mm;' : 'size: 215.9mm 279.4mm; margin: 15mm;';
        const header = `<html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
        <head><meta charset='utf-8'><title>Informe de Vibración Ocupacional</title>
        <style>
            @page { ${pageSize} }
            table { width: 100%; border-collapse: collapse; font-family: Arial, sans-serif; font-size: 8pt; margin-bottom: 16px; }
            th, td { border: 1px solid #000; padding: 5px; }
            th { background-color: #ffedd5; text-align: center; font-weight: bold; }
        </style>
        </head><body>`;
        const footer = "</body></html>";
        const blob = new Blob(['\ufeff' + header + htmlContent + footer], { type: 'application/msword' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }
</script>
@endpush
