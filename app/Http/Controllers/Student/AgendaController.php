<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Advisor;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
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

                if (!$overlaps) {
                    $slots[] = [
                        'starts_at' => $slotStart->toDateTimeString(),
                        'ends_at' => $slotEnd->toDateTimeString(),
                        'label' => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    ];
                }
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
}