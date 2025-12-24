<?php

namespace App\Filament\Widgets;

use App\Models\PaymentRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PaymentMethodChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue by Payment Channel';
    
    protected static ?int $sort = 3;
    
    // Filter ထည့်သွင်းခြင်း
    public ?string $filter = 'month';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last 7 Days',
            'month' => 'This Month',
            'year' => 'This Year',
            'all' => 'All Time',
        ];
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        // Approved ဖြစ်ပြီးသား ငွေစာရင်းကိုပဲ တွက်ချက်မယ်
        $query = PaymentRequest::query()->where('status', 'approved');

        $query->when($activeFilter === 'today', fn ($q) => $q->whereDate('created_at', now()))
              ->when($activeFilter === 'week', fn ($q) => $q->where('created_at', '>=', now()->subDays(7)))
              ->when($activeFilter === 'month', fn ($q) => $q->whereMonth('created_at', now()->month))
              ->when($activeFilter === 'year', fn ($q) => $q->whereYear('created_at', now()->year));

        $data = $query->select('payment_method', DB::raw('sum(amount) as total_amount'))
            ->groupBy('payment_method')
            ->pluck('total_amount', 'payment_method');

        // Labels ပြင်ဆင်ခြင်း
        $formattedLabels = $data->keys()->map(function ($method) {
            return match ($method) {
                'kpay' => 'KBZ Pay',
                'wave' => 'Wave Pay',
                default => strtoupper($method),
            };
        })->toArray();

        // Brand Colors
        $backgroundColors = $data->keys()->map(function ($method) {
            return match ($method) {
                'kpay' => '#0056D2', // KBZ Blue
                'wave' => '#FFE600', // Wave Yellow
                default => '#9CA3AF',
            };
        })->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Total Income (MMK)',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => $backgroundColors,
                    // Bar Chart Style
                    'borderRadius' => 8, // ထောင့်တွေကို အဝိုင်းလုပ်မယ်
                    'barPercentage' => 0.5, // တိုင်အထူအပါး (0.5 to 1.0)
                    'categoryPercentage' => 0.8,
                ],
            ],
            'labels' => $formattedLabels,
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // ✅ Bar Chart ပြောင်းလိုက်ပါပြီ
    }

    // Chart Options (Grid တွေဖျောက်ပြီး ပိုသပ်ရပ်အောင်လုပ်မယ်)
    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false, // Bar Chart မှာ အရောင်ခွဲထားပြီးသားမို့ Legend မလိုပါ
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'display' => true,
                        'drawBorder' => false,
                        'color' => '#f3f4f6', // Grid မျဉ်းအပါးလေးပဲထားမယ်
                    ],
                    'ticks' => [
                        'callback' => "(value) => value.toLocaleString() + ' Ks'", // Y ဝင်ရိုးမှာ Ks တပ်မယ်
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false, // X ဝင်ရိုးက Grid မျဉ်းတွေ ဖျောက်မယ် (ပိုရှင်းအောင်)
                    ],
                ],
            ],
        ];
    }
}