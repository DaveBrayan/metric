@extends('layouts.app')

@section('title', 'Inventario de Equipos — Metric v2 Pachabol')

@push('styles')
<style>
/* ==========================================================================
   EQUIPMENT MODULE STYLES — SELF-CONTAINED (No requiere npm run build)
   ========================================================================== */
.equipment-header-banner {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
    flex-wrap: wrap;
}

.equipment-header-banner h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 28px;
    font-weight: 800;
    letter-spacing: -0.6px;
    color: var(--ink);
    margin-bottom: 6px;
}

.equipment-header-banner p {
    color: #475569;
    font-size: 14px;
    font-weight: 500;
}

/* Toolbar & Filters */
.eq-toolbar-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.eq-filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.eq-filter-pill {
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

.eq-filter-pill:hover {
    border-color: var(--cyan);
    color: var(--ink);
}

.eq-filter-pill.active {
    background: var(--ink);
    border-color: var(--ink);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(15, 28, 46, 0.2);
}

.eq-search-box {
    position: relative;
    width: min(340px, 100%);
}

.eq-search-input {
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

.eq-search-input:focus {
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(16, 185, 223, 0.18);
}

.eq-search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

/* Equipment Table Specifics (Columnas Fijas — Nunca cambian de tamaño entre páginas) */
table#equipmentMasterTable {
    table-layout: fixed !important;
    width: 100% !important;
    min-width: 960px;
    border-collapse: separate;
    border-spacing: 0;
}

table#equipmentMasterTable th {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

table#equipmentMasterTable td {
    overflow: hidden;
    vertical-align: middle;
}

.eq-thumb-cell {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    overflow: hidden;
    background: #f1f5f9;
    display: grid;
    place-items: center;
    border: 1px solid rgba(203, 213, 225, 0.75);
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.eq-thumb-cell:hover {
    transform: scale(1.08);
    box-shadow: 0 6px 16px rgba(15, 28, 46, 0.18);
    border-color: var(--cyan);
}

.eq-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.eq-thumb-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
}

.eq-model-badge {
    display: inline-flex;
    align-items: center;
    background: #f1f5f9;
    border: 1px solid rgba(203, 213, 225, 0.75);
    border-radius: 6px;
    padding: 3px 9px;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    letter-spacing: 0.2px;
}

.eq-serial-code {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-size: 12px;
    font-weight: 700;
    color: #0f1c2e;
    background: rgba(16, 185, 223, 0.08);
    border: 1px solid rgba(16, 185, 223, 0.25);
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-block;
}

/* ==========================================================================
   MODAL BACKDROP & DIALOG (Garantizado: NUNCA se muestra abierto en el body)
   ========================================================================== */
.modal-backdrop-custom {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(15, 28, 46, 0.68) !important;
    backdrop-filter: blur(12px) !important;
    -webkit-backdrop-filter: blur(12px) !important;
    z-index: 9999 !important;
    display: none !important;
    align-items: center !important;
    justify-content: center !important;
    padding: 20px !important;
}

.modal-backdrop-custom.open {
    display: flex !important;
}

.modal-dialog-2col {
    width: 100%;
    max-width: 860px;
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.95);
    border-radius: 20px;
    box-shadow: 0 35px 80px -15px rgba(15, 28, 46, 0.38);
    overflow: hidden;
    animation: scaleInModal 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes scaleInModal {
    0% { transform: scale(0.93) translateY(15px); opacity: 0; }
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
    max-height: 72vh;
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

/* ==========================================================================
   2-COLUMN MODAL LAYOUT
   ========================================================================== */
.modal-2col-layout {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 26px;
}

@media (max-width: 768px) {
    .modal-2col-layout {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

/* Form inputs & controls */
.form-row-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}

@media (max-width: 500px) {
    .form-row-grid {
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
}

.custom-form-select {
    width: 100%;
    height: 42px;
    background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 0 38px 0 14px;
    font-size: 13.5px;
    color: #0f1c2e;
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.custom-form-select:focus {
    border-color: #10b9df;
    box-shadow: 0 0 0 3.5px rgba(16, 185, 223, 0.18);
}

.btn-subtle-link {
    background: transparent;
    border: none;
    color: #64748b;
    font-weight: 600;
    font-size: 13.5px;
    cursor: pointer;
    padding: 9px 18px;
    border-radius: 9999px;
    transition: all 0.2s ease;
}

.btn-subtle-link:hover {
    background: #e2e8f0;
    color: #0f1c2e;
}

.btn-primary-hero-action {
    background: linear-gradient(135deg, #10b9df 0%, #0799a7 100%);
    color: #ffffff;
    border: none;
    border-radius: 9999px;
    padding: 10px 22px;
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

/* Columna 2: Upload Dropzone & Image Box */
.eq-image-upload-zone {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 14px;
    padding: 20px;
    text-align: center;
    position: relative;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 270px;
}

.eq-image-upload-zone.dragover {
    border-color: #10b9df;
    background: rgba(16, 185, 223, 0.06);
}

.eq-preview-container {
    width: 100%;
    max-height: 220px;
    border-radius: 12px;
    overflow: hidden;
    display: none;
    position: relative;
    box-shadow: 0 8px 20px rgba(15, 28, 46, 0.12);
    margin-bottom: 12px;
    background: #0f1c2e;
}

.eq-preview-container.has-image {
    display: block !important;
}

.eq-preview-img {
    width: 100%;
    height: 200px;
    object-fit: contain;
    background: #0f1c2e;
}

.eq-preview-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: rgba(15, 28, 46, 0.75);
    color: #ffffff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: 6px;
    backdrop-filter: blur(4px);
}

.eq-upload-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    width: 100%;
}

.eq-upload-icon-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(16, 185, 223, 0.12);
    color: #10b9df;
    display: grid;
    place-items: center;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}

.eq-image-upload-zone:hover .eq-upload-icon-circle {
    transform: scale(1.08);
    background: rgba(16, 185, 223, 0.2);
}

.eq-upload-title {
    font-weight: 700;
    font-size: 13.5px;
    color: #0f1c2e;
    margin-bottom: 4px;
}

.eq-upload-hint {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 14px;
}

.btn-select-file {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    padding: 8px 16px;
    border-radius: 9999px;
    font-size: 12.5px;
    font-weight: 700;
    color: #0f1c2e;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(15, 28, 46, 0.05);
    transition: all 0.2s ease;
}

.btn-select-file:hover {
    background: #f8fafc;
    border-color: #10b9df;
    color: #10b9df;
}

.btn-remove-photo {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #b91c1c;
    padding: 6px 14px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    margin-top: 8px;
}

.btn-remove-photo:hover {
    background: #fecaca;
}

/* Visor de Fotografía (Ajustado Exacto 500x500px, Fondo Blanco, Header Blanco) */
.photo-viewer-dialog {
    width: 100%;
    max-width: 502px;
    background: #ffffff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 30px 75px -10px rgba(15, 28, 46, 0.4);
    border: 1px solid rgba(226, 232, 240, 0.95);
    animation: scaleInModal 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.photo-viewer-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    background: #ffffff;
    border-bottom: 1px solid rgba(226, 232, 240, 0.9);
}

.photo-viewer-title {
    color: var(--ink);
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    font-size: 16px;
    letter-spacing: -0.3px;
    margin: 0;
}

.photo-viewer-body {
    padding: 0;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
    width: 100%;
    height: 500px;
    overflow: hidden;
}

.photo-viewer-body img {
    width: 500px;
    height: 500px;
    max-width: 100%;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

/* ==========================================================================
   PAGINATION BAR (10 Visibles por Página)
   ========================================================================== */
.eq-pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 22px;
    border-top: 1px solid rgba(226, 232, 240, 0.9);
    background: #ffffff;
    flex-wrap: wrap;
    gap: 14px;
}

.eq-pagination-info {
    font-size: 13px;
    color: #64748b;
    font-weight: 600;
}

.eq-pagination-info strong {
    color: var(--ink);
    font-weight: 800;
}

.eq-pagination-controls {
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-page-step {
    height: 36px;
    padding: 0 14px;
    border-radius: 9px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: var(--ink);
    font-size: 12.5px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}

.btn-page-step:hover:not(:disabled) {
    border-color: var(--cyan);
    color: var(--cyan);
    background: #f8fafc;
}

.btn-page-step:disabled {
    opacity: 0.45;
    cursor: not-allowed;
    background: #f1f5f9;
}

.btn-page-number {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: var(--ink);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s ease;
}

.btn-page-number:hover:not(.active) {
    border-color: var(--cyan);
    color: var(--cyan);
    background: #f8fafc;
}

.btn-page-number.active {
    background: var(--cyan-gradient);
    border-color: transparent;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(16, 185, 223, 0.35);
}
</style>
@endpush

@section('content')
    <!-- Header Banner (Sin las tarjetas KPI que solicitaste retirar) -->
    <div class="equipment-header-banner">
        <div>
            <h1>Inventario de Equipos de Medición</h1>
            <p>Control de calibración, especificaciones técnicas, números de serie y registro fotográfico.</p>
        </div>
        @if($canEdit ?? true)
            <button type="button" class="btn-primary-hero-action" onclick="openCreateEquipmentModal()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Nuevo Equipo</span>
            </button>
        @else
            <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(16, 185, 223, 0.1); border: 1px solid rgba(16, 185, 223, 0.3); padding: 8px 14px; border-radius: 12px; font-size: 12.5px; color: #0369a1; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
                <span>Inventario General (Solo Lectura)</span>
            </div>
        @endif
    </div>

    <!-- Master Table Panel -->
    <div class="glass-card panel-box">
        <!-- Toolbar: Filters & Live Search -->
        <div class="eq-toolbar-bar">
            <div class="eq-filter-group" id="equipmentFilterGroup">
                <button type="button" class="eq-filter-pill active" onclick="filterEquipmentByStatus('all', this)">
                    Todos ({{ count($equipments) }})
                </button>
                <button type="button" class="eq-filter-pill" onclick="filterEquipmentByStatus('Operativo', this)">
                    Operativos
                </button>
                <button type="button" class="eq-filter-pill" onclick="filterEquipmentByStatus('En Calibración', this)">
                    En Calibración
                </button>
                <button type="button" class="eq-filter-pill" onclick="filterEquipmentByStatus('En Mantenimiento', this)">
                    En Mantenimiento
                </button>
            </div>

            <div class="eq-search-box">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="eq-search-icon">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="equipmentSearchInput" 
                    class="eq-search-input" 
                    placeholder="Buscar por equipo, modelo o serie..." 
                    onkeyup="filterEquipmentLive()"
                >
            </div>
        </div>

        <!-- Responsive Table -->
        <div class="table-responsive-box">
            <table class="modern-table" id="equipmentMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">#</th>
                        <th style="width: 76px; text-align: center;">Imagen</th>
                        <th style="width: 35%;">Nombre de Equipo</th>
                        <th style="width: 22%;">Modelo</th>
                        <th style="width: 18%;">N° Serie</th>
                        <th style="width: 130px;">Estado</th>
                        <th style="width: 110px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipments as $item)
                        <tr data-status="{{ $item['status'] }}">
                            <!-- 1. Número -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $item['num'] }}
                            </td>

                            <!-- 2. Imagen / Fotografía -->
                            <td style="text-align: center;">
                                @if(!empty($item['image_url']))
                                    <div class="eq-thumb-cell" style="margin: 0 auto;" onclick="openPhotoModal('{{ $item['image_url'] }}', '{{ addslashes($item['name']) }} - {{ addslashes($item['model']) }}')" title="Ver imagen ampliada">
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="eq-thumb-img">
                                    </div>
                                @else
                                    <div class="eq-thumb-cell" style="cursor: default; margin: 0 auto;" title="Sin fotografía">
                                        <div class="eq-thumb-placeholder">
                                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                                <circle cx="9" cy="9" r="2"/>
                                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- 3. Nombre de Equipo -->
                            <td>
                                <div style="font-weight: 700; color: var(--ink); font-size: 13.5px; line-height: 1.35; word-break: break-word;">
                                    {{ $item['name'] }}
                                </div>
                            </td>

                            <!-- 4. Modelo -->
                            <td>
                                @if($item['model'] !== '—')
                                    <span class="eq-model-badge" title="{{ $item['model'] }}" style="max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item['model'] }}</span>
                                @else
                                    <span style="color: #94a3b8; font-size: 12px;">—</span>
                                @endif
                            </td>

                            <!-- 5. N° Serie -->
                            <td>
                                @if($item['serial_number'] !== 'S/N')
                                    <span class="eq-serial-code" title="{{ $item['serial_number'] }}" style="max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $item['serial_number'] }}</span>
                                @else
                                    <span style="color: #94a3b8; font-size: 12px; font-style: italic;">Sin serie</span>
                                @endif
                            </td>

                            <!-- 6. Estado -->
                            <td>
                                <span class="status-pill-badge {{ $item['status_type'] }}" style="white-space: nowrap;">
                                    {{ $item['status'] }}
                                </span>
                            </td>

                            <!-- 7. Acciones -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    @if($canEdit ?? true)
                                        <!-- Editar Equipo -->
                                        <button 
                                            type="button" 
                                            class="btn-admin-icon-action theme-lime" 
                                            onclick="openEditEquipmentModal('{{ $item['id'] }}', '{{ addslashes($item['name']) }}', '{{ addslashes($item['model'] === '—' ? '' : $item['model']) }}', '{{ addslashes($item['serial_number'] === 'S/N' ? '' : $item['serial_number']) }}', '{{ addslashes($item['raw_description']) }}', '{{ $item['status'] }}', '{{ $item['image_url'] ?? '' }}')" 
                                            title="Editar Equipo" 
                                            aria-label="Editar"
                                        >
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                                <path d="m15 5 4 4"/>
                                            </svg>
                                        </button>

                                        <!-- Eliminar Equipo -->
                                        <button 
                                            type="button" 
                                            class="btn-admin-icon-action theme-danger" 
                                            onclick="deleteEquipment('{{ $item['id'] }}', '{{ addslashes($item['name']) }}')" 
                                            title="Eliminar Equipo" 
                                            aria-label="Eliminar"
                                        >
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                                <line x1="10" y1="11" x2="10" y2="17"/>
                                                <line x1="14" y1="11" x2="14" y2="17"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; color: #64748b; padding: 36px;">
                                No se encontraron equipos registrados en el inventario.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar (10 visibles por página) -->
        <div class="eq-pagination-container" id="equipmentPaginationBar">
            <div class="eq-pagination-info" id="eqPaginationInfo">
                Mostrando <strong id="eqPageStart">1</strong> a <strong id="eqPageEnd">10</strong> de <strong id="eqPageTotal">18</strong> equipos
            </div>
            <div class="eq-pagination-controls" id="eqPaginationControls">
                <!-- Botones generados dinámicamente -->
            </div>
        </div>
    </div>

    <!-- Formulario global oculto para eliminación -->
    <form id="deleteEquipmentFormGlobal" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>

    <!-- ==========================================================================
         MODAL: REGISTRO / EDICIÓN DE EQUIPO (2 COLUMNAS: DATOS TÉCNICOS + FOTOGRAFÍA)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="equipmentModal" onclick="if(event.target === this) closeEquipmentModal()">
        <div class="modal-dialog-2col">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 223, 0.12); color: #10b9df; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 id="equipmentModalTitle" style="font-size: 19px; font-weight: 800; color: #0f1c2e; margin: 0;">Alta de Equipo</h3>
                        <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Especificaciones técnicas y registro fotográfico</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeEquipmentModal()" aria-label="Cerrar modal">✕</button>
            </div>

            <form id="equipmentForm" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="equipmentMethodField" name="_method" value="POST">
                <input type="hidden" id="removeImageField" name="remove_image" value="0">

                <div class="modal-body-custom">
                    <div class="modal-2col-layout">
                        <!-- ==================== COLUMNA 1: DATOS TÉCNICOS ==================== -->
                        <div>
                            <!-- Nombre de Equipo -->
                            <div class="form-field-group">
                                <label class="form-field-label">Nombre de Equipo <span style="color: #ef4444;">*</span></label>
                                <input 
                                    type="text" 
                                    id="eqName" 
                                    name="name" 
                                    class="custom-form-input" 
                                    placeholder="Ej: Anemómetro, Sonómetro, Dosímetro..." 
                                    required
                                >
                            </div>

                            <!-- Modelo y N° de Serie -->
                            <div class="form-row-grid">
                                <div class="form-field-group">
                                    <label class="form-field-label">Modelo</label>
                                    <input 
                                        type="text" 
                                        id="eqModel" 
                                        name="model" 
                                        class="custom-form-input" 
                                        placeholder="Ej: AirflowTes-Master"
                                    >
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">N° de Serie</label>
                                    <input 
                                        type="text" 
                                        id="eqSerialNumber" 
                                        name="serial_number" 
                                        class="custom-form-input" 
                                        placeholder="Ej: mbjb021078"
                                    >
                                </div>
                            </div>

                            <!-- Estado Operativo -->
                            <div class="form-field-group">
                                <label class="form-field-label">Estado Operativo</label>
                                <select id="eqStatus" name="status" class="custom-form-select" required>
                                    <option value="Operativo">Operativo (Habilitado para campo)</option>
                                    <option value="En Calibración">En Calibración</option>
                                    <option value="En Mantenimiento">En Mantenimiento</option>
                                    <option value="Fuera de Servicio">Fuera de Servicio</option>
                                </select>
                            </div>

                            <!-- Descripción -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label">Descripción u Observaciones</label>
                                <textarea 
                                    id="eqDescription" 
                                    name="description" 
                                    class="custom-form-input" 
                                    rows="3" 
                                    placeholder="Observaciones de calibración, accesorios, condiciones de uso..."
                                ></textarea>
                            </div>
                        </div>

                        <!-- ==================== COLUMNA 2: IMAGEN DEL EQUIPO ==================== -->
                        <div>
                            <label class="form-field-label">Fotografía del Equipo</label>
                            
                            <div class="eq-image-upload-zone" id="eqDropZone">
                                <!-- Previsualización de Imagen -->
                                <div class="eq-preview-container" id="eqPreviewContainer">
                                    <span class="eq-preview-badge" id="eqPreviewBadge">Vista previa</span>
                                    <img src="" id="eqPreviewImg" class="eq-preview-img" alt="Vista previa del equipo">
                                </div>

                                <!-- Placeholder cuando no hay imagen cargada -->
                                <div class="eq-upload-placeholder" id="eqUploadPlaceholder" onclick="document.getElementById('eqImageInput').click()">
                                    <div class="eq-upload-icon-circle">
                                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                                            <circle cx="12" cy="13" r="3"/>
                                        </svg>
                                    </div>
                                    <div class="eq-upload-title">Arrastra o sube una imagen</div>
                                    <div class="eq-upload-hint">Formatos: PNG, JPG, WEBP (Máx. 5MB)</div>
                                    <button type="button" class="btn-select-file" onclick="event.stopPropagation(); document.getElementById('eqImageInput').click()">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                            <polyline points="17 8 12 3 7 8"/>
                                            <line x1="12" y1="3" x2="12" y2="15"/>
                                        </svg>
                                        <span>Seleccionar Fotografía</span>
                                    </button>
                                </div>

                                <!-- Botón Quitar Foto (visible solo si hay imagen) -->
                                <button type="button" class="btn-remove-photo" id="btnRemovePhoto" style="display: none;" onclick="removeEquipmentPhoto()">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18"/>
                                        <line x1="6" y1="6" x2="18" y2="18"/>
                                    </svg>
                                    <span>Quitar Fotografía</span>
                                </button>

                                <!-- Input de archivo real -->
                                <input 
                                    type="file" 
                                    id="eqImageInput" 
                                    name="image" 
                                    accept="image/png, image/jpeg, image/webp" 
                                    style="display: none;" 
                                    onchange="handleImageSelection(event)"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-subtle-link" onclick="closeEquipmentModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action" id="btnSubmitEquipment">Guardar Equipo</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL: VISOR DE FOTOGRAFÍA AMPLIADA (LIGHTBOX)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoViewerModal" onclick="if(event.target === this) closePhotoModal()">
        <div class="photo-viewer-dialog">
            <div class="photo-viewer-header">
                <div class="photo-viewer-title" id="photoViewerTitle">Fotografía del Equipo</div>
                <button type="button" class="btn-close-modal" onclick="closePhotoModal()" aria-label="Cerrar visor">✕</button>
            </div>
            <div class="photo-viewer-body">
                <img src="" id="photoViewerImg" alt="Fotografía del Equipo" style="display: none;">
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
/**
 * METRIC V2 — Control de Equipos & Modal 2 Columnas
 */
function openCreateEquipmentModal() {
    const form = document.getElementById('equipmentForm');
    form.reset();
    form.action = "{{ route('equipment.store') }}";
    document.getElementById('equipmentMethodField').value = 'POST';
    document.getElementById('equipmentModalTitle').innerText = 'Alta de Equipo';
    document.getElementById('btnSubmitEquipment').innerText = 'Guardar Equipo';
    document.getElementById('removeImageField').value = '0';

    resetImagePreview();

    const modal = document.getElementById('equipmentModal');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function openEditEquipmentModal(id, name, model, serialNumber, description, status, imageUrl) {
    const form = document.getElementById('equipmentForm');
    form.reset();
    form.action = `/equipos/${id}`;
    document.getElementById('equipmentMethodField').value = 'PUT';
    document.getElementById('equipmentModalTitle').innerText = 'Editar Equipo';
    document.getElementById('btnSubmitEquipment').innerText = 'Actualizar Cambios';
    document.getElementById('removeImageField').value = '0';

    document.getElementById('eqName').value = name;
    document.getElementById('eqModel').value = model;
    document.getElementById('eqSerialNumber').value = serialNumber;
    document.getElementById('eqDescription').value = description;
    document.getElementById('eqStatus').value = status;

    if (imageUrl && imageUrl.trim() !== '') {
        showImagePreview(imageUrl, 'Fotografía Actual');
    } else {
        resetImagePreview();
    }

    const modal = document.getElementById('equipmentModal');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closeEquipmentModal() {
    const modal = document.getElementById('equipmentModal');
    modal.classList.remove('open');
    document.body.style.overflow = '';
}

function handleImageSelection(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('La imagen seleccionada supera el límite máximo de 5MB.');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            showImagePreview(e.target.result, 'Nueva Foto Seleccionada');
            document.getElementById('removeImageField').value = '0';
        };
        reader.readAsDataURL(file);
    }
}

function showImagePreview(src, badgeText) {
    const container = document.getElementById('eqPreviewContainer');
    const img = document.getElementById('eqPreviewImg');
    const badge = document.getElementById('eqPreviewBadge');
    const placeholder = document.getElementById('eqUploadPlaceholder');
    const removeBtn = document.getElementById('btnRemovePhoto');

    img.src = src;
    badge.innerText = badgeText;
    container.classList.add('has-image');
    placeholder.style.display = 'none';
    removeBtn.style.display = 'inline-flex';
}

function resetImagePreview() {
    const container = document.getElementById('eqPreviewContainer');
    const img = document.getElementById('eqPreviewImg');
    const placeholder = document.getElementById('eqUploadPlaceholder');
    const removeBtn = document.getElementById('btnRemovePhoto');
    const input = document.getElementById('eqImageInput');

    img.src = '';
    container.classList.remove('has-image');
    placeholder.style.display = 'flex';
    removeBtn.style.display = 'none';
    input.value = '';
}

function removeEquipmentPhoto() {
    resetImagePreview();
    document.getElementById('removeImageField').value = '1';
}

// Drag & drop listeners for Dropzone
const dropZone = document.getElementById('eqDropZone');
if (dropZone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            document.getElementById('eqImageInput').files = files;
            handleImageSelection({ target: { files: files } });
        }
    }, false);
}

// Lightbox Photo Viewer
function openPhotoModal(imageUrl, title) {
    const img = document.getElementById('photoViewerImg');
    img.src = imageUrl;
    img.style.display = 'block';
    document.getElementById('photoViewerTitle').innerText = title || 'Fotografía del Equipo';
    const modal = document.getElementById('photoViewerModal');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
}

function closePhotoModal() {
    const modal = document.getElementById('photoViewerModal');
    modal.classList.remove('open');
    const img = document.getElementById('photoViewerImg');
    img.src = '';
    img.style.display = 'none';
    document.body.style.overflow = '';
}

// ==========================================================================
// PAGINACIÓN REACTIVA (10 VISIBLES POR PÁGINA) & FILTRADO EN VIVO
// ==========================================================================
const PAGE_SIZE = 10;
let currentEqPage = 1;

function getMatchingEquipmentRows() {
    const query = document.getElementById('equipmentSearchInput').value.toLowerCase().trim();
    const rows = Array.from(document.querySelectorAll('#equipmentMasterTable tbody tr'));
    const activeStatusBtn = document.querySelector('#equipmentFilterGroup .eq-filter-pill.active');
    const statusFilter = activeStatusBtn ? activeStatusBtn.innerText.trim() : 'Todos';

    return rows.filter(row => {
        if (row.querySelector('td[colspan]')) return false;

        const text = row.textContent.toLowerCase();
        const matchesQuery = text.includes(query);

        let matchesStatus = true;
        if (!statusFilter.includes('Todos')) {
            const rowStatus = row.getAttribute('data-status') || '';
            matchesStatus = rowStatus.toLowerCase().includes(statusFilter.toLowerCase());
        }

        return matchesQuery && matchesStatus;
    });
}

function updateEquipmentPagination() {
    const matchingRows = getMatchingEquipmentRows();
    const allRows = Array.from(document.querySelectorAll('#equipmentMasterTable tbody tr'));
    const totalItems = matchingRows.length;
    const totalPages = Math.max(1, Math.ceil(totalItems / PAGE_SIZE));

    if (currentEqPage > totalPages) {
        currentEqPage = totalPages;
    }
    if (currentEqPage < 1) {
        currentEqPage = 1;
    }

    const startIndex = (currentEqPage - 1) * PAGE_SIZE;
    const endIndex = Math.min(startIndex + PAGE_SIZE, totalItems);

    // Ocultar todas las filas
    allRows.forEach(r => r.style.display = 'none');

    // Mostrar únicamente las filas correspondientes a la página activa
    for (let i = startIndex; i < endIndex; i++) {
        if (matchingRows[i]) {
            matchingRows[i].style.display = '';
        }
    }

    // Actualizar etiquetas numéricas del resumen
    const pageStartEl = document.getElementById('eqPageStart');
    const pageEndEl = document.getElementById('eqPageEnd');
    const pageTotalEl = document.getElementById('eqPageTotal');

    if (pageStartEl) pageStartEl.innerText = totalItems === 0 ? 0 : (startIndex + 1);
    if (pageEndEl) pageEndEl.innerText = endIndex;
    if (pageTotalEl) pageTotalEl.innerText = totalItems;

    // Renderizar botones de navegación
    renderPaginationControls(totalPages);
}

function renderPaginationControls(totalPages) {
    const container = document.getElementById('eqPaginationControls');
    if (!container) return;

    if (totalPages <= 1) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    // Botón Anterior
    html += `
        <button type="button" class="btn-page-step" onclick="goToEqPage(${currentEqPage - 1})" ${currentEqPage === 1 ? 'disabled' : ''}>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
            <span>Anterior</span>
        </button>
    `;

    // Botones de Páginas Numeradas
    for (let p = 1; p <= totalPages; p++) {
        html += `
            <button type="button" class="btn-page-number ${p === currentEqPage ? 'active' : ''}" onclick="goToEqPage(${p})">
                ${p}
            </button>
        `;
    }

    // Botón Siguiente
    html += `
        <button type="button" class="btn-page-step" onclick="goToEqPage(${currentEqPage + 1})" ${currentEqPage === totalPages ? 'disabled' : ''}>
            <span>Siguiente</span>
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </button>
    `;

    container.innerHTML = html;
}

function goToEqPage(page) {
    currentEqPage = page;
    updateEquipmentPagination();
    const tableTop = document.getElementById('equipmentMasterTable');
    if (tableTop) {
        tableTop.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
}

function filterEquipmentLive() {
    currentEqPage = 1;
    updateEquipmentPagination();
}

function filterEquipmentByStatus(status, btn) {
    document.querySelectorAll('#equipmentFilterGroup .eq-filter-pill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    currentEqPage = 1;
    updateEquipmentPagination();
}

// Inicializar paginación al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    updateEquipmentPagination();
});
if (document.readyState === 'complete' || document.readyState === 'interactive') {
    setTimeout(updateEquipmentPagination, 60);
}

// Delete Confirmation
function deleteEquipment(id, name) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Eliminar Equipo?',
            text: `¿Estás seguro de eliminar el equipo "${name}"? Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('deleteEquipmentFormGlobal');
                form.action = `/equipos/${id}`;
                form.submit();
            }
        });
    } else {
        if (confirm(`¿Eliminar el equipo "${name}"?`)) {
            const form = document.getElementById('deleteEquipmentFormGlobal');
            form.action = `/equipos/${id}`;
            form.submit();
        }
    }
}
</script>
@endpush
