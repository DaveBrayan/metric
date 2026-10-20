<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoiseMeasurement extends Model
{
    use HasFactory;

    protected $table = 'noise_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'maquinaria_equipo',
        'actividad',
        'descripcion_actividad',
        'tipo_ruido',
        'punto_medicion',
        'tiempo_expos_h',
        'ponderacion',
        'respuesta',
        'mediciones_db',
        'leq_t_db',
        'uso_protectores',
        'tipo_protector',
        'nrr_db',
        'observations',
        'images',
        'image_path',
        'location',
        'latitude',
        'longitude',
        'utm_zone',
        'utm_easting',
        'utm_northing',
        'registered_by',
        'staff_id',
        'cumple',
        'local_uuid',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'tiempo_expos_h' => 'decimal:2',
        'leq_t_db' => 'decimal:2',
        'nrr_db' => 'decimal:2',
        'mediciones_db' => 'array',
        'images' => 'array',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'utm_easting' => 'decimal:3',
        'utm_northing' => 'decimal:3',
        'uso_protectores' => 'boolean',
        'cumple' => 'boolean',
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