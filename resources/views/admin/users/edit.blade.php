@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Editar usuario #{{ $user->id }}</h1>
      <p class="page-subtitle">Actualiza datos del usuario.</p>
    </div>
    <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">← Volver</a>
  </div>

  <form method="POST" action="{{ route('admin.users.update', $user) }}">
    @csrf
    @method('PUT')

    <div style="display:grid; gap:12px; grid-template-columns: 1fr 1fr;">
      <div class="field">
        <label>Nombre</label>
        <input class="input" name="name" value="{{ old('name', $user->name) }}" required>
      </div>

      <div class="field">
        <label>Email</label>
        <input class="input" type="email" name="email" value="{{ old('email', $user->email) }}" required>
      </div>

      <div class="field">
        <label>Rol</label>
        <select class="input" name="role" required>
          <option value="estudiante" @selected(old('role', $user->role)==='estudiante')>Estudiante</option>
          <option value="administrador" @selected(old('role', $user->role)==='administrador')>Administrador</option>
        </select>
      </div>

      <div class="field">
        <label>Nueva contraseña (opcional)</label>
        <input class="input" type="password" name="password">
        <div class="help">Deja vacío para no cambiar.</div>
      </div>

      <div class="field" style="grid-column: 1 / -1;">
        <label>Confirmar nueva contraseña</label>
        <input class="input" type="password" name="password_confirmation">
      </div>
    </div>

    <div class="divider"></div>

    <div class="form-actions">
      <button class="btn btn-primary" type="submit">Actualizar</button>
      <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Cancelar</a>
    </div>
  </form>
</div>

@endsection
