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
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use App\Notifications\SystemNotification;
use Filament\Support\Enums\FontWeight;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage; // ✅ Storage ကို Import လုပ်ရပါမယ်

class PaymentRequestResource extends Resource
{
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $model = PaymentRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Top-up Requests';
    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'pending')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (PaymentRequest $record) => $record->user?->phone ?? '-')
                    ->icon('heroicon-m-user-circle'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('mmk')
                    ->weight(FontWeight::ExtraBold)
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'kpay' => 'heroicon-m-credit-card',
                        'wave' => 'heroicon-m-device-phone-mobile',
                        default => 'heroicon-m-banknotes',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'kpay' => 'info',
                        'wave' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),

                // ✅ FIX: Screenshot ပြင်ဆင်ချက်
                Tables\Columns\ImageColumn::make('screenshot_path')
                    ->label('Proof (Click to view)')
                    ->disk('public')
                    ->square()
                    ->height(80) // ပုံကို အနည်းငယ် ပိုကြီးထားသည်
                    ->width(80)
                    ->extraImgAttributes(['class' => 'object-cover rounded-lg shadow-md border border-gray-200 cursor-pointer hover:opacity-80 transition-opacity'])
                    // ✅ ဤနေရာသည် အရေးကြီးသည် - ပုံကိုနှိပ်ပါက Tab အသစ်ဖြင့် ဖွင့်မည်
                    ->url(fn (PaymentRequest $record) => Storage::disk('public')->url($record->screenshot_path))
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->icon(fn (string $state): string => match ($state) {
                        'pending' => 'heroicon-m-clock',
                        'approved' => 'heroicon-m-check-circle',
                        'rejected' => 'heroicon-m-x-circle',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime('d M Y, h:i A')
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->native(false),
                
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'kpay' => 'KBZ Pay',
                        'wave' => 'Wave Pay',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                ActionGroup::make([
                    Action::make('approve')
                        ->label('Approve Request')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve Top-up')
                        ->modalDescription('Are you sure you want to approve this request? Coins will be added to the user.')
                        ->action(function (PaymentRequest $record) {
                            Transaction::create([
                                'user_id' => $record->user_id,
                                'type' => 'topup',
                                'amount' => $record->amount,
                                'description' => 'Top-up via ' . strtoupper($record->payment_method),
                            ]);

                            $record->user->increment('coins', $record->amount);
                            $record->update(['status' => 'approved']);

                            try {
                                $record->user->notify(new SystemNotification(
                                    'Top-up Successful! 💎',
                                    "You have received " . number_format($record->amount) . " coins.",
                                    'success'
                                ));
                            } catch (\Exception $e) {}

                            Notification::make()->title('Approved successfully')->success()->send();
                        }),

                    Action::make('reject')
                        ->label('Reject Request')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject Top-up')
                        ->form([
                            Forms\Components\Textarea::make('reason')
                                ->label('Rejection Reason')
                                ->placeholder('e.g. Invalid screenshot / Transaction ID not found')
                                ->required(),
                        ])
                        ->action(function (PaymentRequest $record, array $data) {
                            $record->update(['status' => 'rejected']);

                            try {
                                $record->user->notify(new SystemNotification(
                                    'Top-up Rejected ❌',
                                    "Reason: " . $data['reason'],
                                    'error'
                                ));
                            } catch (\Exception $e) {}

                            Notification::make()->title('Request rejected')->danger()->send();
                        }),
                ])
                ->visible(fn (PaymentRequest $record) => $record->status === 'pending')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->tooltip('Manage Request'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort(fn ($query) => $query->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END")->orderBy('created_at', 'desc'));
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentRequests::route('/'),
        ];
    }
}