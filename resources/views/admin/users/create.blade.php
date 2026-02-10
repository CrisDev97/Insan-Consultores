@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Registrar usuario</h1>
      <p class="page-subtitle">Crea un usuario para acceder al sistema.</p>
    </div>
    <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">← Volver</a>
  </div>

  <form method="POST" action="{{ route('admin.users.store') }}">
    @csrf

    <div style="display:grid; gap:12px; grid-template-columns: 1fr 1fr;">
      <div class="field">
        <label>Nombre</label>
        <input class="input" name="name" value="{{ old('name') }}" required>
      </div>

      <div class="field">
        <label>Email</label>
        <input class="input" type="email" name="email" value="{{ old('email') }}" required>
      </div>

      <div class="field">
        <label>Rol</label>
        <select class="input" name="role" required>
          <option value="estudiante" @selected(old('role')==='estudiante')>Estudiante</option>
          <option value="administrador" @selected(old('role')==='administrador')>Administrador</option>
        </select>
      </div>

      <div class="field">
        <label>Contraseña</label>
        <input class="input" type="password" name="password" required>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Confirmar contraseña</label>
        <input class="input" type="password" name="password_confirmation" required>
      </div>
    </div>

    <div class="divider"></div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Guardar</button>
      <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Cancelar</a>
    </div>
  </form>
</div>

@endsection
