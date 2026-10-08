<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VibracionMeasurement extends Model
{
    use HasFactory;

    protected $table = 'vibracion_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'codigo',
        'measurement_date',
        'measurement_time',
        'area',
        'workstation',
        'puesto_trabajo',
        'punto_medicion',
        'trabajador_evaluado',
        'maquina_equipo',
        'duracion_jornada_h',
        'tiempo_expos_h',
        'duracion_prueba_min',
        'tipo',
        'ub_acelerometro',
        'aeqx_ce',
        'aeqy_ce',
        'aeqz_ce',
        'mano_afectada',
        'aeqx_mb',
        'aeqy_mb',
        'aeqz_mb',
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
        'duracion_jornada_h' => 'decimal:2',
        'tiempo_expos_h' => 'decimal:2',
        'duracion_prueba_min' => 'integer',
        'aeqx_ce' => 'decimal:4',
        'aeqy_ce' => 'decimal:4',
        'aeqz_ce' => 'decimal:4',
        'aeqx_mb' => 'decimal:4',
        'aeqy_mb' => 'decimal:4',
        'aeqz_mb' => 'decimal:4',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'utm_easting' => 'decimal:3',
        'utm_northing' => 'decimal:3',
        'images' => 'array',
        'image_urls' => 'array',
    ];

    // Constantes de Valores Límite ISO 2631-1 / ISO 5349-1
    public const CE_NIVEL_ACCION = 0.50; // m/s²
    public const CE_LIMITE_VLE = 1.15;   // m/s²
    public const MB_NIVEL_ACCION = 2.50; // m/s²
    public const MB_LIMITE_VLE = 5.00;   // m/s²

    public function module(): BelongsTo
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Calcula la aceleración total equivalente ponderada.
     */
    public function getAceleracionTotalAttribute(): float
    {
        if ($this->tipo === 'mano_brazo') {
            $ax = (float) ($this->aeqx_mb ?? 0);
            $ay = (float) ($this->aeqy_mb ?? 0);
            $az = (float) ($this->aeqz_mb ?? 0);
            return sqrt(($ax * $ax) + ($ay * $ay) + ($az * $az));
        }

        // Cuerpo entero: ponderaciones X=1.4, Y=1.4, Z=1.0
        $ax = (float) ($this->aeqx_ce ?? 0);
        $ay = (float) ($this->aeqy_ce ?? 0);
        $az = (float) ($this->aeqz_ce ?? 0);
        $wx = 1.4 * $ax;
        $wy = 1.4 * $ay;
        $wz = 1.0 * $az;
        return sqrt(($wx * $wx) + ($wy * $wy) + ($wz * $wz));
    }

    /**
     * Calcula la aceleración diaria normalizada A(8).
     */
    public function getA8Attribute(): float
    {
        $atotal = $this->aceleracion_total;
        $texp = (float) ($this->tiempo_expos_h ?? 8.0);
        if ($texp <= 0) {
            $texp = 8.0;
        }
        return $atotal * sqrt($texp / 8.0);
    }

    /**
     * Retorna el nivel de acción aplicable.
     */
    public function getNivelAccionAttribute(): float
    {
        return $this->tipo === 'mano_brazo' ? self::MB_NIVEL_ACCION : self::CE_NIVEL_ACCION;
    }

    /**
     * Retorna el valor límite de exposición (VLE) aplicable.
     */
    public function getLimiteVleAttribute(): float
    {
        return $this->tipo === 'mano_brazo' ? self::MB_LIMITE_VLE : self::CE_LIMITE_VLE;
    }

    /**
     * Estado de cumplimiento según A(8).
     */
    public function getEstadoCumplimientoAttribute(): string
    {
        $a8 = $this->a8;
        $na = $this->nivel_accion;
        $vle = $this->limite_vle;

        if ($a8 <= $na) {
            return 'CUMPLE';
        } elseif ($a8 <= $vle) {
            return 'NIVEL DE ACCIÓN';
        } else {
            return 'SUPERA LÍMITE';
        }
    }

    /**
     * Helper para obtener el código estándar VIB-X.
     */
    public function getPointCodeAttribute(): string
    {
        if (!empty($this->codigo) && preg_match('/^VIB-\d+/i', $this->codigo)) {
            return strtoupper($this->codigo);
        }
        if (!empty($this->point_number) && preg_match('/^VIB-\d+/i', $this->point_number)) {
            return strtoupper($this->point_number);
        }
        return 'VIB-' . $this->id;
    }
}
