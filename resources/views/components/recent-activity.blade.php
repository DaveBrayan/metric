@props(['activities'])

<div class="glass-card panel-box activity-panel-wrap wow-entrance stagger-7">
    <div class="panel-title-bar">
        <h3>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b9df" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
            <span>Telemetría en Vivo</span>
        </h3>
        <span class="live-signal-badge">
            <span class="signal-dot"></span>
            <span>Online</span>
        </span>
    </div>
    
    <div class="activity-timeline">
        @foreach($activities as $activity)
            <div class="timeline-event">
                <div class="avatar-timeline" style="background: {{ $activity['avatar_bg'] }}; @if(!empty($activity['is_report'])) color: #088fa6; @endif">
                    @if(!empty($activity['is_report']))
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                    @else
                        <span>{{ $activity['avatar'] }}</span>
                    @endif
                </div>
                <div>
                    <p>
                        <strong>{{ $activity['user'] }}</strong> {{ $activity['action'] }}<br>
                        @if(!empty($activity['is_report']))
                            <span class="report-chip">{{ $activity['detail'] }}</span>
                        @else
                            <span style="font-weight: 600; color: var(--ink);">{{ $activity['detail'] }}</span>
                        @endif
                    </p>
                    <span class="time-stamp">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: -1px; margin-right: 3px;">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                        {{ $activity['time'] }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</div>
