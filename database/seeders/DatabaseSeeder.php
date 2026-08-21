<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            RolesSeeder::class,
            RolePermissionSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            [
                'email' => 'gwandeprophet2@gmail.com',
            ],
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

        $this->call([
            SubjectSeeder::class,
            CourseSeeder::class,
            LearningModuleSeeder::class,
            LessonSeeder::class,
            SkillSeeder::class,
            BadgeSeeder::class,
            AIForKidsCurriculumSeeder::class,
            AIForKidsActivitiesSeeder::class,
            ChildrenSeeder::class,
            ChildSkillProgressSeeder::class,
            ActivityProgressSeeder::class,
            LessonProgressSeeder::class,
            CourseProgressSeeder::class,
        ]);
    }
}
