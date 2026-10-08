<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\MeasurementModule;
use App\Models\Staff;
use App\Models\VibracionMeasurement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class VibracionController extends Controller
{
    /**
     * Muestra la página principal del módulo de Vibraciones Ocupacionales.
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
            $installationName = $razonSocial ?: ($module->installation_name ?: 'PLANTA INDUSTRIAL — ÁREA DE OPERACIONES');
        } else {
            $installationName = $module->installation_name ?: $razonSocial;
        }

        // Fechas del módulo
        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Vibración Ocupacional (Cuerpo Entero y Mano-Brazo)';

        // Equipo asignado para vibración ocupacional
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Acelerómetro Triaxial con Medidor de Vibración Humana');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'SVANTEK / Larson Davis') : 'SVANTEK';
        $equipmentModel = $equipment ? ($equipment->model ?: 'SV 106A') : 'SV 106A';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: 'VB-2024-01') : 'VB-2024-01';
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
        $dbMeasurements = $module->vibracionMeasurements()->with('staff')->get();

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

            $pointNum = (!empty($item->codigo) && preg_match('/^VIB-\d+/i', $item->codigo))
                ? strtoupper($item->codigo)
                : ((!empty($item->point_number) && preg_match('/^VIB-\d+/i', $item->point_number))
                    ? strtoupper($item->point_number)
                    : ('VIB-' . ($index + 1)));

            $tipo = strtolower($item->tipo ?? 'cuerpo_entero');
            $isCE = ($tipo === 'cuerpo_entero' || str_contains($tipo, 'cuerpo'));

            $aeqx = $isCE ? (float)($item->aeqx_ce ?? 0) : (float)($item->aeqx_mb ?? 0);
            $aeqy = $isCE ? (float)($item->aeqy_ce ?? 0) : (float)($item->aeqy_mb ?? 0);
            $aeqz = $isCE ? (float)($item->aeqz_ce ?? 0) : (float)($item->aeqz_mb ?? 0);

            $atotal = $item->aceleracion_total;
            $a8 = $item->a8;
            $nivelAccion = $item->nivel_accion;
            $limiteVle = $item->limite_vle;
            $estado = $item->estado_cumplimiento;

            return [
                'id' => $item->id,
                'num' => $pointNum,
                'point_number' => $pointNum,
                'codigo' => $pointNum,
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? Carbon::parse($item->measurement_date)->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '08:00',
                'area' => $item->area ?: 'General',
                'workstation' => $item->puesto_trabajo ?: ($item->workstation ?: 'Operador'),
                'puesto_trabajo' => $item->puesto_trabajo ?: ($item->workstation ?: 'Operador'),
                'trabajador_evaluado' => $item->trabajador_evaluado ?: '—',
                'maquina_equipo' => $item->maquina_equipo ?: '—',
                'duracion_jornada_h' => (float)($item->duracion_jornada_h ?? 8.0),
                'tiempo_expos_h' => (float)($item->tiempo_expos_h ?? 8.0),
                'duracion_prueba_min' => (int)($item->duracion_prueba_min ?? 15),
                'tipo' => $isCE ? 'cuerpo_entero' : 'mano_brazo',
                'tipo_label' => $isCE ? 'Cuerpo Entero' : 'Mano - Brazo',
                'ub_acelerometro' => $item->ub_acelerometro ?: 'base_asiento',
                'mano_afectada' => $item->mano_afectada ?: 'derecha',
                'aeqx' => $aeqx,
                'aeqy' => $aeqy,
                'aeqz' => $aeqz,
                'aeqx_ce' => (float)($item->aeqx_ce ?? 0),
                'aeqy_ce' => (float)($item->aeqy_ce ?? 0),
                'aeqz_ce' => (float)($item->aeqz_ce ?? 0),
                'aeqx_mb' => (float)($item->aeqx_mb ?? 0),
                'aeqy_mb' => (float)($item->aeqy_mb ?? 0),
                'aeqz_mb' => (float)($item->aeqz_mb ?? 0),
                'atotal' => $atotal,
                'a8' => $a8,
                'nivel_accion' => $nivelAccion,
                'limite_vle' => $limiteVle,
                'estado' => $estado,
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
        $cumplenCount = $measurements->where('estado', 'CUMPLE')->count();
        $accionCount = $measurements->where('estado', 'NIVEL DE ACCIÓN')->count();
        $superaCount = $measurements->where('estado', 'SUPERA LÍMITE')->count();
        $avgA8 = $totalMeasurements > 0 ? round($measurements->avg('a8'), 3) : 0.0;

        return view('measurements.vibraciones.index', compact(
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
            'cumplenCount',
            'accionCount',
            'superaCount',
            'avgA8',
            'userName',
            'userRole'
        ));
    }

    /**
     * Guarda un nuevo punto de medición de vibración.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'measurement_date' => 'nullable|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'puesto_trabajo' => 'nullable|string|max:255',
            'workstation' => 'nullable|string|max:255',
            'trabajador_evaluado' => 'nullable|string|max:255',
            'maquina_equipo' => 'nullable|string|max:255',
            'duracion_jornada_h' => 'nullable|numeric',
            'tiempo_expos_h' => 'nullable|numeric',
            'duracion_prueba_min' => 'nullable|integer',
            'tipo' => 'nullable|string|max:50',
            'ub_acelerometro' => 'nullable|string|max:100',
            'mano_afectada' => 'nullable|string|max:50',
            'aeqx' => 'nullable|numeric',
            'aeqy' => 'nullable|numeric',
            'aeqz' => 'nullable|numeric',
            'aeqx_ce' => 'nullable|numeric',
            'aeqy_ce' => 'nullable|numeric',
            'aeqz_ce' => 'nullable|numeric',
            'aeqx_mb' => 'nullable|numeric',
            'aeqy_mb' => 'nullable|numeric',
            'aeqz_mb' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:20',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'observations' => 'nullable|string',
            'staff_id' => 'nullable|exists:staff,id',
            'images.*' => 'nullable|image|max:15360',
        ]);

        $count = $module->vibracionMeasurements()->count();
        $pointCode = 'VIB-' . ($count + 1);

        // Manejo de Fotos
        $uploadedImages = [];
        if ($request->hasFile('images')) {
            $destPath = public_path('uploads/vibraciones');
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0755, true);
            }
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'vib_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $uploadedImages[] = 'uploads/vibraciones/' . $filename;
                }
            }
        }

        $puesto = $validated['puesto_trabajo'] ?? ($validated['workstation'] ?? 'Operador');
        $tipo = $validated['tipo'] ?? 'cuerpo_entero';
        $isCE = ($tipo === 'cuerpo_entero');

        $aeqx = (float)($validated['aeqx'] ?? ($isCE ? ($validated['aeqx_ce'] ?? 0) : ($validated['aeqx_mb'] ?? 0)));
        $aeqy = (float)($validated['aeqy'] ?? ($isCE ? ($validated['aeqy_ce'] ?? 0) : ($validated['aeqy_mb'] ?? 0)));
        $aeqz = (float)($validated['aeqz'] ?? ($isCE ? ($validated['aeqz_ce'] ?? 0) : ($validated['aeqz_mb'] ?? 0)));

        $data = [
            'module_id' => $module->id,
            'point_number' => $pointCode,
            'codigo' => $pointCode,
            'measurement_date' => $validated['measurement_date'] ?? now()->toDateString(),
            'measurement_time' => $validated['measurement_time'] ?? now()->format('H:i'),
            'area' => $validated['area'] ?? 'General',
            'puesto_trabajo' => $puesto,
            'workstation' => $puesto,
            'punto_medicion' => $pointCode,
            'trabajador_evaluado' => $validated['trabajador_evaluado'] ?? null,
            'maquina_equipo' => $validated['maquina_equipo'] ?? null,
            'duracion_jornada_h' => $validated['duracion_jornada_h'] ?? 8.0,
            'tiempo_expos_h' => $validated['tiempo_expos_h'] ?? 8.0,
            'duracion_prueba_min' => $validated['duracion_prueba_min'] ?? 15,
            'tipo' => $tipo,
            'ub_acelerometro' => $validated['ub_acelerometro'] ?? 'base_asiento',
            'mano_afectada' => $validated['mano_afectada'] ?? 'derecha',
            'aeqx_ce' => $isCE ? $aeqx : ($validated['aeqx_ce'] ?? 0.0),
            'aeqy_ce' => $isCE ? $aeqy : ($validated['aeqy_ce'] ?? 0.0),
            'aeqz_ce' => $isCE ? $aeqz : ($validated['aeqz_ce'] ?? 0.0),
            'aeqx_mb' => !$isCE ? $aeqx : ($validated['aeqx_mb'] ?? 0.0),
            'aeqy_mb' => !$isCE ? $aeqy : ($validated['aeqy_mb'] ?? 0.0),
            'aeqz_mb' => !$isCE ? $aeqz : ($validated['aeqz_mb'] ?? 0.0),
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

        VibracionMeasurement::create($data);

        // Actualizar contador del módulo
        $module->points_completed = $module->vibracionMeasurements()->count();
        $module->save();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Punto {$pointCode} guardado exitosamente."]);
        }

        return redirect()->route('modules.vibracion', $module->id)->with('success', "Punto {$pointCode} registrado con éxito.");
    }

    /**
     * Actualiza un punto de medición de vibración.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->vibracionMeasurements()->findOrFail($measurementId);

        $validated = $request->validate([
            'measurement_date' => 'nullable|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'nullable|string|max:255',
            'puesto_trabajo' => 'nullable|string|max:255',
            'workstation' => 'nullable|string|max:255',
            'trabajador_evaluado' => 'nullable|string|max:255',
            'maquina_equipo' => 'nullable|string|max:255',
            'duracion_jornada_h' => 'nullable|numeric',
            'tiempo_expos_h' => 'nullable|numeric',
            'duracion_prueba_min' => 'nullable|integer',
            'tipo' => 'nullable|string|max:50',
            'ub_acelerometro' => 'nullable|string|max:100',
            'mano_afectada' => 'nullable|string|max:50',
            'aeqx' => 'nullable|numeric',
            'aeqy' => 'nullable|numeric',
            'aeqz' => 'nullable|numeric',
            'aeqx_ce' => 'nullable|numeric',
            'aeqy_ce' => 'nullable|numeric',
            'aeqz_ce' => 'nullable|numeric',
            'aeqx_mb' => 'nullable|numeric',
            'aeqy_mb' => 'nullable|numeric',
            'aeqz_mb' => 'nullable|numeric',
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
            $destPath = public_path('uploads/vibraciones');
            if (!File::exists($destPath)) {
                File::makeDirectory($destPath, 0755, true);
            }
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $filename = 'vib_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($destPath, $filename);
                    $currentImages[] = 'uploads/vibraciones/' . $filename;
                }
            }
        }

        $puesto = $validated['puesto_trabajo'] ?? ($validated['workstation'] ?? $measurement->puesto_trabajo);
        $tipo = $validated['tipo'] ?? $measurement->tipo;
        $isCE = ($tipo === 'cuerpo_entero');

        $aeqx = isset($validated['aeqx']) ? (float)$validated['aeqx'] : ($isCE ? (float)($validated['aeqx_ce'] ?? $measurement->aeqx_ce) : (float)($validated['aeqx_mb'] ?? $measurement->aeqx_mb));
        $aeqy = isset($validated['aeqy']) ? (float)$validated['aeqy'] : ($isCE ? (float)($validated['aeqy_ce'] ?? $measurement->aeqy_ce) : (float)($validated['aeqy_mb'] ?? $measurement->aeqy_mb));
        $aeqz = isset($validated['aeqz']) ? (float)$validated['aeqz'] : ($isCE ? (float)($validated['aeqz_ce'] ?? $measurement->aeqz_ce) : (float)($validated['aeqz_mb'] ?? $measurement->aeqz_mb));

        $measurement->update([
            'measurement_date' => $validated['measurement_date'] ?? $measurement->measurement_date,
            'measurement_time' => $validated['measurement_time'] ?? $measurement->measurement_time,
            'area' => $validated['area'] ?? $measurement->area,
            'puesto_trabajo' => $puesto,
            'workstation' => $puesto,
            'trabajador_evaluado' => $validated['trabajador_evaluado'] ?? $measurement->trabajador_evaluado,
            'maquina_equipo' => $validated['maquina_equipo'] ?? $measurement->maquina_equipo,
            'duracion_jornada_h' => $validated['duracion_jornada_h'] ?? $measurement->duracion_jornada_h,
            'tiempo_expos_h' => $validated['tiempo_expos_h'] ?? $measurement->tiempo_expos_h,
            'duracion_prueba_min' => $validated['duracion_prueba_min'] ?? $measurement->duracion_prueba_min,
            'tipo' => $tipo,
            'ub_acelerometro' => $validated['ub_acelerometro'] ?? $measurement->ub_acelerometro,
            'mano_afectada' => $validated['mano_afectada'] ?? $measurement->mano_afectada,
            'aeqx_ce' => $isCE ? $aeqx : ($validated['aeqx_ce'] ?? $measurement->aeqx_ce),
            'aeqy_ce' => $isCE ? $aeqy : ($validated['aeqy_ce'] ?? $measurement->aeqy_ce),
            'aeqz_ce' => $isCE ? $aeqz : ($validated['aeqz_ce'] ?? $measurement->aeqz_ce),
            'aeqx_mb' => !$isCE ? $aeqx : ($validated['aeqx_mb'] ?? $measurement->aeqx_mb),
            'aeqy_mb' => !$isCE ? $aeqy : ($validated['aeqy_mb'] ?? $measurement->aeqy_mb),
            'aeqz_mb' => !$isCE ? $aeqz : ($validated['aeqz_mb'] ?? $measurement->aeqz_mb),
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

        return redirect()->route('modules.vibracion', $module->id)->with('success', "Punto {$measurement->point_number} actualizado.");
    }

    /**
     * Elimina un punto de medición.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->vibracionMeasurements()->findOrFail($measurementId);
        $pointCode = $measurement->point_number;
        $measurement->delete();

        $module->points_completed = $module->vibracionMeasurements()->count();
        $module->save();

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Punto {$pointCode} eliminado correctamente."]);
        }

        return redirect()->route('modules.vibracion', $module->id)->with('success', "Punto {$pointCode} eliminado.");
    }

    /**
     * Actualiza el encabezado técnico del módulo.
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'installation_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'monitoring_type' => 'nullable|string|max:255',
            'equipment_id' => 'nullable|exists:equipment,id',
            'field_staff_ids' => 'nullable|array',
            'field_staff_ids.*' => 'exists:staff,id',
        ]);

        $module->update([
            'installation_name' => $validated['installation_name'] ?? $module->installation_name,
            'start_date' => $validated['start_date'] ?? $module->start_date,
            'end_date' => $validated['end_date'] ?? $module->end_date,
            'monitoring_type' => $validated['monitoring_type'] ?? $module->monitoring_type,
            'equipment_id' => $validated['equipment_id'] ?? $module->equipment_id,
            'field_staff_ids' => $validated['field_staff_ids'] ?? $module->field_staff_ids,
        ]);

        return response()->json(['success' => true, 'message' => 'Encabezado técnico actualizado correctamente.']);
    }

    /**
     * Guarda la configuración del reporte fotográfico.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $module->photo_report_settings = $validated['settings'];
        $module->save();

        return response()->json(['success' => true, 'message' => 'Configuración fotográfica guardada con éxito.']);
    }

    /**
     * Muestra la vista oficial de Informe Técnico de Vibración con 2 Pasos.
     */
    public function showReport($moduleId)
    {
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

        $installationName = $module->installation_name ?: ($razonSocial ?: 'PLANTA INDUSTRIAL');
        $dbMeasurements = $module->vibracionMeasurements()->with('staff')->get();

        $measurementsList = $dbMeasurements->map(function ($item, $index) {
            $pointCode = (!empty($item->codigo) && preg_match('/^VIB-\d+/i', $item->codigo))
                ? strtoupper($item->codigo)
                : ((!empty($item->point_number) && preg_match('/^VIB-\d+/i', $item->point_number))
                    ? strtoupper($item->point_number)
                    : ('VIB-' . ($index + 1)));

            $tipo = strtolower($item->tipo ?? 'cuerpo_entero');
            $isCE = ($tipo === 'cuerpo_entero' || str_contains($tipo, 'cuerpo'));

            $aeqx = $isCE ? (float)($item->aeqx_ce ?? 0) : (float)($item->aeqx_mb ?? 0);
            $aeqy = $isCE ? (float)($item->aeqy_ce ?? 0) : (float)($item->aeqy_mb ?? 0);
            $aeqz = $isCE ? (float)($item->aeqz_ce ?? 0) : (float)($item->aeqz_mb ?? 0);

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

            return (object)[
                'id' => $item->id,
                'point_code' => $pointCode,
                'fecha_fmt' => $item->measurement_date ? Carbon::parse($item->measurement_date)->format('d/m/Y') : '—',
                'hora_fmt' => $item->measurement_time ?: '08:00',
                'area' => $item->area ?: 'General',
                'puesto_trabajo' => $item->puesto_trabajo ?: ($item->workstation ?: 'Operador'),
                'trabajador_evaluado' => $item->trabajador_evaluado ?: '—',
                'maquina_equipo' => $item->maquina_equipo ?: '—',
                'duracion_jornada_h' => (float)($item->duracion_jornada_h ?? 8.0),
                'tiempo_expos_h' => (float)($item->tiempo_expos_h ?? 8.0),
                'duracion_prueba_min' => (int)($item->duracion_prueba_min ?? 15),
                'tipo' => $isCE ? 'cuerpo_entero' : 'mano_brazo',
                'tipo_label' => $isCE ? 'Cuerpo Entero' : 'Mano - Brazo',
                'normativa' => $isCE ? 'ISO 2631-1' : 'ISO 5349-1',
                'ub_acelerometro' => $item->ub_acelerometro ?: 'base_asiento',
                'ub_acelerometro_label' => match($item->ub_acelerometro) {
                    'base_asiento' => 'Base del asiento',
                    'espaldar_asiento' => 'Espaldar del asiento',
                    'base_pies' => 'Base de pies',
                    default => $item->ub_acelerometro ?: 'Base del asiento'
                },
                'mano_afectada' => $item->mano_afectada ?: 'derecha',
                'mano_afectada_label' => match($item->mano_afectada) {
                    'derecha' => 'Mano Derecha',
                    'izquierda' => 'Mano Izquierda',
                    default => $item->mano_afectada ?: 'Mano Derecha'
                },
                'aeqx' => $aeqx,
                'aeqy' => $aeqy,
                'aeqz' => $aeqz,
                'aeqx_ce' => (float)($item->aeqx_ce ?? 0),
                'aeqy_ce' => (float)($item->aeqy_ce ?? 0),
                'aeqz_ce' => (float)($item->aeqz_ce ?? 0),
                'aeqx_mb' => (float)($item->aeqx_mb ?? 0),
                'aeqy_mb' => (float)($item->aeqy_mb ?? 0),
                'aeqz_mb' => (float)($item->aeqz_mb ?? 0),
                'atotal' => $item->aceleracion_total,
                'a8' => $item->a8,
                'nivel_accion' => $item->nivel_accion,
                'limite_vle' => $item->limite_vle,
                'estado' => $item->estado_cumplimiento,
                'utm_zone' => $item->utm_zone ?: '19K',
                'utm_easting' => $item->utm_easting !== null ? (float)$item->utm_easting : 592450.0,
                'utm_northing' => $item->utm_northing !== null ? (float)$item->utm_northing : 8175320.0,
                'observations' => $item->observations ?: 'Sin observaciones.',
                'image_path' => $imagesUrls[0] ?? null,
            ];
        });

        // Configuración guardada previamente para el reporte
        $reportData = $module->anexo2_data ?? [];

        return view('measurements.vibraciones.report', compact(
            'module',
            'project',
            'company',
            'installationName',
            'measurementsList',
            'reportData'
        ));
    }

    /**
     * Guarda conclusiones, notas o campos adicionales del informe técnico.
     */
    public function saveReportData(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $module->anexo2_data = $request->input('report_data', []);
        $module->save();

        return response()->json(['success' => true, 'message' => 'Datos del informe guardados con éxito.']);
    }

    /**
     * Utilidad para convertir UTM a Lat/Lng.
     */
    private function utmToLatLng($easting, $northing, $zoneStr = '19K')
    {
        $zoneNumber = 19;
        $isSouth = true;
        if (preg_match('/(\d+)\s*([A-Za-z]?)/', $zoneStr, $m)) {
            $zoneNumber = (int)$m[1];
            if (!empty($m[2])) {
                $letter = strtoupper($m[2]);
                $isSouth = ($letter <= 'M');
            }
        }

        $a = 6378137.0;
        $e = 0.081819191;
        $e1sq = 0.006739497;
        $k0 = 0.9996;

        $x = (float)$easting - 500000.0;
        $y = (float)$northing;
        if ($isSouth) {
            $y -= 10000000.0;
        }

        $m_val = $y / $k0;
        $mu = $m_val / ($a * (1.0 - $e * $e / 4.0 - 3.0 * $e * $e * $e * $e / 64.0 - 5.0 * $e * $e * $e * $e * $e * $e / 256.0));

        $e1 = (1.0 - sqrt(1.0 - $e * $e)) / (1.0 + sqrt(1.0 - $e * $e));
        $j1 = 3.0 * $e1 / 2.0 - 27.0 * pow($e1, 3) / 32.0;
        $j2 = 21.0 * pow($e1, 2) / 16.0 - 55.0 * pow($e1, 4) / 32.0;
        $j3 = 151.0 * pow($e1, 3) / 96.0;
        $j4 = 1097.0 * pow($e1, 4) / 512.0;

        $fp = $mu + $j1 * sin(2.0 * $mu) + $j2 * sin(4.0 * $mu) + $j3 * sin(6.0 * $mu) + $j4 * sin(8.0 * $mu);

        $c1 = $e1sq * pow(cos($fp), 2);
        $t1 = pow(tan($fp), 2);
        $r1 = $a * (1.0 - $e * $e) / pow(1.0 - $e * $e * pow(sin($fp), 2), 1.5);
        $n1 = $a / sqrt(1.0 - $e * $e * pow(sin($fp), 2));
        $d = $x / ($n1 * $k0);

        $lat = $fp - ($n1 * tan($fp) / $r1) * ($d * $d / 2.0 - (5.0 + 3.0 * $t1 + 10.0 * $c1 - 4.0 * $c1 * $c1 - 9.0 * $e1sq) * pow($d, 4) / 24.0 + (61.0 + 90.0 * $t1 + 298.0 * $c1 + 45.0 * $t1 * $t1 - 252.0 * $e1sq - 3.0 * $c1 * $c1) * pow($d, 6) / 720.0);
        $lat = rad2deg($lat);

        $lonOrigin = ($zoneNumber - 1) * 6 - 180 + 3;
        $lon = ($d - (1.0 + 2.0 * $t1 + $c1) * pow($d, 3) / 6.0 + (5.0 - 2.0 * $c1 + 28.0 * $t1 - 3.0 * $c1 * $c1 + 8.0 * $e1sq + 24.0 * pow($t1, 2)) * pow($d, 5) / 120.0) / cos($fp);
        $lon = $lonOrigin + rad2deg($lon);

        return ['lat' => $lat, 'lng' => $lon];
    }
}
