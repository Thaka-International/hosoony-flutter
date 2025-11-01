<?php

namespace App\Filament\Resources\PerformanceMonthlyResource\Pages;

use App\Filament\Resources\PerformanceMonthlyResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerformanceMonthly extends EditRecord
{
    protected static string $resource = PerformanceMonthlyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
