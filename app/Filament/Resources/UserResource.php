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
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $navigationGroup = 'System';
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3) // Column 3 ခုခွဲမယ်
                    ->schema([
                        // Left Side (Account Info) - နေရာ ၂ ယူမယ်
                        Section::make('Account Information')
                            ->description('Login credentials & personal info')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->password()
                                    // Password ရိုက်မှ Hash လုပ်ပြီးသိမ်းမယ်
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create'),
                            ])->columnSpan(2),

                        // Right Side (Gaming & VIP Status) - နေရာ ၁ ယူမယ်
                        Section::make('Status & Stats')
                            ->schema([
                                TextInput::make('coins')
                                    ->numeric()
                                    ->default(0)
                                    ->prefixIcon('heroicon-o-currency-dollar')
                                    ->label('Coins Balance'),

                                TextInput::make('xp')
                                    ->numeric()
                                    ->default(0)
                                    ->label('XP Points'),

                                TextInput::make('rank')
                                    ->default('Newbie')
                                    ->placeholder('e.g. Elite'),

                                DateTimePicker::make('premium_expires_at')
                                    ->label('Premium Expiry')
                                    ->native(false)
                                    ->suffixIcon('heroicon-o-calendar'),
                            ])->columnSpan(1),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-m-phone'),

                // Premium Status Check
                Tables\Columns\TextColumn::make('premium_expires_at')
                    ->label('Status')
                    ->formatStateUsing(function ($state) {
                        return $state && now()->lt($state) ? 'Premium' : 'Free';
                    })
                    ->badge()
                    ->color(fn ($state) => $state && now()->lt($state) ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('coins')
                    ->numeric(decimalPlaces: 0)
                    ->sortable()
                    ->prefix('Ks ')
                    ->color('warning'),

                Tables\Columns\TextColumn::make('xp')
                    ->sortable()
                    ->label('XP'),

                Tables\Columns\TextColumn::make('rank')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Premium User Filter
                TernaryFilter::make('premium_user')
                    ->label('User Type')
                    ->placeholder('All Users')
                    ->trueLabel('Premium Users')
                    ->falseLabel('Free Users')
                    ->queries(
                        true: fn (Builder $query) => $query->where('premium_expires_at', '>', now()),
                        false: fn (Builder $query) => $query->where('premium_expires_at', '<=', now())->orWhereNull('premium_expires_at'),
                    ),
            ])
            ->actions([
                // 1. Coin ဖြည့်ပေးမယ့် Action (Deposit)
                Action::make('deposit')
                    ->label('Deposit')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Coin ဖြည့်မယ်')
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount to Deposit')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->prefix('Ks'),
                    ])
                    ->action(function (User $record, array $data) {
                        $record->increment('coins', $data['amount']);
                        
                        Notification::make()
                            ->title('Coins Deposited')
                            ->success()
                            ->body("Added {$data['amount']} coins to {$record->name}.")
                            ->send();
                    }),

                // 2. Coin နှုတ်မယ့် Action (Withdraw)
                Action::make('withdraw')
                    ->label('Withdraw')
                    ->icon('heroicon-o-minus-circle')
                    ->color('danger')
                    ->iconButton()
                    ->tooltip('Coin နှုတ်မယ်')
                    ->form([
                        TextInput::make('amount')
                            ->label('Amount to Withdraw')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->prefix('Ks'),
                    ])
                    ->action(function (User $record, array $data) {
                        // လက်ရှိ Coin လောက်ငမလောက် စစ်မယ်
                        if ($record->coins < $data['amount']) {
                            Notification::make()
                                ->title('Insufficient Balance')
                                ->danger()
                                ->body("User only has {$record->coins} coins.")
                                ->send();
                            return;
                        }

                        $record->decrement('coins', $data['amount']);

                        Notification::make()
                            ->title('Coins Withdrawn')
                            ->success()
                            ->body("Removed {$data['amount']} coins from {$record->name}.")
                            ->send();
                    }),

                // 3. Edit Action
                Tables\Actions\EditAction::make(),
                
                // 4. Delete Action
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}