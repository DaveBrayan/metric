<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        // Obtener usuarios reales de la base de datos
        $usersFromDb = User::all();

        // Si la base de datos estuviera vacía, suministrar al admin oficial
        if ($usersFromDb->isEmpty()) {
            $admins = [
                [
                    'id' => 1,
                    'num' => '01',
                    'name' => 'Reynaldo Sirpa',
                    'email' => 'admin@metric.com',
                    'phone' => '+591 715-10200',
                    'initial' => 'R',
                    'role' => 'Superadministrador',
                    'role_theme' => 'cyan',
                    'status' => 'online',
                    'status_label' => 'En línea',
                    'permissions' => ['Control Total', 'Empresas', 'Responsables', 'Personal', 'Proyectos', 'Módulos', 'Telemetría', 'Configuración'],
                ]
            ];
        } else {
            $admins = $usersFromDb->map(function ($u, $index) {
                $rawPerms = is_array($u->permissions) ? $u->permissions : (json_decode($u->permissions, true) ?? ['Control Total']);
                return [
                    'id' => $u->id,
                    'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone ?? '—',
                    'initial' => strtoupper(substr($u->name, 0, 1)),
                    'role' => $u->role ?? 'Superadministrador',
                    'role_theme' => ($u->role === 'Superadministrador') ? 'cyan' : (($u->role === 'Operador de Planta') ? 'lime' : 'amber'),
                    'status' => $u->status ?? 'online',
                    'status_label' => ($u->status === 'online') ? 'En línea' : 'Inactivo',
                    'permissions' => $rawPerms,
                ];
            })->toArray();
        }

        return view('admins.index', compact('userName', 'userRole', 'admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string',
            'role' => 'required|string',
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
        ]);

        $rawPassword = $validated['password'] ?? 'Metric2026#' . Str::random(4);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($rawPassword),
            'role' => $validated['role'],
            'role_theme' => ($validated['role'] === 'Superadministrador') ? 'cyan' : 'lime',
            'status' => 'online',
            'permissions' => $validated['permissions'] ?? ['Telemetría', 'Reportes'],
        ]);

        return redirect()->route('admins.index')->with('success', 'Administrador registrado exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string',
            'role' => 'required|string',
            'status' => 'required|in:online,offline',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => $validated['role'],
            'role_theme' => ($validated['role'] === 'Superadministrador') ? 'cyan' : 'lime',
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Datos actualizados correctamente.', 'user' => $user]);
        }

        return redirect()->route('admins.index')->with('success', 'Administrador actualizado correctamente.');
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'new_password' => 'required|string|min:6',
        ]);

        $user->password = Hash::make($validated['new_password']);
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contraseña restablecida exitosamente.',
                'email' => $user->email,
                'name' => $user->name,
            ]);
        }

        return redirect()->route('admins.index')->with('success', 'Contraseña restablecida exitosamente para ' . $user->name);
    }

    public function updatePermissions(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $permissions = $request->input('permissions', []);
        $user->permissions = $permissions;
        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Matriz de permisos actualizada exitosamente.',
                'permissions' => $user->permissions,
            ]);
        }

        return redirect()->route('admins.index')->with('success', 'Permisos de ' . $user->name . ' actualizados.');
    }

    public function destroy(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('admins.index')->with('info', 'El usuario ya ha sido eliminado.');
        }

        // Prevenir auto-eliminación del usuario en sesión
        if (Auth::id() == $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta en sesión activa.'], 403);
            }
            return redirect()->route('admins.index')->with('error', 'No puedes eliminar tu propia cuenta en sesión activa.');
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Administrador eliminado exitosamente.']);
        }

        return redirect()->route('admins.index')->with('success', 'Administrador eliminado exitosamente.');
    }
}
