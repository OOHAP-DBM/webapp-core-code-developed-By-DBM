import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],

    build: {
        // Inline assets smaller than 4 KB as base64 to reduce HTTP requests
        assetsInlineLimit: 4096,

        // Enable CSS code-splitting: each async JS chunk gets its own CSS file
        cssCodeSplit: true,

        // Source-maps only in development (reduces bundle size in production)
        sourcemap: false,

        rollupOptions: {
            output: {
                // Content-hashed filenames for long-term caching
                entryFileNames:  'assets/[name]-[hash].js',
                chunkFileNames:  'assets/[name]-[hash].js',
                assetFileNames:  'assets/[name]-[hash][extname]',

                // Split large third-party libraries into their own chunk
                manualChunks(id) {
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});
