(function(){
  const btn = document.getElementById('hambBtn');
  const nav = document.getElementById('mainNav');
  if(!btn || !nav) return;

  btn.addEventListener('click', () => nav.classList.toggle('open'));
})();
