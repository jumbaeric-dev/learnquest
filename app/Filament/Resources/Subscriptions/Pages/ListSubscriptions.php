<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\Actions\GrantComplimentaryTimeAction;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Filament\Resources\Subscriptions\Widgets\SubscriptionStatsOverview;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            GrantComplimentaryTimeAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            SubscriptionStatsOverview::class,
        ];
    }
}