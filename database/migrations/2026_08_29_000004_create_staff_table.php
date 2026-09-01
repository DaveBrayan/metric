<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('department')->default('Ingeniería de Automatización');
            $table->string('position')->default('Ingeniero Senior SCADA');
            $table->string('role_theme')->default('cyan');
            $table->string('status')->default('online');
            $table->string('status_label')->default('En Planta');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
