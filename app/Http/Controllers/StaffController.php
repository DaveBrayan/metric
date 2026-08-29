<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();
        $userName = $currentUser ? $currentUser->name : 'Reynaldo';
        $userRole = $currentUser ? $currentUser->role : 'Superadministrador';

        $regions = Region::all();

        $staff = Staff::with('region')->get()->map(function ($s, $index) {
            return [
                'id' => $s->id,
                'num' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'name' => $s->name,
                'email' => $s->email,
                'phone' => $s->phone,
                'initial' => strtoupper(substr($s->name, 0, 1)),
                'department' => $s->department,
                'position' => $s->position,
                'region' => $s->region ? $s->region->name : 'Central',
                'role_theme' => $s->role_theme ?? 'cyan',
                'status' => $s->status ?? 'online',
                'status_label' => $s->status_label ?? 'En Planta',
            ];
        })->toArray();

        return view('staff.index', compact('userName', 'userRole', 'staff', 'regions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:staff,email',
            'phone' => 'nullable|string',
            'department' => 'required|string',
            'position' => 'required|string',
            'region_id' => 'nullable|exists:regions,id',
        ]);

        Staff::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'department' => $validated['department'],
            'position' => $validated['position'],
            'region_id' => $validated['region_id'] ?? null,
            'role_theme' => 'cyan',
            'status' => 'online',
            'status_label' => 'En Planta',
        ]);

        return redirect()->route('staff.index')->with('success', 'Colaborador registrado exitosamente.');
    }
}
