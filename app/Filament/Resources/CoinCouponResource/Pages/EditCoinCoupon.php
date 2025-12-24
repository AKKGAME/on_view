<?php

namespace App\Filament\Resources\CoinCouponResource\Pages;

use App\Filament\Resources\CoinCouponResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCoinCoupon extends EditRecord
{
    protected static string $resource = CoinCouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
