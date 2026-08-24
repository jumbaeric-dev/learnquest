<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Child;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ChildLoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.child-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'pin'      => ['required', 'numeric', 'digits:4'],
        ]);

        $child = Child::where('username', $credentials['username'])->first();

        if ($child && Hash::check($credentials['pin'], $child->pin)) {
            Auth::login($child->user);
            session(['active_child_id' => $child->id]);
            $request->session()->regenerate();

            return redirect()->intended(route('child.dashboard'));
        }

        return back()->withErrors([
            'username' => 'Invalid username or PIN code.',
        ])->onlyInput('username');
    }
}