<?php

namespace App\Models\Concerns;

use Illuminate\Support\Arr;

trait HasLocalizedActivityContent
{
    /**
     * Translate learner-facing activity copy without allowing translations
     * to alter quiz scoring, answer indexes, media URLs, or activity settings.
     */
    public function localizedContent(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $base = is_array($this->content) ? $this->content : [];
        $translations = $this->translations;
        $translated = is_array($translations)
            ? Arr::get($translations, "{$locale}.content", [])
            : [];

        if (! is_array($translated)) {
            return $base;
        }

        foreach ([
            'instructions',
            'story_text',
            'question',
            'project_description',
            'challenge',
            'goal',
            'system_prompt',
        ] as $key) {
            if (array_key_exists($key, $base) && isset($translated[$key]) && is_string($translated[$key]) && trim($translated[$key]) !== '') {
                $base[$key] = $translated[$key];
            }
        }

        // Keep answer indexes and option structure canonical. Only option
        // display text may be translated, and only for existing options.
        if (isset($translated['options']) && is_array($translated['options']) && is_array($base['options'] ?? null)) {
            foreach (array_values($translated['options']) as $index => $optionText) {
                $optionText = is_array($optionText) ? ($optionText['option'] ?? null) : $optionText;

                if (! array_key_exists($index, $base['options']) || ! is_string($optionText) || trim($optionText) === '') {
                    continue;
                }

                if (is_array($base['options'][$index])) {
                    $base['options'][$index]['option'] = $optionText;
                } elseif (is_string($base['options'][$index])) {
                    $base['options'][$index] = $optionText;
                }
            }
        }

        return $base;
    }
}
