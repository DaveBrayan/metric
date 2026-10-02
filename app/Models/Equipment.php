<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipment';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'brand',
        'model',
        'serial_number',
        'description',
        'calibration_date',
        'next_recalibration_date',
        'recalibration_observation',
        'image',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'calibration_date' => 'date:Y-m-d',
        'next_recalibration_date' => 'date:Y-m-d',
    ];

    /**
     * Calibration history records.
     */
    public function calibrations()
    {
        return $this->hasMany(EquipmentCalibration::class, 'equipment_id')->orderBy('calibration_date', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Latest calibration history record.
     */
    public function latestCalibration()
    {
        return $this->hasOne(EquipmentCalibration::class, 'equipment_id')->latestOfMany('calibration_date');
    }

    /**
     * Scope for filtering by query.
     */
    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'LIKE', "%{$term}%")
              ->orWhere('model', 'LIKE', "%{$term}%")
              ->orWhere('serial_number', 'LIKE', "%{$term}%")
              ->orWhere('description', 'LIKE', "%{$term}%")
              ->orWhere('recalibration_observation', 'LIKE', "%{$term}%");
        });
    }
}
