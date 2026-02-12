<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advisor_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advisor_id')->constrained('advisors')->cascadeOnDelete();

            $table->string('title', 255);
            $table->string('type', 50)->default('otro'); // taller, ponencia, colegio, reunion, bloqueo, vacaciones
            $table->dateTime('start_at');
            $table->dateTime('end_at');

            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();

            $table->string('visibility', 20)->default('private'); // private/public
            $table->string('status', 20)->default('confirmed'); // confirmed/tentative/cancelled
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['advisor_id', 'start_at', 'end_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advisor_events');
    }
};