<?php

use App\Filament\Owner\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Slot;
use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\seed;

beforeEach(function () {
    seed();
    $this->ownerUser = User::whereName('Owner')->first();
    $this->doctorUser = User::whereName('Doctor')->first();
    actingAs($this->ownerUser);
});

it('renders the index page', function () {
    get(AppointmentResource::getUrl('index', panel: 'owner'))
        ->assertOk();
});

it('can show a list of appointments', function () {
    $appointments = Appointment::factory(3)
        ->for(Pet::factory())
        ->for(Slot::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();
    
    Livewire::test(AppointmentResource\Pages\ListAppointments::class)
        ->assertCanSeeTableRecords($appointments);
});
