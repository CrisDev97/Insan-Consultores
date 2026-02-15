import { Calendar } from '@fullcalendar/core'
import timeGridPlugin from '@fullcalendar/timegrid'
import interactionPlugin from '@fullcalendar/interaction'
import esLocale from '@fullcalendar/core/locales/es'

document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('studentCalendar')
  if (!el) return

  // ⚠️ Ajusta estos IDs a tus selects reales (los que ya tienes en tu agenda.blade.php)
  const serviceSelect = document.getElementById('service_id')
  const advisorSelect = document.getElementById('advisor_id')

  // Inputs ocultos del form de reserva (si ya los tienes)
    const fStarts = document.getElementById('f_starts_at')
    const fEnds = document.getElementById('f_ends_at')

  const calendar = new Calendar(el, {
    plugins: [timeGridPlugin, interactionPlugin],
    initialView: 'timeGridWeek',
    locale: esLocale,
    slotLabelFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    },
    eventTimeFormat: {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    },
    height: 'auto',
    nowIndicator: true,
    allDaySlot: false,
    slotMinTime: '07:00:00',
    slotMaxTime: '22:00:00',
    firstDay: 1, // lunes
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'timeGridWeek,timeGridDay'
    },

    // Feed dinámico
    events: async (info, success, failure) => {
      const serviceId = serviceSelect?.value
      const advisorId = advisorSelect?.value
      if (!serviceId || !advisorId) {
        success([])
        return
      }

      const url = `/plataforma/agenda/feed?service_id=${serviceId}&advisor_id=${advisorId}&start=${info.startStr.slice(0,10)}&end=${info.endStr.slice(0,10)}`
      try {
        const res = await fetch(url, { headers: { 'Accept': 'application/json' } })
        const data = await res.json()
        success(data)
      } catch (e) {
        failure(e)
      }
    },

    eventClick: (arg) => {
        const blocked = !!arg.event.extendedProps.blocked
        if (blocked) return

        const starts_at = arg.event.extendedProps.starts_at
        const ends_at = arg.event.extendedProps.ends_at

        // Inputs hidden reales de tu blade
        const fService = document.getElementById('f_service_id')
        const fAdvisor = document.getElementById('f_advisor_id')
        const fDate = document.getElementById('f_date')
        const fStarts = document.getElementById('f_starts_at')
        const fEnds = document.getElementById('f_ends_at')

        // Labels del panel derecho
        const dService = document.getElementById('d_service')
        const dAdvisor = document.getElementById('d_advisor')
        const dTime = document.getElementById('d_time')

        const serviceSelect = document.getElementById('service_id')
        const advisorSelect = document.getElementById('advisor_id')
        const btnBook = document.getElementById('btnBook')
        const sessionSelect = document.getElementById('session_number')

        // Guarda selección en hidden inputs
        if (fService) fService.value = serviceSelect?.value || ''
        if (fAdvisor) fAdvisor.value = advisorSelect?.value || ''
        if (fStarts) fStarts.value = starts_at || ''
        if (fEnds) fEnds.value = ends_at || ''

        // date (YYYY-MM-DD) para tu form
        const startDate = (starts_at || '').slice(0, 10)
        if (fDate) fDate.value = startDate

        // Muestra en panel derecho
        const serviceLabel = serviceSelect?.options?.[serviceSelect.selectedIndex]?.text || '—'
        const advisorLabel = advisorSelect?.options?.[advisorSelect.selectedIndex]?.text || '—'
        if (dService) dService.textContent = serviceLabel
        if (dAdvisor) dAdvisor.textContent = advisorLabel
        if (dTime) dTime.textContent = `${startDate} | ${arg.event.start.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })} - ${arg.event.end.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', hour12: false })}`

        // Habilitar reservar solo si ya eligió sesión
        if (btnBook && sessionSelect) {
            btnBook.disabled = !(sessionSelect.value && fStarts.value && fEnds.value)
        }
        },

  })

  calendar.render()

  // Refrescar si cambian selects
  serviceSelect?.addEventListener('change', () => calendar.refetchEvents())
  advisorSelect?.addEventListener('change', () => calendar.refetchEvents())
})
