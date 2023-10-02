<?php

namespace App\Filament\Doctor\Resources\PetResource\Pages;

use App\Filament\Doctor\Resources\PetResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPets extends ListRecords
{
    protected static string $resource = PetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
