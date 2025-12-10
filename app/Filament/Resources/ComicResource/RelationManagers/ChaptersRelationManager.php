<?php

namespace App\Filament\Resources\ComicResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Actions\Action; // Manual Sort အတွက်
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Str helper မလိုတော့ပါ (ID based directory သုံးမည်)

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    protected static ?string $title = 'Chapters';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // Section 1: General Info
                Forms\Components\Section::make('Chapter Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('chapter_number')
                            ->label('Chapter Number')
                            ->numeric()
                            ->required()
                            ->live() // Update directory dynamically if changed
                            ->default(fn () => $this->getOwnerRecord()->chapters()->max('chapter_number') + 1)
                            ->hint('Used for sorting order'),

                        Forms\Components\TextInput::make('title')
                            ->label('Chapter Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., The Beginning'),
                    ]),

                // Section 2: Premium / Monetization
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

                // Section 3: Pages Upload
                Forms\Components\Section::make('Comic Pages')
                    ->schema([
                        FileUpload::make('pages')
                            ->label('Upload Pages')
                            ->helperText('ပုံများကို Select မှတ်ပြီး Upload တင်ပါ။ (001.jpg, 002.jpg)')
                            
                            // ✅ NEW: Manual Sort Button (Edit Mode)
                            ->hintAction(
                                Action::make('sort_images')
                                    ->label('Sort by Filename')
                                    ->icon('heroicon-m-arrow-path')
                                    ->color('info')
                                    ->action(function (Set $set, $state) {
                                        if (is_array($state)) {
                                            natsort($state); 
                                            $set('pages', array_values($state)); // Re-index
                                            
                                            Notification::make()
                                                ->title('Images sorted successfully!')
                                                ->success()
                                                ->send();
                                        }
                                    })
                            )
                            
                            ->multiple() 
                            ->reorderable() 
                            ->appendFiles() 
                            ->panelLayout('grid') 
                            
                            ->preserveFilenames() 
                            
                            // ✅ FIX: Directory by Comic ID & Chapter Number
                            ->directory(function (RelationManager $livewire, Get $get) {
                                $comicId = $livewire->getOwnerRecord()->id; 
                                $chapterNum = $get('chapter_number') ?? 'temp';
                                return "comics/{$comicId}/chapters/{$chapterNum}";
                            }) 
                            
                            ->image()
                            ->imageEditor() 
                            ->imageResizeMode('cover') 
                            ->imageCropAspectRatio(null) 
                            ->imageResizeTargetWidth('850') 
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
                Tables\Columns\IconColumn::make('is_premium')->boolean()->trueIcon('heroicon-o-lock-closed')->falseIcon('heroicon-o-lock-open')->trueColor('warning')->falseColor('success'),
                Tables\Columns\TextColumn::make('coin_price')->label('Price')->formatStateUsing(fn ($state) => $state > 0 ? $state . ' Coins' : 'Free')->sortable(),
            ])
            ->defaultSort('chapter_number', 'desc')
            ->filters([
                Tables\Filters\Filter::make('is_premium')->query(fn (Builder $query) => $query->where('is_premium', true))->label('Premium Chapters Only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Auto-sort on Create
                        if (isset($data['pages']) && is_array($data['pages'])) {
                            natsort($data['pages']); 
                            $data['pages'] = array_values($data['pages']);
                        }
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Auto-sort on Edit Save
                        if (isset($data['pages']) && is_array($data['pages'])) {
                            natsort($data['pages']);
                            $data['pages'] = array_values($data['pages']);
                        }
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}