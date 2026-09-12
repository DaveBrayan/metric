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
        if (!Schema::hasTable('dosimetry_measurements')) {
            Schema::create('dosimetry_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->string('point_number')->default('01');
                $table->date('measurement_date');
                $table->string('measurement_time', 20)->nullable();
                
                // Puesto y Parámetros de Ruido
                $table->string('area')->nullable();
                $table->string('punto_medicion')->nullable();
                $table->string('tipo_ruido')->default('Fluctuante'); // Estable, Fluctuante, Estable escalonado, Impacto
                $table->decimal('tiempo_expos_h', 8, 2)->default(8.0); // TPE (Hr)
                $table->string('ponderacion', 10)->default('A'); // A, C
                $table->string('respuesta', 20)->default('Lento'); // Rápido, Lento
                
                // Mediciones Acústicas (dB)
                $table->decimal('duracion_medicion_h', 8, 2)->nullable(); // TIEMPO DE MEDICIÓN (Hr)
                $table->decimal('nps_max_db', 8, 2)->nullable(); // NPS MAX (dB)
                $table->decimal('nps_min_db', 8, 2)->nullable(); // NPS MIN (dB)
                $table->decimal('leq_t_db', 8, 2)->nullable(); // Leq,T (dB)
                $table->decimal('dosis_pct', 8, 2)->nullable(); // % Dosis calculada
                $table->string('cumple', 30)->nullable(); // CUMPLE, NO CUMPLE
                
                // Ubicación y Coordenadas UTM
                $table->string('location')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->string('utm_zone', 20)->nullable();
                $table->decimal('utm_easting', 12, 3)->nullable();
                $table->decimal('utm_northing', 12, 3)->nullable();
                
                // Fotos, Observaciones y Personal
                $table->string('image_path')->nullable();
                $table->json('images')->nullable();
                $table->text('observations')->nullable();
                $table->string('registered_by')->nullable();
                $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
                $table->string('local_uuid')->nullable();
                
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosimetry_measurements');
    }
};
