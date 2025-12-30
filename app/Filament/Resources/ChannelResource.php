<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChannelResource\Pages;
use App\Filament\Resources\ChannelResource\RelationManagers;
use App\Models\Channel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Form Components
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Set;
use Illuminate\Support\Str;
// Table Components
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;

class ChannelResource extends Resource
{
    protected static ?string $model = Channel::class;

    protected static ?string $navigationIcon = 'heroicon-o-tv'; // Icon ပြောင်းထားပါတယ်
    
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Channel Information')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true) // Name ရိုက်ပြီးတာနဲ့ Slug ကို Auto ဖြည့်မယ်
                                ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                            
                            TextInput::make('slug')
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true),
                        ]),

                        FileUpload::make('logo')
                            ->image()
                            ->directory('channels') // storage/app/public/channels ထဲမှာ သိမ်းမယ်
                            ->imageEditor()
                            ->circleCropper()
                            ->columnSpanFull(),
                    ]),

                Section::make('Social Links')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('telegram_url')
                                ->label('Telegram Link')
                                ->prefixIcon('heroicon-m-paper-airplane')
                                ->url()
                                ->placeholder('https://t.me/...'),
                            
                            TextInput::make('facebook_url')
                                ->label('Facebook Link')
                                ->prefixIcon('heroicon-m-hand-thumb-up')
                                ->url()
                                ->placeholder('https://facebook.com/...'),
                                
                            TextInput::make('website_url')
                                ->label('Website Link')
                                ->prefixIcon('heroicon-m-globe-alt')
                                ->url()
                                ->placeholder('https://example.com'),
                        ]),
                    ]),

                Section::make('Status')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active Channel')
                            ->default(true)
                            ->helperText('If disabled, this channel will be hidden.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')), // Placeholder ပုံရှိရင် ထည့်ထားလို့ရပါတယ်
                
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('slug')
                    ->searchable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true), // ပုံမှန်ဆို ဖျောက်ထားမယ်

                TextColumn::make('telegram_url')
                    ->icon('heroicon-m-link')
                    ->limit(20)
                    ->toggleable(),

                ToggleColumn::make('is_active')
                    ->label('Active'),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Active ဖြစ်တာတွေပဲ စစ်ထုတ်ချင်ရင်
                Tables\Filters\TernaryFilter::make('is_active'),
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
            // နောက်ပိုင်း Channel မှာ Anime တွေဘယ်လောက်ရှိလဲ ပြချင်ရင် Relation Manager ထည့်လို့ရပါတယ်
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChannels::route('/'),
            'create' => Pages\CreateChannel::route('/create'),
            'edit' => Pages\EditChannel::route('/{record}/edit'),
        ];
    }
}