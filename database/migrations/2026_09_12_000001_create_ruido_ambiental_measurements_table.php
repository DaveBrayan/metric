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
        if (!Schema::hasTable('ruido_ambiental_measurements')) {
            Schema::create('ruido_ambiental_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->foreignId('project_id')->nullable()->constrained('projects')->onDelete('cascade');
                $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null');
                
                $table->string('point_number', 50)->nullable();
                $table->date('measurement_date')->nullable();
                $table->string('measurement_time', 50)->nullable();
                
                // Normativa y Zona
                $table->string('normativa')->nullable(); // 'RASIM - ANEXO 12-C' o 'RMCA - ANEXO 6'
                $table->string('tipo_zona')->nullable();
                $table->string('horario')->nullable();
                $table->decimal('limite_normativa', 8, 2)->nullable();
                $table->string('zona_banda', 50)->default('19K');
                
                // Colindancias y Coordenadas UTM
                $table->string('norte_colindancia')->nullable();
                $table->string('norte_x')->nullable();
                $table->string('norte_y')->nullable();
                
                $table->string('sur_colindancia')->nullable();
                $table->string('sur_x')->nullable();
                $table->string('sur_y')->nullable();
                
                $table->string('este_colindancia')->nullable();
                $table->string('este_x')->nullable();
                $table->string('este_y')->nullable();
                
                $table->string('oeste_colindancia')->nullable();
                $table->string('oeste_x')->nullable();
                $table->string('oeste_y')->nullable();
                
                // Puntos Cardinales (P1 Norte, P2 Sur, P3 Este, P4 Oeste)
                $table->string('p1_norte_inicio')->nullable();
                $table->string('p1_norte_fin')->nullable();
                $table->json('p1_norte_puntos')->nullable();
                
                $table->string('p2_sur_inicio')->nullable();
                $table->string('p2_sur_fin')->nullable();
                $table->json('p2_sur_puntos')->nullable();
                
                $table->string('p3_este_inicio')->nullable();
                $table->string('p3_este_fin')->nullable();
                $table->json('p3_este_puntos')->nullable();
                
                $table->string('p4_oeste_inicio')->nullable();
                $table->string('p4_oeste_fin')->nullable();
                $table->json('p4_oeste_puntos')->nullable();
                
                // Mediciones consolidadas & Cálculos
                $table->json('mediciones_db')->nullable();
                $table->decimal('leq_d', 8, 2)->nullable();
                $table->decimal('nps_max', 8, 2)->nullable();
                $table->decimal('nps_min', 8, 2)->nullable();
                $table->boolean('is_compliant')->default(true);
                
                // Geolocalización y Multimedia
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->text('location')->nullable();
                $table->string('image_path')->nullable();
                $table->json('images')->nullable();
                $table->text('observations')->nullable();
                
                $table->string('registered_by')->nullable();
                $table->string('status')->default('Completado');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ruido_ambiental_measurements');
    }
};
