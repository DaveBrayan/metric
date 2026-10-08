@extends('layouts.app')

@section('title', 'Monitoreo de Contaminantes Químicos — Metric v2 Pachabol')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @metricStyle('contaminantes_quimicos')
@endpush

@section('content')
    {{-- 1. Encabezado y Navegación --}}
    @include('measurements.contaminantes_quimicos.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual (Datos de Monitoreo & Equipo de Muestreo) --}}
    @include('measurements.contaminantes_quimicos.partials.technical-cards')

    {{-- 3. Tabla Maestra de Mediciones de Contaminantes Químicos --}}
    @include('measurements.contaminantes_quimicos.partials.table')

    {{-- ========================================================================= --}}
    {{-- MODALES DEL SISTEMA                                                       --}}
    {{-- ========================================================================= --}}
    @include('measurements.contaminantes_quimicos.modals.create-modal')
    @include('measurements.contaminantes_quimicos.modals.edit-modal')
    @include('measurements.contaminantes_quimicos.modals.map-modal')
    @include('measurements.contaminantes_quimicos.modals.photo-viewer-modal')
    @include('measurements.contaminantes_quimicos.modals.export-modal')
    @include('measurements.contaminantes_quimicos.modals.locations-modal')
    @include('measurements.contaminantes_quimicos.modals.tables-modal')

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
        window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG = {
            moduleId: {{ $module->id }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.contaminantes_quimicos.header.update', $module->id) }}",
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
        window.MODULE_ID = window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_CONTAMINANTES_QUIMICOS_CONFIG.registeredByHeader;
    </script>

    {{-- Lógica modularizada de Contaminantes Químicos --}}
    @metricScript('contaminantes_quimicos')

    {{-- SweetAlert2 Notificaciones de Sesión con diseño oficial METRIC --}}
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
