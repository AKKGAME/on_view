<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight;

class TopSpenders extends BaseWidget
{
    protected static ?string $heading = 'Top Coin Holders (VIP)';
    protected static ?int $sort = 3; // နံပါတ် ၃
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // Coins အများဆုံး ၅ ယောက်ကို ဆွဲထုတ်မယ်
                User::query()->orderBy('coins', 'desc')->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('User')
                    ->weight(FontWeight::Bold)
                    ->description(fn (User $record): string => $record->phone),

                Tables\Columns\TextColumn::make('rank')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Legend' => 'warning', // Gold Color
                        'Diamond' => 'info',
                        'Gold' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('coins')
                    ->label('Balance')
                    ->numeric()
                    ->sortable()
                    ->alignRight()
                    ->color('primary')
                    ->weight(FontWeight::ExtraBold),
            ])
            ->paginated(false);
    }
}