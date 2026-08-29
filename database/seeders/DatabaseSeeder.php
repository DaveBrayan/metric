<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Único Superadministrador del Sistema
        User::updateOrCreate(
            ['email' => 'admin@metric.com'],
            [
                'name' => 'Reynaldo Sirpa',
                'password' => Hash::make('9210292Dc#PB'),
                'role' => 'Superadministrador',
                'role_theme' => 'cyan',
                'status' => 'online',
                'phone' => '+591 715-10200',
                'permissions' => ['Control Total', 'Telemetría', 'Finanzas', 'Configuración', 'Auditoría'],
            ]
        );
    }
}
