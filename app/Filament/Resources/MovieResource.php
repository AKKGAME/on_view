<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MovieResource\Pages;
use App\Models\Movie;
use App\Models\Setting;
use App\Models\Genre;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Http;

class MovieResource extends Resource
{
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $model = Movie::class;
    protected static ?string $navigationIcon = 'heroicon-o-film';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. TMDB IMPORT (Movies API)
                Section::make('Quick Import (Movies)')
                    ->description('Search from TMDB Movies Database')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->collapsible()
                    ->schema([
                        Select::make('tmdb_search')
                            ->label('Search Movie')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->dehydrated(false)
                            ->placeholder('Type movie title...')
                            ->getSearchResultsUsing(function (string $search) {
                                $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                                if (!$apiKey || strlen($search) < 3) return [];

                                $results = collect();

                                // Search Movie Endpoint
                                $response = Http::withoutVerifying()->get('https://api.themoviedb.org/3/search/movie', [
                                    'api_key' => $apiKey,
                                    'query' => $search,
                                    'language' => 'en-US',
                                ]);
                                
                                if ($response->successful()) {
                                    foreach ($response->json('results') as $item) {
                                        $year = substr($item['release_date'] ?? '', 0, 4);
                                        $results->put($item['id'], $item['title'] . " ($year)");
                                    }
                                }
                                return $results;
                            })
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (!$state) return;

                                $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                                if (!$apiKey) return;

                                // Get Movie Details
                                $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/movie/{$state}", [
                                    'api_key' => $apiKey,
                                    'language' => 'en-US',
                                ]);

                                if ($response->successful()) {
                                    $data = $response->json();

                                    $set('tmdb_id', $data['id']);
                                    $set('title', $data['title']);
                                    $set('slug', Str::slug($data['title']));
                                    $set('description', $data['overview']);
                                    $set('duration', $data['runtime']);
                                    $set('release_date', $data['release_date']);

                                    if (!empty($data['poster_path'])) {
                                        $set('thumbnail_url', 'https://image.tmdb.org/t/p/original' . $data['poster_path']);
                                    }

                                    if (!empty($data['backdrop_path'])) {
                                        $set('cover_url', 'https://image.tmdb.org/t/p/original' . $data['backdrop_path']);
                                    }

                                    // Auto Genres
                                    if (!empty($data['genres'])) {
                                        $genreIds = [];
                                        foreach ($data['genres'] as $tmdbGenre) {
                                            $genre = Genre::firstOrCreate(
                                                ['name' => $tmdbGenre['name']],
                                                ['slug' => Str::slug($tmdbGenre['name'])]
                                            );
                                            $genreIds[] = $genre->id;
                                        }
                                        $set('genres', $genreIds);
                                    }

                                    Notification::make()->title('Movie Data Imported!')->success()->send();
                                }
                            }),
                    ]),

                // 2. MAIN FORM
                Grid::make(3)->schema([
                    // LEFT COLUMN (2/3)
                    Group::make()->schema([
                        Section::make('Movie Details')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->readOnly(),

                                Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                                
                                // Video URL (Movies have direct link)
                                Textarea::make('video_url')
                                    ->label('Video Source')
                                    ->placeholder('Direct URL or Iframe Embed Code')
                                    ->rows(3)
                                    ->columnSpanFull()
                                    ->required(),
                            ]),

                        Section::make('Media Assets')
                            ->schema([
                                TextInput::make('thumbnail_url')
                                    ->label('Poster URL')
                                    ->prefixIcon('heroicon-o-photo')
                                    ->required(),

                                TextInput::make('cover_url')
                                    ->label('Backdrop URL')
                                    ->prefixIcon('heroicon-o-photo'),
                            ])->columns(2),
                    ])->columnSpan(2),

                    // RIGHT COLUMN (1/3)
                    Group::make()->schema([
                        Section::make('Classification')
                            ->schema([
                                Select::make('genres')
                                    ->relationship('genres', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->required(),

                                TextInput::make('duration')
                                    ->label('Runtime (mins)')
                                    ->numeric(),

                                DatePicker::make('release_date')
                                    ->native(false),
                            ]),

                        Section::make('Monetization')
                            ->schema([
                                Toggle::make('is_premium')
                                    ->label('Premium Content')
                                    ->onColor('success')
                                    ->live(),

                                TextInput::make('coin_price')
                                    ->label('Unlock Price')
                                    ->numeric()
                                    ->default(0)
                                    ->prefixIcon('heroicon-o-currency-dollar')
                                    ->hidden(fn (Get $get) => !$get('is_premium'))
                                    ->required(fn (Get $get) => $get('is_premium')),

                                TextInput::make('xp_reward')
                                    ->numeric()
                                    ->default(10)
                                    ->label('XP Reward'),
                            ]),

                        Section::make('Visibility')
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->default(true)
                                    ->onColor('primary'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Poster')
                    ->width(60)
                    ->height(90),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30)
                    ->description(fn (Movie $record) => $record->release_date ? $record->release_date->format('Y') : '-'),

                Tables\Columns\TextColumn::make('genres.name')
                    ->badge()
                    ->color('primary')
                    ->limitList(2),

                Tables\Columns\ToggleColumn::make('is_premium')
                    ->label('Premium'),

                Tables\Columns\TextColumn::make('coin_price')
                    ->numeric()
                    ->prefix('🪙 ')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Visible')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_premium'),
                Tables\Filters\SelectFilter::make('genres')
                    ->relationship('genres', 'name')
                    ->multiple(),
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
            'index' => Pages\ListMovies::route('/'),
            'create' => Pages\CreateMovie::route('/create'),
            'edit' => Pages\EditMovie::route('/{record}/edit'),
        ];
    }
}