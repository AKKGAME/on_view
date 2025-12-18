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
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Str;
use Filament\Support\Enums\FontWeight;

class ManageAnimeSeasons extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = AnimeResource::class;

    protected static string $view = 'filament.resources.anime-resource.pages.manage-anime-seasons';

    // ✅ 1. Title ကို ပိုရှင်းအောင်ပြမယ်
    public function getTitle(): string|Htmlable
    {
        return "Seasons: " . $this->record->title;
    }

    // ✅ 2. Breadcrumbs (လမ်းကြောင်းပြ) ထည့်မယ်
    public function getBreadcrumbs(): array
    {
        return [
            AnimeResource::getUrl() => 'Animes',
            AnimeResource::getUrl('edit', ['record' => $this->record]) => $this->record->title,
            '#' => 'Manage Seasons',
        ];
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
                    ->color('primary')
                    ->formatStateUsing(fn ($state) => "Season {$state}")
                    ->weight(FontWeight::Bold),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->description(fn (Season $record) => $record->slug) // Slug ကို အောက်မှာဖျော့ဖျော့လေးပြမယ်
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('episodes_count')
                    ->counts('episodes')
                    ->label('Episodes')
                    ->badge()
                    ->icon('heroicon-m-film')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('season_number', 'asc')
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New Season')
                    ->icon('heroicon-o-plus')
                    ->modalWidth('md')
                    ->slideOver() // ဘေးကနေ ဆွဲထွက်လာမယ့် ပုံစံ
                    ->form([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('season_number')
                                    ->required()
                                    ->numeric()
                                    ->label('Season No.')
                                    // ✅ နောက်ဆုံး Season နံပါတ်ကိုရှာပြီး +1 ပေါင်းပေးထားမယ် (Auto Fill)
                                    ->default(fn () => $this->record->seasons()->max('season_number') + 1),

                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->placeholder('e.g. Season 1')
                                    ->live(onBlur: true)
                                    // Title ရိုက်တာနဲ့ Slug အော်တိုဖြည့်မယ်
                                    ->afterStateUpdated(fn (Set $set, $state, Get $get) => 
                                        $set('slug', Str::slug($this->record->title . '-season-' . $get('season_number')))
                                    ),
                            ]),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data) {
                        $data['anime_id'] = $this->record->id;
                        return $data;
                    }),
            ])
            ->actions([
                // ✅ 1. Episodes ကြည့်မည့် ခလုတ် (အထင်းသားပေါ်နေမယ်)
                Tables\Actions\Action::make('episodes')
                    ->label('Manage Episodes')
                    ->icon('heroicon-o-list-bullet')
                    ->color('info')
                    ->button()
                    ->url(fn (Season $record) => AnimeResource::getUrl('episodes', [
                        'record' => $this->record->id, 
                        'season_id' => $record->id
                    ])),

                // ✅ 2. Edit/Delete ကို Group ဖွဲ့လိုက်မယ် (နေရာသက်သာအောင်)
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->form([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('season_number')->required()->numeric(),
                                Forms\Components\TextInput::make('title')->required(),
                            ]),
                            Forms\Components\TextInput::make('slug')->required(),
                        ])
                        ->slideOver(),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ]);
    }
}