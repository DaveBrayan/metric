<!-- Modern Animated & Optimized Sidebar (METRIC_V2) -->
<aside class="sidebar" id="sidebarDrawer" aria-label="Navegación principal">
    <!-- Brand Header -->
    <div class="brand-header">
        <a href="{{ route('dashboard') }}" class="brand-left-info" style="text-decoration: none;" title="Metric v2 Pachabol">
            <div class="brand-logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Pachabol Logo" class="brand-logo-img">
            </div>
            <div class="brand-text-block">
                <div class="brand-title">
                    <span>Metric</span>
                    <span class="v2-sup">v2</span>
                </div>
                <span class="brand-badge">PACHABOL</span>
            </div>
        </a>

        <!-- Animated Collapse Toggle Button (Shortcut: Alt+S) -->
        <button 
            type="button"
            class="btn-collapse-toggle" 
            onclick="toggleSidebarCollapse()" 
            title="Contraer / Expandir menú (Alt + S)" 
            aria-label="Contraer menú lateral"
        >
            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
        </button>
    </div>

    <!-- Scrollable Navigation Container -->
    <div class="nav-scroll-container">
        <!-- Section 1: Principal -->
        <div class="nav-group-section">
            <div class="nav-section-title">Principal</div>
            <a href="{{ route('dashboard') }}" class="nav-item-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="7" height="9" x="3" y="3" rx="1"/>
                    <rect width="7" height="5" x="14" y="3" rx="1"/>
                    <rect width="7" height="9" x="14" y="12" rx="1"/>
                    <rect width="7" height="5" x="3" y="16" rx="1"/>
                </svg>
                <span class="nav-item-text">Dashboard</span>
            </a>
        </div>

        <!-- Section 2: Organización (1. Empresas, 2. Regionales, 3. Responsables, 4. Personal) -->
        <div class="nav-group-section">
            <div class="nav-section-title">Organización</div>
            <a href="{{ route('companies.index') }}" class="nav-item-btn {{ request()->routeIs('companies.*') ? 'active' : '' }}" data-tooltip="Empresas & Clientes">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
                </svg>
                <span class="nav-item-text">Empresas</span>
            </a>

            <a href="{{ route('regions.index') }}" class="nav-item-btn {{ request()->routeIs('regions.*') ? 'active' : '' }}" data-tooltip="Sedes Regionales & Plantas">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span class="nav-item-text">Regionales</span>
            </a>

            <a href="{{ route('managers.index') }}" class="nav-item-btn {{ request()->routeIs('managers.*') ? 'active' : '' }}" data-tooltip="Responsables Técnicos & de Planta">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span class="nav-item-text">Responsables</span>
            </a>

            <a href="{{ route('staff.index') }}" class="nav-item-btn {{ request()->routeIs('staff.*') ? 'active' : '' }}" data-tooltip="Personal & Colaboradores">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span class="nav-item-text">Personal</span>
            </a>
        </div>

        <!-- Section 3: Proyectos & Submódulos -->
        <div class="nav-group-section">
            <div class="nav-section-title">Proyectos</div>
            <a href="{{ route('projects.index') }}" class="nav-item-btn {{ request()->routeIs('projects.index') ? 'active' : '' }}" data-tooltip="Proyectos Activos">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                </svg>
                <span class="nav-item-text">Proyectos Activos</span>
                <span class="nav-badge-pill pulse">36</span>
            </a>

            <!-- Submenu Módulos -->
            <a href="{{ route('modules.index') }}" class="nav-item-btn nav-subitem-indent {{ request()->routeIs('modules.*') ? 'active' : '' }}" data-tooltip="Módulos: Ruido, Agua, Opacidad, Partículas">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 17px; height: 17px;">
                    <polyline points="9 18 15 12 9 6"/>
                </svg>
                <span class="nav-item-text">Módulos</span>
            </a>
        </div>

        <!-- Section 4: Sistema -->
        <div class="nav-group-section">
            <div class="nav-section-title">Sistema</div>
            <a href="{{ route('admins.index') }}" class="nav-item-btn {{ request()->routeIs('admins.*') ? 'active' : '' }}" data-tooltip="Administradores y Roles">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span class="nav-item-text">Administradores</span>
            </a>

            <a href="{{ route('settings.index') }}" class="nav-item-btn {{ request()->routeIs('settings.*') ? 'active' : '' }}" data-tooltip="Configuración General">
                <svg class="nav-svg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <span class="nav-item-text">Configuración</span>
            </a>
        </div>
    </div>

    <!-- Corporate User Workspace Footer -->
    <div class="sidebar-user-footer">
        <div class="user-footer-card" onclick="window.location.href='{{ route('settings.index') }}'" title="Ver Perfil y Configuración">
            <div class="user-footer-avatar">
                <span>{{ substr($userName ?? 'Reynaldo', 0, 1) }}</span>
                <span class="online-dot"></span>
            </div>
            <div class="user-footer-meta">
                <div class="user-footer-name">{{ $userName ?? 'Reynaldo' }}</div>
                <div class="user-footer-role">Administrador</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="user-footer-logout-btn" title="Cerrar Sesión">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>
