<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Modificar project_id a nullable en modules
        try {
            DB::statement('ALTER TABLE `modules` MODIFY `project_id` BIGINT UNSIGNED NULL;');
        } catch (\Throwable $e) {
            // Ignorar si ya es nullable
        }

        // 2. Agregar columna description a modules si no existe
        if (!Schema::hasColumn('modules', 'description')) {
            Schema::table('modules', function (Blueprint $table) {
                $table->text('description')->nullable()->after('name');
            });
        }

        // 3. Agregar columna manager_id a staff si no existe
        if (!Schema::hasColumn('staff', 'manager_id')) {
            Schema::table('staff', function (Blueprint $table) {
                $table->foreignId('manager_id')->nullable()->after('region_id')->constrained('managers')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        // No revertir
    }
};
