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
        if (Schema::hasTable('cold_stress_measurements')) {
            Schema::table('cold_stress_measurements', function (Blueprint $table) {
                if (!Schema::hasColumn('cold_stress_measurements', 'local_uuid')) {
                    $table->string('local_uuid', 100)->nullable()->index()->after('id');
                }
                if (!Schema::hasColumn('cold_stress_measurements', 'registered_by')) {
                    $table->string('registered_by', 255)->nullable()->after('observations');
                }
                if (!Schema::hasColumn('cold_stress_measurements', 'created_by')) {
                    $table->string('created_by', 255)->nullable()->after('registered_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('cold_stress_measurements')) {
            Schema::table('cold_stress_measurements', function (Blueprint $table) {
                if (Schema::hasColumn('cold_stress_measurements', 'local_uuid')) {
                    $table->dropColumn('local_uuid');
                }
                if (Schema::hasColumn('cold_stress_measurements', 'registered_by')) {
                    $table->dropColumn('registered_by');
                }
                if (Schema::hasColumn('cold_stress_measurements', 'created_by')) {
                    $table->dropColumn('created_by');
                }
            });
        }
    }
};

