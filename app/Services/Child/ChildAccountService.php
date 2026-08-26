<?php

namespace App\Services\Child;

use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class ChildAccountService
{
  /**
   * Create a child login account together with
   * the associated Child profile.
   */
  public function createChild(array $data): Child
  {
    return DB::transaction(function () use ($data) {
      $username = $this->resolveUsername($data);

      $pin = $data["pin"] ?? $this->generatePin();

      $user = User::create([
        "name" => trim($data["first_name"] . " " . ($data["last_name"] ?? "")),

        "email" => $this->generateEmail($data),

        /*
         * Child login uses the Child username + PIN.
         *
         * The User password is not the child's login credential.
         */
        "password" => Hash::make(Str::random(24)),
      ]);

      /*
       * The application expects every child account to have
       * the child role.
       */
      $user->assignRole("child");

      $data["user_id"] = $user->id;
      $data["username"] = $username;
      $data["pin"] = Hash::make($pin);

      return Child::create($data);
    });
  }

  /**
   * Resolve the username to use for a new child.
   *
   * If no username was supplied, automatically generate one.
   *
   * If a username was explicitly supplied and already exists,
   * fail clearly rather than silently changing the creator's choice.
   */
  protected function resolveUsername(array $data): string
  {
    $username = trim((string) ($data["username"] ?? ""));

    if ($username === "") {
      return $this->suggestUsername((string) ($data["first_name"] ?? ""));
    }

    if (Child::where("username", $username)->exists()) {
      throw new RuntimeException(
        "The username '{$username}' is already taken."
      );
    }

    return $username;
  }

  /**
   * Generate the first available username based on
   * the child's first name.
   *
   * Examples:
   *
   * john
   * john-2
   * john-3
   * john-4
   */
  public function suggestUsername(string $firstName): string
  {
    $base = Str::slug($firstName);

    if ($base === "") {
      $base = "explorer";
    }

    if (!Child::where("username", $base)->exists()) {
      return $base;
    }

    $number = 2;

    do {
      $candidate = "{$base}-{$number}";
      $number++;
    } while (Child::where("username", $candidate)->exists());

    return $candidate;
  }

  /**
   * Return multiple available username suggestions.
   *
   * Useful for showing alternatives when the preferred
   * username is already taken.
   */
  public function suggestUsernames(string $firstName, int $limit = 3): array
  {
    $base = Str::slug($firstName);

    if ($base === "") {
      $base = "explorer";
    }

    $suggestions = [];
    $number = 1;

    while (count($suggestions) < $limit) {
      $candidate = $number === 1 ? $base : "{$base}-{$number}";

      if (!Child::where("username", $candidate)->exists()) {
        $suggestions[] = $candidate;
      }

      $number++;
    }

    return $suggestions;
  }

  /**
   * Generate a four-digit child PIN.
   */
  protected function generatePin(): string
  {
    return (string) random_int(1000, 9999);
  }

  /**
   * Temporary internal email strategy.
   */
  protected function generateEmail(array $data): string
  {
    return Str::slug($data["first_name"]) .
      "." .
      Str::lower(Str::random(6)) .
      "@learnquest.test";
  }
}
