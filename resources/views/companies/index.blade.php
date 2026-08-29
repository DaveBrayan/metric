@extends('layouts.app')

@section('title', 'Empresas & Clientes — Metric v2 Pachabol')

@push('styles')
    @vite(['resources/css/companies.css'])
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="companies-header-banner">
        <div>
            <h1>Directorio de Empresas & Clientes</h1>
            <p>Monitoreo de convenios corporativos, sectores industriales y plantas vinculadas.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="openCreateCompanyModal()">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nueva Empresa</span>
        </button>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <!-- Toolbar Bar -->
        <div class="companies-toolbar-bar">
            <div class="companies-search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="companiesSearchInput" 
                    class="companies-search-input" 
                    placeholder="Buscar por empresa, código o sector..." 
                    onkeyup="filterCompaniesLive()"
                >
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="companiesMasterTable">
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
                                        <div style="font-size: 11.5px; color: #64748b;">{{ $company['code'] ?? 'COD' }}</div>
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
                                <div style="font-weight: 600; color: var(--ink-secondary);">{{ $company['contact_person'] ?? '—' }}</div>
                                <div style="font-size: 11px; color: #64748b;">{{ $company['email'] ?? '—' }}</div>
                            </td>

                            <!-- 6. Proyectos -->
                            <td>
                                <span class="status-pill-badge done">{{ $company['projects_count'] ?? '0 Proyectos' }}</span>
                            </td>

                            <!-- 7. Estado -->
                            <td>
                                @if(($company['status'] ?? 'Activo') === 'Activo')
                                    <span class="status-pill-badge done">Activo</span>
                                @else
                                    <span class="status-pill-badge pending">Inactivo</span>
                                @endif
                            </td>

                            <!-- 8. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Editar Empresa -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" 
                                            onclick="openEditCompanyModal('{{ $company['id'] }}', '{{ addslashes($company['name']) }}', '{{ addslashes($company['code']) }}', '{{ addslashes($company['industry']) }}', '{{ addslashes($company['contact_person'] ?? '') }}', '{{ $company['email'] ?? '' }}', '{{ $company['phone'] ?? '' }}', '{{ $company['status'] ?? 'Activo' }}')" 
                                            title="Editar Empresa" aria-label="Editar">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar Empresa -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" 
                                            onclick="deleteCompany('{{ $company['id'] }}', '{{ addslashes($company['name']) }}')" 
                                            title="Eliminar Empresa" aria-label="Eliminar">
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
                                No se encontraron empresas registradas.
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
         MODAL: ALTA DE NUEVA EMPRESA
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createCompanyModal" onclick="if(event.target === this) closeModal('createCompanyModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Alta de Empresa Cliente</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('createCompanyModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form action="{{ route('companies.store') }}" method="POST">
                @csrf
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre / Razón Social</label>
                            <input type="text" name="name" class="custom-form-input" placeholder="Ej: Minera San Cristóbal S.A." required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Código / Sigla</label>
                            <input type="text" name="code" class="custom-form-input" placeholder="MSC" required>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Sector Industrial</label>
                            <input type="text" name="industry" class="custom-form-input" placeholder="Minería & Metalurgia" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Contacto Principal</label>
                            <input type="text" name="contact_person" class="custom-form-input" placeholder="Ing. Roberto Cáceres">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Electrónico</label>
                            <input type="email" name="email" class="custom-form-input" placeholder="contacto@empresa.com">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono Corporativo</label>
                            <input type="text" name="phone" class="custom-form-input" placeholder="+591 2 2150000">
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Estado de la Cuenta</label>
                        <select name="status" class="custom-form-select" required>
                            <option value="Activo" selected>Activo (Servicio Habilitado)</option>
                            <option value="Inactivo">Inactivo (Servicio Suspendido)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('createCompanyModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Empresa</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL: EDITAR EMPRESA
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editCompanyModal" onclick="if(event.target === this) closeModal('editCompanyModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Editar Empresa Cliente</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('editCompanyModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="editCompanyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre / Razón Social</label>
                            <input type="text" id="editCompanyName" name="name" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Código / Sigla</label>
                            <input type="text" id="editCompanyCode" name="code" class="custom-form-input" required>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Sector Industrial</label>
                            <input type="text" id="editCompanyIndustry" name="industry" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Contacto Principal</label>
                            <input type="text" id="editCompanyContact" name="contact_person" class="custom-form-input">
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Correo Electrónico</label>
                            <input type="email" id="editCompanyEmail" name="email" class="custom-form-input">
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Teléfono Corporativo</label>
                            <input type="text" id="editCompanyPhone" name="phone" class="custom-form-input">
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Estado de la Cuenta</label>
                        <select id="editCompanyStatus" name="status" class="custom-form-select" required>
                            <option value="Activo">Activo (Servicio Habilitado)</option>
                            <option value="Inactivo">Inactivo (Servicio Suspendido)</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('editCompanyModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/companies.js'])
@endpush
