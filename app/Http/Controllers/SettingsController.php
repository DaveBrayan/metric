<?php

namespace App\Http\Controllers;

use App\Services\AiService;
use App\Services\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';
        
        $savedSettings = SystemSetting::getAllGrouped();

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

        $geminiCfg = AiService::getGeminiConfig();
        $openaiCfg = AiService::getOpenAiConfig();
        $activeProvider = AiService::getActiveProvider();

        $settings = [
            'general' => array_merge([
                'org_name' => 'Pachabol S.R.L.',
                'system_name' => 'Metric v2',
                'timezone' => 'America/La_Paz',
                'language' => 'es_BO',
                'theme' => 'light',
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
            ], $savedSettings['api'] ?? []),
            'ai' => array_merge([
                'active_provider' => $activeProvider,
                'gemini_api_key' => $geminiCfg['api_key'],
                'gemini_model' => $geminiCfg['model'],
                'project_name' => $geminiCfg['project_name'] ?? '',
                'project_number' => $geminiCfg['project_number'] ?? '',
                'openai_api_key' => $openaiCfg['api_key'],
                'openai_model' => $openaiCfg['model'],
                'temperature' => 0.3,
            ], $savedSettings['ai'] ?? []),
        ];

        // Migrar automáticamente si el modelo guardado de Gemini era una versión descontinuada
        $deprecatedModels = ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-1.0-pro', 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-pro'];
        if (in_array($settings['ai']['gemini_model'] ?? '', $deprecatedModels)) {
            $settings['ai']['gemini_model'] = 'gemini-3.8-flash';
        }

        return view('settings.index', compact('userName', 'userRole', 'settings', 'suggestedUrls'));
    }

    public function update(Request $request)
    {
        if ($request->filled('org_name')) {
            SystemSetting::set('general', 'org_name', $request->input('org_name'));
        }
        if ($request->filled('system_name')) {
            SystemSetting::set('general', 'system_name', $request->input('system_name'));
        }
        if ($request->filled('timezone')) {
            SystemSetting::set('general', 'timezone', $request->input('timezone'));
        }
        if ($request->filled('language')) {
            SystemSetting::set('general', 'language', $request->input('language'));
        }

        if ($request->filled('app_api_url')) {
            SystemSetting::set('api', 'app_api_url', rtrim(trim($request->input('app_api_url')), '/'));
        }
        if ($request->filled('webhook_url')) {
            SystemSetting::set('api', 'webhook_url', trim($request->input('webhook_url')));
        }

        // Configuración de Proveedor de Inteligencia Artificial Activo
        if ($request->filled('active_provider')) {
            $provider = strtolower(trim($request->input('active_provider')));
            if (in_array($provider, ['gemini', 'openai'])) {
                SystemSetting::set('ai', 'active_provider', $provider);
            }
        }

        // Configuración de Google Gemini
        if ($request->has('gemini_api_key')) {
            SystemSetting::set('ai', 'gemini_api_key', trim($request->input('gemini_api_key')));
        }
        if ($request->filled('gemini_model')) {
            SystemSetting::set('ai', 'gemini_model', trim($request->input('gemini_model')));
        }
        if ($request->has('gemini_project_name')) {
            SystemSetting::set('ai', 'project_name', trim($request->input('gemini_project_name')));
        }
        if ($request->has('gemini_project_number')) {
            SystemSetting::set('ai', 'project_number', trim($request->input('gemini_project_number')));
        }

        // Configuración de OpenAI
        if ($request->has('openai_api_key')) {
            SystemSetting::set('ai', 'openai_api_key', trim($request->input('openai_api_key')));
        }
        if ($request->filled('openai_model')) {
            SystemSetting::set('ai', 'openai_model', trim($request->input('openai_model')));
        }

        return redirect()->route('settings.index')->with('success', 'Configuración de la plataforma y parámetros de IA actualizados correctamente.');
    }

    /**
     * Cambia de forma rápida el proveedor de IA activo mediante AJAX.
     */
    public function setActiveAiProvider(Request $request)
    {
        $provider = strtolower(trim($request->input('provider', '')));
        if (!in_array($provider, ['gemini', 'openai'])) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor de IA no válido. Selecciona "gemini" u "openai".'
            ], 422);
        }

        AiService::setActiveProvider($provider);
        $providerName = AiService::getActiveProviderName($provider);

        return response()->json([
            'success' => true,
            'active_provider' => $provider,
            'provider_name' => $providerName,
            'message' => "¡Proveedor de IA activo cambiado a {$providerName}!"
        ]);
    }

    /**
     * Obtiene en tiempo real los modelos disponibles para la clave de API de Gemini.
     */
    public function getAvailableGeminiModels(Request $request)
    {
        $apiKey = trim($request->input('gemini_api_key', ''));
        $result = AiService::getAvailableModels('gemini', $apiKey ?: null);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    /**
     * Prueba la conexión con la API de Google Gemini.
     */
    public function testGeminiConnection(Request $request)
    {
        $apiKey = trim($request->input('gemini_api_key', ''));
        $model = trim($request->input('gemini_model', ''));
        $result = AiService::testConnection('gemini', $apiKey ?: null, $model ?: null);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    /**
     * Obtiene en tiempo real los modelos disponibles para la clave de API de OpenAI.
     */
    public function getAvailableOpenAiModels(Request $request)
    {
        $apiKey = trim($request->input('openai_api_key', ''));
        $result = AiService::getAvailableModels('openai', $apiKey ?: null);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }

    /**
     * Prueba la conexión con la API de OpenAI.
     */
    public function testOpenAiConnection(Request $request)
    {
        $apiKey = trim($request->input('openai_api_key', ''));
        $model = trim($request->input('openai_model', ''));
        $result = AiService::testConnection('openai', $apiKey ?: null, $model ?: null);

        return response()->json($result, ($result['success'] ?? false) ? 200 : 400);
    }
}
