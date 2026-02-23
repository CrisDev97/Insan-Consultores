@extends('admin.layout')

@section('content')
<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Calendario de Reservas</h1>
      <p class="page-subtitle">Visualiza todas las sesiones agendadas, estado de sesión y pagos (incluye parciales).</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow p-4">
    <div class="flex flex-wrap gap-2 text-xs mb-3">
      <span class="px-3 py-1 rounded-full" style="background:#FEF9C3;border:1px solid #EAB308;">Pendiente</span>
      <span class="px-3 py-1 rounded-full" style="background:#DCFCE7;border:1px solid #22C55E;">Realizado</span>
    </div>

    <div id="adminCalendar"></div>
  </div>
</div>

{{-- Modal --}}
<div id="apptModal" style="display:none" class="fixed inset-0 bg-black/40 z-50">
  <div class="bg-white rounded-2xl shadow max-w-xl w-[92%] mx-auto mt-16 p-5">
    <div class="flex justify-between items-center mb-3">
      <div class="font-semibold text-lg">Detalle de reserva</div>
      <button id="btnCloseModal" class="btn btn-ghost" type="button">Cerrar</button>
    </div>

    <div class="text-sm grid gap-1 mb-4">
      <div><b>Servicio:</b> <span id="m_service">—</span></div>
      <div><b>Sesión:</b> <span id="m_session">—</span></div>
      <div><b>Estudiante:</b> <span id="m_student">—</span></div>
      <div><b>Asesor:</b> <span id="m_advisor">—</span></div>
      <div><b>Horario:</b> <span id="m_time">—</span></div>
      <div><b>Estado sesión:</b> <span id="m_status">—</span></div>
      <div><b>Pago:</b> <span id="m_pay">—</span></div>
      <div><b>Total:</b> S/ <span id="m_total">0.00</span> — <b>Pagado:</b> S/ <span id="m_paid">0.00</span> — <b>Saldo:</b> S/ <span id="m_balance">0.00</span></div>
    </div>

    <div class="divider"></div>

    <form id="formStatus" class="grid gap-2 mb-4">
      @csrf
      @method('PATCH')

      <div class="font-semibold">Actualizar estado de sesión</div>
      <select id="f_status" class="input">
        <option value="reserved">Pendiente</option>
        <option value="completed">Realizado</option>
        <option value="cancelled">Cancelado</option>
      </select>

      <button class="btn btn-primary" type="submit">Guardar estado</button>
    </form>

    <div class="divider"></div>

    <form id="formPayment" class="grid gap-2">
      @csrf
      @method('PATCH')

      <div class="font-semibold">Actualizar pagos (parcial / total)</div>

      <label class="text-xs">Total del servicio (S/)</label>
      <input id="f_total" class="input" type="number" step="0.01" min="0">

      <label class="text-xs">Agregar pago (S/)</label>
      <input id="f_add_payment" class="input" type="number" step="0.01" min="0" placeholder="Ej: 50.00">

      <label class="text-xs">Notas</label>
      <textarea id="f_notes" class="input" rows="3" placeholder="Ej: Pagó 50% antes de sesión 1"></textarea>

      <button class="btn btn-primary" type="submit">Guardar pago</button>
    </form>
  </div>
</div>

@push('scripts')
  @vite(['resources/js/admin-appointments-calendar.js'])
@endpush
@endsection