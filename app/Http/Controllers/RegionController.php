<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegionController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $regions = Region::withCount(['projects', 'staff'])->get()->map(function ($reg, $index) {
            $isOperativo = in_array(strtolower($reg->status), ['operativo', 'activo']);
            return [
                'id' => $reg->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $reg->name,
                'code' => $reg->code,
                'department' => $reg->department ?? 'La Paz',
                'initial' => strtoupper(substr($reg->code, 0, 2)),
                'theme' => $reg->theme ?? 'cyan',
                'manager' => $reg->manager_name ?? 'Ing. Reynaldo Sirpa',
                'address' => $reg->address,
                'assigned_projects' => ($reg->projects_count ?? 0) . ' Proyectos Asignados',
                'staff_count' => ($reg->staff_count ?? 0) . ' Especialistas',
                'status' => $reg->status ?? 'Operativo',
                'status_type' => $isOperativo ? 'done' : 'in_progress',
            ];
        })->toArray();

        return view('regions.index', compact('userName', 'userRole', 'regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:regions,code',
            'department' => 'required|string',
            'address' => 'nullable|string',
            'manager_name' => 'nullable|string',
            'status' => 'nullable|in:Operativo,Mantenimiento,Inactivo',
        ]);

        Region::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'department' => $validated['department'],
            'address' => $validated['address'] ?? null,
            'manager_name' => $validated['manager_name'] ?? 'Ing. Reynaldo Sirpa',
            'theme' => 'cyan',
            'status' => $validated['status'] ?? 'Operativo',
        ]);

        return redirect()->route('regions.index')->with('success', 'Regional registrada exitosamente.');
    }

    public function update(Request $request, $id)
    {
        $region = Region::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:regions,code,' . $region->id,
            'department' => 'required|string',
            'address' => 'nullable|string',
            'manager_name' => 'nullable|string',
            'status' => 'required|in:Operativo,Mantenimiento,Inactivo',
        ]);

        $region->update([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'department' => $validated['department'],
            'address' => $validated['address'] ?? null,
            'manager_name' => $validated['manager_name'] ?? 'Ing. Reynaldo Sirpa',
            'status' => $validated['status'],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Regional actualizada exitosamente.']);
        }

        return redirect()->route('regions.index')->with('success', 'Regional actualizada exitosamente.');
    }

    public function destroy(Request $request, $id)
    {
        $region = Region::find($id);

        if ($region) {
            $region->delete();
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Regional eliminada exitosamente.']);
        }

        return redirect()->route('regions.index')->with('success', 'Regional eliminada exitosamente.');
    }
}
