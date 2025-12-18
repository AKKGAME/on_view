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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Group;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Enums\FontWeight;

class UserResource extends Resource
{
    protected static ?string $navigationGroup = 'System';
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make() // Left Column
                    ->schema([
                        Section::make('Profile Information')
                            ->icon('heroicon-m-user')
                            ->description('Basic user details and login credentials.')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->prefixIcon('heroicon-m-user')
                                    ->maxLength(255),

                                TextInput::make('phone')
                                    ->label('Phone Number')
                                    ->tel()
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->prefixIcon('heroicon-m-phone')
                                    ->maxLength(255),

                                TextInput::make('password')
                                    ->password()
                                    ->revealable() // မျက်လုံးပုံလေးနဲ့ ကြည့်လို့ရမယ်
                                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->helperText('Leave empty to keep current password'),
                            ]),

                        Section::make('Access Control')
                            ->icon('heroicon-m-shield-check')
                            ->schema([
                                // ✅ Role ရွေးလို့ရအောင် ထည့်ပေးထားသည် (Shield အတွက်)
                                Select::make('roles')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),

                                DateTimePicker::make('premium_expires_at')
                                    ->label('Premium Validity')
                                    ->native(false)
                                    ->suffixIcon('heroicon-o-calendar')
                                    ->helperText('Set a future date to give premium access.'),
                            ]),
                    ])->columnSpan(2),

                Group::make() // Right Column
                    ->schema([
                        Section::make('Game Stats')
                            ->icon('heroicon-m-trophy')
                            ->schema([
                                TextInput::make('coins')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Ks') // Icon အစား စာသားပြမယ်
                                    ->label('Wallet Balance'),

                                TextInput::make('xp')
                                    ->numeric()
                                    ->default(0)
                                    ->label('Experience (XP)'),

                                Select::make('rank')
                                    ->options([
                                        'Novice' => 'Novice',
                                        'Elite' => 'Elite',
                                        'Master' => 'Master',
                                        'GrandMaster' => 'GrandMaster',
                                        'Legend' => 'Legend',
                                    ])
                                    ->default('Novice')
                                    ->native(false),
                            ]),
                        
                        Section::make('System Info')
                            ->schema([
                                TextInput::make('created_at')
                                    ->label('Joined Date')
                                    ->disabled()
                                    ->placeholder(fn ($record) => $record?->created_at?->diffForHumans() ?? 'New User'),
                            ]),
                    ])->columnSpan(1),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->description(fn (User $record) => $record->phone) // ဖုန်းနံပါတ်ကို နာမည်အောက်မှာပြမယ်
                    ->copyable(), // Click နှိပ်ရင် ကူးယူလို့ရမယ်

                // Role ပြသခြင်း
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'super_admin' => 'danger',
                        'moderator' => 'warning',
                        default => 'primary',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('coins')
                    ->label('Balance')
                    ->money('mmk') // 1,000 MMK ပုံစံပြမယ်
                    ->color('success')
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('rank')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Legend' => 'warning', // Gold
                        'GrandMaster' => 'danger',
                        'Novice' => 'gray',
                        default => 'info',
                    }),

                // Premium Status Check (Icon Only)
                Tables\Columns\IconColumn::make('is_premium')
                    ->label('Premium')
                    ->boolean()
                    ->trueIcon('heroicon-s-star')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->getStateUsing(fn ($record) => $record->is_premium),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('premium_user')
                    ->label('Status')
                    ->trueLabel('Premium Only')
                    ->falseLabel('Free Users')
                    ->queries(
                        true: fn (Builder $query) => $query->where('premium_expires_at', '>', now()),
                        false: fn (Builder $query) => $query->where('premium_expires_at', '<=', now())->orWhereNull('premium_expires_at'),
                    ),
                
                // Role Filter
                SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                // Wallet Action တွေကို Group ဖွဲ့လိုက်ခြင်း (နေရာသက်သာစေရန်)
                ActionGroup::make([
                    Action::make('deposit')
                        ->label('Deposit Coins')
                        ->icon('heroicon-o-arrow-down-circle')
                        ->color('success')
                        ->form([
                            TextInput::make('amount')
                                ->label('Amount (MMK)')
                                ->numeric()
                                ->required()
                                ->minValue(100)
                                ->step(100)
                                ->autofocus(),
                        ])
                        ->action(function (User $record, array $data) {
                            $record->increment('coins', $data['amount']);
                            Notification::make()
                                ->title('Deposit Successful')
                                ->success()
                                ->body("Added {$data['amount']} coins to {$record->name}'s wallet.")
                                ->send();
                        }),

                    Action::make('withdraw')
                        ->label('Withdraw Coins')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('danger')
                        ->form([
                            TextInput::make('amount')
                                ->label('Amount (MMK)')
                                ->numeric()
                                ->required()
                                ->minValue(1),
                        ])
                        ->action(function (User $record, array $data) {
                            if ($record->coins < $data['amount']) {
                                Notification::make()->title('Insufficient Balance')->danger()->send();
                                return;
                            }
                            $record->decrement('coins', $data['amount']);
                            Notification::make()
                                ->title('Withdrawal Successful')
                                ->success()
                                ->body("Removed {$data['amount']} coins from {$record->name}.")
                                ->send();
                        }),
                ])
                ->label('Wallet')
                ->icon('heroicon-m-banknotes')
                ->color('warning'),

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
            // Transaction တွေကြည့်ချင်ရင် Relation Manager ထည့်လို့ရပါတယ်
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