<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ComicResource\Pages;
use App\Filament\Resources\ComicResource\RelationManagers;
use App\Models\Comic;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Str;
use Filament\Forms\Set;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

class ComicResource extends Resource
{
    protected static ?string $model = Comic::class;

    // Icon for Sidebar (စာအုပ်ပုံစံ)
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Comic Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                // 1. Title (Auto Generate Slug)
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                                // 2. Slug
                                Forms\Components\TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true),
                                
                                // 3. Author
                                Forms\Components\TextInput::make('author')
                                    ->label('Author / Artist')
                                    ->maxLength(255),

                                // 4. Status
                                Forms\Components\Toggle::make('is_finished')
                                    ->label('Is Completed?')
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->inline(false),
                            ]),

                        Grid::make(2)->schema([
                            // 🔥 Channel Select Box Added Here
                            Select::make('channel_id')
                                ->relationship('channel', 'name')
                                ->searchable()
                                ->preload()
                                ->createOptionForm([
                                    TextInput::make('name')->required()->live()->afterStateUpdated(fn(Set $set, $state) => $set('slug', Str::slug($state))),
                                    TextInput::make('slug')->required(),
                                ])
                                ->label('Source Channel'),

                            TextInput::make('view_count')
                                ->label('Total Views')
                                ->numeric()
                                ->default(0)
                                ->disabled(),
                        ]),

                        // 5. Description
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Cover Art')
                    ->schema([
                        // 6. Cover Image Upload
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->directory('comics/covers') // storage/app/public/comics/covers
                            ->imageEditor()
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Cover Image
                Tables\Columns\ImageColumn::make('cover_image')
                    ->width(60)
                    ->height(90)
                    ->extraImgAttributes(['class' => 'object-cover rounded-md shadow-sm']),

                // 2. Title
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->wrap()
                    ->limit(40),

                // 3. Channel Info
                Tables\Columns\TextColumn::make('channel.name')
                    ->label('Channel')
                    ->icon('heroicon-m-tv')
                    ->color('warning')
                    ->toggleable(),

                // 4. View Count
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->icon('heroicon-m-eye')
                    ->numeric()
                    ->sortable()
                    ->color('gray'),

                // 5. Chapters Count
                Tables\Columns\TextColumn::make('chapters_count')
                    ->counts('chapters')
                    ->label('Chs')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                // 6. Status Icon
                Tables\Columns\IconColumn::make('is_finished')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-s-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // Filter by Status
                Tables\Filters\TernaryFilter::make('is_finished')
                    ->label('Status')
                    ->trueLabel('Completed')
                    ->falseLabel('Ongoing'),

                // Filter by Channel
                Tables\Filters\SelectFilter::make('channel')
                    ->relationship('channel', 'name')
                    ->searchable()
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
            // ✅ Comic တစ်ခုချင်းစီအောက်မှာ Chapter တွေကို စီမံဖို့ Relation Manager ထည့်ပါ
            RelationManagers\ChaptersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListComics::route('/'),
            'create' => Pages\CreateComic::route('/create'),
            'edit' => Pages\EditComic::route('/{record}/edit'),
        ];
    }
}