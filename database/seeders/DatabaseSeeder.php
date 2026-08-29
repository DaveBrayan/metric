<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\Region;
use App\Models\Manager;
use App\Models\Staff;
use App\Models\Project;
use App\Models\MeasurementModule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Superadministrador General Solicitado
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

        // Administradores de apoyo
        User::updateOrCreate(
            ['email' => 'carlos.m@pachabol.com'],
            [
                'name' => 'Carlos Mendoza',
                'password' => Hash::make('9210292Dc#PB'),
                'role' => 'Operador de Planta',
                'role_theme' => 'lime',
                'status' => 'online',
                'permissions' => ['Telemetría', 'Control de Sensores', 'Reportes Diarios'],
            ]
        );

        // 2. Regionales
        $regionLP = Region::updateOrCreate(
            ['code' => 'LPZ-01'],
            [
                'name' => 'Regional La Paz (Sede Central)',
                'department' => 'La Paz',
                'address' => 'Av. 6 de Agosto #2450, Sopocachi',
                'manager_name' => 'Ing. Reynaldo Sirpa',
                'theme' => 'cyan',
                'status' => 'Operativo',
            ]
        );

        $regionSC = Region::updateOrCreate(
            ['code' => 'SCZ-02'],
            [
                'name' => 'Regional Santa Cruz',
                'department' => 'Santa Cruz',
                'address' => 'Parque Industrial PI-22, 4to Anillo',
                'manager_name' => 'Ing. Carlos Mendoza',
                'theme' => 'lime',
                'status' => 'Operativo',
            ]
        );

        $regionCB = Region::updateOrCreate(
            ['code' => 'CBB-03'],
            [
                'name' => 'Regional Cochabamba',
                'department' => 'Cochabamba',
                'address' => 'Av. América Este #1020',
                'manager_name' => 'Ing. Valeria Gutiérrez',
                'theme' => 'cyan',
                'status' => 'Operativo',
            ]
        );

        $regionPT = Region::updateOrCreate(
            ['code' => 'PTS-04'],
            [
                'name' => 'Regional Potosí (Distrito Minero)',
                'department' => 'Potosí',
                'address' => 'Zona San Cristóbal, Km 42',
                'manager_name' => 'Ing. Diego Fernández',
                'theme' => 'lime',
                'status' => 'Operativo',
            ]
        );

        $regionOR = Region::updateOrCreate(
            ['code' => 'ORU-05'],
            [
                'name' => 'Regional Oruro',
                'department' => 'Oruro',
                'address' => 'Av. 24 de Junio, Zona Industrial',
                'manager_name' => 'Ing. Fernando Choque',
                'theme' => 'cyan',
                'status' => 'Operativo',
            ]
        );

        // 3. Empresas Clientes
        $compMSC = Company::updateOrCreate(
            ['code' => 'MSC-01'],
            [
                'name' => 'Minera San Cristóbal S.A.',
                'legal_name' => 'Minera San Cristóbal Sociedad Anónima',
                'nit' => '1028491023',
                'industry' => 'Minería a Cielo Abierto',
                'contact_person' => 'Ing. Roberto Cáceres',
                'email' => 'rcaceres@minerasancristobal.com',
                'phone' => '+591 2 211-4000',
                'theme' => 'cyan',
                'status' => 'Activo',
            ]
        );

        $compCBN = Company::updateOrCreate(
            ['code' => 'CBN-02'],
            [
                'name' => 'Cervecería Boliviana Nacional',
                'legal_name' => 'Cervecería Boliviana Nacional S.A.',
                'nit' => '1020304050',
                'industry' => 'Bebidas & Alimentos',
                'contact_person' => 'Lic. Mariana Daza',
                'email' => 'mdaza@cbn.bo',
                'phone' => '+591 3 346-2000',
                'theme' => 'lime',
                'status' => 'Activo',
            ]
        );

        $compSOB = Company::updateOrCreate(
            ['code' => 'SOB-03'],
            [
                'name' => 'Soboce Cemento & Hormigón',
                'legal_name' => 'Sociedad Boliviana de Cemento S.A.',
                'nit' => '1019283746',
                'industry' => 'Materiales de Construcción',
                'contact_person' => 'Ing. Javier Torrico',
                'email' => 'jtorrico@soboce.com',
                'phone' => '+591 2 281-5500',
                'theme' => 'cyan',
                'status' => 'Activo',
            ]
        );

        $compYPF = Company::updateOrCreate(
            ['code' => 'YPF-04'],
            [
                'name' => 'YPFB Transporte',
                'legal_name' => 'YPFB Transporte S.A.',
                'nit' => '1023456789',
                'industry' => 'Hidrocarburos & Gasoductos',
                'contact_person' => 'Ing. Marcelo Vaca',
                'email' => 'mvaca@ypfbtransporte.com.bo',
                'phone' => '+591 3 356-8000',
                'theme' => 'lime',
                'status' => 'Activo',
            ]
        );

        $compPIL = Company::updateOrCreate(
            ['code' => 'PIL-05'],
            [
                'name' => 'Pil Andina S.A.',
                'legal_name' => 'Pil Andina Sociedad Anónima',
                'nit' => '1034567890',
                'industry' => 'Procesamiento Lácteo',
                'contact_person' => 'Dra. Gabriela Soliz',
                'email' => 'gsoliz@pilandina.com.bo',
                'phone' => '+591 4 435-0000',
                'theme' => 'cyan',
                'status' => 'Activo',
            ]
        );

        // 4. Responsables de Planta
        $mgrMSC = Manager::updateOrCreate(
            ['email' => 'rcaceres@minerasancristobal.com'],
            [
                'company_id' => $compMSC->id,
                'name' => 'Ing. Roberto Cáceres',
                'phone' => '+591 715-99201',
                'position' => 'Gerente de Medio Ambiente & Relaves',
                'status' => 'Activo',
            ]
        );

        $mgrCBN = Manager::updateOrCreate(
            ['email' => 'mdaza@cbn.bo'],
            [
                'company_id' => $compCBN->id,
                'name' => 'Lic. Mariana Daza',
                'phone' => '+591 721-33402',
                'position' => 'Jefa de Calidad & Seguridad Industrial',
                'status' => 'Activo',
            ]
        );

        $mgrSOB = Manager::updateOrCreate(
            ['email' => 'jtorrico@soboce.com'],
            [
                'company_id' => $compSOB->id,
                'name' => 'Ing. Javier Torrico',
                'phone' => '+591 764-88910',
                'position' => 'Supervisor de Hornos & Emisiones',
                'status' => 'Activo',
            ]
        );

        // 5. Personal Técnico
        $staffReynaldo = Staff::updateOrCreate(
            ['email' => 'reynaldo.s@pachabol.com'],
            [
                'region_id' => $regionLP->id,
                'name' => 'Reynaldo Sirpa',
                'phone' => '+591 715-10200',
                'department' => 'Dirección de Operaciones & Monitoreo',
                'position' => 'Director Técnico de Monitoreo',
                'role_theme' => 'cyan',
                'status' => 'online',
                'status_label' => 'En Planta',
            ]
        );

        $staffGonzalo = Staff::updateOrCreate(
            ['email' => 'gonzalo.a@pachabol.com'],
            [
                'region_id' => $regionLP->id,
                'name' => 'Gonzalo Arnez',
                'phone' => '+591 715-44210',
                'department' => 'Ingeniería de Automatización',
                'position' => 'Ingeniero Senior SCADA',
                'role_theme' => 'lime',
                'status' => 'online',
                'status_label' => 'En Planta',
            ]
        );

        $staffCarla = Staff::updateOrCreate(
            ['email' => 'carla.v@pachabol.com'],
            [
                'region_id' => $regionSC->id,
                'name' => 'Carla Villarroel',
                'phone' => '+591 721-88901',
                'department' => 'Telemetría & IoT',
                'position' => 'Especialista en Sensores',
                'role_theme' => 'cyan',
                'status' => 'online',
                'status_label' => 'En Campo',
            ]
        );

        // 6. Proyectos Industriales
        $prjMSC = Project::updateOrCreate(
            ['code' => 'PRJ-MSC-01'],
            [
                'name' => 'Monitoreo de Relaves & Calidad Ambiental',
                'company_id' => $compMSC->id,
                'region_id' => $regionPT->id,
                'manager_id' => $mgrMSC->id,
                'compliance_pct' => 99.40,
                'points_total' => 28,
                'points_completed' => 24,
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
                'start_date' => '2024-01-15',
                'end_date' => '2024-12-31',
            ]
        );

        $prjCBN = Project::updateOrCreate(
            ['code' => 'PRJ-CBN-02'],
            [
                'name' => 'Automatización & Control Ambiental Cervecería',
                'company_id' => $compCBN->id,
                'region_id' => $regionSC->id,
                'manager_id' => $mgrCBN->id,
                'compliance_pct' => 100.00,
                'points_total' => 18,
                'points_completed' => 18,
                'status' => 'Completado',
                'status_type' => 'done',
                'start_date' => '2024-02-01',
                'end_date' => '2024-08-30',
            ]
        );

        $prjSOB = Project::updateOrCreate(
            ['code' => 'PRJ-SOB-03'],
            [
                'name' => 'Control de Emisiones en Hornos & Ruido',
                'company_id' => $compSOB->id,
                'region_id' => $regionLP->id,
                'manager_id' => $mgrSOB->id,
                'compliance_pct' => 98.10,
                'points_total' => 20,
                'points_completed' => 14,
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
                'start_date' => '2024-03-10',
                'end_date' => '2024-11-30',
            ]
        );

        // 7. Módulos de Medición Ambiental
        MeasurementModule::updateOrCreate(
            ['project_id' => $prjMSC->id, 'key' => 'dosimetria'],
            [
                'name' => 'Dosimetría de Ruido',
                'calibration_equipment' => 'SVANTEK SV104A',
                'calibration_certificate' => 'CERT-IBM-2025-084',
                'field_staff_id' => $staffReynaldo->id,
                'points_total' => 15,
                'points_completed' => 12,
                'current_reading' => '78.4',
                'unit' => 'dB(A) Leq',
                'lmp_limit' => 'LMP: 85 dB(A)',
                'status' => 'Conforme',
                'status_theme' => 'done',
            ]
        );

        MeasurementModule::updateOrCreate(
            ['project_id' => $prjMSC->id, 'key' => 'ruido_ambiental'],
            [
                'name' => 'Ruido Ambiental',
                'calibration_equipment' => 'Sonómetro NTi XL2 Clase 1',
                'calibration_certificate' => 'CERT-IBM-2025-092',
                'field_staff_id' => $staffGonzalo->id,
                'points_total' => 10,
                'points_completed' => 10,
                'current_reading' => '58.2',
                'unit' => 'dB(A) Ld/Ln',
                'lmp_limit' => 'LMP: 68 dB(A)',
                'status' => 'Conforme',
                'status_theme' => 'done',
            ]
        );

        MeasurementModule::updateOrCreate(
            ['project_id' => $prjMSC->id, 'key' => 'agua'],
            [
                'name' => 'Calidad de Agua (Parámetros de Campo)',
                'calibration_equipment' => 'HANNA HI98194 + HACH',
                'calibration_certificate' => 'CERT-IBM-2025-103',
                'field_staff_id' => $staffCarla->id,
                'points_total' => 12,
                'points_completed' => 8,
                'current_reading' => '7.35',
                'unit' => 'pH / 14 NTU',
                'lmp_limit' => 'Rango: 6.0 - 9.0 pH',
                'status' => 'Conforme',
                'status_theme' => 'done',
            ]
        );

        MeasurementModule::updateOrCreate(
            ['project_id' => $prjSOB->id, 'key' => 'opacidad'],
            [
                'name' => 'Opacidad (Humos / Emisiones)',
                'calibration_equipment' => 'Opacímetro Testo 308 + MRU',
                'calibration_certificate' => 'CERT-IBM-2025-115',
                'field_staff_id' => $staffGonzalo->id,
                'points_total' => 8,
                'points_completed' => 7,
                'current_reading' => '8.5%',
                'unit' => 'Escala Ringelmann 1',
                'lmp_limit' => 'LMP: < 20%',
                'status' => 'Conforme',
                'status_theme' => 'done',
            ]
        );

        MeasurementModule::updateOrCreate(
            ['project_id' => $prjSOB->id, 'key' => 'particulas'],
            [
                'name' => 'Partículas 24 Horas (PM10 / PM2.5)',
                'calibration_equipment' => 'Muestreador Hi-Vol Tisch TE-6070',
                'calibration_certificate' => 'CERT-IBM-2025-128',
                'field_staff_id' => $staffCarla->id,
                'points_total' => 6,
                'points_completed' => 3,
                'current_reading' => '42.0',
                'unit' => 'µg/m³ PM10',
                'lmp_limit' => 'LMP: 150 µg/m³',
                'status' => 'Conforme',
                'status_theme' => 'done',
            ]
        );
    }
}
