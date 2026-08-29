@extends('layouts.app')

@section('title', 'Centro de Mando & Monitoreo Ambiental — Metric v2 Pachabol')

@push('styles')
    @vite(['resources/css/dashboard.css'])
@endpush

@section('content')
    <!-- 1. Executive Health Hero & Global Command Bar -->
    <div class="executive-hero-banner wow-entrance stagger-1">
        <div class="executive-hero-left">
            <div class="executive-health-kicker">
                <span class="health-dot-pulse"></span>
                <span id="heroHealthBadge">ESTADO AMBIENTAL ÓPTIMO: 98.6% CONFORMIDAD LMP (LEY 1333)</span>
            </div>
            <h1 id="heroMainTitle">
                <span>Centro de Mando & Telemetría Ambiental</span>
            </h1>
            <p id="heroSubDesc">
                Supervisión centralizada de límites máximos permisibles (LMP), estaciones de muestreo y calibración de equipos patrón.
            </p>
        </div>

        <div class="executive-hero-right">
            <!-- 🏢 Company Selector Filter -->
            <div class="dashboard-filter-select-wrap" title="Filtrar por empresa cliente">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="filter-select-icon">
                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                </svg>
                <select id="dashboardCompanyFilterSelect" class="dashboard-filter-select" onchange="handleCompanyFilterChange(this.value)">
                    @foreach($companiesList as $comp)
                        <option value="{{ $comp['id'] }}">{{ $comp['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <!-- 📅 Date Range Capsule -->
            <button type="button" class="date-capsule" onclick="handleDateRangePicker()" title="Cambiar ciclo o período">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span id="currentPeriodLabel">Mayo 2024 (Ciclo Activo)</span>
            </button>

            <!-- 📑 Export Executive Report CTA -->
            <button type="button" class="btn-primary-hero-action" onclick="dispatchQuickAction('Descarga de Reporte Ejecutivo Multi-Empresa')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Exportar Informe</span>
            </button>
        </div>
    </div>

    <!-- 2. 5 Industrial Measurement Modules Strip Grid (Physical Readings & Compliance) -->
    <div class="env-modules-strip-grid">
        @foreach($modulesTelemetry as $index => $mod)
            <div class="glass-card env-module-card theme-{{ $mod['theme'] }} wow-entrance stagger-{{ $index + 1 }}">
                <div class="env-module-head">
                    <div class="env-module-title">{{ $mod['name'] }}</div>
                    <div class="env-module-icon-wrap" aria-hidden="true">
                        @if($mod['key'] === 'dosimetria')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6 8.5a6.5 6.5 0 1 1 13 0c0 6-6 6-6 10a3.5 3.5 0 1 1-7 0"/>
                                <path d="M15 8.5a2.5 2.5 0 0 0-5 0v2"/>
                            </svg>
                        @elseif($mod['key'] === 'ruido_ambiental')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                                <path d="M15.54 8.46a5 5 0 0 1 0 7.07"/>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14"/>
                            </svg>
                        @elseif($mod['key'] === 'agua')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                            </svg>
                        @elseif($mod['key'] === 'opacidad')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                            </svg>
                        @elseif($mod['key'] === 'particulas')
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17.7 7.7a2.5 2.5 0 1 1 1.8 4.3H2"/>
                                <path d="M9.6 4.6A2 2 0 1 1 11 8H2"/>
                                <path d="M12.6 19.4A2 2 0 1 0 14 16H2"/>
                            </svg>
                        @endif
                    </div>
                </div>

                <div class="env-metric-display">
                    <div class="env-metric-val" id="modVal-{{ $mod['key'] }}">{{ $mod['metric_value'] }}</div>
                    <div class="env-metric-unit">{{ $mod['metric_unit'] }}</div>
                </div>

                <div class="env-module-footer">
                    <div class="env-limit-tag">
                        <span>{{ $mod['limit_text'] }}</span>
                        <span class="status-pill-badge {{ $mod['status_theme'] }}" style="padding: 2px 8px; font-size: 10px;">{{ $mod['status'] }}</span>
                    </div>
                    <div class="env-equipment-tag" title="{{ $mod['equipment'] }}">
                        ⚙ {{ $mod['equipment'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- 3. Dual Mid-Grid: Multi-Dimensional Spline Telemetry + Certified Equipment Inventory -->
    <div class="executive-mid-grid">
        <!-- 3.1. Telemetry Spline Chart -->
        <div class="glass-card panel-box wow-entrance stagger-4">
            <div class="panel-title-bar">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                        <polyline points="16 7 22 7 22 13"/>
                    </svg>
                    <span>Curva Multidimensional de Telemetría Ambiental</span>
                </h3>
                <div class="chart-time-pills-group">
                    <button type="button" class="time-pill" onclick="handleChartTimeFilter('24H', this)">24H</button>
                    <button type="button" class="time-pill active" onclick="handleChartTimeFilter('7D', this)">7D</button>
                    <button type="button" class="time-pill" onclick="handleChartTimeFilter('30D', this)">30D</button>
                    <button type="button" class="time-pill" onclick="handleChartTimeFilter('1A', this)">1A</button>
                </div>
            </div>
            
            <div class="svg-chart-container">
                <svg class="interactive-svg" id="dashboardTelemetrySvg" viewBox="0 0 700 255" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="cyanAreaGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#10b9df" stop-opacity="0.35" />
                            <stop offset="70%" stop-color="#10b9df" stop-opacity="0.05" />
                            <stop offset="100%" stop-color="#10b9df" stop-opacity="0" />
                        </linearGradient>
                        <linearGradient id="limeAreaGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#91c51b" stop-opacity="0.28" />
                            <stop offset="70%" stop-color="#91c51b" stop-opacity="0.04" />
                            <stop offset="100%" stop-color="#91c51b" stop-opacity="0" />
                        </linearGradient>
                        <filter id="glowCyan" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#10b9df" flood-opacity="0.5" />
                        </filter>
                        <filter id="glowLime" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#91c51b" flood-opacity="0.5" />
                        </filter>
                    </defs>

                    <!-- Background Grid Lines -->
                    <g stroke="rgba(226, 232, 240, 0.7)" stroke-width="1" stroke-dasharray="4 4">
                        <line x1="50" y1="20" x2="680" y2="20" />
                        <line x1="50" y1="70" x2="680" y2="70" />
                        <line x1="50" y1="120" x2="680" y2="120" />
                        <line x1="50" y1="170" x2="680" y2="170" />
                        <line x1="50" y1="220" x2="680" y2="220" />
                    </g>

                    <!-- Y-Axis Labels -->
                    <g fill="#94a3b8" font-size="10.5" font-family="'Plus Jakarta Sans', sans-serif" font-weight="600">
                        <text x="10" y="24">100%</text>
                        <text x="15" y="74">80%</text>
                        <text x="15" y="124">60%</text>
                        <text x="15" y="174">40%</text>
                        <text x="15" y="224">20%</text>
                    </g>

                    <!-- Lime Area & Spline Path -->
                    <path 
                        id="chartLimeArea"
                        d="M 50 185 C 100 205, 120 210, 155 205 C 190 200, 220 170, 260 165 C 300 160, 330 150, 365 145 C 400 140, 430 115, 470 120 C 510 125, 540 150, 575 145 C 610 140, 640 130, 680 135 L 680 220 L 50 220 Z" 
                        fill="url(#limeAreaGradient)" 
                    />
                    <path 
                        id="chartLimeLine"
                        d="M 50 185 C 100 205, 120 210, 155 205 C 190 200, 220 170, 260 165 C 300 160, 330 150, 365 145 C 400 140, 430 115, 470 120 C 510 125, 540 150, 575 145 C 610 140, 640 130, 680 135" 
                        fill="none" 
                        stroke="#91c51b" 
                        stroke-width="3.5" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                    />

                    <!-- Cyan Area & Spline Path -->
                    <path 
                        id="chartCyanArea"
                        d="M 50 120 C 100 155, 120 160, 155 145 C 190 130, 220 110, 260 115 C 300 120, 330 100, 365 95 C 400 90, 430 50, 470 55 C 510 60, 540 75, 575 70 C 610 65, 640 75, 680 78 L 680 220 L 50 220 Z" 
                        fill="url(#cyanAreaGradient)" 
                    />
                    <path 
                        id="chartCyanLine"
                        d="M 50 120 C 100 155, 120 160, 155 145 C 190 130, 220 110, 260 115 C 300 120, 330 100, 365 95 C 400 90, 430 50, 470 55 C 510 60, 540 75, 575 70 C 610 65, 640 75, 680 78" 
                        fill="none" 
                        stroke="#10b9df" 
                        stroke-width="3.5" 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                    />

                    <!-- Nodes -->
                    <g id="chartCyanNodes" fill="#ffffff" stroke="#10b9df" stroke-width="3" filter="url(#glowCyan)">
                        <circle cx="50" cy="120" r="5" />
                        <circle cx="155" cy="145" r="5" />
                        <circle cx="260" cy="115" r="5" />
                        <circle cx="365" cy="95" r="5" />
                        <circle cx="470" cy="55" r="6" stroke-width="3.5" />
                        <circle cx="575" cy="70" r="5" />
                        <circle cx="680" cy="78" r="5" />
                    </g>
                    <g id="chartLimeNodes" fill="#ffffff" stroke="#91c51b" stroke-width="3" filter="url(#glowLime)">
                        <circle cx="50" cy="185" r="4.5" />
                        <circle cx="155" cy="205" r="4.5" />
                        <circle cx="260" cy="165" r="4.5" />
                        <circle cx="365" cy="145" r="4.5" />
                        <circle cx="470" cy="120" r="5" stroke-width="3.5" />
                        <circle cx="575" cy="145" r="4.5" />
                        <circle cx="680" cy="135" r="4.5" />
                    </g>

                    <!-- X-Axis Marks -->
                    <g id="chartXAxisMarks" fill="#64748b" font-size="11" font-family="'Plus Jakarta Sans', sans-serif" font-weight="600" text-anchor="middle">
                        <text x="50" y="244">18 May</text>
                        <text x="155" y="244">19 May</text>
                        <text x="260" y="244">20 May</text>
                        <text x="365" y="244">21 May</text>
                        <text x="470" y="244">22 May</text>
                        <text x="575" y="244">23 May</text>
                        <text x="680" y="244">24 May</text>
                    </g>
                </svg>
            </div>

            <div class="chart-glow-legend">
                <div class="legend-tag">
                    <span class="dot-indicator cyan"></span>
                    <span>Ruido Ocupacional & Material Particulado 24h</span>
                </div>
                <div class="legend-tag">
                    <span class="dot-indicator lime"></span>
                    <span>Calidad de Agua & Control de Emisiones / Opacidad</span>
                </div>
            </div>
        </div>

        <!-- 3.2. Certified Calibration Instruments Readiness -->
        <div class="glass-card panel-box wow-entrance stagger-5">
            <div class="panel-title-bar">
                <h3>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#91c51b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="6"/>
                        <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
                    </svg>
                    <span>Equipos Patrón Certificados (18/18)</span>
                </h3>
                <span class="status-pill-badge done" style="font-size: 11px;">100% Vigentes</span>
            </div>

            <div class="equipment-readiness-stack">
                @foreach($equipmentInventory as $eq)
                    <div class="equipment-readiness-row">
                        <div class="equipment-name-meta">
                            <div class="equipment-name-title">{{ $eq['name'] }}</div>
                            <div class="equipment-validity-text">{{ $eq['validity'] }}</div>
                        </div>
                        <span class="status-pill-badge done" style="font-size: 11px;">
                            {{ $eq['valid'] }}/{{ $eq['total'] }} Operativos
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- 4. Executive Projects & Modules Master Matrix Table -->
    <div class="glass-card panel-box wow-entrance stagger-5">
        <div class="panel-title-bar table-head-action-bar">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                </svg>
                <span id="projectsTableHeading">Proyectos Industriales en Monitoreo (5)</span>
            </h3>
            
            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="dashboardProjectsSearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por proyecto, cliente o regional..." 
                        onkeyup="searchLiveTableGeneric('dashboardProjectsSearchInput', 'dashboardMasterMatrixTable')"
                        aria-label="Buscar proyectos"
                    >
                </div>

                <a href="{{ route('projects.index') }}" class="filter-toggle-pill" style="text-decoration: none;">
                    <span>Ver todos (36)</span>
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="5" y1="12" x2="19" y2="12"/>
                        <polyline points="12 5 19 12 12 19"/>
                    </svg>
                </a>
            </div>
        </div>
        
        <div class="table-responsive-box">
            <table class="modern-table" id="dashboardMasterMatrixTable">
                <thead>
                    <tr>
                        <th style="width: 45px;">#</th>
                        <th>Proyecto Industrial</th>
                        <th>Empresa / Cliente</th>
                        <th>Regional</th>
                        <th>Módulos Vinculados</th>
                        <th>Puntos Muestreados</th>
                        <th>Cumplimiento LMP</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr data-company-key="{{ $project['company_key'] }}">
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px;">
                                {{ $project['num'] }}
                            </td>

                            <!-- 2. Proyecto -->
                            <td>
                                <div style="font-weight: 700; color: var(--ink); font-size: 13px;">
                                    {{ $project['name'] }}
                                </div>
                                <div style="font-size: 11px; color: #64748b; font-family: monospace;">
                                    {{ $project['code'] }}
                                </div>
                            </td>

                            <!-- 3. Empresa / Cliente -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $project['client_theme'] ?? 'cyan' }}" style="width: 30px; height: 30px; font-size: 11.5px; border-radius: 8px;">
                                        {{ $project['client_initial'] ?? substr($project['client'], 0, 1) }}
                                    </div>
                                    <span style="font-weight: 600; font-size: 12.5px;">{{ $project['client'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Regional -->
                            <td>
                                <span class="status-pill-badge in_progress" style="font-size: 11.5px;">
                                    {{ $project['region'] }}
                                </span>
                            </td>

                            <!-- 5. Módulos Vinculados -->
                            <td>
                                <div style="display: flex; gap: 5px; flex-wrap: wrap;">
                                    @foreach($project['modules_list'] as $modBadge)
                                        <span class="admin-role-badge cyan" style="font-size: 10.5px; padding: 2px 8px;">
                                            {{ $modBadge }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- 6. Puntos Muestreados -->
                            <td>
                                <div class="points-ratio-box">
                                    <div class="points-ratio-text">
                                        <span>{{ $project['points_text'] }}</span>
                                        <span>{{ $project['points_pct'] }}%</span>
                                    </div>
                                    <div class="points-ratio-track">
                                        <div class="points-ratio-fill {{ $project['points_pct'] == 100 ? 'lime' : 'cyan' }}" style="width: {{ $project['points_pct'] }}%;"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- 7. Cumplimiento LMP -->
                            <td>
                                <span class="status-pill-badge done" style="font-size: 11.5px;">
                                    {{ $project['compliance_pct'] }}
                                </span>
                            </td>

                            <!-- 8. Estado -->
                            <td>
                                <span class="status-pill-badge {{ $project['status_type'] ?? 'in_progress' }}">
                                    {{ $project['status'] }}
                                </span>
                            </td>

                            <!-- 9. Acciones Estandarizadas a Colores -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Ver Telemetría / Módulos -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="window.location.href='{{ route('modules.index') }}'" title="Ver Módulos">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                                            <polyline points="2 17 12 22 22 17"/>
                                            <polyline points="2 12 12 17 22 12"/>
                                        </svg>
                                    </button>

                                    <!-- Telemetría en Vivo -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Telemetría en vivo: {{ $project['name'] }}', '⚡')" title="Telemetría">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                        </svg>
                                    </button>

                                    <!-- Certificados de Calibración -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" onclick="triggerToast('Descargando certificados de {{ $project['code'] }}', '📑')" title="Certificados">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="text-align: center; color: var(--muted); padding: 30px;">
                                No se encontraron proyectos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/dashboard.js'])
@endpush
