@extends('layouts.app')

@section('title', 'Ergonomía ROSA — Metric v2 Pachabol')

@push('styles')
    {{-- Leaflet CSS para Mapas Interactivos --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    {{-- Estilos dedicados de Ergonomía ROSA --}}
    @metricStyle('ergonomia_rosa')
@endpush

@section('content')
    {{-- 1. Banner Principal con Acciones Rápidas (Cerrar, Mapa General, Exportar, Ver Tablas, + Nueva Evaluación) --}}
    @include('measurements.ergonomia_rosa.partials.header-banner')

    {{-- 2. Encabezado Técnico Dual Simétrico (Instalación / Fechas & Instrumento / PVD) --}}
    @include('measurements.ergonomia_rosa.partials.technical-cards')

    {{-- 3. Tabla Maestra de Evaluaciones ROSA, Filtros y Paginación Reactiva --}}
    @include('measurements.ergonomia_rosa.partials.table')

    {{-- 4. Modales del Sistema --}}
    @include('measurements.ergonomia_rosa.modals.create-modal')
    @include('measurements.ergonomia_rosa.modals.edit-modal')
    @include('measurements.ergonomia_rosa.modals.map-modal')
    @include('measurements.ergonomia_rosa.modals.photo-viewer-modal')
    @include('measurements.ergonomia_rosa.modals.export-modal')
    @include('measurements.ergonomia_rosa.modals.locations-modal')
    @include('measurements.ergonomia_rosa.modals.photo-report-modal')
    @include('measurements.ergonomia_rosa.modals.tables-modal')
    @include('measurements.ergonomia_rosa.modals.prompts-modal')

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
        window.METRIC_ERGONOMIA_ROSA_CONFIG = {
            moduleId: {{ $module->id ?? 1 }},
            csrfToken: "{{ csrf_token() }}",
            updateHeaderUrl: "{{ route('modules.ergonomia_rosa.header.update', $module->id ?? 1) }}",
            savePhotoReportSettingsUrl: "{{ route('modules.ergonomia_rosa.photo-report-settings.save', $module->id ?? 1) }}",
            measurements: @json($measurements ?? []),
            technicalHeader: {
                installationName: @json($installationName ?? ''),
                startDateFormatted: @json($startDateFormatted ?? ''),
                endDateFormatted: @json($endDateFormatted ?? ''),
                equipmentName: @json($equipmentName ?? ''),
                equipmentBrand: @json($equipmentBrand ?? ''),
                equipmentModel: @json($equipmentModel ?? ''),
                equipmentSerial: @json($equipmentSerial ?? ''),
                monitoringType: @json($monitoringType ?? 'Ergonomía ROSA (Rapid Office Strain Assessment)'),
                registeredByHeader: @json($registeredByHeader ?? '')
            },
            photoReportSettings: {
                distribution: "2x3",
                selectedPoints: @json(collect($measurements ?? [])->pluck('id'))
            },
            registeredByHeader: @json($registeredByHeader ?? '')
        };

        window.MODULE_ID = window.METRIC_ERGONOMIA_ROSA_CONFIG.moduleId;
        window.CSRF_TOKEN = window.METRIC_ERGONOMIA_ROSA_CONFIG.csrfToken;
        window.ALL_MEASUREMENTS_DATA = window.METRIC_ERGONOMIA_ROSA_CONFIG.measurements;
        window.TECHNICAL_HEADER_DATA = window.METRIC_ERGONOMIA_ROSA_CONFIG.technicalHeader;
        window.PHOTO_REPORT_INITIAL_SETTINGS = window.METRIC_ERGONOMIA_ROSA_CONFIG.photoReportSettings;
        window.REGISTERED_BY_HEADER = window.METRIC_ERGONOMIA_ROSA_CONFIG.registeredByHeader;

        // Modal de Prompts en Vista Principal
        function openRosaPromptsModal() {
            const modal = document.getElementById('rosaPromptsModal');
            if (modal) {
                modal.classList.add('open');
                modal.style.setProperty('display', 'flex', 'important');
                const promptInput = document.getElementById('prompt_input');
                if (promptInput && !promptInput.value.trim()) {
                    resetDefaultRosaPrompt();
                }
            }
        }

        function closeRosaPromptsModal() {
            const modal = document.getElementById('rosaPromptsModal');
            if (modal) {
                modal.classList.remove('open');
                modal.style.setProperty('display', 'none', 'important');
            }
        }

        function resetDefaultRosaPrompt() {
            const firstMeas = (window.ALL_MEASUREMENTS_DATA && window.ALL_MEASUREMENTS_DATA.length > 0) ? window.ALL_MEASUREMENTS_DATA[0] : null;
            const puesto = firstMeas ? (firstMeas.puesto_trabajo || 'Puesto de Trabajo General') : 'Puesto Administrativo';
            const area = firstMeas ? (firstMeas.area_sector || 'Administración') : 'Administración';
            const scoreFinal = firstMeas ? (firstMeas.score_final || 6) : 6;
            const scoreA = firstMeas ? (firstMeas.score_a || 5) : 5;
            const scoreB = firstMeas ? (firstMeas.score_b || 2) : 2;
            const scoreC = firstMeas ? (firstMeas.score_c || 5) : 5;
            const scoreD = firstMeas ? (firstMeas.score_d || 5) : 5;

            const defaultPrompt = `Actúa como un especialista senior en Ergonomía Ocupacional y Salud en el Trabajo (SySO).
Realiza una evaluación biomecánica y ergonómica exhaustiva del puesto de trabajo '${puesto}' (Área: ${area}), evaluado mediante el método ROSA (Rapid Office Strain Assessment - ISO 9241 e ISO 11226) con los siguientes resultados normativos:

1. PUNTUACIÓN FINAL ROSA: ${scoreFinal}/10.
2. Puntuación Silla con factor tiempo (Tabla A): ${scoreA}.
3. Puntuación Teléfono y Pantalla (Tabla B): ${scoreB}.
4. Puntuación Ratón y Teclado (Tabla C): ${scoreC}.
5. Puntuación Pantalla y Periféricos (Tabla D): ${scoreD}.

Genera la redacción técnica especializada dividida obligatoriamente en los siguientes 3 apartados:
- '4. Análisis técnico del puesto': Explicación detallada de la carga postural estática y dinámica, ángulo visual hacia la pantalla, alineación de muñecas y soporte de espalda/asiento con base en las puntuaciones obtenidas (redactado en prosa técnica sin asteriscos ni viñetas).
- '5. Observaciones encontradas': Listado de hallazgos críticos observados en el puesto. Cada observación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).
- '6. Recomendaciones por puesto': Medidas ergonómicas correctivas, preventivas y administrativas prioritarias y viables. Cada recomendación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).`;

            const promptInput = document.getElementById('prompt_input');
            if (promptInput) {
                promptInput.value = defaultPrompt;
            }
        }

        function copyRosaPromptText() {
            const text = document.getElementById('prompt_input').value;
            if (!text.trim()) {
                return;
            }
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('¡Prompt copiado al portapapeles!');
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

        async function saveRosaPrompts() {
            const btn = document.getElementById('btnSaveRosaPrompts');
            const btnText = document.getElementById('btnSaveRosaPromptsText');
            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = 'Guardando...';

            const firstMeas = (window.ALL_MEASUREMENTS_DATA && window.ALL_MEASUREMENTS_DATA.length > 0) ? window.ALL_MEASUREMENTS_DATA[0] : null;
            const payload = {
                evaluation_id: firstMeas ? firstMeas.id : '',
                prompt: document.getElementById('prompt_input').value,
                analisis_tecnico: document.getElementById('analisis_tecnico_input').value,
                observaciones: document.getElementById('observaciones_input').value,
                recomendaciones: document.getElementById('recomendaciones_input').value,
            };

            try {
                const response = await fetch("{{ route('modules.ergonomia_rosa.prompts.save', $module->id ?? 1) }}", {
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
                    alert('¡Prompts y análisis técnico guardados correctamente!');
                    setTimeout(() => {
                        closeRosaPromptsModal();
                    }, 600);
                } else {
                    alert('Error al guardar: ' + (data.message || 'Error desconocido'));
                }
            } catch (err) {
                console.error('Error saving rosa prompts:', err);
                alert('Error al conectar con el servidor');
            } finally {
                if (btn) btn.disabled = false;
                if (btnText) btnText.textContent = 'Guardar Información';
            }
        }

        async function generateRosaContentWithAi() {
            const promptInput = document.getElementById('prompt_input');
            const atInput = document.getElementById('analisis_tecnico_input');
            const obsInput = document.getElementById('observaciones_input');
            const recInput = document.getElementById('recomendaciones_input');

            if (promptInput && !promptInput.value.trim()) {
                resetDefaultRosaPrompt();
            }

            // SweetAlert2 centrado en progreso de generación
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Generando informe con Gemini IA',
                    html: `
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 6px 0;">
                            <div style="font-size: 14px; color: #475569; line-height: 1.5; text-align: center;">
                                Procesando la evaluación biomecánica y redactando los <strong>Puntos 4, 5 y 6</strong> del Registro de Evaluación ROSA...
                            </div>
                            <div style="font-size: 12.5px; color: #0284c7; font-weight: 600;">
                                Por favor espera unos momentos ⚡
                            </div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            const firstMeas = (window.ALL_MEASUREMENTS_DATA && window.ALL_MEASUREMENTS_DATA.length > 0) ? window.ALL_MEASUREMENTS_DATA[0] : null;
            const payload = {
                evaluation_id: firstMeas ? firstMeas.id : '',
                prompt: promptInput ? promptInput.value : '',
                prompt_analisis: atInput ? atInput.value : '',
                prompt_observaciones: obsInput ? obsInput.value : '',
                prompt_recomendaciones: recInput ? recInput.value : ''
            };

            try {
                const response = await fetch("{{ route('modules.ergonomia_rosa.generate-ai', $module->id ?? 1) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success && data.data) {
                    if (atInput) atInput.value = data.data.analisis_tecnico || '';
                    if (obsInput) obsInput.value = data.data.observaciones || '';
                    if (recInput) recInput.value = data.data.recomendaciones || '';

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Generación Completada con Éxito!',
                            html: `
                                <div style="font-size: 13.5px; color: #334155; line-height: 1.5; text-align: center;">
                                    Se redactaron y guardaron correctamente los apartados de <strong>Análisis Técnico</strong>, <strong>Observaciones</strong> y <strong>Recomendaciones</strong> con Google Gemini IA.
                                </div>
                            `,
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#0284c7',
                            timer: 4000,
                            timerProgressBar: true
                        });
                    }
                } else {
                    const errorMsg = data.message || 'No se pudo generar el contenido con la IA. Verifica los datos e intenta nuevamente.';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al generar con IA',
                            html: `<div style="font-size: 13.5px; color: #475569;">${errorMsg}</div>`,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            } catch (err) {
                console.error('Error generando con Gemini IA:', err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        html: '<div style="font-size: 13.5px; color: #475569;">Ocurrió un error al conectar con el servidor o el servicio de IA.</div>',
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444'
                    });
                }
            }
        }
    </script>

    {{-- Lógica modularizada de Ergonomía ROSA --}}
    @metricScript('ergonomia_rosa')
@endpush
