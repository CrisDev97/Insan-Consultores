@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Editar servicio</h1>
      <p class="page-subtitle">Actualiza información, estado y orden del servicio.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.services.index') }}">← Volver</a>
  </div>

  <form method="POST" action="{{ route('admin.services.update', $service) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div style="display:grid; gap:12px; grid-template-columns: 1.4fr 0.6fr 0.6fr;">
      <div class="field">
        <label>Título del servicio</label>
        <input class="input" name="title" value="{{ old('title', $service->title ?? $service->name) }}" required>
      </div>

      <div class="field">
        <label>Sesiones</label>
        <input class="input" type="number" name="sessions" value="{{ old('sessions', $service->sessions) }}">
      </div>

      <div class="field">
        <label>Costo (S/)</label>
        <input class="input" name="price" value="{{ old('price', $service->price) }}">
      </div>

      <div class="field">
        <label>Posición</label>
        <input class="input" type="number" name="position" value="{{ old('position', $service->position) }}">
      </div>

      <div class="field">
        <label>Estado</label>
        <select class="input" name="is_active" required>
          <option value="1" @selected(old('is_active', $service->is_active ? '1':'0')==='1')>Activo</option>
          <option value="0" @selected(old('is_active', $service->is_active ? '1':'0')==='0')>Inactivo</option>
        </select>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Descripción</label>
        <textarea class="input" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Lo que incluye / implica (1 ítem por línea)</label>
        <textarea class="input" name="includes" rows="4">{{ old('includes', $service->includes) }}</textarea>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Objetivos (1 ítem por línea)</label>
        <textarea class="input" name="objectives" rows="4">{{ old('objectives', $service->objectives) }}</textarea>
      </div>

      <div class="field">
        <label>Imagen 1 (opcional)</label>
        <input class="input" type="file" name="image_1" accept=".png,.jpg,.jpeg">
        @if(!empty($service->image_1))
          <div class="help">Actual: <a target="_blank" href="{{ asset('storage/'.$service->image_1) }}">Ver</a></div>
        @endif
      </div>

      <div class="field">
        <label>Imagen 2 (opcional)</label>
        <input class="input" type="file" name="image_2" accept=".png,.jpg,.jpeg">
        @if(!empty($service->image_2))
          <div class="help">Actual: <a target="_blank" href="{{ asset('storage/'.$service->image_2) }}">Ver</a></div>
        @endif
      </div>
    </div>

    <div class="divider"></div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Actualizar</button>
      <a class="btn btn-ghost" href="{{ route('admin.services.index') }}">Cancelar</a>
    </div>

  </form>
</div>

@endsection
