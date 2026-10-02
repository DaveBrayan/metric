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
        Schema::create('rosa_measurements', function (Blueprint $table) {
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

            // Imágenes y Ubicación GPS
            $table->string('image_path')->nullable();
            $table->json('images')->nullable();
            $table->string('location')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('utm_zone', 20)->nullable();
            $table->decimal('utm_easting', 12, 3)->nullable();
            $table->decimal('utm_northing', 12, 3)->nullable();

            // TABLA A: Evaluación de la Silla
            $table->tinyInteger('altura_asiento_base')->default(1);
            $table->tinyInteger('altura_asiento_mod')->default(0);
            $table->tinyInteger('profundidad_base')->default(1);
            $table->tinyInteger('profundidad_mod')->default(0);
            $table->tinyInteger('reposabrazos_base')->default(1);
            $table->tinyInteger('reposabrazos_mod')->default(0);
            $table->tinyInteger('respaldo_base')->default(1);
            $table->tinyInteger('respaldo_mod')->default(0);
            $table->tinyInteger('silla_tiempo_uso')->default(0); // -1, 0, +1

            // TABLA B: Pantalla y Teléfono
            $table->tinyInteger('pantalla_base')->default(1);
            $table->tinyInteger('pantalla_mod')->default(0);
            $table->tinyInteger('pantalla_tiempo')->default(0); // -1, 0, +1
            $table->tinyInteger('telefono_base')->default(1);
            $table->tinyInteger('telefono_mod')->default(0);
            $table->tinyInteger('telefono_tiempo')->default(0); // -1, 0, +1

            // TABLA C: Ratón y Teclado
            $table->tinyInteger('raton_base')->default(1);
            $table->tinyInteger('raton_mod')->default(0);
            $table->tinyInteger('raton_tiempo')->default(0); // -1, 0, +1
            $table->tinyInteger('teclado_base')->default(1);
            $table->tinyInteger('teclado_mod')->default(0);
            $table->tinyInteger('teclado_tiempo')->default(0); // -1, 0, +1

            // Actividad Muscular
            $table->tinyInteger('actividad_estatica')->default(0);
            $table->tinyInteger('actividad_repetitiva')->default(0);
            $table->tinyInteger('actividad_inestable')->default(0);

            // Puntuaciones Calculadas ROSA
            $table->tinyInteger('score_a')->default(1); // Silla Final
            $table->tinyInteger('score_b')->default(1); // Pantalla y Teléfono
            $table->tinyInteger('score_c')->default(1); // Ratón y Teclado
            $table->tinyInteger('score_d')->default(1); // Periféricos & Pantalla/Teléfono
            $table->tinyInteger('score_e')->default(1); // ROSA Inicial
            $table->tinyInteger('score_actividad')->default(0); // Actividad Muscular (+0..+3)
            $table->tinyInteger('score_final')->default(1); // Puntuación Final ROSA (1..10)
            $table->string('risk_level', 50)->default('Inapreciable');
            $table->string('action_level', 150)->default('Nivel 1: Postura óptima, no se requiere acción');
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
        Schema::dropIfExists('rosa_measurements');
    }
};
