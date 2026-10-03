<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MeasurementModule extends Model
{
    use HasFactory;

    protected $table = 'modules';

    protected $fillable = [
        'project_id',
        'key',
        'name',
        'description',
        'calibration_equipment',
        'equipment_id',
        'calibration_certificate',
        'field_staff_id',
        'field_staff_ids',
        'points_total',
        'points_completed',
        'current_reading',
        'unit',
        'lmp_limit',
        'status',
        'status_theme',
        'start_date',
        'end_date',
        'monitoring_type',
        'installation_name',
        'photo_report_settings',
        'anexo2_data',
    ];

    protected $casts = [
        'field_staff_ids' => 'array',
        'photo_report_settings' => 'array',
        'anexo2_data' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fieldStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'field_staff_id');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class, 'equipment_id');
    }

    public function illuminationMeasurements(): HasMany
    {
        return $this->hasMany(IlluminationMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function ventilationMeasurements(): HasMany
    {
        return $this->hasMany(VentilationMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function dosimetryMeasurements(): HasMany
    {
        return $this->hasMany(DosimetryMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function ruidoAmbientalMeasurements(): HasMany
    {
        return $this->hasMany(RuidoAmbientalMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function opacityMeasurements(): HasMany
    {
        return $this->hasMany(OpacityMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function coldStressMeasurements(): HasMany
    {
        return $this->hasMany(ColdStressMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function heatStressMeasurements(): HasMany
    {
        return $this->hasMany(HeatStressMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function rebaMeasurements(): HasMany
    {
        return $this->hasMany(RebaMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function rosaMeasurements(): HasMany
    {
        return $this->hasMany(RosaMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function fireActivityMeasurements(): HasMany
    {
        return $this->hasMany(FireActivityMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function fireWeightMeasurements(): HasMany
    {
        return $this->hasMany(FireWeightMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function fireWeightReport(): HasOne
    {
        return $this->hasOne(FireWeightReport::class, 'module_id');
    }

    public function gasMeasurements(): HasMany
    {
        return $this->hasMany(GasMeasurement::class, 'module_id')->orderBy('id', 'asc');
    }

    public function readings(): HasMany
    {
        return $this->hasMany(TelemetryReading::class, 'module_id');
    }

    public function photographicInspections(): HasMany
    {
        return $this->hasMany(PhotographicInspection::class, 'module_id')->orderBy('id', 'asc');
    }

    public function getAssignedStaffAttribute()
    {
        if (!empty($this->field_staff_ids) && is_array($this->field_staff_ids)) {
            return Staff::whereIn('id', $this->field_staff_ids)->get();
        }
        if ($this->fieldStaff) {
            return collect([$this->fieldStaff]);
        }
        return collect();
    }
}

