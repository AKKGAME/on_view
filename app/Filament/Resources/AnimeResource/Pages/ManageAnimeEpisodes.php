<?php

namespace App\Filament\Resources\AnimeResource\Pages;

use App\Filament\Resources\AnimeResource;
use App\Models\Episode;
use App\Models\Season;
use App\Models\Setting;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class ManageAnimeEpisodes extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = AnimeResource::class;
    protected static string $view = 'filament.resources.anime-resource.pages.manage-anime-episodes';

    public $season_id;
    public $season;

    public function mount(int | string $record, int | string $season_id): void
    {
        $this->record = $this->resolveRecord($record);
        $this->season_id = $season_id;
        $this->season = Season::findOrFail($season_id);
    }

    public function getTitle(): string|Htmlable
    {
        return "{$this->season->title} - Episodes";
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_seasons')
                ->label('Back')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(AnimeResource::getUrl('seasons', ['record' => $this->record])),

            // 🔥 1. FETCH SINGLE EPISODE
            Action::make('fetch_single_episode')
                ->label('Fetch Single')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
                ->form([
                    Forms\Components\TextInput::make('episode_number')
                        ->label('Episode Number')
                        ->numeric()
                        ->required()
                        ->default(function () {
                            $lastEp = Episode::where('season_id', $this->season_id)->max('episode_number');
                            return $lastEp ? $lastEp + 1 : 1;
                        }),
                ])
                ->action(function (array $data) {
                    $this->fetchAndCreateEpisode($data['episode_number']);
                }),

            // 🔥🔥🔥 2. NEW: FETCH RANGE OF EPISODES 🔥🔥🔥
            Action::make('fetch_range_episodes')
                ->label('Fetch Range')
                ->icon('heroicon-o-list-bullet')
                ->color('primary')
                ->form([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('start_episode')
                            ->label('Start Episode')
                            ->numeric()
                            ->required()
                            ->default(1),
                        
                        Forms\Components\TextInput::make('end_episode')
                            ->label('End Episode')
                            ->numeric()
                            ->required()
                            ->default(12)
                            ->gt('start_episode'), // Must be greater than start
                    ]),
                ])
                ->action(function (array $data) {
                    $this->fetchEpisodeRange($data['start_episode'], $data['end_episode']);
                }),

            // 🔥 3. FETCH ALL EPISODES
            Action::make('fetch_season_episodes')
                ->label('Fetch All')
                ->icon('heroicon-o-arrow-path-rounded-square')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Fetch Entire Season?')
                ->modalDescription('This will fetch ALL episodes for this season. Existing episodes will be skipped.')
                ->action(fn () => $this->fetchSeasonEpisodes()),
        ];
    }

    // 🔥 Function to Fetch Range of Episodes (e.g., 10 to 20)
    protected function fetchEpisodeRange($start, $end)
    {
        $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
        $tmdbId = $this->record->tmdb_id;
        $seasonNumber = $this->season->season_number;

        if (!$apiKey || !$tmdbId) {
            Notification::make()->title('Missing API Key or TMDB ID')->danger()->send();
            return;
        }

        $count = 0;
        
        // Loop from Start to End
        for ($epNum = $start; $epNum <= $end; $epNum++) {
            
            // Check if exists
            $exists = Episode::where('season_id', $this->season_id)
                ->where('episode_number', $epNum)
                ->exists();

            if ($exists) continue; // Skip existing

            // Fetch from TMDB
            $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$tmdbId}/season/{$seasonNumber}/episode/{$epNum}", [
                'api_key' => $apiKey,
                'language' => 'en-US',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                Episode::create([
                    'season_id' => $this->season_id,
                    'episode_number' => $epNum,
                    'title' => $data['name'] ?? "Episode {$epNum}",
                    'slug' => Str::slug($this->record->title . '-s' . $seasonNumber . '-ep' . $epNum),
                    'overview' => $data['overview'] ?? null,
                    'thumbnail_url' => !empty($data['still_path']) 
                        ? 'https://image.tmdb.org/t/p/original' . $data['still_path'] 
                        : null,
                    'duration' => $data['runtime'] ?? 0,
                    'air_date' => $data['air_date'] ?? null,
                    'video_url' => '#',
                    'is_premium' => false,
                    'coin_price' => 0,
                    'xp_reward' => 10,
                ]);
                $count++;
            }
        }

        if ($count > 0) {
            Notification::make()->title("Successfully imported {$count} episodes (Ep {$start}-{$end})!")->success()->send();
        } else {
            Notification::make()->title("No new episodes found in this range.")->info()->send();
        }

        $this->dispatch('refresh-table');
    }

    protected function fetchSeasonEpisodes()
    {
        $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
        $tmdbId = $this->record->tmdb_id;
        $seasonNumber = $this->season->season_number;

        if (!$apiKey || !$tmdbId) {
            Notification::make()->title('Missing API Key or TMDB ID')->danger()->send();
            return;
        }

        $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$tmdbId}/season/{$seasonNumber}", [
            'api_key' => $apiKey,
            'language' => 'en-US',
        ]);

        if ($response->failed()) {
            Notification::make()->title('Failed to fetch season from TMDB')->danger()->send();
            return;
        }

        $data = $response->json();
        $episodes = $data['episodes'] ?? [];
        $count = 0;

        foreach ($episodes as $epData) {
            $epNum = $epData['episode_number'];

            $exists = Episode::where('season_id', $this->season_id)
                ->where('episode_number', $epNum)
                ->exists();

            if ($exists) continue;

            Episode::create([
                'season_id' => $this->season_id,
                'episode_number' => $epNum,
                'title' => $epData['name'] ?? "Episode {$epNum}",
                'slug' => Str::slug($this->record->title . '-s' . $seasonNumber . '-ep' . $epNum),
                'overview' => $epData['overview'] ?? null,
                'thumbnail_url' => !empty($epData['still_path']) 
                    ? 'https://image.tmdb.org/t/p/original' . $epData['still_path'] 
                    : null,
                'duration' => $epData['runtime'] ?? 0,
                'air_date' => $epData['air_date'] ?? null,
                'video_url' => '#',
                'is_premium' => false,
                'coin_price' => 0,
                'xp_reward' => 10,
            ]);

            $count++;
        }

        if ($count > 0) {
            Notification::make()->title("Successfully imported {$count} episodes!")->success()->send();
        } else {
            Notification::make()->title("No new episodes found.")->info()->send();
        }

        $this->dispatch('refresh-table');
    }

    protected function fetchAndCreateEpisode($episodeNumber)
    {
        $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
        $tmdbId = $this->record->tmdb_id;
        $seasonNumber = $this->season->season_number;

        if (!$apiKey || !$tmdbId) {
            Notification::make()->title('Missing Keys')->danger()->send();
            return;
        }

        $exists = Episode::where('season_id', $this->season_id)
            ->where('episode_number', $episodeNumber)
            ->exists();

        if ($exists) {
            Notification::make()->title("Episode #{$episodeNumber} already exists!")->warning()->send();
            return;
        }

        $response = Http::withoutVerifying()->get("https://api.themoviedb.org/3/tv/{$tmdbId}/season/{$seasonNumber}/episode/{$episodeNumber}", [
            'api_key' => $apiKey, 'language' => 'en-US',
        ]);

        if ($response->failed()) {
            Notification::make()->title('Fetch Failed')->body('Episode not found.')->danger()->send();
            return;
        }

        $data = $response->json();

        Episode::create([
            'season_id' => $this->season_id,
            'episode_number' => $episodeNumber,
            'title' => $data['name'] ?? "Episode {$episodeNumber}",
            'slug' => Str::slug($this->record->title . '-s' . $seasonNumber . '-ep' . $episodeNumber),
            'overview' => $data['overview'] ?? null,
            'thumbnail_url' => !empty($data['still_path']) ? 'https://image.tmdb.org/t/p/original' . $data['still_path'] : null,
            'duration' => $data['runtime'] ?? 0,
            'air_date' => $data['air_date'] ?? null,
            'video_url' => '#', 
            'is_premium' => false,
            'coin_price' => 0,
            'xp_reward' => 10,
        ]);

        Notification::make()->title("Episode #{$episodeNumber} Added Successfully!")->success()->send();
        $this->dispatch('refresh-table'); 
    }

    protected function getEpisodeFormSchema(): array
    {
        return [
            Grid::make(3)->schema([
                Forms\Components\Group::make()->schema([
                    Section::make('Episode Details')
                        ->schema([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('episode_number')
                                    ->required()->numeric()->prefix('#')->live(onBlur: true),
                                Forms\Components\TextInput::make('duration')->numeric()->suffix('mins'),
                            ]),
                            Forms\Components\TextInput::make('title')->required()->live(onBlur: true)
                                ->afterStateUpdated(fn (Set $set, ?string $state, Get $get) => $set('slug', Str::slug($this->record->title . '-s' . $this->season->season_number . '-ep' . $get('episode_number') . '-' . $state))),
                            Forms\Components\Hidden::make('slug'),
                            Forms\Components\Textarea::make('overview')->rows(3)->columnSpanFull(),
                            Forms\Components\DatePicker::make('air_date')->native(false)->displayFormat('d M Y'),
                        ]),
                    Section::make('Media & Subtitles')
                        ->schema([
                            Forms\Components\TextInput::make('thumbnail_url')->label('Thumbnail URL')->url(),
                            Forms\Components\Textarea::make('video_url')->label('Video Source')->rows(2),
                            Repeater::make('subtitles')->relationship()->label('Subtitles / Captions')->schema([
                                Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('language')->required(),
                                    Select::make('format')->options(['vtt' => 'VTT', 'srt' => 'SRT', 'ass' => 'ASS'])->default('vtt')->required(),
                                ]),
                                Forms\Components\TextInput::make('url')->label('URL')->url()->required(),
                            ])->columns(1),
                        ]),
                ])->columnSpan(2),
                Forms\Components\Group::make()->schema([
                    Section::make('Monetization')
                        ->schema([
                            Forms\Components\Toggle::make('is_premium')->label('Premium Content')->live(),
                            Forms\Components\TextInput::make('coin_price')->label('Unlock Price')->numeric()->default(0)->hidden(fn (Get $get) => !$get('is_premium'))->required(fn (Get $get) => $get('is_premium')),
                            Forms\Components\TextInput::make('xp_reward')->numeric()->default(10)->label('XP Reward'),
                        ]),
                ])->columnSpan(1),
            ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Episode::query()->where('season_id', $this->season_id))
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')->label('Thumb')->width(80)->height(50),
                Tables\Columns\TextInputColumn::make('episode_number')->label('Ep #')->type('number')->sortable()->alignCenter()->width(80),
                Tables\Columns\TextColumn::make('title')->searchable()->limit(30),
                Tables\Columns\ToggleColumn::make('is_premium')->label('Premium')->alignCenter(),
                Tables\Columns\TextColumn::make('coin_price')->numeric()->prefix('🪙 ')->sortable()->alignRight(),
                Tables\Columns\TextColumn::make('created_at')->date('d M')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('episode_number', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()->slideOver()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['season_id'] = $this->season_id;
                        if (empty($data['slug'])) $data['slug'] = Str::slug($this->record->title . '-s' . $this->season->season_number . '-ep' . $data['episode_number']);
                        return $data;
                    })
                    ->form($this->getEpisodeFormSchema()),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->slideOver()->form($this->getEpisodeFormSchema()),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('set_price')->form([
                        Forms\Components\Toggle::make('is_premium')->label('Mark as Premium')->default(true),
                        Forms\Components\TextInput::make('coin_price')->label('Coin Price')->numeric()->default(50)->required(),
                        Forms\Components\TextInput::make('xp_reward')->label('XP Reward')->numeric()->default(10),
                    ])->action(function (Collection $records, array $data) {
                        $records->each->update(['is_premium' => $data['is_premium'], 'coin_price' => $data['coin_price'], 'xp_reward' => $data['xp_reward']]);
                        Notification::make()->title('Updated Successfully')->success()->send();
                    })->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('replace_video_domain')->form([
                        Forms\Components\TextInput::make('find_string')->required(),
                        Forms\Components\TextInput::make('replace_string')->required(),
                    ])->action(function (Collection $records, array $data) {
                        $count = 0;
                        foreach ($records as $record) {
                            if ($record->video_url && str_contains($record->video_url, $data['find_string'])) {
                                $newUrl = str_replace($data['find_string'], $data['replace_string'], $record->video_url);
                                $record->update(['video_url' => $newUrl]);
                                $count++;
                            }
                        }
                        Notification::make()->title("Updated {$count} episodes successfully!")->success()->send();
                    })->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}