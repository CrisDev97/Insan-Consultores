<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Portal Admin</title>
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

  <div class="admin-shell">

    {{-- SIDEBAR --}}
    <aside class="admin-sidebar">

      <a class="admin-brand" href="{{ route('admin.dashboard') }}">
        <div class="brand-left">
          <img src="{{ asset('img/brand/insan.PNG') }}" alt="Insan Consultores">
          <div>
            <div class="brand-title">Insan Consultores</div>
            <div class="p-muted" style="margin:2px 0 0;">Portal Admin</div>
          </div>
        </div>

        <span class="brand-pill">
          <span class="brand-dot"></span>
          {{ auth()->user()->role }}
        </span>
      </a>

      <nav class="sidebar-nav">

        <a href="{{ route('admin.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
          <span class="left">
            <span class="sidebar-ico">🏠</span>
            <span class="label">Dashboard</span>
          </span>
        </a>

        <a href="{{ route('admin.users.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
          <span class="left">
            <span class="sidebar-ico">👤</span>
            <span class="label">Usuarios</span>
          </span>
        </a>

        <a href="{{ route('admin.banners.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
          <span class="left">
            <span class="sidebar-ico">🖼️</span>
            <span class="label">Banners</span>
          </span>
        </a>

        <a href="{{ route('admin.services.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.services.*') ? 'active' : '' }}">
          <span class="left">
            <span class="sidebar-ico">🧩</span>
            <span class="label">Servicios</span>
          </span>
        </a>

        <a href="{{ route('admin.messages.index') }}"
           class="sidebar-link {{ request()->routeIs('admin.messages.*') ? 'active' : '' }}">
          <span class="left">
            <span class="sidebar-ico">📩</span>
            <span class="label">Mensajes</span>
          </span>

          @if(!empty($adminUnreadMessages))
            <span class="sidebar-badge">{{ $adminUnreadMessages }}</span>
          @endif
        </a>

      </nav>

      <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn-logout">
            <span>🚪</span> Salir
          </button>
        </form>
      </div>
    </aside>

    {{-- MAIN --}}
    <main class="admin-main">

      <div class="admin-topbar">
        <div>
          <div class="topbar-title">Panel de administración</div>
          <div class="topbar-sub">{{ auth()->user()->name }}</div>
        </div>

        <div class="topbar-right">
          <span class="badge blue">Rol: {{ auth()->user()->role }}</span>
        </div>
      </div>

      {{-- ALERTAS --}}
      @if(session('success'))
        <div class="alert alert-success">
          <div class="alert-ico">✅</div>
          <div><strong>Éxito:</strong> {{ session('success') }}</div>
        </div>
      @endif

      @if(session('error'))
        <div class="alert alert-danger">
          <div class="alert-ico">⚠️</div>
          <div><strong>Error:</strong> {{ session('error') }}</div>
        </div>
      @endif

      @if ($errors->any())
        <div class="alert alert-danger">
          <div class="alert-ico">⚠️</div>
          <div>
            <strong>Revisa los campos:</strong>
            <ul style="margin:8px 0 0; padding-left:18px;">
              @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      @endif

      <div class="card pad">
        @yield('content')
      </div>

    </main>

  </div>

  <script src="{{ asset('js/admin.js') }}" defer></script>
</body>
</html>
