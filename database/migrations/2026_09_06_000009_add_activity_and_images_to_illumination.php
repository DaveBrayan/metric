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
            if (!Schema::hasColumn('illumination_measurements', 'activity_description')) {
                $table->string('activity_description')->nullable()->after('measurement_point');
            }
            if (!Schema::hasColumn('illumination_measurements', 'images')) {
                $table->json('images')->nullable()->after('image_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('illumination_measurements', function (Blueprint $table) {
            if (Schema::hasColumn('illumination_measurements', 'activity_description')) {
                $table->dropColumn('activity_description');
            }
            if (Schema::hasColumn('illumination_measurements', 'images')) {
                $table->dropColumn('images');
            }
        });
    }
};
