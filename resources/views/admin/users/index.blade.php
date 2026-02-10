@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Usuarios</h1>
      <p class="page-subtitle">Administra usuarios del sistema.</p>
    </div>

    <a class="btn btn-primary" href="{{ route('admin.users.create') }}">+ Registrar usuario</a>
  </div>

  <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:12px;">
    <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o email" style="max-width:520px;">
    <button class="btn btn-ghost" type="submit">Buscar</button>
    <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Limpiar</a>
  </form>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:80px;">ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th style="width:160px;">Rol</th>
          <th style="width:260px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
          <tr>
            <td>{{ $u->id }}</td>
            <td style="font-weight:900;">{{ $u->name }}</td>
            <td>{{ $u->email }}</td>
            <td><span class="badge gray">{{ $u->role }}</span></td>
            <td>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="{{ route('admin.users.edit', $u) }}">Editar</a>

                <button
                  type="button"
                  class="btn btn-danger"
                  onclick="__confirmDelete({
                    action: '{{ route('admin.users.destroy', $u) }}',
                    name: '{{ addslashes($u->name) }}'
                  })"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="color:var(--muted);">No hay usuarios.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:12px;">
    {{ $users->links() }}
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
