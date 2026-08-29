<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $userName = 'Reynaldo';
        $userRole = 'Administrador';

        $companies = [
            [
                'id' => 1,
                'num' => '01',
                'name' => 'Minera San Cristóbal S.A.',
                'nit' => '1028394021',
                'initial' => 'M',
                'theme' => 'cyan',
                'industry' => 'Minería & Extracción',
                'contact_name' => 'Ing. Roberto Paz',
                'contact_email' => 'rpaz@minerasancristobal.com',
                'projects_count' => '8 Proyectos',
                'status' => 'Activo',
                'city' => 'Potosí',
            ],
            [
                'id' => 2,
                'num' => '02',
                'name' => 'Cervecería Boliviana Nacional',
                'nit' => '1019283745',
                'initial' => 'C',
                'theme' => 'lime',
                'industry' => 'Bebidas & Alimentos',
                'contact_name' => 'Lic. Marcela Ríos',
                'contact_email' => 'mrios@cbn.bo',
                'projects_count' => '6 Proyectos',
                'status' => 'Activo',
                'city' => 'La Paz / Santa Cruz',
            ],
            [
                'id' => 3,
                'num' => '03',
                'name' => 'Soboce Cemento & Hormigón',
                'nit' => '1002938471',
                'initial' => 'S',
                'theme' => 'cyan',
                'industry' => 'Construcción & Cemento',
                'contact_name' => 'Ing. Javier Claros',
                'contact_email' => 'jclaros@soboce.com',
                'projects_count' => '5 Proyectos',
                'status' => 'Activo',
                'city' => 'Viacha / La Paz',
            ],
            [
                'id' => 4,
                'num' => '04',
                'name' => 'YPFB Transporte & Logística',
                'nit' => '1020394856',
                'initial' => 'Y',
                'theme' => 'lime',
                'industry' => 'Petróleo & Gas',
                'contact_name' => 'Ing. Daniel Soto',
                'contact_email' => 'dsoto@ypfbtransporte.com.bo',
                'projects_count' => '9 Proyectos',
                'status' => 'Activo',
                'city' => 'Santa Cruz',
            ],
            [
                'id' => 5,
                'num' => '05',
                'name' => 'Pil Andina S.A.',
                'nit' => '1011223344',
                'initial' => 'P',
                'theme' => 'cyan',
                'industry' => 'Agroindustria & Lácteos',
                'contact_name' => 'Dra. Andrea Morales',
                'contact_email' => 'amorales@pilandina.com.bo',
                'projects_count' => '4 Proyectos',
                'status' => 'Activo',
                'city' => 'Cochabamba',
            ],
        ];

        return view('companies.index', compact('userName', 'userRole', 'companies'));
    }

    public function store(Request $request)
    {
        return redirect()->route('companies.index')->with('success', 'Empresa registrada exitosamente.');
    }
}
