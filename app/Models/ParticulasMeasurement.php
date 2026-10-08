<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticulasMeasurement extends Model
{
    use HasFactory;

    protected $table = 'particulas_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'workstation',
        'punto_medicion',
        'temperatura',
        'hr_percent',
        'pm10_values',
        'pm10_prom',
        'pm25_values',
        'pm25_prom',
        'pts_values',
        'pts_prom',
        'location',
        'latitude',
        'longitude',
        'utm_zone',
        'utm_easting',
        'utm_northing',
        'image_path',
        'images',
        'image_urls',
        'observations',
        'registered_by',
        'created_by',
        'staff_id',
        'local_uuid',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'temperatura' => 'decimal:2',
        'hr_percent' => 'decimal:2',
        'pm10_values' => 'array',
        'pm10_prom' => 'decimal:3',
        'pm25_values' => 'array',
        'pm25_prom' => 'decimal:3',
        'pts_values' => 'array',
        'pts_prom' => 'decimal:3',
        'images' => 'array',
        'image_urls' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function getPointNameAttribute(): string
    {
        return $this->punto_medicion ?: ($this->workstation ?: 'Punto de Medición');
    }
}
