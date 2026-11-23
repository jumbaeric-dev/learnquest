<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Billing\SubscriptionService;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class ParentEmailVerificationController extends Controller
{
  /**
   * Opens from the emailed link. The "signed" middleware has already checked the
   * signature and expiry, so no login is needed (people click on their phone).
   */
  public function verify(Request $request, int $id, string $hash)
  {
    $user = User::query()->findOrFail($id);

    abort_unless(
      $user->isParent() && hash_equals(sha1($user->getEmailForVerification()), $hash),
      403
    );

    if (!$user->hasVerifiedEmail()) {
      $user->markEmailAsVerified();

      event(new Verified($user));

      if (config("learnquest.billing.trial.requires_verified_email")) {
        app(SubscriptionService::class)->startTrialIfEligible($user);
      }
    }

    $message = "Thanks! Your email address is verified.";

    return $request->user()?->is($user)
      ? redirect()->route("parent.dashboard")->with("status", $message)
      : redirect()->route("parent.login")->with("status", $message . " You can sign in now.");
  }

  public function send(Request $request)
  {
    $user = $request->user();

    if ($user->hasVerifiedEmail()) {
      return back()->with("verification_status", "Your email address is already verified.");
    }

    try {
      $user->sendEmailVerificationNotification();
    } catch (\Throwable $exception) {
      report($exception);

      return back()->with(
        "verification_status",
        "We couldn’t send the email just now. Please try again in a few minutes."
      );
    }

    return back()->with(
      "verification_status",
      "We’ve sent a new verification link to {$user->email}."
    );
  }
}