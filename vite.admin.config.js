import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
  root: path.resolve(__dirname),
  base: '/assets/admin/',
  build: {
    manifest: true,
    outDir: path.resolve(__dirname, 'public/assets/admin'),
    emptyOutDir: true,
    rollupOptions: {
      input: {
        admin: path.resolve(__dirname, 'resources/admin/main.js'),
      },
    },
  },
});
