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
        if (!Schema::hasTable('opacity_measurements')) {
            Schema::create('opacity_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
                $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null');

                $table->string('point_number', 50)->nullable();
                $table->date('measurement_date')->nullable();
                $table->string('measurement_time', 50)->nullable();

                // Card 1: Datos del Vehículo
                $table->string('tipo_vehiculo')->nullable();
                $table->string('marca')->nullable();
                $table->string('modelo')->nullable();
                $table->string('placa')->nullable();

                // Card 2: Mediciones de Opacidad y Motor
                $table->decimal('temp_c', 8, 2)->nullable();
                $table->decimal('opa_1', 8, 2)->nullable();
                $table->decimal('opa_2', 8, 2)->nullable();
                $table->decimal('opa_3', 8, 2)->nullable();
                $table->decimal('rpm_1', 8, 2)->nullable();
                $table->decimal('rpm_2', 8, 2)->nullable();
                $table->decimal('rpm_3', 8, 2)->nullable();

                // Promedios y evaluación
                $table->decimal('opa_promedio', 8, 2)->nullable();
                $table->decimal('rpm_promedio', 8, 2)->nullable();
                $table->decimal('limite_normativa', 8, 2)->nullable()->default(50.0);
                $table->boolean('is_compliant')->default(true);

                // Georreferenciación & Satelital
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('utm_zone', 50)->nullable()->default('19K');
                $table->decimal('utm_easting', 12, 2)->nullable();
                $table->decimal('utm_northing', 12, 2)->nullable();
                $table->string('location_description')->nullable();

                // Observaciones y Fotos
                $table->text('observations')->nullable();
                $table->json('photo_paths')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opacity_measurements');
    }
};
