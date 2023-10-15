<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    use HasFactory;

    /**
     * Get the parent notable model (pet or appointment).
     */
    public function notable(): MorphTo
    {
        return $this->morphTo();
    }
}
