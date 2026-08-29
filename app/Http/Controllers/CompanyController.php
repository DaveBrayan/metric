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
            $isActive = in_array(strtolower($comp->status), ['activo', 'online']);
            return [
                'id' => $comp->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $comp->name,
                'code' => $comp->code,
                'city' => $comp->code ?? 'Nacional',
                'nit' => $comp->nit ?? '—',
                'initial' => strtoupper(substr($comp->name, 0, 1)),
                'industry' => $comp->industry ?? 'Minería & Energía',
                'theme' => $comp->theme ?? 'cyan',
                'contact_name' => $comp->contact_person ?? '—',
                'contact_person' => $comp->contact_person ?? '—',
                'contact_email' => $comp->email ?? '—',
                'email' => $comp->email ?? '—',
                'phone' => $comp->phone ?? '—',
                'status' => $isActive ? 'Activo' : 'Inactivo',
                'status_type' => $isActive ? 'done' : 'pending',
                'projects_count' => ($comp->projects_count ?? 0) . ' Proyectos',
                'active_projects' => ($comp->projects_count ?? 0) . ' Proyectos',
                'total_plants' => ($comp->managers_count ?? 0) . ' Plantas',
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
            'status' => 'nullable|in:Activo,Inactivo',
        ]);

        Company::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'industry' => $validated['industry'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'theme' => 'cyan',
            'status' => $validated['status'] ?? 'Activo',
        ]);

        return redirect()->route('companies.index')->with('success', 'Empresa cliente registrada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:companies,code,' . $company->id,
            'industry' => 'required|string',
            'contact_person' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'status' => 'required|in:Activo,Inactivo',
        ]);

        $company->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'industry' => $validated['industry'],
            'contact_person' => $validated['contact_person'] ?? null,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Empresa actualizada exitosamente.']);
        }

        return redirect()->route('companies.index')->with('success', 'Empresa actualizada exitosamente.');
    }

    public function destroy(Request $request, $id)
    {
        $company = Company::find($id);

        if ($company) {
            $company->delete();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Empresa eliminada exitosamente.']);
        }

        return redirect()->route('companies.index')->with('success', 'Empresa eliminada exitosamente.');
    }
}
