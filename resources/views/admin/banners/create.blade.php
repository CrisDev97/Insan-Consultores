@extends('admin.layout')

@section('content')

<div class="page-head">
  <div>
    <h1 class="h1">Nuevo banner</h1>
    <p class="p-muted">Registra un banner del carrusel. Solo los activos tienen posición.</p>
  </div>

  <a class="btn btn-ghost" href="{{ route('admin.banners.index') }}">← Volver</a>
</div>

<div style="height:14px;"></div>

<form method="POST" action="{{ route('admin.banners.store') }}" enctype="multipart/form-data">
  @csrf

  <div class="form-grid" style="grid-template-columns: 1fr 1fr 220px 240px;">

    <div>
      <div class="label">Nombre</div>
      <input class="input" name="name" value="{{ old('name') }}" required placeholder="Ej: Banner principal">
    </div>

    <div>
      <div class="label">Imagen (.png/.jpg/.jpeg)</div>
      <input class="input" type="file" name="image" accept=".png,.jpg,.jpeg" required>
      <div class="p-muted" style="margin-top:6px;">Máx. 5MB</div>
    </div>

    <div>
      <div class="label">Estado</div>
      <select class="input" name="is_active" required>
        <option value="1" @selected(old('is_active', '1')==='1')>Activo</option>
        <option value="0" @selected(old('is_active')==='0')>Inactivo</option>
      </select>
    </div>

    <div>
      <div class="label">Posición (solo si está Activo)</div>
      <input class="input" type="number" name="position" min="1" max="{{ $maxPos + 1 }}"
             value="{{ old('position') }}"
             placeholder="Ej: 1..{{ $maxPos + 1 }}">
      <div class="p-muted" style="margin-top:6px;">Si lo dejas vacío y está Activo, se agrega al final.</div>
    </div>

  </div>

  <div style="height:14px;"></div>

  <div style="display:flex; gap:10px; justify-content:flex-end; flex-wrap:wrap;">
    <button class="btn btn-primary" type="submit">Guardar</button>
    <a class="btn btn-ghost" href="{{ route('admin.banners.index') }}">Cancelar</a>
  </div>

</form>

@endsection
