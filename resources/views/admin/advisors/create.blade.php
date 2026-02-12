@extends('admin.layout')

@section('page_title', 'Asesores')
@section('page_subtitle', 'Crear asesor')

@section('content')
<div class="page-card">
  <div class="page-head">
    <div>
      <h1 class="page-title">Nuevo Asesor</h1>
      <p class="page-subtitle">Registra un asesor y asígnale los servicios que atenderá.</p>
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

  <form method="POST" action="{{ route('admin.advisors.store') }}">
    @csrf

    <div class="form-grid" style="grid-template-columns: 1fr 1fr 1fr;">
      <div class="field">
        <label>Nombre</label>
        <input class="input" name="name" value="{{ old('name') }}" required>
      </div>

      <div class="field">
        <label>Email</label>
        <input class="input" type="email" name="email" value="{{ old('email') }}">
      </div>

      <div class="field">
        <label>Teléfono</label>
        <input class="input" name="phone" value="{{ old('phone') }}">
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Bio / Descripción</label>
        <textarea class="input" name="bio" rows="3">{{ old('bio') }}</textarea>
      </div>

      <div class="field">
        <label>Activo</label>
        <label class="switch">
          <input type="checkbox" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div class="divider"></div>

    <h3 class="mb-2">Servicios que atiende</h3>
    <p class="p-muted">Marca los servicios y define la duración por sesión (minutos).</p>

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
          @forelse($services as $i => $s)
            <tr>
              <td>
                <input type="checkbox" class="svc-check" data-row="{{ $i }}">
              </td>
              <td>
                <div class="font-semibold">{{ $s->title }}</div>
              </td>

              <td>
                <input type="hidden" name="services[{{ $i }}][id]" value="{{ $s->id }}" disabled class="svc-id-{{ $i }}">
                <input type="number" min="15" max="240" step="15"
                       name="services[{{ $i }}][duration_minutes]"
                       value="60"
                       class="input svc-duration-{{ $i }}"
                       disabled>
              </td>

              <td>
                <input type="checkbox"
                       name="services[{{ $i }}][is_active]"
                       value="1"
                       class="svc-active-{{ $i }}"
                       checked
                       disabled>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="p-muted">No hay servicios activos para asignar.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4 flex gap-2">
      <button class="btn btn-primary" type="submit">Guardar</button>
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
    });
  });
</script>
@endsection