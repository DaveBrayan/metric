<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\MeasurementModule;
use App\Models\Company;
use App\Models\Region;
use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $companies = Company::all();
        $regions = Region::all();
        $managers = Manager::all();

        $projects = Project::with(['company', 'region', 'modules'])->get()->map(function ($prj, $index) {
            $totalMods = $prj->modules->count();
            $completedMods = $prj->modules->where('status', 'Completado')->count();
            $ratio = $totalMods > 0 ? round(($completedMods / $totalMods) * 100) : 0;

            return [
                'id' => $prj->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $prj->name,
                'code' => $prj->code,
                'client' => $prj->company ? $prj->company->name : 'General',
                'client_initial' => $prj->company ? strtoupper(substr($prj->company->name, 0, 1)) : 'G',
                'client_theme' => $prj->company ? ($prj->company->theme ?? 'cyan') : 'cyan',
                'region' => $prj->region ? $prj->region->name : 'Central',
                'modules_completed_text' => "$completedMods de $totalMods Módulos",
                'modules_ratio_pct' => $ratio,
                'progress' => round($prj->compliance_pct),
                'progress_theme' => ($prj->compliance_pct >= 98) ? 'lime' : 'cyan',
                'status' => $prj->status,
                'status_type' => $prj->status_type ?? 'in_progress',
            ];
        })->toArray();

        return view('projects.index', compact('userName', 'userRole', 'projects', 'companies', 'regions', 'managers'));
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

        $query = MeasurementModule::with(['project', 'fieldStaff']);
        if ($selectedModule !== 'todos') {
            $query->where('key', $selectedModule);
        }

        $modulesData = $query->get()->map(function ($mod, $index) {
            $ratio = $mod->points_total > 0 ? round(($mod->points_completed / $mod->points_total) * 100) : 0;

            return [
                'id' => $mod->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'module_key' => $mod->key,
                'module_name' => $mod->name,
                'module_sub' => $mod->project ? $mod->project->name : 'Monitoreo de Campo',
                'module_badge_theme' => ($mod->status_theme === 'done') ? 'lime' : 'cyan',
                'equipment' => $mod->calibration_equipment . ($mod->calibration_certificate ? " ({$mod->calibration_certificate})" : ''),
                'staff_name' => $mod->fieldStaff ? $mod->fieldStaff->name : 'Por Asignar',
                'staff_initial' => $mod->fieldStaff ? strtoupper(substr($mod->fieldStaff->name, 0, 1)) : 'A',
                'staff_theme' => $mod->fieldStaff ? ($mod->fieldStaff->role_theme ?? 'cyan') : 'cyan',
                'staff_role' => $mod->fieldStaff ? $mod->fieldStaff->position : 'Técnico de Campo',
                'points_text' => "{$mod->points_completed} de {$mod->points_total} puntos",
                'points_pct' => $ratio,
                'progress' => $ratio,
                'progress_theme' => ($ratio >= 90) ? 'lime' : 'cyan',
                'status' => $mod->status,
                'status_theme' => $mod->status_theme ?? 'in_progress',
            ];
        })->toArray();

        return view('projects.modules', compact('userName', 'userRole', 'modulesData', 'selectedModule'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:projects,code',
            'company_id' => 'required|exists:companies,id',
            'region_id' => 'required|exists:regions,id',
            'manager_id' => 'nullable|exists:managers,id',
        ]);

        Project::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'company_id' => $validated['company_id'],
            'region_id' => $validated['region_id'],
            'manager_id' => $validated['manager_id'] ?? null,
            'compliance_pct' => 100.00,
            'points_total' => 10,
            'points_completed' => 0,
            'status' => 'Planificación',
            'status_type' => 'pending',
        ]);

        return redirect()->route('projects.index')->with('success', 'Proyecto registrado exitosamente.');
    }
}
