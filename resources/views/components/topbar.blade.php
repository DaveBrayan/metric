<header class="topbar">
    <div class="topbar-left">
        <!-- Mobile Menu Toggle Button -->
        <button class="btn-menu-drawer" onclick="toggleSidebar()" aria-label="Abrir navegación móvil">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <line x1="3" y1="18" x2="21" y2="18"/>
            </svg>
        </button>

        <!-- Global Search Pill -->
        <div class="search-container">
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="search-icon-pos">
                <circle cx="11" cy="11" r="8"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input 
                type="text" 
                class="search-input-pill" 
                placeholder="Buscar métricas, reportes, proyectos..." 
                oninput="if(typeof searchLiveTable === 'function') searchLiveTable()"
                autocomplete="off"
                aria-label="Búsqueda global"
            />
        </div>
    </div>
    
    <div class="topbar-right">
        <!-- Live Notifications Button -->
        <button type="button" class="btn-icon-circle" onclick="triggerToast('Tienes 3 notificaciones del sistema', '🔔')" title="Notificaciones">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            <i class="pulse-badge"></i>
        </button>

        <!-- Help Center Button -->
        <button type="button" class="btn-icon-circle" onclick="triggerToast('Documentación de Metric v2 disponible', '📘')" title="Ayuda y Documentación">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
            </svg>
        </button>

        <!-- User Profile Pill -->
        <div class="user-pill" onclick="triggerToast('Perfil: {{ $userName ?? 'Reynaldo' }} (Administrador)', '🟢')" title="Perfil y Ajustes de Usuario">
            <div class="user-avatar-glow">
                <span>{{ substr($userName ?? 'Reynaldo', 0, 1) }}</span>
            </div>
            <div>
                <span class="user-name-text">{{ $userName ?? 'Reynaldo' }}</span>
                <span class="user-role-badge">Administrador</span>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 2px;">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </div>
</header>
