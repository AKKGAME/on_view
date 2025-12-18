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
                        Section::make('Plan Details')
                            ->description('Define the name and benefits of the VIP plan.')
                            ->icon('heroicon-m-identification')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Plan Name')
                                    ->placeholder('e.g. Monthly VIP Pass')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true),

                                Textarea::make('description')
                                    ->label('Benefits / Description')
                                    ->placeholder("• Unlimited Anime\n• No Ads\n• 4K Quality")
                                    ->rows(5)
                                    ->helperText('List the benefits using bullet points for better readability.')
                                    ->columnSpanFull(),
                            ]),
                    ])->columnSpan(2),

                    // --- RIGHT COLUMN (Pricing & Settings) ---
                    Group::make()->schema([
                        Section::make('Pricing & Validity')
                            ->icon('heroicon-m-currency-dollar')
                            ->schema([
                                TextInput::make('coin_price')
                                    ->label('Price')
                                    ->numeric()
                                    ->prefix('🪙') // Coin Icon
                                    ->suffix('Coins')
                                    ->required()
                                    ->minValue(0)
                                    ->step(100), // ၁၀၀ ခြားစီ တိုးမယ်

                                TextInput::make('duration_days')
                                    ->label('Duration')
                                    ->numeric()
                                    ->prefixIcon('heroicon-m-calendar-days')
                                    ->suffix('Days')
                                    ->required()
                                    ->minValue(1),
                            ]),

                        Section::make('Status')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Active for Purchase')
                                    ->helperText('Enable to let users buy this plan.')
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
                // Name & Description combined
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-o-ticket')
                    ->description(fn (SubscriptionPlan $record) => \Illuminate\Support\Str::limit($record->description, 40)),

                // Price with Coin Icon styling
                Tables\Columns\TextColumn::make('coin_price')
                    ->label('Price')
                    ->formatStateUsing(fn ($state) => number_format($state)) // 1,000 ပုံစံပြမယ်
                    ->icon('heroicon-m-currency-dollar')
                    ->iconPosition('before')
                    ->color('warning') // Gold Color
                    ->sortable()
                    ->suffix(' Coins')
                    ->weight(FontWeight::Bold),

                // Duration Badge
                Tables\Columns\TextColumn::make('duration_days')
                    ->label('Duration')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state >= 365 => 'success', // 1 Year = Green
                        $state >= 30 => 'info',     // 1 Month = Blue
                        default => 'gray',          // Others = Gray
                    })
                    ->formatStateUsing(fn ($state) => "$state Days")
                    ->sortable(),

                // Toggle Active Status
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Status')
                    ->onColor('success')
                    ->offColor('danger'),

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
                Tables\Actions\EditAction::make()->slideOver(), // Slide Over နဲ့ ပွင့်မယ်
                Tables\Actions\DeleteAction::make(),
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