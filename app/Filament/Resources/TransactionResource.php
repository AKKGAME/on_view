<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Finance'; // Sidebar Group

    // Admin က Transaction အသစ် ဖန်တီးခွင့် မပြုပါ (System ကပဲ လုပ်မယ်)
    public static function canCreate(): bool
    {
        return false;
    }

    // Form က မလိုတော့ပေမယ့် Error မတက်အောင် အလွတ်ထားခဲ့ပါ
    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'topup' => 'success',
                        'purchase' => 'warning',
                        'ad_reward' => 'info',
                        'referral_bonus' => 'primary',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->numeric()
                    ->money('mmk') // သို့မဟုတ် ရိုးရိုး number format
                    // အဝင် (Topup, Reward) ဆို အစိမ်း၊ အထွက် (Purchase) ဆို အနီ ပြမယ်
                    ->color(fn (Transaction $record): string => in_array($record->type, ['purchase']) ? 'danger' : 'success') 
                    ->prefix(fn (Transaction $record): string => in_array($record->type, ['purchase']) ? '- ' : '+ '),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->tooltip(fn (Transaction $record): string => $record->description ?? ''),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, h:i A')
                    ->label('Date')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'topup' => 'Top Up',
                        'purchase' => 'Purchase',
                        'ad_reward' => 'Ad Reward',
                        'referral_bonus' => 'Referral Bonus',
                    ]),
            ])
            // Edit နဲ့ Delete Action တွေကို ဖယ်လိုက်ပါပြီ
            ->actions([
                // လိုအပ်ရင် ViewAction ထည့်လို့ရပါတယ် (Popup နဲ့ အပြည့်အစုံကြည့်ဖို့)
                // Tables\Actions\ViewAction::make(), 
            ])
            ->bulkActions([
                // အများကြီး တပြိုင်နက်ဖျက်တာကိုလည်း ပိတ်ထားသင့်ပါတယ်
                // Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTransactions::route('/'),
            // Create နဲ့ Edit route တွေကို ဖြုတ်လိုက်ပါပြီ
        ];
    }
}