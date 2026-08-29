@extends('layouts.app')

@section('title', 'Sedes Regionales & Plantas — Metric v2 Pachabol')

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Sedes Regionales & Plantas Operativas</h1>
            <p>Supervisión geográfica descentralizada, directores de sede y proyectos asignados.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="triggerToast('Abriendo registro de nueva sede regional', '📍')">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nueva Regional</span>
        </button>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <div class="panel-title-bar table-head-action-bar">
            <h3>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>Sedes Operativas (5)</span>
            </h3>

            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="regionSearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por regional o encargado..." 
                        onkeyup="searchLiveTableGeneric('regionSearchInput', 'regionsMasterTable')"
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="regionsMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Sede Regional & Código</th>
                        <th>Director / Encargado</th>
                        <th>Dirección Operativa</th>
                        <th>Proyectos Asignados</th>
                        <th>Personal</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($regions as $region)
                        <tr>
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $region['num'] }}
                            </td>

                            <!-- 2. Sede Regional & Código -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $region['theme'] ?? 'cyan' }}">
                                        {{ $region['initial'] }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--ink);">{{ $region['name'] }}</div>
                                        <div style="font-size: 11px; color: #64748b; font-family: monospace;">{{ $region['code'] }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Director -->
                            <td style="font-weight: 600; color: var(--ink-secondary);">
                                {{ $region['manager'] }}
                            </td>

                            <!-- 4. Dirección Operativa -->
                            <td style="font-size: 12px; color: #64748b;">
                                {{ $region['address'] }}
                            </td>

                            <!-- 5. Proyectos Asignados -->
                            <td>
                                <span class="status-pill-badge done">
                                    {{ $region['assigned_projects'] }}
                                </span>
                            </td>

                            <!-- 6. Personal -->
                            <td style="font-size: 12px; color: #334155; font-weight: 600;">
                                {{ $region['staff_count'] }}
                            </td>

                            <!-- 7. Estado -->
                            <td>
                                <span class="status-pill-badge done">{{ $region['status'] }}</span>
                            </td>

                            <!-- 8. Acciones Estandarizadas a Colores -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Ver Telemetría / Sede -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="triggerToast('Telemetría de la sede {{ $region['name'] }}', '📡')" title="Telemetría" aria-label="Telemetría">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                                        </svg>
                                    </button>

                                    <!-- Editar -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="triggerToast('Editar sede {{ $region['name'] }}', '✏️')" title="Editar" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="if(confirm('¿Deseas dar de baja la regional {{ $region['name'] }}?')) triggerToast('Regional archivada', '🗑️')" title="Eliminar" aria-label="Eliminar">
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
                                No se encontraron sedes regionales registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
