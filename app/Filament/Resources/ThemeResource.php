<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ThemeResource\Pages;
use App\Models\Theme;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;

class ThemeResource extends Resource
{
    protected static ?string $navigationGroup = 'System';
    protected static ?string $model = Theme::class;
    protected static ?string $navigationIcon = 'heroicon-o-paint-brush';
    protected static ?string $navigationLabel = 'App Themes';
    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('General Info')
                    ->description('Theme name and status')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->placeholder('e.g. Christmas Theme'),
                        
                        Toggle::make('is_active')
                            ->label('Set as Active Theme')
                            ->helperText('Enabling this will disable other themes.')
                            ->onColor('success')
                            ->offColor('danger'),
                    ])->columns(2),

                Section::make('Colors Palette')
                    ->schema([
                        ColorPicker::make('primary_color')->required()->label('Primary Color'),
                        ColorPicker::make('accent_color')->required()->label('Accent Color'),
                        ColorPicker::make('bg_gradient_top')->required()->label('Background Top'),
                        ColorPicker::make('bg_gradient_bottom')->required()->label('Background Bottom'),
                    ])->columns(2),

                Section::make('Content & Effects')
                    ->schema([
                        TextInput::make('greeting_text')
                            ->label('Greeting Text')
                            ->placeholder('e.g. Merry Christmas 🎄'),
                        
                        Toggle::make('enable_snow')
                            ->label('Enable Falling Snow Effect 🌨️'),

                        FileUpload::make('icon_url')
                            ->label('Holiday Icon (Optional)')
                            ->image()
                            ->directory('theme-icons')
                            ->columnSpanFull(),
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

                // အရောင်တွေကို အစက်လေးတွေနဲ့ ပြမယ်
                Tables\Columns\ColorColumn::make('primary_color'),
                Tables\Columns\ColorColumn::make('bg_gradient_top')->label('BG Top'),
                
                Tables\Columns\IconColumn::make('enable_snow')
                    ->boolean()
                    ->label('Snow Effect'),

                // Table ထဲကနေ တန်းပြီး Switch လို့ရအောင် ToggleColumn သုံးမယ်
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active Status')
                    ->onColor('success')
                    ->offColor('gray')
                    ->afterStateUpdated(function ($record, $state) {
                        if ($state) {
                            // Table ကနေ On လိုက်ရင် ကျန်တာတွေ ပိတ်မယ်
                            Theme::where('id', '!=', $record->id)->update(['is_active' => false]);
                        }
                    }),
            ])
            ->filters([])
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListThemes::route('/'),
            'create' => Pages\CreateTheme::route('/create'),
            'edit' => Pages\EditTheme::route('/{record}/edit'),
        ];
    }
}