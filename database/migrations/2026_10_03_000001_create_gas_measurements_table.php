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
        if (!Schema::hasTable('gas_measurements')) {
            Schema::create('gas_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->string('point_number', 50)->default('01');
                $table->date('measurement_date');
                $table->string('measurement_time', 20)->nullable();

                // Datos de Identificación y Área
                $table->string('area')->nullable();
                $table->string('workstation')->nullable();
                $table->string('measurement_point')->nullable();
                $table->string('activity_description')->nullable();

                // Condiciones Ambientales / Meteorológicas
                $table->decimal('temperatura', 8, 2)->nullable(); // °C
                $table->decimal('presion_atm', 8, 2)->nullable();  // mmHg
                $table->decimal('vel_aire', 8, 2)->nullable();     // Km/h

                // Lecturas Analíticas de Gases (JSON Estructurado)
                $table->json('selected_gases')->nullable();
                $table->json('gases_readings')->nullable();

                // Columnas de Respaldo Individual para los 13 Gases Principales
                $table->json('o2_values')->nullable();
                $table->decimal('o2_prom', 10, 3)->nullable();

                $table->json('h2s_values')->nullable();
                $table->decimal('h2s_prom', 10, 3)->nullable();

                $table->json('co_values')->nullable();
                $table->decimal('co_prom', 10, 3)->nullable();

                $table->json('lel_values')->nullable();
                $table->decimal('lel_prom', 10, 3)->nullable();

                $table->json('hcho_values')->nullable();
                $table->decimal('hcho_prom', 10, 3)->nullable();

                $table->json('tvoc_values')->nullable();
                $table->decimal('tvoc_prom', 10, 3)->nullable();

                $table->json('co2_values')->nullable();
                $table->decimal('co2_prom', 10, 3)->nullable();

                $table->json('as_values')->nullable();
                $table->decimal('as_prom', 10, 3)->nullable();

                $table->json('so2_values')->nullable();
                $table->decimal('so2_prom', 10, 3)->nullable();

                $table->json('nh3_values')->nullable();
                $table->decimal('nh3_prom', 10, 3)->nullable();

                $table->json('cl2_values')->nullable();
                $table->decimal('cl2_prom', 10, 3)->nullable();

                $table->json('tcov_values')->nullable();
                $table->decimal('tcov_prom', 10, 3)->nullable();

                $table->json('no2_values')->nullable();
                $table->decimal('no2_prom', 10, 3)->nullable();

                // Ubicación Geográfica y Coordenadas UTM / WGS84
                $table->string('location')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('utm_zone', 20)->nullable();
                $table->decimal('utm_easting', 12, 3)->nullable();
                $table->decimal('utm_northing', 12, 3)->nullable();

                // Evidencias Fotográficas, Observaciones y Personal
                $table->string('image_path')->nullable();
                $table->json('images')->nullable();
                $table->text('observations')->nullable();
                $table->string('registered_by')->nullable();
                $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
                $table->string('local_uuid', 100)->nullable()->index();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gas_measurements');
    }
};
