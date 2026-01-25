<?php

namespace App\Filament\Resources\ComicResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection; // Bulk Action အတွက်လိုအပ်

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';
    protected static ?string $title = 'Chapters';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Chapter Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('chapter_number')
                            ->label('Chapter Number')
                            ->numeric()
                            ->required()
                            ->live()
                            ->default(fn () => $this->getOwnerRecord()->chapters()->max('chapter_number') + 1)
                            ->hint('Used for sorting order'),

                        Forms\Components\TextInput::make('title')
                            ->label('Chapter Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., The Beginning'),
                    ]),

                Forms\Components\Section::make('Monetization')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Toggle::make('is_premium')
                            ->label('Premium Content?')
                            ->onColor('warning')
                            ->live()
                            ->default(false),

                        Forms\Components\TextInput::make('coin_price')
                            ->label('Unlock Price (Coins)')
                            ->numeric()
                            ->default(0)
                            ->prefixIcon('heroicon-o-currency-dollar')
                            ->hidden(fn (Get $get): bool => ! $get('is_premium'))
                            ->required(fn (Get $get): bool => $get('is_premium')),
                    ]),

                Forms\Components\Section::make('Comic Pages')
                    ->schema([
                        FileUpload::make('pages')
                            ->label('Upload Pages')
                            ->multiple() 
                            ->reorderable() 
                            ->appendFiles() 
                            ->panelLayout('grid') 
                            ->preserveFilenames() 
                            ->directory(function (RelationManager $livewire, Get $get) {
                                $comicId = $livewire->getOwnerRecord()->id; 
                                $chapterNum = $get('chapter_number') ?? 'temp';
                                return "comics/{$comicId}/chapters/{$chapterNum}";
                            }) 
                            ->image()
                            ->columnSpanFull()
                            ->required(),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('chapter_number')->label('#')->sortable()->width(50),
                Tables\Columns\TextColumn::make('title')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('pages')
                    ->label('Pages')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' Pages' : '0 Pages')
                    ->badge()->color('gray'),
                Tables\Columns\IconColumn::make('is_premium')->label('Premium')->boolean()->trueIcon('heroicon-o-lock-closed')->falseIcon('heroicon-o-lock-open')->trueColor('warning')->falseColor('success'),
                Tables\Columns\TextColumn::make('coin_price')->label('Price')->formatStateUsing(fn ($state) => $state > 0 ? $state . ' Coins' : 'Free')->sortable(),
            ])
            ->defaultSort('chapter_number', 'desc')
            ->filters([
                Tables\Filters\Filter::make('is_premium')->query(fn (Builder $query) => $query->where('is_premium', true))->label('Premium Chapters Only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        if (isset($data['pages']) && is_array($data['pages'])) {
                            natsort($data['pages']); 
                            $data['pages'] = array_values($data['pages']);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // ✅ NEW: Bulk Update Coin Price
                    Tables\Actions\BulkAction::make('set_coin_price')
                        ->label('Set Coin Price')
                        ->icon('heroicon-o-currency-dollar')
                        ->color('warning')
                        ->form([
                            Forms\Components\TextInput::make('new_coin_price')
                                ->label('New Price (Coins)')
                                ->numeric()
                                ->required()
                                ->default(0),
                            Forms\Components\Toggle::make('make_premium')
                                ->label('Make Premium?')
                                ->default(true),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $record) {
                                $record->update([
                                    'coin_price' => $data['new_coin_price'],
                                    'is_premium' => $data['make_premium'],
                                ]);
                            }

                            Notification::make()
                                ->title('Coin prices updated successfully!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}