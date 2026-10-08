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
        if (!Schema::hasTable('particulas_measurements')) {
            Schema::create('particulas_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->string('point_number', 50)->default('01');
                $table->date('measurement_date');
                $table->string('measurement_time', 20)->nullable();

                // Datos de Identificación y Área
                $table->string('area')->nullable();
                $table->string('workstation')->nullable();
                $table->string('punto_medicion')->nullable();

                // Condiciones Ambientales / Meteorológicas
                $table->decimal('temperatura', 8, 2)->nullable(); // °C
                $table->decimal('hr_percent', 8, 2)->nullable();  // %

                // Fracciones de Material Particulado
                $table->json('pm10_values')->nullable();
                $table->decimal('pm10_prom', 10, 3)->nullable(); // µg/m³
                $table->json('pm25_values')->nullable();
                $table->decimal('pm25_prom', 10, 3)->nullable(); // µg/m³
                $table->json('pts_values')->nullable();
                $table->decimal('pts_prom', 10, 3)->nullable();  // µg/m³

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
        Schema::dropIfExists('particulas_measurements');
    }
};
