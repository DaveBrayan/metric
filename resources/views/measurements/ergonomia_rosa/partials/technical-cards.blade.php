    <!-- 2. Encabezado Técnico Dual con Edición Directa Inline y Simetría Perfecta -->
    <div class="technical-summary-grid">
        <!-- Tarjeta Izquierda: Instalación, Fechas y Monitoreo (Directamente Editables) -->
        <div class="tech-header-card">
            <div class="tech-card-header-bar">
                <div class="tech-card-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    <span>Datos Técnicos del Monitoreo</span>
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
                            value="{{ $startDateRaw }}" title="Fecha de inicio del estudio ergonómico ROSA"
                            onchange="autoSaveHeaderField()">
                    </td>
                </tr>
                <tr>
                    <th class="tech-label-cell">FECHA DE FINALIZACIÓN:</th>
                    <td class="tech-val-cell">
                        <input type="date" id="inline_end_date" class="tech-inline-input tech-val-mono"
                            value="{{ $endDateRaw }}" title="Fecha de finalización del estudio"
                            onchange="autoSaveHeaderField()">
                    </td>
                </tr>
                <tr>
                    <th class="tech-label-cell">TIPO DE MONITOREO:</th>
                    <td class="tech-val-cell">
                        <input type="text" id="inline_monitoring_type" class="tech-inline-input"
                            value="{{ $monitoringType }}" placeholder="Ej: Ergonomía ROSA (Rapid Office Strain Assessment)..."
                            title="Escribe el tipo de monitoreo" onchange="autoSaveHeaderField()">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Tarjeta Derecha: Instrumentación Asignada en 2 Columnas Flex (Info 4 filas + Foto al 100% de altura) -->
        <div class="tech-header-card">
            <div class="tech-card-header-bar">
                <div class="tech-card-title">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="9" />
                        <circle cx="12" cy="12" r="3" />
                        <line x1="12" y1="3" x2="12" y2="9" />
                    </svg>
                    <span>Instrumento / Equipo Asignado</span>
                </div>
            </div>
            <div class="tech-card-body-flex">
                <table class="tech-table-grid tech-table-equipment">
                    <tr>
                        <th class="tech-label-cell" style="width: 36%;">EQUIPO:</th>
                        <td class="tech-val-cell">
                            <div class="tech-val-static">{{ $equipmentName }}</div>
                        </td>
                    </tr>
                    <tr>
                        <th class="tech-label-cell">MARCA:</th>
                        <td class="tech-val-cell">
                            <div class="tech-val-static">{{ $equipmentBrand }}</div>
                        </td>
                    </tr>
                    <tr>
                        <th class="tech-label-cell">MODELO:</th>
                        <td class="tech-val-cell">
                            <div class="tech-val-static tech-val-mono">{{ $equipmentModel }}</div>
                        </td>
                    </tr>
                    <tr>
                        <th class="tech-label-cell">SERIE:</th>
                        <td class="tech-val-cell">
                            <div class="tech-val-static tech-val-mono" style="color: #0284c7;">{{ $equipmentSerial }}</div>
                        </td>
                    </tr>
                </table>
                <div class="tech-eq-photo-container">
                    @if($equipmentImage)
                        <img src="{{ $equipmentImage }}" alt="{{ $equipmentName }}" title="{{ $equipmentName }}">
                    @else
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; color: #94a3b8; height: 100%; text-align: center;">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#cbd5e1" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="14" x="2" y="3" rx="2" />
                                <line x1="8" x2="16" y1="21" y2="21" />
                                <line x1="12" x2="12" y1="17" y2="21" />
                            </svg>
                            <span style="font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Sin Imagen</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
