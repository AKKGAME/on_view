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
use Filament\Forms\Components\Repeater; // 🔥 Repeater Added
use Filament\Forms\Components\Select;   // 🔥 Select Added
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

            Action::make('fetch_single_episode')
                ->label('Fetch Next Episode (TMDB)')
                ->icon('heroicon-o-cloud-arrow-down')
                ->color('info')
                ->form([
                    Forms\Components\TextInput::make('episode_number')
                        ->label('Episode Number')
                        ->numeric()
                        ->required()
                        ->default(function () {
                            $lastEp = Episode::where('season_id', $this->season_id)
                                ->max('episode_number');
                            return $lastEp ? $lastEp + 1 : 1;
                        })
                        ->helperText('Enter the episode number you want to fetch.'),
                ])
                ->action(function (array $data) {
                    $this->fetchAndCreateEpisode($data['episode_number']);
                }),
        ];
    }

    protected function fetchAndCreateEpisode($episodeNumber)
    {
        $apiKey = Setting::where('key', 'tmdb_api_key')->value('value');
        $tmdbId = $this->record->tmdb_id;
        $seasonNumber = $this->season->season_number;

        if (!$apiKey) {
            Notification::make()->title('API Key Missing')->danger()->send();
            return;
        }
        if (!$tmdbId) {
            Notification::make()->title('Anime TMDB ID Missing')->danger()->send();
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
            'api_key' => $apiKey,
            'language' => 'en-US',
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

        Notification::make()->title("Episode #{$episodeNumber} Added Successfully!")->success()->send();
        $this->dispatch('refresh-table'); 
    }

    protected function getEpisodeFormSchema(): array
    {
        return [
            Grid::make(3)->schema([
                // LEFT SIDE (Details & Media)
                Forms\Components\Group::make()->schema([
                    Section::make('Episode Details')
                        ->schema([
                            Grid::make(2)->schema([
                                Forms\Components\TextInput::make('episode_number')
                                    ->required()
                                    ->numeric()
                                    ->prefix('#')
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('duration')
                                    ->numeric()
                                    ->suffix('mins'),
                            ]),

                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Set $set, ?string $state, Get $get) {
                                    $slug = Str::slug($this->record->title . '-s' . $this->season->season_number . '-ep' . $get('episode_number') . '-' . $state);
                                    $set('slug', $slug);
                                }),

                            Forms\Components\Hidden::make('slug'),

                            Forms\Components\Textarea::make('overview')
                                ->rows(3)
                                ->columnSpanFull(),
                            
                            Forms\Components\DatePicker::make('air_date')
                                ->native(false)
                                ->displayFormat('d M Y'),
                        ]),

                    Section::make('Media & Subtitles')
                        ->schema([
                            Forms\Components\TextInput::make('thumbnail_url')
                                ->label('Thumbnail URL')
                                ->prefixIcon('heroicon-o-photo')
                                ->url(),

                            Forms\Components\Textarea::make('video_url')
                                ->label('Video Source')
                                ->placeholder('Direct URL or Iframe')
                                ->rows(2),

                            // 🔥🔥🔥 SUBTITLE REPEATER ADDED HERE 🔥🔥🔥
                            Repeater::make('subtitles')
                                ->relationship() // Must relate to hasMany in Episode Model
                                ->label('Subtitles / Captions')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('language')
                                            ->label('Language Label')
                                            ->placeholder('Myanmar')
                                            ->required(),
                                            
                                        Select::make('format')
                                            ->options([
                                                'vtt' => 'VTT (WebVTT)',
                                                'srt' => 'SRT (SubRip)',
                                                'ass' => 'ASS (Advanced SSA)',
                                            ])
                                            ->default('vtt')
                                            ->required(),
                                    ]),

                                    Forms\Components\TextInput::make('url')
                                        ->label('Subtitle File URL')
                                        ->placeholder('https://example.com/subs/myanmar.vtt')
                                        ->prefixIcon('heroicon-m-document-text')
                                        ->url()
                                        ->required(),
                                ])
                                ->itemLabel(fn (array $state): ?string => $state['language'] ?? null)
                                ->collapsed(false)
                                ->collapseAllAction(fn ($action) => $action->label('Collapse All'))
                                ->deleteAction(fn ($action) => $action->requiresConfirmation())
                                ->columns(1),
                        ]),
                ])->columnSpan(2),

                // RIGHT SIDE (Monetization)
                Forms\Components\Group::make()->schema([
                    Section::make('Monetization')
                        ->schema([
                            Forms\Components\Toggle::make('is_premium')
                                ->label('Premium Content')
                                ->onColor('success')
                                ->offColor('gray')
                                ->live(),

                            Forms\Components\TextInput::make('coin_price')
                                ->label('Unlock Price')
                                ->numeric()
                                ->default(0)
                                ->prefixIcon('heroicon-o-currency-dollar')
                                ->hidden(fn (Get $get) => !$get('is_premium'))
                                ->required(fn (Get $get) => $get('is_premium')),

                            Forms\Components\TextInput::make('xp_reward')
                                ->numeric()
                                ->default(10)
                                ->label('XP Reward'),
                        ]),
                ])->columnSpan(1),
            ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Episode::query()->where('season_id', $this->season_id)
            )
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Thumb')
                    ->width(80)
                    ->height(50)
                    ->extraImgAttributes(['class' => 'object-cover rounded']),

                Tables\Columns\TextInputColumn::make('episode_number')
                    ->label('Ep #')
                    ->type('number')
                    ->sortable()
                    ->alignCenter()
                    ->width(80),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30)
                    ->description(fn (Episode $record) => Str::limit($record->overview, 40)),

                Tables\Columns\ToggleColumn::make('is_premium')
                    ->label('Premium')
                    ->onColor('success')
                    ->offColor('gray')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('coin_price')
                    ->numeric()
                    ->prefix('🪙 ')
                    ->sortable()
                    ->alignRight(),

                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('episode_number', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('New Episode')
                    ->icon('heroicon-o-plus')
                    ->slideOver()
                    ->mutateFormDataUsing(function (array $data) {
                        $data['season_id'] = $this->season_id;
                        if (empty($data['slug'])) {
                            $data['slug'] = Str::slug($this->record->title . '-s' . $this->season->season_number . '-ep' . $data['episode_number']);
                        }
                        return $data;
                    })
                    ->form($this->getEpisodeFormSchema()), 
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->form($this->getEpisodeFormSchema()), 
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('set_price')
                        ->label('Update Prices')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\Toggle::make('is_premium')->label('Mark as Premium')->default(true),
                            Forms\Components\TextInput::make('coin_price')->label('Coin Price')->numeric()->default(50)->required(),
                            Forms\Components\TextInput::make('xp_reward')->label('XP Reward')->numeric()->default(10),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'is_premium' => $data['is_premium'],
                                'coin_price' => $data['coin_price'],
                                'xp_reward'  => $data['xp_reward'],
                            ]);
                            Notification::make()->title('Updated Successfully')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

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
                                ->title("Updated {$count} episodes successfully!")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}