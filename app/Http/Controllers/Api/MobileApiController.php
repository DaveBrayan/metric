<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\User;
use App\Models\Project;
use App\Models\MeasurementModule;
use App\Models\IlluminationMeasurement;
use App\Models\VentilationMeasurement;
use App\Models\HeatStressMeasurement;
use App\Models\ColdStressMeasurement;
use App\Models\DosimetryMeasurement;
use App\Models\RebaMeasurement;
use App\Models\RosaMeasurement;
use App\Models\FireActivityMeasurement;
use App\Models\FireWeightMeasurement;
use App\Models\OpacityMeasurement;
use App\Models\GasMeasurement;
use App\Models\PhotographicInspection;
use App\Models\ParticulasMeasurement;
use App\Models\ParticulasAmbientalesMeasurement;
use App\Models\VibracionMeasurement;
use App\Models\RuidoAmbientalMeasurement;
use App\Models\ContaminantesQuimicosMeasurement;
use App\Http\Controllers\VentilationController;
use App\Http\Controllers\HeatStressController;
use App\Http\Controllers\ColdStressController;
use App\Http\Controllers\DosimetryController;
use App\Http\Controllers\RuidoAmbientalController;
use App\Http\Controllers\ErgonomiaRebaController;
use App\Http\Controllers\ErgonomiaRosaController;
use App\Http\Controllers\GasesController;
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
     * Solo retorna los proyectos donde el usuario tiene al menos un módulo asignado.
     */
    public function projects(Request $request)
    {
        $staffId = $request->query('staff_id') ?? $request->header('X-Staff-Id');

        if (empty($staffId)) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer mtoken_')) {
                $raw = substr($authHeader, 14);
                $decoded = base64_decode($raw);
                if ($decoded && str_contains($decoded, ':')) {
                    $parts = explode(':', $decoded, 2);
                    $extractedId = (int) $parts[0];
                    if ($extractedId > 0) {
                        $staffId = $extractedId;
                    }
                }
            }
        }

        if (empty($staffId) || !is_numeric($staffId)) {
            return response()->json([
                'success' => true,
                'projects' => [],
            ]);
        }

        $staffIdInt = (int) $staffId;
        $query = Project::with(['company', 'manager', 'modules'])->orderBy('id', 'desc');

        // Filtrar estrictamente proyectos donde al menos un módulo tiene este staff asignado
        $query->whereHas('modules', function ($mQuery) use ($staffIdInt) {
            $mQuery->where('field_staff_id', $staffIdInt)
                ->orWhereJsonContains('field_staff_ids', $staffIdInt)
                ->orWhereJsonContains('field_staff_ids', (string) $staffIdInt);
        });

        $projects = $query->get()->map(function ($prj) use ($staffIdInt) {
            // Módulos asignados a este usuario específico
            $userMods = $prj->modules->filter(function ($mod) use ($staffIdInt) {
                $assignedStaffIds = is_array($mod->field_staff_ids) ? $mod->field_staff_ids : [];
                if ($mod->field_staff_id) {
                    $assignedStaffIds[] = $mod->field_staff_id;
                }
                $assignedStaffIds = array_values(array_unique(array_filter(array_map('intval', $assignedStaffIds))));
                return in_array($staffIdInt, $assignedStaffIds);
            });

            $totalMods = $userMods->count();
            $completedMods = $userMods->where('status', 'Completado')->count();
            $compliancePct = round($prj->compliance_pct ?? 0);

            $projectTotalMods = $prj->modules->count();

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
                'modules_assigned' => $totalMods,
                'assigned_modules_count' => $totalMods,
                'modules_total' => $projectTotalMods,
                'total_modules_count' => $projectTotalMods,
                'project_modules_count' => $projectTotalMods,
                'modules_completed' => $completedMods,
            ];
        });

        return response()->json([
            'success' => true,
            'projects' => $projects,
        ]);
    }

    /**
     * Módulos de monitoreo asignados al colaborador en un proyecto.
     * Solo retorna los módulos donde el usuario está explícitamente asignado.
     */
    public function projectModules(Request $request, $projectId)
    {
        $staffId = $request->query('staff_id') ?? $request->header('X-Staff-Id');

        if (empty($staffId)) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && str_starts_with($authHeader, 'Bearer mtoken_')) {
                $raw = substr($authHeader, 14);
                $decoded = base64_decode($raw);
                if ($decoded && str_contains($decoded, ':')) {
                    $parts = explode(':', $decoded, 2);
                    $extractedId = (int) $parts[0];
                    if ($extractedId > 0) {
                        $staffId = $extractedId;
                    }
                }
            }
        }

        $project = Project::with(['modules' => function ($q) {
            $q->orderBy('id', 'asc');
        }])->findOrFail($projectId);

        $keyNames = [
            'iluminacion' => 'Iluminación',
            'ruido' => 'Ruido Ocupacional',
            'ruido_ambiental' => 'Ruido Ambiental',
            'dosimetria' => 'Dosimetría',
            'estres_calor' => 'Estrés Térmico (Calor)',
            'estres_frio' => 'Estrés Térmico (Frío)',
            'ventilacion' => 'Ventilación',
            'particulas' => 'Partículas',
            'gases' => 'Gases',
            'vibracion' => 'Vibración',
            'contaminantes_quimicos' => 'Contaminantes Químicos',
            'quimicos' => 'Contaminantes Químicos',
            'ergonomia' => 'Ergonomía REBA',
            'ergonomia_reba' => 'Ergonomía REBA',
            'ergonomia_rosa' => 'Ergonomía ROSA',
            'rosa' => 'Ergonomía ROSA',
            'opacidad' => 'Opacidad',
            'fuego_actividad' => 'Carga de Fuego por Actividad',
            'carga_fuego_actividad' => 'Carga de Fuego por Actividad',
            'fuego_peso' => 'Carga de Fuego por Peso',
            'carga_fuego_peso' => 'Carga de Fuego por Peso',
            'inspeccion_fotografica' => 'Inspección Fotográfica',
            'fotografica' => 'Inspección Fotográfica',
        ];

        $modules = $project->modules
            ->filter(function ($mod) use ($staffId) {
                if (empty($staffId) || !is_numeric($staffId)) {
                    return false; // Sin usuario autenticado/asignado no se muestran módulos
                }
                $assignedStaffIds = is_array($mod->field_staff_ids) ? $mod->field_staff_ids : [];
                if ($mod->field_staff_id) {
                    $assignedStaffIds[] = $mod->field_staff_id;
                }
                $assignedStaffIds = array_values(array_unique(array_filter(array_map('intval', $assignedStaffIds))));
                return in_array((int) $staffId, $assignedStaffIds);
            })
            ->values()
            ->map(function ($mod) use ($staffId, $keyNames) {
                $assignedStaffIds = is_array($mod->field_staff_ids) ? $mod->field_staff_ids : [];
                if ($mod->field_staff_id) {
                    $assignedStaffIds[] = $mod->field_staff_id;
                }
                $staffIds = array_values(array_unique(array_filter(array_map('intval', $assignedStaffIds))));
                $isAssigned = in_array((int) $staffId, $staffIds);

                $key = strtolower($mod->key ?? 'iluminacion');
                $isIlum = ($key === 'iluminacion')
                    || str_contains(strtolower($mod->name ?? ''), 'iluminaci')
                    || str_contains(strtolower($mod->monitoring_type ?? ''), 'iluminaci');
                $isDosi = ($key === 'dosimetria' || $key === 'dosimetry')
                    || str_contains(strtolower($mod->name ?? ''), 'dosimetr')
                    || str_contains(strtolower($mod->monitoring_type ?? ''), 'dosimetr');

                $moduleKey = $isIlum ? 'iluminacion' : ($isDosi ? 'dosimetria' : $key);
                $moduleName = $mod->name ?? ($keyNames[$moduleKey] ?? ucfirst(str_replace('_', ' ', $moduleKey)));
                if ($isIlum && ($moduleName === 'Iluminación Ocupacional' || empty($moduleName))) {
                    $moduleName = 'Iluminación';
                }
                if ($isDosi && ($moduleName === 'Dosimetría de Ruido' || empty($moduleName))) {
                    $moduleName = 'Dosimetría';
                }
                $monType = $moduleName;

                if ($isIlum) {
                    $measurementsCount = $mod->illuminationMeasurements()->count();
                } elseif ($isDosi || $moduleKey === 'dosimetria' || $moduleKey === 'dosimetry') {
                    $measurementsCount = $mod->dosimetryMeasurements()->count();
                } elseif ($moduleKey === 'inspeccion_fotografica' || $moduleKey === 'fotografica') {
                    $measurementsCount = $mod->photographicInspections()->count();
                } elseif ($moduleKey === 'ergonomia' || $moduleKey === 'ergonomia_reba') {
                    $measurementsCount = $mod->rebaMeasurements()->count();
                } elseif ($moduleKey === 'ergonomia_rosa' || $moduleKey === 'rosa') {
                    $measurementsCount = method_exists($mod, 'rebaMeasurements') ? $mod->rebaMeasurements()->count() : (int) ($mod->points_completed ?? 0);
                } elseif ($moduleKey === 'fuego_actividad' || $moduleKey === 'carga_fuego_actividad') {
                    $measurementsCount = $mod->fireActivityMeasurements()->count();
                } elseif ($moduleKey === 'fuego_peso' || $moduleKey === 'carga_fuego_peso') {
                    $measurementsCount = $mod->fireWeightMeasurements()->count();
                } elseif ($moduleKey === 'ventilacion') {
                    $measurementsCount = $mod->ventilationMeasurements()->count();
                } elseif ($moduleKey === 'estres_calor') {
                    $measurementsCount = $mod->heatStressMeasurements()->count();
                } elseif ($moduleKey === 'estres_frio') {
                    $measurementsCount = $mod->coldStressMeasurements()->count();
                } elseif ($moduleKey === 'gases' || $moduleKey === 'gas') {
                    $measurementsCount = $mod->gasMeasurements()->count();
                } else {
                    $measurementsCount = (int) ($mod->points_completed ?? 0);
                }

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
                    'key' => $moduleKey,
                    'name' => $moduleName,
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
                'total_modules' => $project->modules->count(),
                'assigned_modules' => $modules->count(),
            ],
            'modules' => $modules,
        ]);
    }

    /**
     * Obtener puntos de medición en tiempo real de todos los módulos de un proyecto.
     */
    public function projectLiveMeasurements(Request $request, $projectId)
    {
        return app(\App\Http\Controllers\ProjectController::class)->liveMonitoringData($request, $projectId);
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
        $requiredLux = $request->input('required_lux') ?? $request->input('nivel_requerido') ?? 300;
        $activityDescription = $request->input('activity_description') ?? $request->input('descripcion_actividad');
        if (empty($activityDescription)) {
            $luxNormas = [
                25 => 'Paso en construcción',
                50 => 'Pasillos, almacenes y baños',
                75 => 'Trabajos en construcción',
                100 => 'Supervisión intermitente / Montaje mediano',
                300 => 'Trabajos de oficina, lectura y escritura',
                750 => 'Trabajos de pintura e inspección de detalle',
                1500 => 'Alta precisión y ensamble fino',
                3000 => 'Casos especiales (Joyería / Cirugías)',
            ];
            $activityDescription = $luxNormas[(int)$requiredLux] ?? 'Trabajos de oficina, lectura y escritura';
        }
        $lightingType = $request->input('lighting_type') ?? $request->input('tipo_iluminacion') ?? 'Natural y Artificial';
        
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
        $rawCreatedBy = $request->input('created_by');
        $rawRegisteredBy = $request->input('registered_by');
        $rawUserId = $request->input('user_id');

        // Si staff_id no vino explícito, pero alguno de los campos de usuario es numérico:
        if (empty($staffId)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int) $rawCreatedBy;
            } elseif (is_numeric($rawRegisteredBy)) {
                $staffId = (int) $rawRegisteredBy;
            } elseif (is_numeric($rawUserId)) {
                $staffId = (int) $rawUserId;
            }
        }

        // Si aún no se tiene staff_id, intentar resolverlo desde el header Bearer token
        if (empty($staffId)) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+mtoken_([a-zA-Z0-9+\/=]+)/i', $authHeader, $matches)) {
                $decodedToken = base64_decode($matches[1]);
                $tokenParts = explode(':', $decodedToken);
                if (!empty($tokenParts[0]) && is_numeric($tokenParts[0])) {
                    $staffId = (int) $tokenParts[0];
                }
            }
        }

        $registeredByName = null;

        // Si tenemos staffId numérico, obtener el nombre del colaborador o usuario
        if (!empty($staffId)) {
            $staff = Staff::find($staffId);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            } else {
                $user = User::find($staffId);
                if ($user) {
                    $registeredByName = $user->name;
                }
            }
        }

        // Si no se resolvió por staffId, verificar si viene un nombre en texto
        if (empty($registeredByName)) {
            $candidateName = $rawRegisteredBy ?? $rawCreatedBy;
            if (!empty($candidateName) && !is_numeric($candidateName)) {
                $registeredByName = trim($candidateName);
                // Intentar buscar staff por nombre para asociar el staff_id
                $foundStaff = Staff::where('name', $registeredByName)
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [$registeredByName])
                    ->first();
                if ($foundStaff) {
                    $staffId = $foundStaff->id;
                }
            } elseif (!empty($candidateName) && is_numeric($candidateName)) {
                $staff = Staff::find((int) $candidateName);
                if ($staff) {
                    $staffId = $staff->id;
                    $registeredByName = $staff->full_name ?: $staff->name;
                }
            }
        }

        // Fallbacks si aún no se tiene nombre
        if (empty($registeredByName) || is_numeric($registeredByName)) {
            if ($module->fieldStaff) {
                $registeredByName = $module->fieldStaff->full_name ?: $module->fieldStaff->name;
                $staffId = $module->field_staff_id;
            } else {
                $registeredByName = 'Técnico de Campo';
            }
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
        $utmZone = $request->input('utm_zone') ?? $request->input('zone');
        $utmEasting = $request->input('easting') ?? $request->input('utm_easting');
        $utmNorthing = $request->input('northing') ?? $request->input('utm_northing');

        // Parsear si location vino en formato array, objeto o JSON
        if (is_array($locationRaw)) {
            $latitude = $latitude ?? ($locationRaw['lat'] ?? ($locationRaw['latitude'] ?? null));
            $longitude = $longitude ?? ($locationRaw['lng'] ?? ($locationRaw['longitude'] ?? null));
            $utmZone = $utmZone ?? ($locationRaw['utm_zone'] ?? ($locationRaw['zone'] ?? null));
            $utmEasting = $utmEasting ?? ($locationRaw['easting'] ?? ($locationRaw['utm_easting'] ?? null));
            $utmNorthing = $utmNorthing ?? ($locationRaw['northing'] ?? ($locationRaw['utm_northing'] ?? null));
            if (!empty($locationRaw['formatted'])) {
                $locationRaw = $locationRaw['formatted'];
            }
        } elseif (is_string($locationRaw) && (str_starts_with(trim($locationRaw), '{') || str_starts_with(trim($locationRaw), '['))) {
            $decodedLoc = json_decode($locationRaw, true);
            if (is_array($decodedLoc)) {
                $latitude = $latitude ?? ($decodedLoc['lat'] ?? ($decodedLoc['latitude'] ?? null));
                $longitude = $longitude ?? ($decodedLoc['lng'] ?? ($decodedLoc['longitude'] ?? null));
                $utmZone = $utmZone ?? ($decodedLoc['utm_zone'] ?? ($decodedLoc['zone'] ?? null));
                $utmEasting = $utmEasting ?? ($decodedLoc['easting'] ?? ($decodedLoc['utm_easting'] ?? null));
                $utmNorthing = $utmNorthing ?? ($decodedLoc['northing'] ?? ($decodedLoc['utm_northing'] ?? null));
                if (!empty($decodedLoc['formatted'])) {
                    $locationRaw = $decodedLoc['formatted'];
                }
            }
        }

        // Si tenemos UTM pero falta latitude/longitude, convertirlos exactamente
        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng($utmEasting, $utmNorthing, $utmZone ?: '20K');
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        // Si location no es un string formateado "E: ..., N: ..., Z: ...", armar el formato legible
        if (!empty($utmEasting) && !empty($utmNorthing) && !empty($utmZone)) {
            $eFormatted = is_numeric($utmEasting) ? number_format((float)$utmEasting, 3, '.', '') : $utmEasting;
            $nFormatted = is_numeric($utmNorthing) ? number_format((float)$utmNorthing, 3, '.', '') : $utmNorthing;
            $locationRaw = "E: {$eFormatted}, N: {$nFormatted}, Z: {$utmZone}";
        } elseif (!is_string($locationRaw) || empty($locationRaw)) {
            if ($latitude !== null && $longitude !== null) {
                $locationRaw = "{$latitude}, {$longitude}";
            } else {
                $locationRaw = null;
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
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
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

    /**
     * Obtener lista de mediciones de ventilación de un módulo.
     */
    public function getVentilationMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->ventilationMeasurements()->with('staff')->get()->map(function ($item, $index) {
            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? asset($p) : null;
            }, $rawImages)));

            return [
                'id' => $item->id,
                'point_number' => $item->point_number,
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'local_trabajo' => $item->local_trabajo,
                'tipo_local' => $item->tipo_local,
                'tipo_ventilacion' => $item->tipo_ventilacion,
                'elemento_ventilacion' => $item->elemento_ventilacion,
                'temperatura_seca_c' => (float)$item->temperatura_seca_c,
                'vel_aire_ms' => (float)$item->vel_aire_ms,
                'vel_aire_mh' => (float)$item->vel_aire_mh,
                'area_largo_m' => (float)$item->area_largo_m,
                'area_ancho_m' => (float)$item->area_ancho_m,
                'area_diametro_m' => (float)$item->area_diametro_m,
                'area_ventilacion_m2' => (float)$item->area_ventilacion_m2,
                'caudal_m3h' => (float)$item->caudal_m3h,
                'vol_largo_m' => (float)$item->vol_largo_m,
                'vol_ancho_m' => (float)$item->vol_ancho_m,
                'vol_alto_m' => (float)$item->vol_alto_m,
                'volumen_m3' => (float)$item->volumen_m3,
                'renovaciones_h' => (float)$item->renovaciones_h,
                'renovaciones_min' => (float)$item->renovaciones_min,
                'renovaciones_max' => (float)$item->renovaciones_max,
                'renovaciones_intervalo' => $item->renovaciones_intervalo,
                'cumple' => $item->cumple,
                'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                'images' => $imagesUrls,
                'location' => $item->location,
                'latitude' => $item->latitude !== null ? (float)$item->latitude : null,
                'longitude' => $item->longitude !== null ? (float)$item->longitude : null,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => (float)$item->utm_easting,
                'utm_northing' => (float)$item->utm_northing,
                'observations' => $item->observations,
                'registered_by' => $item->registered_by,
                'staff_id' => $item->staff_id,
            ];
        });

        return response()->json([
            'success' => true,
            'module_id' => $module->id,
            'total' => $measurements->count(),
            'measurements' => $measurements,
        ]);
    }

    /**
     * Registrar / sincronizar una medición de ventilación desde la app móvil.
     */
    public function storeVentilationMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurementDate = $request->input('measurement_date') ?? ($request->input('fecha') ?? Carbon::now()->format('Y-m-d'));
        $measurementTime = $request->input('measurement_time') ?? ($request->input('hora') ?? Carbon::now()->format('H:i'));
        $localTrabajo = $request->input('local_trabajo') ?? 'Área Principal';
        $tipoLocal = $request->input('tipo_local') ?? 'Locales de trabajo en general';
        $tipoVentilacion = $request->input('tipo_ventilacion') ?? 'Natural';
        $elementoVentilacion = $request->input('elemento_ventilacion') ?? 'Ventana';

        $velMs = (float) ($request->input('vel_aire_ms') ?? ($request->input('velocidad_ms') ?? 0.0));
        $velMh = (float) ($request->input('vel_aire_mh') ?? ($velMs * 3600.0));

        $largo = (float) ($request->input('area_largo_m') ?? ($request->input('largo') ?? 0.0));
        $ancho = (float) ($request->input('area_ancho_m') ?? ($request->input('ancho') ?? 0.0));
        $diametro = (float) ($request->input('area_diametro_m') ?? ($request->input('diametro') ?? 0.0));

        $areaM2 = 0.0;
        if ($diametro > 0) {
            $radio = $diametro / 2.0;
            $areaM2 = M_PI * ($radio * $radio);
        } elseif ($largo > 0 && $ancho > 0) {
            $areaM2 = $largo * $ancho;
        }

        $caudalM3h = $areaM2 * $velMh;

        $volLargo = (float) ($request->input('vol_largo_m') ?? ($request->input('vol_largo') ?? 0.0));
        $volAncho = (float) ($request->input('vol_ancho_m') ?? ($request->input('vol_ancho') ?? 0.0));
        $volAlto = (float) ($request->input('vol_alto_m') ?? ($request->input('vol_alto') ?? 0.0));
        $volumenM3 = ($volLargo > 0 && $volAncho > 0 && $volAlto > 0) ? ($volLargo * $volAncho * $volAlto) : 0.0;

        $renovH = $volumenM3 > 0 ? ($caudalM3h / $volumenM3) : 0.0;

        $norma = VentilationController::$tiposLocalNorma[$tipoLocal] ?? null;
        $renovMin = $request->input('renovaciones_min') ?? ($norma ? $norma['min'] : null);
        $renovMax = $request->input('renovaciones_max') ?? ($norma ? $norma['max'] : null);
        $intervalo = $request->input('renovaciones_intervalo') ?? ($norma ? $norma['intervalo'] : '');

        $cumple = $request->input('cumple');
        if (empty($cumple)) {
            if ($renovMin !== null && $renovMin > 0) {
                $cumple = $renovH >= $renovMin ? 'CUMPLE' : 'NO CUMPLE';
            } else {
                $cumple = 'CUMPLE';
            }
        }

        // Determinar staff_id y nombre
        $staffId = $request->input('staff_id');
        $registeredByName = $request->input('registered_by') ?? $request->input('registrado_por');
        if (!empty($staffId) && empty($registeredByName)) {
            $st = Staff::find($staffId);
            if ($st) $registeredByName = $st->full_name ?: $st->name;
        }
        if (empty($registeredByName) || is_numeric($registeredByName)) {
            if ($module->fieldStaff) {
                $registeredByName = $module->fieldStaff->full_name ?: $module->fieldStaff->name;
                $staffId = $module->field_staff_id;
            } else {
                $registeredByName = 'Técnico de Campo';
            }
        }

        $existingCount = $module->ventilationMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        // Procesamiento de fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'vent_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/measurements/' . $filename;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $filename = 'vent_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                array_unshift($uploadedImages, 'uploads/measurements/' . $filename);
            }
        }

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

        // Ubicación
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $locationRaw = $request->input('location');
        $utmZone = $request->input('utm_zone') ?? '20K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('easting');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('northing');

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng($utmEasting, $utmNorthing, $utmZone);
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        if (!empty($utmEasting) && !empty($utmNorthing)) {
            $eFormatted = is_numeric($utmEasting) ? number_format((float)$utmEasting, 3, '.', '') : $utmEasting;
            $nFormatted = is_numeric($utmNorthing) ? number_format((float)$utmNorthing, 3, '.', '') : $utmNorthing;
            $locationRaw = "E: {$eFormatted}, N: {$nFormatted}, Z: {$utmZone}";
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->ventilationMeasurements()->find($measurementId);
        }

        $data = [
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'local_trabajo' => $localTrabajo,
            'tipo_local' => $tipoLocal,
            'tipo_ventilacion' => $tipoVentilacion,
            'elemento_ventilacion' => $elementoVentilacion,
            'temperatura_seca_c' => $request->input('temperatura_seca_c'),
            'vel_aire_ms' => $velMs,
            'vel_aire_mh' => $velMh,
            'area_largo_m' => $largo,
            'area_ancho_m' => $ancho,
            'area_diametro_m' => $diametro,
            'area_ventilacion_m2' => $areaM2,
            'caudal_m3h' => $caudalM3h,
            'vol_largo_m' => $volLargo,
            'vol_ancho_m' => $volAncho,
            'vol_alto_m' => $volAlto,
            'volumen_m3' => $volumenM3,
            'renovaciones_h' => $renovH,
            'renovaciones_min' => $renovMin,
            'renovaciones_max' => $renovMax,
            'renovaciones_intervalo' => $intervalo,
            'cumple' => $cumple,
            'image_path' => $imagePath,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $locationRaw,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'observations' => $request->input('observations') ?? $request->input('observaciones'),
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->ventilationMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->ventilationMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Punto de medición de ventilación #{$measurement->point_number} registrado exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de ventilación.
     */
    public function destroyVentilationMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->ventilationMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->ventilationMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de ventilación eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Convierte coordenadas UTM a Latitud/Longitud WGS84.
     */
    private function utmToLatLng($utmEasting, $utmNorthing, $utmZoneStr = '20K')
    {
        $utmZoneNum = 20;
        $utmZoneLetter = 'K';
        if (is_string($utmZoneStr) && preg_match('/(\d+)\s*([A-Za-z]?)/', $utmZoneStr, $utmM)) {
            $utmZoneNum = (int) $utmM[1] ?: 20;
            $utmZoneLetter = strtoupper($utmM[2] ?? 'K');
        } elseif (is_numeric($utmZoneStr)) {
            $utmZoneNum = (int) $utmZoneStr;
        }

        $utmA = 6378137.0;
        $utmF = 1 / 298.257223563;
        $utmB = $utmA * (1 - $utmF);
        $utmE = sqrt(($utmA * $utmA - $utmB * $utmB) / ($utmA * $utmA));
        $utmEPrime = sqrt(($utmA * $utmA - $utmB * $utmB) / ($utmB * $utmB));
        $utmK0 = 0.9996;

        $utmIsSouth = $utmZoneLetter !== '' ? ($utmZoneLetter < 'N') : true;
        $utmX = (float)$utmEasting - 500000.0;
        $utmY = $utmIsSouth ? (float)$utmNorthing - 10000000.0 : (float)$utmNorthing;

        $utmMarc = $utmY / $utmK0;
        $utmE2 = $utmE * $utmE;
        $utmE4 = $utmE2 * $utmE2;
        $utmE6 = $utmE4 * $utmE2;
        $utmE1 = (1 - sqrt(1 - $utmE2)) / (1 + sqrt(1 - $utmE2));

        $utmMu = $utmMarc / ($utmA * (1 - $utmE2 / 4 - 3 * $utmE4 / 64 - 5 * $utmE6 / 256));

        $utmPhi1 = $utmMu +
            (3 * $utmE1 / 2 - 27 * pow($utmE1, 3) / 32) * sin(2 * $utmMu) +
            (21 * $utmE1 * $utmE1 / 16 - 55 * pow($utmE1, 4) / 32) * sin(4 * $utmMu) +
            (151 * pow($utmE1, 3) / 96) * sin(6 * $utmMu) +
            (1097 * pow($utmE1, 4) / 512) * sin(8 * $utmMu);

        $utmSinPhi1 = sin($utmPhi1);
        $utmCosPhi1 = cos($utmPhi1);
        $utmTanPhi1 = tan($utmPhi1);

        $utmN1 = $utmA / sqrt(1 - $utmE2 * $utmSinPhi1 * $utmSinPhi1);
        $utmT1 = $utmTanPhi1 * $utmTanPhi1;
        $utmC1 = $utmEPrime * $utmEPrime * $utmCosPhi1 * $utmCosPhi1;
        $utmR1 = $utmA * (1 - $utmE2) / pow(1 - $utmE2 * $utmSinPhi1 * $utmSinPhi1, 1.5);
        $utmD = $utmX / ($utmN1 * $utmK0);

        $utmD2 = $utmD * $utmD;
        $utmD3 = $utmD2 * $utmD;
        $utmD4 = $utmD2 * $utmD2;
        $utmD5 = $utmD4 * $utmD;
        $utmD6 = $utmD3 * $utmD3;

        $utmLat = $utmPhi1 - ($utmN1 * $utmTanPhi1 / $utmR1) * (
            $utmD2 / 2 -
            (5 + 3 * $utmT1 + 10 * $utmC1 - 4 * $utmC1 * $utmC1 - 9 * $utmEPrime * $utmEPrime) * $utmD4 / 24 +
            (61 + 90 * $utmT1 + 298 * $utmC1 + 45 * $utmT1 * $utmT1 - 252 * $utmEPrime * $utmEPrime - 3 * $utmC1 * $utmC1) * $utmD6 / 720
        );

        $utmLon0 = ($utmZoneNum - 1) * 6 - 180 + 3;
        $utmLon = ($utmLon0 * M_PI / 180.0) + (
            $utmD -
            (1 + 2 * $utmT1 + $utmC1) * $utmD3 / 6 +
            (5 - 2 * $utmC1 + 28 * $utmT1 - 3 * $utmC1 * $utmC1 + 8 * $utmEPrime * $utmEPrime + 24 * $utmT1 * $utmT1) * $utmD5 / 120
        ) / $utmCosPhi1;

        return [
            'lat' => $utmLat * 180.0 / M_PI,
            'lng' => $utmLon * 180.0 / M_PI,
        ];
    }

    /**
     * =========================================================================
     * ESTRÉS TÉRMICO POR CALOR (HEAT STRESS)
     * =========================================================================
     */

    /**
     * Obtener mediciones de estrés térmico por calor de un módulo.
     */
    public function getHeatStressMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->heatStressMeasurements()
            ->with('staff')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item, $index) {
                $photos = is_array($item->photo_paths)
                    ? $item->photo_paths
                    : (json_decode($item->photo_paths, true) ?: []);

                $imageUrls = array_values(array_filter(array_map(function ($p) {
                    if (!$p) return null;
                    if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
                    return asset($p);
                }, $photos)));

                return [
                    'id' => $item->id,
                    'remote_id' => (string) $item->id,
                    'module_id' => (string) $item->module_id,
                    'monitoreo_id' => (string) $item->module_id,
                    'point_number' => $item->point_number ?: ('C-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT)),
                    'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                    'measurement_time' => $item->measurement_time,
                    'measured_at' => $item->measurement_date
                        ? ($item->measurement_date->format('Y-m-d') . ' ' . ($item->measurement_time ?: '00:00:00'))
                        : null,
                    'area' => $item->area,
                    'puesto_trabajo' => $item->puesto_trabajo,
                    'desc_actividades' => $item->desc_actividades,
                    'interior_exterior' => $item->interior_exterior,
                    'aclimatado' => $item->aclimatado,
                    'tipo_ropa_cav' => $item->tipo_ropa_cav,
                    'cav_ajuste_db' => $item->cav_ajuste_db,
                    'capucha' => $item->capucha,
                    'tasa_metabolica' => $item->tasa_metabolica,
                    'temp_c' => $item->temp_c,
                    'hr_percent' => $item->hr_percent,
                    'vel_viento_ms' => $item->vel_viento_ms,
                    'presion_mmhg' => $item->presion_mmhg,
                    'wb_c' => $item->wb_c,
                    'gt_c' => $item->gt_c,
                    'wbgt_c' => $item->wbgt_c,
                    'wbgt_efectivo_c' => $item->wbgt_efectivo_c,
                    'limite_wbgt_lmp' => $item->limite_wbgt_lmp,
                    'regimen_trabajo_descanso' => $item->regimen_trabajo_descanso,
                    'is_compliant' => (bool) $item->is_compliant,
                    'image_urls' => $imageUrls,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'utm_zone' => $item->utm_zone,
                    'utm_easting' => $item->utm_easting,
                    'utm_northing' => $item->utm_northing,
                    'location_description' => $item->location_description,
                    'observations' => $item->observations,
                    'staff_id' => $item->staff_id,
                    'registered_by' => $item->staff ? ($item->staff->full_name ?: $item->staff->name) : 'Técnico de Campo',
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
     * Guardar una medición de estrés térmico por calor desde la app móvil.
     */
    public function storeHeatStressMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $area = $request->input('area');
        $puestoTrabajo = $request->input('puesto_trabajo') ?? $request->input('workstation') ?? 'General';
        $descActividades = $request->input('desc_actividades') ?? $request->input('descripcion_actividad');
        $interiorExterior = $request->input('interior_exterior') ?? 'Interior';
        
        $aclimatadoRaw = $request->input('aclimatado');
        if (is_bool($aclimatadoRaw)) {
            $aclimatadoStr = $aclimatadoRaw ? 'Sí' : 'No';
        } elseif (is_numeric($aclimatadoRaw)) {
            $aclimatadoStr = ((int)$aclimatadoRaw === 1) ? 'Sí' : 'No';
        } else {
            $aclimatadoStr = $aclimatadoRaw ? trim((string)$aclimatadoRaw) : 'Sí';
        }

        $tipoRopa = $request->input('tipo_ropa_cav') ?? 'Ropa de Trabajo';
        $capuchaRaw = $request->input('capucha');
        if (is_bool($capuchaRaw)) {
            $capuchaStr = $capuchaRaw ? 'Sí' : 'No';
        } elseif (is_numeric($capuchaRaw)) {
            $capuchaStr = ((int)$capuchaRaw === 1) ? 'Sí' : 'No';
        } else {
            $capuchaStr = $capuchaRaw ? trim((string)$capuchaRaw) : 'No';
        }

        $tasaMetabolica = $request->input('tasa_metabolica') ?? 'Clase 2 - Índice metabólico medio';

        $tempC = $request->filled('temp_c') ? (float)$request->input('temp_c') : null;
        $hrPercent = $request->filled('hr_percent') ? (float)$request->input('hr_percent') : null;
        $velViento = $request->filled('vel_viento_ms') ? (float)$request->input('vel_viento_ms') : null;
        $presion = $request->filled('presion_mmhg') ? (float)$request->input('presion_mmhg') : null;
        $wbC = $request->filled('wb_c') ? (float)$request->input('wb_c') : null;
        $gtC = $request->filled('gt_c') ? (float)$request->input('gt_c') : null;
        $wbgtRaw = $request->filled('wbgt_c') ? (float)$request->input('wbgt_c') : null;

        // Evaluación técnica automática con ACGIH TLVs
        $eval = HeatStressController::evaluateHeatStress(
            $wbgtRaw, $wbC, $gtC, $tempC, $interiorExterior, $aclimatadoStr, $tipoRopa, $tasaMetabolica
        );

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

        // Staff / Colaborador
        $staffId = $request->input('staff_id');
        $rawCreatedBy = $request->input('created_by') ?? $request->input('registered_by');
        if (empty($staffId) && !empty($rawCreatedBy)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int)$rawCreatedBy;
            } else {
                $foundStaff = Staff::where('name', trim($rawCreatedBy))
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [trim($rawCreatedBy)])
                    ->first();
                if ($foundStaff) $staffId = $foundStaff->id;
            }
        }
        if (empty($staffId)) {
            $staffId = $module->field_staff_id;
        }

        // Fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/heat_stress');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $fn = 'heat_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/heat_stress/' . $fn;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fn = 'heat_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/heat_stress/' . $fn;
                }
            }
        }

        $inputUrls = $request->input('image_urls') ?? $request->input('photo_paths');
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

        // Coordenadas
        $latitude = $request->input('latitude') ?? $request->input('lat');
        $longitude = $request->input('longitude') ?? $request->input('lng');
        $utmZone = $request->input('utm_zone') ?? '19K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('easting');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('northing');

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng($utmEasting, $utmNorthing, $utmZone);
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        $locationRaw = $request->input('location') ?? $request->input('location_description');
        if (empty($locationRaw) && !empty($utmEasting) && !empty($utmNorthing)) {
            $eFormatted = is_numeric($utmEasting) ? number_format((float)$utmEasting, 3, '.', '') : $utmEasting;
            $nFormatted = is_numeric($utmNorthing) ? number_format((float)$utmNorthing, 3, '.', '') : $utmNorthing;
            $locationRaw = "E: {$eFormatted}, N: {$nFormatted}, Z: {$utmZone}";
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->heatStressMeasurements()->find($measurementId);
        }

        $existingCount = $module->heatStressMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? ('C-' . str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT));

        $data = [
            'project_id' => $module->project_id,
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'area' => $area,
            'puesto_trabajo' => $puestoTrabajo,
            'desc_actividades' => $descActividades,
            'interior_exterior' => $interiorExterior,
            'aclimatado' => $aclimatadoStr,
            'tipo_ropa_cav' => $tipoRopa,
            'cav_ajuste_db' => $eval['cav'],
            'capucha' => $capuchaStr,
            'tasa_metabolica' => $tasaMetabolica,
            'temp_c' => $tempC,
            'hr_percent' => $hrPercent,
            'vel_viento_ms' => $velViento,
            'presion_mmhg' => $presion,
            'wb_c' => $wbC,
            'gt_c' => $gtC,
            'wbgt_c' => $eval['wbgt'],
            'wbgt_efectivo_c' => $eval['wbgt_efectivo'],
            'limite_wbgt_lmp' => $eval['lmp'],
            'regimen_trabajo_descanso' => $eval['regimen'],
            'is_compliant' => $eval['compliant'],
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'location_description' => $locationRaw,
            'observations' => $request->input('observations') ?? $request->input('observaciones'),
            'photo_paths' => !empty($uploadedImages) ? $uploadedImages : null,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->heatStressMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->heatStressMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Punto de medición de estrés por calor #{$measurement->point_number} registrado exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'points_completed' => $module->points_completed,
            'wbgt_efectivo_c' => $measurement->wbgt_efectivo_c,
            'limite_wbgt_lmp' => $measurement->limite_wbgt_lmp,
            'is_compliant' => $measurement->is_compliant,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de estrés térmico por calor.
     */
    public function destroyHeatStressMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->heatStressMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->heatStressMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de estrés por calor eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * =========================================================================
     * ESTRÉS TÉRMICO POR FRÍO (COLD STRESS)
     * =========================================================================
     */

    /**
     * Obtener mediciones de estrés térmico por frío de un módulo.
     */
    public function getColdStressMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->coldStressMeasurements()
            ->with('staff')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item, $index) {
                $photos = is_array($item->photo_paths)
                    ? $item->photo_paths
                    : (json_decode($item->photo_paths, true) ?: []);

                $imageUrls = array_values(array_filter(array_map(function ($p) {
                    if (!$p) return null;
                    if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
                    return asset($p);
                }, $photos)));

                return [
                    'id' => $item->id,
                    'remote_id' => (string) $item->id,
                    'module_id' => (string) $item->module_id,
                    'monitoreo_id' => (string) $item->module_id,
                    'point_number' => $item->point_number ?: ('F-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT)),
                    'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                    'measurement_time' => $item->measurement_time,
                    'measured_at' => $item->measurement_date
                        ? ($item->measurement_date->format('Y-m-d') . ' ' . ($item->measurement_time ?: '00:00:00'))
                        : null,
                    'area' => $item->area,
                    'puesto_trabajo' => $item->puesto_trabajo,
                    'desc_actividades' => $item->desc_actividades,
                    'temp_c' => $item->temp_c,
                    'hr_percent' => $item->hr_percent,
                    'vel_viento_ms' => $item->vel_viento_ms,
                    'presion_mmhg' => $item->presion_mmhg,
                    'metabolismo' => $item->metabolismo,
                    'aislamiento' => $item->aislamiento,
                    'indice_viento_wci' => $item->indice_viento_wci,
                    'sensacion_termica_c' => $item->sensacion_termica_c,
                    'nivel_riesgo' => $item->nivel_riesgo,
                    'tiempo_limite_exposicion' => $item->tiempo_limite_exposicion,
                    'is_compliant' => (bool) $item->is_compliant,
                    'image_urls' => $imageUrls,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'utm_zone' => $item->utm_zone,
                    'utm_easting' => $item->utm_easting,
                    'utm_northing' => $item->utm_northing,
                    'location_description' => $item->location_description,
                    'observations' => $item->observations,
                    'staff_id' => $item->staff_id,
                    'registered_by' => $item->staff ? ($item->staff->full_name ?: $item->staff->name) : 'Técnico de Campo',
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
     * Guardar una medición de estrés térmico por frío desde la app móvil.
     */
    public function storeColdStressMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $area = $request->input('area');
        $puestoTrabajo = $request->input('puesto_trabajo') ?? $request->input('workstation') ?? 'General';
        $descActividades = $request->input('desc_actividades') ?? $request->input('descripcion_actividad');

        $tempC = $request->filled('temp_c') ? (float)$request->input('temp_c') : null;
        $hrPercent = $request->filled('hr_percent') ? (float)$request->input('hr_percent') : null;
        $velViento = $request->filled('vel_viento_ms') ? (float)$request->input('vel_viento_ms') : null;
        $presion = $request->filled('presion_mmhg') ? (float)$request->input('presion_mmhg') : null;

        $metabolismo = $request->input('metabolismo') ?? 'Metabolismo moderado';
        $aislamiento = $request->input('aislamiento') ?? 'Vestimenta térmica estándar';

        // Cálculo de WCI, sensación térmica y riesgo
        $eval = ColdStressController::calculateColdStress($tempC, $velViento);

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

        // Staff / Colaborador
        $staffId = $request->input('staff_id');
        $rawCreatedBy = $request->input('created_by') ?? $request->input('registered_by');
        if (empty($staffId) && !empty($rawCreatedBy)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int)$rawCreatedBy;
            } else {
                $foundStaff = Staff::where('name', trim($rawCreatedBy))
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [trim($rawCreatedBy)])
                    ->first();
                if ($foundStaff) $staffId = $foundStaff->id;
            }
        }
        if (empty($staffId)) {
            $staffId = $module->field_staff_id;
        }

        // Fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/cold_stress');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $fn = 'cold_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/cold_stress/' . $fn;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fn = 'cold_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/cold_stress/' . $fn;
                }
            }
        }

        $inputUrls = $request->input('image_urls') ?? $request->input('photo_paths');
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

        // Coordenadas
        $latitude = $request->input('latitude') ?? $request->input('lat');
        $longitude = $request->input('longitude') ?? $request->input('lng');
        $utmZone = $request->input('utm_zone') ?? '19K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('easting');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('northing');

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng($utmEasting, $utmNorthing, $utmZone);
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        $locationRaw = $request->input('location') ?? $request->input('location_description');
        if (empty($locationRaw) && !empty($utmEasting) && !empty($utmNorthing)) {
            $eFormatted = is_numeric($utmEasting) ? number_format((float)$utmEasting, 3, '.', '') : $utmEasting;
            $nFormatted = is_numeric($utmNorthing) ? number_format((float)$utmNorthing, 3, '.', '') : $utmNorthing;
            $locationRaw = "E: {$eFormatted}, N: {$nFormatted}, Z: {$utmZone}";
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->coldStressMeasurements()->find($measurementId);
        }

        $existingCount = $module->coldStressMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? ('F-' . str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT));

        $data = [
            'project_id' => $module->project_id,
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'area' => $area,
            'puesto_trabajo' => $puestoTrabajo,
            'desc_actividades' => $descActividades,
            'temp_c' => $tempC,
            'hr_percent' => $hrPercent,
            'vel_viento_ms' => $velViento,
            'presion_mmhg' => $presion,
            'metabolismo' => $metabolismo,
            'aislamiento' => $aislamiento,
            'indice_viento_wci' => $eval['wci'],
            'sensacion_termica_c' => $eval['sensacion'],
            'nivel_riesgo' => $eval['riesgo'],
            'tiempo_limite_exposicion' => $eval['tle'],
            'is_compliant' => $eval['compliant'],
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'location_description' => $locationRaw,
            'observations' => $request->input('observations') ?? $request->input('observaciones'),
            'photo_paths' => !empty($uploadedImages) ? $uploadedImages : null,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->coldStressMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->coldStressMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Punto de medición de estrés por frío #{$measurement->point_number} registrado exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'points_completed' => $module->points_completed,
            'indice_viento_wci' => $measurement->indice_viento_wci,
            'sensacion_termica_c' => $measurement->sensacion_termica_c,
            'is_compliant' => $measurement->is_compliant,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de estrés térmico por frío.
     */
    public function destroyColdStressMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->coldStressMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->coldStressMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de estrés por frío eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    // =========================================================================
    // ERGONOMÍA REBA (NTP 601 / ISO 11226)
    // =========================================================================

    /**
     * Obtener lista de evaluaciones ergonómicas REBA de un módulo.
     */
    public function getErgonomiaRebaMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->rebaMeasurements()->with('staff')->get()->map(function ($item, $index) {
            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
            }, $rawImages)));

            return [
                'id' => $item->id,
                'remote_id' => (string) $item->id,
                'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'area_sector' => $item->area_sector,
                'area' => $item->area_sector,
                'puesto_trabajo' => $item->puesto_trabajo,
                'factor_riesgo' => $item->factor_riesgo,
                'num_trabajadores' => (int) $item->num_trabajadores,
                'nombres_trabajadores' => is_array($item->nombres_trabajadores) ? $item->nombres_trabajadores : (json_decode($item->nombres_trabajadores, true) ?: []),
                'edad' => $item->edad,
                'tiempo_exposicion_horas' => $item->tiempo_exposicion_horas !== null ? (float) $item->tiempo_exposicion_horas : null,
                'procedimiento_escrito' => $item->procedimiento_escrito,
                'capacitacion' => $item->capacitacion,
                'fuerza_agarre' => $item->fuerza_agarre,
                'carga_peso_kg' => $item->carga_peso_kg !== null ? (float) $item->carga_peso_kg : null,
                'distancia_m' => $item->distancia_m !== null ? (float) $item->distancia_m : null,
                'ayuda_mecanica' => $item->ayuda_mecanica,
                'descripcion_carga' => $item->descripcion_carga,
                'manifestacion_temprana' => $item->manifestacion_temprana,
                'ubicacion_sintoma' => $item->ubicacion_sintoma,
                'tareas' => is_array($item->tareas) ? $item->tareas : (json_decode($item->tareas, true) ?: []),
                'observaciones' => $item->observaciones,
                'image_path' => $item->image_path ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset($item->image_path)) : ($imagesUrls[0] ?? null),
                'images' => $imagesUrls,
                'location' => $item->location,
                'latitude' => $item->latitude !== null ? (float) $item->latitude : null,
                'longitude' => $item->longitude !== null ? (float) $item->longitude : null,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting !== null ? (float) $item->utm_easting : null,
                'utm_northing' => $item->utm_northing !== null ? (float) $item->utm_northing : null,
                'tronco_base' => (int) $item->tronco_base,
                'tronco_mod' => (int) $item->tronco_mod,
                'cuello_base' => (int) $item->cuello_base,
                'cuello_mod' => (int) $item->cuello_mod,
                'piernas_base' => (int) $item->piernas_base,
                'piernas_mod' => (int) $item->piernas_mod,
                'carga_fuerza' => (int) $item->carga_fuerza,
                'carga_brusca' => (int) $item->carga_brusca,
                'brazo_base' => (int) $item->brazo_base,
                'brazo_abduccion' => (int) $item->brazo_abduccion,
                'brazo_hombro_elevado' => (int) $item->brazo_hombro_elevado,
                'brazo_apoyo_gravedad' => (int) $item->brazo_apoyo_gravedad,
                'antebrazo_base' => (int) $item->antebrazo_base,
                'muneca_base' => (int) $item->muneca_base,
                'muneca_mod' => (int) $item->muneca_mod,
                'agarre' => (int) $item->agarre,
                'actividad_estatica' => (int) $item->actividad_estatica,
                'actividad_repetitiva' => (int) $item->actividad_repetitiva,
                'actividad_inestable' => (int) $item->actividad_inestable,
                'score_tabla_a' => (int) $item->score_tabla_a,
                'score_a' => (int) $item->score_a,
                'score_tabla_b' => (int) $item->score_tabla_b,
                'score_b' => (int) $item->score_b,
                'score_c' => (int) $item->score_c,
                'score_actividad' => (int) $item->score_actividad,
                'score_final' => (int) $item->score_final,
                'risk_level' => $item->risk_level,
                'action_level' => $item->action_level,
                'risk_theme' => $item->risk_theme,
                'registered_by' => $item->registered_by,
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
     * Guardar una evaluación ergonómica REBA desde la aplicación móvil.
     */
    public function storeErgonomiaRebaMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $areaSector = $request->input('area_sector') ?? $request->input('area') ?? 'Área Principal';
        $puestoTrabajo = $request->input('puesto_trabajo') ?? 'Puesto Evaluado';
        $factorRiesgo = $request->input('factor_riesgo') ?? 'Posturas forzadas';
        $numTrabajadores = (int) ($request->input('num_trabajadores') ?? 1);
        
        $nombres = $request->input('nombres_trabajadores');
        if (is_string($nombres)) {
            $nombres = json_decode($nombres, true) ?: [$nombres];
        }
        $tareas = $request->input('tareas');
        if (is_string($tareas)) {
            $tareas = json_decode($tareas, true) ?: [$tareas];
        }

        // Calcular puntajes REBA con la matriz normativa
        $calc = ErgonomiaRebaController::calculateRebaScore($request->all());

        // Fecha y hora
        $measuredAt = $request->input('measured_at') ?? $request->input('measurement_date');
        $measurementDate = Carbon::now()->format('Y-m-d');
        $measurementTime = Carbon::now()->format('H:i:s');
        if (!empty($measuredAt)) {
            try {
                $dt = Carbon::parse($measuredAt);
                $measurementDate = $dt->format('Y-m-d');
                $measurementTime = $dt->format('H:i:s');
            } catch (\Throwable $e) {}
        }

        // Staff / Evaluador
        $staffId = $request->input('staff_id');
        $rawCreatedBy = $request->input('created_by') ?? $request->input('registered_by');
        if (empty($staffId) && !empty($rawCreatedBy)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int)$rawCreatedBy;
            } else {
                $foundStaff = Staff::where('name', trim($rawCreatedBy))
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [trim($rawCreatedBy)])
                    ->first();
                if ($foundStaff) $staffId = $foundStaff->id;
            }
        }
        $registeredByName = $rawCreatedBy;
        if (!empty($staffId)) {
            $st = Staff::find($staffId);
            if ($st) $registeredByName = $st->full_name ?: $st->name;
        }

        // Fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/reba');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $fn = 'reba_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/reba/' . $fn;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fn = 'reba_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/reba/' . $fn;
                }
            }
        }

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

        // Coordenadas
        $latitude = $request->input('latitude') ?? $request->input('lat');
        $longitude = $request->input('longitude') ?? $request->input('lng');
        $utmZone = $request->input('utm_zone') ?? $request->input('zone') ?? '20K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('easting');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('northing');
        $locationRaw = $request->input('location');

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng((float)$utmEasting, (float)$utmNorthing, $utmZone);
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        if (!empty($utmEasting) && !empty($utmNorthing) && !empty($utmZone)) {
            $eF = number_format((float)$utmEasting, 2, '.', '');
            $nF = number_format((float)$utmNorthing, 2, '.', '');
            $locationRaw = "Zona {$utmZone} E: {$eF} N: {$nF}";
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->rebaMeasurements()->find($measurementId);
        }

        $existingCount = $module->rebaMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        $pModApi = (int)($request->input('piernas_mod') ?? 0);
        if ($request->has('piernas_flexion_30_60') || $request->has('piernas_flexion_mas_60')) {
            $pMod30 = $request->input('piernas_flexion_30_60') ? 1 : 0;
            $pMod60 = $request->input('piernas_flexion_mas_60') ? 2 : 0;
            $pModApi = $pMod30 + $pMod60;
        }

        $data = [
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'area_sector' => $areaSector,
            'puesto_trabajo' => $puestoTrabajo,
            'factor_riesgo' => $factorRiesgo,
            'num_trabajadores' => $numTrabajadores,
            'nombres_trabajadores' => $nombres,
            'edad' => $request->input('edad'),
            'tiempo_exposicion_horas' => $request->filled('tiempo_exposicion_horas') ? (float)$request->input('tiempo_exposicion_horas') : null,
            'procedimiento_escrito' => $request->input('procedimiento_escrito'),
            'capacitacion' => $request->input('capacitacion'),
            'fuerza_agarre' => $request->input('fuerza_agarre'),
            'carga_peso_kg' => $request->filled('carga_peso_kg') ? (float)$request->input('carga_peso_kg') : null,
            'distancia_m' => $request->filled('distancia_m') ? (float)$request->input('distancia_m') : null,
            'ayuda_mecanica' => $request->input('ayuda_mecanica'),
            'descripcion_carga' => $request->input('descripcion_carga'),
            'manifestacion_temprana' => $request->input('manifestacion_temprana'),
            'ubicacion_sintoma' => $request->input('ubicacion_sintoma'),
            'tareas' => $tareas,
            'observaciones' => $request->input('observaciones'),
            'image_path' => $uploadedImages[0] ?? null,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $locationRaw,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'tronco_base' => (int)($request->input('tronco_base') ?? 1),
            'tronco_mod' => (int)($request->input('tronco_mod') ?? 0),
            'cuello_base' => (int)($request->input('cuello_base') ?? 1),
            'cuello_mod' => (int)($request->input('cuello_mod') ?? 0),
            'piernas_base' => (int)($request->input('piernas_base') ?? 1),
            'piernas_mod' => $pModApi,
            'carga_fuerza' => (int)($request->input('carga_fuerza') ?? 0),
            'carga_brusca' => (int)($request->input('carga_brusca') ?? 0),
            'brazo_base' => (int)($request->input('brazo_base') ?? 1),
            'brazo_abduccion' => (int)($request->input('brazo_abduccion') ?? 0),
            'brazo_hombro_elevado' => (int)($request->input('brazo_hombro_elevado') ?? 0),
            'brazo_apoyo_gravedad' => (int)($request->input('brazo_apoyo_gravedad') ?? 0),
            'antebrazo_base' => (int)($request->input('antebrazo_base') ?? 1),
            'muneca_base' => (int)($request->input('muneca_base') ?? 1),
            'muneca_mod' => (int)($request->input('muneca_mod') ?? 0),
            'agarre' => (int)($request->input('agarre') ?? 0),
            'actividad_estatica' => (int)($request->input('actividad_estatica') ?? 0),
            'actividad_repetitiva' => (int)($request->input('actividad_repetitiva') ?? 0),
            'actividad_inestable' => (int)($request->input('actividad_inestable') ?? 0),
            'score_tabla_a' => $calc['score_tabla_a'],
            'score_a' => $calc['score_a'],
            'score_tabla_b' => $calc['score_tabla_b'],
            'score_b' => $calc['score_b'],
            'score_c' => $calc['score_c'],
            'score_actividad' => $calc['score_actividad'],
            'score_final' => $calc['score_final'],
            'risk_level' => $calc['risk_level'],
            'action_level' => $calc['action_level'],
            'risk_theme' => $calc['risk_theme'],
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->rebaMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->rebaMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Evaluación REBA #{$measurement->point_number} guardada exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'score_final' => $measurement->score_final,
            'risk_level' => $measurement->risk_level,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una evaluación REBA.
     */
    public function destroyErgonomiaRebaMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->rebaMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->rebaMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Evaluación REBA eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    // =========================================================================
    // ERGONOMÍA ROSA (RAPID OFFICE STRAIN ASSESSMENT)
    // =========================================================================

    /**
     * Obtener lista de evaluaciones ergonómicas ROSA de un módulo.
     */
    public function getErgonomiaRosaMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->rosaMeasurements()->with('staff')->get()->map(function ($item, $index) {
            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
            }, $rawImages)));

            return [
                'id' => $item->id,
                'remote_id' => (string) $item->id,
                'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'area_sector' => $item->area_sector,
                'area' => $item->area_sector,
                'puesto_trabajo' => $item->puesto_trabajo,
                'factor_riesgo' => $item->factor_riesgo,
                'num_trabajadores' => (int) $item->num_trabajadores,
                'nombres_trabajadores' => is_array($item->nombres_trabajadores) ? $item->nombres_trabajadores : (json_decode($item->nombres_trabajadores, true) ?: []),
                'edad' => $item->edad,
                'tiempo_exposicion_horas' => $item->tiempo_exposicion_horas !== null ? (float) $item->tiempo_exposicion_horas : null,
                'procedimiento_escrito' => $item->procedimiento_escrito,
                'capacitacion' => $item->capacitacion,
                'fuerza_agarre' => $item->fuerza_agarre,
                'carga_peso_kg' => $item->carga_peso_kg !== null ? (float) $item->carga_peso_kg : null,
                'distancia_m' => $item->distancia_m !== null ? (float) $item->distancia_m : null,
                'ayuda_mecanica' => $item->ayuda_mecanica,
                'descripcion_carga' => $item->descripcion_carga,
                'manifestacion_temprana' => $item->manifestacion_temprana,
                'ubicacion_sintoma' => $item->ubicacion_sintoma,
                'tareas' => is_array($item->tareas) ? $item->tareas : (json_decode($item->tareas, true) ?: []),
                'observaciones' => $item->observaciones,
                'image_path' => $item->image_path ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset($item->image_path)) : ($imagesUrls[0] ?? null),
                'images' => $imagesUrls,
                'location' => $item->location,
                'latitude' => $item->latitude !== null ? (float) $item->latitude : null,
                'longitude' => $item->longitude !== null ? (float) $item->longitude : null,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting !== null ? (float) $item->utm_easting : null,
                'utm_northing' => $item->utm_northing !== null ? (float) $item->utm_northing : null,
                'altura_asiento_base' => (int) $item->altura_asiento_base,
                'altura_asiento_mod' => (int) $item->altura_asiento_mod,
                'profundidad_base' => (int) $item->profundidad_base,
                'profundidad_mod' => (int) $item->profundidad_mod,
                'reposabrazos_base' => (int) $item->reposabrazos_base,
                'reposabrazos_mod' => (int) $item->reposabrazos_mod,
                'respaldo_base' => (int) $item->respaldo_base,
                'respaldo_mod' => (int) $item->respaldo_mod,
                'silla_tiempo_uso' => (int) $item->silla_tiempo_uso,
                'pantalla_base' => (int) $item->pantalla_base,
                'pantalla_mod' => (int) $item->pantalla_mod,
                'pantalla_tiempo' => (int) $item->pantalla_tiempo,
                'telefono_base' => (int) $item->telefono_base,
                'telefono_mod' => (int) $item->telefono_mod,
                'telefono_tiempo' => (int) $item->telefono_tiempo,
                'raton_base' => (int) $item->raton_base,
                'raton_mod' => (int) $item->raton_mod,
                'raton_tiempo' => (int) $item->raton_tiempo,
                'teclado_base' => (int) $item->teclado_base,
                'teclado_mod' => (int) $item->teclado_mod,
                'teclado_tiempo' => (int) $item->teclado_tiempo,
                'actividad_estatica' => (int) $item->actividad_estatica,
                'actividad_repetitiva' => (int) $item->actividad_repetitiva,
                'actividad_inestable' => (int) $item->actividad_inestable,
                'score_a' => (int) $item->score_a,
                'score_b' => (int) $item->score_b,
                'score_c' => (int) $item->score_c,
                'score_d' => (int) $item->score_d,
                'score_e' => (int) $item->score_e,
                'score_actividad' => (int) $item->score_actividad,
                'score_final' => (int) $item->score_final,
                'risk_level' => $item->risk_level,
                'action_level' => $item->action_level,
                'risk_theme' => $item->risk_theme,
                'registered_by' => $item->registered_by,
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
     * Guardar una evaluación ergonómica ROSA desde la aplicación móvil.
     */
    public function storeErgonomiaRosaMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $areaSector = $request->input('area_sector') ?? $request->input('area') ?? 'Oficina Principal';
        $puestoTrabajo = $request->input('puesto_trabajo') ?? 'Puesto Administrativo';
        $factorRiesgo = $request->input('factor_riesgo') ?? 'Trabajo prolongado frente a PVD / Pantallas';
        $numTrabajadores = (int) ($request->input('num_trabajadores') ?? 1);
        
        $nombres = $request->input('nombres_trabajadores');
        if (is_string($nombres)) {
            $nombres = json_decode($nombres, true) ?: [$nombres];
        }
        $tareas = $request->input('tareas');
        if (is_string($tareas)) {
            $tareas = json_decode($tareas, true) ?: [$tareas];
        }

        // Calcular puntajes ROSA con la matriz normativa oficial
        $calc = ErgonomiaRosaController::calculateRosaScore($request->all());

        // Fecha y hora
        $measuredAt = $request->input('measured_at') ?? $request->input('measurement_date');
        $measurementDate = Carbon::now()->format('Y-m-d');
        $measurementTime = Carbon::now()->format('H:i:s');
        if (!empty($measuredAt)) {
            try {
                $dt = Carbon::parse($measuredAt);
                $measurementDate = $dt->format('Y-m-d');
                $measurementTime = $dt->format('H:i:s');
            } catch (\Throwable $e) {}
        }

        // Staff / Evaluador
        $staffId = $request->input('staff_id');
        $rawCreatedBy = $request->input('created_by') ?? $request->input('registered_by');
        if (empty($staffId) && !empty($rawCreatedBy)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int)$rawCreatedBy;
            } else {
                $foundStaff = Staff::where('name', trim($rawCreatedBy))
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [trim($rawCreatedBy)])
                    ->first();
                if ($foundStaff) $staffId = $foundStaff->id;
            }
        }
        $registeredByName = $rawCreatedBy;
        if (!empty($staffId)) {
            $st = Staff::find($staffId);
            if ($st) $registeredByName = $st->full_name ?: $st->name;
        }

        // Fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/rosa');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $fn = 'rosa_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/rosa/' . $fn;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fn = 'rosa_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/rosa/' . $fn;
                }
            }
        }

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

        // Coordenadas
        $latitude = $request->input('latitude') ?? $request->input('lat');
        $longitude = $request->input('longitude') ?? $request->input('lng');
        $utmZone = $request->input('utm_zone') ?? $request->input('zone') ?? '20K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('easting');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('northing');
        $locationRaw = $request->input('location');

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng((float)$utmEasting, (float)$utmNorthing, $utmZone);
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        if (!empty($utmEasting) && !empty($utmNorthing) && !empty($utmZone)) {
            $eF = number_format((float)$utmEasting, 2, '.', '');
            $nF = number_format((float)$utmNorthing, 2, '.', '');
            $locationRaw = "Zona {$utmZone} E: {$eF} N: {$nF}";
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->rosaMeasurements()->find($measurementId);
        }

        $existingCount = $module->rosaMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        $data = [
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'area_sector' => $areaSector,
            'puesto_trabajo' => $puestoTrabajo,
            'factor_riesgo' => $factorRiesgo,
            'num_trabajadores' => $numTrabajadores,
            'nombres_trabajadores' => $nombres,
            'edad' => $request->input('edad'),
            'tiempo_exposicion_horas' => $request->filled('tiempo_exposicion_horas') ? (float)$request->input('tiempo_exposicion_horas') : null,
            'procedimiento_escrito' => $request->input('procedimiento_escrito'),
            'capacitacion' => $request->input('capacitacion'),
            'fuerza_agarre' => $request->input('fuerza_agarre'),
            'carga_peso_kg' => $request->filled('carga_peso_kg') ? (float)$request->input('carga_peso_kg') : null,
            'distancia_m' => $request->filled('distancia_m') ? (float)$request->input('distancia_m') : null,
            'ayuda_mecanica' => $request->input('ayuda_mecanica'),
            'descripcion_carga' => $request->input('descripcion_carga'),
            'manifestacion_temprana' => $request->input('manifestacion_temprana'),
            'ubicacion_sintoma' => $request->input('ubicacion_sintoma'),
            'tareas' => $tareas,
            'observaciones' => $request->input('observaciones'),
            'image_path' => $uploadedImages[0] ?? null,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $locationRaw,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'altura_asiento_base' => (int)($request->input('altura_asiento_base') ?? 1),
            'altura_asiento_mod' => (int)($request->input('altura_asiento_mod') ?? 0),
            'profundidad_base' => (int)($request->input('profundidad_base') ?? 1),
            'profundidad_mod' => (int)($request->input('profundidad_mod') ?? 0),
            'reposabrazos_base' => (int)($request->input('reposabrazos_base') ?? 1),
            'reposabrazos_mod' => (int)($request->input('reposabrazos_mod') ?? 0),
            'respaldo_base' => (int)($request->input('respaldo_base') ?? 1),
            'respaldo_mod' => (int)($request->input('respaldo_mod') ?? 0),
            'silla_tiempo_uso' => (int)($request->input('silla_tiempo_uso') ?? 0),
            'pantalla_base' => (int)($request->input('pantalla_base') ?? 1),
            'pantalla_mod' => (int)($request->input('pantalla_mod') ?? 0),
            'pantalla_tiempo' => (int)($request->input('pantalla_tiempo') ?? 0),
            'telefono_base' => (int)($request->input('telefono_base') ?? 1),
            'telefono_mod' => (int)($request->input('telefono_mod') ?? 0),
            'telefono_tiempo' => (int)($request->input('telefono_tiempo') ?? 0),
            'raton_base' => (int)($request->input('raton_base') ?? 1),
            'raton_mod' => (int)($request->input('raton_mod') ?? 0),
            'raton_tiempo' => (int)($request->input('raton_tiempo') ?? 0),
            'teclado_base' => (int)($request->input('teclado_base') ?? 1),
            'teclado_mod' => (int)($request->input('teclado_mod') ?? 0),
            'teclado_tiempo' => (int)($request->input('teclado_tiempo') ?? 0),
            'actividad_estatica' => (int)($request->input('actividad_estatica') ?? 0),
            'actividad_repetitiva' => (int)($request->input('actividad_repetitiva') ?? 0),
            'actividad_inestable' => (int)($request->input('actividad_inestable') ?? 0),
            'score_a' => $calc['score_a'],
            'score_b' => $calc['score_b'],
            'score_c' => $calc['score_c'],
            'score_d' => $calc['score_d'],
            'score_e' => $calc['score_e'],
            'score_actividad' => $calc['score_actividad'],
            'score_final' => $calc['score_final'],
            'risk_level' => $calc['risk_level'],
            'action_level' => $calc['action_level'],
            'risk_theme' => $calc['risk_theme'],
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->rosaMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->rosaMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Evaluación ROSA #{$measurement->point_number} guardada exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'score_final' => $measurement->score_final,
            'risk_level' => $measurement->risk_level,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una evaluación ROSA.
     */
    public function destroyErgonomiaRosaMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->rosaMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->rosaMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Evaluación ROSA eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    // =========================================================================
    // CARGA DE FUEGO POR ACTIVIDAD (NB 58005 / NTP 453)
    // =========================================================================

    /**
     * Obtener lista de mediciones de Carga de Fuego por Actividad.
     */
    public function getFireActivityMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->fireActivityMeasurements()->with('staff')->get()->map(function ($item, $index) {
            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
            }, $rawImages)));

            $dimensions = is_array($item->dimensions) ? $item->dimensions : (json_decode($item->dimensions, true) ?: [
                'yi_largo' => (float)$item->yi_largo,
                'xi_ancho' => (float)$item->xi_ancho,
                'area_m2' => (float)$item->area_m2,
            ]);

            $activities = is_array($item->activities) ? $item->activities : (json_decode($item->activities, true) ?: []);
            $fireEquipments = is_array($item->fire_equipments) ? $item->fire_equipments : (json_decode($item->fire_equipments, true) ?: []);

            return [
                'id' => $item->id,
                'remote_id' => (string) $item->id,
                'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'date' => $item->measurement_date ? $item->measurement_date->format('d/m/Y') : null,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'time' => $item->measurement_time,
                'macroarea' => $item->macroarea,
                'sector_name' => $item->sector_name,
                'dimensions' => $dimensions,
                'activities' => $activities,
                'fire_equipments' => $fireEquipments,
                'qs_mj_m2' => (float) $item->qs_mj_m2,
                'qs_mcal_m2' => (float) $item->qs_mcal_m2,
                'risk_level' => $item->risk_level,
                'risk_color' => $item->risk_color,
                'risk_theme' => $item->risk_color,
                'ra_value' => $item->ra_value !== null ? (float)$item->ra_value : 1.0,
                'image_path' => $item->image_path ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset($item->image_path)) : ($imagesUrls[0] ?? null),
                'images' => $imagesUrls,
                'images_count' => count($imagesUrls),
                'location' => $item->location,
                'latitude' => $item->latitude !== null ? (float) $item->latitude : null,
                'longitude' => $item->longitude !== null ? (float) $item->longitude : null,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting !== null ? (float) $item->utm_easting : null,
                'utm_northing' => $item->utm_northing !== null ? (float) $item->utm_northing : null,
                'registered_by' => $item->registered_by,
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
     * Guardar una medición de Carga de Fuego por Actividad desde la aplicación móvil.
     */
    public function storeFireActivityMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $macroarea = $request->input('macroarea') ?? 'Macroárea Principal';
        $sectorName = $request->input('sector_name') ?? $request->input('sector') ?? 'Sector 01';

        // Parsear dimensiones
        $dimensions = $request->input('dimensions');
        if (is_string($dimensions)) {
            $dimensions = json_decode($dimensions, true) ?: [];
        }
        $yiLargo = (float)($dimensions['yi_largo'] ?? $request->input('yi_largo') ?? $request->input('largo') ?? 10.0);
        $xiAncho = (float)($dimensions['xi_ancho'] ?? $request->input('xi_ancho') ?? $request->input('ancho') ?? 10.0);
        $areaM2 = (float)($dimensions['area_m2'] ?? $request->input('area_m2') ?? ($yiLargo * $xiAncho));
        if ($areaM2 <= 0) $areaM2 = 1.0;

        $dimensions = [
            'yi_largo' => $yiLargo,
            'xi_ancho' => $xiAncho,
            'area_m2' => $areaM2,
        ];

        // Parsear actividades y calcular Carga de Fuego
        $activities = $request->input('activities');
        if (is_string($activities)) {
            $activities = json_decode($activities, true) ?: [];
        }
        if (!is_array($activities)) $activities = [];

        $fireEquipments = $request->input('fire_equipments') ?? $request->input('equipos');
        if (is_string($fireEquipments)) {
            $fireEquipments = json_decode($fireEquipments, true) ?: [];
        }
        if (!is_array($fireEquipments)) $fireEquipments = [];

        // Cálculo de Qs por actividad: sum(Si * qsi * Ci) / S * Ra
        $raValue = (float)($request->input('ra_value') ?? 1.0);
        if ($raValue <= 0) $raValue = 1.0;

        $sumWeighted = 0.0;
        foreach ($activities as $act) {
            $actLargo = (float)($act['largo'] ?? 0);
            $actAncho = (float)($act['ancho'] ?? 0);
            $actArea = $actLargo > 0 && $actAncho > 0 ? ($actLargo * $actAncho) : (float)($act['area'] ?? $areaM2);
            $qsi = (float)($act['qsi'] ?? 800);
            $ci = (float)($act['ci'] ?? 1.0);
            $sumWeighted += ($actArea * $qsi * $ci);
        }

        $qsMjM2 = $areaM2 > 0 ? round(($sumWeighted / $areaM2) * $raValue, 2) : 0;
        $qsMcalM2 = round($qsMjM2 / 4.1868, 2);

        // Nivel de riesgo normativo
        if ($qsMjM2 <= 800) {
            $riskLevel = 'Bajo';
            $riskColor = 'emerald';
        } elseif ($qsMjM2 <= 3400) {
            $riskLevel = 'Medio';
            $riskColor = 'amber';
        } else {
            $riskLevel = 'Alto';
            $riskColor = 'rose';
        }

        // Fecha y hora
        $measuredAt = $request->input('measured_at') ?? $request->input('measurement_date');
        $measurementDate = Carbon::now()->format('Y-m-d');
        $measurementTime = Carbon::now()->format('H:i:s');
        if (!empty($measuredAt)) {
            try {
                $dt = Carbon::parse($measuredAt);
                $measurementDate = $dt->format('Y-m-d');
                $measurementTime = $dt->format('H:i:s');
            } catch (\Throwable $e) {}
        }

        // Staff
        $staffId = $request->input('staff_id');
        $rawCreatedBy = $request->input('created_by') ?? $request->input('registered_by');
        if (empty($staffId) && !empty($rawCreatedBy)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int)$rawCreatedBy;
            } else {
                $foundStaff = Staff::where('name', trim($rawCreatedBy))
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [trim($rawCreatedBy)])
                    ->first();
                if ($foundStaff) $staffId = $foundStaff->id;
            }
        }
        $registeredByName = $rawCreatedBy;
        if (!empty($staffId)) {
            $st = Staff::find($staffId);
            if ($st) $registeredByName = $st->full_name ?: $st->name;
        }

        // Fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/fire_activity');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $fn = 'fire_act_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/fire_activity/' . $fn;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fn = 'fire_act_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/fire_activity/' . $fn;
                }
            }
        }

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

        // Coordenadas
        $latitude = $request->input('latitude') ?? $request->input('lat');
        $longitude = $request->input('longitude') ?? $request->input('lng');
        $utmZone = $request->input('utm_zone') ?? $request->input('zone') ?? '20K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('easting');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('northing');
        $locationRaw = $request->input('location');

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng((float)$utmEasting, (float)$utmNorthing, $utmZone);
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        if (!empty($utmEasting) && !empty($utmNorthing) && !empty($utmZone)) {
            $eF = number_format((float)$utmEasting, 2, '.', '');
            $nF = number_format((float)$utmNorthing, 2, '.', '');
            $locationRaw = "Zona {$utmZone} E: {$eF} N: {$nF}";
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->fireActivityMeasurements()->find($measurementId);
        }

        $existingCount = $module->fireActivityMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        $data = [
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'macroarea' => $macroarea,
            'sector_name' => $sectorName,
            'dimensions' => $dimensions,
            'yi_largo' => $yiLargo,
            'xi_ancho' => $xiAncho,
            'area_m2' => $areaM2,
            'activities' => $activities,
            'fire_equipments' => $fireEquipments,
            'qs_mj_m2' => $qsMjM2,
            'qs_mcal_m2' => $qsMcalM2,
            'risk_level' => $riskLevel,
            'risk_color' => $riskColor,
            'ra_value' => $raValue,
            'image_path' => $uploadedImages[0] ?? null,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $locationRaw,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->fireActivityMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->fireActivityMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Sector de Carga de Fuego por Actividad #{$measurement->point_number} guardado exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'qs_mj_m2' => $measurement->qs_mj_m2,
            'risk_level' => $measurement->risk_level,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de Carga de Fuego por Actividad.
     */
    public function destroyFireActivityMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->fireActivityMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->fireActivityMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de Carga de Fuego por Actividad eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    // =========================================================================
    // CARGA DE FUEGO POR PESO (NB 58005 / NTP 453)
    // =========================================================================

    /**
     * Obtener lista de mediciones de Carga de Fuego por Peso.
     */
    public function getFireWeightMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->fireWeightMeasurements()->with('staff')->get()->map(function ($item, $index) {
            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
            }, $rawImages)));

            $dimensions = is_array($item->dimensions) ? $item->dimensions : (json_decode($item->dimensions, true) ?: [
                'yi_largo' => (float)$item->yi_largo,
                'xi_ancho' => (float)$item->xi_ancho,
                'area_m2' => (float)$item->area_m2,
            ]);

            $materials = is_array($item->materials) ? $item->materials : (json_decode($item->materials, true) ?: []);
            $fireEquipments = is_array($item->fire_equipments) ? $item->fire_equipments : (json_decode($item->fire_equipments, true) ?: []);

            return [
                'id' => $item->id,
                'remote_id' => (string) $item->id,
                'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'date' => $item->measurement_date ? $item->measurement_date->format('d/m/Y') : null,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'time' => $item->measurement_time,
                'macroarea' => $item->macroarea,
                'sector_name' => $item->sector_name,
                'dimensions' => $dimensions,
                'materials' => $materials,
                'fire_equipments' => $fireEquipments,
                'qs_mj_m2' => (float) $item->qs_mj_m2,
                'qs_mcal_m2' => (float) $item->qs_mcal_m2,
                'risk_level' => $item->risk_level,
                'risk_color' => $item->risk_color,
                'risk_theme' => $item->risk_color,
                'ra_value' => $item->ra_value !== null ? (float)$item->ra_value : 1.0,
                'image_path' => $item->image_path ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset($item->image_path)) : ($imagesUrls[0] ?? null),
                'images' => $imagesUrls,
                'images_count' => count($imagesUrls),
                'location' => $item->location,
                'latitude' => $item->latitude !== null ? (float) $item->latitude : null,
                'longitude' => $item->longitude !== null ? (float) $item->longitude : null,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting !== null ? (float) $item->utm_easting : null,
                'utm_northing' => $item->utm_northing !== null ? (float) $item->utm_northing : null,
                'registered_by' => $item->registered_by,
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
     * Guardar una medición de Carga de Fuego por Peso desde la aplicación móvil.
     */
    public function storeFireWeightMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $macroarea = $request->input('macroarea') ?? 'Macroárea Principal';
        $sectorName = $request->input('sector_name') ?? $request->input('sector') ?? 'Sector 01';

        // Dimensiones
        $dimensions = $request->input('dimensions');
        if (is_string($dimensions)) {
            $dimensions = json_decode($dimensions, true) ?: [];
        }
        $yiLargo = (float)($dimensions['yi_largo'] ?? $request->input('yi_largo') ?? $request->input('largo') ?? 10.0);
        $xiAncho = (float)($dimensions['xi_ancho'] ?? $request->input('xi_ancho') ?? $request->input('ancho') ?? 10.0);
        $areaM2 = (float)($dimensions['area_m2'] ?? $request->input('area_m2') ?? ($yiLargo * $xiAncho));
        if ($areaM2 <= 0) $areaM2 = 1.0;

        $dimensions = [
            'yi_largo' => $yiLargo,
            'xi_ancho' => $xiAncho,
            'area_m2' => $areaM2,
        ];

        // Materiales combustibles y Equipos
        $materials = $request->input('materials');
        if (is_string($materials)) {
            $materials = json_decode($materials, true) ?: [];
        }
        if (!is_array($materials)) $materials = [];

        $fireEquipments = $request->input('fire_equipments') ?? $request->input('equipos');
        if (is_string($fireEquipments)) {
            $fireEquipments = json_decode($fireEquipments, true) ?: [];
        }
        if (!is_array($fireEquipments)) $fireEquipments = [];

        // Cálculo de Qs por peso: sum(Pi * qi * Ki) / S * Ra
        $raValue = (float)($request->input('ra_value') ?? 1.0);
        if ($raValue <= 0) $raValue = 1.0;

        $sumWeighted = 0.0;
        foreach ($materials as $mat) {
            $pesoKg = (float)($mat['peso_kg'] ?? $mat['peso'] ?? 0);
            $qi = (float)($mat['calor_combustion_mj'] ?? $mat['calor_combustion'] ?? $mat['qi'] ?? 16.0);
            $ki = (float)($mat['ki_peligro'] ?? $mat['ki'] ?? 1.0);
            $sumWeighted += ($pesoKg * $qi * $ki);
        }

        $qsMjM2 = $areaM2 > 0 ? round(($sumWeighted / $areaM2) * $raValue, 2) : 0;
        $qsMcalM2 = round($qsMjM2 / 4.1868, 2);

        // Nivel de riesgo normativo
        if ($qsMjM2 <= 800) {
            $riskLevel = 'Bajo';
            $riskColor = 'emerald';
        } elseif ($qsMjM2 <= 3400) {
            $riskLevel = 'Medio';
            $riskColor = 'amber';
        } else {
            $riskLevel = 'Alto';
            $riskColor = 'rose';
        }

        // Fecha y hora
        $measuredAt = $request->input('measured_at') ?? $request->input('measurement_date');
        $measurementDate = Carbon::now()->format('Y-m-d');
        $measurementTime = Carbon::now()->format('H:i:s');
        if (!empty($measuredAt)) {
            try {
                $dt = Carbon::parse($measuredAt);
                $measurementDate = $dt->format('Y-m-d');
                $measurementTime = $dt->format('H:i:s');
            } catch (\Throwable $e) {}
        }

        // Staff
        $staffId = $request->input('staff_id');
        $rawCreatedBy = $request->input('created_by') ?? $request->input('registered_by');
        if (empty($staffId) && !empty($rawCreatedBy)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int)$rawCreatedBy;
            } else {
                $foundStaff = Staff::where('name', trim($rawCreatedBy))
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [trim($rawCreatedBy)])
                    ->first();
                if ($foundStaff) $staffId = $foundStaff->id;
            }
        }
        $registeredByName = $rawCreatedBy;
        if (!empty($staffId)) {
            $st = Staff::find($staffId);
            if ($st) $registeredByName = $st->full_name ?: $st->name;
        }

        // Fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/fire_weight');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $fn = 'fire_wt_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/fire_weight/' . $fn;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fn = 'fire_wt_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fn);
                    $uploadedImages[] = 'uploads/measurements/fire_weight/' . $fn;
                }
            }
        }

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

        // Coordenadas
        $latitude = $request->input('latitude') ?? $request->input('lat');
        $longitude = $request->input('longitude') ?? $request->input('lng');
        $utmZone = $request->input('utm_zone') ?? $request->input('zone') ?? '20K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('easting');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('northing');
        $locationRaw = $request->input('location');

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng((float)$utmEasting, (float)$utmNorthing, $utmZone);
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        if (!empty($utmEasting) && !empty($utmNorthing) && !empty($utmZone)) {
            $eF = number_format((float)$utmEasting, 2, '.', '');
            $nF = number_format((float)$utmNorthing, 2, '.', '');
            $locationRaw = "Zona {$utmZone} E: {$eF} N: {$nF}";
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->fireWeightMeasurements()->find($measurementId);
        }

        $existingCount = $module->fireWeightMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        $data = [
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'macroarea' => $macroarea,
            'sector_name' => $sectorName,
            'dimensions' => $dimensions,
            'yi_largo' => $yiLargo,
            'xi_ancho' => $xiAncho,
            'area_m2' => $areaM2,
            'materials' => $materials,
            'fire_equipments' => $fireEquipments,
            'qs_mj_m2' => $qsMjM2,
            'qs_mcal_m2' => $qsMcalM2,
            'risk_level' => $riskLevel,
            'risk_color' => $riskColor,
            'ra_value' => $raValue,
            'image_path' => $uploadedImages[0] ?? null,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $locationRaw,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'registered_by' => $registeredByName,
            'staff_id' => $staffId,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->fireWeightMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->fireWeightMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => "Sector de Carga de Fuego por Peso #{$measurement->point_number} guardado exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'qs_mj_m2' => $measurement->qs_mj_m2,
            'risk_level' => $measurement->risk_level,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de Carga de Fuego por Peso.
     */
    public function destroyFireWeightMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->fireWeightMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->fireWeightMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de Carga de Fuego por Peso eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Obtener mediciones de dosimetría de un módulo.
     */
    public function getDosimetryMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->dosimetryMeasurements()
            ->with('staff')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item, $index) {
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

                $tpe = (float) ($item->tiempo_expos_h ?? 8.0);
                $lmp = DosimetryController::getLmpForTpe($tpe);
                $leqT = $item->leq_t_db !== null ? (float) $item->leq_t_db : null;
                $isCompliant = ($leqT !== null) ? ($leqT <= $lmp) : true;
                $cumple = ($leqT !== null) ? ($isCompliant ? 'SI' : 'NO') : '—';

                return [
                    'id' => $item->id,
                    'remote_id' => (string) $item->id,
                    'module_id' => (string) $item->module_id,
                    'monitoreo_id' => (string) $item->module_id,
                    'proyecto_id' => (string) $item->module->project_id,
                    'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                    'measurement_time' => $item->measurement_time,
                    'measured_at' => $item->measurement_date 
                        ? ($item->measurement_date->format('Y-m-d') . ' ' . ($item->measurement_time ?: '00:00:00'))
                        : null,
                    'area' => $item->area,
                    'punto_medicion' => $item->punto_medicion,
                    'tipo_ruido' => $item->tipo_ruido,
                    'tiempo_expos_h' => $tpe,
                    'ponderacion' => $item->ponderacion ?: 'A',
                    'respuesta' => $item->respuesta ?: 'Lento',
                    'duracion_medicion_h' => (float) ($item->duracion_medicion_h ?? 0.0),
                    'nps_max_db' => $item->nps_max_db !== null ? (float) $item->nps_max_db : null,
                    'nps_min_db' => $item->nps_min_db !== null ? (float) $item->nps_min_db : null,
                    'leq_t_db' => $leqT,
                    'lmp' => $lmp,
                    'cumple' => $cumple,
                    'is_compliant' => $isCompliant,
                    'dosis_pct' => (float) ($item->dosis_pct ?? 0.0),
                    'image_urls' => $imageUrls,
                    'images' => $imageUrls,
                    'image_path' => $imageUrls[0] ?? null,
                    'location' => $item->location,
                    'latitude' => $item->latitude !== null ? (float) $item->latitude : null,
                    'longitude' => $item->longitude !== null ? (float) $item->longitude : null,
                    'utm_zone' => $item->utm_zone,
                    'utm_easting' => $item->utm_easting,
                    'utm_northing' => $item->utm_northing,
                    'observations' => $item->observations,
                    'observaciones' => $item->observations,
                    'registered_by' => $item->registered_by,
                    'created_by' => $item->registered_by,
                    'staff_id' => $item->staff_id,
                    'local_uuid' => $item->local_uuid,
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
     * Guardar una medición de dosimetría desde la aplicación móvil.
     */
    public function storeDosimetryMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        // Inputs compatibles
        $area = $request->input('area') ?: 'Área Principal';
        $puntoMedicion = $request->input('punto_medicion') ?? $request->input('measurement_point') ?? 'Punto 01';
        $tipoRuido = $request->input('tipo_ruido') ?? $request->input('noise_type') ?? 'Fluctuante';
        $tpe = (float) ($request->input('tiempo_expos_h') ?? $request->input('tpe') ?? 8.0);
        $ponderacion = $request->input('ponderacion') ?: 'A';
        $respuesta = $request->input('respuesta') ?: 'Lento';
        $duracion = (float) ($request->input('duracion_medicion_h') ?? $request->input('duration_h') ?? 0.0);
        
        $npsMax = $request->input('nps_max_db');
        if ($npsMax !== null && $npsMax !== '') {
            $npsMax = (float) $npsMax;
        } else {
            $npsMax = null;
        }

        $npsMin = $request->input('nps_min_db');
        if ($npsMin !== null && $npsMin !== '') {
            $npsMin = (float) $npsMin;
        } else {
            $npsMin = null;
        }

        $leqT = $request->input('leq_t_db') ?? $request->input('leq_db');
        $leqT = $leqT !== null ? (float) $leqT : 0.0;

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
        $rawCreatedBy = $request->input('created_by');
        $rawRegisteredBy = $request->input('registered_by');
        $rawUserId = $request->input('user_id');

        if (empty($staffId)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int) $rawCreatedBy;
            } elseif (is_numeric($rawRegisteredBy)) {
                $staffId = (int) $rawRegisteredBy;
            } elseif (is_numeric($rawUserId)) {
                $staffId = (int) $rawUserId;
            }
        }

        if (empty($staffId)) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+mtoken_([a-zA-Z0-9+\/=]+)/i', $authHeader, $matches)) {
                $decodedToken = base64_decode($matches[1]);
                $tokenParts = explode(':', $decodedToken);
                if (!empty($tokenParts[0]) && is_numeric($tokenParts[0])) {
                    $staffId = (int) $tokenParts[0];
                }
            }
        }

        $registeredByName = null;
        if (!empty($staffId)) {
            $staff = Staff::find($staffId);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            } else {
                $user = User::find($staffId);
                if ($user) {
                    $registeredByName = $user->name;
                }
            }
        }

        if (empty($registeredByName)) {
            $candidateName = $rawRegisteredBy ?? $rawCreatedBy;
            if (!empty($candidateName) && !is_numeric($candidateName)) {
                $registeredByName = trim($candidateName);
                $foundStaff = Staff::where('name', $registeredByName)
                    ->orWhereRaw("CONCAT(TRIM(first_name), ' ', TRIM(last_name)) = ?", [$registeredByName])
                    ->first();
                if ($foundStaff) {
                    $staffId = $foundStaff->id;
                }
            }
        }

        if (empty($registeredByName) || is_numeric($registeredByName)) {
            if ($module->fieldStaff) {
                $registeredByName = $module->fieldStaff->full_name ?: $module->fieldStaff->name;
                $staffId = $module->field_staff_id;
            } else {
                $registeredByName = 'Técnico de Campo';
            }
        }

        // Numeración del punto
        $existingCount = $module->dosimetryMeasurements()->count();
        $pointNumber = $request->input('point_number') ?? $request->input('num') ?? str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT);

        // Procesamiento de imágenes
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/dosimetria/' . $moduleId);
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0777, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'dosi_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/measurements/dosimetria/' . $moduleId . '/' . $filename;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $filename = 'dosi_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/measurements/dosimetria/' . $moduleId . '/' . $filename;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $filename = 'dosi_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                array_unshift($uploadedImages, 'uploads/measurements/dosimetria/' . $moduleId . '/' . $filename);
            }
        }

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
        $utmZone = $request->input('utm_zone') ?? $request->input('zone') ?? '20K';
        $utmEasting = $request->input('easting') ?? $request->input('utm_easting');
        $utmNorthing = $request->input('northing') ?? $request->input('utm_northing');

        if (is_array($locationRaw)) {
            $latitude = $latitude ?? ($locationRaw['lat'] ?? ($locationRaw['latitude'] ?? null));
            $longitude = $longitude ?? ($locationRaw['lng'] ?? ($locationRaw['longitude'] ?? null));
            $utmZone = $utmZone ?? ($locationRaw['utm_zone'] ?? ($locationRaw['zone'] ?? '20K'));
            $utmEasting = $utmEasting ?? ($locationRaw['easting'] ?? ($locationRaw['utm_easting'] ?? null));
            $utmNorthing = $utmNorthing ?? ($locationRaw['northing'] ?? ($locationRaw['utm_northing'] ?? null));
            if (!empty($locationRaw['formatted'])) {
                $locationRaw = $locationRaw['formatted'];
            }
        } elseif (is_string($locationRaw) && (str_starts_with(trim($locationRaw), '{') || str_starts_with(trim($locationRaw), '['))) {
            $decodedLoc = json_decode($locationRaw, true);
            if (is_array($decodedLoc)) {
                $latitude = $latitude ?? ($decodedLoc['lat'] ?? ($decodedLoc['latitude'] ?? null));
                $longitude = $longitude ?? ($decodedLoc['lng'] ?? ($decodedLoc['longitude'] ?? null));
                $utmZone = $utmZone ?? ($decodedLoc['utm_zone'] ?? ($decodedLoc['zone'] ?? '20K'));
                $utmEasting = $utmEasting ?? ($decodedLoc['easting'] ?? ($decodedLoc['utm_easting'] ?? null));
                $utmNorthing = $utmNorthing ?? ($decodedLoc['northing'] ?? ($decodedLoc['utm_northing'] ?? null));
                if (!empty($decodedLoc['formatted'])) {
                    $locationRaw = $decodedLoc['formatted'];
                }
            }
        }

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng((float)$utmEasting, (float)$utmNorthing, $utmZone ?: '20K');
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        if (!empty($utmEasting) && !empty($utmNorthing) && !empty($utmZone)) {
            $eFormatted = is_numeric($utmEasting) ? number_format((float)$utmEasting, 3, '.', '') : $utmEasting;
            $nFormatted = is_numeric($utmNorthing) ? number_format((float)$utmNorthing, 3, '.', '') : $utmNorthing;
            $locationRaw = "E: {$eFormatted}, N: {$nFormatted}, Z: {$utmZone}";
        } elseif (!is_string($locationRaw) || empty($locationRaw)) {
            if ($latitude !== null && $longitude !== null) {
                $locationRaw = "{$latitude}, {$longitude}";
            } else {
                $locationRaw = null;
            }
        }

        // Cálculos normativos
        $lmp = DosimetryController::getLmpForTpe($tpe);
        $cumple = ($leqT <= $lmp) ? 'SI' : 'NO';
        $dosis = ($duracion > 0 && $tpe > 0) ? round(100.0 * ($duracion / 8.0) * pow(2.0, ($leqT - 85.0) / 3.0), 2) : null;

        $localUuid = $request->input('local_uuid');
        $measurementId = $request->input('id') ?? $request->input('remote_id');

        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->dosimetryMeasurements()->find($measurementId);
        }
        if (!$measurement && !empty($localUuid)) {
            $measurement = $module->dosimetryMeasurements()->where('local_uuid', $localUuid)->first();
        }

        $observations = $request->input('observations') ?? $request->input('observaciones');

        $data = [
            'point_number' => $measurement ? $measurement->point_number : $pointNumber,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'area' => $area,
            'punto_medicion' => $puntoMedicion,
            'tipo_ruido' => $tipoRuido,
            'tiempo_expos_h' => $tpe,
            'ponderacion' => $ponderacion,
            'respuesta' => $respuesta,
            'duracion_medicion_h' => $duracion,
            'nps_max_db' => $npsMax,
            'nps_min_db' => $npsMin,
            'leq_t_db' => $leqT,
            'dosis_pct' => $dosis,
            'cumple' => $cumple,
            'image_path' => $imagePath ?: ($measurement ? $measurement->image_path : null),
            'images' => !empty($uploadedImages) ? $uploadedImages : ($measurement ? $measurement->images : null),
            'location' => $locationRaw,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'observations' => $observations,
            'staff_id' => $staffId,
            'registered_by' => $registeredByName,
            'local_uuid' => $localUuid ?: ($measurement ? $measurement->local_uuid : null),
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->dosimetryMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->dosimetryMeasurements()->count();
        $module->save();

        $finalImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $finalUrls = array_values(array_filter(array_map(function($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $finalImages)));

        return response()->json([
            'success' => true,
            'message' => "Medición de dosimetría #{$measurement->point_number} guardada exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'leq_t_db' => $measurement->leq_t_db,
            'cumple' => $measurement->cumple,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de dosimetría.
     */
    public function destroyDosimetryMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->dosimetryMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->dosimetryMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de dosimetría eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Obtener mediciones de Ruido Ambiental de un módulo.
     */
    public function getAmbientNoiseMeasurements(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->ruidoAmbientalMeasurements()
            ->with('staff')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item, $index) {
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

                $p1Puntos = is_array($item->p1_norte_puntos) ? $item->p1_norte_puntos : (json_decode($item->p1_norte_puntos, true) ?: []);
                $p2Puntos = is_array($item->p2_sur_puntos) ? $item->p2_sur_puntos : (json_decode($item->p2_sur_puntos, true) ?: []);
                $p3Puntos = is_array($item->p3_este_puntos) ? $item->p3_este_puntos : (json_decode($item->p3_este_puntos, true) ?: []);
                $p4Puntos = is_array($item->p4_oeste_puntos) ? $item->p4_oeste_puntos : (json_decode($item->p4_oeste_puntos, true) ?: []);
                $allPoints = array_merge($p1Puntos, $p2Puntos, $p3Puntos, $p4Puntos);

                $leqCalculado = RuidoAmbientalController::calculateLeq($allPoints);
                $leqFinal = $item->leq_d !== null ? (float) $item->leq_d : $leqCalculado;
                $limite = $item->limite_normativa !== null ? (float) $item->limite_normativa : 68.0;
                $isCompliant = ($leqFinal !== null) ? ($leqFinal <= $limite) : true;
                $cumple = ($leqFinal !== null) ? ($isCompliant ? 'SI' : 'NO') : '—';

                return [
                    'id' => $item->id,
                    'remote_id' => (string) $item->id,
                    'module_id' => (string) $item->module_id,
                    'monitoreo_id' => (string) $item->module_id,
                    'proyecto_id' => (string) $item->project_id,
                    'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                    'measurement_time' => $item->measurement_time,
                    'measured_at' => $item->measurement_date 
                        ? ($item->measurement_date->format('Y-m-d') . ' ' . ($item->measurement_time ?: '00:00:00'))
                        : null,
                    'normativa' => $item->normativa ?: 'RASIM - ANEXO 12-C',
                    'tipo_zona' => $item->tipo_zona ?: 'Industrial - día',
                    'horario' => $item->horario ?: '08:00 a 22:00',
                    'limite_normativa' => $limite,
                    'zona_banda' => $item->zona_banda ?: '19K',

                    // Colindancias y Coordenadas
                    'norte_colindancia' => $item->norte_colindancia,
                    'norte_x' => $item->norte_x,
                    'norte_y' => $item->norte_y,
                    'sur_colindancia' => $item->sur_colindancia,
                    'sur_x' => $item->sur_x,
                    'sur_y' => $item->sur_y,
                    'este_colindancia' => $item->este_colindancia,
                    'este_x' => $item->este_x,
                    'este_y' => $item->este_y,
                    'oeste_colindancia' => $item->oeste_colindancia,
                    'oeste_x' => $item->oeste_x,
                    'oeste_y' => $item->oeste_y,

                    // Mediciones por punto cardinal
                    'p1_norte_inicio' => $item->p1_norte_inicio,
                    'p1_norte_fin' => $item->p1_norte_fin,
                    'p1_norte_puntos' => $p1Puntos,
                    'p1_norte_leq' => RuidoAmbientalController::calculateLeq($p1Puntos),

                    'p2_sur_inicio' => $item->p2_sur_inicio,
                    'p2_sur_fin' => $item->p2_sur_fin,
                    'p2_sur_puntos' => $p2Puntos,
                    'p2_sur_leq' => RuidoAmbientalController::calculateLeq($p2Puntos),

                    'p3_este_inicio' => $item->p3_este_inicio,
                    'p3_este_fin' => $item->p3_este_fin,
                    'p3_este_puntos' => $p3Puntos,
                    'p3_este_leq' => RuidoAmbientalController::calculateLeq($p3Puntos),

                    'p4_oeste_inicio' => $item->p4_oeste_inicio,
                    'p4_oeste_fin' => $item->p4_oeste_fin,
                    'p4_oeste_puntos' => $p4Puntos,
                    'p4_oeste_leq' => RuidoAmbientalController::calculateLeq($p4Puntos),

                    'mediciones_db' => $allPoints,
                    'leq_d' => $leqFinal,
                    'leq' => $leqFinal,
                    'nps_max' => $item->nps_max !== null ? (float) $item->nps_max : (!empty($allPoints) ? max($allPoints) : null),
                    'nps_min' => $item->nps_min !== null ? (float) $item->nps_min : (!empty($allPoints) ? min($allPoints) : null),
                    'is_compliant' => $isCompliant,
                    'cumple' => $cumple,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'location' => $item->location,
                    'image_urls' => $imageUrls,
                    'images' => $imageUrls,
                    'image_path' => $item->image_path ? (str_starts_with($item->image_path, 'http') ? $item->image_path : asset($item->image_path)) : null,
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
     * Guardar o actualizar una medición de Ruido Ambiental desde la aplicación móvil.
     */
    public function storeAmbientNoiseMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        // Inputs básicos
        $normativa = $request->input('normativa') ?: 'RASIM - ANEXO 12-C';
        $tipoZona = $request->input('tipo_zona') ?: 'Industrial - día';
        $horario = $request->input('horario') ?: '08:00 a 22:00';
        $limiteNormativa = (float) ($request->input('limite_normativa') ?? 68.0);
        $zonaBanda = $request->input('zona_banda') ?: '19K';

        // Colindancias y Coordenadas
        $norteColindancia = $request->input('norte_colindancia');
        $norteX = $request->input('norte_x');
        $norteY = $request->input('norte_y');

        $surColindancia = $request->input('sur_colindancia');
        $surX = $request->input('sur_x');
        $surY = $request->input('sur_y');

        $esteColindancia = $request->input('este_colindancia');
        $esteX = $request->input('este_x');
        $esteY = $request->input('este_y');

        $oesteColindancia = $request->input('oeste_colindancia');
        $oesteX = $request->input('oeste_x');
        $oesteY = $request->input('oeste_y');

        // Procesar puntos cardinales y lecturas
        $parsePoints = function ($val) {
            if (is_array($val)) {
                return array_values(array_filter(array_map('floatval', $val), 'is_numeric'));
            } elseif (is_string($val) && (str_starts_with(trim($val), '[') || str_starts_with(trim($val), '{'))) {
                $decoded = json_decode($val, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('floatval', $decoded), 'is_numeric'));
                }
            }
            return [];
        };

        $p1 = $parsePoints($request->input('p1_norte_puntos'));
        $p2 = $parsePoints($request->input('p2_sur_puntos'));
        $p3 = $parsePoints($request->input('p3_este_puntos'));
        $p4 = $parsePoints($request->input('p4_oeste_puntos'));
        $allPoints = array_merge($p1, $p2, $p3, $p4);

        if (empty($allPoints)) {
            $allPoints = $parsePoints($request->input('mediciones_db'));
        }

        $leq = RuidoAmbientalController::calculateLeq($allPoints);
        if ($leq === null && $request->has('leq_d') && is_numeric($request->input('leq_d'))) {
            $leq = (float) $request->input('leq_d');
        }

        $isCompliant = ($leq !== null) ? ($leq <= $limiteNormativa) : true;
        $npsMax = !empty($allPoints) ? max($allPoints) : ($request->input('nps_max') ? (float)$request->input('nps_max') : null);
        $npsMin = !empty($allPoints) ? min($allPoints) : ($request->input('nps_min') ? (float)$request->input('nps_min') : null);

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
        $rawCreatedBy = $request->input('created_by');
        $rawRegisteredBy = $request->input('registered_by');
        $rawUserId = $request->input('user_id');

        if (empty($staffId)) {
            if (is_numeric($rawCreatedBy)) {
                $staffId = (int) $rawCreatedBy;
            } elseif (is_numeric($rawRegisteredBy)) {
                $staffId = (int) $rawRegisteredBy;
            } elseif (is_numeric($rawUserId)) {
                $staffId = (int) $rawUserId;
            }
        }

        if (empty($staffId)) {
            $authHeader = $request->header('Authorization');
            if ($authHeader && preg_match('/Bearer\s+mtoken_([a-zA-Z0-9+\/=]+)/i', $authHeader, $matches)) {
                $decodedToken = base64_decode($matches[1]);
                $tokenParts = explode(':', $decodedToken);
                if (!empty($tokenParts[0]) && is_numeric($tokenParts[0])) {
                    $staffId = (int) $tokenParts[0];
                }
            }
        }

        $registeredByName = null;
        if (!empty($staffId)) {
            $staff = Staff::find($staffId);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            } else {
                $user = User::find($staffId);
                if ($user) {
                    $registeredByName = $user->name;
                }
            }
        }

        if (empty($registeredByName)) {
            $candidateName = $rawRegisteredBy ?? $rawCreatedBy;
            if (!empty($candidateName) && !is_numeric($candidateName)) {
                $registeredByName = trim($candidateName);
            }
        }

        if (empty($registeredByName)) {
            $registeredByName = 'Técnico de Campo';
        }

        // Manejo de imágenes (archivos multipart o URLs)
        $uploadedImages = [];
        $uploadDir = public_path('uploads/ruido_ambiental');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $filename = 'ra_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/ruido_ambiental/' . $filename;
                }
            }
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'ra_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/ruido_ambiental/' . $filename;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $filename = 'ra_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                array_unshift($uploadedImages, 'uploads/ruido_ambiental/' . $filename);
            }
        }

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

        // Ubicación
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $locationRaw = $request->input('location');

        if (is_array($locationRaw)) {
            $latitude = $latitude ?? ($locationRaw['lat'] ?? ($locationRaw['latitude'] ?? null));
            $longitude = $longitude ?? ($locationRaw['lng'] ?? ($locationRaw['longitude'] ?? null));
            if (!empty($locationRaw['formatted'])) {
                $locationRaw = $locationRaw['formatted'];
            }
        } elseif (is_string($locationRaw) && (str_starts_with(trim($locationRaw), '{') || str_starts_with(trim($locationRaw), '['))) {
            $decodedLoc = json_decode($locationRaw, true);
            if (is_array($decodedLoc)) {
                $latitude = $latitude ?? ($decodedLoc['lat'] ?? ($decodedLoc['latitude'] ?? null));
                $longitude = $longitude ?? ($decodedLoc['lng'] ?? ($decodedLoc['longitude'] ?? null));
                if (!empty($decodedLoc['formatted'])) {
                    $locationRaw = $decodedLoc['formatted'];
                }
            }
        }

        if (($latitude === null || $longitude === null) && !empty($norteX) && !empty($norteY)) {
            $eNum = (float) preg_replace('/[^0-9.]/', '', $norteX);
            $nNum = (float) preg_replace('/[^0-9.]/', '', $norteY);
            if ($eNum > 0 && $nNum > 0) {
                $conv = $this->utmToLatLng($eNum, $nNum, $zonaBanda ?: '19K');
                $latitude = $conv['lat'];
                $longitude = $conv['lng'];
            }
        }

        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;

        if ($measurementId) {
            $measurement = $module->ruidoAmbientalMeasurements()->find($measurementId);
        }

        if ($measurement) {
            $pointNumber = $measurement->point_number;
            $statusCode = 200;
        } else {
            $existingCount = $module->ruidoAmbientalMeasurements()->count();
            $pointNumber = $request->input('point_number') ?? ('P-' . str_pad($existingCount + 1, 2, '0', STR_PAD_LEFT));
            $measurement = new RuidoAmbientalMeasurement();
            $measurement->module_id = $module->id;
            $measurement->project_id = $module->project_id;
            $statusCode = 201;
        }

        $measurement->staff_id = $staffId ?: ($module->field_staff_id ?? null);
        $measurement->point_number = $pointNumber;
        $measurement->measurement_date = $measurementDate;
        $measurement->measurement_time = $measurementTime;
        $measurement->normativa = $normativa;
        $measurement->tipo_zona = $tipoZona;
        $measurement->horario = $horario;
        $measurement->limite_normativa = $limiteNormativa;
        $measurement->zona_banda = $zonaBanda;

        $measurement->norte_colindancia = $norteColindancia;
        $measurement->norte_x = $norteX;
        $measurement->norte_y = $norteY;
        $measurement->sur_colindancia = $surColindancia;
        $measurement->sur_x = $surX;
        $measurement->sur_y = $surY;
        $measurement->este_colindancia = $esteColindancia;
        $measurement->este_x = $esteX;
        $measurement->este_y = $esteY;
        $measurement->oeste_colindancia = $oesteColindancia;
        $measurement->oeste_x = $oesteX;
        $measurement->oeste_y = $oesteY;

        $measurement->p1_norte_inicio = $request->input('p1_norte_inicio');
        $measurement->p1_norte_fin = $request->input('p1_norte_fin');
        $measurement->p1_norte_puntos = $p1;

        $measurement->p2_sur_inicio = $request->input('p2_sur_inicio');
        $measurement->p2_sur_fin = $request->input('p2_sur_fin');
        $measurement->p2_sur_puntos = $p2;

        $measurement->p3_este_inicio = $request->input('p3_este_inicio');
        $measurement->p3_este_fin = $request->input('p3_este_fin');
        $measurement->p3_este_puntos = $p3;

        $measurement->p4_oeste_inicio = $request->input('p4_oeste_inicio');
        $measurement->p4_oeste_fin = $request->input('p4_oeste_fin');
        $measurement->p4_oeste_puntos = $p4;

        $measurement->mediciones_db = $allPoints;
        $measurement->leq_d = $leq;
        $measurement->nps_max = $npsMax;
        $measurement->nps_min = $npsMin;
        $measurement->is_compliant = $isCompliant;

        $measurement->latitude = $latitude;
        $measurement->longitude = $longitude;
        $measurement->location = is_string($locationRaw) ? $locationRaw : json_encode($locationRaw);

        if (!empty($uploadedImages)) {
            $measurement->images = $uploadedImages;
            $measurement->image_path = $imagePath;
        }

        $measurement->observations = $request->input('observations') ?? $request->input('observaciones');
        $measurement->registered_by = $registeredByName;
        $measurement->save();

        $module->points_completed = $module->ruidoAmbientalMeasurements()->count();
        $module->save();

        $finalImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $finalUrls = array_values(array_filter(array_map(function($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $finalImages)));

        return response()->json([
            'success' => true,
            'message' => "Punto de medición de Ruido Ambiental '{$measurement->point_number}' guardado exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'leq_d' => $measurement->leq_d,
            'is_compliant' => $measurement->is_compliant,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de Ruido Ambiental.
     */
    public function destroyAmbientNoiseMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->ruidoAmbientalMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->ruidoAmbientalMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de Ruido Ambiental eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Obtener mediciones de opacidad para la app móvil.
     */
    public function getOpacityMeasurements($moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->opacityMeasurements()->with('staff')->get()->map(function ($item) {
            $photos = is_array($item->photo_paths) ? $item->photo_paths : (json_decode($item->photo_paths, true) ?: []);
            $photoUrls = array_values(array_filter(array_map(function($p) {
                if (!$p) return null;
                if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
                return asset($p);
            }, $photos)));

            return [
                'id' => $item->id,
                'remote_id' => (string) $item->id,
                'point_number' => $item->point_number,
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'measured_at' => $item->measurement_date ? ($item->measurement_date->format('Y-m-d') . ' ' . ($item->measurement_time ?: '00:00:00')) : null,
                'area' => $item->area ?? '',
                'sector' => $item->area ?? '',
                'area_sector' => $item->area ?? '',
                'altitud' => $item->altitud ?? '1500-3000',
                'tipo_vehiculo' => $item->tipo_vehiculo,
                'marca' => $item->marca,
                'modelo' => $item->modelo,
                'placa' => $item->placa,
                'temp_c' => $item->temp_c,
                'opa_1' => $item->opa_1,
                'opa_2' => $item->opa_2,
                'opa_3' => $item->opa_3,
                'rpm_1' => $item->rpm_1,
                'rpm_2' => $item->rpm_2,
                'rpm_3' => $item->rpm_3,
                'opa_promedio' => $item->opa_promedio,
                'rpm_promedio' => $item->rpm_promedio,
                'limite_normativa' => $item->limite_normativa,
                'is_compliant' => (bool) $item->is_compliant,
                'cumple' => $item->is_compliant ? 'Cumple' : 'No cumple',
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting,
                'utm_northing' => $item->utm_northing,
                'location_description' => $item->location_description,
                'location' => $item->location_description ?: (($item->latitude !== null && $item->longitude !== null) ? "{$item->latitude}, {$item->longitude}" : null),
                'observations' => $item->observations,
                'observaciones' => $item->observations,
                'photos' => $photos,
                'image_urls' => $photoUrls,
                'staff_id' => $item->staff_id,
                'registered_by' => $item->staff ? ($item->staff->full_name ?: $item->staff->name) : 'Técnico de Campo',
            ];
        });

        return response()->json([
            'success' => true,
            'count' => $measurements->count(),
            'measurements' => $measurements,
        ]);
    }

    /**
     * Guardar o sincronizar medición de opacidad vehicular desde la app móvil.
     */
    public function storeOpacityMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        // Resolver Staff
        $staffId = null;
        $registeredByName = 'Técnico de Campo';
        $createdBy = $request->input('created_by') ?? $request->input('staff_id');

        if (!empty($createdBy)) {
            if (is_numeric($createdBy)) {
                $staff = Staff::find($createdBy);
                if ($staff) {
                    $staffId = $staff->id;
                    $registeredByName = $staff->full_name ?: $staff->name;
                }
            } else {
                $staff = Staff::whereRaw('LOWER(name) = ?', [strtolower(trim($createdBy))])
                    ->orWhereRaw('LOWER(email) = ?', [strtolower(trim($createdBy))])
                    ->first();
                if ($staff) {
                    $staffId = $staff->id;
                    $registeredByName = $staff->full_name ?: $staff->name;
                }
            }
        }

        if (empty($staffId) && $module->field_staff_id) {
            $staffId = $module->field_staff_id;
            if ($module->fieldStaff) {
                $registeredByName = $module->fieldStaff->full_name ?: $module->fieldStaff->name;
            }
        }

        // Fecha y Hora
        $measuredAt = $request->input('measured_at') ?? $request->input('measurement_date');
        $measurementDate = null;
        $measurementTime = $request->input('measurement_time');

        if (!empty($measuredAt)) {
            try {
                $carbonDate = Carbon::parse($measuredAt);
                $measurementDate = $carbonDate->toDateString();
                if (empty($measurementTime)) {
                    $measurementTime = $carbonDate->format('H:i');
                }
            } catch (\Exception $e) {
                $measurementDate = Carbon::today()->toDateString();
            }
        } else {
            $measurementDate = Carbon::today()->toDateString();
            if (empty($measurementTime)) {
                $measurementTime = Carbon::now()->format('H:i');
            }
        }

        // Área / Sector y Altitud
        $area = $request->input('area') ?? $request->input('sector') ?? $request->input('area_sector') ?? $request->input('location_area');
        $altitud = $request->input('altitud') ?? $request->input('altitude') ?? $request->input('rango_altitud', '1500-3000');

        // Datos del Vehículo
        $tipoVehiculo = $request->input('tipo_vehiculo') ?: ($request->input('nombre_vehiculo') ?: 'Vehículo Diésel');
        $marca = $request->input('marca') ?: '—';
        $modelo = $request->input('modelo') ?: '—';
        $placa = strtoupper(trim((string)($request->input('placa') ?: '—')));

        // Helper para convertir strings con coma o punto a float limpio
        $cleanFloat = function($val) {
            if ($val === null || $val === '') return null;
            if (is_numeric($val)) return (float) $val;
            $clean = str_replace([' ', ','], ['', '.'], (string) $val);
            return is_numeric($clean) ? (float) $clean : null;
        };

        // Lecturas de Temperatura, RPM y Opacidad
        $tempC = $cleanFloat($request->input('temp_c'));
        $opa1 = $cleanFloat($request->input('opa_1') ?? $request->input('lectura_1'));
        $opa2 = $cleanFloat($request->input('opa_2') ?? $request->input('lectura_2'));
        $opa3 = $cleanFloat($request->input('opa_3') ?? $request->input('lectura_3'));

        $rpm1 = $cleanFloat($request->input('rpm_1'));
        $rpm2 = $cleanFloat($request->input('rpm_2'));
        $rpm3 = $cleanFloat($request->input('rpm_3'));

        // Cálculos de promedios
        $opaVals = array_values(array_filter([$opa1, $opa2, $opa3], fn($v) => $v !== null));
        $opaPromedio = count($opaVals) > 0 ? round(array_sum($opaVals) / count($opaVals), 2) : $cleanFloat($request->input('opa_promedio'));

        $rpmVals = array_values(array_filter([$rpm1, $rpm2, $rpm3], fn($v) => $v !== null));
        $rpmPromedio = count($rpmVals) > 0 ? round(array_sum($rpmVals) / count($rpmVals), 2) : $cleanFloat($request->input('rpm_promedio'));

        // Límite permisible (Norma NB 62002)
        $limite = $request->filled('limite_normativa') ? $cleanFloat($request->input('limite_normativa')) : 2.80;
        $isCompliant = ($opaPromedio !== null) ? ($opaPromedio <= $limite) : true;

        // Ubicación y Coordenadas
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $locationRaw = $request->input('location');
        $utmZone = $request->input('utm_zone') ?? $request->input('zone', '19K');
        $utmEasting = $request->input('easting') ?? $request->input('utm_easting');
        $utmNorthing = $request->input('northing') ?? $request->input('utm_northing');

        if (is_array($locationRaw)) {
            $latitude = $latitude ?? ($locationRaw['lat'] ?? ($locationRaw['latitude'] ?? null));
            $longitude = $longitude ?? ($locationRaw['lng'] ?? ($locationRaw['longitude'] ?? null));
            $utmZone = $utmZone ?? ($locationRaw['utm_zone'] ?? ($locationRaw['zone'] ?? '19K'));
            $utmEasting = $utmEasting ?? ($locationRaw['easting'] ?? ($locationRaw['utm_easting'] ?? null));
            $utmNorthing = $utmNorthing ?? ($locationRaw['northing'] ?? ($locationRaw['utm_northing'] ?? null));
            if (!empty($locationRaw['formatted'])) {
                $locationRaw = $locationRaw['formatted'];
            }
        } elseif (is_string($locationRaw) && (str_starts_with(trim($locationRaw), '{') || str_starts_with(trim($locationRaw), '['))) {
            $decodedLoc = json_decode($locationRaw, true);
            if (is_array($decodedLoc)) {
                $latitude = $latitude ?? ($decodedLoc['lat'] ?? ($decodedLoc['latitude'] ?? null));
                $longitude = $longitude ?? ($decodedLoc['lng'] ?? ($decodedLoc['longitude'] ?? null));
                $utmZone = $utmZone ?? ($decodedLoc['utm_zone'] ?? ($decodedLoc['zone'] ?? '19K'));
                $utmEasting = $utmEasting ?? ($decodedLoc['easting'] ?? ($decodedLoc['utm_easting'] ?? null));
                $utmNorthing = $utmNorthing ?? ($decodedLoc['northing'] ?? ($decodedLoc['utm_northing'] ?? null));
                if (!empty($decodedLoc['formatted'])) {
                    $locationRaw = $decodedLoc['formatted'];
                }
            }
        }

        if (($latitude === null || $longitude === null) && !empty($utmEasting) && !empty($utmNorthing)) {
            $conv = $this->utmToLatLng((float)$utmEasting, (float)$utmNorthing, $utmZone ?: '19K');
            $latitude = $conv['lat'];
            $longitude = $conv['lng'];
        }

        if (!empty($utmEasting) && !empty($utmNorthing) && !empty($utmZone)) {
            $locationDescription = "E: {$utmEasting}, N: {$utmNorthing}, Z: {$utmZone}";
        } elseif (!empty($locationRaw) && is_string($locationRaw)) {
            $locationDescription = $locationRaw;
        } elseif ($latitude !== null && $longitude !== null) {
            $locationDescription = "{$latitude}, {$longitude}";
        } else {
            $locationDescription = null;
        }

        // Procesamiento de fotos
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements/opacity');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $filename = 'opa_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/measurements/opacity/' . $filename;
                }
            }
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'opa_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $filename);
                    $uploadedImages[] = 'uploads/measurements/opacity/' . $filename;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $filename = 'opa_' . $module->id . '_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $filename);
                array_unshift($uploadedImages, 'uploads/measurements/opacity/' . $filename);
            }
        }

        // Buscar registro existente para actualizar o crear uno nuevo
        $measurementId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if (!empty($measurementId)) {
            $measurement = $module->opacityMeasurements()->find($measurementId);
        }

        $nextIndex = $module->opacityMeasurements()->count() + 1;
        $pointNum = $request->input('point_number') ?: ($measurement ? $measurement->point_number : ('V-' . str_pad($nextIndex, 2, '0', STR_PAD_LEFT)));
        $observations = $request->input('observations') ?? $request->input('observaciones');

        $data = [
            'module_id' => $module->id,
            'project_id' => $module->project_id,
            'staff_id' => $staffId,
            'point_number' => $pointNum,
            'measurement_date' => $measurementDate,
            'measurement_time' => $measurementTime,
            'area' => $area,
            'altitud' => $altitud,
            'tipo_vehiculo' => $tipoVehiculo,
            'marca' => $marca,
            'modelo' => $modelo,
            'placa' => $placa,
            'temp_c' => $tempC,
            'opa_1' => $opa1,
            'opa_2' => $opa2,
            'opa_3' => $opa3,
            'rpm_1' => $rpm1,
            'rpm_2' => $rpm2,
            'rpm_3' => $rpm3,
            'opa_promedio' => $opaPromedio,
            'rpm_promedio' => $rpmPromedio,
            'limite_normativa' => $limite,
            'is_compliant' => $isCompliant,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'location_description' => $locationDescription,
            'observations' => $observations,
        ];

        if (!empty($uploadedImages)) {
            $data['photo_paths'] = $uploadedImages;
        }

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->opacityMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->opacityMeasurements()->count();
        $module->save();

        $finalPhotos = is_array($measurement->photo_paths) ? $measurement->photo_paths : (json_decode($measurement->photo_paths, true) ?: []);
        $finalUrls = array_values(array_filter(array_map(function($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $finalPhotos)));

        return response()->json([
            'success' => true,
            'message' => "Medición de opacidad para {$measurement->placa} guardada exitosamente.",
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'placa' => $measurement->placa,
            'area' => $measurement->area,
            'sector' => $measurement->area,
            'altitud' => $measurement->altitud,
            'opa_promedio' => $measurement->opa_promedio,
            'is_compliant' => $measurement->is_compliant,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Listar mediciones de gases de un módulo para la app móvil.
     */
    public function getGasesMeasurements($moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->gasMeasurements()->orderBy('id', 'asc')->get()->map(function ($item) {
            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function ($p) {
                if (!$p) return null;
                if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
                return asset($p);
            }, $rawImages)));

            return [
                'id' => (string) $item->id,
                'local_uuid' => $item->local_uuid,
                'remote_id' => (string) $item->id,
                'point_number' => $item->point_number,
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'area' => $item->area,
                'workstation' => $item->workstation,
                'measurement_point' => $item->measurement_point,
                'activity_description' => $item->activity_description,
                'temperatura' => $item->temperatura !== null ? (float)$item->temperatura : null,
                'presion_atm' => $item->presion_atm !== null ? (float)$item->presion_atm : null,
                'vel_aire' => $item->vel_aire !== null ? (float)$item->vel_aire : null,
                'selected_gases' => is_array($item->selected_gases) ? $item->selected_gases : json_decode($item->selected_gases, true),
                'gases_readings' => is_array($item->gases_readings) ? $item->gases_readings : json_decode($item->gases_readings, true),
                'o2_values' => is_array($item->o2_values) ? $item->o2_values : json_decode($item->o2_values, true),
                'o2_prom' => $item->o2_prom !== null ? (float)$item->o2_prom : null,
                'h2s_values' => is_array($item->h2s_values) ? $item->h2s_values : json_decode($item->h2s_values, true),
                'h2s_prom' => $item->h2s_prom !== null ? (float)$item->h2s_prom : null,
                'co_values' => is_array($item->co_values) ? $item->co_values : json_decode($item->co_values, true),
                'co_prom' => $item->co_prom !== null ? (float)$item->co_prom : null,
                'lel_values' => is_array($item->lel_values) ? $item->lel_values : json_decode($item->lel_values, true),
                'lel_prom' => $item->lel_prom !== null ? (float)$item->lel_prom : null,
                'hcho_values' => is_array($item->hcho_values) ? $item->hcho_values : json_decode($item->hcho_values, true),
                'hcho_prom' => $item->hcho_prom !== null ? (float)$item->hcho_prom : null,
                'tvoc_values' => is_array($item->tvoc_values) ? $item->tvoc_values : json_decode($item->tvoc_values, true),
                'tvoc_prom' => $item->tvoc_prom !== null ? (float)$item->tvoc_prom : null,
                'co2_values' => is_array($item->co2_values) ? $item->co2_values : json_decode($item->co2_values, true),
                'co2_prom' => $item->co2_prom !== null ? (float)$item->co2_prom : null,
                'as_values' => is_array($item->as_values) ? $item->as_values : json_decode($item->as_values, true),
                'as_prom' => $item->as_prom !== null ? (float)$item->as_prom : null,
                'so2_values' => is_array($item->so2_values) ? $item->so2_values : json_decode($item->so2_values, true),
                'so2_prom' => $item->so2_prom !== null ? (float)$item->so2_prom : null,
                'nh3_values' => is_array($item->nh3_values) ? $item->nh3_values : json_decode($item->nh3_values, true),
                'nh3_prom' => $item->nh3_prom !== null ? (float)$item->nh3_prom : null,
                'cl2_values' => is_array($item->cl2_values) ? $item->cl2_values : json_decode($item->cl2_values, true),
                'cl2_prom' => $item->cl2_prom !== null ? (float)$item->cl2_prom : null,
                'tcov_values' => is_array($item->tcov_values) ? $item->tcov_values : json_decode($item->tcov_values, true),
                'tcov_prom' => $item->tcov_prom !== null ? (float)$item->tcov_prom : null,
                'no2_values' => is_array($item->no2_values) ? $item->no2_values : json_decode($item->no2_values, true),
                'no2_prom' => $item->no2_prom !== null ? (float)$item->no2_prom : null,
                'location' => $item->location,
                'latitude' => $item->latitude !== null ? (float)$item->latitude : null,
                'longitude' => $item->longitude !== null ? (float)$item->longitude : null,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting !== null ? (float)$item->utm_easting : null,
                'utm_northing' => $item->utm_northing !== null ? (float)$item->utm_northing : null,
                'image_urls' => $imagesUrls,
                'observations' => $item->observations,
                'registered_by' => $item->registered_by,
                'staff_id' => $item->staff_id,
            ];
        });

        return response()->json([
            'success' => true,
            'measurements' => $measurements,
            'points_completed' => $measurements->count(),
        ]);
    }

    /**
     * Registrar o actualizar una medición de gases desde la app móvil.
     */
    public function storeGasesMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $localUuid = $request->input('local_uuid');
        $id = $request->input('id');

        $measurement = null;
        if (!empty($localUuid)) {
            $measurement = $module->gasMeasurements()->where('local_uuid', $localUuid)->first();
        }
        if (!$measurement && !empty($id)) {
            $measurement = $module->gasMeasurements()->find($id);
        }

        $date = $request->input('measurement_date') ?? $request->input('date') ?? $request->input('measured_at') ?? Carbon::now()->format('Y-m-d');
        if (str_contains($date, 'T')) {
            $date = explode('T', $date)[0];
        }

        $time = $request->input('measurement_time') ?? $request->input('time') ?? Carbon::now()->format('H:i');

        $area = $request->input('area') ?? 'Área de Trabajo';
        $workstation = $request->input('workstation') ?? $request->input('puesto_trabajo') ?? 'Puesto General';
        $pointNumber = $request->input('point_number');
        if (empty($pointNumber)) {
            $count = $module->gasMeasurements()->count();
            $pointNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        }

        $temp = $request->filled('temperatura') ? (float)$request->input('temperatura') : null;
        $presion = $request->filled('presion_atm') ? (float)$request->input('presion_atm') : null;
        $velAire = $request->filled('vel_aire') ? (float)$request->input('vel_aire') : null;

        $rawSelectedGases = $request->input('selected_gases');
        $selectedGases = is_array($rawSelectedGases) ? $rawSelectedGases : (json_decode($rawSelectedGases, true) ?: []);
        if (is_string($selectedGases)) {
            $selectedGases = json_decode($selectedGases, true) ?: [];
        }

        $rawGasesReadings = $request->input('gases_readings');
        $gasesReadings = is_array($rawGasesReadings) ? $rawGasesReadings : (json_decode($rawGasesReadings, true) ?: []);
        if (is_string($gasesReadings)) {
            $gasesReadings = json_decode($gasesReadings, true) ?: [];
        }

        if (empty($selectedGases) && is_array($gasesReadings) && !empty($gasesReadings)) {
            $selectedGases = array_keys($gasesReadings);
        }

        // Subida de imágenes
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $safeExt = in_array($ext, ['jpeg', 'jpg', 'png', 'webp']) ? $ext : 'jpg';
                    $fname = 'gas_' . time() . '_' . uniqid() . '.' . $safeExt;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension());
                $safeExt = in_array($ext, ['jpeg', 'jpg', 'png', 'webp']) ? $ext : 'jpg';
                $fname = 'gas_' . time() . '_' . uniqid() . '.' . $safeExt;
                $file->move($uploadDir, $fname);
                array_unshift($uploadedImages, 'uploads/measurements/' . $fname);
            }
        }

        // Si se enviaron URLs remotas o preexistentes
        if ($request->filled('image_urls')) {
            $rawUrls = $request->input('image_urls');
            $decodedUrls = is_array($rawUrls) ? $rawUrls : json_decode($rawUrls, true);
            if (is_array($decodedUrls)) {
                foreach ($decodedUrls as $u) {
                    if ($u && !in_array($u, $uploadedImages)) {
                        $uploadedImages[] = $u;
                    }
                }
            }
        }

        $lat = $request->filled('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->filled('longitude') ? (float)$request->input('longitude') : null;
        $utmZone = $request->input('utm_zone');
        $utmE = $request->filled('utm_easting') ? (float)$request->input('utm_easting') : null;
        $utmN = $request->filled('utm_northing') ? (float)$request->input('utm_northing') : null;

        $locInput = $request->input('location');
        if (is_string($locInput) && (str_starts_with($locInput, '{') || str_starts_with($locInput, '['))) {
            $locDecoded = json_decode($locInput, true);
            if (is_array($locDecoded)) {
                if ($utmZone === null && !empty($locDecoded['utm_zone'])) $utmZone = $locDecoded['utm_zone'];
                if ($utmE === null && !empty($locDecoded['easting'])) $utmE = (float)$locDecoded['easting'];
                if ($utmN === null && !empty($locDecoded['northing'])) $utmN = (float)$locDecoded['northing'];
                if ($lat === null && !empty($locDecoded['latitude'])) $lat = (float)$locDecoded['latitude'];
                if ($lng === null && !empty($locDecoded['longitude'])) $lng = (float)$locDecoded['longitude'];
            }
        }

        $staffId = $request->input('staff_id');
        $registeredBy = $request->input('registered_by') ?? $request->input('created_by');
        if (!empty($staffId)) {
            $st = Staff::find($staffId);
            if ($st) $registeredBy = $st->full_name ?: $st->name;
        }

        $data = [
            'point_number' => $pointNumber,
            'measurement_date' => $date,
            'measurement_time' => $time,
            'area' => $area,
            'workstation' => $workstation,
            'measurement_point' => $request->input('measurement_point') ?? $workstation,
            'activity_description' => $request->input('activity_description') ?? 'Operaciones generales',
            'temperatura' => $temp,
            'presion_atm' => $presion,
            'vel_aire' => $velAire,
            'selected_gases' => $selectedGases,
            'gases_readings' => $gasesReadings,
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'location' => $request->input('location'),
            'observations' => $request->input('observations') ?? $request->input('observaciones'),
            'registered_by' => $registeredBy,
            'staff_id' => $staffId,
            'local_uuid' => $localUuid ?? ($measurement ? $measurement->local_uuid : Str::uuid()->toString()),
        ];

        // Columnas de respaldo por gas si vienen
        $gasesDefs = GasesController::getGasesDefinitions();
        foreach ($gasesDefs as $gDef) {
            $gk = $gDef['key'];
            if (is_array($gasesReadings) && isset($gasesReadings[$gk])) {
                $gData = $gasesReadings[$gk];
                $vals = [];
                if (isset($gData['values']) && is_array($gData['values'])) {
                    $vals = array_values(array_map('floatval', array_filter($gData['values'], 'is_numeric')));
                } elseif (isset($gData['med1']) || isset($gData['med2']) || isset($gData['med3'])) {
                    $m1 = isset($gData['med1']) && is_numeric($gData['med1']) ? (float)$gData['med1'] : null;
                    $m2 = isset($gData['med2']) && is_numeric($gData['med2']) ? (float)$gData['med2'] : null;
                    $m3 = isset($gData['med3']) && is_numeric($gData['med3']) ? (float)$gData['med3'] : null;
                    $vals = array_values(array_filter([$m1, $m2, $m3], fn($x) => $x !== null));
                }
                $data["{$gk}_values"] = !empty($vals) ? $vals : null;
                $data["{$gk}_prom"] = isset($gData['prom']) && is_numeric($gData['prom']) ? (float)$gData['prom'] : (!empty($vals) ? array_sum($vals) / count($vals) : null);
            }
        }

        if (!empty($uploadedImages)) {
            $data['images'] = $uploadedImages;
            $data['image_path'] = $uploadedImages[0];
        }

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->gasMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->gasMeasurements()->count();
        $module->save();

        $finalImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $finalUrls = array_values(array_filter(array_map(function ($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $finalImages)));

        return response()->json([
            'success' => true,
            'message' => "Punto de medición de gases #{$measurement->point_number} guardado exitosamente.",
            'id' => (string) $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'area' => $measurement->area,
            'workstation' => $measurement->workstation,
            'local_uuid' => $measurement->local_uuid,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de gases desde la app móvil.
     */
    public function destroyGasesMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->gasMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->gasMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Medición de gases eliminada correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Obtener puntos de inspección fotográfica para la app móvil.
     */
    public function getPhotographicInspectionMeasurements($moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->photographicInspections()
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($item, $index) {
                $images = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
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
                    'inspection_date' => $item->inspection_date ? $item->inspection_date->format('Y-m-d') : null,
                    'inspection_time' => $item->inspection_time,
                    'measured_at' => $item->inspection_date 
                        ? ($item->inspection_date->format('Y-m-d') . ' ' . ($item->inspection_time ?: '00:00:00'))
                        : null,
                    'area' => $item->area,
                    'observation' => $item->observation,
                    'observacion' => $item->observation,
                    'description' => $item->description,
                    'descripcion' => $item->description,
                    'image_urls' => $imageUrls,
                    'images' => $imageUrls,
                    'location' => $item->location,
                    'latitude' => $item->latitude !== null ? (float)$item->latitude : null,
                    'longitude' => $item->longitude !== null ? (float)$item->longitude : null,
                    'utm_zone' => $item->utm_zone ?: '20K',
                    'utm_easting' => $item->utm_easting !== null ? (float)$item->utm_easting : null,
                    'utm_northing' => $item->utm_northing !== null ? (float)$item->utm_northing : null,
                    'gps_accuracy' => $item->gps_accuracy !== null ? (float)$item->gps_accuracy : null,
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
     * Guardar un punto de inspección fotográfica desde la aplicación móvil.
     */
    public function storePhotographicInspectionMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $pointNumber = $request->input('point_number') ?? $request->input('punto_numero');
        $area = $request->input('area') ?? 'Área General';
        $observation = $request->input('category') ?? $request->input('categoria') ?? $request->input('observation') ?? $request->input('observacion') ?? 'Inspección visual';
        $description = $request->input('description') ?? $request->input('descripcion');
        $location = $request->input('location') ?? $request->input('ubicacion');
        $latitude = $request->input('latitude') ?? $request->input('latitud');
        $longitude = $request->input('longitude') ?? $request->input('longitud');
        $utmZone = $request->input('utm_zone') ?? $request->input('zona_utm') ?? '20K';
        $utmEasting = $request->input('utm_easting') ?? $request->input('este_utm');
        $utmNorthing = $request->input('utm_northing') ?? $request->input('norte_utm');
        $gpsAccuracy = $request->input('gps_accuracy') ?? $request->input('precision_gps');

        $measuredAt = $request->input('measured_at') ?? $request->input('inspection_date');
        $inspectionDate = date('Y-m-d');
        $inspectionTime = date('H:i');
        if (!empty($measuredAt)) {
            try {
                $c = Carbon::parse($measuredAt);
                $inspectionDate = $c->format('Y-m-d');
                $inspectionTime = $c->format('H:i');
            } catch (\Exception $e) {}
        }
        if ($request->filled('inspection_time')) {
            $inspectionTime = $request->input('inspection_time');
        }

        // Subida de imágenes (multipart o base64)
        $uploadedImages = [];
        $uploadDir = public_path('uploads/inspecciones_fotograficas');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        $files = [];
        if ($request->hasFile('photos')) {
            $f = $request->file('photos');
            $files = is_array($f) ? $f : [$f];
        } elseif ($request->hasFile('images')) {
            $f = $request->file('images');
            $files = is_array($f) ? $f : [$f];
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        } elseif ($request->hasFile('photo')) {
            $files = [$request->file('photo')];
        }

        foreach ($files as $photo) {
            if ($photo && $photo->isValid() && count($uploadedImages) < 3) {
                $filename = 'insp_' . $moduleId . '_' . time() . '_' . Str::random(6) . '.' . $photo->getClientOriginalExtension();
                $photo->move($uploadDir, $filename);
                $uploadedImages[] = '/uploads/inspecciones_fotograficas/' . $filename;
            }
        }

        // Base64 photos support
        $base64Photos = $request->input('base64_photos') ?? $request->input('photos_base64');
        if (is_array($base64Photos)) {
            foreach ($base64Photos as $b64) {
                if (count($uploadedImages) >= 3) break;
                if (!empty($b64) && is_string($b64)) {
                    $cleanB64 = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
                    $decoded = base64_decode($cleanB64);
                    if ($decoded) {
                        $filename = 'insp_' . $moduleId . '_' . time() . '_' . Str::random(6) . '.jpg';
                        file_put_contents($uploadDir . '/' . $filename, $decoded);
                        $uploadedImages[] = '/uploads/inspecciones_fotograficas/' . $filename;
                    }
                }
            }
        }

        // Si ya tenía imágenes remotas pasadas en el payload
        $existingImages = $request->input('image_urls') ?? $request->input('images');
        if (is_array($existingImages) && empty($uploadedImages)) {
            $uploadedImages = array_slice($existingImages, 0, 3);
        }

        if (empty($pointNumber)) {
            $count = $module->photographicInspections()->count();
            $pointNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        }

        $remoteId = $request->input('id') ?? $request->input('remote_id');
        $measurement = null;
        if ($remoteId && is_numeric($remoteId)) {
            $measurement = $module->photographicInspections()->find($remoteId);
        }

        $data = [
            'point_number' => $pointNumber,
            'inspection_date' => $inspectionDate,
            'inspection_time' => $inspectionTime,
            'area' => $area,
            'observation' => $observation,
            'description' => $description,
            'location' => $location,
            'latitude' => $latitude !== null ? (float)$latitude : null,
            'longitude' => $longitude !== null ? (float)$longitude : null,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmEasting !== null ? (float)$utmEasting : null,
            'utm_northing' => $utmNorthing !== null ? (float)$utmNorthing : null,
            'gps_accuracy' => $gpsAccuracy !== null ? (float)$gpsAccuracy : null,
            'registered_by' => $request->input('registered_by') ?? $request->input('created_by'),
            'staff_id' => $request->input('staff_id'),
        ];

        if (!empty($uploadedImages)) {
            $data['images'] = $uploadedImages;
            $data['image_path'] = $uploadedImages[0];
        }

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->photographicInspections()->create($data);
            $statusCode = 201;
        }

        $completedCount = $module->photographicInspections()->count();
        $module->points_completed = $completedCount;
        if ($module->points_total < $completedCount) {
            $module->points_total = $completedCount;
        }
        $module->status = ($module->points_total > 0 && $completedCount >= $module->points_total) ? 'Completado' : 'En Progreso';
        $module->status_theme = ($module->status === 'Completado') ? 'done' : 'in_progress';
        $module->save();

        $finalImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $finalUrls = array_values(array_filter(array_map(function ($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $finalImages)));

        return response()->json([
            'success' => true,
            'message' => "Punto de inspección fotográfica #{$measurement->point_number} guardado exitosamente.",
            'id' => (string) $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'area' => $measurement->area,
            'observation' => $measurement->observation,
            'description' => $measurement->description,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar un punto de inspección fotográfica desde la app móvil.
     */
    public function destroyPhotographicInspectionMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->photographicInspections()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->photographicInspections()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Punto de inspección fotográfica eliminado correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Listar puntos de medición de partículas ocupacionales.
     */
    public function getParticlesMeasurements($moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurements = $module->particulasMeasurements()->with('staff')->get()->map(function ($item) {
            $imagesList = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($imagesList) && !empty($item->image_urls)) {
                $imagesList = is_array($item->image_urls) ? $item->image_urls : (json_decode($item->image_urls, true) ?: []);
            }
            $imagesUrls = array_values(array_filter(array_map(function ($p) {
                if (!$p) return null;
                if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
                return asset($p);
            }, $imagesList)));

            return [
                'id' => (string) $item->id,
                'remote_id' => (string) $item->id,
                'local_uuid' => $item->local_uuid,
                'point_number' => $item->point_number,
                'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measured_at' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                'measurement_time' => $item->measurement_time,
                'area' => $item->area,
                'workstation' => $item->workstation ?: $item->punto_medicion,
                'punto_medicion' => $item->punto_medicion ?: $item->workstation,
                'puesto_trabajo' => $item->punto_medicion ?: $item->workstation,
                'temperatura' => $item->temperatura !== null ? (float)$item->temperatura : null,
                'temperatura_c' => $item->temperatura !== null ? (float)$item->temperatura : null,
                'hr_percent' => $item->hr_percent !== null ? (float)$item->hr_percent : null,
                'pm10_values' => is_array($item->pm10_values) ? $item->pm10_values : json_decode($item->pm10_values, true),
                'pm10_prom' => $item->pm10_prom !== null ? (float)$item->pm10_prom : null,
                'pm25_values' => is_array($item->pm25_values) ? $item->pm25_values : json_decode($item->pm25_values, true),
                'pm25_prom' => $item->pm25_prom !== null ? (float)$item->pm25_prom : null,
                'pts_values' => is_array($item->pts_values) ? $item->pts_values : json_decode($item->pts_values, true),
                'pts_prom' => $item->pts_prom !== null ? (float)$item->pts_prom : null,
                'location' => $item->location,
                'latitude' => $item->latitude !== null ? (float)$item->latitude : null,
                'longitude' => $item->longitude !== null ? (float)$item->longitude : null,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting !== null ? (float)$item->utm_easting : null,
                'utm_northing' => $item->utm_northing !== null ? (float)$item->utm_northing : null,
                'image_urls' => $imagesUrls,
                'observations' => $item->observations,
                'observaciones' => $item->observations,
                'registered_by' => $item->registered_by ?: $item->created_by,
                'created_by' => $item->created_by ?: $item->registered_by,
                'staff_id' => $item->staff_id,
            ];
        });

        return response()->json([
            'success' => true,
            'measurements' => $measurements,
            'points_completed' => $measurements->count(),
        ]);
    }

    /**
     * Registrar o actualizar una medición de partículas ocupacionales desde la app móvil.
     */
    public function storeParticlesMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $localUuid = $request->input('local_uuid');
        $id = $request->input('id');

        $measurement = null;
        if (!empty($localUuid)) {
            $measurement = $module->particulasMeasurements()->where('local_uuid', $localUuid)->first();
        }
        if (!$measurement && !empty($id)) {
            $measurement = $module->particulasMeasurements()->find($id);
        }

        $date = $request->input('measurement_date') ?? $request->input('date') ?? $request->input('measured_at') ?? Carbon::now()->format('Y-m-d');
        if (str_contains($date, 'T')) {
            $date = explode('T', $date)[0];
        }

        $time = $request->input('measurement_time') ?? $request->input('time') ?? Carbon::now()->format('H:i');

        $area = $request->input('area') ?? 'Área de Trabajo';
        $puntoMedicion = $request->input('punto_medicion') ?? $request->input('puesto_trabajo') ?? $request->input('workstation') ?? 'Punto de Medición';
        
        $pointNumber = $request->input('point_number');
        if (empty($pointNumber)) {
            $count = $module->particulasMeasurements()->count();
            $pointNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        }

        $temp = $request->filled('temperatura') ? (float)$request->input('temperatura') : ($request->filled('temperatura_c') ? (float)$request->input('temperatura_c') : null);
        $hr = $request->filled('hr_percent') ? (float)$request->input('hr_percent') : null;

        // PM10 readings
        $rawPm10 = $request->input('pm10_values');
        $pm10Values = is_array($rawPm10) ? $rawPm10 : (json_decode($rawPm10, true) ?: []);
        if (is_string($pm10Values)) {
            $pm10Values = json_decode($pm10Values, true) ?: [];
        }
        $pm10Nums = array_values(array_filter(array_map(fn($v) => is_numeric($v) ? (float)$v : null, (array)$pm10Values), fn($v) => $v !== null));
        $pm10Prom = $request->filled('pm10_prom') ? (float)$request->input('pm10_prom') : (!empty($pm10Nums) ? array_sum($pm10Nums) / count($pm10Nums) : null);

        // PM2.5 readings
        $rawPm25 = $request->input('pm25_values');
        $pm25Values = is_array($rawPm25) ? $rawPm25 : (json_decode($rawPm25, true) ?: []);
        if (is_string($pm25Values)) {
            $pm25Values = json_decode($pm25Values, true) ?: [];
        }
        $pm25Nums = array_values(array_filter(array_map(fn($v) => is_numeric($v) ? (float)$v : null, (array)$pm25Values), fn($v) => $v !== null));
        $pm25Prom = $request->filled('pm25_prom') ? (float)$request->input('pm25_prom') : (!empty($pm25Nums) ? array_sum($pm25Nums) / count($pm25Nums) : null);

        // PTS readings (optional/legacy)
        $rawPts = $request->input('pts_values');
        $ptsValues = is_array($rawPts) ? $rawPts : (json_decode($rawPts, true) ?: []);
        if (is_string($ptsValues)) {
            $ptsValues = json_decode($ptsValues, true) ?: [];
        }
        $ptsNums = array_values(array_filter(array_map(fn($v) => is_numeric($v) ? (float)$v : null, (array)$ptsValues), fn($v) => $v !== null));
        $ptsProm = $request->filled('pts_prom') ? (float)$request->input('pts_prom') : (!empty($ptsNums) ? array_sum($ptsNums) / count($ptsNums) : null);

        // Subida de imágenes
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $safeExt = in_array($ext, ['jpeg', 'jpg', 'png', 'webp']) ? $ext : 'jpg';
                    $fname = 'part_' . time() . '_' . uniqid() . '.' . $safeExt;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension());
                $safeExt = in_array($ext, ['jpeg', 'jpg', 'png', 'webp']) ? $ext : 'jpg';
                $fname = 'part_' . time() . '_' . uniqid() . '.' . $safeExt;
                $file->move($uploadDir, $fname);
                array_unshift($uploadedImages, 'uploads/measurements/' . $fname);
            }
        }

        // Si se enviaron URLs remotas o preexistentes
        if ($request->filled('image_urls')) {
            $rawUrls = $request->input('image_urls');
            $decodedUrls = is_array($rawUrls) ? $rawUrls : json_decode($rawUrls, true);
            if (is_array($decodedUrls)) {
                foreach ($decodedUrls as $u) {
                    if ($u && !in_array($u, $uploadedImages)) {
                        $uploadedImages[] = $u;
                    }
                }
            }
        }

        $lat = $request->filled('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->filled('longitude') ? (float)$request->input('longitude') : null;
        $utmZone = $request->input('utm_zone');
        $utmE = $request->filled('utm_easting') ? (float)$request->input('utm_easting') : null;
        $utmN = $request->filled('utm_northing') ? (float)$request->input('utm_northing') : null;

        $locInput = $request->input('location');
        if (is_string($locInput) && (str_starts_with($locInput, '{') || str_starts_with($locInput, '['))) {
            $locDecoded = json_decode($locInput, true);
            if (is_array($locDecoded)) {
                if ($utmZone === null && !empty($locDecoded['utm_zone'])) $utmZone = $locDecoded['utm_zone'];
                if ($utmE === null && !empty($locDecoded['easting'])) $utmE = (float)$locDecoded['easting'];
                if ($utmN === null && !empty($locDecoded['northing'])) $utmN = (float)$locDecoded['northing'];
                if ($lat === null && !empty($locDecoded['latitude'])) $lat = (float)$locDecoded['latitude'];
                if ($lng === null && !empty($locDecoded['longitude'])) $lng = (float)$locDecoded['longitude'];
            }
        }

        $staffId = $request->input('staff_id');
        $registeredBy = $request->input('registered_by') ?? $request->input('created_by');
        if (!empty($staffId)) {
            $st = Staff::find($staffId);
            if ($st) $registeredBy = $st->full_name ?: $st->name;
        }

        $data = [
            'point_number' => $pointNumber,
            'measurement_date' => $date,
            'measurement_time' => $time,
            'area' => $area,
            'workstation' => $puntoMedicion,
            'punto_medicion' => $puntoMedicion,
            'temperatura' => $temp,
            'hr_percent' => $hr,
            'pm10_values' => !empty($pm10Nums) ? $pm10Nums : null,
            'pm10_prom' => $pm10Prom,
            'pm25_values' => !empty($pm25Nums) ? $pm25Nums : null,
            'pm25_prom' => $pm25Prom,
            'pts_values' => !empty($ptsNums) ? $ptsNums : null,
            'pts_prom' => $ptsProm,
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'location' => $request->input('location'),
            'observations' => $request->input('observations') ?? $request->input('observaciones'),
            'registered_by' => $registeredBy,
            'created_by' => $registeredBy,
            'staff_id' => $staffId,
            'local_uuid' => $localUuid ?? ($measurement ? $measurement->local_uuid : Str::uuid()->toString()),
        ];

        if (!empty($uploadedImages)) {
            $data['images'] = $uploadedImages;
            $data['image_urls'] = $uploadedImages;
            $data['image_path'] = $uploadedImages[0];
        }

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->particulasMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->particulasMeasurements()->count();
        $module->save();

        $finalImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $finalUrls = array_values(array_filter(array_map(function ($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $finalImages)));

        return response()->json([
            'success' => true,
            'message' => "Punto de medición de partículas #{$measurement->point_number} guardado exitosamente.",
            'id' => (string) $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'area' => $measurement->area,
            'punto_medicion' => $measurement->punto_medicion,
            'local_uuid' => $measurement->local_uuid,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de partículas ocupacionales desde la app móvil.
     */
    public function destroyParticlesMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->particulasMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->particulasMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Punto de medición de partículas eliminado correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Obtener mediciones de partículas ambientales (calidad de aire) para la app móvil.
     */
    public function getAmbientParticlesMeasurements($moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->particulasAmbientalesMeasurements()
            ->with('staff')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
                if (empty($rawImages) && !empty($item->image_urls)) {
                    $rawImages = is_array($item->image_urls) ? $item->image_urls : (json_decode($item->image_urls, true) ?: []);
                }
                if (empty($rawImages) && !empty($item->image_path)) {
                    $rawImages = [$item->image_path];
                }
                $imagesUrls = array_values(array_filter(array_map(function ($p) {
                    if (!$p) return null;
                    if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
                    return asset($p);
                }, $rawImages)));

                return [
                    'id' => (string) $item->id,
                    'remote_id' => (string) $item->id,
                    'module_id' => (string) $item->module_id,
                    'local_uuid' => $item->local_uuid,
                    'point_number' => $item->point_number,
                    'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                    'measurement_time' => $item->measurement_time,
                    'area' => $item->area,
                    'punto_medicion' => $item->punto_medicion ?: $item->workstation,
                    'workstation' => $item->punto_medicion ?: $item->workstation,
                    'fecha_inicio' => $item->fecha_inicio ? $item->fecha_inicio->format('Y-m-d') : null,
                    'hora_inicio' => $item->hora_inicio,
                    'fecha_fin' => $item->fecha_fin ? $item->fecha_fin->format('Y-m-d') : null,
                    'hora_fin' => $item->hora_fin,
                    'diferencia_horas' => $item->diferencia_horas !== null ? (float)$item->diferencia_horas : null,
                    'temp_max' => $item->temp_max !== null ? (float)$item->temp_max : null,
                    'temp_min' => $item->temp_min !== null ? (float)$item->temp_min : null,
                    'temperatura' => $item->temperatura !== null ? (float)$item->temperatura : null,
                    'presion_atm' => $item->presion_atm !== null ? (float)$item->presion_atm : null,
                    'vel_viento' => $item->vel_viento !== null ? (float)$item->vel_viento : null,
                    'dir_viento' => $item->dir_viento,
                    'hr_percent' => $item->hr_percent !== null ? (float)$item->hr_percent : null,
                    'pm10_filtro_inicial' => $item->pm10_filtro_inicial !== null ? (float)$item->pm10_filtro_inicial : null,
                    'pm10_filtro_final' => $item->pm10_filtro_final !== null ? (float)$item->pm10_filtro_final : null,
                    'pm10_prom' => $item->pm10_prom !== null ? (float)$item->pm10_prom : null,
                    'pst_filtro_inicial' => $item->pst_filtro_inicial !== null ? (float)$item->pst_filtro_inicial : null,
                    'pst_filtro_final' => $item->pst_filtro_final !== null ? (float)$item->pst_filtro_final : null,
                    'pst_prom' => $item->pst_prom !== null ? (float)$item->pst_prom : null,
                    'pm25_filtro_inicial' => $item->pm25_filtro_inicial !== null ? (float)$item->pm25_filtro_inicial : null,
                    'pm25_filtro_final' => $item->pm25_filtro_final !== null ? (float)$item->pm25_filtro_final : null,
                    'pm25_prom' => $item->pm25_prom !== null ? (float)$item->pm25_prom : null,
                    'caudal' => $item->caudal !== null ? (float)$item->caudal : null,
                    'location' => $item->location,
                    'latitude' => $item->latitude !== null ? (float)$item->latitude : null,
                    'longitude' => $item->longitude !== null ? (float)$item->longitude : null,
                    'utm_zone' => $item->utm_zone,
                    'utm_easting' => $item->utm_easting !== null ? (float)$item->utm_easting : null,
                    'utm_northing' => $item->utm_northing !== null ? (float)$item->utm_northing : null,
                    'image_urls' => $imagesUrls,
                    'observations' => $item->observations,
                    'observaciones' => $item->observations,
                    'registered_by' => $item->registered_by ?: $item->created_by,
                    'created_by' => $item->created_by ?: $item->registered_by,
                    'staff_id' => $item->staff_id,
                ];
            });

        return response()->json([
            'success' => true,
            'measurements' => $measurements,
            'points_completed' => $measurements->count(),
        ]);
    }

    /**
     * Registrar o actualizar una medición de partículas ambientales desde la app móvil.
     */
    public function storeAmbientParticlesMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $localUuid = $request->input('local_uuid');
        $id = $request->input('id');

        $measurement = null;
        if (!empty($localUuid)) {
            $measurement = $module->particulasAmbientalesMeasurements()->where('local_uuid', $localUuid)->first();
        }
        if (!$measurement && !empty($id)) {
            $measurement = $module->particulasAmbientalesMeasurements()->find($id);
        }

        $date = $request->input('measurement_date') ?? $request->input('date') ?? $request->input('fecha_inicio') ?? $request->input('measured_at') ?? Carbon::now()->format('Y-m-d');
        if (str_contains($date, 'T')) {
            $date = explode('T', $date)[0];
        }

        $time = $request->input('measurement_time') ?? $request->input('time') ?? $request->input('hora_inicio') ?? Carbon::now()->format('H:i');

        $area = $request->input('area') ?? 'Área Ambiental';
        $puntoMedicion = $request->input('punto_medicion') ?? $request->input('puesto_trabajo') ?? $request->input('workstation') ?? 'Estación de Muestreo';
        
        $pointNumber = $request->input('point_number');
        if (empty($pointNumber)) {
            $count = $module->particulasAmbientalesMeasurements()->count();
            $pointNumber = 'PA-' . ($count + 1);
        } elseif (!str_starts_with(strtoupper($pointNumber), 'PA-')) {
            $pointNumber = 'PA-' . ltrim($pointNumber, '0');
        }

        // Fechas y Horas
        $fechaInicio = $request->input('fecha_inicio') ?? $date;
        if ($fechaInicio && str_contains($fechaInicio, 'T')) $fechaInicio = explode('T', $fechaInicio)[0];
        $horaInicio = $request->input('hora_inicio') ?? $time;
        $fechaFin = $request->input('fecha_fin') ?? $date;
        if ($fechaFin && str_contains($fechaFin, 'T')) $fechaFin = explode('T', $fechaFin)[0];
        $horaFin = $request->input('hora_fin') ?? $time;
        $difHoras = $request->filled('diferencia_horas') ? (float)$request->input('diferencia_horas') : null;

        // Si no vino diferencia de horas, intentar calcularla
        if ($difHoras === null && !empty($fechaInicio) && !empty($horaInicio) && !empty($fechaFin) && !empty($horaFin)) {
            try {
                $startDt = Carbon::parse("$fechaInicio $horaInicio");
                $endDt = Carbon::parse("$fechaFin $horaFin");
                $difHoras = round($endDt->diffInMinutes($startDt) / 60.0, 2);
            } catch (\Throwable $e) {}
        }

        // Meteorología
        $tempMax = $request->filled('temp_max') ? (float)$request->input('temp_max') : null;
        $tempMin = $request->filled('temp_min') ? (float)$request->input('temp_min') : null;
        $temp = $request->filled('temperatura') ? (float)$request->input('temperatura') : ($tempMax && $tempMin ? ($tempMax + $tempMin) / 2 : null);
        $presionAtm = $request->filled('presion_atm') ? (float)$request->input('presion_atm') : null;
        $velViento = $request->filled('vel_viento') ? (float)$request->input('vel_viento') : null;
        $dirViento = $request->input('dir_viento');
        $hr = $request->filled('hr_percent') ? (float)$request->input('hr_percent') : null;

        // PM10 Gravimétrico
        $pm10Ini = $request->filled('pm10_filtro_inicial') ? (float)$request->input('pm10_filtro_inicial') : null;
        $pm10Fin = $request->filled('pm10_filtro_final') ? (float)$request->input('pm10_filtro_final') : null;
        $pm10Prom = $request->filled('pm10_prom') ? (float)$request->input('pm10_prom') : null;

        // PST Gravimétrico
        $pstIni = $request->filled('pst_filtro_inicial') ? (float)$request->input('pst_filtro_inicial') : null;
        $pstFin = $request->filled('pst_filtro_final') ? (float)$request->input('pst_filtro_final') : null;
        $pstProm = $request->filled('pst_prom') ? (float)$request->input('pst_prom') : null;

        // PM2.5 Gravimétrico
        $pm25Ini = $request->filled('pm25_filtro_inicial') ? (float)$request->input('pm25_filtro_inicial') : null;
        $pm25Fin = $request->filled('pm25_filtro_final') ? (float)$request->input('pm25_filtro_final') : null;
        $pm25Prom = $request->filled('pm25_prom') ? (float)$request->input('pm25_prom') : null;

        $caudal = $request->filled('caudal') ? (float)$request->input('caudal') : null;

        // Cálculo de concentración gravimétrica si faltase
        if ($caudal !== null && $caudal > 0 && $difHoras !== null && $difHoras > 0) {
            $volumenM3 = ($caudal / 1000.0) * ($difHoras * 60.0);
            if ($volumenM3 > 0) {
                if ($pm10Prom === null && $pm10Ini !== null && $pm10Fin !== null && $pm10Fin >= $pm10Ini) {
                    $deltaGramos = $pm10Fin - $pm10Ini;
                    $pm10Prom = round(($deltaGramos * 1000000.0) / $volumenM3, 3);
                }
                if ($pstProm === null && $pstIni !== null && $pstFin !== null && $pstFin >= $pstIni) {
                    $deltaGramos = $pstFin - $pstIni;
                    $pstProm = round(($deltaGramos * 1000000.0) / $volumenM3, 3);
                }
                if ($pm25Prom === null && $pm25Ini !== null && $pm25Fin !== null && $pm25Fin >= $pm25Ini) {
                    $deltaGramos = $pm25Fin - $pm25Ini;
                    $pm25Prom = round(($deltaGramos * 1000000.0) / $volumenM3, 3);
                }
            }
        }

        // Subida de imágenes
        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $safeExt = in_array($ext, ['jpeg', 'jpg', 'png', 'webp']) ? $ext : 'jpg';
                    $fname = 'part_amb_' . time() . '_' . uniqid() . '.' . $safeExt;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $safeExt = in_array($ext, ['jpeg', 'jpg', 'png', 'webp']) ? $ext : 'jpg';
                    $fname = 'part_amb_' . time() . '_' . uniqid() . '.' . $safeExt;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $ext = strtolower($file->getClientOriginalExtension());
                $safeExt = in_array($ext, ['jpeg', 'jpg', 'png', 'webp']) ? $ext : 'jpg';
                $fname = 'part_amb_' . time() . '_' . uniqid() . '.' . $safeExt;
                $file->move($uploadDir, $fname);
                array_unshift($uploadedImages, 'uploads/measurements/' . $fname);
            }
        }

        if ($request->filled('image_urls')) {
            $rawUrls = $request->input('image_urls');
            $decodedUrls = is_array($rawUrls) ? $rawUrls : json_decode($rawUrls, true);
            if (is_array($decodedUrls)) {
                foreach ($decodedUrls as $u) {
                    if ($u && !in_array($u, $uploadedImages)) {
                        $uploadedImages[] = $u;
                    }
                }
            }
        }

        $lat = $request->filled('latitude') ? (float)$request->input('latitude') : null;
        $lng = $request->filled('longitude') ? (float)$request->input('longitude') : null;
        $utmZone = $request->input('utm_zone');
        $utmE = $request->filled('utm_easting') ? (float)$request->input('utm_easting') : null;
        $utmN = $request->filled('utm_northing') ? (float)$request->input('utm_northing') : null;

        $locInput = $request->input('location');
        if (is_string($locInput) && (str_starts_with($locInput, '{') || str_starts_with($locInput, '['))) {
            $locDecoded = json_decode($locInput, true);
            if (is_array($locDecoded)) {
                if ($utmZone === null && !empty($locDecoded['utm_zone'])) $utmZone = $locDecoded['utm_zone'];
                if ($utmE === null && !empty($locDecoded['easting'])) $utmE = (float)$locDecoded['easting'];
                if ($utmN === null && !empty($locDecoded['northing'])) $utmN = (float)$locDecoded['northing'];
                if ($lat === null && !empty($locDecoded['latitude'])) $lat = (float)$locDecoded['latitude'];
                if ($lng === null && !empty($locDecoded['longitude'])) $lng = (float)$locDecoded['longitude'];
            }
        }

        $staffId = $request->input('staff_id');
        $registeredBy = $request->input('registered_by') ?? $request->input('created_by');
        if (!empty($staffId)) {
            $st = Staff::find($staffId);
            if ($st) $registeredBy = $st->full_name ?: $st->name;
        }

        $data = [
            'point_number' => $pointNumber,
            'measurement_date' => $date,
            'measurement_time' => $time,
            'area' => $area,
            'workstation' => $puntoMedicion,
            'punto_medicion' => $puntoMedicion,
            'fecha_inicio' => $fechaInicio,
            'hora_inicio' => $horaInicio,
            'fecha_fin' => $fechaFin,
            'hora_fin' => $horaFin,
            'diferencia_horas' => $difHoras,
            'temp_max' => $tempMax,
            'temp_min' => $tempMin,
            'temperatura' => $temp,
            'presion_atm' => $presionAtm,
            'vel_viento' => $velViento,
            'dir_viento' => $dirViento,
            'hr_percent' => $hr,
            'pm10_filtro_inicial' => $pm10Ini,
            'pm10_filtro_final' => $pm10Fin,
            'pm10_prom' => $pm10Prom,
            'pst_filtro_inicial' => $pstIni,
            'pst_filtro_final' => $pstFin,
            'pst_prom' => $pstProm,
            'pm25_filtro_inicial' => $pm25Ini,
            'pm25_filtro_final' => $pm25Fin,
            'pm25_prom' => $pm25Prom,
            'caudal' => $caudal,
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'location' => $request->input('location'),
            'observations' => $request->input('observations') ?? $request->input('observaciones'),
            'registered_by' => $registeredBy,
            'created_by' => $registeredBy,
            'staff_id' => $staffId,
            'local_uuid' => $localUuid ?? ($measurement ? $measurement->local_uuid : Str::uuid()->toString()),
        ];

        if (!empty($uploadedImages)) {
            $data['images'] = $uploadedImages;
            $data['image_urls'] = $uploadedImages;
            $data['image_path'] = $uploadedImages[0];
        }

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->particulasAmbientalesMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->particulasAmbientalesMeasurements()->count();
        $module->save();

        $finalImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $finalUrls = array_values(array_filter(array_map(function ($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $finalImages)));

        return response()->json([
            'success' => true,
            'message' => "Punto de muestreo ambiental #{$measurement->point_number} guardado exitosamente.",
            'id' => (string) $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'area' => $measurement->area,
            'punto_medicion' => $measurement->punto_medicion,
            'local_uuid' => $measurement->local_uuid,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de partículas ambientales desde la app móvil.
     */
    public function destroyAmbientParticlesMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->particulasAmbientalesMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->particulasAmbientalesMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Punto de medición ambiental eliminado correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Obtener mediciones de vibración de un módulo para la app móvil.
     */
    public function getVibracionMeasurements($moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->vibracionMeasurements()
            ->with('staff')
            ->get()
            ->map(function ($item) {
                $images = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
                if (empty($images) && !empty($item->image_urls)) {
                    $images = is_array($item->image_urls) ? $item->image_urls : (json_decode($item->image_urls, true) ?: []);
                }
                $imageUrls = array_values(array_filter(array_map(function ($p) {
                    return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
                }, $images)));

                $tipo = strtolower($item->tipo ?? 'cuerpo_entero');
                $isCE = ($tipo === 'cuerpo_entero' || str_contains($tipo, 'cuerpo'));

                return [
                    'id' => $item->id,
                    'remote_id' => (string) $item->id,
                    'local_uuid' => $item->local_uuid,
                    'point_number' => $item->point_number,
                    'codigo' => $item->codigo ?? $item->point_number,
                    'punto_medicion' => $item->punto_medicion ?? $item->point_number,
                    'measurement_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : null,
                    'measurement_time' => $item->measurement_time,
                    'area' => $item->area,
                    'area_trabajo' => $item->area,
                    'puesto_trabajo' => $item->puesto_trabajo ?: $item->workstation,
                    'workstation' => $item->puesto_trabajo ?: $item->workstation,
                    'trabajador_evaluado' => $item->trabajador_evaluado,
                    'maquina_equipo' => $item->maquina_equipo,
                    'duracion_jornada_h' => (float) ($item->duracion_jornada_h ?? 8.0),
                    'tiempo_expos_h' => (float) ($item->tiempo_expos_h ?? 8.0),
                    'duracion_prueba_min' => (int) ($item->duracion_prueba_min ?? 15),
                    'tipo' => $isCE ? 'cuerpo_entero' : 'mano_brazo',
                    'ub_acelerometro' => $item->ub_acelerometro,
                    'mano_afectada' => $item->mano_afectada,
                    'aeqx_ce' => (float) ($item->aeqx_ce ?? 0),
                    'aeqy_ce' => (float) ($item->aeqy_ce ?? 0),
                    'aeqz_ce' => (float) ($item->aeqz_ce ?? 0),
                    'aeqx_mb' => (float) ($item->aeqx_mb ?? 0),
                    'aeqy_mb' => (float) ($item->aeqy_mb ?? 0),
                    'aeqz_mb' => (float) ($item->aeqz_mb ?? 0),
                    'atotal' => $item->aceleracion_total,
                    'a8' => $item->a8,
                    'nivel_accion' => $item->nivel_accion,
                    'limite_vle' => $item->limite_vle,
                    'estado_cumplimiento' => $item->estado_cumplimiento,
                    'observations' => $item->observations,
                    'image_urls' => $imageUrls,
                    'location' => $item->location,
                    'latitude' => $item->latitude !== null ? (float) $item->latitude : null,
                    'longitude' => $item->longitude !== null ? (float) $item->longitude : null,
                    'utm_zone' => $item->utm_zone,
                    'utm_easting' => $item->utm_easting !== null ? (float) $item->utm_easting : null,
                    'utm_northing' => $item->utm_northing !== null ? (float) $item->utm_northing : null,
                    'registered_by' => $item->staff ? $item->staff->name : $item->registered_by,
                    'staff_id' => $item->staff_id,
                ];
            });

        return response()->json([
            'success' => true,
            'module_id' => $module->id,
            'module_name' => $module->name,
            'points_total' => $module->points_total,
            'points_completed' => $module->vibracionMeasurements()->count(),
            'measurements' => $measurements,
        ]);
    }

    /**
     * Crear o sincronizar una medición de vibración desde la app móvil.
     */
    public function storeVibracionMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'id' => 'nullable',
            'remote_id' => 'nullable',
            'local_uuid' => 'nullable|string',
            'point_number' => 'nullable|string|max:50',
            'codigo' => 'nullable|string|max:50',
            'punto_medicion' => 'nullable|string|max:255',
            'measured_at' => 'nullable|string',
            'measurement_date' => 'nullable|string',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'area_trabajo' => 'nullable|string|max:255',
            'puesto_trabajo' => 'nullable|string|max:255',
            'workstation' => 'nullable|string|max:255',
            'trabajador_evaluado' => 'nullable|string|max:255',
            'maquina_equipo' => 'nullable|string|max:255',
            'duracion_jornada_h' => 'nullable|numeric',
            'tiempo_expos_h' => 'nullable|numeric',
            'duracion_prueba_min' => 'nullable|integer',
            'tipo' => 'nullable|string|max:50',
            'ub_acelerometro' => 'nullable|string|max:100',
            'mano_afectada' => 'nullable|string|max:50',
            'aeqx_ce' => 'nullable|numeric',
            'aeqy_ce' => 'nullable|numeric',
            'aeqz_ce' => 'nullable|numeric',
            'aeqx_mb' => 'nullable|numeric',
            'aeqy_mb' => 'nullable|numeric',
            'aeqz_mb' => 'nullable|numeric',
            'location' => 'nullable',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'registered_by' => 'nullable|string|max:255',
            'created_by' => 'nullable|string|max:255',
            'staff_id' => 'nullable',
            'image_urls' => 'nullable',
            'images.*' => 'nullable|image|max:15360',
            'photos.*' => 'nullable|image|max:15360',
        ]);

        $localUuid = $validated['local_uuid'] ?? null;
        $id = $validated['id'] ?? ($validated['remote_id'] ?? null);

        $measurement = null;
        if (!empty($localUuid)) {
            $measurement = $module->vibracionMeasurements()->where('local_uuid', $localUuid)->first();
        }
        if (!$measurement && !empty($id)) {
            $measurement = $module->vibracionMeasurements()->find($id);
        }

        // Manejo de Fotos (soporta tanto 'images' como 'photos')
        $uploadedImages = [];
        $filesToProcess = [];
        if ($request->hasFile('images')) {
            $imgs = $request->file('images');
            $filesToProcess = is_array($imgs) ? $imgs : [$imgs];
        } elseif ($request->hasFile('photos')) {
            $imgs = $request->file('photos');
            $filesToProcess = is_array($imgs) ? $imgs : [$imgs];
        }

        if (!empty($filesToProcess)) {
            $destPath = public_path('uploads/vibraciones');
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0755, true);
            }
            foreach ($filesToProcess as $file) {
                if ($file && $file->isValid()) {
                    $filename = 'vib_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $uploadedImages[] = 'uploads/vibraciones/' . $filename;
                }
            }
        }

        // Determinar fecha y hora
        $measDate = $validated['measurement_date'] ?? null;
        $measTime = $validated['measurement_time'] ?? null;
        if (empty($measDate) && !empty($validated['measured_at'])) {
            try {
                $dt = Carbon::parse($validated['measured_at']);
                $measDate = $dt->toDateString();
                if (empty($measTime)) {
                    $measTime = $dt->format('H:i');
                }
            } catch (\Exception $e) {
                $measDate = now()->toDateString();
            }
        }
        if (empty($measDate)) $measDate = now()->toDateString();
        if (empty($measTime)) $measTime = now()->format('H:i');

        $existingUrls = [];
        if (!empty($validated['image_urls'])) {
            $raw = $validated['image_urls'];
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $existingUrls = $decoded;
                } else {
                    $existingUrls = [$raw];
                }
            } elseif (is_array($raw)) {
                $existingUrls = $raw;
            }
        }

        $finalUrls = array_values(array_unique(array_merge($existingUrls, $uploadedImages)));

        $pointNum = $validated['codigo'] ?? ($validated['point_number'] ?? null);
        if (empty($pointNum)) {
            $count = $module->vibracionMeasurements()->count();
            $pointNum = 'VIB-' . ($count + 1);
        }

        $area = $validated['area_trabajo'] ?? ($validated['area'] ?? 'General');
        $puesto = $validated['puesto_trabajo'] ?? ($validated['workstation'] ?? 'Operador');

        $data = [
            'module_id' => $module->id,
            'point_number' => $pointNum,
            'codigo' => $pointNum,
            'punto_medicion' => $pointNum,
            'measurement_date' => $measDate,
            'measurement_time' => $measTime,
            'area' => $area,
            'puesto_trabajo' => $puesto,
            'workstation' => $puesto,
            'trabajador_evaluado' => $validated['trabajador_evaluado'] ?? null,
            'maquina_equipo' => $validated['maquina_equipo'] ?? null,
            'duracion_jornada_h' => $validated['duracion_jornada_h'] ?? 8.0,
            'tiempo_expos_h' => $validated['tiempo_expos_h'] ?? 8.0,
            'duracion_prueba_min' => $validated['duracion_prueba_min'] ?? 15,
            'tipo' => $validated['tipo'] ?? 'cuerpo_entero',
            'ub_acelerometro' => $validated['ub_acelerometro'] ?? 'base_asiento',
            'mano_afectada' => $validated['mano_afectada'] ?? 'derecha',
            'aeqx_ce' => $validated['aeqx_ce'] ?? 0.0,
            'aeqy_ce' => $validated['aeqy_ce'] ?? 0.0,
            'aeqz_ce' => $validated['aeqz_ce'] ?? 0.0,
            'aeqx_mb' => $validated['aeqx_mb'] ?? 0.0,
            'aeqy_mb' => $validated['aeqy_mb'] ?? 0.0,
            'aeqz_mb' => $validated['aeqz_mb'] ?? 0.0,
            'location' => is_array($validated['location'] ?? null) ? json_encode($validated['location']) : ($validated['location'] ?? null),
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'utm_zone' => $validated['utm_zone'] ?? '19K',
            'utm_easting' => $validated['utm_easting'] ?? null,
            'utm_northing' => $validated['utm_northing'] ?? null,
            'image_path' => $finalUrls[0] ?? null,
            'images' => $finalUrls,
            'image_urls' => $finalUrls,
            'observations' => $validated['observations'] ?? ($validated['observaciones'] ?? null),
            'registered_by' => $validated['registered_by'] ?? ($validated['created_by'] ?? 'App Móvil'),
            'created_by' => $validated['created_by'] ?? 'App Móvil',
            'staff_id' => $validated['staff_id'] ?? null,
            'local_uuid' => $localUuid,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->vibracionMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->vibracionMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Medición de vibración sincronizada correctamente.',
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'codigo' => $measurement->codigo,
            'local_uuid' => $measurement->local_uuid,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de vibración desde la app móvil.
     */
    public function destroyVibracionMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->vibracionMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->vibracionMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Punto de medición de vibración eliminado correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }

    /**
     * Obtener mediciones de Contaminantes Químicos para la app móvil.
     */
    public function getContaminantesQuimicosMeasurements($moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $measurements = $module->contaminantesQuimicosMeasurements()
            ->with('staff')
            ->orderBy('id', 'asc')
            ->get()
            ->map(function ($m) {
                return [
                    'id' => $m->id,
                    'remote_id' => (string) $m->id,
                    'module_id' => $m->module_id,
                    'point_number' => $m->point_number,
                    'codigo' => $m->codigo ?? $m->point_number,
                    'punto_medicion' => $m->punto_medicion,
                    'measurement_date' => $m->measurement_date ? $m->measurement_date->toDateString() : null,
                    'measurement_time' => $m->measurement_time,
                    'measured_at' => $m->measurement_date ? ($m->measurement_date->toDateString() . ' ' . ($m->measurement_time ?? '08:00:00')) : null,
                    'area' => $m->area,
                    'area_trabajo' => $m->area,
                    'trabajador_nombre' => $m->trabajador_nombre,
                    'trabajador_evaluado' => $m->trabajador_nombre,
                    'masa_inicial_filtro_mg' => $m->masa_inicial_filtro_mg !== null ? (float) $m->masa_inicial_filtro_mg : null,
                    'masa_final_filtro_mg' => $m->masa_final_filtro_mg !== null ? (float) $m->masa_final_filtro_mg : null,
                    'masa_neta_filtro_mg' => $m->masa_neta_filtro,
                    'hora_inicio' => $m->hora_inicio,
                    'hora_final' => $m->hora_final,
                    't_inicial_c' => $m->t_inicial_c !== null ? (float) $m->t_inicial_c : null,
                    't_final_c' => $m->t_final_c !== null ? (float) $m->t_final_c : null,
                    'presion_hpa' => $m->presion_hpa !== null ? (float) $m->presion_hpa : null,
                    'q_inicial_lmin' => $m->q_inicial_lmin !== null ? (float) $m->q_inicial_lmin : null,
                    'q_final_lmin' => $m->q_final_lmin !== null ? (float) $m->q_final_lmin : null,
                    'caudal_promedio_lmin' => $m->caudal_promedio,
                    'location' => $m->location ? (is_array($m->location) ? $m->location : json_decode($m->location, true)) : null,
                    'latitude' => $m->latitude !== null ? (float) $m->latitude : null,
                    'longitude' => $m->longitude !== null ? (float) $m->longitude : null,
                    'utm_zone' => $m->utm_zone ?? '19K',
                    'utm_easting' => $m->utm_easting !== null ? (float) $m->utm_easting : null,
                    'utm_northing' => $m->utm_northing !== null ? (float) $m->utm_northing : null,
                    'image_path' => $m->image_path ? asset($m->image_path) : null,
                    'image_urls' => collect($m->image_urls ?: [])->map(fn($url) => str_starts_with($url, 'http') ? $url : asset($url))->toArray(),
                    'observations' => $m->observations,
                    'observaciones' => $m->observations,
                    'registered_by' => $m->registered_by,
                    'created_by' => $m->created_by,
                    'staff_id' => $m->staff_id,
                    'staff_name' => $m->staff ? $m->staff->name : null,
                    'local_uuid' => $m->local_uuid,
                    'created_at' => $m->created_at ? $m->created_at->toIso8601String() : null,
                    'updated_at' => $m->updated_at ? $m->updated_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'module_id' => $module->id,
            'module_name' => $module->name,
            'points_total' => $module->points_total,
            'points_completed' => $module->contaminantesQuimicosMeasurements()->count(),
            'measurements' => $measurements,
        ]);
    }

    /**
     * Guardar o actualizar una medición de Contaminantes Químicos desde la app móvil.
     */
    public function storeContaminantesQuimicosMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'id' => 'nullable',
            'remote_id' => 'nullable',
            'local_uuid' => 'nullable|string',
            'point_number' => 'nullable|string|max:50',
            'codigo' => 'nullable|string|max:50',
            'punto_medicion' => 'nullable|string|max:255',
            'measured_at' => 'nullable|string',
            'measurement_date' => 'nullable|string',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'area_trabajo' => 'nullable|string|max:255',
            'trabajador_nombre' => 'nullable|string|max:255',
            'trabajador_evaluado' => 'nullable|string|max:255',
            'masa_inicial_filtro_mg' => 'nullable|numeric',
            'masa_inicial_mg' => 'nullable|numeric',
            'masa_final_filtro_mg' => 'nullable|numeric',
            'masa_final_mg' => 'nullable|numeric',
            'hora_inicio' => 'nullable|string|max:20',
            'hora_final' => 'nullable|string|max:20',
            't_inicial_c' => 'nullable|numeric',
            't_final_c' => 'nullable|numeric',
            'presion_hpa' => 'nullable|numeric',
            'q_inicial_lmin' => 'nullable|numeric',
            'q_final_lmin' => 'nullable|numeric',
            'location' => 'nullable',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'registered_by' => 'nullable|string|max:255',
            'created_by' => 'nullable|string|max:255',
            'staff_id' => 'nullable',
            'image_urls' => 'nullable',
            'images.*' => 'nullable|image|max:15360',
            'photos.*' => 'nullable|image|max:15360',
        ]);

        $localUuid = $validated['local_uuid'] ?? null;
        $id = $validated['id'] ?? ($validated['remote_id'] ?? null);

        $measurement = null;
        if (!empty($localUuid)) {
            $measurement = $module->contaminantesQuimicosMeasurements()->where('local_uuid', $localUuid)->first();
        }
        if (!$measurement && !empty($id)) {
            $measurement = $module->contaminantesQuimicosMeasurements()->find($id);
        }

        // Manejo de Fotos (soporta tanto 'images' como 'photos')
        $uploadedImages = [];
        $filesToProcess = [];
        if ($request->hasFile('images')) {
            $imgs = $request->file('images');
            $filesToProcess = is_array($imgs) ? $imgs : [$imgs];
        } elseif ($request->hasFile('photos')) {
            $imgs = $request->file('photos');
            $filesToProcess = is_array($imgs) ? $imgs : [$imgs];
        }

        if (!empty($filesToProcess)) {
            $destPath = public_path('uploads/quimicos');
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0755, true);
            }
            foreach ($filesToProcess as $file) {
                if ($file && $file->isValid()) {
                    $filename = 'cq_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $uploadedImages[] = 'uploads/quimicos/' . $filename;
                }
            }
        }

        // Determinar fecha y hora
        $measDate = $validated['measurement_date'] ?? null;
        $measTime = $validated['measurement_time'] ?? null;
        if (empty($measDate) && !empty($validated['measured_at'])) {
            try {
                $dt = Carbon::parse($validated['measured_at']);
                $measDate = $dt->toDateString();
                if (empty($measTime)) {
                    $measTime = $dt->format('H:i');
                }
            } catch (\Exception $e) {
                $measDate = now()->toDateString();
            }
        }
        if (empty($measDate)) $measDate = now()->toDateString();
        if (empty($measTime)) $measTime = now()->format('H:i');

        $existingUrls = [];
        if (!empty($validated['image_urls'])) {
            $raw = $validated['image_urls'];
            if (is_string($raw)) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $existingUrls = $decoded;
                } else {
                    $existingUrls = [$raw];
                }
            } elseif (is_array($raw)) {
                $existingUrls = $raw;
            }
        }

        $finalUrls = array_values(array_unique(array_merge($existingUrls, $uploadedImages)));

        $pointNum = $validated['codigo'] ?? ($validated['point_number'] ?? null);
        if (empty($pointNum)) {
            $count = $module->contaminantesQuimicosMeasurements()->count();
            $pointNum = 'CQ-' . ($count + 1);
        }

        $area = $validated['area_trabajo'] ?? ($validated['area'] ?? null);
        $trabajador = $validated['trabajador_nombre'] ?? ($validated['trabajador_evaluado'] ?? null);
        $masaInicial = $validated['masa_inicial_filtro_mg'] ?? ($validated['masa_inicial_mg'] ?? null);
        $masaFinal = $validated['masa_final_filtro_mg'] ?? ($validated['masa_final_mg'] ?? null);
        $observaciones = $validated['observaciones'] ?? ($validated['observations'] ?? null);

        $locationVal = $validated['location'] ?? null;
        if (is_array($locationVal)) {
            $locationVal = json_encode($locationVal);
        }

        $data = [
            'point_number' => $pointNum,
            'codigo' => $pointNum,
            'punto_medicion' => $validated['punto_medicion'] ?? null,
            'measurement_date' => $measDate,
            'measurement_time' => $measTime,
            'area' => $area,
            'trabajador_nombre' => $trabajador,
            'masa_inicial_filtro_mg' => $masaInicial !== null ? (float)$masaInicial : null,
            'masa_final_filtro_mg' => $masaFinal !== null ? (float)$masaFinal : null,
            'hora_inicio' => $validated['hora_inicio'] ?? null,
            'hora_final' => $validated['hora_final'] ?? null,
            't_inicial_c' => isset($validated['t_inicial_c']) ? (float)$validated['t_inicial_c'] : null,
            't_final_c' => isset($validated['t_final_c']) ? (float)$validated['t_final_c'] : null,
            'presion_hpa' => isset($validated['presion_hpa']) ? (float)$validated['presion_hpa'] : null,
            'q_inicial_lmin' => isset($validated['q_inicial_lmin']) ? (float)$validated['q_inicial_lmin'] : null,
            'q_final_lmin' => isset($validated['q_final_lmin']) ? (float)$validated['q_final_lmin'] : null,
            'location' => $locationVal,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'utm_zone' => $validated['utm_zone'] ?? '19K',
            'utm_easting' => $validated['utm_easting'] ?? null,
            'utm_northing' => $validated['utm_northing'] ?? null,
            'image_urls' => $finalUrls,
            'images' => $finalUrls,
            'image_path' => !empty($finalUrls) ? $finalUrls[0] : null,
            'observations' => $observaciones,
            'registered_by' => $validated['registered_by'] ?? ($validated['created_by'] ?? 'Técnico'),
            'created_by' => $validated['created_by'] ?? ($validated['registered_by'] ?? 'Técnico'),
            'staff_id' => $validated['staff_id'] ?? null,
            'local_uuid' => $localUuid,
        ];

        if ($measurement) {
            $measurement->update($data);
            $statusCode = 200;
        } else {
            $measurement = $module->contaminantesQuimicosMeasurements()->create($data);
            $statusCode = 201;
        }

        $module->points_completed = $module->contaminantesQuimicosMeasurements()->count();
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Medición de Contaminantes Químicos sincronizada correctamente.',
            'id' => $measurement->id,
            'remote_id' => (string) $measurement->id,
            'point_number' => $measurement->point_number,
            'codigo' => $measurement->codigo,
            'local_uuid' => $measurement->local_uuid,
            'image_urls' => $finalUrls,
            'points_completed' => $module->points_completed,
        ], $statusCode);
    }

    /**
     * Eliminar una medición de Contaminantes Químicos desde la app móvil.
     */
    public function destroyContaminantesQuimicosMeasurement($moduleId, $id)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->contaminantesQuimicosMeasurements()->find($id);

        if ($measurement) {
            $measurement->delete();
            $module->points_completed = $module->contaminantesQuimicosMeasurements()->count();
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Punto de medición de Contaminantes Químicos eliminado correctamente.',
            'points_completed' => $module->points_completed,
        ]);
    }
}






