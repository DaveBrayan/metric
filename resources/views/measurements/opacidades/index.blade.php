@extends('layouts.app')

@php
    $project = $project ?? ($module->project ?? null);
    $projectName = $projectName ?? ($project->name ?? 'Proyecto');
    $companyName = $companyName ?? ($project->company->name ?? '');
    $projectId = $project->id ?? ($module->project_id ?? 0);
@endphp

@section('title', 'Monitoreo de Opacidad (Humos Vehiculares) — Metric v2 Pachabol')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @metricStyle('opacidades')
@endpush

@section('content')
    {{-- 1. Encabezado y Navegación --}}
    @include('measurements.opacidades.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual (Datos de Monitoreo & Equipo Opacímetro) --}}
    @include('measurements.opacidades.partials.technical-cards')

    {{-- 3. Tabla Maestra de Mediciones, Filtros y Paginación Reactiva --}}
    @include('measurements.opacidades.partials.table')

    {{-- ========================================================================= --}}
    {{-- MODALES DEL SISTEMA                                                       --}}
    {{-- ========================================================================= --}}
    @include('measurements.opacidades.modals.create-modal')
    @include('measurements.opacidades.modals.edit-modal')
    @include('measurements.opacidades.modals.map-modal')
    @include('measurements.opacidades.modals.photo-viewer-modal')
    @include('measurements.opacidades.modals.export-modal')
    @include('measurements.opacidades.modals.locations-modal')
    @include('measurements.opacidades.modals.photo-report-modal')
    @include('measurements.opacidades.modals.tables-modal')

    <!-- Formulario oculto para eliminar punto de medición -->
    <form id="deleteMeasurementForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- ExcelJS for High-Fidelity Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

    <!-- Configuración inicial de datos del servidor para el cliente -->
    <script>
        window.METRIC_OPACITY_CONFIG = {
            moduleId: {{ $module->id }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.opacity.header.update', $module->id) }}",
            registeredByHeader: @json($registeredByHeader),
            measurements: @json($measurements),
            photoReportSettings: @json($photoReportSettings ?? []),
            technicalHeader: {
                installationName: @json($installationName),
                startDateFormatted: @json($startDateFormatted),
                endDateFormatted: @json($endDateFormatted),
                monitoringType: @json($monitoringType),
                equipmentName: @json($equipmentName),
                equipmentBrand: @json($equipmentBrand),
                equipmentModel: @json($equipmentModel),
                equipmentSerial: @json($equipmentSerial)
            }
        };
        // Compatibilidad global directa
        window.MODULE_ID = window.METRIC_OPACITY_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_OPACITY_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_OPACITY_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_OPACITY_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_OPACITY_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_OPACITY_CONFIG.registeredByHeader;
    </script>

    {{-- Lógica de Opacidad --}}
    @metricScript('opacidades')

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Guardado!',
                        text: @json(session('success')),
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        timer: 3500,
                        timerProgressBar: true,
                        customClass: {
                            popup: 'metric-swal-popup',
                            confirmButton: 'metric-swal-btn-confirm'
                        }
                    });
                }
            });
        </script>
    @endif
@endpush
