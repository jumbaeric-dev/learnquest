<x-mail::message>
    # Thank you, {{ $name }}

    We’ve received your payment. This email is your receipt.

    <x-mail::table>
        | Detail | |
        | :-- | --: |
        | Plan | {{ $planLabel }} |
        | Amount | {{ $amount }} |
        | Paid on | {{ $paidOn }} |
        | Paid with | {{ $method }} |
        | Payment reference | {{ $reference }} |
        @if ($accessUntil)
        | Access until | {{ $accessUntil }} |
        @endif
    </x-mail::table>

    <x-mail::button :url="$billingUrl">
        View your billing
    </x-mail::button>

    If you have any questions about this payment, reply to this email and include the payment reference above.

    Thanks,<br>
    {{ config('app.name') }}
</x-mail::message>