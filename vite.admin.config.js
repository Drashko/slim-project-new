import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
  root: path.resolve(__dirname, 'resources/admin'),
  base: '/assets/admin/',
  publicDir: false,
  server: {
    fs: {
      allow: [
        path.resolve(__dirname, 'resources'),
        path.resolve(__dirname, 'public'),
      ],
    },
  },
  build: {
    manifest: true,
    outDir: path.resolve(__dirname, 'public/assets/admin'),
    copyPublicDir: false,
    emptyOutDir: false,
    rollupOptions: {
      input: {
        admin: path.resolve(__dirname, 'resources/admin/main.js'),
      },
    },
  },
});
