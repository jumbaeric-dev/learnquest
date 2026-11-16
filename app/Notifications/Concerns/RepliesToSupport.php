<?php

namespace App\Notifications\Concerns;

use Illuminate\Notifications\Messages\MailMessage;

trait RepliesToSupport
{
    /** Replies to parent emails go to your support inbox, not a no-reply address. */
    protected function withSupportReply(MailMessage $message): MailMessage
    {
        $support = config('learnquest.support_email');

        return filled($support) && filter_var($support, FILTER_VALIDATE_EMAIL)
            ? $message->replyTo($support, config('app.name'))
            : $message;
    }
}