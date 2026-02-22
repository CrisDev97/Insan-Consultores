(function () {
  const modal = document.getElementById('confirmModal');
  if (!modal) return;

  const backdrop = modal.querySelector('.modal__backdrop');
  const closeBtns = modal.querySelectorAll('[data-close="1"]');
  const form = document.getElementById('confirmForm');
  const nameEl = document.getElementById('confirmName');

  const sessionsBox = document.getElementById('sessionsBox');
  const sessionsCountInput = document.querySelector('[name="sessions_count"]');

  function renderSessionInputs(count) {
    sessionsBox.innerHTML = '';

    const data = (window.__serviceSessions || []);
    const map = {};
    data.forEach(x => map[x.session_number] = x.duration_minutes);

    for (let i = 1; i <= count; i++) {
      const value = map[i] ?? 60;

      const wrap = document.createElement('div');
      wrap.innerHTML = `
        <label class="text-sm text-slate-600">Sesión ${i} (minutos)</label>
        <input
          type="number"
          min="15"
          max="480"
          step="5"
          name="session_durations[${i}]"
          value="${value}"
          class="mt-1 w-full rounded-lg border-slate-300"
          required
        >
      `;
      sessionsBox.appendChild(wrap);
    }
  }

  // inicial
  const initialCount = parseInt(sessionsCountInput?.value || '0', 10);
  if (initialCount > 0) renderSessionInputs(initialCount);

  sessionsCountInput?.addEventListener('input', () => {
    const c = parseInt(sessionsCountInput.value || '0', 10);
    if (c > 0 && c <= 20) renderSessionInputs(c);
    else sessionsBox.innerHTML = '';
  });

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
