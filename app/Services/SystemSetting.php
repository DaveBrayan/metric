<?php

namespace App\Services;

class SystemSetting
{
    /**
     * Get a setting value by section and key with fallback to defaults.
     */
    public static function get($section, $key, $default = null)
    {
        $settingsFile = storage_path('app/settings.json');
        if (file_exists($settingsFile)) {
            $data = json_decode(file_get_contents($settingsFile), true);
            if (isset($data[$section][$key]) && !empty($data[$section][$key])) {
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
            ]
        ];

        return $defaults[$section][$key] ?? $default;
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
