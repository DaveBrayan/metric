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
}

