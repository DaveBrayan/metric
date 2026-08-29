<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // LPZ-01, SCZ-02, etc.
            $table->string('name');
            $table->string('department');
            $table->string('address')->nullable();
            $table->string('manager_name')->default('Ing. Reynaldo Sirpa');
            $table->string('theme')->default('cyan');
            $table->string('status')->default('Operativo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('regions');
    }
};
