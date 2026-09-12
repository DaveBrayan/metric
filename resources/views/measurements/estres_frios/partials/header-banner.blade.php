    <div class="cold-stress-header-banner">
        <div>
            <h1>
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 14.76V3.5a2.5 2.5 0 0 0-5 0v11.26a4.5 4.5 0 1 0 5 0z" />
                    <line x1="2" y1="12" x2="6" y2="12" />
                    <line x1="18" y1="12" x2="22" y2="12" />
                    <line x1="4.93" y1="4.93" x2="7.76" y2="7.76" />
                    <line x1="16.24" y1="16.24" x2="19.07" y2="19.07" />
                </svg>
                <span>Monitoreo de Estrés Térmico (Frío)</span>
            </h1>
            <p>Registro de temperatura del aire, sensación térmica, índice de enfriamiento WCI, tiempo límite de exposición y memoria técnica.</p>
        </div>

        <div class="header-action-group">
            <a href="{{ route('modules.index', ['proyecto' => $module->project_id]) }}" class="btn-secondary-subtle">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                <span>Volver a Módulos</span>
            </a>

            <!-- Botón Ubicaciones (Modal con todos los puntos y técnicos) -->
            <button type="button" class="btn-secondary-subtle" onclick="openAllLocationsModal()"
                title="Ver mapa con todos los puntos y personal que registró">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                    <circle cx="12" cy="10" r="3" />
                </svg>
                <span>Ubicaciones</span>
            </button>

            <!-- Botón Exportar (Planilla Excel y Reporte Fotográfico) -->
            <button type="button" class="btn-secondary-subtle" onclick="openExportModal()"
                title="Exportar planilla técnica oficial o catálogo fotográfico">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.3"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                <span>Exportar</span>
            </button>

            <!-- Botón Ver Tablas (Matriz técnica e informe de evaluación) -->
            <button type="button" class="btn-secondary-subtle" onclick="openTablesModal()"
                title="Ver tabla técnica e informe de evaluación de estrés por frío">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.3"
                    stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2" />
                    <path d="M3 9h18" />
                    <path d="M3 15h18" />
                    <path d="M9 3v18" />
                </svg>
                <span>Ver Tablas</span>
            </button>

            <button type="button" class="btn-primary-hero-action" onclick="openCreateMeasurementModal()">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                    stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19" />
                    <line x1="5" y1="12" x2="19" y2="12" />
                </svg>
                <span>Nuevo Punto de Medición</span>
            </button>
        </div>
    </div>
