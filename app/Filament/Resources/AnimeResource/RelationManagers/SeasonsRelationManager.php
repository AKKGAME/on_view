<?php

namespace App\Filament\Resources\AnimeResource\RelationManagers;

use App\Filament\Resources\SeasonResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class SeasonsRelationManager extends RelationManager
{
    protected static string $relationship = 'seasons';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('season_number')
                    ->numeric()
                    ->default(1)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('season_number')->label('#'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('episodes_count')->counts('episodes')->label('Episodes'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // Season တစ်ခုချင်းစီထဲဝင်ပြီး Episode ထည့်ဖို့ ခလုတ်
                Tables\Actions\Action::make('manage')
                    ->label('Manage Episodes')
                    ->icon('heroicon-o-arrow-right-circle')
                    ->url(fn ($record) => SeasonResource::getUrl('edit', ['record' => $record])),
                    
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}