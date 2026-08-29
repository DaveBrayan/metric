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
            return [
                'id' => $reg->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $reg->name,
                'code' => $reg->code,
                'initial' => strtoupper(substr($reg->code, 0, 2)),
                'theme' => $reg->theme ?? 'cyan',
                'manager' => $reg->manager_name ?? 'Ing. Reynaldo Sirpa',
                'address' => $reg->address,
                'assigned_projects' => $reg->projects_count . ' Proyectos Asignados',
                'staff_count' => $reg->staff_count . ' Especialistas',
                'status' => $reg->status,
                'status_type' => ($reg->status === 'Operativo') ? 'done' : 'in_progress',
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
        ]);

        Region::create([
            'name' => $validated['name'],
            'code' => strtoupper($validated['code']),
            'department' => $validated['department'],
            'address' => $validated['address'] ?? null,
            'manager_name' => $validated['manager_name'] ?? 'Ing. Reynaldo Sirpa',
            'theme' => 'cyan',
            'status' => 'Operativo',
        ]);

        return redirect()->route('regions.index')->with('success', 'Regional registrada exitosamente.');
    }
}
