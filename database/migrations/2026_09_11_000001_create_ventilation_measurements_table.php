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
        if (!Schema::hasTable('ventilation_measurements')) {
            Schema::create('ventilation_measurements', function (Blueprint $table) {
                $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('point_number')->default('01');
            $table->date('measurement_date');
            $table->string('measurement_time', 20)->nullable();
            
            // Datos de Identificación
            $table->string('local_trabajo')->nullable();
            $table->string('tipo_local')->nullable();
            $table->string('tipo_ventilacion')->default('Natural'); // Natural, Mecánica
            $table->string('elemento_ventilacion')->nullable(); // Puerta, Ventana, Rejas, Ventilador circular, Extractor circular
            $table->decimal('temperatura_seca_c', 8, 2)->nullable();
            $table->decimal('vel_aire_ms', 8, 2)->nullable();
            $table->decimal('vel_aire_mh', 10, 2)->nullable();
            
            // Área de Ventilaciones
            $table->decimal('area_largo_m', 8, 2)->nullable();
            $table->decimal('area_ancho_m', 8, 2)->nullable();
            $table->decimal('area_diametro_m', 8, 2)->nullable();
            $table->decimal('area_ventilacion_m2', 10, 4)->nullable();
            
            // Caudal y Volumen
            $table->decimal('caudal_m3h', 12, 2)->nullable();
            $table->decimal('vol_largo_m', 8, 2)->nullable();
            $table->decimal('vol_ancho_m', 8, 2)->nullable();
            $table->decimal('vol_alto_m', 8, 2)->nullable();
            $table->decimal('volumen_m3', 12, 2)->nullable();
            $table->decimal('renovaciones_h', 10, 2)->nullable();
            
            // Columnas Referenciales y Cumplimiento
            $table->decimal('renovaciones_min', 10, 2)->nullable();
            $table->decimal('renovaciones_max', 10, 2)->nullable();
            $table->string('renovaciones_intervalo', 50)->nullable();
            $table->string('cumple', 30)->nullable(); // CUMPLE, NO CUMPLE, REFERENCIAL
            
            // Ubicación y Coordenadas
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
        Schema::dropIfExists('ventilation_measurements');
    }
};
