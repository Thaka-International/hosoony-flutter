<?php

namespace App\Filament\Resources\CompanionsPublicationResource\Pages;

use App\Filament\Resources\CompanionsPublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCompanionsPublication extends EditRecord
{
    protected static string $resource = CompanionsPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
