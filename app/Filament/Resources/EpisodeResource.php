<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EpisodeResource\Pages;
use App\Models\Episode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get; // <--- ဒီကောင်လေး လိုနေတာပါ

class EpisodeResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Episode::class;

    protected static ?string $navigationIcon = 'heroicon-o-play-circle';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Episode Info')
                    ->schema([
                        // Season ရွေးခိုင်းမယ်
                        Select::make('season_id')
                            ->relationship('season', 'title')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('title')
                            ->label('Episode Title')
                            ->required(),

                        TextInput::make('episode_number')
                            ->numeric()
                            ->required(),
                            
                        TextInput::make('video_url')
                            ->url()
                            ->required()
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Gaming & Premium Logic')
                    ->schema([
                        Toggle::make('is_premium')
                            ->label('Premium Episode?')
                            ->onColor('success')
                            ->offColor('danger')
                            ->live(), // Toggle နှိပ်တာနဲ့ အောက်က coin_price ပေါ်/ပျောက် ဖြစ်ဖို့ live() လိုပါတယ်

                        TextInput::make('coin_price')
                            ->numeric()
                            ->default(0)
                            ->prefix('Coins')
                            // ဒီနေရာမှာ Error တက်နေတာပါ (Get class မသိလို့)
                            ->hidden(fn (Get $get) => !$get('is_premium')), 

                        TextInput::make('xp_reward')
                            ->numeric()
                            ->default(10)
                            ->label('XP Reward'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('season.anime.title')
                    ->label('Anime')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('season.title')
                    ->label('Season'),

                Tables\Columns\TextColumn::make('episode_number')
                    ->label('Ep #')
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean(),

                Tables\Columns\TextColumn::make('coin_price')
                    ->numeric()
                    ->label('Price'),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEpisodes::route('/'),
            'create' => Pages\CreateEpisode::route('/create'),
            'edit' => Pages\EditEpisode::route('/{record}/edit'),
        ];
    }
}