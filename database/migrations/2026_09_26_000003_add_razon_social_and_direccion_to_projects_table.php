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
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (!Schema::hasColumn('projects', 'razon_social')) {
                    $table->string('razon_social')->nullable()->after('name');
                }
                if (!Schema::hasColumn('projects', 'direccion')) {
                    $table->text('direccion')->nullable()->after('razon_social');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('projects')) {
            Schema::table('projects', function (Blueprint $table) {
                if (Schema::hasColumn('projects', 'direccion')) {
                    $table->dropColumn('direccion');
                }
                if (Schema::hasColumn('projects', 'razon_social')) {
                    $table->dropColumn('razon_social');
                }
            });
        }
    }
};
