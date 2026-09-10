<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Models\Project;
use App\Models\MeasurementModule;
use App\Models\IlluminationMeasurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MobileApiController extends Controller
{
    /**
     * Información del servidor y chequeo de salud (Health Check).
     */
    public function serverInfo(Request $request)
    {
        return response()->json([
            'success' => true,
            'system' => 'Metric v2 API',
            'version' => '1.0.0',
            'status' => 'online',
            'timestamp' => Carbon::now()->toIso8601String(),
            'client_ip' => $request->ip(),
        ]);
    }

    /**
     * Autenticación de colaboradores (Personal / Staff).
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:150',
            'fcm_token' => 'nullable|string',
        ]);

        $email = trim(strtolower($validated['email']));
        $password = $validated['password'];

        // 1. Buscar en la tabla Staff
        $staff = Staff::whereRaw('LOWER(email) = ?', [$email])->first();

        // 2. Buscar en la tabla User para verificar contraseñas hasheadas
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$staff && !$user) {
            return response()->json([
                'success' => false,
                'message' => 'Credenciales inválidas. No existe un colaborador registrado con este correo.',
            ], 401);
        }

        $isValidPassword = false;

        // Verificar contra password_plain de staff
        if ($staff && !empty($staff->password_plain) && $staff->password_plain === $password) {
            $isValidPassword = true;
        }

        // Si no validó con password_plain, verificar contra User hash
        if (!$isValidPassword && $user && !empty($user->password)) {
            if (Hash::check($password, $user->password)) {
                $isValidPassword = true;
            }
        }

        if (!$isValidPassword) {
            return response()->json([
                'success' => false,
                'message' => 'Contraseña incorrecta. Verifica tus credenciales.',
            ], 401);
        }

        // Si existe User pero no Staff, crear o resolver Staff asociado
        if (!$staff && $user) {
            $parts = explode(' ', $user->name, 2);
            $staff = Staff::create([
                'first_name' => $parts[0] ?? 'Colaborador',
                'last_name' => $parts[1] ?? '',
                'name' => $user->name,
                'email' => $user->email,
                'position' => $user->role ?? 'Técnico de Campo',
                'status' => 'online',
                'status_label' => 'Activo',
            ]);
        }

        // Actualizar datos del dispositivo y estado online si se envían
        if (!empty($validated['device_name'])) {
            $staff->device_name = trim($validated['device_name']);
        }
        if (!empty($validated['fcm_token'])) {
            $staff->fcm_token = trim($validated['fcm_token']);
        }
        $staff->status = 'online';
        $staff->status_label = 'Activo';
        $staff->save();

        // Generar token de sesión para la app móvil
        $token = 'mtoken_' . base64_encode($staff->id . ':' . Str::random(40));
        $mustChangePassword = (bool) ($staff->must_change_password ?? false);

        return response()->json([
            'success' => true,
            'message' => 'Inicio de sesión exitoso.',
            'token' => $token,
            'must_change_password' => $mustChangePassword,
            'user' => [
                'id' => $staff->id,
                'uid' => (string) $staff->id,
                'name' => $staff->full_name,
                'nombre_completo' => $staff->full_name,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'email' => $staff->email,
                'position' => $staff->position ?? 'Técnico de Campo',
                'device_name' => $staff->device_name,
                'status' => $staff->status,
                'must_change_password' => $mustChangePassword,
            ],
        ]);
    }

    /**
     * Permite al colaborador cambiar su contraseña temporal por una definitiva.
     */
    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'new_password' => 'required|string|min:6|max:100',
            'staff_id' => 'nullable',
            'email' => 'nullable|email',
        ]);

        $staffId = $validated['staff_id'] ?? $request->header('X-Staff-Id');
        $email = $validated['email'] ?? null;
        $staff = null;

        if (!empty($staffId)) {
            $staff = Staff::find($staffId);
        }
        if (!$staff && !empty($email)) {
            $staff = Staff::whereRaw('LOWER(email) = ?', [strtolower(trim($email))])->first();
        }

        if (!$staff) {
            // Extraer ID desde token Bearer si viene
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer mtoken_')) {
                $raw = substr($authHeader, 14);
                $decoded = base64_decode($raw);
                if ($decoded && str_contains($decoded, ':')) {
                    $parts = explode(':', $decoded, 2);
                    $extractedId = (int) $parts[0];
                    if ($extractedId > 0) {
                        $staff = Staff::find($extractedId);
                    }
                }
            }
        }

        if (!$staff) {
            return response()->json([
                'success' => false,
                'message' => 'Colaborador no encontrado para actualizar contraseña.',
            ], 404);
        }

        $newPassword = $validated['new_password'];

        $staff->password_plain = $newPassword;
        $staff->must_change_password = false;
        $staff->save();

        if (!empty($staff->email)) {
            $user = User::where('email', $staff->email)->first();
            if ($user) {
                $user->update([
                    'password' => Hash::make($newPassword),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Contraseña actualizada exitosamente.',
            'user' => [
                'id' => $staff->id,
                'uid' => (string) $staff->id,
                'name' => $staff->full_name,
                'email' => $staff->email,
                'must_change_password' => false,
            ]
        ]);
    }

    /**
     * Lista de proyectos asignados al colaborador.
     */
    public function projects(Request $request)
    {
        $staffId = $request->query('staff_id') ?? $request->header('X-Staff-Id');

        $query = Project::with(['company', 'manager', 'modules'])->orderBy('id', 'desc');

        if (!empty($staffId) && is_numeric($staffId)) {
            $staffIdInt = (int) $staffId;
            // Filtrar proyectos donde al menos un módulo tiene este staff asignado
            $query->whereHas('modules', function ($mQuery) use ($staffIdInt) {
                $mQuery->where('field_staff_id', $staffIdInt)
                    ->orWhereJsonContains('field_staff_ids', $staffIdInt)
                    ->orWhereJsonContains('field_staff_ids', (string) $staffIdInt);
            });
        }

        $projects = $query->get()->map(function ($prj) {
            $totalMods = $prj->modules->count();
            $completedMods = $prj->modules->where('status', 'Completado')->count();
            $compliancePct = round($prj->compliance_pct ?? 0);

            return [
                'id' => (string) $prj->id,
                'code' => $prj->code,
                'name' => $prj->name,
                'nombre' => $prj->name,
                'description' => $prj->description ?? '',
                'descripcion' => $prj->description ?? '',
                'company_name' => $prj->company ? $prj->company->name : 'General',
                'cliente' => $prj->company ? $prj->company->name : 'General',
                'company_code' => $prj->company ? $prj->company->code : 'EMP',
                'compliance_pct' => $compliancePct,
                'porcentaje_avance' => $compliancePct,
                'points_total' => $prj->points_total,
                'points_completed' => $prj->points_completed,
                'status' => $prj->status ?? 'En Ejecución',
                'start_date' => $prj->start_date ? Carbon::parse($prj->start_date)->format('Y-m-d') : null,
                'updated_at' => $prj->updated_at ? $prj->updated_at->toIso8601String() : null,
                'created_at' => $prj->created_at ? $prj->created_at->toIso8601String() : null,
                'modules_count' => $totalMods,
                'modules_completed' => $completedMods,
            ];
        });

        return response()->json([
            'success' => true,
            'projects' => $projects,
        ]);
    }

    /**
     * Módulos de monitoreo de un proyecto.
     */
    public function projectModules(Request $request, $projectId)
    {
        $staffId = $request->query('staff_id') ?? $request->header('X-Staff-Id');

        $project = Project::with(['modules' => function ($q) {
            $q->orderBy('id', 'asc');
        }])->findOrFail($projectId);

        $modules = $project->modules->map(function ($mod) use ($staffId) {
            $assignedStaffIds = is_array($mod->field_staff_ids) ? $mod->field_staff_ids : [];
            if ($mod->field_staff_id) {
                $assignedStaffIds[] = $mod->field_staff_id;
            }
            $isAssigned = empty($staffId) || in_array((int) $staffId, array_map('intval', $assignedStaffIds));

            $isIlum = str_contains(strtolower($mod->name ?? ''), 'iluminaci')
                || str_contains(strtolower($mod->monitoring_type ?? ''), 'iluminaci')
                || strtolower($mod->key ?? '') === 'iluminacion';

            $key = $isIlum ? 'iluminacion' : ($mod->key ?? 'general');
            $monType = $isIlum ? 'Iluminación Ocupacional' : ($mod->monitoring_type ?? $mod->name);

            $measurementsCount = $isIlum 
                ? $mod->illuminationMeasurements()->count() 
                : $mod->points_completed;

            $staffIds = array_values(array_unique(array_filter(array_map('intval', $assignedStaffIds))));
            $assignedStaffMembers = Staff::whereIn('id', $staffIds)->get()->map(function ($s) {
                $fullName = $s->full_name ?: $s->name ?: 'Colaborador';
                return [
                    'id' => (string) $s->id,
                    'nombre' => $fullName,
                    'name' => $fullName,
                    'email' => $s->email ?? '',
                    'username' => !empty($s->email) ? explode('@', $s->email)[0] : '',
                ];
            })->values()->all();

            $pointsTotal = (int) ($mod->points_total ?? 0);
            if ($pointsTotal <= 0 && $isIlum) {
                $pointsTotal = 15;
            }

            return [
                'id' => (string) $mod->id,
                'project_id' => (string) $mod->project_id,
                'proyecto_id' => (string) $mod->project_id,
                'key' => $key,
                'name' => $isIlum ? 'Iluminación Ocupacional' : $mod->name,
                'monitoring_type' => $monType,
                'tipo_monitoreo' => $monType,
                'description' => $mod->description ?? '',
                'descripcion' => $mod->description ?? '',
                'puntos' => $pointsTotal,
                'points_total' => $pointsTotal,
                'puntos_totales' => $pointsTotal,
                'points_completed' => $measurementsCount,
                'completed' => $measurementsCount,
                'puntos_completados' => $measurementsCount,
                'porcentaje_avance' => $pointsTotal > 0 ? round(($measurementsCount / $pointsTotal) * 100) : 0,
                'status' => $mod->status ?? 'En Progreso',
                'status_theme' => $mod->status_theme ?? 'in_progress',
                'assigned_staff_ids' => $staffIds,
                'usuarios_asignados' => $assignedStaffMembers,
                'assigned_staff' => $assignedStaffMembers,
                'is_assigned_to_user' => $isAssigned,
            ];
        });

        return response()->json([
            'success' => true,
            'project' => [
                'id' => (string) $project->id,
                'name' => $project->name,
                'code' => $project->code,
            ],
            'modules' => $modules,
        ]);
    }

    /**
     * Obtener mediciones de iluminación de un módulo.
     */
    public function getIlluminationMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->illuminationMeasurements()
            ->with('staff')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item, $index) {
                $readings = is_array($item->readings) 
                    ? $item->readings 
                    : (json_decode($item->readings, true) ?: []);

                $images = is_array($item->images) 
                    ? $item->images 
                    : (json_decode($item->images, true) ?: []);
                if (empty($images) && !empty($item->image_path)) {
                    $images = [$item->image_path];
                }

                $imageUrls = array_values(array_filter(array_map(function ($p) {
                    if (!$p) return null;
                    if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
                    return asset($p);
                }, $images)));

                return [
                    'id' => $item->id,
                    'remote_id' => (string) $item->id,
                    'module_id' => (string) $item->module_id,
                    'monitoreo_id' => (string) $item->module_id,
                    'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'punto_medicion' => $item->measurement_point,
                    'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                    'measurement_time' => $item->measurement_time,
                    'measured_at' => $item->measurement_date 
                        ? ($item->measurement_date->format('Y-m-d') . ' ' . ($item->measurement_time ?: '00:00:00'))
                        : null,
                    'area' => $item->area,
                    'workstation' => $item->workstation,
                    'puesto_trabajo' => $item->workstation,
                    'activity_description' => $item->activity_description,
                    'descripcion_actividad' => $item->activity_description,
                    'lighting_type' => $item->lighting_type,
                    'tipo_iluminacion' => $item->lighting_type,
                    'required_lux' => (float) $item->required_lux,
                    'nivel_requerido' => (int) $item->required_lux,
                    'measured_lux' => (float) $item->measured_lux,
                    'readings' => $readings,
                    'mediciones_lux' => $readings,
                    'is_compliant' => (float) $item->measured_lux >= (float) $item->required_lux,
                    'image_urls' => $imageUrls,
                    'location' => $item->location,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'observations' => $item->observations,
                    'observaciones' => $item->observations,
                    'registered_by' => $item->registered_by,
                    'created_by' => $item->registered_by,
                    'staff_id' => $item->staff_id,
                ];
            });

        return response()->json([
            'success' => true,
            'module_id' => (string) $module->id,
            'module_name' => $module->name,
            'measurements' => $measurements,
        ]);
    }

    /**
     * Guardar una medición de iluminación desde la aplicación móvil.
     */
    public function storeIlluminationMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        // Procesar inputs compatibles tanto con camelCase como snake_case (app de Flutter)
        $area = $request->input('area');
        $workstation = $request->input('workstation') ?? $request->input('puesto_trabajo') ?? 'General';
        $measurementPoint = $request->input('measurement_point') ?? $request->input('punto_medicion') ?? 'P-01';
        $activityDescription = $request->input('activity_description') ?? $request->input('descripcion_actividad') ?? 'Oficinas y talleres';
        $lightingType = $request->input('lighting_type') ?? $request->input('tipo_iluminacion') ?? 'Natural y Artificial';
        $requiredLux = $request->input('required_lux') ?? $request->input('nivel_requerido') ?? 300;
        
        // Mediciones array / lecturas lux
        $rawReadings = $request->input('readings') ?? $request->input('mediciones_lux') ?? [];
        if (is_string($rawReadings)) {
            $rawReadings = json_decode($rawReadings, true) ?: [];
        }
        $readings = is_array($rawReadings) ? array_map('floatval', array_filter($rawReadings, 'is_numeric')) : [];

        // Valor medido lux promedio
        $measuredLux = $request->input('measured_lux');
        if ($measuredLux === null && !empty($readings)) {
            $measuredLux = round(array_sum($readings) / count($readings), 2);
        }
        $measuredLux = (float) ($measuredLux ?? 0);

        // Fecha y hora
        $measuredAt = $request->input('measured_at') ?? $request->input('measurement_date');
        $measurementDate = Carbon::now()->format('Y-m-d');
        $measurementTime = Carbon::now()->format('H:i');

        if (!empty($measuredAt)) {
            try {
                $dt = Carbon::parse($measuredAt);
                $measurementDate = $dt->format('Y-m-d');
                $measurementTime = $dt->format('H:i');
            } catch (\Throwable $e) {}
        }

        // Staff / Registrador
        $staffId = $request->input('staff_id');
        $registeredByName = $request->input('registered_by') ?? $request->input('created_by');

        if (!empty($staffId)) {
            $staff = Staff::find($staffId);
            if ($staff) {
                $registeredByName = $staff->name;
            }
        }
        if (empty($registeredByName)) {
            $registeredByName = 'Técnico Móvil';
        }

        // Numeración del punto
        $existingCount = $module->illuminationMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        // Procesamiento de imágenes (archivos multipart o URLs/Base64)
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        // Si se enviaron archivos multipart
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'ilum_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/measurements/' . $filename;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $filename = 'ilum_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                array_unshift($uploadedImages, 'uploads/measurements/' . $filename);
            }
        }

        // Si vienen URLs o rutas previas en JSON
        $inputUrls = $request->input('image_urls') ?? $request->input('images');
        if (is_string($inputUrls)) {
            $inputUrls = json_decode($inputUrls, true);
        }
        if (is_array($inputUrls)) {
            foreach ($inputUrls as $u) {
                if (is_string($u) && !in_array($u, $uploadedImages)) {
                    $uploadedImages[] = $u;
                }
            }
        }

        $imagePath = $uploadedImages[0] ?? null;

        // Ubicación y Coordenadas
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $locationRaw = $request->input('location');

        if (is_array($locationRaw)) {
            $latitude = $latitude ?? ($locationRaw['lat'] ?? ($locationRaw['latitude'] ?? null));
            $longitude = $longitude ?? ($locationRaw['lng'] ?? ($locationRaw['longitude'] ?? null));
            $locationRaw = json_encode($locationRaw);
        } elseif (is_string($locationRaw)) {
            $decodedLoc = json_decode($locationRaw, true);
            if (is_array($decodedLoc)) {
                $latitude = $latitude ?? ($decodedLoc['lat'] ?? ($decodedLoc['latitude'] ?? null));
                $longitude = $longitude ?? ($decodedLoc['lng'] ?? ($decodedLoc['longitude'] ?? null));
            }
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->illuminationMeasurements()->find($measurementId);
        }

        $data = [
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'area' => $area ?: 'Área Principal',
            'workstation' => $workstation,
            'measurement_point' => $measurementPoint,
            'activity_description' => $activityDescription,
            'lighting_type' => $lightingType,
            'required_lux' => $requiredLux,
            'measured_lux' => $measuredLux,
            'readings' => !empty($readings) ? $readings : null,
            'image_path' => $imagePath,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $locationRaw,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'observations' => $request->input('observations') ?? $request->input('observaciones'),
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->illuminationMeasurements()->create($data);
            $statusCode = 201;
        }

        // Actualizar puntos completados del módulo
        $module->points_completed = $module->illuminationMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Punto de medición #{$measurement->point_number} registrado exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de iluminación.
     */
    public function destroyIlluminationMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->illuminationMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->illuminationMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }
}
