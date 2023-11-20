<?php

namespace App\Filament\Owner\Resources\AppointmentResource\Pages;

use App\Filament\Owner\Resources\AppointmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;
}
