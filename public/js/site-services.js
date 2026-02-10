(function () {
  function openModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function closeModal(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.style.display = 'none';
    document.body.style.overflow = '';
  }

  document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[data-modal]');
    if (openBtn) {
      openModal(openBtn.dataset.modal);
      return;
    }

    const closeBtn = e.target.closest('[data-close]');
    if (closeBtn) {
      closeModal(closeBtn.dataset.close);
      return;
    }

    // click fuera del modal (overlay)
    if (e.target.classList.contains('modal-overlay')) {
      e.target.style.display = 'none';
      document.body.style.overflow = '';
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;

    document.querySelectorAll('.modal-overlay').forEach(m => {
      if (m.style.display === 'flex') m.style.display = 'none';
    });

    document.body.style.overflow = '';
  });
})();
