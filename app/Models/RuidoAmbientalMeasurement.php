<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuidoAmbientalMeasurement extends Model
{
    use HasFactory;

    protected $table = 'ruido_ambiental_measurements';

    protected $fillable = [
        'module_id',
        'project_id',
        'staff_id',
        'point_number',
        'measurement_date',
        'measurement_time',
        'normativa',
        'tipo_zona',
        'horario',
        'limite_normativa',
        'zona_banda',
        'norte_colindancia',
        'norte_x',
        'norte_y',
        'sur_colindancia',
        'sur_x',
        'sur_y',
        'este_colindancia',
        'este_x',
        'este_y',
        'oeste_colindancia',
        'oeste_x',
        'oeste_y',
        'p1_norte_inicio',
        'p1_norte_fin',
        'p1_norte_puntos',
        'p2_sur_inicio',
        'p2_sur_fin',
        'p2_sur_puntos',
        'p3_este_inicio',
        'p3_este_fin',
        'p3_este_puntos',
        'p4_oeste_inicio',
        'p4_oeste_fin',
        'p4_oeste_puntos',
        'mediciones_db',
        'leq_d',
        'nps_max',
        'nps_min',
        'is_compliant',
        'latitude',
        'longitude',
        'location',
        'image_path',
        'images',
        'observations',
        'registered_by',
        'status',
    ];

    protected $casts = [
        'measurement_date' => 'date',
        'limite_normativa' => 'float',
        'leq_d' => 'float',
        'nps_max' => 'float',
        'nps_min' => 'float',
        'is_compliant' => 'boolean',
        'latitude' => 'float',
        'longitude' => 'float',
        'p1_norte_puntos' => 'array',
        'p2_sur_puntos' => 'array',
        'p3_este_puntos' => 'array',
        'p4_oeste_puntos' => 'array',
        'mediciones_db' => 'array',
        'images' => 'array',
    ];

    public function module()
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
