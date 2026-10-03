<!-- Encabezado Técnico: Datos de Inspección y Resumen de Campo (Sin equipo asignado) -->
<div class="technical-summary-grid">
    <!-- Tarjeta Izquierda: Instalación, Fechas y Monitoreo (Edición Directa Inline) -->
    <div class="tech-header-card">
        <div class="tech-card-header-bar">
            <div class="tech-card-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                    <line x1="16" y1="2" x2="16" y2="6" />
                    <line x1="8" y1="2" x2="8" y2="6" />
                    <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                <span>Datos Técnicos de la Inspección</span>
            </div>
            <span id="headerAutoSaveBadge" class="header-auto-save-status">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <polyline points="20 6 9 17 4 12" />
                </svg>
                <span>Guardado</span>
            </span>
        </div>
        <table class="tech-table-grid">
            <tr>
                <th class="tech-label-cell">INSTALACIÓN:</th>
                <td class="tech-val-cell">
                    <input type="text" id="inline_installation_name" class="tech-inline-input"
                        value="{{ $installationName }}" placeholder="Nombre de la instalación o empresa..."
                        title="Haz clic para editar la instalación" onchange="autoSaveHeaderField()">
                </td>
            </tr>
            <tr>
                <th class="tech-label-cell">FECHA DE INICIO:</th>
                <td class="tech-val-cell">
                    <input type="date" id="inline_start_date" class="tech-inline-input tech-val-mono"
                        value="{{ $startDateRaw }}" title="Fecha de inicio de la inspección"
                        onchange="autoSaveHeaderField()">
                </td>
            </tr>
            <tr>
                <th class="tech-label-cell">FECHA FINALIZACIÓN:</th>
                <td class="tech-val-cell">
                    <input type="date" id="inline_end_date" class="tech-inline-input tech-val-mono"
                        value="{{ $endDateRaw }}" title="Fecha de finalización de la inspección"
                        onchange="autoSaveHeaderField()">
                </td>
            </tr>
            <tr>
                <th class="tech-label-cell">TIPO MONITOREO:</th>
                <td class="tech-val-cell">
                    <input type="text" id="inline_monitoring_type" class="tech-inline-input"
                        value="{{ $monitoringType }}" placeholder="Ej: Inspección en Campo, Diagnóstico..."
                        title="Escribe el tipo de inspección" onchange="autoSaveHeaderField()">
                </td>
            </tr>
        </table>
    </div>

    <!-- Tarjeta Derecha: Resumen de Registro de Campo y Personal Técnico -->
    <div class="tech-header-card">
        <div class="tech-card-header-bar">
            <div class="tech-card-title">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <polyline points="16 11 18 13 22 9" />
                </svg>
                <span>Resumen Operativo de Campo</span>
            </div>
            <span style="font-size: 11px; font-weight: 700; color: #0284c7; background: #e0f2fe; padding: 2px 7px; border-radius: 6px;">
                Módulo Activo
            </span>
        </div>
        <table class="tech-table-grid" style="margin-bottom: 12px;">
            <tr>
                <th class="tech-label-cell" style="width: 32%;">REGISTRADO POR:</th>
                <td class="tech-val-cell">
                    <div style="font-weight: 700; color: var(--ink); font-size: 12.5px; padding-left: 8px;">
                        {{ $registeredByHeader }}
                    </div>
                </td>
            </tr>
            <tr>
                <th class="tech-label-cell">EMPRESA:</th>
                <td class="tech-val-cell">
                    <div style="font-weight: 600; color: #475569; font-size: 12px; padding-left: 8px;">
                        {{ ($module->project && $module->project->company) ? $module->project->company->name : 'Pachabol' }}
                    </div>
                </td>
            </tr>
        </table>

        <div class="tech-stat-pills">
            <div class="tech-stat-pill">
                <div class="tech-stat-number" id="statsPointsCount">{{ $totalPoints }}</div>
                <div class="tech-stat-label">Puntos de Inspección</div>
            </div>
            <div class="tech-stat-pill">
                <div class="tech-stat-number" id="statsPhotosCount" style="color: #0ea5e9;">{{ $totalPhotos }}</div>
                <div class="tech-stat-label">Fotografías Registradas</div>
            </div>
            <div class="tech-stat-pill">
                <div class="tech-stat-number" id="statsGpsCount" style="color: #059669;">{{ $pointsWithGps }}</div>
                <div class="tech-stat-label">Puntos con GPS</div>
            </div>
        </div>
    </div>
</div>
