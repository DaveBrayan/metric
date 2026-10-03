<?php

namespace App\Http\Controllers;

use App\Models\MeasurementModule;
use App\Models\PhotographicInspection;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PhotographicInspectionController extends Controller
{
    /**
     * Muestra la vista principal del módulo de Inspección Fotográfica.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'fieldStaff'])->findOrFail($moduleId);

        // Resuelve información técnica del encabezado
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

        // Fechas exclusivas del módulo
        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Inspección en Campo';

        // Personal asignado para selector y encabezado técnico
        $assignedStaff = $module->getAssignedStaffAttribute();
        if ($assignedStaff && $assignedStaff->isNotEmpty()) {
            $registeredByHeader = $assignedStaff->pluck('name')->implode(', ');
            $staffList = $assignedStaff;
        } else {
            $registeredByHeader = $currentUser ? $currentUser->name : 'Técnico de Campo';
            $staffList = Staff::orderBy('name')->get();
        }

        // Puntos de inspección fotográfica registrados
        $measurements = $module->photographicInspections()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->inspection_date ? $item->inspection_date->format('d/m/Y') : '—';
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

            // Si lat/lng no están en columnas pero hay texto en location
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
                    $conv = $this->utmToLatLng((float)$mE[1], (float)$mN[1], '20K');
                    $lat = $conv['lat'];
                    $lng = $conv['lng'];
                } elseif (preg_match('/(-?[0-9]+\.[0-9]+)\s*,\s*(-?[0-9]+\.[0-9]+)/', $loc, $matches)) {
                    $lat = (float) $matches[1];
                    $lng = (float) $matches[2];
                }
            }

            // Normalización de imágenes (hasta 3 fotos)
            $images = [];
            if (!empty($item->images) && is_array($item->images)) {
                $images = array_values(array_filter($item->images));
            } elseif (!empty($item->image_path)) {
                $images = [$item->image_path];
            }

            $primaryStaff = $assignedStaff->first();
            $registeredByName = $item->registered_by
                ?: ($item->staff ? $item->staff->name : ($primaryStaff ? $primaryStaff->name : ($currentUser ? $currentUser->name : 'Reynaldo')));

            return [
                'id' => $item->id,
                'point_number' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'point_name' => 'Punto ' . ($item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT)),
                'date' => $item->inspection_date ? $item->inspection_date->format('Y-m-d') : '',
                'date_formatted' => $dateFormatted,
                'time' => $item->inspection_time ?: '',
                'area' => $item->area ?: 'Área General',
                'observation' => $item->observation ?: '',
                'description' => $item->description ?: '',
                'location' => $item->location ?: '',
                'latitude' => $lat,
                'longitude' => $lng,
                'utm_zone' => $item->utm_zone ?: '20K',
                'utm_easting' => $item->utm_easting ? (float)$item->utm_easting : null,
                'utm_northing' => $item->utm_northing ? (float)$item->utm_northing : null,
                'gps_accuracy' => $item->gps_accuracy ? (float)$item->gps_accuracy : null,
                'images' => $images,
                'image_path' => !empty($images) ? $images[0] : null,
                'registered_by' => $registeredByName,
                'staff_id' => $item->staff_id,
            ];
        });

        // Configuración de reporte fotográfico guardada
        $photoReportSettings = $module->photo_report_settings ?: [];

        // Conteo y resumen estadístico
        $totalPoints = $measurements->count();
        $totalPhotos = $measurements->sum(fn($m) => count($m['images']));
        $pointsWithGps = $measurements->filter(fn($m) => $m['latitude'] !== null && $m['longitude'] !== null)->count();

        return view('measurements.inspeccion_fotografica.index', compact(
            'module',
            'installationName',
            'startDateRaw',
            'endDateRaw',
            'startDateFormatted',
            'endDateFormatted',
            'monitoringType',
            'registeredByHeader',
            'staffList',
            'measurements',
            'photoReportSettings',
            'totalPoints',
            'totalPhotos',
            'pointsWithGps',
            'userName',
            'userRole'
        ));
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
            'monitoring_type' => 'nullable|string|max:100',
        ]);

        $module->update([
            'installation_name' => $validated['installation_name'] ?? $module->installation_name,
            'start_date' => $validated['start_date'] ?? $module->start_date,
            'end_date' => $validated['end_date'] ?? $module->end_date,
            'monitoring_type' => $validated['monitoring_type'] ?? $module->monitoring_type,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Encabezado técnico actualizado correctamente.',
            'data' => [
                'installation_name' => $module->installation_name,
                'start_date_formatted' => $module->start_date ? $module->start_date->format('d/m/Y') : '—',
                'end_date_formatted' => $module->end_date ? $module->end_date->format('d/m/Y') : '—',
                'monitoring_type' => $module->monitoring_type,
            ]
        ]);
    }

    /**
     * Guarda o edita un punto de inspección fotográfica desde la web.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:20',
            'inspection_date' => 'required|date',
            'inspection_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'observation' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:10',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'gps_accuracy' => 'nullable|numeric',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'nullable|image|max:15360', // 15MB max per image
        ]);

        // Procesar imágenes
        $images = [];
        if ($request->hasFile('photos')) {
            $uploadDir = public_path('uploads/inspecciones_fotograficas');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            foreach ($request->file('photos') as $idx => $photo) {
                if ($photo && $photo->isValid()) {
                    $filename = 'insp_' . $moduleId . '_' . time() . '_' . Str::random(6) . '.' . $photo->getClientOriginalExtension();
                    $photo->move($uploadDir, $filename);
                    $images[] = '/uploads/inspecciones_fotograficas/' . $filename;
                }
            }
        }

        // Generar número de punto consecutivo si no vino
        $pointNumber = $validated['point_number'] ?? null;
        if (empty($pointNumber)) {
            $count = $module->photographicInspections()->count();
            $pointNumber = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
        }

        $inspection = PhotographicInspection::create([
            'module_id' => $module->id,
            'point_number' => $pointNumber,
            'inspection_date' => $validated['inspection_date'],
            'inspection_time' => $validated['inspection_time'] ?? date('H:i'),
            'area' => $validated['area'],
            'observation' => $validated['observation'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'utm_zone' => $validated['utm_zone'] ?? '20K',
            'utm_easting' => $validated['utm_easting'] ?? null,
            'utm_northing' => $validated['utm_northing'] ?? null,
            'gps_accuracy' => $validated['gps_accuracy'] ?? null,
            'images' => $images,
            'image_path' => !empty($images) ? $images[0] : null,
            'registered_by' => $validated['registered_by'] ?? (Auth::user() ? Auth::user()->name : null),
            'staff_id' => $validated['staff_id'] ?? null,
        ]);

        // Actualizar progreso del módulo
        $this->updateModuleProgress($module);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punto de inspección registrado exitosamente.',
                'inspection' => $inspection,
            ]);
        }

        return redirect()->route('modules.photographic_inspection', $module->id)
            ->with('success', "Punto #{$inspection->point_number} registrado exitosamente.");
    }

    /**
     * Actualiza un punto de inspección existente.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $inspection = PhotographicInspection::where('module_id', $module->id)->findOrFail($measurementId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:20',
            'inspection_date' => 'required|date',
            'inspection_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'observation' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'utm_zone' => 'nullable|string|max:10',
            'utm_easting' => 'nullable|numeric',
            'utm_northing' => 'nullable|numeric',
            'gps_accuracy' => 'nullable|numeric',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
            'photos' => 'nullable|array|max:3',
            'photos.*' => 'nullable|image|max:15360',
            'existing_images' => 'nullable|array',
        ]);

        // Mantener imágenes existentes elegidas
        $images = $validated['existing_images'] ?? ($inspection->images ?: []);
        if (!is_array($images)) {
            $images = [];
        }

        // Subir nuevas imágenes (respetando tope de 3)
        if ($request->hasFile('photos')) {
            $uploadDir = public_path('uploads/inspecciones_fotograficas');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            foreach ($request->file('photos') as $photo) {
                if (count($images) >= 3) break;
                if ($photo && $photo->isValid()) {
                    $filename = 'insp_' . $moduleId . '_' . time() . '_' . Str::random(6) . '.' . $photo->getClientOriginalExtension();
                    $photo->move($uploadDir, $filename);
                    $images[] = '/uploads/inspecciones_fotograficas/' . $filename;
                }
            }
        }

        $loc = $validated['location'] ?? $inspection->location;
        if (empty($loc) && !empty($validated['utm_easting']) && !empty($validated['utm_northing'])) {
            $loc = 'E: ' . round($validated['utm_easting']) . ', N: ' . round($validated['utm_northing']) . ', Z: ' . ($validated['utm_zone'] ?? '20K');
        }

        $registeredBy = $validated['registered_by'] ?? $inspection->registered_by;
        if (!empty($validated['staff_id'])) {
            $st = Staff::find($validated['staff_id']);
            if ($st) {
                $registeredBy = $st->name;
            }
        }

        $inspection->update([
            'point_number' => $validated['point_number'] ?? $inspection->point_number,
            'inspection_date' => $validated['inspection_date'],
            'inspection_time' => $validated['inspection_time'] ?? $inspection->inspection_time,
            'area' => $validated['area'],
            'observation' => $validated['observation'],
            'description' => $validated['description'] ?? null,
            'location' => $loc,
            'latitude' => $validated['latitude'] ?? $inspection->latitude,
            'longitude' => $validated['longitude'] ?? $inspection->longitude,
            'utm_zone' => $validated['utm_zone'] ?? $inspection->utm_zone,
            'utm_easting' => $validated['utm_easting'] ?? $inspection->utm_easting,
            'utm_northing' => $validated['utm_northing'] ?? $inspection->utm_northing,
            'gps_accuracy' => $validated['gps_accuracy'] ?? $inspection->gps_accuracy,
            'images' => $images,
            'image_path' => !empty($images) ? $images[0] : null,
            'registered_by' => $registeredBy,
            'staff_id' => $validated['staff_id'] ?? $inspection->staff_id,
        ]);

        $this->updateModuleProgress($module);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Punto de inspección actualizado exitosamente.',
                'inspection' => $inspection,
            ]);
        }

        return redirect()->route('modules.photographic_inspection', $module->id)
            ->with('success', "Punto #{$inspection->point_number} actualizado exitosamente.");
    }

    /**
     * Elimina un punto de inspección.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $inspection = PhotographicInspection::where('module_id', $module->id)->findOrFail($measurementId);
        $pointNum = $inspection->point_number;

        $inspection->delete();

        $this->updateModuleProgress($module);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Punto #{$pointNum} eliminado correctamente.",
            ]);
        }

        return redirect()->route('modules.photographic_inspection', $module->id)
            ->with('success', "Punto #{$pointNum} eliminado correctamente.");
    }

    /**
     * Muestra el informe técnico de Inspección Fotográfica.
     */
    public function showReport($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'fieldStaff'])->findOrFail($moduleId);
        $project = $module->project;
        $company = $project ? $project->company : null;

        $inspections = $module->photographicInspections()->with('staff')->get();
        $assignedStaff = $module->getAssignedStaffAttribute();

        return view('measurements.inspeccion_fotografica.report', compact(
            'module',
            'project',
            'company',
            'inspections',
            'assignedStaff',
            'userName',
            'userRole'
        ));
    }

    /**
     * Guarda la configuración de reporte fotográfico.
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'settings' => 'required|array',
        ]);

        $module->photo_report_settings = $validated['settings'];
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración de reporte fotográfico guardada.',
        ]);
    }

    /**
     * Guarda datos adicionales del informe técnico.
     */
    public function saveReportData(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $data = $request->all();
        $existing = $module->anexo2_data ?: [];
        $merged = array_merge($existing, $data);
        $module->anexo2_data = $merged;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Datos del informe guardados exitosamente.',
        ]);
    }

    /**
     * Recalcula el progreso y puntos completados del módulo.
     */
    private function updateModuleProgress(MeasurementModule $module): void
    {
        $completed = $module->photographicInspections()->count();
        $module->points_completed = $completed;
        if ($module->points_total < $completed) {
            $module->points_total = $completed;
        }
        $module->status = ($module->points_total > 0 && $completed >= $module->points_total) ? 'Completado' : 'En Progreso';
        $module->status_theme = ($module->status === 'Completado') ? 'done' : 'in_progress';
        $module->save();
    }

    /**
     * Conversor simple de UTM a Latitud / Longitud (WGS84).
     */
    private function utmToLatLng(float $easting, float $northing, string $utmZone): array
    {
        $zoneNumber = (int) preg_replace('/[^0-9]/', '', $utmZone);
        if ($zoneNumber < 1 || $zoneNumber > 60) {
            $zoneNumber = 20; // Zona estándar Bolivia
        }
        $isSouthernHemisphere = !str_ends_with(strtoupper($utmZone), 'N');

        $a = 6378137.0; // WGS84
        $f = 1 / 298.257223563;
        $k0 = 0.9996;
        $e = sqrt(2 * $f - $f * $f);
        $ePrimeSq = ($e * $e) / (1 - $e * $e);

        $x = $easting - 500000.0;
        $y = $northing;
        if ($isSouthernHemisphere && $y > 5000000) {
            $y -= 10000000.0;
        }

        $m = $y / $k0;
        $mu = $m / ($a * (1 - $e * $e / 4 - 3 * $e * $e * $e * $e / 64 - 5 * pow($e, 6) / 256));

        $e1 = (1 - sqrt(1 - $e * $e)) / (1 + sqrt(1 - $e * $e));
        $phi1 = $mu + (3 * $e1 / 2 - 27 * pow($e1, 3) / 32) * sin(2 * $mu)
            + (21 * $e1 * $e1 / 16 - 55 * pow($e1, 4) / 32) * sin(4 * $mu)
            + (151 * pow($e1, 3) / 96) * sin(6 * $mu);

        $n1 = $a / sqrt(1 - $e * $e * sin($phi1) * sin($phi1));
        $t1 = tan($phi1) * tan($phi1);
        $c1 = $ePrimeSq * cos($phi1) * cos($phi1);
        $r1 = $a * (1 - $e * $e) / pow(1 - $e * $e * sin($phi1) * sin($phi1), 1.5);
        $d = $x / ($n1 * $k0);

        $lat = $phi1 - ($n1 * tan($phi1) / $r1) * ($d * $d / 2 - (5 + 3 * $t1 + 10 * $c1 - 4 * $c1 * $c1 - 9 * $ePrimeSq) * pow($d, 4) / 24
            + (61 + 90 * $t1 + 298 * $c1 + 45 * $t1 * $t1 - 252 * $ePrimeSq - 3 * $c1 * $c1) * pow($d, 6) / 720);

        $lon = ($d - (1 + 2 * $t1 + $c1) * pow($d, 3) / 6
            + (5 - 2 * $c1 + 28 * $t1 - 3 * $c1 * $c1 + 8 * $ePrimeSq + 24 * $t1 * $t1) * pow($d, 5) / 120) / cos($phi1);

        $lonOrigin = ($zoneNumber - 1) * 6 - 180 + 3;

        return [
            'lat' => rad2deg($lat),
            'lng' => rad2deg($lon) + $lonOrigin,
        ];
    }
}
