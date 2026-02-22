<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->unsignedInteger('session_number'); // 1..N
            $table->unsignedInteger('duration_minutes'); // 45,60,120...
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['service_id', 'session_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_sessions');
    }
};
