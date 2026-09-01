<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $companies = Company::all();

        $managers = Manager::with('company')->withCount('projects')->get()->map(function ($m, $index) {
            $companyName = $m->company ? $m->company->name : 'General';
            $isActive = in_array(strtolower($m->status), ['activo', 'online']);
            return [
                'id' => $m->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $m->name,
                'email' => $m->email,
                'phone' => $m->phone ?? '—',
                'company_id' => $m->company_id,
                'initial' => strtoupper(substr($m->name, 0, 1)),
                'theme' => 'cyan',
                'company' => $companyName,
                'company_initial' => strtoupper(substr($companyName, 0, 1)),
                'company_theme' => $m->company ? ($m->company->theme ?? 'cyan') : 'cyan',
                'projects_count' => ($m->projects_count ?? 0) . ' Proyectos',
                'position' => $m->position ?? 'Gerente de Operaciones',
                'status' => $isActive ? 'Activo' : 'Inactivo',
                'status_type' => $isActive ? 'done' : 'pending',
            ];
        })->toArray();

        return view('managers.index', compact('userName', 'userRole', 'managers', 'companies'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:managers,email',
            'phone' => 'nullable|string',
            'position' => 'required|string',
            'status' => 'nullable|in:Activo,Inactivo',
        ]);

        $manager = Manager::create([
            'company_id' => $validated['company_id'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'position' => $validated['position'],
            'status' => $validated['status'] ?? 'Activo',
        ]);

        return redirect()->route('managers.index')->with('success', 'Responsable de planta registrado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $manager = Manager::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:managers,email,' . $manager->id,
            'phone' => 'nullable|string',
            'position' => 'required|string',
            'status' => 'required|in:Activo,Inactivo',
        ]);

        $manager->update($validated);

        // Si tiene cuenta de usuario vinculada, sincronizar su estado
        $user = User::where('email', $manager->email)->first();
        if ($user) {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'status' => ($validated['status'] === 'Activo') ? 'online' : 'offline',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Responsable actualizado correctamente.']);
        }

        return redirect()->route('managers.index')->with('success', 'Responsable de planta actualizado exitosamente.');
    }

    public function resetPassword(Request $request, $id)
    {
        $manager = Manager::findOrFail($id);

        $validated = $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        // Crear o actualizar usuario en la tabla users para login
        $user = User::updateOrCreate(
            ['email' => $manager->email],
            [
                'name' => $manager->name,
                'password' => Hash::make($validated['new_password']),
                'role' => 'Responsable de Planta',
                'role_theme' => 'lime',
                'status' => ($manager->status === 'Activo') ? 'online' : 'offline',
                'phone' => $manager->phone,
                'permissions' => ['Empresas - Ver', 'Proyectos - Ver', 'Telemetría - Ver', 'Telemetría - Reportes'],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida exitosamente.',
                'email' => $manager->email,
                'name' => $manager->name,
            ]);
        }

        return redirect()->route('managers.index')->with('success', 'Contraseña restablecida para ' . $manager->name);
    }

    public function destroy(Request $request, $id)
    {
        $manager = Manager::find($id);
        
        if ($manager) {
            User::where('email', $manager->email)->delete();
            $manager->delete();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Responsable eliminado exitosamente.']);
        }

        return redirect()->route('managers.index')->with('success', 'Responsable eliminado exitosamente.');
    }
}
