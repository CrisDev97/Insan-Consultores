@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Servicios</h1>
      <p class="page-subtitle">Administra los servicios mostrados en la web (orden, estado e información).</p>
    </div>

    <a class="btn btn-primary" href="{{ route('admin.services.create') }}">+ Nuevo servicio</a>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:100px;">Orden</th>
          <th>Servicio</th>
          <th style="width:140px;">Sesiones</th>
          <th style="width:140px;">Precio</th>
          <th style="width:140px;">Estado</th>
          <th style="width:260px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($services as $s)
          <tr>
            <td style="font-weight:900;">#{{ $s->position }}</td>
            <td>
              <div style="font-weight:950;">{{ $s->title ?? $s->name ?? 'Servicio' }}</div>
              <div style="color:var(--muted); font-size:12px; margin-top:4px;">
                {{ \Illuminate\Support\Str::limit($s->description ?? '', 80) }}
              </div>
            </td>
            <td>{{ $s->sessions ?? '-' }}</td>
            <td>{{ isset($s->price) ? 'S/ '.$s->price : '-' }}</td>
            <td>
              @if(!empty($s->is_active))
                <span class="badge blue">Activo</span>
              @else
                <span class="badge gray">Inactivo</span>
              @endif
            </td>
            <td>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="{{ route('admin.services.edit', $s) }}">Editar</a>

                <button
                  type="button"
                  class="btn btn-danger"
                  onclick="__confirmDelete({
                    action: '{{ route('admin.services.destroy', $s) }}',
                    name: '{{ addslashes($s->title ?? $s->name ?? 'Servicio') }}'
                  })"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="color:var(--muted);">No hay servicios registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:12px;">
    {{ $services->links() }}
  </div>
</div>

{{-- Modal confirm --}}
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
