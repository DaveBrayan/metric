<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RegionController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';

        $regions = [
            [
                'id' => 1,
                'num' => '01',
                'name' => 'Regional La Paz (Sede Central)',
                'code' => 'LPZ-01',
                'initial' => 'LP',
                'theme' => 'cyan',
                'manager' => 'Ing. Reynaldo Sirpa',
                'address' => 'Av. 6 de Agosto #2450, Sopocachi',
                'assigned_projects' => '12 Proyectos Activos',
                'staff_count' => '18 Especialistas',
                'status' => 'Operativo',
                'status_type' => 'done',
            ],
            [
                'id' => 2,
                'num' => '02',
                'name' => 'Regional Santa Cruz',
                'code' => 'SCZ-02',
                'initial' => 'SC',
                'theme' => 'lime',
                'manager' => 'Ing. Carlos Mendoza',
                'address' => 'Parque Industrial PI-22, 4to Anillo',
                'assigned_projects' => '10 Proyectos Activos',
                'staff_count' => '14 Especialistas',
                'status' => 'Operativo',
                'status_type' => 'done',
            ],
            [
                'id' => 3,
                'num' => '03',
                'name' => 'Regional Cochabamba',
                'code' => 'CBB-03',
                'initial' => 'CB',
                'theme' => 'cyan',
                'manager' => 'Ing. Valeria Gutiérrez',
                'address' => 'Av. América Este #1020',
                'assigned_projects' => '6 Proyectos Activos',
                'staff_count' => '10 Especialistas',
                'status' => 'Operativo',
                'status_type' => 'done',
            ],
            [
                'id' => 4,
                'num' => '04',
                'name' => 'Regional Potosí (Distrito Minero)',
                'code' => 'PTS-04',
                'initial' => 'PT',
                'theme' => 'lime',
                'manager' => 'Ing. Diego Fernández',
                'address' => 'Zona San Cristóbal, Km 42',
                'assigned_projects' => '5 Proyectos Activos',
                'staff_count' => '4 Especialistas',
                'status' => 'Operativo',
                'status_type' => 'done',
            ],
            [
                'id' => 5,
                'num' => '05',
                'name' => 'Regional Oruro',
                'code' => 'ORU-05',
                'initial' => 'OR',
                'theme' => 'cyan',
                'manager' => 'Ing. Fernando Choque',
                'address' => 'Av. 24 de Junio, Zona Industrial',
                'assigned_projects' => '3 Proyectos Activos',
                'staff_count' => '2 Especialistas',
                'status' => 'Operativo',
                'status_type' => 'done',
            ],
        ];

        return view('regions.index', compact('userName', 'userRole', 'regions'));
    }

    public function store(Request $request)
    {
        return redirect()->route('regions.index')->with('success', 'Regional registrada exitosamente.');
    }
}
