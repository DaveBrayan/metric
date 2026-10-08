<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticulasAmbientalesMeasurement extends Model
{
    use HasFactory;

    protected $table = 'particulas_ambientales_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area',
        'punto_medicion',
        'workstation',
        'fecha_inicio',
        'hora_inicio',
        'fecha_fin',
        'hora_fin',
        'diferencia_horas',
        'temp_max',
        'temp_min',
        'temperatura',
        'presion_atm',
        'vel_viento',
        'dir_viento',
        'hr_percent',
        'pm10_filtro_inicial',
        'pm10_filtro_final',
        'pm10_prom',
        'pm10_values',
        'pst_filtro_inicial',
        'pst_filtro_final',
        'pst_prom',
        'pts_values',
        'pm25_filtro_inicial',
        'pm25_filtro_final',
        'pm25_prom',
        'pm25_values',
        'caudal',
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
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'diferencia_horas' => 'decimal:2',
        'temp_max' => 'decimal:2',
        'temp_min' => 'decimal:2',
        'temperatura' => 'decimal:2',
        'presion_atm' => 'decimal:2',
        'vel_viento' => 'decimal:2',
        'hr_percent' => 'decimal:2',
        'pm10_filtro_inicial' => 'decimal:4',
        'pm10_filtro_final' => 'decimal:4',
        'pm10_prom' => 'decimal:3',
        'pm10_values' => 'array',
        'pst_filtro_inicial' => 'decimal:4',
        'pst_filtro_final' => 'decimal:4',
        'pst_prom' => 'decimal:3',
        'pts_values' => 'array',
        'pm25_filtro_inicial' => 'decimal:4',
        'pm25_filtro_final' => 'decimal:4',
        'pm25_prom' => 'decimal:3',
        'pm25_values' => 'array',
        'caudal' => 'decimal:2',
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
}
