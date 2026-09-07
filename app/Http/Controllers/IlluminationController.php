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
        $measurements = $module->illuminationMeasurements()->with('staff')->get()->map(function ($item, $index) {
            $dateFormatted = $item->measurement_date ? $item->measurement_date->format('d/m/Y') : '—';
            $measuredLux = (float) $item->measured_lux;
            $requiredLux = (float) $item->required_lux;
            $isCompliant = $measuredLux >= $requiredLux;
            $lat = $item->latitude !== null ? (float) $item->latitude : null;
            $lng = $item->longitude !== null ? (float) $item->longitude : null;

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
                'registered_by' => $item->registered_by ?: ($item->staff ? $item->staff->name : 'Técnico de Campo'),
                'staff_id' => $item->staff_id,
            ];
        });

        // Contadores
        $totalMeasurements = $measurements->count();

        return view('measurements.illumination', compact(
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
            'totalMeasurements'
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:8192',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:8192',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'readings' => 'nullable',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
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
                    $fileName = 'lux_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fileName);
                    $uploadedImages[] = 'uploads/measurements/' . $fileName;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $fileName = 'lux_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $fileName);
                array_unshift($uploadedImages, 'uploads/measurements/' . $fileName);
            }
        }
        $imagePath = $uploadedImages[0] ?? null;

        // Determinar nombre del registrador si se seleccionó staff
        $registeredByName = $validated['registered_by'] ?? null;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->name;
            }
        }
        if (empty($registeredByName)) {
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:8192',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:8192',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'readings' => 'nullable',
            'observations' => 'nullable|string|max:1000',
            'staff_id' => 'nullable|exists:staff,id',
            'registered_by' => 'nullable|string|max:255',
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
                    $fileName = 'lux_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move($uploadDir, $fileName);
                    $uploadedImages[] = 'uploads/measurements/' . $fileName;
                }
            }
        }
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            if ($file->isValid()) {
                $fileName = 'lux_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move($uploadDir, $fileName);
                array_unshift($uploadedImages, 'uploads/measurements/' . $fileName);
            }
        }

        $existingImages = is_array($measurement->images) ? $measurement->images : (json_decode($measurement->images, true) ?: []);
        if (empty($existingImages) && !empty($measurement->image_path)) {
            $existingImages = [$measurement->image_path];
        }

        if (!empty($uploadedImages)) {
            $allImages = array_merge($existingImages, $uploadedImages);
            $imagePath = $uploadedImages[0] ?? $measurement->image_path;
        } else {
            $allImages = $existingImages;
            $imagePath = $measurement->image_path;
        }

        $registeredByName = $validated['registered_by'] ?? $measurement->registered_by;
        if (!empty($validated['staff_id'])) {
            $staff = Staff::find($validated['staff_id']);
            if ($staff) {
                $registeredByName = $staff->name;
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
}
