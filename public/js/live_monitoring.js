/**
 * resources/js/live_monitoring.js
 * METRIC v2 — Real-Time Live Monitoring Radar Engine
 */

(function () {
    'use strict';

    window.LiveRadar = {
        projectId: null,
        dataUrl: '',
        map: null,
        markersLayer: null,
        markersMap: new Map(),
        allPoints: new Map(),
        activeModules: new Set(),
        selectedStaff: 'all',
        searchTerm: '',
        dateStart: null,
        dateEnd: null,
        activePointId: null,
        isolatedPointId: null,
        expandedDetailsIds: new Set(),
        currentPhotoPointId: null,
        currentPhotoIndex: 0,
        currentPhotoList: [],
        isDrawerOpen: false,
        pollTimer: null,
        isPolling: true,
        pollIntervalMs: 3000,
        isFirstLoad: true,
        lastDataHash: '',

        init: function (config) {
            this.projectId = config.projectId;
            this.dataUrl = config.dataUrl;
            this.activeModules = new Set(config.initialModuleKeys || []);

            this.initMap();
            this.initFilters();
            this.initControls();
            this.startPolling();
        },

        initMap: function () {
            // Capas Base: Calles (OSM) y Satélite (Esri World Imagery)
            this.osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19
            });

            this.satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '&copy; Esri &copy; Maxar, Earthstar Geographics',
                maxZoom: 19
            });

            // Centro inicial por defecto (La Paz / Bolivia o coordenadas del proyecto)
            this.map = L.map('liveMonitoringMap', {
                center: [-16.5000, -68.1500],
                zoom: 14,
                layers: [this.osmLayer],
                zoomControl: true,
                fadeAnimation: true,
                zoomAnimation: true,
                markerZoomAnimation: true
            });

            this.markersLayer = L.layerGroup().addTo(this.map);

            // Layer Switcher Buttons: Solo Calles y Satélite
            document.querySelectorAll('.map-layer-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelectorAll('.map-layer-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');
                    const layerKey = btn.dataset.layer;

                    this.map.removeLayer(this.osmLayer);
                    this.map.removeLayer(this.satelliteLayer);

                    if (layerKey === 'satellite') {
                        this.satelliteLayer.addTo(this.map);
                    } else {
                        this.osmLayer.addTo(this.map);
                    }
                });
            });
        },

        initFilters: function () {
            const btnSidebarToggle = document.getElementById('btnToggleSidebarFilter');
            const sidebarPanel = document.getElementById('sidebarFilterPanel');
            const btnApplySidebar = document.getElementById('btnApplySidebarFilters');
            const btnResetAll = document.getElementById('btnResetAllFilters');
            const dateStartInput = document.getElementById('filterDateStart');
            const dateEndInput = document.getElementById('filterDateEnd');
            const btnClearDate = document.getElementById('btnClearDateRange');
            const staffSelect = document.getElementById('staffFilterSelect');

            // Abrir / Cerrar Panel Desplegable de Filtros en el Sidebar
            if (btnSidebarToggle && sidebarPanel) {
                btnSidebarToggle.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isHidden = (sidebarPanel.style.display === 'none' || !sidebarPanel.classList.contains('show'));
                    if (isHidden) {
                        sidebarPanel.style.display = 'flex';
                        void sidebarPanel.offsetWidth; // Force reflow
                        sidebarPanel.classList.add('show');
                        btnSidebarToggle.classList.add('active');
                    } else {
                        sidebarPanel.classList.remove('show');
                        btnSidebarToggle.classList.remove('active');
                        setTimeout(() => {
                            if (!sidebarPanel.classList.contains('show')) {
                                sidebarPanel.style.display = 'none';
                            }
                        }, 200);
                    }
                });

                btnApplySidebar?.addEventListener('click', () => {
                    sidebarPanel.classList.remove('show');
                    btnSidebarToggle.classList.remove('active');
                    setTimeout(() => {
                        if (!sidebarPanel.classList.contains('show')) {
                            sidebarPanel.style.display = 'none';
                        }
                    }, 200);
                });

                document.addEventListener('click', (e) => {
                    if (sidebarPanel.classList.contains('show') && !sidebarPanel.contains(e.target) && !btnSidebarToggle.contains(e.target)) {
                        sidebarPanel.classList.remove('show');
                        btnSidebarToggle.classList.remove('active');
                        setTimeout(() => {
                            if (!sidebarPanel.classList.contains('show')) {
                                sidebarPanel.style.display = 'none';
                            }
                        }, 200);
                    }
                });
            }

            // Checkboxes de Módulos (Pills)
            document.querySelectorAll('.module-filter-checkbox').forEach(chk => {
                chk.addEventListener('change', (e) => {
                    const key = e.target.value;
                    const itemWrap = e.target.closest('.module-filter-pill');
                    if (e.target.checked) {
                        this.activeModules.add(key);
                        itemWrap?.classList.add('active');
                    } else {
                        this.activeModules.delete(key);
                        itemWrap?.classList.remove('active');
                    }
                    this.applyFilters();
                });
            });

            // Seleccionar Todos / Ninguno
            document.getElementById('btnSelectAllModules')?.addEventListener('click', () => {
                document.querySelectorAll('.module-filter-checkbox').forEach(chk => {
                    chk.checked = true;
                    this.activeModules.add(chk.value);
                    chk.closest('.module-filter-pill')?.classList.add('active');
                });
                this.applyFilters();
            });

            document.getElementById('btnSelectNoneModules')?.addEventListener('click', () => {
                document.querySelectorAll('.module-filter-checkbox').forEach(chk => {
                    chk.checked = false;
                    chk.closest('.module-filter-pill')?.classList.remove('active');
                });
                this.activeModules.clear();
                this.applyFilters();
            });

            // Rango de Fechas (Desde - Hasta)
            dateStartInput?.addEventListener('change', (e) => {
                this.dateStart = e.target.value ? e.target.value : null;
                this.applyFilters();
            });

            dateEndInput?.addEventListener('change', (e) => {
                this.dateEnd = e.target.value ? e.target.value : null;
                this.applyFilters();
            });

            btnClearDate?.addEventListener('click', () => {
                if (dateStartInput) dateStartInput.value = '';
                if (dateEndInput) dateEndInput.value = '';
                this.dateStart = null;
                this.dateEnd = null;
                this.applyFilters();
            });

            // Filtro de Personal / Técnico
            staffSelect?.addEventListener('change', (e) => {
                this.selectedStaff = e.target.value;
                this.applyFilters();
            });

            // Botón Restablecer Todos los Filtros
            btnResetAll?.addEventListener('click', () => {
                // 1. Módulos
                document.querySelectorAll('.module-filter-checkbox').forEach(chk => {
                    chk.checked = true;
                    this.activeModules.add(chk.value);
                    chk.closest('.module-filter-pill')?.classList.add('active');
                });
                // 2. Fechas
                if (dateStartInput) dateStartInput.value = '';
                if (dateEndInput) dateEndInput.value = '';
                this.dateStart = null;
                this.dateEnd = null;
                // 3. Personal
                if (staffSelect) staffSelect.value = 'all';
                this.selectedStaff = 'all';

                this.applyFilters();
            });

            this.updateFilterBadges();
        },

        updateFilterBadges: function () {
            const badge = document.getElementById('filterActiveBadge');
            const totalModules = document.querySelectorAll('.module-filter-checkbox').length;
            const activeCount = this.activeModules.size;

            if (badge) {
                badge.textContent = activeCount;
                const isFiltered = (activeCount < totalModules) || (this.selectedStaff !== 'all') || (this.dateStart !== null) || (this.dateEnd !== null);
                if (isFiltered) {
                    badge.classList.add('filtering');
                } else {
                    badge.classList.remove('filtering');
                }
            }
        },

        initControls: function () {
            // Buscador en la barra izquierda
            const searchInput = document.getElementById('sidebarSearchInput');
            const btnClearSearch = document.getElementById('btnClearSearch');

            searchInput?.addEventListener('input', (e) => {
                this.searchTerm = e.target.value.trim().toLowerCase();
                if (btnClearSearch) {
                    btnClearSearch.style.display = this.searchTerm ? 'block' : 'none';
                }
                this.applyFilters();
            });

            btnClearSearch?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                this.searchTerm = '';
                btnClearSearch.style.display = 'none';
                this.applyFilters();
            });

            // Fit Bounds Button
            document.getElementById('btnFitBounds')?.addEventListener('click', () => {
                this.fitBounds();
            });

            // Fullscreen Button (Pantalla Completa en todo el panel)
            const wrapper = document.querySelector('.live-monitoring-wrapper');
            document.getElementById('btnToggleFullscreen')?.addEventListener('click', () => {
                if (!document.fullscreenElement) {
                    if (wrapper.requestFullscreen) {
                        wrapper.requestFullscreen().catch(err => console.warn(err));
                    } else if (wrapper.webkitRequestFullscreen) {
                        wrapper.webkitRequestFullscreen();
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen().catch(err => console.warn(err));
                    }
                }
            });

            document.addEventListener('fullscreenchange', () => {
                const isFs = !!document.fullscreenElement;
                wrapper.classList.toggle('fullscreen-active', isFs);
                if (!isFs) {
                    this.toggleSidebarDrawer(false);
                }
                setTimeout(() => {
                    this.map.invalidateSize();
                }, 300);
            });

            // Floating Bubble Toggle (Para abrir Drawer en Pantalla Completa)
            document.getElementById('btnFloatingFeedToggle')?.addEventListener('click', () => {
                this.toggleSidebarDrawer(!this.isDrawerOpen);
            });

            document.getElementById('btnCloseSidebarDrawer')?.addEventListener('click', () => {
                this.toggleSidebarDrawer(false);
            });

            // Pause / Resume Polling
            const btnPause = document.getElementById('btnTogglePolling');
            btnPause?.addEventListener('click', () => {
                this.isPolling = !this.isPolling;
                if (this.isPolling) {
                    btnPause.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="4" width="4" height="16"></rect>
                            <rect x="14" y="4" width="4" height="16"></rect>
                        </svg>
                        <span>Pausar Radar</span>
                    `;
                    document.getElementById('livePulseBadge')?.classList.remove('paused');
                    this.startPolling();
                } else {
                    btnPause.innerHTML = `
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <span>Reanudar Radar</span>
                    `;
                    document.getElementById('livePulseBadge')?.classList.add('paused');
                    if (this.pollTimer) clearInterval(this.pollTimer);
                }
            });

            // Controles del Visor de Fotografías / Lightbox Modal
            document.getElementById('photoModalBackdrop')?.addEventListener('click', () => this.closePhotoModal());
            document.getElementById('btnClosePhotoModal')?.addEventListener('click', () => this.closePhotoModal());
            document.getElementById('btnPhotoPrev')?.addEventListener('click', () => this.prevPhoto());
            document.getElementById('btnPhotoNext')?.addEventListener('click', () => this.nextPhoto());
            document.getElementById('btnOpenPhotoNewTab')?.addEventListener('click', () => {
                if (this.currentPhotoList && this.currentPhotoList[this.currentPhotoIndex]) {
                    window.open(this.currentPhotoList[this.currentPhotoIndex], '_blank');
                }
            });

            // Navegación por teclado (Flechas y Escape)
            window.addEventListener('keydown', (e) => {
                const modal = document.getElementById('livePhotoModal');
                if (!modal || modal.style.display === 'none' || !modal.classList.contains('show')) return;

                if (e.key === 'Escape') {
                    this.closePhotoModal();
                } else if (e.key === 'ArrowLeft') {
                    this.prevPhoto();
                } else if (e.key === 'ArrowRight') {
                    this.nextPhoto();
                }
            });
        },

        toggleSidebarDrawer: function (open) {
            this.isDrawerOpen = !!open;
            const sidebar = document.getElementById('liveSidebar');
            if (sidebar) {
                sidebar.classList.toggle('drawer-open', this.isDrawerOpen);
            }
        },

        startPolling: function () {
            this.fetchData();
            if (this.pollTimer) clearInterval(this.pollTimer);
            this.pollTimer = setInterval(() => {
                if (this.isPolling) {
                    this.fetchData();
                }
            }, this.pollIntervalMs);
        },

        fetchData: function () {
            const url = this.dataUrl;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) return;

                const measurements = data.measurements || [];
                let newIncomingCount = 0;
                let latestPoint = null;

                // Generar hash simple para detectar si cambiaron los datos
                const currentHash = measurements.map(m => m.id + '_' + m.time_ago).join('|');
                const hasChanged = (currentHash !== this.lastDataHash);

                measurements.forEach(pt => {
                    const exists = this.allPoints.has(pt.id);
                    if (!exists) {
                        this.allPoints.set(pt.id, pt);
                        if (!this.isFirstLoad) {
                            newIncomingCount++;
                            latestPoint = pt;
                        }
                    } else {
                        this.allPoints.set(pt.id, pt);
                    }
                });

                if (hasChanged || this.isFirstLoad) {
                    this.lastDataHash = currentHash;
                    this.renderMarkers();
                    this.renderFeed();
                    this.updateKpis();
                }

                if (this.isFirstLoad) {
                    this.isFirstLoad = false;
                    this.fitBounds();
                } else if (newIncomingCount > 0 && latestPoint) {
                    this.triggerLiveAlert(latestPoint, newIncomingCount);
                }
            })
            .catch(err => {
                console.warn('[LiveRadar] Error en polling:', err);
            });
        },

        renderMarkers: function () {
            const currentMapPoints = this.getMapPoints();
            const visibleIds = new Set(currentMapPoints.map(p => p.id));

            // Eliminar marcadores que ya no están visibles
            this.markersMap.forEach((marker, id) => {
                if (!visibleIds.has(id)) {
                    this.markersLayer.removeLayer(marker);
                    this.markersMap.delete(id);
                }
            });

            // Agregar o actualizar marcadores visibles
            currentMapPoints.forEach(pt => {
                if (!pt.has_coordinates || pt.latitude == null || pt.longitude == null) return;

                if (!this.markersMap.has(pt.id)) {
                    const markerIcon = this.createCustomMarkerIcon(pt);
                    const marker = L.marker([pt.latitude, pt.longitude], { 
                        icon: markerIcon,
                        riseOnHover: true
                    });

                    // Tooltip limpio y no intrusivo al pasar el ratón
                    marker.bindTooltip(`<strong>${pt.module_name} #${pt.point_number}</strong><br><span style="color: #64748b; font-size: 11px;">${pt.area}</span>`, {
                        direction: 'top',
                        offset: [0, -28],
                        className: 'live-marker-tooltip'
                    });

                    // Al hacer clic en el marcador, centramos y expandimos sus detalles en la barra izquierda
                    marker.on('click', () => {
                        this.flyToPoint(pt.id);
                        this.expandCardDetails(pt.id);
                    });

                    this.markersLayer.addLayer(marker);
                    this.markersMap.set(pt.id, marker);
                }
            });
        },

        createCustomMarkerIcon: function (pt) {
            const color = pt.module_color || '#0284c7';
            const num = pt.point_number || '01';

            const html = `
                <div class="custom-live-marker" id="marker_el_${pt.id}">
                    <div class="marker-focus-ring" style="border-color: ${color};"></div>
                    <div class="marker-pin-bubble" style="background: ${color};">
                        <span>${num}</span>
                    </div>
                </div>
            `;

            return L.divIcon({
                className: 'custom-live-marker-wrap',
                html: html,
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });
        },

        renderFeed: function () {
            const feedContainer = document.getElementById('liveActivityFeed');
            if (!feedContainer) return;

            const filtered = this.getFilteredPoints();

            if (filtered.length === 0) {
                feedContainer.innerHTML = `
                    <div style="text-align: center; color: #94a3b8; padding: 28px 10px; font-size: 12.5px;">
                        No se encontraron puntos con los filtros o búsqueda actuales.
                    </div>
                `;
                return;
            }

            let html = '';
            filtered.forEach(pt => {
                const color = pt.module_color || '#0284c7';
                const isActive = (this.activePointId === pt.id) ? 'active' : '';
                const isIsolated = (this.isolatedPointId === pt.id);
                const isExpanded = this.expandedDetailsIds.has(pt.id);

                const photosHtml = (pt.images && pt.images.length > 0)
                    ? `<div class="card-detail-photos">
                        ${pt.images.map((img, idx) => `<img src="${img}" class="card-detail-photo-thumb" onclick="event.stopPropagation(); LiveRadar.openPhotoModal('${pt.id}', ${idx})" alt="Foto" title="Clic para ampliar en visor HD" />`).join('')}
                       </div>`
                    : '';

                const coordsHtml = pt.utm_easting && pt.utm_northing
                    ? `<div class="card-detail-row">
                        <span class="card-detail-label">UTM (${pt.utm_zone}):</span>
                        <span class="card-detail-val">E: ${Math.round(pt.utm_easting)}, N: ${Math.round(pt.utm_northing)}</span>
                       </div>`
                    : '';

                html += `
                    <div class="live-feed-card ${isActive} ${isIsolated ? 'is-isolated-card' : ''}" id="feed_item_${pt.id}" onclick="LiveRadar.flyToPoint('${pt.id}')" title="Centrar punto en mapa">
                        <div class="live-feed-top">
                            <span class="live-feed-tag" style="background: ${color};">
                                ${pt.module_name} #${pt.point_number}
                            </span>
                            <span class="live-feed-time">${pt.time_ago}</span>
                        </div>
                        <div class="live-feed-area">${pt.area} ${pt.workstation && pt.workstation !== '-' ? '— ' + pt.workstation : ''}</div>
                        <div class="live-feed-val" style="color: ${color};">${pt.summary_value}</div>
                        
                        <!-- Fila de Acciones: Técnico + Switch Mostrar Solo Este -->
                        <div class="live-feed-bottom-row">
                            <div class="live-feed-tech">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                                <span>${pt.registered_by}</span>
                            </div>
                            
                            <!-- Switch Mostrar solo este -->
                            <label class="isolate-switch-wrap" onclick="event.stopPropagation();" title="Ocultar los demás puntos y ver solo este en el mapa">
                                <input type="checkbox" class="isolate-switch-input" ${isIsolated ? 'checked' : ''} onchange="LiveRadar.toggleIsolatePoint('${pt.id}', this.checked)" />
                                <span class="isolate-switch-track">
                                    <span class="isolate-switch-thumb"></span>
                                </span>
                                <span class="isolate-switch-label">${isIsolated ? 'Solo este' : 'Mostrar solo este'}</span>
                            </label>
                        </div>

                        <!-- Botón Mostrar / Ocultar Detalles -->
                        <div class="card-details-toggle-bar">
                            <button type="button" class="btn-toggle-card-details ${isExpanded ? 'active' : ''}" onclick="event.stopPropagation(); LiveRadar.toggleCardDetails('${pt.id}')">
                                <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="chevron-icon">
                                    <polyline points="6 9 12 15 18 9"></polyline>
                                </svg>
                                <span>${isExpanded ? 'Ocultar detalles' : 'Mostrar detalles'}</span>
                            </button>
                        </div>

                        <!-- Panel Desplegable de Detalles Completos -->
                        <div class="card-expandable-details ${isExpanded ? 'expanded' : ''}" id="details_box_${pt.id}">
                            <div class="card-details-inner">
                                <div class="card-detail-row">
                                    <span class="card-detail-label">Área / Puesto:</span>
                                    <span class="card-detail-val">${pt.area} ${pt.workstation && pt.workstation !== '-' ? '— ' + pt.workstation : ''}</span>
                                </div>
                                <div class="card-detail-row">
                                    <span class="card-detail-label">Medición / Estado:</span>
                                    <span class="card-detail-val-highlight" style="color: ${color};">${pt.summary_value}</span>
                                </div>
                                ${pt.secondary_value ? `
                                <div class="card-detail-row">
                                    <span class="card-detail-label">Detalle:</span>
                                    <span class="card-detail-val">${pt.secondary_value}</span>
                                </div>` : ''}
                                <div class="card-detail-row">
                                    <span class="card-detail-label">Registrado Por:</span>
                                    <span class="card-detail-val">${pt.registered_by}</span>
                                </div>
                                <div class="card-detail-row">
                                    <span class="card-detail-label">Fecha y Hora:</span>
                                    <span class="card-detail-val">${pt.date_formatted} (${pt.time_ago})</span>
                                </div>
                                ${coordsHtml}
                                ${photosHtml}
                            </div>
                        </div>
                    </div>
                `;
            });

            feedContainer.innerHTML = html;
        },

        toggleCardDetails: function (pointId) {
            const box = document.getElementById(`details_box_${pointId}`);
            const btn = document.querySelector(`#feed_item_${pointId} .btn-toggle-card-details`);

            if (this.expandedDetailsIds.has(pointId)) {
                this.expandedDetailsIds.delete(pointId);
                box?.classList.remove('expanded');
                btn?.classList.remove('active');
                if (btn) btn.querySelector('span').textContent = 'Mostrar detalles';
            } else {
                this.expandedDetailsIds.add(pointId);
                box?.classList.add('expanded');
                btn?.classList.add('active');
                if (btn) btn.querySelector('span').textContent = 'Ocultar detalles';
            }
        },

        expandCardDetails: function (pointId) {
            this.expandedDetailsIds.add(pointId);
            const box = document.getElementById(`details_box_${pointId}`);
            const btn = document.querySelector(`#feed_item_${pointId} .btn-toggle-card-details`);
            box?.classList.add('expanded');
            btn?.classList.add('active');
            if (btn) btn.querySelector('span').textContent = 'Ocultar detalles';

            // Si estamos en pantalla completa y el drawer está cerrado, lo abrimos
            const wrapper = document.querySelector('.live-monitoring-wrapper');
            if (wrapper && wrapper.classList.contains('fullscreen-active')) {
                this.toggleSidebarDrawer(true);
            }
        },

        highlightFeedItem: function (pointId) {
            this.activePointId = pointId;
            document.querySelectorAll('.live-feed-card').forEach(c => c.classList.remove('active'));
            const activeCard = document.getElementById(`feed_item_${pointId}`);
            if (activeCard) {
                activeCard.classList.add('active');
                activeCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        },

        getFilteredPoints: function () {
            const list = Array.from(this.allPoints.values());

            return list.filter(pt => {
                // 1. Filtro de módulos
                const moduleMatch = this.activeModules.has(pt.module_key);
                if (!moduleMatch) return false;

                // 2. Filtro de personal
                const staffMatch = (this.selectedStaff === 'all') || (pt.registered_by === this.selectedStaff);
                if (!staffMatch) return false;

                // 3. Filtro de rango de fechas (Desde - Hasta)
                if (this.dateStart || this.dateEnd) {
                    const ptDateStr = pt.created_at_iso || pt.date_formatted;
                    const ptTime = new Date(ptDateStr).getTime();

                    if (!isNaN(ptTime)) {
                        if (this.dateStart) {
                            const startTs = new Date(this.dateStart + 'T00:00:00').getTime();
                            if (ptTime < startTs) return false;
                        }
                        if (this.dateEnd) {
                            const endTs = new Date(this.dateEnd + 'T23:59:59.999').getTime();
                            if (ptTime > endTs) return false;
                        }
                    }
                }

                // 4. Buscador en vivo
                if (this.searchTerm) {
                    const area = (pt.area || '').toLowerCase();
                    const ws = (pt.workstation || '').toLowerCase();
                    const mod = (pt.module_name || '').toLowerCase();
                    const tech = (pt.registered_by || '').toLowerCase();
                    const val = (pt.summary_value || '').toLowerCase();
                    const sec = (pt.secondary_value || '').toLowerCase();
                    const pNum = (pt.point_number || '').toString().toLowerCase();

                    const match = area.includes(this.searchTerm) ||
                                  ws.includes(this.searchTerm) ||
                                  mod.includes(this.searchTerm) ||
                                  tech.includes(this.searchTerm) ||
                                  val.includes(this.searchTerm) ||
                                  sec.includes(this.searchTerm) ||
                                  pNum.includes(this.searchTerm);
                    if (!match) return false;
                }

                return true;
            });
        },

        getMapPoints: function () {
            if (this.isolatedPointId) {
                const single = this.allPoints.get(this.isolatedPointId);
                return (single && single.has_coordinates && single.latitude != null && single.longitude != null) ? [single] : [];
            }
            return this.getFilteredPoints().filter(p => p.has_coordinates && p.latitude != null && p.longitude != null);
        },

        toggleIsolatePoint: function (pointId, enable) {
            if (enable) {
                this.isolatedPointId = pointId;
                this.activePointId = pointId;
                this.renderMarkers();
                this.renderFeed();
                this.updateIsolationBanner();
                this.flyToPoint(pointId);
                this.expandCardDetails(pointId);
            } else {
                this.clearIsolation();
            }
        },

        clearIsolation: function () {
            this.isolatedPointId = null;
            this.renderMarkers();
            this.renderFeed();
            this.updateIsolationBanner();
            this.fitBounds();
        },

        updateIsolationBanner: function () {
            const banner = document.getElementById('mapIsolationBanner');
            const bannerText = document.getElementById('isolationBannerText');
            if (!banner) return;

            if (this.isolatedPointId) {
                const pt = this.allPoints.get(this.isolatedPointId);
                if (pt && bannerText) {
                    bannerText.innerHTML = `Modo Enfoque: <strong>${pt.module_name} #${pt.point_number}</strong> (<em>${pt.area}</em>)`;
                }
                banner.style.display = 'flex';
            } else {
                banner.style.display = 'none';
            }
        },

        applyFilters: function () {
            this.renderMarkers();
            this.renderFeed();
            this.updateKpis();
            this.updateFilterBadges();
        },

        updateKpis: function () {
            const filtered = this.getFilteredPoints();
            const geoCount = filtered.filter(p => p.has_coordinates).length;

            const elTotal = document.getElementById('kpiTotalGeoPoints');
            if (elTotal) elTotal.textContent = geoCount;

            const elActiveMods = document.getElementById('kpiActiveModulesCount');
            if (elActiveMods) elActiveMods.textContent = this.activeModules.size;

            const elLastTime = document.getElementById('kpiLastSyncTime');
            if (elLastTime && filtered.length > 0) {
                elLastTime.textContent = filtered[0].time_ago;
            }
        },

        fitBounds: function () {
            const filtered = this.getFilteredPoints().filter(p => p.has_coordinates && p.latitude && p.longitude);
            if (filtered.length === 0) return;

            const bounds = L.latLngBounds(filtered.map(p => [p.latitude, p.longitude]));
            this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 17 });
        },

        /**
         * Centrado suave, sin temblores ni saltos bruscos
         */
        flyToPoint: function (pointId) {
            const pt = this.allPoints.get(pointId);
            if (!pt || !pt.latitude || !pt.longitude) return;

            this.highlightFeedItem(pointId);

            const marker = this.markersMap.get(pointId);
            const targetLatLng = L.latLng(pt.latitude, pt.longitude);
            const currentZoom = this.map.getZoom();
            const targetZoom = currentZoom < 15 ? 16 : currentZoom;

            // Trigger smooth ripple animation on the specific marker
            const markerEl = document.getElementById(`marker_el_${pointId}`);
            if (markerEl) {
                markerEl.classList.remove('marker-active-pulse');
                void markerEl.offsetWidth; // Trigger reflow for re-triggering animation
                markerEl.classList.add('marker-active-pulse');
                setTimeout(() => {
                    markerEl.classList.remove('marker-active-pulse');
                }, 2500);
            }

            // Distancia del centro actual al punto
            const currentCenter = this.map.getCenter();
            const distance = currentCenter.distanceTo(targetLatLng);

            if (distance < 20 && currentZoom >= 15) {
                return;
            }

            if (currentZoom < 15) {
                this.map.setView(targetLatLng, targetZoom, {
                    animate: true,
                    duration: 0.45
                });
            } else {
                this.map.panTo(targetLatLng, {
                    animate: true,
                    duration: 0.35,
                    easeLinearity: 0.25
                });
            }
        },

        // =========================================================================
        // TOP-RIGHT RADAR TOAST NOTIFICATION
        // =========================================================================
        triggerLiveAlert: function (pt, totalNew) {
            const container = document.getElementById('liveToastStack');
            if (!container) return;

            const color = pt.module_color || '#10b981';
            const toast = document.createElement('div');
            toast.className = 'live-toast-card';
            toast.style.borderColor = color;

            toast.innerHTML = `
                <div class="live-toast-icon-wrap" style="color: ${color}; background: ${color}1a;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"></path>
                        <path d="M2 12h20"></path>
                    </svg>
                </div>
                <div class="live-toast-info">
                    <div class="live-toast-title">
                        <span>¡Nuevo Registro en Vivo!</span>
                        <span style="font-size: 10.5px; color: #10b981; font-weight: 800;">● EN VIVO</span>
                    </div>
                    <div class="live-toast-desc">
                        <strong>${pt.module_name}</strong> en <em>${pt.area}</em>
                    </div>
                    <div class="live-toast-meta">
                        Valor: <span style="color: ${color}; font-weight: 800;">${pt.summary_value}</span> • Por: ${pt.registered_by}
                    </div>
                    <button type="button" class="btn-toast-fly" onclick="LiveRadar.flyToPoint('${pt.id}'); LiveRadar.expandCardDetails('${pt.id}'); this.closest('.live-toast-card').remove();">
                        Ver en Lista 📍
                    </button>
                </div>
                <div class="live-toast-timer-bar" style="background: ${color};"></div>
            `;

            container.appendChild(toast);

            setTimeout(() => {
                if (toast.parentNode) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateX(50px)';
                    toast.style.transition = 'all 0.3s ease';
                    setTimeout(() => toast.remove(), 300);
                }
            }, 6000);
        },

        // =========================================================================
        // 8. VISOR DE FOTOGRAFÍAS / LIGHTBOX MODAL MULTI-FOTO CON FLECHAS
        // =========================================================================
        openPhotoModal: function (pointId, photoIndex = 0) {
            const pt = this.allPoints.get(pointId);
            if (!pt || !pt.images || pt.images.length === 0) return;

            this.currentPhotoPointId = pointId;
            this.currentPhotoList = pt.images;
            this.currentPhotoIndex = (photoIndex >= 0 && photoIndex < pt.images.length) ? photoIndex : 0;

            const modal = document.getElementById('livePhotoModal');
            if (!modal) return;

            modal.style.display = 'flex';
            void modal.offsetWidth; // Trigger reflow for animation
            modal.classList.add('show');

            this.renderCurrentPhoto();
        },

        renderCurrentPhoto: function () {
            const pt = this.allPoints.get(this.currentPhotoPointId);
            if (!pt || !this.currentPhotoList || this.currentPhotoList.length === 0) return;

            const color = pt.module_color || '#0284c7';
            const totalPhotos = this.currentPhotoList.length;
            const currentIdx = this.currentPhotoIndex;
            const currentUrl = this.currentPhotoList[currentIdx];

            // 1. Badge & Contador
            const badge = document.getElementById('photoPointBadge');
            if (badge) {
                badge.textContent = `${pt.module_name} #${pt.point_number} — ${pt.area}`;
                badge.style.background = color;
            }

            const counter = document.getElementById('photoCounterText');
            if (counter) {
                counter.textContent = totalPhotos > 1 ? `Foto ${currentIdx + 1} de ${totalPhotos}` : 'Fotografía';
            }

            // 2. Imagen Principal
            const mainImg = document.getElementById('photoMainImg');
            if (mainImg) {
                mainImg.style.opacity = '0';
                mainImg.src = currentUrl;
                mainImg.onload = () => {
                    mainImg.style.opacity = '1';
                };
            }

            // 3. Flechas de Navegación (Solo si hay más de 1 foto)
            const btnPrev = document.getElementById('btnPhotoPrev');
            const btnNext = document.getElementById('btnPhotoNext');
            if (btnPrev && btnNext) {
                if (totalPhotos > 1) {
                    btnPrev.style.display = 'grid';
                    btnNext.style.display = 'grid';
                } else {
                    btnPrev.style.display = 'none';
                    btnNext.style.display = 'none';
                }
            }

            // 4. Tira de Miniaturas Inferior
            const thumbsStrip = document.getElementById('photoThumbsStrip');
            if (thumbsStrip) {
                if (totalPhotos > 1) {
                    thumbsStrip.style.display = 'flex';
                    thumbsStrip.innerHTML = this.currentPhotoList.map((imgUrl, idx) => `
                        <img src="${imgUrl}" 
                             class="photo-thumb-item ${idx === currentIdx ? 'active' : ''}" 
                             onclick="LiveRadar.goToPhoto(${idx})" 
                             alt="Miniatura ${idx + 1}" />
                    `).join('');
                } else {
                    thumbsStrip.style.display = 'none';
                    thumbsStrip.innerHTML = '';
                }
            }
        },

        goToPhoto: function (index) {
            if (index >= 0 && index < this.currentPhotoList.length) {
                this.currentPhotoIndex = index;
                this.renderCurrentPhoto();
            }
        },

        prevPhoto: function () {
            if (!this.currentPhotoList || this.currentPhotoList.length <= 1) return;
            this.currentPhotoIndex = (this.currentPhotoIndex - 1 + this.currentPhotoList.length) % this.currentPhotoList.length;
            this.renderCurrentPhoto();
        },

        nextPhoto: function () {
            if (!this.currentPhotoList || this.currentPhotoList.length <= 1) return;
            this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.currentPhotoList.length;
            this.renderCurrentPhoto();
        },

        closePhotoModal: function () {
            const modal = document.getElementById('livePhotoModal');
            if (modal) {
                modal.classList.remove('show');
                setTimeout(() => {
                    if (!modal.classList.contains('show')) {
                        modal.style.display = 'none';
                        const mainImg = document.getElementById('photoMainImg');
                        if (mainImg) mainImg.src = '';
                    }
                }, 200);
            }
        }
    };
})();
