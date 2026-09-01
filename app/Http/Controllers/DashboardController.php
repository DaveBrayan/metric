<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Project;
use App\Models\MeasurementModule;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo Sirpa';
        $currentDate = now()->locale('es')->isoFormat('D [de] MMMM, YYYY');

        // 1. Selector de Empresa Global desde Base de Datos
        $companiesFromDb = Company::all();
        $companiesList = [
            ['id' => 'all', 'name' => 'Todas las Empresas (' . $companiesFromDb->count() . ' Clientes)']
        ];
        foreach ($companiesFromDb as $comp) {
            $companiesList[] = [
                'id' => strtolower($comp->code),
                'name' => $comp->name
            ];
        }

        // 2. Semáforo & Estado General del Sistema
        $totalProjects = Project::count();
        $avgCompliance = $totalProjects > 0 ? round(Project::avg('compliance_pct'), 1) : 98.6;
        $totalSensors = MeasurementModule::sum('points_total');

        $systemHealth = [
            'overall_compliance' => $avgCompliance,
            'compliance_label' => 'Óptimo (LMP Ley 1333)',
            'critical_alerts' => 0,
            'active_sensors' => $totalSensors,
            'calibrated_instruments' => 18,
        ];

        // 3. Tarjetas de las 5 Líneas Ambientales
        $modulesTelemetry = [
            [
                'key' => 'dosimetria',
                'name' => 'Dosimetría de Ruido',
                'metric_value' => '78.4',
                'metric_unit' => 'dB(A) Leq',
                'limit_text' => 'LMP: 85 dB(A)',
                'points_count' => MeasurementModule::where('key', 'dosimetria')->sum('points_total') . ' Puntos',
                'progress' => 85,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'SVANTEK SV104A',
                'theme' => 'cyan',
            ],
            [
                'key' => 'ruido_ambiental',
                'name' => 'Ruido Ambiental',
                'metric_value' => '58.2',
                'metric_unit' => 'dB(A) Ld/Ln',
                'limit_text' => 'LMP: 68 dB(A)',
                'points_count' => MeasurementModule::where('key', 'ruido_ambiental')->sum('points_total') . ' Estaciones',
                'progress' => 100,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'Sonómetro NTi XL2',
                'theme' => 'lime',
            ],
            [
                'key' => 'agua',
                'name' => 'Calidad de Agua',
                'metric_value' => '7.35',
                'metric_unit' => 'pH / 14 NTU',
                'limit_text' => 'Rango: 6.0 - 9.0 pH',
                'points_count' => MeasurementModule::where('key', 'agua')->sum('points_total') . ' Efluentes',
                'progress' => 70,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'HANNA HI98194',
                'theme' => 'cyan',
            ],
            [
                'key' => 'opacidad',
                'name' => 'Opacidad (Humos)',
                'metric_value' => '8.5%',
                'metric_unit' => 'Escala Ringelmann 1',
                'limit_text' => 'LMP: < 20%',
                'points_count' => MeasurementModule::where('key', 'opacidad')->sum('points_total') . ' Chimeneas',
                'progress' => 90,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'Opacímetro Testo 308',
                'theme' => 'lime',
            ],
            [
                'key' => 'particulas',
                'name' => 'Partículas 24 Horas',
                'metric_value' => '42.0',
                'metric_unit' => 'µg/m³ PM10',
                'limit_text' => 'LMP: 150 µg/m³',
                'points_count' => MeasurementModule::where('key', 'particulas')->sum('points_total') . ' Muestras',
                'progress' => 40,
                'status' => 'Conforme',
                'status_theme' => 'done',
                'equipment' => 'Hi-Vol Tisch TE-6070',
                'theme' => 'cyan',
            ],
        ];

        // 4. Parque de Equipos de Calibración
        $equipmentInventory = [
            ['name' => 'Dosímetros Acústicos Svantek', 'total' => 6, 'valid' => 6, 'validity' => 'Vigente Dic 2025', 'theme' => 'cyan'],
            ['name' => 'Sonómetros Integradores Clase 1 NTi', 'total' => 4, 'valid' => 4, 'validity' => 'Vigente Nov 2025', 'theme' => 'lime'],
            ['name' => 'Sondas Multiparámetro de Agua Hanna', 'total' => 4, 'valid' => 4, 'validity' => 'Vigente Oct 2025', 'theme' => 'cyan'],
            ['name' => 'Opacímetros de Emisiones Testo/MRU', 'total' => 2, 'valid' => 2, 'validity' => 'Vigente Ene 2026', 'theme' => 'lime'],
            ['name' => 'Muestreadores de Aire Hi-Vol Tisch', 'total' => 2, 'valid' => 2, 'validity' => 'Vigente Feb 2026', 'theme' => 'cyan'],
        ];

        // 5. Proyectos Industriales desde BD
        $projects = Project::with(['company', 'modules'])->get()->map(function ($prj, $index) {
            $totalPoints = $prj->points_total > 0 ? $prj->points_total : 20;
            $completedPoints = $prj->points_completed;
            $pct = round(($completedPoints / $totalPoints) * 100);

            return [
                'id' => $prj->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'company_key' => $prj->company ? strtolower($prj->company->code) : 'all',
                'name' => $prj->name,
                'code' => $prj->code,
                'client' => $prj->company ? $prj->company->name : 'General',
                'client_initial' => $prj->company ? strtoupper(substr($prj->company->name, 0, 1)) : 'G',
                'client_theme' => $prj->company ? ($prj->company->theme ?? 'cyan') : 'cyan',
                'modules_list' => $prj->modules->pluck('name')->toArray(),
                'points_text' => "$completedPoints de $totalPoints pts",
                'points_pct' => $pct,
                'compliance_pct' => $prj->compliance_pct . '%',
                'status' => $prj->status,
                'status_type' => $prj->status_type ?? 'in_progress',
            ];
        })->toArray();

        return view('dashboard', compact(
            'companiesList',
            'systemHealth',
            'modulesTelemetry',
            'equipmentInventory',
            'projects',
            'userName',
            'currentDate'
        ));
    }
}

