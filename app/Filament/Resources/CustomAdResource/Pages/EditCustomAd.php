<?php

namespace App\Filament\Resources\CustomAdResource\Pages;

use App\Filament\Resources\CustomAdResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomAd extends EditRecord
{
    protected static string $resource = CustomAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
