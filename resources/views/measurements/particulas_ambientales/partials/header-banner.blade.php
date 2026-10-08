<div class="illumination-header-banner">
    <div>
        <h1>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
            <span>Partículas Ambientales</span>
        </h1>
        <p>Monitoreo y evaluación de calidad del aire exterior (PM10, PM2.5 & PTS — Ley 1333 / OMS)</p>
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
            title="Ver mapa con todas las estaciones perimetrales de partículas ambientales">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                <circle cx="12" cy="10" r="3" />
            </svg>
            <span>Ubicaciones</span>
        </button>

        <!-- Botón Tablas Normativas -->
        <button type="button" class="btn-secondary-subtle" onclick="openNormativeTablesModal()"
            title="Ver límites permisibles de calidad de aire (Ley 1333 RMCA y OMS)">
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
            title="Exportar planilla técnica oficial o catálogo fotográfico de partículas ambientales">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            <span>Exportar</span>
        </button>

        <!-- Botón Informe (Planilla técnica oficial con Stepper) -->
        <a href="{{ route('modules.particulas_ambientales.report', $module->id) }}" class="btn-secondary-subtle"
            title="Ver informe oficial de calidad del aire y partículas ambientales con Stepper dinámico">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3"
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
            <span>Nueva Estación Ambiental</span>
        </button>
    </div>
</div>
