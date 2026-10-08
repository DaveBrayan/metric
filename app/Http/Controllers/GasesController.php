<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\GasMeasurement;
use App\Models\MeasurementModule;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class GasesController extends Controller
{
    /**
     * Definición canónica de los 13 gases normativos de monitoreo.
     */
    public static function getGasesDefinitions()
    {
        return [
            ['key' => 'o2', 'name' => 'Oxígeno', 'formula' => 'O2', 'unit' => 'ppm'],
            ['key' => 'h2s', 'name' => 'Ácido Sulfhídrico', 'formula' => 'H2S', 'unit' => 'ppm'],
            ['key' => 'co', 'name' => 'Monóxido de Carbono', 'formula' => 'CO', 'unit' => 'ppm'],
            ['key' => 'lel', 'name' => 'Gases Combustibles', 'formula' => 'LEL', 'unit' => '%'],
            ['key' => 'hcho', 'name' => 'Formaldehídos', 'formula' => 'HCHO', 'unit' => 'mg/m3'],
            ['key' => 'tvoc', 'name' => 'Compuestos Orgánicos Volátiles', 'formula' => 'T-VOC', 'unit' => 'mg/m3'],
            ['key' => 'co2', 'name' => 'Dióxido de Carbono', 'formula' => 'CO2', 'unit' => 'ppm'],
            ['key' => 'as', 'name' => 'Arsénico Inorgánico', 'formula' => 'As', 'unit' => 'mg/m3'],
            ['key' => 'so2', 'name' => 'Dióxido de Azufre', 'formula' => 'SO2', 'unit' => 'ppm'],
            ['key' => 'nh3', 'name' => 'Amoniaco', 'formula' => 'NH3', 'unit' => 'ppm'],
            ['key' => 'cl2', 'name' => 'Cloro Gaseoso', 'formula' => 'Cl2', 'unit' => 'ppm'],
            ['key' => 'tcov', 'name' => 'Compuestos Orgánicos Volátiles Totales', 'formula' => 'TCOV', 'unit' => 'ppm'],
            ['key' => 'no2', 'name' => 'Dióxido de Nitrógeno', 'formula' => 'NO2', 'unit' => 'ppm'],
        ];
    }

    /**
     * Muestra la página principal de monitoreo de Gases para un módulo.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Resuelve información técnica del encabezado: Razón Social de la empresa o proyecto
        $project = $module->project;
        $company = $project ? $project->company : null;
        $razonSocial = '';
        if ($project) {
            if (!empty($project->razon_social)) {
                $razonSocial = $project->razon_social;
            } elseif ($company && !empty($company->legal_name)) {
                $razonSocial = $company->legal_name;
            } elseif ($company && !empty($company->name)) {
                $razonSocial = $company->name;
            } else {
                $razonSocial = $project->name;
            }
        }

        $oldDefault = ($project ? $project->name : '') . ($company ? " - {$company->name}" : '');
        if (empty($module->installation_name) || $module->installation_name === $oldDefault || $module->installation_name === 'PACHABOL - PLANTA CENTRAL EMV') {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'Instalación');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        // Fechas del módulo
        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Seguimiento de Gases';

        // Equipo asignado
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Detector Multigas');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'Industrial Scientific') : 'Industrial Scientific';
        $equipmentModel = $equipment ? ($equipment->model ?: 'MX4 Ventis') : 'MX4 Ventis';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '200428-001') : '200428-001';
        $equipmentImage = ($equipment && $equipment->image) ? asset($equipment->image) : null;

        // Personal asignado para selector y encabezado técnico
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Campo';
            $staffList = Staff::orderBy('name')->get();
        }

        // Mediciones registradas
        $measurements = $module->gasMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

            // Si lat/lng no están en la BD, extraer y convertir desde location
            if (($lat === null || $lng === null) && !empty($item->location)) {
                $loc = trim($item->location);
                if (str_starts_with($loc, '{') || str_starts_with($loc, '[')) {
                    $d = json_decode($loc, true);
                    if (is_array($d)) {
                        if (!empty($d['latitude']) && !empty($d['longitude'])) {
                            $lat = (float) $d['latitude'];
                            $lng = (float) $d['longitude'];
                        } elseif (!empty($d['easting']) && !empty($d['northing'])) {
                            $conv = $this->utmToLatLng($d['easting'], $d['northing'], $d['utm_zone'] ?? $d['zone'] ?? '20K');
                            $lat = $conv['lat'];
                            $lng = $conv['lng'];
                        }
                    }
                } elseif (preg_match('/E:\s*([0-9.]+)/i', $loc, $mE) && preg_match('/N:\s*([0-9.]+)/i', $loc, $mN)) {
                    preg_match('/Z:\s*([0-9A-Za-z]+)/i', $loc, $mZ);
                    $conv = $this->utmToLatLng((float)$mE[1], (float)$mN[1], $mZ[1] ?? '20K');
                    $lat = $conv['lat'];
                    $lng = $conv['lng'];
                }
            }

            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function ($p) {
                return $p ? asset($p) : null;
            }, $rawImages)));

            $selectedGases = is_array($item->selected_gases) ? $item->selected_gases : (json_decode($item->selected_gases, true) ?: []);
            if (is_string($selectedGases)) $selectedGases = json_decode($selectedGases, true) ?: [];

            $gasesReadings = is_array($item->gases_readings) ? $item->gases_readings : (json_decode($item->gases_readings, true) ?: []);
            if (is_string($gasesReadings)) $gasesReadings = json_decode($gasesReadings, true) ?: [];

            if (empty($selectedGases) && is_array($gasesReadings) && !empty($gasesReadings)) {
                $selectedGases = array_keys($gasesReadings);
            }

            return [
                'id' => $item->id,
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '—',
                'area' => $item->area ?: '—',
                'workstation' => $item->workstation ?: '—',
                'measurement_point' => $item->measurement_point ?: '—',
                'activity_description' => $item->activity_description ?: 'Operaciones generales',
                'temperatura' => $item->temperatura !== null ? (float)$item->temperatura : null,
                'presion_atm' => $item->presion_atm !== null ? (float)$item->presion_atm : null,
                'vel_aire' => $item->vel_aire !== null ? (float)$item->vel_aire : null,
                'selected_gases' => $selectedGases,
                'gases_readings' => $gasesReadings,
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
                'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                'raw_image_path' => $item->image_path,
                'images' => $imagesUrls,
                'images_count' => count($imagesUrls),
                'location' => $item->location ?: ($lat && $lng ? "{$lat}, {$lng}" : '—'),
                'latitude' => $lat,
                'longitude' => $lng,
                'utm_zone' => $item->utm_zone,
                'utm_easting' => $item->utm_easting,
                'utm_northing' => $item->utm_northing,
                'observations' => $item->observations ?: 'Sin observaciones',
                'raw_observations' => $item->observations,
                'registered_by' => (function () use ($item, $assignedStaff, $currentUser) {
                    if (!empty($item->registered_by) && !is_numeric($item->registered_by)) {
                        return $item->registered_by;
                    }
                    if ($item->staff) {
                        return $item->staff->full_name ?: $item->staff->name;
                    }
                    if (!empty($item->staff_id)) {
                        $st = Staff::find($item->staff_id);
                        if ($st) return $st->full_name ?: $st->name;
                    }
                    if ($assignedStaff && $assignedStaff->isNotEmpty()) {
                        return $assignedStaff->first()->full_name ?: $assignedStaff->first()->name;
                    }
                    return $currentUser ? $currentUser->name : 'Técnico de Campo';
                })(),
                'staff_id' => $item->staff_id,
            ];
        });

        // Configuración guardada para el Reporte Fotográfico
        $savedSettings = $module->photo_report_settings ?: [];
        $photoReportSettings = [
            'grid' => $savedSettings['grid'] ?? '2x3',
            'orientation' => $savedSettings['orientation'] ?? 'landscape',
            'selected_points' => $savedSettings['selected_points'] ?? $measurements->pluck('id')->toArray(),
            'photo_indices' => $savedSettings['photo_indices'] ?? (object)[],
        ];

        $totalMeasurements = $measurements->count();

        return view('measurements.gases.index', compact(
            'userName',
            'userRole',
            'module',
            'installationName',
            'startDateRaw',
            'endDateRaw',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'registeredByHeader',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'equipmentImage',
            'staffList',
            'measurements',
            'totalMeasurements',
            'photoReportSettings'
        ));
    }

    /**
     * Registra un nuevo punto de monitoreo de gases.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'workstation' => 'required|string|max:255',
            'measurement_point' => 'nullable|string|max:255',
            'activity_description' => 'nullable|string|max:255',
            'temperatura' => 'nullable|numeric',
            'presion_atm' => 'nullable|numeric',
            'vel_aire' => 'nullable|numeric',
            'selected_gases' => 'nullable',
            'gases_readings' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
        ]);

        // Procesar selected_gases y gases_readings
        $selectedGases = $this->parseJsonInput($request->input('selected_gases'));
        $gasesReadings = $this->parseJsonInput($request->input('gases_readings'));

        // Procesar columnas de respaldo individual para cada gas
        $gasColumns = $this->extractGasColumnsFromReadings($gasesReadings, $request);

        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $uploadedImages[] = $this->saveAdaptiveImage($file, $uploadDir);
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                array_unshift($uploadedImages, $this->saveAdaptiveImage($file, $uploadDir));
            }
        }
        $imagePath = $uploadedImages[0] ?? null;

        // Determinar nombre del registrador
        $registeredByName = $validated['registered_by'] ?? null;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }
        if (empty($registeredByName) || is_numeric($registeredByName)) {
            $registeredByName = Auth::user() ? Auth::user()->name : 'Técnico de Campo';
        }

        $measurementCount = $module->gasMeasurements()->count();
        $pointNumber = !empty($validated['point_number'])
            ? $validated['point_number']
            : str_pad($measurementCount + 1, 2, '0', STR_PAD_LEFT);

        // Coordenadas y UTM
        $lat = $validated['latitude'] ?? null;
        $lng = $validated['longitude'] ?? null;
        $utmZone = $validated['utm_zone'] ?? null;
        $utmE = $validated['utm_easting'] ?? null;
        $utmN = $validated['utm_northing'] ?? null;

        if (($lat === null || $lng === null) && ($utmE && $utmN)) {
            $conv = $this->utmToLatLng($utmE, $utmN, $utmZone ?: '20K');
            $lat = $conv['lat'];
            $lng = $conv['lng'];
        }

        $createData = array_merge([
            'point_number' => $pointNumber,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? Carbon::now()->format('H:i'),
            'area' => $validated['area'],
            'workstation' => $validated['workstation'],
            'measurement_point' => $validated['measurement_point'] ?? $validated['workstation'],
            'activity_description' => $validated['activity_description'] ?? 'Operaciones generales',
            'temperatura' => $validated['temperatura'] ?? null,
            'presion_atm' => $validated['presion_atm'] ?? null,
            'vel_aire' => $validated['vel_aire'] ?? null,
            'selected_gases' => $selectedGases,
            'gases_readings' => $gasesReadings,
            'image_path' => $imagePath,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $validated['location'] ?? ($lat && $lng ? "{$lat}, {$lng}" : null),
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $registeredByName,
            'staff_id' => $validated['staff_id'] ?? null,
        ], $gasColumns);

        $module->gasMeasurements()->create($createData);

        // Actualizar puntos completados del módulo
        $module->points_completed = $module->gasMeasurements()->count();
        $module->save();

        return redirect()->route('modules.gases', $moduleId)
            ->with('success', "Punto de medición de gases #{$pointNumber} registrado exitosamente.");
    }

    /**
     * Actualiza un punto de medición de gases existente.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->gasMeasurements()->findOrFail($measurementId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'workstation' => 'required|string|max:255',
            'measurement_point' => 'nullable|string|max:255',
            'activity_description' => 'nullable|string|max:255',
            'temperatura' => 'nullable|numeric',
            'presion_atm' => 'nullable|numeric',
            'vel_aire' => 'nullable|numeric',
            'selected_gases' => 'nullable',
            'gases_readings' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
        ]);

        $selectedGases = $request->has('selected_gases') ? $this->parseJsonInput($request->input('selected_gases')) : $measurement->selected_gases;
        $gasesReadings = $request->has('gases_readings') ? $this->parseJsonInput($request->input('gases_readings')) : $measurement->gases_readings;
        $gasColumns = $this->extractGasColumnsFromReadings($gasesReadings, $request);

        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $uploadedImages[] = $this->saveAdaptiveImage($file, $uploadDir);
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                array_unshift($uploadedImages, $this->saveAdaptiveImage($file, $uploadDir));
            }
        }

        $normalizePath = function ($url) {
            if (!$url || !is_string($url)) return '';
            $parsed = parse_url($url, PHP_URL_PATH);
            $path = $parsed ?: $url;
            return ltrim($path, '/\\');
        };

        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        if (empty($existingImages) && !empty($measurement->image_path)) {
            $existingImages = [$measurement->image_path];
        }

        if ($request->has('remaining_images')) {
            $rawRemaining = $request->input('remaining_images');
            $remaining = is_array($rawRemaining) ? $rawRemaining : json_decode($rawRemaining, true);
            if (is_array($remaining)) {
                $normalizedRemaining = array_map($normalizePath, $remaining);
                $filteredExisting = [];
                foreach ($existingImages as $img) {
                    $norm = $normalizePath($img);
                    if (in_array($norm, $normalizedRemaining) || in_array($img, $remaining)) {
                        $filteredExisting[] = $img;
                    }
                }
                $existingImages = $filteredExisting;
            }
        }

        if (!empty($uploadedImages)) {
            $allImages = array_values(array_merge($existingImages, $uploadedImages));
            $imagePath = $allImages[0] ?? null;
        } else {
            $allImages = array_values($existingImages);
            $imagePath = $allImages[0] ?? null;
        }

        $registeredByName = $validated['registered_by'] ?? $measurement->registered_by;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }

        $lat = array_key_exists('latitude', $validated) ? $validated['latitude'] : $measurement->latitude;
        $lng = array_key_exists('longitude', $validated) ? $validated['longitude'] : $measurement->longitude;
        $utmZone = array_key_exists('utm_zone', $validated) ? $validated['utm_zone'] : $measurement->utm_zone;
        $utmE = array_key_exists('utm_easting', $validated) ? $validated['utm_easting'] : $measurement->utm_easting;
        $utmN = array_key_exists('utm_northing', $validated) ? $validated['utm_northing'] : $measurement->utm_northing;

        if (($lat === null || $lng === null) && ($utmE && $utmN)) {
            $conv = $this->utmToLatLng($utmE, $utmN, $utmZone ?: '20K');
            $lat = $conv['lat'];
            $lng = $conv['lng'];
        }

        $updateData = array_merge([
            'point_number' => $validated['point_number'] ?? $measurement->point_number,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? $measurement->measurement_time,
            'area' => $validated['area'],
            'workstation' => $validated['workstation'],
            'measurement_point' => $validated['measurement_point'] ?? $measurement->measurement_point,
            'activity_description' => $validated['activity_description'] ?? $measurement->activity_description,
            'temperatura' => array_key_exists('temperatura', $validated) ? $validated['temperatura'] : $measurement->temperatura,
            'presion_atm' => array_key_exists('presion_atm', $validated) ? $validated['presion_atm'] : $measurement->presion_atm,
            'vel_aire' => array_key_exists('vel_aire', $validated) ? $validated['vel_aire'] : $measurement->vel_aire,
            'selected_gases' => $selectedGases,
            'gases_readings' => $gasesReadings,
            'image_path' => $imagePath,
            'images' => !empty($allImages) ? $allImages : null,
            'location' => $validated['location'] ?? $measurement->location,
            'latitude' => $lat,
            'longitude' => $lng,
            'utm_zone' => $utmZone,
            'utm_easting' => $utmE,
            'utm_northing' => $utmN,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $registeredByName,
            'staff_id' => $validated['staff_id'] ?? null,
        ], $gasColumns);

        $measurement->update($updateData);

        return redirect()->route('modules.gases', $moduleId)
            ->with('success', "Punto de medición #{$measurement->point_number} actualizado correctamente.");
    }

    /**
     * Elimina un punto de medición de gases.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->gasMeasurements()->findOrFail($measurementId);
        $pointNum = $measurement->point_number;
        $measurement->delete();

        $module->points_completed = $module->gasMeasurements()->count();
        $module->save();

        return redirect()->route('modules.gases', $moduleId)
            ->with('success', "Punto de medición #{$pointNum} eliminado exitosamente.");
    }

    /**
     * Actualiza metadatos del encabezado técnico.
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'installation_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'monitoring_type' => 'nullable|string|max:255',
        ]);

        if ($request->has('installation_name')) {
            $module->installation_name = $validated['installation_name'] ?? '';
        }
        if ($request->has('start_date')) {
            $module->start_date = $validated['start_date'] ?: null;
        }
        if ($request->has('end_date')) {
            $module->end_date = $validated['end_date'] ?: null;
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $validated['monitoring_type'] ?? '';
        }
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Encabezado técnico de gases actualizado correctamente.',
                'data' => [
                    'installation_name' => $module->installation_name,
                    'start_date' => $module->start_date ? $module->start_date->format('Y-m-d') : '',
                    'end_date' => $module->end_date ? $module->end_date->format('Y-m-d') : '',
                    'monitoring_type' => $module->monitoring_type,
                ]
            ]);
        }

        return redirect()->route('modules.gases', $moduleId)
            ->with('success', 'Datos del encabezado técnico actualizados exitosamente.');
    }

    /**
     * Guarda la configuración del reporte fotográfico.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'grid' => 'nullable|string|in:2x3,2x4,3x3,3x4',
            'orientation' => 'nullable|string|in:landscape,portrait',
            'selected_points' => 'nullable|array',
            'photo_indices' => 'nullable|array',
        ]);

        $currentSettings = $module->photo_report_settings ?: [];

        $newSettings = [
            'grid' => $validated['grid'] ?? ($currentSettings['grid'] ?? '2x3'),
            'orientation' => $validated['orientation'] ?? ($currentSettings['orientation'] ?? 'landscape'),
            'selected_points' => array_key_exists('selected_points', $validated) ? $validated['selected_points'] : ($currentSettings['selected_points'] ?? []),
            'photo_indices' => array_key_exists('photo_indices', $validated) ? $validated['photo_indices'] : ($currentSettings['photo_indices'] ?? []),
            'updated_at' => now()->toIso8601String(),
        ];

        $module->photo_report_settings = $newSettings;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración del reporte fotográfico guardada exitosamente.',
            'settings' => $newSettings,
        ]);
    }

    /**
     * Definición canónica y estándares normativos TLV ACGIH para gases de monitoreo.
     */
    public static function getGasesStandards()
    {
        return [
            'o2' => [
                'key' => 'o2',
                'name' => 'Oxígeno',
                'formula' => 'O2',
                'unit' => '%Vol.',
                'tlv' => '19,5',
                'tlv_range' => '19,5 - 23,5',
                'tlv_min' => 19.5,
                'tlv_max' => 23.5,
                'eval_title' => 'EVALUACIÓN DE %O2 EN EL AMBIENTE',
                'rule' => 'range', // >= 19.5 && <= 23.5 (o >= 19.5)
            ],
            'h2s' => [
                'key' => 'h2s',
                'name' => 'Ácido Sulfhídrico',
                'formula' => 'H2S',
                'unit' => 'ppm',
                'tlv' => '1',
                'tlv_range' => '1',
                'tlv_max' => 1.0,
                'eval_title' => 'EVALUACIÓN DE H2S EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'co' => [
                'key' => 'co',
                'name' => 'Monóxido de Carbono',
                'formula' => 'CO',
                'unit' => 'ppm',
                'tlv' => '25',
                'tlv_range' => '25',
                'tlv_max' => 25.0,
                'eval_title' => 'EVALUACIÓN DE CO EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'lel' => [
                'key' => 'lel',
                'name' => 'Gases Combustibles',
                'formula' => 'LEL',
                'unit' => '% LEL',
                'tlv' => '10',
                'tlv_range' => '10',
                'tlv_max' => 10.0,
                'eval_title' => 'EVALUACIÓN DE %LEL EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'hcho' => [
                'key' => 'hcho',
                'name' => 'Formaldehído',
                'formula' => 'HCHO',
                'unit' => 'mg/m3',
                'tlv' => '0,1 (0,12 mg/m3)',
                'tlv_range' => '0,1 (0,12 mg/m3)',
                'tlv_max' => 0.1,
                'eval_title' => 'EVALUACIÓN DE FORMALDEHÍDO (HCHO) EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'tvoc' => [
                'key' => 'tvoc',
                'name' => 'Compuestos Orgánicos Volátiles',
                'formula' => 'T-COV',
                'unit' => 'mg/m3',
                'tlv' => '3',
                'tlv_range' => '3',
                'tlv_max' => 3.0,
                'eval_title' => 'EVALUACIÓN DE COMPUESTOS ORGÁNICOS VOLÁTILES (T-COV) EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'tcov' => [
                'key' => 'tcov',
                'name' => 'Compuestos Orgánicos Volátiles',
                'formula' => 'T-COV',
                'unit' => 'mg/m3',
                'tlv' => '3',
                'tlv_range' => '3',
                'tlv_max' => 3.0,
                'eval_title' => 'EVALUACIÓN DE COMPUESTOS ORGÁNICOS VOLÁTILES (T-COV) EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'co2' => [
                'key' => 'co2',
                'name' => 'Dióxido de Carbono',
                'formula' => 'CO2',
                'unit' => 'ppm',
                'tlv' => '5000',
                'tlv_range' => '5000',
                'tlv_max' => 5000.0,
                'eval_title' => 'EVALUACIÓN DE CO2 EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'nh3' => [
                'key' => 'nh3',
                'name' => 'Amoníaco',
                'formula' => 'NH3',
                'unit' => 'ppm',
                'tlv' => '25',
                'tlv_range' => '25',
                'tlv_max' => 25.0,
                'eval_title' => 'EVALUACIÓN DE AMONÍACO (NH3) EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'cl2' => [
                'key' => 'cl2',
                'name' => 'Cloro Gaseoso',
                'formula' => 'Cl2',
                'unit' => 'ppm',
                'tlv' => '0,1',
                'tlv_range' => '0,1',
                'tlv_max' => 0.1,
                'eval_title' => 'EVALUACIÓN DE CLORO GASEOSO (Cl2) EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'no2' => [
                'key' => 'no2',
                'name' => 'Dióxido de Nitrógeno',
                'formula' => 'NO2',
                'unit' => 'ppm',
                'tlv' => '0,2',
                'tlv_range' => '0,2',
                'tlv_max' => 0.2,
                'eval_title' => 'EVALUACIÓN DE DIÓXIDO DE NITRÓGENO (NO2) EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'so2' => [
                'key' => 'so2',
                'name' => 'Dióxido de Azufre',
                'formula' => 'SO2',
                'unit' => 'ppm',
                'tlv' => '0,25 (STEL) / 2 (TWA ref.)',
                'tlv_range' => '0,25 (STEL) / 2 (TWA ref.)',
                'tlv_max' => 0.25,
                'eval_title' => 'EVALUACIÓN DE DIÓXIDO DE AZUFRE (SO2) EN EL AMBIENTE',
                'rule' => 'max',
            ],
            'as' => [
                'key' => 'as',
                'name' => 'Arsénico Inorgánico',
                'formula' => 'As',
                'unit' => 'mg/m3',
                'tlv' => '0,01',
                'tlv_range' => '0,01',
                'tlv_max' => 0.01,
                'eval_title' => 'EVALUACIÓN DE ARSÉNICO (As) EN EL AMBIENTE',
                'rule' => 'max',
            ],
        ];
    }

    /**
     * Vista de reporte técnico / informe oficial de monitoreo de gases en formato vertical Carta con Stepper dinámico.
     */
    public function showReport($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        $project = $module->project;
        $company = $project ? $project->company : null;
        $razonSocial = '';
        if ($project) {
            if (!empty($project->razon_social)) {
                $razonSocial = $project->razon_social;
            } elseif ($company && !empty($company->legal_name)) {
                $razonSocial = $company->legal_name;
            } elseif ($company && !empty($company->name)) {
                $razonSocial = $company->name;
            } else {
                $razonSocial = $project->name;
            }
        }

        $oldDefault = ($project ? $project->name : '') . ($company ? " - {$company->name}" : '');
        if (empty($module->installation_name) || $module->installation_name === $oldDefault || $module->installation_name === 'PACHABOL - PLANTA CENTRAL EMV') {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'Instalación');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));
        $monitoringType = $module->monitoring_type ?: 'Seguimiento de Gases';

        $equipment = $module->equipment;
        $reportSettings = $module->photo_report_settings ?: [];

        $equipmentName = !empty($reportSettings['equipment_name']) ? $reportSettings['equipment_name'] : ($equipment ? ($equipment->name ?: 'DETECTOR MULTIGAS') : ($module->calibration_equipment ?: 'DETECTOR MULTIGAS'));
        $equipmentBrand = !empty($reportSettings['equipment_brand']) ? $reportSettings['equipment_brand'] : ($equipment ? ($equipment->brand ?: 'Industrial Scientific') : 'Industrial Scientific');
        $equipmentModel = !empty($reportSettings['equipment_model']) ? $reportSettings['equipment_model'] : ($equipment ? ($equipment->model ?: 'MX4 Ventis') : 'MX4 Ventis');
        $equipmentSerial = !empty($reportSettings['equipment_serial']) ? $reportSettings['equipment_serial'] : ($equipment ? ($equipment->serial_number ?: '200428-001') : '200428-001');

        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Campo';
        }

        $measurementsList = $module->gasMeasurements()->with('staff')->get();
        $gasesDefs = self::getGasesDefinitions();
        $gasesStandards = self::getGasesStandards();

        // 1. Detectar dinámicamente qué tipos de gases tienen mediciones en este módulo
        $detectedGasKeys = [];
        foreach ($measurementsList as $m) {
            $gr = is_array($m->gases_readings) ? $m->gases_readings : (json_decode($m->gases_readings, true) ?: []);
            $sg = is_array($m->selected_gases) ? $m->selected_gases : (json_decode($m->selected_gases, true) ?: []);

            foreach ($gasesStandards as $gKey => $std) {
                $promCol = "{$gKey}_prom";
                $valCol = "{$gKey}_values";
                $hasVal = false;

                if ($m->$promCol !== null && is_numeric($m->$promCol)) {
                    $hasVal = true;
                } elseif (isset($gr[$gKey]) && is_array($gr[$gKey])) {
                    if (isset($gr[$gKey]['prom']) && is_numeric($gr[$gKey]['prom'])) $hasVal = true;
                    elseif (!empty($gr[$gKey]['values'])) $hasVal = true;
                    elseif (isset($gr[$gKey]['med1']) || isset($gr[$gKey]['med2']) || isset($gr[$gKey]['med3']) || isset($gr[$gKey]['m1']) || isset($gr[$gKey]['m2']) || isset($gr[$gKey]['m3'])) $hasVal = true;
                } elseif (!empty($m->$valCol)) {
                    $vArr = is_array($m->$valCol) ? $m->$valCol : json_decode($m->$valCol, true);
                    if (is_array($vArr) && count(array_filter($vArr, 'is_numeric')) > 0) $hasVal = true;
                }

                if ($hasVal && !in_array($gKey, $detectedGasKeys)) {
                    $detectedGasKeys[] = $gKey;
                }
            }

            foreach ($sg as $gk) {
                $k = strtolower(trim((string)$gk));
                if ($k && !in_array($k, $detectedGasKeys) && isset($gasesStandards[$k])) {
                    $detectedGasKeys[] = $k;
                }
            }
        }

        // Si no hay gases registrados aún, ofrecer O2 por defecto
        if (empty($detectedGasKeys)) {
            $detectedGasKeys = ['o2'];
        }

        // 2. Construir reporte de gases filtrando SOLO los puntos medidos para cada tipo de gas
        $gasReports = [];
        foreach ($detectedGasKeys as $gKey) {
            $std = $gasesStandards[$gKey] ?? [
                'key' => $gKey,
                'name' => strtoupper($gKey),
                'formula' => strtoupper($gKey),
                'unit' => 'ppm',
                'tlv' => '—',
                'tlv_range' => '—',
                'tlv_min' => null,
                'tlv_max' => 999999,
                'eval_title' => 'EVALUACIÓN DE ' . strtoupper($gKey) . ' EN EL AMBIENTE',
                'rule' => 'max',
            ];

            $gasPoints = [];
            $pointIdx = 1;

            foreach ($measurementsList as $m) {
                $avg = null;
                $promCol = "{$gKey}_prom";
                $valCol = "{$gKey}_values";
                $gr = is_array($m->gases_readings) ? $m->gases_readings : (json_decode($m->gases_readings, true) ?: []);

                if ($m->$promCol !== null && is_numeric($m->$promCol)) {
                    $avg = (float)$m->$promCol;
                } elseif (isset($gr[$gKey]) && is_array($gr[$gKey])) {
                    if (isset($gr[$gKey]['prom']) && is_numeric($gr[$gKey]['prom'])) {
                        $avg = (float)$gr[$gKey]['prom'];
                    } elseif (isset($gr[$gKey]['values']) && is_array($gr[$gKey]['values'])) {
                        $nums = array_values(array_filter($gr[$gKey]['values'], 'is_numeric'));
                        if (!empty($nums)) $avg = array_sum($nums) / count($nums);
                    } elseif (isset($gr[$gKey]['med1']) || isset($gr[$gKey]['med2']) || isset($gr[$gKey]['med3']) || isset($gr[$gKey]['m1']) || isset($gr[$gKey]['m2']) || isset($gr[$gKey]['m3'])) {
                        $m1 = $gr[$gKey]['med1'] ?? ($gr[$gKey]['m1'] ?? null);
                        $m2 = $gr[$gKey]['med2'] ?? ($gr[$gKey]['m2'] ?? null);
                        $m3 = $gr[$gKey]['med3'] ?? ($gr[$gKey]['m3'] ?? null);
                        $nums = array_values(array_filter([$m1, $m2, $m3], 'is_numeric'));
                        if (!empty($nums)) $avg = array_sum($nums) / count($nums);
                    }
                } elseif (!empty($m->$valCol)) {
                    $vArr = is_array($m->$valCol) ? $m->$valCol : json_decode($m->$valCol, true);
                    if (is_array($vArr)) {
                        $nums = array_values(array_filter($vArr, 'is_numeric'));
                        if (!empty($nums)) $avg = array_sum($nums) / count($nums);
                    }
                }

                $sg = is_array($m->selected_gases) ? $m->selected_gases : (json_decode($m->selected_gases, true) ?: []);
                $isSelected = in_array($gKey, $sg) || in_array(strtoupper($gKey), $sg);

                // Solo incluir en la tabla de este gas si tiene lecturas o fue evaluado para este gas
                if ($avg !== null || $isSelected) {
                    $cumple = '—';
                    if ($avg !== null) {
                        if ($gKey === 'o2') {
                            $cumple = ($avg >= 19.5 && $avg <= 23.5) ? 'SI' : 'NO';
                        } else {
                            $maxLimit = $std['tlv_max'] ?? 999999;
                            $cumple = ($avg <= $maxLimit) ? 'SI' : 'NO';
                        }
                    }

                    $formattedAvg = '—';
                    if ($avg !== null) {
                        $decimals = (round($avg, 1) == round($avg, 2)) ? 1 : 2;
                        if (round($avg, 0) == round($avg, 2) && $avg >= 10 && $gKey !== 'o2') $decimals = 0;
                        $formattedAvg = number_format($avg, $decimals, ',', '');
                    }

                    $gasPoints[] = [
                        'num' => $pointIdx++,
                        'original_num' => $m->point_number,
                        'area' => $m->area ?: '—',
                        'measurement_point' => $m->measurement_point ?: ($m->workstation ?: '—'),
                        'avg_val' => $avg,
                        'formatted_avg' => $formattedAvg,
                        'tlv' => $std['tlv'],
                        'cumple' => $cumple,
                    ];
                }
            }

            if (!empty($gasPoints) || count($detectedGasKeys) === 1) {
                $gasReports[$gKey] = [
                    'info' => $std,
                    'points' => $gasPoints,
                ];
            }
        }

        return view('measurements.gases.report', compact(
            'module',
            'installationName',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'registeredByHeader',
            'measurementsList',
            'gasesDefs',
            'gasesStandards',
            'gasReports',
            'reportSettings',
            'userName',
            'userRole'
        ));
    }

    /**
     * Guarda modificaciones en línea del informe técnico de gases.
     */
    public function saveReportData(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

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

        $settings = $module->photo_report_settings ?: [];
        if ($request->has('equipment_name')) {
            $settings['equipment_name'] = $request->input('equipment_name');
        }
        if ($request->has('equipment_brand')) {
            $settings['equipment_brand'] = $request->input('equipment_brand');
        }
        if ($request->has('equipment_model')) {
            $settings['equipment_model'] = $request->input('equipment_model');
        }
        if ($request->has('equipment_serial')) {
            $settings['equipment_serial'] = $request->input('equipment_serial');
        }
        $module->photo_report_settings = $settings;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Informe de gases guardado correctamente.',
        ]);
    }

    /**
     * Helper: Extrae columnas individuales de gas desde gases_readings.
     */
    protected function extractGasColumnsFromReadings($gasesReadings, Request $request)
    {
        $defs = self::getGasesDefinitions();
        $cols = [];

        foreach ($defs as $def) {
            $k = $def['key'];
            $valKey = "{$k}_values";
            $promKey = "{$k}_prom";

            if (is_array($gasesReadings) && isset($gasesReadings[$k])) {
                $gData = $gasesReadings[$k];
                $values = [];
                if (isset($gData['values']) && is_array($gData['values'])) {
                    $values = array_values(array_map('floatval', array_filter($gData['values'], 'is_numeric')));
                } elseif (isset($gData['med1']) || isset($gData['med2']) || isset($gData['med3'])) {
                    $m1 = isset($gData['med1']) && is_numeric($gData['med1']) ? (float)$gData['med1'] : null;
                    $m2 = isset($gData['med2']) && is_numeric($gData['med2']) ? (float)$gData['med2'] : null;
                    $m3 = isset($gData['med3']) && is_numeric($gData['med3']) ? (float)$gData['med3'] : null;
                    $values = array_values(array_filter([$m1, $m2, $m3], fn($x) => $x !== null));
                }

                $prom = isset($gData['prom']) && is_numeric($gData['prom'])
                    ? (float)$gData['prom']
                    : (!empty($values) ? array_sum($values) / count($values) : null);

                $cols[$valKey] = !empty($values) ? $values : null;
                $cols[$promKey] = $prom !== null ? round($prom, 3) : null;
            } elseif ($request->has($valKey) || $request->has($promKey)) {
                $rawVals = $request->input($valKey);
                $values = is_array($rawVals) ? $rawVals : json_decode($rawVals, true);
                if (is_array($values)) {
                    $values = array_values(array_map('floatval', array_filter($values, 'is_numeric')));
                    $cols[$valKey] = !empty($values) ? $values : null;
                    $cols[$promKey] = !empty($values) ? round(array_sum($values) / count($values), 3) : null;
                }
            }
        }

        return $cols;
    }

    /**
     * Helper: Parsea string JSON o array de forma segura.
     */
    protected function parseJsonInput($input)
    {
        if (empty($input)) return null;
        if (is_array($input)) return $input;
        if (is_string($input)) {
            $decoded = json_decode($input, true);
            return is_array($decoded) ? $decoded : null;
        }
        return null;
    }

    /**
     * Guarda adaptativamente imágenes respetando nitidez.
     */
    protected function saveAdaptiveImage($uploadedFile, $uploadDir)
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $safeExt = ($extension === 'jpeg' || $extension === 'jpg') ? 'jpg' : $extension;
        $fileName = 'gas_' . time() . '_' . uniqid() . '.' . $safeExt;
        $destinationPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        $relativePath = 'uploads/measurements/' . $fileName;

        $sourcePath = $uploadedFile->getRealPath();
        $fileSize = $uploadedFile->getSize();

        $imgInfo = @getimagesize($sourcePath);
        if (!$imgInfo) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        $srcWidth = $imgInfo[0];
        $srcHeight = $imgInfo[1];
        $imageType = $imgInfo[2];
        $maxDimension = max($srcWidth, $srcHeight);

        if ($fileSize <= 1.2 * 1024 * 1024 && $maxDimension <= 1920) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        if ($fileSize <= 4 * 1024 * 1024 && $maxDimension <= 2800) {
            $targetMaxDim = 2560;
            $quality = 92;
        } elseif ($fileSize <= 9 * 1024 * 1024 && $maxDimension <= 4500) {
            $targetMaxDim = 2200;
            $quality = 90;
        } else {
            $targetMaxDim = 2048;
            $quality = 88;
        }

        if ($maxDimension > $targetMaxDim) {
            if ($srcWidth >= $srcHeight) {
                $targetWidth = $targetMaxDim;
                $targetHeight = (int) round(($srcHeight * $targetMaxDim) / $srcWidth);
            } else {
                $targetHeight = $targetMaxDim;
                $targetWidth = (int) round(($srcWidth * $targetMaxDim) / $srcHeight);
            }
        } else {
            $targetWidth = $srcWidth;
            $targetHeight = $srcHeight;
        }

        $srcImage = null;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $srcImage = @imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = @imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $srcImage = @imagecreatefromwebp($sourcePath);
                }
                break;
        }

        if (!$srcImage) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $transparent);
        } else {
            $white = imagecolorallocate($dstImage, 255, 255, 255);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $white);
        }

        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);

        $outputFileName = 'gas_' . time() . '_' . uniqid() . '.jpg';
        $outputDestPath = $uploadDir . DIRECTORY_SEPARATOR . $outputFileName;
        $saved = imagejpeg($dstImage, $outputDestPath, $quality);

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        if ($saved && file_exists($outputDestPath)) {
            return 'uploads/measurements/' . $outputFileName;
        }

        $uploadedFile->move($uploadDir, $fileName);
        return $relativePath;
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
}
