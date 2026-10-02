<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentCalibration;
use App\Services\SystemSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class EquipmentController extends Controller
{
    /**
     * Display a listing of equipment.
     */
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $search = $request->input('search');
        $statusFilter = $request->input('status');

        $query = Equipment::with(['calibrations']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('recalibration_observation', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $systemTimezone = SystemSetting::getTimezone();
        $today = Carbon::today($systemTimezone);

        $equipments = $query->orderBy('name', 'asc')->get()->map(function ($item, $index) use ($today) {
            $statusType = match (strtolower($item->status)) {
                'operativo' => 'done',
                'en calibración', 'calibración' => 'in_progress',
                'en mantenimiento', 'mantenimiento' => 'alert',
                default => 'pending',
            };

            // Cálculo y formateo de calibración
            $calibrationDateRaw = $item->calibration_date ? Carbon::parse($item->calibration_date)->format('Y-m-d') : null;
            $calibrationDateFormatted = $item->calibration_date ? Carbon::parse($item->calibration_date)->format('d/m/Y') : null;
            
            // Próxima fecha de recalibración (calculada a 365 días)
            $nextRecalibrationDateRaw = null;
            $nextRecalibrationDateFormatted = null;
            $calibrationStatus = 'sin_calibrar';
            $calibrationStatusText = 'Sin calibrar';
            $calibrationBadgeClass = 'calib-secondary';
            $daysRemaining = null;

            if ($item->calibration_date) {
                $nextDate = $item->next_recalibration_date 
                    ? Carbon::parse($item->next_recalibration_date) 
                    : Carbon::parse($item->calibration_date)->addDays(365);
                
                $nextRecalibrationDateRaw = $nextDate->format('Y-m-d');
                $nextRecalibrationDateFormatted = $nextDate->format('d/m/Y');

                $daysRemaining = (int) $today->diffInDays($nextDate, false);

                if ($daysRemaining < 0) {
                    $calibrationStatus = 'vencido';
                    $calibrationStatusText = 'Vencido';
                    $calibrationBadgeClass = 'calib-danger';
                } elseif ($daysRemaining <= 30) {
                    $calibrationStatus = 'por_vencer';
                    $calibrationStatusText = 'Por vencer';
                    $calibrationBadgeClass = 'calib-warning';
                } else {
                    $calibrationStatus = 'vigente';
                    $calibrationStatusText = 'Vigente';
                    $calibrationBadgeClass = 'calib-success';
                }
            }

            return [
                'id' => $item->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $item->name,
                'model' => $item->model ?? '—',
                'serial_number' => $item->serial_number ?? 'S/N',
                'description' => $item->description ?? 'Sin descripción adicional',
                'raw_description' => $item->description ?? '',
                'calibration_date' => $calibrationDateRaw,
                'calibration_date_formatted' => $calibrationDateFormatted,
                'next_recalibration_date' => $nextRecalibrationDateRaw,
                'next_recalibration_date_formatted' => $nextRecalibrationDateFormatted,
                'recalibration_observation' => $item->recalibration_observation ?? '',
                'calibration_status' => $calibrationStatus,
                'calibration_status_text' => $calibrationStatusText,
                'calibration_badge_class' => $calibrationBadgeClass,
                'days_remaining' => $daysRemaining,
                'calibrations_count' => $item->calibrations->count(),
                'image' => $item->image,
                'image_url' => $item->image ? asset($item->image) : null,
                'status' => $item->status ?? 'Operativo',
                'status_type' => $statusType,
                'initial' => strtoupper(substr($item->name, 0, 1)),
            ];
        });

        // Compute metrics
        $totalCount = Equipment::count();
        $operativosCount = Equipment::where('status', 'Operativo')->count();
        $mantenimientoCount = Equipment::whereIn('status', ['En Calibración', 'En Mantenimiento'])->count();
        $conImagenCount = Equipment::whereNotNull('image')->where('image', '!=', '')->count();

        $isAdmin = in_array(strtolower($userRole), ['superadministrador', 'administrador', 'admin']);
        $canEdit = $isAdmin;

        return view('equipment.index', compact(
            'userName',
            'userRole',
            'equipments',
            'totalCount',
            'operativosCount',
            'mantenimientoCount',
            'conImagenCount',
            'search',
            'statusFilter',
            'canEdit'
        ));
    }

    /**
     * Store a newly created equipment in storage.
     */
    public function store(Request $request)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';
        if (!in_array(strtolower($userRole), ['superadministrador', 'administrador', 'admin'])) {
            return redirect()->route('equipment.index')->with('error', 'Acceso denegado: Solo el Administrador puede registrar equipos en el inventario general.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'calibration_date' => 'nullable|date',
            'recalibration_observation' => 'nullable|string|max:2000',
            'status' => 'required|string|in:Operativo,En Calibración,En Mantenimiento,Fuera de Servicio',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $uploadDir = public_path('uploads/equipment');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $fileName = 'eq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $imagePath = 'uploads/equipment/' . $fileName;
        }

        $calibrationDate = !empty($validated['calibration_date']) ? $validated['calibration_date'] : null;
        $nextRecalibrationDate = null;
        if ($calibrationDate) {
            $nextRecalibrationDate = Carbon::parse($calibrationDate)->addDays(365)->format('Y-m-d');
        }

        $equipment = Equipment::create([
            'name' => $validated['name'],
            'model' => !empty($validated['model']) ? $validated['model'] : null,
            'serial_number' => !empty($validated['serial_number']) ? $validated['serial_number'] : null,
            'description' => !empty($validated['description']) ? $validated['description'] : null,
            'calibration_date' => $calibrationDate,
            'next_recalibration_date' => $nextRecalibrationDate,
            'recalibration_observation' => !empty($validated['recalibration_observation']) ? $validated['recalibration_observation'] : null,
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

        // Si se especificó fecha de calibración, crear registro inicial en el historial
        if ($calibrationDate) {
            EquipmentCalibration::create([
                'equipment_id' => $equipment->id,
                'calibration_date' => $calibrationDate,
                'next_recalibration_date' => $nextRecalibrationDate,
                'observation' => !empty($validated['recalibration_observation']) ? $validated['recalibration_observation'] : 'Calibración inicial registrada al crear equipo.',
                'performed_by' => $currentUser ? $currentUser->name : 'Administrador',
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Equipo registrado correctamente.']);
        }

        return redirect()->route('equipment.index')->with('success', 'Equipo registrado exitosamente.');
    }

    /**
     * Update the specified equipment in storage.
     */
    public function update(Request $request, $id)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';
        if (!in_array(strtolower($userRole), ['superadministrador', 'administrador', 'admin'])) {
            return redirect()->route('equipment.index')->with('error', 'Acceso denegado: Solo el Administrador puede modificar equipos del inventario general.');
        }

        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'calibration_date' => 'nullable|date',
            'recalibration_observation' => 'nullable|string|max:2000',
            'status' => 'required|string|in:Operativo,En Calibración,En Mantenimiento,Fuera de Servicio',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'remove_image' => 'nullable|boolean',
        ]);

        $imagePath = $equipment->image;

        // Si se solicitó eliminar la imagen actual
        if ($request->boolean('remove_image')) {
            if ($equipment->image && File::exists(public_path($equipment->image))) {
                File::delete(public_path($equipment->image));
            }
            $imagePath = null;
        }

        // Si se subió una nueva imagen
        if ($request->hasFile('image')) {
            // Eliminar imagen previa si existía
            if ($equipment->image && File::exists(public_path($equipment->image))) {
                File::delete(public_path($equipment->image));
            }

            $file = $request->file('image');
            $uploadDir = public_path('uploads/equipment');
            if (!File::isDirectory($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $fileName = 'eq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $fileName);
            $imagePath = 'uploads/equipment/' . $fileName;
        }

        $newCalibrationDate = !empty($validated['calibration_date']) ? $validated['calibration_date'] : null;
        $prevCalibrationDate = $equipment->calibration_date ? Carbon::parse($equipment->calibration_date)->format('Y-m-d') : null;

        $nextRecalibrationDate = null;
        if ($newCalibrationDate) {
            $nextRecalibrationDate = Carbon::parse($newCalibrationDate)->addDays(365)->format('Y-m-d');
        }

        // Si la fecha cambió o se agregó por primera vez, registrar entrada en el historial
        if ($newCalibrationDate && $newCalibrationDate !== $prevCalibrationDate) {
            EquipmentCalibration::create([
                'equipment_id' => $equipment->id,
                'calibration_date' => $newCalibrationDate,
                'next_recalibration_date' => $nextRecalibrationDate,
                'observation' => !empty($validated['recalibration_observation']) ? $validated['recalibration_observation'] : 'Actualización de fecha de calibración.',
                'performed_by' => $currentUser ? $currentUser->name : 'Administrador',
            ]);
        }

        $equipment->update([
            'name' => $validated['name'],
            'model' => !empty($validated['model']) ? $validated['model'] : null,
            'serial_number' => !empty($validated['serial_number']) ? $validated['serial_number'] : null,
            'description' => !empty($validated['description']) ? $validated['description'] : null,
            'calibration_date' => $newCalibrationDate,
            'next_recalibration_date' => $nextRecalibrationDate,
            'recalibration_observation' => !empty($validated['recalibration_observation']) ? $validated['recalibration_observation'] : null,
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Equipo actualizado correctamente.']);
        }

        return redirect()->route('equipment.index')->with('success', 'Equipo actualizado exitosamente.');
    }

    /**
     * Store a new calibration log in history and update the equipment.
     */
    public function storeCalibration(Request $request, $id)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';
        if (!in_array(strtolower($userRole), ['superadministrador', 'administrador', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado: Solo el Administrador puede registrar calibraciones.'], 403);
        }

        $equipment = Equipment::findOrFail($id);

        $validated = $request->validate([
            'calibration_date' => 'required|date',
            'observation' => 'nullable|string|max:2000',
            'performed_by' => 'nullable|string|max:255',
        ]);

        $calibrationDate = $validated['calibration_date'];
        $nextRecalibrationDate = Carbon::parse($calibrationDate)->addDays(365)->format('Y-m-d');
        $performedBy = !empty($validated['performed_by']) ? $validated['performed_by'] : ($currentUser ? $currentUser->name : 'Administrador');

        $calibration = EquipmentCalibration::create([
            'equipment_id' => $equipment->id,
            'calibration_date' => $calibrationDate,
            'next_recalibration_date' => $nextRecalibrationDate,
            'observation' => !empty($validated['observation']) ? $validated['observation'] : null,
            'performed_by' => $performedBy,
        ]);

        // Actualizar datos de calibración más reciente en el equipo
        $equipment->update([
            'calibration_date' => $calibrationDate,
            'next_recalibration_date' => $nextRecalibrationDate,
            'recalibration_observation' => !empty($validated['observation']) ? $validated['observation'] : $equipment->recalibration_observation,
        ]);

        $systemTimezone = SystemSetting::getTimezone();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Recalibración guardada exitosamente en el historial.',
                'calibration' => [
                    'id' => $calibration->id,
                    'calibration_date' => Carbon::parse($calibration->calibration_date)->format('d/m/Y'),
                    'next_recalibration_date' => Carbon::parse($calibration->next_recalibration_date)->format('d/m/Y'),
                    'observation' => $calibration->observation ?? 'Sin observaciones',
                    'performed_by' => $calibration->performed_by ?? '—',
                    'created_at' => $calibration->created_at ? $calibration->created_at->setTimezone($systemTimezone)->format('d/m/Y H:i') : '—',
                ]
            ]);
        }

        return redirect()->route('equipment.index')->with('success', 'Recalibración registrada en el historial exitosamente.');
    }

    /**
     * Get calibration history for a specific equipment.
     */
    public function getCalibrations($id)
    {
        $equipment = Equipment::with(['calibrations'])->findOrFail($id);
        $systemTimezone = SystemSetting::getTimezone();
        $today = Carbon::today($systemTimezone);

        $history = $equipment->calibrations->map(function ($calib) use ($today, $systemTimezone) {
            $nextDate = Carbon::parse($calib->next_recalibration_date);
            $daysRemaining = (int) $today->diffInDays($nextDate, false);

            $statusBadge = 'vigente';
            $statusText = 'Vigente';
            if ($daysRemaining < 0) {
                $statusBadge = 'vencido';
                $statusText = 'Vencido';
            } elseif ($daysRemaining <= 30) {
                $statusBadge = 'por_vencer';
                $statusText = 'Por vencer';
            }

            return [
                'id' => $calib->id,
                'calibration_date' => Carbon::parse($calib->calibration_date)->format('d/m/Y'),
                'calibration_date_raw' => Carbon::parse($calib->calibration_date)->format('Y-m-d'),
                'next_recalibration_date' => Carbon::parse($calib->next_recalibration_date)->format('d/m/Y'),
                'next_recalibration_date_raw' => Carbon::parse($calib->next_recalibration_date)->format('Y-m-d'),
                'observation' => $calib->observation ?? 'Sin observaciones registradas',
                'performed_by' => $calib->performed_by ?? 'Administrador',
                'created_at' => $calib->created_at ? $calib->created_at->setTimezone($systemTimezone)->format('d/m/Y H:i') : '—',
                'status_badge' => $statusBadge,
                'status_text' => $statusText,
                'days_remaining' => $daysRemaining,
            ];
        });

        return response()->json([
            'success' => true,
            'equipment' => [
                'id' => $equipment->id,
                'name' => $equipment->name,
                'model' => $equipment->model ?? '—',
                'serial_number' => $equipment->serial_number ?? 'S/N',
                'current_calibration_date' => $equipment->calibration_date ? Carbon::parse($equipment->calibration_date)->format('d/m/Y') : null,
                'current_next_recalibration_date' => $equipment->next_recalibration_date ? Carbon::parse($equipment->next_recalibration_date)->format('d/m/Y') : null,
            ],
            'calibrations' => $history,
        ]);
    }

    /**
     * Delete a calibration record from history.
     */
    public function destroyCalibration(Request $request, $calibrationId)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';
        if (!in_array(strtolower($userRole), ['superadministrador', 'administrador', 'admin'])) {
            return response()->json(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $calibration = EquipmentCalibration::findOrFail($calibrationId);
        $equipmentId = $calibration->equipment_id;
        $calibration->delete();

        // Resincronizar la última calibración vigente en el equipo
        $latest = EquipmentCalibration::where('equipment_id', $equipmentId)
            ->orderBy('calibration_date', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        $equipment = Equipment::find($equipmentId);
        if ($equipment) {
            $equipment->update([
                'calibration_date' => $latest ? $latest->calibration_date : null,
                'next_recalibration_date' => $latest ? $latest->next_recalibration_date : null,
                'recalibration_observation' => $latest ? $latest->observation : null,
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Registro de calibración eliminado correctamente.']);
        }

        return redirect()->route('equipment.index')->with('success', 'Registro de calibración eliminado.');
    }

    /**
     * Remove the specified equipment from storage.
     */
    public function destroy(Request $request, $id)
    {
        $currentUser = Auth::user();
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';
        if (!in_array(strtolower($userRole), ['superadministrador', 'administrador', 'admin'])) {
            return redirect()->route('equipment.index')->with('error', 'Acceso denegado: Solo el Administrador puede eliminar equipos del inventario general.');
        }

        $equipment = Equipment::find($id);

        if ($equipment) {
            if ($equipment->image && File::exists(public_path($equipment->image))) {
                File::delete(public_path($equipment->image));
            }
            $equipment->delete();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Equipo eliminado exitosamente.']);
        }

        return redirect()->route('equipment.index')->with('success', 'Equipo eliminado exitosamente.');
    }
}
