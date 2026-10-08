<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad — METRIC | Pachabol S.R.L.</title>
    
    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #0284c7;
            --primary-dark: #0369a1;
            --primary-light: #e0f2fe;
            --primary-gradient: linear-gradient(135deg, #10b9df 0%, #0799a7 100%);
            --ink: #0f172a;
            --ink-muted: #475569;
            --surface: #ffffff;
            --bg-page: #f8fafc;
            --border: #e2e8f0;
            --border-highlight: #bae6fd;
            --radius-lg: 16px;
            --radius-md: 10px;
            --radius-full: 9999px;
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.05);
            --shadow-md: 0 4px 16px -2px rgba(15, 23, 42, 0.08);
            --shadow-lg: 0 12px 30px -4px rgba(15, 23, 42, 0.12);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-page);
            color: var(--ink);
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        /* Ambient Glow Background */
        .ambient-mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }

        .ambient-mesh .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.35;
        }

        .orb-1 {
            width: 450px;
            height: 450px;
            background: #bae6fd;
            top: -100px;
            right: -100px;
        }

        .orb-2 {
            width: 380px;
            height: 380px;
            background: #ccfbf1;
            bottom: -50px;
            left: -80px;
        }

        /* Navigation Header */
        .public-nav-header {
            position: sticky;
            top: 0;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
            z-index: 100;
            transition: all 0.2s ease;
        }

        .nav-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-logo-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo-img {
            width: 38px;
            height: 38px;
            object-fit: contain;
            border-radius: 8px;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-badge {
            background: var(--primary-light);
            color: var(--primary-dark);
            font-size: 11px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: var(--radius-full);
            border: 1px solid var(--border-highlight);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #ffffff;
            border: 1px solid var(--border);
            border-radius: var(--radius-full);
            font-size: 12.5px;
            font-weight: 700;
            color: var(--ink-muted);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-print:hover {
            border-color: var(--primary);
            color: var(--primary-dark);
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        /* Hero Header Banner */
        .hero-banner {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 32px auto 0;
            padding: 0 24px;
        }

        .hero-card {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff;
            border-radius: var(--radius-lg);
            padding: 40px 36px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
        }

        .hero-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 350px;
            height: 350px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero-badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: var(--radius-full);
            padding: 4px 12px;
            font-size: 11.5px;
            font-weight: 700;
            margin-bottom: 16px;
            backdrop-filter: blur(4px);
        }

        .hero-title {
            font-family: 'Outfit', sans-serif;
            font-size: 32px;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .hero-subtitle {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.9);
            max-width: 720px;
            line-height: 1.6;
        }

        .hero-meta-bar {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
            font-size: 12.5px;
            color: rgba(255, 255, 255, 0.85);
        }

        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .hero-meta-item strong {
            color: #ffffff;
        }

        /* Layout Grid */
        .content-layout {
            position: relative;
            z-index: 1;
            max-width: 1100px;
            margin: 32px auto 60px;
            padding: 0 24px;
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 32px;
            align-items: start;
        }

        @media (max-width: 860px) {
            .content-layout {
                grid-template-columns: 1fr;
            }
            .sidebar-toc {
                display: none;
            }
        }

        /* Table of Contents Sidebar */
        .sidebar-toc {
            position: sticky;
            top: 86px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 20px;
            box-shadow: var(--shadow-sm);
        }

        .toc-title {
            font-size: 12.5px;
            font-weight: 800;
            color: var(--ink);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .toc-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .toc-link {
            display: block;
            padding: 7px 10px;
            border-radius: 8px;
            font-size: 12.5px;
            font-weight: 600;
            color: var(--ink-muted);
            text-decoration: none;
            transition: all 0.15s ease;
            line-height: 1.4;
        }

        .toc-link:hover {
            background: var(--primary-light);
            color: var(--primary-dark);
            transform: translateX(3px);
        }

        /* Main Legal Content */
        .legal-document {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 40px;
            box-shadow: var(--shadow-sm);
        }

        @media (max-width: 640px) {
            .legal-document {
                padding: 24px 18px;
            }
        }

        .policy-section {
            margin-bottom: 36px;
            scroll-margin-top: 100px;
        }

        .policy-section:last-child {
            margin-bottom: 0;
        }

        .section-heading {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1.5px solid var(--border);
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.3px;
        }

        .section-heading svg {
            color: var(--primary);
            flex-shrink: 0;
        }

        .policy-text {
            font-size: 14.5px;
            color: var(--ink-muted);
            line-height: 1.7;
            margin-bottom: 14px;
        }

        .policy-text strong {
            color: var(--ink);
        }

        .policy-list {
            margin: 12px 0 16px 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            font-size: 14px;
            color: var(--ink-muted);
        }

        .policy-list li {
            line-height: 1.6;
        }

        .policy-list li strong {
            color: var(--ink);
        }

        /* Data Card Highlights */
        .highlight-card {
            background: var(--bg-page);
            border: 1px solid var(--border);
            border-left: 4px solid var(--primary);
            border-radius: var(--radius-md);
            padding: 16px 18px;
            margin: 16px 0;
        }

        .highlight-card-title {
            font-size: 13.5px;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .highlight-card-text {
            font-size: 13px;
            color: var(--ink-muted);
            line-height: 1.6;
        }

        /* Permissions Table */
        .permissions-table {
            width: 100%;
            border-collapse: collapse;
            margin: 16px 0;
            font-size: 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            overflow: hidden;
        }

        .permissions-table th {
            background: #f8fafc;
            color: var(--ink);
            font-weight: 800;
            text-align: left;
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .permissions-table td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--border);
            color: var(--ink-muted);
            vertical-align: top;
        }

        .permissions-table tr:last-child td {
            border-bottom: none;
        }

        .badge-perm {
            display: inline-block;
            background: var(--primary-light);
            color: var(--primary-dark);
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-family: monospace;
            font-size: 11.5px;
            border: 1px solid var(--border-highlight);
        }

        /* Footer */
        .public-footer {
            background: #ffffff;
            border-top: 1px solid var(--border);
            padding: 32px 24px;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .footer-text {
            font-size: 13px;
            color: var(--ink-muted);
            margin-bottom: 6px;
        }

        .footer-brand {
            font-weight: 800;
            color: var(--ink);
        }

        /* Print Media */
        @media print {
            .public-nav-header, .sidebar-toc, .ambient-mesh, .btn-print {
                display: none !important;
            }
            .content-layout {
                grid-template-columns: 1fr !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .legal-document {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
            .hero-card {
                background: #0284c7 !important;
                color: #000000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Ambient Mesh Background -->
    <div class="ambient-mesh">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
    </div>

    <!-- Sticky Public Header -->
    <header class="public-nav-header">
        <div class="nav-container">
            <a href="{{ url('/') }}" class="brand-logo-wrap">
                <img src="{{ asset('img/logo.png') }}" alt="Pachabol Logo" class="brand-logo-img" onerror="this.style.display='none'">
                <div>
                    <div class="brand-title">
                        <span>METRIC</span>
                        <span class="brand-badge">SST & Monitoreo</span>
                    </div>
                </div>
            </a>
            <div class="nav-actions">
                <button type="button" class="btn-print" onclick="window.print()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    <span>Imprimir / PDF</span>
                </button>
            </div>
        </div>
    </header>

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="hero-card">
            <div class="hero-badge-pill">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                </svg>
                <span>Documento Legal Oficial</span>
            </div>
            <h1 class="hero-title">Política de Privacidad y Protección de Datos</h1>
            <p class="hero-subtitle">
                Esta Política de Privacidad describe cómo <strong>PACHABOL S.R.L.</strong> recopila, utiliza, almacena y protege la información en la aplicación móvil <strong>Metric (Metric - Registro Monitoreos)</strong> y su plataforma web integrada.
            </p>
            <div class="hero-meta-bar">
                <div class="hero-meta-item">
                    <span>Aplicación: <strong>Metric (Android / Web)</strong></span>
                </div>
                <div class="hero-meta-item">
                    <span>Desarrollador: <strong>PACHABOL S.R.L.</strong></span>
                </div>
                <div class="hero-meta-item">
                    <span>Última actualización: <strong>Octubre 2026</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Layout Grid -->
    <main class="content-layout">
        
        <!-- Sidebar Navigation (TOC) -->
        <aside class="sidebar-toc">
            <div class="toc-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                    <line x1="8" y1="6" x2="21" y2="6"></line>
                    <line x1="8" y1="12" x2="21" y2="12"></line>
                    <line x1="8" y1="18" x2="21" y2="18"></line>
                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                </svg>
                <span>Contenido</span>
            </div>
            <nav>
                <ul class="toc-list">
                    <li><a href="#responsable" class="toc-link">1. Responsable del Tratamiento</a></li>
                    <li><a href="#informacion-recopilada" class="toc-link">2. Información Recopilada</a></li>
                    <li><a href="#permisos-dispositivo" class="toc-link">3. Permisos del Dispositivo</a></li>
                    <li><a href="#finalidad-uso" class="toc-link">4. Finalidad del Uso de Datos</a></li>
                    <li><a href="#almacenamiento-seguridad" class="toc-link">5. Seguridad y Almacenamiento</a></li>
                    <li><a href="#transferencia-terceros" class="toc-link">6. Compartición con Terceros</a></li>
                    <li><a href="#derechos-usuario" class="toc-link">7. Derechos y Eliminación de Datos</a></li>
                    <li><a href="#menores" class="toc-link">8. Privacidad de Menores</a></li>
                    <li><a href="#cambios" class="toc-link">9. Modificaciones a la Política</a></li>
                    <li><a href="#contacto" class="toc-link">10. Contacto y Soporte</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Document Body -->
        <article class="legal-document">

            <!-- 1. Responsable -->
            <section id="responsable" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    <span>1. Responsable del Tratamiento de Datos</span>
                </h2>
                <p class="policy-text">
                    La aplicación móvil <strong>Metric (Metric - Registro Monitoreos)</strong> y el sistema de gestión <strong>METRIC V2</strong> son propiedad y están operados por <strong>PACHABOL S.R.L.</strong>, empresa especializada en servicios de ingeniería, monitoreo ocupacional, ambiental, seguridad y salud en el trabajo (SST).
                </p>
                <div class="highlight-card">
                    <div class="highlight-card-title">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>Identificación Legal del Operador</span>
                    </div>
                    <div class="highlight-card-text">
                        <strong>Razón Social:</strong> PACHABOL S.R.L.<br>
                        <strong>Servicio / Plataforma:</strong> Sistema METRIC — Monitoreo de Higiene y Salud Ocupacional.<br>
                        <strong>Correo Electrónico de Contacto:</strong> <a href="mailto:soporte@pachabol.com" style="color: var(--primary); text-decoration: none; font-weight: 700;">soporte@pachabol.com</a> / <a href="mailto:info@pachabol.com" style="color: var(--primary); text-decoration: none; font-weight: 700;">info@pachabol.com</a>
                    </div>
                </div>
            </section>

            <!-- 2. Información Recopilada -->
            <section id="informacion-recopilada" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <span>2. Información que Recopilamos</span>
                </h2>
                <p class="policy-text">
                    <strong>Metric</strong> es una herramienta técnica diseñada para el levantamiento y procesamiento de datos en campo durante inspecciones de higiene ocupacional y seguridad industrial. Recopilamos las siguientes categorías de información:
                </p>
                <ul class="policy-list">
                    <li>
                        <strong>Datos de Autenticación de Técnicos:</strong> Nombre, correo electrónico corporativo, cargo técnico y credenciales de acceso para la identificación del responsable de cada medición.
                    </li>
                    <li>
                        <strong>Datos Técnicos de Monitoreo Ocupacional:</strong> Mediciones físicas tomadas en campo (niveles de luxometría, decibeles en dosimetría y sonometría, aceleraciones triaxiales en vibración ISO 2631/5349, concentraciones químicas, caudales, gases, opacidad, índices térmicos WBGT y evaluaciones ergonómicas REBA/ROSA).
                    </li>
                    <li>
                        <strong>Datos de Ubicación y Georreferenciación (GPS / UTM):</strong> Coordenadas geográficas (Latitud, Longitud, Este X, Norte Y, Zona UTM) capturadas únicamente en el momento en que el técnico registra un punto de medición.
                    </li>
                    <li>
                        <strong>Evidencias Fotográficas de Campo:</strong> Fotografías capturadas por el personal técnico para documentar las condiciones de los puestos de trabajo, ubicación de instrumentos de medición y fuentes evaluadas.
                    </li>
                    <li>
                        <strong>Información de Equipos e Instrumentos:</strong> Marca, modelo, número de serie y certificados de calibración asociados al instrumento con el que se realiza el estudio técnico.
                    </li>
                </ul>
            </section>

            <!-- 3. Permisos del Dispositivo -->
            <section id="permisos-dispositivo" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                    <span>3. Permisos Solicitados en el Dispositivo</span>
                </h2>
                <p class="policy-text">
                    Para cumplir con sus funciones operativas de ingeniería y registro en terreno, la aplicación solicita los siguientes permisos explícitos:
                </p>
                <table class="permissions-table">
                    <thead>
                        <tr>
                            <th style="width: 35%;">Permiso</th>
                            <th>Propósito / Justificación Técnica</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="badge-perm">ACCESS_FINE_LOCATION</span><br><span class="badge-perm" style="margin-top:4px;">ACCESS_COARSE_LOCATION</span></td>
                            <td>Obtener las coordenadas geográficas exactas (GPS / UTM) del punto físico donde se realiza la medición ocupacional o ambiental para el informe técnico y mapeo en el plano.</td>
                        </tr>
                        <tr>
                            <td><span class="badge-perm">CAMERA</span></td>
                            <td>Permitir al técnico capturar fotografías in situ del puesto de trabajo evaluado, el instrumento calibrado y las condiciones ambientales.</td>
                        </tr>
                        <tr>
                            <td><span class="badge-perm">READ_MEDIA_IMAGES</span><br><span class="badge-perm" style="margin-top:4px;">READ_EXTERNAL_STORAGE</span></td>
                            <td>Permitir adjuntar imágenes previamente tomadas como respaldo fotográfico del informe técnico.</td>
                        </tr>
                        <tr>
                            <td><span class="badge-perm">INTERNET</span><br><span class="badge-perm" style="margin-top:4px;">ACCESS_NETWORK_STATE</span></td>
                            <td>Sincronizar de forma segura las mediciones tomadas en campo hacia el servidor central en la nube mediante protocolo cifrado HTTPS.</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- 4. Finalidad -->
            <section id="finalidad-uso" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <polyline points="9 11 12 14 22 4"></polyline>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                    </svg>
                    <span>4. Finalidad del Uso de la Información</span>
                </h2>
                <p class="policy-text">
                    Los datos recopilados se utilizan exclusivamente para los siguientes fines legítimos:
                </p>
                <ul class="policy-list">
                    <li>Generación de informes técnicos de cumplimiento normativo en Seguridad, Higiene y Salud Ocupacional según normas nacionales (NTS, NB) e internacionales (ISO, OSHA, ACGIH).</li>
                    <li>Cálculo automatizado de índices de exposición laboral y niveles de acción preventivos.</li>
                    <li>Trazabilidad técnica del personal acreditado que ejecutó el monitoreo en campo.</li>
                    <li>Georreferenciación en planos y mapas de los puntos críticos evaluados.</li>
                    <li>Almacenamiento seguro y consulta histórica de los proyectos de monitoreo del cliente.</li>
                </ul>
            </section>

            <!-- 5. Seguridad -->
            <section id="almacenamiento-seguridad" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                    </svg>
                    <span>5. Almacenamiento y Seguridad de los Datos</span>
                </h2>
                <p class="policy-text">
                    La seguridad de la información técnica e industrial es una prioridad fundamental para <strong>PACHABOL S.R.L.</strong> Implementamos medidas de seguridad de estándar de la industria:
                </p>
                <ul class="policy-list">
                    <li><strong>Cifrado en Tránsito:</strong> Todas las comunicaciones entre la aplicación móvil y nuestros servidores se transmiten mediante canales cifrados con certificados SSL/TLS (HTTPS).</li>
                    <li><strong>Control de Acceso Estricto:</strong> El acceso a los datos está restringido únicamente a usuarios autorizados mediante contraseñas seguras y roles de permisos administrados.</li>
                    <li><strong>Protección de Bases de Datos:</strong> Los servidores cuentan con firewalls, respaldos periódicos y políticas de mitigación de vulnerabilidades.</li>
                </ul>
            </section>

            <!-- 6. Terceros -->
            <section id="transferencia-terceros" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="18" cy="5" r="3"></circle>
                        <circle cx="6" cy="12" r="3"></circle>
                        <circle cx="18" cy="19" r="3"></circle>
                        <line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line>
                        <line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line>
                    </svg>
                    <span>6. No Venta ni Compartición con Terceros</span>
                </h2>
                <p class="policy-text">
                    <strong>PACHABOL S.R.L. no vende, no alquila, no comercializa ni comparte la información personal o los datos de monitoreo de sus usuarios con empresas de publicidad, corredores de datos ni terceros con fines comerciales.</strong>
                </p>
                <p class="policy-text">
                    Los datos solo son accesibles por la empresa cliente contratante y los técnicos autorizados para la elaboración del informe técnico de higiene y seguridad.
                </p>
            </section>

            <!-- 7. Derechos del Usuario -->
            <section id="derechos-usuario" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="8.5" cy="7" r="4"></circle>
                        <line x1="20" y1="8" x2="20" y2="14"></line>
                        <line x1="23" y1="11" x2="17" y2="11"></line>
                    </svg>
                    <span>7. Derechos del Usuario y Eliminación de Datos</span>
                </h2>
                <p class="policy-text">
                    Los usuarios tienen derecho en cualquier momento a:
                </p>
                <ul class="policy-list">
                    <li>Conocer qué datos personales o de cuenta han sido registrados en la plataforma.</li>
                    <li>Solicitar la rectificación o actualización de información incorrecta.</li>
                    <li>Solicitar la <strong>eliminación total de su cuenta de usuario y datos personales asociados</strong>.</li>
                </ul>
                <p class="policy-text">
                    Para ejercer estos derechos o solicitar la eliminación de su cuenta, el usuario puede enviar una solicitud por escrito al correo electrónico <strong>soporte@pachabol.com</strong>, y su solicitud será atendida en un plazo máximo de 48 a 72 horas hábiles.
                </p>
            </section>

            <!-- 8. Menores -->
            <section id="menores" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M8 14s1.5 2 4 2 4-2 4-2"></path>
                        <line x1="9" y1="9" x2="9.01" y2="9"></line>
                        <line x1="15" y1="9" x2="15.01" y2="9"></line>
                    </svg>
                    <span>8. Privacidad de Menores</span>
                </h2>
                <p class="policy-text">
                    Nuestros servicios y la aplicación móvil están dirigidos exclusivamente a profesionales, técnicos de campo y empresas en el ámbito laboral y de ingeniería. No recopilamos deliberadamente información de menores de 18 años.
                </p>
            </section>

            <!-- 9. Cambios -->
            <section id="cambios" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                    </svg>
                    <span>9. Modificaciones a la Política de Privacidad</span>
                </h2>
                <p class="policy-text">
                    PACHABOL S.R.L. se reserva el derecho de actualizar esta Política de Privacidad para reflejar cambios normativos, mejoras técnicas o nuevas funcionalidades del sistema. Cualquier actualización se publicará de inmediato en esta misma dirección web pública con su correspondiente fecha de revisión.
                </p>
            </section>

            <!-- 10. Contacto -->
            <section id="contacto" class="policy-section">
                <h2 class="section-heading">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                        <polyline points="22,6 12,13 2,6"></polyline>
                    </svg>
                    <span>10. Canales de Contacto y Soporte</span>
                </h2>
                <p class="policy-text">
                    Si tienes preguntas, sugerencias o inquietudes sobre esta Política de Privacidad o el tratamiento de tus datos, puedes comunicarte con nosotros:
                </p>
                <div class="highlight-card">
                    <div class="highlight-card-text">
                        <strong>Empresa:</strong> PACHABOL S.R.L.<br>
                        <strong>Correo de Soporte y Privacidad:</strong> <a href="mailto:soporte@pachabol.com" style="color: var(--primary); text-decoration: none; font-weight: 700;">soporte@pachabol.com</a><br>
                        <strong>Sitio Web Oficial:</strong> <a href="https://pachabol.com" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 700;">https://pachabol.com</a><br>
                        <strong>Atención:</strong> La Paz - Bolivia &bull; Servicios Nacionales e Internacionales
                    </div>
                </div>
            </section>

        </article>
    </main>

    <!-- Public Footer -->
    <footer class="public-footer">
        <p class="footer-text">
            &copy; {{ date('Y') }} <span class="footer-brand">PACHABOL S.R.L.</span> — Todos los derechos reservados.
        </p>
        <p style="font-size: 11.5px; color: #94a3b8;">
            Sistema METRIC: Plataforma de Monitoreo Ocupacional, Ambiental y Seguridad Industrial.
        </p>
    </footer>

</body>
</html>
