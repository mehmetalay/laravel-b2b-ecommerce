import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
    },
    publicDir: false,
    build: {
        outDir: 'public/build',
        emptyOutDir: true,
        manifest: 'manifest.json',
        rollupOptions: {
            input: {
                app: 'resources/js/app.ts',
                products: 'resources/js/frontend/products.js',
            },
            output: {
                entryFileNames: (chunkInfo: any) => {
                    if (chunkInfo.name === 'products') {
                        return 'frontend/products.js';
                    }

                    return 'app.js';
                },
                chunkFileNames: 'chunks/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
    },
});
