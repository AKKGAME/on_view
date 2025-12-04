<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\FileUpload; // Image Upload အတွက်
use Filament\Forms\Components\DateTimePicker; // Date/Time အတွက်
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;


class BannerResource extends Resource
{
    // Model ကို သတ်မှတ်သည်
    protected static ?string $model = Banner::class;

    // Side Bar Icon
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'System';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Banner Details')
                    ->columns(2)
                    ->schema([
                        // 1. Internal Name
                        TextInput::make('name')
                            ->label('Internal Name')
                            ->maxLength(255),
                            
                        // 2. Display Order
                        TextInput::make('order')
                            ->label('Display Order (Lower = Higher Priority)')
                            ->numeric()
                            ->default(0)
                            ->columnSpan(1),

                        // 3. Image Upload
                        FileUpload::make('image_url')
                            ->label('Banner Image (16:9 or 4:3 Ratio Recommended)')
                            ->disk('public')
                            ->directory('banners') // Storage/app/public/banners ထဲမှာ သိမ်းမည်
                            ->image()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->columnSpanFull(),
                        
                        // 4. External Link URL
                        TextInput::make('link_url')
                            ->label('External Link URL (e.g., website or app store)')
                            ->url()
                            ->maxLength(500)
                            ->placeholder('https://www.example.com')
                            ->columnSpanFull(),
                            
                    ]),
                    
                Forms\Components\Section::make('Activation & Scheduling')
                    ->columns(3)
                    ->schema([
                        // 5. Active Toggle
                        Toggle::make('is_active')
                            ->label('Is Active / Display Banner')
                            ->default(true)
                            ->columnSpan(1),
                            
                        // 6. Start Date
                        DateTimePicker::make('start_date')
                            ->label('Start Date (Optional)')
                            ->helperText('Banner will start showing on this date.')
                            ->nullable(),
                            
                        // 7. End Date
                        DateTimePicker::make('end_date')
                            ->label('End Date (Optional)')
                            ->helperText('Banner will stop showing on this date.')
                            ->afterOrEqual('start_date')
                            ->nullable(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),
                    
                ImageColumn::make('image_url')
                    ->label('Image')
                    ->width(100)
                    ->height(50),
                    
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable(),
                    
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->dateTime()
                    ->sortable(),
                    
                TextColumn::make('end_date')
                    ->label('End Date')
                    ->dateTime()
                    ->sortable(),
                    
                TextColumn::make('link_url')
                    ->label('Link')
                    ->limit(30)
                    ->tooltip(fn (Banner $record): string => $record->link_url ?? 'No Link'),
            ])
            ->filters([
                // Active / Inactive filter
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->trueLabel('Active')
                    ->falseLabel('Inactive')
                    ->nullable(),
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
            ->defaultSort('order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            // No relations needed for the Banner model yet
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
    
    // Global search ကို ပိတ်ထားသည်
    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}