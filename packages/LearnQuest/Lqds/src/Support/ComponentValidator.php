<?php

namespace LearnQuest\Lqds\Support;

class ComponentValidator
{
    public static function resolve(
        mixed $value,
        array $allowed,
        mixed $fallback
    ): mixed {

        if (in_array($value, $allowed, true)) {
            return $value;
        }


        if (app()->environment('local')) {

            logger()->warning(
                'LQDS invalid component value.',
                [
                    'value' => $value,
                    'allowed' => $allowed,
                ]
            );

        }


        return $fallback;
    }
}