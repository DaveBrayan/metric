<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('photographic_inspections')) {
            Schema::create('photographic_inspections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->string('point_number')->default('01'); // N° de punto
                $table->date('inspection_date'); // Fecha
                $table->string('inspection_time', 20)->nullable(); // Hora (ej: 10:30)
                $table->string('area'); // Área o sector general
                $table->text('observation'); // Observación principal del punto
                $table->text('description')->nullable(); // Descripción opcional
                $table->string('location')->nullable(); // Texto de ubicación o coordenadas
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('utm_zone', 10)->nullable();
                $table->decimal('utm_easting', 12, 3)->nullable();
                $table->decimal('utm_northing', 12, 3)->nullable();
                $table->decimal('gps_accuracy', 6, 2)->nullable();
                $table->json('images')->nullable(); // Hasta 3 fotos por punto
                $table->string('image_path')->nullable(); // Foto principal
                $table->string('registered_by')->nullable(); // Nombre del técnico
                $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photographic_inspections');
    }
};
