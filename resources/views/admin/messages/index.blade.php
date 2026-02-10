@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Bandeja de mensajes</h1>
      <p class="page-subtitle">Mensajes recibidos desde el formulario de contacto.</p>

      <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
        <span class="badge blue">Total: {{ $stats['total'] ?? 0 }}</span>
        <span class="badge amber">No leídos: {{ $stats['unread'] ?? 0 }}</span>
        <span class="badge amber">No contactados: {{ $stats['not_contacted'] ?? 0 }}</span>
      </div>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
      @if(Route::has('admin.messages.markAllRead'))
        <form method="POST" action="{{ route('admin.messages.markAllRead') }}">
          @csrf @method('PATCH')
          <button class="btn btn-primary" type="submit">Marcar todo como leído</button>
        </form>
      @elseif(Route::has('admin.messages.readAll'))
        <a href="{{ route('admin.messages.readAll') }}" class="btn btn-primary">Marcar todo como leído</a>
      @endif
    </div>
  </div>

  <form method="GET" action="{{ route('admin.messages.index') }}" style="display:grid; gap:10px; grid-template-columns: 1fr 220px 260px auto auto; align-items:end; margin-top:12px;">
    <div class="field">
      <label>Buscar (nombre o email)</label>
      <input class="input" type="text" name="search" value="{{ request('search') }}" placeholder="Ej: Juan / gmail.com">
    </div>

    <div class="field">
      <label>Leído</label>
      <select class="input" name="read">
        <option value="">Todos</option>
        <option value="0" @selected(request('read')==='0')>No leídos</option>
        <option value="1" @selected(request('read')==='1')>Leídos</option>
      </select>
    </div>

    <div class="field">
      <label>Contactado</label>
      <select class="input" name="contacted">
        <option value="">Todos</option>
        <option value="0" @selected(request('contacted')==='0')>No contactados</option>
        <option value="1" @selected(request('contacted')==='1')>Contactados</option>
      </select>
    </div>

    <button class="btn btn-primary" type="submit">Aplicar</button>
    <a class="btn btn-ghost" href="{{ route('admin.messages.index') }}">Limpiar</a>
  </form>

  <div style="height:14px;"></div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width:190px;">Estado</th>
          <th>Cliente</th>
          <th>Email</th>
          <th>Mensaje</th>
          <th style="width:140px;">Fecha</th>
          <th style="width:320px;">Acciones</th>
        </tr>
      </thead>

      <tbody>
        @forelse($messages as $m)
          <tr>
            <td>
              <span class="badge {{ $m->read_at ? 'blue' : 'amber' }}">
                {{ $m->read_at ? 'Leído' : 'No leído' }}
              </span>
              <span class="badge {{ !empty($m->contacted_at) ? 'blue' : 'amber' }}">
                {{ !empty($m->contacted_at) ? 'Contactado' : 'No contactado' }}
              </span>
            </td>

            <td style="font-weight:900;">
              {{ $m->name }}
              @if($m->phone)
                <div style="color:var(--muted); font-size:12px; font-weight:700;">{{ $m->phone }}</div>
              @endif
            </td>

            <td>{{ $m->email }}</td>

            <td style="color:#334155;">
              {{ \Illuminate\Support\Str::limit($m->message, 80) }}
            </td>

            <td>{{ $m->created_at->format('d/m/Y') }}</td>

            <td>
              <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn btn-ghost" href="{{ route('admin.messages.show', $m) }}">Ver</a>

                @if(Route::has('admin.messages.toggleRead'))
                  <form method="POST" action="{{ route('admin.messages.toggleRead', $m) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-ghost" type="submit">
                      {{ $m->read_at ? 'No leído' : 'Leído' }}
                    </button>
                  </form>
                @endif

                @if(Route::has('admin.messages.toggleContacted'))
                  <form method="POST" action="{{ route('admin.messages.toggleContacted', $m) }}">
                    @csrf @method('PATCH')
                    <button class="btn btn-ghost" type="submit">
                      {{ !empty($m->contacted_at) ? 'No contactado' : 'Contactado' }}
                    </button>
                  </form>
                @endif

                @if(Route::has('admin.messages.destroy'))
                  <button
                    type="button"
                    class="btn btn-danger"
                    onclick="__confirmDelete({
                      action: '{{ route('admin.messages.destroy', $m) }}',
                      name: '{{ addslashes($m->name) }}'
                    })"
                  >
                    Eliminar
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" style="padding:18px; color:var(--muted);">
              No se encontraron mensajes con los filtros actuales.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div style="margin-top:12px;">
    {{ $messages->links() }}
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
      ¿Seguro que deseas eliminar este mensaje de <strong id="confirmName">---</strong>?
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
