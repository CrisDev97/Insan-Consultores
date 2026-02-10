(function () {
  "use strict";

  // ===== Sidebar toggle (mobile)
  const shell = document.getElementById("adminShell");
  const btnSidebar = document.getElementById("btnSidebar");
  if (btnSidebar && shell) {
    btnSidebar.addEventListener("click", () => {
      shell.classList.toggle("sidebar-open");
    });
  }

  // Cerrar sidebar al hacer click fuera (mobile)
  document.addEventListener("click", (e) => {
    if (!shell) return;
    if (!shell.classList.contains("sidebar-open")) return;

    const sidebar = document.getElementById("adminSidebar");
    const btn = document.getElementById("btnSidebar");
    const target = e.target;

    const clickedSidebar = sidebar && sidebar.contains(target);
    const clickedButton = btn && btn.contains(target);

    if (!clickedSidebar && !clickedButton) {
      shell.classList.remove("sidebar-open");
    }
  });

  // ===== Modal confirm delete (reutilizable)
  const modal = document.getElementById("confirmModal");
  const confirmName = document.getElementById("confirmName");
  const confirmForm = document.getElementById("confirmForm");

  function openModal() {
    if (!modal) return;
    modal.classList.add("is-open");
    modal.setAttribute("aria-hidden", "false");
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
  }

  // Cerrar modal por backdrop / botón close / ESC
  if (modal) {
    modal.addEventListener("click", (e) => {
      const el = e.target;
      if (el && el.dataset && el.dataset.close === "1") closeModal();
    });
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") closeModal();
    });
  }

  // Función global para usarla desde Blade
  window.__confirmDelete = function ({ action, name }) {
    if (!modal || !confirmForm) {
      // fallback simple
      const ok = confirm(`¿Eliminar este registro${name ? " de " + name : ""}?`);
      if (ok) {
        window.location.href = action;
      }
      return;
    }

    if (confirmName) confirmName.textContent = name || "---";
    confirmForm.setAttribute("action", action);
    openModal();
  };
})();
