<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Manager;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class IndustrialDataSeeder extends Seeder
{
    public function run(): void
    {
        $companiesData = [
            [
                'code' => 'MSC',
                'name' => 'Minera San Cristóbal S.A.',
                'legal_name' => 'Minera San Cristóbal Sociedad Anónima',
                'nit' => '1028475029',
                'industry' => 'Minería & Concentrados',
                'contact_person' => 'Ing. Carlos Mendoza',
                'email' => 'carlos.mendoza@minerasancristobal.com',
                'phone' => '+591 2 2150000',
                'theme' => 'cyan',
                'status' => 'Activo',
                'manager' => [
                    'name' => 'Ing. Carlos Mendoza',
                    'email' => 'mendoza.carlos@metric.com',
                    'phone' => '+591 715-22001',
                    'position' => 'Gerente de Seguridad & Medio Ambiente',
                ],
            ],
            [
                'code' => 'CBN',
                'name' => 'Cervecería Boliviana Nacional',
                'legal_name' => 'Cervecería Boliviana Nacional S.A.',
                'nit' => '1020304050',
                'industry' => 'Alimentos & Bebidas',
                'contact_person' => 'Lic. Elena Vargas',
                'email' => 'elena.vargas@cbn.bo',
                'phone' => '+591 2 2450100',
                'theme' => 'lime',
                'status' => 'Activo',
                'manager' => [
                    'name' => 'Lic. Elena Vargas',
                    'email' => 'vargas.elena@metric.com',
                    'phone' => '+591 720-33002',
                    'position' => 'Responsable de Gestión Ambiental',
                ],
            ],
            [
                'code' => 'PIL',
                'name' => 'PIL Andina S.A.',
                'legal_name' => 'Planta Industrializadora de Leche Andina',
                'nit' => '1019283746',
                'industry' => 'Lácteos & Alimentos',
                'contact_person' => 'Ing. Roberto Cáceres',
                'email' => 'roberto.caceres@pilandina.com.bo',
                'phone' => '+591 4 4280010',
                'theme' => 'cyan',
                'status' => 'Activo',
                'manager' => [
                    'name' => 'Ing. Roberto Cáceres',
                    'email' => 'caceres.roberto@metric.com',
                    'phone' => '+591 730-44003',
                    'position' => 'Jefe de Planta y Efluentes',
                ],
            ],
            [
                'code' => 'SOB',
                'name' => 'SOBOCE S.A.',
                'legal_name' => 'Sociedad Boliviana de Cemento S.A.',
                'nit' => '1025347890',
                'industry' => 'Cemento & Construcción',
                'contact_person' => 'Ing. Fernando Morales',
                'email' => 'fernando.morales@soboce.com',
                'phone' => '+591 2 2795000',
                'theme' => 'amber',
                'status' => 'Activo',
                'manager' => [
                    'name' => 'Ing. Fernando Morales',
                    'email' => 'morales.fernando@metric.com',
                    'phone' => '+591 712-55004',
                    'position' => 'Superintendente de Emisiones Industriales',
                ],
            ],
            [
                'code' => 'YPF',
                'name' => 'YPFB Refinación S.A.',
                'legal_name' => 'YPFB Refinación Sociedad Anónima',
                'nit' => '1029384756',
                'industry' => 'Hidrocarburos & Petroquímica',
                'contact_person' => 'Ing. Mariana Soto',
                'email' => 'mariana.soto@ypfb.bo',
                'phone' => '+591 3 3462000',
                'theme' => 'cyan',
                'status' => 'Activo',
                'manager' => [
                    'name' => 'Ing. Mariana Soto',
                    'email' => 'soto.mariana@metric.com',
                    'phone' => '+591 740-66005',
                    'position' => 'Coordinadora de Monitoreo HSEQ',
                ],
            ],
        ];

        foreach ($companiesData as $data) {
            $managerData = $data['manager'];
            unset($data['manager']);

            $company = Company::updateOrCreate(['code' => $data['code']], $data);

            $manager = Manager::updateOrCreate(
                ['email' => $managerData['email']],
                [
                    'company_id' => $company->id,
                    'name' => $managerData['name'],
                    'phone' => $managerData['phone'],
                    'position' => $managerData['position'],
                    'status' => 'Activo',
                ]
            );

            // Crear usuario para que el responsable pueda iniciar sesión
            User::updateOrCreate(
                ['email' => $managerData['email']],
                [
                    'name' => $managerData['name'],
                    'password' => Hash::make('12345678'),
                    'role' => 'Responsable de Planta',
                    'role_theme' => 'lime',
                    'status' => 'online',
                    'phone' => $managerData['phone'],
                    'permissions' => ['Personal - Gestionar', 'Proyectos - Crear', 'Módulos - Crear', 'Equipos - Ver'],
                ]
            );
        }

        // Vincular los proyectos existentes a sus respectivas empresas y responsables
        $projects = [
            'PRJ-MSC-01' => 'MSC',
            'PRJ-CBN-02' => 'CBN',
            'PRJ-PIL-03' => 'PIL',
            'PRJ-SOB-04' => 'SOB',
            'PRJ-YPF-05' => 'YPF',
        ];

        foreach ($projects as $code => $companyCode) {
            $comp = Company::where('code', $companyCode)->first();
            if ($comp) {
                $mgr = Manager::where('company_id', $comp->id)->first();
                Project::where('code', $code)->update([
                    'company_id' => $comp->id,
                    'manager_id' => $mgr ? $mgr->id : null,
                    'start_date' => date('Y-m-d'),
                ]);
            }
        }
    }
}
