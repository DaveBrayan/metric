@extends('layouts.app')

@section('title', 'Proyectos en Ejecución — Metric v2 Pachabol')

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Proyectos Industriales en Ejecución</h1>
            <p>Monitoreo integral de avances, módulos de medición ambiental vinculados y sedes asignadas.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('modules.index') }}" class="date-capsule" style="text-decoration: none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                    <polyline points="2 17 12 22 22 17"/>
                    <polyline points="2 12 12 17 22 12"/>
                </svg>
                <span>Módulos de Monitoreo Ambiental</span>
            </a>
            <button type="button" class="btn-primary-hero-action" onclick="triggerToast('Abriendo registro de nuevo proyecto industrial', '📁')">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Nuevo Proyecto</span>
            </button>
        </div>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <div class="panel-title-bar table-head-action-bar">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                </svg>
                <span>Listado General de Proyectos (5)</span>
            </h3>

            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="projectsListSearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por proyecto, cliente o regional..." 
                        onkeyup="searchLiveTableGeneric('projectsListSearchInput', 'projectsFullMasterTable')"
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="projectsFullMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Nombre del Proyecto</th>
                        <th>Cliente / Empresa</th>
                        <th>Regional</th>
                        <th>Módulos Completados</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($projects as $project)
                        <tr>
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $project['num'] }}
                            </td>

                            <!-- 2. Nombre del Proyecto -->
                            <td>
                                <div style="font-weight: 700; color: var(--ink); font-size: 13.5px;">
                                    {{ $project['name'] }}
                                </div>
                                <div style="font-size: 11px; color: #64748b; font-family: monospace;">
                                    {{ $project['code'] }}
                                </div>
                            </td>

                            <!-- 3. Cliente / Empresa -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $project['client_theme'] ?? 'cyan' }}" style="width: 32px; height: 32px; font-size: 12px; border-radius: 9px;">
                                        {{ $project['client_initial'] ?? substr($project['client'], 0, 1) }}
                                    </div>
                                    <span style="font-weight: 600; color: var(--ink); font-size: 12.5px;">{{ $project['client'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Regional -->
                            <td>
                                <span class="status-pill-badge in_progress" style="font-size: 11.5px;">
                                    {{ $project['region'] }}
                                </span>
                            </td>

                            <!-- 5. Módulos Completados -->
                            <td>
                                <div class="points-ratio-box">
                                    <div class="points-ratio-text">
                                        <span>{{ $project['modules_completed_text'] }}</span>
                                        <span>{{ $project['modules_ratio_pct'] }}%</span>
                                    </div>
                                    <div class="points-ratio-track">
                                        <div class="points-ratio-fill {{ $project['modules_ratio_pct'] == 100 ? 'lime' : 'cyan' }}" style="width: {{ $project['modules_ratio_pct'] }}%;"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- 6. Estado -->
                            <td>
                                <span class="status-pill-badge {{ $project['status_type'] ?? 'in_progress' }}">
                                    {{ $project['status'] }}
                                </span>
                            </td>

                            <!-- 8. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Ver Módulos del Proyecto -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="window.location.href='{{ route('modules.index') }}'" title="Ver Módulos de Medición" aria-label="Ver Módulos">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                                            <polyline points="2 17 12 22 22 17"/>
                                            <polyline points="2 12 12 17 22 12"/>
                                        </svg>
                                    </button>

                                    <!-- Ver Telemetría en Vivo -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Telemetría en vivo: {{ $project['name'] }}', '⚡')" title="Telemetría IoT" aria-label="Telemetría">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                        </svg>
                                    </button>

                                    <!-- Editar Proyecto -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" onclick="triggerToast('Editar proyecto {{ $project['name'] }}', '✏️')" title="Editar Proyecto" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar Proyecto -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="if(confirm('¿Deseas dar de baja el proyecto {{ $project['name'] }}?')) triggerToast('Proyecto archivado', '🗑️')" title="Eliminar Proyecto" aria-label="Eliminar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"/>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                            <line x1="10" y1="11" x2="10" y2="17"/>
                                            <line x1="14" y1="11" x2="14" y2="17"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">
                                No se encontraron proyectos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
