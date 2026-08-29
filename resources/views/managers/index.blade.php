@extends('layouts.app')

@section('title', 'Responsables Técnicos & de Planta — Metric v2 Pachabol')

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Responsables Técnicos & de Planta</h1>
            <p>Directorio de ingenieros residentes, supervisores de medio ambiente y jefes de planta por empresa y regional.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="triggerToast('Abriendo registro de nuevo responsable', '👔')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nuevo Responsable</span>
        </button>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <div class="panel-title-bar table-head-action-bar">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Directorio de Responsables (5)</span>
            </h3>

            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="managersSearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por responsable, empresa o regional..." 
                        onkeyup="searchLiveTableGeneric('managersSearchInput', 'managersMasterTable')"
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="managersMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Responsable / Especialidad</th>
                        <th>Empresa / Cliente</th>
                        <th>Regional Asignada</th>
                        <th>Proyectos a Cargo</th>
                        <th>Contacto Directo</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $manager)
                        <tr>
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $manager['num'] }}
                            </td>

                            <!-- 2. Responsable / Cargo -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $manager['theme'] ?? 'cyan' }}">
                                        {{ $manager['initial'] }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--ink);">{{ $manager['name'] }}</div>
                                        <div style="font-size: 11.5px; color: #64748b;">{{ $manager['position'] }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Empresa / Cliente -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $manager['company_theme'] ?? 'cyan' }}" style="width: 28px; height: 28px; font-size: 11px; border-radius: 8px;">
                                        {{ $manager['company_initial'] }}
                                    </div>
                                    <span style="font-weight: 600; color: var(--ink); font-size: 12.5px;">{{ $manager['company'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Regional Asignada -->
                            <td>
                                <span class="status-pill-badge in_progress" style="font-size: 11.5px;">
                                    {{ $manager['region'] }}
                                </span>
                            </td>

                            <!-- 5. Proyectos a Cargo -->
                            <td>
                                <span class="status-pill-badge done">
                                    {{ $manager['projects_count'] }}
                                </span>
                            </td>

                            <!-- 6. Contacto Directo -->
                            <td>
                                <div style="font-family: monospace; font-size: 12px; color: #334155; font-weight: 600;">
                                    {{ $manager['phone'] }}
                                </div>
                                <div style="font-size: 11px; color: #64748b;">
                                    {{ $manager['email'] }}
                                </div>
                            </td>

                            <!-- 7. Estado -->
                            <td>
                                <span class="status-pill-badge {{ $manager['status_type'] ?? 'done' }}">
                                    {{ $manager['status'] }}
                                </span>
                            </td>

                            <!-- 8. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Ver Perfil -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="triggerToast('Ver perfil de {{ $manager['name'] }}', '👔')" title="Ver Perfil" aria-label="Ver Perfil">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>

                                    <!-- Editar Ficha -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Editar ficha de {{ $manager['name'] }}', '✏️')" title="Editar" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="if(confirm('¿Deseas desvincular a {{ $manager['name'] }}?')) triggerToast('Responsable archivado', '🗑️')" title="Eliminar" aria-label="Eliminar">
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
                                No se encontraron responsables registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
