<?php

namespace App\Models\Concerns;

use Illuminate\Support\Arr;

trait HasTranslations
{
    /**
     * Return a translated attribute, falling back to the canonical value.
     * Canonical columns remain the source of truth (currently English).
     */
    public function localized(string $attribute, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        $translations = $this->translations;
        $value = is_array($translations)
            ? Arr::get($translations, "{$locale}.{$attribute}")
            : null;

        if (is_string($value) && trim($value) !== '') {
            return $value;
        }

        if ($value !== null && ! is_string($value)) {
            return $value;
        }

        return $this->getAttribute($attribute);
    }
}
