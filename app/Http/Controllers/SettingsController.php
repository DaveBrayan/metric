<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
                'gemini_api_key' => env('GEMINI_API_KEY', ''),
                'gemini_model' => env('GEMINI_MODEL', 'gemini-3.8-flash'),
                'project_name' => '',
                'project_number' => '',
                'temperature' => 0.3,
            ], $savedSettings['ai'] ?? []),
        ];

        // Migrar automáticamente si el modelo guardado era una versión descontinuada
        $deprecatedModels = ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-1.0-pro', 'gemini-2.0-flash', 'gemini-2.5-flash', 'gemini-2.5-pro'];
        if (in_array($settings['ai']['gemini_model'] ?? '', $deprecatedModels)) {
            $settings['ai']['gemini_model'] = 'gemini-3.8-flash';
        }

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

        // Configuración de Inteligencia Artificial (Google Gemini)
        if ($request->has('gemini_api_key')) {
            $current['ai']['gemini_api_key'] = trim($request->input('gemini_api_key'));
        }
        if ($request->filled('gemini_model')) {
            $current['ai']['gemini_model'] = trim($request->input('gemini_model'));
        }
        if ($request->has('gemini_project_name')) {
            $current['ai']['project_name'] = trim($request->input('gemini_project_name'));
        }
        if ($request->has('gemini_project_number')) {
            $current['ai']['project_number'] = trim($request->input('gemini_project_number'));
        }

        $dir = dirname($settingsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($settingsFile, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return redirect()->route('settings.index')->with('success', 'Configuración de la plataforma y parámetros de IA actualizados correctamente.');
    }

    /**
     * Obtiene en tiempo real los modelos disponibles para la clave de API proporcionada.
     */
    public function getAvailableGeminiModels(Request $request)
    {
        $apiKey = trim($request->input('gemini_api_key', ''));

        if (empty($apiKey)) {
            $settingsFile = storage_path('app/settings.json');
            if (file_exists($settingsFile)) {
                $saved = json_decode(file_get_contents($settingsFile), true) ?: [];
                $apiKey = $saved['ai']['gemini_api_key'] ?? '';
            }
        }
        if (empty($apiKey)) {
            $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '');
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Ingresa tu clave de API de Gemini para listar los modelos disponibles.'
            ], 422);
        }

        try {
            $encodedKey = urlencode($apiKey);
            $url = "https://generativelanguage.googleapis.com/v1beta/models?key={$encodedKey}";
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Accept' => 'application/json',
                ])
                ->get($url);

            if ($response->successful()) {
                $rawModels = $response->json('models') ?? [];
                $availableModels = [];

                foreach ($rawModels as $m) {
                    $methods = $m['supportedGenerationMethods'] ?? [];
                    if (in_array('generateContent', $methods)) {
                        $modelId = str_replace('models/', '', $m['name']);
                        // Filtrar modelos no compatibles para texto general (como modelos solo de audio, musica o imagen pura)
                        if (!str_contains($modelId, 'image') && !str_contains($modelId, 'tts') && !str_contains($modelId, 'robotics') && !str_contains($modelId, 'transcribe') && !str_contains($modelId, 'lyria')) {
                            $availableModels[] = [
                                'id' => $modelId,
                                'name' => $m['displayName'] ?? $modelId,
                                'description' => $m['description'] ?? '',
                            ];
                        }
                    }
                }

                return response()->json([
                    'success' => true,
                    'models' => $availableModels
                ]);
            } else {
                $status = $response->status();
                $err = $response->json('error.message') ?? $response->body();
                return response()->json([
                    'success' => false,
                    'message' => "Error al obtener modelos ({$status}): {$err}"
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error de conexión: " . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Prueba la conexión con la API de Google Gemini utilizando la clave y modelo proporcionados.
     */
    public function testGeminiConnection(Request $request)
    {
        $apiKey = trim($request->input('gemini_api_key', ''));
        $model = trim($request->input('gemini_model', 'gemini-3.8-flash'));

        if (empty($apiKey)) {
            $settingsFile = storage_path('app/settings.json');
            if (file_exists($settingsFile)) {
                $saved = json_decode(file_get_contents($settingsFile), true) ?: [];
                $apiKey = $saved['ai']['gemini_api_key'] ?? '';
            }
        }
        if (empty($apiKey)) {
            $apiKey = config('services.gemini.api_key') ?: env('GEMINI_API_KEY', '');
        }

        if (empty($apiKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Por favor ingresa una clave de API de Gemini para realizar la prueba.'
            ], 422);
        }

        // Si se envió un modelo obsoleto conocido, sugerir o auto-ajustar
        $deprecatedReplacements = [
            'gemini-1.5-flash' => 'gemini-3.8-flash',
            'gemini-1.5-pro' => 'gemini-3.1-pro-preview',
            'gemini-2.0-flash' => 'gemini-3.8-flash',
            'gemini-1.0-pro' => 'gemini-3.8-flash',
            'gemini-2.5-flash' => 'gemini-3.8-flash',
            'gemini-2.5-pro' => 'gemini-3.1-pro-preview',
        ];

        if (isset($deprecatedReplacements[$model])) {
            $suggested = $deprecatedReplacements[$model];
            $model = $suggested;
        }

        try {
            $encodedKey = urlencode($apiKey);
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$encodedKey}";
            $response = Http::withoutVerifying()
                ->timeout(20)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => 'Hola, responde únicamente con la palabra: CONECTADO']
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.1,
                        'maxOutputTokens' => 20
                    ]
                ]);

            if ($response->successful()) {
                $reply = trim($response->json('candidates.0.content.parts.0.text') ?? 'CONECTADO');
                return response()->json([
                    'success' => true,
                    'message' => "¡Conexión exitosa con Google Gemini ({$model})! Respuesta: {$reply}",
                    'model' => $model
                ]);
            } else {
                $status = $response->status();
                $err = $response->json('error.message') ?? $response->body();
                
                $hint = '';
                if ($status === 401 || $status === 403) {
                    $hint = " — (Verifica que la clave de API sea válida y tenga permisos en Google AI Studio: https://aistudio.google.com/app/apikey)";
                } elseif ($status === 503) {
                    $hint = " — (El modelo seleccionado está experimentando alta demanda momentánea en los servidores de Google. Prueba seleccionando gemini-3.8-flash o gemini-3.5-flash).";
                } elseif ($status === 404) {
                    $hint = " — (El identificador del modelo no fue encontrado en la API. Se recomienda usar: gemini-3.8-flash o gemini-flash-latest).";
                }

                return response()->json([
                    'success' => false,
                    'message' => "Error de API de Gemini ({$status}): {$err}{$hint}"
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error de conexión: " . $e->getMessage()
            ], 500);
        }
    }
}
