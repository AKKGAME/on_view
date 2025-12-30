<?php

namespace App\Filament\Widgets;

use App\Models\Movie;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Support\Enums\FontWeight;
use App\Filament\Resources\MovieResource; // MovieResource ရှိမှ အလုပ်လုပ်ပါမည်

class PopularMovies extends BaseWidget
{
    protected static ?string $heading = '🎬 Blockbuster Movies';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // ရောင်းရတဲ့ အရေအတွက် (users_count) အလိုက်စီမယ်
                Movie::withCount('users') 
                    ->orderBy('users_count', 'desc')
                    ->limit(5)
            )
            ->columns([
                // 1. RANKING (1, 2, 3 Medal)
                Tables\Columns\TextColumn::make('index')
                    ->label('#')
                    ->rowIndex()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        1 => '🥇',
                        2 => '🥈',
                        3 => '🥉',
                        default => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        1 => 'warning', // Gold
                        2 => 'gray',    // Silver
                        3 => 'danger',  // Bronze
                        default => 'gray',
                    })
                    ->weight(FontWeight::Bold)
                    ->alignCenter(),

                // 2. POSTER (Square looks better for movies)
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Poster')
                    ->square() // ရုပ်ရှင် Poster မို့ Square ထားတာ ပိုလှပါတယ်
                    ->size(60)
                    ->defaultImageUrl(url('/images/placeholder.png')), // Poster မရှိရင်ပြရန်

                // 3. MOVIE DETAILS (Title + Year/Rating)
                Tables\Columns\TextColumn::make('title')
                    ->label('Movie')
                    ->weight(FontWeight::Bold)
                    ->description(fn (Movie $record) => $record->release_date ? \Carbon\Carbon::parse($record->release_date)->format('Y') : 'Unknown Year')
                    ->searchable(),

                // 4. SOLD COUNT (Badge Style)
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Sold Units')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-ticket')
                    ->formatStateUsing(fn ($state) => number_format($state) . ' Tickets'),

                // 5. PRICE (Per Unit)
                Tables\Columns\TextColumn::make('coin_price')
                    ->label('Unit Price')
                    ->money('mmk')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true), // ပုံမှန်အားဖြင့် ဖျောက်ထားမယ်

                // 6. TOTAL REVENUE (Sold * Price) -> အသစ်ထည့်ထားသည်
                Tables\Columns\TextColumn::make('total_price')
                    ->label('Total Revenue')
                    ->state(fn (Movie $record) => $record->users_count * $record->coin_price) // တွက်ချက်မှု
                    ->money('mmk')
                    ->weight(FontWeight::ExtraBold)
                    ->color('primary'),
            ])
            ->paginated(false)
            
            // ACTION: နှိပ်လိုက်ရင် Edit Page ရောက်မယ်
            ->actions([
                Tables\Actions\Action::make('open')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->url(fn (Movie $record): string => MovieResource::getUrl('edit', ['record' => $record])),
            ]);
    }
}