<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $regions = Region::all();

        $staff = Staff::with('region')->get()->map(function ($s, $index) {
            $isActive = in_array(strtolower($s->status), ['online', 'activo']);
            return [
                'id' => $s->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $s->name,
                'email' => $s->email,
                'phone' => $s->phone ?? '—',
                'region_id' => $s->region_id,
                'initial' => strtoupper(substr($s->name, 0, 1)),
                'department' => $s->department ?? 'Operaciones & Campo',
                'position' => $s->position ?? 'Especialista en Sensores',
                'region' => $s->region ? $s->region->name : 'Sede Central',
                'role_theme' => $s->role_theme ?? 'cyan',
                'status' => $isActive ? 'online' : 'offline',
                'status_label' => $isActive ? ($s->status_label ?? 'En Planta') : 'Inactivo',
            ];
        })->toArray();

        return view('staff.index', compact('userName', 'userRole', 'staff', 'regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'nullable|string',
            'department' => 'required|string',
            'position' => 'required|string',
            'region_id' => 'nullable|exists:regions,id',
            'status' => 'nullable|in:online,offline',
        ]);

        Staff::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'],
            'position' => $validated['position'],
            'region_id' => $validated['region_id'] ?? null,
            'role_theme' => 'cyan',
            'status' => $validated['status'] ?? 'online',
            'status_label' => ($validated['status'] ?? 'online') === 'online' ? 'En Planta' : 'Inactivo',
        ]);

        return redirect()->route('staff.index')->with('success', 'Colaborador técnico registrado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email,' . $staff->id,
            'phone' => 'nullable|string',
            'department' => 'required|string',
            'position' => 'required|string',
            'region_id' => 'nullable|exists:regions,id',
            'status' => 'required|in:online,offline',
        ]);

        $staff->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'],
            'position' => $validated['position'],
            'region_id' => $validated['region_id'] ?? null,
            'status' => $validated['status'],
            'status_label' => ($validated['status'] === 'online') ? 'En Planta' : 'Inactivo',
        ]);

        // Sincronizar cuenta de usuario si existe
        $user = User::where('email', $staff->email)->first();
        if ($user) {
            $user->update([
                'name' => $validated['name'],
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Colaborador actualizado correctamente.']);
        }

        return redirect()->route('staff.index')->with('success', 'Colaborador técnico actualizado correctamente.');
    }

    public function resetPassword(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        // Crear o actualizar usuario en la tabla users para login
        $user = User::updateOrCreate(
            ['email' => $staff->email],
            [
                'name' => $staff->name,
                'password' => Hash::make($validated['new_password']),
                'role' => 'Especialista de Campo',
                'role_theme' => 'cyan',
                'status' => $staff->status ?? 'online',
                'phone' => $staff->phone,
                'permissions' => ['Personal - Ver', 'Módulos - Ver', 'Telemetría - Ver', 'Telemetría - Cargar'],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida exitosamente.',
                'email' => $staff->email,
                'name' => $staff->name,
            ]);
        }

        return redirect()->route('staff.index')->with('success', 'Contraseña restablecida para ' . $staff->name);
    }

    public function destroy(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        
        // Eliminar también acceso de usuario si existe
        User::where('email', $staff->email)->delete();
        $staff->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Colaborador eliminado exitosamente.']);
        }

        return redirect()->route('staff.index')->with('success', 'Colaborador eliminado exitosamente.');
    }
}
