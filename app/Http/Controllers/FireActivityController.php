<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\FireActivityMeasurement;
use App\Models\MeasurementModule;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FireActivityController extends Controller
{
    /**
     * Display the Fire Activity (Carga de Fuego por Actividad) monitoring page for a module.
     */
     public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        // Buscar módulo o generar objeto mock si aún no existe en BD
        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->find($moduleId);

        if (!$module) {
            $module = new MeasurementModule();
            $module->id = (int)$moduleId;
            $module->name = 'Carga de Fuego por Actividad';
            $module->key = 'carga_fuego_actividad';
            $module->description = 'Monitoreo y evaluación de carga de fuego ponderada por actividad según NB 58005 / NTP 453.';
            $module->points_total = 8;
            $module->points_completed = 0;
            $module->start_date = Carbon::now()->subDays(2);
            $module->end_date = Carbon::now();
            $module->monitoring_type = 'Monitoreo Inicial Normativo (NB 58005)';
            $module->installation_name = 'Planta Industrial Central — Corporación Minera';
            $module->calibration_equipment = 'Distanciómetro Láser Bosch GLM 50 C - S/N: 20491823';
        }

        // Resuelve información técnica del encabezado
        $projectName = ($module->project) ? $module->project->name : 'Proyecto Industrial Alpha';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : 'Empresa Principal';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : date('Y-m-d', strtotime('-2 days'));
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : date('Y-m-d');
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : date('d/m/Y', strtotime('-2 days'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : date('d/m/Y');

        $monitoringType = $module->monitoring_type ?: 'Carga de Fuego Ponderada por Actividad (NB 58005 / NTP 453)';

        // Equipo asignado (Distanciómetro / Cinta métrica láser)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Distanciómetro Láser');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Bosch') : 'Bosch Professional';
        $equipmentModel = $equipment ? ($equipment->model ?: 'GLM 50-27 CG') : 'GLM 50-27 CG';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'BSH-7749201') : 'BSH-7749201';
        $equipmentImage = ($equipment && $equipment->image) ? asset($equipment->image) : null;

        // Personal asignado para selector y encabezado técnico
        $assignedStaff = method_exists($module, 'getAssignedStaffAttribute') ? $module->getAssignedStaffAttribute() : null;
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Ing. Reynaldo Pachabol';
            $staffList = Staff::orderBy('name')->get();
        }

        // Consultar BD para mediciones reales
        $dbMeasurements = ($module->exists && method_exists($module, 'fireActivityMeasurements'))
            ? $module->fireActivityMeasurements()->with('staff')->get()
            : collect([]);

        if ($dbMeasurements->isNotEmpty()) {
            $measurements = $dbMeasurements->map(function ($item, $index) use ($assignedStaff, $currentUser) {
                $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
                if (empty($rawImages) && !empty($item->image_path)) {
                    $rawImages = [$item->image_path];
                }
                $imagesUrls = array_values(array_filter(array_map(function($p) {
                    return $p ? asset($p) : null;
                }, $rawImages)));

                $dimensions = is_array($item->dimensions) ? $item->dimensions : (json_decode($item->dimensions, true) ?: []);
                $activities = is_array($item->activities) ? $item->activities : (json_decode($item->activities, true) ?: []);
                $fireEquipments = is_array($item->fire_equipments) ? $item->fire_equipments : (json_decode($item->fire_equipments, true) ?: []);

                $registeredBy = $item->registered_by ?: ($item->staff ? ($item->staff->full_name ?: $item->staff->name) : ($currentUser ? $currentUser->name : 'Técnico Evaluador'));

                return [
                    'id' => $item->id,
                    'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    'date' => $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—',
                    'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                    'time' => $item->measurement_time ?: '—',
                    'macroarea' => $item->macroarea,
                    'sector_name' => $item->sector_name,
                    'dimensions' => $dimensions,
                    'activities' => $activities,
                    'fire_equipments' => $fireEquipments,
                    'qs_mj_m2' => (float)$item->qs_mj_m2,
                    'qs_mcal_m2' => (float)$item->qs_mcal_m2,
                    'risk_level' => $item->risk_level ?: 'Bajo',
                    'risk_theme' => $item->risk_theme ?: 'emerald',
                    'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                    'images' => $imagesUrls,
                    'image_paths' => $imagesUrls,
                    'images_count' => count($imagesUrls),
                    'location' => $item->location ?: ($item->utm_zone ? "Zona {$item->utm_zone} E: {$item->utm_easting} N: {$item->utm_northing}" : '—'),
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                    'utm_zone' => $item->utm_zone,
                    'utm_easting' => $item->utm_easting,
                    'utm_northing' => $item->utm_northing,
                    'observations' => $item->observations ?: 'Sin observaciones',
                    'registered_by' => $registeredBy,
                    'staff_id' => $item->staff_id,
                ];
            });
        } else {
            // Mock Measurements representativos de Carga de Fuego por Actividad
            $mockMeasurements = [
                [
                    'id' => 1,
                    'num' => '01',
                    'date' => date('d/m/Y', strtotime('-2 days')),
                    'raw_date' => date('Y-m-d', strtotime('-2 days')),
                    'time' => '08:45:00',
                    'macroarea' => 'Galpón Principal de Producción',
                    'sector_name' => 'Sector A1 - Almacén de Materias Primas',
                    'dimensions' => [
                        'yi_largo' => 24.50,
                        'xi_ancho' => 18.00,
                        'area_m2' => 441.00,
                    ],
                    'activities' => [
                        [
                            'actividad' => 'Algodón, almacén de',
                            'tipo' => 'Almacén',
                            'largo' => 12.00,
                            'ancho' => 8.50,
                            'alto' => 3.80,
                            'qsi' => 800, // MJ/m2
                            'ci' => 1.3,
                            'descripcion' => 'Almacenamiento en pallets de fardos de algodón comprimido'
                        ],
                        [
                            'actividad' => 'Cartonaje, expedición de',
                            'tipo' => 'Almacén',
                            'largo' => 10.00,
                            'ancho' => 6.00,
                            'alto' => 2.50,
                            'qsi' => 400,
                            'ci' => 1.0,
                            'descripcion' => 'Cajas de cartón corrugado y material de embalaje secundario'
                        ]
                    ],
                    'fire_equipments' => [
                        ['tipo' => 'EXTINTOR', 'cantidad' => 4, 'observacion' => 'PQS 6kg Tipo ABC con manómetro en verde', 'fotos' => []],
                        ['tipo' => 'PULSADOR', 'cantidad' => 2, 'observacion' => 'Pulsador manual rearmable con señalética fotoluminiscente', 'fotos' => []],
                        ['tipo' => 'BOCA DE INCENDIO', 'cantidad' => 1, 'observacion' => 'BIE 25mm con manguera semirrígida de 20m', 'fotos' => []],
                    ],
                    'qs_mj_m2' => 685.20,
                    'qs_mcal_m2' => 163.60,
                    'risk_level' => 'Bajo', // Bajo, Medio, Alto
                    'risk_theme' => 'emerald',
                    'image_path' => null,
                    'images' => [],
                    'images_count' => 3,
                    'location' => 'Zona 20K E: 593412.00 N: 8172930.00',
                    'latitude' => -16.52341,
                    'longitude' => -68.12938,
                    'utm_zone' => '20K',
                    'utm_easting' => 593412.00,
                    'utm_northing' => 8172930.00,
                    'observations' => 'Sector con buena ventilación perimetral. Extintores ubicados a 1.20 m de altura reglamentaria con señalética normalizada.',
                    'registered_by' => 'Ing. Carlos Mendoza (Técnico Evaluador)',
                    'staff_id' => 1,
                ],
                [
                    'id' => 2,
                    'num' => '02',
                    'date' => date('d/m/Y', strtotime('-2 days')),
                    'raw_date' => date('Y-m-d', strtotime('-2 days')),
                    'time' => '10:15:00',
                    'macroarea' => 'Galpón Principal de Producción',
                    'sector_name' => 'Sector A2 - Taller de Pintura y Barnizado',
                    'dimensions' => [
                        'yi_largo' => 15.00,
                        'xi_ancho' => 12.00,
                        'area_m2' => 180.00,
                    ],
                    'activities' => [
                        [
                            'actividad' => 'Barnices',
                            'tipo' => 'Producción',
                            'largo' => 8.00,
                            'ancho' => 5.00,
                            'alto' => 2.40,
                            'qsi' => 1600,
                            'ci' => 1.6,
                            'descripcion' => 'Cabina presurizada de aplicación de barnices y esmaltes sintéticos'
                        ],
                        [
                            'actividad' => 'Automóviles, pintura',
                            'tipo' => 'Producción',
                            'largo' => 7.00,
                            'ancho' => 6.00,
                            'alto' => 2.80,
                            'qsi' => 1200,
                            'ci' => 1.3,
                            'descripcion' => 'Zona de secado y mezclado de solventes'
                        ]
                    ],
                    'fire_equipments' => [
                        ['tipo' => 'EXTINTOR', 'cantidad' => 3, 'observacion' => 'Extintores CO2 de 5kg para fuegos clase B y C', 'fotos' => []],
                        ['tipo' => 'PULSADOR', 'cantidad' => 1, 'observacion' => 'Pulsador antiexplosivo para atmósfera con vapores', 'fotos' => []],
                        ['tipo' => 'ALARMA', 'cantidad' => 2, 'observacion' => 'Detectores ópticos de humo y temperatura direccionables', 'fotos' => []],
                    ],
                    'qs_mj_m2' => 1845.50,
                    'qs_mcal_m2' => 440.70,
                    'risk_level' => 'Medio',
                    'risk_theme' => 'amber',
                    'image_path' => null,
                    'images' => [],
                    'images_count' => 2,
                    'location' => 'Zona 20K E: 593440.00 N: 8172960.00',
                    'latitude' => -16.52315,
                    'longitude' => -68.12910,
                    'utm_zone' => '20K',
                    'utm_easting' => 593440.00,
                    'utm_northing' => 8172960.00,
                    'observations' => 'Cabina con sistema de extracción forzada antiexplosiva. Se requiere reforzar extintor de carro rodante de 25kg.',
                    'registered_by' => 'Ing. Carlos Mendoza (Técnico Evaluador)',
                    'staff_id' => 1,
                ]
            ];
            $measurements = collect($mockMeasurements);
        }

        $totalMeasurements = $measurements->count();

        // Configuración guardada para el Reporte Fotográfico
        $savedSettings = $module->photo_report_settings ?: [];
        $photoReportSettings = [
            'grid' => $savedSettings['grid'] ?? '2x3',
            'orientation' => $savedSettings['orientation'] ?? 'landscape',
            'selected_points' => $savedSettings['selected_points'] ?? $measurements->pluck('id')->toArray(),
            'photo_indices' => $savedSettings['photo_indices'] ?? (object)[],
        ];

        return view('measurements.fuego_actividades.index', compact(
            'userName',
            'userRole',
            'module',
            'installationName',
            'startDateRaw',
            'endDateRaw',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'equipmentImage',
            'registeredByHeader',
            'staffList',
            'measurements',
            'totalMeasurements',
            'photoReportSettings'
        ));
    }

    /**
     * Store a newly created measurement point.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $imagePath = null;
        $imagesPaths = [];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('fire_activity_measurements', 'public');
            $imagesPaths[] = $imagePath;
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('fire_activity_measurements', 'public');
                    $imagesPaths[] = $path;
                    if (!$imagePath) $imagePath = $path;
                }
            }
        }

        // Dimensiones
        $yiLargo = (float)($request->input('yi_largo') ?: 0);
        $xiAncho = (float)($request->input('xi_ancho') ?: 0);
        if ($yiLargo > 0 || $xiAncho > 0) {
            $areaM2 = round($yiLargo * $xiAncho, 2);
            $dimensions = [
                'yi_largo' => $yiLargo,
                'xi_ancho' => $xiAncho,
                'area_m2'  => $areaM2,
            ];
        } else {
            $dimInput = $request->input('dimensions');
            $dimensions = is_string($dimInput) ? (json_decode($dimInput, true) ?: []) : (array)($dimInput ?: []);
            $yiLargo = isset($dimensions['yi_largo']) ? (float)$dimensions['yi_largo'] : 4.0;
            $xiAncho = isset($dimensions['xi_ancho']) ? (float)$dimensions['xi_ancho'] : 3.0;
            $areaM2 = isset($dimensions['area_m2']) ? (float)$dimensions['area_m2'] : round($yiLargo * $xiAncho, 2);
            $dimensions['area_m2'] = $areaM2;
        }

        // Actividades Normativas
        $activities = [];
        if ($request->has('activity_names') && is_array($request->input('activity_names'))) {
            $actNames = $request->input('activity_names', []);
            $actTypes = $request->input('activity_types', []);
            $actLargos = $request->input('activity_largos', []);
            $actAnchos = $request->input('activity_anchos', []);
            $actAltos = $request->input('activity_altos', []);
            $actDescs = $request->input('activity_descs', []);

            foreach ($actNames as $idx => $name) {
                if (!empty(trim($name))) {
                    $tipo = $actTypes[$idx] ?? 'Producción';
                    $largo = isset($actLargos[$idx]) && $actLargos[$idx] !== '' ? (float)$actLargos[$idx] : 1.5;
                    $ancho = isset($actAnchos[$idx]) && $actAnchos[$idx] !== '' ? (float)$actAnchos[$idx] : 1.0;
                    $alto = isset($actAltos[$idx]) ? $actAltos[$idx] : '';
                    $desc = $actDescs[$idx] ?? '';
                    
                    $norm = self::lookupNormativeValuesStatic($name, $tipo);
                    
                    $activities[] = [
                        'actividad' => trim($name),
                        'tipo' => $tipo,
                        'largo' => $largo,
                        'ancho' => $ancho,
                        'alto' => $alto,
                        'descripcion' => $desc,
                        'qxi' => $norm['qxi'],
                        'ci' => $norm['ci'],
                        'ra' => $norm['ra'],
                        'qsi_mcal' => $norm['qxi'],
                        'qsi_mj' => round($norm['qxi'] * 4.184, 1),
                    ];
                }
            }
        } elseif ($request->has('activities')) {
            $rawActs = $request->input('activities');
            $activities = is_string($rawActs) ? (json_decode($rawActs, true) ?: []) : (array)($rawActs ?: []);
        }

        // Equipos Contra Incendios
        $fireEquipments = [];
        if ($request->has('equipment_types') && is_array($request->input('equipment_types'))) {
            $eqTypes = $request->input('equipment_types', []);
            $eqQtys = $request->input('equipment_qtys', []);
            $eqObs = $request->input('equipment_obs', []);

            foreach ($eqTypes as $idx => $t) {
                if (!empty(trim($t))) {
                    $fireEquipments[] = [
                        'tipo' => trim($t),
                        'cantidad' => isset($eqQtys[$idx]) && $eqQtys[$idx] !== '' ? (int)$eqQtys[$idx] : 1,
                        'observacion' => $eqObs[$idx] ?? '',
                    ];
                }
            }
        } elseif ($request->has('fire_equipments')) {
            $rawEqs = $request->input('fire_equipments');
            $fireEquipments = is_string($rawEqs) ? (json_decode($rawEqs, true) ?: []) : (array)($rawEqs ?: []);
        }

        // Cálculo de Qs y Nivel de Riesgo
        $sumCalories = 0;
        foreach ($activities as $act) {
            $ai = (float)($act['largo'] ?? 1.5) * (float)($act['ancho'] ?? 1.0);
            $qxi = (float)($act['qxi'] ?? 0);
            $ci = (float)($act['ci'] ?? 1.0);
            $raVal = (isset($act['ra']) && $act['ra'] !== null && $act['ra'] !== '') ? (float)$act['ra'] : ($qxi === 0 ? 0 : 1.0);
            $sumCalories += ($ai * $qxi * $ci * $raVal);
        }

        $qsMcal = ($areaM2 > 0) ? round($sumCalories / $areaM2, 2) : (float)$request->input('qs_mcal_m2', 0);
        $qsMj = round($qsMcal * 4.184, 1);
        $riskLevel = self::calculateRiskLevelStatic($qsMcal);
        $riskTheme = match (strtolower($riskLevel)) {
            'bajo' => 'emerald',
            'medio' => 'amber',
            'alto', 'muy alto' => 'rose',
            default => 'emerald',
        };

        $measurement = new FireActivityMeasurement();
        $measurement->module_id = $module->id;
        $measurement->measurement_module_id = $module->id;
        $measurement->point_number = $request->input('point_number') ?: str_pad($module->fireActivityMeasurements()->count() + 1, 2, '0', STR_PAD_LEFT);
        $measurement->measurement_date = $request->input('measurement_date') ?: Carbon::now()->format('Y-m-d');
        $measurement->measurement_time = $request->input('measurement_time') ?: Carbon::now()->format('H:i:s');
        $measurement->macroarea = $request->input('macroarea', 'PLANTA BAJA');
        $measurement->sector_name = $request->input('sector_name', 'Sector 1');
        $measurement->dimensions = $dimensions;
        $measurement->yi_largo = $yiLargo;
        $measurement->xi_ancho = $xiAncho;
        $measurement->area_m2 = $areaM2;
        $measurement->activities = $activities;
        $measurement->fire_equipments = $fireEquipments;
        $measurement->qs_mj_m2 = $qsMj;
        $measurement->qs_mcal_m2 = $qsMcal;
        $measurement->risk_level = $riskLevel;
        $measurement->risk_color = $riskTheme;
        $measurement->observations = $request->input('observations');
        $measurement->registered_by = Auth::user() ? Auth::user()->name : 'Técnico Evaluador';
        $measurement->staff_id = $request->input('staff_id');

        // Location
        $measurement->latitude = $request->input('latitude');
        $measurement->longitude = $request->input('longitude');
        $measurement->utm_zone = $request->input('utm_zone', '20K');
        $measurement->utm_easting = $request->input('utm_easting');
        $measurement->utm_northing = $request->input('utm_northing');
        $measurement->location = $request->input('location');

        if ($imagePath) {
            $measurement->image_path = $imagePath;
        }
        if (!empty($imagesPaths)) {
            $measurement->images = $imagesPaths;
        }

        $measurement->save();

        $module->points_completed = $module->fireActivityMeasurements()->count();
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sector de Carga de Fuego por Actividad registrado exitosamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.fire_activity', $moduleId)
            ->with('success', 'Sector de Carga de Fuego por Actividad registrado exitosamente.');
    }

    /**
     * Update an existing measurement point.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $measurement = FireActivityMeasurement::find($measurementId);
        if (!$measurement) {
            $measurement = new FireActivityMeasurement();
            $measurement->module_id = $moduleId;
            $measurement->measurement_module_id = $moduleId;
            $measurement->point_number = $request->input('point_number') ?: str_pad($measurementId, 2, '0', STR_PAD_LEFT);
            $measurement->registered_by = Auth::user() ? Auth::user()->name : 'Técnico Evaluador';
        }

        // Dimensiones
        $yiLargo = (float)($request->input('yi_largo') ?: 0);
        $xiAncho = (float)($request->input('xi_ancho') ?: 0);
        if ($yiLargo > 0 || $xiAncho > 0) {
            $areaM2 = round($yiLargo * $xiAncho, 2);
            $dimensions = [
                'yi_largo' => $yiLargo,
                'xi_ancho' => $xiAncho,
                'area_m2'  => $areaM2,
            ];
        } else {
            $dimInput = $request->input('dimensions');
            $dimensions = is_string($dimInput) ? (json_decode($dimInput, true) ?: []) : (array)($dimInput ?: $measurement->dimensions);
            $yiLargo = isset($dimensions['yi_largo']) ? (float)$dimensions['yi_largo'] : ($measurement->yi_largo ?: 4.0);
            $xiAncho = isset($dimensions['xi_ancho']) ? (float)$dimensions['xi_ancho'] : ($measurement->xi_ancho ?: 3.0);
            $areaM2 = isset($dimensions['area_m2']) ? (float)$dimensions['area_m2'] : round($yiLargo * $xiAncho, 2);
            $dimensions['area_m2'] = $areaM2;
        }

        // Actividades Normativas
        $activities = [];
        if ($request->has('activity_names') && is_array($request->input('activity_names'))) {
            $actNames = $request->input('activity_names', []);
            $actTypes = $request->input('activity_types', []);
            $actLargos = $request->input('activity_largos', []);
            $actAnchos = $request->input('activity_anchos', []);
            $actAltos = $request->input('activity_altos', []);
            $actDescs = $request->input('activity_descs', []);

            foreach ($actNames as $idx => $name) {
                if (!empty(trim($name))) {
                    $tipo = $actTypes[$idx] ?? 'Producción';
                    $largo = isset($actLargos[$idx]) && $actLargos[$idx] !== '' ? (float)$actLargos[$idx] : 1.5;
                    $ancho = isset($actAnchos[$idx]) && $actAnchos[$idx] !== '' ? (float)$actAnchos[$idx] : 1.0;
                    $alto = isset($actAltos[$idx]) ? $actAltos[$idx] : '';
                    $desc = $actDescs[$idx] ?? '';
                    
                    $norm = self::lookupNormativeValuesStatic($name, $tipo);
                    
                    $activities[] = [
                        'actividad' => trim($name),
                        'tipo' => $tipo,
                        'largo' => $largo,
                        'ancho' => $ancho,
                        'alto' => $alto,
                        'descripcion' => $desc,
                        'qxi' => $norm['qxi'],
                        'ci' => $norm['ci'],
                        'ra' => $norm['ra'],
                        'qsi_mcal' => $norm['qxi'],
                        'qsi_mj' => round($norm['qxi'] * 4.184, 1),
                    ];
                }
            }
        } elseif ($request->has('activities')) {
            $rawActs = $request->input('activities');
            $activities = is_string($rawActs) ? (json_decode($rawActs, true) ?: []) : (array)($rawActs ?: []);
        } else {
            $activities = is_array($measurement->activities) ? $measurement->activities : [];
        }

        // Equipos Contra Incendios
        $fireEquipments = [];
        if ($request->has('equipment_types') && is_array($request->input('equipment_types'))) {
            $eqTypes = $request->input('equipment_types', []);
            $eqQtys = $request->input('equipment_qtys', []);
            $eqObs = $request->input('equipment_obs', []);

            foreach ($eqTypes as $idx => $t) {
                if (!empty(trim($t))) {
                    $fireEquipments[] = [
                        'tipo' => trim($t),
                        'cantidad' => isset($eqQtys[$idx]) && $eqQtys[$idx] !== '' ? (int)$eqQtys[$idx] : 1,
                        'observacion' => $eqObs[$idx] ?? '',
                    ];
                }
            }
        } elseif ($request->has('fire_equipments')) {
            $rawEqs = $request->input('fire_equipments');
            $fireEquipments = is_string($rawEqs) ? (json_decode($rawEqs, true) ?: []) : (array)($rawEqs ?: []);
        } else {
            $fireEquipments = is_array($measurement->fire_equipments) ? $measurement->fire_equipments : [];
        }

        // Recalcular Qs y Nivel de Riesgo
        $sumCalories = 0;
        foreach ($activities as $act) {
            $ai = (float)($act['largo'] ?? 1.5) * (float)($act['ancho'] ?? 1.0);
            $qxi = (float)($act['qxi'] ?? 0);
            $ci = (float)($act['ci'] ?? 1.0);
            $raVal = (isset($act['ra']) && $act['ra'] !== null && $act['ra'] !== '') ? (float)$act['ra'] : ($qxi === 0 ? 0 : 1.0);
            $sumCalories += ($ai * $qxi * $ci * $raVal);
        }

        $qsMcal = ($areaM2 > 0) ? round($sumCalories / $areaM2, 2) : 0;
        $qsMj = round($qsMcal * 4.184, 1);
        $riskLevel = self::calculateRiskLevelStatic($qsMcal);
        $riskTheme = match (strtolower($riskLevel)) {
            'bajo' => 'emerald',
            'medio' => 'amber',
            'alto', 'muy alto' => 'rose',
            default => 'emerald',
        };

        if ($request->filled('point_number')) $measurement->point_number = $request->input('point_number');
        if ($request->filled('measurement_date')) $measurement->measurement_date = $request->input('measurement_date');
        if ($request->filled('measurement_time')) $measurement->measurement_time = $request->input('measurement_time');
        if ($request->filled('macroarea')) $measurement->macroarea = $request->input('macroarea');
        if ($request->filled('sector_name')) $measurement->sector_name = $request->input('sector_name');
        if ($request->filled('staff_id')) $measurement->staff_id = $request->input('staff_id');
        if ($request->has('observations')) $measurement->observations = $request->input('observations');
        if ($request->has('latitude')) $measurement->latitude = $request->input('latitude');
        if ($request->has('longitude')) $measurement->longitude = $request->input('longitude');
        if ($request->has('utm_zone')) $measurement->utm_zone = $request->input('utm_zone');
        if ($request->has('utm_easting')) $measurement->utm_easting = $request->input('utm_easting');
        if ($request->has('utm_northing')) $measurement->utm_northing = $request->input('utm_northing');
        if ($request->has('location')) $measurement->location = $request->input('location');

        $measurement->dimensions = $dimensions;
        $measurement->yi_largo = $yiLargo;
        $measurement->xi_ancho = $xiAncho;
        $measurement->area_m2 = $areaM2;
        $measurement->activities = $activities;
        $measurement->fire_equipments = $fireEquipments;
        $measurement->qs_mj_m2 = $qsMj;
        $measurement->qs_mcal_m2 = $qsMcal;
        $measurement->risk_level = $riskLevel;
        $measurement->risk_color = $riskTheme;

        // Imágenes
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('fire_activity_measurements', 'public');
            $measurement->image_path = $imagePath;
            $existingImages = is_array($measurement->images) ? $measurement->images : [];
            array_unshift($existingImages, $imagePath);
            $measurement->images = array_values(array_unique($existingImages));
        }

        if ($request->hasFile('images')) {
            $newImages = [];
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('fire_activity_measurements', 'public');
                    $newImages[] = $path;
                    if (!$measurement->image_path) $measurement->image_path = $path;
                }
            }
            if (!empty($newImages)) {
                $existingImages = is_array($measurement->images) ? $measurement->images : [];
                $measurement->images = array_values(array_unique(array_merge($existingImages, $newImages)));
            }
        }

        $measurement->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sector de Carga de Fuego por Actividad actualizado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.fire_activity', $moduleId)
            ->with('success', 'Sector de Carga de Fuego por Actividad actualizado correctamente.');
    }

    /**
     * Destroy a measurement point.
     */
    public function destroyMeasurement(Request $request, $moduleId, $measurementId)
    {
        $measurement = FireActivityMeasurement::find($measurementId);
        if ($measurement) {
            $measurement->delete();
        }

        $module = MeasurementModule::find($moduleId);
        if ($module && method_exists($module, 'fireActivityMeasurements')) {
            $module->points_completed = $module->fireActivityMeasurements()->count();
            $module->save();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sector eliminado correctamente.'
            ]);
        }

        return redirect()->route('modules.fire_activity', $moduleId)
            ->with('success', 'Sector eliminado correctamente.');
    }

    /**
     * Update technical header fields via AJAX
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::find($moduleId);
        if ($module) {
            if ($request->has('installation_name')) {
                $module->installation_name = $request->input('installation_name');
            }
            if ($request->has('start_date')) {
                $module->start_date = $request->input('start_date');
            }
            if ($request->has('end_date')) {
                $module->end_date = $request->input('end_date');
            }
            if ($request->has('monitoring_type')) {
                $module->monitoring_type = $request->input('monitoring_type');
            }
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Encabezado técnico actualizado correctamente.'
        ]);
    }

    /**
     * Show the Fire Activity Technical Report (Informe) with 3 Steps:
     * Step 1: Matriz de Carga de Fuego por Actividad (Sectores y Actividades)
     * Step 2: Resumen de Sectores y Macro Área (Hoja Carta Horizontal con 2 tablas)
     * Step 3: Dotación y Asignación de Extintores (Hoja Carta Horizontal con Word export)
     */
    public function showReport(Request $request, $moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->find($moduleId);

        if (!$module) {
            $module = new MeasurementModule();
            $module->id = (int)$moduleId;
            $module->name = 'Carga de Fuego por Actividad';
            $module->key = 'carga_fuego_actividad';
            $module->description = 'Monitoreo y evaluación de carga de fuego ponderada por actividad según NB 58005 / NTP 453.';
            $module->points_total = 8;
            $module->points_completed = 0;
            $module->start_date = Carbon::now()->subDays(2);
            $module->end_date = Carbon::now();
            $module->monitoring_type = 'Monitoreo Inicial Normativo (NB 58005)';
            $module->installation_name = 'Planta Industrial Central — Corporación Minera';
            $module->calibration_equipment = 'Distanciómetro Láser Bosch GLM 50 C - S/N: 20491823';
        }

        $projectName = ($module->project) ? $module->project->name : 'Proyecto Industrial Alpha';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : 'Empresa Principal';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : date('Y-m-d', strtotime('-2 days'));
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : date('Y-m-d');
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : date('d/m/Y', strtotime('-2 days'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : date('d/m/Y');

        $monitoringType = $module->monitoring_type ?: 'Carga de Fuego Ponderada por Actividad (NB 58005 / NTP 453)';

        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Distanciómetro Láser');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Bosch') : 'Bosch Professional';
        $equipmentModel = $equipment ? ($equipment->model ?: 'GLM 50-27 CG') : 'GLM 50-27 CG';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'BSH-7749201') : 'BSH-7749201';

        $assignedStaff = method_exists($module, 'getAssignedStaffAttribute') ? $module->getAssignedStaffAttribute() : null;
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Ing. Reynaldo Pachabol';
        }

        // Consultar BD para todas las mediciones/sectores del módulo
        $measurementId = $request->query('measurement_id') ?: $request->query('point');
        $allMeasurements = ($module->exists && method_exists($module, 'fireActivityMeasurements'))
            ? $module->fireActivityMeasurements()->with('staff')->get()
            : collect([]);

        $selectedMeasurements = $allMeasurements;
        if (!empty($measurementId)) {
            $found = $allMeasurements->firstWhere('id', (int)$measurementId) 
                  ?: $allMeasurements->firstWhere('id', $measurementId) 
                  ?: $allMeasurements->firstWhere('point_number', $measurementId);
            if ($found && !empty($found->macroarea)) {
                $macroAreaName = $found->macroarea;
            }
        }

        // Construir siempre el snapshot fresco de registros desde la BD
        $dbSectors = [];
        $dbEquipments = [];
        $macroAreaName = $macroAreaName ?? ($module->installation_name ?: 'PLANTA BAJA');
        $primaryLt = '';
        $primaryAt = '';

        if ($selectedMeasurements->isNotEmpty()) {
            foreach ($selectedMeasurements as $index => $item) {
                if (!empty($item->macroarea)) {
                    $macroAreaName = $item->macroarea;
                }

                $dim = is_array($item->dimensions) ? $item->dimensions : (json_decode($item->dimensions, true) ?: []);
                $lt = isset($dim['yi_largo']) ? (float)$dim['yi_largo'] : ($item->yi_largo ?: (isset($dim['lt']) ? (float)$dim['lt'] : 4.0));
                $at = isset($dim['xi_ancho']) ? (float)$dim['xi_ancho'] : ($item->xi_ancho ?: (isset($dim['at']) ? (float)$dim['at'] : 3.0));
                $area = isset($dim['area_m2']) ? (float)$dim['area_m2'] : ($item->area_m2 ?: round($lt * $at, 2));

                if ($index === 0) {
                    $primaryLt = $lt;
                    $primaryAt = $at;
                }

                $rawActs = is_array($item->activities) ? $item->activities : (json_decode($item->activities, true) ?: []);
                $actItems = [];

                if (empty($rawActs)) {
                    $defaultAct = 'Oficinas comerciales';
                    $defaultTipo = 'Producción';
                    $normVals = self::lookupNormativeValuesStatic($defaultAct, $defaultTipo);
                    $yi = $lt > 0 ? round($lt / 2, 2) : 1.5;
                    $xi = $at > 0 ? round($at / 2, 2) : 1.0;
                    $ai = round($yi * $xi, 2);
                    $qxi = $normVals['qxi'];
                    $ci = $normVals['ci'];
                    $ra = $normVals['ra'];
                    $mult1 = $qxi * $ai * $ci;
                    $aux2 = ($ra !== null && (float)$ra > 0) ? ($mult1 * (float)$ra * $ai) : 0.0;

                    $actItems[] = [
                        'actividad' => $defaultAct,
                        'tipo' => $defaultTipo,
                        'descripcion' => 'Escritorio / Puesto de trabajo',
                        'yi' => $yi,
                        'xi' => $xi,
                        'hi' => '',
                        'qxi' => $qxi,
                        'ai' => $ai,
                        'ci' => $ci,
                        'ra' => $ra,
                        'mult_1' => round($mult1, 2),
                        'aux_2' => round($aux2, 2),
                    ];
                } else {
                    foreach ($rawActs as $a) {
                        $tipo = !empty($a['tipo']) ? $a['tipo'] : 'Producción';
                        $act = !empty($a['actividad']) ? $a['actividad'] : 'Oficinas comerciales';
                        $normVals = self::lookupNormativeValuesStatic($act, $tipo);

                        $yi = isset($a['largo']) ? (float)$a['largo'] : (isset($a['yi']) ? (float)$a['yi'] : 1.5);
                        $xi = isset($a['ancho']) ? (float)$a['ancho'] : (isset($a['xi']) ? (float)$a['xi'] : 1.0);
                        $hi = isset($a['alto']) ? $a['alto'] : (isset($a['hi']) ? $a['hi'] : '');
                        
                        $qxi = $normVals['qxi'];
                        $ci = $normVals['ci'];
                        $ra = $normVals['ra'];
                        $ai = round($yi * $xi, 2);

                        $isProd = stripos($tipo, 'prod') !== false;
                        $hiVal = ($hi !== '' && $hi !== null && is_numeric($hi) && (float)$hi > 0) ? (float)$hi : 1.0;
                        $mult1 = $isProd ? ($qxi * $ai * $ci) : ($qxi * $ai * $hiVal * $ci);

                        $hasRa = ($ra !== null && $ra !== '' && is_numeric($ra));
                        $raNum = $hasRa ? (float)$ra : null;
                        $aux2 = ($hasRa && $mult1 > 0) ? ($mult1 * $raNum * $ai) : 0.0;

                        $actItems[] = [
                            'actividad' => $act,
                            'tipo' => $tipo,
                            'descripcion' => $a['descripcion'] ?? 'Mobiliario y materiales',
                            'yi' => $yi,
                            'xi' => $xi,
                            'hi' => $hi,
                            'qxi' => $qxi,
                            'ai' => $ai,
                            'ci' => $ci,
                            'ra' => $ra,
                            'mult_1' => round($mult1, 2),
                            'aux_2' => round($aux2, 2),
                        ];
                    }
                }

                // Equipos contra incendios registrados en este sector: UBICACIÓN | TIPO | CANTIDAD | OBS.
                $rawEqs = is_array($item->fire_equipments) ? $item->fire_equipments : (json_decode($item->fire_equipments, true) ?: []);
                foreach ($rawEqs as $eq) {
                    $dbEquipments[] = [
                        'ubicacion' => $item->sector_name ?: ('Sector ' . ($index + 1)),
                        'tipo' => $eq['tipo'] ?? 'EXTINTOR',
                        'cantidad' => $eq['cantidad'] ?? ($eq['cant'] ?? 1),
                        'obs' => $eq['observacion'] ?? ($eq['obs'] ?? 'En servicio'),
                    ];
                }

                // Calcular Qp = SUM(Aux_2) / (Lt * At * Lt * At) = SUM / (Area * Area)
                $sumAux2 = 0;
                foreach ($actItems as $actIt) {
                    $sumAux2 += ($actIt['aux_2'] ?? 0);
                }

                $denominator = ($lt > 0 && $at > 0) ? ($lt * $at * $lt * $at) : ($area * $area);
                $qp = ($denominator > 0) ? round($sumAux2 / $denominator, 2) : 0.0;
                $riskLevel = self::calculateRiskLevelStatic($qp);
                $levelNum = self::calculateRiskLevelNumStatic($qp);
                $at_x_qp = round($area * $qp, 2);

                $dbSectors[] = [
                    'id' => $item->id,
                    'name' => $item->sector_name ?: ('SECTOR ' . ($index + 1)),
                    'lt' => $lt,
                    'at' => $at,
                    'area_m2' => $area,
                    'qp' => $qp,
                    'at_x_qp' => $at_x_qp,
                    'risk_level' => $riskLevel,
                    'level_num' => $levelNum,
                    'items' => $actItems,
                ];
            }
        }

        // Siempre armar el reporte dinámico en base a la BD
        if (!empty($dbSectors)) {
            $totalArea = array_sum(array_column($dbSectors, 'area_m2'));
            $sumATxQP = array_sum(array_column($dbSectors, 'at_x_qp'));
            $qm = ($totalArea > 0) ? round($sumATxQP / $totalArea, 2) : 0;
            $macroRisk = self::calculateRiskLevelStatic($qm);
            $macroLevel = self::calculateRiskLevelNumStatic($qm);

            $extCalc = self::calculateExtinguisherStatic('50Kg ABC', (float)$totalArea, $macroRisk, count($dbEquipments));

            $reportData = [
                'macroarea' => $macroAreaName,
                'dimensions' => [
                    'lt' => $primaryLt,
                    'at' => $primaryAt,
                ],
                'sectors' => $dbSectors,
                'fire_equipments' => $dbEquipments,
                'macro_summary' => [
                    'macroarea' => $macroAreaName,
                    'total_area' => round($totalArea, 2),
                    'qm' => $qm,
                    'risk_level' => $macroRisk,
                    'level_num' => $macroLevel
                ],
                'extinguishers' => array_merge([
                    'macroarea' => $macroAreaName,
                    'area_total' => round($totalArea, 2),
                    'qp_macro' => $qm,
                    'risk_level' => $macroRisk,
                ], $extCalc)
            ];
        } else {
            // Mock predeterminado si no hay ningún registro en BD
            $reportData = [
                'macroarea' => 'PLANTA BAJA',
                'dimensions' => [
                    'lt' => 6.2,
                    'at' => 3.0,
                ],
                'fire_equipments' => [
                    ['ubicacion' => 'OFICINA 1', 'tipo' => 'EXTINTOR', 'cantidad' => 1, 'obs' => 'Manómetro en verde'],
                ],
                'sectors' => [
                    [
                        'id' => 1,
                        'name' => 'OFICINA 1',
                        'lt' => 6.2,
                        'at' => 3.0,
                        'area_m2' => 18.60,
                        'qp' => 1.25,
                        'risk_level' => 'BAJO',
                        'level_num' => 1,
                        'items' => [
                            ['actividad' => 'Orfebrería', 'tipo' => 'Almacén', 'descripcion' => 'Decoración', 'yi' => 2.0, 'xi' => 1.0, 'hi' => 0.5, 'qxi' => 0, 'ai' => 2.00, 'ci' => 1.0, 'ra' => null],
                            ['actividad' => 'Oficinas comerciales', 'tipo' => 'Producción', 'descripcion' => 'Escritorio', 'yi' => 1.5, 'xi' => 1.0, 'hi' => '', 'qxi' => 192, 'ai' => 1.50, 'ci' => 1.0, 'ra' => 1.5],
                        ]
                    ]
                ],
                'macro_summary' => [
                    'macroarea' => 'PLANTA BAJA',
                    'total_area' => 18.60,
                    'qm' => 1.25,
                    'risk_level' => 'BAJO',
                    'level_num' => 1
                ],
                'extinguishers' => [
                    'macroarea' => 'PLANTA BAJA',
                    'area_total' => 18.60,
                    'qp_macro' => 1.25,
                    'risk_level' => 'BAJO',
                    'extinguisher_type' => '50Kg ABC',
                    'potential_a' => '40A',
                    'potential_bc' => '160B',
                    'covered_area_a' => 1050.00,
                    'covered_area_b' => 673.50,
                    'num_ext_a' => 0.02,
                    'num_ext_b' => 0.03,
                    'theoretical_qty' => 1,
                    'final_assigned_qty' => 1,
                    'distance_b' => 15.25,
                    'distance_a' => 25.00,
                    'distance_ab' => 15.25
                ]
            ];
        }

        return view('measurements.fuego_actividades.report', compact(
            'module',
            'installationName',
            'projectName',
            'companyName',
            'startDateRaw',
            'endDateRaw',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'registeredByHeader',
            'reportData',
            'dbSectors',
            'dbEquipments',
            'userName',
            'userRole'
        ));
    }

    /**
     * Save the report state or synchronize modified sector values.
     */
    public function saveReportData(Request $request, $moduleId)
    {
        $reportData = $request->input('report_data');
        if (is_string($reportData)) {
            $reportData = json_decode($reportData, true);
        }

        $module = MeasurementModule::find($moduleId);
        if ($module) {
            if ($request->has('installation_name')) {
                $module->installation_name = $request->input('installation_name');
            }
            if ($request->has('start_date')) {
                $module->start_date = $request->input('start_date') ?: null;
            }
            if ($request->has('end_date')) {
                $module->end_date = $request->input('end_date') ?: null;
            }
            if ($request->has('monitoring_type')) {
                $module->monitoring_type = $request->input('monitoring_type');
            }

            if (is_array($reportData)) {
                $existing = $module->anexo2_data ?: [];
                $existing['fire_activity_report'] = $reportData;
                $module->anexo2_data = $existing;
            }

            $module->save();

            if (is_array($reportData) && !empty($reportData['sectors']) && is_array($reportData['sectors'])) {
                foreach ($reportData['sectors'] as $sec) {
                    if (!empty($sec['id'])) {
                        $measurement = FireActivityMeasurement::find($sec['id']);
                        if ($measurement) {
                            if (!empty($sec['name'])) {
                                $measurement->sector_name = $sec['name'];
                            }
                            if (isset($sec['items']) && is_array($sec['items'])) {
                                $actsToSave = [];
                                foreach ($sec['items'] as $it) {
                                    $actsToSave[] = [
                                        'actividad' => $it['actividad'] ?? '',
                                        'tipo' => $it['tipo'] ?? 'Producción',
                                        'largo' => $it['yi'] ?? 0,
                                        'ancho' => $it['xi'] ?? 0,
                                        'alto' => $it['hi'] ?? '',
                                        'descripcion' => $it['descripcion'] ?? '',
                                        'qxi' => $it['qxi'] ?? 0,
                                        'ci' => $it['ci'] ?? 1.0,
                                        'ra' => $it['ra'] ?? null,
                                    ];
                                }
                                $measurement->activities = $actsToSave;
                            }
                            if (isset($sec['qp'])) {
                                $measurement->qs_mcal_m2 = (float)$sec['qp'];
                                $measurement->qs_mj_m2 = round((float)$sec['qp'] * 4.184, 2);
                            }
                            if (isset($sec['risk_level'])) {
                                $measurement->risk_level = $sec['risk_level'];
                            }
                            $measurement->save();
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Informe técnico guardado y sincronizado correctamente.'
        ]);
    }

    /**
     * Helper PHP para resolver qxi, ci y ra según las fórmulas de Excel:
     * - qxi: =SI(D10="Producción"; BUSCARV(C10; extra!$A$2:$K$571; 3; 0); BUSCARV(C10; extra!$A$2:$K$571; 7; 0))
     * - ci:  =BUSCARV(C10; extra!$A$2:$K$571; 10; 0)
     * - ra:  =SI(D10="Producción"; BUSCARV(C10; extra!$A$2:$K$571; 5; 0); BUSCARV(C10; extra!$A$2:$K$571; 9; 0))
     */
    public static function lookupNormativeValuesStatic(?string $actividad, ?string $tipo): array
    {
        if (empty($actividad)) {
            return ['qxi' => 0.0, 'ci' => 1.0, 'ra' => null];
        }

        $catalog = [
            'abonos quimicos' => ['qxi_prod' => 48, 'ra_prod' => 1.5, 'qxi_alm' => 48, 'ra_alm' => 1.0, 'ci' => 1.0],
            'aceites comestibles, expedicion' => ['qxi_prod' => 215, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'aceites comestibles' => ['qxi_prod' => 240, 'ra_prod' => 3.0, 'qxi_alm' => 4520, 'ra_alm' => 3.0, 'ci' => 1.2],
            'aceites: mineral, vegetal y animal' => ['qxi_prod' => 0, 'ra_prod' => null, 'qxi_alm' => 4520, 'ra_alm' => 3.0, 'ci' => 1.2],
            'acero' => ['qxi_prod' => 10, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'acetileno' => ['qxi_prod' => 168, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.6],
            'acido carbonico' => ['qxi_prod' => 10, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'acidos inorganicos' => ['qxi_prod' => 20, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'acumuladores' => ['qxi_prod' => 96, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.0],
            'algodon en rama' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 264, 'ra_alm' => 3.0, 'ci' => 1.2],
            'algodon, almacen de' => ['qxi_prod' => 0, 'ra_prod' => null, 'qxi_alm' => 311, 'ra_alm' => 3.0, 'ci' => 1.2],
            'algodon' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 311, 'ra_alm' => 3.0, 'ci' => 1.2],
            'alimentacion, embalaje' => ['qxi_prod' => 192, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.2],
            'alimentacion, expedicion' => ['qxi_prod' => 240, 'ra_prod' => 3.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'alimentacion, platos precocinados' => ['qxi_prod' => 48, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'almacenes de talleres' => ['qxi_prod' => 287, 'ra_prod' => 3.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'almidon' => ['qxi_prod' => 480, 'ra_prod' => 3.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.6],
            'alquitran' => ['qxi_prod' => 0, 'ra_prod' => null, 'qxi_alm' => 814, 'ra_alm' => 3.0, 'ci' => 1.2],
            'aluminio' => ['qxi_prod' => 48, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'aparatos de radio' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 48, 'ra_alm' => 1.0, 'ci' => 1.2],
            'aparatos de television' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 48, 'ra_alm' => 1.0, 'ci' => 1.2],
            'aparatos electricos' => ['qxi_prod' => 96, 'ra_prod' => 1.0, 'qxi_alm' => 96, 'ra_alm' => 1.0, 'ci' => 1.2],
            'aparatos electronicos' => ['qxi_prod' => 96, 'ra_prod' => 1.0, 'qxi_alm' => 96, 'ra_alm' => 1.0, 'ci' => 1.2],
            'archivos' => ['qxi_prod' => 1005, 'ra_prod' => 3.0, 'qxi_alm' => 407, 'ra_alm' => 3.0, 'ci' => 1.2],
            'automoviles, garajes y aparcamientos' => ['qxi_prod' => 48, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'automoviles, pintura' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.6],
            'automoviles, reparacion' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'azucar' => ['qxi_prod' => 96, 'ra_prod' => 1.5, 'qxi_alm' => 2010, 'ra_alm' => 3.0, 'ci' => 1.6],
            'barnices' => ['qxi_prod' => 1197, 'ra_prod' => 3.0, 'qxi_alm' => 598, 'ra_alm' => 3.0, 'ci' => 1.6],
            'bebidas alcoholicas' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.6],
            'bebidas sin alcohol' => ['qxi_prod' => 20, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.6],
            'bibliotecas' => ['qxi_prod' => 479, 'ra_prod' => 3.0, 'qxi_alm' => 479, 'ra_alm' => 3.0, 'ci' => 1.2],
            'cables' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 144, 'ra_alm' => 1.5, 'ci' => 1.0],
            'calzado' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 96, 'ra_alm' => 1.0, 'ci' => 1.2],
            'carton' => ['qxi_prod' => 72, 'ra_prod' => 1.5, 'qxi_alm' => 1005, 'ra_alm' => 1.5, 'ci' => 1.2],
            'carton ondulado' => ['qxi_prod' => 192, 'ra_prod' => 3.0, 'qxi_alm' => 311, 'ra_alm' => 3.0, 'ci' => 1.2],
            'cartonaje, expedicion de' => ['qxi_prod' => 144, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'cartonaje' => ['qxi_prod' => 192, 'ra_prod' => 1.5, 'qxi_alm' => 598, 'ra_alm' => 3.0, 'ci' => 1.2],
            'caucho' => ['qxi_prod' => 144, 'ra_prod' => 1.5, 'qxi_alm' => 6843, 'ra_alm' => 3.0, 'ci' => 1.2],
            'cemento' => ['qxi_prod' => 10, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'cera' => ['qxi_prod' => 311, 'ra_prod' => 3.0, 'qxi_alm' => 814, 'ra_alm' => 3.0, 'ci' => 1.0],
            'cervecerias' => ['qxi_prod' => 20, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'chocolate' => ['qxi_prod' => 96, 'ra_prod' => 1.5, 'qxi_alm' => 814, 'ra_alm' => 1.5, 'ci' => 1.2],
            'colchones' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 1197, 'ra_alm' => 3.0, 'ci' => 1.2],
            'combustibles liquidos' => ['qxi_prod' => 860, 'ra_prod' => 3.0, 'qxi_alm' => 860, 'ra_alm' => 3.0, 'ci' => 1.6],
            'cuero sintetico' => ['qxi_prod' => 240, 'ra_prod' => 1.5, 'qxi_alm' => 407, 'ra_alm' => 1.5, 'ci' => 1.2],
            'cuero' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 407, 'ra_alm' => 1.5, 'ci' => 1.2],
            'deposito' => ['qxi_prod' => 192, 'ra_prod' => 1.5, 'qxi_alm' => 264, 'ra_alm' => 3.0, 'ci' => 1.2],
            'farmacias' => ['qxi_prod' => 192, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'fundicion de metales' => ['qxi_prod' => 10, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'gasolineras' => ['qxi_prod' => 0, 'ra_prod' => null, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.6],
            'granos' => ['qxi_prod' => 144, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.6],
            'grasas' => ['qxi_prod' => 240, 'ra_prod' => 3.0, 'qxi_alm' => 4307, 'ra_alm' => 3.0, 'ci' => 1.2],
            'harina en sacos' => ['qxi_prod' => 479, 'ra_prod' => 3.0, 'qxi_alm' => 2010, 'ra_alm' => 3.0, 'ci' => 1.6],
            'harina' => ['qxi_prod' => 407, 'ra_prod' => 3.0, 'qxi_alm' => 3110, 'ra_alm' => 3.0, 'ci' => 1.6],
            'hospitales' => ['qxi_prod' => 72, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'hoteles' => ['qxi_prod' => 72, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'imprentas' => ['qxi_prod' => 96, 'ra_prod' => 1.5, 'qxi_alm' => 1914, 'ra_alm' => 3.0, 'ci' => 1.2],
            'juguetes' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.2],
            'laboratorios quimicos' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'librerias' => ['qxi_prod' => 240, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.2],
            'licores' => ['qxi_prod' => 96, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.6],
            'madera' => ['qxi_prod' => 192, 'ra_prod' => 1.5, 'qxi_alm' => 1005, 'ra_alm' => 3.0, 'ci' => 1.2],
            'muebles de madera' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.2],
            'muebles' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 192, 'ra_alm' => 1.5, 'ci' => 1.2],
            'neumaticos' => ['qxi_prod' => 168, 'ra_prod' => 1.5, 'qxi_alm' => 430, 'ra_alm' => 3.0, 'ci' => 1.2],
            'oficinas comerciales' => ['qxi_prod' => 192, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'oficinas tecnicas' => ['qxi_prod' => 144, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'oficinas postales' => ['qxi_prod' => 96, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'orfebreria' => ['qxi_prod' => 48, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'panaderias' => ['qxi_prod' => 240, 'ra_prod' => 1.5, 'qxi_alm' => 72, 'ra_alm' => 1.0, 'ci' => 1.0],
            'papel' => ['qxi_prod' => 48, 'ra_prod' => 1.0, 'qxi_alm' => 2390, 'ra_alm' => 3.0, 'ci' => 1.2],
            'papeleria' => ['qxi_prod' => 192, 'ra_prod' => 1.5, 'qxi_alm' => 264, 'ra_alm' => 3.0, 'ci' => 1.2],
            'plasticos' => ['qxi_prod' => 480, 'ra_prod' => 3.0, 'qxi_alm' => 1412, 'ra_alm' => 3.0, 'ci' => 1.2],
            'quimicos industriales' => ['qxi_prod' => 72, 'ra_prod' => 3.0, 'qxi_alm' => 240, 'ra_alm' => 3.0, 'ci' => 1.0],
            'restaurantes' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'talleres mecanicos' => ['qxi_prod' => 48, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'talleres de pintura' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.6],
            'teatros' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 264, 'ra_alm' => 3.0, 'ci' => 1.0],
            'textiles y confeccion' => ['qxi_prod' => 72, 'ra_prod' => 1.0, 'qxi_alm' => 240, 'ra_alm' => 3.0, 'ci' => 1.2],
            'textiles' => ['qxi_prod' => 120, 'ra_prod' => 1.5, 'qxi_alm' => 240, 'ra_alm' => 3.0, 'ci' => 1.2],
            'vidrieria y ceramica' => ['qxi_prod' => 20, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
            'vidrio' => ['qxi_prod' => 20, 'ra_prod' => 1.0, 'qxi_alm' => 0, 'ra_alm' => null, 'ci' => 1.0],
        ];

        $cleanAct = strtolower(trim($actividad));
        $cleanAct = iconv('UTF-8', 'ASCII//TRANSLIT', $cleanAct) ?: $cleanAct;
        $isProd = stripos($tipo ?: 'Producción', 'prod') !== false;

        foreach ($catalog as $key => $values) {
            if (stripos($cleanAct, $key) !== false || stripos($key, $cleanAct) !== false) {
                $rawQxi = $isProd ? $values['qxi_prod'] : $values['qxi_alm'];
                $rawRa = $isProd ? $values['ra_prod'] : $values['ra_alm'];
                return [
                    'qxi' => (float)($rawQxi !== null ? $rawQxi : 0),
                    'ci'  => (float)$values['ci'],
                    'ra'  => $rawRa !== null ? (float)$rawRa : null,
                ];
            }
        }

        return [
            'qxi' => (float)($isProd ? 192 : 0),
            'ci'  => 1.0,
            'ra'  => $isProd ? 1.5 : null,
        ];
    }

    public static function lookupNormativeQxiStatic(?string $actividad, ?string $tipo): float
    {
        $res = self::lookupNormativeValuesStatic($actividad, $tipo);
        return $res['qxi'];
    }

    /**
     * Fórmula: =SI(P10<=200;"BAJO";SI(P10<=800;"MEDIO";"ALTO"))
     */
    public static function calculateRiskLevelStatic(float $qp): string
    {
        if ($qp <= 200) return 'BAJO';
        if ($qp <= 800) return 'MEDIO';
        return 'ALTO';
    }

    /**
     * Fórmula: =+SI(P10<=100;"1";SI(P10<=200;"2";SI(P10<=300;"3";SI(P10<=400;"4";SI(P10<=800;"5";SI(P10<=1600;"6";SI(P10<=3200;"7";"8")))))))
     */
    public static function calculateRiskLevelNumStatic(float $qp): int
    {
        if ($qp <= 100) return 1;
        if ($qp <= 200) return 2;
        if ($qp <= 300) return 3;
        if ($qp <= 400) return 4;
        if ($qp <= 800) return 5;
        if ($qp <= 1600) return 6;
        if ($qp <= 3200) return 7;
        return 8;
    }

    public static function calculateExtinguisherStatic(string $extType, float $totalArea, string $riskLevel, int $equipmentCount = 0): array
    {
        $types = [
            '4,5Kg ABC' => ['pot_a' => '4A', 'pot_b' => '60B'],
            '9Kg ABC'   => ['pot_a' => '10A', 'pot_b' => '80B'],
            '50Kg ABC'  => ['pot_a' => '40A', 'pot_b' => '160B'],
            '4,5Kg BC'  => ['pot_a' => '', 'pot_b' => '10B'],
            '7Kg BC'    => ['pot_a' => '', 'pot_b' => '20B'],
            '9Kg BC'    => ['pot_a' => '', 'pot_b' => '20B'],
            '6L H2O'    => ['pot_a' => '1A', 'pot_b' => ''],
            '9,5L H2O'  => ['pot_a' => '2A', 'pot_b' => ''],
        ];

        $covATable = [
            '1A'  => ['dist' => 23, 'BAJO' => 280,  'MEDIO' => 0,    'ALTO' => 0],
            '2A'  => ['dist' => 23, 'BAJO' => 560,  'MEDIO' => 280,  'ALTO' => 186],
            '3A'  => ['dist' => 23, 'BAJO' => 840,  'MEDIO' => 420,  'ALTO' => 280],
            '4A'  => ['dist' => 23, 'BAJO' => 1050, 'MEDIO' => 560,  'ALTO' => 370],
            '6A'  => ['dist' => 23, 'BAJO' => 1050, 'MEDIO' => 840,  'ALTO' => 560],
            '10A' => ['dist' => 23, 'BAJO' => 1050, 'MEDIO' => 1050, 'ALTO' => 840],
            '20A' => ['dist' => 23, 'BAJO' => 1050, 'MEDIO' => 1050, 'ALTO' => 1050],
            '40A' => ['dist' => 23, 'BAJO' => 1050, 'MEDIO' => 1050, 'ALTO' => 1050],
        ];

        $covBTable = [
            '5B'   => ['BAJO' => 242.5, 'MEDIO' => 0,     'ALTO' => 0,      'dist_BAJO' => 9.15,  'dist_MEDIO' => 5.49,  'dist_ALTO' => 1.98],
            '10B'  => ['BAJO' => 673.5, 'MEDIO' => 242.5, 'ALTO' => 0,      'dist_BAJO' => 15.25, 'dist_MEDIO' => 9.15,  'dist_ALTO' => 3.29],
            '20B'  => ['BAJO' => 673.5, 'MEDIO' => 673.5, 'ALTO' => 0,      'dist_BAJO' => 15.25, 'dist_MEDIO' => 15.25, 'dist_ALTO' => 5.49],
            '40B'  => ['BAJO' => 673.5, 'MEDIO' => 673.5, 'ALTO' => 242.5,  'dist_BAJO' => 15.25, 'dist_MEDIO' => 15.25, 'dist_ALTO' => 9.15],
            '60B'  => ['BAJO' => 673.5, 'MEDIO' => 673.5, 'ALTO' => 431.1,  'dist_BAJO' => 15.25, 'dist_MEDIO' => 15.25, 'dist_ALTO' => 12.2],
            '80B'  => ['BAJO' => 673.5, 'MEDIO' => 673.5, 'ALTO' => 673.5,  'dist_BAJO' => 15.25, 'dist_MEDIO' => 15.25, 'dist_ALTO' => 15.25],
            '160B' => ['BAJO' => 673.5, 'MEDIO' => 673.5, 'ALTO' => 2182.3, 'dist_BAJO' => 15.25, 'dist_MEDIO' => 15.25, 'dist_ALTO' => 27.45],
        ];

        $riskKey = stripos($riskLevel, 'ALTO') !== false ? 'ALTO' : (stripos($riskLevel, 'MEDIO') !== false ? 'MEDIO' : 'BAJO');
        $typeData = $types[$extType] ?? $types['50Kg ABC'];
        $potA = $typeData['pot_a'];
        $potB = $typeData['pot_b'];

        $covA = 0.0;
        $distA = 23.0;
        if (!empty($potA) && isset($covATable[$potA])) {
            $covA = (float)($covATable[$potA][$riskKey] ?? 0.0);
            $distA = (float)($covATable[$potA]['dist'] ?? 23.0);
        }

        $covB = 0.0;
        $distB = 15.25;
        if (!empty($potB) && isset($covBTable[$potB])) {
            $covB = (float)($covBTable[$potB][$riskKey] ?? 0.0);
            $distKey = 'dist_' . $riskKey;
            $distB = (float)($covBTable[$potB][$distKey] ?? 15.25);
        }

        $distAB = ($distB > 0 && $distB <= $distA) ? $distB : 15.25;
        $numExtA = ($covA > 0) ? round($totalArea / $covA, 2) : 0.0;
        $numExtB = ($covB > 0) ? round($totalArea / $covB, 2) : 0.0;
        $maxExt = max($numExtA, $numExtB);
        $theorQty = ($totalArea > 0 && $maxExt > 0) ? (int)ceil($maxExt) : 1;
        $finalQty = max(0, $equipmentCount);

        return [
            'extinguisher_type' => $extType,
            'potential_a' => $potA,
            'potential_bc' => $potB,
            'covered_area_a' => $covA,
            'covered_area_b' => $covB,
            'num_ext_a' => $numExtA,
            'num_ext_b' => $numExtB,
            'theoretical_qty' => $theorQty,
            'final_assigned_qty' => $finalQty,
            'distance_b' => $distB,
            'distance_a' => $distA,
            'distance_ab' => $distAB,
        ];
    }
}
