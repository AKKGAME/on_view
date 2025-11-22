<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class UserGrowthChart extends ChartWidget
{
    protected static ?string $heading = 'User Growth (Last 30 Days)';
    protected static ?int $sort = 2;
    protected static string $color = 'primary';

    protected function getData(): array
    {
        $data = Trend::model(User::class)
            ->between(
                start: now()->subDays(30),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'New Users',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'borderColor' => '#8b5cf6', // Purple
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}