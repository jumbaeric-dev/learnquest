<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Premium adventure · LearnQuest</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="mx-auto flex min-h-screen max-w-2xl items-center justify-center px-6 py-16">
        <section class="w-full rounded-3xl border border-white/10 bg-white p-8 text-center text-slate-900 shadow-2xl sm:p-12">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-cyan-100 text-3xl" aria-hidden="true">🔒</div>
            <p class="mt-6 text-sm font-bold uppercase tracking-widest text-cyan-700">A new adventure awaits</p>
            <h1 class="mt-3 text-3xl font-extrabold">This adventure is part of Premium</h1>
            <p class="mx-auto mt-4 max-w-lg text-base leading-7 text-slate-600">
                This lesson is not included in your family’s current plan. Ask your parent or caregiver to review the available plans.
            </p>
            <a href="{{ route('parent.login') }}" class="mt-8 inline-flex items-center justify-center rounded-xl bg-cyan-700 px-6 py-3 font-bold text-white transition hover:bg-cyan-800 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2">
                Parent or caregiver sign in
            </a>
            <p class="mt-6 text-xs text-slate-500">LearnQuest helps families explore new skills safely, one step at a time.</p>
        </section>
    </main>
</body>
</html>
