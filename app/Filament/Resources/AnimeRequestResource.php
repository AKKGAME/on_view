<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimeRequestResource\Pages;
use App\Models\AnimeRequest;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use App\Notifications\SystemNotification;

class AnimeRequestResource extends Resource
{
    protected static ?string $navigationGroup = 'Requests';
    protected static ?string $model = AnimeRequest::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Admin က Request အသစ်လုပ်စရာမလိုပါ (User ကပဲ လုပ်မှာမို့လို့)
    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // User တင်ထားတဲ့ အချက်အလက်တွေ (မပြင်ရ - ကြည့်ရုံသက်သက်)
                Forms\Components\TextInput::make('title')
                    ->disabled()
                    ->dehydrated(false), // Database ထဲ ပြန်မထည့်ဘူး
                    
                Forms\Components\Textarea::make('note')
                    ->disabled()
                    ->dehydrated(false)
                    ->columnSpanFull(),
                
                // Admin ပြင်ရမယ့် အပိုင်း (Status)
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved (Coming Soon)',
                        'completed' => 'Completed (Uploaded)',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->selectablePlaceholder(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->wrap(), // စာရှည်ရင် အောက်ဆင်းမယ်

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'approved' => 'info',
                        'completed' => 'success',
                        'rejected' => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y, h:i A')
                    ->label('Date')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                // Edit Page မရှိတော့တဲ့အတွက် ဒီ Action က Modal (Popup) နဲ့ ပွင့်လာပါလိမ့်မယ်
                Tables\Actions\EditAction::make()
                    ->label('Update Status')
                    ->modalHeading('Update Request Status')
                    ->after(function (AnimeRequest $record) {
                        
                        // Notification Logic
                        if ($record->status === 'completed') {
                            $record->user->notify(new SystemNotification(
                                'Request Fulfilled! ✅',
                                "Good news! Your request for '{$record->title}' has been uploaded. Watch now!",
                                'success'
                            ));
                        } elseif ($record->status === 'rejected') {
                            $record->user->notify(new SystemNotification(
                                'Request Declined ❌',
                                "Sorry, we cannot fulfill your request for '{$record->title}' at this time.",
                                'error'
                            ));
                        } elseif ($record->status === 'approved') {
                            $record->user->notify(new SystemNotification(
                                'Request Approved ⏳',
                                "Your request for '{$record->title}' has been approved and is processing.",
                                'info'
                            ));
                        }

                        Notification::make()
                            ->title('Status Updated & Notification Sent')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnimeRequests::route('/'),
            // Create နဲ့ Edit စာမျက်နှာတွေကို ဖြုတ်လိုက်ပါပြီ
        ];
    }
}