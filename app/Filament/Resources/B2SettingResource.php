<?php

namespace App\Filament\Resources;

// ⚠️ FIX 1: Missing Pages Import
use App\Filament\Resources\B2SettingResource\Pages; 
use App\Models\B2Setting;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // For query methods
use Illuminate\Database\Eloquent\Model;

class B2SettingResource extends Resource
{
    protected static ?string $model = B2Setting::class;
    protected static ?string $navigationIcon = 'heroicon-o-cloud';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $label = 'Cloud Settings';
    
    // -----------------------------------------------------------------------
    // 1. FORM (Settings Input)
    // -----------------------------------------------------------------------
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Backblaze B2 / AWS S3 Configuration')
                    ->description('These keys are used for all cloud file storage.')
                    ->schema([
                        Forms\Components\TextInput::make('b2_access_key')
                            ->label('Application Key ID (Access Key)')
                            ->required()
                            ->columnSpan(1),
                        
                        Forms\Components\TextInput::make('b2_secret_key')
                            ->label('Application Key (Secret Key)')
                            ->password()
                            ->revealable()
                            ->required()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('b2_bucket')
                            ->label('Bucket Name')
                            ->required(),
                            
                        Forms\Components\TextInput::make('b2_default_region')
                            ->label('Region')
                            ->required(),
                        
                        Forms\Components\TextInput::make('b2_endpoint')
                            ->label('Endpoint URL')
                            ->url()
                            ->required(),
                    ])->columns(2)
                    // Note: You can add an Action here to clear the cache after saving!
            ]);
    }
    
    // -----------------------------------------------------------------------
    // 2. TABLE (Minimal setup to allow redirect)
    // -----------------------------------------------------------------------
    
    public static function table(Table $table): Table
    {
        // This table is not primarily used, but required for Filament structure
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
            ])
            ->actions([
                // Must have at least one action for the table to function
                Tables\Actions\Action::make('edit_settings')
                    ->url(fn () => static::getUrl('edit', ['record' => 1]))
                    ->icon('heroicon-o-pencil-square'),
            ]);
    }
    
    // -----------------------------------------------------------------------
    // 3. NAVIGATION OVERRIDES (The Fix)
    // -----------------------------------------------------------------------

    // FIX 2: Override getUrl() to link the sidebar directly to the edit page for ID 1
    public static function getUrl(string $name = 'index', array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null): string
    {
        // Index route ခေါ်လာရင် Edit Page (Record 1) ဆီကို Redirect လုပ်မယ်
        if ($name === 'index') {
            return static::getUrl('edit', ['record' => 1], $isAbsolute, $panel, $tenant); 
        }
        
        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant);
    }
    
    // Update getPages() to use the standard route path
    public static function getPages(): array
    {
        return [
            // List page is intentionally omitted
            'edit' => Pages\EditB2Setting::route('/{record}/edit'), 
        ];
    }

}