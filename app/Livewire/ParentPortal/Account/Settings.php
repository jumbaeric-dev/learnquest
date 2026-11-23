<?php

namespace App\Livewire\ParentPortal\Account;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Component;

class Settings extends Component
{
    public string $name = '';
    public string $email = '';

    public string $currentPassword = '';
    public string $password = '';
    public string $passwordConfirmation = '';

    public function mount(): void
    {
        $parent = $this->parent();

        $this->name = $parent->name;
        $this->email = $parent->email;
    }

    public function saveProfile(): void
    {
        $parent = $this->parent();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($parent->id),
            ],
        ]);

        $newEmail = mb_strtolower(trim($validated['email']));
        $emailChanged = $newEmail !== mb_strtolower((string) $parent->email);

        $parent->name = trim($validated['name']);
        $parent->email = $newEmail;

        if ($emailChanged) {
            // A new address must be proven before it is used for receipts or checkout.
            $parent->email_verified_at = null;
        }

        $parent->save();

        if ($emailChanged) {
            try {
                $parent->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        session()->flash(
            'parent_profile_saved',
            'Your profile information has been updated.'
                . ($emailChanged ? ' We’ve sent a link to your new email address to verify it.' : '')
        );
    }

    public function updatePassword(): void
    {
        $parent = $this->parent();

        $validated = $this->validate([
            'currentPassword' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed:passwordConfirmation', Password::min(8)],
        ], [
            'password.confirmed' => 'The new password confirmation does not match.',
        ]);

        if (! Hash::check($validated['currentPassword'], $parent->password)) {
            $this->addError('currentPassword', 'The current password is incorrect.');
            return;
        }

        // Invalidate other authenticated sessions before replacing the password.
        // The auth.session middleware enforces this password-hash change on later requests.
        Auth::guard('web')->logoutOtherDevices($validated['currentPassword']);

        // User's hashed password cast hashes this value when saved.
        $parent->password = $validated['password'];
        $parent->setRememberToken(str()->random(60));
        $parent->save();

        // Keep the session performing this change authenticated with the new password hash.
        session()->put(
            'password_hash_web',
            Auth::guard('web')->hashPasswordForCookie($parent->getAuthPassword())
        );

        $this->reset('currentPassword', 'password', 'passwordConfirmation');
        session()->flash('parent_password_saved', 'Your password has been changed successfully.');
    }

    private function parent(): User
    {
        $user = auth()->user();

        abort_unless($user instanceof User && $user->isParent(), 403);

        return $user;
    }

    public function render(): View
    {
        $this->parent();

        return view('livewire.parent-portal.account.settings');
    }
}
