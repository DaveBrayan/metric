<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContaminantesQuimicosMeasurement extends Model
{
    use HasFactory;

    protected $table = 'contaminantes_quimicos_measurements';

    protected $fillable = [
        'module_id',
        'point_number',
        'codigo',
        'measurement_date',
        'measurement_time',
        'area',
        'punto_medicion',
        'trabajador_nombre',
        'masa_inicial_filtro_mg',
        'masa_final_filtro_mg',
        'hora_inicio',
        'hora_final',
        't_inicial_c',
        't_final_c',
        'presion_hpa',
        'q_inicial_lmin',
        'q_final_lmin',
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
        'masa_inicial_filtro_mg' => 'decimal:4',
        'masa_final_filtro_mg' => 'decimal:4',
        't_inicial_c' => 'decimal:2',
        't_final_c' => 'decimal:2',
        'presion_hpa' => 'decimal:2',
        'q_inicial_lmin' => 'decimal:2',
        'q_final_lmin' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'utm_easting' => 'decimal:3',
        'utm_northing' => 'decimal:3',
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

    /**
     * Calcula la masa neta recolectada en el filtro (mg).
     */
    public function getMasaNetaFiltroAttribute(): ?float
    {
        if ($this->masa_final_filtro_mg !== null && $this->masa_inicial_filtro_mg !== null) {
            return round((float)$this->masa_final_filtro_mg - (float)$this->masa_inicial_filtro_mg, 4);
        }
        return null;
    }

    /**
     * Calcula el caudal promedio (L/min).
     */
    public function getCaudalPromedioAttribute(): ?float
    {
        if ($this->q_inicial_lmin !== null && $this->q_final_lmin !== null) {
            return round(((float)$this->q_inicial_lmin + (float)$this->q_final_lmin) / 2, 3);
        }
        return (float)($this->q_inicial_lmin ?? $this->q_final_lmin ?? 0);
    }
}
