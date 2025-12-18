<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight; // Font Weight အတွက်
use Filament\Tables\Columns\TextColumn;

class LatestTransactions extends BaseWidget
{
    protected static ?int $sort = 5; // နံပါတ် ၅ (အောက်ဆုံး)
    protected int | string | array $columnSpan = 'full'; // နေရာအပြည့်

    // Widget ခေါင်းစဉ်
    protected static ?string $heading = 'Recent Transactions';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::with('user')->latest()->limit(5) 
            )
            // ဇယားကို အစင်းကြားလေးတွေနဲ့ ပြမယ်
            ->striped() 
            
            // "View All" ခလုတ်ထည့်မယ် (Transaction Resource ရှိရင် Route ချိတ်ပါ)
            ->headerActions([
                Tables\Actions\Action::make('view_all')
                    ->label('View All')
                    ->link()
                    ->icon('heroicon-m-arrow-right')
                    ->url(fn () => route('filament.admin.resources.transactions.index')), // Route ရှိရင်ဖွင့်ပါ
            ])
            
            ->columns([
                // 1. User Info (Name + Phone)
                TextColumn::make('user.name')
                    ->label('User')
                    ->icon('heroicon-m-user-circle') // Icon လေးထည့်မယ်
                    ->iconColor('primary')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Transaction $record): string => $record->user->phone ?? 'No Phone') // ဖုန်းနံပါတ်ပါ တွဲပြမယ်
                    ->searchable(),

                // 2. Transaction Type (Badges)
                TextColumn::make('type')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'topup' => 'heroicon-m-arrow-trending-up',
                        'purchase', 'movie_purchase', 'subscription' => 'heroicon-m-arrow-trending-down',
                        'ad_reward' => 'heroicon-m-gift',
                        'referral_bonus' => 'heroicon-m-users',
                        default => 'heroicon-m-sparkles',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'topup' => 'success',
                        'purchase', 'movie_purchase', 'subscription' => 'danger',
                        'ad_reward' => 'info',
                        'referral_bonus' => 'warning', // Referral ကို warning color (yellow/orange)
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                // 3. Description (Short text)
                TextColumn::make('description')
                    ->limit(25)
                    ->color('gray')
                    ->tooltip(fn (TextColumn $column): ?string => $column->getState()),

                // 4. Amount (Right Aligned & Bold)
                TextColumn::make('amount')
                    ->money('mmk')
                    ->alignRight() // ဂဏန်းမို့ ညာကပ်မယ်
                    ->weight(FontWeight::ExtraBold) // စာလုံးအမည်း
                    ->color(fn ($record) => in_array($record->type, ['purchase', 'movie_purchase', 'subscription']) ? 'danger' : 'success'),

                // 5. Time (Since + Tooltip Date)
                TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime()
                    ->formatStateUsing(fn ($state) => $state->diffForHumans()) // "2 mins ago"
                    ->tooltip(fn (TextColumn $column) => $column->getState()->format('d M Y, h:i A')) // Tooltip: "12 Dec 2024, 10:30 PM"
                    ->alignRight()
                    ->color('gray'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No recent transactions'); // Data မရှိရင်ပြမည့်စာ
    }
}