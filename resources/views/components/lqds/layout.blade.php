<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        {{ config('lqds.name') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="min-h-screen bg-slate-100">

<div class="flex min-h-screen">

    <!-- Sidebar -->

    <aside class="w-72 border-r border-slate-200 bg-white">

        <x-lqds.navigation />

    </aside>

    <!-- Main Content -->

    <div class="flex min-w-0 flex-1 flex-col">

        <x-lqds.header />

        <main class="flex-1 p-8">

            {{ $slot }}

        </main>

    </div>

</div>

</body>

</html>