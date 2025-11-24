<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentMethodResource\Pages;
use App\Models\PaymentMethod;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Forms\Set;

class PaymentMethodResource extends Resource
{
    protected static ?string $model = PaymentMethod::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    
    protected static ?string $navigationGroup = 'Finance'; // Finance Group အောက်မှာထားမယ်
    protected static ?string $navigationLabel = 'Payment Methods';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Payment Details')
                    ->schema([
                        // Name ရိုက်ရင် Slug Auto ပေါ်မယ်
                        Forms\Components\TextInput::make('name')
                            ->label('Method Name (e.g. KBZ Pay)')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->readOnly(),

                        Forms\Components\TextInput::make('account_name')
                            ->label('Account Name')
                            ->placeholder('e.g. OnView Official')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('account_number')
                            ->label('Account / Phone Number')
                            ->placeholder('e.g. 09123456789')
                            ->required()
                            ->maxLength(255),

                        // UI အရောင်ရွေးဖို့
                        Forms\Components\Select::make('color_class')
                            ->label('Theme Color')
                            ->options([
                                'blue' => 'Blue (KBZ Pay Style)',
                                'yellow' => 'Yellow (Wave Pay Style)',
                                'red' => 'Red (AYA Pay Style)',
                                'green' => 'Green (KPlus Style)',
                                'purple' => 'Purple (Theme Default)',
                            ])
                            ->default('blue')
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Status')
                            ->default(true)
                            ->helperText('Turn off to hide this method from users.'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('account_name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('account_number')
                    ->copyable() // နှိပ်ရင် Copy ကူးလို့ရမယ်
                    ->icon('heroicon-m-clipboard'),

                Tables\Columns\TextColumn::make('color_class')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'blue' => 'info',
                        'yellow' => 'warning',
                        'red' => 'danger',
                        'green' => 'success',
                        default => 'primary',
                    }),

                // Table ကနေ တန်းပြီး ပိတ်/ဖွင့် လုပ်လို့ရမယ့် Toggle
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            'index' => Pages\ListPaymentMethods::route('/'),
            'create' => Pages\CreatePaymentMethod::route('/create'),
            'edit' => Pages\EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}