<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeatStressMeasurement extends Model
{
    use HasFactory;

    protected $table = 'heat_stress_measurements';

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
        'interior_exterior',
        'aclimatado',
        'tipo_ropa_cav',
        'cav_ajuste_db',
        'capucha',
        'tasa_metabolica',
        'temp_c',
        'hr_percent',
        'vel_viento_ms',
        'presion_mmhg',
        'wb_c',
        'gt_c',
        'wbgt_c',
        'wbgt_efectivo_c',
        'limite_wbgt_lmp',
        'regimen_trabajo_descanso',
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
        'wb_c' => 'float',
        'gt_c' => 'float',
        'wbgt_c' => 'float',
        'wbgt_efectivo_c' => 'float',
        'cav_ajuste_db' => 'float',
        'limite_wbgt_lmp' => 'float',
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
