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
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Support\Enums\FontWeight;

class SubscriptionPlanResource extends Resource
{
    protected static ?string $model = SubscriptionPlan::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'VIP Plans';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    // --- LEFT COLUMN (General Info) ---
                    Group::make()->schema([
                        Section::make('Plan Information')
                            ->description('Define the branding and benefits of this plan.')
                            ->icon('heroicon-m-swatch')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Plan Title')
                                    ->placeholder('e.g. Monthly VIP Pass')
                                    ->required()
                                    ->maxLength(255)
                                    ->prefixIcon('heroicon-m-ticket')
                                    ->live(onBlur: true),

                                Textarea::make('description')
                                    ->label('Plan Benefits')
                                    ->placeholder("• Watch Unlimited Anime\n• Ad-free Experience\n• 4K Resolution")
                                    ->rows(6)
                                    ->helperText('Use bullet points for better readability in the app.')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(2),

                    // --- RIGHT COLUMN (Settings) ---
                    Group::make()->schema([
                        Section::make('Pricing & Terms')
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                TextInput::make('coin_price')
                                    ->label('Price (Coins)')
                                    ->numeric()
                                    ->prefixIcon('heroicon-o-currency-dollar') // Icon အစားထိုး
                                    ->required()
                                    ->minValue(0)
                                    ->step(100),

                                TextInput::make('duration_days')
                                    ->label('Duration (Days)')
                                    ->numeric()
                                    ->prefixIcon('heroicon-m-calendar')
                                    ->suffix('Days')
                                    ->required()
                                    ->minValue(1)
                                    ->helperText('30 = 1 Month, 365 = 1 Year'),
                            ]),

                        Section::make('Availability')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active Plan')
                                    ->helperText('Turn off to hide this plan from users.')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('danger')
                                    ->onIcon('heroicon-m-check')
                                    ->offIcon('heroicon-m-x-mark'),
                            ]),
                    ])->columnSpan(1),
                ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Plan Name
                Tables\Columns\TextColumn::make('name')
                    ->label('Plan Name')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-o-ticket')
                    ->iconColor('primary')
                    ->description(fn (SubscriptionPlan $record) => \Illuminate\Support\Str::limit($record->description, 50)),

                // 2. Price (Coins)
                Tables\Columns\TextColumn::make('coin_price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => number_format($state))
                    ->icon('heroicon-s-currency-dollar') // Solid Icon
                    ->iconColor('warning') // Gold color
                    ->color('warning')
                    ->suffix(' Coins')
                    ->sortable()
                    ->weight(FontWeight::ExtraBold),

                // 3. Duration (Smart Formatting)
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 365 => 'success', // 1 Year = Green
                        $state >= 30 => 'info',     // 1 Month = Blue
                        $state >= 7 => 'warning',   // 1 Week = Orange
                        default => 'gray',
                    })
                    // ✅ ရက် ၃၀ ဆိုရင် "1 Month" လို့ ပြောင်းပြမယ့် Logic
                    ->formatStateUsing(fn ($state) => match ((int)$state) {
                        365 => '1 Year',
                        30 => '1 Month',
                        7 => '1 Week',
                        1 => '1 Day',
                        default => "$state Days",
                    })
                    ->sortable()
                    ->alignCenter(),

                // 4. Status Toggle
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->trueLabel('Active Plans')
                    ->falseLabel('Inactive Plans'),
            ])
            ->actions([
                // Actions ကို Icon Button တွေပြောင်းလိုက်ခြင်း
                Tables\Actions\EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit Plan')
                    ->color('primary')
                    ->slideOver(),
                
                Tables\Actions\DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete Plan')
                    ->color('danger'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('coin_price', 'asc');
    }

    public static function getRelations(): array
    {
        return [];
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