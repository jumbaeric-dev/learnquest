<form method="POST" action="{{ route('locale.update') }}" class="inline-flex items-center gap-2" aria-label="{{ __('localization.language') }}">
    @csrf
    <label for="learnquest-locale" class="sr-only">{{ __('localization.language') }}</label>
    <select id="learnquest-locale" name="locale" onchange="this.form.submit()" class="rounded-lg border border-slate-300 bg-white px-2.5 py-2 text-sm font-medium text-slate-800 shadow-sm focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/30">
        @foreach (config('localization.supported', ['en' => 'English']) as $code => $label)
            <option value="{{ $code }}" @selected(app()->getLocale() === $code)>{{ $label }}</option>
        @endforeach
    </select>
</form>
