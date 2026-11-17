<?php

namespace App\Services;

use App\Models\SystemSetting as SystemSettingModel;

class SystemSetting
{
    /**
     * Get a setting value by section and key with fallback to database, file and defaults.
     */
    public static function get($section, $key, $default = null)
    {
        // 1. Intentar desde la base de datos (clave combinada o simple)
        $dbVal = SystemSettingModel::get("{$section}.{$key}");
        if ($dbVal !== null) {
            return $dbVal;
        }

        $dbValSimple = SystemSettingModel::get($key);
        if ($dbValSimple !== null) {
            return $dbValSimple;
        }

        // 2. Fallback a settings.json
        $settingsFile = storage_path('app/settings.json');
        if (file_exists($settingsFile)) {
            $data = json_decode(file_get_contents($settingsFile), true);
            if (isset($data[$section][$key]) && $data[$section][$key] !== '') {
                return $data[$section][$key];
            }
        }
        
        $defaults = [
            'general' => [
                'org_name' => 'Pachabol S.R.L.',
                'system_name' => 'Metric v2',
                'timezone' => 'America/La_Paz',
                'language' => 'es_BO',
            ],
            'api' => [
                'app_api_url' => 'http://127.0.0.1:8000/api',
            ],
            'ai' => [
                'active_provider' => 'gemini',
                'gemini_model' => 'gemini-3.8-flash',
                'openai_model' => 'gpt-4o-mini',
            ]
        ];

        return $defaults[$section][$key] ?? $default;
    }

    /**
     * Guarda un valor en la base de datos y sincroniza settings.json.
     */
    public static function set($section, $key, $value): void
    {
        // Guardar en base de datos
        SystemSettingModel::set("{$section}.{$key}", $value, $section);

        // Sincronizar archivo local de respaldo
        $settingsFile = storage_path('app/settings.json');
        $data = [];
        if (file_exists($settingsFile)) {
            $data = json_decode(file_get_contents($settingsFile), true) ?: [];
        }
        $data[$section][$key] = $value;
        $dir = dirname($settingsFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($settingsFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    /**
     * Obtiene todas las configuraciones agrupadas con fallback a settings.json y base de datos.
     */
    public static function getAllGrouped(): array
    {
        $settingsFile = storage_path('app/settings.json');
        $savedSettings = [];
        if (file_exists($settingsFile)) {
            $savedSettings = json_decode(file_get_contents($settingsFile), true) ?: [];
        }

        $dbGrouped = SystemSettingModel::getAllGrouped();

        // Normalizar claves del DB (por ejemplo: si la clave es "ai.active_provider", registrarla como "active_provider" dentro de "ai")
        foreach ($dbGrouped as $group => $items) {
            if (!isset($savedSettings[$group])) {
                $savedSettings[$group] = [];
            }
            foreach ($items as $k => $v) {
                $savedSettings[$group][$k] = $v;
                if (str_contains($k, '.')) {
                    $shortKey = explode('.', $k, 2)[1];
                    $savedSettings[$group][$shortKey] = $v;
                }
            }
        }

        return $savedSettings;
    }

    /**
     * Get configured system timezone.
     */
    public static function getTimezone(): string
    {
        return self::get('general', 'timezone', config('app.timezone', 'America/La_Paz')) ?: 'America/La_Paz';
    }

    /**
     * Get organization name.
     */
    public static function getOrgName(): string
    {
        return self::get('general', 'org_name', 'Pachabol S.R.L.');
    }
}
