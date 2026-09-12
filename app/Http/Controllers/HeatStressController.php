<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\HeatStressMeasurement;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class HeatStressController extends Controller
{
    /**
     * Mapeo de CAV (Clothing Adjustment Value) en °C según ACGIH / ISO 7243
     */
    public static function getCavByClothing(?string $clothing): float
    {
        if (!$clothing) return 0.0;
        $c = strtolower(trim($clothing));
        if (str_contains($c, 'capucha') && str_contains($c, 'vapor')) return 11.0;
        if (str_contains($c, 'barrera de vapor') || str_contains($c, 'impermeable')) return 10.0;
        if (str_contains($c, 'doble capa')) return 3.0;
        if (str_contains($c, 'delantal')) return 1.5;
        if (str_contains($c, 'poliolefina') || str_contains($c, 'tyvek')) return 1.0;
        return 0.0;
    }

    /**
     * Límites Permisibles de WBGT (°C) según Tasa Metabólica y Aclimatación (ACGIH TLVs)
     */
    public static function getWbgtLimit(?string $metabolismo, bool $aclimatado): float
    {
        $m = strtolower(trim($metabolismo ?? ''));
        if (str_contains($m, 'clase 0') || str_contains($m, 'reposo')) {
            return $aclimatado ? 33.0 : 32.0;
        }
        if (str_contains($m, 'clase 1') || str_contains($m, 'bajo')) {
            return $aclimatado ? 31.0 : 29.0;
        }
        if (str_contains($m, 'clase 3') || str_contains($m, 'alto')) {
            return $aclimatado ? 26.0 : 23.0;
        }
        if (str_contains($m, 'clase 4') || str_contains($m, 'muy alto')) {
            return $aclimatado ? 25.0 : 20.0;
        }
        // Por defecto: Clase 2 - Índice metabólico medio
        return $aclimatado ? 28.0 : 26.0;
    }

    /**
     * Calcula los índices de calor, WBGT efectivo, LMP y régimen de descanso.
     */
    public static function evaluateHeatStress(
        ?float $wbgtRaw,
        ?float $wbC,
        ?float $gtC,
        ?float $tempC,
        string $interiorExterior,
        string $aclimatadoStr,
        ?string $tipoRopa,
        ?string $tasaMetabolica
    ): array {
        // Cálculo de WBGT si no viene directo
        if ($wbgtRaw !== null && $wbgtRaw > 0) {
            $wbgt = $wbgtRaw;
        } elseif ($wbC !== null && $gtC !== null) {
            if (strtolower($interiorExterior) === 'exterior') {
                $ta = $tempC ?: $gtC;
                $wbgt = (0.7 * $wbC) + (0.2 * $gtC) + (0.1 * $ta);
            } else {
                $wbgt = (0.7 * $wbC) + (0.3 * $gtC);
            }
        } else {
            $wbgt = $tempC ?: 25.0;
        }
        $wbgt = round($wbgt, 1);

        $cav = self::getCavByClothing($tipoRopa);
        $wbgtEfectivo = round($wbgt + $cav, 1);

        $isAclimatado = (strtolower(trim($aclimatadoStr)) === 'sí' || strtolower(trim($aclimatadoStr)) === 'si' || $aclimatadoStr === '1');
        $lmp = self::getWbgtLimit($tasaMetabolica, $isAclimatado);

        $isCompliant = ($wbgtEfectivo <= $lmp);

        $diff = $wbgtEfectivo - $lmp;
        if ($diff <= 0) {
            $regimen = '100% Trabajo / Continuo';
        } elseif ($diff <= 1.0) {
            $regimen = '75% Trabajo / 25% Descanso por hora';
        } elseif ($diff <= 2.0) {
            $regimen = '50% Trabajo / 50% Descanso por hora';
        } elseif ($diff <= 3.0) {
            $regimen = '25% Trabajo / 75% Descanso por hora';
        } else {
            $regimen = 'Interrupción de labores / Controles de ingeniería';
        }

        return [
            'wbgt' => $wbgt,
            'cav' => $cav,
            'wbgt_efectivo' => $wbgtEfectivo,
            'lmp' => $lmp,
            'regimen' => $regimen,
            'compliant' => $isCompliant,
        ];
    }

    /**
     * Display the Heat Stress monitoring page for a module.
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

        $monitoringType = $module->monitoring_type ?: 'Evaluación de Sobrecarga Térmica (Estrés por Calor WBGT)';

        // Equipo asignado (Monitor WBGT / Medidor de Estrés Térmico)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Monitor de Estrés Térmico WBGT');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'QUEST / 3M') : 'QUEST / 3M';
        $equipmentModel = $equipment ? ($equipment->model ?: 'QUESTemp° 36') : 'QUESTemp° 36';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'QT-71829') : 'QT-71829';
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
        $measurements = $module->heatStressMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
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
                'interior_exterior' => $item->interior_exterior ?: 'Interior',
                'aclimatado' => $item->aclimatado ?: 'Sí',
                'tipo_ropa_cav' => $item->tipo_ropa_cav ?: 'Ropa de Trabajo',
                'cav_ajuste_db' => $item->cav_ajuste_db !== null ? (float) $item->cav_ajuste_db : 0.0,
                'capucha' => $item->capucha ?: 'No',
                'tasa_metabolica' => $item->tasa_metabolica ?: 'Clase 2 - Índice metabólico medio',
                'temp_c' => $item->temp_c !== null ? (float) $item->temp_c : null,
                'hr_percent' => $item->hr_percent !== null ? (float) $item->hr_percent : null,
                'vel_viento_ms' => $item->vel_viento_ms !== null ? (float) $item->vel_viento_ms : null,
                'presion_mmhg' => $item->presion_mmhg !== null ? (float) $item->presion_mmhg : null,
                'wb_c' => $item->wb_c !== null ? (float) $item->wb_c : null,
                'gt_c' => $item->gt_c !== null ? (float) $item->gt_c : null,
                'wbgt_c' => $item->wbgt_c !== null ? (float) $item->wbgt_c : null,
                'wbgt_efectivo_c' => $item->wbgt_efectivo_c !== null ? (float) $item->wbgt_efectivo_c : null,
                'limite_wbgt_lmp' => $item->limite_wbgt_lmp !== null ? (float) $item->limite_wbgt_lmp : 28.0,
                'regimen_trabajo_descanso' => $item->regimen_trabajo_descanso ?: 'Continuo',
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

        return view('measurements.estres_calores.index', compact(
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
     * Store a new Heat Stress measurement.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $request->validate([
            'puesto_trabajo' => 'required|string|max:255',
            'temp_c' => 'nullable|numeric',
            'wbgt_c' => 'nullable|numeric',
        ]);

        $wbgtRaw = $request->filled('wbgt_c') ? (float) $request->input('wbgt_c') : null;
        $wbC = $request->filled('wb_c') ? (float) $request->input('wb_c') : null;
        $gtC = $request->filled('gt_c') ? (float) $request->input('gt_c') : null;
        $tempC = $request->filled('temp_c') ? (float) $request->input('temp_c') : null;

        $interiorExterior = $request->input('interior_exterior', 'Interior');
        $aclimatadoStr = $request->input('aclimatado', 'Sí');
        $tipoRopa = $request->input('tipo_ropa_cav', 'Ropa de Trabajo');
        $tasaMetabolica = $request->input('tasa_metabolica', 'Clase 2 - Índice metabólico medio');

        $eval = self::evaluateHeatStress($wbgtRaw, $wbC, $gtC, $tempC, $interiorExterior, $aclimatadoStr, $tipoRopa, $tasaMetabolica);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/heat_stress');
            if (!File::isDirectory($destPath)) {
                File::makeDirectory($destPath, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fileName = 'heat_' . $module->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $fileName);
                    $photoPaths[] = 'uploads/measurements/heat_stress/' . $fileName;
                }
            }
        }

        $nextIndex = $module->heatStressMeasurements()->count() + 1;
        $cleanReqNum = intval(preg_replace('/[^0-9]/', '', (string)$request->input('point_number', '')));
        $pointNum = (string) ($cleanReqNum ?: $nextIndex);

        $measurement = new HeatStressMeasurement();
        $measurement->module_id = $module->id;
        $measurement->project_id = $module->project_id;
        $measurement->staff_id = $request->input('staff_id') ?: ($module->field_staff_id ?: (Auth::id() ?? null));
        $measurement->point_number = $pointNum;
        $measurement->measurement_date = $request->input('measurement_date') ?: Carbon::today();
        $measurement->measurement_time = $request->input('measurement_time') ?: Carbon::now()->format('H:i');

        $measurement->area = $request->input('area');
        $measurement->puesto_trabajo = $request->input('puesto_trabajo');
        $measurement->desc_actividades = $request->input('desc_actividades');

        $measurement->interior_exterior = $interiorExterior;
        $measurement->aclimatado = $aclimatadoStr;
        $measurement->tipo_ropa_cav = $tipoRopa;
        $measurement->cav_ajuste_db = $eval['cav'];
        $measurement->capucha = $request->input('capucha', 'No');
        $measurement->tasa_metabolica = $tasaMetabolica;

        $measurement->temp_c = $tempC;
        $measurement->hr_percent = $request->filled('hr_percent') ? (float) $request->input('hr_percent') : null;
        $measurement->vel_viento_ms = $request->filled('vel_viento_ms') ? (float) $request->input('vel_viento_ms') : null;
        $measurement->presion_mmhg = $request->filled('presion_mmhg') ? (float) $request->input('presion_mmhg') : null;

        $measurement->wb_c = $wbC;
        $measurement->gt_c = $gtC;
        $measurement->wbgt_c = $eval['wbgt'];
        $measurement->wbgt_efectivo_c = $eval['wbgt_efectivo'];
        $measurement->limite_wbgt_lmp = $eval['lmp'];
        $measurement->regimen_trabajo_descanso = $eval['regimen'];
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

        $module->points_completed = $module->heatStressMeasurements()->count();
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punto de medición de estrés por calor registrado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.heat_stress', $module->id)
            ->with('success', '¡Punto de medición de estrés por calor registrado correctamente!');
    }

    /**
     * Update an existing Heat Stress measurement.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = HeatStressMeasurement::where('module_id', $module->id)->findOrFail($measurementId);

        $request->validate([
            'puesto_trabajo' => 'required|string|max:255',
            'temp_c' => 'nullable|numeric',
            'wbgt_c' => 'nullable|numeric',
        ]);

        $wbgtRaw = $request->filled('wbgt_c') ? (float) $request->input('wbgt_c') : null;
        $wbC = $request->filled('wb_c') ? (float) $request->input('wb_c') : null;
        $gtC = $request->filled('gt_c') ? (float) $request->input('gt_c') : null;
        $tempC = $request->filled('temp_c') ? (float) $request->input('temp_c') : $measurement->temp_c;

        $interiorExterior = $request->input('interior_exterior', $measurement->interior_exterior ?: 'Interior');
        $aclimatadoStr = $request->input('aclimatado', $measurement->aclimatado ?: 'Sí');
        $tipoRopa = $request->input('tipo_ropa_cav', $measurement->tipo_ropa_cav ?: 'Ropa de Trabajo');
        $tasaMetabolica = $request->input('tasa_metabolica', $measurement->tasa_metabolica ?: 'Clase 2 - Índice metabólico medio');

        $eval = self::evaluateHeatStress($wbgtRaw, $wbC, $gtC, $tempC, $interiorExterior, $aclimatadoStr, $tipoRopa, $tasaMetabolica);

        $photoPaths = $measurement->photo_paths ?: [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/heat_stress');
            if (!File::isDirectory($destPath)) {
                File::makeDirectory($destPath, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                if ($file->isValid()) {
                    $fileName = 'heat_' . $module->id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $fileName);
                    $photoPaths[] = 'uploads/measurements/heat_stress/' . $fileName;
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

        $measurement->area = $request->input('area');
        $measurement->puesto_trabajo = $request->input('puesto_trabajo');
        $measurement->desc_actividades = $request->input('desc_actividades');

        $measurement->interior_exterior = $interiorExterior;
        $measurement->aclimatado = $aclimatadoStr;
        $measurement->tipo_ropa_cav = $tipoRopa;
        $measurement->cav_ajuste_db = $eval['cav'];
        $measurement->capucha = $request->input('capucha', 'No');
        $measurement->tasa_metabolica = $tasaMetabolica;

        $measurement->temp_c = $tempC;
        $measurement->hr_percent = $request->filled('hr_percent') ? (float) $request->input('hr_percent') : $measurement->hr_percent;
        $measurement->vel_viento_ms = $request->filled('vel_viento_ms') ? (float) $request->input('vel_viento_ms') : $measurement->vel_viento_ms;
        $measurement->presion_mmhg = $request->filled('presion_mmhg') ? (float) $request->input('presion_mmhg') : $measurement->presion_mmhg;

        $measurement->wb_c = $wbC;
        $measurement->gt_c = $gtC;
        $measurement->wbgt_c = $eval['wbgt'];
        $measurement->wbgt_efectivo_c = $eval['wbgt_efectivo'];
        $measurement->limite_wbgt_lmp = $eval['lmp'];
        $measurement->regimen_trabajo_descanso = $eval['regimen'];
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
                'message' => 'Punto de medición de estrés por calor actualizado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.heat_stress', $module->id)
            ->with('success', '¡Punto de medición de estrés por calor actualizado correctamente!');
    }

    /**
     * Delete a Heat Stress measurement.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = HeatStressMeasurement::where('module_id', $module->id)->findOrFail($measurementId);

        $measurement->delete();

        $module->points_completed = $module->heatStressMeasurements()->count();
        $module->save();

        return redirect()->route('modules.heat_stress', $module->id)
            ->with('success', 'Punto de medición de estrés por calor eliminado.');
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
            'message' => 'Encabezado técnico de estrés por calor guardado correctamente.',
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
