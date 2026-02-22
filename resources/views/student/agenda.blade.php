@extends('layouts.student')

@section('title', 'Agenda | Plataforma')
@section('header', 'Agenda')
@section('subheader', 'Selecciona un servicio, un asesor, una sesión y reserva en el calendario (vista semanal).')

@section('content')
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="lg:col-span-2 space-y-6">
      {{-- Selección --}}
      <div class="bg-white border border-slate-200 rounded-xl p-5">
        <div class="font-semibold mb-4">1) Selección</div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="text-sm text-slate-600">Servicio</label>
            <select id="service_id" class="mt-1 w-full rounded-lg border-slate-300">
              <option value="">-- Selecciona --</option>
              @foreach($services as $s)
                <option value="{{ $s->id }}">{{ $s->title }} ({{ $s->sessions_count }} sesiones)</option>
              @endforeach
            </select>
          </div>

          <div>
            <label class="text-sm text-slate-600">Asesor</label>
            <select id="advisor_id" class="mt-1 w-full rounded-lg border-slate-300" disabled>
              <option value="">-- Selecciona servicio primero --</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-slate-600">Sesión</label>
            <select id="session_number" class="mt-1 w-full rounded-lg border-slate-300" disabled>
              <option value="">-- Selecciona servicio --</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-slate-600">Leyenda</label>
            <div class="mt-2 flex flex-wrap gap-2 text-xs">
              <span class="px-3 py-1 rounded-full" style="background:#DCFCE7;border:1px solid #22C55E;">Disponible</span>
              <span class="px-3 py-1 rounded-full" style="background:#FEE2E2;border:1px solid #EF4444;">Reservado</span>
              <span class="px-3 py-1 rounded-full" style="background:#DBEAFE;border:1px solid #3B82F6;">Evento</span>
            </div>
          </div>
        </div>
      </div>

      {{-- Calendario --}}
      <div class="bg-white rounded-2xl shadow p-4">
        <div class="flex items-center justify-between mb-3">
          <div class="font-semibold text-lg">Agenda (Semana)</div>
          <div class="text-sm text-slate-500" id="calendar_hint">Selecciona servicio, asesor y sesión</div>
        </div>
        <div id="studentCalendar"></div>
      </div>
    </div>

    {{-- Panel reserva --}}
    <div>
      <div class="bg-white border border-slate-200 rounded-xl p-5 sticky top-6">
        <div class="font-semibold mb-4">Detalle de reserva</div>

        <form method="POST" action="{{ route('student.agenda.book') }}" id="bookForm" class="space-y-3">
          @csrf

          <input type="hidden" name="service_id" id="f_service_id">
          <input type="hidden" name="advisor_id" id="f_advisor_id">
          <input type="hidden" name="session_number" id="f_session_number">
          <input type="hidden" name="date" id="f_date">
          <input type="hidden" name="starts_at" id="f_starts_at">
          <input type="hidden" name="ends_at" id="f_ends_at">

          <div class="text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Servicio</span><span id="d_service" class="font-medium">—</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Asesor</span><span id="d_advisor" class="font-medium">—</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Sesión</span><span id="d_session" class="font-medium">—</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Fecha/Hora</span><span id="d_time" class="font-medium">—</span></div>
          </div>

          <button type="submit" id="btnBook"
                  class="w-full px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800 disabled:opacity-40"
                  disabled>
            Reservar sesión
          </button>

          <p class="text-xs text-slate-500">
            Tip: haz click en un bloque <b>Disponible</b> del calendario para elegir tu horario.
          </p>
        </form>
      </div>
    </div>

  </div>

  @push('scripts')
    @vite(['resources/js/student-calendar.js'])
    <script>
      const serviceSelect = document.getElementById('service_id');
      const advisorSelect = document.getElementById('advisor_id');
      const sessionSelect = document.getElementById('session_number');

      const fService = document.getElementById('f_service_id');
      const fAdvisor = document.getElementById('f_advisor_id');
      const fSession = document.getElementById('f_session_number');
      const fDate = document.getElementById('f_date');
      const fStarts = document.getElementById('f_starts_at');
      const fEnds = document.getElementById('f_ends_at');

      const dService = document.getElementById('d_service');
      const dAdvisor = document.getElementById('d_advisor');
      const dSession = document.getElementById('d_session');
      const dTime = document.getElementById('d_time');

      const btnBook = document.getElementById('btnBook');
      const hint = document.getElementById('calendar_hint');

      function resetBooking() {
        dTime.textContent = '—';
        fDate.value = '';
        fStarts.value = '';
        fEnds.value = '';
        btnBook.disabled = true;
      }

      async function loadAdvisors(serviceId) {
        advisorSelect.disabled = true;
        advisorSelect.innerHTML = `<option value="">Cargando...</option>`;

        const res = await fetch(`{{ route('student.agenda.advisors') }}?service_id=${serviceId}`);
        const data = await res.json();

        advisorSelect.innerHTML = `<option value="">-- Selecciona --</option>`;
        data.forEach(a => {
          const opt = document.createElement('option');
          opt.value = a.id;
          opt.textContent = a.name;
          advisorSelect.appendChild(opt);
        });

        advisorSelect.disabled = false;
      }

      async function loadSessions(serviceId) {
        sessionSelect.disabled = true;
        sessionSelect.innerHTML = `<option value="">Cargando sesiones...</option>`;

        const res = await fetch(`{{ route('student.agenda.sessions') }}?service_id=${serviceId}`);
        const data = await res.json();

        sessionSelect.innerHTML = `<option value="">-- Selecciona sesión --</option>`;
        data.forEach(s => {
          const opt = document.createElement('option');
          opt.value = s.session_number;
          opt.textContent = `Sesión ${s.session_number} (${s.minutes} min)`;
          sessionSelect.appendChild(opt);
        });

        sessionSelect.disabled = false;
      }

      function canLoadCalendar() {
        return !!(serviceSelect.value && advisorSelect.value && sessionSelect.value);
      }

      function refreshCalendar() {
        if (!window.studentCalendar) return;
        window.studentCalendar.refetchEvents();
      }

      // Servicio
      serviceSelect.addEventListener('change', async () => {
        const serviceId = serviceSelect.value;

        dService.textContent = serviceId ? serviceSelect.options[serviceSelect.selectedIndex]?.text : '—';
        fService.value = serviceId || '';

        // reset dependientes
        advisorSelect.innerHTML = `<option value="">-- Selecciona servicio primero --</option>`;
        advisorSelect.disabled = true;

        sessionSelect.innerHTML = `<option value="">-- Selecciona servicio --</option>`;
        sessionSelect.disabled = true;

        dAdvisor.textContent = '—';
        dSession.textContent = '—';
        fAdvisor.value = '';
        fSession.value = '';

        resetBooking();
        hint.textContent = 'Selecciona servicio, asesor y sesión';

        if (!serviceId) return;

        await loadAdvisors(serviceId);
        await loadSessions(serviceId);
      });

      // Asesor
      advisorSelect.addEventListener('change', () => {
        const advisorId = advisorSelect.value;

        dAdvisor.textContent = advisorId ? advisorSelect.options[advisorSelect.selectedIndex]?.text : '—';
        fAdvisor.value = advisorId || '';

        resetBooking();
        hint.textContent = canLoadCalendar() ? 'Haz click en un bloque Disponible' : 'Selecciona servicio, asesor y sesión';
        refreshCalendar();
      });

      // Sesión
      sessionSelect.addEventListener('change', () => {
        const s = sessionSelect.value;
        dSession.textContent = s ? sessionSelect.options[sessionSelect.selectedIndex]?.text : '—';
        fSession.value = s || '';

        resetBooking();
        hint.textContent = canLoadCalendar() ? 'Haz click en un bloque Disponible' : 'Selecciona servicio, asesor y sesión';
        refreshCalendar();
      });

      // expose to student-calendar.js
      window.__agendaState = () => ({
        service_id: serviceSelect.value,
        advisor_id: advisorSelect.value,
        session_number: sessionSelect.value,
      });

      // handler para cuando haces click en “Disponible” desde el calendario
      window.__pickSlot = (starts_at, ends_at) => {
        const start = new Date(starts_at);
        const yyyy = start.getFullYear();
        const mm = String(start.getMonth()+1).padStart(2,'0');
        const dd = String(start.getDate()).padStart(2,'0');
        const dateStr = `${yyyy}-${mm}-${dd}`;

        fDate.value = dateStr;
        fStarts.value = starts_at;
        fEnds.value = ends_at;

        dTime.textContent = `${dateStr} | ${starts_at.slice(11,16)} - ${ends_at.slice(11,16)}`;

        btnBook.disabled = !(fService.value && fAdvisor.value && fSession.value && fStarts.value && fEnds.value);
      };
    </script>
  @endpush
@endsection
