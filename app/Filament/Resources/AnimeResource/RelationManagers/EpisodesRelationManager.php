<?php

namespace App\Filament\Resources\SeasonResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;

class EpisodesRelationManager extends RelationManager
{
    protected static string $relationship = 'episodes';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('episode_number')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('video_url')
                    ->url()
                    ->required()
                    ->columnSpanFull(),
                
                // Premium Logic
                Forms\Components\Toggle::make('is_premium')
                    ->label('Premium?')
                    ->live(),
                    
                Forms\Components\TextInput::make('coin_price')
                    ->numeric()
                    ->hidden(fn (Get $get) => !$get('is_premium')),
                    
                Forms\Components\TextInput::make('xp_reward')
                    ->numeric()
                    ->default(10),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('episode_number')->label('Ep'),
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\IconColumn::make('is_premium')->boolean(),
                Tables\Columns\TextColumn::make('coin_price'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}