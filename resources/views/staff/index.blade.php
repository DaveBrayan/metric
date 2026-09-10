@extends('layouts.app')

@section('title', 'Personal Técnico & Colaboradores — Metric v2')

@push('styles')
    @vite(['resources/css/staff.css'])
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="staff-header-banner">
        <div>
            <h1>Personal Técnico & Colaboradores</h1>
            <p>Control de operadores de monitoreo industrial, dispositivos móviles y credenciales de acceso.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="openCreateStaffModal()" aria-label="Registrar nuevo personal">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nuevo Personal</span>
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
                    placeholder="Buscar por nombre, cargo o dispositivo..."
                    onkeyup="filterStaffLive()"
                    aria-label="Buscar colaborador"
                >
            </div>
        </div>

        <!-- Master Table: Solo Colaborador, Cargo, Dispositivo, Estado y Acciones -->
        <div class="table-responsive-box">
            <table class="modern-table staff-table" id="staffMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th>Colaborador</th>
                        <th>Correo Electrónico</th>
                        <th>Dispositivo</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr id="staff-row-{{ $member['id'] }}">
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px;">
                                {{ $member['num'] }}
                            </td>

                            <!-- 2. Colaborador (Nombre y Apellido) -->
                            <td>
                                <div class="staff-colaborador-cell">
                                    <div class="staff-avatar-initial">
                                        {{ $member['initial'] }}
                                    </div>
                                    <div>
                                        <div class="staff-name-title">{{ $member['name'] }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- 3. Correo Electrónico -->
                            <td>
                                <span class="staff-email-badge" style="font-family: 'Outfit', 'Inter', monospace; font-size: 13px; color: #334155; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#00b5e2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="20" height="16" x="2" y="4" rx="2"/>
                                        <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                                    </svg>
                                    <span>{{ $member['email'] }}</span>
                                </span>
                            </td>

                            <!-- 4. Dispositivo -->
                            <td>
                                @if($member['has_device'])
                                    <div class="device-badge-box" title="Dispositivo móvil vinculado">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#00b5e2" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
                                            <path d="M12 18h.01"/>
                                        </svg>
                                        <span>{{ $member['device_name'] }}</span>
                                    </div>
                                @else
                                    <span class="device-badge-empty" title="Sin dispositivo asignado">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/>
                                            <line x1="5" y1="2" x2="19" y2="22"/>
                                        </svg>
                                        <span>Sin vincular</span>
                                    </span>
                                @endif
                            </td>

                            <!-- 5. Estado (Activo / Inactivo) -->
                            <td>
                                @if($member['status'] === 'activo')
                                    <span class="status-pill-custom active">
                                        <span class="status-pulse-dot"></span>
                                        <span>Activo</span>
                                    </span>
                                @else
                                    <span class="status-pill-custom inactive">
                                        <span class="status-inactive-dot"></span>
                                        <span>Inactivo</span>
                                    </span>
                                @endif
                            </td>

                            <!-- 6. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- 1. Restablecer Contraseña & WhatsApp (pregunta primero) -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" 
                                            onclick="confirmResetPassword('{{ $member['id'] }}', '{{ addslashes($member['name']) }}')" 
                                            title="Restablecer Contraseña & Generar Acceso WhatsApp" aria-label="Restablecer Contraseña">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </button>

                                    <!-- 2. Editar Colaborador -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" 
                                            onclick="openEditStaffModal('{{ $member['id'] }}', '{{ addslashes($member['first_name']) }}', '{{ addslashes($member['last_name']) }}', '{{ addslashes($member['email']) }}', '{{ $member['status'] }}')" 
                                            title="Editar Colaborador" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- 3. Eliminar Colaborador -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" 
                                            onclick="deleteStaff('{{ $member['id'] }}', '{{ addslashes($member['name']) }}')" 
                                            title="Eliminar Colaborador" aria-label="Eliminar">
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
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 36px;">
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
         MODAL 1: ALTA DE NUEVO COLABORADOR
         Únicamente 4 campos: Nombre, Apellido, Cargo y Estado.
         Sin campo de dispositivo ni contraseña.
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createStaffModal" onclick="if(event.target === this) closeModal('createStaffModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Alta de Personal Técnico</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('createStaffModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="createStaffForm" action="{{ route('staff.store') }}" method="POST" onsubmit="handleCreateStaffSubmit(event)">
                @csrf
                <div class="modal-body-custom">
                    <!-- Banner Informativo -->
                    <div class="info-banner-subtle">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" style="flex-shrink: 0; margin-top: 1px;">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <span>La contraseña y usuario de acceso se generarán automáticamente por el sistema y se mostrarán al guardar listos para WhatsApp.</span>
                    </div>

                    <!-- Fila 1: Nombre y Apellido -->
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label" for="createStaffFirstName">Nombre *</label>
                            <input 
                                type="text" 
                                id="createStaffFirstName" 
                                name="first_name" 
                                class="custom-form-input" 
                                placeholder="Ej: Carlos" 
                                required
                            >
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label" for="createStaffLastName">Apellido *</label>
                            <input 
                                type="text" 
                                id="createStaffLastName" 
                                name="last_name" 
                                class="custom-form-input" 
                                placeholder="Ej: Mamani Ramos" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Fila 2: Correo Electrónico y Estado -->
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label" for="createStaffEmail">Correo Electrónico *</label>
                            <input 
                                type="email" 
                                id="createStaffEmail" 
                                name="email" 
                                class="custom-form-input" 
                                placeholder="Ej: carlos.mamani@metric.com" 
                                required
                            >
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label" for="createStaffStatus">Estado *</label>
                            <select id="createStaffStatus" name="status" class="custom-form-select" required>
                                <option value="activo" selected>Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('createStaffModal')">Cancelar</button>
                    <button type="submit" id="btnSubmitCreateStaff" class="btn-primary-hero-action">Guardar Personal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 2: EDITAR COLABORADOR
         Campos: Nombre, Apellido, Correo Electrónico y Estado.
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editStaffModal" onclick="if(event.target === this) closeModal('editStaffModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Editar Personal Técnico</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('editStaffModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="editStaffForm" method="POST" onsubmit="handleEditStaffSubmit(event)">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <!-- Fila 1: Nombre y Apellido -->
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label" for="editStaffFirstName">Nombre *</label>
                            <input 
                                type="text" 
                                id="editStaffFirstName" 
                                name="first_name" 
                                class="custom-form-input" 
                                required
                            >
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label" for="editStaffLastName">Apellido *</label>
                            <input 
                                type="text" 
                                id="editStaffLastName" 
                                name="last_name" 
                                class="custom-form-input" 
                                required
                            >
                        </div>
                    </div>

                    <!-- Fila 2: Correo Electrónico y Estado -->
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label" for="editStaffEmail">Correo Electrónico *</label>
                            <input 
                                type="email" 
                                id="editStaffEmail" 
                                name="email" 
                                class="custom-form-input" 
                                placeholder="colaborador@metric.com" 
                                required
                            >
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label" for="editStaffStatus">Estado *</label>
                            <select id="editStaffStatus" name="status" class="custom-form-select" required>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('editStaffModal')">Cancelar</button>
                    <button type="submit" id="btnSubmitEditStaff" class="btn-primary-hero-action">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 3: CREDENCIALES & WHATSAPP (DISEÑO EN BLANCO / CLARO)
         Fondo blanco, diseño limpio, alto contraste, sin fondos oscuros.
         Muestra correo, nueva contraseña generada y mensaje listo para WhatsApp.
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="credentialsWhatsAppModal" onclick="if(event.target === this) closeModal('credentialsWhatsAppModal')">
        <div class="modal-dialog-custom modal-dialog-light" style="max-width: 520px;">
            <div class="modal-header-custom modal-header-light">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div class="cred-header-icon-badge">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.3">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </div>
                    <div>
                        <h3 style="font-size: 18px; color: #0f172a; margin: 0;">Credenciales de Acceso</h3>
                        <p style="font-size: 12px; color: #64748b; margin: 2px 0 0;">Acceso generado listo para compartir por WhatsApp</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeModal('credentialsWhatsAppModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <div class="modal-body-custom modal-body-light">
                <!-- Identidad del Colaborador -->
                <div class="cred-identity-card-light">
                    <div class="staff-avatar-initial" id="credModalInitial" style="width: 44px; height: 44px; font-size: 16px;">
                        C
                    </div>
                    <div class="cred-identity-info">
                        <h4 id="credModalName" style="color: #0f172a;">Carlos Mamani Ramos</h4>
                        <p id="credModalPosition" style="color: #64748b;">Técnico de Campo</p>
                    </div>
                </div>

                <!-- Resumen de Datos: Usuario y Contraseña en Blanco / Claro -->
                <div class="cred-summary-grid">
                    <div class="cred-summary-box-light">
                        <span class="cred-summary-label">Usuario / Correo</span>
                        <div class="cred-summary-val-wrap">
                            <span class="cred-summary-val" id="credModalEmail">carlos.mamani@metric.com</span>
                            <button type="button" class="btn-copy-micro" onclick="copyToClipboard(document.getElementById('credModalEmail').textContent, 'Usuario copiado')" title="Copiar usuario" aria-label="Copiar usuario">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="cred-summary-box-light">
                        <span class="cred-summary-label">Nueva Contraseña</span>
                        <div class="cred-summary-val-wrap">
                            <span class="cred-summary-val" id="credModalPassword" style="color: #0284c7;">Metric2026*</span>
                            <button type="button" class="btn-copy-micro" onclick="copyToClipboard(document.getElementById('credModalPassword').textContent, 'Contraseña copiada')" title="Copiar contraseña" aria-label="Copiar contraseña">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                                    <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta WhatsApp en Modo Claro (Blanco con acento verde WhatsApp) -->
                <div class="whatsapp-message-card-light">
                    <div class="whatsapp-card-head-light">
                        <div class="whatsapp-head-title-light">
                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2">
                                <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                            </svg>
                            <span>Mensaje Listo para WhatsApp</span>
                        </div>
                        <span class="whatsapp-metric-tag">METRIC V2</span>
                    </div>

                    <!-- Caja de texto en blanco puro de alto contraste: Usar textarea con auto-selección al clic -->
                    <textarea 
                        id="credWhatsAppText" 
                        class="whatsapp-box-body-light" 
                        rows="7" 
                        readonly 
                        onclick="this.focus(); this.select();"
                        title="Haz clic para seleccionar todo el texto"
                    >Cargando credenciales...</textarea>

                    <!-- Botón de WhatsApp verde accesible con texto blanco -->
                    <button type="button" id="btnCopyWhatsAppCred" class="btn-whatsapp-copy-light" onclick="copyWhatsAppMessage()">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                            <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                        </svg>
                        <span id="btnCopyWhatsAppText">Copiar Texto para WhatsApp</span>
                    </button>
                </div>
            </div>

            <div class="modal-footer-custom modal-footer-light">
                <button type="button" class="btn-primary-hero-action" onclick="closeModal('credentialsWhatsAppModal')">Entendido / Cerrar</button>
            </div>
        </div>
    </div>

    @if(session('created_credentials'))
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                if (typeof window.openCredentialsModal === 'function') {
                    window.openCredentialsModal(@json(session('created_credentials')));
                }
            });
        </script>
    @endif
@endsection

@push('scripts')
    @vite(['resources/js/staff.js'])
@endpush
