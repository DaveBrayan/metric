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
                <button type="button" class="role-filter-pill active" onclick="filterAdminsByRole('all', this)">Todos ({{ count($admins) }})</button>
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
                        <tr id="admin-row-{{ $admin['id'] }}" data-role="{{ $admin['role'] }}">
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $admin['num'] }}
                            </td>

                            <!-- 2. Usuario -->
                            <td>
                                <div class="admin-user-cell">
                                    <div class="admin-avatar-wrap {{ $admin['role_theme'] ?? 'cyan' }}">
                                        <span>{{ $admin['initial'] }}</span>
                                        @if(($admin['status'] ?? 'online') === 'online')
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
                                    @php
                                        $permsList = is_array($admin['permissions']) ? $admin['permissions'] : [];
                                    @endphp
                                    @forelse($permsList as $perm)
                                        <span class="permission-chip">{{ $perm }}</span>
                                    @empty
                                        <span class="permission-chip" style="color: #94a3b8;">Sin permisos asignados</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- 5. Estado -->
                            <td>
                                @if(($admin['status'] ?? 'online') === 'online')
                                    <span class="status-pill-badge done">En línea</span>
                                @else
                                    <span class="status-pill-badge pending">Inactivo</span>
                                @endif
                            </td>

                            <!-- 6. Acciones Interactivas -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- 1. Ver / Gestionar Permisos -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" 
                                            onclick="openPermissionsModal('{{ $admin['id'] }}', '{{ addslashes($admin['name']) }}', '{{ $admin['role'] }}', {{ json_encode($permsList) }})" 
                                            title="Gestionar Permisos por Página" aria-label="Gestionar Permisos">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            <polyline points="9 12 11 14 15 10"/>
                                        </svg>
                                    </button>

                                    <!-- 2. Resetear Contraseña & Copiar WhatsApp -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" 
                                            onclick="openResetPasswordModal('{{ $admin['id'] }}', '{{ addslashes($admin['name']) }}', '{{ $admin['email'] }}')" 
                                            title="Restablecer Contraseña & Generar Acceso WhatsApp" aria-label="Resetear Contraseña">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </button>

                                    <!-- 3. Editar Administrador -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" 
                                            onclick="openEditAdminModal('{{ $admin['id'] }}', '{{ addslashes($admin['name']) }}', '{{ $admin['email'] }}', '{{ $admin['phone'] ?? '' }}', '{{ $admin['role'] }}', '{{ $admin['status'] ?? 'online' }}')" 
                                            title="Editar Datos" aria-label="Editar Administrador">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- 4. Eliminar Administrador -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" 
                                            onclick="openDeleteAdminModal('{{ $admin['id'] }}', '{{ addslashes($admin['name']) }}')" 
                                            title="Eliminar Administrador" aria-label="Eliminar Administrador">
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

    <!-- ==========================================================================
         MODAL 1: ALTA DE NUEVO ADMINISTRADOR
         ========================================================================== -->
    <div class="admin-modal-backdrop" id="adminCreateModal" onclick="if(event.target === this) closeAdminModal('adminCreateModal')">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3>Alta de Nuevo Administrador</h3>
                <button type="button" class="btn-close-modal" onclick="closeAdminModal('adminCreateModal')" aria-label="Cerrar modal">✕</button>
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

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono / WhatsApp</label>
                            <input type="text" name="phone" class="custom-form-input" placeholder="+591 715-00000">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Rol de Privilegio</label>
                            <select name="role" class="custom-form-select" required>
                                <option value="Superadministrador">Superadministrador (Control Total)</option>
                                <option value="Operador de Planta" selected>Operador de Planta (Telemetría & Campo)</option>
                                <option value="Analista de Datos">Analista de Datos (Reportes & Métricas)</option>
                                <option value="Supervisor Técnico">Supervisor Técnico</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn-subtle-link" onclick="closeAdminModal('adminCreateModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Crear Administrador</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 2: EDITAR ADMINISTRADOR
         ========================================================================== -->
    <div class="admin-modal-backdrop" id="adminEditModal" onclick="if(event.target === this) closeAdminModal('adminEditModal')">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3>Editar Administrador</h3>
                <button type="button" class="btn-close-modal" onclick="closeAdminModal('adminEditModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="editAdminForm" method="POST">
                @csrf
                @method('PUT')
                <div class="admin-modal-body">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre Completo</label>
                            <input type="text" id="editAdminName" name="name" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Corporativo</label>
                            <input type="email" id="editAdminEmail" name="email" class="custom-form-input" required>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono / WhatsApp</label>
                            <input type="text" id="editAdminPhone" name="phone" class="custom-form-input">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Rol Asignado</label>
                            <select id="editAdminRole" name="role" class="custom-form-select" required>
                                <option value="Superadministrador">Superadministrador</option>
                                <option value="Operador de Planta">Operador de Planta</option>
                                <option value="Analista de Datos">Analista de Datos</option>
                                <option value="Supervisor Técnico">Supervisor Técnico</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Estado de la Cuenta</label>
                        <select id="editAdminStatus" name="status" class="custom-form-select" required>
                            <option value="online">En línea (Activo)</option>
                            <option value="offline">Inactivo / Suspendido</option>
                        </select>
                    </div>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn-subtle-link" onclick="closeAdminModal('adminEditModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 3: RESETEAR CONTRASEÑA & GENERAR ACCESO WHATSAPP
         ========================================================================== -->
    <div class="admin-modal-backdrop" id="adminResetPasswordModal" onclick="if(event.target === this) closeAdminModal('adminResetPasswordModal')">
        <div class="admin-modal-dialog">
            <div class="admin-modal-header">
                <h3>Restablecer Contraseña & Generar Acceso</h3>
                <button type="button" class="btn-close-modal" onclick="closeAdminModal('adminResetPasswordModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="admin-modal-body">
                    <!-- User Banner -->
                    <div class="user-identity-modal-banner">
                        <div class="admin-avatar-wrap cyan" style="width: 36px; height: 36px; font-size: 13px;">
                            <span id="resetModalInitial">U</span>
                        </div>
                        <div class="user-identity-modal-info">
                            <h4 id="resetModalName">Nombre de Usuario</h4>
                            <p id="resetModalEmail">usuario@metric.com</p>
                        </div>
                    </div>

                    <!-- Password generator row -->
                    <div class="form-field-group">
                        <label class="form-field-label">Nueva Contraseña de Acceso</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="resetNewPasswordInput" name="new_password" class="custom-form-input" required style="font-family: monospace; font-weight: 700; letter-spacing: 0.5px;">
                            <button type="button" class="btn-generate-pass" onclick="generateRandomPassword()">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                                </svg>
                                <span>Generar</span>
                            </button>
                        </div>
                    </div>

                    <!-- WhatsApp Credential Preview Box -->
                    <div class="whatsapp-credential-card">
                        <div class="whatsapp-card-header">
                            <div class="whatsapp-badge">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0 0 12.04 2z"/>
                                </svg>
                                <span>Mensaje Listo para WhatsApp</span>
                            </div>
                            <span style="font-size: 11px; color: #8696a0;">METRIC V2</span>
                        </div>

                        <div class="whatsapp-message-box" id="whatsappCredentialText">
*🔐 Credenciales de Acceso — METRIC V2*
━━━━━━━━━━━━━━━━━━━━━
👤 *Usuario:* cargando...
🔑 *Contraseña:* cargando...
🌐 *Enlace:* {{ url('/login') }}
━━━━━━━━━━━━━━━━━━━━━
⚠️ _Recomendamos ingresar y actualizar su contraseña._
                        </div>

                        <button type="button" class="btn-copy-credential" onclick="copyWhatsAppCredentials()">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                            </svg>
                            <span id="copyCredentialBtnText">Copiar Acceso para WhatsApp</span>
                        </button>
                    </div>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn-subtle-link" onclick="closeAdminModal('adminResetPasswordModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Nueva Contraseña</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 4: GESTIÓN GRANULAR DE PERMISOS POR PÁGINA
         ========================================================================== -->
    <div class="admin-modal-backdrop" id="adminPermissionsModal" onclick="if(event.target === this) closeAdminModal('adminPermissionsModal')">
        <div class="admin-modal-dialog" style="max-width: 680px;">
            <div class="admin-modal-header">
                <h3>Matriz de Permisos & Privilegios</h3>
                <button type="button" class="btn-close-modal" onclick="closeAdminModal('adminPermissionsModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="permissionsForm" method="POST">
                @csrf
                <div class="admin-modal-body">
                    <!-- User Header -->
                    <div class="user-identity-modal-banner">
                        <div class="admin-avatar-wrap cyan" style="width: 36px; height: 36px; font-size: 13px;">
                            <span id="permModalInitial">U</span>
                        </div>
                        <div class="user-identity-modal-info">
                            <h4 id="permModalName">Nombre de Usuario</h4>
                            <p id="permModalRole">Rol: Superadministrador</p>
                        </div>
                    </div>

                    <!-- Quick selection pills -->
                    <div class="perms-quick-actions">
                        <span style="font-size: 12px; font-weight: 700; color: #64748b; margin-right: 4px;">Acceso Rápido:</span>
                        <button type="button" class="btn-perm-pill primary" onclick="toggleAllPermissions(true)">⚡ Control Total (Marcar Todo)</button>
                        <button type="button" class="btn-perm-pill" onclick="setReadOnlyPermissions()">👁️ Solo Lectura</button>
                        <button type="button" class="btn-perm-pill" onclick="toggleAllPermissions(false)">Desmarcar Todo</button>
                    </div>

                    <!-- 1. Empresas -->
                    <div class="perms-section-card">
                        <div class="perms-section-header">
                            <div class="perms-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 21h18M3 7v14M21 7v14M6 11h4M6 15h4M14 11h4M14 15h4M9 3l3-2 3 2v4H9z"/></svg>
                                <span>Empresas Clientes (/empresas)</span>
                            </div>
                        </div>
                        <div class="perms-grid-row">
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Empresas - Ver"><span>Ver</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Empresas - Crear"><span>Crear</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Empresas - Editar"><span>Editar</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Empresas - Eliminar"><span>Eliminar</span></label>
                        </div>
                    </div>

                    <!-- 2. Regionales -->
                    <div class="perms-section-card">
                        <div class="perms-section-header">
                            <div class="perms-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>Sedes Regionales (/regionales)</span>
                            </div>
                        </div>
                        <div class="perms-grid-row">
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Regionales - Ver"><span>Ver</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Regionales - Crear"><span>Crear</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Regionales - Editar"><span>Editar</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Regionales - Eliminar"><span>Eliminar</span></label>
                        </div>
                    </div>

                    <!-- 3. Responsables -->
                    <div class="perms-section-card">
                        <div class="perms-section-header">
                            <div class="perms-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                <span>Responsables de Planta (/responsables)</span>
                            </div>
                        </div>
                        <div class="perms-grid-row">
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Responsables - Ver"><span>Ver</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Responsables - Crear"><span>Crear</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Responsables - Editar"><span>Editar</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Responsables - Eliminar"><span>Eliminar</span></label>
                        </div>
                    </div>

                    <!-- 4. Personal Técnico -->
                    <div class="perms-section-card">
                        <div class="perms-section-header">
                            <div class="perms-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span>Personal Técnico & Especialistas (/personal)</span>
                            </div>
                        </div>
                        <div class="perms-grid-row">
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Personal - Ver"><span>Ver</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Personal - Crear"><span>Crear</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Personal - Editar"><span>Editar</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Personal - Eliminar"><span>Eliminar</span></label>
                        </div>
                    </div>

                    <!-- 5. Proyectos Industriales -->
                    <div class="perms-section-card">
                        <div class="perms-section-header">
                            <div class="perms-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                                <span>Proyectos Industriales (/proyectos)</span>
                            </div>
                        </div>
                        <div class="perms-grid-row">
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Proyectos - Ver"><span>Ver</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Proyectos - Crear"><span>Crear</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Proyectos - Editar"><span>Editar</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Proyectos - Eliminar"><span>Eliminar</span></label>
                        </div>
                    </div>

                    <!-- 6. Módulos & Telemetría -->
                    <div class="perms-section-card">
                        <div class="perms-section-header">
                            <div class="perms-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                                <span>Módulos de Medición & Telemetría IoT</span>
                            </div>
                        </div>
                        <div class="perms-grid-row">
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Telemetría - Ver"><span>Ver En Vivo</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Telemetría - Cargar"><span>Cargar Lecturas</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Telemetría - Calibrar"><span>Calibrar Equipos</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Telemetría - Reportes"><span>Exportar LMP</span></label>
                        </div>
                    </div>

                    <!-- 7. Seguridad & Administradores -->
                    <div class="perms-section-card">
                        <div class="perms-section-header">
                            <div class="perms-section-title">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                <span>Administradores & Configuración (/configuracion)</span>
                            </div>
                        </div>
                        <div class="perms-grid-row">
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Seguridad - Ver"><span>Ver Ajustes</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Seguridad - Crear Admins"><span>Crear Admins</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Seguridad - Reset Claves"><span>Reset Claves</span></label>
                            <label class="perm-toggle-box"><input type="checkbox" name="permissions[]" value="Seguridad - API Keys"><span>Tokens & API</span></label>
                        </div>
                    </div>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn-subtle-link" onclick="closeAdminModal('adminPermissionsModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Matriz de Permisos</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 5: CONFIRMAR ELIMINACIÓN DE ADMINISTRADOR
         ========================================================================== -->
    <div class="admin-modal-backdrop" id="adminDeleteModal" onclick="if(event.target === this) closeAdminModal('adminDeleteModal')">
        <div class="admin-modal-dialog" style="max-width: 460px;">
            <div class="admin-modal-header" style="border-bottom-color: rgba(239, 68, 68, 0.2);">
                <h3 style="color: #dc2626; display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Confirmar Eliminación</span>
                </h3>
                <button type="button" class="btn-close-modal" onclick="closeAdminModal('adminDeleteModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="deleteAdminForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="admin-modal-body" style="padding-top: 18px;">
                    <p style="font-size: 13.5px; color: #334155; line-height: 1.6;">
                        ¿Estás completamente seguro de revocar el acceso y eliminar permanentemente al administrador <b id="deleteAdminName" style="color: var(--ink);">Usuario</b>?
                    </p>
                    <p style="font-size: 12px; color: #dc2626; background: #fee2e2; padding: 10px 12px; border-radius: 8px; margin-top: 12px;">
                        ⚠️ Esta acción suspenderá sus sesiones activas de inmediato.
                    </p>
                </div>

                <div class="admin-modal-footer">
                    <button type="button" class="btn-subtle-link" onclick="closeAdminModal('adminDeleteModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action" style="background: #dc2626; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.35);">Sí, Eliminar Administrador</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/admins.js'])
@endpush
