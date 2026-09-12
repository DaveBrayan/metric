<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VentilationMeasurement extends Model
{
    use HasFactory;

    protected $table = 'ventilation_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'local_trabajo',
        'tipo_local',
        'tipo_ventilacion',
        'elemento_ventilacion',
        'temperatura_seca_c',
        'vel_aire_ms',
        'vel_aire_mh',
        'area_largo_m',
        'area_ancho_m',
        'area_diametro_m',
        'area_ventilacion_m2',
        'caudal_m3h',
        'vol_largo_m',
        'vol_ancho_m',
        'vol_alto_m',
        'volumen_m3',
        'renovaciones_h',
        'renovaciones_min',
        'renovaciones_max',
        'renovaciones_intervalo',
        'cumple',
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
        'temperatura_seca_c' => 'decimal:2',
        'vel_aire_ms' => 'decimal:2',
        'vel_aire_mh' => 'decimal:2',
        'area_largo_m' => 'decimal:2',
        'area_ancho_m' => 'decimal:2',
        'area_diametro_m' => 'decimal:2',
        'area_ventilacion_m2' => 'decimal:4',
        'caudal_m3h' => 'decimal:2',
        'vol_largo_m' => 'decimal:2',
        'vol_ancho_m' => 'decimal:2',
        'vol_alto_m' => 'decimal:2',
        'volumen_m3' => 'decimal:2',
        'renovaciones_h' => 'decimal:2',
        'renovaciones_min' => 'decimal:2',
        'renovaciones_max' => 'decimal:2',
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
