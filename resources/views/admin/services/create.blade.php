@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Nuevo servicio</h1>
      <p class="page-subtitle">Registra un servicio para mostrarlo en la página principal.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.services.index') }}">← Volver</a>
  </div>

  <form method="POST" action="{{ route('admin.services.store') }}" enctype="multipart/form-data">
    @csrf

    <div style="display:grid; gap:12px; grid-template-columns: 1.4fr 0.6fr 0.6fr;">
      <div class="field">
        <label>Título del servicio</label>
        <input class="input" name="title" value="{{ old('title') }}" required placeholder="Ej: Diagnóstico Vocacional">
      </div>

      <div class="field">
        <label>Sesiones</label>
        <input class="input" type="number" name="sessions_count" value="{{ old('sessions_count') }}" placeholder="Ej: 4">
      </div>

      <div class="field">
        <label>Costo (S/)</label>
        <input class="input" name="price" value="{{ old('price') }}" placeholder="Ej: 250.00">
      </div>

      <div class="field">
        <label>Posición</label>
        <input class="input" type="number" name="position" value="{{ old('position', 1) }}">
        <div class="help">Orden en la web.</div>
      </div>

      <div class="field">
        <label>Estado</label>
        <select class="input" name="is_active" required>
          <option value="1" @selected(old('is_active','1')==='1')>Activo</option>
          <option value="0" @selected(old('is_active')==='0')>Inactivo</option>
        </select>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Descripción</label>
        <textarea class="input" name="description" rows="4" placeholder="Describe brevemente el servicio...">{{ old('description') }}</textarea>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Lo que incluye / implica (1 ítem por línea)</label>
        <textarea class="input" name="includes_text" rows="4" placeholder="- Evaluación inicial&#10;- Entrevista">{{ old('includes_text') }}</textarea>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Objetivos (1 ítem por línea)</label>
        <textarea class="input" name="objectives_text" rows="4" placeholder="- Identificar fortalezas&#10;- Definir ruta vocacional">{{ old('objectives_text') }}</textarea>
      </div>

      <div class="field">
        <label>Imagen 1 (referencial)</label>
        <input class="input" type="file" name="image_1" accept=".png,.jpg,.jpeg">
      </div>

      <div class="field">
        <label>Imagen 2 (referencial)</label>
        <input class="input" type="file" name="image_2" accept=".png,.jpg,.jpeg">
      </div>
    </div>

    <div class="divider"></div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Guardar</button>
      <a class="btn btn-ghost" href="{{ route('admin.services.index') }}">Cancelar</a>
    </div>
    <div class="mt-6 bg-white border border-slate-200 rounded-xl p-5">
      <div class="font-semibold mb-3">Duración por sesión</div>
      <p class="text-sm text-slate-500 mb-4">
        Define la duración (en minutos) de cada sesión del servicio. Debe haber una duración para cada sesión (1..N).
      </p>

      @php
        $existing = isset($service)
          ? $service->sessions->keyBy('session_number')
          : collect();
      @endphp

      <div id="sessionsBox" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- se rellena con JS --}}
      </div>

      <p class="text-xs text-slate-500 mt-3">
        Recomendado: 45 / 60 / 90 / 120 min.
      </p>
    </div>

    {{-- manda JSON a JS (solo si existe $service) --}}
    <script>
      window.__serviceSessions = @json(
        isset($service)
          ? $service->sessions->map(fn($x)=>[
              'session_number'=>$x->session_number,
              'duration_minutes'=>$x->duration_minutes
            ])->values()
          : []
      );
    </script>

  </form>
</div>

@endsection
