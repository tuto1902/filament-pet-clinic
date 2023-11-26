<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

final class AvatarOptions
{
    public static function getOptionString(Model $record): string
    {
        return view('filament.components.select-results', compact('record'))->render();
    }
}
