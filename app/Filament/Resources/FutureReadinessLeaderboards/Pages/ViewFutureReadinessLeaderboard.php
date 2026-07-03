<?php

namespace App\Filament\Resources\FutureReadinessLeaderboards\Pages;

use App\Filament\Resources\FutureReadinessLeaderboards\FutureReadinessLeaderboardResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFutureReadinessLeaderboard extends ViewRecord
{
    protected static string $resource = FutureReadinessLeaderboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
