import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('adminCalendar');
  if (!el) return;

  // IMPORTANTE: usa la ruta REAL que tienes en web.php:
  // Route::get('appointments/calendar/feed'...) -> /admin/appointments/calendar/feed
  const FEED_URL = `${window.location.origin}/admin/appointments/calendar/feed`;

  const calendar = new Calendar(el, {
    plugins: [timeGridPlugin, dayGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: 'es',

    // 24h
    slotLabelFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
    eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },

    allDaySlot: false,
    nowIndicator: true,
    height: 'auto',
    expandRows: true,

    // rango visual (ajústalo si deseas)
    slotMinTime: '06:00:00',
    slotMaxTime: '22:00:00',

    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'timeGridWeek,timeGridDay,dayGridMonth'
    },

    events: async (info, success, failure) => {
      try {
        const url = new URL(FEED_URL);
        url.searchParams.set('start', info.startStr);
        url.searchParams.set('end', info.endStr);

        const res = await fetch(url.toString(), {
          headers: { 'Accept': 'application/json' }
        });

        // si estás logueado pero el middleware redirige, aquí te traería HTML (login)
        // esto detecta ese caso
        const ct = res.headers.get('content-type') || '';
        if (!ct.includes('application/json')) {
          console.error('Feed no devolvió JSON. Revisa auth/middleware o URL:', url.toString());
          const text = await res.text();
          console.error(text);
          success([]);
          return;
        }

        const data = await res.json();
        success(Array.isArray(data) ? data : []);
      } catch (e) {
        console.error(e);
        failure(e);
      }
    },

    eventDidMount: (info) => {
      // para que respete los saltos de línea \n en el title
      const titleEl = info.el.querySelector('.fc-event-title');
      if (titleEl) titleEl.style.whiteSpace = 'pre-line';
    },

    eventClick: (info) => {
      // luego conectamos el modal aquí (si ya lo tienes, lo enlazamos en el siguiente paso)
      console.log('click event', info.event.id, info.event.extendedProps);
    }
  });

  calendar.render();
  window.adminCalendar = calendar;
});