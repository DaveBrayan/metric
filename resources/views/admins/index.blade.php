@extends('layouts.app')

@section('title', 'Gestión de Administradores — Metric v2 Pachabol')

@push('styles')
    @vite(['resources/css/admins.css'])
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Gestión de Administradores & Roles</h1>
            <p>Control de acceso corporativo, asignación de privilegios industriales y supervisión de sesiones.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="openCreateAdminModal()">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nuevo Administrador</span>
        </button>
    </div>

    <!-- Main Table Panel -->
    <div class="glass-card panel-box">
        <!-- Toolbar Bar -->
        <div class="admins-toolbar-bar">
            <div class="admins-search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="adminsSearchInput" 
                    class="admins-search-input" 
                    placeholder="Buscar por nombre, correo o rol..."
                    onkeyup="filterAdminsLive()"
                >
            </div>

            <div class="admins-role-filter-group">
                <button type="button" class="role-filter-pill active" onclick="filterAdminsByRole('all', this)">Todos (6)</button>
                <button type="button" class="role-filter-pill" onclick="filterAdminsByRole('Superadministrador', this)">Superadmin</button>
                <button type="button" class="role-filter-pill" onclick="filterAdminsByRole('Operador', this)">Operadores</button>
                <button type="button" class="role-filter-pill" onclick="filterAdminsByRole('Analista', this)">Analistas</button>
            </div>
        </div>

        <!-- Master Table -->
        <div class="table-responsive-box">
            <table class="modern-table" id="adminsMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Administrador / Usuario</th>
                        <th>Rol Asignado</th>
                        <th>Matriz de Permisos</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($admins as $admin)
                        <tr>
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $admin['num'] }}
                            </td>

                            <!-- 2. Usuario -->
                            <td>
                                <div class="admin-user-cell">
                                    <div class="admin-avatar-wrap {{ $admin['role_theme'] ?? 'cyan' }}">
                                        <span>{{ $admin['initial'] }}</span>
                                        @if($admin['status'] === 'online')
                                            <span class="online-dot" style="bottom: -2px; right: -2px;"></span>
                                        @endif
                                    </div>
                                    <div class="admin-user-info">
                                        <div class="admin-user-name">{{ $admin['name'] }}</div>
                                        <div class="admin-user-email">{{ $admin['email'] }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Rol -->
                            <td>
                                <span class="admin-role-badge {{ $admin['role_theme'] ?? 'cyan' }}">
                                    {{ $admin['role'] }}
                                </span>
                            </td>

                            <!-- 4. Permisos -->
                            <td>
                                <div class="permissions-chips-wrap">
                                    @foreach($admin['permissions'] as $perm)
                                        <span class="permission-chip">{{ $perm }}</span>
                                    @endforeach
                                </div>
                            </td>

                            <!-- 5. Estado -->
                            <td>
                                @if($admin['status'] === 'online')
                                    <span class="status-pill-badge done">En línea</span>
                                @else
                                    <span class="status-pill-badge pending">Inactivo</span>
                                @endif
                            </td>

                            <!-- 6. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- 1. Ver / Gestionar Permisos -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="handleAdminRowAction('Ver y Gestionar Permisos', '{{ $admin['name'] }}')" title="Ver / Gestionar Permisos" aria-label="Ver Permisos">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            <polyline points="9 12 11 14 15 10"/>
                                        </svg>
                                    </button>

                                    <!-- 2. Resetear Contraseña -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" onclick="handleAdminRowAction('Restablecer Contraseña', '{{ $admin['name'] }}')" title="Resetear Contraseña" aria-label="Resetear Contraseña">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </button>

                                    <!-- 3. Editar Administrador -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" onclick="handleAdminRowAction('Editar Datos de Administrador', '{{ $admin['name'] }}')" title="Editar Administrador" aria-label="Editar Administrador">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- 4. Eliminar Administrador -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="if(confirm('¿Estás seguro de suspender/eliminar al administrador {{ $admin['name'] }}?')) handleAdminRowAction('Eliminar Administrador', '{{ $admin['name'] }}')" title="Eliminar Administrador" aria-label="Eliminar Administrador">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
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
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 30px;">
                                No se encontraron administradores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Administrator Modal Window -->
    <div class="admin-modal-backdrop" id="adminCreateModal" onclick="if(event.target === this) closeAdminModal()">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3>Alta de Nuevo Administrador</h3>
                <button type="button" class="btn-close-modal" onclick="closeAdminModal()" aria-label="Cerrar modal">✕</button>
            </div>

            <form action="{{ route('admins.store') }}" method="POST">
                @csrf
                <div class="admin-modal-body">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre Completo</label>
                            <input type="text" name="name" class="custom-form-input" placeholder="Ej: Fernando Rojas" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Corporativo</label>
                            <input type="email" name="email" class="custom-form-input" placeholder="fernando.r@pachabol.com" required>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Rol y Nivel de Privilegio</label>
                        <select name="role" class="custom-form-select" required>
                            <option value="Superadministrador">Superadministrador (Control total)</option>
                            <option value="Operador de Planta" selected>Operador de Planta (Telemetría y Control)</option>
                            <option value="Analista de Datos">Analista de Datos (Reportes y Métricas)</option>
                            <option value="Supervisor Técnico">Supervisor Técnico</option>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Permisos Específicos</label>
                        <div class="checkbox-permissions-grid">
                            <label class="permission-check-label">
                                <input type="checkbox" name="permissions[]" value="Telemetría" checked>
                                <span>Monitoreo de Telemetría</span>
                            </label>
                            <label class="permission-check-label">
                                <input type="checkbox" name="permissions[]" value="Control de Planta" checked>
                                <span>Control de Sensores</span>
                            </label>
                            <label class="permission-check-label">
                                <input type="checkbox" name="permissions[]" value="Reportes" checked>
                                <span>Generación de Reportes</span>
                            </label>
                            <label class="permission-check-label">
                                <input type="checkbox" name="permissions[]" value="Configuración">
                                <span>Configuración de Servidores</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn-subtle-link" onclick="closeAdminModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Crear y Enviar Invitación</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admins.js'])
@endpush
