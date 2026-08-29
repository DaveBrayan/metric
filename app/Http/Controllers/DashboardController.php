<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userName = 'Reynaldo';
        $currentDate = '24 de mayo, 2024';

        // 1. Selector de Empresa Global
        $companiesList = [
            ['id' => 'all', 'name' => 'Todas las Empresas (24 Clientes)'],
            ['id' => 'msc', 'name' => 'Minera San Cristóbal S.A. (Potosí)'],
            ['id' => 'cbn', 'name' => 'Cervecería Boliviana Nacional (Santa Cruz)'],
            ['id' => 'soboce', 'name' => 'Soboce Cemento & Hormigón (La Paz)'],
            ['id' => 'ypfb', 'name' => 'YPFB Transporte (Santa Cruz)'],
            ['id' => 'pil', 'name' => 'Pil Andina S.A. (Cochabamba)'],
        ];

        // 2. Semáforo & Estado General del Sistema
        $systemHealth = [
            'overall_compliance' => 98.6,
            'compliance_label' => 'Óptimo (LMP Ley 1333)',
            'critical_alerts' => 0,
            'active_sensors' => 142,
            'calibrated_instruments' => 18,
            'active_regions' => 5,
        ];

        // 3. Tarjetas de las 5 Líneas Ambientales con Métricas Físicas Reales
        $modulesTelemetry = [
            [
                'key' => 'dosimetria',
                'name' => 'Dosimetría de Ruido',
                'metric_value' => '78.4',
                'metric_unit' => 'dB(A) Leq',
                'limit_text' => 'LMP: 85 dB(A)',
                'points_count' => '84 Puntos',
                'progress' => 85,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'SVANTEK SV104A',
                'theme' => 'cyan',
                'icon' => 'ear',
            ],
            [
                'key' => 'ruido_ambiental',
                'name' => 'Ruido Ambiental',
                'metric_value' => '58.2',
                'metric_unit' => 'dB(A) Ld/Ln',
                'limit_text' => 'LMP: 68 dB(A)',
                'points_count' => '62 Estaciones',
                'progress' => 100,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'Sonómetro NTi XL2',
                'theme' => 'lime',
                'icon' => 'speaker',
            ],
            [
                'key' => 'agua',
                'name' => 'Calidad de Agua',
                'metric_value' => '7.35',
                'metric_unit' => 'pH / 14 NTU',
                'limit_text' => 'Rango: 6.0 - 9.0 pH',
                'points_count' => '48 Efluentes',
                'progress' => 70,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'HANNA HI98194',
                'theme' => 'cyan',
                'icon' => 'droplet',
            ],
            [
                'key' => 'opacidad',
                'name' => 'Opacidad (Humos)',
                'metric_value' => '8.5%',
                'metric_unit' => 'Escala Ringelmann 1',
                'limit_text' => 'LMP: < 20%',
                'points_count' => '38 Chimeneas',
                'progress' => 90,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'Opacímetro Testo 308',
                'theme' => 'lime',
                'icon' => 'flame',
            ],
            [
                'key' => 'particulas',
                'name' => 'Partículas 24 Horas',
                'metric_value' => '42.0',
                'metric_unit' => 'µg/m³ PM10',
                'limit_text' => 'LMP: 150 µg/m³',
                'points_count' => '52 Muestras',
                'progress' => 40,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'Hi-Vol Tisch TE-6070',
                'theme' => 'cyan',
                'icon' => 'wind',
            ],
        ];

        // 4. Resumen Comparativo de Empresas Clientes
        $enterprisesStats = [
            ['name' => 'Minera San Cristóbal', 'key' => 'msc', 'projects' => 8, 'compliance' => 99.4, 'region' => 'Potosí', 'status' => 'Normal', 'theme' => 'cyan'],
            ['name' => 'Cervecería Boliviana Nacional', 'key' => 'cbn', 'projects' => 6, 'compliance' => 100.0, 'region' => 'Santa Cruz', 'status' => 'Normal', 'theme' => 'lime'],
            ['name' => 'Soboce Cemento & Hormigón', 'key' => 'soboce', 'projects' => 5, 'compliance' => 98.1, 'region' => 'La Paz', 'status' => 'Normal', 'theme' => 'cyan'],
            ['name' => 'YPFB Transporte', 'key' => 'ypfb', 'projects' => 9, 'compliance' => 97.5, 'region' => 'Santa Cruz', 'status' => 'Estable', 'theme' => 'lime'],
            ['name' => 'Pil Andina S.A.', 'key' => 'pil', 'projects' => 4, 'compliance' => 96.8, 'region' => 'Cochabamba', 'status' => 'Estable', 'theme' => 'cyan'],
        ];

        // 5. Parque de Equipos de Calibración & Certificaciones
        $equipmentInventory = [
            ['name' => 'Dosímetros Acústicos Svantek', 'total' => 6, 'valid' => 6, 'validity' => 'Vigente Dic 2025', 'theme' => 'cyan'],
            ['name' => 'Sonómetros Integradores Clase 1 NTi', 'total' => 4, 'valid' => 4, 'validity' => 'Vigente Nov 2025', 'theme' => 'lime'],
            ['name' => 'Sondas Multiparámetro de Agua Hanna', 'total' => 4, 'valid' => 4, 'validity' => 'Vigente Oct 2025', 'theme' => 'cyan'],
            ['name' => 'Opacímetros de Emisiones Testo/MRU', 'total' => 2, 'valid' => 2, 'validity' => 'Vigente Ene 2026', 'theme' => 'lime'],
            ['name' => 'Muestreadores de Aire Hi-Vol Tisch', 'total' => 2, 'valid' => 2, 'validity' => 'Vigente Feb 2026', 'theme' => 'cyan'],
        ];

        // 6. Proyectos Industriales en Monitoreo
        $projects = [
            [
                'id' => 1,
                'num' => '01',
                'company_key' => 'msc',
                'name' => 'Monitoreo de Relaves & Calidad Ambiental',
                'code' => 'PRJ-MSC-01',
                'client' => 'Minera San Cristóbal S.A.',
                'client_initial' => 'M',
                'client_theme' => 'cyan',
                'region' => 'Potosí',
                'modules_list' => ['Ruido', 'Agua', 'Partículas', 'Opacidad'],
                'points_text' => '24 de 28 pts',
                'points_pct' => 85,
                'compliance_pct' => '99.4%',
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
            ],
            [
                'id' => 2,
                'num' => '02',
                'company_key' => 'cbn',
                'name' => 'Automatización & Control Ambiental Cervecería',
                'code' => 'PRJ-CBN-02',
                'client' => 'Cervecería Boliviana Nacional',
                'client_initial' => 'C',
                'client_theme' => 'lime',
                'region' => 'Santa Cruz',
                'modules_list' => ['Ruido Ocupacional', 'Agua Efluentes', 'Emisiones'],
                'points_text' => '18 de 18 pts',
                'points_pct' => 100,
                'compliance_pct' => '100%',
                'status' => 'Completado',
                'status_type' => 'done',
            ],
            [
                'id' => 3,
                'num' => '03',
                'company_key' => 'soboce',
                'name' => 'Control de Emisiones en Hornos & Ruido',
                'code' => 'PRJ-SOB-03',
                'client' => 'Soboce Cemento & Hormigón',
                'client_initial' => 'S',
                'client_theme' => 'cyan',
                'region' => 'Viacha / La Paz',
                'modules_list' => ['Opacidad', 'Partículas 24h', 'Ruido'],
                'points_text' => '14 de 20 pts',
                'points_pct' => 70,
                'compliance_pct' => '98.1%',
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
            ],
            [
                'id' => 4,
                'num' => '04',
                'company_key' => 'ypfb',
                'name' => 'Telemetría de Gasoducto Central & Estaciones',
                'code' => 'PRJ-YPF-04',
                'client' => 'YPFB Transporte',
                'client_initial' => 'Y',
                'client_theme' => 'lime',
                'region' => 'Santa Cruz',
                'modules_list' => ['Ruido Ambiental', 'Agua'],
                'points_text' => '8 de 16 pts',
                'points_pct' => 50,
                'compliance_pct' => '97.5%',
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
            ],
            [
                'id' => 5,
                'num' => '05',
                'company_key' => 'pil',
                'name' => 'Control de Calidad de Efluentes & Cadena de Frío',
                'code' => 'PRJ-PIL-05',
                'client' => 'Pil Andina S.A.',
                'client_initial' => 'P',
                'client_theme' => 'cyan',
                'region' => 'Cochabamba',
                'modules_list' => ['Agua Efluentes', 'Ruido Ocupacional'],
                'points_text' => '4 de 12 pts',
                'points_pct' => 33,
                'compliance_pct' => '96.8%',
                'status' => 'Planificación',
                'status_type' => 'pending',
            ],
        ];

        return view('dashboard', compact(
            'companiesList',
            'systemHealth',
            'modulesTelemetry',
            'enterprisesStats',
            'equipmentInventory',
            'projects',
            'userName',
            'currentDate'
        ));
    }
}
