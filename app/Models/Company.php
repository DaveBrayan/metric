<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'legal_name',
        'nit',
        'industry',
        'contact_person',
        'email',
        'phone',
        'theme',
        'status',
    ];

    public function managers(): HasMany
    {
        return $this->hasMany(Manager::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
