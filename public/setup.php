<?php
/**
 * METRIC_V2 — Web Installer & Dependency Manager
 * Permite instalar Composer o descomprimir vendor.zip directamente desde el navegador web.
 */

ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

$baseDir = realpath(__DIR__ . '/..');
$vendorDir = $baseDir . '/vendor';
$vendorZip = $baseDir . '/vendor.zip';
$publicVendorZip = __DIR__ . '/vendor.zip';

$action = $_GET['action'] ?? null;
$outputMessage = '';
$statusType = 'info';

if ($action === 'composer_install') {
    if (function_exists('shell_exec')) {
        $cmd = "cd " . escapeshellarg($baseDir) . " && composer install --no-dev --optimize-autoloader 2>&1";
        $cmdOutput = shell_exec($cmd);
        if ($cmdOutput) {
            $outputMessage = "Resultado de Composer:<br><pre style='background:#0f172a;color:#38bdf8;padding:15px;border-radius:8px;'>" . htmlspecialchars($cmdOutput) . "</pre>";
            $statusType = 'success';
        } else {
            $outputMessage = "El comando se ejecutó pero no devolvió salida. Verifica si la carpeta <b>vendor/</b> fue creada.";
            $statusType = 'info';
        }
    } else {
        $outputMessage = "La función shell_exec() está deshabilitada en este hosting. Usa la opción de <b>Descomprimir vendor.zip</b> abajo.";
        $statusType = 'error';
    }
}

if ($action === 'unzip_vendor') {
    $zipPath = file_exists($vendorZip) ? $vendorZip : (file_exists($publicVendorZip) ? $publicVendorZip : null);
    
    if ($zipPath && class_exists('ZipArchive')) {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($baseDir);
            $zip->close();
            $outputMessage = "¡La carpeta <b>vendor/</b> se descomprimió exitosamente en el servidor!";
            $statusType = 'success';
        } else {
            $outputMessage = "No se pudo abrir el archivo vendor.zip.";
            $statusType = 'error';
        }
    } else {
        $outputMessage = "No se encontró el archivo <code>vendor.zip</code> en la raíz del proyecto. Por favor sube <b>vendor.zip</b> a <code>" . htmlspecialchars($baseDir) . "</code>.";
        $statusType = 'error';
    }
}

if ($action === 'clear_cache') {
    $dirs = [
        $baseDir . '/bootstrap/cache/config.php',
        $baseDir . '/bootstrap/cache/routes.php',
        $baseDir . '/bootstrap/cache/services.php',
        $baseDir . '/bootstrap/cache/packages.php',
    ];
    foreach ($dirs as $f) {
        if (file_exists($f)) @unlink($f);
    }
    $outputMessage = "Cachés de Laravel limpiadas correctamente.";
    $statusType = 'success';
}

$hasVendor = file_exists($vendorDir . '/autoload.php');
$hasEnv = file_exists($baseDir . '/.env');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalador Web — METRIC V2</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: #0b1320; color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .installer-card { background: #131e32; border: 1px solid #1e293b; border-radius: 16px; max-width: 680px; width: 100%; padding: 32px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); }
        h1 { font-size: 24px; color: #10b9df; margin-bottom: 8px; }
        p { color: #94a3b8; font-size: 14px; line-height: 1.5; margin-bottom: 24px; }
        .status-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px; }
        .status-box { background: #1e293b; padding: 14px 18px; border-radius: 10px; display: flex; align-items: center; justify-content: space-between; font-size: 13px; font-weight: 600; }
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; }
        .badge.ok { background: #14532d; color: #86efac; border: 1px solid #22c55e; }
        .badge.missing { background: #7f1d1d; color: #fca5a5; border: 1px solid #ef4444; }
        .action-list { display: flex; flex-direction: column; gap: 14px; margin-bottom: 24px; }
        .btn-action { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; background: #1e293b; border: 1px solid #334155; border-radius: 12px; color: #ffffff; text-decoration: none; font-weight: 700; font-size: 14px; transition: all 0.2s; }
        .btn-action:hover { background: #0284c7; border-color: #38bdf8; transform: translateY(-2px); }
        .btn-action.secondary { background: #0f172a; border-color: #1e293b; }
        .btn-action.secondary:hover { background: #1e293b; border-color: #64748b; }
        .alert-box { padding: 14px 18px; border-radius: 10px; margin-bottom: 20px; font-size: 13.5px; line-height: 1.5; }
        .alert-box.success { background: #064e3b; border: 1px solid #059669; color: #a7f3d0; }
        .alert-box.error { background: #450a0a; border: 1px solid #dc2626; color: #fecaca; }
        .alert-box.info { background: #082f49; border: 1px solid #0284c7; color: #bae6fd; }
        .footer-note { font-size: 12px; color: #64748b; text-align: center; border-top: 1px solid #1e293b; padding-top: 16px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="installer-card">
        <h1>⚙️ Instalador Web & Diagnóstico — METRIC V2</h1>
        <p>Utilidad para inicializar dependencias de Laravel en servidores sin acceso a terminal SSH.</p>

        <?php if ($outputMessage): ?>
            <div class="alert-box <?= $statusType ?>">
                <?= $outputMessage ?>
            </div>
        <?php endif; ?>

        <div class="status-grid">
            <div class="status-box">
                <span>Carpeta /vendor</span>
                <span class="badge <?= $hasVendor ? 'ok' : 'missing' ?>"><?= $hasVendor ? 'INSTALADO ✓' : 'FALTA ✕' ?></span>
            </div>
            <div class="status-box">
                <span>Archivo .env</span>
                <span class="badge <?= $hasEnv ? 'ok' : 'missing' ?>"><?= $hasEnv ? 'CONFIGURADO ✓' : 'FALTA ✕' ?></span>
            </div>
        </div>

        <div class="action-list">
            <!-- Opción 1 -->
            <a href="?action=composer_install" class="btn-action">
                <div>
                    <div>1. Intentar instalar vía Composer Web</div>
                    <small style="color:#94a3b8;font-weight:400;font-size:12px;">Ejecuta composer install si el hosting tiene shell_exec activo</small>
                </div>
                <span>⚡ Ejecutar</span>
            </a>

            <!-- Opción 2 -->
            <a href="?action=unzip_vendor" class="btn-action">
                <div>
                    <div>2. Descomprimir vendor.zip</div>
                    <small style="color:#94a3b8;font-weight:400;font-size:12px;">Descomprime el archivo vendor.zip subido a la raíz</small>
                </div>
                <span>📦 Descomprimir</span>
            </a>

            <!-- Opción 3 -->
            <a href="?action=clear_cache" class="btn-action secondary">
                <div>
                    <div>3. Limpiar Cachés de Configuración</div>
                    <small style="color:#64748b;font-weight:400;font-size:12px;">Borra cachés previas de Laravel para aplicar el .env</small>
                </div>
                <span>🧹 Limpiar</span>
            </a>
        </div>

        <?php if ($hasVendor): ?>
            <div style="text-align: center; margin-top: 10px;">
                <a href="/" style="display: inline-block; padding: 12px 28px; background: #10b9df; color: #0f172a; font-weight: 800; text-decoration: none; border-radius: 8px;">
                    🚀 Ir al Sistema METRIC V2
                </a>
            </div>
        <?php endif; ?>

        <div class="footer-note">
            Una vez finalizada la instalación, por seguridad puedes eliminar el archivo <code>public/setup.php</code>.
        </div>
    </div>
</body>
</html>
