<div class="glass-card panel-box wow-entrance stagger-5">
    <!-- Panel Header with Vector Icon and Time Range Selector -->
    <div class="panel-title-bar">
        <h3>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                <polyline points="16 7 22 7 22 13"/>
            </svg>
            <span>Curva de Monitoreo & Muestreos Ambientales</span>
        </h3>
        <div class="chart-time-pills-group">
            <button type="button" class="time-pill" onclick="handleChartTimeFilter('24H', this)">24H</button>
            <button type="button" class="time-pill active" onclick="handleChartTimeFilter('7D', this)">7D</button>
            <button type="button" class="time-pill" onclick="handleChartTimeFilter('30D', this)">30D</button>
            <button type="button" class="time-pill" onclick="handleChartTimeFilter('1A', this)">1A</button>
        </div>
    </div>
    
    <!-- SVG Area Spline Chart -->
    <div class="svg-chart-container">
        <svg class="interactive-svg" id="dashboardTelemetrySvg" viewBox="0 0 700 260" preserveAspectRatio="none">
            <defs>
                <!-- Cyan Area Fill Gradient -->
                <linearGradient id="cyanAreaGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#10b9df" stop-opacity="0.35" />
                    <stop offset="70%" stop-color="#10b9df" stop-opacity="0.05" />
                    <stop offset="100%" stop-color="#10b9df" stop-opacity="0" />
                </linearGradient>

                <!-- Lime Area Fill Gradient -->
                <linearGradient id="limeAreaGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#91c51b" stop-opacity="0.28" />
                    <stop offset="70%" stop-color="#91c51b" stop-opacity="0.04" />
                    <stop offset="100%" stop-color="#91c51b" stop-opacity="0" />
                </linearGradient>

                <!-- Glow Filters for Key Data Nodes -->
                <filter id="glowCyan" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#10b9df" flood-opacity="0.5" />
                </filter>
                <filter id="glowLime" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#91c51b" flood-opacity="0.5" />
                </filter>
            </defs>

            <!-- Horizontal Background Grid Lines -->
            <g stroke="rgba(226, 232, 240, 0.7)" stroke-width="1" stroke-dasharray="4 4">
                <line x1="50" y1="20" x2="680" y2="20" />
                <line x1="50" y1="70" x2="680" y2="70" />
                <line x1="50" y1="120" x2="680" y2="120" />
                <line x1="50" y1="170" x2="680" y2="170" />
                <line x1="50" y1="220" x2="680" y2="220" />
            </g>

            <!-- Y-Axis Labels -->
            <g fill="#94a3b8" font-size="10.5" font-family="'Plus Jakarta Sans', sans-serif" font-weight="600">
                <text x="10" y="24">1,000</text>
                <text x="20" y="74">800</text>
                <text x="20" y="124">600</text>
                <text x="20" y="174">400</text>
                <text x="20" y="224">200</text>
            </g>

            <!-- Lime Smooth Area Path -->
            <path 
                id="chartLimeArea"
                d="M 50 185 C 100 205, 120 210, 155 205 C 190 200, 220 170, 260 165 C 300 160, 330 150, 365 145 C 400 140, 430 115, 470 120 C 510 125, 540 150, 575 145 C 610 140, 640 130, 680 135 L 680 220 L 50 220 Z" 
                fill="url(#limeAreaGradient)" 
            />

            <!-- Lime Smooth Spline Line -->
            <path 
                id="chartLimeLine"
                d="M 50 185 C 100 205, 120 210, 155 205 C 190 200, 220 170, 260 165 C 300 160, 330 150, 365 145 C 400 140, 430 115, 470 120 C 510 125, 540 150, 575 145 C 610 140, 640 130, 680 135" 
                fill="none" 
                stroke="#91c51b" 
                stroke-width="3.5" 
                stroke-linecap="round" 
                stroke-linejoin="round" 
            />

            <!-- Cyan Smooth Area Path -->
            <path 
                id="chartCyanArea"
                d="M 50 120 C 100 155, 120 160, 155 145 C 190 130, 220 110, 260 115 C 300 120, 330 100, 365 95 C 400 90, 430 50, 470 55 C 510 60, 540 75, 575 70 C 610 65, 640 75, 680 78 L 680 220 L 50 220 Z" 
                fill="url(#cyanAreaGradient)" 
            />

            <!-- Cyan Smooth Spline Line -->
            <path 
                id="chartCyanLine"
                d="M 50 120 C 100 155, 120 160, 155 145 C 190 130, 220 110, 260 115 C 300 120, 330 100, 365 95 C 400 90, 430 50, 470 55 C 510 60, 540 75, 575 70 C 610 65, 640 75, 680 78" 
                fill="none" 
                stroke="#10b9df" 
                stroke-width="3.5" 
                stroke-linecap="round" 
                stroke-linejoin="round" 
            />

            <!-- Cyan Glowing Nodes -->
            <g id="chartCyanNodes" fill="#ffffff" stroke="#10b9df" stroke-width="3" filter="url(#glowCyan)">
                <circle cx="50" cy="120" r="5" />
                <circle cx="155" cy="145" r="5" />
                <circle cx="260" cy="115" r="5" />
                <circle cx="365" cy="95" r="5" />
                <circle cx="470" cy="55" r="6" stroke-width="3.5" />
                <circle cx="575" cy="70" r="5" />
                <circle cx="680" cy="78" r="5" />
            </g>

            <!-- Lime Glowing Nodes -->
            <g id="chartLimeNodes" fill="#ffffff" stroke="#91c51b" stroke-width="3" filter="url(#glowLime)">
                <circle cx="50" cy="185" r="4.5" />
                <circle cx="155" cy="205" r="4.5" />
                <circle cx="260" cy="165" r="4.5" />
                <circle cx="365" cy="145" r="4.5" />
                <circle cx="470" cy="120" r="5" stroke-width="3.5" />
                <circle cx="575" cy="145" r="4.5" />
                <circle cx="680" cy="135" r="4.5" />
            </g>

            <!-- X-Axis Date Marks -->
            <g id="chartXAxisMarks" fill="#64748b" font-size="11" font-family="'Plus Jakarta Sans', sans-serif" font-weight="600" text-anchor="middle">
                <text x="50" y="248">18 May</text>
                <text x="155" y="248">19 May</text>
                <text x="260" y="248">20 May</text>
                <text x="365" y="248">21 May</text>
                <text x="470" y="248">22 May</text>
                <text x="575" y="248">23 May</text>
                <text x="680" y="248">24 May</text>
            </g>
        </svg>
    </div>

    <!-- Glowing Legend Tag Bar -->
    <div class="chart-glow-legend">
        <div class="legend-tag">
            <span class="dot-indicator cyan"></span>
            <span>Muestreos de Ruido & Partículas</span>
        </div>
        <div class="legend-tag">
            <span class="dot-indicator lime"></span>
            <span>Calidad de Agua & Control de Emisiones</span>
        </div>
    </div>
</div>
