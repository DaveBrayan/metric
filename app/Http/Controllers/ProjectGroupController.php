<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectGroupController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';

        $kpis = [
            [
                'title' => 'Grupos de Proyectos',
                'value' => '6',
                'growth' => 'Clústeres Activos',
                'icon' => 'shield',
                'theme' => 'cyan',
            ],
            [
                'title' => 'Proyectos Totales',
                'value' => '36',
                'growth' => 'Distribución 100%',
                'icon' => 'activity',
                'theme' => 'lime',
            ],
            [
                'title' => 'Presupuesto Consolidado',
                'value' => '$420K',
                'growth' => '+14.5% vs Q1',
                'icon' => 'lock',
                'theme' => 'cyan',
            ],
            [
                'title' => 'Rendimiento Promedio',
                'value' => '94.2%',
                'growth' => 'Alta Eficiencia',
                'icon' => 'users',
                'theme' => 'lime',
            ],
        ];

        $groups = [
            [
                'id' => 1,
                'name' => 'Automatización SCADA & PLC',
                'code' => 'GRP-SCADA',
                'description' => 'Sistemas de control distribuido, interfaces HMI y PLC industriales para líneas de producción continua.',
                'theme' => 'cyan',
                'projects_count' => 12,
                'budget' => '$160,000',
                'lead_engineer' => 'Gonzalo Arnez',
                'progress' => 82,
                'status' => 'active',
                'status_label' => 'En Ejecución',
            ],
            [
                'id' => 2,
                'name' => 'Telemetría IoT & Sensores LoRaWAN',
                'code' => 'GRP-IOT',
                'description' => 'Redes de sensores inalámbricos, gateways industriales y monitoreo de variables ambientales en tiempo real.',
                'theme' => 'lime',
                'projects_count' => 9,
                'budget' => '$95,000',
                'lead_engineer' => 'Carla Villarroel',
                'progress' => 74,
                'status' => 'active',
                'status_label' => 'En Ejecución',
            ],
            [
                'id' => 3,
                'name' => 'Minería & Extracción Inteligente',
                'code' => 'GRP-MINE',
                'description' => 'Monitoreo de maquinaria pesada, control de ductos y telemetría de relaves mineros con alta tolerancia a fallos.',
                'theme' => 'cyan',
                'projects_count' => 7,
                'budget' => '$110,000',
                'lead_engineer' => 'Diego Fernández',
                'progress' => 90,
                'status' => 'active',
                'status_label' => 'En Ejecución',
            ],
            [
                'id' => 4,
                'name' => 'Eficiencia Energética & Paneles Solares',
                'code' => 'GRP-ENERGY',
                'description' => 'Sistemas de autogeneración solar, bancos de condensadores y auditoría de consumo eléctrico por kilovatio/hora.',
                'theme' => 'lime',
                'projects_count' => 5,
                'budget' => '$40,000',
                'lead_engineer' => 'Mauricio Beltrán',
                'progress' => 60,
                'status' => 'active',
                'status_label' => 'En Ejecución',
            ],
            [
                'id' => 5,
                'name' => 'Software & Analítica Predictiva',
                'code' => 'GRP-DATA',
                'description' => 'Algoritmos de machine learning para detección temprana de anomalías en turbinas y motores industriales.',
                'theme' => 'cyan',
                'projects_count' => 3,
                'budget' => '$25,000',
                'lead_engineer' => 'Valeria Gutiérrez',
                'progress' => 45,
                'status' => 'active',
                'status_label' => 'Planificación',
            ],
        ];

        return view('project_groups.index', compact('userName', 'userRole', 'kpis', 'groups'));
    }

    public function store(Request $request)
    {
        return redirect()->route('project_groups.index')->with('success', 'Grupo de proyectos creado exitosamente.');
    }
}
