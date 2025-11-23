<?php

namespace App\Filament\Resources\AnimeResource\Pages;

use App\Filament\Resources\AnimeResource;
use App\Models\Season;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Forms; // Form တွေသုံးဖို့ ဒါလေးလိုပါတယ်
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;

class ManageAnimeSeasons extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = AnimeResource::class;

    protected static string $view = 'filament.resources.anime-resource.pages.manage-anime-seasons';

    public function getTitle(): string|Htmlable
    {
        return "Manage Seasons: " . $this->record->title;
    }

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Season::query()->where('anime_id', $this->record->id)
            )
            ->columns([
                Tables\Columns\TextColumn::make('season_number')
                    ->label('Season')
                    ->sortable()
                    ->badge()
                    ->color('warning')
                    ->formatStateUsing(fn ($state) => "Season $state"),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('episodes_count')
                    ->counts('episodes')
                    ->label('Episodes')
                    ->badge()
                    ->color('gray'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add Season')
                    // ✅ Form ကွက်လပ်များ ထည့်ပေးရပါမယ်
                    ->form([
                        Forms\Components\TextInput::make('season_number')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($set, $state) => $set('slug', Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required(),
                    ])
                    ->mutateFormDataUsing(function (array $data) {
                        $data['anime_id'] = $this->record->id;
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    // ✅ Edit အတွက်လည်း Form လိုပါတယ်
                    ->form([
                        Forms\Components\TextInput::make('season_number')
                            ->required()
                            ->numeric(),
                        Forms\Components\TextInput::make('title')
                            ->required(),
                        Forms\Components\TextInput::make('slug')
                            ->required(),
                    ]),
                
                // Episodes Page သို့ သွားမည့် ခလုတ်
                Tables\Actions\Action::make('episodes')
                    ->label('Manage Episodes')
                    ->icon('heroicon-o-list-bullet')
                    ->button() // Button ပုံစံပြောင်းလိုက်ရင် ပိုထင်ရှားပါတယ်
                    ->outlined()
                    ->url(fn (Season $record) => AnimeResource::getUrl('episodes', [
                        'record' => $this->record->id, 
                        'season_id' => $record->id
                    ])),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->defaultSort('season_number', 'asc'); // Season 1, 2, 3 အစဉ်လိုက်စီမယ်
    }
}