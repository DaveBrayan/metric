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
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre del equipo (e.g., Anemómetro, Sonómetro, Dosímetro)
            $table->string('model')->nullable(); // Modelo (e.g., AirflowTes-Master, AN100)
            $table->string('serial_number')->nullable(); // N° de Serie (e.g., mbjb021078, 211111005)
            $table->text('description')->nullable(); // Descripción u observaciones
            $table->string('image')->nullable(); // Ruta de imagen del equipo
            $table->string('status')->default('Operativo'); // Operativo, En Calibración, En Mantenimiento, Fuera de Servicio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment');
    }
};
