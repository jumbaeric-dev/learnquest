<?php

namespace App\View\Components\ParentPortal;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class VerifyEmailBanner extends Component
{
    public bool $show = false;

    public string $email = '';

    public function __construct()
    {
        $user = auth()->user();

        $this->show = $user instanceof User
            && $user->isParent()
            && ! $user->hasVerifiedEmail();

        $this->email = $this->show ? (string) $user->email : '';
    }

    public function render(): View
    {
        return view('components.parent-portal.verify-email-banner');
    }
}