@extends('layouts.app')

@section('title', 'Anexo 2 y Matrices Normativas ROSA — Metric v2')

@push('styles')
    @metricStyle('ergonomia_rosa')
    <style>
        .rosa-dashboard-container {
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }
        .doc-editable-narrative {
            outline: none;
            min-height: 22px;
            padding: 3px 5px;
            border: 1px dashed transparent;
            border-radius: 4px;
            transition: all 0.2s ease;
            cursor: text;
        }
        .doc-editable-narrative:hover {
            border-color: #94a3b8;
            background-color: #f8fafc;
        }
        .doc-editable-narrative:focus {
            border-color: #0284c7 !important;
            background-color: #ffffff !important;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }
        .doc-editable-narrative:empty:before {
            content: attr(data-placeholder);
            color: #94a3b8;
            font-style: italic;
        }
        .syso-live-input {
            width: 100%;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 2px 4px;
            font-family: inherit;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            background: transparent;
            outline: none;
            box-sizing: border-box;
            transition: all 0.15s ease;
        }
        .syso-live-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .syso-live-input:focus {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }
        .syso-live-textarea {
            width: 100%;
            border: 1px solid transparent;
            border-radius: 4px;
            padding: 2px 4px;
            font-family: Arial, sans-serif;
            font-size: inherit;
            font-weight: inherit;
            color: inherit;
            background: transparent;
            outline: none;
            box-sizing: border-box;
            resize: vertical;
            line-height: 1.35;
            white-space: pre-wrap;
            transition: all 0.15s ease;
            overflow-y: hidden;
            display: block;
        }
        .syso-live-textarea:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .syso-live-textarea:focus {
            border-color: #0284c7;
            background: #ffffff;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.15);
        }
        .table-row-actions-left {
            position: absolute;
            right: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-right: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            z-index: 10;
            white-space: nowrap;
        }
        .btn-add-table-row {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            background-color: #0284c7;
            color: #ffffff;
            border: 1px solid #0369a1;
            font-size: 14px;
            font-weight: bold;
            line-height: 1;
            cursor: pointer;
            transition: all 0.15s ease;
            padding: 0;
            box-shadow: 0 1px 3px rgba(2, 132, 199, 0.3);
            user-select: none;
        }
        .btn-add-table-row:hover {
            background-color: #0369a1;
            transform: scale(1.1);
        }
        .btn-add-table-row:active {
            transform: scale(0.95);
        }
        .btn-del-table-row {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 19px;
            height: 19px;
            border-radius: 4px;
            background-color: #ef4444;
            color: #ffffff;
            border: 1px solid #dc2626;
            font-size: 13px;
            font-weight: bold;
            line-height: 1;
            cursor: pointer;
            transition: all 0.15s ease;
            padding: 0;
            box-shadow: 0 1px 3px rgba(239, 68, 68, 0.3);
            user-select: none;
        }
        .btn-del-table-row:hover {
            background-color: #dc2626;
            transform: scale(1.1);
        }
        .btn-del-table-row:active {
            transform: scale(0.95);
        }
        @media print {
            .table-row-actions-left, .btn-add-table-row, .btn-del-table-row {
                display: none !important;
            }
        }
        /* ==========================================================================
           MATRICES NORMATIVAS ROSA (TABLAS A, B, C, D, E) — PREMIUM METRIC DESIGN
           ========================================================================== */
        .rosa-matrices-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1200px) {
            .rosa-matrices-grid {
                grid-template-columns: 1fr;
            }
        }
        .rosa-matrix-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 10px 30px -5px rgba(15, 28, 46, 0.05), 0 2px 6px -1px rgba(15, 28, 46, 0.02);
            display: flex;
            flex-direction: column;
            align-items: stretch;
            transition: all 0.25s ease;
            position: relative;
            overflow: hidden;
        }
        .rosa-matrix-card:hover {
            box-shadow: 0 16px 36px -6px rgba(15, 28, 46, 0.09);
            border-color: #cbd5e1;
        }
        .rosa-matrix-card-wide {
            grid-column: 1 / -1;
        }
        .rosa-matrix-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid #f1f5f9;
        }
        .rosa-matrix-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .rosa-matrix-badge {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 15px;
            display: grid;
            place-items: center;
            flex-shrink: 0;
            color: #ffffff;
        }
        .rosa-matrix-badge.badge-a {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
        }
        .rosa-matrix-badge.badge-b {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
        }
        .rosa-matrix-badge.badge-c {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
        }
        .rosa-matrix-badge.badge-d {
            background: linear-gradient(135deg, #ea580c 0%, #c2410c 100%);
            box-shadow: 0 4px 10px rgba(234, 88, 12, 0.3);
        }
        .rosa-matrix-badge.badge-e {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            box-shadow: 0 4px 10px rgba(220, 38, 38, 0.3);
        }
        .rosa-matrix-title {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .rosa-matrix-subtitle {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 500;
        }
        .rosa-posture-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 3px 10px;
            border-radius: 8px;
            font-size: 11.5px;
            color: #334155;
            font-weight: 600;
        }
        .rosa-section-badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .risk-inapreciable {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }
        .risk-bajo {
            background-color: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .risk-medio {
            background-color: #fef9c3;
            color: #854d0e;
            border: 1px solid #fde047;
        }
        .risk-alto {
            background-color: #ffedd5;
            color: #9a3412;
            border: 1px solid #fdba74;
        }
        .risk-muy-alto {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Scorecards de Resumen ROSA */
        .rosa-scorecard-widget {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
            box-shadow: 0 2px 6px rgba(15, 28, 46, 0.02);
        }
        .rosa-scorecard-header {
            background: #f1f5f9;
            padding: 8px 14px;
            font-size: 11.5px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .rosa-scorecard-body {
            padding: 10px 14px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 10px;
        }
        .rosa-scorecard-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.15s ease;
        }
        .rosa-scorecard-item:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .rosa-scorecard-item .item-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }
        .rosa-scorecard-item .item-val {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
        }
        .rosa-scorecard-footer {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-top: 1.5px solid #bfdbfe;
            padding: 9px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .rosa-scorecard-footer.footer-purple {
            background: linear-gradient(135deg, #f5f3ff, #ede9fe);
            border-color: #ddd6fe;
        }
        .rosa-scorecard-footer.footer-emerald {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-color: #a7f3d0;
        }
        .rosa-scorecard-footer.footer-orange {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            border-color: #fed7aa;
        }
        .rosa-scorecard-footer.footer-red {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            border-color: #fecaca;
        }
        .rosa-scorecard-footer .footer-label {
            font-size: 12px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .rosa-scorecard-footer.footer-purple .footer-label { color: #6d28d9; }
        .rosa-scorecard-footer.footer-emerald .footer-label { color: #047857; }
        .rosa-scorecard-footer.footer-orange .footer-label { color: #c2410c; }
        .rosa-scorecard-footer.footer-red .footer-label { color: #b91c1c; }
        .rosa-scorecard-footer .footer-val {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 900;
            color: #1d4ed8;
            background: #ffffff;
            padding: 3px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
        }
        .rosa-scorecard-footer.footer-purple .footer-val { color: #6d28d9; box-shadow: 0 2px 6px rgba(109, 40, 217, 0.2); }
        .rosa-scorecard-footer.footer-emerald .footer-val { color: #047857; box-shadow: 0 2px 6px rgba(5, 150, 105, 0.2); }
        .rosa-scorecard-footer.footer-orange .footer-val { color: #c2410c; box-shadow: 0 2px 6px rgba(234, 88, 12, 0.2); }
        .rosa-scorecard-footer.footer-red .footer-val { color: #b91c1c; box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2); }

        /* Matrix Grid Tables */
        .rosa-table-scroll-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 8px rgba(15, 28, 46, 0.03);
            background: #ffffff;
        }
        .rosa-grid-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 11.5px;
            text-align: center;
            background: #ffffff;
        }
        .rosa-grid-table th, .rosa-grid-table td {
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 6px 7px;
            min-width: 26px;
            height: 28px;
            text-align: center;
            vertical-align: middle;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .rosa-grid-table th:last-child, .rosa-grid-table td:last-child {
            border-right: none;
        }
        .rosa-grid-table tr:last-child td, .rosa-grid-table tr:last-child th {
            border-bottom: none;
        }
        .rosa-th-primary {
            background: #0f1c2e;
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .rosa-th-secondary {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 11px;
        }
        .rosa-th-sub {
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            font-size: 10.5px;
        }
        .rosa-th-rowhead {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 11px;
            padding: 4px 8px;
        }
        .rosa-grid-table td {
            color: #334155;
            font-weight: 600;
            background: #ffffff;
        }
        .rosa-grid-table tr:hover td:not(.rosa-highlight-cell) {
            background-color: #f8fafc;
        }
        .rosa-grid-table td:hover:not(.rosa-highlight-cell) {
            background-color: #f0f9ff;
            color: #0284c7;
        }
        .rosa-highlight-cell, .cell-selected {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            border-radius: 4px;
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #f59e0b, 0 4px 10px rgba(217, 119, 6, 0.45) !important;
            position: relative;
            z-index: 10;
            font-size: 12.5px;
        }
        .cell-shaded {
            background-color: #fef3c7 !important;
            color: #92400e !important;
            font-weight: 700;
        }
        .matrix-footnote {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 11.5px;
            font-style: italic;
            color: #64748b;
            margin-top: 10px;
            text-align: center;
            width: 100%;
        }

        /* Modal de Prompts y Análisis Técnico */
        .modal-dialog-prompts {
            max-width: 920px;
            width: 95%;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        .prompts-form-grid {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .prompts-field-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .prompts-label-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .prompts-label {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .prompts-textarea {
            width: 100%;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 13px;
            color: #1e293b;
            line-height: 1.5;
            background: #ffffff;
            resize: vertical;
            transition: all 0.2s ease;
        }
        .prompts-textarea:focus {
            outline: none;
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .prompts-btn-mini {
            padding: 3px 8px;
            font-size: 11.5px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #475569;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s ease;
        }
        .prompts-btn-mini:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        /* ==========================================================================
           STEPPER HEADER BAR — DISEÑO GRID RESPONSIVO SIN SCROLL HORIZONTAL
           ========================================================================== */
        .stepper-header-bar {
            background: #ffffff;
            border: 1px solid rgba(203, 213, 225, 0.9);
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }
        .stepper-nav-track {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 6px;
            padding: 8px 10px;
            overflow: visible;
            scrollbar-width: none;
            align-items: stretch;
        }
        .stepper-nav-track::-webkit-scrollbar {
            display: none;
        }
        .step-nav-btn {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 10px;
            border-radius: 10px;
            background: transparent;
            border: 1.5px solid transparent;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            text-align: left;
            white-space: normal;
            min-width: 0;
            width: 100%;
        }
        .step-nav-btn:hover:not(.active) {
            background: #f8fafc;
            border-color: #e2e8f0;
        }
        .step-nav-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 12.5px;
            font-weight: 800;
            background: #f1f5f9;
            color: #64748b;
            border: 1.5px solid #cbd5e1;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }
        .step-nav-text {
            display: flex;
            flex-direction: column;
            min-width: 0;
            overflow: hidden;
        }
        .step-nav-title {
            font-size: 12.5px;
            font-weight: 700;
            color: #334155;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .step-nav-subtitle {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .step-nav-btn.active {
            background: #f0f9ff;
            border-color: #0284c7;
            box-shadow: 0 2px 8px rgba(2, 132, 199, 0.12);
        }
        .step-nav-btn.active .step-nav-number {
            background: linear-gradient(135deg, #10b9df 0%, #0284c7 100%);
            color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.35);
        }
        .step-nav-btn.active .step-nav-title {
            color: #0284c7;
            font-weight: 800;
        }
        .step-nav-btn.active .step-nav-subtitle {
            color: #0369a1;
        }
        .step-nav-btn.completed .step-nav-number {
            background: #ecfdf5;
            color: #059669;
            border-color: #a7f3d0;
        }
        @media (max-width: 1180px) {
            .step-nav-subtitle {
                display: none;
            }
            .step-nav-btn {
                padding: 8px 6px;
                gap: 7px;
            }
        }
        @media (max-width: 720px) {
            .stepper-nav-track {
                gap: 4px;
                padding: 6px;
            }
            .step-nav-title {
                display: none;
            }
            .step-nav-btn {
                padding: 6px;
                justify-content: center;
            }
        }
    </style>
@endpush

@section('content')
    <div class="stepper-page-wrapper">

        <!-- 1. Header Banner & Navegación -->
        <div class="illumination-header-banner" style="margin-bottom: 20px;">
            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <a href="{{ route('modules.ergonomia_rosa', $module->id) }}" class="btn-secondary-subtle"
                        style="padding: 6px 14px; font-size: 12px;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12" />
                            <polyline points="12 19 5 12 12 5" />
                        </svg>
                        <span>Volver al Monitoreo</span>
                    </a>
                    <span style="font-size: 12px; color: #94a3b8;">/</span>
                    <span
                        style="font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 9999px; border: 1px solid #bae6fd;">
                        {{ $installationName }}
                    </span>
                </div>
                <h1>
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="3" rx="2" />
                        <path d="M3 9h18" />
                        <path d="M3 15h18" />
                        <path d="M9 3v18" />
                    </svg>
                    <span>Ergonomía ROSA — Anexo 2 & Matrices Normativas</span>
                </h1>
                <p style="margin: 0; color: #64748b; font-size: 13.5px;">
                    Registro oficial de identificación de factores disergonómicos y catálogo técnico del método ROSA (Sonne,
                    Villalta & Andrews)
                </p>
            </div>

            <div class="header-action-group" style="display: flex; align-items: center; gap: 10px;">
                <a href="{{ route('modules.ergonomia_rosa', $module->id) }}" class="btn-secondary-subtle" style="padding: 7px 14px; font-size: 13px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    <span>Ir a Evaluaciones</span>
                </a>
            </div>
        </div>

        <!-- 2. Barra Superior de Pasos Interactivos (Grid 4 Columnas) -->
        <div class="stepper-header-bar">
            <div class="stepper-nav-track" id="rosaStepperTrack">

                <!-- Paso 1 -->
                <button type="button" class="step-nav-btn active" data-step="1" onclick="goToStep(1)" title="Paso 1: Registro Anexo 2">
                    <div class="step-nav-number">1</div>
                    <div class="step-nav-text">
                        <span class="step-nav-title">Paso 1: Anexo 2</span>
                        <span class="step-nav-subtitle">Factores de Riesgo</span>
                    </div>
                </button>

                <!-- Paso 2 -->
                <button type="button" class="step-nav-btn" data-step="2" onclick="goToStep(2)" title="Paso 2: Tablas Normativas ROSA">
                    <div class="step-nav-number">2</div>
                    <div class="step-nav-text">
                        <span class="step-nav-title">Paso 2: Tablas ROSA</span>
                        <span class="step-nav-subtitle">Matrices A - E</span>
                    </div>
                </button>

                <!-- Paso 3 -->
                <button type="button" class="step-nav-btn" data-step="3" onclick="goToStep(3)" title="Paso 3: Evaluación Detallada">
                    <div class="step-nav-number">3</div>
                    <div class="step-nav-text">
                        <span class="step-nav-title">Paso 3: Evaluación</span>
                        <span class="step-nav-subtitle">Informe Técnico</span>
                    </div>
                </button>

                <!-- Paso 4 -->
                <button type="button" class="step-nav-btn" data-step="4" onclick="goToStep(4)" title="Paso 4: Registro Nº 3 (Medidas Correctivas y Preventivas)">
                    <div class="step-nav-number">4</div>
                    <div class="step-nav-text">
                        <span class="step-nav-title">Paso 4: Medidas M.C.P.</span>
                        <span class="step-nav-subtitle">Registro Nº 3</span>
                    </div>
                </button>

                <!-- Paso 5 -->
                <button type="button" class="step-nav-btn" data-step="5" onclick="goToStep(5)" title="Paso 5: Registro Nº 4 (Matriz de Seguimiento)">
                    <div class="step-nav-number">5</div>
                    <div class="step-nav-text">
                        <span class="step-nav-title">Paso 5: Seguimiento</span>
                        <span class="step-nav-subtitle">Registro Nº 4</span>
                    </div>
                </button>

            </div>

            <!-- Barra de Progreso del Stepper -->
            <div class="stepper-progress-track">
                <div class="stepper-progress-fill" id="stepperProgressBar" style="width: 20%;"></div>
            </div>
        </div>

        <!-- 3. Contenido de los Pasos (Full Width) -->
        <div class="stepper-content-area">

            <!-- ========================================================================= -->
            <!-- PASO 1: REGISTRO ANEXO 2 (HOJA EDITABLE CARTA PARA WORD)                 -->
            <!-- ========================================================================= -->
            <div class="step-pane-content active" id="step_pane_1">

                <div class="anexo2-sheet-wrapper">

                    <!-- Barra de Herramientas de la Hoja -->
                    <div class="anexo2-sheet-toolbar">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="step-pane-badge" style="margin-bottom: 0;">Formato Oficial</span>
                            <span id="headerAutoSaveBadge" class="header-auto-save-status">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Guardado</span>
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" class="btn-download-sheet" onclick="downloadAnexo2Doc()"
                                title="Descargar documento oficial compatible con Microsoft Word (.doc) en formato vertical y Arial 9pt">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span>Descargar Word (.doc)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Documento Anexo 2 (Diseño Fiel Formato Carta - Idéntico a Imagen Oficial) -->
                    <div class="anexo2-sheet-card" id="anexo2DocumentSheet"
                        style="font-family: Arial, sans-serif; font-size: 9pt;">

                        <!-- Encabezado del Formato -->
                        <div style="text-align: center; margin-bottom: 12px;">
                            <div
                                style="font-size: 11pt; font-weight: bold; letter-spacing: 0.5px; color: #000000; text-transform: uppercase; font-family: Arial, sans-serif;">
                                ANEXO 2</div>
                            <div
                                style="font-size: 9.5pt; font-weight: bold; letter-spacing: 0.3px; color: #000000; text-transform: uppercase; font-family: Arial, sans-serif;">
                                REGISTRO Nº 1: IDENTIFICACIÓN DE FACTORES DE RIESGOS DISERGONÓMICOS
                            </div>
                        </div>

                        <!-- Tabla 1: Datos Generales de la Empresa y Puesto -->
                        <table class="anexo2-table" id="anexo2GeneralDataTable"
                            style="margin-bottom: 10px; font-family: Arial, sans-serif; font-size: 9pt; width: 100%;">
                            <tbody>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Razón Social:</td>
                                    <td colspan="3" id="anexo2_val_razon_social"
                                        style="width: 72%; padding: 2.5px 6px; font-weight: bold;">
                                        {{ $anexo2Data['razon_social'] ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Dirección de la empresa o establecimiento laboral:</td>
                                    <td colspan="3" id="anexo2_val_direccion"
                                        style="width: 72%; padding: 2.5px 6px; font-weight: bold;">
                                        {{ $anexo2Data['direccion'] ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">Área
                                        y Sector en estudio:</td>
                                    <td id="anexo2_val_area_sector"
                                        style="width: 32%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['area_sector'] ?? 'Administración' }}
                                    </td>
                                    <td style="width: 24%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">N°
                                        de trabajadores:</td>
                                    <td id="anexo2_val_num_trabajadores"
                                        style="width: 16%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['num_trabajadores'] ?? 1 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Puesto de trabajo:</td>
                                    <td colspan="3" id="anexo2_val_puesto_trabajo"
                                        style="width: 72%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['puesto_trabajo'] ?? 'Gerente general' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Procedimiento de trabajo escrito:</td>
                                    <td id="anexo2_val_procedimiento_escrito"
                                        style="width: 32%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['procedimiento_escrito'] ?? 'SI' }}
                                    </td>
                                    <td style="width: 24%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Capacitación:</td>
                                    <td id="anexo2_val_capacitacion"
                                        style="width: 16%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['capacitacion'] ?? 'Si' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Nombre del trabajador/es:</td>
                                    <td colspan="3" id="anexo2_val_nombre_trabajador"
                                        style="width: 72%; padding: 2.5px 6px;">
                                        {{ $anexo2Data['nombre_trabajador'] ?? '' }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Manifestación temprana:</td>
                                    <td id="anexo2_val_manifestacion_temprana"
                                        style="width: 32%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['manifestacion_temprana'] ?? 'No' }}
                                    </td>
                                    <td style="width: 24%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Ubicación del síntoma:</td>
                                    <td id="anexo2_val_ubicacion_sintoma"
                                        style="width: 16%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['ubicacion_sintoma'] ?? 'Ninguna' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Título de Sección Paso 1 -->
                        <div
                            style="font-size: 9pt; font-weight: bold; color: #000000; margin: 8px 0 5px 0; font-family: Arial, sans-serif;">
                            PASO 1: Identificar, las tareas y los factores de riesgo que se presentan de forma habitual en
                            el puesto de trabajo.
                        </div>

                        <!-- Tabla 2: Matriz de Identificación de Factores de Riesgo -->
                        <table class="anexo2-table" id="anexo2FactorsMatrixTable"
                            style="margin-bottom: 5px; font-family: Arial, sans-serif; font-size: 8.5pt;">
                            <colgroup>
                                <col style="width: 4%;">
                                <col style="width: 24%;">
                                <col style="width: 12%;">
                                <col style="width: 12%;">
                                <col style="width: 12%;">
                                <col style="width: 12%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                                <col style="width: 8%;">
                            </colgroup>
                            <thead>
                                <tr
                                    style="background-color: #deebf7; background: #deebf7; color: #000000; text-align: center; font-weight: bold;">
                                    <th colspan="2" rowspan="2"
                                        style="width: 28%; text-align: center; vertical-align: middle; border: 1px solid #000000; font-size: 8.5pt; font-weight: bold; background-color: #deebf7; background: #deebf7; padding: 4px 4px;">
                                        Factor de riesgo de la jornada habitual de trabajo
                                    </th>
                                    <th colspan="3"
                                        style="width: 36%; text-align: center; vertical-align: middle; border: 1px solid #000000; font-size: 8.5pt; font-weight: bold; background-color: #deebf7; background: #deebf7; padding: 4px 3px;">
                                        Tareas habituales del Puesto de Trabajo
                                    </th>
                                    <th rowspan="2"
                                        style="width: 12%; text-align: center; vertical-align: middle; font-size: 8pt; line-height: 1.15; border: 1px solid #000000; font-weight: bold; background-color: #deebf7; background: #deebf7; padding: 4px 2px;">
                                        Tiempo total de exposición al Factor de Riesgo
                                    </th>
                                    <th colspan="3"
                                        style="width: 24%; text-align: center; vertical-align: middle; border: 1px solid #000000; font-size: 8.5pt; font-weight: bold; background-color: #deebf7; background: #deebf7; padding: 4px 3px;">
                                        Nivel de Riesgo
                                    </th>
                                </tr>
                                <tr
                                    style="background-color: #ffffff; background: #ffffff; text-align: center; font-size: 7.5pt;">
                                    <th id="anexo2_header_tarea_1"
                                        style="width: 12%; padding: 3px 2px; border: 1px solid #000000; font-size: 7.5pt; font-weight: normal; line-height: 1.15; vertical-align: middle; background-color: #ffffff; background: #ffffff; text-align: center;">
                                        {{ !empty($anexo2Data['tareas']['tarea_1']) ? $anexo2Data['tareas']['tarea_1'] : '---' }}
                                    </th>
                                    <th id="anexo2_header_tarea_2"
                                        style="width: 12%; padding: 3px 2px; border: 1px solid #000000; font-size: 7.5pt; font-weight: normal; line-height: 1.15; vertical-align: middle; background-color: #ffffff; background: #ffffff; text-align: center;">
                                        {{ !empty($anexo2Data['tareas']['tarea_2']) ? $anexo2Data['tareas']['tarea_2'] : '---' }}
                                    </th>
                                    <th id="anexo2_header_tarea_3"
                                        style="width: 12%; padding: 3px 2px; border: 1px solid #000000; font-size: 7.5pt; font-weight: normal; line-height: 1.15; vertical-align: middle; background-color: #ffffff; background: #ffffff; text-align: center;">
                                        {{ !empty($anexo2Data['tareas']['tarea_3']) ? $anexo2Data['tareas']['tarea_3'] : '---' }}
                                    </th>
                                    <th id="anexo2_header_risk_tarea_1"
                                        style="width: 8%; padding: 3px 2px; border: 1px solid #000000; font-size: 7.5pt; font-weight: normal; word-break: break-word; line-height: 1.15; vertical-align: middle; background-color: #ffffff; background: #ffffff; text-align: center;">
                                        {{ !empty($anexo2Data['tareas']['tarea_1']) ? $anexo2Data['tareas']['tarea_1'] : '---' }}
                                    </th>
                                    <th id="anexo2_header_risk_tarea_2"
                                        style="width: 8%; padding: 3px 2px; border: 1px solid #000000; font-size: 7.5pt; font-weight: normal; word-break: break-word; line-height: 1.15; vertical-align: middle; background-color: #ffffff; background: #ffffff; text-align: center;">
                                        {{ !empty($anexo2Data['tareas']['tarea_2']) ? $anexo2Data['tareas']['tarea_2'] : '---' }}
                                    </th>
                                    <th id="anexo2_header_risk_tarea_3"
                                        style="width: 8%; padding: 3px 2px; border: 1px solid #000000; font-size: 7.5pt; font-weight: normal; word-break: break-word; line-height: 1.15; vertical-align: middle; background-color: #ffffff; background: #ffffff; text-align: center;">
                                        {{ !empty($anexo2Data['tareas']['tarea_3']) ? $anexo2Data['tareas']['tarea_3'] : '---' }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $factorsList = [
                                        'A' => 'Levantamiento y descenso',
                                        'B' => 'Empuje / arrastre',
                                        'C' => 'Transporte',
                                        'D' => 'Bipedestación',
                                        'E' => 'Movimientos repetitivos',
                                        'F' => 'Postura forzada',
                                        'G' => 'Vibraciones',
                                        'H' => 'Confort térmico',
                                        'I' => 'Estrés de contacto',
                                    ];
                                @endphp

                                @foreach($factorsList as $code => $name)
                                    @php
                                        $fData = $anexo2Data['factors'][$code] ?? [];
                                        $t1 = !empty($fData['t1']);
                                        $t2 = !empty($fData['t2']);
                                        $t3 = !empty($fData['t3']);
                                        $horas = $fData['horas'] ?? '';
                                        $r1 = $fData['r1'] ?? '';
                                        $r2 = $fData['r2'] ?? '';
                                        $r3 = $fData['r3'] ?? '';
                                    @endphp
                                    <tr data-factor-code="{{ $code }}">
                                        <td
                                            style="text-align: center; font-weight: bold; background: #ffffff; border: 1px solid #000000;">
                                            {{ $code }}
                                        </td>
                                        <td style="font-weight: normal; padding: 2.5px 5px; border: 1px solid #000000;">
                                            {{ $name }}
                                        </td>

                                        <!-- Checkboxes Tareas 1, 2, 3 -->
                                        <td style="text-align: center; padding: 2px; border: 1px solid #000000;">
                                            <input type="checkbox" class="anexo2-check" data-factor="{{ $code }}" data-sub="t1"
                                                {{ $t1 ? 'checked' : '' }}>
                                        </td>
                                        <td style="text-align: center; padding: 2px; border: 1px solid #000000;">
                                            <input type="checkbox" class="anexo2-check" data-factor="{{ $code }}" data-sub="t2"
                                                {{ $t2 ? 'checked' : '' }}>
                                        </td>
                                        <td style="text-align: center; padding: 2px; border: 1px solid #000000;">
                                            <input type="checkbox" class="anexo2-check" data-factor="{{ $code }}" data-sub="t3"
                                                {{ $t3 ? 'checked' : '' }}>
                                        </td>

                                        <!-- Horas de Exposición -->
                                        <td style="text-align: center; padding: 2px; border: 1px solid #000000;">
                                            <input type="number" class="anexo2-input" data-factor="{{ $code }}" data-sub="horas"
                                                value="{{ $horas }}" min="0" max="24" style="text-align: center;"
                                                placeholder="">
                                        </td>

                                        <!-- Niveles de Riesgo T1, T2, T3 con color dinámico -->
                                        <td style="text-align: center; padding: 2px; border: 1px solid #000000;"
                                            class="anexo2-risk-cell" data-level="{{ $r1 }}">
                                            <select class="anexo2-risk-select" data-factor="{{ $code }}" data-sub="r1">
                                                <option value=""> </option>
                                                <option value="1" {{ $r1 == '1' ? 'selected' : '' }}>1</option>
                                                <option value="2" {{ $r1 == '2' ? 'selected' : '' }}>2</option>
                                                <option value="3" {{ $r1 == '3' ? 'selected' : '' }}>3</option>
                                            </select>
                                        </td>
                                        <td style="text-align: center; padding: 2px; border: 1px solid #000000;"
                                            class="anexo2-risk-cell" data-level="{{ $r2 }}">
                                            <select class="anexo2-risk-select" data-factor="{{ $code }}" data-sub="r2">
                                                <option value=""> </option>
                                                <option value="1" {{ $r2 == '1' ? 'selected' : '' }}>1</option>
                                                <option value="2" {{ $r2 == '2' ? 'selected' : '' }}>2</option>
                                                <option value="3" {{ $r2 == '3' ? 'selected' : '' }}>3</option>
                                            </select>
                                        </td>
                                        <td style="text-align: center; padding: 2px; border: 1px solid #000000;"
                                            class="anexo2-risk-cell" data-level="{{ $r3 }}">
                                            <select class="anexo2-risk-select" data-factor="{{ $code }}" data-sub="r3">
                                                <option value=""> </option>
                                                <option value="1" {{ $r3 == '1' ? 'selected' : '' }}>1</option>
                                                <option value="2" {{ $r3 == '2' ? 'selected' : '' }}>2</option>
                                                <option value="3" {{ $r3 == '3' ? 'selected' : '' }}>3</option>
                                            </select>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <!-- Nota al pie de la matriz -->
                        <div
                            style="font-size: 8pt; color: #000000; margin-bottom: 10px; font-style: italic; font-family: Arial, sans-serif;">
                            <strong>Nota:</strong> Si alguno de los factores de riesgo se encuentra presente, continuar con
                            la Evaluación Inicial de Factores de Riesgos Disergonómicos que se identificaron, completando el
                            Registro Nº 2, según corresponda.
                        </div>

                        <!-- Sección Inferior: Referencia de Niveles de Riesgo (100% de Ancho) -->
                        <div style="margin-bottom: 12px;">
                            <div
                                style="font-size: 8.5pt; font-weight: bold; color: #000000; margin-bottom: 3px; font-family: Arial, sans-serif;">
                                Referencia de los niveles de riesgo:
                            </div>
                            <table class="anexo2-table"
                                style="width: 100%; font-family: Arial, sans-serif; font-size: 8pt;">
                                <thead>
                                    <tr style="background: #ffffff; text-align: center; font-weight: bold;">
                                        <th style="width: 12%; border: 1px solid #000000; padding: 2.5px; font-size: 8pt;">
                                            Nivel</th>
                                        <th style="width: 18%; border: 1px solid #000000; padding: 2.5px; font-size: 8pt;">
                                            Color</th>
                                        <th style="width: 70%; border: 1px solid #000000; padding: 2.5px; font-size: 8pt;">
                                            Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td
                                            style="text-align: center; font-weight: bold; border: 1px solid #000000; font-size: 8pt; padding: 2.5px;">
                                            3</td>
                                        <td
                                            style="background: #ff0000; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000000; font-size: 8pt; padding: 2.5px;">
                                            Rojo</td>
                                        <td
                                            style="font-size: 8pt; line-height: 1.2; padding: 2.5px 5px; border: 1px solid #000000;">
                                            Nivel no tolerable, se deberán implementar medidas correctivas y/o preventivas
                                            en forma inmediata, con el objeto de disminuir el nivel de riesgo.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="text-align: center; font-weight: bold; border: 1px solid #000000; font-size: 8pt; padding: 2.5px;">
                                            2</td>
                                        <td
                                            style="background: #ffff00; color: #000000; font-weight: bold; text-align: center; border: 1px solid #000000; font-size: 8pt; padding: 2.5px;">
                                            Amarillo</td>
                                        <td
                                            style="font-size: 8pt; line-height: 1.2; padding: 2.5px 5px; border: 1px solid #000000;">
                                            Nivel moderado, se deberán implementar medidas correctivas y/o preventivas para
                                            proteger la salud del trabajador.
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="text-align: center; font-weight: bold; border: 1px solid #000000; font-size: 8pt; padding: 2.5px;">
                                            1</td>
                                        <td
                                            style="background: #00b050; color: #000000; font-weight: bold; text-align: center; border: 1px solid #000000; font-size: 8pt; padding: 2.5px;">
                                            Verde</td>
                                        <td
                                            style="font-size: 8pt; line-height: 1.2; padding: 2.5px 5px; border: 1px solid #000000;">
                                            Nivel tolerable, no se considera necesaria la implementación de medidas
                                            correctivas y/o preventivas para proteger la salud del trabajador.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tabla 4: Profesional con Registro SySO (Centrada con 60% de ancho) -->
                        <div style="display: flex; justify-content: center; margin-top: 22px;">
                            <table class="anexo2-table"
                                style="width: 60%; margin: 0 auto; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt;">
                                <thead>
                                    <tr style="background: #deebf7; color: #000000; text-align: center;">
                                        <th colspan="2"
                                            style="font-weight: bold; font-size: 9pt; padding: 3px 6px; border: 1px solid #000000; text-transform: uppercase;">
                                            PROFESIONAL CON REGISTRO SySO vigente
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td
                                            style="width: 32%; font-weight: bold; background: #ffffff; border: 1px solid #000000; padding: 2.5px 6px; text-align: left;">
                                            Nombre:</td>
                                        <td
                                            style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;">
                                            <input type="text" id="step1_syso_nombre" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_nombre'] ?? '' }}" 
                                                placeholder="Nombre del profesional..."
                                                oninput="autoSaveSysoField('profesional_nombre', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="width: 32%; font-weight: bold; background: #ffffff; border: 1px solid #000000; padding: 2.5px 6px; text-align: left;">
                                            N° Registro:</td>
                                        <td
                                            style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;">
                                            <input type="text" id="step1_syso_reg" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_registro'] ?? '' }}" 
                                                placeholder="N° de Registro..."
                                                oninput="autoSaveSysoField('profesional_registro', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td
                                            style="width: 32%; font-weight: bold; background: #ffffff; border: 1px solid #000000; padding: 2.5px 6px; text-align: left;">
                                            Fecha:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;">
                                            <input type="text" id="step1_syso_fecha" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_fecha'] ?? '' }}" 
                                                placeholder="dd/mm/aaaa"
                                                oninput="autoSaveSysoField('profesional_fecha', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr style="height: 36pt;">
                                        <td
                                            style="width: 32%; font-weight: bold; background: #ffffff; border: 1px solid #000000; padding: 2.5px 6px; vertical-align: top; text-align: left;">
                                            Firma:</td>
                                        <td
                                            style="width: 68%; height: 36pt; border: 1px solid #000000; text-align: center; vertical-align: middle;">
                                            &nbsp;
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- PASO 2: MATRICES NORMATIVAS ROSA (TABLAS A, B, C, D, E)                   -->
            <!-- ========================================================================= -->
            <div class="step-pane-content" id="step_pane_2">
                @php
                    $matrixA = [
                        2 => [2=>2, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                        3 => [2=>2, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                        4 => [2=>3, 3=>3, 4=>3, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                        5 => [2=>4, 3=>4, 4=>4, 5=>4, 6=>5, 7=>6, 8=>7, 9=>8],
                        6 => [2=>5, 3=>5, 4=>5, 5=>5, 6=>6, 7=>7, 8=>8, 9=>9],
                        7 => [2=>6, 3=>6, 4=>6, 5=>7, 6=>7, 7=>8, 8=>8, 9=>9],
                        8 => [2=>7, 3=>7, 4=>7, 5=>7, 6=>8, 7=>8, 8=>9, 9=>9],
                    ];
                    $colsA = [2, 3, 4, 5, 6, 7, 8, 9];
                    $rowsA = [2, 3, 4, 5, 6, 7, 8];

                    $matrixB = [
                        0 => [0=>1, 1=>1, 2=>1, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>6],
                        1 => [0=>1, 1=>1, 2=>2, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6, 8=>6],
                        2 => [0=>1, 1=>2, 2=>2, 3=>3, 4=>3, 5=>4, 6=>6, 7=>7, 8=>7],
                        3 => [0=>2, 1=>2, 2=>3, 3=>3, 4=>4, 5=>5, 6=>6, 7=>8, 8=>8],
                        4 => [0=>3, 1=>3, 2=>4, 3=>4, 4=>5, 5=>6, 6=>7, 7=>8, 8=>8],
                        5 => [0=>4, 1=>4, 2=>5, 3=>5, 4=>6, 5=>7, 6=>8, 7=>9, 8=>9],
                        6 => [0=>5, 1=>5, 2=>6, 3=>7, 4=>8, 5=>8, 6=>9, 7=>9, 8=>9],
                    ];
                    $colsB = [0, 1, 2, 3, 4, 5, 6, 7, 8];
                    $rowsB = [0, 1, 2, 3, 4, 5, 6];

                    $matrixC = [
                        0 => [0=>1, 1=>1, 2=>1, 3=>2, 4=>3, 5=>4, 6=>5, 7=>6],
                        1 => [0=>1, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7],
                        2 => [0=>1, 1=>2, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6, 7=>7],
                        3 => [0=>2, 1=>3, 2=>3, 3=>3, 4=>5, 5=>6, 6=>7, 7=>8],
                        4 => [0=>3, 1=>4, 2=>4, 3=>5, 4=>5, 5=>6, 6=>7, 7=>8],
                        5 => [0=>4, 1=>5, 2=>5, 3=>6, 4=>6, 5=>7, 6=>8, 7=>9],
                        6 => [0=>5, 1=>6, 2=>6, 3=>7, 4=>7, 5=>8, 6=>8, 7=>9],
                        7 => [0=>6, 1=>7, 2=>7, 3=>8, 4=>8, 5=>9, 6=>9, 7=>9],
                    ];
                    $colsC = [0, 1, 2, 3, 4, 5, 6, 7];
                    $rowsC = [0, 1, 2, 3, 4, 5, 6, 7];

                    $matrixD = [];
                    for ($r = 1; $r <= 9; $r++) {
                        $matrixD[$r] = [];
                        for ($c = 1; $c <= 9; $c++) {
                            $matrixD[$r][$c] = max($r, $c);
                        }
                    }
                    $colsD = range(1, 9);
                    $rowsD = range(1, 9);

                    $matrixE = [];
                    for ($r = 1; $r <= 10; $r++) {
                        $matrixE[$r] = [];
                        for ($c = 1; $c <= 10; $c++) {
                            $matrixE[$r][$c] = max($r, $c);
                        }
                    }
                    $colsE = range(1, 10);
                    $rowsE = range(1, 10);

                    $finalScore = $rosaScores['score_final'] ?? 1;
                    if ($finalScore <= 1) {
                        $curRiskLevel = 'Inapreciable';
                        $curActionLevel = 'Nivel 0: No es necesaria acción';
                        $curBadgeClass = 'risk-inapreciable';
                    } elseif ($finalScore <= 4) {
                        $curRiskLevel = 'Bajo';
                        $curActionLevel = 'Nivel 1: Riesgo bajo, puede mejorarse';
                        $curBadgeClass = 'risk-bajo';
                    } elseif ($finalScore == 5) {
                        $curRiskLevel = 'Medio';
                        $curActionLevel = 'Nivel 2: Acción necesaria, requiere rediseño';
                        $curBadgeClass = 'risk-medio';
                    } elseif ($finalScore <= 8) {
                        $curRiskLevel = 'Alto';
                        $curActionLevel = 'Nivel 3: Acción necesaria pronto';
                        $curBadgeClass = 'risk-alto';
                    } else {
                        $curRiskLevel = 'Muy Alto';
                        $curActionLevel = 'Nivel 4: Acción urgente requerida';
                        $curBadgeClass = 'risk-muy-alto';
                    }
                @endphp

                <div class="rosa-dashboard-container">
                    
                    <!-- Encabezado y Selector de Puesto (Estilo REBA) -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <span class="step-pane-badge">Paso 2 de 5 — Matrices Normativas ISO 9241 / NTP 601</span>
                            <h2 style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a; margin: 4px 0 0 0;">
                                Tablas de Referencia Normativa del Método ROSA
                            </h2>
                        </div>

                        <!-- Selector de puesto para resaltar valores en matrices -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label for="step2MeasSelect" style="font-size: 12.5px; font-weight: 700; color: #475569;">Puesto:</label>
                            <select id="step2MeasSelect" class="anexo2-select" onchange="changeMeasurement(this.value)" style="padding: 5px 12px; font-size: 12.5px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                                @forelse($allMeasurements as $mOption)
                                    <option value="{{ $mOption->id }}" {{ $selectedMeasurement && $selectedMeasurement->id === $mOption->id ? 'selected' : '' }}>
                                        Punto {{ $mOption->point_number }}: {{ $mOption->puesto_trabajo ?: 'Puesto '.$mOption->point_number }} (Score ROSA: {{ $mOption->calculated_score_final ?? $mOption->score_final }})
                                    </option>
                                @empty
                                    <option value="">Sin mediciones registradas</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <!-- Resumen del Puesto Evaluado (Top KPI Panel) -->
                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 18px 24px; margin-bottom: 28px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 14px; font-weight: 800; color: #0f172a;">Puesto Evaluado:</span>
                                <span style="font-size: 14px; font-weight: 700; color: #0284c7;">{{ $selectedMeasurement ? $selectedMeasurement->puesto_trabajo : 'Evaluación General' }}</span>
                                <span style="font-size: 12px; color: #64748b;">(Área: {{ $selectedMeasurement ? $selectedMeasurement->area_sector : 'Administración' }})</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 12px; font-weight: 700; color: #475569;">Nivel de Riesgo:</span>
                                <span class="rosa-section-badge {{ $curBadgeClass }}">{{ $curRiskLevel }} ({{ $curActionLevel }})</span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; text-align: center;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Puntuación Final</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 24px; font-weight: 900; color: {{ $finalScore >= 5 ? '#dc2626' : '#0284c7' }};">{{ $finalScore }}/10</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Silla Base (Tabla A)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a;">{{ $rosaScores['score_a_base'] }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Silla + Tiempo (Score A)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0284c7;">{{ $rosaScores['score_a_tiempo'] }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Teléfono y Pantalla (B)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #6d28d9;">{{ $rosaScores['score_b'] }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Ratón y Teclado (C)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #047857;">{{ $rosaScores['score_c'] }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Periféricos (Score D)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #c2410c;">{{ $rosaScores['score_d'] }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid de Matrices Normativas ROSA -->
                    <div class="rosa-matrices-grid">

                        <!-- MATRIZ 1: TABLA A (SILLA) -->
                        <div class="rosa-matrix-card">
                            <div class="rosa-matrix-header">
                                <div class="rosa-matrix-title-wrap">
                                    <span class="rosa-matrix-badge badge-a">A</span>
                                    <div>
                                        <h4 class="rosa-matrix-title">Tabla A: Puntuación de la Silla</h4>
                                        <span class="rosa-matrix-subtitle">Asiento (A-1 + A-2) vs Respaldo y Reposabrazos (A-3 + A-4)</span>
                                    </div>
                                </div>
                                <span class="rosa-posture-badge">
                                    Puntaje Base: <strong>{{ $rosaScores['score_a_base'] }}</strong>
                                </span>
                            </div>

                            <!-- Scorecard de Resumen Tabla A -->
                            <div class="rosa-scorecard-widget" style="width: 100%;">
                                <div class="rosa-scorecard-header">
                                    <span>Puntuaciones del Puesto</span>
                                    <span style="font-size: 10px; opacity: 0.7;">Silla (Grupo A)</span>
                                </div>
                                <div class="rosa-scorecard-body">
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Asiento (A-1 + A-2)</span>
                                        <span class="item-val">{{ $rosaScores['asiento'] }}</span>
                                    </div>
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Soporte (A-3 + A-4)</span>
                                        <span class="item-val">{{ $rosaScores['soporte'] }}</span>
                                    </div>
                                </div>
                                <div class="rosa-scorecard-footer">
                                    <span class="footer-label">Puntuación Tabla A (Base)</span>
                                    <span class="footer-val">{{ $rosaScores['score_a_base'] }}</span>
                                </div>
                            </div>

                            <div class="rosa-table-scroll-wrap">
                                <table class="rosa-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="rosa-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="{{ count($colsA) }}" class="rosa-th-primary">Reposabrazos + Respaldo (A-3 + A-4)</th>
                                        </tr>
                                        <tr>
                                            @foreach($colsA as $c)
                                                <th class="rosa-th-sub">{{ $c }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rowsA as $rIdx => $r)
                                            <tr>
                                                @if($rIdx === 0)
                                                    <th rowspan="{{ count($rowsA) }}" class="rosa-th-rowhead" style="vertical-align: middle; width: 110px; line-height: 1.2;">
                                                        Asiento<br>(A-1 + A-2)
                                                    </th>
                                                @endif
                                                <th class="rosa-th-secondary">{{ $r }}</th>
                                                @foreach($colsA as $c)
                                                    @php
                                                        $val = $matrixA[$r][$c] ?? '';
                                                        $isSelected = ($r == $rosaScores['asiento'] && $c == $rosaScores['soporte']);
                                                    @endphp
                                                    <td class="{{ $isSelected ? 'rosa-highlight-cell' : '' }}">{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="matrix-footnote">Tabla A. Puntuación base de la silla de trabajo.</div>
                        </div>

                        <!-- MATRIZ 2: TABLA B (TELÉFONO Y PANTALLA) -->
                        <div class="rosa-matrix-card">
                            <div class="rosa-matrix-header">
                                <div class="rosa-matrix-title-wrap">
                                    <span class="rosa-matrix-badge badge-b">B</span>
                                    <div>
                                        <h4 class="rosa-matrix-title">Tabla B: Teléfono y Pantalla</h4>
                                        <span class="rosa-matrix-subtitle">Intersección Teléfono (B-1) vs Pantalla (B-2)</span>
                                    </div>
                                </div>
                                <span class="rosa-posture-badge">
                                    Puntaje B: <strong>{{ $rosaScores['score_b'] }}</strong>
                                </span>
                            </div>

                            <!-- Scorecard de Resumen Tabla B -->
                            <div class="rosa-scorecard-widget" style="width: 100%;">
                                <div class="rosa-scorecard-header">
                                    <span>Puntuaciones del Puesto</span>
                                    <span style="font-size: 10px; opacity: 0.7;">Teléfono y Pantalla</span>
                                </div>
                                <div class="rosa-scorecard-body">
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Teléfono (B-1)</span>
                                        <span class="item-val">{{ $rosaScores['telefono'] }}</span>
                                    </div>
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Pantalla (B-2)</span>
                                        <span class="item-val">{{ $rosaScores['pantalla'] }}</span>
                                    </div>
                                </div>
                                <div class="rosa-scorecard-footer footer-purple">
                                    <span class="footer-label">Puntuación Tabla B</span>
                                    <span class="footer-val">{{ $rosaScores['score_b'] }}</span>
                                </div>
                            </div>

                            <div class="rosa-table-scroll-wrap">
                                <table class="rosa-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="rosa-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="{{ count($colsB) }}" class="rosa-th-primary">Pantalla (B-2)</th>
                                        </tr>
                                        <tr>
                                            @foreach($colsB as $c)
                                                <th class="rosa-th-sub">{{ $c }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rowsB as $rIdx => $r)
                                            <tr>
                                                @if($rIdx === 0)
                                                    <th rowspan="{{ count($rowsB) }}" class="rosa-th-rowhead" style="vertical-align: middle; width: 90px; line-height: 1.2;">
                                                        Teléfono<br>(B-1)
                                                    </th>
                                                @endif
                                                <th class="rosa-th-secondary">{{ $r }}</th>
                                                @foreach($colsB as $c)
                                                    @php
                                                        $val = $matrixB[$r][$c] ?? '';
                                                        $isSelected = ($r == $rosaScores['telefono'] && $c == $rosaScores['pantalla']);
                                                    @endphp
                                                    <td class="{{ $isSelected ? 'rosa-highlight-cell' : '' }}">{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="matrix-footnote">Tabla B. Puntuación combinada de teléfono y pantalla.</div>
                        </div>

                        <!-- MATRIZ 3: TABLA C (RATÓN Y TECLADO) -->
                        <div class="rosa-matrix-card">
                            <div class="rosa-matrix-header">
                                <div class="rosa-matrix-title-wrap">
                                    <span class="rosa-matrix-badge badge-c">C</span>
                                    <div>
                                        <h4 class="rosa-matrix-title">Tabla C: Ratón y Teclado</h4>
                                        <span class="rosa-matrix-subtitle">Intersección Ratón (C-1) vs Teclado (C-2)</span>
                                    </div>
                                </div>
                                <span class="rosa-posture-badge">
                                    Puntaje C: <strong>{{ $rosaScores['score_c'] }}</strong>
                                </span>
                            </div>

                            <!-- Scorecard de Resumen Tabla C -->
                            <div class="rosa-scorecard-widget" style="width: 100%;">
                                <div class="rosa-scorecard-header">
                                    <span>Puntuaciones del Puesto</span>
                                    <span style="font-size: 10px; opacity: 0.7;">Periféricos</span>
                                </div>
                                <div class="rosa-scorecard-body">
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Ratón (C-1)</span>
                                        <span class="item-val">{{ $rosaScores['raton'] }}</span>
                                    </div>
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Teclado (C-2)</span>
                                        <span class="item-val">{{ $rosaScores['teclado'] }}</span>
                                    </div>
                                </div>
                                <div class="rosa-scorecard-footer footer-emerald">
                                    <span class="footer-label">Puntuación Tabla C</span>
                                    <span class="footer-val">{{ $rosaScores['score_c'] }}</span>
                                </div>
                            </div>

                            <div class="rosa-table-scroll-wrap">
                                <table class="rosa-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="rosa-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="{{ count($colsC) }}" class="rosa-th-primary">Teclado (C-2)</th>
                                        </tr>
                                        <tr>
                                            @foreach($colsC as $c)
                                                <th class="rosa-th-sub">{{ $c }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rowsC as $rIdx => $r)
                                            <tr>
                                                @if($rIdx === 0)
                                                    <th rowspan="{{ count($rowsC) }}" class="rosa-th-rowhead" style="vertical-align: middle; width: 90px; line-height: 1.2;">
                                                        Ratón<br>(C-1)
                                                    </th>
                                                @endif
                                                <th class="rosa-th-secondary">{{ $r }}</th>
                                                @foreach($colsC as $c)
                                                    @php
                                                        $val = $matrixC[$r][$c] ?? '';
                                                        $isSelected = ($r == $rosaScores['raton'] && $c == $rosaScores['teclado']);
                                                    @endphp
                                                    <td class="{{ $isSelected ? 'rosa-highlight-cell' : '' }}">{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="matrix-footnote">Tabla C. Puntuación combinada de ratón y teclado.</div>
                        </div>

                        <!-- MATRIZ 4: TABLA D (PANTALLA Y PERIFÉRICOS) -->
                        <div class="rosa-matrix-card">
                            <div class="rosa-matrix-header">
                                <div class="rosa-matrix-title-wrap">
                                    <span class="rosa-matrix-badge badge-d">D</span>
                                    <div>
                                        <h4 class="rosa-matrix-title">Tabla D: Pantalla y Periféricos</h4>
                                        <span class="rosa-matrix-subtitle">Intersección Tabla B vs Tabla C</span>
                                    </div>
                                </div>
                                <span class="rosa-posture-badge">
                                    Puntaje D: <strong>{{ $rosaScores['score_d'] }}</strong>
                                </span>
                            </div>

                            <!-- Scorecard de Resumen Tabla D -->
                            <div class="rosa-scorecard-widget" style="width: 100%;">
                                <div class="rosa-scorecard-header">
                                    <span>Puntuaciones del Puesto</span>
                                    <span style="font-size: 10px; opacity: 0.7;">Pantalla y Periféricos</span>
                                </div>
                                <div class="rosa-scorecard-body">
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Tabla B (Tel/Pantalla)</span>
                                        <span class="item-val">{{ $rosaScores['score_b'] }}</span>
                                    </div>
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Tabla C (Ratón/Teclado)</span>
                                        <span class="item-val">{{ $rosaScores['score_c'] }}</span>
                                    </div>
                                </div>
                                <div class="rosa-scorecard-footer footer-orange">
                                    <span class="footer-label">Puntuación Tabla D</span>
                                    <span class="footer-val">{{ $rosaScores['score_d'] }}</span>
                                </div>
                            </div>

                            <div class="rosa-table-scroll-wrap">
                                <table class="rosa-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="rosa-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="{{ count($colsD) }}" class="rosa-th-primary">Tabla C (Ratón y Teclado)</th>
                                        </tr>
                                        <tr>
                                            @foreach($colsD as $c)
                                                <th class="rosa-th-sub">{{ $c }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rowsD as $rIdx => $r)
                                            <tr>
                                                @if($rIdx === 0)
                                                    <th rowspan="{{ count($rowsD) }}" class="rosa-th-rowhead" style="vertical-align: middle; width: 110px; line-height: 1.2;">
                                                        Tabla B<br>(Teléfono y<br>Pantalla)
                                                    </th>
                                                @endif
                                                <th class="rosa-th-secondary">{{ $r }}</th>
                                                @foreach($colsD as $c)
                                                    @php
                                                        $val = $matrixD[$r][$c] ?? '';
                                                        $isSelected = ($r == $rosaScores['score_b'] && $c == $rosaScores['score_c']);
                                                    @endphp
                                                    <td class="{{ $isSelected ? 'rosa-highlight-cell' : '' }}">{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="matrix-footnote">Tabla D. Puntuación consolidada de pantalla y periféricos.</div>
                        </div>

                        <!-- MATRIZ 5: TABLA E (PUNTUACIÓN FINAL ROSA - TARJETA ANCHA) -->
                        <div class="rosa-matrix-card rosa-matrix-card-wide">
                            <div class="rosa-matrix-header">
                                <div class="rosa-matrix-title-wrap">
                                    <span class="rosa-matrix-badge badge-e">E</span>
                                    <div>
                                        <h4 class="rosa-matrix-title">Tabla E: Puntuación Final del Método ROSA</h4>
                                        <span class="rosa-matrix-subtitle">Intersección Silla (Tabla A con tiempo) vs Pantalla y Periféricos (Tabla D)</span>
                                    </div>
                                </div>
                                <span class="rosa-posture-badge" style="font-size: 13px; font-weight: 800; color: {{ $finalScore >= 5 ? '#dc2626' : '#0284c7' }};">
                                    Puntuación Final: <strong>{{ $rosaScores['score_final'] }} / 10</strong>
                                </span>
                            </div>

                            <!-- Scorecard de Resumen Tabla E -->
                            <div class="rosa-scorecard-widget" style="width: 100%;">
                                <div class="rosa-scorecard-header">
                                    <span>Puntuaciones del Puesto</span>
                                    <span style="font-size: 10px; opacity: 0.7;">Evaluación Global ROSA</span>
                                </div>
                                <div class="rosa-scorecard-body">
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Tabla A (Silla con Tiempo)</span>
                                        <span class="item-val">{{ $rosaScores['score_a_tiempo'] }}</span>
                                    </div>
                                    <div class="rosa-scorecard-item">
                                        <span class="item-label">Tabla D (Periféricos)</span>
                                        <span class="item-val">{{ $rosaScores['score_d'] }}</span>
                                    </div>
                                </div>
                                <div class="rosa-scorecard-footer footer-red">
                                    <span class="footer-label">Puntuación Final ROSA (Score E)</span>
                                    <span class="footer-val">{{ $rosaScores['score_final'] }}</span>
                                </div>
                            </div>

                            <div class="rosa-table-scroll-wrap">
                                <table class="rosa-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="rosa-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="{{ count($colsE) }}" class="rosa-th-primary">Tabla D (Pantalla y Periféricos)</th>
                                        </tr>
                                        <tr>
                                            @foreach($colsE as $c)
                                                <th class="rosa-th-sub">{{ $c }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rowsE as $rIdx => $r)
                                            <tr>
                                                @if($rIdx === 0)
                                                    <th rowspan="{{ count($rowsE) }}" class="rosa-th-rowhead" style="vertical-align: middle; width: 130px; line-height: 1.2;">
                                                        Tabla A<br>(Silla con<br>Factor Tiempo)
                                                    </th>
                                                @endif
                                                <th class="rosa-th-secondary">{{ $r }}</th>
                                                @foreach($colsE as $c)
                                                    @php
                                                        $val = $matrixE[$r][$c] ?? '';
                                                        $isSelected = ($r == $rosaScores['score_a_tiempo'] && $c == $rosaScores['score_d']);
                                                        $isShaded = ($r >= 5 || $c >= 5);
                                                    @endphp
                                                    <td class="{{ $isSelected ? 'rosa-highlight-cell' : ($isShaded ? 'cell-shaded' : '') }}">{{ $val }}</td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="matrix-footnote">
                                Tabla E. Puntuación final del método ROSA. Las casillas sombreadas (≥ 5) corresponden al nivel de riesgo que requiere actuación ergonómica inmediata.
                            </div>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- PASO 3: REGISTRO DE EVALUACIÓN DETALLADA (HOJA CARTA PARA WORD)           -->
            <!-- ========================================================================= -->
            <div class="step-pane-content" id="step_pane_3">

                <div class="anexo2-sheet-wrapper">

                    <!-- Barra de Herramientas de la Hoja -->
                    <div class="anexo2-sheet-toolbar">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="step-pane-badge" style="margin-bottom: 0;">Formato Oficial — Evaluación Detallada</span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" class="btn-clear-sheet-ai" id="btnClearStep3Ai" onclick="clearRosaAiContent()"
                                title="Limpiar el contenido de los Puntos 4, 5 y 6"
                                style="display: inline-flex; align-items: center; justify-content: center; gap: 5px; padding: 7px 12px; border-radius: 8px; font-size: 12.5px; font-weight: 600; background: #ffffff; color: #64748b; border: 1px solid #cbd5e1; cursor: pointer; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s ease;"
                                onmouseover="this.style.background='#fee2e2'; this.style.color='#ef4444'; this.style.borderColor='#fca5a5';"
                                onmouseout="this.style.background='#ffffff'; this.style.color='#64748b'; this.style.borderColor='#cbd5e1';">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                    <line x1="10" y1="11" x2="10" y2="17"/>
                                    <line x1="14" y1="11" x2="14" y2="17"/>
                                </svg>
                                <span>Limpiar</span>
                            </button>

                            <button type="button" class="btn-ai-generate-sheet" id="btnGenerateStep3Ai" onclick="generateRosaContentWithAi()"
                                title="Generar automáticamente con Gemini IA el Análisis Técnico, Observaciones y Recomendaciones (Puntos 4, 5 y 6)"
                                style="display: inline-flex; align-items: center; gap: 7px; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border: 1px solid #0284c7; cursor: pointer; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25); transition: all 0.2s ease;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3Z"/>
                                    <path d="M18 15h6"/>
                                    <path d="M21 12v6"/>
                                </svg>
                                <span id="btnGenerateStep3AiText">Generar con IA</span>
                            </button>

                            @php
                                $hasRosaAiContent = !empty(trim($promptsData['analisis_tecnico'] ?? '')) || 
                                                     !empty(trim($promptsData['observaciones'] ?? '')) || 
                                                     !empty(trim($promptsData['recomendaciones'] ?? ''));
                            @endphp
                            <button type="button" class="btn-download-sheet" id="btnDownloadStep3Word" onclick="downloadEvaluacionDetalladaDoc()"
                                title="Descargar registro de evaluación detallada compatible con Microsoft Word (.doc) en formato vertical y Arial 9pt"
                                @if(!$hasRosaAiContent) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span>Descargar Word (.doc)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Documento Evaluación Detallada (1 Sola Hoja Carta - Arial 9pt) -->
                    <div class="anexo2-sheet-card" id="evaluacionDetalladaSheet"
                        style="font-family: Arial, sans-serif; font-size: 9pt; color: #000000;">

                        <!-- Caja de Encabezado Principal -->
                        <div style="border: 1.5px solid #000000; padding: 6px 10px; text-align: center; margin-bottom: 12px; background: #ffffff;">
                            <div style="font-family: Arial, sans-serif; font-size: 9.5pt; font-weight: bold; text-transform: uppercase; line-height: 1.25; color: #000000;">
                                REGISTRO DE EVALUACIÓN DETALLADA DE RIESGOS DISERGONÓMICOS<br>
                                MEDIANTE EL MÉTODO ROSA EN PUESTOS ADMINISTRATIVOS
                            </div>
                        </div>

                        <!-- 1. Identificación del puesto evaluado -->
                        <div style="margin-bottom: 10px;">
                            <div style="font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; margin-bottom: 4px; color: #000000;">
                                1. Identificación del puesto evaluado
                            </div>
                            <table class="anexo2-table" style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt;">
                                <thead>
                                    <tr style="background: #deebf7;">
                                        <th style="width: 48%; border: 1px solid #000000; padding: 2.5px 6px; text-align: left; font-weight: bold; color: #000000; font-size: 9pt;">Ítem</th>
                                        <th style="width: 52%; border: 1px solid #000000; padding: 2.5px 6px; text-align: left; font-weight: bold; color: #000000; font-size: 9pt;">Descripción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Área / Sector:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_area_sector">{{ $anexo2Data['area_sector'] ?? ($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Administración') : 'Administración') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Puesto de trabajo:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_puesto_trabajo">{{ $anexo2Data['puesto_trabajo'] ?? ($selectedMeasurement ? ($selectedMeasurement->puesto_trabajo ?: 'Gerente general') : 'Gerente general') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Trabajador evaluado:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_nombre_trabajador">{{ $anexo2Data['nombre_trabajador'] ?? ($selectedMeasurement ? (is_array($selectedMeasurement->nombres_trabajadores) ? implode(', ', array_filter($selectedMeasurement->nombres_trabajadores)) : ($selectedMeasurement->nombres_trabajadores ?: 'Carlos Rene Ichuta Ichuta')) : 'Carlos Rene Ichuta Ichuta') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Fecha de evaluación:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_fecha">{{ $selectedMeasurement ? ($selectedMeasurement->date_formatted ?: ($selectedMeasurement->date ?: date('d/m/Y'))) : ($anexo2Data['profesional_fecha'] ?: date('d/m/Y')) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Actividad principal:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_actividad">{{ $selectedMeasurement ? ($selectedMeasurement->factor_riesgo ?: ($anexo2Data['actividad_principal'] ?? 'Gestionar y gerentar el trabajo del personal')) : 'Gestionar y gerentar el trabajo del personal' }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Tiempo de exposición:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_tiempo_exposicion">{{ (int)($selectedMeasurement ? ($selectedMeasurement->tiempo_exposicion_horas ?: 8) : 8) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. Resultados del Método ROSA por puesto -->
                        <div style="margin-bottom: 10px;">
                            <div style="font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; margin-bottom: 4px; color: #000000;">
                                2. Resultados del Método ROSA por puesto
                            </div>
                            <table class="anexo2-table" style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt;">
                                <thead>
                                    <tr style="background: #deebf7;">
                                        <th style="width: 16%; border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Grupo evaluado</th>
                                        <th style="width: 58%; border: 1px solid #000000; padding: 2.5px 6px; text-align: left; font-weight: bold; color: #000000; font-size: 9pt;">Descripción</th>
                                        <th style="width: 26%; border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Puntaje obtenido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Tabla A</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Silla de trabajo: altura, profundidad, reposabrazos, respaldo y duración</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;">{{ $rosaScores['score_a_tiempo'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Tabla B</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Teléfono y pantalla</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;">{{ $rosaScores['score_b'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Tabla C</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Ratón y teclado</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;">{{ $rosaScores['score_c'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Tabla D</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Pantalla y periféricos</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;">{{ $rosaScores['score_d'] }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;">Tabla E</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold;">Resultado final ROSA</td>
                                        @php
                                            $finalScore = $rosaScores['score_final'];
                                            $finalBg = ($finalScore >= 5) ? '#ff0000' : (($finalScore >= 3) ? '#ffff00' : '#00b050');
                                            $finalColor = ($finalScore >= 5) ? '#ffffff' : '#000000';
                                        @endphp
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold; background-color: {{ $finalBg }}; color: {{ $finalColor }}; font-size: 9.5pt;">{{ $finalScore }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 3. Interpretación del nivel de riesgo -->
                        <div style="margin-bottom: 10px;">
                            <div style="font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; margin-bottom: 4px; color: #000000;">
                                3. Interpretación del nivel de riesgo
                            </div>
                            <table class="anexo2-table" style="width: 82%; margin: 0 auto; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt;">
                                <thead>
                                    <tr style="background: #ffffff;">
                                        <th style="width: 22%; border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Puntaje final ROSA</th>
                                        <th style="width: 22%; border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Nivel de riesgo</th>
                                        <th style="width: 56%; border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Acción requerida</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background: #fff2cc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">1 a 2</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Inapreciable</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">No requiere actuación inmediata</td>
                                    </tr>
                                    <tr style="background: #fce4d6;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">3 a 4</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Bajo</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Puede requerir mejoras básicas</td>
                                    </tr>
                                    <tr style="background: #f8cecc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">5 a 6</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Medio</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Requiere intervención ergonómica</td>
                                    </tr>
                                    <tr style="background: #f8cecc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">7 a 8</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Alto</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Requiere intervención en corto plazo</td>
                                    </tr>
                                    <tr style="background: #f8cecc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">9 a 10</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Muy alto</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Requiere intervención prioritaria</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 4. Análisis técnico del puesto -->
                        <div style="margin-bottom: 10px;">
                            <div style="font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; margin-bottom: 4px; color: #000000;">
                                4. Análisis técnico del puesto
                            </div>
                            @php
                                $scoreNum = $rosaScores['score_final'];
                                if ($scoreNum <= 2) {
                                    $riskText = 'Inapreciable';
                                    $actionText = 'No requiere actuación inmediata';
                                } elseif ($scoreNum <= 4) {
                                    $riskText = 'Bajo';
                                    $actionText = 'Puede requerir mejoras básicas';
                                } elseif ($scoreNum <= 6) {
                                    $riskText = 'Medio';
                                    $actionText = 'Requiere intervención ergonómica';
                                } elseif ($scoreNum <= 8) {
                                    $riskText = 'Alto';
                                    $actionText = 'Requiere intervención en corto plazo';
                                } else {
                                    $riskText = 'Muy alto';
                                    $actionText = 'Requiere intervención prioritaria';
                                }
                            @endphp
                            <div id="doc_val_analisis_tecnico_narrative" contenteditable="true"
                                class="doc-editable-narrative"
                                style="font-family: Arial, sans-serif; font-size: 9pt; color: #1e293b; line-height: 1.35; margin-bottom: 6px; text-align: justify;"
                                oninput="autoSaveNarrativeField('analisis_tecnico', this.innerText)"
                                data-placeholder="Escriba aquí el análisis técnico o pulse 'Generar con IA'...">{{ $promptsData['analisis_tecnico'] ?? '' }}</div>
                            <table style="width: 75%; margin: 6px auto; font-family: Arial, sans-serif; font-size: 9pt; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 38%; padding: 1.5px 0;">Puntuación final ROSA de:</td>
                                    <td style="width: 62%; padding: 1.5px 0; font-weight: bold;" id="doc_val_score_final_text">{{ $scoreNum }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 38%; padding: 1.5px 0;">Nivel de riesgo:</td>
                                    <td style="width: 62%; padding: 1.5px 0; font-weight: bold;" id="doc_val_risk_level_text">{{ $riskText }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 38%; padding: 1.5px 0;">Este resultado indica que:</td>
                                    <td style="width: 62%; padding: 1.5px 0; font-weight: bold;" id="doc_val_action_text">{{ $actionText }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- 5. Observaciones encontradas -->
                        <div style="margin-bottom: 10px;">
                            <div style="font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; margin-bottom: 4px; color: #000000;">
                                5. Observaciones encontradas
                            </div>
                            <div id="doc_val_observaciones_narrative" contenteditable="true"
                                class="doc-editable-narrative"
                                style="font-family: Arial, sans-serif; font-size: 9pt; color: #1e293b; line-height: 1.35; text-align: justify; white-space: pre-line;"
                                oninput="autoSaveNarrativeField('observaciones', this.innerText)"
                                data-placeholder="Escriba aquí las observaciones o pulse 'Generar con IA'...">{{ $promptsData['observaciones'] ?? '' }}</div>
                        </div>

                        <!-- 6. Recomendaciones por puesto -->
                        <div style="margin-bottom: 12px;">
                            <div style="font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; margin-bottom: 4px; color: #000000;">
                                6. Recomendaciones por puesto
                            </div>
                            <div id="doc_val_recomendaciones_narrative" contenteditable="true"
                                class="doc-editable-narrative"
                                style="font-family: Arial, sans-serif; font-size: 9pt; color: #1e293b; line-height: 1.35; text-align: justify; white-space: pre-line;"
                                oninput="autoSaveNarrativeField('recomendaciones', this.innerText)"
                                data-placeholder="Escriba aquí las recomendaciones o pulse 'Generar con IA'...">{{ $promptsData['recomendaciones'] ?? '' }}</div>
                        </div>

                        <!-- Tabla SySO -->
                        <div style="display: flex; justify-content: center; margin-top: 18px;">
                            <table class="anexo2-table" style="width: 60%; margin: 0 auto; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt;">
                                <thead>
                                    <tr style="background: #deebf7;">
                                        <th colspan="2" style="border: 1px solid #000000; padding: 3px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt; text-transform: uppercase;">
                                            PROFESIONAL CON REGISTRO SySO vigente
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; text-align: left; background: #ffffff;">Nombre:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;" id="doc_val_syso_nombre">
                                            <input type="text" id="step3_syso_nombre" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_nombre'] ?? '' }}" 
                                                placeholder="Nombre del profesional..."
                                                oninput="autoSaveSysoField('profesional_nombre', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; text-align: left; background: #ffffff;">N° Registro:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;" id="doc_val_syso_reg">
                                            <input type="text" id="step3_syso_reg" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_registro'] ?? '' }}" 
                                                placeholder="N° de Registro..."
                                                oninput="autoSaveSysoField('profesional_registro', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; text-align: left; background: #ffffff;">Fecha:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;" id="doc_val_syso_fecha">
                                            <input type="text" id="step3_syso_fecha" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_fecha'] ?? '' }}" 
                                                placeholder="dd/mm/aaaa"
                                                oninput="autoSaveSysoField('profesional_fecha', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr style="height: 36pt;">
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; vertical-align: top; text-align: left; background: #ffffff;">Firma:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 2.5px 6px; height: 36pt; vertical-align: middle; text-align: center;">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- PASO 4: REGISTRO Nº 3 (MEDIDAS CORRECTIVAS Y PREVENTIVAS)                 -->
            <!-- ========================================================================= -->
            <div class="step-pane-content" id="step_pane_4">
                @php
                    $step4Storage = $anexo2Data['step4_data'] ?? [];
                    $measKey4 = $selectedMeasurement ? (string)$selectedMeasurement->id : 'general';
                    $step4Data = $step4Storage[$measKey4] ?? $step4Storage['general'] ?? [];
                    $trabajadorName4 = $selectedMeasurement ? (is_array($selectedMeasurement->nombres_trabajadores) ? implode(', ', array_filter($selectedMeasurement->nombres_trabajadores)) : ($selectedMeasurement->nombres_trabajadores ?: 'Carlos Rene Ichuta Ichuta')) : 'Carlos Rene Ichuta Ichuta';
                    $area4 = $selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Administración') : ($anexo2Data['area_sector'] ?? 'Administración');
                    $puesto4 = $selectedMeasurement ? ($selectedMeasurement->puesto_trabajo ?: 'Gerente general') : ($anexo2Data['puesto_trabajo'] ?? 'Gerente general');
                    $tarea4 = $selectedMeasurement ? ($selectedMeasurement->tarea_analizada ?: 'Tarea 1') : 'Tarea 1';
                    $fecha4Default = $selectedMeasurement ? ($selectedMeasurement->date_formatted ?: ($selectedMeasurement->date ?: date('d/m/Y'))) : date('d/m/Y');

                    $maxGenIdx = 3;
                    $maxEspIdx = 3;
                    foreach (array_keys($step4Data) as $k) {
                        if (preg_match('/^gen_medida_(\d+)$/', $k, $matches)) {
                            $idx = (int)$matches[1];
                            if ($idx > $maxGenIdx) $maxGenIdx = $idx;
                        }
                        if (preg_match('/^esp_medida_(\d+)$/', $k, $matches)) {
                            $idx = (int)$matches[1];
                            if ($idx > $maxEspIdx) $maxEspIdx = $idx;
                        }
                    }
                    $totalGenRows = max(3, $maxGenIdx);
                    $totalEspRows = max(3, $maxEspIdx);
                @endphp

                <div class="anexo2-sheet-wrapper">

                    <!-- Barra de Herramientas de la Hoja -->
                    <div class="anexo2-sheet-toolbar">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="step-pane-badge" style="margin-bottom: 0;">Formato Oficial — Registro Nº 3</span>
                            <span id="step4AutoSaveBadge" class="header-auto-save-status">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Guardado</span>
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" class="btn-download-sheet" id="btnDownloadStep4Word" onclick="downloadRegistro3Doc()"
                                title="Descargar Registro Nº 3 compatible con Microsoft Word (.doc) en formato vertical y Arial 9pt">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span>Descargar Word (.doc)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Documento Registro Nº 3 (Hoja Carta - Arial 9pt) -->
                    <div class="anexo2-sheet-card" id="registro3Sheet"
                        style="font-family: Arial, sans-serif; font-size: 9pt; color: #000000;">

                        <!-- Caja de Encabezado Principal -->
                        <div style="border: 1.5px solid #000000; padding: 6px 10px; text-align: center; margin-bottom: 12px; background: #ffffff;">
                            <div style="font-family: Arial, sans-serif; font-size: 9.5pt; font-weight: bold; text-transform: uppercase; color: #000000;">
                                REGISTRO Nº 3: IDENTIFICACIÓN DE MEDIDAS CORRECTIVAS Y PREVENTIVAS
                            </div>
                        </div>

                        <!-- 1. Tabla de Identificación General -->
                        <table class="anexo2-table" style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt; margin-bottom: 12px;">
                            <tbody>
                                <tr>
                                    <td style="width: 26%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal;">Razón Social:</td>
                                    <td style="width: 38%; border: 1px solid #000000; padding: 3px 6px; font-weight: bold;" id="step4_val_razon_social">
                                        {{ $anexo2Data['razon_social'] ?? ($companyName ?? 'SURGE SRL - CASA MATRIZ SENKATA') }}
                                    </td>
                                    <td style="width: 36%; border: 1px solid #000000; padding: 3px 6px; text-align: center; font-weight: normal;" id="step4_header_trabajador">
                                        Nombre/s del trabajador/es:
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 26%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal;">Dirección de la empresa o establecimiento:</td>
                                    <td style="width: 38%; border: 1px solid #000000; padding: 3px 6px; font-weight: bold;" id="step4_val_direccion">
                                        {{ $anexo2Data['direccion'] ?? ($companyAddress ?? '25 de Julio Alejandria Nº 8345 UV Edificio') }}
                                    </td>
                                    <td rowspan="4" style="width: 36%; border: 1px solid #000000; padding: 6px 8px; text-align: center; vertical-align: middle; font-weight: bold; font-size: 9pt;" id="step4_val_trabajador">
                                        {{ $trabajadorName4 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 26%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal;">Área y Sector en estudio:</td>
                                    <td style="width: 38%; border: 1px solid #000000; padding: 3px 6px; font-weight: bold;" id="step4_val_area">
                                        {{ $area4 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 26%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal;">Puesto de Trabajo:</td>
                                    <td style="width: 38%; border: 1px solid #000000; padding: 3px 6px; font-weight: bold;" id="step4_val_puesto">
                                        {{ $puesto4 }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 26%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal;">Tarea analizada:</td>
                                    <td style="width: 38%; border: 1px solid #000000; padding: 3px 6px; font-weight: bold;" id="step4_val_tarea">
                                        {{ $tarea4 }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- 2. Tabla Principal: Medidas Correctivas y Preventivas (M.C.P.) -->
                        <table class="anexo2-table" style="width: 100%; table-layout: fixed; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt; margin-bottom: 12px;">
                            <colgroup>
                                <col style="width: 6.5%;">
                                <col style="width: 53.5%;">
                                <col style="width: 13%;">
                                <col style="width: 5.5%;">
                                <col style="width: 5.5%;">
                                <col style="width: 16%;">
                            </colgroup>
                            <thead>
                                <tr style="background: #ffffff;">
                                    <th colspan="6" style="border: 1.5px solid #000000; padding: 4px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">
                                        Medidas Correctivas y Preventivas (M.C.P.)
                                    </th>
                                </tr>
                                <tr style="background: #deebf7; text-align: center; font-weight: bold;">
                                    <th style="border: 1px solid #000000; padding: 3px 4px; text-align: center;">N°</th>
                                    <th style="border: 1px solid #000000; padding: 3px 6px; text-align: center;">Medidas Preventivas Generales</th>
                                    <th style="border: 1px solid #000000; padding: 3px 4px; text-align: center;">Fecha</th>
                                    <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center;">SI</th>
                                    <th style="border: 1px solid #000000; padding: 3px 2px; text-align: center;">NO</th>
                                    <th style="border: 1px solid #000000; padding: 3px 4px; text-align: center;">Observaciones</th>
                                </tr>
                            </thead>
                            <tbody id="step4_mcp_tbody">
                                <!-- Medidas Preventivas Generales -->
                                <!-- Medidas Preventivas Generales -->
                                @for($g = 1; $g <= $totalGenRows; $g++)
                                    <tr data-step4-gen-row="{{ $g }}">
                                        <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold; position: relative;">
                                            <div class="table-row-actions-left">
                                                <button type="button" class="btn-del-table-row" onclick="removeStep4GenRow({{ $g }})" title="Eliminar fila">&times;</button>
                                                @if($g === $totalGenRows)
                                                    <button type="button" class="btn-add-table-row" id="btnAddStep4Gen" onclick="addStep4GenRow()" title="Añadir fila">+</button>
                                                @endif
                                            </div>
                                            <span class="step4-gen-num">{{ $g }}</span>
                                        </td>
                                        <td style="border: 1px solid #000000; padding: 1px 4px; vertical-align: top;">
                                            <textarea id="step4_gen_medida_{{ $g }}" class="syso-live-textarea" rows="2" style="font-size: 8.5pt;"
                                                placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)">{{ $step4Data['gen_medida_'.$g] ?? '' }}</textarea>
                                        </td>
                                        <td style="border: 1px solid #000000; padding: 2px 4px; text-align: center;">
                                            <input type="text" id="step4_gen_fecha_{{ $g }}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;"
                                                value="{{ $step4Data['gen_fecha_'.$g] ?? '' }}" placeholder="dd/mm/aaaa" oninput="autoSaveStep4()">
                                        </td>
                                        <td style="border: 1px solid #000000; padding: 2px; text-align: center;">
                                            <input type="checkbox" id="step4_gen_si_{{ $g }}" {{ !empty($step4Data['gen_si_'.$g]) && $step4Data['gen_si_'.$g] == '1' ? 'checked' : '' }} onchange="if(this.checked){ const noEl = document.getElementById('step4_gen_no_{{ $g }}'); if(noEl) noEl.checked = false; } autoSaveStep4()" style="cursor: pointer; width: 15px; height: 15px; display: block; margin: 0 auto; accent-color: #0284c7;">
                                        </td>
                                        <td style="border: 1px solid #000000; padding: 2px; text-align: center;">
                                            <input type="checkbox" id="step4_gen_no_{{ $g }}" {{ !empty($step4Data['gen_no_'.$g]) && $step4Data['gen_no_'.$g] == '1' ? 'checked' : '' }} onchange="if(this.checked){ const siEl = document.getElementById('step4_gen_si_{{ $g }}'); if(siEl) siEl.checked = false; } autoSaveStep4()" style="cursor: pointer; width: 15px; height: 15px; display: block; margin: 0 auto; accent-color: #0284c7;">
                                        </td>
                                        <td style="border: 1px solid #000000; padding: 1px 2px; vertical-align: top;">
                                            <textarea id="step4_gen_obs_{{ $g }}" class="syso-live-textarea" rows="1" style="font-size: 8.5pt;"
                                                placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)">{{ $step4Data['gen_obs_'.$g] ?? '' }}</textarea>
                                        </td>
                                    </tr>
                                @endfor

                                <!-- Header Sección Específicas -->
                                <tr id="step4_header_esp" style="background: #deebf7; text-align: center; font-weight: bold;">
                                    <th style="border: 1px solid #000000; padding: 3px 4px; text-align: center;">N°</th>
                                    <th colspan="4" style="border: 1px solid #000000; padding: 3px 6px; text-align: center;">Medidas Correctivas y Preventivas Específicas (Administrativas y de Ingeniería)</th>
                                    <th style="border: 1px solid #000000; padding: 3px 4px; text-align: center;">Observaciones</th>
                                </tr>

                                <!-- Medidas Correctivas y Preventivas Específicas -->
                                @for($e = 1; $e <= $totalEspRows; $e++)
                                    <tr data-step4-esp-row="{{ $e }}">
                                        <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold; position: relative;">
                                            <div class="table-row-actions-left">
                                                <button type="button" class="btn-del-table-row" onclick="removeStep4EspRow({{ $e }})" title="Eliminar fila">&times;</button>
                                                @if($e === $totalEspRows)
                                                    <button type="button" class="btn-add-table-row" id="btnAddStep4Esp" onclick="addStep4EspRow()" title="Añadir fila">+</button>
                                                @endif
                                            </div>
                                            <span class="step4-esp-num">{{ $e }}</span>
                                        </td>
                                        <td colspan="4" style="border: 1px solid #000000; padding: 1px 4px; vertical-align: top;">
                                            <textarea id="step4_esp_medida_{{ $e }}" class="syso-live-textarea" rows="1" style="font-size: 8.5pt;"
                                                placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)">{{ $step4Data['esp_medida_'.$e] ?? '' }}</textarea>
                                        </td>
                                        <td style="border: 1px solid #000000; padding: 1px 4px; vertical-align: top;">
                                            <textarea id="step4_esp_obs_{{ $e }}" class="syso-live-textarea" rows="1" style="font-size: 8.5pt;"
                                                placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)">{{ $step4Data['esp_obs_'.$e] ?? '' }}</textarea>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>

                        <!-- 3. Caja de Observaciones -->
                        <div style="border: 1.5px solid #000000; padding: 6px 8px; margin-bottom: 14px; background: #ffffff;">
                            <div style="font-weight: bold; font-size: 9pt; margin-bottom: 3px;">Observaciones:</div>
                            <textarea id="step4_observaciones_box" class="syso-live-textarea" rows="2"
                                style="font-family: Arial, sans-serif; font-size: 9pt; color: #000000; background: #ffffff; width: 100%; min-height: 52px; line-height: 1.35; resize: vertical; border: none; outline: none; padding: 2px 2px; box-sizing: border-box;"
                                placeholder=""
                                oninput="autoExpandTextarea(this); autoSaveStep4()"
                                onkeydown="handleTabKey(event, this)">{{ $step4Data['observaciones'] ?? '' }}</textarea>
                        </div>

                        <!-- 4. Tabla SySO -->
                        <div style="display: flex; justify-content: center; margin-top: 18px;">
                            <table class="anexo2-table" style="width: 60%; margin: 0 auto; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt;">
                                <thead>
                                    <tr style="background: #deebf7;">
                                        <th colspan="2" style="border: 1px solid #000000; padding: 3px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt; text-transform: uppercase;">
                                            PROFESIONAL CON REGISTRO SySO vigente
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; text-align: left; background: #ffffff;">Nombre:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;">
                                            <input type="text" id="step4_syso_nombre" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_nombre'] ?? '' }}" 
                                                placeholder="Nombre del profesional..."
                                                oninput="autoSaveSysoField('profesional_nombre', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; text-align: left; background: #ffffff;">N° Registro:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;">
                                            <input type="text" id="step4_syso_reg" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_registro'] ?? '' }}" 
                                                placeholder="N° de Registro..."
                                                oninput="autoSaveSysoField('profesional_registro', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; text-align: left; background: #ffffff;">Fecha:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 1px 4px; text-align: left;">
                                            <input type="text" id="step4_syso_fecha" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_fecha'] ?? '' }}" 
                                                placeholder="dd/mm/aaaa"
                                                oninput="autoSaveSysoField('profesional_fecha', this.value)"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr style="height: 36pt;">
                                        <td style="width: 32%; border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold; vertical-align: top; text-align: left; background: #ffffff;">Firma:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 2.5px 6px; height: 36pt; vertical-align: middle; text-align: center;">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- PASO 5: REGISTRO Nº 4 (MATRIZ DE SEGUIMIENTO DE MEDIDAS PREVENTIVAS)      -->
            <!-- ========================================================================= -->
            <div class="step-pane-content" id="step_pane_5">
                @php
                    $step5Data = $anexo2Data['step5_data'] ?? [];
                    $maxIndexFromData = -1;
                    foreach ($step5Data as $k => $v) {
                        if (preg_match('/^(?:puesto|fecha_eval|risk|f_admin|f_ing|f_cierre)_row_(\d+)$/', $k, $matches)) {
                            $maxIndexFromData = max($maxIndexFromData, (int)$matches[1]);
                        } elseif (preg_match('/^puesto_(\d+)$/', $k, $matches)) {
                            $maxIndexFromData = max($maxIndexFromData, (int)$matches[1]);
                        }
                    }
                    $totalRowsCount = max(6, count($allMeasurements), $maxIndexFromData + 1);
                @endphp

                <div class="anexo2-sheet-wrapper">

                    <!-- Barra de Herramientas de la Hoja -->
                    <div class="anexo2-sheet-toolbar">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="step-pane-badge" style="margin-bottom: 0;">Formato Oficial — Registro Nº 4</span>
                            <span id="step5AutoSaveBadge" class="header-auto-save-status">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <polyline points="20 6 9 17 4 12" />
                                </svg>
                                <span>Guardado</span>
                            </span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" class="btn-download-sheet" id="btnDownloadStep5Word" onclick="downloadRegistro4Doc()"
                                title="Descargar Registro Nº 4 compatible con Microsoft Word (.doc) en formato vertical y Arial 9pt">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                                    <polyline points="7 10 12 15 17 10" />
                                    <line x1="12" y1="15" x2="12" y2="3" />
                                </svg>
                                <span>Descargar Word (.doc)</span>
                            </button>
                        </div>
                    </div>

                    <!-- Documento Registro Nº 4 (Hoja Carta - Arial 9pt) -->
                    <div class="anexo2-sheet-card" id="registro4Sheet"
                        style="font-family: Arial, sans-serif; font-size: 9pt; color: #000000;">

                        <!-- Caja de Encabezado Principal -->
                        <div style="border: 1.5px solid #000000; padding: 6px 10px; text-align: center; margin-bottom: 12px; background: #ffffff;">
                            <div style="font-family: Arial, sans-serif; font-size: 9.5pt; font-weight: bold; text-transform: uppercase; color: #000000; letter-spacing: 0.3px;">
                                REGISTRO Nº 4: MATRIZ DE SEGUIMIENTO DE MEDIDAS PREVENTIVAS
                            </div>
                        </div>

                        <!-- 1. Tabla de Identificación General -->
                        <table class="anexo2-table" style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt; margin-bottom: 14px;">
                            <tbody>
                                <tr>
                                    <td style="width: 32%; border: 1px solid #000000; padding: 3.5px 6px; font-weight: normal; background: #ffffff;">Razón Social:</td>
                                    <td style="width: 68%; border: 1px solid #000000; padding: 3.5px 6px; font-weight: bold; background: #ffffff;" id="step5_val_razon_social">
                                        {{ $anexo2Data['razon_social'] ?? ($companyName ?? 'SURGE SRL - CASA MATRIZ SENKATA') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 32%; border: 1px solid #000000; padding: 3.5px 6px; font-weight: normal; background: #ffffff;">Dirección de la empresa o establecimiento laboral:</td>
                                    <td style="width: 68%; border: 1px solid #000000; padding: 3.5px 6px; font-weight: bold; background: #ffffff;" id="step5_val_direccion">
                                        {{ $anexo2Data['direccion'] ?? ($companyAddress ?? '25 de Julio Alejandria Nº 8345 UV Edificio') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 32%; border: 1px solid #000000; padding: 3.5px 6px; font-weight: normal; background: #ffffff;">Área y Sector en estudio:</td>
                                    <td style="width: 68%; border: 1px solid #000000; padding: 3.5px 6px; font-weight: bold; background: #ffffff;" id="step5_val_area">
                                        {{ $anexo2Data['area_sector'] ?? ($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Administración') : 'Administración') }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- 2. Matriz de Seguimiento -->
                        <table class="anexo2-table" id="step5_seguimiento_table" style="width: 100%; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt; margin-bottom: 18px;">
                            <thead>
                                <tr style="background: #deebf7; text-align: center; font-weight: bold; font-size: 8.5pt;">
                                    <th style="width: 5%; border: 1px solid #000000; padding: 4px 2px; text-align: center;">N°</th>
                                    <th style="width: 25%; border: 1px solid #000000; padding: 4px 5px; text-align: center;">Nombre del Puesto</th>
                                    <th style="width: 13%; border: 1px solid #000000; padding: 4px 3px; text-align: center;">Fecha de Evaluación</th>
                                    <th style="width: 13%; border: 1px solid #000000; padding: 4px 3px; text-align: center;">Nivel de riesgo</th>
                                    <th style="width: 16%; border: 1px solid #000000; padding: 4px 3px; text-align: center; line-height: 1.15;">Fecha de implementación de la Medida Administrativa</th>
                                    <th style="width: 16%; border: 1px solid #000000; padding: 4px 3px; text-align: center; line-height: 1.15;">Fecha de implementación de la Medida de Ingeniería</th>
                                    <th style="width: 12%; border: 1px solid #000000; padding: 4px 3px; text-align: center;">Fecha de Cierre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < $totalRowsCount; $i++)
                                    @php
                                        $m = $allMeasurements[$i] ?? null;
                                        $rowId = $m ? (string)$m->id : ('row_'.$i);
                                        $rowNum = $i + 1;
                                        $puestoVal = $step5Data['puesto_'.$rowId] ?? ($m ? ($m->puesto_trabajo ?: 'Puesto '.$rowNum) : '');
                                        $fechaEvalVal = $step5Data['fecha_eval_'.$rowId] ?? ($m ? ($m->date_formatted ?: ($m->date ?: '')) : '');
                                        $riskVal = $step5Data['risk_'.$rowId] ?? ($m ? ($m->calculated_risk_level ?? 'Medio') : '');
                                        $fAdmin = $step5Data['f_admin_'.$rowId] ?? '';
                                        $fIng = $step5Data['f_ing_'.$rowId] ?? '';
                                        $fCierre = $step5Data['f_cierre_'.$rowId] ?? '';
                                    @endphp
                                    <tr data-row-id="{{ $rowId }}">
                                        <td style="width: 5%; border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold; position: relative;">
                                            <div class="table-row-actions-left">
                                                <button type="button" class="btn-del-table-row" onclick="removeStep5Row('{{ $rowId }}')" title="Eliminar fila">&times;</button>
                                                @if($rowNum === $totalRowsCount)
                                                    <button type="button" class="btn-add-table-row" id="btnAddStep5Row" onclick="addStep5Row()" title="Añadir fila">+</button>
                                                @endif
                                            </div>
                                            <span class="step5-row-num">{{ $rowNum }}</span>
                                        </td>
                                        <td style="width: 25%; border: 1px solid #000000; padding: 2px 4px;">
                                            <input type="text" id="step5_puesto_{{ $rowId }}" class="syso-live-input" value="{{ $puestoVal }}" placeholder="..." oninput="autoSaveStep5()" style="font-size: 8.5pt;">
                                        </td>
                                        <td style="width: 13%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                                            <input type="text" id="step5_fecha_eval_{{ $rowId }}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="{{ $fechaEvalVal }}" placeholder="dd/mm/aaaa" oninput="autoSaveStep5()">
                                        </td>
                                        <td style="width: 13%; border: 1px solid #000000; padding: 2px 3px; text-align: center; font-weight: bold;">
                                            <input type="text" id="step5_risk_{{ $rowId }}" class="syso-live-input" style="text-align: center; font-weight: bold; font-size: 8.5pt;" value="{{ $riskVal }}" placeholder="..." oninput="autoSaveStep5()">
                                        </td>
                                        <td style="width: 16%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                                            <input type="text" id="step5_f_admin_{{ $rowId }}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="{{ $fAdmin }}" placeholder="" oninput="autoSaveStep5()">
                                        </td>
                                        <td style="width: 16%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                                            <input type="text" id="step5_f_ing_{{ $rowId }}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="{{ $fIng }}" placeholder="" oninput="autoSaveStep5()">
                                        </td>
                                        <td style="width: 12%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                                            <input type="text" id="step5_f_cierre_{{ $rowId }}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="{{ $fCierre }}" placeholder="dd/mm/aaaa" oninput="autoSaveStep5()">
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>

                        <!-- 3. Tabla SySO -->
                        <div style="display: flex; justify-content: center; margin-top: 22px;">
                            <table class="anexo2-table" style="width: 65%; margin: 0 auto; border-collapse: collapse; border: 1.5px solid #000000; font-family: Arial, sans-serif; font-size: 9pt;">
                                <thead>
                                    <tr style="background: #deebf7;">
                                        <th colspan="2" style="border: 1px solid #000000; padding: 3.5px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt; text-transform: uppercase;">
                                            PROFESIONAL CON REGISTRO SySO vigente
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal; text-align: left; background: #ffffff;">Nombre:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 2px 4px; text-align: left;">
                                            <input type="text" id="step5_syso_nombre" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_nombre'] ?? '' }}" 
                                                placeholder="Nombre del profesional..."
                                                oninput="autoSaveSysoField('profesional_nombre', this.value); autoSaveStep5();"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal; text-align: left; background: #ffffff;">N° Registro:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 2px 4px; text-align: left;">
                                            <input type="text" id="step5_syso_reg" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_registro'] ?? '' }}" 
                                                placeholder="N° de Registro..."
                                                oninput="autoSaveSysoField('profesional_registro', this.value); autoSaveStep5();"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="width: 32%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal; text-align: left; background: #ffffff;">Fecha:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 2px 4px; text-align: left;">
                                            <input type="text" id="step5_syso_fecha" class="syso-live-input" 
                                                value="{{ $anexo2Data['profesional_fecha'] ?? '' }}" 
                                                placeholder="dd/mm/aaaa"
                                                oninput="autoSaveSysoField('profesional_fecha', this.value); autoSaveStep5();"
                                                style="width: 100%; border: none; background: transparent; font-family: inherit; font-size: 9pt; text-align: left; outline: none; padding: 2px 4px; box-sizing: border-box;">
                                        </td>
                                    </tr>
                                    <tr style="height: 38pt;">
                                        <td style="width: 32%; border: 1px solid #000000; padding: 3px 6px; font-weight: normal; vertical-align: top; text-align: left; background: #ffffff;">Firma:</td>
                                        <td style="width: 68%; border: 1px solid #000000; padding: 2px 6px; height: 38pt; vertical-align: middle; text-align: center;">&nbsp;</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- 4. Barra Inferior de Navegación del Stepper -->
        <div class="stepper-footer-bar">
            <div class="stepper-footer-left">
                <span class="stepper-indicator-text" id="stepperIndicatorText">
                    Paso <strong>1</strong> de <strong>5</strong>
                </span>
                <span class="stepper-keyboard-hint">
                    Navega con <kbd>&larr;</kbd> <kbd>&rarr;</kbd>
                </span>
            </div>

            <div class="stepper-footer-right">
                <button type="button" class="btn-stepper-nav subtle" id="btnPrevStep" onclick="prevStep()" disabled>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6" />
                    </svg>
                    <span>Paso Anterior</span>
                </button>

                <button type="button" class="btn-stepper-nav primary" id="btnNextStep" onclick="nextStep()">
                    <span id="btnNextStepText">Paso Siguiente (Tablas ROSA)</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6" />
                    </svg>
                </button>
            </div>
        </div>

    </div>

    <!-- ========================================================================= -->
    <!-- MODAL: PROMPTS, ANÁLISIS TÉCNICO, OBSERVACIONES Y RECOMENDACIONES         -->
    <!-- ========================================================================= -->
    @include('measurements.ergonomia_rosa.modals.prompts-modal')
@endsection

@push('scripts')
    <script>
        let currentStep = 1;
        const totalSteps = 5;
        const moduleId = {{ $module->id ?? 1 }};
        const csrfToken = '{{ csrf_token() }}';

        // Auto-expansión reactiva para textareas de tablas y observaciones
        function autoExpandTextarea(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = Math.max(el.scrollHeight, 24) + 'px';
        }

        // Manejador para permitir tabulación (sangría) dentro de textareas
        function handleTabKey(e, el) {
            if (e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                const start = el.selectionStart;
                const end = el.selectionEnd;
                const val = el.value;
                el.value = val.substring(0, start) + "    " + val.substring(end);
                el.selectionStart = el.selectionEnd = start + 4;
                el.dispatchEvent(new Event('input'));
            }
        }

        function goToStep(step) {
            if (step < 1) step = 1;
            if (step > totalSteps) step = totalSteps;
            currentStep = step;

            // Actualizar tabs
            document.querySelectorAll('.step-nav-btn').forEach(btn => {
                const stepNum = parseInt(btn.getAttribute('data-step'));
                btn.classList.toggle('active', stepNum === currentStep);
                btn.classList.toggle('completed', stepNum < currentStep);
            });

            // Actualizar paneles
            document.querySelectorAll('.step-pane-content').forEach(pane => {
                pane.classList.remove('active');
            });
            const activePane = document.getElementById(`step_pane_${currentStep}`);
            if (activePane) activePane.classList.add('active');

            // Redimensionar textareas en el paso activo
            setTimeout(() => {
                document.querySelectorAll('.syso-live-textarea').forEach(el => {
                    autoExpandTextarea(el);
                });
            }, 60);

            // Actualizar barra de progreso
            const progressPct = (currentStep / totalSteps) * 100;
            const progressEl = document.getElementById('stepperProgressBar');
            if (progressEl) progressEl.style.width = `${progressPct}%`;

            // Actualizar botones de navegación
            const prevBtn = document.getElementById('btnPrevStep');
            const nextBtn = document.getElementById('btnNextStep');
            const nextText = document.getElementById('btnNextStepText');
            const indicator = document.getElementById('stepperIndicatorText');

            if (prevBtn) prevBtn.disabled = (currentStep === 1);
            if (indicator) indicator.innerHTML = `Paso <strong>${currentStep}</strong> de <strong>${totalSteps}</strong>`;

            if (currentStep === 1) {
                if (nextText) nextText.textContent = 'Paso Siguiente (Tablas ROSA)';
            } else if (currentStep === 2) {
                if (nextText) nextText.textContent = 'Paso Siguiente (Evaluación Detallada)';
            } else if (currentStep === 3) {
                if (nextText) nextText.textContent = 'Paso Siguiente (Registro Nº 3 - Medidas)';
            } else if (currentStep === 4) {
                if (nextText) nextText.textContent = 'Paso Siguiente (Registro Nº 4 - Seguimiento)';
            } else {
                if (nextText) nextText.textContent = 'Volver a Evaluaciones';
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function nextStep() {
            if (currentStep >= totalSteps) {
                window.location.href = "{{ route('modules.ergonomia_rosa', $module->id) }}";
                return;
            }
            goToStep(currentStep + 1);
        }

        function prevStep() {
            if (currentStep > 1) {
                goToStep(currentStep - 1);
            }
        }

        // Atajos de teclado para el stepper
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowRight' && !['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                nextStep();
            } else if (e.key === 'ArrowLeft' && !['INPUT', 'SELECT', 'TEXTAREA'].includes(document.activeElement.tagName)) {
                prevStep();
            }
        });

        function changeMeasurement(measId) {
            if (!measId) return;
            const url = new URL(window.location.href);
            url.searchParams.set('evaluation_id', measId);
            window.location.href = url.toString();
        }

        // =========================================================================
        // AUTO-GUARDADO REACTIVO DEL ANEXO 2
        // =========================================================================
        let anexo2SaveTimer = null;
        const anexo2HeaderData = @json($anexo2Data);

        function gatherAnexo2Data() {
            const factors = {};
            const factorCodes = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
            const factorNames = {
                'A': 'Levantamiento y descenso',
                'B': 'Empuje / arrastre',
                'C': 'Transporte',
                'D': 'Bipedestación',
                'E': 'Movimientos repetitivos',
                'F': 'Postura forzada',
                'G': 'Vibraciones',
                'H': 'Confort térmico',
                'I': 'Estrés de contacto'
            };

            factorCodes.forEach(code => {
                const row = document.querySelector(`tr[data-factor-code="${code}"]`);
                if (row) {
                    const t1 = row.querySelector('input[data-sub="t1"]')?.checked || false;
                    const t2 = row.querySelector('input[data-sub="t2"]')?.checked || false;
                    const t3 = row.querySelector('input[data-sub="t3"]')?.checked || false;
                    const horas = row.querySelector('input[data-sub="horas"]')?.value || '';
                    const r1 = row.querySelector('select[data-sub="r1"]')?.value || '';
                    const r2 = row.querySelector('select[data-sub="r2"]')?.value || '';
                    const r3 = row.querySelector('select[data-sub="r3"]')?.value || '';

                    factors[code] = {
                        name: factorNames[code] || '',
                        t1: t1,
                        t2: t2,
                        t3: t3,
                        horas: horas,
                        r1: r1,
                        r2: r2,
                        r3: r3
                    };
                }
            });

            function getCellText(id, fallback) {
                const el = document.getElementById(id);
                if (!el) return (fallback !== undefined && fallback !== null) ? String(fallback) : '';
                const txt = el.innerText !== undefined ? el.innerText.trim() : (el.textContent ? el.textContent.trim() : '');
                return txt !== '' ? txt : ((fallback !== undefined && fallback !== null) ? String(fallback) : '');
            }

            return {
                razon_social: getCellText('anexo2_val_razon_social', anexo2HeaderData.razon_social || ''),
                direccion: getCellText('anexo2_val_direccion', anexo2HeaderData.direccion || ''),
                area_sector: getCellText('anexo2_val_area_sector', anexo2HeaderData.area_sector || 'Administración'),
                num_trabajadores: getCellText('anexo2_val_num_trabajadores', anexo2HeaderData.num_trabajadores || 1),
                puesto_trabajo: getCellText('anexo2_val_puesto_trabajo', anexo2HeaderData.puesto_trabajo || 'Gerente general'),
                procedimiento_escrito: getCellText('anexo2_val_procedimiento_escrito', anexo2HeaderData.procedimiento_escrito || 'SI'),
                capacitacion: getCellText('anexo2_val_capacitacion', anexo2HeaderData.capacitacion || 'Si'),
                nombre_trabajador: getCellText('anexo2_val_nombre_trabajador', anexo2HeaderData.nombre_trabajador || ''),
                manifestacion_temprana: getCellText('anexo2_val_manifestacion_temprana', anexo2HeaderData.manifestacion_temprana || 'No'),
                ubicacion_sintoma: getCellText('anexo2_val_ubicacion_sintoma', anexo2HeaderData.ubicacion_sintoma || 'Ninguna'),
                tareas: {
                    tarea_1: document.getElementById('anexo2_header_tarea_1')?.innerText.trim() || '---',
                    tarea_2: document.getElementById('anexo2_header_tarea_2')?.innerText.trim() || '---',
                    tarea_3: document.getElementById('anexo2_header_tarea_3')?.innerText.trim() || '---',
                },
                factors: factors,
                profesional_nombre: document.getElementById('step1_syso_nombre')?.value || document.getElementById('step3_syso_nombre')?.value || document.getElementById('step4_syso_nombre')?.value || anexo2HeaderData.profesional_nombre || '',
                profesional_registro: document.getElementById('step1_syso_reg')?.value || document.getElementById('step3_syso_reg')?.value || document.getElementById('step4_syso_reg')?.value || anexo2HeaderData.profesional_registro || '',
                profesional_fecha: document.getElementById('step1_syso_fecha')?.value || document.getElementById('step3_syso_fecha')?.value || document.getElementById('step4_syso_fecha')?.value || anexo2HeaderData.profesional_fecha || '',
            };
        }

        function triggerAnexo2AutoSave() {
            const badge = document.getElementById('anexo2AutoSaveBadge');
            const textEl = document.getElementById('anexo2AutoSaveText');
            if (badge && textEl) {
                badge.style.opacity = '0.6';
                textEl.textContent = 'Guardando...';
            }

            clearTimeout(anexo2SaveTimer);
            anexo2SaveTimer = setTimeout(() => {
                const payload = {
                    _token: csrfToken,
                    anexo2_data: gatherAnexo2Data()
                };

                fetch(`/modulos/${moduleId}/ergonomia-rosa/anexo2`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (badge && textEl) {
                            badge.style.opacity = '1';
                            textEl.textContent = 'Guardado ✓';
                        }
                    })
                    .catch(err => {
                        console.error('Error al guardar Anexo 2:', err);
                        if (badge && textEl) {
                            badge.style.opacity = '1';
                            textEl.textContent = 'Error al guardar';
                        }
                    });
            }, 500);
        }

        // Actualizar colores dinámicos en celdas de nivel de riesgo
        function updateRiskColors() {
            document.querySelectorAll('.anexo2-risk-select').forEach(sel => {
                const val = sel.value;
                const td = sel.closest('td');
                if (td) {
                    td.setAttribute('data-level', val);
                    if (val === '1') {
                        td.style.backgroundColor = '#00b050';
                        td.style.color = '#000000';
                        sel.style.color = '#000000';
                    } else if (val === '2') {
                        td.style.backgroundColor = '#ffff00';
                        td.style.color = '#000000';
                        sel.style.color = '#000000';
                    } else if (val === '3') {
                        td.style.backgroundColor = '#ff0000';
                        td.style.color = '#ffffff';
                        sel.style.color = '#ffffff';
                    } else {
                        td.style.backgroundColor = '';
                        td.style.color = '';
                        sel.style.color = '';
                    }
                }
            });
        }

        // Event listeners para interacción y autoguardado en la matriz de factores
        document.addEventListener('DOMContentLoaded', () => {
            const matrixTable = document.getElementById('anexo2FactorsMatrixTable');
            if (matrixTable) {
                matrixTable.querySelectorAll('input, select').forEach(el => {
                    el.addEventListener('input', () => {
                        updateRiskColors();
                        triggerAnexo2AutoSave();
                    });
                    el.addEventListener('change', () => {
                        updateRiskColors();
                        triggerAnexo2AutoSave();
                    });
                });
            }
            updateRiskColors();
        });

        // =========================================================================
        // UTILIDADES DE FORMATO Y EXPORTACIÓN OFICIAL A MICROSOFT WORD (ARIAL 9PT)
        // =========================================================================

        function escapeHtml(str) {
            if (str === undefined || str === null) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function showActionToast(message, type = 'success') {
            const existing = document.querySelector('.table-action-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = `table-action-toast ${type}`;
            toast.innerHTML = `
                                                        <svg class="table-action-toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                            <polyline points="20 6 9 17 4 12"/>
                                                        </svg>
                                                        <span>${message}</span>
                                                    `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(12px)';
                setTimeout(() => toast.remove(), 400);
            }, 4000);
        }

        // =========================================================================
        // GENERADOR HTML COMPATIBLE 100% MICROSOFT WORD — PASO 1 (ANEXO 2)
        // =========================================================================
        function generateAnexo2WordHtml() {
            const data = gatherAnexo2Data();

            const factorCodes = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I'];
            const factorNames = {
                'A': 'Levantamiento y descenso',
                'B': 'Empuje / arrastre',
                'C': 'Transporte',
                'D': 'Bipedestación',
                'E': 'Movimientos repetitivos',
                'F': 'Postura forzada',
                'G': 'Vibraciones',
                'H': 'Confort térmico',
                'I': 'Estrés de contacto'
            };

            const getBgStyle = (lvl) => {
                if (lvl === '1') return { td: 'background-color: #00b050; background: #00b050;', span: 'color: #000000;' };
                if (lvl === '2') return { td: 'background-color: #ffff00; background: #ffff00;', span: 'color: #000000;' };
                if (lvl === '3') return { td: 'background-color: #ff0000; background: #ff0000;', span: 'color: #ffffff;' };
                return { td: '', span: '' };
            };

            let factorRowsHtml = '';
            factorCodes.forEach(code => {
                const f = data.factors[code] || {};
                const r1Cfg = getBgStyle(f.r1);
                const r2Cfg = getBgStyle(f.r2);
                const r3Cfg = getBgStyle(f.r3);

                const t1Mark = f.t1 ? 'X' : '&nbsp;';
                const t2Mark = f.t2 ? 'X' : '&nbsp;';
                const t3Mark = f.t3 ? 'X' : '&nbsp;';

                factorRowsHtml += `
                                                        <tr style="mso-yfti-irow: 1; height: 16pt;">
                                                            <td width="4%" align="center" valign="middle" style="width: 4%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; background-color: #ffffff; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${code}</span></p>
                                                            </td>
                                                            <td width="24%" align="left" valign="middle" style="width: 24%; text-align: left; vertical-align: middle; mso-valign: center; padding: 2pt 4pt; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">
                                                                <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">${escapeHtml(factorNames[code])}</span></p>
                                                            </td>
                                                            <td width="12%" align="center" valign="middle" style="width: 12%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${t1Mark}</span></p>
                                                            </td>
                                                            <td width="12%" align="center" valign="middle" style="width: 12%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${t2Mark}</span></p>
                                                            </td>
                                                            <td width="12%" align="center" valign="middle" style="width: 12%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${t3Mark}</span></p>
                                                            </td>
                                                            <td width="12%" align="center" valign="middle" style="width: 12%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">${escapeHtml(f.horas || '')}</span></p>
                                                            </td>
                                                            <td width="8%" align="center" valign="middle" style="width: 8%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; ${r1Cfg.td}">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; ${r1Cfg.span}">${escapeHtml(f.r1 || '')}</span></p>
                                                            </td>
                                                            <td width="8%" align="center" valign="middle" style="width: 8%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; ${r2Cfg.td}">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; ${r2Cfg.span}">${escapeHtml(f.r2 || '')}</span></p>
                                                            </td>
                                                            <td width="8%" align="center" valign="middle" style="width: 8%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; ${r3Cfg.td}">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; ${r3Cfg.span}">${escapeHtml(f.r3 || '')}</span></p>
                                                            </td>
                                                        </tr>`;
            });

            const task1 = document.getElementById('anexo2_header_tarea_1')?.innerText.trim() || '---';
            const task2 = document.getElementById('anexo2_header_tarea_2')?.innerText.trim() || '---';
            const task3 = document.getElementById('anexo2_header_tarea_3')?.innerText.trim() || '---';

            return `<!DOCTYPE html>
                                            <html xmlns:v="urn:schemas-microsoft-com:vml"
                                                  xmlns:o="urn:schemas-microsoft-com:office:office"
                                                  xmlns:w="urn:schemas-microsoft-com:office:word"
                                                  xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
                                                  xmlns="http://www.w3.org/TR/REC-html40">
                                            <head>
                                            <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
                                            <meta name="ProgId" content="Word.Document">
                                            <meta name="Generator" content="Microsoft Word 15">
                                            <meta name="Originator" content="Microsoft Word 15">
                                            <!--[if gte mso 9]>
                                            <xml>
                                              <w:WordDocument>
                                                <w:View>Print</w:View>
                                                <w:Zoom>100</w:Zoom>
                                                <w:DoNotOptimizeForBrowser/>
                                              </w:WordDocument>
                                            </xml>
                                            <![endif]-->
                                            <style>
                                              @page WordSection1 {
                                                size: 21.59cm 27.94cm; /* Carta / Letter */
                                                margin: 1.2cm 1.5cm 1.0cm 1.5cm; /* Margen superior e inferior optimizados */
                                                mso-header-margin: 35.4pt;
                                                mso-footer-margin: 35.4pt;
                                                mso-paper-source: 0;
                                              }
                                              div.WordSection1 {
                                                page: WordSection1;
                                                font-family: Arial, Helvetica, sans-serif !important;
                                                font-size: 9.0pt !important;
                                                color: #000000;
                                              }
                                              /* Estilos específicos para textos FUERA de las tablas: interlineado cómodo y separación limpia */
                                              p.doc-out-text, p.doc-title, p.doc-subtitle, p.section-heading, p.footnote-text, p.ref-heading {
                                                font-family: Arial, sans-serif !important;
                                                color: #000000 !important;
                                                line-height: 1.28 !important;
                                                mso-line-height-rule: at-least !important;
                                                mso-para-margin-left: 0pt !important;
                                                mso-para-margin-right: 0pt !important;
                                              }
                                              /* Contenido dentro de celdas: tablas compactas e intactas */
                                              td p, th p, td p.MsoNormal, th p.MsoNormal {
                                                margin: 0cm !important;
                                                margin-top: 0pt !important;
                                                margin-bottom: 0pt !important;
                                                margin-left: 0pt !important;
                                                margin-right: 0pt !important;
                                                mso-para-margin: 0cm !important;
                                                mso-para-margin-top: 0pt !important;
                                                mso-para-margin-bottom: 0pt !important;
                                                mso-para-margin-left: 0pt !important;
                                                mso-para-margin-right: 0pt !important;
                                                font-family: Arial, sans-serif !important;
                                                line-height: normal !important;
                                                mso-line-height-rule: exactly !important;
                                              }
                                              table.MsoNormalTable, table {
                                                border-collapse: collapse !important;
                                                mso-table-layout-alt: fixed !important;
                                                border: 1.0pt solid #000000 !important;
                                                font-family: Arial, sans-serif !important;
                                                font-size: 9.0pt !important;
                                              }
                                              td, th {
                                                font-family: Arial, sans-serif !important;
                                                font-size: 9.0pt !important;
                                                mso-ansi-font-size: 9.0pt !important;
                                                border: 1.0pt solid #000000 !important;
                                                vertical-align: middle !important;
                                                mso-valign: center !important;
                                                margin: 0pt !important;
                                                mso-para-margin: 0pt !important;
                                              }
                                            </style>
                                            </head>
                                            <body style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; margin: 0; padding: 0;">
                                            <!--StartFragment-->
                                            <div class="WordSection1" style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; color: #000000; width: 100%;">

                                                <!-- Encabezado del Formato -->
                                                <p class="MsoNormal doc-title" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 2pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 2pt; text-align: center; font-family: Arial, sans-serif; font-size: 11.0pt; mso-ansi-font-size: 11.0pt; font-weight: bold; line-height: 1.25;">
                                                    <span style="font-family: Arial, sans-serif; font-size: 11.0pt; mso-ansi-font-size: 11.0pt; font-weight: bold;">ANEXO 2</span>
                                                </p>
                                                <p class="MsoNormal doc-subtitle" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 8pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 8pt; text-align: center; font-family: Arial, sans-serif; font-size: 9.5pt; mso-ansi-font-size: 9.5pt; font-weight: bold; line-height: 1.25;">
                                                    <span style="font-family: Arial, sans-serif; font-size: 9.5pt; mso-ansi-font-size: 9.5pt; font-weight: bold; text-transform: uppercase;">REGISTRO Nº 1: IDENTIFICACIÓN DE FACTORES DE RIESGOS DISERGONÓMICOS</span>
                                                </p>

                                                <!-- Tabla 1: Datos Generales de la Empresa y Puesto -->
                                                <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; border-collapse: collapse; mso-table-layout-alt: fixed; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 9.0pt; margin-bottom: 8pt;">
                                                    <tr style="mso-yfti-irow: 0; height: 16pt;">
                                                        <td width="28%" align="left" valign="middle" style="width: 28%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Razón Social:</span></p>
                                                        </td>
                                                        <td colspan="3" width="72%" align="left" valign="middle" style="width: 72%; font-weight: bold; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.razon_social) || '&nbsp;'}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="mso-yfti-irow: 1; height: 16pt;">
                                                        <td width="28%" align="left" valign="middle" style="width: 28%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Dirección de la empresa o establecimiento laboral:</span></p>
                                                        </td>
                                                        <td colspan="3" width="72%" align="left" valign="middle" style="width: 72%; font-weight: bold; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.direccion) || '&nbsp;'}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="mso-yfti-irow: 2; height: 16pt;">
                                                        <td width="28%" align="left" valign="middle" style="width: 28%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Área y Sector en estudio:</span></p>
                                                        </td>
                                                        <td width="32%" align="center" valign="middle" style="width: 32%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; text-align: center; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.area_sector) || '&nbsp;'}</span></p>
                                                        </td>
                                                        <td width="24%" align="left" valign="middle" style="width: 24%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">N° de trabajadores:</span></p>
                                                        </td>
                                                        <td width="16%" align="center" valign="middle" style="width: 16%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; text-align: center; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.num_trabajadores) || '1'}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="mso-yfti-irow: 3; height: 16pt;">
                                                        <td width="28%" align="left" valign="middle" style="width: 28%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Puesto de trabajo:</span></p>
                                                        </td>
                                                        <td colspan="3" width="72%" align="center" valign="middle" style="width: 72%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; text-align: center; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.puesto_trabajo) || '&nbsp;'}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="mso-yfti-irow: 4; height: 16pt;">
                                                        <td width="28%" align="left" valign="middle" style="width: 28%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Procedimiento de trabajo escrito:</span></p>
                                                        </td>
                                                        <td width="32%" align="center" valign="middle" style="width: 32%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; text-align: center; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.procedimiento_escrito) || '&nbsp;'}</span></p>
                                                        </td>
                                                        <td width="24%" align="left" valign="middle" style="width: 24%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Capacitación:</span></p>
                                                        </td>
                                                        <td width="16%" align="center" valign="middle" style="width: 16%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; text-align: center; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.capacitacion) || '&nbsp;'}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="mso-yfti-irow: 5; height: 16pt;">
                                                        <td width="28%" align="left" valign="middle" style="width: 28%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Nombre del trabajador/es:</span></p>
                                                        </td>
                                                        <td colspan="3" width="72%" align="left" valign="middle" style="width: 72%; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">${escapeHtml(data.nombre_trabajador) || '&nbsp;'}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="mso-yfti-irow: 6; height: 16pt;">
                                                        <td width="28%" align="left" valign="middle" style="width: 28%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Manifestación temprana:</span></p>
                                                        </td>
                                                        <td width="32%" align="center" valign="middle" style="width: 32%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; text-align: center; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.manifestacion_temprana) || '&nbsp;'}</span></p>
                                                        </td>
                                                        <td width="24%" align="left" valign="middle" style="width: 24%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Ubicación del síntoma:</span></p>
                                                        </td>
                                                        <td width="16%" align="center" valign="middle" style="width: 16%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; text-align: center; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">${escapeHtml(data.ubicacion_sintoma) || '&nbsp;'}</span></p>
                                                        </td>
                                                    </tr>
                                                </table>

                                                <!-- Título de Sección Paso 1 con espaciado e interlineado -->
                                                <p class="MsoNormal section-heading" style="margin: 0cm; margin-top: 8pt; margin-bottom: 5pt; mso-para-margin: 0cm; mso-para-margin-top: 8pt; mso-para-margin-bottom: 5pt; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; line-height: 1.25; font-family: Arial, sans-serif;">
                                                    <span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">PASO 1: Identificar, las tareas y los factores de riesgo que se presentan de forma habitual en el puesto de trabajo.</span>
                                                </p>

                                                <!-- Tabla 2: Matriz de Factores de Riesgo -->
                                                <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; border-collapse: collapse; mso-table-layout-alt: fixed; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 9.0pt; margin-bottom: 4pt;">
                                                    <thead>
                                                        <tr style="background-color: #deebf7; background: #deebf7; font-weight: bold; height: 18pt;">
                                                            <th width="28%" colspan="2" rowspan="2" align="center" valign="middle" style="width: 28%; border: 1.0pt solid #000000; background-color: #deebf7; background: #deebf7; text-align: center; vertical-align: middle; mso-valign: center; padding: 2.5pt 3pt; font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-weight: bold;">Factor de riesgo de la jornada habitual de trabajo</span></p>
                                                            </th>
                                                            <th width="36%" colspan="3" align="center" valign="middle" style="width: 36%; border: 1.0pt solid #000000; background-color: #deebf7; background: #deebf7; text-align: center; vertical-align: middle; mso-valign: center; padding: 2.5pt 3pt; font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-weight: bold;">Tareas habituales del Puesto de Trabajo</span></p>
                                                            </th>
                                                            <th width="12%" rowspan="2" align="center" valign="middle" style="width: 12%; border: 1.0pt solid #000000; background-color: #deebf7; background: #deebf7; text-align: center; vertical-align: middle; mso-valign: center; padding: 2.5pt 2pt; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: 1.15; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15; font-weight: bold;">Tiempo total de exposición al Factor de Riesgo</span></p>
                                                            </th>
                                                            <th width="24%" colspan="3" align="center" valign="middle" style="width: 24%; border: 1.0pt solid #000000; background-color: #deebf7; background: #deebf7; text-align: center; vertical-align: middle; mso-valign: center; padding: 2.5pt 3pt; font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-weight: bold;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-weight: bold;">Nivel de Riesgo</span></p>
                                                            </th>
                                                        </tr>
                                                        <tr style="background-color: #ffffff; background: #ffffff; height: 16pt;">
                                                            <th width="12%" align="center" valign="middle" style="width: 12%; border: 1.0pt solid #000000; text-align: center; vertical-align: middle; mso-valign: center; padding: 2pt 1pt; font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: 1.15; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">${escapeHtml(task1)}</span></p>
                                                            </th>
                                                            <th width="12%" align="center" valign="middle" style="width: 12%; border: 1.0pt solid #000000; text-align: center; vertical-align: middle; mso-valign: center; padding: 2pt 1pt; font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: 1.15; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">${escapeHtml(task2)}</span></p>
                                                            </th>
                                                            <th width="12%" align="center" valign="middle" style="width: 12%; border: 1.0pt solid #000000; text-align: center; vertical-align: middle; mso-valign: center; padding: 2pt 1pt; font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: 1.15; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">${escapeHtml(task3)}</span></p>
                                                            </th>
                                                            <th width="8%" align="center" valign="middle" style="width: 8%; border: 1.0pt solid #000000; text-align: center; vertical-align: middle; mso-valign: center; padding: 2pt 1pt; font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: 1.15; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">${escapeHtml(task1)}</span></p>
                                                            </th>
                                                            <th width="8%" align="center" valign="middle" style="width: 8%; border: 1.0pt solid #000000; text-align: center; vertical-align: middle; mso-valign: center; padding: 2pt 1pt; font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: 1.15; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">${escapeHtml(task2)}</span></p>
                                                            </th>
                                                            <th width="8%" align="center" valign="middle" style="width: 8%; border: 1.0pt solid #000000; text-align: center; vertical-align: middle; mso-valign: center; padding: 2pt 1pt; font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">
                                                                <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; line-height: 1.15; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 7.5pt; mso-ansi-font-size: 7.5pt; font-weight: normal; line-height: 1.15;">${escapeHtml(task3)}</span></p>
                                                            </th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        ${factorRowsHtml}
                                                    </tbody>
                                                </table>

                                                <p class="MsoNormal footnote-text" style="margin: 0cm; margin-top: 5pt; margin-bottom: 7pt; mso-para-margin: 0cm; mso-para-margin-top: 5pt; mso-para-margin-bottom: 7pt; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-style: italic; line-height: 1.25; font-family: Arial, sans-serif;">
                                                    <span style="font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-style: italic;"><strong>Nota:</strong> Si alguno de los factores de riesgo se encuentra presente, continuar con la Evaluación Inicial de Factores de Riesgos Disergonómicos que se identificaron, completando el Registro Nº 2, según corresponda.</span>
                                                </p>

                                                <!-- Tabla 3: Referencia de los niveles de riesgo (8.0pt Arial) -->
                                                <p class="MsoNormal ref-heading" style="margin: 0cm; margin-top: 7pt; margin-bottom: 4pt; mso-para-margin: 0cm; mso-para-margin-top: 7pt; mso-para-margin-bottom: 4pt; line-height: 1.25; font-weight: bold; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-family: Arial, sans-serif;">
                                                    <span style="font-family: Arial, sans-serif; font-size: 8.5pt; mso-ansi-font-size: 8.5pt; font-weight: bold;">Referencia de los niveles de riesgo:</span>
                                                </p>
                                                <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; border-collapse: collapse; mso-table-layout-alt: fixed; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; margin-bottom: 8pt;">
                                                    <tr style="font-weight: bold; background-color: #ffffff; background: #ffffff; height: 16pt;">
                                                        <th width="12%" align="center" valign="middle" style="width: 12%; border: 1.0pt solid #000000; padding: 2pt; text-align: center; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">Nivel</span></p>
                                                        </th>
                                                        <th width="18%" align="center" valign="middle" style="width: 18%; border: 1.0pt solid #000000; padding: 2pt; text-align: center; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">Color</span></p>
                                                        </th>
                                                        <th width="70%" align="center" valign="middle" style="width: 70%; border: 1.0pt solid #000000; padding: 2pt 4pt; text-align: center; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">Descripción</span></p>
                                                        </th>
                                                    </tr>
                                                    <tr style="height: 16pt;">
                                                        <td width="12%" align="center" valign="middle" style="width: 12%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">3</span></p>
                                                        </td>
                                                        <td width="18%" align="center" valign="middle" style="width: 18%; background-color: #ff0000; background: #ff0000; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold; color: #ffffff;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold; color: #ffffff;">Rojo</span></p>
                                                        </td>
                                                        <td width="70%" align="left" valign="middle" style="width: 70%; text-align: left; vertical-align: middle; mso-valign: center; padding: 2pt 4pt; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: 1.15;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15;">Nivel no tolerable, se deberán implementar medidas correctivas y/o preventivas en forma inmediata, con el objeto de disminuir el nivel de riesgo.</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="height: 16pt;">
                                                        <td width="12%" align="center" valign="middle" style="width: 12%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">2</span></p>
                                                        </td>
                                                        <td width="18%" align="center" valign="middle" style="width: 18%; background-color: #ffff00; background: #ffff00; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold; color: #000000;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold; color: #000000;">Amarillo</span></p>
                                                        </td>
                                                        <td width="70%" align="left" valign="middle" style="width: 70%; text-align: left; vertical-align: middle; mso-valign: center; padding: 2pt 4pt; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: 1.15;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15;">Nivel moderado, se deberán implementar medidas correctivas y/o preventivas para proteger la salud del trabajador.</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="height: 16pt;">
                                                        <td width="12%" align="center" valign="middle" style="width: 12%; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold;">1</span></p>
                                                        </td>
                                                        <td width="18%" align="center" valign="middle" style="width: 18%; background-color: #00b050; background: #00b050; text-align: center; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold; color: #000000;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; font-weight: bold; color: #000000;">Verde</span></p>
                                                        </td>
                                                        <td width="70%" align="left" valign="middle" style="width: 70%; text-align: left; vertical-align: middle; mso-valign: center; padding: 2pt 4pt; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: 1.15;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt; mso-ansi-font-size: 8.0pt; line-height: 1.15;">Nivel tolerable, no se considera necesaria la implementación de medidas correctivas y/o preventivas para proteger la salud del trabajador.</span></p>
                                                        </td>
                                                    </tr>
                                                </table>

                                                <!-- Espacio separador entre tablas para Word -->
                                                <p class="MsoNormal" style="margin: 0cm; margin-top: 10pt; margin-bottom: 2pt; mso-para-margin-top: 10pt; mso-para-margin-bottom: 2pt; font-size: 8.0pt; line-height: 10pt;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt;">&nbsp;</span></p>

                                                <!-- Tabla 4: Profesional SySO (Centrada con 60% de ancho) -->
                                                <table width="60%" border="1" cellspacing="0" cellpadding="0" align="center" style="width: 60%; margin: 0 auto; border-collapse: collapse; mso-table-layout-alt: fixed; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                    <tr style="background-color: #deebf7; background: #deebf7; text-align: center; height: 18pt;">
                                                        <th colspan="2" align="center" valign="middle" style="border: 1.0pt solid #000000; padding: 3pt; text-align: center; vertical-align: middle; mso-valign: center; background-color: #deebf7; background: #deebf7; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold; text-transform: uppercase;">PROFESIONAL CON REGISTRO SySO vigente</span></p>
                                                        </th>
                                                    </tr>
                                                    <tr style="height: 16pt;">
                                                        <td width="32%" align="left" valign="middle" style="width: 32%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Nombre:</span></p>
                                                        </td>
                                                        <td width="68%" align="left" valign="middle" style="width: 68%; text-align: left; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: left;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">${escapeHtml(data.profesional_nombre || '')}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="height: 16pt;">
                                                        <td width="32%" align="left" valign="middle" style="width: 32%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">N° Registro:</span></p>
                                                        </td>
                                                        <td width="68%" align="left" valign="middle" style="width: 68%; text-align: left; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: left;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">${escapeHtml(data.profesional_registro || '')}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="height: 16pt;">
                                                        <td width="32%" align="left" valign="middle" style="width: 32%; font-weight: bold; background-color: #ffffff; border: 1.0pt solid #000000; padding: 2pt 4pt; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Fecha:</span></p>
                                                        </td>
                                                        <td width="68%" align="left" valign="middle" style="width: 68%; text-align: left; vertical-align: middle; mso-valign: center; border: 1.0pt solid #000000; padding: 2pt 4pt; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: left;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt;">${escapeHtml(data.profesional_fecha || '')}</span></p>
                                                        </td>
                                                    </tr>
                                                    <tr style="height: 36pt;">
                                                        <td width="32%" align="left" valign="top" style="width: 32%; font-weight: bold; background-color: #ffffff; vertical-align: top; padding-top: 4pt; padding-left: 4pt; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal;"><span style="font-family: Arial, sans-serif; font-size: 9.0pt; mso-ansi-font-size: 9.0pt; font-weight: bold;">Firma:</span></p>
                                                        </td>
                                                        <td width="68%" align="center" valign="middle" style="height: 36pt; border: 1.0pt solid #000000; text-align: center; vertical-align: middle; mso-valign: center; font-family: Arial, sans-serif; font-size: 9.0pt;">
                                                            <p class="MsoNormal" align="center" style="margin: 0cm; margin-top: 0pt; margin-bottom: 0pt; mso-para-margin: 0cm; mso-para-margin-top: 0pt; mso-para-margin-bottom: 0pt; line-height: normal; text-align: center;">&nbsp;</p>
                                                        </td>
                                                    </tr>
                                                </table>

                                            </div>
                                            <!--EndFragment-->
                                            </body>
                                            </html>`;
        }

        // Copiar Anexo 2 al portapapeles para Word
        function copyAnexo2ToClipboard() {
            const html = generateAnexo2WordHtml();
            copyHtmlToClipboardUniversal(html, '¡Hoja Anexo 2 copiada en Arial 9pt con cabeceras de 7pt y riesgos en 8pt! Lista para pegar en Word (Ctrl + V).');
        }

        // Descargar Anexo 2 directamente como archivo compatible con Word (.doc)
        function downloadAnexo2Doc() {
            const html = generateAnexo2WordHtml();
            const blob = new Blob(['\ufeff' + html], {
                type: 'application/msword;charset=utf-8'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Anexo_2_Identificacion_Factores_Ergonomia_ROSA.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Abriendo en formato Word oficial.');
        }

        // =========================================================================
        // GENERADOR HTML COMPATIBLE MICROSOFT WORD — PASO 2 (MATRICES NORMATIVAS)
        // =========================================================================
        const currentRosaScores = @json($rosaScores);

        function changeRosaEvaluation(evalId) {
            const url = new URL(window.location.href);
            url.searchParams.set('evaluation_id', evalId);
            url.hash = 'step2';
            window.location.href = url.toString();
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (window.location.hash === '#step3' || new URLSearchParams(window.location.search).get('step') === '3') {
                goToStep(3);
            } else if (window.location.hash === '#step2' || new URLSearchParams(window.location.search).get('step') === '2') {
                goToStep(2);
            }
        });

        // =========================================================================
        // GENERADOR HTML COMPATIBLE MICROSOFT WORD — PASO 2 (MATRICES NORMATIVAS)
        // =========================================================================
        function generateRosaMatricesWordHtml() {
            const sc = currentRosaScores || {
                asiento: 4, soporte: 6, score_a_base: 5, score_a_tiempo: 6,
                telefono: 3, pantalla: 1, score_b: 2,
                raton: 3, teclado: 4, score_c: 5,
                score_d: 5, score_final: 6
            };

            const matA = {
                2: {2:2, 3:2, 4:3, 5:4, 6:5, 7:6, 8:7, 9:8},
                3: {2:2, 3:2, 4:3, 5:4, 6:5, 7:6, 8:7, 9:8},
                4: {2:3, 3:3, 4:3, 5:4, 6:5, 7:6, 8:7, 9:8},
                5: {2:4, 3:4, 4:4, 5:4, 6:5, 7:6, 8:7, 9:8},
                6: {2:5, 3:5, 4:5, 5:5, 6:6, 7:7, 8:8, 9:9},
                7: {2:6, 3:6, 4:6, 5:7, 6:7, 7:8, 8:8, 9:9},
                8: {2:7, 3:7, 4:7, 5:7, 6:8, 7:8, 8:9, 9:9}
            };
            const colsA = [2, 3, 4, 5, 6, 7, 8, 9];
            const rowsA = [2, 3, 4, 5, 6, 7, 8];

            const matB = {
                0: {0:1, 1:1, 2:1, 3:2, 4:3, 5:4, 6:5, 7:6, 8:6},
                1: {0:1, 1:1, 2:2, 3:2, 4:3, 5:4, 6:5, 7:6, 8:6},
                2: {0:1, 1:2, 2:2, 3:3, 4:3, 5:4, 6:6, 7:7, 8:7},
                3: {0:2, 1:2, 2:3, 3:3, 4:4, 5:5, 6:6, 7:8, 8:8},
                4: {0:3, 1:3, 2:4, 3:4, 4:5, 5:6, 6:7, 7:8, 8:8},
                5: {0:4, 1:4, 2:5, 3:5, 4:6, 5:7, 6:8, 7:9, 8:9},
                6: {0:5, 1:5, 2:6, 3:7, 4:8, 5:8, 6:9, 7:9, 8:9}
            };
            const colsB = [0, 1, 2, 3, 4, 5, 6, 7, 8];
            const rowsB = [0, 1, 2, 3, 4, 5, 6];

            const matC = {
                0: {0:1, 1:1, 2:1, 3:2, 4:3, 5:4, 6:5, 7:6},
                1: {0:1, 1:1, 2:2, 3:3, 4:4, 5:5, 6:6, 7:7},
                2: {0:1, 1:2, 2:2, 3:3, 4:4, 5:5, 6:6, 7:7},
                3: {0:2, 1:3, 2:3, 3:3, 4:5, 5:6, 6:7, 7:8},
                4: {0:3, 1:4, 2:4, 3:5, 4:5, 5:6, 6:7, 7:8},
                5: {0:4, 1:5, 2:5, 3:6, 4:6, 5:7, 6:8, 7:9},
                6: {0:5, 1:6, 2:6, 3:7, 4:7, 5:8, 6:8, 7:9},
                7: {0:6, 1:7, 2:7, 3:8, 4:8, 5:9, 6:9, 7:9}
            };
            const colsC = [0, 1, 2, 3, 4, 5, 6, 7];
            const rowsC = [0, 1, 2, 3, 4, 5, 6, 7];

            const matD = {};
            for (let r = 1; r <= 9; r++) {
                matD[r] = {};
                for (let c = 1; c <= 9; c++) {
                    matD[r][c] = Math.max(r, c);
                }
            }
            const colsD = [1, 2, 3, 4, 5, 6, 7, 8, 9];
            const rowsD = [1, 2, 3, 4, 5, 6, 7, 8, 9];

            const matE = {};
            for (let r = 1; r <= 10; r++) {
                matE[r] = {};
                for (let c = 1; c <= 10; c++) {
                    matE[r][c] = Math.max(r, c);
                }
            }
            const colsE = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
            const rowsE = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

            function renderWordMatrix(cols, rows, mat, selectedRow, selectedCol, topLabel, leftLabel, isShadedCheck) {
                let colHeaders = cols.map(c => `<th style="border: 1.0pt solid #000000; padding: 2pt 3pt; font-size: 8.0pt; background-color: #f1f5f9;">${c}</th>`).join('');
                let rowsHtml = '';
                rows.forEach((r, rIdx) => {
                    let leftTh = (rIdx === 0) ? `<th rowspan="${rows.length}" style="border: 1.0pt solid #000000; padding: 4pt 3pt; font-size: 7.5pt; font-weight: bold; background-color: #f8fafc; vertical-align: middle; text-align: center;">${leftLabel}</th>` : '';
                    let cells = cols.map(c => {
                        let val = (mat[r] && mat[r][c] !== undefined) ? mat[r][c] : '';
                        let isSel = (r == selectedRow && c == selectedCol);
                        let isShaded = isShadedCheck ? isShadedCheck(r, c) : false;
                        let bg = isSel ? 'background-color: #2563eb; color: #ffffff; font-weight: bold;' : (isShaded ? 'background-color: #fed7aa;' : '');
                        return `<td style="border: 1.0pt solid #000000; padding: 2pt 3pt; font-size: 8.0pt; text-align: center; ${bg}">${val}</td>`;
                    }).join('');
                    rowsHtml += `<tr>${leftTh}<th style="border: 1.0pt solid #000000; padding: 2pt 3pt; font-size: 8.0pt; background-color: #f1f5f9;">${r}</th>${cells}</tr>`;
                });

                return `<table width="100%" border="1" cellspacing="0" cellpadding="0" style="border-collapse: collapse; border: 1.0pt solid #000000; font-family: Arial, sans-serif; text-align: center; margin-bottom: 4pt;">
                    <thead>
                        <tr>
                            <th colspan="2" rowspan="2" style="border: 1.0pt solid #000000; background: #ffffff;"></th>
                            <th colspan="${cols.length}" style="border: 1.0pt solid #000000; padding: 3pt; font-size: 8.0pt; font-weight: bold; background-color: #f8fafc;">${topLabel}</th>
                        </tr>
                        <tr>${colHeaders}</tr>
                    </thead>
                    <tbody>${rowsHtml}</tbody>
                </table>`;
            }

            return `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="ProgId" content="Word.Document">
<meta name="Generator" content="Microsoft Word 15">
<style>
  @page WordSection1 {
    size: 21.59cm 27.94cm;
    margin: 1.5cm 1.5cm 1.5cm 1.5cm;
  }
  div.WordSection1 {
    page: WordSection1;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 8.5pt !important;
    color: #000000;
  }
  table {
    border-collapse: collapse;
    border: 1.0pt solid #000000;
    font-family: Arial, sans-serif !important;
  }
  td, th {
    border: 1.0pt solid #000000;
    vertical-align: middle;
    font-family: Arial, sans-serif !important;
  }
  .sum-tbl {
    width: 320pt;
    margin-bottom: 6pt;
    border-collapse: collapse;
    border: 1.5pt solid #000000;
    font-size: 8.5pt;
  }
  .sum-tbl th {
    padding: 3pt 6pt;
    font-size: 9.0pt;
    font-weight: bold;
    text-align: left;
    background-color: #ffffff;
  }
  .sum-tbl td {
    padding: 2.5pt 6pt;
    font-size: 8.5pt;
  }
  .yellow-row td {
    background-color: #ffff00 !important;
    font-weight: bold !important;
  }
  .caption-txt {
    font-size: 8.0pt;
    font-style: italic;
    color: #334155;
    margin-top: 2pt;
    margin-bottom: 12pt;
  }
</style>
</head>
<body style="font-family: Arial, sans-serif; font-size: 8.5pt; margin: 0; padding: 0;">
<div class="WordSection1">

    <div align="center" style="text-align: center; margin-bottom: 10pt;">
        <p style="margin: 0; font-size: 11.0pt; font-weight: bold; text-transform: uppercase;">MÉTODO ROSA (Rapid Office Strain Assessment)</p>
        <p style="margin: 0; font-size: 9.5pt; font-weight: bold; text-transform: uppercase; color: #475569;">MATRICES BIOMECÁNICAS DE DECISIÓN CRUZADA</p>
    </div>

    <!-- TABLA A -->
    <table class="sum-tbl">
        <tr><th colspan="2">TABLA A. Puntuación más alta</th></tr>
        <tr><td>Asiento: altura + profundidad (A-1 + A-2)</td><td align="center" style="text-align:center; font-weight:bold; width:45pt;">${sc.asiento}</td></tr>
        <tr><td>Reposabrazos + respaldo (A-3 + A-4)</td><td align="center" style="text-align:center; font-weight:bold;">${sc.soporte}</td></tr>
        <tr class="yellow-row"><td>Puntuación</td><td align="center" style="text-align:center;">${sc.score_a_base}</td></tr>
    </table>
    ${renderWordMatrix(colsA, rowsA, matA, sc.asiento, sc.soporte, 'Reposabrazos + respaldo (A-3 + A-4)', 'Asiento: altura<br>+ profundidad<br>(A-1 + A-2)')}
    <p class="caption-txt">Tabla A. Puntuación de la silla</p>

    <!-- TABLA B -->
    <table class="sum-tbl">
        <tr><th colspan="2">Tabla B. Puntuación de teléfono y pantalla.</th></tr>
        <tr><td>Teléfono (B1)</td><td align="center" style="text-align:center; font-weight:bold; width:45pt;">${sc.telefono}</td></tr>
        <tr><td>Pantalla (B2)</td><td align="center" style="text-align:center; font-weight:bold;">${sc.pantalla}</td></tr>
        <tr class="yellow-row"><td>Puntuación</td><td align="center" style="text-align:center;">${sc.score_b}</td></tr>
    </table>
    ${renderWordMatrix(colsB, rowsB, matB, sc.telefono, sc.pantalla, 'Pantalla (B-2)', 'Teléfono<br>(B-1)')}
    <p class="caption-txt">Tabla B. Puntuación de teléfono y pantalla.</p>

    <!-- TABLA C -->
    <table class="sum-tbl">
        <tr><th colspan="2">Tabla C. Puntuación de ratón y teclado.</th></tr>
        <tr><td>Ratón (C1)</td><td align="center" style="text-align:center; font-weight:bold; width:45pt;">${sc.raton}</td></tr>
        <tr><td>Teclado (C2)</td><td align="center" style="text-align:center; font-weight:bold;">${sc.teclado}</td></tr>
        <tr class="yellow-row"><td>Puntuación</td><td align="center" style="text-align:center;">${sc.score_c}</td></tr>
    </table>
    ${renderWordMatrix(colsC, rowsC, matC, sc.raton, sc.teclado, 'Teclado (C-2)', 'Ratón<br>(C-1)')}
    <p class="caption-txt">Tabla C. Puntuación de ratón y teclado.</p>

    <!-- TABLA D -->
    <table class="sum-tbl">
        <tr><th colspan="2">Tabla D: pantalla y periféricos.</th></tr>
        <tr><td>Tabla C (ratón y teclado)</td><td align="center" style="text-align:center; font-weight:bold; width:45pt;">${sc.score_c}</td></tr>
        <tr><td>Tabla B (teléfono y pantalla)</td><td align="center" style="text-align:center; font-weight:bold;">${sc.score_b}</td></tr>
        <tr class="yellow-row"><td>Puntuación</td><td align="center" style="text-align:center;">${sc.score_d}</td></tr>
    </table>
    ${renderWordMatrix(colsD, rowsD, matD, sc.score_b, sc.score_c, 'Tabla C (ratón y teclado)', 'Tabla B<br>(teléfono y<br>pantalla)')}
    <p class="caption-txt">Tabla D. Puntuación de pantalla y periféricos.</p>

    <!-- TABLA E -->
    <table class="sum-tbl">
        <tr><th colspan="2">Tabla E: puntuación final</th></tr>
        <tr><td>Tabla D (pantalla y periféricos)</td><td align="center" style="text-align:center; font-weight:bold; width:45pt;">${sc.score_d}</td></tr>
        <tr><td>Tabla A (silla) con factor tiempo</td><td align="center" style="text-align:center; font-weight:bold;">${sc.score_a_tiempo}</td></tr>
        <tr class="yellow-row"><td>Puntuación final</td><td align="center" style="text-align:center;">${sc.score_final}</td></tr>
    </table>
    ${renderWordMatrix(colsE, rowsE, matE, sc.score_a_tiempo, sc.score_d, 'Tabla D (pantalla y periféricos)', 'Tabla A<br>(silla) con<br>factor tiempo', (r, c) => (r >= 5 || c >= 5))}
    <p class="caption-txt">Tabla E. Puntuación final del método ROSA. Las casillas sombreadas corresponden al nivel de acción que requiere actuación.</p>

</div>
</body>
</html>`;
        }

        // Copiar matrices ROSA al portapapeles para Word
        function copyRosaMatricesToClipboard() {
            const html = generateRosaMatricesWordHtml();
            copyHtmlToClipboardUniversal(html, '¡Tablas Normativas ROSA copiadas! Listas para pegar en Word (Ctrl + V).');
        }

        // Descargar matrices ROSA directamente como documento Word (.doc)
        function downloadRosaMatricesDoc() {
            const html = generateRosaMatricesWordHtml();
            const blob = new Blob(['\ufeff' + html], {
                type: 'application/msword;charset=utf-8'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Tablas_Normativas_Metodo_ROSA.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Catálogo normativo ROSA para Word.');
        }

        // =========================================================================
        // EXPORTACIÓN UNIVERSAL AL PORTAPAPELES (ALTA FIDELIDAD MICROSOFT WORD)
        // =========================================================================
        function copyHtmlToClipboardUniversal(html, successMsg) {
            const plainText = "ANEXO 2 - IDENTIFICACIÓN DE FACTORES DE RIESGOS DISERGONÓMICOS (MÉTODO ROSA)";

            if (navigator.clipboard && window.ClipboardItem) {
                const blobHtml = new Blob([html], { type: 'text/html' });
                const blobText = new Blob([plainText], { type: 'text/plain' });

                navigator.clipboard.write([
                    new ClipboardItem({
                        'text/html': blobHtml,
                        'text/plain': blobText
                    })
                ]).then(() => {
                    showActionToast(successMsg);
                }).catch(err => {
                    console.warn('ClipboardItem fallo, utilizando listener oncopy:', err);
                    execCopyEventFallback(html, plainText, successMsg);
                });
            } else {
                execCopyEventFallback(html, plainText, successMsg);
            }
        }

        function execCopyEventFallback(html, plainText, successMsg) {
            const onCopy = function (e) {
                e.clipboardData.setData('text/html', html);
                e.clipboardData.setData('text/plain', plainText || 'Documento exportado');
                e.preventDefault();
            };
            document.addEventListener('copy', onCopy);
            try {
                const ok = document.execCommand('copy');
                if (ok) {
                    showActionToast(successMsg);
                } else {
                    fallbackDomSelectionCopy(html, successMsg);
                }
            } catch (err) {
                fallbackDomSelectionCopy(html, successMsg);
            } finally {
                document.removeEventListener('copy', onCopy);
            }
        }

        function fallbackDomSelectionCopy(html, successMsg) {
            const dummy = document.createElement('div');
            dummy.style.position = 'fixed';
            dummy.style.left = '-9999px';
            dummy.innerHTML = html;
            document.body.appendChild(dummy);

            const range = document.createRange();
            range.selectNodeContents(dummy);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
            try {
                document.execCommand('copy');
                showActionToast(successMsg || '¡Copiado al portapapeles! Puedes pegarlo en Word (Ctrl + V).');
            } catch (e) {
                showActionToast('Presione Ctrl + C para copiar el contenido', 'warning');
            }
            sel.removeAllRanges();
            document.body.removeChild(dummy);
        }

        // =========================================================================
        // GENERADOR HTML COMPATIBLE MICROSOFT WORD — PASO 3 (EVALUACIÓN DETALLADA)
        // =========================================================================
        function generateEvaluacionDetalladaWordHtml() {
            const puesto = @json($selectedMeasurement ? $selectedMeasurement->puesto_trabajo : ($anexo2Data['puesto_trabajo'] ?? 'Gerente general'));
            const area = @json($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Administración') : ($anexo2Data['area_sector'] ?? 'Administración'));
            const trabajador = @json($selectedMeasurement ? (is_array($selectedMeasurement->nombres_trabajadores) ? implode(', ', array_filter($selectedMeasurement->nombres_trabajadores)) : ($selectedMeasurement->nombres_trabajadores ?: ($anexo2Data['nombre_trabajador'] ?? 'Carlos Rene Ichuta Ichuta'))) : ($anexo2Data['nombre_trabajador'] ?? 'Carlos Rene Ichuta Ichuta'));
            const fecha = @json($selectedMeasurement ? ($selectedMeasurement->date_formatted ?: ($selectedMeasurement->date ?: date('d/m/Y'))) : ($anexo2Data['profesional_fecha'] ?: date('d/m/Y')));
            const actividad = @json($selectedMeasurement ? ($selectedMeasurement->factor_riesgo ?: ($anexo2Data['actividad_principal'] ?? 'Gestionar y gerentar el trabajo del personal')) : 'Gestionar y gerentar el trabajo del personal');
            const tiempo = parseInt(@json($selectedMeasurement ? ($selectedMeasurement->tiempo_exposicion_horas ?: 8) : 8), 10) || 8;

            const scoreA = @json($rosaScores['score_a_tiempo']);
            const scoreB = @json($rosaScores['score_b']);
            const scoreC = @json($rosaScores['score_c']);
            const scoreD = @json($rosaScores['score_d']);
            const scoreFinal = @json($rosaScores['score_final']);

            const riskLevel = (scoreFinal <= 2) ? 'Inapreciable' : ((scoreFinal <= 4) ? 'Bajo' : ((scoreFinal <= 6) ? 'Medio' : ((scoreFinal <= 8) ? 'Alto' : 'Muy alto')));
            const actionRequired = (scoreFinal <= 2) ? 'No requiere actuación inmediata' : ((scoreFinal <= 4) ? 'Puede requerir mejoras básicas' : ((scoreFinal <= 6) ? 'Requiere intervención ergonómica' : ((scoreFinal <= 8) ? 'Requiere intervención en corto plazo' : 'Requiere intervención prioritaria')));

            const elAT = document.getElementById('doc_val_analisis_tecnico_narrative');
            const elObs = document.getElementById('doc_val_observaciones_narrative');
            const elRec = document.getElementById('doc_val_recomendaciones_narrative');

            const analisisTecnico = (elAT ? elAT.innerText.trim() : '') || (document.getElementById('analisis_tecnico_input') ? document.getElementById('analisis_tecnico_input').value : '') || @json($promptsData['analisis_tecnico'] ?? '');
            const observaciones = (elObs ? elObs.innerText.trim() : '') || (document.getElementById('observaciones_input') ? document.getElementById('observaciones_input').value : '') || @json($promptsData['observaciones'] ?? '');
            const recomendaciones = (elRec ? elRec.innerText.trim() : '') || (document.getElementById('recomendaciones_input') ? document.getElementById('recomendaciones_input').value : '') || @json($promptsData['recomendaciones'] ?? '');

            const profesionalNombre = document.getElementById('step3_syso_nombre')?.value || document.getElementById('step1_syso_nombre')?.value || @json($anexo2Data['profesional_nombre'] ?? '');
            const profesionalRegistro = document.getElementById('step3_syso_reg')?.value || document.getElementById('step1_syso_reg')?.value || @json($anexo2Data['profesional_registro'] ?? '');
            const profesionalFecha = document.getElementById('step3_syso_fecha')?.value || document.getElementById('step1_syso_fecha')?.value || @json($anexo2Data['profesional_fecha'] ?? '');

            const finalScoreBg = (scoreFinal >= 5) ? '#ff0000' : ((scoreFinal >= 3) ? '#ffff00' : '#00b050');
            const finalScoreColor = (scoreFinal >= 5) ? '#ffffff' : '#000000';

            return `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<meta name="ProgId" content="Word.Document">
<meta name="Generator" content="Microsoft Word 15">
<meta name="Originator" content="Microsoft Word 15">
<!--[if gte mso 9]>
<xml>
  <w:WordDocument>
    <w:View>Print</w:View>
    <w:Zoom>100</w:Zoom>
    <w:DoNotOptimizeForBrowser/>
  </w:WordDocument>
</xml>
<![endif]-->
<style>
  @page WordSection1 {
    size: 612.0pt 792.0pt;
    margin: 36.0pt 36.0pt 36.0pt 36.0pt;
    mso-header-margin: 0pt;
    mso-footer-margin: 0pt;
    mso-paper-source: 0;
  }
  div.WordSection1 { page: WordSection1; }
  body {
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
    color: #000000;
    margin: 0;
    padding: 0;
  }
  table {
    border-collapse: collapse;
    mso-table-layout-alt: fixed;
    border: 1.0pt solid #000000;
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
  }
  th, td {
    border: 1.0pt solid #000000;
    padding: 2.0pt 4.0pt;
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
    color: #000000;
  }
  .sec-title {
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
    font-weight: bold;
    margin-top: 6.0pt;
    margin-bottom: 2.0pt;
  }
  .th-blue {
    background-color: #deebf7;
    background: #deebf7;
    font-weight: bold;
  }
  p.MsoNormal {
    margin: 0cm;
    margin-bottom: 0pt;
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
  }
</style>
</head>
<body>
<div class="WordSection1">

  <!-- Header Box -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; border: 1.0pt solid #000000; margin-bottom: 6pt;">
    <tr>
      <td align="center" style="padding: 4pt 6pt; text-align: center; border: 1.0pt solid #000000;">
        <p class="MsoNormal" align="center" style="text-align: center; font-weight: bold; font-size: 9.5pt; text-transform: uppercase;">
          REGISTRO DE EVALUACIÓN DETALLADA DE RIESGOS DISERGONÓMICOS<br>
          MEDIANTE EL MÉTODO ROSA EN PUESTOS ADMINISTRATIVOS
        </p>
      </td>
    </tr>
  </table>

  <!-- 1. Identificación del puesto evaluado -->
  <p class="sec-title">1. Identificación del puesto evaluado</p>
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 6pt;">
    <tr class="th-blue">
      <th width="48%" align="left" style="width: 48%; text-align: left; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Ítem</th>
      <th width="52%" align="left" style="width: 52%; text-align: left; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Descripción</th>
    </tr>
    <tr>
      <td style="padding: 2pt 4pt;">Área / Sector:</td>
      <td style="padding: 2pt 4pt;">${escapeHtml(area)}</td>
    </tr>
    <tr>
      <td style="padding: 2pt 4pt;">Puesto de trabajo:</td>
      <td style="padding: 2pt 4pt;">${escapeHtml(puesto)}</td>
    </tr>
    <tr>
      <td style="padding: 2pt 4pt;">Trabajador evaluado:</td>
      <td style="padding: 2pt 4pt;">${escapeHtml(trabajador)}</td>
    </tr>
    <tr>
      <td style="padding: 2pt 4pt;">Fecha de evaluación:</td>
      <td style="padding: 2pt 4pt;">${escapeHtml(fecha)}</td>
    </tr>
    <tr>
      <td style="padding: 2pt 4pt;">Actividad principal:</td>
      <td style="padding: 2pt 4pt;">${escapeHtml(actividad)}</td>
    </tr>
    <tr>
      <td style="padding: 2pt 4pt;">Tiempo de exposición:</td>
      <td style="padding: 2pt 4pt;">${escapeHtml(tiempo)}</td>
    </tr>
  </table>

  <!-- 2. Resultados del Método ROSA por puesto -->
  <p class="sec-title">2. Resultados del Método ROSA por puesto</p>
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 6pt;">
    <tr class="th-blue">
      <th width="16%" align="center" style="width: 16%; text-align: center; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Grupo evaluado</th>
      <th width="58%" align="left" style="width: 58%; text-align: left; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Descripción</th>
      <th width="26%" align="center" style="width: 26%; text-align: center; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Puntaje obtenido</th>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Tabla A</td>
      <td style="padding: 2pt 4pt;">Silla de trabajo: altura, profundidad, reposabrazos, respaldo y duración</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreA}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Tabla B</td>
      <td style="padding: 2pt 4pt;">Teléfono y pantalla</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreB}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Tabla C</td>
      <td style="padding: 2pt 4pt;">Ratón y teclado</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreC}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Tabla D</td>
      <td style="padding: 2pt 4pt;">Pantalla y periféricos</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreD}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">Tabla E</td>
      <td style="font-weight: bold; padding: 2pt 4pt;">Resultado final ROSA</td>
      <td align="center" style="text-align: center; font-weight: bold; background-color: ${finalScoreBg}; color: ${finalScoreColor}; padding: 2pt 4pt;">${scoreFinal}</td>
    </tr>
  </table>

  <!-- 3. Interpretación del nivel de riesgo -->
  <p class="sec-title">3. Interpretación del nivel de riesgo</p>
  <table width="82%" align="center" border="1" cellspacing="0" cellpadding="0" style="width: 82%; margin: 0 auto; margin-bottom: 6pt;">
    <tr>
      <th width="22%" align="center" style="width: 22%; text-align: center; font-weight: bold; padding: 2pt 4pt; background-color: #ffffff;">Puntaje final ROSA</th>
      <th width="22%" align="center" style="width: 22%; text-align: center; font-weight: bold; padding: 2pt 4pt; background-color: #ffffff;">Nivel de riesgo</th>
      <th width="56%" align="center" style="width: 56%; text-align: center; font-weight: bold; padding: 2pt 4pt; background-color: #ffffff;">Acción requerida</th>
    </tr>
    <tr style="background-color: #fff2cc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">1 a 2</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Inapreciable</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">No requiere actuación inmediata</td>
    </tr>
    <tr style="background-color: #fce4d6;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">3 a 4</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Bajo</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Puede requerir mejoras básicas</td>
    </tr>
    <tr style="background-color: #f8cecc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">5 a 6</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Medio</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Requiere intervención ergonómica</td>
    </tr>
    <tr style="background-color: #f8cecc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">7 a 8</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Alto</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Requiere intervención en corto plazo</td>
    </tr>
    <tr style="background-color: #f8cecc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">9 a 10</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Muy alto</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Requiere intervención prioritaria</td>
    </tr>
  </table>

  <!-- 4. Análisis técnico del puesto -->
  <p class="sec-title">4. Análisis técnico del puesto</p>
  ${analisisTecnico ? `<p class="MsoNormal" style="margin-top: 2pt; margin-bottom: 4pt; text-align: justify; line-height: 1.25;">${escapeHtml(analisisTecnico)}</p>` : ''}
  <table width="75%" align="center" border="0" cellspacing="0" cellpadding="0" style="width: 75%; margin: 4pt auto 6pt auto; border: none;">
    <tr>
      <td width="38%" style="border: none; padding: 1pt 0;">Puntuación final ROSA de:</td>
      <td width="62%" style="border: none; padding: 1pt 0; font-weight: bold;">${scoreFinal}</td>
    </tr>
    <tr>
      <td width="38%" style="border: none; padding: 1pt 0;">Nivel de riesgo:</td>
      <td width="62%" style="border: none; padding: 1pt 0; font-weight: bold;">${riskLevel}</td>
    </tr>
    <tr>
      <td width="38%" style="border: none; padding: 1pt 0;">Este resultado indica que:</td>
      <td width="62%" style="border: none; padding: 1pt 0; font-weight: bold;">${actionRequired}</td>
    </tr>
  </table>

  <!-- 5. Observaciones encontradas -->
  <p class="sec-title">5. Observaciones encontradas</p>
  <p class="MsoNormal" style="margin-top: 2pt; margin-bottom: 4pt; text-align: justify; line-height: 1.25;">
    ${observaciones ? formatWordBullets(observaciones) : ''}
  </p>

  <!-- 6. Recomendaciones por puesto -->
  <p class="sec-title">6. Recomendaciones por puesto</p>
  <p class="MsoNormal" style="margin-top: 2pt; margin-bottom: 6pt; text-align: justify; line-height: 1.25;">
    ${recomendaciones ? formatWordBullets(recomendaciones) : ''}
  </p>

  <!-- Tabla SySO -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 8pt; margin-bottom: 8pt; mso-para-margin-top: 8pt; mso-para-margin-bottom: 8pt; font-size: 8.0pt; line-height: 8.0pt;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt;">&nbsp;</span></p>
  <table width="60%" align="center" border="1" cellspacing="0" cellpadding="0" style="width: 60%; margin: 0 auto;">
    <tr class="th-blue">
      <th colspan="2" align="center" style="text-align: center; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt; text-transform: uppercase;">
        PROFESIONAL CON REGISTRO SySO vigente
      </th>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">Nombre:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalNombre)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">N° Registro:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalRegistro)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">Fecha:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalFecha)}</td>
    </tr>
    <tr style="height: 36pt;">
      <td width="32%" style="width: 32%; font-weight: bold; vertical-align: top; padding: 2pt 4pt; text-align: left;">Firma:</td>
      <td width="68%" style="width: 68%; height: 36pt; text-align: center;">&nbsp;</td>
    </tr>
  </table>

</div>
</body>
</html>`;
        }

        // Formateador de viñetas para Word HTML
        function formatWordBullets(text) {
            if (!text) return '';
            let cleaned = text.replace(/^\s*[\*\-]\s*/gm, '• ');
            cleaned = cleaned.replace(/\s+[\*]\s+/g, '\n• ');
            cleaned = cleaned.replace(/\s+•\s+/g, '\n• ');
            return cleaned.split('\n')
                .map(l => l.trim())
                .filter(l => l.length > 0)
                .map(l => escapeHtml(l.startsWith('•') ? l : '• ' + l))
                .join('<br>');
        }

        // Formateador de viñetas limpias para texto plano
        function formatCleanBullets(text) {
            if (!text) return '';
            let cleaned = text.replace(/^\s*[\*\-]\s*/gm, '• ');
            cleaned = cleaned.replace(/\s+[\*]\s+/g, '\n• ');
            cleaned = cleaned.replace(/\s+•\s+/g, '\n• ');
            const lines = cleaned.split('\n').map(l => l.trim()).filter(l => l.length > 0);
            return lines.map(l => l.startsWith('•') ? l : '• ' + l).join('\n');
        }

        // Copiar Evaluación Detallada al portapapeles para Word
        function copyEvaluacionDetalladaToClipboard() {
            const html = generateEvaluacionDetalladaWordHtml();
            copyHtmlToClipboardUniversal(html, '¡Registro de Evaluación Detallada copiado! Listo para pegar en Word (Ctrl + V).');
        }

        // Descargar Evaluación Detallada como documento Word (.doc)
        function downloadEvaluacionDetalladaDoc() {
            const elAnalisis = document.getElementById('doc_val_analisis_tecnico_narrative');
            const elObs = document.getElementById('doc_val_observaciones_narrative');
            const elRec = document.getElementById('doc_val_recomendaciones_narrative');

            const hasContent = (elAnalisis && elAnalisis.textContent.trim().length > 0) ||
                               (elObs && elObs.textContent.trim().length > 0) ||
                               (elRec && elRec.textContent.trim().length > 0);

            if (!hasContent) {
                showActionToast('Primero debes generar el contenido con Gemini IA antes de descargar el documento Word.', 'warning');
                return;
            }

            const html = generateEvaluacionDetalladaWordHtml();
            const blob = new Blob(['\ufeff' + html], {
                type: 'application/msword;charset=utf-8'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Registro_Evaluacion_Detallada_Metodo_ROSA.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Evaluación Detallada ROSA para Word.');
        }

        // =========================================================================
        // AUTO-GUARDADO: CAMPOS PROFESIONAL SySO Y NARRATIVAS EDITABLES
        // =========================================================================
        let sysoAutoSaveTimer = null;
        function autoSaveSysoField(key, value) {
            // Sincronizar en vivo entre Step 1, Step 3, Step 4 y Step 5
            if (key === 'profesional_nombre') {
                ['step1_syso_nombre', 'step3_syso_nombre', 'step4_syso_nombre', 'step5_syso_nombre'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.value !== value) el.value = value;
                });
                if (typeof anexo2HeaderData !== 'undefined') anexo2HeaderData.profesional_nombre = value;
            } else if (key === 'profesional_registro') {
                ['step1_syso_reg', 'step3_syso_reg', 'step4_syso_reg', 'step5_syso_reg'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.value !== value) el.value = value;
                });
                if (typeof anexo2HeaderData !== 'undefined') anexo2HeaderData.profesional_registro = value;
            } else if (key === 'profesional_fecha') {
                ['step1_syso_fecha', 'step3_syso_fecha', 'step4_syso_fecha', 'step5_syso_fecha'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.value !== value) el.value = value;
                });
                if (typeof anexo2HeaderData !== 'undefined') anexo2HeaderData.profesional_fecha = value;
            }

            clearTimeout(sysoAutoSaveTimer);
            sysoAutoSaveTimer = setTimeout(async () => {
                const nom = document.getElementById('step5_syso_nombre')?.value || document.getElementById('step4_syso_nombre')?.value || document.getElementById('step3_syso_nombre')?.value || document.getElementById('step1_syso_nombre')?.value || '';
                const reg = document.getElementById('step5_syso_reg')?.value || document.getElementById('step4_syso_reg')?.value || document.getElementById('step3_syso_reg')?.value || document.getElementById('step1_syso_reg')?.value || '';
                const fec = document.getElementById('step5_syso_fecha')?.value || document.getElementById('step4_syso_fecha')?.value || document.getElementById('step3_syso_fecha')?.value || document.getElementById('step1_syso_fecha')?.value || '';

                if (typeof anexo2HeaderData !== 'undefined') {
                    anexo2HeaderData.profesional_nombre = nom;
                    anexo2HeaderData.profesional_registro = reg;
                    anexo2HeaderData.profesional_fecha = fec;
                }

                const payload = {
                    anexo2_data: {
                        profesional_nombre: nom,
                        profesional_registro: reg,
                        profesional_fecha: fec,
                    }
                };

                try {
                    const resp = await fetch("{{ route('modules.ergonomia_rosa.anexo2.save', $module->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await resp.json();
                    if (data.success) {
                        showActionToast('¡Datos del profesional SySO guardados!');
                    }
                } catch (err) {
                    console.error('Error auto-saving SySO fields:', err);
                }
            }, 600);
        }

        // =========================================================================
        // PASO 4: GENERACIÓN Y DESCARGA DE WORD - REGISTRO Nº 3
        // =========================================================================
        function generateRegistro3WordHtml() {
            const razonSocial = document.getElementById('step4_val_razon_social')?.innerText.trim() || @json($companyName ?? 'SURGE SRL - CASA MATRIZ SENKATA');
            const direccion = document.getElementById('step4_val_direccion')?.innerText.trim() || @json($companyAddress ?? '25 de Julio Alejandria Nº 8345 UV Edificio');
            const area = document.getElementById('step4_val_area')?.innerText.trim() || 'Administración';
            const puesto = document.getElementById('step4_val_puesto')?.innerText.trim() || 'Gerente general';
            const tarea = document.getElementById('step4_val_tarea')?.innerText.trim() || 'Tarea 1';
            const trabajador = document.getElementById('step4_val_trabajador')?.innerText.trim() || 'Carlos Rene Ichuta Ichuta';

            function formatDocMultiline(val) {
                if (!val) return '';
                return escapeHtml(val).replace(/\r\n/g, '<br>').replace(/\n/g, '<br>');
            }

            // Filas Medidas Preventivas Generales
            const genRows = [];
            const genTrs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-gen-row]');
            genTrs.forEach((tr, idx) => {
                const g = tr.getAttribute('data-step4-gen-row') || (idx + 1);
                const med = document.getElementById('step4_gen_medida_' + g)?.value || '';
                const fecha = document.getElementById('step4_gen_fecha_' + g)?.value || '';
                const si = document.getElementById('step4_gen_si_' + g)?.checked ? '☒' : '☐';
                const no = document.getElementById('step4_gen_no_' + g)?.checked ? '☒' : '☐';
                const obs = document.getElementById('step4_gen_obs_' + g)?.value || '';
                genRows.push({ num: idx + 1, med, fecha, si, no, obs });
            });

            let genRowsHtml = '';
            genRows.forEach(r => {
                genRowsHtml += `<tr>
      <td align="center" style="text-align: center; font-weight: bold;">${r.num}</td>
      <td style="text-align: left; line-height: 1.15;">${formatDocMultiline(r.med)}</td>
      <td align="center" style="text-align: center;">${escapeHtml(r.fecha)}</td>
      <td align="center" style="text-align: center; font-size: 11pt;">${r.si}</td>
      <td align="center" style="text-align: center; font-size: 11pt;">${r.no}</td>
      <td>${formatDocMultiline(r.obs)}</td>
    </tr>`;
            });

            // Filas Medidas Específicas
            const espRows = [];
            const espTrs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-esp-row]');
            espTrs.forEach((tr, idx) => {
                const e = tr.getAttribute('data-step4-esp-row') || (idx + 1);
                const med = document.getElementById('step4_esp_medida_' + e)?.value || '';
                const obs = document.getElementById('step4_esp_obs_' + e)?.value || '';
                espRows.push({ num: idx + 1, med, obs });
            });

            let espRowsHtml = '';
            espRows.forEach(r => {
                espRowsHtml += `<tr>
      <td align="center" style="text-align: center; font-weight: bold;">${r.num}</td>
      <td colspan="4">${formatDocMultiline(r.med)}</td>
      <td>${formatDocMultiline(r.obs)}</td>
    </tr>`;
            });

            const observacionesGenerales = document.getElementById('step4_observaciones_box')?.value || '';

            const profesionalNombre = document.getElementById('step4_syso_nombre')?.value || document.getElementById('step1_syso_nombre')?.value || @json($anexo2Data['profesional_nombre'] ?? '');
            const profesionalRegistro = document.getElementById('step4_syso_reg')?.value || document.getElementById('step1_syso_reg')?.value || @json($anexo2Data['profesional_registro'] ?? '');
            const profesionalFecha = document.getElementById('step4_syso_fecha')?.value || document.getElementById('step1_syso_fecha')?.value || @json($anexo2Data['profesional_fecha'] ?? '');

            return `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<meta name="ProgId" content="Word.Document">
<meta name="Generator" content="Microsoft Word 15">
<meta name="Originator" content="Microsoft Word 15">
<!--[if gte mso 9]>
<xml>
  <w:WordDocument>
    <w:View>Print</w:View>
    <w:Zoom>100</w:Zoom>
    <w:DoNotOptimizeForBrowser/>
  </w:WordDocument>
</xml>
<![endif]-->
<style>
  @page WordSection1 {
    size: 612.0pt 792.0pt;
    margin: 36.0pt 36.0pt 36.0pt 36.0pt;
    mso-header-margin: 0pt;
    mso-footer-margin: 0pt;
    mso-paper-source: 0;
  }
  div.WordSection1 { page: WordSection1; }
  body {
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
    color: #000000;
    margin: 0;
    padding: 0;
  }
  table {
    border-collapse: collapse;
    mso-table-layout-alt: fixed;
    border: 1.0pt solid #000000;
    font-family: Arial, sans-serif;
    font-size: 8.5pt;
  }
  th, td {
    border: 1.0pt solid #000000;
    padding: 2.5pt 4.0pt;
    font-family: Arial, sans-serif;
    font-size: 8.5pt;
    color: #000000;
  }
  .th-blue {
    background-color: #deebf7;
    background: #deebf7;
    font-weight: bold;
    text-align: center;
  }
  p.MsoNormal {
    margin: 0cm;
    margin-bottom: 0pt;
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
  }
</style>
</head>
<body>
<div class="WordSection1">

  <!-- Header Box -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; border: 1.0pt solid #000000;">
    <tr>
      <td align="center" style="padding: 4pt 6pt; text-align: center; border: 1.0pt solid #000000;">
        <p class="MsoNormal" align="center" style="text-align: center; font-weight: bold; font-size: 9.5pt; text-transform: uppercase;">
          REGISTRO Nº 3: IDENTIFICACIÓN DE MEDIDAS CORRECTIVAS Y PREVENTIVAS
        </p>
      </td>
    </tr>
  </table>

  <!-- Separador / Espacio entre tablas para Word -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 6pt; margin-bottom: 6pt; mso-para-margin-top: 6pt; mso-para-margin-bottom: 6pt; font-size: 6.0pt; line-height: 6.0pt;"><span style="font-family: Arial, sans-serif; font-size: 6.0pt;">&nbsp;</span></p>

  <!-- 1. Tabla de Identificación -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%;">
    <tr>
      <td width="26%" style="width: 26%;">Razón Social:</td>
      <td width="38%" style="width: 38%; font-weight: bold;">${escapeHtml(razonSocial)}</td>
      <td width="36%" align="center" style="width: 36%; text-align: center;">Nombre/s del trabajador/es:</td>
    </tr>
    <tr>
      <td width="26%" style="width: 26%;">Dirección de la empresa o establecimiento:</td>
      <td width="38%" style="width: 38%; font-weight: bold;">${escapeHtml(direccion)}</td>
      <td width="36%" rowspan="4" align="center" valign="middle" style="width: 36%; text-align: center; vertical-align: middle; font-weight: bold; font-size: 9.0pt;">${escapeHtml(trabajador)}</td>
    </tr>
    <tr>
      <td width="26%" style="width: 26%;">Área y Sector en estudio:</td>
      <td width="38%" style="width: 38%; font-weight: bold;">${escapeHtml(area)}</td>
    </tr>
    <tr>
      <td width="26%" style="width: 26%;">Puesto de Trabajo:</td>
      <td width="38%" style="width: 38%; font-weight: bold;">${escapeHtml(puesto)}</td>
    </tr>
    <tr>
      <td width="26%" style="width: 26%;">Tarea analizada:</td>
      <td width="38%" style="width: 38%; font-weight: bold;">${escapeHtml(tarea)}</td>
    </tr>
  </table>

  <!-- Separador / Espacio entre tablas para Word -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 6pt; margin-bottom: 6pt; mso-para-margin-top: 6pt; mso-para-margin-bottom: 6pt; font-size: 6.0pt; line-height: 6.0pt;"><span style="font-family: Arial, sans-serif; font-size: 6.0pt;">&nbsp;</span></p>

  <!-- 2. Medidas Correctivas y Preventivas (M.C.P.) -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; table-layout: fixed;">
    <colgroup>
      <col width="5.5%" style="width: 5.5%;">
      <col width="54.5%" style="width: 54.5%;">
      <col width="13%" style="width: 13%;">
      <col width="5.5%" style="width: 5.5%;">
      <col width="5.5%" style="width: 5.5%;">
      <col width="16%" style="width: 16%;">
    </colgroup>
    <tr>
      <th colspan="6" align="center" style="text-align: center; background-color: #ffffff; font-weight: bold; padding: 3pt 4pt; font-size: 9.0pt;">
        Medidas Correctivas y Preventivas (M.C.P.)
      </th>
    </tr>
    <tr class="th-blue">
      <th align="center" style="text-align: center;">N°</th>
      <th align="center" style="text-align: center;">Medidas Preventivas Generales</th>
      <th align="center" style="text-align: center;">Fecha</th>
      <th align="center" style="text-align: center;">SI</th>
      <th align="center" style="text-align: center;">NO</th>
      <th align="center" style="text-align: center;">Observaciones</th>
    </tr>
    ${genRowsHtml}
    <tr class="th-blue">
      <th align="center" style="text-align: center;">N°</th>
      <th colspan="4" align="center" style="text-align: center;">Medidas Correctivas y Preventivas Específicas (Administrativas y de Ingeniería)</th>
      <th align="center" style="text-align: center;">Observaciones</th>
    </tr>
    ${espRowsHtml}
  </table>

  <!-- Separador / Espacio entre tablas para Word -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 6pt; margin-bottom: 6pt; mso-para-margin-top: 6pt; mso-para-margin-bottom: 6pt; font-size: 6.0pt; line-height: 6.0pt;"><span style="font-family: Arial, sans-serif; font-size: 6.0pt;">&nbsp;</span></p>

  <!-- 3. Observaciones Box -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%;">
    <tr>
      <td style="padding: 4pt 6pt; min-height: 36pt;">
        <p class="MsoNormal" style="font-weight: bold; margin-bottom: 2pt;">Observaciones:</p>
        <p class="MsoNormal" style="text-align: justify; line-height: 1.2;">${formatDocMultiline(observacionesGenerales)}</p>
      </td>
    </tr>
  </table>

  <!-- Separador / Espacio entre tablas para Word -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 8pt; margin-bottom: 8pt; mso-para-margin-top: 8pt; mso-para-margin-bottom: 8pt; font-size: 8.0pt; line-height: 8.0pt;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt;">&nbsp;</span></p>

  <!-- 4. Tabla SySO -->
  <table width="60%" align="center" border="1" cellspacing="0" cellpadding="0" style="width: 60%; margin: 0 auto;">
    <tr class="th-blue">
      <th colspan="2" align="center" style="text-align: center; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt; text-transform: uppercase;">
        PROFESIONAL CON REGISTRO SySO vigente
      </th>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">Nombre:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalNombre)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">N° Registro:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalRegistro)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">Fecha:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalFecha)}</td>
    </tr>
    <tr style="height: 36pt;">
      <td width="32%" style="width: 32%; font-weight: bold; vertical-align: top; padding: 2pt 4pt; text-align: left;">Firma:</td>
      <td width="68%" style="width: 68%; height: 36pt; text-align: center;">&nbsp;</td>
    </tr>
  </table>

</div>
</body>
</html>`;
        }

        function downloadRegistro3Doc() {
            const html = generateRegistro3WordHtml();
            const blob = new Blob(['\ufeff' + html], {
                type: 'application/msword;charset=utf-8'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Registro_3_Identificacion_Medidas_Correctivas_Preventivas.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Registro Nº 3 para Word.');
        }



        let inMemoryStep4Data = @json($anexo2Data['step4_data'] ?? []);
        if (Array.isArray(inMemoryStep4Data)) {
            let obj = {};
            inMemoryStep4Data.forEach((item, idx) => { if(item) obj[idx] = item; });
            inMemoryStep4Data = obj;
        }
        let step4AutoSaveTimer = null;
        function autoSaveStep4() {
            clearTimeout(step4AutoSaveTimer);
            const badge = document.getElementById('step4AutoSaveBadge');
            if (badge) {
                badge.style.opacity = '0.5';
                const s = badge.querySelector('span');
                if (s) s.textContent = 'Guardando...';
            }

            step4AutoSaveTimer = setTimeout(async () => {
                const measKey = '{{ $selectedMeasurement ? (string)$selectedMeasurement->id : "general" }}';
                const step4Obj = {
                    observaciones: document.getElementById('step4_observaciones_box')?.value || '',
                };

                const genTrs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-gen-row]');
                genTrs.forEach((tr, idx) => {
                    const g = tr.getAttribute('data-step4-gen-row') || (idx + 1);
                    step4Obj['gen_medida_' + g] = document.getElementById('step4_gen_medida_' + g)?.value || '';
                    step4Obj['gen_fecha_' + g] = document.getElementById('step4_gen_fecha_' + g)?.value || '';
                    step4Obj['gen_si_' + g] = document.getElementById('step4_gen_si_' + g)?.checked ? '1' : '0';
                    step4Obj['gen_no_' + g] = document.getElementById('step4_gen_no_' + g)?.checked ? '1' : '0';
                    step4Obj['gen_obs_' + g] = document.getElementById('step4_gen_obs_' + g)?.value || '';
                });

                const espTrs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-esp-row]');
                espTrs.forEach((tr, idx) => {
                    const e = tr.getAttribute('data-step4-esp-row') || (idx + 1);
                    step4Obj['esp_medida_' + e] = document.getElementById('step4_esp_medida_' + e)?.value || '';
                    step4Obj['esp_obs_' + e] = document.getElementById('step4_esp_obs_' + e)?.value || '';
                });

                if (Array.isArray(inMemoryStep4Data)) {
                    let obj = {};
                    inMemoryStep4Data.forEach((item, idx) => { if(item) obj[idx] = item; });
                    inMemoryStep4Data = obj;
                }
                inMemoryStep4Data[measKey] = step4Obj;

                const payload = {
                    anexo2_data: {
                        step4_data: inMemoryStep4Data
                    }
                };

                try {
                    const resp = await fetch("{{ route('modules.ergonomia_rosa.anexo2.save', $module->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await resp.json();
                    if (data.success) {
                        if (data.anexo2_data && data.anexo2_data.step4_data) {
                            inMemoryStep4Data = data.anexo2_data.step4_data;
                        }
                        if (badge) {
                            badge.style.opacity = '1';
                            const s = badge.querySelector('span');
                            if (s) s.textContent = 'Guardado';
                        }
                    }
                } catch (err) {
                    console.error('Error auto-saving Step 4:', err);
                }
            }, 400);
        }

        function addStep4GenRow() {
            const trs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-gen-row]');
            const newIdx = trs.length + 1;
            const headerEsp = document.getElementById('step4_header_esp');

            const newTr = document.createElement('tr');
            newTr.setAttribute('data-step4-gen-row', newIdx);
            newTr.innerHTML = `
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold; position: relative;">
                    <div class="table-row-actions-left"></div>
                    <span class="step4-gen-num">${newIdx}</span>
                </td>
                <td style="border: 1px solid #000000; padding: 1px 4px; vertical-align: top;">
                    <textarea id="step4_gen_medida_${newIdx}" class="syso-live-textarea" rows="2" style="font-size: 8.5pt;"
                        placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)"></textarea>
                </td>
                <td style="border: 1px solid #000000; padding: 2px 4px; text-align: center;">
                    <input type="text" id="step4_gen_fecha_${newIdx}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;"
                        placeholder="dd/mm/aaaa" oninput="autoSaveStep4()">
                </td>
                <td style="border: 1px solid #000000; padding: 2px; text-align: center;">
                    <input type="checkbox" id="step4_gen_si_${newIdx}" onchange="if(this.checked){ const noEl = document.getElementById('step4_gen_no_${newIdx}'); if(noEl) noEl.checked = false; } autoSaveStep4()" style="cursor: pointer; width: 15px; height: 15px; display: block; margin: 0 auto; accent-color: #0284c7;">
                </td>
                <td style="border: 1px solid #000000; padding: 2px; text-align: center;">
                    <input type="checkbox" id="step4_gen_no_${newIdx}" onchange="if(this.checked){ const siEl = document.getElementById('step4_gen_si_${newIdx}'); if(siEl) siEl.checked = false; } autoSaveStep4()" style="cursor: pointer; width: 15px; height: 15px; display: block; margin: 0 auto; accent-color: #0284c7;">
                </td>
                <td style="border: 1px solid #000000; padding: 1px 2px; vertical-align: top;">
                    <textarea id="step4_gen_obs_${newIdx}" class="syso-live-textarea" rows="1" style="font-size: 8.5pt;"
                        placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)"></textarea>
                </td>
            `;

            if (headerEsp && headerEsp.parentNode) {
                headerEsp.parentNode.insertBefore(newTr, headerEsp);
            } else {
                document.getElementById('step4_mcp_tbody').appendChild(newTr);
            }

            refreshStep4GenRowButtons();
            document.getElementById('step4_gen_medida_' + newIdx)?.focus();
            autoSaveStep4();
        }

        function removeStep4GenRow(idx) {
            const allTrs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-gen-row]');
            if (allTrs.length <= 1) {
                showActionToast('Debe existir al menos una fila.');
                return;
            }
            const tr = document.querySelector(`#step4_mcp_tbody tr[data-step4-gen-row="${idx}"]`);
            if (tr) {
                tr.remove();
            }
            const trs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-gen-row]');
            trs.forEach((row, i) => {
                const curNum = i + 1;
                row.setAttribute('data-step4-gen-row', curNum);
                const numSpan = row.querySelector('.step4-gen-num');
                if (numSpan) numSpan.textContent = curNum;

                const medEl = row.querySelector('textarea[id^="step4_gen_medida_"]');
                if (medEl) medEl.id = 'step4_gen_medida_' + curNum;

                const fEl = row.querySelector('input[id^="step4_gen_fecha_"]');
                if (fEl) fEl.id = 'step4_gen_fecha_' + curNum;

                const siEl = row.querySelector('input[id^="step4_gen_si_"]');
                if (siEl) {
                    siEl.id = 'step4_gen_si_' + curNum;
                    siEl.onchange = function() {
                        if (this.checked) {
                            const noEl = document.getElementById('step4_gen_no_' + curNum);
                            if (noEl) noEl.checked = false;
                        }
                        autoSaveStep4();
                    };
                }

                const noEl = row.querySelector('input[id^="step4_gen_no_"]');
                if (noEl) {
                    noEl.id = 'step4_gen_no_' + curNum;
                    noEl.onchange = function() {
                        if (this.checked) {
                            const siEl = document.getElementById('step4_gen_si_' + curNum);
                            if (siEl) siEl.checked = false;
                        }
                        autoSaveStep4();
                    };
                }

                const obsEl = row.querySelector('textarea[id^="step4_gen_obs_"]');
                if (obsEl) obsEl.id = 'step4_gen_obs_' + curNum;
            });

            refreshStep4GenRowButtons();
            autoSaveStep4();
        }

        function refreshStep4GenRowButtons() {
            const trs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-gen-row]');
            trs.forEach((tr, i) => {
                const curNum = i + 1;
                let container = tr.querySelector('.table-row-actions-left');
                if (!container) {
                    container = document.createElement('div');
                    container.className = 'table-row-actions-left';
                    tr.querySelector('td:first-child').prepend(container);
                }
                container.innerHTML = '';

                const btnDel = document.createElement('button');
                btnDel.type = 'button';
                btnDel.className = 'btn-del-table-row';
                btnDel.innerHTML = '&times;';
                btnDel.title = 'Eliminar fila';
                btnDel.onclick = () => removeStep4GenRow(curNum);
                container.appendChild(btnDel);

                if (i === trs.length - 1) {
                    const btnAdd = document.createElement('button');
                    btnAdd.type = 'button';
                    btnAdd.className = 'btn-add-table-row';
                    btnAdd.id = 'btnAddStep4Gen';
                    btnAdd.textContent = '+';
                    btnAdd.title = 'Añadir fila';
                    btnAdd.onclick = () => addStep4GenRow();
                    container.appendChild(btnAdd);
                }
            });
        }

        function addStep4EspRow() {
            const trs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-esp-row]');
            const newIdx = trs.length + 1;

            const newTr = document.createElement('tr');
            newTr.setAttribute('data-step4-esp-row', newIdx);
            newTr.innerHTML = `
                <td style="border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold; position: relative;">
                    <div class="table-row-actions-left"></div>
                    <span class="step4-esp-num">${newIdx}</span>
                </td>
                <td colspan="4" style="border: 1px solid #000000; padding: 1px 4px; vertical-align: top;">
                    <textarea id="step4_esp_medida_${newIdx}" class="syso-live-textarea" rows="1" style="font-size: 8.5pt;"
                        placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)"></textarea>
                </td>
                <td style="border: 1px solid #000000; padding: 1px 4px; vertical-align: top;">
                    <textarea id="step4_esp_obs_${newIdx}" class="syso-live-textarea" rows="1" style="font-size: 8.5pt;"
                        placeholder="" oninput="autoExpandTextarea(this); autoSaveStep4()" onkeydown="handleTabKey(event, this)"></textarea>
                </td>
            `;

            document.getElementById('step4_mcp_tbody').appendChild(newTr);
            refreshStep4EspRowButtons();
            document.getElementById('step4_esp_medida_' + newIdx)?.focus();
            autoSaveStep4();
        }

        function removeStep4EspRow(idx) {
            const allTrs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-esp-row]');
            if (allTrs.length <= 1) {
                showActionToast('Debe existir al menos una fila.');
                return;
            }
            const tr = document.querySelector(`#step4_mcp_tbody tr[data-step4-esp-row="${idx}"]`);
            if (tr) {
                tr.remove();
            }
            const trs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-esp-row]');
            trs.forEach((row, i) => {
                const curNum = i + 1;
                row.setAttribute('data-step4-esp-row', curNum);
                const numSpan = row.querySelector('.step4-esp-num');
                if (numSpan) numSpan.textContent = curNum;

                const medEl = row.querySelector('textarea[id^="step4_esp_medida_"]');
                if (medEl) medEl.id = 'step4_esp_medida_' + curNum;

                const obsEl = row.querySelector('textarea[id^="step4_esp_obs_"]');
                if (obsEl) obsEl.id = 'step4_esp_obs_' + curNum;
            });

            refreshStep4EspRowButtons();
            autoSaveStep4();
        }

        function refreshStep4EspRowButtons() {
            const trs = document.querySelectorAll('#step4_mcp_tbody tr[data-step4-esp-row]');
            trs.forEach((tr, i) => {
                const curNum = i + 1;
                let container = tr.querySelector('.table-row-actions-left');
                if (!container) {
                    container = document.createElement('div');
                    container.className = 'table-row-actions-left';
                    tr.querySelector('td:first-child').prepend(container);
                }
                container.innerHTML = '';

                const btnDel = document.createElement('button');
                btnDel.type = 'button';
                btnDel.className = 'btn-del-table-row';
                btnDel.innerHTML = '&times;';
                btnDel.title = 'Eliminar fila';
                btnDel.onclick = () => removeStep4EspRow(curNum);
                container.appendChild(btnDel);

                if (i === trs.length - 1) {
                    const btnAdd = document.createElement('button');
                    btnAdd.type = 'button';
                    btnAdd.className = 'btn-add-table-row';
                    btnAdd.id = 'btnAddStep4Esp';
                    btnAdd.textContent = '+';
                    btnAdd.title = 'Añadir fila';
                    btnAdd.onclick = () => addStep4EspRow();
                    container.appendChild(btnAdd);
                }
            });
        }

        function addStep5Row() {
            const tableEl = document.getElementById('step5_seguimiento_table');
            if (!tableEl) return;
            const trs = tableEl.querySelectorAll('tbody tr[data-row-id]');
            const nextIndex = trs.length;
            const newRowId = 'row_' + nextIndex;
            const rowNum = nextIndex + 1;

            const newTr = document.createElement('tr');
            newTr.setAttribute('data-row-id', newRowId);
            newTr.innerHTML = `
                <td style="width: 5%; border: 1px solid #000000; padding: 3px 2px; text-align: center; font-weight: bold; position: relative;">
                    <div class="table-row-actions-left"></div>
                    <span class="step5-row-num">${rowNum}</span>
                </td>
                <td style="width: 25%; border: 1px solid #000000; padding: 2px 4px;">
                    <input type="text" id="step5_puesto_${newRowId}" class="syso-live-input" value="" placeholder="..." oninput="autoSaveStep5()" style="font-size: 8.5pt;">
                </td>
                <td style="width: 13%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                    <input type="text" id="step5_fecha_eval_${newRowId}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="" placeholder="dd/mm/aaaa" oninput="autoSaveStep5()">
                </td>
                <td style="width: 13%; border: 1px solid #000000; padding: 2px 3px; text-align: center; font-weight: bold;">
                    <input type="text" id="step5_risk_${newRowId}" class="syso-live-input" style="text-align: center; font-weight: bold; font-size: 8.5pt;" value="" placeholder="..." oninput="autoSaveStep5()">
                </td>
                <td style="width: 16%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                    <input type="text" id="step5_f_admin_${newRowId}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="" placeholder="" oninput="autoSaveStep5()">
                </td>
                <td style="width: 16%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                    <input type="text" id="step5_f_ing_${newRowId}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="" placeholder="" oninput="autoSaveStep5()">
                </td>
                <td style="width: 12%; border: 1px solid #000000; padding: 2px 3px; text-align: center;">
                    <input type="text" id="step5_f_cierre_${newRowId}" class="syso-live-input" style="text-align: center; font-size: 8.5pt;" value="" placeholder="dd/mm/aaaa" oninput="autoSaveStep5()">
                </td>
            `;

            tableEl.querySelector('tbody').appendChild(newTr);
            refreshStep5RowButtons();
            document.getElementById('step5_puesto_' + newRowId)?.focus();
            autoSaveStep5();
        }

        function removeStep5Row(rowId) {
            const tableEl = document.getElementById('step5_seguimiento_table');
            if (!tableEl) return;
            const allTrs = tableEl.querySelectorAll('tbody tr[data-row-id]');
            if (allTrs.length <= 1) {
                showActionToast('Debe existir al menos una fila.');
                return;
            }
            const tr = tableEl.querySelector(`tbody tr[data-row-id="${rowId}"]`);
            if (tr) {
                tr.remove();
            }

            const trs = tableEl.querySelectorAll('tbody tr[data-row-id]');
            trs.forEach((row, i) => {
                const rowNum = i + 1;
                const numSpan = row.querySelector('.step5-row-num');
                if (numSpan) numSpan.textContent = rowNum;
            });

            refreshStep5RowButtons();
            autoSaveStep5();
        }

        function refreshStep5RowButtons() {
            const tableEl = document.getElementById('step5_seguimiento_table');
            if (!tableEl) return;
            const trs = tableEl.querySelectorAll('tbody tr[data-row-id]');

            trs.forEach((tr, i) => {
                const rowNum = i + 1;
                const rowId = tr.getAttribute('data-row-id');
                let container = tr.querySelector('.table-row-actions-left');
                if (!container) {
                    container = document.createElement('div');
                    container.className = 'table-row-actions-left';
                    tr.querySelector('td:first-child').prepend(container);
                }
                container.innerHTML = '';

                const btnDel = document.createElement('button');
                btnDel.type = 'button';
                btnDel.className = 'btn-del-table-row';
                btnDel.innerHTML = '&times;';
                btnDel.title = 'Eliminar fila';
                btnDel.onclick = () => removeStep5Row(rowId);
                container.appendChild(btnDel);

                if (i === trs.length - 1) {
                    const btnAdd = document.createElement('button');
                    btnAdd.type = 'button';
                    btnAdd.className = 'btn-add-table-row';
                    btnAdd.id = 'btnAddStep5Row';
                    btnAdd.textContent = '+';
                    btnAdd.title = 'Añadir fila';
                    btnAdd.onclick = () => addStep5Row();
                    container.appendChild(btnAdd);
                }
            });
        }

        let step5AutoSaveTimer = null;
        function autoSaveStep5() {
            clearTimeout(step5AutoSaveTimer);
            const badge = document.getElementById('step5AutoSaveBadge');
            if (badge) {
                badge.style.opacity = '0.5';
                const s = badge.querySelector('span');
                if (s) s.textContent = 'Guardando...';
            }

            step5AutoSaveTimer = setTimeout(async () => {
                const step5Obj = {};
                const tableEl = document.getElementById('step5_seguimiento_table');
                if (tableEl) {
                    const trs = tableEl.querySelectorAll('tbody tr[data-row-id]');
                    trs.forEach(tr => {
                        const rowId = tr.getAttribute('data-row-id');
                        const pInput = document.getElementById('step5_puesto_' + rowId);
                        const fInput = document.getElementById('step5_fecha_eval_' + rowId);
                        const rInput = document.getElementById('step5_risk_' + rowId);
                        const adminInput = document.getElementById('step5_f_admin_' + rowId);
                        const ingInput = document.getElementById('step5_f_ing_' + rowId);
                        const cierreInput = document.getElementById('step5_f_cierre_' + rowId);

                        if (pInput) step5Obj['puesto_' + rowId] = pInput.value;
                        if (fInput) step5Obj['fecha_eval_' + rowId] = fInput.value;
                        if (rInput) step5Obj['risk_' + rowId] = rInput.value;
                        if (adminInput) step5Obj['f_admin_' + rowId] = adminInput.value;
                        if (ingInput) step5Obj['f_ing_' + rowId] = ingInput.value;
                        if (cierreInput) step5Obj['f_cierre_' + rowId] = cierreInput.value;
                    });
                }

                const payload = {
                    anexo2_data: {
                        step5_data: step5Obj
                    }
                };

                try {
                    const resp = await fetch("{{ route('modules.ergonomia_rosa.anexo2.save', $module->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await resp.json();
                    if (data.success && badge) {
                        badge.style.opacity = '1';
                        const s = badge.querySelector('span');
                        if (s) s.textContent = 'Guardado';
                    }
                } catch (err) {
                    console.error('Error auto-saving Step 5:', err);
                }
            }, 400);
        }

        // =========================================================================
        // PASO 5: GENERACIÓN Y DESCARGA DE WORD - REGISTRO Nº 4
        // =========================================================================
        function generateRegistro4WordHtml() {
            const razonSocial = document.getElementById('step5_val_razon_social')?.innerText.trim() || @json($companyName ?? 'SURGE SRL - CASA MATRIZ SENKATA');
            const direccion = document.getElementById('step5_val_direccion')?.innerText.trim() || @json($companyAddress ?? '25 de Julio Alejandria Nº 8345 UV Edificio');
            const area = document.getElementById('step5_val_area')?.innerText.trim() || 'Administración';

            const rows = [];
            const tableEl = document.getElementById('step5_seguimiento_table');
            if (tableEl) {
                const trs = tableEl.querySelectorAll('tbody tr[data-row-id]');
                trs.forEach((tr, idx) => {
                    const rowId = tr.getAttribute('data-row-id');
                    const puesto = document.getElementById('step5_puesto_' + rowId)?.value || tr.children[1]?.innerText.trim() || '';
                    const fechaEval = document.getElementById('step5_fecha_eval_' + rowId)?.value || tr.children[2]?.innerText.trim() || '';
                    const risk = document.getElementById('step5_risk_' + rowId)?.value || tr.children[3]?.innerText.trim() || '';
                    const fAdmin = document.getElementById('step5_f_admin_' + rowId)?.value || '';
                    const fIng = document.getElementById('step5_f_ing_' + rowId)?.value || '';
                    const fCierre = document.getElementById('step5_f_cierre_' + rowId)?.value || '';

                    rows.push({
                        num: idx + 1,
                        puesto,
                        fechaEval,
                        risk,
                        fAdmin,
                        fIng,
                        fCierre
                    });
                });
            }

            let rowsHtml = '';
            rows.forEach(r => {
                rowsHtml += `<tr>
      <td align="center" style="text-align: center; font-weight: bold;">${r.num}</td>
      <td>${escapeHtml(r.puesto)}</td>
      <td align="center" style="text-align: center;">${escapeHtml(r.fechaEval)}</td>
      <td align="center" style="text-align: center; font-weight: bold;">${escapeHtml(r.risk)}</td>
      <td align="center" style="text-align: center;">${escapeHtml(r.fAdmin)}</td>
      <td align="center" style="text-align: center;">${escapeHtml(r.fIng)}</td>
      <td align="center" style="text-align: center;">${escapeHtml(r.fCierre)}</td>
    </tr>`;
            });

            const profesionalNombre = document.getElementById('step5_syso_nombre')?.value || document.getElementById('step1_syso_nombre')?.value || @json($anexo2Data['profesional_nombre'] ?? '');
            const profesionalRegistro = document.getElementById('step5_syso_reg')?.value || document.getElementById('step1_syso_reg')?.value || @json($anexo2Data['profesional_registro'] ?? '');
            const profesionalFecha = document.getElementById('step5_syso_fecha')?.value || document.getElementById('step1_syso_fecha')?.value || @json($anexo2Data['profesional_fecha'] ?? '');

            return `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office"
      xmlns:w="urn:schemas-microsoft-com:office:word"
      xmlns:m="http://schemas.microsoft.com/office/2004/12/omml"
      xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<meta name="ProgId" content="Word.Document">
<meta name="Generator" content="Microsoft Word 15">
<meta name="Originator" content="Microsoft Word 15">
<!--[if gte mso 9]>
<xml>
  <w:WordDocument>
    <w:View>Print</w:View>
    <w:Zoom>100</w:Zoom>
    <w:DoNotOptimizeForBrowser/>
  </w:WordDocument>
</xml>
<![endif]-->
<style>
  @page WordSection1 {
    size: 612.0pt 792.0pt;
    margin: 36.0pt 36.0pt 36.0pt 36.0pt;
    mso-header-margin: 0pt;
    mso-footer-margin: 0pt;
    mso-paper-source: 0;
  }
  div.WordSection1 { page: WordSection1; }
  body {
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
    color: #000000;
    margin: 0;
    padding: 0;
  }
  table {
    border-collapse: collapse;
    mso-table-layout-alt: fixed;
    border: 1.0pt solid #000000;
    font-family: Arial, sans-serif;
    font-size: 8.0pt;
  }
  th, td {
    border: 1.0pt solid #000000;
    padding: 2.5pt 3.5pt;
    font-family: Arial, sans-serif;
    font-size: 8.0pt;
    color: #000000;
  }
  .th-blue {
    background-color: #deebf7;
    background: #deebf7;
    font-weight: bold;
    text-align: center;
  }
  p.MsoNormal {
    margin: 0cm;
    margin-bottom: 0pt;
    font-family: Arial, sans-serif;
    font-size: 9.0pt;
  }
</style>
</head>
<body>
<div class="WordSection1">

  <!-- Header Box -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; border: 1.0pt solid #000000;">
    <tr>
      <td align="center" style="padding: 4pt 6pt; text-align: center; border: 1.0pt solid #000000;">
        <p class="MsoNormal" align="center" style="text-align: center; font-weight: bold; font-size: 9.5pt; text-transform: uppercase;">
          REGISTRO Nº 4: MATRIZ DE SEGUIMIENTO DE MEDIDAS PREVENTIVAS
        </p>
      </td>
    </tr>
  </table>

  <!-- Separador / Espacio entre tablas para Word -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 6pt; margin-bottom: 6pt; mso-para-margin-top: 6pt; mso-para-margin-bottom: 6pt; font-size: 6.0pt; line-height: 6.0pt;"><span style="font-family: Arial, sans-serif; font-size: 6.0pt;">&nbsp;</span></p>

  <!-- 1. Identificación -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; font-size: 8.5pt;">
    <tr>
      <td width="32%" style="width: 32%;">Razón Social:</td>
      <td width="68%" style="width: 68%; font-weight: bold;">${escapeHtml(razonSocial)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%;">Dirección de la empresa o establecimiento laboral:</td>
      <td width="68%" style="width: 68%; font-weight: bold;">${escapeHtml(direccion)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%;">Área y Sector en estudio:</td>
      <td width="68%" style="width: 68%; font-weight: bold;">${escapeHtml(area)}</td>
    </tr>
  </table>

  <!-- Separador / Espacio entre tablas para Word -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 6pt; margin-bottom: 6pt; mso-para-margin-top: 6pt; mso-para-margin-bottom: 6pt; font-size: 6.0pt; line-height: 6.0pt;"><span style="font-family: Arial, sans-serif; font-size: 6.0pt;">&nbsp;</span></p>

  <!-- 2. Matriz de Seguimiento -->
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%;">
    <tr class="th-blue">
      <th width="5%" align="center" style="width: 5%; text-align: center;">N°</th>
      <th width="25%" align="center" style="width: 25%; text-align: center;">Nombre del Puesto</th>
      <th width="13%" align="center" style="width: 13%; text-align: center;">Fecha de Evaluación</th>
      <th width="13%" align="center" style="width: 13%; text-align: center;">Nivel de riesgo</th>
      <th width="16%" align="center" style="width: 16%; text-align: center; line-height: 1.15;">Fecha de implementación de la Medida Administrativa</th>
      <th width="16%" align="center" style="width: 16%; text-align: center; line-height: 1.15;">Fecha de implementación de la Medida de Ingeniería</th>
      <th width="12%" align="center" style="width: 12%; text-align: center;">Fecha de Cierre</th>
    </tr>
    ${rowsHtml}
  </table>

  <!-- Separador / Espacio entre tablas para Word -->
  <p class="MsoNormal" style="margin: 0cm; margin-top: 8pt; margin-bottom: 8pt; mso-para-margin-top: 8pt; mso-para-margin-bottom: 8pt; font-size: 8.0pt; line-height: 8.0pt;"><span style="font-family: Arial, sans-serif; font-size: 8.0pt;">&nbsp;</span></p>

  <!-- 3. Tabla SySO -->
  <table width="65%" align="center" border="1" cellspacing="0" cellpadding="0" style="width: 65%; margin: 0 auto; font-size: 8.5pt;">
    <tr class="th-blue">
      <th colspan="2" align="center" style="text-align: center; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt; text-transform: uppercase;">
        PROFESIONAL CON REGISTRO SySO vigente
      </th>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">Nombre:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalNombre)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">N° Registro:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalRegistro)}</td>
    </tr>
    <tr>
      <td width="32%" style="width: 32%; font-weight: bold; padding: 2pt 4pt; text-align: left;">Fecha:</td>
      <td width="68%" style="width: 68%; padding: 2pt 4pt; text-align: left;">${escapeHtml(profesionalFecha)}</td>
    </tr>
    <tr style="height: 36pt;">
      <td width="32%" style="width: 32%; font-weight: bold; vertical-align: top; padding: 2pt 4pt; text-align: left;">Firma:</td>
      <td width="68%" style="width: 68%; height: 36pt; text-align: center;">&nbsp;</td>
    </tr>
  </table>

</div>
</body>
</html>`;
        }

        function downloadRegistro4Doc() {
            const html = generateRegistro4WordHtml();
            const blob = new Blob(['\ufeff' + html], {
                type: 'application/msword;charset=utf-8'
            });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Registro_4_Matriz_Seguimiento_Medidas_Preventivas.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Registro Nº 4 para Word.');
        }



        let narrativeAutoSaveTimer = null;
        function autoSaveNarrativeField(field, text) {
            if (field === 'analisis_tecnico') {
                const input = document.getElementById('analisis_tecnico_input');
                if (input) input.value = text;
            } else if (field === 'observaciones') {
                const input = document.getElementById('observaciones_input');
                if (input) input.value = text;
            } else if (field === 'recomendaciones') {
                const input = document.getElementById('recomendaciones_input');
                if (input) input.value = text;
            }

            // Comprobar estado del botón de descarga de Word
            const elAT = document.getElementById('doc_val_analisis_tecnico_narrative');
            const elObs = document.getElementById('doc_val_observaciones_narrative');
            const elRec = document.getElementById('doc_val_recomendaciones_narrative');
            const hasContent = (elAT && elAT.innerText.trim().length > 0) ||
                               (elObs && elObs.innerText.trim().length > 0) ||
                               (elRec && elRec.innerText.trim().length > 0);
            
            const btnWord = document.getElementById('btnDownloadStep3Word');
            if (btnWord) {
                if (hasContent) {
                    btnWord.disabled = false;
                    btnWord.style.opacity = '1';
                    btnWord.style.cursor = 'pointer';
                    btnWord.removeAttribute('disabled');
                } else {
                    btnWord.disabled = true;
                    btnWord.style.opacity = '0.5';
                    btnWord.style.cursor = 'not-allowed';
                    btnWord.setAttribute('disabled', 'disabled');
                }
            }

            clearTimeout(narrativeAutoSaveTimer);
            narrativeAutoSaveTimer = setTimeout(async () => {
                const payload = {
                    evaluation_id: '{{ $selectedMeasurement ? $selectedMeasurement->id : "" }}',
                    prompt: document.getElementById('prompt_input') ? document.getElementById('prompt_input').value : '',
                    analisis_tecnico: document.getElementById('doc_val_analisis_tecnico_narrative')?.innerText || '',
                    observaciones: document.getElementById('doc_val_observaciones_narrative')?.innerText || '',
                    recomendaciones: document.getElementById('doc_val_recomendaciones_narrative')?.innerText || '',
                };

                try {
                    await fetch("{{ route('modules.ergonomia_rosa.prompts.save', $module->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                } catch (err) {
                    console.error('Error auto-saving narrative field:', err);
                }
            }, 800);
        }

        // =========================================================================
        // CONTROLADOR DEL MODAL DE PROMPTS Y ANÁLISIS TÉCNICO ROSA
        // =========================================================================
        function resetDefaultRosaPrompt() {
            const puesto = @json($selectedMeasurement ? $selectedMeasurement->puesto_trabajo : 'Puesto de Trabajo');
            const scoreFinal = @json($rosaScores['score_final']);
            const scoreA = @json($rosaScores['score_a_tiempo']);
            const scoreB = @json($rosaScores['score_b']);
            const scoreC = @json($rosaScores['score_c']);
            const scoreD = @json($rosaScores['score_d']);
            const area = @json($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Administración') : 'Administración');
            const trabajador = @json($selectedMeasurement ? (is_array($selectedMeasurement->nombres_trabajadores) ? implode(', ', array_filter($selectedMeasurement->nombres_trabajadores)) : ($selectedMeasurement->nombres_trabajadores ?: 'Carlos Rene Ichuta Ichuta')) : 'Carlos Rene Ichuta Ichuta');
            const tiempo = parseInt(@json($selectedMeasurement ? ($selectedMeasurement->tiempo_exposicion_horas ?: 8) : 8), 10) || 8;

            const defaultPrompt = `Actúa como un especialista senior en Ergonomía Ocupacional y Salud en el Trabajo (SySO).
Realiza una evaluación biomecánica y ergonómica exhaustiva del puesto de trabajo '${puesto}' (Área: ${area}, Trabajador: ${trabajador}, Exposición: ${tiempo} hrs/día), evaluado mediante el método ROSA (Rapid Office Strain Assessment - ISO 9241 e ISO 11226) con los siguientes resultados normativos:

1. PUNTUACIÓN FINAL ROSA: ${scoreFinal}/10.
2. Puntuación Silla con factor tiempo (Tabla A): ${scoreA}.
3. Puntuación Teléfono y Pantalla (Tabla B): ${scoreB}.
4. Puntuación Ratón y Teclado (Tabla C): ${scoreC}.
5. Puntuación Pantalla y Periféricos (Tabla D): ${scoreD}.

Genera la redacción técnica especializada dividida obligatoriamente en los siguientes 3 apartados:
- '4. Análisis técnico del puesto': Explicación detallada de la carga postural estática y dinámica, ángulo visual hacia la pantalla, alineación de muñecas y soporte de espalda/asiento con base en las puntuaciones obtenidas (redactado en prosa técnica sin asteriscos ni viñetas).
- '5. Observaciones encontradas': Listado de hallazgos críticos observados en el puesto. Cada observación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).
- '6. Recomendaciones por puesto': Medidas ergonómicas correctivas, preventivas y administrativas prioritarias y viables. Cada recomendación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).`;

            document.getElementById('prompt_input').value = defaultPrompt;
            showActionToast('Plantilla de prompt cargada.');
        }

        function openRosaPromptsModal() {
            const modal = document.getElementById('rosaPromptsModal');
            if (modal) {
                modal.classList.add('open');
                modal.style.setProperty('display', 'flex', 'important');
                const promptInput = document.getElementById('prompt_input');
                if (promptInput && !promptInput.value.trim()) {
                    resetDefaultRosaPrompt();
                }
            }
        }

        function closeRosaPromptsModal() {
            const modal = document.getElementById('rosaPromptsModal');
            if (modal) {
                modal.classList.remove('open');
                modal.style.setProperty('display', 'none', 'important');
            }
        }

        function copyRosaPromptText() {
            const text = document.getElementById('prompt_input').value;
            if (!text.trim()) {
                showActionToast('No hay prompt para copiar', 'warning');
                return;
            }
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    showActionToast('¡Prompt copiado al portapapeles!');
                });
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                showActionToast('¡Prompt copiado al portapapeles!');
            }
        }

        async function saveRosaPrompts() {
            const btn = document.getElementById('btnSaveRosaPrompts');
            const btnText = document.getElementById('btnSaveRosaPromptsText');
            if (btn) btn.disabled = true;
            if (btnText) btnText.textContent = 'Guardando...';

            const payload = {
                evaluation_id: '{{ $selectedMeasurement ? $selectedMeasurement->id : "" }}',
                prompt: document.getElementById('prompt_input').value,
                analisis_tecnico: document.getElementById('analisis_tecnico_input').value,
                observaciones: document.getElementById('observaciones_input').value,
                recomendaciones: document.getElementById('recomendaciones_input').value,
            };

            try {
                const response = await fetch("{{ route('modules.ergonomia_rosa.prompts.save', $module->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success) {
                    showActionToast('¡Prompts y análisis técnico guardados correctamente!');
                    
                    // Actualizar en vivo los textos del Step 3 si están en pantalla
                    const elAnalisis = document.getElementById('doc_val_analisis_tecnico_narrative');
                    if (elAnalisis) elAnalisis.textContent = payload.analisis_tecnico;

                    const elObs = document.getElementById('doc_val_observaciones_narrative');
                    if (elObs) elObs.textContent = formatCleanBullets(payload.observaciones);

                    const elRec = document.getElementById('doc_val_recomendaciones_narrative');
                    if (elRec) elRec.textContent = formatCleanBullets(payload.recomendaciones);

                    // Habilitar botón de descarga de Word si hay contenido
                    const btnWord = document.getElementById('btnDownloadStep3Word');
                    if (btnWord && (payload.analisis_tecnico || payload.observaciones || payload.recomendaciones)) {
                        btnWord.disabled = false;
                        btnWord.style.opacity = '1';
                        btnWord.style.cursor = 'pointer';
                        btnWord.removeAttribute('disabled');
                    }

                    setTimeout(() => {
                        closeRosaPromptsModal();
                    }, 600);
                } else {
                    showActionToast('Error al guardar: ' + (data.message || 'Error desconocido'), 'error');
                }
            } catch (err) {
                console.error('Error saving rosa prompts:', err);
                showActionToast('Error al conectar con el servidor', 'error');
            } finally {
                if (btn) btn.disabled = false;
                if (btnText) btnText.textContent = 'Guardar Información';
            }
        }

        async function generateRosaContentWithAi() {
            const btnStep3 = document.getElementById('btnGenerateStep3Ai');
            const btnStep3Text = document.getElementById('btnGenerateStep3AiText');
            const promptInput = document.getElementById('prompt_input');
            const atInput = document.getElementById('analisis_tecnico_input');
            const obsInput = document.getElementById('observaciones_input');
            const recInput = document.getElementById('recomendaciones_input');

            if (promptInput && !promptInput.value.trim()) {
                resetDefaultRosaPrompt();
            }

            if (btnStep3) {
                btnStep3.disabled = true;
                btnStep3.style.opacity = '0.75';
            }
            if (btnStep3Text) btnStep3Text.innerHTML = '⚡ Generando con Gemini IA...';
            showActionToast('Conectando con Google Gemini IA para generar el contenido...', 'info');

            // SweetAlert2 centrado en progreso de generación
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Generando informe con Gemini IA',
                    html: `
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 12px; padding: 6px 0;">
                            <div style="font-size: 14px; color: #475569; line-height: 1.5; text-align: center;">
                                Procesando la evaluación biomecánica y redactando los <strong>Puntos 4, 5 y 6</strong> del Registro de Evaluación ROSA...
                            </div>
                            <div style="font-size: 12.5px; color: #0284c7; font-weight: 600;">
                                Por favor espera unos momentos ⚡
                            </div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            const payload = {
                evaluation_id: '{{ $selectedMeasurement ? $selectedMeasurement->id : "" }}',
                prompt: promptInput ? promptInput.value : '',
                prompt_analisis: atInput ? atInput.value : '',
                prompt_observaciones: obsInput ? obsInput.value : '',
                prompt_recomendaciones: recInput ? recInput.value : ''
            };

            try {
                const response = await fetch("{{ route('modules.ergonomia_rosa.generate-ai', $module->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                if (data.success && data.data) {
                    const cleanObs = formatCleanBullets(data.data.observaciones || '');
                    const cleanRec = formatCleanBullets(data.data.recomendaciones || '');

                    if (atInput) atInput.value = data.data.analisis_tecnico || '';
                    if (obsInput) obsInput.value = cleanObs;
                    if (recInput) recInput.value = cleanRec;

                    // Actualizar en vivo la vista previa del documento Step 3 con resaltado sutil
                    const elAnalisis = document.getElementById('doc_val_analisis_tecnico_narrative');
                    if (elAnalisis) {
                        elAnalisis.textContent = data.data.analisis_tecnico || '';
                        elAnalisis.style.transition = 'background-color 0.5s ease';
                        elAnalisis.style.backgroundColor = '#ecfdf5';
                        setTimeout(() => elAnalisis.style.backgroundColor = 'transparent', 1500);
                    }

                    const elObs = document.getElementById('doc_val_observaciones_narrative');
                    if (elObs) {
                        elObs.textContent = cleanObs;
                        elObs.style.transition = 'background-color 0.5s ease';
                        elObs.style.backgroundColor = '#ecfdf5';
                        setTimeout(() => elObs.style.backgroundColor = 'transparent', 1500);
                    }

                    const elRec = document.getElementById('doc_val_recomendaciones_narrative');
                    if (elRec) {
                        elRec.textContent = cleanRec;
                        elRec.style.transition = 'background-color 0.5s ease';
                        elRec.style.backgroundColor = '#ecfdf5';
                        setTimeout(() => elRec.style.backgroundColor = 'transparent', 1500);
                    }

                    // Habilitar el botón de descarga de Word
                    const btnWord = document.getElementById('btnDownloadStep3Word');
                    if (btnWord) {
                        btnWord.disabled = false;
                        btnWord.style.opacity = '1';
                        btnWord.style.cursor = 'pointer';
                        btnWord.removeAttribute('disabled');
                    }

                    showActionToast('¡Puntos 4, 5 y 6 generados exitosamente con Gemini IA!');

                    // SweetAlert2 Éxito Centrado
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Generación Completada con Éxito!',
                            html: `
                                <div style="font-size: 13.5px; color: #334155; line-height: 1.5; text-align: center;">
                                    Se redactaron y guardaron correctamente los apartados:<br><br>
                                    <span style="color: #0284c7; font-weight: 600;">• 4. Análisis técnico del puesto</span><br>
                                    <span style="color: #0284c7; font-weight: 600;">• 5. Observaciones encontradas</span><br>
                                    <span style="color: #0284c7; font-weight: 600;">• 6. Recomendaciones por puesto</span>
                                </div>
                            `,
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#0284c7',
                            customClass: {
                                confirmButton: 'btn-primary-custom'
                            },
                            timer: 4500,
                            timerProgressBar: true
                        });
                    }
                } else {
                    const errorMsg = data.message || 'No se pudo generar el contenido con la IA. Verifica los datos e intenta nuevamente.';
                    showActionToast('Error: ' + errorMsg, 'error');

                    // SweetAlert2 Error Centrado
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error al generar con IA',
                            html: `
                                <div style="font-size: 13.5px; color: #475569; line-height: 1.5; text-align: center;">
                                    ${escapeHtml(errorMsg)}
                                </div>
                            `,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#ef4444'
                        });
                    }
                }
            } catch (err) {
                console.error('Error generando con Gemini IA:', err);
                showActionToast('Error de red al conectar con el servidor', 'error');

                // SweetAlert2 Error de Conexión Centrado
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Conexión',
                        html: `
                            <div style="font-size: 13.5px; color: #475569; line-height: 1.5; text-align: center;">
                                Ocurrió un error al conectar con el servidor o el servicio de IA. Por favor, verifica tu conexión e inténtalo nuevamente.
                            </div>
                        `,
                        confirmButtonText: 'Entendido',
                        confirmButtonColor: '#ef4444'
                    });
                }
            } finally {
                if (btnStep3) {
                    btnStep3.disabled = false;
                    btnStep3.style.opacity = '1';
                }
                if (btnStep3Text) btnStep3Text.innerHTML = 'Generar con IA';
            }
        }

        async function clearRosaAiContent() {
            if (!confirm('¿Deseas limpiar el contenido generado por la IA en los Puntos 4, 5 y 6?')) {
                return;
            }

            const elAT = document.getElementById('doc_val_analisis_tecnico_narrative');
            const elObs = document.getElementById('doc_val_observaciones_narrative');
            const elRec = document.getElementById('doc_val_recomendaciones_narrative');

            if (elAT) elAT.innerText = '';
            if (elObs) elObs.innerText = '';
            if (elRec) elRec.innerText = '';

            const atInput = document.getElementById('analisis_tecnico_input');
            const obsInput = document.getElementById('observaciones_input');
            const recInput = document.getElementById('recomendaciones_input');

            if (atInput) atInput.value = '';
            if (obsInput) obsInput.value = '';
            if (recInput) recInput.value = '';

            // Deshabilitar botón de descarga de Word
            const btnWord = document.getElementById('btnDownloadStep3Word');
            if (btnWord) {
                btnWord.disabled = true;
                btnWord.style.opacity = '0.5';
                btnWord.style.cursor = 'not-allowed';
                btnWord.setAttribute('disabled', 'disabled');
            }

            // Auto-guardar vaciado en el servidor
            const payload = {
                evaluation_id: '{{ $selectedMeasurement ? $selectedMeasurement->id : "" }}',
                prompt: document.getElementById('prompt_input') ? document.getElementById('prompt_input').value : '',
                analisis_tecnico: '',
                observaciones: '',
                recomendaciones: '',
            };

            try {
                await fetch("{{ route('modules.ergonomia_rosa.prompts.save', $module->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                showActionToast('¡Contenido de los Puntos 4, 5 y 6 limpiado correctamente!');
            } catch (err) {
                console.error('Error al limpiar contenido IA:', err);
                showActionToast('Contenido limpiado en pantalla.');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Inicializar auto-expansión en todos los textareas reactivos
            document.querySelectorAll('.syso-live-textarea').forEach(el => {
                autoExpandTextarea(el);
            });
        });
    </script>
@endpush