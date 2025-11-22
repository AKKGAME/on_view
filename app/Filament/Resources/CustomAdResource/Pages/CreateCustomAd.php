<?php

namespace App\Filament\Resources\CustomAdResource\Pages;

use App\Filament\Resources\CustomAdResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomAd extends CreateRecord
{
    protected static string $resource = CustomAdResource::class;
}
