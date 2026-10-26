<?php

namespace App\Http\Controllers;

use App\Models\MeasurementModule;
use App\Models\Project;
use App\Models\Staff;
use App\Models\Equipment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PhotoReportController extends Controller
{
    /**
     * Muestra la página unificada de Reporte Fotográfico de Monitoreos.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $projects = Project::with('company')->orderBy('name')->get();

        $selectedProjectId = $request->query('proyecto');
        $selectedModuleId = $request->query('modulo') ?: $request->query('module_id');

        $modulesQuery = MeasurementModule::with(['project.company', 'fieldStaff', 'equipment']);
        if ($selectedProjectId && $selectedProjectId !== 'todos') {
            $modulesQuery->where('project_id', $selectedProjectId);
        }
        $modules = $modulesQuery->orderBy('name')->get();

        $activeProject = null;
        if ($selectedProjectId && $selectedProjectId !== 'todos') {
            $activeProject = $projects->firstWhere('id', $selectedProjectId);
        }

        $activeModule = null;
        if ($selectedModuleId) {
            $activeModule = MeasurementModule::with(['project.company', 'fieldStaff', 'equipment'])->find($selectedModuleId);
        }

        if (!$activeModule && $modules->isNotEmpty()) {
            $activeModule = $modules->first();
        }

        $formattedData = null;
        if ($activeModule) {
            $formattedData = $this->formatModuleData($activeModule);
        }

        // Resumen ligero de todos los módulos para el selector reactivo
        $allModulesList = MeasurementModule::with(['project.company'])->orderBy('name')->get()->map(function ($m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'key' => $m->key,
                'monitoring_type' => $m->monitoring_type ?: $m->name,
                'project_id' => $m->project_id,
                'project_name' => $m->project ? $m->project->name : 'Proyecto General',
                'company_name' => ($m->project && $m->project->company) ? $m->project->company->name : 'Pachabol',
                'points_count' => $m->points_total ?: 0,
            ];
        });

        return view('projects.photo_report', [
            'userName' => $userName,
            'userRole' => $userRole,
            'projects' => $projects,
            'modules' => $modules,
            'allModulesList' => $allModulesList,
            'selectedProjectId' => $selectedProjectId,
            'selectedModuleId' => $selectedModuleId,
            'activeProject' => $activeProject,
            'activeModule' => $activeModule,
            'initialData' => $formattedData,
        ]);
    }

    /**
     * Endpoint API/AJAX: Devuelve los datos fotográficos y mediciones de un módulo específico.
     */
    public function getModuleData($id)
    {
        $module = MeasurementModule::with(['project.company', 'fieldStaff', 'equipment'])->findOrFail($id);
        $data = $this->formatModuleData($module);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Endpoint API: Guarda la configuración de mosaico (grid, fotos seleccionadas, encuadre) de un módulo.
     */
    public function saveSettings(Request $request, $id)
    {
        $module = MeasurementModule::findOrFail($id);
        $payload = $request->input('settings') ?? $request->all();

        $settings = [
            'grid' => $payload['grid'] ?? '2x3',
            'selected_points' => $payload['selected_points'] ?? [],
            'photo_indices' => $payload['photo_indices'] ?? [],
            'image_fit' => $payload['image_fit'] ?? 'contain',
            'photo_fits' => $payload['photo_fits'] ?? [],
        ];

        $module->photo_report_settings = $settings;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de mosaico fotográfico guardada con éxito en el módulo.',
            'settings' => $settings,
        ]);
    }

    /**
     * Normaliza los datos técnicos y fotografías de cualquier tipo de monitoreo ambiental o industrial.
     */
    protected function formatModuleData(MeasurementModule $module): array
    {
        $key = strtolower(trim($module->key ?? ''));

        // Ficha técnica del estudio
        $projectName = ($module->project) ? $module->project->name : 'Proyecto Industrial Alpha';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : 'Empresa Principal';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : date('d/m/Y', strtotime('-3 days'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : date('d/m/Y');

        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Equipo Certificado de Monitoreo');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Baseline / Cal') : 'Equipo Certificado';
        $equipmentModel = $equipment ? ($equipment->model ?: 'Model Pro') : 'Serie Industrial';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'CAL-99210') : 'CAL-99210';

        $assignedStaff = method_exists($module, 'getAssignedStaffAttribute') ? $module->getAssignedStaffAttribute() : null;
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
        } else {
            $registeredByHeader = Auth::user() ? Auth::user()->name : 'Ing. Reynaldo Pachabol (Especialista SySO)';
        }

        $normative = $this->getNormativeReference($key);

        // Obtener mediciones reales según el tipo de módulo
        $dbMeasurements = $this->fetchMeasurementsByModuleKey($module, $key);

        $measurementsList = [];
        if ($dbMeasurements->isNotEmpty()) {
            $measurementsList = $dbMeasurements->map(function ($item, $index) use ($key, $registeredByHeader) {
                // Extracción segura de imágenes
                $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
                if (empty($rawImages) && !empty($item->image_path)) {
                    $rawImages = [$item->image_path];
                }
                $imagesUrls = array_values(array_filter(array_map(function($p) {
                    return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
                }, $rawImages)));

                $pointNum = $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $code = $this->formatPointCode($key, $pointNum);

                $extracted = $this->extractTechnicalDetails($item, $key);

                return [
                    'id' => $item->id,
                    'num' => $pointNum,
                    'code' => $code,
                    'title' => $extracted['title'],
                    'area' => $extracted['area'],
                    'badge_label' => $extracted['badge_label'],
                    'badge_color' => $extracted['badge_color'],
                    'badge_bg' => $extracted['badge_bg'],
                    'date' => $item->measurement_date ? Carbon::parse($item->measurement_date)->format('d/m/Y') : ($item->date ?? '—'),
                    'time' => $item->measurement_time ?: ($item->time ?? '—'),
                    'registered_by' => $item->staff ? ($item->staff->full_name ?: $item->staff->name) : ($item->registered_by ?: $registeredByHeader),
                    'images' => $imagesUrls,
                    'image_path' => $imagesUrls[0] ?? null,
                    'images_count' => count($imagesUrls),
                    'details' => $extracted['details'],
                ];
            })->toArray();
        }

        // Ajustes de reporte guardados
        $savedSettings = $module->photo_report_settings ?: [];
        $photoReportSettings = [
            'grid' => $savedSettings['grid'] ?? ($savedSettings['distribution'] ?? '2x3'),
            'selected_points' => $savedSettings['selected_points'] ?? array_column($measurementsList, 'id'),
            'photo_indices' => $savedSettings['photo_indices'] ?? [],
            'image_fit' => $savedSettings['image_fit'] ?? 'contain',
            'photo_fits' => $savedSettings['photo_fits'] ?? [],
        ];

        return [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'key' => $module->key,
                'project_id' => $module->project_id,
                'project_name' => $projectName,
                'company_name' => $companyName,
                'installation_name' => $installationName,
                'monitoring_type' => $module->monitoring_type ?: $module->name,
                'normative' => $normative,
                'start_date' => $startDateFormatted,
                'end_date' => $endDateFormatted,
                'equipment_name' => $equipmentName,
                'equipment_brand' => $equipmentBrand,
                'equipment_model' => $equipmentModel,
                'equipment_serial' => $equipmentSerial,
                'registered_by' => $registeredByHeader,
            ],
            'measurements' => $measurementsList,
            'total_measurements' => count($measurementsList),
            'total_with_photos' => count(array_filter($measurementsList, fn($m) => !empty($m['image_path']))),
            'photo_report_settings' => $photoReportSettings,
        ];
    }

    /**
     * Consulta las mediciones correspondientes según la clave del módulo.
     */
    protected function fetchMeasurementsByModuleKey(MeasurementModule $module, string $key)
    {
        $relations = [
            'ergonomia_rosa' => 'rosaMeasurements',
            'rosa' => 'rosaMeasurements',
            'ergonomia_reba' => 'rebaMeasurements',
            'reba' => 'rebaMeasurements',
            'iluminacion' => 'illuminationMeasurements',
            'iluminaciones' => 'illuminationMeasurements',
            'illumination' => 'illuminationMeasurements',
            'ventilacion' => 'ventilationMeasurements',
            'ventilaciones' => 'ventilationMeasurements',
            'ventilation' => 'ventilationMeasurements',
            'ruido_ocupacional' => 'noiseMeasurements',
            'ruido_ocupacionales' => 'noiseMeasurements',
            'noise' => 'noiseMeasurements',
            'ruido' => 'noiseMeasurements',
            'ruido_ambiental' => 'ruidoAmbientalMeasurements',
            'ruido_ambientales' => 'ruidoAmbientalMeasurements',
            'dosimetria' => 'dosimetryMeasurements',
            'dosimetrias' => 'dosimetryMeasurements',
            'estres_calor' => 'heatStressMeasurements',
            'estres_calores' => 'heatStressMeasurements',
            'heat_stress' => 'heatStressMeasurements',
            'estres_frio' => 'coldStressMeasurements',
            'estres_frios' => 'coldStressMeasurements',
            'cold_stress' => 'coldStressMeasurements',
            'vibracion' => 'vibracionMeasurements',
            'vibraciones' => 'vibracionMeasurements',
            'gases' => 'gasMeasurements',
            'gas' => 'gasMeasurements',
            'particulas' => 'particulasMeasurements',
            'particles' => 'particulasMeasurements',
            'particulas_ambientales' => 'particulasAmbientalesMeasurements',
            'ambient_particles' => 'particulasAmbientalesMeasurements',
            'carga_fuego_actividad' => 'fireActivityMeasurements',
            'carga_fuego_peso' => 'fireWeightMeasurements',
            'opacidad' => 'opacityMeasurements',
            'opacidades' => 'opacityMeasurements',
            'inspeccion_fotografica' => 'photographicInspections',
            'contaminantes_quimicos' => 'contaminantesQuimicosMeasurements',
            'quimicos' => 'contaminantesQuimicosMeasurements',
        ];

        $relation = $relations[$key] ?? null;
        if ($relation && method_exists($module, $relation)) {
            return $module->{$relation}()->with('staff')->get();
        }

        // Búsqueda inteligente de respaldo si no coincidiera la key exacta
        foreach ($relations as $rel) {
            if (method_exists($module, $rel)) {
                $check = $module->{$rel}()->with('staff')->get();
                if ($check->isNotEmpty()) {
                    return $check;
                }
            }
        }

        return collect([]);
    }

    /**
     * Extrae y normaliza título, área, badge y detalles técnicos según el tipo de monitoreo.
     */
    protected function extractTechnicalDetails($item, string $key): array
    {
        $title = $item->puesto_trabajo ?? ($item->point_name ?? ($item->tarea_evaluada ?? ($item->equipment_evaluated ?? 'Punto de Monitoreo')));
        $area = $item->area_sector ?? ($item->area ?? ($item->sector ?? 'Área de Trabajo'));
        $badgeLabel = 'Monitoreo';
        $badgeColor = '#0284c7';
        $badgeBg = '#e0f2fe';
        $details = '';

        if (str_contains($key, 'rosa')) {
            $score = $item->score_final ?? 5;
            $risk = $item->risk_level ?? 'Medio';
            $badgeLabel = "ROSA: {$score} • {$risk}";
            if ($score <= 2) { $badgeColor = '#166534'; $badgeBg = '#dcfce7'; }
            elseif ($score <= 4) { $badgeColor = '#854d0e'; $badgeBg = '#fef9c3'; }
            elseif ($score <= 7) { $badgeColor = '#c2410c'; $badgeBg = '#ffedd5'; }
            else { $badgeColor = '#991b1b'; $badgeBg = '#fee2e2'; }
            $tareas = is_array($item->tareas) ? implode(', ', array_slice($item->tareas, 0, 2)) : (string)($item->tareas ?? '');
            $details = $tareas ?: ($item->observaciones ?? '');
        } elseif (str_contains($key, 'reba')) {
            $score = $item->puntuacion_final ?? ($item->score_final ?? 4);
            $action = $item->nivel_accion ?? 'Medio';
            $badgeLabel = "REBA: {$score} • {$action}";
            $badgeColor = '#c2410c'; $badgeBg = '#ffedd5';
            $details = $item->tarea_evaluada ?? '';
        } elseif (str_contains($key, 'iluminac')) {
            $lux = $item->measured_lux ?? ($item->lux ?? '—');
            $status = $item->compliance_status ?? ($item->cumple ? 'Cumple' : 'No Cumple');
            $badgeLabel = "{$lux} Lux • {$status}";
            $badgeColor = strtolower($status) === 'cumple' ? '#166534' : '#991b1b';
            $badgeBg = strtolower($status) === 'cumple' ? '#dcfce7' : '#fee2e2';
            $details = 'Tipo de Tarea: ' . ($item->visual_task ?? 'General');
        } elseif (str_contains($key, 'ventilac')) {
            $flow = $item->air_flow ?? ($item->flujo_aire ?? '—');
            $badgeLabel = "{$flow} m³/h • " . ($item->compliance_status ?? 'Conforme');
            $badgeColor = '#0284c7'; $badgeBg = '#e0f2fe';
            $details = 'Renovaciones: ' . ($item->air_renovations ?? '—') . ' ren/h';
        } elseif (str_contains($key, 'ruido') || str_contains($key, 'noise') || str_contains($key, 'dosimetr')) {
            $dba = $item->laeq_measured ?? ($item->twa_measured ?? ($item->valor ?? '—'));
            $badgeLabel = "{$dba} dBA";
            $badgeColor = (float)$dba > 85 ? '#991b1b' : '#166534';
            $badgeBg = (float)$dba > 85 ? '#fee2e2' : '#dcfce7';
            $details = 'Exposición: ' . ($item->tiempo_exposicion ?? '8 hrs');
        } elseif (str_contains($key, 'vibrac')) {
            $awz = $item->awz_value ?? ($item->aceleracion ?? '—');
            $badgeLabel = "{$awz} m/s² • " . ($item->tipo_vibracion ?? 'Cuerpo Entero');
            $badgeColor = '#7c3aed'; $badgeBg = '#f3e8ff';
            $details = 'Eje dominante: ' . ($item->eje_dominante ?? 'Z');
        } elseif (str_contains($key, 'calor') || str_contains($key, 'heat') || str_contains($key, 'frio') || str_contains($key, 'cold')) {
            $temp = $item->tgbh_indoor ?? ($item->ambient_temp ?? ($item->temperatura ?? '—'));
            $badgeLabel = "{$temp} °C";
            $badgeColor = '#ea580c'; $badgeBg = '#ffedd5';
            $details = 'Régimen: ' . ($item->work_regime ?? 'Continuo');
        } elseif (str_contains($key, 'gas')) {
            $ppm = $item->measured_ppm ?? ($item->concentracion ?? '—');
            $gas = $item->gas_type ?? 'Gas';
            $badgeLabel = "{$gas}: {$ppm} ppm";
            $badgeColor = '#0891b2'; $badgeBg = '#cffafe';
            $details = 'LMP: ' . ($item->lmp_limit ?? 'Estándar');
        } elseif (str_contains($key, 'particul')) {
            $conc = $item->measured_concentration ?? ($item->concentracion ?? '—');
            $badgeLabel = "{$conc} mg/m³";
            $badgeColor = '#475569'; $badgeBg = '#f1f5f9';
            $details = 'Fracción: ' . ($item->fraction_type ?? 'Inhalable');
        } else {
            $badgeLabel = 'Evaluado';
            $badgeColor = '#0284c7'; $badgeBg = '#e0f2fe';
            $details = $item->observaciones ?? ($item->descripcion ?? '');
        }

        return [
            'title' => $title,
            'area' => $area,
            'badge_label' => $badgeLabel,
            'badge_color' => $badgeColor,
            'badge_bg' => $badgeBg,
            'details' => $details,
        ];
    }

    /**
     * Formatea el código correlativo (ej: ROSA-01, ILUM-01, etc.).
     */
    protected function formatPointCode(string $key, $num): string
    {
        $prefix = match (true) {
            str_contains($key, 'rosa') => 'ROSA',
            str_contains($key, 'reba') => 'REBA',
            str_contains($key, 'iluminac') => 'ILUM',
            str_contains($key, 'ventilac') => 'VENT',
            str_contains($key, 'ruido_amb') => 'RAMB',
            str_contains($key, 'ruido') || str_contains($key, 'noise') => 'RUIDO',
            str_contains($key, 'dosimetr') => 'DOSIM',
            str_contains($key, 'vibrac') => 'VIBR',
            str_contains($key, 'calor') => 'CALOR',
            str_contains($key, 'frio') => 'FRIO',
            str_contains($key, 'gas') => 'GAS',
            str_contains($key, 'particul') => 'PART',
            str_contains($key, 'fuego') => 'FUEGO',
            str_contains($key, 'opacid') => 'OPAC',
            default => 'MONIT',
        };

        $numeric = preg_replace('/[^0-9]/', '', (string)$num);
        if ($numeric) {
            return $prefix . '-' . str_pad($numeric, 2, '0', STR_PAD_LEFT);
        }
        return $prefix . '-' . trim((string)$num);
    }

    /**
     * Devuelve la normativa de referencia según el tipo de monitoreo.
     */
    protected function getNormativeReference(string $key): string
    {
        return match (true) {
            str_contains($key, 'rosa') => 'METODOLOGÍA ISO 9241-11 • ISO 11226 (RAPID OFFICE STRAIN ASSESSMENT)',
            str_contains($key, 'reba') => 'MÉTODO REBA • RAPID ENTIRE BODY ASSESSMENT (HIGNETT & MCATAMNEY)',
            str_contains($key, 'iluminac') => 'NORMA TÉCNICA NB 51001 • ILUMINACIÓN EN AMBIENTES DE TRABAJO',
            str_contains($key, 'ventilac') => 'NORMA NB/ISO 7730 • CONDICIONES AMBIENTALES Y VENTILACIÓN INDUSTRIAL',
            str_contains($key, 'ruido') || str_contains($key, 'noise') => 'NORMA TÉCNICA NB 51002 • EVALUACIÓN DE NIVELES DE RUIDO OCUPACIONAL',
            str_contains($key, 'vibrac') => 'NORMA ISO 2631 / ISO 5349 • VIBRACIONES MANO-BRAZO Y CUERPO ENTERO',
            str_contains($key, 'calor') => 'NORMA ISO 7243 • ESTIMACIÓN DEL ESTRÉS TÉRMICO MEDIANTE ÍNDICE TGBH',
            str_contains($key, 'frio') => 'NORMA ISO 11079 • EVALUACIÓN DE AMBIENTES FRÍOS Y AISLAMIENTO REQUERIDO',
            default => 'SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO — METRIC V2',
        };
    }
}
