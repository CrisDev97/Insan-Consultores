<div class="toast-wrap" data-toast-wrap>
  @if(session('success'))
    <div class="toast success" data-toast data-timeout="3500">
      <div class="toast-top">
        <div class="toast-title">✅ Acción realizada</div>
        <button class="toast-close" type="button" data-toast-close aria-label="Cerrar">×</button>
      </div>
      <div class="toast-msg">{{ session('success') }}</div>
      <div class="toast-bar" data-toast-bar><span></span></div>
    </div>
  @endif

  @if(session('error'))
    <div class="toast error" data-toast data-timeout="4500">
      <div class="toast-top">
        <div class="toast-title">⛔ Ocurrió un problema</div>
        <button class="toast-close" type="button" data-toast-close aria-label="Cerrar">×</button>
      </div>
      <div class="toast-msg">{{ session('error') }}</div>
      <div class="toast-bar" data-toast-bar><span></span></div>
    </div>
  @endif
</div>
