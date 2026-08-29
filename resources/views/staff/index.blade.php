@extends('layouts.app')

@section('title', 'Personal & Colaboradores — Metric v2 Pachabol')

@push('styles')
    @vite(['resources/css/staff.css'])
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="staff-header-banner">
        <div>
            <h1>Gestión de Personal & Especialistas</h1>
            <p>Control de plantilla técnica, operadores de monitoreo industrial y especialistas en campo.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="openCreateStaffModal()">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nuevo Colaborador</span>
        </button>
    </div>

    <!-- Main Table Panel -->
    <div class="glass-card panel-box">
        <!-- Toolbar Bar -->
        <div class="staff-toolbar-bar">
            <div class="staff-search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="staffSearchInput" 
                    class="staff-search-input" 
                    placeholder="Buscar por especialista, especialidad o regional..."
                    onkeyup="filterStaffLive()"
                >
            </div>
        </div>

        <!-- Master Table -->
        <div class="table-responsive-box">
            <table class="modern-table" id="staffMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Colaborador</th>
                        <th>Departamento & Regional</th>
                        <th>Proyecto Asignado</th>
                        <th>Dispositivo Vinculado</th>
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
                                        {{ $member['initial'] ?? substr($member['name'] ?? 'P', 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--ink);">{{ $member['name'] ?? 'Especialista' }}</div>
                                        <div style="font-size: 11.5px; color: #64748b;">{{ $member['email'] ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Departamento & Regional -->
                            <td>
                                <div style="font-weight: 700; color: var(--ink);">{{ $member['department'] ?? 'Operaciones' }}</div>
                                <div style="font-size: 11.5px; color: var(--cyan); font-weight: 600; display: inline-flex; align-items: center; gap: 4px; margin-top: 2px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                                        <circle cx="12" cy="10" r="3"/>
                                    </svg>
                                    <span>{{ $member['region'] ?? 'Sede Central' }}</span>
                                </div>
                            </td>

                            <!-- 4. Proyecto Asignado -->
                            <td>
                                <span class="status-pill-badge in_progress" style="font-size: 11.5px; font-weight: 700;">
                                    {{ $member['assigned_project'] ?? 'PRJ-General' }}
                                </span>
                            </td>

                            <!-- 5. Dispositivo Vinculado -->
                            <td>
                                <div style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 12px; font-weight: 600; color: #334155;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
                                        <path d="M12 18h.01"/>
                                    </svg>
                                    <span>{{ $member['linked_device'] ?? $member['phone'] ?? 'Colector Móvil' }}</span>
                                </div>
                            </td>

                            <!-- 6. Estado -->
                            <td>
                                @if(($member['status'] ?? 'online') === 'online')
                                    <span class="status-pill-badge done">{{ $member['status_label'] ?? 'En Planta' }}</span>
                                @else
                                    <span class="status-pill-badge pending">{{ $member['status_label'] ?? 'Inactivo' }}</span>
                                @endif
                            </td>

                            <!-- 7. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- 1. Reset Contraseña WhatsApp -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" 
                                            onclick="openResetPasswordModal('{{ $member['id'] }}', '{{ addslashes($member['name']) }}', '{{ $member['email'] }}')" 
                                            title="Generar Acceso & Enviar por WhatsApp" aria-label="Resetear Contraseña">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </button>

                                    <!-- 2. Editar Colaborador -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" 
                                            onclick="openEditStaffModal('{{ $member['id'] }}', '{{ addslashes($member['name']) }}', '{{ $member['email'] }}', '{{ $member['phone'] ?? '' }}', '{{ addslashes($member['department']) }}', '{{ addslashes($member['position']) }}', '{{ $member['region_id'] ?? '' }}', '{{ $member['status'] ?? 'online' }}')" 
                                            title="Editar Colaborador" aria-label="Editar">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- 3. Eliminar Colaborador -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" 
                                            onclick="deleteStaff('{{ $member['id'] }}', '{{ addslashes($member['name']) }}')" 
                                            title="Eliminar Colaborador" aria-label="Eliminar">
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
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 30px;">
                                No se encontraron colaboradores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hidden global delete form -->
    <form id="deleteFormGlobal" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- ==========================================================================
         MODAL: ALTA DE NUEVO COLABORADOR
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createStaffModal" onclick="if(event.target === this) closeModal('createStaffModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Alta de Personal & Especialista</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('createStaffModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form action="{{ route('staff.store') }}" method="POST">
                @csrf
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre y Apellidos</label>
                            <input type="text" name="name" class="custom-form-input" placeholder="Ej: Ing. Gonzalo Arnez" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Sede Regional</label>
                            <select name="region_id" class="custom-form-select">
                                <option value="">Seleccionar regional...</option>
                                @foreach($regions as $reg)
                                    <option value="{{ $reg->id }}">{{ $reg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Electrónico</label>
                            <input type="email" name="email" class="custom-form-input" placeholder="gonzalo.a@pachabol.com" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono / WhatsApp</label>
                            <input type="text" name="phone" class="custom-form-input" placeholder="+591 715-00000">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Departamento</label>
                            <input type="text" name="department" class="custom-form-input" placeholder="Ingeniería de Automatización" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Cargo / Especialidad</label>
                            <input type="text" name="position" class="custom-form-input" placeholder="Especialista Senior SCADA" required>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Estado de Acceso</label>
                        <select name="status" class="custom-form-select" required>
                            <option value="online" selected>Activo (Permitir Acceso)</option>
                            <option value="offline">Inactivo (Bloquear Acceso)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('createStaffModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Colaborador</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL: EDITAR COLABORADOR
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editStaffModal" onclick="if(event.target === this) closeModal('editStaffModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Editar Personal Técnico</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('editStaffModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="editStaffForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre y Apellidos</label>
                            <input type="text" id="editStaffName" name="name" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Sede Regional</label>
                            <select id="editStaffRegion" name="region_id" class="custom-form-select">
                                <option value="">Sin sede específica</option>
                                @foreach($regions as $reg)
                                    <option value="{{ $reg->id }}">{{ $reg->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Electrónico</label>
                            <input type="email" id="editStaffEmail" name="email" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono / WhatsApp</label>
                            <input type="text" id="editStaffPhone" name="phone" class="custom-form-input">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Departamento</label>
                            <input type="text" id="editStaffDepartment" name="department" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Cargo / Especialidad</label>
                            <input type="text" id="editStaffPosition" name="position" class="custom-form-input" required>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Estado de la Cuenta</label>
                        <select id="editStaffStatus" name="status" class="custom-form-select" required>
                            <option value="online">Activo (En Planta)</option>
                            <option value="offline">Inactivo (Acceso Bloqueado)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('editStaffModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL: RESETEAR CONTRASEÑA & GENERAR ACCESO WHATSAPP
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="resetPasswordModal" onclick="if(event.target === this) closeModal('resetPasswordModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Restablecer Contraseña & Acceso WhatsApp</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('resetPasswordModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="modal-body-custom">
                    <div class="user-identity-modal-banner">
                        <div class="admin-avatar-wrap cyan" style="width: 36px; height: 36px; font-size: 13px;">
                            <span id="resetModalInitial">P</span>
                        </div>
                        <div class="user-identity-modal-info">
                            <h4 id="resetModalName">Nombre del Colaborador</h4>
                            <p id="resetModalEmail">colaborador@pachabol.com</p>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Nueva Contraseña Generada</label>
                        <div style="display: flex; gap: 8px;">
                            <input type="text" id="resetNewPasswordInput" name="new_password" class="custom-form-input" required style="font-family: monospace; font-weight: 700; letter-spacing: 0.5px;">
                            <button type="button" class="btn-generate-pass" onclick="generateRandomPassword()">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                                </svg>
                                <span>Generar</span>
                            </button>
                        </div>
                    </div>

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
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                            </svg>
                            <span id="copyCredentialBtnText">Copiar Acceso para WhatsApp</span>
                        </button>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('resetPasswordModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Nueva Contraseña</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/staff.js'])
@endpush
