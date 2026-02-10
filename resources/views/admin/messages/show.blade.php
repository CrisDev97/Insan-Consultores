@extends('admin.layout')

@section('content')

<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Mensaje</h1>
      <p class="page-subtitle">Detalle del mensaje recibido.</p>
    </div>

    <a class="btn btn-ghost" href="{{ route('admin.messages.index') }}">← Volver</a>
  </div>

  <div style="display:grid; gap:12px; grid-template-columns: 1fr 1fr;">
    <div class="field">
      <label>Nombre</label>
      <input class="input" value="{{ $message->name }}" disabled>
    </div>

    <div class="field">
      <label>Email</label>
      <input class="input" value="{{ $message->email }}" disabled>
    </div>

    <div class="field">
      <label>Teléfono</label>
      <input class="input" value="{{ $message->phone ?? '-' }}" disabled>
    </div>

    <div class="field">
      <label>Fecha</label>
      <input class="input" value="{{ $message->created_at->format('d/m/Y H:i') }}" disabled>
    </div>

    <div class="field" style="grid-column: 1 / -1;">
      <label>Mensaje</label>
      <textarea class="input" rows="6" disabled>{{ $message->message }}</textarea>
    </div>
  </div>

  <div class="divider"></div>

  <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end;">
    @if(Route::has('admin.messages.toggleRead'))
      <form method="POST" action="{{ route('admin.messages.toggleRead', $message) }}">
        @csrf @method('PATCH')
        <button class="btn btn-ghost" type="submit">
          {{ $message->read_at ? 'Marcar como No leído' : 'Marcar como Leído' }}
        </button>
      </form>
    @endif

    @if(Route::has('admin.messages.toggleContacted'))
      <form method="POST" action="{{ route('admin.messages.toggleContacted', $message) }}">
        @csrf @method('PATCH')
        <button class="btn btn-ghost" type="submit">
          {{ !empty($message->contacted_at) ? 'Marcar No contactado' : 'Marcar Contactado' }}
        </button>
      </form>
    @endif
  </div>
</div>

@endsection
