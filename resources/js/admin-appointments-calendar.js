import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

function qs(id) { return document.getElementById(id); }

document.addEventListener('DOMContentLoaded', () => {
  const el = qs('adminCalendar');
  if (!el) return;

  const modal = qs('apptModal');
  const btnClose = qs('btnCloseModal');

  const modalTitle = qs('modalTitle');
  const appointmentActions = qs('appointmentActions');
  const eventOnly = qs('eventOnly');

  const mService = qs('m_service');
  const mSession = qs('m_session');
  const mStudent = qs('m_student');
  const mStudentEmail = qs('m_student_email');
  const mAdvisor = qs('m_advisor');
  const mTime = qs('m_time');
  const mStatus = qs('m_status');
  const mPay = qs('m_pay');
  const mTotal = qs('m_total');
  const mPaid = qs('m_paid');
  const mBalance = qs('m_balance');

  const fStatus = qs('f_status');
  const fTotal = qs('f_total');
  const fAdd = qs('f_add_payment');
  const fMethod = qs('f_method');
  const fNotes = qs('f_notes');

  const formStatus = qs('formStatus');
  const formPayment = qs('formPayment');
  const paymentsBox = qs('paymentsBox');

  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

  let currentAppointmentId = null;
  let calendar = null;

  function openModal() {
    modal.classList.remove('hidden');
  }

  function closeModal() {
    modal.classList.add('hidden');
  }

  btnClose?.addEventListener('click', closeModal);
  modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });

  async function loadPayments(appointmentId) {
    try {
      paymentsBox.innerHTML = `<div class="text-slate-500">Cargando...</div>`;
      const res = await fetch(`/admin/appointments/${appointmentId}/payments`, {
        headers: { 'Accept': 'application/json' }
      });

      if (!res.ok) throw new Error('HTTP ' + res.status);

      const data = await res.json();

      if (!Array.isArray(data) || data.length === 0) {
        paymentsBox.innerHTML = `<div class="text-slate-500">Sin pagos registrados.</div>`;
        return;
      }

      const rows = data.map(p => {
        const dt = p.paid_at ? String(p.paid_at).replace('T', ' ').slice(0, 16) : '';
        const method = p.method ? `• ${p.method}` : '';
        const notes = p.notes ? `<div class="text-xs text-slate-500">${p.notes}</div>` : '';
        return `
          <div class="border border-slate-200 rounded-lg p-3 mb-2">
            <div class="flex justify-between">
              <div><b>S/ ${Number(p.amount).toFixed(2)}</b> ${method}</div>
              <div class="text-xs text-slate-500">${dt}</div>
            </div>
            ${notes}
          </div>
        `;
      }).join('');

      paymentsBox.innerHTML = rows;
    } catch (e) {
      paymentsBox.innerHTML = `<div class="text-rose-600">No se pudo cargar el historial.</div>`;
    }
  }

  function setAppointmentModal(props) {
    modalTitle.textContent = 'Detalle de reserva';
    appointmentActions.style.display = 'block';
    eventOnly.style.display = 'none';

    currentAppointmentId = props.appointment_id;

    mService.textContent = props.service || '—';
    mSession.textContent = props.session_number ? `Sesión ${props.session_number}` : '—';
    mStudent.textContent = props.student || '—';
    mStudentEmail.textContent = props.student_email ? `(${props.student_email})` : '';
    mAdvisor.textContent = props.advisor || '—';
    mTime.textContent = `${props.starts_at || ''} → ${props.ends_at || ''}`;

    const statusLabel =
      props.status === 'completed' ? 'Realizado' :
      props.status === 'cancelled' ? 'Cancelado' :
      'Pendiente';
    mStatus.textContent = statusLabel;

    const payLabel =
      props.payment_status === 'paid' ? 'Pagado' :
      props.payment_status === 'partial' ? 'Parcial' :
      'Pendiente';
    mPay.textContent = payLabel;

    mTotal.textContent = Number(props.service_total || 0).toFixed(2);
    mPaid.textContent = Number(props.paid_total || 0).toFixed(2);
    mBalance.textContent = Number(props.balance || 0).toFixed(2);

    fStatus.value = props.status || 'pending';
    fTotal.value = Number(props.service_total || 0).toFixed(2);
    fAdd.value = '';
    fMethod.value = '';
    fNotes.value = props.payment_notes || '';

    loadPayments(currentAppointmentId);
    openModal();
  }

  function setEventModal(props) {
    modalTitle.textContent = 'Detalle de evento';
    appointmentActions.style.display = 'none';
    eventOnly.style.display = 'block';

    mService.textContent = props.title_text || 'Evento';
    mSession.textContent = props.type ? String(props.type).toUpperCase() : '—';
    mStudent.textContent = '—';
    mStudentEmail.textContent = '';
    mAdvisor.textContent = props.advisor || '—';
    mTime.textContent = `${props.starts_at || ''} → ${props.ends_at || ''}`;
    mStatus.textContent = '—';
    mPay.textContent = '—';
    mTotal.textContent = '0.00';
    mPaid.textContent = '0.00';
    mBalance.textContent = '0.00';

    openModal();
  }

  // ✅ Crear calendar ANTES de los listeners que lo usan
  calendar = new Calendar(el, {
    plugins: [timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: 'es',

    slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

    slotMinTime: '06:00:00',
    slotMaxTime: '22:00:00',

    allDaySlot: false,
    nowIndicator: true,
    height: 'auto',
    expandRows: true,

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'timeGridWeek,timeGridDay'
    },

    events: async (info, success, failure) => {
      try {
        const url = new URL(window.location.origin + '/admin/appointments/calendar/feed');
        url.searchParams.set('start', info.startStr);
        url.searchParams.set('end', info.endStr);

        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);

        const data = await res.json();
        success(Array.isArray(data) ? data : []);
      } catch (e) {
        failure(e);
      }
    },

    eventClick: (clickInfo) => {
      const props = clickInfo.event.extendedProps || {};
      const kind = props.kind;

      if (kind === 'appointment') {
        setAppointmentModal(props);
        return;
      }

      if (kind === 'advisor_event') {
        setEventModal(props);
        return;
      }
    }
  });

  calendar.render();

  // ✅ Listeners ya con calendar definido
  formStatus?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentAppointmentId) return;

    const res = await fetch(`/admin/appointments/${currentAppointmentId}/status`, {
      method: 'PATCH',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify({ status: fStatus.value })
    });

    if (res.ok) {
      calendar.refetchEvents();
      closeModal();
    }
  });

  formPayment?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!currentAppointmentId) return;

    const payload = {
      service_total: fTotal.value,
      add_payment: fAdd.value,
      method: fMethod.value,
      payment_notes: fNotes.value,
    };

    const res = await fetch(`/admin/appointments/${currentAppointmentId}/payment`, {
      method: 'PATCH',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf,
      },
      body: JSON.stringify(payload)
    });

    if (res.ok) {
      calendar.refetchEvents();
      await loadPayments(currentAppointmentId);
    }
  });
});