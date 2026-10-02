@extends('layouts.app')

@section('title', 'Anexo 2 y Matrices Normativas REBA — Metric v2')

@push('styles')
    @metricStyle('ergonomia_reba')
    <style>
        .reba-dashboard-container {
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
        .reba-matrices-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 28px;
        }
        @media (max-width: 1024px) {
            .reba-matrices-grid {
                grid-template-columns: 1fr;
            }
        }
        /* ==========================================================================
           MATRICES NORMATIVAS REBA (TABLAS A, B, C) — PREMIUM METRIC DESIGN
           ========================================================================== */
        .reba-matrices-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1200px) {
            .reba-matrices-grid {
                grid-template-columns: 1fr;
            }
        }
        .reba-matrix-card {
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
        .reba-matrix-card:hover {
            box-shadow: 0 16px 36px -6px rgba(15, 28, 46, 0.09);
            border-color: #cbd5e1;
        }
        .reba-matrix-card-wide {
            grid-column: 1 / -1;
        }
        .reba-matrix-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1.5px solid #f1f5f9;
        }
        .reba-matrix-title-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .reba-matrix-badge {
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
        .reba-matrix-badge.badge-a {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            box-shadow: 0 4px 10px rgba(2, 132, 199, 0.3);
        }
        .reba-matrix-badge.badge-b {
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.3);
        }
        .reba-matrix-badge.badge-c {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            box-shadow: 0 4px 10px rgba(5, 150, 105, 0.3);
        }
        .reba-matrix-title {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.3px;
        }
        .reba-matrix-subtitle {
            font-size: 11.5px;
            color: #64748b;
            font-weight: 500;
        }

        /* Scorecards de Resumen */
        .reba-scorecard-widget {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 16px;
            box-shadow: 0 2px 6px rgba(15, 28, 46, 0.02);
        }
        .reba-scorecard-header {
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
        .reba-scorecard-body {
            padding: 10px 14px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
            gap: 10px;
        }
        .reba-scorecard-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.15s ease;
        }
        .reba-scorecard-item:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .reba-scorecard-item .item-label {
            font-size: 12px;
            font-weight: 600;
            color: #475569;
        }
        .reba-scorecard-item .item-val {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            background: #f1f5f9;
            padding: 2px 8px;
            border-radius: 6px;
        }
        .reba-scorecard-footer {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-top: 1.5px solid #bfdbfe;
            padding: 9px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .reba-scorecard-footer .footer-label {
            font-size: 12px;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .reba-scorecard-footer .footer-val {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 900;
            color: #1d4ed8;
            background: #ffffff;
            padding: 3px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
        }

        /* Matrix Grid Tables */
        .reba-table-scroll-wrap {
            width: 100%;
            overflow-x: auto;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            box-shadow: 0 2px 8px rgba(15, 28, 46, 0.03);
            background: #ffffff;
        }
        .reba-grid-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 11.5px;
            text-align: center;
            background: #ffffff;
        }
        .reba-grid-table th, .reba-grid-table td {
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 5px 6px;
            min-width: 24px;
            height: 26px;
            text-align: center;
            vertical-align: middle;
            transition: background-color 0.15s ease, color 0.15s ease;
        }
        .reba-grid-table th:last-child, .reba-grid-table td:last-child {
            border-right: none;
        }
        .reba-grid-table tr:last-child td, .reba-grid-table tr:last-child th {
            border-bottom: none;
        }
        .reba-th-primary {
            background: #0f1c2e;
            color: #ffffff;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .reba-th-secondary {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 11px;
        }
        .reba-th-sub {
            background: #f8fafc;
            color: #64748b;
            font-weight: 700;
            font-size: 10.5px;
        }
        .reba-th-rowhead {
            background: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 11.5px;
            width: 30px;
        }
        .reba-grid-table td {
            color: #334155;
            font-weight: 600;
            background: #ffffff;
        }
        .reba-grid-table tr:hover td:not(.reba-highlight-cell) {
            background-color: #f8fafc;
        }
        .reba-grid-table td:hover:not(.reba-highlight-cell) {
            background-color: #f0f9ff;
            color: #0284c7;
        }
        .reba-highlight-cell {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
            color: #ffffff !important;
            font-weight: 900 !important;
            border-radius: 4px;
            box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px #f59e0b, 0 4px 10px rgba(217, 119, 6, 0.45) !important;
            position: relative;
            z-index: 10;
            font-size: 12.5px;
        }

        /* Tarjeta de Actividad Muscular */
        .reba-activity-card {
            background: #f8fafc;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            padding: 14px 18px;
            margin-top: 16px;
            width: 100%;
            max-width: 760px;
            box-sizing: border-box;
            text-align: left;
        }
        .reba-activity-card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .reba-activity-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            color: #334155;
            margin-bottom: 4px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
        }
        .reba-activity-item.active {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #065f46;
            font-weight: 600;
        }
        .reba-activity-badge {
            font-size: 11px;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 6px;
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
            flex-shrink: 0;
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
            grid-template-columns: repeat(4, minmax(0, 1fr));
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

        /* ==========================================================================
           ANEXO 2 & REGISTROS REBA — HOJA TAMAÑO CARTA IDÉNTICA A ROSA (8.5 x 11 pulg.)
           ========================================================================== */
        .anexo2-sheet-wrapper {
            width: 100%;
            max-width: 816px; /* 8.5 in @ 96 DPI = 816px (21.59 cm) */
            margin: 0 auto;
        }
        .anexo2-sheet-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 14px;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            padding: 10px 16px;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(15, 28, 46, 0.03);
        }
        .anexo2-sheet-card {
            background: #ffffff;
            border: 1.5px solid #000000;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.1);
            width: 100%;
            max-width: 816px; /* 21.59 cm */
            min-height: 1056px; /* 11 in @ 96 DPI = 1056px / 27.94 cm */
            box-sizing: border-box;
            padding: 20mm 18mm;
            border-radius: 2px;
            margin: 0 auto 30px auto;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000000;
        }
        .anexo2-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #000000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            color: #000000;
        }
        .anexo2-table th,
        .anexo2-table td {
            border: 1px solid #000000;
            padding: 3px 5px;
            vertical-align: middle;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
        }
        .anexo2-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 2px 4px;
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
            color: #000000;
            border-radius: 2px;
            transition: all 0.15s ease;
            box-sizing: border-box;
        }
        .anexo2-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .anexo2-input:focus {
            outline: none;
            border-color: #0284c7;
            background: #f0f9ff;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        }
        .anexo2-task-input {
            width: 100%;
            border: 1px solid transparent;
            background: transparent;
            padding: 2px 2px;
            font-size: 7pt;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            text-align: center;
            color: #000000;
            border-radius: 2px;
            line-height: 1.15;
            box-sizing: border-box;
        }
        .anexo2-task-input:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }
        .anexo2-task-input:focus {
            outline: none;
            border-color: #0284c7;
            background: #e0f2fe;
            box-shadow: 0 0 0 2px rgba(2, 132, 199, 0.2);
        }
        .anexo2-select {
            border: 1px solid #cbd5e1;
            border-radius: 3px;
            background: #ffffff;
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 700;
            padding: 2px 6px;
            text-align: center;
            text-align-last: center;
            -moz-text-align-last: center;
            cursor: pointer;
            color: #000000;
        }
        .anexo2-select:focus {
            outline: none;
            border-color: #0284c7;
        }
        .anexo2-check {
            width: 15px;
            height: 15px;
            cursor: pointer;
            accent-color: #0284c7;
            margin: 0 auto;
            display: block;
        }
        .anexo2-risk-select {
            width: 100%;
            border: none;
            background: transparent;
            font-size: 9pt;
            font-family: Arial, Helvetica, sans-serif;
            font-weight: 800;
            padding: 2px 2px;
            text-align: center;
            text-align-last: center;
            -moz-text-align-last: center;
            cursor: pointer;
            color: inherit;
            appearance: none;
            -webkit-appearance: none;
        }
        .anexo2-risk-select:focus {
            outline: none;
        }
        .anexo2-risk-cell {
            transition: background-color 0.2s ease;
        }
        .anexo2-risk-cell[data-level="1"],
        .anexo2-risk-cell[data-level="Inapreciable"],
        .anexo2-risk-cell[data-level="Bajo"] {
            background-color: #00b050 !important;
            color: #000000 !important;
        }
        .anexo2-risk-cell[data-level="1"] select,
        .anexo2-risk-cell[data-level="Inapreciable"] select,
        .anexo2-risk-cell[data-level="Bajo"] select {
            color: #000000 !important;
            font-weight: 800;
        }
        .anexo2-risk-cell[data-level="2"],
        .anexo2-risk-cell[data-level="Medio"] {
            background-color: #ffff00 !important;
            color: #000000 !important;
        }
        .anexo2-risk-cell[data-level="2"] select,
        .anexo2-risk-cell[data-level="Medio"] select {
            color: #000000 !important;
            font-weight: 800;
        }
        .anexo2-risk-cell[data-level="3"],
        .anexo2-risk-cell[data-level="Alto"],
        .anexo2-risk-cell[data-level="Muy Alto"] {
            background-color: #ff0000 !important;
            color: #ffffff !important;
        }
        .anexo2-risk-cell[data-level="3"] select,
        .anexo2-risk-cell[data-level="Alto"] select,
        .anexo2-risk-cell[data-level="Muy Alto"] select {
            color: #ffffff !important;
            font-weight: 800;
        }
        .btn-copy-sheet {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            background: #0f172a;
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        }
        .btn-copy-sheet:hover {
            background: #0284c7;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
        }
        .btn-download-sheet {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 8px;
            background: #0284c7;
            color: #ffffff;
            font-size: 12.5px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.25);
        }
        .btn-download-sheet:hover {
            background: #0369a1;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.4);
        }
    </style>
@endpush

@section('content')
<div class="stepper-page-wrapper">

    <!-- 1. Encabezado y Navegación del Módulo de Tablas y Anexo 2 REBA -->
    <div class="illumination-header-banner" style="margin-bottom: 24px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <a href="{{ route('modules.ergonomia_reba', $module->id) }}" class="btn-secondary-subtle" style="padding: 6px 14px; font-size: 12px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="12" x2="5" y2="12" />
                        <polyline points="12 19 5 12 12 5" />
                    </svg>
                    <span>Volver al Monitoreo</span>
                </a>
                <span style="font-size: 12px; color: #94a3b8;">/</span>
                <span style="font-size: 12px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 8px; border-radius: 9999px; border: 1px solid #bae6fd;">
                    {{ $installationName }}
                </span>
            </div>
            <h1>
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="5" r="3" />
                    <path d="M6.5 9a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1" />
                    <path d="M17.5 9a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1" />
                    <path d="M9 11v8a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2v-8" />
                </svg>
                <span>Matrices Normativas y Anexo 2 — Ergonomía REBA</span>
            </h1>
            <p style="margin: 0; color: #64748b; font-size: 13.5px;">
                Formato Oficial Anexo 2, Matrices Normativas (Tablas A, B, C y Modificadores), Evaluación Detallada y Registros 3 y 4 según NTP 601 / ISO 11226
            </p>
        </div>

        <div class="header-action-group">
            <a href="{{ route('modules.ergonomia_reba', $module->id) }}" class="btn-primary-hero-action">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Ir a Evaluaciones</span>
            </a>
        </div>
    </div>

    <!-- 2. Barra Superior de Pasos Interactivos (Grid 4 Columnas) -->
    <div class="stepper-header-bar">
        <div class="stepper-nav-track" id="rebaStepperTrack">
            
            <!-- Paso 1 -->
            <button type="button" class="step-nav-btn active" data-step="1" onclick="goToStep(1)" title="Paso 1: Registro Anexo 2">
                <div class="step-nav-number">1</div>
                <div class="step-nav-text">
                    <span class="step-nav-title">Paso 1: Anexo 2</span>
                    <span class="step-nav-subtitle">Factores de Riesgo</span>
                </div>
            </button>

            <!-- Paso 2 -->
            <button type="button" class="step-nav-btn" data-step="2" onclick="goToStep(2)" title="Paso 2: Tablas Normativas REBA">
                <div class="step-nav-number">2</div>
                <div class="step-nav-text">
                    <span class="step-nav-title">Paso 2: Matrices REBA</span>
                    <span class="step-nav-subtitle">Tablas A, B, C y Modif.</span>
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

        </div>

        <!-- Barra de Progreso del Stepper -->
        <div class="stepper-progress-track">
            <div class="stepper-progress-fill" id="stepperProgressBar" style="width: 25%;"></div>
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
                            <span id="anexo2AutoSaveBadge" class="header-auto-save-status">
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
                                        {{ $anexo2Data['razon_social'] ?? ($companyName ?? '') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Dirección de la empresa o establecimiento laboral:</td>
                                    <td colspan="3" id="anexo2_val_direccion"
                                        style="width: 72%; padding: 2.5px 6px; font-weight: bold;">
                                        {{ $anexo2Data['direccion'] ?? ($companyAddress ?? '') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">Área
                                        y Sector en estudio:</td>
                                    <td id="anexo2_val_area_sector"
                                        style="width: 32%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['area_sector'] ?? ($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Administración') : 'Administración') }}
                                    </td>
                                    <td style="width: 24%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">N°
                                        de trabajadores:</td>
                                    <td id="anexo2_val_num_trabajadores"
                                        style="width: 16%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['num_trabajadores'] ?? ($selectedMeasurement ? ($selectedMeasurement->num_trabajadores ?: 1) : 1) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Puesto de trabajo:</td>
                                    <td colspan="3" id="anexo2_val_puesto_trabajo"
                                        style="width: 72%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['puesto_trabajo'] ?? ($selectedMeasurement ? ($selectedMeasurement->puesto_trabajo ?: 'Gerente general') : 'Gerente general') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Procedimiento de trabajo escrito:</td>
                                    <td id="anexo2_val_procedimiento_escrito"
                                        style="width: 32%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['procedimiento_escrito'] ?? ($selectedMeasurement ? ($selectedMeasurement->procedimiento_escrito ?: 'SI') : 'SI') }}
                                    </td>
                                    <td style="width: 24%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Capacitación:</td>
                                    <td id="anexo2_val_capacitacion"
                                        style="width: 16%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['capacitacion'] ?? ($selectedMeasurement ? ($selectedMeasurement->capacitacion ?: 'Si') : 'Si') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Nombre del trabajador/es:</td>
                                    <td colspan="3" id="anexo2_val_nombre_trabajador"
                                        style="width: 72%; padding: 2.5px 6px;">
                                        {{ $anexo2Data['nombre_trabajador'] ?? ($selectedMeasurement ? (is_array($selectedMeasurement->nombres_trabajadores) ? implode(', ', array_filter($selectedMeasurement->nombres_trabajadores)) : ($selectedMeasurement->nombres_trabajadores ?: 'Carlos Rene Ichuta Ichuta')) : 'Carlos Rene Ichuta Ichuta') }}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 28%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Manifestación temprana:</td>
                                    <td id="anexo2_val_manifestacion_temprana"
                                        style="width: 32%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['manifestacion_temprana'] ?? ($selectedMeasurement ? ($selectedMeasurement->manifestacion_temprana ?: 'No') : 'No') }}
                                    </td>
                                    <td style="width: 24%; font-weight: bold; background: #ffffff; padding: 2.5px 6px;">
                                        Ubicación del síntoma:</td>
                                    <td id="anexo2_val_ubicacion_sintoma"
                                        style="width: 16%; padding: 2.5px 6px; text-align: center; font-weight: bold;">
                                        {{ $anexo2Data['ubicacion_sintoma'] ?? ($selectedMeasurement ? ($selectedMeasurement->ubicacion_sintoma ?: 'Ninguna') : 'Ninguna') }}
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
            <!-- PASO 2: MATRICES NORMATIVAS REBA (TABLAS A, B, C Y CRITERIOS)             -->
            <!-- ========================================================================= -->
            <div class="step-pane-content" id="step_pane_2">
                <div class="reba-dashboard-container">
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <span class="step-pane-badge">Paso 2 de 5 — Matrices Normativas NTP 601 / ISO 11226</span>
                            <h2 style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a; margin: 4px 0 0 0;">
                                Tablas de Referencia Normativa del Método REBA
                            </h2>
                        </div>

                        <!-- Selector de puesto para resaltar valores en matrices -->
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <label for="step2MeasSelect" style="font-size: 12.5px; font-weight: 700; color: #475569;">Puesto:</label>
                            <select id="step2MeasSelect" class="anexo2-select" onchange="changeMeasurement(this.value)" style="padding: 5px 12px; font-size: 12.5px; background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 8px;">
                                @forelse($allMeasurements as $mOption)
                                    <option value="{{ $mOption->id }}" {{ $selectedMeasurement && $selectedMeasurement->id === $mOption->id ? 'selected' : '' }}>
                                        Punto {{ $mOption->point_number }}: {{ $mOption->puesto_trabajo ?: 'Puesto '.$mOption->point_number }} (Score REBA: {{ $mOption->calculated_score_final ?? $mOption->score_final }})
                                    </option>
                                @empty
                                    <option value="">Sin mediciones registradas</option>
                                @endforelse
                            </select>
                        </div>
                    </div>

                    <!-- Resumen del Puesto Evaluado -->
                    @php
                        $curTronco = $rebaScores['tronco_final'] ?? 1;
                        $curCuello = $rebaScores['cuello_final'] ?? 1;
                        $curPiernas = $rebaScores['piernas_final'] ?? 1;
                        $curScoreTablaA = $rebaScores['score_tabla_a'] ?? 1;
                        $curScoreA = $rebaScores['score_a'] ?? 1;

                        $curBrazo = $rebaScores['brazo_final'] ?? 1;
                        $curAntebrazo = $rebaScores['antebrazo_final'] ?? 1;
                        $curMuneca = $rebaScores['muneca_final'] ?? 1;
                        $curScoreTablaB = $rebaScores['score_tabla_b'] ?? 1;
                        $curScoreB = $rebaScores['score_b'] ?? 1;

                        $curScoreC = $rebaScores['score_c'] ?? 1;
                        $curScoreActividad = $rebaScores['score_actividad'] ?? 0;
                        $curScoreFinal = $rebaScores['score_final'] ?? 1;
                        $curRiskLevel = $rebaScores['risk_level'] ?? 'Inapreciable';
                        $curActionLevel = $rebaScores['action_level'] ?? 'Nivel 0: No es necesaria acción';
                        $curBadgeClass = $rebaScores['badge_class'] ?? 'risk-inapreciable';

                        $tableAData = [
                            1 => [1=>[1=>1,2=>2,3=>3,4=>4], 2=>[1=>2,2=>3,3=>4,4=>5], 3=>[1=>3,2=>4,3=>5,4=>6]],
                            2 => [1=>[1=>2,2=>3,3=>4,4=>5], 2=>[1=>3,2=>4,3=>5,4=>6], 3=>[1=>4,2=>5,3=>6,4=>7]],
                            3 => [1=>[1=>2,2=>4,3=>5,4=>6], 2=>[1=>4,2=>5,3=>6,4=>7], 3=>[1=>5,2=>6,3=>7,4=>8]],
                            4 => [1=>[1=>3,2=>5,3=>6,4=>7], 2=>[1=>5,2=>6,3=>7,4=>8], 3=>[1=>6,2=>7,3=>8,4=>9]],
                            5 => [1=>[1=>4,2=>6,3=>7,4=>8], 2=>[1=>6,2=>7,3=>8,4=>9], 3=>[1=>7,2=>8,3=>9,4=>9]],
                        ];

                        $tableBData = [
                            1 => [1=>[1=>1,2=>2,3=>2], 2=>[1=>1,2=>2,3=>3]],
                            2 => [1=>[1=>1,2=>2,3=>3], 2=>[1=>2,2=>3,3=>4]],
                            3 => [1=>[1=>3,2=>4,3=>5], 2=>[1=>4,2=>5,3=>5]],
                            4 => [1=>[1=>4,2=>5,3=>5], 2=>[1=>5,2=>6,3=>7]],
                            5 => [1=>[1=>6,2=>7,3=>8], 2=>[1=>7,2=>8,3=>8]],
                            6 => [1=>[1=>7,2=>8,3=>8], 2=>[1=>8,2=>9,3=>9]],
                        ];

                        $tableCData = [
                            1  => [1=>1, 2=>1, 3=>1, 4=>2, 5=>3, 6=>3, 7=>4, 8=>5, 9=>6, 10=>7, 11=>7, 12=>7],
                            2  => [1=>1, 2=>2, 3=>2, 4=>3, 5=>4, 6=>4, 7=>5, 8=>6, 9=>6, 10=>7, 11=>7, 12=>8],
                            3  => [1=>2, 2=>3, 3=>3, 4=>3, 5=>4, 6=>5, 7=>6, 8=>7, 9=>7, 10=>8, 11=>8, 12=>8],
                            4  => [1=>3, 2=>4, 3=>4, 4=>4, 5=>5, 6=>6, 7=>7, 8=>8, 9=>8, 10=>9, 11=>9, 12=>9],
                            5  => [1=>4, 2=>4, 3=>4, 4=>5, 5=>6, 6=>7, 7=>8, 8=>8, 9=>9, 10=>9, 11=>9, 12=>9],
                            6  => [1=>6, 2=>6, 3=>6, 4=>7, 5=>8, 6=>8, 7=>9, 8=>9, 9=>10, 10=>10, 11=>10, 12=>10],
                            7  => [1=>7, 2=>7, 3=>7, 4=>8, 5=>9, 6=>9, 7=>9, 8=>10, 9=>10, 10=>11, 11=>11, 12=>11],
                            8  => [1=>8, 2=>8, 3=>8, 4=>9, 5=>10, 6=>10, 7=>10, 8=>10, 9=>10, 10=>11, 11=>11, 12=>11],
                            9  => [1=>9, 2=>9, 3=>9, 4=>10, 5=>10, 6=>10, 7=>11, 8=>11, 9=>11, 10=>12, 11=>12, 12=>12],
                            10 => [1=>10, 2=>10, 3=>10, 4=>11, 5=>11, 6=>11, 7=>11, 8=>12, 9=>12, 10=>12, 11=>12, 12=>12],
                            11 => [1=>11, 2=>11, 3=>11, 4=>11, 5=>12, 6=>12, 7=>12, 8=>12, 9=>12, 10=>12, 11=>12, 12=>12],
                            12 => [1=>12, 2=>12, 3=>12, 4=>12, 5=>12, 6=>12, 7=>12, 8=>12, 9=>12, 10=>12, 11=>12, 12=>12],
                        ];
                    @endphp

                    <div style="background: #f8fafc; border: 1.5px solid #cbd5e1; border-radius: 12px; padding: 18px 24px; margin-bottom: 28px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; flex-wrap: wrap; gap: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 14px; font-weight: 800; color: #0f172a;">Puesto Evaluado:</span>
                                <span style="font-size: 14px; font-weight: 700; color: #0284c7;">{{ $selectedMeasurement ? $selectedMeasurement->puesto_trabajo : 'Evaluación General' }}</span>
                                <span style="font-size: 12px; color: #64748b;">(Área: {{ $selectedMeasurement ? $selectedMeasurement->area_sector : 'Producción' }})</span>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 12px; font-weight: 700; color: #475569;">Nivel de Riesgo:</span>
                                <span class="reba-section-badge {{ $curBadgeClass }}" style="font-size: 12px; padding: 4px 12px;">{{ $curRiskLevel }} ({{ $curActionLevel }})</span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; text-align: center;">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Puntuación Final</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 24px; font-weight: 900; color: #0284c7;">{{ $curScoreFinal }}/15</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Grupo A (Tabla A)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a;">{{ $curScoreTablaA }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Puntuación A (Total)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a;">{{ $curScoreA }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Grupo B (Tabla B)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a;">{{ $curScoreTablaB }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Puntuación B (Total)</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a;">{{ $curScoreB }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Puntuación C</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #0f172a;">{{ $curScoreC }}</div>
                            </div>
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px;">
                                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Actividad Muscular</div>
                                <div style="font-family: Outfit, sans-serif; font-size: 20px; font-weight: 800; color: #059669;">+{{ $curScoreActividad }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Grid de Matrices REBA -->
                    <div class="reba-matrices-grid">
                        
                        <!-- Matriz 1: Tabla A (Tronco, Cuello, Piernas) -->
                        <div class="reba-matrix-card">
                            <div class="reba-matrix-header">
                                <div class="reba-matrix-title-wrap">
                                    <span class="reba-matrix-badge badge-a">A</span>
                                    <div>
                                        <h4 class="reba-matrix-title">Tabla A: Tronco, Cuello y Piernas</h4>
                                        <span class="reba-matrix-subtitle">Evaluación biomecánica Grupo A</span>
                                    </div>
                                </div>
                                <span class="reba-posture-badge" style="font-size: 11.5px; padding: 4px 10px;">
                                    Puntaje: <strong>{{ $curScoreTablaA }}</strong>
                                </span>
                            </div>

                            <!-- Scorecard de Resumen Tabla A -->
                            <div class="reba-scorecard-widget" style="width: 100%;">
                                <div class="reba-scorecard-header">
                                    <span>Puntuaciones del Puesto</span>
                                    <span style="font-size: 10px; opacity: 0.7;">Grupo A</span>
                                </div>
                                <div class="reba-scorecard-body">
                                    <div class="reba-scorecard-item">
                                        <span class="item-label">Tronco</span>
                                        <span class="item-val">{{ $curTronco }}</span>
                                    </div>
                                    <div class="reba-scorecard-item">
                                        <span class="item-label">Cuello</span>
                                        <span class="item-val">{{ $curCuello }}</span>
                                    </div>
                                    <div class="reba-scorecard-item">
                                        <span class="item-label">Piernas</span>
                                        <span class="item-val">{{ $curPiernas }}</span>
                                    </div>
                                </div>
                                <div class="reba-scorecard-footer">
                                    <span class="footer-label">Puntuación Tabla A</span>
                                    <span class="footer-val">{{ $curScoreTablaA }}</span>
                                </div>
                            </div>

                            <div class="reba-table-scroll-wrap">
                                <table class="reba-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="reba-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="12" class="reba-th-primary">Cuello</th>
                                        </tr>
                                        <tr>
                                            <th colspan="4" class="reba-th-secondary">1</th>
                                            <th colspan="4" class="reba-th-secondary">2</th>
                                            <th colspan="4" class="reba-th-secondary">3</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="reba-th-sub">Piernas</th>
                                            @for($c = 1; $c <= 3; $c++)
                                                @for($p = 1; $p <= 4; $p++)
                                                    <th class="reba-th-sub">{{ $p }}</th>
                                                @endfor
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tableAData as $trVal => $cuellos)
                                            <tr>
                                                @if($trVal === 1)
                                                    <th rowspan="5" class="reba-th-rowhead" style="vertical-align: middle; width: 44px;">Tronco</th>
                                                @endif
                                                <th class="reba-th-rowhead">{{ $trVal }}</th>
                                                @foreach($cuellos as $cVal => $piernas)
                                                    @foreach($piernas as $pVal => $cellVal)
                                                        @php
                                                            $isH = ($trVal === $curTronco && $cVal === $curCuello && ($pVal === $curPiernas || ($pVal === 4 && $curPiernas >= 4)));
                                                        @endphp
                                                        <td class="{{ $isH ? 'reba-highlight-cell' : '' }}">
                                                            {{ $cellVal }}
                                                        </td>
                                                    @endforeach
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Matriz 2: Tabla B (Brazos, Antebrazos, Muñecas) -->
                        <div class="reba-matrix-card">
                            <div class="reba-matrix-header">
                                <div class="reba-matrix-title-wrap">
                                    <span class="reba-matrix-badge badge-b">B</span>
                                    <div>
                                        <h4 class="reba-matrix-title">Tabla B: Brazos, Antebrazos y Muñecas</h4>
                                        <span class="reba-matrix-subtitle">Evaluación biomecánica Grupo B</span>
                                    </div>
                                </div>
                                <span class="reba-posture-badge" style="font-size: 11.5px; padding: 4px 10px;">
                                    Puntaje: <strong>{{ $curScoreTablaB }}</strong>
                                </span>
                            </div>

                            <!-- Scorecard de Resumen Tabla B -->
                            <div class="reba-scorecard-widget" style="width: 100%;">
                                <div class="reba-scorecard-header">
                                    <span>Puntuaciones del Puesto</span>
                                    <span style="font-size: 10px; opacity: 0.7;">Grupo B</span>
                                </div>
                                <div class="reba-scorecard-body">
                                    <div class="reba-scorecard-item">
                                        <span class="item-label">Brazos</span>
                                        <span class="item-val">{{ $curBrazo }}</span>
                                    </div>
                                    <div class="reba-scorecard-item">
                                        <span class="item-label">Antebrazos</span>
                                        <span class="item-val">{{ $curAntebrazo }}</span>
                                    </div>
                                    <div class="reba-scorecard-item">
                                        <span class="item-label">Muñecas</span>
                                        <span class="item-val">{{ $curMuneca }}</span>
                                    </div>
                                </div>
                                <div class="reba-scorecard-footer" style="background: linear-gradient(135deg, #f5f3ff, #ede9fe); border-color: #ddd6fe;">
                                    <span class="footer-label" style="color: #6d28d9;">Puntuación Tabla B</span>
                                    <span class="footer-val" style="color: #6d28d9; box-shadow: 0 2px 6px rgba(109, 40, 217, 0.2);">{{ $curScoreTablaB }}</span>
                                </div>
                            </div>

                            <div class="reba-table-scroll-wrap">
                                <table class="reba-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="reba-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="6" class="reba-th-primary">Antebrazo</th>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="reba-th-secondary">1</th>
                                            <th colspan="3" class="reba-th-secondary">2</th>
                                        </tr>
                                        <tr>
                                            <th colspan="2" class="reba-th-sub">Muñeca</th>
                                            @for($ab = 1; $ab <= 2; $ab++)
                                                @for($m = 1; $m <= 3; $m++)
                                                    <th class="reba-th-sub">{{ $m }}</th>
                                                @endfor
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tableBData as $brVal => $antebrazos)
                                            <tr>
                                                @if($brVal === 1)
                                                    <th rowspan="6" class="reba-th-rowhead" style="vertical-align: middle; width: 44px;">Brazo</th>
                                                @endif
                                                <th class="reba-th-rowhead">{{ $brVal }}</th>
                                                @foreach($antebrazos as $abVal => $munecas)
                                                    @foreach($munecas as $mVal => $cellVal)
                                                        @php
                                                            $isH = ($brVal === $curBrazo && $abVal === $curAntebrazo && $mVal === $curMuneca);
                                                        @endphp
                                                        <td class="{{ $isH ? 'reba-highlight-cell' : '' }}">
                                                            {{ $cellVal }}
                                                        </td>
                                                    @endforeach
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Matriz 3: Tabla C (Puntuación A vs Puntuación B) y Puntuación Final -->
                        <div class="reba-matrix-card reba-matrix-card-wide">
                            <div class="reba-matrix-header">
                                <div class="reba-matrix-title-wrap">
                                    <span class="reba-matrix-badge badge-c">C</span>
                                    <div>
                                        <h4 class="reba-matrix-title">Tabla C: Intersección Puntuación A vs B</h4>
                                        <span class="reba-matrix-subtitle">Cálculo de Score C + Modificador de Actividad Muscular</span>
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                    <span class="reba-posture-badge" style="font-size: 11.5px; padding: 4px 10px;">
                                        Score C: <strong>{{ $curScoreC }}</strong>
                                    </span>
                                    <span class="reba-section-badge {{ $curBadgeClass }}" style="font-size: 11.5px; padding: 4px 12px;">
                                        Final: <strong>{{ $curScoreFinal }}/15</strong>
                                    </span>
                                </div>
                            </div>

                            <!-- Tablas de Resumen C y Final -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 16px; width: 100%;">
                                <!-- Resumen Tabla C -->
                                <div class="reba-scorecard-widget" style="margin-bottom: 0;">
                                    <div class="reba-scorecard-header">
                                        <span>Cálculo Intermedio Tabla C</span>
                                        <span style="font-size: 10px; opacity: 0.7;">A vs B</span>
                                    </div>
                                    <div class="reba-scorecard-body">
                                        <div class="reba-scorecard-item">
                                            <span class="item-label">Puntuación Total A</span>
                                            <span class="item-val">{{ $curScoreA }}</span>
                                        </div>
                                        <div class="reba-scorecard-item">
                                            <span class="item-label">Puntuación Total B</span>
                                            <span class="item-val">{{ $curScoreB }}</span>
                                        </div>
                                    </div>
                                    <div class="reba-scorecard-footer" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); border-color: #a7f3d0;">
                                        <span class="footer-label" style="color: #047857;">Puntuación Tabla C</span>
                                        <span class="footer-val" style="color: #047857; box-shadow: 0 2px 6px rgba(4, 120, 87, 0.2);">{{ $curScoreC }}</span>
                                    </div>
                                </div>

                                <!-- Resumen Puntuación Final -->
                                <div class="reba-scorecard-widget" style="margin-bottom: 0;">
                                    <div class="reba-scorecard-header">
                                        <span>Consolidación Final REBA</span>
                                        <span style="font-size: 10px; opacity: 0.7;">Resultado</span>
                                    </div>
                                    <div class="reba-scorecard-body">
                                        <div class="reba-scorecard-item">
                                            <span class="item-label">Puntuación Tabla C</span>
                                            <span class="item-val">{{ $curScoreC }}</span>
                                        </div>
                                        <div class="reba-scorecard-item">
                                            <span class="item-label">Actividad Muscular</span>
                                            <span class="item-val" style="color: #059669;">+{{ $curScoreActividad }}</span>
                                        </div>
                                    </div>
                                    <div class="reba-scorecard-footer" style="background: linear-gradient(135deg, #fef2f2, #fee2e2); border-color: #fecaca;">
                                        <span class="footer-label" style="color: #b91c1c;">Puntuación Final</span>
                                        <span class="footer-val" style="color: #b91c1c; box-shadow: 0 2px 6px rgba(185, 28, 28, 0.2);">{{ $curScoreFinal }} / 15</span>
                                    </div>
                                </div>
                            </div>

                            <div class="reba-table-scroll-wrap" style="max-width: 900px; margin: 0 auto;">
                                <table class="reba-grid-table">
                                    <thead>
                                        <tr>
                                            <th colspan="2" rowspan="2" class="reba-th-primary" style="background: #0f1c2e;"></th>
                                            <th colspan="12" class="reba-th-primary">Puntuación B</th>
                                        </tr>
                                        <tr>
                                            @for($b = 1; $b <= 12; $b++)
                                                <th class="reba-th-secondary">{{ $b }}</th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tableCData as $aRow => $bCols)
                                            <tr>
                                                @if($aRow === 1)
                                                    <th rowspan="12" class="reba-th-rowhead" style="vertical-align: middle; width: 64px;">Puntuación A</th>
                                                @endif
                                                <th class="reba-th-rowhead">{{ $aRow }}</th>
                                                @foreach($bCols as $bCol => $valC)
                                                    @php
                                                        $isH = ($aRow === $curScoreA && $bCol === $curScoreB);
                                                    @endphp
                                                    <td class="{{ $isH ? 'reba-highlight-cell' : '' }}">
                                                        {{ $valC }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Leyenda de puntuación de la actividad -->
                            <div class="reba-activity-card" style="margin: 16px auto 0 auto;">
                                <div class="reba-activity-card-title">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                    </svg>
                                    <span>Puntuación por Actividad Muscular:</span>
                                </div>
                                <div class="reba-activity-item {{ !empty($selectedMeasurement->actividad_estatica) ? 'active' : '' }}">
                                    <span>• <strong>+1:</strong> Una o más partes del cuerpo estáticas (mantenidas más de 1 minuto).</span>
                                    @if(!empty($selectedMeasurement->actividad_estatica))
                                        <span class="reba-activity-badge">+1 Aplicado</span>
                                    @endif
                                </div>
                                <div class="reba-activity-item {{ !empty($selectedMeasurement->actividad_repetitiva) ? 'active' : '' }}">
                                    <span>• <strong>+1:</strong> Movimientos repetitivos (repetidos más de 4 veces por minuto, excluyendo caminar).</span>
                                    @if(!empty($selectedMeasurement->actividad_repetitiva))
                                        <span class="reba-activity-badge">+1 Aplicado</span>
                                    @endif
                                </div>
                                <div class="reba-activity-item {{ !empty($selectedMeasurement->actividad_inestable) ? 'active' : '' }}">
                                    <span>• <strong>+1:</strong> Se producen cambios posturales importantes o posturas inestables.</span>
                                    @if(!empty($selectedMeasurement->actividad_inestable))
                                        <span class="reba-activity-badge">+1 Aplicado</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Tabla de Criterios de Acción y Riesgo REBA -->
                    <div style="margin-top: 24px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 18px;">
                        <h3 style="font-family: Outfit, sans-serif; font-size: 15px; font-weight: 800; margin: 0 0 12px 0; color: #0f172a; text-align: center;">
                            Criterios de Puntuación Final, Nivel de Acción y Nivel de Riesgo REBA
                        </h3>
                        <table class="illumination-custom-table" style="width: 100%; max-width: 750px; margin: 0 auto; font-size: 12px; text-align: center;">
                            <thead>
                                <tr style="background: #0f1c2e; color: #ffffff;">
                                    <th style="width: 20%; text-align: center;">Puntuación Final</th>
                                    <th style="width: 20%; text-align: center;">Nivel de Acción</th>
                                    <th style="width: 25%; text-align: center;">Nivel de Riesgo</th>
                                    <th style="width: 35%; text-align: center;">Acción Requerida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr style="{{ $curScoreFinal === 1 ? 'background: #ecfdf5; font-weight: bold;' : '' }}">
                                    <td><strong>1</strong></td>
                                    <td>Nivel 0</td>
                                    <td><span class="reba-section-badge risk-inapreciable">Inapreciable</span></td>
                                    <td>No es necesaria acción</td>
                                </tr>
                                <tr style="{{ $curScoreFinal >= 2 && $curScoreFinal <= 3 ? 'background: #f7fee7; font-weight: bold;' : '' }}">
                                    <td><strong>2 - 3</strong></td>
                                    <td>Nivel 1</td>
                                    <td><span class="reba-section-badge risk-bajo">Bajo</span></td>
                                    <td>Puede ser necesaria la acción</td>
                                </tr>
                                <tr style="{{ $curScoreFinal >= 4 && $curScoreFinal <= 7 ? 'background: #fffbeb; font-weight: bold;' : '' }}">
                                    <td><strong>4 - 7</strong></td>
                                    <td>Nivel 2</td>
                                    <td><span class="reba-section-badge risk-medio">Medio</span></td>
                                    <td>Es necesaria la acción</td>
                                </tr>
                                <tr style="{{ $curScoreFinal >= 8 && $curScoreFinal <= 10 ? 'background: #fff7ed; font-weight: bold;' : '' }}">
                                    <td><strong>8 - 10</strong></td>
                                    <td>Nivel 3</td>
                                    <td><span class="reba-section-badge risk-alto">Alto</span></td>
                                    <td>Es necesaria la acción pronto</td>
                                </tr>
                                <tr style="{{ $curScoreFinal >= 11 ? 'background: #fef2f2; font-weight: bold;' : '' }}">
                                    <td><strong>11 - 15</strong></td>
                                    <td>Nivel 4</td>
                                    <td><span class="reba-section-badge risk-muy-alto">Muy Alto</span></td>
                                    <td>Es necesaria la acción de inmediato</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- PASO 3: EVALUACIÓN DETALLADA Y REDACCIÓN CON IA (HOJA TÉCNICA)            -->
            <!-- ========================================================================= -->
            <div class="step-pane-content" id="step_pane_3">
                <div class="anexo2-sheet-wrapper">
                    
                    <!-- Barra de Herramientas de la Hoja -->
                    <div class="anexo2-sheet-toolbar">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="step-pane-badge" style="margin-bottom: 0;">Formato Oficial — Evaluación Detallada</span>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <button type="button" class="btn-clear-sheet-ai" id="btnClearStep3Ai" onclick="clearRebaAiContent()"
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

                            <button type="button" class="btn-ai-generate-sheet" id="btnGenerateStep3Ai" onclick="generateRebaAiNarrative()"
                                title="Generar automáticamente con Gemini IA el Análisis Técnico, Observaciones y Recomendaciones (Puntos 4, 5 y 6)"
                                style="display: inline-flex; align-items: center; gap: 7px; padding: 7px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #ffffff; border: 1px solid #0284c7; cursor: pointer; box-shadow: 0 2px 4px rgba(2, 132, 199, 0.25); transition: all 0.2s ease;">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3Z"/>
                                    <path d="M18 15h6"/>
                                    <path d="M21 12v6"/>
                                </svg>
                                <span id="btnGenerateStep3AiText">Generar con IA</span>
                            </button>

                            @php
                                $hasRebaAiContent = !empty(trim($promptsData['analisis_tecnico'] ?? '')) || 
                                                     !empty(trim($promptsData['observaciones'] ?? '')) || 
                                                     !empty(trim($promptsData['recomendaciones'] ?? ''));
                            @endphp
                            <button type="button" class="btn-download-sheet" id="btnDownloadStep3Word" onclick="downloadEvaluacionDetalladaDoc()"
                                title="Descargar registro de evaluación detallada compatible con Microsoft Word (.doc) en formato vertical y Arial 9pt"
                                @if(!$hasRebaAiContent) disabled style="opacity: 0.5; cursor: not-allowed;" @endif>
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
                                MEDIANTE EL MÉTODO REBA EN PUESTOS OPERATIVOS
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
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_area_sector">{{ $anexo2Data['area_sector'] ?? ($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Producción') : 'Producción') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Puesto de trabajo:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_puesto_trabajo">{{ $anexo2Data['puesto_trabajo'] ?? ($selectedMeasurement ? ($selectedMeasurement->puesto_trabajo ?: 'Operario de Producción') : 'Operario de Producción') }}</td>
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
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_actividad">{{ $selectedMeasurement ? ($selectedMeasurement->tarea_analizada ?: ($selectedMeasurement->factor_riesgo ?: ($anexo2Data['actividad_principal'] ?? ($anexo2Data['tareas']['tarea_1'] ?? 'Manipulación y traslado manual de cargas')))) : ($anexo2Data['actividad_principal'] ?? 'Manipulación y traslado manual de cargas') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Tiempo de exposición:</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;" id="doc_val_tiempo_exposicion">{{ (int)($selectedMeasurement ? ($selectedMeasurement->tiempo_exposicion_horas ?: 8) : 8) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- 2. Resultados del Método REBA por puesto -->
                        <div style="margin-bottom: 10px;">
                            <div style="font-family: Arial, sans-serif; font-size: 9pt; font-weight: bold; margin-bottom: 4px; color: #000000;">
                                2. Resultados del Método REBA por puesto
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
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Cuello, Tronco y Piernas</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;" id="doc_val_score_tabla_a">{{ $curScoreTablaA }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Puntuación A</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Puntuación Grupo A + Carga / Fuerza</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;" id="doc_val_score_a">{{ $curScoreA }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Tabla B</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Brazo, Antebrazo y Muñeca</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;" id="doc_val_score_tabla_b">{{ $curScoreTablaB }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Puntuación B</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Puntuación Grupo B + Agarre</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;" id="doc_val_score_b">{{ $curScoreB }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Tabla C</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px;">Puntuación combinada Tabla A y Tabla B</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;" id="doc_val_score_c">{{ $curScoreC }}</td>
                                    </tr>
                                    <tr>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold;">Tabla Final</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; font-weight: bold;">Resultado final REBA (Tabla C + Actividad)</td>
                                        @php
                                            $finalScore = (int)$curScoreFinal;
                                            if ($finalScore >= 11) {
                                                $finalBg = '#ff0000';
                                                $finalColor = '#ffffff';
                                            } elseif ($finalScore >= 8) {
                                                $finalBg = '#ff6600';
                                                $finalColor = '#ffffff';
                                            } elseif ($finalScore >= 4) {
                                                $finalBg = '#ffff00';
                                                $finalColor = '#000000';
                                            } elseif ($finalScore >= 2) {
                                                $finalBg = '#92d050';
                                                $finalColor = '#000000';
                                            } else {
                                                $finalBg = '#00b050';
                                                $finalColor = '#ffffff';
                                            }
                                        @endphp
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold; background-color: {{ $finalBg }}; color: {{ $finalColor }}; font-size: 9.5pt;" id="doc_val_score_final">{{ $finalScore }}</td>
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
                                        <th style="width: 22%; border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Puntaje final REBA</th>
                                        <th style="width: 22%; border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Nivel de riesgo</th>
                                        <th style="width: 56%; border: 1px solid #000000; padding: 2.5px 6px; text-align: center; font-weight: bold; color: #000000; font-size: 9pt;">Acción requerida</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr style="background: #fff2cc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">1</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Inapreciable</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">No es necesaria actuación</td>
                                    </tr>
                                    <tr style="background: #fce4d6;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">2 a 3</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Bajo</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Puede ser necesaria la actuación</td>
                                    </tr>
                                    <tr style="background: #f8cecc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">4 a 7</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Medio</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Es necesaria la actuación</td>
                                    </tr>
                                    <tr style="background: #f8cecc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">8 a 10</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Alto</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Es necesaria la actuación pronto</td>
                                    </tr>
                                    <tr style="background: #f8cecc;">
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center; font-weight: bold;">11 a 15</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 4px; text-align: center;">Muy alto</td>
                                        <td style="border: 1px solid #000000; padding: 2.5px 6px; text-align: center;">Es necesaria la actuación de inmediato</td>
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
                                $scoreNum = (int)$curScoreFinal;
                                if ($scoreNum <= 1) {
                                    $riskText = 'Inapreciable';
                                    $actionText = 'No es necesaria actuación';
                                } elseif ($scoreNum <= 3) {
                                    $riskText = 'Bajo';
                                    $actionText = 'Puede ser necesaria la actuación';
                                } elseif ($scoreNum <= 7) {
                                    $riskText = 'Medio';
                                    $actionText = 'Es necesaria la actuación';
                                } elseif ($scoreNum <= 10) {
                                    $riskText = 'Alto';
                                    $actionText = 'Es necesaria la actuación pronto';
                                } else {
                                    $riskText = 'Muy alto';
                                    $actionText = 'Es necesaria la actuación de inmediato';
                                }
                            @endphp
                            <div id="doc_val_analisis_tecnico_narrative" contenteditable="true"
                                class="doc-editable-narrative"
                                style="font-family: Arial, sans-serif; font-size: 9pt; color: #1e293b; line-height: 1.35; margin-bottom: 6px; text-align: justify;"
                                oninput="autoSaveNarrativeField('analisis_tecnico', this.innerText)"
                                data-placeholder="Escriba aquí el análisis técnico o pulse 'Generar con IA'...">{{ $promptsData['analisis_tecnico'] ?? '' }}</div>
                            <table style="width: 75%; margin: 6px auto; font-family: Arial, sans-serif; font-size: 9pt; border-collapse: collapse;">
                                <tr>
                                    <td style="width: 38%; padding: 1.5px 0;">Puntuación final REBA de:</td>
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
                                style="font-family: Arial, sans-serif; font-size: 9pt; line-height: 1.35; text-align: justify; white-space: pre-line;"
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
                    $area4 = $selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Producción') : ($anexo2Data['area_sector'] ?? 'Producción');
                    $puesto4 = $selectedMeasurement ? ($selectedMeasurement->puesto_trabajo ?: 'Operario de Producción') : ($anexo2Data['puesto_trabajo'] ?? 'Operario de Producción');
                    $tarea4 = $selectedMeasurement ? ($selectedMeasurement->tarea_analizada ?: 'Manipulación y traslado manual de cargas') : 'Manipulación y traslado manual de cargas';
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

    </div>

    <!-- Barra Inferior de Navegación del Stepper -->
    <div class="stepper-footer-bar">
        <div class="stepper-footer-left">
            <span class="stepper-indicator-text">
                Paso <strong id="stepIndicatorNumber">1</strong> de <strong>4</strong>
            </span>
            <span class="stepper-keyboard-hint">
                <kbd>←</kbd> <kbd>→</kbd> para navegar
            </span>
        </div>
        <div class="stepper-footer-right">
            <button type="button" class="btn-stepper-nav subtle" id="btnStepPrev" onclick="navigateStep(-1)" disabled>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="15 18 9 12 15 6"/>
                </svg>
                <span>Anterior</span>
            </button>
            <button type="button" class="btn-stepper-nav primary" id="btnStepNext" onclick="navigateStep(1)">
                <span>Siguiente</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
            </button>
        </div>
    </div>

</div>

<!-- Modal de Prompts y Asistente IA REBA -->
@include('measurements.ergonomia_reba.modals.prompts-modal')

@endsection

@push('scripts')
    <!-- SweetAlert2 para mensajes de generación y toasts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        const moduleId = {{ $module->id ?? 1 }};
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

        function showActionToast(message, type = 'success') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2800,
                    timerProgressBar: true,
                    icon: type === 'error' ? 'error' : (type === 'warning' ? 'warning' : 'success'),
                    title: message
                });
            } else {
                const toast = document.createElement('div');
                toast.className = `table-action-toast ${type}`;
                toast.innerHTML = `<span>${message}</span>`;
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 3000);
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            return String(text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function autoExpandTextarea(el) {
            if (!el) return;
            el.style.height = 'auto';
            el.style.height = (el.scrollHeight) + 'px';
        }

        function handleTabKey(e, el) {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = el.selectionStart;
                const end = el.selectionEnd;
                el.value = el.value.substring(0, start) + "\t" + el.value.substring(end);
                el.selectionStart = el.selectionEnd = start + 1;
            }
        }

        // =========================================================================
        // CONTROLADOR DEL STEPPER INTERACTIVO DE 5 PASOS
        // =========================================================================
        function goToStep(step) {
            if (step < 1 || step > totalSteps) return;
            currentStep = step;

            document.querySelectorAll('.step-nav-btn').forEach(btn => {
                const s = parseInt(btn.getAttribute('data-step'), 10);
                btn.classList.remove('active', 'completed');
                if (s === currentStep) {
                    btn.classList.add('active');
                } else if (s < currentStep) {
                    btn.classList.add('completed');
                }
            });

            document.querySelectorAll('.step-pane-content').forEach(pane => {
                pane.classList.remove('active');
            });
            const targetPane = document.getElementById(`step_pane_${currentStep}`);
            if (targetPane) targetPane.classList.add('active');

            const progress = (currentStep / totalSteps) * 100;
            const bar = document.getElementById('stepperProgressBar');
            if (bar) bar.style.width = `${progress}%`;

            const ind = document.getElementById('stepIndicatorNumber');
            if (ind) ind.textContent = currentStep;

            const btnPrev = document.getElementById('btnStepPrev');
            const btnNext = document.getElementById('btnStepNext');
            if (btnPrev) btnPrev.disabled = (currentStep === 1);
            if (btnNext) {
                if (currentStep === totalSteps) {
                    btnNext.innerHTML = `<span>Finalizar</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>`;
                } else {
                    btnNext.innerHTML = `<span>Siguiente</span><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="9 18 15 12 9 6"/></svg>`;
                }
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function navigateStep(direction) {
            goToStep(currentStep + direction);
        }

        document.addEventListener('keydown', (e) => {
            if (['input', 'textarea', 'select'].includes(document.activeElement.tagName.toLowerCase())) {
                return;
            }
            if (document.activeElement.isContentEditable) {
                return;
            }
            if (e.key === 'ArrowRight') {
                navigateStep(1);
            } else if (e.key === 'ArrowLeft') {
                navigateStep(-1);
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
            if (badge) {
                badge.style.opacity = '0.6';
                const s = badge.querySelector('span');
                if (s) s.textContent = 'Guardando...';
            }

            clearTimeout(anexo2SaveTimer);
            anexo2SaveTimer = setTimeout(() => {
                const payload = {
                    _token: csrfToken,
                    anexo2_data: gatherAnexo2Data()
                };

                fetch("{{ route('modules.ergonomia_reba.anexo2.save', $module->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                    .then(res => res.json())
                    .then(data => {
                        if (badge) {
                            badge.style.opacity = '1';
                            const s = badge.querySelector('span');
                            if (s) s.textContent = 'Guardado';
                        }
                    })
                    .catch(err => {
                        console.error('Error al guardar Anexo 2:', err);
                        if (badge) {
                            badge.style.opacity = '1';
                            const s = badge.querySelector('span');
                            if (s) s.textContent = 'Error al guardar';
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

        let sysoAutoSaveTimer = null;
        function autoSaveSysoField(key, value) {
            // Sincronizar en vivo entre Step 1, Step 3 y Step 4
            if (key === 'profesional_nombre') {
                ['step1_syso_nombre', 'step3_syso_nombre', 'step4_syso_nombre'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.value !== value) el.value = value;
                });
                if (typeof anexo2HeaderData !== 'undefined') anexo2HeaderData.profesional_nombre = value;
            } else if (key === 'profesional_registro') {
                ['step1_syso_reg', 'step3_syso_reg', 'step4_syso_reg'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.value !== value) el.value = value;
                });
                if (typeof anexo2HeaderData !== 'undefined') anexo2HeaderData.profesional_registro = value;
            } else if (key === 'profesional_fecha') {
                ['step1_syso_fecha', 'step3_syso_fecha', 'step4_syso_fecha'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el && el.value !== value) el.value = value;
                });
                if (typeof anexo2HeaderData !== 'undefined') anexo2HeaderData.profesional_fecha = value;
            }

            clearTimeout(sysoAutoSaveTimer);
            sysoAutoSaveTimer = setTimeout(async () => {
                const nom = document.getElementById('step1_syso_nombre')?.value || document.getElementById('step3_syso_nombre')?.value || document.getElementById('step4_syso_nombre')?.value || '';
                const reg = document.getElementById('step1_syso_reg')?.value || document.getElementById('step3_syso_reg')?.value || document.getElementById('step4_syso_reg')?.value || '';
                const fec = document.getElementById('step1_syso_fecha')?.value || document.getElementById('step3_syso_fecha')?.value || document.getElementById('step4_syso_fecha')?.value || '';

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
                    const resp = await fetch("{{ route('modules.ergonomia_reba.anexo2.save', $module->id) }}", {
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
        // PASO 4: CONTROLADOR DE FILAS DINÁMICAS (MEDIDAS GENERALES Y ESPECÍFICAS)
        // =========================================================================
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
                    const resp = await fetch("{{ route('modules.ergonomia_reba.anexo2.save', $module->id) }}", {
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
                    await fetch("{{ route('modules.ergonomia_reba.prompts.save', $module->id) }}", {
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
        // CONTROLADOR DEL MODAL DE PROMPTS Y ASISTENTE IA REBA
        // =========================================================================
        function resetDefaultRebaPrompt() {
            const puesto = @json($selectedMeasurement ? $selectedMeasurement->puesto_trabajo : 'Puesto de Trabajo');
            const scoreFinal = @json($rebaScores['score_final']);
            const scoreA = @json($rebaScores['score_a']);
            const scoreB = @json($rebaScores['score_b']);
            const scoreC = @json($rebaScores['score_c']);
            const scoreActividad = @json($rebaScores['score_actividad']);
            const riskLevel = @json($rebaScores['risk_level']);
            const actionLevel = @json($rebaScores['action_level']);
            const area = @json($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Producción') : 'Producción');
            const trabajador = @json($selectedMeasurement ? (is_array($selectedMeasurement->nombres_trabajadores) ? implode(', ', array_filter($selectedMeasurement->nombres_trabajadores)) : ($selectedMeasurement->nombres_trabajadores ?: 'Carlos Rene Ichuta Ichuta')) : 'Carlos Rene Ichuta Ichuta');
            const tiempo = parseInt(@json($selectedMeasurement ? ($selectedMeasurement->tiempo_exposicion_horas ?: 8) : 8), 10) || 8;

            const defaultPrompt = `Actúa como un especialista senior en Ergonomía Ocupacional y Salud en el Trabajo (SySO).
Realiza una evaluación biomecánica y ergonómica exhaustiva del puesto de trabajo '${puesto}' (Área: ${area}, Trabajador: ${trabajador}, Exposición: ${tiempo} hrs/día), evaluado mediante el método REBA (Rapid Entire Body Assessment - NTP 601 / ISO 11226 / UNE-EN 1005-4) con los siguientes resultados normativos:

1. PUNTUACIÓN FINAL REBA: ${scoreFinal}/15 (Nivel de Riesgo: ${riskLevel}, Acción: ${actionLevel}).
2. Puntuación Grupo A (Tronco, Cuello, Piernas y Carga/Fuerza): ${scoreA}.
3. Puntuación Grupo B (Brazo, Antebrazo, Muñeca y Agarre): ${scoreB}.
4. Puntuación Tabla C (A vs B): ${scoreC}.
5. Puntuación de Actividad Muscular: +${scoreActividad}.

Genera la redacción técnica especializada dividida obligatoriamente en los siguientes 3 apartados:
- '4. Análisis técnico del puesto': Explicación detallada de la carga postural de cuerpo entero, ángulos articulares de tronco, cuello y extremidades superiores e inferiores, fuerza ejercida y esfuerzo estático/repetitivo (redactado en prosa técnica sin asteriscos ni viñetas).
- '5. Observaciones encontradas': Listado de hallazgos críticos observados en el puesto. Cada observación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).
- '6. Recomendaciones por puesto': Medidas ergonómicas correctivas, preventivas, de ingeniería y administrativas prioritarias y viables. Cada recomendación DEBE ir en una línea separada comenzando con el símbolo '• ' (NO uses asteriscos * ni texto continuo).`;

            document.getElementById('prompt_input').value = defaultPrompt;
            showActionToast('Plantilla de prompt cargada.');
        }

        function openRebaPromptsModal() {
            const modal = document.getElementById('rebaPromptsModal');
            if (modal) {
                modal.classList.add('open');
                modal.style.setProperty('display', 'flex', 'important');
                const promptInput = document.getElementById('prompt_input');
                if (promptInput && !promptInput.value.trim()) {
                    resetDefaultRebaPrompt();
                }
            }
        }

        function closeRebaPromptsModal() {
            const modal = document.getElementById('rebaPromptsModal');
            if (modal) {
                modal.classList.remove('open');
                modal.style.setProperty('display', 'none', 'important');
            }
        }

        async function saveRebaPrompts() {
            const btn = document.getElementById('btnSaveRebaPrompts');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = `<span>Guardando...</span>`;
            }

            const payload = {
                evaluation_id: '{{ $selectedMeasurement ? $selectedMeasurement->id : "" }}',
                prompt: document.getElementById('prompt_input').value,
                analisis_tecnico: document.getElementById('analisis_tecnico_input').value,
                observaciones: document.getElementById('observaciones_input').value,
                recomendaciones: document.getElementById('recomendaciones_input').value,
            };

            try {
                const resp = await fetch("{{ route('modules.ergonomia_reba.prompts.save', $module->id) }}", {
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
                    showActionToast('¡Prompts y análisis técnico guardados correctamente!');
                    
                    const elAT = document.getElementById('doc_val_analisis_tecnico_narrative');
                    const elObs = document.getElementById('doc_val_observaciones_narrative');
                    const elRec = document.getElementById('doc_val_recomendaciones_narrative');

                    if (elAT) elAT.innerText = payload.analisis_tecnico;
                    if (elObs) elObs.innerText = payload.observaciones;
                    if (elRec) elRec.innerText = payload.recomendaciones;

                    const btnWord = document.getElementById('btnDownloadStep3Word');
                    if (btnWord) {
                        const hasContent = (payload.analisis_tecnico.trim().length > 0) || 
                                           (payload.observaciones.trim().length > 0) || 
                                           (payload.recomendaciones.trim().length > 0);
                        if (hasContent) {
                            btnWord.disabled = false;
                            btnWord.style.opacity = '1';
                            btnWord.style.cursor = 'pointer';
                            btnWord.removeAttribute('disabled');
                        }
                    }

                    closeRebaPromptsModal();
                } else {
                    showActionToast('Error al guardar: ' + (data.message || 'Intente nuevamente'), 'error');
                }
            } catch (err) {
                console.error('Error saving prompts:', err);
                showActionToast('Error de conexión al guardar los prompts.', 'error');
            } finally {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg><span>Guardar y Aplicar</span>`;
                }
            }
        }

        function copyRebaPromptText() {
            const input = document.getElementById('prompt_input');
            if (input) {
                navigator.clipboard.writeText(input.value).then(() => {
                    showActionToast('¡Prompt copiado al portapapeles!');
                }).catch(() => {
                    input.select();
                    document.execCommand('copy');
                    showActionToast('¡Prompt copiado al portapapeles!');
                });
            }
        }

        // =========================================================================
        // GENERACIÓN INTELIGENTE DE ANÁLISIS TÉCNICO MEDIANTE GOOGLE GEMINI AI
        // =========================================================================
        async function generateRebaAiNarrative() {
            const btnStep3 = document.getElementById('btnGenerateStep3Ai') || document.getElementById('btnStep3GenerateAi');
            const btnStep3Text = document.getElementById('btnGenerateStep3AiText') || document.getElementById('btnStep3GenerateAiText');
            const promptInput = document.getElementById('prompt_input');
            const atInput = document.getElementById('analisis_tecnico_input');
            const obsInput = document.getElementById('observaciones_input');
            const recInput = document.getElementById('recomendaciones_input');

            if (promptInput && !promptInput.value.trim()) {
                resetDefaultRebaPrompt();
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
                                Procesando la evaluación biomecánica y redactando los <strong>Puntos 4, 5 y 6</strong> del Registro de Evaluación REBA...
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
                const response = await fetch("{{ route('modules.ergonomia_reba.generate-ai', $module->id) }}", {
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
                    const rawAT = data.analisis_tecnico || data.data?.analisis_tecnico || '';
                    const rawObs = data.observaciones || data.data?.observaciones || '';
                    const rawRec = data.recomendaciones || data.data?.recomendaciones || '';

                    const cleanObs = formatCleanBullets(rawObs);
                    const cleanRec = formatCleanBullets(rawRec);

                    if (atInput) atInput.value = rawAT;
                    if (obsInput) obsInput.value = cleanObs;
                    if (recInput) recInput.value = cleanRec;

                    // Actualizar en vivo la vista previa del documento Step 3 con resaltado sutil
                    const elAnalisis = document.getElementById('doc_val_analisis_tecnico_narrative');
                    if (elAnalisis) {
                        elAnalisis.textContent = rawAT;
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

        async function clearRebaAiContent() {
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
                await fetch("{{ route('modules.ergonomia_reba.prompts.save', $module->id) }}", {
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

        // =========================================================================
        // GENERACIÓN Y DESCARGA DE WORD (.DOC) - LOS 4 DOCUMENTOS OFICIALES
        // =========================================================================
        
        // 1. Descarga Anexo 2
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

        function downloadAnexo2Doc() {
            const html = generateAnexo2WordHtml();
            const blob = new Blob(['\ufeff' + html], { type: 'application/msword;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Anexo_2_Identificacion_Factores_Ergonomia_REBA.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Anexo 2 para Word.');
        }

        // =========================================================================
        // EXPORTACIÓN UNIVERSAL AL PORTAPAPELES (ALTA FIDELIDAD MICROSOFT WORD)
        // =========================================================================
        function copyHtmlToClipboardUniversal(html, successMsg) {
            const plainText = "ANEXO 2 - IDENTIFICACIÓN DE FACTORES DE RIESGOS DISERGONÓMICOS (MÉTODO REBA)";

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

        // 2. Descarga Evaluación Detallada (Paso 3)
        function generateEvaluacionDetalladaWordHtml() {
            const puesto = @json($selectedMeasurement ? $selectedMeasurement->puesto_trabajo : ($anexo2Data['puesto_trabajo'] ?? 'Operario de Producción'));
            const area = @json($selectedMeasurement ? ($selectedMeasurement->area_sector ?: 'Producción') : ($anexo2Data['area_sector'] ?? 'Producción'));
            const trabajador = @json($selectedMeasurement ? (is_array($selectedMeasurement->nombres_trabajadores) ? implode(', ', array_filter($selectedMeasurement->nombres_trabajadores)) : ($selectedMeasurement->nombres_trabajadores ?: ($anexo2Data['nombre_trabajador'] ?? 'Carlos Rene Ichuta Ichuta'))) : ($anexo2Data['nombre_trabajador'] ?? 'Carlos Rene Ichuta Ichuta'));
            const fecha = @json($selectedMeasurement ? ($selectedMeasurement->date_formatted ?: ($selectedMeasurement->date ?: date('d/m/Y'))) : ($anexo2Data['profesional_fecha'] ?: date('d/m/Y')));
            const actividad = @json($selectedMeasurement ? ($selectedMeasurement->tarea_analizada ?: ($selectedMeasurement->factor_riesgo ?: ($anexo2Data['actividad_principal'] ?? ($anexo2Data['tareas']['tarea_1'] ?? 'Manipulación y traslado manual de cargas')))) : ($anexo2Data['actividad_principal'] ?? 'Manipulación y traslado manual de cargas'));
            const tiempo = parseInt(@json($selectedMeasurement ? ($selectedMeasurement->tiempo_exposicion_horas ?: 8) : 8), 10) || 8;

            const scoreTablaA = document.getElementById('doc_val_score_tabla_a')?.innerText.trim() || @json($rebaScores['score_tabla_a'] ?? $curScoreTablaA);
            const scoreA = document.getElementById('doc_val_score_a')?.innerText.trim() || @json($rebaScores['score_a'] ?? $curScoreA);
            const scoreTablaB = document.getElementById('doc_val_score_tabla_b')?.innerText.trim() || @json($rebaScores['score_tabla_b'] ?? $curScoreTablaB);
            const scoreB = document.getElementById('doc_val_score_b')?.innerText.trim() || @json($rebaScores['score_b'] ?? $curScoreB);
            const scoreC = document.getElementById('doc_val_score_c')?.innerText.trim() || @json($rebaScores['score_c'] ?? $curScoreC);
            const scoreFinal = parseInt(document.getElementById('doc_val_score_final')?.innerText.trim() || @json($rebaScores['score_final'] ?? $curScoreFinal), 10) || 1;

            let riskLevel = 'Inapreciable';
            let actionRequired = 'No es necesaria actuación';
            let finalScoreBg = '#00b050';
            let finalScoreColor = '#ffffff';

            if (scoreFinal >= 11) {
                riskLevel = 'Muy alto';
                actionRequired = 'Es necesaria la actuación de inmediato';
                finalScoreBg = '#ff0000';
                finalScoreColor = '#ffffff';
            } else if (scoreFinal >= 8) {
                riskLevel = 'Alto';
                actionRequired = 'Es necesaria la actuación pronto';
                finalScoreBg = '#ff6600';
                finalScoreColor = '#ffffff';
            } else if (scoreFinal >= 4) {
                riskLevel = 'Medio';
                actionRequired = 'Es necesaria la actuación';
                finalScoreBg = '#ffff00';
                finalScoreColor = '#000000';
            } else if (scoreFinal >= 2) {
                riskLevel = 'Bajo';
                actionRequired = 'Puede ser necesaria la actuación';
                finalScoreBg = '#92d050';
                finalScoreColor = '#000000';
            }

            const elAT = document.getElementById('doc_val_analisis_tecnico_narrative');
            const elObs = document.getElementById('doc_val_observaciones_narrative');
            const elRec = document.getElementById('doc_val_recomendaciones_narrative');

            const analisisTecnico = (elAT ? elAT.innerText.trim() : '') || (document.getElementById('analisis_tecnico_input') ? document.getElementById('analisis_tecnico_input').value : '') || @json($promptsData['analisis_tecnico'] ?? '');
            const observaciones = (elObs ? elObs.innerText.trim() : '') || (document.getElementById('observaciones_input') ? document.getElementById('observaciones_input').value : '') || @json($promptsData['observaciones'] ?? '');
            const recomendaciones = (elRec ? elRec.innerText.trim() : '') || (document.getElementById('recomendaciones_input') ? document.getElementById('recomendaciones_input').value : '') || @json($promptsData['recomendaciones'] ?? '');

            const profesionalNombre = document.getElementById('step3_syso_nombre')?.value || document.getElementById('step1_syso_nombre')?.value || @json($anexo2Data['profesional_nombre'] ?? '');
            const profesionalRegistro = document.getElementById('step3_syso_reg')?.value || document.getElementById('step1_syso_reg')?.value || @json($anexo2Data['profesional_registro'] ?? '');
            const profesionalFecha = document.getElementById('step3_syso_fecha')?.value || document.getElementById('step1_syso_fecha')?.value || @json($anexo2Data['profesional_fecha'] ?? '');

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
          MEDIANTE EL MÉTODO REBA EN PUESTOS OPERATIVOS
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

  <!-- 2. Resultados del Método REBA por puesto -->
  <p class="sec-title">2. Resultados del Método REBA por puesto</p>
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="width: 100%; margin-bottom: 6pt;">
    <tr class="th-blue">
      <th width="16%" align="center" style="width: 16%; text-align: center; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Grupo evaluado</th>
      <th width="58%" align="left" style="width: 58%; text-align: left; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Descripción</th>
      <th width="26%" align="center" style="width: 26%; text-align: center; background-color: #deebf7; font-weight: bold; padding: 2pt 4pt;">Puntaje obtenido</th>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Tabla A</td>
      <td style="padding: 2pt 4pt;">Cuello, Tronco y Piernas</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreTablaA}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Puntuación A</td>
      <td style="padding: 2pt 4pt;">Puntuación Grupo A + Carga / Fuerza</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreA}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Tabla B</td>
      <td style="padding: 2pt 4pt;">Brazo, Antebrazo y Muñeca</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreTablaB}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Puntuación B</td>
      <td style="padding: 2pt 4pt;">Puntuación Grupo B + Agarre</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreB}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Tabla C</td>
      <td style="padding: 2pt 4pt;">Puntuación combinada Tabla A y Tabla B</td>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">${scoreC}</td>
    </tr>
    <tr>
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">Tabla Final</td>
      <td style="font-weight: bold; padding: 2pt 4pt;">Resultado final REBA (Tabla C + Actividad)</td>
      <td align="center" style="text-align: center; font-weight: bold; background-color: ${finalScoreBg}; color: ${finalScoreColor}; padding: 2pt 4pt;">${scoreFinal}</td>
    </tr>
  </table>

  <!-- 3. Interpretación del nivel de riesgo -->
  <p class="sec-title">3. Interpretación del nivel de riesgo</p>
  <table width="82%" align="center" border="1" cellspacing="0" cellpadding="0" style="width: 82%; margin: 0 auto; margin-bottom: 6pt;">
    <tr>
      <th width="22%" align="center" style="width: 22%; text-align: center; font-weight: bold; padding: 2pt 4pt; background-color: #ffffff;">Puntaje final REBA</th>
      <th width="22%" align="center" style="width: 22%; text-align: center; font-weight: bold; padding: 2pt 4pt; background-color: #ffffff;">Nivel de riesgo</th>
      <th width="56%" align="center" style="width: 56%; text-align: center; font-weight: bold; padding: 2pt 4pt; background-color: #ffffff;">Acción requerida</th>
    </tr>
    <tr style="background-color: #fff2cc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">1</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Inapreciable</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">No es necesaria actuación</td>
    </tr>
    <tr style="background-color: #fce4d6;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">2 a 3</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Bajo</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Puede ser necesaria la actuación</td>
    </tr>
    <tr style="background-color: #f8cecc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">4 a 7</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Medio</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Es necesaria la actuación</td>
    </tr>
    <tr style="background-color: #f8cecc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">8 a 10</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Alto</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Es necesaria la actuación pronto</td>
    </tr>
    <tr style="background-color: #f8cecc;">
      <td align="center" style="text-align: center; font-weight: bold; padding: 2pt 4pt;">11 a 15</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Muy alto</td>
      <td align="center" style="text-align: center; padding: 2pt 4pt;">Es necesaria la actuación de inmediato</td>
    </tr>
  </table>

  <!-- 4. Análisis técnico del puesto -->
  <p class="sec-title">4. Análisis técnico del puesto</p>
  ${analisisTecnico ? `<p class="MsoNormal" style="margin-top: 2pt; margin-bottom: 4pt; text-align: justify; line-height: 1.25;">${escapeHtml(analisisTecnico)}</p>` : ''}
  <table width="75%" align="center" border="0" cellspacing="0" cellpadding="0" style="width: 75%; margin: 4pt auto 6pt auto; border: none;">
    <tr>
      <td width="38%" style="border: none; padding: 1pt 0;">Puntuación final REBA de:</td>
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
            a.download = 'Registro_Evaluacion_Detallada_Metodo_REBA.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Evaluación Detallada REBA para Word.');
        }

        // 3. Descarga Registro 3
        function generateRegistro3WordHtml() {
            const razonSocial = document.getElementById('step4_val_razon_social')?.innerText.trim() || @json($companyName ?? 'SURGE SRL - CASA MATRIZ SENKATA');
            const direccion = document.getElementById('step4_val_direccion')?.innerText.trim() || @json($companyAddress ?? '25 de Julio Alejandria Nº 8345 UV Edificio');
            const area = document.getElementById('step4_val_area')?.innerText.trim() || 'Producción';
            const puesto = document.getElementById('step4_val_puesto')?.innerText.trim() || 'Operario de Producción';
            const tarea = document.getElementById('step4_val_tarea')?.innerText.trim() || 'Manipulación y traslado manual de cargas';
            const trabajador = document.getElementById('step4_val_trabajador')?.innerText.trim() || 'Carlos Rene Ichuta Ichuta';

            function formatDocMultiline(val) {
                if (!val) return '';
                return escapeHtml(val).replace(/\r\n/g, '<br>').replace(/\n/g, '<br>');
            }

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
            const profesionalNombre = document.getElementById('step4_syso_nombre')?.value || @json($anexo2Data['profesional_nombre'] ?? '');
            const profesionalRegistro = document.getElementById('step4_syso_reg')?.value || @json($anexo2Data['profesional_registro'] ?? '');
            const profesionalFecha = document.getElementById('step4_syso_fecha')?.value || @json($anexo2Data['profesional_fecha'] ?? '');

            return `<!DOCTYPE html>
<html xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
<head>
<meta charset="utf-8">
<style>
  @page WordSection1 { size: 612.0pt 792.0pt; margin: 36.0pt 36.0pt 36.0pt 36.0pt; }
  div.WordSection1 { page: WordSection1; }
  body { font-family: Arial, sans-serif; font-size: 9.0pt; color: #000000; margin: 0; padding: 0; }
  table { border-collapse: collapse; width: 100%; border: 1.0pt solid #000000; font-family: Arial, sans-serif; font-size: 8.5pt; }
  th, td { border: 1.0pt solid #000000; padding: 2.5pt 4.0pt; font-family: Arial, sans-serif; font-size: 8.5pt; color: #000000; }
  .th-blue { background-color: #deebf7; font-weight: bold; text-align: center; }
  p.MsoNormal { margin: 0cm; font-family: Arial, sans-serif; font-size: 9.0pt; }
</style>
</head>
<body>
<div class="WordSection1">
  <table width="100%" border="1" cellspacing="0" cellpadding="0">
    <tr>
      <td align="center" style="padding: 4pt 6pt; text-align: center;">
        <p class="MsoNormal" align="center" style="text-align: center; font-weight: bold; font-size: 9.5pt; text-transform: uppercase;">
          REGISTRO Nº 3: IDENTIFICACIÓN DE MEDIDAS CORRECTIVAS Y PREVENTIVAS
        </p>
      </td>
    </tr>
  </table>
  <p class="MsoNormal" style="margin: 4pt 0; font-size: 4pt;">&nbsp;</p>
  <table width="100%" border="1" cellspacing="0" cellpadding="0">
    <tr>
      <td width="26%">Razón Social:</td>
      <td width="38%" style="font-weight: bold;">${escapeHtml(razonSocial)}</td>
      <td width="36%" align="center">Nombre/s del trabajador/es:</td>
    </tr>
    <tr>
      <td width="26%">Dirección de la empresa o establecimiento:</td>
      <td width="38%" style="font-weight: bold;">${escapeHtml(direccion)}</td>
      <td width="36%" rowspan="4" align="center" valign="middle" style="font-weight: bold; font-size: 9.0pt; text-align: center;">${escapeHtml(trabajador)}</td>
    </tr>
    <tr>
      <td width="26%">Área y Sector en estudio:</td>
      <td width="38%" style="font-weight: bold;">${escapeHtml(area)}</td>
    </tr>
    <tr>
      <td width="26%">Puesto de Trabajo:</td>
      <td width="38%" style="font-weight: bold;">${escapeHtml(puesto)}</td>
    </tr>
    <tr>
      <td width="26%">Tarea analizada:</td>
      <td width="38%" style="font-weight: bold;">${escapeHtml(tarea)}</td>
    </tr>
  </table>
  <p class="MsoNormal" style="margin: 4pt 0; font-size: 4pt;">&nbsp;</p>
  <table width="100%" border="1" cellspacing="0" cellpadding="0" style="table-layout: fixed;">
    <colgroup>
      <col width="5.5%" style="width: 5.5%;">
      <col width="54.5%" style="width: 54.5%;">
      <col width="13%" style="width: 13%;">
      <col width="5.5%" style="width: 5.5%;">
      <col width="5.5%" style="width: 5.5%;">
      <col width="16%" style="width: 16%;">
    </colgroup>
    <tr>
      <th colspan="6" align="center" style="font-weight: bold; padding: 3pt 4pt; font-size: 9.0pt;">Medidas Correctivas y Preventivas (M.C.P.)</th>
    </tr>
    <tr class="th-blue">
      <th>N°</th>
      <th>Medidas Preventivas Generales</th>
      <th>Fecha</th>
      <th>SI</th>
      <th>NO</th>
      <th>Observaciones</th>
    </tr>
    ${genRowsHtml}
    <tr class="th-blue">
      <th>N°</th>
      <th colspan="4">Medidas Correctivas y Preventivas Específicas (Administrativas y de Ingeniería)</th>
      <th>Observaciones</th>
    </tr>
    ${espRowsHtml}
  </table>
  <p class="MsoNormal" style="margin: 4pt 0; font-size: 4pt;">&nbsp;</p>
  <table width="100%" border="1" cellspacing="0" cellpadding="0">
    <tr>
      <td style="padding: 4pt 6pt; min-height: 36pt;">
        <p class="MsoNormal" style="font-weight: bold; margin-bottom: 2pt;">Observaciones:</p>
        <p class="MsoNormal">${formatDocMultiline(observacionesGenerales)}</p>
      </td>
    </tr>
  </table>
  <p class="MsoNormal" style="margin: 6pt 0; font-size: 6pt;">&nbsp;</p>
  <table width="60%" align="center" border="1" cellspacing="0" cellpadding="0" style="margin: 0 auto;">
    <tr class="th-blue">
      <th colspan="2" align="center">PROFESIONAL CON REGISTRO SySO vigente</th>
    </tr>
    <tr>
      <td width="32%" style="font-weight: bold;">Nombre:</td>
      <td width="68%">${escapeHtml(profesionalNombre)}</td>
    </tr>
    <tr>
      <td width="32%" style="font-weight: bold;">N° Registro:</td>
      <td width="68%">${escapeHtml(profesionalRegistro)}</td>
    </tr>
    <tr>
      <td width="32%" style="font-weight: bold;">Fecha:</td>
      <td width="68%">${escapeHtml(profesionalFecha)}</td>
    </tr>
    <tr style="height: 36pt;">
      <td width="32%" style="font-weight: bold; vertical-align: top;">Firma:</td>
      <td width="68%" style="height: 36pt;">&nbsp;</td>
    </tr>
  </table>
</div>
</body>
</html>`;
        }

        function downloadRegistro3Doc() {
            const html = generateRegistro3WordHtml();
            const blob = new Blob(['\ufeff' + html], { type: 'application/msword;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'Registro_3_Identificacion_Medidas_Correctivas_Preventivas_REBA.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
            showActionToast('¡Descarga iniciada! Registro Nº 3 para Word.');
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.syso-live-textarea').forEach(el => {
                autoExpandTextarea(el);
            });
        });
    </script>
@endpush
