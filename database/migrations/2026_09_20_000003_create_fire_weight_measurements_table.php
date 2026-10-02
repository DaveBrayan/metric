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
        Schema::create('fire_weight_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('point_number', 50)->nullable();
            $table->date('measurement_date')->nullable();
            $table->string('measurement_time', 20)->nullable();

            // Identificación del Sector
            $table->string('macroarea')->nullable();
            $table->string('sector_name')->nullable();

            // Dimensiones
            $table->json('dimensions')->nullable();
            $table->decimal('yi_largo', 10, 2)->nullable();
            $table->decimal('xi_ancho', 10, 2)->nullable();
            $table->decimal('area_m2', 10, 2)->nullable();

            // Materiales combustibles registrados y Equipos contra incendios
            $table->json('materials')->nullable();
            $table->json('fire_equipments')->nullable();

            // Resultados calculados de Carga de Fuego
            $table->decimal('qs_mj_m2', 12, 2)->default(0);
            $table->decimal('qs_mcal_m2', 12, 2)->default(0);
            $table->string('risk_level', 50)->default('Bajo');
            $table->string('risk_color', 30)->default('emerald');
            $table->decimal('ra_value', 8, 2)->nullable();

            // Evidencias y Ubicación
            $table->string('image_path')->nullable();
            $table->json('images')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('utm_zone', 20)->nullable();
            $table->decimal('utm_easting', 12, 3)->nullable();
            $table->decimal('utm_northing', 12, 3)->nullable();

            // Personal
            $table->string('registered_by')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fire_weight_measurements');
    }
};
