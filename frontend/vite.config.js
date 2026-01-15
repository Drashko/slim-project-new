import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";
import { resolve } from "node:path";

export default ({ command, mode }) => {
  const env = loadEnv(mode, resolve(__dirname, ".."), "");
  const outDir = env.ASSET_BUILD_PATH || "../public/assets";
  const base = env.ASSET_PUBLIC_PREFIX || "/assets/";

  return defineConfig({
    plugins: [react()],
    root: __dirname,
    base: command === "serve" ? "/" : base,
    build: {
      outDir,
      emptyOutDir: true,
      manifest: true,
      rollupOptions: {
        input: {
          "public/main": resolve(__dirname, "src/public/main.js"),
          "public/react": resolve(__dirname, "src/main.jsx"),
          "admin/main": resolve(__dirname, "src/admin/main.js"),
          "admin/react": resolve(__dirname, "src/admin/react.jsx"),
        },
      },
    },
    server: {
      proxy: {
        "/api": {
          target: "http://localhost:8000",
          changeOrigin: true,
          secure: false,
        },
      },
    },
  });
};
