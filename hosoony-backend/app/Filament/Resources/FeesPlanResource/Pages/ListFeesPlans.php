<?php

namespace App\Filament\Resources\FeesPlanResource\Pages;

use App\Filament\Resources\FeesPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeesPlans extends ListRecords
{
    protected static string $resource = FeesPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
