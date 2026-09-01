<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // PRJ-MSC-01, PRJ-CBN-02, etc.
            $table->string('name');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('managers')->onDelete('set null');
            $table->decimal('compliance_pct', 5, 2)->default(98.50);
            $table->integer('points_total')->default(20);
            $table->integer('points_completed')->default(15);
            $table->string('status')->default('En Ejecución');
            $table->string('status_type')->default('in_progress'); // in_progress, done, pending, stopped
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
