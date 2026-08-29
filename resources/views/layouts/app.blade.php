<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Metric v2 - Pachabol')</title>
    
    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('img/logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/logo.png') }}">
    
    <!-- Google Fonts: Plus Jakarta Sans & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 Modern UI -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Rendered & Bundled by Vite for Maximum Performance -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    <!-- Ambient Diffused Mesh Background -->
    <div class="ambient-mesh">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>
    </div>

    <div class="app-layout">
        <!-- Modern Animated Sidebar -->
        @include('components.sidebar')

        <!-- Main Flow -->
        <div class="main-content-flow">
            <!-- Topbar -->
            @include('components.topbar')

            <!-- Container Page Content -->
            <main class="page-container">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Wow Toast Notification Element -->
    <div class="wow-toast" id="wowToast">
        <span id="toastIconSymbol">✨</span>
        <span id="toastTextContent">Notificación interactiva</span>
    </div>

    @stack('scripts')
</body>
</html>
