<?php

namespace App\Services\Child;

use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Data\Child\Dashboard\WelcomeDTO;
use App\Data\Child\Dashboard\ExplorerHeaderDTO;
use App\Data\Child\Dashboard\CurrentMissionDTO;

class ChildAccountService
{
    /**
     * Create a child login account together with
     * the associated Child profile.
     */
    public function createChild(array $data): Child
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([

                'name' => trim(
                    $data['first_name']
                    .' '
                    .$data['last_name']
                ),

                'email' => $this->generateEmail($data),

                /*
                 * Temporary.
                 * Explorer PIN arrives later.
                 */
                'password' => Hash::make(
                    Str::random(24)
                ),

            ]);

            $user->assignRole('child');

            $data['user_id'] = $user->id;

            return Child::create($data);

        });
    }

    /**
     * Temporary email strategy.
     */
    protected function generateEmail(array $data): string
    {
        return Str::slug(
            $data['first_name']
        )
        .'.'
        .Str::lower(Str::random(6))
        .'@learnquest.test';
    }
}