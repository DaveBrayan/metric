<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FireWeightMeasurement extends Model
{
    use HasFactory;

    protected $table = 'fire_weight_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'macroarea',
        'sector_name',
        'dimensions',
        'yi_largo',
        'xi_ancho',
        'area_m2',
        'materials',
        'fire_equipments',
        'qs_mj_m2',
        'qs_mcal_m2',
        'risk_level',
        'risk_color',
        'ra_value',
        'image_path',
        'images',
        'location',
        'latitude',
        'longitude',
        'utm_zone',
        'utm_easting',
        'utm_northing',
        'registered_by',
        'staff_id',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'dimensions' => 'array',
        'materials' => 'array',
        'fire_equipments' => 'array',
        'images' => 'array',
        'yi_largo' => 'float',
        'xi_ancho' => 'float',
        'area_m2' => 'float',
        'qs_mj_m2' => 'float',
        'qs_mcal_m2' => 'float',
        'ra_value' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'utm_easting' => 'float',
        'utm_northing' => 'float',
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
