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
        if (Schema::hasTable('ruido_ambiental_measurements')) {
            Schema::table('ruido_ambiental_measurements', function (Blueprint $table) {
                if (!Schema::hasColumn('ruido_ambiental_measurements', 'local_uuid')) {
                    $table->string('local_uuid', 100)->nullable()->index()->after('id');
                }
            });
        }

        if (Schema::hasTable('ventilation_measurements')) {
            Schema::table('ventilation_measurements', function (Blueprint $table) {
                if (!Schema::hasColumn('ventilation_measurements', 'area')) {
                    $table->string('area')->nullable()->after('point_number');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ruido_ambiental_measurements')) {
            Schema::table('ruido_ambiental_measurements', function (Blueprint $table) {
                if (Schema::hasColumn('ruido_ambiental_measurements', 'local_uuid')) {
                    $table->dropColumn('local_uuid');
                }
            });
        }

        if (Schema::hasTable('ventilation_measurements')) {
            Schema::table('ventilation_measurements', function (Blueprint $table) {
                if (Schema::hasColumn('ventilation_measurements', 'area')) {
                    $table->dropColumn('area');
                }
            });
        }
    }
};

