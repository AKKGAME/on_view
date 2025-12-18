<?php

namespace App\Filament\Widgets;

use App\Models\PaymentRequest;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Carbon\Carbon;

class IncomeChart extends ChartWidget
{
    protected static ?string $heading = 'Income Overview';
    
    protected static ?int $sort = 2; // နံပါတ် ၂
    protected int | string | array $columnSpan = 1; // တစ်ဝက်ပဲယူမယ်
    
    public ?string $filter = 'year';

    protected function getFilters(): ?array
    {
        return [
            'today' => 'Today',
            'week' => 'Last 7 Days',
            'month' => 'This Month',
            'year' => 'This Year',
        ];
    }

    // ✅ FIX: protected မှ public သို့ ပြောင်းလိုက်ပါပြီ
    public function getDescription(): ?string
    {
        return 'Total income calculated from approved transactions.';
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $query = Trend::query(PaymentRequest::query()->where('status', 'approved'));

        match ($activeFilter) {
            'today' => $data = $query
                ->between(start: now()->startOfDay(), end: now()->endOfDay())
                ->perHour()
                ->sum('amount'),
            
            'week' => $data = $query
                ->between(start: now()->subDays(7), end: now())
                ->perDay()
                ->sum('amount'),

            'month' => $data = $query
                ->between(start: now()->startOfMonth(), end: now()->endOfMonth())
                ->perDay()
                ->sum('amount'),

            'year' => $data = $query
                ->between(start: now()->startOfYear(), end: now()->endOfYear())
                ->perMonth()
                ->sum('amount'),
            
            default => null,
        };

        return [
            'datasets' => [
                [
                    'label' => 'Income (MMK)',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => ['rgba(74, 222, 128, 0.8)'], 
                    'borderColor' => '#22c55e',
                    'borderWidth' => 1,
                    'barThickness' => 20, 
                    'borderRadius' => 4, 
                ],
            ],
            'labels' => $data->map(function (TrendValue $value) use ($activeFilter) {
                $date = Carbon::parse($value->date);

                return match ($activeFilter) {
                    'today' => $date->format('h A'), 
                    'year' => $date->format('M'),    
                    default => $date->format('d M'), 
                };
            }),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}