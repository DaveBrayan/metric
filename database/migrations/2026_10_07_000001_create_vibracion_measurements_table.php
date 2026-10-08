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
        Schema::create('vibracion_measurements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('point_number', 50)->default('VIB-1');
            $table->string('codigo', 50)->nullable();
            $table->date('measurement_date')->nullable();
            $table->string('measurement_time', 20)->default('08:00');
            $table->string('area', 255)->nullable();
            $table->string('workstation', 255)->nullable();
            $table->string('puesto_trabajo', 255)->nullable();
            $table->string('punto_medicion', 255)->nullable();
            $table->string('trabajador_evaluado', 255)->nullable();
            $table->string('maquina_equipo', 255)->nullable();
            $table->decimal('duracion_jornada_h', 8, 2)->default(8.00);
            $table->decimal('tiempo_expos_h', 8, 2)->default(8.00);
            $table->integer('duracion_prueba_min')->default(15);
            $table->string('tipo', 50)->default('cuerpo_entero'); // cuerpo_entero | mano_brazo
            $table->string('ub_acelerometro', 100)->nullable(); // base_asiento | espaldar_asiento | base_pies
            $table->decimal('aeqx_ce', 10, 4)->default(0.0000);
            $table->decimal('aeqy_ce', 10, 4)->default(0.0000);
            $table->decimal('aeqz_ce', 10, 4)->default(0.0000);
            $table->string('mano_afectada', 50)->nullable(); // derecha | izquierda
            $table->decimal('aeqx_mb', 10, 4)->default(0.0000);
            $table->decimal('aeqy_mb', 10, 4)->default(0.0000);
            $table->decimal('aeqz_mb', 10, 4)->default(0.0000);
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
        Schema::dropIfExists('vibracion_measurements');
    }
};
