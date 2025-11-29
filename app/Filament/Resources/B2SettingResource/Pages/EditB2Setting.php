<?php

namespace App\Filament\Resources\B2SettingResource\Pages;

use App\Filament\Resources\B2SettingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditB2Setting extends EditRecord
{
    protected static string $resource = B2SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
