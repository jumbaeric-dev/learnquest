<?php

namespace App\Filament\Resources\Children\Pages;

use App\Filament\Resources\Children\ChildResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\Child\ChildAccountService;

class CreateChild extends CreateRecord
{
    protected static string $resource = ChildResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return app(ChildAccountService::class)
            ->createChild($data);
    }

}
