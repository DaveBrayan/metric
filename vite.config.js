import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/dashboard.css',
                'resources/css/login.css',
                'resources/css/settings.css',
                'resources/css/admins.css',
                'resources/css/managers.css',
                'resources/css/staff.css',
                'resources/css/companies.css',
                'resources/css/iluminaciones.css',
                'resources/css/ventilaciones.css',
                'resources/css/dosimetrias.css',
                'resources/css/ruido_ambientales.css',
                'resources/css/opacidades.css',
                'resources/css/estres_frios.css',
                'resources/css/estres_calores.css',
                'resources/js/app.js',
                'resources/js/dashboard.js',
                'resources/js/login.js',
                'resources/js/settings.js',
                'resources/js/admins.js',
                'resources/js/managers.js',
                'resources/js/staff.js',
                'resources/js/companies.js',
                'resources/js/iluminaciones.js',
                'resources/js/ventilaciones.js',
                'resources/js/dosimetrias.js',
                'resources/js/ruido_ambientales.js',
                'resources/js/opacidades.js',
                'resources/js/estres_frios.js',
                'resources/js/estres_calores.js',
            ],
            refresh: true,
        }),
    ],
    server: {
        hmr: {
            host: 'localhost',
        },
    },
});
