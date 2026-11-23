<x-lq.glass-card>

    <div class="flex items-center justify-between">

        <h3 class="font-bold text-slate-800">
            🗺️ {{ __('localization.world_progress') }}
        </h3>

        <span class="text-sm font-semibold text-slate-500">
            {{ __('localization.courses_count_progress', ['completed' => $progress['completedCourses'], 'total' => $progress['totalCourses']]) }}
        </span>

    </div>

    <div class="mt-3">

        <div class="lq-progress">
            <span style="width: {{ $progress['percentage'] }}%;"></span>
        </div>

        <p class="mt-1 text-right text-xs text-slate-400">
            {{ __('localization.percent_complete', ['percent' => $progress['percentage']]) }}
        </p>

    </div>

</x-lq.glass-card>
