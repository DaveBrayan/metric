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
        // 1. Agregar campos de calibración a la tabla equipment
        Schema::table('equipment', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment', 'calibration_date')) {
                $table->date('calibration_date')->nullable()->after('description');
            }
            if (!Schema::hasColumn('equipment', 'next_recalibration_date')) {
                $table->date('next_recalibration_date')->nullable()->after('calibration_date');
            }
            if (!Schema::hasColumn('equipment', 'recalibration_observation')) {
                $table->text('recalibration_observation')->nullable()->after('next_recalibration_date');
            }
        });

        // 2. Crear tabla de historial de calibraciones
        if (!Schema::hasTable('equipment_calibrations')) {
            Schema::create('equipment_calibrations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->constrained('equipment')->onDelete('cascade');
                $table->date('calibration_date');
                $table->date('next_recalibration_date');
                $table->text('observation')->nullable();
                $table->string('performed_by')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_calibrations');

        Schema::table('equipment', function (Blueprint $table) {
            if (Schema::hasColumn('equipment', 'recalibration_observation')) {
                $table->dropColumn('recalibration_observation');
            }
            if (Schema::hasColumn('equipment', 'next_recalibration_date')) {
                $table->dropColumn('next_recalibration_date');
            }
            if (Schema::hasColumn('equipment', 'calibration_date')) {
                $table->dropColumn('calibration_date');
            }
        });
    }
};
