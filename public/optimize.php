<?php
/**
 * METRIC_V2 — Centro de Mantenimiento, Optimización y Migraciones Web
 * Herramienta integral para ejecutar comandos Artisan desde el navegador.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

$baseDir = realpath(__DIR__ . '/..');

if (!file_exists($baseDir . '/vendor/autoload.php')) {
    die("<h2 style='color:#ef4444;font-family:sans-serif;background:#0b1320;padding:30px;text-align:center;'>❌ Error: No se encontró la carpeta vendor. Por favor descomprime vendor.zip primero.</h2>");
}

require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$action = $_GET['action'] ?? null;
$outputLog = '';
$actionTitle = '';
$statusClass = 'info';

function runArtisan($command, $params = []) {
    try {
        Illuminate\Support\Facades\Artisan::call($command, $params);
        return Illuminate\Support\Facades\Artisan::output();
    } catch (\Throwable $e) {
        return "❌ Error: " . $e->getMessage() . "\n";
    }
}

if ($action) {
    switch ($action) {
        case 'all_in_one':
            $actionTitle = 'Optimización Integral, Migraciones & Datos Iniciales (1-Clic)';
            $outputLog .= "=== 1. Ejecutando Migraciones ===\n" . runArtisan('migrate', ['--force' => true]) . "\n";
            $outputLog .= "=== 2. Sembrando Administrador & Datos Iniciales ===\n" . runArtisan('db:seed', ['--force' => true]) . "\n";
            $outputLog .= "=== 3. Creando Storage Link ===\n" . runArtisan('storage:link') . "\n";
            $outputLog .= "=== 4. Limpiando Cachés Previas ===\n" . runArtisan('optimize:clear') . "\n";
            $outputLog .= "=== 5. Optimizando Rutas & Configuración ===\n" . runArtisan('optimize') . "\n";
            $statusClass = 'success';
            break;

        case 'migrate':
            $actionTitle = 'Migraciones de Base de Datos';
            $outputLog = runArtisan('migrate', ['--force' => true]);
            $statusClass = 'success';
            break;

        case 'seed':
            $actionTitle = 'Sembrar Administrador & Datos Iniciales (db:seed)';
            $outputLog = runArtisan('db:seed', ['--force' => true]);
            $statusClass = 'success';
            break;

        case 'storage_link':
            $actionTitle = 'Crear Enlace Simbólico de Almacenamiento (Storage Link)';
            $outputLog = runArtisan('storage:link');
            $statusClass = 'success';
            break;

        case 'clear_cache':
            $actionTitle = 'Limpieza de Todas las Cachés (Config, Rutas, Vistas, Cache)';
            $outputLog = runArtisan('optimize:clear');
            $statusClass = 'success';
            break;

        case 'optimize':
            $actionTitle = 'Compilación y Optimización de Producción';
            $outputLog = runArtisan('optimize');
            $statusClass = 'success';
            break;

        case 'route_list':
            $actionTitle = 'Lista de Rutas Registradas';
            $outputLog = runArtisan('route:list');
            $statusClass = 'info';
            break;
    }
}

$dbStatus = 'Desconocido';
try {
    Illuminate\Support\Facades\DB::connection()->getPdo();
    $dbName = Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    $dbStatus = "Conectado a MySQL ($dbName)";
} catch (\Throwable $e) {
    $dbStatus = "Sin conexión (" . $e->getMessage() . ")";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Centro de Optimización & Mantenimiento — METRIC V2</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: #080e1a; color: #f8fafc; min-height: 100vh; padding: 30px 20px; display: flex; justify-content: center; }
        .container { max-width: 860px; width: 100%; }
        .header-card { background: #111c2e; border: 1px solid rgba(16, 185, 223, 0.25); border-radius: 16px; padding: 24px 28px; margin-bottom: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .header-card h1 { color: #10b9df; font-size: 22px; font-weight: 800; margin-bottom: 6px; display: flex; align-items: center; gap: 10px; }
        .header-card p { color: #94a3b8; font-size: 13.5px; line-height: 1.5; }
        
        .db-status-bar { margin-top: 14px; padding: 8px 14px; background: #0b1320; border-radius: 8px; font-size: 12.5px; display: flex; align-items: center; justify-content: space-between; border: 1px solid #1e293b; }
        .db-badge { padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 11px; }
        .db-badge.ok { background: #14532d; color: #86efac; }
        .db-badge.err { background: #7f1d1d; color: #fca5a5; }

        .tools-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 14px; margin-bottom: 24px; }
        .btn-card { background: #111c2e; border: 1px solid #1e293b; padding: 18px 20px; border-radius: 12px; color: #f8fafc; text-decoration: none; display: flex; flex-direction: column; justify-content: space-between; gap: 8px; transition: all 0.2s ease; }
        .btn-card:hover { background: #1a2a44; border-color: #10b9df; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(16, 185, 223, 0.15); }
        .btn-card.featured { grid-column: span 2; background: linear-gradient(135deg, #0d3846 0%, #112836 100%); border-color: #10b9df; }
        .btn-card.featured:hover { background: linear-gradient(135deg, #0e4455 0%, #133345 100%); box-shadow: 0 8px 25px rgba(16, 185, 223, 0.3); }
        .btn-card-title { font-size: 14.5px; font-weight: 800; color: #ffffff; display: flex; align-items: center; justify-content: space-between; }
        .btn-card-desc { font-size: 12px; color: #94a3b8; line-height: 1.4; }
        .icon-tag { font-size: 18px; }

        .output-box { background: #020617; border: 1px solid #1e293b; border-radius: 12px; padding: 20px; margin-bottom: 24px; }
        .output-header { font-size: 13px; font-weight: 700; color: #10b9df; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; }
        pre { background: #0b1320; border: 1px solid #1e293b; padding: 16px; border-radius: 8px; color: #38bdf8; font-family: monospace; font-size: 12.5px; line-height: 1.5; overflow-x: auto; white-space: pre-wrap; max-height: 350px; }

        .btn-portal { display: inline-flex; align-items: center; justify-content: center; gap: 8px; width: 100%; padding: 14px; background: #10b9df; color: #0b1320; font-weight: 800; font-size: 15px; border-radius: 10px; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 14px rgba(16, 185, 223, 0.3); }
        .btn-portal:hover { background: #38bdf8; transform: translateY(-2px); }

        @media(max-width: 600px) {
            .tools-grid { grid-template-columns: 1fr; }
            .btn-card.featured { grid-column: span 1; }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header-card">
        <h1>⚡ Centro de Optimización & Mantenimiento — METRIC V2</h1>
        <p>Ejecuta migraciones, limpia cachés del sistema, crea enlaces simbólicos y optimiza el rendimiento de Laravel con un solo clic.</p>
        
        <div class="db-status-bar">
            <span>Base de Datos: <b><?= htmlspecialchars($dbStatus) ?></b></span>
            <span class="db-badge <?= strpos($dbStatus, 'Conectado') !== false ? 'ok' : 'err' ?>">
                <?= strpos($dbStatus, 'Conectado') !== false ? 'EN LÍNEA ✓' : 'DESCONECTADO ✕' ?>
            </span>
        </div>
    </div>

    <?php if ($action && $outputLog): ?>
        <div class="output-box">
            <div class="output-header">
                <span>Resultado: <?= htmlspecialchars($actionTitle) ?></span>
                <span style="color:#86efac; font-size: 11px;">EJECUTADO ✓</span>
            </div>
            <pre><?= htmlspecialchars($outputLog) ?></pre>
        </div>
    <?php endif; ?>

    <div class="tools-grid">
        <!-- 1-Click All in One -->
        <a href="?action=all_in_one" class="btn-card featured">
            <div class="btn-card-title">
                <span>🚀 Optimización Total en 1 Clic (Recomendado)</span>
                <span class="icon-tag">⚡</span>
            </div>
            <div class="btn-card-desc">
                Ejecuta migraciones pendientes, crea el storage link, limpia todas las cachés anteriores y optimiza las rutas y configuración en un solo paso.
            </div>
        </a>

        <!-- Migraciones -->
        <a href="?action=migrate" class="btn-card">
            <div class="btn-card-title">
                <span>🗄️ Ejecutar Migraciones</span>
                <span class="icon-tag">📦</span>
            </div>
            <div class="btn-card-desc">
                Crea y actualiza todas las tablas relacionales en MySQL (<code>php artisan migrate --force</code>).
            </div>
        </a>

        <!-- Sembrar Administrador y Datos -->
        <a href="?action=seed" class="btn-card">
            <div class="btn-card-title">
                <span>👤 Sembrar Administrador & Datos</span>
                <span class="icon-tag">🌱</span>
            </div>
            <div class="btn-card-desc">
                Registra a <b>admin@metric.com</b> y los datos iniciales de empresas, proyectos y módulos (<code>db:seed</code>).
            </div>
        </a>

        <!-- Limpiar Cachés -->
        <a href="?action=clear_cache" class="btn-card">
            <div class="btn-card-title">
                <span>🧹 Limpiar Cachés del Sistema</span>
                <span class="icon-tag">🔄</span>
            </div>
            <div class="btn-card-desc">
                Limpia cachés de configuración, vistas Blade, eventos y rutas (<code>php artisan optimize:clear</code>).
            </div>
        </a>

        <!-- Storage Link -->
        <a href="?action=storage_link" class="btn-card">
            <div class="btn-card-title">
                <span>🔗 Enlace de Archivos (Storage Link)</span>
                <span class="icon-tag">📁</span>
            </div>
            <div class="btn-card-desc">
                Crea el enlace simbólico para que las imágenes y documentos subidos sean accesibles públicamente.
            </div>
        </a>

        <!-- Optimizar Rutas & Config -->
        <a href="?action=optimize" class="btn-card">
            <div class="btn-card-title">
                <span>⚡ Optimizar Rutas y Vistas</span>
                <span class="icon-tag">🚀</span>
            </div>
            <div class="btn-card-desc">
                Pre-compila las rutas y la configuración para obtener la máxima velocidad de respuesta en producción.
            </div>
        </a>
    </div>

    <a href="/" class="btn-portal">
        <span>Ir al Portal Principal METRIC V2</span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/>
            <polyline points="12 5 19 12 12 19"/>
        </svg>
    </a>
</div>
</body>
</html>
