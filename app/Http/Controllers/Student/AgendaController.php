<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use App\Models\AdvisorEvent;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    public function index()
    {
        $services = Service::query()
            ->where('is_active', 1)
            ->orderBy('position')
            ->get(['id','title','sessions_count']);

        return view('student.agenda', compact('services'));
    }

    // devuelve asesores por servicio
    public function advisors(Request $request)
    {
        $serviceId = (int) $request->query('service_id');

        $advisors = Advisor::query()
            ->where('is_active', 1)
            ->whereHas('services', function ($q) use ($serviceId) {
                $q->where('services.id', $serviceId)
                  ->where('advisor_service.is_active', 1);
            })
            ->orderBy('name')
            ->get(['id','name']);

        return response()->json($advisors);
    }

    // genera slots por día según disponibilidad y citas existentes
    public function slots(Request $request)
    {
        $serviceId = (int) $request->query('service_id');
        $advisorId = (int) $request->query('advisor_id');
        $date = $request->query('date'); // YYYY-MM-DD

        if (!$serviceId || !$advisorId || !$date) {
            return response()->json(['message' => 'Parámetros incompletos'], 422);
        }

        $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $weekday = (int) $day->dayOfWeek; // 0=Sunday

        // duración (desde pivote advisor_service)
        $duration = (int) DB::table('advisor_service')
            ->where('advisor_id', $advisorId)
            ->where('service_id', $serviceId)
            ->value('duration_minutes') ?? 60;

        // disponibilidad del día
        $ranges = DB::table('advisor_availabilities')
            ->where('advisor_id', $advisorId)
            ->where('weekday', $weekday)
            ->where('is_active', 1)
            ->get(['start_time','end_time','slot_minutes']);

        if ($ranges->isEmpty()) {
            return response()->json([]);
        }

        // citas ocupadas del asesor ese día
        $busy = Appointment::query()
            ->where('advisor_id', $advisorId)
            ->whereDate('starts_at', $day->toDateString())
            ->whereIn('status', ['reserved'])
            ->get(['starts_at','ends_at']);

        $busyIntervals = $busy->map(fn($a) => [
            'start' => Carbon::parse($a->starts_at),
            'end' => Carbon::parse($a->ends_at),
        ]);

        $events = AdvisorEvent::query()
            ->where('advisor_id', $advisorId)
            ->where('is_active', 1)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($day) {
                $q->where('start_at', '<', $day->copy()->endOfDay())
                ->where('end_at', '>', $day->copy()->startOfDay());
            })
            ->get(['start_at','end_at','title','type','visibility']);

        $eventIntervals = $events->map(fn($e) => [
            'start' => Carbon::parse($e->start_at),
            'end' => Carbon::parse($e->end_at),
            'reason' => ($e->visibility === 'public')
                ? (strtoupper((string) $e->type) . ': ' . (string) $e->title)
                : 'Ocupado',
        ]);


        $slots = [];

        foreach ($ranges as $r) {
            $slotMinutes = (int) $r->slot_minutes;
            $start = Carbon::parse($day->toDateString().' '.$r->start_time);
            $end = Carbon::parse($day->toDateString().' '.$r->end_time);

            // iterar en slots (ej 60 min)
            for ($t = $start->copy(); $t->addMinutes(0)->lessThan($end); $t->addMinutes($slotMinutes)) {
                $slotStart = $t->copy();
                $slotEnd = $t->copy()->addMinutes($duration);

                // no pasar del rango
                if ($slotEnd->greaterThan($end)) {
                    continue;
                }

                // no permitir pasado
                if ($slotStart->lessThanOrEqualTo(now())) {
                    continue;
                }

                // verificar choque con busy
                $overlaps = $busyIntervals->contains(function ($b) use ($slotStart, $slotEnd) {
                    return $slotStart->lt($b['end']) && $slotEnd->gt($b['start']);
                });

                $blockedReason = null;

                // choque con citas
                $overlapsAppointment = $busyIntervals->contains(function ($b) use ($slotStart, $slotEnd) {
                    return $slotStart->lt($b['end']) && $slotEnd->gt($b['start']);
                });
                if ($overlapsAppointment) {
                    $blockedReason = 'Reservado';
                }

                // choque con eventos (si aún no está bloqueado por cita)
                if (!$blockedReason) {
                    $eventHit = $eventIntervals->first(function ($e) use ($slotStart, $slotEnd) {
                        return $slotStart->lt($e['end']) && $slotEnd->gt($e['start']);
                    });
                    if ($eventHit) {
                        $blockedReason = $eventHit['reason'];
                    }
                }

                $slots[] = [
                    'starts_at' => $slotStart->toDateTimeString(),
                    'ends_at'   => $slotEnd->toDateTimeString(),
                    'label'     => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    'blocked'   => (bool) $blockedReason,
                    'reason'    => $blockedReason,
                ];

            }
        }

        return response()->json($slots);
    }

    public function book(Request $request)
    {
        $data = $request->validate([
            'service_id' => ['required','integer'],
            'advisor_id' => ['required','integer'],
            'date' => ['required','date_format:Y-m-d'],
            'starts_at' => ['required','date_format:Y-m-d H:i:s'],
            'ends_at' => ['required','date_format:Y-m-d H:i:s'],
            'session_number' => ['required','integer','min:1'],
        ]);

        $studentId = (int) $request->user()->id;

        // validación sesión máxima (según servicio)
        $service = Service::findOrFail($data['service_id']);
        if ((int)$data['session_number'] > (int)$service->sessions_count) {
            return back()->with('error', 'Número de sesión inválido para este servicio.');
        }

        // evitar reservar misma sesión dos veces
        $exists = Appointment::query()
            ->where('student_user_id', $studentId)
            ->where('service_id', $service->id)
            ->where('session_number', $data['session_number'])
            ->whereIn('status', ['reserved','completed'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ya tienes reservada esa sesión para este servicio.');
        }

        // crear reserva si el slot sigue libre (bloqueo simple)
        try {
            DB::transaction(function () use ($data, $studentId) {

                $conflict = Appointment::query()
                    ->where('advisor_id', $data['advisor_id'])
                    ->where('status', 'reserved')
                    ->where(function ($q) use ($data) {
                        $q->whereBetween('starts_at', [$data['starts_at'], $data['ends_at']])
                          ->orWhereBetween('ends_at', [$data['starts_at'], $data['ends_at']])
                          ->orWhere(function ($q2) use ($data) {
                              $q2->where('starts_at', '<', $data['starts_at'])
                                 ->where('ends_at', '>', $data['ends_at']);
                          });
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    throw new \RuntimeException('El horario ya fue tomado, elige otro.');
                }

                Appointment::create([
                    'student_user_id' => $studentId,
                    'advisor_id' => $data['advisor_id'],
                    'service_id' => $data['service_id'],
                    'session_number' => $data['session_number'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],
                    'status' => 'reserved',
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('student.agenda')->with('success', 'Sesión reservada correctamente.');
    }

    public function feed(Request $request)
    {
        $serviceId = (int) $request->query('service_id');
        $advisorId = (int) $request->query('advisor_id');
        $start = $request->query('start'); // YYYY-MM-DD
        $end   = $request->query('end');   // YYYY-MM-DD (FullCalendar lo manda como fin-exclusivo)

        if (!$serviceId || !$advisorId || !$start || !$end) {
            return response()->json([]);
        }

        $startDate = Carbon::parse($start)->startOfDay();
        $endDate   = Carbon::parse($end)->startOfDay(); // fin-exclusivo

        // duración del servicio (pivot advisor_service)
        $duration = (int) (DB::table('advisor_service')
            ->where('advisor_id', $advisorId)
            ->where('service_id', $serviceId)
            ->value('duration_minutes') ?? 60);

        // Trae TODAS las disponibilidades del asesor (por weekday)
        $availabilities = DB::table('advisor_availabilities')
            ->where('advisor_id', $advisorId)
            ->where('is_active', 1)
            ->get(['weekday','start_time','end_time','slot_minutes'])
            ->groupBy('weekday');

        // Citas del rango
        $appointments = Appointment::query()
            ->where('advisor_id', $advisorId)
            ->where('status', 'reserved')
            ->where('starts_at', '<', $endDate)
            ->where('ends_at',   '>', $startDate)
            ->get(['starts_at','ends_at']);

        $busyIntervals = $appointments->map(fn($a) => [
            'start' => Carbon::parse($a->starts_at),
            'end'   => Carbon::parse($a->ends_at),
        ]);

        // Eventos del rango
        $events = AdvisorEvent::query()
            ->where('advisor_id', $advisorId)
            ->where('is_active', 1)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $endDate)
            ->where('end_at',   '>', $startDate)
            ->get(['start_at','end_at','title','type','visibility']);

        $eventIntervals = $events->map(fn($e) => [
            'start'  => Carbon::parse($e->start_at),
            'end'    => Carbon::parse($e->end_at),
            'reason' => ($e->visibility === 'public')
                ? (strtoupper((string)$e->type).': '.(string)$e->title)
                : 'Ocupado',
            'kind'   => 'event',
        ]);

        $calendar = [];

        // Itera día por día del rango
        $period = CarbonPeriod::create($startDate, '1 day', $endDate->copy()->subDay());
        foreach ($period as $day) {
            $weekday = (int) $day->dayOfWeek;
            $ranges = $availabilities->get($weekday, collect());
            if ($ranges->isEmpty()) continue;

            foreach ($ranges as $r) {
                $slotMinutes = (int) $r->slot_minutes;
                $rangeStart = Carbon::parse($day->toDateString().' '.$r->start_time);
                $rangeEnd   = Carbon::parse($day->toDateString().' '.$r->end_time);

                for ($t = $rangeStart->copy(); $t->lt($rangeEnd); $t->addMinutes($slotMinutes)) {
                    $slotStart = $t->copy();
                    $slotEnd   = $t->copy()->addMinutes($duration);

                    if ($slotEnd->gt($rangeEnd)) continue;
                    if ($slotStart->lte(now())) continue;

                    // ¿Choca con cita?
                    $isReserved = $busyIntervals->contains(fn($b) =>
                        $slotStart->lt($b['end']) && $slotEnd->gt($b['start'])
                    );

                    // ¿Choca con evento?
                    $eventHit = $eventIntervals->first(fn($e) =>
                        $slotStart->lt($e['end']) && $slotEnd->gt($e['start'])
                    );

                    if ($isReserved) {
                        $calendar[] = [
                            'title' => 'Reservado',
                            'start' => $slotStart->toIso8601String(),
                            'end'   => $slotEnd->toIso8601String(),
                            'display' => 'block',
                            'backgroundColor' => '#FEE2E2',
                            'borderColor' => '#EF4444',
                            'textColor' => '#7F1D1D',
                            'extendedProps' => [
                                'blocked' => true,
                                'reason'  => 'Reservado',
                            ],
                        ];
                        continue;
                    }

                    if ($eventHit) {
                        $calendar[] = [
                            'title' => $eventHit['reason'],
                            'start' => $slotStart->toIso8601String(),
                            'end'   => $slotEnd->toIso8601String(),
                            'display' => 'block',
                            'backgroundColor' => '#DBEAFE',
                            'borderColor' => '#3B82F6',
                            'textColor' => '#1E3A8A',
                            'extendedProps' => [
                                'blocked' => true,
                                'reason'  => $eventHit['reason'],
                            ],
                        ];
                        continue;
                    }

                    // Disponible
                    $calendar[] = [
                        'title' => 'Disponible',
                        'start' => $slotStart->toIso8601String(),
                        'end'   => $slotEnd->toIso8601String(),
                        'display' => 'block',
                        'backgroundColor' => '#DCFCE7',
                        'borderColor' => '#22C55E',
                        'textColor' => '#14532D',
                        'extendedProps' => [
                            'blocked'   => false,
                            'starts_at' => $slotStart->toDateTimeString(),
                            'ends_at'   => $slotEnd->toDateTimeString(),
                        ],
                    ];
                }
            }
        }

        return response()->json($calendar);
    }
}