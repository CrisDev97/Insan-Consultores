import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('studentCalendar');
  if (!el) return;

  const calendar = new Calendar(el, {
    plugins: [timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: 'es',
    timeZone: 'America/Lima',

    slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

    // rango visible (puedes subir/bajar)
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
        const state = window.__agendaState ? window.__agendaState() : {};
        if (!state.service_id || !state.advisor_id || !state.session_number) {
          success([]);
          return;
        }

        const url = new URL(window.location.origin + '/plataforma/agenda/feed');
        url.searchParams.set('start', info.startStr);
        url.searchParams.set('end', info.endStr);
        url.searchParams.set('service_id', state.service_id);
        url.searchParams.set('advisor_id', state.advisor_id);
        url.searchParams.set('session_number', state.session_number);

        const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
        const data = await res.json();

        const mapped = (data || []).map(ev => {
          const kind = ev?.extendedProps?.kind;

          if (kind === 'available') {
            return { ...ev, backgroundColor: '#DCFCE7', borderColor: '#22C55E', textColor: '#14532D' };
          }
          if (kind === 'reserved') {
            return { ...ev, backgroundColor: '#FEE2E2', borderColor: '#EF4444', textColor: '#7F1D1D' };
          }
          if (kind === 'event') {
            return { ...ev, backgroundColor: '#DBEAFE', borderColor: '#3B82F6', textColor: '#1E3A8A' };
          }

          return ev;
        });

        success(mapped);
      } catch (e) {
        failure(e);
      }
    },

    eventClick: (info) => {
      const kind = info.event.extendedProps?.kind;

      if (kind !== 'available') return;

      const starts_at = info.event.extendedProps?.starts_at;
      const ends_at = info.event.extendedProps?.ends_at;

      if (starts_at && ends_at && window.__pickSlot) {
        window.__pickSlot(starts_at, ends_at);
      }
    }
  });

  calendar.render();
  window.studentCalendar = calendar;
});