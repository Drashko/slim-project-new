import { defineConfig } from 'vite';
import path from 'node:path';

export default defineConfig({
  root: path.resolve(__dirname, 'resources/public'),
  base: '/assets/public/',
  publicDir: false,
  server: {
    port: 5176,
    strictPort: true,
  },
  build: {
    manifest: true,
    outDir: path.resolve(__dirname, 'public/assets/public'),
    emptyOutDir: false,
    rollupOptions: {
      input: {
        public: path.resolve(__dirname, 'resources/public/main.js'),
      },
    },
  },
});
