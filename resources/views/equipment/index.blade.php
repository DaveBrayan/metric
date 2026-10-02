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
            min-width: 1050px;
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

        .admin-actions-cell {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 6px;
            flex-wrap: nowrap;
        }

        .btn-admin-icon-action {
            width: 36px !important;
            height: 36px !important;
            min-width: 36px !important;
            max-width: 36px !important;
            aspect-ratio: 1 / 1 !important;
            border-radius: 10px !important;
            display: inline-grid !important;
            place-items: center !important;
            padding: 0 !important;
            flex-shrink: 0 !important;
            box-sizing: border-box !important;
        }

        .eq-thumb-cell {
            width: 48px;
            height: 48px;
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
            padding: 2px 8px;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            letter-spacing: 0.2px;
        }

        .eq-serial-code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            font-size: 11.5px;
            font-weight: 700;
            color: #0f1c2e;
            background: rgba(16, 185, 223, 0.08);
            border: 1px solid rgba(16, 185, 223, 0.25);
            padding: 2px 7px;
            border-radius: 6px;
            display: inline-block;
        }

        /* ==========================================================================
       CALIBRATION & RECALIBRATION UI STYLES
       ========================================================================== */
        .eq-calib-card {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .eq-calib-header {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .eq-calib-tag-label {
            font-size: 12.5px;
            font-weight: 600;
            color: #475569;
        }

        .eq-calib-main-date {
            font-family: 'Outfit', sans-serif;
            font-size: 13.5px;
            font-weight: 800;
            color: #0f1c2e;
            letter-spacing: -0.2px;
        }

        .eq-calib-next-legend {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 11.5px;
            font-weight: 600;
            color: #475569;
        }

        .eq-calib-next-legend svg {
            color: #0284c7;
            flex-shrink: 0;
        }

        .eq-calib-next-date {
            font-weight: 700;
            color: #0369a1;
        }

        .calib-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 2px 8px;
            border-radius: 9999px;
            font-size: 10.5px;
            font-weight: 700;
            line-height: 1.3;
            width: fit-content;
        }

        .calib-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .calib-warning {
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
        }

        .calib-danger {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .calib-secondary {
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #e2e8f0;
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

        .modal-dialog-history {
            width: 100%;
            max-width: 900px;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 20px;
            box-shadow: 0 35px 80px -15px rgba(15, 28, 46, 0.38);
            overflow: hidden;
            animation: scaleInModal 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes scaleInModal {
            0% {
                transform: scale(0.93) translateY(15px);
                opacity: 0;
            }

            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        .modal-header-custom {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
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
            padding: 22px 24px;
            max-height: 74vh;
            overflow-y: auto;
            background: #ffffff;
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

        /* ==========================================================================
       2-COLUMN MODAL LAYOUT
       ========================================================================== */
        .modal-2col-layout {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 24px;
        }

        @media (max-width: 768px) {
            .modal-2col-layout {
                grid-template-columns: 1fr;
                gap: 18px;
            }
        }

        /* Form inputs & controls */
        .form-row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 14px;
        }

        @media (max-width: 500px) {
            .form-row-grid {
                grid-template-columns: 1fr;
            }
        }

        .form-field-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 14px;
        }

        .form-field-label {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .custom-form-input {
            width: 100%;
            height: 40px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 0 13px;
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
            padding: 9px 13px;
            resize: vertical;
            font-family: inherit;
        }

        .custom-form-select {
            width: 100%;
            height: 40px;
            background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 14px center;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            padding: 0 38px 0 13px;
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
            font-size: 13px;
            cursor: pointer;
            padding: 8px 16px;
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
            padding: 9px 20px;
            font-weight: 700;
            font-size: 13px;
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
            padding: 16px;
            text-align: center;
            position: relative;
            transition: all 0.25s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 250px;
        }

        .eq-image-upload-zone.dragover {
            border-color: #10b9df;
            background: rgba(16, 185, 223, 0.06);
        }

        .eq-preview-container {
            width: 100%;
            max-height: 200px;
            border-radius: 12px;
            overflow: hidden;
            display: none;
            position: relative;
            box-shadow: 0 8px 20px rgba(15, 28, 46, 0.12);
            margin-bottom: 10px;
            background: #0f1c2e;
        }

        .eq-preview-container.has-image {
            display: block !important;
        }

        .eq-preview-img {
            width: 100%;
            height: 180px;
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
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(16, 185, 223, 0.12);
            color: #10b9df;
            display: grid;
            place-items: center;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .eq-image-upload-zone:hover .eq-upload-icon-circle {
            transform: scale(1.08);
            background: rgba(16, 185, 223, 0.2);
        }

        .eq-upload-title {
            font-weight: 700;
            font-size: 13px;
            color: #0f1c2e;
            margin-bottom: 3px;
        }

        .eq-upload-hint {
            font-size: 11.5px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .btn-select-file {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 7px 14px;
            border-radius: 9999px;
            font-size: 12px;
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
            padding: 5px 12px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 6px;
        }

        .btn-remove-photo:hover {
            background: #fecaca;
        }

        /* ==========================================================================
       DYNAMIC CALIBRATION HELPER BOX IN FORMS
       ========================================================================== */
        .calib-calc-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 10px;
            padding: 10px 12px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeInCalib 0.2s ease;
        }

        @keyframes fadeInCalib {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .calib-calc-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: #0284c7;
            color: #ffffff;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }

        .calib-calc-text {
            font-size: 12px;
            color: #0369a1;
            line-height: 1.35;
        }

        .calib-calc-text strong {
            color: #0c4a6e;
            font-weight: 700;
        }

        /* ==========================================================================
       CALIBRATION HISTORY MODAL SPECIFICS
       ========================================================================== */
        .calib-summary-banner {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }

        .calib-summary-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .calib-summary-label {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .calib-summary-value {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: #0f1c2e;
        }

        .calib-add-panel {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 18px;
            margin-bottom: 22px;
            box-shadow: 0 4px 14px rgba(15, 28, 46, 0.04);
        }

        .calib-add-title {
            font-size: 13.5px;
            font-weight: 800;
            color: #0f1c2e;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .calib-history-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .calib-history-table th {
            background: #f1f5f9;
            padding: 10px 14px;
            font-size: 11.5px;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-bottom: 1.5px solid #cbd5e1;
        }

        .calib-history-table td {
            padding: 12px 14px;
            font-size: 12.5px;
            color: #334155;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .calib-history-table tbody tr:hover {
            background: #f8fafc;
        }

        /* Action button for calibration (Gauge/Medidor de calibración) */
        .btn-admin-icon-action.theme-cyan {
            background: rgba(16, 185, 223, 0.1);
            color: #0891b2;
            border: 1px solid rgba(16, 185, 223, 0.25);
        }

        .btn-admin-icon-action.theme-cyan:hover {
            background: #0891b2;
            color: #ffffff;
            border-color: #0891b2;
        }

        /* Visor de Fotografía (500x500px, Fondo Blanco) */
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
    <!-- Header Banner -->
    <div class="equipment-header-banner">
        <div>
            <h1>Inventario de Equipos de Medición</h1>
            <p>Control de fechas de calibración (+365 días), historial técnico, números de serie y registro fotográfico.</p>
        </div>
        @if($canEdit ?? true)
            <button type="button" class="btn-primary-hero-action" onclick="openCreateEquipmentModal()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span>Nuevo Equipo</span>
            </button>
        @else
            <div
                style="display: inline-flex; align-items: center; gap: 8px; background: rgba(16, 185, 223, 0.1); border: 1px solid rgba(16, 185, 223, 0.3); padding: 8px 14px; border-radius: 12px; font-size: 12.5px; color: #0369a1; font-weight: 600;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <line x1="12" y1="16" x2="12" y2="12" />
                    <line x1="12" y1="8" x2="12.01" y2="8" />
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round" class="eq-search-icon">
                    <circle cx="11" cy="11" r="8" />
                    <line x1="21" y1="21" x2="16.65" y2="16.65" />
                </svg>
                <input type="text" id="equipmentSearchInput" class="eq-search-input"
                    placeholder="Buscar por equipo, modelo, serie o calibración..." onkeyup="filterEquipmentLive()">
            </div>
        </div>

        <!-- Responsive Table -->
        <div class="table-responsive-box">
            <table class="modern-table" id="equipmentMasterTable">
                <thead>
                    <tr>
                        <th style="width: 45px; text-align: center;">#</th>
                        <th style="width: 65px; text-align: center;">Imagen</th>
                        <th style="width: 25%;">Nombre de Equipo</th>
                        <th style="width: 18%;">Modelo & Serie</th>
                        <th style="width: 32%;">Calibración & Recalibración</th>
                        <th style="width: 145px;">Estado</th>
                        <th style="width: 155px; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipments as $item)
                        <tr data-status="{{ $item['status'] }}">
                            <!-- 1. Número -->
                            <td
                                style="font-family: 'Outfit', sans-serif; font-weight: 800; color: #94a3b8; font-size: 13px; text-align: center;">
                                {{ $item['num'] }}
                            </td>

                            <!-- 2. Imagen / Fotografía -->
                            <td style="text-align: center;">
                                @if(!empty($item['image_url']))
                                    <div class="eq-thumb-cell" style="margin: 0 auto;"
                                        onclick="openPhotoModal('{{ $item['image_url'] }}', '{{ addslashes($item['name']) }} - {{ addslashes($item['model']) }}')"
                                        title="Ver imagen ampliada">
                                        <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}" class="eq-thumb-img">
                                    </div>
                                @else
                                    <div class="eq-thumb-cell" style="cursor: default; margin: 0 auto;" title="Sin fotografía">
                                        <div class="eq-thumb-placeholder">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                                <circle cx="9" cy="9" r="2" />
                                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                            </svg>
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- 3. Nombre de Equipo -->
                            <td>
                                <div
                                    style="font-weight: 700; color: var(--ink); font-size: 13.5px; line-height: 1.35; word-break: break-word;">
                                    {{ $item['name'] }}
                                </div>
                            </td>

                            <!-- 4. Modelo & Serie -->
                            <td>
                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: flex-start;">
                                    @if($item['model'] !== '—')
                                        <span class="eq-model-badge"
                                            title="Modelo: {{ $item['model'] }}">{{ $item['model'] }}</span>
                                    @else
                                        <span style="color: #94a3b8; font-size: 12px;">—</span>
                                    @endif

                                    @if($item['serial_number'] !== 'Serie')
                                        <span class="eq-serial-code" title="N° Serie: {{ $item['serial_number'] }}">Serie:
                                            {{ $item['serial_number'] }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- 5. Calibración & Próxima Recalibración -->
                            <td>
                                @if(!empty($item['calibration_date_formatted']))
                                    <div class="eq-calib-card">
                                        <div class="eq-calib-header">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#0891b2"
                                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                                                <line x1="16" y1="2" x2="16" y2="6" />
                                                <line x1="8" y1="2" x2="8" y2="6" />
                                                <line x1="3" y1="10" x2="21" y2="10" />
                                            </svg>
                                            <span class="eq-calib-tag-label">Calibración:</span>
                                            <span class="eq-calib-main-date">{{ $item['calibration_date_formatted'] }}</span>
                                            <span class="calib-pill {{ $item['calibration_badge_class'] }}">
                                                {{ $item['calibration_status_text'] }}
                                            </span>
                                        </div>
                                        <div class="eq-calib-next-legend">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10" />
                                                <polyline points="12 6 12 12 16 14" />
                                            </svg>
                                            <span>Próx. Recalibración: <strong
                                                    class="eq-calib-next-date">{{ $item['next_recalibration_date_formatted'] }}</strong></span>
                                        </div>
                                    </div>
                                @else
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span class="calib-pill calib-secondary">Sin calibración registrada</span>
                                    </div>
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
                                <div class="admin-actions-cell" style="justify-content: flex-end; gap: 6px;">
                                    <!-- Botón Calibración (Icono de calibración / medidor dial) -->
                                    <button type="button" class="btn-admin-icon-action theme-cyan"
                                        onclick="openCalibrationHistoryModal('{{ $item['id'] }}', '{{ addslashes($item['name']) }}', '{{ addslashes($item['model']) }}', '{{ addslashes($item['serial_number']) }}')"
                                        title="Calibración — {{ $item['name'] }}" aria-label="Calibración">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="m12 14 4-4" />
                                            <path d="M3.34 19a10 10 0 1 1 17.32 0" />
                                        </svg>
                                    </button>

                                    @if($canEdit ?? true)
                                        <!-- Editar Equipo -->
                                        <button type="button" class="btn-admin-icon-action theme-lime"
                                            onclick="openEditEquipmentModal('{{ $item['id'] }}', '{{ addslashes($item['name']) }}', '{{ addslashes($item['model'] === '—' ? '' : $item['model']) }}', '{{ addslashes($item['serial_number'] === 'Serie' ? '' : $item['serial_number']) }}', '{{ addslashes($item['raw_description']) }}', '{{ $item['status'] }}', '{{ $item['image_url'] ?? '' }}')"
                                            title="Editar Equipo" aria-label="Editar">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                                <path d="m15 5 4 4" />
                                            </svg>
                                        </button>

                                        <!-- Eliminar Equipo -->
                                        <button type="button" class="btn-admin-icon-action theme-danger"
                                            onclick="deleteEquipment('{{ $item['id'] }}', '{{ addslashes($item['name']) }}')"
                                            title="Eliminar Equipo" aria-label="Eliminar">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6" />
                                                <path
                                                    d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                <line x1="10" y1="11" x2="10" y2="17" />
                                                <line x1="14" y1="11" x2="14" y2="17" />
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
                Mostrando <strong id="eqPageStart">1</strong> a <strong id="eqPageEnd">10</strong> de <strong
                    id="eqPageTotal">{{ count($equipments) }}</strong> equipos
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
             MODAL: REGISTRO / EDICIÓN DE EQUIPO (DATOS TÉCNICOS + FOTOGRAFÍA)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="equipmentModal" onclick="if(event.target === this) closeEquipmentModal()">
        <div class="modal-dialog-2col">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div
                        style="width: 36px; height: 36px; border-radius: 10px; background: rgba(16, 185, 223, 0.12); color: #10b9df; display: grid; place-items: center;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="equipmentModalTitle" style="font-size: 19px; font-weight: 800; color: #0f1c2e; margin: 0;">
                            Alta de Equipo</h3>
                        <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Especificaciones técnicas y registro
                            fotográfico</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeEquipmentModal()"
                    aria-label="Cerrar modal">✕</button>
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
                                <label class="form-field-label">Nombre de Equipo <span
                                        style="color: #ef4444;">*</span></label>
                                <input type="text" id="eqName" name="name" class="custom-form-input"
                                    placeholder="Ej: Anemómetro, Sonómetro, Dosímetro..." required>
                            </div>

                            <!-- Modelo y N° de Serie -->
                            <div class="form-row-grid">
                                <div class="form-field-group">
                                    <label class="form-field-label">Modelo</label>
                                    <input type="text" id="eqModel" name="model" class="custom-form-input"
                                        placeholder="Ej: AirflowTes-Master">
                                </div>
                                <div class="form-field-group">
                                    <label class="form-field-label">N° de Serie</label>
                                    <input type="text" id="eqSerialNumber" name="serial_number" class="custom-form-input"
                                        placeholder="Ej: mbjb021078">
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
                                <label class="form-field-label">Descripción General</label>
                                <textarea id="eqDescription" name="description" class="custom-form-input" rows="3"
                                    placeholder="Accesorios incluidos, condiciones de transporte, etc."></textarea>
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
                                <div class="eq-upload-placeholder" id="eqUploadPlaceholder"
                                    onclick="document.getElementById('eqImageInput').click()">
                                    <div class="eq-upload-icon-circle">
                                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                                            <path
                                                d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z" />
                                            <circle cx="12" cy="13" r="3" />
                                        </svg>
                                    </div>
                                    <div class="eq-upload-title">Arrastra o sube una imagen</div>
                                    <div class="eq-upload-hint">Formatos: PNG, JPG, WEBP (Máx. 5MB)</div>
                                    <button type="button" class="btn-select-file"
                                        onclick="event.stopPropagation(); document.getElementById('eqImageInput').click()">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                            <polyline points="17 8 12 3 7 8" />
                                            <line x1="12" y1="3" x2="12" y2="15" />
                                        </svg>
                                        <span>Seleccionar Fotografía</span>
                                    </button>
                                </div>

                                <!-- Botón Quitar Foto (visible solo si hay imagen) -->
                                <button type="button" class="btn-remove-photo" id="btnRemovePhoto" style="display: none;"
                                    onclick="removeEquipmentPhoto()">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="18" y1="6" x2="6" y2="18" />
                                        <line x1="6" y1="6" x2="18" y2="18" />
                                    </svg>
                                    <span>Quitar Fotografía</span>
                                </button>

                                <!-- Input de archivo real -->
                                <input type="file" id="eqImageInput" name="image" accept="image/png, image/jpeg, image/webp"
                                    style="display: none;" onchange="handleImageSelection(event)">
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
             MODAL: CALIBRACIÓN DEL EQUIPO (CALIBRACIÓN - {EQUIPO})
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="equipmentCalibrationModal"
        onclick="if(event.target === this) closeCalibrationHistoryModal()">
        <div class="modal-dialog-history">
            <div class="modal-header-custom">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div
                        style="width: 38px; height: 38px; border-radius: 10px; background: rgba(16, 185, 223, 0.14); color: #0891b2; display: grid; place-items: center;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 14 4-4" />
                            <path d="M3.34 19a10 10 0 1 1 17.32 0" />
                        </svg>
                    </div>
                    <div>
                        <h3 id="calibModalTitle" style="font-size: 18px; font-weight: 800; color: #0f1c2e; margin: 0;">
                            Calibración</h3>
                        <p id="calibModalSubtitle" style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Cargando
                            detalles del equipo...</p>
                    </div>
                </div>
                <button type="button" class="btn-close-modal" onclick="closeCalibrationHistoryModal()"
                    aria-label="Cerrar modal">✕</button>
            </div>

            <div class="modal-body-custom">
                <!-- KPI Summary Banner -->
                <div class="calib-summary-banner">
                    <div class="calib-summary-item">
                        <span class="calib-summary-label">Última Calibración</span>
                        <span class="calib-summary-value" id="calibSummaryLastDate">—</span>
                    </div>
                    <div class="calib-summary-item">
                        <span class="calib-summary-label">Próxima Recalibración</span>
                        <span class="calib-summary-value" id="calibSummaryNextDate" style="color: #0284c7;">—</span>
                    </div>
                    <div class="calib-summary-item">
                        <span class="calib-summary-label">Estado de Vigencia</span>
                        <div id="calibSummaryStatusBadge">
                            <span class="calib-pill calib-secondary">Calculando...</span>
                        </div>
                    </div>
                    <div class="calib-summary-item">
                        <span class="calib-summary-label">Total Calibraciones</span>
                        <span class="calib-summary-value" id="calibSummaryTotalCount">0</span>
                    </div>
                </div>

                @if($canEdit ?? true)
                    <!-- Panel para Registrar Nueva Recalibración (+365 días) -->
                    <div class="calib-add-panel">
                        <div class="calib-add-title">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0891b2" stroke-width="2.3"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            <span>Registrar Nueva Calibración</span>
                        </div>

                        <form id="newRecalibrationForm" onsubmit="submitNewRecalibration(event)">
                            <input type="hidden" id="newRecalibEquipmentId" value="">

                            <div class="form-row-grid" style="margin-bottom: 12px;">
                                <div class="form-field-group" style="margin-bottom: 0;">
                                    <label class="form-field-label">Fecha de Calibración <span
                                            style="color: #ef4444;">*</span></label>
                                    <input type="date" id="newRecalibDate" name="calibration_date" class="custom-form-input"
                                        required onchange="handleCalibrationDateInput(this.value, 'newRecalibCalcBox')">
                                </div>
                                <div class="form-field-group" style="margin-bottom: 0;">
                                    <label class="form-field-label">Registrado Por / Técnico</label>
                                    <input type="text" id="newRecalibPerformer" name="performed_by" class="custom-form-input"
                                        value="{{ $userName }}" placeholder="Nombre del técnico o responsable">
                                </div>
                            </div>

                            <div class="calib-calc-box" id="newRecalibCalcBox" style="display: none; margin-bottom: 12px;">
                                <div class="calib-calc-icon">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10" />
                                        <polyline points="12 6 12 12 16 14" />
                                    </svg>
                                </div>
                                <div class="calib-calc-text">
                                    <strong>Próxima fecha de recalibración (365 días):</strong><br>
                                    <span id="newRecalibDateText"
                                        style="font-weight: 800; font-size: 13px; color: #0284c7;">—</span>
                                </div>
                            </div>

                            <div class="form-field-group" style="margin-bottom: 12px;">
                                <label class="form-field-label">Observación de Recalibración</label>
                                <textarea id="newRecalibObservation" name="observation" class="custom-form-input" rows="2"
                                    placeholder="Laboratorio, número de certificado, resultados o ajustes efectuados..."></textarea>
                            </div>

                            <div style="display: flex; justify-content: flex-end;">
                                <button type="submit" class="btn-primary-hero-action" id="btnSubmitRecalibration">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12" />
                                    </svg>
                                    <span>Guardar</span>
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Tabla de Historial Cronológico -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden;">
                    <div
                        style="padding: 12px 16px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-weight: 800; font-size: 13px; color: #0f1c2e; display: flex; align-items: center; justify-content: space-between;">
                        <span>Registro Cronológico de Calibraciones</span>
                        <span id="calibHistoryBadgeCount" style="font-size: 11.5px; color: #64748b; font-weight: 600;">0
                            registros</span>
                    </div>

                    <div style="max-height: 280px; overflow-y: auto;">
                        <table class="calib-history-table">
                            <thead>
                                <tr>
                                    <th style="width: 130px;">Fecha Calib.</th>
                                    <th style="width: 170px;">Próx. Recalibración</th>
                                    <th>Observación</th>
                                    <th style="width: 140px;">Registrado Por</th>
                                    @if($canEdit ?? true)
                                        <th style="width: 60px; text-align: center;">Acción</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="calibHistoryTableBody">
                                <tr>
                                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">
                                        Cargando historial de calibración...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer-custom">
                <button type="button" class="btn-subtle-link" onclick="closeCalibrationHistoryModal()">Cerrar</button>
            </div>
        </div>
    </div>

    <!-- ==========================================================================
             MODAL: VISOR DE FOTOGRAFÍA AMPLIADA (LIGHTBOX)
             ========================================================================== -->
    <div class="modal-backdrop-custom" id="photoViewerModal" onclick="if(event.target === this) closePhotoModal()">
        <div class="photo-viewer-dialog">
            <div class="photo-viewer-header">
                <div class="photo-viewer-title" id="photoViewerTitle">Fotografía del Equipo</div>
                <button type="button" class="btn-close-modal" onclick="closePhotoModal()"
                    aria-label="Cerrar visor">✕</button>
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
         * METRIC V2 — Control de Equipos, Calibraciones (+365 días) e Historial
         */

        // Función auxiliar para sumar 365 días a una fecha en formato YYYY-MM-DD
        function calculate365DaysNextDate(dateString) {
            if (!dateString) return null;
            const parts = dateString.split('-');
            if (parts.length !== 3) return null;

            const year = parseInt(parts[0], 10);
            const month = parseInt(parts[1], 10) - 1;
            const day = parseInt(parts[2], 10);

            const d = new Date(Date.UTC(year, month, day));
            d.setUTCDate(d.getUTCDate() + 365);

            const nextYear = d.getUTCFullYear();
            const nextMonth = String(d.getUTCMonth() + 1).padStart(2, '0');
            const nextDay = String(d.getUTCDate()).padStart(2, '0');

            return {
                raw: `${nextYear}-${nextMonth}-${nextDay}`,
                formatted: `${nextDay}/${nextMonth}/${nextYear}`
            };
        }

        // Handler reactivo de fecha en input de calibración
        function handleCalibrationDateInput(dateValue, calcBoxId) {
            const calcBox = document.getElementById(calcBoxId);
            if (!calcBox) return;

            if (!dateValue) {
                calcBox.style.display = 'none';
                return;
            }

            const nextObj = calculate365DaysNextDate(dateValue);
            if (nextObj) {
                calcBox.style.display = 'flex';
                const targetTextEl = document.getElementById('newRecalibDateText');
                if (targetTextEl) {
                    targetTextEl.innerText = `${nextObj.formatted} (en 365 días)`;
                }
            } else {
                calcBox.style.display = 'none';
            }
        }

        // Modal Alta / Edición de Equipo (Sin campos de calibración)
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

        // ==========================================================================
        // MODAL DE CALIBRACIÓN (CALIBRACIÓN - {EQUIPO})
        // ==========================================================================
        let currentActiveEquipmentId = null;

        function openCalibrationHistoryModal(equipmentId, name, model, serialNumber) {
            currentActiveEquipmentId = equipmentId;

            document.getElementById('calibModalTitle').innerText = `Calibración - ${name}`;
            document.getElementById('calibModalSubtitle').innerText = `Modelo: ${model || '—'} | N° Serie: ${serialNumber || 'Serie'}`;

            const newEqIdField = document.getElementById('newRecalibEquipmentId');
            if (newEqIdField) newEqIdField.value = equipmentId;

            // Reset nuevo formulario de recalibración
            const recalibDateInput = document.getElementById('newRecalibDate');
            if (recalibDateInput) {
                const todayStr = new Date().toISOString().split('T')[0];
                recalibDateInput.value = todayStr;
                handleCalibrationDateInput(todayStr, 'newRecalibCalcBox');
            }
            const recalibObs = document.getElementById('newRecalibObservation');
            if (recalibObs) recalibObs.value = '';

            // Cargar historial por AJAX
            fetchEquipmentCalibrationHistory(equipmentId);

            const modal = document.getElementById('equipmentCalibrationModal');
            modal.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function closeCalibrationHistoryModal() {
            const modal = document.getElementById('equipmentCalibrationModal');
            modal.classList.remove('open');
            document.body.style.overflow = '';
        }

        function fetchEquipmentCalibrationHistory(equipmentId) {
            const tbody = document.getElementById('calibHistoryTableBody');
            tbody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center; color: #94a3b8; padding: 24px;">
                    Cargando historial de calibración...
                </td>
            </tr>
        `;

            fetch(`/equipos/${equipmentId}/calibraciones`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #ef4444; padding: 20px;">Error al cargar calibraciones.</td></tr>`;
                        return;
                    }

                    const eq = data.equipment;
                    const calibrations = data.calibrations;

                    // Actualizar banner KPI
                    document.getElementById('calibSummaryLastDate').innerText = eq.current_calibration_date || 'Sin calibrar';
                    document.getElementById('calibSummaryNextDate').innerText = eq.current_next_recalibration_date ? `${eq.current_next_recalibration_date}` : '—';
                    document.getElementById('calibSummaryTotalCount').innerText = calibrations.length;
                    document.getElementById('calibHistoryBadgeCount').innerText = `${calibrations.length} registro(s)`;

                    // Badge de estado
                    const badgeContainer = document.getElementById('calibSummaryStatusBadge');
                    if (calibrations.length > 0) {
                        const first = calibrations[0];
                        let badgeClass = 'calib-success';
                        if (first.status_badge === 'vencido') badgeClass = 'calib-danger';
                        if (first.status_badge === 'por_vencer') badgeClass = 'calib-warning';

                        badgeContainer.innerHTML = `<span class="calib-pill ${badgeClass}">${first.status_text}</span>`;
                    } else {
                        badgeContainer.innerHTML = `<span class="calib-pill calib-secondary">Sin calibrar</span>`;
                    }

                    // Renderizar filas de historial
                    if (calibrations.length === 0) {
                        tbody.innerHTML = `
                    <tr>
                        <td colspan="5" style="text-align: center; color: #64748b; padding: 28px;">
                            No existen registros de calibración para este equipo.
                        </td>
                    </tr>
                `;
                        return;
                    }

                    let rowsHtml = '';
                    calibrations.forEach((c, idx) => {
                        let badgeClass = 'calib-success';
                        if (c.status_badge === 'vencido') badgeClass = 'calib-danger';
                        if (c.status_badge === 'por_vencer') badgeClass = 'calib-warning';

                        rowsHtml += `
                    <tr>
                        <td>
                            <div style="font-weight: 800; color: #0f1c2e; font-size: 13px;">${c.calibration_date}</div>
                            ${idx === 0 ? '<span style="font-size: 10.5px; background: #e0f2fe; color: #0369a1; padding: 1px 6px; border-radius: 4px; font-weight: 700;">Última Vigente</span>' : ''}
                        </td>
                        <td>
                            <div style="font-weight: 700; color: #0284c7; font-size: 13px;">${c.next_recalibration_date}</div>
                        </td>
                        <td>
                            <div style="font-size: 12.5px; color: #334155; line-height: 1.35;">${c.observation}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: #0f1c2e; font-size: 12px;">${c.performed_by}</div>
                            <div style="font-size: 11px; color: #94a3b8;">${c.created_at}</div>
                        </td>
                        @if($canEdit ?? true)
                            <td style="text-align: center;">
                                <button 
                                    type="button" 
                                    class="btn-admin-icon-action theme-danger" 
                                    style="width: 30px; height: 30px;" 
                                    onclick="deleteCalibrationRecord(${c.id})" 
                                    title="Eliminar este registro"
                                >
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                </button>
                            </td>
                        @endif
                    </tr>
                `;
                    });

                    tbody.innerHTML = rowsHtml;
                })
                .catch(err => {
                    console.error(err);
                    tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: #ef4444; padding: 20px;">Error de red al cargar calibraciones.</td></tr>`;
                });
        }

        // Guardar nueva calibración (+365 días) por AJAX
        function submitNewRecalibration(event) {
            event.preventDefault();
            if (!currentActiveEquipmentId) return;

            const btn = document.getElementById('btnSubmitRecalibration');
            btn.disabled = true;
            btn.innerHTML = `<span>Guardando...</span>`;

            const calibrationDate = document.getElementById('newRecalibDate').value;
            const observation = document.getElementById('newRecalibObservation').value;
            const performedBy = document.getElementById('newRecalibPerformer').value;

            fetch(`/equipos/${currentActiveEquipmentId}/calibraciones`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    calibration_date: calibrationDate,
                    observation: observation,
                    performed_by: performedBy
                })
            })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>Guardar</span>
            `;

                    if (data.success) {
                        document.getElementById('newRecalibObservation').value = '';
                        fetchEquipmentCalibrationHistory(currentActiveEquipmentId);

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Calibración Guardada!',
                                text: 'La calibración y su próxima fecha de recalibración han sido registradas.',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            alert('Calibración registrada correctamente.');
                            window.location.reload();
                        }
                    } else {
                        alert(data.message || 'Error al guardar la calibración.');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = `<span>Guardar</span>`;
                    alert('Error de conexión al guardar.');
                });
        }

        // Eliminar un registro específico con SweetAlert
        function deleteCalibrationRecord(calibrationId) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Eliminar Calibración?',
                    text: '¿Estás seguro de eliminar este registro del historial? Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        performDeleteCalibration(calibrationId);
                    }
                });
            } else {
                if (confirm('¿Estás seguro de eliminar este registro del historial?')) {
                    performDeleteCalibration(calibrationId);
                }
            }
        }

        function performDeleteCalibration(calibrationId) {
            fetch(`/equipos/calibraciones/${calibrationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: 'El registro de calibración fue eliminado.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                        fetchEquipmentCalibrationHistory(currentActiveEquipmentId);
                    } else {
                        alert(data.message || 'Error al eliminar.');
                    }
                })
                .catch(err => {
                    alert('Error de conexión al eliminar.');
                });
        }

        // ==========================================================================
        // PREVISUALIZACIÓN DE IMÁGENES & DRAG & DROP
        // ==========================================================================
        function handleImageSelection(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen seleccionada supera el límite máximo de 5MB.');
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function (e) {
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
        document.addEventListener('DOMContentLoaded', function () {
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