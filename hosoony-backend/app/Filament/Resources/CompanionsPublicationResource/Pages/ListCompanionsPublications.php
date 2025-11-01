<?php

namespace App\Filament\Resources\CompanionsPublicationResource\Pages;

use App\Filament\Resources\CompanionsPublicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCompanionsPublications extends ListRecords
{
    protected static string $resource = CompanionsPublicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
