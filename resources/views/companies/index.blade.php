@extends('layouts.app')

@section('title', 'Empresas & Clientes — Metric v2 Pachabol')

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Directorio de Empresas & Clientes</h1>
            <p>Monitoreo de convenios corporativos, sectores industriales y plantas vinculadas.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="triggerToast('Abriendo registro de nueva empresa', '🏢')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nueva Empresa</span>
        </button>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <div class="panel-title-bar table-head-action-bar">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                </svg>
                <span>Empresas Registradas (5)</span>
            </h3>

            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="companySearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por empresa, NIT o ciudad..." 
                        onkeyup="searchLiveTableGeneric('companySearchInput', 'companyMasterTable')"
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="companyMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Empresa / Razón Social</th>
                        <th>Sector Industrial</th>
                        <th>NIT / Identificador</th>
                        <th>Contacto Principal</th>
                        <th>Proyectos</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($companies as $company)
                        <tr>
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $company['num'] }}
                            </td>

                            <!-- 2. Empresa / Razón Social -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $company['theme'] ?? 'cyan' }}">
                                        {{ $company['initial'] ?? substr($company['name'] ?? 'E', 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--ink);">{{ $company['name'] ?? 'Empresa' }}</div>
                                        <div style="font-size: 11.5px; color: #64748b;">{{ $company['city'] ?? $company['code'] ?? 'Nacional' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Sector Industrial -->
                            <td>
                                <span class="status-pill-badge in_progress">{{ $company['industry'] ?? 'Industrial' }}</span>
                            </td>

                            <!-- 4. NIT -->
                            <td style="font-family: monospace; font-size: 12.5px; color: #334155; font-weight: 600;">
                                {{ $company['nit'] ?? '—' }}
                            </td>

                            <!-- 5. Contacto Principal -->
                            <td>
                                <div style="font-weight: 600; color: var(--ink-secondary);">{{ $company['contact_name'] ?? $company['contact_person'] ?? '—' }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $company['contact_email'] ?? $company['email'] ?? '—' }}</div>
                            </td>

                            <!-- 6. Proyectos -->
                            <td>
                                <span class="status-pill-badge done">{{ $company['projects_count'] ?? $company['active_projects'] ?? '0 Proyectos' }}</span>
                            </td>

                            <!-- 7. Estado -->
                            <td>
                                <span class="status-pill-badge done">{{ $company['status'] ?? 'Activo' }}</span>
                            </td>

                            <!-- 8. Acciones Estandarizadas a Colores -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Ver Ficha / Detalle -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="triggerToast('Ver ficha de {{ $company['name'] }}', '🏢')" title="Ver Empresa" aria-label="Ver Ficha">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>

                                    <!-- Editar -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Editar empresa {{ $company['name'] }}', '✏️')" title="Editar" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="if(confirm('¿Deseas dar de baja a {{ $company['name'] }}?')) triggerToast('Empresa archivada', '🗑️')" title="Eliminar" aria-label="Eliminar">
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
                                No se encontraron empresas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
