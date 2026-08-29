<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';

        $projects = [
            [
                'id' => 1,
                'num' => '01',
                'name' => 'Monitoreo de Relaves & Calidad Ambiental',
                'code' => 'PRJ-MSC-01',
                'client' => 'Minera San Cristóbal S.A.',
                'client_initial' => 'M',
                'client_theme' => 'cyan',
                'region' => 'Potosí',
                'modules_completed_text' => '4 de 5 Módulos',
                'modules_ratio_pct' => 80,
                'progress' => 85,
                'progress_theme' => 'cyan',
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
            ],
            [
                'id' => 2,
                'num' => '02',
                'name' => 'Automatización & Control Ambiental Cervecería',
                'code' => 'PRJ-CBN-02',
                'client' => 'Cervecería Boliviana Nacional',
                'client_initial' => 'C',
                'client_theme' => 'lime',
                'region' => 'Santa Cruz',
                'modules_completed_text' => '5 de 5 Módulos',
                'modules_ratio_pct' => 100,
                'progress' => 100,
                'progress_theme' => 'lime',
                'status' => 'Completado',
                'status_type' => 'done',
            ],
            [
                'id' => 3,
                'num' => '03',
                'name' => 'Control de Emisiones en Hornos & Ruido',
                'code' => 'PRJ-SOB-03',
                'client' => 'Soboce Cemento & Hormigón',
                'client_initial' => 'S',
                'client_theme' => 'cyan',
                'region' => 'Viacha / La Paz',
                'modules_completed_text' => '3 de 5 Módulos',
                'modules_ratio_pct' => 60,
                'progress' => 70,
                'progress_theme' => 'cyan',
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
            ],
            [
                'id' => 4,
                'num' => '04',
                'name' => 'Telemetría de Gasoducto & Estaciones de Bombeo',
                'code' => 'PRJ-YPF-04',
                'client' => 'YPFB Transporte',
                'client_initial' => 'Y',
                'client_theme' => 'lime',
                'region' => 'Santa Cruz',
                'modules_completed_text' => '2 de 5 Módulos',
                'modules_ratio_pct' => 40,
                'progress' => 45,
                'progress_theme' => 'cyan',
                'status' => 'En Ejecución',
                'status_type' => 'in_progress',
            ],
            [
                'id' => 5,
                'num' => '05',
                'name' => 'Control de Calidad de Efluentes & Cadena de Frío',
                'code' => 'PRJ-PIL-05',
                'client' => 'Pil Andina S.A.',
                'client_initial' => 'P',
                'client_theme' => 'cyan',
                'region' => 'Cochabamba',
                'modules_completed_text' => '1 de 5 Módulos',
                'modules_ratio_pct' => 20,
                'progress' => 20,
                'progress_theme' => 'cyan',
                'status' => 'Planificación',
                'status_type' => 'pending',
            ],
        ];

        return view('projects.index', compact('userName', 'userRole', 'projects'));
    }

    /**
     * Environmental & Industrial Measurement Modules Table
     */
    public function modules(Request $request)
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';
        $selectedModule = $request->query('tipo', 'todos');

        $modulesData = [
            [
                'id' => 1,
                'num' => '01',
                'module_key' => 'dosimetria',
                'module_name' => 'Dosimetría de Ruido',
                'module_sub' => 'Dosis Ocupacional Leq dB(A) / 8 hrs',
                'module_badge_theme' => 'cyan',
                'equipment' => 'Dosímetro SVANTEK SV104A (Calib. 2025)',
                'staff_name' => 'Carla Villarroel',
                'staff_initial' => 'C',
                'staff_theme' => 'cyan',
                'staff_role' => 'Especialista en Acústica',
                'points_text' => '10 de 12 puntos',
                'points_pct' => 83,
                'progress' => 85,
                'progress_theme' => 'cyan',
                'status' => 'En Ejecución',
                'status_theme' => 'in_progress',
            ],
            [
                'id' => 2,
                'num' => '02',
                'module_key' => 'ruido_ambiental',
                'module_name' => 'Ruido Ambiental',
                'module_sub' => 'Nivel Sonoro Diurno & Nocturno Ld/Ln',
                'module_badge_theme' => 'lime',
                'equipment' => 'Sonómetro Integrador NTi XL2 Clase 1',
                'staff_name' => 'Gonzalo Arnez',
                'staff_initial' => 'G',
                'staff_theme' => 'lime',
                'staff_role' => 'Ing. Ambiental de Campo',
                'points_text' => '8 de 8 estaciones',
                'points_pct' => 100,
                'progress' => 100,
                'progress_theme' => 'lime',
                'status' => 'Completado',
                'status_theme' => 'done',
            ],
            [
                'id' => 3,
                'num' => '03',
                'module_key' => 'agua',
                'module_name' => 'Agua (Parámetros de Campo)',
                'module_badge_theme' => 'cyan',
                'module_sub' => 'pH, Conductividad, OD, Turbidez',
                'equipment' => 'Multiparámetro HANNA HI98194 + HACH',
                'staff_name' => 'Mauricio Beltrán',
                'staff_initial' => 'M',
                'staff_theme' => 'cyan',
                'staff_role' => 'Analista Químico',
                'points_text' => '4 de 6 puntos',
                'points_pct' => 66,
                'progress' => 70,
                'progress_theme' => 'cyan',
                'status' => 'En Ejecución',
                'status_theme' => 'in_progress',
            ],
            [
                'id' => 4,
                'num' => '04',
                'module_key' => 'opacidad',
                'module_name' => 'Opacidad (Humos / Emisiones)',
                'module_badge_theme' => 'lime',
                'module_sub' => 'Escala Ringelmann & % Densidad',
                'equipment' => 'Opacímetro Testo 308 + MRU Nova',
                'staff_name' => 'Fernando Choque',
                'staff_initial' => 'F',
                'staff_theme' => 'lime',
                'staff_role' => 'Técnico Emisiones',
                'points_text' => '4 de 4 chimeneas',
                'points_pct' => 100,
                'progress' => 90,
                'progress_theme' => 'lime',
                'status' => 'Completado',
                'status_theme' => 'done',
            ],
            [
                'id' => 5,
                'num' => '05',
                'module_key' => 'particulas',
                'module_name' => 'Partículas 24 Horas',
                'module_badge_theme' => 'cyan',
                'module_sub' => 'Material Particulado PM10 & PM2.5 (24h)',
                'equipment' => 'Muestreador Hi-Vol Tisch TE-6070',
                'staff_name' => 'Diego Fernández',
                'staff_initial' => 'D',
                'staff_theme' => 'cyan',
                'staff_role' => 'Supervisor Calidad Aire',
                'points_text' => '2 de 6 estaciones',
                'points_pct' => 33,
                'progress' => 40,
                'progress_theme' => 'cyan',
                'status' => 'Planificación',
                'status_theme' => 'pending',
            ],
        ];

        return view('projects.modules', compact('userName', 'userRole', 'modulesData', 'selectedModule'));
    }

    public function store(Request $request)
    {
        return redirect()->route('projects.index')->with('success', 'Proyecto registrado exitosamente.');
    }
}
