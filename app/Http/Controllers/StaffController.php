<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';

        $staff = [
            [
                'id' => 1,
                'num' => '01',
                'name' => 'Reynaldo Sirpa',
                'email' => 'reynaldo.s@pachabol.com',
                'initial' => 'R',
                'role_theme' => 'cyan',
                'department' => 'Dirección de Operaciones & Monitoreo',
                'position' => 'Director Técnico de Monitoreo',
                'region' => 'La Paz (Central)',
                'status' => 'online',
                'status_label' => 'En Planta',
                'phone' => '+591 715-10200',
            ],
            [
                'id' => 2,
                'num' => '02',
                'name' => 'Gonzalo Arnez',
                'email' => 'gonzalo.a@pachabol.com',
                'initial' => 'G',
                'role_theme' => 'lime',
                'department' => 'Ingeniería de Automatización',
                'position' => 'Ingeniero Senior SCADA',
                'region' => 'La Paz (Central)',
                'status' => 'online',
                'status_label' => 'En Planta',
                'phone' => '+591 715-44210',
            ],
            [
                'id' => 3,
                'num' => '03',
                'name' => 'Carla Villarroel',
                'email' => 'carla.v@pachabol.com',
                'initial' => 'C',
                'role_theme' => 'cyan',
                'department' => 'Telemetría & IoT',
                'position' => 'Especialista en Sensores',
                'region' => 'Santa Cruz',
                'status' => 'online',
                'status_label' => 'En Campo',
                'phone' => '+591 721-88901',
            ],
            [
                'id' => 4,
                'num' => '04',
                'name' => 'Mauricio Beltrán',
                'email' => 'mauricio.b@pachabol.com',
                'initial' => 'M',
                'role_theme' => 'lime',
                'department' => 'Mantenimiento Industrial',
                'position' => 'Jefe de Cuadrilla',
                'region' => 'Cochabamba',
                'status' => 'online',
                'status_label' => 'En Planta',
                'phone' => '+591 764-11234',
            ],
            [
                'id' => 5,
                'num' => '05',
                'name' => 'Silvia Torrico',
                'email' => 'silvia.t@pachabol.com',
                'initial' => 'S',
                'role_theme' => 'cyan',
                'department' => 'Control de Calidad',
                'position' => 'Auditora de Métricas',
                'region' => 'La Paz',
                'status' => 'offline',
                'status_label' => 'Descanso',
                'phone' => '+591 706-99321',
            ],
            [
                'id' => 6,
                'num' => '06',
                'name' => 'Fernando Choque',
                'email' => 'fernando.c@pachabol.com',
                'initial' => 'F',
                'role_theme' => 'lime',
                'department' => 'Infraestructura TI',
                'position' => 'Técnico de Redes IoT',
                'region' => 'Oruro',
                'status' => 'online',
                'status_label' => 'En Planta',
                'phone' => '+591 732-55412',
            ],
        ];

        return view('staff.index', compact('userName', 'userRole', 'staff'));
    }

    public function store(Request $request)
    {
        return redirect()->route('staff.index')->with('success', 'Colaborador registrado exitosamente.');
    }
}
