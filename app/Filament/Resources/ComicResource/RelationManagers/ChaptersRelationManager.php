<?php

namespace App\Filament\Resources\ComicResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get; // Dynamic Form Logic အတွက်
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ChaptersRelationManager extends RelationManager
{
    protected static string $relationship = 'chapters';

    // Title on the relation tab
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
                            ->default(fn () => $this->getOwnerRecord()->chapters()->max('chapter_number') + 1) // နောက်ဆုံး Chapter နံပါတ်ကို အလိုအလျောက်တိုးပေးသည်
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
                            ->onColor('warning') // Gold color like look
                            ->live() // ✅ Toggle နှိပ်လိုက်တာနဲ့ State ကို update လုပ်ပြီး အောက်က Price ကို ပြ/မပြ လုပ်မည်
                            ->default(false),

                        Forms\Components\TextInput::make('coin_price')
                            ->label('Unlock Price (Coins)')
                            ->numeric()
                            ->default(0)
                            ->prefixIcon('heroicon-o-currency-dollar')
                            // ✅ is_premium က true ဖြစ်မှသာ ဒီ field ကို ပြမည်
                            ->hidden(fn (Get $get): bool => ! $get('is_premium'))
                            ->required(fn (Get $get): bool => $get('is_premium')),
                    ]),

                // Section 3: Pages Upload (The most important part for Comics)
                Forms\Components\Section::make('Manga / Comic Pages')
                    ->schema([
                        Forms\Components\FileUpload::make('pages')
                            ->label('Upload Pages')
                            ->helperText('You can upload multiple images. Drag and drop to reorder pages.')
                            ->multiple() // ✅ ပုံအများကြီးတင်ခွင့်ပြုသည်
                            ->reorderable() // ✅ ပုံအစီအစဉ်ကို ဆွဲရွှေ့ပြီး ပြင်နိုင်သည်
                            ->directory('comics/chapters') // storage path
                            ->image()
                            ->imageEditor() // ပုံဖြတ်ညှပ်ကပ် လုပ်နိုင်သည်
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
                // 1. Chapter Number
                Tables\Columns\TextColumn::make('chapter_number')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                // 2. Title
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->weight('bold'),

                // 3. Page Count (ပုံဘယ်နှပုံပါလဲ ပြရန်)
                Tables\Columns\TextColumn::make('pages')
                    ->label('Pages')
                    ->formatStateUsing(fn ($state) => is_array($state) ? count($state) . ' Pages' : '0 Pages')
                    ->badge()
                    ->color('gray'),

                // 4. Premium Status
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('warning')
                    ->falseColor('success'),

                // 5. Price
                Tables\Columns\TextColumn::make('coin_price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state . ' Coins' : 'Free')
                    ->sortable(),
            ])
            ->defaultSort('chapter_number', 'desc') // အသစ်ဆုံး Chapter ကို အပေါ်ဆုံးမှာပြမည်
            ->filters([
                // Premium Filter
                Tables\Filters\Filter::make('is_premium')
                    ->query(fn (Builder $query) => $query->where('is_premium', true))
                    ->label('Premium Chapters Only'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}