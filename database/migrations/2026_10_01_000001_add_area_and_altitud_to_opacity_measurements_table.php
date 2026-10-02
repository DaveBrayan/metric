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
        Schema::table('opacity_measurements', function (Blueprint $table) {
            if (!Schema::hasColumn('opacity_measurements', 'area')) {
                $table->string('area')->nullable()->after('measurement_time');
            }
            if (!Schema::hasColumn('opacity_measurements', 'altitud')) {
                $table->string('altitud')->nullable()->default('1500-3000')->after('area');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opacity_measurements', function (Blueprint $table) {
            if (Schema::hasColumn('opacity_measurements', 'altitud')) {
                $table->dropColumn('altitud');
            }
            if (Schema::hasColumn('opacity_measurements', 'area')) {
                $table->dropColumn('area');
            }
        });
    }
};
