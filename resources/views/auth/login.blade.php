@extends('layouts.auth')

@section('title', 'Iniciar Sesión — Metric v2 Pachabol')

@section('content')
<div class="auth-viewport-centered">
    <!-- Master Floating 2-Column Glassmorphism Card -->
    <div class="auth-master-card">
        <!-- Left Column: Cinematic Hero (login.png + Live Telemetry) -->
        <aside class="hero-cinematic-panel">
            <img src="{{ asset('img/login.png') }}" alt="Metric v2 Industrial Platform" class="hero-bg-media">
            
            <!-- Atmospheric Depth Overlays -->
            <div class="hero-gradient-overlay"></div>
            <div class="hero-radial-glow"></div>

            <!-- Bottom Hero Copy & Floating Telemetry Badges -->
            <div class="hero-bottom-content">
                <div class="hero-tagline-kicker">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
                    </svg>
                    <span>SISTEMA DE GESTIÓN OPERATIVA</span>
                </div>

                <h1 class="hero-headline-text">
                    Control inteligente, monitoreo y analítica <span class="cyan-highlight">en tiempo real</span>.
                </h1>

                <!-- Live Telemetry Badges with SVG Vector Icons -->
                <div class="hero-telemetry-grid">
                    <div class="telemetry-card">
                        <div class="telemetry-icon-box lime">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                            </svg>
                        </div>
                        <div>
                            <div class="telemetry-metric-val">99.98%</div>
                            <div class="telemetry-metric-lbl">Disponibilidad en Planta</div>
                        </div>
                    </div>

                    <div class="telemetry-card">
                        <div class="telemetry-icon-box cyan">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="telemetry-metric-val">+4,280</div>
                            <div class="telemetry-metric-lbl">Mediciones en Vivo / seg</div>
                        </div>
                    </div>
                </div>

                <div class="hero-footer-security">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Conexión Encriptada SSL 256-bit • Servidores Pachabol © {{ date('Y') }}</span>
                </div>
            </div>
        </aside>

        <!-- Right Column: Logo Pachabol Header & High-Contrast Form -->
        <section class="form-interactive-panel">
            <div class="form-content-box">
                <!-- Prominent Pachabol Logo Header on the Right Column -->
                <div class="right-col-brand-header">
                    <img src="{{ asset('img/logo.png') }}" alt="Pachabol Logo" class="brand-right-logo">
                    <div class="brand-right-meta">
                        <div class="brand-right-title">
                            <span>Metric</span>
                            <span class="v2-pill-tag">v2</span>
                        </div>
                        <span class="pachabol-pill-tag">PACHABOL</span>
                    </div>
                </div>

                <div class="form-header-area">
                    <h2>Iniciar Sesión</h2>
                    <p>Ingresa tus credenciales para acceder a la plataforma corporativa.</p>
                </div>

                @if ($errors->any())
                    <div class="auth-error-banner">
                        <span>⚠️</span>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST" id="loginAuthForm">
                    @csrf

                    <!-- Email Input -->
                    <div class="form-group-item">
                        <label for="emailInput" class="field-label">Correo Electrónico</label>
                        <div class="field-input-wrapper">
                            <span class="field-leading-icon">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="20" height="16" x="2" y="4" rx="2"/>
                                <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
                            </svg>
                            </span>
                            <input 
                                type="email" 
                                id="emailInput" 
                                name="email" 
                                class="auth-text-input" 
                                placeholder="tu-correo@pachabol.com" 
                                value="{{ old('email', 'admin@pachabol.com') }}" 
                                required 
                                autocomplete="email"
                            >
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group-item">
                        <label for="passwordInput" class="field-label">Contraseña</label>
                        <div class="field-input-wrapper">
                            <span class="field-leading-icon">
                                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input 
                                type="password" 
                                id="passwordInput" 
                                name="password" 
                                class="auth-text-input" 
                                placeholder="••••••••" 
                                required 
                                autocomplete="current-password"
                            >
                            <button type="button" class="btn-eye-toggle" onclick="togglePasswordVisibility('passwordInput', this)" title="Mostrar / Ocultar contraseña" aria-label="Mostrar contraseña">
                                <!-- Eye Open -->
                                <svg class="eye-open" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                                <!-- Eye Closed -->
                                <svg class="eye-closed" style="display: none;" width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m15 18-.722-3.25"/>
                                    <path d="M2 8a10.645 10.645 0 0 0 20 0"/>
                                    <path d="m20 15-1.726-2.05"/>
                                    <path d="m4 15 1.726-2.05"/>
                                    <path d="m9 18 .722-3.25"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Actions Bar: Remember Me & Forgot Password -->
                    <div class="auth-actions-bar">
                        <label class="remember-checkbox-label">
                            <input type="checkbox" name="remember" id="rememberMe" checked>
                            <span>Recordar sesión</span>
                        </label>
                        <a href="javascript:void(0)" onclick="triggerToast('Enlace de restablecimiento enviado a soporte', '📩')" class="link-forgot-pass">
                            ¿Olvidaste tu contraseña?
                        </a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-primary-auth-submit" id="btnSubmitLogin">
                        <span id="btnSubmitText">Acceder a la Plataforma</span>
                        <span id="btnSubmitSpinner" style="display: none;">⏳ Verificando credenciales...</span>
                        <span class="arrow-icon">→</span>
                    </button>
                </form>
            </div>
        </section>
    </div>
</div>
@endsection
