<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\PaymentRequest;
use App\Models\AnimeRequest;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1; // အပေါ်ဆုံးမှာထားမယ်

    protected function getStats(): array
    {
        // ဒီလဝင်ငွေ
        $monthlyIncome = PaymentRequest::where('status', 'approved')
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('amount');
            
        // မနေ့ကထက် ဘယ်လောက်တိုးလဲ (ဥပမာ တွက်ပြခြင်း)
        // တကယ့် Logic မှာတော့ ရှေ့လနဲ့ နှိုင်းယှဉ်ရမယ်
        
        return [
            Stat::make('Total Revenue (This Month)', number_format($monthlyIncome) . ' Ks')
                ->description('32% increase')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17]) // အလှဆင် Graph
                ->color('success'),

            Stat::make('Total Users', User::count())
                ->description('New players joined')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([15, 4, 10, 2, 12, 4, 12])
                ->color('primary'),

            Stat::make('Pending Top-ups', PaymentRequest::where('status', 'pending')->count())
                ->description('Needs attention')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),

            Stat::make('Pending Anime Requests', AnimeRequest::where('status', 'pending')->count())
                ->description('User requests')
                ->descriptionIcon('heroicon-m-film')
                ->color('info'),
        ];
    }
}