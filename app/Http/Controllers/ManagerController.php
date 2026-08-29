<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ManagerController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';

        $managers = [
            [
                'id' => 1,
                'num' => '01',
                'name' => 'Ing. Fernando Mendoza',
                'email' => 'fmendoza@minerasancristobal.com',
                'initial' => 'F',
                'theme' => 'cyan',
                'position' => 'Gerente de Seguridad & Medio Ambiente',
                'company' => 'Minera San Cristóbal S.A.',
                'company_initial' => 'M',
                'company_theme' => 'cyan',
                'region' => 'Potosí',
                'projects_count' => '3 Proyectos',
                'phone' => '+591 715-22340',
                'status' => 'Activo',
                'status_type' => 'done',
            ],
            [
                'id' => 2,
                'num' => '02',
                'name' => 'Lic. Claudia Salinas',
                'email' => 'csalinas@cbn.bo',
                'initial' => 'C',
                'theme' => 'lime',
                'position' => 'Supervisora de Calidad & Efluentes',
                'company' => 'Cervecería Boliviana Nacional',
                'company_initial' => 'C',
                'company_theme' => 'lime',
                'region' => 'Santa Cruz',
                'projects_count' => '2 Proyectos',
                'phone' => '+591 721-99812',
                'status' => 'Activo',
                'status_type' => 'done',
            ],
            [
                'id' => 3,
                'num' => '03',
                'name' => 'Ing. Rodrigo Alarcón',
                'email' => 'ralarcon@soboce.com',
                'initial' => 'R',
                'theme' => 'cyan',
                'position' => 'Jefe de Planta & Emisiones Térmicas',
                'company' => 'Soboce Cemento & Hormigón',
                'company_initial' => 'S',
                'company_theme' => 'cyan',
                'region' => 'Viacha / La Paz',
                'projects_count' => '2 Proyectos',
                'phone' => '+591 764-55102',
                'status' => 'Activo',
                'status_type' => 'done',
            ],
            [
                'id' => 4,
                'num' => '04',
                'name' => 'Ing. Gabriela Paredes',
                'email' => 'gparedes@ypfbtransporte.com.bo',
                'initial' => 'G',
                'theme' => 'lime',
                'position' => 'Coordinadora de Telemetría de Ductos',
                'company' => 'YPFB Transporte',
                'company_initial' => 'Y',
                'company_theme' => 'lime',
                'region' => 'Santa Cruz',
                'projects_count' => '4 Proyectos',
                'phone' => '+591 732-66781',
                'status' => 'Activo',
                'status_type' => 'done',
            ],
            [
                'id' => 5,
                'num' => '05',
                'name' => 'Dr. Marcelo Quiroga',
                'email' => 'mquiroga@pilandina.com.bo',
                'initial' => 'M',
                'theme' => 'cyan',
                'position' => 'Director de Control Sanitario & Frío',
                'company' => 'Pil Andina S.A.',
                'company_initial' => 'P',
                'company_theme' => 'cyan',
                'region' => 'Cochabamba',
                'projects_count' => '1 Proyecto',
                'phone' => '+591 706-11943',
                'status' => 'En Turno',
                'status_type' => 'in_progress',
            ],
        ];

        return view('managers.index', compact('userName', 'userRole', 'managers'));
    }

    public function store(Request $request)
    {
        return redirect()->route('managers.index')->with('success', 'Responsable registrado exitosamente.');
    }
}
