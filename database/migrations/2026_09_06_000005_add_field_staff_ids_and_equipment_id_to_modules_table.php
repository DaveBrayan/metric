<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (!Schema::hasColumn('modules', 'field_staff_ids')) {
                $table->json('field_staff_ids')->nullable()->after('field_staff_id');
            }
            if (!Schema::hasColumn('modules', 'equipment_id')) {
                $table->foreignId('equipment_id')->nullable()->after('calibration_equipment')->constrained('equipment')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('modules', function (Blueprint $table) {
            if (Schema::hasColumn('modules', 'equipment_id')) {
                $table->dropForeign(['equipment_id']);
                $table->dropColumn('equipment_id');
            }
            if (Schema::hasColumn('modules', 'field_staff_ids')) {
                $table->dropColumn('field_staff_ids');
            }
        });
    }
};
