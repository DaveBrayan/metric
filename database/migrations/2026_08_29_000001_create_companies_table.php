<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // MSC-01, CBN-02, SOB-03, etc.
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('nit')->nullable();
            $table->string('industry')->default('Minería & Energía');
            $table->string('contact_person')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('theme')->default('cyan');
            $table->string('status')->default('Activo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
