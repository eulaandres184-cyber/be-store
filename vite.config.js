import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS
                'resources/css/app.css',
                'resources/css/pos.css',
                // JS
                'resources/js/app.js',
                'resources/js/pos.js',
            ],
            refresh: true,
        }),
    ],
});
