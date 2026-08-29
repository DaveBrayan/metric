@extends('layouts.app')

@section('title', 'Responsables de Planta — Metric v2 Pachabol')

@push('styles')
    @vite(['resources/css/managers.css'])
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="managers-header-banner">
        <div>
            <h1>Responsables de Planta & Clientes</h1>
            <p>Directorio de contactos clave, supervisores de área y líderes de cumplimiento ambiental.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="openCreateManagerModal()">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nuevo Responsable</span>
        </button>
    </div>

    <!-- Main Table Panel -->
    <div class="glass-card panel-box">
        <!-- Toolbar Bar -->
        <div class="managers-toolbar-bar">
            <div class="managers-search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="managersSearchInput" 
                    class="managers-search-input" 
                    placeholder="Buscar por responsable, empresa o cargo..."
                    onkeyup="filterManagersLive()"
                >
            </div>
        </div>

        <!-- Master Table -->
        <div class="table-responsive-box">
            <table class="modern-table" id="managersMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Responsable / Cargo</th>
                        <th>Empresa / Cliente</th>
                        <th>Regional</th>
                        <th>Proyectos</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($managers as $manager)
                        <tr>
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $manager['num'] ?? '-' }}
                            </td>

                            <!-- 2. Responsable / Cargo -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $manager['theme'] ?? 'cyan' }}">
                                        {{ $manager['initial'] ?? substr($manager['name'] ?? 'R', 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--ink);">{{ $manager['name'] ?? 'Responsable' }}</div>
                                        <div style="font-size: 11.5px; color: #64748b;">{{ $manager['position'] ?? 'Cargo' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Empresa / Cliente -->
                            <td>
                                <div class="client-pill-tag">
                                    <div class="client-initial-box {{ $manager['company_theme'] ?? 'cyan' }}" style="width: 28px; height: 28px; font-size: 11px; border-radius: 8px;">
                                        {{ $manager['company_initial'] ?? substr($manager['company'] ?? 'G', 0, 1) }}
                                    </div>
                                    <span style="font-weight: 600; color: var(--ink); font-size: 12.5px;">{{ $manager['company'] ?? 'General' }}</span>
                                </div>
                            </td>

                            <!-- 4. Regional Asignada -->
                            <td>
                                <span class="status-pill-badge in_progress" style="font-size: 11.5px;">
                                    {{ $manager['region'] ?? 'Central' }}
                                </span>
                            </td>

                            <!-- 5. Proyectos a Cargo -->
                            <td>
                                <span class="status-pill-badge done">
                                    {{ $manager['projects_count'] ?? '0 Proyectos' }}
                                </span>
                            </td>

                            <!-- 6. Contacto Directo -->
                            <td>
                                <div style="font-weight: 600; color: var(--ink-secondary); font-size: 12.5px;">{{ $manager['email'] ?? '—' }}</div>
                                <div style="font-size: 11px; color: #64748b; font-family: monospace;">{{ $manager['phone'] ?? '—' }}</div>
                            </td>

                            <!-- 7. Estado -->
                            <td>
                                @if(($manager['status'] ?? 'Activo') === 'Activo')
                                    <span class="status-pill-badge done">Activo</span>
                                @else
                                    <span class="status-pill-badge pending">Inactivo</span>
                                @endif
                            </td>

                            <!-- 8. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- 1. Reset Contraseña WhatsApp -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" 
                                            onclick="openResetPasswordModal('{{ $manager['id'] }}', '{{ addslashes($manager['name']) }}', '{{ $manager['email'] }}')" 
                                            title="Generar Acceso & Enviar por WhatsApp" aria-label="Resetear Contraseña">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </button>

                                    <!-- 2. Editar Responsable -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" 
                                            onclick="openEditManagerModal('{{ $manager['id'] }}', '{{ addslashes($manager['name']) }}', '{{ $manager['email'] }}', '{{ $manager['phone'] ?? '' }}', '{{ addslashes($manager['position']) }}', '{{ $manager['company_id'] ?? '' }}', '{{ $manager['status'] ?? 'Activo' }}')" 
                                            title="Editar Responsable" aria-label="Editar">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- 3. Eliminar Responsable -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" 
                                            onclick="deleteManager('{{ $manager['id'] }}', '{{ addslashes($manager['name']) }}')" 
                                            title="Eliminar Responsable" aria-label="Eliminar">
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
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 30px;">
                                No se encontraron responsables registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Hidden global form for SweetAlert delete -->
    <form id="deleteFormGlobal" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- ==========================================================================
         MODAL: ALTA DE NUEVO RESPONSABLE
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createManagerModal" onclick="if(event.target === this) closeModal('createManagerModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Alta de Responsable de Planta</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('createManagerModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form action="{{ route('managers.store') }}" method="POST">
                @csrf
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre y Apellidos</label>
                            <input type="text" name="name" class="custom-form-input" placeholder="Ej: Ing. Roberto Cáceres" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Empresa Cliente</label>
                            <select name="company_id" class="custom-form-select" required>
                                <option value="">Seleccionar empresa...</option>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Electrónico</label>
                            <input type="email" name="email" class="custom-form-input" placeholder="rcaceres@empresa.com" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono / WhatsApp</label>
                            <input type="text" name="phone" class="custom-form-input" placeholder="+591 715-00000">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Cargo / Responsabilidad</label>
                            <input type="text" name="position" class="custom-form-input" placeholder="Gerente de Medio Ambiente" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Estado de Acceso</label>
                            <select name="status" class="custom-form-select" required>
                                <option value="Activo" selected>Activo (Permitir Acceso)</option>
                                <option value="Inactivo">Inactivo (Bloquear Acceso)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('createManagerModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Responsable</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL: EDITAR RESPONSABLE
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editManagerModal" onclick="if(event.target === this) closeModal('editManagerModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Editar Responsable de Planta</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('editManagerModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="editManagerForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre y Apellidos</label>
                            <input type="text" id="editManagerName" name="name" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Empresa Asignada</label>
                            <select id="editManagerCompany" name="company_id" class="custom-form-select" required>
                                @foreach($companies as $comp)
                                    <option value="{{ $comp->id }}">{{ $comp->name }} ({{ $comp->code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Electrónico</label>
                            <input type="email" id="editManagerEmail" name="email" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono / WhatsApp</label>
                            <input type="text" id="editManagerPhone" name="phone" class="custom-form-input">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Cargo / Responsabilidad</label>
                            <input type="text" id="editManagerPosition" name="position" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Estado de la Cuenta</label>
                            <select id="editManagerStatus" name="status" class="custom-form-select" required>
                                <option value="Activo">Activo (Acceso Habilitado)</option>
                                <option value="Inactivo">Inactivo (Acceso Bloqueado)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('editManagerModal')">Cancelar</button>
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
                            <span id="resetModalInitial">R</span>
                        </div>
                        <div class="user-identity-modal-info">
                            <h4 id="resetModalName">Nombre del Responsable</h4>
                            <p id="resetModalEmail">responsable@empresa.com</p>
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
    @vite(['resources/js/managers.js'])
@endpush
