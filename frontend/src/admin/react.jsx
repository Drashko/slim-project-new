import "../preamble.js";
import React from "react";
import ReactDOM from "react-dom/client";
import { reactRegistry } from "./reactRegistry.js";

const resolveComponent = (name) => {
  if (!name) {
    return reactRegistry.App;
  }

  return reactRegistry[name] ?? reactRegistry.App;
};

const parseProps = (value) => {
  if (!value) {
    return {};
  }

  try {
    return JSON.parse(value);
  } catch (error) {
    console.warn("Failed to parse React props payload.", error);
    return {};
  }
};

const mountReactRoots = () => {
  const nodes = document.querySelectorAll("[data-react-component]");

  if (nodes.length === 0) {
    return;
  }

  nodes.forEach((node) => {
    const componentName = node.dataset.reactComponent || "App";
    const Component = resolveComponent(componentName);
    const props = parseProps(node.dataset.reactProps);

    ReactDOM.createRoot(node).render(
      <React.StrictMode>
        <Component {...props} />
      </React.StrictMode>
    );
  });
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", mountReactRoots);
} else {
  mountReactRoots();
}
