@props(['distribution'])

<div class="glass-card panel-box wow-entrance stagger-6">
    <div class="panel-title-bar">
        <h3>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#91c51b" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21.21 15.89A10 10 0 1 1 8 2.83"/>
                <path d="M22 12A10 10 0 0 0 12 2v10z"/>
            </svg>
            <span>Módulos de Medición</span>
        </h3>
        <a href="{{ route('modules.index') }}" class="btn-subtle-link" style="text-decoration: none;">
            Ver Módulos
        </a>
    </div>
    
    <div class="donut-wrapper">
        <!-- Floating Conic Ring -->
        <div class="donut-aura" title="Distribución de muestreos por línea ambiental" style="background: conic-gradient(#10b9df 0deg 108deg, #91c51b 108deg 198deg, #0896b5 198deg 270deg, #7ca817 270deg 324deg, #94a3b8 324deg 360deg);">
            <div class="donut-center-metric">
                <div class="count">{{ $distribution['total'] ?? 142 }}</div>
                <div class="label">Muestreos</div>
            </div>
        </div>
        
        <!-- Legend Breakdown List -->
        <div class="donut-legend-stack">
            @foreach($distribution['items'] ?? [] as $item)
                <div class="donut-row-item">
                    <span class="donut-bullet" style="background: {{ $item['color'] }}; box-shadow: 0 0 6px {{ $item['color'] }};"></span>
                    <b>{{ $item['label'] }}</b>
                    <small>{{ $item['percentage'] }}% ({{ $item['count'] }})</small>
                </div>
            @endforeach
        </div>
    </div>
</div>
