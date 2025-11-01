<?php

namespace App\Filament\Resources\FeesPlanResource\Pages;

use App\Filament\Resources\FeesPlanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeesPlan extends EditRecord
{
    protected static string $resource = FeesPlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
