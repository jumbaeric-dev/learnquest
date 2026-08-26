<?php

namespace Database\Factories;

use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Child>
 */
class ChildFactory extends Factory
{
  protected $model = Child::class;

  public function definition(): array
  {
    $user = User::factory();

    return [
      "user_id" => $user,
      "parent_id" => $user,

      "first_name" => fake()->firstName(),
      "last_name" => fake()->lastName(),

      "username" => fake()
        ->unique()
        ->userName(),
      "pin" => Hash::make("1234"),

      "date_of_birth" => fake()->date(),
      "avatar" => null,
      "xp" => 0,
      "level" => 1,
      "is_active" => true,
    ];
  }
}
