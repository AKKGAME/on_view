<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SystemSettingResource\Pages;
use App\Filament\Resources\SystemSettingResource\RelationManagers;
use App\Models\SystemSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SystemSettingResource extends Resource
{
    protected static ?string $navigationGroup = 'System';
    protected static ?string $model = SystemSetting::class;
    protected static ?int $navigationSort = 6;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('label')
                ->disabled() // နာမည်ကို မပြင်စေချင်လို့
                ->columnSpanFull(),
            Forms\Components\TextInput::make('value')
                ->label('Coin Amount')
                ->numeric()
                ->required(),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('label')->label('Setting Name'),
            Tables\Columns\TextColumn::make('value')->label('Value (Coins)')->weight('bold'),
        ])
        // အသစ်ထပ်မထည့်စေချင်၊ မဖျက်စေချင်ရင် ဒါတွေပိတ်ထားပါ
        ->actions([ Tables\Actions\EditAction::make() ]) 
        ->paginated(false);
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
            'index' => Pages\ListSystemSettings::route('/'),
            'create' => Pages\CreateSystemSetting::route('/create'),
            'edit' => Pages\EditSystemSetting::route('/{record}/edit'),
        ];
    }
}
