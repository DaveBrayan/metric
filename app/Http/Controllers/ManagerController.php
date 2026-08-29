<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ManagerController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $companies = Company::all();

        $managers = Manager::with('company')->get()->map(function ($m, $index) {
            return [
                'id' => $m->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $m->name,
                'email' => $m->email,
                'phone' => $m->phone,
                'initial' => strtoupper(substr($m->name, 0, 1)),
                'company' => $m->company ? $m->company->name : 'General',
                'company_theme' => $m->company ? ($m->company->theme ?? 'cyan') : 'cyan',
                'position' => $m->position,
                'status' => $m->status,
                'status_type' => ($m->status === 'Activo') ? 'done' : 'in_progress',
            ];
        })->toArray();

        return view('managers.index', compact('userName', 'userRole', 'managers', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'position' => 'required|string',
        ]);

        Manager::create($validated);

        return redirect()->route('managers.index')->with('success', 'Responsable registrado exitosamente.');
    }
}
