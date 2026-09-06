@extends('layouts.app')

@section('title', 'Módulos de Medición & Monitoreo — Metric v2 Pachabol')

@push('styles')
<style>
/* ==========================================================================
   MODAL STYLES (Garantizado: Idéntico a Responsables y Proyectos)
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

.modal-dialog-modules {
    width: 100%;
    max-width: 980px;
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

.module-desc-cell {
    font-size: 12.5px;
    color: #475569;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 280px;
}

/* ==========================================================================
   TWO-COLUMN MODAL LAYOUT
   ========================================================================== */
.modal-two-columns-layout {
    display: grid;
    grid-template-columns: 1fr 1.1fr;
    gap: 24px;
}

@media (max-width: 860px) {
    .modal-two-columns-layout {
        grid-template-columns: 1fr;
        gap: 18px;
    }
}

.modal-col-left {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.modal-col-right {
    display: flex;
    flex-direction: column;
    gap: 10px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px 18px;
}

/* Equipment Selection in Column 2 */
.eq-header-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.badge-count-pill {
    background: #e2e8f0;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 9999px;
}

.eq-search-box {
    position: relative;
    width: 100%;
}

.eq-search-input {
    width: 100%;
    height: 38px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 0 12px 0 34px;
    font-size: 12.5px;
    color: #0f172a;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.eq-search-input:focus {
    border-color: #10b9df;
    box-shadow: 0 0 0 3px rgba(16, 185, 223, 0.15);
}

.eq-search-icon {
    position: absolute;
    left: 11px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #94a3b8;
}

.eq-cards-scroll-box {
    max-height: 330px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 8px;
    padding-right: 4px;
}

/* Equipment Item Card */
.equipment-item-card {
    border: 1.5px solid #e2e8f0;
    border-radius: 12px;
    background: #ffffff;
    padding: 9px 12px;
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    gap: 12px;
    user-select: none;
    position: relative;
}

.equipment-item-card:hover {
    border-color: #93c5fd;
    background: #fafcfe;
    transform: translateY(-1px);
}

.equipment-item-card.selected {
    border-color: #0284c7;
    background: #f0f9ff;
    box-shadow: 0 4px 14px rgba(2, 132, 199, 0.14);
}

.eq-card-img-box {
    width: 46px;
    height: 46px;
    border-radius: 9px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    overflow: hidden;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.eq-card-img-box img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.eq-card-info {
    flex: 1;
    min-width: 0;
}

.eq-card-name {
    font-size: 12.8px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
    margin-bottom: 3px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.equipment-item-card.selected .eq-card-name {
    color: #0369a1;
}

.eq-card-meta {
    font-size: 11.5px;
    color: #64748b;
    font-family: monospace;
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}

.eq-card-sn-badge {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    padding: 1px 6px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 600;
    color: #475569;
}

.equipment-item-card.selected .eq-card-sn-badge {
    background: #e0f2fe;
    border-color: #bae6fd;
    color: #0369a1;
}

.eq-card-radio-indicator {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1.8px solid #cbd5e1;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    transition: all 0.18s ease;
    color: transparent;
}

.equipment-item-card.selected .eq-card-radio-indicator {
    border-color: #0284c7;
    background: #0284c7;
    color: #ffffff;
}

/* Staff Multi-select Styling */
.staff-multiselect-scroll {
    max-height: 175px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
    gap: 6px;
    padding: 8px;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
}

.staff-checkbox-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 10px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}

.staff-checkbox-item:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}

.staff-checkbox-item.checked {
    background: #f0fdf4;
    border-color: #86efac;
}

.staff-multi-check {
    width: 16px;
    height: 16px;
    accent-color: #059669;
    cursor: pointer;
}

.staff-item-info {
    flex: 1;
    min-width: 0;
}

.staff-item-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.staff-item-role {
    font-size: 11px;
    color: #64748b;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Table Equipment Card */
.table-equipment-card {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 6px 10px;
    max-width: 270px;
    transition: all 0.2s ease;
}

.table-equipment-card:hover {
    border-color: #cbd5e1;
    background: #f1f5f9;
}

.table-eq-thumb {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    overflow: hidden;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.table-eq-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Staff Name Badges in Table */
.staff-name-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 700;
    line-height: 1.25;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.staff-name-badge.emerald { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.staff-name-badge.cyan { background: #ecfeff; color: #0e7490; border-color: #a5f3fc; }
.staff-name-badge.blue { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.staff-name-badge.amber { background: #fffbeb; color: #b45309; border-color: #fde68a; }
.staff-name-badge.purple { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }

/* Project Pill under Module Name */
.module-project-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: rgba(14, 165, 233, 0.08);
    border: 1px solid rgba(14, 165, 233, 0.22);
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 11px;
    color: #0284c7;
    line-height: 1.25;
}

/* Points of Sampling & Progress Percentage */
.points-progress-cell {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 120px;
}
.points-fraction-text {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 13px;
    color: #0f1c2e;
}
.points-ratio-badge {
    font-size: 10.5px;
    font-weight: 700;
    padding: 1.5px 7px;
    border-radius: 9999px;
    display: inline-block;
}
.points-ratio-badge.pending { background: #f1f5f9; color: #64748b; }
.points-ratio-badge.active { background: #e0f2fe; color: #0284c7; }
.points-ratio-badge.done { background: #ecfdf5; color: #059669; }

.points-progress-track {
    width: 100%;
    height: 6px;
    background: #e2e8f0;
    border-radius: 9999px;
    overflow: hidden;
}
.points-progress-bar {
    height: 100%;
    border-radius: 9999px;
    transition: width 0.3s ease;
}
.points-progress-bar.pending { background: #94a3b8; }
.points-progress-bar.active { background: linear-gradient(90deg, #10b9df, #0284c7); }
.points-progress-bar.done { background: linear-gradient(90deg, #10b981, #059669); }

/* Table Column Fixed Layout */
table#modulesMasterTable {
    table-layout: fixed !important;
    width: 100% !important;
    min-width: 980px;
    border-collapse: separate;
    border-spacing: 0;
}
table#modulesMasterTable th {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
table#modulesMasterTable td {
    overflow: hidden;
    vertical-align: middle;
}

/* Staff more count badge (+N) */
.staff-name-badge.more-count {
    background: #f1f5f9;
    color: #475569;
    border: 1.5px dashed #94a3b8;
    font-weight: 800;
    cursor: help;
    font-size: 11.5px;
    padding: 2.5px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.staff-name-badge.more-count:hover {
    background: var(--ink);
    color: #ffffff;
    border-color: var(--ink);
    border-style: solid;
}

/* Active Project Filter Banner */
.active-project-filter-banner {
    background: linear-gradient(135deg, rgba(240, 249, 255, 0.95) 0%, rgba(224, 242, 254, 0.85) 100%);
    border: 1.5px solid rgba(186, 230, 253, 0.95);
    border-radius: 14px;
    padding: 12px 18px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    box-shadow: 0 4px 14px rgba(2, 132, 199, 0.08);
}
.active-prj-icon-badge {
    width: 36px;
    height: 36px;
    background: #ffffff;
    border-radius: 10px;
    display: grid;
    place-items: center;
    border: 1px solid rgba(186, 230, 253, 0.8);
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.1);
}
.btn-clear-project-filter {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #ffffff;
    border: 1px solid rgba(186, 230, 253, 0.9);
    color: #0369a1;
    font-size: 12.5px;
    font-weight: 700;
    padding: 7px 14px;
    border-radius: 9999px;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
}
.btn-clear-project-filter:hover {
    background: #0284c7;
    color: #ffffff;
    border-color: #0284c7;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
    transform: translateY(-1px);
}

/* Pagination Bar (10 Visibles por Página) */
.modules-pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 22px;
    border-top: 1px solid rgba(226, 232, 240, 0.9);
    background: #ffffff;
    border-bottom-left-radius: 16px;
    border-bottom-right-radius: 16px;
    flex-wrap: wrap;
    gap: 12px;
}
.modules-pagination-info {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
}
.modules-pagination-info strong {
    color: var(--ink);
    font-weight: 800;
}
.modules-pagination-controls {
    display: flex;
    align-items: center;
    gap: 6px;
}
.modules-pag-btn {
    min-width: 34px;
    height: 34px;
    padding: 0 10px;
    border-radius: 8px;
    border: 1px solid rgba(203, 213, 225, 0.85);
    background: #ffffff;
    color: #475569;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.modules-pag-btn:hover:not(:disabled) {
    border-color: #10b9df;
    color: #0284c7;
    background: #f0f9ff;
}
.modules-pag-btn.active {
    background: #0f1c2e;
    border-color: #0f1c2e;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(15, 28, 46, 0.25);
}
.modules-pag-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f8fafc;
}
</style>
@endpush

@section('content')
    <!-- Header Banner -->
    <div class="admins-header-banner">
        <div>
            <h1>Módulos de Medición & Monitoreo</h1>
            <p>Administración de tipos de monitoreo ambiental, equipos asignados, personal de campo y puntos de muestreo.</p>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="{{ route('projects.index') }}" class="date-capsule" style="text-decoration: none;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                <span>Volver a Proyectos</span>
            </a>
            <button type="button" class="btn-primary-hero-action" onclick="openCreateModuleModal()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Nuevo Módulo</span>
            </button>
        </div>
    </div>

    @if(!empty($activeProject))
        <!-- Active Project Filter Banner -->
        <div class="active-project-filter-banner">
            <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                <div class="active-prj-icon-badge">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #0284c7;">
                        Filtrando Módulos del Proyecto
                    </div>
                    <div style="font-size: 15px; font-weight: 800; color: var(--ink); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span>{{ $activeProject->name }}</span>
                        @if(!empty($activeProject->code))
                            <span style="font-family: monospace; font-size: 11.5px; color: #0369a1; background: rgba(14, 165, 233, 0.12); padding: 2px 7px; border-radius: 6px; font-weight: 700;">
                                {{ $activeProject->code }}
                            </span>
                        @endif
                        @if(!empty($activeProject->company))
                            <span style="font-size: 13px; font-weight: 600; color: #64748b;">
                                — {{ $activeProject->company->name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            <a href="{{ route('modules.index') }}" class="btn-clear-project-filter" title="Quitar filtro y ver todos los módulos">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                <span>Ver Todos los Módulos</span>
            </a>
        </div>
    @endif

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <!-- Module Filter Pills & Quick Search -->
        <div class="table-head-action-bar" style="align-items: center;">
            <div class="admins-role-filter-group" id="moduleFilterGroup">
                <button type="button" class="role-filter-pill active" onclick="filterModulesByTag('all', this)">
                    Todos ({{ count($modulesData) }})
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('dosimetria', this)">
                    Dosimetría de Ruido
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('ruido_ambiental', this)">
                    Ruido Ambiental
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('agua', this)">
                    Agua (Parám. de Campo)
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('opacidad', this)">
                    Opacidad (Humos/Emisiones)
                </button>
                <button type="button" class="role-filter-pill" onclick="filterModulesByTag('particulas', this)">
                    Partículas 24 Horas
                </button>
            </div>

            <div class="table-search-and-action">
                <div class="table-quick-search-box">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>
                    <input 
                        type="text" 
                        id="modulesSearchInput" 
                        class="table-search-input" 
                        placeholder="Buscar por módulo, descripción, equipo o personal..." 
                        onkeyup="searchModulesLiveTable()"
                    >
                </div>
            </div>
        </div>

        <div class="table-responsive-box">
            <table class="modern-table" id="modulesMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 26%;">Tipo de Módulo & Proyecto</th>
                        <th style="width: 22%;">Personal de Campo Asignado</th>
                        <th style="width: 25%;">Equipos de Medición</th>
                        <th style="width: 170px;">Puntos de Muestreo & Avance</th>
                        <th style="width: 115px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="modulesTableBody">
                    @forelse($modulesData as $item)
                        <tr class="module-data-row" 
                            data-module-type="{{ $item['module_key'] }}"
                            data-project-id="{{ $item['project_id'] }}"
                            data-search="{{ strtolower($item['module_name'] . ' ' . $item['project_name'] . ' ' . $item['description'] . ' ' . $item['staff_name'] . ' ' . $item['equipment']) }}">
                            
                            <!-- 1. # -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 14px; text-align: center;">
                                {{ $item['num'] }}
                            </td>

                            <!-- 2. Tipo de Módulo & Proyecto Asignado -->
                            <td>
                                <div style="font-weight: 800; color: var(--ink); font-size: 13.5px; margin-bottom: 4px;">
                                    {{ $item['module_name'] }}
                                </div>
                                <div class="module-project-pill" title="Proyecto Asignado">
                                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
                                    </svg>
                                    <span style="font-weight: 700;">{{ $item['project_name'] }}</span>
                                    @if(!empty($item['project_code']))
                                        <span style="font-family: monospace; opacity: 0.8; font-size: 10px;">({{ $item['project_code'] }})</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 3. Personal de Campo Asignado (Nombres en Badges con límite +N) -->
                            <td>
                                @if(!empty($item['staff_members']) && count($item['staff_members']) > 0)
                                    @php
                                        $maxVisibleStaff = 2;
                                        $visibleStaff = array_slice($item['staff_members'], 0, $maxVisibleStaff);
                                        $remainingStaff = array_slice($item['staff_members'], $maxVisibleStaff);
                                        $remainingCount = count($remainingStaff);
                                        $remainingNames = implode(', ', array_column($remainingStaff, 'name'));
                                    @endphp
                                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 5px;">
                                        @foreach($visibleStaff as $stf)
                                            <span class="staff-name-badge {{ $stf['theme'] ?? 'cyan' }}" title="{{ $stf['role'] ?? 'Técnico de Campo' }}">
                                                {{ $stf['name'] }}
                                            </span>
                                        @endforeach
                                        @if($remainingCount > 0)
                                            <span class="staff-name-badge more-count" title="Otros miembros asignados: {{ $remainingNames }}">
                                                +{{ $remainingCount }}
                                            </span>
                                        @endif
                                    </div>
                                @elseif(!empty($item['staff_name']) && $item['staff_name'] !== 'Por Asignar')
                                    <span class="staff-name-badge {{ $item['staff_theme'] ?? 'cyan' }}">
                                        {{ $item['staff_name'] }}
                                    </span>
                                @else
                                    <span style="font-size: 12px; color: #94a3b8; font-style: italic;">Por Asignar</span>
                                @endif
                            </td>

                            <!-- 4. Equipos de Medición (Card con Imagen - Sin S/N) -->
                            <td>
                                <div class="table-equipment-card">
                                    <div class="table-eq-thumb">
                                        @if(!empty($item['equipment_image']))
                                            <img src="{{ $item['equipment_image'] }}" alt="Equipo">
                                        @else
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/>
                                            </svg>
                                        @endif
                                    </div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div style="font-weight: 700; color: var(--ink); font-size: 12.5px; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $item['equipment_name'] }}">
                                            {{ $item['equipment_name'] }}
                                        </div>
                                        @if(!empty($item['equipment_model']))
                                            <div style="font-size: 11px; color: #64748b; font-family: monospace; line-height: 1.2; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $item['equipment_model'] }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- 5. Puntos de Muestreo & Avance (ej. 0/50 y %) -->
                            <td>
                                <div class="points-progress-cell">
                                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                                        <span class="points-fraction-text">
                                            {{ $item['points_fraction'] }} Puntos
                                        </span>
                                        <span class="points-ratio-badge {{ $item['points_ratio'] >= 100 ? 'done' : ($item['points_ratio'] > 0 ? 'active' : 'pending') }}">
                                            {{ $item['points_ratio'] }}%
                                        </span>
                                    </div>
                                    <div class="points-progress-track">
                                        <div class="points-progress-bar {{ $item['points_ratio'] >= 100 ? 'done' : ($item['points_ratio'] > 0 ? 'active' : 'pending') }}" style="width: {{ $item['points_ratio'] }}%;"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- 6. Acciones -->
                            <td>
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <!-- Editar Módulo -->
                                    <button type="button" class="btn-admin-icon-action theme-amber" onclick='openEditModuleModal(@json($item))' title="Editar Módulo" aria-label="Editar">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                            <path d="m15 5 4 4"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar Módulo -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="confirmDeleteModule('{{ $item['id'] }}', '{{ addslashes($item['module_name']) }}')" title="Eliminar Módulo" aria-label="Eliminar">
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
                            <td colspan="6" style="text-align: center; color: #64748b; padding: 36px;">
                                No se encontraron módulos registrados en el sistema.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="6" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron módulos que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar (10 visibles por página) -->
        <div class="modules-pagination-container" id="modulesPaginationBar">
            <div class="modules-pagination-info" id="modulesPaginationInfo">
                Mostrando <strong id="modPageStart">1</strong> a <strong id="modPageEnd">10</strong> de <strong id="modPageTotal">{{ count($modulesData) }}</strong> módulos
            </div>
            <div class="modules-pagination-controls" id="modulesPaginationControls">
                <!-- Botones generados dinámicamente -->
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 1: NUEVO MÓDULO (2 COLUMNAS: EQUIPOS EN CARDS CON IMÁGENES)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createModuleModal" role="dialog" aria-modal="true" aria-labelledby="createModuleModalTitle">
        <div class="modal-dialog-modules">
            <div class="modal-header-custom">
                <div class="modal-header-title">
                    <h2 id="createModuleModalTitle">Nuevo Módulo</h2>
                    <p>Registra los parámetros de monitoreo ambiental y asigna el equipo y personal técnico.</p>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateModuleModal()" aria-label="Cerrar modal">✕</button>
            </div>

            <form action="{{ route('modules.store') }}" method="POST" id="createModuleForm" onsubmit="return validateModuleSubmit('create')">
                @csrf
                <div class="modal-body-custom">
                    <div class="modal-two-columns-layout">
                        <!-- COLUMNA 1 (IZQUIERDA) -->
                        <div class="modal-col-left">
                            <!-- 1. PROYECTO ASIGNADO -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_module_project_id">
                                    Proyecto Asignado <span class="req">*</span>
                                </label>
                                <select name="project_id" id="create_module_project_id" class="custom-form-select" required>
                                    <option value="">-- Seleccionar Proyecto --</option>
                                    @foreach($projectsList as $prj)
                                        <option value="{{ $prj->id }}">
                                            {{ $prj->name }} @if(!empty($prj->code)) ({{ $prj->code }}) @endif @if(!empty($prj->company)) — {{ $prj->company->name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. TIPO DE MÓDULO -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_module_key">
                                    Tipo de Módulo <span class="req">*</span>
                                </label>
                                <select name="key" id="create_module_key" class="custom-form-select" required>
                                    <option value="">-- Seleccionar Tipo de Módulo --</option>
                                    <option value="dosimetria">Dosimetría de Ruido</option>
                                    <option value="ruido_ambiental">Ruido Ambiental</option>
                                    <option value="agua">Agua (Parámetros de Campo)</option>
                                    <option value="opacidad">Opacidad (Humos y Emisiones)</option>
                                    <option value="particulas">Partículas 24 Horas</option>
                                    <option value="iluminacion">Iluminación Ocupacional</option>
                                    <option value="estres_termico">Estrés Térmico</option>
                                    <option value="otro">Monitoreo Personalizado</option>
                                </select>
                            </div>

                            <!-- 2. DESCRIPCIÓN -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_module_description">
                                    Descripción
                                </label>
                                <textarea 
                                    name="description" 
                                    id="create_module_description" 
                                    class="custom-form-input" 
                                    rows="3" 
                                    placeholder="Describe los alcances del monitoreo, área de muestreo o especificaciones técnicas..."
                                ></textarea>
                            </div>

                            <!-- 3. PERSONAL DE CAMPO ASIGNADO (MÚLTIPLE) -->
                            <div class="form-field-group">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <label class="form-field-label" style="margin-bottom: 0;">
                                        Personal de Campo Asignado
                                    </label>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">(Múltiple selección)</span>
                                </div>
                                <div class="staff-multiselect-scroll" id="create_staff_container">
                                    @forelse($staffList as $stf)
                                        <label class="staff-checkbox-item" for="create_stf_{{ $stf->id }}">
                                            <input 
                                                type="checkbox" 
                                                name="field_staff_ids[]" 
                                                id="create_stf_{{ $stf->id }}" 
                                                value="{{ $stf->id }}" 
                                                class="staff-multi-check"
                                                onchange="toggleStaffCardSelection(this)"
                                            >
                                            <div class="client-initial-box {{ $stf->role_theme ?? 'cyan' }}" style="width: 28px; height: 28px; font-size: 11px; border-radius: 8px; flex-shrink: 0;">
                                                {{ strtoupper(substr($stf->name, 0, 1)) }}
                                            </div>
                                            <div class="staff-item-info">
                                                <div class="staff-item-name">{{ $stf->name }}</div>
                                                <div class="staff-item-role">{{ $stf->position ?? 'Técnico de Campo' }}</div>
                                            </div>
                                        </label>
                                    @empty
                                        <div style="font-size: 12px; color: #94a3b8; text-align: center; padding: 12px;">
                                            No hay personal registrado aún.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 4. TOTAL DE PUNTOS DE MUESTREO (DEFECTO: 1) -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label" for="create_points_total">
                                    Total de Puntos de Muestreo <span class="req">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="points_total" 
                                    id="create_points_total" 
                                    class="custom-form-input" 
                                    value="1" 
                                    min="1" 
                                    placeholder="1" 
                                    required
                                >
                            </div>
                        </div>

                        <!-- COLUMNA 2 (DERECHA: EQUIPOS DE MEDICIÓN EN FORMA DE CARDS) -->
                        <div class="modal-col-right">
                            <div class="eq-header-row">
                                <label class="form-field-label" style="margin-bottom: 0;">
                                    Equipos de Medición <span class="req">*</span>
                                </label>
                                <span class="badge-count-pill">{{ count($equipmentList) }} equipos</span>
                            </div>

                            <!-- Buscador rápido de equipos -->
                            <div class="eq-search-box">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="eq-search-icon">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                                <input 
                                    type="text" 
                                    class="eq-search-input" 
                                    placeholder="Buscar equipo por nombre, modelo o serie..." 
                                    onkeyup="filterEquipmentCards(this, 'create_eq_cards_container')"
                                >
                            </div>

                            <!-- Contenedor scrollable de Cards de Equipos -->
                            <div class="eq-cards-scroll-box" id="create_eq_cards_container">
                                @foreach($equipmentList as $eq)
                                    @php
                                        $eqCalibrationText = $eq->name . ($eq->model ? ' (' . $eq->model . ')' : '') . ($eq->serial_number ? ' - S/N: ' . $eq->serial_number : '');
                                        $hasImg = !empty($eq->image) && file_exists(public_path($eq->image));
                                    @endphp
                                    <div 
                                        class="equipment-item-card" 
                                        data-eq-id="{{ $eq->id }}"
                                        data-eq-text="{{ $eqCalibrationText }}"
                                        data-search="{{ strtolower($eq->name . ' ' . $eq->model . ' ' . $eq->serial_number) }}"
                                        onclick="selectEquipmentCard(this, '{{ $eq->id }}', '{{ addslashes($eqCalibrationText) }}', 'create')"
                                    >
                                        <div class="eq-card-img-box">
                                            @if($hasImg)
                                                <img src="{{ asset($eq->image) }}" alt="{{ $eq->name }}">
                                            @else
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="eq-card-info">
                                            <div class="eq-card-name">{{ $eq->name }}</div>
                                            <div class="eq-card-meta">
                                                @if(!empty($eq->model))
                                                    <span>Mod: {{ $eq->model }}</span>
                                                @endif
                                                @if(!empty($eq->serial_number))
                                                    <span class="eq-card-sn-badge">S/N: {{ $eq->serial_number }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="eq-card-radio-indicator">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Hidden inputs para enviar el equipo seleccionado -->
                            <input type="hidden" name="equipment_id" id="create_equipment_id" required>
                            <input type="hidden" name="calibration_equipment" id="create_calibration_equipment" required>
                            <div id="create_eq_validation_msg" style="display: none; color: #ef4444; font-size: 11.5px; font-weight: 600; padding: 4px 6px;">
                                Por favor selecciona un equipo de medición.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeCreateModuleModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Módulo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 2: EDITAR MÓDULO (2 COLUMNAS: EQUIPOS EN CARDS CON IMÁGENES)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editModuleModal" role="dialog" aria-modal="true" aria-labelledby="editModuleModalTitle">
        <div class="modal-dialog-modules">
            <div class="modal-header-custom">
                <div class="modal-header-title">
                    <h2 id="editModuleModalTitle">Editar Módulo</h2>
                    <p>Actualiza la información del monitoreo, personal técnico o equipo asignado.</p>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeEditModuleModal()" aria-label="Cerrar modal">✕</button>
            </div>

            <form action="" method="POST" id="editModuleForm" onsubmit="return validateModuleSubmit('edit')">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="modal-two-columns-layout">
                        <!-- COLUMNA 1 (IZQUIERDA) -->
                        <div class="modal-col-left">
                            <!-- 1. PROYECTO ASIGNADO -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_module_project_id">
                                    Proyecto Asignado <span class="req">*</span>
                                </label>
                                <select name="project_id" id="edit_module_project_id" class="custom-form-select" required>
                                    <option value="">-- Seleccionar Proyecto --</option>
                                    @foreach($projectsList as $prj)
                                        <option value="{{ $prj->id }}">
                                            {{ $prj->name }} @if(!empty($prj->code)) ({{ $prj->code }}) @endif @if(!empty($prj->company)) — {{ $prj->company->name }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- 2. TIPO DE MÓDULO -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_module_key">
                                    Tipo de Módulo <span class="req">*</span>
                                </label>
                                <select name="key" id="edit_module_key" class="custom-form-select" required>
                                    <option value="dosimetria">Dosimetría de Ruido</option>
                                    <option value="ruido_ambiental">Ruido Ambiental</option>
                                    <option value="agua">Agua (Parámetros de Campo)</option>
                                    <option value="opacidad">Opacidad (Humos y Emisiones)</option>
                                    <option value="particulas">Partículas 24 Horas</option>
                                    <option value="iluminacion">Iluminación Ocupacional</option>
                                    <option value="estres_termico">Estrés Térmico</option>
                                    <option value="otro">Monitoreo Personalizado</option>
                                </select>
                            </div>

                            <!-- 2. DESCRIPCIÓN -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_module_description">
                                    Descripción
                                </label>
                                <textarea 
                                    name="description" 
                                    id="edit_module_description" 
                                    class="custom-form-input" 
                                    rows="3" 
                                    placeholder="Describe los alcances del monitoreo, área de muestreo o especificaciones técnicas..."
                                ></textarea>
                            </div>

                            <!-- 3. PERSONAL DE CAMPO ASIGNADO (MÚLTIPLE) -->
                            <div class="form-field-group">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <label class="form-field-label" style="margin-bottom: 0;">
                                        Personal de Campo Asignado
                                    </label>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">(Múltiple selección)</span>
                                </div>
                                <div class="staff-multiselect-scroll" id="edit_staff_container">
                                    @forelse($staffList as $stf)
                                        <label class="staff-checkbox-item" for="edit_stf_{{ $stf->id }}">
                                            <input 
                                                type="checkbox" 
                                                name="field_staff_ids[]" 
                                                id="edit_stf_{{ $stf->id }}" 
                                                value="{{ $stf->id }}" 
                                                class="staff-multi-check"
                                                onchange="toggleStaffCardSelection(this)"
                                            >
                                            <div class="client-initial-box {{ $stf->role_theme ?? 'cyan' }}" style="width: 28px; height: 28px; font-size: 11px; border-radius: 8px; flex-shrink: 0;">
                                                {{ strtoupper(substr($stf->name, 0, 1)) }}
                                            </div>
                                            <div class="staff-item-info">
                                                <div class="staff-item-name">{{ $stf->name }}</div>
                                                <div class="staff-item-role">{{ $stf->position ?? 'Técnico de Campo' }}</div>
                                            </div>
                                        </label>
                                    @empty
                                        <div style="font-size: 12px; color: #94a3b8; text-align: center; padding: 12px;">
                                            No hay personal registrado aún.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <!-- 4. TOTAL DE PUNTOS DE MUESTREO -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label" for="edit_points_total">
                                    Total de Puntos de Muestreo <span class="req">*</span>
                                </label>
                                <input 
                                    type="number" 
                                    name="points_total" 
                                    id="edit_points_total" 
                                    class="custom-form-input" 
                                    min="1" 
                                    required
                                >
                            </div>
                        </div>

                        <!-- COLUMNA 2 (DERECHA: EQUIPOS DE MEDICIÓN EN FORMA DE CARDS) -->
                        <div class="modal-col-right">
                            <div class="eq-header-row">
                                <label class="form-field-label" style="margin-bottom: 0;">
                                    Equipos de Medición <span class="req">*</span>
                                </label>
                                <span class="badge-count-pill">{{ count($equipmentList) }} equipos</span>
                            </div>

                            <!-- Buscador rápido de equipos -->
                            <div class="eq-search-box">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="eq-search-icon">
                                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                                </svg>
                                <input 
                                    type="text" 
                                    class="eq-search-input" 
                                    placeholder="Buscar equipo por nombre, modelo o serie..." 
                                    onkeyup="filterEquipmentCards(this, 'edit_eq_cards_container')"
                                >
                            </div>

                            <!-- Contenedor scrollable de Cards de Equipos -->
                            <div class="eq-cards-scroll-box" id="edit_eq_cards_container">
                                @foreach($equipmentList as $eq)
                                    @php
                                        $eqCalibrationText = $eq->name . ($eq->model ? ' (' . $eq->model . ')' : '') . ($eq->serial_number ? ' - S/N: ' . $eq->serial_number : '');
                                        $hasImg = !empty($eq->image) && file_exists(public_path($eq->image));
                                    @endphp
                                    <div 
                                        class="equipment-item-card" 
                                        data-eq-id="{{ $eq->id }}"
                                        data-eq-text="{{ $eqCalibrationText }}"
                                        data-search="{{ strtolower($eq->name . ' ' . $eq->model . ' ' . $eq->serial_number) }}"
                                        onclick="selectEquipmentCard(this, '{{ $eq->id }}', '{{ addslashes($eqCalibrationText) }}', 'edit')"
                                    >
                                        <div class="eq-card-img-box">
                                            @if($hasImg)
                                                <img src="{{ asset($eq->image) }}" alt="{{ $eq->name }}">
                                            @else
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="eq-card-info">
                                            <div class="eq-card-name">{{ $eq->name }}</div>
                                            <div class="eq-card-meta">
                                                @if(!empty($eq->model))
                                                    <span>Mod: {{ $eq->model }}</span>
                                                @endif
                                                @if(!empty($eq->serial_number))
                                                    <span class="eq-card-sn-badge">S/N: {{ $eq->serial_number }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="eq-card-radio-indicator">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="20 6 9 17 4 12"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Hidden inputs para enviar el equipo seleccionado -->
                            <input type="hidden" name="equipment_id" id="edit_equipment_id" required>
                            <input type="hidden" name="calibration_equipment" id="edit_calibration_equipment" required>
                            <div id="edit_eq_validation_msg" style="display: none; color: #ef4444; font-size: 11.5px; font-weight: 600; padding: 4px 6px;">
                                Por favor selecciona un equipo de medición.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeEditModuleModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Hidden Delete Form -->
    <form id="deleteModuleForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<script>
    /* Selection Logic for Equipment Cards */
    function selectEquipmentCard(cardElement, eqId, eqText, modalType) {
        const containerId = modalType === 'edit' ? 'edit_eq_cards_container' : 'create_eq_cards_container';
        const inputId = modalType === 'edit' ? 'edit_equipment_id' : 'create_equipment_id';
        const inputTextId = modalType === 'edit' ? 'edit_calibration_equipment' : 'create_calibration_equipment';
        const valMsgId = modalType === 'edit' ? 'edit_eq_validation_msg' : 'create_eq_validation_msg';

        // Deseleccionar previas
        document.querySelectorAll(`#${containerId} .equipment-item-card`).forEach(c => c.classList.remove('selected'));

        // Seleccionar actual
        cardElement.classList.add('selected');

        const idField = document.getElementById(inputId);
        if (idField) idField.value = eqId;

        const textField = document.getElementById(inputTextId);
        if (textField) textField.value = eqText;

        const valMsg = document.getElementById(valMsgId);
        if (valMsg) valMsg.style.display = 'none';
    }

    function filterEquipmentCards(inputElement, containerId) {
        const term = inputElement.value.trim().toLowerCase();
        const cards = document.querySelectorAll(`#${containerId} .equipment-item-card`);
        cards.forEach(card => {
            const search = card.getAttribute('data-search') || '';
            card.style.display = (!term || search.includes(term)) ? 'flex' : 'none';
        });
    }

    function toggleStaffCardSelection(checkbox) {
        const label = checkbox.closest('.staff-checkbox-item');
        if (label) {
            if (checkbox.checked) {
                label.classList.add('checked');
            } else {
                label.classList.remove('checked');
            }
        }
    }

    function validateModuleSubmit(modalType) {
        const inputId = modalType === 'edit' ? 'edit_equipment_id' : 'create_equipment_id';
        const inputTextId = modalType === 'edit' ? 'edit_calibration_equipment' : 'create_calibration_equipment';
        const valMsgId = modalType === 'edit' ? 'edit_eq_validation_msg' : 'create_eq_validation_msg';

        const eqId = document.getElementById(inputId)?.value;
        const eqText = document.getElementById(inputTextId)?.value;

        if (!eqId && !eqText) {
            const valMsg = document.getElementById(valMsgId);
            if (valMsg) valMsg.style.display = 'block';
            return false;
        }
        return true;
    }

    /* Modal Controls */
    function openCreateModuleModal() {
        const modal = document.getElementById('createModuleModal');
        if (modal) {
            // Reset project select (preselect active project if filtered)
            const prjSelect = document.getElementById('create_module_project_id');
            const defaultProjectId = '{{ (!empty($selectedProjectId) && $selectedProjectId !== "todos") ? $selectedProjectId : "" }}';
            if (prjSelect) {
                prjSelect.value = defaultProjectId;
            }

            // Reset staff checkboxes
            document.querySelectorAll('#create_staff_container .staff-checkbox-item').forEach(label => {
                const check = label.querySelector('.staff-multi-check');
                if (check) check.checked = false;
                label.classList.remove('checked');
            });

            // Reset equipment cards
            document.querySelectorAll('#create_eq_cards_container .equipment-item-card').forEach(c => c.classList.remove('selected'));
            const eqId = document.getElementById('create_equipment_id');
            if (eqId) eqId.value = '';
            const calEq = document.getElementById('create_calibration_equipment');
            if (calEq) calEq.value = '';
            const valMsg = document.getElementById('create_eq_validation_msg');
            if (valMsg) valMsg.style.display = 'none';

            // Reset points to 1 strictly
            const ptsInput = document.getElementById('create_points_total');
            if (ptsInput) ptsInput.value = 1;

            modal.classList.add('open');
            setTimeout(() => {
                const select = document.getElementById('create_module_key');
                if (select) select.focus();
            }, 100);
        }
    }

    function closeCreateModuleModal() {
        const modal = document.getElementById('createModuleModal');
        if (modal) modal.classList.remove('open');
    }

    function openEditModuleModal(item) {
        const form = document.getElementById('editModuleForm');
        if (!form) return;

        form.action = `/modulos/${item.id}`;

        // 0. Proyecto Asignado
        const projectSelect = document.getElementById('edit_module_project_id');
        if (projectSelect) projectSelect.value = item.project_id || '';

        // 1. Tipo de módulo
        const keySelect = document.getElementById('edit_module_key');
        if (keySelect) keySelect.value = item.module_key || 'dosimetria';

        // 2. Descripción
        const descInput = document.getElementById('edit_module_description');
        if (descInput) descInput.value = item.description && item.description !== 'Sin descripción registrada.' ? item.description : '';

        // 3. Personal de Campo (Checkboxes múltiples)
        const staffIds = Array.isArray(item.field_staff_ids) 
            ? item.field_staff_ids.map(Number) 
            : (item.field_staff_id ? [Number(item.field_staff_id)] : []);

        document.querySelectorAll('#edit_staff_container .staff-checkbox-item').forEach(label => {
            const check = label.querySelector('.staff-multi-check');
            if (check) {
                const sid = Number(check.value);
                check.checked = staffIds.includes(sid);
                if (check.checked) {
                    label.classList.add('checked');
                } else {
                    label.classList.remove('checked');
                }
            }
        });

        // 4. Puntos de muestreo (default 1)
        const ptsInput = document.getElementById('edit_points_total');
        if (ptsInput) ptsInput.value = item.points_total || 1;

        // 5. Equipo de Medición (Cards)
        document.querySelectorAll('#edit_eq_cards_container .equipment-item-card').forEach(c => c.classList.remove('selected'));
        
        let targetCard = null;
        if (item.equipment_id) {
            targetCard = document.querySelector(`#edit_eq_cards_container .equipment-item-card[data-eq-id="${item.equipment_id}"]`);
        }
        if (!targetCard && item.equipment) {
            const cleanEq = item.equipment.toLowerCase();
            const cards = document.querySelectorAll('#edit_eq_cards_container .equipment-item-card');
            for (let c of cards) {
                const txt = (c.getAttribute('data-eq-text') || '').toLowerCase();
                if (txt.includes(cleanEq) || cleanEq.includes(txt)) {
                    targetCard = c;
                    break;
                }
            }
        }

        const editEqId = document.getElementById('edit_equipment_id');
        const editCalEq = document.getElementById('edit_calibration_equipment');
        const valMsg = document.getElementById('edit_eq_validation_msg');
        if (valMsg) valMsg.style.display = 'none';

        if (targetCard) {
            targetCard.classList.add('selected');
            targetCard.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            if (editEqId) editEqId.value = targetCard.getAttribute('data-eq-id');
            if (editCalEq) editCalEq.value = targetCard.getAttribute('data-eq-text');
        } else {
            if (editEqId) editEqId.value = item.equipment_id || '';
            if (editCalEq) editCalEq.value = item.equipment || '';
        }

        const modal = document.getElementById('editModuleModal');
        if (modal) modal.classList.add('open');
    }

    function closeEditModuleModal() {
        const modal = document.getElementById('editModuleModal');
        if (modal) modal.classList.remove('open');
    }

    function confirmDeleteModule(id, name) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Eliminar Módulo?',
                html: `Se eliminará el módulo <b>${name}</b>. Esta acción no se puede deshacer.`,
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
                    const form = document.getElementById('deleteModuleForm');
                    form.action = `/modulos/${id}`;
                    form.submit();
                }
            });
        } else {
            if (confirm(`¿Estás seguro de eliminar el módulo "${name}"?`)) {
                const form = document.getElementById('deleteModuleForm');
                form.action = `/modulos/${id}`;
                form.submit();
            }
        }
    }

    /* Modal Close on Outside Click and Escape */
    document.addEventListener('DOMContentLoaded', () => {
        ['createModuleModal', 'editModuleModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.classList.remove('open');
                });
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCreateModuleModal();
                closeEditModuleModal();
            }
        });
    });

    /* ==========================================================================
       PAGINACIÓN REACTIVA (10 VISIBLES POR PÁGINA) & FILTRADO EN VIVO
       ========================================================================== */
    const MOD_PAGE_SIZE = 10;
    let currentModPage = 1;
    let currentModuleFilter = 'all';

    function getMatchingModuleRows() {
        const searchTerm = document.getElementById('modulesSearchInput')?.value.trim().toLowerCase() || '';
        const rows = Array.from(document.querySelectorAll('#modulesMasterTable tbody tr.module-data-row'));

        return rows.filter(row => {
            const rowType = row.getAttribute('data-module-type') || '';
            const rowSearch = row.getAttribute('data-search') || '';

            const matchesTag = (currentModuleFilter === 'all' || rowType === currentModuleFilter);
            const matchesSearch = (!searchTerm || rowSearch.includes(searchTerm));

            return matchesTag && matchesSearch;
        });
    }

    function updateModulesPagination() {
        const matchingRows = getMatchingModuleRows();
        const allRows = Array.from(document.querySelectorAll('#modulesMasterTable tbody tr.module-data-row'));
        const totalItems = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / MOD_PAGE_SIZE));

        if (currentModPage > totalPages) currentModPage = totalPages;
        if (currentModPage < 1) currentModPage = 1;

        const startIdx = (currentModPage - 1) * MOD_PAGE_SIZE;
        const endIdx = startIdx + MOD_PAGE_SIZE;

        // Ocultar todas las filas
        allRows.forEach(row => row.style.display = 'none');

        // Mostrar solo las del bloque de la página actual
        matchingRows.slice(startIdx, endIdx).forEach(row => {
            row.style.display = '';
        });

        // Control de mensajes vacío o sin resultados
        const emptyTableRow = document.getElementById('emptyTableRow');
        const emptySearchRow = document.getElementById('noResultsSearchRow');
        if (allRows.length === 0) {
            if (emptyTableRow) emptyTableRow.style.display = '';
            if (emptySearchRow) emptySearchRow.style.display = 'none';
        } else if (totalItems === 0) {
            if (emptyTableRow) emptyTableRow.style.display = 'none';
            if (emptySearchRow) emptySearchRow.style.display = '';
        } else {
            if (emptyTableRow) emptyTableRow.style.display = 'none';
            if (emptySearchRow) emptySearchRow.style.display = 'none';
        }

        // Actualizar resumen en la barra de paginación
        const startEl = document.getElementById('modPageStart');
        const endEl = document.getElementById('modPageEnd');
        const totalEl = document.getElementById('modPageTotal');

        if (startEl) startEl.textContent = totalItems === 0 ? 0 : (startIdx + 1);
        if (endEl) endEl.textContent = Math.min(endIdx, totalItems);
        if (totalEl) totalEl.textContent = totalItems;

        renderModulesPaginationControls(totalPages);
    }

    function renderModulesPaginationControls(totalPages) {
        const container = document.getElementById('modulesPaginationControls');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        // Botón Anterior
        html += `<button type="button" class="modules-pag-btn" onclick="goToModPage(${currentModPage - 1})" ${currentModPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>`;

        // Botones de página
        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || (p >= currentModPage - 1 && p <= currentModPage + 1)) {
                html += `<button type="button" class="modules-pag-btn ${p === currentModPage ? 'active' : ''}" onclick="goToModPage(${p})">${p}</button>`;
            } else if (p === currentModPage - 2 || p === currentModPage + 2) {
                html += `<span style="padding: 0 4px; color: #94a3b8; font-weight: 700;">...</span>`;
            }
        }

        // Botón Siguiente
        html += `<button type="button" class="modules-pag-btn" onclick="goToModPage(${currentModPage + 1})" ${currentModPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>`;

        container.innerHTML = html;
    }

    function goToModPage(page) {
        currentModPage = page;
        updateModulesPagination();
        const table = document.getElementById('modulesMasterTable');
        if (table) table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function filterModulesByTag(moduleKey, btnElement) {
        currentModuleFilter = moduleKey;
        document.querySelectorAll('#moduleFilterGroup .role-filter-pill').forEach(b => b.classList.remove('active'));
        if (btnElement) btnElement.classList.add('active');
        currentModPage = 1;
        updateModulesPagination();
    }

    function searchModulesLiveTable() {
        currentModPage = 1;
        updateModulesPagination();
    }

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', () => {
        updateModulesPagination();
    });
    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(updateModulesPagination, 60);
    }
</script>
@endpush
