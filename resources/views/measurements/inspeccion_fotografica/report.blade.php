@extends('layouts.app')

@section('title', 'Informe Técnico — Inspección Fotográfica en Campo — Metric v2')

@push('styles')
    @metricStyle('inspeccion_fotografica')
    <style>
        .report-page-container {
            width: 100%;
            padding: 20px 0 60px 0;
            background-color: #f1f5f9;
            min-height: calc(100vh - 70px);
        }

        .report-sheet-wrapper {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 16px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .report-toolbar-card {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 10px 18px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(15, 28, 46, 0.04);
            flex-wrap: wrap;
            box-sizing: border-box;
        }

        .report-printable-sheet {
            width: 100%;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(15, 28, 46, 0.08);
            padding: 36px 42px;
            box-sizing: border-box;
        }

        .report-doc-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 20px;
            border-bottom: 2.5px solid #0284c7;
            margin-bottom: 24px;
        }

        .report-logo-text {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 900;
            color: #0284c7;
            letter-spacing: -0.5px;
        }

        .report-doc-title {
            text-align: right;
        }

        .report-doc-title h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 19px;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .report-doc-title p {
            font-size: 12px;
            color: #64748b;
            margin: 0;
            font-weight: 600;
        }

        .report-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: #f8fafc;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }

        .report-meta-table td, .report-meta-table th {
            padding: 8px 14px;
            font-size: 12px;
        }

        .report-meta-table th {
            font-weight: 800;
            color: #475569;
            width: 20%;
            background: #f1f5f9;
            border-right: 1px solid #e2e8f0;
            text-transform: uppercase;
            font-size: 11px;
        }

        .report-meta-table td {
            color: #0f172a;
            font-weight: 700;
        }

        .report-points-grid {
            display: flex;
            flex-direction: column;
            gap: 24px;
            margin-bottom: 30px;
        }

        .report-point-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            page-break-inside: avoid;
        }

        .report-point-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 10px;
            margin-bottom: 12px;
            border-bottom: 1px solid #f1f5f9;
        }

        .report-point-num {
            font-size: 15px;
            font-weight: 800;
            color: #0284c7;
            font-family: 'Outfit', sans-serif;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .report-point-photos-row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .report-point-photo {
            flex: 1;
            min-width: 220px;
            max-width: 320px;
            height: 200px;
            border-radius: 10px;
            object-fit: cover;
            border: 1px solid #cbd5e1;
        }

        .report-signatures {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            margin-top: 50px;
            padding-top: 30px;
            page-break-inside: avoid;
        }

        .report-sig-block {
            text-align: center;
            border-top: 1.5px solid #0f172a;
            padding-top: 8px;
        }

        .report-sig-block strong {
            display: block;
            font-size: 13px;
            color: #0f172a;
        }

        .report-sig-block span {
            font-size: 11px;
            color: #64748b;
        }

        @media print {
            body {
                background: #ffffff !important;
            }
            .report-page-container {
                padding: 0 !important;
                background: #ffffff !important;
            }
            .report-toolbar-card, .metric-nav-container, .btn-secondary-subtle, .btn-primary-hero-action {
                display: none !important;
            }
            .report-printable-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }
        }
    </style>
@endpush

@section('content')
<div class="report-page-container">
    <div class="report-sheet-wrapper">
        <!-- Barra de Herramientas Superior -->
        <div class="report-toolbar-card">
            <div style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ route('modules.photographic_inspection', $module->id) }}" class="btn-secondary-subtle">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    <span>Volver a la Inspección</span>
                </a>
                <span style="font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 4px 10px; border-radius: 6px;">
                    Vista Preliminar de Impresión
                </span>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" class="btn-primary-hero-action" onclick="window.print()">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <polyline points="6 9 6 2 18 2 18 9" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <rect width="12" height="8" x="6" y="14" />
                    </svg>
                    <span>Imprimir / Guardar PDF</span>
                </button>
            </div>
        </div>

        <!-- Hoja Imprimible Oficial -->
        <div class="report-printable-sheet">
            <!-- Encabezado del Documento -->
            <div class="report-doc-header">
                <div>
                    <div class="report-logo-text">PACHABOL • METRIC</div>
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 0.5px;">SISTEMAS DE MONITOREO Y EVALUACIÓN AMBIENTAL</div>
                </div>
                <div class="report-doc-title">
                    <h1>Informe de Inspección Fotográfica</h1>
                    <p>Registro y Evidencias Visuales de Campo</p>
                </div>
            </div>

            <!-- Tabla de Metadatos de la Inspección -->
            <table class="report-meta-table">
                <tr>
                    <th>Empresa / Cliente:</th>
                    <td>{{ ($project && $project->company) ? $project->company->name : 'General' }}</td>
                    <th>Proyecto:</th>
                    <td>{{ $project ? $project->name : 'General' }}</td>
                </tr>
                <tr>
                    <th>Instalación:</th>
                    <td>{{ $module->installation_name ?: 'Instalación Central' }}</td>
                    <th>Tipo de Evaluación:</th>
                    <td>{{ $module->monitoring_type ?: 'Inspección en Campo' }}</td>
                </tr>
                <tr>
                    <th>Fecha de Inspección:</th>
                    <td>{{ $module->start_date ? $module->start_date->format('d/m/Y') : date('d/m/Y') }}</td>
                    <th>Total Puntos:</th>
                    <td>{{ $inspections->count() }} Puntos Registrados</td>
                </tr>
                <tr>
                    <th>Personal Técnico:</th>
                    <td colspan="3">
                        @if($assignedStaff && $assignedStaff->isNotEmpty())
                            {{ $assignedStaff->pluck('name')->implode(', ') }}
                        @else
                            {{ $userName }}
                        @endif
                    </td>
                </tr>
            </table>

            <!-- Listado de Puntos de Inspección con Fotografías -->
            <div class="report-points-grid">
                @forelse($inspections as $index => $item)
                    <div class="report-point-card">
                        <div class="report-point-card-header">
                            <div class="report-point-num">
                                <span>Punto #{{ $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                                <span style="font-size: 13px; color: #0f172a; font-weight: 700;">— {{ $item->area }}</span>
                            </div>
                            <div style="font-size: 11.5px; color: #64748b; font-family: monospace; font-weight: 600;">
                                @if($item->latitude !== null && $item->longitude !== null)
                                    GPS: {{ number_format($item->latitude, 5) }}, {{ number_format($item->longitude, 5) }}
                                    @if($item->utm_easting && $item->utm_northing)
                                        (UTM E:{{ round($item->utm_easting) }} N:{{ round($item->utm_northing) }})
                                    @endif
                                @elseif($item->location)
                                    {{ $item->location }}
                                @endif
                            </div>
                        </div>

                        <div style="margin-bottom: 8px;">
                            <strong style="font-size: 12px; color: #334155;">Observación:</strong>
                            <span style="font-size: 13px; color: #0f172a; margin-left: 6px;">{{ $item->observation }}</span>
                        </div>

                        @if(!empty($item->description))
                            <div style="margin-bottom: 10px;">
                                <strong style="font-size: 12px; color: #334155;">Descripción:</strong>
                                <span style="font-size: 12.5px; color: #475569; margin-left: 6px;">{{ $item->description }}</span>
                            </div>
                        @endif

                        @php
                            $images = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
                            if (empty($images) && !empty($item->image_path)) {
                                $images = [$item->image_path];
                            }
                        @endphp

                        @if(!empty($images))
                            <div class="report-point-photos-row">
                                @foreach($images as $img)
                                    <img src="{{ $img }}" alt="Foto Punto {{ $item->point_number }}" class="report-point-photo">
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <div style="text-align: center; padding: 40px; color: #94a3b8;">
                        No existen puntos de inspección fotográfica para emitir en este informe.
                    </div>
                @endforelse
            </div>

            <!-- Bloque de Firmas -->
            <div class="report-signatures">
                <div class="report-sig-block">
                    <strong>TÉCNICO DE CAMPO</strong>
                    <span>Especialista en Evaluación Ambiental y de Seguridad</span>
                </div>
                <div class="report-sig-block">
                    <strong>SUPERVISOR / RESPONSABLE</strong>
                    <span>Aprobación Técnica del Estudio</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
