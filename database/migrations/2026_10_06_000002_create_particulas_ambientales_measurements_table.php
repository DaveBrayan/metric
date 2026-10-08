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
        if (!Schema::hasTable('particulas_ambientales_measurements')) {
            Schema::create('particulas_ambientales_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->string('point_number', 50)->default('01');
                $table->date('measurement_date')->nullable();
                $table->string('measurement_time', 50)->nullable();

                // Identificación de la estación
                $table->string('area')->nullable();
                $table->string('punto_medicion')->nullable();
                $table->string('workstation')->nullable();

                // Fechas y Horarios de Muestreo Continuo
                $table->date('fecha_inicio')->nullable();
                $table->string('hora_inicio', 20)->nullable();
                $table->date('fecha_fin')->nullable();
                $table->string('hora_fin', 20)->nullable();
                $table->decimal('diferencia_horas', 8, 2)->nullable(); // Horas totales de muestreo (ej: 24.00)

                // Condiciones Meteorológicas
                $table->decimal('temp_max', 8, 2)->nullable(); // °C
                $table->decimal('temp_min', 8, 2)->nullable(); // °C
                $table->decimal('temperatura', 8, 2)->nullable(); // Temp promedio °C
                $table->decimal('presion_atm', 8, 2)->nullable(); // mmHg
                $table->decimal('vel_viento', 8, 2)->nullable(); // Km/h
                $table->string('dir_viento', 20)->nullable(); // N, NE, E, SE, S, SW, W, NW, etc.
                $table->decimal('hr_percent', 8, 2)->nullable(); // %

                // Fracción PM-10 Gravimétrica
                $table->decimal('pm10_filtro_inicial', 10, 4)->nullable(); // gr
                $table->decimal('pm10_filtro_final', 10, 4)->nullable(); // gr
                $table->decimal('pm10_prom', 10, 3)->nullable(); // Concentración µg/m³
                $table->json('pm10_values')->nullable();

                // Fracción PST (Partículas Suspendidas Totales) Gravimétrica
                $table->decimal('pst_filtro_inicial', 10, 4)->nullable(); // gr
                $table->decimal('pst_filtro_final', 10, 4)->nullable(); // gr
                $table->decimal('pst_prom', 10, 3)->nullable(); // Concentración µg/m³
                $table->json('pts_values')->nullable();

                // Fracción PM2.5 (si aplica)
                $table->decimal('pm25_filtro_inicial', 10, 4)->nullable(); // gr
                $table->decimal('pm25_filtro_final', 10, 4)->nullable(); // gr
                $table->decimal('pm25_prom', 10, 3)->nullable(); // Concentración µg/m³
                $table->json('pm25_values')->nullable();

                // Caudal y Volumen
                $table->decimal('caudal', 10, 2)->nullable(); // L/min

                // Ubicación Geográfica y Coordenadas UTM
                $table->string('location')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('utm_zone', 20)->nullable();
                $table->decimal('utm_easting', 12, 3)->nullable();
                $table->decimal('utm_northing', 12, 3)->nullable();

                // Evidencias Fotográficas, Observaciones y Personal
                $table->string('image_path')->nullable();
                $table->json('images')->nullable();
                $table->json('image_urls')->nullable();
                $table->text('observations')->nullable();
                $table->string('registered_by')->nullable();
                $table->string('created_by')->nullable();
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
        Schema::dropIfExists('particulas_ambientales_measurements');
    }
};
