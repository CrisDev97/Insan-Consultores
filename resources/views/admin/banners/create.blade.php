@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Nuevo banner</h1>
      <p class="page-subtitle">Registra un banner del carrusel. Solo los activos tienen posición.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.banners.index') }}">← Volver</a>
  </div>

  <form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
    @csrf

    <div style="display:grid; gap:12px; grid-template-columns: 1.4fr 1fr;">
      <div class="field">
        <label>Nombre</label>
        <input class="input" name="name" value="{{ old('name') }}" required placeholder="Ej: Banner principal">
      </div>

      <div class="field">
        <label>Imagen (.png/.jpg/.jpeg)</label>
        <input class="input" type="file" name="image" accept=".png,.jpg,.jpeg" required>
        <div class="help">Máx 5MB</div>
      </div>

      <div class="field">
        <label>Estado</label>
        <select class="input" name="is_active" required>
          <option value="1" @selected(old('is_active', '1')==='1')>Activo</option>
          <option value="0" @selected(old('is_active')==='0')>Inactivo</option>
        </select>
      </div>

      <div class="field">
        <label>Posición (solo si está Activo)</label>
        <input class="input" type="number" name="position" min="1" max="{{ $maxPos + 1 }}"
               value="{{ old('position') }}"
               placeholder="Ej: 1..{{ $maxPos + 1 }}">
        <div class="help">Si lo dejas vacío y está Activo, se agrega al final.</div>
      </div>
    </div>

    <div class="divider"></div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Guardar</button>
      <a class="btn btn-ghost" href="{{ route('admin.banners.index') }}">Cancelar</a>
    </div>
  </form>
</div>

@endsection
