<?php

namespace App\Filament\Resources\SectionResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AnimesRelationManager extends RelationManager
{
    protected static string $relationship = 'animes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
            ]);
    }

public function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('title')
        ->columns([
            Tables\Columns\ImageColumn::make('thumbnail_url'),
            Tables\Columns\TextColumn::make('title'),
        ])
        ->headerActions([
            Tables\Actions\AttachAction::make()
                ->preloadRecordSelect(), // Anime များရင် search လုပ်လို့ရအောင်
        ])
        ->actions([
            Tables\Actions\DetachAction::make(),
        ])
        ->reorderable('sort_order'); // Drag & Drop နဲ့စီလို့ရအောင် (Pivot table မှာ sort_order ရှိရမယ်)
}
}
