@extends('admin.layout')

@section('content')

<div class="page-head">
  <div>
    <h1 class="page-title">👥 Usuarios</h1>
    <p class="page-subtitle">Administra usuarios del sistema y sus permisos.</p>
  </div>

  <a class="btn btn-primary" href="{{ route('admin.users.create') }}">
    <span>➕</span> Nuevo usuario
  </a>
</div>

{{-- Buscador mejorado --}}
<div class="card pad" style="margin-bottom: 1.5rem;">
  <form method="GET" action="{{ route('admin.users.index') }}" class="search-form">
    <div class="search-group">
      <label class="search-label">🔍 Buscar usuario</label>
      <input 
        class="input" 
        type="text" 
        name="search" 
        value="{{ request('search') }}" 
        placeholder="Buscar por nombre o email..."
        style="flex: 1; min-width: 300px;">
    </div>
    
    <div class="search-actions">
      <button class="btn btn-primary" type="submit">Buscar</button>
      <a class="btn btn-ghost" href="{{ route('admin.users.index') }}">Limpiar</a>
    </div>
  </form>
</div>

{{-- Tabla mejorada --}}
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width: 80px;">ID</th>
          <th>Usuario</th>
          <th>Email</th>
          <th style="width: 140px;">Rol</th>
          <th style="width: 280px; text-align: center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($users as $u)
          <tr>
            <td>
              <span class="badge gray">#{{ $u->id }}</span>
            </td>
            <td>
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div class="user-avatar">
                  {{ strtoupper(substr($u->name, 0, 1)) }}
                </div>
                <div>
                  <div style="font-weight: 700; color: var(--text);">{{ $u->name }}</div>
                  <div style="font-size: 0.8125rem; color: var(--muted); margin-top: 0.125rem;">
                    Registrado {{ $u->created_at->diffForHumans() }}
                  </div>
                </div>
              </div>
            </td>
            <td>
              <a href="mailto:{{ $u->email }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                {{ $u->email }}
              </a>
            </td>
            <td>
              @if($u->role === 'admin')
                <span class="badge blue">👑 Admin</span>
              @else
                <span class="badge gray">👤 {{ ucfirst($u->role) }}</span>
              @endif
            </td>
            <td>
              <div class="action-buttons">
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.users.edit', $u) }}" title="Editar usuario">
                  ✏️ Editar
                </a>

                <button
                  type="button"
                  class="btn btn-danger btn-sm"
                  onclick="__confirmDelete({
                    action: '{{ route('admin.users.destroy', $u) }}',
                    name: '{{ addslashes($u->name) }}'
                  })"
                  title="Eliminar usuario"
                >
                  🗑️ Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty-state">
              <div class="empty-state-content">
                <div class="empty-state-icon">👤</div>
                <div class="empty-state-title">No hay usuarios registrados</div>
                <div class="empty-state-text">Comienza agregando tu primer usuario al sistema.</div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($users->hasPages())
    <div class="card-footer">
      {{ $users->links() }}
    </div>
  @endif
</div>

{{-- Modal confirm mejorado --}}
<div id="confirmModal" class="modal" aria-hidden="true">
  <div class="modal__backdrop" data-close="1"></div>

  <div class="modal__panel" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
    <div class="modal__head">
      <div class="modal__title" id="confirmTitle">
        <span style="font-size: 1.5rem;">⚠️</span>
        Confirmar eliminación
      </div>
      <button class="modal__close" type="button" data-close="1">✕</button>
    </div>

    <div class="modal__body">
      <p style="margin: 0 0 1rem;">
        ¿Estás seguro que deseas eliminar a <strong id="confirmName">---</strong>?
      </p>
      <div class="alert-box alert-box--warning">
        <span>⚠️</span>
        <span>Esta acción no se puede deshacer. Todos los datos asociados se perderán permanentemente.</span>
      </div>
    </div>

    <div class="modal__actions">
      <button class="btn btn-ghost" type="button" data-close="1">Cancelar</button>
      <form id="confirmForm" method="POST" action="#">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">
          🗑️ Sí, eliminar
        </button>
      </form>
    </div>
  </div>
</div>

<style>
/* Estilos adicionales para mejorar el diseño */
.search-form {
  display: flex;
  align-items: flex-end;
  gap: 1rem;
  flex-wrap: wrap;
}

.search-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  flex: 1;
}

.search-label {
  font-size: 0.875rem;
  font-weight: 600;
  color: var(--text);
}

.search-actions {
  display: flex;
  gap: 0.5rem;
}

.user-avatar {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--primary-2));
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1rem;
  flex-shrink: 0;
  box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
  flex-wrap: wrap;
}

.btn-sm {
  padding: 0.625rem 1rem;
  font-size: 0.875rem;
}

.empty-state {
  padding: 4rem 2rem !important;
  text-align: center;
}

.empty-state-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1rem;
}

.empty-state-icon {
  font-size: 4rem;
  opacity: 0.3;
}

.empty-state-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text);
}

.empty-state-text {
  color: var(--muted);
  max-width: 400px;
}

.card-footer {
  padding: 1rem 1.5rem;
  border-top: 1px solid var(--border);
  background: #fafaf9;
}

.alert-box {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.875rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  line-height: 1.5;
}

.alert-box--warning {
  background: rgba(245, 158, 11, 0.1);
  border: 1px solid rgba(245, 158, 11, 0.3);
  color: #92400e;
}

@media (max-width: 768px) {
  .search-form {
    flex-direction: column;
    align-items: stretch;
  }
  
  .search-actions {
    flex-direction: column;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>

@endsection