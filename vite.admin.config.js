import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';

export default defineConfig({
  root: path.resolve(__dirname, 'resources/admin'),
  base: '/assets/admin/',
  publicDir: false,
  plugins: [react()],
  server: {
    port: 5175,
    strictPort: true,
  },
  build: {
    manifest: true,
    outDir: path.resolve(__dirname, 'public/assets/admin'),
    emptyOutDir: false,
    rollupOptions: {
      input: {
        admin: path.resolve(__dirname, 'resources/admin/main.js'),
        react: path.resolve(__dirname, 'resources/admin/react.jsx'),
      },
    },
  },
});
