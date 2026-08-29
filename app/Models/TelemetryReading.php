<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelemetryReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'station_code',
        'value',
        'unit',
        'is_compliant',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'is_compliant' => 'boolean',
        'recorded_at' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }
}
