<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ChildrenSeeder extends Seeder
{
  public function run(): void
  {
    $parents = User::role("parent")->get();

    if ($parents->isEmpty()) {
      $this->command->warn("No parent users found. Seed parents first.");

      return;
    }

    $children = [
      ["Emma", "Johnson"],
      ["Liam", "Williams"],
      ["Olivia", "Brown"],
      ["Noah", "Jones"],
      ["Ava", "Garcia"],
      ["Sophia", "Miller"],
      ["James", "Davis"],
      ["Isabella", "Wilson"],
      ["Lucas", "Moore"],
      ["Mia", "Taylor"],

      ["Benjamin", "Anderson"],
      ["Charlotte", "Thomas"],
      ["Henry", "Jackson"],
      ["Amelia", "White"],
      ["Elijah", "Harris"],
      ["Harper", "Martin"],
      ["Daniel", "Thompson"],
      ["Evelyn", "Martinez"],
      ["Michael", "Robinson"],
      ["Abigail", "Clark"],

      ["Alexander", "Lewis"],
      ["Emily", "Lee"],
      ["Samuel", "Walker"],
      ["Ella", "Hall"],
      ["David", "Allen"],
      ["Scarlett", "Young"],
      ["Joseph", "King"],
      ["Grace", "Wright"],
      ["Matthew", "Scott"],
      ["Chloe", "Green"],
    ];

    /*
        |--------------------------------------------------------------------------
        | Development login credentials
        |--------------------------------------------------------------------------
        |
        | Every seeded Explorer uses:
        |
        | PIN: 1234
        |
        | Username is generated from the first name.
        |
        */

    foreach ($children as [$firstName, $lastName]) {
      $xp = rand(50, 5000);

      $parent = $parents->random();

      /*
            |--------------------------------------------------------------------------
            | Generate child username
            |--------------------------------------------------------------------------
            */

      $baseUsername = Str::lower(Str::slug($firstName));

      $username = $baseUsername;

      $counter = 1;

      while (Child::where("username", $username)->exists()) {
        $username = $baseUsername . $counter;
        $counter++;
      }

      /*
            |--------------------------------------------------------------------------
            | Create User account
            |--------------------------------------------------------------------------
            */

      $user = User::create([
        "name" => "{$firstName} {$lastName}",

        "email" => $username . "@learnquest.test",

        "password" => Hash::make("password"),
      ]);

      $user->assignRole("child");

      /*
            |--------------------------------------------------------------------------
            | Create Child profile
            |--------------------------------------------------------------------------
            */

      Child::create([
        "user_id" => $user->id,

        "parent_id" => $parent->id,

        "username" => $username,

        "pin" => Hash::make("1234"),

        "first_name" => $firstName,

        "last_name" => $lastName,

        "date_of_birth" => now()
          ->subYears(rand(6, 14))
          ->subDays(rand(0, 365)),

        "avatar" => null,

        "xp" => $xp,

        "level" => floor($xp / 100) + 1,

        "is_active" => true,
      ]);

      $this->command->info("Created Explorer: {$username} / PIN: 1234");
    }
  }
}
