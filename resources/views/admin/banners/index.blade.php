@extends('admin.layout')

@section('content')

<div class="page-head">
  <div>
    <h1 class="page-title">🖼️ Banners del Carrusel</h1>
    <p class="page-subtitle">Gestiona los banners que se muestran en el carrusel principal del sitio web.</p>
  </div>

  <a class="btn btn-primary" href="{{ route('admin.banners.create') }}">
    <span>➕</span> Nuevo banner
  </a>
</div>

{{-- Estadísticas --}}
<div class="stats-grid">
  <div class="stat-card stat-card--success">
    <div class="stat-card__icon">✅</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $active->count() }}</div>
      <div class="stat-card__label">Banners activos</div>
    </div>
  </div>

  <div class="stat-card stat-card--muted">
    <div class="stat-card__icon">💤</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $inactive->count() }}</div>
      <div class="stat-card__label">Banners inactivos</div>
    </div>
  </div>

  <div class="stat-card stat-card--info">
    <div class="stat-card__icon">📊</div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ $active->count() + $inactive->count() }}</div>
      <div class="stat-card__label">Total banners</div>
    </div>
  </div>
</div>

{{-- Sección de Banners Activos --}}
<div class="card" style="margin-bottom: 1.5rem;">
  <div class="card-header">
    <div>
      <h2 class="card-title">✅ Banners Activos</h2>
      <p class="card-subtitle">Se muestran en el carrusel según su posición.</p>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width: 100px;">Posición</th>
          <th style="width: 180px;">Vista previa</th>
          <th>Nombre</th>
          <th style="width: 140px;">Estado</th>
          <th style="width: 280px; text-align: center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($active as $b)
          <tr>
            <td>
              <div class="position-badge">
                <span class="position-number">#{{ $b->position }}</span>
                <span class="position-label">Posición</span>
              </div>
            </td>
            <td>
              <div class="banner-preview">
                <img src="{{ asset('storage/'.$b->image_path) }}" alt="{{ $b->name }}">
                <div class="banner-preview-overlay">
                  <a href="{{ asset('storage/'.$b->image_path) }}" target="_blank" class="preview-btn">
                    👁️ Ver
                  </a>
                </div>
              </div>
            </td>
            <td>
              <div style="font-weight: 700; color: var(--text); margin-bottom: 0.25rem;">
                {{ $b->name }}
              </div>
              <div style="font-size: 0.8125rem; color: var(--muted);">
                Creado {{ $b->created_at->diffForHumans() }}
              </div>
            </td>
            <td>
              <span class="badge blue">
                <span class="status-dot status-dot--active"></span>
                Activo
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.banners.edit', $b) }}" title="Editar banner">
                  ✏️ Editar
                </a>

                <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button 
                    class="btn btn-danger btn-sm" 
                    type="button"
                    onclick="__confirmDelete({
                      action: '{{ route('admin.banners.destroy', $b) }}',
                      name: '{{ addslashes($b->name) }}',
                      form: this.parentElement
                    })"
                    title="Eliminar banner">
                    🗑️ Eliminar
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty-state">
              <div class="empty-state-content">
                <div class="empty-state-icon">🖼️</div>
                <div class="empty-state-title">No hay banners activos</div>
                <div class="empty-state-text">
                  Crea tu primer banner para mostrarlo en el carrusel del sitio web.
                </div>
                <a href="{{ route('admin.banners.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                  ➕ Crear primer banner
                </a>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

{{-- Sección de Banners Inactivos --}}
<div class="card">
  <div class="card-header">
    <div>
      <h2 class="card-title">💤 Banners Inactivos</h2>
      <p class="card-subtitle">No se muestran en el carrusel. Actívalos para mostrarlos.</p>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th style="width: 100px;">ID</th>
          <th style="width: 180px;">Vista previa</th>
          <th>Nombre</th>
          <th style="width: 140px;">Estado</th>
          <th style="width: 280px; text-align: center;">Acciones</th>
        </tr>
      </thead>
      <tbody>
        @forelse($inactive as $b)
          <tr class="row-inactive">
            <td>
              <span class="badge gray">#{{ $b->id }}</span>
            </td>
            <td>
              <div class="banner-preview banner-preview--inactive">
                <img src="{{ asset('storage/'.$b->image_path) }}" alt="{{ $b->name }}">
                <div class="banner-preview-overlay">
                  <a href="{{ asset('storage/'.$b->image_path) }}" target="_blank" class="preview-btn">
                    👁️ Ver
                  </a>
                </div>
              </div>
            </td>
            <td>
              <div style="font-weight: 700; color: var(--muted); margin-bottom: 0.25rem;">
                {{ $b->name }}
              </div>
              <div style="font-size: 0.8125rem; color: var(--muted); opacity: 0.7;">
                Creado {{ $b->created_at->diffForHumans() }}
              </div>
            </td>
            <td>
              <span class="badge gray">
                <span class="status-dot status-dot--inactive"></span>
                Inactivo
              </span>
            </td>
            <td>
              <div class="action-buttons">
                <a class="btn btn-ghost btn-sm" href="{{ route('admin.banners.edit', $b) }}" title="Editar banner">
                  ✏️ Editar
                </a>

                <form method="POST" action="{{ route('admin.banners.destroy', $b) }}" style="display: inline;">
                  @csrf
                  @method('DELETE')
                  <button 
                    class="btn btn-danger btn-sm" 
                    type="button"
                    onclick="__confirmDelete({
                      action: '{{ route('admin.banners.destroy', $b) }}',
                      name: '{{ addslashes($b->name) }}',
                      form: this.parentElement
                    })"
                    title="Eliminar banner">
                    🗑️ Eliminar
                  </button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="empty-state">
              <div class="empty-state-content">
                <div class="empty-state-icon">✨</div>
                <div class="empty-state-title">No hay banners inactivos</div>
                <div class="empty-state-text">
                  Todos tus banners están activos y visibles en el sitio web.
                </div>
              </div>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
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
        ¿Estás seguro que deseas eliminar el banner <strong id="confirmName">---</strong>?
      </p>
      <div class="alert-box alert-box--warning">
        <span>⚠️</span>
        <span>Esta acción no se puede deshacer. El banner dejará de mostrarse en el sitio web.</span>
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

.stat-card--success .stat-card__icon {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.2));
}

.stat-card--muted .stat-card__icon {
  background: rgba(0, 0, 0, 0.05);
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

/* Card header */
.card-header {
  padding: 1.5rem;
  border-bottom: 1px solid var(--border);
  background: linear-gradient(180deg, #fafaf9, #ffffff);
}

.card-title {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 700;
  color: var(--text);
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.card-subtitle {
  margin: 0.375rem 0 0;
  font-size: 0.875rem;
  color: var(--muted);
  font-weight: 500;
}

/* Badge de posición */
.position-badge {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.25rem;
}

.position-number {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--primary);
  font-family: 'DM Serif Display', serif;
}

.position-label {
  font-size: 0.75rem;
  color: var(--muted);
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

/* Vista previa del banner */
.banner-preview {
  position: relative;
  width: 160px;
  height: 90px;
  border-radius: var(--radius-sm);
  overflow: hidden;
  border: 1px solid var(--border);
  box-shadow: var(--shadow-soft);
  transition: all var(--transition-base);
}

.banner-preview:hover {
  transform: scale(1.05);
  box-shadow: var(--shadow);
}

.banner-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

.banner-preview-overlay {
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  transition: opacity var(--transition-base);
}

.banner-preview:hover .banner-preview-overlay {
  opacity: 1;
}

.preview-btn {
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.9);
  color: var(--text);
  text-decoration: none;
  border-radius: var(--radius-sm);
  font-size: 0.875rem;
  font-weight: 600;
  box-shadow: var(--shadow-md);
  transition: all var(--transition-base);
}

.preview-btn:hover {
  background: #ffffff;
  transform: translateY(-2px);
}

.banner-preview--inactive {
  opacity: 0.6;
  filter: grayscale(30%);
}

/* Fila inactiva */
.row-inactive {
  opacity: 0.7;
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
  
  .banner-preview {
    width: 120px;
    height: 68px;
  }
  
  .action-buttons {
    flex-direction: column;
  }
}
</style>

<script>
// Función mejorada para confirmar eliminación
function __confirmDelete(options) {
  const modal = document.getElementById('confirmModal');
  const nameEl = document.getElementById('confirmName');
  const form = document.getElementById('confirmForm');
  
  nameEl.textContent = options.name || '---';
  form.action = options.action;
  
  // Si se pasó un form element, lo guardamos para ejecutarlo después
  if (options.form) {
    form.onsubmit = function(e) {
      e.preventDefault();
      options.form.submit();
    };
  }
  
  modal.classList.add('is-open');
  modal.setAttribute('aria-hidden', 'false');
}
</script>

@endsection