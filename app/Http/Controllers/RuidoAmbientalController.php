<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\RuidoAmbientalMeasurement;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class RuidoAmbientalController extends Controller
{
    /**
     * Calcula el Leq (Nivel de Presión Sonora Continuo Equivalente) logarítmico real
     * según la fórmula internacional ISO 1996 / EPA / RASIM:
     * Leq = 10 * log10( (1/N) * sum(10^(Li/10)) )
     */
    public static function calculateLeq(array $values): ?float
    {
        $filtered = array_values(array_filter($values, function ($v) {
            return is_numeric($v) && (float) $v > 0;
        }));

        $n = count($filtered);
        if ($n === 0) {
            return null;
        }

        $sum = 0.0;
        foreach ($filtered as $v) {
            $sum += pow(10.0, ((float) $v) / 10.0);
        }

        $leq = 10.0 * log10($sum / $n);
        return round($leq, 1);
    }

    /**
     * Display the Ruido Ambiental monitoring page for a module.
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

        $monitoringType = $module->monitoring_type ?: 'Seguimiento Perimetral';

        // Equipo asignado (Sonómetro / Analizador Acústico)
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Sonómetro Integrador Clase 1');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'SVANTEK') : 'SVANTEK';
        $equipmentModel = $equipment ? ($equipment->model ?: 'SV 971A') : 'SV 971A';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '924810') : '924810';
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
        $measurements = $module->ruidoAmbientalMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
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
                            $conv = $this->utmToLatLng((float)$d['easting'], (float)$d['northing'], $d['utm_zone'] ?? $d['zone'] ?? '19K');
                            $lat = $conv['lat'];
                            $lng = $conv['lng'];
                        }
                    }
                } elseif (preg_match('/E:\s*([0-9.]+)/i', $loc, $mE) && preg_match('/N:\s*([0-9.]+)/i', $loc, $mN)) {
                    preg_match('/Z:\s*([0-9A-Za-z]+)/i', $loc, $mZ);
                    $conv = $this->utmToLatLng((float)$mE[1], (float)$mN[1], $mZ[1] ?? '19K');
                    $lat = $conv['lat'];
                    $lng = $conv['lng'];
                }
            }

            // Si aún no tiene lat/lng pero tiene norte_x / norte_y
            if (($lat === null || $lng === null) && !empty($item->norte_x) && !empty($item->norte_y)) {
                $eNum = (float) preg_replace('/[^0-9.]/', '', $item->norte_x);
                $nNum = (float) preg_replace('/[^0-9.]/', '', $item->norte_y);
                if ($eNum > 0 && $nNum > 0) {
                    $conv = $this->utmToLatLng($eNum, $nNum, $item->zona_banda ?: '19K');
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

            // Procesar puntos cardinales
            $p1Puntos = is_array($item->p1_norte_puntos) ? $item->p1_norte_puntos : (json_decode($item->p1_norte_puntos, true) ?: []);
            $p2Puntos = is_array($item->p2_sur_puntos) ? $item->p2_sur_puntos : (json_decode($item->p2_sur_puntos, true) ?: []);
            $p3Puntos = is_array($item->p3_este_puntos) ? $item->p3_este_puntos : (json_decode($item->p3_este_puntos, true) ?: []);
            $p4Puntos = is_array($item->p4_oeste_puntos) ? $item->p4_oeste_puntos : (json_decode($item->p4_oeste_puntos, true) ?: []);

            $allPuntos = array_merge($p1Puntos, $p2Puntos, $p3Puntos, $p4Puntos);
            $leqCalculado = self::calculateLeq($allPuntos);
            $leqFinal = $item->leq_d !== null ? (float) $item->leq_d : $leqCalculado;

            $limite = $item->limite_normativa !== null ? (float) $item->limite_normativa : 68.0;
            $isCompliant = ($leqFinal !== null) ? ($leqFinal <= $limite) : true;
            $cumple = ($leqFinal !== null) ? ($isCompliant ? 'SI' : 'NO') : '—';

            $npsMax = !empty($allPuntos) ? max($allPuntos) : ($item->nps_max ?? null);
            $npsMin = !empty($allPuntos) ? min($allPuntos) : ($item->nps_min ?? null);

            return [
                'id' => $item->id,
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '—',
                'normativa' => $item->normativa ?: 'RASIM - ANEXO 12-C',
                'tipo_zona' => $item->tipo_zona ?: 'Industrial - día',
                'horario' => $item->horario ?: '08:00 a 22:00',
                'limite_normativa' => number_format($limite, 1, ',', '.'),
                'raw_limite_normativa' => $limite,
                'zona_banda' => $item->zona_banda ?: '19K',
                
                // Colindancias
                'norte_colindancia' => $item->norte_colindancia ?: 'Calle / Vía Principal',
                'norte_x' => $item->norte_x ?: '—',
                'norte_y' => $item->norte_y ?: '—',
                'sur_colindancia' => $item->sur_colindancia ?: 'Área Industrial Vecina',
                'sur_x' => $item->sur_x ?: '—',
                'sur_y' => $item->sur_y ?: '—',
                'este_colindancia' => $item->este_colindancia ?: 'Terreno Abierto',
                'este_x' => $item->este_x ?: '—',
                'este_y' => $item->este_y ?: '—',
                'oeste_colindancia' => $item->oeste_colindancia ?: 'Instalaciones Comerciales',
                'oeste_x' => $item->oeste_x ?: '—',
                'oeste_y' => $item->oeste_y ?: '—',
                
                // Puntos Cardinales
                'p1_norte_inicio' => $item->p1_norte_inicio ?: '—',
                'p1_norte_fin' => $item->p1_norte_fin ?: '—',
                'p1_norte_puntos' => $p1Puntos,
                'p1_norte_leq' => self::calculateLeq($p1Puntos),
                
                'p2_sur_inicio' => $item->p2_sur_inicio ?: '—',
                'p2_sur_fin' => $item->p2_sur_fin ?: '—',
                'p2_sur_puntos' => $p2Puntos,
                'p2_sur_leq' => self::calculateLeq($p2Puntos),
                
                'p3_este_inicio' => $item->p3_este_inicio ?: '—',
                'p3_este_fin' => $item->p3_este_fin ?: '—',
                'p3_este_puntos' => $p3Puntos,
                'p3_este_leq' => self::calculateLeq($p3Puntos),
                
                'p4_oeste_inicio' => $item->p4_oeste_inicio ?: '—',
                'p4_oeste_fin' => $item->p4_oeste_fin ?: '—',
                'p4_oeste_puntos' => $p4Puntos,
                'p4_oeste_leq' => self::calculateLeq($p4Puntos),
                
                'mediciones_db' => $allPuntos,
                'total_readings_count' => count($allPuntos),
                'leq_d' => $leqFinal !== null ? number_format($leqFinal, 1, ',', '.') : '—',
                'raw_leq_d' => $leqFinal,
                'nps_max' => $npsMax !== null ? number_format($npsMax, 1, ',', '.') : '—',
                'raw_nps_max' => $npsMax,
                'nps_min' => $npsMin !== null ? number_format($npsMin, 1, ',', '.') : '—',
                'raw_nps_min' => $npsMin,
                'cumple' => $cumple,
                'is_compliant' => $isCompliant,
                
                'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                'raw_image_path' => $item->image_path,
                'images' => $imagesUrls,
                'images_count' => count($imagesUrls),
                'location' => $item->location ?: ($lat && $lng ? "{$lat}, {$lng}" : '—'),
                'latitude' => $lat,
                'longitude' => $lng,
                'observations' => $item->observations ?: 'Sin observaciones registradas.',
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

        // Totales y estadísticas de cumplimiento
        $totalMeasurements = $measurements->count();
        $compliantMeasurements = $measurements->where('is_compliant', true)->count();
        $nonCompliantMeasurements = $totalMeasurements - $compliantMeasurements;
        $complianceRate = $totalMeasurements > 0 ? round(($compliantMeasurements / $totalMeasurements) * 100, 1) : 100.0;

        $allLeqValues = $measurements->pluck('raw_leq_d')->filter()->values()->all();
        $avgLeq = !empty($allLeqValues) ? round(array_sum($allLeqValues) / count($allLeqValues), 1) : null;
        $maxLeq = !empty($allLeqValues) ? max($allLeqValues) : null;
        $minLeq = !empty($allLeqValues) ? min($allLeqValues) : null;

        $photoReportSettings = $module->photo_report_settings ?: [];

        return view('measurements.ruido_ambientales.index', compact(
            'userName',
            'userRole',
            'module',
            'projectName',
            'companyName',
            'installationName',
            'startDateFormatted',
            'endDateFormatted',
            'startDateRaw',
            'endDateRaw',
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
            'compliantMeasurements',
            'nonCompliantMeasurements',
            'complianceRate',
            'avgLeq',
            'maxLeq',
            'minLeq',
            'photoReportSettings'
        ));
    }

    /**
     * Store a newly created Ruido Ambiental measurement.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string',
            'normativa' => 'required|string',
            'tipo_zona' => 'required|string',
            'horario' => 'nullable|string',
            'limite_normativa' => 'required|numeric',
            'zona_banda' => 'nullable|string',
            
            // Colindancias
            'norte_colindancia' => 'nullable|string',
            'norte_x' => 'nullable|string',
            'norte_y' => 'nullable|string',
            'sur_colindancia' => 'nullable|string',
            'sur_x' => 'nullable|string',
            'sur_y' => 'nullable|string',
            'este_colindancia' => 'nullable|string',
            'este_x' => 'nullable|string',
            'este_y' => 'nullable|string',
            'oeste_colindancia' => 'nullable|string',
            'oeste_x' => 'nullable|string',
            'oeste_y' => 'nullable|string',
            
            // Horas y puntos
            'p1_norte_inicio' => 'nullable|string',
            'p1_norte_fin' => 'nullable|string',
            'p1_norte_puntos' => 'nullable',
            'p2_sur_inicio' => 'nullable|string',
            'p2_sur_fin' => 'nullable|string',
            'p2_sur_puntos' => 'nullable',
            'p3_este_inicio' => 'nullable|string',
            'p3_este_fin' => 'nullable|string',
            'p3_este_puntos' => 'nullable',
            'p4_oeste_inicio' => 'nullable|string',
            'p4_oeste_fin' => 'nullable|string',
            'p4_oeste_puntos' => 'nullable',
            
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string',
            'photos.*' => 'nullable|image|max:10240',
        ]);

        $parsePointsArray = function ($input) {
            if (is_array($input)) {
                return array_values(array_filter(array_map('floatval', $input)));
            }
            if (is_string($input) && !empty(trim($input))) {
                $decoded = json_decode($input, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('floatval', $decoded)));
                }
                $parts = explode(',', $input);
                return array_values(array_filter(array_map('floatval', $parts)));
            }
            return [];
        };

        $p1 = $parsePointsArray($request->input('p1_norte_puntos'));
        $p2 = $parsePointsArray($request->input('p2_sur_puntos'));
        $p3 = $parsePointsArray($request->input('p3_este_puntos'));
        $p4 = $parsePointsArray($request->input('p4_oeste_puntos'));

        $allPoints = array_merge($p1, $p2, $p3, $p4);
        $leq = self::calculateLeq($allPoints);
        $limite = (float) $validated['limite_normativa'];
        $isCompliant = ($leq !== null) ? ($leq <= $limite) : true;
        $npsMax = !empty($allPoints) ? max($allPoints) : null;
        $npsMin = !empty($allPoints) ? min($allPoints) : null;

        // Manejo de imágenes
        $uploadedImages = [];
        if ($request->hasFile('photos')) {
            $destDir = public_path('uploads/ruido_ambiental');
            if (!File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                $fileName = 'ra_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destDir, $fileName);
                $uploadedImages[] = 'uploads/ruido_ambiental/' . $fileName;
            }
        }

        $nextNum = $module->ruidoAmbientalMeasurements()->count() + 1;
        $pointNumber = 'P-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);

        $m = new RuidoAmbientalMeasurement();
        $m->module_id = $module->id;
        $m->project_id = $module->project_id;
        $m->staff_id = $validated['staff_id'] ?? $module->field_staff_id;
        $m->point_number = $pointNumber;
        $m->measurement_date = $validated['measurement_date'];
        $m->measurement_time = $validated['measurement_time'] ?? date('H:i');
        
        $m->normativa = $validated['normativa'];
        $m->tipo_zona = $validated['tipo_zona'];
        $m->horario = $validated['horario'] ?? null;
        $m->limite_normativa = $limite;
        $m->zona_banda = $validated['zona_banda'] ?? '19K';
        
        $m->norte_colindancia = $validated['norte_colindancia'] ?? null;
        $m->norte_x = $validated['norte_x'] ?? null;
        $m->norte_y = $validated['norte_y'] ?? null;
        $m->sur_colindancia = $validated['sur_colindancia'] ?? null;
        $m->sur_x = $validated['sur_x'] ?? null;
        $m->sur_y = $validated['sur_y'] ?? null;
        $m->este_colindancia = $validated['este_colindancia'] ?? null;
        $m->este_x = $validated['este_x'] ?? null;
        $m->este_y = $validated['este_y'] ?? null;
        $m->oeste_colindancia = $validated['oeste_colindancia'] ?? null;
        $m->oeste_x = $validated['oeste_x'] ?? null;
        $m->oeste_y = $validated['oeste_y'] ?? null;
        
        $m->p1_norte_inicio = $validated['p1_norte_inicio'] ?? null;
        $m->p1_norte_fin = $validated['p1_norte_fin'] ?? null;
        $m->p1_norte_puntos = $p1;
        
        $m->p2_sur_inicio = $validated['p2_sur_inicio'] ?? null;
        $m->p2_sur_fin = $validated['p2_sur_fin'] ?? null;
        $m->p2_sur_puntos = $p2;
        
        $m->p3_este_inicio = $validated['p3_este_inicio'] ?? null;
        $m->p3_este_fin = $validated['p3_este_fin'] ?? null;
        $m->p3_este_puntos = $p3;
        
        $m->p4_oeste_inicio = $validated['p4_oeste_inicio'] ?? null;
        $m->p4_oeste_fin = $validated['p4_oeste_fin'] ?? null;
        $m->p4_oeste_puntos = $p4;
        
        $m->mediciones_db = $allPoints;
        $m->leq_d = $leq;
        $m->nps_max = $npsMax;
        $m->nps_min = $npsMin;
        $m->is_compliant = $isCompliant;
        
        $m->latitude = $validated['latitude'] ?? null;
        $m->longitude = $validated['longitude'] ?? null;
        $m->location = $validated['location'] ?? null;
        
        if (!empty($uploadedImages)) {
            $m->image_path = $uploadedImages[0];
            $m->images = $uploadedImages;
        }
        
        $m->observations = $validated['observations'] ?? null;
        $m->registered_by = $validated['registered_by'] ?? (Auth::user() ? Auth::user()->name : 'Técnico');
        $m->save();

        // Actualizar progreso del módulo
        $ptsDone = $module->ruidoAmbientalMeasurements()->count();
        $module->points_completed = $ptsDone;
        if ($ptsDone >= $module->points_total) {
            $module->status = 'Completado';
            $module->status_theme = 'done';
        } else {
            $module->status = 'En Progreso';
            $module->status_theme = 'in_progress';
        }
        $module->save();

        return redirect()->route('modules.ruido_ambiental', $moduleId)->with('success', "Punto de medición '{$pointNumber}' registrado exitosamente con Leq {$leq} dBA.");
    }

    /**
     * Update an existing Ruido Ambiental measurement.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $m = RuidoAmbientalMeasurement::where('module_id', $moduleId)->findOrFail($measurementId);

        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string',
            'normativa' => 'required|string',
            'tipo_zona' => 'required|string',
            'horario' => 'nullable|string',
            'limite_normativa' => 'required|numeric',
            'zona_banda' => 'nullable|string',
            
            // Colindancias
            'norte_colindancia' => 'nullable|string',
            'norte_x' => 'nullable|string',
            'norte_y' => 'nullable|string',
            'sur_colindancia' => 'nullable|string',
            'sur_x' => 'nullable|string',
            'sur_y' => 'nullable|string',
            'este_colindancia' => 'nullable|string',
            'este_x' => 'nullable|string',
            'este_y' => 'nullable|string',
            'oeste_colindancia' => 'nullable|string',
            'oeste_x' => 'nullable|string',
            'oeste_y' => 'nullable|string',
            
            // Horas y puntos
            'p1_norte_inicio' => 'nullable|string',
            'p1_norte_fin' => 'nullable|string',
            'p1_norte_puntos' => 'nullable',
            'p2_sur_inicio' => 'nullable|string',
            'p2_sur_fin' => 'nullable|string',
            'p2_sur_puntos' => 'nullable',
            'p3_este_inicio' => 'nullable|string',
            'p3_este_fin' => 'nullable|string',
            'p3_este_puntos' => 'nullable',
            'p4_oeste_inicio' => 'nullable|string',
            'p4_oeste_fin' => 'nullable|string',
            'p4_oeste_puntos' => 'nullable',
            
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string',
            'photos.*' => 'nullable|image|max:10240',
        ]);

        $parsePointsArray = function ($input) {
            if (is_array($input)) {
                return array_values(array_filter(array_map('floatval', $input)));
            }
            if (is_string($input) && !empty(trim($input))) {
                $decoded = json_decode($input, true);
                if (is_array($decoded)) {
                    return array_values(array_filter(array_map('floatval', $decoded)));
                }
                $parts = explode(',', $input);
                return array_values(array_filter(array_map('floatval', $parts)));
            }
            return [];
        };

        $p1 = $parsePointsArray($request->input('p1_norte_puntos'));
        $p2 = $parsePointsArray($request->input('p2_sur_puntos'));
        $p3 = $parsePointsArray($request->input('p3_este_puntos'));
        $p4 = $parsePointsArray($request->input('p4_oeste_puntos'));

        $allPoints = array_merge($p1, $p2, $p3, $p4);
        $leq = self::calculateLeq($allPoints);
        $limite = (float) $validated['limite_normativa'];
        $isCompliant = ($leq !== null) ? ($leq <= $limite) : true;
        $npsMax = !empty($allPoints) ? max($allPoints) : null;
        $npsMin = !empty($allPoints) ? min($allPoints) : null;

        // Imágenes existentes
        $currentImages = is_array($m->images) ? $m->images : (json_decode($m->images, true) ?: []);
        if (empty($currentImages) && !empty($m->image_path)) {
            $currentImages = [$m->image_path];
        }

        if ($request->hasFile('photos')) {
            $destDir = public_path('uploads/ruido_ambiental');
            if (!File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                $fileName = 'ra_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destDir, $fileName);
                $currentImages[] = 'uploads/ruido_ambiental/' . $fileName;
            }
        }

        $m->measurement_date = $validated['measurement_date'];
        $m->measurement_time = $validated['measurement_time'] ?? $m->measurement_time;
        if (!empty($validated['staff_id'])) {
            $m->staff_id = $validated['staff_id'];
        }
        
        $m->normativa = $validated['normativa'];
        $m->tipo_zona = $validated['tipo_zona'];
        $m->horario = $validated['horario'] ?? $m->horario;
        $m->limite_normativa = $limite;
        $m->zona_banda = $validated['zona_banda'] ?? $m->zona_banda;
        
        $m->norte_colindancia = $validated['norte_colindancia'] ?? $m->norte_colindancia;
        $m->norte_x = $validated['norte_x'] ?? $m->norte_x;
        $m->norte_y = $validated['norte_y'] ?? $m->norte_y;
        $m->sur_colindancia = $validated['sur_colindancia'] ?? $m->sur_colindancia;
        $m->sur_x = $validated['sur_x'] ?? $m->sur_x;
        $m->sur_y = $validated['sur_y'] ?? $m->sur_y;
        $m->este_colindancia = $validated['este_colindancia'] ?? $m->este_colindancia;
        $m->este_x = $validated['este_x'] ?? $m->este_x;
        $m->este_y = $validated['este_y'] ?? $m->este_y;
        $m->oeste_colindancia = $validated['oeste_colindancia'] ?? $m->oeste_colindancia;
        $m->oeste_x = $validated['oeste_x'] ?? $m->oeste_x;
        $m->oeste_y = $validated['oeste_y'] ?? $m->oeste_y;
        
        $m->p1_norte_inicio = $validated['p1_norte_inicio'] ?? $m->p1_norte_inicio;
        $m->p1_norte_fin = $validated['p1_norte_fin'] ?? $m->p1_norte_fin;
        $m->p1_norte_puntos = $p1;
        
        $m->p2_sur_inicio = $validated['p2_sur_inicio'] ?? $m->p2_sur_inicio;
        $m->p2_sur_fin = $validated['p2_sur_fin'] ?? $m->p2_sur_fin;
        $m->p2_sur_puntos = $p2;
        
        $m->p3_este_inicio = $validated['p3_este_inicio'] ?? $m->p3_este_inicio;
        $m->p3_este_fin = $validated['p3_este_fin'] ?? $m->p3_este_fin;
        $m->p3_este_puntos = $p3;
        
        $m->p4_oeste_inicio = $validated['p4_oeste_inicio'] ?? $m->p4_oeste_inicio;
        $m->p4_oeste_fin = $validated['p4_oeste_fin'] ?? $m->p4_oeste_fin;
        $m->p4_oeste_puntos = $p4;
        
        $m->mediciones_db = $allPoints;
        $m->leq_d = $leq;
        $m->nps_max = $npsMax;
        $m->nps_min = $npsMin;
        $m->is_compliant = $isCompliant;
        
        if (!empty($validated['latitude'])) $m->latitude = $validated['latitude'];
        if (!empty($validated['longitude'])) $m->longitude = $validated['longitude'];
        if (!empty($validated['location'])) $m->location = $validated['location'];
        
        if (!empty($currentImages)) {
            $m->image_path = $currentImages[0];
            $m->images = $currentImages;
        }
        
        $m->observations = $validated['observations'] ?? $m->observations;
        if (!empty($validated['registered_by'])) {
            $m->registered_by = $validated['registered_by'];
        }
        $m->save();

        return redirect()->route('modules.ruido_ambiental', $moduleId)->with('success', "Punto de medición '{$m->point_number}' actualizado exitosamente.");
    }

    /**
     * Destroy a Ruido Ambiental measurement.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $m = RuidoAmbientalMeasurement::where('module_id', $moduleId)->findOrFail($measurementId);
        $pointNum = $m->point_number;
        $m->delete();

        $ptsDone = $module->ruidoAmbientalMeasurements()->count();
        $module->points_completed = $ptsDone;
        if ($ptsDone >= $module->points_total) {
            $module->status = 'Completado';
            $module->status_theme = 'done';
        } else {
            $module->status = 'En Progreso';
            $module->status_theme = 'in_progress';
        }
        $module->save();

        return redirect()->route('modules.ruido_ambiental', $moduleId)->with('success', "Punto de medición '{$pointNum}' eliminado exitosamente.");
    }

    /**
     * Update module header / technical settings.
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'installation_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'monitoring_type' => 'nullable|string|max:100',
            'calibration_equipment' => 'nullable|string|max:255',
            'equipment_id' => 'nullable|exists:equipment,id',
            'field_staff_ids' => 'nullable|array',
            'field_staff_ids.*' => 'exists:staff,id',
        ]);

        $module->installation_name = $validated['installation_name'] ?? $module->installation_name;
        if (!empty($validated['start_date'])) $module->start_date = $validated['start_date'];
        if (!empty($validated['end_date'])) $module->end_date = $validated['end_date'];
        if (!empty($validated['monitoring_type'])) $module->monitoring_type = $validated['monitoring_type'];
        if (!empty($validated['calibration_equipment'])) $module->calibration_equipment = $validated['calibration_equipment'];
        if (!empty($validated['equipment_id'])) $module->equipment_id = $validated['equipment_id'];
        if (!empty($validated['field_staff_ids'])) {
            $module->field_staff_ids = array_values(array_unique(array_map('intval', $validated['field_staff_ids'])));
            $module->field_staff_id = $module->field_staff_ids[0] ?? $module->field_staff_id;
        }
        $module->save();

        return redirect()->route('modules.ruido_ambiental', $moduleId)->with('success', 'Encabezado técnico y metadatos actualizados correctamente.');
    }

    /**
     * Save photo report layout settings.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $settings = $request->input('photo_report_settings', []);

        $module->photo_report_settings = $settings;
        $module->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Configuración de catálogo fotográfico guardada con éxito.',
        ]);
    }

    /**
     * UTM to Latitude/Longitude converter (WGS84)
     */
    private function utmToLatLng($easting, $northing, $zoneStr = '19K')
    {
        $zoneNumber = 19;
        $zoneLetter = 'K';
        if (preg_match('/([0-9]{1,2})\s*([C-X])/i', $zoneStr, $matches)) {
            $zoneNumber = (int)$matches[1];
            $zoneLetter = strtoupper($matches[2]);
        } elseif (preg_match('/([0-9]{1,2})/i', $zoneStr, $matches)) {
            $zoneNumber = (int)$matches[1];
        }

        $isNorthern = ($zoneLetter >= 'N');
        $a = 6378137.0; // Semi-eje mayor WGS84
        $f = 1 / 298.257223563;
        $b = $a * (1 - $f);
        $e2 = ($a * $a - $b * $b) / ($a * $a);
        $ePrime2 = ($a * $a - $b * $b) / ($b * $b);
        $k0 = 0.9996;

        $x = $easting - 500000.0;
        $y = $northing;
        if (!$isNorthern) {
            // Hemisferio sur: no restar falso norte si northing < 10000000
            if ($y > 5000000.0) {
                $y -= 10000000.0;
            }
        }

        $m = $y / $k0;
        $mu = $m / ($a * (1.0 - $e2 / 4.0 - 3.0 * $e2 * $e2 / 64.0 - 5.0 * $e2 * $e2 * $e2 / 256.0));

        $e1 = (1.0 - sqrt(1.0 - $e2)) / (1.0 + sqrt(1.0 - $e2));
        $j1 = (3.0 * $e1 / 2.0 - 27.0 * pow($e1, 3) / 32.0);
        $j2 = (21.0 * pow($e1, 2) / 16.0 - 55.0 * pow($e1, 4) / 32.0);
        $j3 = (151.0 * pow($e1, 3) / 96.0);
        $j4 = (1097.0 * pow($e1, 4) / 512.0);

        $fp = $mu + $j1 * sin(2.0 * $mu) + $j2 * sin(4.0 * $mu) + $j3 * sin(6.0 * $mu) + $j4 * sin(8.0 * $mu);

        $c1 = $ePrime2 * pow(cos($fp), 2);
        $t1 = pow(tan($fp), 2);
        $r1 = $a * (1.0 - $e2) / pow(1.0 - $e2 * pow(sin($fp), 2), 1.5);
        $n1 = $a / sqrt(1.0 - $e2 * pow(sin($fp), 2));

        $d = $x / ($n1 * $k0);

        $lat = $fp - ($n1 * tan($fp) / $r1) * ($d * $d / 2.0 - (5.0 + 3.0 * $t1 + 10.0 * $c1 - 4.0 * $c1 * $c1 - 9.0 * $ePrime2) * pow($d, 4) / 24.0 + (61.0 + 90.0 * $t1 + 298.0 * $c1 + 45.0 * $t1 * $t1 - 252.0 * $ePrime2 - 3.0 * $c1 * $c1) * pow($d, 6) / 720.0);
        $lat = rad2deg($lat);

        $lngOrigin = ($zoneNumber - 1) * 6 - 180 + 3;
        $lng = ($d - (1.0 + 2.0 * $t1 + $c1) * pow($d, 3) / 6.0 + (5.0 - 2.0 * $c1 + 28.0 * $t1 - 3.0 * $c1 * $c1 + 8.0 * $ePrime2 + 24.0 * $t1 * $t1) * pow($d, 5) / 120.0) / cos($fp);
        $lng = $lngOrigin + rad2deg($lng);

        return [
            'lat' => round($lat, 7),
            'lng' => round($lng, 7),
        ];
    }
}
