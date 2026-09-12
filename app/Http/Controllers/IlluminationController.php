<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\IlluminationMeasurement;
use App\Models\MeasurementModule;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class IlluminationController extends Controller
{
    /**
     * Display the illumination monitoring page for a module.
     */
    public function index($moduleId)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $module = MeasurementModule::with(['project.company', 'equipment', 'fieldStaff'])->findOrFail($moduleId);

        // Resuelve información técnica del encabezado (fiel a la captura)
        $projectName = $module->project ? $module->project->name : 'Proyecto';
        $companyName = ($module->project && $module->project->company) ? $module->project->company->name : '';
        $defaultInstallation = $projectName . ($companyName ? " - {$companyName}" : '');
        $installationName = $module->installation_name ?: $defaultInstallation;

        // Las fechas son exclusivas del módulo (no se heredan del proyecto)
        $startDateRaw = $module->start_date ? $module->start_date->format('Y-m-d') : '';
        $endDateRaw = $module->end_date ? $module->end_date->format('Y-m-d') : '';
        $startDateFormatted = $module->start_date ? $module->start_date->format('d/m/Y') : '';
        $endDateFormatted = $module->end_date ? $module->end_date->format('d/m/Y') : '';

        $monitoringType = $module->monitoring_type ?: 'Seguimiento';

        // Equipo asignado
        $equipment = $module->equipment;
        $equipmentName = $equipment ? $equipment->name : ($module->calibration_equipment ?: 'Luxómetro');
        $equipmentBrand = $equipment ? ($equipment->brand ?: 'PCE') : 'PCE';
        $equipmentModel = $equipment ? ($equipment->model ?: 'PCE - 174') : 'PCE - 174';
        $equipmentSerial = $equipment ? ($equipment->serial_number ?: '150206371') : '150206371';
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
        $measurements = $module->illuminationMeasurements()->with('staff')->get()->map(function ($item, $index) use ($assignedStaff, $currentUser) {
            $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';
            $measuredLux = (float) $item->measured_lux;
            $requiredLux = (float) $item->required_lux;
            $isCompliant = $measuredLux >= $requiredLux;
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

            // Si lat/lng no están en la BD, extraer y convertir desde location
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
                    preg_match('/Z:\s*([0-9A-Za-z]+)/i', $loc, $mZ);
                    $conv = $this->utmToLatLng((float)$mE[1], (float)$mN[1], $mZ[1] ?? '20K');
                    $lat = $conv['lat'];
                    $lng = $conv['lng'];
                }
            }

            $readingsList = is_array($item->readings) ? $item->readings : (json_decode($item->readings, true) ?: []);

            $rawImages = is_array($item->images) ? $item->images : (json_decode($item->images, true) ?: []);
            if (empty($rawImages) && !empty($item->image_path)) {
                $rawImages = [$item->image_path];
            }
            $imagesUrls = array_values(array_filter(array_map(function($p) {
                return $p ? asset($p) : null;
            }, $rawImages)));

            return [
                'id' => $item->id,
                'num' => $item->point_number ?: str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'date' => $dateFormatted,
                'raw_date' => $item->measurement_date ? $item->measurement_date->format('Y-m-d') : '',
                'time' => $item->measurement_time ?: '—',
                'area' => $item->area,
                'workstation' => $item->workstation,
                'measurement_point' => $item->measurement_point,
                'activity_description' => $item->activity_description ?: 'Oficinas y talleres',
                'lighting_type' => $item->lighting_type ?: 'Artificial',
                'required_lux' => number_format($requiredLux, 0),
                'raw_required_lux' => $requiredLux,
                'measured_lux' => number_format($measuredLux, 1),
                'raw_measured_lux' => $measuredLux,
                'readings' => $readingsList,
                'readings_count' => count($readingsList),
                'is_compliant' => $isCompliant,
                'image_path' => $item->image_path ? asset($item->image_path) : ($imagesUrls[0] ?? null),
                'raw_image_path' => $item->image_path,
                'images' => $imagesUrls,
                'images_count' => count($imagesUrls),
                'location' => $item->location ?: ($lat && $lng ? "{$lat}, {$lng}" : '—'),
                'latitude' => $lat,
                'longitude' => $lng,
                'observations' => $item->observations ?: 'Sin observaciones',
                'raw_observations' => $item->observations,
                'registered_by' => (function() use ($item, $assignedStaff, $currentUser) {
                    if (!empty($item->registered_by) && !is_numeric($item->registered_by)) {
                        return $item->registered_by;
                    }
                    if ($item->staff) {
                        return $item->staff->full_name ?: $item->staff->name;
                    }
                    if (!empty($item->staff_id)) {
                        $st = \App\Models\Staff::find($item->staff_id);
                        if ($st) return $st->full_name ?: $st->name;
                    }
                    if (!empty($item->registered_by) && is_numeric($item->registered_by)) {
                        $st = \App\Models\Staff::find((int) $item->registered_by);
                        if ($st) return $st->full_name ?: $st->name;
                        $u = \App\Models\User::find((int) $item->registered_by);
                        if ($u) return $u->name;
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

        // Contadores
        $totalMeasurements = $measurements->count();

        return view('measurements.iluminaciones.index', compact(
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
            'photoReportSettings'
        ));
    }

    /**
     * Store a new illumination measurement point.
     */
    public function storeMeasurement(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'workstation' => 'required|string|max:255',
            'measurement_point' => 'required|string|max:255',
            'activity_description' => 'nullable|string|max:255',
            'lighting_type' => 'required|string|in:Natural,Artificial,Mixta',
            'required_lux' => 'required|numeric|min:0',
            'measured_lux' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'readings' => 'nullable',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
        ], [
            'images.*.uploaded' => 'Una o más imágenes superaron el límite de carga del servidor. Las imágenes se optimizarán automáticamente al seleccionarlas.',
            'image.uploaded' => 'La imagen supera el límite de carga del servidor.',
            'images.*.image' => 'Cada archivo debe ser una imagen válida (JPG, PNG, WebP, GIF).',
            'images.*.mimes' => 'Formato de imagen no admitido. Usa JPG, PNG o WebP.',
            'images.*.max' => 'La fotografía no debe superar los 30 MB.',
        ]);

        $readings = null;
        if ($request->filled('readings')) {
            $rawReadings = $request->input('readings');
            $decoded = is_array($rawReadings) ? $rawReadings : json_decode($rawReadings, true);
            if (is_array($decoded)) {
                $readings = array_slice(array_values(array_map('floatval', array_filter($decoded, 'is_numeric'))), 0, 25);
            }
        }

        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $uploadedImages[] = $this->saveAdaptiveImage($file, $uploadDir);
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                array_unshift($uploadedImages, $this->saveAdaptiveImage($file, $uploadDir));
            }
        }
        $imagePath = $uploadedImages[0] ?? null;

        // Determinar nombre del registrador si se seleccionó staff
        $registeredByName = $validated['registered_by'] ?? null;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }
        if (empty($registeredByName) || is_numeric($registeredByName)) {
            $registeredByName = Auth::user() ? Auth::user()->name : 'Técnico de Campo';
        }

        $measurementCount = $module->illuminationMeasurements()->count();
        $pointNumber = !empty($validated['point_number']) 
            ? $validated['point_number'] 
            : str_pad($measurementCount + 1, 2, '0', STR_PAD_LEFT);

        $module->illuminationMeasurements()->create([
            'point_number' => $pointNumber,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? Carbon::now()->format('H:i'),
            'area' => $validated['area'],
            'workstation' => $validated['workstation'],
            'measurement_point' => $validated['measurement_point'],
            'activity_description' => $validated['activity_description'] ?? 'Oficinas y talleres',
            'lighting_type' => $validated['lighting_type'],
            'required_lux' => $validated['required_lux'],
            'measured_lux' => $validated['measured_lux'],
            'readings' => $readings,
            'image_path' => $imagePath,
            'images' => !empty($uploadedImages) ? $uploadedImages : null,
            'location' => $validated['location'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $registeredByName,
            'staff_id' => $validated['staff_id'] ?? null,
        ]);

        // Actualizar automáticamente los puntos completados del módulo
        $module->points_completed = $module->illuminationMeasurements()->count();
        $module->save();

        return redirect()->route('modules.illumination', $moduleId)
            ->with('success', "Punto de medición #{$pointNumber} registrado exitosamente.");
    }

    /**
     * Update an existing illumination measurement point.
     */
    public function updateMeasurement(Request $request, $moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->illuminationMeasurements()->findOrFail($measurementId);

        $validated = $request->validate([
            'point_number' => 'nullable|string|max:50',
            'measurement_date' => 'required|date',
            'measurement_time' => 'nullable|string|max:20',
            'area' => 'required|string|max:255',
            'workstation' => 'required|string|max:255',
            'measurement_point' => 'required|string|max:255',
            'activity_description' => 'nullable|string|max:255',
            'lighting_type' => 'required|string|in:Natural,Artificial,Mixta',
            'required_lux' => 'required|numeric|min:0',
            'measured_lux' => 'required|numeric|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:30720',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'readings' => 'nullable',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
        ], [
            'images.*.uploaded' => 'Una o más imágenes superaron el límite de carga del servidor. Las imágenes se optimizarán automáticamente al seleccionarlas.',
            'image.uploaded' => 'La imagen supera el límite de carga del servidor.',
            'images.*.image' => 'Cada archivo debe ser una imagen válida (JPG, PNG, WebP, GIF).',
            'images.*.mimes' => 'Formato de imagen no admitido. Usa JPG, PNG o WebP.',
            'images.*.max' => 'La fotografía no debe superar los 30 MB.',
        ]);

        $readings = null;
        if ($request->has('readings')) {
            $rawReadings = $request->input('readings');
            $decoded = is_array($rawReadings) ? $rawReadings : json_decode($rawReadings, true);
            if (is_array($decoded)) {
                $readings = array_slice(array_values(array_map('floatval', array_filter($decoded, 'is_numeric'))), 0, 25);
            }
        }

        $uploadedImages = [];
        $uploadDir = public_path('uploads/measurements');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                if ($file->isValid()) {
                    $uploadedImages[] = $this->saveAdaptiveImage($file, $uploadDir);
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                array_unshift($uploadedImages, $this->saveAdaptiveImage($file, $uploadDir));
            }
        }

        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        if (empty($existingImages) && !empty($measurement->image_path)) {
            $existingImages = [$measurement->image_path];
        }

        // Si el usuario eliminó fotos en la edición, conservar solo las remaining_images especificadas
        if ($request->has('remaining_images')) {
            $rawRemaining = $request->input('remaining_images');
            $remaining = is_array($rawRemaining) ? $rawRemaining : json_decode($rawRemaining, true);
            if (is_array($remaining)) {
                // Filtrar las imágenes que se mantienen
                $existingImages = array_values(array_intersect($existingImages, $remaining));
            }
        }

        if (!empty($uploadedImages)) {
            $allImages = array_merge($existingImages, $uploadedImages);
            $imagePath = $allImages[0] ?? null;
        } else {
            $allImages = $existingImages;
            $imagePath = $allImages[0] ?? null;
        }

        $registeredByName = $validated['registered_by'] ?? $measurement->registered_by;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->full_name ?: $staff->name;
            }
        }
        if (empty($registeredByName) || is_numeric($registeredByName)) {
            if ($measurement->staff) {
                $registeredByName = $measurement->staff->full_name ?: $measurement->staff->name;
            } elseif ($module->fieldStaff) {
                $registeredByName = $module->fieldStaff->full_name ?: $module->fieldStaff->name;
            } else {
                $registeredByName = Auth::user() ? Auth::user()->name : 'Técnico de Campo';
            }
        }

        $measurement->update([
            'point_number' => $validated['point_number'] ?? $measurement->point_number,
            'measurement_date' => $validated['measurement_date'],
            'measurement_time' => $validated['measurement_time'] ?? $measurement->measurement_time,
            'area' => $validated['area'],
            'workstation' => $validated['workstation'],
            'measurement_point' => $validated['measurement_point'],
            'activity_description' => $validated['activity_description'] ?? $measurement->activity_description ?? 'Oficinas y talleres',
            'lighting_type' => $validated['lighting_type'],
            'required_lux' => $validated['required_lux'],
            'measured_lux' => $validated['measured_lux'],
            'readings' => $readings !== null ? $readings : $measurement->readings,
            'image_path' => $imagePath,
            'images' => !empty($allImages) ? $allImages : null,
            'location' => $validated['location'] ?? null,
            'latitude' => array_key_exists('latitude', $validated) ? $validated['latitude'] : $measurement->latitude,
            'longitude' => array_key_exists('longitude', $validated) ? $validated['longitude'] : $measurement->longitude,
            'observations' => $validated['observations'] ?? null,
            'registered_by' => $registeredByName,
            'staff_id' => $validated['staff_id'] ?? null,
        ]);

        return redirect()->route('modules.illumination', $moduleId)
            ->with('success', "Punto de medición #{$measurement->point_number} actualizado correctamente.");
    }

    /**
     * Delete an illumination measurement point.
     */
    public function destroyMeasurement($moduleId, $measurementId)
    {
        $module = MeasurementModule::findOrFail($moduleId);
        $measurement = $module->illuminationMeasurements()->findOrFail($measurementId);
        $pointNum = $measurement->point_number;
        $measurement->delete();

        // Actualizar puntos completados
        $module->points_completed = $module->illuminationMeasurements()->count();
        $module->save();

        return redirect()->route('modules.illumination', $moduleId)
            ->with('success', "Punto de medición #{$pointNum} eliminado exitosamente.");
    }

    /**
     * Update technical header metadata (Instalación, Fechas, Tipo de Monitoreo).
     */
    public function updateHeader(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'installation_name' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'monitoring_type' => 'nullable|string|max:255',
        ]);

        if ($request->has('installation_name')) {
            $module->installation_name = $validated['installation_name'] ?? '';
        }
        if ($request->has('start_date')) {
            $module->start_date = $validated['start_date'] ?: null;
        }
        if ($request->has('end_date')) {
            $module->end_date = $validated['end_date'] ?: null;
        }
        if ($request->has('monitoring_type')) {
            $module->monitoring_type = $validated['monitoring_type'] ?? '';
        }
        $module->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Encabezado técnico actualizado correctamente.',
                'data' => [
                    'installation_name' => $module->installation_name,
                    'start_date' => $module->start_date ? $module->start_date->format('Y-m-d') : '',
                    'end_date' => $module->end_date ? $module->end_date->format('Y-m-d') : '',
                    'monitoring_type' => $module->monitoring_type,
                ]
            ]);
        }

        return redirect()->route('modules.illumination', $moduleId)
            ->with('success', 'Datos del encabezado técnico actualizados exitosamente.');
    }

    /**
     * Save photo report settings (grid distribution, orientation, selected points, chosen photo per point).
     */
    public function savePhotoReportSettings(Request $request, $moduleId)
    {
        $module = MeasurementModule::findOrFail($moduleId);

        $validated = $request->validate([
            'grid' => 'nullable|string|in:2x3,2x4,3x3,3x4',
            'orientation' => 'nullable|string|in:landscape,portrait',
            'selected_points' => 'nullable|array',
            'photo_indices' => 'nullable|array',
        ]);

        $currentSettings = $module->photo_report_settings ?: [];

        $newSettings = [
            'grid' => $validated['grid'] ?? ($currentSettings['grid'] ?? '2x3'),
            'orientation' => $validated['orientation'] ?? ($currentSettings['orientation'] ?? 'landscape'),
            'selected_points' => array_key_exists('selected_points', $validated) ? $validated['selected_points'] : ($currentSettings['selected_points'] ?? []),
            'photo_indices' => array_key_exists('photo_indices', $validated) ? $validated['photo_indices'] : ($currentSettings['photo_indices'] ?? []),
            'updated_at' => now()->toIso8601String(),
        ];

        $module->photo_report_settings = $newSettings;
        $module->save();

        return response()->json([
            'success' => true,
            'message' => 'Configuración del reporte fotográfico guardada exitosamente.',
            'settings' => $newSettings,
        ]);
    }

    /**
     * Procesa y guarda una imagen de forma adaptativa según su peso (bytes) y dimensiones (px).
     * Si la imagen ya es ligera y tiene dimensiones estándar, se conserva intacta sin recomprimir.
     * Si es pesada o de sensor ultra-HD (48MP+), se remuestrea con bicúbico manteniendo nitidez.
     */
    protected function saveAdaptiveImage($uploadedFile, $uploadDir)
    {
        $extension = strtolower($uploadedFile->getClientOriginalExtension());
        $safeExt = ($extension === 'jpeg' || $extension === 'jpg') ? 'jpg' : $extension;
        $fileName = 'lux_' . time() . '_' . uniqid() . '.' . $safeExt;
        $destinationPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        $relativePath = 'uploads/measurements/' . $fileName;

        $sourcePath = $uploadedFile->getRealPath();
        $fileSize = $uploadedFile->getSize(); // Bytes

        // Leer información de dimensiones de la imagen
        $imgInfo = @getimagesize($sourcePath);
        if (!$imgInfo) {
            // Si no se pueden leer dimensiones (no es imagen raster válida), mover directamente
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        $srcWidth = $imgInfo[0];
        $srcHeight = $imgInfo[1];
        $imageType = $imgInfo[2];
        $maxDimension = max($srcWidth, $srcHeight);

        // NIVEL 0: ÓPTIMA / LIVIANA
        // Si pesa <= 1.2 MB y sus dimensiones no superan 1920px (Full HD),
        // no se altera ni recomprime: se mantiene 100% el archivo original sin pérdida de nitidez.
        if ($fileSize <= 1.2 * 1024 * 1024 && $maxDimension <= 1920) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        // Determinar perfil adaptativo según peso y resolución
        if ($fileSize <= 4 * 1024 * 1024 && $maxDimension <= 2800) {
            // NIVEL 1: PESO MEDIO (1.2MB - 4MB o hasta 2.8K)
            // Conserva altísima resolución (hasta 2560px QHD) y calidad 92 (visualmente idéntica)
            $targetMaxDim = 2560;
            $quality = 92;
        } elseif ($fileSize <= 9 * 1024 * 1024 && $maxDimension <= 4500) {
            // NIVEL 2: PESADA (4MB - 9MB o hasta 4.5K)
            // Escala a 2200px con calidad 90 manteniendo dígitos y etiquetas nítidas
            $targetMaxDim = 2200;
            $quality = 90;
        } else {
            // NIVEL 3: ULTRA PESADA / SENSOR MÓVIL RAW (48MP+, 6000x8000px, > 9MB)
            // Escala a 2048px con calidad 88 y remuestreo bicúbico
            $targetMaxDim = 2048;
            $quality = 88;
        }

        // Calcular nuevas dimensiones respetando la relación de aspecto
        if ($maxDimension > $targetMaxDim) {
            if ($srcWidth >= $srcHeight) {
                $targetWidth = $targetMaxDim;
                $targetHeight = (int) round(($srcHeight * $targetMaxDim) / $srcWidth);
            } else {
                $targetHeight = $targetMaxDim;
                $targetWidth = (int) round(($srcWidth * $targetMaxDim) / $srcHeight);
            }
        } else {
            $targetWidth = $srcWidth;
            $targetHeight = $srcHeight;
        }

        // Cargar imagen según formato
        $srcImage = null;
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $srcImage = @imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $srcImage = @imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $srcImage = @imagecreatefromwebp($sourcePath);
                }
                break;
        }

        if (!$srcImage) {
            $uploadedFile->move($uploadDir, $fileName);
            return $relativePath;
        }

        // Crear lienzo de alta fidelidad
        $dstImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Si es PNG con canal alfa, conservar transparencia o fondo blanco limpio
        if ($imageType === IMAGETYPE_PNG) {
            imagealphablending($dstImage, false);
            imagesavealpha($dstImage, true);
            $transparent = imagecolorallocatealpha($dstImage, 255, 255, 255, 127);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $transparent);
        } else {
            // Fondo blanco para evitar fondos negros en bordes
            $white = imagecolorallocate($dstImage, 255, 255, 255);
            imagefilledrectangle($dstImage, 0, 0, $targetWidth, $targetHeight, $white);
        }

        // Remuestreo bicúbico de alta calidad
        imagecopyresampled($dstImage, $srcImage, 0, 0, 0, 0, $targetWidth, $targetHeight, $srcWidth, $srcHeight);

        // Guardar como JPG de alta calidad para máxima compatibilidad en PDF y web
        $outputFileName = 'lux_' . time() . '_' . uniqid() . '.jpg';
        $outputDestPath = $uploadDir . DIRECTORY_SEPARATOR . $outputFileName;
        
        $saved = imagejpeg($dstImage, $outputDestPath, $quality);

        imagedestroy($srcImage);
        imagedestroy($dstImage);

        if ($saved && file_exists($outputDestPath)) {
            return 'uploads/measurements/' . $outputFileName;
        }

        // Fallback: mover archivo original
        $uploadedFile->move($uploadDir, $fileName);
        return $relativePath;
    }

    /**
     * Convierte coordenadas UTM a Latitud/Longitud WGS84.
     */
    private function utmToLatLng($utmEasting, $utmNorthing, $utmZoneStr = '20K')
    {
        $utmZoneNum = 20;
        $utmZoneLetter = 'K';
        if (is_string($utmZoneStr) && preg_match('/(\d+)\s*([A-Za-z]?)/', $utmZoneStr, $utmM)) {
            $utmZoneNum = (int) $utmM[1] ?: 20;
            $utmZoneLetter = strtoupper($utmM[2] ?? 'K');
        } elseif (is_numeric($utmZoneStr)) {
            $utmZoneNum = (int) $utmZoneStr;
        }

        $utmA = 6378137.0;
        $utmF = 1 / 298.257223563;
        $utmB = $utmA * (1 - $utmF);
        $utmE = sqrt(($utmA * $utmA - $utmB * $utmB) / ($utmA * $utmA));
        $utmEPrime = sqrt(($utmA * $utmA - $utmB * $utmB) / ($utmB * $utmB));
        $utmK0 = 0.9996;

        $utmIsSouth = $utmZoneLetter !== '' ? ($utmZoneLetter < 'N') : true;
        $utmX = (float)$utmEasting - 500000.0;
        $utmY = $utmIsSouth ? (float)$utmNorthing - 10000000.0 : (float)$utmNorthing;

        $utmMarc = $utmY / $utmK0;
        $utmE2 = $utmE * $utmE;
        $utmE4 = $utmE2 * $utmE2;
        $utmE6 = $utmE4 * $utmE2;
        $utmE1 = (1 - sqrt(1 - $utmE2)) / (1 + sqrt(1 - $utmE2));

        $utmMu = $utmMarc / ($utmA * (1 - $utmE2 / 4 - 3 * $utmE4 / 64 - 5 * $utmE6 / 256));

        $utmPhi1 = $utmMu +
            (3 * $utmE1 / 2 - 27 * pow($utmE1, 3) / 32) * sin(2 * $utmMu) +
            (21 * $utmE1 * $utmE1 / 16 - 55 * pow($utmE1, 4) / 32) * sin(4 * $utmMu) +
            (151 * pow($utmE1, 3) / 96) * sin(6 * $utmMu) +
            (1097 * pow($utmE1, 4) / 512) * sin(8 * $utmMu);

        $utmSinPhi1 = sin($utmPhi1);
        $utmCosPhi1 = cos($utmPhi1);
        $utmTanPhi1 = tan($utmPhi1);

        $utmN1 = $utmA / sqrt(1 - $utmE2 * $utmSinPhi1 * $utmSinPhi1);
        $utmT1 = $utmTanPhi1 * $utmTanPhi1;
        $utmC1 = $utmEPrime * $utmEPrime * $utmCosPhi1 * $utmCosPhi1;
        $utmR1 = $utmA * (1 - $utmE2) / pow(1 - $utmE2 * $utmSinPhi1 * $utmSinPhi1, 1.5);
        $utmD = $utmX / ($utmN1 * $utmK0);

        $utmD2 = $utmD * $utmD;
        $utmD3 = $utmD2 * $utmD;
        $utmD4 = $utmD2 * $utmD2;
        $utmD5 = $utmD4 * $utmD;
        $utmD6 = $utmD3 * $utmD3;

        $utmLat = $utmPhi1 - ($utmN1 * $utmTanPhi1 / $utmR1) * (
            $utmD2 / 2 -
            (5 + 3 * $utmT1 + 10 * $utmC1 - 4 * $utmC1 * $utmC1 - 9 * $utmEPrime * $utmEPrime) * $utmD4 / 24 +
            (61 + 90 * $utmT1 + 298 * $utmC1 + 45 * $utmT1 * $utmT1 - 252 * $utmEPrime * $utmEPrime - 3 * $utmC1 * $utmC1) * $utmD6 / 720
        );

        $utmLon0 = ($utmZoneNum - 1) * 6 - 180 + 3;
        $utmLon = ($utmLon0 * M_PI / 180.0) + (
            $utmD -
            (1 + 2 * $utmT1 + $utmC1) * $utmD3 / 6 +
            (5 - 2 * $utmC1 + 28 * $utmT1 - 3 * $utmC1 * $utmC1 + 8 * $utmEPrime * $utmEPrime + 24 * $utmT1 * $utmT1) * $utmD5 / 120
        ) / $utmCosPhi1;

        return [
            'lat' => $utmLat * 180.0 / M_PI,
            'lng' => $utmLon * 180.0 / M_PI,
        ];
    }
}
