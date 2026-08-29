@extends('layouts.app')

@section('title', 'Personal & Colaboradores — Metric v2 Pachabol')

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Gestión de Personal & Colaboradores</h1>
            <p>Control de plantilla técnica, operadores de planta industrial y especialistas en campo.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="triggerToast('Abriendo registro de nuevo colaborador', '👤')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nuevo Colaborador</span>
        </button>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <div class="panel-title-bar table-head-action-bar">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Nómina de Colaboradores (5)</span>
            </h3>

            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="staffSearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por nombre, cargo o regional..." 
                        onkeyup="searchLiveTableGeneric('staffSearchInput', 'staffMasterTable')"
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="staffMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Colaborador</th>
                        <th>Departamento & Especialidad</th>
                        <th>Regional Asignada</th>
                        <th>Teléfono Corporativo</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $member['num'] }}
                            </td>

                            <!-- 2. Colaborador -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $member['role_theme'] ?? 'cyan' }}">
                                        {{ $member['initial'] }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--ink);">{{ $member['name'] }}</div>
                                        <div style="font-size: 11.5px; color: #64748b;">{{ $member['email'] }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Departamento & Cargo -->
                            <td>
                                <div style="font-weight: 600; color: var(--ink-secondary);">{{ $member['position'] }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $member['department'] }}</div>
                            </td>

                            <!-- 4. Regional Asignada -->
                            <td>
                                <span class="status-pill-badge in_progress">{{ $member['region'] }}</span>
                            </td>

                            <!-- 5. Teléfono -->
                            <td style="font-family: monospace; font-size: 12px; color: #475569; font-weight: 600;">
                                {{ $member['phone'] }}
                            </td>

                            <!-- 6. Estado -->
                            <td>
                                @if($member['status'] === 'online')
                                    <span class="status-pill-badge done">{{ $member['status_label'] }}</span>
                                @else
                                    <span class="status-pill-badge pending">{{ $member['status_label'] }}</span>
                                @endif
                            </td>

                            <!-- 7. Acciones Estandarizadas a Colores -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Ver Perfil -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="triggerToast('Ver perfil de {{ $member['name'] }}', '👤')" title="Ver Perfil" aria-label="Ver Perfil">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>

                                    <!-- Editar -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Editar ficha de {{ $member['name'] }}', '✏️')" title="Editar" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="if(confirm('¿Deseas desvincular a {{ $member['name'] }}?')) triggerToast('Colaborador archivado', '🗑️')" title="Eliminar" aria-label="Eliminar">
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
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 30px;">
                                No se encontraron colaboradores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
