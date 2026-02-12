@extends('admin.layout')

@section('page_title', 'Asesores')
@section('page_subtitle', 'Disponibilidad')

@section('content')
@php
  $days = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    0 => 'Domingo',
  ];

  $byDay = [];
  foreach ($rows as $r) {
    $byDay[$r->weekday] = $r;
  }
@endphp

<div class="page-card">
  <div class="page-head">
    <div>
      <h1 class="page-title">Disponibilidad: {{ $advisor->name }}</h1>
      <p class="page-subtitle">Define horarios por día. Luego el estudiante verá slots disponibles.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.advisors.edit', $advisor) }}">← Volver</a>
  </div>

  <div class="divider"></div>

  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.advisors.availability.update', $advisor) }}">
    @csrf

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th>Día</th>
            <th style="width:160px;">Activo</th>
            <th style="width:200px;">Inicio</th>
            <th style="width:200px;">Fin</th>
            <th style="width:200px;">Slot (min)</th>
          </tr>
        </thead>
        <tbody>
          @foreach($days as $weekday => $label)
            @php
              $row = $byDay[$weekday] ?? null;
              $active = old("items.$weekday.is_active", $row->is_active ?? true);
              $start = old("items.$weekday.start_time", $row->start_time ?? '09:00');
              $end = old("items.$weekday.end_time", $row->end_time ?? '18:00');
              $slot = old("items.$weekday.slot_minutes", $row->slot_minutes ?? 60);
            @endphp

            <tr>
              <td class="font-semibold">{{ $label }}</td>
              <td>
                <input type="hidden" name="items[{{ $weekday }}][weekday]" value="{{ $weekday }}">
                <label class="switch">
                  <input type="checkbox" name="items[{{ $weekday }}][is_active]" value="1" {{ $active ? 'checked' : '' }}>
                  <span class="slider"></span>
                </label>
              </td>
              <td>
                <input class="input" type="time" name="items[{{ $weekday }}][start_time]" value="{{ $start }}">
              </td>
              <td>
                <input class="input" type="time" name="items[{{ $weekday }}][end_time]" value="{{ $end }}">
              </td>
              <td>
                <input class="input" type="number" min="15" max="240" step="15" name="items[{{ $weekday }}][slot_minutes]" value="{{ $slot }}">
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex gap-2">
      <button class="btn btn-primary" type="submit">Guardar disponibilidad</button>
      <a class="btn btn-ghost" href="{{ route('admin.advisors.index') }}">Cancelar</a>
    </div>
  </form>

  <div class="divider"></div>

  <div class="p-muted">
    <strong>Tip:</strong> Si el asesor atiende 09:00–12:00 y slots de 60 min, el sistema generará 09:00, 10:00, 11:00 (según duración real de la sesión).
  </div>
</div>
@endsection