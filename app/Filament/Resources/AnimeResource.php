<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimeResource\Pages;
use App\Models\Anime;
use App\Models\Genre;
use App\Models\Setting;
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
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Http;

class AnimeResource extends Resource
{
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $model = Anime::class;
    protected static ?string $navigationIcon = 'heroicon-o-film';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- TOP SECTION: TMDB IMPORT ---
                Section::make('TMDB Quick Import')
                    ->description('Search and auto-fill details from The Movie Database.')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->collapsible()
                    ->schema([
                        Select::make('tmdb_id')
                            ->label('Search Anime')
                            ->searchable()
                            ->preload()
                            ->live(debounce: 500)
                            ->placeholder('Enter Anime Title or TMDB ID...')
                            ->prefixIcon('heroicon-m-magnifying-glass')
                            ->getSearchResultsUsing(function (string $search) {
                                $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                                if (!$apiKey || strlen($search) < 3) return [];

                                $results = collect();

                                // 1. Search by ID if numeric
                                if (is_numeric($search)) {
                                    $idResponse = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$search}", [
                                        'api_key' => $apiKey, 'language' => 'en-US',
                                    ]);
                                    if ($idResponse->successful()) {
                                        $data = $idResponse->json();
                                        $year = substr($data['first_air_date'] ?? '', 0, 4);
                                        $results->put($data['id'], "★ Found ID: " . $data['name'] . " ($year)");
                                    }
                                }

                                // 2. Search by Name
                                $searchResponse = Http::withoutVerifying()->get('https://api.themoviedb.org/3/search/tv', [
                                    'api_key' => $apiKey, 'query' => $search, 'language' => 'en-US',
                                ]);
                                
                                if ($searchResponse->successful()) {
                                    foreach ($searchResponse->json('results') as $item) {
                                        if (!$results->has($item['id'])) {
                                            $year = substr($item['first_air_date'] ?? '', 0, 4);
                                            $overview = Str::limit($item['overview'] ?? '', 50);
                                            $results->put($item['id'], "{$item['name']} ($year) - {$overview}");
                                        }
                                    }
                                }
                                return $results;
                            })
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if (!$state) return;
                                self::fillDataFromTmdb($set, $state);
                            })
                            ->helperText('Select a result to auto-fill the form below.'),
                    ])
                    ->columnSpanFull(),

                // --- MAIN CONTENT ---
                Group::make()
                    ->schema([
                        Section::make('General Information')
                            ->icon('heroicon-m-document-text')
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
                                    ->readOnly()
                                    ->prefix(url('/anime/')),

                                Textarea::make('description')
                                    ->rows(5)
                                    ->maxLength(65535)
                                    ->columnSpanFull(),

                                Grid::make(2)->schema([
                                    Select::make('genres')
                                        ->relationship('genres', 'name')
                                        ->multiple()
                                        ->preload()
                                        ->searchable()
                                        ->createOptionForm([
                                            TextInput::make('name')->required()->live()->afterStateUpdated(fn(Set $set, $state) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->required(),
                                        ]),

                                    // 🔥 Channel Select Box Added Here
                                    Select::make('channel_id')
                                        ->relationship('channel', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')->required()->live()->afterStateUpdated(fn(Set $set, $state) => $set('slug', Str::slug($state))),
                                            TextInput::make('slug')->required(),
                                        ])
                                        ->label('Translator/Channel'),
                                ]),
                            ]),

                        Section::make('Media Assets')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                TextInput::make('thumbnail_url')
                                    ->label('Poster Image URL')
                                    ->prefixIcon('heroicon-m-link')
                                    ->placeholder('https://...')
                                    ->required(),

                                TextInput::make('cover_url')
                                    ->label('Backdrop/Cover URL')
                                    ->prefixIcon('heroicon-m-link')
                                    ->placeholder('https://...'),
                            ])->columns(1),
                    ])
                    ->columnSpan(['lg' => 2]),

                // --- SIDEBAR (STATUS & META) ---
                Group::make()
                    ->schema([
                        Section::make('Status & Meta')
                            ->icon('heroicon-m-adjustments-horizontal')
                            ->schema([
                                Toggle::make('is_completed')
                                    ->label('Completed Status')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-clock')
                                    ->inline(false),

                                Grid::make(2)->schema([
                                    TextInput::make('total_episodes')
                                        ->label('Total Eps')
                                        ->numeric()
                                        ->default(0),
                                    
                                    TextInput::make('view_count')
                                        ->label('Views')
                                        ->numeric()
                                        ->default(0)
                                        ->disabled(),
                                ]),
                                
                                DatePicker::make('created_at')
                                    ->label('Added Date')
                                    ->native(false)
                                    ->disabled(),
                            ]),
                        
                        Section::make('Seasons Management')
                            ->description('Manage seasons and episodes after creating the anime.')
                            ->schema([
                                Forms\Components\Placeholder::make('info')
                                    ->label('Note')
                                    ->content('You can sync seasons/episodes from the list view or manage them manually via the "Seasons" button.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Poster')
                    ->square()
                    ->extraImgAttributes(['class' => 'object-cover rounded-lg shadow-md']) 
                    ->height(80)
                    ->width(80),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (Anime $record) => Str::limit($record->description, 40))
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->icon('heroicon-m-tv')
                    ->color('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('seasons_count')
                    ->counts('seasons')
                    ->label('Seasons')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->icon('heroicon-m-eye')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
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
                    ->label('Status')
                    ->trueLabel('Completed')
                    ->falseLabel('Ongoing'),
                
                Tables\Filters\SelectFilter::make('genres')
                    ->relationship('genres', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('channel')
                    ->relationship('channel', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                // 1. Manage Seasons Button
                Tables\Actions\Action::make('manage_seasons')
                    ->label('Episodes')
                    ->icon('heroicon-m-rectangle-stack')
                    ->color('info')
                    ->url(fn (Anime $record) => AnimeResource::getUrl('seasons', ['record' => $record])),

                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    
                    // 2. Sync TMDB Button
                    Tables\Actions\Action::make('sync_tmdb')
                        ->label('Sync Seasons (TMDB)')
                        ->icon('heroicon-m-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Sync with TMDB')
                        ->modalDescription('This will fetch/update all seasons and episodes from TMDB. This process might take a few seconds.')
                        ->action(function (Anime $record) {
                            self::syncSeasonsFromTmdb($record);
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'seasons' => Pages\ManageAnimeSeasons::route('/{record}/seasons'),
            'episodes' => Pages\ManageAnimeEpisodes::route('/{record}/seasons/{season_id}/episodes'),
        ];
    }

    // --- HELPER FUNCTIONS ---

    protected static function fillDataFromTmdb(Set $set, string $tmdbId)
    {
        $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
        if (!$apiKey) {
            Notification::make()->title('API Key Missing')->danger()->send();
            return;
        }

        $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$tmdbId}", [
            'api_key' => $apiKey, 'language' => 'en-US',
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

            // Genres
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

            Notification::make()->title('Data Imported from TMDB')->success()->send();
        }
    }

    protected static function syncSeasonsFromTmdb(Anime $record)
    {
        $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
        
        if (!$apiKey || !$record->tmdb_id) {
            Notification::make()->title('Missing API Key or TMDB ID')->danger()->send();
            return;
        }

        $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$record->tmdb_id}", [
            'api_key' => $apiKey, 'language' => 'en-US',
        ]);

        if ($response->failed()) {
            Notification::make()->title('Connection Failed')->body('Could not connect to TMDB.')->danger()->send();
            return;
        }

        $data = $response->json();
        $seasons = $data['seasons'] ?? [];
        $count = 0;

        foreach ($seasons as $tmdbSeason) {
            if ($tmdbSeason['season_number'] === 0) continue; 

            $season = $record->seasons()->updateOrCreate(
                ['season_number' => $tmdbSeason['season_number']],
                [
                    'title' => $tmdbSeason['name'],
                    'slug' => Str::slug($record->title . '-season-' . $tmdbSeason['season_number']),
                ]
            );

            // Fetch Episodes
            $seasonRes = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$record->tmdb_id}/season/{$tmdbSeason['season_number']}", [
                'api_key' => $apiKey, 'language' => 'en-US',
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
                            'thumbnail_url' => $ep['still_path'] ? 'https://image.tmdb.org/t/p/original' . $ep['still_path'] : null,
                            'duration' => $ep['runtime'] ?? null,
                            'air_date' => $ep['air_date'] ?? null,
                            'video_url' => '#', 
                            'is_premium' => false, 
                            'coin_price' => 0,
                        ]
                    );
                }
                $count++;
            }
        }

        Notification::make()
            ->title('Sync Completed')
            ->body("Successfully synced {$count} seasons and their episodes.")
            ->success()
            ->send();
    }
}