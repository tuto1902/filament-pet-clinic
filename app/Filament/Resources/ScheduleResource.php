<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ScheduleResource\Pages;
use App\Filament\Resources\ScheduleResource\RelationManagers;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\Slot;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ScheduleResource extends Resource
{
    protected static ?string $model = Schedule::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $doctorRole = Role::whereName('doctor')->first();
        return $form
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\DatePicker::make('date')
                        ->native(false)
                        ->required(),
                    Forms\Components\Select::make('owner_id')
                        ->native(false)
                        ->options(
                            Filament::getTenant()
                                ->users()
                                ->whereBelongsTo($doctorRole)
                                ->get()
                                ->pluck('name', 'id')
                        )
                        ->required(),
                    Forms\Components\Repeater::make('slots')
                        ->relationship()
                        ->schema([
                            Forms\Components\TimePicker::make('start')
                                ->seconds(false)
                                ->required(),
                            Forms\Components\TimePicker::make('end')
                                ->seconds(false)
                                ->required()
                        ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->groups([
                Tables\Grouping\Group::make('date')
                    ->collapsible()
                    ->getTitleFromRecordUsing(fn (Schedule $record) => $record->date->format('M d, Y'))
            ])
            ->defaultGroup('date')
            ->groupsInDropdownOnDesktop()
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('owner.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slots')
                    ->badge()
                    ->formatStateUsing(fn (Slot $state) => $state->start->format('h:i A') . ' - ' . $state->end->format('h:i A')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn (Schedule $record) => $record->slots()->delete())
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
            'index' => Pages\ListSchedules::route('/'),
            'create' => Pages\CreateSchedule::route('/create'),
            'edit' => Pages\EditSchedule::route('/{record}/edit'),
        ];
    }    
}
