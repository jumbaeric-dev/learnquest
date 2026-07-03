<?php

namespace App\Filament\Resources\ChildStreaks\Pages;

use App\Filament\Resources\ChildStreaks\ChildStreakResource;
use Filament\Resources\Pages\ListRecords;

class ListChildStreaks extends ListRecords
{
    protected static string $resource =
        ChildStreakResource::class;
}