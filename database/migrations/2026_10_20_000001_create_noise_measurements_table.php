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
        Schema::create('noise_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('measurement_modules')->cascadeOnDelete();
            
            // Campos básicos
            $table->string('point_number')->default('P-01');
            $table->date('measurement_date')->nullable();
            $table->time('measurement_time')->nullable();
            
            // Ubicación de la medición
            $table->string('area')->nullable();
            $table->string('maquinaria_equipo')->nullable();
            $table->string('actividad')->nullable();
            $table->string('descripcion_actividad')->nullable();
            
            // Tipo y características del ruido
            $table->string('tipo_ruido')->nullable(); // Estable, Fluctuante, Estable escalonado, Impacto
            $table->string('punto_medicion')->nullable();
            $table->decimal('tiempo_expos_h', 8, 2)->default(8.0);
            
            // Parámetros de medición
            $table->string('ponderacion')->default('A'); // A o C
            $table->string('respuesta')->default('Lento'); // Lento o Rápido
            
            // Mediciones y resultados
            $table->json('mediciones_db')->nullable(); // Array de valores en dB
            $table->decimal('leq_t_db', 8, 2)->nullable(); // LEQ equivalente
            
            // Protección personal
            $table->boolean('uso_protectores')->default(false);
            $table->string('tipo_protector')->nullable();
            $table->decimal('nrr_db', 8, 2)->nullable(); // Noise Reduction Rating
            
            // Observaciones y documentación
            $table->text('observations')->nullable();
            $table->json('images')->nullable(); // URLs o paths de imágenes
            $table->string('image_path')->nullable(); // Primera imagen principal
            
            // Ubicación geográfica
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('utm_zone')->nullable();
            $table->decimal('utm_easting', 12, 3)->nullable();
            $table->decimal('utm_northing', 12, 3)->nullable();
            
            // Registrador
            $table->string('registered_by')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->cascadeOnDelete();
            
            // Compliance / Cumplimiento
            $table->boolean('cumple')->default(true);
            $table->string('local_uuid')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Índices
            $table->index('module_id');
            $table->index('measurement_date');
            $table->index('punto_medicion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noise_measurements');
    }
};