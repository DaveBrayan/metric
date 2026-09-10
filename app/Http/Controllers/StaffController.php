<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StaffController extends Controller
{
    /**
     * Muestra la lista de personal técnico y colaboradores.
     */
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $staff = Staff::orderBy('id', 'asc')->get()->map(function ($s, $index) {
            $isActive = in_array(strtolower($s->status), ['online', 'activo']);
            $fullName = $s->full_name;

            // Extraer nombre y apellido limpios
            $firstName = $s->first_name;
            $lastName = $s->last_name;
            if (empty($firstName) && !empty($s->name)) {
                $parts = preg_split('/\s+/', trim($s->name), 2);
                $firstName = $parts[0] ?? '';
                $lastName = $parts[1] ?? '';
            }

            // Formatear token FCM para visualización en tabla
            $fcmToken = $s->fcm_token;
            $fcmSnippet = null;
            if (!empty($fcmToken)) {
                $trimmed = trim($fcmToken);
                $fcmSnippet = strlen($trimmed) > 16 
                    ? substr($trimmed, 0, 8) . '...' . substr($trimmed, -6)
                    : $trimmed;
            }

            return [
                'id' => $s->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'first_name' => $firstName ?: 'Colaborador',
                'last_name' => $lastName ?: '',
                'name' => $fullName,
                'email' => $s->email,
                'initial' => strtoupper(substr($firstName ?: ($fullName ?: 'C'), 0, 1)),
                'position' => $s->position ?: 'Técnico de Campo',
                'device_name' => $s->device_name ?: '—',
                'has_device' => !empty($s->device_name) && $s->device_name !== '—',
                'fcm_token' => $fcmToken,
                'fcm_snippet' => $fcmSnippet,
                'has_fcm' => !empty($fcmToken),
                'password_plain' => $s->password_plain ?: 'Metric2026*',
                'role_theme' => $s->role_theme ?: 'cyan',
                'status' => $isActive ? 'activo' : 'inactivo',
                'status_label' => $isActive ? 'Activo' : 'Inactivo',
            ];
        })->toArray();

        return view('staff.index', compact('userName', 'userRole', 'staff'));
    }

    /**
     * Genera una contraseña temporal simple en mayúsculas (ej: 321AS5SA).
     * Excluye estrictamente la letra 'I' mayúscula para evitar confusiones de lectura.
     */
    public static function generateTemporaryPassword(): string
    {
        $letters = 'ABCDEFGHJKLMNPQRSTUVWXYZ'; // Sin 'I' ni 'O'
        $digits = '23456789';

        $p1 = $digits[rand(0, strlen($digits) - 1)] . $digits[rand(0, strlen($digits) - 1)] . $digits[rand(0, strlen($digits) - 1)];
        $p2 = $letters[rand(0, strlen($letters) - 1)] . $letters[rand(0, strlen($letters) - 1)];
        $p3 = $digits[rand(0, strlen($digits) - 1)];
        $p4 = $letters[rand(0, strlen($letters) - 1)] . $letters[rand(0, strlen($letters) - 1)];

        return "{$p1}{$p2}{$p3}{$p4}";
    }

    /**
     * Registra un nuevo colaborador.
     * Genera contraseña temporal (ej: 321AS5SA) y marca must_change_password = true.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:staff,email|unique:users,email',
            'status' => 'required|in:activo,inactivo,online,offline',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un formato de correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya se encuentra registrado en el sistema.',
        ]);

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);
        $fullName = trim("{$firstName} {$lastName}");
        $email = strtolower(trim($validated['email']));

        // Generación de contraseña temporal con formato solicitado (sin letra 'I')
        $autoPassword = $this->generateTemporaryPassword();

        $isActive = in_array(strtolower($validated['status']), ['activo', 'online']);
        $currentUser = Auth::user();
        $manager = $currentUser ? \App\Models\Manager::where('email', $currentUser->email)->first() : null;

        $staff = Staff::create([
            'manager_id' => $manager ? $manager->id : null,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $fullName,
            'email' => $email,
            'position' => 'Técnico de Campo',
            'device_name' => null,
            'password_plain' => $autoPassword,
            'must_change_password' => true,
            'role_theme' => 'cyan',
            'status' => $isActive ? 'online' : 'offline',
            'status_label' => $isActive ? 'Activo' : 'Inactivo',
        ]);

        // Crear cuenta de usuario para login con la contraseña generada
        try {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $fullName,
                    'password' => Hash::make($autoPassword),
                    'role' => 'Técnico de Campo',
                    'role_theme' => 'cyan',
                    'status' => $isActive ? 'online' : 'offline',
                    'permissions' => ['Personal - Ver', 'Módulos - Ver', 'Telemetría - Ver', 'Telemetría - Cargar'],
                ]
            );
        } catch (\Throwable $e) {
            // Continuar si falla la réplica de usuario
        }

        $credentialsData = [
            'id' => $staff->id,
            'name' => $fullName,
            'email' => $email,
            'password' => $autoPassword,
            'position' => $staff->position,
            'login_url' => url('/login'),
        ];

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Colaborador {$fullName} registrado exitosamente.",
                'credentials' => $credentialsData,
            ]);
        }

        return redirect()->route('staff.index')
            ->with('created_credentials', $credentialsData)
            ->with('success', "Colaborador {$fullName} registrado exitosamente.");
    }

    /**
     * Actualiza los datos de un colaborador (Nombre, Apellido, Correo y Estado).
     */
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $user = !empty($staff->email) ? User::where('email', $staff->email)->first() : null;

        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:staff,email,' . $staff->id . ($user ? '|unique:users,email,' . $user->id : ''),
            'status' => 'required|in:activo,inactivo,online,offline',
        ], [
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingresa un formato de correo electrónico válido.',
            'email.unique' => 'Este correo electrónico ya está en uso por otro colaborador.',
        ]);

        $firstName = trim($validated['first_name']);
        $lastName = trim($validated['last_name']);
        $fullName = trim("{$firstName} {$lastName}");
        $newEmail = strtolower(trim($validated['email']));
        $oldEmail = $staff->email;
        $isActive = in_array(strtolower($validated['status']), ['activo', 'online']);

        $staff->update([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => $fullName,
            'email' => $newEmail,
            'status' => $isActive ? 'online' : 'offline',
            'status_label' => $isActive ? 'Activo' : 'Inactivo',
        ]);

        // Sincronizar cuenta de usuario si existe
        if ($user) {
            $user->update([
                'name' => $fullName,
                'email' => $newEmail,
                'status' => $isActive ? 'online' : 'offline',
            ]);
        } elseif (!empty($oldEmail)) {
            $userOld = User::where('email', $oldEmail)->first();
            if ($userOld) {
                $userOld->update([
                    'name' => $fullName,
                    'email' => $newEmail,
                    'status' => $isActive ? 'online' : 'offline',
                ]);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Colaborador {$fullName} actualizado exitosamente.",
                'staff' => [
                    'id' => $staff->id,
                    'name' => $fullName,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $staff->email,
                    'status' => $isActive ? 'activo' : 'inactivo',
                ]
            ]);
        }

        return redirect()->route('staff.index')->with('success', "Colaborador {$fullName} actualizado correctamente.");
    }

    /**
     * Restablece la contraseña de un colaborador generando una contraseña temporal en mayúsculas sin letra I.
     */
    public function resetPassword(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $newPassword = $this->generateTemporaryPassword();

        $staff->password_plain = $newPassword;
        $staff->must_change_password = true;
        $staff->save();

        if (!empty($staff->email)) {
            $user = User::where('email', $staff->email)->first();
            if ($user) {
                $user->update([
                    'password' => Hash::make($newPassword),
                ]);
            }
        }

        $credentialsData = [
            'id' => $staff->id,
            'name' => $staff->full_name,
            'email' => $staff->email,
            'password' => $newPassword,
            'position' => $staff->position,
            'login_url' => url('/login'),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Contraseña temporal regenerada exitosamente.',
            'credentials' => $credentialsData,
        ]);
    }

    /**
     * Endpoint para registrar o actualizar el token FCM desde la aplicación móvil.
     */
    public function updateFcmToken(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'fcm_token' => 'required|string',
            'device_name' => 'nullable|string|max:150',
        ]);

        $staff->fcm_token = $validated['fcm_token'];
        if (!empty($validated['device_name'])) {
            $staff->device_name = trim($validated['device_name']);
        }
        $staff->save();

        return response()->json([
            'success' => true,
            'message' => 'Token FCM vinculado exitosamente.',
            'staff_id' => $staff->id,
            'device_name' => $staff->device_name,
        ]);
    }

    /**
     * Elimina un colaborador y su cuenta asociada.
     */
    public function destroy(Request $request, $id)
    {
        $staff = Staff::find($id);

        if ($staff) {
            $name = $staff->name ?: 'Colaborador';
            if (!empty($staff->email)) {
                User::where('email', $staff->email)->delete();
            }
            $staff->delete();
            $msg = "Colaborador {$name} eliminado exitosamente.";
        } else {
            $msg = "Colaborador no encontrado.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return redirect()->route('staff.index')->with('success', $msg);
    }
}
