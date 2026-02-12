@extends('admin.layout')

@section('content')

<div class="page-head">
  <div>
    <h1 class="page-title">📩 Mensajes</h1>
    <p class="page-subtitle">Mensajes recibidos desde el formulario de contacto del sitio web.</p>
  </div>

  @if(Route::has('admin.messages.markAllRead'))
    <form method="POST" action="{{ route('admin.messages.markAllRead') }}">
      @csrf @method('PATCH')
      <button class="btn btn-primary" type="submit">
        ✅ Marcar todo como leído
      </button>
    </form>
  @elseif(Route::has('admin.messages.readAll'))
    <a href="{{ route('admin.messages.readAll') }}" class="btn btn-primary">
      ✅ Marcar todo como leído
    </a>
  @endif
</div>

{{-- Estadísticas --}}
<div class="stats-grid">
  <div class="stat-card stat-card--primary">
    <div class="stat-card__icon">📊</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $stats['total'] ?? 0 }}</div>
      <div class="stat-card__label">Total mensajes</div>
    </div>
  </div>

  <div class="stat-card stat-card--warning">
    <div class="stat-card__icon">📬</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $stats['unread'] ?? 0 }}</div>
      <div class="stat-card__label">No leídos</div>
    </div>
  </div>

  <div class="stat-card stat-card--info">
    <div class="stat-card__icon">📞</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $stats['not_contacted'] ?? 0 }}</div>
      <div class="stat-card__label">Pendientes</div>
    </div>
  </div>
</div>

{{-- Filtros mejorados --}}
<div class="card pad" style="margin-bottom: 1.5rem;">
  <div style="margin-bottom: 1rem;">
    <h3 style="margin: 0; font-size: 1rem; font-weight: 700; color: var(--text);">🔍 Filtrar mensajes</h3>
    <p style="margin: 0.25rem 0 0; font-size: 0.875rem; color: var(--muted);">
      Encuentra mensajes por nombre, email o estado
    </p>
  </div>

  <form method="GET" action="{{ route('admin.messages.index') }}" class="filter-form">
    <div class="filter-grid">
      <div class="field">
        <label>Buscar</label>
        <input 
          class="input" 
          type="text" 
          name="search" 
          value="{{ request('search') }}" 
          placeholder="Nombre o email...">
      </div>

      <div class="field">
        <label>Estado de lectura</label>
        <select class="input" name="read">
          <option value="">Todos</option>
          <option value="0" @selected(request('read')==='0')>📬 No leídos</option>
          <option value="1" @selected(request('read')==='1')>✅ Leídos</option>
        </select>
      </div>

      <div class="field">
        <label>Estado de contacto</label>
        <select class="input" name="contacted">
          <option value="">Todos</option>
          <option value="0" @selected(request('contacted')==='0')>📞 No contactados</option>
          <option value="1" @selected(request('contacted')==='1')>✔️ Contactados</option>
        </select>
      </div>
    </div>

    <div class="filter-actions">
      <button class="btn btn-primary" type="submit">
        🔍 Aplicar filtros
      </button>
      <a class="btn btn-ghost" href="{{ route('admin.messages.index') }}">
        🔄 Limpiar
      </a>
    </div>
  </form>
</div>

{{-- Tabla mejorada --}}
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width: 180px;">Estado</th>
          <th>Remitente</th>
          <th>Mensaje</th>
          <th style="width: 140px;">Fecha</th>
          <th style="width: 320px; text-align: center;">Acciones</th>
        </tr>
      </thead>

      <tbody>
        @forelse($messages as $m)
          <tr class="{{ !$m->read_at ? 'row-unread' : '' }}">
            <td>
              <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @if($m->read_at)
                  <span class="badge blue">
                    <span class="status-dot status-dot--success"></span>
                    Leído
                  </span>
                @else
                  <span class="badge amber">
                    <span class="status-dot status-dot--warning"></span>
                    No leído
                  </span>
                @endif

                @if(!empty($m->contacted_at))
                  <span class="badge blue">
                    <span class="status-dot status-dot--success"></span>
                    Contactado
                  </span>
                @else
                  <span class="badge gray">
                    <span class="status-dot status-dot--inactive"></span>
                    Pendiente
                  </span>
                @endif
              </div>
            </td>

            <td>
              <div class="contact-info">
                <div class="contact-avatar">
                  {{ strtoupper(substr($m->name, 0, 1)) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                  <div class="contact-name">{{ $m->name }}</div>
                  <a href="mailto:{{ $m->email }}" class="contact-email">
                    {{ $m->email }}
                  </a>
                  @if($m->phone)
                    <a href="tel:{{ $m->phone }}" class="contact-phone">
                      📞 {{ $m->phone }}
                    </a>
                  @endif
                </div>
              </div>
            </td>

            <td>
              <div class="message-preview">
                {{ \Illuminate\Support\Str::limit($m->message, 100) }}
              </div>
            </td>

            <td>
              <div class="date-info">
                <div class="date-primary">{{ $m->created_at->format('d/m/Y') }}</div>
                <div class="date-secondary">{{ $m->created_at->format('H:i') }}</div>
                <div class="date-relative">{{ $m->created_at->diffForHumans() }}</div>
              </div>
            </td>

            <td>
              <div class="action-buttons">
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.messages.show', $m) }}" title="Ver mensaje completo">
                  👁️ Ver
                </a>

                @if(Route::has('admin.messages.toggleRead'))
                  <form method="POST" action="{{ route('admin.messages.toggleRead', $m) }}" style="display: inline;">
                    @csrf @method('PATCH')
                    <button class="btn btn-ghost btn-sm" type="submit" title="Cambiar estado de lectura">
                      {{ $m->read_at ? '📬' : '✅' }}
                    </button>
                  </form>
                @endif

                @if(Route::has('admin.messages.toggleContacted'))
                  <form method="POST" action="{{ route('admin.messages.toggleContacted', $m) }}" style="display: inline;">
                    @csrf @method('PATCH')
                    <button class="btn btn-ghost btn-sm" type="submit" title="Cambiar estado de contacto">
                      {{ !empty($m->contacted_at) ? '📞' : '✔️' }}
                    </button>
                  </form>
                @endif

                @if(Route::has('admin.messages.destroy'))
                  <button
                    type="button"
                    class="btn btn-danger btn-sm"
                    onclick="__confirmDelete({
                      action: '{{ route('admin.messages.destroy', $m) }}',
                      name: '{{ addslashes($m->name) }}'
                    })"
                    title="Eliminar mensaje"
                  >
                    🗑️
                  </button>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty-state">
              <div class="empty-state-content">
                <div class="empty-state-icon">📭</div>
                <div class="empty-state-title">No hay mensajes</div>
                <div class="empty-state-text">
                  {{ request()->hasAny(['search', 'read', 'contacted']) 
                    ? 'No se encontraron mensajes con los filtros aplicados.' 
                    : 'Aún no has recibido mensajes desde el formulario de contacto.' }}
                </div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($messages->hasPages())
    <div class="card-footer">
      {{ $messages->links() }}
    </div>
  @endif
</div>

{{-- Modal confirm --}}
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
        ¿Estás seguro que deseas eliminar el mensaje de <strong id="confirmName">---</strong>?
      </p>
      <div class="alert-box alert-box--warning">
        <span>⚠️</span>
        <span>Esta acción no se puede deshacer. Perderás el registro del mensaje permanentemente.</span>
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
/* Estadísticas */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  box-shadow: var(--shadow-soft);
  transition: all var(--transition-base);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow);
}

.stat-card__icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-sm);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.stat-card--primary .stat-card__icon {
  background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(234, 88, 12, 0.2));
}

.stat-card--warning .stat-card__icon {
  background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.2));
}

.stat-card--info .stat-card__icon {
  background: linear-gradient(135deg, rgba(59, 130, 246, 0.15), rgba(37, 99, 235, 0.2));
}

.stat-card__value {
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1;
  color: var(--text);
  font-family: 'DM Serif Display', serif;
}

.stat-card__label {
  font-size: 0.875rem;
  color: var(--muted);
  margin-top: 0.25rem;
  font-weight: 500;
}

/* Filtros */
.filter-form {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.filter-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.filter-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: flex-start;
  flex-wrap: wrap;
}

/* Información de contacto */
.contact-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.contact-avatar {
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

.contact-name {
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.25rem;
}

.contact-email {
  display: block;
  color: var(--primary);
  text-decoration: none;
  font-size: 0.875rem;
  margin-bottom: 0.125rem;
}

.contact-email:hover {
  text-decoration: underline;
}

.contact-phone {
  display: block;
  color: var(--muted);
  text-decoration: none;
  font-size: 0.8125rem;
  font-weight: 600;
}

.contact-phone:hover {
  color: var(--text);
}

/* Vista previa del mensaje */
.message-preview {
  color: #334155;
  line-height: 1.6;
  font-size: 0.9375rem;
}

/* Información de fecha */
.date-info {
  text-align: left;
}

.date-primary {
  font-weight: 700;
  color: var(--text);
  margin-bottom: 0.25rem;
}

.date-secondary {
  font-size: 0.875rem;
  color: var(--muted);
  margin-bottom: 0.25rem;
}

.date-relative {
  font-size: 0.8125rem;
  color: var(--muted);
  font-style: italic;
}

/* Fila no leída */
.row-unread {
  background: rgba(245, 158, 11, 0.04);
  border-left: 3px solid var(--warning);
}

/* Status dot */
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-dot--success {
  background: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.status-dot--warning {
  background: #f59e0b;
  box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2);
  animation: pulse 2s ease-in-out infinite;
}

.status-dot--inactive {
  background: #9ca3af;
}

/* Botones de acción */
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

/* Empty state */
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
  line-height: 1.6;
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

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@media (max-width: 768px) {
  .stats-grid {
    grid-template-columns: 1fr;
  }
  
  .filter-grid {
    grid-template-columns: 1fr;
  }
  
  .filter-actions {
    flex-direction: column;
  }
  
  .contact-avatar {
    width: 32px;
    height: 32px;
    font-size: 0.875rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>

@endsection