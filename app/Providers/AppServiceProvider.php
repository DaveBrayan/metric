<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce Operational Timezone from System Settings (Configuración -> Zona Horaria de Operaciones)
        try {
            $tz = \App\Services\SystemSetting::getTimezone();
            if (!empty($tz)) {
                date_default_timezone_set($tz);
                config(['app.timezone' => $tz]);
            }
        } catch (\Throwable $e) {
        }

        // Auto-Serving & Live-Sync Stylesheets (No npm run build required!)
        Blade::directive('metricStyle', function ($expression) {
            return "<?php 
                \$raw = trim($expression, \"'\\\"\");
                \$file = basename(\$raw);
                if (!str_ends_with(\$file, '.css')) { \$file .= '.css'; }
                \$resPath = resource_path('css/' . \$file);
                \$pubPath = public_path('css/' . \$file);
                \$v = time();
                if (file_exists(\$resPath)) {
                    \$v = filemtime(\$resPath);
                    if (!file_exists(\$pubPath) || filemtime(\$resPath) > @filemtime(\$pubPath)) {
                        @copy(\$resPath, \$pubPath);
                    }
                } elseif (file_exists(\$pubPath)) {
                    \$v = filemtime(\$pubPath);
                }
                echo '<link rel=\"stylesheet\" href=\"' . asset('css/' . \$file) . '?v=' . \$v . '\">';
            ?>";
        });

        // Auto-Serving & Live-Sync Scripts (No npm run build required!)
        Blade::directive('metricScript', function ($expression) {
            return "<?php 
                \$raw = trim($expression, \"'\\\"\");
                \$file = basename(\$raw);
                if (!str_ends_with(\$file, '.js')) { \$file .= '.js'; }
                \$resPath = resource_path('js/' . \$file);
                \$pubPath = public_path('js/' . \$file);
                \$v = time();
                if (file_exists(\$resPath)) {
                    \$v = filemtime(\$resPath);
                    if (!file_exists(\$pubPath) || filemtime(\$resPath) > @filemtime(\$pubPath)) {
                        @copy(\$resPath, \$pubPath);
                    }
                } elseif (file_exists(\$pubPath)) {
                    \$v = filemtime(\$pubPath);
                }
                echo '<script src=\"' . asset('js/' . \$file) . '?v=' . \$v . '\"></script>';
            ?>";
        });
    }
}

