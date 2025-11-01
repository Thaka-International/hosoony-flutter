<?php

namespace App\Filament\Resources\DailyTaskDefinitionResource\Pages;

use App\Filament\Resources\DailyTaskDefinitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyTaskDefinition extends EditRecord
{
    protected static string $resource = DailyTaskDefinitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
