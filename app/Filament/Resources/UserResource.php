<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;

class UserResource extends Resource
{
    protected static ?string $navigationGroup = 'System';
    protected static ?string $model = User::class;

    // User နဲ့ပတ်သက်တဲ့ Icon (လူပုံ)
    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Account Information')
                    ->description('Manage user login details')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true) // ဖုန်းနံပါတ် တူလို့မရ (Edit လုပ်နေတဲ့အချိန် ကိုယ့်နံပါတ်ကိုယ်တော့ ခွင့်ပြု)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            ->dehydrated(fn ($state) => filled($state)) // Password ဖြည့်မှ Database ထဲထည့်မယ်
                            ->required(fn (string $context): bool => $context === 'create') // Create လုပ်ချိန်မှာ Required ဖြစ်မယ်
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Gaming Stats')
                    ->description('Manage coins, XP and ranking')
                    ->schema([
                        TextInput::make('coins')
                            ->numeric()
                            ->default(0)
                            ->prefixIcon('heroicon-o-currency-dollar') // Coin Icon လေးပြမယ်
                            ->label('Coins Balance'),

                        TextInput::make('xp')
                            ->numeric()
                            ->default(0)
                            ->label('XP (Experience)'),

                        TextInput::make('rank')
                            ->default('Newbie')
                            ->placeholder('e.g. Pro, Elite, Master'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->label('Phone'),

                // Coins ကို အရောင်နဲ့ ပမာဏ ပြမယ်
                Tables\Columns\TextColumn::make('coins')
                    ->numeric()
                    ->sortable()
                    ->money('mmk') // သို့မဟုတ် ရိုးရိုး number format ထားလို့ရပါတယ်
                    ->color('warning'),

                Tables\Columns\TextColumn::make('xp')
                    ->numeric()
                    ->sortable()
                    ->label('XP')
                    ->badge(),

                Tables\Columns\TextColumn::make('rank')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}