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
use Filament\Forms\Components\Group; // Group ထပ်ထည့်ရန်
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class SeasonResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false; // Sidebar မှာမပြဘူး (Anime ထဲကနေပဲဝင်မယ်)
    protected static ?string $model = Season::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // LEFT COLUMN (Main Details)
                Group::make()
                    ->schema([
                        Section::make('Season Details')
                            ->schema([
                                Select::make('anime_id')
                                    ->relationship('anime', 'title') // Anime title ကိုပြမယ်
                                    ->searchable() // စာရိုက်ရှာလို့ရမယ်
                                    ->preload()
                                    ->required()
                                    ->label('Anime Series'),

                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('e.g. Season 1')
                                    ->label('Season Title'),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]), // Desktop မှာ 2/3 နေရာယူမယ်

                // RIGHT COLUMN (Sorting & Meta)
                Group::make()
                    ->schema([
                        Section::make('Ordering')
                            ->schema([
                                TextInput::make('season_number')
                                    ->numeric()
                                    ->default(1)
                                    ->required()
                                    ->label('Season Number')
                                    ->helperText('Used for sorting order.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]), // Desktop မှာ 1/3 နေရာယူမယ်
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Anime နာမည် (Bold & Primary Color)
                Tables\Columns\TextColumn::make('anime.title')
                    ->searchable()
                    ->sortable()
                    ->label('Anime Series')
                    ->weight('bold')
                    ->color('primary'),

                // Season နာမည်
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->label('Season Name')
                    ->description(fn (Season $record): string => "Season {$record->season_number}"),

                // Season Number (Badge)
                Tables\Columns\TextColumn::make('season_number')
                    ->sortable()
                    ->label('#')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                // အပိုင်းအရေအတွက် (Count with Badge)
                Tables\Columns\TextColumn::make('episodes_count')
                    ->counts('episodes')
                    ->label('Episodes')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('anime.title')
            ->filters([
                // Anime အလိုက် Filter စစ်လို့ရအောင်
                Tables\Filters\SelectFilter::make('anime')
                    ->relationship('anime', 'title')
                    ->label('Filter by Anime')
                    ->searchable()
                    ->preload(),
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