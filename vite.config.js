import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/pos.css',
                'resources/css/productos.css',
                'resources/css/documentos.css',
                'resources/js/app.js',
                'resources/js/pos.js',
                'resources/js/productos.js',
                'resources/css/app.css', 
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
     server: {
        host: 'localhost', // Evita el problema de IPv6 [::1]
        cors: true,        // Permite que Laravel acceda a los assets
    },
});
