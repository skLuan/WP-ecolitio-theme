import { defineConfig } from 'vite'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
    server: {
        host: true,
        port: 3000,
    },
    build: {
        outDir: 'dist',
        manifest: true,
        emptyOutDir: true,
        rollupOptions: {
            input: 'src/main.js',
        },
    },
    plugins: [
        tailwindcss(),
    ],
})