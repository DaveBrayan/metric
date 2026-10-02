@extends('layouts.app')

@section('title', 'Ergonomía REBA — Metric v2 Pachabol')

@push('styles')
    {{-- Leaflet CSS para Mapas Interactivos --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    {{-- Estilos dedicados de Ergonomía REBA --}}
    @metricStyle('ergonomia_reba')
@endpush

@section('content')
    {{-- 1. Banner Principal con Acciones Rápidas (Cerrar, Mapa General, Exportar, Ver Tablas, + Nueva Evaluación) --}}
    @include('measurements.ergonomia_reba.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual Simétrico (Instalación / Fechas & Ergónomo Asignado / Métodos) --}}
    @include('measurements.ergonomia_reba.partials.technical-cards')

    {{-- 3. Tabla Maestra de Evaluaciones REBA, Filtros y Paginación Reactiva --}}
    @include('measurements.ergonomia_reba.partials.table')

    {{-- 4. Modales del Sistema --}}
    @include('measurements.ergonomia_reba.modals.create-modal')
    @include('measurements.ergonomia_reba.modals.edit-modal')
    @include('measurements.ergonomia_reba.modals.map-modal')
    @include('measurements.ergonomia_reba.modals.photo-viewer-modal')
    @include('measurements.ergonomia_reba.modals.export-modal')
    @include('measurements.ergonomia_reba.modals.locations-modal')
    @include('measurements.ergonomia_reba.modals.photo-report-modal')
    @include('measurements.ergonomia_reba.modals.tables-modal')
    @include('measurements.ergonomia_reba.modals.prompts-modal')

    {{-- Formulario Oculto para Eliminar Evaluación vía POST/DELETE --}}
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
        window.METRIC_ERGONOMIA_REBA_CONFIG = {
            moduleId: {{ $module->id ?? 1 }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.ergonomia_reba.header.update', $module->id ?? 1) }}",
            savePhotoReportSettingsUrl: "{{ route('modules.ergonomia_reba.photo-report-settings.save', $module->id ?? 1) }}",
            measurements: @json($measurements ?? []),
            technicalHeader: {
                installationName: @json($installationName ?? ''),
                startDateFormatted: @json($startDateFormatted ?? ''),
                endDateFormatted: @json($endDateFormatted ?? ''),
                equipmentName: @json($equipmentName ?? ''),
                equipmentBrand: @json($equipmentBrand ?? ''),
                equipmentModel: @json($equipmentModel ?? ''),
                equipmentSerial: @json($equipmentSerial ?? ''),
                monitoringType: @json($monitoringType ?? 'Ergonomía REBA'),
                registeredByHeader: @json($registeredByHeader ?? '')
            },
            photoReportSettings: {
                distribution: "2x3",
                selectedPoints: @json(collect($measurements ?? [])->pluck('id'))
            },
            registeredByHeader: @json($registeredByHeader ?? '')
        };

        window.MODULE_ID = window.METRIC_ERGONOMIA_REBA_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_ERGONOMIA_REBA_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_ERGONOMIA_REBA_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_ERGONOMIA_REBA_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_ERGONOMIA_REBA_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_ERGONOMIA_REBA_CONFIG.registeredByHeader;

        // Modal de Prompts en Vista Principal REBA
        function openRebaPromptsModal() {
            const modal = document.getElementById('rebaPromptsModal');
            if (modal) {
                modal.classList.add('open');
                modal.style.setProperty('display', 'flex', 'important');
                const promptInput = document.getElementById('prompt_input');
                if (promptInput && !promptInput.value.trim()) {
                    resetDefaultRebaPrompt();
                }
            }
        }

        function closeRebaPromptsModal() {
            const modal = document.getElementById('rebaPromptsModal');
            if (modal) {
                modal.classList.remove('open');
                modal.style.setProperty('display', 'none', 'important');
            }
        }

        function resetDefaultRebaPrompt() {
            const firstMeas = (window.ALL_MEASUREMENTS_DATA && window.ALL_MEASUREMENTS_DATA.length > 0) ? window.ALL_MEASUREMENTS_DATA[0] : null;
            const puesto = firstMeas ? (firstMeas.puesto_trabajo || 'Puesto de Trabajo Operativo') : 'Operario de Producción';
            const area = firstMeas ? (firstMeas.area_sector || 'Producción') : 'Producción';
            const scoreFinal = firstMeas ? (firstMeas.score_final || 5) : 5;
            const scoreA = firstMeas ? (firstMeas.score_a || 4) : 4;
            const scoreB = firstMeas ? (firstMeas.score_b || 4) : 4;
            const scoreC = firstMeas ? (firstMeas.score_c || 4) : 4;
            const scoreActividad = firstMeas ? (firstMeas.score_actividad || 1) : 1;
            const riskLevel = firstMeas ? (firstMeas.risk_level || 'Medio') : 'Medio';
            const actionLevel = firstMeas ? (firstMeas.action_level || 'Es necesaria la acción') : 'Es necesaria la acción';

            const defaultPrompt = `Actúa como un especialista senior en Ergonomía Ocupacional y Salud en el Trabajo (SySO).
Realiza una evaluación biomecánica y ergonómica exhaustiva del puesto de trabajo '${puesto}' (Área: ${area}), evaluado mediante el método REBA (Rapid Entire Body Assessment - NTP 601 / ISO 11226 / UNE-EN 1005-4) con los siguientes resultados normativos:

1. PUNTUACIÓN FINAL REBA: ${scoreFinal}/15 (Nivel de Riesgo: ${riskLevel}, Acción: ${actionLevel}).
2. Puntuación Grupo A (Tronco, Cuello, Piernas y Carga/Fuerza): ${scoreA}.
3. Puntuación Grupo B (Brazo, Antebrazo, Muñeca y Agarre): ${scoreB}.
4. Puntuación Tabla C (A vs B): ${scoreC}.
5. Puntuación de Actividad Muscular: +${scoreActividad}.

Genera la redacción técnica especializada dividida obligatoriamente en los siguientes 3 apartados:
- '4. Análisis técnico del puesto': Explicación detallada de la carga postural de cuerpo entero, ángulos articulares de tronco, cuello y extremidades superiores e inferiores, fuerza ejercida y esfuerzo estático/repetitivo (redactado en prosa técnica sin asteriscos ni viñetas).
- '5. Observaciones encontradas': Listado de hallazgos críticos observados en el puesto. Cada observación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).
- '6. Recomendaciones por puesto': Medidas ergonómicas correctivas, preventivas, de ingeniería y administrativas prioritarias y viables. Cada recomendación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).`;

            const promptInput = document.getElementById('prompt_input');
            if (promptInput) {
                promptInput.value = defaultPrompt;
            }
        }

        function copyRebaPromptText() {
            const text = document.getElementById('prompt_input').value;
            if (!text.trim()) {
                return;
            }
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Copiado!',
                            text: 'Prompt copiado al portapapeles',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        alert('¡Prompt copiado al portapapeles!');
                    }
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                alert('¡Prompt copiado al portapapeles!');
            }
        }

        async function saveRebaPrompts() {
            const btn = document.getElementById('btnSaveRebaPrompts');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<span>Guardando...</span>`;
            }

            const firstMeas = (window.ALL_MEASUREMENTS_DATA && window.ALL_MEASUREMENTS_DATA.length > 0) ? window.ALL_MEASUREMENTS_DATA[0] : null;
            const payload = {
                evaluation_id: firstMeas ? firstMeas.id : '',
                prompt: document.getElementById('prompt_input').value,
                analisis_tecnico: document.getElementById('analisis_tecnico_input').value,
                observaciones: document.getElementById('observaciones_input').value,
                recomendaciones: document.getElementById('recomendaciones_input').value,
            };

            try {
                const response = await fetch("{{ route('modules.ergonomia_reba.prompts.save', $module->id ?? 1) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Prompts Guardados!',
                            text: 'Se han guardado correctamente los prompts y el análisis técnico.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('¡Prompts y análisis técnico guardados correctamente!');
                    }
                    setTimeout(() => {
                        closeRebaPromptsModal();
                    }, 600);
                } else {
                    alert('Error al guardar: ' + (data.message || 'Error desconocido'));
                }
            } catch (err) {
                console.error('Error saving reba prompts:', err);
                alert('Error al conectar con el servidor');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg><span>Guardar y Aplicar</span>`;
                }
            }
        }
    </script>

    {{-- Lógica modularizada de Ergonomía REBA --}}
    @metricScript('ergonomia_reba')
@endpush
