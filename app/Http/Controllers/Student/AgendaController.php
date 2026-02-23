<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use App\Models\AdvisorEvent;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\ServiceSession;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaController extends Controller
{
    private function tz(): DateTimeZone
    {
        return new DateTimeZone(config('app.timezone', 'America/Lima'));
    }

    private function parseDay(string $ymd): DateTimeImmutable
    {
        // YYYY-MM-DD
        $dt = DateTimeImmutable::createFromFormat('Y-m-d', $ymd, $this->tz());
        if (!$dt) {
            throw new \InvalidArgumentException('Fecha inválida');
        }
        return $dt->setTime(0, 0, 0);
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->tz());
    }

    public function index()
    {
        $services = Service::query()
            ->where('is_active', 1)
            ->orderBy('position')
            ->get(['id','title','sessions_count']);

        return view('student.agenda', compact('services'));
    }

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

    // ✅ sesiones 1..N con duración desde service_sessions
    public function sessions(Request $request)
    {
        $serviceId = (int) $request->query('service_id');
        if (!$serviceId) return response()->json([], 422);

        $service = Service::find($serviceId);
        if (!$service) return response()->json([], 404);

        $count = (int) ($service->sessions_count ?? 0);
        if ($count <= 0) return response()->json([]);

        $rows = ServiceSession::query()
            ->where('service_id', $serviceId)
            ->get(['session_number','duration_minutes'])
            ->keyBy('session_number');

        $out = [];
        for ($i = 1; $i <= $count; $i++) {
            $out[] = [
                'session_number' => $i,
                'minutes' => (int) (($rows[$i]->duration_minutes ?? null) ?: 60),
            ];
        }

        return response()->json($out);
    }

    // (opcional) tu endpoint viejo por día, lo dejamos, pero si ya usas FullCalendar, podrías no usarlo
    public function slots(Request $request)
    {
        $serviceId = (int) $request->query('service_id');
        $advisorId = (int) $request->query('advisor_id');
        $date = (string) $request->query('date');

        if (!$serviceId || !$advisorId || !$date) {
            return response()->json(['message' => 'Parámetros incompletos'], 422);
        }

        $day = $this->parseDay($date);
        $weekday = (int) $day->format('w'); // 0=Domingo

        // si quieres: duración por sesión aquí también, pero normalmente ya usas el FEED
        $duration = (int) (DB::table('advisor_service')
            ->where('advisor_id', $advisorId)
            ->where('service_id', $serviceId)
            ->value('duration_minutes') ?? 60);

        $ranges = DB::table('advisor_availabilities')
            ->where('advisor_id', $advisorId)
            ->where('weekday', $weekday)
            ->where('is_active', 1)
            ->get(['start_time','end_time','slot_minutes']);

        if ($ranges->isEmpty()) return response()->json([]);

        $busy = Appointment::query()
            ->where('advisor_id', $advisorId)
            ->whereDate('starts_at', $day->format('Y-m-d'))
            ->whereIn('status', ['pending','reserved','completed'])
            ->get(['starts_at','ends_at']);

        $busyIntervals = $busy->map(function ($a) {
            return [
                'start' => strtotime($a->starts_at),
                'end'   => strtotime($a->ends_at),
            ];
        });

        $events = AdvisorEvent::query()
            ->where('advisor_id', $advisorId)
            ->where('is_active', 1)
            ->where('status', '!=', 'cancelled')
            ->whereDate('start_at', '<=', $day->format('Y-m-d'))
            ->whereDate('end_at', '>=', $day->format('Y-m-d'))
            ->get(['start_at','end_at','title','type','visibility']);

        $eventIntervals = $events->map(function ($e) {
            $reason = ($e->visibility === 'public')
                ? (strtoupper((string) $e->type) . ': ' . (string) $e->title)
                : 'Ocupado';

            return [
                'start' => strtotime($e->start_at),
                'end'   => strtotime($e->end_at),
                'reason'=> $reason,
            ];
        });

        $nowTs = $this->now()->getTimestamp();

        $slots = [];

        foreach ($ranges as $r) {
            $slotMinutes = (int) $r->slot_minutes;

            $rangeStart = new DateTimeImmutable($day->format('Y-m-d').' '.$r->start_time, $this->tz());
            $rangeEnd   = new DateTimeImmutable($day->format('Y-m-d').' '.$r->end_time, $this->tz());

            for ($t = $rangeStart; $t < $rangeEnd; $t = $t->add(new DateInterval('PT'.$slotMinutes.'M'))) {
                $slotStart = $t;
                $slotEnd   = $t->add(new DateInterval('PT'.$duration.'M'));

                if ($slotEnd > $rangeEnd) continue;
                if ($slotStart->getTimestamp() <= $nowTs) continue;

                $ss = $slotStart->getTimestamp();
                $ee = $slotEnd->getTimestamp();

                $blockedReason = null;

                $overlapsAppointment = $busyIntervals->contains(fn($b) => $ss < $b['end'] && $ee > $b['start']);
                if ($overlapsAppointment) $blockedReason = 'Reservado';

                if (!$blockedReason) {
                    $eventHit = $eventIntervals->first(fn($e) => $ss < $e['end'] && $ee > $e['start']);
                    if ($eventHit) $blockedReason = $eventHit['reason'];
                }

                $slots[] = [
                    'starts_at' => $slotStart->format('Y-m-d H:i:s'),
                    'ends_at'   => $slotEnd->format('Y-m-d H:i:s'),
                    'label'     => $slotStart->format('H:i').' - '.$slotEnd->format('H:i'),
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

        $service = Service::findOrFail($data['service_id']);
        $count = (int) ($service->sessions_count ?? 0);

        if ($count <= 0 || (int)$data['session_number'] > $count) {
            return back()->with('error', 'Número de sesión inválido para este servicio.');
        }

        // ✅ Importante: NO bloquees por “reserved/completed” si tu sistema ahora crea “pending”
        $exists = Appointment::query()
            ->where('student_user_id', $studentId)
            ->where('service_id', $service->id)
            ->where('session_number', $data['session_number'])
            ->whereIn('status', ['pending','reserved','completed'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ya tienes reservada esa sesión para este servicio.');
        }

        try {
            DB::transaction(function () use ($data, $studentId, $service) {

                // conflicto con citas existentes (considera pending/reserved/completed como ocupado)
                $conflict = Appointment::query()
                    ->where('advisor_id', $data['advisor_id'])
                    ->whereIn('status', ['pending','reserved','completed'])
                    ->where(function ($q) use ($data) {
                        $q->where('starts_at', '<', $data['ends_at'])
                          ->where('ends_at',   '>', $data['starts_at']);
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($conflict) {
                    throw new \RuntimeException('El horario ya fue tomado, elige otro.');
                }

                $total = (float) ($service->price ?? 0);

                Appointment::create([
                    'student_user_id' => $studentId,
                    'advisor_id' => $data['advisor_id'],
                    'service_id' => $data['service_id'],
                    'session_number' => $data['session_number'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],

                    // ✅ recomendado: nuevo estado por defecto = pending
                    'status' => 'pending',

                    // pago
                    'service_total' => $total > 0 ? $total : null,
                    'paid_total' => 0,
                    'payment_status' => 'pending',
                    'balance' => $total > 0 ? $total : 0,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('student.agenda')->with('success', 'Sesión reservada correctamente.');
    }

    // ✅ FEED FullCalendar (sin Carbon) usando duración real por sesión
    public function feed(Request $request)
    {
        $serviceId = (int) $request->query('service_id');
        $advisorId = (int) $request->query('advisor_id');
        $sessionNumber = (int) $request->query('session_number');

        $start = (string) $request->query('start'); // ISO
        $end   = (string) $request->query('end');   // ISO (fin-exclusivo)

        if (!$serviceId || !$advisorId || !$sessionNumber || !$start || !$end) {
            return response()->json([]);
        }

        $minutes = (int) (ServiceSession::query()
            ->where('service_id', $serviceId)
            ->where('session_number', $sessionNumber)
            ->value('duration_minutes') ?? 60);

        if ($minutes < 15 || $minutes > 480) {
            return response()->json(['message' => 'Duración de sesión inválida'], 422);
        }

        $startDate = (new DateTimeImmutable($start, $this->tz()))->setTime(0,0,0);
        $endDate   = (new DateTimeImmutable($end,   $this->tz()))->setTime(0,0,0);
        $nowTs     = $this->now()->getTimestamp();

        $availabilities = DB::table('advisor_availabilities')
            ->where('advisor_id', $advisorId)
            ->where('is_active', 1)
            ->get(['weekday','start_time','end_time','slot_minutes'])
            ->groupBy('weekday');

        // 👇 Si tú creas status=pending, aquí debes incluirlo para que se vea ocupado
        $appointments = Appointment::query()
            ->where('advisor_id', $advisorId)
            ->whereIn('status', ['pending','reserved','completed'])
            ->where('starts_at', '<', $endDate->format('Y-m-d H:i:s'))
            ->where('ends_at',   '>', $startDate->format('Y-m-d H:i:s'))
            ->get(['starts_at','ends_at','status']);

        $busyIntervals = $appointments->map(fn($a) => [
            'start' => strtotime($a->starts_at),
            'end'   => strtotime($a->ends_at),
        ]);

        $events = AdvisorEvent::query()
            ->where('advisor_id', $advisorId)
            ->where('is_active', 1)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $endDate->format('Y-m-d H:i:s'))
            ->where('end_at',   '>', $startDate->format('Y-m-d H:i:s'))
            ->get(['start_at','end_at','title','type','visibility']);

        $eventIntervals = $events->map(function ($e) {
            $reason = ($e->visibility === 'public')
                ? (strtoupper((string)$e->type).': '.(string)$e->title)
                : 'Ocupado';

            return [
                'start' => strtotime($e->start_at),
                'end'   => strtotime($e->end_at),
                'reason'=> $reason,
            ];
        });

        $calendar = [];

        $period = new DatePeriod($startDate, new DateInterval('P1D'), $endDate); // fin-exclusivo
        foreach ($period as $day) {
            $weekday = (int) $day->format('w'); // 0..6
            $ranges = $availabilities->get($weekday, collect());
            if ($ranges->isEmpty()) continue;

            foreach ($ranges as $r) {
                $slotMinutes = (int) $r->slot_minutes;

                $rangeStart = new DateTimeImmutable($day->format('Y-m-d').' '.$r->start_time, $this->tz());
                $rangeEnd   = new DateTimeImmutable($day->format('Y-m-d').' '.$r->end_time,   $this->tz());

                for ($t = $rangeStart; $t < $rangeEnd; $t = $t->add(new DateInterval('PT'.$slotMinutes.'M'))) {
                    $slotStart = $t;
                    $slotEnd   = $t->add(new DateInterval('PT'.$minutes.'M'));

                    if ($slotEnd > $rangeEnd) continue;
                    if ($slotStart->getTimestamp() <= $nowTs) continue;

                    $ss = $slotStart->getTimestamp();
                    $ee = $slotEnd->getTimestamp();

                    $isReserved = $busyIntervals->contains(fn($b) => $ss < $b['end'] && $ee > $b['start']);
                    $eventHit   = $eventIntervals->first(fn($e) => $ss < $e['end'] && $ee > $e['start']);

                    if ($isReserved) {
                        $calendar[] = [
                            'title' => 'Reservado',
                            'start' => $slotStart->format(DATE_ATOM),
                            'end'   => $slotEnd->format(DATE_ATOM),
                            'display' => 'block',
                            'extendedProps' => [
                                'kind' => 'reserved',
                                'blocked' => true,
                                'reason' => 'Reservado',
                            ],
                        ];
                        continue;
                    }

                    if ($eventHit) {
                        $calendar[] = [
                            'title' => $eventHit['reason'],
                            'start' => $slotStart->format(DATE_ATOM),
                            'end'   => $slotEnd->format(DATE_ATOM),
                            'display' => 'block',
                            'extendedProps' => [
                                'kind' => 'event',
                                'blocked' => true,
                                'reason' => $eventHit['reason'],
                            ],
                        ];
                        continue;
                    }

                    $calendar[] = [
                        'title' => 'Disponible',
                        'start' => $slotStart->format(DATE_ATOM),
                        'end'   => $slotEnd->format(DATE_ATOM),
                        'display' => 'block',
                        'extendedProps' => [
                            'kind' => 'available',
                            'blocked' => false,
                            'starts_at' => $slotStart->format('Y-m-d H:i:s'),
                            'ends_at'   => $slotEnd->format('Y-m-d H:i:s'),
                        ],
                    ];
                }
            }
        }

        return response()->json($calendar);
    }
}