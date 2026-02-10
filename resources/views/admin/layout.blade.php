<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Admin</title>
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

  <div class="admin-shell" id="adminShell">

    {{-- SIDEBAR --}}
    <aside class="admin-sidebar" id="adminSidebar">
      <div class="sb-brand">
        <div class="sb-brand__top">
          <div class="sb-brand__title">Portal Admin</div>
          <span class="sb-role">{{ auth()->user()->role }}</span>
        </div>

        <a class="sb-logo" href="{{ route('admin.dashboard') }}">
          <img src="{{ asset('img/brand/insan.PNG') }}" alt="Insan Consultores">
        </a>
      </div>

      <nav class="sb-nav">

        <a href="{{ route('admin.dashboard') }}"
           class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <span class="sb-ico">🏠</span>
          <span class="sb-text">Dashboard</span>
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="sb-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <span class="sb-ico">👥</span>
          <span class="sb-text">Usuarios</span>
        </a>

        <a href="{{ route('admin.banners.index') }}"
           class="sb-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
          <span class="sb-ico">🖼️</span>
          <span class="sb-text">Banners</span>
        </a>

        <a href="{{ route('admin.services.index') }}"
           class="sb-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
          <span class="sb-ico">🧩</span>
          <span class="sb-text">Servicios</span>
        </a>

        <a href="{{ route('admin.messages.index') }}"
           class="sb-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
          <span class="sb-ico">📩</span>
          <span class="sb-text">Bandeja de mensajes</span>

          @if(!empty($adminUnreadMessages))
            <span class="sb-badge">{{ $adminUnreadMessages }}</span>
          @endif
        </a>

      </nav>

      <div class="sb-footer">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn btn-ghost w-100">Salir</button>
        </form>
      </div>
    </aside>

    {{-- MAIN --}}
    <main class="admin-main">

      {{-- TOPBAR --}}
      <header class="admin-topbar">
        <div class="tb-left">
          <button class="tb-burger" type="button" aria-label="Menu" id="btnSidebar">☰</button>

          <div class="tb-search">
            <span class="tb-search__ico">🔎</span>
            <input type="text" placeholder="Search..." aria-label="Buscar">
          </div>
        </div>

        <div class="tb-right">
          <div class="tb-user">
            <div class="tb-user__name">{{ auth()->user()->name }}</div>
            <div class="tb-user__role">Admin</div>
          </div>
        </div>
      </header>

      {{-- ALERTAS GLOBALES (tus sesiones actuales ok/err) --}}
      @if (session('ok'))
        <div class="alert alert-success">
          <div class="alert-ico">✅</div>
          <div><strong>Éxito:</strong> {{ session('ok') }}</div>
        </div>
      @endif

      @if (session('err'))
        <div class="alert alert-danger">
          <div class="alert-ico">⚠️</div>
          <div><strong>Error:</strong> {{ session('err') }}</div>
        </div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <div class="alert-ico">⚠️</div>
          <div>
            <strong>Revisa los campos:</strong>
            <ul class="alert-list">
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      {{-- CONTENT --}}
      <section class="admin-content">
        @yield('content')
      </section>

    </main>

  </div>

  <script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
