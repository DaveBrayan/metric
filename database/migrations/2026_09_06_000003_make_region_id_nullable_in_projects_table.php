<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('projects', 'region_id')) {
            try {
                DB::statement('ALTER TABLE `projects` MODIFY `region_id` BIGINT UNSIGNED NULL;');
            } catch (\Throwable $e) {
                // Si la columna ya es nullable o el motor no soporta esta sintaxis directa
            }
        }
    }

    public function down(): void
    {
        // No revertir nulabilidad
    }
};
