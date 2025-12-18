<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomAdResource\Pages;
use App\Filament\Resources\CustomAdResource\RelationManagers;
use App\Models\CustomAd;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomAdResource extends Resource
{
    protected static ?string $model = CustomAd::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 4;

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('title')
                ->required(),
            
            Forms\Components\TextInput::make('video_path')
                ->label('Video Direct URL') // Label ပြောင်းလိုက်တယ်
                ->placeholder('https://example.com/video.mp4')
                ->url() // URL Format ဖြစ်မဖြစ် စစ်မယ်
                ->required()
                ->columnSpanFull(),

            Forms\Components\TextInput::make('duration')
                ->numeric()
                ->suffix('Seconds')
                ->default(15)
                ->required(),

            Forms\Components\TextInput::make('reward')
                ->numeric()
                ->prefix('Coins')
                ->default(20)
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('title'),
            Tables\Columns\TextColumn::make('duration')->suffix('s'),
            Tables\Columns\TextColumn::make('reward')->color('warning'),
            Tables\Columns\IconColumn::make('is_active')->boolean(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCustomAds::route('/'),
            'create' => Pages\CreateCustomAd::route('/create'),
            'edit' => Pages\EditCustomAd::route('/{record}/edit'),
        ];
    }
}
