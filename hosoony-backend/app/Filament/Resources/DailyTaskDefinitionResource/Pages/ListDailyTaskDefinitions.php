<?php

namespace App\Filament\Resources\DailyTaskDefinitionResource\Pages;

use App\Filament\Resources\DailyTaskDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDailyTaskDefinitions extends ListRecords
{
    protected static string $resource = DailyTaskDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
