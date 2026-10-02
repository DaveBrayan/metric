<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\FireWeightMeasurement;
use App\Models\FireWeightReport;
use App\Models\MeasurementModule;
use App\Models\Staff;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FireWeightController extends Controller
{
    /**
     * Display the Fire Weight (Carga de Fuego por Peso) monitoring page for a module.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        // Buscar módulo o generar objeto mock elegante si aún no existe en BD
        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->find($moduleId);

        if (!$module) {
            $module = new MeasurementModule();
            $module->id = (int)$moduleId;
            $module->name = 'Carga de Fuego por Peso';
            $module->key = 'carga_fuego_peso';
            $module->description = 'Monitoreo y evaluación de carga de fuego ponderada por peso de materiales combustibles según NB 58005 / NTP 453.';
            $module->points_total = 8;
            $module->points_completed = 0;
            $module->start_date = Carbon::now()->subDays(2);
            $module->end_date = Carbon::now();
            $module->monitoring_type = 'Monitoreo Inicial por Peso (NB 58005)';
            $module->installation_name = 'Planta Industrial Central — Corporación Minera';
            $module->calibration_equipment = 'Balanza Digital Industrial Torrey PCR-40 & Distanciómetro Bosch GLM 50 C';
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

        $monitoringType = $module->monitoring_type ?: 'Carga de Fuego Ponderada por Peso (NB 58005 / NTP 453)';

        // Equipo asignado (Balanza Dinamométrica / Distanciómetro Láser)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Balanza Digital Industrial & Distanciómetro');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Torrey / Bosch') : 'Torrey & Bosch';
        $equipmentModel = $equipment ? ($equipment->model ?: 'PCR-40 / GLM 50-27 CG') : 'PCR-40 / GLM 50-27 CG';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'TR-9940128') : 'TR-9940128';
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
        $dbMeasurements = ($module->exists && method_exists($module, 'fireWeightMeasurements'))
            ? $module->fireWeightMeasurements()->with('staff')->get()
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
                $materials = is_array($item->materials) ? $item->materials : (json_decode($item->materials, true) ?: []);
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
                    'materials' => $materials,
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
            })->toArray();
        } else {
            // Mock Measurements representativos de Carga de Fuego por Peso
            $mockMeasurements = [
                [
                    'id' => 1,
                    'num' => '01',
                    'date' => date('d/m/Y', strtotime('-2 days')),
                    'raw_date' => date('Y-m-d', strtotime('-2 days')),
                    'time' => '08:45:00',
                    'macroarea' => 'Almacén Central de Logística',
                    'sector_name' => 'Sector P1 - Almacén de Embalajes y Pallets',
                    'dimensions' => [
                        'yi_largo' => 20.00,
                        'xi_ancho' => 15.00,
                        'area_m2' => 300.00,
                    ],
                    'materials' => [
                        [
                            'actividad' => 'Almacenes - en general',
                            'material' => 'Madera',
                            'peso_kg' => 2500.00,
                            'ki_mcal_kg' => 4.40,
                            'ki_mj_kg' => 18.41,
                            'ci' => 1.3,
                            'cantidad' => 120,
                            'descripcion' => 'Pallets de madera de pino apilados en racks metálicos'
                        ],
                        [
                            'actividad' => 'Almacenes - en general',
                            'material' => 'Cartón',
                            'peso_kg' => 1200.00,
                            'ki_mcal_kg' => 4.00,
                            'ki_mj_kg' => 16.74,
                            'ci' => 1.3,
                            'cantidad' => 450,
                            'descripcion' => 'Cajas de cartón corrugado plegadas para despacho'
                        ],
                        [
                            'actividad' => 'Almacenes - en general',
                            'material' => 'Polietileno',
                            'peso_kg' => 350.00,
                            'ki_mcal_kg' => 11.10,
                            'ki_mj_kg' => 46.44,
                            'ci' => 1.6,
                            'cantidad' => 25,
                            'descripcion' => 'Rollos de film termocontraíble para embalaje (stretch film)'
                        ]
                    ],
                    'fire_equipments' => [
                        ['tipo' => 'EXTINTOR', 'cantidad' => 3, 'observacion' => 'PQS 6kg Tipo ABC con precinto intacto', 'fotos' => []],
                        ['tipo' => 'PULSADOR', 'cantidad' => 2, 'observacion' => 'Pulsador de alarma rearmable', 'fotos' => []],
                        ['tipo' => 'BOCA DE INCENDIO', 'cantidad' => 1, 'observacion' => 'BIE 25mm con manguera de 20m presurizada', 'fotos' => []],
                    ],
                    'qs_mj_m2' => 354.10,
                    'qs_mcal_m2' => 84.60,
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
                    'observations' => 'Sector con buena ventilación perimetral. Los materiales se encuentran ordenados sobre estanterías metálicas con pasillos libres de 1.80m.',
                    'registered_by' => 'Ing. Carlos Mendoza (Técnico Evaluador)',
                    'staff_id' => 1,
                ],
                [
                    'id' => 2,
                    'num' => '02',
                    'date' => date('d/m/Y', strtotime('-2 days')),
                    'raw_date' => date('Y-m-d', strtotime('-2 days')),
                    'time' => '10:30:00',
                    'macroarea' => 'Galpón de Químicos e Insumos',
                    'sector_name' => 'Sector P2 - Bodega de Solventes y Pinturas',
                    'dimensions' => [
                        'yi_largo' => 12.00,
                        'xi_ancho' => 10.00,
                        'area_m2' => 120.00,
                    ],
                    'materials' => [
                        [
                            'actividad' => 'Pinturas y barnices - fabricación',
                            'material' => 'Aguarrás',
                            'peso_kg' => 800.00,
                            'ki_mcal_kg' => 10.20,
                            'ki_mj_kg' => 42.68,
                            'ci' => 1.6,
                            'cantidad' => 4,
                            'descripcion' => 'Tambores metálicos de 200 litros con solvente mineral'
                        ],
                        [
                            'actividad' => 'Pinturas y barnices - fabricación',
                            'material' => 'Alcohol etílico',
                            'peso_kg' => 500.00,
                            'ki_mcal_kg' => 7.10,
                            'ki_mj_kg' => 29.71,
                            'ci' => 1.6,
                            'cantidad' => 10,
                            'descripcion' => 'Bidones de alcohol etílico al 96%'
                        ],
                        [
                            'actividad' => 'Pinturas y barnices - fabricación',
                            'material' => 'Resina sintética',
                            'peso_kg' => 1500.00,
                            'ki_mcal_kg' => 8.50,
                            'ki_mj_kg' => 35.56,
                            'ci' => 1.6,
                            'cantidad' => 60,
                            'descripcion' => 'Sacos de resina epóxica sólida'
                        ]
                    ],
                    'fire_equipments' => [
                        ['tipo' => 'EXTINTOR', 'cantidad' => 4, 'observacion' => 'PQS 10kg Tipo ABC y CO2 5kg para derrames', 'fotos' => []],
                        ['tipo' => 'ALARMA', 'cantidad' => 2, 'observacion' => 'Sirena estroboscópica antiexplosiva', 'fotos' => []],
                        ['tipo' => 'PULSADOR', 'cantidad' => 2, 'observacion' => 'Pulsador manual IP65 estanco', 'fotos' => []],
                    ],
                    'qs_mj_m2' => 1342.50,
                    'qs_mcal_m2' => 320.80,
                    'risk_level' => 'Medio',
                    'risk_theme' => 'amber',
                    'image_path' => null,
                    'images' => [],
                    'images_count' => 2,
                    'location' => 'Zona 20K E: 593450.00 N: 8172960.00',
                    'latitude' => -16.52305,
                    'longitude' => -68.12910,
                    'utm_zone' => '20K',
                    'utm_easting' => 593450.00,
                    'utm_northing' => 8172960.00,
                    'observations' => 'Ventilación forzada continua antideflagrante. Contención de derrames perimetral y ducha de emergencia operativa.',
                    'registered_by' => 'Ing. Carlos Mendoza (Técnico Evaluador)',
                    'staff_id' => 1,
                ]
            ];

            $measurements = $mockMeasurements;
        }

        $totalMeasurements = count($measurements);

        // Ajustar points_completed dinámicamente
        $module->points_total = max($totalMeasurements, 4);
        $module->points_completed = $totalMeasurements;

        return view('measurements.fuego_pesos.index', compact(
            'module',
            'projectName',
            'companyName',
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
            'userName',
            'userRole'
        ));
    }

    /**
     * Guardar nuevo sector de Carga de Fuego por Peso
     */
    public function storeMeasurement($moduleId, Request $request)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'nullable|date',
            'measurement_time' => 'nullable|string|max:20',
            'macroarea' => 'required|string|max:255',
            'sector_name' => 'required|string|max:255',
            'dimensions' => 'nullable',
            'materials' => 'nullable',
            'fire_equipments' => 'nullable',
            'qs_mj_m2' => 'nullable|numeric',
            'qs_mcal_m2' => 'nullable|numeric',
            'risk_level' => 'nullable|string|max:50',
            'observations' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'staff_id' => 'nullable|integer',
        ]);

        $imagePath = null;
        $imagesPaths = [];

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('fire_weight_measurements', 'public');
            $imagesPaths[] = $imagePath;
        }

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('fire_weight_measurements', 'public');
                    $imagesPaths[] = $path;
                    if (!$imagePath) $imagePath = $path;
                }
            }
        }

        $dimensions = $request->input('dimensions');
        if (is_string($dimensions)) {
            $dimensions = json_decode($dimensions, true) ?: [];
        }

        $materials = $request->input('materials');
        if (is_string($materials)) {
            $materials = json_decode($materials, true) ?: [];
        }

        $fireEquipments = $request->input('fire_equipments');
        if (is_string($fireEquipments)) {
            $fireEquipments = json_decode($fireEquipments, true) ?: [];
        }

        $qsMj = (float)$request->input('qs_mj_m2', 0);
        $qsMcal = (float)$request->input('qs_mcal_m2', round($qsMj / 4.184, 2));

        $riskLevel = $request->input('risk_level');
        if (!$riskLevel) {
            if ($qsMj < 800) {
                $riskLevel = 'Bajo';
            } elseif ($qsMj <= 2000) {
                $riskLevel = 'Medio';
            } else {
                $riskLevel = 'Alto';
            }
        }

        $riskTheme = match (strtolower($riskLevel)) {
            'bajo' => 'emerald',
            'medio' => 'amber',
            'alto', 'muy alto' => 'rose',
            default => 'emerald',
        };

        $measurement = new FireWeightMeasurement();
        $measurement->module_id = $module->id;
        $measurement->point_number = $request->input('point_number') ?: str_pad($module->fireWeightMeasurements()->count() + 1, 2, '0', STR_PAD_LEFT);
        $measurement->measurement_date = $request->input('measurement_date') ?: Carbon::now()->format('Y-m-d');
        $measurement->measurement_time = $request->input('measurement_time') ?: Carbon::now()->format('H:i:s');
        $measurement->macroarea = $request->input('macroarea');
        $measurement->sector_name = $request->input('sector_name');
        $measurement->dimensions = $dimensions;
        $measurement->materials = $materials;
        $measurement->fire_equipments = $fireEquipments;
        $measurement->qs_mj_m2 = $qsMj;
        $measurement->qs_mcal_m2 = $qsMcal;
        $measurement->risk_level = $riskLevel;
        $measurement->risk_theme = $riskTheme;
        $measurement->observations = $request->input('observations');
        $measurement->registered_by = Auth::user() ? Auth::user()->name : 'Técnico Evaluador';
        $measurement->staff_id = $request->input('staff_id');

        // Location
        $measurement->latitude = $request->input('latitude');
        $measurement->longitude = $request->input('longitude');
        $measurement->utm_zone = $request->input('utm_zone');
        $measurement->utm_easting = $request->input('utm_easting');
        $measurement->utm_northing = $request->input('utm_northing');

        if ($imagePath) {
            $measurement->image_path = $imagePath;
        }
        if (!empty($imagesPaths)) {
            $measurement->images = $imagesPaths;
        }

        $measurement->save();

        $module->points_completed = $module->fireWeightMeasurements()->count();
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sector de Carga de Fuego por Peso registrado exitosamente.',
                'measurement' => $measurement
            ]);
        }

        return redirect()->route('modules.fire_weight', $moduleId)
            ->with('success', 'Sector de Carga de Fuego por Peso registrado exitosamente.');
    }

    /**
     * Actualizar sector existente de Carga de Fuego por Peso
     */
    public function updateMeasurement($moduleId, $measurementId, Request $request)
    {
        $measurement = FireWeightMeasurement::findOrFail($measurementId);

        $measurement->fill($request->except(['image', 'images']));

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('fire_weight_measurements', 'public');
            $measurement->image_path = $imagePath;
        }

        $measurement->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sector de Carga de Fuego por Peso actualizado exitosamente.',
                'measurement' => $measurement
            ]);
        }

        return redirect()->route('modules.fire_weight', $moduleId)
            ->with('success', 'Sector de Carga de Fuego por Peso actualizado exitosamente.');
    }

    /**
     * Eliminar sector de Carga de Fuego por Peso
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $measurement = FireWeightMeasurement::find($measurementId);
        if ($measurement) {
            $measurement->delete();
        }

        $module = MeasurementModule::find($moduleId);
        if ($module && method_exists($module, 'fireWeightMeasurements')) {
            $module->points_completed = $module->fireWeightMeasurements()->count();
            $module->save();
        }

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Sector de Carga de Fuego por Peso eliminado correctamente.'
            ]);
        }

        return redirect()->route('modules.fire_weight', $moduleId)
            ->with('success', 'Sector de Carga de Fuego por Peso eliminado correctamente.');
    }

    /**
     * Autosave directo de campos del Encabezado Técnico Dual
     */
    public function updateHeader($moduleId, Request $request)
    {
        $module = MeasurementModule::find($moduleId);
        if ($module) {
            if ($request->has('installation_name')) {
                $module->installation_name = $request->input('installation_name');
            }
            if ($request->has('start_date') && !empty($request->input('start_date'))) {
                $module->start_date = Carbon::parse($request->input('start_date'));
            }
            if ($request->has('end_date') && !empty($request->input('end_date'))) {
                $module->end_date = Carbon::parse($request->input('end_date'));
            }
            if ($request->has('monitoring_type')) {
                $module->monitoring_type = $request->input('monitoring_type');
            }
            $module->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Encabezado técnico guardado correctamente.'
        ]);
    }

    /**
     * Guardar preferencias y selección de fotos para el Reporte Fotográfico
     */
    public function savePhotoReportSettings($moduleId, Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Configuración de reporte fotográfico guardada con éxito.'
        ]);
    }

    /**
     * Show the Fire Weight Technical Report (Informe) with 3 Steps:
     * Step 1: Matriz de Carga de Fuego por Peso (Sectores, Materiales, Hi, Ci, Ra)
     * Step 2: Resumen de Sectores y Macro Área (Hoja Carta Horizontal con 2 tablas)
     * Step 3: Dotación y Asignación de Extintores (Hoja Carta Horizontal con Word export)
     */
    public function showReport(Request $request, $moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff', 'fireWeightReport'])->find($moduleId);

        if (!$module) {
            $module = new MeasurementModule();
            $module->id = (int)$moduleId;
            $module->name = 'Carga de Fuego por Peso';
            $module->key = 'carga_fuego_peso';
            $module->description = 'Monitoreo y evaluación de carga de fuego ponderada por peso de materiales combustibles según NB 58005 / NTP 453.';
            $module->points_total = 8;
            $module->points_completed = 0;
            $module->start_date = Carbon::now()->subDays(2);
            $module->end_date = Carbon::now();
            $module->monitoring_type = 'Monitoreo Inicial por Peso (NB 58005)';
            $module->installation_name = 'Planta Industrial Central — Corporación Minera';
            $module->calibration_equipment = 'Balanza Digital Industrial Torrey PCR-40 & Distanciómetro Bosch GLM 50 C';
        }

        $projectName = ($module->project) ? $module->project->name : 'Proyecto Industrial Alpha';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : 'Empresa Principal';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : date('Y-m-d', strtotime('-2 days'));
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : date('Y-m-d');
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : date('d/m/Y', strtotime('-2 days'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : date('d/m/Y');

        $monitoringType = $module->monitoring_type ?: 'Carga de Fuego Ponderada por Peso (NB 58005 / NTP 453)';

        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Balanza Digital Industrial & Distanciómetro');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Torrey / Bosch') : 'Torrey & Bosch';
        $equipmentModel = $equipment ? ($equipment->model ?: 'PCR-40 / GLM 50-27 CG') : 'PCR-40 / GLM 50-27 CG';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'TR-9940128') : 'TR-9940128';

        $assignedStaff = method_exists($module, 'getAssignedStaffAttribute') ? $module->getAssignedStaffAttribute() : null;
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Ing. Reynaldo Pachabol';
        }

        // Consultar BD para todas las mediciones/sectores del módulo
        $measurementId = $request->query('measurement_id') ?: $request->query('point');
        $allMeasurements = ($module->exists && method_exists($module, 'fireWeightMeasurements'))
            ? $module->fireWeightMeasurements()->with('staff')->get()
            : collect([]);

        $selectedMeasurements = $allMeasurements;
        $macroAreaName = $module->installation_name ?: 'PLANTA BAJA';

        if (!empty($measurementId) && $allMeasurements->isNotEmpty()) {
            $found = $allMeasurements->firstWhere('id', (int)$measurementId) 
                  ?: $allMeasurements->firstWhere('id', $measurementId) 
                  ?: $allMeasurements->firstWhere('point_number', $measurementId);
            if ($found && !empty($found->macroarea)) {
                $macroAreaName = $found->macroarea;
            }
        }

        // Verificar si ya existe un informe guardado en la tabla fire_weight_reports
        $savedReport = FireWeightReport::where('module_id', $moduleId)->first();

        $dbSectors = [];
        $dbEquipments = [];
        $primaryLt = '';
        $primaryAt = '';

        if ($selectedMeasurements->isNotEmpty()) {
            foreach ($selectedMeasurements as $index => $item) {
                if (!empty($item->macroarea)) {
                    $macroAreaName = $item->macroarea;
                }

                $dim = is_array($item->dimensions) ? $item->dimensions : (json_decode($item->dimensions, true) ?: []);
                $lt = isset($dim['yi_largo']) ? (float)$dim['yi_largo'] : ($item->yi_largo ?: (isset($dim['lt']) ? (float)$dim['lt'] : 0.0));
                $at = isset($dim['xi_ancho']) ? (float)$dim['xi_ancho'] : ($item->xi_ancho ?: (isset($dim['at']) ? (float)$dim['at'] : 0.0));
                $area = isset($dim['area_m2']) ? (float)$dim['area_m2'] : ($item->area_m2 ?: round($lt * $at, 2));

                if ($index === 0 && ($lt > 0 || $at > 0)) {
                    $primaryLt = $lt;
                    $primaryAt = $at;
                }

                $rawMats = is_array($item->materials) ? $item->materials : (json_decode($item->materials, true) ?: []);
                $matItems = [];
                $sectorActividad = 'Almacenes - en general';

                if (!empty($rawMats)) {
                    foreach ($rawMats as $mIdx => $m) {
                        $material = !empty($m['material']) ? $m['material'] : 'Madera';
                        $desc = !empty($m['descripcion']) ? $m['descripcion'] : '';
                        $peso = isset($m['peso_kg']) ? (float)$m['peso_kg'] : (isset($m['peso']) ? (float)$m['peso'] : 0.0);
                        $cant = isset($m['cantidad']) ? (float)$m['cantidad'] : (isset($m['cant']) ? (float)$m['cant'] : 1.0);
                        $hi = isset($m['hi']) ? (float)$m['hi'] : (isset($m['ki_mcal_kg']) ? (float)$m['ki_mcal_kg'] : null);
                        $ci = isset($m['ci']) ? (float)$m['ci'] : null;
                        
                        if ($hi === null || $ci === null) {
                            $lookup = self::lookupMaterialValuesStatic($material);
                            if ($hi === null) $hi = $lookup['hi'];
                            if ($ci === null) $ci = $lookup['ci'];
                        }

                        if (!empty($m['actividad'])) {
                            $sectorActividad = $m['actividad'];
                        }

                        $pixhixci = round(($peso * $cant) * $hi * $ci, 2);

                        $matItems[] = [
                            'material' => $material,
                            'descripcion' => $desc,
                            'peso_kg' => $peso,
                            'cantidad' => $cant,
                            'hi' => $hi,
                            'ci' => $ci,
                            'pixhixci' => $pixhixci,
                        ];
                    }
                } else {
                    $matItems[] = [
                        'material' => '',
                        'descripcion' => '',
                        'peso_kg' => 0.0,
                        'cantidad' => 1.0,
                        'hi' => 4.0,
                        'ci' => 1.0,
                        'pixhixci' => 0.0,
                    ];
                }

                $sectorRa = self::lookupActivityRaStatic($sectorActividad);

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

                // Calcular Qp = (SUM(PixHixCi) * Ra) / (Lt * At)
                $sumPixHixCi = 0;
                foreach ($matItems as $mIt) {
                    $sumPixHixCi += ($mIt['pixhixci'] ?? 0);
                }

                $qp = ($area > 0) ? round(($sumPixHixCi * $sectorRa) / $area, 2) : 0.0;
                $riskLevel = self::calculateRiskLevelStatic($qp);
                $levelNum = self::calculateRiskLevelNumStatic($qp);
                $at_x_qp = round($area * $qp, 2);

                $dbSectors[] = [
                    'id' => $item->id,
                    'name' => $item->sector_name ?: ('SECTOR ' . ($index + 1)),
                    'actividad' => $sectorActividad,
                    'lt' => $lt,
                    'at' => $at,
                    'area_m2' => $area,
                    'ra' => $sectorRa,
                    'qp' => $qp,
                    'at_x_qp' => $at_x_qp,
                    'risk_level' => $riskLevel,
                    'level_num' => $levelNum,
                    'materials' => $matItems,
                ];
            }
        }

        // Construir siempre reportData basado en los registros reales existentes
        $totalArea = !empty($dbSectors) ? array_sum(array_column($dbSectors, 'area_m2')) : 0;
        $sumATxQP = !empty($dbSectors) ? array_sum(array_column($dbSectors, 'at_x_qp')) : 0;
        $qm = ($totalArea > 0) ? round($sumATxQP / $totalArea, 2) : 0;
        $macroRisk = self::calculateRiskLevelStatic($qm);
        $macroLevel = self::calculateRiskLevelNumStatic($qm);

        $extType = '50Kg ABC';
        $finalAssignedQty = null;

        if ($savedReport && !empty($savedReport->extinguishers)) {
            $savedExt = is_array($savedReport->extinguishers) ? $savedReport->extinguishers : json_decode($savedReport->extinguishers, true);
            if (!empty($savedExt['extinguisher_type'])) {
                $extType = $savedExt['extinguisher_type'];
            }
            if (isset($savedExt['final_assigned_qty'])) {
                $finalAssignedQty = $savedExt['final_assigned_qty'];
            }
        }

        $extCalc = self::calculateExtinguisherStatic($extType, (float)$totalArea, $macroRisk, count($dbEquipments));
        if ($finalAssignedQty !== null) {
            $extCalc['final_assigned_qty'] = $finalAssignedQty;
        }

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

        return view('measurements.fuego_pesos.report', compact(
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
     * Save the report state to the dedicated fire_weight_reports table
     * and synchronize sector values to fire_weight_measurements.
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
            $module->save();

            if (is_array($reportData)) {
                // Guardar en tabla dedicada fire_weight_reports
                FireWeightReport::updateOrCreate(
                    ['module_id' => $moduleId],
                    [
                        'macroarea' => $reportData['macroarea'] ?? ($module->installation_name ?: 'PLANTA BAJA'),
                        'dimensions' => $reportData['dimensions'] ?? null,
                        'sectors' => $reportData['sectors'] ?? null,
                        'fire_equipments' => $reportData['fire_equipments'] ?? null,
                        'macro_summary' => $reportData['macro_summary'] ?? null,
                        'extinguishers' => $reportData['extinguishers'] ?? null,
                        'report_data' => $reportData,
                    ]
                );

                // Sincronizar también con mediciones de fire_weight_measurements si existen IDs
                if (!empty($reportData['sectors']) && is_array($reportData['sectors'])) {
                    foreach ($reportData['sectors'] as $sec) {
                        if (!empty($sec['id'])) {
                            $measurement = FireWeightMeasurement::find($sec['id']);
                            if ($measurement) {
                                if (!empty($sec['name'])) {
                                    $measurement->sector_name = $sec['name'];
                                }
                                if (isset($sec['materials']) && is_array($sec['materials'])) {
                                    $matsToSave = [];
                                    foreach ($sec['materials'] as $mat) {
                                        $matsToSave[] = [
                                            'actividad' => $sec['actividad'] ?? 'Almacenes - en general',
                                            'material' => $mat['material'] ?? 'Madera',
                                            'descripcion' => $mat['descripcion'] ?? '',
                                            'peso_kg' => (float)($mat['peso_kg'] ?? 0),
                                            'cantidad' => (float)($mat['cantidad'] ?? 1),
                                            'ki_mcal_kg' => (float)($mat['hi'] ?? 4.0),
                                            'hi' => (float)($mat['hi'] ?? 4.0),
                                            'ci' => (float)($mat['ci'] ?? 1.0),
                                            'pixhixci' => (float)($mat['pixhixci'] ?? 0),
                                        ];
                                    }
                                    $measurement->materials = $matsToSave;
                                }
                                if (isset($sec['lt']) || isset($sec['at'])) {
                                    $measurement->dimensions = [
                                        'yi_largo' => (float)($sec['lt'] ?? 0),
                                        'xi_ancho' => (float)($sec['at'] ?? 0),
                                        'area_m2' => (float)($sec['area_m2'] ?? 0),
                                    ];
                                    $measurement->yi_largo = (float)($sec['lt'] ?? 0);
                                    $measurement->xi_ancho = (float)($sec['at'] ?? 0);
                                    $measurement->area_m2 = (float)($sec['area_m2'] ?? 0);
                                }
                                if (isset($sec['ra'])) {
                                    $measurement->ra_value = (float)$sec['ra'];
                                }
                                if (isset($sec['qp'])) {
                                    $measurement->qs_mcal_m2 = (float)$sec['qp'];
                                    $measurement->qs_mj_m2 = round((float)$sec['qp'] * 4.184, 2);
                                }
                                if (isset($sec['risk_level'])) {
                                    $measurement->risk_level = $sec['risk_level'];
                                    $measurement->risk_color = match (strtolower($sec['risk_level'])) {
                                        'bajo' => 'emerald',
                                        'medio' => 'amber',
                                        'alto', 'muy alto' => 'rose',
                                        default => 'emerald',
                                    };
                                }
                                $measurement->save();
                            }
                        }
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Informe técnico de Carga de Fuego por Peso guardado y sincronizado correctamente.'
        ]);
    }

    /**
     * Catálogo completo de Actividades y su factor Ra extraído de extra!$F$1:$H$94
     * Fórmula Excel: =VLOOKUP(I10, extra!$F$1:$H$94, 3, 0)
     */
    public static function lookupActivityRaStatic(?string $actividad): float
    {
        if (empty($actividad)) return 1.0;
        $actClean = self::normalizeCatalogKey($actividad);

        $catalog = [
            'Aceites comestibles – fabricación' => 1.5,
            'Almacenes - en general' => 1,
            'Barnices - fabricación' => 1.5,
            'Barnizados - taller' => 1.5,
            'Bebidas - sin alcohol' => 1,
            'Bebidas alcohólicas fabricación' => 1.5,
            'Bebidas carbonatadas - fabricación' => 1,
            'Betún - preparación' => 1,
            'Carpintería' => 1.5,
            'Café - torrefacto' => 1.5,
            'Cartón - fabricación de cajas y elementos' => 1.5,
            'Caucho - fabricación de objetos' => 1.5,
            'Celuloide - fabricación' => 1,
            'Cera - fabricación de artículos' => 1,
            'Cerámica - taller' => 1,
            'Cerveza - fabricación' => 1.5,
            'Chocolate - fabricación' => 1.5,
            'Colas - fabricación' => 1,
            'Confección - talleres' => 1,
            'Conservas - fabricación' => 1,
            'Corcho - tratamiento' => 1.5,
            'Cuerdas' => 1.5,
            'Fabricación Cosméticos' => 1,
            'Cuero - tratamiento y objetos' => 1.5,
            'Destilerías - mat. inflamables' => 1.5,
            'Disolventes - destilación' => 1.5,
            'Ebanistería (sin alm. madera)' => 1,
            'Electricista - taller' => 1.5,
            'Electricidad - fabricación aparatos' => 1,
            'Electricidad - reparación aparatos' => 1.5,
            'Electrónica – fabricación aparatos' => 1,
            'Electrónica - reparación aparatos' => 1,
            'Motores eléctricos - fabricación' => 1.5,
            'Orfebrería - fabricación' => 1,
            'Panificación - elaboración y hornos de pan' => 1,
            'Pasamanería - taller' => 1,
            'Embarcaciones - fabricación' => 1.5,
            'Escobas - fabricación' => 1,
            'Esterillas - fabricación' => 1,
            'Fertilizantes químicos - fabricación' => 1.5,
            'Fibras artificiales' => 1.5,
            'Producción manipulación' => 1,
            'Forjas y herrerías' => 1,
            'Frigoríficos - cámaras' => 1,
            'Fundición de metales' => 1,
            'Galvanoplástica' => 1,
            'Géneros de punto - fabricación' => 1.5,
            'Grasas comestibles - fabricación' => 1.5,
            'Imprenta' => 1.5,
            'Industrias químicas' => 3,
            'Juguetes - fabricación' => 1.5,
            'Laboratorios eléctricos' => 1,
            'Laboratorios físicos y metalúrgicos' => 1,
            'Laboratorios fotográficos' => 1,
            'Laboratorios químicos' => 1.5,
            'Licores - fabricación' => 1.5,
            'Madera – fabricación contrachapados' => 1.5,
            'Mampostería - fabricación' => 1,
            'Mantequilla - fabricación' => 1,
            'Máquinas - fabricación' => 1.5,
            'Marcos - fabricación' => 1.5,
            'Materiales usados - tratamiento' => 1.5,
            'Mecanización de metales' => 1,
            'Medias - fabricación' => 1.5,
            'Medicamentos - laboratorios' => 1,
            'Metales - fabricación de artículos' => 1,
            'Muebles - fabricación (madera)' => 1.5,
            'Muebles - fabricación (metal)' => 1,
            'Molinos harineros' => 1.5,
            'Resinas sintéticas - fabricación' => 1.5,
            'Sacos - fabricación' => 1,
            'Seda artificial - fabricación' => 1.5,
            'Taller mecánico' => 1,
            'Papel - fabricación' => 1,
            'Pastas alimenticias - fabricación' => 1,
            'Pinturas - talleres' => 1.5,
            'Pinturas y barnices - fabricación' => 3,
            'Pinceles y cepillos - fabricación' => 3,
            'Pirotecnia - fabricación' => 1.5,
            'Plancha - taller' => 3,
            'Placas de resina sintética -fabricación' => 1,
            'Productos alimenticios - fabricación' => 1.5,
            'Reparaciones - taller' => 1,
            'Tapicería' => 1.5,
            'Teatro' => 1,
            'Tejidos - fábricas' => 1,
            'Telefónica - central' => 1,
            'Tintas de imprenta - fabricación' => 1.5,
            'Tintorerías' => 1,
            'Transformadores - construcción' => 1,
            'Vidrio - fabricación de artículos' => 1,
            'Vulcanización' => 1.5,
            'Zapatos - fabricación' => 1.5,
        ];

        // 1. Coincidencia directa
        if (isset($catalog[$actividad])) {
            return (float)$catalog[$actividad];
        }

        // 2. Coincidencia normalizada
        foreach ($catalog as $key => $raVal) {
            if (self::normalizeCatalogKey($key) === $actClean) {
                return (float)$raVal;
            }
        }

        // 3. Coincidencia por subcadena
        foreach ($catalog as $key => $raVal) {
            $kClean = self::normalizeCatalogKey($key);
            if (str_contains($actClean, $kClean) || str_contains($kClean, $actClean)) {
                return (float)$raVal;
            }
        }

        return 1.0;
    }

    /**
     * Catálogo completo de Materiales Combustibles (Hi y Ci) extraído de extra!$A$2:$D$126
     * Fórmulas Excel: 
     * - Hi: =VLOOKUP(C10, extra!$A$2:$D$126, 3, 0)
     * - Ci: =VLOOKUP(C10, extra!$A$2:$D$126, 4, 0)
     */
    public static function lookupMaterialValuesStatic(?string $material): array
    {
        if (empty($material)) return ['hi' => 4.0, 'ci' => 1.0];
        $matClean = self::normalizeCatalogKey($material);

        $catalog = [
            'Aceite de algodón' => ['hi' => 9, 'ci' => 1],
            'Aceite de creosota' => ['hi' => 9, 'ci' => 1.2],
            'Aceite de lino' => ['hi' => 9, 'ci' => 1.2],
            'Aceite mineral' => ['hi' => 10, 'ci' => 1],
            'Aceite de oliva' => ['hi' => 10, 'ci' => 1],
            'Aceite de parafina' => ['hi' => 10, 'ci' => 1],
            'Acetaldehído' => ['hi' => 6, 'ci' => 1.6],
            'Acetamida' => ['hi' => 5, 'ci' => 1],
            'Acetato de amilo' => ['hi' => 8, 'ci' => 1.2],
            'Acetato de polivinilo' => ['hi' => 5, 'ci' => 1],
            'Acetona' => ['hi' => 7, 'ci' => 1.6],
            'Acetileno' => ['hi' => 12, 'ci' => 1.6],
            'Acetileno disuelto' => ['hi' => 4, 'ci' => 1.6],
            'Ácido acético' => ['hi' => 4, 'ci' => 1.2],
            'Ácido benzoico' => ['hi' => 6, 'ci' => 1],
            'Acroleína' => ['hi' => 7, 'ci' => 1.6],
            'Aguarrás' => ['hi' => 10, 'ci' => 1.2],
            'Albúmina vegetal' => ['hi' => 6, 'ci' => 1],
            'Alcanfor' => ['hi' => 9, 'ci' => 1.2],
            'Alcohol alílico' => ['hi' => 8, 'ci' => 1.6],
            'Alcohol amílico' => ['hi' => 10, 'ci' => 1.2],
            'Alcohol butílico' => ['hi' => 8, 'ci' => 1.2],
            'Alcohol cetílico' => ['hi' => 10, 'ci' => 1],
            'Alcohol etílico' => ['hi' => 6, 'ci' => 1.6],
            'Alcohol metílico' => ['hi' => 5, 'ci' => 1.6],
            'Almidón' => ['hi' => 4, 'ci' => 1],
            'Anhídrido acético' => ['hi' => 4, 'ci' => 1.2],
            'Anilina' => ['hi' => 9, 'ci' => 1.2],
            'Antraceno' => ['hi' => 10, 'ci' => 1],
            'Antracita' => ['hi' => 8, 'ci' => 1],
            'Azúcar' => ['hi' => 4, 'ci' => 1],
            'Azufre' => ['hi' => 2, 'ci' => 1.2],
            'Benzaldehído' => ['hi' => 8, 'ci' => 1.2],
            'Bencina' => ['hi' => 10, 'ci' => 1.6],
            'Benzol' => ['hi' => 10, 'ci' => 1.6],
            'Benzofena' => ['hi' => 8, 'ci' => 1],
            'Butano' => ['hi' => 11, 'ci' => 1.6],
            'Cacao en polvo' => ['hi' => 4, 'ci' => 1],
            'Café' => ['hi' => 4, 'ci' => 1],
            'Cafeína' => ['hi' => 5, 'ci' => 1],
            'Calcio' => ['hi' => 1, 'ci' => 1.2],
            'Caucho' => ['hi' => 10, 'ci' => 1.2],
            'Carbón' => ['hi' => 7.5, 'ci' => 1],
            'Carbono' => ['hi' => 8, 'ci' => 1],
            'Cartón' => ['hi' => 4, 'ci' => 1],
            'Cartón asfáltico' => ['hi' => 5, 'ci' => 1.2],
            'Celuloide' => ['hi' => 4, 'ci' => 1.6],
            'Celulosa' => ['hi' => 4, 'ci' => 1],
            'Cereales' => ['hi' => 4, 'ci' => 1],
            'Chocolate' => ['hi' => 6, 'ci' => 1],
            'Cicloheptano' => ['hi' => 11, 'ci' => 1.6],
            'Ciclohexano' => ['hi' => 11, 'ci' => 1.6],
            'Ciclopentano' => ['hi' => 11, 'ci' => 1.6],
            'Ciclopropano' => ['hi' => 12, 'ci' => 1.6],
            'Cloruro de polivinilo' => ['hi' => 5, 'ci' => 1],
            'Cola celulósica' => ['hi' => 9, 'ci' => 1.2],
            'Coque de hulla' => ['hi' => 7, 'ci' => 1],
            'Cuero' => ['hi' => 5, 'ci' => 1],
            'Dietilamina' => ['hi' => 10, 'ci' => 1.6],
            'Dietilcetona' => ['hi' => 8, 'ci' => 1.6],
            'Dietileter' => ['hi' => 9, 'ci' => 1.6],
            'Difenil' => ['hi' => 10, 'ci' => 1.2],
            'Dinamita (75 %)' => ['hi' => 1, 'ci' => 1.6],
            'Dipenteno' => ['hi' => 11, 'ci' => 1.2],
            'Ebonita' => ['hi' => 8, 'ci' => 1],
            'Etano' => ['hi' => 12, 'ci' => 1.6],
            'Éter amílico' => ['hi' => 10, 'ci' => 1.6],
            'Éter etílico' => ['hi' => 8, 'ci' => 1.6],
            'Fibra de coco' => ['hi' => 6, 'ci' => 1],
            'Fenol' => ['hi' => 8, 'ci' => 1.2],
            'Fósforo' => ['hi' => 6, 'ci' => 1.6],
            'Furano' => ['hi' => 6, 'ci' => 1.6],
            'Gasóleo' => ['hi' => 10, 'ci' => 1.2],
            'Glicerina' => ['hi' => 4, 'ci' => 1],
            'Grasas' => ['hi' => 10, 'ci' => 1],
            'Gutapercha' => ['hi' => 11, 'ci' => 1.2],
            'Harina de trigo' => ['hi' => 4, 'ci' => 1],
            'Heptano' => ['hi' => 11, 'ci' => 1.6],
            'Hexametileno' => ['hi' => 11, 'ci' => 1.6],
            'Hexano' => ['hi' => 11, 'ci' => 1.6],
            'Hidrógeno' => ['hi' => 34, 'ci' => 1.6],
            'Hidruro de magnesio' => ['hi' => 4, 'ci' => 1.6],
            'Hidruro de sodio' => ['hi' => 2, 'ci' => 1.6],
            'Lana' => ['hi' => 5, 'ci' => 1],
            'Leche en polvo' => ['hi' => 4, 'ci' => 1],
            'Lino' => ['hi' => 4, 'ci' => 1],
            'Linóleum' => ['hi' => 5, 'ci' => 1],
            'Madera' => ['hi' => 4, 'ci' => 1],
            'Magnesio' => ['hi' => 6, 'ci' => 1.6],
            'Malta' => ['hi' => 4, 'ci' => 1],
            'Mantequilla' => ['hi' => 9, 'ci' => 1],
            'Metano' => ['hi' => 12, 'ci' => 1.6],
            'Monóxido de carbono' => ['hi' => 2, 'ci' => 1.6],
            'Nitrito de acetona' => ['hi' => 7, 'ci' => 1.6],
            'Nitrocelulosa' => ['hi' => 2, 'ci' => 1.6],
            'Octano' => ['hi' => 11, 'ci' => 1.6],
            'Papel' => ['hi' => 4, 'ci' => 1],
            'Parafina' => ['hi' => 11, 'ci' => 1],
            'Pentano' => ['hi' => 12, 'ci' => 1.6],
            'Petróleo' => ['hi' => 10, 'ci' => 1.2],
            'Poliamida' => ['hi' => 7, 'ci' => 1],
            'Policarbonato' => ['hi' => 7, 'ci' => 1],
            'Poliéster' => ['hi' => 6, 'ci' => 1],
            'Poliestireno' => ['hi' => 10, 'ci' => 1],
            'Polietileno' => ['hi' => 10, 'ci' => 1],
            'Poliisobutileno' => ['hi' => 11, 'ci' => 1],
            'Politetrafluoretileno' => ['hi' => 1, 'ci' => 1],
            'Poliuretano' => ['hi' => 6, 'ci' => 1.2],
            'Propano' => ['hi' => 11, 'ci' => 1.6],
            'Rayón' => ['hi' => 4, 'ci' => 1],
            'Resina de pino' => ['hi' => 10, 'ci' => 1.2],
            'Resina de fenol' => ['hi' => 6, 'ci' => 1],
            'Resina de urea' => ['hi' => 5, 'ci' => 1],
            'Seda' => ['hi' => 5, 'ci' => 1],
            'Sisal' => ['hi' => 4, 'ci' => 1],
            'Sodio' => ['hi' => 1, 'ci' => 1.6],
            'Sulfuro de carbono' => ['hi' => 3, 'ci' => 1.6],
            'Tabaco' => ['hi' => 4, 'ci' => 1],
            'Té' => ['hi' => 4, 'ci' => 1],
            'Tetralina' => ['hi' => 11, 'ci' => 1.2],
            'Toluol' => ['hi' => 10, 'ci' => 1.6],
            'Triacetato' => ['hi' => 4, 'ci' => 1],
            'Turba' => ['hi' => 8, 'ci' => 1],
            'Urea' => ['hi' => 2, 'ci' => 1],
            'Viscosa' => ['hi' => 4, 'ci' => 1],
        ];

        // 1. Coincidencia directa
        if (isset($catalog[$material])) {
            return $catalog[$material];
        }

        // 2. Coincidencia normalizada
        foreach ($catalog as $key => $vals) {
            if (self::normalizeCatalogKey($key) === $matClean) {
                return $vals;
            }
        }

        // 3. Coincidencia por subcadena
        foreach ($catalog as $key => $vals) {
            $kClean = self::normalizeCatalogKey($key);
            if (str_contains($matClean, $kClean) || str_contains($kClean, $matClean)) {
                return $vals;
            }
        }

        return ['hi' => 4.0, 'ci' => 1.0];
    }

    /**
     * Normalizar cadenas de texto para búsquedas insensibles a mayúsculas y acentos
     */
    public static function normalizeCatalogKey(?string $text): string
    {
        if (empty($text)) return '';
        $str = mb_strtolower(trim($text), 'UTF-8');
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', '–', '—', '-', '/', '  '],
            ['a', 'e', 'i', 'o', 'u', 'n', ' ', ' ', ' ', ' ', ' '],
            $str
        );
        return trim($str);
    }

    /**
     * Fórmula: =SI(P10<=200;"BAJO";SI(P10<=800;"MEDIO";"ALTO"))
     */
    public static function calculateRiskLevelStatic(?float $qp): string
    {
        $val = (float)($qp ?? 0);
        if ($val <= 200) return 'BAJO';
        if ($val <= 800) return 'MEDIO';
        return 'ALTO';
    }

    /**
     * Fórmula: =+SI(P10<=100;"1";SI(P10<=200;"2";SI(P10<=300;"3";SI(P10<=400;"4";SI(P10<=800;"5";SI(P10<=1600;"6";SI(P10<=3200;"7";"8")))))))
     */
    public static function calculateRiskLevelNumStatic(?float $qp): int
    {
        $val = (float)($qp ?? 0);
        if ($val <= 100) return 1;
        if ($val <= 200) return 2;
        if ($val <= 300) return 3;
        if ($val <= 400) return 4;
        if ($val <= 800) return 5;
        if ($val <= 1600) return 6;
        if ($val <= 3200) return 7;
        return 8;
    }

    /**
     * Cálculo normativo de dotación de extintores (NB 58005)
     */
    public static function calculateExtinguisherStatic(string $extType, float $totalArea, string $riskLevel, int $existingCount = 0): array
    {
        $extCatalog = [
            "4,5Kg ABC" => ["extintor" => "ABC", "pot_a" => "4A", "pot_b" => "60B"],
            "9Kg ABC"   => ["extintor" => "ABC", "pot_a" => "10A", "pot_b" => "80B"],
            "50Kg ABC"  => ["extintor" => "ABC", "pot_a" => "40A", "pot_b" => "160B"],
            "4,5Kg BC"  => ["extintor" => "CO2", "pot_a" => "", "pot_b" => "10B"],
            "7Kg BC"    => ["extintor" => "CO2", "pot_a" => "", "pot_b" => "20B"],
            "9Kg BC"    => ["extintor" => "CO2", "pot_a" => "", "pot_b" => "20B"],
            "6L H2O"    => ["extintor" => "Agua", "pot_a" => "1A", "pot_b" => ""],
            "9,5L H2O"  => ["extintor" => "Agua", "pot_a" => "2A", "pot_b" => ""],
        ];

        $coveredAreaA = [
            "1A"  => ["BAJO" => 280,  "MEDIO" => 0,    "ALTO" => 0,    "dist" => 23],
            "2A"  => ["BAJO" => 560,  "MEDIO" => 280,  "ALTO" => 186,  "dist" => 23],
            "3A"  => ["BAJO" => 840,  "MEDIO" => 420,  "ALTO" => 280,  "dist" => 23],
            "4A"  => ["BAJO" => 1050, "MEDIO" => 560,  "ALTO" => 370,  "dist" => 23],
            "6A"  => ["BAJO" => 1050, "MEDIO" => 840,  "ALTO" => 560,  "dist" => 23],
            "10A" => ["BAJO" => 1050, "MEDIO" => 1050, "ALTO" => 840,  "dist" => 23],
            "20A" => ["BAJO" => 1050, "MEDIO" => 1050, "ALTO" => 1050, "dist" => 23],
            "40A" => ["BAJO" => 1050, "MEDIO" => 1050, "ALTO" => 1050, "dist" => 23],
        ];

        $coveredAreaB = [
            "5B"   => ["BAJO" => 242.5, "MEDIO" => 0,     "ALTO" => 0,      "dist_BAJO" => 9.15,  "dist_MEDIO" => 5.49,  "dist_ALTO" => 1.98],
            "10B"  => ["BAJO" => 673.5, "MEDIO" => 242.5, "ALTO" => 0,      "dist_BAJO" => 15.25, "dist_MEDIO" => 9.15,  "dist_ALTO" => 3.29],
            "20B"  => ["BAJO" => 673.5, "MEDIO" => 673.5, "ALTO" => 0,      "dist_BAJO" => 15.25, "dist_MEDIO" => 15.25, "dist_ALTO" => 5.49],
            "40B"  => ["BAJO" => 673.5, "MEDIO" => 673.5, "ALTO" => 242.5,  "dist_BAJO" => 15.25, "dist_MEDIO" => 15.25, "dist_ALTO" => 9.15],
            "60B"  => ["BAJO" => 673.5, "MEDIO" => 673.5, "ALTO" => 431.1,  "dist_BAJO" => 15.25, "dist_MEDIO" => 15.25, "dist_ALTO" => 12.2],
            "80B"  => ["BAJO" => 673.5, "MEDIO" => 673.5, "ALTO" => 673.5,  "dist_BAJO" => 15.25, "dist_MEDIO" => 15.25, "dist_ALTO" => 15.25],
            "160B" => ["BAJO" => 673.5, "MEDIO" => 673.5, "ALTO" => 2182.3, "dist_BAJO" => 15.25, "dist_MEDIO" => 15.25, "dist_ALTO" => 27.45],
        ];

        $riskKey = strtoupper(trim($riskLevel));
        if (!in_array($riskKey, ['BAJO', 'MEDIO', 'ALTO'])) {
            $riskKey = 'BAJO';
        }

        $typeData = $extCatalog[$extType] ?? $extCatalog["50Kg ABC"];
        $potA = $typeData['pot_a'] ?? '';
        $potBC = $typeData['pot_b'] ?? '';

        $covA = ($potA && isset($coveredAreaA[$potA][$riskKey])) ? $coveredAreaA[$potA][$riskKey] : 0;
        $distA = ($potA && isset($coveredAreaA[$potA]['dist'])) ? $coveredAreaA[$potA]['dist'] : 23.0;

        $covB = ($potBC && isset($coveredAreaB[$potBC][$riskKey])) ? $coveredAreaB[$potBC][$riskKey] : 0;
        $distKey = "dist_{$riskKey}";
        $distB = ($potBC && isset($coveredAreaB[$potBC][$distKey])) ? $coveredAreaB[$potBC][$distKey] : 15.25;

        $distAB = ($distB > 0 && $distB <= $distA) ? $distB : 15.25;

        $numExtA = ($covA > 0) ? ($totalArea / $covA) : 0;
        $numExtB = ($covB > 0) ? ($totalArea / $covB) : 0;

        $maxExt = max($numExtA, $numExtB);
        $theorQty = ($totalArea > 0 && $maxExt > 0) ? (int)ceil($maxExt) : 1;
        $assignedQty = max($theorQty, $existingCount, 1);

        return [
            'extinguisher_type' => $extType,
            'potential_a' => $potA,
            'potential_bc' => $potBC,
            'covered_area_a' => $covA,
            'covered_area_b' => $covB,
            'num_ext_a' => round($numExtA, 2),
            'num_ext_b' => round($numExtB, 2),
            'theoretical_qty' => $theorQty,
            'final_assigned_qty' => $assignedQty,
            'distance_b' => $distB,
            'distance_a' => $distA,
            'distance_ab' => $distAB,
        ];
    }
}

