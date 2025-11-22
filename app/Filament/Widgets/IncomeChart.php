<?php

namespace App\Filament\Widgets;

use App\Models\PaymentRequest;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Monthly Income (MMK)';
    
    protected static ?int $sort = 2; // User Growth Chart နဲ့ တန်းတူထားမယ်
    
    protected static string $color = 'success'; // အစိမ်းရောင် Theme

    protected function getData(): array
    {
        // ပြင်ဆင်ချက်: Approved ဖြစ်ပြီးသား ငွေလွှဲခြင်းများကိုသာ တွက်ချက်မည်
        $data = Trend::query(PaymentRequest::query()->where('status', 'approved'))
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->sum('amount');

        return [
            'datasets' => [
                [
                    'label' => 'Income',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#4ade80', // Solid Green
                    'borderColor' => '#4ade80',
                    'barThickness' => 25, // တိုင်အလုံး အကြီးအသေး
                    'borderRadius' => 4, // ထောင့်ကွေး
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}