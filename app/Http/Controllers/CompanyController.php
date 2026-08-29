<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $companies = Company::withCount(['projects', 'managers'])->get()->map(function ($comp, $index) {
            return [
                'id' => $comp->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $comp->name,
                'code' => $comp->code,
                'initial' => strtoupper(substr($comp->name, 0, 1)),
                'industry' => $comp->industry,
                'theme' => $comp->theme ?? 'cyan',
                'contact_person' => $comp->contact_person,
                'email' => $comp->email,
                'phone' => $comp->phone,
                'status' => $comp->status,
                'status_type' => ($comp->status === 'Activo') ? 'done' : 'in_progress',
                'active_projects' => $comp->projects_count . ' Proyectos',
                'total_plants' => $comp->managers_count . ' Plantas',
            ];
        })->toArray();

        return view('companies.index', compact('userName', 'userRole', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:companies,code',
            'industry' => 'required|string',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
        ]);

        Company::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'industry' => $validated['industry'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'theme' => 'cyan',
            'status' => 'Activo',
        ]);

        return redirect()->route('companies.index')->with('success', 'Empresa registrada exitosamente.');
    }
}
