<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'first_name')) {
                $table->string('first_name', 100)->nullable()->after('manager_id');
            }
            if (!Schema::hasColumn('staff', 'last_name')) {
                $table->string('last_name', 100)->nullable()->after('first_name');
            }
            if (!Schema::hasColumn('staff', 'device_name')) {
                $table->string('device_name', 150)->nullable()->after('position');
            }
            if (!Schema::hasColumn('staff', 'fcm_token')) {
                $table->text('fcm_token')->nullable()->after('device_name');
            }
            if (!Schema::hasColumn('staff', 'password_plain')) {
                $table->string('password_plain', 100)->nullable()->after('fcm_token');
            }
        });

        // Hacer nullable email, phone y department para máxima flexibilidad
        try {
            DB::statement('ALTER TABLE `staff` MODIFY `email` VARCHAR(255) NULL;');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE `staff` MODIFY `department` VARCHAR(255) NULL;');
        } catch (\Throwable $e) {}

        try {
            DB::statement('ALTER TABLE `staff` MODIFY `phone` VARCHAR(255) NULL;');
        } catch (\Throwable $e) {}

        // Migrar registros existentes dividiendo name en first_name y last_name
        try {
            $existing = DB::table('staff')->get();
            foreach ($existing as $member) {
                $parts = preg_split('/\s+/', trim($member->name ?? ''), 2);
                $firstName = $parts[0] ?? 'Colaborador';
                $lastName = $parts[1] ?? '';
                
                $deviceName = $member->device_name ?: ($member->phone ?: 'Terminal Colector v2.4');
                $passwordPlain = $member->password_plain ?: 'Metric2026*';

                DB::table('staff')->where('id', $member->id)->update([
                    'first_name' => $member->first_name ?: $firstName,
                    'last_name' => $member->last_name ?: $lastName,
                    'device_name' => $deviceName,
                    'password_plain' => $passwordPlain,
                ]);
            }
        } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'first_name')) {
                $table->dropColumn('first_name');
            }
            if (Schema::hasColumn('staff', 'last_name')) {
                $table->dropColumn('last_name');
            }
            if (Schema::hasColumn('staff', 'device_name')) {
                $table->dropColumn('device_name');
            }
            if (Schema::hasColumn('staff', 'fcm_token')) {
                $table->dropColumn('fcm_token');
            }
            if (Schema::hasColumn('staff', 'password_plain')) {
                $table->dropColumn('password_plain');
            }
        });
    }
};
