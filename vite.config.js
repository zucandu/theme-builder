import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '@theme': path.resolve(__dirname, './resources/js/components/themes/default'),
        },
    },
    server: {
        port: 5173,
        host: 'localhost',
    },
});
