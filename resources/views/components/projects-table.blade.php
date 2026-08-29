@props(['projects'])

<div class="glass-card panel-box wow-entrance stagger-7">
    <div class="panel-title-bar table-head-action-bar">
        <h3>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
            </svg>
            <span>Proyectos en Monitoreo Activo</span>
        </h3>
        
        <div class="table-search-and-action">
            <!-- Embedded Table Filter Input -->
            <div class="table-quick-search-box">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="dashboardProjectsSearchInput" 
                    class="table-search-input" 
                    placeholder="Filtrar proyectos o clientes..." 
                    onkeyup="searchLiveTableGeneric('dashboardProjectsSearchInput', 'dashboardInteractiveProjectsTable')"
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
        <table class="modern-table" id="dashboardInteractiveProjectsTable">
            <thead>
                <tr>
                    <th style="width: 45px;">#</th>
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
                        <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px;">
                            {{ $project['num'] ?? '01' }}
                        </td>

                        <!-- 2. Nombre del Proyecto -->
                        <td>
                            <div style="font-weight: 700; color: var(--ink); font-size: 13px;">
                                {{ $project['name'] }}
                            </div>
                            <div style="font-size: 11px; color: #64748b; font-family: monospace;">
                                {{ $project['code'] ?? 'PRJ-00' }}
                            </div>
                        </td>

                        <!-- 3. Cliente / Empresa -->
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
                                {{ $project['region'] ?? 'La Paz' }}
                            </span>
                        </td>

                        <!-- 5. Módulos Completados -->
                        <td>
                            <div class="points-ratio-box">
                                <div class="points-ratio-text">
                                    <span>{{ $project['modules_completed_text'] ?? '3 de 5 Módulos' }}</span>
                                    <span>{{ $project['modules_ratio_pct'] ?? 60 }}%</span>
                                </div>
                                <div class="points-ratio-track">
                                    <div class="points-ratio-fill {{ ($project['modules_ratio_pct'] ?? 0) == 100 ? 'lime' : 'cyan' }}" style="width: {{ $project['modules_ratio_pct'] ?? 60 }}%;"></div>
                                </div>
                            </div>
                        </td>

                        <!-- 6. Estado -->
                        <td>
                            <span class="status-pill-badge {{ $project['status_type'] ?? 'in_progress' }}">
                                {{ $project['status'] }}
                            </span>
                        </td>

                        <!-- 7. Acciones Estandarizadas a Colores -->
                        <td>
                            <div class="admin-actions-cell">
                                <button type="button" class="btn-admin-icon-action theme-cyan" onclick="window.location.href='{{ route('modules.index') }}'" title="Ver Módulos">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                                        <polyline points="2 17 12 22 22 17"/>
                                        <polyline points="2 12 12 17 22 12"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Telemetría en vivo: {{ $project['name'] }}', '⚡')" title="Telemetría">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--muted); padding: 30px;">
                            No se encontraron proyectos activos.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
