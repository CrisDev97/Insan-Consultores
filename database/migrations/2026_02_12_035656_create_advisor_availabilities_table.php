<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('advisor_availabilities', function (Blueprint $table) {
      $table->id();
      $table->foreignId('advisor_id')->constrained('advisors')->cascadeOnDelete();
      $table->unsignedTinyInteger('weekday'); // 0=Dom,1=Lun...6=Sab
      $table->time('start_time');
      $table->time('end_time');
      $table->unsignedSmallInteger('slot_minutes')->default(60);
      $table->boolean('is_active')->default(true);
      $table->timestamps();
    });
  }
  public function down(): void {
    Schema::dropIfExists('advisor_availabilities');
  }
};
