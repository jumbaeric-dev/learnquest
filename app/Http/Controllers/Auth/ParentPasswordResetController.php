<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ParentPasswordResetController extends Controller
{
  public function showForgotForm()
  {
    return view("auth.parent-forgot-password");
  }

  public function sendLink(Request $request)
  {
    $data = $request->validate([
      "email" => ["required", "string", "email", "max:255"],
    ]);

    $user = User::query()
      ->whereRaw("LOWER(email) = ?", [Str::lower(trim($data["email"]))])
      ->first();

    // Only parent accounts can reset here. The reply is identical in every case,
    // so the form never reveals which emails have accounts.
    if ($user && $user->isParent()) {
      $email = $user->email;

      // Send after the response, so the reply takes the same time whether or not
      // the account exists.
      dispatch(function () use ($email) {
        try {
          Password::sendResetLink(["email" => $email]);
        } catch (\Throwable $exception) {
          report($exception);
        }
      })->afterResponse();
    }

    return back()->with(
      "status",
      "If that email belongs to a parent account, we’ve sent a link to reset the password."
    );
  }

  public function showResetForm(Request $request, string $token)
  {
    return view("auth.parent-reset-password", [
      "token" => $token,
      "email" => (string) $request->query("email", ""),
    ]);
  }

  public function reset(Request $request)
  {
    $data = $request->validate([
      "token" => ["required", "string"],
      "email" => ["required", "string", "email", "max:255"],
      "password" => ["required", "confirmed", PasswordRule::min(8)],
    ]);

    $status = Password::reset(
      [
        "email" => trim($data["email"]),
        "password" => $data["password"],
        "password_confirmation" => (string) $request->input("password_confirmation"),
        "token" => $data["token"],
      ],
      function (User $user, string $password): void {
        abort_unless($user->isParent(), 403);

        $user->forceFill([
          "password" => $password, // hashed by the model cast
          "remember_token" => Str::random(60),
        ])->save();

        event(new PasswordReset($user));
      }
    );

    if ($status === Password::PASSWORD_RESET) {
      return redirect()
        ->route("parent.login")
        ->with("status", "Your password has been reset. You can sign in now.");
    }

    return back()
      ->withInput($request->only("email"))
      ->withErrors([
        "email" => "This reset link is invalid or has expired. Please request a new one.",
      ]);
  }
}
