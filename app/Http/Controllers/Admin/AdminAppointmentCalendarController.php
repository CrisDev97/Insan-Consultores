<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
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
        // ISO8601 con zona (FullCalendar lo lee perfecto)
        return $dt->format(DATE_ATOM);
    }

    private function toDt(string $value): DateTimeImmutable
    {
        // FullCalendar manda ISO como: 2026-02-22T00:00:00-05:00 o con Z
        // DateTimeImmutable lo parsea bien si le pasas timezone
        return new DateTimeImmutable($value, $this->tz());
    }

    public function index()
    {
        return view('admin.appointments.calendar');
    }

    public function feed(Request $request)
    {
        $start = (string) $request->query('start'); // ISO
        $end   = (string) $request->query('end');   // ISO (fin-exclusivo)

        if (!$start || !$end) {
            return response()->json([]);
        }

        $startDt = $this->toDt($start);
        $endDt   = $this->toDt($end);

        // Importante: incluye pending para que se vea en admin
        $items = Appointment::query()
            ->with([
                'service:id,title,price',
                'advisor:id,name',
                'student:id,name,email'
            ])
            ->where('starts_at', '<', $endDt->format('Y-m-d H:i:s'))
            ->where('ends_at',   '>', $startDt->format('Y-m-d H:i:s'))
            ->whereIn('status', ['pending','reserved','completed','cancelled'])
            ->orderBy('starts_at')
            ->get();

        $events = $items->map(function ($a) {
            $serviceTitle = $a->service?->title ?? 'Servicio';
            $advisorName  = $a->advisor?->name ?? 'Asesor';
            $studentName  = $a->student?->name ?? 'Estudiante';
            $sessionLabel = 'Sesión '.((int)$a->session_number);

            // Estado sesión (tu lógica)
            $sessionStatus = match ($a->status) {
                'completed' => 'Realizado',
                'cancelled' => 'Cancelado',
                default     => 'Pendiente',
            };

            // Estado pago
            $payStatus = match ($a->payment_status) {
                'paid'    => 'Pagado',
                'partial' => 'Parcial',
                default   => 'Pendiente',
            };

            // Colores
            // Pendiente = ámbar, Realizado = verde, Cancelado = gris
            $bg = '#FEF9C3'; $bd = '#EAB308'; $tx = '#713F12';
            if ($a->status === 'completed') { $bg = '#DCFCE7'; $bd = '#22C55E'; $tx = '#14532D'; }
            if ($a->status === 'cancelled') { $bg = '#E5E7EB'; $bd = '#9CA3AF'; $tx = '#111827'; }

            // starts_at / ends_at pueden ser string en tu modelo (sin casts)
            $s = new DateTimeImmutable((string)$a->starts_at, $this->tz());
            $e = new DateTimeImmutable((string)$a->ends_at,   $this->tz());

            return [
                'id' => $a->id,
                'title' => "{$serviceTitle} • {$sessionLabel}\n{$studentName} • {$advisorName}\n{$sessionStatus} | Pago: {$payStatus}",
                'start' => $this->iso($s),
                'end'   => $this->iso($e),
                'display' => 'block',
                'backgroundColor' => $bg,
                'borderColor' => $bd,
                'textColor' => $tx,
                'extendedProps' => [
                    'service' => $serviceTitle,
                    'session_number' => (int) $a->session_number,
                    'advisor' => $advisorName,
                    'student' => $studentName,
                    'student_email' => $a->student?->email,
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

        return response()->json($events);
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

        $appointment->update([
            'service_total' => $total > 0 ? $total : null,
            'paid_total' => $paid,
            'balance' => $balance,
            'payment_status' => $paymentStatus,
            'payment_notes' => $data['payment_notes'] ?? $appointment->payment_notes,
            // si tu columna existe; si no, quita estas 2 líneas
            'last_payment_at' => $add > 0 ? now() : $appointment->last_payment_at,
        ]);

        return response()->json(['ok' => true]);
    }
}