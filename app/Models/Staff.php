<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'region_id',
        'name',
        'email',
        'phone',
        'department',
        'position',
        'role_theme',
        'status',
        'status_label',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(MeasurementModule::class, 'field_staff_id');
    }
}
