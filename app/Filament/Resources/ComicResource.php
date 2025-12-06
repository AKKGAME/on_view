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

class ComicResource extends Resource
{
    protected static ?string $model = Comic::class;

    // Icon for Sidebar (စာအုပ်ပုံစံ)
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    
    protected static ?string $navigationGroup = 'Content Management';

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
                    ->height(90),

                // 2. Title
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // 3. Author
                Tables\Columns\TextColumn::make('author')
                    ->searchable()
                    ->placeholder('Unknown'),

                // 4. Status Icon
                Tables\Columns\IconColumn::make('is_finished')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning'),

                // 5. Chapters Count (Optional - Performance ထိခိုက်နိုင်သည်)
                Tables\Columns\TextColumn::make('chapters_count')
                    ->counts('chapters')
                    ->label('Chapters'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter by Status
                Tables\Filters\Filter::make('is_finished')
                    ->query(fn (Builder $query) => $query->where('is_finished', true))
                    ->label('Completed Only'),
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