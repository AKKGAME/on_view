<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Filament\Resources\UserResource;
use Filament\Support\Enums\FontWeight;

class LatestUsers extends BaseWidget
{
    // Widget ခေါင်းစဉ် (ဒါကိုဖြုတ်ချင်ရင် null ပေးနိုင်ပါတယ်)
    protected static ?string $heading = 'New Joiners';

    protected static ?int $sort = 3;
    
    // နေရာအပြည့်မယူဘဲ ဘေးကပ်လျက်နေအောင် 1 လို့ထားပါတယ်
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                // နောက်ဆုံး အကောင့်ဖွင့်ထားသူ ၅ ဦး
                User::query()->latest()->limit(5)
            )
            ->columns([
                // 1. User Info (Icon + Name + Phone)
                Tables\Columns\TextColumn::make('name')
                    ->label(null) // Header ခေါင်းစဉ် ဖျောက်မယ်
                    ->weight(FontWeight::Bold)
                    ->icon('heroicon-m-user-circle') // ရှေ့ဆုံးက လူပုံ Icon
                    ->iconColor('primary')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Medium)
                    ->description(fn (User $record): string => $record->phone ?? 'No Phone'), // ဖုန်းနံပါတ်ကို အောက်မှာပြမယ်

                // 2. Joined Time (Right Aligned)
                Tables\Columns\TextColumn::make('created_at')
                    ->label(null) // Header ဖျောက်မယ်
                    ->since() // "5 mins ago" ပုံစံပြမယ်
                    ->color('gray')
                    ->alignRight()
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Small),
            ])
            ->paginated(false) // စာမျက်နှာ ခွဲတာတွေ ဖြုတ်မယ်
            ->recordUrl(
                // Row ကိုနှိပ်လိုက်ရင် User Edit Page ကိုသွားမယ်
                fn (User $record): string => UserResource::getUrl('edit', ['record' => $record])
            );
    }
}