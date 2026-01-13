/* Minimal responsive sidebar toggle for Digiboard integration */
(function () {
  function closeSidebar() {
    document.body.classList.remove('sidebar-open');
  }

  function toggleSidebar() {
    document.body.classList.toggle('sidebar-open');
  }

  document.addEventListener('click', function (e) {
    var btn = e.target.closest('.sidebar-toggle-btn');
    if (btn) {
      e.preventDefault();
      toggleSidebar();
      return;
    }

    var backdrop = e.target.closest('.sidebar-backdrop');
    if (backdrop) {
      e.preventDefault();
      closeSidebar();
    }

    // Close after clicking a link on small screens
    var link = e.target.closest('.main-sidebar a');
    if (link && window.innerWidth < 992) {
      closeSidebar();
    }
  });

  // Ensure state is sane when resizing to desktop
  window.addEventListener('resize', function () {
    if (window.innerWidth >= 992) {
      closeSidebar();
    }
  });
})();
