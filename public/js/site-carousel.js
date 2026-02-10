(function () {
  const root = document.querySelector('[data-carousel="root"]');
  if (!root) return;

  const track = root.querySelector('[data-carousel="track"]');
  const slides = Array.from(root.querySelectorAll('[data-carousel="slide"]'));
  const prevBtn = root.querySelector('[data-carousel="prev"]');
  const nextBtn = root.querySelector('[data-carousel="next"]');
  const dotsWrap = root.querySelector('[data-carousel="dots"]');

  if (!track || slides.length === 0) return;

  let index = 0;
  let timer = null;
  const AUTOPLAY_MS = 4500;

  function render() {
    const offset = index * 100;
    track.style.transform = `translateX(-${offset}%)`;

    if (dotsWrap) {
      const dots = Array.from(dotsWrap.querySelectorAll('.carousel-dot'));
      dots.forEach((d, i) => d.classList.toggle('is-active', i === index));
    }
  }

  function goTo(i) {
    if (i < 0) index = slides.length - 1;
    else if (i >= slides.length) index = 0;
    else index = i;
    render();
  }

  function next() { goTo(index + 1); }
  function prev() { goTo(index - 1); }

  function stopAutoplay() {
    if (timer) window.clearInterval(timer);
    timer = null;
  }

  function startAutoplay() {
    if (slides.length <= 1) return;
    stopAutoplay();
    timer = window.setInterval(next, AUTOPLAY_MS);
  }

  // Dots
  if (dotsWrap) {
    dotsWrap.innerHTML = slides
      .map((_, i) => `<button class="carousel-dot ${i === 0 ? 'is-active' : ''}" type="button" aria-label="Ir al banner ${i+1}" data-dot="${i}"></button>`)
      .join('');

    dotsWrap.addEventListener('click', (e) => {
      const btn = e.target.closest('[data-dot]');
      if (!btn) return;
      stopAutoplay();
      goTo(parseInt(btn.dataset.dot, 10));
      startAutoplay();
    });
  }

  // Buttons
  if (prevBtn) prevBtn.addEventListener('click', () => { stopAutoplay(); prev(); startAutoplay(); });
  if (nextBtn) nextBtn.addEventListener('click', () => { stopAutoplay(); next(); startAutoplay(); });

  // Pause on hover (desktop)
  root.addEventListener('mouseenter', stopAutoplay);
  root.addEventListener('mouseleave', startAutoplay);

  // Swipe (mobile)
  let startX = 0;
  let isDown = false;

  root.addEventListener('pointerdown', (e) => {
    isDown = true;
    startX = e.clientX;
    stopAutoplay();
  });

  root.addEventListener('pointerup', (e) => {
    if (!isDown) return;
    isDown = false;
    const diff = e.clientX - startX;

    if (Math.abs(diff) > 40) {
      if (diff < 0) next();
      else prev();
    }
    startAutoplay();
  });

  root.addEventListener('pointercancel', () => {
    isDown = false;
    startAutoplay();
  });

  // Init
  render();
  startAutoplay();
})();
