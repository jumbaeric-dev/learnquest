<?php

namespace App\Support;

/**
 * Formats integer minor-unit amounts without relying on floating-point math.
 */
final class Money
{
    public static function format(int $amountMinor, string $currency): string
    {
        $currency = strtoupper(trim($currency));
        $isNegative = $amountMinor < 0;
        $absoluteAmount = abs($amountMinor);

        $wholeUnits = intdiv($absoluteAmount, 100);
        $minorUnits = $absoluteAmount % 100;

        $formattedAmount = number_format($wholeUnits, 0, '.', ',');

        if ($minorUnits !== 0) {
            $formattedAmount .= '.' . str_pad(
                (string) $minorUnits,
                2,
                '0',
                STR_PAD_LEFT
            );
        }

        $formattedAmount = match ($currency) {
            'KES' => 'KSh ' . $formattedAmount,
            'USD' => '$' . $formattedAmount,
            default => $currency . ' ' . $formattedAmount,
        };

        return $isNegative ? '-' . $formattedAmount : $formattedAmount;
    }
}
