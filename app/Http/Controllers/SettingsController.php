<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';
        
        $settingsFile = storage_path('app/settings.json');
        $savedSettings = [];
        if (file_exists($settingsFile)) {
            $savedSettings = json_decode(file_get_contents($settingsFile), true) ?: [];
        }

        // Detectar IPs locales del servidor
        $hostname = gethostname();
        $localIp = gethostbyname($hostname);
        if ($localIp === '127.0.0.1' || empty($localIp)) {
            $localIp = request()->server('SERVER_ADDR', '192.168.1.100');
        }
        $port = request()->server('SERVER_PORT', '8000');
        if ($port == '80' || $port == '443') {
            $portSuffix = '';
        } else {
            $portSuffix = ':' . $port;
        }

        $suggestedUrls = [
            'local_ip' => "http://{$localIp}{$portSuffix}/api",
            'emulator' => "http://10.0.2.2{$portSuffix}/api",
            'localhost' => "http://127.0.0.1{$portSuffix}/api",
        ];

        $currentApiUrl = $savedSettings['api']['app_api_url'] ?? $suggestedUrls['local_ip'];

        $settings = [
            'general' => array_merge([
                'org_name' => 'Pachabol Industrial & Metrics S.A.',
                'system_name' => 'Metric v2',
                'timezone' => 'America/La_Paz',
                'language' => 'es_BO',
                'theme' => 'light',
                'auto_refresh' => '30s',
            ], $savedSettings['general'] ?? []),
            'security' => array_merge([
                'two_factor_auth' => true,
                'session_timeout' => 45, // minutos
                'password_expiry' => 90, // días
                'ip_whitelist_enabled' => false,
                'min_password_length' => 12,
            ], $savedSettings['security'] ?? []),
            'alerts' => array_merge([
                'notify_email' => true,
                'notify_sms' => false,
                'notify_webhook' => true,
                'temp_threshold' => 78.5, // °C
                'pressure_threshold' => 120.0, // PSI
                'flow_min_threshold' => 45.0, // L/min
                'daily_digest' => true,
            ], $savedSettings['alerts'] ?? []),
            'api' => array_merge([
                'api_key' => 'pk_live_pacha_98f4a7b1c3e6d8920fa58c4129',
                'webhook_url' => 'https://api.pachabol.com/v1/telemetry/events',
                'app_api_url' => $currentApiUrl,
                'rate_limit' => '10,000 req/min',
                'last_synced' => 'En línea',
            ], $savedSettings['api'] ?? [])
        ];

        return view('settings.index', compact('userName', 'userRole', 'settings', 'suggestedUrls'));
    }

    public function update(Request $request)
    {
        $settingsFile = storage_path('app/settings.json');
        $current = [];
        if (file_exists($settingsFile)) {
            $current = json_decode(file_get_contents($settingsFile), true) ?: [];
        }

        if ($request->filled('org_name')) {
            $current['general']['org_name'] = $request->input('org_name');
        }
        if ($request->filled('system_name')) {
            $current['general']['system_name'] = $request->input('system_name');
        }
        if ($request->filled('timezone')) {
            $current['general']['timezone'] = $request->input('timezone');
        }
        if ($request->filled('language')) {
            $current['general']['language'] = $request->input('language');
        }

        if ($request->filled('app_api_url')) {
            $current['api']['app_api_url'] = rtrim(trim($request->input('app_api_url')), '/');
        }
        if ($request->filled('webhook_url')) {
            $current['api']['webhook_url'] = trim($request->input('webhook_url'));
        }

        $dir = dirname($settingsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($settingsFile, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return redirect()->route('settings.index')->with('success', 'Configuración del sistema y URL de la app móvil actualizadas correctamente.');
    }
}
