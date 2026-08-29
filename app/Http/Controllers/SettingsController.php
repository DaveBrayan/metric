<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';
        
        $settings = [
            'general' => [
                'org_name' => 'Pachabol Industrial & Metrics S.A.',
                'system_name' => 'Metric v2',
                'timezone' => 'America/La_Paz (GMT-4)',
                'language' => 'es_BO',
                'theme' => 'light',
                'auto_refresh' => '30s',
            ],
            'security' => [
                'two_factor_auth' => true,
                'session_timeout' => 45, // minutos
                'password_expiry' => 90, // días
                'ip_whitelist_enabled' => false,
                'min_password_length' => 12,
            ],
            'alerts' => [
                'notify_email' => true,
                'notify_sms' => false,
                'notify_webhook' => true,
                'temp_threshold' => 78.5, // °C
                'pressure_threshold' => 120.0, // PSI
                'flow_min_threshold' => 45.0, // L/min
                'daily_digest' => true,
            ],
            'api' => [
                'api_key' => 'pk_live_pacha_98f4a7b1c3e6d8920fa58c4129',
                'webhook_url' => 'https://api.pachabol.com/v1/telemetry/events',
                'rate_limit' => '10,000 req/min',
                'last_synced' => 'Hace 4 minutos',
            ]
        ];

        return view('settings.index', compact('userName', 'userRole', 'settings'));
    }

    public function update(Request $request)
    {
        return redirect()->route('settings.index')->with('success', 'Configuración del sistema actualizada correctamente.');
    }
}
