<?php

namespace App\Filament\Widgets;

use App\Models\PaymentRequest;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class PaymentMethodChart extends ChartWidget
{
    protected static ?string $heading = 'Payment Methods';
    protected static ?int $sort = 3; // နံပါတ် ၃
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        // Payment Method အလိုက် အရေအတွက်ကို ရေတွက်ခြင်း
        $data = PaymentRequest::query()
            ->select('payment_method', DB::raw('count(*) as total'))
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        return [
            'datasets' => [
                [
                    'label' => 'Transactions',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        '#0056D2', // KBZPay Blue
                        '#FFE600', // WavePay Yellow
                        '#F35325', // Other (Red/Orange)
                        '#81B441', // Other (Green)
                    ],
                    'borderWidth' => 0,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // အကွင်းပုံစံ
    }
}