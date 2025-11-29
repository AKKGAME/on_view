<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimeResource\Pages;
use App\Filament\Resources\AnimeResource\RelationManagers\SeasonsRelationManager;
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
                // 1. TMDB IMPORT SECTION
                Section::make('Import from TMDB')
                    ->description('Search via Title OR TMDB ID to auto-fill data.')
                    ->schema([
                        Select::make('tmdb_id')
                            ->label('Search Anime (Title or ID)')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->placeholder('Type title or ID (e.g., 60625)...')
                            ->getSearchResultsUsing(function (string $search) {
                                $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                                if (!$apiKey) return [];

                                $results = collect();

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

                                $data = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$state}", [
                                    'api_key' => $apiKey,
                                    'language' => 'en-US',
                                ])->json();

                                $set('title', $data['name']);
                                $set('slug', Str::slug($data['name']));
                                $set('description', $data['overview']);
                                $set('total_episodes', $data['number_of_episodes'] ?? 0);
                                $set('is_completed', ($data['status'] === 'Ended' || $data['status'] === 'Canceled'));

                                if (!empty($data['poster_path'])) {
                                    $set('thumbnail_url', 'https://image.tmdb.org/t/p/original' . $data['poster_path']);
                                }

                                if (!empty($data['backdrop_path'])) {
                                    $set('cover_url', 'https://image.tmdb.org/t/p/original' . $data['backdrop_path']);
                                }

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
                            }),
                    ])
                    ->columnSpanFull(), 

                // 2. LEFT COLUMN
                Group::make()
                    ->schema([
                        Section::make('General Information')
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
                                    ->rows(5)
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Media (URL Only)')
                            ->schema([
                                TextInput::make('thumbnail_url')
                                    ->label('Poster Image URL')
                                    ->url()
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('cover_url')
                                    ->label('Cover Image URL')
                                    ->url()
                                    ->columnSpan(1),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                // 3. RIGHT COLUMN
                Group::make()
                    ->schema([
                        Section::make('Classification')
                            ->schema([
                                Select::make('genres')
                                    ->relationship('genres', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, $state) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')->required(),
                                    ])
                                    ->required(),

                                TextInput::make('total_episodes')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Episode Count'),
                            ]),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_completed')
                                    ->label('Completed Series?')
                                    ->onColor('success')
                                    ->offColor('gray'),
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
                    ->width(50)
                    ->height(75)
                    ->extraImgAttributes([
                        'class' => 'object-cover rounded-md',
                        'style' => 'aspect-ratio: 2/3;',
                    ]),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30)
                    ->description(fn (Anime $record) => Str::limit($record->description, 40)),

                Tables\Columns\TextColumn::make('genres.name')
                    ->badge()
                    ->color('primary')
                    ->limitList(2),

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
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
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
                // 1. PRIMARY ACTION: MANAGE SEASONS (Button Style)
                Tables\Actions\Action::make('manage_seasons')
                    ->label('Seasons')
                    ->icon('heroicon-o-rectangle-stack')
                    ->color('info')
                    ->button() // Button အဖြစ်ပြမည်
                    ->url(fn (Anime $record) => AnimeResource::getUrl('seasons', ['record' => $record])),

                // 2. SECONDARY ACTIONS (Grouped in Dropdown)
                Tables\Actions\ActionGroup::make([
                    
                    // View Action
                    Tables\Actions\EditAction::make(),

                    // TMDB Fetch Action
                    Tables\Actions\Action::make('fetch_seasons')
                        ->label('Sync TMDB')
                        ->icon('heroicon-o-cloud-arrow-down')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Import Seasons & Episodes')
                        ->modalDescription('This process may take a few minutes. Please do not close the window.')
                        ->action(function (Anime $record) {
                            
                            set_time_limit(300); 
                            ini_set('memory_limit', '512M');

                            $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                            
                            if (!$apiKey || !$record->tmdb_id) {
                                Notification::make()->title('Error: Missing API Key or TMDB ID')->danger()->send();
                                return;
                            }

                            $response = Http::withoutVerifying()
                                ->timeout(60)
                                ->get("https://api.themoviedb.org/3/tv/{$record->tmdb_id}", [
                                    'api_key' => $apiKey,
                                    'language' => 'en-US',
                                ]);

                            if ($response->failed()) {
                                Notification::make()->title('TMDB Connection Failed')->danger()->send();
                                return;
                            }

                            $data = $response->json();
                            
                            if (empty($data['seasons'])) {
                                Notification::make()->title('No Seasons Found')->warning()->send();
                                return;
                            }

                            foreach ($data['seasons'] as $tmdbSeason) {
                                if ($tmdbSeason['season_number'] === 0) continue; 

                                $season = $record->seasons()->updateOrCreate(
                                    ['season_number' => $tmdbSeason['season_number']],
                                    [
                                        'title' => $tmdbSeason['name'],
                                        'slug' => Str::slug($record->title . '-season-' . $tmdbSeason['season_number']),
                                    ]
                                );

                                $seasonResponse = Http::withoutVerifying()
                                    ->timeout(60)
                                    ->get("https://api.themoviedb.org/3/tv/{$record->tmdb_id}/season/{$tmdbSeason['season_number']}", [
                                        'api_key' => $apiKey,
                                        'language' => 'en-US',
                                    ]);

                                if ($seasonResponse->successful()) {
                                    $seasonData = $seasonResponse->json();
                                    
                                    foreach ($seasonData['episodes'] as $tmdbEpisode) {
                                        $season->episodes()->updateOrCreate(
                                            ['episode_number' => $tmdbEpisode['episode_number']],
                                            [
                                                'title' => $tmdbEpisode['name'],
                                                'slug' => Str::slug($record->title . '-s' . $tmdbSeason['season_number'] . '-ep' . $tmdbEpisode['episode_number']),
                                                'overview' => $tmdbEpisode['overview'],
                                                'thumbnail_url' => $tmdbEpisode['still_path'] 
                                                    ? 'https://image.tmdb.org/t/p/original' . $tmdbEpisode['still_path'] 
                                                    : null,
                                                'video_url' => '#', 
                                                'is_premium' => false, 
                                                'coin_price' => 0,
                                                'xp_reward' => 10,
                                            ]
                                        );
                                    }
                                }
                                sleep(1);
                            }

                            Notification::make()->title('Import Successful!')->success()->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ])
                ->icon('heroicon-m-ellipsis-vertical') // 3 dots icon
                ->color('gray')
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
        return [
            // SeasonsRelationManager::class, // Custom Page သုံးတဲ့အတွက် ဒါကို ပိတ်ထားလို့ရပါတယ်
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnimes::route('/'),
            'create' => Pages\CreateAnime::route('/create'),
            'edit' => Pages\EditAnime::route('/{record}/edit'),
            
            // Custom Pages
            'seasons' => Pages\ManageAnimeSeasons::route('/{record}/seasons'),
            'episodes' => Pages\ManageAnimeEpisodes::route('/{record}/seasons/{season_id}/episodes'),
        ];
    }
}