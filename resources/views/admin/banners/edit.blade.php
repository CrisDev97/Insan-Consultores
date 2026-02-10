@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Editar banner #{{ $banner->id }}</h1>
      <p class="page-subtitle">Actualiza información del banner.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.banners.index') }}">← Volver</a>
  </div>

  <form method="POST" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div style="display:grid; gap:12px; grid-template-columns: 1.4fr 1fr;">
      <div class="field">
        <label>Nombre</label>
        <input class="input" name="name" value="{{ old('name', $banner->name) }}" required>
      </div>

      <div class="field">
        <label>Imagen (opcional)</label>
        <input class="input" type="file" name="image" accept=".png,.jpg,.jpeg">
        <div class="help">
          Actual: <a href="{{ asset('storage/'.$banner->image_path) }}" target="_blank">Ver</a>
        </div>
      </div>

      <div class="field">
        <label>Estado</label>
        <select class="input" name="is_active" required>
          <option value="1" @selected(old('is_active', $banner->is_active ? '1':'0')==='1')>Activo</option>
          <option value="0" @selected(old('is_active', $banner->is_active ? '1':'0')==='0')>Inactivo</option>
        </select>
      </div>

      <div class="field">
        <label>Posición (solo si Activo)</label>
        <input class="input" type="number" name="position" min="1" max="{{ $maxPos + 1 }}"
               value="{{ old('position', $banner->position) }}"
               placeholder="Ej: 1..{{ $maxPos + 1 }}">
        <div class="help">Si está inactivo, la posición se ignora y queda vacía.</div>
      </div>
    </div>

    <div class="divider"></div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Actualizar</button>
      <a class="btn btn-ghost" href="{{ route('admin.banners.index') }}">Cancelar</a>
    </div>
  </form>
</div>

@endsection
