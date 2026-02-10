@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Banners</h1>
      <p class="page-subtitle">Gestiona banners del carrusel. Solo los activos tienen posición.</p>
    </div>

    <a class="btn btn-primary" href="{{ route('admin.banners.create') }}">+ Nuevo banner</a>
  </div>

  <h3 style="margin: 6px 0 10px; font-weight:900;">Activos</h3>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Posición</th>
          <th>Nombre</th>
          <th>Imagen</th>
          <th>Estado</th>
          <th style="width:260px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($active as $b)
          <tr>
            <td>{{ $b->position }}</td>
            <td style="font-weight:900;">{{ $b->name }}</td>
            <td>
              <a class="btn btn-ghost" href="{{ asset('storage/'.$b->image_path) }}" target="_blank">Ver</a>
            </td>
            <td><span class="badge blue">Activo</span></td>
            <td>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="{{ route('admin.banners.edit', $b) }}">Editar</a>

                <button
                  type="button"
                  class="btn btn-danger"
                  onclick="__confirmDelete({
                    action: '{{ route('admin.banners.destroy', $b) }}',
                    name: '{{ addslashes($b->name) }}'
                  })"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" style="color:var(--muted);">No hay banners activos.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="height:14px;"></div>

  <h3 style="margin: 6px 0 10px; font-weight:900;">Inactivos</h3>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Imagen</th>
          <th>Estado</th>
          <th style="width:260px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($inactive as $b)
          <tr>
            <td>{{ $b->id }}</td>
            <td style="font-weight:900;">{{ $b->name }}</td>
            <td>
              <a class="btn btn-ghost" href="{{ asset('storage/'.$b->image_path) }}" target="_blank">Ver</a>
            </td>
            <td><span class="badge gray">Inactivo</span></td>
            <td>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="{{ route('admin.banners.edit', $b) }}">Editar</a>

                <button
                  type="button"
                  class="btn btn-danger"
                  onclick="__confirmDelete({
                    action: '{{ route('admin.banners.destroy', $b) }}',
                    name: '{{ addslashes($b->name) }}'
                  })"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="5" style="color:var(--muted);">No hay banners inactivos.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

</div>

{{-- Modal confirm (reutilizable) --}}
<div id="confirmModal" class="modal" aria-hidden="true">
  <div class="modal__backdrop" data-close="1"></div>

  <div class="modal__panel" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="modal__head">
      <div class="modal__title" id="confirmTitle">🗑️ Confirmar eliminación</div>
      <button class="modal__close" type="button" data-close="1">✕</button>
    </div>

    <div class="modal__body">
      ¿Seguro que deseas eliminar <strong id="confirmName">---</strong>?
      <div style="color:var(--muted); font-size:12px; margin-top:8px;">
        Esta acción no se puede deshacer.
      </div>
    </div>

    <div class="modal__actions">
      <button class="btn btn-ghost" type="button" data-close="1">Cancelar</button>
      <form id="confirmForm" method="POST" action="#">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">Eliminar</button>
      </form>
    </div>
  </div>
</div>

@endsection
