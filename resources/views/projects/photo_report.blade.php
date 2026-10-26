@extends('layouts.app')

@section('title', 'Reporte Fotográfico de Monitoreos — Metric v2 Pachabol')

@push('styles')
    @metricStyle('ergonomia_rosa')
    <style>
        /* ==========================================================================
           REPORTE FOTOGRÁFICO HUB — ESTILOS COMPLEMENTARIOS Y SELECTORES
           ========================================================================== */
        .photo-hub-header-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 22px;
            box-shadow: 0 4px 16px rgba(15, 28, 46, 0.04);
        }

        .photo-hub-selectors-grid {
            display: grid;
            grid-template-columns: 1fr 1.3fr auto;
            gap: 16px;
            align-items: flex-end;
        }

        @media (max-width: 900px) {
            .photo-hub-selectors-grid {
                grid-template-columns: 1fr;
            }
        }

        .hub-field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .hub-field-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hub-select-input {
            width: 100%;
            height: 42px;
            background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%230284c7' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat right 14px center;
            background-size: 16px;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 0 38px 0 14px;
            font-size: 13.5px;
            font-weight: 600;
            color: #0f172a;
            outline: none;
            cursor: pointer;
            appearance: none;
            transition: all 0.2s ease;
        }

        .hub-select-input:focus {
            background-color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 0 0 3.5px rgba(2, 132, 199, 0.16);
        }

        /* Ficha Técnica del Módulo Activo */
        .photo-hub-tech-summary {
            background: linear-gradient(135deg, #09131f 0%, #0f1c2e 60%, #132742 100%);
            border: 1px solid rgba(16, 185, 223, 0.35);
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            box-shadow: 0 6px 20px rgba(15, 28, 46, 0.18);
        }

        .hub-summary-badge-title {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .hub-module-avatar {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(16, 185, 223, 0.15);
            border: 1.5px solid rgba(16, 185, 223, 0.4);
            color: #38bdf8;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .hub-module-info h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            margin: 0 0 2px 0;
        }

        .hub-module-info p {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
        }

        .hub-tech-meta-pills {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .hub-meta-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 11.5px;
            color: #e2e8f0;
            font-weight: 600;
        }

        .hub-meta-pill strong {
            color: #38bdf8;
            font-weight: 700;
        }

        /* Toolbar y Contenedor del Reporte */
        .photo-report-container-hub {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(15, 28, 46, 0.04);
            margin-bottom: 40px;
        }

        /* Lightbox Modal */
        .photo-viewer-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(9, 13, 22, 0.88);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .photo-viewer-modal-backdrop.open {
            display: flex !important;
        }

        .photo-viewer-dialog {
            max-width: 900px;
            width: 100%;
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.6);
            display: flex;
            flex-direction: column;
        }

        .photo-viewer-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .photo-viewer-header h3 {
            margin: 0;
            font-size: 14.5px;
            font-weight: 700;
        }

        .photo-viewer-body {
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            max-height: 75vh;
            overflow: hidden;
            background: #020617;
        }

        .photo-viewer-body img {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 8px;
        }

        /* Animación suave de carga */
        @keyframes pulseLoad {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.45; }
        }
        .loading-shimmer {
            animation: pulseLoad 1.2s infinite ease-in-out;
        }
    </style>
@endpush

@section('content')
    <!-- 1. Header Banner Principal con Navegación -->
    <div class="admins-header-banner" style="margin-bottom: 20px;">
        <div>
            <h1 style="display: flex; align-items: center; gap: 10px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                </svg>
                <span>Reporte Fotográfico de Monitoreos</span>
            </h1>
            <p>Generación de mosaicos de evidencia fotográfica, optimización de encuadre y exportación de hojas Carta en PDF de alta fidelidad.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
            <a href="{{ route('modules.index', $selectedProjectId ? ['proyecto' => $selectedProjectId] : []) }}" class="date-capsule" style="text-decoration: none;" title="Ir al listado de módulos">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                    <polyline points="2 17 12 22 22 17"/>
                    <polyline points="2 12 12 17 22 12"/>
                </svg>
                <span>Módulos de Proyectos</span>
            </a>
            <a href="{{ route('projects.index') }}" class="date-capsule" style="text-decoration: none;" title="Ir a proyectos">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                </svg>
                <span>Proyectos</span>
            </a>
        </div>
    </div>

    <!-- 2. Panel Selector de Proyecto y Módulo / Tipo de Monitoreo -->
    <div class="photo-hub-header-card">
        <div class="photo-hub-selectors-grid">
            <!-- Selector de Proyecto -->
            <div class="hub-field-group">
                <label for="projectSelector" class="hub-field-label">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    <span>1. Proyecto Industrial:</span>
                </label>
                <select id="projectSelector" class="hub-select-input" onchange="onProjectSelectChange(this.value)">
                    <option value="todos" {{ empty($selectedProjectId) || $selectedProjectId === 'todos' ? 'selected' : '' }}>— Todos los Proyectos —</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ (string)$selectedProjectId === (string)$proj->id ? 'selected' : '' }}>
                            {{ $proj->name }} @if(!empty($proj->company)) ({{ $proj->company->name }}) @endif
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Selector de Módulo / Tipo de Monitoreo -->
            <div class="hub-field-group">
                <label for="moduleSelector" class="hub-field-label">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                    <span>2. Módulo / Tipo de Monitoreo:</span>
                </label>
                <select id="moduleSelector" class="hub-select-input" onchange="onModuleSelectChange(this.value)">
                    @forelse($modules as $mod)
                        <option value="{{ $mod->id }}" {{ $activeModule && (string)$activeModule->id === (string)$mod->id ? 'selected' : '' }}>
                            {{ $mod->name }} • {{ $mod->project ? $mod->project->name : 'Proyecto' }} ({{ $mod->points_total ?: 0 }} pts)
                        </option>
                    @empty
                        <option value="">No hay módulos registrados en este proyecto</option>
                    @endforelse
                </select>
            </div>

            <!-- Botón de Recarga / Refresco Rápido -->
            <div style="display: flex; gap: 8px;">
                <button type="button" class="btn-secondary-subtle" onclick="refreshCurrentModuleData()" title="Recargar mediciones y fotografías del módulo seleccionado">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 4 23 10 17 10"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                    <span>Actualizar</span>
                </button>
            </div>
        </div>

        <!-- Ficha Técnica Resumida del Módulo Seleccionado -->
        <div class="photo-hub-tech-summary" id="hubTechSummary">
            <div class="hub-summary-badge-title">
                <div class="hub-module-avatar" id="hubModuleAvatar">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="3" rx="2"/>
                        <line x1="8" y1="21" x2="16" y2="21"/>
                        <line x1="12" y1="17" x2="12" y2="21"/>
                    </svg>
                </div>
                <div class="hub-module-info">
                    <h2 id="hubModuleName">{{ $initialData['module']['name'] ?? 'Módulo de Monitoreo' }}</h2>
                    <p id="hubModuleSub">{{ $initialData['module']['normative'] ?? 'Metodología Técnica Pachabol' }}</p>
                </div>
            </div>

            <div class="hub-tech-meta-pills">
                <div class="hub-meta-pill" title="Empresa / Cliente">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/></svg>
                    <span id="hubCompanyVal">{{ $initialData['module']['company_name'] ?? '—' }}</span>
                </div>
                <div class="hub-meta-pill" title="Instalación / Sede">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    <span id="hubInstallationVal">{{ $initialData['module']['installation_name'] ?? '—' }}</span>
                </div>
                <div class="hub-meta-pill" title="Equipo de Medición Calibrado">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                    <span id="hubEquipmentVal">{{ $initialData['module']['equipment_name'] ?? 'Instrumento Certificado' }}</span>
                </div>
                <div class="hub-meta-pill" title="Evaluador / Responsable de Campo">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    <span id="hubStaffVal">{{ $initialData['module']['registered_by'] ?? 'Técnico Ergónomo' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Contenedor Principal del Reporte Fotográfico (Mismas funciones del Modal) -->
    <div class="photo-report-container-hub" id="photoReportHubCard">
        <!-- Toolbar con Selectores de Distribución, Ajuste, Selección y Pestañas -->
        <div class="photo-report-toolbar">
            <!-- Selector de Distribución Mosaico -->
            <div class="photo-toolbar-group">
                <span class="photo-toolbar-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="7" height="7" x="3" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="3" rx="1" />
                        <rect width="7" height="7" x="14" y="14" rx="1" />
                        <rect width="7" height="7" x="3" y="14" rx="1" />
                    </svg>
                    Distribución Mosaico:
                </span>
                <div class="grid-dist-selector" id="gridDistSelector">
                    <button type="button" class="btn-grid-dist active" data-grid="2x3" onclick="changeGridDistribution('2x3')">
                        <span>2x3</span>
                        <span class="dist-sub">(6 fotos)</span>
                    </button>
                    <button type="button" class="btn-grid-dist" data-grid="2x4" onclick="changeGridDistribution('2x4')">
                        <span>2x4</span>
                        <span class="dist-sub">(8 fotos)</span>
                    </button>
                    <button type="button" class="btn-grid-dist" data-grid="3x3" onclick="changeGridDistribution('3x3')">
                        <span>3x3</span>
                        <span class="dist-sub">(9 fotos)</span>
                    </button>
                    <button type="button" class="btn-grid-dist" data-grid="3x4" onclick="changeGridDistribution('3x4')">
                        <span>3x4</span>
                        <span class="dist-sub">(12 fotos)</span>
                    </button>
                </div>
            </div>

            <!-- Selector de Ajuste General de Fotos (Contener vs Expandir) -->
            <div class="photo-toolbar-group">
                <span class="photo-toolbar-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect width="18" height="18" x="3" y="3" rx="2" />
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                        <circle cx="9" cy="9" r="2" />
                    </svg>
                    Ajuste General:
                </span>
                <div class="photo-fit-selector" id="photoGlobalFitSelector">
                    <button type="button" class="btn-photo-fit-global active" data-fit="contain" onclick="setGlobalImageFit('contain')" title="Contener: Imagen completa visible sin recortar">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <rect width="18" height="18" x="3" y="3" rx="2" stroke-dasharray="2 2" />
                            <rect width="10" height="10" x="7" y="7" rx="1" />
                        </svg>
                        <span>Contener</span>
                    </button>
                    <button type="button" class="btn-photo-fit-global" data-fit="cover" onclick="setGlobalImageFit('cover')" title="Expandir: Llena todo el card sin distorsionar la imagen">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <polyline points="15 3 21 3 21 9" />
                            <polyline points="9 21 3 21 3 15" />
                            <line x1="21" y1="3" x2="14" y2="10" />
                            <line x1="3" y1="21" x2="10" y2="14" />
                        </svg>
                        <span>Expandir</span>
                    </button>
                </div>
            </div>

            <!-- Controles de Selección Masiva -->
            <div class="photo-toolbar-group">
                <span class="photo-toolbar-label">Selección:</span>
                <button type="button" class="btn-toolbar-action" id="btnSelectAllPoints" onclick="selectAllPoints(true)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Todas</span>
                </button>
                <button type="button" class="btn-toolbar-action" id="btnDeselectAllPoints" onclick="selectAllPoints(false)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    <span>Ninguna</span>
                </button>
                <span class="photo-selection-pill" id="photoSelectionCountText">0 de 0 seleccionadas</span>
            </div>

            <!-- Pestañas de Vista y Acciones de Guardado y Descarga PDF -->
            <div class="photo-toolbar-group" style="margin-left: auto;">
                <button type="button" class="btn-toolbar-action" id="btnSaveReportSettings" onclick="savePhotoReportSettingsToServer(true)" title="Guardar distribución y selección de fotos en el servidor">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    <span>Guardar Selección</span>
                </button>

                <div class="photo-view-tabs">
                    <button type="button" class="photo-tab-btn active" id="tabBtnInteractive" onclick="switchPhotoReportTab('interactive')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="7" x="3" y="3" rx="1"/><rect width="7" height="7" x="14" y="3" rx="1"/><rect width="7" height="7" x="14" y="14" rx="1"/><rect width="7" height="7" x="3" y="14" rx="1"/></svg>
                        <span>Configurar Mosaico</span>
                    </button>
                    <button type="button" class="photo-tab-btn" id="tabBtnSheets" onclick="switchPhotoReportTab('sheets')">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        <span>Vista Previa Hojas Carta</span>
                    </button>
                </div>

                <span class="photo-selection-pill" id="photoReportPagesIndicator" style="background: #e0f2fe; color: #0284c7; border-color: #bae6fd;">
                    Hojas Carta: 0 (2x3)
                </span>

                <button type="button" class="btn-toolbar-action btn-toolbar-primary" id="btnDownloadPhotoReportPdf" onclick="downloadPhotoReportPDF()" title="Descargar Reporte en PDF (Hoja Carta)">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    <span>Descargar PDF (Carta)</span>
                </button>
            </div>
        </div>

        <!-- Contenedor Principal de Contenido Scrollable -->
        <div class="photo-report-container" style="padding: 24px;">
            <!-- Modo 1: Cuadrícula Interactiva de Configuración -->
            <div id="photoInteractiveView">
                <div class="photo-interactive-grid" id="photoInteractiveGridContainer">
                    <!-- Renderizado dinámico vía JavaScript -->
                </div>
            </div>

            <!-- Modo 2: Vista Previa de Hojas Tamaño Carta -->
            <div id="photoSheetsView" style="display: none;">
                <div class="photo-sheets-container" id="photoSheetsContainer">
                    <!-- Renderizado dinámico de hojas Carta -->
                </div>
            </div>
        </div>
    </div>

    <!-- Contenedor Oculto para Generación Limpia de Canvas PDF -->
    <div id="photoReportPrintArea" style="display: none;"></div>

    <!-- Modal Lightbox para Ampliar Fotografías -->
    <div class="photo-viewer-modal-backdrop" id="photoViewerModal" onclick="closePhotoViewer()">
        <div class="photo-viewer-dialog" onclick="event.stopPropagation()">
            <div class="photo-viewer-header">
                <h3 id="photoViewerTitle">Evidencia Fotográfica</h3>
                <button type="button" class="btn-close-modal" onclick="closePhotoViewer()" style="color: #ffffff; background: rgba(255,255,255,0.1);">✕</button>
            </div>
            <div class="photo-viewer-body">
                <img id="photoViewerImg" src="" alt="Ampliación">
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- html2canvas and jsPDF para Compilación de Alta Definición -->
    <script src="{{ asset('js/html2canvas.min.js') }}"></script>
    <script src="{{ asset('js/jspdf.umd.min.js') }}"></script>

    <script>
        // Inyección de Datos Iniciales y Configuración Reactiva
        window.ALL_MODULES_SUMMARY = @json($allModulesList ?? []);
        window.CURRENT_HUB_DATA = @json($initialData ?? null);
        window.CSRF_TOKEN = "{{ csrf_token() }}";

        // Objeto de Estado del Reporte Fotográfico
        const photoReportConfig = {
            moduleId: {{ $activeModule->id ?? 0 }},
            moduleInfo: null,
            measurements: [],
            grid: '2x3',
            selectedPoints: new Set(),
            photoIndices: {},
            globalFit: 'contain',
            pointFits: {},
            currentTab: 'interactive',
            isInitialized: false
        };

        let photoReportAutoSaveTimeout = null;

        // Inicialización al cargar la página
        document.addEventListener('DOMContentLoaded', function () {
            if (window.CURRENT_HUB_DATA) {
                loadModuleDataIntoEngine(window.CURRENT_HUB_DATA);
            } else {
                renderEmptyState('Selecciona un proyecto y módulo para generar el reporte fotográfico.');
            }
        });

        // 1. Manejo del Selector de Proyectos (Filtrado de Módulos)
        function onProjectSelectChange(projectId) {
            const moduleSelect = document.getElementById('moduleSelector');
            if (!moduleSelect) return;

            moduleSelect.innerHTML = '';
            let filteredModules = window.ALL_MODULES_SUMMARY || [];

            if (projectId !== 'todos' && projectId !== '') {
                filteredModules = filteredModules.filter(m => String(m.project_id) === String(projectId));
            }

            if (filteredModules.length === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = 'No hay módulos registrados en este proyecto';
                moduleSelect.appendChild(opt);
                renderEmptyState('Este proyecto no tiene módulos de medición asociados aún.');
                return;
            }

            filteredModules.forEach((m, idx) => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = `${m.name} • ${m.project_name} (${m.points_count} pts)`;
                if (idx === 0) opt.selected = true;
                moduleSelect.appendChild(opt);
            });

            // Cargar el primer módulo del proyecto
            if (filteredModules.length > 0) {
                fetchAndLoadModule(filteredModules[0].id);
            }
        }

        // 2. Manejo del Selector de Módulos
        function onModuleSelectChange(moduleId) {
            if (!moduleId) return;
            fetchAndLoadModule(moduleId);
        }

        function refreshCurrentModuleData() {
            const moduleSelect = document.getElementById('moduleSelector');
            if (moduleSelect && moduleSelect.value) {
                fetchAndLoadModule(moduleSelect.value);
            }
        }

        // Carga Asíncrona de Datos del Módulo vía API
        async function fetchAndLoadModule(moduleId) {
            const container = document.getElementById('photoInteractiveGridContainer');
            if (container) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #0284c7;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="animation: spin 0.8s linear infinite; margin-bottom: 12px;">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                        </svg>
                        <p style="margin: 0; font-size: 14px; font-weight: 700; color: #334155;">Cargando evidencias fotográficas del módulo...</p>
                    </div>
                `;
            }

            try {
                const response = await fetch(`/reporte-fotografico/modulo/${moduleId}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    }
                });
                const result = await response.json();
                if (result.success && result.data) {
                    loadModuleDataIntoEngine(result.data);
                } else {
                    renderEmptyState('No se pudieron obtener las mediciones del módulo seleccionado.');
                }
            } catch (err) {
                console.error('Error cargando módulo:', err);
                renderEmptyState('Ocurrió un error al conectar con el servidor.');
            }
        }

        // Carga los datos normalizados en el motor reactivo
        function loadModuleDataIntoEngine(data) {
            photoReportConfig.moduleId = data.module.id;
            photoReportConfig.moduleInfo = data.module;
            photoReportConfig.measurements = data.measurements || [];

            // Actualizar tarjeta resumen técnica
            document.getElementById('hubModuleName').textContent = data.module.name || 'Módulo de Monitoreo';
            document.getElementById('hubModuleSub').textContent = data.module.normative || 'Metodología Técnica';
            document.getElementById('hubCompanyVal').textContent = data.module.company_name || '—';
            document.getElementById('hubInstallationVal').textContent = data.module.installation_name || '—';
            document.getElementById('hubEquipmentVal').textContent = data.module.equipment_name || 'Instrumento Certificado';
            document.getElementById('hubStaffVal').textContent = data.module.registered_by || 'Técnico de Campo';

            // Cargar configuración guardada
            const saved = data.photo_report_settings || {};
            const validGrids = ['2x3', '2x4', '3x3', '3x4'];
            photoReportConfig.grid = validGrids.includes(saved.grid) ? saved.grid : '2x3';

            photoReportConfig.selectedPoints.clear();
            if (Array.isArray(saved.selected_points) && saved.selected_points.length > 0) {
                saved.selected_points.forEach(id => photoReportConfig.selectedPoints.add(Number(id)));
            } else {
                photoReportConfig.measurements.forEach(m => photoReportConfig.selectedPoints.add(Number(m.id)));
            }

            photoReportConfig.photoIndices = saved.photo_indices || {};
            photoReportConfig.globalFit = (saved.image_fit === 'cover' ? 'cover' : 'contain');
            photoReportConfig.pointFits = saved.photo_fits || {};

            updateGridSelectorButtonsUI();
            updateGlobalFitButtonsUI();
            renderInteractiveGrid();
            renderPhotoSheets();
            updateSelectionCounterUI();
        }

        // 3. Distribución Mosaico
        function changeGridDistribution(newGrid) {
            const validGrids = ['2x3', '2x4', '3x3', '3x4'];
            if (!validGrids.includes(newGrid)) return;

            photoReportConfig.grid = newGrid;
            updateGridSelectorButtonsUI();
            renderPhotoSheets();
            updateSelectionCounterUI();
            triggerAutoSavePhotoReportConfig();
        }

        function updateGridSelectorButtonsUI() {
            const buttons = document.querySelectorAll('#gridDistSelector .btn-grid-dist');
            buttons.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.grid === photoReportConfig.grid);
            });
        }

        function getPhotosPerPage(grid) {
            switch (grid) {
                case '2x4': return 8;
                case '3x3': return 9;
                case '3x4': return 12;
                case '2x3':
                default: return 6;
            }
        }

        // 4. Ajuste de Fotos (Contener vs Expandir)
        function setGlobalImageFit(fit) {
            if (fit !== 'contain' && fit !== 'cover') return;
            photoReportConfig.globalFit = fit;
            photoReportConfig.pointFits = {};
            updateGlobalFitButtonsUI();
            renderInteractiveGrid();
            renderPhotoSheets();
            triggerAutoSavePhotoReportConfig();
        }

        function updateGlobalFitButtonsUI() {
            const buttons = document.querySelectorAll('#photoGlobalFitSelector .btn-photo-fit-global');
            buttons.forEach(btn => {
                btn.classList.toggle('active', btn.dataset.fit === (photoReportConfig.globalFit || 'contain'));
            });
        }

        function setPointImageFit(pointId, fit, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            pointId = Number(pointId);
            if (fit !== 'contain' && fit !== 'cover') return;

            photoReportConfig.pointFits[pointId] = fit;

            const card = document.getElementById('photoPointCard_' + pointId);
            if (card) {
                const imgBox = card.querySelector('.photo-card-img-box');
                if (imgBox) {
                    imgBox.classList.remove('fit-contain', 'fit-cover');
                    imgBox.classList.add('fit-' + fit);
                }
                const btns = card.querySelectorAll('.btn-card-fit');
                btns.forEach(b => {
                    b.classList.toggle('active', b.dataset.fit === fit);
                });
            }

            renderPhotoSheets();
            triggerAutoSavePhotoReportConfig();
        }

        function getPointImageFit(pointId) {
            pointId = Number(pointId);
            if (photoReportConfig.pointFits && (photoReportConfig.pointFits[pointId] === 'cover' || photoReportConfig.pointFits[pointId] === 'contain')) {
                return photoReportConfig.pointFits[pointId];
            }
            return photoReportConfig.globalFit || 'contain';
        }

        // 5. Selección y Controles Masivos
        function togglePointSelection(pointId, isChecked) {
            pointId = Number(pointId);
            if (isChecked) {
                photoReportConfig.selectedPoints.add(pointId);
            } else {
                photoReportConfig.selectedPoints.delete(pointId);
            }

            const card = document.getElementById('photoPointCard_' + pointId);
            if (card) {
                card.classList.toggle('excluded', !isChecked);
            }

            updateSelectionCounterUI();
            renderPhotoSheets();
            triggerAutoSavePhotoReportConfig();
        }

        function selectAllPoints(select) {
            photoReportConfig.measurements.forEach(m => {
                const id = Number(m.id);
                const chk = document.getElementById('chkPoint_' + id);
                const card = document.getElementById('photoPointCard_' + id);
                if (select) {
                    photoReportConfig.selectedPoints.add(id);
                    if (chk) chk.checked = true;
                    if (card) card.classList.remove('excluded');
                } else {
                    photoReportConfig.selectedPoints.delete(id);
                    if (chk) chk.checked = false;
                    if (card) card.classList.add('excluded');
                }
            });

            updateSelectionCounterUI();
            renderPhotoSheets();
            triggerAutoSavePhotoReportConfig();
        }

        function updateSelectionCounterUI() {
            const count = photoReportConfig.selectedPoints.size;
            const total = photoReportConfig.measurements.length;
            const textEl = document.getElementById('photoSelectionCountText');
            if (textEl) {
                textEl.textContent = `${count} de ${total} seleccionadas`;
            }

            const perPage = getPhotosPerPage(photoReportConfig.grid);
            const totalPages = Math.ceil(count / perPage) || 0;
            const pagesInd = document.getElementById('photoReportPagesIndicator');
            if (pagesInd) {
                pagesInd.textContent = `Hojas Carta: ${totalPages} (${photoReportConfig.grid})`;
            }
        }

        // 6. Navegación Multi-Foto
        function navigatePointPhoto(pointId, delta, event) {
            if (event) {
                event.stopPropagation();
                event.preventDefault();
            }
            pointId = Number(pointId);
            const item = photoReportConfig.measurements.find(m => Number(m.id) === pointId);
            if (!item) return;

            const images = item.images || (item.image_path ? [item.image_path] : []);
            if (images.length <= 1) return;

            const curr = photoReportConfig.photoIndices[pointId] || 0;
            const next = (curr + delta + images.length) % images.length;
            photoReportConfig.photoIndices[pointId] = next;

            const imgEl = document.getElementById('photoImg_' + pointId);
            if (imgEl) {
                imgEl.src = images[next];
            }
            const countEl = document.getElementById('photoCountPill_' + pointId);
            if (countEl) {
                countEl.textContent = `Foto ${next + 1} de ${images.length}`;
            }

            renderPhotoSheets();
            triggerAutoSavePhotoReportConfig();
        }

        // 7. Pestañas de Vista
        function switchPhotoReportTab(tab) {
            photoReportConfig.currentTab = tab;
            const tabBtnInteractive = document.getElementById('tabBtnInteractive');
            const tabBtnSheets = document.getElementById('tabBtnSheets');
            const interactiveView = document.getElementById('photoInteractiveView');
            const sheetsView = document.getElementById('photoSheetsView');

            if (tab === 'sheets') {
                if (tabBtnInteractive) tabBtnInteractive.classList.remove('active');
                if (tabBtnSheets) tabBtnSheets.classList.add('active');
                if (interactiveView) interactiveView.style.display = 'none';
                if (sheetsView) {
                    sheetsView.style.display = 'block';
                    renderPhotoSheets();
                }
            } else {
                if (tabBtnInteractive) tabBtnInteractive.classList.add('active');
                if (tabBtnSheets) tabBtnSheets.classList.remove('active');
                if (interactiveView) interactiveView.style.display = 'block';
                if (sheetsView) sheetsView.style.display = 'none';
            }
        }

        // 8. Renderizado de la Cuadrícula Interactiva
        function renderInteractiveGrid() {
            const container = document.getElementById('photoInteractiveGridContainer');
            if (!container) return;

            const items = photoReportConfig.measurements || [];
            if (items.length === 0) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 50px 20px; color: #94a3b8; background: #ffffff; border-radius: 12px; border: 1.5px dashed #cbd5e1;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 8px;"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        <p style="margin: 0; font-size: 14px; font-weight: 700; color: #334155;">Este módulo no tiene mediciones registradas con evidencia fotográfica.</p>
                        <p style="margin: 6px 0 0 0; font-size: 12px; color: #64748b;">Ingresa al módulo para registrar nuevos puntos o inspecciones fotográficas.</p>
                    </div>
                `;
                return;
            }

            let html = '';
            items.forEach(m => {
                const pointId = Number(m.id);
                const images = m.images && m.images.length > 0 ? m.images : (m.image_path ? [m.image_path] : []);
                const selectedIdx = photoReportConfig.photoIndices[pointId] || 0;
                const currentImg = images[selectedIdx] || images[0] || '';
                const isSelected = photoReportConfig.selectedPoints.has(pointId);
                const hasMultiplePhotos = images.length > 1;
                const pointFit = getPointImageFit(pointId);

                html += `
                    <div class="photo-point-card ${isSelected ? '' : 'excluded'}" id="photoPointCard_${pointId}">
                        <div class="photo-card-select-bar">
                            <label class="photo-select-checkbox-lbl" title="Marcar para incluir en el reporte PDF">
                                <input type="checkbox" id="chkPoint_${pointId}" ${isSelected ? 'checked' : ''} onchange="togglePointSelection(${pointId}, this.checked)">
                                <span>Incluir en PDF</span>
                            </label>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <div class="photo-card-fit-toggle" title="Ajuste de foto para este punto">
                                    <button type="button" class="btn-card-fit ${pointFit === 'contain' ? 'active' : ''}" data-fit="contain" onclick="setPointImageFit(${pointId}, 'contain', event)" title="Contener: Foto completa sin recortar">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect width="18" height="18" x="3" y="3" rx="2" stroke-dasharray="2 2"/><rect width="10" height="10" x="7" y="7" rx="1"/></svg>
                                        <span>Contener</span>
                                    </button>
                                    <button type="button" class="btn-card-fit ${pointFit === 'cover' ? 'active' : ''}" data-fit="cover" onclick="setPointImageFit(${pointId}, 'cover', event)" title="Expandir: Llenar el recuadro">
                                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
                                        <span>Expandir</span>
                                    </button>
                                </div>
                                <span style="font-size: 11px; font-weight: 800; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 4px;">
                                    ${escapeHtml(m.code || ('P-' + m.num))}
                                </span>
                            </div>
                        </div>

                        <div class="photo-card-info-top">
                            <div class="photo-info-row">
                                <span class="photo-info-label">Puesto:</span>
                                <span class="photo-info-value" title="${escapeHtml(m.title)}" style="font-weight: 800; color: #0f172a;">${escapeHtml(m.title)}</span>
                            </div>
                            <div class="photo-info-row">
                                <span class="photo-info-label">Área:</span>
                                <span class="photo-info-value" title="${escapeHtml(m.area)}">${escapeHtml(m.area)}</span>
                            </div>
                            <div class="photo-info-row" style="margin-top: 3px; align-items: center; gap: 6px;">
                                <span style="font-size: 10px; font-weight: 800; color: ${m.badge_color || '#0284c7'}; background: ${m.badge_bg || '#e0f2fe'}; padding: 2px 7px; border-radius: 4px;">
                                    ${escapeHtml(m.badge_label || 'Evaluado')}
                                </span>
                            </div>
                            ${m.details ? `
                                <div class="photo-info-row" style="margin-top: 2px;">
                                    <span class="photo-info-label">Detalles:</span>
                                    <span class="photo-info-value" style="font-size: 10.5px; color: #475569;" title="${escapeHtml(m.details)}">${escapeHtml(m.details)}</span>
                                </div>
                            ` : ''}
                        </div>

                        <div class="photo-card-img-box fit-${pointFit}" id="photoCardImgBox_${pointId}">
                            ${currentImg ? `
                                <img id="photoImg_${pointId}" src="${currentImg}" alt="${m.code}" onclick="openPhotoViewer('${currentImg}', '${escapeJs(m.code)}: ${escapeJs(m.title)}')" title="Clic para ampliar fotografía">
                                <button type="button" class="photo-zoom-btn" onclick="openPhotoViewer('${currentImg}', '${escapeJs(m.code)}: ${escapeJs(m.title)}')" title="Ampliar">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                </button>
                            ` : `
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #64748b;">
                                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                                    <span style="font-size: 11px; margin-top: 4px; font-weight: 600;">Sin fotografía</span>
                                </div>
                            `}

                            ${hasMultiplePhotos ? `
                                <button type="button" class="photo-nav-arrow left" onclick="navigatePointPhoto(${pointId}, -1, event)" title="Foto anterior">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                                <button type="button" class="photo-nav-arrow right" onclick="navigatePointPhoto(${pointId}, 1, event)" title="Siguiente foto">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                                <div class="photo-count-pill" id="photoCountPill_${pointId}">
                                    Foto ${selectedIdx + 1} de ${images.length}
                                </div>
                            ` : (images.length === 1 ? `
                                <div class="photo-count-pill">Foto 1 de 1</div>
                            ` : '')}
                        </div>

                        <div class="photo-card-info-bottom">
                            <span class="photo-dt-label">
                                <svg class="photo-dt-icon" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                                </svg>
                                ${escapeHtml(m.date || '—')} ${escapeHtml(m.time && m.time !== '—' ? m.time : '')}
                            </span>
                            <span class="photo-dt-pill" title="${escapeHtml(m.registered_by || '')}">Ev: ${escapeHtml(m.registered_by || 'Técnico')}</span>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // 9. Renderizado de las Hojas Tamaño Carta
        function renderPhotoSheets() {
            const sheetsContainer = document.getElementById('photoSheetsContainer');
            const printContainer = document.getElementById('photoReportPrintArea');

            const allItems = photoReportConfig.measurements || [];
            const info = photoReportConfig.moduleInfo || {};
            const selectedItems = allItems.filter(m => photoReportConfig.selectedPoints.has(Number(m.id)));
            const perPage = getPhotosPerPage(photoReportConfig.grid);

            if (selectedItems.length === 0) {
                const emptyHtml = `
                    <div style="text-align: center; padding: 50px 20px; color: #94a3b8; background: #ffffff; border-radius: 10px; border: 1.5px dashed #cbd5e1; max-width: 600px; margin: 30px auto;">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 8px;"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        <h3 style="font-size: 15px; margin: 0 0 6px 0; color: #334155;">No hay fotografías seleccionadas para el reporte Carta</h3>
                        <p style="margin: 0; font-size: 12.5px;">Activa las casillas "Incluir en PDF" o pulsa en "Todas" para armar las páginas.</p>
                    </div>
                `;
                if (sheetsContainer) sheetsContainer.innerHTML = emptyHtml;
                if (printContainer) printContainer.innerHTML = '';
                return;
            }

            const pages = [];
            for (let i = 0; i < selectedItems.length; i += perPage) {
                pages.push(selectedItems.slice(i, i + perPage));
            }

            const totalPages = pages.length;
            const now = new Date();
            const dateEmissionFormatted = now.toLocaleDateString('es-BO', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + now.toLocaleTimeString('es-BO', { hour: '2-digit', minute: '2-digit' });

            let sheetsHtml = '';

            pages.forEach((pageItems, pageIdx) => {
                const pageNum = pageIdx + 1;

                sheetsHtml += `
                    <div class="photo-report-sheet">
                        <!-- Encabezado Ejecutivo Carta -->
                        <div class="sheet-header-box">
                            <div class="sheet-header-left">
                                <div class="sheet-header-logo-badge">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="20" height="14" x="2" y="3" rx="2"/>
                                        <line x1="8" y1="21" x2="16" y2="21"/>
                                        <line x1="12" y1="17" x2="12" y2="21"/>
                                    </svg>
                                </div>
                                <div class="sheet-header-titles">
                                    <div class="sheet-header-brand-row">
                                        <span class="sheet-company-title">PACHABOL S.R.L.</span>
                                        <span class="sheet-brand-dot">•</span>
                                        <span class="sheet-system-tag">Seguridad, Salud &amp; Medio Ambiente</span>
                                    </div>
                                    <h1>REPORTE FOTOGRÁFICO — ${escapeHtml((info.name || 'MONITOREO AMBIENTAL').toUpperCase())}</h1>
                                    <div class="sheet-header-subtitle-row">
                                        <span class="sheet-tech-tag">${escapeHtml(info.normative || 'METODOLOGÍA NORMATIVA VIGENTE')}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="sheet-header-meta-card">
                                <div class="sheet-meta-card-header">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    <span>FICHA TÉCNICA DE EVALUACIÓN</span>
                                </div>
                                <div class="sheet-meta-grid">
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">INSTALACIÓN:</span>
                                        <span class="sheet-meta-val" title="${escapeHtml(info.installation_name || '—')}">${escapeHtml(info.installation_name || '—')}</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">EVALUADOR:</span>
                                        <span class="sheet-meta-val" title="${escapeHtml(info.registered_by || '—')}">${escapeHtml(info.registered_by || '—')}</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">PERÍODO:</span>
                                        <span class="sheet-meta-val">${escapeHtml(info.start_date || '—')}${info.end_date && info.end_date !== info.start_date ? ' al ' + escapeHtml(info.end_date) : ''}</span>
                                    </div>
                                    <div class="sheet-meta-row">
                                        <span class="sheet-meta-lbl">INSTRUMENTO:</span>
                                        <span class="sheet-meta-val" style="color: #38bdf8; font-weight: 800;" title="${escapeHtml(info.equipment_name || 'Certificado')}">${escapeHtml(info.equipment_name || 'Certificado')}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Grilla Dinámica de Fotos -->
                        <div class="sheet-photos-grid dist-${photoReportConfig.grid}">
                            ${pageItems.map(m => {
                                const pointId = Number(m.id);
                                const images = m.images && m.images.length > 0 ? m.images : (m.image_path ? [m.image_path] : []);
                                const selectedIdx = photoReportConfig.photoIndices[pointId] || 0;
                                const currentImg = images[selectedIdx] || images[0] || '';
                                const pointFit = getPointImageFit(pointId);

                                return `
                                    <div class="sheet-card-item">
                                        <div class="photo-card-info-top">
                                            <div class="photo-info-row" style="justify-content: space-between;">
                                                <span class="photo-code">${escapeHtml(m.code || ('P-' + m.num))}</span>
                                                <span style="font-weight: 800; font-size: 8px; color: ${m.badge_color || '#0284c7'};">${escapeHtml(m.badge_label || '')}</span>
                                            </div>
                                            <div class="photo-info-row" style="margin-top: 1px;">
                                                <span class="photo-info-label">PUESTO:</span>
                                                <span class="photo-info-value" style="font-weight: 800;" title="${escapeHtml(m.title)}">${escapeHtml(m.title)}</span>
                                            </div>
                                            <div class="photo-info-row">
                                                <span class="photo-info-label">ÁREA:</span>
                                                <span class="photo-info-value" title="${escapeHtml(m.area)}">${escapeHtml(m.area)}</span>
                                            </div>
                                            ${m.details ? `
                                                <div class="photo-info-row">
                                                    <span class="photo-info-label">DETALLE:</span>
                                                    <span class="photo-info-value" style="font-size: 7.5px; color: #64748b;" title="${escapeHtml(m.details)}">${escapeHtml(m.details)}</span>
                                                </div>
                                            ` : ''}
                                        </div>

                                        <div class="photo-card-img-box fit-${pointFit}">
                                            ${currentImg ? `
                                                <img src="${currentImg}" alt="${m.code}" crossorigin="anonymous">
                                            ` : `
                                                <div style="color: #64748b; font-size: 8.5px; font-weight: 700; text-align: center; padding: 10px;">
                                                    Sin fotografía
                                                </div>
                                            `}
                                        </div>

                                        <div class="photo-card-info-bottom">
                                            <span class="photo-dt-label">
                                                <svg class="photo-dt-icon" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                                ${escapeHtml(m.date || '—')} ${escapeHtml(m.time && m.time !== '—' ? m.time : '')}
                                            </span>
                                            <span class="photo-dt-pill">${escapeHtml(m.registered_by || 'Técnico')}</span>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>

                        <!-- Pie de Página Institucional Carta -->
                        <div class="sheet-footer-box">
                            <div class="sheet-footer-brand">
                                <span>PACHABOL S.R.L.</span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>SISTEMA METRIC</span>
                                <span style="color: #cbd5e1;">|</span>
                                <span>HOJA TAMAÑO CARTA</span>
                            </div>
                            <div>
                                <span>Emisión: ${dateEmissionFormatted}</span>
                            </div>
                            <div>
                                <span class="sheet-footer-page-num">PÁGINA ${pageNum} DE ${totalPages}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            if (sheetsContainer) sheetsContainer.innerHTML = sheetsHtml;
            if (printContainer) printContainer.innerHTML = sheetsHtml;
        }

        // 10. Persistencia en Servidor y LocalStorage
        function triggerAutoSavePhotoReportConfig() {
            clearTimeout(photoReportAutoSaveTimeout);
            photoReportAutoSaveTimeout = setTimeout(() => {
                savePhotoReportSettingsToServer(false);
            }, 1500);
        }

        async function savePhotoReportSettingsToServer(showToast = false) {
            const btnSave = document.getElementById('btnSaveReportSettings');
            if (btnSave && showToast) {
                btnSave.disabled = true;
                btnSave.innerHTML = `
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="animation: spin 0.8s linear infinite;">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                    </svg>
                    <span>Guardando...</span>
                `;
            }

            const payload = {
                grid: photoReportConfig.grid,
                selected_points: Array.from(photoReportConfig.selectedPoints),
                photo_indices: photoReportConfig.photoIndices,
                image_fit: photoReportConfig.globalFit,
                photo_fits: photoReportConfig.pointFits
            };

            try {
                localStorage.setItem('metric_hub_photo_' + (photoReportConfig.moduleId || '0'), JSON.stringify(payload));
            } catch (e) {
                console.warn('Error en localStorage', e);
            }

            try {
                const res = await fetch(`/reporte-fotografico/modulo/${photoReportConfig.moduleId}/settings`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (showToast && typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Configuración Guardada!',
                        text: `Distribución (${photoReportConfig.grid}), selección de fotos y encuadre almacenados con éxito en el módulo.`,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        timer: 2500,
                        timerProgressBar: true,
                        buttonsStyling: false,
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                    });
                }
            } catch (err) {
                console.error('Error guardando configuración:', err);
                if (showToast && typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Guardado Local',
                        text: 'La configuración se conservó en tu navegador para continuar con la exportación.',
                        icon: 'info',
                        confirmButtonText: 'Aceptar',
                        timer: 2500,
                        buttonsStyling: false,
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                    });
                }
            } finally {
                if (btnSave && showToast) {
                    btnSave.disabled = false;
                    btnSave.innerHTML = `
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        <span>Guardar Selección</span>
                    `;
                }
            }
        }

        // 11. Normalización Matemática Óptica para Generación de Canvas / PDF
        function fixSheetImagesForCanvas(sheet) {
            const revertList = [];
            const boxes = sheet.querySelectorAll('.sheet-card-item .photo-card-img-box');

            boxes.forEach(box => {
                const img = box.querySelector('img');
                if (!img) return;

                const nw = img.naturalWidth;
                const nh = img.naturalHeight;
                if (!nw || !nh) return;

                const isCover = box.classList.contains('fit-cover');
                const boxRect = box.getBoundingClientRect();
                const boxWidth = boxRect.width || box.clientWidth;
                const boxHeight = boxRect.height || box.clientHeight;
                if (boxWidth <= 0 || boxHeight <= 0) return;

                const imgRatio = nw / nh;
                const boxRatio = boxWidth / boxHeight;

                let scaledWidth = boxWidth;
                let scaledHeight = boxHeight;
                let offsetX = 0;
                let offsetY = 0;

                if (isCover) {
                    if (imgRatio > boxRatio) {
                        scaledHeight = boxHeight;
                        scaledWidth = boxHeight * imgRatio;
                        offsetX = (boxWidth - scaledWidth) / 2;
                        offsetY = 0;
                    } else {
                        scaledWidth = boxWidth;
                        scaledHeight = boxWidth / imgRatio;
                        offsetX = 0;
                        offsetY = (boxHeight - scaledHeight) / 2;
                    }
                } else {
                    if (imgRatio > boxRatio) {
                        scaledWidth = boxWidth;
                        scaledHeight = boxWidth / imgRatio;
                        offsetX = 0;
                        offsetY = (boxHeight - scaledHeight) / 2;
                    } else {
                        scaledHeight = boxHeight;
                        scaledWidth = boxHeight * imgRatio;
                        offsetX = (boxWidth - scaledWidth) / 2;
                        offsetY = 0;
                    }
                }

                const prevBoxStyle = box.getAttribute('style') || '';
                const prevImgStyle = img.getAttribute('style') || '';

                box.style.setProperty('position', 'relative', 'important');
                box.style.setProperty('overflow', 'hidden', 'important');

                img.style.setProperty('position', 'absolute', 'important');
                img.style.setProperty('left', `${offsetX}px`, 'important');
                img.style.setProperty('top', `${offsetY}px`, 'important');
                img.style.setProperty('width', `${scaledWidth}px`, 'important');
                img.style.setProperty('height', `${scaledHeight}px`, 'important');
                img.style.setProperty('max-width', 'none', 'important');
                img.style.setProperty('max-height', 'none', 'important');
                img.style.setProperty('min-width', '0', 'important');
                img.style.setProperty('min-height', '0', 'important');
                img.style.setProperty('object-fit', 'fill', 'important');
                img.style.setProperty('margin', '0', 'important');
                img.style.setProperty('display', 'block', 'important');

                revertList.push(() => {
                    if (prevBoxStyle) box.setAttribute('style', prevBoxStyle);
                    else box.removeAttribute('style');
                    if (prevImgStyle) img.setAttribute('style', prevImgStyle);
                    else img.removeAttribute('style');
                });
            });

            return function restoreAll() {
                revertList.forEach(fn => fn());
            };
        }

        // 12. Generación y Descarga Directa en Formato Carta PDF
        async function downloadPhotoReportPDF() {
            if (!photoReportConfig.selectedPoints || photoReportConfig.selectedPoints.size === 0) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Sin selección',
                        text: 'Debes seleccionar al menos una fotografía para generar y descargar el reporte en PDF.',
                        icon: 'warning',
                        confirmButtonText: 'Entendido',
                        buttonsStyling: false,
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                    });
                } else {
                    alert('Debes seleccionar al menos una fotografía para descargar el PDF.');
                }
                return;
            }

            const btnDownload = document.getElementById('btnDownloadPhotoReportPdf');
            const origBtnHtml = btnDownload ? btnDownload.innerHTML : '';

            try {
                if (btnDownload) {
                    btnDownload.disabled = true;
                    btnDownload.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="animation: spin 0.8s linear infinite;">
                            <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                            <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                        </svg>
                        <span>Generando PDF...</span>
                    `;
                }

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Generando Reporte PDF',
                        html: `
                            <div style="display:flex;flex-direction:column;align-items:center;gap:10px;padding:12px 0;">
                                <span style="font-size:13px;color:#475569;">Compilando hojas tamaño Carta en alta resolución...</span>
                                <span id="pdfGenProgressText" style="font-size:11.5px;font-weight:700;color:#0284c7;">Preparando páginas...</span>
                            </div>
                        `,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        customClass: { popup: 'metric-swal-popup' }
                    });
                }

                switchPhotoReportTab('sheets');
                renderPhotoSheets();
                await new Promise(r => setTimeout(r, 150));

                const sheetsContainer = document.getElementById('photoSheetsContainer');
                if (!sheetsContainer) {
                    throw new Error('No se encontró el contenedor de hojas Carta.');
                }

                const sheets = Array.from(sheetsContainer.querySelectorAll('.photo-report-sheet'));
                if (sheets.length === 0) {
                    throw new Error('No hay hojas generadas para exportar en el reporte.');
                }

                const JsPDF = (window.jspdf && window.jspdf.jsPDF) ? window.jspdf.jsPDF : window.jsPDF;
                if (!JsPDF) {
                    throw new Error('La librería jsPDF no está disponible en la página.');
                }
                if (typeof html2canvas === 'undefined') {
                    throw new Error('La librería html2canvas no está disponible en la página.');
                }

                const pdf = new JsPDF({
                    orientation: 'portrait',
                    unit: 'mm',
                    format: 'letter',
                    compress: true
                });

                const letterWidthMm = 215.9;
                const letterHeightMm = 279.4;

                for (let i = 0; i < sheets.length; i++) {
                    const sheet = sheets[i];
                    const progressEl = document.getElementById('pdfGenProgressText');
                    if (progressEl) {
                        progressEl.textContent = `Procesando hoja ${i + 1} de ${sheets.length}...`;
                    }

                    // Asegurar carga de todas las imágenes
                    const sheetImages = Array.from(sheet.querySelectorAll('img'));
                    await Promise.all(sheetImages.map(img => {
                        if (img.complete) return Promise.resolve();
                        return new Promise(resolve => {
                            img.onload = resolve;
                            img.onerror = resolve;
                        });
                    }));

                    const prevBoxShadow = sheet.style.boxShadow;
                    const prevBorderRadius = sheet.style.borderRadius;
                    const prevBorder = sheet.style.border;
                    const prevMargin = sheet.style.margin;

                    sheet.style.boxShadow = 'none';
                    sheet.style.borderRadius = '0';
                    sheet.style.border = 'none';
                    sheet.style.margin = '0';

                    const restoreImages = fixSheetImagesForCanvas(sheet);
                    let canvas;
                    try {
                        canvas = await html2canvas(sheet, {
                            scale: 2,
                            useCORS: true,
                            logging: false,
                            backgroundColor: '#ffffff'
                        });
                    } finally {
                        restoreImages();
                    }

                    sheet.style.boxShadow = prevBoxShadow;
                    sheet.style.borderRadius = prevBorderRadius;
                    sheet.style.border = prevBorder;
                    sheet.style.margin = prevMargin;

                    const imgData = canvas.toDataURL('image/jpeg', 0.95);

                    if (i > 0) {
                        pdf.addPage('letter', 'portrait');
                    }

                    pdf.addImage(imgData, 'JPEG', 0, 0, letterWidthMm, letterHeightMm, undefined, 'FAST');
                }

                const modInfo = photoReportConfig.moduleInfo || {};
                const modClean = (modInfo.name || 'Monitoreo').trim().replace(/[^a-zA-Z0-9_\-]/g, '_').substring(0, 30);
                const instClean = (modInfo.installation_name || 'Instalacion').trim().replace(/[^a-zA-Z0-9_\-]/g, '_').substring(0, 30);
                const dateStr = new Date().toISOString().slice(0, 10);
                const fileName = `Reporte_Fotografico_${modClean}_${instClean}_${dateStr}.pdf`;

                pdf.save(fileName);

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: '¡Descarga Exitosa!',
                        text: `El reporte fotográfico (${sheets.length} ${sheets.length === 1 ? 'hoja carta' : 'hojas carta'}) se ha descargado exitosamente en PDF.`,
                        icon: 'success',
                        confirmButtonText: 'Aceptar',
                        timer: 3000,
                        timerProgressBar: true,
                        buttonsStyling: false,
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                    });
                }
            } catch (err) {
                console.error('Error generando PDF:', err);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Error al exportar PDF',
                        text: 'Ocurrió un error al generar el archivo PDF: ' + (err.message || 'Error desconocido'),
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        buttonsStyling: false,
                        customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-danger' }
                    });
                } else {
                    alert('Error generando PDF: ' + err.message);
                }
            } finally {
                if (btnDownload && origBtnHtml) {
                    btnDownload.disabled = false;
                    btnDownload.innerHTML = origBtnHtml;
                }
            }
        }

        // 13. Lightbox de Fotografía
        function openPhotoViewer(url, caption) {
            const m = document.getElementById('photoViewerModal');
            const img = document.getElementById('photoViewerImg');
            const cap = document.getElementById('photoViewerTitle');
            if (m && img) {
                img.src = url;
                if (cap) cap.textContent = caption || 'Evidencia Fotográfica';
                m.classList.add('open');
            }
        }

        function closePhotoViewer() {
            const m = document.getElementById('photoViewerModal');
            if (m) m.classList.remove('open');
        }

        function renderEmptyState(msg) {
            const container = document.getElementById('photoInteractiveGridContainer');
            if (container) {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #94a3b8; background: #ffffff; border-radius: 12px; border: 1.5px dashed #cbd5e1;">
                        <svg width="45" height="45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="margin-bottom: 10px;"><rect width="18" height="18" x="3" y="3" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/></svg>
                        <h3 style="font-size: 15px; margin: 0 0 6px 0; color: #334155;">${escapeHtml(msg)}</h3>
                        <p style="margin: 0; font-size: 12.5px; color: #64748b;">Selecciona un módulo en la parte superior para visualizar y exportar las evidencias en formato mosaico Carta.</p>
                    </div>
                `;
            }
            const sheetsContainer = document.getElementById('photoSheetsContainer');
            if (sheetsContainer) sheetsContainer.innerHTML = '';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function escapeJs(str) {
            if (!str) return '';
            return String(str).replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
        }
    </script>
@endpush
