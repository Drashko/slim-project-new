import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react";
import { resolve } from "node:path";

export default ({ command, mode }) => {
  const env = loadEnv(mode, process.cwd(), "");
  const outDir = env.REACT_ASSET_BUILD_PATH || "../public/assets/react";
  const base = env.REACT_ASSET_PUBLIC_PREFIX || "/assets/react/";

  return defineConfig({
    plugins: [react()],
    root: __dirname,
    base: command === "serve" ? "/" : base,
    build: {
      outDir,
      emptyOutDir: true,
      manifest: true,
      rollupOptions: {
        input: resolve(__dirname, "src/main.jsx"),
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
