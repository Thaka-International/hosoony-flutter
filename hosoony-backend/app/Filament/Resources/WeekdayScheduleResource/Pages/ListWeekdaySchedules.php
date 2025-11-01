<?php

namespace App\Filament\Resources\WeekdayScheduleResource\Pages;

use App\Filament\Resources\WeekdayScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListWeekdaySchedules extends ListRecords
{
    protected static string $resource = WeekdayScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
