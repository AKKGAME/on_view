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
use Filament\Support\Enums\FontWeight;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Collection; // 🔥 Import Collection

class MovieResource extends Resource
{
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $model = Movie::class;
    protected static ?string $navigationIcon = 'heroicon-o-film';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- TOP: TMDB IMPORT ---
                Section::make('TMDB Quick Import')
                    ->description('Search and auto-fill details from TMDB Movies Database.')
                    ->icon('heroicon-o-cloud-arrow-down')
                    ->collapsible()
                    ->schema([
                        Select::make('tmdb_search')
                            ->label('Search Movie')
                            ->searchable()
                            ->preload()
                            ->live(debounce: 500)
                            ->dehydrated(false)
                            ->placeholder('Enter Movie Title or ID...')
                            ->prefixIcon('heroicon-m-magnifying-glass')
                            ->getSearchResultsUsing(function (string $search) {
                                $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
                                if (!$apiKey || strlen($search) < 3) return [];

                                $results = collect();

                                // 1. Search API
                                $response = Http::withoutVerifying()->get('https://api.themoviedb.org/3/search/movie', [
                                    'api_key' => $apiKey, 'query' => $search, 'language' => 'en-US',
                                ]);
                                
                                if ($response->successful()) {
                                    foreach ($response->json('results') as $item) {
                                        $year = substr($item['release_date'] ?? '', 0, 4);
                                        $overview = Str::limit($item['overview'] ?? '', 50);
                                        $results->put($item['id'], "{$item['title']} ($year) - {$overview}");
                                    }
                                }
                                return $results;
                            })
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                if ($state) self::fillMovieDataFromTmdb($set, $state);
                            })
                            ->helperText('Select a movie to auto-fill the details below.'),
                    ]),

                // --- MAIN CONTENT ---
                Forms\Components\Grid::make(3)->schema([
                    // LEFT COLUMN (Main Info) - Takes 2 Columns
                    Group::make()->schema([
                        Section::make('Movie Information')
                            ->icon('heroicon-m-film')
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
                                    ->prefix(url('/movie/')),

                                Textarea::make('description')
                                    ->rows(8)
                                    ->columnSpanFull(),

                                Textarea::make('video_url')
                                    ->label('Video Source URL')
                                    ->placeholder('https://example.com/movie.mp4 or Embed Code')
                                    ->rows(3)
                                    ->hintIcon('heroicon-m-video-camera')
                                    ->required()
                                    ->columnSpanFull()
                                    ->helperText('Direct link (MP4/M3U8) or iframe embed code.'),
                            ])->columns(2),

                        Section::make('Visual Assets')
                            ->icon('heroicon-m-photo')
                            ->schema([
                                TextInput::make('thumbnail_url')
                                    ->label('Poster URL')
                                    ->prefixIcon('heroicon-m-link')
                                    ->placeholder('https://...')
                                    ->required(),

                                TextInput::make('cover_url')
                                    ->label('Backdrop URL')
                                    ->prefixIcon('heroicon-m-link')
                                    ->placeholder('https://...'),
                            ])->columns(2),
                    ])->columnSpan(['lg' => 2]),

                    // RIGHT COLUMN (Meta & Settings) - Takes 1 Column
                    Group::make()->schema([
                        Section::make('Classification')
                            ->icon('heroicon-m-tag')
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

                                // 🔥 Channel Select Box
                                Select::make('channel_id')
                                    ->relationship('channel', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->createOptionForm([
                                        TextInput::make('name')->required()->live()->afterStateUpdated(fn(Set $set, $state) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')->required(),
                                    ])
                                    ->label('Translator/Channel'),

                                TextInput::make('duration')
                                    ->label('Runtime')
                                    ->numeric()
                                    ->suffix('mins')
                                    ->prefixIcon('heroicon-m-clock'),

                                DatePicker::make('release_date')
                                    ->label('Release Date')
                                    ->native(false)
                                    ->suffixIcon('heroicon-m-calendar'),
                            ]),

                        Section::make('Monetization')
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                Toggle::make('is_premium')
                                    ->label('Premium Only')
                                    ->onColor('warning')
                                    ->offColor('gray')
                                    ->onIcon('heroicon-s-star')
                                    ->offIcon('heroicon-o-lock-open')
                                    ->live(),

                                Group::make()->schema([
                                    TextInput::make('coin_price')
                                        ->label('Unlock Price')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('🪙')
                                        ->required(),
                                ])
                                ->visible(fn (Get $get) => $get('is_premium')),

                                TextInput::make('xp_reward')
                                    ->label('XP Reward')
                                    ->numeric()
                                    ->default(10)
                                    ->prefix('★')
                                    ->helperText('XP given after watching.'),
                            ]),

                        Section::make('Visibility & Stats')
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->default(true)
                                    ->onColor('success'),

                                TextInput::make('view_count')
                                    ->label('Total Views')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->prefixIcon('heroicon-m-eye'),
                            ]),
                    ])->columnSpan(['lg' => 1]),
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
                    ->height(90)
                    ->extraImgAttributes(['class' => 'object-cover rounded shadow']),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->limit(30)
                    ->description(fn (Movie $record) => $record->release_date ? $record->release_date->format('Y') : '-')
                    ->wrap(),

                // 🔥 Channel Column
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->icon('heroicon-m-tv')
                    ->color('warning')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('genres.name')
                    ->badge()
                    ->color('primary')
                    ->limitList(2)
                    ->separator(','),

                // 🔥 View Count Column
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->icon('heroicon-m-eye')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->color('gray'),

                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Type')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('coin_price')
                    ->label('Price')
                    ->numeric()
                    ->color('success')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state > 0 ? number_format($state) . ' Coins' : 'Free'),

                Tables\Columns\ToggleColumn::make('is_published')
                    ->label('Visible'),

                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_premium')
                    ->label('Content Type')
                    ->trueLabel('Premium Only')
                    ->falseLabel('Free Content'),
                    
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    // 🔥🔥 BULK ACTION: Replace Video URL Domain 🔥🔥
                    Tables\Actions\BulkAction::make('replace_video_domain')
                        ->label('Replace URL Domain')
                        ->icon('heroicon-o-link')
                        ->color('info')
                        ->form([
                            Forms\Components\TextInput::make('find_string')
                                ->label('Find (Old Domain/Path)')
                                ->placeholder('e.g. https://s3.us-east-005.backblazeb2.com/')
                                ->required(),

                            Forms\Components\TextInput::make('replace_string')
                                ->label('Replace With (New Domain)')
                                ->placeholder('e.g. https://stream.animegabar.com/')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = 0;
                            foreach ($records as $record) {
                                // Only process if video_url exists and contains the search string
                                if ($record->video_url && str_contains($record->video_url, $data['find_string'])) {
                                    $newUrl = str_replace(
                                        $data['find_string'], 
                                        $data['replace_string'], 
                                        $record->video_url
                                    );
                                    
                                    $record->update(['video_url' => $newUrl]);
                                    $count++;
                                }
                            }

                            Notification::make()
                                ->title("Updated {$count} movies successfully!")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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

    // --- HELPER FUNCTION ---
    protected static function fillMovieDataFromTmdb(Set $set, string $tmdbId)
    {
        $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
        if (!$apiKey) {
            Notification::make()->title('API Key Missing')->danger()->send();
            return;
        }

        $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/movie/{$tmdbId}", [
            'api_key' => $apiKey, 'language' => 'en-US',
        ]);

        if ($response->successful()) {
            $data = $response->json();

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

            Notification::make()->title('Movie Data Imported Successfully')->success()->send();
        }
    }
}