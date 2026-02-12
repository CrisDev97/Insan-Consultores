@extends('admin.layout')

@section('page_title', 'Asesores')
@section('page_subtitle', 'Gestiona asesores y su disponibilidad')

@section('content')
<div class="page-card">
  <div class="page-head">
    <div>
      <h1 class="page-title">Asesores</h1>
      <p class="page-subtitle">Crea, edita y configura disponibilidad por asesor.</p>
    </div>

    <a class="btn btn-primary" href="{{ route('admin.advisors.create') }}">+ Nuevo asesor</a>
  </div>

  <div class="divider"></div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th style="width:80px;">ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Teléfono</th>
          <th style="width:120px;">Activo</th>
          <th style="width:280px;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($advisors as $a)
          <tr>
            <td>{{ $a->id }}</td>
            <td>{{ $a->name }}</td>
            <td>{{ $a->email ?? '—' }}</td>
            <td>{{ $a->phone ?? '—' }}</td>
            <td>
              @if($a->is_active)
                <span class="badge badge-success">Sí</span>
              @else
                <span class="badge badge-muted">No</span>
              @endif
            </td>
            <td class="flex gap-2">
              <a class="btn btn-ghost" href="{{ route('admin.advisors.edit', $a) }}">Editar</a>
              <a class="btn btn-ghost" href="{{ route('admin.advisors.availability.edit', $a) }}">Disponibilidad</a>

              <form method="POST" action="{{ route('admin.advisors.destroy', $a) }}" onsubmit="return confirm('¿Eliminar asesor?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger" type="submit">Eliminar</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="p-muted">No hay asesores registrados.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-3">
    {{ $advisors->links() }}
  </div>
</div>
@endsection