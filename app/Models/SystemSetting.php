<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $table = 'system_settings';

    protected $fillable = [
        'key',
        'value',
        'group',
    ];

    /**
     * Cache estático en memoria para la petición actual
     */
    protected static array $runtimeCache = [];

    /**
     * Obtiene un valor de configuración de la base de datos por su clave.
     */
    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, self::$runtimeCache)) {
            return self::$runtimeCache[$key];
        }

        try {
            $record = self::where('key', $key)->first();
            if ($record) {
                $val = $record->value;
                // Si es un JSON serializado
                if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
                    $decoded = json_decode($val, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $val = $decoded;
                    }
                }
                self::$runtimeCache[$key] = $val;
                return $val;
            }
        } catch (\Throwable $e) {
            // En caso de que la tabla aún no exista o error de conexión
        }

        return $default;
    }

    /**
     * Guarda o actualiza un valor de configuración en la base de datos.
     */
    public static function set(string $key, $value, string $group = 'general'): self
    {
        $storedValue = is_array($value) || is_object($value) ? json_encode($value) : $value;

        $record = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'group' => $group,
            ]
        );

        self::$runtimeCache[$key] = $value;

        return $record;
    }

    /**
     * Obtiene todas las configuraciones agrupadas por su grupo.
     */
    public static function getAllGrouped(): array
    {
        $grouped = [];
        try {
            $records = self::all();
            foreach ($records as $r) {
                $val = $r->value;
                if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
                    $decoded = json_decode($val, true);
                    if (json_last_error() === JSON_ERROR_NONE) {
                        $val = $decoded;
                    }
                }
                $grouped[$r->group][$r->key] = $val;
                if (str_contains($r->key, '.')) {
                    $cleanKey = explode('.', $r->key, 2)[1];
                    $grouped[$r->group][$cleanKey] = $val;
                }
            }
        } catch (\Throwable $e) {}

        return $grouped;
    }

    /**
     * Limpia la caché en memoria.
     */
    public static function flushCache(): void
    {
        self::$runtimeCache = [];
    }
}
