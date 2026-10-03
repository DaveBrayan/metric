@extends('layouts.app')

@section('title', 'Proyectos Industriales en Ejecución — Metric v2 Pachabol')

@push('styles')
<style>
/* ==========================================================================
   PROJECTS MODULE STYLES — SELF-CONTAINED (No requiere npm run build)
   ========================================================================== */
.projects-header-banner {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.projects-header-banner h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.6px;
    color: var(--ink);
    margin-bottom: 6px;
}

.projects-header-banner p {
    color: #475569;
    font-size: 14px;
    font-weight: 500;
}

/* Toolbar & Filters */
.projects-toolbar-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.projects-filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.projects-filter-pill {
    background: #ffffff;
    border: 1px solid rgba(203, 213, 225, 0.85);
    border-radius: var(--radius-full);
    padding: 7px 16px;
    font-size: 12.5px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
}

.projects-filter-pill:hover {
    border-color: var(--cyan);
    color: var(--ink);
}

.projects-filter-pill.active {
    background: var(--ink);
    border-color: var(--ink);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(15, 28, 46, 0.2);
}

.projects-search-box {
    position: relative;
    width: min(340px, 100%);
}

.projects-search-input {
    width: 100%;
    height: 40px;
    background: #ffffff;
    border: 1px solid rgba(203, 213, 225, 0.95);
    border-radius: var(--radius-full);
    padding: 0 16px 0 40px;
    font-size: 13px;
    color: var(--ink);
    outline: none;
    transition: all 0.2s ease;
}

.projects-search-input:focus {
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(16, 185, 223, 0.18);
}

.projects-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

/* Projects Table Specifics (Columnas fijas para consistencia entre hojas) */
table#projectsFullMasterTable {
    table-layout: fixed !important;
    width: 100% !important;
    min-width: 1080px;
    border-collapse: separate;
    border-spacing: 0;
}

table#projectsFullMasterTable th {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

table#projectsFullMasterTable td {
    vertical-align: middle;
    overflow: hidden;
}

.project-desc-cell {
    font-size: 12.5px;
    color: #475569;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
}

.date-created-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 4px 10px;
    border-radius: 8px;
    white-space: nowrap;
}

.project-company-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 600;
    color: #0369a1;
    background: rgba(14, 165, 233, 0.1);
    border: 1px solid rgba(14, 165, 233, 0.2);
    padding: 2px 8px;
    border-radius: 6px;
    white-space: nowrap;
    max-width: 100%;
    overflow: hidden;
    text-overflow: ellipsis;
}

.project-manager-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    color: #475569;
    font-weight: 500;
    white-space: nowrap;
}

/* ==========================================================================
   MODAL DIALOGS & BACKDROPS (Garantizado: NUNCA se renderiza fuera de lugar)
   ========================================================================== */
.modal-backdrop-custom {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(15, 28, 46, 0.68) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    z-index: 9999 !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 20px !important;
}

.modal-backdrop-custom.open {
    display: flex !important;
}

.modal-dialog-projects {
    width: 100%;
    max-width: 760px;
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.95);
    border-radius: 20px;
    box-shadow: 0 35px 80px -15px rgba(15, 28, 46, 0.38);
    overflow: hidden;
    animation: scaleInModal 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes scaleInModal {
    0% { transform: scale(0.94) translateY(12px); opacity: 0; }
    100% { transform: scale(1) translateY(0); opacity: 1; }
}

.modal-header-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 26px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.modal-header-title h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 19px;
    font-weight: 800;
    color: var(--ink);
    margin: 0 0 3px 0;
}

.modal-header-title p {
    font-size: 13px;
    color: #64748b;
    margin: 0;
}

.btn-close-modal {
    border: 0;
    background: #f1f5f9;
    width: 34px;
    height: 34px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    color: #64748b;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s ease;
}

.btn-close-modal:hover {
    background: #e2e8f0;
    color: #0f1c2e;
}

.modal-body-custom {
    padding: 24px 26px;
    max-height: 74vh;
    overflow-y: auto;
    background: #ffffff;
}

.modal-footer-custom {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding: 16px 26px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

/* Form Controls */
.form-row-grid-2col {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

@media (max-width: 600px) {
    .form-row-grid-2col {
        grid-template-columns: 1fr;
    }
}

.form-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-bottom: 16px;
}

.form-field-label {
    font-size: 12.5px;
    font-weight: 700;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 4px;
}

.form-field-label .req {
    color: #ef4444;
}

.custom-form-input {
    width: 100%;
    height: 42px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 0 14px;
    font-size: 13.5px;
    color: #0f1c2e;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.custom-form-input:focus {
    border-color: #10b9df;
    box-shadow: 0 0 0 3.5px rgba(16, 185, 223, 0.18);
}

textarea.custom-form-input {
    height: auto;
    padding: 10px 14px;
    resize: vertical;
    font-family: inherit;
    line-height: 1.45;
}

.custom-form-select {
    width: 100%;
    height: 42px;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center;
    background-size: 16px;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 0 38px 0 14px;
    font-size: 13.5px;
    color: #0f1c2e;
    outline: none;
    cursor: pointer;
    appearance: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.custom-form-select:focus {
    border-color: #10b9df;
    box-shadow: 0 0 0 3.5px rgba(16, 185, 223, 0.18);
}

.readonly-field {
    background: #f8fafc !important;
    color: #1e293b !important;
    font-weight: 600 !important;
    border-color: #cbd5e1 !important;
    cursor: default !important;
}

.btn-subtle-link {
    background: transparent;
    border: 0;
    color: #64748b;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    padding: 9px 18px;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.btn-subtle-link:hover {
    color: #0f172a;
    background: #e2e8f0;
}

.btn-primary-hero-action {
    background: linear-gradient(135deg, #10b9df 0%, #0799a7 100%);
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    padding: 10px 24px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    box-shadow: 0 6px 18px -3px rgba(16, 185, 223, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
}

.btn-primary-hero-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px -3px rgba(16, 185, 223, 0.55);
}

/* Pagination bar */
.projects-pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
    border-radius: 0 0 16px 16px;
    flex-wrap: wrap;
    gap: 12px;
}

.projects-pagination-info {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.projects-pagination-nav {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-page-step {
    min-width: 32px;
    height: 32px;
    padding: 0 8px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #475569;
    font-size: 12.5px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-page-step:hover:not(:disabled) {
    border-color: #10b9df;
    color: #0f1c2e;
    background: #f0fdfa;
}

.btn-page-step.active {
    background: #0f1c2e;
    border-color: #0f1c2e;
    color: #ffffff;
    font-weight: 700;
}

.btn-page-step:disabled {
    opacity: 0.4;
    cursor: not-allowed;
}
</style>
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="projects-header-banner">
        <div>
            <h1>Proyectos Industriales en Ejecución</h1>
            <p>Monitoreo integral de avances, módulos de medición ambiental vinculados y sedes asignadas.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn-primary-hero-action" onclick="openCreateProjectModal()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Nuevo Proyecto</span>
            </button>
        </div>
    </div>

    <!-- Feedback Alerts -->
    @if(session('success'))
        <div style="margin-bottom: 20px; padding: 14px 18px; background: #f0fdf4; border: 1.5px solid #86efac; border-radius: 12px; color: #15803d; font-size: 13.5px; font-weight: 600; display: flex; align-items: center; gap: 10px; box-shadow: 0 4px 12px rgba(34, 197, 94, 0.1);">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div style="margin-bottom: 20px; padding: 14px 18px; background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 12px; color: #b91c1c; font-size: 13.5px; font-weight: 600;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>Por favor corrige los siguientes errores:</span>
            </div>
            <ul style="margin: 0 0 0 24px; padding: 0; font-size: 13px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <div class="projects-toolbar-bar">
            <!-- Filter Pills -->
            <div class="projects-filter-group">
                <button type="button" class="projects-filter-pill active" onclick="filterProjectsByStatus('all', this)">
                    Todos ({{ count($projects) }})
                </button>
                <button type="button" class="projects-filter-pill" onclick="filterProjectsByStatus('Planificación', this)">
                    Planificación
                </button>
                <button type="button" class="projects-filter-pill" onclick="filterProjectsByStatus('En Ejecución', this)">
                    En Ejecución
                </button>
                <button type="button" class="projects-filter-pill" onclick="filterProjectsByStatus('Completado', this)">
                    Completado
                </button>
                <button type="button" class="projects-filter-pill" onclick="filterProjectsByStatus('En Pausa', this)">
                    En Pausa
                </button>
            </div>

            <!-- Search input -->
            <div class="projects-search-box">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="projects-search-icon">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="projectsListSearchInput" 
                    class="projects-search-input" 
                    placeholder="Buscar por proyecto, cliente, responsable..." 
                    onkeyup="onProjectSearchKeyup()"
                >
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="projectsFullMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px;">#</th>
                        <th style="width: 155px;">Código</th>
                        <th style="width: 260px;">Proyecto</th>
                        <th style="width: 240px;">Descripción</th>
                        <th style="width: 160px;">Módulos Completados</th>
                        <th style="width: 120px;">Estado</th>
                        <th style="width: 130px;">Fecha de Inicio</th>
                        <th style="width: 110px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="projectsTableBody">
                    @forelse($projects as $project)
                        <tr class="project-data-row" 
                            data-status="{{ $project['status'] }}"
                            data-search="{{ strtolower($project['name'] . ' ' . $project['code'] . ' ' . $project['client'] . ' ' . $project['manager_name'] . ' ' . $project['description']) }}">
                            
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px;">
                                {{ $project['num'] }}
                            </td>

                            <!-- 2. Código Identificador -->
                            <td>
                                <span style="display: inline-block; font-size: 12px; color: #0284c7; font-family: monospace; font-weight: 800; background: rgba(14, 165, 233, 0.1); padding: 4px 8px; border-radius: 6px; border: 1px solid rgba(14, 165, 233, 0.25); letter-spacing: 0.3px;">
                                    {{ $project['code'] }}
                                </span>
                            </td>

                            <!-- 3. Proyecto (Nombre, Empresa y Responsable) -->
                            <td>
                                <!-- Nombre del Proyecto -->
                                <div style="font-weight: 700; color: var(--ink); font-size: 13.5px; margin-bottom: 4px; line-height: 1.3;">
                                    {{ $project['name'] }}
                                </div>

                                <!-- Empresa -->
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 5px;">
                                    <span class="project-company-tag" title="Empresa: {{ $project['client'] }}">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"/>
                                            <line x1="9" y1="22" x2="9" y2="22"/>
                                            <line x1="8" y1="6" x2="8.01" y2="6"/>
                                            <line x1="16" y1="6" x2="16.01" y2="6"/>
                                            <line x1="12" y1="6" x2="12.01" y2="6"/>
                                            <line x1="12" y1="10" x2="12.01" y2="10"/>
                                            <line x1="12" y1="14" x2="12.01" y2="14"/>
                                            <line x1="16" y1="10" x2="16.01" y2="10"/>
                                            <line x1="16" y1="14" x2="16.01" y2="14"/>
                                            <line x1="8" y1="10" x2="8.01" y2="10"/>
                                            <line x1="8" y1="14" x2="8.01" y2="14"/>
                                        </svg>
                                        <span>{{ $project['client'] }}</span>
                                    </span>
                                </div>

                                <!-- Responsable Asignado -->
                                <div class="project-manager-tag" title="Responsable Asignado">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    <span style="color: #64748b; font-size: 11px;">Resp:</span>
                                    <span style="font-weight: 600; color: #1e293b; font-size: 11.5px;">{{ $project['manager_name'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Descripción -->
                            <td>
                                <div class="project-desc-cell" title="{{ $project['description'] }}">
                                    {{ $project['description'] }}
                                </div>
                            </td>

                            <!-- 5. Módulos Completados -->
                            <td>
                                <div class="points-ratio-box">
                                    <div class="points-ratio-text">
                                        <span>{{ $project['modules_completed_text'] }}</span>
                                        <span>{{ $project['modules_ratio_pct'] }}%</span>
                                    </div>
                                    <div class="points-ratio-track">
                                        <div class="points-ratio-fill {{ $project['modules_ratio_pct'] == 100 ? 'lime' : 'cyan' }}" style="width: {{ $project['modules_ratio_pct'] }}%;"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- 6. Estado -->
                            <td>
                                <span class="status-pill-badge {{ $project['status_type'] ?? 'in_progress' }}">
                                    {{ $project['status'] }}
                                </span>
                            </td>

                            <!-- 7. Fecha de Inicio de Proyecto -->
                            <td>
                                <div class="date-created-badge" title="Fecha de Inicio del Proyecto">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span>{{ $project['start_date_formatted'] }}</span>
                                </div>
                            </td>

                            <!-- 8. Acciones -->
                            <td>
                                <div class="admin-actions-cell">
                                    <!-- Monitoreo en Tiempo Real (Live Radar) -->
                                    <button type="button" class="btn-admin-icon-action theme-live" onclick="window.location.href='{{ route('projects.live_monitoring', $project['id']) }}'" title="Monitoreo en Tiempo Real (Radar GPS y Mediciones en Vivo)" aria-label="Monitoreo en Tiempo Real">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/>
                                            <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/>
                                            <path d="M2 12h20"/>
                                        </svg>
                                    </button>

                                    <!-- Ver Módulos del Proyecto -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick="window.location.href='{{ route('modules.index', ['proyecto' => $project['id']]) }}'" title="Ver Módulos de Medición de este Proyecto" aria-label="Ver Módulos">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polygon points="12 2 2 7 12 12 22 7 12 2"/>
                                            <polyline points="2 17 12 22 22 17"/>
                                            <polyline points="2 12 12 17 22 12"/>
                                        </svg>
                                    </button>

                                    <!-- Editar Proyecto -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" 
                                            onclick='openEditProjectModal(@json($project))' 
                                            title="Editar Proyecto" 
                                            aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar Proyecto -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" 
                                            onclick="confirmDeleteProject('{{ $project['id'] }}', '{{ addslashes($project['name']) }}')" 
                                            title="Eliminar Proyecto" 
                                            aria-label="Eliminar">
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
                        <tr id="emptyTableRow">
                            <td colspan="8" style="text-align: center; color: #64748b; padding: 36px;">
                                No se encontraron proyectos registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="8" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron proyectos que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls (10 por página) -->
        <div class="projects-pagination-container" id="projectsPaginationBar">
            <div class="projects-pagination-info" id="projectsPaginationInfo">
                Mostrando <span id="pagStart">0</span> a <span id="pagEnd">0</span> de <span id="pagTotal">0</span> proyectos
            </div>
            <div class="projects-pagination-nav" id="projectsPaginationNav">
                <!-- Generado por JavaScript -->
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 1: NUEVO PROYECTO
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createProjectModal" role="dialog" aria-modal="true" aria-labelledby="createModalTitle">
        <div class="modal-dialog-projects">
            <!-- Modal Header -->
            <div class="modal-header-custom">
                <div class="modal-header-title">
                    <h2 id="createModalTitle">Registrar Nuevo Proyecto Industrial</h2>
                    <p>Ingresa los detalles del proyecto y la empresa cliente correspondiente.</p>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateProjectModal()" aria-label="Cerrar modal">
                    ✕
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('projects.store') }}" method="POST" id="createProjectForm">
                @csrf
                <div class="modal-body-custom">
                    <!-- 1. NOMBRE DEL PROYECTO (Primero) -->
                    <div class="form-field-group">
                        <label class="form-field-label" for="create_project_name">
                            Nombre del Proyecto <span class="req">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="create_project_name" 
                            class="custom-form-input" 
                            placeholder="Lugar o Empresa donde se realiza el proyecto (ej. Planta Papelbol Villa Tunari)" 
                            required
                        >
                    </div>

                    <!-- 2. DESCRIPCIÓN DEL PROYECTO (Segundo) -->
                    <div class="form-field-group">
                        <label class="form-field-label" for="create_description">
                            Descripción del Proyecto
                        </label>
                        <textarea 
                            name="description" 
                            id="create_description" 
                            class="custom-form-input" 
                            rows="2" 
                            placeholder="Describe los objetivos, alcance ambiental, puntos de monitoreo o especificaciones del proyecto..."
                        ></textarea>
                    </div>

                    <!-- 2.1 RAZÓN SOCIAL & DIRECCIÓN DE LA EMPRESA -->
                    <div class="form-row-grid-2col">
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_razon_social">
                                Razón Social
                            </label>
                            <input 
                                type="text" 
                                name="razon_social" 
                                id="create_razon_social" 
                                class="custom-form-input" 
                                placeholder="Nombre o Razón Social legal de la empresa"
                            >
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label" for="create_direccion">
                                Dirección de la empresa o establecimiento laboral
                            </label>
                            <input 
                                type="text" 
                                name="direccion" 
                                id="create_direccion" 
                                class="custom-form-input" 
                                placeholder="Dirección completa del establecimiento laboral"
                            >
                        </div>
                    </div>

                    <!-- 3. EMPRESA & RESPONSABLE (Auto-trae el responsable) -->
                    <div class="form-row-grid-2col">
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_company_id">
                                Empresa / Cliente <span class="req">*</span>
                            </label>
                            <select name="company_id" id="create_company_id" class="custom-form-select" onchange="onCompanySelectChange(this.value)" required>
                                <option value="">-- Seleccionar Empresa --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" data-sigla="{{ strtoupper($company->code) }}" data-legal="{{ $company->legal_name ?: $company->name }}" data-address="{{ $company->address ?? '' }}">
                                        {{ $company->name }} ({{ strtoupper($company->code) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label" for="create_manager_name_display">
                                Responsable a Cargo
                            </label>
                            <input 
                                type="text" 
                                id="create_manager_name_display" 
                                class="custom-form-input readonly-field" 
                                readonly 
                                placeholder="Selecciona una empresa para cargar el responsable..."
                            >
                            <input type="hidden" name="manager_id" id="create_manager_id">
                        </div>
                    </div>

                    <!-- 4. SIGLA DE PROYECTO & FECHA DE INICIO DE PROYECTO -->
                    <div class="form-row-grid-2col">
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_project_sigla">
                                Sigla del Proyecto (Máx. 3 caracteres) <span class="req">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="project_sigla" 
                                id="create_project_sigla" 
                                class="custom-form-input" 
                                value="PRJ" 
                                maxlength="3"
                                style="text-transform: uppercase; font-family: monospace; font-weight: 700;"
                                oninput="this.value = this.value.toUpperCase(); updateAutoProjectCode();"
                                required
                            >
                            <small style="color: #64748b; font-size: 11px;">Abreviatura de 3 caracteres (ej. PRJ, AIR, GAS, AGU).</small>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label" for="create_start_date">
                                Fecha de Inicio de Proyecto <span class="req">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="start_date" 
                                id="create_start_date" 
                                class="custom-form-input" 
                                value="{{ date('Y-m-d') }}" 
                                onchange="updateAutoProjectCode()"
                                required
                            >
                        </div>
                    </div>

                    <!-- 5. CÓDIGO IDENTIFICADOR & ESTADO -->
                    <div class="form-row-grid-2col">
                        <div class="form-field-group">
                            <label class="form-field-label" for="create_project_code">
                                Código Identificador
                            </label>
                            <div style="position: relative;">
                                <input 
                                    type="text" 
                                    name="code" 
                                    id="create_project_code" 
                                    class="custom-form-input" 
                                    readonly 
                                    style="background: #f8fafc; font-family: monospace; font-weight: 800; color: #0284c7; letter-spacing: 0.5px; border-color: #38bdf8; padding-right: 75px;"
                                >
                                <div style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 10.5px; font-weight: 700; color: #0284c7; background: rgba(14, 165, 233, 0.12); padding: 2px 7px; border-radius: 6px;">
                                    Auto
                                </div>
                            </div>
                            <small style="color: #64748b; font-size: 11px;">Fórmula: [EMPRESA]-[PROYECTO]-[MES]-[AÑO]</small>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label" for="create_status">
                                Estado Inicial <span class="req">*</span>
                            </label>
                            <select name="status" id="create_status" class="custom-form-select" required>
                                <option value="Planificación" selected>Planificación</option>
                                <option value="En Ejecución">En Ejecución</option>
                                <option value="Completado">Completado</option>
                                <option value="En Pausa">En Pausa</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeCreateProjectModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Proyecto</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 2: EDITAR PROYECTO
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editProjectModal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle">
        <div class="modal-dialog-projects">
            <!-- Modal Header -->
            <div class="modal-header-custom">
                <div class="modal-header-title">
                    <h2 id="editModalTitle">Editar Proyecto Industrial</h2>
                    <p>Actualiza nombre, descripción, empresa, responsable o fecha de inicio.</p>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeEditProjectModal()" aria-label="Cerrar modal">
                    ✕
                </button>
            </div>

            <!-- Modal Form -->
            <form action="" method="POST" id="editProjectForm">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <!-- 1. Nombre del Proyecto -->
                    <div class="form-field-group">
                        <label class="form-field-label" for="edit_project_name">
                            Nombre del Proyecto <span class="req">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="name" 
                            id="edit_project_name" 
                            class="custom-form-input" 
                            placeholder="Lugar o Empresa donde se realiza el proyecto (ej. Planta Papelbol Villa Tunari)"
                            required
                        >
                    </div>

                    <!-- 2. Descripción del Proyecto -->
                    <div class="form-field-group">
                        <label class="form-field-label" for="edit_description">
                            Descripción del Proyecto
                        </label>
                        <textarea 
                            name="description" 
                            id="edit_description" 
                            class="custom-form-input" 
                            rows="2" 
                            placeholder="Describe los objetivos, alcance ambiental o especificaciones..."
                        ></textarea>
                    </div>

                    <!-- 2.1 Razón Social & Dirección -->
                    <div class="form-row-grid-2col">
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_razon_social">
                                Razón Social
                            </label>
                            <input 
                                type="text" 
                                name="razon_social" 
                                id="edit_razon_social" 
                                class="custom-form-input" 
                                placeholder="Nombre o Razón Social legal de la empresa"
                            >
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_direccion">
                                Dirección de la empresa o establecimiento laboral
                            </label>
                            <input 
                                type="text" 
                                name="direccion" 
                                id="edit_direccion" 
                                class="custom-form-input" 
                                placeholder="Dirección completa del establecimiento laboral"
                            >
                        </div>
                    </div>

                    <!-- 3. Empresa & Responsable -->
                    <div class="form-row-grid-2col">
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_company_id">
                                Empresa / Cliente <span class="req">*</span>
                            </label>
                            <select name="company_id" id="edit_company_id" class="custom-form-select" onchange="onEditCompanySelectChange(this.value)" required>
                                <option value="">-- Seleccionar Empresa --</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" data-sigla="{{ strtoupper($company->code) }}" data-legal="{{ $company->legal_name ?: $company->name }}" data-address="{{ $company->address ?? '' }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_manager_name_display">
                                Responsable a Cargo
                            </label>
                            <input 
                                type="text" 
                                id="edit_manager_name_display" 
                                class="custom-form-input readonly-field" 
                                readonly 
                                placeholder="Selecciona una empresa para cargar el responsable..."
                            >
                            <input type="hidden" name="manager_id" id="edit_manager_id">
                        </div>
                    </div>

                    <!-- 4. Fecha de Inicio de Proyecto & Código -->
                    <div class="form-row-grid-2col">
                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_start_date">
                                Fecha de Inicio de Proyecto <span class="req">*</span>
                            </label>
                            <input 
                                type="date" 
                                name="start_date" 
                                id="edit_start_date" 
                                class="custom-form-input" 
                                required
                            >
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label" for="edit_project_code">
                                Código Identificador <span class="req">*</span>
                            </label>
                            <input 
                                type="text" 
                                name="code" 
                                id="edit_project_code" 
                                class="custom-form-input" 
                                style="text-transform: uppercase; font-family: monospace; font-weight: 700;"
                                required
                            >
                        </div>
                    </div>

                    <!-- 5. Estado -->
                    <div class="form-field-group" style="margin-bottom: 0;">
                        <label class="form-field-label" for="edit_status">
                            Estado del Proyecto <span class="req">*</span>
                        </label>
                        <select name="status" id="edit_status" class="custom-form-select" required>
                            <option value="Planificación">Planificación</option>
                            <option value="En Ejecución">En Ejecución</option>
                            <option value="Completado">Completado</option>
                            <option value="En Pausa">En Pausa</option>
                        </select>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeEditProjectModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteProjectForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
/* ==========================================================================
   PROJECTS JAVASCRIPT LOGIC (Paginación 10x, Filtros, Modales & Código Auto)
   ========================================================================== */
let allProjectRows = [];
let filteredProjectRows = [];
let currentPage = 1;
const pageSize = 10;
let currentStatusFilter = 'all';
let currentSearchTerm = '';

document.addEventListener('DOMContentLoaded', () => {
    allProjectRows = Array.from(document.querySelectorAll('.project-data-row'));
    applyProjectsFilter();

    // Close modals on click outside
    ['createProjectModal', 'editProjectModal'].forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('open');
                }
            });
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeCreateProjectModal();
            closeEditProjectModal();
        }
    });
});

/* ==========================================================================
   CODIFICACIÓN AUTOMÁTICA DEL PROYECTO: [SIGLA_EMPRESA]-[SIGLA_PROYECTO]-[MM]-[YY]
   ========================================================================== */
function updateAutoProjectCode() {
    const companySelect = document.getElementById('create_company_id');
    let compSigla = 'EMP';
    if (companySelect && companySelect.selectedIndex > 0) {
        const opt = companySelect.options[companySelect.selectedIndex];
        compSigla = (opt.getAttribute('data-sigla') || 'EMP').toUpperCase().trim().substring(0, 3);
    }

    const siglaInput = document.getElementById('create_project_sigla');
    let projSigla = siglaInput ? siglaInput.value.trim().toUpperCase().substring(0, 3) : 'PRJ';
    if (!projSigla) projSigla = 'PRJ';

    const dateInput = document.getElementById('create_start_date');
    let mm = '09';
    let yy = '26';
    if (dateInput && dateInput.value) {
        const parts = dateInput.value.split('-'); // YYYY-MM-DD
        if (parts.length === 3) {
            yy = parts[0].slice(-2);
            mm = parts[1];
        }
    }

    const autoCode = `${compSigla}-${projSigla}-${mm}-${yy}`;
    const codeInput = document.getElementById('create_project_code');
    if (codeInput) {
        codeInput.value = autoCode;
    }
}

const managersList = @json($managers);

function updateManagerForCompany(companyId, displayInputId, hiddenInputId) {
    const displayInput = document.getElementById(displayInputId);
    const hiddenInput = document.getElementById(hiddenInputId);
    if (!displayInput || !hiddenInput) return;

    if (!companyId) {
        displayInput.value = '';
        displayInput.placeholder = 'Selecciona una empresa para cargar el responsable...';
        hiddenInput.value = '';
        return;
    }

    const mgr = managersList.find(m => m.company_id == companyId);
    if (mgr) {
        const pos = mgr.position ? ` (${mgr.position})` : '';
        displayInput.value = `${mgr.name}${pos}`;
        hiddenInput.value = mgr.id;
    } else {
        displayInput.value = 'Sin responsable registrado';
        hiddenInput.value = '';
    }
}

function onCompanySelectChange(companyId) {
    // 1. Auto-cargar Responsable asignado a esta empresa en campo solo lectura
    updateManagerForCompany(companyId, 'create_manager_name_display', 'create_manager_id');

    // 2. Auto-sugerir Razón Social y Dirección si están vacías
    const companySelect = document.getElementById('create_company_id');
    if (companySelect && companySelect.selectedIndex > 0) {
        const opt = companySelect.options[companySelect.selectedIndex];
        const legal = opt.getAttribute('data-legal') || '';
        const address = opt.getAttribute('data-address') || '';
        const razonInput = document.getElementById('create_razon_social');
        const dirInput = document.getElementById('create_direccion');
        if (razonInput && (!razonInput.value || razonInput.getAttribute('data-autofilled') === 'true')) {
            razonInput.value = legal;
            razonInput.setAttribute('data-autofilled', 'true');
        }
        if (dirInput && (!dirInput.value || dirInput.getAttribute('data-autofilled') === 'true')) {
            dirInput.value = address;
            dirInput.setAttribute('data-autofilled', 'true');
        }
    }

    // 3. Recalcular código de proyecto con la nueva sigla de empresa
    updateAutoProjectCode();
}

function onEditCompanySelectChange(companyId) {
    // Auto-cargar Responsable asignado a la nueva empresa en modal de edición
    updateManagerForCompany(companyId, 'edit_manager_name_display', 'edit_manager_id');
}

/* Modal Controls */
function openCreateProjectModal() {
    const modal = document.getElementById('createProjectModal');
    if (modal) {
        modal.classList.add('open');

        // Seleccionar la primera empresa disponible si no hay una seleccionada
        const companySelect = document.getElementById('create_company_id');
        if (companySelect && companySelect.selectedIndex <= 0 && companySelect.options.length > 1) {
            companySelect.selectedIndex = 1;
            onCompanySelectChange(companySelect.value);
        } else if (companySelect && companySelect.value) {
            onCompanySelectChange(companySelect.value);
        } else {
            updateAutoProjectCode();
        }

        setTimeout(() => {
            const input = document.getElementById('create_project_name');
            if (input) input.focus();
        }, 120);
    }
}

function closeCreateProjectModal() {
    const modal = document.getElementById('createProjectModal');
    if (modal) {
        modal.classList.remove('open');
    }
}

function openEditProjectModal(project) {
    const form = document.getElementById('editProjectForm');
    if (!form) return;

    form.action = `/proyectos/${project.id}`;

    document.getElementById('edit_project_name').value = project.name || '';
    document.getElementById('edit_razon_social').value = project.razon_social || '';
    document.getElementById('edit_direccion').value = project.direccion || '';
    document.getElementById('edit_description').value = project.description && project.description !== 'Sin descripción registrada.' ? project.description : '';
    document.getElementById('edit_company_id').value = project.client_id || '';
    
    // Asignar responsable a cargo (campo de solo lectura)
    document.getElementById('edit_manager_id').value = project.manager_id || '';
    if (project.manager_name && project.manager_name !== 'No Asignado') {
        document.getElementById('edit_manager_name_display').value = project.manager_name;
    } else if (project.client_id) {
        updateManagerForCompany(project.client_id, 'edit_manager_name_display', 'edit_manager_id');
    } else {
        document.getElementById('edit_manager_name_display').value = 'Sin responsable registrado';
    }

    document.getElementById('edit_start_date').value = project.start_date_raw || '';
    document.getElementById('edit_project_code').value = project.code || '';
    document.getElementById('edit_status').value = project.status || 'Planificación';

    const modal = document.getElementById('editProjectModal');
    if (modal) {
        modal.classList.add('open');
    }
}

function closeEditProjectModal() {
    const modal = document.getElementById('editProjectModal');
    if (modal) {
        modal.classList.remove('open');
    }
}

function confirmDeleteProject(id, name) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar Proyecto?',
            html: `Se eliminará el proyecto <b>${name}</b> y sus vinculaciones asociadas. Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true,
            borderRadius: '16px'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteProjectForm');
                form.action = `/proyectos/${id}`;
                form.submit();
            }
        });
    } else {
        if (confirm(`¿Estás seguro de eliminar el proyecto "${name}"?`)) {
            const form = document.getElementById('deleteProjectForm');
            form.action = `/proyectos/${id}`;
            form.submit();
        }
    }
}

/* Filtering & Pagination */
function onProjectSearchKeyup() {
    const input = document.getElementById('projectsListSearchInput');
    currentSearchTerm = input ? input.value.trim().toLowerCase() : '';
    currentPage = 1;
    applyProjectsFilter();
}

function filterProjectsByStatus(status, btnElement) {
    currentStatusFilter = status;
    currentPage = 1;

    document.querySelectorAll('.projects-filter-pill').forEach(pill => {
        pill.classList.remove('active');
    });
    if (btnElement) {
        btnElement.classList.add('active');
    }

    applyProjectsFilter();
}

function applyProjectsFilter() {
    filteredProjectRows = allProjectRows.filter(row => {
        const rowStatus = row.getAttribute('data-status') || '';
        const rowSearch = row.getAttribute('data-search') || '';

        const matchesStatus = (currentStatusFilter === 'all') || (rowStatus.toLowerCase() === currentStatusFilter.toLowerCase());
        const matchesSearch = !currentSearchTerm || rowSearch.includes(currentSearchTerm);

        return matchesStatus && matchesSearch;
    });

    renderProjectsPagination();
}

function renderProjectsPagination() {
    const total = filteredProjectRows.length;
    const totalPages = Math.max(1, Math.ceil(total / pageSize));

    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;

    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);

    // Hide all rows first
    allProjectRows.forEach(row => {
        row.style.display = 'none';
    });

    // Show only the slice of filtered rows
    for (let i = start; i < end; i++) {
        if (filteredProjectRows[i]) {
            filteredProjectRows[i].style.display = '';
        }
    }

    // Toggle No-Results row
    const noResultsRow = document.getElementById('noResultsSearchRow');
    if (noResultsRow) {
        noResultsRow.style.display = (total === 0 && allProjectRows.length > 0) ? '' : 'none';
    }

    // Update Pagination Info
    const pagStartEl = document.getElementById('pagStart');
    const pagEndEl = document.getElementById('pagEnd');
    const pagTotalEl = document.getElementById('pagTotal');

    if (pagStartEl) pagStartEl.innerText = total === 0 ? '0' : (start + 1);
    if (pagEndEl) pagEndEl.innerText = end;
    if (pagTotalEl) pagTotalEl.innerText = total;

    // Render Navigation Buttons
    const nav = document.getElementById('projectsPaginationNav');
    if (!nav) return;

    if (total <= pageSize) {
        nav.innerHTML = '';
        return;
    }

    let html = '';
    // Previous Button
    html += `<button type="button" class="btn-page-step" onclick="goToProjectPage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
    </button>`;

    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentPage - 1 && p <= currentPage + 1)) {
            html += `<button type="button" class="btn-page-step ${p === currentPage ? 'active' : ''}" onclick="goToProjectPage(${p})">${p}</button>`;
        } else if (p === currentPage - 2 || p === currentPage + 2) {
            html += `<span style="padding: 0 4px; color: #94a3b8; font-size: 12px;">...</span>`;
        }
    }

    // Next Button
    html += `<button type="button" class="btn-page-step" onclick="goToProjectPage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </button>`;

    nav.innerHTML = html;
}

function goToProjectPage(page) {
    currentPage = page;
    renderProjectsPagination();
}
</script>
@endpush
