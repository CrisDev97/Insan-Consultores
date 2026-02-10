@extends('admin.layout')

@section('page_title', 'Banners')
@section('page_subtitle', 'Gestiona banners del carrusel. Solo los activos tienen posición.')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="h1">Banners</h1>
      <p class="p-muted">Gestiona banners del carrusel. Solo los activos tienen posición.</p>

      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:12px;">
        <span class="badge blue">Activos: {{ $active->count() }}</span>
        <span class="badge yellow">Inactivos: {{ $inactive->count() }}</span>
      </div>
    </div>

    <a class="btn btn-primary" href="{{ route('admin.banners.create') }}">➕ Nuevo banner</a>
  </div>
</div>

<div style="height:14px;"></div>

<div class="card pad">
  <h2 style="margin:0; font-size:16px; font-weight:950;">Activos</h2>
  <p class="p-muted" style="margin-top:6px;">Se muestran en el carrusel según su posición.</p>

  <div class="table-wrap" style="margin-top:12px;">
    <table>
      <thead>
        <tr>
          <th style="width:120px;">Posición</th>
          <th>Nombre</th>
          <th style="width:160px;">Imagen</th>
          <th style="width:120px;">Estado</th>
          <th style="width:260px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($active as $b)
          <tr>
            <td><span class="badge yellow">#{{ $b->position }}</span></td>
            <td style="font-weight:900;">{{ $b->name }}</td>
            <td>
              <a class="btn btn-ghost" target="_blank" href="{{ asset('storage/'.$b->image_path) }}">Ver</a>
            </td>
            <td><span class="badge blue">Activo</span></td>
            <td style="display:flex; gap:8px; flex-wrap:wrap;">
              <a class="btn btn-ghost" href="{{ route('admin.banners.edit', $b) }}">Editar</a>

              <form method="POST" action="{{ route('admin.banners.destroy', $b) }}"
                    onsubmit="return confirm('¿Eliminar este banner?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding:18px; color:var(--muted);">
              No hay banners activos.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div style="height:14px;"></div>

<div class="card pad">
  <h2 style="margin:0; font-size:16px; font-weight:950;">Inactivos</h2>
  <p class="p-muted" style="margin-top:6px;">No se muestran en el carrusel (no tienen posición efectiva).</p>

  <div class="table-wrap" style="margin-top:12px;">
    <table>
      <thead>
        <tr>
          <th style="width:90px;">ID</th>
          <th>Nombre</th>
          <th style="width:160px;">Imagen</th>
          <th style="width:120px;">Estado</th>
          <th style="width:260px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($inactive as $b)
          <tr>
            <td><span class="badge gray">#{{ $b->id }}</span></td>
            <td style="font-weight:900;">{{ $b->name }}</td>
            <td>
              <a class="btn btn-ghost" target="_blank" href="{{ asset('storage/'.$b->image_path) }}">Ver</a>
            </td>
            <td><span class="badge gray">Inactivo</span></td>
            <td style="display:flex; gap:8px; flex-wrap:wrap;">
              <a class="btn btn-ghost" href="{{ route('admin.banners.edit', $b) }}">Editar</a>

              <form method="POST" action="{{ route('admin.banners.destroy', $b) }}"
                    onsubmit="return confirm('¿Eliminar este banner?');">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding:18px; color:var(--muted);">
              No hay banners inactivos.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
