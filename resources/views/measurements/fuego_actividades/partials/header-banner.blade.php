<div class="illumination-header-banner">
    <div>
        <h1>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path
                    d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 3.5z" />
            </svg>
            <span>Carga de Fuego por Actividad</span>
        </h1>
    </div>

    <div class="header-action-group">
        <a href="{{ route('modules.index', ['proyecto' => $module->project_id ?? null]) }}"
            class="btn-secondary-subtle">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="19" y1="12" x2="5" y2="12" />
                <polyline points="12 19 5 12 12 5" />
            </svg>
            <span>Volver a Módulos</span>
        </a>

        <!-- Botón Ubicaciones (Modal con todos los sectores y técnicos) -->
        <button type="button" class="btn-secondary-subtle" onclick="openAllLocationsModal()"
            title="Ver mapa satelital con todos los sectores y personal que registró">
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


        <button type="button" class="btn-primary-hero-action" onclick="openCreateMeasurementModal()">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            <span>Nuevo Sector / Área</span>
        </button>
    </div>
</div>