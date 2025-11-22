<?php

namespace App\Filament\Resources\CustomAdResource\Pages;

use App\Filament\Resources\CustomAdResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCustomAds extends ListRecords
{
    protected static string $resource = CustomAdResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
