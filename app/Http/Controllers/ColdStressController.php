<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\ColdStressMeasurement;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ColdStressController extends Controller
{
    /**
     * Calcula el Índice de Enfriamiento por Viento (WCI) y la Sensación Térmica (°C).
     */
    public static function calculateColdStress(?float $tempC, ?float $velMs): array
    {
        if ($tempC === null) {
            return ['wci' => null, 'sensacion' => null, 'riesgo' => 'Normal', 'tle' => 'Jornada normal'];
        }

        $v = ($velMs !== null && $velMs > 0) ? $velMs : 0.2; // Velocidad mínima en reposo

        // Fórmula WCI (Siple-Passel): WCI = (10.45 + 10*sqrt(v) - v) * (33 - temp)
        $wci = (10.45 + 10.0 * sqrt($v) - $v) * (33.0 - $tempC);
        $wci = round($wci, 1);

        // Sensación Térmica (Wind Chill Temperature en °C)
        $vKmh = $v * 3.6;
        if ($vKmh > 4.8 && $tempC <= 10.0) {
            $sensacion = 13.12 + 0.6215 * $tempC - 11.37 * pow($vKmh, 0.16) + 0.3965 * $tempC * pow($vKmh, 0.16);
            $sensacion = round($sensacion, 1);
        } else {
            $sensacion = round($tempC - ($v * 1.2), 1);
        }

        // Evaluación de Nivel de Riesgo y TLE (Tiempo Límite de Exposición)
        if ($wci < 1000) {
            $riesgo = 'Bajo (Aceptable)';
            $tle = 'Jornada continua con EPP estándar';
            $compliant = true;
        } elseif ($wci < 1200) {
            $riesgo = 'Moderado';
            $tle = '45 a 60 min de trabajo / 10 min de recuperación cálida';
            $compliant = true;
        } elseif ($wci < 1400) {
            $riesgo = 'Alto (Peligro de Congelación)';
            $tle = 'Máximo 30 min continuo / Ropa térmica certificada';
            $compliant = false;
        } else {
            $riesgo = 'Severo / Extremo';
            $tle = 'Máximo 15 min / Prohibido trabajo en solitario';
            $compliant = false;
        }

        return [
            'wci' => $wci,
            'sensacion' => $sensacion,
            'riesgo' => $riesgo,
            'tle' => $tle,
            'compliant' => $compliant,
        ];
    }

    /**
     * Display the Cold Stress monitoring page for a module.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Encabezado técnico
        $projectName = $module->project ? $module->project->name : 'Proyecto';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : '';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Evaluación de Estrés Térmico por Frío';

        // Equipo asignado (Termo-anemómetro / Monitor de Frío)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Termo-Anemómetro Digital');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'TESTO') : 'TESTO';
        $equipmentModel = $equipment ? ($equipment->model ?: 'Testo 417') : 'Testo 417';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'TC-90412') : 'TC-90412';
        $equipmentImage = ($equipment && $equipment->image) ? asset($equipment->image) : null;

        // Personal asignado
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Campo';
            $staffList = Staff::orderBy('name')->get();
        }

        // Mediciones registradas
        $measurements = $module->coldStressMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
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

            $photos = [];
            if (!empty($item->photo_paths) && is_array($item->photo_paths)) {
                foreach ($item->photo_paths as $p) {
                    $photos[] = asset($p);
                }
            }

            return [
                'id' => $item->id,
                'num' => $index + 1,
                'point_number' => (string) (intval(preg_replace('/[^0-9]/', '', (string)$item->point_number)) ?: ($index + 1)),
                'date_formatted' => $dateFormatted,
                'date_raw' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time_raw' => $item->measurement_time ?: '',
                'area' => $item->area ?: 'Área Operativa',
                'puesto_trabajo' => $item->puesto_trabajo ?: 'Operador',
                'desc_actividades' => $item->desc_actividades ?: '',
                'temp_c' => $item->temp_c !== null ? (float) $item->temp_c : null,
                'hr_percent' => $item->hr_percent !== null ? (float) $item->hr_percent : null,
                'vel_viento_ms' => $item->vel_viento_ms !== null ? (float) $item->vel_viento_ms : null,
                'presion_mmhg' => $item->presion_mmhg !== null ? (float) $item->presion_mmhg : null,
                'metabolismo' => $item->metabolismo ?: 'Metabolismo moderado',
                'aislamiento' => $item->aislamiento ?: 'Vestimenta térmica estándar',
                'indice_viento_wci' => $item->indice_viento_wci !== null ? (float) $item->indice_viento_wci : null,
                'sensacion_termica_c' => $item->sensacion_termica_c !== null ? (float) $item->sensacion_termica_c : null,
                'nivel_riesgo' => $item->nivel_riesgo ?: 'Bajo',
                'tiempo_limite_exposicion' => $item->tiempo_limite_exposicion ?: 'Jornada normal',
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

        $project = $module->project;
        $photoReportSettings = $module->photo_report_settings ?: [];

        return view('measurements.estres_frios.index', compact(
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
     * Store a new Cold Stress measurement.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $request->validate([
            'puesto_trabajo' => 'required|string|max:255',
            'temp_c' => 'required|numeric',
        ]);

        $tempC = (float) $request->input('temp_c');
        $velViento = $request->filled('vel_viento_ms') ? (float) $request->input('vel_viento_ms') : null;

        $eval = self::calculateColdStress($tempC, $velViento);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/cold_stress');
            if (!File::isDirectory($destPath)) {
                File::makeDirectory($destPath, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fileName = 'cold_' . $module->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $fileName);
                    $photoPaths[] = 'uploads/measurements/cold_stress/' . $fileName;
                }
            }
        }

        $nextIndex = $module->coldStressMeasurements()->count() + 1;
        $cleanReqNum = intval(preg_replace('/[^0-9]/', '', (string)$request->input('point_number', '')));
        $pointNum = (string) ($cleanReqNum ?: $nextIndex);

        $measurement = new ColdStressMeasurement();
        $measurement->module_id = $module->id;
        $measurement->project_id = $module->project_id;
        $measurement->staff_id = $request->input('staff_id') ?: ($module->field_staff_id ?: (Auth::id() ?? null));
        $measurement->point_number = $pointNum;
        $measurement->measurement_date = $request->input('measurement_date') ?: Carbon::today();
        $measurement->measurement_time = $request->input('measurement_time') ?: Carbon::now()->format('H:i');

        $measurement->area = $request->input('area');
        $measurement->puesto_trabajo = $request->input('puesto_trabajo');
        $measurement->desc_actividades = $request->input('desc_actividades');

        $measurement->temp_c = $tempC;
        $measurement->hr_percent = $request->filled('hr_percent') ? (float) $request->input('hr_percent') : null;
        $measurement->vel_viento_ms = $velViento;
        $measurement->presion_mmhg = $request->filled('presion_mmhg') ? (float) $request->input('presion_mmhg') : null;

        $measurement->metabolismo = $request->input('metabolismo');
        $measurement->aislamiento = $request->input('aislamiento');

        $measurement->indice_viento_wci = $eval['wci'];
        $measurement->sensacion_termica_c = $eval['sensacion'];
        $measurement->nivel_riesgo = $eval['riesgo'];
        $measurement->tiempo_limite_exposicion = $eval['tle'];
        $measurement->is_compliant = $eval['compliant'];

        $measurement->latitude = $request->filled('latitude') ? (float) $request->input('latitude') : null;
        $measurement->longitude = $request->filled('longitude') ? (float) $request->input('longitude') : null;
        $measurement->utm_zone = $request->input('utm_zone', '19K');
        $measurement->utm_easting = $request->filled('utm_easting') ? (float) $request->input('utm_easting') : null;
        $measurement->utm_northing = $request->filled('utm_northing') ? (float) $request->input('utm_northing') : null;
        $measurement->location_description = $request->input('location_description');
        $measurement->observations = $request->input('observations');
        $measurement->photo_paths = $photoPaths;

        $measurement->save();

        $module->points_completed = $module->coldStressMeasurements()->count();
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punto de medición de estrés por frío registrado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.cold_stress', $module->id)
            ->with('success', '¡Punto de medición de estrés por frío registrado correctamente!');
    }

    /**
     * Update an existing Cold Stress measurement.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = ColdStressMeasurement::where('module_id', $module->id)->findOrFail($measurementId);

        $request->validate([
            'puesto_trabajo' => 'required|string|max:255',
            'temp_c' => 'required|numeric',
        ]);

        $tempC = (float) $request->input('temp_c');
        $velViento = $request->filled('vel_viento_ms') ? (float) $request->input('vel_viento_ms') : null;
        $eval = self::calculateColdStress($tempC, $velViento);

        $photoPaths = $measurement->photo_paths ?: [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/cold_stress');
            if (!File::isDirectory($destPath)) {
                File::makeDirectory($destPath, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fileName = 'cold_' . $module->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $fileName);
                    $photoPaths[] = 'uploads/measurements/cold_stress/' . $fileName;
                }
            }
        }

        if ($request->has('remaining_images') && !empty($request->input('remaining_images'))) {
            $remaining = json_decode($request->input('remaining_images'), true);
            if (is_array($remaining)) {
                $cleanRemaining = [];
                foreach ($remaining as $r) {
                    $parsed = parse_url($r, PHP_URL_PATH);
                    $rel = ltrim($parsed, '/');
                    if (str_contains($rel, 'uploads/measurements/cold_stress/')) {
                        $sub = substr($rel, strpos($rel, 'uploads/measurements/cold_stress/'));
                        $cleanRemaining[] = $sub;
                    } elseif (!empty($r) && !str_starts_with($r, 'data:')) {
                        $cleanRemaining[] = $r;
                    }
                }
                $photoPaths = array_values(array_unique(array_merge($cleanRemaining, $photoPaths)));
            }
        }

        $measurement->staff_id = $request->input('staff_id') ?: $measurement->staff_id;
        if ($request->filled('measurement_date')) {
            $measurement->measurement_date = $request->input('measurement_date');
        }
        if ($request->filled('measurement_time')) {
            $measurement->measurement_time = $request->input('measurement_time');
        }

        if ($request->filled('point_number')) {
            $cleanReqNum = intval(preg_replace('/[^0-9]/', '', (string)$request->input('point_number')));
            if ($cleanReqNum > 0) {
                $measurement->point_number = (string)$cleanReqNum;
            }
        }

        $measurement->area = $request->input('area');
        $measurement->puesto_trabajo = $request->input('puesto_trabajo');
        $measurement->desc_actividades = $request->input('desc_actividades');

        $measurement->temp_c = $tempC;
        $measurement->hr_percent = $request->filled('hr_percent') ? (float) $request->input('hr_percent') : $measurement->hr_percent;
        $measurement->vel_viento_ms = $velViento;
        $measurement->presion_mmhg = $request->filled('presion_mmhg') ? (float) $request->input('presion_mmhg') : $measurement->presion_mmhg;

        $measurement->metabolismo = $request->input('metabolismo');
        $measurement->aislamiento = $request->input('aislamiento');

        $measurement->indice_viento_wci = $eval['wci'];
        $measurement->sensacion_termica_c = $eval['sensacion'];
        $measurement->nivel_riesgo = $eval['riesgo'];
        $measurement->tiempo_limite_exposicion = $eval['tle'];
        $measurement->is_compliant = $eval['compliant'];

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
                'message' => 'Punto de medición de estrés por frío actualizado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.cold_stress', $module->id)
            ->with('success', '¡Punto de medición de estrés por frío actualizado correctamente!');
    }

    /**
     * Delete a Cold Stress measurement.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = ColdStressMeasurement::where('module_id', $module->id)->findOrFail($measurementId);

        $measurement->delete();

        $module->points_completed = $module->coldStressMeasurements()->count();
        $module->save();

        return redirect()->route('modules.cold_stress', $module->id)
            ->with('success', 'Punto de medición de estrés por frío eliminado.');
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
            'message' => 'Encabezado técnico de estrés por frío guardado correctamente.',
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
     * Convert UTM coordinates to Lat/Lng.
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
