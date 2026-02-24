<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdvisorEvent;
use App\Models\Appointment;
use App\Models\AppointmentPayment;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Request;

class AdminAppointmentCalendarController extends Controller
{
    private function tz(): DateTimeZone
    {
        return new DateTimeZone(config('app.timezone', 'America/Lima'));
    }

    private function iso(DateTimeImmutable $dt): string
    {
        return $dt->format(DATE_ATOM);
    }

    private function toDt(string $value): DateTimeImmutable
    {
        // FullCalendar manda ISO con -05:00 o Z
        return new DateTimeImmutable($value, $this->tz());
    }

    public function index()
    {
        return view('admin.appointments.calendar');
    }

    public function feed(Request $request)
    {
        $start = (string) $request->query('start');
        $end   = (string) $request->query('end'); // fin-exclusivo

        if (!$start || !$end) {
            return response()->json([]);
        }

        $startDt = $this->toDt($start);
        $endDt   = $this->toDt($end);

        $startSql = $startDt->format('Y-m-d H:i:s');
        $endSql   = $endDt->format('Y-m-d H:i:s');

        // =========================
        // 1) CITAS (appointments)
        // =========================
        $appointments = Appointment::query()
            ->with([
                'service:id,title,price',
                'advisor:id,name',
                'student:id,name,email'
            ])
            ->where('starts_at', '<', $endSql)
            ->where('ends_at',   '>', $startSql)
            ->whereIn('status', ['pending','reserved','completed','cancelled'])
            ->orderBy('starts_at')
            ->get();

        $apptEvents = $appointments->map(function ($a) {
            $serviceTitle = $a->service?->title ?? 'Servicio';
            $advisorName  = $a->advisor?->name ?? 'Asesor';
            $studentName  = $a->student?->name ?? 'Estudiante';
            $sessionLabel = 'Sesión '.((int)$a->session_number);

            $sessionStatus = match ((string)$a->status) {
                'completed' => 'Realizado',
                'cancelled' => 'Cancelado',
                default     => 'Pendiente',
            };

            $payStatus = match ((string)($a->payment_status ?? 'pending')) {
                'paid'    => 'Pagado',
                'partial' => 'Parcial',
                default   => 'Pendiente',
            };

            // Colores (profesional)
            // Pendiente = ámbar, Realizado = verde, Cancelado = gris
            $bg = '#FEF9C3'; $bd = '#EAB308'; $tx = '#713F12';
            if ((string)$a->status === 'completed') { $bg = '#DCFCE7'; $bd = '#22C55E'; $tx = '#14532D'; }
            if ((string)$a->status === 'cancelled') { $bg = '#E5E7EB'; $bd = '#9CA3AF'; $tx = '#111827'; }

            $s = new DateTimeImmutable((string)$a->starts_at, $this->tz());
            $e = new DateTimeImmutable((string)$a->ends_at,   $this->tz());

            return [
                'id' => 'appt-'.$a->id,
                'title' => "{$serviceTitle} • {$sessionLabel}\n{$studentName} • {$advisorName}\n{$sessionStatus} | Pago: {$payStatus}",
                'start' => $this->iso($s),
                'end'   => $this->iso($e),
                'display' => 'block',
                'backgroundColor' => $bg,
                'borderColor' => $bd,
                'textColor' => $tx,
                'extendedProps' => [
                    'kind' => 'appointment',
                    'appointment_id' => (int) $a->id,

                    'service' => $serviceTitle,
                    'session_number' => (int) $a->session_number,
                    'advisor' => $advisorName,
                    'student' => $studentName,
                    'student_email' => (string) ($a->student?->email ?? ''),

                    'status' => (string) $a->status,
                    'payment_status' => (string) ($a->payment_status ?? 'pending'),

                    'service_total' => (float) ($a->service_total ?? 0),
                    'paid_total' => (float) ($a->paid_total ?? 0),
                    'balance' => (float) ($a->balance ?? 0),
                    'payment_notes' => (string) ($a->payment_notes ?? ''),

                    'starts_at' => $s->format('Y-m-d H:i:s'),
                    'ends_at'   => $e->format('Y-m-d H:i:s'),
                ],
            ];
        });

        // =========================
        // 2) EVENTOS (advisor_events)
        // =========================
        $events = AdvisorEvent::query()
            ->with(['advisor:id,name'])
            ->where('is_active', 1)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $endSql)
            ->where('end_at',   '>', $startSql)
            ->orderBy('start_at')
            ->get();

        $eventEvents = $events->map(function ($ev) {
            $advisorName = $ev->advisor?->name ?? 'Asesor';

            // En admin sí mostramos el detalle completo.
            $title = strtoupper((string)$ev->type).': '.(string)$ev->title.' • '.$advisorName;

            $s = new DateTimeImmutable((string)$ev->start_at, $this->tz());
            $e = new DateTimeImmutable((string)$ev->end_at,   $this->tz());

            return [
                'id' => 'evt-'.$ev->id,
                'title' => $title,
                'start' => $this->iso($s),
                'end'   => $this->iso($e),
                'display' => 'block',
                'backgroundColor' => '#DBEAFE',
                'borderColor' => '#3B82F6',
                'textColor' => '#1E3A8A',
                'extendedProps' => [
                    'kind' => 'advisor_event',
                    'event_id' => (int) $ev->id,
                    'advisor' => $advisorName,
                    'type' => (string) $ev->type,
                    'title_text' => (string) $ev->title,
                    'visibility' => (string) $ev->visibility,
                    'starts_at' => $s->format('Y-m-d H:i:s'),
                    'ends_at'   => $e->format('Y-m-d H:i:s'),
                ],
            ];
        });

        // Unimos ambos
        return response()->json($apptEvents->values()->merge($eventEvents->values())->values());
    }

    // ✅ historial pagos
    public function payments(Appointment $appointment)
    {
        $items = AppointmentPayment::query()
            ->where('appointment_id', $appointment->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->get(['id','amount','method','notes','paid_at','created_at']);

        return response()->json($items);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,reserved,completed,cancelled'],
        ]);

        $appointment->update([
            'status' => $data['status'],
        ]);

        return response()->json(['ok' => true]);
    }

    public function updatePayment(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'service_total' => ['nullable', 'numeric', 'min:0'],
            'add_payment' => ['nullable', 'numeric', 'min:0'],
            'method' => ['nullable', 'string', 'max:50'],
            'payment_notes' => ['nullable', 'string'],
        ]);

        $total = (float)($data['service_total'] ?? $appointment->service_total ?? 0);
        $add   = (float)($data['add_payment'] ?? 0);

        $paid = (float)($appointment->paid_total ?? 0) + $add;
        if ($paid < 0) $paid = 0;
        if ($total > 0 && $paid > $total) $paid = $total;

        $balance = $total > 0 ? max($total - $paid, 0) : 0;

        $paymentStatus =
            ($total > 0 && $paid >= $total) ? 'paid'
            : (($paid > 0) ? 'partial' : 'pending');

        // guarda appointment
        $appointment->update([
            'service_total' => $total > 0 ? $total : null,
            'paid_total' => $paid,
            'balance' => $balance,
            'payment_status' => $paymentStatus,
            'payment_notes' => $data['payment_notes'] ?? $appointment->payment_notes,
        ]);

        // guarda historial si hubo pago
        if ($add > 0) {
            AppointmentPayment::create([
                'appointment_id' => $appointment->id,
                'amount' => $add,
                'method' => $data['method'] ?? null,
                'notes' => $data['payment_notes'] ?? null,
                'created_by' => (int) $request->user()->id,
                'paid_at' => now(),
            ]);
        }

        return response()->json(['ok' => true]);
    }
}