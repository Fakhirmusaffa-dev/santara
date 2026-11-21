import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
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
        // Ikut host APP_URL agar origin dev server sama dengan aplikasi.
        // Origin lain (mis. [::1]) akan diblokir CSP 'self' sehingga CSS/gambar tidak termuat.
        host: new URL(process.env.APP_URL ?? 'http://localhost').hostname,
        // Sajikan public/images dari root agar path /images/... di CSS konsisten di dev & build.
        proxy: {
            '/images': {
                target: process.env.APP_URL ?? 'http://localhost:8000',
            },
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
