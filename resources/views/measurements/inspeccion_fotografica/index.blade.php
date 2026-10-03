@extends('layouts.app')

@section('title', 'Inspección Fotográfica — Metric v2 Pachabol')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @metricStyle('inspeccion_fotografica')
@endpush

@section('content')
    {{-- 1. Encabezado y Navegación --}}
    @include('measurements.inspeccion_fotografica.partials.header-banner')

    {{-- 2. Encabezado Técnico (Sin equipo asignado, con datos de inspección y resumen) --}}
    @include('measurements.inspeccion_fotografica.partials.technical-cards')

    {{-- 3. Tabla Maestra de Puntos de Inspección Fotográfica --}}
    @include('measurements.inspeccion_fotografica.partials.table')

    {{-- ========================================================================= --}}
    {{-- MODALES DEL SISTEMA                                                       --}}
    {{-- ========================================================================= --}}
    @include('measurements.inspeccion_fotografica.modals.edit-modal')
    @include('measurements.inspeccion_fotografica.modals.map-modal')
    @include('measurements.inspeccion_fotografica.modals.locations-modal')
    @include('measurements.inspeccion_fotografica.modals.photo-viewer-modal')
    @include('measurements.inspeccion_fotografica.modals.export-modal')
    @include('measurements.inspeccion_fotografica.modals.photo-report-modal')

    <!-- Formulario oculto para eliminar punto de inspección -->
    <form id="deleteMeasurementForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Configuración inicial de datos del servidor para el cliente -->
    <script>
        window.METRIC_PHOTOGRAPHIC_CONFIG = {
            moduleId: {{ $module->id }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.photographic_inspection.header.update', $module->id) }}",
            storeMeasurementUrl: "{{ route('modules.photographic_inspection.measurements.store', $module->id) }}",
            registeredByHeader: @json($registeredByHeader),
            measurements: @json($measurements),
            photoReportSettings: @json($photoReportSettings ?? []),
            technicalHeader: {
                installationName: @json($installationName),
                startDateFormatted: @json($startDateFormatted),
                endDateFormatted: @json($endDateFormatted),
                monitoringType: @json($monitoringType)
            }
        };

        window.MODULE_ID = window.METRIC_PHOTOGRAPHIC_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_PHOTOGRAPHIC_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_PHOTOGRAPHIC_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_PHOTOGRAPHIC_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_PHOTOGRAPHIC_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_PHOTOGRAPHIC_CONFIG.registeredByHeader;
    </script>

    {{-- Lógica modularizada de Inspección Fotográfica --}}
    @metricScript('inspeccion_fotografica')

    {{-- SweetAlert2 Notificaciones de Sesión --}}
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Operación Exitosa!',
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
