<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CoinCouponResource\Pages;
use App\Models\CoinCoupon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CoinCouponResource extends Resource
{
    protected static ?string $model = CoinCoupon::class;
    protected static ?string $navigationIcon = 'heroicon-o-gift';
    protected static ?string $navigationGroup = 'Promotion Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Coupon Details')->schema([
                    Forms\Components\TextInput::make('code')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(20)
                        // 🔥 ပြင်ဆင်ထားသောအပိုင်း (၁): CSS ဖြင့် အကြီးစာလုံးပြခြင်း
                        ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                        // 🔥 ပြင်ဆင်ထားသောအပိုင်း (၂): Save လုပ်ချိန်တွင် PHP ဖြင့် အကြီးစာလုံးပြောင်းခြင်း
                        ->dehydrateStateUsing(fn (string $state): string => strtoupper($state))
                        ->suffixAction(
                            Forms\Components\Actions\Action::make('generate')
                                ->icon('heroicon-m-arrow-path')
                                ->action(function (Forms\Set $set) {
                                    $set('code', strtoupper(Str::random(8)));
                                })
                        ),

                    Forms\Components\TextInput::make('coin_amount')
                        ->required()
                        ->numeric()
                        ->prefix('🪙')
                        ->label('Coin Amount'),

                    Forms\Components\TextInput::make('usage_limit')
                        ->numeric()
                        ->label('Total Usage Limit')
                        ->placeholder('Leave empty for unlimited')
                        ->helperText('လူဘယ်နှယောက် အသုံးပြုခွင့်ပေးမလဲ။'),

                    Forms\Components\DateTimePicker::make('expires_at')
                        ->label('Expiry Date'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->onColor('success')
                        ->offColor('danger'),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('coin_amount')
                    ->label('Coins')
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('used_count')
                    ->label('Used')
                    ->formatStateUsing(fn ($record) => $record->used_count . ' / ' . ($record->usage_limit ?? '∞')),

                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('No Expiry'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoinCoupons::route('/'),
            'create' => Pages\CreateCoinCoupon::route('/create'),
            'edit' => Pages\EditCoinCoupon::route('/{record}/edit'),
        ];
    }
}