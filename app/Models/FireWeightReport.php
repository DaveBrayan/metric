<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FireWeightReport extends Model
{
    use HasFactory;

    protected $table = 'fire_weight_reports';

    protected $fillable = [
        'module_id',
        'macroarea',
        'dimensions',
        'sectors',
        'fire_equipments',
        'macro_summary',
        'extinguishers',
        'report_data',
    ];

    protected $casts = [
        'dimensions' => 'array',
        'sectors' => 'array',
        'fire_equipments' => 'array',
        'macro_summary' => 'array',
        'extinguishers' => 'array',
        'report_data' => 'array',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(MeasurementModule::class, 'module_id');
    }
}
