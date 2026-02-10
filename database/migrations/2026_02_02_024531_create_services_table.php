<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();

            // Datos principales
            $table->string('title', 150);
            $table->text('description')->nullable();

            // Detalle (listas) -> JSON
            $table->json('includes')->nullable();   // lo que implica / incluye
            $table->json('objectives')->nullable(); // objetivos

            // Info de negocio
            $table->unsignedInteger('sessions_count')->nullable(); // número de sesiones
            $table->decimal('price', 10, 2)->nullable();           // costo

            // Imágenes (2 referenciales)
            $table->string('image_1_path')->nullable();
            $table->string('image_2_path')->nullable();

            // Orden / estado (misma lógica que banners)
            $table->unsignedInteger('position')->default(1);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
