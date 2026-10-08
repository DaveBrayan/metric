<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\Staff;
use App\Models\ContaminantesQuimicosMeasurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class ContaminantesQuimicosController extends Controller
{
    /**
     * Muestra la página principal del módulo de Contaminantes Químicos.
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
            $installationName = $razonSocial ?: ($module->installation_name ?: 'PLANTA INDUSTRIAL — ÁREA DE PROCESOS QUÍMICOS');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        // Fechas del módulo
        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Contaminantes Químicos (Gases, Vapores y Polvos)';

        // Equipo asignado para muestreo de contaminantes químicos
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Bomba de Muestreo Personal con Tren de Muestreo');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'SKC / Gilian') : 'SKC';
        $equipmentModel = $equipment ? ($equipment->model ?: 'AirChek XR5000') : 'AirChek XR5000';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'CQ-2024-01') : 'CQ-2024-01';
        $equipmentImage = ($equipment && $equipment->image) ? asset($equipment->image) : null;

        // Personal asignado para selector y encabezado técnico
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Higiene Ocupacional';
            $staffList = Staff::orderBy('name')->get();
        }

        // Obtener mediciones registradas en base de datos
        $dbMeasurements = $module->contaminantesQuimicosMeasurements()->with('staff')->get();

        $measurements = $dbMeasurements->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->measurement_date ? Carbon::parse($item->measurement_date)->format('d/m/Y') : '—';
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
                            $conv = $this->utmToLatLng($d['easting'], $d['northing'], $d['utm_zone'] ?? $d['zone'] ?? '19K');
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

            $pointNum = (!empty($item->codigo) && preg_match('/^CQ-\d+/i', $item->codigo))
                ? strtoupper($item->codigo)
                : ((!empty($item->point_number) && preg_match('/^CQ-\d+/i', $item->point_number))
                    ? strtoupper($item->point_number)
                    : ('CQ-' . ($index + 1)));

            $masaInicial = (float)($item->masa_inicial_filtro_mg ?? 0);
            $masaFinal = (float)($item->masa_final_filtro_mg ?? 0);
            $masaNeta = ($item->masa_final_filtro_mg !== null && $item->masa_inicial_filtro_mg !== null)
                ? round($masaFinal - $masaInicial, 4)
                : 0.0;

            $qInicial = (float)($item->q_inicial_lmin ?? 0);
            $qFinal = (float)($item->q_final_lmin ?? 0);
            $qProm = ($qInicial > 0 || $qFinal > 0)
                ? round(($qInicial + $qFinal) / ($qInicial > 0 && $qFinal > 0 ? 2 : 1), 3)
                : 0.0;

            $tInicial = (float)($item->t_inicial_c ?? 0);
            $tFinal = (float)($item->t_final_c ?? 0);
            $tProm = ($tInicial > 0 || $tFinal > 0)
                ? round(($tInicial + $tFinal) / ($tInicial > 0 && $tFinal > 0 ? 2 : 1), 2)
                : 0.0;

            // Calcular tiempo de muestreo en minutos si existen hora inicio y fin
            $tiempoMuestreoMin = 0;
            if (!empty($item->hora_inicio) && !empty($item->hora_final)) {
                try {
                    $t1 = Carbon::createFromFormat('H:i', trim($item->hora_inicio));
                    $t2 = Carbon::createFromFormat('H:i', trim($item->hora_final));
                    if ($t2->lessThan($t1)) {
                        $t2->addDay();
                    }
                    $tiempoMuestreoMin = $t1->diffInMinutes($t2);
                } catch (\Exception $e) {
                    $tiempoMuestreoMin = 0;
                }
            }

            // Volumen muestreado en m3: (Q_prom L/min * min) / 1000
            $volumenMuestreadoM3 = ($qProm > 0 && $tiempoMuestreoMin > 0)
                ? round(($qProm * $tiempoMuestreoMin) / 1000, 4)
                : 0.0;

            // Concentración en mg/m3: masa neta (mg) / volumen (m3)
            $concentracionMgM3 = ($volumenMuestreadoM3 > 0 && $masaNeta > 0)
                ? round($masaNeta / $volumenMuestreadoM3, 4)
                : 0.0;

            return [
                'id' => $item->id,
                'num' => $pointNum,
                'point_number' => $pointNum,
                'codigo' => $pointNum,
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? Carbon::parse($item->measurement_date)->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '08:00',
                'area' => $item->area ?: 'General',
                'punto_medicion' => $item->punto_medicion ?: $pointNum,
                'workstation' => $item->punto_medicion ?: 'Punto de Muestreo',
                'trabajador_nombre' => $item->trabajador_nombre ?: '—',
                'masa_inicial_filtro_mg' => $masaInicial,
                'masa_final_filtro_mg' => $masaFinal,
                'masa_neta_mg' => $masaNeta,
                'hora_inicio' => $item->hora_inicio ?: '08:00',
                'hora_final' => $item->hora_final ?: '12:00',
                'tiempo_muestreo_min' => $tiempoMuestreoMin,
                't_inicial_c' => $tInicial,
                't_final_c' => $tFinal,
                't_prom_c' => $tProm,
                'presion_hpa' => (float)($item->presion_hpa ?? 1013.25),
                'q_inicial_lmin' => $qInicial,
                'q_final_lmin' => $qFinal,
                'q_prom_lmin' => $qProm,
                'volumen_m3' => $volumenMuestreadoM3,
                'concentracion_mg_m3' => $concentracionMgM3,
                'image_path' => $imagesUrls[0] ?? null,
                'raw_image_path' => $rawImages[0] ?? null,
                'images' => $imagesUrls,
                'raw_images' => $rawImages,
                'images_count' => count($imagesUrls),
                'location' => $item->location ?: ($lat && $lng ? "{$lat}, {$lng}" : '—'),
                'latitude' => $lat ?? -16.5034,
                'longitude' => $lng ?? -68.1324,
                'utm_zone' => $item->utm_zone ?: '19K',
                'utm_easting' => $item->utm_easting !== null ? (float) $item->utm_easting : 592450.0,
                'utm_northing' => $item->utm_northing !== null ? (float) $item->utm_northing : 8175320.0,
                'observations' => $item->observations ?: 'Sin observaciones registradas.',
                'registered_by' => $item->staff ? $item->staff->name : ($item->registered_by ?: ($currentUser ? $currentUser->name : 'Técnico')),
                'staff_id' => $item->staff_id,
            ];
        });

        // Conteo y KPIs
        $totalMeasurements = $measurements->count();
        $totalVolumen = round($measurements->sum('volumen_m3'), 3);
        $avgConcentracion = $totalMeasurements > 0 ? round($measurements->avg('concentracion_mg_m3'), 4) : 0.0;
        $totalMasaRecolectada = round($measurements->sum('masa_neta_mg'), 4);

        $photoReportSettings = $module->photo_report_settings ?? [];

        return view('measurements.contaminantes_quimicos.index', compact(
            'module',
            'project',
            'company',
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
            'totalVolumen',
            'avgConcentracion',
            'totalMasaRecolectada',
            'photoReportSettings',
            'userName',
            'userRole'
        ));
    }

    /**
     * Guarda un nuevo punto de medición de contaminantes químicos.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'measurement_date' => 'nullable|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'punto_medicion' => 'nullable|string|max:255',
            'trabajador_nombre' => 'nullable|string|max:255',
            'masa_inicial_filtro_mg' => 'nullable|numeric',
            'masa_final_filtro_mg' => 'nullable|numeric',
            'hora_inicio' => 'nullable|string|max:20',
            'hora_final' => 'nullable|string|max:20',
            't_inicial_c' => 'nullable|numeric',
            't_final_c' => 'nullable|numeric',
            'presion_hpa' => 'nullable|numeric',
            'q_inicial_lmin' => 'nullable|numeric',
            'q_final_lmin' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'images.*' => 'nullable|image|max:15360',
        ]);

        $count = $module->contaminantesQuimicosMeasurements()->count();
        $pointCode = 'CQ-' . ($count + 1);

        // Manejo de Fotos
        $uploadedImages = [];
        if ($request->hasFile('images')) {
            $destPath = public_path('uploads/contaminantes_quimicos');
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0755, true);
            }
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'cq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $uploadedImages[] = 'uploads/contaminantes_quimicos/' . $filename;
                }
            }
        }

        $data = [
            'module_id' => $module->id,
            'point_number' => $pointCode,
            'codigo' => $pointCode,
            'measurement_date' => $validated['measurement_date'] ?? now()->toDateString(),
            'measurement_time' => $validated['measurement_time'] ?? now()->format('H:i'),
            'area' => $validated['area'] ?? 'General',
            'punto_medicion' => $validated['punto_medicion'] ?? $pointCode,
            'trabajador_nombre' => $validated['trabajador_nombre'] ?? null,
            'masa_inicial_filtro_mg' => $validated['masa_inicial_filtro_mg'] ?? null,
            'masa_final_filtro_mg' => $validated['masa_final_filtro_mg'] ?? null,
            'hora_inicio' => $validated['hora_inicio'] ?? '08:00',
            'hora_final' => $validated['hora_final'] ?? '12:00',
            't_inicial_c' => $validated['t_inicial_c'] ?? null,
            't_final_c' => $validated['t_final_c'] ?? null,
            'presion_hpa' => $validated['presion_hpa'] ?? null,
            'q_inicial_lmin' => $validated['q_inicial_lmin'] ?? null,
            'q_final_lmin' => $validated['q_final_lmin'] ?? null,
            'utm_zone' => $validated['utm_zone'] ?? '19K',
            'utm_easting' => $validated['utm_easting'] ?? null,
            'utm_northing' => $validated['utm_northing'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'location' => ($validated['latitude'] && $validated['longitude']) ? "{$validated['latitude']}, {$validated['longitude']}" : null,
            'image_path' => $uploadedImages[0] ?? null,
            'images' => $uploadedImages,
            'image_urls' => $uploadedImages,
            'observations' => $validated['observations'] ?? null,
            'staff_id' => $validated['staff_id'] ?? null,
            'registered_by' => Auth::user() ? Auth::user()->name : 'Técnico',
            'created_by' => Auth::user() ? Auth::user()->name : 'Técnico',
        ];

        ContaminantesQuimicosMeasurement::create($data);

        // Actualizar contador del módulo
        $module->points_completed = $module->contaminantesQuimicosMeasurements()->count();
        $module->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Punto {$pointCode} guardado exitosamente."]);
        }

        return redirect()->route('modules.contaminantes_quimicos', $module->id)->with('success', "Punto {$pointCode} registrado con éxito.");
    }

    /**
     * Actualiza un punto de medición de contaminantes químicos.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->contaminantesQuimicosMeasurements()->findOrFail($measurementId);

        $validated = $request->validate([
            'measurement_date' => 'nullable|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'punto_medicion' => 'nullable|string|max:255',
            'trabajador_nombre' => 'nullable|string|max:255',
            'masa_inicial_filtro_mg' => 'nullable|numeric',
            'masa_final_filtro_mg' => 'nullable|numeric',
            'hora_inicio' => 'nullable|string|max:20',
            'hora_final' => 'nullable|string|max:20',
            't_inicial_c' => 'nullable|numeric',
            't_final_c' => 'nullable|numeric',
            'presion_hpa' => 'nullable|numeric',
            'q_inicial_lmin' => 'nullable|numeric',
            'q_final_lmin' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'images.*' => 'nullable|image|max:15360',
            'keep_images' => 'nullable|string',
        ]);

        $currentImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        if (empty($currentImages) && !empty($measurement->image_urls)) {
            $currentImages = is_array($measurement->image_urls) ? $measurement->image_urls : (json_decode($measurement->image_urls, true) ?: []);
        }

        // Filtrar según keep_images si fue enviado
        if ($request->has('keep_images')) {
            $keepList = json_decode($request->input('keep_images'), true) ?: [];
            $currentImages = array_values(array_intersect($currentImages, $keepList));
        }

        // Nuevas fotos
        if ($request->hasFile('images')) {
            $destPath = public_path('uploads/contaminantes_quimicos');
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0755, true);
            }
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'cq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $currentImages[] = 'uploads/contaminantes_quimicos/' . $filename;
                }
            }
        }

        $measurement->update([
            'measurement_date' => $validated['measurement_date'] ?? $measurement->measurement_date,
            'measurement_time' => $validated['measurement_time'] ?? $measurement->measurement_time,
            'area' => $validated['area'] ?? $measurement->area,
            'punto_medicion' => $validated['punto_medicion'] ?? $measurement->punto_medicion,
            'trabajador_nombre' => $validated['trabajador_nombre'] ?? $measurement->trabajador_nombre,
            'masa_inicial_filtro_mg' => $validated['masa_inicial_filtro_mg'] ?? $measurement->masa_inicial_filtro_mg,
            'masa_final_filtro_mg' => $validated['masa_final_filtro_mg'] ?? $measurement->masa_final_filtro_mg,
            'hora_inicio' => $validated['hora_inicio'] ?? $measurement->hora_inicio,
            'hora_final' => $validated['hora_final'] ?? $measurement->hora_final,
            't_inicial_c' => $validated['t_inicial_c'] ?? $measurement->t_inicial_c,
            't_final_c' => $validated['t_final_c'] ?? $measurement->t_final_c,
            'presion_hpa' => $validated['presion_hpa'] ?? $measurement->presion_hpa,
            'q_inicial_lmin' => $validated['q_inicial_lmin'] ?? $measurement->q_inicial_lmin,
            'q_final_lmin' => $validated['q_final_lmin'] ?? $measurement->q_final_lmin,
            'utm_zone' => $validated['utm_zone'] ?? $measurement->utm_zone,
            'utm_easting' => $validated['utm_easting'] ?? $measurement->utm_easting,
            'utm_northing' => $validated['utm_northing'] ?? $measurement->utm_northing,
            'latitude' => $validated['latitude'] ?? $measurement->latitude,
            'longitude' => $validated['longitude'] ?? $measurement->longitude,
            'location' => ($validated['latitude'] ?? $measurement->latitude) && ($validated['longitude'] ?? $measurement->longitude)
                ? ($validated['latitude'] ?? $measurement->latitude) . ', ' . ($validated['longitude'] ?? $measurement->longitude)
                : $measurement->location,
            'image_path' => $currentImages[0] ?? null,
            'images' => $currentImages,
            'image_urls' => $currentImages,
            'observations' => $validated['observations'] ?? $measurement->observations,
            'staff_id' => $validated['staff_id'] ?? $measurement->staff_id,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Punto {$measurement->point_number} actualizado con éxito."]);
        }

        return redirect()->route('modules.contaminantes_quimicos', $module->id)->with('success', "Punto {$measurement->point_number} actualizado.");
    }

    /**
     * Elimina un punto de medición de contaminantes químicos.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->contaminantesQuimicosMeasurements()->findOrFail($measurementId);
        $pointCode = $measurement->point_number;
        $measurement->delete();

        $module->points_completed = $module->contaminantesQuimicosMeasurements()->count();
        $module->save();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Punto {$pointCode} eliminado correctamente."]);
        }

        return redirect()->route('modules.contaminantes_quimicos', $module->id)->with('success', "Punto {$pointCode} eliminado con éxito.");
    }

    /**
     * Actualiza el encabezado técnico del módulo (Inline auto-save).
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $module->installation_name = $request->input('installation_name', $module->installation_name);
        $module->start_date = $request->input('start_date', $module->start_date);
        $module->end_date = $request->input('end_date', $module->end_date);
        $module->monitoring_type = $request->input('monitoring_type', $module->monitoring_type);
        $module->save();

        return response()->json(['success' => true, 'message' => 'Encabezado técnico actualizado correctamente.']);
    }

    /**
     * Guarda la configuración del reporte fotográfico.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $module->photo_report_settings = $request->all();
        $module->save();

        return response()->json(['success' => true, 'message' => 'Configuración de reporte fotográfico guardada.']);
    }

    /**
     * Convierte coordenadas UTM a Latitud/Longitud (WGS84 aproximado).
     */
    private function utmToLatLng($easting, $northing, $zoneStr = '19K')
    {
        $zone = (int) preg_replace('/[^0-9]/', '', $zoneStr) ?: 19;
        $isSouth = true;

        $x = (float) $easting - 500000.0;
        $y = (float) $northing;
        if ($isSouth) {
            $y -= 10000000.0;
        }

        $k0 = 0.9996;
        $a = 6378137.0;
        $eccSquared = 0.00669438;
        $e1 = (1 - sqrt(1 - $eccSquared)) / (1 + sqrt(1 - $eccSquared));

        $M = $y / $k0;
        $mu = $M / ($a * (1 - $eccSquared / 4 - 3 * $eccSquared * $eccSquared / 64 - 5 * $eccSquared * $eccSquared * $eccSquared / 256));

        $phi1Rad = $mu + (3 * $e1 / 2 - 27 * pow($e1, 3) / 32) * sin(2 * $mu)
            + (21 * pow($e1, 2) / 16 - 55 * pow($e1, 4) / 32) * sin(4 * $mu)
            + (151 * pow($e1, 3) / 96) * sin(6 * $mu);

        $N1 = $a / sqrt(1 - $eccSquared * sin($phi1Rad) * sin($phi1Rad));
        $T1 = tan($phi1Rad) * tan($phi1Rad);
        $C1 = $eccSquared / (1 - $eccSquared) * cos($phi1Rad) * cos($phi1Rad);
        $R1 = $a * (1 - $eccSquared) / pow(1 - $eccSquared * sin($phi1Rad) * sin($phi1Rad), 1.5);
        $D = $x / ($N1 * $k0);

        $lat = $phi1Rad - ($N1 * tan($phi1Rad) / $R1) * ($D * $D / 2 - (5 + 3 * $T1 + 10 * $C1 - 4 * $C1 * $C1 - 9 * ($eccSquared / (1 - $eccSquared))) * pow($D, 4) / 24);
        $lat = rad2deg($lat);

        $lng = ($D - (1 + 2 * $T1 + $C1) * pow($D, 3) / 6 + (5 - 2 * $C1 + 28 * $T1 - 3 * $C1 * $C1 + 8 * ($eccSquared / (1 - $eccSquared)) + 24 * $T1 * $T1) * pow($D, 5) / 120) / cos($phi1Rad);
        $longOrigin = ($zone - 1) * 6 - 180 + 3;
        $lng = $longOrigin + rad2deg($lng);

        return ['lat' => round($lat, 7), 'lng' => round($lng, 7)];
    }
}
