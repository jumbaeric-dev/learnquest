@props([
    'props' => [],
])

<x-lq.glass-card>

    <h2 class="mb-6 text-lg font-semibold">

        Properties

    </h2>

    <table class="w-full text-left">

        <thead>

            <tr class="border-b">

                <th class="py-3">Prop</th>

                <th>Type</th>

                <th>Default</th>

                <th>Description</th>

            </tr>

        </thead>

        <tbody>

            @foreach($props as $prop)

                <tr class="border-b">

                    <td class="py-4 font-mono">

                        {{ $prop['name'] }}

                    </td>

                    <td>

                        {{ $prop['type'] }}

                    </td>

                    <td>

                        {{ $prop['default'] }}

                    </td>

                    <td>

                        {{ $prop['description'] }}

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</x-lq.glass-card>