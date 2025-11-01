<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('تعديل')
                ->icon('heroicon-o-pencil'),
            Actions\DeleteAction::make()
                ->label('حذف')
                ->icon('heroicon-o-trash'),
        ];
    }
}
