<?php

namespace App\Filament\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Pet;
use App\Models\Role;
use App\Models\Slot;
use App\Models\User;
use App\Support\AvatarOptions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();

        return $form
            ->schema([
                Forms\Components\Section::make([
                Forms\Components\Select::make('pet_id')
                    ->label('Pet')
                    ->allowHtml()
                    ->searchable()
                    ->required()
                    ->columnSpanFull()
                    ->getSearchResultsUsing(function (string $search) {
                        $pets = Pet::where('name', 'like', "%{$search}%")->limit(50)->get();
                    
                        return $pets->mapWithKeys(function ($pet) {
                                return [$pet->getKey() => AvatarOptions::getOptionString($pet)];
                        })->toArray();
                    })
                    ->options(function (): array {
                        $pets = Pet::all();

                        return $pets->mapWithKeys(function ($pet) {
                            return [$pet->getKey() => AvatarOptions::getOptionString($pet)];
                        })->toArray();
                    }),
                    Forms\Components\Select::make('clinic_id')
                        ->relationship('clinic', 'name')
                        ->preload()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set) {
                            $set('date', null);
                            $set('doctor', null);
                        }),
                    Forms\Components\DatePicker::make('date')
                        ->native(false)
                        ->displayFormat('M d, Y')
                        ->closeOnDateSelection()
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('doctor_id', null)),
                    Forms\Components\Select::make('doctor_id')
                        ->label('Doctor')
                        ->allowHtml()
                        ->options(function (Get $get) use ($doctorRole) {
                            $doctors = User::whereBelongsTo($doctorRole)
                                ->whereHas('schedules', function (Builder $query) use ($get) {
                                    $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                                    $query
                                        ->where('day_of_week', $dayOfTheWeek)
                                        ->where('clinic_id', $get('clinic_id'));

                                })
                                ->get();
                            return $doctors->mapWithKeys(function ($doctor) {
                                return [$doctor->getKey() => AvatarOptions::getOptionString($doctor)];
                            })->toArray();
                        })
                        ->native(false)
                        ->hidden(fn (Get $get) => blank($get('date')))
                        ->live()
                        ->afterStateUpdated(fn (Set $set) => $set('slot_id', null))
                        ->helperText(function ($component) {
                            if (! $component->getOptions()) {
                                return new HtmlString(
                                    '<span class="text-sm text-danger-600 dark:text-danger-400">No Doctors available. Please select a different clinic or date</span>'
                                );
                            }

                            return '';
                        }),
                    Forms\Components\Select::make('slot_id')
                        ->native(false)
                        ->label('Slot')
                        ->required()
                        ->options(function (Get $get) {
                            $doctor = User::find($get('doctor_id'));
                            $dayOfTheWeek = Carbon::parse($get('date'))->dayOfWeek;
                            $clinicId = $get('clinic_id');
                            
                            return $clinicId ? Slot::availableFor($doctor, $dayOfTheWeek, $clinicId)->get()->pluck('formatted_time', 'id') : [];
                        })
                        ->hidden(fn (Get $get) => blank($get('doctor_id')))
                        ->getOptionLabelFromRecordUsing(fn (Slot $record) => $record->formatted_time)
                        ->helperText(function ($component) {
                            if (! $component->getOptions()) {
                                return new HtmlString(
                                    '<span class="text-sm text-danger-600 dark:text-danger-400">No time slots available. Please select a different clinic, date or doctor</span>'
                                );
                            }

                            return '';
                        }),
                    Forms\Components\TextInput::make('description')
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->native(false)
                        ->options(AppointmentStatus::class)
                        ->visibleOn(Pages\EditAppointment::class)
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        $doctorId = Role::whereName('doctor')->pluck('id');

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pet.name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('clinic.name')
                    ->label('Clinic')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('slot.formatted_time')
                    ->label('Time')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('clinic_id')
                    ->label('Clinic')
                    ->relationship('clinic', 'name'),
                Tables\Filters\SelectFilter::make('doctor_id')
                    ->label('Doctor')
                    // ToDo: rework the $query into a private function
                    ->relationship('doctor', 'name', modifyQueryUsing: fn (Builder $query) => $query->where('role_id', $doctorId)),
                Tables\Filters\SelectFilter::make('status')
                    ->options(AppointmentStatus::class)
            ])
            ->actions([
                Tables\Actions\Action::make('Confirm')
                    ->action(function (Appointment $record) {
                        $record->status = AppointmentStatus::Confirmed;
                        $record->save();
                    })
                    ->visible(fn (Appointment $record) => $record->status == AppointmentStatus::Created)
                    ->color('success')
                    ->icon('heroicon-o-check'),
                Tables\Actions\Action::make('Cancel')
                    ->action(function (Appointment $record) {
                        $record->status = AppointmentStatus::Canceled;
                        $record->save();
                    })
                    ->visible(fn (Appointment $record) => $record->status != AppointmentStatus::Canceled)
                    ->color('danger')
                    ->icon('heroicon-o-x-mark'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAppointments::route('/'),
            'create' => Pages\CreateAppointment::route('/create'),
            'edit' => Pages\EditAppointment::route('/{record}/edit'),
        ];
    }
}
