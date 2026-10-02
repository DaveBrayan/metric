@extends('layouts.app')

@section('title', 'Carga de Fuego por Peso — Metric v2 Pachabol')

@push('styles')
    {{-- Leaflet CSS para Mapas Interactivos --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    {{-- Estilos dedicados de Carga de Fuego por Peso --}}
    @metricStyle('fuego_pesos')
@endpush

@section('content')
    {{-- 1. Banner Principal con Acciones Rápidas (Cerrar, Mapa General, Exportar, Ver Tablas, + Nuevo Sector) --}}
    @include('measurements.fuego_pesos.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual Simétrico (Instalación / Fechas / Monitoreo & Equipo Asignado) --}}
    @include('measurements.fuego_pesos.partials.technical-cards')

    {{-- 3. Tabla Maestra de Sectores de Carga de Fuego por Peso, Filtros y Paginación Reactiva --}}
    @include('measurements.fuego_pesos.partials.table')

    {{-- 4. Modales del Sistema --}}
    @include('measurements.fuego_pesos.modals.create-modal')
    @include('measurements.fuego_pesos.modals.edit-modal')
    @include('measurements.fuego_pesos.modals.map-modal')
    @include('measurements.fuego_pesos.modals.photo-viewer-modal')
    @include('measurements.fuego_pesos.modals.export-modal')
    @include('measurements.fuego_pesos.modals.locations-modal')
    @include('measurements.fuego_pesos.modals.photo-report-modal')
    @include('measurements.fuego_pesos.modals.tables-modal')

    {{-- Formulario Oculto para Eliminar Sector vía POST/DELETE --}}
    <form id="deleteMeasurementForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
    {{-- Leaflet JS para Mapas Interactivos --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    {{-- ExcelJS para Exportación de Planilla Oficial --}}
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.3.0/dist/exceljs.min.js"></script>

    {{-- Configuración e Inyección de Datos Reactivos --}}
    <script>
        window.METRIC_FIRE_WEIGHT_CONFIG = {
            moduleId: {{ $module->id ?? 1 }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.fire_weight.header.update', $module->id ?? 1) }}",
            savePhotoReportSettingsUrl: "{{ route('modules.fire_weight.photo-report-settings.save', $module->id ?? 1) }}",
            measurements: @json($measurements ?? []),
            technicalHeader: {
                installationName: @json($installationName ?? ''),
                startDateFormatted: @json($startDateFormatted ?? ''),
                endDateFormatted: @json($endDateFormatted ?? ''),
                equipmentName: @json($equipmentName ?? ''),
                equipmentBrand: @json($equipmentBrand ?? ''),
                equipmentModel: @json($equipmentModel ?? ''),
                equipmentSerial: @json($equipmentSerial ?? ''),
                monitoringType: @json($monitoringType ?? 'Carga de Fuego por Peso'),
                registeredByHeader: @json($registeredByHeader ?? '')
            },
            photoReportSettings: {
                distribution: "2x3",
                selectedPoints: @json(collect($measurements ?? [])->pluck('id'))
            },
            registeredByHeader: @json($registeredByHeader ?? '')
        };

        window.MODULE_ID = window.METRIC_FIRE_WEIGHT_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_FIRE_WEIGHT_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_FIRE_WEIGHT_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_FIRE_WEIGHT_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_FIRE_WEIGHT_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_FIRE_WEIGHT_CONFIG.registeredByHeader;
    </script>

    {{-- Lógica modularizada de Carga de Fuego por Peso --}}
    @metricScript('fuego_pesos')
@endpush
