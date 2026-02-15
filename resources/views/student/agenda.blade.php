@extends('layouts.student')

@section('title', 'Agenda | Plataforma')
@section('header', 'Agenda')
@section('subheader', 'Selecciona un servicio, un asesor y elige un horario disponible.')

@section('content')
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Selección --}}
    <div class="lg:col-span-2 space-y-6">
      <div class="bg-white border border-slate-200 rounded-xl p-5">
        <div class="font-semibold mb-4">1) Selección</div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-sm text-slate-600">Servicio</label>
            <select id="service_id" class="mt-1 w-full rounded-lg border-slate-300">
              <option value="">-- Selecciona --</option>
              @foreach($services as $s)
                <option value="{{ $s->id }}" data-sessions="{{ $s->sessions_count }}">
                  {{ $s->title }} ({{ $s->sessions_count }} sesiones)
                </option>
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
            <label class="text-sm text-slate-600">Día</label>
            <input id="date" type="date" class="mt-1 w-full rounded-lg border-slate-300" disabled>
          </div>
        </div>
      </div>

      {{-- Calendario --}}
      <div class="bg-white border border-slate-200 rounded-xl p-5">
        <div class="flex items-center justify-between mb-4">
          <div class="font-semibold">2) Agenda (Semana)</div>

          <div class="flex gap-2 text-xs">
            <span class="px-3 py-1 rounded-full" style="background:#DCFCE7;border:1px solid #22C55E;">Disponible</span>
            <span class="px-3 py-1 rounded-full" style="background:#FEE2E2;border:1px solid #EF4444;">Reservado</span>
            <span class="px-3 py-1 rounded-full" style="background:#DBEAFE;border:1px solid #3B82F6;">Evento</span>
          </div>
        </div>

        <div id="studentCalendar"></div>

        <div class="mt-3 text-sm text-slate-500">
          Selecciona <b>Servicio</b> y <b>Asesor</b> para cargar la agenda.
          Haz clic en un bloque <span class="font-medium" style="color:#14532D;">Disponible</span> para reservar.
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
          <input type="hidden" name="date" id="f_date">
          <input type="hidden" name="starts_at" id="f_starts_at">
          <input type="hidden" name="ends_at" id="f_ends_at">

          <div class="text-sm">
            <div class="flex justify-between"><span class="text-slate-500">Servicio</span><span id="d_service" class="font-medium">—</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Asesor</span><span id="d_advisor" class="font-medium">—</span></div>
            <div class="flex justify-between"><span class="text-slate-500">Fecha/Hora</span><span id="d_time" class="font-medium">—</span></div>
          </div>

          <div>
            <label class="text-sm text-slate-600">Sesión</label>
            <select name="session_number" id="session_number" class="mt-1 w-full rounded-lg border-slate-300" disabled>
              <option value="">-- Selecciona servicio --</option>
            </select>
            <p class="text-xs text-slate-500 mt-1">Debes reservar por sesión (1..N).</p>
          </div>

          <button type="submit" id="btnBook"
                  class="w-full px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800 disabled:opacity-40"
                  disabled>
            Reservar sesión
          </button>
        </form>
      </div>
    </div>

  </div>

  @push('scripts')
  @vite(['resources/js/student-calendar.js'])
  <script>
    const serviceSelect = document.getElementById('service_id');
    const advisorSelect = document.getElementById('advisor_id');
    const dateInput = document.getElementById('date');
    const slotsBox = document.getElementById('slots');
    const slotsHint = document.getElementById('slots_hint');

    const fService = document.getElementById('f_service_id');
    const fAdvisor = document.getElementById('f_advisor_id');
    const fDate = document.getElementById('f_date');
    const fStarts = document.getElementById('f_starts_at');
    const fEnds = document.getElementById('f_ends_at');

    const dService = document.getElementById('d_service');
    const dAdvisor = document.getElementById('d_advisor');
    const dTime = document.getElementById('d_time');

    const sessionSelect = document.getElementById('session_number');
    const btnBook = document.getElementById('btnBook');

    function resetSlots() {
      if (slotsBox) {
        slotsBox.innerHTML = `<div class="text-slate-500 text-sm">Sin horarios.</div>`;
      }
      if (slotsHint) {
        slotsHint.textContent = '';
      }
      dTime.textContent = '—';
      fStarts.value = '';
      fEnds.value = '';
      btnBook.disabled = true;
    }

    function buildSessions(count) {
      sessionSelect.innerHTML = '';
      if (!count) {
        sessionSelect.innerHTML = `<option value="">-- Selecciona servicio --</option>`;
        sessionSelect.disabled = true;
        return;
      }
      sessionSelect.disabled = false;
      sessionSelect.innerHTML = `<option value="">-- Selecciona sesión --</option>`;
      for (let i=1; i<=count; i++) {
        const opt = document.createElement('option');
        opt.value = i;
        opt.textContent = `Sesión ${i} de ${count}`;
        sessionSelect.appendChild(opt);
      }
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

    async function loadSlots() {
      const serviceId = serviceSelect.value;
      const advisorId = advisorSelect.value;
      const date = dateInput.value;

      resetSlots();

      if (!serviceId || !advisorId || !date) return;

      slotsHint.textContent = 'Cargando horarios...';

      const res = await fetch(`{{ route('student.agenda.slots') }}?service_id=${serviceId}&advisor_id=${advisorId}&date=${date}`);
      const slots = await res.json();

      slotsHint.textContent = slots.length ? 'Selecciona un horario' : 'No hay disponibilidad para ese día';

      if (!slots.length) {
        slotsBox.innerHTML = `<div class="text-slate-500 text-sm">No hay horarios disponibles.</div>`;
        return;
      }

      slotsBox.innerHTML = '';
      slots.forEach(s => {
        const btn = document.createElement('button');
        btn.type = 'button';

        const isBlocked = !!s.blocked;

        btn.className = isBlocked
          ? 'text-left px-4 py-3 rounded-lg border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed'
          : 'text-left px-4 py-3 rounded-lg border border-slate-200 hover:border-slate-400 hover:bg-slate-50 transition';

        btn.disabled = isBlocked;

        const sub = isBlocked && s.reason ? `<div class="text-xs text-rose-600 mt-1">${s.reason}</div>` : '';

        btn.innerHTML = `
          <div class="font-medium">${s.label}</div>
          <div class="text-xs text-slate-500">${date}</div>
          ${sub}
        `;

        if (!isBlocked) {
          btn.onclick = () => {
            dTime.textContent = `${date} | ${s.label}`;
            fStarts.value = s.starts_at;
            fEnds.value = s.ends_at;

            btnBook.disabled = !(sessionSelect.value && fStarts.value && fEnds.value);
          };
        }

        slotsBox.appendChild(btn);
      });

    }

    serviceSelect.addEventListener('change', async () => {
      const serviceId = serviceSelect.value;
      const label = serviceSelect.options[serviceSelect.selectedIndex]?.text || '—';
      dService.textContent = serviceId ? label : '—';
      fService.value = serviceId || '';

      // sessions_count
      const sessions = serviceSelect.options[serviceSelect.selectedIndex]?.dataset?.sessions;
      buildSessions(parseInt(sessions || '0', 10));

      advisorSelect.innerHTML = `<option value="">-- Selecciona servicio primero --</option>`;
      advisorSelect.disabled = true;
      dateInput.value = '';
      dateInput.disabled = true;

      dAdvisor.textContent = '—';
      fAdvisor.value = '';
      fDate.value = '';

      resetSlots();

      if (!serviceId) return;

      await loadAdvisors(serviceId);
      dateInput.disabled = false;
    });

    advisorSelect.addEventListener('change', () => {
      const name = advisorSelect.options[advisorSelect.selectedIndex]?.text || '—';
      dAdvisor.textContent = advisorSelect.value ? name : '—';
      fAdvisor.value = advisorSelect.value || '';
      resetSlots();
      loadSlots();
    });

    dateInput.addEventListener('change', () => {
      fDate.value = dateInput.value || '';
      resetSlots();
      loadSlots();
    });

    sessionSelect.addEventListener('change', () => {
      // habilita reservar si ya hay slot elegido
      btnBook.disabled = !(sessionSelect.value && fStarts.value && fEnds.value);
    });
  </script>
  @endpush
@endsection