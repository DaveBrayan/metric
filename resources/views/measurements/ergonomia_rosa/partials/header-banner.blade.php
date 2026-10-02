<!-- 1. Encabezado y Navegación del Módulo de Ergonomía ROSA -->
<div class="illumination-header-banner">
    <div>
        <h1>
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="3" rx="2" />
                <line x1="8" x2="16" y1="21" y2="21" />
                <line x1="12" x2="12" y1="17" y2="21" />
            </svg>
            <span>Ergonomía ROSA</span>
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

        <!-- Botón Ubicaciones (Modal con todos los puestos y evaluadores) -->
        <button type="button" class="btn-secondary-subtle" onclick="openAllLocationsModal()"
            title="Ver mapa satelital con todos los puestos de oficina evaluados">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z" />
                <circle cx="12" cy="10" r="3" />
            </svg>
            <span>Ubicaciones</span>
        </button>

        <!-- Botón Exportar (Planilla Excel y Reporte Fotográfico) -->
        <button type="button" class="btn-secondary-subtle" onclick="openExportModal()"
            title="Exportar planilla técnica oficial o catálogo fotográfico ROSA">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.3"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="7 10 12 15 17 10" />
                <line x1="12" y1="15" x2="12" y2="3" />
            </svg>
            <span>Exportar</span>
        </button>

        <!-- Botón Prompts (Asistente IA y Análisis Técnico) -->
        <button type="button" class="btn-secondary-subtle" onclick="openRosaPromptsModal()"
            title="Prompts y análisis técnico especializado con Asistente IA">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3Z"/>
                <path d="M18 15h6"/>
                <path d="M21 12v6"/>
            </svg>
            <span>Prompts</span>
        </button>


        <!-- Botón Principal: Nueva Evaluación ROSA (Tema Rose / Crimson) -->
        <button type="button" class="btn-primary-hero-action" onclick="openCreateRosaModal()">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <line x1="5" y1="12" x2="19" y2="12" />
            </svg>
            <span>Nueva Evaluación ROSA</span>
        </button>
    </div>
</div>