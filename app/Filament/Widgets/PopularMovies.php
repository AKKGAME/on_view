<?php

namespace App\Filament\Widgets;

use App\Models\Movie;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PopularMovies extends BaseWidget
{
    protected static ?string $heading = 'Best Selling Movies';
    protected static ?int $sort = 4; // နံပါတ် ၄
    protected int | string | array $columnSpan = 'full'; // နေရာအပြည့် (သို့) 1 ထားလို့လဲရပါတယ်

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // ဝယ်တဲ့လူအရေအတွက် (users_count) များတဲ့အလိုက် စီမယ်
                Movie::withCount('users') 
                    ->orderBy('users_count', 'desc')
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('poster') // Poster ရှိရင်ပြမယ်
                    ->circular(),

                Tables\Columns\TextColumn::make('title')
                    ->label('Movie Title')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->title)
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('price')
                    ->money('mmk')
                    ->color('gray'),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Sold')
                    ->badge()
                    ->color('success')
                    ->icon('heroicon-m-shopping-cart'),
            ])
            ->paginated(false)
            ->emptyStateHeading('No sales yet');
    }
}