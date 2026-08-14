<?php

namespace App\Support\Lqds;

class ComponentValidator
{
    public static function resolve(
        mixed $value,
        array $allowed,
        mixed $fallback
    ): mixed {
        return in_array($value, $allowed, true)
            ? $value
            : $fallback;
    }
}