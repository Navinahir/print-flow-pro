import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/merchant.css',
                'resources/js/merchant.js',
                'resources/css/merchant-delivery-labels.css',
                'resources/js/merchant-delivery-labels.js',
            ],
            refresh: true,
        }),
    ],
});
