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
            if (!Schema::hasColumn('illumination_measurements', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location');
            }
            if (!Schema::hasColumn('illumination_measurements', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('illumination_measurements', function (Blueprint $table) {
            if (Schema::hasColumn('illumination_measurements', 'longitude')) {
                $table->dropColumn('longitude');
            }
            if (Schema::hasColumn('illumination_measurements', 'latitude')) {
                $table->dropColumn('latitude');
            }
        });
    }
};
