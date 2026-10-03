<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotographicInspection extends Model
{
    use HasFactory;

    protected $table = 'photographic_inspections';

    protected $fillable = [
        'module_id',
        'point_number',
        'inspection_date',
        'inspection_time',
        'area',
        'observation',
        'description',
        'location',
        'latitude',
        'longitude',
        'utm_zone',
        'utm_easting',
        'utm_northing',
        'gps_accuracy',
        'images',
        'image_path',
        'registered_by',
        'staff_id',
    ];

    protected $casts = [
        'inspection_date' => 'date',
        'images' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'utm_easting' => 'decimal:3',
        'utm_northing' => 'decimal:3',
        'gps_accuracy' => 'decimal:2',
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
