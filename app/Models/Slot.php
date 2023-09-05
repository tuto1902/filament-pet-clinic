<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slot extends Model
{
    use HasFactory;

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime'
    ];

    public function appointment(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }
}
