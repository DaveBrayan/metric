<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DosimetryMeasurement extends Model
{
    use HasFactory;

    protected $table = 'dosimetry_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'punto_medicion',
        'tipo_ruido',
        'tiempo_expos_h',
        'ponderacion',
        'respuesta',
        'duracion_medicion_h',
        'nps_max_db',
        'nps_min_db',
        'leq_t_db',
        'dosis_pct',
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
        'tiempo_expos_h' => 'decimal:2',
        'duracion_medicion_h' => 'decimal:2',
        'nps_max_db' => 'decimal:2',
        'nps_min_db' => 'decimal:2',
        'leq_t_db' => 'decimal:2',
        'dosis_pct' => 'decimal:2',
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
