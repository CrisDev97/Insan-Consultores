@extends('admin.layout')

@section('content')

<div class="page-head">
  <div>
    <h1 class="page-title">🧩 Servicios</h1>
    <p class="page-subtitle">Administra los servicios mostrados en la web (orden, estado e información).</p>
  </div>

  <a class="btn btn-primary" href="{{ route('admin.services.create') }}">
    <span>➕</span> Nuevo servicio
  </a>
</div>

{{-- Estadísticas rápidas --}}
<div class="stats-grid">
  <div class="stat-card stat-card--primary">
    <div class="stat-card__icon">📊</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $services->total() }}</div>
      <div class="stat-card__label">Total servicios</div>
    </div>
  </div>

  <div class="stat-card stat-card--success">
    <div class="stat-card__icon">✅</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $services->where('is_active', true)->count() }}</div>
      <div class="stat-card__label">Activos</div>
    </div>
  </div>

  <div class="stat-card stat-card--muted">
    <div class="stat-card__icon">💤</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $services->where('is_active', false)->count() }}</div>
      <div class="stat-card__label">Inactivos</div>
    </div>
  </div>
</div>

{{-- Tabla mejorada --}}
<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width: 100px;">Orden</th>
          <th>Servicio</th>
          <th style="width: 140px;">Sesiones</th>
          <th style="width: 140px;">Precio</th>
          <th style="width: 140px;">Estado</th>
          <th style="width: 280px; text-align: center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($services as $s)
          <tr>
            <td>
              <span class="badge amber" style="font-size: 0.9375rem; font-weight: 800;">
                #{{ $s->position }}
              </span>
            </td>
            <td>
              <div style="display: flex; align-items: flex-start; gap: 1rem;">
                {{-- Miniatura del servicio --}}
                @if($s->image_1_path)
                  <div class="service-thumb">
                    <img src="{{ asset('storage/'.$s->image_1_path) }}" alt="{{ $s->title ?? 'Servicio' }}">
                  </div>
                @else
                  <div class="service-thumb service-thumb--empty">
                    🖼️
                  </div>
                @endif
                
                <div style="flex: 1; min-width: 0;">
                  <div style="font-weight: 700; color: var(--text); margin-bottom: 0.25rem; font-size: 1rem;">
                    {{ $s->title ?? $s->name ?? 'Servicio' }}
                  </div>
                  <div style="color: var(--muted); font-size: 0.875rem; line-height: 1.5;">
                    {{ \Illuminate\Support\Str::limit($s->description ?? 'Sin descripción', 100) }}
                  </div>
                </div>
              </div>
            </td>
            <td>
              @if($s->sessions_count)
                <span class="info-badge">
                  📅 {{ $s->sessions_count }}
                </span>
              @else
                <span style="color: var(--muted); font-size: 0.875rem;">—</span>
              @endif
            </td>
            <td>
              @if(isset($s->price))
                <span class="price-badge">
                  S/ {{ number_format($s->price, 2) }}
                </span>
              @else
                <span style="color: var(--muted); font-size: 0.875rem;">—</span>
              @endif
            </td>
            <td>
              @if(!empty($s->is_active))
                <span class="badge blue">
                  <span class="status-dot status-dot--active"></span>
                  Activo
                </span>
              @else
                <span class="badge gray">
                  <span class="status-dot status-dot--inactive"></span>
                  Inactivo
                </span>
              @endif
            </td>
            <td>
              <div class="action-buttons">
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.services.edit', $s) }}" title="Editar servicio">
                  ✏️ Editar
                </a>

                <button
                  type="button"
                  class="btn btn-danger btn-sm"
                  onclick="__confirmDelete({
                    action: '{{ route('admin.services.destroy', $s) }}',
                    name: '{{ addslashes($s->title ?? $s->name ?? 'Servicio') }}'
                  })"
                  title="Eliminar servicio"
                >
                  🗑️ Eliminar
                </button>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="empty-state">
              <div class="empty-state-content">
                <div class="empty-state-icon">🧩</div>
                <div class="empty-state-title">No hay servicios registrados</div>
                <div class="empty-state-text">Comienza agregando tu primer servicio para mostrar en el sitio web.</div>
                <a href="{{ route('admin.services.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                  ➕ Crear primer servicio
                </a>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if($services->hasPages())
    <div class="card-footer">
      {{ $services->links() }}
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
        ¿Estás seguro que deseas eliminar <strong id="confirmName">---</strong>?
      </p>
      <div class="alert-box alert-box--warning">
        <span>⚠️</span>
        <span>Esta acción no se puede deshacer. El servicio dejará de mostrarse en el sitio web.</span>
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

.stat-card--success .stat-card__icon {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.2));
}

.stat-card--muted .stat-card__icon {
  background: rgba(0, 0, 0, 0.05);
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

/* Miniaturas de servicios */
.service-thumb {
  width: 64px;
  height: 64px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  flex-shrink: 0;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-soft);
}

.service-thumb img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.service-thumb--empty {
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, #fafaf9, #f5f5f4);
  font-size: 1.5rem;
  opacity: 0.5;
}

/* Info badges */
.info-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  border-radius: var(--radius-full);
  background: rgba(59, 130, 246, 0.1);
  border: 1px solid rgba(59, 130, 246, 0.2);
  color: #1e40af;
  font-size: 0.875rem;
  font-weight: 600;
}

.price-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.5rem 0.875rem;
  border-radius: var(--radius-full);
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.15));
  border: 1px solid rgba(16, 185, 129, 0.25);
  color: #065f46;
  font-size: 0.875rem;
  font-weight: 700;
}

/* Status dot */
.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  display: inline-block;
}

.status-dot--active {
  background: #10b981;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
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
  
  .service-thumb {
    width: 48px;
    height: 48px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>

@endsection