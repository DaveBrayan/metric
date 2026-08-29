<?php
/**
 * METRIC_V2 — Ejecutor Web de Migraciones de Base de Datos
 * Ejecuta `php artisan migrate --force` directamente dentro del servidor.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cargar Laravel
$baseDir = realpath(__DIR__ . '/..');

if (!file_exists($baseDir . '/vendor/autoload.php')) {
    die("<h2 style='color:#ef4444;font-family:sans-serif;'>❌ Error: No se encontró la carpeta vendor. Por favor descomprime vendor.zip primero.</h2>");
}

require $baseDir . '/vendor/autoload.php';
$app = require_once $baseDir . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Migraciones de Base de Datos — METRIC V2</title>
    <style>
        body { background: #0b1320; color: #f8fafc; font-family: monospace; padding: 25px; line-height: 1.6; }
        .box { max-width: 800px; margin: 0 auto; background: #131e32; border: 1px solid #1e293b; border-radius: 12px; padding: 24px; }
        h1 { color: #10b9df; font-size: 20px; margin-bottom: 15px; }
        pre { background: #020617; border: 1px solid #1e293b; padding: 15px; border-radius: 8px; color: #38bdf8; overflow-x: auto; white-space: pre-wrap; font-size: 13px; }
        .success { color: #86efac; font-weight: bold; margin-top: 15px; font-size: 15px; }
        .btn { display: inline-block; padding: 10px 20px; background: #10b9df; color: #0b1320; font-weight: bold; text-decoration: none; border-radius: 6px; margin-top: 15px; }
    </style>
</head>
<body>
<div class="box">
    <h1>🗄️ Ejecutando Migraciones en `pachabol_metric`...</h1>
    <pre>
<?php
try {
    Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    $output = Illuminate\Support\Facades\Artisan::output();
    echo htmlspecialchars($output);
    echo "</pre>";
    echo "<div class='success'>✅ ¡Migraciones ejecutadas con éxito en la base de datos!</div>";
    echo "<p><a href='/' class='btn'>Ir al Sistema METRIC V2 ➔</a></p>";
} catch (\Throwable $e) {
    echo "❌ Error al ejecutar migraciones:\n" . htmlspecialchars($e->getMessage());
    echo "</pre>";
    echo "<p style='color:#fca5a5;margin-top:10px;'>Verifica que el archivo <code>.env</code> en el servidor tenga <code>DB_HOST=localhost</code>.</p>";
}
?>
</div>
</body>
</html>
