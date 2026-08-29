<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'department',
        'address',
        'manager_name',
        'theme',
        'status',
    ];

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
