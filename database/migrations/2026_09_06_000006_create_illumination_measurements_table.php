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
        // 1. Metadata en modules para encabezado de monitoreo
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'start_date')) {
                $table->date('start_date')->nullable()->after('name');
            }
            if (!Schema::hasColumn('modules', 'end_date')) {
                $table->date('end_date')->nullable()->after('start_date');
            }
            if (!Schema::hasColumn('modules', 'monitoring_type')) {
                $table->string('monitoring_type')->default('Seguimiento')->after('end_date'); // Rutinario, Seguimiento, Especial
            }
            if (!Schema::hasColumn('modules', 'installation_name')) {
                $table->string('installation_name')->nullable()->after('monitoring_type');
            }
        });

        // 2. Marca en equipos de medición
        Schema::table('equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment', 'brand')) {
                $table->string('brand')->nullable()->after('name');
            }
        });

        // 3. Tabla de mediciones de iluminación ocupacional
        if (!Schema::hasTable('illumination_measurements')) {
            Schema::create('illumination_measurements', function (Blueprint $table) {
                $table->id();
                $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
                $table->string('point_number')->default('01'); // N° de punto
                $table->date('measurement_date'); // Fecha
                $table->string('measurement_time', 20)->nullable(); // Hora (ej: 09:30)
                $table->string('area'); // Área
                $table->string('workstation'); // Puesto de Trabajo
                $table->string('measurement_point'); // Punto de Medición
                $table->string('lighting_type')->default('Artificial'); // Natural, Artificial, Mixta
                $table->decimal('required_lux', 10, 2)->default(300.00); // Nivel Requerido
                $table->decimal('measured_lux', 10, 2)->default(0.00); // Mediciones (LUX)
                $table->string('image_path')->nullable(); // Imágenes
                $table->string('location')->nullable(); // Ubicación
                $table->text('observations')->nullable(); // Observaciones
                $table->string('registered_by')->nullable(); // Registrado por
                $table->foreignId('staff_id')->nullable()->constrained('staff')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('illumination_measurements');

        Schema::table('equipment', function (Blueprint $table) {
            if (Schema::hasColumn('equipment', 'brand')) {
                $table->dropColumn('brand');
            }
        });

        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'installation_name')) {
                $table->dropColumn('installation_name');
            }
            if (Schema::hasColumn('modules', 'monitoring_type')) {
                $table->dropColumn('monitoring_type');
            }
            if (Schema::hasColumn('modules', 'end_date')) {
                $table->dropColumn('end_date');
            }
            if (Schema::hasColumn('modules', 'start_date')) {
                $table->dropColumn('start_date');
            }
        });
    }
};
