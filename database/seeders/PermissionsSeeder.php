<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            'manage users',

            'manage subjects',
            'manage courses',
            'manage modules',
            'manage lessons',
            'manage activities',

            'view analytics',

            'manage schools',

            'manage ai content',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }
    }
}