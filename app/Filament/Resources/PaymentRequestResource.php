<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentRequestResource\Pages;
use App\Models\PaymentRequest;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use App\Notifications\SystemNotification; // Notification Class ကို Import လုပ်ထားပါတယ်

class PaymentRequestResource extends Resource
{
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $model = PaymentRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Top-up Requests';

    // Admin က ဖန်တီးစရာမလိုဘူး (User ကပဲ တင်မှာမို့လို့ Create ကို ပိတ်ထားမယ်)
    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('User')->searchable(),
                Tables\Columns\TextColumn::make('user.phone')->label('Phone'),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kpay' => 'success',
                        'wave' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->money('mmk')
                    ->color('warning')
                    ->weight('bold'),

                Tables\Columns\ImageColumn::make('screenshot_path')
                    ->disk('public')
                    ->height(80),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // APPROVE ACTION (with Notification)
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (PaymentRequest $record) => $record->status === 'pending')
                    ->action(function (PaymentRequest $record) {
                        // ၁. User ကို Coin တိုးပေးမယ်
                        $record->user->increment('coins', $record->amount);

                        // ၂. Transaction History မှာ မှတ်တမ်းတင်မယ်
                        Transaction::create([
                            'user_id' => $record->user_id,
                            'type' => 'topup',
                            'amount' => $record->amount,
                            'description' => 'Top-up via ' . $record->payment_method,
                        ]);

                        // ၃. Status ကို Approved ပြောင်းမယ်
                        $record->update(['status' => 'approved']);

                        // ၄. User ဆီ Notification ပို့မယ်
                        $record->user->notify(new SystemNotification(
                            'Top-up Approved! 💎',
                            "You have received " . number_format($record->amount) . " coins.",
                            'success'
                        ));

                        // Admin ကို Success ပြမယ်
                        Notification::make()->title('Approved & Coins Added')->success()->send();
                    }),

                // REJECT ACTION (with Notification)
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (PaymentRequest $record) => $record->status === 'pending')
                    ->action(function (PaymentRequest $record) {
                        $record->update(['status' => 'rejected']);

                        // User ဆီ Notification ပို့မယ်
                        $record->user->notify(new SystemNotification(
                            'Top-up Rejected ❌',
                            "Your request for " . number_format($record->amount) . " coins was declined.",
                            'error'
                        ));

                        // Admin ကို Success ပြမယ်
                        Notification::make()->title('Request Rejected')->danger()->send();
                    }),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentRequests::route('/'),
        ];
    }
}