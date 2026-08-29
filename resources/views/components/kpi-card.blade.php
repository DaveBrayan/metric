@props(['kpi', 'index' => 1])

<div class="glass-card kpi-modern-card kpi-theme-{{ $kpi['theme'] ?? 'cyan' }} wow-entrance stagger-{{ $index ?? 1 }}">
    <!-- Top Meta Row -->
    <div class="kpi-head">
        <div class="kpi-icon-glow" aria-hidden="true">
            @if(($kpi['icon'] ?? '') === 'folder')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
                </svg>
            @elseif(($kpi['icon'] ?? '') === 'activity')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
            @elseif(($kpi['icon'] ?? '') === 'user-group')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
            @elseif(($kpi['icon'] ?? '') === 'shield')
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <polyline points="9 12 11 14 15 10"/>
                </svg>
            @else
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            @endif
        </div>

        <div class="kpi-sparkle-pill">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="18 15 12 9 6 15"></polyline>
            </svg>
            <span>{{ $kpi['growth'] }}</span>
        </div>
    </div>
    
    <!-- Numeric Metric Content -->
    <div class="kpi-body">
        <div class="kpi-metric-title">{{ $kpi['title'] }}</div>
        <div class="kpi-metric-number" data-countup-target="{{ $kpi['raw_number'] ?? $kpi['value'] }}">{{ $kpi['value'] }}</div>
        
        <div class="kpi-footer-metric">
            <span class="kpi-comparison-text">{{ $kpi['comparison'] ?? 'Monitoreo Activo' }}</span>
            
            <!-- Micro Sparkline SVG -->
            <svg class="kpi-micro-sparkline" width="64" height="20" viewBox="0 0 64 20" fill="none">
                @if(($kpi['theme'] ?? '') === 'lime')
                    <path d="M2 16L14 13L28 15L42 7L54 9L62 3" stroke="#91c51b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                @else
                    <path d="M2 17L16 11L28 13L40 6L52 8L62 2" stroke="#10b9df" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                @endif
            </svg>
        </div>
    </div>
</div>
