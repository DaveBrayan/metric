<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        // Obtener usuarios reales de la base de datos
        $usersFromDb = User::all();

        // Si la base de datos aún no tiene registros, mostrar al único administrador oficial
        if ($usersFromDb->isEmpty()) {
            $admins = [
                [
                    'id' => 1,
                    'num' => '01',
                    'name' => 'Reynaldo Sirpa',
                    'email' => 'admin@metric.com',
                    'initial' => 'R',
                    'role' => 'Superadministrador',
                    'role_theme' => 'cyan',
                    'status' => 'online',
                    'status_label' => 'En línea',
                    'permissions' => ['Control Total', 'Telemetría', 'Finanzas', 'Configuración'],
                ]
            ];
        } else {
            $admins = $usersFromDb->map(function ($u, $index) {
                return [
                    'id' => $u->id,
                    'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'name' => $u->name,
                    'email' => $u->email,
                    'initial' => strtoupper(substr($u->name, 0, 1)),
                    'role' => $u->role ?? 'Superadministrador',
                    'role_theme' => $u->role_theme ?? 'cyan',
                    'status' => $u->status ?? 'online',
                    'status_label' => ($u->status === 'online') ? 'En línea' : 'Inactivo',
                    'permissions' => is_array($u->permissions) ? $u->permissions : ['Control Total', 'Telemetría'],
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
            'role' => 'required|string',
            'permissions' => 'nullable|array',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('9210292Dc#PB'),
            'role' => $validated['role'],
            'role_theme' => ($validated['role'] === 'Superadministrador') ? 'cyan' : 'lime',
            'status' => 'online',
            'permissions' => $validated['permissions'] ?? ['Telemetría'],
        ]);

        return redirect()->route('admins.index')->with('success', 'Administrador creado y credenciales enviadas correctamente.');
    }
}
