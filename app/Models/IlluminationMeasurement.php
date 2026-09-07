<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IlluminationMeasurement extends Model
{
    use HasFactory;

    protected $table = 'illumination_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'workstation',
        'measurement_point',
        'activity_description',
        'lighting_type',
        'required_lux',
        'measured_lux',
        'readings',
        'image_path',
        'images',
        'location',
        'latitude',
        'longitude',
        'observations',
        'registered_by',
        'staff_id',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'required_lux' => 'decimal:2',
        'measured_lux' => 'decimal:2',
        'readings' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function getIsCompliantAttribute(): bool
    {
        return (float) $this->measured_lux >= (float) $this->required_lux;
    }
}
