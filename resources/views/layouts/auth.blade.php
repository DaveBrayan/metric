<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Iniciar Sesión — Metric v2 Pachabol')</title>
    
    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Rendered & Bundled by Vite for Maximum Performance -->
    @vite(['resources/css/app.css', 'resources/css/login.css', 'resources/js/app.js', 'resources/js/login.js'])
    @stack('styles')
</head>
<body>
    <!-- Interactive Canvas Particle Network Background -->
    <canvas id="particlesCanvas"></canvas>

    <!-- Ambient Diffused Mesh Background -->
    <div class="ambient-mesh">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    @yield('content')

    <!-- Wow Toast Notification Element -->
    <div class="wow-toast" id="wowToast">
        <span id="toastIconSymbol">✨</span>
        <span id="toastTextContent">Notificación interactiva</span>
    </div>

    @stack('scripts')
</body>
</html>
