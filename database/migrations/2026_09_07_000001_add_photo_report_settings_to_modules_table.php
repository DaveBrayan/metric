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
        if (Schema::hasTable('modules') && !Schema::hasColumn('modules', 'photo_report_settings')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->json('photo_report_settings')->nullable()->after('installation_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'photo_report_settings')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('photo_report_settings');
            });
        }
    }
};
