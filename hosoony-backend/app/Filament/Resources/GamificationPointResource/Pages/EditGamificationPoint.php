<?php

namespace App\Filament\Resources\GamificationPointResource\Pages;

use App\Filament\Resources\GamificationPointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGamificationPoint extends EditRecord
{
    protected static string $resource = GamificationPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
