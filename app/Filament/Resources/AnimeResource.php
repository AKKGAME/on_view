<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimeResource\Pages;
use App\Filament\Resources\AnimeResource\RelationManagers\SeasonsRelationManager;
use App\Models\Anime;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;

class AnimeResource extends Resource
{
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $model = Anime::class;
    protected static ?string $navigationIcon = 'heroicon-o-film';
    protected static ?int $navigationSort = 1; // Menu မှာ အပေါ်ဆုံးထားမယ်

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // LEFT COLUMN (Main Content)
                Group::make()
                    ->schema([
                        Section::make('General Information')
                            ->description('Basic details about the anime.')
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
                                    ->readOnly(), // Slug ကို လက်နဲ့မပြင်စေချင်ရင် ReadOnly ထားနိုင်

                                Textarea::make('description')
                                    ->rows(5)
                                    ->columnSpanFull()
                                    ->placeholder('Write a brief synopsis...'),
                            ]),

                        Section::make('Media')
                            ->schema([
                                FileUpload::make('thumbnail_url')
                                    ->label('Poster Image (Vertical)')
                                    ->image()
                                    ->imageEditor() // ပုံဖြတ်လို့ရအောင်
                                    ->directory('anime-thumbnails')
                                    ->columnSpan(1),

                                FileUpload::make('cover_url')
                                    ->label('Cover Image (Horizontal)')
                                    ->image()
                                    ->imageEditor()
                                    ->directory('anime-covers')
                                    ->columnSpan(1),
                            ])->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]), // Desktop မှာ 2/3 နေရာယူမယ်

                // RIGHT COLUMN (Meta Data)
                Group::make()
                    ->schema([
                        Section::make('Classification')
                            ->schema([
                                Select::make('genres')
                                    ->relationship('genres', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([ // Genre အသစ် ဒီကနေတန်းထည့်လို့ရအောင်
                                        TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set, $state) => $set('slug', Str::slug($state))),
                                        TextInput::make('slug')->required(),
                                    ])
                                    ->required(),

                                TextInput::make('total_episodes')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Episode Count'),
                            ]),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_completed')
                                    ->label('Completed Series?')
                                    ->onColor('success')
                                    ->offColor('gray')
                                    ->helperText('Toggle on if the anime has finished airing.'),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]), // Desktop မှာ 1/3 နေရာယူမယ်
            ])
            ->columns(3); // စုစုပေါင်း 3 column grid
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail_url')
                    ->label('Poster')
                    ->height(60)
                    ->rounded(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(30),

                // Genre တွေကို Badge လေးတွေနဲ့ ပြမယ်
                Tables\Columns\TextColumn::make('genres.name')
                    ->badge()
                    ->color('primary')
                    ->limitList(2),

                Tables\Columns\TextColumn::make('seasons_count')
                    ->counts('seasons')
                    ->label('Seasons')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_completed')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_completed')
                    ->label('Completion Status'),
                
                Tables\Filters\SelectFilter::make('genres')
                    ->relationship('genres', 'name')
                    ->multiple()
                    ->preload(),
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
    
    public static function getRelations(): array
    {
        return [
            SeasonsRelationManager::class, 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnimes::route('/'),
            'create' => Pages\CreateAnime::route('/create'),
            'edit' => Pages\EditAnime::route('/{record}/edit'),
        ];
    }
}