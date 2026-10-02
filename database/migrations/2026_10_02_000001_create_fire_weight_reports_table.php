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
        Schema::create('fire_weight_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('modules')->onDelete('cascade');
            $table->string('macroarea')->nullable();
            $table->json('dimensions')->nullable();
            $table->json('sectors')->nullable();
            $table->json('fire_equipments')->nullable();
            $table->json('macro_summary')->nullable();
            $table->json('extinguishers')->nullable();
            $table->json('report_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fire_weight_reports');
    }
};
