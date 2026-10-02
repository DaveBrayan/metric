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
        Schema::create('reba_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('point_number', 50)->nullable();
            $table->date('measurement_date')->nullable();
            $table->string('measurement_time', 20)->nullable();

            // Datos del puesto de trabajo
            $table->string('area_sector')->nullable();
            $table->string('puesto_trabajo')->nullable();
            $table->string('factor_riesgo')->nullable();
            $table->integer('num_trabajadores')->default(1);
            $table->json('nombres_trabajadores')->nullable();
            $table->string('edad', 50)->nullable();
            $table->decimal('tiempo_exposicion_horas', 5, 2)->nullable();
            $table->string('procedimiento_escrito', 20)->nullable();
            $table->string('capacitacion', 20)->nullable();
            $table->string('fuerza_agarre', 50)->nullable();
            $table->decimal('carga_peso_kg', 8, 2)->nullable();
            $table->decimal('distancia_m', 8, 2)->nullable();
            $table->string('ayuda_mecanica')->nullable();
            $table->text('descripcion_carga')->nullable();
            $table->string('manifestacion_temprana', 20)->nullable();
            $table->string('ubicacion_sintoma', 100)->nullable();
            $table->json('tareas')->nullable();
            $table->text('observaciones')->nullable();

            // Imágenes y Ubicación
            $table->string('image_path')->nullable();
            $table->json('images')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('utm_zone', 20)->nullable();
            $table->decimal('utm_easting', 12, 3)->nullable();
            $table->decimal('utm_northing', 12, 3)->nullable();

            // Entradas Grupo A (Tronco, Cuello, Piernas, Carga/Fuerza)
            $table->tinyInteger('tronco_base')->default(1);
            $table->tinyInteger('tronco_mod')->default(0);
            $table->tinyInteger('cuello_base')->default(1);
            $table->tinyInteger('cuello_mod')->default(0);
            $table->tinyInteger('piernas_base')->default(1);
            $table->tinyInteger('piernas_mod')->default(0);
            $table->tinyInteger('carga_fuerza')->default(0);
            $table->tinyInteger('carga_brusca')->default(0);

            // Entradas Grupo B (Brazos, Antebrazos, Muñecas, Agarre)
            $table->tinyInteger('brazo_base')->default(1);
            $table->tinyInteger('brazo_abduccion')->default(0);
            $table->tinyInteger('brazo_hombro_elevado')->default(0);
            $table->tinyInteger('brazo_apoyo_gravedad')->default(0);
            $table->tinyInteger('antebrazo_base')->default(1);
            $table->tinyInteger('muneca_base')->default(1);
            $table->tinyInteger('muneca_mod')->default(0);
            $table->tinyInteger('agarre')->default(0);

            // Puntuación por Actividad Muscular
            $table->tinyInteger('actividad_estatica')->default(0);
            $table->tinyInteger('actividad_repetitiva')->default(0);
            $table->tinyInteger('actividad_inestable')->default(0);

            // Puntuaciones Calculadas REBA
            $table->tinyInteger('score_tabla_a')->default(1);
            $table->tinyInteger('score_a')->default(1);
            $table->tinyInteger('score_tabla_b')->default(1);
            $table->tinyInteger('score_b')->default(1);
            $table->tinyInteger('score_c')->default(1);
            $table->tinyInteger('score_actividad')->default(0);
            $table->tinyInteger('score_final')->default(1);
            $table->string('risk_level', 50)->default('Inapreciable');
            $table->string('action_level', 150)->default('Nivel 0: No es necesaria acción');
            $table->string('risk_theme', 50)->default('emerald');

            // Personal y Registro
            $table->string('registered_by')->nullable();
            $table->foreignId('staff_id')->nullable()->constrained('staff')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reba_measurements');
    }
};
