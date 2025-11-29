<?php

namespace App\Filament\Resources\B2SettingResource\Pages;

use App\Filament\Resources\B2SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListB2Settings extends ListRecords
{
    protected static string $resource = B2SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
