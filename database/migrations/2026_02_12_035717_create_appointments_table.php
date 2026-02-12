<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('appointments', function (Blueprint $table) {
      $table->id();

      $table->foreignId('student_user_id')->constrained('users')->cascadeOnDelete();
      $table->foreignId('advisor_id')->constrained('advisors')->cascadeOnDelete();
      $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();

      $table->unsignedSmallInteger('session_number'); // 1..sessions_count
      $table->dateTime('starts_at');
      $table->dateTime('ends_at');

      $table->string('status')->default('reserved'); // reserved/cancelled/completed
      $table->timestamps();

      // evita choque exacto (mínimo)
      $table->index(['advisor_id', 'starts_at']);
      $table->index(['student_user_id', 'starts_at']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('appointments');
  }
};
