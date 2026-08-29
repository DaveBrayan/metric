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
                'resources/js/app.js',
                'resources/js/dashboard.js',
                'resources/js/login.js',
                'resources/js/settings.js',
                'resources/js/admins.js',
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
