@extends('layouts.app')

@section('title', 'Módulos de Medición & Monitoreo — Metric v2 Pachabol')

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Módulos de Medición & Monitoreo</h1>
            <p>Control operativo de dosimetría de ruido, ruido ambiental, calidad de agua, opacidad y partículas 24 horas.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('projects.index') }}" class="date-capsule" style="text-decoration: none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                <span>Volver a Proyectos</span>
            </a>
            <button type="button" class="btn-primary-hero-action" onclick="triggerToast('Registrando nuevo módulo de medición', '🧪')">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Nuevo Módulo</span>
            </button>
        </div>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <!-- Module Filter Pills & Quick Search -->
        <div class="table-head-action-bar" style="align-items: center;">
            <div class="admins-role-filter-group" id="moduleFilterGroup">
                <button type="button" class="role-filter-pill active" onclick="filterModulesByTag('all', this)">
                    Todos (5)
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('dosimetria', this)">
                    Dosimetría de Ruido
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('ruido_ambiental', this)">
                    Ruido Ambiental
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('agua', this)">
                    Agua (Parám. de Campo)
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('opacidad', this)">
                    Opacidad (Humos/Emisiones)
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('particulas', this)">
                    Partículas 24 Horas
                </button>
            </div>

            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="modulesSearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por módulo, equipo o personal..." 
                        onkeyup="searchLiveTableGeneric('modulesSearchInput', 'modulesMasterTable')"
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="modulesMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Módulo de Medición</th>
                        <th>Equipo de Calibración</th>
                        <th>Personal de Campo</th>
                        <th>Puntos Completados</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($modulesData as $item)
                        <tr data-module-type="{{ $item['module_key'] }}">
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $item['num'] }}
                            </td>

                            <!-- 2. Módulo de Medición -->
                            <td>
                                <div style="font-weight: 700; color: var(--ink); font-size: 13.5px;">
                                    {{ $item['module_name'] }}
                                </div>
                                <div style="font-size: 11.5px; color: #64748b;">
                                    {{ $item['module_sub'] }}
                                </div>
                            </td>

                            <!-- 3. Equipo de Calibración -->
                            <td style="font-size: 12px; color: #334155; font-family: monospace; max-width: 240px;">
                                <div style="font-weight: 600;">{{ $item['equipment'] }}</div>
                            </td>

                            <!-- 4. Personal de Campo -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $item['staff_theme'] ?? 'cyan' }}" style="width: 32px; height: 32px; font-size: 12px; border-radius: 9px;">
                                        {{ $item['staff_initial'] }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--ink); font-size: 12.5px;">{{ $item['staff_name'] }}</div>
                                        <div style="font-size: 11px; color: #64748b;">{{ $item['staff_role'] }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 5. Puntos Completados (con barra de progreso) -->
                            <td>
                                <div class="points-ratio-box">
                                    <div class="points-ratio-text">
                                        <span>{{ $item['points_text'] }}</span>
                                        <span>{{ $item['points_pct'] }}%</span>
                                    </div>
                                    <div class="points-ratio-track">
                                        <div class="points-ratio-fill {{ $item['points_pct'] == 100 ? 'lime' : 'cyan' }}" style="width: {{ $item['points_pct'] }}%;"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- 6. Estado -->
                            <td>
                                <span class="status-pill-badge {{ $item['status_theme'] ?? 'done' }}">
                                    {{ $item['status'] }}
                                </span>
                            </td>

                            <!-- 8. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Ver Telemetría / Curva -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="triggerToast('Telemetría en vivo: {{ $item['module_name'] }}', '📈')" title="Ver Gráfica de Medición" aria-label="Ver Medición">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                                        </svg>
                                    </button>

                                    <!-- Descargar Certificado de Calibración -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" onclick="triggerToast('Descargando certificado de calibración', '📑')" title="Descargar Certificado de Calibración" aria-label="Certificado">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="7 10 12 15 17 10"/>
                                            <line x1="12" y1="15" x2="12" y2="3"/>
                                        </svg>
                                    </button>

                                    <!-- Editar Módulo -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Editar parámetros de {{ $item['module_name'] }}', '✏️')" title="Editar Módulo" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Suspender / Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="if(confirm('¿Deseas dar de baja este módulo?')) triggerToast('Módulo archivado', '🗑️')" title="Eliminar Módulo" aria-label="Eliminar">
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
                                No se encontraron módulos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Script for Live Tag Filtering -->
    <script>
        function filterModulesByTag(moduleKey, btnElement) {
            document.querySelectorAll('#moduleFilterGroup .role-filter-pill').forEach(b => b.classList.remove('active'));
            btnElement.classList.add('active');

            const rows = document.querySelectorAll('#modulesMasterTable tbody tr');
            rows.forEach(row => {
                const rowType = row.getAttribute('data-module-type');
                if (moduleKey === 'all' || rowType === moduleKey) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
@endsection
