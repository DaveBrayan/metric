@extends('layouts.app')

@section('title', 'Radar en Tiempo Real — ' . $project->name . ' | Metric v2')

@push('styles')
    <!-- Leaflet CSS for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="stylesheet" href="{{ asset('css/live_monitoring.css') }}">
@endpush

@section('content')
<div class="live-monitoring-wrapper">
    <!-- 1. Header & Live Status Card (Limpio y Elegante) -->
    <header class="live-header-card">
        <div class="live-header-left">
            <a href="{{ route('projects.index') }}" class="btn-back-round" title="Volver a Proyectos">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
            </a>
            <div class="live-project-info">
                <h1>
                    <span>{{ $project->name }}</span>
                    @if(!empty($project->code))
                        <span class="live-project-code-badge">{{ $project->code }}</span>
                    @endif
                </h1>
                <div class="live-project-sub">
                    <span><strong>Empresa:</strong> {{ $project->company ? $project->company->name : 'General' }}</span>
                    <span class="sub-separator">•</span>
                    <span><strong>Responsable:</strong> {{ $project->manager ? $project->manager->name : 'No Asignado' }}</span>
                    <span class="sub-separator">•</span>
                    <div class="live-status-pill" id="livePulseBadge">
                        <span class="live-pulse-dot"></span>
                        <span>RADAR EN VIVO (3s)</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="live-header-right">
            <!-- KPI 1: Puntos Georreferenciados -->
            <div class="live-kpi-chip">
                <span class="live-kpi-chip-val" id="kpiTotalGeoPoints">0</span>
                <span class="live-kpi-chip-label">Puntos GPS</span>
            </div>

            <!-- KPI 2: Módulos Activos -->
            <div class="live-kpi-chip">
                <span class="live-kpi-chip-val" id="kpiActiveModulesCount">{{ count($activeModulesList) }}</span>
                <span class="live-kpi-chip-label">Módulos</span>
            </div>

            <!-- KPI 3: Último Registro -->
            <div class="live-kpi-chip">
                <span class="live-kpi-chip-val" id="kpiLastSyncTime" style="font-size: 13px;">Reciente</span>
                <span class="live-kpi-chip-label">Último Dato</span>
            </div>

            <div class="header-action-divider"></div>

            <!-- Botones de Control de Mapa -->
            <button type="button" class="btn-live-action" id="btnFitBounds" title="Ajustar y centrar todos los puntos en el mapa">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 3 21 3 21 9"></polyline>
                    <polyline points="9 21 3 21 3 15"></polyline>
                    <line x1="21" y1="3" x2="14" y2="10"></line>
                    <line x1="3" y1="21" x2="10" y2="14"></line>
                </svg>
                <span>Centrar</span>
            </button>

            <button type="button" class="btn-live-action" id="btnToggleFullscreen" title="Pantalla Completa">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                </svg>
                <span>Pantalla Completa</span>
            </button>

            <button type="button" class="btn-live-action" id="btnTogglePolling" title="Pausar o Reanudar el Radar">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="6" y="4" width="4" height="16"></rect>
                    <rect x="14" y="4" width="4" height="16"></rect>
                </svg>
                <span>Pausar Radar</span>
            </button>
        </div>
    </header>

    <!-- 2. Main Split (Streaming Sidebar + Interactive Map) -->
    <div class="live-main-split">
        <!-- Sidebar Dedicado al Streaming en Tiempo Real con Buscador y Filtros Completos -->
        <aside class="live-sidebar" id="liveSidebar">
            <div class="live-sidebar-header">
                <div class="live-sidebar-title-group">
                    <h2>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3">
                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                        </svg>
                        <span>Actividad Reciente</span>
                    </h2>
                    <div class="streaming-indicator-badge">
                        <span class="live-pulse-dot-sm"></span>
                        <span>EN VIVO</span>
                    </div>
                </div>

                <!-- Botón de cerrar drawer (visible en modo pantalla completa) -->
                <button type="button" class="btn-close-sidebar-drawer" id="btnCloseSidebarDrawer" title="Cerrar panel flotante">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Barra de Búsqueda y Botón de Filtros Integrados -->
            <div class="sidebar-search-and-filters">
                <div class="sidebar-search-row">
                    <div class="sidebar-search-box">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.3">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <input type="text" id="sidebarSearchInput" placeholder="Buscar área, puesto, técnico..." autocomplete="off" />
                        <button type="button" id="btnClearSearch" class="btn-clear-search" style="display: none;" title="Limpiar búsqueda">✕</button>
                    </div>

                    <!-- Botón de Filtros al lado del buscador -->
                    <button type="button" class="btn-sidebar-filter-toggle" id="btnToggleSidebarFilter" title="Filtrar por módulos, rango de fechas y personal">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filtros</span>
                        <span class="filter-count-badge" id="filterActiveBadge">{{ count($activeModulesList) }}</span>
                    </button>
                </div>

                <!-- Panel Desplegable de Filtros dentro del Sidebar -->
                <div class="sidebar-filter-dropdown-panel" id="sidebarFilterPanel" style="display: none;">
                    <!-- Sección 1: Módulos del Proyecto -->
                    <div class="filter-section">
                        <div class="filter-section-header">
                            <span class="filter-section-title">Módulos del Proyecto</span>
                            <div class="filter-quick-links">
                                <button type="button" class="btn-filter-quick" id="btnSelectAllModules">Todos</button>
                                <span class="quick-sep">|</span>
                                <button type="button" class="btn-filter-quick" id="btnSelectNoneModules">Ninguno</button>
                            </div>
                        </div>
                        <div class="module-filter-grid">
                            @forelse($activeModulesList as $mod)
                                <label class="module-filter-pill active" style="--pill-color: {{ $mod['color'] }};">
                                    <input type="checkbox" class="module-filter-checkbox" value="{{ $mod['key'] }}" checked />
                                    <span class="module-dot" style="background: {{ $mod['color'] }};"></span>
                                    <span class="module-pill-text">{{ $mod['name'] }}</span>
                                    <span class="module-pill-count">{{ $mod['points_count'] }}</span>
                                </label>
                            @empty
                                <div class="empty-filter-notice">No hay módulos creados para este proyecto.</div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Sección 2: Rango de Fechas (Desde - Hasta) -->
                    <div class="filter-section">
                        <div class="filter-section-header">
                            <span class="filter-section-title">Rango de Fechas</span>
                            <button type="button" class="btn-filter-quick" id="btnClearDateRange">Limpiar fechas</button>
                        </div>
                        <div class="date-range-inputs-grid">
                            <div class="date-field-wrap">
                                <label for="filterDateStart" class="date-field-label">Desde:</label>
                                <input type="date" id="filterDateStart" class="filter-date-input" />
                            </div>
                            <div class="date-field-wrap">
                                <label for="filterDateEnd" class="date-field-label">Hasta:</label>
                                <input type="date" id="filterDateEnd" class="filter-date-input" />
                            </div>
                        </div>
                    </div>

                    <!-- Sección 3: Personal / Técnico -->
                    @if(count($uniqueStaff) > 0)
                        <div class="filter-section">
                            <span class="filter-section-title">Personal / Técnico</span>
                            <div class="select-with-icon">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <select class="staff-filter-select" id="staffFilterSelect">
                                    <option value="all">Todos los colaboradores ({{ count($uniqueStaff) }})</option>
                                    @foreach($uniqueStaff as $sId => $sName)
                                        <option value="{{ $sName }}">{{ $sName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Footer de Filtros -->
                    <div class="sidebar-filter-footer">
                        <button type="button" class="btn-reset-all-filters" id="btnResetAllFilters">Restablecer Todo</button>
                        <button type="button" class="btn-apply-sidebar-filters" id="btnApplySidebarFilters">Cerrar Filtros</button>
                    </div>
                </div>
            </div>

            <div class="live-sidebar-feed-wrap">
                <div class="live-feed-list" id="liveActivityFeed">
                    <div class="live-feed-loading">
                        <div class="loading-spinner-sm"></div>
                        <span>Cargando datos satelitales en tiempo real...</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Central Map -->
        <main class="live-map-wrapper">
            <!-- Floating Bubble Toggle (Para abrir Actividad Reciente en Pantalla Completa) -->
            <button type="button" class="btn-floating-feed-toggle" id="btnFloatingFeedToggle" title="Abrir panel de Actividad Reciente">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                </svg>
                <span>Actividad Reciente</span>
                <span class="live-pulse-dot-sm"></span>
            </button>

            <!-- Floating Layer Control: Solo Calles y Satélite -->
            <div class="map-floating-overlay-top">
                <span class="layer-control-label">Capa:</span>
                <button type="button" class="map-layer-btn active" data-layer="osm" title="Mapa de Calles y Rutas (OpenStreetMap)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"></polygon>
                        <line x1="8" y1="2" x2="8" y2="18"></line>
                        <line x1="16" y1="6" x2="16" y2="22"></line>
                    </svg>
                    <span>Calles</span>
                </button>
                <button type="button" class="map-layer-btn" data-layer="satellite" title="Fotografía Satelital en Alta Resolución (Esri World Imagery)">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="2" y1="12" x2="22" y2="12"></line>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                    </svg>
                    <span>Satélite</span>
                </button>
            </div>

            <!-- Floating Isolation Banner (Aparece al activar "Mostrar solo este") -->
            <div class="map-isolation-banner" id="mapIsolationBanner" style="display: none;">
                <div class="isolation-banner-left">
                    <span class="isolation-pulse-dot"></span>
                    <span class="isolation-banner-text" id="isolationBannerText">Mostrando únicamente este punto</span>
                </div>
                <button type="button" class="btn-clear-isolation" onclick="LiveRadar.clearIsolation()" title="Restablecer y mostrar todos los puntos en el mapa">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    <span>Ver todos los puntos</span>
                </button>
            </div>

            <!-- Leaflet Map Container -->
            <div id="liveMonitoringMap"></div>
        </main>
    </div>

    <!-- 3. Floating Real-Time Radar Toast Container (Top-Right) -->
    <div class="live-toast-stack" id="liveToastStack"></div>

    <!-- 4. Modal Visor de Fotografías en Alta Definición con Galería y Flechas -->
    <div class="photo-lightbox-modal" id="livePhotoModal" style="display: none;">
        <div class="photo-lightbox-backdrop" id="photoModalBackdrop"></div>
        <div class="photo-lightbox-card">
            <!-- Header del Visor -->
            <div class="photo-lightbox-header">
                <div class="photo-lightbox-meta">
                    <span class="photo-point-badge" id="photoPointBadge">Módulo #01</span>
                    <span class="photo-counter-pill" id="photoCounterText">Foto 1 de 1</span>
                </div>
                <div class="photo-lightbox-actions">
                    <button type="button" class="btn-lightbox-action" id="btnOpenPhotoNewTab" title="Abrir imagen original en nueva pestaña">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                            <polyline points="15 3 21 3 21 9"></polyline>
                            <line x1="10" y1="14" x2="21" y2="3"></line>
                        </svg>
                    </button>
                    <button type="button" class="btn-close-photo-modal" id="btnClosePhotoModal" title="Cerrar visor (Esc)">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Escenario de Imagen con Flechas de Navegación -->
            <div class="photo-lightbox-stage">
                <button type="button" class="photo-nav-btn prev" id="btnPhotoPrev" title="Foto anterior (←)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                </button>

                <div class="photo-stage-img-wrap">
                    <img src="" id="photoMainImg" alt="Fotografía en alta resolución" class="photo-main-img" />
                </div>

                <button type="button" class="photo-nav-btn next" id="btnPhotoNext" title="Siguiente foto (→)">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>

            <!-- Tira de Miniaturas Inferior -->
            <div class="photo-lightbox-thumbnails" id="photoThumbsStrip"></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <!-- Leaflet JS for Maps -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script src="{{ asset('js/live_monitoring.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            LiveRadar.init({
                projectId: {{ $project->id }},
                dataUrl: '{{ route("projects.live_monitoring.data", $project->id) }}',
                initialModuleKeys: @json(array_column($activeModulesList, 'key'))
            });
        });
    </script>
@endpush


