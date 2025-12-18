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
    protected static ?int $sort = 1; // နံပါတ် ၁
    protected int | string | array $columnSpan = 'full'; // နေရာအပြည့်

    // Chart Data ယူရန် Helper Function (နောက်ဆုံး ၇ ရက်စာ)
    private function getChartData(string $model): array
    {
        return $model::selectRaw('count(*) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupByRaw('Date(created_at)')
            ->orderByRaw('Date(created_at)')
            ->pluck('count')
            ->toArray();
    }

    protected function getStats(): array
    {
        // 1. REVENUE CALCULATION (Real Comparison)
        $thisMonth = PaymentRequest::where('status', 'approved')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->sum('amount');

        $lastMonth = PaymentRequest::where('status', 'approved')
            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->sum('amount');

        // ရာခိုင်နှုန်း တွက်ချက်ခြင်း
        $revenueDiff = $thisMonth - $lastMonth;
        $revenueIcon = $revenueDiff >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
        $revenueColor = $revenueDiff >= 0 ? 'success' : 'danger';
        $percentage = $lastMonth > 0 ? round((($thisMonth - $lastMonth) / $lastMonth) * 100) : 100;
        $revenueDesc = $revenueDiff >= 0 ? "Increased by {$percentage}%" : "Decreased by " . abs($percentage) . "%";


        // 2. USER GROWTH
        $newUsersThisWeek = User::where('created_at', '>=', now()->subDays(7))->count();


        // 3. PENDING COUNTS
        $pendingTopups = PaymentRequest::where('status', 'pending')->count();
        $pendingRequests = AnimeRequest::where('status', 'pending')->count();

        return [
            // --- STAT 1: Monthly Income ---
            Stat::make('Monthly Revenue', number_format($thisMonth) . ' MMK')
                ->description($revenueDesc) // အတက်အကျ စာသား
                ->descriptionIcon($revenueIcon) // အတက်အကျ Icon
                ->chart($this->getChartData(PaymentRequest::class)) // Data အစစ် Chart
                ->color($revenueColor), // အတက်အကျ အရောင်

            // --- STAT 2: Total Users ---
            Stat::make('Total Users', User::count())
                ->description("+$newUsersThisWeek new this week")
                ->descriptionIcon('heroicon-m-user-group')
                ->chart($this->getChartData(User::class))
                ->color('primary'),

            // --- STAT 3: Pending Top-ups ---
            Stat::make('Pending Top-ups', $pendingTopups)
                ->description($pendingTopups > 0 ? 'Needs attention' : 'All clear')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color($pendingTopups > 0 ? 'warning' : 'success'), // Pending ရှိမှ Warning ပြမယ်

            // --- STAT 4: Anime Requests ---
            Stat::make('Anime Requests', $pendingRequests)
                ->description($pendingRequests > 0 ? 'User requests waiting' : 'No new requests')
                ->descriptionIcon('heroicon-m-film')
                ->color($pendingRequests > 0 ? 'info' : 'gray'),
        ];
    }
}