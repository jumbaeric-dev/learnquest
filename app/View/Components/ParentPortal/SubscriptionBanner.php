<?php

namespace App\View\Components\ParentPortal;

use App\Models\User;
use App\Services\Billing\RenewalNoticeService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SubscriptionBanner extends Component
{
    /** @var array{level:string,message:string,cta:string}|null */
    public ?array $notice = null;

    public function __construct(RenewalNoticeService $notices, public bool $hide = false)
    {
        $user = auth()->user();

        $this->notice = (! $hide && $user instanceof User)
            ? $notices->forParent($user)
            : null;
    }

    public function render(): View
    {
        return view('components.parent-portal.subscription-banner');
    }
}