import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pos.css',
                'resources/css/productos.css',
                'resources/js/app.js',
                'resources/js/pos.js',
                'resources/js/productos.js',
            ],
            refresh: true,
        }),
    ],
});
