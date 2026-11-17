<?php

namespace App\Services;

use App\Models\SystemSetting as SystemSettingModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiService
{
    /**
     * Proveedores soportados.
     */
    public const PROVIDER_GEMINI = 'gemini';
    public const PROVIDER_OPENAI = 'openai';

    /**
     * Obtiene el proveedor de IA actualmente activo ('gemini' o 'openai').
     */
    public static function getActiveProvider(): string
    {
        $active = SystemSetting::get('ai', 'active_provider', self::PROVIDER_GEMINI);
        return in_array($active, [self::PROVIDER_GEMINI, self::PROVIDER_OPENAI]) ? $active : self::PROVIDER_GEMINI;
    }

    /**
     * Establece el proveedor de IA activo.
     */
    public static function setActiveProvider(string $provider): void
    {
        if (in_array($provider, [self::PROVIDER_GEMINI, self::PROVIDER_OPENAI])) {
            SystemSetting::set('ai', 'active_provider', $provider);
        }
    }

    /**
     * Obtiene el nombre descriptivo del proveedor activo.
     */
    public static function getActiveProviderName(?string $provider = null): string
    {
        $p = $provider ?: self::getActiveProvider();
        return match ($p) {
            self::PROVIDER_OPENAI => 'OpenAI (ChatGPT)',
            default => 'Google Gemini',
        };
    }

    /**
     * Obtiene la configuración de Google Gemini desde BD / settings.json / env.
     */
    public static function getGeminiConfig(): array
    {
        $apiKey = SystemSetting::get('ai', 'gemini_api_key')
            ?: (config('services.gemini.api_key') ?: env('GEMINI_API_KEY', ''));

        $model = SystemSetting::get('ai', 'gemini_model')
            ?: (config('services.gemini.model') ?: env('GEMINI_MODEL', 'gemini-3.8-flash'));

        // Normalización de modelos obsoletos
        $deprecatedReplacements = [
            'gemini-1.5-flash' => 'gemini-3.8-flash',
            'gemini-1.5-pro' => 'gemini-3.1-pro-preview',
            'gemini-2.0-flash' => 'gemini-3.8-flash',
            'gemini-1.0-pro' => 'gemini-3.8-flash',
            'gemini-2.5-flash' => 'gemini-3.8-flash',
            'gemini-2.5-pro' => 'gemini-3.1-pro-preview',
        ];
        if (isset($deprecatedReplacements[$model])) {
            $model = $deprecatedReplacements[$model];
        }

        return [
            'api_key' => trim((string)$apiKey),
            'model' => trim((string)$model),
            'project_name' => SystemSetting::get('ai', 'project_name', ''),
            'project_number' => SystemSetting::get('ai', 'project_number', ''),
        ];
    }

    /**
     * Obtiene la configuración de OpenAI desde BD / settings.json / env.
     */
    public static function getOpenAiConfig(): array
    {
        $apiKey = SystemSetting::get('ai', 'openai_api_key')
            ?: (config('services.openai.api_key') ?: env('OPENAI_API_KEY', ''));

        $model = SystemSetting::get('ai', 'openai_model')
            ?: (config('services.openai.model') ?: env('OPENAI_MODEL', 'gpt-4o-mini'));

        return [
            'api_key' => trim((string)$apiKey),
            'model' => trim((string)$model) ?: 'gpt-4o-mini',
        ];
    }

    /**
     * Genera contenido utilizando el proveedor de IA actualmente activo.
     */
    public static function generateContent(string $prompt, array $options = []): array
    {
        $provider = $options['provider'] ?? self::getActiveProvider();

        if ($provider === self::PROVIDER_OPENAI) {
            return self::generateWithOpenAi($prompt, $options);
        }

        return self::generateWithGemini($prompt, $options);
    }

    /**
     * Llamada a la API de Google Gemini.
     */
    protected static function generateWithGemini(string $prompt, array $options = []): array
    {
        $cfg = self::getGeminiConfig();
        $apiKey = $options['api_key'] ?? $cfg['api_key'];
        $model = $options['model'] ?? $cfg['model'];
        $temperature = $options['temperature'] ?? 0.35;
        $maxTokens = $options['max_tokens'] ?? 4096;

        if (empty($apiKey)) {
            return [
                'success' => false,
                'provider' => self::PROVIDER_GEMINI,
                'provider_name' => 'Google Gemini',
                'message' => 'No se ha configurado la clave API de Google Gemini en la base de datos o en la Configuración de Plataforma.',
                'status' => 422
            ];
        }

        try {
            $encodedKey = urlencode($apiKey);
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$encodedKey}";

            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'contents' => [
                        [
                            'role' => 'user',
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => (float)$temperature,
                        'maxOutputTokens' => (int)$maxTokens,
                    ]
                ]);

            // Reintento automático si hay saturación temporal (503)
            if (!$response->successful() && $response->status() === 503 && $model !== 'gemini-3.8-flash') {
                $model = 'gemini-3.8-flash';
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$encodedKey}";
                $response = Http::withoutVerifying()
                    ->timeout(60)
                    ->withHeaders([
                        'x-goog-api-key' => $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post($url, [
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'temperature' => (float)$temperature,
                            'maxOutputTokens' => (int)$maxTokens,
                        ]
                    ]);
            }

            if (!$response->successful()) {
                $status = $response->status();
                $err = $response->json('error.message') ?? $response->body();
                $hint = '';
                if ($status === 401 || $status === 403) {
                    $hint = ' (Asegúrate de que la clave API sea válida en Google AI Studio: https://aistudio.google.com/app/apikey).';
                } elseif ($status === 503) {
                    $hint = ' (El servicio de Google Gemini está saturado temporalmente. Por favor intenta en unos segundos).';
                }
                return [
                    'success' => false,
                    'provider' => self::PROVIDER_GEMINI,
                    'provider_name' => 'Google Gemini',
                    'message' => "Error de la API de Gemini ({$status}): {$err}{$hint}",
                    'status' => $status
                ];
            }

            $rawText = trim($response->json('candidates.0.content.parts.0.text') ?? '');

            return [
                'success' => true,
                'text' => $rawText,
                'provider' => self::PROVIDER_GEMINI,
                'provider_name' => 'Google Gemini',
                'model' => $model,
                'status' => 200
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'provider' => self::PROVIDER_GEMINI,
                'provider_name' => 'Google Gemini',
                'message' => 'Error de conexión con Google Gemini: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }

    /**
     * Llamada a la API de OpenAI (ChatGPT).
     */
    protected static function generateWithOpenAi(string $prompt, array $options = []): array
    {
        $cfg = self::getOpenAiConfig();
        $apiKey = $options['api_key'] ?? $cfg['api_key'];
        $model = $options['model'] ?? $cfg['model'];
        $temperature = $options['temperature'] ?? 0.35;
        $maxTokens = $options['max_tokens'] ?? 4096;

        if (empty($apiKey)) {
            return [
                'success' => false,
                'provider' => self::PROVIDER_OPENAI,
                'provider_name' => 'OpenAI',
                'message' => 'No se ha configurado la clave API de OpenAI en la base de datos o en la Configuración de Plataforma.',
                'status' => 422
            ];
        }

        try {
            $url = 'https://api.openai.com/v1/chat/completions';

            $response = Http::withoutVerifying()
                ->timeout(60)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Eres un especialista de alto nivel en Ergonomía Ocupacional, Seguridad y Salud en el Trabajo (SySO). Responde siempre en español formal y técnico según las instrucciones estructuradas solicitadas.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => (float)$temperature,
                    'max_tokens' => (int)$maxTokens,
                ]);

            if (!$response->successful()) {
                $status = $response->status();
                $err = $response->json('error.message') ?? $response->body();
                $hint = '';
                if ($status === 401) {
                    $hint = ' (Clave API de OpenAI incorrecta o revocada. Verifica tu clave en https://platform.openai.com/api-keys).';
                } elseif ($status === 429) {
                    $hint = ' (Límite de cuota o tasa de peticiones excedido en OpenAI. Verifica tu saldo o límite de uso en https://platform.openai.com/usage).';
                }
                return [
                    'success' => false,
                    'provider' => self::PROVIDER_OPENAI,
                    'provider_name' => 'OpenAI',
                    'message' => "Error de la API de OpenAI ({$status}): {$err}{$hint}",
                    'status' => $status
                ];
            }

            $rawText = trim($response->json('choices.0.message.content') ?? '');

            return [
                'success' => true,
                'text' => $rawText,
                'provider' => self::PROVIDER_OPENAI,
                'provider_name' => 'OpenAI',
                'model' => $model,
                'status' => 200
            ];

        } catch (\Throwable $e) {
            return [
                'success' => false,
                'provider' => self::PROVIDER_OPENAI,
                'provider_name' => 'OpenAI',
                'message' => 'Error de conexión con OpenAI: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }

    /**
     * Prueba la conexión con el proveedor indicado (o activo).
     */
    public static function testConnection(string $provider, ?string $apiKey = null, ?string $model = null): array
    {
        if ($provider === self::PROVIDER_OPENAI) {
            $apiKey = $apiKey ?: self::getOpenAiConfig()['api_key'];
            $model = $model ?: self::getOpenAiConfig()['model'];

            if (empty($apiKey)) {
                return [
                    'success' => false,
                    'message' => 'Por favor ingresa tu clave API de OpenAI para realizar la prueba.'
                ];
            }

            try {
                $response = Http::withoutVerifying()
                    ->timeout(20)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model ?: 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'user', 'content' => 'Responde únicamente con la palabra: CONECTADO']
                        ],
                        'temperature' => 0.1,
                        'max_tokens' => 15,
                    ]);

                if ($response->successful()) {
                    $reply = trim($response->json('choices.0.message.content') ?? 'CONECTADO');
                    return [
                        'success' => true,
                        'message' => "¡Conexión exitosa con OpenAI ({$model})! Respuesta: {$reply}",
                        'provider' => self::PROVIDER_OPENAI,
                        'model' => $model
                    ];
                }

                $status = $response->status();
                $err = $response->json('error.message') ?? $response->body();
                $hint = '';
                if ($status === 401) {
                    $hint = ' (Clave API inválida o expirada).';
                } elseif ($status === 429) {
                    $hint = ' (Cuota insuficiente o límite de velocidad alcanzado en tu cuenta OpenAI).';
                }
                return [
                    'success' => false,
                    'message' => "Error de API de OpenAI ({$status}): {$err}{$hint}"
                ];
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'message' => 'Error de conexión con OpenAI: ' . $e->getMessage()
                ];
            }
        }

        // Gemini
        $apiKey = $apiKey ?: self::getGeminiConfig()['api_key'];
        $model = $model ?: self::getGeminiConfig()['model'];

        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Por favor ingresa tu clave API de Google Gemini para realizar la prueba.'
            ];
        }

        // Auto-reemplazo
        $deprecated = [
            'gemini-1.5-flash' => 'gemini-3.8-flash',
            'gemini-1.5-pro' => 'gemini-3.1-pro-preview',
            'gemini-2.0-flash' => 'gemini-3.8-flash',
            'gemini-1.0-pro' => 'gemini-3.8-flash',
            'gemini-2.5-flash' => 'gemini-3.8-flash',
            'gemini-2.5-pro' => 'gemini-3.1-pro-preview',
        ];
        if (isset($deprecated[$model])) {
            $model = $deprecated[$model];
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
                return [
                    'success' => true,
                    'message' => "¡Conexión exitosa con Google Gemini ({$model})! Respuesta: {$reply}",
                    'provider' => self::PROVIDER_GEMINI,
                    'model' => $model
                ];
            }

            $status = $response->status();
            $err = $response->json('error.message') ?? $response->body();
            return [
                'success' => false,
                'message' => "Error de API de Gemini ({$status}): {$err}"
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión con Gemini: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lista los modelos disponibles del proveedor.
     */
    public static function getAvailableModels(string $provider, ?string $apiKey = null): array
    {
        if ($provider === self::PROVIDER_OPENAI) {
            $apiKey = $apiKey ?: self::getOpenAiConfig()['api_key'];
            if (empty($apiKey)) {
                return [
                    'success' => false,
                    'message' => 'Ingresa tu clave de API de OpenAI para listar los modelos disponibles.'
                ];
            }

            try {
                $response = Http::withoutVerifying()
                    ->timeout(15)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . $apiKey,
                        'Accept' => 'application/json',
                    ])
                    ->get('https://api.openai.com/v1/models');

                if ($response->successful()) {
                    $raw = $response->json('data') ?? [];
                    $models = [];
                    foreach ($raw as $m) {
                        $id = $m['id'] ?? '';
                        if (str_starts_with($id, 'gpt-') || str_starts_with($id, 'o1') || str_starts_with($id, 'o3') || str_starts_with($id, 'chatgpt-')) {
                            // Excluir modelos específicos de audio puro o imagen
                            if (!str_contains($id, 'transcribe') && !str_contains($id, 'tts') && !str_contains($id, 'realtime') && !str_contains($id, 'audio')) {
                                $models[] = [
                                    'id' => $id,
                                    'name' => $id,
                                    'description' => 'Modelo de lenguaje OpenAI'
                                ];
                            }
                        }
                    }

                    // Ordenar poniendo los más modernos primero
                    usort($models, function($a, $b) {
                        return strcmp($a['id'], $b['id']);
                    });

                    return [
                        'success' => true,
                        'models' => array_values($models)
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Error al obtener modelos de OpenAI: ' . ($response->json('error.message') ?? $response->body())
                ];
            } catch (\Throwable $e) {
                return [
                    'success' => false,
                    'message' => 'Error de conexión con OpenAI: ' . $e->getMessage()
                ];
            }
        }

        // Gemini
        $apiKey = $apiKey ?: self::getGeminiConfig()['api_key'];
        if (empty($apiKey)) {
            return [
                'success' => false,
                'message' => 'Ingresa tu clave de API de Gemini para listar los modelos disponibles.'
            ];
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
                        if (!str_contains($modelId, 'image') && !str_contains($modelId, 'tts') && !str_contains($modelId, 'robotics') && !str_contains($modelId, 'transcribe') && !str_contains($modelId, 'lyria')) {
                            $availableModels[] = [
                                'id' => $modelId,
                                'name' => $m['displayName'] ?? $modelId,
                                'description' => $m['description'] ?? '',
                            ];
                        }
                    }
                }

                return [
                    'success' => true,
                    'models' => $availableModels
                ];
            }

            return [
                'success' => false,
                'message' => 'Error al obtener modelos de Gemini: ' . ($response->json('error.message') ?? $response->body())
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión con Gemini: ' . $e->getMessage()
            ];
        }
    }
}
