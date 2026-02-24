<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->cascadeOnDelete();
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('method', 50)->nullable(); // efectivo, yape, etc
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // user admin
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['appointment_id', 'paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_payments');
    }
};