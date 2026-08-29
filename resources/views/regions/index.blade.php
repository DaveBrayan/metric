@extends('layouts.app')

@section('title', 'Sedes Regionales & Plantas — Metric v2 Pachabol')

@push('styles')
    @vite(['resources/css/regions.css'])
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="regions-header-banner">
        <div>
            <h1>Sedes Regionales & Plantas Operativas</h1>
            <p>Supervisión geográfica descentralizada, directores de sede y proyectos asignados.</p>
        </div>
        <button type="button" class="btn-primary-hero-action" onclick="openCreateRegionModal()">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            <span>Nueva Regional</span>
        </button>
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <!-- Toolbar Bar -->
        <div class="regions-toolbar-bar">
            <div class="regions-search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="regionsSearchInput" 
                    class="regions-search-input" 
                    placeholder="Buscar por regional, código o encargado..." 
                    onkeyup="filterRegionsLive()"
                >
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
                                        {{ $region['initial'] ?? substr($region['code'] ?? 'REG', 0, 2) }}
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
                                {{ $region['address'] ?? '—' }}
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
                                @if(($region['status'] ?? 'Operativo') === 'Operativo')
                                    <span class="status-pill-badge done">Operativo</span>
                                @elseif(($region['status'] ?? '') === 'Mantenimiento')
                                    <span class="status-pill-badge pending">Mantenimiento</span>
                                @else
                                    <span class="status-pill-badge in_progress">{{ $region['status'] ?? 'Inactivo' }}</span>
                                @endif
                            </td>

                            <!-- 8. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Editar Sede -->
                                    <button type="button" class="btn-admin-icon-action theme-lime" 
                                            onclick="openEditRegionModal('{{ $region['id'] }}', '{{ addslashes($region['name']) }}', '{{ addslashes($region['code']) }}', '{{ addslashes($region['department'] ?? '') }}', '{{ addslashes($region['manager']) }}', '{{ addslashes($region['address'] ?? '') }}', '{{ $region['status'] ?? 'Operativo' }}')" 
                                            title="Editar Regional" aria-label="Editar">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar Sede -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" 
                                            onclick="deleteRegion('{{ $region['id'] }}', '{{ addslashes($region['name']) }}')" 
                                            title="Eliminar Regional" aria-label="Eliminar">
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
                                No se encontraron sedes regionales registradas.
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
         MODAL: ALTA DE NUEVA REGIONAL
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createRegionModal" onclick="if(event.target === this) closeModal('createRegionModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Alta de Sede Regional</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('createRegionModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form action="{{ route('regions.store') }}" method="POST">
                @csrf
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre de Sede</label>
                            <input type="text" name="name" class="custom-form-input" placeholder="Ej: Regional La Paz" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Código / Sigla</label>
                            <input type="text" name="code" class="custom-form-input" placeholder="LPZ" required>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Departamento</label>
                            <input type="text" name="department" class="custom-form-input" placeholder="La Paz" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Director / Encargado</label>
                            <input type="text" name="manager_name" class="custom-form-input" placeholder="Ing. Reynaldo Sirpa">
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Dirección Operativa</label>
                        <input type="text" name="address" class="custom-form-input" placeholder="Av. 6 de Agosto #2450">
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Estado Operativo</label>
                        <select name="status" class="custom-form-select" required>
                            <option value="Operativo" selected>Operativo (En Operación Normal)</option>
                            <option value="Mantenimiento">En Mantenimiento</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('createRegionModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Regional</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL: EDITAR REGIONAL
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editRegionModal" onclick="if(event.target === this) closeModal('editRegionModal')">
        <div class="modal-dialog-custom">
            <div class="modal-header-custom">
                <h3>Editar Sede Regional</h3>
                <button type="button" class="btn-close-modal" onclick="closeModal('editRegionModal')" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="editRegionForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Nombre de Sede</label>
                            <input type="text" id="editRegionName" name="name" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Código / Sigla</label>
                            <input type="text" id="editRegionCode" name="code" class="custom-form-input" required>
                        </div>
                    </div>

                    <div class="form-row-grid">
                        <div class="form-field-group">
                            <label class="form-field-label">Departamento</label>
                            <input type="text" id="editRegionDepartment" name="department" class="custom-form-input" required>
                        </div>
                        <div class="form-field-group">
                            <label class="form-field-label">Director / Encargado</label>
                            <input type="text" id="editRegionManager" name="manager_name" class="custom-form-input">
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Dirección Operativa</label>
                        <input type="text" id="editRegionAddress" name="address" class="custom-form-input">
                    </div>

                    <div class="form-field-group">
                        <label class="form-field-label">Estado Operativo</label>
                        <select id="editRegionStatus" name="status" class="custom-form-select" required>
                            <option value="Operativo">Operativo (En Operación Normal)</option>
                            <option value="Mantenimiento">En Mantenimiento</option>
                            <option value="Inactivo">Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeModal('editRegionModal')">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite(['resources/js/regions.js'])
@endpush
