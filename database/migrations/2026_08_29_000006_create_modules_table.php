<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('key'); // dosimetria, ruido_ambiental, agua, opacidad, particulas
            $table->string('name'); // Dosimetría de Ruido, Ruido Ambiental, etc.
            $table->string('calibration_equipment')->default('SVANTEK SV104A');
            $table->string('calibration_certificate')->nullable();
            $table->foreignId('field_staff_id')->nullable()->constrained('staff')->onDelete('set null');
            $table->integer('points_total')->default(15);
            $table->integer('points_completed')->default(10);
            $table->string('current_reading')->nullable(); // 78.4, 7.35, etc.
            $table->string('unit')->default('dB(A)');
            $table->string('lmp_limit')->default('LMP: 85 dB(A)');
            $table->string('status')->default('Conforme');
            $table->string('status_theme')->default('done'); // done, in_progress, alert, pending
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
