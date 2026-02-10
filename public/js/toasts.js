(function () {
  const wrap = document.querySelector('[data-toast-wrap]');
  if (!wrap) return;

  function showToast(el, ms = 3500){
    const bar = el.querySelector('[data-toast-bar]');
    const close = el.querySelector('[data-toast-close]');

    requestAnimationFrame(() => el.classList.add('show'));

    // barra de tiempo
    if (bar) {
      const span = bar.querySelector('span');
      span.style.transitionDuration = ms + 'ms';
      requestAnimationFrame(() => span.style.transform = 'scaleX(0)');
    }

    const timer = setTimeout(() => hideToast(el), ms);

    if (close) {
      close.addEventListener('click', () => {
        clearTimeout(timer);
        hideToast(el);
      });
    }
  }

  function hideToast(el){
    el.classList.remove('show');
    setTimeout(() => el.remove(), 220);
  }

  document.querySelectorAll('[data-toast]').forEach((el) => {
    const ms = parseInt(el.getAttribute('data-timeout') || '3500', 10);
    showToast(el, ms);
  });
})();
