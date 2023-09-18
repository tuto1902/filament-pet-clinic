<?php

namespace App\Filament\Resources\ScheduleResource\Pages;

use App\Enums\DaysOfTheWeek;
use App\Filament\Resources\ScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class ListSchedules extends ListRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Sunday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Sunday)),
            'Monday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Monday)),
            'Tuesday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Tuesday)),
            'Wednesday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Wednesday)),
            'Thursday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Thursday)),
            'Friday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Friday)),
            'Saturday' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('day_of_week', DaysOfTheWeek::Saturday)),
        ];
    }

    public function getDefaultActiveTab(): string | int | null
    {
        return Carbon::today()->format('l');
    }
}
