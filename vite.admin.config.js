import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
  root: path.resolve(__dirname, 'resources/admin'),
  base: '/assets/admin/',
  publicDir: false,
  server: {
    port: 5174,
    strictPort: true,
  },
  build: {
    manifest: true,
    outDir: path.resolve(__dirname, 'public/assets/admin'),
    emptyOutDir: false,
    rollupOptions: {
      input: {
        admin: path.resolve(__dirname, 'resources/admin/main.js'),
      },
    },
  },
});
