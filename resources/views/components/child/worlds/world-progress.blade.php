<x-lq.glass-card>

    <div class="flex items-center justify-between">

        <h3 class="font-bold text-slate-800">
            🗺️ World Progress
        </h3>

        <span class="text-sm font-semibold text-slate-500">
            {{ $progress['completedCourses'] }} / {{ $progress['totalCourses'] }} courses
        </span>

    </div>

    <div class="mt-3">

        <div class="lq-progress">
            <span style="width: {{ $progress['percentage'] }}%;"></span>
        </div>

        <p class="mt-1 text-right text-xs text-slate-400">
            {{ $progress['percentage'] }}% complete
        </p>

    </div>

</x-lq.glass-card>
