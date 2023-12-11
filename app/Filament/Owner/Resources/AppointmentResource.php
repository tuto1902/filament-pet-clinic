<?php

namespace App\Filament\Owner\Resources;

use App\Enums\AppointmentStatus;
use App\Filament\Owner\Resources\AppointmentResource\Pages;
use App\Models\Appointment;
use App\Models\Role;
use App\Models\User;
use App\Support\AvatarOptions;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();
        
        return $form
            ->schema([
                Forms\Components\Select::make('pet_id')
                    ->required(),
                Forms\Components\Select::make('clinic_id')
                    ->required(),
                    Forms\Components\DatePicker::make('date')
                    ->required(),
                Forms\Components\Select::make('doctor_id')
                    ->required()
                    ->hidden(fn (Get $get) => blank($get('date')))
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
                    ->helperText(function ($component) {
                        if (! $component->getOptions()) {
                            return new HtmlString(
                                '<span class="text-sm text-danger-600 dark:text-danger-400">No doctors available. Please select a different clinic or date</span>'
                            );
                        }

                        return '';
                    }),
                Forms\Components\Select::make('slot_id')
                    ->required(),
                Forms\Components\TextInput::make('description')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('pet.avatar')
                    ->circular(),
                Tables\Columns\TextColumn::make('pet.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->sortable()
                    ->searchable(),
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
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
