<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\DosimetryMeasurement;
use App\Models\MeasurementModule;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class DosimetryController extends Controller
{
    /**
     * Calcula el Límite Máximo Permisible (LMP en dBA) según el tiempo de exposición (TPE en horas).
     * Norma estándar: 85 dBA para 8 horas. Con tasa de intercambio de 3 dB.
     */
    public static function getLmpForTpe($tpeHoras)
    {
        $tpe = (float) $tpeHoras;
        if ($tpe <= 0) $tpe = 8.0;
        
        // LMP = 85 - 10 * log10(TPE / 8) / log10(2)
        $lmp = 85.0 - (10.0 * log10($tpe / 8.0) / log10(2.0));
        return round($lmp, 1);
    }

    /**
     * Display the dosimetry monitoring page for a module.
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
        $projectName = $project ? $project->name : 'Proyecto General';
        $companyName = $company ? $company->name : 'Empresa';
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

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));

        $monitoringType = $module->monitoring_type ?: 'Seguimiento';

        // Equipo asignado (Dosímetro de Ruido)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Dosímetro de Ruido Ocupacional');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'SVANTEK') : 'SVANTEK';
        $equipmentModel = $equipment ? ($equipment->model ?: 'SV 104A') : 'SV 104A';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '814920') : '814920';
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
        $measurements = $module->dosimetryMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';
            
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

            // Extraer y convertir UTM si falta lat/lng
            if (($lat === null || $lng === null) && !empty($item->location)) {
                $loc = trim($item->location);
                if (str_starts_with($loc, '{') || str_starts_with($loc, '[')) {
                    $d = json_decode($loc, true);
                    if (is_array($d)) {
                        if (!empty($d['latitude']) && !empty($d['longitude'])) {
                            $lat = (float) $d['latitude'];
                            $lng = (float) $d['longitude'];
                        } elseif (!empty($d['lat']) && !empty($d['lng'])) {
                            $lat = (float) $d['lat'];
                            $lng = (float) $d['lng'];
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
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? asset($p) : null;
            }, $rawImages)));

            $tpe = (float) ($item->tiempo_expos_h ?? 8.0);
            $duracionMed = (float) ($item->duracion_medicion_h ?? 0.0);
            $npsMax = $item->nps_max_db !== null ? (float) $item->nps_max_db : null;
            $npsMin = $item->nps_min_db !== null ? (float) $item->nps_min_db : null;
            $leqT = $item->leq_t_db !== null ? (float) $item->leq_t_db : null;

            $lmp = self::getLmpForTpe($tpe);
            
            // Evaluación de cumplimiento normativo
            $isCompliant = ($leqT !== null) ? ($leqT <= $lmp) : true;
            $cumple = ($leqT !== null) ? ($isCompliant ? 'SI' : 'NO') : '—';

            // 1) Nivel de presión sonora diario equivalente Laeq,d (dBA) = Leq,T + 10 * log10(TPE / 8)
            $laeqD = null;
            if ($leqT !== null && $tpe > 0) {
                $laeqD = round($leqT + (10.0 * log10($tpe / 8.0)), 2);
            }

            // 2) Dosis de ruido para estudios a 8 horas = 10^((Laeq,d - 85) / 10)
            $dosisRuido = null;
            if ($laeqD !== null) {
                $dosisRuido = round(pow(10.0, ($laeqD - 85.0) / 10.0), 2);
            }

            // Dosis de ruido porcentual (% Dosis clásica)
            $dosisCalculada = null;
            if ($leqT !== null && $duracionMed > 0 && $tpe > 0) {
                $dosisCalculada = round(100.0 * ($duracionMed / 8.0) * pow(2.0, ($leqT - 85.0) / 3.0), 1);
            }

            return [
                'id' => $item->id,
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '—',
                'area' => $item->area ?: 'Área Principal',
                'punto_medicion' => $item->punto_medicion ?: 'Punto 01',
                'tipo_ruido' => $item->tipo_ruido ?: 'Fluctuante',
                'tiempo_expos_h' => number_format($tpe, 1, ',', '.'),
                'raw_tiempo_expos_h' => $tpe,
                'ponderacion' => $item->ponderacion ?: 'A',
                'respuesta' => $item->respuesta ?: 'Lento',
                'duracion_medicion_h' => $duracionMed > 0 ? number_format($duracionMed, 1, ',', '.') : '—',
                'raw_duracion_medicion_h' => $duracionMed,
                'nps_max_db' => $npsMax !== null ? number_format($npsMax, 1, ',', '.') : '—',
                'raw_nps_max_db' => $npsMax,
                'nps_min_db' => $npsMin !== null ? number_format($npsMin, 1, ',', '.') : '—',
                'raw_nps_min_db' => $npsMin,
                'leq_t_db' => $leqT !== null ? number_format($leqT, 1, ',', '.') : '—',
                'raw_leq_t_db' => $leqT,
                'lmp' => number_format($lmp, 1, ',', '.'),
                'raw_lmp' => $lmp,
                'laeq_d_db' => $laeqD !== null ? number_format($laeqD, 2, ',', '.') : '—',
                'raw_laeq_d_db' => $laeqD,
                'dosis_ruido' => $dosisRuido !== null ? number_format($dosisRuido, 2, ',', '.') : '—',
                'raw_dosis_ruido' => $dosisRuido,
                'dosis_pct' => $dosisCalculada !== null ? number_format($dosisCalculada, 1, ',', '.') : ($item->dosis_pct !== null ? number_format((float)$item->dosis_pct, 1, ',', '.') : '—'),
                'raw_dosis_pct' => $dosisCalculada ?? (float)$item->dosis_pct,
                'cumple' => $cumple,
                'is_compliant' => $isCompliant,
                'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                'raw_image_path' => $item->image_path,
                'images' => $imagesUrls,
                'images_count' => count($imagesUrls),
                'location' => $item->location ?: ($lat && $lng ? "{$lat}, {$lng}" : '—'),
                'latitude' => $lat,
                'longitude' => $lng,
                'utm_zone' => $item->utm_zone ?: '20K',
                'utm_easting' => (float)$item->utm_easting,
                'utm_northing' => (float)$item->utm_northing,
                'observations' => $item->observations ?: 'Sin observaciones',
                'raw_observations' => $item->observations,
                'registered_by' => (function() use ($item, $assignedStaff, $currentUser) {
                    if (!empty($item->registered_by) && !is_numeric($item->registered_by)) {
                        return $item->registered_by;
                    }
                    if ($item->staff) {
                        return $item->staff->name;
                    }
                    if ($assignedStaff && $assignedStaff->isNotEmpty()) {
                        return $assignedStaff->first()->name;
                    }
                    return $currentUser ? $currentUser->name : 'Técnico de Campo';
                })(),
                'staff_id' => $item->staff_id,
            ];
        });

        // KPIs y Métricas Globales
        $totalPoints = $measurements->count();
        $leqValues = $measurements->pluck('raw_leq_t_db')->filter(fn($v) => $v !== null && $v > 0);
        $npsMaxValues = $measurements->pluck('raw_nps_max_db')->filter(fn($v) => $v !== null && $v > 0);
        $npsMinValues = $measurements->pluck('raw_nps_min_db')->filter(fn($v) => $v !== null && $v > 0);

        $promedioLeq = $leqValues->isNotEmpty() ? round($leqValues->average(), 1) : null;
        $maxNps = $npsMaxValues->isNotEmpty() ? $npsMaxValues->max() : ($leqValues->isNotEmpty() ? $leqValues->max() : null);
        $minNps = $npsMinValues->isNotEmpty() ? $npsMinValues->min() : ($leqValues->isNotEmpty() ? $leqValues->min() : null);

        $cumpleCount = $measurements->where('cumple', 'SI')->count();
        $noCumpleCount = $measurements->where('cumple', 'NO')->count();
        $cumplePorcentaje = $totalPoints > 0 ? round(($cumpleCount / $totalPoints) * 100) : 0;

        $stats = [
            'total_points' => $totalPoints,
            'promedio_leq' => $promedioLeq !== null ? number_format($promedioLeq, 1, ',', '.') : '—',
            'raw_promedio_leq' => $promedioLeq,
            'max_nps' => $maxNps !== null ? number_format($maxNps, 1, ',', '.') : '—',
            'raw_max_nps' => $maxNps,
            'min_nps' => $minNps !== null ? number_format($minNps, 1, ',', '.') : '—',
            'raw_min_nps' => $minNps,
            'cumple_count' => $cumpleCount,
            'no_cumple_count' => $noCumpleCount,
            'cumple_porcentaje' => $cumplePorcentaje,
            'is_global_compliant' => $noCumpleCount === 0 && $totalPoints > 0,
        ];

        // Tipos de Ruido disponibles
        $tiposRuido = ['Estable', 'Fluctuante', 'Estable escalonado', 'Impacto'];

        return view('measurements.dosimetrias.index', compact(
            'module',
            'userName',
            'userRole',
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
            'stats',
            'tiposRuido'
        ));
    }

    /**
     * Store a new dosimetry measurement.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'punto_medicion' => 'required|string|max:255',
            'tipo_ruido' => 'required|string|max:100',
            'tiempo_expos_h' => 'nullable|numeric',
            'ponderacion' => 'nullable|string|max:10',
            'respuesta' => 'nullable|string|max:20',
            'duracion_medicion_h' => 'nullable|numeric',
            'nps_max_db' => 'nullable|numeric',
            'nps_min_db' => 'nullable|numeric',
            'leq_t_db' => 'required|numeric',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
            'photos.*' => 'nullable|image|max:15360',
        ]);

        $images = [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/dosimetria/' . $moduleId);
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0777, true, true);
            }
            foreach ($request->file('photos') as $photo) {
                $filename = 'dosi_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                $photo->move($destPath, $filename);
                $images[] = 'uploads/measurements/dosimetria/' . $moduleId . '/' . $filename;
            }
        }

        $nextNum = $module->dosimetryMeasurements()->count() + 1;
        $pointNumber = str_pad($nextNum, 2, '0', STR_PAD_LEFT);

        $tpe = (float) ($validated['tiempo_expos_h'] ?? 8.0);
        $lmp = self::getLmpForTpe($tpe);
        $leqT = (float) $validated['leq_t_db'];
        $cumple = ($leqT <= $lmp) ? 'SI' : 'NO';

        $duracion = (float) ($validated['duracion_medicion_h'] ?? 0.0);
        $dosis = ($duracion > 0 && $tpe > 0) ? round(100.0 * ($duracion / 8.0) * pow(2.0, ($leqT - 85.0) / 3.0), 2) : null;

        $measurement = DosimetryMeasurement::create([
            'module_id' => $module->id,
            'point_number' => $pointNumber,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? Carbon::now()->format('H:i'),
            'area' => $validated['area'],
            'punto_medicion' => $validated['punto_medicion'],
            'tipo_ruido' => $validated['tipo_ruido'],
            'tiempo_expos_h' => $tpe,
            'ponderacion' => $validated['ponderacion'] ?? 'A',
            'respuesta' => $validated['respuesta'] ?? 'Lento',
            'duracion_medicion_h' => $duracion,
            'nps_max_db' => $validated['nps_max_db'] ?? null,
            'nps_min_db' => $validated['nps_min_db'] ?? null,
            'leq_t_db' => $leqT,
            'dosis_pct' => $dosis,
            'cumple' => $cumple,
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'utm_zone' => $validated['utm_zone'] ?? '20K',
            'utm_easting' => $validated['utm_easting'] ?? null,
            'utm_northing' => $validated['utm_northing'] ?? null,
            'image_path' => $images[0] ?? null,
            'images' => $images,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $validated['registered_by'] ?? (Auth::user() ? Auth::user()->name : 'Técnico de Campo'),
            'staff_id' => $validated['staff_id'] ?? null,
        ]);

        // Actualiza contador de puntos completados
        $module->update(['points_completed' => $module->dosimetryMeasurements()->count()]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punto de dosimetría registrado correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.dosimetry', $moduleId)
            ->with('success', 'Medición de dosimetría registrada exitosamente.');
    }

    /**
     * Update an existing dosimetry measurement.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $measurement = DosimetryMeasurement::where('module_id', $moduleId)->findOrFail($measurementId);

        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'punto_medicion' => 'required|string|max:255',
            'tipo_ruido' => 'required|string|max:100',
            'tiempo_expos_h' => 'nullable|numeric',
            'ponderacion' => 'nullable|string|max:10',
            'respuesta' => 'nullable|string|max:20',
            'duracion_medicion_h' => 'nullable|numeric',
            'nps_max_db' => 'nullable|numeric',
            'nps_min_db' => 'nullable|numeric',
            'leq_t_db' => 'required|numeric',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
            'existing_photos' => 'nullable|array',
            'photos.*' => 'nullable|image|max:15360',
        ]);

        $normalizePath = function($url) {
            if (!$url || !is_string($url)) return '';
            $parsed = parse_url($url, PHP_URL_PATH);
            $path = $parsed ?: $url;
            return ltrim($path, '/\\');
        };

        // Extraer imágenes existentes de la BD
        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        if (empty($existingImages) && !empty($measurement->image_path)) {
            $existingImages = [$measurement->image_path];
        }

        // Si se envió lista de remaining_images o existing_photos, filtrar
        if ($request->has('remaining_images') || $request->has('existing_photos')) {
            $rawRemaining = $request->input('remaining_images') ?? $request->input('existing_photos');
            $remaining = is_array($rawRemaining) ? $rawRemaining : (json_decode($rawRemaining, true) ?: []);
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

        $newImages = [];
        if ($request->hasFile('photos')) {
            $destPath = public_path('uploads/measurements/dosimetria/' . $moduleId);
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0777, true, true);
            }
            foreach ($request->file('photos') as $photo) {
                if ($photo->isValid()) {
                    $filename = 'dosi_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
                    $photo->move($destPath, $filename);
                    $newImages[] = 'uploads/measurements/dosimetria/' . $moduleId . '/' . $filename;
                }
            }
        }

        $allImages = array_values(array_merge($existingImages, $newImages));

        $tpe = (float) ($validated['tiempo_expos_h'] ?? 8.0);
        $lmp = self::getLmpForTpe($tpe);
        $leqT = (float) $validated['leq_t_db'];
        $cumple = ($leqT <= $lmp) ? 'SI' : 'NO';

        $duracion = (float) ($validated['duracion_medicion_h'] ?? 0.0);
        $dosis = ($duracion > 0 && $tpe > 0) ? round(100.0 * ($duracion / 8.0) * pow(2.0, ($leqT - 85.0) / 3.0), 2) : null;

        $measurement->update([
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? $measurement->measurement_time,
            'area' => $validated['area'],
            'punto_medicion' => $validated['punto_medicion'],
            'tipo_ruido' => $validated['tipo_ruido'],
            'tiempo_expos_h' => $tpe,
            'ponderacion' => $validated['ponderacion'] ?? $measurement->ponderacion,
            'respuesta' => $validated['respuesta'] ?? $measurement->respuesta,
            'duracion_medicion_h' => $duracion,
            'nps_max_db' => $validated['nps_max_db'] ?? null,
            'nps_min_db' => $validated['nps_min_db'] ?? null,
            'leq_t_db' => $leqT,
            'dosis_pct' => $dosis,
            'cumple' => $cumple,
            'location' => $validated['location'] ?? $measurement->location,
            'latitude' => $validated['latitude'] ?? $measurement->latitude,
            'longitude' => $validated['longitude'] ?? $measurement->longitude,
            'utm_zone' => $validated['utm_zone'] ?? $measurement->utm_zone,
            'utm_easting' => $validated['utm_easting'] ?? $measurement->utm_easting,
            'utm_northing' => $validated['utm_northing'] ?? $measurement->utm_northing,
            'image_path' => $allImages[0] ?? null,
            'images' => $allImages,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $validated['registered_by'] ?? $measurement->registered_by,
            'staff_id' => $validated['staff_id'] ?? $measurement->staff_id,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Medición de dosimetría actualizada correctamente.',
                'measurement' => $measurement,
            ]);
        }

        return redirect()->route('modules.dosimetry', $moduleId)
            ->with('success', 'Medición actualizada exitosamente.');
    }

    /**
     * Delete a dosimetry measurement.
     */
    public function destroyMeasurement(Request $request, $moduleId, $measurementId)
    {
        $measurement = DosimetryMeasurement::where('module_id', $moduleId)->findOrFail($measurementId);
        
        $images = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        foreach ($images as $img) {
            $path = public_path($img);
            if (File::exists($path)) {
                File::delete($path);
            }
        }

        $measurement->delete();

        $module = MeasurementModule::find($moduleId);
        if ($module) {
            $module->update(['points_completed' => $module->dosimetryMeasurements()->count()]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Medición eliminada correctamente.',
            ]);
        }

        return redirect()->route('modules.dosimetry', $moduleId)
            ->with('success', 'Medición eliminada exitosamente.');
    }

    /**
     * Update header settings for the module.
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'installation_name' => 'nullable|string|max:255',
            'monitoring_type' => 'nullable|string|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'equipment_id' => 'nullable|exists:equipment,id',
            'field_staff_ids' => 'nullable|array',
            'field_staff_ids.*' => 'exists:staff,id',
        ]);

        $module->update([
            'installation_name' => $validated['installation_name'] ?? $module->installation_name,
            'monitoring_type' => $validated['monitoring_type'] ?? $module->monitoring_type,
            'start_date' => $validated['start_date'] ?? $module->start_date,
            'end_date' => $validated['end_date'] ?? $module->end_date,
            'equipment_id' => $validated['equipment_id'] ?? $module->equipment_id,
            'field_staff_ids' => $validated['field_staff_ids'] ?? $module->field_staff_ids,
        ]);

        return redirect()->route('modules.dosimetry', $moduleId)
            ->with('success', 'Encabezado técnico actualizado correctamente.');
    }

    /**
     * Save photo report settings.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $settings = $request->input('settings', []);

        $module->update([
            'photo_report_settings' => $settings,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de reporte fotográfico guardada.',
        ]);
    }

    /**
     * Conversor básico de Coordenadas UTM a Latitud/Longitud para Leaflet Maps
     */
    private function utmToLatLng($easting, $northing, $zoneStr)
    {
        $easting = (float) $easting;
        $northing = (float) $northing;

        preg_match('/(\d+)\s*([A-Za-z]?)/', $zoneStr, $m);
        $zone = !empty($m[1]) ? (int)$m[1] : 20;
        $isSouth = true; // Bolivia / Cono Sur por defecto
        if (!empty($m[2]) && strtoupper($m[2]) >= 'N') {
            $isSouth = false;
        }

        $a = 6378137.0; // WGS84
        $f = 1 / 298.257223563;
        $k0 = 0.9996;
        $e = sqrt(2 * $f - $f * $f);
        $e1sq = $e * $e / (1 - $e * $e);

        $x = $easting - 500000.0;
        $y = $northing;
        if ($isSouth) {
            $y -= 10000000.0;
        }

        $m_val = $y / $k0;
        $mu = $m_val / ($a * (1 - $e * $e / 4 - 3 * $e * $e * $e * $e / 64 - 5 * $e * $e * $e * $e * $e * $e / 256));

        $e1 = (1 - sqrt(1 - $e * $e)) / (1 + sqrt(1 - $e * $e));
        $phi1Rad = $mu + (3 * $e1 / 2 - 27 * $e1 * $e1 * $e1 / 32) * sin(2 * $mu)
            + (21 * $e1 * $e1 / 16 - 55 * $e1 * $e1 * $e1 * $e1 / 32) * sin(4 * $mu)
            + (151 * $e1 * $e1 * $e1 / 96) * sin(6 * $mu);

        $sinPhi1 = sin($phi1Rad);
        $cosPhi1 = cos($phi1Rad);
        $tanPhi1 = tan($phi1Rad);

        $n1 = $a / sqrt(1 - $e * $e * $sinPhi1 * $sinPhi1);
        $t1 = $tanPhi1 * $tanPhi1;
        $c1 = $e1sq * $cosPhi1 * $cosPhi1;
        $r1 = $a * (1 - $e * $e) / pow(1 - $e * $e * $sinPhi1 * $sinPhi1, 1.5);
        $d_val = $x / ($n1 * $k0);

        $lat = $phi1Rad - ($n1 * $tanPhi1 / $r1) * ($d_val * $d_val / 2
            - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 * $c1 - 9 * $e1sq) * pow($d_val, 4) / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 * $t1 - 252 * $e1sq - 3 * $c1 * $c1) * pow($d_val, 6) / 720);
        $lat = rad2deg($lat);

        $lon0 = ($zone - 1) * 6 - 180 + 3;
        $lon = ($d_val - (1 + 2 * $t1 + $c1) * pow($d_val, 3) / 6
            + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 * $c1 + 8 * $e1sq + 24 * $t1 * $t1) * pow($d_val, 5) / 120) / $cosPhi1;
        $lon = $lon0 + rad2deg($lon);

        return ['lat' => round($lat, 7), 'lng' => round($lon, 7)];
    }

    /**
     * Display the official technical report page for noise dosimetry monitoring (Landscape Sheet).
     */
    public function showReport($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Technical Header Information: Razón Social de la empresa o proyecto para Instalación
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

        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : ($module->created_at ? $module->created_at->format('d/m/Y') : date('d/m/Y'));

        $monitoringType = $module->monitoring_type ?: 'Seguimiento';

        // Assigned Equipment
        $equipment = $module->equipment;
        $reportSettings = $module->photo_report_settings ?: [];

        $equipmentName = !empty($reportSettings['equipment_name']) ? $reportSettings['equipment_name'] : ($equipment ? ($equipment->name ?: 'DOSIMETRO-INLITE-1') : ($module->calibration_equipment ?: 'DOSIMETRO-INLITE-1'));
        $equipmentBrand = !empty($reportSettings['equipment_brand']) ? $reportSettings['equipment_brand'] : ($equipment ? ($equipment->brand ?: 'INLITE') : 'INLITE');
        $equipmentModel = !empty($reportSettings['equipment_model']) ? $reportSettings['equipment_model'] : ($equipment ? ($equipment->model ?: 'DoseMax V2') : 'DoseMax V2');
        $equipmentSerial = !empty($reportSettings['equipment_serial']) ? $reportSettings['equipment_serial'] : ($equipment ? ($equipment->serial_number ?: '2512071420AA') : '2512071420AA');

        // Staff
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Campo';
        }

        // Measurements List
        $dbMeasurements = $module->dosimetryMeasurements()->with('staff')->get();
        $measurementsList = [];

        if ($dbMeasurements->isNotEmpty()) {
            foreach ($dbMeasurements as $index => $item) {
                $tpe = (float) ($item->tiempo_expos_h ?? 8.0);
                $duracionMed = (float) ($item->duracion_medicion_h ?? 0.0);
                $npsMax = $item->nps_max_db !== null ? (float) $item->nps_max_db : null;
                $npsMin = $item->nps_min_db !== null ? (float) $item->nps_min_db : null;
                $leqT = $item->leq_t_db !== null ? (float) $item->leq_t_db : null;

                // 1) Nivel de presión sonora diario equivalente Laeq,d (dBA) = Leq,T + 10 * log10(TPE / 8)
                $laeqD = null;
                if ($leqT !== null && $tpe > 0) {
                    $laeqD = round($leqT + (10.0 * log10($tpe / 8.0)), 2);
                }

                // 2) Dosis de ruido para estudios a 8 horas = 10^((Laeq,d - 85) / 10)
                $dosisRuido = null;
                if ($laeqD !== null) {
                    $dosisRuido = round(pow(10.0, ($laeqD - 85.0) / 10.0), 2);
                }

                $acciones = ($item->observations && $item->observations !== 'Sin observaciones') ? $item->observations : '';
                if (empty($acciones)) {
                    if ($dosisRuido !== null && $dosisRuido >= 1.0) {
                        $acciones = 'Uso obligatorio de EPP auditivo y rotación';
                    } elseif ($dosisRuido !== null && $dosisRuido >= 0.5) {
                        $acciones = 'Capacitación y monitoreo periódico';
                    } else {
                        $acciones = 'Ninguna';
                    }
                }

                $measurementsList[] = [
                    'id' => $item->id,
                    'num' => $item->point_number ?: ($index + 1),
                    'area' => $item->area ?: 'Área Operativa',
                    'punto_medicion' => $item->punto_medicion ?: 'Punto de Medición',
                    'tipo_ruido' => $item->tipo_ruido ?: 'Fluctuante',
                    'tiempo_expos_h' => $tpe,
                    'ponderacion' => $item->ponderacion ?: 'A',
                    'respuesta' => strtoupper($item->respuesta ?: 'LENTA'),
                    'duracion_medicion_h' => $duracionMed,
                    'nps_max_db' => $npsMax,
                    'nps_min_db' => $npsMin,
                    'leq_t_db' => $leqT,
                    'laeq_d_db' => $laeqD,
                    'dosis_ruido' => $dosisRuido,
                    'acciones_tomar' => $acciones,
                ];
            }
        } else {
            // Mock sample rows matching the official dosimetry table
            $measurementsList = [
                [
                    'id' => 1,
                    'num' => 1,
                    'area' => 'Materia Prima',
                    'punto_medicion' => 'Pala frontal',
                    'tipo_ruido' => 'Fluctuante',
                    'tiempo_expos_h' => 4.0,
                    'ponderacion' => 'A',
                    'respuesta' => 'LENTA',
                    'duracion_medicion_h' => 0.50,
                    'nps_max_db' => 100.00,
                    'nps_min_db' => 80.00,
                    'leq_t_db' => 72.00,
                    'laeq_d_db' => 68.99,
                    'dosis_ruido' => 0.03,
                    'acciones_tomar' => 'Usos de EPP',
                ],
                [
                    'id' => 2,
                    'num' => 2,
                    'area' => 'Dosificación - Carguio',
                    'punto_medicion' => 'Operario',
                    'tipo_ruido' => 'Fluctuante',
                    'tiempo_expos_h' => 6.0,
                    'ponderacion' => 'A',
                    'respuesta' => 'LENTA',
                    'duracion_medicion_h' => 3.00,
                    'nps_max_db' => 85.70,
                    'nps_min_db' => 82.10,
                    'leq_t_db' => 84.60,
                    'laeq_d_db' => 83.35,
                    'dosis_ruido' => 0.68,
                    'acciones_tomar' => 'Capacitación',
                ],
                [
                    'id' => 3,
                    'num' => 3,
                    'area' => 'Molienda y Trituración',
                    'punto_medicion' => 'Operador de Molino',
                    'tipo_ruido' => 'Continuo',
                    'tiempo_expos_h' => 8.0,
                    'ponderacion' => 'A',
                    'respuesta' => 'LENTA',
                    'duracion_medicion_h' => 4.00,
                    'nps_max_db' => 92.40,
                    'nps_min_db' => 86.20,
                    'leq_t_db' => 88.50,
                    'laeq_d_db' => 88.50,
                    'dosis_ruido' => 2.24,
                    'acciones_tomar' => 'Uso obligatorio de EPP tipo copa y rotación de personal',
                ],
                [
                    'id' => 4,
                    'num' => 4,
                    'area' => 'Taller de Mantenimiento',
                    'punto_medicion' => 'Mecánico',
                    'tipo_ruido' => 'Intermitente',
                    'tiempo_expos_h' => 8.0,
                    'ponderacion' => 'A',
                    'respuesta' => 'LENTA',
                    'duracion_medicion_h' => 2.50,
                    'nps_max_db' => 95.80,
                    'nps_min_db' => 78.30,
                    'leq_t_db' => 82.10,
                    'laeq_d_db' => 82.10,
                    'dosis_ruido' => 0.51,
                    'acciones_tomar' => 'Inspección periódica y uso de tapones auditivos',
                ],
            ];
        }

        return view('measurements.dosimetrias.report', compact(
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
            'registeredByHeader',
            'measurementsList',
            'reportSettings',
            'userName',
            'userRole'
        ));
    }

    /**
     * Save/autosave report header settings or overrides for dosimetry.
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

        // Custom equipment overrides in photo_report_settings if provided
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
            'message' => 'Informe de dosimetría guardado correctamente.',
        ]);
    }
}
