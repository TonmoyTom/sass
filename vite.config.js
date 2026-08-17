import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.ts',
            ],
            ssr: 'resources/js/ssr.ts',
            refresh: [
                'resources/**',
                'Modules/**/resources/**',   // ← module page change korle o hot-reload hobe
                'routes/**',
                'Modules/**/routes/**',
            ],
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        tailwindcss(),
    ],

    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '~': path.resolve(__dirname, 'resources/sass'),
            '@modules': path.resolve(__dirname, 'Modules'),   // ← notun, module page-e import shortcut-er jonno (optional)
        },
    },

    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        cors: true,
        hmr: {
            host: 'localhost',
            port: 5173,
            protocol: 'ws',
            clientPort: 5173,
        },
        origin: 'http://localhost:5173',
        watch: {
            usePolling: process.env.VITE_POLLING === 'true',
            interval: 300,
            ignored: [
                '**/node_modules/**',
                '**/vendor/**',
                '**/storage/**',
                '**/.git/**',
                '**/public/build/**',
            ],
        },
    },
});
