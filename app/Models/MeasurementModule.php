<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeasurementModule extends Model
{
    use HasFactory;

    protected $table = 'modules';

    protected $fillable = [
        'project_id',
        'key',
        'name',
        'calibration_equipment',
        'calibration_certificate',
        'field_staff_id',
        'points_total',
        'points_completed',
        'current_reading',
        'unit',
        'lmp_limit',
        'status',
        'status_theme',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fieldStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'field_staff_id');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(TelemetryReading::class, 'module_id');
    }
}
