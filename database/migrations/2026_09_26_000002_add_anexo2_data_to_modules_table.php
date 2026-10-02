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
        if (Schema::hasTable('modules') && !Schema::hasColumn('modules', 'anexo2_data')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->json('anexo2_data')->nullable()->after('photo_report_settings');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('modules') && Schema::hasColumn('modules', 'anexo2_data')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->dropColumn('anexo2_data');
            });
        }
    }
};
