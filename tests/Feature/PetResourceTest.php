<?php

use App\Filament\Owner\Resources\PetResource;
use App\Models\Pet;
use App\Models\Role;
use App\Models\User;
use Database\Factories\PetFactory;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed();
    $this->ownerUser = User::whereName('Owner')->first();
    actingAs($this->ownerUser);
});

it('renders the index page', function () {
    get(PetResource::getUrl('index', panel: 'owner'))
        ->assertOk();
});

it('renders the create page', function () {
    get(PetResource::getUrl('create', panel: 'owner'))
        ->assertOk();
});

it('renders the edit page', function () {
    $pet = Pet::factory()->create();

    get(PetResource::getUrl('edit', ['record' => $pet], panel: 'owner'))
        ->assertOk();
});

it('can list pets', function () {
    $pets = Pet::factory(3)
        ->for($this->ownerUser, relationship: 'owner')
        ->create();

    Livewire::test(PetResource\Pages\ListPets::class)
        ->assertCanSeeTableRecords($pets)
        ->assertSeeText([
            $pets[0]->name,
            $pets[0]->date_of_birth->format(config('app.date_format')),
            $pets[0]->type->name,

            $pets[1]->name,
            $pets[1]->date_of_birth->format(config('app.date_format')),
            $pets[1]->type->name,

            $pets[2]->name,
            $pets[2]->date_of_birth->format(config('app.date_format')),
            $pets[2]->type->name,
        ]);
});

it('only shows pets for the current owner', function () {
    $myPet = Pet::factory()
        ->for($this->ownerUser, relationship: 'owner')
        ->create();

    $otherOwner = User::factory()->role('owner')->create();

    $otherPet = Pet::factory()
        ->for($otherOwner, relationship: 'owner')->create();

    Livewire::test(PetResource\Pages\ListPets::class)
        ->assertSeeText($myPet->name)
        ->assertDontSeeText($otherPet->name);
});

it('can create pets', function () {
    $newPet = Pet::factory()
        ->for($this->ownerUser, relationship: 'owner')
        ->make();

    Livewire::test(PetResource\Pages\CreatePet::class)
        ->fillForm([
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' => $newPet->type
        ])
        ->call('create')
        ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Pet::class, [
            'name' => $newPet->name,
            'date_of_birth' => $newPet->date_of_birth,
            'type' => $newPet->type
        ]);
});
