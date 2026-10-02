<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RosaMeasurement extends Model
{
    use HasFactory;

    protected $table = 'rosa_measurements';

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
        'altura_asiento_base',
        'altura_asiento_mod',
        'profundidad_base',
        'profundidad_mod',
        'reposabrazos_base',
        'reposabrazos_mod',
        'respaldo_base',
        'respaldo_mod',
        'silla_tiempo_uso',
        'pantalla_base',
        'pantalla_mod',
        'pantalla_tiempo',
        'telefono_base',
        'telefono_mod',
        'telefono_tiempo',
        'raton_base',
        'raton_mod',
        'raton_tiempo',
        'teclado_base',
        'teclado_mod',
        'teclado_tiempo',
        'actividad_estatica',
        'actividad_repetitiva',
        'actividad_inestable',
        'score_a',
        'score_b',
        'score_c',
        'score_d',
        'score_e',
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
        'tiempo_exposicion_horas' => 'decimal:2',
        'carga_peso_kg' => 'decimal:2',
        'distancia_m' => 'decimal:2',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'utm_easting' => 'decimal:3',
        'utm_northing' => 'decimal:3',
        'altura_asiento_base' => 'integer',
        'altura_asiento_mod' => 'integer',
        'profundidad_base' => 'integer',
        'profundidad_mod' => 'integer',
        'reposabrazos_base' => 'integer',
        'reposabrazos_mod' => 'integer',
        'respaldo_base' => 'integer',
        'respaldo_mod' => 'integer',
        'silla_tiempo_uso' => 'integer',
        'pantalla_base' => 'integer',
        'pantalla_mod' => 'integer',
        'pantalla_tiempo' => 'integer',
        'telefono_base' => 'integer',
        'telefono_mod' => 'integer',
        'telefono_tiempo' => 'integer',
        'raton_base' => 'integer',
        'raton_mod' => 'integer',
        'raton_tiempo' => 'integer',
        'teclado_base' => 'integer',
        'teclado_mod' => 'integer',
        'teclado_tiempo' => 'integer',
        'actividad_estatica' => 'integer',
        'actividad_repetitiva' => 'integer',
        'actividad_inestable' => 'integer',
        'score_a' => 'integer',
        'score_b' => 'integer',
        'score_c' => 'integer',
        'score_d' => 'integer',
        'score_e' => 'integer',
        'score_actividad' => 'integer',
        'score_final' => 'integer',
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
