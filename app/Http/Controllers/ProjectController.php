<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MeasurementModule;
use App\Models\Company;
use App\Models\Manager;
use App\Models\Staff;
use App\Models\Equipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $companies = Company::with('managers')->orderBy('name')->get();
        $managers = Manager::with('company')->orderBy('name')->get();

        $projects = Project::with(['company', 'manager', 'modules'])->orderBy('id', 'desc')->get()->map(function ($prj, $index) {
            $totalMods = $prj->modules->count();
            $completedMods = $prj->modules->where('status', 'Completado')->count();
            $ratio = $totalMods > 0 ? round(($completedMods / $totalMods) * 100) : 0;

            $startDateObj = $prj->start_date ? \Carbon\Carbon::parse($prj->start_date) : ($prj->created_at ? $prj->created_at : null);
            $startDateFormatted = $startDateObj ? $startDateObj->format('d/m/Y') : '-';
            $startDateRaw = $startDateObj ? $startDateObj->format('Y-m-d') : date('Y-m-d');

            // Extraer sigla de proyecto si el código tiene formato SIG-PRJ-MM-YY
            $codeParts = explode('-', $prj->code);
            $projSigla = (count($codeParts) >= 2) ? $codeParts[1] : 'PRJ';

            return [
                'id' => $prj->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $prj->name,
                'razon_social' => $prj->razon_social ?? ($prj->company ? ($prj->company->legal_name ?: $prj->company->name) : ''),
                'direccion' => $prj->direccion ?? '',
                'code' => $prj->code,
                'project_sigla' => strlen($projSigla) <= 3 ? $projSigla : 'PRJ',
                'description' => $prj->description ?? 'Sin descripción registrada.',
                'start_date_formatted' => $startDateFormatted,
                'start_date_raw' => $startDateRaw,
                'created_at_formatted' => $prj->created_at ? $prj->created_at->format('d/m/Y') : '-',
                'created_at_date' => $prj->created_at ? $prj->created_at->format('Y-m-d') : date('Y-m-d'),
                'client' => $prj->company ? $prj->company->name : 'General',
                'client_code' => $prj->company ? strtoupper($prj->company->code) : 'EMP',
                'client_id' => $prj->company_id,
                'client_initial' => $prj->company ? strtoupper(substr($prj->company->name, 0, 1)) : 'G',
                'client_theme' => $prj->company ? ($prj->company->theme ?? 'cyan') : 'cyan',
                'manager_id' => $prj->manager_id,
                'manager_name' => $prj->manager ? $prj->manager->name : 'No Asignado',
                'modules_completed_text' => "$completedMods de $totalMods Módulos",
                'modules_ratio_pct' => $ratio,
                'progress' => round($prj->compliance_pct),
                'progress_theme' => ($prj->compliance_pct >= 98) ? 'lime' : 'cyan',
                'status' => $prj->status,
                'status_type' => $prj->status_type ?? 'in_progress',
            ];
        })->toArray();

        return view('projects.index', compact('userName', 'userRole', 'projects', 'companies', 'managers'));
    }

    /**
     * Environmental & Industrial Measurement Modules Table
     */
    public function modules(Request $request)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';
        $selectedModule = $request->query('tipo', 'todos');
        $selectedProjectId = $request->query('proyecto', $request->query('project_id', 'todos'));

        $query = MeasurementModule::with(['project.company', 'fieldStaff', 'equipment']);
        if ($selectedModule !== 'todos') {
            $query->where('key', $selectedModule);
        }

        $activeProject = null;
        if ($selectedProjectId !== 'todos' && !empty($selectedProjectId)) {
            $query->where('project_id', $selectedProjectId);
            $activeProject = Project::with('company')->find($selectedProjectId);
        }

        // Preload staff for all modules to avoid N+1 queries if multiple staff
        $allStaffMap = Staff::all()->keyBy('id');

        $modulesData = $query->get()->map(function ($mod, $index) use ($allStaffMap) {
            // Resolve staff members (multiple or single)
            $staffMembers = [];
            if (!empty($mod->field_staff_ids) && is_array($mod->field_staff_ids)) {
                foreach ($mod->field_staff_ids as $sid) {
                    if (isset($allStaffMap[$sid])) {
                        $s = $allStaffMap[$sid];
                        $staffMembers[] = [
                            'id' => $s->id,
                            'name' => $s->name,
                            'email' => $s->email ?? '',
                            'initial' => strtoupper(substr($s->name, 0, 1)),
                            'theme' => $s->role_theme ?? 'cyan',
                            'role' => $s->position ?? 'Técnico de Campo',
                        ];
                    }
                }
            }
            if (empty($staffMembers) && $mod->fieldStaff) {
                $staffMembers[] = [
                    'id' => $mod->fieldStaff->id,
                    'name' => $mod->fieldStaff->name,
                    'email' => $mod->fieldStaff->email ?? '',
                    'initial' => strtoupper(substr($mod->fieldStaff->name, 0, 1)),
                    'theme' => $mod->fieldStaff->role_theme ?? 'cyan',
                    'role' => $mod->fieldStaff->position ?? 'Técnico de Campo',
                ];
            }

            // Primary staff info fallback
            $primaryStaff = $staffMembers[0] ?? null;

            // Resolve equipment
            $eq = $mod->equipment;
            $equipmentName = $eq ? $eq->name : ($mod->calibration_equipment ?: 'Sin equipo');
            $equipmentModel = $eq ? $eq->model : '';
            $equipmentSn = $eq ? $eq->serial_number : '';
            $equipmentImage = ($eq && $eq->image) ? asset($eq->image) : null;
            $equipmentId = $mod->equipment_id ?? ($eq ? $eq->id : null);

            $ptsTotal = $mod->points_total ?: 1;
            $ptsCompleted = $mod->points_completed ?: 0;
            $ptsRatio = $ptsTotal > 0 ? round(($ptsCompleted / $ptsTotal) * 100) : 0;

            return [
                'id' => $mod->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'project_id' => $mod->project_id,
                'project_name' => $mod->project ? $mod->project->name : 'Proyecto General',
                'project_code' => $mod->project ? $mod->project->code : '',
                'project_company' => ($mod->project && $mod->project->company) ? $mod->project->company->name : '',
                'module_key' => $mod->key,
                'module_name' => $mod->name,
                'description' => $mod->description ?? 'Sin descripción registrada.',
                'equipment' => $mod->calibration_equipment,
                'equipment_id' => $equipmentId,
                'equipment_name' => $equipmentName,
                'equipment_model' => $equipmentModel,
                'equipment_sn' => $equipmentSn,
                'equipment_image' => $equipmentImage,
                'field_staff_id' => $mod->field_staff_id,
                'field_staff_ids' => $mod->field_staff_ids ?? ($mod->field_staff_id ? [$mod->field_staff_id] : []),
                'staff_members' => $staffMembers,
                'staff_name' => $primaryStaff ? $primaryStaff['name'] : 'Por Asignar',
                'staff_initial' => $primaryStaff ? $primaryStaff['initial'] : 'A',
                'staff_theme' => $primaryStaff ? $primaryStaff['theme'] : 'cyan',
                'staff_role' => $primaryStaff ? $primaryStaff['role'] : 'Técnico de Campo',
                'points_total' => $ptsTotal,
                'points_completed' => $ptsCompleted,
                'points_ratio' => $ptsRatio,
                'points_fraction' => "{$ptsCompleted}/{$ptsTotal}",
                'points_text' => "{$ptsTotal} Puntos",
            ];
        })->toArray();

        // Si es Responsable, filtrar solo el personal que él creó (manager_id)
        $isManager = $currentUser && in_array(strtolower($currentUser->role ?? ''), ['responsable', 'responsable de planta', 'manager', 'cliente']);
        $manager = $currentUser ? Manager::where('email', $currentUser->email)->first() : null;

        if (($isManager || $manager) && $manager) {
            $staffList = Staff::where('manager_id', $manager->id)->orderBy('name')->get();
            if ($staffList->isEmpty()) {
                $staffList = Staff::orderBy('name')->get();
            }
            $projectsList = Project::where('manager_id', $manager->id)->orderBy('name')->get();
            if ($projectsList->isEmpty()) {
                $projectsList = Project::orderBy('name')->get();
            }
        } else {
            $staffList = Staff::orderBy('name')->get();
            $projectsList = Project::orderBy('name')->get();
        }

        $equipmentList = Equipment::orderBy('name')->get();

        return view('projects.modules', compact('userName', 'userRole', 'modulesData', 'selectedModule', 'staffList', 'equipmentList', 'projectsList', 'activeProject', 'selectedProjectId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'company_id' => 'required|exists:companies,id',
            'manager_id' => 'nullable|exists:managers,id',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'project_sigla' => 'nullable|string|max:3',
            'code' => 'nullable|string|max:100',
            'status' => 'nullable|string|max:50',
        ]);

        $status = $validated['status'] ?? 'Planificación';
        $statusType = match ($status) {
            'En Ejecución', 'En Progreso', 'Activo' => 'in_progress',
            'Completado', 'Finalizado' => 'done',
            'En Pausa', 'Detenido' => 'delayed',
            default => 'pending',
        };

        // Generar código automático si no se suministró: [SIGLA_EMPRESA]-[SIGLA_PROYECTO]-[MM]-[YY]
        $company = Company::find($validated['company_id']);
        $compSigla = $company ? strtoupper(substr($company->code, 0, 3)) : 'EMP';
        $projSigla = !empty($validated['project_sigla']) ? strtoupper(substr($validated['project_sigla'], 0, 3)) : 'PRJ';
        $startDate = $validated['start_date'];
        $mm = date('m', strtotime($startDate));
        $yy = date('y', strtotime($startDate));

        $autoCode = "{$compSigla}-{$projSigla}-{$mm}-{$yy}";
        $targetCode = !empty($validated['code']) ? strtoupper(trim($validated['code'])) : $autoCode;

        // Garantizar unicidad si ya existiera el código
        $finalCode = $targetCode;
        $counter = 1;
        while (Project::where('code', $finalCode)->exists()) {
            $finalCode = "{$targetCode}-" . str_pad($counter, 2, '0', STR_PAD_LEFT);
            $counter++;
        }

        $project = new Project();
        $project->name = $validated['name'];
        $project->razon_social = $validated['razon_social'] ?? null;
        $project->direccion = $validated['direccion'] ?? null;
        $project->code = $finalCode;
        $project->company_id = $validated['company_id'];
        $project->region_id = $request->input('region_id', null);
        $project->manager_id = $validated['manager_id'] ?? null;
        $project->description = $validated['description'] ?? null;
        $project->start_date = $startDate;
        $project->created_at = $startDate . ' ' . date('H:i:s');
        $project->status = $status;
        $project->status_type = $statusType;
        $project->compliance_pct = 0.00;
        $project->points_total = 0;
        $project->points_completed = 0;

        $project->save();

        return redirect()->route('projects.index')->with('success', "Proyecto '{$project->name}' ({$finalCode}) registrado exitosamente.");
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'razon_social' => 'nullable|string|max:255',
            'direccion' => 'nullable|string|max:500',
            'company_id' => 'required|exists:companies,id',
            'manager_id' => 'nullable|exists:managers,id',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'code' => 'required|string|max:100|unique:projects,code,' . $project->id,
            'status' => 'nullable|string|max:50',
        ]);

        $status = $validated['status'] ?? $project->status;
        $statusType = match ($status) {
            'En Ejecución', 'En Progreso', 'Activo' => 'in_progress',
            'Completado', 'Finalizado' => 'done',
            'En Pausa', 'Detenido' => 'delayed',
            default => 'pending',
        };

        $project->name = $validated['name'];
        $project->razon_social = $validated['razon_social'] ?? null;
        $project->direccion = $validated['direccion'] ?? null;
        $project->code = strtoupper($validated['code']);
        $project->company_id = $validated['company_id'];
        $project->manager_id = $validated['manager_id'] ?? null;
        $project->description = $validated['description'] ?? null;
        $project->start_date = $validated['start_date'];
        $project->status = $status;
        $project->status_type = $statusType;

        $project->save();

        return redirect()->route('projects.index')->with('success', 'Proyecto actualizado exitosamente.');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $name = $project->name;
        $project->delete();

        return redirect()->route('projects.index')->with('success', "Proyecto '{$name}' eliminado exitosamente.");
    }

    /**
     * Store a newly created measurement module
     */
    public function storeModule(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:100',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'field_staff_ids' => 'nullable|array',
            'field_staff_ids.*' => 'exists:staff,id',
            'field_staff_id' => 'nullable|exists:staff,id',
            'equipment_id' => 'nullable|exists:equipment,id',
            'calibration_equipment' => 'nullable|string|max:255',
            'points_total' => 'required|integer|min:1',
            'project_id' => 'nullable|exists:projects,id',
        ]);

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
        $moduleName = !empty($validated['name']) 
            ? $validated['name'] 
            : ($keyNames[$validated['key']] ?? ucfirst(str_replace('_', ' ', $validated['key'])));

        // Asociar a proyecto si existe
        $projectId = $validated['project_id'] ?? null;
        if (!$projectId) {
            $currentUser = Auth::user();
            $manager = $currentUser ? Manager::where('email', $currentUser->email)->first() : null;
            if ($manager) {
                $projectId = Project::where('manager_id', $manager->id)->value('id') ?? Project::value('id');
            } else {
                $projectId = Project::value('id');
            }
        }

        // Procesar personal múltiple
        $staffIds = [];
        if (!empty($validated['field_staff_ids']) && is_array($validated['field_staff_ids'])) {
            $staffIds = array_values(array_unique(array_map('intval', $validated['field_staff_ids'])));
        } elseif (!empty($validated['field_staff_id'])) {
            $staffIds = [intval($validated['field_staff_id'])];
        }
        $primaryStaffId = !empty($staffIds) ? $staffIds[0] : null;

        // Procesar equipo de medición
        $equipmentId = !empty($validated['equipment_id']) ? intval($validated['equipment_id']) : null;
        $calibrationEquipment = $validated['calibration_equipment'] ?? null;
        if ($equipmentId) {
            $eq = Equipment::find($equipmentId);
            if ($eq) {
                $calibrationEquipment = $eq->name . ($eq->model ? " ({$eq->model})" : '') . ($eq->serial_number ? " - S/N: {$eq->serial_number}" : '');
            }
        }
        if (empty($calibrationEquipment)) {
            $calibrationEquipment = 'Equipo Estándar de Medición';
        }

        $module = new MeasurementModule();
        $module->project_id = $projectId;
        $module->key = $validated['key'];
        $module->name = $moduleName;
        $module->description = $validated['description'] ?? null;
        $module->field_staff_id = $primaryStaffId;
        $module->field_staff_ids = !empty($staffIds) ? $staffIds : null;
        $module->equipment_id = $equipmentId;
        $module->calibration_equipment = $calibrationEquipment;
        $module->points_total = $validated['points_total'] ?? 1;
        $module->points_completed = 0;
        $module->status = 'En Progreso';
        $module->status_theme = 'in_progress';
        $module->save();

        return redirect()->route('modules.index')->with('success', "Módulo '{$module->name}' registrado exitosamente.");
    }

    /**
     * Update an existing measurement module
     */
    public function updateModule(Request $request, $id)
    {
        $module = MeasurementModule::findOrFail($id);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'key' => 'required|string|max:100',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'field_staff_ids' => 'nullable|array',
            'field_staff_ids.*' => 'exists:staff,id',
            'field_staff_id' => 'nullable|exists:staff,id',
            'equipment_id' => 'nullable|exists:equipment,id',
            'calibration_equipment' => 'nullable|string|max:255',
            'points_total' => 'required|integer|min:1',
        ]);

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
        $moduleName = !empty($validated['name']) 
            ? $validated['name'] 
            : ($keyNames[$validated['key']] ?? $module->name);

        // Procesar personal múltiple
        $staffIds = [];
        if (!empty($validated['field_staff_ids']) && is_array($validated['field_staff_ids'])) {
            $staffIds = array_values(array_unique(array_map('intval', $validated['field_staff_ids'])));
        } elseif (!empty($validated['field_staff_id'])) {
            $staffIds = [intval($validated['field_staff_id'])];
        }
        $primaryStaffId = !empty($staffIds) ? $staffIds[0] : null;

        // Procesar equipo de medición
        $equipmentId = !empty($validated['equipment_id']) ? intval($validated['equipment_id']) : null;
        $calibrationEquipment = $validated['calibration_equipment'] ?? $module->calibration_equipment;
        if ($equipmentId) {
            $eq = Equipment::find($equipmentId);
            if ($eq) {
                $calibrationEquipment = $eq->name . ($eq->model ? " ({$eq->model})" : '') . ($eq->serial_number ? " - S/N: {$eq->serial_number}" : '');
            }
        }

        $module->project_id = $validated['project_id'];
        $module->key = $validated['key'];
        $module->name = $moduleName;
        $module->description = $validated['description'] ?? null;
        $module->field_staff_id = $primaryStaffId;
        $module->field_staff_ids = !empty($staffIds) ? $staffIds : null;
        if ($equipmentId) {
            $module->equipment_id = $equipmentId;
        }
        $module->calibration_equipment = $calibrationEquipment;
        $module->points_total = $validated['points_total'];
        $module->save();

        return redirect()->route('modules.index')->with('success', "Módulo '{$module->name}' actualizado exitosamente.");
    }

    /**
     * Delete a measurement module
     */
    public function destroyModule($id)
    {
        $module = MeasurementModule::findOrFail($id);
        $name = $module->name;
        $module->delete();

        return redirect()->route('modules.index')->with('success', "Módulo '{$name}' eliminado exitosamente.");
    }

    /**
     * Monitoreo en Tiempo Real (Live Radar Dashboard)
     */
    public function liveMonitoring($id)
    {
        $project = Project::with([
            'company',
            'manager',
            'modules.fieldStaff',
            'modules.equipment'
        ])->findOrFail($id);

        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        // Mapeo de metadatos y paleta por tipo de módulo
        $moduleMetadata = [
            'iluminacion' => [
                'name' => 'Iluminación Ocupacional',
                'color' => '#f59e0b',
                'bg' => '#fffbeb',
                'border' => '#fde68a',
                'icon' => 'lightbulb',
            ],
            'dosimetria' => [
                'name' => 'Dosimetría de Ruido',
                'color' => '#8b5cf6',
                'bg' => '#f5f3ff',
                'border' => '#ddd6fe',
                'icon' => 'volume-2',
            ],
            'ruido_ambiental' => [
                'name' => 'Ruido Ambiental',
                'color' => '#6366f1',
                'bg' => '#eef2ff',
                'border' => '#c7d2fe',
                'icon' => 'activity',
            ],
            'gases' => [
                'name' => 'Gases Ocupacionales',
                'color' => '#10b981',
                'bg' => '#ecfdf5',
                'border' => '#a7f3d0',
                'icon' => 'wind',
            ],
            'inspeccion_fotografica' => [
                'name' => 'Inspección Fotográfica',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
                'border' => '#bae6fd',
                'icon' => 'camera',
            ],
            'fotografica' => [
                'name' => 'Inspección Fotográfica',
                'color' => '#0284c7',
                'bg' => '#e0f2fe',
                'border' => '#bae6fd',
                'icon' => 'camera',
            ],
            'ventilacion' => [
                'name' => 'Ventilación Ocupacional',
                'color' => '#14b8a6',
                'bg' => '#f0fdfa',
                'border' => '#99f6e4',
                'icon' => 'airplay',
            ],
            'estres_termico_calor' => [
                'name' => 'Estrés Térmico (Calor)',
                'color' => '#f43f5e',
                'bg' => '#fff1f2',
                'border' => '#fecdd3',
                'icon' => 'sun',
            ],
            'estres_calor' => [
                'name' => 'Estrés Térmico (Calor)',
                'color' => '#f43f5e',
                'bg' => '#fff1f2',
                'border' => '#fecdd3',
                'icon' => 'sun',
            ],
            'estres_termico_frio' => [
                'name' => 'Estrés Térmico (Frío)',
                'color' => '#38bdf8',
                'bg' => '#f0f9ff',
                'border' => '#bae6fd',
                'icon' => 'cloud-snow',
            ],
            'estres_frio' => [
                'name' => 'Estrés Térmico (Frío)',
                'color' => '#38bdf8',
                'bg' => '#f0f9ff',
                'border' => '#bae6fd',
                'icon' => 'cloud-snow',
            ],
            'carga_fuego_actividad' => [
                'name' => 'Carga de Fuego (Actividad)',
                'color' => '#f97316',
                'bg' => '#fff7ed',
                'border' => '#ffedd5',
                'icon' => 'flame',
            ],
            'fuego_actividad' => [
                'name' => 'Carga de Fuego (Actividad)',
                'color' => '#f97316',
                'bg' => '#fff7ed',
                'border' => '#ffedd5',
                'icon' => 'flame',
            ],
            'carga_fuego_peso' => [
                'name' => 'Carga de Fuego (Peso)',
                'color' => '#ea580c',
                'bg' => '#fff7ed',
                'border' => '#fed7aa',
                'icon' => 'shield',
            ],
            'fuego_peso' => [
                'name' => 'Carga de Fuego (Peso)',
                'color' => '#ea580c',
                'bg' => '#fff7ed',
                'border' => '#fed7aa',
                'icon' => 'shield',
            ],
            'ergonomia_reba' => [
                'name' => 'Ergonomía REBA',
                'color' => '#a855f7',
                'bg' => '#faf5ff',
                'border' => '#e9d5ff',
                'icon' => 'user-check',
            ],
            'ergonomia_rosa' => [
                'name' => 'Ergonomía ROSA',
                'color' => '#d946ef',
                'bg' => '#fdf4ff',
                'border' => '#f5d0fe',
                'icon' => 'monitor',
            ],
            'opacidad' => [
                'name' => 'Opacidad Vehicular',
                'color' => '#64748b',
                'bg' => '#f8fafc',
                'border' => '#e2e8f0',
                'icon' => 'truck',
            ],
            'vibracion' => [
                'name' => 'Vibración Ocupacional',
                'color' => '#eab308',
                'bg' => '#fefce8',
                'border' => '#fef08a',
                'icon' => 'radio',
            ],
        ];

        // Recolectar módulos creados para el proyecto
        $activeModulesList = [];
        $uniqueStaff = [];

        foreach ($project->modules as $mod) {
            $key = $mod->key;
            $meta = $moduleMetadata[$key] ?? [
                'name' => $mod->name,
                'color' => '#0284c7',
                'bg' => '#f0f9ff',
                'border' => '#bae6fd',
                'icon' => 'layers',
            ];

            // Conteo de puntos completados
            $ptsCount = $mod->points_completed ?: 0;

            $activeModulesList[] = [
                'id' => $mod->id,
                'key' => $key,
                'name' => $mod->name ?: $meta['name'],
                'color' => $meta['color'],
                'bg' => $meta['bg'],
                'border' => $meta['border'],
                'icon' => $meta['icon'],
                'points_count' => $ptsCount,
                'points_total' => $mod->points_total,
                'status' => $mod->status,
                'field_staff' => $mod->fieldStaff ? $mod->fieldStaff->name : null,
            ];

            if ($mod->fieldStaff) {
                $uniqueStaff[$mod->fieldStaff->id] = $mod->fieldStaff->name;
            }
        }

        return view('projects.live-monitoring', compact(
            'project',
            'activeModulesList',
            'uniqueStaff',
            'userName',
            'userRole'
        ));
    }

    /**
     * Endpoint API / AJAX para Polling en Tiempo Real de Mediciones
     */
    public function liveMonitoringData(Request $request, $id)
    {
        $project = Project::with(['modules'])->findOrFail($id);
        $since = $request->query('since'); // ISO date or null

        $measurements = [];
        $latestTimestamp = null;

        $moduleColors = [
            'iluminacion' => '#f59e0b',
            'dosimetria' => '#8b5cf6',
            'ruido_ambiental' => '#6366f1',
            'gases' => '#10b981',
            'inspeccion_fotografica' => '#0284c7',
            'fotografica' => '#0284c7',
            'ventilacion' => '#14b8a6',
            'estres_termico_calor' => '#f43f5e',
            'estres_calor' => '#f43f5e',
            'estres_termico_frio' => '#38bdf8',
            'estres_frio' => '#38bdf8',
            'carga_fuego_actividad' => '#f97316',
            'fuego_actividad' => '#f97316',
            'carga_fuego_peso' => '#ea580c',
            'fuego_peso' => '#ea580c',
            'ergonomia_reba' => '#a855f7',
            'reba' => '#a855f7',
            'ergonomia_rosa' => '#d946ef',
            'rosa' => '#d946ef',
            'opacidad' => '#64748b',
            'vibracion' => '#eab308',
        ];

        foreach ($project->modules as $mod) {
            $key = $mod->key;
            $modColor = $moduleColors[$key] ?? '#0284c7';
            $items = collect();

            switch ($key) {
                case 'iluminacion':
                    $q = $mod->illuminationMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $luxList = is_array($m->mediciones_lux) ? $m->mediciones_lux : (json_decode($m->mediciones_lux, true) ?: []);
                        $avgLux = !empty($luxList) ? round(array_sum($luxList) / count($luxList), 1) : null;
                        $summary = $avgLux !== null ? "{$avgLux} Lux" : 'Medición Lux';
                        $statusBadge = ($avgLux && $m->nivel_requerido) 
                            ? ($avgLux >= $m->nivel_requerido ? 'Cumple' : 'No Cumple') 
                            : 'Registrado';

                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary,
                            $m->nivel_requerido ? "Req: {$m->nivel_requerido} Lux" : ($m->tipo_iluminacion ?: 'Iluminación'),
                            $statusBadge,
                            $m->puesto_trabajo
                        );
                    });
                    break;

                case 'dosimetria':
                case 'ruido':
                    $q = $mod->dosimetryMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = ($m->leq_db !== null ? "{$m->leq_db} dBA" : 'Ruido Ocupacional');
                        $sec = ($m->dosis_pct !== null ? "Dosis: {$m->dosis_pct}%" : '');
                        $statusBadge = ($m->leq_db !== null && $m->leq_db > 85.0) ? 'Alerta LMP' : 'Conforme';

                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $sec, $statusBadge, $m->puesto_trabajo
                        );
                    });
                    break;

                case 'ruido_ambiental':
                    $q = $mod->ruidoAmbientalMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = "Leq: {$m->leq_db} dBA";
                        $sec = "Lmax: {$m->lmax_db} dBA";
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $sec, 'Registrado', $m->tipo_fuente
                        );
                    });
                    break;

                case 'gases':
                    $q = $mod->gasMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $gases = is_array($m->gases_evaluados) ? $m->gases_evaluados : (json_decode($m->gases_evaluados, true) ?: []);
                        $gasSummary = !empty($gases) ? count($gases) . ' Gases Monitoreados' : 'Monitoreo Gases';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $gasSummary, $m->cumplimiento ?: 'En rango', $m->cumplimiento ?: 'Conforme', $m->puesto_trabajo
                        );
                    });
                    break;

                case 'inspeccion_fotografica':
                case 'fotografica':
                    $q = $mod->photographicInspections()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $cat = $m->observation ?: 'Inspección Visual';
                        $desc = $m->description ?: 'Hallazgo fotográfico';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $cat, $desc, 'Evaluado', null
                        );
                    });
                    break;

                case 'ventilacion':
                    $q = $mod->ventilationMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->velocidad_ms !== null ? "{$m->velocidad_ms} m/s" : 'Flujo Aire';
                        $sec = $m->caudal_m3h !== null ? "Caudal: {$m->caudal_m3h} m³/h" : ($m->tipo_sistema ?: '');
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $sec, 'Medido', $m->tipo_sistema
                        );
                    });
                    break;

                case 'estres_termico_calor':
                case 'estres_calor':
                    $q = $mod->heatStressMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->tgbh_interior !== null ? "TGBH Int: {$m->tgbh_interior} °C" : 'Estrés Calor';
                        $sec = $m->tgbh_exterior !== null ? "Ext: {$m->tgbh_exterior} °C" : '';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $sec, 'Evaluado', $m->puesto_trabajo
                        );
                    });
                    break;

                case 'estres_termico_frio':
                case 'estres_frio':
                    $q = $mod->coldStressMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->temperatura_aire !== null ? "{$m->temperatura_aire} °C" : 'Estrés Frío';
                        $sec = $m->velocidad_viento !== null ? "Viento: {$m->velocidad_viento} m/s" : '';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $sec, 'Evaluado', $m->puesto_trabajo
                        );
                    });
                    break;

                case 'carga_fuego_actividad':
                case 'fuego_actividad':
                    $q = $mod->fireActivityMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->densidad_carga !== null ? "{$m->densidad_carga} MJ/m²" : 'Carga Fuego';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $m->actividad ?: 'Actividad Industrial', $m->nivel_riesgo ?: 'Evaluado', null
                        );
                    });
                    break;

                case 'carga_fuego_peso':
                case 'fuego_peso':
                    $q = $mod->fireWeightMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->densidad_carga_mj_m2 !== null ? "{$m->densidad_carga_mj_m2} MJ/m²" : 'Carga Peso';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $m->materiales ?: 'Inventario', 'Calculado', null
                        );
                    });
                    break;

                case 'ergonomia_reba':
                case 'reba':
                    $q = $mod->rebaMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->puntuacion_final !== null ? "REBA: {$m->puntuacion_final}" : 'Ergonomía REBA';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $m->tarea ?: '', "Nivel {$m->nivel_accion}", null
                        );
                    });
                    break;

                case 'ergonomia_rosa':
                case 'rosa':
                    $q = $mod->rosaMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->puntuacion_rosa !== null ? "ROSA: {$m->puntuacion_rosa}" : 'Ergonomía ROSA';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $m->puesto_trabajo ?: '', "Riesgo {$m->nivel_riesgo}", $m->puesto_trabajo
                        );
                    });
                    break;

                case 'opacidad':
                    $q = $mod->opacityMeasurements()->with('staff');
                    if ($since) $q->where('created_at', '>', $since);
                    $items = $q->get()->map(function ($m) use ($key, $mod, $modColor) {
                        $summary = $m->opacidad_pct !== null ? "{$m->opacidad_pct}% Opacidad" : 'Opacidad';
                        $sec = $m->vehiculo_placa ? "Placa: {$m->vehiculo_placa}" : '';
                        return $this->formatLivePoint(
                            $m, $key, $mod, $modColor,
                            $summary, $sec, 'Evaluado', null
                        );
                    });
                    break;
            }

            foreach ($items as $item) {
                if ($item) {
                    $measurements[] = $item;
                    if (!$latestTimestamp || $item['created_at_iso'] > $latestTimestamp) {
                        $latestTimestamp = $item['created_at_iso'];
                    }
                }
            }
        }

        // Ordenar del más reciente al más antiguo
        usort($measurements, function ($a, $b) {
            return strcmp($b['created_at_iso'], $a['created_at_iso']);
        });

        return response()->json([
            'success' => true,
            'project_id' => $project->id,
            'project_name' => $project->name,
            'total_points' => count($measurements),
            'last_timestamp' => $latestTimestamp ?: now()->toIso8601String(),
            'measurements' => $measurements,
        ]);
    }

    /**
     * Formateador estandarizado de punto de medición para el Radar en Vivo
     */
    private function formatLivePoint($m, $moduleKey, $mod, $color, $summary, $secondary, $statusBadge, $workstation = null)
    {
        $lat = $m->latitude !== null ? (float)$m->latitude : null;
        $lng = $m->longitude !== null ? (float)$m->longitude : null;
        $utmE = $m->utm_easting !== null ? (float)$m->utm_easting : null;
        $utmN = $m->utm_northing !== null ? (float)$m->utm_northing : null;
        $utmZ = $m->utm_zone ?: '20K';

        // Si faltan lat/long pero se tienen coordenadas UTM, convertir al vuelo
        if (($lat === null || $lng === null || ($lat == 0 && $lng == 0)) && $utmE && $utmN) {
            $converted = $this->utmToLatLng($utmE, $utmN, $utmZ, true);
            if ($converted) {
                $lat = $converted['latitude'];
                $lng = $converted['longitude'];
            }
        }

        // Formateo de imágenes
        $images = is_array($m->images) ? $m->images : (json_decode($m->images, true) ?: []);
        if (empty($images) && !empty($m->image_path)) {
            $images = [$m->image_path];
        }
        if (empty($images) && !empty($m->image_urls)) {
            $images = is_array($m->image_urls) ? $m->image_urls : (json_decode($m->image_urls, true) ?: []);
        }

        $imageUrls = array_values(array_filter(array_map(function ($p) {
            if (!$p) return null;
            if (str_starts_with($p, 'http://') || str_starts_with($p, 'https://')) return $p;
            return asset($p);
        }, $images)));

        // Nombre de responsable / técnico de campo
        $authorName = 'Técnico de Campo';
        if ($m->staff) {
            $authorName = $m->staff->name;
        } elseif (!empty($m->registered_by)) {
            $authorName = $m->registered_by;
        } elseif ($mod->fieldStaff) {
            $authorName = $mod->fieldStaff->name;
        }

        $createdAt = $m->created_at ?: now();
        $createdAtIso = $createdAt ? $createdAt->toIso8601String() : now()->toIso8601String();
        $dateFormatted = $createdAt ? $createdAt->format('d/m/Y H:i') : '-';
        $timeAgo = $createdAt ? $createdAt->diffForHumans() : 'reciente';

        return [
            'id' => "{$moduleKey}_{$m->id}",
            'raw_id' => $m->id,
            'module_id' => $mod->id,
            'module_key' => $moduleKey,
            'module_name' => $mod->name,
            'module_color' => $color,
            'point_number' => $m->point_number ?: sprintf('%02d', $m->id),
            'area' => $m->area ?: 'Área General',
            'workstation' => $workstation ?: ($m->puesto_trabajo ?? '-'),
            'summary_value' => $summary,
            'secondary_value' => $secondary,
            'status_badge' => $statusBadge,
            'registered_by' => $authorName,
            'created_at_iso' => $createdAtIso,
            'date_formatted' => $dateFormatted,
            'time_ago' => $timeAgo,
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZ,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'has_coordinates' => ($lat !== null && $lng !== null && ($lat != 0 || $lng != 0)),
            'images' => $imageUrls,
        ];
    }

    /**
     * Conversor matemático WGS84 UTM a Latitud / Longitud (Hemisferio Sur por defecto)
     */
    private function utmToLatLng($easting, $northing, $zone = '20', $southernHemisphere = true)
    {
        if (!$easting || !$northing || $easting < 100000 || $northing < 100000) return null;
        $zoneNumber = intval(preg_replace('/[^0-9]/', '', $zone)) ?: 20;

        $k0 = 0.9996;
        $a = 6378137.0; // Radio mayor WGS84
        $e = 0.081819191; // Excentricidad WGS84
        $e1sq = 0.006739497;

        $x = $easting - 500000.0;
        $y = $northing;
        if ($southernHemisphere) {
            $y -= 10000000.0;
        }

        $longOrigin = ($zoneNumber - 1) * 6 - 180 + 3;

        $m = $y / $k0;
        $mu = $m / ($a * (1.0 - $e * $e / 4.0 - 3.0 * $e * $e * $e * $e / 64.0 - 5.0 * $e * $e * $e * $e * $e * $e / 256.0));

        $e1 = (1.0 - sqrt(1.0 - $e * $e)) / (1.0 + sqrt(1.0 - $e * $e));

        $j1 = (3.0 * $e1 / 2.0 - 27.0 * $e1 * $e1 * $e1 / 32.0);
        $j2 = (21.0 * $e1 * $e1 / 16.0 - 55.0 * $e1 * $e1 * $e1 * $e1 / 32.0);
        $j3 = (151.0 * $e1 * $e1 * $e1 / 96.0);
        $j4 = (1097.0 * $e1 * $e1 * $e1 * $e1 / 512.0);

        $fp = $mu + $j1 * sin(2.0 * $mu) + $j2 * sin(4.0 * $mu) + $j3 * sin(6.0 * $mu) + $j4 * sin(8.0 * $mu);

        $c1 = $e1sq * cos($fp) * cos($fp);
        $t1 = tan($fp) * tan($fp);
        $n1 = $a / sqrt(1.0 - $e * $e * sin($fp) * sin($fp));
        $r1 = $a * (1.0 - $e * $e) / pow(1.0 - $e * $e * sin($fp) * sin($fp), 1.5);
        $d = $x / ($n1 * $k0);

        $lat = $fp - ($n1 * tan($fp) / $r1) * ($d * $d / 2.0 - (5.0 + 3.0 * $t1 + 10.0 * $c1 - 4.0 * $c1 * $c1 - 9.0 * $e1sq) * $d * $d * $d * $d / 24.0 + (61.0 + 90.0 * $t1 + 298.0 * $c1 + 45.0 * $t1 * $t1 - 252.0 * $e1sq - 3.0 * $c1 * $c1) * $d * $d * $d * $d * $d * $d / 720.0);
        $lat = rad2deg($lat);

        $lon = ($d - (1.0 + 2.0 * $t1 + $c1) * $d * $d * $d / 6.0 + (5.0 - 2.0 * $c1 + 28.0 * $t1 - 3.0 * $c1 * $c1 + 8.0 * $e1sq + 24.0 * $t1 * $t1) * $d * $d * $d * $d * $d / 120.0) / cos($fp);
        $lon = $longOrigin + rad2deg($lon);

        return [
            'latitude' => round($lat, 6),
            'longitude' => round($lon, 6),
        ];
    }
}

