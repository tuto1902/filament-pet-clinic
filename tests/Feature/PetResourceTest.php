<?php

use App\Filament\Owner\Resources\PetResource;
use App\Models\Pet;

use function Pest\Laravel\actingAs;

test('it renders the index page', function () {
    actingAs($this->ownerUser)
        ->get(PetResource::getUrl('index', panel: 'owner'))
        ->assertOk();
});

test('it renders the create page', function () {
    actingAs($this->ownerUser)
        ->get(PetResource::getUrl('create', panel: 'owner'))
        ->assertOk();
});

test('it renders the edit page', function () {
    $pet = Pet::factory()->create();

    actingAs($this->ownerUser)
        ->get(PetResource::getUrl('edit', ['record' => $pet], panel: 'owner'))
        ->assertOk();
});
