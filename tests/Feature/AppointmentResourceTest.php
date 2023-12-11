<?php

use App\Enums\DaysOfTheWeek;
use App\Filament\Owner\Resources\AppointmentResource;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Pet;
use App\Models\Schedule;
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
    Storage::fake('avatars');
});

it('renders the index page', function () {
    get(AppointmentResource::getUrl('index', panel: 'owner'))
        ->assertOk();
});

it('can show a list of appointments', function () {

    $appointments = Appointment::factory(3)
        ->for(Pet::factory())
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    Livewire::test(AppointmentResource\Pages\ListAppointments::class)
        ->assertCanSeeTableRecords($appointments)
        ->assertSeeText($appointments[0]->pet->name)
        ->assertSeeText($appointments[0]->description)
        ->assertSeeText($appointments[0]->doctor->name)
        ->assertSeeText($appointments[0]->clinic->name)
        ->assertSeeText($appointments[0]->date->format('M d, Y'))
        ->assertSeeText($appointments[0]->slot->formatted_time)
        ->assertSeeText($appointments[0]->status->name);
});

it('only shows appointments for owned pets', function () {
    $myPet = Pet::factory()
        ->for($this->ownerUser, 'owner');

    $anotherPet = Pet::factory()->create();   

    $myPetAppointment = Appointment::factory()
        ->for($myPet)
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    $anotherAppointment = Appointment::factory()
        ->for($anotherPet)
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

        get(AppointmentResource::getUrl('index', panel: 'owner'))
            ->assertOk()
            ->assertSeeText($myPetAppointment->pet->name)
            ->assertDontSeeText($anotherAppointment->pet->name);
});

it('shows pet avatars', function () {
    $appointment = Appointment::factory()
        ->for(Pet::factory())
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->create();

    Livewire::test(AppointmentResource\Pages\ListAppointments::class)
        ->assertTableColumnStateSet('pet.avatar', 'avatar.png', $appointment);
});

it('can create appointments', function () {
    $appointment = Appointment::factory()
        ->for(Pet::factory())
        ->for(Slot::factory())
        ->for(Clinic::factory())
        ->state(['doctor_id' => $this->doctorUser->id])
        ->make();

    Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
        ->fillForm([
            'pet_id' => $appointment->pet_id,
            'clinic_id' => $appointment->clinic_id,
            'doctor_id' => $appointment->doctor_id,
            'slot_id' => $appointment->slot_id,
            'date' => $appointment->date,
            'description' => $appointment->description,
            'status' => $appointment->status,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

        $this->assertDatabaseHas(Appointment::class, [
            'pet_id' => $appointment->pet_id,
            'clinic_id' => $appointment->clinic_id,
            'doctor_id' => $appointment->doctor_id,
            'slot_id' => $appointment->slot_id,
            'date' => $appointment->date,
            'description' => $appointment->description,
            'status' => $appointment->status,
        ]);
});

it('hides doctor input by default on create', function () {
    Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
        ->assertFormFieldIsHidden('doctor_id');
});

it('shows doctor input when date is set', function () {
    Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
        ->fillForm([
            'date' => fake()->date()
        ])
        ->assertFormFieldIsVisible('doctor_id');
});

it('shows only available doctors for the selected date and clinic', function () {
    $mondaySchedule = Schedule::factory()
        ->for(User::factory()->role('doctor'), 'owner')
        ->for(Clinic::factory())
        ->state([
            'day_of_week' => DaysOfTheWeek::Monday,
        ])
        ->create();
    $tuesdaySchedule = Schedule::factory()
        ->for(User::factory()->role('doctor'), 'owner')
        ->for(Clinic::factory())
        ->state([
            'day_of_week' => DaysOfTheWeek::Tuesday,
        ])
        ->create();
    
    Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
        ->fillForm([
            'clinic_id' => $mondaySchedule->clinic->id,
            'date' => date('Y-m-d', strtotime('Monday'))
        ])
        ->assertSee($mondaySchedule->owner->name)
        ->assertDontSee($tuesdaySchedule->owner->name);
});

it('shows an error message when there are no available doctors', function () {
    $mondaySchedule = Schedule::factory()
        ->for(User::factory()->role('doctor'), 'owner')
        ->for(Clinic::factory())
        ->state([
            'day_of_week' => DaysOfTheWeek::Monday,
        ])
        ->create();
    
    Livewire::test(AppointmentResource\Pages\CreateAppointment::class)
        ->fillForm([
            'clinic_id' => $mondaySchedule->clinic->id,
            'date' => date('Y-m-d', strtotime('Tuesday'))
        ])
        ->assertSeeText('No doctors available');
});