<?php

namespace App\Filament\Resources\CoinCouponResource\Pages;

use App\Filament\Resources\CoinCouponResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCoinCoupons extends ListRecords
{
    protected static string $resource = CoinCouponResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
