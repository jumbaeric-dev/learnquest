<?php

namespace App\Filament\Widgets;

use App\Models\Child;
use App\Services\FutureReadinessService;
use Filament\Widgets\ChartWidget;

class FutureReadinessChartWidget extends ChartWidget
{
    protected int|string|array $columnSpan = 1;
    protected ?string $heading = 'Future Readiness Skills';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $child = Child::with(
            'skillProgress.skill'
        )->first();

        if (! $child) {
            return [
                'datasets' => [],
                'labels' => [],
            ];
        }

        $data = FutureReadinessService::radarData(
            $child
        );

        return [
            'datasets' => [
                [
                    'label' => $child->full_name,
                    'data' => $data['values'],
                ],
            ],

            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'radar';
    }
}