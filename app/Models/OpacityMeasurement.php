<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpacityMeasurement extends Model
{
    use HasFactory;

    protected $table = 'opacity_measurements';

    protected $fillable = [
        'module_id',
        'project_id',
        'staff_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'altitud',
        'tipo_vehiculo',
        'marca',
        'modelo',
        'placa',
        'temp_c',
        'opa_1',
        'opa_2',
        'opa_3',
        'rpm_1',
        'rpm_2',
        'rpm_3',
        'opa_promedio',
        'rpm_promedio',
        'limite_normativa',
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
        'opa_1' => 'float',
        'opa_2' => 'float',
        'opa_3' => 'float',
        'rpm_1' => 'float',
        'rpm_2' => 'float',
        'rpm_3' => 'float',
        'opa_promedio' => 'float',
        'rpm_promedio' => 'float',
        'limite_normativa' => 'float',
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
