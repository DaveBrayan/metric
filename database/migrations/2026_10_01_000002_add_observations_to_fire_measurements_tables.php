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
        if (Schema::hasTable('fire_activity_measurements')) {
            Schema::table('fire_activity_measurements', function (Blueprint $table) {
                if (!Schema::hasColumn('fire_activity_measurements', 'observations')) {
                    $table->text('observations')->nullable()->after('utm_northing');
                }
            });
        }

        if (Schema::hasTable('fire_weight_measurements')) {
            Schema::table('fire_weight_measurements', function (Blueprint $table) {
                if (!Schema::hasColumn('fire_weight_measurements', 'observations')) {
                    $table->text('observations')->nullable()->after('utm_northing');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('fire_activity_measurements')) {
            Schema::table('fire_activity_measurements', function (Blueprint $table) {
                if (Schema::hasColumn('fire_activity_measurements', 'observations')) {
                    $table->dropColumn('observations');
                }
            });
        }

        if (Schema::hasTable('fire_weight_measurements')) {
            Schema::table('fire_weight_measurements', function (Blueprint $table) {
                if (Schema::hasColumn('fire_weight_measurements', 'observations')) {
                    $table->dropColumn('observations');
                }
            });
        }
    }
};
