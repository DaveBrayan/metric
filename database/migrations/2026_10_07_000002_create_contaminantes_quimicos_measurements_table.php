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
        Schema::create('contaminantes_quimicos_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('point_number', 50)->default('CQ-1');
            $table->string('codigo', 50)->nullable();
            $table->date('measurement_date')->nullable();
            $table->string('measurement_time', 20)->default('08:00');
            $table->string('area', 255)->nullable();
            $table->string('punto_medicion', 255)->nullable();
            $table->string('trabajador_nombre', 255)->nullable();
            $table->decimal('masa_inicial_filtro_mg', 12, 4)->nullable();
            $table->decimal('masa_final_filtro_mg', 12, 4)->nullable();
            $table->string('hora_inicio', 20)->nullable();
            $table->string('hora_final', 20)->nullable();
            $table->decimal('t_inicial_c', 8, 2)->nullable();
            $table->decimal('t_final_c', 8, 2)->nullable();
            $table->decimal('presion_hpa', 8, 2)->nullable();
            $table->decimal('q_inicial_lmin', 8, 2)->nullable();
            $table->decimal('q_final_lmin', 8, 2)->nullable();
            $table->text('location')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('utm_zone', 20)->default('19K');
            $table->decimal('utm_easting', 12, 3)->nullable();
            $table->decimal('utm_northing', 12, 3)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->json('images')->nullable();
            $table->json('image_urls')->nullable();
            $table->text('observations')->nullable();
            $table->string('registered_by', 255)->nullable();
            $table->string('created_by', 255)->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->string('local_uuid', 100)->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contaminantes_quimicos_measurements');
    }
};
