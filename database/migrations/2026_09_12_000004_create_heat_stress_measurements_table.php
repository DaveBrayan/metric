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
        Schema::create('heat_stress_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
            $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null');

            $table->string('point_number', 50)->nullable();
            $table->date('measurement_date')->nullable();
            $table->string('measurement_time', 50)->nullable();

            // Puesto y Actividad
            $table->string('area')->nullable();
            $table->string('puesto_trabajo')->nullable();
            $table->text('desc_actividades')->nullable();

            // Entorno y Aclimatación
            $table->string('interior_exterior')->nullable()->default('Interior'); // 'Interior' | 'Exterior'
            $table->string('aclimatado', 10)->nullable()->default('Sí');          // 'Sí' | 'No'
            $table->string('tipo_ropa_cav')->nullable();
            $table->decimal('cav_ajuste_db', 8, 2)->nullable()->default(0.0);
            $table->string('capucha', 10)->nullable()->default('No');
            $table->string('tasa_metabolica')->nullable();

            // Parámetros Térmicos
            $table->decimal('temp_c', 8, 2)->nullable();
            $table->decimal('hr_percent', 8, 2)->nullable();
            $table->decimal('vel_viento_ms', 8, 2)->nullable();
            $table->decimal('presion_mmhg', 8, 2)->nullable();
            $table->decimal('wb_c', 8, 2)->nullable();
            $table->decimal('gt_c', 8, 2)->nullable();
            $table->decimal('wbgt_c', 8, 2)->nullable();
            $table->decimal('wbgt_efectivo_c', 8, 2)->nullable();
            $table->decimal('limite_wbgt_lmp', 8, 2)->nullable();
            $table->string('regimen_trabajo_descanso', 100)->nullable();
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heat_stress_measurements');
    }
};
