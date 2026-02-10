(() => {
  const header = document.getElementById('siteHeader');
  const nav = document.getElementById('siteNav');
  const toggle = document.getElementById('navToggle');
  const overlay = document.getElementById('navOverlay');

  if (!header || !nav || !toggle || !overlay) return;

  const setOpen = (open) => {
    nav.classList.toggle('is-open', open);
    overlay.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');

    // animación del icono hamburguesa -> X
    const bar = toggle.querySelector('.nav-toggle__icon');
    if (bar) {
      if (open) {
        bar.style.background = 'transparent';
        bar.style.setProperty('--open', '1');
        bar.style.position = 'relative';
        bar.style.setProperty('transform', 'none');
        bar.style.setProperty('opacity', '1');
        bar.style.setProperty('transition', 'none');
      } else {
        bar.style.background = '';
      }
    }
  };

  toggle.addEventListener('click', () => {
    const open = toggle.getAttribute('aria-expanded') === 'true';
    setOpen(!open);
  });

  overlay.addEventListener('click', () => setOpen(false));

  // Cierra el menú al hacer click en un link (mobile)
  nav.querySelectorAll('a').forEach(a => {
    a.addEventListener('click', () => setOpen(false));
  });

  // Header "scrolled" class (opcional)
  window.addEventListener('scroll', () => {
    header.classList.toggle('is-scrolled', window.scrollY > 8);
  }, { passive: true });
})();

(() => {
  const root = document.querySelector('[data-carousel]');
  if (!root) return;

  const track = root.querySelector('[data-track]');
  const slides = Array.from(root.querySelectorAll('[data-slide]'));
  const prevBtn = root.querySelector('[data-prev]');
  const nextBtn = root.querySelector('[data-next]');
  const dotsWrap = root.querySelector('[data-dots]');

  if (!track || slides.length === 0) return;

  let index = 0;
  let timer = null;
  const AUTOPLAY_MS = 5000;

  // Dots
    const dots = slides.map((_, i) => {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = 'pro-carousel__dot' + (i === 0 ? ' is-active' : '');
    b.setAttribute('aria-label', `Ir al banner ${i + 1}`);
    b.addEventListener('click', () => goTo(i, true));
    dotsWrap && dotsWrap.appendChild(b);
    return b;
    });

  const render = () => {
    track.style.transform = `translateX(${-index * 100}%)`;
    dots.forEach((d, i) => d.classList.toggle('is-active', i === index));
  };

  const goTo = (i, userAction = false) => {
    index = (i + slides.length) % slides.length;
    render();
    if (userAction) restartAutoplay();
  };

  const next = () => goTo(index + 1);
  const prev = () => goTo(index - 1);

  nextBtn && nextBtn.addEventListener('click', () => next());
  prevBtn && prevBtn.addEventListener('click', () => prev());

  // Autoplay + pausa en hover (mejor UX)
  const startAutoplay = () => {
    if (slides.length <= 1) return;
    timer = setInterval(next, AUTOPLAY_MS);
  };
  const stopAutoplay = () => {
    if (timer) clearInterval(timer);
    timer = null;
  };
  const restartAutoplay = () => {
    stopAutoplay();
    startAutoplay();
  };

  root.addEventListener('mouseenter', stopAutoplay);
  root.addEventListener('mouseleave', startAutoplay);

  // Swipe básico (móvil)
  let startX = 0;
  let dragging = false;

  root.addEventListener('touchstart', (e) => {
    startX = e.touches[0].clientX;
    dragging = true;
    stopAutoplay();
  }, { passive: true });

  root.addEventListener('touchend', (e) => {
    if (!dragging) return;
    dragging = false;
    const endX = e.changedTouches[0].clientX;
    const diff = endX - startX;

    if (Math.abs(diff) > 40) {
      diff < 0 ? next() : prev();
    }
    startAutoplay();
  });

  render();
  startAutoplay();
})();

