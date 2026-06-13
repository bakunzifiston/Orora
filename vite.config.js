import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/marketplace-landing.css',
                'resources/css/marketplace-shop.css',
                'resources/css/marketplace-learning.css',
                'resources/css/marketplace-about.css',
                'resources/css/marketplace-contact.css',
                'resources/css/marketplace-trace.css',
                'resources/js/app.js',
                'resources/js/farm-form.js',
                'resources/js/livestock-form.js',
                'resources/js/animal-form.js',
                'resources/js/health-form.js',
                'resources/js/feed-calculator-form.js',
                'resources/js/health-overview-charts.js',
                'resources/js/landing-page.js',
                'resources/js/marketplace-shop.js',
                'resources/js/marketplace-learning.js',
                'resources/js/marketplace-about.js',
                'resources/js/marketplace-contact.js',
                'resources/js/marketplace-trace.js',
                'resources/js/select-other-form.js',
            ],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
