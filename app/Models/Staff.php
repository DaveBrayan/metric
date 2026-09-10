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
        'manager_id',
        'first_name',
        'last_name',
        'name',
        'email',
        'position',
        'device_name',
        'fcm_token',
        'password_plain',
        'phone',
        'department',
        'role_theme',
        'status',
        'status_label',
        'must_change_password',
    ];

    protected $casts = [
        'must_change_password' => 'boolean',
    ];

    /**
     * Devuelve el nombre completo combinando first_name y last_name o fallback a name.
     */
    public function getFullNameAttribute(): string
    {
        $combined = trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
        return $combined ?: ($this->name ?? 'Colaborador');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Manager::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(MeasurementModule::class, 'field_staff_id');
    }
}
