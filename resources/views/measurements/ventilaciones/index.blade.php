@extends('layouts.app')

@section('title', 'Monitoreo de Ventilación Ocupacional — Metric v2 Pachabol')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @metricStyle('ventilaciones')
@endpush

@section('content')
    {{-- 1. Encabezado y Navegación --}}
    @include('measurements.ventilaciones.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual (Datos de Monitoreo & Equipo Anemómetro) --}}
    @include('measurements.ventilaciones.partials.technical-cards')

    {{-- 3. Tabla Maestra de Mediciones de Ventilación, Filtros y Paginación --}}
    @include('measurements.ventilaciones.partials.table')

    {{-- ========================================================================= --}}
    {{-- MODALES DEL SISTEMA                                                       --}}
    {{-- ========================================================================= --}}
    @include('measurements.ventilaciones.modals.create-modal')
    @include('measurements.ventilaciones.modals.edit-modal')
    @include('measurements.ventilaciones.modals.map-modal')
    @include('measurements.ventilaciones.modals.photo-viewer-modal')
    @include('measurements.ventilaciones.modals.export-modal')
    @include('measurements.ventilaciones.modals.locations-modal')
    @include('measurements.ventilaciones.modals.photo-report-modal')
    @include('measurements.ventilaciones.modals.tables-modal')

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
        window.METRIC_VENTILATION_CONFIG = {
            moduleId: {{ $module->id }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.ventilation.header.update', $module->id) }}",
            registeredByHeader: @json($registeredByHeader),
            measurements: @json($measurements),
            photoReportSettings: @json($photoReportSettings ?? []),
            tiposLocalNorma: @json(\App\Http\Controllers\VentilationController::$tiposLocalNorma),
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
        window.MODULE_ID = window.METRIC_VENTILATION_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_VENTILATION_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_VENTILATION_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_VENTILATION_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_VENTILATION_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_VENTILATION_CONFIG.registeredByHeader;
        window.TIPOS_LOCAL_NORMA = window.METRIC_VENTILATION_CONFIG.tiposLocalNorma;
    </script>

    {{-- Lógica modularizada de Ventilación --}}
    @metricScript('ventilaciones')

    {{-- SweetAlert2 Notificaciones de Sesión --}}
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

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Atención',
                        text: @json(session('error')),
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'metric-swal-popup',
                            confirmButton: 'metric-swal-btn-danger'
                        }
                    });
                }
            });
        </script>
    @endif

    @if(isset($errors) && $errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Errores de Validación',
                        html: `{!! implode('<br>', $errors->all()) !!}`,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        customClass: {
                            popup: 'metric-swal-popup',
                            confirmButton: 'metric-swal-btn-danger'
                        }
                    });
                }
            });
        </script>
    @endif
@endpush
