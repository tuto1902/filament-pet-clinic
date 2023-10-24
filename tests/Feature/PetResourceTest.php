<?php

use function Pest\Laravel\get;
use App\Filament\Owner\Resources\PetResource;
use App\Models\Pet;

use function Pest\Laravel\actingAs;

test('it renders the index page', function () {
    actingAs($this->ownerUser)
        ->get('owner/pets')
        ->assertOk();
});

test('it renders the create page', function () {
    actingAs($this->ownerUser)
        ->get('owner/pets/create')
        ->assertOk();
});

test('it renders the edit page', function () {
    $pet = Pet::factory()->create();

    actingAs($this->ownerUser)
        ->get("owner/pets/$pet->id/edit")
        ->assertOk();
});
