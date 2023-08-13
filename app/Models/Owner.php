<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    use HasFactory;

    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    public function clinic(): BelongsToMany
    {
        return $this->belongsToMany(Clinic::class);
    }
}
