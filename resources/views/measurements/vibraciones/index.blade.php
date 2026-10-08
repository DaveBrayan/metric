@extends('layouts.app')

@section('title', 'Monitoreo de Vibración Ocupacional (ISO 2631-1 & ISO 5349-1) — Metric v2 Pachabol')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @metricStyle('vibracion')
@endpush

@section('content')
    {{-- 1. Encabezado y Navegación --}}
    @include('measurements.vibraciones.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual (Datos de Monitoreo & Equipo Acelerómetro) --}}
    @include('measurements.vibraciones.partials.technical-cards')

    {{-- 3. Tabla Maestra de Mediciones de Vibración, Filtros y Paginación --}}
    @include('measurements.vibraciones.partials.table')

    {{-- ========================================================================= --}}
    {{-- MODALES DEL SISTEMA                                                       --}}
    {{-- ========================================================================= --}}
    @include('measurements.vibraciones.modals.create-modal')
    @include('measurements.vibraciones.modals.edit-modal')
    @include('measurements.vibraciones.modals.map-modal')
    @include('measurements.vibraciones.modals.photo-viewer-modal')
    @include('measurements.vibraciones.modals.export-modal')
    @include('measurements.vibraciones.modals.locations-modal')
    @include('measurements.vibraciones.modals.tables-modal')
@endsection

@push('scripts')
    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- ExcelJS for High-Fidelity Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

    <!-- Configuración inicial de datos del servidor para el cliente -->
    <script>
        window.METRIC_VIBRACION_CONFIG = {
            moduleId: {{ $module->id }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.vibracion.header.update', $module->id) }}",
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
        window.MODULE_ID = window.METRIC_VIBRACION_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_VIBRACION_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_VIBRACION_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_VIBRACION_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_VIBRACION_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_VIBRACION_CONFIG.registeredByHeader;
    </script>

    {{-- Lógica modularizada de Vibración Ocupacional --}}
    @metricScript('vibracion')
@endpush
