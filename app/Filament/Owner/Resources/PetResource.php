<?php

namespace App\Filament\Owner\Resources;

use App\Enums\PetType;
use App\Filament\Owner\Resources\PetResource\Pages;
use App\Filament\Owner\Resources\PetResource\RelationManagers;
use App\Models\Pet;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PetResource extends Resource
{
    protected static ?string $model = Pet::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('avatar')
                    ->image()
                    ->imageEditor(),
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\DatePicker::make('date_of_birth')
                   ->required(),
                Forms\Components\Select::make('type')
                    ->options(PetType::class)
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        $owner = Filament::auth()->user();
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->owner($owner))
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->date(config('app.date_format')),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
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
            'index' => Pages\ListPets::route('/'),
            'create' => Pages\CreatePet::route('/create'),
            'edit' => Pages\EditPet::route('/{record}/edit'),
        ];
    }    
}
