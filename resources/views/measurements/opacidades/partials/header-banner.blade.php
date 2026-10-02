<div class="opacity-header-banner">
    <div>
        <h1>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.5 2.8C2.1 10.7 2 10.9 2 11.2V16c0 .6.4 1 1 1h2" />
                <circle cx="7" cy="17" r="2" />
                <path d="M9 17h6" />
                <circle cx="17" cy="17" r="2" />
            </svg>
            <span>Monitoreo de Opacidad</span>
        </h1>
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
            title="Ver mapa con todos los puntos georreferenciados">
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

        <!-- Botón Informe (Planilla técnica oficial de medición y evaluación de opacidad) -->
        <a href="{{ route('modules.opacity.report', $module->id) }}" class="btn-secondary-subtle"
            title="Ver planilla de características y resultados de opacidad en formato horizontal">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
                <line x1="16" y1="13" x2="8" y2="13" />
                <line x1="16" y1="17" x2="8" y2="17" />
                <polyline points="10 9 9 9 8 9" />
            </svg>
            <span>Informe</span>
        </a>

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