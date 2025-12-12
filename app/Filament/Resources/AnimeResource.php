<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimeResource\Pages;
use App\Models\Anime;
use App\Models\Setting;
use App\Models\Genre;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
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
use Filament\Forms\Components\FileUpload; // Image Upload
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Http;

class AnimeResource extends Resource
{
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $model = Anime::class;
    protected static ?string $navigationIcon = 'heroicon-o-film';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. TMDB IMPORT (Top Full Width)
                Section::make('Quick Import')
                    ->description('Auto-fill details from TMDB Database')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->collapsible()
                    ->schema([
                        Select::make('tmdb_id')
                            ->label('Search TMDB (Title or ID)')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->placeholder('Search anime...')
                            ->getSearchResultsUsing(function (string $search) {
                                $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                                if (!$apiKey || strlen($search) < 3) return [];

                                $results = collect();

                                // Search by ID
                                if (is_numeric($search)) {
                                    $idResponse = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$search}", [
                                        'api_key' => $apiKey,
                                        'language' => 'en-US',
                                    ]);

                                    if ($idResponse->successful()) {
                                        $data = $idResponse->json();
                                        $year = substr($data['first_air_date'] ?? '', 0, 4);
                                        $results->put($data['id'], "★ ID MATCH: " . $data['name'] . " ($year)");
                                    }
                                }

                                // Search by Name
                                $searchResponse = Http::withoutVerifying()->get('https://api.themoviedb.org/3/search/tv', [
                                    'api_key' => $apiKey,
                                    'query' => $search,
                                    'language' => 'en-US',
                                ]);
                                
                                if ($searchResponse->successful()) {
                                    foreach ($searchResponse->json('results') as $item) {
                                        if (!$results->has($item['id'])) {
                                            $year = substr($item['first_air_date'] ?? '', 0, 4);
                                            $results->put($item['id'], $item['name'] . " ($year)");
                                        }
                                    }
                                }

                                return $results;
                            })
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (!$state) return;

                                $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                                if (!$apiKey) return;

                                $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$state}", [
                                    'api_key' => $apiKey,
                                    'language' => 'en-US',
                                ]);

                                if ($response->successful()) {
                                    $data = $response->json();

                                    $set('title', $data['name']);
                                    $set('slug', Str::slug($data['name']));
                                    $set('description', $data['overview']);
                                    $set('total_episodes', $data['number_of_episodes'] ?? 0);
                                    $set('is_completed', in_array($data['status'], ['Ended', 'Canceled']));

                                    if (!empty($data['poster_path'])) {
                                        $set('thumbnail_url', 'https://image.tmdb.org/t/p/original' . $data['poster_path']);
                                    }

                                    if (!empty($data['backdrop_path'])) {
                                        $set('cover_url', 'https://image.tmdb.org/t/p/original' . $data['backdrop_path']);
                                    }

                                    // Auto Create & Select Genres
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

                                    Notification::make()->title('Data Imported Successfully')->success()->send();
                                }
                            }),
                    ]),

                // 2. MAIN CONTENT (Split Layout)
                Grid::make(3)->schema([
                    // LEFT SIDE (Info & Media)
                    Group::make()->schema([
                        Section::make('Anime Details')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->readOnly(),

                                Textarea::make('description')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Media Assets')
                            ->description('Use direct image URLs (faster) or upload manually.')
                            ->schema([
                                TextInput::make('thumbnail_url')
                                    ->label('Poster URL')
                                    ->prefixIcon('heroicon-o-photo')
                                    ->required(),

                                TextInput::make('cover_url')
                                    ->label('Cover URL')
                                    ->prefixIcon('heroicon-o-photo'),
                            ])->columns(2),
                    ])->columnSpan(2),

                    // RIGHT SIDE (Meta & Status)
                    Group::make()->schema([
                        Section::make('Classification')
                            ->schema([
                                Select::make('genres')
                                    ->relationship('genres', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('name')->required(),
                                        TextInput::make('slug')->required(),
                                    ])
                                    ->required(),

                                TextInput::make('total_episodes')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('#'),
                            ]),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_completed')
                                    ->label('Completed?')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(false),
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
                    ->square()
                    ->height(80)
                    ->width(80)
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(40)
                    ->wrap(),

                Tables\Columns\TextColumn::make('genres.name')
                    ->badge()
                    ->color('primary')
                    ->limitList(2)
                    ->separator(','),

                Tables\Columns\TextColumn::make('seasons_count')
                    ->counts('seasons')
                    ->label('Seasons')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Completion Status'),
                
                Tables\Filters\SelectFilter::make('genres')
                    ->relationship('genres', 'name')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                // MAIN ACTION: Manage Seasons
                Tables\Actions\Action::make('manage_seasons')
                    ->label('Seasons')
                    ->icon('heroicon-o-rectangle-stack')
                    ->color('info')
                    ->button()
                    ->url(fn (Anime $record) => AnimeResource::getUrl('seasons', ['record' => $record])),

                // DROPDOWN ACTIONS
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    // Smart Sync TMDB
                    Tables\Actions\Action::make('fetch_seasons')
                        ->label('Sync TMDB')
                        ->icon('heroicon-o-cloud-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Sync Seasons & Episodes')
                        ->modalDescription('This will fetch all seasons and episodes from TMDB. Existing data will be updated.')
                        ->action(function (Anime $record) {
                            $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                            
                            if (!$apiKey || !$record->tmdb_id) {
                                Notification::make()->title('Missing API Key or ID')->danger()->send();
                                return;
                            }

                            // Fetch Seasons
                            $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$record->tmdb_id}", [
                                'api_key' => $apiKey,
                                'language' => 'en-US',
                            ]);

                            if ($response->failed()) {
                                Notification::make()->title('TMDB Connection Failed')->danger()->send();
                                return;
                            }

                            $data = $response->json();
                            $seasons = $data['seasons'] ?? [];

                            foreach ($seasons as $tmdbSeason) {
                                if ($tmdbSeason['season_number'] === 0) continue; // Skip Specials

                                $season = $record->seasons()->updateOrCreate(
                                    ['season_number' => $tmdbSeason['season_number']],
                                    [
                                        'title' => $tmdbSeason['name'],
                                        'slug' => Str::slug($record->title . '-season-' . $tmdbSeason['season_number']),
                                    ]
                                );

                                // Fetch Episodes for this Season
                                $seasonRes = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$record->tmdb_id}/season/{$tmdbSeason['season_number']}", [
                                    'api_key' => $apiKey,
                                    'language' => 'en-US',
                                ]);

                                if ($seasonRes->successful()) {
                                    $episodes = $seasonRes->json('episodes') ?? [];
                                    
                                    foreach ($episodes as $ep) {
                                        $season->episodes()->updateOrCreate(
                                            ['episode_number' => $ep['episode_number']],
                                            [
                                                'title' => $ep['name'],
                                                'slug' => Str::slug($record->title . '-s' . $tmdbSeason['season_number'] . '-ep' . $ep['episode_number']),
                                                'overview' => $ep['overview'],
                                                'thumbnail_url' => $ep['still_path'] 
                                                    ? 'https://image.tmdb.org/t/p/original' . $ep['still_path'] 
                                                    : null,
                                                
                                                // ✅ FIX: Added runtime & air_date
                                                'duration' => $ep['runtime'] ?? null,
                                                'air_date' => $ep['air_date'] ?? null,

                                                'video_url' => '#', 
                                                'is_premium' => false, 
                                                'coin_price' => 0,
                                            ]
                                        );
                                    }
                                }
                            }

                            Notification::make()->title('Sync Completed!')->success()->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical')
                ->tooltip('More Options'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnimes::route('/'),
            'create' => Pages\CreateAnime::route('/create'),
            'edit' => Pages\EditAnime::route('/{record}/edit'),
            
            // Custom Pages for Season/Episode Management
            'seasons' => Pages\ManageAnimeSeasons::route('/{record}/seasons'),
            'episodes' => Pages\ManageAnimeEpisodes::route('/{record}/seasons/{season_id}/episodes'),
        ];
    }
}