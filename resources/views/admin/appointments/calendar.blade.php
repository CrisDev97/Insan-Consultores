@extends('admin.layout')

@section('content')
<div class="card pad">
  <div class="page-head">
    <div>
      <h1 class="page-title">Calendario de Reservas</h1>
      <p class="page-subtitle">Visualiza sesiones, eventos de asesores, estados y pagos (incluye parciales).</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl shadow p-4">
    <div class="flex flex-wrap gap-2 text-xs mb-3">
      <span class="px-3 py-1 rounded-full" style="background:#FEF9C3;border:1px solid #EAB308;">Sesión Pendiente</span>
      <span class="px-3 py-1 rounded-full" style="background:#DCFCE7;border:1px solid #22C55E;">Sesión Realizada</span>
      <span class="px-3 py-1 rounded-full" style="background:#E5E7EB;border:1px solid #9CA3AF;">Sesión Cancelada</span>
      <span class="px-3 py-1 rounded-full" style="background:#DBEAFE;border:1px solid #3B82F6;">Evento (Asesor)</span>
    </div>

    <div id="adminCalendar"></div>
  </div>
</div>

{{-- MODAL ÚNICO --}}
<div id="apptModal" class="apptModal hidden">
  <div class="apptModal__card">
    <div class="apptModal__head">
      <div id="modalTitle" class="apptModal__title">Detalle</div>
      <button id="btnCloseModal" type="button" class="apptModal__close">Cerrar</button>
    </div>

    <div class="apptModal__info">
      <div><b>Servicio:</b> <span id="m_service">—</span></div>
      <div><b>Sesión / Tipo:</b> <span id="m_session">—</span></div>
      <div><b>Estudiante:</b> <span id="m_student">—</span> <span id="m_student_email" class="muted"></span></div>
      <div><b>Asesor:</b> <span id="m_advisor">—</span></div>
      <div><b>Horario:</b> <span id="m_time">—</span></div>
      <div><b>Estado sesión:</b> <span id="m_status">—</span></div>
      <div><b>Pago:</b> <span id="m_pay">—</span></div>
      <div>
        <b>Total:</b> S/ <span id="m_total">0.00</span>
        — <b>Pagado:</b> S/ <span id="m_paid">0.00</span>
        — <b>Saldo:</b> S/ <span id="m_balance">0.00</span>
      </div>
    </div>

    <hr class="apptModal__hr"/>

    <div id="appointmentActions">
      <form id="formStatus" class="apptModal__section">
        @csrf
        @method('PATCH')

        <div class="apptModal__sectionTitle">Actualizar estado de sesión</div>
        <select id="f_status" class="apptModal__input">
          <option value="pending">Pendiente</option>
          <option value="reserved">Reservado</option>
          <option value="completed">Realizado</option>
          <option value="cancelled">Cancelado</option>
        </select>

        <button type="submit" class="apptModal__btn">Guardar estado</button>
      </form>

      <hr class="apptModal__hr"/>

      <form id="formPayment" class="apptModal__section">
        @csrf
        @method('PATCH')

        <div class="apptModal__sectionTitle">Actualizar pagos (parcial / total)</div>

        <label class="apptModal__label">Total del servicio (S/)</label>
        <input id="f_total" class="apptModal__input" type="number" step="0.01" min="0">

        <label class="apptModal__label">Agregar pago (S/)</label>
        <input id="f_add_payment" class="apptModal__input" type="number" step="0.01" min="0" placeholder="Ej: 50.00">

        <label class="apptModal__label">Método</label>
        <input id="f_method" class="apptModal__input" placeholder="Ej: Efectivo / Yape / Plin">

        <label class="apptModal__label">Notas</label>
        <textarea id="f_notes" class="apptModal__input" rows="3" placeholder="Ej: Pagó 50% antes de sesión 1"></textarea>

        <button type="submit" class="apptModal__btn">Guardar pago</button>
      </form>

      <hr class="apptModal__hr"/>

      <div class="apptModal__section">
        <div class="apptModal__sectionTitle">Historial de pagos</div>
        <div id="paymentsBox" class="muted">
          <div>Sin datos.</div>
        </div>
      </div>
    </div>

    <div id="eventOnly" class="hidden">
      <div class="muted">Este es un evento. Solo es informativo (no editable aquí).</div>
    </div>
  </div>
</div>

@push('scripts')
  @vite(['resources/js/admin-appointments-calendar.js'])
@endpush
@endsection