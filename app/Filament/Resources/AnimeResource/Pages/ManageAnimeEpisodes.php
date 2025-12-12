<?php

namespace App\Filament\Resources\AnimeResource\Pages;

use App\Filament\Resources\AnimeResource;
use App\Models\Episode;
use App\Models\Season;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms;
use Filament\Actions\Action; // Header Action အတွက်
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

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

    // Header တွင် Back Button ထည့်ခြင်း
    protected function getHeaderActions(): array
    {
        return [
            Action::make('back_to_seasons')
                ->label('Back to Seasons')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(AnimeResource::getUrl('seasons', ['record' => $this->record])),
        ];
    }

    protected function getEpisodeFormSchema(): array
    {
        return [
            Grid::make(3)->schema([
                // LEFT SIDE (Content Info)
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
                                // Slug ကို Auto ဖြည့်ပေးမယ် (Anime Slug + Season + Ep)
                                ->afterStateUpdated(function (Set $set, ?string $state, Get $get) {
                                    $slug = Str::slug($this->record->title . '-s' . $this->season->season_number . '-ep' . $get('episode_number') . '-' . $state);
                                    $set('slug', $slug);
                                }),

                            Forms\Components\Hidden::make('slug'), // Hidden Slug

                            Forms\Components\Textarea::make('overview')
                                ->rows(3)
                                ->columnSpanFull(),
                            
                            Forms\Components\DatePicker::make('air_date')
                                ->native(false)
                                ->displayFormat('d M Y'),
                        ]),

                    Section::make('Media')
                        ->schema([
                            Forms\Components\TextInput::make('thumbnail_url')
                                ->label('Thumbnail URL')
                                ->prefixIcon('heroicon-o-photo')
                                ->url(),

                            Forms\Components\Textarea::make('video_url')
                                ->label('Video Source')
                                ->placeholder('Direct URL or Iframe')
                                ->rows(3),
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
                                ->label('XP Reward')
                                ->helperText('User gets this XP after watching.'),
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

                // Table ထဲမှာတင် အဖွင့်အပိတ်လုပ်လို့ရမယ်
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
                    ->slideOver() // ညာဘက်ကနေ Slide ဝင်လာမယ်
                    ->mutateFormDataUsing(function (array $data) {
                        $data['season_id'] = $this->season_id;
                        // Slug မပါလာရင် Auto ဖြည့်မယ်
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

                    // BULK SET PRICE
                    Tables\Actions\BulkAction::make('set_price')
                        ->label('Update Prices')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\Toggle::make('is_premium')
                                ->label('Mark as Premium')
                                ->default(true),

                            Forms\Components\TextInput::make('coin_price')
                                ->label('Coin Price')
                                ->numeric()
                                ->default(50)
                                ->required(),
                                
                            Forms\Components\TextInput::make('xp_reward')
                                ->label('XP Reward')
                                ->numeric()
                                ->default(10),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each->update([
                                'is_premium' => $data['is_premium'],
                                'coin_price' => $data['coin_price'],
                                'xp_reward'  => $data['xp_reward'],
                            ]);

                            Notification::make()
                                ->title('Episodes Updated Successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }
}