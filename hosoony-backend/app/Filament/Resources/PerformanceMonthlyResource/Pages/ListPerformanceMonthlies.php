<?php

namespace App\Filament\Resources\PerformanceMonthlyResource\Pages;

use App\Filament\Resources\PerformanceMonthlyResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerformanceMonthlies extends ListRecords
{
    protected static string $resource = PerformanceMonthlyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
