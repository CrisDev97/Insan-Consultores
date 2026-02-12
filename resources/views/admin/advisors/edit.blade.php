@extends('admin.layout')

@section('page_title', 'Asesores')
@section('page_subtitle', 'Editar asesor')

@section('content')
<div class="page-card">
  <div class="page-head">
    <div>
      <h1 class="page-title">Editar Asesor #{{ $advisor->id }}</h1>
      <p class="page-subtitle">Actualiza datos y servicios asignados.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.advisors.index') }}">← Volver</a>
  </div>

  <div class="divider"></div>

  @if($errors->any())
    <div class="alert alert-danger">
      <ul>
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <form method="POST" action="{{ route('admin.advisors.update', $advisor) }}">
    @csrf
    @method('PUT')

    <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
      <div class="field">
        <label>Nombre</label>
        <input class="input" name="name" value="{{ old('name', $advisor->name) }}" required>
      </div>

      <div class="field">
        <label>Email</label>
        <input class="input" type="email" name="email" value="{{ old('email', $advisor->email) }}">
      </div>

      <div class="field">
        <label>Teléfono</label>
        <input class="input" name="phone" value="{{ old('phone', $advisor->phone) }}">
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Bio / Descripción</label>
        <textarea class="input" name="bio" rows="3">{{ old('bio', $advisor->bio) }}</textarea>
      </div>

      <div class="field">
        <label>Activo</label>
        <label class="switch">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', $advisor->is_active) ? 'checked' : '' }}>
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="divider"></div>

    <div class="flex items-center justify-between">
      <div>
        <h3 class="mb-0">Servicios que atiende</h3>
        <p class="p-muted">Marca servicios y duración por sesión.</p>
      </div>

      <a class="btn btn-ghost" href="{{ route('admin.advisors.availability.edit', $advisor) }}">
        Configurar disponibilidad →
      </a>
    </div>

    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr>
            <th style="width:60px;"></th>
            <th>Servicio</th>
            <th style="width:220px;">Duración (min)</th>
            <th style="width:120px;">Activo</th>
          </tr>
        </thead>
        <tbody>
          @foreach($services as $i => $s)
            @php
              $isChecked = array_key_exists($s->id, $selected);
              $duration = $isChecked ? $selected[$s->id] : 60;
            @endphp

            <tr>
              <td>
                <input type="checkbox" class="svc-check" data-row="{{ $i }}" {{ $isChecked ? 'checked' : '' }}>
              </td>

              <td>
                <div class="font-semibold">{{ $s->title }}</div>
              </td>

              <td>
                <input type="hidden" name="services[{{ $i }}][id]" value="{{ $s->id }}" class="svc-id-{{ $i }}" {{ $isChecked ? '' : 'disabled' }}>
                <input type="number" min="15" max="240" step="15"
                       name="services[{{ $i }}][duration_minutes]"
                       value="{{ old("services.$i.duration_minutes", $duration) }}"
                       class="input svc-duration-{{ $i }}"
                       {{ $isChecked ? '' : 'disabled' }}>
              </td>

              <td>
                <input type="checkbox"
                       name="services[{{ $i }}][is_active]"
                       value="1"
                       class="svc-active-{{ $i }}"
                       {{ $isChecked ? 'checked' : '' }}
                       {{ $isChecked ? '' : 'disabled' }}>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex gap-2">
      <button class="btn btn-primary" type="submit">Guardar cambios</button>
      <a class="btn btn-ghost" href="{{ route('admin.advisors.index') }}">Cancelar</a>
    </div>
  </form>
</div>

<script>
  document.querySelectorAll('.svc-check').forEach(chk => {
    chk.addEventListener('change', (e) => {
      const row = e.target.dataset.row;

      const idInput = document.querySelector('.svc-id-' + row);
      const durInput = document.querySelector('.svc-duration-' + row);
      const activeInput = document.querySelector('.svc-active-' + row);

      const enabled = e.target.checked;

      idInput.disabled = !enabled;
      durInput.disabled = !enabled;
      activeInput.disabled = !enabled;

      if (enabled && !durInput.value) durInput.value = 60;
    });
  });
</script>
@endsection