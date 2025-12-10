<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AppVersionResource\Pages;
use App\Models\AppVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;

class AppVersionResource extends Resource
{
    protected static ?string $model = AppVersion::class;

    // Admin Panel ဘေးဘားမှာ ပေါ်မယ့် Icon (ကြိုက်တာပြောင်းလို့ရပါတယ်)
    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';
    
    // Menu Group နာမည် (Optional)
    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('App Version Details')
                    ->description('Manage application update versions here.')
                    ->schema([
                        Grid::make(2) // 2 Columns
                            ->schema([
                                // 1. Version Code
                                Forms\Components\TextInput::make('version_code')
                                    ->label('Version Number')
                                    ->placeholder('e.g., 1.0.2')
                                    ->required()
                                    ->maxLength(255),

                                // 2. Platform (Android/iOS)
                                Forms\Components\Select::make('platform')
                                    ->options([
                                        'android' => 'Android',
                                        'ios' => 'iOS',
                                    ])
                                    ->default('android')
                                    ->required(),
                            ]),

                        // 3. Download URL (Direct Link)
                        Forms\Components\TextInput::make('download_url')
                            ->label('Direct Download URL')
                            ->placeholder('https://your-server.com/app-v1.0.2.apk')
                            ->url() // URL format စစ်ပေးမယ်
                            ->required()
                            ->columnSpanFull(), // နေရာအပြည့်ယူမယ်

                        // 4. Update Message
                        Forms\Components\Textarea::make('message')
                            ->label('Update Note / Message')
                            ->placeholder('Bug fixes and performance improvements.')
                            ->rows(3)
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                // 5. Force Update Switch
                                Forms\Components\Toggle::make('force_update')
                                    ->label('Force Update?')
                                    ->helperText('User must update to continue using the app.')
                                    ->onColor('danger') // အနီရောင်ပြမယ်
                                    ->default(false),

                                // 6. Active Status Switch
                                Forms\Components\Toggle::make('is_active')
                                    ->label('Set as Active Version')
                                    ->helperText('This version will be checked by the app.')
                                    ->onColor('success')
                                    ->default(true),
                            ]),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Version
                Tables\Columns\TextColumn::make('version_code')
                    ->sortable()
                    ->searchable()
                    ->weight('bold'),

                // Platform Badge
                Tables\Columns\TextColumn::make('platform')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'android' => 'success',
                        'ios' => 'info',
                        default => 'gray',
                    }),

                // Force Update Icon
                Tables\Columns\IconColumn::make('force_update')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('danger')
                    ->label('Forced?'),

                // Active Switch (Table ပေါ်ကနေ တန်းပိတ်လို့ရအောင် ToggleColumn သုံးထားသည်)
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Platform အလိုက် Filter စစ်ချင်ရင်
                Tables\Filters\SelectFilter::make('platform')
                    ->options([
                        'android' => 'Android',
                        'ios' => 'iOS',
                    ]),
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
            // Default Sorting (Latest first)
            ->defaultSort('id', 'desc'); 
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
            'index' => Pages\ListAppVersions::route('/'),
            'create' => Pages\CreateAppVersion::route('/create'),
            'edit' => Pages\EditAppVersion::route('/{record}/edit'),
        ];
    }
}