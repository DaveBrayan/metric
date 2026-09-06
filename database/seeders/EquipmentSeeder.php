<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            [
                'name' => 'Anemómetro',
                'model' => 'AirflowTes-Master',
                'serial_number' => 'mbjb021078',
                'description' => 'AirflowTes-Master',
                'status' => 'Operativo',
            ],
            [
                'name' => 'Anemómetro',
                'model' => 'AN100',
                'serial_number' => '211111005',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Contador de Partículas Suspendidas',
                'model' => 'LKC-1000 2ND',
                'serial_number' => '23.08.10 qq',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Distanciómetro',
                'model' => '60MT',
                'serial_number' => '11',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Distanciómetro',
                'model' => 'LS - P',
                'serial_number' => '12',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Dosímetro',
                'model' => 'GA113',
                'serial_number' => '5',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Luxómetro',
                'model' => 'Lux Test Master',
                'serial_number' => '2',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Luxómetro',
                'model' => 'PCE-174',
                'serial_number' => '150206371',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Medidor de calidad de aire',
                'model' => 'VSON',
                'serial_number' => 'wp6930s',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Medidor de calidad de aire',
                'model' => 'TEMTOP',
                'serial_number' => '1234',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Medidor de CO2',
                'model' => 'CO2+CO METER',
                'serial_number' => '10',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Medidor Multigases',
                'model' => 'BH - 45',
                'serial_number' => '9',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Proyectora',
                'model' => null,
                'serial_number' => '13',
                'description' => 'Equipo sin numero de serie',
                'status' => 'Operativo',
            ],
            [
                'name' => 'Sonómetro',
                'model' => 'SL400',
                'serial_number' => '4',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Sonómetro',
                'model' => 'SoundTestMaster',
                'serial_number' => '3',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Termohigrómetro',
                'model' => 'TC100',
                'serial_number' => '6',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Trípode',
                'model' => 'INSTRUMENT TRIPOD',
                'serial_number' => '14',
                'description' => null,
                'status' => 'Operativo',
            ],
            [
                'name' => 'Vibrometro',
                'model' => 'Inlite',
                'serial_number' => '123',
                'description' => null,
                'status' => 'Operativo',
            ],
        ];

        foreach ($items as $item) {
            Equipment::updateOrCreate(
                [
                    'name' => $item['name'],
                    'serial_number' => $item['serial_number'],
                ],
                $item
            );
        }
    }
}
