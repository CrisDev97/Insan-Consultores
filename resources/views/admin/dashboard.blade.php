@extends('admin.layout')

@section('content')
  <h1>Admin</h1>
  <p>Panel del Administrador.</p>

  <div class="actions">
    <a class="btn" href="{{ route('admin.users.index') }}">Gestionar usuarios</a>
  </div>
@endsection
