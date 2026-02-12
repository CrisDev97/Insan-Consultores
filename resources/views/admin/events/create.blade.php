@extends('admin.layout')

@section('page_title', 'Eventos')
@section('page_subtitle', 'Crear evento')

@section('content')
<div class="page-card">
  <div class="page-head">
    <div>
      <h1 class="page-title">Nuevo Evento</h1>
      <p class="page-subtitle">Este evento bloqueará la disponibilidad del asesor en la agenda.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.events.index') }}">← Volver</a>
  </div>

  <div class="divider"></div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.events.store') }}">
    @csrf

    <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
      <div class="field">
        <label>Asesor</label>
        <select class="input" name="advisor_id" required>
          <option value="">Seleccionar...</option>
          @foreach($advisors as $a)
            <option value="{{ $a->id }}" {{ old('advisor_id') == $a->id ? 'selected' : '' }}>
              {{ $a->name }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="field">
        <label>Tipo</label>
        <select class="input" name="type" required>
          @foreach($types as $t)
            <option value="{{ $t }}" {{ old('type','taller') === $t ? 'selected' : '' }}>
              {{ strtoupper($t) }}
            </option>
          @endforeach
        </select>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Título</label>
        <input class="input" name="title" value="{{ old('title') }}" required>
      </div>

      <div class="field">
        <label>Inicio</label>
        <input class="input" type="datetime-local" name="start_at" value="{{ old('start_at') }}" required>
      </div>

      <div class="field">
        <label>Fin</label>
        <input class="input" type="datetime-local" name="end_at" value="{{ old('end_at') }}" required>
      </div>

      <div class="field">
        <label>Visibilidad para estudiantes</label>
        <select class="input" name="visibility" required>
          <option value="private" {{ old('visibility','private') === 'private' ? 'selected' : '' }}>Privado (mostrar “Ocupado”)</option>
          <option value="public" {{ old('visibility') === 'public' ? 'selected' : '' }}>Público (mostrar título)</option>
        </select>
      </div>

      <div class="field">
        <label>Estado</label>
        <select class="input" name="status" required>
          <option value="confirmed" {{ old('status','confirmed') === 'confirmed' ? 'selected' : '' }}>Confirmado</option>
          <option value="tentative" {{ old('status') === 'tentative' ? 'selected' : '' }}>Tentativo</option>
          <option value="cancelled" {{ old('status') === 'cancelled' ? 'selected' : '' }}>Cancelado</option>
        </select>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Ubicación (opcional)</label>
        <input class="input" name="location" value="{{ old('location') }}">
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Notas / Detalles (opcional)</label>
        <textarea class="input" name="notes" rows="3">{{ old('notes') }}</textarea>
      </div>

      <div class="field">
        <label>Activo</label>
        <label class="switch">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="mt-4 flex gap-2">
      <button class="btn btn-primary" type="submit">Guardar</button>
      <a class="btn btn-ghost" href="{{ route('admin.events.index') }}">Cancelar</a>
    </div>
  </form>
</div>
@endsection