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
        Schema::table('illumination_measurements', function (Blueprint $table) {
            if (!Schema::hasColumn('illumination_measurements', 'readings')) {
                $table->json('readings')->nullable()->after('measured_lux');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('illumination_measurements', function (Blueprint $table) {
            if (Schema::hasColumn('illumination_measurements', 'readings')) {
                $table->dropColumn('readings');
            }
        });
    }
};
