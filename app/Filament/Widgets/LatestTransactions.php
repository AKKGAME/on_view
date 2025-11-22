<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTransactions extends BaseWidget
{
    protected static ?int $sort = 3; // အောက်ဆုံးမှာထားမယ်
    protected int | string | array $columnSpan = 'full'; // နေရာအပြည့်ယူမယ်

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::query()->latest()->limit(5) // နောက်ဆုံး ၅ ခုပဲပြမယ်
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'topup' => 'success',
                        'purchase' => 'danger', // အနီရောင်ပြမယ် (သုံးလိုက်လို့)
                        'ad_reward' => 'info',
                        'referral_bonus' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->money('mmk')
                    ->color(fn ($record) => $record->type === 'purchase' ? 'danger' : 'success')
                    ->prefix(fn ($record) => $record->type === 'purchase' ? '- ' : '+ '),

                Tables\Columns\TextColumn::make('description')
                    ->limit(30)
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Time')
                    ->since(), // "5 mins ago" ပုံစံပြမယ်
            ])
            ->paginated(false); // Pagination မလိုဘူး
    }
}