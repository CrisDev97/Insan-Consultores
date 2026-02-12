<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('advisor_service', function (Blueprint $table) {
      $table->id();
      $table->foreignId('advisor_id')->constrained('advisors')->cascadeOnDelete();
      $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
      $table->unsignedSmallInteger('duration_minutes')->default(60); // duración sesión
      $table->boolean('is_active')->default(true);
      $table->timestamps();

      $table->unique(['advisor_id', 'service_id']);
    });
  }
  public function down(): void {
    Schema::dropIfExists('advisor_service');
  }
};
