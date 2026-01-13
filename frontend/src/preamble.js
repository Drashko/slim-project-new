if (import.meta.env.DEV && typeof window !== "undefined") {
  // Ensure the React Fast Refresh preamble runs even if the HTML isn't transformed by Vite.
  if (!window.__vite_plugin_react_preamble_installed__) {
    window.$RefreshReg$ = () => {};
    window.$RefreshSig$ = () => (type) => type;
    window.__vite_plugin_react_preamble_installed__ = true;
  }

  import("/@react-refresh").then((RefreshRuntime) => {
    RefreshRuntime.injectIntoGlobalHook(window);
  });
}
