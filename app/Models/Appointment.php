<?php

namespace App\Models;

use App\Enums\AppointmentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Appointment extends Model
{
    use HasFactory;

    protected static $unguarded = false;

    public $fillable = [
        'pet_id',
        'slot_id',
        'clinic_id',
        'doctor_id',
        'date',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => AppointmentStatus::class,
        'date' => 'datetime'
    ];

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Slot::class);
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function scopeNew(Builder $query): void
    {
        $query->whereStatus(AppointmentStatus::Created);
    }

    /**
     * Get all of the appointments' notes.
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}
