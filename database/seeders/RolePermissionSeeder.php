<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionsSeeder::class,
        ]);

        $allPermissions = Permission::pluck('name')->all();

        Role::findByName('admin')->syncPermissions($allPermissions);

        Role::findByName('content_creator')->syncPermissions([
            'manage subjects',
            'manage courses',
            'manage modules',
            'manage lessons',
            'manage activities',
            'manage ai content',
        ]);

        Role::findByName('school_admin')->syncPermissions([
            'manage users',
            'view analytics',
            'manage schools',
        ]);

        Role::findByName('teacher')->syncPermissions([
            'view analytics',
        ]);

        Role::findByName('parent')->syncPermissions([
            'view analytics',
        ]);
    }
}
