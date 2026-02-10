(function () {
  const modal = document.getElementById('confirmModal');
  if (!modal) return;

  const backdrop = modal.querySelector('.modal__backdrop');
  const closeBtns = modal.querySelectorAll('[data-close="1"]');
  const form = document.getElementById('confirmForm');
  const nameEl = document.getElementById('confirmName');

  function openModal() {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeModal() {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    if (form) form.setAttribute('action', '#');
    if (nameEl) nameEl.textContent = '---';
  }

  if (backdrop) backdrop.addEventListener('click', closeModal);
  closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

  window.__confirmDelete = function ({ action, name }) {
    if (!form) return;
    form.setAttribute('action', action);
    if (nameEl) nameEl.textContent = name || '---';
    openModal();
  };

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeModal();
  });
})();
