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
                // Extracción segura y multiformato de imágenes (soporta photo_paths, images, image_urls, photos, image_path)
                $rawCandidateImages = [];
                if (!empty($item->photo_paths)) {
                    $rawCandidateImages = is_array($item->photo_paths) ? $item->photo_paths : (json_decode($item->photo_paths, true) ?: [$item->photo_paths]);
                }
                if (empty($rawCandidateImages) && !empty($item->images)) {
                    $rawCandidateImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: [$item->images]);
                }
                if (empty($rawCandidateImages) && !empty($item->image_urls)) {
                    $rawCandidateImages = is_array($item->image_urls) ? $item->image_urls : (json_decode($item->image_urls, true) ?: [$item->image_urls]);
                }
                if (empty($rawCandidateImages) && !empty($item->photos)) {
                    $rawCandidateImages = is_array($item->photos) ? $item->photos : (json_decode($item->photos, true) ?: [$item->photos]);
                }
                if (empty($rawCandidateImages) && !empty($item->image_path)) {
                    $rawCandidateImages = [$item->image_path];
                }
                if (empty($rawCandidateImages) && !empty($item->photo_url)) {
                    $rawCandidateImages = [$item->photo_url];
                }

                // Aplanar y sanitizar strings y arrays anidados
                $flatImages = [];
                foreach ((array)$rawCandidateImages as $img) {
                    if (is_array($img)) {
                        foreach ($img as $sub) {
                            if ($sub && is_string($sub)) {
                                $flatImages[] = $sub;
                            }
                        }
                    } elseif (is_string($img)) {
                        $trimmed = trim($img);
                        if (str_starts_with($trimmed, '[') && str_ends_with($trimmed, ']')) {
                            $decoded = json_decode($trimmed, true);
                            if (is_array($decoded)) {
                                foreach ($decoded as $sub) {
                                    if ($sub && is_string($sub)) {
                                        $flatImages[] = $sub;
                                    }
                                }
                                continue;
                            }
                        }
                        if ($trimmed !== '') {
                            $flatImages[] = $trimmed;
                        }
                    }
                }

                $imagesUrls = array_values(array_filter(array_map(function($p) {
                    if (empty($p)) return null;
                    $clean = trim($p, '"\'');
                    if (str_starts_with($clean, 'http://') || str_starts_with($clean, 'https://') || str_starts_with($clean, 'data:')) {
                        return $clean;
                    }
                    return asset(ltrim($clean, '/'));
                }, $flatImages)));

                // Extracción inteligente y robusta de fecha
                $dateVal = '—';
                $rawDate = $item->inspection_date 
                    ?? ($item->measurement_date 
                    ?? ($item->measured_at 
                    ?? ($item->date 
                    ?? ($item->fecha 
                    ?? ($item->created_at ?? null)))));

                if ($rawDate) {
                    try {
                        $dateVal = Carbon::parse($rawDate)->format('d/m/Y');
                    } catch (\Throwable $e) {
                        $dateVal = (string)$rawDate;
                    }
                }

                // Extracción inteligente y robusta de hora
                $timeVal = '—';
                $rawTime = $item->inspection_time 
                    ?? ($item->measurement_time 
                    ?? ($item->time 
                    ?? ($item->hora 
                    ?? ($item->hora_inicio ?? null))));

                if (!empty($rawTime) && $rawTime !== '—') {
                    $timeVal = substr((string)$rawTime, 0, 5);
                } elseif (!empty($item->measured_at)) {
                    try {
                        $parsed = Carbon::parse($item->measured_at);
                        if ($parsed->format('H:i') !== '00:00') {
                            $timeVal = $parsed->format('H:i');
                        }
                    } catch (\Throwable $e) {}
                }

                if ($timeVal === '—' && !empty($item->created_at)) {
                    try {
                        $timeVal = Carbon::parse($item->created_at)->format('H:i');
                    } catch (\Throwable $e) {}
                }

                $pointNum = $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $code = $this->formatPointCode($key, $pointNum);

                $extracted = $this->extractTechnicalDetails($item, $key);
                $category = $extracted['category'] ?? ($item->category ?? ($item->categoria ?? ($item->observation ?? '')));

                return [
                    'id' => $item->id,
                    'num' => $pointNum,
                    'code' => $code,
                    'title' => $extracted['title'],
                    'area' => $extracted['area'],
                    'category' => $category,
                    'badge_label' => $extracted['badge_label'],
                    'badge_color' => $extracted['badge_color'],
                    'badge_bg' => $extracted['badge_bg'],
                    'date' => $dateVal,
                    'time' => $timeVal,
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

        $isPhotographic = str_contains($key, 'fotograf') 
            || str_contains($key, 'inspecc') 
            || str_contains(mb_strtolower($module->name ?? ''), 'fotograf') 
            || str_contains(mb_strtolower($module->name ?? ''), 'inspecc');

        $categoriesPresent = [];
        if ($isPhotographic) {
            $categoriesPresent = collect($measurementsList)
                ->pluck('category')
                ->filter(fn($c) => !empty($c) && $c !== '—' && $c !== 'Inspección' && $c !== 'Evaluado')
                ->unique()
                ->values()
                ->toArray();
        }

        return [
            'module' => [
                'id' => $module->id,
                'name' => $module->name,
                'key' => $module->key,
                'is_photographic' => $isPhotographic,
                'categories' => $categoriesPresent,
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
            'calor' => 'heatStressMeasurements',
            'estres_frio' => 'coldStressMeasurements',
            'estres_frios' => 'coldStressMeasurements',
            'cold_stress' => 'coldStressMeasurements',
            'frio' => 'coldStressMeasurements',
            'vibracion' => 'vibracionMeasurements',
            'vibraciones' => 'vibracionMeasurements',
            'gases' => 'gasMeasurements',
            'gas' => 'gasMeasurements',
            'particulas' => 'particulasMeasurements',
            'particles' => 'particulasMeasurements',
            'particulas_ambientales' => 'particulasAmbientalesMeasurements',
            'ambient_particles' => 'particulasAmbientalesMeasurements',
            'carga_fuego_actividad' => 'fireActivityMeasurements',
            'fuego_actividad' => 'fireActivityMeasurements',
            'carga_fuego_peso' => 'fireWeightMeasurements',
            'fuego_peso' => 'fireWeightMeasurements',
            'opacidad' => 'opacityMeasurements',
            'opacidades' => 'opacityMeasurements',
            'opacity' => 'opacityMeasurements',
            'inspeccion_fotografica' => 'photographicInspections',
            'inspeccion_fotograficas' => 'photographicInspections',
            'fotografica' => 'photographicInspections',
            'fotograficas' => 'photographicInspections',
            'photographic_inspection' => 'photographicInspections',
            'photographic_inspections' => 'photographicInspections',
            'inspeccion' => 'photographicInspections',
            'inspecciones' => 'photographicInspections',
            'contaminantes_quimicos' => 'contaminantesQuimicosMeasurements',
            'quimicos' => 'contaminantesQuimicosMeasurements',
        ];

        $relation = $relations[$key] ?? null;
        if ($relation && method_exists($module, $relation)) {
            return $module->{$relation}()->with('staff')->get();
        }

        // Búsqueda inteligente por nombre del módulo si la key no coincide
        $nameLower = strtolower($module->name ?? '');
        if (str_contains($nameLower, 'calor') && method_exists($module, 'heatStressMeasurements')) {
            return $module->heatStressMeasurements()->with('staff')->get();
        }
        if (str_contains($nameLower, 'frío') || str_contains($nameLower, 'frio') && method_exists($module, 'coldStressMeasurements')) {
            return $module->coldStressMeasurements()->with('staff')->get();
        }
        if (str_contains($nameLower, 'opacid') && method_exists($module, 'opacityMeasurements')) {
            return $module->opacityMeasurements()->with('staff')->get();
        }
        if ((str_contains($nameLower, 'fotograf') || str_contains($nameLower, 'inspecc')) && method_exists($module, 'photographicInspections')) {
            return $module->photographicInspections()->with('staff')->get();
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
        $title = $item->puesto_trabajo ?? ($item->point_name ?? ($item->tarea_evaluada ?? ($item->equipment_evaluated ?? null)));
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
        } elseif (str_contains($key, 'calor') || str_contains($key, 'heat')) {
            $title = $item->puesto_trabajo ?: ($item->desc_actividades ?: 'Puesto de Trabajo');
            $area = $item->area ?: 'Área Operativa';
            $temp = $item->wbgt_efectivo_c ?? ($item->wbgt_c ?? ($item->temp_c ?? ($item->tgbh_indoor ?? '—')));
            $isCompliant = $item->is_compliant ?? true;
            $badgeLabel = "TGBH: {$temp} °C • " . ($isCompliant ? 'Conforme' : 'No Conforme');
            $badgeColor = $isCompliant ? '#166534' : '#ea580c';
            $badgeBg = $isCompliant ? '#dcfce7' : '#ffedd5';
            $details = 'Régimen: ' . ($item->regimen_trabajo_descanso ?: ($item->work_regime ?: 'Continuo'));
        } elseif (str_contains($key, 'frio') || str_contains($key, 'cold')) {
            $title = $item->puesto_trabajo ?: ($item->desc_actividades ?: 'Puesto de Trabajo');
            $area = $item->area ?: 'Área Operativa';
            $temp = $item->sensacion_termica_c ?? ($item->temp_c ?? ($item->ambient_temp ?? '—'));
            $risk = $item->nivel_riesgo ?: ($item->is_compliant ? 'Bajo' : 'Moderado');
            $isCompliant = $item->is_compliant ?? ($risk === 'Bajo');
            $badgeLabel = "Sensación: {$temp} °C • Riesgo {$risk}";
            $badgeColor = $isCompliant ? '#166534' : '#0369a1';
            $badgeBg = $isCompliant ? '#dcfce7' : '#e0f2fe';
            $details = 'Viento: ' . ($item->vel_viento_ms ? "{$item->vel_viento_ms} m/s" : '—') . ' • Presión: ' . ($item->presion_mmhg ? "{$item->presion_mmhg} mmHg" : '—');
        } elseif (str_contains($key, 'opacid') || str_contains($key, 'opacity')) {
            $vehicleTitle = trim(($item->marca ?? '') . ' ' . ($item->modelo ?? ''));
            $title = $vehicleTitle ?: ($item->tipo_vehiculo ?: ($item->placa ? "Vehículo {$item->placa}" : 'Vehículo Evaluado'));
            $area = $item->area ?: 'Patio de Maniobras / Operaciones';
            $opaVal = $item->opa_promedio !== null ? (float)$item->opa_promedio : ($item->opa_1 ?? null);
            $opaText = $opaVal !== null ? "{$opaVal}% Opacidad" : 'Opacidad';
            $isCompliant = $item->is_compliant ?? true;
            $badgeLabel = "{$opaText} • " . ($isCompliant ? 'Cumple' : 'Excede');
            $badgeColor = $isCompliant ? '#166534' : '#991b1b';
            $badgeBg = $isCompliant ? '#dcfce7' : '#fee2e2';
            $details = 'Placa: ' . ($item->placa ?: 'S/P') . ' • RPM: ' . ($item->rpm_promedio ?: '—');
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
        } elseif (str_contains($key, 'fotograf') || str_contains($key, 'inspecc')) {
            $category = trim((string)($item->category ?? ($item->categoria ?? ($item->observation ?? ($item->observacion ?? '')))));
            if (empty($category)) {
                $category = 'Inspección';
            }

            $title = $category;
            if (!empty($item->puesto_trabajo)) {
                $title = $item->puesto_trabajo;
            } elseif (!empty($item->point_name)) {
                $title = $item->point_name;
            }

            return [
                'title' => $title,
                'area' => $area,
                'category' => $category,
                'badge_label' => '',
                'badge_color' => '',
                'badge_bg' => '',
                'details' => '',
            ];
        } else {
            $badgeLabel = 'Evaluado';
            $badgeColor = '#0284c7'; $badgeBg = '#e0f2fe';
            $details = $item->observaciones ?? ($item->descripcion ?? '');
        }

        if (empty($title)) {
            $title = 'Punto de Monitoreo';
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
            str_contains($key, 'fotograf') || str_contains($key, 'inspecc') => 'INSP',
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
            str_contains($key, 'opacid') || str_contains($key, 'opacity') => 'NORMA AMBIENTAL DE EMISIONES VEHICULARES • MÉTODO DE OPACIDAD RINGELMANN',
            str_contains($key, 'fotograf') || str_contains($key, 'inspecc') => 'INSPECCIÓN TÉCNICA DE SEGURIDAD INDUSTRIAL Y CONDICIONES DE CAMPO',
            default => 'SISTEMA DE GESTIÓN DE SEGURIDAD Y SALUD EN EL TRABAJO — METRIC V2',
        };
    }
}
