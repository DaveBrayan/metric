<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
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

        $query = Equipment::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('model', 'LIKE', "%{$search}%")
                  ->orWhere('serial_number', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($statusFilter) && $statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $equipments = $query->orderBy('name', 'asc')->get()->map(function ($item, $index) {
            $statusType = match (strtolower($item->status)) {
                'operativo' => 'done',
                'en calibración', 'calibración' => 'in_progress',
                'en mantenimiento', 'mantenimiento' => 'alert',
                default => 'pending',
            };

            return [
                'id' => $item->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $item->name,
                'model' => $item->model ?? '—',
                'serial_number' => $item->serial_number ?? 'S/N',
                'description' => $item->description ?? 'Sin descripción adicional',
                'raw_description' => $item->description ?? '',
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

        Equipment::create([
            'name' => $validated['name'],
            'model' => !empty($validated['model']) ? $validated['model'] : null,
            'serial_number' => !empty($validated['serial_number']) ? $validated['serial_number'] : null,
            'description' => !empty($validated['description']) ? $validated['description'] : null,
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

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

        $equipment->update([
            'name' => $validated['name'],
            'model' => !empty($validated['model']) ? $validated['model'] : null,
            'serial_number' => !empty($validated['serial_number']) ? $validated['serial_number'] : null,
            'description' => !empty($validated['description']) ? $validated['description'] : null,
            'status' => $validated['status'],
            'image' => $imagePath,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Equipo actualizado correctamente.']);
        }

        return redirect()->route('equipment.index')->with('success', 'Equipo actualizado exitosamente.');
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
