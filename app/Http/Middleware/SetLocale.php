<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = array_keys(config('localization.supported', ['en' => 'English']));
        $locale = $this->validLocale($request->user()?->locale, $supported)
            ?? $this->validLocale($request->session()->get('locale'), $supported)
            ?? $this->localeFromCountry($request, $supported)
            ?? $this->localeFromBrowser($request, $supported)
            ?? $this->validLocale(config('localization.default', 'en'), $supported)
            ?? 'en';

        App::setLocale($locale);

        return $next($request);
    }

    private function localeFromCountry(Request $request, array $supported): ?string
    {
        if (! config('localization.country_detection.enabled')) {
            return null;
        }

        $header = (string) config('localization.country_detection.header', 'CF-IPCountry');
        $country = strtoupper(trim((string) $request->header($header, '')));

        if ($country === '' || $country === 'XX' || $country === 'T1') {
            return null;
        }

        return $this->validLocale(
            config("localization.country_detection.country_locales.{$country}"),
            $supported,
        );
    }

    private function localeFromBrowser(Request $request, array $supported): ?string
    {
        $preferred = $request->getPreferredLanguage($supported);

        return $this->validLocale($preferred, $supported);
    }

    private function validLocale(mixed $locale, array $supported): ?string
    {
        if (! is_string($locale) || $locale === '') {
            return null;
        }

        $locale = str_replace('_', '-', $locale);
        $baseLocale = strtolower(explode('-', $locale)[0]);

        return in_array($baseLocale, $supported, true) ? $baseLocale : null;
    }
}
