<?php

namespace App\Filament\Resources\AnimeRequestResource\Pages;

use App\Filament\Resources\AnimeRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnimeRequest extends EditRecord
{
    protected static string $resource = AnimeRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
