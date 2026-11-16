<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use App\Notifications\WelcomeParentNotification;

class ParentAuthController extends Controller
{
  public function showLoginForm(Request $request)
  {
    if ($request->user()?->isParent()) {
      return redirect()->route("parent.dashboard");
    }

    return view("auth.parent-login");
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      "email" => ["required", "string", "email", "max:255"],
      "password" => ["required", "string"],
    ]);

    $email = Str::lower(trim($credentials["email"]));
    $throttleKey = $this->throttleKey($request, $email);

    $maxAttempts = max(
      1,
      (int) config("learnquest.auth.parent_login.max_attempts", 5)
    );
    $decaySeconds = max(
      1,
      (int) config("learnquest.auth.parent_login.decay_seconds", 60)
    );

    if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
      $seconds = RateLimiter::availableIn($throttleKey);

      return back()
        ->withErrors([
          "email" => sprintf(
            "Too many login attempts. Please try again in %d seconds.",
            $seconds
          ),
        ])
        ->onlyInput("email");
    }

    $user = User::query()->whereRaw("LOWER(email) = ?", [$email])->first();

    /*
     * Only parents may use this form. Child accounts (which have an
     * internal email and a random password) and other staff roles get the
     * same generic error so the form never reveals which emails exist.
     */
    if (
      $user &&
      $user->isParent() &&
      Hash::check($credentials["password"], $user->password)
    ) {
      RateLimiter::clear($throttleKey);

      Auth::login($user, $request->boolean("remember"));
      $request->session()->regenerate();
      $request->session()->forget("active_child_id");

      return redirect()->intended(route("parent.dashboard"));
    }

    RateLimiter::hit($throttleKey, $decaySeconds);

    return back()
      ->withErrors(["email" => "Invalid email or password."])
      ->onlyInput("email");
  }

  public function showRegisterForm(Request $request)
  {
    if ($request->user()?->isParent()) {
      return redirect()->route("parent.dashboard");
    }

    return view("auth.parent-register");
  }

  public function register(Request $request)
  {
    $data = $request->validate([
      "name" => ["required", "string", "max:255"],
      "email" => [
        "required",
        "string",
        "email",
        "max:255",
        Rule::unique("users", "email"),
      ],
      "password" => ["required", "confirmed", Password::min(8)],
    ]);

    $user = DB::transaction(function () use ($data) {
      $user = User::create([
        "name" => trim($data["name"]),
        "email" => Str::lower(trim($data["email"])),
        "password" => $data["password"], // hashed by the model cast
        "free_plan_grandfathered" => (bool) config("learnquest.billing.free_plan_enabled", true),
      ]);

      $user->assignRole("parent");

      return $user;
    });

    Auth::login($user);
    $trial = app(\App\Services\Billing\SubscriptionService::class)->startTrialIfEligible($user);

    try {
      $user->notify(new WelcomeParentNotification($trial?->ends_at));
    } catch (\Throwable $exception) {
      report($exception);
    }
    $request->session()->regenerate();
    $request->session()->forget("active_child_id");

    return redirect()->route("parent.dashboard");
  }

  private function throttleKey(Request $request, string $email): string
  {
    return "parent-login:" .
      hash("sha256", $email . "|" . ($request->ip() ?? "unknown"));
  }
}
