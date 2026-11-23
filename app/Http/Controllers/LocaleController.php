<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController
{
    public function update(Request $request): RedirectResponse
    {
        $supported = array_keys(config('localization.supported', ['en' => 'English']));

        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in($supported)],
        ]);

        $locale = $validated['locale'];
        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->forceFill(['locale' => $locale])->save();
        }

        return redirect()->back()->with('status', __('localization.language_updated'));
    }
}
