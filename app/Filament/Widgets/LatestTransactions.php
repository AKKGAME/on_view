<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LatestTransactions extends BaseWidget
{
    protected static ?int $sort = 1;
    
    // Data အပြောင်းအလဲမြန်ရင် 15s ထားပါ၊ All Time မို့လို့ အရမ်းမပြောင်းလဲရင် 30s သို့ 60s ထားတာ Server အတွက် ပိုကောင်းပါတယ်
    protected static ?string $pollingInterval = '60s'; 

    protected function getStats(): array
    {
        // All Time ဖြစ်တဲ့အတွက် Date Filter တွေ မလိုတော့ပါ (ဖြုတ်လိုက်ပါပြီ)

        // 1. TOP-UP TOTAL (All Time)
        $totalTopup = Transaction::where('type', 'topup')->sum('amount');

        // 2. CONTENT SALES (All Time)
        // purchase + movie_purchase
        $totalPurchase = Transaction::whereIn('type', ['purchase', 'movie_purchase'])->sum('amount');

        // 3. SUBSCRIPTION SALES (All Time)
        $totalSub = Transaction::where('type', 'subscription')->sum('amount');

        return [
            // CARD 1: Total Income (Top-up)
            Stat::make('Total Top-up Income', number_format($totalTopup) . ' Ks')
                ->description('All time deposits') // စာသားပြောင်းထားသည်
                ->descriptionIcon('heroicon-m-wallet')
                ->color('success') // အစိမ်း
                ->chart([7, 2, 10, 3, 15, 4, 17]), // ပုံသေ Chart (အလှဆင်ရန်)

            // CARD 2: Total Content Sales
            Stat::make('Total Content Sales', number_format($totalPurchase) . ' Ks')
                ->description('All time episodes & movies sold')
                ->descriptionIcon('heroicon-m-film')
                ->color('warning'), // လိမ္မော်

            // CARD 3: Total VIP Subscriptions
            Stat::make('Total VIP Sales', number_format($totalSub) . ' Ks')
                ->description('All time plan sales')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'), // အပြာ
        ];
    }
}