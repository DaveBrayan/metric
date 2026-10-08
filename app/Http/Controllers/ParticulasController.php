<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\ParticulasMeasurement;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ParticulasController extends Controller
{
    /**
     * Umbrales Normativos según ACGIH (TLVs) para Material Particulado.
     */
    public const PM10_LIMIT = 10.0; // µg/m³ (Fracción Inhalable 10(I))
    public const PM25_LIMIT = 3.0;  // µg/m³ (Fracción Respirable 3(R))

    /**
     * Muestra la página principal del módulo de Partículas Ocupacionales.
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
        if (empty($module->installation_name) || $module->installation_name === $oldDefault) {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'PAPELBOL - VILLA TUNARI');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        // Fechas del módulo
        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Seguimiento';

        // Equipo asignado
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Medidor de Partículas');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'TEMTOP') : 'TEMTOP';
        $equipmentModel = $equipment ? ($equipment->model ?: 'LKC-1000 Series') : 'LKC-1000 Series';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '23.08.10 QQ') : '23.08.10 QQ';
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
        $measurements = $module->particulasMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

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
                }
            }

            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_urls)) {
                $rawImages = is_array($item->image_urls) ? $item->image_urls : (json_decode($item->image_urls, true) ?: []);
            }
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function ($p) {
                return $p ? (str_starts_with($p, 'http') ? $p : asset($p)) : null;
            }, $rawImages)));

            $pm10Values = is_array($item->pm10_values) ? $item->pm10_values : (json_decode($item->pm10_values, true) ?: []);
            $pm25Values = is_array($item->pm25_values) ? $item->pm25_values : (json_decode($item->pm25_values, true) ?: []);

            $pm10Prom = $item->pm10_prom !== null ? (float)$item->pm10_prom : null;
            $pm25Prom = $item->pm25_prom !== null ? (float)$item->pm25_prom : null;

            $pm10Cumple = $pm10Prom !== null ? ($pm10Prom <= self::PM10_LIMIT) : null;
            $pm25Cumple = $pm25Prom !== null ? ($pm25Prom <= self::PM25_LIMIT) : null;

            return [
                'id' => $item->id,
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '—',
                'area' => $item->area ?: '—',
                'punto_medicion' => $item->punto_medicion ?: ($item->workstation ?: '—'),
                'workstation' => $item->punto_medicion ?: ($item->workstation ?: '—'),
                'temperatura' => $item->temperatura !== null ? (float)$item->temperatura : null,
                'hr_percent' => $item->hr_percent !== null ? (float)$item->hr_percent : null,
                'pm10_values' => $pm10Values,
                'pm10_prom' => $pm10Prom,
                'pm10_cumple' => $pm10Cumple,
                'pm25_values' => $pm25Values,
                'pm25_prom' => $pm25Prom,
                'pm25_cumple' => $pm25Cumple,
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

        // Estadísticas
        $compliantCount = $measurements->filter(fn($m) => ($m['pm10_cumple'] === true || $m['pm10_cumple'] === null) && ($m['pm25_cumple'] === true || $m['pm25_cumple'] === null))->count();
        $complianceRate = $totalMeasurements > 0 ? round(($compliantCount / $totalMeasurements) * 100, 1) : 100.0;

        return view('measurements.particulas.index', compact(
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
            'compliantCount',
            'complianceRate',
            'photoReportSettings'
        ));
    }

    /**
     * Muestra el Informe Oficial de Partículas Ocupacionales con Stepper (2 Pasos en Tamaño Carta Vertical).
     *
     * Paso 1: PUNTOS DE MEDICIÓN (Tabla limpia: N° | Área | Punto de medición | Coordenadas UTM E, N)
     * Paso 2: EVALUACIÓN DE RIESGOS (Metadatos equipo + Tabla completa de riesgos con PM10 y PM2.5, límites ACGIH y cumplimiento)
     */
    public function showReport($moduleId)
    {
        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Identificación de la instalación
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
        if (empty($module->installation_name) || $module->installation_name === $oldDefault) {
            $installationName = $razonSocial ?: ($module->installation_name ?: 'PAPELBOL - VILLA TUNARI');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : Carbon::now()->format('d/m/Y');
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : $startDateFormatted;
        $monitoringType = $module->monitoring_type ?: 'Seguimiento';

        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'MEDIDOR DE PARTICULAS');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'TEMTOP') : 'TEMTOP';
        $equipmentModel = $equipment ? ($equipment->model ?: 'LKC-1000 Series') : 'LKC-1000 Series';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '23.08.10 QQ') : '23.08.10 QQ';

        $assignedStaff = $module->getAssignedStaffAttribute();
        $registeredByHeader = ($assignedStaff && $assignedStaff->isNotEmpty())
            ? $assignedStaff->pluck('name')->implode(', ')
            : 'PACHABOL MEDIO AMBIENTE & SEGURIDAD';

        // Obtener mediciones
        $measurementsList = $module->particulasMeasurements()->orderBy('id', 'asc')->get();

        return view('measurements.particulas.report', compact(
            'module',
            'installationName',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'registeredByHeader',
            'equipmentName',
            'equipmentBrand',
            'equipmentModel',
            'equipmentSerial',
            'measurementsList'
        ));
    }

    /**
     * Guarda los datos customizados del informe de partículas vía AJAX.
     */
    public function saveReportData(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        if ($request->has('installation_name')) {
            $module->installation_name = $request->input('installation_name');
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $request->input('monitoring_type');
        }
        if ($request->has('equipment_name')) {
            $module->calibration_equipment = $request->input('equipment_name');
        }

        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Datos del informe de partículas actualizados correctamente.',
        ]);
    }

    /**
     * Registrar una nueva medición desde la interfaz web.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'punto_medicion' => 'nullable|string|max:255',
            'workstation' => 'nullable|string|max:255',
            'temperatura' => 'nullable|numeric',
            'hr_percent' => 'nullable|numeric',
            'pm10_1' => 'nullable|numeric',
            'pm10_2' => 'nullable|numeric',
            'pm10_3' => 'nullable|numeric',
            'pm25_1' => 'nullable|numeric',
            'pm25_2' => 'nullable|numeric',
            'pm25_3' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'images.*' => 'nullable|image|max:10240',
        ]);

        $puntoMedicion = $validated['punto_medicion'] ?? $validated['workstation'] ?? 'Punto de Medición';

        $pm10Nums = array_values(array_filter([
            $request->filled('pm10_1') ? (float)$request->input('pm10_1') : null,
            $request->filled('pm10_2') ? (float)$request->input('pm10_2') : null,
            $request->filled('pm10_3') ? (float)$request->input('pm10_3') : null,
        ], fn($v) => $v !== null));
        $pm10Prom = !empty($pm10Nums) ? array_sum($pm10Nums) / count($pm10Nums) : null;

        $pm25Nums = array_values(array_filter([
            $request->filled('pm25_1') ? (float)$request->input('pm25_1') : null,
            $request->filled('pm25_2') ? (float)$request->input('pm25_2') : null,
            $request->filled('pm25_3') ? (float)$request->input('pm25_3') : null,
        ], fn($v) => $v !== null));
        $pm25Prom = !empty($pm25Nums) ? array_sum($pm25Nums) / count($pm25Nums) : null;

        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $fname = 'part_' . time() . '_' . uniqid() . '.' . $ext;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }

        $pointNumber = $validated['point_number'];
        if (empty($pointNumber)) {
            $count = $module->particulasMeasurements()->count();
            $pointNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        }

        $data = [
            'module_id' => $module->id,
            'point_number' => $pointNumber,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? Carbon::now()->format('H:i'),
            'area' => $validated['area'] ?? 'General',
            'workstation' => $puntoMedicion,
            'punto_medicion' => $puntoMedicion,
            'temperatura' => $validated['temperatura'] ?? null,
            'hr_percent' => $validated['hr_percent'] ?? null,
            'pm10_values' => !empty($pm10Nums) ? $pm10Nums : null,
            'pm10_prom' => $pm10Prom,
            'pm25_values' => !empty($pm25Nums) ? $pm25Nums : null,
            'pm25_prom' => $pm25Prom,
            'utm_zone' => $validated['utm_zone'] ?? '20K',
            'utm_easting' => $validated['utm_easting'] ?? null,
            'utm_northing' => $validated['utm_northing'] ?? null,
            'observations' => $validated['observations'] ?? null,
            'staff_id' => $validated['staff_id'] ?? null,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'image_path' => !empty($uploadedImages) ? $uploadedImages[0] : null,
            'registered_by' => Auth::user() ? Auth::user()->name : 'Técnico de Campo',
        ];

        $measurement = $module->particulasMeasurements()->create($data);

        $module->points_completed = $module->particulasMeasurements()->count();
        $module->save();

        return redirect()->route('modules.particulas', $moduleId)->with('success', 'Medición de partículas registrada con éxito.');
    }

    /**
     * Actualizar una medición desde la interfaz web.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->particulasMeasurements()->findOrFail($measurementId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'punto_medicion' => 'nullable|string|max:255',
            'workstation' => 'nullable|string|max:255',
            'temperatura' => 'nullable|numeric',
            'hr_percent' => 'nullable|numeric',
            'pm10_1' => 'nullable|numeric',
            'pm10_2' => 'nullable|numeric',
            'pm10_3' => 'nullable|numeric',
            'pm25_1' => 'nullable|numeric',
            'pm25_2' => 'nullable|numeric',
            'pm25_3' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'images.*' => 'nullable|image|max:10240',
        ]);

        $puntoMedicion = $validated['punto_medicion'] ?? $validated['workstation'] ?? ($measurement->punto_medicion ?: 'Punto de Medición');

        $pm10Nums = array_values(array_filter([
            $request->filled('pm10_1') ? (float)$request->input('pm10_1') : null,
            $request->filled('pm10_2') ? (float)$request->input('pm10_2') : null,
            $request->filled('pm10_3') ? (float)$request->input('pm10_3') : null,
        ], fn($v) => $v !== null));
        $pm10Prom = !empty($pm10Nums) ? array_sum($pm10Nums) / count($pm10Nums) : $measurement->pm10_prom;

        $pm25Nums = array_values(array_filter([
            $request->filled('pm25_1') ? (float)$request->input('pm25_1') : null,
            $request->filled('pm25_2') ? (float)$request->input('pm25_2') : null,
            $request->filled('pm25_3') ? (float)$request->input('pm25_3') : null,
        ], fn($v) => $v !== null));
        $pm25Prom = !empty($pm25Nums) ? array_sum($pm25Nums) / count($pm25Nums) : $measurement->pm25_prom;

        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        $uploadedImages = $existingImages;
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $fname = 'part_' . time() . '_' . uniqid() . '.' . $ext;
                    $file->move($uploadDir, $fname);
                    $uploadedImages[] = 'uploads/measurements/' . $fname;
                }
            }
        }

        $data = [
            'point_number' => $validated['point_number'] ?? $measurement->point_number,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? $measurement->measurement_time,
            'area' => $validated['area'] ?? $measurement->area,
            'workstation' => $puntoMedicion,
            'punto_medicion' => $puntoMedicion,
            'temperatura' => $validated['temperatura'] ?? $measurement->temperatura,
            'hr_percent' => $validated['hr_percent'] ?? $measurement->hr_percent,
            'pm10_values' => !empty($pm10Nums) ? $pm10Nums : $measurement->pm10_values,
            'pm10_prom' => $pm10Prom,
            'pm25_values' => !empty($pm25Nums) ? $pm25Nums : $measurement->pm25_values,
            'pm25_prom' => $pm25Prom,
            'utm_zone' => $validated['utm_zone'] ?? $measurement->utm_zone,
            'utm_easting' => $validated['utm_easting'] ?? $measurement->utm_easting,
            'utm_northing' => $validated['utm_northing'] ?? $measurement->utm_northing,
            'observations' => $validated['observations'] ?? $measurement->observations,
            'staff_id' => $validated['staff_id'] ?? $measurement->staff_id,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'image_path' => !empty($uploadedImages) ? $uploadedImages[0] : null,
        ];

        $measurement->update($data);

        return redirect()->route('modules.particulas', $moduleId)->with('success', 'Medición actualizada correctamente.');
    }

    /**
     * Eliminar una medición.
     */
    public function destroyMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->particulasMeasurements()->findOrFail($measurementId);
        $measurement->delete();

        $module->points_completed = $module->particulasMeasurements()->count();
        $module->save();

        return redirect()->route('modules.particulas', $moduleId)->with('success', 'Medición eliminada correctamente.');
    }

    /**
     * Actualiza el encabezado general del módulo.
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        if ($request->has('installation_name')) {
            $module->installation_name = $request->input('installation_name');
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $request->input('monitoring_type');
        }

        if ($request->has('start_date')) {
            $module->start_date = $request->filled('start_date') ? $request->input('start_date') : null;
        }
        if ($request->has('end_date')) {
            $module->end_date = $request->filled('end_date') ? $request->input('end_date') : null;
        }

        if ($request->filled('equipment_id')) {
            $module->equipment_id = $request->input('equipment_id');
        }

        if ($request->filled('field_staff_ids')) {
            $ids = $request->input('field_staff_ids');
            $module->field_staff_ids = is_array($ids) ? array_map('intval', $ids) : [intval($ids)];
        }

        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Encabezado técnico de partículas actualizado correctamente.',
                'data' => [
                    'installation_name' => $module->installation_name,
                    'start_date' => $module->start_date ? $module->start_date->format('Y-m-d') : '',
                    'end_date' => $module->end_date ? $module->end_date->format('Y-m-d') : '',
                    'monitoring_type' => $module->monitoring_type,
                ]
            ]);
        }

        return redirect()->route('modules.particulas', $moduleId)->with('success', 'Encabezado del módulo actualizado.');
    }

    /**
     * Guarda la configuración del reporte fotográfico.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $module->photo_report_settings = [
            'grid' => $request->input('grid', '2x3'),
            'orientation' => $request->input('orientation', 'landscape'),
            'selected_points' => $request->input('selected_points', []),
            'photo_indices' => $request->input('photo_indices', []),
        ];

        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de fotos guardada correctamente.',
        ]);
    }

    /**
     * Helper UTM a LatLng para mapas de geolocalización.
     */
    private function utmToLatLng($easting, $northing, $zoneStr = '20K')
    {
        $zoneNumber = 20;
        $isSouth = true;
        if (preg_match('/^([0-9]{1,2})\s*([C-X]?)$/i', trim($zoneStr), $matches)) {
            $zoneNumber = (int) $matches[1];
            $band = strtoupper($matches[2] ?? '');
            if ($band !== '') {
                $isSouth = ($band < 'N');
            }
        }

        $a = 6378137.0;
        $f = 1 / 298.257223563;
        $k0 = 0.9996;
        $e = sqrt(2 * $f - $f * $f);
        $e1 = (1 - sqrt(1 - $e * $e)) / (1 + sqrt(1 - $e * $e));

        $x = (float) $easting - 500000.0;
        $y = (float) $northing;
        if ($isSouth) {
            $y -= 10000000.0;
        }

        $M = $y / $k0;
        $mu = $M / ($a * (1 - $e * $e / 4 - 3 * $e * $e * $e * $e / 64 - 5 * $e * $e * $e * $e * $e * $e / 256));

        $phi1Rad = $mu + (3 * $e1 / 2 - 27 * pow($e1, 3) / 32) * sin(2 * $mu)
            + (21 * $e1 * $e1 / 16 - 55 * pow($e1, 4) / 32) * sin(4 * $mu)
            + (151 * pow($e1, 3) / 96) * sin(6 * $mu);

        $N1 = $a / sqrt(1 - $e * $e * sin($phi1Rad) * sin($phi1Rad));
        $T1 = tan($phi1Rad) * tan($phi1Rad);
        $C1 = ($e * $e / (1 - $e * $e)) * cos($phi1Rad) * cos($phi1Rad);
        $R1 = $a * (1 - $e * $e) / pow(1 - $e * $e * sin($phi1Rad) * sin($phi1Rad), 1.5);
        $D = $x / ($N1 * $k0);

        $latRad = $phi1Rad - ($N1 * tan($phi1Rad) / $R1) * (
            $D * $D / 2
            - (5 + 3 * $T1 + 10 * $C1 - 4 * $C1 * $C1 - 9 * ($e * $e / (1 - $e * $e))) * pow($D, 4) / 24
            + (61 + 90 * $T1 + 298 * $C1 + 45 * $T1 * $T1 - 252 * ($e * $e / (1 - $e * $e)) - 3 * $C1 * $C1) * pow($D, 6) / 720
        );

        $lonOrigin = ($zoneNumber - 1) * 6 - 180 + 3;
        $lonRad = ($D - (1 + 2 * $T1 + $C1) * pow($D, 3) / 6
            + (5 - 2 * $C1 + 28 * $T1 - 3 * $C1 * $C1 + 8 * ($e * $e / (1 - $e * $e)) + 24 * $T1 * $T1) * pow($D, 5) / 120
        ) / cos($phi1Rad);

        return [
            'lat' => round(rad2deg($latRad), 7),
            'lng' => round($lonOrigin + rad2deg($lonRad), 7),
        ];
    }
}
