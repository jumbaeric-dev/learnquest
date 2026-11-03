<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Roles and permissions must exist before assigning roles.
        $this->call([
            RolesSeeder::class,
            RolePermissionSeeder::class,
        ]);

        // Development accounts.
        $admin = User::firstOrCreate(
            ['email' => 'gwandeprophet2@gmail.com'],
            [
                'name' => 'Eric Jumba',
                'password' => Hash::make('888888'),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);

        $contentCreator = User::firstOrCreate(
            ['email' => 'creator@learnquest.test'],
            [
                'name' => 'Content Creator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $contentCreator->syncRoles(['content_creator']);

        $schoolAdmin = User::firstOrCreate(
            ['email' => 'school@learnquest.test'],
            [
                'name' => 'School Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $schoolAdmin->syncRoles(['school_admin', 'parent']);

        // Canonical LearnQuest data. Dependencies matter:
        // worlds, then skills, then the curriculum.
        $this->call([
            SubjectSeeder::class,
            SkillSeeder::class,
            BadgeSeeder::class,
            LearnQuestCurriculumSeeder::class,

            PlanSeeder::class,
            // Preserve the old course without restoring legacy activity
            // types, duplicated activities, or published unreviewed content.
            LegacyAiCoursePreservationSeeder::class,

            // Demo child accounts; learning progress starts empty.
            ChildrenSeeder::class,
        ]);
    }
}