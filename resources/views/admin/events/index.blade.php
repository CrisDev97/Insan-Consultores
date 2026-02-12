@extends('admin.layout')

@section('page_title', 'Eventos')
@section('page_subtitle', 'Bloqueos y actividades de asesores')

@section('content')
<div class="page-card">
  <div class="page-head">
    <div>
      <h1 class="page-title">Eventos</h1>
      <p class="page-subtitle">Registra talleres, ponencias y bloqueos para que la agenda del estudiante muestre horarios ocupados.</p>
    </div>

    <a class="btn btn-primary" href="{{ route('admin.events.create') }}">+ Nuevo evento</a>
  </div>

  <div class="divider"></div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <form method="GET" action="{{ route('admin.events.index') }}" class="form-grid" style="grid-template-columns: 300px 180px 1fr;">
    <div class="field">
      <label>Filtrar por asesor</label>
      <select class="input" name="advisor_id">
        <option value="">Todos</option>
        @foreach($advisors as $a)
          <option value="{{ $a->id }}" {{ (string)$advisorId === (string)$a->id ? 'selected' : '' }}>
            {{ $a->name }}
          </option>
        @endforeach
      </select>
    </div>

    <div class="field" style="align-self:end;">
      <button class="btn btn-ghost" type="submit">Filtrar</button>
    </div>
  </form>

  <div class="divider"></div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th style="width:80px;">ID</th>
          <th>Asesor</th>
          <th>Título</th>
          <th style="width:140px;">Tipo</th>
          <th style="width:200px;">Inicio</th>
          <th style="width:200px;">Fin</th>
          <th style="width:140px;">Visibilidad</th>
          <th style="width:140px;">Estado</th>
          <th style="width:240px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($events as $e)
          <tr>
            <td>{{ $e->id }}</td>
            <td>{{ $e->advisor?->name ?? '—' }}</td>
            <td>
              <div class="font-semibold">{{ $e->title }}</div>
              @if($e->location)
                <div class="p-muted">{{ $e->location }}</div>
              @endif
            </td>
            <td>{{ strtoupper($e->type) }}</td>
            <td>{{ optional($e->start_at)->format('d/m/Y H:i') }}</td>
            <td>{{ optional($e->end_at)->format('d/m/Y H:i') }}</td>
            <td>
              @if($e->visibility === 'public')
                <span class="badge badge-success">Público</span>
              @else
                <span class="badge badge-muted">Privado</span>
              @endif
            </td>
            <td>
              @php $st = $e->status; @endphp
              @if($st === 'confirmed')
                <span class="badge badge-success">Confirmado</span>
              @elseif($st === 'tentative')
                <span class="badge badge-warning">Tentativo</span>
              @else
                <span class="badge badge-muted">Cancelado</span>
              @endif
            </td>
            <td class="flex gap-2">
              <a class="btn btn-ghost" href="{{ route('admin.events.edit', $e) }}">Editar</a>

              <form method="POST" action="{{ route('admin.events.destroy', $e) }}" onsubmit="return confirm('¿Eliminar evento?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="p-muted">No hay eventos registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $events->links() }}
  </div>
</div>
@endsection