<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Estado de sesión (tu "reserved" será Pendiente, "completed" Realizado)
            // Si ya tienes status, NO lo tocamos, solo lo usamos.

            // Pago
            $table->decimal('service_total', 10, 2)->nullable()->after('status'); // total del servicio (paquete)
            $table->decimal('paid_total', 10, 2)->default(0)->after('service_total'); // pagado acumulado
            $table->enum('payment_status', ['pending','partial','paid'])->default('pending')->after('paid_total');
            $table->decimal('balance', 10, 2)->default(0)->after('payment_status'); // saldo = total - pagado
            $table->timestamp('last_payment_at')->nullable()->after('balance');
            $table->text('payment_notes')->nullable()->after('last_payment_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'service_total',
                'paid_total',
                'payment_status',
                'balance',
                'last_payment_at',
                'payment_notes',
            ]);
        });
    }
};