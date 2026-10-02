<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RebaMeasurement extends Model
{
    use HasFactory;

    protected $table = 'reba_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'area_sector',
        'puesto_trabajo',
        'factor_riesgo',
        'num_trabajadores',
        'nombres_trabajadores',
        'edad',
        'tiempo_exposicion_horas',
        'procedimiento_escrito',
        'capacitacion',
        'fuerza_agarre',
        'carga_peso_kg',
        'distancia_m',
        'ayuda_mecanica',
        'descripcion_carga',
        'manifestacion_temprana',
        'ubicacion_sintoma',
        'tareas',
        'observaciones',
        'image_path',
        'images',
        'location',
        'latitude',
        'longitude',
        'utm_zone',
        'utm_easting',
        'utm_northing',
        'tronco_base',
        'tronco_mod',
        'cuello_base',
        'cuello_mod',
        'piernas_base',
        'piernas_mod',
        'carga_fuerza',
        'carga_brusca',
        'brazo_base',
        'brazo_abduccion',
        'brazo_hombro_elevado',
        'brazo_apoyo_gravedad',
        'antebrazo_base',
        'muneca_base',
        'muneca_mod',
        'agarre',
        'actividad_estatica',
        'actividad_repetitiva',
        'actividad_inestable',
        'score_tabla_a',
        'score_a',
        'score_tabla_b',
        'score_b',
        'score_c',
        'score_actividad',
        'score_final',
        'risk_level',
        'action_level',
        'risk_theme',
        'registered_by',
        'staff_id',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'nombres_trabajadores' => 'array',
        'tareas' => 'array',
        'images' => 'array',
        'tiempo_exposicion_horas' => 'float',
        'carga_peso_kg' => 'float',
        'distancia_m' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
        'utm_easting' => 'float',
        'utm_northing' => 'float',
        'score_final' => 'integer',
        'score_a' => 'integer',
        'score_b' => 'integer',
        'score_c' => 'integer',
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
