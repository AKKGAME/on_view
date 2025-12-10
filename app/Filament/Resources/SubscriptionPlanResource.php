<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubscriptionPlanResource\Pages;
use App\Models\SubscriptionPlan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class SubscriptionPlanResource extends Resource
{
    // Model သတ်မှတ်ခြင်း
    protected static ?string $model = SubscriptionPlan::class;

    // Sidebar Icon (Heroicons)
    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    // Sidebar Group Name (Optional)
    protected static ?string $navigationGroup = 'Finance';

    // Label on Sidebar
    protected static ?string $navigationLabel = 'VIP Plans';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Plan Details')
                    ->description('Create premium subscription plans for users.')
                    ->schema([
                        // 1. Plan Name
                        Forms\Components\TextInput::make('name')
                            ->label('Plan Name')
                            ->placeholder('e.g., 1 Month VIP')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            // 2. Price in Coins
                            Forms\Components\TextInput::make('coin_price')
                                ->label('Price (Coins)')
                                ->numeric() // ဂဏန်းပဲရိုက်လို့ရမယ်
                                ->prefixIcon('heroicon-o-currency-dollar')
                                ->required()
                                ->minValue(0),

                            // 3. Duration in Days
                            Forms\Components\TextInput::make('duration_days')
                                ->label('Duration (Days)')
                                ->numeric()
                                ->suffix('Days')
                                ->required()
                                ->minValue(1),
                        ]),

                        // 4. Description
                        Forms\Components\Textarea::make('description')
                            ->label('Description / Benefits')
                            ->placeholder('e.g., Unlimited access to all anime and comics.')
                            ->rows(3)
                            ->columnSpanFull(),

                        // 5. Active Switch
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Plan')
                            ->helperText('If disabled, users cannot buy this plan.')
                            ->default(true)
                            ->onColor('success'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Plan Name
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                // Coin Price
                Tables\Columns\TextColumn::make('coin_price')
                    ->label('Price')
                    ->suffix(' Coins')
                    ->sortable()
                    ->color('warning'), // ရွှေရောင်စာလုံးပြမယ်

                // Duration
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->suffix(' Days')
                    ->sortable(),

                // Active Status (Toggle Column - Table ပေါ်ကနေ တန်းပိတ်လို့ရအောင်)
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter: Active ဖြစ်တာပဲ ကြည့်ချင်ရင်
                Tables\Filters\Filter::make('active')
                    ->query(fn ($query) => $query->where('is_active', true))
                    ->label('Active Plans Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('coin_price', 'asc'); // ဈေးအနည်းဆုံးကနေ စီပြမယ်
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
            'index' => Pages\ListSubscriptionPlans::route('/'),
            'create' => Pages\CreateSubscriptionPlan::route('/create'),
            'edit' => Pages\EditSubscriptionPlan::route('/{record}/edit'),
        ];
    }
}