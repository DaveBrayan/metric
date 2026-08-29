<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';

        $admins = [
            [
                'id' => 1,
                'num' => '01',
                'name' => 'Reynaldo Sirpa',
                'email' => 'reynaldo.s@pachabol.com',
                'initial' => 'R',
                'role' => 'Superadministrador',
                'role_theme' => 'cyan',
                'status' => 'online',
                'status_label' => 'En línea',
                'permissions' => ['Control Total', 'Telemetría', 'Finanzas', 'Configuración'],
            ],
            [
                'id' => 2,
                'num' => '02',
                'name' => 'Carlos Mendoza',
                'email' => 'carlos.m@pachabol.com',
                'initial' => 'C',
                'role' => 'Operador de Planta',
                'role_theme' => 'lime',
                'status' => 'online',
                'status_label' => 'En línea',
                'permissions' => ['Telemetría', 'Control de Sensores', 'Reportes Diarios'],
            ],
            [
                'id' => 3,
                'num' => '03',
                'name' => 'Valeria Gutiérrez',
                'email' => 'valeria.g@pachabol.com',
                'initial' => 'V',
                'role' => 'Analista de Datos',
                'role_theme' => 'cyan',
                'status' => 'online',
                'status_label' => 'En línea',
                'permissions' => ['Reportes Ejecutivos', 'Métricas de Producción'],
            ],
            [
                'id' => 4,
                'num' => '04',
                'name' => 'Diego Fernández',
                'email' => 'diego.f@pachabol.com',
                'initial' => 'D',
                'role' => 'Supervisor Técnico',
                'role_theme' => 'lime',
                'status' => 'offline',
                'status_label' => 'Inactivo',
                'permissions' => ['Telemetría', 'Mantenimiento de Planta'],
            ],
            [
                'id' => 5,
                'num' => '05',
                'name' => 'Luciana Morales',
                'email' => 'luciana.m@pachabol.com',
                'initial' => 'L',
                'role' => 'Administradora de Cuentas',
                'role_theme' => 'cyan',
                'status' => 'offline',
                'status_label' => 'Inactivo',
                'permissions' => ['Clientes', 'Facturación', 'Proyectos'],
            ],
            [
                'id' => 6,
                'num' => '06',
                'name' => 'Alejandro Ramos',
                'email' => 'alejandro.r@pachabol.com',
                'initial' => 'A',
                'role' => 'Soporte y Seguridad TI',
                'role_theme' => 'slate',
                'status' => 'offline',
                'status_label' => 'Inactivo',
                'permissions' => ['Auditoría', 'Registros de Seguridad', 'Backups'],
            ],
        ];

        return view('admins.index', compact('userName', 'userRole', 'admins'));
    }

    public function store(Request $request)
    {
        return redirect()->route('admins.index')->with('success', 'Administrador creado y credenciales enviadas correctamente.');
    }
}
