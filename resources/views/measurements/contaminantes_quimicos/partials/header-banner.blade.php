<div class="illumination-header-banner">
    <div>
        <h1>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 2v7.31L4.89 20a2 2 0 0 0 1.77 3h14.68a2 2 0 0 0 1.77-3L14 9.31V2" />
                <path d="M8.5 2h7" />
                <path d="M7 16h10" />
            </svg>
            <span>Monitoreo de Contaminantes Químicos</span>
        </h1>
        <p>Muestreo y evaluación de agentes químicos, gases, vapores y polvos (NB 510002 / ACGIH / NIOSH)</p>
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
            title="Ver mapa con todos los puntos georreferenciados de muestreo">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                <circle cx="12" cy="10" r="3" />
            </svg>
            <span>Ubicaciones</span>
        </button>

        <!-- Botón Tablas Normativas (LMP Químicos) -->
        <button type="button" class="btn-secondary-subtle" onclick="openNormativeTablesModal()"
            title="Ver límites permisibles y normas de referencia (NB 510002 / ACGIH)">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2" />
                <path d="M3 9h18" />
                <path d="M9 21V9" />
            </svg>
            <span>LMP Normas</span>
        </button>

        <!-- Botón Exportar (Planilla Excel y Reporte Fotográfico) -->
        <button type="button" class="btn-secondary-subtle" onclick="openExportModal()"
            title="Exportar planilla técnica oficial o catálogo fotográfico de contaminantes químicos">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            <span>Exportar</span>
        </button>

        <button type="button" class="btn-primary-hero-action" onclick="openCreateMeasurementModal()">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            <span>Nuevo Punto de Muestreo</span>
        </button>
    </div>
</div>
