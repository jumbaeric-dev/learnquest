<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ChildLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.child-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'pin' => ['required', 'numeric', 'digits:4'],
        ]);

        $throttleKey = $this->throttleKey(
            $request,
            $credentials['username']
        );

        $maxAttempts = max(1, (int) config(
            'learnquest.auth.child_login.max_attempts',
            5
        ));
        $decaySeconds = max(1, (int) config(
            'learnquest.auth.child_login.decay_seconds',
            60
        ));

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withErrors([
                    'username' => sprintf(
                        'Too many login attempts. Please try again in %d seconds.',
                        $seconds
                    ),
                ])
                ->onlyInput('username');
        }

        $child = Child::query()
            ->where('username', $credentials['username'])
            ->first();

        if (
            $child
            && $child->is_active
            && Hash::check($credentials['pin'], $child->pin)
        ) {
            RateLimiter::clear($throttleKey);

            Auth::login($child->user);
            $request->session()->regenerate();

            session([
                'active_child_id' => $child->id,
            ]);

            return redirect()->intended(route('child.dashboard'));
        }

        RateLimiter::hit($throttleKey, max(1, $decaySeconds));

        return back()
            ->withErrors([
                'username' => 'Invalid username or PIN code.',
            ])
            ->onlyInput('username');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Throttle each username separately per client IP, without exposing
     * the PIN or allowing case variants to create independent buckets.
     */
    private function throttleKey(Request $request, string $username): string
    {
        $normalizedUsername = Str::lower(trim($username));

        return 'child-login:' . hash(
            'sha256',
            $normalizedUsername . '|' . ($request->ip() ?? 'unknown')
        );
    }
}
