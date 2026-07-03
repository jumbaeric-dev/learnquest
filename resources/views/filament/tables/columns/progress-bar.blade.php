<div class="w-full">
    <div class="flex justify-between text-xs mb-1">
        <span>{{ $getState() }}%</span>
    </div>

    <div class="w-full bg-gray-200 rounded-full h-2.5">
        <div
            class="bg-primary-600 h-2.5 rounded-full"
            style="width: {{ $getState() }}%"
        ></div>
    </div>
</div>