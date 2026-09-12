@extends('layouts.app')

@section('title', 'Configuración del Sistema — Metric v2 Pachabol')

@push('styles')
    @metricStyle('settings')
@endpush

@section('content')
    <!-- Settings Header Banner -->
    <div class="settings-header-banner">
        <div>
            <h1>Configuración de la Plataforma</h1>
            <p>Administración de parámetros generales, políticas de seguridad corporativa e integraciones API.</p>
        </div>
        <button type="submit" form="settingsForm" class="btn-primary-hero-action" onclick="triggerToast('Configuración guardada exitosamente', '✓')">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                <polyline points="17 21 17 13 7 13 7 21"/>
                <polyline points="7 3 7 8 15 8"/>
            </svg>
            <span>Guardar Cambios</span>
        </button>
    </div>

    <!-- Navigation Tabs Bar (3 Clean Tabs) -->
    <div class="settings-tabs-bar">
        <button type="button" class="tab-nav-btn active" onclick="switchSettingsTab('tab-general', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="3"/>
                <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
            </svg>
            <span>General & Organización</span>
        </button>

        <button type="button" class="tab-nav-btn" onclick="switchSettingsTab('tab-security', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <span>Seguridad & Acceso</span>
        </button>

        <button type="button" class="tab-nav-btn" onclick="switchSettingsTab('tab-api', this)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="16 18 22 12 16 6"/>
                <polyline points="8 6 2 12 8 18"/>
            </svg>
            <span>API & Integraciones</span>
        </button>
    </div>

    <form id="settingsForm" action="{{ route('settings.update') }}" method="POST">
        @csrf
        
        <!-- Tab 1: General & Organización -->
        <div class="settings-tab-pane active" id="tab-general">
            <div class="settings-form-layout">
                <div>
                    <!-- Organization Info Card -->
                    <div class="glass-card settings-card">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: var(--cyan-soft-gradient); color: var(--cyan); width: 40px; height: 40px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                                    <polyline points="9 22 9 12 15 12 15 22"/>
                                </svg>
                            </div>
                            <div>
                                <h2>Identidad de la Organización</h2>
                                <p>Información corporativa visible en reportes, paneles y comunicaciones.</p>
                            </div>
                        </div>

                        <div class="form-row-grid">
                            <div class="form-field-group">
                                <label class="form-field-label">Nombre de la Empresa</label>
                                <input type="text" name="org_name" class="custom-form-input" value="{{ $settings['general']['org_name'] ?? 'Pachabol S.A.' }}">
                            </div>

                            <div class="form-field-group">
                                <label class="form-field-label">Nombre del Sistema</label>
                                <input type="text" name="system_name" class="custom-form-input" value="{{ $settings['general']['system_name'] ?? 'Metric v2' }}">
                            </div>
                        </div>

                        <div class="form-row-grid">
                            <div class="form-field-group">
                                <label class="form-field-label">Zona Horaria de Operaciones</label>
                                <select name="timezone" class="custom-form-select">
                                    <option value="America/La_Paz" selected>America/La_Paz (GMT-4, Bolivia)</option>
                                    <option value="America/Santiago">America/Santiago (GMT-3)</option>
                                    <option value="America/Lima">America/Lima (GMT-5)</option>
                                    <option value="America/Argentina/Buenos_Aires">America/Buenos Aires (GMT-3)</option>
                                </select>
                            </div>

                            <div class="form-field-group">
                                <label class="form-field-label">Idioma Predeterminado</label>
                                <select name="language" class="custom-form-select">
                                    <option value="es_BO" selected>Español (Bolivia)</option>
                                    <option value="es_ES">Español (Internacional)</option>
                                    <option value="en_US">English (US)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-field-group">
                            <label class="form-field-label">Frecuencia de Actualización en Vivo</label>
                            <select name="auto_refresh" class="custom-form-select">
                                <option value="10s">Cada 10 segundos (Alta resolución)</option>
                                <option value="30s" selected>Cada 30 segundos (Recomendado)</option>
                                <option value="60s">Cada 1 minuto (Ahorro de ancho de banda)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Right Quick Info Card -->
                <div>
                    <div class="glass-card settings-card">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: var(--lime-soft-gradient); color: var(--lime); width: 40px; height: 40px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                                </svg>
                            </div>
                            <div>
                                <h2>Estado del Servidor</h2>
                                <p>Nodo industrial METRIC-NODE-01</p>
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Versión del Software</span>
                                <b>Metric v2.4.0-Enterprise</b>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Estado del Sistema</span>
                                <span style="color: #5c840c; font-weight: 700;">● 100% Operativo</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f1f5f9; padding-bottom: 8px;">
                                <span style="color: #64748b;">Ubicación de Servidores</span>
                                <b>La Paz, Bolivia</b>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Certificado SSL / TLS</span>
                                <span style="color: #088fa6; font-weight: 700;">Válido (256-bit)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 2: Seguridad & Acceso -->
        <div class="settings-tab-pane" id="tab-security">
            <div class="settings-form-layout">
                <div>
                    <div class="glass-card settings-card">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: var(--cyan-soft-gradient); color: var(--cyan); width: 40px; height: 40px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </div>
                            <div>
                                <h2>Políticas de Seguridad & Acceso</h2>
                                <p>Control de autenticación y protección de cuentas de administrador.</p>
                            </div>
                        </div>

                        <!-- 2FA Toggle -->
                        <div class="toggle-setting-row">
                            <div class="toggle-text-block">
                                <h4>Autenticación en Dos Pasos Obligatoria (2FA)</h4>
                                <p>Exige a todos los operadores y administradores validar su identidad con código OTP.</p>
                            </div>
                            <label class="switch-control">
                                <input type="checkbox" name="two_factor_auth" checked>
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <!-- IP Whitelist Toggle -->
                        <div class="toggle-setting-row">
                            <div class="toggle-text-block">
                                <h4>Restricción de Acceso por IP Corporativa</h4>
                                <p>Permite el inicio de sesión únicamente desde rangos de red autorizados en planta.</p>
                            </div>
                            <label class="switch-control">
                                <input type="checkbox" name="ip_whitelist_enabled">
                                <span class="switch-slider"></span>
                            </label>
                        </div>

                        <div class="form-row-grid" style="margin-top: 20px;">
                            <div class="form-field-group">
                                <label class="form-field-label">Tiempo de Inactividad para Cierre de Sesión</label>
                                <select name="session_timeout" class="custom-form-select">
                                    <option value="15">15 minutos</option>
                                    <option value="30">30 minutos</option>
                                    <option value="45" selected>45 minutos (Recomendado)</option>
                                    <option value="60">1 hora</option>
                                </select>
                            </div>

                            <div class="form-field-group">
                                <label class="form-field-label">Expiración Periódica de Contraseñas</label>
                                <select name="password_expiry" class="custom-form-select">
                                    <option value="30">Cada 30 días</option>
                                    <option value="60">Cada 60 días</option>
                                    <option value="90" selected>Cada 90 días</option>
                                    <option value="0">Sin expiración obligatoria</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Danger Zone Card -->
                <div>
                    <div class="glass-card settings-card danger-zone-card">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: #fee2e2; color: #dc2626; width: 40px; height: 40px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                                    <line x1="12" y1="9" x2="12" y2="13"/>
                                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                                </svg>
                            </div>
                            <div>
                                <h2>Zona Crítica</h2>
                                <p>Acciones de seguridad avanzada</p>
                            </div>
                        </div>
                        <p style="font-size: 12.5px; color: #7f1d1d; margin-bottom: 16px;">
                            Cerrar todas las sesiones activas en otros dispositivos forzará a todos los administradores a autenticarse de nuevo.
                        </p>
                        <button type="button" class="btn-danger-action" onclick="if(confirm('¿Cerrar todas las sesiones remotas?')) triggerToast('Sesiones remotas finalizadas', '🔒')">
                            Cerrar Sesiones Activas
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab 3: API & Integraciones -->
        <div class="settings-tab-pane" id="tab-api">
            <div class="settings-form-layout">
                <div>
                    <!-- Tarjeta: Enlace y Conexión para App Móvil -->
                    <div class="glass-card settings-card" style="border: 1.5px solid rgba(0, 181, 226, 0.35); background: linear-gradient(180deg, rgba(255,255,255,0.95) 0%, rgba(240, 249, 255, 0.85) 100%); margin-bottom: 24px;">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: var(--cyan-soft-gradient); color: var(--cyan); width: 44px; height: 44px;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                                </svg>
                            </div>
                            <div>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <h2 style="margin: 0;">Enlace para App Móvil (Flutter)</h2>
                                    <span style="font-size: 11px; font-weight: 700; background: rgba(0, 181, 226, 0.15); color: #008aa8; padding: 2px 8px; border-radius: 999px;">ACTIVO</span>
                                </div>
                                <p style="margin: 2px 0 0 0;">Configura la URL base del servidor para que los técnicos de campo sincronicen mediciones y se autentiquen desde la app.</p>
                            </div>
                        </div>

                        <div class="form-field-group" style="margin-top: 16px;">
                            <label class="form-field-label" style="font-weight: 700; color: #0f172a;">URL Base de la API Móvil</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="url" id="appApiUrlInput" name="app_api_url" class="custom-form-input" 
                                    style="font-family: monospace; font-size: 14px; font-weight: 600; color: #0369a1; background: #ffffff;"
                                    value="{{ $settings['api']['app_api_url'] ?? $suggestedUrls['local_ip'] }}"
                                    placeholder="http://192.168.1.X:8000/api o https://tudominio.com/api" required>
                                <button type="button" class="btn-copy-code" style="height: 42px; border-radius: 10px; padding: 0 16px;" onclick="navigator.clipboard.writeText(document.getElementById('appApiUrlInput').value); triggerToast('URL de API copiada', '📋');">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect width="13" height="13" x="9" y="9" rx="2" ry="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                    <span>Copiar</span>
                                </button>
                            </div>
                            <div class="form-field-hint" style="margin-top: 6px;">
                                💡 Puedes cambiar esta URL en cualquier momento sin recompilar la app móvil. Tanto en pruebas locales como en producción.
                            </div>
                        </div>

                        <!-- Selector rápido de entornos -->
                        <div style="margin-top: 16px;">
                            <span style="font-size: 11.5px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Acceso Rápido a IPs Detectadas:</span>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px;">
                                <button type="button" class="badge-role" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-size: 12px;"
                                    onclick="document.getElementById('appApiUrlInput').value='{{ $suggestedUrls['local_ip'] }}'; updateQrCode('{{ $suggestedUrls['local_ip'] }}');">
                                    📶 Red Local Wi-Fi: <b>{{ $suggestedUrls['local_ip'] }}</b>
                                </button>
                                <button type="button" class="badge-role" style="background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-size: 12px;"
                                    onclick="document.getElementById('appApiUrlInput').value='{{ $suggestedUrls['emulator'] }}'; updateQrCode('{{ $suggestedUrls['emulator'] }}');">
                                    📱 Emulador Android: <b>{{ $suggestedUrls['emulator'] }}</b>
                                </button>
                                <button type="button" class="badge-role" style="background: #f8fafc; color: #475569; border: 1px solid #cbd5e1; cursor: pointer; padding: 6px 12px; border-radius: 8px; font-size: 12px;"
                                    onclick="document.getElementById('appApiUrlInput').value='{{ $suggestedUrls['localhost'] }}'; updateQrCode('{{ $suggestedUrls['localhost'] }}');">
                                    💻 Localhost PC: <b>{{ $suggestedUrls['localhost'] }}</b>
                                </button>
                            </div>
                        </div>

                        <!-- Código QR para sincronización rápida -->
                        <div style="display: flex; align-items: center; gap: 16px; margin-top: 20px; padding: 14px; background: rgba(255,255,255,0.85); border-radius: 12px; border: 1px solid #e2e8f0;">
                            <img id="qrCodeImg" 
                                src="https://api.qrserver.com/v1/create-qr-code/?size=110x110&margin=4&data={{ urlencode($settings['api']['app_api_url'] ?? $suggestedUrls['local_ip']) }}" 
                                alt="QR Conexión App Móvil" 
                                style="width: 100px; height: 100px; border-radius: 8px; border: 1px solid #cbd5e1; background: white;"
                                onerror="this.style.display='none'">
                            <div>
                                <h3 style="font-size: 13.5px; font-weight: 700; margin: 0 0 4px 0; color: #0f172a;">Escaneo Directo con la App Móvil</h3>
                                <p style="font-size: 12px; color: #64748b; margin: 0; line-height: 1.45;">
                                    Abre la app <b>Metric</b> en tu teléfono, pulsa el icono de configuración <b>⚙️</b> en la pantalla de inicio de sesión y escanea este código o pega la URL para vincular el teléfono inmediatamente.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Tarjeta: Llave de API y Webhook -->
                    <div class="glass-card settings-card">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: var(--cyan-soft-gradient); color: var(--cyan); width: 40px; height: 40px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="16 18 22 12 16 6"/>
                                    <polyline points="8 6 2 12 8 18"/>
                                </svg>
                            </div>
                            <div>
                                <h2>Llave de API de Producción</h2>
                                <p>Autenticación segura para integración con sistemas ERP, SCADA y servicios externos.</p>
                            </div>
                        </div>

                        <label class="form-field-label">API Key Activa</label>
                        <div class="api-key-box">
                            <span class="api-key-text" id="activeApiKeyText">{{ $settings['api']['api_key'] ?? 'pk_live_pacha_98f4a7b1c3e6d8920fa58c4129' }}</span>
                            <button type="button" class="btn-copy-code" onclick="copyApiKey(document.getElementById('activeApiKeyText').textContent)">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="13" height="13" x="9" y="9" rx="2" ry="2"/>
                                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                </svg>
                                <span>Copiar</span>
                            </button>
                        </div>

                        <div class="form-field-group" style="margin-top: 24px;">
                            <label class="form-field-label">Webhook URL para Eventos en Vivo</label>
                            <input type="url" name="webhook_url" class="custom-form-input" value="{{ $settings['api']['webhook_url'] ?? 'https://api.pachabol.com/v1/telemetry/events' }}">
                            <div class="form-field-hint">Enviaremos peticiones HTTP POST cifradas cuando ocurran alertas o sincronizaciones.</div>
                        </div>
                    </div>
                </div>

                <div>
                    <!-- Tarjeta: Estado de la API Móvil -->
                    <div class="glass-card settings-card" style="margin-bottom: 24px;">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: #ecfdf5; color: #059669; width: 40px; height: 40px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                    <polyline points="22 4 12 14.01 9 11.01"/>
                                </svg>
                            </div>
                            <div>
                                <h2>Servicios API Móvil</h2>
                                <p>Endpoints disponibles</p>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #64748b;">Login de Personal:</span>
                                <span style="font-family: monospace; font-size: 11.5px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0f172a;">/api/login</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #64748b;">Proyectos Asignados:</span>
                                <span style="font-family: monospace; font-size: 11.5px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0f172a;">/api/projects</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #64748b;">Módulos de Monitoreo:</span>
                                <span style="font-family: monospace; font-size: 11.5px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0f172a;">/api/projects/{id}/modules</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span style="color: #64748b;">Medición Iluminación:</span>
                                <span style="font-family: monospace; font-size: 11.5px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #0f172a;">/api/modules/{id}/illumination</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px dashed #e2e8f0; padding-top: 8px;">
                                <span style="color: #64748b;">Estado del Servidor:</span>
                                <b style="color: #16a34a;">● En línea (Activo)</b>
                            </div>
                        </div>
                    </div>

                    <div class="glass-card settings-card">
                        <div class="settings-section-head">
                            <div class="kpi-icon-glow" style="background: var(--lime-soft-gradient); color: var(--lime); width: 40px; height: 40px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </div>
                            <div>
                                <h2>Límite de Consultas</h2>
                                <p>Plan Enterprise Dedicado</p>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 10px; font-size: 13px;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Tasa permitida:</span>
                                <b>10,000 req/min</b>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: #64748b;">Última sincronización:</span>
                                <b>En tiempo real</b>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        function updateQrCode(url) {
            const qrImg = document.getElementById('qrCodeImg');
            if (qrImg) {
                qrImg.src = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&margin=4&data=' + encodeURIComponent(url);
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('appApiUrlInput');
            if (input) {
                input.addEventListener('input', function() {
                    updateQrCode(this.value);
                });
            }
        });
    </script>
@endsection

@push('scripts')
    @metricScript('settings')
@endpush
