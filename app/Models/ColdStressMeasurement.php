<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ColdStressMeasurement extends Model
{
    use HasFactory;

    protected $table = 'cold_stress_measurements';

    protected $fillable = [
        'module_id',
        'project_id',
        'staff_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'puesto_trabajo',
        'desc_actividades',
        'temp_c',
        'hr_percent',
        'vel_viento_ms',
        'presion_mmhg',
        'metabolismo',
        'aislamiento',
        'indice_viento_wci',
        'sensacion_termica_c',
        'nivel_riesgo',
        'tiempo_limite_exposicion',
        'is_compliant',
        'latitude',
        'longitude',
        'utm_zone',
        'utm_easting',
        'utm_northing',
        'location_description',
        'observations',
        'photo_paths',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'is_compliant' => 'boolean',
        'temp_c' => 'float',
        'hr_percent' => 'float',
        'vel_viento_ms' => 'float',
        'presion_mmhg' => 'float',
        'indice_viento_wci' => 'float',
        'sensacion_termica_c' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'utm_easting' => 'float',
        'utm_northing' => 'float',
        'photo_paths' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
