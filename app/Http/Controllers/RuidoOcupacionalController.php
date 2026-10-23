<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\NoiseMeasurement;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class RuidoOcupacionalController extends Controller
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
     * Display the Ruido Ocupacional monitoring page for a module.
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
        $measurements = $module->noiseMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
    $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';
    $lat = $item->latitude !== null ? (float) $item->latitude : null;
    $lng = $item->longitude !== null ? (float) $item->longitude : null;
    
    // Ubicación fallback
    if (($lat === null || $lng === null) && !empty($item->location)) {
        $loc = trim($item->location);
        if (str_starts_with($loc, '{') || str_starts_with($loc, '[')) {
            $d = json_decode($loc, true);
            if (is_array($d) && !empty($d['latitude']) && !empty($d['longitude'])) {
                $lat = (float) $d['latitude'];
                $lng = (float) $d['longitude'];
            }
        }
    }

    // Imágenes
    $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
    if (empty($rawImages) && !empty($item->image_path)) {
        $rawImages = [$item->image_path];
    }
    $imagesUrls = array_values(array_filter(array_map(function ($p) {
        return $p ? asset($p) : null;
    }, $rawImages)));

    // Mediciones NPS y cálculos NTS-002/17
    $medicionesDb = is_array($item->mediciones_db) ? $item->mediciones_db : (json_decode($item->mediciones_db, true) ?: []);
    $medicionesValidas = array_values(array_filter($medicionesDb, function($v) { return is_numeric($v) && (float)$v > 0; }));
    $leqCalculado = self::calculateLeq($medicionesValidas);
    $leqFinal = $item->leq_t_db !== null ? (float) $item->leq_t_db : $leqCalculado;
    $npsMax = !empty($medicionesValidas) ? max($medicionesValidas) : null;
    $tpe = $item->tiempo_expos_h !== null ? (float) $item->tiempo_expos_h : 8.0;
    $tmpe = ($leqFinal !== null && $leqFinal > 0) ? (8.0 / pow(2.0, ($leqFinal - 85.0) / 3.0)) : null;
    $dosis = ($tmpe !== null && $tmpe > 0) ? ($tpe / $tmpe) : null;

    // Personal que registró
    $registeredBy = (function () use ($item, $assignedStaff, $currentUser) {
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
    })();

    return [
        'id' => $item->id,
        'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
        'date' => $dateFormatted,
        'date_formatted' => $dateFormatted,
        'time' => $item->measurement_time ? substr($item->measurement_time, 0, 5) : '—',
        'area' => $item->area ?: 'Área General',
        'punto_medicion' => $item->punto_medicion ?: 'Punto ' . ($index + 1),
        'tipo_ruido' => $item->tipo_ruido ?: 'Continuo',
        'tiempo_expos_h' => $tpe,
        'ponderacion' => $item->ponderacion ?: 'A',
        'respuesta' => $item->respuesta ?: 'Lenta',
        'leq_t_db' => $leqFinal,
        'nps_max' => $npsMax,
        'mediciones_db' => $medicionesValidas,
        'registered_by' => $registeredBy,
        'latitude' => $lat,
        'longitude' => $lng,
        'images' => $imagesUrls,
        'image_path' => !empty($imagesUrls) ? $imagesUrls[0] : null,
        'images_count' => count($imagesUrls),
        'observations' => $item->observations ?: '',
        'tmpe' => $tmpe,
        'dosis' => $dosis,
        'tpe_supera_tmpe' => ($dosis !== null && $dosis > 1.0),
        'requiere_dosimetria' => (($dosis !== null && $dosis > 1.0) || ($leqFinal !== null && $leqFinal > 85.0)),
    ];
});
        $measurements = $measurements->values();

        // Estadísticas básicas
        $totalMeasurements = $measurements->count();

        $savedSettings = $module->photo_report_settings ?: [];
        $currentIds = array_values(array_filter(array_column($measurements->toArray(), 'id')));
        $savedSelected = $savedSettings['selected_points'] ?? [];

        // Validar que los puntos guardados existan actualmente
        $validSelected = (!empty($savedSelected) && is_array($savedSelected))
            ? array_values(array_intersect($savedSelected, $currentIds))
            : [];

        // Si no hay selección o ninguno de los guardados existe, seleccionar todos
        $finalSelectedPoints = (!empty($validSelected)) ? $validSelected : $currentIds;

        $photoReportSettings = [
            'grid' => $savedSettings['grid'] ?? ($savedSettings['distribution'] ?? '2x3'),
            'selected_points' => $finalSelectedPoints,
            'photo_indices' => $savedSettings['photo_indices'] ?? [],
            'image_fit' => $savedSettings['image_fit'] ?? 'contain',
            'photo_fits' => $savedSettings['photo_fits'] ?? [],
        ];

        return view('measurements.ruido_ocupacionales.index', compact(
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
            'photoReportSettings'
        ));
    }

    /**
     * Store a newly created Ruido Ocupacional measurement.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string',

            // Ubicación laboral
            'area' => 'required|string|max:255',
            'punto_medicion' => 'required|string|max:255',
            'tipo_ruido' => 'required|string|max:100',
            'actividad' => 'required|string|max:255',

            // Parámetros del sonómetro
            'ponderacion' => 'nullable|string|max:10',
            'respuesta' => 'nullable|string|max:10',
            'tiempo_expos_h' => 'required|numeric|min:0.1',

            // Mediciones NPS (JSON array)
            'mediciones_db' => 'nullable|string',

            // Protección auditiva
            'uso_protectores' => 'nullable',
            'tipo_protector' => 'nullable|string|max:255',
            'nrr_db' => 'nullable|numeric',

            // Ubicación y documentación
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string',
            'photos.*' => 'nullable|image|max:10240',
        ]);

        // Parse mediciones array from JSON
        $medicionesRaw = $request->input('mediciones_db', '[]');
        $mediciones = is_array($medicionesRaw)
            ? array_values(array_filter(array_map('floatval', $medicionesRaw), fn($v) => $v > 0))
            : array_values(array_filter(array_map('floatval', json_decode($medicionesRaw, true) ?: []), fn($v) => $v > 0));

        // Cálculos NTS-002/17
        $leq = self::calculateLeq($mediciones);
        $tpe = (float) ($validated['tiempo_expos_h'] ?? 8.0);
        $tmpe = ($leq !== null && $leq > 0) ? (8.0 / pow(2.0, ($leq - 85.0) / 3.0)) : null;
        $dosis = ($tmpe !== null && $tmpe > 0) ? ($tpe / $tmpe) : null;
        $cumple = ($dosis !== null) ? ($dosis <= 1.0) : true;

        // Manejo de imágenes
        $uploadedImages = [];
        if ($request->hasFile('photos')) {
            $destDir = public_path('uploads/ruido_ocupacional');
            if (!File::isDirectory($destDir)) {
                File::makeDirectory($destDir, 0755, true, true);
            }
            foreach ($request->file('photos') as $file) {
                $fileName = 'ro_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($destDir, $fileName);
                $uploadedImages[] = 'uploads/ruido_ocupacional/' . $fileName;
            }
        }

        $nextNum = $module->noiseMeasurements()->count() + 1;
        $pointNumber = 'P-' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);

        $m = new NoiseMeasurement();
        $m->module_id = $module->id;
        $m->staff_id = $validated['staff_id'] ?? $module->field_staff_id;
        $m->point_number = $pointNumber;
        $m->measurement_date = $validated['measurement_date'];
        $m->measurement_time = $validated['measurement_time'] ?? date('H:i');

        $m->area = $validated['area'];
        $m->punto_medicion = $validated['punto_medicion'];
        $m->tipo_ruido = $validated['tipo_ruido'];
        $m->actividad = $validated['actividad'];

        $m->ponderacion = $validated['ponderacion'] ?? 'A';
        $m->respuesta = $validated['respuesta'] ?? 'Lento';
        $m->tiempo_expos_h = $tpe;

        $m->mediciones_db = $mediciones;
        $m->leq_t_db = $leq;

        $m->uso_protectores = !empty($validated['uso_protectores']) && $validated['uso_protectores'] != '0';
        $m->tipo_protector = $validated['tipo_protector'] ?? null;
        $m->nrr_db = $validated['nrr_db'] ?? null;

        $m->cumple = $cumple;

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
        $ptsDone = $module->noiseMeasurements()->count();
        $module->points_completed = $ptsDone;
        if ($ptsDone >= $module->points_total) {
            $module->status = 'Completado';
            $module->status_theme = 'done';
        } else {
            $module->status = 'En Progreso';
            $module->status_theme = 'in_progress';
        }
        $module->save();

        return redirect()->route('modules.ruido_ocupacional', $moduleId)->with('success', "Punto de medición '{$pointNumber}' registrado exitosamente con LAeq {$leq} dBA.");
    }

    /**
     * Update an existing Ruido Ocupacional measurement.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $m = NoiseMeasurement::where('module_id', $moduleId)->findOrFail($measurementId);

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

        return redirect()->route('modules.ruido_ocupacional', $moduleId)->with('success', "Punto de medición '{$m->point_number}' actualizado exitosamente.");
    }

    /**
     * Destroy a Ruido Ocupacional measurement.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $m = NoiseMeasurement::where('module_id', $moduleId)->findOrFail($measurementId);
        $pointNum = $m->point_number;
        $m->delete();

        $ptsDone = $module->noiseMeasurements()->count();
        $module->points_completed = $ptsDone;
        if ($ptsDone >= $module->points_total) {
            $module->status = 'Completado';
            $module->status_theme = 'done';
        } else {
            $module->status = 'En Progreso';
            $module->status_theme = 'in_progress';
        }
        $module->save();

        return redirect()->route('modules.ruido_ocupacional', $moduleId)->with('success', "Punto de medición '{$pointNum}' eliminado exitosamente.");
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

        return redirect()->route('modules.ruido_ocupacional', $moduleId)->with('success', 'Encabezado técnico y metadatos actualizados correctamente.');
    }

    /**
     * Display the official technical report page for ruido ocupacional monitoring.
     */
    public function showReport($moduleId)
    {
        $currentUser = Auth::user();
        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);
        
        $projectName = $module->project ? $module->project->name : 'Proyecto';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : '';
        $installationName = $module->installation_name ?: ($projectName . ($companyName ? " - {$companyName}" : ''));
        
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';
        
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : 'Sonómetro Integrador';
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'SVANTEK') : 'SVANTEK';
        $equipmentModel = $equipment ? ($equipment->model ?: 'SV 971A') : 'SV 971A';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '924810') : '924810';
        
        $measurements = $module->noiseMeasurements()->orderBy('id', 'asc')->get()->map(function ($item) {
            $mediciones = is_string($item->mediciones_db) ? json_decode($item->mediciones_db, true) : $item->mediciones_db;
            $mediciones = is_array($mediciones) ? $mediciones : [];

            // NTS-002/17: Full calculation chain
            $validNps = array_values(array_filter(array_map('floatval', $mediciones), function ($v) {
                return $v > 0;
            }));
            $n = count($validNps);
            $sumaEnergia = 0.0;
            $npsMax = 0.0;
            foreach ($validNps as $v) {
                $sumaEnergia += pow(10.0, $v / 10.0);
                if ($v > $npsMax) $npsMax = $v;
            }

            $leq = $n > 0 ? (10.0 * log10($sumaEnergia / $n)) : 0;
            $tpe = (float) ($item->tiempo_expos_h ?: 8);
            $tmpe = $leq > 0 ? (8.0 / pow(2, ($leq - 85.0) / 3.0)) : null;
            $dosis = ($tmpe && $tmpe > 0) ? ($tpe / $tmpe) : null;
            $tpeSuperaTmpe = ($tmpe !== null && $tpe > $tmpe);

            return [
                'area' => $item->area,
                'punto_medicion' => $item->punto_medicion,
                'tipo_ruido' => $item->tipo_ruido,
                'tiempo_expos_h' => $tpe,
                'ponderacion' => $item->ponderacion ?? 'A',
                'respuesta' => $item->respuesta ?? 'Lento',
                'nps_max' => $n > 0 ? round($npsMax, 2) : null,
                'leq_t_db' => $n > 0 ? round($leq, 2) : null,
                'tmpe' => $tmpe !== null ? round($tmpe, 2) : null,
                'dosis' => $dosis !== null ? round($dosis, 4) : null,
                'tpe_supera_tmpe' => $tpeSuperaTmpe,
                'requiere_dosimetria' => $tpeSuperaTmpe,
                'observations' => $item->observations,
                'cumple' => !$tpeSuperaTmpe,
            ];
        });
        
        return view('measurements.ruido_ocupacionales.report', [
            'module' => $module,
            'installationName' => $installationName,
            'startDateFormatted' => $startDateFormatted,
            'endDateFormatted' => $endDateFormatted,
            'equipmentName' => $equipmentName,
            'equipmentBrand' => $equipmentBrand,
            'equipmentModel' => $equipmentModel,
            'equipmentSerial' => $equipmentSerial,
            'measurements' => $measurements,
            'registeredBy' => $currentUser ? $currentUser->name : 'Técnico de Campo',
        ]);
    }

    /**
     * Guarda la configuración del reporte fotográfico de Ruido Ocupacional.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $payload = $request->input('settings') ?? ($request->input('photo_report_settings') ?? $request->all());

        $settings = [
            'grid' => $payload['grid'] ?? '2x3',
            'selected_points' => $payload['selected_points'] ?? [],
            'photo_indices' => $payload['photo_indices'] ?? [],
            'image_fit' => $payload['image_fit'] ?? 'contain',
            'photo_fits' => $payload['photo_fits'] ?? [],
        ];

        $module->photo_report_settings = $settings;
        $module->save();

        return response()->json([
            'success' => true,
            'status' => 'success',
            'message' => 'Configuración de mosaico fotográfico guardada con éxito.',
            'settings' => $settings,
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

    /**
     * Descarga un reporte de Ruido Ocupacional en formato Word (.docx)
     */
    public function downloadWordReport($moduleId, $measurementId)
    {
        $measurement = NoiseMeasurement::where('module_id', $moduleId)->findOrFail($measurementId);
        $module = MeasurementModule::with('project.company', 'equipment', 'fieldStaff')->findOrFail($moduleId);

        $projectName = $module->project ? $module->project->name : 'Proyecto';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : '';
        $installationName = $module->installation_name ?: ($projectName . ($companyName ? " - {$companyName}" : ''));

        $html = $this->generateWordHTML($measurement, $module, $installationName);

        $fileName = 'Ruido_Ocupacional_' . $measurement->point_number . '_' . now()->format('YmdHis') . '.doc';

        return response($html)
            ->header('Content-Type', 'application/msword')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Genera el HTML del reporte Word
     */
    private function generateWordHTML($measurement, $module, $installationName)
    {
        $compliant = isset($measurement->cumple) && $measurement->cumple;
        $compliantText = $compliant ? '<span style="color: #059669; font-weight: bold;">✓ CUMPLE</span>' : '<span style="color: #dc2626; font-weight: bold;">✗ NO CUMPLE</span>';
        
        return '<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Reporte Ruido Ocupacional</title>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h1 { color: #0284c7; border-bottom: 2px solid #0284c7; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; }
th, td { border: 1px solid #cbd5e1; padding: 8px; text-align: left; }
th { background-color: #f0f9ff; }
</style>
</head>
<body>
<h1>Reporte de Ruido Ocupacional NTS-002/17</h1>
<table>
<tr><td><strong>Ubicación:</strong></td><td>' . htmlspecialchars($installationName) . '</td></tr>
<tr><td><strong>Fecha:</strong></td><td>' . ($measurement->measurement_date ? $measurement->measurement_date->format('d/m/Y') : 'N/A') . '</td></tr>
<tr><td><strong>Punto:</strong></td><td>' . htmlspecialchars($measurement->punto_medicion ?? 'N/A') . '</td></tr>
<tr><td><strong>Área:</strong></td><td>' . htmlspecialchars($measurement->area ?? 'N/A') . '</td></tr>
</table>
<h2>Parámetros</h2>
<table>
<tr><th>Tipo Ruido</th><th>Ponderación</th><th>Respuesta</th><th>Exposición (h)</th></tr>
<tr><td>' . htmlspecialchars($measurement->tipo_ruido ?? 'N/A') . '</td><td>' . htmlspecialchars($measurement->ponderacion ?? 'A') . '</td><td>' . htmlspecialchars($measurement->respuesta ?? 'Lenta') . '</td><td>' . ($measurement->tiempo_expos_h ? number_format($measurement->tiempo_expos_h, 2) : 'N/A') . '</td></tr>
</table>
<h2>Resultados Acústicos</h2>
<table>
<tr><th>LAeq (dB)</th><th>NPS Máximo (dB)</th><th>Mediciones</th></tr>
<tr><td><strong>' . ($measurement->leq_t_db ? number_format($measurement->leq_t_db, 1) : 'N/A') . '</strong></td><td>' . ($measurement->nps_max ? number_format($measurement->nps_max, 1) : 'N/A') . '</td><td>' . (is_array($measurement->mediciones_db) ? count($measurement->mediciones_db) : 0) . '</td></tr>
</table>
<h2>Dosis (NTS-002/17)</h2>
<table>
<tr><th>TMPE (h)</th><th>Dosis</th><th>Cumplimiento</th></tr>
<tr><td>' . ($measurement->tmpe ? number_format($measurement->tmpe, 2) : 'N/A') . '</td><td><strong>' . ($measurement->dosis ? number_format($measurement->dosis, 2) : 'N/A') . '</strong></td><td>' . $compliantText . '</td></tr>
</table>
<h2>Protección Auditiva</h2>
<table>
<tr><th>Protectores</th><th>Tipo</th><th>NRR (dB)</th></tr>
<tr><td>' . ($measurement->uso_protectores ? 'Sí' : 'No') . '</td><td>' . htmlspecialchars($measurement->tipo_protector ?? '-') . '</td><td>' . ($measurement->nrr_db ? number_format($measurement->nrr_db, 1) : '-') . '</td></tr>
</table>
<h2>Observaciones</h2>
<p>' . ($measurement->observations ? htmlspecialchars($measurement->observations) : 'Sin observaciones') . '</p>
<hr>
<p style="color: #64748b; font-size: 12px;">Generado por METRIC V2 — Norma: NTS-002/17</p>
</body>
</html>';
    }
}



