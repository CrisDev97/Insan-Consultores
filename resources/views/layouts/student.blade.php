<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Plataforma | Insan Consultores')</title>

  @vite(['resources/css/app.css', 'resources/js/app.js'])
  @stack('head')
</head>

<body class="bg-slate-100 text-slate-900">
  <div class="min-h-screen flex">

    {{-- SIDEBAR --}}
    <aside class="w-72 bg-white border-r border-slate-200 flex flex-col">
      <div class="p-5 border-b border-slate-200">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold">
            IC
          </div>
          <div>
            <div class="font-semibold leading-tight">Insan Consultores</div>
            <div class="text-xs text-slate-500">Plataforma del Estudiante</div>
          </div>
        </div>
      </div>

      <nav class="p-3 space-y-1">
        @php
          $item = "flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition";
          $active = "bg-slate-900 text-white";
          $idle = "text-slate-700 hover:bg-slate-100";
        @endphp

        <a href="{{ route('student.agenda') }}"
           class="{{ $item }} {{ request()->routeIs('student.agenda') ? $active : $idle }}">
          <span>📅</span> <span class="font-medium">Agenda</span>
        </a>

        <a href="{{ route('student.dashboard') }}"
           class="{{ $item }} {{ request()->routeIs('student.dashboard') ? $active : $idle }}">
          <span>🏠</span> <span class="font-medium">Inicio</span>
        </a>

        <a href="{{ route('student.appointments') }}"
           class="{{ $item }} {{ request()->routeIs('student.appointments') ? $active : $idle }}">
          <span>✅</span> <span class="font-medium">Mis Citas</span>
        </a>

        <a href="{{ route('student.services') }}"
           class="{{ $item }} {{ request()->routeIs('student.services') ? $active : $idle }}">
          <span>🧩</span> <span class="font-medium">Servicios</span>
        </a>

        <a href="{{ route('student.profile') }}"
           class="{{ $item }} {{ request()->routeIs('student.profile') ? $active : $idle }}">
          <span>👤</span> <span class="font-medium">Perfil</span>
        </a>
      </nav>

      <div class="mt-auto p-4 border-t border-slate-200">
        <div class="flex items-center gap-3 mb-3">
          <div class="h-9 w-9 rounded-full bg-slate-200 flex items-center justify-center">
            👤
          </div>
          <div class="min-w-0">
            <div class="text-sm font-medium truncate">{{ auth()->user()->name ?? 'Estudiante' }}</div>
            <div class="text-xs text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</div>
          </div>
        </div>

        <form method="POST" action="{{ route('student.logout') }}">
          @csrf
          <button type="submit"
                  class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
            ⎋ Cerrar sesión
          </button>
        </form>
      </div>
    </aside>

    {{-- CONTENT --}}
    <div class="flex-1 flex flex-col">
      {{-- TOPBAR --}}
      <header class="bg-white border-b border-slate-200">
        <div class="px-6 py-4 flex items-center justify-between">
          <div>
            <h1 class="text-lg font-semibold">@yield('header', 'Agenda')</h1>
            <p class="text-sm text-slate-500">@yield('subheader', '')</p>
          </div>

          <div class="flex items-center gap-3">
            <span class="text-xs px-2 py-1 rounded-full bg-slate-100 border border-slate-200 text-slate-600">
              Estudiante
            </span>
          </div>
        </div>
      </header>

      {{-- FLASH --}}
      <div class="px-6 pt-6">
        @if(session('success'))
          <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800 text-sm">
            {{ session('success') }}
          </div>
        @endif

        @if(session('error'))
          <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm">
            {{ session('error') }}
          </div>
        @endif
      </div>

      {{-- PAGE --}}
      <main class="px-6 pb-10">
        @yield('content')
      </main>
    </div>

  </div>

  @stack('scripts')
</body>
</html>