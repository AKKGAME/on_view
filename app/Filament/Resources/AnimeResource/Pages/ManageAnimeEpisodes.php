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
use Filament\Forms\Get;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
// ✅ အသစ်ထပ်ထည့်ထားသော Import များ
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
        return "Episodes: " . $this->season->title;
    }

    protected function getEpisodeFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('episode_number')
                ->required()
                ->numeric(),
                
            Forms\Components\TextInput::make('title')
                ->required(),
                
            Forms\Components\Textarea::make('overview')
                ->rows(3)
                ->columnSpanFull(),
                
            Forms\Components\TextInput::make('thumbnail_url')
                ->label('Image URL'),
                
            Forms\Components\Textarea::make('video_url')
                ->label('Video Source')
                ->rows(3)
                ->columnSpanFull(),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Toggle::make('is_premium')
                        ->label('Premium Episode?')
                        ->onColor('success')
                        ->offColor('gray')
                        ->live(), 

                    Forms\Components\TextInput::make('coin_price')
                        ->numeric()
                        ->default(0)
                        ->prefix('Coins')
                        ->hidden(fn (Get $get) => !$get('is_premium')), 

                    Forms\Components\TextInput::make('xp_reward')
                        ->numeric()
                        ->default(10)
                        ->label('XP Reward'),
                ])->columns(3)->columnSpanFull(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Episode::query()->where('season_id', $this->season_id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('episode_number')
                    ->label('Ep')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Img')
                    ->height(40)
                    ->rounded(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\IconColumn::make('is_premium')
                    ->boolean()
                    ->label('Premium'),
                
                Tables\Columns\TextColumn::make('coin_price')
                    ->numeric()
                    ->label('Price')
                    ->sortable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Episode')
                    ->mutateFormDataUsing(function (array $data) {
                        $data['season_id'] = $this->season_id;
                        $data['slug'] = Str::slug($this->record->title . '-s' . $this->season->season_number . '-ep' . $data['episode_number']);
                        return $data;
                    })
                    ->form($this->getEpisodeFormSchema()), 
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->form($this->getEpisodeFormSchema()), 
                
                Tables\Actions\DeleteAction::make(),
            ])
            // ✅ Bulk Actions (Select လုပ်ပြီး Coin သတ်မှတ်ရန်)
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    
                    Tables\Actions\DeleteBulkAction::make(),

                    Tables\Actions\BulkAction::make('set_price')
                        ->label('Set Coin Price')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->form([
                            Forms\Components\Toggle::make('is_premium')
                                ->label('Mark as Premium?')
                                ->default(true),

                            Forms\Components\TextInput::make('coin_price')
                                ->label('Coin Amount')
                                ->numeric()
                                ->default(50)
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $records->each(function ($record) use ($data) {
                                $record->update([
                                    'is_premium' => $data['is_premium'],
                                    'coin_price' => $data['coin_price'],
                                ]);
                            });

                            Notification::make()
                                ->title('Prices Updated Successfully')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('episode_number', 'asc');
    }
}