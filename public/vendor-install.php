<?php
/**
 * METRIC_V2 — Ejecutor Web de Composer
 * Al abrir este archivo en el navegador ejecuta: composer install --no-dev --optimize-autoloader
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(600);
ini_set('memory_limit', '1024M');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Instalando Vendor — METRIC V2</title>
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
    <h1>🚀 Ejecutando Composer Install en Producción...</h1>
<?php

$root = realpath(__DIR__ . '/..');
putenv("COMPOSER_HOME=$root/.composer");

echo "<p>📂 Directorio del proyecto: <code>" . htmlspecialchars($root) . "</code></p>";
echo "<pre>";

// Buscar la ruta de PHP y Composer en el servidor
$phpBin = PHP_BINARY ? PHP_BINARY : 'php';
$composerCmd = "composer";

// Probar si composer existe en el sistema
$testComposer = @shell_exec("composer --version 2>&1");
if (!$testComposer || stripos($testComposer, 'Composer version') === false) {
    // Si no está global, descargar composer.phar automáticamente
    if (!file_exists($root . '/composer.phar')) {
        echo "⬇️ Descargando composer.phar en el servidor...\n";
        @copy('https://getcomposer.org/composer-stable.phar', $root . '/composer.phar');
    }
    if (file_exists($root . '/composer.phar')) {
        $composerCmd = "$phpBin $root/composer.phar";
    }
}

$fullCommand = "cd " . escapeshellarg($root) . " && $composerCmd install --no-dev --optimize-autoloader 2>&1";

echo "💻 Comando: $fullCommand\n\n";

// Ejecutar comando
$output = shell_exec($fullCommand);

if ($output) {
    echo htmlspecialchars($output);
} else {
    echo "⚠️ El servidor no devolvió salida en shell_exec. Es posible que las funciones exec/shell_exec estén bloqueadas por la seguridad del hosting.";
}

echo "</pre>";

if (file_exists($root . '/vendor/autoload.php')) {
    echo "<div class='success'>✅ ¡Éxito! La carpeta <b>vendor</b> ha sido instalada y el proyecto está listo.</div>";
    echo "<p><a href='/' class='btn'>Ir al Sistema METRIC V2 ➔</a></p>";
} else {
    echo "<p style='color:#fca5a5;'>Si no se instaló, tu hosting tiene bloqueada la ejecución de comandos desde la web. En ese caso sólo sube el archivo <b>vendor.zip</b> por el Administrador de Archivos de cPanel y extráelo.</p>";
}
?>
</div>
</body>
</html>
