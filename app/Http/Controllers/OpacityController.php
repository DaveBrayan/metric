<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\OpacityMeasurement;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class OpacityController extends Controller
{
    /**
     * Display the Opacity monitoring page for a module.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Resuelve información técnica del encabezado
        $projectName = $module->project ? $module->project->name : 'Proyecto';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : '';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Emisión de Humos Vehiculares';

        // Equipo asignado (Opacímetro / Tacómetro)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Opacímetro de Flujo Parcial');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'BRAIN BEE') : 'BRAIN BEE';
        $equipmentModel = $equipment ? ($equipment->model ?: 'OPA-100') : 'OPA-100';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'OP-88341') : 'OP-88341';
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
        $measurements = $module->opacityMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';

            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

            if (($lat === null || $lng === null) && !empty($item->location_description)) {
                $loc = trim($item->location_description);
                if (preg_match('/E:\s*([0-9.]+)/i', $loc, $mE) && preg_match('/N:\s*([0-9.]+)/i', $loc, $mN)) {
                    preg_match('/Z:\s*([0-9A-Za-z]+)/i', $loc, $mZ);
                    $conv = $this->utmToLatLng((float)$mE[1], (float)$mN[1], $mZ[1] ?? '19K');
                    $lat = $conv['lat'];
                    $lng = $conv['lng'];
                }
            }

            $staffName = $item->staff ? $item->staff->name : ($assignedStaff->first() ? $assignedStaff->first()->name : ($currentUser ? $currentUser->name : 'Técnico'));

            // Galería de fotos
            $photos = [];
            if (!empty($item->photo_paths) && is_array($item->photo_paths)) {
                foreach ($item->photo_paths as $p) {
                    $photos[] = asset($p);
                }
            }

            return [
                'id' => $item->id,
                'num' => $index + 1,
                'point_number' => $item->point_number ?: ('V-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT)),
                'date_formatted' => $dateFormatted,
                'date_raw' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time_raw' => $item->measurement_time ?: '',
                'tipo_vehiculo' => $item->tipo_vehiculo ?: 'Vehículo',
                'marca' => $item->marca ?: '',
                'modelo' => $item->modelo ?: '',
                'placa' => $item->placa ?: '',
                'temp_c' => $item->temp_c !== null ? (float) $item->temp_c : null,
                'opa_1' => $item->opa_1 !== null ? (float) $item->opa_1 : null,
                'opa_2' => $item->opa_2 !== null ? (float) $item->opa_2 : null,
                'opa_3' => $item->opa_3 !== null ? (float) $item->opa_3 : null,
                'rpm_1' => $item->rpm_1 !== null ? (float) $item->rpm_1 : null,
                'rpm_2' => $item->rpm_2 !== null ? (float) $item->rpm_2 : null,
                'rpm_3' => $item->rpm_3 !== null ? (float) $item->rpm_3 : null,
                'opa_promedio' => $item->opa_promedio !== null ? (float) $item->opa_promedio : null,
                'rpm_promedio' => $item->rpm_promedio !== null ? (float) $item->rpm_promedio : null,
                'limite_normativa' => $item->limite_normativa !== null ? (float) $item->limite_normativa : 50.0,
                'is_compliant' => (bool) $item->is_compliant,
                'latitude' => $lat,
                'longitude' => $lng,
                'utm_zone' => $item->utm_zone ?: '19K',
                'utm_easting' => $item->utm_easting !== null ? (float) $item->utm_easting : null,
                'utm_northing' => $item->utm_northing !== null ? (float) $item->utm_northing : null,
                'location_description' => $item->location_description ?: '',
                'observations' => $item->observations ?: '',
                'staff_id' => $item->staff_id,
                'staff_name' => $staffName,
                'photos' => $photos,
            ];
        });

        $totalMeasurements = $measurements->count();
        $compliantCount = $measurements->where('is_compliant', true)->count();
        $nonCompliantCount = $totalMeasurements - $compliantCount;

        $photoReportSettings = $module->photo_report_settings ?: [];

        $project = $module->project;

        return view('measurements.opacidades.index', compact(
            'module',
            'project',
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
            'compliantCount',
            'nonCompliantCount',
            'photoReportSettings',
            'userName',
            'userRole'
        ));
    }

    /**
     * Store a new Opacity measurement.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $request->validate([
            'tipo_vehiculo' => 'required|string|max:255',
            'placa' => 'required|string|max:50',
            'measurement_date' => 'nullable|date',
            'limite_normativa' => 'nullable|numeric',
        ]);

        $opa1 = $request->filled('opa_1') ? (float) $request->input('opa_1') : null;
        $opa2 = $request->filled('opa_2') ? (float) $request->input('opa_2') : null;
        $opa3 = $request->filled('opa_3') ? (float) $request->input('opa_3') : null;

        $rpm1 = $request->filled('rpm_1') ? (float) $request->input('rpm_1') : null;
        $rpm2 = $request->filled('rpm_2') ? (float) $request->input('rpm_2') : null;
        $rpm3 = $request->filled('rpm_3') ? (float) $request->input('rpm_3') : null;

        // Calcula promedio de opacidad
        $opaVals = array_values(array_filter([$opa1, $opa2, $opa3], fn($v) => $v !== null));
        $opaPromedio = count($opaVals) > 0 ? round(array_sum($opaVals) / count($opaVals), 2) : ($request->filled('opa_promedio') ? (float) $request->input('opa_promedio') : null);

        // Calcula promedio de RPM
        $rpmVals = array_values(array_filter([$rpm1, $rpm2, $rpm3], fn($v) => $v !== null));
        $rpmPromedio = count($rpmVals) > 0 ? round(array_sum($rpmVals) / count($rpmVals), 0) : ($request->filled('rpm_promedio') ? (float) $request->input('rpm_promedio') : null);

        $limite = $request->filled('limite_normativa') ? (float) $request->input('limite_normativa') : 50.0;
        $isCompliant = ($opaPromedio !== null) ? ($opaPromedio <= $limite) : true;

        // Subida de fotografías
        $photoPaths = [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/opacity');
            if (!File::isDirectory($destPath)) {
                File::makeDirectory($destPath, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fileName = 'opa_' . $module->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $fileName);
                    $photoPaths[] = 'uploads/measurements/opacity/' . $fileName;
                }
            }
        }

        $nextIndex = $module->opacityMeasurements()->count() + 1;
        $pointNum = $request->input('point_number') ?: ('V-' . str_pad($nextIndex, 2, '0', STR_PAD_LEFT));

        $measurement = new OpacityMeasurement();
        $measurement->module_id = $module->id;
        $measurement->project_id = $module->project_id;
        $measurement->staff_id = $request->input('staff_id') ?: ($module->field_staff_id ?: (Auth::id() ?? null));
        $measurement->point_number = $pointNum;
        $measurement->measurement_date = $request->input('measurement_date') ?: Carbon::today();
        $measurement->measurement_time = $request->input('measurement_time') ?: Carbon::now()->format('H:i');

        $measurement->tipo_vehiculo = $request->input('tipo_vehiculo');
        $measurement->marca = $request->input('marca');
        $measurement->modelo = $request->input('modelo');
        $measurement->placa = strtoupper(trim($request->input('placa')));

        $measurement->temp_c = $request->filled('temp_c') ? (float) $request->input('temp_c') : null;
        $measurement->opa_1 = $opa1;
        $measurement->opa_2 = $opa2;
        $measurement->opa_3 = $opa3;
        $measurement->rpm_1 = $rpm1;
        $measurement->rpm_2 = $rpm2;
        $measurement->rpm_3 = $rpm3;
        $measurement->opa_promedio = $opaPromedio;
        $measurement->rpm_promedio = $rpmPromedio;
        $measurement->limite_normativa = $limite;
        $measurement->is_compliant = $isCompliant;

        $measurement->latitude = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        $measurement->longitude = $request->filled('longitude') ? (float) $request->input('longitude') : null;
        $measurement->utm_zone = $request->input('utm_zone', '19K');
        $measurement->utm_easting = $request->filled('utm_easting') ? (float) $request->input('utm_easting') : null;
        $measurement->utm_northing = $request->filled('utm_northing') ? (float) $request->input('utm_northing') : null;
        $measurement->location_description = $request->input('location_description');
        $measurement->observations = $request->input('observations');
        $measurement->photo_paths = $photoPaths;

        $measurement->save();

        // Actualiza puntos completados en módulo
        $module->points_completed = $module->opacityMeasurements()->count();
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punto de medición de opacidad registrado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.opacity', $module->id)
            ->with('success', '¡Punto de medición de opacidad registrado correctamente!');
    }

    /**
     * Update an existing Opacity measurement.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = OpacityMeasurement::where('module_id', $module->id)->findOrFail($measurementId);

        $request->validate([
            'tipo_vehiculo' => 'required|string|max:255',
            'placa' => 'required|string|max:50',
            'measurement_date' => 'nullable|date',
            'limite_normativa' => 'nullable|numeric',
        ]);

        $opa1 = $request->filled('opa_1') ? (float) $request->input('opa_1') : null;
        $opa2 = $request->filled('opa_2') ? (float) $request->input('opa_2') : null;
        $opa3 = $request->filled('opa_3') ? (float) $request->input('opa_3') : null;

        $rpm1 = $request->filled('rpm_1') ? (float) $request->input('rpm_1') : null;
        $rpm2 = $request->filled('rpm_2') ? (float) $request->input('rpm_2') : null;
        $rpm3 = $request->filled('rpm_3') ? (float) $request->input('rpm_3') : null;

        $opaVals = array_values(array_filter([$opa1, $opa2, $opa3], fn($v) => $v !== null));
        $opaPromedio = count($opaVals) > 0 ? round(array_sum($opaVals) / count($opaVals), 2) : ($request->filled('opa_promedio') ? (float) $request->input('opa_promedio') : $measurement->opa_promedio);

        $rpmVals = array_values(array_filter([$rpm1, $rpm2, $rpm3], fn($v) => $v !== null));
        $rpmPromedio = count($rpmVals) > 0 ? round(array_sum($rpmVals) / count($rpmVals), 0) : ($request->filled('rpm_promedio') ? (float) $request->input('rpm_promedio') : $measurement->rpm_promedio);

        $limite = $request->filled('limite_normativa') ? (float) $request->input('limite_normativa') : ($measurement->limite_normativa ?: 50.0);
        $isCompliant = ($opaPromedio !== null) ? ($opaPromedio <= $limite) : true;

        $photoPaths = $measurement->photo_paths ?: [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/opacity');
            if (!File::isDirectory($destPath)) {
                File::makeDirectory($destPath, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fileName = 'opa_' . $module->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $fileName);
                    $photoPaths[] = 'uploads/measurements/opacity/' . $fileName;
                }
            }
        }

        $measurement->staff_id = $request->input('staff_id') ?: $measurement->staff_id;
        if ($request->filled('measurement_date')) {
            $measurement->measurement_date = $request->input('measurement_date');
        }
        if ($request->filled('measurement_time')) {
            $measurement->measurement_time = $request->input('measurement_time');
        }

        $measurement->tipo_vehiculo = $request->input('tipo_vehiculo');
        $measurement->marca = $request->input('marca');
        $measurement->modelo = $request->input('modelo');
        $measurement->placa = strtoupper(trim($request->input('placa')));

        $measurement->temp_c = $request->filled('temp_c') ? (float) $request->input('temp_c') : $measurement->temp_c;
        $measurement->opa_1 = $opa1;
        $measurement->opa_2 = $opa2;
        $measurement->opa_3 = $opa3;
        $measurement->rpm_1 = $rpm1;
        $measurement->rpm_2 = $rpm2;
        $measurement->rpm_3 = $rpm3;
        $measurement->opa_promedio = $opaPromedio;
        $measurement->rpm_promedio = $rpmPromedio;
        $measurement->limite_normativa = $limite;
        $measurement->is_compliant = $isCompliant;

        if ($request->filled('latitude')) {
            $measurement->latitude = (float) $request->input('latitude');
        }
        if ($request->filled('longitude')) {
            $measurement->longitude = (float) $request->input('longitude');
        }
        if ($request->filled('utm_zone')) {
            $measurement->utm_zone = $request->input('utm_zone');
        }
        if ($request->filled('utm_easting')) {
            $measurement->utm_easting = (float) $request->input('utm_easting');
        }
        if ($request->filled('utm_northing')) {
            $measurement->utm_northing = (float) $request->input('utm_northing');
        }
        if ($request->filled('location_description')) {
            $measurement->location_description = $request->input('location_description');
        }
        if ($request->filled('observations')) {
            $measurement->observations = $request->input('observations');
        }

        $measurement->photo_paths = $photoPaths;
        $measurement->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punto de medición de opacidad actualizado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.opacity', $module->id)
            ->with('success', '¡Punto de medición de opacidad actualizado correctamente!');
    }

    /**
     * Delete an Opacity measurement.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = OpacityMeasurement::where('module_id', $module->id)->findOrFail($measurementId);

        $measurement->delete();

        $module->points_completed = $module->opacityMeasurements()->count();
        $module->save();

        return redirect()->route('modules.opacity', $module->id)
            ->with('success', 'Punto de medición de opacidad eliminado.');
    }

    /**
     * Auto-save direct inline editing for header technical fields.
     */
    public function updateHeader(Request $request, $moduleId)
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

        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Encabezado técnico de opacidad guardado correctamente.',
        ]);
    }

    /**
     * Save photo report settings.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $module->photo_report_settings = $request->input('settings', []);
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de reporte fotográfico guardada.',
        ]);
    }

    /**
     * Utility: Convert UTM coordinates (WGS84 Zone 19S / 20S) to Latitude/Longitude.
     */
    private function utmToLatLng(float $easting, float $northing, string $zoneStr = '19K'): array
    {
        $zone = (int) preg_replace('/[^0-9]/', '', $zoneStr);
        if ($zone === 0) $zone = 19;

        $a = 6378137.0;
        $f = 1 / 298.257223563;
        $k0 = 0.9996;
        $e = sqrt(2 * $f - $f * $f);
        $e1 = (1 - sqrt(1 - $e * $e)) / (1 + sqrt(1 - $e * $e));

        $x = $easting - 500000.0;
        $y = $northing - 10000000.0;

        $m = $y / $k0;
        $mu = $m / ($a * (1 - ($e * $e) / 4 - 3 * ($e ** 4) / 64 - 5 * ($e ** 6) / 256));

        $phi1Rad = $mu + (3 * $e1 / 2 - 27 * ($e1 ** 3) / 32) * sin(2 * $mu)
            + (21 * ($e1 ** 2) / 16 - 55 * ($e1 ** 4) / 32) * sin(4 * $mu)
            + (151 * ($e1 ** 3) / 96) * sin(6 * $mu);

        $n1 = $a / sqrt(1 - ($e * sin($phi1Rad)) ** 2);
        $t1 = tan($phi1Rad) ** 2;
        $c1 = ($e * $e / (1 - $e * $e)) * (cos($phi1Rad) ** 2);
        $r1 = $a * (1 - $e * $e) / ((1 - ($e * sin($phi1Rad)) ** 2) ** 1.5);
        $d = $x / ($n1 * $k0);

        $lat = $phi1Rad - ($n1 * tan($phi1Rad) / $r1) * (
            ($d * $d) / 2 - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 * $c1 - 9 * ($e * $e / (1 - $e * $e))) * ($d ** 4) / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 * $t1 - 252 * ($e * $e / (1 - $e * $e)) - 3 * $c1 * $c1) * ($d ** 6) / 720
        );
        $lat = rad2deg($lat);

        $lng = ($d - (1 + 2 * $t1 + $c1) * ($d ** 3) / 6 + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 * $c1 + 8 * ($e * $e / (1 - $e * $e)) + 24 * $t1 * $t1) * ($d ** 5) / 120) / cos($phi1Rad);
        $lng = ($zone - 1) * 6 - 180 + 3 + rad2deg($lng);

        return ['lat' => round($lat, 6), 'lng' => round($lng, 6)];
    }
}
