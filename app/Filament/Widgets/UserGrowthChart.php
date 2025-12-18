<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Carbon\Carbon;

class UserGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'User Growth';
    
    protected static ?int $sort = 2; // နံပါတ် ၂ (IncomeChart နဲ့ အတူတူထားတာမို့ ဘေးချင်းယှဉ်သွားမယ်)
    protected int | string | array $columnSpan = 1; // တစ်ဝက်ပဲယူမယ်
    
    // Default Filter ကို 'month' (ရက် ၃၀) ထားပါမယ်
    public ?string $filter = 'month';

    // 1. Filter များသတ်မှတ်ခြင်း
    protected function getFilters(): ?array
    {
        return [
            'week' => 'Last 7 Days',
            'month' => 'Last 30 Days',
            'year' => 'This Year',
        ];
    }

    // 2. ရွေးထားသော ကာလအတွင်း User ဘယ်လောက်တိုးလဲ ပြမယ်
    public function getDescription(): ?string
    {
        return 'New users joining the platform.';
    }

    protected function getData(): array
    {
        $activeFilter = $this->filter;

        $query = Trend::model(User::class);

        // Filter အလိုက် Data ဆွဲထုတ်ပုံ ပြောင်းခြင်း
        match ($activeFilter) {
            'week' => $data = $query
                ->between(start: now()->subDays(7), end: now())
                ->perDay()
                ->count(),

            'month' => $data = $query
                ->between(start: now()->subDays(30), end: now())
                ->perDay()
                ->count(),

            'year' => $data = $query
                ->between(start: now()->startOfYear(), end: now()->endOfYear())
                ->perMonth()
                ->count(),

            default => null,
        };

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    
                    // Styling: ခရမ်းရောင် Theme
                    'borderColor' => '#8b5cf6', // Primary Purple
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)', // အရောင်ဖျော့ဖျော့ ဖြည့်မယ်
                    'fill' => true,
                    'tension' => 0.4, // မျဉ်းကို အကွေးလေးဖြစ်အောင်လုပ်တာ (Smooth Curve)
                    'pointRadius' => 4, // အစက်အပျောက် ဆိုဒ်
                    'pointHoverRadius' => 6,
                ],
            ],
            // Label Formatting: Filter အလိုက် ရက်စွဲပုံစံပြောင်းမယ်
            'labels' => $data->map(function (TrendValue $value) use ($activeFilter) {
                $date = Carbon::parse($value->date);
                
                return match ($activeFilter) {
                    'year' => $date->format('M'),    // Jan, Feb
                    default => $date->format('d M'), // 01 Jan
                };
            }),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}