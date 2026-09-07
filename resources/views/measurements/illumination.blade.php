@extends('layouts.app')

@section('title', 'Monitoreo de Iluminación Ocupacional — Metric v2 Pachabol')

@push('styles')
<!-- Leaflet CSS for Maps -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<style>
/* ==========================================================================
   MONITOREO DE ILUMINACIÓN OCUPACIONAL — DESIGN SYSTEM
   ========================================================================== */

/* 1. Header & Navigation */
.illumination-header-banner {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 22px;
    flex-wrap: wrap;
}

.illumination-header-banner h1 {
    font-family: 'Outfit', sans-serif;
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -0.5px;
    color: var(--ink);
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.illumination-header-banner p {
    color: #64748b;
    font-size: 13.5px;
    font-weight: 500;
}

.header-action-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-secondary-subtle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    border: 1px solid rgba(203, 213, 225, 0.9);
    border-radius: var(--radius-full);
    padding: 9px 18px;
    font-size: 13px;
    font-weight: 700;
    color: #475569;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 3px rgba(15, 28, 46, 0.04);
}

.btn-secondary-subtle:hover {
    border-color: var(--cyan);
    color: var(--ink);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 223, 0.15);
}

.btn-primary-hero-action {
    background: linear-gradient(135deg, #10b9df 0%, #0799a7 100%);
    color: #ffffff;
    border: none;
    border-radius: var(--radius-full);
    padding: 10px 22px;
    font-weight: 700;
    font-size: 13.5px;
    cursor: pointer;
    box-shadow: 0 6px 18px -3px rgba(16, 185, 223, 0.4);
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-primary-hero-action:hover {
    transform: translateY(-1px);
    box-shadow: 0 8px 22px -3px rgba(16, 185, 223, 0.55);
}

/* ==========================================================================
   2. ENCABEZADO TÉCNICO DUAL CON EDICIÓN INLINE DIRECTA
   ========================================================================== */
.technical-summary-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

@media (max-width: 960px) {
    .technical-summary-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
}

.tech-header-card {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 14px rgba(15, 28, 46, 0.04);
    display: flex;
    flex-direction: column;
}

.tech-table-grid {
    width: 100%;
    border-collapse: collapse;
}

.tech-table-grid tr {
    border-bottom: 1.5px solid #cbd5e1;
}

.tech-table-grid tr:last-child {
    border-bottom: none;
}

.tech-table-grid th.tech-label-cell {
    width: 38%;
    background: #f8fafc;
    border-right: 1.5px solid #cbd5e1;
    padding: 10px 16px;
    font-family: 'Outfit', sans-serif;
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    color: var(--ink);
    text-align: left;
    vertical-align: middle;
}

.tech-table-grid td.tech-val-cell {
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
    vertical-align: middle;
    background: #ffffff;
}

.tech-inline-input {
    width: 100%;
    height: 36px;
    background: #ffffff;
    border: 1.5px solid transparent;
    border-radius: 8px;
    padding: 0 10px;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.tech-inline-input:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.tech-inline-input:focus {
    background: #ffffff;
    border-color: #10b9df;
    box-shadow: 0 0 0 3px rgba(16, 185, 223, 0.18);
}

.tech-inline-input.tech-val-mono {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-size: 12.5px;
}

.tech-card-header-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f1f5f9;
    padding: 8px 16px;
    border-bottom: 1.5px solid #cbd5e1;
}

.tech-card-title {
    font-size: 11.5px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #475569;
    display: flex;
    align-items: center;
    gap: 6px;
}

.header-auto-save-status {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 700;
    color: #059669;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.header-auto-save-status.visible {
    opacity: 1;
}

.header-auto-save-status.saving {
    color: #0284c7;
}

.tech-val-static {
    padding: 8px 6px;
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
}

.tech-val-mono {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    font-size: 12.5px;
    letter-spacing: 0.3px;
}

/* Columna 3 de Tarjeta de Equipo: Fotografía directa sin card ni modal */
.tech-eq-photo-cell {
    width: 32%;
    text-align: center;
    vertical-align: middle;
    border-left: 1.5px solid #cbd5e1;
    padding: 12px;
    background: #ffffff;
}

/* ==========================================================================
   3. TABLA MAESTRA DE MEDICIONES
   ========================================================================== */
.illumination-table-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.95);
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(15, 28, 46, 0.05);
    overflow: hidden;
}

.illumination-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    gap: 16px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.85);
    flex-wrap: wrap;
}

.table-filter-pills {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-pill-btn {
    background: #ffffff;
    border: 1px solid rgba(203, 213, 225, 0.85);
    border-radius: var(--radius-full);
    padding: 6px 14px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-pill-btn:hover {
    border-color: var(--cyan);
    color: var(--ink);
}

.filter-pill-btn.active {
    background: var(--ink);
    border-color: var(--ink);
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(15, 28, 46, 0.2);
}

.search-box-pill {
    position: relative;
    width: min(340px, 100%);
}

.search-input-pill {
    width: 100%;
    height: 38px;
    background: #ffffff;
    border: 1px solid rgba(203, 213, 225, 0.95);
    border-radius: var(--radius-full);
    padding: 0 16px 0 38px;
    font-size: 12.5px;
    color: var(--ink);
    outline: none;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

.search-input-pill:focus {
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(16, 185, 223, 0.15);
}

.search-icon-inside {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    pointer-events: none;
}

/* Tabla con layout fijo y columnas consistentes */
table#illuminationMasterTable {
    table-layout: fixed !important;
    width: 100% !important;
    min-width: 1240px;
    border-collapse: separate;
    border-spacing: 0;
}

table#illuminationMasterTable th,
table#illuminationMasterTable td {
    vertical-align: middle;
    box-sizing: border-box;
}

table#illuminationMasterTable thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    padding: 14px 14px;
    border-bottom: 1.5px solid #e2e8f0;
    white-space: nowrap;
}

table#illuminationMasterTable tbody tr {
    transition: background-color 0.15s ease;
}

table#illuminationMasterTable tbody tr:hover {
    background-color: #f8fafc;
}

table#illuminationMasterTable tbody td {
    padding: 13px 14px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
    color: #334155;
}

/* Badges y Tags */
.lighting-type-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: var(--radius-full);
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.lighting-type-tag.Natural {
    background: #fef9c3;
    color: #a16207;
    border: 1px solid #fde047;
}

.lighting-type-tag.Artificial {
    background: #e0f2fe;
    color: #0369a1;
    border: 1px solid #bae6fd;
}

.lighting-type-tag.Mixta {
    background: #ede9fe;
    color: #6d28d9;
    border: 1px solid #ddd6fe;
}

.lux-req-code {
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 12.5px;
    font-weight: 700;
    color: #475569;
    background: #f1f5f9;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    display: inline-block;
}

.lux-measured-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 11px;
    border-radius: 8px;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 13px;
    font-weight: 800;
}

.lux-measured-badge.compliant {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.lux-measured-badge.non-compliant {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

.table-thumb-preview {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    border: 1.5px solid #cbd5e1;
    overflow: hidden;
    background: #f8fafc;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.table-thumb-preview:hover {
    transform: scale(1.08);
    border-color: var(--cyan);
    box-shadow: 0 4px 10px rgba(16, 185, 223, 0.25);
}

.table-thumb-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.staff-badge-cell {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 600;
    color: #1e293b;
    background: #f8fafc;
    padding: 4px 9px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    max-width: 130px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Botón Ver Mapa */
.btn-map-marker-view {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    color: #0284c7;
    border-radius: 6px;
    padding: 5px 9px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-map-marker-view:hover {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.25);
}

/* Acciones en la tabla */
.btn-admin-icon-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #f1f5f9;
    color: #475569;
}

.btn-admin-icon-action.theme-cyan:hover {
    background: #e0f2fe;
    border-color: #bae6fd;
    color: #0284c7;
}

.btn-admin-icon-action.theme-lime:hover {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #059669;
}

.btn-admin-icon-action.theme-danger:hover {
    background: #fef2f2;
    border-color: #fecaca;
    color: #dc2626;
}

/* Paginación */
.illumination-pagination-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    background: #ffffff;
    flex-wrap: wrap;
    gap: 12px;
}

.illumination-pagination-info {
    font-size: 13px;
    color: #64748b;
    font-weight: 500;
}

.illumination-pagination-controls {
    display: flex;
    align-items: center;
    gap: 6px;
}

.ill-pag-btn {
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

.ill-pag-btn:hover:not(:disabled) {
    border-color: #10b9df;
    color: #0284c7;
    background: #f0f9ff;
}

.ill-pag-btn.active {
    background: #0f1c2e;
    border-color: #0f1c2e;
    color: #ffffff;
    box-shadow: 0 2px 8px rgba(15, 28, 46, 0.25);
}

.ill-pag-btn:disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: #f8fafc;
}

/* ==========================================================================
   4. MODALS (GRANDE EN 3 COLUMNAS PARA REGISTRO Y EDICIÓN)
   ========================================================================== */
.modal-backdrop-custom {
    position: fixed !important;
    inset: 0 !important;
    background: rgba(15, 28, 46, 0.68) !important;
    backdrop-filter: blur(10px) !important;
    -webkit-backdrop-filter: blur(10px) !important;
    z-index: 9999 !important;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-backdrop-custom.open {
    display: flex !important;
}

.modal-dialog-illumination {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 25px 60px -15px rgba(15, 28, 46, 0.35);
    width: 100%;
    max-width: 800px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: modalSlideUp 0.28s var(--spring-ease);
}

.modal-dialog-illumination.modal-dialog-lg {
    max-width: 1200px !important;
    width: 96% !important;
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(24px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

.modal-header-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    border-bottom: 1px solid #e2e8f0;
    background: #ffffff;
}

.modal-header-custom h2 {
    font-family: 'Outfit', sans-serif;
    font-size: 19px;
    font-weight: 800;
    color: var(--ink);
    margin: 0;
}

.btn-close-modal {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    border: none;
    background: #f1f5f9;
    color: #64748b;
    font-size: 16px;
    font-weight: 700;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s ease;
}

.btn-close-modal:hover {
    background: #e2e8f0;
    color: #0f172a;
}

.modal-body-custom {
    padding: 20px 24px;
    overflow-y: auto;
    flex: 1;
}

.modal-footer-custom {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 12px;
    padding: 14px 24px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}

/* Modal en 3 Columnas */
.modal-three-cols-grid {
    display: grid;
    grid-template-columns: 1.2fr 0.9fr 0.9fr;
    gap: 22px;
}

@media (max-width: 1040px) {
    .modal-three-cols-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }
}

.modal-col-card {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.modal-col-heading {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #0284c7;
    padding-bottom: 8px;
    border-bottom: 1.5px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.form-grid-two-cols {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

@media (max-width: 640px) {
    .form-grid-two-cols {
        grid-template-columns: 1fr;
    }
}

.form-field-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-bottom: 10px;
}

.form-field-label {
    font-size: 12px;
    font-weight: 700;
    color: var(--ink);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.form-field-label .req {
    color: #ef4444;
}

.custom-form-input, .custom-form-select {
    width: 100%;
    height: 38px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 0 12px;
    font-size: 12.5px;
    color: #0f172a;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s ease;
}

.custom-form-input:focus, .custom-form-select:focus {
    border-color: #10b9df;
    box-shadow: 0 0 0 3px rgba(16, 185, 223, 0.16);
}

/* Leyenda Normativa Dinámica */
.normative-lux-legend {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 12px;
    margin-top: 5px;
}

.normative-lux-legend .legend-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 3px;
}

.normative-lux-legend .legend-area-name {
    font-size: 12px;
    font-weight: 800;
    color: var(--ink);
}

.normative-lux-legend .legend-lux-badge {
    font-size: 11px;
    font-weight: 800;
    background: #e0f2fe;
    color: #0369a1;
    padding: 2px 7px;
    border-radius: 4px;
}

.normative-lux-legend .legend-app-text {
    font-size: 11px;
    color: #64748b;
    margin: 0;
    line-height: 1.35;
}

/* Mediciones LUX en Badges (hasta 25 puntos) */
.readings-input-bar {
    display: flex;
    gap: 8px;
    align-items: center;
}

.btn-add-reading {
    background: #0284c7;
    color: #ffffff;
    border: none;
    border-radius: 8px;
    padding: 0 14px;
    height: 38px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s ease;
}

.btn-add-reading:hover {
    background: #0369a1;
}

.readings-badges-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    max-height: 95px;
    overflow-y: auto;
    background: #f8fafc;
    border: 1.5px dashed #cbd5e1;
    border-radius: 8px;
    padding: 8px;
    min-height: 48px;
    align-items: center;
    margin-top: 6px;
}

.lux-reading-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    background: #ffffff;
    border: 1px solid #93c5fd;
    color: #0369a1;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 11.5px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 6px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}

.lux-reading-badge button {
    background: none;
    border: none;
    color: #ef4444;
    font-size: 12px;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    display: flex;
    align-items: center;
}

.readings-summary-strip {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 6px;
    padding: 6px 10px;
    background: #f1f5f9;
    border-radius: 6px;
    font-size: 11.5px;
    font-weight: 600;
    color: #475569;
}

/* Columna 2: Visor Fotográfico TIPO SLIDE / CARRUSEL (Mucho más grande y navegable) */
.modal-photo-slider-wrapper {
    width: 100%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.modal-slider-viewport {
    width: 100%;
    height: 360px;
    border-radius: 12px;
    background: #0b1320;
    border: 1.5px solid #cbd5e1;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: inset 0 2px 8px rgba(0,0,0,0.25);
}

.modal-slider-main-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: block;
    transition: opacity 0.25s ease;
}

.modal-slider-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    text-align: center;
    padding: 24px;
}

.slider-counter-badge {
    position: absolute;
    top: 10px;
    right: 12px;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    color: #ffffff;
    font-family: 'SFMono-Regular', Consolas, monospace;
    font-size: 11.5px;
    font-weight: 700;
    padding: 3px 9px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    z-index: 5;
}

.slider-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.75);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    font-size: 15px;
    font-weight: 800;
    cursor: pointer;
    display: grid;
    place-items: center;
    transition: all 0.2s ease;
    z-index: 6;
}

.slider-nav-btn:hover {
    background: #0284c7;
    border-color: #0284c7;
    transform: translateY(-50%) scale(1.08);
}

.slider-nav-btn.prev {
    left: 10px;
}

.slider-nav-btn.next {
    right: 10px;
}

.slider-thumbs-strip {
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    padding: 4px 0;
    min-height: 44px;
}

.slider-thumb-item {
    width: 44px;
    height: 44px;
    border-radius: 6px;
    border: 2px solid transparent;
    overflow: hidden;
    cursor: pointer;
    opacity: 0.65;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

.slider-thumb-item.active {
    border-color: #0284c7;
    opacity: 1;
    transform: scale(1.05);
}

.slider-thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn-add-photos-trigger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    height: 36px;
    border: 1.5px dashed #0284c7;
    background: #f0f9ff;
    color: #0284c7;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-add-photos-trigger:hover {
    background: #e0f2fe;
    border-color: #0369a1;
}

/* Modo Consulta vs Modo Edición */
.modal-badge-view {
    font-size: 11px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.modal-badge-edit {
    font-size: 11px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 6px;
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.btn-modal-edit-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 32px;
    padding: 0 12px;
    border-radius: 8px;
    border: 1.5px solid #0284c7;
    background: #ffffff;
    color: #0284c7;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-modal-edit-action:hover {
    background: #0284c7;
    color: #ffffff;
}

.btn-modal-edit-action.active-editing {
    background: #059669;
    border-color: #059669;
    color: #ffffff;
}

/* ==========================================================================
   MODO LECTURA DEL MODAL (Sin cajas de formulario, valores limpios y elegantes)
   ========================================================================== */
.modal-view-mode .custom-form-input:disabled,
.modal-view-mode .custom-form-select:disabled,
.modal-view-mode .custom-form-textarea:disabled {
    background: transparent !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 3px 0 !important;
    height: auto !important;
    min-height: auto !important;
    font-size: 13.5px !important;
    font-weight: 700 !important;
    color: var(--ink) !important;
    -webkit-text-fill-color: var(--ink) !important;
    box-shadow: none !important;
    cursor: default !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    outline: none !important;
}

.modal-view-mode .custom-form-select:disabled {
    background-image: none !important;
}

.modal-view-mode input[type="date"]::-webkit-calendar-picker-indicator,
.modal-view-mode input[type="time"]::-webkit-calendar-picker-indicator {
    display: none !important;
}

.modal-view-mode input[type="number"]::-webkit-inner-spin-button,
.modal-view-mode input[type="number"]::-webkit-outer-spin-button {
    -webkit-appearance: none !important;
    margin: 0 !important;
}
.modal-view-mode input[type="number"] {
    -moz-appearance: textfield !important;
}

.modal-view-mode .form-field-label {
    font-size: 11px !important;
    font-weight: 700 !important;
    color: #64748b !important;
    text-transform: uppercase !important;
    letter-spacing: 0.5px !important;
    margin-bottom: 2px !important;
}

.modal-view-mode .form-field-label .req {
    display: none !important;
}

.modal-view-mode .form-field-group {
    border-bottom: 1px solid #f1f5f9;
    padding-bottom: 5px;
    margin-bottom: 7px;
}

.modal-view-mode .form-field-group:last-child {
    border-bottom: none;
}

.modal-view-mode .readings-badges-container {
    border: 1px solid #e2e8f0 !important;
    background: #ffffff !important;
}

.btn-admin-view-action {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    height: 30px;
    padding: 0 10px;
    border-radius: 6px;
    border: 1px solid #93c5fd;
    background: #eff6ff;
    color: #0284c7;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-admin-view-action:hover {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
}

.custom-form-textarea {
    width: 100%;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 12.5px;
    font-family: inherit;
    color: #0f172a;
    outline: none;
    box-sizing: border-box;
    transition: all 0.2s ease;
    resize: vertical;
}

.custom-form-textarea:focus {
    border-color: #10b9df;
    box-shadow: 0 0 0 3px rgba(16, 185, 223, 0.16);
}

.modal-view-mode input:disabled,
.modal-view-mode select:disabled,
.modal-view-mode textarea:disabled {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
    color: #1e293b !important;
    cursor: default !important;
    box-shadow: none !important;
    opacity: 0.95 !important;
}

/* Columna 3: Mini Mapa Leaflet */
.modal-minimap-container {
    width: 100%;
    height: 200px;
    border-radius: 10px;
    border: 1.5px solid #cbd5e1;
    overflow: hidden;
    margin-top: 6px;
}

/* Modal Maps Viewer */
#mapContainerLeaflet {
    width: 100%;
    height: 380px;
    border-radius: 12px;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    z-index: 10;
}

.map-card-info-grid {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 16px;
    margin-bottom: 16px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 18px;
    align-items: center;
}

@media (max-width: 600px) {
    .map-card-info-grid {
        grid-template-columns: 1fr;
    }
}

/* ==========================================================================
   EXPORT MODAL, ALL LOCATIONS MAP MODAL & PHOTO REPORT STYLES
   ========================================================================== */

/* 1. Modal de Opciones de Exportación */
.export-options-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
}

@media (max-width: 640px) {
    .export-options-grid {
        grid-template-columns: 1fr;
    }
}

.export-option-card {
    background: #ffffff;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 22px 20px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 16px;
    transition: all 0.25s ease;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.export-option-card:hover {
    border-color: #0284c7;
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -4px rgba(2, 132, 199, 0.14);
}

.export-option-card.excel-theme:hover {
    border-color: #059669;
    box-shadow: 0 12px 24px -4px rgba(5, 150, 105, 0.14);
}

.export-card-top {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.export-card-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.export-card-icon.excel-icon {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}

.export-card-icon.photo-icon {
    background: #e0f2fe;
    color: #0284c7;
    border: 1px solid #bae6fd;
}

.export-card-body h3 {
    font-family: 'Outfit', sans-serif;
    font-size: 15.5px;
    font-weight: 800;
    color: var(--ink);
    margin: 0 0 4px 0;
}

.export-card-body p {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.45;
    margin: 0;
}

.export-card-badge {
    display: inline-block;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    padding: 2px 7px;
    border-radius: 4px;
    margin-bottom: 6px;
}

.export-card-badge.badge-excel {
    background: #d1fae5;
    color: #065f46;
}

.export-card-badge.badge-photo {
    background: #e0f2fe;
    color: #075985;
}

.export-card-btn {
    width: 100%;
    padding: 10px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.export-card-btn.excel-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
}

.export-card-btn.excel-btn:hover {
    background: #047857;
    box-shadow: 0 6px 16px rgba(5, 150, 105, 0.35);
}

.export-card-btn.photo-btn {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
}

.export-card-btn.photo-btn:hover {
    background: #0369a1;
    box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
}

/* 2. Modal de Todas las Ubicaciones */
.all-loc-modal-grid {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 16px;
    min-height: 520px;
}

@media (max-width: 900px) {
    .all-loc-modal-grid {
        grid-template-columns: 1fr;
    }
}

#allLocationsMapLeaflet {
    width: 100%;
    height: 520px;
    border-radius: 12px;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    z-index: 10;
}

.all-loc-sidebar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-height: 520px;
    overflow-y: auto;
}

.all-loc-point-item {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    padding: 10px 12px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.all-loc-point-item:hover {
    border-color: #0284c7;
    background: #f0f9ff;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.1);
}

.all-loc-point-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
}

.custom-map-pin {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 11.5px;
    color: #ffffff;
    border: 2.5px solid #ffffff;
    box-shadow: 0 3px 10px rgba(0, 0, 0, 0.35);
}

.custom-map-pin.pin-compliant {
    background: #059669;
}

.custom-map-pin.pin-non-compliant {
    background: #dc2626;
}

/* 3. Modal de Reporte Fotográfico */
.photo-report-container {
    max-height: 72vh;
    overflow-y: auto;
    padding: 16px 20px;
}

.photo-report-header-banner {
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 10px;
    padding: 14px 18px;
    margin-bottom: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    font-size: 12.5px;
}

@media (max-width: 700px) {
    .photo-report-header-banner {
        grid-template-columns: 1fr;
    }
}

.photo-report-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 18px;
}

.photo-card-item {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 28, 46, 0.04);
    display: flex;
    flex-direction: column;
}

.photo-card-top-bar {
    padding: 9px 12px;
    background: #f1f5f9;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.photo-card-img-wrap {
    width: 100%;
    height: 190px;
    background: #0f172a;
    display: grid;
    place-items: center;
    overflow: hidden;
    position: relative;
}

.photo-card-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.photo-card-meta {
    padding: 12px 14px;
    display: flex;
    flex-direction: column;
    gap: 7px;
    font-size: 12px;
}

.photo-meta-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
    border-bottom: 1px dashed #f1f5f9;
    padding-bottom: 4px;
}

.photo-meta-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.photo-meta-lbl {
    color: #64748b;
    font-weight: 600;
    font-size: 11.5px;
    flex-shrink: 0;
}

.photo-meta-val {
    color: var(--ink);
    font-weight: 700;
    text-align: right;
    word-break: break-word;
}

/* Print Styles for Photographic Report */
@media print {
    body * {
        visibility: hidden !important;
    }
    #photoReportModal,
    #photoReportModal * {
        visibility: visible !important;
    }
    #photoReportModal {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        background: #ffffff !important;
        padding: 0 !important;
        overflow: visible !important;
        z-index: 999999 !important;
    }
    .modal-dialog-illumination {
        max-width: 100% !important;
        width: 100% !important;
        box-shadow: none !important;
        border: none !important;
    }
    .modal-header-custom,
    .modal-footer-custom,
    .btn-close-modal,
    .btn-print-action {
        display: none !important;
    }
    .photo-report-container {
        max-height: none !important;
        overflow: visible !important;
        padding: 0 !important;
    }
    .photo-card-item {
        break-inside: avoid !important;
        page-break-inside: avoid !important;
        margin-bottom: 16px !important;
        border: 1px solid #000 !important;
    }
    .photo-card-img-wrap {
        height: 220px !important;
    }
}
</style>
@endpush

@section('content')
    <!-- 1. Header Banner -->
    <div class="illumination-header-banner">
        <div>
            <h1>
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4"/>
                    <path d="M12 2v2"/>
                    <path d="M12 20v2"/>
                    <path d="m4.93 4.93 1.41 1.41"/>
                    <path d="m17.66 17.66 1.41 1.41"/>
                    <path d="M2 12h2"/>
                    <path d="M20 12h2"/>
                    <path d="m6.34 17.66-1.41 1.41"/>
                    <path d="m19.07 4.93-1.41 1.41"/>
                </svg>
                <span>Monitoreo de Iluminación Ocupacional</span>
            </h1>
            <p>Registro de puntos de luxometría, geolocalización de áreas, archivo fotográfico y memoria técnica.</p>
        </div>

        <div class="header-action-group">
            <a href="{{ route('modules.index', ['proyecto' => $module->project_id]) }}" class="btn-secondary-subtle">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                <span>Volver a Módulos</span>
            </a>

            <!-- Botón Ubicaciones (Modal con todos los puntos y técnicos) -->
            <button type="button" class="btn-secondary-subtle" onclick="openAllLocationsModal()" title="Ver mapa con todos los puntos y personal que registró">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>Ubicaciones</span>
            </button>

            <!-- Botón Exportar (Planilla Excel y Reporte Fotográfico) -->
            <button type="button" class="btn-secondary-subtle" onclick="openExportModal()" title="Exportar planilla técnica oficial o catálogo fotográfico">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                <span>Exportar</span>
            </button>

            <button type="button" class="btn-primary-hero-action" onclick="openCreateMeasurementModal()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"/>
                    <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                <span>Nuevo Punto de Medición</span>
            </button>
        </div>
    </div>

    <!-- 2. Encabezado Técnico Dual con Edición Directa Inline -->
    <div class="technical-summary-grid">
        <!-- Tarjeta Izquierda: Instalación, Fechas y Monitoreo (Directamente Editables) -->
        <div class="tech-header-card">
            <div class="tech-card-header-bar">
                <div class="tech-card-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>Datos Técnicos del Monitoreo</span>
                </div>
                <span id="headerAutoSaveBadge" class="header-auto-save-status">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Guardado</span>
                </span>
            </div>
            <table class="tech-table-grid">
                <tr>
                    <th class="tech-label-cell">INSTALACIÓN:</th>
                    <td class="tech-val-cell">
                        <input 
                            type="text" 
                            id="inline_installation_name" 
                            class="tech-inline-input" 
                            value="{{ $installationName }}" 
                            placeholder="Nombre de la instalación o empresa..."
                            title="Haz clic para editar la instalación"
                            onchange="autoSaveHeaderField()"
                        >
                    </td>
                </tr>
                <tr>
                    <th class="tech-label-cell">FECHA DE INICIO:</th>
                    <td class="tech-val-cell">
                        <input 
                            type="date" 
                            id="inline_start_date" 
                            class="tech-inline-input tech-val-mono" 
                            value="{{ $startDateRaw }}" 
                            title="Fecha de inicio del monitoreo de iluminación"
                            onchange="autoSaveHeaderField()"
                        >
                    </td>
                </tr>
                <tr>
                    <th class="tech-label-cell">FECHA DE FINALIZACIÓN:</th>
                    <td class="tech-val-cell">
                        <input 
                            type="date" 
                            id="inline_end_date" 
                            class="tech-inline-input tech-val-mono" 
                            value="{{ $endDateRaw }}" 
                            title="Fecha de finalización del monitoreo"
                            onchange="autoSaveHeaderField()"
                        >
                    </td>
                </tr>
                <tr>
                    <th class="tech-label-cell">TIPO DE MONITOREO:</th>
                    <td class="tech-val-cell">
                        <input 
                            type="text" 
                            id="inline_monitoring_type" 
                            class="tech-inline-input" 
                            value="{{ $monitoringType }}" 
                            placeholder="Ej: Seguimiento, Rutinario, Periódico..."
                            title="Escribe el tipo de monitoreo"
                            onchange="autoSaveHeaderField()"
                        >
                    </td>
                </tr>
            </table>
        </div>

        <!-- Tarjeta Derecha: Equipo de Medición Asignado en 3 Columnas (Imagen Directa sin Card ni Modal) -->
        <div class="tech-header-card">
            <div class="tech-card-header-bar">
                <div class="tech-card-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="3" x2="12" y2="9"/></svg>
                    <span>Equipo Asignado</span>
                </div>
            </div>
            <table class="tech-table-grid">
                <tr>
                    <th class="tech-label-cell" style="width: 25%;">EQUIPO:</th>
                    <td class="tech-val-cell" style="width: 43%;"><div class="tech-val-static">{{ $equipmentName }}</div></td>
                    <td rowspan="4" class="tech-eq-photo-cell">
                        @if($equipmentImage)
                            <img src="{{ $equipmentImage }}" alt="{{ $equipmentName }}" style="max-width: 100%; max-height: 145px; object-fit: contain; display: block; margin: 0 auto;">
                        @else
                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #94a3b8; padding: 8px;">
                                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                    <circle cx="9" cy="9" r="2"/>
                                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                                <span style="font-size: 11px; font-weight: 600; margin-top: 4px;">Sin imagen</span>
                            </div>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th class="tech-label-cell">MARCA:</th>
                    <td class="tech-val-cell"><div class="tech-val-static">{{ $equipmentBrand }}</div></td>
                </tr>
                <tr>
                    <th class="tech-label-cell">MODELO:</th>
                    <td class="tech-val-cell"><div class="tech-val-static tech-val-mono">{{ $equipmentModel }}</div></td>
                </tr>
                <tr>
                    <th class="tech-label-cell">SERIE:</th>
                    <td class="tech-val-cell"><div class="tech-val-static tech-val-mono" style="color: #0284c7;">{{ $equipmentSerial }}</div></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- 3. Tabla Maestra de Mediciones de Iluminación (Sin Observaciones) -->
    <div class="illumination-table-card">
        <!-- Toolbar & Filtros -->
        <div class="illumination-toolbar">
            <div class="table-filter-pills" id="illuminationFilterGroup">
                <button type="button" class="filter-pill-btn active" onclick="filterIllumination('all', this)">
                    Todos ({{ $totalMeasurements }})
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('compliant', this)">
                    Conformes
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('non-compliant', this)">
                    No Conformes
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('Natural', this)">
                    Natural
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('Artificial', this)">
                    Artificial
                </button>
                <button type="button" class="filter-pill-btn" onclick="filterIllumination('Mixta', this)">
                    Mixta
                </button>
            </div>

            <div class="search-box-pill">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-inside">
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input 
                    type="text" 
                    id="illuminationSearchInput" 
                    class="search-input-pill" 
                    placeholder="Buscar por área, puesto, punto, personal o ubicación..."
                    onkeyup="searchIlluminationLive()"
                >
            </div>
        </div>

        <!-- Tabla Responsive -->
        <div class="table-responsive-box">
            <table class="modern-table" id="illuminationMasterTable">
                <thead>
                    <tr>
                        <th style="width: 50px; text-align: center;">N°</th>
                        <th style="width: 110px;">Fecha / Hora</th>
                        <th style="width: 170px;">Área / Puesto</th>
                        <th style="width: 130px;">Punto de Medición</th>
                        <th style="width: 160px;">Descripción de Actividad</th>
                        <th style="width: 115px;">Tipo Iluminación</th>
                        <th style="width: 105px;">Nivel Requerido</th>
                        <th style="width: 130px;">Mediciones (LUX)</th>
                        <th style="width: 80px; text-align: center;">Imágenes</th>
                        <th style="width: 140px;">Registrado por</th>
                        <th style="width: 100px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="illuminationTableBody">
                    @forelse($measurements as $m)
                        <tr class="illumination-data-row"
                            data-compliant="{{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}"
                            data-lighting="{{ $m['lighting_type'] }}"
                            data-search="{{ strtolower($m['num'] . ' ' . $m['area'] . ' ' . $m['workstation'] . ' ' . $m['measurement_point'] . ' ' . $m['activity_description'] . ' ' . $m['lighting_type'] . ' ' . $m['location'] . ' ' . $m['registered_by']) }}">
                            
                            <!-- 1. N° -->
                            <td style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13.5px; text-align: center;">
                                {{ $m['num'] }}
                            </td>

                            <!-- 2. Fecha / Hora (Hora debajo de Fecha) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-size: 12.5px; font-weight: 700; color: var(--ink);">{{ $m['date'] }}</span>
                                    <span style="font-family: monospace; font-size: 11.5px; color: #64748b; display: inline-flex; align-items: center; gap: 4px;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                        {{ $m['time'] }}
                                    </span>
                                </div>
                            </td>

                            <!-- 3. Área / Puesto de Trabajo (Puesto debajo de Área) -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span style="font-weight: 700; color: var(--ink); font-size: 13px;">{{ $m['area'] }}</span>
                                    <span style="font-size: 11.5px; color: #64748b; font-weight: 500;" title="{{ $m['workstation'] }}">{{ $m['workstation'] }}</span>
                                </div>
                            </td>

                            <!-- 4. Punto de Medición -->
                            <td title="{{ $m['measurement_point'] }}" style="font-weight: 600; color: #1e293b;">
                                {{ $m['measurement_point'] }}
                            </td>

                            <!-- 5. Descripción de Actividad -->
                            <td>
                                <span style="font-size: 12px; font-weight: 700; color: #0284c7; background: #f0f9ff; padding: 2px 7px; border-radius: 4px; border: 1px solid #bae6fd;">
                                    {{ $m['activity_description'] }}
                                </span>
                            </td>

                            <!-- 6. Tipo Iluminación -->
                            <td>
                                <span class="lighting-type-tag {{ $m['lighting_type'] }}">
                                    @if($m['lighting_type'] === 'Natural')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/></svg>
                                    @elseif($m['lighting_type'] === 'Artificial')
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6"/><path d="M10 22h4"/><path d="M15.09 14c.18-.98.65-1.74 1.41-2.5A4.65 4.65 0 0 0 18 8 6 6 0 0 0 6 8c0 1 .23 2.23 1.5 3.5.76.76 1.23 1.52 1.41 2.5"/></svg>
                                    @else
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m4.93 4.93 14.14 14.14"/></svg>
                                    @endif
                                    <span>{{ $m['lighting_type'] }}</span>
                                </span>
                            </td>

                            <!-- 7. Nivel Requerido -->
                            <td>
                                <span class="lux-req-code">{{ $m['required_lux'] }} LUX</span>
                            </td>

                            <!-- 8. Mediciones (LUX) con contador de puntos -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <span class="lux-measured-badge {{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}" title="{{ $m['is_compliant'] ? 'Cumple con el nivel requerido' : 'Por debajo del nivel requerido' }}">
                                        @if($m['is_compliant'])
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                        @else
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        @endif
                                        <span>{{ $m['measured_lux'] }} LUX</span>
                                    </span>
                                    @if(!empty($m['readings_count']) && $m['readings_count'] > 1)
                                        <span style="font-size: 10.5px; font-weight: 700; color: #64748b;">{{ $m['readings_count'] }} lecturas prom.</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 9. Imágenes (con indicador de fotos) -->
                            <td style="text-align: center;">
                                @if(!empty($m['image_path']))
                                    <div class="table-thumb-preview" onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Punto {{ $m['num'] }}: {{ addslashes($m['measurement_point']) }}')" title="Ver fotografía ampliada">
                                        <img src="{{ $m['image_path'] }}" alt="Foto">
                                        @if(!empty($m['images_count']) && $m['images_count'] > 1)
                                            <span style="position: absolute; bottom: 2px; right: 2px; background: rgba(15, 23, 42, 0.85); color: #fff; font-size: 9px; font-weight: 800; padding: 1px 4px; border-radius: 4px;">{{ $m['images_count'] }}</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="table-thumb-preview" style="cursor: default; opacity: 0.5;" title="Sin fotografía registrada">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                            <circle cx="9" cy="9" r="2"/>
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>

                            <!-- 10. Registrado por (Personal / Técnico que registró la medición) -->
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 28px; height: 28px; border-radius: 50%; background: #e0f2fe; border: 1.5px solid #bae6fd; color: #0284c7; font-weight: 800; font-size: 11.5px; display: grid; place-items: center; flex-shrink: 0;" title="{{ $m['registered_by'] }}">
                                        {{ strtoupper(substr($m['registered_by'] ?? 'T', 0, 1)) }}
                                    </div>
                                    <div style="display: flex; flex-direction: column; overflow: hidden; line-height: 1.25;">
                                        <span style="font-size: 12px; font-weight: 700; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 125px;" title="{{ $m['registered_by'] }}">
                                            {{ $m['registered_by'] }}
                                        </span>
                                        <span style="font-size: 10px; color: #64748b; font-weight: 600;">Personal</span>
                                    </div>
                                </div>
                            </td>

                            <!-- 11. Acciones (Botones de acción solo icono con estilo idéntico a Equipos) -->
                            <td style="text-align: right;">
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 8px;">
                                    <!-- Ver Detalle -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan" onclick='openViewMeasurementModal(@json($m))' title="Ver detalle del punto" aria-label="Ver">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                    </button>

                                    <!-- Eliminar -->
                                    <button type="button" class="btn-admin-icon-action theme-danger" onclick="confirmDeleteMeasurement('{{ $m['id'] }}', '{{ $m['num'] }}')" title="Eliminar Punto" aria-label="Eliminar">
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
                            <td colspan="11" style="text-align: center; color: #64748b; padding: 36px;">
                                No se han registrado puntos de medición en este módulo de iluminación.
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noResultsSearchRow" style="display: none;">
                        <td colspan="11" style="text-align: center; color: #64748b; padding: 36px;">
                            No se encontraron puntos de medición que coincidan con la búsqueda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Barra de Paginación Reactiva (10 por página) -->
        <div class="illumination-pagination-container" id="illuminationPaginationBar">
            <div class="illumination-pagination-info" id="illuminationPaginationInfo">
                Mostrando <strong id="illPageStart">1</strong> a <strong id="illPageEnd">10</strong> de <strong id="illPageTotal">{{ $totalMeasurements }}</strong> puntos de medición
            </div>
            <div class="illumination-pagination-controls" id="illuminationPaginationControls">
                <!-- Dinámico por JS -->
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 1: NUEVA MEDICIÓN DE ILUMINACIÓN (MODAL GRANDE EN 3 COLUMNAS)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="createMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="createMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/>
                    </svg>
                    <h2 id="createMeasModalTitle">Nuevo Punto de Medición de Iluminación</h2>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCreateMeasurementModal()" aria-label="Cerrar">✕</button>
            </div>
            <form action="{{ route('modules.illumination.measurements.store', $module->id) }}" method="POST" enctype="multipart/form-data" id="createMeasurementForm">
                @csrf
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">
                        
                        <!-- COLUMNA 1: Datos Técnicos y Mediciones LUX -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & LUX</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong id="create_pt_num_disp">{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="create_point_number" value="{{ str_pad($totalMeasurements + 1, 2, '0', STR_PAD_LEFT) }}">

                            <!-- Fecha y Hora (Prominente) -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_date">Fecha de Medición <span class="req">*</span></label>
                                    <input type="date" name="measurement_date" id="create_measurement_date" class="custom-form-input" required value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_measurement_time">Hora</label>
                                    <input type="time" name="measurement_time" id="create_measurement_time" class="custom-form-input" value="{{ date('H:i') }}">
                                </div>
                            </div>

                            <!-- Área y Puesto de Trabajo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_area">Área de Trabajo <span class="req">*</span></label>
                                    <input type="text" name="area" id="create_area" class="custom-form-input" required placeholder="Ej: Planta de Producción">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_workstation">Puesto de Trabajo <span class="req">*</span></label>
                                    <input type="text" name="workstation" id="create_workstation" class="custom-form-input" required placeholder="Ej: Operador de Prensa #1">
                                </div>
                            </div>

                            <!-- 1. Punto de Medición -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_measurement_point">Punto de Medición <span class="req">*</span></label>
                                <input type="text" name="measurement_point" id="create_measurement_point" class="custom-form-input" required placeholder="Ej: Mesa central plano 0.85m">
                            </div>

                            <!-- 2. Descripción de la Actividad (Determina el Nivel Requerido) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_activity_description">
                                    <span>Descripción de la Actividad <span class="req">*</span></span>
                                    <span style="font-size: 11px; color: #0284c7; font-weight: 600;">Tipo de Tarea / Área</span>
                                </label>
                                <select name="activity_description" id="create_activity_description" class="custom-form-select" required onchange="onActivityDescriptionChange('create')">
                                    <option value="Paso en construcción" data-lux="25">Paso en construcción</option>
                                    <option value="Tránsito general" data-lux="50">Tránsito general</option>
                                    <option value="Trabajo en construcción" data-lux="75">Trabajo en construcción</option>
                                    <option value="Tareas simples" data-lux="100">Tareas simples</option>
                                    <option value="Oficinas y talleres" data-lux="300" selected>Oficinas y talleres</option>
                                    <option value="Finos y detalle" data-lux="750">Finos y detalle</option>
                                    <option value="Alta precisión" data-lux="1500">Alta precisión</option>
                                    <option value="Casos especiales" data-lux="3000">Casos especiales</option>
                                </select>
                            </div>

                            <!-- 3. Tipo de Iluminación -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_lighting_type">Tipo de Iluminación <span class="req">*</span></label>
                                <select name="lighting_type" id="create_lighting_type" class="custom-form-select" required>
                                    <option value="Artificial" selected>Artificial</option>
                                    <option value="Natural">Natural</option>
                                    <option value="Mixta">Mixta</option>
                                </select>
                            </div>

                            <!-- 4. Nivel Requerido (Desplegable Normativo sincronizado con Leyenda) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="create_required_lux">
                                    <span>Nivel Requerido (LUX) <span class="req">*</span></span>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 500;">Norma Ocupacional</span>
                                </label>
                                <select name="required_lux" id="create_required_lux" class="custom-form-select" onchange="onRequiredLuxChange('create')">
                                    <option value="25">25 LUX</option>
                                    <option value="50">50 LUX</option>
                                    <option value="75">75 LUX</option>
                                    <option value="100">100 LUX</option>
                                    <option value="300" selected>300 LUX</option>
                                    <option value="750">750 LUX</option>
                                    <option value="1500">1500 LUX</option>
                                    <option value="3000">3000 LUX</option>
                                </select>
                                <!-- Leyenda de aplicación según norma -->
                                <div id="create_normative_legend" class="normative-lux-legend">
                                    <div class="legend-top-row">
                                        <span class="legend-area-name" id="create_legend_area">Oficinas y talleres</span>
                                        <span class="legend-lux-badge" id="create_legend_badge">300 LUX Mínimo</span>
                                    </div>
                                    <p class="legend-app-text" id="create_legend_app">Computadoras, lectura, escritura y trabajos comunes.</p>
                                </div>
                            </div>

                            <!-- 5. Mediciones LUX (Hasta 25 puntos en Badges) -->
                            <div class="form-field-group">
                                <div class="form-field-label">
                                    <span>Lecturas de Luxometría (Hasta 25 puntos)</span>
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7;" id="create_readings_count_badge">0/25</span>
                                </div>
                                <div class="readings-input-bar">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        id="create_quick_lux_input" 
                                        class="custom-form-input" 
                                        placeholder="Ej: 345.5 (Presiona Enter para agregar)"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault(); addReadingPoint('create');}"
                                    >
                                    <button type="button" class="btn-add-reading" onclick="addReadingPoint('create')">+ Agregar</button>
                                </div>
                                <!-- Badges de Puntos Registrados -->
                                <div id="create_readings_badges_container" class="readings-badges-container">
                                    <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas agregadas. Ingrese valores (hasta 25 puntos) o escriba el valor promedio.</span>
                                </div>
                                <div class="readings-summary-strip" id="create_readings_summary_strip">
                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <span>Mín: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Máx: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Promedio: <strong id="create_calc_lux_display" style="color: #94a3b8;">0.0 LUX</strong></span>
                                    </div>
                                    <div>
                                        <span style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Sin datos</span>
                                    </div>
                                </div>
                                <input type="hidden" name="readings" id="create_readings_json" value="[]">
                                <input type="hidden" name="measured_lux" id="create_measured_lux" value="300">
                            </div>

                            <!-- Personal Registrador (Fijo en encabezado, no modificable) -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <label class="form-field-label">Personal Registrador</label>
                                <div class="custom-form-input" style="background: #f8fafc; display: flex; align-items: center; gap: 7px; color: #334155; font-weight: 600; cursor: default;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                    <span>{{ $registeredByHeader }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico TIPO SLIDE / CARRUSEL (Más grande) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Archivo Fotográfico (Slide)</span>
                                <span style="font-size: 11px; color: #64748b;" id="create_photo_count_indicator">0 fotos</span>
                            </div>
                            
                            <div class="modal-photo-slider-wrapper">
                                <div class="modal-slider-viewport" id="create_slider_viewport">
                                    <span id="create_slider_counter" class="slider-counter-badge" style="display: none;">1 / 1</span>
                                    
                                    <button type="button" class="slider-nav-btn prev" id="create_slider_btn_prev" onclick="slidePhotoNav('create', -1)" style="display: none;" aria-label="Anterior">❮</button>
                                    <button type="button" class="slider-nav-btn next" id="create_slider_btn_next" onclick="slidePhotoNav('create', 1)" style="display: none;" aria-label="Siguiente">❯</button>

                                    <img id="create_slider_img" class="modal-slider-main-img" src="" alt="Foto punto" style="display: none;">

                                    <div id="create_slider_placeholder" class="modal-slider-placeholder" onclick="document.getElementById('create_images_input').click()" style="cursor: pointer;">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                            <circle cx="9" cy="9" r="2"/>
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Subir Fotografías del Punto</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">Haz clic para seleccionar una o más imágenes</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="create_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="create_images_input" multiple accept="image/*" style="display: none;" onchange="handleMultipleImagesSelected(this, 'create')">
                                <button type="button" class="btn-add-photos-trigger" onclick="document.getElementById('create_images_input').click()">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    <span>+ Subir / Agregar Fotografías</span>
                                </button>
                            </div>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite subir varias fotos del punto. Puedes pasar imagen por imagen con las flechas.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Dónde Está Ubicado (GPS + Mini Mapa + Observaciones) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button" onclick="getCurrentGpsPosition('create_latitude', 'create_longitude', 'create')" style="background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; display: inline-flex; align-items: center; gap: 3px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/></svg>
                                    Mi GPS
                                </button>
                            </div>

                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_latitude">Latitud</label>
                                    <input type="number" step="any" name="latitude" id="create_latitude" class="custom-form-input" placeholder="-16.5034120" onchange="syncModalMapMarker('create')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="create_longitude">Longitud</label>
                                    <input type="number" step="any" name="longitude" id="create_longitude" class="custom-form-input" placeholder="-68.1324560" onchange="syncModalMapMarker('create')">
                                </div>
                            </div>

                            <!-- Mini Mapa interactivo: Dónde está ubicado -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa (Haz clic para posicionar)</label>
                                <div id="create_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones: Textarea directamente debajo del mapa -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="create_observations">Observaciones</label>
                                <textarea name="observations" id="create_observations" class="custom-form-textarea" rows="3" placeholder="Observaciones técnicas, fuentes de deslumbramiento, estado de luminarias..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle" onclick="closeCreateMeasurementModal()">Cancelar</button>
                    <button type="submit" class="btn-primary-hero-action">Guardar Punto de Medición</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 2: DETALLE / EDICIÓN DE MEDICIÓN (MODO CONSULTA CON BOTÓN EDITAR)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="editMeasurementModal" role="dialog" aria-modal="true" aria-labelledby="editMeasModalTitle">
        <div class="modal-dialog-illumination modal-dialog-lg">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                    <h2 id="editMeasModalTitle">Detalle del Punto de Medición</h2>
                    <span id="modalModeStatusBadge" class="modal-badge-view">Solo Lectura</span>
                    <!-- Personal Registrado al lado de Solo Lectura -->
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 9999px; padding: 3px 10px; font-size: 11.5px; font-weight: 700; color: #334155;" title="Personal Registrador">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span id="edit_modal_registered_by">{{ $registeredByHeader }}</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <!-- Botón para activar/desactivar la edición -->
                    <button type="button" class="btn-modal-edit-action" id="btnToggleEditMode" onclick="toggleModalEditMode()">
                        <span id="btnToggleEditModeIcon" style="display: inline-flex; align-items: center;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
                                <path d="m15 5 4 4"/>
                            </svg>
                        </span>
                        <span id="btnToggleEditModeText">Editar</span>
                    </button>
                    <button type="button" class="btn-close-modal" onclick="closeEditMeasurementModal()" aria-label="Cerrar">✕</button>
                </div>
            </div>
            <form id="editMeasurementForm" action="" method="POST" enctype="multipart/form-data" class="modal-view-mode">
                @csrf
                @method('PUT')
                <div class="modal-body-custom">
                    <div class="modal-three-cols-grid">
                        
                        <!-- COLUMNA 1: Datos Técnicos y Mediciones LUX -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>1. Datos del Punto & LUX</span>
                                <span style="font-size: 11px; font-weight: 700; color: #64748b;">N° <strong id="edit_pt_num_disp">01</strong></span>
                            </div>

                            <input type="hidden" name="point_number" id="edit_point_number">

                            <!-- Fecha y Hora (Prominente) -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_date">Fecha de Medición <span class="req">*</span></label>
                                    <input type="date" name="measurement_date" id="edit_measurement_date" class="custom-form-input" required disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_measurement_time">Hora</label>
                                    <input type="time" name="measurement_time" id="edit_measurement_time" class="custom-form-input" disabled>
                                </div>
                            </div>

                            <!-- Área y Puesto de Trabajo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_area">Área de Trabajo <span class="req">*</span></label>
                                    <input type="text" name="area" id="edit_area" class="custom-form-input" required disabled>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_workstation">Puesto de Trabajo <span class="req">*</span></label>
                                    <input type="text" name="workstation" id="edit_workstation" class="custom-form-input" required disabled>
                                </div>
                            </div>

                            <!-- 1. Punto de Medición -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_measurement_point">Punto de Medición <span class="req">*</span></label>
                                <input type="text" name="measurement_point" id="edit_measurement_point" class="custom-form-input" required disabled>
                            </div>

                            <!-- 2. Descripción de la Actividad & Tipo de Iluminación en paralelo -->
                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_activity_description">
                                        <span>Descripción de Actividad <span class="req">*</span></span>
                                    </label>
                                    <select name="activity_description" id="edit_activity_description" class="custom-form-select" required disabled onchange="onActivityDescriptionChange('edit')">
                                        <option value="Paso en construcción" data-lux="25">Paso en construcción</option>
                                        <option value="Tránsito general" data-lux="50">Tránsito general</option>
                                        <option value="Trabajo en construcción" data-lux="75">Trabajo en construcción</option>
                                        <option value="Tareas simples" data-lux="100">Tareas simples</option>
                                        <option value="Oficinas y talleres" data-lux="300">Oficinas y talleres</option>
                                        <option value="Finos y detalle" data-lux="750">Finos y detalle</option>
                                        <option value="Alta precisión" data-lux="1500">Alta precisión</option>
                                        <option value="Casos especiales" data-lux="3000">Casos especiales</option>
                                    </select>
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_lighting_type">Tipo de Iluminación <span class="req">*</span></label>
                                    <select name="lighting_type" id="edit_lighting_type" class="custom-form-select" required disabled>
                                        <option value="Artificial">Artificial</option>
                                        <option value="Natural">Natural</option>
                                        <option value="Mixta">Mixta</option>
                                    </select>
                                </div>
                            </div>

                            <!-- 3. Nivel Requerido (Sin la leyenda descriptiva) -->
                            <div class="form-field-group">
                                <label class="form-field-label" for="edit_required_lux">
                                    <span>Nivel Requerido (LUX) <span class="req">*</span></span>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 500;">Norma Ocupacional</span>
                                </label>
                                <select name="required_lux" id="edit_required_lux" class="custom-form-select" disabled onchange="onRequiredLuxChange('edit')">
                                    <option value="25">25 LUX</option>
                                    <option value="50">50 LUX</option>
                                    <option value="75">75 LUX</option>
                                    <option value="100">100 LUX</option>
                                    <option value="300">300 LUX</option>
                                    <option value="750">750 LUX</option>
                                    <option value="1500">1500 LUX</option>
                                    <option value="3000">3000 LUX</option>
                                </select>
                            </div>

                            <!-- 4. Mediciones LUX (Hasta 25 puntos en Badges) -->
                            <div class="form-field-group" style="margin-bottom: 0;">
                                <div class="form-field-label">
                                    <span>Lecturas de Luxometría (Hasta 25 puntos)</span>
                                    <span style="font-size: 11px; font-weight: 700; color: #0284c7;" id="edit_readings_count_badge">0/25</span>
                                </div>
                                <div class="readings-input-bar" id="edit_readings_input_bar" style="display: none;">
                                    <input 
                                        type="number" 
                                        step="0.1" 
                                        id="edit_quick_lux_input" 
                                        class="custom-form-input" 
                                        placeholder="Ej: 345.5 (Presiona Enter para agregar)"
                                        onkeydown="if(event.key==='Enter'){event.preventDefault(); addReadingPoint('edit');}"
                                    >
                                    <button type="button" class="btn-add-reading" onclick="addReadingPoint('edit')">+ Agregar</button>
                                </div>
                                <!-- Badges de Puntos Registrados -->
                                <div id="edit_readings_badges_container" class="readings-badges-container">
                                    <span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas agregadas.</span>
                                </div>
                                <div class="readings-summary-strip" id="edit_readings_summary_strip">
                                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                        <span>Mín: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Máx: <strong style="color: #94a3b8;">—</strong></span>
                                        <span style="color: #cbd5e1;">|</span>
                                        <span>Promedio: <strong id="edit_calc_lux_display" style="color: #94a3b8;">0.0 LUX</strong></span>
                                    </div>
                                    <div>
                                        <span style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Sin datos</span>
                                    </div>
                                </div>
                                <input type="hidden" name="readings" id="edit_readings_json" value="[]">
                                <input type="hidden" name="measured_lux" id="edit_measured_lux" value="0">
                            </div>
                        </div>

                        <!-- COLUMNA 2: Archivo Fotográfico TIPO SLIDE / CARRUSEL (Más grande) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>2. Archivo Fotográfico (Slide)</span>
                                <span style="font-size: 11px; color: #64748b;" id="edit_photo_count_indicator">0 fotos</span>
                            </div>
                            
                            <div class="modal-photo-slider-wrapper">
                                <div class="modal-slider-viewport" id="edit_slider_viewport">
                                    <span id="edit_slider_counter" class="slider-counter-badge" style="display: none;">1 / 1</span>
                                    
                                    <button type="button" class="slider-nav-btn prev" id="edit_slider_btn_prev" onclick="slidePhotoNav('edit', -1)" style="display: none;" aria-label="Anterior">❮</button>
                                    <button type="button" class="slider-nav-btn next" id="edit_slider_btn_next" onclick="slidePhotoNav('edit', 1)" style="display: none;" aria-label="Siguiente">❯</button>

                                    <img id="edit_slider_img" class="modal-slider-main-img" src="" alt="Foto punto" style="display: none;">

                                    <div id="edit_slider_placeholder" class="modal-slider-placeholder">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px;">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                            <circle cx="9" cy="9" r="2"/>
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                        </svg>
                                        <strong style="font-size: 13.5px; color: #cbd5e1; margin-bottom: 4px;">Sin Fotografía</strong>
                                        <span style="font-size: 11.5px; color: #94a3b8;">No se registraron imágenes para este punto</span>
                                    </div>
                                </div>

                                <div class="slider-thumbs-strip" id="edit_slider_thumbs" style="display: none;"></div>

                                <input type="file" name="images[]" id="edit_images_input" multiple accept="image/*" style="display: none;" onchange="handleMultipleImagesSelected(this, 'edit')">
                                <button type="button" class="btn-add-photos-trigger" id="edit_btn_add_photos" onclick="document.getElementById('edit_images_input').click()" style="display: none;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                                    <span>+ Subir / Agregar Nuevas Fotos</span>
                                </button>
                            </div>
                            <span style="font-size: 11.5px; color: #64748b; line-height: 1.4;">Permite visualizar todas las fotos del punto en modo carrusel continuo.</span>
                        </div>

                        <!-- COLUMNA 3: Ubicación Geográfica & Dónde Está Ubicado (GPS + Mini Mapa + Observaciones) -->
                        <div class="modal-col-card">
                            <div class="modal-col-heading">
                                <span>3. Ubicación & GPS</span>
                                <button type="button" id="edit_btn_gps" onclick="getCurrentGpsPosition('edit_latitude', 'edit_longitude', 'edit')" style="display: none; background: none; border: none; color: #0284c7; font-size: 11px; font-weight: 700; cursor: pointer; align-items: center; gap: 3px;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/></svg>
                                    Mi GPS
                                </button>
                            </div>

                            <div class="form-grid-two-cols">
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_latitude">Latitud</label>
                                    <input type="number" step="any" name="latitude" id="edit_latitude" class="custom-form-input" placeholder="-16.5034120" disabled onchange="syncModalMapMarker('edit')">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label" for="edit_longitude">Longitud</label>
                                    <input type="number" step="any" name="longitude" id="edit_longitude" class="custom-form-input" placeholder="-68.1324560" disabled onchange="syncModalMapMarker('edit')">
                                </div>
                            </div>

                            <!-- Mini Mapa interactivo: Dónde está ubicado -->
                            <div>
                                <label class="form-field-label" style="margin-bottom: 4px;">Ubicación en Mapa</label>
                                <div id="edit_modal_map" class="modal-minimap-container"></div>
                            </div>

                            <!-- Observaciones: Textarea directamente debajo del mapa -->
                            <div class="form-field-group" style="margin-top: 6px;">
                                <label class="form-field-label" for="edit_observations">Observaciones</label>
                                <textarea name="observations" id="edit_observations" class="custom-form-textarea" rows="3" disabled placeholder="Observaciones técnicas, fuentes de deslumbramiento, estado de luminarias..."></textarea>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" class="btn-secondary-subtle" onclick="closeEditMeasurementModal()">Cerrar</button>
                    <button type="submit" class="btn-primary-hero-action" id="edit_modal_submit_btn" style="display: none;">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 3: VISOR DE MAPA Y FOTOGRAFÍA DEL PUNTO (MAPS)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="mapLocationModal" role="dialog" aria-modal="true" aria-labelledby="mapModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 860px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="mapModalTitle" style="font-size: 17px; margin: 0;">Ubicación del Punto en Mapa</h2>
                        <span id="mapModalSubtitle" style="font-size: 12px; color: #64748b; font-weight: 500;">Punto de Medición</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeMapModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <!-- Panel de Información y Fotografía -->
                <div class="map-card-info-grid">
                    <div>
                        <div style="font-size: 14px; font-weight: 800; color: var(--ink); margin-bottom: 3px;" id="mapCardPointName">Área / Puesto</div>
                        <div style="font-size: 12.5px; color: #64748b; margin-bottom: 6px;" id="mapCardLocationDesc">Ubicación física</div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span id="mapCardLuxBadge" class="lux-measured-badge compliant" style="font-size: 11.5px; padding: 3px 8px;">345 LUX</span>
                            <span id="mapCardCoords" style="font-family: monospace; font-size: 11.5px; background: #ffffff; border: 1px solid #cbd5e1; padding: 3px 7px; border-radius: 6px; color: #334155;">-16.5034, -68.1324</span>
                        </div>
                    </div>

                    <!-- Miniatura de Foto en el Mapa si existe -->
                    <div id="mapModalPhotoThumbWrap" style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px;">
                        <div id="mapModalPhotoThumb" class="table-thumb-preview" style="width: 58px; height: 58px; border-radius: 10px;" onclick="expandCurrentMapPhoto()" title="Clic para ampliar fotografía">
                            <img id="mapModalPhotoImg" src="" alt="Fotografía">
                        </div>
                        <span style="font-size: 10.5px; font-weight: 700; color: #0284c7; cursor: pointer;" onclick="expandCurrentMapPhoto()">Ver Foto</span>
                    </div>
                </div>

                <!-- Contenedor del Mapa Leaflet -->
                <div id="mapContainerLeaflet"></div>
            </div>

            <div class="modal-footer-custom" style="justify-content: space-between;">
                <a id="openInGoogleMapsBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn-secondary-subtle" style="font-size: 12px; padding: 7px 14px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>Abrir en Google Maps</span>
                </a>
                <button type="button" class="btn-primary-hero-action" onclick="closeMapModal()" style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 4: VISOR DE FOTOGRAFÍA AMPLIFICADA
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoViewerModal" onclick="closePhotoViewer()" role="dialog" aria-modal="true">
        <div class="modal-dialog-illumination" style="max-width: 600px; background: transparent; box-shadow: none;" onclick="event.stopPropagation()">
            <div style="position: relative; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.4);">
                <div style="padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0;">
                    <h3 id="photoViewerTitle" style="font-size: 14px; font-weight: 800; color: var(--ink); margin: 0;">Fotografía del Punto</h3>
                    <button type="button" class="btn-close-modal" onclick="closePhotoViewer()">✕</button>
                </div>
                <div style="padding: 12px; display: grid; place-items: center; background: #0f172a;">
                    <img id="photoViewerImg" src="" alt="Fotografía" style="max-width: 100%; max-height: 65vh; object-fit: contain; border-radius: 8px;">
                </div>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 5: OPCIONES DE EXPORTACIÓN (PLANILLA EXCEL & REPORTE FOTOGRÁFICO)
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="exportOptionsModal" role="dialog" aria-modal="true" aria-labelledby="exportModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 680px;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 34px; height: 34px; border-radius: 9px; background: #ecfdf5; color: #059669; display: grid; place-items: center;">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="exportModalTitle" style="font-size: 17px; margin: 0;">Exportar Monitoreo de Iluminación</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Selecciona el formato de exportación para este estudio</span>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeExportModal()" aria-label="Cerrar">✕</button>
            </div>

            <div class="modal-body-custom" style="padding: 22px 24px;">
                <div class="export-options-grid">
                    <!-- Opción 1: Planilla Excel -->
                    <div class="export-option-card excel-theme" onclick="downloadExcelPlanilla()">
                        <div>
                            <div class="export-card-top">
                                <div class="export-card-icon excel-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="8" y1="13" x2="16" y2="13"/>
                                        <line x1="8" y1="17" x2="16" y2="17"/>
                                        <polyline points="10 9 9 9 8 9"/>
                                    </svg>
                                </div>
                                <div class="export-card-body">
                                    <span class="export-card-badge badge-excel">Planilla Oficial .xlsx</span>
                                    <h3>Planilla Técnica Excel</h3>
                                    <p>Encabezado corporativo azul, datos del luxómetro, columnas combinadas, lecturas M1-M16, cálculo estadístico y evaluación de riesgos.</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="export-card-btn excel-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            <span>Descargar Planilla (.xlsx)</span>
                        </button>
                    </div>

                    <!-- Opción 2: Reporte Fotográfico -->
                    <div class="export-option-card" onclick="openPhotoReportModal()">
                        <div>
                            <div class="export-card-top">
                                <div class="export-card-icon photo-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                                        <circle cx="12" cy="13" r="4"/>
                                    </svg>
                                </div>
                                <div class="export-card-body">
                                    <span class="export-card-badge badge-photo">Catálogo Visual</span>
                                    <h3>Reporte Fotográfico</h3>
                                    <p>Catálogo visual con todas las evidencias fotográficas, coordenadas GPS, personal registrador, lecturas LUX y observaciones por punto.</p>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="export-card-btn photo-btn">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                <circle cx="9" cy="9" r="2"/>
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                            </svg>
                            <span>Ver Reporte Fotográfico</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-secondary-subtle" onclick="closeExportModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 6: MAPA GENERAL DE TODAS LAS UBICACIONES Y PERSONAL REGISTRADOR
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="allLocationsModal" role="dialog" aria-modal="true" aria-labelledby="allLocationsModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1100px; width: 95%;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="allLocationsModalTitle" style="font-size: 17px; margin: 0;">Ubicaciones de Puntos de Medición</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Puntos de luxometría geolocalizados y personal técnico registrador</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 12px; font-weight: 800; background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px;">
                        {{ $totalMeasurements }} Puntos Registrados
                    </span>
                    <button type="button" class="btn-close-modal" onclick="closeAllLocationsModal()" aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="modal-body-custom" style="padding: 18px 22px;">
                <div class="all-loc-modal-grid">
                    <!-- Mapa Leaflet Interactivo -->
                    <div>
                        <div id="allLocationsMapLeaflet"></div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 10px; font-size: 12px; color: #64748b;">
                            <div style="display: flex; align-items: center; gap: 14px;">
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #059669; display: inline-block;"></span>
                                    <span>Cumple valor requerido</span>
                                </span>
                                <span style="display: inline-flex; align-items: center; gap: 5px;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #dc2626; display: inline-block;"></span>
                                    <span>No cumple</span>
                                </span>
                            </div>
                            <span style="font-size: 11.5px; color: #94a3b8;">Haz clic en un marcador para ver el personal que registró el punto</span>
                        </div>
                    </div>

                    <!-- Panel Lateral con Lista de Puntos y Personal Registrador -->
                    <div class="all-loc-sidebar">
                        <div style="padding: 4px 6px; font-size: 12.5px; font-weight: 800; color: var(--ink); display: flex; align-items: center; justify-content: space-between;">
                            <span>Puntos Registrados</span>
                            <span style="font-size: 11px; color: #0284c7;">Clic para enfocar</span>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @forelse($measurements as $idx => $m)
                                <div class="all-loc-point-item" onclick="focusPointOnAllLocationsMap({{ $idx }})">
                                    <div class="all-loc-point-top">
                                        <div style="display: flex; align-items: center; gap: 6px;">
                                            <span style="font-size: 11.5px; font-weight: 800; background: {{ $m['is_compliant'] ? '#ecfdf5' : '#fef2f2' }}; color: {{ $m['is_compliant'] ? '#065f46' : '#991b1b' }}; border: 1px solid {{ $m['is_compliant'] ? '#a7f3d0' : '#fecaca' }}; padding: 1px 6px; border-radius: 4px;">
                                                #{{ $m['num'] }}
                                            </span>
                                            <strong style="font-size: 12.5px; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">
                                                {{ $m['measurement_point'] }}
                                            </strong>
                                        </div>
                                        <span style="font-size: 11px; font-weight: 700; color: {{ $m['is_compliant'] ? '#059669' : '#dc2626' }};">
                                            {{ $m['measured_lux'] }} LUX
                                        </span>
                                    </div>
                                    <div style="font-size: 11.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $m['area'] }} • {{ $m['workstation'] }}
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 3px; font-size: 11.5px; color: #0284c7; background: #f0f9ff; padding: 4px 8px; border-radius: 6px; border: 1px solid #e0f2fe;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                        <span style="font-weight: 600; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                            Registrado por: <strong>{{ $m['registered_by'] }}</strong>
                                        </span>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align: center; color: #94a3b8; padding: 20px; font-size: 12.5px;">
                                    No hay puntos registrados en este módulo.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: flex-end;">
                <button type="button" class="btn-primary-hero-action" onclick="closeAllLocationsModal()" style="padding: 8px 18px; font-size: 13px;">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
         MODAL 7: REPORTE FOTOGRÁFICO DE PUNTOS DE MONITOREO
         ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoReportModal" role="dialog" aria-modal="true" aria-labelledby="photoReportModalTitle">
        <div class="modal-dialog-illumination" style="max-width: 1150px; width: 96%;">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 36px; height: 36px; border-radius: 9px; background: #e0f2fe; color: #0284c7; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="photoReportModalTitle" style="font-size: 17px; margin: 0;">Reporte Fotográfico — Niveles de Iluminación</h2>
                        <span style="font-size: 12px; color: #64748b; font-weight: 500;">Memoria visual y técnica de los puntos de medición evaluados</span>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <button type="button" class="btn-secondary-subtle btn-print-action" onclick="window.print()" title="Imprimir o guardar como PDF">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <rect width="12" height="8" x="6" y="14"/>
                        </svg>
                        <span>Imprimir / PDF</span>
                    </button>
                    <button type="button" class="btn-close-modal" onclick="closePhotoReportModal()" aria-label="Cerrar">✕</button>
                </div>
            </div>

            <div class="photo-report-container">
                <!-- Banner de Datos Técnicos de Estudio -->
                <div class="photo-report-header-banner">
                    <div>
                        <div style="margin-bottom: 4px;"><strong>INSTALACIÓN:</strong> <span style="color: var(--ink);">{{ $installationName }}</span></div>
                        <div style="margin-bottom: 4px;"><strong>FECHA DE MONITOREO:</strong> <span>{{ $startDateFormatted }} @if($endDateFormatted && $endDateFormatted !== $startDateFormatted) al {{ $endDateFormatted }} @endif</span></div>
                        <div><strong>TIPO DE MONITOREO:</strong> <span>{{ $monitoringType }}</span></div>
                    </div>
                    <div>
                        <div style="margin-bottom: 4px;"><strong>EQUIPO:</strong> <span>{{ $equipmentName }}</span></div>
                        <div style="margin-bottom: 4px;"><strong>MARCA / MODELO:</strong> <span>{{ $equipmentBrand }} / {{ $equipmentModel }}</span></div>
                        <div><strong>SERIE:</strong> <span style="font-family: monospace; color: #0284c7;">{{ $equipmentSerial }}</span></div>
                    </div>
                </div>

                <!-- Grilla de Tarjetas Fotográficas -->
                <div class="photo-report-grid">
                    @forelse($measurements as $m)
                        <div class="photo-card-item">
                            <div class="photo-card-top-bar">
                                <div style="display: flex; align-items: center; gap: 7px;">
                                    <span style="font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 13px; color: var(--ink);">
                                        Punto #{{ $m['num'] }}
                                    </span>
                                    <span style="font-size: 11px; color: #64748b; font-weight: 600;">
                                        {{ $m['measurement_point'] }}
                                    </span>
                                </div>
                                <span class="lux-measured-badge {{ $m['is_compliant'] ? 'compliant' : 'non-compliant' }}" style="font-size: 11px; padding: 2px 7px;">
                                    {{ $m['is_compliant'] ? 'CUMPLE' : 'NO CUMPLE' }}
                                </span>
                            </div>

                            <!-- Imagen del punto -->
                            <div class="photo-card-img-wrap">
                                @if(!empty($m['image_path']))
                                    <img src="{{ $m['image_path'] }}" alt="Punto {{ $m['num'] }}" onclick="openPhotoViewer('{{ $m['image_path'] }}', 'Punto #{{ $m['num'] }}: {{ addslashes($m['measurement_point']) }}')" style="cursor: zoom-in;">
                                @else
                                    <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; color: #64748b;">
                                        <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/>
                                            <circle cx="9" cy="9" r="2"/>
                                            <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                        </svg>
                                        <span style="font-size: 11px; margin-top: 4px; font-weight: 600;">Sin fotografía registrada</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Datos del Punto -->
                            <div class="photo-card-meta">
                                <div class="photo-meta-row">
                                    <span class="photo-meta-lbl">Área / Puesto:</span>
                                    <span class="photo-meta-val">{{ $m['area'] }} — {{ $m['workstation'] }}</span>
                                </div>
                                <div class="photo-meta-row">
                                    <span class="photo-meta-lbl">Actividad:</span>
                                    <span class="photo-meta-val">{{ $m['activity_description'] }}</span>
                                </div>
                                <div class="photo-meta-row">
                                    <span class="photo-meta-lbl">Iluminación / LUX:</span>
                                    <span class="photo-meta-val">
                                        {{ $m['lighting_type'] }} • <strong>{{ $m['measured_lux'] }} LUX</strong> <span style="font-size: 10.5px; color: #64748b;">(Req: {{ $m['required_lux'] }} LUX)</span>
                                    </span>
                                </div>
                                <div class="photo-meta-row">
                                    <span class="photo-meta-lbl">Registrado por:</span>
                                    <span class="photo-meta-val" style="color: #0284c7;">
                                        {{ $m['registered_by'] }}
                                    </span>
                                </div>
                                <div class="photo-meta-row">
                                    <span class="photo-meta-lbl">Fecha / Hora:</span>
                                    <span class="photo-meta-val" style="font-family: monospace; font-size: 11.5px;">{{ $m['date'] }} {{ $m['time'] }}</span>
                                </div>
                                @if(!empty($m['latitude']) && !empty($m['longitude']))
                                    <div class="photo-meta-row">
                                        <span class="photo-meta-lbl">GPS:</span>
                                        <span class="photo-meta-val" style="font-family: monospace; font-size: 11px;">{{ number_format((float)$m['latitude'], 6) }}, {{ number_format((float)$m['longitude'], 6) }}</span>
                                    </div>
                                @endif
                                @if(!empty($m['observations']) && $m['observations'] !== 'Sin observaciones')
                                    <div class="photo-meta-row">
                                        <span class="photo-meta-lbl">Observaciones:</span>
                                        <span class="photo-meta-val" style="font-style: italic; font-weight: 500;">{{ $m['observations'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #94a3b8;">
                            No hay mediciones registradas para generar el reporte fotográfico.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="modal-footer-custom" style="justify-content: space-between;">
                <span style="font-size: 12px; color: #64748b;">METRIC v2 Pachabol — Módulo de Iluminación Ocupacional</span>
                <button type="button" class="btn-secondary-subtle" onclick="closePhotoReportModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- Formulario oculto para eliminar -->
    <form id="deleteMeasurementForm" action="" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@push('scripts')
<!-- Leaflet JS for Maps -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<!-- ExcelJS for High-Fidelity Excel Export -->
<script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>

<script>
    const ALL_MEASUREMENTS_DATA = @json($measurements);
    const TECHNICAL_HEADER_DATA = {
        installationName: @json($installationName),
        startDateFormatted: @json($startDateFormatted),
        endDateFormatted: @json($endDateFormatted),
        monitoringType: @json($monitoringType),
        equipmentName: @json($equipmentName),
        equipmentBrand: @json($equipmentBrand),
        equipmentModel: @json($equipmentModel),
        equipmentSerial: @json($equipmentSerial)
    };

    /* ==========================================================================
       DATOS NORMATIVOS PARA NIVEL REQUERIDO Y LEYENDA DINÁMICA
       ========================================================================== */
    const NORMATIVE_DATA = {
        "25": { area: "Paso en construcción", app: "Pasillos y vías en obras." },
        "50": { area: "Tránsito general", app: "Pasillos, almacenes y baños." },
        "75": { area: "Trabajo en construcción", app: "Áreas operativas dentro de la obra." },
        "100": { area: "Tareas simples", app: "Mover materiales y supervisar maquinaria." },
        "300": { area: "Oficinas y talleres", app: "Computadoras, lectura, escritura y trabajos comunes." },
        "750": { area: "Finos y detalle", app: "Pintura de detalle e inspección de piezas pequeñas." },
        "1500": { area: "Alta precisión", app: "Ensamble de piezas minúsculas o diminutas." },
        "3000": { area: "Casos especiales", app: "Joyería, electrónica fina y cirugías." }
    };

    const ACTIVITY_TO_LUX = {
        "Paso en construcción": 25,
        "Tránsito general": 50,
        "Trabajo en construcción": 75,
        "Tareas simples": 100,
        "Oficinas y talleres": 300,
        "Finos y detalle": 750,
        "Alta precisión": 1500,
        "Casos especiales": 3000
    };

    const LUX_TO_ACTIVITY = {
        "25": "Paso en construcción",
        "50": "Tránsito general",
        "75": "Trabajo en construcción",
        "100": "Tareas simples",
        "300": "Oficinas y talleres",
        "750": "Finos y detalle",
        "1500": "Alta precisión",
        "3000": "Casos especiales"
    };

    function onActivityDescriptionChange(prefix) {
        const actSelect = document.getElementById(`${prefix}_activity_description`);
        const reqSelect = document.getElementById(`${prefix}_required_lux`);
        if (!actSelect || !reqSelect) return;

        const activity = actSelect.value;
        if (ACTIVITY_TO_LUX[activity]) {
            reqSelect.value = String(ACTIVITY_TO_LUX[activity]);
        }
        updateNormativeLegend(prefix);
        renderReadingsBadges(prefix);
    }

    function onRequiredLuxChange(prefix) {
        const actSelect = document.getElementById(`${prefix}_activity_description`);
        const reqSelect = document.getElementById(`${prefix}_required_lux`);
        if (!reqSelect) return;

        const val = String(parseInt(reqSelect.value) || 300);
        if (actSelect && LUX_TO_ACTIVITY[val]) {
            actSelect.value = LUX_TO_ACTIVITY[val];
        }
        updateNormativeLegend(prefix);
        renderReadingsBadges(prefix);
    }

    function updateNormativeLegend(prefix) {
        const select = document.getElementById(`${prefix}_required_lux`);
        const areaEl = document.getElementById(`${prefix}_legend_area`);
        const badgeEl = document.getElementById(`${prefix}_legend_badge`);
        const appEl = document.getElementById(`${prefix}_legend_app`);
        if (!select) return;

        const val = String(parseInt(select.value) || 300);
        if (NORMATIVE_DATA[val]) {
            if (areaEl) areaEl.textContent = NORMATIVE_DATA[val].area;
            if (badgeEl) badgeEl.textContent = `${val} LUX Mínimo`;
            if (appEl) appEl.textContent = NORMATIVE_DATA[val].app;
        } else {
            if (areaEl) areaEl.textContent = "Personalizado";
            if (badgeEl) badgeEl.textContent = `${select.value} LUX`;
            if (appEl) appEl.textContent = "Valor normativo específico según criterio técnico.";
        }
    }

    /* ==========================================================================
       GESTIÓN DE MEDICIONES LUX (HASTA 25 PUNTOS EN BADGES)
       ========================================================================== */
    let createReadingsList = [];
    let editReadingsList = [];
    let isEditUnlocked = false;

    function addReadingPoint(prefix) {
        if (prefix === 'edit' && !isEditUnlocked) return;
        const list = (prefix === 'create') ? createReadingsList : editReadingsList;
        if (list.length >= 25) {
            alert('Límite alcanzado: Máximo 25 puntos de medición.');
            return;
        }

        const input = document.getElementById(`${prefix}_quick_lux_input`);
        if (!input) return;
        const val = parseFloat(input.value);
        if (isNaN(val) || val < 0) {
            alert('Ingrese un valor numérico de LUX válido.');
            input.focus();
            return;
        }

        list.push(val);
        input.value = '';
        input.focus();
        renderReadingsBadges(prefix);
    }

    function removeReadingPoint(prefix, index) {
        if (prefix === 'edit' && !isEditUnlocked) return;
        const list = (prefix === 'create') ? createReadingsList : editReadingsList;
        list.splice(index, 1);
        renderReadingsBadges(prefix);
    }

    function renderReadingsBadges(prefix) {
        const list = (prefix === 'create') ? createReadingsList : editReadingsList;
        const container = document.getElementById(`${prefix}_readings_badges_container`);
        const countBadge = document.getElementById(`${prefix}_readings_count_badge`);
        const displayEl = document.getElementById(`${prefix}_calc_lux_display`);
        const hiddenJson = document.getElementById(`${prefix}_readings_json`);
        const hiddenMeasured = document.getElementById(`${prefix}_measured_lux`);
        const reqSelect = document.getElementById(`${prefix}_required_lux`);

        if (!container) return;

        if (countBadge) countBadge.textContent = `${list.length}/25`;
        if (hiddenJson) hiddenJson.value = JSON.stringify(list);

        if (list.length === 0) {
            container.innerHTML = `<span style="font-size: 11.5px; color: #94a3b8; font-style: italic;">Sin lecturas agregadas. Ingrese valores (hasta 25 puntos).</span>`;
            if (displayEl) displayEl.textContent = '0.0 LUX';
            if (hiddenMeasured) hiddenMeasured.value = '0';
            const strip = document.getElementById(`${prefix}_readings_summary_strip`);
            if (strip) {
                strip.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        <span>Mín: <strong style="color: #94a3b8;">—</strong></span>
                        <span style="color: #cbd5e1;">|</span>
                        <span>Máx: <strong style="color: #94a3b8;">—</strong></span>
                        <span style="color: #cbd5e1;">|</span>
                        <span>Promedio: <strong id="${prefix}_calc_lux_display" style="color: #94a3b8;">0.0 LUX</strong></span>
                    </div>
                    <div>
                        <span style="font-size: 10.5px; padding: 2px 7px; border-radius: 4px; font-weight: 700; background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1;">Sin datos</span>
                    </div>
                `;
            }
            return;
        }

        let sum = 0;
        let badgesHtml = '';
        const canDelete = (prefix === 'create' || isEditUnlocked);

        list.forEach((val, idx) => {
            sum += val;
            badgesHtml += `
                <span class="lux-reading-badge">
                    <span>#${idx + 1}: <strong>${val.toFixed(1)}</strong></span>
                    ${canDelete ? `<button type="button" onclick="removeReadingPoint('${prefix}', ${idx})" title="Eliminar lectura #${idx + 1}">✕</button>` : ''}
                </span>
            `;
        });

        container.innerHTML = badgesHtml;

        const minVal = Math.min(...list);
        const maxVal = Math.max(...list);
        const avg = sum / list.length;
        const req = reqSelect ? (parseFloat(reqSelect.value) || 300) : 300;
        const compliant = avg >= req;

        if (hiddenMeasured) hiddenMeasured.value = avg.toFixed(1);

        const strip = document.getElementById(`${prefix}_readings_summary_strip`);
        if (strip) {
            strip.innerHTML = `
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span>Mín: <strong style="color: var(--ink); font-weight: 800;">${minVal.toFixed(1)} LUX</strong></span>
                    <span style="color: #cbd5e1;">|</span>
                    <span>Máx: <strong style="color: var(--ink); font-weight: 800;">${maxVal.toFixed(1)} LUX</strong></span>
                    <span style="color: #cbd5e1;">|</span>
                    <span>Promedio: <strong id="${prefix}_calc_lux_display" style="color: #0284c7; font-weight: 800; font-size: 12.5px;">${avg.toFixed(1)} LUX</strong></span>
                </div>
                <div>
                    <span style="font-size: 10.5px; padding: 2px 8px; border-radius: 4px; font-weight: 800; display: inline-flex; align-items: center; gap: 3px; background: ${compliant ? '#ecfdf5' : '#fef2f2'}; color: ${compliant ? '#059669' : '#dc2626'}; border: 1px solid ${compliant ? '#a7f3d0' : '#fecaca'};">
                        ${compliant 
                            ? '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> CUMPLE' 
                            : '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> NO CUMPLE'
                        }
                    </span>
                </div>
            `;
        }
    }

    /* ==========================================================================
       ARCHIVO FOTOGRÁFICO SLIDE / CARRUSEL CONTINUO (MÚLTIPLES FOTOS)
       ========================================================================== */
    const modalPhotos = {
        create: [],
        edit: []
    };
    const modalPhotoIndex = {
        create: 0,
        edit: 0
    };

    function renderPhotoSlider(prefix) {
        const photos = modalPhotos[prefix] || [];
        const count = photos.length;
        let idx = modalPhotoIndex[prefix] || 0;

        if (idx >= count) idx = Math.max(0, count - 1);
        if (idx < 0) idx = 0;
        modalPhotoIndex[prefix] = idx;

        const img = document.getElementById(`${prefix}_slider_img`);
        const placeholder = document.getElementById(`${prefix}_slider_placeholder`);
        const counter = document.getElementById(`${prefix}_slider_counter`);
        const prevBtn = document.getElementById(`${prefix}_slider_btn_prev`);
        const nextBtn = document.getElementById(`${prefix}_slider_btn_next`);
        const thumbsStrip = document.getElementById(`${prefix}_slider_thumbs`);
        const countIndicator = document.getElementById(`${prefix}_photo_count_indicator`);

        if (countIndicator) {
            countIndicator.textContent = count === 1 ? '1 foto' : `${count} fotos`;
        }

        if (count === 0) {
            if (img) { img.src = ''; img.style.display = 'none'; }
            if (placeholder) placeholder.style.display = 'flex';
            if (counter) counter.style.display = 'none';
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            if (thumbsStrip) { thumbsStrip.innerHTML = ''; thumbsStrip.style.display = 'none'; }
            return;
        }

        if (placeholder) placeholder.style.display = 'none';
        if (img) {
            img.src = photos[idx];
            img.style.display = 'block';
            img.onclick = () => openPhotoViewer(photos[idx], `Fotografía ${idx + 1} de ${count}`);
            img.style.cursor = 'zoom-in';
        }

        if (counter) {
            counter.textContent = `${idx + 1} / ${count}`;
            counter.style.display = 'inline-block';
        }

        // Botones de navegación previa / siguiente
        if (prevBtn) prevBtn.style.display = count > 1 ? 'grid' : 'none';
        if (nextBtn) nextBtn.style.display = count > 1 ? 'grid' : 'none';

        // Tira de miniaturas
        if (thumbsStrip) {
            if (count > 1) {
                thumbsStrip.style.display = 'flex';
                let thumbsHtml = '';
                photos.forEach((src, i) => {
                    thumbsHtml += `
                        <div class="slider-thumb-item ${i === idx ? 'active' : ''}" onclick="selectSlidePhoto('${prefix}', ${i})" title="Foto #${i + 1}">
                            <img src="${src}" alt="Thumb ${i + 1}">
                        </div>
                    `;
                });
                thumbsStrip.innerHTML = thumbsHtml;
            } else {
                thumbsStrip.innerHTML = '';
                thumbsStrip.style.display = 'none';
            }
        }
    }

    function slidePhotoNav(prefix, direction) {
        const photos = modalPhotos[prefix] || [];
        if (photos.length <= 1) return;
        let idx = modalPhotoIndex[prefix] + direction;
        if (idx < 0) idx = photos.length - 1;
        if (idx >= photos.length) idx = 0;
        modalPhotoIndex[prefix] = idx;
        renderPhotoSlider(prefix);
    }

    function selectSlidePhoto(prefix, index) {
        modalPhotoIndex[prefix] = index;
        renderPhotoSlider(prefix);
    }

    function handleMultipleImagesSelected(input, prefix) {
        if (!input.files || input.files.length === 0) return;
        const files = Array.from(input.files);
        let loadedCount = 0;

        if (prefix === 'create') {
            modalPhotos.create = [];
        }

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = function(e) {
                modalPhotos[prefix].push(e.target.result);
                loadedCount++;
                if (loadedCount === files.length) {
                    modalPhotoIndex[prefix] = modalPhotos[prefix].length - 1;
                    renderPhotoSlider(prefix);
                }
            };
            reader.readAsDataURL(file);
        });
    }

    /* ==========================================================================
       MINI MAPAS INTERACTIVOS DENTRO DEL MODAL (COLUMNA 3: DÓNDE ESTÁ UBICADO)
       ========================================================================== */
    let createModalMap = null;
    let createModalMarker = null;
    let editModalMap = null;
    let editModalMarker = null;

    function initModalMiniMap(prefix, lat, lng) {
        const mapContainerId = `${prefix}_modal_map`;
        const container = document.getElementById(mapContainerId);
        if (!container) return;

        lat = parseFloat(lat) || -16.5034120;
        lng = parseFloat(lng) || -68.1324560;

        if (prefix === 'create') {
            if (!createModalMap) {
                createModalMap = L.map(mapContainerId).setView([lat, lng], 16);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(createModalMap);

                createModalMarker = L.marker([lat, lng], { draggable: true }).addTo(createModalMap);

                createModalMarker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    document.getElementById('create_latitude').value = pos.lat.toFixed(7);
                    document.getElementById('create_longitude').value = pos.lng.toFixed(7);
                });

                createModalMap.on('click', (e) => {
                    createModalMarker.setLatLng(e.latlng);
                    document.getElementById('create_latitude').value = e.latlng.lat.toFixed(7);
                    document.getElementById('create_longitude').value = e.latlng.lng.toFixed(7);
                });
            } else {
                createModalMap.invalidateSize();
                createModalMap.setView([lat, lng], 16);
                createModalMarker.setLatLng([lat, lng]);
            }
        } else {
            if (!editModalMap) {
                editModalMap = L.map(mapContainerId).setView([lat, lng], 16);
                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap'
                }).addTo(editModalMap);

                editModalMarker = L.marker([lat, lng], { draggable: isEditUnlocked }).addTo(editModalMap);

                editModalMarker.on('dragend', (e) => {
                    if (!isEditUnlocked) return;
                    const pos = e.target.getLatLng();
                    document.getElementById('edit_latitude').value = pos.lat.toFixed(7);
                    document.getElementById('edit_longitude').value = pos.lng.toFixed(7);
                });

                editModalMap.on('click', (e) => {
                    if (!isEditUnlocked) return;
                    editModalMarker.setLatLng(e.latlng);
                    document.getElementById('edit_latitude').value = e.latlng.lat.toFixed(7);
                    document.getElementById('edit_longitude').value = e.latlng.lng.toFixed(7);
                });
            } else {
                editModalMap.invalidateSize();
                editModalMap.setView([lat, lng], 16);
                editModalMarker.setLatLng([lat, lng]);
                if (editModalMarker.dragging) {
                    if (isEditUnlocked) editModalMarker.dragging.enable();
                    else editModalMarker.dragging.disable();
                }
            }
        }
    }

    function syncModalMapMarker(prefix) {
        if (prefix === 'edit' && !isEditUnlocked) return;
        const latInput = document.getElementById(`${prefix}_latitude`);
        const lngInput = document.getElementById(`${prefix}_longitude`);
        if (!latInput || !lngInput) return;

        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (isNaN(lat) || isNaN(lng)) return;

        if (prefix === 'create' && createModalMap && createModalMarker) {
            createModalMarker.setLatLng([lat, lng]);
            createModalMap.panTo([lat, lng]);
        } else if (prefix === 'edit' && editModalMap && editModalMarker) {
            editModalMarker.setLatLng([lat, lng]);
            editModalMap.panTo([lat, lng]);
        }
    }

    /* ==========================================================================
       AUTO-GUARDADO DIRECTO DEL ENCABEZADO TÉCNICO
       ========================================================================== */
    let autoSaveTimeout = null;

    function autoSaveHeaderField() {
        const badge = document.getElementById('headerAutoSaveBadge');
        if (badge) {
            badge.classList.add('visible', 'saving');
            badge.innerHTML = `
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="spin-slow"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                <span>Guardando...</span>
            `;
        }

        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            const installationName = document.getElementById('inline_installation_name')?.value || '';
            const startDate = document.getElementById('inline_start_date')?.value || '';
            const endDate = document.getElementById('inline_end_date')?.value || '';
            const monitoringType = document.getElementById('inline_monitoring_type')?.value || '';

            fetch("{{ route('modules.illumination.header.update', $module->id) }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    installation_name: installationName,
                    start_date: startDate,
                    end_date: endDate,
                    monitoring_type: monitoringType
                })
            })
            .then(res => res.json())
            .then(data => {
                if (badge) {
                    badge.classList.remove('saving');
                    badge.classList.add('visible');
                    badge.innerHTML = `
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Guardado</span>
                    `;
                    setTimeout(() => {
                        badge.classList.remove('visible');
                    }, 2500);
                }
            })
            .catch(err => {
                console.error("Error al guardar encabezado técnico:", err);
                if (badge) {
                    badge.classList.remove('saving');
                    badge.innerHTML = `<span style="color: #ef4444;">Error</span>`;
                }
            });
        }, 300);
    }

    /* ==========================================================================
       GEOLOCALIZACIÓN GPS DIRECTA DEL DISPOSITIVO
       ========================================================================== */
    function getCurrentGpsPosition(latInputId, lngInputId, prefix) {
        if (prefix === 'edit' && !isEditUnlocked) return;
        if (!navigator.geolocation) {
            alert('La geolocalización no es soportada por este navegador.');
            return;
        }

        const latInput = document.getElementById(latInputId);
        const lngInput = document.getElementById(lngInputId);

        navigator.geolocation.getCurrentPosition(
            (pos) => {
                const lat = pos.coords.latitude.toFixed(7);
                const lng = pos.coords.longitude.toFixed(7);
                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;
                if (prefix) syncModalMapMarker(prefix);
            },
            (err) => {
                alert('No se pudo obtener la ubicación GPS: ' + err.message);
            },
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }

    /* ==========================================================================
       PAGINACIÓN REACTIVA (10 VISIBLES POR PÁGINA) & FILTRADO EN VIVO
       ========================================================================== */
    const ILL_PAGE_SIZE = 10;
    let currentIllPage = 1;
    let currentIllFilter = 'all';

    function getMatchingRows() {
        const searchTerm = document.getElementById('illuminationSearchInput')?.value.trim().toLowerCase() || '';
        const rows = Array.from(document.querySelectorAll('#illuminationMasterTable tbody tr.illumination-data-row'));

        return rows.filter(row => {
            const compliantStatus = row.getAttribute('data-compliant') || '';
            const lightingType = row.getAttribute('data-lighting') || '';
            const searchData = row.getAttribute('data-search') || '';

            // Match filter
            let matchesFilter = true;
            if (currentIllFilter === 'compliant') {
                matchesFilter = (compliantStatus === 'compliant');
            } else if (currentIllFilter === 'non-compliant') {
                matchesFilter = (compliantStatus === 'non-compliant');
            } else if (['Natural', 'Artificial', 'Mixta'].includes(currentIllFilter)) {
                matchesFilter = (lightingType === currentIllFilter);
            }

            // Match search
            const matchesSearch = (!searchTerm || searchData.includes(searchTerm));

            return matchesFilter && matchesSearch;
        });
    }

    function updateIlluminationPagination() {
        const matchingRows = getMatchingRows();
        const allRows = Array.from(document.querySelectorAll('#illuminationMasterTable tbody tr.illumination-data-row'));
        const totalItems = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalItems / ILL_PAGE_SIZE));

        if (currentIllPage > totalPages) currentIllPage = totalPages;
        if (currentIllPage < 1) currentIllPage = 1;

        const startIdx = (currentIllPage - 1) * ILL_PAGE_SIZE;
        const endIdx = startIdx + ILL_PAGE_SIZE;

        allRows.forEach(row => row.style.display = 'none');
        matchingRows.slice(startIdx, endIdx).forEach(row => {
            row.style.display = '';
        });

        const emptyTableRow = document.getElementById('emptyTableRow');
        const noResultsSearchRow = document.getElementById('noResultsSearchRow');
        if (allRows.length === 0) {
            if (emptyTableRow) emptyTableRow.style.display = '';
            if (noResultsSearchRow) noResultsSearchRow.style.display = 'none';
        } else if (totalItems === 0) {
            if (emptyTableRow) emptyTableRow.style.display = 'none';
            if (noResultsSearchRow) noResultsSearchRow.style.display = '';
        } else {
            if (emptyTableRow) emptyTableRow.style.display = 'none';
            if (noResultsSearchRow) noResultsSearchRow.style.display = 'none';
        }

        const startEl = document.getElementById('illPageStart');
        const endEl = document.getElementById('illPageEnd');
        const totalEl = document.getElementById('illPageTotal');

        if (startEl) startEl.textContent = totalItems === 0 ? 0 : (startIdx + 1);
        if (endEl) endEl.textContent = Math.min(endIdx, totalItems);
        if (totalEl) totalEl.textContent = totalItems;

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        const container = document.getElementById('illuminationPaginationControls');
        if (!container) return;

        if (totalPages <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';

        html += `<button type="button" class="ill-pag-btn" onclick="goToIllPage(${currentIllPage - 1})" ${currentIllPage <= 1 ? 'disabled' : ''} aria-label="Página anterior">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>`;

        for (let p = 1; p <= totalPages; p++) {
            if (p === 1 || p === totalPages || (p >= currentIllPage - 1 && p <= currentIllPage + 1)) {
                html += `<button type="button" class="ill-pag-btn ${p === currentIllPage ? 'active' : ''}" onclick="goToIllPage(${p})">${p}</button>`;
            } else if (p === currentIllPage - 2 || p === currentIllPage + 2) {
                html += `<span style="padding: 0 4px; color: #94a3b8; font-weight: 700;">...</span>`;
            }
        }

        html += `<button type="button" class="ill-pag-btn" onclick="goToIllPage(${currentIllPage + 1})" ${currentIllPage >= totalPages ? 'disabled' : ''} aria-label="Página siguiente">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>`;

        container.innerHTML = html;
    }

    function goToIllPage(page) {
        currentIllPage = page;
        updateIlluminationPagination();
        const table = document.getElementById('illuminationMasterTable');
        if (table) table.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function filterIllumination(filterKey, btn) {
        currentIllFilter = filterKey;
        document.querySelectorAll('#illuminationFilterGroup .filter-pill-btn').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');
        currentIllPage = 1;
        updateIlluminationPagination();
    }

    function searchIlluminationLive() {
        currentIllPage = 1;
        updateIlluminationPagination();
    }

    /* ==========================================================================
       MAPA MODAL COMPLETO (LEAFLET + FOTOGRAFÍA DEL PUNTO)
       ========================================================================== */
    let leafletMapInstance = null;
    let leafletMarkerInstance = null;
    let activeMapPhotoUrl = null;
    let activeMapPointTitle = null;

    function openMapModal(item) {
        const modal = document.getElementById('mapLocationModal');
        if (!modal) return;

        document.getElementById('mapModalTitle').textContent = `Punto #${item.num} — ${item.area}`;
        document.getElementById('mapModalSubtitle').textContent = `${item.workstation} • ${item.measurement_point}`;
        document.getElementById('mapCardPointName').textContent = `${item.area} — ${item.workstation}`;
        document.getElementById('mapCardLocationDesc').textContent = item.location && item.location !== '—' ? item.location : 'Ubicación registrada en planta';

        const luxBadge = document.getElementById('mapCardLuxBadge');
        if (luxBadge) {
            luxBadge.className = `lux-measured-badge ${item.is_compliant ? 'compliant' : 'non-compliant'}`;
            luxBadge.textContent = `${item.measured_lux} LUX (${item.is_compliant ? 'CUMPLE' : 'NO CUMPLE'})`;
        }

        activeMapPhotoUrl = item.image_path || null;
        activeMapPointTitle = `Punto #${item.num}: ${item.measurement_point}`;
        const photoWrap = document.getElementById('mapModalPhotoThumbWrap');
        const photoImg = document.getElementById('mapModalPhotoImg');
        if (activeMapPhotoUrl) {
            if (photoImg) photoImg.src = activeMapPhotoUrl;
            if (photoWrap) photoWrap.style.display = 'flex';
        } else {
            if (photoWrap) photoWrap.style.display = 'none';
        }

        let lat = parseFloat(item.latitude);
        let lng = parseFloat(item.longitude);

        if (isNaN(lat) || isNaN(lng)) {
            lat = -16.5034120;
            lng = -68.1324560;
        }

        document.getElementById('mapCardCoords').textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;

        const gmapsBtn = document.getElementById('openInGoogleMapsBtn');
        if (gmapsBtn) {
            gmapsBtn.href = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
        }

        modal.classList.add('open');

        setTimeout(() => {
            initOrUpdateLeafletMap(lat, lng, item);
        }, 120);
    }

    function initOrUpdateLeafletMap(lat, lng, item) {
        const container = document.getElementById('mapContainerLeaflet');
        if (!container) return;

        if (!leafletMapInstance) {
            leafletMapInstance = L.map('mapContainerLeaflet').setView([lat, lng], 16);

            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(leafletMapInstance);
        } else {
            leafletMapInstance.invalidateSize();
            leafletMapInstance.setView([lat, lng], 16);
        }

        if (leafletMarkerInstance) {
            leafletMapInstance.removeLayer(leafletMarkerInstance);
        }

        let popupContent = `
            <div style="font-family: sans-serif; font-size: 12px; line-height: 1.4; max-width: 200px;">
                <strong style="color: #0f172a; font-size: 13px;">Punto #${item.num}</strong><br>
                <span style="color: #64748b;">${item.area}</span><br>
                <div style="margin: 4px 0; font-weight: 700; color: ${item.is_compliant ? '#059669' : '#dc2626'};">${item.measured_lux} LUX</div>
        `;

        if (item.image_path) {
            popupContent += `
                <div style="margin-top: 6px; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; cursor: pointer;" onclick="openPhotoViewer('${item.image_path}', 'Punto #${item.num}')">
                    <img src="${item.image_path}" style="width: 100%; height: 90px; object-fit: cover; display: block;">
                </div>
            `;
        }

        popupContent += `</div>`;

        leafletMarkerInstance = L.marker([lat, lng])
            .addTo(leafletMapInstance)
            .bindPopup(popupContent)
            .openPopup();
    }

    function closeMapModal() {
        const modal = document.getElementById('mapLocationModal');
        if (modal) modal.classList.remove('open');
    }

    function expandCurrentMapPhoto() {
        if (activeMapPhotoUrl) {
            openPhotoViewer(activeMapPhotoUrl, activeMapPointTitle);
        }
    }

    /* ==========================================================================
       GESTIÓN DE MODALES: CREACIÓN, VISUALIZACIÓN Y DESBLOQUEO DE EDICIÓN
       ========================================================================== */
    function openCreateMeasurementModal() {
        const modal = document.getElementById('createMeasurementModal');
        if (modal) {
            modal.classList.add('open');
            createReadingsList = [];
            renderReadingsBadges('create');

            // Reset actividad y nivel requerido
            const actSelect = document.getElementById('create_activity_description');
            if (actSelect) actSelect.value = 'Oficinas y talleres';
            const reqSelect = document.getElementById('create_required_lux');
            if (reqSelect) reqSelect.value = '300';
            updateNormativeLegend('create');

            // Reset fotos slider
            modalPhotos.create = [];
            modalPhotoIndex.create = 0;
            renderPhotoSlider('create');
            const fileInput = document.getElementById('create_images_input');
            if (fileInput) fileInput.value = '';

            // Reset observaciones
            const obs = document.getElementById('create_observations');
            if (obs) obs.value = '';

            setTimeout(() => {
                initModalMiniMap('create', -16.5034120, -68.1324560);
            }, 150);
        }
    }

    function closeCreateMeasurementModal() {
        const modal = document.getElementById('createMeasurementModal');
        if (modal) modal.classList.remove('open');
    }

    function applyEditModeState(isEditing) {
        isEditUnlocked = isEditing;

        const form = document.getElementById('editMeasurementForm');
        if (form) {
            if (isEditing) {
                form.classList.remove('modal-view-mode');
            } else {
                form.classList.add('modal-view-mode');
            }
        }

        // Indicador de Estado y Botón de Desbloqueo en el Encabezado
        const badge = document.getElementById('modalModeStatusBadge');
        if (badge) {
            badge.className = isEditing ? 'modal-badge-edit' : 'modal-badge-view';
            badge.textContent = isEditing ? 'Modo Edición' : 'Solo Lectura';
        }

        const btn = document.getElementById('btnToggleEditMode');
        const btnText = document.getElementById('btnToggleEditModeText');
        const btnIcon = document.getElementById('btnToggleEditModeIcon');
        if (btn) {
            if (isEditing) {
                btn.classList.add('active-editing');
                if (btnText) btnText.textContent = 'Lectura';
                if (btnIcon) {
                    btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>`;
                }
            } else {
                btn.classList.remove('active-editing');
                if (btnText) btnText.textContent = 'Editar';
                if (btnIcon) {
                    btnIcon.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>`;
                }
            }
        }

        // Habilitar / Deshabilitar Campos de Entrada
        const fieldsToToggle = [
            'edit_measurement_date',
            'edit_measurement_time',
            'edit_area',
            'edit_workstation',
            'edit_measurement_point',
            'edit_activity_description',
            'edit_lighting_type',
            'edit_required_lux',
            'edit_latitude',
            'edit_longitude',
            'edit_observations'
        ];

        fieldsToToggle.forEach(fieldId => {
            const el = document.getElementById(fieldId);
            if (el) el.disabled = !isEditing;
        });

        // Visibilidad de Botones y Controles de Edición
        const submitBtn = document.getElementById('edit_modal_submit_btn');
        if (submitBtn) submitBtn.style.display = isEditing ? 'inline-flex' : 'none';

        const readingsInputBar = document.getElementById('edit_readings_input_bar');
        if (readingsInputBar) readingsInputBar.style.display = isEditing ? 'flex' : 'none';

        const gpsBtn = document.getElementById('edit_btn_gps');
        if (gpsBtn) gpsBtn.style.display = isEditing ? 'inline-flex' : 'none';

        const addPhotosBtn = document.getElementById('edit_btn_add_photos');
        if (addPhotosBtn) addPhotosBtn.style.display = isEditing ? 'inline-flex' : 'none';

        // Arrastre de marcador en mapa
        if (editModalMarker && editModalMarker.dragging) {
            if (isEditing) editModalMarker.dragging.enable();
            else editModalMarker.dragging.disable();
        }

        // Re-renderizar lecturas para mostrar/ocultar botón eliminar ✕
        renderReadingsBadges('edit');
    }

    function toggleModalEditMode() {
        applyEditModeState(!isEditUnlocked);
    }

    function openViewMeasurementModal(item) {
        const form = document.getElementById('editMeasurementForm');
        if (!form) return;

        form.action = `/modulos/{{ $module->id }}/iluminacion/mediciones/${item.id}`;

        document.getElementById('edit_point_number').value = item.num || '';
        document.getElementById('edit_pt_num_disp').textContent = item.num || '01';

        // Personal registrador en encabezado
        const staffEl = document.getElementById('edit_modal_registered_by');
        if (staffEl) {
            staffEl.textContent = item.registered_by || '{{ $registeredByHeader }}';
        }

        // Fecha y Hora de medición
        document.getElementById('edit_measurement_date').value = item.raw_date || '';
        document.getElementById('edit_measurement_time').value = item.time !== '—' ? item.time : '';
        document.getElementById('edit_area').value = item.area || '';
        document.getElementById('edit_workstation').value = item.workstation || '';
        document.getElementById('edit_measurement_point').value = item.measurement_point || '';

        // Descripción de Actividad
        const actSelect = document.getElementById('edit_activity_description');
        if (actSelect) {
            actSelect.value = item.activity_description || 'Oficinas y talleres';
        }

        // Tipo de Iluminación
        document.getElementById('edit_lighting_type').value = item.lighting_type || 'Artificial';

        // Nivel Requerido Select & Legend
        const reqSelect = document.getElementById('edit_required_lux');
        if (reqSelect) {
            const rawReq = String(parseInt(item.raw_required_lux) || (ACTIVITY_TO_LUX[item.activity_description] || 300));
            reqSelect.value = rawReq;
            updateNormativeLegend('edit');
        }

        // Mediciones LUX (Badges de lecturas hasta 25 puntos)
        editReadingsList = [];
        if (item.readings && Array.isArray(item.readings) && item.readings.length > 0) {
            editReadingsList = [...item.readings];
        } else if (item.raw_measured_lux > 0) {
            editReadingsList = [parseFloat(item.raw_measured_lux)];
        }

        // Visor Fotográfico Slide Continuo (Múltiples Fotos)
        modalPhotos.edit = [];
        if (item.images && Array.isArray(item.images) && item.images.length > 0) {
            modalPhotos.edit = [...item.images];
        } else if (item.image_path) {
            modalPhotos.edit = [item.image_path];
        }
        modalPhotoIndex.edit = 0;
        renderPhotoSlider('edit');

        // Coordenadas y Observaciones
        const lat = (item.latitude !== null && item.latitude !== '') ? parseFloat(item.latitude) : -16.5034120;
        const lng = (item.longitude !== null && item.longitude !== '') ? parseFloat(item.longitude) : -68.1324560;
        document.getElementById('edit_latitude').value = (item.latitude !== null) ? item.latitude : '';
        document.getElementById('edit_longitude').value = (item.longitude !== null) ? item.longitude : '';
        document.getElementById('edit_observations').value = item.observations || '';

        // Limpiar selector de archivo
        const fileInput = document.getElementById('edit_images_input');
        if (fileInput) fileInput.value = '';

        // Iniciar SIEMPRE en Modo Consulta (Solo Lectura)
        applyEditModeState(false);

        const modal = document.getElementById('editMeasurementModal');
        if (modal) {
            modal.classList.add('open');
            setTimeout(() => {
                initModalMiniMap('edit', lat, lng);
            }, 150);
        }
    }

    // Alias para compatibilidad
    function openEditMeasurementModal(item) {
        openViewMeasurementModal(item);
    }

    function closeEditMeasurementModal() {
        const modal = document.getElementById('editMeasurementModal');
        if (modal) {
            modal.classList.remove('open');
            applyEditModeState(false);
        }
    }

    function openPhotoViewer(url, title) {
        const modal = document.getElementById('photoViewerModal');
        const img = document.getElementById('photoViewerImg');
        const t = document.getElementById('photoViewerTitle');
        if (img) img.src = url;
        if (t) t.textContent = title || 'Fotografía del Punto';
        if (modal) modal.classList.add('open');
    }

    function closePhotoViewer() {
        const modal = document.getElementById('photoViewerModal');
        if (modal) modal.classList.remove('open');
    }

    function confirmDeleteMeasurement(id, pointNum) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: `¿Eliminar Punto #${pointNum}?`,
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true,
                buttonsStyling: false,
                customClass: {
                    popup: 'metric-swal-popup',
                    confirmButton: 'metric-swal-btn-danger',
                    cancelButton: 'metric-swal-btn-cancel'
                }
            }).then((res) => {
                if (res.isConfirmed) {
                    const form = document.getElementById('deleteMeasurementForm');
                    form.action = `/modulos/{{ $module->id }}/iluminacion/mediciones/${id}`;
                    form.submit();
                }
            });
        } else {
            if (confirm(`¿Estás seguro de eliminar el punto de medición #${pointNum}?`)) {
                const form = document.getElementById('deleteMeasurementForm');
                form.action = `/modulos/{{ $module->id }}/iluminacion/mediciones/${id}`;
                form.submit();
            }
        }
    }

    /* ==========================================================================
       GESTIÓN DE MODAL EXPORTAR & GENERACIÓN EXCEL (.XLSX)
       ========================================================================== */
    function openExportModal() {
        const modal = document.getElementById('exportOptionsModal');
        if (modal) modal.classList.add('open');
    }

    function closeExportModal() {
        const modal = document.getElementById('exportOptionsModal');
        if (modal) modal.classList.remove('open');
    }

    function openPhotoReportModal() {
        closeExportModal();
        const modal = document.getElementById('photoReportModal');
        if (modal) modal.classList.add('open');
    }

    function closePhotoReportModal() {
        const modal = document.getElementById('photoReportModal');
        if (modal) modal.classList.remove('open');
    }

    /* ==========================================================================
       MODAL MAPA DE TODAS LAS UBICACIONES (LEAFLET MULTI-PUNTO)
       ========================================================================== */
    let allLocationsMapInstance = null;
    let allLocationsMarkersLayer = null;
    let allLocationsMarkersList = [];

    function openAllLocationsModal() {
        const modal = document.getElementById('allLocationsModal');
        if (!modal) return;
        modal.classList.add('open');

        setTimeout(() => {
            initAllLocationsMap();
        }, 150);
    }

    function closeAllLocationsModal() {
        const modal = document.getElementById('allLocationsModal');
        if (modal) modal.classList.remove('open');
    }

    function initAllLocationsMap() {
        const container = document.getElementById('allLocationsMapLeaflet');
        if (!container) return;

        if (!allLocationsMapInstance) {
            allLocationsMapInstance = L.map('allLocationsMapLeaflet').setView([-16.503412, -68.132456], 15);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(allLocationsMapInstance);
            allLocationsMarkersLayer = L.featureGroup().addTo(allLocationsMapInstance);
        } else {
            allLocationsMapInstance.invalidateSize();
        }

        allLocationsMarkersLayer.clearLayers();
        allLocationsMarkersList = [];

        const bounds = [];

        ALL_MEASUREMENTS_DATA.forEach((m, idx) => {
            let lat = parseFloat(m.latitude);
            let lng = parseFloat(m.longitude);

            if (isNaN(lat) || isNaN(lng)) {
                lat = -16.503412 + (idx * 0.00035);
                lng = -68.132456 + (idx * 0.00035);
            }

            bounds.push([lat, lng]);

            const pinHtml = `
                <div class="custom-map-pin ${m.is_compliant ? 'pin-compliant' : 'pin-non-compliant'}" title="Punto #${m.num}">
                    ${m.num}
                </div>
            `;

            const customIcon = L.divIcon({
                html: pinHtml,
                className: 'custom-div-pin-wrapper',
                iconSize: [30, 30],
                iconAnchor: [15, 15],
                popupAnchor: [0, -16]
            });

            let photoSection = '';
            if (m.image_path) {
                photoSection = `
                    <div style="margin-top: 6px; border-radius: 6px; overflow: hidden; border: 1px solid #cbd5e1; cursor: pointer;" onclick="openPhotoViewer('${m.image_path}', 'Punto #${m.num}: ${addslashes(m.measurement_point)}')">
                        <img src="${m.image_path}" style="width: 100%; height: 90px; object-fit: cover; display: block;">
                    </div>
                `;
            }

            const popupHtml = `
                <div style="font-family: 'Outfit', sans-serif; font-size: 12px; line-height: 1.4; min-width: 210px; max-width: 260px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 6px; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px;">
                        <strong style="font-size: 13px; color: #0f172a;">Punto #${m.num}</strong>
                        <span style="font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: ${m.is_compliant ? '#ecfdf5' : '#fef2f2'}; color: ${m.is_compliant ? '#059669' : '#dc2626'}; border: 1px solid ${m.is_compliant ? '#a7f3d0' : '#fecaca'};">
                            ${m.is_compliant ? 'CUMPLE' : 'NO CUMPLE'}
                        </span>
                    </div>
                    <div style="font-weight: 700; color: #1e293b; margin-bottom: 2px;">${m.measurement_point}</div>
                    <div style="font-size: 11.5px; color: #64748b; margin-bottom: 6px;">${m.area} • ${m.workstation}</div>
                    
                    <div style="display: flex; align-items: center; gap: 6px; background: #f0f9ff; border: 1px solid #bae6fd; padding: 4px 8px; border-radius: 6px; margin-bottom: 6px;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        <span style="font-size: 11px; color: #0369a1;">Registrado por: <strong>${m.registered_by}</strong></span>
                    </div>

                    <div style="font-size: 11.5px; margin-bottom: 6px;">
                        <span>Lectura: <strong>${m.measured_lux} LUX</strong></span>
                        <span style="color: #64748b;">(Req: ${m.required_lux} LUX)</span>
                    </div>

                    ${photoSection}
                </div>
            `;

            const marker = L.marker([lat, lng], { icon: customIcon })
                .bindPopup(popupHtml);

            allLocationsMarkersLayer.addLayer(marker);
            allLocationsMarkersList.push(marker);
        });

        if (bounds.length > 0) {
            allLocationsMapInstance.fitBounds(bounds, { padding: [40, 40], maxZoom: 17 });
        }
    }

    function focusPointOnAllLocationsMap(index) {
        if (!allLocationsMarkersList[index] || !allLocationsMapInstance) return;
        const marker = allLocationsMarkersList[index];
        allLocationsMapInstance.setView(marker.getLatLng(), 17, { animate: true });
        marker.openPopup();
    }

    function addslashes(str) {
        return (str + '').replace(/[\\"']/g, '\\$&').replace(/\u0000/g, '\\0');
    }

    /* ==========================================================================
       EXPORTACIÓN PLANILLA EXCEL (.XLSX) CON FORMATO TÉCNICO OFICIAL
       ========================================================================== */
    async function downloadExcelPlanilla() {
        if (typeof ExcelJS === 'undefined') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Inicializando librería...',
                    text: 'La librería de exportación se está cargando. Por favor reintenta en un momento.',
                    icon: 'info',
                    confirmButtonText: 'Aceptar',
                    customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                });
            } else {
                alert('Cargando librería Excel. Por favor reintenta.');
            }
            return;
        }

        try {
            const workbook = new ExcelJS.Workbook();
            workbook.creator = 'METRIC v2 Pachabol';
            workbook.created = new Date();
            const sheet = workbook.addWorksheet('Planilla de Iluminación', {
                views: [{ showGridLines: true }]
            });

            // 29 Columnas en total (A hasta AC)
            sheet.columns = [
                { key: 'num', width: 6 },                   // A: N°
                { key: 'area', width: 26 },                  // B: Área
                { key: 'workstation', width: 24 },           // C: Puesto de trabajo
                { key: 'point', width: 20 },                 // D: Punto de medición
                { key: 'activity', width: 26 },              // E: Descripción de la actividad
                { key: 'time', width: 14 },                  // F: Horario de medición
                { key: 'lighting', width: 14 },              // G: Tipo de iluminación
                { key: 'required_lux', width: 16 },          // H: Nivel iluminancia requerido (Lux)
                { key: 'm1', width: 7 },                     // I: M1
                { key: 'm2', width: 7 },                     // J: M2
                { key: 'm3', width: 7 },                     // K: M3
                { key: 'm4', width: 7 },                     // L: M4
                { key: 'm5', width: 7 },                     // M: M5
                { key: 'm6', width: 7 },                     // N: M6
                { key: 'm7', width: 7 },                     // O: M7
                { key: 'm8', width: 7 },                     // P: M8
                { key: 'm9', width: 7 },                     // Q: M9
                { key: 'm10', width: 7 },                    // R: M10
                { key: 'm11', width: 7 },                    // S: M11
                { key: 'm12', width: 7 },                    // T: M12
                { key: 'm13', width: 7 },                    // U: M13
                { key: 'm14', width: 7 },                    // V: M14
                { key: 'm15', width: 7 },                    // W: M15
                { key: 'm16', width: 7 },                    // X: M16
                { key: 'min', width: 8 },                    // Y: Min
                { key: 'max', width: 8 },                    // Z: Max
                { key: 'prom', width: 10 },                  // AA: Promedio
                { key: 'compliance', width: 14 },            // AB: Cumple/no cumple el valor
                { key: 'obs', width: 30 }                    // AC: Observaciones
            ];

            const thinBorder = {
                top: { style: 'thin', color: { argb: 'FF000000' } },
                left: { style: 'thin', color: { argb: 'FF000000' } },
                bottom: { style: 'thin', color: { argb: 'FF000000' } },
                right: { style: 'thin', color: { argb: 'FF000000' } }
            };

            const applyBoxBorder = (startRow, startCol, endRow, endCol) => {
                for (let r = startRow; r <= endRow; r++) {
                    for (let c = startCol; c <= endCol; c++) {
                        const cell = sheet.getCell(r, c);
                        cell.border = thinBorder;
                    }
                }
            };

            // FILA 1: Encabezado Banner Azul #2B579A
            sheet.mergeCells('A1:AC1');
            const r1 = sheet.getCell('A1');
            r1.value = 'PLANILLA DE MEDICIÓN Y EVALUACIÓN DE NIVELES DE ILUMINACIÓN';
            r1.font = { name: 'Calibri', size: 13, bold: true, color: { argb: 'FFFFFFFF' } };
            r1.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF2B579A' } };
            r1.alignment = { vertical: 'middle', horizontal: 'center' };
            sheet.getRow(1).height = 28;

            // FILA 2: Espacio
            sheet.getRow(2).height = 10;

            // FILAS 3 A 6: Encabezados Técnicos Duales (Instalación vs Equipo)
            const instName = document.getElementById('inline_installation_name')?.value || TECHNICAL_HEADER_DATA.installationName || 'PAPELBOL - VILLA TUNARI';
            const stDate = TECHNICAL_HEADER_DATA.startDateFormatted || '23/7/2026';
            const enDate = TECHNICAL_HEADER_DATA.endDateFormatted || '24/7/2026';
            const monType = document.getElementById('inline_monitoring_type')?.value || TECHNICAL_HEADER_DATA.monitoringType || 'RUTINARIO:   SEGUIMIENTO: X';
            const eqName = TECHNICAL_HEADER_DATA.equipmentName || 'LUXÓMETRO - PCE';
            const eqBrand = TECHNICAL_HEADER_DATA.equipmentBrand || 'PCE';
            const eqModel = TECHNICAL_HEADER_DATA.equipmentModel || 'PCE - 174';
            const eqSerial = TECHNICAL_HEADER_DATA.equipmentSerial || '150206371';

            // Lado Izquierdo
            // Fila 3
            sheet.mergeCells('A3:C3');
            sheet.mergeCells('D3:L3');
            sheet.getCell('A3').value = 'INSTALACIÓN:';
            sheet.getCell('D3').value = instName;

            // Fila 4
            sheet.mergeCells('A4:C4');
            sheet.mergeCells('D4:L4');
            sheet.getCell('A4').value = 'FECHA DE INICIO:';
            sheet.getCell('D4').value = stDate;

            // Fila 5
            sheet.mergeCells('A5:C5');
            sheet.mergeCells('D5:L5');
            sheet.getCell('A5').value = 'FECHA DE FINALIZACIÓN:';
            sheet.getCell('D5').value = enDate;

            // Fila 6
            sheet.mergeCells('A6:C6');
            sheet.mergeCells('D6:L6');
            sheet.getCell('A6').value = 'TIPO DE MONITOREO:';
            sheet.getCell('D6').value = monType.includes('SEGUIMIENTO') ? monType : `RUTINARIO:   SEGUIMIENTO: ${monType || 'X'}`;

            applyBoxBorder(3, 1, 6, 12);

            // Lado Derecho
            // Fila 3
            sheet.mergeCells('T3:V3');
            sheet.mergeCells('W3:AC3');
            sheet.getCell('T3').value = 'EQUIPO:';
            sheet.getCell('W3').value = eqName;

            // Fila 4
            sheet.mergeCells('T4:V4');
            sheet.mergeCells('W4:AC4');
            sheet.getCell('T4').value = 'MARCA:';
            sheet.getCell('W4').value = eqBrand;

            // Fila 5
            sheet.mergeCells('T5:V5');
            sheet.mergeCells('W5:AC5');
            sheet.getCell('T5').value = 'MODELO:';
            sheet.getCell('W5').value = eqModel;

            // Fila 6
            sheet.mergeCells('T6:V6');
            sheet.mergeCells('W6:AC6');
            sheet.getCell('T6').value = 'SERIE:';
            sheet.getCell('W6').value = eqSerial;

            applyBoxBorder(3, 20, 6, 29);

            // Formato tipográfico de las cajas técnicas 3-6
            for (let r = 3; r <= 6; r++) {
                sheet.getRow(r).height = 19;
                const leftLbl = sheet.getCell(r, 1);
                leftLbl.font = { name: 'Calibri', size: 9, bold: true };
                leftLbl.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

                const leftVal = sheet.getCell(r, 4);
                leftVal.font = { name: 'Calibri', size: 9 };
                leftVal.alignment = { vertical: 'middle', horizontal: 'center' };

                const rightLbl = sheet.getCell(r, 20);
                rightLbl.font = { name: 'Calibri', size: 9, bold: true };
                rightLbl.alignment = { vertical: 'middle', horizontal: 'left', indent: 1 };

                const rightVal = sheet.getCell(r, 23);
                rightVal.font = { name: 'Calibri', size: 9 };
                rightVal.alignment = { vertical: 'middle', horizontal: 'center' };
            }

            // FILA 7: EVALUACIÓN DE RIESGOS (Centrado)
            sheet.mergeCells('A7:AC7');
            const r7 = sheet.getCell('A7');
            r7.value = 'EVALUACIÓN DE RIESGOS';
            r7.font = { name: 'Calibri', size: 12, bold: true, color: { argb: 'FF000000' } };
            r7.alignment = { vertical: 'middle', horizontal: 'center' };
            sheet.getRow(7).height = 25;

            // FILAS 8 y 9: ENCABEZADOS DE TABLA (Fondo #D9E1F2)
            const headerBg = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD9E1F2' } };
            const headerFont = { name: 'Calibri', size: 9, bold: true, color: { argb: 'FF000000' } };

            sheet.mergeCells('A8:A9');
            sheet.getCell('A8').value = 'N°';

            sheet.mergeCells('B8:B9');
            sheet.getCell('B8').value = 'Área';

            sheet.mergeCells('C8:C9');
            sheet.getCell('C8').value = 'Puesto de trabajo';

            sheet.mergeCells('D8:D9');
            sheet.getCell('D8').value = 'Punto de medición';

            sheet.mergeCells('E8:E9');
            sheet.getCell('E8').value = 'Descripción de la actividad';

            sheet.mergeCells('F8:F9');
            sheet.getCell('F8').value = 'Horario de\nmedición';

            sheet.mergeCells('G8:G9');
            sheet.getCell('G8').value = 'Tipo de\niluminación';

            sheet.mergeCells('H8:H9');
            sheet.getCell('H8').value = 'Nivel\niluminancia\nrequerido (Lux)';

            // M1 a M16 en fila 8 combinada
            sheet.mergeCells('I8:X8');
            sheet.getCell('I8').value = 'Medición de iluminancia (Lux)';

            for (let mIdx = 1; mIdx <= 16; mIdx++) {
                const colNum = 8 + mIdx; // 9 = I
                sheet.getCell(9, colNum).value = `M${mIdx}`;
            }

            // Resultados Min, Max, Promedio
            sheet.mergeCells('Y8:AA8');
            sheet.getCell('Y8').value = 'Resultados';
            sheet.getCell(9, 25).value = 'Min';
            sheet.getCell(9, 26).value = 'Max';
            sheet.getCell(9, 27).value = 'Promedio';

            sheet.mergeCells('AB8:AB9');
            sheet.getCell('AB8').value = 'Cumple/no\ncumple el\nvalor';

            sheet.mergeCells('AC8:AC9');
            sheet.getCell('AC8').value = 'Observaciones';

            sheet.getRow(8).height = 26;
            sheet.getRow(9).height = 20;

            for (let r = 8; r <= 9; r++) {
                for (let c = 1; c <= 29; c++) {
                    const cell = sheet.getCell(r, c);
                    cell.fill = headerBg;
                    cell.font = headerFont;
                    cell.border = thinBorder;
                    cell.alignment = { vertical: 'middle', horizontal: 'center', wrapText: true };
                }
            }

            // FILAS DE DATOS (A partir de fila 10)
            let currentRow = 10;
            ALL_MEASUREMENTS_DATA.forEach((item, index) => {
                sheet.getRow(currentRow).height = 24;

                let readings = Array.isArray(item.readings) ? item.readings : [];
                if (readings.length === 0 && item.raw_measured_lux > 0) {
                    readings = [parseFloat(item.raw_measured_lux)];
                }

                let minVal = null;
                let maxVal = null;
                let avgVal = parseFloat(item.raw_measured_lux) || 0;

                if (readings.length > 0) {
                    minVal = Math.min(...readings);
                    maxVal = Math.max(...readings);
                    const sum = readings.reduce((a, b) => a + b, 0);
                    avgVal = sum / readings.length;
                }

                const reqLux = parseFloat(item.raw_required_lux) || 300;
                const isComp = avgVal >= reqLux;

                // Col A: N°
                const cA = sheet.getCell(currentRow, 1);
                cA.value = parseInt(item.num) || (index + 1);
                cA.alignment = { vertical: 'middle', horizontal: 'center' };

                // Col B: Área
                const cB = sheet.getCell(currentRow, 2);
                cB.value = item.area || '';
                cB.alignment = { vertical: 'middle', horizontal: 'left' };

                // Col C: Puesto de trabajo
                const cC = sheet.getCell(currentRow, 3);
                cC.value = item.workstation || '';
                cC.alignment = { vertical: 'middle', horizontal: 'left' };

                // Col D: Punto de medición
                const cD = sheet.getCell(currentRow, 4);
                cD.value = item.measurement_point || '';
                cD.alignment = { vertical: 'middle', horizontal: 'left' };

                // Col E: Descripción de la actividad
                const cE = sheet.getCell(currentRow, 5);
                cE.value = item.activity_description || '';
                cE.alignment = { vertical: 'middle', horizontal: 'left' };

                // Col F: Horario de medición
                const cF = sheet.getCell(currentRow, 6);
                cF.value = item.time && item.time !== '—' ? item.time : '';
                cF.alignment = { vertical: 'middle', horizontal: 'center' };

                // Col G: Tipo de iluminación
                const cG = sheet.getCell(currentRow, 7);
                cG.value = item.lighting_type || 'Natural';
                cG.alignment = { vertical: 'middle', horizontal: 'center' };

                // Col H: Nivel iluminancia requerido (Lux)
                const cH = sheet.getCell(currentRow, 8);
                cH.value = reqLux;
                cH.numFmt = '#,##0.00';
                cH.alignment = { vertical: 'middle', horizontal: 'center' };

                // Cols I a X: M1 a M16
                for (let mIdx = 0; mIdx < 16; mIdx++) {
                    const colNum = 9 + mIdx;
                    const cM = sheet.getCell(currentRow, colNum);
                    if (readings[mIdx] !== undefined) {
                        cM.value = parseFloat(readings[mIdx]);
                        cM.numFmt = '#,##0.0';
                    } else {
                        cM.value = '';
                    }
                    cM.alignment = { vertical: 'middle', horizontal: 'center' };
                }

                // Col Y: Min
                const cY = sheet.getCell(currentRow, 25);
                if (minVal !== null) {
                    cY.value = parseFloat(minVal.toFixed(1));
                    cY.numFmt = '#,##0.0';
                } else {
                    cY.value = '';
                }
                cY.alignment = { vertical: 'middle', horizontal: 'center' };

                // Col Z: Max
                const cZ = sheet.getCell(currentRow, 26);
                if (maxVal !== null) {
                    cZ.value = parseFloat(maxVal.toFixed(1));
                    cZ.numFmt = '#,##0.0';
                } else {
                    cZ.value = '';
                }
                cZ.alignment = { vertical: 'middle', horizontal: 'center' };

                // Col AA: Promedio
                const cAA = sheet.getCell(currentRow, 27);
                cAA.value = parseFloat(avgVal.toFixed(1));
                cAA.numFmt = '#,##0.0';
                cAA.alignment = { vertical: 'middle', horizontal: 'center' };

                // Col AB: Cumple/no cumple el valor
                const cAB = sheet.getCell(currentRow, 28);
                cAB.value = isComp ? 'Cumple' : 'No Cumple';
                cAB.alignment = { vertical: 'middle', horizontal: 'center' };

                // Col AC: Observaciones
                const cAC = sheet.getCell(currentRow, 29);
                cAC.value = item.observations && item.observations !== 'Sin observaciones' ? item.observations : '';
                cAC.alignment = { vertical: 'middle', horizontal: 'left' };

                // Aplicar fuentes y bordes a toda la fila
                for (let c = 1; c <= 29; c++) {
                    const cell = sheet.getCell(currentRow, c);
                    cell.font = { name: 'Calibri', size: 9 };
                    cell.border = thinBorder;
                }

                currentRow++;
            });

            // Descargar archivo .xlsx
            const buffer = await workbook.xlsx.writeBuffer();
            const blob = new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
            const link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            const fileNameSafe = (instName || 'Estudio_Iluminacion').replace(/[^a-zA-Z0-9_-]/g, '_');
            link.download = `Planilla_Iluminacion_${fileNameSafe}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            closeExportModal();

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Planilla Generada!',
                    text: 'El archivo Excel oficial ha sido descargado exitosamente.',
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    timer: 3500,
                    timerProgressBar: true,
                    customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-confirm' }
                });
            }
        } catch (err) {
            console.error('Error al generar planilla Excel:', err);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Error de Exportación',
                    text: 'Ocurrió un inconveniente al generar el archivo Excel: ' + err.message,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    customClass: { popup: 'metric-swal-popup', confirmButton: 'metric-swal-btn-danger' }
                });
            } else {
                alert('Error al exportar: ' + err.message);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateIlluminationPagination();

        ['createMeasurementModal', 'editMeasurementModal', 'mapLocationModal', 'photoViewerModal', 'exportOptionsModal', 'allLocationsModal', 'photoReportModal'].forEach(id => {
            const modal = document.getElementById(id);
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) modal.classList.remove('open');
                });
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeCreateMeasurementModal();
                closeEditMeasurementModal();
                closeMapModal();
                closePhotoViewer();
                closeExportModal();
                closeAllLocationsModal();
                closePhotoReportModal();
            }
        });
    });

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        setTimeout(updateIlluminationPagination, 60);
    }
</script>

{{-- SweetAlert2 Notificaciones con diseño oficial del sistema METRIC --}}
@if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¡Guardado!',
                    text: @json(session('success')),
                    icon: 'success',
                    confirmButtonText: 'Aceptar',
                    buttonsStyling: false,
                    timer: 3500,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'metric-swal-popup',
                        confirmButton: 'metric-swal-btn-confirm'
                    }
                });
            }
        });
    </script>
@endif

@if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Atención',
                    text: @json(session('error')),
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'metric-swal-popup',
                        confirmButton: 'metric-swal-btn-danger'
                    }
                });
            }
        });
    </script>
@endif

@if(isset($errors) && $errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Errores de Validación',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    icon: 'error',
                    confirmButtonText: 'Aceptar',
                    buttonsStyling: false,
                    customClass: {
                        popup: 'metric-swal-popup',
                        confirmButton: 'metric-swal-btn-danger'
                    }
                });
            }
        });
    </script>
@endif
@endpush
