import "./admin.css";

const ready = () => {
  document.documentElement.dataset.adminAssets = "ready";
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", ready);
} else {
  ready();
}
