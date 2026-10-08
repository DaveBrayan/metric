@extends('layouts.app')

@section('title', 'Monitoreo de Partículas Ambientales (PM10, PM2.5 & PTS) — Metric v2 Pachabol')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @metricStyle('particulas_ambientales')
@endpush

@section('content')
    {{-- 1. Encabezado y Navegación --}}
    @include('measurements.particulas_ambientales.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual (Datos de Monitoreo & Equipo Muestreador) --}}
    @include('measurements.particulas_ambientales.partials.technical-cards')

    {{-- 3. Tabla Maestra de Mediciones Ambientales, Filtros y Paginación --}}
    @include('measurements.particulas_ambientales.partials.table')

    {{-- ========================================================================= --}}
    {{-- MODALES DEL SISTEMA                                                       --}}
    {{-- ========================================================================= --}}
    @include('measurements.particulas_ambientales.modals.create-modal')
    @include('measurements.particulas_ambientales.modals.edit-modal')
    @include('measurements.particulas_ambientales.modals.map-modal')
    @include('measurements.particulas_ambientales.modals.photo-viewer-modal')
    @include('measurements.particulas_ambientales.modals.export-modal')
    @include('measurements.particulas_ambientales.modals.locations-modal')
    @include('measurements.particulas_ambientales.modals.tables-modal')
@endsection

@push('scripts')
    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <!-- ExcelJS for High-Fidelity Excel Export -->
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

    <!-- Configuración inicial de datos del servidor para el cliente -->
    <script>
        window.METRIC_PARTICULAS_AMB_CONFIG = {
            moduleId: {{ $module->id }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.particulas_ambientales.header.update', $module->id) }}",
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
        window.MODULE_ID = window.METRIC_PARTICULAS_AMB_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_PARTICULAS_AMB_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_PARTICULAS_AMB_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_PARTICULAS_AMB_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_PARTICULAS_AMB_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_PARTICULAS_AMB_CONFIG.registeredByHeader;
    </script>

    {{-- Lógica modularizada de Partículas Ambientales --}}
    @metricScript('particulas_ambientales')
@endpush
