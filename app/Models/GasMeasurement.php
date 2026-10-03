<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GasMeasurement extends Model
{
    use HasFactory;

    protected $table = 'gas_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'workstation',
        'measurement_point',
        'activity_description',
        'temperatura',
        'presion_atm',
        'vel_aire',
        'selected_gases',
        'gases_readings',
        'o2_values',
        'o2_prom',
        'h2s_values',
        'h2s_prom',
        'co_values',
        'co_prom',
        'lel_values',
        'lel_prom',
        'hcho_values',
        'hcho_prom',
        'tvoc_values',
        'tvoc_prom',
        'co2_values',
        'co2_prom',
        'as_values',
        'as_prom',
        'so2_values',
        'so2_prom',
        'nh3_values',
        'nh3_prom',
        'cl2_values',
        'cl2_prom',
        'tcov_values',
        'tcov_prom',
        'no2_values',
        'no2_prom',
        'location',
        'latitude',
        'longitude',
        'utm_zone',
        'utm_easting',
        'utm_northing',
        'image_path',
        'images',
        'observations',
        'registered_by',
        'staff_id',
        'local_uuid',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'temperatura' => 'decimal:2',
        'presion_atm' => 'decimal:2',
        'vel_aire' => 'decimal:2',
        'selected_gases' => 'array',
        'gases_readings' => 'array',
        'o2_values' => 'array',
        'o2_prom' => 'decimal:3',
        'h2s_values' => 'array',
        'h2s_prom' => 'decimal:3',
        'co_values' => 'array',
        'co_prom' => 'decimal:3',
        'lel_values' => 'array',
        'lel_prom' => 'decimal:3',
        'hcho_values' => 'array',
        'hcho_prom' => 'decimal:3',
        'tvoc_values' => 'array',
        'tvoc_prom' => 'decimal:3',
        'co2_values' => 'array',
        'co2_prom' => 'decimal:3',
        'as_values' => 'array',
        'as_prom' => 'decimal:3',
        'so2_values' => 'array',
        'so2_prom' => 'decimal:3',
        'nh3_values' => 'array',
        'nh3_prom' => 'decimal:3',
        'cl2_values' => 'array',
        'cl2_prom' => 'decimal:3',
        'tcov_values' => 'array',
        'tcov_prom' => 'decimal:3',
        'no2_values' => 'array',
        'no2_prom' => 'decimal:3',
        'images' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'utm_easting' => 'decimal:3',
        'utm_northing' => 'decimal:3',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
