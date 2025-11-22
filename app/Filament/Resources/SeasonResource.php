<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeasonResource\Pages;
use App\Filament\Resources\SeasonResource\RelationManagers;
use App\Models\Season;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class SeasonResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Season::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Season Information')
                    ->schema([
                        // Anime ရွေးမယ့် Dropdown
                        Select::make('anime_id')
                            ->relationship('anime', 'title') // Anime title ကိုပြမယ်
                            ->searchable() // စာရိုက်ရှာလို့ရမယ်
                            ->preload()
                            ->required()
                            ->label('Select Anime'),

                        // Season နာမည် (ဥပမာ - Season 1, Swordsmith Village Arc)
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Season 1')
                            ->label('Season Title'),

                        // အမှတ်စဉ် (စီရလွယ်အောင်)
                        TextInput::make('season_number')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->label('Season Number'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Anime နာမည်
                Tables\Columns\TextColumn::make('anime.title')
                    ->searchable()
                    ->sortable()
                    ->label('Anime Series')
                    ->weight('bold'), // စာလုံးအထူပြမယ်

                // Season နာမည်
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Season Name'),

                // အပိုင်းအရေအတွက် (Count)
                Tables\Columns\TextColumn::make('episodes_count')
                    ->counts('episodes') // Episode ဘယ်နှပိုင်းရှိလဲ ရေတွက်ပြမယ်
                    ->label('Episodes')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('season_number')
                    ->sortable()
                    ->label('#'),
            ])
            ->defaultSort('anime.title') // Default Anime နာမည်နဲ့စီမယ်
            ->filters([
                // Anime အလိုက် Filter စစ်လို့ရအောင်
                Tables\Filters\SelectFilter::make('anime')
                    ->relationship('anime', 'title')
                    ->label('Filter by Anime'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EpisodesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeasons::route('/'),
            'create' => Pages\CreateSeason::route('/create'),
            'edit' => Pages\EditSeason::route('/{record}/edit'),
        ];
    }
}