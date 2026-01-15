const ready = () => {
  document.documentElement.dataset.publicAssets = "ready";
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", ready);
} else {
  ready();
}
